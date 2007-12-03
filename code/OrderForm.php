<?php

/**
 * @package ecommerce
 */
 
 /**
  * Order form for the checkout object
  */
class OrderForm extends Form{
	function __construct($controller, $name) {
		
		// include extra requirements for this form
		Requirements::javascript("jsparty/behaviour.js");
		Requirements::javascript("ecommerce/javascript/CheckoutPage.js");
		
		// get the orders and member info (if available) and define the total number format
		$sc = Order::ShoppingCart();
		$total = '$' . number_format($sc->Total(), 2);
		$member = Member::currentUser();
		
		// create country field to change country (so the order amounts can be recalculated if necessary)
		$countryField = new DropdownField("Country", "", Geoip::getCountryDropDown(), EcommerceRole::findCountry());
		$countryField = $countryField->performReadonlyTransformation();
		$countryFieldGroup = new FieldGroup(
			'Country',
			$countryField,
			new FormAction('ChangeCountry', 'Change Country')
		);
		$countryFieldGroup->subfieldParam = 'Field';

		// check if there is a shipping country set, otherwise use the findCountry function for a member
		if($sc->ShippingCountry) {
			$shippingCountry = $sc->ShippingCountry;
		} else {
			$shippingCountry = EcommerceRole::findCountry();
		}
		
		// create country field to change country (so the order amounts can be recalculated if necessary)
		$shippingCountryField = new DropdownField("ShippingCountry", "", Geoip::getCountryDropDown(), $shippingCountry);
		$shippingCountryField = $shippingCountryField->performReadonlyTransformation();
		$shippingCountryFieldGroup = new FieldGroup(
			'Country',
			$shippingCountryField,
			new FormAction('ChangeCountry2', 'Change Country')
		);
		$shippingCountryFieldGroup->subfieldParam = 'Field';

		// setup password fields
		if(!$member) {
			$membershipHeader = new HeaderField("Membership details", 3);
			$accountField = new LiteralField("AccountInfo", "<p>Please choose a password, so you can login and check your order history in the future.</p><br />");
			$passwordField = new FieldGroup(
				new PasswordField('Password', 'Password'),
				new PasswordField('ConfirmPassword', 'Confirm Password')
			);
		} else {
			$membershipHeader = new HiddenField('MembershipHeaderHidden', '');
			$accountField = new HiddenField('AccountInfo', '');
			$passwordField = new HiddenField('PasswordHidden', '');
		}
		
		// if a terms page is in the system, create a field to confirm the user read it
		if($tacPage = DataObject::get_one('EcommerceTermsPage')) {
			$readConditionsField = new CheckboxField('ReadConditions', "I agree to the terms and conditions stated on <a href=\"$tacPage->URLSegment\" title=\"Read the shop terms and conditions for this site\">the terms and conditions</a> page");
		} else {
			$readConditionsField = new HiddenField('ReadConditions', '');
		}
		
		// initialise variables with contact fields from member, remove country because orderform requires
		// a custom country field setup
		if(!$member) {
			$contactFields = EcommerceRole::getEcommerceFields();
			$contactFields->removeByName('Country');
		} else {
			$contactFields = EcommerceRole::getAddressFields();
		}
		
		// setup the shipping fields, if UseShippingAddress is true (can be set when changing country)
		if($sc->UseShippingAddress) {
			$shippingFields =	new CompositeField(
				new HeaderField("Send goods to different address", 3),
				new LiteralField('ShippingNote', '<p class="warningMessage"><em>Your goods will be sent to the address below.</em></p>'),
				new LiteralField("Help", "<p>You can use this for gift giving; no billing information will be disclosed to this address.</p>"),
				new TextField("ShippingName", "Name"),
				new TextField("ShippingAddress", "Address"),
				new TextField("ShippingAddress2", ""),
				new TextField("ShippingCity", "City"),
				$shippingCountryFieldGroup,
				new FormAction_WithoutLabel('useBillingAddress', 'Use Billing Address for Shipping')
			);
		} else {
			$shippingFields = new FormAction_WithoutLabel('useDifferentShippingAddress', 'Use Different Shipping Address');
			// $shippingFields = new HiddenField('ShippingDetailsHidden', '');
		}
		
		// setup the fields into a fieldset
		$fields = new FieldSet(
			$left = new CompositeField(
				$contactFields,
				$countryFieldGroup,
				$shippingFields
			),
			$right = new CompositeField(
				$membershipHeader,
				$accountField,
				$passwordField
			),
			$bottom = new CompositeField(
				$readConditionsField
			)
		);
		
		// apply IDs so we can style these blocks of fields
		$left->setID('LeftOrder');
		$right->setID('RightOrder');
		$bottom->setID('BottomOrder');
		
		
		// Add the payment processing fields 
		$paymentFields = Payment::combined_form_fields($total . " " . $sc->Currency(), $sc->Subtotal());
		foreach($paymentFields as $field) {
			$right->push($field);
		}
		
		$actions = 	new FieldSet(
			new FormAction("processOrder", "Place order and make payment")
		);
		
		// setup required fields
		$requiredFieldsArr = array(
			"FirstName",
			"Surname",
			"Address",
			"Email",
			"City"
		);

		// if not a member, add some password fields so a member can be setup
		if(!$member) {
			$requiredFieldsArr[] = "Password";
			$requiredFieldsArr[] = "ConfirmPassword";
		}

		// if terms page exists, add validation for the field on the form
		if($tacPage) {
			$requiredFieldsArr[] = 'ReadConditions';
		}
		
		// if UseShippingAddress is true, require validation from these fields
		if($sc->UseShippingAddress) {
			$requiredFieldsArr[] = "ShippingName";
			$requiredFieldsArr[] = "ShippingAddress";
			$requiredFieldsArr[] = "ShippingAddress2";
			$requiredFieldsArr[] = "ShippingCity";
		}
		
		// merge payment field requirements
		if($methodRequirements = Payment::combined_form_requirements()) {
			$requiredFieldsArr = array_merge($requiredFieldsArr, $methodRequirements);
		}
		
		// apply the required fields array we've accumulated into the object		
		$RequiredFields = new EcommerceRequiredFields(
			$requiredFieldsArr
		);

		parent::__construct($controller, $name, $fields, $actions, $RequiredFields);
		
		// Load any data available from our serialized data into the form
		if($serialized_data = Session::get("MemberOrderData")) {
			$unserialized_data = unserialize($serialized_data);
			$this->loadNonBlankDataFrom($unserialized_data);
		}
		
		// Load any data avaliable into the form.
		if($member = Member::currentUser()){
			$this->loadNonBlankDataFrom($member);
		}
		
	}
	
	
	/** 
	 * Processes the order information from the Shopping cart, creates or merges
	 * the member from the database, and then processes the payment.
	 */
	function processOrder($data, $form) {
		// if the password and confirm password don't match, then return an error
		if($data['Password'] != $data['ConfirmPassword']) {
			$form->addErrorMessage('ConfirmPassword', 'The passwords do not match', 'bad');
			Director::redirectBack();
			exit;
		}
		
		$sc = Order::ShoppingCart();
		
		// Check to see if there are still items in the shopping cart
		if($sc->Items()){
			$cartContents = Session::get('cartContents');
			$member = EcommerceRole::createOrMerge($data);
			$member->write();
			$member->logIn();
			
			// Get, and save the order from session.
			$order = $sc->createOrderFromShoppingCart();
			// Update order with shipping address
			$form->saveInto($order);
			
			$order->write();
			
			$data['BillingId'] = $order->ID;
			
			// Save payment data from form and process payment
			
			
			$payment = Object::create($data['PaymentMethod']);
			if(!$payment instanceof Payment) user_error(get_class($payment) ." is not a Payment object!", E_USER_ERROR);
			$form->saveInto($payment);
			$payment->OrderID = $order->ID;
			$payment->Amount = $order->Total();
			
			// Worldpay doesn't have a payment object so we write one here
			if($data['PaymentMethod'] == 'WorldpayPayment') {
				$payment->write();
			}			
			
			$result = $payment->processPayment($data, $form);
							
			// Successful payment
			if($result['Success']) {
			  	Session::set('Order.OrderID',$order->ID);
				Session::set('Order.PurchaseComplete', true);
				
				$order->sendReceipt();
				$order->isComplete();
				$order->write();
				
				Director::redirect(CheckoutPage::find_link() . "OrderSuccessful/$order->ID");
				return;
				
			// Longer payment process, such as Worldpay
			} else if($result['Processing']) {
				return $result['ReturnValue'];
	 		
			// Failed payment
			} else {
				Session::set('cartContents',$cartContents);
				Session::set('Order.OrderID', $order->ID);
				Session::clear('Order.PurchaseComplete');
				$form->sessionMessage("Sorry, your payment was not accepted, please try again<br/><strong>$result[HelpText]:</strong> $result[MerchantHelpText]","bad");
	 			Director::redirect(CheckoutPage::find_link() . "$order->ID");
	 			return;
			}
		
		} else {
			// no items, redirect back
			$form->sessionMessage("Please add some items to your cart","warning");
		   	Director::redirectBack();
		   	return;
		}
		
	}

	/**
	 * Disable the validator when you're calling ChangeCountry
	 */
	function beforeProcessing() {
		if(isset($_REQUEST['action_ChangeCountry']) || isset($_REQUEST['action_ChangeCountry2']) || isset($_REQUEST['action_useDifferentShippingAddress']) ||  isset($_REQUEST['action_useBillingAddress'])) {
			return true;
		} else {
			return parent::beforeProcessing();
		}
	}

}

?>
