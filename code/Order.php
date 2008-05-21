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
 	
	static $db = array(
		"Status" => "Enum('Unpaid,Paid,Query,Processing,Sent,Complete,Cancelled','Unpaid')",
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
		'Attributes' => 'Order_Attribute',
		'OrderStatusLogs' => 'OrderStatusLog'
	);
	
	static $casting = array(
		"SubTotal" => "Currency",
		"Total" => "Currency",
		"Shipping" => "Currency",
		"TotalOutstanding" => "Currency",
	);
	
	// Static Values And Management
	
	/**
	 * Currency used in orders
	 */
	protected static $site_currency = 'USD';
	
	static function set_site_currency($currency) {self::$site_currency = $currency;}
	static function site_currency() {return self::$site_currency;}
	
	/**
	 * The modifiers represent the additional charges or deductions associated to an order like shipping, tax but also vounchers, etc...
	 */
	protected static $modifiers = array();
	
	static function set_modifiers($modifiers) {self::$modifiers = $modifiers;}
	
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
				if(! $modifiersNameExcluded || ! is_array($modifiersNameExcluded) || ! in_array(self::$modifiersName, get_class($modifier))) $total += $modifier->getValue();
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
	function _Total() {return $this->_SubTotal() + $this->_ModifiersSubTotal();}
	
	/**
	 * Checks to see if any payments have been made on this order
	 * and if so, subracts the payment amount from the order
	 * ASSUMPTION : Only one payment per order
	 */
	function _TotalOutstanding(){
		// TODO Total is returning a casted object which you can't do addition too.... DUM
		$total = $this->_Total();
		if($this->ID) {
			$payment = Object::create("Payment");
			$payment = DataObject::get_one(get_class($payment),"Payment.OrderID = $this->ID");

			// revised rounding from Hayden
			// we HAVE to do this, because we use $Title.Nice on the front end which is inconsistent
			// with the calculation in php
			
			// TODO - find a better way to do this. Sean and Hayden @ SS had a crack at it, but couldn't
			// get anywhere but do this
			$difference = (round($total * 100) - round($payment->Amount * 100)) / 100;
			
			if($payment->Status == 'Success') {
				return $difference;
			} else {
				return $total;
			}
		} else {
			return $total;
		}
	}
	
	// Order attributes access functions
	
	function Payment() {return $this->ID ? DataObject::get('Payment', "`OrderID` = '$this->ID'") : null;}
	function Customer() {return $this->Member();}
	
	/**
	 * Return the currency of this order.
	 * Note: this is a fixed value across the entire site. 
	 */
	function Currency() {return self::site_currency();}
	
	static function current_order() {return new Order();}
	
	static function save_to_database() {
		
		//1) Order creation
		
		$order = new Order();
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
	
	function SubTotalIDForTable() {return 'Table_Order_SubTotal';}
	function TotalIDForTable() {return 'Table_Order_Total';}
	
	function SubTotalIDForCart() {return 'Cart_Order_SubTotal';}
	function TotalIDForCart() {return 'Cart_Order_Total';}
	
	function updateForAjax(array &$js) {
		$js[$this->SubTotalIDForTable()] = $this->_SubTotal();
		$js[$this->TotalIDForTable()] = $this->_Total();
		$js[$this->SubTotalIDForCart()] = $this->_SubTotal();
		$js[$this->TotalIDForCart()] = $this->_Total();
	}
		
	static $factory_class = "Order";

	/**
	 * Items are added to this array when loaded in memory
	 */
	//protected $items = array();
	
	/**
	 * This can be set to an object that handles data operations.
	 * ShoppingCart is one such handler. 
	 */
	//protected $dataHandler = null;
	
	/**
	 * All these status count as a "completed order"
	 */
	static $complete_status = array('Paid', 'Sent', 'Complete');


	/**
	 * Set the email of the administrator
	 */
	protected static $receiptEmail;	

	static function set_email($e) {
		self::$receiptEmail = $e;
	}
	
	/**
	 * Set the subject of the order receipt email.
	 */
	protected static $receiptSubject;
	
	static function set_subject($subject) {
		self::$receiptSubject = $subject;
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
	
	/**
	 * If a Order is manually set to paid, update
	 * the appropriate Payment. Also log the change of the Status automatically.
	 */
	function onBeforeWrite() {
		if($this->Status == 'Paid' && !$this->original['Status'] != 'Paid') {
			// if the status was set to paid for the first time, update a payment-object
			$payment = DataObject::get_one('Payment', "OrderID = {$this->ID}");
			if($payment) {
				$payment->Status = 'Success';
				$payment->write();
			}
		} else if($this->Status == 'Unpaid') {
			// if the status was set to set unpaid for the first time, update a payment-object
			$payment = DataObject::get_one('Payment', "OrderID = {$this->ID}");
			if($payment) {
				$payment->Status = "Pending";
				$payment->write();
			}
		}
		parent::onBeforeWrite();
	}

	/**
	 * Turn this order object into a shopping cart
	 */
	/*function changeToShoppingCart() {	

		$this->dataHandler = new ShoppingCart();
		
		// We load the order object's default 'DataObject stuff' with the cart contents.
		// That way, if we ever need to save or access these values internally, the data is there.
		$this->items = $this->dataHandler->items($this);
		$this->record = $this->dataHandler->getRecord($this);
		$this->modifiers = $this->dataHandler->modifiers($this);

	}*/
	
	/**
	 * Creates an order from the shopping cart
	 * Saves the order to the database
	 */
	/*static function createOrderFromShoppingCart() {
		$order = Order::create();
		$order->changeToShoppingCart();
		$order = $order->saveToDatabase();
		return $order;
	}*/

	/**
	 * Turn a 'data handled' order (such as a shopping cart) into a regular order
	 */
	/*function saveToDatabase(){
		if(! $this->dataHandler) {
			user_error('saveToDatabase called on a non-data-handled object, turning it into a ShoppingCart', E_USER_WARNING);
			$this->changeToShoppingCart();
		}
				
		// Store the items to this object for later saving. 
		// Remove the datahandler
		$this->items = $this->dataHandler->items($this);
		$this->modifiers = $this->dataHandler->modifiers($this);
		$this->dataHandler = null;
		
		// 23/7/2007 Sean says: This creates blank members? This is not working.
		// @todo - find out why this was here.		
		//$member = $this->Member();

		$member = Member::currentUser();
		
		$this->MemberID = $member->ID;
		$this->TotalOrderValue = $this->Total();
		
		// items and modifiers can't be added to the order or saved till the 
		// order has an id
		$items = $this->Items();
		$modifiers = $this->Modifiers();
		$this->write();
			
		// if there are any items, iterate through them, and write
		// the order IDs	
		if($items) {
			foreach($items as $item){
				$item->OrderID = $this->ID;
				$item->write();
			}
		}
		
		// Problem : There is no any modifiers here, so there are not saved
		
		// if there are any modifiers, iterate through them
		if($modifiers) {
			foreach($modifiers as $modifier) {
				$modifier->OrderID = $this->ID;
				$modifier->write();
			}
		}
		
		return $this;
	} */
	
	/**
	 * Returns the value of a particular field.
	 * This makes use of the dataHandler where necessary.
	 */
	/*function getField($fieldName) {
		if($this->dataHandler) return $this->dataHandler->getField($this, $fieldName);
		else return parent::getField($fieldName);
	}*/

	/**
	 * Sets the value of a particular field.
	 * This makes use of the dataHandler where necessary.
	 */
	/*function setField($fieldName, $fieldValue) {	
		if($this->dataHandler) $this->dataHandler->setField($this, $fieldName, $fieldValue);
		return parent::setField($fieldName, $fieldValue);
	}*/
	
	/**
	 * Factory method to create an Order.
	 */
	static function create($data = null){
		$className = self::$factory_class;
		if($className){
			return new $className($data);
		}else{
			USER_ERROR("ORDER::createOrderItem() - Order class Not defined in _config.php ");
		}
	}
	
	/**
	 * Creates the shopping cart object
	 */
	/*static function ShoppingCart() {
		$order = Order::create();
		if($order){
			$order->changeToShoppingCart();
		}else{
			USER_ERROR("ORDER::ShoppingCart() - Could not create order from base class", E_USER_ERROR);
		}
		return $order;
	}*/
	
	static function makeFrom($className){
		self::$factory_class = $className;
	}

	/**
	 * Factory method for new order items.
	 */ 
	/*function createOrderItem($product, $quantity) {
		$orderClassName = $this->stat('item_class');
		return new $orderClassName($product, $quantity);
	}*/
	
	/**
	* Adds a product to this order. If there is an ID, it updates the DB.
	* @param DataObject $product An instance of product you wish to add.
	* @param int $quantity The quantity you wish to add, default is 1.
	*/
	/*function add($product, $quantity = 1) {
		if(!isset($this->items[$product->ID])) $this->items[$product->ID] = 0;
		$this->items[$product->ID] += $quantity;
		if($this->dataHandler) {
			$this->dataHandler->setQuantity($this, $product, $this->items[$product->ID]);	
		} elseif($this->ID) {
			$this->addToDatabase($product);
		}
  	}*/
  	
  	/*function removeByQuantity($product, $quantity = 1) {
  		$this->items[$product->ID] -= $quantity;
  		if($this->dataHandler) {
  			$this->dataHandler->setQuantity($this, $product, $this->items[$product->ID]);
  		} else if($this->ID) {
  			$this->addToDatabase($product);
  		}
  	}*/
  
	/**
	 * Saves the given product to the database as an orderItem
	 */
	/*function addToDatabase($product) {
		$orderItem = $this->createOrderItem($product, $this->items[$product->ID]); 	
		$orderItem->setCart($this);
		$orderItem->OrderID = $this->ID;
		$orderItem->write();
	}*/
	
	/** 
	* Reduces the quanity of a product in the order by one, 
	* or if it is one, it removes it all together.
	*/
	/*function remove($product){
		$id = $product->ID;
   		$this->items[$id]--;
   	
		if($this->dataHandler){
			$this->dataHandler->setQuantity($this, $product, $this->items[$product->ID]);	
		}else if($this->ID){
			$this->removeFromDatabase($product);
		}
		
		if($this->items[$id] <= 0) {
   			unset($this->items[$id]);
   		}
   		
   		$this->updateModifiers();
	}*/
	
	/**
	 * Return the quantity of items of that ID in the cart
	 */
	/*function getQuantity($productID){
		return $this->items[$productID];
	}*/
	
	/*function removeall($product){
		unset($this->items[$product->ID]);
		if($this->dataHandler){
			$this->dataHandler->setQuantity($this, $product, 0);
		}else{
			$this->removeFromDatabase($product);	
		}
		
		$this->updateModifiers();
	}*/
	
	/**
	* Removes an item from the database (you need an ID to be stored on the order)
	*/
	/*function removeFromDatabase($product){
		// We need to have an order ID to get the saved order items.
		
		// TODO: Should we have some data integrity here to say you can't remove 
		// a product from the DB if it has a payment ?
		if($this->ID){
			$orderItem = DataObject::get_one("Order_item", "Product.ID = $product->ID AND Order.ID = $this->ID");
			$orderItem->delete();
		}		
	}*/
	
	/*function Items() {
 		// If we have an ID, assume that this is a database order
 		if($this->ID) return $this->itemsFromDatabase();
 		else if($products = ShoppingCart::get_products()) return $this->createOrderItems($products);
 		else return null;
	}*/
	
	function ContinueCountItems() {
		if($items = $this->Items()) {
			$i = 1;
			foreach($items as $item) $item->setCountID($i++);
		}
		return $items;
	}
		
	/*protected function createOrderItems(array $products, $write = false) {
		$orderItems = array();
		$orderItemClass = $this->stat('item_class');
		foreach($products as $productID => $quantity) {
			if($quantity > 0) {
				$product = DataObject::get_by_id('Product', $productID);
				$orderItem = new $orderItemClass($product, $quantity);
				if($write) {
					$orderItem->OrderID = $this->ID;
					$orderItem->write();
				}
				else array_push($orderItems, $orderItem);
			}
		}
		return $write ? $this->itemsFromDatabase() : new DataObjectSet($orderItems);
	}*/
		
	/*function addModifier(OrderModifier $modifier) {
		if(! $this->modifiers) $this->modifiers = new DataObjectSet();
		$this->modifiers->push($modifier);
		if($this->dataHandler) $this->dataHandler->addModifier($this, $modifier);*/	
		/*elseif($this->ID) {
			$this->addToDatabase($product);
		}*/
	//}
		
   /** 
	* Removes a modifier
	* @param modifier : Modifier to remove
	*/
	/*function removeModifier($modifier){
		if($modifiers = $this->modifiers) {
   			$newModifiers = new DataObjectSet();
   			foreach($modifiers as $oneModifier) {
   				if($oneModifier !== $modifier) $newModifiers->push($oneModifier);
   			}
   			$this->modifiers = $newModifiers;
   		}
   		
		if($this->dataHandler) $this->dataHandler->removeModifier($this, $modifier);	
		else if($this->ID) $modifier->delete();
				
		if($this->modifiers->Count() == 0) $this->modifiers = null;
	}*/
		
	/**
	 * Attempts to process this orders payment.
	 * Assummes the correct payment data, for each payement type is 
	 * included in $paymentData. it also assumes the order has been written, before
	 * payment can be made.
	 */
	  function attemptPurchase($paymentData) {
		$this->write();
		
		// Process Payment (add subscription product info)
		$paymentData['OrderID'] = $this->ID;

		// NOTE: The reference to $_SESSION is bad.  We need to work out how the shopping cart in session can be linked to a saved order.  
		Session::set('CartInfo.OrderID', $order->ID);

		$payment = new Payment($paymentData);
		$payment->OrderID = $this->ID;
		$result = $payment->processPayment();
				
		if($result[Payment::$success]) {
			// PURCHASE COMPLETE
		  return true;
		} else {
			// PURCHASE FAILURE
		  return false;
		}
	}
		
	function OrderContent(){
		if($member = Member::currentUser()){
			// If the order was successful, get the appropriate checkout text
			if(Session::get('Order.PurchaseComplete') == 1){
				$Checkout = DataObject::get("Checkout");
				return $Checkout->PurchaseComplete;
			}else{
				return "Order Error";
				//@todo find a more appropriate error message here.
			}
		}
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
	 * Sends an receipt to the client (and another to the admin)
	 * ASSUMPTION : Member MUST be set for this order.
	 * @param $emailClass - the class name of the email you wish to send
	 * @param $copyToAdmin - true by default, whether it should send a copy to the admin
	 */
	protected function sendEmail($emailClass, $copyToAdmin = true) {
 		// define the member and set_email() address of the admin
 		$member = $this->Member();
 		
 		// if Order::$receiptEmail is set from the static Order::set_email() in _config.php then use that,
 		// otherwise try and use the getAdminEmail() function from Email, otherwise just do nothing.
 		if(self::$receiptEmail) {
 			$adminEmail = self::$receiptEmail;
 		} else {
 			$adminEmail = Email::getAdminEmail();
 		}

		if(self::$receiptSubject) {
			$subject = self::$receiptSubject;
		}
 		
 		// send an email to the customer
 		$e = new $emailClass($member->Email, $adminEmail);
		$e->populateTemplate($this);
		$e->populateTemplate(
			array(
				"Order" => $this,
				"Member" => $member
			)
		);
		if(isset($subject)) $e->setSubject($subject);
		$e->send();
		
		// if copyToAdmin is true, send a copy to the admin AND if the admin email has been defined.
		if($copyToAdmin && $adminEmail) {
			$e2 = new $emailClass($adminEmail, $adminEmail, "User of the site has submitted an order");
			$e2->populateTemplate($this);
			$e2->populateTemplate(
				array(
					"Order" => $this,
					"Member" => $member
				)
			);
			if($subject) $e2->setSubject($subject);
			$e2->send();			
		}
	}
	
	function sendReceipt() {
		$this->sendEmail('Order_receiptEmail');
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
		
	function _SuccessfulPaymentLink(){
		return Director::AbsoluteBaseURL(). CheckoutPage::find_link() . "paid";
	}
	
	public function _Logo(){
		global $projectLogo;
		return Director::AbsoluteBaseURL() . $projectLogo;
	}

	/**
	 * returns true or false based on the if a payment has been made.
	 */
	function isComplete(){
		$totaloutstanding = $this->TotalOutstanding();
		return ($totaloutstanding == 0 && (in_array($this->Status,$this->completeStati)));
	}
	
	/**
	* Overloaded from Shopping cart
	*/
	static function saveOrder(){
		$sc =  Order::ShoppingCart();
		$order = Object::create('Order');

		$member = Member();
		$order->MemberID = $member->ID;
		$order->TotalOrderValue = $sc->Total();
		$orderID = $order->write();
			
		// items can't be added to the order or saved till the order has an id
		if($items = $sc->Items()){
			foreach($items as $item){
				$item->OrderID = $orderID;
				$item->write();
			}
		}else{
			user_error("Order: No Items in Order", E_USER_WARNING);
		}
		$sc->setID($orderID);
		return $order;
	}
	 
	/**
	 * Return the member's email address
	 */
	public function MemberEmail(){
		$member = DataObject::get_by_id("Member", $this->MemberID);
		return $member->Email;
	}
	
	function updatePrinted($printed){
		$this->__set("Printed", $printed);
		$this->write();
	}

	/**
	 * Once the module is created, create some dummy pages to show developers what the
	 * structure of the ProductGroup and Products is in the SiteTree. These are a good
	 * starting point for anyone new to the e-commerce module.
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();

		// Check if there are any Product pages in the system before attempting this
		if(!DataObject::get_one('Product')) {
			// Create a ProductGroup page - it will be the top most level (ParentID is 0)		
			if(!DataObject::get_one('ProductGroup', 'ParentID = 0')) {
				$productgroupPageLvl1 = new ProductGroup();
				$productgroupPageLvl1->Title = 'Products';
				$productgroupPageLvl1->Content = "<p>This is the top level products page, it uses the <em>product group</em> page type, and it allows you to show your products checked as 'featured' on it. It also allows you to nest <em>product group</em> pages inside it.</p>
						<p>For example, you have a product group called 'DVDs', and inside you have more product groups like 'sci-fi', 'horrors' or 'action'.</p>
						<p>In this example we have setup a main product group (this page), with a nested product group containing 2 example products.</p>";	
				$productgroupPageLvl1->URLSegment = 'products';
				$productgroupPageLvl1->ParentID = 0;
				$productgroupPageLvl1->Status = 'Published';
				$productgroupPageLvl1->write();
				$productgroupPageLvl1->publish('Stage', 'Live');
				Database::alteration_message("ProductGroup (ParentID = 0) page created.","created");
				
			}
			
			// check if there is already a ProductGroup of ParentID 0 in the database
			if(DataObject::get_one('ProductGroup', 'ParentID = 0')) {
				$topLevelProductGroup = DataObject::get_one('ProductGroup', 'ParentID = 0');
			}
			
			// check if not there is already a ProductGroup of not ParentID 0 in the database
			if(!DataObject::get_one('ProductGroup', 'ParentID != 0')) {
			// Create a nested ProductGroup inside the top level (ParentID is the top level ProductGroup ID)
				$productgroupPageLvl2 = new ProductGroup();
				$productgroupPageLvl2->Title = 'Example product group';
				$productgroupPageLvl2->Content = '<p>This is a nested <em>product group</em> within the main <em>product group</em> page. You can add a paragraph here to describe what this product group is about, and what sort of products you can expect to find in it.</p>';
				$productgroupPageLvl2->URLSegment = 'example-product-group';
				if($topLevelProductGroup) {
					$productgroupPageLvl2->ParentID = $topLevelProductGroup->ID;
				} else {
					$productgroupPageLvl2->ParentID = $productgroupPageLvl1->ID;
				}
				$productgroupPageLvl2->Status = 'Published';
				$productgroupPageLvl2->write();
				$productgroupPageLvl2->publish('Stage', 'Live');
				
				Database::alteration_message("ProductGroup (ID = {$productgroupPageLvl2->ID}) page created.","created");

				// Create a child Product of our ProductGroup page nested inside another ProductGroup page
				// Create it as a featured product as an example of how the feature works
				$productPage = new Product();
				$productPage->Title = 'Example product';
				$productPage->Content = '<p>This is a <em>product</em>. It\'s description goes into the Content field as a standard SilverStripe page would have it\'s content. This is an ideal place to describe your product.</p>
												<p>You may also notice that we have checked it as a featured product and it will display on the main Products page.</p>';
				$productPage->URLSegment = 'example-product';
				$productPage->ParentID = $productgroupPageLvl2->ID;
				$productPage->Weight = '0.50';
				$productPage->Model = 'Joe Bloggs';
				$productPage->Price = '15.00';
				$productPage->AllowPurchase = 1;
				$productPage->FeaturedProduct = 1;
				$productPage->Status = 'Published';
				$productPage->write();
				$productPage->publish('Stage', 'Live');
				Database::alteration_message("Product (ID = {$productPage->ID}, ParentID = {$productgroupPageLvl2->ID}) page created.","created");
				
				// Create a child Product of our ProductGroup page nested inside another ProductGroup page
				$productPage2 = new Product();
				$productPage2->Title = 'Example product 2';
				$productPage2->Content = '<p>This is a <em>product</em>. It\'s description goes into the Content field as a standard SilverStripe page would have it\'s content. This is an ideal place to describe your product.</p>';
				$productPage2->URLSegment = 'example-product-2';
				$productPage2->ParentID = $productgroupPageLvl2->ID;
				$productPage2->Weight = '1.2';
				$productPage2->Model = 'Jane Bloggs';
				$productPage2->Price = '25.00';
				$productPage2->AllowPurchase = 1;
				$productPage2->Status = 'Published';
				$productPage2->write();
				$productPage2->publish('Stage', 'Live');
				Database::alteration_message("Product (ID = {$productPage2->ID}, ParentID = {$productgroupPageLvl2->ID}) page created.","created");		
			}			
		}		
		
		// Create a CheckoutPage page
		if(!DataObject::get_one('CheckoutPage')) {
			$checkoutPage = new CheckoutPage();
			$checkoutPage->Title = 'Checkout';
			$checkoutPage->Content = '<p>This is the checkout page. The order summary and order form appear below this content.</p>';
			$checkoutPage->PurchaseComplete = '<p>Your purchase is complete.</p>';
			$checkoutPage->ChequeMessage = '<p>Please note: Your goods will not be dispatched until we receive your payment.</p>';
			$checkoutPage->URLSegment = 'checkout';
			$checkoutPage->ShowInMenus = 0;
			$checkoutPage->Status = 'Published';
			$checkoutPage->write();
			$checkoutPage->publish('Stage', 'Live');
			Database::alteration_message("Checkout page created","created");
		}

		// Create an AccountPage page
		if(!DataObject::get_one('AccountPage')) {
			$accountPage = new AccountPage();
			$accountPage->Title = 'Account';
			$accountPage->Content = '<p>This is the account page. It is used for shop users to login and change their member details if they have an account.</p>';
			$accountPage->URLSegment = 'account';
			$accountPage->ShowInMenus = 0;
			$accountPage->Status = 'Published';
			$accountPage->write();
			$accountPage->publish('Stage', 'Live');
			Database::alteration_message("Account page created.","created");
		}
		
		// Create a shop terms and conditions page
		if(!DataObject::get_one('EcommerceTermsPage')) {
			$termsPage = new EcommerceTermsPage();
			$termsPage->Title = 'Terms and Conditions';
			$termsPage->Content = '<p>You can place your shop\'s terms and conditions here, if this page exists a checkbox will appear on the order form on' .
				' the checkout page, so a user has to confirm they agree to these terms and conditions here.</p>';
			$termsPage->URLSegment = 'terms-and-conditions';
			$termsPage->ShowInMenus = 0;
			$termsPage->Status = 'Published';
			$termsPage->write();
			$termsPage->publish('Stage', 'Live');
			Database::alteration_message("Terms and conditions page created.","created");
		}
		
		// If some orders with the old structure exist (hasShippingCost, Shipping and AddedTax columns presents in Order table), create the Order Modifiers SimpleShippingModifier and TaxModifier and associate them to the order
		$exist = DB::query("SHOW COLUMNS FROM `Order` LIKE 'Shipping'")->numRecords();
 		if($exist > 0) {
 			if($orders = DataObject::get('Order')) {
 				foreach($orders as $order) {
 					$id = $order->ID;
 					$hasShippingCost = DB::query("SELECT `hasShippingCost` FROM `Order` WHERE `ID` = '$id'")->value();
 					$shipping = DB::query("SELECT `Shipping` FROM `Order` WHERE `ID` = '$id'")->value();
 					$addedTax = DB::query("SELECT `AddedTax` FROM `Order` WHERE `ID` = '$id'")->value();
					$countryCode = $order->findShippingCountry(true);
					$country = Geoip::countryCode2name($countryCode);
 					if($hasShippingCost == '1' && $shipping != null) {
 						$simpleShippingModifier = new SimpleShippingModifier();
 						$simpleShippingModifier->Amount = $shipping < 0 ? abs($shipping) : $shipping;
 						$simpleShippingModifier->Type = 'Chargable';
 						$simpleShippingModifier->OrderID = $id;
 						$simpleShippingModifier->Country = $country;
 						$simpleShippingModifier->CountryCode = $countryCode;
 						$simpleShippingModifier->ShippingChargeType = 'Default';
 						$simpleShippingModifier->writeForStructureChanges();
 					}
 					if($addedTax != null) {
 						$taxModifier = new TaxModifier();
 						$taxModifier->Amount = $addedTax < 0 ? abs($addedTax) : $addedTax;
 						$taxModifier->Type = 'Chargable';
 						$taxModifier->OrderID = $id;
 						$taxModifier->Country = $country;
 						$taxModifier->Name = 'Undefined After Ecommerce Upgrade';
 						$taxModifier->TaxType = 'Exclusive';
 						$taxModifier->writeForStructureChanges();
 					}
 				}
 				echo( "<div style=\"padding:5px; color:white; background-color:blue;\">The 'SimpleShippingModifier' and 'TaxModifier' objects have been successfully created and linked to the appropriate orders present in the 'Order' table.</div>" );	
 			}
 			DB::query("ALTER TABLE `Order` CHANGE COLUMN `hasShippingCost` `_obsolete_hasShippingCost` tinyint(1)");
 			DB::query("ALTER TABLE `Order` CHANGE COLUMN `Shipping` `_obsolete_Shipping` decimal(9,2)");
 			DB::query("ALTER TABLE `Order` CHANGE COLUMN `AddedTax` `_obsolete_AddedTax` decimal(9,2)");
 			echo( "<div style=\"padding:5px; color:white; background-color:blue;\">The columns 'hasShippingCost', 'Shipping' and 'AddedTax' of the table 'Order' have been renamed successfully. Also, the columns have been renamed respectly to '_obsolete_hasShippingCost', '_obsolete_Shipping' and '_obsolete_AddedTax'.</div>" );
  		}
	}

	/**
	 * Complete orders content from checkout object
	 */
	function OrderContentSuccessful() {return DataObject::get_one('CheckoutPage')->PurchaseComplete;}
	
	/**
	 * Incomplete orders content from checkout object
	 */
	function OrderContentIncomplete() {return DataObject::get_one('CheckoutPage')->PurchaseIncomplete;}
	
}

/**
 * Our controller points us to the correct order information
 */
class Order_Controller extends Controller {
	
	static $URLSegment = 'order';
	
	static function showLink($id) {return self::$URLSegment . '/show/' . $id;}
	
	function show() {
		if($orderID = Director::urlParam('ID')) {
			if($memberID = Member::currentUserID()) return DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'");
			else {
				Session::setFormMessage('Login', 'You need to be logged in to view that page', 'warning');
				Director::redirect('Security/login/');
				return;
			}
		}
	}
	
	function DisplayOrder() {
		if($orderID = Director::urlParam('ID')) {
			if($memberID = Member::currentUserID()) return DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'");
			else return null;
		}
		else return ShoppingCart::current_order();
	}	
	
	/**
	 * Return the order ID
	 */
	/*function orderID() {
		$orderID = $this->urlParams["ID"];
		if(!$orderID) $orderID = Session::get('Order.OrderID');
		return $orderID;
	}*/
	
	/**
	 * Displays the order information  @where is this used ?
	 */
	function DisplayFinalisedOrder(){
		/*if($orderID = $this->orderID()){
			$member = Member();
			if($orderID && $member){
				$order = DataObject::get_one("Order", "`Order`.ID = $orderID && MemberID = $member->ID");
				return $order;
			}
		}*/
		if($orderID = Director::urlParam('ID') && $memberID = Member::currentUserID()) return DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'");
		else return null;
	}
	
	/**
	 * Check if the Member exists before displaying the order content,
	 * redirect them back to the Security section if not
	 */
	function OrderSuccessful() {
		if($member = Member::currentMember()) return array();
		else {
			Session::setFormMessage('Login', 'You need to be logged in to view that page', 'warning');
			Director::redirect('Security/login/');
			return;
		}
	}

}

/**
 * This class stores extra information about the order item,
 * such as colour, size, or type as defined in the Product
 * Attribute class
 */
class Order_Item_Attribute extends Product_Attribute{
	static $db = array(
		"AttributeTitle" => "Varchar(50)",
		"Type" => "Enum (array('Size','Colour','Subscription'),'Size')",
		"Quantity" => "Int",
		"UnitPrice" => "Currency",
		"OrderID" => "Int",
		"ProductID" => "Int",
	);
	static $has_one = array(
		"Order_Item" => "Order_Item", // Internal field becomes OrderID, not Order
		"Product_Atrribute" => "Product_Atrribute",
	);
}

/**
 * This class handles the receipt email which gets sent once an order is made.
 * You can call it by issuing Order::sendReceipt().
 */  
class Order_receiptEmail extends Email_Template {

	protected $ss_template = 'Order_receiptEmail';
	
	public function __construct($to = null, $from = null, $subject = null) {
		$this->to = $to ? $to : '$Member.Email';
		$this->from = $from;
		$this->subject = $subject ? $subject : 'Shop Sale Information (#$ID)';
		
		if(!isset($this->from, $this->subject)) {
			user_error('From or subject for email have not been defined. You probably haven\'t called Order::set_email() in your _config.php file.', E_USER_ERROR);
		}
		
		parent::__construct();
	}
}

/**
 * This class handles the status email which is sent after changing the attributes
 * in the report (eg. status changed to 'Shipped').
 */ 
class Order_statusEmail extends Email_Template {

	protected $ss_template = 'Order_statusEmail';

}

class Order_Attribute extends DataObject {
	
	protected $id;
	
	static $has_one = array(
		'Order' => 'Order'
	);
	
	public function __construct($object = null) {		
		
		// Case 1 : Constructed by the static function get of DataObject
		
		if(is_array($object)) parent::__construct($object);
		
		// Case 2 : Constructed in memory
		
		else parent::__construct();
	}
	
	public function getId() {return $this->id;}
	public function setId($id) {$this->id = $id;}
	
	function ClassForTable() {
		$class = get_class($this);
		$classes[] = strtolower($class);
		while(get_parent_class($class) != 'DataObject' && $class = get_parent_class($class)) $classes[] = strtolower($class);
		return implode(' ', $classes);
	}
	
	protected function MainID() {return get_class($this) . '_' . ($this->ID ? $this->ID : $this->id);}
	
	function IDForTable() {return 'Table_' . $this->MainID();}
	function IDForCart() {return 'Cart_' . $this->MainID();}
}

?>
