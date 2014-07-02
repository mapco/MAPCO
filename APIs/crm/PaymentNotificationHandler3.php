<?php

/*
->Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
->Completed: The payment has been completed, and the funds have been added successfully to your account balance.
->Created: A German ELV payment is made using Express Checkout.
->Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
Expired: This authorization has expired and cannot be captured.
Failed: The payment has failed. This happens only if the payment was made from your customer’s bank account.
->Pending: The payment is pending. See pending_reason for more information.
->Refunded: You refunded the payment.
->Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
Processed: A payment has been accepted.
Voided: This authorization has been voided.
*/

	$res = q("SELECT * FROM payment_notification_messages3 WHERE id = ".$_POST["id"], $dbshop, __FILE__, __LINE__);
	$message_id = $_POST["id"];
	
	$row = mysqli_fetch_assoc($res);
	
	$msg = $row["message"];
	
	$text = array();
	
	$text = explode("&", $msg);
	
	for ($i=1; $i<sizeof($text); $i++)
	{
		$zeile = explode("=", $text[$i]);
		if ($zeile[0]!="")
		{
			$_POST[$zeile[0]]=$zeile[1];	
		}
	}

//CHECK FOR ALREADY EXISTING IPN
	$res_check = q("SELECT * FROM payment_notification_messages4 WHERE ipn_track_id = '".$_POST["ipn_track_id"]." AND payment_type_id = 4'", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_check)>0)
	{
		echo "DUPLICATE ENTRY FOR ".$_POST["ipn_track_id"];
		exit;
	}


//WRITE MESSAGE
	q("INSERT INTO payment_notification_messages4 (message, date_received, processed, checked, payment_type_id, ipn_track_id) VALUES ('".mysqli_real_escape_string($dbshop, $row["message"])."', ".$row["date_recieved"].", 0, '', 4, '".mysqli_real_escape_string($dbshop, $_POST["ipn_track_id"])."')", $dbshop, __FILE__, __LINE__);
$message_id = mysqli_insert_id($dbshop);

//************************************************

//extract($_POST);
//print_r($_POST);

//FELDER VORBEREITEN
$payment_date=urldecode($_POST["payment_date"]);

$paymentdate=strtotime($payment_date);

$payer_email=urldecode($_POST["payer_email"]);
$receiver_email=urldecode($_POST["receiver_email"]);

$processed=false;

$accounting = 0;

if (!isset($_POST["PayID"]))
{

/**********************************************************************
C O M P L E T E D 
**********************************************************************/
// - BUCHUNGSWIRKSAM

	if ($_POST["payment_status"]=="Completed")
	{
		//FOR EBAY TRANSACTION
	echo "ZAHLUNG COMPLETED";	
		if (isset($_POST["for_auction"]) && ($_POST["for_auction"]=="true" || $_POST["for_auction"]=="TRUE"))
		{
	echo " ZAHLUNG FÜR EBAY ";
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_item=q("SELECT * FROM shop_orders_items WHERE foreign_transactionID = '".$_POST["ebay_txn_id1"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				//KEIN ITEM GEFUNDEN	
			}
			if (mysqli_num_rows($res_item)>1)
			{
				//KEIN EINDEUTIGES ITEM
				show_error();	
			}
			
			if (mysqli_num_rows($res_item)==1)
			{
				//Search for ORDER
				$item=mysqli_fetch_assoc($res_item);
				
				//CHECK CURRENCY
				if ($_POST["mc_currency"]!=$item["Currency_Code"])
				{
					//CURRENCIES STIMMEN NICHT ÜBEREIN
					show_error();
					exit;
					$currency = "";
					$exchange_rate = 0;
				}
				else
				{
					$currency = $_POST["mc_currency"];
					$exchange_rate = $item["exchange_rate_to_EUR"];
				}
							
				$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$item["order_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order)==0)
				{
					//KEINE ORDER ZUM ITEM GEFUNDEN
					show_error();
				}
				if (mysqli_num_rows($res_order)>1)
				{
					//KEINE eindeutige ORDER ZUM ITEM GEFUNDEN
					show_error();
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
				$currency = "";
				$exchange_rate = 0;
	
				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;			
			}
			
			$orderTransactionID=$_POST["ebay_txn_id1"];

			$payment_status = $_POST["payment_status"];
			$pending_reason = $_POST["pending_reason"];

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		//elseif ($_POST["txn_type"]=="express_checkout")
		else
		{
		//ONLINE-SHOP PAYMENTS

echo " ZAHLUNG FÜR ONLINE-SHOP ";
			$res_order = q("SELECT * FROM shop_orders WHERE Payments_TransactionID = '".$_POST["txn_id"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_order)==0)
			{
				//KEINE ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				//show_error();
			}
			if (mysqli_num_rows($res_order)>1)
			{
				//KEINE eindeutige ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error();
			}
			if (mysqli_num_rows($res_order)==1)
			{
				$order=mysqli_fetch_assoc($res_order);
				$shop_id = $order["shop_id"];
				$order_id = $order["id_order"];
				$user_id = $order["customer_id"];
				
				//GET ORDERS_ITEMS
				$res_order_items = q("SELECT * FROM shop_orders_items WHERE order_id = ".$order["id_order"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order_items)==0)
				{ 
					//KEIN ITEM ZUR ORDER GEFUNDEN
					show_error();
				}
				else
				{
					$item = mysqli_fetch_assoc($res_order_items);
					
					//CHECK CURRENCY
					if ($_POST["mc_currency"]!=$item["Currency_Code"])
					{
						//CURRENCIES STIMMEN NICHT ÜBEREIN
						show_error();
						
						$currency = "";
						$exchange_rate = 0;
					}
					else
					{
						$currency = $_POST["mc_currency"];
						$exchange_rate = $item["exchange_rate_to_EUR"];
					}
				}
				
			}
			else
			{
				$currency = "";
				$exchange_rate = 0;

				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;
			}
			$orderTransactionID = $order_id;
			
			$payment_status = $_POST["payment_status"];
			$pending_reason = '';
		}
		
		
		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			echo "****".$order_id."*******";
			// "COMPLETED" CAN UPDATE [ CREATED||PENDING ] WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$_POST["txn_id"])
			{
				if ($order["Payments_TransactionState"] == "Created" || $order["Payments_TransactionState"] == "Pending")
				{
					//UPDATE SHOP_ORDERS
					echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
				}
				else
				{
					echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
					echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
				}
				else
				{
					echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}
		
		//BUCHUNG FÜR USER_DEPOSIT in EUR
		if ($user_id != 0 && $exchange_rate != 0)
		{
			$accounting = $_POST["mc_gross"]*(1/$exchange_rate);
		}
		
	$processed=true;
	}
		
/**********************************************************************
P E N D I N G
**********************************************************************/
		
	if ($_POST["payment_status"]=="Pending")
	{
		//FOR EBAY TRANSACTION
	echo "ZAHLUNG PENDING ";	
		if (isset($_POST["for_auction"]) && ($_POST["for_auction"]=="true" || $_POST["for_auction"]=="TRUE"))
		{
	echo " ZAHLUNG FÜR EBAY ";
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
			$res_item=q("SELECT * FROM shop_orders_items WHERE foreign_transactionID = '".$_POST["ebay_txn_id1"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				//KEIN ITEM GEFUNDEN	
			}
			if (mysqli_num_rows($res_item)>1)
			{
				//KEIN EINDEUTIGES ITEM
				show_error();	
			}
			
			if (mysqli_num_rows($res_item)==1)
			{
				//Search for ORDER
				$item=mysqli_fetch_assoc($res_item);
				
				//CHECK CURRENCY
				if ($_POST["mc_currency"]!=$item["Currency_Code"])
				{
					//CURRENCIES STIMMEN NICHT ÜBEREIN
					show_error();
					exit;
					$currency = "";
					$exchange_rate = 0;
				}
				else
				{
					$currency = $_POST["mc_currency"];
					$exchange_rate = $item["exchange_rate_to_EUR"];
				}

				$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$item["order_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order)==0)
				{
					//KEINE ORDER ZUM ITEM GEFUNDEN
					show_error();
				}
				if (mysqli_num_rows($res_order)>1)
				{
					//KEINE eindeutige ORDER ZUM ITEM GEFUNDEN
					show_error();
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
				$currency = "";
				$exchange_rate = 0;
	
				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;			
			}
			
			$orderTransactionID=$_POST["ebay_txn_id1"];
				
			$payment_status = $_POST["payment_status"];
			$pending_reason = $_POST["pending_reason"];

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		//elseif ($_POST["txn_type"]=="express_checkout")
		else
		{
		//ONLINE-SHOP PAYMENTS

echo " ZAHLUNG FÜR ONLINE-SHOP ";
			$res_order = q("SELECT * FROM shop_orders WHERE Payments_TransactionID = '".$_POST["txn_id"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_order)==0)
			{
				//KEINE ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error();
			}
			if (mysqli_num_rows($res_order)>1)
			{
				//KEINE eindeutige ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error();
			}
			if (mysqli_num_rows($res_order)==1)
			{
				$order=mysqli_fetch_assoc($res_order);
				$shop_id = $order["shop_id"];
				$order_id = $order["id_order"];
				$user_id = $order["customer_id"];

				//GET ORDERS_ITEMS
				$res_order_items = q("SELECT * FROM shop_orders_items WHERE order_id = ".$order["id_order"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order_items)==0)
				{ 
					//KEIN ITEM ZUR ORDER GEFUNDEN
					show_error();
				}
				else
				{
					$item = mysqli_fetch_assoc($res_order_items);
					
					//CHECK CURRENCY
					if ($_POST["mc_currency"]!=$item["Currency_Code"])
					{
						//CURRENCIES STIMMEN NICHT ÜBEREIN
						show_error();
						
						$currency = "";
						$exchange_rate = 0;
					}
					else
					{
						$currency = $_POST["mc_currency"];
						$exchange_rate = $item["exchange_rate_to_EUR"];
					}
				}
			}
			else
			{
				$currency = "";
				$exchange_rate = 0;

				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;
			}
			$orderTransactionID = $order_id;
			
			$payment_status = $_POST["payment_status"];
			$pending_reason = $_POST["pending_reason"];
		}
		
		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			echo "****".$order_id."*******";
			// "PENDING" CAN UPDATE [ CREATED ] WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$_POST["txn_id"])
			{
				if ($order["Payments_TransactionState"] == "Created")
				{
					//UPDATE SHOP_ORDERS
					echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Pending";
				}
				else
				{
					echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
					echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Pending";
				}
				else
				{
					echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}

	$processed=true;
	}

/**********************************************************************
C R E A T E D
**********************************************************************/
		
	if ($_POST["payment_status"]=="Created")
	{
		//FOR EBAY TRANSACTION
	echo "ZAHLUNG CRAETED ";	
		if (isset($_POST["for_auction"]) && ($_POST["for_auction"]=="true" || $_POST["for_auction"]=="TRUE"))
		{
	echo " ZAHLUNG FÜR EBAY ";
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_item=q("SELECT * FROM shop_orders_items WHERE foreign_transactionID = '".$_POST["ebay_txn_id1"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				//KEIN ITEM GEFUNDEN	
			}
			if (mysqli_num_rows($res_item)>1)
			{
				//KEIN EINDEUTIGES ITEM
				show_error();	
			}
			
			if (mysqli_num_rows($res_item)==1)
			{
				//Search for ORDER
				$item=mysqli_fetch_assoc($res_item);
				
				//CHECK CURRENCY
				if ($_POST["mc_currency"]!=$item["Currency_Code"])
				{
					//CURRENCIES STIMMEN NICHT ÜBEREIN
					show_error();
					exit;
					$currency = "";
					$exchange_rate = 0;
				}
				else
				{
					$currency = $_POST["mc_currency"];
					$exchange_rate = $item["exchange_rate_to_EUR"];
				}

				$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$item["order_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order)==0)
				{
					//KEINE ORDER ZUM ITEM GEFUNDEN
					show_error();
				}
				if (mysqli_num_rows($res_order)>1)
				{
					//KEINE eindeutige ORDER ZUM ITEM GEFUNDEN
					show_error();
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
				$currency = "";
				$exchange_rate = 0;
	
				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;			
			}
			
			$orderTransactionID=$_POST["ebay_txn_id1"];
				
			$payment_status = $_POST["payment_status"];
			$pending_reason = $_POST["pending_reason"];

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		//elseif ($_POST["txn_type"]=="express_checkout")
		else
		{
		//ONLINE-SHOP PAYMENTS

echo " ZAHLUNG FÜR ONLINE-SHOP ";
			$res_order = q("SELECT * FROM shop_orders WHERE Payments_TransactionID = '".$_POST["txn_id"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_order)==0)
			{
				//KEINE ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error();
			}
			if (mysqli_num_rows($res_order)>1)
			{
				//KEINE eindeutige ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error();
			}
			if (mysqli_num_rows($res_order)==1)
			{
				$order=mysqli_fetch_assoc($res_order);
				$shop_id = $order["shop_id"];
				$order_id = $order["id_order"];
				$user_id = $order["customer_id"];

				//GET ORDERS_ITEMS
				$res_order_items = q("SELECT * FROM shop_orders_items WHERE order_id = ".$order["id_order"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order_items)==0)
				{ 
					//KEIN ITEM ZUR ORDER GEFUNDEN
					show_error();
				}
				else
				{
					$item = mysqli_fetch_assoc($res_order_items);
					
					//CHECK CURRENCY
					if ($_POST["mc_currency"]!=$item["Currency_Code"])
					{
						//CURRENCIES STIMMEN NICHT ÜBEREIN
						show_error();
						
						$currency = "";
						$exchange_rate = 0;
					}
					else
					{
						$currency = $_POST["mc_currency"];
						$exchange_rate = $item["exchange_rate_to_EUR"];
					}
				}
			}
			else
			{
				$currency = "";
				$exchange_rate = 0;

				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;
			}
			$orderTransactionID = $order_id;
			
			$payment_status = $_POST["payment_status"];
			$pending_reason = $_POST["pending_reason"];
		}
		
		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			echo "****".$order_id."*******";
			// "CREATED" CAN ONLY BE SET IF THERE IS NO OTHER STATE WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$_POST["txn_id"])
			{
				if ($order["Payments_TransactionState"] == "")
				{
					//UPDATE SHOP_ORDERS
					echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Created";
				}
				else
				{
					echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
					echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
				}
				else
				{
					echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}

	$processed=true;
	}


/**********************************************************************
D E N I E D
**********************************************************************/
		
	if ($_POST["payment_status"]=="Denied")
	{
		//FOR EBAY TRANSACTION
	echo "ZAHLUNG DENIED ";	
		if (isset($_POST["for_auction"]) && ($_POST["for_auction"]=="true" || $_POST["for_auction"]=="TRUE"))
		{
	echo " ZAHLUNG FÜR EBAY ";
			//CHECK FOR SHOP_ORDERID
			//search for ebay_orderItem with OrderID
			$res_item=q("SELECT * FROM shop_orders_items WHERE foreign_transactionID = '".$_POST["ebay_txn_id1"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				//KEIN ITEM GEFUNDEN	
			}
			if (mysqli_num_rows($res_item)>1)
			{
				//KEIN EINDEUTIGES ITEM
				show_error();	
			}
			
			if (mysqli_num_rows($res_item)==1)
			{
				//Search for ORDER
				$item=mysqli_fetch_assoc($res_item);
				
				//CHECK CURRENCY
				//CHECK CURRENCY
				if ($_POST["mc_currency"]!=$item["Currency_Code"])
				{
					//CURRENCIES STIMMEN NICHT ÜBEREIN
					show_error();
					exit;
					$currency = "";
					$exchange_rate = 0;
				}
				else
				{
					$currency = $_POST["mc_currency"];
					$exchange_rate = $item["exchange_rate_to_EUR"];
				}

				$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$item["order_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order)==0)
				{
					//KEINE ORDER ZUM ITEM GEFUNDEN
					show_error();
				}
				if (mysqli_num_rows($res_order)>1)
				{
					//KEINE eindeutige ORDER ZUM ITEM GEFUNDEN
					show_error();
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
				$currency = "";
				$exchange_rate = 0;
	
				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;			
			}
			
			$orderTransactionID=$_POST["ebay_txn_id1"];
				
			$payment_status = $_POST["payment_status"];
			$pending_reason = "";

			//FLAG FOR Payment_notification_messages
			$processed=true;	
			
		}
		//elseif ($_POST["txn_type"]=="express_checkout")
		else
		{
		//ONLINE-SHOP PAYMENTS

echo " ZAHLUNG FÜR ONLINE-SHOP ";
			$res_order = q("SELECT * FROM shop_orders WHERE Payments_TransactionID = '".$_POST["txn_id"]."'", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_order)==0)
			{
				//KEINE ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error();
			}
			if (mysqli_num_rows($res_order)>1)
			{
				//KEINE eindeutige ORDER ZUR PAYMENTSTRANSACTIONID GEFUNDEN
				show_error();
			}
			if (mysqli_num_rows($res_order)==1)
			{
				$order=mysqli_fetch_assoc($res_order);
				$shop_id = $order["shop_id"];
				$order_id = $order["id_order"];
				$user_id = $order["customer_id"];

				//GET ORDERS_ITEMS
				$res_order_items = q("SELECT * FROM shop_orders_items WHERE order_id = ".$order["id_order"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_order_items)==0)
				{ 
					//KEIN ITEM ZUR ORDER GEFUNDEN
					show_error();
				}
				else
				{
					$item = mysqli_fetch_assoc($res_order_items);
					
					//CHECK CURRENCY
					if ($_POST["mc_currency"]!=$item["Currency_Code"])
					{
						//CURRENCIES STIMMEN NICHT ÜBEREIN
						show_error();
						
						$currency = "";
						$exchange_rate = 0;
					}
					else
					{
						$currency = $_POST["mc_currency"];
						$exchange_rate = $item["exchange_rate_to_EUR"];
					}
				}
			}
			else
			{
				$currency = "";
				$exchange_rate = 0;

				$shop_id = 0;
				$order_id = 0;
				$user_id = 0;
			}
			$orderTransactionID = $order_id;
			
			$payment_status = $_POST["payment_status"];
			$pending_reason = "";
		}

		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			echo "****".$order_id."*******";
			// "DENIED" CAN UPDATE [ CREATED || PENDING ] WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$_POST["txn_id"])
			{
				if ($order["Payments_TransactionState"] == "Created" || $order["Payments_TransactionState"] == "Pending")
				{
					//UPDATE SHOP_ORDERS
					echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Denied";
				}
				else
				{
					echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
					echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
				}
				else
				{
					echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}



	$processed=true;
	}



/**********************************************************************
R E F U N D E D
**********************************************************************/
// - BUCHUNGSWIRKSAM
	if ($_POST["payment_status"]=="Refunded")
	{
		echo "REFUNDED";
		//FIND PARENTTRANSACTION
		$res_ipn = q("SELECT * FROM payment_notifications4 WHERE paymentTransactionID = '".$_POST["parent_txn_id"]."' LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ipn)==0)
		{
			//KEINE ZUGEHÖRIGE IPN GEFUNDEN
			//show_error();
			echo "KEINE ZUGEHÖRIGE IPN GEFUNDEN";
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
		
		$payment_status = $_POST["payment_status"];
		$pending_reason = $_POST["reason_code"];


		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			echo "****".$order_id."*******";
			// "REFUNDED" CAN UPDATE [ CREATED || PENDING || COMPLETED || REVERSED || CANCELED_REVERSAL] WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$_POST["parent_txn_id"])
			{
				if ($order["Payments_TransactionState"] == "Created" || $order["Payments_TransactionState"] == "Pending" || $order["Payments_TransactionState"] == "Completed" || $order["Payments_TransactionState"] == "Reversed" || $order["Payments_TransactionState"] == "Canceled_Reversal")
				{
					//UPDATE SHOP_ORDERS + TXN_ID
					echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Refunded";
				}
				else
				{
					echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
					echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Refunded";
				}
				else
				{
					echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}

		//BUCHUNG FÜR USER_DEPOSIT in EUR
		if ($user_id != 0 && $exchange_rate != 0)
		{
			$accounting = $_POST["mc_gross"]*(1/$exchange_rate);
		}

		$processed=true;

	}


/**********************************************************************
R E V E R S E D	
**********************************************************************/
// - BUCHUNGSWIRKSAM
	if ($_POST["payment_status"]=="Reversed")
	{
		echo "REVERSED";
		//FIND PARENTTRANSACTION
		$res_ipn = q("SELECT * FROM payment_notifications4 WHERE paymentTransactionID = '".$_POST["parent_txn_id"]."' LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ipn)==0)
		{
			//KEINE ZUGEHÖRIGE IPN GEFUNDEN
			//show_error();
			echo "KEINE ZUGEHÖRIGE IPN GEFUNDEN";
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
		
		$payment_status = $_POST["payment_status"];
		$pending_reason = $_POST["reason_code"];

		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			echo "****".$order_id."*******";
			// "REVERSED" CAN UPDATE [ CREATED || PENDING || COMPLETED ] WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$_POST["parent_txn_id"])
			{
				if ($order["Payments_TransactionState"] == "Created" || $order["Payments_TransactionState"] == "Pending" || $order["Payments_TransactionState"] == "Completed")
				{
					//UPDATE SHOP_ORDERS + TXN_ID
					echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Reversed";
				}
				else
				{
					echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
					echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
				}
				else
				{
					echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}

		//BUCHUNG FÜR USER_DEPOSIT in EUR
		if ($user_id != 0 && $exchange_rate != 0)
		{
			$accounting = $_POST["mc_gross"]*(1/$exchange_rate);
		}

		$processed=true;

	}

/**********************************************************************
C A N C E L E D   R E V E R S A L
**********************************************************************/
// BUCHUNGSWIRKSAM		
	if ($_POST["payment_status"]=="Canceled_Reversal")
	{
		echo "CANCELED REVERSAL";
		//FIND PARENTTRANSACTION
		$res_ipn = q("SELECT * FROM payment_notifications4 WHERE paymentTransactionID = '".$_POST["parent_txn_id"]."' LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ipn)==0)
		{
			//KEINE ZUGEHÖRIGE IPN GEFUNDEN
			//show_error();
			echo "KEINE ZUGEHÖRIGE IPN GEFUNDEN";
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
		
		$payment_status = $_POST["payment_status"];
		$pending_reason = $_POST["reason_code"];


		//UPDATE SHOP_ORDER PAYMENTSTATUS
		if ($order_id!=0)
		{
			echo "****".$order_id."*******";
			// "CANCELED REVERSAL" CAN UPDATE [ CREATED || PENDING || COMPLETED || REVERSED] WITH SAME TRANSACTION_ID	
			if ($order["Payments_TransactionID"]==$_POST["parent_txn_id"])
			{
				if ($order["Payments_TransactionState"] == "Created" || $order["Payments_TransactionState"] == "Pending" || $order["Payments_TransactionState"] == "Completed" || $order["Payments_TransactionState"] == "Reversed")
				{
					//UPDATE SHOP_ORDERS + TXN_ID
					echo "UPDATE shop_orders (SAME TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Canceled_Reversal";
				}
				else
				{
					echo "NO UPDATE because of: ".$order["Payments_TransactionState"];
				}
			}
			else
			//UPDATE IN ALL CASES IF TRANSACTION_ID NOT EQUAL, ONLY IF PAYMENTDATE IS NEWER THAN ALREADY EXISTING PAYMENTTRANSACTION
			{
				if ($order["Payments_TransactionStateDate"]<$paymentdate)
				{	
					echo "UPDATE shop_orders (DIFFERENT TXN_ID) FROM ".$order["Payments_TransactionState"]." TO Completed";
				}
				else
				{
					echo "NO UPDATE (DIFFERENT TXN_ID) because Transaction is older";
				}
			}
		}

		//BUCHUNG FÜR USER_DEPOSIT in EUR
		if ($user_id != 0 && $exchange_rate != 0)
		{
			$accounting = $_POST["mc_gross"]*(1/$exchange_rate);
		}

		$processed=true;

	}
	

	if ($processed)
	{

		//SET USER_DEPOSIT
		//GET LAST 	USER_DEPOSIT
		if ($user_id!=0)
		{
			$res_deposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$user_id." ORDER BY id_PN LIMIT 1", $dbshop, __FILE__, __LINE__);
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
		}
		else
		{
			$user_deposit = 0;
		}
		
		//SET ORDER_DEPOSIT
		if ($order_id!=0)
		{
			//GET LAST ORDERDEPOSIT
			$res_order_deposit = q("SELECT * FROM payment_notifications4 WHERE state = 'Order' AND order_id = ".$order_id." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
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
			$order_deposit+=$accounting;
		}
		else
		{
			$order_deposit = 0;
		}

		
		//WRITE IPN
		$res=q("INSERT INTO payment_notifications4 (
			PNM_id,
			shop_id, 
			PN_date, 
			payment_date, 
			notification_type,
			state, 
			state_reason, 
			orderTransactionID, 
			order_id, 
			total,
			fee,
			Currency,
			exchange_rate_from_EUR,
			accounting_EUR,
			order_deposit_EUR,
			user_id,
			user_deposit_EUR,
			payment_type_id,
			transaction_type,
			buyer_lastname,
			buyer_firstname,
			payment_mail, 
			payer_id,
			receiver_mail,
			receiver_id,
			paymentTransactionID,
			parentPaymentTransactionID,
			payment_note
		) VALUES (
			".$message_id.",
			".$shop_id.",
			".time().",
			".$paymentdate.",
			1,
			'".mysqli_real_escape_string($dbshop, $payment_status)."', 
			'".mysqli_real_escape_string($dbshop, $pending_reason)."', 
			'".mysqli_real_escape_string($dbshop, $orderTransactionID)."', 
			".$order_id.",
			".($_POST["mc_gross"]*1).", 
			".($_POST["mc_fee"]*1).", 
			'".mysqli_real_escape_string($dbshop, $currency)."', 
			".$exchange_rate.", 
			".$accounting.",
			".$order_deposit.",
			".$user_id.", 
			".$user_deposit.",
			4,
			'".mysqli_real_escape_string($dbshop, $_POST["txn_type"])."', 
			'".mysqli_real_escape_string($dbshop, $_POST["last_name"])."', 
			'".mysqli_real_escape_string($dbshop, $_POST["first_name"])."', 
			'".mysqli_real_escape_string($dbshop, $payer_email)."',
			'".mysqli_real_escape_string($dbshop, $_POST["payer_id"])."',
			'".mysqli_real_escape_string($dbshop, $receiver_email)."',
			'".mysqli_real_escape_string($dbshop, $_POST["receiver_id"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["txn_id"])."', 
			'".mysqli_real_escape_string($dbshop, $_POST["parent_txn_id"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["memo"])."'
		)",$dbshop, __FILE__, __LINE__);

		if (mysqli_errno($dbshop)!=0) $processed=false;
	}
	
	if ($processed)
	{
		//IPN MESSAGE PROCESSED	
		q("UPDATE payment_notification_messages4 SET processed = 1 WHERE id = ".$message_id, $dbshop, __FILE__, __LINE__);
	}
}

?>