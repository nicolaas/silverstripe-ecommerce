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
				<tr id="$IDForTable" class="$ClassForTable">
					<td class="product title" scope="row">
						<% if Link %>
							<a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>">$Title</a>
						<% else %>
							$Title
						<% end_if %>
					</td>
					<td class="center quantity">
						<strong>
							<a class="ajaxQuantityLink" href="$removeLink" title="<% sprintf(_t("REMOVEONE","Remove one of &quot;%s&quot; from your cart"),$Title) %>">
								<img src="ecommerce/images/minus.gif" alt="-"/>
							</a>
						</strong> 
						<% if AjaxQuantityField %>
							$AjaxQuantityField
						<% else %>
							$Quantity
						<% end_if %>
						<strong>
							<a class="ajaxQuantityLink" href="$addLink" title="<% sprintf(_t("ADDONE","Add one more of &quot;%s&quot; to your cart"),$Title) %>">
								<img src="ecommerce/images/plus.gif" alt="+"/>
							</a>
						</strong>
					</td>
					<td class="right unitprice">$UnitPrice.Nice</td>
					<td class="right total" id="$TotalIDForTable">$Total.Nice</td>
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
			<td class="right" id="$SubTotalIDForTable">$SubTotal.Nice</td>
		</tr>
		
		<% control Modifiers %>
			<% if ShowInOrderTable %>
				<tr id="$IDForTable" class="$ClassForTable">
					<td colspan="2" scope="row" id="$TitleIdForTable">$TitleForTable</td>
					<td>&nbsp;</td>
					<td class="right" id="$ValueIdForTable">$ValueForTable</td>
				</tr>
			<% end_if %>
		<% end_control %>
		
		<tr class="gap Total">
			<td colspan="2" scope="row"><% _t("TOTAL","Total") %></td>
			<td>&nbsp;</td>
			<td class="right" id="$TotalIDForTable">$Total.Nice $Currency</td>
		</tr>
	</tbody>
</table>