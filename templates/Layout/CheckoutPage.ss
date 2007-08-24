<div id="Checkout">
	<h3 class="process"><span>Process:</span> &nbsp;<span class="current">Checkout</span> &nbsp;&gt;&nbsp;Order Status</h3>
	
	<div class="typography">
		<h2>$Title</h2>
		<% if Content %>
			$Content
		<% end_if %>
	</div>
	
	<% control DisplayOrder %>
		<% include OrderInformation_Editable %>
	<% end_control %>
	
	$OrderForm	
</div>