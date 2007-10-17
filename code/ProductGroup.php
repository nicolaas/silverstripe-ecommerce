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
	function Cart(){
		return Order::ShoppingCart();
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
	 * Return children from the stage site
	 * @param showAll Inlcude all of the elements, even those not shown in the menus.
	 */
	public function stageChildren($showAll = false) {
		if($showAll) $filter = " AND ShowInMenus = 1";
		
		$productGroups = DataObject::get("ProductGroup","ParentID = $this->ID" . $filter);
		if(!$productGroups) 
			$productGroups = new DataObjectSet();
			
		$childproducts = $this->childProducts();
		if($childproducts){
			if($childproducts->Count()){
				foreach($childproducts as $product){
					$productGroups->push($product);
				}
			}
		}
		return $productGroups;
	}

	/**
	 * Return children from the live site, if it exists.
	 * @param showAll Inlcude all of the elements, even those not shown in the menus.
	 */
	public function liveChildren($showAll = false) {
		if($showAll) $filter = " AND ShowInMenus = 1";
		
		$productGroups = Versioned::get_by_stage("ProductGroup","Live","ParentID = $this->ID","Sort");
		if(!$productGroups) 
			$productGroups = new DataObjectSet();
			
		$childproducts = $this->childProducts();
		if($childproducts){
			if($childproducts->Count()){
				foreach($childproducts as $product){
					$productGroups->push($product);
				}
			}
		}
		return $productGroups;
	}

	/**
	 * Returns the Products as children of the current page.
	 */
	public function childProducts() {
		return DataObject::get("Product", "ShowInMenus = 1 AND ParentID = " . $this->ID);
	}
	
	/**
	 * This only accidentally works; it's all a bit dodgy.
	 * @TODO - re-work this
	 **/
	public function numChildren() {
		$stageChildren = $this->stageChildren();
		$liveChildren = $this->liveChildren();
		return $stageChildren ? $stageChildren->Count() : 0 + $liveChildren ?  $liveChildren->Count() : 0;
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
		Requirements::javascript('ecommerce/javascript/ProductGroup.js');

		Requirements::themedCSS('ProductGroup');
		Requirements::themedCSS('Cart');
		
		parent::init();
	}

}

?>
