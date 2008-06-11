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
		return ShoppingCart::current_order();
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
	
	/**
	 * Creates automatically two product group pages when the ecommerce module
	 * is added to a project
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		if(! DataObject::get_one('ProductGroup')) {
			$page1 = new ProductGroup();
			$page1->Title = 'Products';
			$page1->Content = "
				<p>This is the top level products page, it uses the <em>product group</em> page type, and it allows you to show your products checked as 'featured' on it. It also allows you to nest <em>product group</em> pages inside it.</p>
				<p>For example, you have a product group called 'DVDs', and inside you have more product groups like 'sci-fi', 'horrors' or 'action'.</p>
				<p>In this example we have setup a main product group (this page), with a nested product group containing 2 example products.</p>
			";
			$page1->URLSegment = 'products';
			$page1->writeToStage('Stage');
			$page1->publish('Stage', 'Live');
			Database::alteration_message('Product group page \'Products\' created', 'created');
			
			$page2 = new ProductGroup();
			$page2->Title = 'Example product group';
			$page2->Content = '<p>This is a nested <em>product group</em> within the main <em>product group</em> page. You can add a paragraph here to describe what this product group is about, and what sort of products you can expect to find in it.</p>';
			$page2->URLSegment = 'example-product-group';
			$page2->ParentID = $page1->ID;
			$page2->writeToStage('Stage');
			$page2->publish('Stage', 'Live');
			Database::alteration_message('Product group page \'Example product group\' created', 'created');
		}
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

		Requirements::themedCSS('ProductGroup');
		Requirements::themedCSS('Cart');
		
		parent::init();
	}

}

?>
