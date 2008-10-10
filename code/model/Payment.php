<?php 

/**
 * Payment class.
 * Use this to process all manner of payments
 * 
 * @package ecommerce
 */
class Payment extends DataObject {
	
	public static $testCredentials, $liveCredentials;
	
	/**
 	 * Incomplete(Default) : Payment created but no successful yet or the process has been stop instantly
 	 * Success : Payment successful
 	 * Failure : Payment failed during process or (if Cheque, non received or not well formated) 
 	 * Pending : For cheque Only
 	 */
	static $db = array(
		'Status' => "Enum('Incomplete,Success,Failure,Pending','Incomplete')",
		'Amount' => 'Currency',
		'Currency' => 'Varchar(3)',
		'TxnRef' => 'Text',
		'Message' => 'Text',
		'IP' => 'Varchar',
		'ProxyIP' => 'Varchar',
		'AuthorizationCode' => 'Text'
	);
	
	static $has_one = array(
		'Order' => 'Order'
	);
	
	function populateDefaults() {
		parent::populateDefaults();
		
		$this->Currency = Order::site_currency();
		$this->setClientIP();
		$this->AuthorizationCode = md5(uniqid(rand(), true));
 	}
	
	/**
	 * Set the IP address and Proxy IP (if available) from the site visitor.
	 * Does an ok job of proxy detection. Probably can't be too much better because anonymous proxies
	 * will make themselves invisible.
	 */	
	function setClientIP() {
		if(isset($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
		else $ip = null;
		
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$proxy = $ip;
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		
		// If the IP and/or Proxy IP have already been set, we want to be sure we don't set it again.
		if(!$this->IP) $this->IP = $ip;
		if(!$this->ProxyIP && isset($proxy)) $this->ProxyIP = $proxy;
	}
	
	/**
	 * Subclasses of Payment that are allowed to be used on this site.
	 */
	protected static $supported_methods = array(
		'ChequePayment' => 'Cheque'
	);
	
	/**
	 * Set the payment methods that this site supports.
	 * 
	 *The classes should all be subclasses of Payment.
	 *
	 * @param array $methodMap A map, mapping class names to human-readable descriptions of the payment methods.
	 */
	static function set_supported_methods($methodMap) {
		self::$supported_methods = $methodMap;
	}
	
	/**
	 * Returns the 'nice' title of the payment method given.
	 * 
	 * @param string $method The ClassName of the payment method.
	 */
	static function findPaymentMethod($method) {
		return self::$supported_methods[$method];
	}
	
	/**
	 * Returns the Payment method used. It just resolves the classname
	 * to the 'nice' title as defined in Payment::set_supported_methods().
	 * For example: 'ChequePayment' => 'Cheque'
	 * 
	 * @return string
	 */
	function PaymentMethod() {
		if(self::findPaymentMethod($this->ClassName)) {
			return self::findPaymentMethod($this->ClassName);
		}
	}
	
	/**
	 * Return a set of payment fields from all enabled
	 * payment methods for this site, given the . {@link Payment::set_supported_methods()}
	 * is used to define which methods are available.
	 * 
	 * @TODO Why do we have subtotal? What is the relevance of
	 * a subtotal? If you pay for something, you just require a single
	 * amount!
	 * 
	 * @return FieldSet
	 */
	static function combined_form_fields($amount, $subtotal) {

		// Create the initial form fields, which defines an OptionsetField
		// allowing the user to choose which payment method to use.
		$fields = new FieldSet(
			new HeaderField(_t('Payment.PAYMENTTYPE', 'Payment Type'), 3),
			new OptionsetField(
				'PaymentMethod',
				'',
				self::$supported_methods,
				array_shift(array_keys(self::$supported_methods))
			)
		);
		
		// If the user defined an numerically indexed array, throw an error
		if(ArrayLib::is_associative(self::$supported_methods)) {
			foreach(self::$supported_methods as $methodClass => $methodTitle) {
				
				// Create a new CompositeField with method specific fields,
				// as defined on each payment method class using getPaymentFormFields()
				$methodFields = new CompositeField(singleton($methodClass)->getPaymentFormFields());
				$methodFields->setID("MethodFields_$methodClass");
				$methodFields->addExtraClass('paymentfields');
				
				// Add those fields to the initial FieldSet we first created
				$fields->push($methodFields);
			}
		} else {
			user_error('Payment::set_supported_methods() requires an associative array.', E_USER_ERROR);
		}
		
		// Add the amount and subtotal fields for the payment amount
		$fields->push(new ReadonlyField('Amount', _t('Payment.AMOUNT', 'Amount'), $amount));
		$fields->push(new HiddenField('Subtotal', _t('Payment.SUBTOTAL', 'Subtotal'), $subtotal));
		
		return $fields;
	}
	
	/**
	 * Return the form requirements for all the payment methods.
	 * 
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
	
	/**
	 * Return the payment form fields that should
	 * be shown on the checkout order form for the
	 * payment type. e.g. for {@link DPSPayment},
	 * this would be a set of fields to enter your
	 * credit card details.
	 * 
	 * This needs to be implemented on your subclass
	 * of Payment, even if it only returns NULL. It
	 * should return a {@link FieldSet}.
	 * 
	 * @return FieldSet
	 */
	function getPaymentFormFields() {
		user_error("Please implement getPaymentFormFields() on $this->class", E_USER_ERROR);
	}
	
	/**
	 * Perform payment processing for the type of
	 * payment. For example, if this was a credit card
	 * payment type, you would perform the data send
	 * off to the payment gateway on this function for
	 * your payment subclass.
	 * 
	 * This is used by {@link OrderForm} when it is
	 * submitted.
	 *
	 * @param array $data The form request data - see OrderForm
	 * @param OrderForm $form The form object submitted on
	 */
	function processPayment($data, $form) {
		user_error("Please implement processPayment() on $this->class", E_USER_ERROR);
	}
	
	/**
	 * Define what fields defined in {@link Order->getPaymentFormFields()}
	 * should be required. 
	 * 
	 * @see DPSPayment->getPaymentFormRequirements() for an example on how
	 * this is implemented.
	 * 
	 * @return array
	 */
	function getPaymentFormRequirements() {
		user_error("Please implement getPaymentFormRequirements() on $this->class", E_USER_ERROR);
	}
	
	/**
	 * Automatically change the status of the order to paid
	 * if successful.
	 * 
	 * Precondition: Order status is unpaid.
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
	
	function __construct($value = null) {
		$this->value = $value;
	}
	
	function getValue() {
		return $this->value;
	}
	
	abstract function isSuccess();
	
	abstract function isProcessing();
	
}

class Payment_Success extends Payment_Result {
	
	function isSuccess() {
		return true;
	}
	
	function isProcessing() {
		return false;
	}
	
}

class Payment_Processing extends Payment_Result {
		
	function isSuccess() {
		return false;
	}

	function isProcessing() {
		return true;
	}
	
}

class Payment_Failure extends Payment_Result {
	
	function isSuccess() {
		return false;
	}
	
	function isProcessing() {
		return false;
	}
	
}	

?>
