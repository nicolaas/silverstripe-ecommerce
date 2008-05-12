<?php

/**
 * @package ecommerce
 */
 
/** 
 * The OrderModifier class is a databound object for handling the additionale charges or deductions of an order.
 */
 
class OrderModifier extends DataObject {

	static $db = array(
		'Amount' => 'Currency',
		'Type' => "Enum(array('Chargable','Deductable'))"
	);
	
	static $has_one = array(
		'Order' => 'Order'
	);
	
	//protected $order;
	
	protected static $is_chargable = true;
	
	//function setOrder(Order $order) {$this->order = $order;}
	
	/*
	 * This function is called when the order inits its modifiers.
	 * It is better than directly construct the modifier in the Order class because, the user may need to create several modifiers or customize it.
	 */
	public static function init_for_order($className) {
		$modifier = new $className();
		ShoppingCart::add_modifier($modifier);
	}
	
	//1) Attributes Functions Access
	
	/*
	 * This function must be called all the time we want the amount value because it checks if the order modifier already exists in the DB. In That case, it returns the Amount value.
	 * Otherwise, it returns the calculation based on the live order and its items.
	 */
	function Amount() {return $this->ID ? $this->Amount : $this->LiveAmount();}
	
	/*
	 * This function returns the amount of the modifier based on the live order and its items.
	 */
	function LiveAmount() {return 0;}
	
	function IsChargable() {return $this->ID ? $this->Type == 'Chargable' : $this->stat('is_chargable');}
	
	/*
	 * This function must be called all the time we want to access the order because it checks if the order already exists in the DB or not
	 */
	function Order() {
		if($this->ID) return DataObject::get_by_id('Order', $this->OrderID);
		else return ShoppingCart::current_order();
	}
	
	//2) Display Functions
		
	// Functions called from the Cart
	function ShowInCart() {return true;}
	function TitleForCart() {return $this->TitleForTable();}
	function ValueIdForCart() {return 'Cart_' . $this->ValueIdForTable();}
	function ValueForCart() {return $this->ValueForTable();}
	
	// Functions called from the Order table
	function ShowInOrderTable() {return true;}
	function ClassNameForTable() {return $this->ID ? $this->ClassName : get_class($this);}
	function TitleIdForTable() {return $this->ValueIdForTable() . '_Title';}
	function TitleForTable() {return 'Modifier';}
	function ValueIdForTable() {return 'Cost';}
	function ValueForTable() {return $this->getValue();}
	
	final function getValue() {
		$amount = $this->Amount();
		return ($this->IsChargable() ? 1 : -1) * $amount;
	}
	
	function updateJavascript(array &$js) {
		$js[$this->ValueIdForCart()] = $this->ValueForCart();
		$js[$this->ValueIdForTable()] = $this->ValueForTable();
		$js[$this->TitleIdForTable()] = $this->TitleForTable();
	}
	
	//3) Database Writing Functions
	
	function update() {}
	
	public function onBeforeWrite() {
		$this->Amount = $this->Amount();
		$this->Type = $this->IsChargable() ? 'Chargable' : 'Deductable';
		/*$this->OrderID = $this->Order()->ID;*/
		parent::onBeforeWrite();
	}
	
	public function writeForStructureChanges() {
		parent::write();
	}
	
	//4) Form Functions
	
	static function show_form(/*Order $order*/) {return false;}
	
	static function get_form(/*Order $order, */CheckoutPage $checkoutPage) {
		return new OrderModifierForm(/*$order, */$checkoutPage, 'ModifierForm', new FieldSet(), new FieldSet());
	}
}

?>
