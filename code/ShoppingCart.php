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
		static $items = 'items';
		static $modifiers = 'modifiers';
	
	//2) Functions which return variable names stored in the session
	
	private static function setting_table_name() {return self::$current_order . '.' . self::$setting;}
	private static function setting_index($setting) {return self::setting_table_name() . '.' . $setting;}
		private static function initialized_setting_index() {return self::setting_index(self::$initialized);}
		private static function country_setting_index() {return self::setting_index(self::$country);}
		private static function uses_different_shipping_address_index() {return self::setting_index(self::$uses_different_address);}
	private static function items_table_name() {return self::$current_order . '.' . self::$items;}
	private static function item_index($index) {return self::items_table_name() . '.' . $index;}
	private static function modifiers_table_name() {return self::$current_order . '.' . self::$modifiers;}
	private static function modifier_index($index) {return self::modifiers_table_name() . '.' . $index;}
		
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
	
	//4) Items management
		
	static function add_new_item(OrderItem $item) {
		$itemsTableIndex = self::items_table_name();
		if($serializedItems = Session::get($itemsTableIndex)) {
			foreach($serializedItems as $itemIndex => $serializedItem) {
				if($serializedItem != null) {
					$unserializedItem = unserialize($serializedItem);
					if($unserializedItem->hasSameContent($item)) return self::add_item($itemIndex, $item->getQuantity());
				}
			}
		}
		Session::addToArray($itemsTableIndex, serialize($item));
	}
	
	static function add_item($itemIndex, $quantity = 1) {
		$serializedItemIndex = self::item_index($itemIndex);
		$serializedItem = Session::get($serializedItemIndex);
		$unserializedItem = unserialize($serializedItem);
		$unserializedItem->addProtectedQuantity($quantity);
		self::set_item($itemIndex, $unserializedItem);
	}
		
	static function set_quantity_item($itemIndex, $quantity) {
		$serializedItemIndex = self::item_index($itemIndex);
		$serializedItem = Session::get($serializedItemIndex);
		$unserializedItem = unserialize($serializedItem);
		$unserializedItem->setProtectedQuantity($quantity);
		self::set_item($itemIndex, $unserializedItem);
	}
	
	static function remove_item($itemIndex, $quantity = 1) {
		$serializedItemIndex = self::item_index($itemIndex);
		$serializedItem = Session::get($serializedItemIndex);
		$unserializedItem = unserialize($serializedItem);
		$newQuantity = $unserializedItem->getQuantity() - $quantity;
		if($newQuantity > 0) {
			$unserializedItem->setProtectedQuantity($newQuantity);
			self::set_item($itemIndex, $unserializedItem);
		}
		else Session::clear($serializedItemIndex);
	}
	
	static function remove_all_item($itemIndex) {
		$serializedItemIndex = self::item_index($itemIndex);
		Session::clear($serializedItemIndex);
	}
	
	static function remove_all_items() {
		$itemsTableIndex = self::items_table_name();
		Session::clear($itemsTableIndex);
	}
	
	static function has_items() {
		$itemsTableIndex = self::items_table_name();
		return Session::get($itemsTableIndex) != null;
	}
		
	static function get_items() {
		$itemsTableIndex = self::items_table_name();
		if($serializedItems = Session::get($itemsTableIndex)) {
			$items = array();
			foreach($serializedItems as $itemIndex => $serializedItem) {
				if($serializedItem != null) {
					$unserializedItem = unserialize($serializedItem);
					$unserializedItem->setId($itemIndex);
					array_push($items, $unserializedItem);
				}
			}
			return $items;
		}
		return null;
	}
	
	protected static function set_item($itemIndex, OrderItem $item) {
		$serializedItemIndex = self::item_index($itemIndex);
		Session::set($serializedItemIndex, serialize($item));
	}
	
	//5) Modifiers management
	
	static function init_all_modifiers() {
		Order::init_all_modifiers();
	}
	
	static function add_new_modifier(OrderModifier $modifier) {
		$modifiersTableIndex = self::modifiers_table_name();
		Session::addToArray($modifiersTableIndex, serialize($modifier));
	}
		
	static function remove_modifier($modifierIndex) {
		$serializedModifierIndex = self::modifier_index($modifierIndex);
		Session::clear($serializedModifierIndex);
	}
			
	static function remove_all_modifiers() {
		$modifiersTableIndex = self::modifiers_table_name();
		Session::clear($modifiersTableIndex);
	}
	
	static function has_modifiers() {
		$modifiersTableIndex = self::modifiers_table_name();
		return Session::get($modifiersTableIndex) != null;
	}
		
	static function get_modifiers() {
		if(! self::is_initialized()) {
			self::init_all_modifiers();
			self::set_initialized(true);
		}
		$modifiersTableIndex = self::modifiers_table_name();
		if($serializedModifiers = Session::get($modifiersTableIndex)) {
			$modifiers = array();
			foreach($serializedModifiers as $modifierIndex => $serializedModifier) {
				if($serializedModifier != null) {
					$unserializedModifier = unserialize($serializedModifier);
					$unserializedModifier->setId($modifierIndex);
					array_push($modifiers, $unserializedModifier);
				}
			}
			return $modifiers;
		}
		return null;
	}
	
	//6) Init function
	
	static function clear() {
		self::remove_all_settings();
		self::remove_all_items();
		self::remove_all_modifiers();
	}
	
	//7) Current order access function
	
	static function current_order() {
		 return Order::current_order();
	}
	
	//8) Database saving function
	
	static function save_current_order() {
		return Order::save_current_order();
  	}
  	
}

class ShoppingCart_Controller extends Controller {
	
	static $URLSegment = 'shoppingcart';
	
	static function add_item_link($id) {return self::$URLSegment . '/additem/' . $id;}
	static function remove_item_link($id) {return self::$URLSegment . '/removeitem/' . $id;}
	static function remove_all_item_link($id) {return self::$URLSegment . '/removeallitem/' . $id;}
	static function set_quantity_item_link($id) {return self::$URLSegment . '/setquantityitem/' . $id;}
	
	static function remove_modifier_link($id) {return self::$URLSegment . '/removemodifier/' . $id;}
	
	function additem() {
		$itemId = $this->urlParams['ID'];
		ShoppingCart::add_item($itemId);
		Director::redirectBack();
	}
	
	function removeitem() {
		$itemId = $this->urlParams['ID'];
		ShoppingCart::remove_item($itemId);
		Director::redirectBack();
	}
	
	function removeallitem() {
		$itemId = $this->urlParams['ID'];
		ShoppingCart::remove_all_item($itemId);
		Director::redirectBack();
	}
	
	/**
	 * Ajax method to set an item quantity
	 */
	function setquantityitem() {
		$itemId = $this->urlParams['ID'];
		$quantity = $_REQUEST['quantity'];
		if(is_numeric($quantity) && is_int($quantity + 0)) {
			if($quantity > 0) {
				ShoppingCart::set_quantity_item($itemId, $quantity);
				$currentOrder = ShoppingCart::current_order();
				
				$js = array();
				
				if($items = $currentOrder->Items()) {
					foreach($items as $item) $item->updateForAjax($js);
				}
				
				if($modifiers = $currentOrder->Modifiers()) {
					foreach($modifiers as $modifier) $modifier->updateForAjax($js);
				}
				
				$currentOrder->updateForAjax($js);
				
				return json_encode($js);
			}
			else user_error("Bad data to Product->setQuantity: quantity=$quantity", E_USER_WARNING);
		}
		else user_error("Bad data to Product->setQuantity: quantity=$quantity", E_USER_WARNING);
	}
	
	function removemodifier() {
		$modifierId = $this->urlParams['ID'];
		ShoppingCart::remove_modifier($modifierId);
		Director::redirectBack();
	}
		
}