<?php

class ProductVariation extends DataObject {
	
	static $db = array(
		'Title' => 'Text',
		'Price' => 'Currency'
	);
	
	static $has_one = array(
		'Product' => 'Product'
	);
	
	static $casting = array(
		'Title' => 'Text',
		'Price' => 'Currency'
	);
	
	static $versioning = array('Stage');
	
	static $extensions = array("Versioned('Stage')");
	
	function getCMSFields_forPopup() {
		$fields = array();
		$fields[] = new TextField('Title');
		$fields[] = new TextField('Price');
		return new FieldSet($fields);
	}
	
	function AllowPurchase() {return $this->Price;}
	
	/*
	 * Returns if the product variation is already in the shopping cart.
	 * Note : This function is usable in the Product Variation context because a
	 * ProductVariation_OrderItem only has a ProductVariation object in attribute
	 */
	function IsInCart() {return $this->Item() ? true : false;}
	
	/*
	 * Returns the order item which contains the product variation
	 * Note : This function is usable in the ProductVariation context because a
	 * ProductVariation_OrderItem only has a ProductVariation object in attribute
	 */
	function Item() {
		$currentOrder = ShoppingCart::current_order();
		if($items = $currentOrder->Items()) {
			foreach($items as $item) {
				if($item instanceof ProductVariation_OrderItem && $itemProductVariation = $item->ProductVariation()) {
					if($itemProductVariation->ID == $this->ID && $itemProductVariation->Version == $this->Version) return $item;
				}
			}
		}
		else return null;
	}
	
	function addLink() {return $this->Product()->addVariationLink($this->ID);}
}

class ProductVariation_OrderItem extends OrderItem {
	
	protected $_productVariationID;
	protected $_productVariationVersion;
	protected $_productVersion;
	
	static $db = array(
		'ProductVariationVersion' => 'Int',
		'ProductVersion' => 'Int'
	);
	
	static $has_one = array(
		'ProductVariation' => 'ProductVariation'
	);
	
	public function __construct($productVariation = null, $quantity = 1) {
		
		// Case 1 : Constructed by the static function get of DataObject
		
		if(is_array($productVariation)) {
			$this->ProductVariationID = $this->_productVariationID = $productVariation['ProductVariationID'];
			$this->ProductVariationVersion = $this->_productVariationVersion = $productVariation['ProductVariationVersion'];
			$this->ProductVersion = $this->_productVersion = $productVariation['ProductVersion'];
			parent::__construct($productVariation, $quantity);
		}
		
		// Case 2 : Constructed in memory
		
		else if(is_object($productVariation)) {
			parent::__construct($productVariation, $quantity);
			$this->_productVariationID = $productVariation->ID;
 			$this->_productVariationVersion = $productVariation->Version;
 			$this->_productVersion = $productVariation->Product()->Version;
		}
		
		else parent::__construct();
	}
	
	// ProductVariation Access Function
	
	/*
	 * To DO : Add Lives and Drafts Values Management
	 */
	public function ProductVariation($current = false) {
		if($current) return DataObject::get_by_id('ProductVariation', $this->_productVariationID);
		else return Versioned::get_version('ProductVariation', $this->_productVariationID, $this->_productVariationVersion);
	}
	
	public function Product($current = false) {
		$productID = $this->ProductVariation()->ProductID;
		if($current) return DataObject::get_by_id('Product', $productID);
		else return Versioned::get_version('Product', $productID, $this->_productVersion);
	}
	
	// Functions to overload
	
	function hasSameContent($orderItem) {
		$equals = parent::hasSameContent($orderItem);
		return $equals && $orderItem instanceof ProductVariation_OrderItem && $this->_productVariationID == $orderItem->_productVariationID && $this->_productVariationVersion == $orderItem->_productVariationVersion && $this->_productVersion == $orderItem->_productVersion;
	}
	
	function UnitPrice() {return $this->ProductVariation()->Price;}
	
	function TableTitle() {return $this->Product()->Title . ' (' . $this->ProductVariation()->Title . ')';}
	function Link() {
		if($product = $this->Product(true)) return $product->Link();
	}
				
	// Database Writing Methods
	
	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->ProductVariationID = $this->_productVariationID;
		$this->ProductVariationVersion = $this->_productVariationVersion;
		$this->ProductVersion = $this->_productVersion;
	}
	
	// Debug Function
		
	public function debug() {
		$title = $this->TableTitle();
		$productVariationID = $this->_productVariationID;
		$productVariationVersion = $this->_productVariationVersion;
		$productID = $this->ProductVariation()->ProductID;
		$productVersion = $this->_productVersion;
		return parent::debug() .<<<HTML
			<h3>ProductVariation_OrderItem class details</h3>
			<p>
				<b>Title : </b>$title<br/>
				<b>ProductVariation ID : </b>$productVariationID<br/>
				<b>ProductVariation Version : </b>$productVariationVersion<br/>
				<b>Product ID : </b>$productID<br/>
				<b>Product Version : </b>$productVersion
			</p>
HTML;
	}
}

?>
