<?php

/**
 * Italian (Italy) language pack
 * @package modules: ecommerce
 * @subpackage i18n
 */

i18n::include_locale_file('modules: ecommerce', 'en_US');

global $lang;

if(array_key_exists('it_IT', $lang) && is_array($lang['it_IT'])) {
	$lang['it_IT'] = array_merge($lang['en_US'], $lang['it_IT']);
} else {
	$lang['it_IT'] = $lang['en_US'];
}

$lang['it_IT']['AccountPage.ss']['COMPLETED'] = 'Ordini Completati';
$lang['it_IT']['AccountPage.ss']['INCOMPLETE'] = 'Ordini non completati';
$lang['it_IT']['AccountPage.ss']['NOCOMPLETED'] = 'Non sono stati trovati ordini completati.';
$lang['it_IT']['AccountPage.ss']['NOINCOMPLETE'] = 'Non sono stati trovati ordini non completati.';
$lang['it_IT']['AccountPage.ss']['ORDER'] = 'Ordine n°';
$lang['it_IT']['AccountPage.ss']['READMORE'] = 'Vedi altre informazioni sull\'ordine n°%s';
$lang['it_IT']['Cart.ss']['CheckoutClick'] = 'Clicca qui per effettuare il checkout';
$lang['it_IT']['Cart.ss']['CheckoutGoTo'] = 'Procedi al checkout';
$lang['it_IT']['Cart.ss']['HEADLINE'] = 'Il Mio Carrello';
$lang['it_IT']['Cart.ss']['NOITEMS'] = 'Non ci sono elementi nel tuo carrello';
$lang['it_IT']['Cart.ss']['PRICE'] = 'Prezzo';
$lang['it_IT']['Cart.ss']['READMORE'] = 'Clicca qui per leggere più informazioni su &quot;%s&quot;';
$lang['it_IT']['Cart.ss']['Remove'] = 'Rimuovi &quot;%s&quot; dal tuo carrello';
$lang['it_IT']['Cart.ss']['RemoveAlt'] = 'Rimuovi';
$lang['it_IT']['Cart.ss']['SHIPPING'] = 'Spedizione';
$lang['it_IT']['Cart.ss']['SUBTOTAL'] = 'Subtotale';
$lang['it_IT']['Cart.ss']['TOTAL'] = 'Totale';
$lang['it_IT']['CheckoutPage']['NOPAGE'] = 'Non è ancora stata creata una pagina di Checkout in questo sito - per favore creane una!';
$lang['it_IT']['CheckoutPage.ss']['CHECKOUT'] = 'Checkout';
$lang['it_IT']['CheckoutPage.ss']['ORDERSTATUS'] = 'Stato dell\'ordine';
$lang['it_IT']['CheckoutPage_OrderIncomplete.ss']['BACKTOCHECKOUT'] = 'Clicca qui per tornare al Checkout';
$lang['it_IT']['CheckoutPage_OrderIncomplete.ss']['CHECKOUT'] = 'Checkout';
$lang['it_IT']['CheckoutPage_OrderIncomplete.ss']['CHEQUEINSTRUCTIONS'] = 'Se hai ordinato tramite assegno riceverai un\'email con le istruzioni.';
$lang['it_IT']['CheckoutPage_OrderIncomplete.ss']['DETAILSSUBMITTED'] = 'Questi sono le informazioni che hai fornito';
$lang['it_IT']['CheckoutPage_OrderIncomplete.ss']['INCOMPLETE'] = 'Ordine non completato';
$lang['it_IT']['CheckoutPage_OrderIncomplete.ss']['ORDERSTATUS'] = 'Stato dell\'Ordine';
$lang['it_IT']['CheckoutPage_OrderSuccessful.ss']['BACKTOCHECKOUT'] = 'Clicca qui per tornare al Checkout';
$lang['it_IT']['CheckoutPage_OrderSuccessful.ss']['CHECKOUT'] = 'Checkout';
$lang['it_IT']['CheckoutPage_OrderSuccessful.ss']['EMAILDETAILS'] = 'Una copia di questa pagina è stata inviata al tuo indirizzo email a conferma dell\'ordine.';
$lang['it_IT']['CheckoutPage_OrderSuccessful.ss']['ORDERSTATUS'] = 'Stato dell\'Ordine';
$lang['it_IT']['CheckoutPage_OrderSuccessful.ss']['SUCCESSFULl'] = 'Ordine Inviato Correttamente!';
$lang['it_IT']['ChequePayment']['MESSAGE'] = 'Il pagamento tramite assegno è stato accettato. Nota Bene: i prodotti non verranno spediti fino all\'avvenuto ricevimento del pagamento.';
$lang['it_IT']['MemberForm']['DETAILSSAVED'] = 'I tuoi dati sono stati salvati';
$lang['it_IT']['OrderInformation.ss']['ADDRESS'] = 'Indirizzo';
$lang['it_IT']['OrderInformation.ss']['AMOUNT'] = 'Importo';
$lang['it_IT']['OrderInformation.ss']['CITY'] = 'Città';
$lang['it_IT']['OrderInformation.ss']['COUNTRY'] = 'Stato';
$lang['it_IT']['OrderInformation.ss']['DATE'] = 'Data';
$lang['it_IT']['OrderInformation.ss']['DETAILS'] = 'Dettagli';
$lang['it_IT']['OrderInformation.ss']['EMAIL'] = 'Email';
$lang['it_IT']['OrderInformation.ss']['MOBILE'] = 'Telefono cellulare';
$lang['it_IT']['OrderInformation.ss']['NAME'] = 'Nome';
$lang['it_IT']['OrderInformation.ss']['ORDERSUMMARY'] = 'Riepilogo dell\'ordine';
$lang['it_IT']['OrderInformation.ss']['PAYMENTID'] = 'ID del Pagamento';
$lang['it_IT']['OrderInformation.ss']['PAYMENTINFORMATION'] = 'Informazioni sul Pagamento';
$lang['it_IT']['OrderInformation.ss']['PAYMENTMETHOD'] = 'metodo';
$lang['it_IT']['OrderInformation.ss']['PAYMENTSTATUS'] = 'Stato del Pagamento';
$lang['it_IT']['OrderInformation.ss']['PHONE'] = 'Telefono fisso';
$lang['it_IT']['OrderInformation.ss']['PRICE'] = 'Prezzo';
$lang['it_IT']['OrderInformation.ss']['PRODUCT'] = 'Prodotto';
$lang['it_IT']['OrderInformation.ss']['QUANTITY'] = 'Quantità';
$lang['it_IT']['OrderInformation.ss']['SHIPPING'] = 'Spedizione';
$lang['it_IT']['OrderInformation.ss']['SHIPPINGTO'] = 'a';
$lang['it_IT']['OrderInformation.ss']['SUBTOTAL'] = 'Subtotale';
$lang['it_IT']['OrderInformation.ss']['TABLESUMMARY'] = 'Qui viene visualizzato tutto il contenuto del tuo carrello insieme a un riepilogo di tutti i costi associati e a una scelta delle opzioni di pagamento.';
$lang['it_IT']['OrderInformation.ss']['TOTALl'] = 'Totale';
$lang['it_IT']['OrderInformation.ss']['TOTALPRICE'] = 'Totale';
$lang['it_IT']['OrderInformation_Editable.ss']['NOITEMS'] = '<strong>Non</strong> ci sono oggetti nel tuo carrello.';
$lang['it_IT']['OrderInformation_Editable.ss']['ORDERINFORMATION'] = 'Informazioni sull\'ordine';
$lang['it_IT']['OrderInformation_Editable.ss']['PRICE'] = 'Prezzo';
$lang['it_IT']['OrderInformation_Editable.ss']['PRODUCT'] = 'Prodotto';
$lang['it_IT']['OrderInformation_Editable.ss']['QUANTITY'] = 'Quantità';
$lang['it_IT']['OrderInformation_Editable.ss']['SHIPPING'] = 'Spedizione';
$lang['it_IT']['OrderInformation_Editable.ss']['TOTAL'] = 'Totale';
$lang['it_IT']['OrderInformation_NoPricing.ss']['ADDRESS'] = 'Indirizzo';
$lang['it_IT']['OrderInformation_NoPricing.ss']['BUYERSADDRESS'] = 'Indirizzo del Acquirente';
$lang['it_IT']['OrderInformation_NoPricing.ss']['CITY'] = 'Città';
$lang['it_IT']['OrderInformation_NoPricing.ss']['COUNTRY'] = 'Nazione';
$lang['it_IT']['OrderInformation_NoPricing.ss']['CUSTOMERDETAILS'] = 'Dettagli del Cliente';
$lang['it_IT']['OrderInformation_NoPricing.ss']['EMAIL'] = 'Email';
$lang['it_IT']['OrderInformation_NoPricing.ss']['MOBILE'] = 'Cellulare';
$lang['it_IT']['OrderInformation_NoPricing.ss']['NAME'] = 'Nome';
$lang['it_IT']['OrderInformation_NoPricing.ss']['ORDERINFO'] = 'Informazioni sull\'Ordine #';
$lang['it_IT']['OrderInformation_NoPricing.ss']['PHONE'] = 'Telefono';
$lang['it_IT']['OrderInformation_PackingSlip.ss']['DESCRIPTION'] = 'Descrizione';
$lang['it_IT']['OrderInformation_PackingSlip.ss']['ITEM'] = 'Oggetto';
$lang['it_IT']['OrderInformation_PackingSlip.ss']['ORDERDATE'] = 'Data dell\'ordine';
$lang['it_IT']['OrderInformation_PackingSlip.ss']['ORDERNUMBER'] = 'Numero d\'Ordine';
$lang['it_IT']['OrderInformation_PackingSlip.ss']['PAGETITLE'] = 'Negozio Stampa Ordini';
$lang['it_IT']['OrderInformation_PackingSlip.ss']['QUANTITY'] = 'Quantità';
$lang['it_IT']['OrderInformation_Print.ss']['PAGETITLE'] = 'Stampa Ordini';
$lang['it_IT']['OrderReport']['CHANGESTATUS'] = 'Cambia lo status dell\'ordine';
$lang['it_IT']['OrderReport']['NOTEEMAIL'] = 'Nota/Email';
$lang['it_IT']['OrderReport']['PRINTEACHORDER'] = 'Stampa tutti gli ordini visualizzati';
$lang['it_IT']['OrderReport']['SENDNOTETO'] = 'Manda questa nota a %s (%s)';
$lang['it_IT']['PaymentInformation.ss']['AMOUNT'] = 'Importo';
$lang['it_IT']['PaymentInformation.ss']['DATE'] = 'Data';
$lang['it_IT']['PaymentInformation.ss']['DETAILS'] = 'Dettagli';
$lang['it_IT']['PaymentInformation.ss']['PAYMENTID'] = 'ID del Pagamento';
$lang['it_IT']['PaymentInformation.ss']['PAYMENTINFORMATION'] = 'Informazioni sul Pagamento';
$lang['it_IT']['PaymentInformation.ss']['PAYMENTMETHOD'] = 'Metodo';
$lang['it_IT']['PaymentInformation.ss']['PAYMENTSTATUS'] = 'Stato del Pagamento';
$lang['it_IT']['Product.ss']['ADDLINK'] = 'Aggiungi questo articolo al carrello';
$lang['it_IT']['Product.ss']['AUTHOR'] = 'Autore';
$lang['it_IT']['Product.ss']['IMAGE'] = 'Immagine %s';
$lang['it_IT']['Product.ss']['ItemID'] = 'Oggetto n°';
$lang['it_IT']['Product.ss']['NOIMAGE'] = 'Siamo spiacenti, nessuna immagine per il prodotto &quot;%s&quot;';
$lang['it_IT']['Product.ss']['QUANTITYCART'] = 'Numero di oggetti nel carrello';
$lang['it_IT']['Product.ss']['REMOVELINK'] = '&raquo; Togli dal carrello';
$lang['it_IT']['Product.ss']['SIZE'] = 'Grandezza';
$lang['it_IT']['ProductGroupItem.ss']['ADD'] = 'Aggiungi &quot;%s&quot; al tuo carrello';
$lang['it_IT']['ProductGroupItem.ss']['ADDLINK'] = 'Aggiungi questo oggetto al carrello';
$lang['it_IT']['ProductGroupItem.ss']['ADDONE'] = 'Aggiungi un\'unità di  &quot;%s&quot; al tuo carrello';
$lang['it_IT']['ProductGroupItem.ss']['AUTHOR'] = 'Autore';
$lang['it_IT']['ProductGroupItem.ss']['IMAGE'] = 'Immagine di %s';
$lang['it_IT']['ProductGroupItem.ss']['NOIMAGE'] = 'Siamo spiacenti, non abbiamo nessuna immagine per il prodotto &quot;%s&quot;';
$lang['it_IT']['ProductGroupItem.ss']['QUANTITY'] = 'Quantità';
$lang['it_IT']['ProductGroupItem.ss']['READMORE'] = 'Clicca qui per saperne di più su &quot; %s &quot;';
$lang['it_IT']['ProductGroupItem.ss']['READMORECONTENT'] = 'Clicca qui per saperne di più &raquo;';
$lang['it_IT']['ProductGroupItem.ss']['REMOVE'] = 'Rimuovi &quot;%s&quot; dal tuo carrello';
$lang['it_IT']['ProductGroupItem.ss']['REMOVELINK'] = '&raquo; Rimuovi dal carrello';
$lang['it_IT']['ProductGroupItem.ss']['REMOVEONE'] = 'Rimuovi un\'unità di &quot;%s&quot; dal tuo carrello';
$lang['it_IT']['ProductMenu.ss']['GOTOPAGE'] = 'Vai a Pagina %s';
$lang['it_IT']['SSReport']['ALLCLICKHERE'] = 'Clicca qui per vedere tutti i prodotti';
$lang['it_IT']['SSReport']['INVOICE'] = 'fattura';
$lang['it_IT']['SSReport']['PRINT'] = 'stampa';
$lang['it_IT']['SSReport']['VIEW'] = 'visualizza';
$lang['it_IT']['ViewAllProducts.ss']['AUTHOR'] = 'Autore';
$lang['it_IT']['ViewAllProducts.ss']['CATEGORIES'] = 'Categorie';
$lang['it_IT']['ViewAllProducts.ss']['IMAGE'] = 'Immagine di %s';
$lang['it_IT']['ViewAllProducts.ss']['LASTEDIT'] = 'Ultima modifica';
$lang['it_IT']['ViewAllProducts.ss']['LINK'] = 'Link';
$lang['it_IT']['ViewAllProducts.ss']['NOIMAGE'] = 'Mi dispiace, non ho un\'immagine per &quot;%s&quot;';
$lang['it_IT']['ViewAllProducts.ss']['NOSUBJECTS'] = 'Nessun soggetto impostato';
$lang['it_IT']['ViewAllProducts.ss']['PRICE'] = 'Prezzo';
$lang['it_IT']['ViewAllProducts.ss']['PRODUCTID'] = 'ID del Prodotto';
$lang['it_IT']['ViewAllProducts.ss']['WEIGHT'] = 'Peso';

?>