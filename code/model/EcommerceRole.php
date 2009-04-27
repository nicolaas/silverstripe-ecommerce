<?php
/**
 * EcommerceRole provides customisations to the {@link Member}
 * class specifically for this ecommerce module.
 * 
 * @package ecommerce
 */
class EcommerceRole extends DataObjectDecorator {

	function extraStatics() {
		return array(
			'db' => array(
				'Address' => 'Varchar',
				'AddressLine2' => 'Varchar',
				'City' => 'Varchar',
				'Country' => 'Varchar',
				'HomePhone' => 'Varchar',
				'MobilePhone' => 'Varchar',
				'Notes' => 'HTMLText'
			)
		);
	}

	function updateCMSFields($fields) {
		$fields->removeByName('Country');
		$fields->addFieldToTab('Root.Main', new DropdownField('Country', 'Country', Geoip::getCountryDropDown()));
	}
	
	/**
	 * Return the member fields to be shown on {@link OrderForm}.
	 * @return FieldSet
	 */
	function getEcommerceFields() {
		$fields = new FieldSet(
			new HeaderField('Personal Information', 3),
			new TextField('FirstName', 'First Name'),
			new TextField('Surname', 'Surname'),
			new TextField('HomePhone', 'Phone'),
			new TextField('MobilePhone', 'Mobile'),
			new EmailField('Email', 'Email'),
			new TextField('Address', 'Address'),
			new TextField('AddressLine2', '&nbsp;'),
			new TextField('City', 'City'),
			new DropdownField('Country', 'Country', Geoip::getCountryDropDown(), self::findCountry())
		);
		
		$this->owner->extend('augmentEcommerceFields', $fields);
		
		return $fields;
	}

	/**
	 * Return which member fields should be required on {@link OrderForm}
	 * and {@link ShopAccountForm}.
	 * 
	 * @return array
	 */
	function getEcommerceRequiredFields() {
		$fields = array(
			'FirstName',
			'Surname',
			'Email',
			'Address',
			'City',
			'Country'
		);
		
		$this->owner->extend('augmentEcommerceRequiredFields', $fields);
		
		return $fields;
	}
	
	function CountryTitle() {
		return self::findCountryTitle($this->owner->Country);
	}

	/**
	 * Create a new member from the given data or merge with the built-in fields.
	 * 
	 * @param data the array data from a submitted form.
	 * @return Member record that was updated or created
	 */
	public static function createOrMerge($data) {
		// Because we are using a ConfirmedPasswordField, the password will
		// be an array of two fields
		if(isset($data['Password']) && is_array($data['Password'])) {
			$data['Password'] = $data['Password']['_Password'];
		}
		
		if($existingMember = Member::currentUser()) {
			$existingMember->update($data);
			
			return $existingMember;
		} else {
			$member = new Member();
			$member->update($data);
			
			return $member;
		}
	}
	
	/**
	 * Find the member's country.
	 *
	 * If there is no member logged in, try to resolve
	 * their IP address to a country.
	 *
	 * @return string Found country of member
	 */
	static function findCountry() {
		$member = Member::currentUser();
		
		if($member && $member->Country) {
			$country = $member->Country;
		} else {
			// HACK Avoid CLI tests from breaking (GeoIP gets in the way of unbiased tests!)
			// @todo Introduce a better way of disabling GeoIP as needed (Geoip::disable() ?)
			if(Director::is_cli()) {
				$country = null;
			} else {
				$country = Geoip::visitor_country();
			}
		}
		
		return $country;
	}
	
	/**
	 * Give the two letter code to resolve the title of the country.
	 *
	 * @param string $code Country code
	 * @return string|boolean String if country found, boolean FALSE if nothing found
	 */
	static function findCountryTitle($code) {
		$countries = Geoip::getCountryDropDown();
		// check if code was provided, and is found in the country array
		if($code && $countries[$code]) {
			return $countries[$code];		
		} else {
			return false;
		}
	}

}
?>