<?php

	define("PN_Table", "payment_notifications4");
	define("PNM_Table", "payment_notification_messages4");


	//$required=array("mode" => "textNN", "orderid" => "numericNN", "payment_total" => "nummericNN", "accounting_date" => "numericNN");
	$required=array("PNM_id" => "numericNN");
	check_man_params($required);

	function getLastUserDeposit($userid)
	{
		global $dbshop;

		$res_userdeposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$userid." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_userdeposit)==0)
		{
			//KEIN EINTRAG GEFUNDEN - > OLD DEPOSIT = 0
			$user_deposit=0;
		}
		else
		{
			$row_userdeposit = mysqli_fetch_assoc($res_userdeposit);
			$user_deposit = $row_userdeposit["user_deposit_EUR"];
		}

		return $user_deposit;		
	}
	
	function update_PaymentStatus ($orderid, $paymentTransactionID, $paymentState, $paymentStateDate)
	{
		$postfields = array();
		$postfields["API"] = "shop";
		$postfields["APIRequest"] = "OrdersPaymentStatusUpdate";
		$postfields["orderid"]=$orderid;
		$postfields["paymentTransactionID"]=$paymentTransactionID;
		$postfields["paymentState"]=$paymentState;
		$postfields["paymentStateDate"]=$paymentStateDate;
		$postfields["payments_type_id"]=3;
		
		$response = soa2($postfields, __FILE__, __LINE__);
		if ($response->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse update_PaymentStatus:".print_r($response, true).print_r($postfields, true));
		}
	}

	//GET PAYMENTNOTIFICATIONMESSAGE DATA
	$res_PNM=q("SELECT * FROM ".PNM_Table." WHERE id = ".$_POST["PNM_id"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_PNM)==0)
	{
		//PAYMENTMESSAGE NICHT GEFUNDEN
		show_error(9803, 9, __FILE__, __LINE__, "PaymentMessageID".$_POST["PNM_id"]);
		exit;
	}
	$row_PNM=mysqli_fetch_assoc($res_PNM);
	
	if ($row_PNM["processed"]==1)
	{
		//MESSAGE ALREADY PROCESSED
		show_error(9804, 9, __FILE__, __LINE__, "PaymentMessageID".$_POST["PNM_id"]);
		exit;
	}
	//WRITE MESSAGE TO ARRAY
	$text = array();
	$text = explode("&", $row_PNM["message"]);
	
	$PN_data = array();
	for ($i=0; $i<sizeof($text); $i++)
	{
		$zeile = explode("=", $text[$i]);
		if ($zeile[0]!="")
		{
			$PN_data[$zeile[0]]=$zeile[1];	
		}
	}
//print_r($PN_data);
	//GET CURRECNIES AND EXCHANGERATES
	$currencies=array();
	$res_curr=q("SELECT * FROM shop_currencies", $dbshop, __FILE__, __LINE__);
	while ($row_curr = mysqli_fetch_assoc($res_curr))
	{
		$currencies[$row_curr["currency_code"]] = $row_curr;
	}


	//GET ORDER
	$response="";
	$responseXML="";
	
	$postfields=array();
	$postfields["API"]="shop";
	$postfields["Action"]="OrderGet";
	$postfields["id_order"]=$PN_data["Order_ID"];
	
	$responseXML = post(PATH."soa/", $postfields);
	
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$order = new SimpleXMLElement($responseXML);
	}
	catch(Exception $e)
	{
		//XML FEHLERHAFT
		show_error(9756, 7, __FILE__, __LINE__, $responseXML);
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if ($order->Ack[0]!="Success")
	{
		show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($order, true).print_r($postfields, true));
		exit;
	}

	if($PN_data["payment_status"]=="Completed")
	{
		
		$accounting = $PN_data["gross"]/$currencies[$PN_data["currency"]]["exchange_rate_to_EUR"];
		$last_user_deposit = getLastUserDeposit($PN_data["customer_id"]);
		$user_deposit = $last_user_deposit+$accounting;
	
		//ZAHLUNG IN PAYMENTNOTIFICATIONS SCHREIBEN
		$insert_data=array();
		$insert_data["f_id"]=$_POST["PNM_id"];
		$insert_data["shop_id"]=(int)$response->shop_id[0];
		$insert_data["PN_date"]=$PN_data["payment_date"];
		$insert_data["accounting_date"]=$PN_data["payment_date"];
		$insert_data["notification_type"]=1;
		$insert_data["reason"]="Completed";
		$insert_data["order_id"]=$PN_data["Order_ID"];
		$insert_data["total"]=$PN_data["gross"];
		$insert_data["currency"]=$PN_data["currency"];
		$insert_data["exchange_rate_from_EUR"]=$currencies[$PN_data["currency"]]["exchange_rate_to_EUR"];
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["deposit_EUR"]=$accounting;
		$insert_data["user_id"]=$PN_data["customer_id"];
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=3;
		$insert_data["buyer_lastname"]=$PN_data["payer_lastname"];
		$insert_data["buyer_firstname"]=$PN_data["payer_firstname"];
		
		$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
	
		$id_PN = mysqli_insert_id($dbshop);
		
		q("UPDATE ".PN_Table." SET paymentTransactionID = ".$id_PN." WHERE id_PN = ".$id_PN, $dbshop, __FILE__, __LINE__);
	
		//ZAHLUNG BUCHEN
		$postfields=array();
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationHandler";
		$postfields["mode"]="Payment";
		$postfields["orderid"]=$PN_data["Order_ID"];
		$postfields["TransactionID"]=$id_PN;
		
		$responseXML = post(PATH."/soa2/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			//XML FEHLERHAFT
			show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		
		if ($response->Ack[0]!="Success")
		{
			echo $responseXML;
			exit;
		}
	}

?>