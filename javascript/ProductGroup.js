Behaviour.register({
	'.ajaxQuantityField' : {
		initialise: function() {
			this.disabled = false;
		},
		onchange : function() {
			var matches = this.className.match(/product-([0-9]+)/);
			
			if(matches) {
				this.value = this.value.replace(/[^0-9]+/g,'');
				if(!this.value) this.value = 0;
				var productID = matches[1];
				var url = document.getElementsByTagName('base')[0].href + 'Order/setCartQuantity/?ProductID=' + productID + '&Quantity=' + this.value + "&isProductGroup=1";
				
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
	}
});