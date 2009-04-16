<?php
/**
 * Handles calculation of sales tax on Orders.
 * If you would like to make your own tax calculator, create a subclass of
 * this and link it in using Order::set_modifiers()
 * 
 * Sample configuration in your _config.php:
 * 
 * TaxModifier::set_for_country("NZ", 0.125, "GST", "inclusive")
 * 
 * See {@link set_for_country} for more information
 * 
 * @package ecommerce
 */
class TaxModifier extends OrderModifier {
	
	static $db = array(
		'Country' => 'Text',
		'Rate' => 'Double',
		'Name' => 'Text',
		'TaxType' => "Enum('Exclusive,Inclusive')"
	);
	
	static $names_by_country;
	
	static $rates_by_country;

	static $excl_by_country;
	
	/**
	 * Set the tax information for a particular country.
	 * By default, no tax is charged.
	 * 
	 * @param $country string The two-letter country code
	 * @param $rate float The tax rate, eg, 0.125 = 12.5%
	 * @param $name string The name to give to the tax, eg, "GST"
	 * @param $inclexcl string "inclusive" if the prices are tax-inclusive.
	 * 						"exclusive" if tax should be added to the order total.
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
	
	// Attributes Functions
	
	function Country() {
		return $this->ID ? $this->Country : $this->LiveCountry();
	}
	
	function Rate() {
		return $this->ID ? $this->Rate : $this->LiveRate();
	}
	
	function Name() {
		return $this->ID ? $this->Name : $this->LiveName();
	}
	
	function IsExclusive() {
		return $this->ID ? $this->TaxType == 'Exclusive' : $this->LiveIsExclusive();
	}
	
	protected function LiveCountry() {
		return EcommerceRole::findCountry();
	}
	
	protected function LiveRate() {
		return self::$rates_by_country[$this->LiveCountry()];
	}
	
	protected function LiveName() {
		return self::$names_by_country[$this->LiveCountry()];
	}
	
	protected function LiveIsExclusive() {
		return self::$excl_by_country[$this->LiveCountry()];
	}
	
	function LiveAmount() {
		return $this->AddedCharge();
	}
	
	/**
	 * Get the tax amount that needs to be added to the given order.
	 * If tax is inclusive, then this will be 0
	 */
	function AddedCharge() {
		return $this->IsExclusive() ? $this->Charge() : 0;
	}
	
	/**
	 * Get the tax amount on the given order.
	 */
	function Charge() {
		// Exclusive is easy
		// Inclusive is harder. For instance, with GST the tax amount is 1/9 of the inclusive price, not 1/8
		return $this->TaxableAmount() * ($this->IsExclusive() ? $this->Rate() : (1 - (1 / (1 + $this->Rate())))) ;
	}
	
	function TaxableAmount() {
		$order = $this->Order();
		return $order->SubTotal() + $order->ModifiersSubTotal(array($this));
	}
					
	// Display Functions
	
	function ShowInTable() {
		return $this->Rate();
	}
	
	/*
	 * Precondition : Their is a rate
	 */
	function TableTitle() {
		return number_format($this->Rate() * 100, 1) . '% ' . $this->Name() . ($this->IsExclusive() ? '' : ' (included in the above price)');
	}
	
	// Database Writing Function
	
	/*
	 * Precondition : The order item is not saved in the database yet
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		
		$this->Country = $this->LiveCountry();
		$this->Rate = $this->LiveRate();
		$this->Name = $this->LiveName();
		$this->TaxType = $this->LiveIsExclusive() ? 'Exclusive' : 'Inclusive';
	}
}
?>