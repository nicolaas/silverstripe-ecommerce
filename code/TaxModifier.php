<?php

/**
 * @package ecommerce
 */

/**
 * Handles calculation of sales tax on Orders.
 * If you would like to make your own tax calculator, create a subclass of
 * this and link it in using Order::set_modifiers()
 * 
 * Configuration:
 * TaxModifier::set_for_country("NZ", 0.125, "GST", "inclusive")
 * See {@link set_for_country} for more information
 */
class TaxModifier extends OrderModifier {
	
	static $db = array(
		'Country' => 'Text',
		'Rate' => 'Decimal',
		'Name' => 'Text',
		'TaxType' => "Enum(array('Exclusive','Inclusive'))"
	);
	
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
	
	//1) Attributes Functions Access
	
	function Country() {return $this->ID ? $this->Country : $this->LiveCountry();}
	function Rate() {return $this->ID ? $this->Rate : $this->LiveRate();}
	function Name() {return $this->ID ? $this->Name : $this->LiveName();}
	function IsExclusive() {return $this->ID ? $this->TaxType == 'Exclusive' : $this->LiveIsExclusive();}
	
	function LiveCountry() {return EcommerceRole::findCountry();}
	function LiveRate() {return self::$rates_by_country[$this->LiveCountry()];}
	function LiveName() {return self::$names_by_country[$this->LiveCountry()];}
	function LiveIsExclusive() {return self::$excl_by_country[$this->LiveCountry()];}
	
	function LiveAmount() {return $this->AddedCharge();}
	
	function TaxableAmount() {
		$order = $this->Order();
		return $order->SubTotal() + $order->ModifiersSubTotal(array($this));
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
	
	//2) Display Functions
	
	// Functions called from the Cart
	function ShowInCart() {return $this->ShowInOrderTable();}
	
	// Functions called from the Order table
	function ShowInOrderTable() {return $this->LineItemTitle();}
	function ClassNameForTable() {return 'GST';}
	function TitleForTable() {return $this->LineItemTitle();}
	function ValueIdForTable() {return 'TaxCost';}
	function ValueForTable() {
		$val = new Currency('currency');
		$val->setValue($this->Charge());
		return $val->Nice();
	}
	
	//3) Database Writing Function
	
	public function write() {
		$this->Country = $this->Country();
		$this->Rate = $this->Rate();
		$this->Name = $this->Name();
		$this->TaxType = $this->IsExclusive() ? 'Exclusive' : 'Inclusive';
		parent::write();
	}
}

?>