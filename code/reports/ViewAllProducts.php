<?php

/**
 *  A controller class for dealing with popup windows with correct content
 */	
class ViewAllProducts extends Controller {
	
	//Default action.
	function index() {
		return $this->renderWith('ViewAllProducts');
	}
	
	function GetAllTips(){
		$id = $this->urlParams[ID];

		if(is_numeric($id)){
			$order = DataObject::get_by_id("Order", $id);
			$order->updatePrinted(true);
			return $order;
		}else{
			
			$orderReport = new OrderReport("OrderReport",null,"",null,"Order");
			$orderReport -> filter_onchange();

			$orders = $orderReport -> getRecords();
			foreach($orders as $order)
				$order->updatePrinted(true);
			
			return $orders;
		}
	}	
	
	/**
	* Get all the products in the system
	*/
	function AllProducts(){
		return DataObject::get("Product","ParentID != -1");	
	}

}

?>