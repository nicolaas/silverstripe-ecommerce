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
	 * This determines whether the OrderModifierForm
	 * is shown or not. {@link OrderModifier::get_form()}.
	 *
	 * @return boolean
	 */
	static function show_form() {
		return false;
	}
	
	/**
	 * This function returns a form that allows a user
	 * to change the modifier to the order.
	 *
	 * @param Controller $controller $controller The controller
	 * @return OrderModifierForm or subclass
	 */
	static function get_form($controller) {
		return new OrderModifierForm($controller, 'ModifierForm', new FieldSet(), new FieldSet());
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
	 * If the current instance of this OrderModifier
	 * exists in the database, check if the Type in
	 * the DB field is "Chargable", if it is, return
	 * true, otherwise check the static "is_chargable",
	 * since this instance currently isn't in the DB.
	 *
	 * @return boolean
	 */
	function IsChargable() {
		return $this->ID ? $this->Type == 'Chargable' : $this->stat('is_chargable');
	}
	
	/**
	 * This is the name of the attribute.
	 * In which case, it's the modifier.
	 *
	 * @return string
	 */
	function TableTitle() {
		return 'Modifier';
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
		$amount = $this->obj('Amount')->Nice();
		
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $amount);
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $amount);
		$js[] = array('id' => $this->TableTitleID(), 'parameter' => 'innerHTML', 'value' => $this->TableTitle());
	}
	
	function removeLink() {
		return ShoppingCart_Controller::remove_modifier_link($this->_id);
	}
	
	/**
	 * Before this OrderModifier is written to
	 * the database, we set some of the fields
	 * based on the way it was set up
	 * {@link OrderModifier::is_chargable()}.
	 * 
	 * Precondition: The order item is not
	 * saved in the database yet.
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
