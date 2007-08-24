<?php

/**
 * @package ecommerce
 */
 
/**
 * The ShoppingCart class is a 'data handler' for the Order object.
 * It turns the order into a shopping cart.
 */
class ShoppingCart extends Object {
	
	/**
	 * Set the quantity of that given product in the shopping cart as $quantity.
	 * If the cart contents are less than 0, make sure it unsets the cart contents
	 * for this product instead of showing a negative quantity.
	 */
	function setQuantity($order, $product, $quantity){
		if($quantity) {
			$_SESSION['cartContents'][$product->ID] = $quantity;
			if($_SESSION['cartContents'][$product->ID] < 0) {
				unset($_SESSION['cartContents'][$product->ID]);
			} 
		} else { 
			unset($_SESSION['cartContents'][$product->ID]);
		}
	}
	
	/**
	 * Allows any extra fields stored to be returned from session
	 */
	function getField($order, $fieldName){
		// Get a default values from the order if there's nothing in the session
		if(!isset($_SESSION['cartDetails']) || !is_array($_SESSION['cartDetails'])) {
			$_SESSION['cartDetails'] = $order->toMap();
		}
		return $_SESSION['cartDetails'][$fieldName];
	}
	
	/**
	 * Sets associated order information to be stored in session
	 */
	function setField($order, $fieldName, $fieldValue){
		$_SESSION['cartDetails'][$fieldName] = $fieldValue;
	}
	
	/**
	 * Stores order item information in session
	 */
	function items($order) {
		if(!isset($_SESSION['cartDetails']) || !is_array($_SESSION['cartDetails'])) {
			$_SESSION['cartContents'] = $order->items();
		}
		return $_SESSION['cartContents'];
	}
	
	/**
	 * Returns the associated order information from session
	 */
	function getRecord($order) {
		if(!is_array($_SESSION['cartDetails'])) 
			return array();
		else 
			return $_SESSION['cartDetails'];
	}
	
	/**
	 * Adds a product ID to session
	 */
	function add($product) {
		$id = $product->ID;
		$this->items[$id]++;
		$_SESSION['cartContents'][$id]++;
	}
	
	/**
	 * Removes the product from session
	 */
	function remove($product){
		$id = $product->ID;
		$this->items[$id]--;
		$_SESSION['cartContents'][$id]--;
		if($_SESSION['cartContents'][$id]==0)
			unset($_SESSION['cartContents'][$id]);
		}
	 
	/**
	 * Removes all the products from session
	 */
	function removeall($product){
		$id = $product->ID;
		unset($_SESSION['cartContents'][$id]);
	}
	
	/**
	 * @TODO Same as above. Deprecated?
	 */
	function removeAllProducts(){
		unset($_SESSION['cartContents']);
	}
	
	/**
	 * Sets the order ID (for when you have saved a order)
	 */ 
	function setID($ID){
		$_SESSION['CartInfo']['OrderID'] = $ID;
	}
	
	/**
	 * Returns the order ID
	 */
	function getID(){
		return $_SESSION['CartInfo']['OrderID'];
	}

	/**
	 * Returns the order error message
	 */
	function OrderError(){
		return $_SESSION['OrderError'];
	}
	
	/**
	 * Returns the cart contents from session
	 */
	function sourceItems() {
		return $_SESSION['cartContents'];
	}
	
	/**
	 * Returns information about the cart
	 */
	function getData() {
		return $_SESSION['cartInfo'];
	}
	  
	/**
	 * Creates and saves the order with member data
	 */
	static function saveOrder(){
		$sc = new ShoppingCart();
		$order = new Order();
		
		$member = Member::currentUser();
		$order->MemberID = $member->ID;
	  	
		//$order->TotalOrderValue = $sc->Total();
		$sc->setID($order->write());	 	
		
		// can't be assigned until  order has an id
		foreach($sc->Items() as $item){
			$item->OrderID = $order->ID;
			$item->write();
		}		
		return $order;
  } 
	
}
