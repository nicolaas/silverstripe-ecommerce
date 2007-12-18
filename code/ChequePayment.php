<?php

/**
 * @package ecommerce
 */

/**
 * Payment object representing a cheque payment
 */
class ChequePayment extends Payment {
	/**
	 * Process the Cheque payment method
	 */
	function processPayment($data, $form) {
		if(!$this->PaymentMethod)
			$this->PaymentMethod = "Cheque";
			
		if(!$this->Status)
			$this->Status = "Pending";
			
		if(!$this->Message)
			$this->Message = "<p class=\"warningMessage\">"._t("ChequePayment.MESSAGE","Payment accepted via Cheque. Please note: products will not be shipped until payment has been received.")."</p>";
		
		$result['Success'] = "Success";
		$result['PaymentID'] = $this->write();
		return $result;
	}
	
	function getPaymentFormFields() {
		return new FieldSet(
			// retrieve cheque content from the ChequeContent() method on this class
			new LiteralField("Chequeblurb", '<div id="Cheque" class="typography">' . $this->ChequeContent() . '</div>'),
			new HiddenField("Cheque", "Cheque", 0)
		);
	}
	function getPaymentFormRequirements() {
		return null;
	}

	/**
	 * Returns the Cheque content from the CheckoutPage
	 */
	function ChequeContent() {
		return DataObject::get_one('CheckoutPage')->ChequeMessage;
	}
	
	/**
	 *  Function used for in template to check if payment is cheque.
	 */
	function IsCheque(){
		return true;
	}
	
}

?>
