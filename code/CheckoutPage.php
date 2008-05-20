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
	
	/*
	 * Return a DataObjectSet which contains the forms to add some modifiers to update the OrderInformation table
	 */
	function ModifierForms() {return Order::get_modifier_forms($this);}
	
	/**
	 * Return the OrderForm object
	 */
	function OrderForm() {return new OrderForm($this, 'OrderForm');}
		
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
