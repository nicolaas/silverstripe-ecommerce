<table id="InformationTable" cellspacing="0" cellpadding="0" summary="The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.">
	<thead>
		<tr class="gap">
			<th colspan="4" scope="row" class="corner">Payment Information</th>
		</tr>
	</thead>
	<tbody>
			<tr class="summary">
				<td colspan="3" scope="row" class="right">Payment ID</td>
				<td class="price">#$ID</td>
			</tr>

			<tr class="summary">
				<td colspan="3" scope="row" class="right">Date</td>
				<td class="price">$LastEdited.Time $LastEdited.Nice</td>
			</tr>

			<tr class="summary">
				<td colspan="3" scope="row" class="right">Amount</td>
				<td class="price">$Amount.Nice $Currency</td>
			</tr>
			
			<tr class="summary">
				<td colspan="3" scope="row" class="right">Payment Status</td>
				<td class="price">$Status</td>
			</tr>
			
			<tr class="summary">
				<td colspan="3" scope="row" class="right">Method</td>
				<td class="price">$PaymentMethod</td>
			</tr>
			<% if Message %>
			<tr class="summary">
				<td colspan="3" scope="row" class="right">Details</td>
				<td class="price">$Message</td>
			</tr>
			<% end_if %>
		
	</tbody>
</table>
