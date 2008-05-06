<?php

/**
 * @package ecommerce
 */
 
 /**
  * Product Group is a 'holder' for Products within the CMS
  * It contains functions for versioning child products
  */
class ProductGroup extends Page {
	
	static $add_action = 'a Product Group Page';
	
	static $casting = array();
	
	static $allowed_children = array('Product', 'ProductGroup');
	
	static $default_child = 'Product';
	
	static $icon = 'cms/images/treeicons/folder';

	static $db = array();

	static $belongs_many_many = array(
		'ChildProducts' => 'Product'
	);

	/**
	 * Returns the shopping cart
	 */
	function Cart() {
		HTTP::set_cache_age(0);
		//return Order::ShoppingCart();
		return CurrentOrder::display_order();
	}

	/**
	 * Return nested/child ProductGroups using the Children function
	 * rather than a DataObject::get call
	 */
	function _ShowProductGroups(){
		if($children = $this->Children()){
			foreach($children as $child){
				if(!is_a($child, "ProductGroup")) return false;
			}
			return true;
		}
	}

	/**
	 * Return all Products in the system that are flagged as 'featured'
	 */
	function FeaturedProducts() {
		return DataObject::get("Product", "ShowInMenus = 1 AND FeaturedProduct = 1");	
	}
	
	/** 
	 * Return ProductGroups as children of the current page
	 */
	function ChildGroups() {
		return DataObject::get("ProductGroup", "ShowInMenus = 1 AND ParentID = " . $this->ID);
	}
	
	/**
	 * Generate a product menu using this function
	 */
	function GroupsMenu() {
		$p = $this->Parent();
		if(!$p->ID || !($p instanceof ProductGroup)) {
			return $this->ChildGroups();
		} else {
			return $p->GroupsMenu();
		}
	}
	
	/**
	 * Returns the Products as children of the current page.
	 */
	public function childProducts() {
		return DataObject::get("Product", "ShowInMenus = 1 AND ParentID = " . $this->ID);
	}
	
	/**
	 * Check if this product group is the top level
	 */
	function IsTopLevel() {
		return (!$this->Parent() || !in_array($this->Parent()->class, $this->stat('allowed_children')));
	}
	
}

class ProductGroup_Controller extends Page_Controller {

	/**
	 * Include the product's requirements, override if the project has the file,
	 * otherwise use the module one instead
	 */
	function init() {
		Requirements::javascript('jsparty/prototype.js');
		Requirements::javascript('jsparty/prototype_improvements.js');
		Requirements::javascript('jsparty/behaviour.js');
		Requirements::javascript('ecommerce/javascript/AjaxQuantity.js');

		Requirements::themedCSS('ProductGroup');
		Requirements::themedCSS('Cart');
		
		parent::init();
	}

}

?>
