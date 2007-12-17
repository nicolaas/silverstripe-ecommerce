<h3 class="orderInfo"><% _t("ORDERINFORMATION","Order Information") %></h3>
<table id="InformationTable" class="editable" cellspacing="0" cellpadding="0" summary="<% _t("TABLESUMMARY","The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.") %>">
	<thead>
		<tr>
			<th scope="col" class="left"><% _t("PRODUCT","Product") %></th>
			<th scope="col" class="center"><% _t("QUANTITY", "Quantity") %></th>
			<th scope="col" class="right"><% _t("PRICE","Price") %> ($Currency)</th>
			<th scope="col" class="right"><% _t("TOTALPRICE","Total Price") %> ($Currency)</th>
		</tr>
	</thead>
	<tbody>
		<% if Items %>
			<% control Items %>
				<tr>
					<td class="product" scope="row"><a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>">$Title</a></td>
					<td class="center">
						<strong><a class="ajaxQuantityLink" href="{$Link}remove/" title="<% sprintf(_t("REMOVEONE","Remove one of &quot;%s&quot; from your cart"),$Title) %>">
							<img src="ecommerce/images/minus.gif" alt="-" /></a></strong> 
							
							$AjaxQuantityField
						
						<strong><a class="ajaxQuantityLink" href="{$Link}add/" title="<% sprintf(_t("ADDONE","Add one more of &quot;%s&quot; to your cart"),$Title) %>">
							<img src="ecommerce/images/plus.gif" alt="+" /></a></strong>
					</td>
					<td class="right">$Price.Nice</td>
					<td class="right" id="Item{$ProductID}_Subtotal">$SubTotal.Nice</td>
				</tr>
			<% end_control %>
		<% else %>
			<tr>
				<td colspan="4" scope="row" class="center"><% _t("NOITEMS","There are <strong>no</strong> items in your cart.") %></td>
			</tr>
		<% end_if %>
						
		<tr class="gap summary">
			<td colspan="2" scope="row"><% _t("SUBTOTAL","Sub-total") %></td>
			<td>&nbsp;</td>
			<td class="right" id="Subtotal">$Subtotal.Nice</td>
		</tr>

		<% if Shipping %>
			<tr class="summary">
				<td colspan="2" scope="row"><% _t("SHIPPING","Shipping") %><% if findShippingCountry %> <% _t("SHIPPINGTO","to") %> $findShippingCountry<% end_if %></td>
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
			<td colspan="2" scope="row"><% _t("TOTAL","Total") %></td>
			<td>&nbsp;</td>
			<td class="right" id="GrandTotal">$Total.Nice $Currency</td>
		</tr>
	</tbody>
</table>
