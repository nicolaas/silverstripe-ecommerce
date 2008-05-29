<?php
/// <summary>
/// Summary description for GatewayRequest.
/// Copyright Web Active Corporation Pty Ltd  - All rights reserved. 1998-2004
/// This code is for exclusive use with the eWAY payment gateway
/// </summary>
class GatewayRequest
{
	var $txCustomerID = "";

	var $txAmount = 0;

      	var $txCardholderName = "";

      	var $txCardNumber = "";

      	var $txCardExpiryMonth = "01";

      	var $txCardExpiryYear = "00";

      	var $txTransactionNumber = "";

      	var $txCardholderFirstName = "";

      	var $txCardholderLastName = "";

      	var $txCardholderEmailAddress = "";

      	var $txCardholderAddress = "";

      	var $txCardholderPostalCode = "";

      	var $txInvoiceReference = "";

      	var $txInvoiceDescription = "";

	//var $txCVN = "";

      	var $txOption1 = "";

      	var $txOption2 = "";

      	var $txOption3 = "";

	function GatewayRequest(){
	} 

      	function EwayCustomerID($value) 
	{
		$this->txCustomerID=$value;
        }

      	function InvoiceAmount($value)
      	{
            	$this->txAmount=$value;

        }

      	function CardHolderName($value)
      	{
        	$this->txCardholderName=$value;

        }

      	function CardExpiryMonth($value)  
      	{
            	$this->txCardExpiryMonth=$value;
        }

      	function CardExpiryYear($value)
      	{
            	$this->txCardExpiryYear=$value;
        }

      	function TransactionNumber($value)
      	{
            	$this->txTransactionNumber=$value;

         }

      	function PurchaserFirstName($value)
      	{
            	$this->txCardholderFirstName=$value;
        }

	function PurchaserLastName($value)
      	{
            	$this->txCardholderLastName=$value;
        }

      	function CardNumber($value)
      	{
            	$this->txCardNumber=$value;
        }

      	function PurchaserAddress($value)
      	{
      		$this->txCardholderAddress=$value;
      	}

      	function PurchaserPostalCode($value)
      	{
      		$this->txCardholderPostalCode=$value;
      	}

      	function PurchaserEmailAddress($value)
      	{
      		$this->txCardholderEmailAddress=$value;
      	}

      	function InvoiceReference($value) 
	{
         	$this->txInvoiceReference=$value; 
	}

      	function InvoiceDescription($value) 
	{
         	$this->txInvoiceDescription=$value; 
	}

	//function CVN($value) 
	//{
	// 	$this->txCVN=$value; 
	//}

      	function EwayOption1($value) 
	{
         	$this->txOption1=$value; 
	}

        function EwayOption2($value) 
        {
                $this->txOption2=$value; 
        }

        function EwayOption3($value) 
        {
                $this->txOption3=$value; 
        }
	
      	function ToXml()
      	{
         	// We don't really need the overhead of creating an XML DOM object
         	// to really just concatenate a string together.

         	$xml = "<ewaygateway>";
	        $xml .= $this->CreateNode("ewayCustomerID", $this->txCustomerID);
         	$xml .= $this->CreateNode("ewayTotalAmount", $this->txAmount);
         	$xml .= $this->CreateNode("ewayCardHoldersName", $this->txCardholderName);
         	$xml .= $this->CreateNode("ewayCardNumber", $this->txCardNumber);
         	$xml .= $this->CreateNode("ewayCardExpiryMonth", $this->txCardExpiryMonth);
         	$xml .= $this->CreateNode("ewayCardExpiryYear", $this->txCardExpiryYear);
         	$xml .= $this->CreateNode("ewayTrxnNumber", $this->txTransactionNumber);
         	$xml .= $this->CreateNode("ewayCustomerInvoiceDescription", $this->txInvoiceDescription);
         	$xml .= $this->CreateNode("ewayCustomerFirstName", $this->txCardholderFirstName);
         	$xml .= $this->CreateNode("ewayCustomerLastName", $this->txCardholderLastName);
         	$xml .= $this->CreateNode("ewayCustomerEmail", $this->txCardholderEmailAddress);
         	$xml .= $this->CreateNode("ewayCustomerAddress", $this->txCardholderAddress);
         	$xml .= $this->CreateNode("ewayCustomerPostcode", $this->txCardholderPostalCode);
         	$xml .= $this->CreateNode("ewayCustomerInvoiceRef", $this->txInvoiceReference);
		 //$xml .= $this->CreateNode("ewayCVN", $this->txCVN);
         	$xml .= $this->CreateNode("ewayOption1", $this->txOption1);
         	$xml .= $this->CreateNode("ewayOption2", $this->txOption2);
         	$xml .= $this->CreateNode("ewayOption3", $this->txOption3);
		$xml .= "</ewaygateway>";
         	return $xml;
      	}
   
      	/// <summary>
      	/// Builds a simple XML node.
      	/// </summary>
      	/// <param name="NodeName">The name of the node being created.</param>
      	/// <param name="NodeValue">The value of the node being created.</param>
      	/// <returns>An XML node as a string.</returns>
	function CreateNode($NodeName, $NodeValue)
        {
                $node = "<" . $NodeName . ">" . $NodeValue . "</" . $NodeName . ">";
                return $node;
        }

}

?>
