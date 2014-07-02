<?php

	if ( !isset($_POST["OrderID"]) )
	{
		echo '<crm_set_shipment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItemID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderItemID (id.shop_orders_items) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_shipment_stateResponse>'."\n";
		exit;
	}

	//GET ORDERdata
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_order)==0)
	{
		echo '<crm_set_shipment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItem nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderItemID konnte keine Bestellposition gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_shipment_stateResponse>'."\n";
		exit;
	}
	$order=mysql_fetch_array($res_order);
	
	
	if ( !isset($_POST["state"]) )
	{
		echo '<crm_set_shipment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Status nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Status zur Bestellposition angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_shipment_stateResponse>'."\n";
		exit;
	}

	//GET SHOP TYPES
	$shops=array();
	$res_shops=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
	while ($row_shops=mysql_fetch_array($res_shops))
	{
		$shops[$row_shops["id_shop"]]=$row_shops["shop_type"];
	}

//	if ($_POST["state"]==2)
//	{
		q("UPDATE shop_orders SET status_id = ".$_POST["state"].", status_date = ".time().", shipping_number = '".mysql_real_escape_string($_POST["shipping_number"], $dbshop)."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
//	}

	if ($shops[$order["shop_id"]]==2)
	{
		//UPDATE EBAY
		$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "CompleteSale", "id_order" => $_POST["OrderID"]) );
		

		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<crm_set_shipment_stateResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Antwort von eBay fehlerhaft.</shortMsg>'."\n";
			echo '		<longMsg>Beim Abrufen der Serverantwort von eBay ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</crm_set_shipment_stateResponse>'."\n";
			exit;
		}
	}
	
	echo "<crm_set_shipment_stateResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<state_id>".$_POST["state"]."</state_id>\n";
	echo "<state_date>".time()."</state_date>\n";
	echo "</crm_set_shipment_stateResponse>";


?>