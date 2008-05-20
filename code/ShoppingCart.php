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
	
	/*static function add_product(Product $product) {
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
	}*/
	
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
		$unserializedItem->addQuantity($quantity);
		self::set_item($itemIndex, $unserializedItem);
	}
	
	protected static function set_item($itemIndex, Order_item $item) {
		$serializedItemIndex = self::item_index($itemIndex);
		Session::set($serializedItemIndex, serialize($item));
	}
	
	static function set_item_quantity($itemIndex, $quantity) {
		$serializedItemIndex = self::item_index($itemIndex);
		$serializedItem = Session::get($serializedItemIndex);
		$unserializedItem = unserialize($serializedItem);
		$unserializedItem->setQuantity($quantity);
		self::set_item($itemIndex, $unserializedItem);
	}
	
	static function remove_item($itemIndex, $quantity = 1) {
		$serializedItemIndex = self::item_index($itemIndex);
		$serializedItem = Session::get($serializedItemIndex);
		$unserializedItem = unserialize($serializedItem);
		$newQuantity = $unserializedItem->getQuantity() - $quantity;
		if($newQuantity > 0) {
			$unserializedItem->setQuantity($newQuantity);
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
	
	//5) Modifier management
	
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
	
	static function save_current_order_to_database() {
		return Order::save_to_database();
  	}
  	
}

class ShoppingCart_Controller extends Controller {
	
	static $URLSegment = 'shoppingcart';
	
	static function additemLink($id) {return self::$URLSegment . '/additem/' . $id;}
	static function removeitemLink($id) {return self::$URLSegment . '/removeitem/' . $id;}
	static function removeallitemLink($id) {return self::$URLSegment . '/removeallitem/' . $id;}
	static function setquantityLink($id) {return self::$URLSegment . '/setquantity/' . $id;}
	
	static function removemodifierLink($id) {return self::$URLSegment . '/removemodifier/' . $id;}
	
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
	function setquantity() {
		$itemId = $this->urlParams['ID'];
		$quantity = $_REQUEST['quantity'];
		if(is_numeric($quantity) && is_int($quantity + 0)) {
			if($quantity > 0) {
				ShoppingCart::set_item_quantity($itemId, $quantity);
				$currentOrder = ShoppingCart::current_order();
				
				$js = array();
				
				if($items = $currentOrder->Items()) {
					foreach($items as $item) $item->updateForAjax($js);
				}
				
				if($modifiers = $currentOrder->Modifiers()) {
					foreach($modifiers as $modifier) $modifier->updateForAjax($js);
				}
				
				$currentOrder->updateForAjax($js);
				
				/*$item_subtotal = 0;
				$item_quantity = 0;
				$subtotal = 0;
				$grand_total = 0;
				
				if($items = $currentOrder->Items()) {
					foreach($items as $item) {
						if($item->ProductID == $this->ID) {
							$item_subtotal = $item->SubTotal;
							$item_quantity = $item->Quantity;
						}
					}
				}
								
				// TODO Use glyphs instead of hard-coding to be the '$' glyph
				$item_subtotal = '$' . number_format($item_subtotal, 2);
				$subtotal = '$' . number_format($currentOrder->_Subtotal(), 2);
				$grand_total = '$' . number_format($currentOrder->_Total(), 2) . " " . $currentOrder->Currency();
				
				$js = array();
				
				$js['Item' . $this->ID . '_Subtotal'] = $item_subtotal;
				$js['Subtotal'] = $subtotal;
				$js['GrandTotal'] = $grand_total;
				$js['OrderForm_OrderForm_Amount'] = $grand_total;
				
				$js['Cart_Item' . $this->ID . '_Quantity'] = $item_quantity;
				$js['Cart_Subtotal'] = $subtotal;
				$js['Cart_GrandTotal'] = $grand_total;
					*/			
				return Product::javascript_for_new_values($js);
				//return $js;
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