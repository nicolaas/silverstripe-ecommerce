<div id="Checkout">
	<h3 class="process"><span>Process:</span> &nbsp;<a href="checkout/" title="Click here to go back to the Checkout">Checkout</a> &nbsp;&gt;&nbsp; <span class="current">Order Status</span></h3>
	<div class="typography">
		<h2>Order Incomplete</h2>
		<p>If you ordered by cheque you will receive an email with instructions.</p>
		<p>Here are the details you submitted:</p>
	</div>

	<% control DisplayOrder %>
		<% include OrderInformation %>
	<% end_control %>
			
	<% if OrderSummary %><h2>$OrderSummary</h2><% end_if %>
</div>