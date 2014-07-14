<?php
	include("config.php");
//	include("templates/".TEMPLATE_BACKEND."/header.php");
/*	
//	$res=q("SELECT * FROM payment_notification_messages order by id desc limit 3500, 3500;", $dbshop, __FILE__, __LINE__);

	$res=q("SELECT * FROM payment_notification_messages_alt WHERE date_recieved > 1380578400  AND imported =0", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		$field=array();
		$Varfield=array();
		$field=explode("&", $row["message"] );
		echo $row["id"]." ".sizeof($field)."<br />";
		$tmp=array();
		for ($i=0; $i<sizeof($field); $i++)
		{
			$tmp=array();
			$tmp=explode("=", $field[$i]);
			//echo $i.":      ".$tmp[0]."-".$tmp[1]."<br />";
			if (isset($tmp[0]) && isset($tmp[1]))
			{
				$Varfield[$tmp[0]]=$tmp[1];
			}

		}
			$Varfield["API"]="payments";
			$Varfield["Action"]="PaymentNotificationListener_PayPal2";
			 $Varfield["DATE_RECIEVED"]=$row["date_recieved"];
		//print_r($Varfield);
		echo $responseXML=post(PATH."/soa/", $Varfield)."<br />";
		q("UPDATE payment_notification_messages_alt SET imported = 1 WHERE id =".$row["id"], $dbshop, __FILE, __LINE);

	}
	
*/	
	

/*
	//PART FOR MoneyTranser

	$start=time();

	$counter=0;
	$res = q("SELECT * FROM payment_notifications2 WHERE payment_type ='2' AND payment_date > 1380578400 AND imported = 0", $dbshop, __FILE__, __LINE__);
	echo mysqli_num_rows($res)."<br />";
	while ( $counter<2500 && (time()-$start)<2000)
	//while ($row = mysqli_fetch_assoc($res))
	{
		
		if ($row = mysqli_fetch_assoc($res))
		{

			if ($row["total"]<0)
			{
				$mode = "BankTransfer_SendMoney";	
			}
			else
			{
				$mode = "BankTransfer";	
			}
		
			$postfield = array();
			$postfield["API"] = "payments";
			$postfield["APIRequest"] = "PaymentNotificationSet_Manual";
			$postfield["mode"] = $mode;
			$postfield["orderid"] = $row["shop_orderID"];
			$postfield["payment_total"] = $row["total"];
			$postfield["accounting_date"] = $row["payment_date"];
			$counter++;
			echo $counter."  ".post(PATH."soa2/", $postfield)."<br />";
			q("UPDATE payment_notifications2 SET imported = 1 WHERE id_PN = ".$row["id_PN"], $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$counter=1000000000;
		}
	}
	
*/	

/*
	$start=time();
	
	//GET LAST ORDERADD
	$res_check = q("SELECT * FROM payment_notifications WHERE notification_type = 2 ORDER BY order_id DESC LIMIT 1", $dbshop, __FILE__, LINE__);
	if (mysqli_num_rows($res_check)>0)
	{
		$check_row=mysqli_fetch_assoc($res_check);
		$order_id = $check_row["order_id"];
	}
	else
	{
		$order_id = 1759010;
	}
	 
	//PART IMPORT SHOP_ORDRES
	$counter=0;
	$res = q("SELECT * FROM shop_orders WHERE firstmod > 1380578400 AND id_order > ".$order_id." ORDER BY id_order", $dbshop, __FILE__, __LINE__);
	echo mysqli_num_rows($res);
	while ( $counter<5000 && (time()-$start)<180)
	//while($row = mysqli_fetch_assoc($res))
	{
		//$row = mysqli_fetch_assoc($res);
	//	echo $row["id_order"]."<br />";
		 $counter++;
	
	
		if ($row = mysqli_fetch_assoc($res))
		{
			//GET ORDER EVENT WHERE 
			$res_ev = q("SELECT * FROM shop_orders_events WHERE order_id = ".$row["id_order"]." AND eventtype_id = 1", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_ev)==0)
			{
				$event_id = 1;
				
			}
			else
			{
				$row_ev = mysqli_fetch_assoc($res_ev);
				$event_id = $row_ev["id_event"];
			}
			$postfield = array();
			$postfield["API"] = "payments";
			$postfield["APIRequest"] = "PaymentNotificationHandler";
			$postfield["mode"] = "OrderAdd";
			$postfield["orderid"] = $row["id_order"];
			$postfield["order_event_id"] = $event_id;
	
	
		//	$counter++;
			echo $counter."  ".$row["id_order"]." ".post(PATH."soa2/", $postfield)."<br />";
		}
		else
		{
			$counter=1000000000;
		}
	}
	
	$end=time();
	
	echo "LAUFZEIT: ".($end-$start);
	*/
	
	
	//PART FOR ACCOUNTING
	
	$start = time();
	$counter = 0;
	$res = q("SELECT * FROM payment_notification_messages WHERE processed = 0 AND processed2 = 0 AND payment_type_id = 4 ORDER BY id", $dbshop, __FILE__, __LINE__);
	echo mysqli_num_rows($res)."<br />";
	
	$postfield["API"]="payments";
	
	
	while ( $counter<1000 && (time()-$start)<70)
	{
		
		if ($row = mysqli_fetch_assoc($res))
		{
			$counter++;
			$OK = true;
			if ($row["payment_type_id"] == 4) // PAYPAL
			{
				$postfield["APIRequest"]="PaymentsNotificationSet_PayPal";
				$postfield["id"]=$row["id"];
			}
			if ($row["payment_type_id"] == 5) // PayGenic
			{
				$postfield["APIRequest"]="PaymentNotificationSet_Paygenic";
				$postfield["id"]=$row["id"];
			}
			if ($row["payment_type_id"] == 2) // Vorkasse
			{
				$postfield["APIRequest"]="PaymentNotificationSet_BankTransfer";
				$postfield["PNM_id"]=$row["id"];
			}
			$responseXML = post(PATH."soa2/", $postfield);
			
			echo $counter." ".$row["id"]." ".$responseXML."<br />";
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				//XML FEHLERHAFT
				echo $row["payment_type_id"]."XML FEHLERHAFT:".$responseXML."<br />";
				$OK = false;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			
			if ($response->Ack[0]!="Success")
			{
				echo $row["payment_type_id"].$responseXML;
				$OK = false;
			}
			
			if ($OK)
			{
				 q("UPDATE payment_notification_messages SET processed = 1 WHERE id = ".$row["id"], $dbshop, __FILE__, __LINE__);
				 echo $counter."<br />";
			}
			 q("UPDATE payment_notification_messages SET processed2 = 1 WHERE id = ".$row["id"], $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$counter = 1000000000;
		}
	}
	$end=time();
	
	echo "LAUFZEIT: ".($end-$start);

?>