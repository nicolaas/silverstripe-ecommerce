<?php
/// <summary>
/// Summary description for GatewayConnector.
/// Copyright Web Active Corporation Pty Ltd  - All rights reserved. 1998-2004
/// This code is for exclusive use with the eWAY payment gateway
/// </summary>
//include_once('GatewayRequest.php');
//include_once('GatewayResponse.php');

class GatewayConnector {
      /// <summary>
      /// The Uri of the Eway payment gateway
      /// </summary>

      /// <summary>
      /// Do the post to the gateway and retrieve the response
      /// </summary>
      /// <param name="GatewayRequest"></param>
      /// <returns></returns>
     
	var $response = "";
	var $uri = "";
	var $timeout =36000;

	function GatewayConnector()
	{
		//change gateway in webconfig
	 	//$config =  simplexml_load_file('Web.config') or die ("Unable to load XMLfile!");
		//$this->uri = $config->appSettings->ewayGateway;
		$this->uri = EwayPayment::get_url();
	}

	// <summary>
        // The Uri of the Eway payment gateway
      	// </summary>
	function Uri($value)
	{
         	$this->uri = $value;       
      	}

      	function ConnectionTimeout($value)
      	{
         	$this->timeout = $value; 
      	}

      	function Response()
      	{
         	return $this->response;       

      	}

	// <summary>
      	// Do the post to the gateway and retrieve the response
      	// </summary>
      	// <param name="GatewayRequest"></param>
      	// <returns></returns>
	function ProcessRequest($request)
      	{
		$requestxml = $request->ToXML();

       		$ch = curl_init();

       		curl_setopt($ch, CURLOPT_URL,$this->uri);
       		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       		curl_setopt($ch, CURLOPT_TIMEOUT, 36000);
      		//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestxml); 
       		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		// Send the data out over the wire
		$data = curl_exec($ch);        
		if (curl_errno($ch)) {
			// Net connection failed
			// try and get the error text
           		print curl_error($ch);
			return false;
       		} 
		else {
           		curl_close($ch);
			// get the response
			$this->response = new GatewayResponse($data);
           		//return $data;	 
			return true;
		}

	}	

}


?>
