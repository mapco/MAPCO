<?php

// SOA2 SERVICE 
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

	function getLastOrderTotal($orderid)
	{
		global $dbshop;

		$res_ordertotal = q("SELECT * FROM ".PN_Table." WHERE notification_type = 2 AND order_id = ".$orderid." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ordertotal)==0)
		{
			return false;
		}
		$PN_ordertotal = mysqli_fetch_assoc($res_ordertotal);
		
		return $PN_ordertotal["total"];
	}

	function getLastOrderTotalID($orderid)
	{
		global $dbshop;

		$res_ordertotal = q("SELECT * FROM ".PN_Table." WHERE notification_type = 2 AND order_id = ".$orderid." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ordertotal)==0)
		{
			return false;
		}
		$PN_ordertotal = mysqli_fetch_assoc($res_ordertotal);
		
		return $PN_ordertotal["id_PN"];
	}

	function getLastOrderDeposit($orderid)
	{
		global $dbshop;

		$res_orderdeposit = q("SELECT * FROM ".PN_Table." WHERE NOT notification_type = 2 AND order_id = ".$orderid." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_orderdeposit)==0)
		{
			return false;
		}
		
		$PN_orderdeposit = mysqli_fetch_assoc($res_orderdeposit);	
		
		return $PN_orderdeposit["deposit_EUR"];
	}
	
	function getLastOrderExchangerate($orderid)
	{
		global $dbshop;

		$res_orderdeposit = q("SELECT * FROM ".PN_Table." WHERE NOT notification_type = 2 AND order_id = ".$orderid." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_orderdeposit)==0)
		{
			return false;
		}
		
		$PN_orderdeposit = mysqli_fetch_assoc($res_orderdeposit);	
		
		return $PN_orderdeposit["exchange_rate_from_EUR"];
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


	function getLastPaymentTotal($transactionID)
	{
		global $dbshop;

		$res_paymentdeposit = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID ='".$transactionID."' AND notification_type = 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_paymentdeposit)==0)
		{
			//KEIN EINTRAG GEFUNDEN	
			//echo "Keine PaymentTransaction zur ID gefunden";
			//show_error()
			//exit;
			return 0;
		}
		
		$row_paymentdeposit = mysqli_fetch_assoc($res_paymentdeposit);
		
		return $row_paymentdeposit["total"];	
	}

	function getLastPaymentDeposit($transactionID)
	{
		global $dbshop;

		$res_paymentdeposit = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID ='".$transactionID."' ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_paymentdeposit)==0)
		{
			return false;
		}
		
		$PN_paymentdeposit = mysqli_fetch_assoc($res_paymentdeposit);	
		
		return $PN_paymentdeposit["deposit_EUR"];
	}
	
	function getLastPaymentDepositID($transactionID)
	{
		global $dbshop;

		$res_paymentdeposit = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID ='".$transactionID."' ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_paymentdeposit)==0)
		{
			return false;
		}
		
		$PN_paymentdeposit = mysqli_fetch_assoc($res_paymentdeposit);	
		
		return $PN_paymentdeposit["id_PN"];
	}

	function getLastPayment($transactionID)
	{
		global $dbshop;

		$res_paymentdeposit = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID ='".$transactionID."' ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_paymentdeposit)==0)
		{
			return false;
		}
		
		return $PN_paymentdeposit = mysqli_fetch_assoc($res_paymentdeposit);	
		
	}

//*********************************************************************************************************************************


	define("PN_Table", "payment_notifications4");
	
	$required=array("orderid" => "numericNN");
	
	check_man_params($required);

	$payments=array();

	//GET PAYMENTS FOR ORDER
	$res_payments = q("SELECT * FROM ".PN_Table." WHERE notification_type = 1 AND order_id = ".$_POST["orderid"], $dbshop, __FILE__, __LINE__);
	while ($row_payments = mysqli_fetch_assoc($res_payments))
	{
		$payments[$row_payments["paymentTransactionID"]] = $row_payments;
	}
	
	//GET SYSTEM ACCOUNTINGS FOR ORDER
	$res_payments = q("SELECT * FROM ".PN_Table." WHERE notification_type = 5 AND order_id = ".$_POST["orderid"], $dbshop, __FILE__, __LINE__);
	while ($row_payments = mysqli_fetch_assoc($res_payments))
	{
		if ($row_payments["paymentTransactionID"]!="")
		{
			//CHECK OB TRANSACTIONID BEREITS BEKANNT, ERSETZE EINTRAG WENN GEFUNDENER NEUER
			if (isset($payments[$row_payments["paymentTransactionID"]]) && $payments[$row_payments["paymentTransactionID"]]["payment_type_id"] == $row_payments["payment_type_id"])
			{
				$payments[$row_payments["paymentTransactionID"]] = $row_payments;
			}
			//KEIN EINTRAG ZUR TX_ID -> EINTRAGEN
			if (!isset($payments[$row_payments["paymentTransactionID"]]))
			{
				$payments[$row_payments["paymentTransactionID"]] = $row_payments;
			}
		}
		else
		{

			//GET ACCORDING ACCOUNTING
			$res_payments2 = q("SELECT * FROM ".PN_Table." WHERE id_PN = ".$row_payments["f_id"], $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_payments2)>0)	
			{
				$row_payments2 = mysqli_fetch_assoc($res_payments2);
				//CHECK OB TRANSACTIONID BEREITS BEKANNT, ERSETZE EINTRAG WENN GEFUNDENER NEUER
				if (isset($payments[$row_payments2["paymentTransactionID"]]) && $payments[$row_payments2["paymentTransactionID"]]["payment_type_id"] == $row_payments2["payment_type_id"])
				{
					$payments[$row_payments2["paymentTransactionID"]] = $row_payments2;
				}
				if (!isset($payments[$row_payments2["paymentTransactionID"]]))
				{
					$payments[$row_payments2["paymentTransactionID"]] = $row_payments2;
				}

			}
		}
	}
	
	//GET ORDERDATA
	$order = getOrderData($_POST["orderid"]);

	//ORDERINFO
	$orderinfo = array();
	$last_ordertotal = getLastOrderTotal($_POST["orderid"]);
	$last_exchangerate = getLastOrderExchangerate($_POST["orderid"]);
	$orderinfo["orderTotal"]=$last_ordertotal;
	$orderinfo["orderCurrency"]=$order["currency"];
	$orderinfo["orderTotalEUR"]=$last_ordertotal*(1/$last_exchangerate);
	$orderinfo["check_orderTotal"]=$order["ordertotal"];
	$orderinfo["check_orderTotalEUR"]=$order["ordertotalEUR"];
	
	$last_orderdeposit = getLastOrderDeposit($_POST["orderid"]);
	$orderinfo["orderDeposit"]=$last_orderdeposit;


	$last_userdeposit = getLastUserDeposit($order["user_id"]);
	$orderinfo["userdeposit"]=$last_userdeposit;
	
	$paymentinfo=array();
	//INFO TO PAYMENTS
	foreach ($payments as $transactionID => $payment)
	{

		$last_paymenttotal =  getLastPaymentTotal($transactionID);
		$paymentinfo[$transactionID]["paymentTotal"] = $last_paymenttotal;
		
		$last_paymentdeposit = getLastPaymentDeposit($transactionID);
		$paymentinfo[$transactionID]["paymentDeposit"] = $last_paymentdeposit;
	}
		
//	print_r($orderinfo);
//	print_r($paymentinfo);
	
	//SERVICE RESPONSE
	echo '<orderinfo>'."\n";
	while (list ($key, $val) = each ($orderinfo))
	{
		echo '	<'.$key.'>'.$val.'</'.$key.'>'."\n";
	}
	echo '</orderinfo>'."\n";
	foreach($paymentinfo as $TxID => $payment)
	{
		echo '<paymentinfo>'."\n";
		echo '	<TransactionID>'.$TxID.'</TransactionID>'."\n";
	//	echo '	<'.$key.'>'.$val.'</'.$key.'>'."\n";
		while (list ($key, $val) = each ($payment))
		{
			echo '	<'.$key.'>'.$val.'</'.$key.'>'."\n";
		}
		echo '</paymentinfo>'."\n";
	}

?>