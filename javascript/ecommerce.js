$(document).ready(
	function() {
		$('input.ajaxQuantityField').each(
			function() {
				$(this).attr('disabled', false);
				$(this).change(
					function() {
						var name = $(this).attr('name')+ '_SetQuantityLink';
						var setQuantityLink = $('[name=' + name + ']');
						if($(setQuantityLink).length > 0) {
							setQuantityLink = $(setQuantityLink).get(0);
							if(! this.value) this.value = 0;
							else this.value = this.value.replace(/[^0-9]+/g, '');
							var url = $('base').attr('href') + setQuantityLink.value + '?quantity=' + this.value;
							$.getJSON(url, null, setChangesJQuery);
						}
					}
				);
			}
		);
		$('select.ajaxCountryField').each(
			function() {
				$(this).attr('disabled', false);
				$(this).change(
					function() {
						var id = '#' + $(this).attr('id') + '_SetCountryLink';
						var setCountryLink = $(id);
						if($(setCountryLink).length > 0) {
							setCountryLink = $(setCountryLink).get(0);
							var url = $('base').attr('href') + setCountryLink.value + '/' + this.value;
							$.getJSON(url, null, setChangesJQuery);
						}
					}
				);
			}
		);
	}
);

function setChanges(changes) {
	for(var i in changes) {
		var change = changes[i];
		if(typeof(change.parameter) != 'undefined' && typeof(change.value) != 'undefined') {
			var parameter = change.parameter;
			var value = escapeHTML(change.value);
			if(change.id) {
				var id = '#' + change.id;
				$(id).attr(parameter, value);
			}
			else if(change.name) {
				var name = change.name;
				$('[name=' + name + ']').each(
					function() {
						$(this).attr(parameter, value);
					}
				);
			}
		}
	}
}

function escapeHTML(str) {
   var div = document.createElement('div');
   var text = document.createTextNode(str);
   div.appendChild(text);
   return div.innerHTML;
}