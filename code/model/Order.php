<?php 

/**
 * @package ecommerce
 */
 
/** 
 * The order class is a databound object for handling Orders within sapphire.
 * Each order can contain one or many products, each with special attributes.
 * This class handles all order specific algorithims and processing. 
 * Listed below are
 * class Order ( our main order class ) 
 * class Order_item ( our subclass which handles multiple products in an order
 * class Order_item_attribute ( which handles any attributes of the product such as colour,
 * size, or type )
 * class Order_receiptEmail (which handles all emails generated from an order) * typically overloaded.
 */
 
 class Order extends DataObject {
 	
 	/**
 	 * Unpaid(Default) : Order created but no successful payment yet
 	 * Paid : Order successfully paid
 	 * Query : 
 	 * Processing : Order already paid and the package is  currently processed
 	 * Sent : Order already paid and now sent
 	 * Complete : 
 	 * AdminCancelled : Order cancelled by the administrator
 	 * MemberCancelled : Order cancelled by the member
 	 */
	static $db = array(
		'Status' => "Enum(array('Unpaid', 'Paid', 'Query', 'Processing', 'Sent', 'Complete', 'AdminCancelled', 'MemberCancelled'), 'Unpaid')",
		"Country" => "Text",
		"UseShippingAddress" => "Boolean",
		"ShippingName" => "Text",
		"ShippingAddress" => "Text",
		"ShippingAddress2" => "Text",
		"ShippingCity" => "Text",
		"ShippingCountry" => "Text",
		"Printed" => "Boolean"
	);
	
	static $has_one = array (
		'Member' => 'Member'
	);
	
	static $has_many = array(
		'Attributes' => 'OrderAttribute',
		'OrderStatusLogs' => 'OrderStatusLog',
		'Payments' => 'Payment'
	);
	
	static $casting = array(
		"SubTotal" => "Currency",
		"Total" => "Currency",
		"Shipping" => "Currency",
		"TotalOutstanding" => "Currency",
	);
	
	// Static Values And Management
	
	/**
	 * Order class used for creation
	 */
	protected static $order_class = 'Order';
	
	static function set_order_class($orderClass) {self::$order_class = $orderClass;}
	
	/**
	 * Status which stand for already paid because the order has a payment successful
	 */
	static $paid_status = array('Paid', 'Processing', 'Sent', 'Complete');
	
	/**
	 * Currency used in orders
	 */
	protected static $site_currency = 'USD';
	
	protected static $receiptEmail;	

	protected static $receiptSubject;

	protected static $can_cancel_before_payment = true;

	protected static $can_cancel_before_processing = false;
	
	protected static $can_cancel_before_sending = false;
	
	protected static $can_cancel_after_sending = false;
	
	/**
	 * The modifiers represent the additional charges or deductions associated to an order like shipping, tax but also vounchers, etc...
	 */
	protected static $modifiers = array();

	static function set_site_currency($currency) {
		self::$site_currency = $currency;
	}

	static function set_email($e) {
		self::$receiptEmail = $e;
	}

	/**
	 * Return the site currency in use.
	 *
	 * @return string
	 */
	static function site_currency() {
		return self::$site_currency;
	}

	/**
	 * Set the subject of the order receipt email.
	 */
	static function set_subject($subject) {
		self::$receiptSubject = $subject;
	}	
	
	static function set_modifiers($modifiers) {
		self::$modifiers = $modifiers;
	}

	static function set_cancel_before_payment($value) {
		self::$can_cancel_before_payment = $value;
	}

	static function set_cancel_before_processing($value) {
		self::$can_cancel_before_processing = $value;
	}
	
	static function set_cancel_before_sending($value) {
		self::$can_cancel_before_sending = $value;
	}
	
	static function set_cancel_after_sending($value) {
		self::$can_cancel_after_sending = $value;
	}
	
	// Items Management
	
	/**
	 * Returns the items of the order, if it hasn't been saved yet
	 * it returns the items from session, if it has, it returns them 
	 * from the DB entry.
	 */
	function Items() {
 		if($this->ID) return $this->itemsFromDatabase();
 		else if($items = ShoppingCart::get_items()) return $this->createItems($items);
 		else return null;
	}
	
	protected function itemsFromDatabase() {
		return DataObject::get('OrderItem', "`OrderID` = '$this->ID'");
	}
	
	protected function createItems(array $items, $write = false) {
		if($write) {
			foreach($items as $item) {
				$item->OrderID = $this->ID;
				$item->write();
			}
		}
		return $write ? $this->itemsFromDatabase() : new DataObjectSet($items);
	}
	
	/**
	 * Returns the subtotal of the items for this order.
	 */
	function _SubTotal() {
		$result = 0;
		if($items = $this->Items()) {
			foreach($items as $item) $result += $item->Total();
		}
		return $result;
	}
	
	// Modifiers Management
	
	/**
	 * Returns the modifiers of the order, if it hasn't been saved yet
	 * it returns the modifiers from session, if it has, it returns them 
	 * from the DB entry.
	 */ 
 	function Modifiers() {
 		if($this->ID) return $this->modifiersFromDatabase();
 		else if($modifiers = ShoppingCart::get_modifiers()) return $this->createModifiers($modifiers);
 		else return null;
	}
	
	protected function modifiersFromDatabase() {
		return DataObject::get('OrderModifier', "`OrderID` = '$this->ID'");
	}
	
	protected function createModifiers(array $modifiers, $write = false) {
		if($write) {
			foreach($modifiers as $modifier) {
				$modifier->OrderID = $this->ID;
				$modifier->write();
			}
		}
		return $write ? $this->modifiersFromDatabase() : new DataObjectSet($modifiers);
	}
	
	/**
	 * Returns the subtotal of the modifiers of this order without those in the optional array parameter (usefull for the tax calculation).
	 */
	function _ModifiersSubTotal($modifiersNameExcluded = null) {
		$total = 0;
		if($modifiers = $this->Modifiers()) {
			foreach($modifiers as $modifier) {
				if(! $modifiersNameExcluded || ! is_array($modifiersNameExcluded) || ! in_array(self::$modifiersName, get_class($modifier))) $total += $modifier->Total();
			}
		}
		return $total;
	}
	
	static function init_all_modifiers() {
		if(self::$modifiers && is_array(self::$modifiers) && count(self::$modifiers) > 0) {
			foreach(self::$modifiers as $className) {
				if(class_exists($className)) {
					$modifier = new $className();
					if($modifier instanceof OrderModifier) eval("$className::init_for_order(\$className);");
				}
			}
		}
	}
	
	/*
	 * Return a DataObjectSet which contains the forms to add some modifiers to update the OrderInformation table
	 */
	static function get_modifier_forms($controller) {
		$forms = array();
		if(self::$modifiers && is_array(self::$modifiers) && count(self::$modifiers) > 0) {
			foreach(self::$modifiers as $className) {
				if(class_exists($className)) {
					$modifier = new $className();
					if($modifier instanceof OrderModifier && eval("return $className::show_form();") && $form = eval("return $className::get_form(\$controller);")) array_push($forms, $form);
				}
			}
		}
		return count($forms) > 0 ? new DataObjectSet($forms) : null;
	}
	
	// Order Management
	
	/**
  	 * Returns the total cost of an order including the additional charges or deductions of its modifiers.
  	 */
	function _Total() {
		return $this->_SubTotal() + $this->_ModifiersSubTotal();
	}
	
	/**
	 * Checks to see if any payments have been made on this order
	 * and if so, subracts the payment amount from the order
	 * Precondition : The order is in DB
	 */
	function _TotalOutstanding(){
		$total = $this->_Total();
		if($payments = $this->Payments()) {
			foreach($payments as $payment) {
				if($payment->Status == 'Success') $total -= $payment->Amount;
			}
		}
		return $total;
	}
	
	function Link() {
		return AccountPage::get_order_link($this->ID);
	}
	
	/*
	 * Returns if the order can be cancelled
	 * Precondition : Order is in DB
	 */
	function CanCancel() {
		switch($this->Status) {
			case 'Unpaid' : return self::$can_cancel_before_payment;
			case 'Paid' : return self::$can_cancel_before_processing;
			case 'Processing' :	case 'Query' : return self::$can_cancel_before_sending;
			case 'Sent' : case 'Complete' : return self::$can_cancel_after_sending;
			default : return false;
		}
	}
		
	// Order attributes access functions
	
	/*
	 * Returns the payments of the order
	 * Precondition : Order is in DB
	 */
	function Payments() {
		return DataObject::get('Payment', "`OrderID` = '$this->ID'", '`LastEdited` DESC');
	}
	
	/**
	 * Return the currency of this order.
	 * Note: this is a fixed value across the entire site. 
	 */
	function Currency() {
		return self::$site_currency;
	}
	
	static function create() {
		$orderClass = self::$order_class; 
		return new $orderClass();
	}
	
	static function current_order() {
		return self::create();
	}
	
	static function save_current_order() {
		
		//1) Order creation
		
		$order = self::current_order();
		$order->write();
		
		//2) Items saving
		
		if($items = ShoppingCart::get_items()) $order->createItems($items, true);
		
		//3) Modifiers saving
		
		if($modifiers = ShoppingCart::get_modifiers()) $order->createModifiers($modifiers, true);
		
		//4) Member saving
		
		$order->MemberID = Member::currentUserID();
		
		$order->write();
		
		return $order;
	}
	
	// Order Template Management
	
	function TableSubTotalID() {
		return 'Table_Order_SubTotal';
	}

	function TableTotalID() {
		return 'Table_Order_Total';
	}
	
	function CartSubTotalID() {
		return 'Cart_Order_SubTotal';
	}

	function CartTotalID() {
		return 'Cart_Order_Total';
	}
	
	function updateForAjax(array &$js) {
		$subTotal = DBField::create('Currency', $this->_SubTotal())->Nice();
		$total = DBField::create('Currency', $this->_Total())->Nice() . ' ' . self::$site_currency;
		$js[] = array('id' => $this->TableSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
	}
	
	function IsSent() {
		return $this->Status == 'Sent';
	}
	
	function IsProcessing() {
		return $this->IsSent() || $this->Status == 'Processing';
	}
	
	function IsValidate() {
		return $this->IsProcessing() || $this->Status == 'Paid';
	}
	
	function IsPaid() {
		return $this->IsValidate();
	}
	
	function Status() {
		return $this->IsPaid() ? _t('Order.SUCCESSFULL', 'Order Successful') : _t('Order.INCOMPLETE', 'Order Incomplete');
	}
	
	function checkoutLink() {
		return $this->ID ? CheckoutPage::get_checkout_order_link($this->ID) : CheckoutPage::find_link();
	}
	
	// Order Emails Sending Management 
  	
  	/*
	 * Send the receipt of the order by mail
	 * Precondition : The order payment has been successful
	 */
	function sendReceipt() {
		$this->sendEmail('Order_ReceiptEmail');
	}
  	
	/*
	 * Send a mail of the order to the client (and another to the admin)
	 * @param $emailClass - the class name of the email you wish to send
	 * @param $copyToAdmin - true by default, whether it should send a copy to the admin
	 */
	protected function sendEmail($emailClass, $copyToAdmin = true) {
 		$from = self::$receiptEmail ? self::$receiptEmail : Email::getAdminEmail();
 		$to = $this->Member()->Email;
		$subject = self::$receiptSubject ? self::$receiptSubject : "Shop Sale Information #$this->ID";
 		
 		$purchaseCompleteMessage = DataObject::get_one('CheckoutPage')->PurchaseComplete;
 		
 		$email = new $emailClass();
 		$email->setFrom($from);
 		$email->setTo($to);
 		$email->setSubject($subject);
		if($copyToAdmin) $email->setBcc(Email::getAdminEmail());
		
		$email->populateTemplate(
			array(
				'PurchaseCompleteMessage' => $purchaseCompleteMessage,
				'Order' => $this
			)
		);
		
		$email->send();
	}
	
	/**
	 * Returns the correct shipping address. If there is an alternate
	 * shipping country then it uses that. Else it's the member's country.
	 * @param $codeOnly - if set, returns only the country code as opposed to the full name.
	 */
	function findShippingCountry($codeOnly = false) {
		if(! $this->ID)	$country = ShoppingCart::has_country() ? ShoppingCart::get_country() : EcommerceRole::findCountry();
		else if(! $this->UseShippingAddress || ! $country = $this->ShippingCountry)	$country = EcommerceRole::findCountry();
		return $codeOnly ? $country : EcommerceRole::findCountryTitle($country);
	}
							
	/*
	 * Returns a TaxModifier object that provides information about tax on this order.
	 */
	function TaxInfo() {
		if($modifiers = $this->Modifiers()) {
			foreach($modifiers as $modifier) {
				if($modifier instanceof TaxModifier) return $modifier;
			}
		}
	}
  			
	/**
	 * Send a message to the client containing the latest note of {@OrderStatusLog} and the current status.
	 * Used in {@OrderReport}.
	 * 
	 * @param $note Optional note-content (instead of using the OrderStatusLog)
	 */
	function sendStatusChange($note = null) {
		if(!$note) {
			$logs = DataObject::get('OrderStatusLog', "OrderID = {$this->ID}", "Created DESC", null, 1);
			$latestLog = $logs->First();
			$note = $latestLog->Note;
		}
		
		$member = $this->Member();
		
 		if(self::$receiptEmail) {
 			$adminEmail = self::$receiptEmail;
 		} else {
 			$adminEmail = Email::getAdminEmail();
 		}		
		
		$e = new Order_statusEmail();
		$e->populateTemplate($this);
		$e->populateTemplate(
			array(
				"Order" => $this,
				"Member" => $member,
				"Note" => $note
			)
		);
		$e->from = $adminEmail;
		$e->setSubject('Your order status');
		$e->setTo($member->Email);
		$e->send();
	}
				
	function updatePrinted($printed){
		$this->__set("Printed", $printed);
		$this->write();
	}

	/**
	 * Updates the database structure of the Order table
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		// 1) If some orders with the old structure exist (hasShippingCost, Shipping and AddedTax columns presents in Order table), create the Order Modifiers SimpleShippingModifier and TaxModifier and associate them to the order
		
		$exist = DB::query("SHOW COLUMNS FROM `Order` LIKE 'Shipping'")->numRecords();
 		if($exist > 0) {
 			if($orders = DataObject::get('Order')) {
 				foreach($orders as $order) {
 					$id = $order->ID;
 					$hasShippingCost = DB::query("SELECT `hasShippingCost` FROM `Order` WHERE `ID` = '$id'")->value();
 					$shipping = DB::query("SELECT `Shipping` FROM `Order` WHERE `ID` = '$id'")->value();
 					$addedTax = DB::query("SELECT `AddedTax` FROM `Order` WHERE `ID` = '$id'")->value();
					$country = $order->findShippingCountry(true);
 					if($hasShippingCost == '1' && $shipping != null) {
 						$modifier1 = new SimpleShippingModifier();
 						$modifier1->Amount = $shipping < 0 ? abs($shipping) : $shipping;
 						$modifier1->Type = 'Chargable';
 						$modifier1->OrderID = $id;
 						$modifier1->Country = $country;
 						$modifier1->ShippingChargeType = 'Default';
 						$modifier1->write();
 					}
 					if($addedTax != null) {
 						$modifier2 = new TaxModifier();
 						$modifier2->Amount = $addedTax < 0 ? abs($addedTax) : $addedTax;
 						$modifier2->Type = 'Chargable';
 						$modifier2->OrderID = $id;
 						$modifier2->Country = $country;
 						$modifier2->Name = 'Undefined After Ecommerce Upgrade';
 						$modifier2->TaxType = 'Exclusive';
 						$modifier2->write();
 					}
 				}
 				Database::alteration_message('The \'SimpleShippingModifier\' and \'TaxModifier\' objects have been successfully created and linked to the appropriate orders present in the \'Order\' table', 'created');	
 			}
 			DB::query("ALTER TABLE `Order` CHANGE COLUMN `hasShippingCost` `_obsolete_hasShippingCost` tinyint(1)");
 			DB::query("ALTER TABLE `Order` CHANGE COLUMN `Shipping` `_obsolete_Shipping` decimal(9,2)");
 			DB::query("ALTER TABLE `Order` CHANGE COLUMN `AddedTax` `_obsolete_AddedTax` decimal(9,2)");
 			Database::alteration_message('The columns \'hasShippingCost\', \'Shipping\' and \'AddedTax\' of the table \'Order\' have been renamed successfully. Also, the columns have been renamed respectly to \'_obsolete_hasShippingCost\', \'_obsolete_Shipping\' and \'_obsolete_AddedTax\'', 'obsolete');
  		}
  		
  		// 2) Cancel status update
  		
  		if($orders = DataObject::get('Order', "`Status` = 'Cancelled'")) {
  			foreach($orders as $order) {
  				$order->Status = 'AdminCancelled';
  				$order->write();
  			}
  			Database::alteration_message('The orders which status was \'Cancelled\' have been successfully changed to the status \'AdminCancelled\'', 'changed');
  		}
	}
	
}

/**
 * This class handles the receipt email which gets sent once an order is made.
 * You can call it by issuing sendReceipt() in the Order class.
 */  
class Order_ReceiptEmail extends Email_Template {

	protected $ss_template = 'Order_ReceiptEmail';

}

/**
 * This class handles the status email which is sent after changing the attributes
 * in the report (eg. status changed to 'Shipped').
 */ 
class Order_StatusEmail extends Email_Template {

	protected $ss_template = 'Order_StatusEmail';

}

class Order_CancelForm extends Form {
	
	function __construct($controller, $name, $orderID) {
		$fields[] = new HiddenField('OrderID', '', $orderID);
		$actions[] = new FormAction('doCancel', 'Cancel This Order');
		parent::__construct($controller, $name, new FieldSet($fields), new FieldSet($actions));
	}
	
	function doCancel($RAW_data, $form) {
		$SQL_data = Convert::raw2sql($RAW_data);
		$order = DataObject::get_by_id('Order', $SQL_data['OrderID']);
		$order->Status = 'MemberCancelled';
		$order->write();
		Director::redirectBack();
		return;
	}
	
}

?>
