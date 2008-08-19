jQuery.fn.ssnavigation = function(options) {
	
	var defaults = {
		to_show_expr : 'img.show',
		to_hide_expr : 'img.hide',
		items_list_expr : '.itemsList',
		item_expr : '.item',
		items_sublist_class : 'itemsSubList',
		
		pagination : {
			prev_text : '&lt; Previous',
			prev_show_always : false,
			next_text : 'Next &gt;',
			next_show_always : false,
			num_edge_entries : 1
		},
		
		results_bar_expr : '.resultsBar',
		first_item_expr : '.firstItemIndex',
		last_item_expr : '.lastItemIndex',
		items_total_expr : '.itemsTotal',
		items_dropdown_expr : 'select.itemsDropdown',
		items_number : 3
	};
	
	var params = jQuery.extend(true, defaults, options || {});
			
	return this.each(
		function() {
						
			// 1) Main Settings (Static)
						
			var categoryID = this.id;
			var itemsListExpr = '#' + categoryID + ' ' + params.items_list_expr;
			var itemsSubListExpr = itemsListExpr + ' .' + params.items_sublist_class;
			var itemsExpr = itemsListExpr + ' ' + params.item_expr;
			
			var itemsList = $(itemsListExpr);
			var items = $(itemsExpr);
			var itemsLength = items.length;
						
			var itemsListWidth = $(itemsListExpr).width();
			
			var itemsSubListWrapHtml = '<li class="' + params.items_sublist_class + '"><ul></ul><div class="clear"><!-- --></div></li>';
						
			var resultsBarExpr = '#' + categoryID + ' ' + params.results_bar_expr;
			var firstItemExpr = resultsBarExpr + ' ' + params.first_item_expr;
			var lastItemExpr = resultsBarExpr + ' ' + params.last_item_expr;
			var itemsTotalExpr = resultsBarExpr + ' ' + params.items_total_expr;
			var itemsDropdownExpr = resultsBarExpr + ' ' + params.items_dropdown_expr;
			
			var itemsDropdown = $(itemsDropdownExpr);
			
			// 2) Attributes
			
			var pageIndex = 0;
			var itemsPerPage = params.items_number <= itemsLength ? params.items_number : itemsLength;
			
			// 3) Functions
						
			function initToggleElements() {
				
				// a) Main Expressions
				
				var toggleElementsNameExpr = '[name=' + categoryID + ']';
				var showToggleElementsExpr = params.to_show_expr + toggleElementsNameExpr;
				var toggleElementsExpr = showToggleElementsExpr + ',' + params.to_hide_expr + toggleElementsNameExpr;
				
				// b) Show Toggle Elements Hiding
				
				$(showToggleElementsExpr).hide();
				
				// c) Toggle Functionality Setting
				
				$(toggleElementsExpr).click(
					function() {
						$(toggleElementsExpr).toggle();
						$('#' + categoryID).slideToggle('slow');
					}
				);
			}
			
			function initSubLists() {
				var start = 0;
				
				while(start < itemsLength) {
					$(itemsExpr + ':gt(' + (start - 1) + '):lt(' + itemsPerPage + ')').wrapAll(itemsSubListWrapHtml);
					start += itemsPerPage;
				}
				
				$(itemsListExpr).css('overflow-x', 'hidden');
				$(itemsListExpr).css('width', itemsListWidth * itemsLength);
				
				$(itemsListExpr + ' .' + params.items_sublist_class).css('width', itemsListWidth).css('float', 'left');
			}
			
			function initDropdown() {
				
				// a) Dropdown Enabling
				
				$(itemsDropdown).attr('disabled', false);
				$(itemsDropdown).empty();
				
				// b) Options Creation
				
				for(var i = 1; i <= itemsLength; i++) {
					var optionParams = {'value' : i};
					if(i == itemsPerPage) jQuery.extend(optionParams, {'defaultSelected' : true});
					var option = jQuery.create('option', optionParams, [i]);
					$(itemsDropdown).append(option);
				}
								
				// c) Dropdown Registration To Changes
				
				$(itemsDropdown).change(
					function() {
						updateSubLists(parseInt(this.value));
					}
				);
			}
			
			function updatePagination(newPageIndex, navigationBar, jQueryFunction) {
				
				// a) Main Settings
				
				pageIndex = newPageIndex;
				var itemsPreviousSubListsExpr = itemsSubListExpr + ':lt(' + pageIndex + ')';
				var itemsCurrentNextSubListsExpr = itemsSubListExpr + ':gt(' + (pageIndex - 1) + ')';
				var marginLeft = - itemsListWidth;
				
				jQueryFunction = jQueryFunction || jQuery.fn.animate;
												
				// b) Sub Lists Selection
				
				var itemsPreviousSubLists = $(itemsPreviousSubListsExpr).filter(
					function() {
						return $(this).css('margin-left') != marginLeft + 'px';
					}
				);
				var itemsCurrentNextSubLists = $(itemsCurrentNextSubListsExpr).filter(
					function() {
						return $(this).css('margin-left') == marginLeft + 'px';
					}
				);
				
				// c) Sub Lists Animation
				
				jQueryFunction.apply($(itemsPreviousSubLists), [{'marginLeft' : marginLeft}]);
				jQueryFunction.apply($(itemsCurrentNextSubLists), [{'marginLeft' : 0}]);
				
				// d) Results Bar Update
				
				updateResultsBar();
				
				return false;
			}
			
			function updateResultsBar() {
				
				$(firstItemExpr).text(itemsPerPage * pageIndex + 1);
				$(lastItemExpr).text(Math.min(itemsPerPage * (pageIndex + 1), itemsLength));
				$(itemsTotalExpr).text(itemsLength);
				
			}
			
			function updateSubLists(itemsPerPageNew) {
				var removeItems = itemsPerPageNew < itemsPerPage;
				itemsPerPage = itemsPerPageNew;
				
				$(itemsSubListExpr).each(
					function(indexSubList) {
						
						// a) Main Settings
						
						var itemsInSubList = $(this).find(params.item_expr);
						var itemsInSubListLength = $(itemsInSubList).length;
						var indexNextSubList = indexSubList + 1;
						var nextSubList = $(itemsSubListExpr + ':eq(' + indexNextSubList + ')');
						var itemsInNextSubList = $(nextSubList).find(params.item_expr);
						
						// b) Sub Lists Reorganisation
						
						if(removeItems) {
							var itemsToNextSubList = $(itemsInSubList).filter(
								function(indexItem) {return indexItem >= itemsPerPage;}
							);
							
							if(itemsToNextSubList.length > 0) {
								
								$(itemsToNextSubList).remove();
								
								if(nextSubList.length == 0) {
									for(var i = indexNextSubList; i < itemsSubListsLength(); i++) {
										$(itemsList).append(itemsSubListWrapHtml);
										$(itemsListExpr + ' .' + params.items_sublist_class).css('width', itemsListWidth).css('float', 'left');
										nextSubList = $(itemsSubListExpr + ':eq(' + i + ')');
										nextSubList = nextSubList.get(0);
										var itemsToNextSubListRange = itemsToNextSubList.get().splice((i - indexNextSubList) * itemsPerPage, itemsPerPage);
										$(nextSubList).find('ul').append(itemsToNextSubListRange);
									}
								}
								else {
									nextSubList = nextSubList.get(0);
									
									$(nextSubList).find('ul').prepend(itemsToNextSubList);
								}
								
								$(itemsToNextSubList).show('drop', {direction : 'left'}, 1000);
							}
						}
						else {
							var itemsInSubListLastIndex = indexSubList * itemsPerPage + itemsInSubListLength - 1;
							var itemsFromNextSubLists = $(itemsExpr + ':gt(' + itemsInSubListLastIndex + '):lt(' + (itemsPerPage - itemsInSubListLength) + ')');
							
							$(itemsFromNextSubLists).remove();
							$(this).find('ul').append(itemsFromNextSubLists);
							$(itemsFromNextSubLists).show('drop', {direction : 'right'}, 1000);
						}
					}
				);
				
				$(itemsSubListExpr + ':gt(' + (itemsSubListsLength() - 1) + ')').remove();
				
				// c) Navigation Bar Update
				
				var navigationBar = $('#' + categoryID + ' .navigationBar');
				var paginationParams = {
					items_per_page : itemsPerPage,
					callback : updatePagination
				};
				paginationParams = jQuery.extend(paginationParams, params.pagination);
				if(pageIndex >= itemsSubListsLength()) pageIndex = itemsSubListsLength() - 1; 
				paginationParams.current_page = pageIndex;
				
				$(navigationBar).pagination(itemsLength, paginationParams);
				
				// d) Apply The Current Page Parameter Value To The Sub Lists
				
				paginationParams.callback(paginationParams.current_page, navigationBar);
			}
			
			function itemsSubListsLength() {return Math.ceil(itemsLength / itemsPerPage);}
						
			// 4) Toggle Elements Initialisation
			
			initToggleElements();
			
			// 5) Items Wrapping In Sub Lists
			
			initSubLists();
			
			// 6) Dropdown Initialisation
			
			initDropdown();
			
			// 7) Navigation Bar Creation (If There Is More Than One Page)
			
			if(itemsLength > itemsPerPage) {
				
				// a) Navigation Bar Node Creation
				
				var navigationBar = jQuery.create(
					'div',
					{
						'class' : 'navigationBar'
					}
				);
				
				// b) Navigation Bar Node Adding To The HTML Code
				
				var clearDivExpr = itemsListExpr + ' + div.clear';
				
				if($(clearDivExpr)) $(clearDivExpr).after(navigationBar);
				else $(itemsListExpr).after(navigationBar);
				
				// c) Register The Navigation Bar Node To The Pagination System
				
				var paginationParams = {
					items_per_page : itemsPerPage,
					callback : updatePagination
				};
				paginationParams = jQuery.extend(paginationParams, params.pagination);
				if(paginationParams.current_page && paginationParams.current_page >= itemsSubListsLength()) paginationParams.current_page = itemsSubListsLength() - 1;
				
				$(navigationBar).pagination(itemsLength, paginationParams);
				
				// d) Apply The Current Page Parameter Value To The Sub Lists
				
				if(paginationParams.current_page) paginationParams.callback(paginationParams.current_page, navigationBar, jQuery.fn.css);
			}
			else updateResultsBar();
		}
	);
}


