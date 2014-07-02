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



	if ( !isset($_POST["OrderID"]) )
	{
		echo '<OrderAddressUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Bestellnummer (id_order) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderAddressUpdateResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<OrderAddressUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Bestellung mit der Nummer '.$_POST["OrderID"].' konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderAddressUpdateResponse>'."\n";
		exit;
	}
	$order[0]=mysqli_fetch_array($results);
	/*
	if ($order[0]["combined_with"]>0)
	{
		$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$order[0]["combined_with"]." AND NOT id_order = ".$order[0]["id_order"].";", $dbshop, __FILE__, __LINE__);
		while ($row_orders=mysqli_fetch_array($res_orders))
		{
			$order[]=$row_orders;
		}
	}
*/
	if ( !isset($_POST["customer_id"]) || $_POST["customer_id"]==0 || $_POST["customer_id"]=="")
	{
		echo '<OrderAddressUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Customer_ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Customer/User ID angegeben werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderAddressUpdateResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["country_code"]) )
	{
		echo '<OrderAddressUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Country Code nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Country Code (DE, etc.) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderAddressUpdateResponse>'."\n";
		exit;
	}
	
	$results=q("SELECT * FROM shop_shops WHERE id_shop=".$order[0]["shop_id"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<OrderAddressUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der in der Bestellung angegebene Shop (shop_id) ist ungültig.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderAddressUpdateResponse>'."\n";
		exit;
	}
	$shop=mysqli_fetch_array($results);

	if ( !isset($_POST["addresstype"]) )
	{
		echo '<OrderAddressUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Adresstype nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Adresstyp (bill, ship, both) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderAddressUpdateResponse>'."\n";
		exit;
	}

	// GET COUNTY & CODE
	$res_country_code=q("SELECT * FROM shop_countries WHERE country_code = '".$_POST["country_code"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_country_code)==0)
	{
		echo '<OrderAddressUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Lieferland nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zum übermittelten CountryCode konnte kein Land gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderAddressUpdateResponse>'."\n";
		exit;
	}
	$country_code=mysqli_fetch_array($res_country_code);


	
	//CHECK IF address is known in shop_bill_adr
		//GET ALL ADDRESSES FROM CMS_USER
	switch ($_POST["addresstype"])
	{
		case "bill": $active="active = 1"; break;
		case "ship": $active="active_ship_adr = 1"; break;
		case "both": $active="active = 1 AND active_ship_adr = 1"; break;
	}
		
	$known_addresses=array();
	$res_check=q("SELECT * FROM shop_bill_adr WHERE user_id = ".$_POST["customer_id"]." AND ".$active.";", $dbshop, __FILE__, __LINE__);
	while ($row_check=mysqli_fetch_array($res_check))
	{
		if ($row_check["company"]==$_POST["company"] && $row_check["firstname"]==$_POST["firstname"] && $row_check["lastname"]==$_POST["lastname"] && $row_check["street"]==$_POST["street"] && $row_check["number"]==$_POST["number"] && $row_check["additional"]==$_POST["additional"] && $row_check["zip"]==$_POST["zip"] && $row_check["city"]==$_POST["city"]  && $row_check["country_id"]==$country_code["id_country"])
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
			".$_POST["shop_id"].",
			'',
			'".mysqli_real_escape_string($dbshop, $_POST["company"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["firstname"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["lastname"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["street"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["number"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["additional"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["zip"])."',
			'".mysqli_real_escape_string($dbshop, $_POST["city"])."',
			'".mysqli_real_escape_string($dbshop, $country_code["country"])."',
			".$country_code["id_country"]."
		);", $dbshop, __FILE__, __LINE__);
		
		$adrID=mysqli_insert_id($dbshop);
		
		$data=array();
		//DATA FOR ORDEREVENT
		$data["adr_id"]=$adrID;
		$data["user_id"]=$_POST["customer_id"];
		$data["shop_id"]=$_POST["shop_id"];
		$data["foreign_address_id"]=$foreign_addressID;
		$data["company"]=$_POST["ship_company"];
		$data["firstname"]=$_POST["ship_firstname"];
		$data["lastname"]=$_POST["ship_lastname"];
		$data["street"]=$_POST["ship_street"];
		$data["number"]=$_POST["ship_number"];
		$data["additional"]=$_POST["ship_additional"];
		$data["zip"]=$_POST["ship_zip"];
		$data["city"]=$_POST["ship_city"];
		$data["country"]=$_POST["country"];
		$data["country_id"]=$_POST["id_country"];
		//SAVE ORDEREVENT
		$id_event=save_order_event(9, $order[0]["id_order"], $data);

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
			bill_country = '".mysqli_real_escape_string($dbshop, $country_code["country"])."',
			bill_country_code = '".mysqli_real_escape_string($dbshop, $country_code["country_code"])."',
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
			bill_country = '".mysqli_real_escape_string($dbshop, $country_code["country"])."',
			bill_country_code = '".mysqli_real_escape_string($dbshop, $country_code["country_code"])."',
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
			ship_country = '".mysqli_real_escape_string($dbshop, $country_code["country"])."',
			ship_country_code = '".mysqli_real_escape_string($dbshop, $country_code["country_code"])."',
			ship_adr_id = ".$adrID.",
			ship_address_manual_update = 1 
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
			ship_country = '".mysqli_real_escape_string($dbshop, $country_code["country"])."',
			ship_country_code = '".mysqli_real_escape_string($dbshop, $country_code["country_code"])."',
			ship_adr_id = ".$adrID.",
			ship_address_manual_update = 1 
			WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
		}
	}


		
	echo "<OrderAddressUpdateResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<country>".$country_code["country"]."</country>\n";
	echo "</OrderAddressUpdateResponse>";

?>