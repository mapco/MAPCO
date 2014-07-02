<?php

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

	//CHECK FOR REQUIRED POST FIELDAS
	$required=array("orderid" =>"numericNN");
	check_man_params($required);
	
	
	$res_check=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["orderid"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_check)==0)
	{
		//ORDER NICHT GEFUNDEN
		show_error(9774, 7, __FILE__, __LINE__, "order_id: ".$_POST["orderid"]);
		exit;
	}
	//CHECK
	$order = mysqli_fetch_assoc($res_check);
	
	$orderids=array();
	//IF COMBINED -> GET ALL ORDERIDs
	if ($order["combined_with"]>0)
	{
		$res2 = q("SELECT * FROM shop_orders WHERE combined_with = ".$_POST["orderid"], $dbshop, __FILE__, __LINE__);
		while ($row2 = mysqli_fetch_assoc($res2))
		{
			$orderids[]=$row2["id_order"];	
		}
	}
	else
	{
		$orderids[]=$_POST["orderid"];
	}
	
	foreach ($orderids as $orderid)
	{
		q("UPDATE shop_orders SET status_id = 4, status_date = ".time()." WHERE id_order = ".$orderid, $dbshop, __FILE__, __LINE__);

		$event_id = save_order_event(28, $orderid, array("status_id" => 4));

		//PAYMENTNOTIFICATIONHANDLER
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationHandler";
		$postfields["mode"]="OrderAdjustment";
		$postfields["orderid"]=$orderid;
		$postfields["order_event_id"]=$event_id;
		
		$responseXML=post(PATH."soa2/", $postfields);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			//XML FEHLERHAFT
			//echo "XMLERROR".$responseXML;
			//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			//exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response->Ack[0]=="Success")
		{
		}
		else
		{
			//show_error();
		}


	}

?>