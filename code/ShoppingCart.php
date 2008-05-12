<?php

/**
 * @package ecommerce
 */
 
/**
 * The ShoppingCart class is a 'data handler' for the Order object.
 * It turns the order into a shopping cart.
 */
class ShoppingCart extends Object {
	
	//1) Main data used to store the products and modifiers in the session
	
	static $current_order = 'current_order';
		static $setting = 'setting';
			static $initialized = 'initialized';
			static $country = 'country';
			static $uses_different_address = 'uses_different_address';
		static $product = 'product';
		static $modifier = 'modifier';
	
	//2) Functions which return variable names stored in the session
	
	private static function setting_table_name() {return self::$current_order . '.' . self::$setting;}
	private static function setting_index($setting) {return self::setting_table_name() . '.' . $setting;}
		private static function initialized_setting_index() {return self::setting_index(self::$initialized);}
		private static function country_setting_index() {return self::setting_index(self::$country);}
		private static function uses_different_shipping_address_index() {return self::setting_index(self::$uses_different_address);}
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
	
	//3 Bis) Shipping management
	
	static function has_country() {
		$countrySettingIndex = self::country_setting_index();
		return Session::get($countrySettingIndex) != null;
	}
	
	static function set_country($country) {
		$countrySettingIndex = self::country_setting_index();
		Session::set($countrySettingIndex, $country);
	}
	
	static function get_country() {
		$countrySettingIndex = self::country_setting_index();
		return Session::get($countrySettingIndex);
	}
	
	static function remove_country() {
		$countrySettingIndex = self::country_setting_index();
		Session::clear($countrySettingIndex);
	}
	
	static function set_uses_different_shipping_address($usesDifferentAddress) {
		$usesDifferentShippingAddressIndex = self::uses_different_shipping_address_index();
		$usesDifferentAddress ? Session::set($usesDifferentShippingAddressIndex, true) : Session::clear($usesDifferentShippingAddressIndex);
	}
	
	static function uses_different_shipping_address() {
		$usesDifferentShippingAddressIndex = self::uses_different_shipping_address_index();
		return Session::get($usesDifferentShippingAddressIndex);
	}
	
	//4) Product management
	
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
	
	//5) Modifier management
	
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
	
	//6) Init function
	
	static function clear() {
		self::remove_all_settings();
		self::remove_all_products();
		self::remove_all_modifiers();
	}
	
	//7) Current order access function
	
	static function current_order() {
		 return Order::current_order();
	}
	
	//8) Database saving function
	
	static function save_current_order_to_database() {
		return Order::save_to_database();
  	}
  	
}
