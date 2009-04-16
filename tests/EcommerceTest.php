<?php

/**
 * High level tests of the whole ecommerce module
 */
class EcommerceTest extends FunctionalTest {
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	static $disable_theme = true;
	static $use_draft_site = true;
	
	function setUp() {
		parent::setUp();
		Order::set_modifiers(array('SimpleShippingModifier', 'TaxModifier'));
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