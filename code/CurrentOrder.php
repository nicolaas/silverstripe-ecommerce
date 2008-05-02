<?php

/**
 * @package ecommerce
 */
 
/**
 * The ShoppingCart class is a 'data handler' for the Order object.
 * It turns the order into a shopping cart.
 */
class CurrentOrder extends Object {
	
	//1) Main data used to store the products and modifiers in the session
	
	static $current_order = 'current_order';
	static $setting = 'setting';
	static $initialized = 'initialized';
	static $product = 'product';
	static $modifier = 'modifier';
	
	//2) Functions which return variable names stored in the session
	
	private static function setting_table_name() {return self::$current_order . '.' . self::$setting;}
	private static function setting_index($setting) {return self::setting_table_name() . '.' . $setting;}
	
	private static function initialized_setting_index() {return self::setting_index(self::$initialized);}
		
	private static function product_table_name() {return self::$current_order . '.' . self::$product;}
	private static function product_index(Product $product) {return self::product_table_name() . '.' . $product->ID;}
	
	private static function modifier_table_name() {return self::$current_order . '.' . self::$modifier;}
	
	//3) Initialisation management
	
	static function is_initialized() {
		$initializedSettingIndex = self::initialized_setting_index();
		return Session::get($initializedSettingIndex);
	}
	
	static function set_initialized($initialized) {
		$initializedSettingIndex = self::initialized_setting_index();
		$initialized ? Session::set($initializedSettingIndex, true) : Session::clear($initializedSettingIndex);
	}
	
	static function remove_all_settings() {
		$settingTableIndex = self::setting_table_name();
		Session::clear($settingTableIndex);
	}
	
	//3) Product management
	
	static function add_product(Product $product) {
		$productIndex = self::product_index($product);
		$newQuantity = Session::get($productIndex) + 1;
		Session::set($productIndex, $newQuantity);
	}
	
	static function set_product_quantity(Product $product, $quantity) {
		$productIndex = self::product_index($product);
		Session::set($productIndex, $quantity);
	}
	
	static function remove_product(Product $product) {
		$productIndex = self::product_index($product);
		$newQuantity = Session::get($productIndex) - 1;
		if($newQuantity > 0)
			Session::set($productIndex, $newQuantity);
		else
			Session::clear($productIndex);
	}
	
	static function remove_all_product(Product $product) {
		$productIndex = self::product_index($product);
		Session::clear($productIndex);
	}
	
	static function remove_all_products() {
		$productTableIndex = self::product_table_name();
		Session::clear($productTableIndex);
	}
	
	static function has_products() {
		$productTableIndex = self::product_table_name();
		return Session::get($productTableIndex) != null;
	}
		
	static function get_products() {
		$productTableIndex = self::product_table_name();
		return Session::get($productTableIndex);
	}
	
	//4) Modifier management
	
	static function add_modifier(OrderModifier $modifier) {
		$modifierTableIndex = self::modifier_table_name();
		Session::addToArray($modifierTableIndex, serialize($modifier));
	}
	
	static function init_all_modifiers() {
		Order::init_all_modifiers();
	}
	
	static function remove_modifier(OrderModifier $modifier) {
		$modifierTableIndex = self::modifier_table_name();
		$modifierTable = Session::get($modifierTableIndex);
		self::remove_all_modifiers();
		foreach($modifierTable as $serializeModifier) {
			if(unserialize($serializeModifier) !== $modifier)
				Session::addToArray($modifierTableIndex, $serializeModifier);
		}
		$modifierTable = Session::get($modifierTableIndex);
		if(count($modifierTable) == 0)
			Session::clear($modifierTableIndex);
	}
		
	static function remove_all_modifiers() {
		$modifierTableIndex = self::modifier_table_name();
		Session::clear($modifierTableIndex);
	}
	
	static function has_modifiers() {
		$modifierTableIndex = self::modifier_table_name();
		return Session::get($modifierTableIndex) != null;
	}
		
	static function get_modifiers() {
		if(! self::is_initialized()) {
			self::init_all_modifiers();
			self::set_initialized(true);
		}
		$modifierTableIndex = self::modifier_table_name();
		if($serializeModifiers = Session::get($modifierTableIndex)) {
			$modifiers = array();
			foreach($serializeModifiers as $serializeModifier) array_push($modifiers, unserialize($serializeModifier));
			return $modifiers;
		}
		return null;
	}
	
	//5) Init function
	
	static function clear() {
		self::remove_all_settings();
		self::remove_all_products();
		self::remove_all_modifiers();
	}
	
	//6) Display Function
	
	static function display_order() {
		 return Order::display_order();
	}
	
	//7) Database saving function
	
	static function save_to_database() {
		return Order::save_to_database();
  	} 
	
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
			Session::set('cartContents',$order->Items());
		}
		return Session::get('cartContents');
	}
	
	/**
	 * Stores order modifiers information in session and returns them
	 * 
	 * @param Order $order : the order containing the modifiers
	 * @return the modifiers in a DataObjectSet
	 */
	function modifiers($order) {
		$tmp = Session::get('cartDetails');
		if(! isset($tmp) || ! is_array($tmp)) {
			if($modifiers = $order->Modifiers()) {
				foreach($modifiers as $modifier) $this->addModifier($order, $modifier);
			}
		}
		if($cartModifiers = Session::get('cartModifiers')) {
			$modifiers = new DataObjectSet();
			foreach($cartModifiers as $cartModifier) $modifiers->push(unserialize($cartModifier));
			return $modifiers;
		}
		return null;
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
