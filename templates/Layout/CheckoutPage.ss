<div id="Checkout">
	<h3 class="process"><span><% _t("PROCESS","Process") %>:</span> &nbsp;<span class="current"><% _t("CHECKOUT","Checkout") %></span> &nbsp;&gt;&nbsp;<% _t("ORDERSTATUS","Order Status") %></h3>
	
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
