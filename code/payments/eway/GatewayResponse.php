<?php
/// <summary>
/// Summary description for GatewayResponse.
/// Copyright Web Active Corporation Pty Ltd  - All rights reserved. 1998-2004
/// This code is for exclusive use with the eWAY payment gateway
/// </summary>
class GatewayResponse
{

	var $txAmount = 0;
      	var $txTransactionNumber = "";
      	var $txInvoiceReference = "";
      	var $txOption1 = "";
      	var $txOption2 = "";
      	var $txOption3 = "";
      	var $txStatus = false;
      	var $txAuthCode = "";
      	var $txError = "";

	function GatewayResponse($Xml)
	{
		$xtr = simplexml_load_string($Xml) or die ("Unable to load XML string!");
                $this->txError = $xtr->ewayTrxnError;
                $this->txStatus = $xtr->ewayTrxnStatus;
                $this->txTransactionNumber = $xtr->ewayTrxnNumber;
                $this->txOption1 = $xtr->ewayTrxnOption1;
                $this->txOption2 = $xtr->ewayTrxnOption2;
		$this->txOption3 = $xtr->ewayTrxnOption3;
                $this->txAmount = $xtr->ewayReturnAmount; 
                $this->txAuthCode = $xtr->ewayAuthCode;
                $this->txInvoiceReference = $xtr->ewayTrxnReference;
      	}


      	function TransactionNumber()
      	{
         	return $this->txTransactionNumber; 
      	}

      	function InvoiceReference() 
      	{
         	return $this->txInvoiceReference; 
      	}

      	function Option1() 
      	{
        	return $this->txOption1; 
      	}

      	function Option2() 
      	{
         	return $this->txOption2; 
      	}

      	function Option3() 
      	{
         	return $this->txOption3; 
      	}

      	function AuthorisationCode()
      	{
         	return $this->txAuthCode; 
      	}
   
      	function Error()
      	{
         	return $this->txError; 
      	}   
   
      	function Amount() 
      	{
         	return $this->txAmount; 
      	}   
      
	function Status()
      	{
         	return $this->txStatus;
      	}
}

?>
