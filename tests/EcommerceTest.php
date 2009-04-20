<?php
/**
 * High level tests of the whole ecommerce module.
 * 
 * @package ecommerce
 */
class EcommerceTest extends FunctionalTest {
	
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	
	static $disable_theme = true;
	
	static $use_draft_site = true;
	
	function setUp() {
		parent::setUp();
		
		/* Set the modifiers to test */
		Order::set_modifiers(array(
			'SimpleShippingModifier',
			'TaxModifier'
		));
		
		/* Set the tax configuration on a per-country basis to test */
		TaxModifier::set_for_country('NZ', 0.125, 'GST', 'inclusive');
		TaxModifier::set_for_country('UK', 0.175, 'VAT', 'exclusive');
		
		/* Let's check that we have the Payment module installed properly */
		$this->assertTrue(class_exists('Payment'), 'Payment module is installed.');
		
		/* Set the site currency to NZD - this updates all the pricing labels from USD to NZD */
		Payment::set_site_currency('NZD');
		
		/* Set up the simple shipping calculator to test */
		SimpleShippingModifier::set_default_charge(10);
		SimpleShippingModifier::set_charges_for_countries(array(
			'NZ' => 5,
			'UK' => 20
		));
	}
	
	function testTaxModifier() {
		/* Add 2 of the product-1b to the shopping cart */
		$this->get('product-1b/add');
		$this->get('product-1b/add');

		/* Log our NZ member in so we can assert they see the GST component */
		$this->session()->inst_set('loggedInAs', $this->idFromFixture('Member', 'member'));
		
		/* 12.5% GST appears to our NZ user logged in */
		$this->get('checkout/');
		$this->assertPartialMatchBySelector('tr.taxmodifier td', array(
			'12.5% GST (included in the above price)'
		));

		/* Let's check the totals to make sure GST wasn't being added (which is important!) */
		/* NZD is shown as the label, since it was set as the site currency in setUp() */
		$this->assertExactMatchBySelector('#Table_Order_Total', '$1,205.00 NZD');
		
		/* Let's sneakily change the GST to be exclusive, altering the checkout total */
		TaxModifier::set_for_country('NZ', 0.125, 'GST', 'exclusive');
		
		/* See what the checkout page has got now */
		$this->get('checkout/');
		
		/* Check the total, it has changed since the GST is now exclusive */
		$this->assertExactMatchBySelector('#Table_Order_Total', '$1,355.63 NZD');
				
		/* Member logs out */
		$this->session()->inst_set('loggedInAs', null);
	}
	
	function testSimpleShippingModifier() {
		/* Add 2 of the product-1b to the shopping cart */
		$this->get('product-1b/add');
		$this->get('product-1b/add');

		/* Initially, 10 should be charged for everyone */
		$this->get('checkout/');
		$this->assertPartialMatchBySelector('tr.simpleshippingmodifier td', array(
			'$10.00'
		));
		
		/* Check the total is correct */
		$this->assertExactMatchBySelector('#Table_Order_Total', '$1,210.00 NZD');
		
		/* Log in an NZ member in so we can assert a different price set for NZ customers */
		$this->session()->inst_set('loggedInAs', $this->idFromFixture('Member', 'member'));
		
		/* 5 is now charged, because we are logged in with a member from NZ */
		$this->get('checkout/');
		$this->assertPartialMatchBySelector('tr.simpleshippingmodifier td', array(
			'$5.00'
		));
		
		/* Check the total was updated with the change in shipping applied */
		$this->assertExactMatchBySelector('#Table_Order_Total', '$1,205.00 NZD');
		
		/* Member logs out */
		$this->session()->inst_set('loggedInAs', null);
	}

	function testCanViewAccountPage() {
		/* If we're not logged in we get directed to the log-in page */
		$this->get('account/');
		$this->assertPartialMatchBySelector('p.message', array(
			"You'll need to login before you can access the account page. If you are not registered, you won't be able to access it until you'll make your first order, otherwise please enter your details below.", ));

		/* But if we're logged on you can see */
		$this->session()->inst_set('loggedInAs', $this->idFromFixture('Member', 'member'));
		$this->get('account/');
		$this->assertPartialMatchBySelector('#PastOrders h3', array('Your Order History'));
	}
	
	function testCanViewCheckoutPage() {
		$this->get('checkout/');
	}
	
	function testCanViewProductPage() {
		$this->get('product-1a/');
		$this->get('product-2b/');
	}
	
	function testCanViewProductGroupPage() {
		$this->get('group-1/');
		$this->get('group-2/');
	}
	
}
?>