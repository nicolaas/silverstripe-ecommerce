<?php

/**
 * @package ecommerce
 */
 
/** 
 * This handles the shipping calculator, allowing modification 
 * for the shipping process for different projects
 */
abstract class ShippingCalculator extends Object {
	
	static $factory_class = "SimpleShippingCalculator";
	static $a;
	static $b;
	static $c;
	
	
	static function makeFrom($className, $a, $b, $c) {
		self::$factory_class = $className;	
		self::$a = $a;
		self::$b = $b;
		self::$c = $c;
	}
	
	static function create() {
		$className = self::$factory_class;
		$a = self::$a;
		$b = self::$b;
		$c = self::$c;
		return new $className($a, $b, $c);
	}

	 abstract function getCharge(Order $o);
  
}
?>