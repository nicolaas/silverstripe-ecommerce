<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
	<head>
		<% base_tag %>
		
		<style type="text/css">
			html,body{
				text-align:left;
				font-family:Arial,Helvetica,sans-serif;
				border:0;
				padding:0;
				margin:0;
			}
			#CustomContent{
				background:#fff;
			}
			img {
				float:right;
				border:2px solid #eee;
				margin-right:10px;
			}
			h1{
				color:#16719D;
				line-height:1.4em !important;
				font-size:1.5em !important;
				font-weight:normal !important;
				width:95%;
			}
			.product{
				margin-bottom:20px;
				padding:10px;
				clear:left !important;
			}
		</style>
		<title>View All Products</title>
	</head>
	<body>
	
		<div id="CustomContent">
			<% control AllProducts %>
				<div class="product">
					<h1>$Title - <a href="$Link" title="$Title" >$ID</a></h1>
					<div class="typography">
						<% if Image %>
							<img src="$Image.ContentImage.URL" alt="<% sprintf(_t("IMAGE","%s image"),$Title) %>" />
						<% else %>
							<img src="ecommerce/images/productPlaceHolderNormal.gif" alt="<% sprintf(_t("NOIMAGE","Sorry, no image for &quot;%s&quot;"),$Title)%>" />
						<% end_if %>
						<% if Content %>$Content<% else %><p><% _t("NOCONTENT","No content set.") %></p><% end_if %>
						<p><strong><% _t("PRODUCTID","Product ID") %>:</strong> #{$ID}</p>
						<p><strong><% _t("LINK","Link") %>:</strong> <a href="$Link" title="View $Title">$Link</a></p>
						<% if Model %><p><strong><% _t("AUTHOR","Author") %>:</strong> $Model.XML</p><% end_if %>
						<p><strong><% _t("LASTEDIT","Last edited") %>:</strong> $LastEdited.Nice</p>
						<p><strong><% _t("PRICE","Price") %>:</strong> $Price.Nice $Currency</p>
						<p><strong><% _t("WEIGHT","Weight") %>:</strong> {$Weight}kg</p>
						<p><strong><% _t("CATEGORIES","Categories") %></strong></p>
						<% if Parents %>
							<ul>
								<% control Parents %>
									<li><% control Parent %><% control Parent %>$Title - <% end_control %>$Title -<% end_control %>$Parent.Title - <a href="$Link" title="$Title" >$Title</a></li>
								<% end_control %>
							</ul>
						<% else %>
							<blockquote style="color:red;"><% _t("NOSUBJECTS","No Subjects Set") %></blockquote>
					<% end_if %>
					</div>
				</div>
				<hr>
		<% end_control %>
		</div>
	</body>
</html>

