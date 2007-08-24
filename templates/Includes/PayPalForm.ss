<form target="paypal" id="ProductPurchase" action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<!-- a buy now button is represented by the command _xclick -->
	<input type="hidden" name="cmd" value="_cart" />
	<input type="hidden" name="business" value="carrieandpaul@clear.net.nz" />
	<input type="hidden" name="return" value="$SuccessfulPaymentLink" />
	<input type="hidden" name="upload" value="1" />
	<input type="hidden" name="currency_code" value="$Currency" />
	<input type="hidden" name="rm" value="2" />
	<input type="hidden" name="invoice" value="$ID" />
	<input type="hidden" name="image_url" value="$Logo" />
	<input type="hidden" name="tax_cart" value="$AddedTax" />
	<!-- add the item to the PayPal-hosted shopping cart -->
	<% control ContinueCountItems %>
	<input type="hidden" name="item_number_$CountID" value="$ID" />
	<input type="hidden" name="item_name_$CountID" value="$Title" />
	<input type="hidden" name="quantity_$CountID"  value="$Quantity" />
	<input type="hidden" name="amount_$CountID" value="$Price" />
	<input type="hidden" name="on0_$CountID" value="Description" />
	<input type="hidden" name="image_url_$CountID" value="$ThumbnailLink" />
	<input type="hidden" name="os0_$CountID" value="$PlainContent" />
	<% end_control %>
	<input type="image" src="http://www.paypal.com/en_US/i/btn/x-click-but01.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
</form>