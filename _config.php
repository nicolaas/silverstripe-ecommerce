<?php

/*
 * This file is needed to identify this as a SilverStripe module 
 */

// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'EcommerceRole');

Director::addRules(50, array(
	ShoppingCart_Controller::$URLSegment . '/$Action/$ID' => 'ShoppingCart_Controller'
));

// This is the default shipping method class - SimpleShippingModifier.
// To use your own one, copy this line, with the second argument as your
// custom shipping class into mysite/_config.php - this will override
// SimpleShippingModifier
Order::set_modifiers(array('SimpleShippingModifier'));

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
