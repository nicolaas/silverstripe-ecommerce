<?php

/**
 * @package ecommerce
 */
 
/**
 * Account page shows order history and a form to allow the member to edit its details
 */
class AccountPage extends Page {
	
	static $add_action = 'an Account Page';
	
	/**
	 * Returns the link or the URLSegment to the first account page on this site
	 * @param urlSegment : returns the URLSegment only if true
	 */
	static function find_link($urlSegment = false) {
		if(! $page = DataObject::get_one('AccountPage')) user_error(_t('AccountPage.NOPAGE', 'No AccountPage on this site - please create one !'), E_USER_ERROR);
		else return $urlSegment ? $page->URLSegment : $page->Link();
	}
	
	/**
	 * Returns the link or the URLSegment to the first account page on this site
	 * to get the details of the order which id is in parameter
	 * @param orderID : ID of the order
	 * @param urlSegment : returns the URLSegment only if true
	 */
	static function get_order_link($orderID, $urlSegment = false) {
		if(! $page = DataObject::get_one('AccountPage')) user_error(_t('AccountPage.NOPAGE', 'No AccountPage on this site - please create one !'), E_USER_ERROR);
		else return ($urlSegment ? $page->URLSegment . '/' : $page->Link()) . 'order/' . $orderID; 
	}
}

class AccountPage_Controller extends Page_Controller {

	/**
	 * Includes account page requirements - the stylesheet for one and
	 * Redirects to the login page if nobody is logged
	 */
	function init() {
		parent::init();
		Requirements::themedCSS('AccountPage');
		if(! Member::currentUser()) {
			$messages = array(
				'default' => '<p class="message good">' . _t('AccountPage.Message', 'You\'ll need to login before you can access the account page. If you are not registered, you won\'t be able to access it until you\'ll make your first order, otherwise please enter your details below.') . '</p>',
				'logInAgain' => 'You have been logged out. If you would like to log in again, please do so below.'
			);
			Security::permissionFailure($this, $messages);
			return;
		}
	}

	/**
	 * Returns the MemberForm object
	 */
	function MemberForm() {return new MemberForm($this, 'MemberForm');}
	
	/**
	 * Returns all the complete orders of this member
	 */
	function CompleteOrders() {return $this->MemberOrders();}
	
	/**
	 * Returns all the incomplete orders of this member
	 */
	function IncompleteOrders() {return $this->MemberOrders(false);}
	
	/**
	 * Returns the either complete or incomplete orders of this member
	 * @param complete : order status for filter
	 */
	protected function MemberOrders($complete = true) {
		$memberID = Member::currentUserID();
		$statusFilter = "`Status` "  . ($complete ? '' : 'NOT') . " IN ('" . implode("','", Order::$complete_status) . "')";
		return DataObject::get('Order', "`MemberID` = '$memberID' AND $statusFilter", "`Created` DESC");
	}
	
	/**
	 * Returns the order details of the order which id is in the url
	 * Precondition : a user is logged 
	 */
	function order() {
		Requirements::themedCSS('CheckoutPage');
		$memberID = Member::currentUserID();
		if($orderID = Director::urlParam('ID')) {
			if($order = DataObject::get_one('Order', "`Order`.`ID` = '$orderID' AND `MemberID` = '$memberID'")) return array('Order' => $order);
			else {
				return array(
					'Order' => false,
					'Message' => 'You do not have any order corresponding to this ID, so you can not see it. However you can <a href="' . AccountPage::find_link() . '">see you details and orders</a>.'
				);
			}
		}
		else {
			return array(
				'Order' => false,
				'Message' => 'There is no order ID specified, so you can not see this page. However you can <a href="' . AccountPage::find_link() . '">see you details and orders</a>.'
			);
		}
	}
}

?>
