<h3 class="orderInfo">Order Information</h3>
<table id="InformationTable" class="editable" cellspacing="0" cellpadding="0" summary="The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.">
	<thead>
		<tr>
			<th scope="col" class="left">Product</th>
			<th scope="col" class="center">Quantity</th>
			<th scope="col" class="right">Price ($Currency)</th>
			<th scope="col" class="right">Sub-total ($Currency)</th>
		</tr>
	</thead>
	<tbody>
		<% if Items %>
			<% control Items %>
				<tr>
					<td class="product" scope="row"><a href="$Link" title="Click here to read more on &quot;{$Title}&quot;">$Title</a></td>
					<td class="center">
						<strong><a class="ajaxQuantityLink" href="{$Link}remove/" title="Remove one of &quot;{$Title}&quot; from your cart">
							<img src="ecommerce/images/minus.gif" alt="-" /></a></strong> 
							
							$AjaxQuantityField
						
						<strong><a class="ajaxQuantityLink" href="{$Link}add/" title="Add one more of &quot;{$Title}&quot; to your cart">
							<img src="ecommerce/images/plus.gif" alt="+" /></a></strong>
					</td>
					<td class="right">$Price.Nice</td>
					<td class="right" id="Item{$ProductID}_Subtotal">$SubTotal.Nice</td>
				</tr>
			<% end_control %>
		<% else %>
			<tr>
				<td colspan="4" scope="row" class="center">There are <strong>no</strong> items in your cart.</td>
			</tr>
		<% end_if %>
						
		<tr class="gap summary">
			<td colspan="2" scope="row">Sub-total</td>
			<td>&nbsp;</td>
			<td class="right" id="Subtotal">$Subtotal.Nice</td>
		</tr>

		<% if Shipping %>
			<tr class="summary">
				<td colspan="2" scope="row">Shipping<% if findShippingCountry %> to $findShippingCountry<% end_if %></td>
				<td>&nbsp;</td>
				<td class="right" id="ShippingCost">$Shipping.Nice</td>
			</tr>
		<% end_if %>

		<% if TaxInfo.LineItemTitle %>
		<tr id="GST" class="summary">
			<td colspan="3" scope="row">$TaxInfo.LineItemTitle</td>
			<td class="right" id="TaxCost">$TaxInfo.Charge.Nice</td>
		</tr>
		<% end_if %>
		
		<tr class="gap Total">
			<td colspan="2" scope="row">Total</td>
			<td>&nbsp;</td>
			<td class="right" id="GrandTotal">$Total.Nice $Currency</td>
		</tr>
	</tbody>
</table>
