<?php

/**
 * @package ecommerce
 */
 
/**
 * Product contains the actual individual products
 * data - including extra fields like Price and Weight
 */
class Product extends Page {
	
	static $db = array(
		'Price' => 'Currency',
		'Weight' => 'Decimal(9,2)',
		'Model' => 'Varchar',
		'FeaturedProduct' => 'Boolean',
		'AllowPurchase' => 'Boolean',
		'InternalItemID' => 'Varchar(30)'
	);
	
	static $has_one = array(
		'Image' => 'Product_Image'
	);
	
	static $many_many = array(
		'ProductGroups' => 'ProductGroup'
	);
	
	static $defaults = array(
		'AllowPurchase' => true
	);
	
	static $casting = array();
	
	static $default_parent = 'ProductGroup';
	
	static $add_action = 'a Product Page';
	
	static $icon = 'cms/images/treeicons/book';
	
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

		$productGroupsTable = $this->getCMSProductGroups();
		$fields->addFieldToTab(
			'Root.Content',
			new Tab(
				'Product Groups',
				new HeaderField('Which other groups I want this product to appear in ?'),
				$productGroupsTable
			)
		);

		return $fields;
	}
	
	protected function getCMSProductGroups() {
		$tableField = new ManyManyComplexTableField(
			$this,
			'ProductGroups',
			'ProductGroup',
			array(
				'Title' => 'Product Group Page Title'
			)
		);
		$tableField->setPageSize(30);
		$tableField->setPermissions(array());
		return $tableField;
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
				if($item instanceof Product_OrderItem && $itemProduct = $item->Product()) {
					if($itemProduct->ID == $this->ID && $itemProduct->Version == $this->Version) return $item;
				}
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
	
	/**
	 * Creates automatically two product pages when the ecommerce module is
	 * added to a project
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		if(! DataObject::get_one('Product')) {		
			if(! DataObject::get_one('ProductGroup')) singleton('ProductGroup')->requireDefaultRecords();
			if($group = DataObject::get_one('ProductGroup', '', true, '`ParentID` DESC')) {
				$content = '<p>This is a <em>product</em>. It\'s description goes into the Content field as a standard SilverStripe page would have it\'s content. This is an ideal place to describe your product.</p>';
				
				$page1 = new Product();
				$page1->Title = 'Example product';
				$page1->Content = $content . '<p>You may also notice that we have checked it as a featured product and it will be displayed on the main Products page.</p>';
				$page1->URLSegment = 'example-product';
				$page1->ParentID = $group->ID;
				$page1->Price = '15.00';
				$page1->Weight = '0.50';
				$page1->Model = 'Joe Bloggs';
				$page1->FeaturedProduct = true;
				$page1->writeToStage('Stage');
				$page1->publish('Stage', 'Live');
				Database::alteration_message('Product page \'Example product\' created', 'created');
				
				$page2 = new Product();
				$page2->Title = 'Example product 2';
				$page2->Content = $content;
				$page2->URLSegment = 'example-product-2';
				$page2->ParentID = $group->ID;
				$page2->Price = '25.00';
				$page2->Weight = '1.2';
				$page2->Model = 'Jane Bloggs';
				$page2->writeToStage('Stage');
				$page2->publish('Stage', 'Live');
				Database::alteration_message('Product page \'Example product 2\' created', 'created');		
			}
		}
	}
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
	
	protected $_productID;
	protected $_productVersion;
	
	static $db = array(
		'ProductVersion' => 'Int'
	);
	
	static $has_one = array(
		'Product' => 'Product'
	);
	
	public function __construct($product = null, $quantity = 1) {
		
		// Case 1 : Constructed by the static function get of DataObject
		
		if(is_array($product)) {
			$this->ProductID = $this->_productID = $product['ProductID'];
			$this->ProductVersion = $this->_productVersion = $product['ProductVersion'];
			parent::__construct($product, $quantity);
		}
		
		// Case 2 : Constructed in memory
		
		else if(is_object($product)) {
			parent::__construct($product, $quantity);
			$this->_productID = $product->ID;
 			$this->_productVersion = $product->Version;
		}
		
		else parent::__construct();
	}
	
	// Product Access Function
	
	/*
	 * To DO : Add Lives and Drafts Values Management
	 */
	public function Product($current = false) {
		if($current) return DataObject::get_by_id('Product', $this->_productID);
		else return Versioned::get_version('Product', $this->_productID, $this->_productVersion);
	}
	
	// Functions to overload
	
	function hasSameContent($orderItem) {
		$equals = parent::hasSameContent($orderItem);
		return $equals && $orderItem instanceof Product_OrderItem && $this->_productID == $orderItem->_productID && $this->_productVersion == $orderItem->_productVersion;
	}
	
	function UnitPrice() {return $this->Product()->Price;}
	
	function TableTitle() {return $this->Product()->Title;}
	function Link() {
		if($product = $this->Product(true)) return $product->Link();
	}
				
	// Database Writing Methods
	
	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->ProductID = $this->_productID;
		$this->ProductVersion = $this->_productVersion;
	}
	
	// Debug Function
		
	public function debug() {
		$productTitle = $this->Product()->Title;
		$productID = $this->_productID;
		$productVersion = $this->_productVersion;
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
