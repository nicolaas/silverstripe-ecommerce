<div id="Checkout">
	<h3 class="process"><span><% _t("PROCESS","Process") %>:</span> &nbsp;<a href="checkout/" title="<% _t("BACKTOCHECKOUT","Click here to go back to the Checkout") %>"><% _t("CHECKOUT","Checkout") %></a> &nbsp;&gt;&nbsp; <span class="current"><% _t("ORDERSTATUS","Order Status") %></span></h3>
	
	<div class="typography">
		<h2><% _t("SUCCESSFULl","Order Successful") %></h2>
		<p><strong><% _t("EMAILDETAILS","A copy of this has been sent to your email address confirming the order details.") %></strong></p>
		$PurchaseComplete
	</div>
	
	<% control DisplayOrder %>
		<% include OrderInformation %>
	<% end_control %>
			
	<% if OrderSummary %>
		<h2>$OrderSummary</h2>
	<% end_if %>
</div>
