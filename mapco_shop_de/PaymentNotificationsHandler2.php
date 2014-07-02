<?php 
session_start();

	include("config.php");
	include("functions/shop_mail_order2.php");
require_once '../APIs/payments/PayPalConstants.php';


extract($_GET);
//******************************
//PAYPAL                       *
//******************************

$processed=false;

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
/*
	$nvp="";
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$nvp .= '&'.$key.'='.$value;
	}
*/	
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
	

//	$res=q("insert into payment_notification_messages (message, date_recieved) values ('".mysql_real_escape_string($text, $dbshop)."', ".time().");", $dbshop, __FILE__, __LINE__);

	$processed=false;
	
//	$pnm_id=mysql_insert_id($dbshop);

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
		while ($row=mysql_fetch_array($res)) {$accountID=$row["id_account"];}

		$PAYMENTTYPE="PayPal";
		
		
//**************************************************************************
// PAYMENTSTATUS COMPLETED
//**************************************************************************

	if ($payment_status=="Completed")
	{
		//FOR EBAY TRANSACTION
		
		if (isset($for_auction) && ($for_auction=="true" || $for_auction=="TRUE"))
		{
			if ($accountID==1)
			{
				$accountName="EBAY_MAPCO";
			}
			if ($accountID==3) 
			{
				$accountName="EBAY_AP";
			}
			
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_ebay_orderItem=q("SELECT * FROM ebay_orders_items2 WHERE TransactionID = '".$ebay_txn_id1."';", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_ebay_orderItem)==1)
			{
				//Search for Ebay_orderID
				$ebay_orderItem=mysql_fetch_array($res_ebay_orderItem);
				$res_ebay_order=q("SELECT * FROM ebay_orders2 WHERE OrderID = '".$ebay_orderItem["OrderID"]."';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($ebay_orderItem)==1)
				{
					$ebay_order=mysql_fetch_array($res_ebay_order);
					//Search for Shop_order_id
					$res_shop_order=q("SELECT * FROM shop_orders_crm WHERE foreign_order_id = '".$ebay_order["id_order"].";", $dbshop, __FILE__, __LINE__);
					if (mysql_num_rows($res_shop_order)==1)
					{
						$shop_order=mysql_fetch_array($res_shop_order);
						$shop_orderID=$shop_order["id_order"];
					}
					else 
					{	$shop_orderID=0;}
				}
				else 
				{	$shop_orderID=0;}
			}
			else 
			{	$shop_orderID=0;}
			
			$orderTransactionID=$ebay_txn_id1;
			$buyer_id=$auction_buyer_id;
				

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		
		if (!isset($for_auction) && isset($custom) && $txn_type!="send_money")
		{
			if ($accountID==1)
			{
				$accountName="MAPCO_ONLINESHOP";
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP";
			}
			
			$res_shop_order=q("SELECT * FROM shop_orders where Payments_TransactionID = '".$txn_id."';", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_shop_order)==1) 
			{
				$shop_order=mysql_fetch_array($res_shop_order);
				$shop_orderID=$shop_order["id_order"];
				$buyer_id=$shop_order["customer_id"];
				$shop_order_Payments_TransactionState=$row["Payments_TransactionState"];
			}
			else 
			{
				$shop_orderID=0;
				$buyer_id="";
				$shop_order_Payments_TransactionState="";
			}
			
			$orderTransactionID=$id_order;

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
			//>>>>>>>>>>>>>>>>>>>>>>> UPDATE ORDEREVENTS && SHOPORDER PAYMENTSTATUS && SEND ORDER MAIL
		}
		if (!isset($for_auction) && $txn_type=="send_money")
		{
			if ($accountID==1)
			{
				$accountName="MAPCO_ONLINESHOP_Zahlungsaufforderung";
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP_Zahlungsaufforderung";
			}
			
			$orderTransactionID="";
			$shop_orderID=0;
			$buyer_id="";

			//FLAG FOR Payment_notification_messages
			$processed=true;	
		}

	} // IF $payment_status=="Completed"

//**************************************************************************
// PAYMENTSTATUS PENDING
//**************************************************************************


	if ($payment_status=="Pending")
	{
		//FOR EBAY TRANSACTION
		
		if (isset($for_auction) && ($for_auction=="true" || $for_auction=="TRUE"))
		{
			if ($accountID==1)
			{
				$accountName="EBAY_MAPCO";
			}
			if ($accountID==3) 
			{
				$accountName="EBAY_AP";
			}
			
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_ebay_orderItem=q("SELECT * FROM ebay_orders_items2 WHERE TransactionID = '".$ebay_txn_id1."';", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_ebay_orderItem)==1)
			{
				//Search for Ebay_orderID
				$ebay_orderItem=mysql_fetch_array($res_ebay_orderItem);
				$res_ebay_order=q("SELECT * FROM ebay_orders2 WHERE OrderID = '".$ebay_orderItem["OrderID"]."';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($ebay_orderItem)==1)
				{
					$ebay_order=mysql_fetch_array($res_ebay_order);
					//Search for Shop_order_id
					$res_shop_order=q("SELECT * FROM shop_orders_crm WHERE foreign_order_id = '".$ebay_order["id_order"].";", $dbshop, __FILE__, __LINE__);
					if (mysql_num_rows($res_shop_order)==1)
					{
						$shop_order=mysql_fetch_array($res_shop_order);
						$shop_orderID=$shop_order["id_order"];
					}
					else 
					{	$shop_orderID=0;}
				}
				else 
				{	$shop_orderID=0;}
			}
			else 
			{	$shop_orderID=0;}
			
			$orderTransactionID=$ebay_txn_id1;
			$buyer_id=$auction_buyer_id;

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		
		if (!isset($for_auction) && isset($custom) && $txn_type!="send_money")
		{
			if ($accountID==1)
			{
				$accountName="MAPCO_ONLINESHOP";
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP";
			}
			
			$res_shop_order=q("SELECT * FROM shop_orders where Payments_TransactionID = '".$txn_id."';", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_shop_order)==1) 
			{
				$shop_order=mysql_fetch_array($res_shop_order);
				$shop_orderID=$shop_order["id_order"];
				$buyer_id=$shop_order["customer_id"];
				$shop_order_Payments_TransactionState=$row["Payments_TransactionState"];
			}
			else 
			{
				$shop_orderID=0;
				$buyer_id="";
				$shop_order_Payments_TransactionState="";
			}
			
			$orderTransactionID=$shop_orderID;

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
			//>>>>>>>>>>>>>>>>>>>>>>> UPDATE ORDEREVENTS && SHOPORDER PAYMENTSTATUS && SEND ORDER MAIL
		}
		
		if (!isset($for_auction) && $txn_type=="send_money")
		{
			if ($accountID==1)
			{
				$accountName="MAPCO_ONLINESHOP_Zahlungsaufforderung";
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP_Zahlungsaufforderung";
			}

			$orderTransactionID="";
			$shop_orderID=0;
			$buyer_id="";

			//FLAG FOR Payment_notification_messages
			$processed=true;	
		}


	} // IF $payment_status=="Pending"


//**************************************************************************
// PAYMENTSTATUS CREATED
//**************************************************************************


	if ($payment_status=="Created")
	{
		//FOR EBAY TRANSACTION
		
		if (isset($for_auction) && ($for_auction=="true" || $for_auction=="TRUE"))
		{
			if ($accountID==1)
			{
				$accountName="EBAY_MAPCO";
			}
			if ($accountID==3) 
			{
				$accountName="EBAY_AP";
			}
			
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_ebay_orderItem=q("SELECT * FROM ebay_orders_items2 WHERE TransactionID = '".$ebay_txn_id1."';", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_ebay_orderItem)==1)
			{
				//Search for Ebay_orderID
				$ebay_orderItem=mysql_fetch_array($res_ebay_orderItem);
				$res_ebay_order=q("SELECT * FROM ebay_orders2 WHERE OrderID = '".$ebay_orderItem["OrderID"]."';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($ebay_orderItem)==1)
				{
					$ebay_order=mysql_fetch_array($res_ebay_order);
					//Search for Shop_order_id
					$res_shop_order=q("SELECT * FROM shop_orders_crm WHERE foreign_order_id = '".$ebay_order["id_order"].";", $dbshop, __FILE__, __LINE__);
					if (mysql_num_rows($res_shop_order)==1)
					{
						$shop_order=mysql_fetch_array($res_shop_order);
						$shop_orderID=$shop_order["id_order"];
					}
					else 
					{	$shop_orderID=0;}
				}
				else 
				{	$shop_orderID=0;}
			}
			else 
			{	$shop_orderID=0;}
			
			$orderTransactionID=$ebay_txn_id1;
			$buyer_id=$auction_buyer_id;

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		} // EBAY +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		if (!isset($for_auction) && isset($custom) && $txn_type!="send_money")
		{
			if ($accountID==1)
			{
				$accountName="MAPCO_ONLINESHOP";
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP";
			}
			
			$res_shop_order=q("SELECT * FROM shop_orders where Payments_TransactionID = '".$txn_id."';", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_shop_order)==1) 
			{
				$shop_order=mysql_fetch_array($res_shop_order);
				$shop_orderID=$shop_order["id_order"];
				$buyer_id=$shop_order["customer_id"];
				$shop_order_Payments_TransactionState=$row["Payments_TransactionState"];
			}
			else 
			{
				$id_order=0;
				$buyer_id="";
				$shop_order_Payments_TransactionState="";
			}
			
			$orderTransactionID=$id_order;

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
			//>>>>>>>>>>>>>>>>>>>>>>> UPDATE ORDEREVENTS && SHOPORDER PAYMENTSTATUS && SEND ORDER MAIL
		}
		
		if (!isset($for_auction) && $txn_type=="send_money")
		{
			if ($accountID==1)
			{
				$accountName="MAPCO_ONLINESHOP_Zahlungsaufforderung";
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP_Zahlungsaufforderung";
			}

			$orderTransactionID="";
			$shop_orderID=0;
			$buyer_id="";

			//FLAG FOR Payment_notification_messages
			$processed=true;	
		}

	} // IF $payment_status=="CREATED"
	

//**************************************************************************
// PAYMENTSTATUS Refunded
//**************************************************************************

	if ($payment_status=="Refunded")
	{
		//GET PARENT TRANSACTION
		$res_parent=q("SELECT * FROM payment_notifications2 WHERE paymentTransactionID = '".$parent_txn_id."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res_parent)==1)
		{
			$parent_paymentTransaction=mysql_fetch_array($res_parent);
			
			$payment_account_id=$parent_paymentTransaction["payment_account_id"];
			$orderTransactionID=$parent_paymentTransaction["orderTransactionID"];
			$shop_orderID=$parent_paymentTransaction["shop_orderID"];
			$accountName=$parent_paymentTransaction["platform"];
			
			//UPDATE ORDEREVENTS && SHOPORDER PAYMENTSTATUS

		}
		else
		{
			//mail("", "", "ERROR - PaymentNotification", "Zu Refund konnte keine PARENT TRANSACTION gefunden werden")
			$payment_account_id=$accountID;
			$orderTransactionID="";
			$shop_orderID=0;
			$accountName="";
		}
		//FLAG FOR Payment_notification_messages
		$processed=true;	

	}
	
//**************************************************************************
// PAYMENTSTATUS Canceled_Reversal
//**************************************************************************

	if ($payment_status=="Canceled_Reversal")
	{
		//GET PARENT TRANSACTION
		$res_parent=q("SELECT * FROM payment_notifications2 WHERE paymentTransactionID = '".$parent_txn_id."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res_parent)==1)
		{
			$parent_paymentTransaction=mysql_fetch_array($res_parent);
			
			$payment_account_id=$parent_paymentTransaction["payment_account_id"];
			$orderTransactionID=$parent_paymentTransaction["orderTransactionID"];
			$shop_orderID=$parent_paymentTransaction["shop_orderID"];
			$accountName=$parent_paymentTransaction["platform"];
			
			//UPDATE ORDEREVENTS && SHOPORDER PAYMENTSTATUS

		}
		else
		{
			//mail("", "", "ERROR - PaymentNotification", "Zu Refund konnte keine PARENT TRANSACTION gefunden werden")
			$payment_account_id=$accountID;
			$orderTransactionID="";
			$shop_orderID=0;
			$accountName="";
		}
		//FLAG FOR Payment_notification_messages
		$processed=true;	

	}


//**************************************************************************
// PAYMENTSTATUS Reversed
//**************************************************************************

	if ($payment_status=="Reversed")
	{
		//GET PARENT TRANSACTION
		$res_parent=q("SELECT * FROM payment_notifications2 WHERE paymentTransactionID = '".$parent_txn_id."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res_parent)==1)
		{
			$parent_paymentTransaction=mysql_fetch_array($res_parent);
			
			$payment_account_id=$parent_paymentTransaction["payment_account_id"];
			$orderTransactionID=$parent_paymentTransaction["orderTransactionID"];
			$shop_orderID=$parent_paymentTransaction["shop_orderID"];
			$accountName=$parent_paymentTransaction["platform"];
			
			//UPDATE ORDEREVENTS && SHOPORDER PAYMENTSTATUS

		}
		else
		{
			//mail("", "", "ERROR - PaymentNotification", "Zu Refund konnte keine PARENT TRANSACTION gefunden werden")
			$payment_account_id=$accountID;
			$orderTransactionID="";
			$shop_orderID=0;
			$accountName="";
		}
		//FLAG FOR Payment_notification_messages
		$processed=true;	

	}
	
	if ($processed)
	{
		$sql ="INSERT INTO payment_notifications2 (".
		$sql.="payment_account_id, ";
		$sql2 ="'".$accountID."', ";
		$sql.="PN_date, ";
		$sql2.=time().", ";
		$sql.="payment_date, ";
		$sql2.=$paymentdate.", ";
		$sql.="state, ";
		$sql2.="'".mysql_real_escape_string($payment_status, $dbshop)."', ";
		$sql.="state_reason, ";
		$sql2.="'".mysql_real_escape_string($pending_reason, $dbshop)."', ";
		$sql.="orderTransactionID, ";
		$sql2.="'".mysql_real_escape_string($orderTransactionID, $dbshop)."', ";
		$sql.="shop_orderID, ";
		$sql2.=$shop_orderID.", ";
		$sql.="total, ";
		$sql2.=($mc_gross*1).", ";
		$sql.="fee, ";
		$sql2.=($mc_fee*1).", ";
		$sql.="platform, ";
		$sql2.="'".$accountName."', ";
		$sql.="buyer_id, ";
		$sql2.="'".mysql_real_escape_string($buyer_id, $dbshop)."', ";
		$sql.="payment_type, ";
		$sql2.="'".mysql_real_escape_string($PAYMENTTYPE, $dbshop)."', ";
		$sql.="buyer_lastname, ";
		$sql2.="'".mysql_real_escape_string($last_name, $dbshop)."', ";
		$sql.="buyer_firstname, ";
		$sql2.="'".mysql_real_escape_string($first_name, $dbshop)."', ";
		$sql.="buyer_mail, ";
		$sql2.="'".mysql_real_escape_string($payer_email, $dbshop)."', ";
		$sql.="paymentTransactionID, ";
		$sql2.="'".mysql_real_escape_string($txn_id, $dbshop)."', ";
		$sql.="parentPaymentTransactionID";
		$sql2.="'".mysql_real_escape_string($parent_txn_id, $dbshop)."'";
		$sql.=") VALUES (".$sql2.");";
		$res=q($sql, $dbshop, __FILE__, __LINE__);
		
		//UPDATE PAYMENT_NOTIFICATION_MESSAGES
		//$res=q("UPDATE payment_notification_messages SET processed = 1 WHERE id = ".$pnm_id.";", $dbshop, __FILE__, __LINE__);
	}
}

//###############################################################################################################################
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
	$res=q("SELECT * FROM paygenic_accounts WHERE MerchantID = '".$MerchantID."';", $dbshop, __FILE__, __LINE__);
	$row=mysql_fetch_array($res);
	$ACCOUNTID=$row["id_account"];
	
	//TOTAL

		$res=q("SELECT * FROM shop_orders_crm WHERE Payments_TransactionID = '".$PayID."';", $dbshop, __FILE__, __LINE__);
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

		
	
		//Erhaltene Daten komplett speichern
		$nvp="";
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$nvp .= '&'.$key.'='.$value;
		}
		//$res=q("insert into payment_notification_messages (message, date_recieved, processed) values ('".mysql_real_escape_string($nvp, $dbshop)."', ".time().", 0);", $dbshop, __FILE__, __LINE__);
	//	$pnm_id=mysql_insert_id($dbshop);
	
		//Speichern in PaymentNotifications Tabelle
		$res=q("INSERT INTO payment_notifications2 (payment_account_id, PN_date, payment_date, state, state_reason, orderTransactionID,shop_orderID,total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES (".$ACCOUNTID.", ".time().", ".$Payments_TransactionStateDate.", '".mysql_real_escape_string($Status, $dbshop)."', '".mysql_real_escape_string($Description, $dbshop)."', '".mysql_real_escape_string($TransID, $dbshop)."', '".mysql_real_escape_string($id_order, $dbshop)."', '".$TOTAL."', '0', '".$PLATFORM."', '".$buyer_id."', '".$PAYMENTTYPE."','".$buyer_lastname."', '".$buyer_firstname."', '".$buyer_mail."', '".mysql_real_escape_string($PayID, $dbshop)."');", $dbshop, __FILE__, __LINE__);
		
		// $res=q("UPDATE payment_notification_messages SET processed = 1 WHERE id = ".$pnm_id.";", $dbshop, __FILE__, __LINE__);
	
}
?>
