<?php

/**
 * @package ecommerce
 */
 
 /**
  * Order form for the checkout object
  */
class OrderForm extends Form{
	function __construct($controller, $name) {
				
		// get the orders and member info (if available) and define the total number format
		//$sc = Order::ShoppingCart();
		$sc = CurrentOrder::display_order();
		$total = '$' . number_format($sc->Total(), 2);
		$member = Member::currentUser();
		
		// create country field to change country (so the order amounts can be recalculated if necessary)
		/*$countryField = new DropdownField("Country", "", Geoip::getCountryDropDown(), EcommerceRole::findCountry());
		$countryField = $countryField->performReadonlyTransformation();
		$countryFieldGroup = new FieldGroup(
			'Country',
			$countryField,
			new FormAction('ChangeCountry', 'Change Country')
		);
		$countryFieldGroup->subfieldParam = 'Field';*/

		// check if there is a shipping country set, otherwise use the findCountry function for a member
		if($sc->ShippingCountry) {
			$shippingCountry = $sc->ShippingCountry;
		} else {
			$shippingCountry = EcommerceRole::findCountry();
		}
		
		// create country field to change country (so the order amounts can be recalculated if necessary)
		/*$shippingCountryField = new DropdownField("ShippingCountry", "", Geoip::getCountryDropDown(), $shippingCountry);
		$shippingCountryField = $shippingCountryField->performReadonlyTransformation();
		$shippingCountryFieldGroup = new FieldGroup(
			'Country',
			$shippingCountryField,
			new FormAction('ChangeCountry2', 'Change Country')
		);
		$shippingCountryFieldGroup->subfieldParam = 'Field';*/

		// setup password fields
		if(!$member || $member->Password == '') {
			$membershipHeader = new HeaderField("Membership details", 3);
			$memberField = new LiteralField('MemberInfo', "<p class=\"message good\">If your are already a member, please <a href=\"Security/login?BackURL=checkout/\">login</a>.</p>");
			$accountField = new LiteralField("AccountInfo", "<p>Please choose a password, so you can login and check your order history in the future.</p><br />");
			$passwordField = new FieldGroup(
				new ConfirmedPasswordField('Password', 'Password')
			);
			$member = new Member();
		} else {
			$membershipHeader = new HiddenField('MembershipHeaderHidden', '');
			$memberField = new HiddenField('MemberInfo', '');
			$accountField = new HiddenField('AccountInfo', '');
			$passwordField = new HiddenField('PasswordHidden', '');
		}
		
		// if a terms page is in the system, create a field to confirm the user read it
		if($tacPage = DataObject::get_one('EcommerceTermsPage')) {
			$readConditionsField = new CheckboxField('ReadConditions', "I agree to the terms and conditions stated on the <a href=\"$tacPage->URLSegment\" title=\"Read the shop terms and conditions for this site\">terms and conditions</a> page");
		} else {
			$readConditionsField = new HiddenField('ReadConditions', '');
		}
		
		// initialise variables with contact fields from member, remove country because orderform requires
		// a custom country field setup

		$contactFields = $member->getEcommerceFields();
		$contactFields->removeByName('Country');
		//$contactFields->fieldByName('Country')->addExtraClass('ajaxCountryField');
		$countryField = new DropdownField('Country', 'Country', Geoip::getCountryDropDown(), $sc->findShippingCountry(true));
		if(! CurrentOrder::uses_different_shipping_address()) $countryField->addExtraClass('ajaxCountryField');
		$contactFields->push($countryField);
		
		// setup the shipping fields, if UseShippingAddress is true (can be set when changing country)
		/*if($sc->UseShippingAddress) {
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
		}*/
		if(CurrentOrder::uses_different_shipping_address()) {
			$country2Field = new DropdownField('ShippingCountry', 'Country', Geoip::getCountryDropDown(), $sc->findShippingCountry(true));
			$country2Field->addExtraClass('ajaxCountryField');
			$shippingFields = new CompositeField(
				new HeaderField('Send goods to different address', 3),
				new LiteralField('ShippingNote', '<p class="warningMessage"><em>Your goods will be sent to the address below.</em></p>'),
				new LiteralField('Help', '<p>You can use this for gift giving; no billing information will be disclosed to this address.</p>'),
				new TextField('ShippingName', 'Name', CurrentOrder::get_name_different_shipping_address()),
				new TextField('ShippingAddress', 'Address', CurrentOrder::get_address_different_shipping_address()),
				new TextField('ShippingAddress2', '', CurrentOrder::get_address2_different_shipping_address()),
				new TextField('ShippingCity', 'City', CurrentOrder::get_city_different_shipping_address()),
				$country2Field,
				new HiddenField('UseShippingAddress', '', true),
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
				//$countryFieldGroup,
				$shippingFields
			),
			$right = new CompositeField(
				$membershipHeader,
				$memberField,
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

		// if terms page exists, add validation for the field on the form
		if($tacPage) {
			$requiredFieldsArr[] = 'ReadConditions';
		}
		
		// if UseShippingAddress is true, require validation from these fields
		if(CurrentOrder::uses_different_shipping_address()) {
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
		$RequiredFields = new CustomRequiredFields(
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
		
		// Update the country if necessary
		if(! CurrentOrder::uses_different_shipping_address()) $countryField->setValue($sc->findShippingCountry(true));
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
