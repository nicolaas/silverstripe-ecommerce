<?php

/**
 * @package ecommerce
 */

/**
 * Handles calculation of sales tax on Orders
 * 
 * Configuration:
 * TaxModifier::set_for_country("NZ", 0.125, "GST", "inclusive")
 * See {@link set_for_country} for more information
 */
class TaxModifier extends OrderModifier {
	
	static $names_by_country;
	static $rates_by_country;
	static $excl_by_country;
			
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
			case 'inclusive' : self::$excl_by_country[$country] = false; break;
			case 'exclusive' : self::$excl_by_country[$country] = true; break;
			default: user_error("TaxModifier::set_for_country - bad argument '$inclexcl' for \$inclexl.  Must be 'inclusive' or 'exclusive'.", E_USER_ERROR);
		} 
	}
		
	function TaxableAmount() {
		$order = $this->getOrder();
		return $order->SubTotal() + $order->ModifiersSubTotal(array($this));
	}
	
	function Country() {
		return EcommerceRole::findCountry();
	}
	
	function Name() {
		return self::$names_by_country[$this->Country()];
	}
	
	function Rate() {
		return self::$rates_by_country[$this->Country()];
	}
	
	function IsExclusive() {
		return self::$excl_by_country[$this->Country()];
	}
	
	/**
	 * Get the tax amount on the given order.
	 */
	function Charge() {
		// Exclusive is easy
		// Inclusive is harder.  For instance, with GST the tax amount is 1/9 of the inclusive price, not 1/8
		return $this->TaxableAmount() * ($this->IsExclusive() ? $this->Rate() : (1 - (1 / (1 + $this->Rate())))) ;
	}

	/**
	 * Get the tax amount that needs to be added to the given order.
	 * If tax is inclusive, then this will be 0
	 */
	function AddedCharge() {
		return IsExclusive() ? $this->Charge() : 0;
	}
	
	/**
	 * Return a piece of text to put at the end of the price.
	 * For example, "incl. GST" or "excl. VAT"
	 */
	function PriceSuffix() {
		if($this->Rate()) return ($this->IsExclusive() ? 'excl. ' : 'incl. ') . $this->Name();
		else return '';
	}

	/**
	 * Return a title of the tax line item in the report.
	 * For example, "GST (included in the above price)" or "VAT"
	 */
	function LineItemTitle() {
		if($this->Rate()) return $this->RateAsPercentage() . ' ' . $this->Name() . ($this->IsExclusive() ? '' : ' (included in the above price)');
	}
	
	/**
	 * Returns the tax rate as a percentage for the given country.
	 * If there is no tax, it will return null
	 */
	function RateAsPercentage() {
		return $this->Rate() ? number_format($this->Rate() * 100, 1) . '%' : null;
	}
	
	function ShowInOrderTable() {return $this->LineItemTitle();}
	function ClassNameForTable() {return 'GST';}
	function TitleForTable() {return $this->LineItemTitle();}
	function ValueIdForTable() {return 'TaxCost';}
	function ValueForTable() {
		$val = new Currency('currency');
		$val->setValue($this->Charge());
		return $val->Nice();
	}
	
	function getAmount() {
		return $this->AddedCharge();
	}
}

?>