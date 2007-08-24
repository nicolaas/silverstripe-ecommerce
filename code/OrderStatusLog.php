<?php

/**
 * @package ecommerce
 */
 
/**
 * Object to keep the status for an order
 */
class OrderStatusLog extends DataObject {
	
	static $db = array(
		"Status" => "Varchar(255)",
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