<?php
/** 
 * The OrderModifier class is a databound object for
 * handling the additional charges or deductions of
 * an order.
 * 
 * @package ecommerce
 */
class OrderModifier extends OrderAttribute {

	public static $db = array(
		'Amount' => 'Currency',
		'Type' => "Enum('Chargable,Deductable')"
	);
	
	/**
	 * This determines whether the current modifier
	 * is chargable, in that it adds an amount to the
	 * order. An example of when this would be true is
	 * for a shipping or tax calculator.
	 * 
	 * If you set this to false for your modifier, then
	 * it will deduct from the order instead given the
	 * amount returned in {@link OrderModifier->LiveAmount()}.
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
	 * @todo When is this used?
	 * @todo How is this used?
	 * @todo How does one create their own OrderModifierForm implementation?
	 *
	 * @param Controller $controller $controller The controller
	 * @return OrderModifierForm or subclass
	 */
	static function get_form($controller) {
		return new OrderModifierForm($controller, 'ModifierForm', new FieldSet(), new FieldSet());
	}
	
	/**
	 * This function is always called to determine the
	 * amount this modifier needs to charge or deduct.
	 * 
	 * If the modifier exists in the DB, in which case it
	 * already exists for a given order, we just return
	 * the Amount data field from the DB. This is for
	 * existing orders.
	 * 
	 * If this is a new order, and the modifier doesn't
	 * exist in the DB ($this->ID is 0), so we return
	 * the amount from $this->LiveAmount() which is a
	 * calculation based on the order and it's items.
	 */
	function Amount() {
		return $this->ID ? $this->Amount : $this->LiveAmount();
	}
	
	/**
	 * This function returns the amount of the modifier
	 * based on the current order and its items. It's
	 * designed to be overloaded on your OrderModifier
	 * subclass.
	 * 
	 * See SimpleShippingModifier->LiveAmount()
	 * See TaxModifier->LiveAmount()
	 * 
	 * For example, it could produce a tax calculation,
	 * and return a number, which is the amount the
	 * modifier uses to charge or deduct, based on the
	 * setting of {@link OrderModifier::$is_chargable}.
	 */
	protected function LiveAmount() {
		user_error("Please implement LiveAmount() on $this->class", E_USER_ERROR);
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
	 * This describes what the name of the
	 * modifier should be, in relation to
	 * the order table on the check out page
	 * - which the templates uses directly.
	 * 
	 * For example, this could be something
	 * like "Shipping to NZ", where NZ is a
	 * dynamic variable on where the user
	 * currently is, using {@link Geoip}.
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