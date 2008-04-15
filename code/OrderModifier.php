<?php
/*
 * Created on Apr 11, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
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
	
	public function __construct($order = null) {
		parent::__construct();
		$this->order = $order;
	}
		
	function updateOrderInformationEditableFields(FieldSet &$fields) {
	}
	
	function ClassNameForTable() {
		if($this->ID) return $this->ClassName;
		else return get_class($this);
	}
	function TitleForTable() { return 'Modifier'; }
	function ValueIdForTable() { return 'Cost'; }
	function ValueForTable() { return $this->getValue(); }
	
	function getAmount(Order $order) {
		return 0;
	}
	
	final function getValue() {
		if($this->ID) $order = DataObject::get_by_id('Order', $this->OrderID);
		else $order = $this->order;
		$amount = $this->getAmount($order);
		return (self::$isChargable ? 1 : -1) * $amount;
	}
}

?>
