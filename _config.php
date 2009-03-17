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

/*
 * MemberTableField::addPermissions() has been deperated,
 * Please set permissions using setPermissions(Array) on the MemberTableField object.
 */
//MemberTableField::addPermissions(array('show', 'export'));

/*
 * MemberTableField::addMembershipFields() has been deprecated.
 * Please implement updateSummaryFields() on a Member decorator instead.
 */

// Add additional fields to the MemberTableField in the CMS for e-commerce.
/*MemberTableField::addMembershipFields(array(
	'Address' => 'Address',
	'AddressLine2' => 'Address Line 2',
	'HomePhone' => 'Home Phone',
	'MobilePhone' => 'Mobile Phone',
	'City' => 'City',
	'Country' => 'Country'
));*/

LeftAndMain::require_css('ecommerce/css/DataReportCMSMain.css');
LeftAndMain::require_javascript('ecommerce/javascript/DataReport.js');

?>
