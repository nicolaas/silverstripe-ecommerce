<?php

class EcommercePayment extends DataObjectDecorator {
	function extraStatics() {
		return array(
			'has_one' => array(
				'Order' => 'Order'
			)
		);
	}
	
	function onBeforeWrite() {
		if($this->owner->Status == 'Success' && $this->owner->Order()) {
			$order = $this->owner->Order();
			$order->Status = 'Paid';
			$order->write();
			$order->sendReceipt();
		}
	}
	
	function redirectToOrder() {
		$order = $this->owner->Order();
		Director::redirect($order->Link());
		return;
	}
}

?>