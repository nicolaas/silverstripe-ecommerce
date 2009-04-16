<?php
/**
 * Data class that keeps a log of a single
 * status of an order.
 * 
 * @package ecommerce
 */
class OrderStatusLog extends DataObject {
	
	static $db = array(
		'Status' => 'Varchar(255)',
		'Note' => 'Text',
		'SentToCustomer' => 'Boolean'
	);
	
	static $has_one = array(
		'Author' => 'Member',
		'Order' => 'Order'
	);
	
	function onBeforeSave() {
		if(!$this->ID) {
			$this->AuthorID = Member::currentUser()->ID;
		}
		
		parent::onBeforeSave();
	}
}
?>