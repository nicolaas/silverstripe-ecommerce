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
			static $country = 'country';
			static $different_address = 'different_address';
				static $uses = 'uses';
				static $name = 'name';
				static $address = 'address';
				static $address2 = 'address2';
				static $city = 'city';
		static $product = 'product';
		static $modifier = 'modifier';
	
	//2) Functions which return variable names stored in the session
	
	private static function setting_table_name() {return self::$current_order . '.' . self::$setting;}
	private static function setting_index($setting) {return self::setting_table_name() . '.' . $setting;}
		private static function initialized_setting_index() {return self::setting_index(self::$initialized);}
		private static function country_setting_index() {return self::setting_index(self::$country);}
		private static function different_address_setting_table_name() {return self::setting_table_name() . '.' . self::$different_address;}
		private static function different_address_setting_index($address_setting) {return self::different_address_setting_table_name() . '.' . $address_setting;}
			private static function uses_different_shipping_address_index() {return self::different_address_setting_index(self::$uses);}
			private static function name_different_shipping_address_index() {return self::different_address_setting_index(self::$name);}
			private static function address_different_shipping_address_index() {return self::different_address_setting_index(self::$address);}
			private static function address2_different_shipping_address_index() {return self::different_address_setting_index(self::$address2);}
			private static function city_different_shipping_address_index() {return self::different_address_setting_index(self::$city);}
			private static function country_different_shipping_address_index() {return self::different_address_setting_index(self::$country);}
			
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
	
	//3) Shipping management
	
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
	
	static function set_name_different_shipping_address($nameDifferentAddress) {
		$nameDifferentShippingAddressIndex = self::name_different_shipping_address_index();
		$nameDifferentAddress ? Session::set($nameDifferentShippingAddressIndex, $nameDifferentAddress) : Session::clear($nameDifferentShippingAddressIndex);
	}
	
	static function get_name_different_shipping_address() {
		$nameDifferentShippingAddressIndex = self::name_different_shipping_address_index();
		return Session::get($nameDifferentShippingAddressIndex);
	}
	
	static function set_address_different_shipping_address($addressDifferentAddress) {
		$addressDifferentShippingAddressIndex = self::address_different_shipping_address_index();
		$addressDifferentAddress ? Session::set($addressDifferentShippingAddressIndex, $addressDifferentAddress) : Session::clear($addressDifferentShippingAddressIndex);
	}
	
	static function get_address_different_shipping_address() {
		$addressDifferentShippingAddressIndex = self::address_different_shipping_address_index();
		return Session::get($addressDifferentShippingAddressIndex);
	}
	
	static function set_address2_different_shipping_address($address2DifferentAddress) {
		$address2DifferentShippingAddressIndex = self::address2_different_shipping_address_index();
		$address2DifferentAddress ? Session::set($address2DifferentShippingAddressIndex, $address2DifferentAddress) : Session::clear($address2DifferentShippingAddressIndex);
	}
	
	static function get_address2_different_shipping_address() {
		$address2DifferentShippingAddressIndex = self::address2_different_shipping_address_index();
		return Session::get($address2DifferentShippingAddressIndex);
	}
	
	static function set_city_different_shipping_address($cityDifferentAddress) {
		$cityDifferentShippingAddressIndex = self::city_different_shipping_address_index();
		$cityDifferentAddress ? Session::set($cityDifferentShippingAddressIndex, $cityDifferentAddress) : Session::clear($cityDifferentShippingAddressIndex);
	}
	
	static function get_city_different_shipping_address() {
		$cityDifferentShippingAddressIndex = self::city_different_shipping_address_index();
		return Session::get($cityDifferentShippingAddressIndex);
	}
	
	static function set_country_different_shipping_address($countryDifferentAddress) {
		$countryDifferentShippingAddressIndex = self::country_different_shipping_address_index();
		$countryDifferentAddress ? Session::set($countryDifferentShippingAddressIndex, $countryDifferentAddress) : Session::clear($countryDifferentShippingAddressIndex);
	}
	
	static function get_country_different_shipping_address() {
		$countryDifferentShippingAddressIndex = self::country_different_shipping_address_index();
		return Session::get($countryDifferentShippingAddressIndex);
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
	
	//7) Display Function
	
	static function display_order() {
		 return Order::display_order();
	}
	
	//8) Database saving function
	
	static function save_to_database() {
		return Order::save_to_database();
  	}
  	
}
