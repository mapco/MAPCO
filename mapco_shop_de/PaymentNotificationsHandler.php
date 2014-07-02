<?php 


	include("config.php");
	include("functions/shop_mail_order2.php");
require_once '../APIs/payments/PayPalConstants.php';


extract($_POST);
//******************************
//PAYPAL                       *
//******************************

$processed=false;

if (!isset($Userdata))
{

	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
	}
	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

	$fp = fsockopen (IPN_ENDPOINT, 443, $errno, $errstr, 30);
	if (!$fp) {
	// HTTP ERROR
	} else {
		fputs ($fp, $header . $req);
		while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0) {
			}
	
	    }
		fclose ($fp);
	}
	
	//Erhaltene Daten komplett speichern
	$nvp="";
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$nvp .= '&'.$key.'='.$value;
	}
	
	//Zeichensatzkovertierung
		if (isset($charset) && $charset=="windows-1252") 
		{
			$text=iconv("windows-1252", "utf-8", $nvp);
			$payment_status=iconv("windows-1252", "utf-8", $payment_status);
			$pending_reason=iconv("windows-1252", "utf-8", $pending_reason);
			$auction_buyer_id=iconv("windows-1252", "utf-8", $auction_buyer_id);
			$last_name=iconv("windows-1252", "utf-8", $last_name);
			$first_name=iconv("windows-1252", "utf-8", $first_name);
			$payer_email=iconv("windows-1252", "utf-8", $payer_email);
			
		//	$receiver_email=iconv("windows-1252", "utf-8", $receiver_email);
		//	$business==iconv("windows-1252", "utf-8", $business);
		}
		else $text=$nvp;

		if (!isset($receiver_email) || $receiver_email=="") $receiver_email=$business;
	
	$res=q("insert into testtable (text) values ('".mysqli_real_escape_string($dbshop, $text)."');", $dbshop, __FILE__, __LINE__);

	
	$res=q("insert into payment_notification_messages (message, date_recieved) values ('".mysqli_real_escape_string($dbshop, $text)."', ".time().");", $dbshop, __FILE__, __LINE__);
	
	$pnm_id=mysqli_insert_id($dbshop);

	


	//Zahldatum für Timestamp vorbereiten
		switch (substr($payment_date, 9,3)) {
			case "Jan": $month=1; break;
			case "Feb": $month=2; break;
			case "Mar": $month=3; break;
			case "Apr": $month=4; break;
			case "May": $month=5; break;
			case "Jun": $month=6; break;
			case "Jul": $month=7; break;
			case "Aug": $month=8; break;
			case "Sep": $month=9; break;
			case "Oct": $month=10; break;
			case "Nov": $month=11; break;
			case "Dec": $month=12; break;
		}
		$paymentdate=mktime(substr($payment_date, 0,2)*1, substr($payment_date, 3,2)*1, substr($payment_date, 6,2)*1, $month, substr($payment_date, 13,2)*1, substr($payment_date, 17)*1);
		
		if (!isset($pending_reason)) $pending_reason="";

		$res=q("SELECT * FROM paypal_accounts WHERE account_address = '".$receiver_email."';", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res)) {$accountID=$row["id_account"];}
		if ($accountID==1)
			{
				$ebay_accountName="EBAY_MAPCO";
			}
		if ($accountID==3) 
			{
				$ebay_accountName="EBAY_AP";
			}

	if (isset($for_auction) && ($for_auction=="true" || $for_auction=="TRUE"))
	{
		//TRANSACTION FOR EBAY
		$PAYMENTTYPE="PayPal";
	
		$res=q("INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $payment_status)."', '".mysqli_real_escape_string($dbshop, $pending_reason)."', '".mysqli_real_escape_string($dbshop, $ebay_txn_id1)."', ".($mc_gross*1).", ".($mc_fee*1).", '".$ebay_accountName."', '".mysqli_real_escape_string($dbshop, $auction_buyer_id)."', '".mysqli_real_escape_string($dbshop, $PAYMENTTYPE)."', '".mysqli_real_escape_string($dbshop, $last_name)."', '".mysqli_real_escape_string($dbshop, $first_name)."', '".mysqli_real_escape_string($dbshop, $payer_email)."', '".mysqli_real_escape_string($dbshop, $txn_id)."');", $dbshop, __FILE__, __LINE__);
		$processed=true;

	}
	elseif($accountID==1)
	//TRANSACTION FOR MAPCO_WEBSHOP
	{
		//RÜCKZAHLUNG
		if (isset($parent_txn_id) && isset($payment_status) && $payment_status=="Refunded")
		{
			$res=q("SELECT * FROM shop_orders where paymentTransactionID = '".$parent_txn_id."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res)>0) {
				$row=mysqli_fetch_array($res);
				$id_order=$row["id_order"];
				$buyer_id=$row["customer_id"];
				$PayPal_TransactionState=$row["Payments_TransactionState"];
			}
			else {
				$id_order="MOxxxxxxxxx";
				$buyer_id="MOxxxxxxxxx";
				$PayPal_TransactionState="MOxxxxxxxxx";
			}
			$accountName="MAPCO_ONLINESHOP_Rückzahlung";
		}
		else 
		{
			$res=q("SELECT * FROM shop_orders where Payments_TransactionID = '".$txn_id."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res)>0) {
				$row=mysqli_fetch_array($res);
				$id_order=$row["id_order"];
				$buyer_id=$row["customer_id"];
				$PayPal_TransactionState=$row["Payments_TransactionState"];
			}
			else {
				$id_order="MOxxxxxxxxx";
				$buyer_id="MOxxxxxxxxx";
				$PayPal_TransactionState="MOxxxxxxxxx";
			}
			if (isset($txn_type) && $txn_type=="send_money")
			{
				$accountName="MAPCO_Zahlungsaufforderung";
			}
			else 
			{
				$accountName="MAPCO_ONLINESHOP";
			}
		}
		$PAYMENTTYPE="PayPal";
		
			$res=q("INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $payment_status)."', '".mysqli_real_escape_string($dbshop, $pending_reason)."', '".mysqli_real_escape_string($dbshop, $id_order)."', ".($mc_gross*1).", ".($mc_fee*1).", '".$accountName."', '".mysqli_real_escape_string($dbshop, $buyer_id)."', '".mysqli_real_escape_string($dbshop, $PAYMENTTYPE)."', '".mysqli_real_escape_string($dbshop, $last_name)."', '".mysqli_real_escape_string($dbshop, $first_name)."', '".mysqli_real_escape_string($dbshop, $payer_email)."', '".mysqli_real_escape_string($dbshop, $txn_id)."');", $dbshop, __FILE__, __LINE__);
		$processed=true;

		
		// CHECK OB ORDERMAIL gesendet werden muss
		if ($PayPal_TransactionState == "Pending" && $payment_status=="Completed")
		{
			mail_order2($row["id_order"], false, true);
			
			//HERBSTAKTION
			$responseXml = post(PATH."soa2/", array("API" => "shop", "APIRequest" => "OrderDepositAdd", "order_id" => $row["id_order"]));
				
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				show_error(9756, 7, __FILE__, __LINE__, $responseXml, false);

			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if( $response->Ack[0]!="Success")
			{
				show_error(9781, 7, __FILE__, __LINE__, $responseXml, false);
			}

			
		}
		
		//STATUSABGLEICH (IPN->ShopOrders)
		if ($PayPal_TransactionState!=$payment_status && $PayPal_TransactionState!="xxxxxxxxx")
		{ 
			$res=q("UPDATE shop_orders SET Payment_TransactionState = '".$payment_status."', PaymentTransactionStateDate = ".time()." WHERE id_order = ".$id_order.";", $dbshop, __FILE__, __LINE__);
		}
			
	}
	
	elseif($accountID==3)
	{
		if (isset($txn_type) && $txn_type=="send_money")
		{
			$id_order="AZxxxxxxx";
			$buyer_id="AZxxxxxxx";
			$PayPal_TransactionState="AZxxxxxxxxx";
			$accountName="AUTOPARTNER_Zahlungsaufforderung";
		}
		else 
		{
			$accountName="AUTOPARTNER_ONLINESHOP";
			$varField["paymentTransactionID"]=$txn_id;
			$varField["API"]="payments";
			$varField["Action"]="PaymentNotificationGetOrder";
			if (strpos(PATH, "www")>0)
			{
				$responseXML=post("http://www.ihr-autopartner.de/soa/", $varField);
			}
			else 
			{
				$responseXML=post("http://localhost/AUTOPARTNER/AUTOPARTNER/soa/", $varField);
			}
	
			if (strpos($responseXML, "<Ack>Success</Ack>")>0)
			{
				$xml = new SimpleXMLElement($responseXML);
				
				$id_order=$xml->id_order[0];
				$buyer_id=$xml->costumer_id[0];
				$PayPal_TransactionState=$xml->Payments_TransactionState[0];
			}
			else 
			{
				$id_order="AOxxxxxxx";
				$buyer_id="AOxxxxxxx";
				$PayPal_TransactionState="AOxxxxxxxxx";
			}
		}
			$PAYMENTTYPE="PayPal";

		
		$res=q("INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $payment_status)."', '', '".mysqli_real_escape_string($dbshop, $id_order)."', '".($mc_gross*1)."', ".($mc_fee*1).", '".$accountName."', '".mysqli_real_escape_string($dbshop, $buyer_id)."', '".$PAYMENTTYPE."', '".mysqli_real_escape_string($dbshop, urldecode($last_name))."', '".mysqli_real_escape_string($dbshop, urldecode($first_name))."', '".mysqli_real_escape_string($dbshop, urldecode($payer_email))."', '".mysqli_real_escape_string($dbshop, $txn_id)."');", $dbshop, __FILE__, __LINE__);	
				$processed=true;


		//PAYMENTSTATUS ABGLEICHEN & ggf. ORDERMAIL SENDEN
		if ($PayPal_TransactionState!=$payment_status && $PayPal_TransactionState!="AOxxxxxxxxx")
		{
			$varField["paymentTransactionID"]=$txn_id;
			$varField["paymentState"]=$payment_status;
			$varField["API"]="payments";
			$varField["Action"]="PaymentNotificationUpdateOrder";

			if (strpos(PATH, "www")>0)
			{
				$responseXML=post("https://www.ihr-autopartner.de/soa/", $varField);
			}
			else 
			{
				$responseXML=post("http://localhost/AUTOPARTNER/AUTOPARTNER/soa/", $varField);
			}
		}
	
	}
if ($processed)	$res=q("UPDATE payment_notification_messages SET processed = 1 WHERE id = ".$pnm_id.";", $dbshop, __FILE__, __LINE__);

} // PAYPAL

//*******************************
// PAYGENIC
//*******************************
if (isset($Userdata))
{
	//PAYMENTTYPE
	switch ($PaymentType*1)
	{
		case 1: $PAYMENTTYPE="Kreditkarte"; break;	
		case 2: $PAYMENTTYPE="Lastschrift"; break;
		case 3: $PAYMENTTYPE="Sofortüberweisung"; break;				
		default: $PAYMENTTYPE=$PaymentType;
	}

	//ACCOUNT ID
	switch ($Userdata)
	{
		case "paygenic_mapco": $MerchantID="mapco_gmbh"; $PLATFORM="MAPCO_ONLINESHOP"; break;
		case "paygenic_autopartner": $MerchantID="autopartner_gmbh"; $PLATFORM="AUTOPARTNER_ONLINESHOP"; break;
	}
	$res=q("SELECT * FROM paygenic_accounts WHERE MerchantID_test = '".$MerchantID."';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	$ACCOUNTID=$row["id_account"];
	
	//TOTAL

	if ($Userdata=="paygenic_mapco")
	{
		$res=q("SELECT * FROM shop_orders WHERE Payments_TransactionID = '".$PayID."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)>0)
		{
			$row=mysqli_fetch_array($res);
			$id_order=$row["id_order"];
			$buyer_id=$row["customer_id"];
			if ($row["bill_lastname"]!="")
			{
				$buyer_lastname=$row["bill_lastname"];
				$buyer_firstname=$row["bill_firstname"];
			}
			else 
			{
				$buyer_lastname=$row["ship_lastname"];
				$buyer_firstname=$row["ship_firstname"];
			}
			$buyer_mail=$row["usermail"];
			$TOTAL=0;
			$Payments_TransactionStateDate=$row["Payments_TransactionStateDate"];
		}
		else
		{
			$id_order=$TransID;
			$buyer_id="MOyyyyyy";
			$buyer_lastname="MOyyyyyy";
			$buyer_firstname="MOyyyyyy";
			$buyer_mail="MOyyyyyy";
			$TOTAL=0;
			$Payments_TransactionStateDate=0;
		}
	}
	
	if ($Userdata=="paygenic_autopartner")
	{
		$varField["paymentTransactionID"]=$PayID;
		$varField["API"]="payments";
		$varField["Action"]="PaymentNotificationGetOrder";
		if (strpos(PATH, "www")>0)
		{
			$responseXML=post("https://www.ihr-autopartner.de/soa/", $varField);
		}
		else 
		{
			$responseXML=post("http://localhost/AUTOPARTNER/AUTOPARTNER/soa/", $varField);
		}

		if (strpos($responseXML, "<Ack>Success</Ack>")>0)
		{
			$xml = new SimpleXMLElement($responseXML);
			
			$id_order=$xml->id_order[0];
			$buyer_id=$xml->costumer_id[0];
			$buyer_lastname=$xml->buyer_lastname[0];
			$buyer_firstname=$xml->buyer_firstname[0];
			$buyer_mail=$xml->buyer_mail[0];
			$TOTAL=$xml->total[0];
			$Payments_TransactionStateDate=$xml->Payments_TransactionStateDate[0];
		}
		else 
		{
			$id_order=$TransID;
			$buyer_id="AOyyyyyy";
			$buyer_lastname="AOyyyyyy";
			$buyer_firstname="AOyyyyyy";
			$buyer_mail="AOyyyyyy";
			$TOTAL=0;
			$Payments_TransactionStateDate=0;
		}
	}

		
	
		//Erhaltene Daten komplett speichern
		$nvp="";
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$nvp .= '&'.$key.'='.$value;
		}
		$res=q("insert into payment_notification_messages (message, date_recieved, processed) values ('".mysqli_real_escape_string($dbshop, $nvp)."', ".time().", 0);", $dbshop, __FILE__, __LINE__);
		$pnm_id=mysqli_insert_id($dbshop);
		
	$res=q("insert into testtable (text) values ('".mysqli_real_escape_string($dbshop, $nvp)."');", $dbshop, __FILE__, __LINE__);

	
		//Speichern in PaymentNotifications Tabelle
		$res=q("INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES (".$ACCOUNTID.", ".time().", ".$Payments_TransactionStateDate.", '".mysqli_real_escape_string($dbshop, $Status)."', '".mysqli_real_escape_string($dbshop, $Description)."', '".mysqli_real_escape_string($dbshop, $id_order)."', '".$TOTAL."', '0', '".$PLATFORM."', '".$buyer_id."', '".$PAYMENTTYPE."','".$buyer_lastname."', '".$buyer_firstname."', '".$buyer_mail."', '".mysqli_real_escape_string($dbshop, $PayID)."');", $dbshop, __FILE__, __LINE__);
		
		$res=q("UPDATE payment_notification_messages SET processed = 1 WHERE id = ".$pnm_id.";", $dbshop, __FILE__, __LINE__);
	
}

/*TEST FOR ACCOUNTING SYSTEM*/

	define("PNM_Table", "payment_notification_messages4");
	define("PN_Table", "payment_notifications4");


	//ERHALTENE DATEN SPEICHERN
	$message="";
	$charset=$_POST["charset"];
	foreach ($_POST as $key => $value) 
	{
		//$value = urlencode(stripslashes($value));
		$value=iconv($charset, "utf-8", $value);
		
		if ($message!="") $message.='&';
		
		$message.= $key.'='.$value;
	}

	$ipn_track_id = $_POST["ipn_track_id"];

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
	else
	{
		$row_check=mysqli_fetch_assoc($res_check);
		$id = $row_check["id"];
	}
	$response="";
	$responseXML="";

	
	$postfields["API"]="payments";
	$postfields["APIRequest"]="PaymentsNotificationSet_PayPal";
	$postfields["usertoken"]="merci2664";
	//$postfields["ipn_track_id"]=$ipn_track_id;
	$postfields["id"]=$id;
//	$response=soa2($postfields);
	$responseXML=post(PATH."soa2/", $postfields);
//	mail ("nputzing@mapco.de", "IPNHandler-Aufruf", $responseXML.print_r($postfields,true));
	$use_errors = libxml_use_internal_errors(true);
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
	//	q_update(PNM_Table, $update_data, "id = ".$id, $dbshop, __FILE__, __LINE__);
		q("UPDATE payment_notification_messages4 SET processed = 1 WHERE id =".$id, $dbshop, __FILE__, __LINE__);
	}



include("../APIs/crm/PaymentNotificationsHandler2.php");
?>