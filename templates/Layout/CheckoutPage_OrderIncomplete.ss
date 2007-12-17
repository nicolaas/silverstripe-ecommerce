<div id="Checkout">
	<h3 class="process"><span><% _t("PROCESS","Process") %>:</span> &nbsp;<a href="checkout/" title="<% _t("BACKTOCHECKOUT","Click here to go back to the Checkout") %>"><% _t("CHECKOUT","Checkout") %></a> &nbsp;&gt;&nbsp; <span class="current"><% _t("ORDERSTATUS","Order Status") %></span></h3>
	<div class="typography">
		<h2><% _t("INCOMPLETE","Order Incomplete") %></h2>
		<p><% _t("CHEQUEINSTRUCTIONS","If you ordered by cheque you will receive an email with instructions.") %></p>
		<p><% _t("DETAILSSUBMITTED","Here are the details you submitted") %>:</p>
	</div>

	<% control DisplayOrder %>
		<% include OrderInformation %>
	<% end_control %>
			
	<% if OrderSummary %><h2>$OrderSummary</h2><% end_if %>
</div>
