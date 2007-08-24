<table id="InformationTable" cellspacing="0" cellpadding="0" summary="The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.">
	<thead>
		<tr>
			<th scope="col">Product</th>
			<th scope="col" class="center">Quantity</th>
			<th scope="col" class="right">Price ($Currency)</th>
			<th scope="col" class="right corner">Total Price ($Currency)</th>
		</tr>
	</thead>
	<tbody>
		<% control Items %>
			<tr>
				<td class="product" scope="row">$Title</td>
				<td class="center">$Quantity</td>
				<td class="right">$Price.Nice</td>
				<td class="right">$SubTotal.Nice</td>
			</tr>
		<% end_control %>			
		<tr class="gap summary">
			<td colspan="3" scope="row">Sub-total</td>
			<td class="right">$Subtotal.Nice</td>
		</tr>

		<% if Shipping %>
			<tr class="summary">
				<td colspan="3" scope="row">Shipping<% if findShippingCountry %> to $findShippingCountry<% end_if %></td>
				<td class="right">$Shipping.Nice</td>
			</tr>
		<% end_if %>

		<% if TaxInfo.LineItemTitle %>
		<tr id="GST" class="summary">
			<td colspan="3" scope="row">$TaxInfo.LineItemTitle</td>
			<td class="right">$TaxInfo.Charge.Nice</td>
		</tr>
		<% end_if %>

		<tr class="gap Total">
			<td colspan="3" scope="row">Total</td>
			<td class="right">$Total.Nice</td>
		</tr>
	
	<% control OrderPayment %>
		<tr class="gap">
			<td colspan="4" scope="row" class="left ordersummary"><h3>Order Summary:</h3></td>
		</tr>
		<tr class="gap">
			<th colspan="4" scope="row" class="left">Payment Information</th>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Payment ID</td>
			<td class="price">#$ID</td>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Date</td>
			<td class="price">$LastEdited.Nice</td>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Amount</td>
			<td class="price">$Amount.Nice $Currency</td>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Payment Status</td>
			<td class="price">$Status</td>
		</tr>
		<% if PaymentMethod %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left">Method</td>
				<td class="price">$PaymentMethod</td>
			</tr>
		<% end_if %>
		<% if Message %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left">Details</td>
				<td class="price">$Message</td>
			</tr>
		<% end_if %>
	<% end_control %>
	<tr class="gap Total">
		<td colspan="3" scope="row" class="left"><strong>Total outstanding</strong></td>
		<td class="price"><strong>$TotalOutstanding.Nice </strong></td>
	</tr>
	
	<% control OrderCustomer %>
		<tr class="gap">
			<th colspan="4" scope="row" class="left">Customer Details</th>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left">Name</td>
			<td class="price">$FirstName $Surname</td>
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

	<% if UseShippingAddress %>
		<tr class="gap shippingDetails">
			<th colspan="4" scope="row" class="left">Shipping Details</th>
		</tr>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left">Name</td>
			<td class="price">$ShippingName</td>
		</tr>
		<% if ShippingAddress %>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left">Address</td>
			<td class="price">$ShippingAddress</td>
		</tr>
		<% end_if %>
		<% if ShippingAddress2 %>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left"></td>
			<td colspan="3" class="price">$ShippingAddress2</td>
		</tr>
		<% end_if %>
		<% if ShippingCity %>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left">City</td>
			<td class="price">$ShippingCity</td>
		</tr>
		<% end_if %>
		<% if ShippingCountry %>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left">Country</td>
			<td class="price">$ShippingCountry</td>
		</tr>
		<% end_if %>
	<% end_if %>
	
	</tbody>
</table>