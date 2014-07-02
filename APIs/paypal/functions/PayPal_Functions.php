<?php

	function accountDataGet($params)
	{
		global $dbshop;
		/*
			PARAMS
				[shop_id]	= numeric => DEFAULT $_SESSION["id_shop"]
				[production] = 0||1 => 0 = Sandbox, 1 = productive System => DEFAULT FLAG FROM ACCOUNT DATA
		*/

		//SET PRODUCTION => FLAG FOR PRODUCTION SYSTEM DATA OR SANVOX DATA
			//CHECK IF FUNCTION CALL HAS FLAG
		if ( isset( $params["production"] ) && is_numeric( $params["production"] ) && in_array( $params["production"], array(0,1) ) )
		{
			$production = $params["production"];
		}
			//OTHERWISE GET FLAG FROM ACCOUNT DATA
		else
		{
			$production = $account_data["production"];
		}

		if ( isset( $params["shop_id"] ) && is_numeric( $params["shop_id"] ) )
		{
			$shop_id = $params["shop_id"];
		}
		else
		{
			$shop_id = $_SESSION["id_shop"];	
		}
	
	
		$res_account_data=q("SELECT * FROM paypal_accounts WHERE shop_id = ".$shop_id." LIMIT 1", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows( $res_account_data ) == 0 )
		{
			return false;	
		}
		
		$account_data = mysqli_fetch_assoc( $res_account_data );
		
			
		$function_return_data = array();
		
		if ( $production == 0)
		{
			$function_return_data["API_USERNAME"] 	= $account_data["sandbox_API_USER"];
			$function_return_data["API_PASSWORD"] 	= $account_data["sandbox_API_PW"];
			$function_return_data["API_SIGNATURE"] 	= $account_data["sandbox_Signature"];
			$function_return_data["API_ENDPOINT"] 	= "https://api-3t.sandbox.paypal.com/nvp";
			$function_return_data["PAYPAL_URL"] 	= "https://www.sandbox.paypal.com/webscr&amp;cmd=_express-checkout&amp;token=";
			$function_return_data["IPN_ENDPOINT"]	= "";
			$function_return_data["VERSION"]		= $account_data["Version"];
			$function_return_data["id_account"]		= $account_data["id_account"];
			
		}
		if ( $production == 1)
		{
			$function_return_data["API_USERNAME"] 	= $account_data["API_USER"];
			$function_return_data["API_PASSWORD"] 	= $account_data["API_PW"];
			$function_return_data["API_SIGNATURE"] 	= $account_data["Signature"];
			$function_return_data["API_ENDPOINT"] 	= "https://api-3t.paypal.com/nvp";
			$function_return_data["PAYPAL_URL"] 	= "https://www.paypal.com/webscr&amp;cmd=_express-checkout&amp;token=";
			$function_return_data["IPN_ENDPOINT"]	= $account_data["IPN_Endpoint"];
			$function_return_data["VERSION"]		= $account_data["Version"];
			$function_return_data["id_account"]		= $account_data["id_account"];
		}
		
		$function_return_data["USE_PROXY"]		= false;
		$function_return_data["PROXY_HOST"]		= "127.0.0.1";
		$function_return_data["PROXY_PORT"] 	= "808";

		
		return $function_return_data;

	} //END OF FUNCTION

	function deformatNVP($nvpstr)
	{
	
		$intial=0;
		$nvpArray = array();
	
	
		while(strlen($nvpstr)){
			//postion of Key
			$keypos= strpos($nvpstr,'=');
			//position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
	
			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
		 }
		return $nvpArray;
	}


	function send_request( $params )
	{
		
		global $dbshop;
		/*
		PARAMS
			[method] (REQUIRED) (string)= "SetExpressCheckout" || "GetExpressCheckoutDetails" || "DoExpressCheckoutPayment"
			[RequestData] (REQUIRED) (array) => NAME VALUE PAIRS containing data submitting to paypal
			[order_id] (REQUIRED) => for tracking call
			[shop_id]	 (int) => DEFAULT $_SESSION["id_shop"]
			[production] = 0||1 => 0 = Sandbox, 1 = productive System => DEFAULT FLAG FROM ACCOUNT DATA
		*/
		
		//CHECK FOR REQUIRED METHOD
		if ( !isset($params["method"]) )
		{
			return false;
		}
		//CHECK FOR METHOD IN "SetExpressCheckout" || "GetExpressCheckoutDetails" || "DoExpressCheckoutPayment"
		$methods = array();
		$methods[] = "SetExpressCheckout";
		$methods[] = "GetExpressCheckoutDetails";
		$methods[] = "DoExpressCheckoutPayment";
		if ( !in_array($params["method"], $methods ) )
		{
			$function_return_data["Ack"] 			= "Error";
			$function_return_data["ErrorCode"] 		= 11354;
			$function_return_data["ErrorMsg"] 		= "Falsche Methode für PayPal ExpressCheckout";
			$function_return_data["Errortext"] 		= "übergebene Methode: ".$params["method"]." Übergebene Parameter: ".print_r($params);
	
			return $function_return_data;

		}
		
		$method = $params["method"];
		
		// SET SHOP_ID
		if ( isset( $params["shop_id"] ) && is_numeric( $params["shop_id"] ) )
		{
			$shop_id = $params["shop_id"];
		}
		else
		{
			$shop_id = $_SESSION["id_shop"];	
		}

		if ( isset( $params["production"] ) && is_numeric( $params["production"] ) && in_array( $params["production"], array(0,1) ) )
		{
			$production = $params["production"];
		}
			//OTHERWISE GET FLAG FROM ACCOUNT DATA
		else
		{
			$production = $account_data["production"];
		}
		
		// GET PAYPAL ACCOUNT DATA
			//PARAM LIST
			$callparams = array();
			$callparams["shop_id"] 		= $shop_id;
			$callparams["production"] 	= $production;

			if ( !$accountdata = accountDataGet($callparams) )
			{
		
				$function_return_data["Ack"] 			= "Error";
				$function_return_data["ErrorCode"] 		= 11353;
				$function_return_data["ErrorMsg"] 		= "FEHLER BEI DER ERMITTLUNG DER PAYPAL ACCOUNTDATEN";
				$function_return_data["Errortext"] 		= print_r($params);
		
				return $function_return_data;
			}

		//CREATE REQUESTSTRING for submitting to paypal
		$nvp = "";
		
		$nvp .= "METHOD=".urlencode($method);
		$nvp .= "&VERSION=".urlencode($accountdata["VERSION"]);
		$nvp .= "&PWD=".urlencode($accountdata["API_PASSWORD"]);
		$nvp .= "&USER=".urlencode($accountdata["API_USERNAME"]);
		$nvp .= "&SIGNATURE=".urlencode($accountdata["API_SIGNATURE"]);
		
		foreach ( $params["RequestData"] as $name => $value )
		{
			$nvp .= "&".$name."=".urlencode($value);	
		}
		
		
		//TRACK PAYPAL CALL
		$data_field = array();
		$data_field["order_id"] = $params["order_id"];
		$data_field["paypal_account_id"] = $accountdata["id_account"];
		$data_field["method"] = $method;
		$data_field["paypal_request"] = urldecode( $nvp );
		$data_field["request_time"] = time();
		$data_field["production"] = $production;
		
		q_insert("paypal_tracking", $data_field, $dbshop, __FILE__, __LINE__);
		$paypal_tracking_id = mysqli_insert_id( $dbshop );
		
		//return $nvp;
		
		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$accountdata["API_ENDPOINT"]);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		//if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
		//Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
		if( $accountdata["USE_PROXY"] )
		{
			curl_setopt ($ch, $accountdata["CURLOPT_PROXY"], $accountdata["PROXY_HOST"].":".$accountdata["PROXY_PORT"]); 
		}
	
		//setting the nvp as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvp);
	
		//getting response from server
		$response = curl_exec($ch);


		$function_return_data = array();

		if ( curl_errno($ch) ) 
		{
			$function_return_data["Ack"] 			= "Error";
			$function_return_data["ErrorCode"] 		= 11350;
			$function_return_data["ErrorMsg"]		= "CURL ERROR";
			$function_return_data["Errortext"] 		= "Curl-ErrorNR: ".curl_errno($ch). " Curl-ErrorMsg: ".curl_error($ch);
			
			return $function_return_data;
		} 
		else 
		{
			//closing the curl
			curl_close($ch);
		}


		//convrting NVPResponse to an Associative Array
		$nvpResArray = deformatNVP($response);
		

		//TRACK PAYPAL CALL
		$data_field = array();
		$data_field["paypal_response"] = urldecode($response);
		$data_field["token"] =$nvpResArray["TOKEN"];
		$data_field["response_time"] = time();
		
		q_update("paypal_tracking", $data_field, "WHERE id = ".$paypal_tracking_id, $dbshop, __FILE__, __LINE__);


		$function_return_data["Ack"] 		= "Success";
		$function_return_data["Response"] 	= $nvpResArray;

		return $function_return_data;

	}

?>