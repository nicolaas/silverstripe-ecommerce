<?php 

/**
 * Payment class.
 * Use this to process all manner of payments
 * @package ecommerce
 */

class Payment extends DataObject {
	
	public static $testCredentials, $liveCredentials;

	static $casting = array(
		"Amount" => "Currency",
		"LastEdited" => "Datetime"
	);
	
	/**
 	 * Incomplete(Default) : Payment created but no successful yet or the process has been stop instantly
 	 * Success : Payment successful
 	 * Failure : Payment failed during process or (if Cheque, non received or not well formated) 
 	 * Pending : For cheque Only
 	 */
	static $db = array(
		"Message" => "Text",
		"Status" => "Enum(array('Incomplete','Success','Failure','Pending'), 'Incomplete')",
		"Amount" => "Decimal",
		"Currency" =>"Varchar(3)",
		"TxnRef" => "Text",
		"IP" => "Varchar",
		"ProxyIP" => "Varchar"
	);
	
	/**
	 * Sets up the relationship between Member and Payment
	 */
	static $has_one = array(
		"Member" => "Member",
		"Order" => "Order"
	);
		
	/**
	 * Process this payment, and set the status message in the session.
	 * Returns true on a successful payment, false on an error (such as CC declined).
	 */	 
	function __construct($data = null) {
		parent::__construct($data);
	 	
		// Check if we have a Member table, otherwise it breaks db/build
	 	if(ClassInfo::hasTable('Member') && Member::currentUser()) {
	 		$this->MemberID = Member::currentUser()->ID;
	 	}
	}
	
	function populateDefaults() {
		parent::populateDefaults();
		$this->Currency = Order::site_currency();
		$this->setClientIP();
	}

	function setAmount($val){
		$this->setField('Amount', number_format(ereg_replace("[^0-9.]", "", $val), 2, ".", ""));
	}

	/**
	 * Set the IP address and Proxy IP (if available) from the site visitor.
	 * Does an ok job of proxy detection. Probably can't be too much better because anonymous proxies
	 * will make themselves invisible.
	 */	
	function setClientIP() {
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			if(isset($_SERVER['HTTP_CLIENT_IP'])) {
				$proxy = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$proxy = $_SERVER['REMOTE_ADDR'];
			}
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			if(isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		}
		
		// If the IP and/or Proxy IP have already been set, we want to be sure we don't set it again.
		if(!$this->IP && isset($ip)) $this->IP = $ip;
		if(!$this->ProxyIP && isset($proxy)) $this->ProxyIP = $proxy;
	}	
	
	/**
	 * Subclasses of Payment that are allowed to be used on this site.
	 */
	protected static $supported_methods = array('ChequePayment' => 'Cheque');
	
	/**
	 * Set the payment methods that this site supports
	 * @param methodMap A map, mapping class names to human-readable descriptions of the payment methods.
	 * The classes should all be subclasses of Payment.
	 */
	static function set_supported_methods($methodMap) {
		self::$supported_methods = $methodMap;
	}
	
	/**
	 * Returns the 'nice' title of the payment method given.
	 * @param - $method - the ClassName of the payment method.
	 */
	static function findPaymentMethod($method) {
		return self::$supported_methods[$method];
	}
		
	/**
	 * Returns the Payment method used. It just resolves the classname
	 * to the 'nice' title as defined in Payment::set_supported_methods().
	 * For example: 'ChequePayment' => 'Cheque'
	 */
	function PaymentMethod() {
		if(self::findPaymentMethod($this->ClassName)) {
			return self::findPaymentMethod($this->ClassName);
		}
	}
	
	/**
	 * Return the field set of payment fields from all the enabled payment classes in this site, ready to be 
	 * inserted into a form.
	 */
	static function combined_form_fields($amount, $subtotal) {
		// Initial form, with the optionset for switching between methods		
		$fields = new FieldSet(
			// Payment Form
			new HeaderField("Payment Type",3),
			new OptionsetField("PaymentMethod", "",
				self::$supported_methods,
				array_shift(array_keys(self::$supported_methods))
			)
		);
		
		// 1x CompositeField for each payment method
		foreach(self::$supported_methods as $method => $methodTitle) {
			$composite = new CompositeField(singleton($method)->getPaymentFormFields());
			$composite->setID("MethodFields_$method");
			$composite->addExtraClass('paymentfields');
			$fields->push($composite);
		}
		
		// Final fields below that
		$fields->push(new ReadonlyField("Amount", "Amount", $amount));
		$fields->push(new HiddenField("Subtotal", "Subtotal", $subtotal));

		return $fields;
	}
	
	/**
	 * Return the form requirements for all the payment methods
	 * @return An array suitable for passing to CustomRequiredFields
	 */
	static function combined_form_requirements() {
		$requirements = array();
		
		// Loop on available methods
		foreach(self::$supported_methods as $method => $methodTitle) {
			$methodRequirements = singleton($method)->getPaymentFormRequirements();
			if($methodRequirements) {
				// Put limiters into the JS/PHP code to only use those requirements for this payment method
				$methodRequirements['js'] = "for(var i=0; i <= this.elements.PaymentMethod.length-1; i++) "
					. "if(this.elements.PaymentMethod[i].value == '$method' && this.elements.PaymentMethod[i].checked == true) {"
					. $methodRequirements['js'] . " } ";

				$methodRequirements['php'] = "if(\$data['PaymentMethod'] == '$method') { " . 
					$methodRequirements['php'] . " } ";
					
				$requirements[] = $methodRequirements;
			}
		}
		
		return $requirements;
	}
	
	function getPaymentFormFields() {
		user_error("Please implement getPaymentFormFields() on $this->class", E_USER_ERROR);
	}
	
	function processPayment($data, $form) {
		user_error("Please implement processPayment() on $this->class", E_USER_ERROR);
	}
	
	function getPaymentFormRequirements() {
		user_error("Please implement getPaymentFormRequirements() on $this->class", E_USER_ERROR);
	}
	
	/**
	 * Function which automatically changes the status of the order to paid if successful
	 * Precondition : Order status is unpaid
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		if($this->Status == 'Success') {
			$order = $this->Order();
			$order->Status = 'Paid';
			$order->write();
			$order->sendReceipt();
		}
	}
	
	function redirectToOrder() {
		$order = $this->Order();
		Director::redirect($order->Link());
		return;
	}
}

abstract class Payment_Result {
	
	protected $value;
	
	function __construct($value = null) {$this->value = $value;}
	
	function getValue() {return $this->value;}
	
	abstract function isSuccess();
	abstract function isProcessing();
	
}

class Payment_Success extends Payment_Result {
		
	function isSuccess() {return true;}
	function isProcessing() {return false;}
}

class Payment_Processing extends Payment_Result {
	
	function __construct($form) {parent::__construct($form);}
	
	function isSuccess() {return false;}
	function isProcessing() {return true;}
}

class Payment_Failure extends Payment_Result {
		
	function isSuccess() {return false;}
	function isProcessing() {return false;}
}	

?>
