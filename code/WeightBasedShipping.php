<?php

/**
 * @package ecommerce
 */
 
/**
 * Calculates the shipping cost of an order, by taking the products
 * and calculating the shipping weight, based on an array set in _config
 * ASSUMPTION: The total order weight can be at maximum the last item
 * in the $shippingCosts array.
 */
class WeightBasedShipping extends ShippingCalculator{
	
	/**
	 * Calculates the extra charges from this order based on the weight attribute of a product
 	 * ASSUMPTION -> weight in grams
	 */
	function getCharge(Order $o){
	
		$orderItems = $o->Items();
		// Calculate the total weight of the order
		if($orderItems){
			foreach($orderItems as $orderItem){
				$totalWeight += ($orderItem->Weight * $orderItem->quantity);
			}
		}
		
		// Check if UseShippingAddress is true, and if so then
		// check if ShippingCountry exists, and use that if it does
		if($o->UseShippingAddress) {
			if($o->ShippingCountry) {
				$shippingCountry = $o->ShippingCountry;
			}
		}
		
		// if there is a shipping country then check whether it is national or
		// international
		if($shippingCountry) {
			if($shippingCountry == "NZ"){
				$cost = $this->nationalCost($totalWeight);
			}else{
				$cost = $this->internationalCost($totalWeight, $shippingCountry);
			}
			
			return $cost;
		}else{
			if($o && $o->MemberID && ($member = DataObject::get_by_id("Member", $o->MemberID))){
				if($member->Country) {
					$country = $member->Country;
				}
			}else{
				$country = Geoip::visitor_country(); 
			}
			if(!$country) {
				$country = 'NZ';	
			}	
			if($country == "NZ"){
				return $this->nationalCost($totalWeight);
			}else{
				return $this->internationalCost($totalWeight,$country);
			}
		}
	}
	
	/**
	 * Retrieve the cost from NZ shipping
	 */
	function nationalCost($totalWeight){
		// if a product can't have a weight, don't charge/display it
		if($totalWeight <= 0) {
			return "0.00";
		}
		
		// return the pricing appropriate for the weight
		$shippingCosts = self::$a['NZ'];
		
		return $this->getCostFromWeightList($totalWeight, $shippingCosts);
	}

	/**
	 * Retrieve the cost from overseas shipping
	 */
	function internationalCost($totalWeight, $country){
		// if a product can't have a weight. Don't charge/display it
		if($totalWeight <= 0) {
			return "0.00";
		}
		
		// return the pricing appropriate for the weight
		$shippingCosts = self::$a[$country];
		
		// if there isn't any country code specifically in the array, use a zone instead
		if(!$shippingCosts) {
			$zone = self::$b[$country];
			$shippingCosts = self::$a[$zone];
		}
		return $this->getCostFromWeightList($totalWeight, $shippingCosts);
	}
	
	/**
	 * Get the cost from a list of max-weight => cost pairs
	 */
	function getCostFromWeightList($totalWeight, $shippingCosts) {
		if($shippingCosts) {
			foreach($shippingCosts as $weight => $cost) {
				if($totalWeight >= $weight) {
					continue;
				} else {
					return $cost;
				}
			}		
			return array_pop($shippingCosts);
		}
	}
	
}
?>
