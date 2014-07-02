<?php

// SOA2 Service

	define("SHOP_ORDER", "shop_orders");

/*
	Service setzt die status_id ggf. auf 7
	Payments_TransactionState & Payments_TransactionStateDate & payments_type_id werden aktualisiert
		Payments_TransactionState wird in Abhängigkeit vom bereits gespeichertem State aktualisiert (wenn StateDate neuer)
		Payments_TransactionStateDate wird in Abhängigkeit vom bereits gespeichertem State aktualisiert (wenn StateDate neuer)
		payments_type_id wird auf eingegangene Zahlung aktualisiert
		Payments_TransactionID wird auf eingegangene Zahlung aktualisiert

	Service schreibt BuyerPaymentNote in shop_orders ($_POST["payment_note"])


	Service erwartet als OrderID (bei kombinierten Orders) die "MutterOrderID"

*/

	$required=array("orderid" => "numericNN", "paymentTransactionID" => "text", "paymentState" => "text", "paymentStateDate" => "textNN", "payments_type_id" => "numeric");	
	check_man_params($required);
	//GET ORDERDATA
	$postfields = array();
	$postfields["API"] = "shop";
	$postfields["Action"] = "OrderGet";
	$postfields["id_order"] = $_POST["orderid"];
	
	$responseXML=post(PATH."soa/", $postfields);

	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXML);
	}
	catch(Exception $e)
	{
		show_error(9756, 7, __FILE__, __LINE__, "ServiceResponse".$responseXML);
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if ($response->Ack[0]!="Success")
	{
		show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".$responseXML);
		exit;
	}

	$status_id_old = (int)$response->status_id[0];
	$status_date_old = (int)$response->status_date[0];
	$Payments_TransactionState_old = (string)$response->Payments_TransactionState[0];
	$Payments_TransactionStateDate_old = (string)$response->Payments_TransactionStateDate[0];
	$Payments_TransactionID_old = (string)$response->Payments_TransactionID[0];
	
	$combined_with = (int)$response->combined_with[0];

	if (!isset($_POST["order_deposit"]))
	{
		//GET LAST ORDERDEPOSIT
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentNotificationLastOrderDepositGet";
		$postfields["orderid"] = $_POST["orderid"];
		
		$response = soa2($postfields, __FILE__, __LINE__);
		if ($response->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response,true));
			exit;
		}
		$last_order_deposit = (float)$response->orderdeposit[0];
	}
	else
	{
		$last_order_deposit = $_POST["order_deposit"];
	}

	//UPDATE SHOP_ORDER PAYMENTSTATUS
	if (($status_id_old==1 || $status_id_old==5) && $last_order_deposit<0.02)
	{
		$status_id = 7;
		$status_date = time();
	}
	else
	{
		$status_id = $status_id_old;
		$status_date = $status_date_old;
	}
	
	
	$update = false;
	//WENN PaymentTransactionIDs von Order und Payment übereinstimmen	
	if ($Payments_TransactionID_old==$_POST["paymentTransactionID"])
	{
		switch ($_POST["paymentState"])
		{
			// "CREATED" CAN ONLY BE SET IF THERE IS NO OTHER STATE WITH SAME TRANSACTION_ID	
			case "Created": 
				if ($Payments_TransactionState_old == "")
				{
					$update = true;
				}
				break;	
			// "COMPLETED" CAN UPDATE [ CREATED||PENDING ] WITH SAME TRANSACTION_ID	
			case "Completed": 
				if ($Payments_TransactionState_old == "Created" || $Payments_TransactionState_old == "Pending")
				{
					$update = true;
				}
				break;	
			// "PENDING" CAN UPDATE [ CREATED ] WITH SAME TRANSACTION_ID	
			case "Pending":
				if ($Payments_TransactionState_old == "Created")
				{
					$update = true;
				}
				break;
			// "DENIED" CAN UPDATE [ CREATED || PENDING ] WITH SAME TRANSACTION_ID	
			case "Denied":
				if ($Payments_TransactionState_old == "Created" || $Payments_TransactionState_old == "Pending")
				{
					$update = true;
				}
				break;	
			// "REFUNDED" CAN UPDATE [ CREATED || PENDING || COMPLETED || REVERSED || CANCELED_REVERSAL] WITH SAME TRANSACTION_ID	
			case "Refunded":
				if ($Payments_TransactionState_old == "Created" || $Payments_TransactionState_old == "Pending" || $Payments_TransactionState_old == "Completed" || $Payments_TransactionState_old == "Reversed" || $Payments_TransactionState_old == "Canceled_Reversal")
				{
					$update = true;
				}
				break;	
			// "REVERSED" CAN UPDATE [ CREATED || PENDING || COMPLETED ] WITH SAME TRANSACTION_ID	
			case "Reversed":
				if ($Payments_TransactionState_old == "Created" || $Payments_TransactionState_old == "Pending" || $Payments_TransactionState_old == "Completed")
				{
					$update = true;
				}
				break;	
			// "CANCELED REVERSAL" CAN UPDATE [ CREATED || PENDING || COMPLETED || REVERSED] WITH SAME TRANSACTION_ID	
			case "Canceled_Reversal":
				if ($Payments_TransactionState_old == "Created" || $Payments_TransactionState_old == "Pending" || $Payments_TransactionState_old == "Completed" || $Payments_TransactionState_old == "Reversed")
				{
					$update = true;
				}
				break;	
		}
	}
	//WENN PaymentTransactionIDs von Order und Payment NICHT übereinstimmen -> Update wenn PaymentStateDate neuer als bereits gespeicherter
	elseif ($Payments_TransactionStateDate_old<$_POST["paymentStateDate"] || $Payments_TransactionID_old=="")
	{
		$update = true;
	}

	
	if ($update)
	{
		$Payments_TransactionState = $_POST["paymentState"];
		$Payments_TransactionStateDate = $_POST["paymentStateDate"];	

		$datafield = array();
		$datafield["status_id"]=$status_id;
		$datafield["status_date"]=$status_date;
		$datafield["payments_type_id"]=$_POST["payments_type_id"];
		$datafield["Payments_TransactionID"]=$_POST["paymentTransactionID"];
		$datafield["Payments_TransactionState"]=$Payments_TransactionState;
		$datafield["Payments_TransactionStateDate"]=$Payments_TransactionStateDate;
		$datafield["order_deposit"]=$last_order_deposit;

	//	if ($combined_with>0)
	//	{
	//		q_update("shop_orders", $datafield, "WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__ );
	//	}
	//	else
		{
			q_update("shop_orders", $datafield, "WHERE id_order = ".$_POST["orderid"], $dbshop, __FILE__, __LINE__ );
		}

	}
	
	//SCHREIBE BUYERPAYMENTNOTE
	if (isset($_POST["payment_note"]) && $_POST["payment_note"]!="")
	{
		if ($combined_with>0)
		{
			q("UPDATE ".SHOP_ORDER." SET PayPal_BuyerNote = ".mysqli_real_escape_string($dbshop, $_POST["payment_note"])." WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__);

		}
		else
		{
			q("UPDATE ".SHOP_ORDER." SET PayPal_BuyerNote = ".mysqli_real_escape_string($dbshop, $_POST["payment_note"])." WHERE id_order = ".$_POST["orderid"], $dbshop, __FILE__, __LINE__);
		}
	}
	
?>