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
