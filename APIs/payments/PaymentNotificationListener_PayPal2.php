<?php
	define("PNM_Table", "payment_notification_messages4");

	//include("config.php");
	//include("functions/shop_mail_order2.php");
	//require_once '../APIs/payments/PayPalConstants.php';
	
	//FOR TESTING
	/*
		define('API_USERNAME', $row["sandbox_API_USER"]);
		define('API_PASSWORD', $row["sandbox_API_PW"]);
		define('API_SIGNATURE', $row["sandbox_Signature"]);
		define('API_ENDPOINT', 'https://api-3t.sandbox.paypal.com/nvp');
		define('PAYPAL_URL', 'https://www.sandbox.paypal.com/webscr&amp;cmd=_express-checkout&amp;token=');

		define('IPN_ENDPOINT', 'ssl://www.sandbox.paypal.com');
		
		define('VERSION', '63.0');
		define('USE_PROXY',FALSE);
		define('PROXY_HOST', '127.0.0.1');
		define('PROXY_PORT', '808');
		*/
	//FOR TESTING
	
	
	
	//ERHALTENE DATEN SPEICHERN
	$message="";
	$ipn_track_id="";
	$charset=$_POST["charset"];
	foreach ($_POST as $key => $value) 
	{
		//$value = urlencode(stripslashes($value));
		$value=iconv($charset, "utf-8", urldecode($value));
		
		if ($message!="") $message.='&';
		
		$message.= $key.'='.$value;
	}

	$ipn_track_id = $_POST["ipn_track_id"];
	
	//CHECK IF MESSAGE IS ALREADY KNOWN
	if ($ipn_track_id!="")
	{
		$res_check=q("SELECT * FROM ".PNM_Table." WHERE ipn_track_id = '".$ipn_track_id."'", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==0)
		{
	
			$insert_data=array();
			$insert_data["message"]=$message;
			$insert_data["date_received"]=time();
			$insert_data["processed"]=0;
			$insert_data["checked"]="unchecked";
			$insert_data["payment_type_id"]=4;
			$insert_data["ipn_track_id"]=$ipn_track_id;
		
			$res_insert = q_insert(PNM_Table, $insert_data, $dbshop, __FILE__, __LINE__);
		
			$id = mysqli_insert_id($dbshop);
		}
	}
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
	}
	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
/*
	$fp = fsockopen (IPN_ENDPOINT, 443, $errno, $errstr, 30);
	if (!$fp) 
	{
		// HTTP ERROR
		//q("INSERT INTO payment_notification_messages4 (message) VALUES ('NO CONNECTION')", $dbshop, __FILE__, __LINE__);

	} 
	else 
	{
	//q("INSERT INTO payment_notification_messages4 (message) VALUES ('CONNECTED')", $dbshop, __FILE__, __LINE__);

		$i=0;
		$tmp=fputs ($fp, $header . $req);
		if ($tmp)
		{
//			q("INSERT INTO payment_notification_messages4 (message) VALUES ('datasent:".$tmp."')", $dbshop, __FILE__, __LINE__);
		}
		else
		{
//			q("INSERT INTO payment_notification_messages4 (message) VALUES ('NO DATA SENT')", $dbshop, __FILE__, __LINE__);
		}
//		if (feof($fp)) q("INSERT INTO payment_notification_messages4 (message) VALUES ('NO DATA Recieved')", $dbshop, __FILE__, __LINE__);
//		if (!feof($fp)) q("INSERT INTO payment_notification_messages4 (message) VALUES ('DATA Recieved')", $dbshop, __FILE__, __LINE__);

		$verfired="";

		while (!feof($fp))
		{
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0) {
				$verfired="VERIFIED";
			}
			if (strcmp ($res, "INVALID") == 0) { $verfired="INVALID";}
			
	    }
		fclose ($fp);

		$update_data=array();
		$update_data["checked"]=$verfired;
		//q_update("payment_notification_messages4", $update_data, "ipn_track_id = '".$ipn_track_id."'", $dbshop, __FILE__, __LINE__);
		//q_update("payment_notification_messages4", $update_data, "id = ".$id, $dbshop, __FILE__, __LINE__);

		
		//CALL PAYMENTNOTIFICATION SET
	//	if ($verfired == "VERIFIED") 
		{
			$postfields["API"]="payments";
			$postfields["APIRequest"]="PaymentsNotificationSet_PayPal";
			$postfields["usertoken"]="merci2664";
			//$postfields["ipn_track_id"]=$ipn_track_id;
			$postfields["id"]=$id;
			$responseXML=post(PATH."soa2/", $postfields);
			
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				//XML FEHLERHAFT
				echo "XMLERROR".$responseXML;
				//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				//exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]=="Success")
			{
				$update_data=array();
				$update_data["processed"]=1;
				//q_update("payment_notification_messages4", $update_data, "ipn_track_id = '".$ipn_track_id."'", $dbshop, __FILE__, __LINE__);
				q_update("payment_notification_messages4", $update_data, "id = ".$id, $dbshop, __FILE__, __LINE__);
			}
	
//	echo $responseXML;
	
	*/
	if ($ipn_track_id!="")
	{
			$postfields["API"]="payments";
			$postfields["APIRequest"]="PaymentsNotificationSet_PayPal";
			$postfields["usertoken"]="merci2664";
			//$postfields["ipn_track_id"]=$ipn_track_id;
			$postfields["id"]=$id;
			$responseXML=post(PATH."soa2/", $postfields);
			
			try
			{
//				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				//XML FEHLERHAFT
				echo "XMLERROR".$responseXML;
				//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				//exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]=="Success")
			{
				$update_data=array();
				$update_data["processed"]=1;
				//q_update("payment_notification_messages4", $update_data, "ipn_track_id = '".$ipn_track_id."'", $dbshop, __FILE__, __LINE__);
				//q_update("payment_notification_messages4", $update_data, "id = ".$id, $dbshop, __FILE__, __LINE__);
				q("UPDATE ".PNM_Table." SET processed = 1 WHERE id = ".$id, $dbshop, __FILE__ , __LINE__);
			}

		}
//	}


?>