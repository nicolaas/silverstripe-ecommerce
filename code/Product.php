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

	static $icon = "cms/images/treeicons/book";

	static $db = array(
		"Price" => "Currency",
		"Weight" => "Decimal(9,2)",
		"Model" => "Varchar",
		"FeaturedProduct" => "Boolean",
		"AllowPurchase" => "Boolean"
	);
	
	protected $cart;

	/**
	 * Image Support 
	 */
	static $has_one = array(
		"Image" => "Product_Image"
	);
	
	/** 
	 * Allows the product to have many parents, and many attrubutes
	 */
	static $many_many = array(
		//"Attribute" => "ProductAttribute"
		//"Parents" => "SiteTree"
	);

	/**
	 * A ProductGroup can have many Products as children
	 */
	/*static $belongs_many_many = array(
		"NestedProducts" => "Product"
	);*/

	/** 
	 * Allows this product to know which
	 * order it has been added to
	 */
	function setCart($cart) {
		$this->cart = $cart;
	}
	
	/**
	 * Create the fields for a product within the CMS
	 */
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		// standard extra fields like weight and price
		$fields->addFieldToTab("Root.Content.Main", new TextField("Weight", "Weight (kg)", "", 12));
		$fields->addFieldToTab("Root.Content.Main", new TextField("Price", "Price", "", 12));
		$fields->addFieldToTab("Root.Content.Main", new TextField("Model", "Author", "", 50));
		
		// product image field
		$fields->addFieldToTab("Root.Content.Images", new ImageField("Image", "Product Image"));

		// flags for this product which affect it's behaviour on the site
		$fields->addFieldToTab("Root.Content.Main", new CheckboxField("FeaturedProduct", "Featured Product"));
		$fields->addFieldToTab("Root.Content.Main", new CheckboxField("AllowPurchase", "Allow product to be purchased",1));

		// this is to allow the product to have multiple product group parents or 'categories'
		
		// disabled - this isn't working and it's a bit buggy especially, let's turn it off for now until we find an
		// idea solution here.
		//$fields->addFieldToTab("Root.Content.Main", new TreeMultiselectField("Parents", "Product Groups","SiteTree"));
		return $fields;
	}

	/**
	 * Returns the shopping cart
	 */
	function Cart(){
		return Order::ShoppingCart();
	}

	/**
	 * Conditions for whether a product can be purchased.
	 * If it has the checkbox for 'Allow this product to be purchased',
	 * as well as having a price. Otherwise a user can't buy it.
	 */
	function AllowPurchase() {
		if($this->AllowPurchase && $this->Price) {
			return true;
		}
	}
	
	/** 
	 * Sets the ParentID for the component set
	 * which handles our "multiple parents"
	 */
	/*function setParentID($id) {	
		// if ParentID exists, delete it from the Product_Parents table
		if($parentToDelete = $this->getField('ParentID')){
			$this->Parents()->remove($parentToDelete);
		}
		
		// Add the new parent to the Product_Parents table	
		$parents = $this->Parents();
		$parents->add($id);
		
		// Set the ParentID on the SiteTree object for new and reorganise behaviour
		$this->setField('ParentID', $id);
	}*/
	
	/**
	 * Called when we try and set the Parents() component set
	 * by Tree Multiselect Field in the administration.
	 */
	/*function onChangeParents(&$items) {
		// This ensures this product can never be a parent of itself
		if($items){
			foreach($items as $k => $id){
				if($id == $this->ID)
					unset($items[$k]);
			}
		}	
		return true;
	}*/
	
	/**
	 * Return children from the stage site
	 * @param showAll Inlcude all of the elements, even those not shown in the menus.
	 */
	/*
	public function stageChildren($showAll = false) {
		return $this->NestedProducts();
		
		if($showAll) $filter = " AND ShowInMenus = 1";
		$productGroups = DataObject::get("ProductGroup","ParentID = $this->ID" . $filter);

		if(!$productGroups) 
			$productGroups = new DataObjectSet();
		
		$childproducts = $this->ChildProducts();
		if($childproducts){
			if($childproducts->Count()){
				foreach($childproducts as $product){
					$productGroups->push($product);
				}
			}
			return $productGroups;
		}
	}
	*/

	/**
	 * Return children from the live site, if it exists.
	 * @param showAll Inlcude all of the elements, even those not shown in the menus.
	 */
	/*
	public function liveChildren($showAll = false) {
		return $this->NestedProducts();

		if($showAll) $filter = " AND ShowInMenus = 1";
		
		$productGroups = Versioned::get_by_stage("ProductGroup","Live","ParentID = $this->ID","Sort");
		if(!$productGroups) 
			$productGroups = new DataObjectSet();
			
		$childproducts = $this->ChildProducts();
		if($childproducts){
			if($childproducts->Count()){
				foreach($childproducts as $product){
					$productGroups->push($product);
				}
			}
		}
		return $productGroups;
	}
	*/

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
	
	/**
	 * This only accidentally works; it's all a bit dodgy.
	 */
	/*
	public function numChildren() {
		return $this->NestedProducts()->count();
		$stageChildren = $this->stageChildren();
		$liveChildren = $this->liveChildren();
		return $stageChildren ? $stageChildren->Count() : 0 + $liveChildren ?  $liveChildren->Count() : 0;
	}
	*/

	/**
	 * Return the classes to appear on this node in the CMS tree.
	 * We add 'manyparents' to indicate that this node may appear more than
	 * once in the tree.
	 */
	/*function CMSTreeClasses($controller) {
		return parent::CMSTreeClasses($controller) . ' manyparents';
	}*/
		
	function ShoppingCart(){
		$order = Order::create();
		$order->changeToShoppingCart();
		return $order;
	}
	
	/**
	 * If this object is reorganised in the CMS, we need to update the Parents field,
	 * otherwise the order will be messed up when we next press save
	 */
	/*function cmsCleanup_parentChanged() {
		$parents = $this->Parents();
		foreach($parents as $parent) $parentIDs[] = $parent->ID;
		$parentVal = implode(",", $parentIDs);
		
		return parent::cmsCleanup_parentChanged() . "
			$('Form_EditForm').elements.Parents.treeDropdownField.setValue('$parentVal');		
		";
	}*/
	
	/**
	 * Return a field that will update the shopping cart using ajax when updated
	 */
	public function AjaxQuantityField() {
		$sc = Order::ShoppingCart();
		if($items = $sc->Items()) {
			foreach($items as $productID => $Quantity) {
				if(is_object($Quantity)) {
					if($Quantity->ProductID == $this->ID) {
						$setQuantity = $Quantity->Quantity;
					}
				} else {
					if($productID == $this->ID) {
						$setQuantity = $Quantity;
					}
				}
			}
		}
		return "<input class=\"ajaxQuantityField product-$this->ID\" type=\"text\" value=\"$setQuantity\" size=\"3\" maxlength=\"3\" disabled=\"disabled\" />";
	}
	
	/**
	 * Returns the quantity of the current product in your cart
	 */
	function Quantity() {
		$order = Order::ShoppingCart();
		if($items = $order->Items()) {
			foreach($items as $item) {
				if($item->ProductID == $this->ID) {
					return $item->Quantity;
				}
			}
		} else {
			return false;
		}
	}	

	/**
	 * Returns the quantity field
	 */
	function QuantityField() {	
		$sc = Order::ShoppingCart();
		if($items = $sc->Items()) {
			foreach($items as $item) {
				if($item->ProductID == $this->ID) {
					$setQuantity = $item->Quantity;
				}
			}
		}
		if(!$setQuantity) {
			$setQuantity = 1;
		}
		return new TextField("Quantity", "Copies", $setQuantity, 3);	
	}

	/**
	 * Checks if the product is in the cart or not
	 */
	function IsInCart() {
		$order = Order::ShoppingCart();		
		if($items = $order->Items()) {
			foreach($items as $item) {
				if($item->ProductID == $this->ID) {
					return true;
				}
			}
		} else {
			return false;
		}
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
		return singleton('Order')->TaxInfo();
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
		Requirements::javascript('ecommerce/javascript/Product.js');
		
		Requirements::themedCSS('Product');
		Requirements::themedCSS('Cart');

		parent::init();
	}
	
	/**
	 * This is used by the OrderForm to add more of this product to the current cart.
	 */
	function add() {
		if($this->AllowPurchase && $this->Price) {
			$order = Order::ShoppingCart();
			$order->add($this->data());
			Director::redirectBack();
		} else {
			return false;
		}
	}
	
	
	/**
	 * Adds a product to your cart then redirects you to the checkout
	 */
	function buyNow($data) {
		if($this->AllowPurchase && $this->Price) {
			$order = Order::ShoppingCart();
			$checkout = DataObject::get_one("CheckoutPage");
			$quantity = (int) $_REQUEST['Quantity'];
			if($quantity >= 1) {
				$order->add($this->data(), $quantity);
			} else {
				$this->remove();
			}
			Director::redirect($checkout->Link());			
		} else {
			return false;
		}
	}

	/**
	 * Adds a product to the current shopping cart
	 */
	function addToCart() {
		if($this->AllowPurchase && $this->Price) {
			$order = Order::ShoppingCart();
			$quantity = (int) $_REQUEST['Quantity'];
			if($quantity >= 1) {
				$order->add($this->data(), $quantity);
			} else {
				$this->remove();
			}
			Director::redirectBack();
		} else {
			return false;
		}
	}
	
	/**
	 * Remove product by ID
	 */
	function removeFromCart() {
		//unset($_SESSION['cartContents'][$this->ID]);
		
		$order = Order::ShoppingCart();
		$quantity = (int) $_REQUEST['Quantity'];
		if($quantity >= 1) {
			$order->removeByQuantity($this->data(), $quantity);
		}
	
		Director::redirectBack();
	}
	
	/**
	 * This is used by the OrderForm to remove the item(s) from your cart.
	 */
	function remove(){
		$order = Order::ShoppingCart();
		$order->remove($this->data());
		Director::redirectBack();
	}
	
	/**
	 * Remove all of the current product from the cart.
	 */
	function removeall() {
		$order = Order::ShoppingCart();
		$order->removeall($this);
		Director::redirectBack();
	}
	
	/**
	 * Uses the find_link() method on CheckoutPage to find
	 * the link for the checkout page used on the site.
	 */
	function CheckoutLink() {
		return CheckoutPage::find_link();
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

?>