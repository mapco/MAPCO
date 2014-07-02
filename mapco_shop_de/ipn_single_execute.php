<?php

include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");


if (!isset($_POST["go"]))
{
	echo '<form action="ipn_single_execute.php" method="post">';
	echo '<input type="text" name="PN_ID" size="10" />';
	echo '<input type="submit" name="go" value="LOS!" />';
	echo '</form>';
}
else
{
	$res=q("select * from payment_notification_messages where id = ".$_POST["PN_ID"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	//$msg=iconv("windows-1252", "utf-8", $row["message"]);
	$msg=str_replace("%3A", ":",$row["message"]);
	$msg=str_replace("%2C", ",",$msg);
	$msg=str_replace("%40", "@",$msg);
	$msg=str_replace("%DF", "ß",$msg);
	$msg=str_replace("%FC", "ü",$msg);
	$msg=str_replace("%2F", "/",$msg);
	$msg=str_replace("%D3", "O",$msg);
	$msg=str_replace("+", " ",$msg);
	$msg=str_replace("%3A", ":",$msg);
	
				$vars=explode('&', trim($msg));
				$varField = array();
				for ($j=0; $j<sizeof($vars); $j++) {
					$tmp=explode('=',trim($vars[$j]));
					//if (isset($tmp[0]) && isset($tmp[1])) $_POST[$tmp[0]]=$tmp[1];
					if (isset($tmp[0]) && isset($tmp[1])) 
					{
						$varField[$tmp[0]]=$tmp[1];
						echo $tmp[0].': '.$tmp[1].'<br />';
					}
				}
				
	//Zahldatum für Timestamp vorbereiten
		
		switch (substr($varField["payment_date"], 9,3)) {
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
		$paymentdate=mktime(substr($varField["payment_date"], 0,2)*1, substr($varField["payment_date"], 3,2)*1, substr($varField["payment_date"], 6,2)*1, $month, substr($varField["payment_date"], 13,2)*1, substr($varField["payment_date"], 17)*1);

		if (!isset($varField["pending_reason"])) $varField["pending_reason"]="";
		if (!isset($varField["receiver_email"]) || $varField["receiver_email"]=="") $varField["receiver_email"]=$varField["business"];

		$res=q("SELECT * FROM paypal_accounts WHERE account_address = '".$varField["receiver_email"]."';", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res)) {$accountID=$row["id_account"];}
		if ($accountID==1)
		{
			$ebay_accountName="EBAY_MAPCO";
		}
		if ($accountID==3) 
		{
			$ebay_accountName="EBAY_AP";
		}
		
	if (isset($varField["for_auction"]) && ($varField["for_auction"]=="true" || $varField["for_auction"]=="TRUE"))
	{
		$PAYMENTTYPE="PayPal";
		$res=q("INSERT INTO payment_notifications2 (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $varField["$payment_status"])."', '".mysqli_real_escape_string($dbshop, $varField["pending_reason"])."', '".mysqli_real_escape_string($dbshop, $varField["ebay_txn_id1"])."', ".($varField["mc_gross"]*1).", ".($varField["mc_fee"]*1).", '".$ebay_accountName."', '".mysqli_real_escape_string($dbshop, $varField["auction_buyer_id"])."', '".mysqli_real_escape_string($dbshop, $PAYMENTTYPE)."', '".mysqli_real_escape_string($dbshop, $varField["last_name"])."', '".mysqli_real_escape_string($dbshop, $varField["first_name"])."', '".mysqli_real_escape_string($dbshop, $varField["payer_email"])."', '".mysqli_real_escape_string($dbshop, $varField["txn_id"])."');", $dbshop, __FILE__, __LINE__);
		
		echo 'INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('.$accountID.', '.time().', '.$paymentdate.', '.mysqli_real_escape_string($dbshop, $varField["$payment_status"]).', '.mysqli_real_escape_string($dbshop, $varField["pending_reason"]).', '.mysqli_real_escape_string($dbshop, $varField["ebay_txn_id1"]).', '.($varField["mc_gross"]*1).', '.($varField["mc_fee"]*1).', '.$ebay_accountName.', '.mysqli_real_escape_string($dbshop, $varField["auction_buyer_id"]).', '.mysqli_real_escape_string($dbshop, $PAYMENTTYPE).', '.mysqli_real_escape_string($dbshop, $varField["last_name"]).', '.mysqli_real_escape_string($dbshop, $varField["first_name"]).', '.mysqli_real_escape_string($dbshop, $varField["payer_email"]).', '.mysqli_real_escape_string($dbshop, $varField["txn_id"]);
	}
	elseif($accountID==1)
	//TRANSACTION FOR MAPCO_WEBSHOP
	{
		$res=q("SELECT * FROM shop_orders where Payments_TransactionID = '".$varField["txn_id"]."';", $dbshop, __FILE__, __LINE__);
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
		if (isset($varField["txn_type"]) && $varField["txn_type"]=="send_money")
		{
			$accountName="MAPCO_Zahlungsaufforderung";
		}
		else 
		{
			$accountName="MAPCO_ONLINESHOP";
		}
		$PAYMENTTYPE="PayPal";
		
			$res=q("INSERT INTO payment_notifications2 (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $varField["$payment_status"])."', '".mysqli_real_escape_string($dbshop, $varField["pending_reason"])."', '".mysqli_real_escape_string($dbshop, $id_order)."', ".($varField["mc_gross"]*1).", ".($varField["mc_fee"]*1).", '".$accountName."', '".mysqli_real_escape_string($dbshop, $buyer_id)."', '".mysqli_real_escape_string($dbshop, $PAYMENTTYPE)."', '".mysqli_real_escape_string($dbshop, $varField["last_name"])."', '".mysqli_real_escape_string($dbshop, $varField["first_name"])."', '".mysqli_real_escape_string($dbshop, $varField["payer_email"])."', '".mysqli_real_escape_string($dbshop, $varField["txn_id"])."');", $dbshop, __FILE__, __LINE__);
		echo "HALLO2";

		
		// CHECK OB ORDERMAIL gesendet werden muss
		if ($PayPal_TransactionState == "Pending" && $varField["$payment_status"]=="Completed")
		{
		//	mail_order2($row["id_order"], false, true);
		}
		
		//STATUSABGLEICH (IPN->ShopOrders)
		if ($PayPal_TransactionState!=$varField["payment_status"] && $PayPal_TransactionState!="xxxxxxxxx")
		{ 
			//$res=q("UPDATE shop_orders SET Payment_TransactionState = '".$payment_status."', PaymentTransactionStateDate = ".time()." WHERE id_order = ".$id_order.";", $dbshop, __FILE__, __LINE__);
		}
			
	}
	
	elseif($accountID==3)
	{
		if (isset($varField["txn_type"]) && $varField["txn_type"]=="send_money")
		{
			$id_order="AZxxxxxxx";
			$buyer_id="AZxxxxxxx";
			$PayPal_TransactionState="AZxxxxxxxxx";
			$accountName="AUTOPARTNER_Zahlungsaufforderung";
		}
		else 
		{
			$accountName="AUTOPARTNER_ONLINESHOP";
			$varField2["paymentTransactionID"]=$varField["txn_id"];
			$varField2["API"]="payments";
			$varField2["Action"]="PaymentNotificationGetOrder";
			if (strpos(PATH, "www")>0)
			{
				$responseXML=post("http://www.ihr-autopartner.de/soa/", $varField2);
			}
			else 
			{
				$responseXML=post("http://localhost/AUTOPARTNER/AUTOPARTNER/soa/", $varField2);
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

		
		//$res=q("INSERT INTO payment_notifications (payment_account_id, PN_date, payment_date, state, state_reason, orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, paymentTransactionID) VALUES ('".$accountID."', ".time().", ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $payment_status)."', '', '".mysqli_real_escape_string($dbshop, $id_order)."', '".($mc_gross*1)."', ".($mc_fee*1).", '".$accountName."', '".mysqli_real_escape_string($dbshop, $buyer_id)."', '".$PAYMENTTYPE."', '".mysqli_real_escape_string($dbshop, urldecode($last_name))."', '".mysqli_real_escape_string($dbshop, urldecode($first_name))."', '".mysqli_real_escape_string($dbshop, urldecode($payer_email))."', '".mysqli_real_escape_string($dbshop, $txn_id)."');", $dbshop, __FILE__, __LINE__);	
				//$processed=true;
		echo "HALLO3";

	}
	else echo "NOTHING";

}

?>