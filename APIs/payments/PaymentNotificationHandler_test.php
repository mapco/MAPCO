<?php

	/**********************
		MODES:
		-	OrderAdd
		-	OrderAdjustment
		-	OrderReturn
		
		-	Exchange

		-	Payment
		-	LinkingPayment
	**********************/
	
	/*********************
		NOTIFICATION TYPES
		1	ZAHLUNGEN (Zahlungseingang / Zahlungsausgang)
		2	ORDERS	(Einbuchung / Adjustments bei OrderUpdate oder Rückgaben)
		3	GUTSCHRIFTEN (aus Aktionen etc);
		4	Interne Buchungen auf Zahlungen oder Gutschriften
		5	Interne Buchungen auf Orders
	********************/
	include_once "constants.php";
	
	define("PN_Table", "payment_notifications");
	define("Shop_Order", "shop_orders");

	define('TABLE_SHOP_RETURNS', 'shop_returns2');
	define('TABLE_SHOP_ORDERS_CREDITS', 'shop_orders_credits2');


	$currencies=array();
	$res_curr=q("SELECT * FROM shop_currencies", $dbshop, __FILE__, __LINE__);
	while ($row_curr = mysqli_fetch_assoc($res_curr))
	{
		$currencies[$row_curr["currency_code"]] = $row_curr;
	}



	function update_PaymentStatus ($orderid, $paymentTransactionID, $paymentState, $paymentStateDate, $payments_type_id, $order_deposit)
	{
		$postfields = array();
		$postfields["API"] = "shop";
		$postfields["APIRequest"] = "OrdersPaymentStatusUpdate";
		$postfields["orderid"]=$orderid;
		$postfields["paymentTransactionID"]=$paymentTransactionID;
		$postfields["paymentState"]=$paymentState;
		$postfields["paymentStateDate"]=$paymentStateDate;
		$postfields["payments_type_id"]=$payments_type_id;
		$postfields["order_deposit"]=$order_deposit;
		
		$response = soa2($postfields, __FILE__, __LINE__);
		//	$responseXML=post(PATH."soa2/", $postfields);
/*
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
*/
		if ($response->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true), false);
		}
		//mail ("nputzing@mapco.de", "TEST", print_r($postfields, true).print_r($responseXML, true));
	}

	function getOrderData($orderid, $mode="")
	{
		global $currencies;
		global $dbshop;
		//GET ORDER
		$postfields["API"]="shop";
		$postfields["APIRequest"]="OrderDetailGet_neu";
		$postfields["OrderID"]=$orderid;
		if ($mode!="") $postfields["mode"]=$mode;

		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			$responsefield=array();
			$responsefield["status_id"]=(int)$response->Order[0]->status_id[0];
			$responsefield["ordertype_id"]=(int)$response->Order[0]->ordertype_id[0];
			
			if ($responsefield["status_id"] == 4 || $responsefield["ordertype_id"]==6)
			{
				$responsefield["ordertotalEUR"]=0;
				$responsefield["ordertotal"]=0;
			}
			else
			{
				$responsefield["ordertotalEUR"]=(float)str_replace(",", ".",(string)$response->Order[0]->completeTotalGross[0]);
				$responsefield["ordertotal"]=(float)str_replace(",", ".",(string)$response->Order[0]->completeTotalGrossFC[0]);
			}
			$responsefield["shop_id"]=(int)$response->Order[0]->shop_id[0];
			$responsefield["user_id"]=(int)$response->Order[0]->customer_id[0];
			

			$responsefield["firstmod"]=(int)$response->Order[0]->firstmod[0];
			$responsefield["currency"]=(string)$response->Order[0]->Currency_Code[0];
			$responsefield["buyer_firstname"]=(string)$response->Order[0]->bill_adr_firstname[0];
			$responsefield["buyer_lastname"]=(string)$response->Order[0]->bill_adr_lastname[0];
			$responsefield["payments_type_id"]=(string)$response->Order[0]->payments_type_id[0];
			$responsefield["Payments_TransactionID"]=(string)$response->Order[0]->Payments_TransactionID[0];
			$responsefield["Payments_TransactionState"]=(string)$response->Order[0]->Payments_TransactionState[0];
			$responsefield["Payments_TransactionStateDate"]=(int)$response->Order[0]->Payments_TransactionStateDate[0];
			$responsefield["order_deposit"]=(float)$response->Order[0]->order_deposit[0];
			
			
			$orderitems=array();
			$exchangerate=0;
			for ($i=0; isset($response->Order[0]->OrderItems[0]->Item[$i]); $i++)
			{
				$item = $response->Order[0]->OrderItems[0]->Item[$i];
				
				$orderitems[(int)$item->OrderItemID[0]]=$item;
				$exchangerate =(float)$item->OrderItemExchangeRateToEUR[0];
			}
			$responsefield["orderitems"]=$orderitems;
			
			if ($exchangerate==0)

			{
				$exchangerate=$currencies[$responsefield["currency"]]["exchange_rate_to_EUR"];
			}
			
			$responsefield["exchangerate"]=$exchangerate;
			
			//GET SHOPTYPE
			$res_shoptype = q("SELECT * FROM shop_shops WHERE id_shop = ".$responsefield["shop_id"], $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_shoptype)==0)
			{
				//KEIN SHOP mit ID gefunden
				show_error(9757, 7, __FILE__, __LINE__, "id_shop: ".$responsefield["shop_id"]. "OrderID :".$orderid);
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
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}

	}

	function getLastOrderTotal($orderid)
	{
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentNotificationLastOrderTotalGet";
		$postfields["orderid"]=$orderid;

		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			return (float)$response->ordertotal[0];
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}
	}

	function getLastOrderTotalID($orderid)
	{
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentNotificationLastOrderIDGet";
		$postfields["orderid"]=$orderid;

		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			if ((int)$response->id_PN[0]==0)
			{
				return false;
			}
			else
			{
				return (int)$response->id_PN[0];
			}
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}

	}

	function getLastOrderDeposit($orderid)
	{
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentNotificationLastOrderDepositGet";
		$postfields["orderid"]=$orderid;

		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			return (float)$response->orderdeposit[0];
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}

	}

	function getLastUserDeposit($userid)
	{
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentNotificationLastUserDepositGet";
		$postfields["userid"]=$userid;

		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			return (float)$response->user_deposit[0];
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}
	}


	function getLastPaymentTotal($transactionID)
	{
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentNotificationLastPaymentTotalGet";
		$postfields["transactionID"]=$transactionID;

		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			if ((string)$response->has_total[0]=="true")
			{
				return (float)$response->payment_total[0];
			}
			else
			{
				show_error(9802, 9, __FILE__, __LINE__, "PaymentTransactionID ".$transactionID);
				exit;
			}
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}

	}

	function getLastPaymentDeposit($transactionID)
	{
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentNotificationLastPaymentDepositGet";
		$postfields["transactionID"]=$transactionID;

		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			if ((string)$response->has_deposit[0]=="true")
			{
				return (float)$response->payment_deposit[0];
			}
			else
			{
				show_error(9802, 9, __FILE__, __LINE__, "PaymentTransactionID ".$transactionID." LINE: ".__LINE__);
				exit;
			}
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}

	}

/*	
	function getLastPaymentID($transactionID)
	{
		global $dbshop;

		$res_paymentdeposit = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID ='".$transactionID."' AND (notification_type = 1 OR notification_type = 4) ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_paymentdeposit)==0)
		{
			return false;
		}
		
		$PN_paymentdeposit = mysqli_fetch_assoc($res_paymentdeposit);	
		
		return $PN_paymentdeposit["id_PN"];
	}
*/

	function getLastPayment($transactionID)
	{
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentNotificationLastPaymentGet";
		$postfields["TransactionID"]=$transactionID;

		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			if (isset($response->payment[0]))
			{
				return $response->payment[0];
			}
			else
			{
				show_error(9802, 9, __FILE__, __LINE__, "PaymentNotificationLastPaymentGet - >PaymentTransactionID ".$transactionID." LINE:".__LINE__);
				exit;
			}
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}

	}
	
	
	function getPaymentOrders($transactionID)
	{
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentGet";
		$postfields["TransactionID"]=$transactionID;
		$response = soa2($postfields, __FILE__, __LINE__);
		if ($response->Ack[0]=="Success")
		{
			$orderids=array();
			
			if (!isset($response->orders[0]))
			{
				//return $orderids;			
			}
			else
			{
				$i=0;
				while (isset($response->orders[0]->order_id[$i]))
				{
					$orderids[]=(int)$response->orders[0]->order_id[$i];
					$i++;	
				}
			}
			return $orderids;
			
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}
		
	}
	
	function returnsOrderIdsGet($returnid)
	{
		$postfields = array();
		$postfields["API"] = "shop";
		$postfields["APIRequest"] = "OrderReturnGet";
		$postfields["return_id"]=$returnid;
		$response = soa2($postfields, __FILE__, __LINE__);
		if ($response->Ack[0]=="Success")
		{
			$orderids=array();
			
			if (!isset($response->orderreturn[0]->returnitems[0]->returnitem[0]))
			{
				//return $orderids;			
			}
			else
			{
				$i=0;
				while (isset($response->orderreturn[0]->returnitems[0]->returnitem[$i]))
				{
					$orderids[]=(int)$response->orderreturn[0]->returnitems[0]->returnitem[$i]->order_id[0];
					$i++;	
				}
			}
			return $orderids;
			
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			exit;
		}
		
	}
	
	function orderPaymentsGet($orderid)
	{
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "OrderPaymentsGet";
		$postfields["orderid"]=$orderid;
		$response = soa2($postfields, __FILE__, __LINE__);
//print_r($response);		
		$returnfield = array();
		
		if ((string)$response->Ack[0]=="Success")
		{

			for ($i=0; isset($response->paymentdata[0]->transaction[$i]); $i++ )
			{
				if (isset($response->paymentdata[0]->transaction[$i]->transactionID[0]))
				{
					$transactionID = (string)$response->paymentdata[0]->transaction[$i]->transactionID[0];
					
					for ($j=0; isset($response->paymentdata[0]->transaction[$i]->accounting[$j]); $j++ )
					{
						//NOTIFICATION TYPE
						$returnfield[$transactionID][$j]["notification_type"]=(int)$response->paymentdata[0]->transaction[$i]->accounting[$j]->notification_type[0];
						//PAYMENT TOTAL
						if ($returnfield[$transactionID][$j]["notification_type"]==1)
						{
							$returnfield[$transactionID][$j]["total_EUR"]=round((float)$response->paymentdata[0]->transaction[$i]->accounting[$j]->total[0]/(float)$response->paymentdata[0]->transaction[$i]->accounting[$j]->exchange_rate_from_EUR[0], 2);
						}
						else
						{
							$returnfield[$transactionID][$j]["total_EUR"]=0;
						}
						//DEPOSIT EUR
						$returnfield[$transactionID][$j]["deposit_EUR"]=(float)$response->paymentdata[0]->transaction[$i]->accounting[$j]->deposit_EUR[0];
						//PaymentTypeID
						$returnfield[$transactionID][$j]["payment_type_id"]=(int)$response->paymentdata[0]->transaction[$i]->accounting[$j]->payment_type_id[0];
						//id_PN
						$returnfield[$transactionID][$j]["id_PN"]=(int)$response->paymentdata[0]->transaction[$i]->accounting[$j]->id_PN[0];
						//orderid
						$returnfield[$transactionID][$j]["order_id"]=(int)$response->paymentdata[0]->transaction[$i]->accounting[$j]->order_id[0];
					}
				}
			}
			return $returnfield;
		}
		else
		{
			return false;
		}	
	}

//***********************************************************************************************

	$required=array("mode" => "textNN");
	
	check_man_params($required);
	
	$processed=false;

	
/************************************************************************************************************
ORDER - OrderAdd
************************************************************************************************************/
	if ($_POST["mode"]=="OrderAdd")
	{
		$required=array("orderid" =>"numericNN", "order_event_id" => "numericNN");
		check_man_params($required);
	
		// CHECK IF ENTRY ALREADY EXISTS
		$res_check = q("SELECT * FROM ".PN_Table." WHERE order_id = ".$_POST["orderid"]." AND notification_type = 2 AND reason = 'OrderAdd'", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)>0)
		{
			//EINTRAG EXISTIERT BEREITS
			show_error(9798, 9, __FILE__, __LINE__, "OrderID: ".$_POST["orderid"]." EventID: ".$_POST["order_event_id"]);
			exit;
		}
		
		$order=getOrderData($_POST["orderid"], "single");
		
		$accounting=$order["ordertotalEUR"]*(-1);
		
		//$accounting*=1/$order["exchangerate"];
		
		$user_deposit=getLastUserDeposit($order["user_id"]);
		$user_deposit+=$accounting;

		//ORDER SCHREIBEN in PaymentNotifications 
		$insert_data=array();
		$insert_data["f_id"]=$_POST["order_event_id"];
		$insert_data["shop_id"]=$order["shop_id"];
		$insert_data["PN_date"]=time();
		$insert_data["accounting_date"]=$order["firstmod"];
		$insert_data["notification_type"]=2;
		$insert_data["reason"]="OrderAdd";
		$insert_data["order_id"]=$_POST["orderid"];
		$insert_data["total"]=$order["ordertotal"];
		$insert_data["currency"]=$order["currency"];
		$insert_data["exchange_rate_from_EUR"]=$order["exchangerate"];
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["deposit_EUR"]=$accounting;
		$insert_data["user_id"]=$order["user_id"];
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=$order["payments_type_id"];
		$insert_data["buyer_lastname"]=$order["buyer_lastname"];
		$insert_data["buyer_firstname"]=$order["buyer_firstname"];
		
		$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
		
		update_PaymentStatus ($_POST["orderid"], $order["Payments_TransactionID"], "Created", $order["firstmod"], $order["payments_type_id"], $accounting);	
		

		//WENN EBAYORDER -> FINDE VORHANDE PAYMENTS 
		if ($order["shoptype"]==2)
		{
			//SUCHE NACH TRANSACTIONID im Feld payment_notifications.orderTransactionID aus einer der TransactionID aus order
			if (isset($order["orderitems"]))
			{
				foreach ($order["orderitems"] as $itemid => $orderitem)
				//for ($i=0; isset($response->Order[0]->OrderItems[0]->Item[$i]); $i++)

				{
					$foreignTranactionID = $orderitem->OrderItemforeign_transactionID[0];
					//GET PAYMENTNOTIFICATION (payment) for TransactionID
					if ($foreignTranactionID!="" && $foreignTranactionID!=0)
					{
						$res_notification = q("SELECT * FROM ".PN_Table." WHERE orderTransactionID = '".$foreignTranactionID."' AND order_id = 0 AND notification_type = 1", $dbshop, __FILE__, __LINE__);
						while ($row_notification=mysqli_fetch_assoc($res_notification))
						{
							//$accounting=(float)$row_notification["total"]*(1/$row_notification["exchange_rate_from_EUR"])*-1;
							if ($row_notification["deposit_EUR"]!=0) //PREVENT ACCOUNTING FOR CREATED && PENDING
							{
								$accounting = $row_notification["deposit_EUR"];
								$accounting*=-1;

								$user_deposit=getLastUserDeposit(0);
								$user_deposit+=$accounting;
								$payment_deposit=getLastPaymentDeposit($row_notification["paymentTransactionID"]);
								$payment_deposit+=$accounting;
								
								//SCHREIBE SYSTEMBUCHUNG - LINKING PAYMENT(FROM)
								$insert_data=array();
								$insert_data["f_id"]=$row_notification["id_PN"];
								$insert_data["shop_id"]=0;
								$insert_data["PN_date"]=time();
								$insert_data["accounting_date"]=$row_notification["accounting_date"];
								$insert_data["notification_type"]=4;
								$insert_data["reason"]="Linking Payment";
								$insert_data["order_id"]=0;
								$insert_data["total"]=0;
								$insert_data["currency"]=$row_notification["currency"];
								$insert_data["exchange_rate_from_EUR"]=$row_notification["exchange_rate_from_EUR"];
								$insert_data["accounting_EUR"]=$accounting;
								$insert_data["deposit_EUR"]=$payment_deposit;
								$insert_data["user_id"]=0;
								$insert_data["user_deposit_EUR"]=$user_deposit;
								$insert_data["payment_type_id"]=$row_notification["payment_type_id"];
								$insert_data["buyer_lastname"]=$row_notification["buyer_lastname"];
								$insert_data["buyer_firstname"]=$row_notification["buyer_firstname"];
								$insert_data["payment_mail"]=$row_notification["payment_mail"];
								$insert_data["payer_id"]=$row_notification["payer_id"];
								$insert_data["receiver_mail"]=$row_notification["receiver_mail"];
								$insert_data["receiver_id"]=$row_notification["receiver_id"];
								$insert_data["paymentTransactionID"]=$row_notification["paymentTransactionID"];
								$insert_data["parentPaymentTransactionID"]=$row_notification["parentPaymentTransactionID"];
								$insert_data["payment_note"]=$row_notification["payment_note"];
								
								$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
								
								$id_PN = mysqli_insert_id($dbshop);
								
								$accounting*=-1;
								$user_deposit=getLastUserDeposit($order["user_id"]);
								$user_deposit+=$accounting;
								
								//SCHREIBE SYSTEMBUCHUNG -LINKING PAYMENT(TO)
								$insert_data=array();
								$insert_data["f_id"]=$id_PN;
								$insert_data["shop_id"]=0;
								$insert_data["PN_date"]=time();
								$insert_data["accounting_date"]=$row_notification["accounting_date"];
								$insert_data["notification_type"]=4;
								$insert_data["reason"]="Linking Payment";
								$insert_data["order_id"]=0;
								$insert_data["total"]=0;
								$insert_data["currency"]=$row_notification["currency"];
								$insert_data["exchange_rate_from_EUR"]=$row_notification["exchange_rate_from_EUR"];
								$insert_data["accounting_EUR"]=$accounting;
								$insert_data["deposit_EUR"]=$accounting;
								$insert_data["user_id"]=$order["user_id"];
								$insert_data["user_deposit_EUR"]=$user_deposit;
								$insert_data["payment_type_id"]=$row_notification["payment_type_id"];
								$insert_data["buyer_lastname"]=$row_notification["buyer_lastname"];
								$insert_data["buyer_firstname"]=$row_notification["buyer_firstname"];
								$insert_data["payment_mail"]=$row_notification["payment_mail"];
								$insert_data["payer_id"]=$row_notification["payer_id"];
								$insert_data["receiver_mail"]=$row_notification["receiver_mail"];
								$insert_data["receiver_id"]=$row_notification["receiver_id"];
								$insert_data["paymentTransactionID"]=$row_notification["paymentTransactionID"];
								$insert_data["parentPaymentTransactionID"]=$row_notification["parentPaymentTransactionID"];
								$insert_data["payment_note"]=$row_notification["payment_note"];
	
								$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
								
								$id_PN = mysqli_insert_id($dbshop);
								
								//VERRECHNUNG ZAHLUNG mit ORDER
								$last_orderdeposit=getLastOrderDeposit($_POST["orderid"])*-1;
								$payment_deposit=$insert_data["deposit_EUR"];
								if ($last_orderdeposit<=0)
								{
									$accounting=0;
								}
								else
								{
									if ($last_orderdeposit>=$payment_deposit)
									{
										$accounting = $payment_deposit*-1;
									}
									else
									{
										$accounting = $last_orderdeposit*-1;
									}
								}
								$payment_deposit+=$accounting;
								$user_deposit=$insert_data["user_deposit_EUR"]+$accounting;
	
								if ($accounting!=0)
								{
									//SCHREIBE SYSTEMBUCHUNG - PAYMENT(FROM)
									$insert_data=array();
									$insert_data["f_id"]=$id_PN;
									$insert_data["shop_id"]=0;
									$insert_data["PN_date"]=time();
									$insert_data["accounting_date"]=$row_notification["accounting_date"];
									$insert_data["notification_type"]=4;
									$insert_data["reason"]="Payment";
									$insert_data["order_id"]=0;
									$insert_data["total"]=0;
									$insert_data["currency"]=$row_notification["currency"];
									$insert_data["exchange_rate_from_EUR"]=$row_notification["exchange_rate_from_EUR"];
									$insert_data["accounting_EUR"]=$accounting;
									$insert_data["deposit_EUR"]=$payment_deposit;
									$insert_data["user_id"]=$order["user_id"];
									$insert_data["user_deposit_EUR"]=$user_deposit;
									$insert_data["payment_type_id"]=$row_notification["payment_type_id"];
									$insert_data["buyer_lastname"]=$row_notification["buyer_lastname"];
									$insert_data["buyer_firstname"]=$row_notification["buyer_firstname"];
									$insert_data["payment_mail"]=$row_notification["payment_mail"];
									$insert_data["payer_id"]=$row_notification["payer_id"];
									$insert_data["receiver_mail"]=$row_notification["receiver_mail"];
									$insert_data["receiver_id"]=$row_notification["receiver_id"];
									$insert_data["paymentTransactionID"]=$row_notification["paymentTransactionID"];
									$insert_data["parentPaymentTransactionID"]=$row_notification["parentPaymentTransactionID"];
									$insert_data["payment_note"]=$row_notification["payment_note"];
		
									$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
									
									$id_PN = mysqli_insert_id($dbshop);
									
									$accounting*=-1;
									$user_deposit=$insert_data["user_deposit_EUR"]+$accounting;
									$order_deposit=getLastOrderDeposit($_POST["orderid"]);
									$order_deposit+=$accounting;
		
									//SCHREIBE SYSTEMBUCHUNG - PAYMENT(TO)
									$insert_data=array();
									$insert_data["f_id"]=$id_PN;
									$insert_data["shop_id"]=$order["shop_id"];
									$insert_data["PN_date"]=time();
									$insert_data["accounting_date"]=$row_notification["accounting_date"];
									$insert_data["notification_type"]=5;
									$insert_data["reason"]="Payment";
									$insert_data["order_id"]=$_POST["orderid"];
									$insert_data["total"]=0;
									$insert_data["currency"]=$row_notification["currency"];
									$insert_data["exchange_rate_from_EUR"]=$row_notification["exchange_rate_from_EUR"];
									$insert_data["accounting_EUR"]=$accounting;
									$insert_data["deposit_EUR"]=$order_deposit;
									$insert_data["user_id"]=$order["user_id"];
									$insert_data["user_deposit_EUR"]=$user_deposit;
									$insert_data["payment_type_id"]=$row_notification["payment_type_id"];
									$insert_data["buyer_lastname"]=$row_notification["buyer_lastname"];
									$insert_data["buyer_firstname"]=$row_notification["buyer_firstname"];
		
									$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
									
									$id_PN = mysqli_insert_id($dbshop);
	
									//UPDATE SHOP_ORDER PAYMENTSTATUS
									update_PaymentStatus ($_POST["orderid"], $row_notification["paymentTransactionID"], $row_notification["reason"], $row_notification["accounting_date"], $row_notification["payment_type_id"], getLastOrderDeposit($_POST["orderid"]));	
	
									// SET FLAG FOR NO UPDATE 
									q("UPDATE shop_orders SET payment_updated = 1 WHERE id_order = ".$_POST["orderid"], $dbshop, __FILE__, __LINE); 
									//PAYPAL BUYER NOTE ÜBERTRAGEN
									if ($row_notification["payment_note"]!="")
									{
									//mail("nputzing@mapco.de", "PayPal_BuyerNote ORDERADD", "OrderID ".$_POST["orderid"]." BuyerNote ".$row_notification["payment_note"]);
										q("UPDATE ".Shop_Order." SET PayPal_BuyerNote = '".mysqli_real_escape_string($dbshop, $row_notification["payment_note"])."' WHERE id_order = ".$_POST["orderid"], $dbshop, __FILE__,__LINE__);
									}
								} // IF ACCOUNTING ==0
							}
						} //
					}
				}
			}
		} // IF SHOPTYPE==2
				
	} // MODE OrderAdd

//********************************************************************

	if ($_POST["mode"]=="OrderAdjustment")
	{
		$required=array("orderid" =>"numericNN", "order_event_id" => "numericNN");
		check_man_params($required);

		$order=getOrderData($_POST["orderid"], "single");

		$last_ordertotal=getLastOrderTotal($_POST["orderid"]);


		
//CHECK IF SOMETHING HAS CHNAGED
		if ($last_ordertotal != $order["ordertotal"])
		{

			$act_ordertotalEUR=$order["ordertotalEUR"];
			$act_ordertotal=$order["ordertotal"];
			
		//	$accounting = ($last_ordertotal/$order["exchangerate"])-$act_ordertotalEUR;
			$accounting = round(($last_ordertotal-$act_ordertotal)/$order["exchangerate"],2);
			
			$last_orderdeposit=getLastOrderDeposit($_POST["orderid"]);
			$order_deposit=$last_orderdeposit+$accounting;
			
			$last_userdeposit=getLastUserDeposit($order["user_id"]);
			$user_deposit=$last_userdeposit+$accounting;
	
			$insert_data=array();
			$insert_data["f_id"]=$_POST["order_event_id"];
			$insert_data["shop_id"]=$order["shop_id"];
			$insert_data["PN_date"]=time();
			$insert_data["accounting_date"]=time();
			$insert_data["notification_type"]=2;
			$insert_data["reason"]="OrderAdjustment";
			$insert_data["order_id"]=$_POST["orderid"];
			$insert_data["total"]=$order["ordertotal"];
			$insert_data["currency"]=$order["currency"];
			$insert_data["exchange_rate_from_EUR"]=$order["exchangerate"];
			$insert_data["accounting_EUR"]=$accounting;
			$insert_data["deposit_EUR"]=$order_deposit;
			$insert_data["user_id"]=$order["user_id"];
			$insert_data["user_deposit_EUR"]=$user_deposit;
			$insert_data["payment_type_id"]=0;
			$insert_data["buyer_lastname"]=$order["buyer_lastname"];
			$insert_data["buyer_firstname"]=$order["buyer_firstname"];
	
			$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
			
			update_PaymentStatus ($_POST["orderid"], $order["Payments_TransactionID"], $order["Payments_TransactionState"], $order["Payments_TransactionStateDate"], $order["payments_type_id"], $order_deposit);	

			//CHECK IF ORDER IS EXCHANGE -> DO SWAP DEPOSIT
			if ( $order['ordertype_id'] == 4 )
			{
				$res_check = q("SELECT a.id_shop_order_credit FROM ".TABLE_SHOP_ORDERS_CREDITS." as a, ".TABLE_SHOP_RETURNS." as b  WHERE b.exchange_order_id = ".$_POST['orderid']." AND a.return_id = b.id_return", $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows( $res_check ) == 0 )
				{
					//show_error;
					exit;	
				}
				$row_check = mysqli_fetch_assoc( $res_check );
				$postfields 				= array();
				$postfields['API'] 			= 'payments';
				$postfields['APIRequest'] 	= 'PaymentNotificationHandler_test';
				$postfields['mode'] 		= 'OrderExchangeDepositSwap';
				$postfields['creditid'] 	= $row_check['id_shop_order_credit'];

			echo soa2($postfields, __FILE__, __LINE__, 'xml');
			}
		}
		else
		{
			//echo"NOTHING CHNAGED";
		}
	} // MODE OrderAdjustment
	
//******************************************************************************************

	if ($_POST["mode"]=="OrderReturn")
	{
		//$required=array("orderid" =>"numericNN", "returnid" => "numericNN", "order_event_id" => "numericNN");
		$required=array("returnid" => "numericNN", "order_event_id" => "numericNN");
		check_man_params($required);
		
//CHECK IF SOMETHING HAS CHANGED

		// get order_ids from returned items and sum of returns
		$orderids = returnsOrderIdsGet($_POST["returnid"]);		
		foreach ($orderids as $orderid)	
		{
			$order=getOrderData($orderid,"single");
	
			$last_ordertotal=getLastOrderTotal($orderid);

			$act_ordertotalEUR=$order["ordertotalEUR"];
			
			$last_ordertotalEUR=round($last_ordertotal/$order["exchangerate"],2);
			$accounting = $last_ordertotalEUR-$act_ordertotalEUR;
//echo "§".$order["ordertotal"]."§%".$last_ordertotal;
			if ($last_ordertotal != $order["ordertotal"])
			{

				$last_orderdeposit=getLastOrderDeposit($orderid);
				$order_deposit=$last_orderdeposit+$accounting;
				
				$last_userdeposit=getLastUserDeposit($order["user_id"]);
				$user_deposit=$last_userdeposit+$accounting;
		
				$insert_data=array();
				$insert_data["f_id"]=$_POST["order_event_id"];
				$insert_data["shop_id"]=$order["shop_id"];
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=time();
				$insert_data["notification_type"]=2;
				$insert_data["reason"]="OrderReturn";
				$insert_data["order_id"]=$orderid;
				$insert_data["total"]=$order["ordertotal"];
				$insert_data["currency"]=$order["currency"];
				$insert_data["exchange_rate_from_EUR"]=$order["exchangerate"];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$order_deposit;
				$insert_data["user_id"]=$order["user_id"];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=0;
				$insert_data["buyer_lastname"]=$order["buyer_lastname"];
				$insert_data["buyer_firstname"]=$order["buyer_firstname"];
		
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
			}
		}

	} // MODE OrdeReturn

//******************************************************************************************

	if ($_POST["mode"]=="Exchange")
	{
		$required=array("returnid" =>"numericNN", "order_event_id" => "numericNN");
		check_man_params($required);

		$postfields["API"]="shop";
		$postfields["APIRequest"]="OrderReturnGet";
		$postfields["return_id"]=$_POST["returnid"];

		$response_return = soa2($postfields);
		
		if ($response_return->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response_return, true));
			exit;
		}

		if ($response_return->orderreturn[0]->return_type[0]!="exchange")
		{
			//RETURN IST KEIN EXCHANGE
			show_error(9799, 8, __FILE__, __LINE__, "ReturnID".$_POST["return_id"]);
			exit;
		}

		$exchange_orderid = (int)$response_return->orderreturn[0]->exchange_order_id[0];
	//	$orderid = (int)$response_return->orderreturn[0]->order_id[0];
	
	//	$order=getOrderData($orderid);
		
		if ($exchange_orderid == 0)
		{
			//FEHLER, EXCHANGEORDERID DARF NICHT 0 SEIN
			show_error(9800, 7, __FILE__, __LINE__, "ReturnID".$_POST["return_id"]);
			exit;
		}
		
		//CHECK EXHANGE ORDER VORHANDEN?
		$res_check=q("SELECT * FROM shop_orders WHERE id_order = ".$exchange_orderid, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==0)
		{
			//EXCHANGEORDER NICHT VORHANDEN
			show_error(9801, 7, __FILE__, __LINE__, "ReturnID".$_POST["return_id"]." UmtauschOrderID: ".$exchange_orderid);
			exit;
		}
		
		
		//GET TOTAL OF RETURNITEMS
		$returntotal=array();
		$returntotalEUR = array();
		for ($i=0; isset($response_return->orderreturn[0]->returnitems[0]->returnitem[$i]); $i++)		
		{
			$order_id = (int)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->order_id[0];
			if (!isset($returntotal[$order_id]))
			{
				$returntotal[$order_id]=0;
				$returntotalEUR[$order_id]=0;	
			}
			
			$returntotal[$order_id]+=(float)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->price[0]*(int)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->amount[0];
			$returntotalEUR[$order_id]+=(float)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->price[0]*(int)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->amount[0]*(1/(float)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->exchange_rate_to_EUR[0]);
			//$exchangerate=(float)$response_return->orderreturn[0]->returnitem[$i]->exchange_rate_to_EUR[0];
		}

		$orderids=array();
		// get order_ids from returned items and sum of returns
		$orderids = returnsOrderIdsGet($_POST["returnid"]);		
		

		foreach ($orderids as $orderid)	
		{
			//GET SUM OF SWAPS OF ORDERID for Returnid
			$swap_sum=0;
			$res = q("SELECT * FROM ".PN_Table." WHERE notification_type = 5 AND reason = 'ExchangeSwapOrderDeposit' AND reason_detail = '".$_POST["returnid"]."' AND order_id = ".$orderid, $dbshop, __FILE__, __LINE__);
			while ($row=mysqli_fetch_assoc($res))
			{
				$swap_sum+=$row["accounting_EUR"];
			}
			$swap_sum*=-1;
			
			if ($swap_sum!=$returntotalEUR[$orderid])
			{
				$accounting=$swap_sum-$returntotalEUR[$orderid];
			
			
				$order=getOrderData($orderid,"single");

	//		$act_ordertotalEUR=$order["ordertotalEUR"];
			
	//		$last_ordertotal=getLastOrderTotal($orderid);
	//		$last_ordertotalEUR = $last_ordertotal*(1/$order["exchangerate"]);
				
	//		if ($last_ordertotalEUR!=$act_ordertotalEUR)
	//		{
	//			$accounting = $last_ordertotalEUR-$act_ordertotalEUR;
				
				$last_ordertotalID=getLastOrderTotalID($orderid);
				$act_ordertotal=$order["ordertotal"];
		
				
				$last_orderdeposit=getLastOrderDeposit($orderid);
				$order_deposit=$last_orderdeposit+$accounting;
				
				$last_userdeposit=getLastUserDeposit($order["user_id"]);
				$user_deposit=$last_userdeposit+$accounting;
/*
				//ORDERADJUSTMENT
				$insert_data=array();
				$insert_data["f_id"]=$_POST["order_event_id"];
				$insert_data["shop_id"]=$order["shop_id"];
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=time();
				$insert_data["notification_type"]=2;
				$insert_data["reason"]="OrderExchange";
				$insert_data["order_id"]=$orderid;
				$insert_data["total"]=$order["ordertotal"];
				$insert_data["currency"]=$order["currency"];
				$insert_data["exchange_rate_from_EUR"]=$order["exchangerate"];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$order_deposit;
				$insert_data["user_id"]=$order["user_id"];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=0;
				$insert_data["buyer_lastname"]=$order["buyer_lastname"];
				$insert_data["buyer_firstname"]=$order["buyer_firstname"];
		
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
				
				$id_PN = mysqli_insert_id($dbshop);
		
				//buche deposits
		
				$accounting*=-1;
				$order_deposit+=$accounting;
				$user_deposit+=$accounting;
			*/	
				//SCHREIBE SYSTEMBUCHUNG - EXCHANGE(FROM)
				$insert_data=array();
				$insert_data["f_id"]=$last_ordertotalID;
				$insert_data["shop_id"]=$order["shop_id"];
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=time();
				$insert_data["notification_type"]=5;
				$insert_data["reason"]="ExchangeSwapOrderDeposit";
				$insert_data["reason_detail"]=$_POST["returnid"];
				$insert_data["order_id"]=$orderid;
				$insert_data["total"]=0;
				$insert_data["currency"]=$order["currency"];
				$insert_data["exchange_rate_from_EUR"]=$order["exchangerate"];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$order_deposit;
				$insert_data["user_id"]=$order["user_id"];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=0;
				$insert_data["buyer_lastname"]=$order["buyer_lastname"];
				$insert_data["buyer_firstname"]=$order["buyer_firstname"];
		
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
				
				$id_PN = mysqli_insert_id($dbshop);
				
				$accounting*=-1;
		
				$exchange_order=getOrderData($exchange_orderid, "single");
		
				//GET LAST ORDERTOTAL FROM EXCHANGE
				$last_ex_ordertotal = getLastOrderTotal($exchange_orderid);
				
				//GET LAST ORDERDEPOSIT FROM EXCHANGE
				$last_ex_orderdeposit = getLastOrderDeposit($exchange_orderid);
				
				$exchange_order_deposit=$last_ex_orderdeposit+$accounting;
				
				//GET LAST USERDEPOSIT
				$user_deposit=getLastUserDeposit($order["user_id"]);
				$user_deposit+=$accounting;
		
		
				//SCHREIBE SYSTEMBUCHUNG - EXCHANGE(TO)
				$insert_data=array();
				$insert_data["f_id"]=$id_PN;
				$insert_data["shop_id"]=$exchange_order["shop_id"];
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=time();
				$insert_data["notification_type"]=5;
				$insert_data["reason"]="ExchangeSwapOrderDeposit";
				$insert_data["reason_detail"]=$_POST["returnid"];
				$insert_data["order_id"]=$exchange_orderid;
				$insert_data["total"]=0;
				$insert_data["currency"]=$exchange_order["currency"];
				$insert_data["exchange_rate_from_EUR"]=$exchange_order["exchangerate"];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$exchange_order_deposit;
				$insert_data["user_id"]=$exchange_order["user_id"];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=0;
				$insert_data["buyer_lastname"]=$exchange_order["buyer_lastname"];
				$insert_data["buyer_firstname"]=$exchange_order["buyer_firstname"];
				
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
			}
		}
	} // MODE EXCHANGE


	if ($_POST["mode"]=="OrderExchangeDepositSwap")
	{
		//$required=array("creditid" =>"numericNN", "order_event_id" => "numericNN");
		$required=array("creditid" =>"numericNN");
		check_man_params($required);

		$postfields["API"]			= "shop";
		$postfields["APIRequest"]	= "OrderCreditGet";
		$postfields["id_credit"]	= $_POST["creditid"];

		$credit = soa2($postfields);
		
		if ( $credit->Ack[0] != "Success" )
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($credit, true));
			exit;
		}

		if ( $credit->credit[0]->type[0] != "exchange" )
		{
			//RETURN IST KEIN EXCHANGE
			show_error(9799, 8, __FILE__, __LINE__, "Credit".$_POST["creditid"] );
			exit;
		}

		//GET EXCHANGEORDERID
		$index = 0;
		$exchange_orderid = 0;
		
		while ( isset( $credit->credit[0]->creditpositions[0]->creditposition[$index] ))
		{
			if ( (int)$credit->credit[0]->creditpositions[0]->creditposition[$index]->reason_id[0] == 1 ) 
			{
				if ( (int)$credit->credit[0]->creditpositions[0]->creditposition[$index]->return[0]->exchange_order_id[0] != 0)
				{
					$exchange_orderid = (int)$credit->credit[0]->creditpositions[0]->creditposition[$index]->return[0]->exchange_order_id[0];
					$return = $credit->credit[0]->creditpositions[0]->creditposition[$index]->return;
				}
			}
			$index ++;
		}
		

		if ($exchange_orderid == 0)
		{
			//FEHLER, EXCHANGEORDERID DARF NICHT 0 SEIN
			show_error(9800, 7, __FILE__, __LINE__, "CreditID".$_POST["creditid"]);
			exit;
		}
		
		//CHECK EXHANGE ORDER VORHANDEN?
		$res_check=q("SELECT * FROM shop_orders WHERE id_order = ".$exchange_orderid, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==0)
		{
			//EXCHANGEORDER NICHT VORHANDEN
			show_error(9801, 7, __FILE__, __LINE__, "CreditID".$_POST["creditid"]." UmtauschOrderID: ".$exchange_orderid);
			exit;
		}
	

		
		//GET TOTAL OF RETURNITEMS
		$returntotal=array();
		$returntotalEUR = array();
		for ($i=0; isset($return->returnitems[0]->returnitem[$i]); $i++)		
		{
			$order_id = (int)$return->order_id[0];
			if (!isset($returntotal[$order_id]))
			{
				$returntotal[$order_id]=0;
				$returntotalEUR[$order_id]=0;	
			}
			
			$returntotal[$order_id]+=(float)$return->returnitems[0]->returnitem[$i]->returnitem_price[0] * (int)$return->returnitems[0]->returnitem[$i]->amount[0];
			$returntotalEUR[$order_id]+=(float)$return->returnitems[0]->returnitem[$i]->returnitem_price[0] * (int)$return->returnitems[0]->returnitem[$i]->amount[0] * (1/(float)$return->returnitems[0]->returnitem[$i]->returnitem_exchange_rate_to_EUR[0]);
		}

		$orderids=array();
		
		// get order_ids from returned items and sum of returns
		$orderids = returnsOrderIdsGet( (int)$return->id_return[0] );		
		
		foreach ($orderids as $orderid)	
		{
			
			$order=getOrderData($orderid,"single");

			//GET LAST ORDERTOTAL FROM EXCHANGE
			$last_ex_ordertotal = getLastOrderTotal($exchange_orderid);
			
			//GET LAST ORDERDEPOSIT FROM EXCHANGE
			$last_ex_orderdeposit = getLastOrderDeposit($exchange_orderid);
			
			$last_ordertotalID=getLastOrderTotalID($orderid);
			$act_ordertotal=$order["ordertotal"];
	
			
			$last_orderdeposit=getLastOrderDeposit($orderid);
			
			echo "LAST ORDERDEPOSIT: ".$last_orderdeposit;
			
			$last_userdeposit=getLastUserDeposit($order["user_id"]);

echo "LAST USERDEPOSIT: ".$last_userdeposit;

			if ($last_ex_orderdeposit < 0 )
			{
				//GET SUM OF SWAPS OF ORDERID for Returnid
				$swap_sum=0;
				$res = q("SELECT * FROM ".PN_Table." WHERE notification_type = 5 AND reason = 'ExchangeSwapOrderDeposit' AND reason_detail = '".(int)$return->id_return[0]."' AND order_id = ".$orderid, $dbshop, __FILE__, __LINE__);
				while ($row=mysqli_fetch_assoc($res))
				{
					$swap_sum+=$row["accounting_EUR"];
				}
				$swap_sum*=-1;
	
				//es darf nicht mehr als der wert des zurückkommenden Artikel verschoben werden, aber auch nicht mehr als der Wert des Austauschartikels wert ist
				$to_account = $swap_sum-$returntotalEUR[$orderid];
				// last_ex_orderdeposit && to_account are negative
				if ( $last_ex_orderdeposit >= $to_account )
				{
					$accounting = $last_ex_orderdeposit;
				}
				else
				{
					$accounting = $to_account;
				}
			}
			else
			{
				$accounting = $last_ex_orderdeposit;
			}


			if ( $accounting != 0 )
			{

				$user_deposit=$last_userdeposit+$accounting;
				$order_deposit=$last_orderdeposit+$accounting;

				//SCHREIBE SYSTEMBUCHUNG - EXCHANGE(FROM)
				$insert_data=array();
				$insert_data["f_id"]=$last_ordertotalID;
				$insert_data["shop_id"]=$order["shop_id"];
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=time();
				$insert_data["notification_type"]=5;
				$insert_data["reason"]="ExchangeSwapOrderDeposit";
				$insert_data["reason_detail"]=(int)$return->id_return[0];
				$insert_data["order_id"]=$orderid;
				$insert_data["total"]=0;
				$insert_data["currency"]=$order["currency"];
				$insert_data["exchange_rate_from_EUR"]=$order["exchangerate"];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$order_deposit;
				$insert_data["user_id"]=$order["user_id"];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=0;
				$insert_data["buyer_lastname"]=$order["buyer_lastname"];
				$insert_data["buyer_firstname"]=$order["buyer_firstname"];
		
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
				
				$id_PN = mysqli_insert_id($dbshop);
				
				$accounting*=-1;
		
				$exchange_order=getOrderData($exchange_orderid, "single");
		
				//GET LAST ORDERTOTAL FROM EXCHANGE
				$last_ex_ordertotal = getLastOrderTotal($exchange_orderid);
				
				//GET LAST ORDERDEPOSIT FROM EXCHANGE
				$last_ex_orderdeposit = getLastOrderDeposit($exchange_orderid);
				
				$exchange_order_deposit=$last_ex_orderdeposit+$accounting;
				
				//GET LAST USERDEPOSIT
				$user_deposit=getLastUserDeposit($order["user_id"]);
				$user_deposit+=$accounting;
		
		
				//SCHREIBE SYSTEMBUCHUNG - EXCHANGE(TO)
				$insert_data=array();
				$insert_data["f_id"]=$id_PN;
				$insert_data["shop_id"]=$exchange_order["shop_id"];
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=time();
				$insert_data["notification_type"]=5;
				$insert_data["reason"]="ExchangeSwapOrderDeposit";
				$insert_data["reason_detail"]=(int)$return->id_return[0];
				$insert_data["order_id"]=$exchange_orderid;
				$insert_data["total"]=0;
				$insert_data["currency"]=$exchange_order["currency"];
				$insert_data["exchange_rate_from_EUR"]=$exchange_order["exchangerate"];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$exchange_order_deposit;
				$insert_data["user_id"]=$exchange_order["user_id"];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=0;
				$insert_data["buyer_lastname"]=$exchange_order["buyer_lastname"];
				$insert_data["buyer_firstname"]=$exchange_order["buyer_firstname"];
				
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
			}
		}
	}


//**************************************************************************************

	if ($_POST["mode"]=="Payment")
	{
		$required=array("orderid" =>"numericNN", "TransactionID" => "textNN");
		check_man_params($required);
		
		$order=getOrderData($_POST["orderid"], "single");
		$payment = getLastPayment($_POST["TransactionID"]);

		$last_orderdeposit=getLastOrderDeposit($_POST["orderid"])*-1;
		$payment_deposit=(float)$payment->deposit_EUR[0];

		if ($last_orderdeposit<=0)
		{
			$accounting=0;
		}
		else
		{
			if ($last_orderdeposit>=$payment_deposit)
			{
				$accounting = $payment_deposit*-1;
			}
			else
			{
				$accounting = $last_orderdeposit*-1;
			}
		
			$payment_deposit+=$accounting;
			$user_deposit=getLastUserDeposit($order["user_id"])+$accounting;
			
			//SCHREIBE SYSTEMBUCHUNG - PAYMENT(FROM)
			$insert_data=array();
			$insert_data["f_id"]=(int)$payment->id_PN[0];
			$insert_data["shop_id"]=0;
			$insert_data["PN_date"]=time();
			$insert_data["accounting_date"]=(int)$payment->accounting_date[0];
			$insert_data["notification_type"]=4;
			$insert_data["reason"]="Payment";
			$insert_data["order_id"]=0;
			$insert_data["total"]=0;
			$insert_data["currency"]=(string)$payment->currency[0];
			$insert_data["exchange_rate_from_EUR"]=(float)$payment->exchange_rate_from_EUR[0];
			$insert_data["accounting_EUR"]=$accounting;
			$insert_data["deposit_EUR"]=$payment_deposit;
			$insert_data["user_id"]=$order["user_id"];
			$insert_data["user_deposit_EUR"]=$user_deposit;
			$insert_data["payment_type_id"]=(int)$payment->payment_type_id[0];
			$insert_data["buyer_lastname"]=(string)$payment->buyer_lastname[0];
			$insert_data["buyer_firstname"]=(string)$payment->buyer_firstname[0];
			$insert_data["payment_mail"]=(string)$payment->payment_mail[0];
			$insert_data["payer_id"]=(string)$payment->payer_id[0];
			$insert_data["receiver_mail"]=(string)$payment->receiver_mail[0];
			$insert_data["receiver_id"]=(string)$payment->receiver_id[0];
			$insert_data["paymentTransactionID"]=(string)$payment->paymentTransactionID[0];
			$insert_data["parentPaymentTransactionID"]=(string)$payment->parentPaymentTransactionID[0];
			$insert_data["payment_note"]=(string)$payment->payment_note[0];
	
			$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
			
			$id_PN = mysqli_insert_id($dbshop);
			
			$accounting*=-1;
			$user_deposit=$insert_data["user_deposit_EUR"]+$accounting;
			$order_deposit=getLastOrderDeposit($_POST["orderid"]);
			$order_deposit+=$accounting;
	
			//SCHREIBE SYSTEMBUCHUNG - PAYMENT(TO)
			$insert_data=array();
			$insert_data["f_id"]=$id_PN;
			$insert_data["shop_id"]=$order["shop_id"];
			$insert_data["PN_date"]=time();
			$insert_data["accounting_date"]=(int)$payment->accounting_date[0];
			$insert_data["notification_type"]=5;
			$insert_data["reason"]="Payment";
			$insert_data["order_id"]=$_POST["orderid"];
			$insert_data["total"]=0;
			$insert_data["currency"]=(string)$payment->currency[0];
			$insert_data["exchange_rate_from_EUR"]=(float)$payment->exchange_rate_from_EUR[0];
			$insert_data["accounting_EUR"]=$accounting;
			$insert_data["deposit_EUR"]=$order_deposit;
			$insert_data["user_id"]=$order["user_id"];
			$insert_data["user_deposit_EUR"]=$user_deposit;
			$insert_data["payment_type_id"]=(int)$payment->payment_type_id[0];
			$insert_data["buyer_lastname"]=$order["buyer_lastname"];
			$insert_data["buyer_firstname"]=$order["buyer_firstname"];
	
			$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
			
			$id_PN = mysqli_insert_id($dbshop);
			
			//PAYPAL BUYER NOTE ÜBERTRAGEN
			if ((string)$payment->payment_note[0]!="")
			{
			//	mail("nputzing@mapco.de", "PayPal_BuyerNote PAYMENT", "OrderID ".$orderid." BuyerNote ".(string)$payment->payment_note[0]);
				q("UPDATE ".Shop_Order." SET PayPal_BuyerNote = '".mysqli_real_escape_string($dbshop, (string)$payment->payment_note[0])."' WHERE id_order = ".$_POST["orderid"], $dbshop, __FILE__,__LINE__);
			}
			
			update_PaymentStatus ($_POST["orderid"], (string)$payment->paymentTransactionID[0], "Completed", (int)$payment->accounting_date[0], (int)$payment->payment_type_id[0], getLastOrderDeposit($_POST["orderid"]));
		
			// SET FLAG FOR NO UPDATE 
			q("UPDATE shop_orders SET payment_updated = 1 WHERE id_order = ".$_POST["orderid"], $dbshop, __FILE__, __LINE); 


		} // IF ACCOUNTING

	} // MODE PAYMENT

//***********************************************************
	if ($_POST["mode"]=="Refund")
	{
		/*
			REFUND Buchungen
			0.) Finde Orders zur Zahlung -> Selectiere Orders mit Deposit > 0
			
		XXXX	1.) Orderdeposit wird mit Refundbuchung verrechnet (wenn Orderdeposit >0 bis Orderdeposit = 0)
		XXXX	2.) Wenn Refunddeposit nicht = 0 wird nach Zahlungen zur Order gesucht und deren positiver Deposit mit dem Refund verrechnet bis Refunddeposit = 0 oder keine weiteren positiven Paymentdeposits vorhanden sind
		xXXX	3.) Wenn Refunddeposit nicht = 0 wird rest auf Orderdeposit gebucht
		
			1.) Suche Payment mit passender TransactionID <-> ParentTransaction, wenn positiver Zahlungsdeposit -> verrechne Refund
			2.) Wenn Refund noch positiven Betrag übrig hat, prüfe Orders zur Zahlung -> wenn positiver Deposit vorhanden, bebuche Order
																						> wenn kein positiver Deposit und nur eine Order -> bebuche order
																						-> wenn kein positiver Deposit und mehrere Order -> FEHLERMELDUNG
		*/


		$required=array("TransactionID" => "textNN");
		check_man_params($required);
	/*	
		//GET REFUNDTRANSACTION
		$postfields = array();
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationLastPaymentGet";
		$postfields["TransactionID"]=$_POST["TransactionID"];

		$response = soa2($postfields, __FILE__, __LINE__);
		if ($response->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true));
			exit;
		}
		$refundtransaction = $response->payment[0];
		//print_r($refundtransaction);
	*/
	

		//GET REFUNDTRANSACTION
		$refundtransaction=getLastPayment($_POST["TransactionID"]);

		if ((float)$refundtransaction->deposit_EUR[0]<0) //REFUNDDEPOSIT ist negativ
		{
			
			//GET PAYMENTTRANSACTION
			$payment=getLastPayment((string)$refundtransaction->parentPaymentTransactionID[0]);

			//CHECK if payment has positiv deposit
			if ((float)$payment->deposit_EUR[0]>0)	
			{
				$paymentdeposit=(float)$payment->deposit_EUR[0];
				$refunddeposit=(float)$refundtransaction->deposit_EUR[0];
				
				//GET ACCOUNTING
				if ($paymentdeposit>$refunddeposit*-1)
				{
					$accounting = $refunddeposit*-1;
				}
				else
				{
					$accounting = $paymentdeposit;
				}
				
				$refund_deposit=$refunddeposit+$accounting;
				
				$user_deposit=getLastUserDeposit((int)$refundtransaction->user_id[0]);
				$user_deposit+=$accounting;
				
				//REFUND BUCHEN (FROM)
				$insert_data=array();
				$insert_data["f_id"]=(int)$refundtransaction->id_PN[0];
				$insert_data["shop_id"]=0;
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=(int)$refundtransaction->accounting_date[0];
				$insert_data["notification_type"]=4;
				$insert_data["reason"]="Refund_Payment";
				$insert_data["order_id"]=0;
				$insert_data["total"]=0;
				$insert_data["currency"]=(string)$refundtransaction->currency[0];
				$insert_data["exchange_rate_from_EUR"]=(string)$refundtransaction->exchange_rate_from_EUR[0];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$refund_deposit;
				$insert_data["user_id"]=(int)$refundtransaction->user_id[0];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=(int)$refundtransaction->payment_type_id[0];
				$insert_data["buyer_lastname"]=(string)$refundtransaction->buyer_lastname[0];
				$insert_data["buyer_firstname"]=(string)$refundtransaction->buyer_firstname[0];
				$insert_data["payment_mail"]=(string)$refundtransaction->payment_mail[0];
				$insert_data["payer_id"]=(string)$refundtransaction->payer_id[0];
				$insert_data["receiver_mail"]=(string)$refundtransaction->receiver_mail[0];
				$insert_data["receiver_id"]=(string)$refundtransaction->receiver_id[0];
				$insert_data["paymentTransactionID"]=(string)$refundtransaction->paymentTransactionID[0];
				$insert_data["parentPaymentTransactionID"]=(string)$refundtransaction->parentPaymentTransactionID[0];
				$insert_data["payment_note"]=(string)$refundtransaction->payment_note[0];
				
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
				
				$id_PN = mysqli_insert_id($dbshop);
				
				$accounting*=-1;
				$user_deposit=getLastUserDeposit((int)$refundtransaction->user_id[0]);
				$user_deposit+=$accounting;
			
				$payment_deposit=$paymentdeposit;
				$payment_deposit+=$accounting;	
				
				//REFUND BUCHEN (TO)
				$insert_data=array();
				$insert_data["f_id"]=$id_PN;
				$insert_data["shop_id"]=0;
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=(int)$refundtransaction->accounting_date[0];
				$insert_data["notification_type"]=4;
				$insert_data["reason"]="Refund_Payment";
				$insert_data["order_id"]=0;
				$insert_data["total"]=0;
				$insert_data["currency"]=(string)$refundtransaction->currency[0];
				$insert_data["exchange_rate_from_EUR"]=(string)$refundtransaction->exchange_rate_from_EUR[0];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$payment_deposit;
				$insert_data["user_id"]=(int)$refundtransaction->user_id[0];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=(int)$payment->payment_type_id[0];
				$insert_data["buyer_lastname"]=(string)$payment->buyer_lastname[0];
				$insert_data["buyer_firstname"]=(string)$payment->buyer_firstname[0];
				$insert_data["payment_mail"]=(string)$payment->payment_mail[0];
				$insert_data["payer_id"]=(string)$payment->payer_id[0];
				$insert_data["receiver_mail"]=(string)$payment->receiver_mail[0];
				$insert_data["receiver_id"]=(string)$payment->receiver_id[0];
				$insert_data["paymentTransactionID"]=(string)$payment->paymentTransactionID[0];
				$insert_data["parentPaymentTransactionID"]="";
				$insert_data["payment_note"]="";
		
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
				
				$id_PN = mysqli_insert_id($dbshop);


				
			}
			
		}
		
		//GET REFUNDTRANSACTION
		$refundtransaction=getLastPayment($_POST["TransactionID"]);
 		$refunddeposit=(float)$refundtransaction->deposit_EUR[0];

		if ($refunddeposit<0) //REFUNDDEPOSIT ist negativ
		{
			//GET ORDERIDs
			$order_ids=array();
			$order_ids= getPaymentOrders((string)$refundtransaction->parentPaymentTransactionID[0]);
			if (sizeof($order_ids)>0)
			{
				//CHECK IF there are refundable orders
				$order_deposit = array();
				for ($i=0; $i<sizeof($order_ids); $i++)
				{
					$tmp = getLastOrderDeposit($order_ids[$i]);
					if ($tmp>0)
					{	
						$order_deposit[$order_ids[$i]]=getLastOrderDeposit($order_ids[$i]);
				
					}
				}
				if (sizeof($order_deposit)==0 && sizeof($order_ids)==1)
				{
					//BUCHE REFUNDDEPOSIT AUF EINZELORDER
					$order_deposit[$order_ids[0]]=getLastOrderDeposit($order_ids[0]);
						
				}
				else if (sizeof($order_deposit)==0 && sizeof($order_ids)>1)
				{
					//REFUND IST NICHT GENAU EINER ORDER ZUORDBAR
					show_error(0,9,__FILE__,__LINE__);
					exit;	
				}
				//BUCHE REFUND AUF ORDERS bis RefundDEPOSIT = 0 || oder rest auf letzte Order
				//for ($j=0; $j<sizeof($order_deposit); $j++)
				$j=0;

				foreach($order_deposit as $order_id => $data)
				{
					$j++;
					if ($refunddeposit<0)
					{
						
						if ($j == sizeof($order_deposit))
						{
							//BUCHE REST AUF LETZTE ORDER
							$accounting = $refunddeposit*-1;
						}
						elseif ($order_deposit[$order_id]>$refunddeposit*-1)
						{
							
							$accounting = $refunddeposit*-1;
						}
						else
						{
							$accounting = $order_deposit[$order_id];
						}

						$order=getOrderData($order_id);
						//$accounting=$refund_deposit*-1;
						
						$refund_deposit=$refunddeposit+$accounting;
						$refunddeposit+=$accounting;
						
						$user_deposit=getLastUserDeposit($order["user_id"]);
						$user_deposit+=$accounting;
						
						//REFUND BUCHEN (FROM)
						$insert_data=array();
						$insert_data["f_id"]=(int)$refundtransaction->id_PN[0];
						$insert_data["shop_id"]=0;
						$insert_data["PN_date"]=time();
						$insert_data["accounting_date"]=(int)$refundtransaction->accounting_date[0];
						$insert_data["notification_type"]=4;
						$insert_data["reason"]="Refund_Order";
						$insert_data["order_id"]=0;
						$insert_data["total"]=0;
						$insert_data["currency"]=(string)$refundtransaction->currency[0];
						$insert_data["exchange_rate_from_EUR"]=(string)$refundtransaction->exchange_rate_from_EUR[0];
						$insert_data["accounting_EUR"]=$accounting;
						$insert_data["deposit_EUR"]=$refund_deposit;
						$insert_data["user_id"]=$order["user_id"];
						$insert_data["user_deposit_EUR"]=$user_deposit;
						$insert_data["payment_type_id"]=(int)$refundtransaction->payment_type_id[0];
						$insert_data["buyer_lastname"]=(string)$refundtransaction->buyer_lastname[0];
						$insert_data["buyer_firstname"]=(string)$refundtransaction->buyer_firstname[0];
						$insert_data["payment_mail"]=(string)$refundtransaction->payment_mail[0];
						$insert_data["payer_id"]=(string)$refundtransaction->payer_id[0];
						$insert_data["receiver_mail"]=(string)$refundtransaction->receiver_mail[0];
						$insert_data["receiver_id"]=(string)$refundtransaction->receiver_id[0];
						$insert_data["paymentTransactionID"]=(string)$refundtransaction->paymentTransactionID[0];
						$insert_data["parentPaymentTransactionID"]=(string)$refundtransaction->parentPaymentTransactionID[0];
						$insert_data["payment_note"]=(string)$refundtransaction->payment_note[0];
						
						$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
						
						$id_PN = mysqli_insert_id($dbshop);
						
						$accounting*=-1;
						$user_deposit=getLastUserDeposit($order["user_id"]);
						$user_deposit+=$accounting;
						
						$order_deposit=getLastOrderDeposit($order_id);
						$order_deposit+=$accounting;
						
						//REFUND BUCHEN (TO)
						$insert_data=array();
						$insert_data["f_id"]=$id_PN;
						$insert_data["shop_id"]=$order["shop_id"];
						$insert_data["PN_date"]=time();
						$insert_data["accounting_date"]=(int)$refundtransaction->accounting_date[0];
						$insert_data["notification_type"]=5;
						$insert_data["reason"]="Refund_Order";
						$insert_data["order_id"]=$order_id;
						$insert_data["total"]=0;
						$insert_data["currency"]=$order["currency"];
						$insert_data["exchange_rate_from_EUR"]=$order["exchangerate"];
						$insert_data["accounting_EUR"]=$accounting;
						$insert_data["deposit_EUR"]=$order_deposit;
						$insert_data["user_id"]=$order["user_id"];
						$insert_data["user_deposit_EUR"]=$user_deposit;
						$insert_data["payment_type_id"]=(int)$refundtransaction->payment_type_id[0];
						$insert_data["buyer_lastname"]=$order["buyer_lastname"];
						$insert_data["buyer_firstname"]=$order["buyer_firstname"];
				
						$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
						
						$id_PN = mysqli_insert_id($dbshop);
						
						update_PaymentStatus ($order_id, (string)$refundtransaction->paymentTransactionID[0], "Refunded", (int)$last_payment->accounting_date[0], (int)$last_payment->payment_type_id[0], getLastOrderDeposit($order_id));

						// SET FLAG FOR NO UPDATE 
						q("UPDATE shop_orders SET payment_updated = 1 WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE); 

					} // IF REFUNDDEPOSIT <0
				}
				
			}


		}

		
		
	} // MODE REFUND



//*************************************************************

	if ($_POST["mode"]=="LinkingPayment")
	{
		$required=array("orderid" =>"numericNN", "TransactionID" => "textNN");
		check_man_params($required);
		
		$order = getOrderData($_POST["orderid"]);
		
		//$last_payment = getLastPayment($_POST["TransactionID"]);
		$res_notification = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID = '".$_POST["TransactionID"]."' ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_notification)==0)
		{
			// KEIN PAYMENT GEFUNDEN
			show_error(9802, 7, __FILE__, __LINE__, "OrderID".$_POST["orderid"]." paymentTransactionID: ".$_POST["TransactionID"]);
			exit;
		}
		
		$row_notification=mysqli_fetch_assoc($res_notification);

		if ($row_notification["order_id"]==$_POST["orderid"])
		{
			//PAYMENT BEREITS ZUGEORDNET
		}
		else
		{
			//$accounting=(float)$row_notification["total"]*(1/$row_notification["exchange_rate_from_EUR"])*-1;
			$accounting = $row_notification["deposit_EUR"];
			$accounting*=-1;
			
			$user_deposit=getLastUserDeposit($row_notification["user_id"]);
			$user_deposit+=$accounting;
			$payment_deposit=getLastPaymentDeposit($row_notification["paymentTransactionID"]);
			$payment_deposit+=$accounting;
			
			//SCHREIBE SYSTEMBUCHUNG - LINKING PAYMENT(FROM)
			$insert_data=array();
			$insert_data["f_id"]=$row_notification["id_PN"];
			$insert_data["shop_id"]=0;
			$insert_data["PN_date"]=time();
			$insert_data["accounting_date"]=$row_notification["accounting_date"];
			$insert_data["notification_type"]=4;
			$insert_data["reason"]="Linking Payment";
			$insert_data["order_id"]=0;
			$insert_data["total"]=0;
			$insert_data["currency"]=$row_notification["currency"];
			$insert_data["exchange_rate_from_EUR"]=$row_notification["exchange_rate_from_EUR"];
			$insert_data["accounting_EUR"]=$accounting;
			$insert_data["deposit_EUR"]=$payment_deposit;
			$insert_data["user_id"]=$row_notification["user_id"];
			$insert_data["user_deposit_EUR"]=$user_deposit;
			$insert_data["payment_type_id"]=$row_notification["payment_type_id"];
			$insert_data["buyer_lastname"]=$row_notification["buyer_lastname"];
			$insert_data["buyer_firstname"]=$row_notification["buyer_firstname"];
			$insert_data["payment_mail"]=$row_notification["payment_mail"];
			$insert_data["payer_id"]=$row_notification["payer_id"];
			$insert_data["receiver_mail"]=$row_notification["receiver_mail"];
			$insert_data["receiver_id"]=$row_notification["receiver_id"];
			$insert_data["paymentTransactionID"]=$row_notification["paymentTransactionID"];
			$insert_data["parentPaymentTransactionID"]=$row_notification["parentPaymentTransactionID"];
			$insert_data["payment_note"]=$row_notification["payment_note"];
			
			$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
			
			$id_PN = mysqli_insert_id($dbshop);
			
			$accounting*=-1;
			$user_deposit=getLastUserDeposit($order["user_id"]);
			$user_deposit+=$accounting;
			
			//SCHREIBE SYSTEMBUCHUNG -LINKING PAYMENT(TO)
			$insert_data=array();
			$insert_data["f_id"]=$id_PN;
			$insert_data["shop_id"]=0;
			$insert_data["PN_date"]=time();
			$insert_data["accounting_date"]=$row_notification["accounting_date"];
			$insert_data["notification_type"]=4;
			$insert_data["reason"]="Linking Payment";
			$insert_data["order_id"]=$_POST["orderid"];
			$insert_data["total"]=0;
			$insert_data["currency"]=$row_notification["currency"];
			$insert_data["exchange_rate_from_EUR"]=$row_notification["exchange_rate_from_EUR"];
			$insert_data["accounting_EUR"]=$accounting;
			$insert_data["deposit_EUR"]=$accounting;
			$insert_data["user_id"]=$order["user_id"];
			$insert_data["user_deposit_EUR"]=$user_deposit;
			$insert_data["payment_type_id"]=$row_notification["payment_type_id"];
			$insert_data["buyer_lastname"]=$row_notification["buyer_lastname"];
			$insert_data["buyer_firstname"]=$row_notification["buyer_firstname"];
			$insert_data["payment_mail"]=$row_notification["payment_mail"];
			$insert_data["payer_id"]=$row_notification["payer_id"];
			$insert_data["receiver_mail"]=$row_notification["receiver_mail"];
			$insert_data["receiver_id"]=$row_notification["receiver_id"];
			$insert_data["paymentTransactionID"]=$row_notification["paymentTransactionID"];
			$insert_data["parentPaymentTransactionID"]=$row_notification["parentPaymentTransactionID"];
			$insert_data["payment_note"]=$row_notification["payment_note"];
	
			$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
			
			$id_PN = mysqli_insert_id($dbshop);
		}
		
	}

	if ($_POST["mode"]=="PaymentWriteBack")
	{
		$required=array("orderid" =>"numericNN");
		check_man_params($required);
		/*
		CHECK FOR OPTIONAL TXNID 	-> keine TxnID übergeben: buche alle Zahlungen zur Order zurück
									-> TxnID übergeben: buche nur diese Transaktion zurück
		*/
		
		$payments=orderPaymentsGet($_POST["orderid"]);
		
		$TxnIDs = array();
		if (isset($_POST["TransactionID"]) && $_POST["TransactionID"]!="")
		{
			$TxnIDs[$_POST["TransactionID"]] = 1;
		}
		else
		{
			foreach ($payments as $TxnID => $notifications)
			{
				$TxnIDs[$TxnID] = 1;
			}
		}

		//$order = getOrderData($_POST["orderid"], "single");

		//BUCHE ZURÜCK
		foreach ($TxnIDs as $TxnID => $data)
		{
			$OK = false;
			//GET "PAYMENT PAIR" for ORDER AND TRANSACTION
			$res_notification_type5 = q("SELECT * FROM payment_notifications WHERE 
										notification_type = 5 AND 
										reason = 'Payment' AND
										order_id = ".$_POST["orderid"]." AND 
										f_id = (
											SELECT id_PN FROM payment_notifications WHERE
											paymentTransactionID = '".$TxnID."' AND
											notification_type = 4 AND
											reason = 'Payment' )", $dbshop, __FILE__, __LINE__);
			
			if (mysqli_num_rows($res_notification_type5)>0)
			{
				$row_notification_type5 = mysqli_fetch_assoc($res_notification_type5);
				$OK = true;
			}

			if ($OK)
			{
				//GET PAYMENTDATA (NOTIFICATIONTYPE 1)
				$res_notification_type1 = q("SELECT * FROM payment_notifications WHERE notification_type = 1 AND paymentTransactionID = '".$TxnID."'", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_notification_type1)==0)
				{
					$OK=false;
				}
				else
				{
					$row_notification_type1 = mysqli_fetch_assoc($res_notification_type1);
				}
			}

			if ($OK)
			{
				//BUCHUNG
				$accounting=$row_notification_type5["accounting_EUR"];
				
				$last_orderdeposit = getLastOrderDeposit($_POST["orderid"]);
				$order_deposit = $last_orderdeposit-$accounting;
				
				$last_userdeposit = getLastUserDeposit($row_notification_type5["user_id"]);
				$user_deposit = $last_userdeposit - $accounting;
				
				$accounting*=-1;
				
				//BUCHE VON ORDER
				$insert_data=array();
				$insert_data["f_id"]=$row_notification_type5["id_PN"];
				$insert_data["shop_id"]=$row_notification_type5["shop_id"];
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=time();
				$insert_data["notification_type"]=5;
				$insert_data["reason"]="Payment Write Back";
				$insert_data["order_id"]=$_POST["orderid"];
				$insert_data["total"]=0;
				$insert_data["currency"]=$row_notification_type5["currency"];
				$insert_data["exchange_rate_from_EUR"]=$row_notification_type5["exchange_rate_from_EUR"];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$order_deposit;
				$insert_data["user_id"]=$row_notification_type5["user_id"];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=$row_notification_type5["payment_type_id"];
				$insert_data["buyer_lastname"]=$row_notification_type5["buyer_lastname"];
				$insert_data["buyer_firstname"]=$row_notification_type5["buyer_firstname"];
				$insert_data["payment_mail"]=$row_notification_type5["payment_mail"];
				$insert_data["payer_id"]=$row_notification_type5["payer_id"];
				$insert_data["receiver_mail"]=$row_notification_type5["receiver_mail"];
				$insert_data["receiver_id"]=$row_notification_type5["receiver_id"];
				$insert_data["paymentTransactionID"]=$row_notification_type5["paymentTransactionID"];
				$insert_data["parentPaymentTransactionID"]=$row_notification_type5["parentPaymentTransactionID"];
				$insert_data["payment_note"]=$row_notification_type5["payment_note"];
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);
				
				$id_PN = mysqli_insert_id($dbshop);
				
				$accounting*=-1;
				$last_paymentdeposit = getLastPaymentDeposit($TxnID);
				$payment_deposit = $last_paymentdeposit+$accounting;
				
				$user_deposit+=$accounting;

				//BUCHE AUF PAYEMENT
				$insert_data=array();
				$insert_data["f_id"]=$id_PN;
				$insert_data["shop_id"]=0;
				$insert_data["PN_date"]=time();
				$insert_data["accounting_date"]=time();
				$insert_data["notification_type"]=4;
				$insert_data["reason"]="Payment Write Back";
				$insert_data["order_id"]=0;
				$insert_data["total"]=0;
				$insert_data["currency"]=$row_notification_type1["currency"];
				$insert_data["exchange_rate_from_EUR"]=$row_notification_type1["exchange_rate_from_EUR"];
				$insert_data["accounting_EUR"]=$accounting;
				$insert_data["deposit_EUR"]=$payment_deposit;
				$insert_data["user_id"]=$row_notification_type5["user_id"];
				$insert_data["user_deposit_EUR"]=$user_deposit;
				$insert_data["payment_type_id"]=$row_notification_type1["payment_type_id"];
				$insert_data["buyer_lastname"]=$row_notification_type1["buyer_lastname"];
				$insert_data["buyer_firstname"]=$row_notification_type1["buyer_firstname"];
				$insert_data["payment_mail"]=$row_notification_type1["payment_mail"];
				$insert_data["payer_id"]=$row_notification_type1["payer_id"];
				$insert_data["receiver_mail"]=$row_notification_type1["receiver_mail"];
				$insert_data["receiver_id"]=$row_notification_type1["receiver_id"];
				$insert_data["paymentTransactionID"]=$row_notification_type1["paymentTransactionID"];
				$insert_data["parentPaymentTransactionID"]=$row_notification_type1["parentPaymentTransactionID"];
				$insert_data["payment_note"]=$row_notification_type1["payment_note"];
				$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);

				echo '<transactionID>'.$TxnID.'</transactionID>'."\n";


			} // IF OK
		}
	} // IF MODE == "PaymentWriteBack"

?>