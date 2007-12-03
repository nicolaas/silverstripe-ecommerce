<?php

/**
 * @package ecommerce
 */
 
/**
 * Checkout has been changed to a databound controller, to use
 * seperate tabs and fields for messages.
 */
class CheckoutPage extends Page{
		
	static $db = array(
		"PurchaseComplete" => "Text",
		"ChequeMessage" => "Text"
	);
	
	static $add_action = 'a Checkout Page';

	public function Order(){
		if($member = Member::currentUser()){
		 return array();
		}else{			
		   Director::redirect('403/');	
			die();
		}
	}
	
	/**
	 * Create the fields for the checkout page within the CMS
	 */	
	function getCMSFields() {
		$fields = parent::getCMSFields();

		// information about these images		
		$shopmessagecomplete = '<p>This message is shown, along with order information after they submit the checkout:<p>';
		$shopchequemessage = '<p>This message is shown when a user selects cheque as a payment option on the checkout:</p>';

		// add the editable message fields
		$fields->addFieldToTab("Root.Content.Messages", new HeaderField("Checkout Messages",2));
		$fields->addFieldToTab("Root.Content.Messages", new LiteralField("shop", $shopmessagecomplete));
		$fields->addFieldToTab("Root.Content.Messages", new HtmlEditorField("PurchaseComplete", ""));
		$fields->addFieldToTab("Root.Content.Messages", new LiteralField("shop", $shopchequemessage));
		$fields->addFieldToTab("Root.Content.Messages", new HtmlEditorField("ChequeMessage", "", 5));

		return $fields;
	}

	
	/**
	 * Return a link to the checkout form on this site.
	 * Will look for the first CheckoutPage object in the database.
	 * Return a link in the form url-segment/
	 * @param - $urlSegment - returns a URLSegment only if set
	 */ 
	static function find_link($urlSegment = null) {
		$page = DataObject::get_one("CheckoutPage", "");
		if(!$page) {
			user_error("No CheckoutPage on this site - please create one!", E_USER_ERROR);
		}		
		if($urlSegment) {
			return $page->URLSegment;
		} else {
			return $page->Link();
		}
	}
}

class CheckoutPage_Controller extends Page_Controller{
	/**
	 * Include the checkout requirements, override if the project has the file,
	 * otherwise use the module one instead
	 */
	public function init() {
		// include stylesheet for the checkout page
		Requirements::themedCSS('CheckoutPage');

		$sc = Order::Shoppingcart();
		$country = Geoip::visitor_country();

		parent::init();
	}
	
	/** 
	 * Processes the order information from the Shopping cart, creates or merges
	 * the member from the database, and then processes the payment.
	 */
	function processOrder($data, $form) {
		// if the password and confirm password don't match, then return an error
		if($data['Password'] != $data['ConfirmPassword']) {
			$form->addErrorMessage('ConfirmPassword', 'The passwords do not match', 'bad');
			Director::redirectBack();
			exit;
		}
		
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
		
	}
	
	/**
	 * Complete orders content
	 */
	function OrderContentSuccessful() {
		return $this->PurchaseComplete;
	}
	
	/**
	 * Incomplete orders content
	 */	
	function OrderContentIncomplete() {
		return $this->PurchaseIncomplete;
	}
	
	/**
	 * Return the order payment information
	 */
	function OrderPaymentInfo(){
		$orderID = $this->orderID();
		
		$member = Member::currentUser();
		if($orderID && $member){
			if($payment = DataObject::get("Payment","`Payment`.OrderID = $orderID AND `Payment`.MemberID = $member->ID")) {
				$payment->LastEdited = date('d/m/Y',strtotime($payment->LastEdited));
			}
			return $payment;
		}
	}

	/**
	 * Return the order information 
	 */
	function DisplayOrder(){
		if($action = Director::urlParam("Action")){
			if($action == "OrderSuccessful" || $action = "OrderIncomplete"){
				
				// only remove all products if OrderSuccessful
				// @todo - is there a better way to do this?
				if($action == "OrderSuccessful") {
					singleton('ShoppingCart')->removeAllProducts();
				}
				$orderID = $this->orderID();

				$member = Member::currentMember();
				if($orderID && $member){
					$order = DataObject::get_one("Order", "`Order`.ID = $orderID AND MemberID = $member->ID");
				}
			}
		}else if($sc = Order::ShoppingCart()){
			$order = $sc;
		}
		return $order;
	}	
	
	/**
	 * Return the order ID
	 */
	function orderID() {
		$orderID = $this->urlParams["ID"];
		if(!$orderID) $orderID = Session::get('Order.OrderID');
		return $orderID;
	}
	
	/**
	 * Displays the order information  @where is this used ?
	 */
	function DisplayFinalisedOrder(){
		if($orderID = $this->orderID()){
			$member = Member();
			if($orderID && $member){
				$order = DataObject::get_one("Order", "`Order`.ID = $orderID && MemberID = $member->ID");
				return $order;
			}
		}		
	}
	
	/**
	 * Check if the Member exists before displaying the order content,
	 * redirect them back to the Security section if not
	 */
	function OrderSuccessful(){
		if($member = Member::currentMember()){
			return array();
		}else{
			Session::setFormMessage("Login","You need to be logged in to view that page","warning");
			Director::redirect("Security/Login/");
			return;
		}
	}
	

	/**
	 * Return the OrderForm object
	 */
	function OrderForm() {
		return new OrderForm($this, "OrderForm");
	}

	function ChangeCountry($data, $form) {
		$member = EcommerceRole::createOrMerge($data);
		$sc = Order::ShoppingCart();
		
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
	
	function ChangeCountry2($data, $form) {
		return $this->ChangeCountry($data, $form);
	}
	
	function ChangeCountryForm(){
		$member = Member::currentUser();
		$sc = Order::ShoppingCart();
		
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
		$sc = Order::ShoppingCart();
		
		$form->saveInto($sc);
		
		// if Order exists, recalculate shipping because country changed
		if($sc) {
			$sc->calcShipping();
		}

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
		$sc = Order::ShoppingCart();
		
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
		$sc = Order::ShoppingCart();
		
		$sc->UseShippingAddress = false;
		$sc->write();
		
		Director::redirectBack();
	}
}


?>
