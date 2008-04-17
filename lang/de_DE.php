<?php

/**
 * German (Germany) language pack
 * @package modules: ecommerce
 * @subpackage i18n
 */

i18n::include_locale_file('modules: ecommerce', 'en_US');

global $lang;

if(array_key_exists('de_DE', $lang) && is_array($lang['de_DE'])) {
	$lang['de_DE'] = array_merge($lang['en_US'], $lang['de_DE']);
} else {
	$lang['de_DE'] = $lang['en_US'];
}

$lang['de_DE']['AccountPage.ss']['COMPLETED'] = 'Abgeschlossene Bestellungen';
$lang['de_DE']['AccountPage.ss']['HISTORY'] = 'Ihr Bestellhistorie';
$lang['de_DE']['AccountPage.ss']['INCOMPLETE'] = 'Offene Bestellungen';
$lang['de_DE']['AccountPage.ss']['NOCOMPLETED'] = 'Es konnten keine abgeschlossenen Bestellungen gefunden werden.';
$lang['de_DE']['AccountPage.ss']['NOINCOMPLETE'] = 'Es konnten keine offenen Bestellungen gefunden werden.';
$lang['de_DE']['AccountPage.ss']['ORDER'] = 'Bestellung Nr.';
$lang['de_DE']['Cart.ss']['HEADLINE'] = 'Mein Warenkorb';
$lang['de_DE']['Cart.ss']['NOITEMS'] = 'In Ihrem Warenkorb befinden sich zur Zeit keine Artikel';
$lang['de_DE']['Cart.ss']['PRICE'] = 'Preis';
$lang['de_DE']['Cart.ss']['READMORE'] = 'Erfahren Sie hier mehr über &quot;%s&quot;';
$lang['de_DE']['Cart.ss']['Remove'] = '&quot;%s&quot; aus dem Warenkorb entfernen';
$lang['de_DE']['Cart.ss']['RemoveAlt'] = 'entfernen';
$lang['de_DE']['Cart.ss']['SHIPPING'] = 'Versandkosten';
$lang['de_DE']['Cart.ss']['SUBTOTAL'] = 'Zwischensumme';
$lang['de_DE']['Cart.ss']['TOTAL'] = 'Summe';
$lang['de_DE']['CheckoutPage.ss']['ORDERSTATUS'] = 'Bestellstatus';
$lang['de_DE']['CheckoutPage_OrderIncomplete.ss']['CHEQUEINSTRUCTIONS'] = 'Falls Sie die Bezahlung per Scheck gewählt haben erhalten Sie eine E-Mail mit weiteren Details zur Abwicklung.';
$lang['de_DE']['CheckoutPage_OrderIncomplete.ss']['INCOMPLETE'] = 'Bestellung nicht vollständig';
$lang['de_DE']['CheckoutPage_OrderIncomplete.ss']['ORDERSTATUS'] = 'Bestellstatus';
$lang['de_DE']['CheckoutPage_OrderSuccessful.ss']['EMAILDETAILS'] = 'Zur Bestätigung wurde eine Kopie an Ihre E-Mail Adresse verschickt';
$lang['de_DE']['CheckoutPage_OrderSuccessful.ss']['ORDERSTATUS'] = 'Bestellstatus';
$lang['de_DE']['CheckoutPage_OrderSuccessful.ss']['SUCCESSFULl'] = 'Bestellung erfolgreich durchgeführt';
$lang['de_DE']['ChequePayment']['MESSAGE'] = 'Bezahlung per Scheck (Vorkasse). Bitte beachten: Der Versand erfolgt erst nach Zahlungseingang / -buchung.';
$lang['de_DE']['FindOrderReport']['DATERANGE'] = 'Zeitraum';
$lang['de_DE']['MemberForm']['DETAILSSAVED'] = 'Ihre Daten wurden gespeichert';
$lang['de_DE']['MemberForm']['LOGGEDIN'] = 'Sie sing eingelogt';
$lang['de_DE']['OrderInformation.ss']['ADDRESS'] = 'Adresse';
$lang['de_DE']['OrderInformation.ss']['AMOUNT'] = 'Betrag';
$lang['de_DE']['OrderInformation.ss']['BUYERSADDRESS'] = 'Käuferadresse';
$lang['de_DE']['OrderInformation.ss']['CITY'] = 'Stadt';
$lang['de_DE']['OrderInformation.ss']['COUNTRY'] = 'Land';
$lang['de_DE']['OrderInformation.ss']['CUSTOMERDETAILS'] = 'Kundendetails';
$lang['de_DE']['OrderInformation.ss']['DATE'] = 'Datum';
$lang['de_DE']['OrderInformation.ss']['DETAILS'] = 'Details';
$lang['de_DE']['OrderInformation.ss']['EMAIL'] = 'E-Mail';
$lang['de_DE']['OrderInformation.ss']['MOBILE'] = 'Handy';
$lang['de_DE']['OrderInformation.ss']['NAME'] = 'Name';
$lang['de_DE']['OrderInformation.ss']['ORDERSUMMARY'] = 'Bestellübersicht';
$lang['de_DE']['OrderInformation.ss']['PAYMENTID'] = 'Zahlungs ID';
$lang['de_DE']['OrderInformation.ss']['PAYMENTINFORMATION'] = 'Zahlungsinformationen';
$lang['de_DE']['OrderInformation.ss']['PAYMENTMETHOD'] = 'Methode';
$lang['de_DE']['OrderInformation.ss']['PAYMENTSTATUS'] = 'Bezahlstatus';
$lang['de_DE']['OrderInformation.ss']['PHONE'] = 'Telefon';
$lang['de_DE']['OrderInformation.ss']['PRICE'] = 'Preis';
$lang['de_DE']['OrderInformation.ss']['PRODUCT'] = 'Produkt';
$lang['de_DE']['OrderInformation.ss']['QUANTITY'] = 'Menge';
$lang['de_DE']['OrderInformation.ss']['SHIPPING'] = 'Versandkosten';
$lang['de_DE']['OrderInformation.ss']['SHIPPINGTO'] = 'an';
$lang['de_DE']['OrderInformation.ss']['SUBTOTAL'] = 'Zwischensumme';
$lang['de_DE']['OrderInformation.ss']['TOTALl'] = 'Gesamt';
$lang['de_DE']['OrderInformation.ss']['TOTALOUTSTANDING'] = 'Gesamt ausstehend';
$lang['de_DE']['OrderInformation.ss']['TOTALPRICE'] = 'Gesamtpreis';
$lang['de_DE']['OrderInformation_Editable.ss']['NOITEMS'] = 'Es sind <strong>keine</strong> Artikel in Ihrem Warenkorb';
$lang['de_DE']['OrderInformation_Editable.ss']['ORDERINFORMATION'] = 'Bestellinformationen';
$lang['de_DE']['OrderInformation_Editable.ss']['PRICE'] = 'Preis';
$lang['de_DE']['OrderInformation_Editable.ss']['PRODUCT'] = 'Produkt';
$lang['de_DE']['OrderInformation_Editable.ss']['QUANTITY'] = 'Menge';
$lang['de_DE']['OrderInformation_Editable.ss']['READMORE'] = 'Erfahren Sie hier mehr über &quot;%s&quot;';
$lang['de_DE']['OrderInformation_Editable.ss']['SHIPPING'] = 'Versankosten';
$lang['de_DE']['OrderInformation_Editable.ss']['SUBTOTAL'] = 'Zwischensumme';
$lang['de_DE']['OrderInformation_Editable.ss']['TOTAL'] = 'Gesamt';
$lang['de_DE']['OrderInformation_Editable.ss']['TOTALPRICE'] = 'Gesamtpreis';
$lang['de_DE']['OrderInformation_NoPricing.ss']['ADDRESS'] = 'Adresse';
$lang['de_DE']['OrderInformation_NoPricing.ss']['BUYERSADDRESS'] = 'Käuferadresse';
$lang['de_DE']['OrderInformation_NoPricing.ss']['CITY'] = 'Stadt';
$lang['de_DE']['OrderInformation_NoPricing.ss']['COUNTRY'] = 'Land';
$lang['de_DE']['OrderInformation_NoPricing.ss']['CUSTOMERDETAILS'] = 'Kundendetails';
$lang['de_DE']['OrderInformation_NoPricing.ss']['EMAIL'] = 'E-Mail';
$lang['de_DE']['OrderInformation_NoPricing.ss']['MOBILE'] = 'Handy';
$lang['de_DE']['OrderInformation_NoPricing.ss']['NAME'] = 'Name';
$lang['de_DE']['OrderInformation_NoPricing.ss']['PHONE'] = 'Telefon';
$lang['de_DE']['OrderInformation_PackingSlip.ss']['DESCRIPTION'] = 'Beschreibung';
$lang['de_DE']['OrderInformation_PackingSlip.ss']['ITEM'] = 'Artikel';
$lang['de_DE']['OrderInformation_PackingSlip.ss']['ORDERDATE'] = 'Bestelldatum';
$lang['de_DE']['OrderInformation_PackingSlip.ss']['ORDERNUMBER'] = 'Bestellnummer';
$lang['de_DE']['OrderInformation_PackingSlip.ss']['PAGETITLE'] = 'Shopbestellungen drucken';
$lang['de_DE']['OrderInformation_PackingSlip.ss']['QUANTITY'] = 'Menge';
$lang['de_DE']['OrderInformation_Print.ss']['PAGETITLE'] = 'Bestellungen drucken';
$lang['de_DE']['OrderReport']['CHANGESTATUS'] = 'Lieferstatus ändern';
$lang['de_DE']['OrderReport']['NOTEEMAIL'] = 'Notiz/E-Mail';
$lang['de_DE']['OrderReport']['PRINTEACHORDER'] = 'Alle angezeigten Bestellungen drucken';
$lang['de_DE']['Order_statusEmail.ss']['STATUSCHANGE'] = 'Status geändert auf  "%s" für die Bestellung Nr.';
$lang['de_DE']['PaymentInformation.ss']['AMOUNT'] = 'Betrag';
$lang['de_DE']['PaymentInformation.ss']['DATE'] = 'Datum';
$lang['de_DE']['PaymentInformation.ss']['DETAILS'] = 'Details';
$lang['de_DE']['PaymentInformation.ss']['PAYMENTINFORMATION'] = 'Zahlungsinformationen';
$lang['de_DE']['PaymentInformation.ss']['PAYMENTMETHOD'] = 'Methode';
$lang['de_DE']['PaymentInformation.ss']['PAYMENTSTATUS'] = 'Bezahlstatus';
$lang['de_DE']['Product.ss']['ADD'] = '&quot;%s&quot; zjm Warenkorb hinzufügen';
$lang['de_DE']['Product.ss']['ADDLINK'] = 'Diesen Artikel zum Warenkorb hinzufügen';
$lang['de_DE']['Product.ss']['AUTHOR'] = 'Autor';
$lang['de_DE']['Product.ss']['IMAGE'] = '%s Bild';
$lang['de_DE']['Product.ss']['ItemID'] = 'Artikel Nr.';
$lang['de_DE']['Product.ss']['NOIMAGE'] = 'Keine Produktabbildung vorhanden für &quot;%s&quot;';
$lang['de_DE']['Product.ss']['QUANTITYCART'] = 'Menge im Warenkorb';
$lang['de_DE']['Product.ss']['REMOVELINK'] = '&raquo; Aus dem Warenkorb entfernen';
$lang['de_DE']['Product.ss']['SIZE'] = 'Größe';
$lang['de_DE']['ProductGroup.ss']['VIEWGROUP'] = 'Produktgruppe &quot;%s&quot; anzeigen';
$lang['de_DE']['ProductGroupItem.ss']['ADD'] = '&quot;%s&quot; zum Warenkorb hinzufügen';
$lang['de_DE']['ProductGroupItem.ss']['ADDLINK'] = 'Diesen Artikel zum Warenkorb hinzufügen';
$lang['de_DE']['ProductGroupItem.ss']['AUTHOR'] = 'Autor';
$lang['de_DE']['ProductGroupItem.ss']['IMAGE'] = '%s Bild';
$lang['de_DE']['ProductGroupItem.ss']['NOIMAGE'] = 'Keine Produktabbildung vorhanden für &quot;%s&quot;';
$lang['de_DE']['ProductGroupItem.ss']['QUANTITY'] = 'Menge';
$lang['de_DE']['ProductGroupItem.ss']['READMORE'] = 'Erfahren Sie hier mehr über &quot;%s&quot;';
$lang['de_DE']['ProductGroupItem.ss']['REMOVELINK'] = '&raquo; Aus dem Warenkorb entfernen';
$lang['de_DE']['ProductMenu.ss']['GOTOPAGE'] = 'Zur %s Seite';
$lang['de_DE']['Report']['ALLCLICKHERE'] = 'Klicken Sie hier, um alle Produkte anzuzeigen';
$lang['de_DE']['Report']['INVOICE'] = 'Rechnung';
$lang['de_DE']['Report']['PRINT'] = 'drucken';
$lang['de_DE']['Report']['VIEW'] = 'anzeigen';
$lang['de_DE']['ViewAllProducts.ss']['AUTHOR'] = 'Autor';
$lang['de_DE']['ViewAllProducts.ss']['CATEGORIES'] = 'Kategorien';
$lang['de_DE']['ViewAllProducts.ss']['IMAGE'] = '%s Bild';
$lang['de_DE']['ViewAllProducts.ss']['LASTEDIT'] = 'Zuletzt bearbeitet';
$lang['de_DE']['ViewAllProducts.ss']['LINK'] = 'Link';
$lang['de_DE']['ViewAllProducts.ss']['PRICE'] = 'Preis';
$lang['de_DE']['ViewAllProducts.ss']['PRODUCTID'] = 'Produkt ID';
$lang['de_DE']['ViewAllProducts.ss']['WEIGHT'] = 'Gewicht';

?>