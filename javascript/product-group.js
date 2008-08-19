$(document).ready(
	function() {
		$('.category').ssnavigation(
			{
				items_list_expr : '.productList',
				item_expr : '.productItem',
				items_sublist_class : 'productGroup',
				
				first_item_expr : '.firstProductIndex',
				last_item_expr : '.lastProductIndex',
				items_total_expr : '.productsTotal',
				items_dropdown_expr : 'select.productsDropdown',
				items_number : 4
			}
		);
	}
);