<?php

/**
 * @package ecommerce
 */
 
 /**
  * Handles the communication with DPS for payments
  */
class DPS {
	 
	/**
	 * Required XML fields for Px POST
	 *  Auth:  Authorizes a transactions. Must be completed within 7 days using the "Complete" TxnType.
	 *  Purchase:  Funds are transferred immediately
	 *  Complete:  Completes (settles) a pre-approved Auth Transaction. The DpsTxnRef value returned by the original approved Auth transaction must be supplied.
	 *  Refund: - Funds transferred immediately. Must be enabled as a special option.
	 *  Validate: Validation Transaction. Effects a $1.00 Auth to validate card details including expiry date 
	 *            Often utilised with the EnableAddBillCard property set to 1 to automatically add to Billing
	 *  		  Database if the transaction is approved.
	 */
	 static $requiredDetails = array(
		"Auth" => array("Amount", "CardHolderName", "CardNumber", "DateExpiry", "TxnType"),
		"Purchase" => array("Amount", "CardHolderName", "CardNumber", "DateExpiry", "TxnType"),
		"Complete" => array("DpsTxnRef", "TxnType"),
		"Status" => "TxnId",
		
		// not supported yet
		/**
		 * "Refund" => array(),
		 * "Validate" => array(),		 
		 */
		
	);
	
	/**
	 * Valid XML elements for PX POST
	 */
	public static $legalDetails = array(
		// Required
		"CardHolderName",
		"CardNumber", 
		"Amount", 				// Amount of transaction (dddddd.cc)
		"DateExpiry", 			//  Expiry Date on Card
		"TxnType", 				// Purchase', 'Auth', 'Complete', 'Refund', 'Validate'
		
		// Optional 
		"TxnId",				// Used for checking the status of a transaction
		"MerchantReference",	// Optional Reference to Appear on Transaction Reports Max 64 Characters
		"Cvc2", 				// Card Verification number. This number is found on the back of a credit card in the signature panel - it is different from the embossed card number and provides an additional safety check.
		"InputCurrency", 		// You will need to specify a currency here if you will be doing transactions in multiple currencies.
		"EnableAddBillCard", 	// Needed for recurring billing transactions when adding a card to the DPS system. Set element to 1 for true and 0 for false
		"DpsTxnRef",			// Output from an original transaction request. Is a required field to do second stage transactions like refund and completions.
		"BillingId", 			// Needs to be generated to add a card for recurring billing and sent again when rebilling transactions.
		"TxnData1", 			// Optional Free Text
		"TxnData2", 			// Optional Free Text
		"TxnData3", 			// Optional Free Text
	);
	
	public static $supportedCurrencies = array(
		"CAD" => "Canadian Dollar",
		"CHF"  => "Swiss Franc",
		"EUR"  => "Euro",
		"FRF"  => "French Franc",
		"GBP" => "United Kingdom Pound",
		"HKD"  => "Hong Kong Dollar",
		"JPY"  => "Japanese Yen",
		"NZD"  => "New Zealand Dollar",
		"SGD"  => "Singapore Dollar",
		"USD"  => "United States Dollar",
		"ZAR"  => "Rand",
		"AUD"  => "Australian Dollar",
		"WST"  => "Samoan Tala",
		"VUV"  => "Vanuatu Vatu",
		"TOP" => "Tongan Pa'anga",
		"SBD" => "Solomon Islands Dollar",
		"PGK" => "Papua New Guinea Kina",
		"MYR" => "Malaysian Ringgit",
		"KWD" => "Kuwaiti Dinar",
		"FJD" => "Fiji Dollar",
	);
	
	
	static function pxpost($details, $credentials) {
		// Allowed detail entries
		
		// Check credentials
		if(!$credentials[Username] || !$credentials[Password]) {
			user_error("dpsRequest_pxd not passed credentials", E_USER_ERROR);
	
			return array(
				"Success" => false,
				"Fatal" => true,
				"ResponseText" => "MISSING_CREDENTIALS",
				"HelpText" => "The server has been misconfigured",
				"MerchantHelpText" => "dpsRequest_pxd not passed credentials",
			);
		}
		
		// Check required details
		$requiredDetails = self::$requiredDetails; 
			
		foreach($requiredDetails[$details[TxnType]] as $k) {
			if(!$details[$k]) {
				return array(
					"Success" => false,
					"Fatal" => true,
					"ResponseText" => "MISSING_DETAILS",
					"HelpText" => "The server has been misconfigured",
					"MerchantHelpText" => "dpsRequest_pxd not required detail '$k'",
				);
			}
		}
		
		if($details[InputCurrency])
			if( !array_key_exists("$details[InputCurrency]",$this->supportedCurrencies) )
				USER_ERROR("DPS: Unsupported currency $details[InputCurrency]",E_USER_ERROR);
	
			
		// Build transaction XML
		$transactionXML = 
			"<Txn><PostUsername>$credentials[Username]</PostUsername>" .
			"<PostPassword>$credentials[Password]</PostPassword>";
			
		foreach($details as $k => $v) {
			// Illegal details are ignored
			if(in_array($k, self::$legalDetails)) {
				$v = htmlentities($v);
				$transactionXML .= "<$k>$v</$k>";
			}		
		}
	
		$transactionXML .= "</Txn>";
		
	 	// sean: trac #1221 this is important to use the following URL, any other ones are deprecated	
	 	$response = DPS::postTransaction("https://www.paymentexpress.com/pxpost.aspx", $transactionXML);
	 	
		if(!$response)
			return array(
				"Success" => 0,
				"ReCo" => "X1",
				"ResponseText" => "COMMUNICATIONS ERROR",
				"HelpText" => "There has been a communication error with the payment server",
			);
			
		$msg = new DPS_MifMessage($response);
		
		$success = $msg->get_element_text("Success");
		if($success === false) {
			// sean: #1221 -- perform some exception handling b/c we may have seen bad xml from DPS, but the trans  
			// may have gone through and been processed just fine.  

			// Build status-checking XML 
 			$statusXML =  
			"<Txn><PostUsername>$credentials[Username]</PostUsername>" . 
			"<PostPassword>$credentials[Password]</PostPassword>" . 
			"<TxnType>Status</TxnType>" . 
			"<TxnId>$details[TxnId]</TxnId>" . 
			"</Txn>"; 

			// sean: changed url below for #1221: very important to use the URL below as other URLs are deprecated 
			$statusResponse = DPS::postTransaction("https://www.paymentexpress.com/pxpost.aspx", $statusXML); 

			if(!$statusResponse) 
				return array( 
					"Success" => 0, 
					"ReCo" => "X1", 
					"ResponseText" => "COMMUNICATIONS ERROR", 
					"HelpText" => "There has been a communication error with the payment server", 
				); 

				$msg = new DPS_MifMessage($statusResponse); 
				// error_log("dps status response is ---" . $statusResponse . "---"); 
				$success = $msg->get_element_text("Success"); 
				if($success === false) { 
					// This situation below is the final (worst-case) situation. 
					mail("support@silverstripe.com", "URGENT CREDIT CARD ERROR!",  
						"The following response could not be decoded:\n\n$statusResponse"); 
					user_error("I'm sorry, but our credit card server is not currently working.  We will let you 
							know by e-mail as soon as it is back up and running",E_USER_ERROR); 
				} 
		}
		
		$reco           = $msg->get_element_text("ReCo");
		$responsetext   = $msg->get_element_text("ResponseText");
		$helptext       = $msg->get_element_text("HelpText");
		$datesettlement = $msg->get_element_text("Transaction/DateSettlement");
		if(ereg('([0-9]{4})([0-9]{2})([0-9]{2})',$datesettlement,$parts))
			$datesettlement = "$parts[1]-$parts[2]-$parts[3]";
		$txnref = $msg->get_element_text("TxnRef");
	
		$returnVal = array(
			"Success" => $success,
			"ReCo" => $reco,
			"ResponseText" => $responsetext,
			"HelpText" => $helptext,
			"TxnRef" => $txnref,
	
			"CardHolderResponseText" => $msg->get_element_text("CardHolderResponseText"),
			"CardHolderResponseDescription" => $msg->get_element_text("CardHolderResponseDescription"),
			"CardHolderHelpText" => $msg->get_element_text("CardHolderHelpText"),
			"MerchantResponseText" => $msg->get_element_text("MerchantResponseText"),
			"MerchantResponseDescription" => $msg->get_element_text("MerchantResponseDescription"),
			"MerchantHelpText" => $msg->get_element_text("MerchantHelpText"),
		);
		if ($success == "1") 
			$returnVal[DateSettlement] = $datesettlement;
		else {
			//echo "$transactionXML\n\n$response";
		}
	
		return $returnVal;
	}

	/*
	 * POSTs some data over HTTP(S) and retrieves the response
	 * Requires curl-ssl.
	 */
	private static function postTransaction($url, $data) {
		$curl = "/usr/bin/curl";	
		
		$data = addslashes($data);	
		$url = addslashes($url);
		
		if (file_exists($curl) == false) trigger_error("DPS cannot be contacted as curl is not installed ($curl was not found)");
		
		$command = "$curl -m 120 -d \"$data\" \"$url\" -L";
		exec($command, $postResponse, $return_var);
		$postResponse = implode ($postResponse, "\n");
		
		if ($return_var == 1)
			trigger_error(htmlentities("dps/postTransaction() curl installed at $curl does not support HTTPS. Please install 'curl-ssl'."), E_USER_ERROR);	
		elseif ($return_var == 6)
			trigger_error("dps/postTransaction() curl returned error (6) - could not contact payment server \"$url\".", E_USER_ERROR);		
		elseif ($return_var)
			trigger_error(htmlentities("dps/postTransaction() running curl returned error $return_var! see 'man curl' and look up this code.\n$command \n$postResponse"), E_USER_ERROR);
	
		return $postResponse;
	}
}


/*
 * DPS_MifMessage.
 * Use this class to parse a DPS PX MifMessage in XML form, and access the content.
 */
class DPS_MifMessage {
  var $xml_;
  var $xml_index_;
  var $xml_value_;

  /* 
   * Constructor:
   * Create a DPS MifMessage with the specified XML text.
   * The constructor returns a null object if there is a parsing error.
   */
	function __construct($xml) {
		$p = xml_parser_create();
		xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,0);
		$ok = xml_parse_into_struct($p, $xml, &$value, &$index);
		xml_parser_free($p);
		if ($ok) {
			$this->xml_ = $xml;
			$this->xml_value_ = $value;
			$this->xml_index_ = $index;
		}
	}

  /*
   * Return the value of the specified top-level attribute.
   * This method can only return attributes of the root element.
   * If the attribute is not found, return "".
   */
	function get_attribute($attribute){
		$attributes = $this->xml_value_[0]["attributes"];
		return $attributes[$attribute];
	}

	/*
	 * Return the text of the specified element.
	 * The element is given as a simplified XPath-like name.
	 * For example, "Link/ServerOk" refers to the ServerOk element
	 * nested in the Link element (nested in the root element).
	 * If the element is not found, return "".
	 */
	function get_element_text($element) {
		$index = $this->get_element_index($element, 0);
		if ($index == 0) {
			return false;
		} else {
			return $this->xml_value_[$index]["value"];
		}
	}

	/*
	 * (internal method)
	 * Return the index of the specified element,
	 * relative to some given root element index.
	 */
	function get_element_index($element, $rootindex = 0) {
		$pos = strpos($element, "/");
		if ($pos !== false) {
			// element contains '/': find first part
			$start_path = substr($element,0,$pos);
			$remain_path = substr($element,$pos+1);
			$index = $this->get_element_index($start_path, $rootindex);			
			if ($index == 0) {
				// couldn't find first part; give up.
				return 0;
			}
			// recursively find rest
			return $this->get_element_index($remain_path, $index);
		} else {
			// search from the parent across all its children
			// i.e. until we get the parent's close tag.
			$level = $this->xml_value_[$rootindex]["level"];
			if ($this->xml_value_[$rootindex]["type"] == "complete") {
				return 0;   // no children
			}
			$index = $rootindex+1;
			while ($index<count($this->xml_value_) && 
			!($this->xml_value_[$index]["level"]==$level && 
			$this->xml_value_[$index]["type"]=="close")) {
				// if one below parent and tag matches, bingo
				if ($this->xml_value_[$index]["level"] == $level+1 &&
				($this->xml_value_[$index]["type"] == "complete" || $this->xml_value_[$index]["type"] == "open") &&
				$this->xml_value_[$index]["tag"] == $element) {
       			return $index;
     			}
     			$index++;
   		}
   		return 0;
 		}
	}
}



?>