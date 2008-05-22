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
		
	/**
	 * Create the fields for the checkout page within the CMS
	 */	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		// Information about the messages		
		$shopMessageComplete = '<p>This message is shown, along with order information after they submit the checkout :<p>';
		$shopChequeMessage = '<p>This message is shown when a user selects cheque as a payment option on the checkout :</p>';

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
	
	function CanCheckout() {
		if($orderID = Director::urlParam('ID')) {
			if($memberID = Member::currentUserID()) {
				if($order = DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'")) return ! $order->IsComplete();
				else return false;
			}
			else return false;
		}
		else return true;
	}
	
	/*
	 * Return either the current order from the shopping cart or
	 * if there is an ID in the url the order which has this ID if and only if
	 * the member logged has done this order and if this order is incomplete.
	 * Precondition : the user can checkout
	 */
	function Order() {
		if($orderID = Director::urlParam('ID')) return DataObject::get_by_id('Order', $orderID);
		else return ShoppingCart::current_order();
	}
	
	/*
	 * Return a DataObjectSet which contains the forms to add some modifiers to update the OrderInformation table
	 * Precondition : the user can checkout
	 */
	function ModifierForms() {return Order::get_modifier_forms($this);}
	
	/*
	 * Return the OrderForm object
	 * Precondition : the user can checkout
	 */
	function OrderForm() {return new OrderForm($this, 'OrderForm');}
	
	/*
	 * Return the reason why the user has not been able to checkout
	 * Precondition : the user can not checkout
	 */
	function Message() {
		$orderID = Director::urlParam('ID');
		if($memberID = Member::currentUserID()) {
			if($order = DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'")) return 'You can not checkout this order because it has been already successfully completed. Click <a href="' . $order->Link . '">here</a> to see its details, otherwise you can <a href="' . CheckoutPage::find_link() . '">checkout</a> your current order.';
			else return 'You do not have any order corresponding to this ID, so you can not checkout. However you can <a href="' . CheckoutPage::find_link() . '">checkout</a> your current order.';
		}
		else {
			$messages = array(
				'default' => '<p class="message good">' . _t('Message', 'You\'ll need to login before you can checkout this order. If you are not registered, you can not checkout this order anyway, otherwise please enter your details below.') . '</p>',
				'logInAgain' => 'You have been logged out. If you would like to log in again, please do so below.'
			);
			Security::permissionFailure($this, $messages);
			return;
		}
	}
			
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
		
	function setCountry() {
		if(isset($_REQUEST['country']) && $country = $_REQUEST['country']) {
			ShoppingCart::set_country($country);
			$currentOrder = ShoppingCart::current_order();
			
			$js = array();
			
			$grand_total = '$' . number_format($currentOrder->_Total(), 2) . " " . $currentOrder->Currency();
			$js['GrandTotal'] = $grand_total;
			$js['OrderForm_OrderForm_Amount'] = $grand_total;
			$js['Cart_GrandTotal'] = $grand_total;
			
			if($modifiers = $currentOrder->Modifiers()) {
				foreach($modifiers as $modifier) $modifier->updateJavascript($js);
			}
			
			return Product::javascript_for_new_values($js);
		}
		else user_error("Bad data to CheckoutPage->setCountry: country=" . $_REQUEST['country'], E_USER_WARNING);
	}
}


?>
