<?php

//	include_once "constants.php";

	$required=array("mode" => "textNN");
	check_man_params($required);

	define("PN_Table", "payment_notifications");
	define("PNM_Table", "payment_notification_messages");
	define("SHOP_ORDER", "shop_orders");
	
	//GET shop TYPES
	$shops=array();
	$res_shop=q("SELECT * FROM shop_shops", $dbshop, __FILE__, __LINE__);
	while ($row_shop = mysqli_fetch_assoc($res_shop))
	{
		$shops[$row_shop["id_shop"]] = $row_shop;
	}

	if ($_POST["mode"]=="PayPal")
	{
		
		/*
			VOR AUFRUF MUSS TRANSACTIONID UND PAYMENTTYPEID IN SHOP_ORDERS GESCHRIEBEN SEIN
		*/
		
		$required=array("orderid" => "numericNN");
	
		check_man_params($required);
		
		//GET ORDER
		$postfields=array();
		$postfields["API"]="shop";
		$postfields["Action"]="OrderGet";
		$postfields["id_order"]=$_POST["orderid"];
		
		$responseXML = post(PATH."soa/", $postfields);
		
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
			$transactionID=(string)$response->Payments_TransactionID[0];
			$payment_type_id=(int)$response->payments_type_id[0];
			$Payments_TransactionState=(string)$response->Payments_TransactionState[0];
			$Payments_TransactionStateDate=(int)$response->Payments_TransactionStateDate[0];
		}
		else
		{
			echo "ERROR OrderGet Aufruf".$responseXML;
		}

		$orderids=array();
		$orders=array();
		$res_orders = q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["orderid"],$dbshop, __FILE__,__LINE__);
		$row_orders = mysqli_fetch_assoc($res_orders);
		if ($row_orders["combined_with"]>0)
		{
			$res_orders2 = q("SELECT * FROM shop_orders WHERE combined_with = ".$row_orders["combined_with"], $dbshop, __FILE__, __LINE__);
			while ($row_orders2 = mysqli_fetch_assoc($res_orders2))
			{
				$orderids[]=$row_orders2["id_order"];
				$orders[$row_orders2["id_order"]]=$row_orders2;
			}
		}
		else
		{
			$orderids[0]=$_POST["orderid"];
			$orders[$row_orders["id_order"]]=$row_orders;
		}
		
		foreach($orders as $orderid => $orderdata)
		{
			//SUCHE NACH PAYMENT IN NOTIFICATIONS -> GET LAST
			$res_notification = q("SELECT * FROM ".PN_Table." WHERE payment_type_id = ".$payment_type_id." AND paymentTransactionID = '".$transactionID."' ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_notification)==0)
			{
				//KEINE ZAHLUNG GEFUNDEN	
				//??? ABRUFEN BEI PAYPAL???
			}
			else
			{
				$notification = mysqli_fetch_assoc($res_notification);
	//print_r($notification);
				//LINKING PAYMENT 
				$responseXML="";
				$response="";
				
				$postfields=array();
				$postfields["API"]="payments";
				$postfields["APIRequest"]="PaymentNotificationHandler";
				$postfields["mode"]="LinkingPayment";
				$postfields["orderid"]=$orderid;
				$postfields["TransactionID"]=$transactionID;
				
				$responseXML = post(PATH."/soa2/", $postfields);
				
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
					exit;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				if ($response->Ack[0]!="Success")
				{
					echo $responseXML;
					exit;
				}
	
	
	
				$responseXML="";
				$response="";
		
				//ZAHLUNG BUCHEN
				$postfields=array();
				$postfields["API"]="payments";
				$postfields["APIRequest"]="PaymentNotificationHandler";
				$postfields["mode"]="Payment";
				$postfields["orderid"]=$orderid;
				$postfields["TransactionID"]=$transactionID;
				
				$responseXML = post(PATH."/soa2/", $postfields);
				
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
				
				if ($response->Ack[0]!="Success")
				{
					echo $responseXML;
					exit;
				}
				
				//UPDATE SHOP_ORDER PAYMENTSTATUS
				$update=false;
				if ($orders[$orderid]["Payments_TransactionStateDate"]>=$notification["accounting_date"])
				{
					if ($orders[$orderid]["Payments_TransactionState"]=="" || $orders[$orderid]["Payments_TransactionState"]=="Created")
					{
						$update = true;
					}
					elseif ($orders[$orderid]["Payments_TransactionState"]=="Pending")
					{
						if ($notification["reason"]=="Completed" || $notification["reason"]=="Denied")	$update = true;
					}
					elseif ($orders[$orderid]["Payments_TransactionState"]=="Completed")
					{
						if ($notification["reason"]=="Refunded" || $notification["reason"]=="Reversed" || $notification["reason"]=="Canceled_Reversal")	$update = true;
					}
					elseif ($orders[$orderid]["Payments_TransactionState"]=="Reversed")
					{
						if ($notification["reason"]=="Canceled_Reversal") $update = true;
					}
				}
				else
				// NEUERE PN WERDEN IMMER GEUPDATED
				{
					$update = true;
				}
				
				if ($update)
				{
	
					$responseXML="";
					$response="";
					
					$postfields=array();
					$postfields["API"]="shop";
					$postfields["APIRequest"]="OrderUpdate";
					$postfields["SELECTOR_id_order"]=$orderid;
					
					$postfields["Payments_TransactionState"]=$notification["reason"];
					$postfields["Payments_TransactionStateDate"]=$notification["accounting_date"];
	
					$responseXML = post(PATH."/soa2/", $postfields);
					
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
	
				}
				
			}
		} // FOREACH
	} // MODE PAYPAL

	if ($_POST["mode"]=="PayPal_Refund")
	{
		$required=array("orderid" => "numericNN");
		check_man_params($required);
		
		if (isset($_POST["accounting_date"]) && $_POST["accounting_date"]!=0)
		{
			$accounting_date = $_POST["accounting_date"];
		}
		else
		{
			$accounting_date = time();
		}
		
		//GET ORDER
		$postfields=array();
		$postfields["API"]="shop";
		$postfields["Action"]="OrderGet";
		$postfields["id_order"]=$_POST["orderid"];
		
		$responseXML = post(PATH."soa/", $postfields);
		
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
			$transactionID=(string)$response->Payments_TransactionID[0];
			$payment_type_id=(int)$response->payments_type_id[0];
			$Payments_TransactionState=(string)$response->Payments_TransactionState[0];
			$Payments_TransactionStateDate=(int)$response->Payments_TransactionStateDate[0];
		}
		else
		{
			echo "ERROR OrderGet Aufruf".$responseXML;
		}
		
		//SCHREIBE PAYMENTSTATUS IN SHOP_ORDERS
		//UPDATE SHOP_ORDER PAYMENTSTATUS
		$update=false;
		if ($Payments_TransactionStateDate>=$accounting_date)
		{
			if ($Payments_TransactionState=="" || $Payments_TransactionState=="Created")
			{
				$update = true;
			}
			elseif ($Payments_TransactionState=="Pending")
			{
				$update = true;
			}
			elseif ($Payments_TransactionState=="Completed")
			{
				$update = true;
			}
		}
		else
		// NEUERE PN WERDEN IMMER GEUPDATED
		{
			$update = true;
		}
		
		if ($update)
		{

			$responseXML="";
			$response="";
			
			$postfields=array();
			$postfields["API"]="shop";
			$postfields["APIRequest"]="OrderUpdate";
			$postfields["SELECTOR_id_order"]=$_POST["orderid"];
			
			$postfields["Payments_TransactionState"]="Refunded";
			$postfields["Payments_TransactionStateDate"]=$accounting_date;

			$responseXML = post(PATH."/soa2/", $postfields);
			
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

		}

	} // IF MODE PayPal_Refund


	if ($_POST["mode"]=="PayPal_SendMoney")
	{
		$required=array("orderid" => "numericNN", "TransactionID" => "textNN", "receiver_mail" => "textNN", "sent_total" =>"numericNN");
		check_man_params($required);

		if (isset($_POST["accounting_date"]) && $_POST["accounting_date"]!=0)
		{
			$accounting_date = $_POST["accounting_date"];
		}
		else
		{
			$accounting_date = time();
		}
		
		$accounting=$_POST["sent_total"]*-1;

		//GET ORDER
		$postfields=array();
		$postfields["API"]="shop";
		$postfields["Action"]="OrderGet";
		$postfields["id_order"]=$_POST["orderid"];
		
		$responseXML = post(PATH."soa/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			//XML FEHLERHAFT
			echo "XMLERROR GET ORDER".$responseXML;
			//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			//exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response->Ack[0]=="Success")
		{
			$transactionID=(string)$response->Payments_TransactionID[0];
			$payment_type_id=(int)$response->payments_type_id[0];
			$Payments_TransactionState=(string)$response->Payments_TransactionState[0];
			$Payments_TransactionStateDate=(int)$response->Payments_TransactionStateDate[0];
			$buyer_lastname=(string)$response->bill_lastname[0];
			$buyer_firstname=(string)$response->bill_firstname[0];
			$shop_id=(int)$response->shop_id[0];
			$user_id=(int)$response->customer_id[0];
		}
		else
		{
			echo "ERROR OrderGet Aufruf".$responseXML;
		}


		$responseXML="";
		$response="";

		//GET ORDERPAYMENTS
		$postfields=array();
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationLastUserDepositGet";
		$postfields["userid"]=$user_id;
		/*
		$responseXML = post(PATH."soa2/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			//XML FEHLERHAFT
			echo "XMLERROR GET PAYMENTS".$responseXML;
			//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			//exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		*/
		$response = soa2($postfields, __FILE__, __LINE__);
		if ($response->Ack[0]=="Success")
		{
			$user_deposit=(float)$response->user_deposit[0];

		}
		else
		{
			echo "ERROR OrderGet Aufruf";
			//show_error();
		}
		
		$user_deposit+=$accounting;
		
		//DEFINE PAYPAL-ACCOUNT 
		$res_paypalaccount = q("SELECT * FROM paypal_accounts WHERE shop_id = ".$shop_id, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_paypalaccount)==0)
		{
			echo "KEIN PAYPAL-ACCOUNT zur Bestellung gefunden";
			exit;
		}
		$paypal_account= mysqli_fetch_assoc($res_paypalaccount);
		
		//WRITE IPN
		$insert_data=array();
		$insert_data["f_id"]=0;
		$insert_data["shop_id"]=$shop_id;
		$insert_data["PN_date"]=time();
		$insert_data["accounting_date"]=$accounting_date;
		$insert_data["notification_type"]=1;
		$insert_data["reason"]="send_money";
		$insert_data["reason_detail"]="";
		$insert_data["orderTransactionID"]="";
		$insert_data["order_id"]=$_POST["orderid"];
		$insert_data["total"]=$accounting;
		$insert_data["fee"]=0;
		$insert_data["currency"]="EUR";
		$insert_data["exchange_rate_from_EUR"]=1;
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["deposit_EUR"]=$accounting;
		$insert_data["user_id"]=$user_id;
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=4;
		$insert_data["buyer_lastname"]=$buyer_lastname;
		$insert_data["buyer_firstname"]=$buyer_firstname;
		$insert_data["payment_mail"]=$paypal_account["account_address"];
		$insert_data["payer_id"]="";
		$insert_data["receiver_mail"]=$_POST["receiver_mail"];
		$insert_data["receiver_id"]="";
		$insert_data["paymentTransactionID"]=$_POST["TransactionID"];
		$insert_data["parentPaymentTransactionID"]="";
		$insert_data["payment_note"]="";

		$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);

		$id_PN = mysqli_insert_id($dbshop);

	}
	
	if ($_POST["mode"]=="BankTransfer")
	{
		$required=array("orderid" => "numericNN", "payment_total" => "nummericNN", "accounting_date" => "numericNN");
		check_man_params($required);

		//GET ORDER
		$response="";
		$responseXML="";
		
		$postfields=array();
		$postfields["API"]="shop";
		$postfields["Action"]="OrderGet";
		$postfields["id_order"]=$_POST["orderid"];
		
		$responseXML = post(PATH."soa/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$order = new SimpleXMLElement($responseXML);
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
		if ($order->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($order, true).print_r($postfields, true));
		}


		//CREATE PAYMENTNOTIFICATIONMESSAGE
		$msg = "";
		$msg.="payment_status=Completed";
		$msg.="&payment_date=".urlencode($_POST["accounting_date"]);
		$msg.="&gross=".urlencode($_POST["payment_total"]);
		$msg.="&currency=EUR";
		$msg.="&Order_ID=".urlencode($_POST["orderid"]);
		$msg.="&customer_id=".urlencode((string)$order->customer_id[0]);
		$msg.="&payer_firstname=".urlencode((string)$order->bill_firstname[0]);
		$msg.="&payer_lastname=".urlencode((string)$order->bill_lastname[0]);

		//WRITE PAYMENTNOTIFICATIONMESSAGE
		$insert_data=array();
		$insert_data["message"]=$msg;
		$insert_data["date_received"]=time();
		$insert_data["processed"]=0;
		$insert_data["checked"]="unchecked";
		$insert_data["payment_type_id"]=2;

		$res_insert = q_insert(PNM_Table, $insert_data, $dbshop, __FILE__, __LINE__);
		
		$id_PNM=mysqli_insert_id($dbshop);
		
		q("UPDATE ".PNM_Table." SET ipn_track_id = ".$id_PNM." WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);


		$responseXML="";
		$response="";
		
		$postfields=array();
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationSet_BankTransfer";
		$postfields["PNM_id"]=$id_PNM;
		//$postfields["orderid"]=$_POST["orderid"];
		//$postfields["mode"]="payment";
		//$postfields["payment_total"]=$_POST["payment_total"];
		//$postfields["accounting_date"]=$_POST["accounting_date"];

		$responseXML = post(PATH."/soa2/", $postfields);
		
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
		
		// SET PAYMENTNOTIFICATIONMESSAGE TO PROCESSED
		if ($response->Ack[0]=="Success")
		{
			q("UPDATE ".PNM_Table." SET processed = 1 WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);			
		}
		
		
		// IF EBAY ORDER -> Call ReviseCheckoutStatus
		if ($shops[(int)$order->shop_id[0]]["shop_type"]==2)
		{
			$postfields=array();
			$postfields["API"] = "ebay";
			$postfields["Action"] = "ReviseCheckoutStatus";
			$postfields["mode"] = "paymentupdate";
			$postfields["amount"] = $_POST["payment_total"];
			$postfields["paymentmode"] = "payment";
			$postfields["id_order"] = $_POST["orderid"];
			$responseXML = post(PATH."/soa/", $postfields);
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				//XML FEHLERHAFT
				show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				//exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			
			// SET PAYMENTNOTIFICATIONMESSAGE TO PROCESSED
			if ($response->Ack[0]=="Success")
			{
				//EBAY_ZAHLUNGSSTATUS KONNTNICHT GESTZT WERDEN
				//show_error()		
			}
		}
	}
	
	if ($_POST["mode"]=="BankTransfer_SendMoney")
	{
		$required=array("orderid" => "numericNN", "payment_total" => "nummericNN", "accounting_date" => "numericNN", "ParentTransactionID" => "textNN");
		check_man_params($required);

		//GET ORDER
		$response="";
		$responseXML="";
		
		$postfields=array();
		$postfields["API"]="shop";
		$postfields["Action"]="OrderGet";
		$postfields["id_order"]=$_POST["orderid"];
		
		$responseXML = post(PATH."soa/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$order = new SimpleXMLElement($responseXML);
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
		if ($order->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($order, true).print_r($postfields, true));
		}


		//CREATE PAYMENTNOTIFICATIONMESSAGE
		$msg = "";
		$msg.="payment_status=Refunded";
		$msg.="&payment_date=".urlencode($_POST["accounting_date"]);
		$msg.="&gross=-".urlencode($_POST["payment_total"]);
		$msg.="&currency=EUR";
		$msg.="&Order_ID=".urlencode($_POST["orderid"]);
		$msg.="&ParentTransactionID=".urlencode($_POST["ParentTransactionID"]);
		$msg.="&customer_id=".urlencode((string)$order->customer_id[0]);
		$msg.="&payer_firstname=".urlencode((string)$order->bill_firstname[0]);
		$msg.="&payer_lastname=".urlencode((string)$order->bill_lastname[0]);

		//WRITE PAYMENTNOTIFICATIONMESSAGE
		$insert_data=array();
		$insert_data["message"]=$msg;
		$insert_data["date_received"]=time();
		$insert_data["processed"]=0;
		$insert_data["checked"]="unchecked";
		$insert_data["payment_type_id"]=2;

		$res_insert = q_insert(PNM_Table, $insert_data, $dbshop, __FILE__, __LINE__);
		
		$id_PNM=mysqli_insert_id($dbshop);
		
		q("UPDATE ".PNM_Table." SET ipn_track_id = ".$id_PNM." WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);


		$responseXML="";
		$response="";
		
		$postfields=array();
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationSet_BankTransfer";
		$postfields["PNM_id"]=$id_PNM;
		//$postfields["orderid"]=$_POST["orderid"];
		//$postfields["mode"]="payment";
		//$postfields["payment_total"]=$_POST["payment_total"];
		//$postfields["accounting_date"]=$_POST["accounting_date"];

		$responseXML = post(PATH."/soa2/", $postfields);
		
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
		
		// SET PAYMENTNOTIFICATIONMESSAGE TO PROCESSED
		if ($response->Ack[0]=="Success")
		{
			q("UPDATE ".PNM_Table." SET processed = 1 WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);			
		}

		// IF EBAY ORDER -> Call ReviseCheckoutStatus
		if ($shops[(int)$order->shop_id[0]]["shop_type"]==2)
		{
			$postfields=array();
			$postfields["API"] = "ebay";
			$postfields["Action"] = "ReviseCheckoutStatus";
			$postfields["mode"] = "paymentupdate";
			$postfields["amount"] = $_POST["payment_total"];
			$postfields["paymentmode"] = "refund";
			$postfields["id_order"] = $_POST["orderid"];
			$responseXML = post(PATH."/soa/", $postfields);
			
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				//XML FEHLERHAFT
				show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				//exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			
			// SET PAYMENTNOTIFICATIONMESSAGE TO PROCESSED
			if ($response->Ack[0]=="Success")
			{
				//EBAY_ZAHLUNGSSTATUS KONNTNICHT GESTZT WERDEN
				//show_error()		
			}
		}

	}

	
	if ($_POST["mode"]=="COD")
	{
		$required=array("orderid" => "numericNN", "payment_total" => "nummericNN", "accounting_date" => "numericNN");
		check_man_params($required);

		//GET ORDER
		$response="";
		$responseXML="";
		
		$postfields=array();
		$postfields["API"]="shop";
		$postfields["Action"]="OrderGet";
		$postfields["id_order"]=$_POST["orderid"];
		
		$responseXML = post(PATH."soa/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$order = new SimpleXMLElement($responseXML);
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
		if ($order->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($order, true).print_r($postfields, true));
		}


		//CREATE PAYMENTNOTIFICATIONMESSAGE
		$msg = "";
		$msg.="payment_status=Completed";
		$msg.="&payment_date=".urlencode($_POST["accounting_date"]);
		$msg.="&gross=".urlencode($_POST["payment_total"]);
		$msg.="&currency=EUR";
		$msg.="&Order_ID=".urlencode($_POST["orderid"]);
		$msg.="&customer_id=".urlencode((string)$order->customer_id[0]);
		$msg.="&payer_firstname=".urlencode((string)$order->bill_firstname[0]);
		$msg.="&payer_lastname=".urlencode((string)$order->bill_lastname[0]);

		//WRITE PAYMENTNOTIFICATIONMESSAGE
		$insert_data=array();
		$insert_data["message"]=$msg;
		$insert_data["date_received"]=time();
		$insert_data["processed"]=0;
		$insert_data["checked"]="unchecked";
		$insert_data["payment_type_id"]=3;

		$res_insert = q_insert(PNM_Table, $insert_data, $dbshop, __FILE__, __LINE__);
		
		$id_PNM=mysqli_insert_id($dbshop);
		
		q("UPDATE ".PNM_Table." SET ipn_track_id = ".$id_PNM." WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);


		$responseXML="";
		$response="";
		
		$postfields=array();
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationSet_COD";
		$postfields["PNM_id"]=$id_PNM;

		$responseXML = post(PATH."/soa2/", $postfields);
		
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
		
		// SET PAYMENTNOTIFICATIONMESSAGE TO PROCESSED
		if ($response->Ack[0]=="Success")
		{
			q("UPDATE ".PNM_Table." SET processed = 1 WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);			
		}

		// IF EBAY ORDER -> Call ReviseCheckoutStatus
		if ($shops[(int)$order->shop_id[0]]["shop_type"]==2)
		{
			$postfields=array();
			$postfields["API"] = "ebay";
			$postfields["Action"] = "ReviseCheckoutStatus";
			$postfields["mode"] = "paymentupdate";
			$postfields["amount"] = $_POST["payment_total"];
			$postfields["paymentmode"] = "payment";
			$postfields["id_order"] = $_POST["orderid"];
			$responseXML = post(PATH."/soa/", $postfields);
	//mail("nputzing@mapco.de", "Payment to Ebay", $responseXML);		
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				//XML FEHLERHAFT
				show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				//exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			
			// SET PAYMENTNOTIFICATIONMESSAGE TO PROCESSED
			if ($response->Ack[0]!="Success")
			{
				//EBAY_ZAHLUNGSSTATUS KONNTNICHT GESTZT WERDEN
				//show_error()		
			}
		}


	}

	if ($_POST["mode"]=="CreditCard")
	{
		$required=array("orderid" => "numericNN", "payment_total" => "nummericNN", "accounting_date" => "numericNN", "TransactionID" => "textNN" );
		check_man_params($required);

		//GET ORDER
		$response="";
		$responseXML="";
		
		$postfields=array();
		$postfields["API"]="shop";
		$postfields["Action"]="OrderGet";
		$postfields["id_order"]=$_POST["orderid"];
		
		$responseXML = post(PATH."soa/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$order = new SimpleXMLElement($responseXML);
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
		if ($order->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($order, true).print_r($postfields, true));
		}
		
		//SPEICHERE TxnID in shop orders
		q("UPDATE shop_orders SET Payments_TransactionID = '".$_POST["TransactionID"]."' WHERE id_order = ".$_POST["orderid"], $dbshop, __FILE__, __LINE__);


		//CREATE PAYMENTNOTIFICATIONMESSAGE
		$msg = "";
		$msg.="Status=OK";
		$msg.="&Code=0";
		$msg.="&Description=OK";
		$msg.="&PaymentType=1";
		$msg.="&CardType=".$_POST["CardType"];
		$msg.="&Userdata=paygenic_mapco";
		$msg.="&payment_date=".urlencode($_POST["accounting_date"]); //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&gross=".urlencode($_POST["payment_total"]); //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&currency=EUR"; //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&Order_ID=".urlencode($_POST["orderid"]); //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&PayID=".urlencode($_POST["TransactionID"]);
		$msg.="&XID=";
		$msg.="&TransID=".urlencode($_POST["orderid"]);
		$msg.="&payer_firstname=".urlencode((string)$order->bill_firstname[0]); //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&payer_lastname=".urlencode((string)$order->bill_lastname[0]);//IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD

		//WRITE PAYMENTNOTIFICATIONMESSAGE
		$insert_data=array();
		$insert_data["message"]=$msg;
		$insert_data["date_received"]=time();
		$insert_data["processed"]=0;
		$insert_data["checked"]="unchecked";
		$insert_data["payment_type_id"]=5;

		$res_insert = q_insert(PNM_Table, $insert_data, $dbshop, __FILE__, __LINE__);
		
		$id_PNM=mysqli_insert_id($dbshop);
		
		q("UPDATE ".PNM_Table." SET ipn_track_id = ".$id_PNM." WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);


		$responseXML="";
		$response="";
		
		$postfields=array();
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationSet_Paygenic";
		$postfields["id"]=$id_PNM;

		$responseXML = post(PATH."/soa2/", $postfields);
		
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
		
		// SET PAYMENTNOTIFICATIONMESSAGE TO PROCESSED
		if ($response->Ack[0]=="Success")
		{
			q("UPDATE ".PNM_Table." SET processed = 1 WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);			
		}


	}

	if ($_POST["mode"]=="CreditCard_refund")
	{
		$required=array("orderid" => "numericNN", "payment_total" => "nummericNN", "accounting_date" => "numericNN", "ParentTransactionID" => "textNN", "CardType" => "numericNN" );
		check_man_params($required);

		//GET ORDER
		$response="";
		$responseXML="";
		
		$postfields=array();
		$postfields["API"]="shop";
		$postfields["Action"]="OrderGet";
		$postfields["id_order"]=$_POST["orderid"];
		
		$responseXML = post(PATH."soa/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$order = new SimpleXMLElement($responseXML);
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
		if ($order->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($order, true).print_r($postfields, true));
		}


		//CREATE PAYMENTNOTIFICATIONMESSAGE
		$msg = "";
		$msg.="Status=Refunded";
		$msg.="&Code=0";
		$msg.="&Description=OK";
		$msg.="&PaymentType=1";
		$msg.="&CardType=".$_POST["CardType"];
		$msg.="&Userdata=paygenic_mapco";
		$msg.="&payment_date=".urlencode($_POST["accounting_date"]); //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&gross=-".urlencode($_POST["payment_total"]); //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&currency=EUR"; //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&Order_ID=".urlencode($_POST["orderid"]); //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&PayID=".urlencode($_POST["ParentTransactionID"]."-".time());
		$msg.="&ParentTransactionID=".urlencode($_POST["ParentTransactionID"]); //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&XID=";
		$msg.="&TransID=".urlencode($_POST["orderid"]);
		$msg.="&payer_firstname=".urlencode((string)$order->bill_firstname[0]); //IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD
		$msg.="&payer_lastname=".urlencode((string)$order->bill_lastname[0]);//IN MESSAGE VON PAYGENIC NICHT VORHANDENES FELD

		//WRITE PAYMENTNOTIFICATIONMESSAGE
		$insert_data=array();
		$insert_data["message"]=$msg;
		$insert_data["date_received"]=time();
		$insert_data["processed"]=0;
		$insert_data["checked"]="unchecked";
		$insert_data["payment_type_id"]=5;

		$res_insert = q_insert(PNM_Table, $insert_data, $dbshop, __FILE__, __LINE__);
		
		$id_PNM=mysqli_insert_id($dbshop);
		
		q("UPDATE ".PNM_Table." SET ipn_track_id = ".$id_PNM." WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);


		$responseXML="";
		$response="";
		
		$postfields=array();
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationSet_Paygenic";
		$postfields["id"]=$id_PNM;

		$responseXML = post(PATH."/soa2/", $postfields);
		
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
		
		// SET PAYMENTNOTIFICATIONMESSAGE TO PROCESSED
		if ($response->Ack[0]=="Success")
		{
			q("UPDATE ".PNM_Table." SET processed = 1 WHERE id = ".$id_PNM, $dbshop, __FILE__, __LINE__);			
		}
	}
	
?>