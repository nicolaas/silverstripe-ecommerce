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
	 
	static $db = array (
		"Message" => "Text",
		"Status" =>"Enum(array('Success', 'Failure', 'Incomplete', 'Pending'), 'Incomplete')",
		"Amount" => "Decimal",
		"Currency" =>"Varchar(3)",
		"TxnRef" => "Text",
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
	 	if(ClassInfo::hasTable('Member') && Member::currentUser()->ID) {
	 		$this->MemberID = Member::currentUser()->ID;
	 	}
	}
	
	function populateDefaults() {
		parent::populateDefaults();
		$this->Currency = Order::site_currency();
	}

	function setAmount($val){
		$this->setField('Amount', number_format(ereg_replace("[^0-9.]", "", $val), 2, ".", ""));
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
}
?>