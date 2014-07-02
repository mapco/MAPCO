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
	$required=array("SELECTOR_id_order" =>"numericNN");
	check_man_params($required);
	
	//CHECK IF ENTRY EXISTS
	$res_check=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["SELECTOR_id_order"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_check)==0)
	{
		//ORDER NICHT GEFUNDEN
		show_error(9774, 7, __FILE__, __LINE__, "order_id: ".$_POST["SELECTOR_id_order"]);
		exit;
	}
	$order = mysqli_fetch_assoc($res_check);
	/*
	$adr_id=0;
	//CHECK FOR RIGHT shipping_type 
	if (isset($_POST["shipping_type_id"]) && $_POST["shipping_type_id"]!=0)
	{
		//SHIPPINGTYPE KANN NUR IN VERBINDUNG MIT EINER ADRESSE GESETZT WERDEN
		if ( (!isset($_POST["ship_adr_id"]) || $_POST["ship_adr_id"]==0) && (!isset($_POST["bill_adr_id"]) || $_POST["bill_adr_id"]==0) && ($order["ship_adr_id"]==0 && $order["bill_adr_id"]==0) )
		{
			//KEINE ADRESSE GESETZT
			//show_error(9789, 7, __FILE__, __LINE__);
			//exit;	
			$_POST["shipping_type_id"] = 0;
		}
		
		//GET ADDRESS
		if ((isset($_POST["ship_adr_id"]) || isset($_POST["bill_adr_id"])) && $_POST["shipping_type_id"] != 0)
		{
			if ($_POST["ship_adr_id"]!=0) $adr_id = $_POST["ship_adr_id"]; else $adr_id = $_POST["bill_adr_id"];
		}
		elseif ($_POST["shipping_type_id"] != 0)
		{
			if ($order["ship_adr_id"]!=0) $adr_id = $order["ship_adr_id"]; else $adr_id = $order["bill_adr_id"];
		}
		
		if ($adr_id !=0)
		{
			$res_address = q("SELECT * FROM shop_bill_adr WHERE adr_id = ".$adr_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_address)==0)
			{
				//KEINE ADRESSE ZUR ADRESS_ID GEFUNDEN
				show_error(9790, 7, __FILE__, __LINE__, "ADRESS_ID: ".$adr_id);
				exit;
			}
			$address = mysqli_fetch_assoc($res_address);
		}
		
		if ($_POST["shipping_type_id"] != 0)
		{
			//GET SHIPPINGTYPE DATA
			$res_shipping = q("SELECT * FROM shop_shipping_types WHERE id_shippingtype = ".$_POST["shipping_type_id"], $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_shipping)==0)
			{
				//KEINEN SHIPPINGTYPE GEFUNDEN
				show_error(9791, 7, __FILE__, __LINE__, "SHIPPINGTYPE_ID: ".$_POST["shipping_type_id"]);
				exit;
			}
			$shippingtype=mysqli_fetch_assoc($res_shipping);
		}
		
		if (isset($address) && isset($shippingtype))
		{
			//PRÜFE OB ZIELLAND MIT SHIPPINGTYPE ÜBEREINSTIMMT
			if ( ($address["country_id"]==1 && $shippingtype["international"]!=0) || ($shippingtype["international"]==1 && $address["country_id"]==1) )
			{
				//ZIELLAND UND SHIPPINGTYPE GEHÖREN NICHT ZUSAMMEN
				show_error(9792, 7, __FILE__, __LINE__, "SHIPPINGTYPE_ID: ".$shippingtype["id_shippingtype"].", COUNTRY_ID: ".$address["country_id"]);
				exit;
			}
		}
	}
	*/

	//INSERT DATA
		//get TableStructure
	$data=array();
	$nvp="";
	$selector="";
	$res_struct=q("SHOW COLUMNS FROM shop_orders;", $dbshop, __FILE__, __LINE__);
	while($struct=mysqli_fetch_assoc($res_struct))
	{
		if ($struct["Extra"]!="auto_increment")
		{
			if (isset($_POST[$struct["Field"]]))
			{
				//DATA FOR ORDEREVENT
				$data[$struct["Field"]]=$_POST[$struct["Field"]];
				
				if ($nvp!="") $nvp.=", ";
				$nvp.=$struct["Field"]." = '".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				
			}
			elseif(isset($_POST["SELECTOR_".$struct["Field"]]))
			{
				//DATA FOR ORDEREVENT
				$data["SELECTOR_".$struct["Field"]]=$_POST["SELECTOR_".$struct["Field"]];
				
				if ($selector!="") $selector.= " AND ";
				$selector.=$struct["Field"]." = '".$_POST["SELECTOR_".$struct["Field"]]."'";
			}
		}
		elseif(isset($_POST["SELECTOR_".$struct["Field"]]))
		{
			//DATA FOR ORDEREVENT
			$data["SELECTOR_".$struct["Field"]]=$_POST["SELECTOR_".$struct["Field"]];

			if ($selector!="") $selector.= " AND ";
			$selector.=$struct["Field"]." = '".$_POST["SELECTOR_".$struct["Field"]]."'";
		}
	
	}

	//CHECK IF THERE IS ANYTHING TO UPDATE
	if ($nvp!="" && $selector!="")
	{
		$sql="UPDATE shop_orders SET ".$nvp." WHERE ".$selector.";";

		$res_update=q($sql,$dbshop, __FILE__, __LINE__);
		$affected_rows=mysqli_affected_rows($dbshop);

		//SET ORDEREVENT
		$id_event=save_order_event(3, $_POST["SELECTOR_id_order"], $data);


		//SERVICE RESPONSE		
		echo '	<affected_rows>'.$affected_rows.'</affected_rows>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
		
		if(!isset($_POST["mode"]) || (isset($_POST["mode"]) && $_POST["mode"]!="ebay"))
		{
/*
			//GET "MOTHER" ORDER
			$res_m_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["SELECTOR_id_order"], $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)==0)
			{
				//show_error();
			}
			$row_m_order = mysqli_fetch_assoc($res_m_order);
			if ($row_m_order["combined_with"]>0) 
			{
				$order_id = $row_m_order["combined_with"];
			}
			else
			{
				$order_id = $_POST["SELECTOR_id_order"];
			}
*/
			//CALL PAYMENTNOTIFICATIONHANDLER
			$postfields["API"]="payments";
			$postfields["APIRequest"]="PaymentNotificationHandler";
			$postfields["mode"]="OrderAdjustment";
			$postfields["orderid"]=$_POST["SELECTOR_id_order"];
			$postfields["order_event_id"]=$id_event;
			
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

	}
	else
	{
		//SERVICE RESPONSE		
		echo '	<affected_rows>0</affected_rows>'."\n";
		echo '	<id_event>0</id_event>'."\n";
	}

		
	
?>