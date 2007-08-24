<% include ProductMenu %>

<div id="ProductGroup">

	<div id="Breadcrumbs" class="typography">
   	<p>$Breadcrumbs</p>
	</div>

	<h2 class="pageTitle">
		$Title
	</h2>	

	<% if Content %>
		<div class="typography">
			$Content
		</div>
	<% end_if %>
	
	<% if IsTopLevel %>
		<% if ChildProducts %>
			<div class="product_summary">
				<ul id="ProductList">
					<% control ChildProducts %>
						<% include ProductGroupItem %>
					<% end_control %>
				</ul>
			</div>
		<% end_if %>
		<% if FeaturedProducts %>
			<div class="product_summary">
				<h3 class="productGroupTitle">Featured Products</h3>
				<ul id="ProductList">
					<% control FeaturedProducts %>
						<% include ProductGroupItem %>
					<% end_control %>
				</ul>
			</div>
		<% end_if %>
	<% else %>
		<% if ChildGroups %>
			<div class="product_summary">
				<% if ChildProducts %>
					<ul id="ProductList">
						<% control ChildProducts %>
							<% include ProductGroupItem %>
						<% end_control %>
					</ul>
				<% end_if %>
				<% control ChildGroups %>
					<% if ChildGroups %>
						<h3 class="productGroupTitle"><a href="$Link" title="View the product group &quot;{$Title}&quot;">$Title</a></h3>
						<ul id="ProductList">
							<% control ChildGroups %>
								<% if ChildProducts %>
								<% end_if %>
							<% end_control %>
						</ul>
					<% end_if %>
				<% end_control %>
			</div>
		<% else %>
			<div class="product_summary">
				<% if ChildProducts %>
					<ul id="ProductList">
						<% control ChildProducts %>
							<% include ProductGroupItem %>
						<% end_control %>
					</ul>
				<% end_if %>
			</div>
		<% end_if %>
	<% end_if %>
</div>