<?php

	define("PN_Table", "payment_notifications4");
	define("PNM_Table", "payment_notification_messages4");
	
	define("SHOP_ORDER", "shop_orders");
	define("SHOP_ORDER_ITEMS", "shop_orders_items");


	function getLastUserDeposit($userid)
	{

		
		$postfields = array();
		$postfields["API"] = "payments";
		$postfields["APIRequest"] = "PaymentNotificationLastUserDepositGet";
		$postfields["userid"]=$userid;
	//	$use_errors = libxml_use_internal_errors(true);
/*
		$responseXML = post(PATH."soa2/", $postfields);
		try 
		{
			$response = new SimpleXMLElement($responseXML);
			
		}
		catch (Exception $e)
		{ 
			show_error(9756, 7, __FILE__, __LINE__, "ServiceResponse getLastUserDeposit:".$responseXML.print_r($postfields, true));
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
*/
		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			return (float)$response->user_deposit[0];
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse getLastUserDeposit:".print_r($response, true).print_r($postfields, true));
			exit;
		}
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
		$postfields["payments_type_id"]=4;
		
		$response = soa2($postfields, __FILE__, __LINE__);
		if ($response->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse update_PaymentStatus:".print_r($response, true).print_r($postfields, true));
		}
	}

//********************************************************************************************************************************************

	//$required=array("ipn_track_id" => "textNN");
	$required=array("id" => "numericNN");
	check_man_params($required);
	
	
	//GET PAYMENT MESSAGE
	$res_PNM=q("SELECT * FROM ".PNM_Table." WHERE id = ".$id, $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_PNM)==0)
	{
		//PAYMENTMESSAGE NICHT GEFUNDEN
		show_error(9803, 9, __FILE__, __LINE__, "PaymentMessageID".$id);
		exit;
	}
	$row_PNM=mysqli_fetch_assoc($res_PNM);
	
	if ($row_PNM["processed"]==1)
	{
		//MESSAGE ALREADY PROCESSED
		show_error(9804, 9, __FILE__, __LINE__, "PaymentMessageID".$id);
		exit;
	}
/*	
	if ($row_PNM["checked"]!="VERIFIED")
	{
		//MESSAGE NOT VALID
		show_error(9805, 9, __FILE__, __LINE__, "PaymentMessageID".$id);
		exit;
	}
*/	
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

	//GET CURRECNIES AND EXCHANGERATES
	$currencies=array();
	$res_curr=q("SELECT * FROM shop_currencies", $dbshop, __FILE__, __LINE__);
	while ($row_curr = mysqli_fetch_assoc($res_curr))
	{
		$currencies[$row_curr["currency_code"]] = $row_curr;
	}


	//$payment_date=urldecode($_POST["payment_date"]);
	
	$paymentdate=strtotime(urldecode($PN_data["payment_date"]));
	
	//$payer_email=urldecode($_POST["payer_email"]);
	//$receiver_email=urldecode($_POST["receiver_email"]);
	
	$processed=false;
	
	$accounting = 0;


/**********************************************************************
C O M P L E T E D 
**********************************************************************/
// - BUCHUNGSWIRKSAM

	if ($PN_data["payment_status"]=="Completed")
	{
		//FOR EBAY TRANSACTION
		if (isset($PN_data["for_auction"]) && ($PN_data["for_auction"]=="true" || $PN_data["for_auction"]=="TRUE"))
		{
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_item=q("SELECT * FROM ".SHOP_ORDER_ITEMS." WHERE foreign_transactionID = '".$PN_data["ebay_txn_id1"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				//KEIN ITEM GEFUNDEN	
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];

			}
			if (mysqli_num_rows($res_item)>1)
			{
				//KEIN EINDEUTIGES ITEM
				show_error(9806, 7, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]);
				exit;
			}
			
			if (mysqli_num_rows($res_item)==1)
			{
				//Search for ORDER
				$item=mysqli_fetch_assoc($res_item);
				
				//CHECK CURRENCY
				if ($PN_data["mc_currency"]!=$item["Currency_Code"])
				{
					//CURRENCIES STIMMEN NICHT ÜBEREIN
					show_error(9807, 9, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]. "OrderItemID: ".$item["id"], false);
					$currency = "";
					$exchange_rate = 0;
				}
				else
				{
					$currency = $PN_data["mc_currency"];
					$exchange_rate = $item["exchange_rate_to_EUR"];
				}
							
				$res_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$item["order_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order)==0)
				{
					//KEINE ORDER ZUM ITEM GEFUNDEN
					show_error(9762, 7, __FILE__, __LINE__, "OrderID: ".$item["order_id"]." OrderItemID: ".$item["id"], false);
					exit;
				}
				if (mysqli_num_rows($res_order)==1)
				{
					$order=mysqli_fetch_assoc($res_order);
					$shop_id = $order["shop_id"];
					$order_id = $order["id_order"];
					$user_id = $order["customer_id"];
					
				}
				else
				{
					$shop_id = 0;
					$order_id = 0;
					$user_id = 0;
				}
				
				
			}
			else 
			{
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
	
				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;			
			}
			
			$orderTransactionID=$PN_data["ebay_txn_id1"];

			$payment_status = $PN_data["payment_status"];
			$pending_reason = $PN_data["pending_reason"];

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		//elseif ($_POST["txn_type"]=="express_checkout")
		else
		{
		//ONLINE-SHOP PAYMENTS


			$res_order = q("SELECT * FROM ".SHOP_ORDER." WHERE Payments_TransactionID = '".$PN_data["txn_id"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_order)==0)
			{
				//KEINE ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
			}
			if (mysqli_num_rows($res_order)>1)
			{
				//KEINE eindeutige ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error(9808, 9, __FILE__, __LINE__, "Payments_TransactionID: ".$PN_data["txn_id"]);
				exit;
			}
			if (mysqli_num_rows($res_order)==1)
			{
				$order=mysqli_fetch_assoc($res_order);
				$shop_id = $order["shop_id"];
				$order_id = $order["id_order"];
				$user_id = $order["customer_id"];
				
				//GET ORDERS_ITEMS
				$res_order_items = q("SELECT * FROM ".SHOP_ORDERS_ITMES." WHERE order_id = ".$order["id_order"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order_items)==0)
				{ 
					//KEIN ITEM ZUR ORDER GEFUNDEN
					$currency = $PN_data["mc_currency"];
					$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
				}
				else
				{
					$item = mysqli_fetch_assoc($res_order_items);
					
					//CHECK CURRENCY
					if ($PN_data["mc_currency"]!=$item["Currency_Code"])
					{
						show_error(9807, 9, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]. "OrderItemID: ".$item["id"], false);
						$currency = "";
						$exchange_rate = 0;
					}
					else
					{
						$currency = $PN_data["mc_currency"];
						$exchange_rate = $item["exchange_rate_to_EUR"];
					}
				}
				
			}
			else
			{
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];

				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;
			}
			$orderTransactionID = $order_id;
			
			$payment_status = $PN_data["payment_status"];
			$pending_reason = '';
			
			
			//SENDE VERKAUFSMAIL WENN STATUS ZUVOR CREATED ODER PENDING WAR
			if (($order["Payments_TransactionState"] == "Created" || $order["Payments_TransactionState"] == "Pending") && $order_id!=0)
			{
				$post_data=array();
				$post_data["API"]="shop";
				$post_data["APIRequest"]="MailOrderSeller";
				$post_data["order_id"]=$order_id;
				
				$postdata=http_build_query($post_data);

				$response = soa2($postdata);
				
			}
			
		} // SHOP ORDER
		
		//GET "MOTHER ORDER"
		$combined_with = 0;
		if ($order_id!=0)
		{
			$res_m_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)>0)
			{
				$row_m_order = mysqli_fetch_assoc($res_m_order);
				if ($row_m_order["combined_with"]>0) 
				{
					$order_id = $row_m_order["combined_with"];
					$combined_with = $order_id;
				}
			}
		}

		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			update_PaymentStatus ($order_id, $PN_data["txn_id"], $PN_data["payment_status"], $paymentdate);	
		}
		/*
		if ($order_id!=0)
		{
			if ($row_m_order["status_id"]==1 || $row_m_order["status_id"]==5)
			{
				$status_id = 7;
				$state_date = time();
			}
			else
			{
				$status_id = $row_m_order["status_id"];
				$status_date = $row_m_order["status_date"];
			}
			
			
			// "COMPLETED" CAN UPDATE [ CREATED||PENDING ] WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$PN_data["txn_id"])
			{
				if ($order["Payments_TransactionState"] == "Created" || $order["Payments_TransactionState"] == "Pending")
				{
					//UPDATE SHOP_ORDERS
					//echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
					if ($combined_with==0)
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Completed', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Completed', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__);
					}
				}
				else
				{
					//echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
					//echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
					if ($combined_with==0)
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Completed', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Completed', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__);
					}

				}
				else
				{
				//	echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older ". $paymentdate;
				}
			}
		}
		*/

		if ($exchange_rate!=0)
		{
			$accounting = $PN_data["mc_gross"]*(1/$exchange_rate);
		}
		else
		{
			$accounting = 0;
		}

		//BUCHUNG FÜR USER_DEPOSIT in EUR
		//if ($user_id != 0 )
		{
			$user_deposit = getLastUserDeposit($user_id);
			$user_deposit+= $accounting;
		}
				
	$processed=true;
	}
		
/**********************************************************************
P E N D I N G
**********************************************************************/
		
	if ($PN_data["payment_status"]=="Pending")
	{
		//FOR EBAY TRANSACTION

		if (isset($PN_data["for_auction"]) && ($PN_data["for_auction"]=="true" || $PN_data["for_auction"]=="TRUE"))
		{

			/*
			if ($_POST["receiver_id"]=="Q7YPYZF9B7R5W")
			{
				$shop_id=3;
			}
			elseif ($_POST["receiver_id"]=="CXAY2DMMVAE6G") 
			{
				$shop_id=4;
			}
			else
			{
				//KEIN RECIEVERKONTO GEFUNDEN
				show_error();
			}
			*/
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_item=q("SELECT * FROM ".SHOP_ORDER_ITEMS." WHERE foreign_transactionID = '".$PN_data["ebay_txn_id1"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				//KEIN ITEM GEFUNDEN	
			}
			if (mysqli_num_rows($res_item)>1)
			{
				//KEIN EINDEUTIGES ITEM
				show_error(9806, 7, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]);
				exit;
			}
			
			if (mysqli_num_rows($res_item)==1)
			{
				//Search for ORDER
				$item=mysqli_fetch_assoc($res_item);
				
				//CHECK CURRENCY
				if ($PN_data["mc_currency"]!=$item["Currency_Code"])
				{
					//CURRENCIES STIMMEN NICHT ÜBEREIN
					show_error(9807, 9, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]. "OrderItemID: ".$item["id"], false);
					$currency = "";
					$exchange_rate = 0;
				}
				else
				{
					$currency = $PN_data["mc_currency"];
					$exchange_rate = $item["exchange_rate_to_EUR"];
				}

				$res_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$item["order_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order)==0)
				{
					//KEINE ORDER ZUM ITEM GEFUNDEN
					show_error(9762, 7, __FILE__, __LINE__, "OrderID: ".$item["order_id"]." OrderItemID: ".$item["id"], false);
					exit;
				}
				if (mysqli_num_rows($res_order)==1)
				{
					$order=mysqli_fetch_assoc($res_order);
					$shop_id = $order["shop_id"];
					$order_id = $order["id_order"];
					$user_id = $order["customer_id"];
				}
				else
				{
					$shop_id = 0;
					$order_id = 0;
					$user_id = 0;
				}
			}
			else 
			{	
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
	
				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;			
			}
			
			$orderTransactionID=$PN_data["ebay_txn_id1"];
				
			$payment_status = $PN_data["payment_status"];
			$pending_reason = $PN_data["pending_reason"];

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		//elseif ($_POST["txn_type"]=="express_checkout")
		else
		{
		//ONLINE-SHOP PAYMENTS

			$res_order = q("SELECT * FROM ".SHOP_ORDER." WHERE Payments_TransactionID = '".$PN_data["txn_id"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_order)==0)
			{
				//KEINE ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
			}
			if (mysqli_num_rows($res_order)>1)
			{
				//KEINE eindeutige ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error(9808, 9, __FILE__, __LINE__, "Payments_TransactionID: ".$PN_data["txn_id"]);
				exit;
			}
			if (mysqli_num_rows($res_order)==1)
			{
				$order=mysqli_fetch_assoc($res_order);
				$shop_id = $order["shop_id"];
				$order_id = $order["id_order"];
				$user_id = $order["customer_id"];

				//GET ORDERS_ITEMS
				$res_order_items = q("SELECT * FROM ".SHOP_ORDER_ITEMS." WHERE order_id = ".$order["id_order"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order_items)==0)
				{ 
					//KEIN ITEM ZUR ORDER GEFUNDEN
					$currency = $PN_data["mc_currency"];
					$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
				}
				else
				{
					$item = mysqli_fetch_assoc($res_order_items);
					
					//CHECK CURRENCY
					if ($PN_data["mc_currency"]!=$item["Currency_Code"])
					{
						//CURRENCIES STIMMEN NICHT ÜBEREIN
						show_error(9807, 9, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]. "OrderItemID: ".$item["id"], false);
						
						$currency = "";
						$exchange_rate = 0;
					}
					else
					{
						$currency = $PN_data["mc_currency"];
						$exchange_rate = $item["exchange_rate_to_EUR"];
					}
				}
			}
			else
			{
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];

				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;
			}
			$orderTransactionID = $order_id;
			
			$payment_status = $PN_data["payment_status"];
			$pending_reason = $PN_data["pending_reason"];
		}

		//GET "MOTHER ORDER"
		$combined_with = 0;
		if ($order_id!=0)
		{
			$res_m_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)>0)
			{
				$row_m_order = mysqli_fetch_assoc($res_m_order);
				if ($row_m_order["combined_with"]>0) 
				{
					$order_id = $row_m_order["combined_with"];
					$combined_with = $order_id;
				}
			}
		}

		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			update_PaymentStatus ($order_id, $PN_data["txn_id"], $PN_data["payment_status"], $paymentdate);	
		}
/*
		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			if ($row_m_order["status_id"]==1 || $row_m_order["status_id"]==5)
			{
				$status_id = 7;
				$state_date = time();
			}
			else
			{
				$status_id = $row_m_order["status_id"];
				$status_date = $row_m_order["status_date"];
			}

			// "PENDING" CAN UPDATE [ CREATED ] WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$PN_data["txn_id"])
			{
				if ($order["Payments_TransactionState"] == "Created")
				{
					//UPDATE SHOP_ORDERS
				//	echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Pending";
					if ($combined_with==0)
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Pending', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Pending', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__);
					}

				}
				else
				{
				//	echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
				//	echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Pending";
					if ($combined_with==0)
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Pending', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Pending', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__);
					}

				}
				else
				{
			//		echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}
		*/
	//PENDING IST NICHT BUCHUNGSWIRKSAM
	$accounting=0;
	$user_deposit = getLastUserDeposit($user_id);


	$processed=true;
	}

/**********************************************************************
C R E A T E D
**********************************************************************/
		
	if ($PN_data["payment_status"]=="Created")
	{
		//FOR EBAY TRANSACTION
		if (isset($PN_data["for_auction"]) && ($PN_data["for_auction"]=="true" || $PN_data["for_auction"]=="TRUE"))
		{
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_item=q("SELECT * FROM ".SHOP_ORDER_ITEMS." WHERE foreign_transactionID = '".$PN_data["ebay_txn_id1"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				//KEIN ITEM GEFUNDEN	
			}
			if (mysqli_num_rows($res_item)>1)
			{
				//KEIN EINDEUTIGES ITEM
				show_error(9806, 7, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]);
				exit;
			}
			
			if (mysqli_num_rows($res_item)==1)
			{
				//Search for ORDER
				$item=mysqli_fetch_assoc($res_item);
				
				//CHECK CURRENCY
				if ($PN_data["mc_currency"]!=$item["Currency_Code"])
				{
					//CURRENCIES STIMMEN NICHT ÜBEREIN
					show_error(9807, 9, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]. "OrderItemID: ".$item["id"], false);
					$currency = "";
					$exchange_rate = 0;
				}
				else
				{
					$currency = $PN_data["mc_currency"];
					$exchange_rate = $item["exchange_rate_to_EUR"];
				}

				$res_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$item["order_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order)==0)
				{
					//KEINE ORDER ZUM ITEM GEFUNDEN
					show_error(9762, 7, __FILE__, __LINE__, "OrderID: ".$item["order_id"]." OrderItemID: ".$item["id"], false);
					exit;
				}
				if (mysqli_num_rows($res_order)==1)
				{
					$order=mysqli_fetch_assoc($res_order);
					$shop_id = $order["shop_id"];
					$order_id = $order["id_order"];
					$user_id = $order["customer_id"];
				}
				else
				{
					$shop_id = 0;
					$order_id = 0;
					$user_id = 0;
				}
			}
			else 
			{	
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
	
				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;			
			}
			
			$orderTransactionID=$PN_data["ebay_txn_id1"];
				
			$payment_status = $PN_data["payment_status"];
			$pending_reason = $PN_data["pending_reason"];

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		//elseif ($_POST["txn_type"]=="express_checkout")
		else
		{
		//ONLINE-SHOP PAYMENTS

			$res_order = q("SELECT * FROM ".SHOP_ORDER." WHERE Payments_TransactionID = '".$PN_data["txn_id"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_order)==0)
			{
				//KEINE ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
			}
			if (mysqli_num_rows($res_order)>1)
			{
				//KEINE eindeutige ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error(9808, 9, __FILE__, __LINE__, "Payments_TransactionID: ".$PN_data["txn_id"]);
				exit;
			}
			if (mysqli_num_rows($res_order)==1)
			{
				$order=mysqli_fetch_assoc($res_order);
				$shop_id = $order["shop_id"];
				$order_id = $order["id_order"];
				$user_id = $order["customer_id"];

				//GET ORDERS_ITEMS
				$res_order_items = q("SELECT * FROM ".SHOP_ORDER_ITEMS." WHERE order_id = ".$order["id_order"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order_items)==0)
				{ 
					//KEIN ITEM ZUR ORDER GEFUNDEN
					$currency = $PN_data["mc_currency"];
					$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
				}
				else
				{
					$item = mysqli_fetch_assoc($res_order_items);
					
					//CHECK CURRENCY
					if ($PN_data["mc_currency"]!=$item["Currency_Code"])
					{
						//CURRENCIES STIMMEN NICHT ÜBEREIN
						show_error(9807, 9, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]. "OrderItemID: ".$item["id"], false);
						
						$currency = "";
						$exchange_rate = 0;
					}
					else
					{
						$currency = $PN_data["mc_currency"];
						$exchange_rate = $item["exchange_rate_to_EUR"];
					}
				}
			}
			else
			{
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];

				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;
			}
			$orderTransactionID = $order_id;
			
			$payment_status = $PN_data["payment_status"];
			$pending_reason = $PN_data["pending_reason"];
		}

		//GET "MOTHER ORDER"
		$combined_with = 0;
		if ($order_id!=0)
		{
			$res_m_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)>0)
			{
				$row_m_order = mysqli_fetch_assoc($res_m_order);
				if ($row_m_order["combined_with"]>0) 
				{
					$order_id = $row_m_order["combined_with"];
					$combined_with = $order_id;
				}
			}
		}

		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			update_PaymentStatus ($order_id, $PN_data["txn_id"], $PN_data["payment_status"], $paymentdate);	
		}
/*		
		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			if ($row_m_order["status_id"]==1 || $row_m_order["status_id"]==5)
			{
				$status_id = 7;
				$state_date = time();
			}
			else
			{
				$status_id = $row_m_order["status_id"];
				$status_date = $row_m_order["status_date"];
			}
			// "CREATED" CAN ONLY BE SET IF THERE IS NO OTHER STATE WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$PN_data["txn_id"])
			{
				if ($order["Payments_TransactionState"] == "")
				{
					//UPDATE SHOP_ORDERS
				//	echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Created";
					if ($combined_with==0)
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Created', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Created', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__);
					}
				}
				else
				{
				//	echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
					//echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
					if ($combined_with==0)
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Created', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Created', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__);
					}
				}
				else
				{
				//	echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}
	*/
	//CREATED IST NICHT BUCHUNGSWIRKSAM
	$accounting=0;
	$user_deposit = getLastUserDeposit($user_id);

	$processed=true;
	}


/**********************************************************************
D E N I E D
**********************************************************************/
		
	if ($PN_data["payment_status"]=="Denied")
	{
		//FOR EBAY TRANSACTION
		if (isset($PN_data["for_auction"]) && ($PN_data["for_auction"]=="true" || $PN_data["for_auction"]=="TRUE"))
		{
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_item=q("SELECT * FROM ".SHOP_ORDER_ITEMS." WHERE foreign_transactionID = '".$PN_data["ebay_txn_id1"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				//KEIN ITEM GEFUNDEN	
			}
			if (mysqli_num_rows($res_item)>1)
			{
				//KEIN EINDEUTIGES ITEM
				show_error(9806, 7, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]);
				exit;
			}
			
			if (mysqli_num_rows($res_item)==1)
			{
				//Search for ORDER
				$item=mysqli_fetch_assoc($res_item);
				
				//CHECK CURRENCY
				//CHECK CURRENCY
				if ($PN_data["mc_currency"]!=$item["Currency_Code"])
				{
					//CURRENCIES STIMMEN NICHT ÜBEREIN
					show_error(9807, 9, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]. "OrderItemID: ".$item["id"], false);
					$currency = "";
					$exchange_rate = 0;
				}
				else
				{
					$currency = $PN_data["mc_currency"];
					$exchange_rate = $item["exchange_rate_to_EUR"];
				}

				$res_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$item["order_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order)==0)
				{
					//KEINE ORDER ZUM ITEM GEFUNDEN
					show_error(9762, 7, __FILE__, __LINE__, "OrderID: ".$item["order_id"]." OrderItemID: ".$item["id"], false);
					exit;
				}
				if (mysqli_num_rows($res_order)==1)
				{
					$order=mysqli_fetch_assoc($res_order);
					$shop_id = $order["shop_id"];
					$order_id = $order["id_order"];
					$user_id = $order["customer_id"];
				}
				else
				{
					$shop_id = 0;
					$order_id = 0;
					$user_id = 0;
				}
			}
			else 
			{	
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
	
				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;			
			}
			
			$orderTransactionID=$PN_data["ebay_txn_id1"];
				
			$payment_status = $PN_data["payment_status"];
			$pending_reason = "";

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		//elseif ($_POST["txn_type"]=="express_checkout")
		else
		{
		//ONLINE-SHOP PAYMENTS

			$res_order = q("SELECT * FROM ".SHOP_ORDER." WHERE Payments_TransactionID = '".$PN_data["txn_id"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_order)==0)
			{
				//KEINE ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
			}
			if (mysqli_num_rows($res_order)>1)
			{
				//KEINE eindeutige ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error(9808, 9, __FILE__, __LINE__, "Payments_TransactionID: ".$PN_data["txn_id"]);
				exit;
			}
			if (mysqli_num_rows($res_order)==1)
			{
				$order=mysqli_fetch_assoc($res_order);
				$shop_id = $order["shop_id"];
				$order_id = $order["id_order"];
				$user_id = $order["customer_id"];

				//GET ORDERS_ITEMS
				$res_order_items = q("SELECT * FROM ".SHOP_ORDER_ITEMS." WHERE order_id = ".$order["id_order"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order_items)==0)
				{ 
					//KEIN ITEM ZUR ORDER GEFUNDEN
					$currency = $PN_data["mc_currency"];
					$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
				}
				else
				{
					$item = mysqli_fetch_assoc($res_order_items);
					
					//CHECK CURRENCY
					if ($PN_data["mc_currency"]!=$item["Currency_Code"])
					{
						//CURRENCIES STIMMEN NICHT ÜBEREIN
						show_error(9807, 9, __FILE__, __LINE__, "EbayTransactionID".$PN_data["ebay_txn_id1"]. "OrderItemID: ".$item["id"], false);
						
						$currency = "";
						$exchange_rate = 0;
					}
					else
					{
						$currency = $PN_data["mc_currency"];
						$exchange_rate = $item["exchange_rate_to_EUR"];
					}
				}
			}
			else
			{
				$currency = $PN_data["mc_currency"];
				$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];

				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;
			}
			$orderTransactionID = $order_id;
			
			$payment_status = $PN_data["payment_status"];
			$pending_reason = "";
		}

		//GET "MOTHER ORDER"
		$combined_with = 0;
		if ($order_id!=0)
		{
			$res_m_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)>0)
			{
				$row_m_order = mysqli_fetch_assoc($res_m_order);
				if ($row_m_order["combined_with"]>0) 
				{
					$order_id = $row_m_order["combined_with"];
					$combined_with = $order_id;
				}
			}
		}

		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			update_PaymentStatus ($order_id, $PN_data["txn_id"], $PN_data["payment_status"], $paymentdate);	
		}
/*
		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			if ($row_m_order["status_id"]==1 || $row_m_order["status_id"]==5)
			{
				$status_id = 7;
				$state_date = time();
			}
			else
			{
				$status_id = $row_m_order["status_id"];
				$status_date = $row_m_order["status_date"];
			}

			// "DENIED" CAN UPDATE [ CREATED || PENDING ] WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$PN_data["txn_id"])
			{
				if ($order["Payments_TransactionState"] == "Created" || $order["Payments_TransactionState"] == "Pending")
				{
					//UPDATE SHOP_ORDERS
				//	echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Denied";
					if ($combined_with==0)
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Denied', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Denied', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__);
					}
				}
				else
				{
				//	echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
				//	echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
					if ($combined_with==0)
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Denied', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						q("UPDATE ".SHOP_ORDERS." SET Payments_TransactionState = 'Denied', Payments_TransactionStateDate =".time().", Payments_TransactionID = '".mysqli_real_escape_string($dbshop, $PN_data["txn_id"])."', status_id = ".$status_id.", status_date = ".$status_date." WHERE combined_with = ".$combined_with, $dbshop, __FILE__, __LINE__);
					}
				}
				else
				{
			//		echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}
*/
	//DENIED IST NICHT BUCHUNGSWIRKSAM
	$accounting=0;
	$user_deposit = getLastUserDeposit($user_id);

	$processed=true;
	}



/**********************************************************************
R E F U N D E D
**********************************************************************/
// - BUCHUNGSWIRKSAM
	if ($PN_data["payment_status"]=="Refunded")
	{
		
		$postfields = array();
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentGet";
		$postfields["TransactionID"]=$PN_data["parent_txn_id"];
		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			$user_id = (int)$response->PaymentUserID[0];
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true));
			exit;
		}
		
		$order_id = 0;
		$shop_id = 0;
		$orderTransactionID = "";
		
		$currency = $PN_data["mc_currency"];
		$exchange_rate = $currencies[$currency]["exchange_rate_to_EUR"];
print_r("EXCHANGE: ".$exchange_rate)		;
		$payment_status = $PN_data["payment_status"];
		$pending_reason = $PN_data["reason_code"];

		//BUCHUNG FÜR USER_DEPOSIT in EUR
		if ($exchange_rate != 0)
		{
			$accounting = $PN_data["mc_gross"]*(1/$exchange_rate);
		}
		else
		{
			$accounting = 0;
		}
		
//		$accounting*=-1;
		
		//REFUNDED IST  BUCHUNGSWIRKSAM
		$user_deposit = getLastUserDeposit($user_id);
		$user_deposit+= $accounting;

		$processed=true;

	}


/**********************************************************************
R E V E R S E D	
**********************************************************************/
// - BUCHUNGSWIRKSAM
	if ($PN_data["payment_status"]=="Reversed")
	{
		//FIND PARENTTRANSACTION
		$res_ipn = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID = '".$PN_data["parent_txn_id"]."' LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ipn)==0)
		{
			//KEINE ZUGEHÖRIGE IPN GEFUNDEN
			//show_error();
//			echo "KEINE ZUGEHÖRIGE IPN GEFUNDEN";
			$shop_id = 0;
			$orderTransactionID = "";
			$order_id = 0;
			$user_id = 0;

			$currency = "";
			$exchange_rate = 0;

		}
		else
		{
			$row_ipn = mysqli_fetch_assoc($res_ipn);
			
			$shop_id = $row_ipn["shop_id"];
			$orderTransactionID = $row_ipn["orderTransactionID"];
			$order_id = $row_ipn["order_id"];
			$user_id = $row_ipn["user_id"];
			
			$currency = $row_ipn["Currency"];
			$exchange_rate = $row_ipn["exchange_rate_from_EUR"];

		}
		
		$payment_status = $PN_data["payment_status"];
		$pending_reason = $PN_data["reason_code"];


		//GET "MOTHER ORDER"
		$combined_with = 0;
		if ($order_id!=0)
		{
			$res_m_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)>0)
			{
				$row_m_order = mysqli_fetch_assoc($res_m_order);
				if ($row_m_order["combined_with"]>0) 
				{
					$order_id = $row_m_order["combined_with"];
					$combined_with = $order_id;
				}
			}
		}

		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			update_PaymentStatus ($order_id, $PN_data["txn_id"], $PN_data["payment_status"], $paymentdate);	
		}

		//BUCHUNG FÜR USER_DEPOSIT in EUR
		if ($exchange_rate != 0)
		{
			$accounting = $PN_data["mc_gross"]*(1/$exchange_rate);
		}
		else
		{
			$accounting = 0;
		}
		
		$accounting*=-1;
		
		//Reversed IST  BUCHUNGSWIRKSAM
		$user_deposit = getLastUserDeposit($user_id);
		$user_deposit+= $accounting;

		$processed=true;

	}

/**********************************************************************
C A N C E L E D   R E V E R S A L
**********************************************************************/
// BUCHUNGSWIRKSAM		
	if ($PN_data["payment_status"]=="Canceled_Reversal")
	{
		//FIND PARENTTRANSACTION
		$res_ipn = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID = '".$PN_data["parent_txn_id"]."' LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ipn)==0)
		{
			//KEINE ZUGEHÖRIGE IPN GEFUNDEN
			//show_error();
	//		echo "KEINE ZUGEHÖRIGE IPN GEFUNDEN";
			$shop_id = 0;
			$orderTransactionID = "";
			$order_id = 0;
			$user_id = 0;

			$currency = "";
			$exchange_rate = 0;

		}
		else
		{
			$row_ipn = mysqli_fetch_assoc($res_ipn);
			
			$shop_id = $row_ipn["shop_id"];
			$orderTransactionID = $row_ipn["orderTransactionID"];
			$order_id = $row_ipn["order_id"];
			$user_id = $row_ipn["user_id"];

			$currency = $row_ipn["Currency"];
			$exchange_rate = $row_ipn["exchange_rate_from_EUR"];

		}
		
		$payment_status = $PN_data["payment_status"];
		$pending_reason = $PN_data["reason_code"];

		//GET "MOTHER ORDER"
		$combined_with = 0;
		if ($order_id!=0)
		{
			$res_m_order = q("SELECT * FROM ".SHOP_ORDER." WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)>0)
			{
				$row_m_order = mysqli_fetch_assoc($res_m_order);
				if ($row_m_order["combined_with"]>0) 
				{
					$order_id = $row_m_order["combined_with"];
					$combined_with = $order_id;
				}
			}
		}


		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			update_PaymentStatus ($order_id, $PN_data["txn_id"], $PN_data["payment_status"], $paymentdate);	
		}

		//BUCHUNG FÜR USER_DEPOSIT in EUR
		if ($user_id != 0 && $exchange_rate != 0)
		{
			$accounting = $PN_data["mc_gross"]*(1/$exchange_rate);
		}

		//BUCHUNG FÜR USER_DEPOSIT in EUR
		if ($exchange_rate != 0)
		{
			$accounting = $PN_data["mc_gross"]*(1/$exchange_rate);
		}
		else
		{
			$accounting = 0;
		}
		
		//Reversed IST  BUCHUNGSWIRKSAM
		$user_deposit = getLastUserDeposit($user_id);
		$user_deposit+= $accounting;

		$processed=true;

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
		$insert_data["reason"]=$payment_status;
		$insert_data["reason_detail"]=$pending_reason;
		$insert_data["orderTransactionID"]=$orderTransactionID;
		$insert_data["order_id"]=$order_id;
		$insert_data["total"]=$PN_data["mc_gross"]*1;
		$insert_data["fee"]=$PN_data["mc_fee"]*1;
		$insert_data["currency"]=$currency;
		$insert_data["exchange_rate_from_EUR"]=$exchange_rate;
		$insert_data["accounting_EUR"]=$accounting;
		$insert_data["deposit_EUR"]=$accounting;
		$insert_data["user_id"]=$user_id;
		$insert_data["user_deposit_EUR"]=$user_deposit;
		$insert_data["payment_type_id"]=4;
		$insert_data["buyer_lastname"]=$order["buyer_lastname"];
		$insert_data["buyer_firstname"]=$order["buyer_firstname"];
		$insert_data["payment_mail"]=$PN_data["payer_email"];
		$insert_data["payer_id"]=$PN_data["payer_id"];
		$insert_data["receiver_mail"]=$PN_data["receiver_email"];
		$insert_data["receiver_id"]=$PN_data["receiver_id"];
		$insert_data["paymentTransactionID"]=$PN_data["txn_id"];
		$insert_data["parentPaymentTransactionID"]=$PN_data["parent_txn_id"];
		$insert_data["payment_note"]=$PN_data["memo"];

		$res_insert = q_insert(PN_Table, $insert_data, $dbshop, __FILE__, __LINE__);

		$id_PN = mysqli_insert_id($dbshop);
		
		//SPEICHER PAYPALBUYERNOTE
		if ($PN_data["memo"]!="" && $order_id!=0)
		{
			mail("nputzing@mapco.de", "PayPal_BuyerNote PAYMENTNotification SET", "OrderID ".$order_id." BuyerNote ".$PN_data["memo"]);

			q("UPDATE shop_orders SET PayPal_BuyerNote = '".mysqli_real_escape_string($dbshop, $PN_data["memo"])."' WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
		}
	}
	
	if (!$processed)
	{
		
		//KEINE BEARBEITUNG
		show_error(9809, 9, __FILE__, __LINE__, "PaymentMessageID: ".$_POST["id"]);
		
		exit;
	}
	else
	{
		
		$postfields=array();
		if ($PN_data["payment_status"]=="Completed")
		{
			$postfields["mode"]="Payment";
			$postfields["orderid"]=$order_id;
			$postfields["TransactionID"]=$PN_data["txn_id"];
		}
		if ($PN_data["payment_status"]=="Refunded")
		{
			$postfields["mode"]="Refund";
		//	$postfields["orderid"]=$order_id;
			$postfields["TransactionID"]=$PN_data["txn_id"];
		}
		if ($PN_data["payment_status"]=="Reversed")
		{
			$postfields["mode"]="Refund";
			$postfields["orderid"]=$order_id;
			$postfields["TransactionID"]=$PN_data["txn_id"];
		}
		if ($PN_data["payment_status"]=="Canceled_Reversal")
		{
			$postfields["mode"]="Payment";
			$postfields["orderid"]=$order_id;
			$postfields["TransactionID"]=$PN_data["txn_id"];
		}

//AUFRUF PAYMENTNOTIFICATIONHANDLER ZUM BUCHEN DER ZAHLUNG

		//WENN ORDER BEKANNT IST
		if (($order_id!=0 && isset($postfields["mode"])) || (isset($postfields["mode"]) || $postfields["mode"]=="Refund"))
		{
			$postfields["API"]="payments";
			$postfields["APIRequest"]="PaymentNotificationHandler";
		//	$responseXML=post(PATH."soa2/", $postfields);
			$response = soa2($postfields);
			if ($response->Ack[0]=="Success")
			{
			}
			else
			{
				//show_error();
			}
		}
			
	}



//SERVICE RESPONSE
	
	echo '<id_PN>'.$id_PN.'</id_PN>'."\n";

?>