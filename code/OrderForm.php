<?php

/**
 * @package ecommerce
 */
 
 /**
  * Order form for the checkout object
  */
class OrderForm extends Form{
	
	function __construct($controller, $name) {
		
		// 1) Member and shipping fields
		
		$member = Member::currentUser() ? Member::currentUser() : new Member();
		$memberFields = $member->getEcommerceFields();
		$requiredFields = $member->getEcommerceRequiredFields();
		
		if(ShoppingCart::uses_different_shipping_address()) {
			$countryField = new DropdownField('ShippingCountry', 'Country', Geoip::getCountryDropDown(), EcommerceRole::findCountry());
			$countryField->addExtraClass('ajaxCountryField');
			$shippingFields = new CompositeField(
				new HeaderField('Send goods to different address', 3),
				new LiteralField('ShippingNote', '<p class="warningMessage"><em>Your goods will be sent to the address below.</em></p>'),
				new LiteralField('Help', '<p>You can use this for gift giving. No billing information will be disclosed to this address.</p>'),
				new TextField('ShippingName', 'Name'),
				new TextField('ShippingAddress', 'Address'),
				new TextField('ShippingAddress2', ''),
				new TextField('ShippingCity', 'City'),
				$countryField,
				new HiddenField('UseShippingAddress', '', true),
				new FormAction_WithoutLabel('useMemberShippingAddress', 'Use Billing Address for Shipping')
			);
			
			$requiredFields[] = 'ShippingName';
			$requiredFields[] = 'ShippingAddress';
			$requiredFields[] = 'ShippingCity';
			$requiredFields[] = 'ShippingCountry';
		}
		else {
			$memberFields->fieldByName('Country')->addExtraClass('ajaxCountryField');
			$shippingFields = new FormAction_WithoutLabel('useDifferentShippingAddress', 'Use Different Shipping Address');
		}
		
		$leftFields = new CompositeField($memberFields, $shippingFields);
		$leftFields->setID('LeftOrder');
				
		$rightFields = new CompositeField();
		$rightFields->setID('RightOrder');
		
		if(! $member->ID || $member->Password == '') {
			$rightFields->push(new HeaderField('Membership Details', 3));
			$rightFields->push(new LiteralField('MemberInfo', "<p class=\"message good\">If your are already a member, please <a href=\"Security/login?BackURL=checkout/\">login</a>.</p>"));
			$rightFields->push(new LiteralField('AccountInfo', "<p>Please choose a password, so you can login and check your order history in the future.</p><br/>"));
			$rightFields->push(new FieldGroup(new ConfirmedPasswordField('Password', 'Password')));
			
			$requiredFields[] = 'Password[_Password]';
			$requiredFields[] = 'Password[_ConfirmPassword]';
		}
		
		// 2) Payment fields
		
		$currentOrder = ShoppingCart::current_order();
		$total = '$' . number_format($currentOrder->Total(), 2);
		$paymentFields = Payment::combined_form_fields("$total " . $currentOrder->Currency(), $currentOrder->Subtotal());
		foreach($paymentFields as $field) $rightFields->push($field);
		
		if($paymentRequiredFields = Payment::combined_form_requirements()) $requiredFields = array_merge($requiredFields, $paymentRequiredFields);
		
		// 3) Put all the fields in one FieldSet
		
		$fields = new FieldSet($leftFields, $rightFields);
		
		// 4) Terms and conditions field
		
		// If a terms and conditions page exists, we need to create a field to confirm the user has read it
		if($tacPage = DataObject::get_one('EcommerceTermsPage')) {
			$bottomFields = new CompositeField(new CheckboxField('ReadTermsAndConditions', "I agree to the terms and conditions stated on the <a href=\"$tacPage->URLSegment\" title=\"Read the shop terms and conditions for this site\">terms and conditions</a> page"));
			$bottomFields->setID('BottomOrder');
			
			$fields->push($bottomFields);
			
			$requiredFields[] = 'ReadTermsAndConditions';
		}
		
		// 5) Actions and required fields creation
		
		$actions = 	new FieldSet(new FormAction('processOrder', 'Place order and make payment'));
		
		$requiredFields = new CustomRequiredFields($requiredFields);
		
		// 6) Form construction
		
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
		
		// 7) Member details loading
		
		if($member->ID)	$this->loadNonBlankDataFrom($member);
	}

	/**
	 * Disable the validator when the action clicked is to use a different shipping address
	 * or use the member shipping address.
	 */
	function beforeProcessing() {
		if(isset($_REQUEST['action_useDifferentShippingAddress']) || isset($_REQUEST['action_useMemberShippingAddress'])) return true;
		else return parent::beforeProcessing();
	}
	
	/*
	 * Save in the session that the current member wants to use a different shipping address.
	 */
	function useDifferentShippingAddress($data, $form) {
		ShoppingCart::set_uses_different_shipping_address(true);
		Director::redirectBack();
	}
	
	/*
	 * Save in the session that the current member wants to use his address as a shipping address.
	 */
	function useMemberShippingAddress($data, $form) {
		ShoppingCart::set_uses_different_shipping_address(false);
		Director::redirectBack();
	}
	
	/** 
	 * Processes the order information from the Shopping cart, creates or merges
	 * the member from the database, and then processes the payment.
	 * This function concerns only the current order
	 */
	function processOrder($data, $form) {
		
		// 1) Check to see if there are still items in the current order
		
		if(ShoppingCart::has_products()) {
			
			// 2) Save the member details
			
			$member = EcommerceRole::createOrMerge($data);
			$member->write();
			$member->logIn();
			
			// 3) Save the current order details (items and modifiers) (which are at the moment in the session) in the database
			
			$order = ShoppingCart::save_current_order_to_database();
			
			// 4) Save shipping address details
			
			$form->saveInto($order);
			
			$order->write();
			
			// 5) Proceed to payment
			
			$data['BillingId'] = $order->ID;
			
			// Save payment data from form and process payment
						
			$payment = Object::create($data['PaymentMethod']);
			if(! $payment instanceof Payment) user_error(get_class($payment) . ' is not a Payment object !', E_USER_ERROR);
			$form->saveInto($payment);
			$payment->OrderID = $order->ID;
			$payment->Amount = $order->Total();
			
			// Worldpay doesn't have a payment object so we write one here
			if($data['PaymentMethod'] == 'WorldpayPayment')	$payment->write();
			
			$result = $payment->processPayment($data, $form);
			
			if($result[Payment::$success]) { // Successful payment
				$order->sendReceipt();
				$order->write();
				
				ShoppingCart::clear();
				
				Director::redirect(CheckoutPage::find_link() . "OrderSuccessful/$order->ID");
				return;
			}
			// Longer payment process, such as Worldpay
			else if($result['Processing']) return $result['ReturnValue'];
	 		
			else { // Failed payment
				$form->sessionMessage("Sorry, your payment was not accepted, please try again<br/><strong>$result[HelpText]:</strong> $result[MerchantHelpText]", 'bad');
	 			Director::redirect(CheckoutPage::find_link() . $order->ID);
	 			return;
			}
		}
		else { // There is no items in the current order
			$form->sessionMessage('Please add some items to your cart', 'warning');
		   	Director::redirectBack();
		   	return;
		}
	}
}

?>
