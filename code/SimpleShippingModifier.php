<?php

/**
 * @package ecommerce
 */

/**
 * SimpleShoppingModifier is the default shipping calculation scheme.
 * It lets you set a fixed shipping costs, or a fixed cost for each country you're delivering to.
 * If you require more advanced shipping control, we suggest that you create your own subclass of {@link OrderModifier}
 */
class SimpleShippingModifier extends OrderModifier {
	
	static $db = array(
		'Country' => 'Text',
		'CountryCode' => 'Text',
		'ShippingChargeType' => "Enum(array('Default','ForCountry'))"
	);
	
	static $default_charge = 0;
	static $charges_by_country = array();

	static function set_charges($charge) {
		self::$default_charge = $charge;
	}
	
	/**
	 * Set shipping charges on a country by country basis. 
	 * For example, SimpleShippingModifier::set_charges_for_countries(array(
	 *   'US' => 10,
	 *   'NZ' => 5,
	 * ));
	 * @param countryMap A map of 2-letter country codes
	 */	
	static function set_charges_for_countries($countryMap) {
		self::$charges_by_country = array_merge(self::$charges_by_country, $countryMap);
	}
	
	//1) Attributes Functions Access
	
	function Country() {return $this->ID ? $this->Country : $this->LiveCountry();}
	function CountryCode() {return $this->ID ? $this->CountryCode : $this->LiveCountryCode();}
	function IsDefaultCharge() {return $this->ID ? $this->ShippingChargeType == 'Default' : $this->LiveIsDefaultCharge();}
	
	function LiveCountry($code = false) {
		$order = $this->Order();
		return $order->findShippingCountry($code);
	}
	function LiveCountryCode() {return $this->LiveCountry(true);}
	function LiveIsDefaultCharge() {return ! ($this->LiveCountryCode() && array_key_exists($this->LiveCountryCode(), self::$charges_by_country));}
	
	/**
	 * Find the amount for the shipping on the shipping country for the order.
	 */
	function LiveAmount() {return $this->LiveIsDefaultCharge() ? self::$default_charge : self::$charges_by_country[$this->LiveCountryCode()];}
	
	//2) Display Functions
	
	// Functions called from the Cart
	function ShowInCart() {return $this->getValue() > 0;}
	function TitleForCart() {return 'Shipping';}
	
	// Functions called from the Order table
	function TitleForTable() {
		if($shippingCountry = $this->Country()) return "Shipping to $shippingCountry";
		else return 'Shipping';
	}
	function ValueIdForTable() {return 'ShippingCost';}
	function ValueForTable() {
		$val = new Currency('currency');
		$val->setValue($this->getValue());
		return $val->Nice();
	}
	
	//3) Database Writing Functions
	
	public function write() {
		$this->Country = $this->Country();
		$this->CountryCode = $this->CountryCode();
		$this->ShippingChargeType = $this->IsDefaultCharge() ? 'Default' : 'ForCountry';
		parent::write();
	}
	
}