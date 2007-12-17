<% include ProductMenu %>

<div id="Product">
	
	<div id="Breadcrumbs" class="typography">
   	<p>$Breadcrumbs</p>
	</div>
	
	<h2 class="pageTitle">
		$Title
	</h2>
	
	<div class="product_details">
		<% if Image.ContentImage %>
			<img class="productImage" src="$Image.ContentImage.URL" alt="<% sprintf(_t("IMAGE","%s image"),$Title) %>" />
		<% else %>
			<img src="ecommerce/images/productPlaceHolderNormal.gif" alt="<% sprintf(_t("NOIMAGE","Sorry, no product image for &quot;%s&quot;"),$Title) %>" />
		<% end_if %>
		<% if FeaturedProduct %>
			<p class="featured"><% _t("FEATURED","This is a featured product.") %></p>
		<% end_if %>
		<p><% _t("ItemID","Item #") %>{$ID}</p>
		<% if Model %><p><% _t("AUTHOR","Author") %>: $Model.XML</p><% end_if %>
		<% if Size %><p><% _t("SIZE","Size") %>: $Size.XML</p><% end_if %>
		<% if Price %><p class="price_display">$Price.Nice $Currency $TaxInfo.PriceSuffix</p><% end_if %>
		<% if AllowPurchase %>
			<% if IsInCart %>
				<div class="quantityBox">
					<span><% _t("QUANTITYCART","Quantity in cart") %>:</span>
					<a class="ajaxQuantityLink" href="{$Link}remove/" title="<% sprintf(_t("REMOVEALL","Remove one of &quot;%s&quot; from your cart"),$Title) %>">
						<img src="ecommerce/images/minus.gif" alt="-" />
					</a>	
					$AjaxQuantityField
					<a class="ajaxQuantityLink" href="{$Link}add/" title="<% sprintf(_t("ADDONE","Add one more of &quot;%s&quot; to your cart"),$Title) %>">
						<img src="ecommerce/images/plus.gif" alt="+" />
					</a>
					<ul class="productActions">
						<li><a href="{$Link}removeall" title="<% sprintf(_t("REMOVE","Remove &quot;%s&quot; from your cart"),$Title) %>"><% _t("REMOVELINK","&raquo; Remove from cart") %></a></li>
						<li><a href="$CheckoutLink" title="<% _t("GOTOCHECKOUT","Go to the checkout now") %>"><% _t("GOTOCHECKOUTLINK","&raquo; Go to the checkout") %></a></li>
					</ul>
				</div>
			<% else %>
				<p class="quantityBox"><a href="{$Link}add" title="<% sprintf(_t("ADD","Add &quot;%s&quot; to your cart"),$Title) %>"><% _t("ADDLINK","Add this item to cart") %></a></p>
			<% end_if %>
		<% end_if %>
	</div>
	<% if Content %>
		<div class="productContent typography">
			$Content
		</div>
	<% end_if %>
</div>
