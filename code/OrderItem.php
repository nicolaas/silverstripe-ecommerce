<?php

/** 
 * An order item is a product which has been added to an order, 
 * ready for purchase. An order item is typically a product itself,
 * but also can include references to other information such as 
 * product attributes like colour, size, or type.
 */
class OrderItem extends DataObject {
	
	protected $id;
	protected $quantity;
	protected $countID; // Not sure to keep it !

	static $db = array(
		'Quantity' => 'Int'
	);
		
	static $has_one = array(
		'Order' => 'Order'
	);
	
	static $casting = array(
		'UnitPrice' => 'Currency',
		'Total' => 'Currency'
	);
	
	public function __construct($product = null, $quantity = 1) {		
		if(is_array($product)) { // Constructed by the static function get of DataObject
  			$this->quantity = $product['Quantity'];
  			parent::__construct($product);  			
		}
		else { // Constructed in memory
			parent::__construct();
			$this->quantity = $quantity;
		}
	}
			
	// Functions to overload
	
	function hasSameContent($orderItem) {return $orderItem instanceof Order_Item;}
	
	function UnitPrice() {return 0;}
	
	function Title() {return 'Product';}
	function Link() {return null;}
			
	// Functions not to overload
	
	public function getId() {return $this->id;}
	public function setId($id) {$this->id = $id;}
	public function getQuantity() {return $this->quantity;}
	public function setQuantity($quantity) {$this->quantity = $quantity;}
	public function addQuantity($quantity) {$this->quantity += $quantity;}
	function getCountID() {return $this->countID;}
	function setCountID($countId) {$this->countID = $countId;}
	
	function ClassForTable() {
		$class = get_class($this);
		$classes[] = strtolower($class);
		while(get_parent_class($class) != 'DataObject' && $class = get_parent_class($class)) $classes[] = strtolower($class);
		return implode(' ', $classes);
	}
	
	function AjaxQuantityField() {
		Requirements::javascript('ecommerce/javascript/AjaxQuantity.js');
		$quantityID = $this->IDForTable() . '_Quantity';
		$setQuantityLinkID = $quantityID . '_SetQuantityLink';
		$setQuantityLink = $this->setquantityLink();
		return <<<HTML
			<input id="$quantityID" class="ajaxQuantityField" type="text" value="$this->quantity" size="3" maxlength="3" disabled="disabled"/>
			<input id="$setQuantityLinkID" type="hidden" value="$setQuantityLink"/>
HTML;
	}
		
	function Total() {return $this->UnitPrice() * $this->quantity;}
	
	protected function MainID() {return 'OrderItem_' . ($this->ID ? $this->ID : $this->id);}
		
	function IDForTable() {return 'Table_' . $this->MainID();}
	function TotalIDForTable() {return $this->IDForTable() . '_Total';}
	
	function IDForCart() {return 'Cart_' . $this->MainID();}
	function TotalIDForCart() {return $this->IDForCart() . '_Total';}
	function QuantityIDForCart() {return $this->IDForCart() . '_Quantity';}
	
	function updateForAjax(array &$js) {
		$js[$this->TotalIDForTable()] = $this->Total();
		$js[$this->TotalIDForCart()] = $this->Total();
		$js[$this->QuantityIDForCart()] = $this->getQuantity();
	}
	
	function addLink() {return ShoppingCart_Controller::additemLink($this->id);}
	function removeLink() {return ShoppingCart_Controller::removeitemLink($this->id);}
	function removeallLink() {return ShoppingCart_Controller::removeallitemLink($this->id);}
	function setquantityLink() {return ShoppingCart_Controller::setquantityLink($this->id);}
	
	// Database Writing Methods
	
	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Quantity = $this->quantity;
	}
	
	// Debug Function
	
	public function debug() {
		$id = $this->ID ? $this->ID : $this->id;
		$quantity = $this->ID ? $this->Quantity : $this->quantity;
		$orderID = $this->ID ? $this->OrderID : 'The order has not been saved yet, so there is no ID';
		return <<<HTML
			<h2>$this->class</h2> 
			<h3>OrderItem class details</h3>
			<p>
				<b>ID : </b>$id<br/>
				<b>Quantity : </b>$quantity<br/>
				<b>Order ID : </b>$orderID
			</p>
HTML;
	}
}

?>
