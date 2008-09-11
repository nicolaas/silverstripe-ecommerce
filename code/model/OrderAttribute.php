<?php

/**
 * OrderAttribute is an attribute which makes up
 * an Order. {@see OrderModifier} and
 * {@see OrderItem} for implementations of it.
 * 
 * @package ecommerce
 */
class OrderAttribute extends DataObject {
	
	protected $_id;
	
	static $has_one = array(
		'Order' => 'Order'
	);
	
	static $casting = array(
		'TableTitle' => 'Text',
		'CartTitle' => 'Text'
	);
	
	// Local ID Attribute Management
	
	public function getIdAttribute() {
		return $this->_id;
	}
	
	public function setIdAttribute($id) {
		$this->_id = $id;
	}
	
	// Order Function Access
	
	function Order() {
		if($this->ID) return DataObject::get_by_id('Order', $this->OrderID);
		else return ShoppingCart::current_order();
	}
	
	// Display Functions
	
	function Classes() {
		$class = get_class($this);
		$classes[] = strtolower($class);
		while(get_parent_class($class) != 'DataObject' && $class = get_parent_class($class)) $classes[] = strtolower($class);
		return implode(' ', $classes);
	}
	
	function MainID() {
		return get_class($this) . '_' . ($this->ID ? 'DB_' . $this->ID : $this->_id);
	}
	
	function TableID() {
		return 'Table_' . $this->MainID();
	}
	
	function CartID() {
		return 'Cart_' . $this->MainID();
	}
	
	function ShowInTable() {
		return true;
	}
	
	function ShowInCart() {
		return $this->ShowInTable();
	}
	
	function TableTitleID() {
		return $this->TableID() . '_Title';
	}
	
	function CartTitleID() {
		return $this->CartID() . '_Title';
	}
	
	function TableTitle() {
		return 'Attribute';
	}
	
	function CartTitle() {
		return $this->TableTitle();
	}
	
	function Link() {
		return null;
	}
	
	function TableTotalID() {
		return $this->TableID() . '_Total';
	}
	
	function CartTotalID() {
		return $this->CartID() . '_Total';
	}

}

?>