<?php
	$starttime = time()+microtime();
	//include("config.php");
	//include("templates/".TEMPLATE_BACKEND."/header.php");
	
	//*********************************************************************************************************************************
	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
		//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			if (!is_numeric($val)) $xml.='<![CDATA['.$val.']]>'; else $xml.=$val;
			$xml.='</'.$key.'>';
			
		}
		$xml.='</data>';
		
		//SAVE EVENT
		q("INSERT INTO shop_orders_events (
			order_id, 
			eventtype_id, 
			data, 
			firstmod, 
			firstmod_user
		) VALUES (
			".$order_id.",
			".$eventtype_id.",
			'".mysqli_real_escape_string($dbshop, $xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
		
	}

	
	
	function send_FINmail($order, $mode)
	{

		global $errorcounter;
		
		//SEND E-MAIL
		
		$OK1=true;
		$OK2=true;
/*		
		$responseXML=post(PATH."soa/", array("API" => "crm", "Action" => "send_fin_mail", "OrderID" => $order[0]["id_order"], "mode" => $mode) );
		$use_errors = libxml_use_internal_errors(true);

		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			$OK1=false;
			error__log(3,1,__FILE__, __LINE__, "Beim Abrufen der Serverantwort ist ein XML-Fehler aufgetreten.");
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if( $response->Ack[0]=="Failure")
		{
			$OK1=false;
			error__log(3,2,__FILE__, __LINE__, "E-Mail wurde nicht versendet. Fehlermeldung: ".$responseXML."ORDER ID: ".$order[0]["id_order"]);
		}
		if (!$OK1) $errorcounter++;
*/		
		
		
		//neu
		$responseXML=post(PATH."soa2/", array("API" => "crm", "APIRequest" => "MailFinSend", "OrderID" => $order[0]["id_order"], "mode" => $mode) );
		$use_errors = libxml_use_internal_errors(true);

		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			$OK1=false;
			error__log(3,1,__FILE__, __LINE__, "Beim Abrufen der Serverantwort ist ein XML-Fehler aufgetreten.");
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if( $response->Ack[0]=="Failure")
		{
			$OK1=false;
			error__log(3,2,__FILE__, __LINE__, "E-Mail wurde nicht versendet. Fehlermeldung: ".$responseXML."ORDER ID: ".$order[0]["id_order"]);
		}
		if (!$OK1) $errorcounter++;
	
	
	
		//SEND EBAY MESSAGE

		$responseXML=post(PATH."soa/", array("API" => "crm", "Action" => "send_fin_eBayMessage", "OrderID" => $order[0]["id_order"], "mode" => $mode) );

		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			$OK2=false;
			error__log(3,3,__FILE__, __LINE__, "Beim Abrufen der Serverantwort ist ein XML-Fehler aufgetreten.");
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if( $response->Ack[0]=="Failure")
		{
			$OK2=false;
			error__log(3,4,__FILE__, __LINE__, "Ebay-Nachricht wurde nicht versendet. Fehlermeldung: ".$responseXML."ORDER ID: ".$order[0]["id_order"]);
		}
		if (!$OK2) $errorcounter++;
		
	
		//UPDATE MAIL COUNTER
		if ($OK1 || $OK2)
		{
			global $dbshop;
		//	q_update("shop_orders6", array("fz_fin_mail_count" => $order["fz_fin_mail_count"]+1, "fz_fin_mail_lastsent" => time()), "WHERE id_order = '".$order["id_order"]."'", $dbshop, __FILE__, __LINE__);
			if ($order[0]["combined_with"]<=0)
			{
				q("UPDATE shop_orders SET fz_fin_mail_count = ".($order[0]["fz_fin_mail_count"]+1).", fz_fin_mail_lastsent = ".time()." WHERE id_order = '".$order[0]["id_order"]."';", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				q("UPDATE shop_orders SET fz_fin_mail_count = ".($order[0]["fz_fin_mail_count"]+1).", fz_fin_mail_lastsent = ".time()." WHERE combined_with = '".$order[0]["combined_with"]."';", $dbshop, __FILE__, __LINE__);
			}
			
			$data=array();
			$data["fz_fin_mail_count"]=$order[0]["fz_fin_mail_count"]+1;
			$data["fz_fin_mail_lastsent"]=time();
			
			//SET ORDEREVENT
			$id_event= save_order_event(16, $order[0]["id_order"], $data);
			
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	
	//*********************************************************************************************************************************
	
//	$holydays = array("03.10.2013", "25.12.2013", "26.12.2013", "31.10.2013", "01.01.2014");
	$holydays = array("29.05.2014", "09.06.2014", "25.12.2014", "26.12.2014", "31.10.2014", "01.01.2015");
	
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
	
	if (!isset($_POST["max_mails"])) $_POST["max_mails"]=100;
	
	//GET ORDERS ONLY FROM LAST WEEK
	$orders_from = time()-(7*24*3600);

	//GET ORDERS FOR SHOP ID 3 & 4 (STATUS 1 & 7)
	$res_orders=q("SELECT * FROM shop_orders WHERE (shop_id = 3 OR shop_id = 4 ) AND (status_id = 1 OR status_id = 7) AND NOT usermail = '' AND ordertype_id = 1 AND fz_fin_mail_count <2 AND firstmod > ".$orders_from." ORDER BY fz_fin_mail_count", $dbshop, __FILE__, __LINE__);
	//$res_orders=q("SELECT * FROM shop_orders WHERE (shop_id = 3 OR shop_id = 4 ) AND (status_id = 1 OR status_id = 7) AND id_order = 1724622;", $dbshop, __FILE__, __LINE__);
//	echo "QUERY: SELECT * FROM shop_orders WHERE (shop_id = 3 OR shop_id = 4 ) AND (status_id = 1 OR status_id = 7) AND NOT usermail = '' AND fz_fin_mail_count <3 AND firstmod > ".$orders_from." ORDER BY fz_fin_mail_count";
	echo mysqli_num_rows($res_orders)."<br />";
	
	while ($order[0]=mysqli_fetch_array($res_orders))
	{
		$send_mail=true;
		$exit=false;

		$stoptime = time()+microtime();
		if ($stoptime-$starttime>60) $exit=true;

		if ($order[0]["combined_with"]>0 && $order[0]["combined_with"]!=$order[0]["id_order"]) $exit=true;

		if (!$exit)
		{
			//get OrderItems
			$orderItems=array();

		
			//GET ORDER ITEMSdata
			$in_orders='';
			for ($j=0; $j<sizeof($order); $j++)
			{
				if ($in_orders=='') $in_orders.=$order[$j]["id_order"]; else $in_orders.=", ".$order[$j]["id_order"];
			}
		
			$res_order_items=q("SELECT * FROM shop_orders_items WHERE order_id IN (".$in_orders.");", $dbshop, __FILE__, __LINE__);
			$orderpositioncount=mysqli_num_rows($res_order_items);

			if ($orderpositioncount==0)
			{
				$exit=true;
			}
			else
			{

				$customerVehicleCount=0;
				while ($row_orderItems=mysqli_fetch_array($res_order_items))
				{
					//CHECK OB mindestens ein OrderItem.checkes != 0
					$orderItems[$row_orderItems["id"]]=$row_orderItems;
					if ($row_orderItems["checked"]!=0) $send_mail=false;
					
					if ($row_orderItems["customer_vehicle_id"]!=0) $customerVehicleCount++;
					
				}
				//CHECK OB ALLEN ORDERPOSITIONS ein Fz zugeordnet wurde
				if ($customerVehicleCount==$orderpositioncount) $send_mail=false;
			}
		}
		
		//MAIL SENDEN
		
		if ($_POST["max_mails"]<$firstmails+$secondmails+$thirdmails) $send_mail=false;
		
		if ($send_mail && !$exit)
		{
			//ERSTE ANFRAGEMAIL versenden
			if ($order[0]["fz_fin_mail_count"]==0) 
			{
				//echo "<b>Firstmail</b> ".$order[0]["id_order"]."<br>";
				if (send_FINmail($order, "first")) $firstmails++;
			}
			/*
			if ($order[0]["fz_fin_mail_count"]==1) 
			{
				if (check_send_mail() && (time()>=$order[0]["fz_fin_mail_lastsent"]+24*3600))
				{
					//echo "<b>Second Mail</b> ".$order[0]["id_order"]."<br>";
					if (send_FINmail($order, "second")) $secondmails++;
				}
				else 
				{
					//echo "keine 2te mail ".$order[0]["id_order"]."<br>";
				}
			}
			*/
			//if ($order[0]["fz_fin_mail_count"]==2 && $order[0]["Payments_TransactionStateDate"]!=0)
			if ($order[0]["fz_fin_mail_count"]==1 && $order[0]["Payments_TransactionStateDate"]!=0)
			{
				if (check_send_mail() && (time()>=$order[0]["fz_fin_mail_lastsent"]+24*3600))
				{
					//echo "<b>Shipment</b> ".$order[0]["id_order"]."<br>";
					if (send_FINmail($order, "third")) $thirdmails++;
				}
				else 
				{
			//		echo "keine 3te mail ".$order[0]["id_order"]."<br>";
				}
			}
				 
		}
		else
		{
			// echo "+".$order[0]["fz_fin_mail_count"]."KEINE MAIL ".$order[0]["id_order"]."<br>";
		}
		

		
//		echo $order["id_order"]."<br />";
		
	}
	
	echo "Anzahl der Fehler: ".$errorcounter."\n";
	echo "Anzahl gesendeter first-Mails: ".$firstmails."\n";
	echo "Anzahl gesendeter second-Mails: ".$secondmails."\n";
	echo "Anzahl gesendeter third-Mails: ".$thirdmails."\n";
	



?>