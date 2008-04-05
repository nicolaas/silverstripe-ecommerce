<?php

/**
 * @package ecommerce
 */
 
/** 
 * This handles the shipping calculator, allowing modification 
 * for the shipping process for different projects
 */
abstract class ShippingCalculator extends Object {
	
	/**
	 * Abstract class for calculating and returning a shipping amount
	 * based on the passed in Order instance. This method is defined on
	 * the shipping subclasses of ShippingCalculator.
	 * 
	 * For an example on how this is created, please see {@link SimpleShippingCalculator}
	 *
	 * @param Order $o The order to calculate shipping of
	 */
	abstract function getCharge(Order $o);
  
}
?>