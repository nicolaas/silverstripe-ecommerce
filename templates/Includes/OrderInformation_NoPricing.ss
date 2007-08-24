<table id="InformationTable" cellspacing="0" cellpadding="0" summary="The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.">
	
	<thead>
		<tr class="gap">
			<td colspan="4" scope="row" class="left ordersummary"><h3>Information for Order #{$ID}:</h3></td>
		</tr>
	</thead>
	
	<tbody>
	<% control OrderCustomer %>
		<tr class="gap">
			<th colspan="4" scope="row" class="left">Customer Details</th>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Name</td>
			<td class="price">$CreditCardName</td>
		</tr>
		<% if HomePhone %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left">Phone</td>
				<td class="price">$HomePhone</td>
			</tr>
		<% end_if %>
		<% if MobilePhone %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left">Mobile</td>
				<td class="price">$MobilePhone</td>
			</tr>
		<% end_if %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Email</td>
			<td class="price">$Email</td>
		</tr>				
		<tr class="gap">
			<th colspan="4" scope="row" class="left">Address</th>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Buyer's Address</td>
			<td class="price">$Address</td>
		</tr>
		<% if AddressLine2 %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left"></td>
				<td class="price">$AddressLine2</td>
			</tr>
		<% end_if %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">City</td>
			<td class="price">$City</td>
		</tr>
		<% if Country %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Country</td>
			<td class="price">$Country</td>
		</tr>
		<% end_if %>
	<% end_control %>

	<% if ShippingName %>
		<tr class="gap">
			<th colspan="4" scope="row" class="left">Shipping Details</th>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Name</td>
			<td class="price">$ShippingName</td>
		</tr>
		<% if ShippingAddress %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Address</td>
			<td class="price">$ShippingAddress</td>
		</tr>
		<% end_if %>
		<% if ShippingAddress2 %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"></td>
			<td colspan="3" class="price">$ShippingAddress2</td>
		</tr>
		<% end_if %>
		<% if ShippingCity %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">City</td>
			<td class="price">$ShippingCity</td>
		</tr>
		<% end_if %>
		<% if ShippingCountry %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Country</td>
			<td class="price">$ShippingCountry</td>
		</tr>
		<% end_if %>
	<% end_if %>
	
	</tbody>
</table>