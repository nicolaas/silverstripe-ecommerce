<?php

/*
 * This file is needed to identify this as a SilverStripe module 
 */

// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'EcommerceRole');

// Add additional fields to the MemberTableField in the CMS for e-commerce.
MemberTableField::addPermissions(array('show', 'export'));
MemberTableField::addMembershipFields(array(
	'Address' => 'Address',
	'AddressLine2' => 'Address Line 2',
	'HomePhone' => 'Home Phone',
	'MobilePhone' => 'Mobile Phone',
	'City' => 'City',
	'Country' => 'Country'
));

?>
