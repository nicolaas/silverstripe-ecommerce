<?php

/**
 * @package ecommerce
 */
 
/** 
 * This handles the shipping calculator, allowing modification 
 * for the shipping process for different projects
 */
abstract class ShippingCalculator extends Object {
	
	abstract function getCharge(Order $o);
  
}
?>