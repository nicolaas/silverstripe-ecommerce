<?php

/**
 * @package ecommerce
 */
 
/**
 * Class which implement a log object to keep a history of the status changes
 * of an order
 */
class OrderStatusLog extends DataObject {
	
	static $db = array(
		'Status' => 'Varchar(255)',
		"Note" => "Text",
		"SentToCustomer" => "Boolean"
	);
	
	static $has_one = array(
		"Author" => "Member",
		"Order" => "Order"
	);
	
	function onBeforeSave() {
		if(!$this->ID) {
			$this->AuthorID = Member::currentUser()->ID;
		}
		
		parent::onBeforeSave();
	}
}

?>