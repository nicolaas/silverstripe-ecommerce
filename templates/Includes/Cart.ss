	<% control Cart %>
		<div id="ShoppingCart">
			<h3><% _t("HEADLINE","My Cart") %></h3>
			<% if Items %>
				<ul>
					<% control Items %>
						<li>
							<a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>">$Title</a> <a href="{$Link}removeall" title="<% sprintf(_t("Remove","Remove &quot;%s&quot; from your cart"),$Title) %>"><img src="ecommerce/images/minus.gif" alt="<% _t("RemoveAlt","Remove") %>" /> </a>
							<span class="price"><% _t("PRICE","Price") %>: <strong>$Price.Nice</strong> (x <strong id="Cart_Item{$ProductID}_Quantity">$Quantity</strong>)</span>
						</li>
					<% end_control %>
					<li class="subtotal"><% _t("SUBTOTAL","Subtotal") %>: <strong id="Cart_Subtotal">$Subtotal.Nice</strong></li>
					
					<% control Modifiers %>
						<% if ShowInCart %>
							<li>$TitleForCart: <strong id="$ValueIdForCart">$ValueForCart</strong></li>
						<% end_if %>
					<% end_control %>
					
					<li class="total"><% _t("TOTAL","Total") %>: <strong id="Cart_GrandTotal">$Total.Nice $Currency</strong></li>
					<li class="buyProducts"><p><a class="checkoutButton" href="checkout" title="<% _t("CheckoutClick","Click here to go to the checkout") %>"><% _t("CheckoutGoTo","Go to checkout") %> &gt;&gt;</a></p></li>
				</ul>
			<% else %> 
				<p class="noItems"><% _t("NOITEMS","There are no items in your cart") %>.</p>
			<% end_if %>
		</div>
	<% end_control %>

