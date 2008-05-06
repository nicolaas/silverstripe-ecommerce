<?php

/**
 * @package ecommerce
 */
 
/**
 * Checkout has been changed to a databound controller, to use
 * seperate tabs and fields for messages.
 */
class CheckoutPage extends Page {
		
	static $db = array(
		'PurchaseComplete' => 'HTMLText',
		'ChequeMessage' => 'HTMLText'
	);
	
	static $add_action = 'a Checkout Page';

	/*public function Order(){
		if($member = Member::currentUser()){
		 return array();
		}else{			
		  	Director::redirect('403/');	
			return;
		}
	}*/
	
	/**
	 * Create the fields for the checkout page within the CMS
	 */	
	function getCMSFields() {
		$fields = parent::getCMSFields();

		// information about these images		
		$shopMessageComplete = '<p>This message is shown, along with order information after they submit the checkout :<p>';
		$shopChequeMessage = '<p>This message is shown when a user selects cheque as a payment option on the checkout :</p>';

		// add the editable message fields
		$fields->addFieldToTab('Root.Content.Messages', new HeaderField('Checkout Messages', 2));
		$fields->addFieldToTab('Root.Content.Messages', new LiteralField('ShopMessageComplete', $shopMessageComplete));
		$fields->addFieldToTab('Root.Content.Messages', new HtmlEditorField('PurchaseComplete', ''));
		$fields->addFieldToTab('Root.Content.Messages', new LiteralField('ShopChequeMessage', $shopChequeMessage));
		$fields->addFieldToTab('Root.Content.Messages', new HtmlEditorField('ChequeMessage', '', 5));

		return $fields;
	}

	
	/**
	 * Return a link to the checkout form on this site.
	 * Will look for the first CheckoutPage object in the database.
	 * Return a link in the form url-segment/
	 * @param - $urlSegment - returns a URLSegment only if set
	 */ 
	static function find_link($urlSegment = null) {
		if(! $page = DataObject::get_one('CheckoutPage')) user_error(_t('CheckoutPage.NOPAGE', 'No CheckoutPage on this site - please create one !'), E_USER_ERROR);
		if($urlSegment) return $page->URLSegment;
		else return $page->Link();
	}
}

class CheckoutPage_Controller extends Page_Controller {
	
	/**
	 * Include the checkout requirements, override if the project has the file,
	 * otherwise use the module one instead
	 */
	public function init() {
		// include extra js requirements for this page
		Requirements::javascript('jsparty/behaviour.js');
		Requirements::javascript('ecommerce/javascript/CheckoutPage.js');
		Requirements::javascript('ecommerce/javascript/AjaxQuantity.js');
		
		// include stylesheet for the checkout page
		Requirements::themedCSS('CheckoutPage');
		
		// init the virtual methods for the order modifiers
		$this->initVirtualMethods();

		parent::init();
	}
	
	/*
	 * Inits the virtual methods from the name of the modifier forms to redirect the action method to the form class
	 */
	protected function initVirtualMethods() {
		if($forms = $this->ModifierForms()) {
			foreach($forms as $form) $this->addWrapperMethod($form->Name(), 'getOrderModifierForm');
		}
	}
	
	/*
	 * Returns the form which name is 'methodname'
	 * @param methodname : name of the virtual method called
	 */
	protected function getOrderModifierForm($methodName) {
		// loops for all modifier forms, finds form named $methodName and returns it
		if($forms = $this->ModifierForms()) {
			foreach($forms as $form) {
				if($form->Name() == $methodName) return $form;
			}
		}
	}
	
	/** 
	 * Processes the order information from the Shopping cart, creates or merges
	 * the member from the database, and then processes the payment.
	 */
	/*function processOrder($data, $form) {
		$sc = Order::ShoppingCart();
		
		// Check to see if there are still items in the shopping cart
		if($sc->Items()){
			$cartContents = Session::get('cartContents');
			$member = EcommerceRole::createOrMerge($data);
			$member->write();
			$member->logIn();
			
			// Get, and save the order from session.
			$order = $sc->createOrderFromShoppingCart();
			// Update order with shipping address
			$form->saveInto($order);
			
			$order->write();
			
			$data['BillingId'] = $order->ID;
			
			// Save payment data from form and process payment
			
			
			$payment = Object::create($data['PaymentMethod']);
			if(!$payment instanceof Payment) user_error(get_class($payment) ." is not a Payment object!", E_USER_ERROR);
			$form->saveInto($payment);
			$payment->OrderID = $order->ID;
			$payment->Amount = $order->Total();
			
			// Worldpay doesn't have a payment object so we write one here
			if($data['PaymentMethod'] == 'WorldpayPayment') {
				$payment->write();
			}			
			
			$result = $payment->processPayment($data, $form);
							
			// Successful payment
			if($result['Success']) {
			  	Session::set('Order.OrderID',$order->ID);
				Session::set('Order.PurchaseComplete', true);
				
				$order->sendReceipt();
				$order->isComplete();
				$order->write();
				
				Director::redirect(CheckoutPage::find_link() . "OrderSuccessful/$order->ID");
				return;
				
			// Longer payment process, such as Worldpay
			} else if($result['Processing']) {
				return $result['ReturnValue'];
	 		
			// Failed payment
			} else {
				Session::set('cartContents',$cartContents);
				Session::set('Order.OrderID', $order->ID);
				Session::clear('Order.PurchaseComplete');
				$form->sessionMessage("Sorry, your payment was not accepted, please try again<br/><strong>$result[HelpText]:</strong> $result[MerchantHelpText]","bad");
	 			Director::redirect(CheckoutPage::find_link() . "$order->ID");
	 			return;
			}
		
		} else {
			// no items, redirect back
			$form->sessionMessage("Please add some items to your cart","warning");
		   	Director::redirectBack();
		   	return;
		}
		
	}*/
	
	/** 
	 * Processes the order information from the Shopping cart, creates or merges
	 * the member from the database, and then processes the payment.
	 * This function concerns only the current order
	 */
	function processOrder($data, $form) {
		//Check to see if there are still items in the current order
		if(CurrentOrder::has_products()) {
			//$cartContents = Session::get('cartContents');
			$member = EcommerceRole::createOrMerge($data);
			$member->write();
			$member->logIn();
			
			// Save the current order from session as an Order object and return it
			$order = CurrentOrder::save_to_database();
			// Update order with shipping address
			$form->saveInto($order);
			
			$order->write();
			
			$data['BillingId'] = $order->ID;
			
			// Save payment data from form and process payment
						
			$payment = Object::create($data['PaymentMethod']);
			if(!$payment instanceof Payment) user_error(get_class($payment) ." is not a Payment object!", E_USER_ERROR);
			$form->saveInto($payment);
			$payment->OrderID = $order->ID;
			$payment->Amount = $order->Total();
			
			// Worldpay doesn't have a payment object so we write one here
			if($data['PaymentMethod'] == 'WorldpayPayment') {
				$payment->write();
			}
			
			$result = $payment->processPayment($data, $form);
			
			// Successful payment
			if($result['Success']) {
			  	//Session::set('Order.OrderID',$order->ID);
				//Session::set('Order.PurchaseComplete', true);
				
				$order->sendReceipt();
				//$order->isComplete();
				$order->write();
				
				CurrentOrder::clear();
				
				Director::redirect(CheckoutPage::find_link() . "OrderSuccessful/$order->ID");
				return;
				
			// Longer payment process, such as Worldpay
			} else if($result['Processing']) {
				return $result['ReturnValue'];
	 		
			// Failed payment
			} else {
				//Session::set('cartContents',$cartContents);
				//Session::set('Order.OrderID', $order->ID);
				//Session::clear('Order.PurchaseComplete');
				$form->sessionMessage("Sorry, your payment was not accepted, please try again<br/><strong>$result[HelpText]:</strong> $result[MerchantHelpText]","bad");
	 			Director::redirect(CheckoutPage::find_link() . "$order->ID");
	 			return;
			}
		
		} else {
			// no items, redirect back
			$form->sessionMessage("Please add some items to your cart","warning");
		   	Director::redirectBack();
		   	return;
		}
		
	}
	
	/**
	 * Complete orders content
	 */
	/*function OrderContentSuccessful() {
		return $this->PurchaseComplete;
	}*/
	
	/**
	 * Incomplete orders content
	 */	
	/*function OrderContentIncomplete() {
		return $this->PurchaseIncomplete;
	}*/
	
	/**
	 * Return the order payment information
	 * This function is not used
	 */
	/*function OrderPaymentInfo(){
		$orderID = $this->orderID();
		
		$member = Member::currentUser();
		if($orderID && $member){
			if($payment = DataObject::get("Payment","`Payment`.OrderID = $orderID AND `Payment`.MemberID = $member->ID")) {
				$payment->LastEdited = date('d/m/Y',strtotime($payment->LastEdited));
			}
			return $payment;
		}
	}*/
		
	function DisplayOrder() {
		if($orderID = Director::urlParam('ID')) {
			if($memberID = Member::currentUserID()) return DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'");
			else return null;
		}
		else return CurrentOrder::display_order();
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
	
	/*
	 * Return a DataObjectSet which contains the forms to add some modifiers to update the OrderInformation table
	 */
	function ModifierForms() {return Order::ModifierForms($this);}
	
	/**
	 * Return the OrderForm object
	 */
	function OrderForm() {return new OrderForm($this, 'OrderForm');}

	function ChangeCountry($data, $form) {
		$member = EcommerceRole::createOrMerge($data);
		//$sc = Order::ShoppingCart();
		$sc = CurrentOrder::display_order();
		
		if($member) {
			$form->saveInto($member);
			$member->write();
			$member->login();
		}
		
		// Serialize the order data if it exists
		// unset some data first because it shouldn't be serialized
		unset($data['ShippingCountry']);
		unset($data['Country']);
		unset($data['Amount']);
		unset($data['CreditCardNumber']);
		unset($data['DateExpiry']);
		unset($data['ReadConditions']);
		$serialized_data = serialize($data);
		Session::set("MemberOrderData", $serialized_data);
		
		if($sc) {
			$form->saveInto($sc);
		}

		return array(
			'OrderForm' => $this->ChangeCountryForm()
		);
	}
	
	function setCountry() {
		if(isset($_REQUEST['country']) && $country = $_REQUEST['country']) {
			CurrentOrder::set_country($country);
			$sc = CurrentOrder::display_order();
			
			$js = array();
						
			$grand_total = '$' . number_format($sc->_Total(), 2) . " " . $sc->Currency();
			$js['GrandTotal'] = $grand_total;
			$js['OrderForm_OrderForm_Amount'] = $grand_total;
			$js['Cart_GrandTotal'] = $grand_total;
			
			if($modifiers = $sc->Modifiers()) {
				foreach($modifiers as $modifier) $modifier->updateJavascript($js);
			}
			
			return Product::javascript_for_new_values($js);
		}
		else user_error("Bad data to CheckoutPage->setCountry: country=" . $_REQUEST['country'], E_USER_WARNING);
	}
	
	function ChangeCountry2($data, $form) {
		return $this->ChangeCountry($data, $form);
	}
	
	function ChangeCountryForm(){
		$member = Member::currentUser();
		//$sc = Order::ShoppingCart();
		$sc = CurrentOrder::display_order();
		
		if($sc->ShippingCountry) {
			$shipCountry = $sc->ShippingCountry;
		} else {
			$shipCountry = EcommerceRole::findCountry();
		}

		$fields = new FieldSet(
			new DropdownField("Country", "Country", Geoip::getCountryDropDown(), Geoip::visitor_country()),
			new DropdownField("ShippingCountry", "Shipping Country", Geoip::getCountryDropDown(), $shipCountry),
			new CheckboxField("UseShippingAddress", "I will send this order to a different address other than my own.")
		);		
		
		$actions = new FieldSet(
			new FormAction("updateCountry", "Save Country")
		);
		
		$form = new Form($this, "ChangeCountryForm", $fields, $actions);
		
		if($member) {
			$form->loadDataFrom($member);
		}
		
		return $form;
	}

	/**
	 * Updates the Country
	 */
	function updateCountry($data, $form){
		$member = Member::currentUser();	
		//$sc = Order::ShoppingCart();
		$sc = CurrentOrder::display_order();
		
		$form->saveInto($sc);
		
		if($member) {
			$form->saveInto($member);
			$member->write();
			$member->login();	
		} else {
			$fields = $form->Fields();
			$country = $fields->fieldByName("Country")->Value();
			Director::redirect($this->Link() . "?country=$country");
		}
		
		Director::redirect($this->Link());
	}
	
		
	function useDifferentShippingAddress($data, $form) {
		$member = EcommerceRole::createOrMerge($data);
		//$sc = Order::ShoppingCart();
		$sc = CurrentOrder::display_order();
		if($member) {
			$form->saveInto($member);
			$member->write();
			$member->login();
		}
		
		$sc->UseShippingAddress = true;
		$sc->write();
		
		Director::redirectBack();
	}
	
	function useBillingAddress($data, $form) {
		$member = EcommerceRole::createOrMerge($data);
		//$sc = Order::ShoppingCart();
		$sc = CurrentOrder::display_order();
		
		$sc->UseShippingAddress = false;
		$sc->write();
		
		Director::redirectBack();
	}
}


?>
