_ALL_PAYMENT_METHODS = [];

function PaymentMethodChanged() {
	var i, divEl;
	for(i = 0; i < _ALL_PAYMENT_METHODS.length; i++) {
		divEl = $('MethodFields_' + _ALL_PAYMENT_METHODS[i]);
		if(divEl) {
			divEl.style.display = (_ALL_PAYMENT_METHODS[i] == this.value) ? 'block' : 'none';
		} 
	}
}

Behaviour.register({
	'#PaymentMethod input[type=radio]' : {
		initialise: function() {
			_ALL_PAYMENT_METHODS[_ALL_PAYMENT_METHODS.length] = this.value;

			var i, divEl;
			for(i = 0; i < _ALL_PAYMENT_METHODS.length; i++) {
				divEl = $('MethodFields_' + _ALL_PAYMENT_METHODS[i]);
				if(i == 0) {
					divEl.style.display = 'block';
				} else {
					divEl.style.display = 'none';
				}
			}
		},
		onclick: PaymentMethodChanged
	},
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
				var URLSegment = $('Product-' + productID + '-URLSegment').value;
				var url = document.getElementsByTagName('base')[0].href + URLSegment + '/setQuantity?quantity=' + this.value + '&isCheckout=1';
				//var url = document.getElementsByTagName('base')[0].href + 'Order/setCartQuantity/?ProductID=' + productID + '&Quantity=' + this.value + "&isCheckout=1";
				
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
	'#OrderForm_OrderForm_action_ChangeCountry' : {
		onclick: function() {
			$('OrderForm_OrderForm').onsubmit = function() {
				return true;
			}
		}
	},
	'#OrderForm_OrderForm_action_ChangeCountry2' : {
		onclick: function() {
			$('OrderForm_OrderForm').onsubmit = function() {
				return true;
			}
		}
	},
	'#OrderForm_OrderForm_action_useDifferentShippingAddress' : {
		onclick: function() {
			$('OrderForm_OrderForm').onsubmit = function() {
				return true;
			}
		}
	},
	'#OrderForm_OrderForm_action_useBillingAddress' : {
		onclick: function() {
			$('OrderForm_OrderForm').onsubmit = function() {
				return true;
			}
		}
	}
});
