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
		
		Order::set_modifiers(array(
			'SimpleShippingModifier',
			'TaxModifier'
		));
		
		TaxModifier::set_for_country('NZ', 0.125, 'GST', 'inclusive');
		TaxModifier::set_for_country('UK', 0.175, 'VAT', 'exclusive');
		
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
		
		/* Log in an NZ member in so we can assert a different price set for NZ customers */
		$this->session()->inst_set('loggedInAs', $this->idFromFixture('Member', 'member'));
		
		/* 5 is now charged, because we are logged in with a member from NZ */
		$this->get('checkout/');
		$this->assertPartialMatchBySelector('tr.simpleshippingmodifier td', array(
			'$5.00'
		));
		
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