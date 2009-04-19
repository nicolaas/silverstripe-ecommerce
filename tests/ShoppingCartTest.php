<?php
/**
 * Test {@link ShoppingCart}
 */
class ShoppingCartTest extends FunctionalTest {

	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	
	static $disable_theme = true;
	
	static $use_draft_site = true;
	
	function testAddItemsToCart() {
		/* Retrieve the product to compare from fixture */
		$productID = $this->idFromFixture('Product', 'p2b');
		
		/* Add 2 items of product-2b to the cart */
		$this->get('product-2b/add');	// New item
		$this->get('product-2b/add'); // Incrementing existing item by 1

		/* Get the items from the cart in session */
		$items = ShoppingCart::get_items();
		
		/* There is 1 item/product in the cart */
		$this->assertTrue(count($items) == 1, 'There is 1 item in the cart');
		
		/* We have the product that we asserted in our fixture file, with a quantity of 2 in the cart */
		$this->assertTrue($items[0]->getIdAttribute() == $productID, 'We have the correct Product ID in the cart.');
		$this->assertTrue($items[0]->getQuantity() == 2, 'We have 2 of this product in the cart.');
	}
	
	function testAddQuanitityToExistingItemInCart() {
	}
	
	function testRemoveItemFromCart() {
	}
	
	function testClearEntireCart() {
		/* Invoke the existing test for adding items to the cart */
		$this->testAddItemsToCart();
		
		/* Get the items from the cart in session */
		$items = ShoppingCart::get_items();
		
		/* We have 1 item in the cart */
		$this->assertTrue(count($items) == 1, 'There is 1 item in the cart');
		
		/* Clear the shopping cart */
		ShoppingCart::clear();
		
		/* Get the items back from the cart again */
		$items = ShoppingCart::get_items();
		
		/* We have nothing in the cart now */
		$this->assertTrue(count($items) == 0, 'There are no items in the cart');
	}
	
}
?>