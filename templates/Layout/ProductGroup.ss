<% include ProductMenu %>

<div id="ProductGroup">
	<div id="Breadcrumbs" class="typography">
   		<p>$Breadcrumbs</p>
	</div>
	
	<h2 class="pageTitle">$Title</h2>
	
	<% if Content %>
		<div class="typography">
			$Content
		</div>
	<% end_if %>
	
	<% if FeaturedProducts %>
		<h3 class="categoryTitle"><% _t("FEATURED","Featured Products") %><img src="ecommerce/images/products/hide.gif" alt="Hide" name="FeaturedProducts" class="hide"/><img src="ecommerce/images/products/show.gif" alt="Show" name="FeaturedProducts" class="show"/></h3>
		<div id="FeaturedProducts" class="category">
			<div class="resultsBar typography">
				<select class="productsDropdown" disabled="disabled">
					<option value="$FeaturedProducts.Count" selected="selected">$FeaturedProducts.Count</option>
				</select>
				<p class="productsDropdown">Products Per Page</p>
				<p class="resultsShowing">Showing <span class="firstProductIndex">1</span> to <span class="lastProductIndex">$FeaturedProducts.Count</span> of <span class="productsTotal">$FeaturedProducts.Count</span> products</p>
			</div>
			<div class="clear"><!-- --></div>
			<ul class="productList">
				<% control FeaturedProducts %>
					<% include ProductGroupItem %>
				<% end_control %>
			</ul>
			<div class="clear"><!-- --></div>
		</div>
	<% end_if %>
	<% if OtherProducts %>
		<h3 class="categoryTitle"><% _t("OTHER","Other Products") %><img src="ecommerce/images/products/hide.gif" alt="Hide" name="OtherProducts" class="hide"/><img src="ecommerce/images/products/show.gif" alt="Show" name="OtherProducts" class="show"/></h3>
		<div id="OtherProducts" class="category">
			<div class="resultsBar typography">
				<select class="productsDropdown" disabled="disabled">
					<option value="$OtherProducts.Count" selected="selected">$OtherProducts.Count</option>
				</select>
				<p class="productsDropdown">Products Per Page</p>
				<p class="resultsShowing">Showing <span class="firstProductIndex">1</span> to <span class="lastProductIndex">$OtherProducts.Count</span> of <span class="productsTotal">$OtherProducts.Count</span> products</p>
			</div>
			<div class="clear"><!-- --></div>
			<ul class="productList">
				<% control OtherProducts %>
					<% include ProductGroupItem %>
				<% end_control %>
			</ul>
			<div class="clear"><!-- --></div>
		</div>
	<% end_if %>
</div>
