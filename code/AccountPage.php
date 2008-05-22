<?php

/**
 * @package ecommerce
 */
 
/**
 * Account page to show order history and for the member
 * to edit their details
 */
class AccountPage extends Page {
	
	static $add_action = 'an Account Page';
	
}

class AccountPage_Controller extends Page_Controller {

	/**
	 * Include account page requirements - the stylesheet for one and
	 * Redirect to the login page if nobody is logged
	 */
	function init() {
		parent::init();
		Requirements::themedCSS('AccountPage');
		if(! Member::currentUser()) {
			$messages = array(
				'default' => '<p class="message good">' . _t('Message', 'You\'ll need to login before you can access the account page. If you are not registered, you won\'t be able to access it until you\'ll make your first order, otherwise please enter your details below.') . '</p>',
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
	 * Returns all complete orders from this member
	 */
	function CompleteOrders() {return $this->MemberOrders();}
	
	/**
	 * Returns all incomplete orders from this member
	 */
	function IncompleteOrders() {return $this->MemberOrders(false);}
	
	/**
	 * Returns the orders from this member
	 */
	protected function MemberOrders($complete = true) {
		$memberID = Member::currentUserID();
		$statusFilter = "`Status` "  . ($complete ? '' : 'NOT') . " IN ('" . implode("','", Order::$complete_status) . "')";
		return DataObject::get('Order', "`MemberID` = '$memberID' AND $statusFilter", "`Created` DESC");
	}
	
}

?>
