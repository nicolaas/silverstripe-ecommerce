<li class="productItem">
	<% if Image %>
		<a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>"><img src="$Image.Thumbnail.URL" alt="<% sprintf(_t("IMAGE","%s image"),$Title) %>" /></a>
	<% else %>
		<a href="$Link" title="<% sprintf(_t("READMORE"),$Title) %>"><img src="ecommerce/images/productPlaceHolderThumbnail.gif" alt="<% sprintf(_t("NOIMAGE","Sorry, no product image for &quot;%s&quot;"),$Title) %>" /></a>
	<% end_if %>

		<h3 class="productTitle"><a href="$Link" title="<% sprintf(_t("READMORE"),$Title) %>">$Title</a></h3>
		<% if Model %><p><strong><% _t("AUTHOR","Author") %>:</strong> $Model.XML</p><% end_if %>
		<p>$Content.LimitWordCountPlainText(15) <a href="$Link" title="<% sprintf(_t("READMORE"),$Title) %>"><% _t("READMORECONTENT","Click to read more &raquo;") %></a></p>
		<p>
			<% if Price %><span class="price_display">$Price.Nice $Currency $TaxInfo.PriceSuffix</span><% end_if %>
			<% if AllowPurchase %>
				<% if IsInCart %>
					<div class="quantityBox">
						<span><% _t("QUANTITY","Quantity") %>:</span>
						<a class="ajaxQuantityLink" href="{$Link}remove/" title="<% sprintf(_t("REMOVEONE","Remove one of &quot;%s&quot; from your cart"),$Title) %>">
							<img src="ecommerce/images/minus.gif" alt="-" />
						</a>	
						$AjaxQuantityField
						<a class="ajaxQuantityLink" href="{$Link}add/" title="<% sprintf(_t("ADDONE","Add one more of &quot;%s&quot; to your cart"),$Title) %>">
							<img src="ecommerce/images/plus.gif" alt="+" />
						</a>
						<ul class="productActions">
							<li><a href="{$Link}removeall" title="<% sprintf(_t("REMOVE","Remove &quot;%s&quot; from your cart"),$Title) %>"><% _t("REMOVELINK","&raquo; Remove from cart") %></a></li>
						</ul>
					</div>
				<% else %>
					<p class="quantityBox"><a href="{$Link}add" title="<% sprintf(_t("ADD","Add &quot;%s&quot; to your cart"),$Title) %>"><% _t("ADDLINK","Add this item to cart") %></a></p>
				<% end_if %>
			<% end_if %>
		</p>
</li>																			
