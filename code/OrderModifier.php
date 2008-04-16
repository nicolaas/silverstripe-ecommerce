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
	
	protected $order;
	
	private static $isChargable = true;
	
	public function __construct(Order $order = null) {
		parent::__construct();
		$this->order = $order;
	}
	
	/*
	 * This function is called when the order inits its modifiers.
	 * It is better than directly construct the modifier in the Order class because, the user may need to create several modifiers or customize it.
	 */
	public static function init_for_order($className, Order $order) {
		$order->addModifier(new $className($order));
	}
	
	/*
	 * This function must be called all the time we want to access the order because it checks if the order already exists in the DB or not
	 */
	function getOrder() {
		if($this->ID) return DataObject::get_by_id('Order', $this->OrderID);
		else return $this->order;
	}
		
	function updateOrderInformationEditableFields(FieldSet &$fields) {
	}
	
	// Functions called from the Cart
	function ShowInCart() {return true;}
	function TitleForCart() {return $this->TitleForTable();}
	function ValueIdForCart() {return 'Cart_' . $this->ValueIdForTable();}
	function ValueForCart() {return $this->ValueForTable();}
	
	// Functions called from the Order table
	function ShowInOrderTable() {return true;}
	function ClassNameForTable() {
		if($this->ID) return $this->ClassName;
		else return get_class($this);
	}
	function TitleForTable() {return 'Modifier';}
	function ValueIdForTable() {return 'Cost';}
	function ValueForTable() {return $this->getValue();}
	
	function getAmount() {
		return 0;
	}
	
	final function getValue() {
		$amount = $this->getAmount();
		return (self::$isChargable ? 1 : -1) * $amount;
	}
}

?>
