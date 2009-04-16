<?php
/**
 * Checkout page shows the order details to make a checkout
 * 
 * @package ecommerce
 */
class CheckoutPage extends Page {
		
	static $db = array(
		'PurchaseComplete' => 'HTMLText',
		'ChequeMessage' => 'HTMLText'
	);
	
	static $has_one = array(
		'TermsPage' => 'Page'
	);
	
	static $add_action = 'a Checkout Page';
	
	/**
	 * Returns the link or the URLSegment to the first checkout page on this site
	 * @param urlSegment : returns the URLSegment only if true
	 */
	static function find_link($urlSegment = false) {
		$page = DataObject::get_one('CheckoutPage');
		if(!$page) throw new Exception(_t('CheckoutPage.NOPAGE', 'No CheckoutPage on this site - please create one!'));
		else return $urlSegment ? $page->URLSegment : $page->Link();
	}
	
	/**
	 * Returns the link or the URLSegment to the first checkout page on this site
	 * to checkout the order which id is under the Action parameter
	 * @param orderID : ID of the order
	 * @param urlSegment : returns the URLSegment only if true
	 */
	static function get_checkout_order_link($orderID, $urlSegment = false) {
		$page = DataObject::get_one('CheckoutPage');
		if(!$page) throw new Exception(_t('CheckoutPage.NOPAGE', 'No CheckoutPage on this site - please create one!'));
		else return ($urlSegment ? $page->URLSegment . '/' : $page->Link()) . $orderID; 
	}
	
	/**
	 * Creates the fields for the checkout page within the CMS
	 */	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Content.Main', new TreeDropdownField('TermsPageID', 'Terms and Conditions Page', 'SiteTree'));
		
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
	 * Creates automatically a checkout page when the ecommerce module is
	 * added to a project and transfers the EcommerceTermsPage table to Page
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		if(! $page = DataObject::get_one('CheckoutPage')) {
			$page = new CheckoutPage();
			$page->Title = 'Checkout';
			$page->Content = '<p>This is the checkout page. The order summary and order form appear below this content.</p>';
			$page->PurchaseComplete = '<p>Your purchase is complete.</p>';
			$page->ChequeMessage = '<p>Please note: Your goods will not be dispatched until we receive your payment.</p>';
			$page->URLSegment = 'checkout';
			$page->ShowInMenus = 0;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			Database::alteration_message('Checkout page \'Checkout\' created', 'created');
		}
		
		if($page->TermsPageID == 0 && $termsPage = DataObject::get_one('Page', "`URLSegment` = 'terms-and-conditions'")) {
			$page->TermsPageID = $termsPage->ID;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			Database::alteration_message("Page '{$termsPage->Title}' linked to the Checkout page '{$page->Title}'", 'changed');
		}
 	}
}

class CheckoutPage_Controller extends Page_Controller {
	
	/**
	 * Includes the checkout requirements, overrides if the project
	 * has the file, otherwise uses the module one instead
	 */
	public function init() {
		if(!class_exists('Payment')) {
			trigger_error('The payment module must be installed for the ecommerce module to function.', E_USER_WARNING);
		}
		
		// include extra js requirements for this page
		Requirements::javascript('jsparty/behaviour.js');
		Requirements::javascript('ecommerce/javascript/CheckoutPage.js');
		
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
		if($orderID = Director::urlParam('Action')) {
			if($memberID = Member::currentUserID()) {
				if($order = DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'")) return ! $order->IsPaid();
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
		if($orderID = Director::urlParam('Action')) return DataObject::get_by_id('Order', $orderID);
		else return ShoppingCart::current_order();
	}
	
	/**
	 * Returns a DataObjectSet which contains the forms to add some modifiers to update the OrderInformation table
	 * Precondition : the user can checkout
	 */
	function ModifierForms() {
		return Order::get_modifier_forms($this);
	}
	
	/**
	 * Returns the OrderForm object
	 * Precondition : the user can checkout
	 */
	function OrderForm() {
		return new OrderForm($this, 'OrderForm');
	}
	
	/**
	 * Returns the reason why the user can not checkout
	 * Precondition : the user can not checkout
	 */
	function Message() {
		$orderID = Director::urlParam('Action');
		if($memberID = Member::currentUserID()) {
			if($order = DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'")) return 'You can not checkout this order because it has been already successfully completed. Click <a href="' . $order->Link() . '">here</a> to see its details, otherwise you can <a href="' . CheckoutPage::find_link() . '">checkout</a> your current order.';
			else return 'You do not have any order corresponding to this ID, so you can not checkout this order. However you can <a href="' . CheckoutPage::find_link() . '">checkout</a> your current order.';
		}
		else {
			$redirectLink = CheckoutPage::get_checkout_order_link($orderID);
			return 'You can not checkout this order because you are not logged. To do so, please <a href="Security/login?BackURL=' . $redirectLink . '">login</a> first, otherwise you can <a href="' . CheckoutPage::find_link() . '">checkout</a> your current order.';
		}
	}
}
?>