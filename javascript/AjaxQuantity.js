Behaviour.register({
	'input.ajaxQuantityField' : {
		initialise: function() {
			this.disabled = false;
		},
		onchange : function() {
			//var matches = this.className.match(/product-([0-9]+)/);
			var setQuantityLink = $(this.id + '_SetQuantityLink');
			
			if(setQuantityLink) {
				this.value = this.value.replace(/[^0-9]+/g,'');
				if(!this.value) this.value = 0;
				//var productID = matches[1];
				//var URLSegment = $('Product-' + productID + '-URLSegment').value;
				//var url = document.getElementsByTagName('base')[0].href + URLSegment + '/setQuantity?quantity=' + this.value;
				
				var url = document.getElementsByTagName('base')[0].href + setQuantityLink.value + '?quantity=' + this.value;
				
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