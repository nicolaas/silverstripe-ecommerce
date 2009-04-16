<?php
/** 
 * An order item is a product which has been added to an order, 
 * ready for purchase. An order item is typically a product itself,
 * but also can include references to other information such as 
 * product attributes like colour, size, or type.
 * 
 * @package ecommerce
 */
class OrderItem extends OrderAttribute {
	
	protected $_quantity;
	
	static $db = array(
		'Quantity' => 'Int'
	);
	
	static $casting = array(
		'UnitPrice' => 'Currency',
		'Total' => 'Currency'
	);
	
	public function __construct($object = null, $quantity = 1) {		

		// Case 1: Constructed by getting OrderItem from DB
		if(is_array($object)) {
			$this->_quantity = $object['Quantity'];
			parent::__construct($object);
		} else {		
			// Case 2: Constructed in memory
			parent::__construct();
			$this->_quantity = $quantity;
		}
	}
	
	// Functions to overload
	
	function hasSameContent($orderItem) {
		return $orderItem instanceof OrderItem;
	}
	
	function UnitPrice() {
		user_error("Please implement UnitPrice() on $this->class", E_USER_ERROR);
	}
	
	function TableTitle() {
		return 'Product';
	}
	
	function ProductTitle() {
		return $this->Product()->Title;
	}
	
	// Functions not to overload
	
	public function getQuantity() {
		return $this->_quantity;
	}
	
	/*
	 * Precondition : The order item is not saved in the database yet
	 */
	public function setQuantityAttribute($quantity) {
		$this->_quantity = $quantity;
	}
	
	/*
	 * Precondition : The order item is not saved in the database yet
	 */
	public function addQuantityAttribute($quantity) {
		$this->_quantity += $quantity;
	}
	
	protected function AjaxQuantityFieldName() {
		return $this->MainID() . '_Quantity';
	}
	
	function AjaxQuantityField() {
		Requirements::javascript('jsparty/jquery/jquery.js');
		Requirements::javascript('ecommerce/javascript/ecommerce.js');
		$quantityName = $this->AjaxQuantityFieldName();
		$setQuantityLinkName = $quantityName . '_SetQuantityLink';
		$setQuantityLink = $this->setquantityLink();
		return <<<HTML
			<input name="$quantityName" class="ajaxQuantityField" type="text" value="$this->_quantity" size="3" maxlength="3" disabled="disabled"/>
			<input name="$setQuantityLinkName" type="hidden" value="$setQuantityLink"/>
HTML;
	}
	
	// Display Functions
	
	function Total() {
		return $this->UnitPrice() * $this->_quantity;
	}
	
	function CartQuantityID() {
		return $this->CartID() . '_Quantity';
	}
	
	function updateForAjax(array &$js) {
		$total = DBField::create('Currency', $this->Total())->Nice();
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartQuantityID(), 'parameter' => 'innerHTML', 'value' => $this->getQuantity());
		$js[] = array('name' => $this->AjaxQuantityFieldName(), 'parameter' => 'value', 'value' => $this->getQuantity());
	}
	
	function addLink() {
		return ShoppingCart_Controller::add_item_link($this->_id);
	}
	
	function removeLink() {
		return ShoppingCart_Controller::remove_item_link($this->_id);
	}
	
	function removeallLink() {
		return ShoppingCart_Controller::remove_all_item_link($this->_id);
	}
	
	function setquantityLink() {
		return ShoppingCart_Controller::set_quantity_item_link($this->_id);
	}
	
	function checkoutLink() {
		return CheckoutPage::find_link();
	}
	
	// Database Writing Function
	
	/*
	 * Precondition : The order item is not saved in the database yet
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Quantity = $this->_quantity;
	}
	
	// Debug Function
	
	public function debug() {
		$id = $this->ID ? $this->ID : $this->_id;
		$quantity = $this->_quantity;
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