<?php

class PayPalPayment extends Payment {
	function getPaymentFormFields() {
		return null;
	}
	function getPaymentFormRequirements() {
		return null;
	}
	
	function processPayment($data, $form) {
		Director::redirect(CheckoutPage::find_link() . "ConfirmPayPal/$order->ID");
	}
}

class PayPalPayment_Handler extends Controller {
	/**
	 * Only For PayPal type payment, for dealing with reply from PayPal
	 */
	function paid(){
		$order = DataObject::get_by_id("Order", $_REQUEST[invoice]);
			
		if($_REQUEST['payment_status']=='Completed'){
			
			$order->Status = 'paid';
			$order->write();
			
			// Save payment data from form and process payment
			$payment = Object::create("Payment");
			$payment->Amount = $_REQUEST[mc_gross];
			//$payment->GST = $_REQUEST[tax];
			$payment->OrderID = $order->ID;
			$payment->Status = 'Success';
			$payment->Currentcy = $_REQUEST[mc_currency];
			$payment->PaymentMethod = 'PayPal';
			$payment->DpsTxnRef = $_REQUEST[txn_id];
			
			$paymentID = $payment->write();
	  		$_SESSION['Order']['OrderID'] = $order->ID;
			$_SESSION['Order']['PurchaseComplete'] = true;
				
			$order->sendReceipt();
			$order->isComplete();
			Director::redirect(CheckoutPage::find_link() . "OrderSuccessful/$order->ID");
		}else{
			Director::redirect(CheckoutPage::find_link() . "ConfirmPayPal/$order->ID");
		}
	}
}

?>