<?php

/**
 * @package ecommerce
 */
 
 /**
  * MemberForm has a set of fields for the AccountPage object.
  * It allows the shop member to edit their details.
  */
class MemberForm extends Form {
	
	function __construct($controller, $name) {
		$member = Member::currentUser();
		
		$contactFields = EcommerceRole::getEcommerceFields();
		$logoutField = new LiteralField('LogoutNote', "<p class=\"message good\">You are currently logged in. Click <a href=\"Security/logout\" title=\"Click here to log out\">here</a> to log out.</p>");
		
		$fields = new FieldSet(
			$logoutField,
			$contactFields,

			new HeaderField("Login Details", 3),
			new PasswordField("Password", "Password"),
			new PasswordField("ConfirmPassword", "Confirm Password")
		);
		
		$actions = new FieldSet(
			new FormAction("submit", "Save Changes"),
			new FormAction("proceed", "Save and proceed to checkout")
		);
		
		$requiredFieldList = array(
			"FirstName",
			"Surname",
			"Address",
			"Email",
			"City",
			"Password"
		);
		
		$RequiredFields = new CustomRequiredFields($requiredFieldList);

		parent::__construct($controller, $name, $fields, $actions, $RequiredFields);
		
		// Load any data avaliable into the form.
		if($member = Member::currentUser()){
			$this->loadNonBlankDataFrom($member);
		}
	}
	
	/**
	 * Save the changes to the form
	 */
	function submit($data, $form) {
		$member = Member::currentUser();
		
		if(!empty($data['Password']) && !empty($data['ConfirmPassword'])) {
			if($data['Password'] == $data['ConfirmPassword']) {
				$member->Password = $data['Password'];
			} else {
				$form->sessionMessage('The passwords do not match', 'bad');
				Director::redirectBack();
			}
		} else {
			$form->dataFieldByName('Password')->setValue($member->Password);
		}

		$form->saveInto($member);
		$member->write();
		$form->sessionMessage('Your details have been saved', 'bad');
		Director::redirectBack();
		return;
	}
	
	/**
	 * Save the changes to the form, and redirect to the checkout page
	 */
	function proceed($data, $form) {
		$member = Member::currentUser();
		
		if(!empty($data['Password']) && !empty($data['ConfirmPassword'])) {
			if($data['Password'] == $data['ConfirmPassword']) {
				$member->Password = $data['Password'];
			} else {
				$form->sessionMessage('The passwords do not match', 'bad');
				Director::redirectBack();
			}
		} else {
			$form->dataFieldByName('Password')->setValue($member->Password);
		}

		$form->saveInto($member);
		$member->write();
		$form->sessionMessage('Your details have been saved', 'bad');
		Director::redirect(CheckoutPage::find_link());
		return;
	}
	
}

?>