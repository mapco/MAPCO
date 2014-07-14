<?php

	/*
		- SKRIPT prüft Zahltyp der Bestellung und führt Zahlungsaktionen durch
		- Zahlinformationnen werden in die Order geschrieben
		- Orderevent (OrderAdd) wird geschrieben
		- Buchung zur Order (OrderAdd) wird durchgeführt
		- bei erfolg wird abschließend der Orderstatus von 0 auf 1 oder 7 gesetzt
	
	*/
	 

	$order_id 					= $_SESSION["checkout_order_id"]; 
	$payment_response_from 		= $_SESSION["checkout"]["payment_response_from"];
	$paypal_token 				= $_SESSION["checkout"]["paypal_token"];
	$paypal_payerID 			= $_SESSION["checkout"]["paypal_payerID"];
	$payment_response_from 		= $_SESSION["checkout"]["payment_response_from"];
	$getvars2 					= $_SESSION["checkout"]["getvars2"];


/*
	HIER NOCH CHECK OB DIE SESSIONVARIABLEN GESETZT SIND
*/
	
 
	include("../../mapco_shop_de/functions/cms_tl.php");

	//GET ORDER DATA
	$postfield = array();
	$postfield["API"]				= "shop";
	$postfield["APIRequest"]		= "OrderDetailGet_neu_test";
	$postfield["OrderID"]			= $order_id;
	
	$order = soa3($postfield, __FILE__, __LINE__, "obj");
	
	if ( (string)$order->Ack[0] != "Success" )
	{
		$error_type = (string)$order->Ack[0];
		
		$error_code  = $order->xpath($error_type.'/Code');
		$errorID	 = $order->xpath($error_type.'/ErrorID');
		$errortext = "Service-Fehler. ErrorID: ".(int)$errorID[0]." ErrorCode: ".(int)$error_code[0]." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);

		//FRONEND FEHLERAUSGABE	
		$error = show_error(11357, 12, __FILE__, __LINE__, $errortext, true);
		//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
		$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
		$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
		service_exit();
	}

	//GET PAYMENT TYPE ID -> NEEDED FOR SELECTING PAYMENTPROCESSING BLOCK
	$paymenttype_id = (int)$order->Order[0]->payments_type_id[0];


//######################################################
// P A Y P A L   B L O C K
//######################################################
	
	if ( $paymenttype_id == 4 ) // PAYPAL
	{

		// CHECK IF CALLED FROM CHECKOUT OR PAYPAL
		if ( $payment_response_from == "paypal")
		{
			
			//RETURN FROM PAYPAL -> SUCCESS
			if ( $getvars2 == "success" )
			{	
				//***********************
				//PAYPALCHECKOUT_GET
				//***********************
				$postfield = array();
				$postfield["API"] 			= "paypal";
				$postfield["APIRequest"] 	= "PayPalExpressCheckoutGet";
				$postfield["order_id"] 		= $order_id;
				$postfield["paypal_token"] 	= $paypal_token;
				
				$response = soa3($postfield, __FILE__, __LINE__, "obj");
				
	
				//SERVICE RESPONSE CHECK
				if ( (string)$response->Ack[0] != "Success" )
				{
					$error_type = (string)$response->Ack[0];
					
					$error_code  = (int)$response->xpath($error_type.'/Code');
					$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
					
					//AUSWERTUNG DER PAYPALFEHLER
					if ( $error_code[0] == 11350) // PAYPAL-CURL FEHLER
					{
						$errortext = "PayPal Curl-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0];
						//FRONEND FEHLERAUSGABE		
						$error = show_error(11351, 12, __FILE__, __LINE__, $errortext, true);
					}
					else
					{
						$errortext = "PayPal Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0];
						//FRONEND FEHLERAUSGABE		
						$error = show_error(11355, 12, __FILE__, __LINE__, $errortext, true);
					}
					//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
					$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
					$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
					service_exit();

				}

				//PAYPAL RESPONSE CHECK
				$paypal_checkout_response = $response;
				//FEHLERBEHANDLUNG
				if ( (string)$paypal_checkout_response->ACK[0] == "Failure" || (string)$paypal_checkout_response->Ack[0] == "FailureWithWarning" )				{
					$paypal_errorcode = (int)$paypal_checkout_response->L_ERRORCODE0[0];
					$paypal_errorshort = (string)$paypal_checkout_response->L_SHORTMESSAGE0[0];
					$paypal_errorlong = (string)$paypal_checkout_response->L_LONGMESSAGE0[0];
					
					//SCHREIBE PAYPAL-FEHLER		
					$errortext = "Paypal-ErrorShort: ".$paypal_errorshort." PayPal-ErrorLong: ".$paypal_errorlong." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
					$error = show_error($paypal_errorcode, 13, __FILE__, __LINE__, $errortext, false);
					
					//FEHLERAUSGABE FRONTEND
					$errortext2 = "PayPal Fehler. ErrorID: ".$error[$error["Ack"]]["ErrorID"]." ErrorCode: ".$error[$error["Ack"]]["Code"];
					$error2 = show_error(11352, 12, __FILE__, __LINE__, $errortext2, true);
					//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
					$_SESSION["checkout"]["error_code"] = $error2[$error2["Ack"]]["Code"];
					$_SESSION["checkout"]["error_id"] 	= $error2[$error2["Ack"]]["ErrorID"];
					service_exit();
					
				}
				elseif ( (string)$paypal_checkout_response->ACK[0] == "SuccessWithWarning" )
				{
					$paypal_errorcode = (int)$paypal_checkout_response->L_ERRORCODE0[0];
					$paypal_errorshort = (string)$paypal_checkout_response->L_SHORTMESSAGE0[0];
					$paypal_errorlong = (string)$paypal_checkout_response->L_LONGMESSAGE0[0];
					
					//SCHREIBE PAYPAL-FEHLER		
					$errortext = "Paypal-ErrorShort: ".$paypal_errorshort." PayPal-ErrorLong: ".$paypal_errorlong." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
					$error = show_error($paypal_errorcode, 13, __FILE__, __LINE__, $errortext, false);
				}
				
				//CHECK IF TRANSACTION ALREADY COMPLETED 
				if ( (string)$paypal_checkout_response->CHECKOUTSTATUS[0] == "PaymentCompleted" ) 
				{
					$error2 = show_error(11356, 12, __FILE__, __LINE__, $errortext2, true);
					//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
					$_SESSION["checkout"]["error_code"] = $error2[$error2["Ack"]]["Code"];
					$_SESSION["checkout"]["error_id"] 	= $error2[$error2["Ack"]]["ErrorID"];
					service_exit();
				}
				//CHECK IF TRANSACTION ALREADY COMPLETED 
	/*
	if ( (string)$paypal_checkout_response->CHECKOUTSTATUS[0] == "PaymentActionFailed" ) 
	{
		$error2 = show_error(11356, 12, __FILE__, __LINE__, $errortext2, true);
		//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
		$_SESSION["checkout"]["error_code"] = $error2[$error2["Ack"]]["Code"];
		$_SESSION["checkout"]["error_id"] 	= $error2[$error2["Ack"]]["ErrorID"];
		service_exit();
	}
	*/
				//GET ADDRESS
				$address = array();
				$address["Name"]	 = (string)$paypal_checkout_response->PAYMENTREQUEST_0_SHIPTONAME[0];
				$address["Street1"] 	= (string)$paypal_checkout_response->PAYMENTREQUEST_0_SHIPTOSTREET[0];
				if ( isset($paypal_checkout_response->PAYMENTREQUEST_0_SHIPTOSTREET2[0]) )
				{
					$address["Street2"] = (string)$paypal_checkout_response->PAYMENTREQUEST_0_SHIPTOSTREET2[0];
				}
				else
				{
					$address["Street2"] = "";
				}
				$address["City"]	 = (string)$paypal_checkout_response->PAYMENTREQUEST_0_SHIPTOCITY[0];
				$address["ZIP"] = (string)$paypal_checkout_response->PAYMENTREQUEST_0_SHIPTOZIP[0];
				$address["Country"] = (string)$paypal_checkout_response->PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE[0];
				if ( isset($paypal_checkout_response->PAYMENTREQUEST_0_SHIPTOSTREET2[0]) )
				{
					$address["Phone"] = (string)$paypal_checkout_response->PAYMENTREQUEST_0_SHIPTOPHONENUM[0];
				}
				else
				{
					$address["Phone"] = "";
				}
				
				//MAKE ADDRESS UPDATE
					// CHECK IF SHIPPING ADDRESS IS BILL ADRESS
				//>>>>>>>>>>>>>
					//GET BUYER NOTE
				if ( isset($paypal_checkout_response->PAYMENTREQUEST_0_NOTETEXT[0]) )
				{
					$buyernote = (string)$paypal_checkout_response->PAYMENTREQUEST_0_NOTETEXT[0];
				}
				else
				{
					$buyernote = "";
				}
			
			
				//***************************
				// PAYPALCHECKOUT_DO
				//***************************
				
				$postfield = array();
				$postfield["API"]			= "paypal";
				$postfield["APIRequest"]	= "PayPalExpressCheckoutDo";
				$postfield["order_id"] 		= $order_id;
				$postfield["paypal_token"] 	= $paypal_token;
				$postfield["payerID"] 		= $paypal_payerID;
				
				$response = soa3($postfield, __FILE__, __LINE__, "obj");
				
					

				//SERVICE RESPONSE CHECK
				if ( (string)$response->Ack[0] != "Success" )
				{
					$error_type = (string)$response->Ack[0];
					
					$error_code  = (int)$response->xpath($error_type.'/Code');
					$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
					
					//AUSWERTUNG DER PAYPALFEHLER
					if ( $error_code[0] == 11350) // PAYPAL-CURL FEHLER
					{
						$errortext = "PayPal Curl-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0];
						//FRONEND FEHLERAUSGABE		
						$error = show_error(11351, 12, __FILE__, __LINE__, $errortext, false);
					}
					else
					{
						$errortext = "PayPal Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0];
						//FRONEND FEHLERAUSGABE		
						$error = show_error(11355, 12, __FILE__, __LINE__, $errortext, true);
					}
					//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
					$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
					$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
					service_exit();
				}
				
				//$paypal_checkout_response = $response->Response[0];
				$paypal_checkout_response = $response;
				if ( (string)$paypal_checkout_response->ACK[0] == "Failure" || (string)$paypal_checkout_response->ACK[0] == "FailureWithWarning" )				{
					$paypal_errorcode = (int)$paypal_checkout_response->L_ERRORCODE0[0];
					$paypal_errorshort = (string)$paypal_checkout_response->L_SHORTMESSAGE0[0];
					$paypal_errorlong = (string)$paypal_checkout_response->L_LONGMESSAGE0[0];
					
					//SCHREIBE PAYPAL-FEHLER		
					$errortext = "Paypal-ErrorShort: ".$paypal_errorshort." PayPal-ErrorLong: ".$paypal_errorlong." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
					$error = show_error($paypal_errorcode, 13, __FILE__, __LINE__, $errortext, false);
					
					//FEHLERAUSGABE FRONTEND
					$errortext2 = "PayPal Fehler. ErrorID: ".$error[$error["Ack"]]["ErrorID"]." ErrorCode: ".$error[$error["Ack"]]["Code"];
					$error2 = show_error(11352, 12, __FILE__, __LINE__, $errortext2, true);
					//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
					$_SESSION["checkout"]["error_code"] = $error2[$error2["Ack"]]["Code"];
					$_SESSION["checkout"]["error_id"] 	= $error2[$error2["Ack"]]["ErrorID"];
					service_exit();
					
				}
				elseif ( (string)$paypal_checkout_response->ACK[0] == "SuccessWithWarning" )
				{
					$paypal_errorcode = (int)$paypal_checkout_response->L_ERRORCODE0[0];
					$paypal_errorshort = (string)$paypal_checkout_response->L_SHORTMESSAGE0[0];
					$paypal_errorlong = (string)$paypal_checkout_response->L_LONGMESSAGE0[0];
					
					//SCHREIBE PAYPAL-FEHLER		
					$errortext = "Paypal-ErrorShort: ".$paypal_errorshort." PayPal-ErrorLong: ".$paypal_errorlong." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
					$error = show_error($paypal_errorcode, 13, __FILE__, __LINE__, $errortext, false);
				}
				
				
				// GET TRANSACTION ID
				$txn_id = (string)$paypal_checkout_response->PAYMENTINFO_0_TRANSACTIONID[0];
				
				// GET PAYMENTSTATUS
				$payment_state = (string)$paypal_checkout_response->PAYMENTINFO_0_PAYMENTSTATUS[0];
				
				if ( isset($paypal_checkout_response->PAYMENTINFO_0_PENDINGREASON[0]) )
				{
					$pending_reason = (string)$paypal_checkout_response->PAYMENTINFO_0_PENDINGREASON[0];
				}
				else
				{
					$pending_reason = "";
				}
				
				// SET Order_Status
				if ( $payment_state == "Completed" )
				{
					$status_id = 7;	
				}
				else
				{
					$status_id = 1;	
				}
				
				//UPDATE ORDER
				$postfield = array();
				$postfield["API"] = "shop";
				$postfield["APIRequest"] = "OrderUpdate";
				$postfield["mode"] = "shop";
				$postfield["SELECTOR_id_order"] = $order_id;
				$postfield["status_id"] = $status_id;
				$postfield["status_date"] = time();
				$postfield["Payments_TransactionID"] = $txn_id;
				$postfield["Payments_TransactionState"] = $payment_state;
				$postfield["PayPal_PendingReason"] = $pending_reason;
				$postfield["Payments_TransactionStateDate"] = time();
				$postfield["firstmod"] = time();
				$postfield["firstmod_user"] = $_SESSION["checkout_user_id"];

				if ( $buyernote != "")
				{
					$postfield["PayPal_BuyerNote"] = $buyernote;
				}
				
				$response = soa3($postfield, __FILE__, __LINE__, "obj");
				if ( (string)$response->Ack[0] != "Success" )
				{
					$error_type = (string)$response->Ack[0];
					
					$error_code  = (int)$response->xpath($error_type.'/Code');
					$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
					$errortext = "Service-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0]." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true)." Parameter-Serviceaufruf: ".print_r($postfield, true);
			
					//FRONEND FEHLERAUSGABE	
					$error = show_error(11357, 12, __FILE__, __LINE__, $errortext, true);
					//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
					$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
					$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
					service_exit();
				}
	
				//SET ORDEREVENT
					//GET ORDER
					$postfield = array();
					$postfield["API"] 			= "shop";
					$postfield["APIRequest"] 	= "OrderGet";
					$postfield["id_order"] 		= $order_id;
					
					$order_xml = soa3($postfield, __FILE__, __LINE__, "xml");
					
				$postfield = array();
				$postfield["API"] 			= "shop";
				$postfield["APIRequest"] 	= "OrderEventSet";
				$postfield["order_id"] 		= $order_id;
				$postfield["eventtype_id"] 	= 1;
				$postfield["data"] 			= $order_xml;
				
				$response = soa3($postfield, __FILE__, __LINE__, "obj");
				
				//SERVICE RESPONSE CHECK
				if ( (string)$response->Ack[0] != "Success" )
				{
					$error_type = (string)$response->Ack[0];
					
					$error_code  = (int)$response->xpath($error_type.'/Code');
					$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
					$errortext = "Service-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0]." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
			
				/*
				ab hier keinen fehler mehr ausgeben, da ZAHLUNG schon durch	
					//FRONEND FEHLERAUSGABE	
					$error = show_error(11357, 12, __FILE__, __LINE__, $errortext, true);
					//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
					$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
					$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
				*/
				//	service_exit();
				}
				
				//SET EVENT ID
				$event_id = (int)$response->id_event[0];
				
					
	
				//CALL PAYMENTNOTIFICATION HANDLER
				$postfield["API"] 			= "payments";
				$postfield["APIRequest"] 	= "PaymentNotificationHandler";
				$postfield["mode"] 			= "OrderAdd";
				$postfield["orderid"]		= $order_id;
				$postfield["order_event_id"]= $event_id;
				
				$response = soa3($postfield, __FILE__, __LINE__, "obj");
				
				//SERVICE RESPONSE CHECK
				if ( (string)$response->Ack[0] != "Success" )
				{
					$error_type	 = (string)$response->Ack[0];
					
					$error_code  = $response->xpath($error_type.'/Code');
					$errorID	 = $response->xpath($error_type.'/ErrorID');
					$errortext = "Service-Fehler. ErrorType: ".$error_type."/". (string)$response->Ack[0]."  ErrorID: ".(int)$errorID[0]." ErrorCode: ".(int)$error_code[0]." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
					/*
				ab hier keinen fehler mehr ausgeben, da order schon geschrieben	
		
					//FRONEND FEHLERAUSGABE	
					$error = show_error(11357, 12, __FILE__, __LINE__, $errortext, true);
					//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
					$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
					$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
					*/
					service_exit();
				}
				
				
				//IF EVERYTHING IS OK FINALY SET status_id FROM 0 TO 1 || 7
					// SET Order_Status
					if ( $payment_state == "Completed" )
					{
						$status_id = 7;	
					}
					else
					{
						$status_id = 1;	
					}
/*
				//UPDATE ORDER
				$postfield = array();
				$postfield["API"] = "shop";
				$postfield["APIRequest"] = "OrderUpdate";
				$postfield["mode"] = "shop";
				$postfield["SELECTOR_id_order"] = $order_id;
				$postfield["status_id"] = $status_id;
				$postfield["status_date"] = time();
				
				$response = soa3($postfield, __FILE__, __LINE__, "obj");
				if ( (string)$response->Ack[0] != "Success" )
				{
					$error_type = (string)$response->Ack[0];
					
					$error_code  = (int)$response->xpath($error_type.'/Code');
					$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
					$errortext = "Service-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0]." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
			
					//FRONEND FEHLERAUSGABE	
					$error = show_error(11357, 12, __FILE__, __LINE__, $errortext, true);
					//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
					$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
					$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
					service_exit();
				}
	*/			
			}
			
			elseif ( $getvars2 == "cancel" ) 
			//RETURN FROM PAYPAL -> CANCEL
			{
				$errortext = "SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
				
				//FRONEND FEHLERAUSGABE
				$error = show_error(11358, 12, __FILE__, __LINE__, $errortext, true);
				//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
				$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
				$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
				service_exit();
			}
			else
			{
			$errortext = "SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
			
			//FRONEND FEHLERAUSGABE
			$error = show_error(11358, 12, __FILE__, __LINE__, $errortext, true);
			//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
			$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
			$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
			service_exit();
			}
		}
		// PAYPAL EXPRESS CHECKOUT SET
		else
		{
			//echo "SET EXPRESS CHECKOUT";
			//***********************
			//PAYPALCHECKOUT_GET
			//***********************
			$postfield = array();
			$postfield["API"] 			= "paypal";
			$postfield["APIRequest"] 	= "PayPalExpressCheckoutSet";
			$postfield["order_id"] 		= $order_id;
			
		 	$response = soa3($postfield, __FILE__, __LINE__, "obj");


			//SERVICE RESPONSE CHECK
			if ( (string)$response->Ack[0] != "Success" )
			{
				$error_type = (string)$response->Ack[0];
				
				$error_code  = (int)$response->xpath($error_type.'/Code');
				$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
				
				//AUSWERTUNG DER PAYPALFEHLER
				if ( $error_code[0] == 11350) // PAYPAL-CURL FEHLER
				{
					$errortext = "PayPal Curl-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0];
					//FRONEND FEHLERAUSGABE		
					$error = show_error(11351, 12, __FILE__, __LINE__, $errortext, true);
				}
				else
				{
					$errortext = "PayPal Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0];
					//FRONEND FEHLERAUSGABE		
					$error = show_error(11355, 12, __FILE__, __LINE__, $errortext, true);
				}
				//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
				$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
				$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
				service_exit();
			}

			//PAYPAL RESPONSE CHECK
			$paypal_checkout_response = $response;
			if ( (string)$paypal_checkout_response->ACK[0] == "Failure" || (string)$paypal_checkout_response->ACK[0] == "FailureWithWarning" )
			{
				$paypal_errorcode = (int)$paypal_checkout_response->L_ERRORCODE0[0];
				$paypal_errorshort = (string)$paypal_checkout_response->L_SHORTMESSAGE0[0];
				$paypal_errorlong = (string)$paypal_checkout_response->L_LONGMESSAGE0[0];
				
				//SCHREIBE PAYPAL-FEHLER		
				$errortext = "Paypal-ErrorShort: ".$paypal_errorshort." PayPal-ErrorLong: ".$paypal_errorlong." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
				$error = show_error($paypal_errorcode, 13, __FILE__, __LINE__, $errortext, false);
				
				//FEHLERAUSGABE FRONTEND
				$errortext2 = "PayPal Fehler. ErrorID: ".$error[$error["Ack"]]["ErrorID"]." ErrorCode: ".$error[$error["Ack"]]["Code"];
				$error2 = show_error(11352, 12, __FILE__, __LINE__, $errortext2, true);
				//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
				$_SESSION["checkout"]["error_code"] = $error2[$error2["Ack"]]["Code"];
				$_SESSION["checkout"]["error_id"] 	= $error2[$error2["Ack"]]["ErrorID"];
				service_exit();
				
			}
			elseif ( (string)$paypal_checkout_response->ACK[0] == "SuccessWithWarning" )
			{
				$paypal_errorcode = (int)$paypal_checkout_response->L_ERRORCODE0[0];
				$paypal_errorshort = (string)$paypal_checkout_response->L_SHORTMESSAGE0[0];
				$paypal_errorlong = (string)$paypal_checkout_response->L_LONGMESSAGE0[0];
				
				//SCHREIBE PAYPAL-FEHLER		
				$errortext = "Paypal-ErrorShort: ".$paypal_errorshort." PayPal-ErrorLong: ".$paypal_errorlong." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
				$error = show_error($paypal_errorcode, 13, __FILE__, __LINE__, $errortext, false);
			}
			
	/*		
			echo "orderid:".$_SESSION['checkout_order_id']."<br />";
			print_r($paypal_checkout_response);
			exit;
		*/	
			$token = (string)$paypal_checkout_response->TOKEN[0];
			$paypal_url = (string)$paypal_checkout_response->paypal_url[0];
			
			echo "<paypal_redirect_url><![CDATA[".$paypal_url."]]></paypal_redirect_url>\n";;


		}
	}


//################################
//BLOCK FOR OTHER PAYMENTS
//################################

	if ( $paymenttype_id != 4 ) // ALL OTHER PAYMENTS
	{
		
		$status_id = 1;	
		$txn_id = "";
		$pending_reason = "";
		
		//SET PAYMENT STATUS
			// FÜR RECHNUNG UND NACHNAHME -> PENDING
			if ( $paymenttype_id == 1 || $paymenttype_id == 3 )
			{
				$payment_state = "Pending";
				$payment_state_time = time();
			}
			// ALLE ANDEREN ZAHLUNGEN -> CREATED
			else
			{
				$payment_state = "Created";
				$payment_state_time = 0;
			}
		
		//UPDATE ORDER
		$postfield = array();
		$postfield["API"] = "shop";
		$postfield["APIRequest"] = "OrderUpdate";
		$postfield["mode"] = "shop";
		$postfield["SELECTOR_id_order"] = $order_id;
		//$postfield["status_id"] = $status_id;
		//$postfield["status_date"] = time();
		$postfield["Payments_TransactionID"] = $txn_id;
		$postfield["Payments_TransactionState"] = $payment_state;
		$postfield["PayPal_PendingReason"] = $pending_reason;
		$postfield["Payments_TransactionStateDate"] = $payment_state_time;
		
		$response = soa3($postfield, __FILE__, __LINE__, "obj");
		if ( (string)$response->Ack[0] != "Success" )
		{
			$error_type = (string)$response->Ack[0];
			
			$error_code  = (int)$response->xpath($error_type.'/Code');
			$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
			$errortext = "Service-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0]." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
	
			//FRONEND FEHLERAUSGABE	
			$error = show_error(11357, 12, __FILE__, __LINE__, $errortext, true);
			//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
			$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
			$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
			service_exit();
		}

		//SET ORDEREVENT
			//GET ORDER
			$postfield = array();
			$postfield["API"] 			= "shop";
			$postfield["APIRequest"] 	= "OrderGet";
			$postfield["id_order"] 		= $_SESSION["checkout_order_id"];
			
			$order_xml = soa3($postfield, __FILE__, __LINE__, "xml");
			
		$postfield = array();
		$postfield["API"] 			= "shop";
		$postfield["APIRequest"] 	= "OrderEventSet";
		$postfield["order_id"] 		= $order_id;
		$postfield["eventtype_id"] 	= 1;
		$postfield["data"] 			= $order_xml;
		
		$response = soa3($postfield, __FILE__, __LINE__, "obj");
		
		//SERVICE RESPONSE CHECK
		if ( (string)$response->Ack[0] != "Success" )
		{
			$error_type = (string)$response->Ack[0];
			
			$error_code  = (int)$response->xpath($error_type.'/Code');
			$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
			$errortext = "Service-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0]." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
	
			//FRONEND FEHLERAUSGABE	
			$error = show_error(11357, 12, __FILE__, __LINE__, $errortext, true);
			//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
			$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
			$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
			service_exit();
		}
		
		//SET EVENT ID
		$event_id = (int)$response->id_event[0];
		
			

		//CALL PAYMENTNOTIFICATION HANDLER
		$postfield["API"] 			= "payments";
		$postfield["APIRequest"] 	= "PaymentNotificationHandler";
		$postfield["mode"] 			= "OrderAdd";
		$postfield["orderid"]		= $order_id;
		$postfield["order_event_id"]= $event_id;
		
		$response = soa3($postfield, __FILE__, __LINE__, "obj");
		
		//SERVICE RESPONSE CHECK
		if ( (string)$response->Ack[0] != "Success" )
		{
			$error_type = (string)$response->Ack[0];
			
			$error_code  = (int)$response->xpath($error_type.'/Code');
			$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
			$errortext = "Service-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0]." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
	
			//FRONEND FEHLERAUSGABE	
			$error = show_error(11357, 12, __FILE__, __LINE__, $errortext, true);
			//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
			$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
			$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
			service_exit();
		}
		
		//IF EVERYTHING IS OK FINALY SET status_id FROM 0 TO 1 || 7
			// SET Order_Status
			if ( $payment_state == "Completed" )
			{
				$status_id = 7;	
			}
			else
			{
				$status_id = 1;	
			}

		//UPDATE ORDER
		$postfield = array();
		$postfield["API"] = "shop";
		$postfield["APIRequest"] = "OrderUpdate";
		$postfield["mode"] = "shop";
		$postfield["SELECTOR_id_order"] = $order_id;
		$postfield["status_id"] = $status_id;
		$postfield["status_date"] = time();
		$postfield["firstmod"] = time();
		$postfield["firstmod_user"] = $_SESSION["checkout_user_id"];
		
		$response = soa3($postfield, __FILE__, __LINE__, "obj");
		if ( (string)$response->Ack[0] != "Success" )
		{
			$error_type = (string)$response->Ack[0];
			
			$error_code  = (int)$response->xpath($error_type.'/Code');
			$errorID	 = (int)$response->xpath($error_type.'/ErrorID');
			$errortext = "Service-Fehler. ErrorID: ".$errorID[0]." ErrorCode: ".$error_code[0]." SESSION-Variablen: ".print_r($_SESSION, true)." GET-Variablen: ".print_r($_GET, true);
	
			//FRONEND FEHLERAUSGABE	
			$error = show_error(11357, 12, __FILE__, __LINE__, $errortext, true);
			//ÜBERGABE AN SESSION FÜR AUSGABE IN CHECKOUT PROCESS
			$_SESSION["checkout"]["error_code"] = $error[$error["Ack"]]["Code"];
			$_SESSION["checkout"]["error_id"] 	= $error[$error["Ack"]]["ErrorID"];
			service_exit();
		}

	}



//======================================================================


?>