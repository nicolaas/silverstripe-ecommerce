<?php

/**
 * @package ecommerce
 */
 
/** 
 * The OrderModifier class is a databound object for handling the additionale charges or deductions of an order.
 */
 
class OrderModifier extends Order_Attribute {

	static $db = array(
		'Amount' => 'Currency',
		'Type' => "Enum(array('Chargable','Deductable'))"
	);
	
	protected static $is_chargable = true;
	
	/*
	 * This function is called when the order inits its modifiers.
	 * It is better than directly construct the modifier in the Order class because, the user may need to create several modifiers or customize it.
	 */
	public static function init_for_order($className) {
		$modifier = new $className();
		ShoppingCart::add_new_modifier($modifier);
	}
	
	//1) Attributes Functions Access
	
	/*
	 * This function must be called all the time we want the amount value because it checks if the order modifier already exists in the DB. In That case, it returns the Amount value.
	 * Otherwise, it returns the calculation based on the live order and its items.
	 */
	function Amount() {return $this->ID ? $this->Amount : $this->LiveAmount();}
	
	/*
	 * This function returns the amount of the modifier based on the current order and its items.
	 */
	protected function LiveAmount() {return 0;}
	
	function IsChargable() {return $this->ID ? $this->Type == 'Chargable' : $this->stat('is_chargable');}
		
	// Display Functions
	
	function TableTitle() {return 'Modifier';}
	
	function TotalNice() {
		$amount = DBField::create('Currency', $this->Amount())->Nice();
		return ($this->IsChargable() ? '' : '- ') . $amount;
	}
	
	// Functions not to overload
	
	function Total() {
		$amount = $this->Amount();
		return ($this->IsChargable() ? 1 : -1) * $amount;
	}
	
	function updateForAjax(array &$js) {
		$js[] = array('id' => $this->ValueIdForCart(), 'parameter' => 'innerHTML', 'value' => $this->ValueForCart());
		$js[] = array('id' => $this->ValueIdForTable(), 'parameter' => 'innerHTML', 'value' => $this->ValueForTable());
		$js[] = array('id' => $this->TableTitleID(), 'parameter' => 'innerHTML', 'value' => $this->TitleForTable());
	}
	
	// Form Functions
	
	static function show_form() {return false;}
	
	static function get_form($controller) {
		return new OrderModifierForm($controller, 'ModifierForm', new FieldSet(), new FieldSet());
	}
	
	// Database Writing Function
		
	/*
	 * Precondition : The order item is not saved in the database yet
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Amount = $this->Amount();
		$this->Type = $this->stat('is_chargable') ? 'Chargable' : 'Deductable';
	}
	
	// Debug Function
	
	public function debug() {
		$id = $this->ID ? $this->ID : $this->_id;
		$amount = $this->Amount();
		$type = $this->IsChargable() ? 'Chargable' : 'Deductable';
		$orderID = $this->ID ? $this->OrderID : 'The order has not been saved yet, so there is no ID';
		return <<<HTML
			<h2>$this->class</h2> 
			<h3>OrderModifier class details</h3>
			<p>
				<b>ID : </b>$id<br/>
				<b>Amount : </b>$amount<br/>
				<b>Type : </b>$type<br/>
				<b>Order ID : </b>$orderID
			</p>
HTML;
	}
}

?>
