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
	function MemberForm(){
		return new MemberForm($this, "MemberForm");
	}
	
	/**
	 * Returns all incomplete orders from this member
	 */
	function IncompleteOrders() {
		$memberID = Member::currentMember()->ID;		
		$completeStati = "'" . implode("','",singleton('Order')->completeStati) . "'";
		return DataObject::get("Order","`MemberID` = $memberID AND `Status` NOT IN ({$completeStati})","`Order`.`Created` DESC");
	}
	
	/**
	 * Returns all complete orders from this member
	 */
	function CompleteOrders() {
		$memberID = Member::currentMember()->ID;
		$completeStati = "'" . implode("','",singleton('Order')->completeStati) . "'";
		return DataObject::get("Order","`MemberID` = $memberID AND `Status` IN ({$completeStati})","`Order`.`Created` DESC");
	}
	
}

?>
