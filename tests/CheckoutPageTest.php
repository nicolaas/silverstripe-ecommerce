<?php

class CheckoutPageTest extends FunctionalTest {
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	static $disable_theme = true;
	static $use_draft_site = true;

	function testFindLink() {
		/* find_link returns a link to the checkout page, ending in a slash */
		$link = CheckoutPage::find_link();
		$this->assertEquals(Director::baseURL() . 'checkout/', $link);

		/* If there is no checkout page, then an exception is thrown */
		$page = DataObject::get_one("CheckoutPage");
		$page->delete();		
		$page->flushCache();
		
		$this->setExpectedException('Exception');
		$link = CheckoutPage::find_link();
	}
	
	function testCheckout() {
		/* Add a couple of items to the cart */
		$this->get('product-1a/add');
		$this->get('product-1b/add');
		$this->get('product-1b/add');
		
		/* Check the cart */
		$this->get('checkout/');
		//Debug::message($this->content());
		$this->assertExactMatchBySelector('#InformationTable tr.orderitem td.product a', array(
			'Product 1a',
			'Product 1b',
		));
		$this->assertExactHTMLMatchBySelector('#InformationTable tr.orderitem td.quantity input.ajaxQuantityField', array(
			'<input name="Product_OrderItem_0_Quantity" class="ajaxQuantityField" type="text" value="1" size="3" maxlength="3" disabled="disabled"/>',
			'<input name="Product_OrderItem_1_Quantity" class="ajaxQuantityField" type="text" value="2" size="3" maxlength="3" disabled="disabled"/>',
		));
		$this->assertExactMatchBySelector('#InformationTable tr.orderitem td.tota', array(
			'$500.00',
			'$1,200.00',
		));

		$this->assertExactMatchBySelector('#Table_Order_SubTotal', array(
			'$1,700.00',
		));
	}
	
}