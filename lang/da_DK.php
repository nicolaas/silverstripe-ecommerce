<?php

/**
 * Danish (Denmark) language pack
 * @package modules: ecommerce
 * @subpackage i18n
 */

i18n::include_locale_file('modules: ecommerce', 'en_US');

global $lang;

if(array_key_exists('da_DK', $lang) && is_array($lang['da_DK'])) {
	$lang['da_DK'] = array_merge($lang['en_US'], $lang['da_DK']);
} else {
	$lang['da_DK'] = $lang['en_US'];
}

$lang['da_DK']['AccountPage.ss']['COMPLETED'] = 'Fuldførte ordrer';
$lang['da_DK']['AccountPage.ss']['NOCOMPLETED'] = 'Ingen fuldførte ordrer blev fundet.';
$lang['da_DK']['AccountPage.ss']['ORDER'] = 'Ordre #';
$lang['da_DK']['Cart.ss']['HEADLINE'] = 'Min indkøbsvogn';
$lang['da_DK']['Cart.ss']['PRICE'] = 'Pris';
$lang['da_DK']['Cart.ss']['RemoveAlt'] = 'Fjern';
$lang['da_DK']['Cart.ss']['SHIPPING'] = 'Levering';
$lang['da_DK']['Cart.ss']['SUBTOTAL'] = 'Subtotal';
$lang['da_DK']['Cart.ss']['TOTAL'] = 'Total';
$lang['da_DK']['CheckoutPage.ss']['ORDERSTATUS'] = 'Ordrestatus';
$lang['da_DK']['FindOrderReport']['DATERANGE'] = 'Dato interval';
$lang['da_DK']['MemberForm']['DETAILSSAVED'] = 'Dine detajler er blevet gemt';
$lang['da_DK']['MemberForm']['LOGGEDIN'] = 'Du er i øjeblikket logget ind.';
$lang['da_DK']['OrderInformation.ss']['ADDRESS'] = 'Adresse';
$lang['da_DK']['OrderInformation.ss']['AMOUNT'] = 'Antal';
$lang['da_DK']['OrderInformation.ss']['BUYERSADDRESS'] = 'Købers adresse';
$lang['da_DK']['OrderInformation.ss']['CITY'] = 'By';
$lang['da_DK']['OrderInformation.ss']['COUNTRY'] = 'Land';
$lang['da_DK']['OrderInformation.ss']['CUSTOMERDETAILS'] = 'Kundedetaljer';
$lang['da_DK']['OrderInformation.ss']['DATE'] = 'Dato';
$lang['da_DK']['OrderInformation.ss']['DETAILS'] = 'Detaljer';
$lang['da_DK']['OrderInformation.ss']['EMAIL'] = 'Email';
$lang['da_DK']['OrderInformation.ss']['MOBILE'] = 'Mobil';
$lang['da_DK']['OrderInformation.ss']['NAME'] = 'Navn';
$lang['da_DK']['OrderInformation.ss']['ORDERSUMMARY'] = 'Ordresammendrag';
$lang['da_DK']['OrderInformation.ss']['PAYMENTID'] = 'Betalingsid';
$lang['da_DK']['OrderInformation.ss']['PAYMENTINFORMATION'] = 'Betalingsinformationer';
$lang['da_DK']['OrderInformation.ss']['PAYMENTMETHOD'] = 'Metode';
$lang['da_DK']['OrderInformation.ss']['PAYMENTSTATUS'] = 'Betalingsstatus';
$lang['da_DK']['OrderInformation.ss']['PHONE'] = 'Telefon';
$lang['da_DK']['OrderInformation.ss']['PRICE'] = 'Pris';
$lang['da_DK']['OrderInformation.ss']['PRODUCT'] = 'Produkt';
$lang['da_DK']['OrderInformation.ss']['QUANTITY'] = 'Antal';
$lang['da_DK']['OrderInformation.ss']['SHIPPING'] = 'Levering';
$lang['da_DK']['OrderInformation.ss']['SHIPPINGTO'] = 'til';
$lang['da_DK']['OrderInformation.ss']['SUBTOTAL'] = 'Subtotal';
$lang['da_DK']['OrderInformation.ss']['TOTALl'] = 'Total';
$lang['da_DK']['OrderInformation.ss']['TOTALOUTSTANDING'] = 'Udestående totalt';
$lang['da_DK']['OrderInformation.ss']['TOTALPRICE'] = 'Total pris';
$lang['da_DK']['OrderInformation_Editable.ss']['ORDERINFORMATION'] = 'Ordreinformation';
$lang['da_DK']['OrderInformation_Editable.ss']['PRICE'] = 'Pris';
$lang['da_DK']['OrderInformation_Editable.ss']['PRODUCT'] = 'Produkt';
$lang['da_DK']['OrderInformation_Editable.ss']['QUANTITY'] = 'Antal';
$lang['da_DK']['OrderInformation_Editable.ss']['SHIPPING'] = 'Levering';
$lang['da_DK']['OrderInformation_Editable.ss']['SHIPPINGTO'] = 'til';
$lang['da_DK']['OrderInformation_Editable.ss']['SUBTOTAL'] = 'Subtotal';
$lang['da_DK']['OrderInformation_Editable.ss']['TOTAL'] = 'Total';
$lang['da_DK']['OrderInformation_Editable.ss']['TOTALPRICE'] = 'Total pris';
$lang['da_DK']['OrderInformation_NoPricing.ss']['ADDRESS'] = 'Adresse';
$lang['da_DK']['OrderInformation_NoPricing.ss']['BUYERSADDRESS'] = 'Købers adresse';
$lang['da_DK']['OrderInformation_NoPricing.ss']['CITY'] = 'By';
$lang['da_DK']['OrderInformation_NoPricing.ss']['COUNTRY'] = 'Land';
$lang['da_DK']['OrderInformation_NoPricing.ss']['CUSTOMERDETAILS'] = 'Kundedetaljer';
$lang['da_DK']['OrderInformation_NoPricing.ss']['EMAIL'] = 'Email';
$lang['da_DK']['OrderInformation_NoPricing.ss']['MOBILE'] = 'Mobil';
$lang['da_DK']['OrderInformation_NoPricing.ss']['NAME'] = 'Navn';
$lang['da_DK']['OrderInformation_NoPricing.ss']['ORDERINFO'] = 'Information tilhørende ordre #';
$lang['da_DK']['OrderInformation_NoPricing.ss']['PHONE'] = 'Telefon';
$lang['da_DK']['OrderInformation_PackingSlip.ss']['DESCRIPTION'] = 'Beskrivelse';
$lang['da_DK']['OrderInformation_PackingSlip.ss']['ITEM'] = 'Artikel';
$lang['da_DK']['OrderInformation_PackingSlip.ss']['ORDERDATE'] = 'Ordredato';
$lang['da_DK']['OrderInformation_PackingSlip.ss']['QUANTITY'] = 'Antal';
$lang['da_DK']['OrderInformation_Print.ss']['PAGETITLE'] = 'Print  ordrer';
$lang['da_DK']['OrderReport']['CHANGESTATUS'] = 'Ændr ordrestatus';
$lang['da_DK']['OrderReport']['NOTEEMAIL'] = 'Note/Email';
$lang['da_DK']['OrderReport']['PRINTEACHORDER'] = 'Print alle viste ordrer';
$lang['da_DK']['OrderReport']['SENDNOTETO'] = 'Send denne note til %s (%s)';
$lang['da_DK']['PaymentInformation.ss']['DATE'] = 'Dato';
$lang['da_DK']['PaymentInformation.ss']['DETAILS'] = 'Detaljer';
$lang['da_DK']['PaymentInformation.ss']['PAYMENTID'] = 'Betalingsid';
$lang['da_DK']['PaymentInformation.ss']['PAYMENTINFORMATION'] = 'Betalingsinformationer';
$lang['da_DK']['PaymentInformation.ss']['PAYMENTMETHOD'] = 'Metode';
$lang['da_DK']['PaymentInformation.ss']['PAYMENTSTATUS'] = 'Betalingsstatus';
$lang['da_DK']['Product.ss']['IMAGE'] = '%s billede';
$lang['da_DK']['ProductGroupItem.ss']['AUTHOR'] = 'Forfatter';
$lang['da_DK']['ProductGroupItem.ss']['IMAGE'] = '%s billede';
$lang['da_DK']['ProductGroupItem.ss']['QUANTITY'] = 'Antal';
$lang['da_DK']['ProductGroupItem.ss']['READMORECONTENT'] = 'Klik for at læse mere &raquo';
$lang['da_DK']['Report']['ALLCLICKHERE'] = 'Klik her for at se alle produkter';
$lang['da_DK']['Report']['INVOICE'] = 'Faktura';
$lang['da_DK']['Report']['PRINT'] = 'print';
$lang['da_DK']['Report']['VIEW'] = 'Vis';
$lang['da_DK']['ViewAllProducts.ss']['AUTHOR'] = 'Forfatter';
$lang['da_DK']['ViewAllProducts.ss']['CATEGORIES'] = 'Kategorier';
$lang['da_DK']['ViewAllProducts.ss']['IMAGE'] = '%s billede';
$lang['da_DK']['ViewAllProducts.ss']['LASTEDIT'] = 'Sidst ændret';
$lang['da_DK']['ViewAllProducts.ss']['LINK'] = 'Link';
$lang['da_DK']['ViewAllProducts.ss']['PRICE'] = 'Pris';
$lang['da_DK']['ViewAllProducts.ss']['PRODUCTID'] = 'Produktid';
$lang['da_DK']['ViewAllProducts.ss']['WEIGHT'] = 'Vægt';

?>