Behaviour.register({
	'input.ajaxQuantityField' : {
		initialise: function() {
			this.disabled = false;
		},
		onchange : function() {
			var setQuantityLink = document.getElementsByName(this.name + '_SetQuantityLink')[0];
			
			if(setQuantityLink) {
				if(! this.value) this.value = 0;
				else this.value = this.value.replace(/[^0-9]+/g, '');
			
				var url = document.getElementsByTagName('base')[0].href + setQuantityLink.value + '?quantity=' + this.value;
				
				new Ajax.Request(
					url,
					{
						method: 'get',
						onFailure: function(response) {
							alert("There was an error updating your order information. Please try again.");
						},
						onComplete: function(response) {
							var changes = response.responseText;
							changes = eval('(' + changes + ')');
							for(var i = 0; i < changes.length; i++) {
								var change = changes[i];
								if(change.parameter && change.value) {
									var parameter = change.parameter;
									var value = change.value;
									if(change.id) {
										var id = change.id;
										if($(id)) {
											var task = '$(\'' + id + '\').' + parameter + ' = \'' + escapeHTML(value) + '\';';
											eval(task);
										}
									}
									else if(change.name) {
										var name = change.name;
										var elements = document.getElementsByName(name);
										for(var j = 0; j < elements.length; j++) {
											var element = elements[j];
											var task = 'element.' + parameter + ' = \'' + escapeHTML(value) + '\';';
											eval(task);
										}
									}
								}
							}
						}
					}
				);
			}
		}
	},
	'select.ajaxCountryField' : {
		initialise: function() {
			this.disabled = false;
		},
		onchange : function() {
			// Improve the url checking
			var url = document.location.href.replace(/\/$/,'');
			url += '/setCountry/?country=' + this.value;
			
			new Ajax.Request(
				url,
				{
					method: 'get',
					onFailure: function(response) {
						alert("There was an error updating your order information. Please try again.");
					},
					onComplete: function(response) {
						eval(response.responseText);
					}
				}
			);
		}
	}
});

function escapeHTML(str) {
   var div = document.createElement('div');
   var text = document.createTextNode(str);
   div.appendChild(text);
   return div.innerHTML;
}; 