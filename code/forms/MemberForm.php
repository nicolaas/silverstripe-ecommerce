<?php
 /**
  * MemberForm has a set of fields for the AccountPage object.
  * It allows the shop member to edit their details.
  * 
  * @package ecommerce
  */
class MemberForm extends Form {
	
	function __construct($controller, $name) {
		$member = Member::currentUser();
		
		if($member && $member->exists()) {
			$contactFields = $member->getEcommerceFields();
			$logoutField = new LiteralField('LogoutNote', "<p class=\"message good\">" . _t("MemberForm.LOGGEDIN","You are currently logged in as ") . $member->FirstName . ' ' . $member->Surname . ". Click <a href=\"Security/logout\" title=\"Click here to log out\">here</a> to log out.</p>");
			$passwordField = new ConfirmedPasswordField("Password", "Password");
			
			if($member->Password != '') {
				$passwordField->setCanBeEmpty(true);
			}
			
			$fields = new FieldSet(
				$logoutField,
				$contactFields,
	
				new HeaderField("Login Details", 3),
				$passwordField
			);
		} else {
			$fields = new FieldSet();
		}
		
		$actions = new FieldSet(
			new FormAction("submit", "Save Changes"),
			new FormAction("proceed", "Save and proceed to checkout")
		);
		
		$requiredFieldList = array(
			"FirstName",
			"Surname",
			"Address",
			"Email",
			"City"
		);
		
		$requiredFields = new CustomRequiredFields($requiredFieldList);

		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
		
		if($member) $this->loadDataFrom($member);
	}
	
	/**
	 * Save the changes to the form
	 */
	function submit($data, $form) {
		$member = Member::currentUser();
		if(!$member) return false;
		
		$form->saveInto($member);
		$member->write();
		$form->sessionMessage(_t("MemberForm.DETAILSSAVED",'Your details have been saved'), 'bad');
		
		Director::redirectBack();
		return true;
	}
	
	/**
	 * Save the changes to the form, and redirect to the checkout page
	 */
	function proceed($data, $form) {
		$member = Member::currentUser();
		if(!$member) return false;

		$form->saveInto($member);
		$member->write();
		$form->sessionMessage(_t("MemberForm.DETAILSSAVED",'Your details have been saved'), 'bad');

		Director::redirect(CheckoutPage::find_link());
		return true;
	}
	
}
?>