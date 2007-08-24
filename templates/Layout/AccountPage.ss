<div id="Account">
	<% if CurrentMember %>
		<div class="typography">
			<h2>$Title</h2>
			
			<% if Content %>
				$Content
			<% end_if %>
		</div>
		<ul id="PastOrders">
			<li><h3>Your Order History</h3></li>
			<li><h4>Completed Orders</h4></li>
			<% if CompleteOrders %>
				<% control CompleteOrders %>
					<li><a href="checkout/OrderSuccessful/$ID" title="Read more on Order #{$ID}">Order #{$ID}</a> ($Created.Nice)</li>
				<% end_control %>
			<% else %>
				<li>No completed orders were found.</li>
			<% end_if %>
			<li><h4>Incomplete Orders</h4></li>
			<% if IncompleteOrders %>
				<% control IncompleteOrders %>
					<li><a href="checkout/OrderIncomplete/$ID" title="Read more on Order #{$ID}">Order #{$ID}</a> ($Created.Nice)</li>
				<% end_control %>
			<% else %>
				<li>No incomplete orders were found.</li>
			<% end_if %>
		</ul>
		
		$MemberForm
	<% else %>
		<div class="typography">
			<p class="message good">Please enter your details to login to the account page.<br />This page is only accessible after your first order, when you are assigned a password.</p>
		</div>
		
		$LoginForm
	<% end_if %>
</div>