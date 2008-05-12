<?php

/**
 * @package ecommerce
 */

/**
 * Payment type to support credit-card payments through DPS.
 * 	
 * Supported currencies: 
 * 	CAD  	Canadian Dollar
 * 	CHF 	Swiss Franc
 * 	EUR 	Euro
 * 	FRF 	French Franc
 * 	GBP 	United Kingdom Pound
 * 	HKD 	Hong Kong Dollar
 * 	JPY 	Japanese Yen
 * 	NZD 	New Zealand Dollar
 * 	SGD 	Singapore Dollar
 * 	USD 	United States Dollar
 * 	ZAR 	Rand
 * 	AUD 	Australian Dollar
 * 	WST 	Samoan Tala
 * 	VUV 	Vanuatu Vatu
 * 	TOP 	Tongan Pa'anga
 * 	SBD 	Solomon Islands Dollar
 * 	PGK 	Papua New Guinea Kina
 * 	MYR 	Malaysian Ringgit
 * 	KWD 	Kuwaiti Dinar
 * 	FJD 	Fiji Dollar
 */	

class DPSPayment extends Payment {
	/**
	 * DPS log-in credentials
	 */
	protected static $dps_username, $dps_password;
	
	/**
	 * This function processes the payment via the DPS communication object
	 */
	function processPayment($data, $form) {
		// DPS still contacted on test/dev, but test credentials used
		if($this->BillingId){
			$details = array(
				'CardHolderName' => $this->CardHolderName,
				'CardNumber' => $this->CreditCardNumber,
				'Amount' => $this->Amount,
				'Currency' => $this->Currency,
				'DateExpiry' => $this->DateExpiry,
				'TxnType' => $this->ManuallyConfirm ? "Auth" : "Purchase",
				'MerchantReference' => "Order".$this->OrderID,
				"EnableDuplicateCheck" => "0",
				"EnableRm" => "1",
				"DpsBillingId" => $this->BillingId,
	  		);
		}else{
			$details = array(
				'CardHolderName' => $this->CardHolderName,
				'CardNumber' => $this->CreditCardNumber,
				'Amount' => $this->Amount,
				'Currency' => $this->Currency,
				'DateExpiry' => $this->DateExpiry ,
				'TxnType' => $this->ManuallyConfirm ? "Auth" : "Purchase",
				'MerchantReference' => "Order".$this->OrderID,
				"EnableDuplicateCheck" => "0",
			
	  		);
		}
  		
		$credentials = array(
			'Username' => self::$dps_username,
			'Password' => self::$dps_password
		);
		
		// This allows $data to be passed directly from the form.
		if(is_array($details['CardNumber'])){
			$details['CardNumber'] = implode("",$this->CreditCardNumber);	
		}
	
	
	  	$result = DPS::pxpost($details, $credentials);
	  	$this->Message = "<p>$result[ResponseText] <br /> $result[HelpText]</p>";

	  	// Get the DPS transaction reference and place it with the rest of the payment information.
		// Fall back to using TxnRef if DpsTxnRef not populated.
		if($result['TxnRef']) {
			$this->TxnRef = $result['TxnRef'];	  	
		}
	  				  	
	  	if($result['Fatal'] ){
	  		 	global $project;
	  	
	  	  		// Server of communication problems	
	  			$e = new Email(
	            "server@silverstripe.com",
	            "support@silverstripe.com",
	            "DPS FATAL ERROR",
	            "<h1> Fatal Error on " . $project ." </h1> <p> <strong> DPS Response: </strong>$result[ResponseText]
	             <br /> <strong>Helptext:</strong> $result[HelpText] </p> $result[MerchantHelpText]</p>"
	        );
	        $e->send();
	  	}
	  	
	  	if($result[Payment::$success]) {
	  		$this->Status = 'Success';
	  		$order = $this->Order();
	  		$order->Status = 'Paid';
	  		$order->write();
	  		
	  		// create a log-entry for it
			$logEntry = new OrderStatusLog();
			$logEntry->OrderID = $order->ID;
			$logEntry->Status = 'Paid';
			$logEntry->write();
	  	} else {
	  		$this->Status = "Failure";
	  		Session::set('Message', $result['HelpText']);
	  	}

		$this->write();
		return $result;
	}
	
	function getPaymentFormFields() {
		return new FieldSet(
			new TextField("CardHolderName", "Card Holder Name:"),
			new CreditCardField("CreditCardNumber", "Credit Card Number:"),
			new NumericField("DateExpiry", "Credit Card Expiry:(MMYY)", "", 4),
			new LiteralField("DPSInfo", '<a href="http://www.paymentexpress.com/privacypolicy.htm" title="Read DPS\' privacy policy"><img src="https://www.paymentexpress.com/dpslogo.gif" alt="Payments powered by DPS" /></a>')
		);
	}

	/**
	 * Returns the required fields to add to the order form, when using this payment method. 
	 */
	function getPaymentFormRequirements() {
			return array(
				"js" => "
					require('CardHolderName');
					require('CreditCardNumber');
					require('DateExpiry');
				",
				"php" => '
					$this->requireField("CardHolderName", $data);
					$this->requireField("CreditCardNumber", $data);
					$this->requireField("DateExpiry", $data);
				',
			);
	}
	
	static function set_account($username, $password) {
		self::$dps_username = $username;
		self::$dps_password = $password;
	}
	
}

?>