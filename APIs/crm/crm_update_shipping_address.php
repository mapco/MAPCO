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
			'".mysqli_real_escape_string($dbshop,$xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
		
	}



	if ( !isset($_POST["OrderID"]) )
	{
		echo '<crm_update_shipping_addressResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Bestellnummer (id_order) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_shipping_addressResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<crm_update_shipping_addressResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Bestellung mit der Nummer '.$_POST["OrderID"].' konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_shipping_addressResponse>'."\n";
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

	if ( !isset($_POST["ship_country_code"]) )
	{
		echo '<crm_update_shipping_addressResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Country Code nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Country Code (DE, etc.) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_shipping_addressResponse>'."\n";
		exit;
	}
	
	$results=q("SELECT * FROM shop_shops WHERE id_shop=".$order[0]["shop_id"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<crm_update_shipping_addressResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der in der Bestellung angegebene Shop (shop_id) ist ungültig.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_shipping_addressResponse>'."\n";
		exit;
	}
	$shop=mysqli_fetch_array($results);


	// GET COUNTY & CODE
	$res_country_code=q("SELECT * FROM shop_countries WHERE country_code = '".$_POST["ship_country_code"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_country_code)==0)
	{
		echo '<crm_update_shipping_addressResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Liferland nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zum übermittelten CountryCode konnte kein Land gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_shipping_addressResponse>'."\n";
		exit;
	}
	$country_code=mysqli_fetch_array($res_country_code);

	if ($order[0]["combined_with"]>0)
	{
		q("UPDATE shop_orders SET
		customer_id = ".$_POST["customer_id"].",
		usermail = '".mysqli_real_escape_string($dbshop,$_POST["usermail"])."',
		userphone = '".mysqli_real_escape_string($dbshop,$_POST["userphone"])."',
		bill_company = '".mysqli_real_escape_string($dbshop,$_POST["ship_company"])."',
		bill_firstname = '".mysqli_real_escape_string($dbshop,$_POST["ship_firstname"])."',
		bill_lastname = '".mysqli_real_escape_string($dbshop,$_POST["ship_lastname"])."',
		bill_street = '".mysqli_real_escape_string($dbshop,$_POST["ship_street"])."',
		bill_number = '".mysqli_real_escape_string($dbshop,$_POST["ship_number"])."',
		bill_additional = '".mysqli_real_escape_string($dbshop,$_POST["ship_additional"])."',
		bill_zip = '".mysqli_real_escape_string($dbshop,$_POST["ship_zip"])."',
		bill_city = '".mysqli_real_escape_string($dbshop,$_POST["ship_city"])."',
		bill_country = '".mysqli_real_escape_string($dbshop,$country_code["country"])."',
		bill_country_code = '".mysqli_real_escape_string($dbshop,$country_code["country_code"])."',
		bill_address_manual_update = 1 
		WHERE combined_with = ".$order[0]["combined_with"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		q("UPDATE shop_orders SET
		customer_id = ".$_POST["customer_id"].",
		usermail = '".mysqli_real_escape_string($dbshop,$_POST["usermail"])."',
		userphone = '".mysqli_real_escape_string($dbshop,$_POST["userphone"])."',
		bill_company = '".mysqli_real_escape_string($dbshop,$_POST["ship_company"])."',
		bill_firstname = '".mysqli_real_escape_string($dbshop,$_POST["ship_firstname"])."',
		bill_lastname = '".mysqli_real_escape_string($dbshop,$_POST["ship_lastname"])."',
		bill_street = '".mysqli_real_escape_string($dbshop,$_POST["ship_street"])."',
		bill_number = '".mysqli_real_escape_string($dbshop,$_POST["ship_number"])."',
		bill_additional = '".mysqli_real_escape_string($dbshop,$_POST["ship_additional"])."',
		bill_zip = '".mysqli_real_escape_string($dbshop,$_POST["ship_zip"])."',
		bill_city = '".mysqli_real_escape_string($dbshop,$_POST["ship_city"])."',
		bill_country = '".mysqli_real_escape_string($dbshop,$country_code["country"])."',
		bill_country_code = '".mysqli_real_escape_string($dbshop,$country_code["country_code"])."',
		bill_address_manual_update = 1 
		WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	}
	
	//CHECK IF address is known in shop_bill_adr
	$res_check=q("SELECT * FROM shop_bill_adr WHERE adr_id = ".$order[0]["bill_adr_id"]." and user_id = ".$order[0]["customer_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_check)>0)
	{
		//UPDATE shop_bill_adr
		q("UPDATE shop_bill_adr SET
			ADR__ID = 0,
			company = '".mysqli_real_escape_string($dbshop,$_POST["ship_company"])."',
			firstname = '".mysqli_real_escape_string($dbshop,$_POST["ship_firstname"])."',
			lastname = '".mysqli_real_escape_string($dbshop,$_POST["ship_lastname"])."',
			street = '".mysqli_real_escape_string($dbshop,$_POST["ship_street"])."',
			number = '".mysqli_real_escape_string($dbshop,$_POST["ship_number"])."',
			additional = '".mysqli_real_escape_string($dbshop,$_POST["ship_additional"])."',
			zip = '".mysqli_real_escape_string($dbshop,$_POST["ship_zip"])."',
			city = '".mysqli_real_escape_string($dbshop,$_POST["ship_city"])."',
			country = '".mysqli_real_escape_string($dbshop,$country_code["country"])."',
			country_id = '".mysqli_real_escape_string($dbshop,$country_code["id_country"])."'
		WHERE adr_id = ".$order[0]["bill_adr_id"]." AND user_id = ".$order[0]["customer_id"].";", $dbshop, __FILE__, __LINE__);
		
		$data=array();
		//DATA FOR ORDEREVENT
		$data["ADR__ID"]=0;
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
		$data["SELECTOR_adr_id"]=$order[0]["bill_adr_id"];
		$data["SELECTOR_user_id"]=$order[0]["customer_id"];
		//SAVE ORDEREVENT
		$id_event=save_order_event(8, $order[0]["id_order"], $data);

	}
	else
	{
		$foreign_addressID="";
		//GET FOREIGN ADDRESS ID
		if ($shop["shop_type"]==2)
		{
			$res_f_adrID=q("SELECT * FROM ebay_orders WHERE OrderID = '".$order[0]["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_f_adrID)>0)
			{
				$ebay_order=mysqli_fetch_array($res_f_adrID);
				$foreign_addressID=$ebay_order["ShippingAddressAddressID"];
			}
			else
			{
				$foreign_addressID="";
			}
		}
			
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
			country_id, 
			standard,
			active
		) VALUES (
			".$_POST["customer_id"].",
			".$_POST["shop_id"].",
			'".mysqli_real_escape_string($dbshop,$foreign_addressID)."',
			'".mysqli_real_escape_string($dbshop,$_POST["ship_company"])."',
			'".mysqli_real_escape_string($dbshop,$_POST["ship_firstname"])."',
			'".mysqli_real_escape_string($dbshop,$_POST["ship_lastname"])."',
			'".mysqli_real_escape_string($dbshop,$_POST["ship_street"])."',
			'".mysqli_real_escape_string($dbshop,$_POST["ship_number"])."',
			'".mysqli_real_escape_string($dbshop,$_POST["ship_additional"])."',
			'".mysqli_real_escape_string($dbshop,$_POST["ship_zip"])."',
			'".mysqli_real_escape_string($dbshop,$_POST["ship_city"])."',
			'".mysqli_real_escape_string($dbshop,$country_code["country"])."',
			".$country_code["id_country"].",
			1,
			1
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

			
		if ($order[0]["combined_with"]>0)
		{
			q("UPDATE shop_orders SET
			bill_adr_id = ".$adrID." 
			WHERE combined_with = ".$order[0]["combined_with"].";", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			q("UPDATE shop_orders SET
			bill_adr_id = ".$adrID." 
			WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
		}
	}
		
	/*	
	if ($shop["shop_type"]==2 && $order["payments_type_id"]!=4)
	{
		//UPDATE EBAY DATA
		$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "ReviseCheckoutStatus", "id_order" => $_POST["OrderID"], "amount" => $_POST["amount"]));
	
//	echo $responseXml;
	
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<crm_update_shipping_addressResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Antwort von Service fehlerhaft.</shortMsg>'."\n";
			echo '		<longMsg>Beim Abrufen der Serverantwort ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</crm_update_shipping_addressResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if( $response->Ack[0]=="Failure")
		{
			echo '<crm_update_shipping_addressResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Die Zahlung konnte bei Ebay nicht gesetzt werden.</shortMsg>'."\n";
			echo '		<longMsg><![CDATA['.$responseXml.']]></longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</crm_update_shipping_addressResponse>'."\n";
			exit;
		}
	
	}
*/
	echo "<crm_update_shipping_addressResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<country>".$country_code["country"]."</country>\n";
	echo "</crm_update_shipping_addressResponse>";

?>