<?php

	check_man_params(array("orderid" =>"numericNN"));		
	
	$orderid = $_POST["orderid"];
	
	$eBayTxnIDs = array();
	$TxnIDs = array();
	
	$dosomething = false;
	
	// CHECK IF THERE IS SOMETHING TO DO
	//GET ORDERPAYMENTS
	$postfield = array();
	$postfield['API']			= "payments";
	$postfield['APIRequest']	= "OrderPaymentsGet";
	$postfield['orderid']		= $orderid;

	$response = soa2($postfield, __FILE__, __LINE__);
	
	$i = 0;
	
	$paymentdeposit = array();
	$paymentdeposit_sum = 0;
	
	if ( (string)$response->Ack[0] == "Success" )
	{
		
		while ( isset( $response->paymentdata[0]->transaction[$i]->transactionID[0]) )
		{
			$paymentdeposit[(string)$response->paymentdata[0]->transaction[$i]->transactionID[0]] = (float)$response->paymentdata[0]->transaction[$i]->lastpaymentdeposit[0];
			$paymentdeposit_sum += (float)$response->paymentdata[0]->transaction[$i]->lastpaymentdeposit[0];
			
		
			$i++;

		}
		
		$orderdeposit=(float)$response->orderdata[0]->lastOrderDeposit[0];
		
		if ($orderdeposit + $paymentdeposit_sum == 0 && $orderdeposit != 0 && $paymentdeposit_sum != 0)
		{
			$dosomething = true;
		}
	}
	else
	{
		show_error(9797, 8, __FILE__, __LINE__, print_r($postfield, true));
		exit;
	}
			

	
	if ($dosomething)
	{
	
		//GET SHOPS
		$shops = array();
		$res_shops = q("SELECT * FROM shop_shops", $dbshop, __FILE__, __LINE__);
		while ( $row_shops = mysqli_fetch_assoc( $res_shops ) )
		{
			$shops[$row_shops["id_shop"]]=$row_shops;
		}
		
		// GET ORDER
		$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$orderid,  $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($res_order)==0 )
		{
			//show_error
			exit;			
		}
		$order = mysqli_fetch_assoc($res_order);
	
		//IF ORDER = EBAYORDER -> GET ITEMS FROM ORDER
		if ( $shops[$order["shop_id"]]["shop_type"] == 2 )
		{
			$eBayTxnIDs = array();
			$res_items = q("SELECT * FROM shop_orders_items WHERE order_id = ".$orderid, $dbshop, __FILE__, __LINE__);
			while ( $row_items = mysqli_fetch_assoc($res_items))
			{
				if ( $row_items["foreign_transactionID"] != "" )
			 	$eBayTxnIDs[] = $row_items["foreign_transactionID"];	
			//echo "<br />";
			}
		}
		
		// SEARCH FOR TxnIDs BY $eBayTxnIDs[]
		if ( sizeof($eBayTxnIDs) > 0)
		{
			$res_pn = q("SELECT * FROM payment_notifications WHERE notification_type IN (1,5) AND ( order_id = ".$orderid." OR orderTransactionID IN ('".implode("', '", $eBayTxnIDs)."') )", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$res_pn = q("SELECT * FROM payment_notifications WHERE notification_type IN (1,5) AND order_id = ".$orderid, $dbshop, __FILE__, __LINE__);	
		}
		
		while ( $row_pn = mysqli_fetch_assoc($res_pn))
		{
			if ( $row_pn["paymentTransactionID"] != "" && $row_pn["paymentTransactionID"] != "0")
			{
				$TxnIDs[$row_pn["paymentTransactionID"]] = 1;	
			}
		}
	
		foreach($TxnIDs as $TxnID => $data)
		{
			//echo $TxnID."<br />";
			
			//CHECK IF PAYMENT IS ALREADY LINKED TO ORDER
			$postfield = array();
			$postfield['API']			= "payments";
			$postfield['APIRequest']	= "PaymentGet";
			$postfield['TransactionID']	= $TxnID;
			
			$response = soa2($postfield, __FILE__, __LINE__);
			
			$linkedOrder = 0;
			
			if ( (string)$response->Ack[0] == "Success" )
			{
				$linkedOrder = (int)$response->LinkedToOrder[0];	
			}
			
			$OK = true;
			if ($linkedOrder != $orderid)
			{
				//echo " LINKING PAYMNENT";	
				$postfield = array();
				$postfield['API']			= "payments";
				$postfield['APIRequest']	= "PaymentNotificationHandler";
				$postfield['mode']			= "LinkingPayment";
				$postfield['orderid']		= $orderid;
				$postfield['TransactionID']	= $TxnID;
				
				$response = soa2($postfield, __FILE__, __LINE__);
	
				//PAYMENT
				if ( (string)$response->Ack[0] != "Success" )
				{
					$OK = false;
				}
	
			}
			
			if ($OK)
			{
				$postfield = array();
				$postfield['API']			= "payments";
				$postfield['APIRequest']	= "PaymentNotificationHandler";
				$postfield['mode']			= "Payment";
				$postfield['orderid']		= $orderid;
				$postfield['TransactionID']	= $TxnID;
				
				$response = soa2($postfield, __FILE__, __LINE__);
	
				//echo " ACCOUNTING PAYMENT";
			}
			
		}
	}



?>