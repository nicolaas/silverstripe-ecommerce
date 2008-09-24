<?php

/** 
 * The OrderModifier class is a databound object for
 * handling the additional charges or deductions of
 * an order.
 * 
 * @package ecommerce
 */
class OrderModifier extends OrderAttribute {

	static $db = array(
		'Amount' => 'Currency',
		'Type' => "Enum('Chargable,Deductable')"
	);
	
	/**
	 * @TODO Describe this variable's role in
	 * relation to the $db Type field.
	 *
	 * @var boolean
	 */
	protected static $is_chargable = true;
	
	/*
	 * This function is called when the order initialises
	 * it's modifiers. It is better than directly
	 * constructing the modifier in the Order class
	 * because the user may need to create several
	 * modifiers or customize it.
	 * 
	 * @TODO Write a better description for this function
	 * than the one above. It's not easy to understand.
	 */
	public static function init_for_order($className) {
		$modifier = new $className();
		ShoppingCart::add_new_modifier($modifier);
	}
	
	/**
	 * This function must be called all the time we want
	 * the amount value because it checks if the order
	 * modifier already exists in the DB. In That case,
	 * it returns the Amount value. Otherwise, it returns
	 * the calculation based on the live order and its items.
	 * 
	 * @TODO Write a better description for this function
	 * than the one above. It's not easy to understand.
	 */
	function Amount() {
		return $this->ID ? $this->Amount : $this->LiveAmount();
	}
	
	/**
	 * This function returns the amount of the modifier
	 * based on the current order and its items.
	 * 
	 * @TODO Does this return the total?
	 */
	protected function LiveAmount() {
		return 0;
	}
	
	/**
	 * @TODO Write a description of what this method does.
	 *
	 * @return boolean
	 */
	function IsChargable() {
		return $this->ID ? $this->Type == 'Chargable' : $this->stat('is_chargable');
	}
	
	/**
	 * @TODO Write a description of what this method does.
	 *
	 * @return unknown
	 */
	function TableTitle() {
		return 'Modifier';
	}
	
	function TotalNice() {
		$amount = DBField::create('Currency', $this->Amount())->Nice();
		return ($this->IsChargable() ? '' : '- ') . $amount;
	}
	
	/**
	 * @TODO Write a description of what this method does.
	 *
	 * @return boolean
	 */
	function CanRemove() {
		return !$this->stat('is_chargable');
	}
	
	/**
	 * @TODO Write a description of what this method does.
	 *
	 * @return boolean
	 */
	function Total() {
		$amount = $this->Amount();
		return ($this->IsChargable() ? 1 : -1) * $amount;
	}
	
	function updateForAjax(array &$js) {
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $this->TotalNice());
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $this->TotalNice());
		$js[] = array('id' => $this->TableTitleID(), 'parameter' => 'innerHTML', 'value' => $this->TableTitle());
	}
	
	function removeLink() {
		return ShoppingCart_Controller::remove_modifier_link($this->_id);
	}
	
	static function show_form() {
		return false;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $controller
	 * @return unknown
	 */
	static function get_form($controller) {
		return new OrderModifierForm($controller, 'ModifierForm', new FieldSet(), new FieldSet());
	}
	
	/**
	 * Precondition : The order item is not saved in the database yet
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Amount = $this->Amount();
		$this->Type = $this->stat('is_chargable') ? 'Chargable' : 'Deductable';
	}
	
	/**
	 * Debug helper method.
	 */
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
