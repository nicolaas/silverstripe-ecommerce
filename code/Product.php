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
		'AllowPurchase' => 'Boolean'
	);

	/**
	 * Image Support 
	 */
	static $has_one = array(
		'Image' => 'Product_Image'
	);

	protected $cart;

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

		return $fields;
	}

	/**
	 * Returns the shopping cart
	 */
	function Cart() {
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

	function ShoppingCart(){
		$order = Order::create();
		$order->changeToShoppingCart();
		return $order;
	}

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
		return (bool) $this->Quantity();
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