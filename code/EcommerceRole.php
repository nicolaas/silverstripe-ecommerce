<?php

/**
 * @package ecommerce
 */

/**
 * EcommerceRole is a DataObjectDecorator for the member class to allow additional
 * member fields for the module. It has a base set of contact fields that can be
 * statically called anywhere in the system using EcommerceRole::getEcommerceFields();
 * The OrderForm and MemberForm class uses this call.
 */
class EcommerceRole extends DataObjectDecorator {

 	/**
	 * Edit the given query object to support queries for this extension
	 */
	function augmentSQL(SQLQuery &$query) {}

 	/**
	 * Update the database schema as required by this extension.
	 */
	function augmentDatabase() {
 		$exist = DB::query("SHOW TABLES LIKE 'ShopMember'")->numRecords();
 		if($exist > 0) {
 			DB::query("UPDATE `Member`, `ShopMember` " .
 				"SET `Member`.`ClassName` = 'Member'," .
 				"`Member`.`Address` = `ShopMember`.`Address`," .
 				"`Member`.`AddressLine2` = `ShopMember`.`AddressLine2`," .
 				"`Member`.`City` = `ShopMember`.`City`," .
 				"`Member`.`Country` = `ShopMember`.`Country`," .
 				"`Member`.`HomePhone` = `ShopMember`.`HomePhone`," .
 				"`Member`.`MobilePhone` = `ShopMember`.`MobilePhone`," .
  				"`Member`.`Notes` = `ShopMember`.`Notes`" .				
 				"WHERE `Member`.`ID` = `ShopMember`.`ID`"
 			);
 			echo("<div style=\"padding:5px; color:white; background-color:blue;\">The data transfer has succeeded. However, to complete it, you must delete the ShopMember table. To do this, execute the query \"DROP TABLE 'ShopMember'\".</div>");
 		}
	}

	/**
	 * Define extra database fields for this extension.
	 */
	function extraDBFields() {
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

	/**
	 * Add fields to the member popup box in the CMS.
	 */
	function updateCMSFields(FieldSet &$fields) {
		$fields->push(new TextField('HomePhone', 'Phone'));
		$fields->push(new TextField('MobilePhone', 'Mobile'));
		$fields->push(new TextField('Address', 'Address'));
		$fields->push(new TextField('AddressLine2', 'Address Line 2'));
		$fields->push(new TextField('City', 'City'));
		$fields->push(new DropdownField('Country', 'Country', Geoip::getCountryDropDown()));
	}
	
	/**
	 * Define the objects for these database fields used by other forms like MemberForm and OrderForm.
	 */
	static function getEcommerceFields() {
		return new CompositeField(
			new HeaderField('Personal Information', 3),
			new TextField('FirstName', 'First Name'),
			new TextField('Surname', 'Surname'),
			new TextField('HomePhone', 'Phone'),
			new TextField('MobilePhone', 'Mobile'),
			new EmailField('Email', 'Email'),
			new TextField('Address', 'Address'),
			new TextField('AddressLine2', ''),
			new TextField('City', 'City'),
			new DropdownField('Country', 'Country', Geoip::getCountryDropDown(), self::findCountry())
		);
	}

	/**
	 * Create a new member from the given data or merge with the built-in fields.
	 * @param data the array data from a submitted form.
	 */
	public static function createOrMerge($data) {
		$email = is_array($data) ? $data['Email'] : $data->Email;
		if($existingMember = DataObject::get_one('Member', "Email = '$email'")) {
			$existingMember->update($data);
			return $existingMember;
		} else {
			return new Member($data);
		}
	}
	
	/**
	 * Find the member country, if the member doesn't exist then return
	 * the Geoip visitor country based on their IP address.
	 */
	static function findCountry(){
		$member = Member::currentUser();
		if($member && $member->Country) {
			$country = $member->Country;
		} else {
			$country = Geoip::visitor_country();
		}
		return $country;
	}
	
	/**
	 * Give the two letter code to resolve the title of the country.
	 * @param $code - the two letter country code you want the full name of.
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
