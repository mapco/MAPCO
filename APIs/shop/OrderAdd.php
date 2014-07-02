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

	//default values
	if (!isset($_POST["VAT"])) $_POST["VAT"]=19; //default 19% MwSt


	//CHECK FOR MODE
	check_man_params(array("mode" => "text"));

	if ($_POST["mode"]=="new")
	{
		/*
		INSERT NEW ORDER
		- benötigt die in $required definierten Felder
		- zusätzlich in $_POST übergebene Werte werden ebenfalls in die Tabelle geschrieben (bei Übereinstimmung von Feldnamen)
		- AUTOFILL	- firstmod
					- firstmoduser
					- lastmod
					- lastmoduser
		*/

		$required=array("shop_id" =>"numericNN", 
						"status_id" =>"numericNN", 
						//"Currency_Code" => "currency", 
						"customer_id" =>"numericNN", 
						"usermail" => "text", 
						"bill_lastname" => "text", 
						"bill_zip" => "text", 
						"bill_city" => "text", 
						"bill_street" => "text", 
						"bill_number" => "text", 
						"shipping_costs" =>"numeric", 
						"shipping_type_id" =>"numeric", 
						"bill_adr_id" =>"numeric", 
						"shipping_net" =>"numeric");
	
		check_man_params($required);					
		
		//AUTOFILL
		if (!isset($_POST["firstmod"])) $_POST["firstmod"]=time();
		if (!isset($_POST["firstmod_user"])) $_POST["firstmod_user"]=$_SESSION["id_user"];
		if (!isset($_POST["lastmod"])) $_POST["lastmod"]=time();
		if (!isset($_POST["lastmod_user"])) $_POST["lastmod_user"]=$_SESSION["id_user"];

	}

	if ($_POST["mode"]=="copy")
	{
		/*
		INSERT NEW ORDER AS COPY
		- benötigt shop_orders.id_order (SOURCE)
		- schreibt die SOURCE-Werte in die neue Order, wenn die Felder nicht per $_POST übergeben werden
		*/ 
		
		$required=array("id_order" =>"numericNN"); 
		check_man_params($required);
		
		//GET DATA
		$res_data=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_data)==0)
		{
			//ORDER NICHT GEFUNDEN
			show_error(9774, 7, __FILE__, __LINE__, "order_id: ".$_POST["id_order"]);
			exit;
		}
		$oldorder_data=mysqli_fetch_assoc($res_data);

	}

	if ($_POST["mode"]=="manual" || $_POST["mode"]=="ebay")
	{
		/*
		schreibt Order, wie Felder übergeben
		- AUTOFILL	- firstmod
					- firstmoduser
					- lastmod
					- lastmoduser
		*/
		//AUTOFILL
		if (!isset($_POST["firstmod"])) $_POST["firstmod"]=time();
		if (!isset($_POST["firstmod_user"])) $_POST["firstmod_user"]=$_SESSION["id_user"];
		if (!isset($_POST["lastmod"])) $_POST["lastmod"]=time();
		if (!isset($_POST["lastmod_user"])) $_POST["lastmod_user"]=$_SESSION["id_user"];

	}

/*
	$adr_id = 0;

	//CHECK FOR RIGHT shipping_type 
	if (isset($_POST["shipping_type_id"]) && $_POST["shipping_type_id"]!=0)
	{
		//SHIPPINGTYPE KANN NUR IN VERBINDUNG MIT EINER ADRESSE GESETZT WERDEN
		if ( (!isset($_POST["ship_adr_id"]) || $_POST["ship_adr_id"]==0) && (!isset($_POST["bill_adr_id"]) || $_POST["bill_adr_id"]==0) )
		{
			//KEINE ADRESSE GESETZT
		//	show_error(9789, 7, __FILE__, __LINE__);
		//	exit;	
		$_POST["shipping_type_id"] = 0;
		}
		
		//GET ADDRESS
		if (($_POST["mode"]=="new" || $_POST["mode"]=="manual") && $_POST["shipping_type_id"]!=0)
		{
			if ($_POST["ship_adr_id"]!=0) $adr_id = $_POST["ship_adr_id"]; else $adr_id = $_POST["bill_adr_id"];
		}
		elseif($_POST["mode"]=="copy"  && $_POST["shipping_type_id"]!=0)
		{
			if (isset($_POST["ship_adr_id"]) || isset($_POST["bill_adr_id"]))
			{
				if ($_POST["ship_adr_id"]!=0) $adr_id = $_POST["ship_adr_id"]; else $adr_id = $_POST["bill_adr_id"];
			}
			else
			{
				if ($oldorder_data["ship_adr_id"]!=0) $adr_id = $oldorder_data["ship_adr_id"]; else $adr_id = $oldorder_data["bill_adr_id"];
			}
		}
		if ($adr_id != 0){
			
			$res_address = q("SELECT * FROM shop_bill_adr WHERE adr_id = ".$adr_id, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_address)==0)
			{
				//KEINE ADRESSE ZUR ADRESS_ID GEFUNDEN
				show_error(9790, 7, __FILE__, __LINE__, "ADRESS_ID: ".$adr_id);
				exit;
			}
			$address = mysqli_fetch_assoc($res_address);
		}
		if ($_POST["shipping_type_id"]!=0)
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

			
//INSERT ====================================================================================
	if ($_POST["mode"]=="new" || $_POST["mode"]=="manual")
	{
		
		//INSERT DATA
			//get TableStructure
		$fields="";
		$values="";
		
		$data=array(); // DATAFIELD FOR ORDEREVENT
		
		$res_struct=q("SHOW COLUMNS FROM shop_orders;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];
					
					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_order=mysqli_insert_id($dbshop);
		
		$data["id_order"]=$id_order;
		
		//SET ORDEREVENT
		$id_event=save_order_event(1, $id_order, $data);
		
		
		//SERVICE RESPONSE
		echo '	<id_order>'.$id_order.'</id_order>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
		
		
		//CALL PAYMENTNOTIFICATIONHANDLER
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationHandler";
		$postfields["mode"]="OrderAdd";
		$postfields["orderid"]=$id_order;
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
	
	if ($_POST["mode"]=="copy")
	{
		
		//INSERT DATA
			//get TableStructure
		$data=array();
		$fields="";
		$values="";
		$res_struct=q("SHOW COLUMNS FROM shop_orders;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				// IF SET POST DATA 
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];

					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
				// ELSE USE DATA FROM SOURCE
				else	
				{
					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $oldorder_data[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_order=mysqli_insert_id($dbshop);

		$data["id_order"]=$id_order;
		
		$id_event=save_order_event(1, $id_order, $data);
		//SERVICE RESPONSE
		echo '	<id_order>'.$id_order.'</id_order>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
		
		//CALL PAYMENTNOTIFICATIONHANDLER
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationHandler";
		$postfields["mode"]="OrderAdd";
		$postfields["orderid"]=$id_order;
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

	if ($_POST["mode"]=="ebay")
	{
		
		//INSERT DATA
			//get TableStructure
		$fields="";
		$values="";
		
		$data=array(); // DATAFIELD FOR ORDEREVENT
		
		$res_struct=q("SHOW COLUMNS FROM shop_orders;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];
					
					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_order=mysqli_insert_id($dbshop);
		
		$data["id_order"]=$id_order;
		
		//SET ORDEREVENT
		$id_event=save_order_event(1, $id_order, $data);
		
		
		//SERVICE RESPONSE
		echo '	<id_order>'.$id_order.'</id_order>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
	}

	if ($_POST["mode"]=="shop")
	{
		
		//INSERT DATA
			//get TableStructure
		$fields="";
		$values="";
		
		$data=array(); // DATAFIELD FOR ORDEREVENT
		
		$res_struct=q("SHOW COLUMNS FROM shop_orders;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];
					
					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_order=mysqli_insert_id($dbshop);
		
		$data["id_order"]=$id_order;
		if($id_order>0) $_SESSION["order_id"]=$id_order;
		
		//SET ORDEREVENT
		$id_event=save_order_event(1, $id_order, $data);
		
		
		//SERVICE RESPONSE
		echo '	<id_order>'.$id_order.'</id_order>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
	}
?>