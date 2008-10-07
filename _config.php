<?php

/*
 * This file is needed to identify this as a SilverStripe module 
 */

// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'EcommerceRole');

Director::addRules(50, array(
	ShoppingCart_Controller::$URLSegment . '/$Action/$ID' => 'ShoppingCart_Controller',
	WorldpayPayment_Handler::$URLSegment . '/$Action/$ID' => 'WorldpayPayment_Handler',
	PayPalPayment_Handler::$URLSegment . '/$Action/$ID' => 'PayPalPayment_Handler'
));

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

LeftAndMain::require_css('ecommerce/css/DataReportCMSMain.css');
LeftAndMain::require_javascript('ecommerce/javascript/DataReport.js');

?>
