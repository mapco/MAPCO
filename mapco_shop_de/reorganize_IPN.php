<?php

include("config.php");

$resIPN=q("SELECT * FROM testtable ORDER BY id;", $dbshop, __FILE__, __LINE__);



while ($rowIPN=mysqli_fetch_array($resIPN))
{
//print_r($varField);
//echo "<br />";
unset($for_auction);
unset($Userdata);
$payment_status="";
$payment_date=0;
$pending_reason="";
$auction_buyer_id="";
$last_name="";
$first_name="";
$payer_email="";
$accountID="";
$ebay_accountName="";
$month=0;
$paymentdate="";
$charset="";
$PAYMENTTYPE="";
$ebay_txn_id1="";
$mc_gross="";
$mc_fee="";
$txn_id="";
$ebay_accountName="";
$PayPal_TransactionState="";
$buyer_id="";
$id_order="";
$accountName="";

	$varField = array();
	$vars=explode('&', $rowIPN["text"]);
	for ($j=0; $j<sizeof($vars); $j++) {
		if($tmp=explode('=',trim($vars[$j])))	$varField[$tmp[0]]=$tmp[1];
	
	}

	extract($varField);
	
	if (isset($txn_id))
	{
		$res=q("SELECT * FROM paypal_IPN WHERE orderID = '".$ebay_txn_id1."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)==1)
		{
			$row=mysqli_fetch_array($res);
			$IPN_date=$row["IPN_date"];
		}
		else $IPN_date=$rowIPN["id"];
	}
	else $IPN_date=$rowIPN["id"];
	
//	echo $IPN_date.'<br />';
	
	if (isset($charset) && $charset=="windows-1252") $text=iconv("windows-1252", "utf-8", $rowIPN["text"]); else $text=$rowIPN["text"];
	
//	$res=q("INSERT INTO payment_notification_messages2 SET message = '".mysqli_real_escape_string($dbshop, $text)."', date_recieved =  ".$IPN_date.", processed = 0;", $dbshop, __FILE__, __LINE__);
	


	$pnm_id=mysqli_insert_id($dbshop);

$processed=false;
if (!isset($Userdata))
{

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
		
		
		//Zeichensatzkovertierung
		if (isset($charset) && $charset=="windows-1252") 
		{
			$text=iconv("windows-1252", "utf-8", $rowIPN["text"]);
			$payment_status=iconv("windows-1252", "utf-8", $payment_status);
			$pending_reason=iconv("windows-1252", "utf-8", $pending_reason);
			$auction_buyer_id=iconv("windows-1252", "utf-8", $auction_buyer_id);
			$last_name=iconv("windows-1252", "utf-8", $last_name);
			$first_name=iconv("windows-1252", "utf-8", $first_name);
			$payer_email=iconv("windows-1252", "utf-8", $payer_email);
			$business=iconv("windows-1252", "utf-8", $business);
		}
		else $text=$rowIPN["text"];
		 
		 
		if (!isset($pending_reason)) $pending_reason="";
$business=str_replace("%40","@",$business);

		$res=q("SELECT * FROM paypal_accounts WHERE account_address = '".$business."';", $dbshop, __FILE__, __LINE__);
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
	
		$res=q("INSERT INTO payment_notifications2 (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".$IPN_date.", ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $payment_status)."', '".mysqli_real_escape_string($dbshop, $pending_reason)."', '".mysqli_real_escape_string($dbshop, $ebay_txn_id1)."', ".($mc_gross*1).", ".($mc_fee*1).", '".$ebay_accountName."', '".mysqli_real_escape_string($dbshop, $auction_buyer_id)."', '".mysqli_real_escape_string($dbshop, $PAYMENTTYPE)."', '".mysqli_real_escape_string($dbshop, $last_name)."', '".mysqli_real_escape_string($dbshop, $first_name)."', '".mysqli_real_escape_string($dbshop, $payer_email)."', '".mysqli_real_escape_string($dbshop, $txn_id)."');", $dbshop, __FILE__, __LINE__);
	$processed=true;
	}
	elseif($accountID==1)
	//TRANSACTION FOR MAPCO_WEBSHOP
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
		$PAYMENTTYPE="PayPal";

		
		$res=q("INSERT INTO payment_notifications2 (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".$IPN_date.", ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $payment_status)."', '".mysqli_real_escape_string($dbshop, $pending_reason)."', '".mysqli_real_escape_string($dbshop, $id_order)."', ".($mc_gross*1).", ".($mc_fee*1).", '".$accountName."', '".mysqli_real_escape_string($dbshop, $buyer_id)."', '".mysqli_real_escape_string($dbshop, $PAYMENTTYPE)."', '".mysqli_real_escape_string($dbshop, $last_name)."', '".mysqli_real_escape_string($dbshop, $first_name)."', '".mysqli_real_escape_string($dbshop, $payer_email)."', '".mysqli_real_escape_string($dbshop, $txn_id)."');", $dbshop, __FILE__, __LINE__);
			$processed=true;

		// CHECK OB ORDERMAIL gesendet werden muss
		if ($PayPal_TransactionState == "Pending" && $payment_status=="Completed")
		{
			//mail_order2($row["id_order"], false, true);
		}
		
		//STATUSABGLEICH (IPN->ShopOrders)
		if ($PayPal_TransactionState!=$payment_status && $PayPal_TransactionState!="xxxxxxxxx")
		{ 
			//$res=q("UPDATE shop_orders SET Payment_TransactionState = '".$payment_status."', PaymentTransactionStateDate = ".time()." WHERE id_order = ".$id_order.";", $dbshop, __FILE__, __LINE__);
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
			$varField["usertoken"]="merci2664";
			$varField["paymentTransactionID"]=$txn_id;
	echo $txn_id.'<br />';
			$varField["API"]="payments";
			$varField["Action"]="PaymentNotificationGetOrder";
			if (strpos(PATH, "www")>0)
			{
				$responseXML=post("http://www.ihr-autopartner.de/soa/index.php", $varField);
			}
			else 
			{
				$responseXML=post("http://localhost/AUTOPARTNER/AUTOPARTNER/soa/index.php", $varField);
			}
	echo $responseXML."####<br />";
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

		
		$res=q("INSERT INTO payment_notifications2 (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".$IPN_date.", ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $payment_status)."', '', '".mysqli_real_escape_string($dbshop, $id_order)."', '".($mc_gross*1)."', ".($mc_fee*1).", '".$accountName."', '".mysqli_real_escape_string($dbshop, $buyer_id)."', '".$PAYMENTTYPE."' ,'".mysqli_real_escape_string($dbshop, urldecode($last_name))."', '".mysqli_real_escape_string($dbshop, urldecode($first_name))."', '".mysqli_real_escape_string($dbshop, urldecode($payer_email))."', '".mysqli_real_escape_string($dbshop, $txn_id)."');", $dbshop, __FILE__, __LINE__);	
			$processed=true;


	//	$res=q("UPDATE payment_notification_messages SET processed = 1 WHERE id = ".$pnm_id.";", $dbshop, __FILE__, __LINE__);

/*
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
		
		*/
	}

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if ($processed) $proc=1; else $proc=0;

	$res=q("INSERT INTO payment_notification_messages2 SET message = '".mysqli_real_escape_string($dbshop, $text)."', date_recieved =  ".$IPN_date.", processed = ".$proc.";", $dbshop, __FILE__, __LINE__);

	
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

		
/*	
		//Erhaltene Daten komplett speichern
		$nvp="";
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$nvp .= '&'.$key.'='.$value;
		}
		$res=q("insert into payment_notification_messages (message, date_recieved, processed) values ('".mysqli_real_escape_string($dbshop, $nvp)."', ".time().", 0);", $dbshop, __FILE__, __LINE__);
		$pnm_id=mysqli_insert_id($dbshop);
		
	$res=q("insert into testtable (text) values ('".mysqli_real_escape_string($dbshop, $nvp)."');", $dbshop, __FILE__, __LINE__);

	*/
		//Speichern in PaymentNotifications Tabelle
		$res=q("INSERT INTO payment_notifications2 (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES (".$ACCOUNTID.", ".time().", ".$Payments_TransactionStateDate.", '".mysqli_real_escape_string($dbshop, $Status)."', '".mysqli_real_escape_string($dbshop, $Description)."', '".mysqli_real_escape_string($dbshop, $id_order)."', '".$TOTAL."', '0', '".$PLATFORM."', '".$buyer_id."', '".$PAYMENTTYPE."','".$buyer_lastname."', '".$buyer_firstname."', '".$buyer_mail."', '".mysqli_real_escape_string($dbshop, $PayID)."');", $dbshop, __FILE__, __LINE__);
		
		//$res=q("UPDATE payment_notification_messages SET processed = 1 WHERE id = ".$pnm_id.";", $dbshop, __FILE__, __LINE__);
	//if ($processed) $proc=1; else $proc=0;

	$res=q("INSERT INTO payment_notification_messages2 SET message = '".mysqli_real_escape_string($dbshop, $text)."', date_recieved =  ".$IPN_date.", processed = 1;", $dbshop, __FILE__, __LINE__);
	
}

}
echo "FERTIG";

?>