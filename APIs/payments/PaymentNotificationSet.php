<?php

	/*
	MODES
	-	OrderUpdate
	-	OrderAdd
	-	ManualPaymentNotification
	-	ExchangeNotification
	-	AccountingReturn
	-	CreditFromPromotion
	-	CreditFromOrder
	*/

	//SOA2 SERVICE
	
	$required=array("mode" => "textNN");
	
	check_man_params($required);
	
	$processed=false;
	
/********************************************************************************************
 G E T   R E Q U I R E D   D A T A
********************************************************************************************/	
	if ($_POST["mode"] == "OrderUpdate" || $_POST["mode"] == "OrderAdd" || $_POST["mode"] == "ManualPaymentNotification" || $_POST["mode"] == "ExchangeNotification" || $_POST["mode"] == "AccountingReturn" || $_POST["mode"] == "CrossEntryFromOrderDeposit")
	{
		$required=array("orderid" =>"numericNN", "order_event_id" => "numericNN");
		check_man_params($required);
		
		//GET ORDER
		$postfields["API"]="shop";
		$postfields["APIRequest"]="OrderDetailGet";
		$postfields["OrderID"]=$_POST["orderid"];
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
			$act_ordertotal=(float)str_replace(",", ".",(string)$response->Order[0]->orderTotalGross[0]);
	
			$shop_id=(int)$response->Order[0]->shop_id[0];
			$user_id=(int)$response->Order[0]->customer_id[0];
		//	$ordertotal=(float)str_replace(",", ".",(string)$response->Order[0]->orderTotalGross[0]);
			$firstmod=(int)$response->Order[0]->firstmod[0];
			$currency=(string)$response->Order[0]->Currency_Code[0];
			$buyer_firstname=(string)$response->Order[0]->bill_adr_firstname[0];
			$buyer_lastname=(string)$response->Order[0]->bill_adr_lastname[0];
			
			$orderitems=array();
			for ($i=0; isset($response->Order[0]->OrderItems[0]->Item[$i]); $i++)
			{
				$item = $response->Order[0]->OrderItems[0]->Item[$i];
				
				$orderitems[(int)$item->OrderItemID[0]]=$item;
				$exchangerate = $item->OrderItemExchangeRateToEUR[0];
			}
	
		}
		else
		{
			echo "ERROR:".$responseXML;
			//show_error(0, 7, __FILE__, __LINE__, $responseXML);
			exit;
		}
	
		//GET CURRENCIES
		$currencies=array();
		$res_curr=q("SELECT * FROM shop_currencies", $dbshop, __FILE__, __LINE__);
		while ($row_curr = mysqli_fetch_assoc($res_curr))
		{
			$currencies[$row_curr["currency_code"]] = $row_curr;
		}
	}
/****************************************************************************************************
 O R D E R   U P D A T E
 ***************************************************************************************************/
	
	if ($_POST["mode"]=="OrderUpdate")
	{
		//GET LAST ORDERTOTAL
		$res_ordertotal = q("SELECT * FROM payment_notifications4 WHERE notification_type = 2 AND order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ordertotal)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_ordertotal = mysqli_fetch_assoc($res_ordertotal);

		$last_ordertotal=$PN_ordertotal["total"]*(1/$PN_ordertotal["exchange_rate_from_EUR"]);
		$act_ordertotalEUR*=$PN_ordertotal["exchange_rate_from_EUR"];
		
		$accounting = $last_ordertotal-$act_ordertotalEUR;
		
		//GET LAST ORDERDEPOSIT
		$res_orderdeposit = q("SELECT * FROM payment_notifications4 WHERE order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_orderdeposit)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_orderdeposit = mysqli_fetch_assoc($res_orderdeposit);

		$order_deposit=$PN_orderdeposit["order_deposit_EUR"]+$accounting;
	
		//SET USER_DEPOSIT
		//GET LAST 	USER_DEPOSIT
		$user_deposit = 0;
		$res_deposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$PN_orderdeposit["user_id"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_deposit)==0)
		{
			//KEIN EINTRAG GEFUNDEN - > OLD DEPOSIT = 0
			$user_deposit=0;
		}
		else
		{
			$row_deposit = mysqli_fetch_assoc($res_deposit);
			$user_deposit = $row_deposit["user_deposit_EUR"];
		}
		$user_deposit+=$accounting;


		$state = "OrderAdjustment";
		$state_reason = "";

		$insert_data=array();
		$insert_data["PNM_id"]=$_POST["order_event_id"];
		$insert_data["shop_id"]=$PN_ordertotal["shop_id"];
		$insert_data["PN_date"]=time();
		$insert_data["payment_date"]=time();
		$insert_data["notification_type"]=2;
		$insert_data["state"]=$state;
		$insert_data["order_id"]=$PN_ordertotal["order_id"];
		$insert_data["total"]=$act_ordertotal;
		$insert_data["Currency"]=$PN_ordertotal["Currency"];
		$insert_data["exchange_rate_from_EUR"]=$PN_ordertotal["exchange_rate_from_EUR"];
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["order_deposit_EUR"]=$order_deposit;
		$insert_data["user_id"]=$PN_ordertotal["user_id"];
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=0;
		$insert_data["buyer_lastname"]=$PN_ordertotal["buyer_lastname"];
		$insert_data["buyer_firstname"]=$PN_ordertotal["buyer_firstname"];
		
		$res_insert=q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);
		
		if (mysqli_errno($dbshop)!=0)
		{
			//FEHLER BEIM SHREIBEN IN DIE DATENBANK
			echo "FEHLER BEIM ScHREIBEN IN DIE DATENBANK";
			exit;
		}

		$processed=true;
	}

/****************************************************************************************************
 O R D E R   A D D
 ***************************************************************************************************/

	if ($_POST["mode"]=="OrderAdd")
	{
		
//FIRST HAVE TO CHECK IF ORDERADD ENTRY ALREADY EXISTS
//.................


	
		$accounting=$act_ordertotal*(-1);
		
		$exchangerate=0;
		if (isset($response->Order[0]->OrderItems[0]->Item[0]))
		{
			$exchangerate=(float)$response->Order[0]->OrderItems[0]->Item[0]->OrderItemExchangeRateToEUR[0];
		}
		else
		{
			$exchangerate=$currencies[$currency]["exchange_rate_to_EUR"];
		}
		$accounting*=1/$exchangerate;

		//GET LAST 	USER_DEPOSIT
		$res_deposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$user_id." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_deposit)==0)
		{
			//KEIN EINTRAG GEFUNDEN - > OLD DEPOSIT = 0
			$user_deposit=0;
		}
		else
		{
			$row_deposit = mysqli_fetch_assoc($res_deposit);
			$user_deposit = $row_deposit["user_deposit_EUR"];
		}
		$user_deposit+=$accounting;

		//ORDER SCHREIBEN in PaymentNotifications 
		$insert_data["PNM_id"]=$_POST["order_event_id"];
		$insert_data["shop_id"]=$shop_id;
		$insert_data["PN_date"]=time();
		$insert_data["payment_date"]=$firstmod;
		$insert_data["notification_type"]=2;
		$insert_data["state"]="OrderAdd";
		$insert_data["order_id"]=$_POST["orderid"];
		$insert_data["total"]=$act_ordertotal;
		$insert_data["Currency"]=$currency;
		$insert_data["exchange_rate_from_EUR"]=$exchangerate;
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["order_deposit_EUR"]=$accounting;
		$insert_data["user_id"]=$user_id;
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["buyer_lastname"]=$buyer_lastname;
		$insert_data["buyer_firstname"]=$buyer_firstname;
		
		$res_insert = q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);

		//ORDER FOR WHICH SHOP_TYPE
			//GET SHOP_SHOP
			$res_shop=q("SELECT * FROM shop_shops WHERE id_shop = ".$shop_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_shop)==0)
			{
				//KEIN SHOP mit ID gefunden
				//show_error()
				echo "SHOP nicht gefunden";
				exit;
			}
			else
			{
				$shop=mysqli_fetch_assoc($res_shop);
			}
		if ($shop["shop_type"]==2) //EBAY
		{
			//SUCHE NACH TRANSACTIONID im Feld payment_notifications.orderTransactionID aus einer der TransactionID aus order
			if (isset($response->Order[0]->OrderItems[0]->Item[0]))
			{
				for ($i=0; isset($response->Order[0]->OrderItems[0]->Item[$i]); $i++)
				{
					$foreignTranactionID = $response->Order[0]->OrderItems[0]->Item[$i]->OrderItemforeign_transactionID[0];
					//GET PAYMENTNOTIFICATION (payment) for TransactionID
					if ($foreignTranactionID!="" && $foreignTranactionID!=0)
					{
						$res_notification = q("SELECT * FROM payment_notifications4 WHERE orderTransactionID = ".$foreignTranactionID." AND order_id = 0", $dbshop, __FILE__, __LINE__);
						while ($row_notification=mysqli_fetch_assoc($res_notification))
						{
							//SET USER_DEPOSIT
							//GET LAST 	USER_DEPOSIT
							$res_deposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$user_id." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
							if (mysqli_num_rows($res_deposit)==0)
							{
								//KEIN EINTRAG GEFUNDEN - > OLD DEPOSIT = 0
								$user_deposit=0;
							}
							else
							{
								$row_deposit = mysqli_fetch_assoc($res_deposit);
								$user_deposit = $row_deposit["user_deposit_EUR"];
							}
							$user_deposit+=(float)$row_notification["total"];

							//SET ORDER_DEPOSIT
							//GET LAST ORDERDEPOSIT
							$res_order_deposit = q("SELECT * FROM payment_notifications4 WHERE order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
							if (mysqli_num_rows($res_order_deposit)==0)
							{
								//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
								$order_deposit=0;
							}
							else
							{
								$row_order_deposit = mysqli_fetch_assoc($res_order_deposit);
								$order_deposit=$row_order_deposit["order_deposit_EUR"];
							}
							$order_deposit+=(float)$row_notification["total"];
							
							//SCHREIBE SYSTEMBUCHUNG
							$insert_data=array();
							$insert_data["PNM_id"]=$row_notification["id_PN"];
							$insert_data["shop_id"]=$shop_id;
							$insert_data["PN_date"]=time();
							$insert_data["payment_date"]=$row_notification["payment_date"];
							$insert_data["notification_type"]=3;
							$insert_data["state"]="Linking Notifications";
							$insert_data["order_id"]=$_POST["orderid"];
							$insert_data["total"]=$row_notification["total"];
							$insert_data["Currency"]=$currency;
							if (isset($response->Order[0]->OrderItems[0]->Item[0]))
							{
								$insert_data["exchange_rate_from_EUR"]=(float)$response->Order[0]->OrderItems[0]->Item[0]->OrderItemExchangeRateToEUR[0];
							}
							else
							{
								$insert_data["exchange_rate_from_EUR"]=$currencies[$currency]["exchange_rate_to_EUR"];
							}
							$insert_data["accounting_EUR"]=$insert_data["total"]*(1/$insert_data["exchange_rate_from_EUR"]);
							$insert_data["order_deposit_EUR"]=$order_deposit;
							$insert_data["user_id"]=$user_id;
							$insert_data["user_deposit_EUR"]=$user_deposit;
							$insert_data["buyer_lastname"]=$buyer_lastname;
							$insert_data["buyer_firstname"]=$buyer_firstname;
							
							$res_insert = q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);
						}
					}
				}
			}
		}



		if ($shop["shop_type"]==1) // ONLINESHOP
		{
			
		}
		if ($shop["shop_type"]==3) // AMAZON
		{
			
		}
		

		//CHECK FOR EXISTING PAYMNETNOTIFICATIONS (payments)
		
		
		$processed=true;
	}


/****************************************************************************************************
 M A N U A L   P A Y M E N T   N O T I F I C A T I O N
 ***************************************************************************************************/
	
	if ($_POST["mode"]=="ManualPaymentNotification")
	{
		
		$required=array("payment_type_id" => "numericNN", "payment_amount" => "numeric", "payment_mode" => "textNN");
		check_man_params($required);

		//SET USER_DEPOSIT
		//GET LAST 	USER_DEPOSIT
		$res_deposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$user_id." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_deposit)==0)
		{
			//KEIN EINTRAG GEFUNDEN - > OLD DEPOSIT = 0
			$user_deposit=0;
		}
		else
		{
			$row_deposit = mysqli_fetch_assoc($res_deposit);
			$user_deposit = $row_deposit["user_deposit_EUR"];
		}


		//SET ORDER_DEPOSIT
		//GET LAST ORDERDEPOSIT
		$res_order_deposit = q("SELECT * FROM payment_notifications4 WHERE order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_order_deposit)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			$order_deposit=0;
		}
		else
		{
			$row_order_deposit = mysqli_fetch_assoc($res_order_deposit);
			$order_deposit=$row_order_deposit["order_deposit_EUR"];
		}
		
		
		$accounting=$_POST["payment_amount"];
		

		if ($_POST["payment_type_id"]==2) // ÜBERWEISUNG
		{
			$PNM_id = $_POST["order_event_id"];
		}
		
		if ($_POST["payment_mode"]=="refund")
		{
			// CHECK FOR PARENT_PAYMENTTRANSACTION ID
			check_man_params(array("ParentTransactionID" => "TextNN"));
			
			//CHECK IF TRANSACTION EXISTS
			$res_check=q("SELECT * FROM payment_notifications4 WHERE paymentTransactionID = '".$_POST["ParentTransactionID"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)==0)
			{
				//PAYMENTNOTIFICATION NICHT VORHANDEN
				//show_error()
				exit;
			}
			
			$accounting*=-1;
			$state = "Refunded";
			$_POST["payment_amount"]*=-1;

			$parentTransactionID=$_POST["ParentTransactionID"];
			
		}
		else
		{
			$parentTransactionID='';	
			$state="Completed";
		}
		
		$order_deposit+=$accounting;
		$user_deposit+=$accounting;
		

		//SCHREIBE ZAHLUNGSBUCHUNG
		$insert_data=array();
		$insert_data["PNM_id"]=$PNM_id;
		$insert_data["shop_id"]=$shop_id;
		$insert_data["PN_date"]=time();
		$insert_data["payment_date"]=time();
		$insert_data["notification_type"]=1; // ZAHLUNG
		$insert_data["state"]=$state;
		$insert_data["state_reason"]="";
		$insert_data["order_id"]=$_POST["orderid"];
		$insert_data["total"]=$_POST["payment_amount"];
		$insert_data["Currency"]="EUR";
		//$insert_data["Currency"]=$currency;
		/*
		if (isset($response->Order[0]->OrderItems[0]->Item[0]))
		{
			$insert_data["exchange_rate_from_EUR"]=(float)$response->Order[0]->OrderItems[0]->Item[0]->OrderItemExchangeRateToEUR[0];
		}
		else
		{
			$insert_data["exchange_rate_from_EUR"]=$currencies[$currency]["exchange_rate_to_EUR"];
		}
		*/
		$insert_data["exchange_rate_from_EUR"]=1;
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["order_deposit_EUR"]=$order_deposit;
		$insert_data["user_id"]=$user_id;
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=$_POST["payment_type_id"];
		$insert_data["buyer_lastname"]=$buyer_lastname;
		$insert_data["buyer_firstname"]=$buyer_firstname;
		$insert_data["parentPaymentTransactionID"]=$parentTransactionID;
		
		$res_insert = q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);
		
		$id_PN = mysqli_insert_id($dbshop);
		
		if($_POST["payment_type_id"]==2)
		{
			//SETZE "PAYMENTSTRANSACTIONID" NACH	
			q("UPDATE payment_notifications4 SET paymentTransactionID = '".$id_PN."' WHERE id_PN = ".$id_PN, $dbshop, __FILE__, __LINE__);
		}
	$processed=true;
	}
	
/****************************************************************************************************
 E X C H A N G E   N O T I F I C A T I O N
****************************************************************************************************/
	if ($_POST["mode"]=="ExchangeNotification")
	{

		/*
		- Wert des zurückkommenden Artikels wird auf alte Bestellung aufgebucht (Gustchrift)-> Userdeposit & Orderdeposit verringert
		- Wert wird der Umtauschbestellung gutgeschrieben -> Userdeposit & Orderdeposit erhöht
		- accounting des diferenzbetrages zwischen Rückgabe & umtauschartikel auf Umtauschbestellung
		
		- bei Eingang des Rücksendeartikels wird der Wert des Artikels auf alte Bestellung gutgeschrieben
		*/


		/*
			AUFRUF:
			- WENN UMTASUCHARTIKEL ANGELEGT WURDE
		*/

		check_man_params(array("return_id" => "numericNN"));
		
		//CHECK IF RETURN EXISTS AND IS EXCHANGE
		//GET RETURNDATA
		$postfields["API"]="shop";
		$postfields["APIRequest"]="OrderReturnGet";
		$postfields["return_id"]=$_POST["return_id"];
		$responseXML_return=post(PATH."soa2/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response_return = new SimpleXMLElement($responseXML_return);
		}
		catch(Exception $e)
		{
			//XML FEHLERHAFT
			echo "XMLERROR".$responseXML_return;
			//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			//exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response_return->Ack[0]!="Success")
		{
			echo "ERROR:".$responseXML_return;
			//show_error(0, 7, __FILE__, __LINE__, $responseXML);
			exit;
		}

		if ($response_return->orderreturn[0]->return_type[0]!="exchange")
		{
			//REURN IST KEIN EXCHANGE
			//show_error();
			exit;
		}

		$exchange_orderid = (int)$response_return->orderreturn[0]->exchange_order_id[0];
		
		if ($exchange_orderid == 0)
		{
			//FEHLER, EXCHANGEORDERID DARF NICHT 0 SEIN
			//show_error();
			exit;
		}
		
		//CHECK EXHANGE ORDER VORHANDEN?
		$res_check=q("SELECT * FROM shop_orders WHERE id_order = ".$exchange_orderid, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==0)
		{
			//EXCHANGEORDER NICHT VORHANDEN
			//show_error()
			exit;
		}
		
		
		//GET TOTAL OF RETURNITEMS
		$returntotal=0;
		$returntotalEUR = 0;
		for ($i=0; isset($response_return->orderreturn[0]->returnitems[0]->returnitem[$i]); $i++)		
		{
			$returntotal+=(float)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->price[0]*(int)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->amount[0];
			$returntotalEUR+=(float)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->price[0]*(int)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->amount[0]*(1/(float)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->exchange_rate_to_EUR[0]);
			//$exchangerate=(float)$response_return->orderreturn[0]->returnitem[$i]->exchange_rate_to_EUR[0];
		}
		

		//GET LAST ORDERTOTAL
		$res_ordertotal = q("SELECT * FROM payment_notifications4 WHERE notification_type = 2 AND order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ordertotal)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_ordertotal = mysqli_fetch_assoc($res_ordertotal);
		
		$last_ordertotal=$PN_ordertotal["total"]*(1/$PN_ordertotal["exchange_rate_from_EUR"]);
		
		$new_ordertotal=($act_ordertotal*$PN_ordertotal["exchange_rate_from_EUR"])+$returntotal;
		$new_ordertotalEUR=$act_ordertotal+$returntotalEUR;
		
		
		$accounting = $last_ordertotal-$new_ordertotalEUR;
		
		//GET LAST ORDERDEPOSIT
		$res_orderdeposit = q("SELECT * FROM payment_notifications4 WHERE order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_orderdeposit)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_orderdeposit = mysqli_fetch_assoc($res_orderdeposit);

		$order_deposit=$PN_orderdeposit["order_deposit_EUR"]+$accounting;
	
		//SET USER_DEPOSIT
		//GET LAST 	USER_DEPOSIT
		$user_deposit = 0;
		$res_userdeposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$PN_orderdeposit["user_id"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_userdeposit)==0)
		{
			//KEINE Notification GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_userdeposit = mysqli_fetch_assoc($res_userdeposit);
		$user_deposit=$PN_userdeposit["user_deposit_EUR"]+$accounting;


		//SCHREIBE GUTSCHRIFT AUF ALTE ORDER
		$state = "Credit Note";
		$state_reason = "preexchange";

		$insert_data=array();
		$insert_data["PNM_id"]=$_POST["order_event_id"];
		$insert_data["shop_id"]=$PN_ordertotal["shop_id"];
		$insert_data["PN_date"]=time();
		$insert_data["payment_date"]=time();
		$insert_data["notification_type"]=3;
		$insert_data["state"]=$state;
		$insert_data["order_id"]=$PN_ordertotal["order_id"];
		$insert_data["total"]=$new_ordertotal;
		$insert_data["Currency"]=$PN_ordertotal["Currency"];
		$insert_data["exchange_rate_from_EUR"]=$PN_ordertotal["exchange_rate_from_EUR"];
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["order_deposit_EUR"]=$order_deposit;
		$insert_data["user_id"]=$PN_ordertotal["user_id"];
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=0;
		$insert_data["buyer_lastname"]=$PN_ordertotal["buyer_lastname"];
		$insert_data["buyer_firstname"]=$PN_ordertotal["buyer_firstname"];
		
		$res_insert=q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);
	
		$id_PN=mysqli_insert_id($dbshop);
		
		q("UPDATE payment_notifications4 SET paymentTransactionID = '".$id_PN."' WHERE id_PN = ".$id_PN, $dbshop, __FILE__, __LINE__);

		
		$accounting*=-1;

		//GET EXCHANGEORDER
		$postfields["API"]="shop";
		$postfields["APIRequest"]="OrderDetailGet";
		$postfields["OrderID"]=$exchange_orderid;
		$responseXML_exchange=post(PATH."soa2/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response_exchange = new SimpleXMLElement($responseXML_exchange);
		}
		catch(Exception $e)
		{
			//XML FEHLERHAFT
			echo "XMLERROR".$responseXML_exchange;
			//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			//exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response_exchange->Ack[0]=="Success")
		{
			$ex_ordertotal=(float)str_replace(",", ".",(string)$response_exchange->Order[0]->orderTotalGross[0]);
	
	
		}
		else
		{
			echo "ERROR:".$responseXML_exchange;
			//show_error(0, 7, __FILE__, __LINE__, $responseXML);
			exit;
		}

		
		//GET LAST ORDERTOTAL FROM EXCHANGE
		$res_ordertotal = q("SELECT * FROM payment_notifications4 WHERE notification_type = 2 AND order_id = ".$exchange_orderid." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ordertotal)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_ordertotal = mysqli_fetch_assoc($res_ordertotal);

		//GET LAST ORDERDEPOSIT
		$res_orderdeposit = q("SELECT * FROM payment_notifications4 WHERE order_id = ".$exchange_orderid." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_orderdeposit)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_orderdeposit = mysqli_fetch_assoc($res_orderdeposit);

		$order_deposit=$PN_orderdeposit["order_deposit_EUR"]+$accounting;
	
		//SET USER_DEPOSIT
		//GET LAST 	USER_DEPOSIT
		$user_deposit = 0;
		$res_userdeposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$PN_orderdeposit["user_id"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_userdeposit)==0)
		{
			//KEINE Notification GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_userdeposit = mysqli_fetch_assoc($res_userdeposit);
		$user_deposit=$PN_userdeposit["user_deposit_EUR"]+$accounting;
		
		//SCHREIBE Interne Verrechnung AUF ExchangeORDER
		$state = "Credit Note";
		$state_reason = "preexchange";

		$insert_data=array();
		$insert_data["PNM_id"]=$_POST["order_event_id"];
		$insert_data["shop_id"]=$PN_ordertotal["shop_id"];
		$insert_data["PN_date"]=time();
		$insert_data["payment_date"]=time();
		$insert_data["notification_type"]=3;
		$insert_data["state"]=$state;
		$insert_data["order_id"]=$PN_ordertotal["order_id"];
		$insert_data["total"]=$ex_ordertotal;
		$insert_data["Currency"]=$PN_ordertotal["Currency"];
		$insert_data["exchange_rate_from_EUR"]=$PN_ordertotal["exchange_rate_from_EUR"];
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["order_deposit_EUR"]=$order_deposit;
		$insert_data["user_id"]=$PN_ordertotal["user_id"];
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=0;
		$insert_data["buyer_lastname"]=$PN_ordertotal["buyer_lastname"];
		$insert_data["buyer_firstname"]=$PN_ordertotal["buyer_firstname"];
		$insert_data["parentPaymentTransactionID"]=$id_PN;
		
		$res_insert=q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);

		$id_PN=mysqli_insert_id($dbshop);

		q("UPDATE payment_notifications4 SET paymentTransactionID = '".$id_PN."' WHERE id_PN = ".$id_PN, $dbshop, __FILE__, __LINE__);
		

	$processed=true;
	}
	
/**************************************************************************************************
 A C C O U N T I N G   R E T U R N
**************************************************************************************************/
	if ($_POST["mode"]=="AccountingReturn")
	{
		check_man_params(array("return_id" => "numericNN"));
		
		//CHECK IF RETURN
		//GET RETURNDATA
		$postfields["API"]="shop";
		$postfields["APIRequest"]="OrderReturnGet";
		$postfields["return_id"]=$_POST["return_id"];
		$responseXML_return=post(PATH."soa2/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response_return = new SimpleXMLElement($responseXML_return);
		}
		catch(Exception $e)
		{
			//XML FEHLERHAFT
			echo "XMLERROR".$responseXML_return;
			//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			//exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response_return->Ack[0]!="Success")
		{
			echo "ERROR:".$responseXML_return;
			//show_error(0, 7, __FILE__, __LINE__, $responseXML);
			exit;
		}

		if ($response_return->orderreturn[0]->return_type[0]!="exchange")
		{
			//REURN IST KEIN EXCHANGE
			//show_error();
			exit;
		}

		//GET TOTAL OF RETURNITEMS
		$returntotal=0;
		$returntotalEUR = 0;
		for ($i=0; isset($response_return->orderreturn[0]->returnitems[0]->returnitem[$i]); $i++)		
		{
			$returntotal+=(float)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->price[0]*(int)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->amount[0];
			$returntotalEUR+=(float)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->price[0]*(int)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->amount[0]*(1/(float)$response_return->orderreturn[0]->returnitems[0]->returnitem[$i]->exchange_rate_to_EUR[0]);
			//$exchangerate=(float)$response_return->orderreturn[0]->returnitem[$i]->exchange_rate_to_EUR[0];
		}
		
		//GET LAST ORDERTOTAL
		$res_ordertotal = q("SELECT * FROM payment_notifications4 WHERE notification_type = 2 AND order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ordertotal)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_ordertotal = mysqli_fetch_assoc($res_ordertotal);
		
		$last_ordertotal=$PN_ordertotal["total"]*(1/$PN_ordertotal["exchange_rate_from_EUR"]);
		
		$new_ordertotal=($act_ordertotal*$PN_ordertotal["exchange_rate_from_EUR"])-$returntotal;
		$new_ordertotalEUR=$act_ordertotal-$returntotalEUR;
		
		
		$accounting = $last_ordertotal-$new_ordertotalEUR;
		
		//GET LAST ORDERDEPOSIT
		$res_orderdeposit = q("SELECT * FROM payment_notifications4 WHERE order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_orderdeposit)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_orderdeposit = mysqli_fetch_assoc($res_orderdeposit);

		$order_deposit=$PN_orderdeposit["order_deposit_EUR"]+$accounting;
	
		//SET USER_DEPOSIT
		//GET LAST 	USER_DEPOSIT
		$user_deposit = 0;
		$res_userdeposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$PN_orderdeposit["user_id"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_userdeposit)==0)
		{
			//KEINE Notification GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_userdeposit = mysqli_fetch_assoc($res_userdeposit);
		$user_deposit=$PN_userdeposit["user_deposit_EUR"]+$accounting;

		//SCHREIBE GUTSCHRIFT AUF ORDER
		$state = "Credit Note";
		$state_reason = "ItemsReturn";

		$insert_data=array();
		$insert_data["PNM_id"]=$_POST["order_event_id"];
		$insert_data["shop_id"]=$PN_ordertotal["shop_id"];
		$insert_data["PN_date"]=time();
		$insert_data["payment_date"]=time();
		$insert_data["notification_type"]=3;
		$insert_data["state"]=$state;
		$insert_data["state_reason"]=$state_reason;
		$insert_data["order_id"]=$PN_ordertotal["order_id"];
		$insert_data["total"]=$new_ordertotal;
		$insert_data["Currency"]=$PN_ordertotal["Currency"];
		$insert_data["exchange_rate_from_EUR"]=$PN_ordertotal["exchange_rate_from_EUR"];
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["order_deposit_EUR"]=$order_deposit;
		$insert_data["user_id"]=$PN_ordertotal["user_id"];
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=0;
		$insert_data["buyer_lastname"]=$PN_ordertotal["buyer_lastname"];
		$insert_data["buyer_firstname"]=$PN_ordertotal["buyer_firstname"];
		
		$res_insert=q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);
	
		$id_PN=mysqli_insert_id($dbshop);

		$processed=true;
	}


/**************************************************************************************************
C R E D I T F R O M P R O M O T I O N
**************************************************************************************************/
	if ($_POST["mode"]=="CreditFromPromotion")
	{
		check_man_params(array("user_id" => "numericNN", "credit_gross" => "numericNN", "state" => "textNN", "state_reason" => "textNN"));
		
		$accounting = $_POST["credit_gross"];

		//GET LAST 	USER_DEPOSIT
		$user_deposit = 0;
		$res_userdeposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$_POST["user_id"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_userdeposit)==0)
		{
			//KEINE Notification GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		
		//GET USERDATA
		$res_user = q("SELECT * FROM cms_users WHERE id_user = ".$_POST["user_id"], $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_user)==0)
		{
			//USER NICHT GEFUNDEN
			//show_error();
			exit;
		}
		
		$user=mysqli_fetch_assoc($res_user);
		
		if ($user["firstname"]=="" && $user["lastname"]=="")
		{
			if (strpos($user["name"]," ")===false)
			{
				$firstname=substr($user["name"], 0, strpos($user["name"],"."));
				
				$lastname=substr($user["name"], strpos($user["name"],".")+1);
			}
			else
			{
				$firstname=substr($user["name"], 0, strpos($user["name"]," "));
				
				$lastname=substr($user["name"], strpos($user["name"]," ")+1);
			}
			
			if ($firstname=="")
			{
				$lastname=$user["name"];
			}
		}
		else
		{
			$firstname=$user["firstname"];
			$lastname=$user["lastname"];
		}
		
		
		$PN_userdeposit = mysqli_fetch_assoc($res_userdeposit);
		$user_deposit=$PN_userdeposit["user_deposit_EUR"]+$accounting;
	

		//SCHREIBE GUTSCHRIFT AUF ORDER
		$state = $_POST["state"];
		$state_reason = $_POST["state_reason"];

		$insert_data=array();
		$insert_data["PNM_id"]=0;
		$insert_data["shop_id"]=0;
		$insert_data["PN_date"]=time();
		$insert_data["payment_date"]=time();
		$insert_data["notification_type"]=4;
		$insert_data["state"]=$state;
		$insert_data["state_reason"]=$state_reason;
		$insert_data["order_id"]=0;
		$insert_data["total"]=$accounting;
		$insert_data["Currency"]="EUR";
		$insert_data["exchange_rate_from_EUR"]=1;
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["order_deposit_EUR"]=0;
		$insert_data["user_id"]=$_POST["user_id"];
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=0;
		$insert_data["buyer_lastname"]=$lastname;
		$insert_data["buyer_firstname"]=$firstname;
		
		$res_insert=q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);
	
		$id_PN=mysqli_insert_id($dbshop);

		$processed=true;

	}

/**************************************************************************************************
C R O S S E N T R Y F R O M O R D E R D E P O S I T
**************************************************************************************************/
	if ($_POST["mode"]=="CrossEntryFromOrderDeposit")
	{
		check_man_params(array("crossentry_gross" => "numericNN", "orderid_from" => "numericNN"));

		//CHECK IF CREDIT_GROSS <= ORDERDEPOSIT
		//GET LAST ORDERDEPOSIT
		$order_deposit_from=0;
		$res_orderdeposit_from = q("SELECT * FROM payment_notifications4 WHERE order_id = ".$_POST["orderid_from"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_orderdeposit_from)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_orderdeposit_from = mysqli_fetch_assoc($res_orderdeposit_from);
		
		if ($_POST["crossentry_gross"]>$PN_orderdeposit_from["order_deposit_EUR"])
		{	
			//CREDIT_GROSS ÜBERSTEIGT ZULÄSSIGE SUMME
			echo "Crossentry_GROSS ÜBERSTEIGT ZULÄSSIGE SUMME";
			//show_error();
			exit;
		}
		
		$accounting =  $_POST["crossentry_gross"]*-1;

		//GET LAST 	USER_DEPOSIT
		$user_deposit = 0;
		$res_userdeposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$user_id." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_userdeposit)==0)
		{
			//KEINE Notification GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_userdeposit = mysqli_fetch_assoc($res_userdeposit);
	
		$user_deposit=$PN_userdeposit["user_deposit_EUR"]+$accounting;
		$order_deposit_from=$PN_orderdeposit_from["order_deposit_EUR"]+$accounting;
		
		//SYSTEMBUCHUNG FROM ORDER
		$state = "Cross Entry";
		$state_reason = "accounting orderdeposit";

		$insert_data=array();
		$insert_data["PNM_id"]=$PN_orderdeposit_from["id_PN"];
		$insert_data["shop_id"]=$PN_orderdeposit_from["shop_id"];
		$insert_data["PN_date"]=time();
		$insert_data["payment_date"]=time();
		$insert_data["notification_type"]=3;
		$insert_data["state"]=$state;
		$insert_data["state_reason"]=$state_reason;
		$insert_data["order_id"]=$_POST["orderid_from"];
		$insert_data["total"]=$PN_orderdeposit_from["total"];
		$insert_data["Currency"]=$PN_orderdeposit_from["Currency"];
		$insert_data["exchange_rate_from_EUR"]=$PN_orderdeposit_from["exchange_rate_from_EUR"];
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["order_deposit_EUR"]=$order_deposit_from;
		$insert_data["user_id"]=$user_id;
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=0;
		$insert_data["buyer_lastname"]=$PN_orderdeposit_from["buyer_lastname"];
		$insert_data["buyer_firstname"]=$PN_orderdeposit_from["buyer_firstname"];
		
		$res_insert=q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);
	
		$id_PN=mysqli_insert_id($dbshop);
		q("UPDATE payment_notifications4 SET paymentTransactionID = '".$id_PN."' WHERE id_PN = ".$id_PN, $dbshop, __FILE__, __LINE__);


	//******************
	
		$order_deposit_to=0;
		$res_orderdeposit_to = q("SELECT * FROM payment_notifications4 WHERE order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_orderdeposit_to)==0)
		{
			//KEINE ORDER GEFUNDEN -> für update muss notification vorhanden sein
			//show_error();
			exit;
		}
		$PN_orderdeposit_to = mysqli_fetch_assoc($res_orderdeposit_to);
		$order_deposit_to=$PN_orderdeposit_to["order_deposit_EUR"]+$accounting;


		//SYSTEMBUCHUNG TO ORDER
		
		$accounting*=-1;
		$user_deposit+=$accounting;
		
		$state = "Cross Entry";
		$state_reason = "accounting orderdeposit";

		$insert_data=array();
		$insert_data["PNM_id"]=$id_PN;
		$insert_data["shop_id"]=$shop_id;
		$insert_data["PN_date"]=time();
		$insert_data["payment_date"]=time();
		$insert_data["notification_type"]=3;
		$insert_data["state"]=$state;
		$insert_data["state_reason"]=$state_reason;
		$insert_data["order_id"]=$_POST["orderid"];
		$insert_data["total"]=$act_ordertotal;
		$insert_data["Currency"]=$currency;
		$insert_data["exchange_rate_from_EUR"]=$exchangerate;
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["order_deposit_EUR"]=$order_deposit_to;
		$insert_data["user_id"]=$user_id;
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=0;
		$insert_data["buyer_lastname"]=$buyer_lastname;
		$insert_data["buyer_firstname"]=$buyer_firstname;
		$insert_data["parentPaymentTransactionID"]=$id_PN;
		
		$res_insert=q_insert("payment_notifications4", $insert_data, $dbshop, __FILE__, __LINE__);
	
		$id_PN=mysqli_insert_id($dbshop);
		
		q("UPDATE payment_notifications4 SET paymentTransactionID = '".$id_PN."' WHERE id_PN = ".$id_PN, $dbshop, __FILE__, __LINE__);

		$processed=true;
	}

/**************************************************************************************************
C R E D I T T O O R D E R
**************************************************************************************************/
	if ($_POST["mode"]=="CreditToOrder")
	{
		check_man_params(array("TransactionID" => "textNN", "credit_gross" => "numericNN"));
		
		//CHECK IF CREDIT EXISTS 
		$res_check=q("SELECT * FROM payment_notifications4 WHERE paymentTransactionID = '".$_POST["TransactionID"]."' AND user_id = ".$user_id, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==0)
		{
			//GUTSCHRIFT EXISTIERT NICHT
			echo "GUTSCHRIFT EXISTIERT NICHT";
			//show_error();
			exit;
		}
		$credit=mysqli_fetch_assoc($res_check);
		
		//CHECK IF CREDIT STILL IS OPEN / GET DIFF TOTAL
		$credit_accountings=0;
		$res_credit_accountings=q("SELECT * FROM payment_notifications4 WHERE notification_type = 3 AND user_id = ".$user_id." AND PNM_id = ".$credit["id_PN"], $db_shop, __FILE__, __LINE__);
		while ($row_credit_accountings = mysqli_fetch_assoc($res_credit_accountings))
		{
			
			
		}
		

		$processed=true;
	}

	if ($processed)
	{
	
	}
	else
	{
		//KEINE BEARBEITUNG DER PAYMENTNOTIFICATION
		echo "KEINE BEARBEITUNG DER PAYMENTNOTIFICATION";
		//show_error();
		exit;
	}
		
		
?>