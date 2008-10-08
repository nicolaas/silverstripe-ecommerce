<?php

/**
 * Norwegian Bokmal (Norway) language pack
 * @package modules: ecommerce
 * @subpackage i18n
 */

i18n::include_locale_file('modules: ecommerce', 'en_US');

global $lang;

if(array_key_exists('nb_NO', $lang) && is_array($lang['nb_NO'])) {
	$lang['nb_NO'] = array_merge($lang['en_US'], $lang['nb_NO']);
} else {
	$lang['nb_NO'] = $lang['en_US'];
}

$lang['nb_NO']['AccountPage.ss']['COMPLETED'] = 'Fullførte bestillinger';
$lang['nb_NO']['AccountPage.ss']['ORDER'] = 'Bestilling #';
$lang['nb_NO']['Cart.ss']['CheckoutClick'] = 'Trykk her for for å gå til kassen';
$lang['nb_NO']['Cart.ss']['CheckoutGoTo'] = 'Gå til kassen';
$lang['nb_NO']['Cart.ss']['HEADLINE'] = 'Min handlevogn';
$lang['nb_NO']['Cart.ss']['NOITEMS'] = 'Det er ingenting i handlevognen';
$lang['nb_NO']['Cart.ss']['PRICE'] = 'Pris';
$lang['nb_NO']['Cart.ss']['READMORE'] = 'Trykk her for å lese mer på  &quot;%s&quot;';
$lang['nb_NO']['Cart.ss']['Remove'] = 'Fjern &quot;%s&quot; fra handlevognen';
$lang['nb_NO']['Cart.ss']['RemoveAlt'] = 'Fjern';
$lang['nb_NO']['Cart.ss']['SHIPPING'] = 'Frakt';
$lang['nb_NO']['Cart.ss']['SUBTOTAL'] = 'Delsum';
$lang['nb_NO']['Cart.ss']['TOTAL'] = 'Sum';
$lang['nb_NO']['CheckoutPage.ss']['ORDERSTATUS'] = 'Bestillingsstatus';
$lang['nb_NO']['CheckoutPage_OrderIncomplete.ss']['BACKTOCHECKOUT'] = 'Trykk her for å gå tilbake til kassen';
$lang['nb_NO']['CheckoutPage_OrderIncomplete.ss']['ORDERSTATUS'] = 'Bestillingsstatus';
$lang['nb_NO']['CheckoutPage_OrderSuccessful.ss']['BACKTOCHECKOUT'] = 'Trykk her for å gå tilbake til kassen';
$lang['nb_NO']['ChequePayment']['MESSAGE'] = 'Betalingen er akspetert med sjekk. Merk: varene vil ikke bli sendt før betalingen er motatt';
$lang['nb_NO']['FindOrderReport']['DATERANGE'] = 'Datoområde';
$lang['nb_NO']['MemberForm']['DETAILSSAVED'] = 'Detaljene er lagret';
$lang['nb_NO']['MemberForm']['LOGGEDIN'] = 'Du er logget inn';
$lang['nb_NO']['OrderInformation.ss']['ADDRESS'] = 'Adresse';
$lang['nb_NO']['OrderInformation.ss']['AMOUNT'] = 'Beløp';
$lang['nb_NO']['OrderInformation.ss']['BUYERSADDRESS'] = 'Kundeadresse';
$lang['nb_NO']['OrderInformation.ss']['CITY'] = 'By';
$lang['nb_NO']['OrderInformation.ss']['COUNTRY'] = 'Land';
$lang['nb_NO']['OrderInformation.ss']['CUSTOMERDETAILS'] = 'Kundedetaljer';
$lang['nb_NO']['OrderInformation.ss']['DATE'] = 'Dato';
$lang['nb_NO']['OrderInformation.ss']['DETAILS'] = 'Detaljer';
$lang['nb_NO']['OrderInformation.ss']['EMAIL'] = 'E-postadresse';
$lang['nb_NO']['OrderInformation.ss']['MOBILE'] = 'Mobilnr.';
$lang['nb_NO']['OrderInformation.ss']['NAME'] = 'NAvn';
$lang['nb_NO']['OrderInformation.ss']['ORDERSUMMARY'] = 'Sammendrag av bestillingen';
$lang['nb_NO']['OrderInformation.ss']['PAYMENTID'] = 'Betalings-ID';
$lang['nb_NO']['OrderInformation.ss']['PAYMENTINFORMATION'] = 'Betalingsinformasjon';
$lang['nb_NO']['OrderInformation.ss']['PAYMENTMETHOD'] = 'Metode';
$lang['nb_NO']['OrderInformation.ss']['PAYMENTSTATUS'] = 'Betalingsstatus';
$lang['nb_NO']['OrderInformation.ss']['PHONE'] = 'Telefonnr.';
$lang['nb_NO']['OrderInformation.ss']['PRICE'] = 'Pris';
$lang['nb_NO']['OrderInformation.ss']['PRODUCT'] = 'Produkt';
$lang['nb_NO']['OrderInformation.ss']['QUANTITY'] = 'Antall';
$lang['nb_NO']['OrderInformation.ss']['SHIPPING'] = 'Frakt';
$lang['nb_NO']['OrderInformation.ss']['SHIPPINGTO'] = 'til';
$lang['nb_NO']['OrderInformation.ss']['SUBTOTAL'] = 'Delsum';
$lang['nb_NO']['OrderInformation.ss']['TABLESUMMARY'] = 'Innholdet i handlevognen blir vist i dette skjemaet sammen med et sammendrag av avgiftene i forbindelse med en bestilling og et sammendrag av betalingsmulighetene.';
$lang['nb_NO']['OrderInformation.ss']['TOTALl'] = 'Sum';
$lang['nb_NO']['OrderInformation.ss']['TOTALPRICE'] = 'Totalpris';
$lang['nb_NO']['OrderInformation_Editable.ss']['ADDONE'] = 'Legg til en til av &quot;%s&quot; til handelvognen';
$lang['nb_NO']['OrderInformation_Editable.ss']['NOITEMS'] = 'Det er <strong>ingenting</strong> i handlekurven.';
$lang['nb_NO']['OrderInformation_Editable.ss']['ORDERINFORMATION'] = 'Bestillingsinformasjon';
$lang['nb_NO']['OrderInformation_Editable.ss']['PRICE'] = 'Pris';
$lang['nb_NO']['OrderInformation_Editable.ss']['PRODUCT'] = 'Produkt';
$lang['nb_NO']['OrderInformation_Editable.ss']['QUANTITY'] = 'Antall';
$lang['nb_NO']['OrderInformation_Editable.ss']['READMORE'] = 'Klikk her for å lese mer om &quot;%s&quot;';
$lang['nb_NO']['OrderInformation_Editable.ss']['REMOVEONE'] = 'Fjern en av &quot;%s&quot; fra handlevognen';
$lang['nb_NO']['OrderInformation_Editable.ss']['SHIPPING'] = 'Frakt';
$lang['nb_NO']['OrderInformation_Editable.ss']['SHIPPINGTO'] = 'til';
$lang['nb_NO']['OrderInformation_Editable.ss']['SUBTOTAL'] = 'Delsum';
$lang['nb_NO']['OrderInformation_Editable.ss']['TOTAL'] = 'Sum';
$lang['nb_NO']['OrderInformation_Editable.ss']['TOTALPRICE'] = 'Totalpris';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['ADDRESS'] = 'Adresse';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['BUYERSADDRESS'] = 'Kjøpers adresse';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['CITY'] = 'By';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['COUNTRY'] = 'Land';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['CUSTOMERDETAILS'] = 'Kundedetaljer';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['EMAIL'] = 'E-postadresse';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['MOBILE'] = 'Mobilnummer';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['NAME'] = 'Navn';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['ORDERINFO'] = 'Informasjon om bestilling #';
$lang['nb_NO']['OrderInformation_NoPricing.ss']['PHONE'] = 'Telefonnummer';
$lang['nb_NO']['OrderInformation_PackingSlip.ss']['DESCRIPTION'] = 'Beskrivelse';
$lang['nb_NO']['OrderInformation_PackingSlip.ss']['ORDERDATE'] = 'Bestillingsdato';
$lang['nb_NO']['OrderInformation_PackingSlip.ss']['ORDERNUMBER'] = 'Bestillingsnummer';
$lang['nb_NO']['OrderInformation_PackingSlip.ss']['QUANTITY'] = 'Antall';
$lang['nb_NO']['OrderInformation_Print.ss']['PAGETITLE'] = 'Skriv ut bestillinger';
$lang['nb_NO']['OrderReport']['CHANGESTATUS'] = 'Endre bestillingsstatus';
$lang['nb_NO']['OrderReport']['NOTEEMAIL'] = 'Merknad/e-post';
$lang['nb_NO']['OrderReport']['SENDNOTETO'] = 'Send merknaden til %s (%s)';
$lang['nb_NO']['PaymentInformation.ss']['AMOUNT'] = 'Beløp';
$lang['nb_NO']['PaymentInformation.ss']['DATE'] = 'Dato';
$lang['nb_NO']['PaymentInformation.ss']['DETAILS'] = 'Detaljer';
$lang['nb_NO']['PaymentInformation.ss']['PAYMENTID'] = 'Betalings-ID';
$lang['nb_NO']['PaymentInformation.ss']['PAYMENTINFORMATION'] = 'Betalingsinformasjon';
$lang['nb_NO']['PaymentInformation.ss']['PAYMENTMETHOD'] = 'Metode';
$lang['nb_NO']['PaymentInformation.ss']['PAYMENTSTATUS'] = 'Betalingsstatus';
$lang['nb_NO']['ProductGroupItem.ss']['ADD'] = 'Legg &quot;%s&quot; i handlekurven';
$lang['nb_NO']['ProductGroupItem.ss']['ADDLINK'] = 'Legg i handlevognen';
$lang['nb_NO']['ProductGroupItem.ss']['ADDONE'] = 'Legg til en til av &quot;%s&quot; i handlevognen';
$lang['nb_NO']['ProductGroupItem.ss']['AUTHOR'] = 'Forfatter';
$lang['nb_NO']['ProductGroupItem.ss']['IMAGE'] = '%s bilde';
$lang['nb_NO']['ProductGroupItem.ss']['NOIMAGE'] = 'Beklager, det finnes ikke noe bilde av &quot;%s&quot;';
$lang['nb_NO']['ProductGroupItem.ss']['QUANTITY'] = 'Antall';
$lang['nb_NO']['ProductGroupItem.ss']['READMORE'] = 'Trykk her for å lese mer om &quot;%s&quot;';
$lang['nb_NO']['ProductGroupItem.ss']['READMORECONTENT'] = 'Trykk her for å lese mer &raquo;';
$lang['nb_NO']['ProductGroupItem.ss']['REMOVE'] = 'Fjern &quot;%s&quot; fra handlekurven';
$lang['nb_NO']['ProductGroupItem.ss']['REMOVELINK'] = '&raquo; Fjern fra handlevognen';
$lang['nb_NO']['ProductGroupItem.ss']['REMOVEONE'] = 'Fjern en av &quot;%s&quot; fra handlevognen';
$lang['nb_NO']['ProductMenu.ss']['GOTOPAGE'] = 'Gå til %s';
$lang['nb_NO']['SSReport']['ALLCLICKHERE'] = 'Trykk her for å se alle produktene';
$lang['nb_NO']['SSReport']['INVOICE'] = 'regning';
$lang['nb_NO']['ViewAllProducts.ss']['AUTHOR'] = 'Forfatter';
$lang['nb_NO']['ViewAllProducts.ss']['CATEGORIES'] = 'Kategorier';
$lang['nb_NO']['ViewAllProducts.ss']['LASTEDIT'] = 'Sist redigert';
$lang['nb_NO']['ViewAllProducts.ss']['LINK'] = 'Lenke';
$lang['nb_NO']['ViewAllProducts.ss']['PRICE'] = 'Pris';
$lang['nb_NO']['ViewAllProducts.ss']['PRODUCTID'] = 'Produkt-ID';
$lang['nb_NO']['ViewAllProducts.ss']['WEIGHT'] = 'Vekt';

?>