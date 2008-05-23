<div id="Account">
	<% control Order %>
		<div class="typography">
			<h2>$Status</h2>
			<p><strong>
				<% if IsComplete %>
					<% _t("EMAILDETAILS","A copy of this has been sent to your email address confirming the order details.") %>
				<% else %>
					Your order details have been saved, however you need to <a href="$CheckoutLink{$ID}" title="<% _t("BACKTOCHECKOUT","Click here to go back to the Checkout") %>">complete your order</a>.
				<% end_if %>
			</strong></p>
		</div>
		<% include OrderInformation %>
	<% end_control %>
</div>