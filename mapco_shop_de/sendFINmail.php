<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	//*********************************************************************************************************************************
	function send_FINmail($order, $mode)
	{
		global $errorcounter;
		
		//SEND E-MAIL
		$error1=false;
		$error2=false;
		
		$responseXML=post(PATH."soa/", array("API" => "crm", "Action" => "send_fin_mail_test", "OrderID" => $order["id_order"], "mode" => $mode) );
		$use_errors = libxml_use_internal_errors(true);

		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			$error1=true;
			error__log(3,1,__FILE__, __LINE__, "Beim Abrufen der Serverantwort ist ein XML-Fehler aufgetreten.");
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if( $response->Ack[0]=="Failure")
		{
			$error1=true;
			error__log(3,2,__FILE__, __LINE__, "E-Mail wurde nicht versendet. Fehlermeldung: ".$response);
		}
		if ($error1) $errorcounter++;
	
		//SEND EBAY MESSAGE

		$responseXML=post(PATH."soa/", array("API" => "crm", "Action" => "send_fin_eBayMessage_test", "OrderID" => $order["id_order"], "mode" => $mode) );

		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			$error2=true;
			error__log(3,3,__FILE__, __LINE__, "Beim Abrufen der Serverantwort ist ein XML-Fehler aufgetreten.");
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if( $response->Ack[0]=="Failure")
		{
			$error2=true;
			error__log(3,4,__FILE__, __LINE__, "Ebay-Nachricht wurde nicht versendet. Fehlermeldung: ".$response);
		}
		if ($error2) $errorcounter++;
		
		
		//UPDATE MAIL COUNTER
		if (($error1 && !$error2) || (!$error1 && $error2) || (!$error1 && !$error2))
		{
			global $dbshop;
		//	q_update("shop_orders6", array("fz_fin_mail_count" => $order["fz_fin_mail_count"]+1, "fz_fin_mail_lastsent" => time()), "WHERE id_order = '".$order["id_order"]."'", $dbshop, __FILE__, __LINE__);
			q("UPDATE shop_orders SET fz_fin_mail_count = ".($order["fz_fin_mail_count"]+1).", fz_fin_mail_lastsent = ".time()." WHERE id_order = '".$order["id_order"]."';", $dbshop, __FILE__, __LINE__);
			
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	
	//*********************************************************************************************************************************
	
	$holydays = array("03.10.2013", "25.12.2013", "26.12.2013");
	
	//CHECK OB MAILS GESENDET WERDEN SOLLEN - NUR WERKTAGS, KEINE FEIERTAGE 
	function check_send_mail()
	{
		global $holydays;
		$today=time();
		//FÜR TEST
		//$today=mktime(17,30,00,10,04,2013);
		if (date("N", $today)<6 && !in_array(date("d.m.Y", $today), $holydays))
		{
			//WENN MONTAG ODER NACH FEIERTAG
			if (date("N", $today)==1 || in_array( date("d.m.Y",$today-(24*3600)), $holydays))
			{
				//nach 17 UHR
				if (date("G", $today)>=17)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}
	
//************************************************************************************************************************
	
	$errorcounter=0;
	$firstmails=0;
	$secondmails=0;
	$thirdmails=0;
	
	if (!isset($_POST["max_mails"])) $_POST["max_mails"]=50;

	//GET ORDERS FOR SHOP ID 3 & 4 (STATUS 1 & 7)
	//$res_orders=q("SELECT * FROM shop_orders WHERE (shop_id = 3 OR shop_id = 4 ) AND (status_id = 1 OR status_id = 7);", $dbshop, __FILE__, __LINE__);
	$res_orders=q("SELECT * FROM shop_orders WHERE (shop_id = 3 OR shop_id = 4 ) AND (status_id = 1 OR status_id = 7) AND id_order = 1724622;", $dbshop, __FILE__, __LINE__);
	
	echo mysqli_num_rows($res_orders)."<br />";
	
	while ($order=mysqli_fetch_array($res_orders))
	{
		$send_mail=true;
		$exit=false;

		//get OrderItems
		$orderItems=array();
		$res_orderItems=q("SELECT * FROM shop_orders_items WHERE order_id = ".$order["id_order"].";", $dbshop, __FILE__, __LINE__);
		$orderpositioncount=mysqli_num_rows($res_orderItems);
		if ($orderpositioncount==0)
		{
			$exit=true;
		}
		else
		{
			$customerVehicleCount=0;
			while ($row_orderItems=mysqli_fetch_array($res_orderItems))
			{
				//CHECK OB mindestens ein OrderItem.checkes != 0
				$orderItems[$row_orderItems["id"]]=$row_orderItems;
				if ($row_orderItems["checked"]!=0) $send_mail=false;
				
				if ($row_orderItems["customer_vehicle_id"]!=0) $customerVehicleCount++;
				
			}
			//CHECK OB ALLEN ORDERPOSITIONS ein Fz zugeordnet wurde
			if ($customerVehicleCount==$orderpositioncount) $send_mail=false;

		}
		
		//MAIL SENDEN
		
		if ($_POST["max_mails"]<$firstmails+$secondmails+$thirdmails) $send_mail=false;
		
		if ($send_mail && !$exit)
		{
			//ERSTE ANFRAGEMAIL versenden
			if ($order["fz_fin_mail_count"]==0) 
			{
				echo "<b>Firstmail</b> ";
				
				if (send_FINmail($order, "first")) $firstmails++;
			}
			if ($order["fz_fin_mail_count"]==1) 
			{
				if (check_send_mail() && (time()>=$order["fz_fin_mail_lastsent"]+24*3600))
				{
					echo "<b>Second Mail</b> ";
					if (send_FINmail($order, "second")) $secondmails++;
				}
				else 
				{
					echo "keine 2te mail";
				}
			}
			if ($order["fz_fin_mail_count"]>1)
			{
				if (check_send_mail() && (time()>=$order["fz_fin_mail_lastsent"]+24*3600))
				{
					echo "<b>Shipment</b> ";
					if (send_FINmail($order, "third")) $thirdmails++;
				}
				else 
				{
					echo "keine 3te mail";
				}
			}
				 
		}
		else
		{
		//	echo "+".$order["fz_fin_mail_count"]."KEINE MAIL ";
		}
		

		
//		echo $order["id_order"]."<br />";
		
	}
	
	echo "Anzahl der Fehler: ".$errorcounter."\n";
	echo "Anzahl gesendeter first-Mails: ".$firstmails."\n";
	echo "Anzahl gesendeter second-Mails: ".$secondmails."\n";
	echo "Anzahl gesendeter third-Mails: ".$thirdmails."\n";
	


	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>