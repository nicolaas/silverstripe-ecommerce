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

// To do : fix the validation problem on the credit card number and date expiry
// js validation

class DPSPayment extends Payment {
	
	// DPS Informations
	
	protected static $privacy_link = 'http://www.paymentexpress.com/privacypolicy.htm';
	protected static $logo = 'ecommerce/images/payments/dps.gif';
	
	// URLs
	
	protected static $url = 'https://www.paymentexpress.com/pxpost.aspx';
	
	// Payment Informations
	
	protected static $username;
	protected static $password;
	static function set_account($username, $password) {
		self::$username = $username;
		self::$password = $password;
	}
	
	protected static $cvn_mode = true;
	static function unset_cvn_mode() {self::$cvn_mode = false;}
		
	protected static $credit_cards = array(
		'Visa' => 'ecommerce/images/payments/methods/visa.jpg',
		'MasterCard' => 'ecommerce/images/payments/methods/mastercard.jpg',
		'American Express' => 'ecommerce/images/payments/methods/american-express.gif',
		'Dinners Club' => 'ecommerce/images/payments/methods/dinners-club.jpg',
		'JCB' => 'ecommerce/images/payments/methods/jcb.jpg'
	);
	static function remove_credit_card($creditCard) {unset(self::$credit_cards[$creditCard]);}
	
	function getPaymentFormFields() {
		$logo = '<img src="' . self::$logo . '" alt="Credit card payments powered by DPS"/>';
		$privacyLink = '<a href="' . self::$privacy_link . '" target="_blank" title="Read DPS\'s privacy policy">' . $logo . '</a><br/>';
		$paymentsList = '';
		foreach(self::$credit_cards as $name => $image) $paymentsList .= '<img src="' . $image . '" alt="' . $name . '"/>';
		$fields = new FieldSet(
			new LiteralField('DPSInfo', $privacyLink),
			new LiteralField('DPSPaymentsList', $paymentsList),
			new TextField('DPS_CreditCardHolderName', 'Credit Card Holder Name :'),
			new CreditCardField('DPS_CreditCardNumber', 'Credit Card Number :'),
			new TextField('DPS_CreditCardExpiry', 'Credit Card Expiry : (MMYY)', '', 4)
		);
		if(self::$cvn_mode) $fields->push(new TextField('DPS_CreditCardCVN', 'Credit Card CVN : (3 or 4 digits)', '', 4));
		return $fields;
	}

	/**
	 * Returns the required fields to add to the order form, when using this payment method. 
	 */
	function getPaymentFormRequirements() {
		$jsCode = <<<JS
			require('DPS_CreditCardHolderName');
			require('DPS_CreditCardNumber');
			require('DPS_CreditCardExpiry');
JS;
		$phpCode = '
			$this->requireField("DPS_CreditCardHolderName", $data);
			$this->requireField("DPS_CreditCardNumber", $data);
			$this->requireField("DPS_CreditCardExpiry", $data);
		';
		return array('js' => $jsCode, 'php' => $phpCode);
	}
		
	/**
	 * This function processes the payment via the DPS communication object
	 */
/*	function processPaymentOld($data, $form) {
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
	}*/
	
	function processPayment($data, $form) {
		
		// 1) Main Settings
		
		$inputs['PostUsername'] = self::$username;
		$inputs['PostPassword'] = self::$password;
		
		// 2) Payment Informations
		
		$inputs['Amount'] = $this->Amount;
		$inputs['InputCurrency'] = $this->Currency;
		$inputs['TxnId'] = $this->ID;
		$inputs['TxnType'] = 'Purchase';
		
		// 3) Credit Card Informations
		
		$inputs['CardHolderName'] = $data['DPS_CreditCardHolderName'];
		$inputs['CardNumber'] = implode('', $data['DPS_CreditCardNumber']);
		$inputs['DateExpiry'] = $data['DPS_CreditCardExpiry'];
		if(self::$cvn_mode) $inputs['Cvc2'] = $data['DPS_CreditCardCVN'] ? $data['DPS_CreditCardCVN'] : '';
		
		// 4) DPS Transaction Sending
			
  		$responseFields = $this->doPayment($inputs);
		
		// 5) DPS Response Management
		
		if($responseFields['SUCCESS']) {
			$this->Status = 'Success';
			$result = new Payment_Success();
		}
		else {
			$this->Status = 'Failure';
			$result = new Payment_Failure();
		}
		
		if($transactionRef = $responseFields['DPSTXNREF']) $this->TxnRef = $transactionRef;
		
		if($helpText = $responseFields['HELPTEXT']) $this->Message = $helpText;
		else if($responseText = $responseFields['RESPONSETEXT']) $this->Message = $responseText;
		
		$this->write();
		return $result;
	}
	
	function doPayment(array $inputs) {
		
		// 1) Transaction Creation
		
		$transaction = '<Txn>';
		foreach($inputs as $name => $value) $transaction .= "<$name>$value</$name>"; 
		$transaction .= '</Txn>';
		
		// 2) CURL Creation
		
		$clientURL = curl_init(); 
		curl_setopt($clientURL, CURLOPT_URL, self::$url);
		curl_setopt($clientURL, CURLOPT_POST, 1);
		curl_setopt($clientURL, CURLOPT_POSTFIELDS, $transaction);
		curl_setopt($clientURL, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($clientURL, CURLOPT_SSLVERSION, 3);
		
		// 3) CURL Execution
		
		$resultXml = curl_exec($clientURL); 
		
		// 4) CURL Closing
		
		curl_close ($clientURL);
		
		// 5) XML Parser Creation
		
		$xmlParser = xml_parser_create();
		$values = null;
		$indexes = null;
		xml_parse_into_struct($xmlParser, $resultXml, $values, $indexes);
		xml_parser_free($xmlParser);
		
		// 6) XML Result Parsed In A PHP Array
		
		$resultPhp = array();
		$level = array();
		foreach($values as $xmlElement) {
			if($xmlElement['type'] == 'open') {
				if(array_key_exists('attributes', $xmlElement)) list($level[$xmlElement['level']], $extra) = array_values($xmlElement['attributes']);
				else $level[$xmlElement['level']] = $xmlElement['tag'];
			}
			else if ($xmlElement['type'] == 'complete') {
				$startLevel = 1;
				$phpArray = '$resultPhp';
				while($startLevel < $xmlElement['level']) $phpArray .= '[$level['. $startLevel++ .']]';
				$phpArray .= '[$xmlElement[\'tag\']] = $xmlElement[\'value\'];';
				eval($phpArray);
			}
		}
		
		$result = $resultPhp['TXN'];
		
		return $result;
	}
}

/*$MerchantReference = $params[TXN][$success][MERCHANTREFERENCE];
$CardHolderName	= $params[TXN][$success][CARDHOLDERNAME];
$AuthCode = $params[TXN][$success][AUTHCODE];
$Amount	= $params[TXN][$success][AMOUNT];
$CurrencyName = $params[TXN][$success][CURRENCYNAME];
$TxnType = $params[TXN][$success][TXNTYPE];
$CardNumber	= $params[TXN][$success][CARDNUMBER];
$DateExpiry	= $params[TXN][$success][DATEEXPIRY];
$CardHolderResponseText	= $params[TXN][$success][CARDHOLDERRESPONSETEXT];
$CardHolderResponseDescription = $params[TXN][$success][CARDHOLDERRESPONSEDESCRIPTION];
$MerchantResponseText = $params[TXN][$success][MERCHANTRESPONSETEXT];
$DPSTxnRef = $params[TXN][$success][DPSTXNREF];*/

?>