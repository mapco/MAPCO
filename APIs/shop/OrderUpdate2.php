<?php

	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
		//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			$xml.=' <old>';
				if (!is_numeric($val["old"])) $xml.='<![CDATA['.$val["old"].']]>'; else $xml.=$val["old"];
			$xml.=' </old>';
			$xml.=' <new>';
				if (!is_numeric($val["new"])) $xml.='<![CDATA['.$val["new"].']]>'; else $xml.=$val["new"];
			$xml.=' </new>';
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

	//CHECK FOR REQUIRED POST FIELDS
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
	
	//CHECK FOR RIGHT shipping_type 
	if (isset($_POST["shipping_type_id"]) && $_POST["shipping_type_id"]!=0)
	{
		//SHIPPINGTYPE KANN NUR IN VERBINDUNG MIT EINER ADRESSE GESETZT WERDEN
		if ( (!isset($_POST["ship_adr_id"]) || $_POST["ship_adr_id"]==0) && (!isset($_POST["bill_adr_id"]) || $_POST["bill_adr_id"]==0) && ($order["ship_adr_id"]==0 && $order["bill_adr_id"]==0) )
		{
			//KEINE ADRESSE GESETZT
			show_error(9789, 7, __FILE__, __LINE__);
			exit;	
		}
		
		//GET ADDRESS
		if (isset($_POST["ship_adr_id"]) || isset($_POST["bill_adr_id"]))
		{
			if ($_POST["ship_adr_id"]!=0) $adr_id = $_POST["ship_adr_id"]; else $adr_id = $_POST["bill_adr_id"];
		}
		else
		{
			if ($order["ship_adr_id"]!=0) $adr_id = $order["ship_adr_id"]; else $adr_id = $order["bill_adr_id"];
		}
		
		$res_address = q("SELECT * FROM shop_bill_adr WHERE adr_id = ".$adr_id, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_address)==0)
		{
			//KEINE ADRESSE ZUR ADRESS_ID GEFUNDEN
			show_error(9790, 7, __FILE__, __LINE__, "ADRESS_ID: ".$adr_id);
			exit;
		}
		$address = mysqli_fetch_assoc($res_address);
		
		//GET SHIPPINGTYPE DATA
		$res_shipping = q("SELECT * FROM shop_shipping_types WHERE id_shippingtype = ".$_POST["shipping_type_id"], $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shipping)==0)
		{
			//KEINEN SHIPPINGTYPE GEFUNDEN
			show_error(9791, 7, __FILE__, __LINE__, "SHIPPINGTYPE_ID: ".$_POST["shipping_type_id"]);
			exit;
		}
		$shippingtype=mysqli_fetch_assoc($res_shipping);
		
		//PRÜFE OB ZIELLAND MIT SHIPPINGTYPE ÜBEREINSTIMMT
		if ( ($address["country_id"]==1 && $shippingtype["international"]!=0) || ($shippingtype["international"]==1 && $address["country_id"]==1) )
		{
			//ZIELLAND UND SHIPPINGTYPE GEHÖREN NICHT ZUSAMMEN
			show_error(9792, 7, __FILE__, __LINE__, "SHIPPINGTYPE_ID: ".$shippingtype["id_shippingtype"].", COUNTRY_ID: ".$address["country_id"]);
			exit;
		}
	}
	

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
	
	$id_event=0;
	
	//CHECK FOR ANYTHING CHANGED AND GET CHANGES
	$order_changed=false;
	$eventdata=array();
	foreach($data as $key => $val)
	{
		if ($order[$key]!=$val)	
		{
			$order_changed=true;
			$eventdata[$key]["old"]=$order[$key];
			$eventdata[$key]["new"]=$val;
		}
	}
	
	//IF THERE ARE CHANGES -> WRITE ORDEREVENT && CALL 
	if ($order_changed)
	{
		$id_event=save_order_event(3, $_POST["SELECTOR_id_order"], $eventdata);
	}
		

	//CHECK IF THERE IS ANYTHING TO UPDATE
	if ($nvp!="" && $selector!="" && $order_changed)
	{
		$sql="UPDATE shop_orders SET ".$nvp." WHERE ".$selector.";";

		$res_update=q($sql,$dbshop, __FILE__, __LINE__);
		$affected_rows=mysqli_affected_rows();

/*
		//SET ORDEREVENT
		$id_event=save_order_event(3, $_POST["SELECTOR_id_order"], $data);
*/

		//SERVICE RESPONSE		
		echo '	<affected_rows>'.$affected_rows.'</affected_rows>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";

	}
	else
	{
		//SERVICE RESPONSE		
		echo '	<affected_rows>0</affected_rows>'."\n";
		echo '	<id_event>0</id_event>'."\n";
	}

		
	
?>