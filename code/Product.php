<?php

/**
 * @package ecommerce
 */
 
/**
 * Product contains the actual individual products
 * data - including extra fields like Price and Weight
 */
class Product extends Page {
	
	static $add_action = 'a Product Page';
	
	static $casting = array();
	
	static $default_parent = 'ProductGroup';
	
	static $icon = 'cms/images/treeicons/book';
	
	static $db = array(
		'Price' => 'Currency',
		'Weight' => 'Decimal(9,2)',
		'Model' => 'Varchar',
		'FeaturedProduct' => 'Boolean',
		'AllowPurchase' => 'Boolean',
		"InternalItemID" => "Varchar(30)",
	);
	
	/**
	 * Image Support 
	 */
	static $has_one = array(
		'Image' => 'Product_Image'
	);
	
	static $defaults = array(
		'AllowPurchase' => true
	);
	
	/**
	 * Create the fields for a product within the CMS
	 */
	function getCMSFields() {
		$fields = parent::getCMSFields();

				// standard extra fields like weight and price
		$fields->addFieldToTab("Root.Content.Main", new TextField("Weight", "Weight (kg)", "", 12));
		$fields->addFieldToTab("Root.Content.Main", new TextField("Price", "Price", "", 12));
		$fields->addFieldToTab("Root.Content.Main", new TextField("Model", "Author", "", 50));

		$fields->addFieldToTab("Root.Content.Main", new TextField("InternalItemID","Product Code","",7));

		// product image field
		if(!$fields->dataFieldByName("Image")) {
			$fields->addFieldToTab("Root.Content.Images", new ImageField("Image", "Product Image"));
		}

		// flags for this product which affect it's behaviour on the site
		$fields->addFieldToTab("Root.Content.Main", new CheckboxField("FeaturedProduct", "Featured Product"));
		$fields->addFieldToTab("Root.Content.Main", new CheckboxField("AllowPurchase", "Allow product to be purchased",1));

		return $fields;
	}

	/**
	 * Returns the shopping cart
	 */
	function Cart() {
		HTTP::set_cache_age(0);
		return ShoppingCart::current_order();
	}

	/**
	 * Conditions for whether a product can be purchased.
	 * If it has the checkbox for 'Allow this product to be purchased',
	 * as well as having a price. Otherwise a user can't buy it.
	 */
	function AllowPurchase() {return $this->AllowPurchase && $this->Price;}

	/** 
	 * Return nested/child ProductGroups underneath this one
	 */
	function ChildGroups() {
		return DataObject::get("ProductGroup", "ShowInMenus = 1 AND ParentID = " . $this->ID);
	}	
	
	function GroupsMenu() {
		$p = $this->Parent();
		if(!$p->ID || !($p instanceof ProductGroup)) {
			return $this->ChildGroups();
		} else {
			return $p->GroupsMenu();
		}
	}
		
	/*
	 * Returns if the product is already in the shopping cart.
	 * Note : This function is usable in the Product context because a
	 * Product_OrderItem only has a Product object in attribute
	 */
	function IsInCart() {return $this->Item() ? true : false;}
	
	/*
	 * Returns the order item which contains the product
	 * Note : This function is usable in the Product context because a
	 * Product_OrderItem only has a Product object in attribute
	 */
	function Item() {
		$currentOrder = ShoppingCart::current_order();
		if($items = $currentOrder->Items()) {
			foreach($items as $item) {
				if($item instanceof Product_OrderItem && $item->getProduct()->ID == $this->ID) return $item;
			}
		}
		else return null;
	}
	
	/**
	 * Return the currency being used on the site.
	 */
	function Currency() {
		return Order::site_currency();
	}
	
	/**
	 * Return the gloal tax information of the site.
	 */
	function TaxInfo() {
		$currentOrder = ShoppingCart::current_order();
		return $currentOrder->TaxInfo();
	}
	
	function addLink() {return $this->Link() . 'add';}
}

class Product_Attribute extends DataObject {

}

class Product_Controller extends Page_Controller {
	
	/**
	 * Include the product group's requirements, override if the project has the file,
	 * otherwise use the module one instead
	 */	
	function init(){
		Requirements::javascript('jsparty/prototype.js');
		Requirements::javascript('jsparty/prototype_improvements.js');
		Requirements::javascript('jsparty/behaviour.js');
		Requirements::javascript('ecommerce/javascript/AjaxQuantity.js');
		
		Requirements::themedCSS('Product');
		Requirements::themedCSS('Cart');

		parent::init();
	}
		
	/**
	 * This is used by the OrderForm to add more of this product to the current cart.
	 */
	
	function add() {
		if($this->AllowPurchase()) {
			ShoppingCart::add_new_item(new Product_OrderItem($this));
			Director::redirectBack();
		}
		else return false;
	}
}

/**
 * Class to support product images
 */
class Product_Image extends Image {
	static $db = null;

	function generateThumbnail($gd) {
		$gd->setQuality(80);
		return $gd->paddedResize(140,100);
	}
	
	function generateContentImage($gd) {
		$gd->setQuality(90);
		return $gd->resizeByWidth(200);
	}
	
	function generateLargeImage($gd) {
		$gd->setQuality(90);
		return $gd->resizeByWidth(600);
	}
	
}

class Product_OrderItem extends OrderItem {
	
	protected $product;
	
	static $db = array(
		'ProductVersion' => 'Int'
	);
	
	static $has_one = array(
		'Product' => 'Product'
	);
	
	public function __construct($product = null, $quantity = 1) {
				
		// Case 1 : Constructed by the static function get of DataObject
		
		if(is_array($product)) {
			if($dbProduct = DataObject::get_by_id('Product', $product['ProductID'])) {
				$this->ProductVersion = $product['ProductVersion'];
				$this->ProductID = $product['ProductID'];
				$this->product = $dbProduct;
				$this->failover = $dbProduct;
				parent::__construct($product, $quantity);
			}
			else user_error("Product #$product[ProductID] not found", E_USER_ERROR);
		}
		
		// Case 2 : Constructed in memory
		
		else if(is_object($product)) {
			parent::__construct($product, $quantity);
			$this->product = $product;
 			$this->failover = $product;
		}
		
		else parent::__construct();
	}
	
	public function ProductVersioned() {
		return Versioned::get_version('Product', $this->ProductID, $this->ProductVersion);
	}
	
	function getProduct() {return $this->product;}
	
	// Functions to overload
	
	function hasSameContent($orderItem) {
		$equals = parent::hasSameContent($orderItem);
		return $equals && $orderItem instanceof Product_OrderItem && $this->product->ID == $orderItem->product->ID;
	}
	
	function UnitPrice() {return $this->product->Price;}
	
	function Title() {return $this->product->Title;}
	function Link() {return $this->product->Link();}
	
	/*
	public function AjaxQuantityField() {
		if($this->failover->hasMethod('AjaxQuantityField'))	return $this->failover->AjaxQuantityField();
		else return null;
	}
	
	public function addToCart($items = 1) {
   	$this->quantity += $items;
	}
		
	protected $cart;
		
	function ThumbnailLink(){
		$image = $this->product->Image();

		return Director::AbsoluteBaseURL().$image->Filename;
	}

	//-----------------------------------------------------------------------------------------//
	function getTotal() {
		return $this->__get("UnitPrice") * $this->__get("Quantity");
	} */
			
	// Database Writing Methods
	
	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->ProductVersion = $this->product->Version;
		$this->ProductID = $this->product->ID;
	}
	
	// Debug Function
		
	public function debug() {
		if($this->ID) {
			$productTitle = $this->ProductVersioned()->Title;
			$productID = $this->ProductID;
			$productVersion = $this->ProductVersion;
		}
		else {
			$productTitle = $this->product->Title;
			$productID = $this->product->ID;
			$productVersion = $this->product->Version;
		}
		return parent::debug() .<<<HTML
			<h3>Product_OrderItem class details</h3>
			<p>
				<b>Product Title : </b>$productTitle<br/>
				<b>Product ID : </b>$productID<br/>
				<b>Product Version : </b>$productVersion
			</p>
HTML;
	}
}

?>
