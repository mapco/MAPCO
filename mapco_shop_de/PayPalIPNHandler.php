<?php 

	include("config.php");
	include("functions/shop_mail_order.php");
require_once '../APIs/payments/PayPalConstants.php';


// Revision Notes
// 11/04/11 - changed post back url from https://www.paypal.com/cgi-bin/webscr to https://ipnpb.paypal.com/cgi-bin/webscr
// For more info see below:
// https://www.x.com/content/bulletin-ip-address-expansion-paypal-services
// "ACTION REQUIRED: if you are using IPN (Instant Payment Notification) for Order Management and your IPN listener script is behind a firewall that uses ACL (Access Control List) rules which restrict outbound traffic to a limited number of IP addresses, then you may need to do one of the following: 
// To continue posting back to https://www.paypal.com  to perform IPN validation you will need to update your firewall ACL to allow outbound access to *any* IP address for the servers that host your IPN script
// OR Alternatively, you will need to modify  your IPN script to post back IPNs to the newly created URL https://ipnpb.paypal.com using HTTPS (port 443) and update firewall ACL rules to allow outbound access to the ipnpb.paypal.com IP ranges (see end of message)."


/////////////////////////////////////////////////
/////////////Begin Script below./////////////////
/////////////////////////////////////////////////

// read the post from PayPal system and add 'cmd'
//ERHALTENE MESSAGE muss unveränder an PayPal zur Bestätigung zurückgesandt werden

extract($_POST);
//PAYPAL *******************************************************************************************************************************
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
		if (isset($charset) && $charset=="windows-1252") $nvp=iconv("windows-1252", "utf-8", $$nvp);
			
	
	$res=q("insert into testtable2 (text) values ('".mysql_real_escape_string($nvp, $dbshop)."');", $dbshop, __FILE__, __LINE__);
/*
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

	if (isset($for_auction) && $for_auction=="true")
	{
		//TRANSACTION FOR EBAY
		//EBAYDATEN ABGLEICHEN + AKTIONEN
			
			
		$res=q("INSERT INTO paypal_IPN (paypal_account_id, IPN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysql_real_escape_string($payment_status, $dbshop)."', '', '".mysql_real_escape_string($ebay_txn_id1, $dbshop)."', ".($mc_gross*1).", ".($mc_fee*1).", '".$ebay_accountName."', '".mysql_real_escape_string($auction_buyer_id, $dbshop)."', '".mysql_real_escape_string(urldecode($last_name), $dbshop)."', '".mysql_real_escape_string(urldecode($first_name), $dbshop)."', '".mysql_real_escape_string(urldecode($payer_email), $dbshop)."', '".mysql_real_escape_string($txn_id, $dbshop)."');", $dbshop, __FILE__, __LINE__);
	}
	elseif(isset($custom) && $custom=="Mapco Onlineshop")
	{
		$res=q("SELECT * FROM shop_orders where Payments_TransactionID = '".$txn_id."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res)>0) {
			$row=mysql_fetch_array($res);
			$id_order=$row["id_order"];
			$buyer_id=$row["customer_id"];
			$PayPal_TransactionState=$row["Payments_TransactionState"];
		}
		else {
			$id_order="xxxxxxxxx";
			$buyer_id="xxxxxxxxx";
			$PayPal_TransactionState="xxxxxxxxx";
		}
		
		$accountName="MAPCO_ONLINESHOP";
		
		$res=q("INSERT INTO paypal_IPN (paypal_account_id, IPN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysql_real_escape_string($payment_status, $dbshop)."', '', '".mysql_real_escape_string($id_order, $dbshop)."', '".($mc_gross*1)."', ".($mc_fee*1).", '".$accountName."', '".mysql_real_escape_string($buyer_id, $dbshop)."', '".mysql_real_escape_string(urldecode($last_name), $dbshop)."', '".mysql_real_escape_string(urldecode($first_name), $dbshop)."', '".mysql_real_escape_string(urldecode($payer_email), $dbshop)."', '".mysql_real_escape_string($txn_id, $dbshop)."');", $dbshop, __FILE__, __LINE__);	
		
		// CHECK OB ORDERMAIL gesendet werden muss
		if ($PayPal_TransactionState == "Pending" && $payment_status=="Completed")
		{
			mail_order($row["id_order"]);
		}
		
		//STATUSABGLEICH (IPN->ShopOrders)
		if ($PayPal_TransactionState!=$payment_status && $PayPal_TransactionState!="xxxxxxxxx")
		{ 
			$res=q("UPDATE shop_orders SET Payments_TransactionState = '".$payment_status."', Payments_TransactionStateDate = ".time()." WHERE id_order = ".$id_order.";", $dbshop, __FILE__, __LINE__);
		}
			
	}
	elseif(isset($custom) && $custom=="Autopartner Onlineshop")
	{
		$accountName="AUTOPARTNER_ONLINESHOP";
		$id_order="xxxxxxxxx";
		$buyer_id="xxxxxxxxx";


		$res=q("INSERT INTO paypal_IPN (paypal_account_id, IPN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysql_real_escape_string($payment_status, $dbshop)."', '', '".mysql_real_escape_string($id_order, $dbshop)."', '".($mc_gross*1)."', ".($mc_fee*1).", '".$accountName."', '".mysql_real_escape_string($buyer_id, $dbshop)."', '".mysql_real_escape_string(urldecode($last_name), $dbshop)."', '".mysql_real_escape_string(urldecode($first_name), $dbshop)."', '".mysql_real_escape_string(urldecode($payer_email), $dbshop)."', '".mysql_real_escape_string($txn_id, $dbshop)."');", $dbshop, __FILE__, __LINE__);	

	}
*/
} // PAYPAL

// PAYGENIC
if (isset($Userdata) && $Userdata == "paygenic")
{
	//Erhaltene Daten komplett speichern
	$nvp="";
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$nvp .= '&'.$key.'='.$value;
	}
	$res=q("insert into testtable (text) values ('".mysql_real_escape_string($nvp, $dbshop)."');", $dbshop, __FILE__, __LINE__);

	//Speichern in IPN Tabelle
	$res=q("INSERT INTO paypal_IPN (paypal_account_id, IPN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES (0, ".time().", 0, '".mysql_real_escape_string($Status, $dbshop)."', '".mysql_real_escape_string($Description, $dbshop)."', '".mysql_real_escape_string($TransID, $dbshop)."', '', '', 'MAPCO_ONLINESHOP', '', '', '', '', '".mysql_real_escape_string($XID, $dbshop)."');", $dbshop, __FILE__, __LINE__);	
}
?>