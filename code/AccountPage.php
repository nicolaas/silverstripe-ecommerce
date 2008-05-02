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
	 * Include account page requirements - the stylesheet for one.
	 */
	function init() {
		parent::init();
		Requirements::themedCSS('AccountPage');
	}

	/**
	 * Returns the MemberForm object
	 */
	function MemberForm() {return new MemberForm($this, 'MemberForm');}
	
	/**
	 * Returns all complete orders from this member
	 */
	function CompleteOrders() {return $this->MemberOrders(true);}
	
	/**
	 * Returns all incomplete orders from this member
	 */
	function IncompleteOrders() {return $this->MemberOrders(false);}
		
	function MemberOrders($complete) {
		$memberID = Member::currentUserID();		
		$statusFilter = "`Status` "  . ($complete ? '' : 'NOT') . " IN ('" . implode("','", Order::$complete_status) . "')";
		return DataObject::get('Order', "`MemberID` = '$memberID' AND $statusFilter", "`Created` DESC");
	}
	
}

?>
