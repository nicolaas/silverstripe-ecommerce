<?php

/**
 * @package ecommerce
 */
 
/**
 * Checkout page shows the order details to make a checkout
 */
class CheckoutPage extends Page {
		
	static $db = array(
		'PurchaseComplete' => 'HTMLText',
		'ChequeMessage' => 'HTMLText'
	);
	
	static $add_action = 'a Checkout Page';
		
	/**
	 * Creates the fields for the checkout page within the CMS
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
	 * Returns the link or the URLSegment to the first checkout page on this site
	 * @param urlSegment : returns the URLSegment only if true
	 */
	static function find_link($urlSegment = false) {
		if(! $page = DataObject::get_one('CheckoutPage')) user_error(_t('CheckoutPage.NOPAGE', 'No CheckoutPage on this site - please create one !'), E_USER_ERROR);
		if($urlSegment) return $page->URLSegment;
		else return $page->Link();
	}
}

class CheckoutPage_Controller extends Page_Controller {
	
	/**
	 * Includes the checkout requirements, overrides if the project
	 * has the file, otherwise uses the module one instead
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
		
	/**
	 * Inits the virtual methods from the name of the modifier forms to
	 * redirect the action method to the form class
	 */
	protected function initVirtualMethods() {
		if($forms = $this->ModifierForms()) {
			foreach($forms as $form) $this->addWrapperMethod($form->Name(), 'getOrderModifierForm');
		}
	}
	
	/**
	 * Returns the form which name is equal to the parameter
	 * @param methodName : name of the virtual method called
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
	 * Checks if the user can checkout
	 */
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
	
	/**
	 * Returns either the current order from the shopping cart or
	 * if there is an ID in the url the order which has this ID if and only if
	 * the member logged has done this order and if this order is incomplete.
	 * Precondition : the user can checkout
	 */
	function Order() {
		if($orderID = Director::urlParam('ID')) return DataObject::get_by_id('Order', $orderID);
		else return ShoppingCart::current_order();
	}
	
	/**
	 * Returns a DataObjectSet which contains the forms to add some modifiers to update the OrderInformation table
	 * Precondition : the user can checkout
	 */
	function ModifierForms() {return Order::get_modifier_forms($this);}
	
	/**
	 * Returns the OrderForm object
	 * Precondition : the user can checkout
	 */
	function OrderForm() {return new OrderForm($this, 'OrderForm');}
	
	/**
	 * Returns the reason why the user can not checkout
	 * Precondition : the user can not checkout
	 */
	function Message() {
		$orderID = Director::urlParam('ID');
		if($memberID = Member::currentUserID()) {
			if($order = DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'")) return 'You can not checkout this order because it has been already successfully completed. Click <a href="' . $order->Link . '">here</a> to see its details, otherwise you can <a href="' . CheckoutPage::find_link() . '">checkout</a> your current order.';
			else return 'You do not have any order corresponding to this ID, so you can not checkout. However you can <a href="' . CheckoutPage::find_link() . '">checkout</a> your current order.';
		}
		else {
			$redirectLink = CheckoutPage::find_link() . "/$orderID";
			return 'You can not checkout this order because you are not logged. To do so, please <a href="Security/login?backURL=' . $redirectLink . '">login</a> first, otherwise you can <a href="' . CheckoutPage::find_link() . '">checkout</a> your current order.';
		}
	}
}


?>
