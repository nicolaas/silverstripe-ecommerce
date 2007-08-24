<?php

/**
 * @package ecommerce
 */

/**
 * SimpleShoppingCalculator is the default shipping calculation scheme.
 * It lets you set a fixed shipping costs, or a fixed cost for each country you're delivering to.
 * If you require more advanced shipping control, we suggest that you create your own subclass of {@link ShippingCalculator}
 */
class SimpleShippingCalculator extends ShippingCalculator {
	static $default_charge = 0;
	static $charges_by_country = array();

	static function set_charges($charge) {
		self::$default_charge = $charge;
	}
	
	/**
	 * Set shipping charges on a country by country basis. 
	 * For example, SimpleShippingCalculator::set_charges_for_countries(array(
	 *   'US' => 10,
	 *   'NZ' => 5,
	 * ));
	 * @param countryMap A map of 2-letter country codes
	 */	
	static function set_charges_for_countries($countryMap) {
		self::$charges_by_country = array_merge(self::$charges_by_country, $countryMap);
	}
	
	/**
	 * Find the charge for the shipping on the shipping country for the order.
	 */
	function getCharge(Order $o) {
		if($o->findShippingCountry(true) && array_key_exists($o->findShippingCountry(true), self::$charges_by_country)) {
			return self::$charges_by_country[$o->findShippingCountry(true)];
		} else {
			return self::$default_charge;
		}
	}
}