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
			Session::set('cartContents.'.$product->ID, $quantity);
			if(Session::get('cartContents.'.$product->ID) <= 0) {
				Session::clear('cartContents.'.$product->ID);
			} 
		} else { 			
			Session::clear('cartContents.'.$product->ID);
		}
	}
	
	/**
	 * Allows any extra fields stored to be returned from session
	 */
	function getField($order, $fieldName){
		// Get a default values from the order if there's nothing in the session
		$tmp = Session::get('cartDetails');
		if(!isset($tmp) || !is_array($tmp)) {
			Session::set('cartDetails', $order->toMap());
		}
		return Session::get('cartDetails.'.$fieldName);
	}
	
	/**
	 * Sets associated order information to be stored in session
	 */
	function setField($order, $fieldName, $fieldValue){
		Session::set('cartDetails.'.$fieldName, $fieldValue);
	}
	
	/**
	 * Stores order item information in session
	 */
	function items($order) {
		$tmp = Session::get('cartDetails');
		if(!isset($tmp) || !is_array($tmp)) {
			Session::set('cartContents',$order->items());
		}
		return Session::get('cartContents');
	}
	
	/**
	 * Returns the associated order information from session
	 */
	function getRecord($order) {
		if(!is_array(Session::get('cartDetails'))) 
			return array();
		else 
			return Session::get('cartDetails');
	}
	
	/**
	 * Adds a product ID to session
	 */
	function add($product) {
		$id = $product->ID;
		$this->items[$id]++;
		Session::set('cartContents.'.$id, Session::get('cartContents.'.$id)+1);
	}
	
	/**
	 * Removes the product from session
	 */
	function remove($product){
		$id = $product->ID;
		$this->items[$id]--;
		Session::set('cartContents.'.$id, Session::get('cartContents.'.$id)-1);
		if(Session::get('cartContents.'.$id)==0)
			Session::clear('cartContents.'.$id);
	}
	 
	/**
	 * Removes all the products from session
	 */
	function removeall($product){
		$id = $product->ID;
		Session::clear('cartContents.'.$id);
	}
	
	/**
	 * @TODO Same as above. Deprecated?
	 */
	function removeAllProducts(){
		Session::clear('cartContents');
	}
	
	/**
	 * Sets the order ID (for when you have saved a order)
	 */ 
	function setID($ID){
		Session::set('CartInfo.OrderID', $ID);
	}
	
	/**
	 * Returns the order ID
	 */
	function getID(){
		return Session::get('CartInfo.OrderID');
	}

	/**
	 * Returns the order error message
	 */
	function OrderError(){
		return Session::get('OrderError');
	}
	
	/**
	 * Returns the cart contents from session
	 */
	function sourceItems() {
		return Session::get('cartContents');
	}
	
	/**
	 * Returns information about the cart
	 */
	function getData() {
		return Session::get('cartInfo');
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
