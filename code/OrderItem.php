<?php

/** 
 * An order item is a product which has been added to an order, 
 * ready for purchase. An order item is typically a product itself,
 * but also can include references to other information such as 
 * product attributes like colour, size, or type.
 */
class OrderItem extends Order_Attribute {
	
	protected $_quantity;
	protected $countID; // Not sure to keep it !

	static $db = array(
		'Quantity' => 'Int'
	);
	
	static $casting = array(
		'UnitPrice' => 'Currency',
		'Total' => 'Currency'
	);
	
	public function __construct($object = null, $quantity = 1) {		
		
		// Case 1 : Constructed by the static function get of DataObject
		
		if(is_array($object)) {
			$this->_quantity = $object['Quantity'];
			parent::__construct($object);
		}
		
		// Case 2 : Constructed in memory
		
		else {
			parent::__construct();
			$this->_quantity = $quantity;
		}
	}
			
	// Functions to overload
	
	function hasSameContent($orderItem) {return $orderItem instanceof OrderItem;}
	
	function UnitPrice() {return 0;}
	
	function Title() {return 'Product';}
	function Link() {return null;}
			
	// Functions not to overload
	
	public function getQuantity() {return $this->_quantity;}
	public function setQuantityAttribute($quantity) {$this->_quantity = $quantity;}
	public function addQuantityAttribute($quantity) {$this->_quantity += $quantity;}
	function getCountID() {return $this->countID;}
	function setCountID($countId) {$this->countID = $countId;}
		
	protected function AjaxQuantityFieldName() {return $this->MainID() . '_Quantity';}
	
	function AjaxQuantityField() {
		Requirements::javascript('ecommerce/javascript/AjaxQuantity.js');
		$quantityName = $this->AjaxQuantityFieldName();
		$setQuantityLinkName = $quantityName . '_SetQuantityLink';
		$setQuantityLink = $this->setquantityLink();
		return <<<HTML
			<input name="$quantityName" class="ajaxQuantityField" type="text" value="$this->_quantity" size="3" maxlength="3" disabled="disabled"/>
			<input name="$setQuantityLinkName" type="hidden" value="$setQuantityLink"/>
HTML;
	}
		
	function Total() {return $this->UnitPrice() * $this->_quantity;}
	
	function TotalIDForTable() {return $this->IDForTable() . '_Total';}
	
	function TotalIDForCart() {return $this->IDForCart() . '_Total';}
	function QuantityIDForCart() {return $this->IDForCart() . '_Quantity';}
	
	function updateForAjax(array &$js) {
		$total = DBField::create('Currency', $this->Total())->Nice();
		$js[] = array('id' => $this->TotalIDForTable(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->TotalIDForCart(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->QuantityIDForCart(), 'parameter' => 'innerHTML', 'value' => $this->getQuantity());
		$js[] = array('name' => $this->AjaxQuantityFieldName(), 'parameter' => 'value', 'value' => $this->getQuantity());
	}
	
	function addLink() {return ShoppingCart_Controller::add_item_link($this->_id);}
	function removeLink() {return ShoppingCart_Controller::remove_item_link($this->_id);}
	function removeallLink() {return ShoppingCart_Controller::remove_all_item_link($this->_id);}
	function setquantityLink() {return ShoppingCart_Controller::set_quantity_item_link($this->_id);}
	function checkoutLink() {return CheckoutPage::find_link();}
	
	// Database Writing Methods
	
	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Quantity = $this->_quantity;
	}
	
	// Debug Function
	
	public function debug() {
		$id = $this->ID ? $this->ID : $this->_id;
		$quantity = $this->ID ? $this->Quantity : $this->_quantity;
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
