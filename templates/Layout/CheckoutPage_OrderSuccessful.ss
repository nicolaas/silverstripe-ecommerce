<div id="Checkout">
	<h3 class="process"><span>Process:</span> &nbsp;<a href="checkout/" title="Click here to go back to the Checkout">Checkout</a> &nbsp;&gt;&nbsp; <span class="current">Order Status</span></h3>
	
	<div class="typography">
		<h2>Order Successful</h2>
		<p><strong>A copy of this has been sent to your email address confirming the order details.</strong></p>
		$PurchaseComplete
	</div>
	
	<% control DisplayOrder %>
		<% include OrderInformation %>
	<% end_control %>
			
	<% if OrderSummary %>
		<h2>$OrderSummary</h2>
	<% end_if %>
</div>