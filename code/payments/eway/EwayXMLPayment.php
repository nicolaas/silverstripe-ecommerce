<?php

class EwayXMLPayment extends Payment {
	
	private static $url = 'https://www.eway.com.au/gateway/xmlpayment.asp';
	
	protected static $customer_id;
	static function set_customer_id($id) {self::$customer_id = $id;}
	
	protected static $test_mode = false;	
	static function set_test_mode() {self::$test_mode = true;}
	
	private static $test_customer_id = '87654321';
	private static $test_url = 'https://www.eway.com.au/gateway/xmltest/TestPage.asp';
	private static $test_credit_card_number = '4444333322221111';
	private static $test_total_amount = 'The test Total Amount should end in 00 or 08 to get a successful response and has to be in cents (e.g. 1000 for $10.00 or 1008 for $10.08) - all other amounts will return a failed response.';
	
	static function get_url() {return self::$test_mode ? self::$test_url : self::$url;}
		
	function getPaymentFormFields() {
		return new FieldSet(
			new TextField('CardHolderName', 'Card Holder Name'),
			new CreditCardField('CreditCardNumber', 'Credit Card Number', self::$test_mode ? self::$test_credit_card_number : ''),
			new TextField('ExpiryMonth', 'Expiry Month (MM)', self::$test_mode ? '05' : '', 2),
			new TextField('ExpiryYear', 'Expiry Year (YY)', self::$test_mode ? '10' : '', 2),
			new DropdownField(
				'CreditCardType',
				'Credit Card Type',
				array(
					'VISA' => 'Visa',
					'MASTERCARD' => 'MasterCard',
					'BANKCARD' => 'BankCard',
					'AMEX' => 'Amex',
					'DINERS' => 'Diners',
					'JCB' => 'JCB'
				)
			),
			new LiteralField('EwayInfo', '<a href="http://www.eway.com.au/Company/About/Privacy.aspx" title="Read Eway\'s privacy policy"><img src="http://www.landlinks.com.au/media/share/eway_logo.gif" alt="Payments powered by Eway"/></a>')
		);
	}
	
	function getPaymentFormRequiredFields() {
		return new FieldSet(
			new TextField('CardHolderName', 'Card Holder Name'),
			new CreditCardField('CreditCardNumber', 'Credit Card Number'),
			new NumericField('ExpiryMonth', 'Expiry Month (MM)', '', 2),
			new NumericField('ExpiryYear', 'Expiry Year (YY)', '', 2),
			new DropdownField(
				'CreditCardType',
				'Credit Card Type',
				array(
					'VISA' => 'Visa',
					'MASTERCARD' => 'MasterCard',
					'BANKCARD' => 'BankCard',
					'AMEX' => 'Amex',
					'DINERS' => 'Diners',
					'JCB' => 'JCB'
				)
			)
		);
	}
	
	function getPaymentFormRequirements() {
		return array(
			'js' => "
				require('CardHolderName');
				require('CreditCardNumber');
				require('ExpiryMonth');
				require('ExpiryYear');
			",
			'php' => '
				$this->requireField("CardHolderName", $data);
				$this->requireField("CreditCardNumber", $data);
				$this->requireField("ExpiryMonth", $data);
				$this->requireField("ExpiryYear", $data);
			'
		);
	}
	
	function processPayment($data, $form) {
		$txtFirstName = $data['FirstName'];
		$txtLastName = $data['SurName'];
		$txtEmail = $data['Email'];
		$txtAddress = $data['Address'] . ' ' . $data['AddressLine2'] . ' ' . $data['City'] . ' ' . $data['Country'];
		$txtCCName = $data['CardHolderName'];
		$txtCCNumber = self::$test_mode ? self::$test_credit_card_number : implode('', $data['CreditCardNumber']);
		$ddlExpiryMonth = $data['ExpiryMonth'];
		$ddlExpiryYear = $data['ExpiryYear'];
		$txtAmount = self::$test_mode ? '1008' : $this->Amount * 100;
		
		//create eWAY gateway object
		$eWAYgateway = new GatewayConnector;
		$eWAYRequest = new GatewayRequest;
		
		// Set the payment details
		$eWAYRequest->EwayCustomerID(self::$test_mode ? self::$test_customer_id : self::$customer_id);
		
		//get values input by user on form, or set to database values
		$eWAYRequest->PurchaserFirstName($txtFirstName);
		$eWAYRequest->PurchaserLastName($txtLastName);
		$eWAYRequest->PurchaserEmailAddress($txtEmail);
		$eWAYRequest->PurchaserAddress($txtAddress);
		$eWAYRequest->CardHolderName($txtCCName);
		$eWAYRequest->CardNumber($txtCCNumber);
		$eWAYRequest->CardExpiryMonth($ddlExpiryMonth);
		$eWAYRequest->CardExpiryYear($ddlExpiryYear);
		$eWAYRequest->InvoiceAmount($txtAmount);
		
		$order = $this->Order();
		
		// Do the payment, send XML doc containing information gathered
		if($eWAYgateway->ProcessRequest($eWAYRequest)) {
			// payment succesfully sent to gateway
			if($eWAYResponse = $eWAYgateway->Response()) {
				$this->Status = 'Success';
				$this->TxnRef = $eWAYResponse->TransactionNumber();
				$result = new Payment_Success();
			}
			else {
				$this->Status = 'Failure';
				$result = new Payment_Failure('The payment has not been completed correctly. An invalid response has been received from the Eway payment.');
			}
		}
		else {
			$this->Status = 'Failure';
			$result = new Payment_Failure('The payment has not been completed correctly. The payment request has not been sent to the Eway server correctly.');
		}
		
		if(self::$test_mode) $this->Message = 'This payment was just a test';
		
		$this->write();
		return $result;
	}
}

?>