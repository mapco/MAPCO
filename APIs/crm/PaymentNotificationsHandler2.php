<?php 
//session_start();

//	include("config.php");
//	include("../functions/shop_mail_order2.php");
//require_once '../../APIs/payments/PayPalConstants.php';



//extract($_POST);
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

	$nvp="";
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$nvp .= '&'.$key.'='.$value;
	}
	
	//Zeichensatzkovertierung
	
		//if (isset($charset) && $charset=="windows-1252") 
		
		if (!isset($memo)) $memo='';
		
		
		if (strpos($nvp, "%")>0)
		{
			$text=iconv("windows-1252", "utf-8", $nvp);
			$payment_status=iconv("windows-1252", "utf-8", $payment_status);
			$pending_reason=iconv("windows-1252", "utf-8", $pending_reason);
			$auction_buyer_id=iconv("windows-1252", "utf-8", $auction_buyer_id);
			$last_name=iconv("windows-1252", "utf-8", $last_name);
			$first_name=iconv("windows-1252", "utf-8", $first_name);
			$payer_email=iconv("windows-1252", "utf-8", $payer_email);
			$payment_date=iconv("utf-8", "windows-1252", $payment_date);
			$receiver_email=iconv("windows-1252", "utf-8", $receiver_email);
			$business=iconv("windows-1252", "utf-8", $business);
			$memo=iconv("windows-1252", "utf-8", $memo);
		}
	
		else $text=$nvp;
	//$text=$nvp;
/*
	$payment_date=str_replace("%3A", ":", $payment_date);
	$payment_date=str_replace("%2C", ",", $payment_date);
	$payment_date=str_replace("+", " ", $payment_date);

	$payer_email=str_replace("%40", "@", $payer_email);
	
	$last_name=str_replace("%F6", "ö", $last_name);
	$last_name=str_replace("%E4", "ä", $last_name);
	$last_name=str_replace("%FC", "ü", $last_name);
	$last_name=str_replace("%C4", "Ä", $last_name);
	$last_name=str_replace("%D6", "Ö", $last_name);
	$last_name=str_replace("%DC", "Ü", $last_name);
	$last_name=str_replace("%21", "!", $last_name);
	$last_name=str_replace("%2A", "*", $last_name);
	$last_name=str_replace("%DF", "ß", $last_name);

	$first_name=str_replace("%F6", "ö", $first_name);
	$first_name=str_replace("%E4", "ä", $first_name);
	$first_name=str_replace("%FC", "ü", $first_name);
	$first_name=str_replace("%C4", "Ä", $first_name);
	$first_name=str_replace("%D6", "Ö", $first_name);
	$first_name=str_replace("%DC", "Ü", $first_name);
	$first_name=str_replace("%21", "!", $first_name);
	$first_name=str_replace("%2A", "*", $first_name);
	$first_name=str_replace("%DF", "ß", $first_name);

	$auction_buyer_id=str_replace("%21", "!", $auction_buyer_id);
	$auction_buyer_id=str_replace("%2A", "*", $auction_buyer_id);

	$receiver_email=str_replace("%40", "@", $receiver_email);
	$business=str_replace("%40", "@", $business);
	*/
		if (!isset($receiver_email) || $receiver_email=="") $receiver_email=$business;
	

	$res=q("insert into payment_notification_messages3 (message, date_recieved) values ('".mysqli_real_escape_string($dbshop, $text)."', ".time().");", $dbshop, __FILE__, __LINE__);
	echo "#".$pnm_id=mysqli_insert_id($dbshop);
//	$pnm_id=mysqli_insert_id($dbshop);

	$processed=false;
	
	$paymentdate=strtotime($payment_date);

	if (!isset($pending_reason)) $pending_reason="";

	$accountID=0;
	$res=q("SELECT * FROM paypal_accounts WHERE account_address = '".$receiver_email."';", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res)) {$accountID=$row["id_account"];}

	$PAYMENTTYPE="PayPal";
	$payment_type_id=4;
		
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
				$shop_id=3;
			}
			if ($accountID==3) 
			{
				$accountName="EBAY_AP";
				$shop_id=4;
			}
			
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_ebay_orderItem=q("SELECT * FROM ebay_orders_items WHERE TransactionID = '".$ebay_txn_id1."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_ebay_orderItem)==1)
			//if (mysqli_affected_rows()==1)
			{
				//Search for Ebay_orderID
				$ebay_orderItem=mysqli_fetch_array($res_ebay_orderItem);
				$res_ebay_order=q("SELECT * FROM ebay_orders WHERE OrderID = '".$ebay_orderItem["OrderID"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_ebay_order)==1)
				{
					$ebay_order=mysqli_fetch_array($res_ebay_order);
					//Search for Shop_order_id
					$res_shop_order=q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$ebay_order["OrderID"]."';", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_shop_order)==1)
					{
						$shop_order=mysqli_fetch_array($res_shop_order);
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
				$shop_id=1;
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP";
				$shop_id=2;
			}
			
			$res_shop_order=q("SELECT * FROM shop_orders where Payments_TransactionID = '".$txn_id."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_shop_order)==1) 
			{
				$shop_order=mysqli_fetch_array($res_shop_order);
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
				$shop_id=1;
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP_Zahlungsaufforderung";
				$shop_id=2;
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
				$shop_id=3;
			}
			if ($accountID==3) 
			{
				$accountName="EBAY_AP";
				$shop_id=4;
			}
			
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_ebay_orderItem=q("SELECT * FROM ebay_orders_items WHERE TransactionID = '".$ebay_txn_id1."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_ebay_orderItem)==1)
			{
				//Search for Ebay_orderID
				$ebay_orderItem=mysqli_fetch_array($res_ebay_orderItem);
				$res_ebay_order=q("SELECT * FROM ebay_orders WHERE OrderID = '".$ebay_orderItem["OrderID"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_ebay_order)==1)
				{
					$ebay_order=mysqli_fetch_array($res_ebay_order);
					//Search for Shop_order_id
					$res_shop_order=q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$ebay_order["OrderID"]."';", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_shop_order)==1)
					{
						$shop_order=mysqli_fetch_array($res_shop_order);
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
				$shop_id=1;
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP";
				$shop_id=2;
			}
			
			$res_shop_order=q("SELECT * FROM shop_orders where Payments_TransactionID = '".$txn_id."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_shop_order)==1) 
			{
				$shop_order=mysqli_fetch_array($res_shop_order);
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
				$shop_id=1;
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP_Zahlungsaufforderung";
				$shop_id=2;
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
				$shop_id=3;
			}
			if ($accountID==3) 
			{
				$accountName="EBAY_AP";
				$shop_id=4;
			}
			
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_ebay_orderItem=q("SELECT * FROM ebay_orders_items WHERE TransactionID = '".$ebay_txn_id1."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_ebay_orderItem)==1)
			{
				//Search for Ebay_orderID
				$ebay_orderItem=mysqli_fetch_array($res_ebay_orderItem);
				$res_ebay_order=q("SELECT * FROM ebay_orders WHERE OrderID = '".$ebay_orderItem["OrderID"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_ebay_order)==1)
				{
					$ebay_order=mysqli_fetch_array($res_ebay_order);
					//Search for Shop_order_id
					$res_shop_order=q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$ebay_order["OrderID"]."';", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_shop_order)==1)
					{
						$shop_order=mysqli_fetch_array($res_shop_order);
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
				$shop_id=1;
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP";
				$shop_id=2;
			}
			
			$res_shop_order=q("SELECT * FROM shop_orders where Payments_TransactionID = '".$txn_id."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_shop_order)==1) 
			{
				$shop_order=mysqli_fetch_array($res_shop_order);
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
				$shop_id=1;
			}
			if ($accountID==3) 
			{
				$accountName="AUTOPARTNER_ONLINESHOP_Zahlungsaufforderung";
				$shop_id=2;
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
		$res_parent=q("SELECT * FROM payment_notifications3 WHERE paymentTransactionID = '".$parent_txn_id."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_parent)==1)
		{
			$parent_paymentTransaction=mysqli_fetch_array($res_parent);
			$payment_account_id=$parent_paymentTransaction["payment_account_id"];
			$orderTransactionID=$parent_paymentTransaction["orderTransactionID"];
			$shop_orderID=$parent_paymentTransaction["shop_orderID"];
			$accountName=$parent_paymentTransaction["platform"];
			$buyer_id=$parent_paymentTransaction["buyer_id"];
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
		$res_parent=q("SELECT * FROM payment_notifications3 WHERE paymentTransactionID = '".$parent_txn_id."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_parent)==1)
		{
			$parent_paymentTransaction=mysqli_fetch_array($res_parent);
			
			$payment_account_id=$parent_paymentTransaction["payment_account_id"];
			$orderTransactionID=$parent_paymentTransaction["orderTransactionID"];
			$shop_orderID=$parent_paymentTransaction["shop_orderID"];
			$accountName=$parent_paymentTransaction["platform"];
			$buyer_id=$parent_paymentTransaction["buyer_id"];
			$shop_id=$parent_paymentTransaction["shop_id"];
			//UPDATE ORDEREVENTS && SHOPORDER PAYMENTSTATUS

		}
		else
		{
			//mail("", "", "ERROR - PaymentNotification", "Zu Refund konnte keine PARENT TRANSACTION gefunden werden")
			$payment_account_id=$accountID;
			$orderTransactionID="";
			$shop_orderID=0;
			$accountName="";
			$shop_id=0;
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
		$res_parent=q("SELECT * FROM payment_notifications3 WHERE paymentTransactionID = '".$parent_txn_id."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_parent)==1)
		{
			$parent_paymentTransaction=mysqli_fetch_array($res_parent);
			
			$payment_account_id=$parent_paymentTransaction["payment_account_id"];
			$orderTransactionID=$parent_paymentTransaction["orderTransactionID"];
			$shop_orderID=$parent_paymentTransaction["shop_orderID"];
			$accountName=$parent_paymentTransaction["platform"];
			$buyer_id=$parent_paymentTransaction["buyer_id"];
			$shop_id=$parent_paymentTransaction["shop_id"];
			//UPDATE ORDEREVENTS && SHOPORDER PAYMENTSTATUS

		}
		else
		{
			//mail("", "", "ERROR - PaymentNotification", "Zu Refund konnte keine PARENT TRANSACTION gefunden werden")
			$payment_account_id=$accountID;
			$orderTransactionID="";
			$shop_orderID=0;
			$accountName="";
			$shop_id=0;
		}
		//FLAG FOR Payment_notification_messages
		$processed=true;	

	}
	
	if ($processed)
	{
		$res=q("INSERT INTO payment_notifications3 (
			payment_account_id, 
			shop_id, 
			PN_date, 
			payment_date, 
			state, 
			state_reason, 
			orderTransactionID, 
			shop_orderID, 
			total,
			fee,
			platform,
			buyer_id,
			payment_type,
			payment_type_id,
			buyer_lastname,
			buyer_firstname,
			buyer_mail, 
			paymentTransactionID,
			parentPaymentTransactionID,
			payment_note
		) VALUES (
			".$accountID.",
			".$shop_id.",
			".time().",
			".$paymentdate.",
			'".mysqli_real_escape_string($dbshop, $payment_status)."', 
			'".mysqli_real_escape_string($dbshop, $pending_reason)."', 
			'".mysqli_real_escape_string($dbshop, $orderTransactionID)."', 
			".$shop_orderID.",
			".($mc_gross*1).", 
			".($mc_fee*1).", 
			'".$accountName."',
			'".mysqli_real_escape_string($dbshop, $buyer_id)."', 
			'".mysqli_real_escape_string($dbshop, $PAYMENTTYPE)."', 
			".$payment_type_id.",
			'".mysqli_real_escape_string($dbshop, $last_name)."', 
			'".mysqli_real_escape_string($dbshop, $first_name)."', 
			'".mysqli_real_escape_string($dbshop, $payer_email)."',
			'".mysqli_real_escape_string($dbshop, $txn_id)."', 
			'".mysqli_real_escape_string($dbshop, $parent_txn_id)."',
			'".mysqli_real_escape_string($dbshop, $memo)."'
		);",$dbshop, __FILE__, __LINE__);

//		error_logs(__FILE__, __LINE__, mysqli_insert_id($dbshop));

		//UPDATE PAYMENT_NOTIFICATION_MESSAGES
		$res=q("UPDATE payment_notification_messages3 SET processed = 1 WHERE id = ".$pnm_id.";", $dbshop, __FILE__, __LINE__);


		//UPDATE PayPal_BuyerNote.shop_orders
		if ($memo!="" && $shop_orderID!=0)
		{
			q("UPDATE shop_orders SET PayPal_BuyerNote = '".mysqli_real_escape_string($dbshop, $memo)."' WHERE id_order = ".$shop_orderID.";", $dbshop, __FILE__, __LINE__);
		}
			
		
	}
}

//###############################################################################################################################
//*******************************
// PAYGENIC
//*******************************
if (isset($Userdata))
{
	//Erhaltene Daten komplett speichern
	$nvp="";
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$nvp .= '&'.$key.'='.$value;
	}

	$res=q("insert into payment_notification_messages3 (message, date_recieved, processed) values ('".mysqli_real_escape_string($dbshop, $nvp)."', ".time().", 0);", $dbshop, __FILE__, __LINE__);
		$pnm_id=mysqli_insert_id($dbshop);


	//PAYMENTTYPE
	switch ($PaymentType*1)
	{
		case 1: $PAYMENTTYPE="Kreditkarte"; $payment_type_id=5; break;	
		case 2: $PAYMENTTYPE="Lastschrift"; $payment_type_id=0; break;
		case 3: $PAYMENTTYPE="Sofortüberweisung"; $payment_type_id=6; break;				
		default: $PAYMENTTYPE=$PaymentType;
	}

	//ACCOUNT ID
	switch ($Userdata)
	{
		case "paygenic_mapco": $MerchantID="mapco_gmbh"; $PLATFORM="MAPCO_ONLINESHOP"; $shop_id=1; break;
		case "paygenic_autopartner": $MerchantID="autopartner_gmbh"; $PLATFORM="AUTOPARTNER_ONLINESHOP"; $shop_id=2; break;
	}
	$res=q("SELECT * FROM paygenic_accounts WHERE MerchantID_live = '".$MerchantID."';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	$ACCOUNTID=$row["id_account"];
	
	//TOTAL

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

		//Speichern in PaymentNotifications Tabelle
		$res=q("INSERT INTO payment_notifications3 (payment_account_id, shop_id, PN_date, payment_date, state, state_reason, orderTransactionID,shop_orderID,total, fee, platform, buyer_id, payment_type, payment_type_id, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES (".$ACCOUNTID.", ".$shop_id.", ".time().", ".$Payments_TransactionStateDate.", '".mysqli_real_escape_string($dbshop, $Status)."', '".mysqli_real_escape_string($dbshop, $Description)."', '".mysqli_real_escape_string($dbshop, $TransID)."', '".mysqli_real_escape_string($dbshop, $id_order)."', '".$TOTAL."', '0', '".$PLATFORM."', '".$buyer_id."', '".$PAYMENTTYPE."', ".$payment_type_id.", '".$buyer_lastname."', '".$buyer_firstname."', '".$buyer_mail."', '".mysqli_real_escape_string($dbshop, $PayID)."');", $dbshop, __FILE__, __LINE__);
		
		 $res=q("UPDATE payment_notification_messages3 SET processed = 1 WHERE id = ".$pnm_id.";", $dbshop, __FILE__, __LINE__);
	


}
?>
