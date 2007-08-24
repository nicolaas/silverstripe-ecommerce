<?php

/**
 * @package ecommerce
 */

/**
 * Handles calculation of sales tax on Orders
 * 
 * Configuration:
 * TaxCalculator::set_for_country("NZ", 0.125, "GST", "inclusive")
 * See {@link set_for_country} for more information
 */
class TaxCalculator extends ViewableData {
	static $names_by_country;
	static $rates_by_country;
	static $excl_by_country;
	
	static $casting = array(
		'Charge' => 'Currency',
		'AddedCharge' => 'Currency',
	);
	
	protected $amount, $country;
	protected $name, $rate, $excl;
	
	/**
	 * Set the tax information for a particular country.  By default, no tax is charged.
	 * @param $country String The two-letter country code
	 * @param $rate Float The tax rate, eg, 0.125 = 12.5%
	 * @param $name String The name to give to the tax, eg, "GST"
	 * @param $inclexcl String "inclusive" if the prices are tax-inclusive.  "exclusive" if tax should be added to the order total.
	 */
	static function set_for_country($country, $rate, $name, $inclexcl) {
		self::$names_by_country[$country] = $name;
		self::$rates_by_country[$country] = $rate;
		switch($inclexcl) {
			case "inclusive": self::$excl_by_country[$country] = false; break;
			case "exclusive": self::$excl_by_country[$country] = true; break;
			default: user_error("TaxCalculator::set_for_country - bad argument '$inclexcl' for \$inclexl.  Must be 'inclusive' or 'exclusive'.", E_USER_ERROR);
		} 
	}
	
	
	/**
	 * Create a new TaxCalculator object, which will provide a number of pieces of tax information.
	 * @param $amount Float The total amount of the order, including shipping, before tax calculation has been done.
	 * @param $country String The 2 letter country code of the bill payer.
	 */
	function __construct($amount, $country) {
		$this->amount = $amount;
		$this->country = $country;
		$this->rate = self::$rates_by_country[$country];
		$this->name = self::$names_by_country[$country];
		$this->excl = self::$excl_by_country[$country];
	}
	
	
	/**
	 * Get the tax amount on the given order.
	 */
	function Charge() {
		// Exclusive is easy
		if($this->excl) return $this->amount * $this->rate;
		
		// Inclusive is harder.  For instance, with GST the tax amount is 1/9 of the inclusive price, not 1/8
		else return $this->amount * (1-(1/(1+$this->rate)));
	}

	/**
	 * Get the tax amount that needs to be added to the given order.
	 * If tax is inclusive, then this will be 0
	 */
	function AddedCharge() {
		if($this->excl) {
			return $this->Charge();
		} else {
			return 0;
		}
	}
	
	/**
	 * Return a piece of text to put at the end of the price.
	 * For example, "incl. GST" or "excl. VAT"
	 */
	function PriceSuffix() {
		if($this->rate) {
			if($this->excl) return "excl. " . $this->name;
			else return "incl. " . $this->name;
		}
	}

	/**
	 * Return a title of the tax line item in the report.
	 * For example, "GST (included in the above price)" or "VAT"
	 */
	function LineItemTitle() {
		if($this->rate) {
			if($this->excl) return $this->Rate() . ' ' . $this->name;
			else return $this->Rate() . ' ' . $this->name . " (included in the above price)";
		}
	}
	
	/**
	 * Returns the tax rate as a percentage for the given country.
	 * If there is no tax, it will return null
	 */
	function Rate() {
		if($this->rate) {
			return number_format($this->rate * 100,1) . '%';
		} else {
			return null;
		}
	}
	
}

?>