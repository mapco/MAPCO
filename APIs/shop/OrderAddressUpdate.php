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


	//CHECK FOR REQUIRED POST-VARIABLES
	$required=array("OrderID" =>"numericNN", 
				"customer_id" =>"numericNN", 
				"addresstype" => "text");
	check_man_params($required);					


	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		
		show_error(9762, 7, __FILE__, __LINE__, "OrderID:".$_POST["OrderID"]);
		exit;
	}
	$order[0]=mysqli_fetch_array($results);

	if ( !isset($_POST["country_code"]) && $_POST["country_code"]=="" && !isset($_POST["country_id"]) && ($_POST["country_id"]==0 || $_POST["country_id"]=="") )
	{
		show_error(9763, 7, __FILE__, __LINE__);
		exit;

	}

	// GET COUNTY & CODE
	if ( isset($_POST["country_code"]) && $_POST["country_code"]!="")
	{
		$res_country_code=q("SELECT * FROM shop_countries WHERE country_code = '".$_POST["country_code"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_country_code)==0)
		{
			show_error(9764, 7, __FILE__, __LINE__, "CountryCode: ".$_POST["country_code"]);
			exit;
		}
	}
	
	if ( isset($_POST["country_id"]) && $_POST["country_id"]!="" && $_POST["country_id"]!=0)
	{
		$res_country_code=q("SELECT * FROM shop_countries WHERE id_country = '".$_POST["country_id"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_country_code)==0)
		{
			show_error(9765, 7, __FILE__, __LINE__, "CountryCode: ".$_POST["country_code"]);
			exit;
		}
	}
	$country=mysqli_fetch_array($res_country_code);

	
	$results=q("SELECT * FROM shop_shops WHERE id_shop=".$order[0]["shop_id"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		show_error(9757, 7, __FILE__, __LINE__, "Shop_ID: ".$order[0]["shop_id"]);
		exit;
	}
	$shop=mysqli_fetch_array($results);

	if ( $_POST["addresstype"]!="bill" && $_POST["addresstype"]!="ship" && $_POST["addresstype"]!="both")
	{
		show_error(9766, 7, __FILE__, __LINE__, "AddressType: ".$_POST["addresstype"]);
		exit;
	}



	
	//CHECK IF address is known in shop_bill_adr
		//GET ALL ADDRESSES FROM CMS_USER
	switch ($_POST["addresstype"])
	{
		case "bill": $active=" AND active = 1"; break;
		case "ship": $active=" AND active_ship_adr = 1"; break;
		case "both": $active=" AND active = 1 AND active_ship_adr = 1"; break;
		default: $active="";
	}
		
	$known_addresses=array();
	$res_check=q("SELECT * FROM shop_bill_adr WHERE user_id = ".$_POST["customer_id"].$active.";", $dbshop, __FILE__, __LINE__);
	while ($row_check=mysqli_fetch_array($res_check))
	{
		if ($row_check["company"]==$_POST["company"] && $row_check["firstname"]==$_POST["firstname"] && $row_check["lastname"]==$_POST["lastname"] && $row_check["street"]==$_POST["street"] && $row_check["number"]==$_POST["number"] && $row_check["additional"]==$_POST["additional"] && $row_check["zip"]==$_POST["zip"] && $row_check["city"]==$_POST["city"]  && $row_check["country_id"]==$country["id_country"])
		{
			$known_addresses[]=$row_check["adr_id"];
		}
		
	}
		
	//if address is known -> do nothing
	//else insert new address
	if (sizeof($known_addresses)>0)
	{
		$adrID=$known_addresses[0];
		
		$data=array();
		//DATA FOR ORDEREVENT
		$data["adr_id"]=$adrID;
		//SAVE ORDEREVENT
		$id_event=save_order_event(9, $order[0]["id_order"], $data);

	}
	else
	{
			
		// INSERT ADDRESS
		q("INSERT INTO shop_bill_adr (
			user_id,
			shop_id,
			foreign_address_id,
			company, 
			firstname, 
			lastname, 
			street, 
			number, 
			additional, 
			zip, 
			city, 
			country, 
			country_id
		) VALUES (
			".$_POST["customer_id"].",
			".$order[0]["shop_id"].",
			'',
			'".mysqli_real_escape_string($dbshop, $_POST["company"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["firstname"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["lastname"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["street"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["number"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["additional"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["zip"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["city"])."',
			'".mysqli_real_escape_string($dbshop, $country["country"])."',
			".$country["id_country"]."
		);", $dbshop, __FILE__, __LINE__);
		
		$adrID=mysqli_insert_id($dbshop);
		
		$data=array();
		//DATA FOR ORDEREVENT
		$data["adr_id"]=$adrID;
		$data["user_id"]=$_POST["customer_id"];
		$data["company"]=$_POST["company"];
		$data["firstname"]=$_POST["firstname"];
		$data["lastname"]=$_POST["lastname"];
		$data["street"]=$_POST["street"];
		$data["number"]=$_POST["number"];
		$data["additional"]=$_POST["additional"];
		$data["zip"]=$_POST["zip"];
		$data["city"]=$_POST["city"];
		$data["country"]=$country["country"];
		$data["country_id"]=$country["id_country"];
		
		//SAVE ORDEREVENT
		if ($_POST["addresstype"]=="bill" || $_POST["addresstype"]=="both")
		{
 			$id_event=save_order_event(22, $order[0]["id_order"], $data);
		}
		if ($_POST["addresstype"]=="ship" || $_POST["addresstype"]=="both")
		{
 			$id_event=save_order_event(9, $order[0]["id_order"], $data);
		}
	}

	// SHOP_ORDERS
	if ($_POST["addresstype"]=="bill" || $_POST["addresstype"]=="both")
	{
		if ($order[0]["combined_with"]>0)
		{
			q("UPDATE shop_orders SET
			customer_id = ".$_POST["customer_id"].",
			usermail = '".mysqli_real_escape_string($dbshop, $_POST["usermail"])."',
			userphone = '".mysqli_real_escape_string($dbshop, $_POST["userphone"])."',
			bill_company = '".mysqli_real_escape_string($dbshop, $_POST["company"])."',
			bill_firstname = '".mysqli_real_escape_string($dbshop, $_POST["firstname"])."',
			bill_lastname = '".mysqli_real_escape_string($dbshop, $_POST["lastname"])."',
			bill_street = '".mysqli_real_escape_string($dbshop, $_POST["street"])."',
			bill_number = '".mysqli_real_escape_string($dbshop, $_POST["number"])."',
			bill_additional = '".mysqli_real_escape_string($dbshop, $_POST["additional"])."',
			bill_zip = '".mysqli_real_escape_string($dbshop, $_POST["zip"])."',
			bill_city = '".mysqli_real_escape_string($dbshop, $_POST["city"])."',
			bill_country = '".mysqli_real_escape_string($dbshop, $country["country"])."',
			bill_country_code = '".mysqli_real_escape_string($dbshop, $country["country_code"])."',
			bill_adr_id = ".$adrID.",
			bill_address_manual_update = 1 
			WHERE combined_with = ".$order[0]["combined_with"].";", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			q("UPDATE shop_orders SET
			customer_id = ".$_POST["customer_id"].",
			usermail = '".mysqli_real_escape_string($dbshop, $_POST["usermail"])."',
			userphone = '".mysqli_real_escape_string($dbshop, $_POST["userphone"])."',
			bill_company = '".mysqli_real_escape_string($dbshop, $_POST["company"])."',
			bill_firstname = '".mysqli_real_escape_string($dbshop, $_POST["firstname"])."',
			bill_lastname = '".mysqli_real_escape_string($dbshop, $_POST["lastname"])."',
			bill_street = '".mysqli_real_escape_string($dbshop, $_POST["street"])."',
			bill_number = '".mysqli_real_escape_string($dbshop, $_POST["number"])."',
			bill_additional = '".mysqli_real_escape_string($dbshop, $_POST["additional"])."',
			bill_zip = '".mysqli_real_escape_string($dbshop, $_POST["zip"])."',
			bill_city = '".mysqli_real_escape_string($dbshop, $_POST["city"])."',
			bill_country = '".mysqli_real_escape_string($dbshop, $country["country"])."',
			bill_country_code = '".mysqli_real_escape_string($dbshop, $country["country_code"])."',
			bill_adr_id = ".$adrID.",
			bill_address_manual_update = 1 
			WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
		}
	}
	
	if ($_POST["addresstype"]=="ship" || $_POST["addresstype"]=="both")
	{
		if ($order[0]["combined_with"]>0)
		{
			q("UPDATE shop_orders SET
			customer_id = ".$_POST["customer_id"].",
			usermail = '".mysqli_real_escape_string($dbshop, $_POST["usermail"])."',
			userphone = '".mysqli_real_escape_string($dbshop, $_POST["userphone"])."',
			ship_company = '".mysqli_real_escape_string($dbshop, $_POST["company"])."',
			ship_firstname = '".mysqli_real_escape_string($dbshop, $_POST["firstname"])."',
			ship_lastname = '".mysqli_real_escape_string($dbshop, $_POST["lastname"])."',
			ship_street = '".mysqli_real_escape_string($dbshop, $_POST["street"])."',
			ship_number = '".mysqli_real_escape_string($dbshop, $_POST["number"])."',
			ship_additional = '".mysqli_real_escape_string($dbshop, $_POST["additional"])."',
			ship_zip = '".mysqli_real_escape_string($dbshop, $_POST["zip"])."',
			ship_city = '".mysqli_real_escape_string($dbshop, $_POST["city"])."',
			ship_country = '".mysqli_real_escape_string($dbshop, $country["country"])."',
			ship_country_code = '".mysqli_real_escape_string($dbshop, $country["country_code"])."',
			ship_adr_id = ".$adrID.",
			bill_address_manual_update = 1 
			WHERE combined_with = ".$order[0]["combined_with"].";", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			q("UPDATE shop_orders SET
			customer_id = ".$_POST["customer_id"].",
			usermail = '".mysqli_real_escape_string($dbshop, $_POST["usermail"])."',
			userphone = '".mysqli_real_escape_string($dbshop, $_POST["userphone"])."',
			ship_company = '".mysqli_real_escape_string($dbshop, $_POST["company"])."',
			ship_firstname = '".mysqli_real_escape_string($dbshop, $_POST["firstname"])."',
			ship_lastname = '".mysqli_real_escape_string($dbshop, $_POST["lastname"])."',
			ship_street = '".mysqli_real_escape_string($dbshop, $_POST["street"])."',
			ship_number = '".mysqli_real_escape_string($dbshop, $_POST["number"])."',
			ship_additional = '".mysqli_real_escape_string($dbshop, $_POST["additional"])."',
			ship_zip = '".mysqli_real_escape_string($dbshop, $_POST["zip"])."',
			ship_city = '".mysqli_real_escape_string($dbshop, $_POST["city"])."',
			ship_country = '".mysqli_real_escape_string($dbshop, $country["country"])."',
			ship_country_code = '".mysqli_real_escape_string($dbshop, $country["country_code"])."',
			ship_adr_id = ".$adrID.",
			bill_address_manual_update = 1 
			WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
		}
	}


		
	//	echo "<OrderAddressUpdateResponse>\n";
	//echo "<Ack>Success</Ack>\n";
//	echo "<country>".$country["country"]."</country>\n";
	// echo "</OrderAddressUpdateResponse>";

?>