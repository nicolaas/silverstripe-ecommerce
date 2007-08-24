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
			<img class="productImage" src="$Image.ContentImage.URL" alt="$Title image" />
		<% else %>
			<img src="ecommerce/images/productPlaceHolderNormal.gif" alt="Sorry, no product image for &quot;{$Title}&quot;" />
		<% end_if %>
		<% if FeaturedProduct %>
			<p class="featured">This is a featured product.</p>
		<% end_if %>
		<p>Item #{$ID}</p>
		<% if Model %><p>Author: $Model.XML</p><% end_if %>
		<% if Size %><p>Size: $Size.XML</p><% end_if %>
		<% if Price %><p class="price_display">$Price.Nice $Currency $TaxInfo.PriceSuffix</p><% end_if %>
		<% if AllowPurchase %>
			<% if IsInCart %>
				<div class="quantityBox">
					<span>Quantity in cart:</span>
					<a class="ajaxQuantityLink" href="{$Link}remove/" title="Remove one of &quot;{$Title}&quot; from your cart">
						<img src="ecommerce/images/minus.gif" alt="-" />
					</a>	
					$AjaxQuantityField
					<a class="ajaxQuantityLink" href="{$Link}add/" title="Add one more of &quot;{$Title}&quot; to your cart">
						<img src="ecommerce/images/plus.gif" alt="+" />
					</a>
					<ul class="productActions">
						<li><a href="{$Link}removeall" title="Remove &quot;{$Title}&quot; from your cart">&raquo; Remove this item from cart</a></li>
						<li><a href="$CheckoutLink" title="Go to the checkout now">&raquo; Go to the checkout</a></li>
					</ul>
				</div>
			<% else %>
				<p class="quantityBox"><a href="{$Link}add" title="Add &quot;{$Title}&quot; to your cart">Add this item to cart</a></p>
			<% end_if %>
		<% end_if %>
	</div>
	<% if Content %>
		<div class="productContent typography">
			$Content
		</div>
	<% end_if %>
</div>
