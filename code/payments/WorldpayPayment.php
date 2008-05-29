<?php

/**
 * @package ecommerce
 */
 
/**
 * Sub-class of Payment that supports Worldpay as its payment processor
 **/ 
 
/** 
 *  Configuration
 *  =============
 *  You need to define the installation ID, test mode and callback
 *  password in _config.php of your project:
 *  WorldpayPayment::set_installation_id(111111);
 *  WorldpayPayment::set_testmode(100);
 *  WorldpayPayment::set_callback_password(blahblah);
 */
class WorldpayPayment extends Payment {
	
	protected static $privacy_link = 'http://www.worldpay.com/about_us/index.php?page=privacy';
	protected static $logo = 'ecommerce/images/payments/worldpay.gif';
	
	protected static $installation_id;	
	static function set_installation_id($id) {
		self::$installation_id = $id;
	}
	protected static $worldpay_testmode;	
	static function set_testmode($testmode) {
		self::$worldpay_testmode = $testmode;
	}
	static $callback_password;
		static function set_callback_password($pass) {
		self::$callback_password = $pass;
	}
	
	function getPaymentFormFields() {
		$logo = '<img src="' . self::$logo . '" alt="Credit card payments powered by WorldPay"/>';
		$privacyLink = '<a href="' . self::$privacy_link . '" target="_blank" title="Read WorldPay\'s privacy policy">' . $logo . '</a><br/>';
		return new FieldSet(
			new LiteralField('WorldPayInfo', $privacyLink),
			new LiteralField(
				'WorldPayPaymentsList',
				'<img src="ecommerce/images/payments/methods/visa.jpg" alt="Visa"/>' .
				'<img src="ecommerce/images/payments/methods/mastercard.jpg" alt="MasterCard"/>' .
				'<img src="ecommerce/images/payments/methods/american-express.gif" alt="American Express"/>' .
				'<img src="ecommerce/images/payments/methods/dinners-club.jpg" alt="Dinners Club"/>' .
				'<img src="ecommerce/images/payments/methods/jcb.jpg" alt="JCB"/>'
			)
		);
	}
		
	function getPaymentFormRequirements() {return null;}
		
	function processPayment($data, $form) {
		$page = new Page();
		
		$page->Title = 'Redirection to WorldPay...';
		$page->Logo = '<img src="' . self::$logo . '" alt="Payments powered by WorldPay"/>';
		$page->Form = $this->WorldPayForm();

		$controller = new Page_Controller($page);
		
		$form = $controller->renderWith('PaymentProcessingPage');
		
		return new Payment_Processing($form);
	}
	
	function WorldPayForm() {
		$m = $this->Member();
		$o = DataObject::get_by_id("Order", $this->OrderID);

		$callbackURL = ereg_replace('^[a-zA-Z]+://','', Director::absoluteBaseURL(). 'WorldpayPayment_Handler/paid/' . $this->ID);
		
		/*
		 * A quick start guide to setting up Worldpay can be found here:
		 * http://support.worldpay.com/kb/integration_guides/junior/quickstep/help/quickstep_guide.html
		 */
		
		$info = array(
			"DestObject" => "Order $this->OrderID",
			"instId" => self::$installation_id,
			"currency" => Order::site_currency(),
			"desc" => "Order #$this->OrderID",
			"cartId" => "Order #$this->OrderID",
			"testMode" => self::$worldpay_testmode,
			"amount" => $this->Amount,
			"name" => "$m->FirstName $m->Surname",
			"address" => $m->Address . "\n" . $m->AddressLine2 . "\n" . $m->City,
			"country" => $m->Country,
			"email" => $m->Email,
			"postcode" => $m->Postcode,
			"tel" => $m->HomePhone,
			"fax" => $m->Fax,
			"fixContact" => 1, // ???
			"MC_paymentID" => $this->ID,
			"MC_callback" => $callbackURL // absolute base URL, without the HTTP://
		);
		
		foreach($info as $k => $v) {
			$fields .= "<input type=\"hidden\" name=\"" . Convert::raw2att($k) . "\" value=\"" . Convert::raw2att($v) . "\" />\n";
		}
		$installation_id = self::$installation_id;
		return <<<HTML
			<form id="PaymentForm" method="post" action="https://select.worldpay.com/wcc/purchase">
				<h2>Now forwarding you to WorldPay...</h2>
				$fields
			
				<div id="WorldPayInfo">
					<script src="https://www.worldpay.com/cgenerator/cgenerator.php?instId=$installation_id" type="text/javascript"></script>
				</div>
				<p class="Actions" id="Submit">
				   <input type="submit" value="Make Payment" />
				</p>
				<p id="Submitting" style="display: none">We are now redirecting you to worldpay...</p>
			</form>
			
			<script>
				$('Submit').style.display = 'none';
				$('Submitting').style.display = '';
				$('PaymentForm').submit();
			</script>
HTML;
	}
	
	function getPaymentFormRequiredFields() {
		return array();
	}
}

/**
 * Handler for responses from the WorldPay site
 */
class WorldpayPayment_Handler extends Controller {
	/**
	 * Get the Order object to modify, check security that it's the object you want to modify based
	 * off Worldpay confirmation, update the Order object to show complete and Payment object to show
	 * that it was received. Finally, send a receipt to the buyer to show these details.
	 */		
	function paid() {
		// Check if callback password is the same, otherwise fail
		if($_REQUEST['callbackPW'] == WorldpayPayment::$callback_password) {
			$paymentID = $_REQUEST['MC_paymentID'];
			if(is_numeric($paymentID)) {
				if($payment = DataObject::get_by_id('WorldpayPayment', $paymentID)) {
					if($_REQUEST['transStatus'] == "Y")	$payment->Status = 'Success';
					else $payment->Status = 'Failure';
					$payment->write();
					$payment->redirectToOrder();
				}
				else USER_ERROR("CheckoutPage::OrderConfirmed - There is no Payment object for this order object (Order ID ".$orderID.")", E_USER_WARNING);
			}	
			else USER_ERROR('CheckoutPage::OrderConfirmed - Order ID is NOT numeric', E_USER_WARNING);
		}
		else USER_ERROR("CheckoutPage::OrderConfirmed - Order error - password failed" ,E_USER_WARNING);
		return;
	}
	
	/*function paid() {
		global $project;
	
		// Check if callback password is the same, otherwise fail
		if($_REQUEST['callbackPW'] == WorldpayPayment::$callback_password) {
			if($_REQUEST['transStatus'] == "Y") {
			
				// The transaction was successful, so mark the order as complete
				// Check if the order ID is numeric, otherwise fail
				$orderID = $_REQUEST['MC_orderID'];
				
				if(is_numeric($orderID)) {
					$order = DataObject::get_by_id('Order', $orderID);
					if($order) {
						$order->Status = 'Paid';
						$order->write();
					
						$payment = DataObject::get_one("Payment", "`Payment`.`OrderID` = '$orderID'");
						if($payment) {
							$payment->Status = "Success";
							$payment->write();
						
							// create a log-entry for it
							$logEntry = new OrderStatusLog();
							$logEntry->OrderID = $orderID;
							$logEntry->Status = 'Paid';
							$logEntry->write();
						} else {
							USER_ERROR("CheckoutPage::OrderConfirmed - There is no Payment object for this order object (Order ID ".$orderID.")", E_USER_WARNING);
						}
						// @todo Work out a good way to store WorldPay's callback information - SS1 just dumped the POST data to the same row as the Order in a field called 'transDetails'			
						// send request
						//
					
						$order->Member();
						// TODO - FIX THIS, emails at the moment are inaccurate because GeoIP fails to find
						// for example, it finds the shipping country as the UK (and thus breaks shipping / tax logic in email)
						//$order->sendReceipt();

						$url = Director::absoluteBaseURL() . $order->Link();
											
					} else {
						USER_ERROR("CheckoutPage::OrderConfirmed - Order cannot be found",E_USER_WARNING);
						return;
					}
				} else {
					USER_ERROR("CheckoutPage::OrderConfirmed - Order ID is NOT numeric",E_USER_WARNING);
					return;
				}	
			} else {
				$orderID = $_REQUEST['MC_orderID'];
				if(is_numeric($orderID)) {
					if($order = DataObject::get_by_id('Order', $orderID)) $url = Director::absoluteBaseURL() . $order->Link();
					else {
						USER_ERROR('CheckoutPage::OrderConfirmed - Order cannot be found', E_USER_WARNING);
						return;
					}
				}
				else {
					USER_ERROR('CheckoutPage::OrderConfirmed - Order ID is NOT numeric', E_USER_WARNING);
					return;
				}
			}
		
		} else {
			USER_ERROR("CheckoutPage::OrderConfirmed - Order error - password failed" ,E_USER_WARNING);
			return;
		}
	
		// In the absence of an error, go to the Order complete/incomplete page.
		// WorldPay does not permit HTTP Header redirects, so we use a META REFRESH one instead
		echo "<html><head><meta http-equiv=\"Refresh\" content=\"0;url=$url\"></head><body>Redirecting to <a href=\"$url\">$url</a></body></html>";
	}*/
}

?>