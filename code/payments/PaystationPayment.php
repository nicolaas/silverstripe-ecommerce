<?php

class PaystationPayment extends Payment {
	
	// PayPal Informations
	
	protected static $privacy_link = 'http://paystation.co.nz/privacy-policy';
	protected static $logo = 'ecommerce/images/payments/paystation.jpg';
	
	// URLs

	protected static $url = 'https://www.paystation.co.nz/direct/paystation.dll?paystation';

	// Test Mode

	protected static $test_mode = false;
	static function set_test_mode() {self::$test_mode = true;}

	// Payment Informations

	protected static $merchant_id;
	static function set_merchant_id($merchant_id) {self::$merchant_id = $merchant_id;}
	
	function getPaymentFormFields() {
		$logo = '<img src="' . self::$logo . '" alt="Credit card payments powered by Paystation"/>';
		$privacyLink = '<a href="' . self::$privacy_link . '" target="_blank" title="Read Paystation\'s privacy policy">' . $logo . '</a><br/>';
		return new FieldSet(
			new LiteralField('PaystationInfo', $privacyLink),
			new LiteralField(
				'PaystationPaymentsList',
				'<img src="ecommerce/images/payments/methods/visa.jpg" alt="Visa"/>' .
				'<img src="ecommerce/images/payments/methods/mastercard.jpg" alt="MasterCard"/>' .
				'<img src="ecommerce/images/payments/methods/american-express.gif" alt="American Express"/>'
			),
			new CreditCardField('Paystation_CreditCardNumber', 'Credit Card Number :'),
			new TextField('Paystation_CreditCardExpiry', 'Credit Card Expiry : (MMYY)', '', 4),
			new DropdownField(
				'Paystation_CreditCardType',
				'Credit Card Type :',
				array(
					'visa' => 'Visa',
					'mastercard' => 'MasterCard',
					'amex' => 'Amex'
				)
			)
		);
	}
	
	/**
	 * Returns the required fields to add to the order form, when using this payment method. 
	 */
	function getPaymentFormRequirements() {
		$jsCode = <<<JS
			require('Paystation_CreditCardNumber');
			require('Paystation_CreditCardExpiry');
JS;
		$phpCode = '
			$this->requireField("Paystation_CreditCardNumber", $data);
			$this->requireField("Paystation_CreditCardExpiry", $data);
		';
		return array('js' => $jsCode, 'php' => $phpCode);
	}
	
	function processPayment($data, $form) {
		
		// 1) Main Settings
		
		$inputs['pi'] = self::$merchant_id;
		$inputs['2pty'] = 't';
		
		// 2) Payment Informations
		
		$inputs['am'] = $this->Amount * 100;
		$inputs['ms'] = $this->ID;
		
		// 3) Credit Card Informations
		
		$inputs['cardno'] = implode('', $data['Paystation_CreditCardNumber']);
		$inputs['cardexp'] = $data['Paystation_CreditCardExpiry'];
		$inputs['ct'] = $data['Paystation_CreditCardType'];
		
		// 4) Test Mode And Redirection Informations
		
		if(self::$test_mode) $inputs['tm'] = 't';
		$inputs['no_redirect'] = 't';
		
		// 5) Paystation Transaction Sending
			
  		$responseFields = $this->doPayment($inputs);
		
		// 6) Paystation Response Management
				
		if($responseFields['EC'] == '0') {
			$this->Status = 'Success';
			$result = new Payment_Success();
		}
		else {
			$this->Status = 'Failure';
			$result = new Payment_Failure();
		}
		
		$this->Message = $responseFields['EM'] ? $responseFields['EM'] : 'The merchant ID has not been set by the administrator';
		
		$this->write();
		return $result;
	}
	
	function doPayment(array $inputs) {
		
		// 1) URL Creation
		
		foreach ($inputs as $name => $value) $params .= "&$name=$value";
		$params = substr($params, 1);
		
		// 2) CURL Creation
		
		$clientURL = curl_init();
		$definedVars = get_defined_vars();
		 
		curl_setopt($clientURL, CURLOPT_URL, self::$url);
		curl_setopt($clientURL, CURLOPT_POST, 1);
		curl_setopt($clientURL, CURLOPT_POSTFIELDS, $params);
		curl_setopt($clientURL, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($clientURL, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($clientURL, CURLOPT_USERAGENT, $definedVars['HTTP_USER_AGENT']);
		
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
		
		$result = $resultPhp['RESPONSE'];
		
		return $result;
	}
}

?>