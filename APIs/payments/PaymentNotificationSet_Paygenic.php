<?php

	define("PN_Table", "payment_notifications4");
	define("PNM_Table", "payment_notification_messages4");
	
	define("SHOP_ORDERS", "shop_orders");
	define("SHOP_ORDERS_ITEMS", "shop_orders_items");

	function getOrderData($orderid)
	{
		global $currencies;
		global $dbshop;
		//GET ORDER
		$postfields["API"]="shop";
		$postfields["APIRequest"]="OrderDetailGet";
		$postfields["OrderID"]=$orderid;
		$responseXML=post(PATH."soa2/", $postfields);
		
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
			$responsefield=array();
			
			$responsefield["ordertotal"]=(float)str_replace(",", ".",(string)$response->Order[0]->orderTotalGross[0]);
			$responsefield["shop_id"]=(int)$response->Order[0]->shop_id[0];
			$responsefield["user_id"]=(int)$response->Order[0]->customer_id[0];

			$responsefield["firstmod"]=(int)$response->Order[0]->firstmod[0];
			$responsefield["currency"]=(string)$response->Order[0]->Currency_Code[0];
			$responsefield["buyer_firstname"]=(string)$response->Order[0]->bill_adr_firstname[0];
			$responsefield["buyer_lastname"]=(string)$response->Order[0]->bill_adr_lastname[0];
			
			$orderitems=array();
			$exchangerate=0;
			for ($i=0; isset($response->Order[0]->OrderItems[0]->Item[$i]); $i++)
			{
				$item = $response->Order[0]->OrderItems[0]->Item[$i];
				
				$orderitems[(int)$item->OrderItemID[0]]=$item;
				$exchangerate = $item->OrderItemExchangeRateToEUR[0];
			}
			$responsefield["orderitems"]=$orderitems;
			
			if ($exchangerate==0)
			{
				$exchangerate=$currencies[$responsefield["currency"]]["exchange_rate_to_EUR"];
			}
			
			$responsefield["exchangerate"]=$exchangerate;
			$responsefield["ordertotalEUR"]=$responsefield["ordertotal"]/$exchangerate;
			
			//GET SHOPTYPE
			$res_shoptype = q("SELECT * FROM shop_shops WHERE id_shop = ".$responsefield["shop_id"], $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_shoptype)==0)
			{
				//KEIN SHOP mit ID gefunden
				//show_error()
				echo "SHOP nicht gefunden";
				exit;
			}
			else
			{
				$shoptype=mysqli_fetch_assoc($res_shoptype);
				$responsefield["shoptype"]=$shoptype["shop_type"];
			}

			
			return $responsefield;
			
		}
		else
		{
			echo "ERROR:".$responseXML;
			//show_error(0, 7, __FILE__, __LINE__, $responseXML);
			exit;
		}

	}

	function getLastUserDeposit($userid)
	{
		global $dbshop;

		$res_userdeposit = q("SELECT * FROM ".PN_Table." WHERE user_id = ".$userid." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
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


//*******************************************************************************************************

	$required=array("id" => "numericNN");
	
	check_man_params($required);
	
	//q("UPDATE payment_notification_messages4 SET processed = 2 WHERE id = ".$_POST["id"], $dbshop, __FILE__, __LINE__);
	
	
	
	//GET PAYMENT MESSAGE
	$res_PNM=q("SELECT * FROM ".PNM_Table." WHERE id = ".$id, $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_PNM)==0)
	{
		//PAYMENTMESSAGE NIOCHT GEFUNDEN
		//show_error();
		exit;
	}
	$row_PNM=mysqli_fetch_assoc($res_PNM);
	
	if ($row_PNM["processed"]==1)
	{
		//MESSAGE ALREADY PROCESSED
		//show_error();
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
	$processed = false;

	if ($PN_data["Status"]=="OK")
	{

		$res=q("SELECT * FROM shop_orders WHERE Payments_TransactionID = '".$PN_data["PayID"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)>0)
		{
			$row = mysqli_fetch_assoc($res);
			$order_id = $row["id_order"];
			//GET "MOTHER ORDER"
			$combined_with = 0;
			
			$res_m_order = q("SELECT * FROM shop_orders WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)>0)
			{
				$row_m_order = mysqli_fetch_assoc($res_m_order);
				if ($row_m_order["combined_with"]>0) 
				{
					$order_id = $row_m_order["combined_with"];
					$combined_with = $order_id;
				}
			}
			$res = q("SELECT * FROM shop_orders WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			$row = mysqli_fetch_assoc($res);
	
			//UNTERSCHEIDUNG NACH MANUELLER oder AUTOM. NOTIFIUCATION
				// manuelle haben mehr Datenfelder
			if (isset ($PN_data["gross"]))
			//MAUNELLE
			{
				$total = $PN_data["gross"];
				$shop_id = $row["shop_id"];
				$paymentdate = $PN_data["payment_date"];
				$user_id=$row["customer_id"];
				$order_id=$row["id_order"];
				$buyer_lastname = $PN_data["payer_lastname"];
				$buyer_firstname = $PN_data["payer_firstname"];
				
			}
			else
			//AUTOMATISCHE
			{
				$order = getOrderData($row["id_order"]);
				
				$total = $order["ordertotalEUR"];
		
				$shop_id = $row["shop_id"];
				$paymentdate = $row["Payments_TransactionStateDate"];
				$order_id=$row["id_order"];
				$user_id=$row["customer_id"];		
				$buyer_lastname = $order["buyer_lastname"];
				$buyer_firstname = $order["buyer_firstname"];
			}
		}
		else
		{
			// KEINE ORDER MIT TransactionID gefunden
			//show_error();
			
			$shop_id = 0;
			$paymentdate = 0;
			$order_id = 0;
			$user_id=0;
			
			$total = 0;
	
			$buyer_lastname = "";
			$buyer_firstname = "";
	
		}
		
		$parenTransactionID="";
		
		$reason = "Completed";	
		$reason_detail = "";
		$accounting = $total;
		$user_deposit = getLastUserDeposit($user_id);
		$user_deposit+=$accounting;
		
		//PAYMENTTYPE
		switch ($PN_data["PaymentType"]*1)
		{
			case 1: $payment_type_id=5; break;	
			case 2: $payment_type_id=0; break;
			case 3: $payment_type_id=6; break;		
			
			default: echo "Unbekannte Zahlart von PayGenic übermittelt"; exit;		
			
		}
		
		$processed = true;	
	}
		
	if ($PN_data["Status"]=="Refunded")
	{
	//STATUS KANN NUR MANUELL AUSGELÖST WERDEN
		$res=q("SELECT * FROM shop_orders WHERE Payments_TransactionID = '".$PN_data["ParentTransactionID"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)>0)
		{
			$row = mysqli_fetch_assoc($res);
			$order_id = $row["id_order"];
			//GET "MOTHER ORDER"
			$combined_with = 0;
			
			$res_m_order = q("SELECT * FROM shop_orders WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)>0)
			{
				$row_m_order = mysqli_fetch_assoc($res_m_order);
				if ($row_m_order["combined_with"]>0) 
				{
					$order_id = $row_m_order["combined_with"];
					$combined_with = $order_id;
				}
			}
			$res = q("SELECT * FROM shop_orders WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			$row = mysqli_fetch_assoc($res);
	
			$total = $PN_data["gross"];
			$shop_id = $row["shop_id"];
			$paymentdate = $PN_data["payment_date"];
			$user_id=$row["customer_id"];
			$order_id=$row["id_order"];
			$buyer_lastname = $PN_data["payer_lastname"];
			$buyer_firstname = $PN_data["payer_firstname"];
				
		}
		else
		{
			// KEINE ORDER MIT TransactionID gefunden
			//show_error();
			
			$shop_id = 0;
			$paymentdate = 0;
			$order_id = 0;
			$user_id=0;
			
			$total = 0;
	
			$buyer_lastname = "";
			$buyer_firstname = "";
	
		}
		
		$parenTransactionID=$PN_data["ParentTransactionID"];
		
		$reason = "Refunded";	
		$reason_detail = "refund";
		$accounting = $total;
		$user_deposit = getLastUserDeposit($user_id);
		$user_deposit+=$accounting;
		
		//PAYMENTTYPE
		switch ($PN_data["PaymentType"]*1)
		{
			case 1: $payment_type_id=5; break;	
			case 2: $payment_type_id=0; break;
			case 3: $payment_type_id=6; break;		
			
			default: echo "Unbekannte Zahlart von PayGenic übermittelt"; exit;		
			
		}
		
		$processed = true;	
	}
	
	
	if ($processed)
	{

		//WRITE IPN
		$insert_data=array();
		$insert_data["f_id"]=$row_PNM["id"];
		$insert_data["shop_id"]=$shop_id;
		$insert_data["PN_date"]=time();
		$insert_data["accounting_date"]=$paymentdate;
		$insert_data["notification_type"]=1;
		$insert_data["reason"]=$reason;
		$insert_data["reason_detail"]=$reason_detail;
		$insert_data["orderTransactionID"]=$PN_data["TransID"];
		$insert_data["order_id"]=$order_id;
		$insert_data["total"]=$total;
		$insert_data["fee"]=0;
		$insert_data["currency"]="EUR";
		$insert_data["exchange_rate_from_EUR"]=1;
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["deposit_EUR"]=$accounting;
		$insert_data["user_id"]=$user_id;
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=$payment_type_id;
		$insert_data["buyer_lastname"]=$buyer_lastname;
		$insert_data["buyer_firstname"]=$buyer_firstname;
		$insert_data["payment_mail"]="";
		$insert_data["payer_id"]="";
		$insert_data["receiver_mail"]="";
		$insert_data["receiver_id"]="";
		$insert_data["paymentTransactionID"]=$PN_data["PayID"];
		$insert_data["parentPaymentTransactionID"]=$parenTransactionID;
		$insert_data["payment_note"]="";
	
		$res_insert = q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);
	
		$id_PN = mysqli_insert_id($dbshop);

	//AUFRUF PAYMENTNOTIFICATIONHANDLER ZUM BUCHEN DER ZAHLUNG
		//WENN ORDER BEKANNT IST
		if ($order_id!=0)
		{

			$postfields["API"]="payments";
			$postfields["APIRequest"]="PaymentNotificationHandler";
			$postfields["mode"]="Refund";
			//$postfields["orderid"]=$order_id;
			$postfields["TransactionID"]=$PN_data["PayID"];
			echo $responseXML=post(PATH."soa2/", $postfields);
			
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				//XML FEHLERHAFT
				//echo "XMLERROR".$responseXML;
				show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]!="Success")
			{
				show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse PaymentNotificationHandler:".print_r($response, true).print_r($postfields, true));
			}
			
		}
		
		//
		
	}
	else
	{
		//show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse PaymentNotificationHandler:".print_r($response, true).print_r($postfields, true));	
		echo "<Ack>Failure</Ack>";
	}
?>