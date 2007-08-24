<li class="productItem">
	<% if Image %>
		<a href="$Link" title="Click here to read more on &quot;{$Title}&quot;"><img src="$Image.Thumbnail.URL" alt="$Title image" /></a>
	<% else %>
		<a href="$Link" title="Click here to read more on &quot;{$Title}&quot;"><img src="ecommerce/images/productPlaceHolderThumbnail.gif" alt="Sorry, no product image for &quot;{$Title}&quot;" /></a>
	<% end_if %>

		<h3 class="productTitle"><a href="$Link" title="Click here to read more on &quot;{$Title}&quot;">$Title</a></h3>
		<% if Model %><p><strong>Author:</strong> $Model.XML</p><% end_if %>
		<p>$Content.LimitWordCountPlainText(15) <a href="$Link" title="Click here to read more on &quot;{$Title}&quot;">Click to read more &raquo;</a></p>
		<p>
			<% if Price %><span class="price_display">$Price.Nice $Currency $TaxInfo.PriceSuffix</span><% end_if %>
			<% if AllowPurchase %>
				<% if IsInCart %>
					<div class="quantityBox">
						<span>Quantity:</span>
						<a class="ajaxQuantityLink" href="{$Link}remove/" title="Remove one of &quot;{$Title}&quot; from your cart">
							<img src="ecommerce/images/minus.gif" alt="-" />
						</a>	
						$AjaxQuantityField
						<a class="ajaxQuantityLink" href="{$Link}add/" title="Add one more of &quot;{$Title}&quot; to your cart">
							<img src="ecommerce/images/plus.gif" alt="+" />
						</a>
						<ul class="productActions">
							<li><a href="{$Link}removeall" title="Remove &quot;{$Title}&quot; from your cart">&raquo; Remove from cart</a></li>
						</ul>
					</div>
				<% else %>
					<p class="quantityBox"><a href="{$Link}add" title="Add &quot;{$Title}&quot; to your cart">Add this item to cart</a></p>
				<% end_if %>
			<% end_if %>
		</p>
</li>																			
