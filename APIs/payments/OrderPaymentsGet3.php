<?php

	define("PN_Table", "payment_notifications4");
	define("SHOP_ORDER", "shop_orders");
	
	$required=array("orderid" => "numericNN");
	
	check_man_params($required);

	if (isset($_POST["mode"]) && $_POST["mode"] == "single")
	{
		$single=true;
	}
	else
	{
		$single=false;
	}
	
	$orderids = array();
	
	if (!$single)
	{
		$res_order=q("SELECT * FROM shop_orders WHERE id_order =".$_POST["orderid"], $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_order)!=1)
		{
			exit;
		}
		else
		{
			$order[0]=mysqli_fetch_assoc($res_order);	
		}
		
		if ($order[0]["combined_with"]>0)
		{
			$tmp_orderid=$order[0]["combined_with"];
			
			//GET ALL ORDERS OF COMBINATION; ORDER[0] ->"MOTHER"
			//$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
			$res_orders=q("SELECT * FROM shop_orders WHERE id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
			$order[0]=mysqli_fetch_array($res_orders);
			
			$orderids[]=$order[0]["id_order"];
			
			
			$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND NOT id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
			while ($row_orders=mysqli_fetch_array($res_orders))
			{
				$orderids[]=$row_orders["id_order"];
			}
		}
		else
		{
			$orderids[]=$_POST["orderid"];
		}
	}
	else
	{
		$orderids[]=$_POST["orderid"];
	}


	$TransactionIDs = array();
	$orderdata = array();
//	$lastOrderDepositEUR= false;
	$lastOrderDepositEURsum=0;
	$lastOrderDepositFCsum=0;
	$ordertotal=0;
	$ordertotalEUR=0;
	//SUCHE ALLE VORKOMMENDEN TxnIDs zur OrderID
		//ABSTEIGENDE SORTIERUNG IST WICHTIG ZUR BESTIMMUNG DES LAST_ORDER_DEPOSIT
	//$res_order_notifications = q("SELECT * FROM ".PN_Table." WHERE order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC", $dbshop, __FILE__, __LINE__);
	for ($i=0; $i<sizeof($orderids); $i++)
	{
		$lastOrderDepositEUR= false;

		$res_order_notifications = q("SELECT * FROM ".PN_Table." WHERE order_id = ".$orderids[$i]." ORDER BY id_PN DESC", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_order_notifications)==0)
		{
			//KEINE ORDERNOTIFICATIONS GEFUNDEN
			//show_error();
		//	exit;
		}
		else
		{
			while ($row_order_notifications = mysqli_fetch_assoc($res_order_notifications))
			{
				if ($row_order_notifications["notification_type"]==1)
				{
					$TransactionIDs[$row_order_notifications["paymentTransactionID"]]=$row_order_notifications;
				}
				if ($row_order_notifications["notification_type"]==5 && $row_order_notifications["reason"]=="Payment")
				{
					//GET According Notification Type 4
					$res_notification = q("SELECT * FROM ".PN_Table." WHERE id_PN = ".$row_order_notifications["f_id"], $dbshop, __FILE__, __LINE__);
					while ($row_notification = mysqli_fetch_assoc($res_notification))
					{
						if ($row_notification["paymentTransactionID"]!="")
						{
							$TransactionIDs[$row_notification["paymentTransactionID"]]=$row_order_notifications;
						}
					}
				}
				if ($row_order_notifications["notification_type"]==2)
				{
					$orderdata[$i][]=$row_order_notifications;
					
				}
				//define  LastOrderDeposit
				if (($row_order_notifications["notification_type"]==5 || $row_order_notifications["notification_type"]==2) && !$lastOrderDepositEUR)
				{
					$lastOrderDepositEUR = $row_order_notifications["deposit_EUR"];
					$lastOrderDepositFC = round($row_order_notifications["deposit_EUR"]*$row_order_notifications["exchange_rate_from_EUR"], 2);
					$lastOrderDepositEURsum+=$lastOrderDepositEUR;
					$lastOrderDepositFCsum+=$lastOrderDepositFC;
				}
			}
			$ordertotal+=$orderdata[$i][0]["total"];
			$ordertotalEUR+=$orderdata[$i][0]["total"]/$orderdata[$i][0]["exchange_rate_from_EUR"];
		}
	}

	//GET ALL DATA FOR TxnIDs
	$transactions=array();
	$lastpaymentdeposit=array();
	foreach ($TransactionIDs as $TransactionID => $TransactionsData)
	{
		$res_notifications = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID = '".$TransactionID."' ORDER BY id_PN DESC", $dbshop, __FILE__, __LINE__);
		while ($row_notifications = mysqli_fetch_assoc($res_notifications))
		{
			$transactions[$TransactionID][$row_notifications["id_PN"]]=$row_notifications;
			if (!isset($lastpaymentdeposit[$TransactionID])) $lastpaymentdeposit[$TransactionID]=$row_notifications["deposit_EUR"];
		}
	}
		
	//GET ALL according Data for Types 4
	$transactions_=$transactions;
	foreach ($transactions_ as $TransactionID => $TransactionsData)
	{
		foreach ($TransactionsData as $id_PN => $PN_Data)
		{
			if ($PN_Data["notification_type"]==4)
			{
				//GET according accounting
				$res_notification = q("SELECT * FROM ".PN_Table." WHERE f_id = ".$id_PN." AND notification_type IN (4,5)", $dbshop, __FILE__, __LINE__);
				while ($row_notification = mysqli_fetch_assoc($res_notification))
				{
					$transactions[$TransactionID][$row_notification["id_PN"]]=$row_notification;
				}
			}
		}
	}

	echo '<paymentdata>'."\n";
	foreach ($transactions as $transactionID => $transactionsData)
	{
		echo '<transaction>'."\n";
		echo '	<transactionID><![CDATA['.$transactionID.']]></transactionID>'."\n";
		echo '	<lastpaymentdeposit><![CDATA['.$lastpaymentdeposit[$transactionID].']]></lastpaymentdeposit>'."\n";
		foreach ($transactionsData as $id_PN => $PN_Data)
		{
			echo '<accounting>'."\n";
			while (list ($key, $val) = each ($PN_Data))
			{
				echo '	<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
			}
			echo '</accounting>'."\n";
		}
		echo '</transaction>'."\n";
	}
	echo '</paymentdata>'."\n";
	
	//ORDERDATA
	echo '<orderdata>'."\n";
	echo '<lastOrderDeposit>'.$lastOrderDepositFCsum.'</lastOrderDeposit>'."\n";
	echo '<lastOrderDepositEUR>'.$lastOrderDepositEURsum.'</lastOrderDepositEUR>'."\n";
	//echo '<lastOrderTotal>'.$orderdata[0]["total"].'</lastOrderTotal>'."\n";
	echo '<lastOrderTotal>'.$ordertotal.'</lastOrderTotal>'."\n";
	//echo '<lastOrderTotalEUR>'.round($orderdata[0]["total"]/$orderdata[0]["exchange_rate_from_EUR"],2).'</lastOrderTotalEUR>'."\n";
	echo '<lastOrderTotalEUR>'.round($ordertotalEUR,2).'</lastOrderTotalEUR>'."\n";
	for ($i=0; $i<sizeof($orderids); $i++)
	{
		foreach ($orderdata[$i] as $id_PN => $data)
		{
			echo '<accounting>'."\n";
			while (list ($key, $val) = each ($data))
			{
				echo '	<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
			}
			echo '</accounting>'."\n";
		}
	}
	echo '</orderdata>'."\n";

//print_r($transactions);
?>