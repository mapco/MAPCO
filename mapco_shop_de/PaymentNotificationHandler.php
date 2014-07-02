<?php 


	include("config.php");
	include("functions/shop_mail_order2.php");
require_once '../APIs/payments/PayPalConstants.php';


extract($_GET);
//******************************
//PAYPAL                       *
//******************************

if (!isset($Userdata))
{
/*
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
*/	
	//Erhaltene Daten komplett speichern
	$nvp="";
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$nvp .= '&'.$key.'='.$value;
	}
	
//	$res=q("insert into testtable (text) values ('".mysql_real_escape_string($nvp, $dbshop)."');", $dbshop, __FILE__, __LINE__);

	
	$res=q("insert into payment_notification_messages (message, date_recieved) values ('".mysql_real_escape_string($nvp, $dbshop)."', ".time().");", $dbshop, __FILE__, __LINE__);
	


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

		$res=q("SELECT * FROM paypal_accounts WHERE account_address = '".$business."';", $dbshop, __FILE__, __LINE__);
		while ($row=mysql_fetch_array($res)) {$accountID=$row["id_account"];}
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
		//EBAYDATEN ABGLEICHEN + AKTIONEN
		$PAYMENTTYPE="PayPal";
			
			$res=q("INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysql_real_escape_string($payment_status, $dbshop)."', '".mysql_real_escape_string($pending_reason, $dbshop)."', '".mysql_real_escape_string($ebay_txn_id1, $dbshop)."', ".($mc_gross*1).", ".($mc_fee*1).", '".$ebay_accountName."', '".mysql_real_escape_string($auction_buyer_id, $dbshop)."', '".mysql_real_escape_string($PAYMENTTYPE, $dbshop)."', '".mysql_real_escape_string($last_name, $dbshop)."', '".mysql_real_escape_string($first_name, $dbshop)."', '".mysql_real_escape_string($payer_email, $dbshop)."', '".mysql_real_escape_string($txn_id, $dbshop)."');", $dbshop, __FILE__, __LINE__);
	}
	elseif($accountID==1)
	//TRANSACTION FOR MAPCO_WEBSHOP
	{
		$res=q("SELECT * FROM shop_orders where Payment_TransactionID = '".$txn_id."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res)>0) {
			$row=mysql_fetch_array($res);
			$id_order=$row["id_order"];
			$buyer_id=$row["customer_id"];
			$PayPal_TransactionState=$row["Payment_TransactionState"];
		}
		else {
			$id_order="MOxxxxxxxxx";
			$buyer_id="MOxxxxxxxxx";
			$PayPal_TransactionState="MOxxxxxxxxx";
		}
		
		$accountName="MAPCO_ONLINESHOP";
		
			$res=q("INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysql_real_escape_string($payment_status, $dbshop)."', '".mysql_real_escape_string($pending_reason, $dbshop)."', '".mysql_real_escape_string($id_order, $dbshop)."', ".($mc_gross*1).", ".($mc_fee*1).", '".$accountName."', '".mysql_real_escape_string($buyer_id, $dbshop)."', '".mysql_real_escape_string($PAYMENTTYPE, $dbshop)."', '".mysql_real_escape_string($last_name, $dbshop)."', '".mysql_real_escape_string($first_name, $dbshop)."', '".mysql_real_escape_string($payer_email, $dbshop)."', '".mysql_real_escape_string($txn_id, $dbshop)."');", $dbshop, __FILE__, __LINE__);
		
		// CHECK OB ORDERMAIL gesendet werden muss
		if ($PayPal_TransactionState == "Pending" && $payment_status=="Completed")
		{
			mail_order2($row["id_order"], false, true);
		}
		
		//STATUSABGLEICH (IPN->ShopOrders)
		if ($PayPal_TransactionState!=$payment_status && $PayPal_TransactionState!="xxxxxxxxx")
		{ 
			$res=q("UPDATE shop_orders SET Payment_TransactionState = '".$payment_status."', PaymentTransactionStateDate = ".time()." WHERE id_order = ".$id_order.";", $dbshop, __FILE__, __LINE__);
		}
			
	}
	
	elseif($accountID==3)
	{
		$accountName="AUTOPARTNER_ONLINESHOP";

		$varField["paymentTransactionID"]=$txn_id;
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
			$PayPal_TransactionState=$xml->Payments_TransactionState[0];
		}
		else 
		{
			$id_order="AOxxxxxxx";
			$buyer_id="AOxxxxxxx";
			$PayPal_TransactionState="AOxxxxxxxxx";
		}
		
		$res=q("INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysql_real_escape_string($payment_status, $dbshop)."', '', '".mysql_real_escape_string($id_order, $dbshop)."', '".($mc_gross*1)."', ".($mc_fee*1).", '".$accountName."', '".mysql_real_escape_string($buyer_id, $dbshop)."', '".mysql_real_escape_string(urldecode($last_name), $dbshop)."', '".mysql_real_escape_string(urldecode($first_name), $dbshop)."', '".mysql_real_escape_string(urldecode($payer_email), $dbshop)."', '".mysql_real_escape_string($txn_id, $dbshop)."');", $dbshop, __FILE__, __LINE__);	

		//PAYMENTSTATUS ABGLEICHEN & ggf. ORDERMAIL SENDEN
		if ($PayPal_TransactionState!=$payment_status && $PayPal_TransactionState!="xxxxxxxxx")
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
	$row=mysql_fetch_array($res);
	$ACCOUNTID=$row["id_account"];
	
	//TOTAL

	if ($Userdata=="paygenic_mapco")
	{
		$res=q("SELECT * FROM shop_orders WHERE Payments_TransactionID = '".$PayID."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res)>0)
		{
			$row=mysql_fetch_array($res);
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
		foreach ($_GET as $key => $value) {
			$value = urlencode(stripslashes($value));
			$nvp .= '&'.$key.'='.$value;
		}
		$res=q("insert into payment_notification_messages (message, date_recieved) values ('".mysql_real_escape_string($nvp, $dbshop)."', ".time().");", $dbshop, __FILE__, __LINE__);
		
	//$res=q("insert into testtable (text) values ('".mysql_real_escape_string($nvp, $dbshop)."');", $dbshop, __FILE__, __LINE__);

	
		//Speichern in PaymentNotifications Tabelle
		$res=q("INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES (".$ACCOUNTID.", ".time().", ".$Payments_TransactionStateDate.", '".mysql_real_escape_string($Status, $dbshop)."', '".mysql_real_escape_string($Description, $dbshop)."', '".mysql_real_escape_string($id_order, $dbshop)."', '".$TOTAL."', '0', '".$PLATFORM."', '".$buyer_id."', '".$PAYMENTTYPE."','".$buyer_lastname."', '".$buyer_firstname."', '".$buyer_mail."', '".mysql_real_escape_string($PayID, $dbshop)."');", $dbshop, __FILE__, __LINE__);	
	
}
?>