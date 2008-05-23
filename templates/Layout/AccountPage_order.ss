<div id="Account">
	<div class="typography">
	<% if Order %>
		<% control Order %>
			<h2>$Status</h2>
			<p><strong>
				<% if IsComplete %>
					<% _t("EMAILDETAILS","A copy of this has been sent to your email address confirming the order details.") %>
				<% else %>
					Your order details have been saved, however you need to <a href="$CheckoutLink" title="<% _t("BACKTOCHECKOUT","Click here to go to the checkout page") %>">complete your order</a>.
				<% end_if %>
			</strong></p>
			<% include OrderInformation %>
		<% end_control %>
	<% else %>
		<p><strong>$Message</strong></p>
	<% end_if %>
	</div>
</div>