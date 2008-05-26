<?php

/**
 * Test a product
 */
class ProductTest extends FunctionalTest {
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	static $disable_theme = true;

	function testPage() {
		$this->useDraftSite();

		$_REQUEST['showqueries'] = 1;
		Debug::show($this->get('group-1'));
	}

}