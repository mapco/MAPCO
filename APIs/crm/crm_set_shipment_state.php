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
	if (mysqli_num_rows($res_order)==0)
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
	$order[0]=mysqli_fetch_array($res_order);
	
  	if ($order[0]["combined_with"]>0)
	{
		$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$order[0]["combined_with"]." AND NOT id_order = ".$order[0]["id_order"].";", $dbshop, __FILE__, __LINE__);
		while ($row_orders=mysqli_fetch_array($res_orders))
		{
			$order[]=$row_orders;
		}
	}
	
	
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
	while ($row_shops=mysqli_fetch_array($res_shops))
	{
		$shops[$row_shops["id_shop"]]=$row_shops["shop_type"];
	}

	for ($i=0; $i<sizeof($order); $i++)
	{
		if ($order[$i]["status_id"] == 1 || $order[$i]["status_id"] == 5 || $order[$i]["status_id"] == 7)
		{
	
			q("UPDATE shop_orders SET status_id = ".$_POST["state"].", status_date = ".time().", lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE id_order = ".$order[$i]["id_order"].";", $dbshop, __FILE__, __LINE__);
		}
		
		//SAVE ORDEREVENT
			//CREATE DATA FOR EVENT
			$data='';
			$data.='<data>';
			$data.='<status_id>'.$_POST["state"].'</status_id>';
			$data.='<status_date>'.time().'</status_date>';
			$data.='<lastmod>'.time().'</lastmod>';
			$data.='<lastmod_user>'.$_SESSION["id_user"].'</lastmod_user>';
			$data.='</data>';

			$responseXml = post("http://www.mapco.de/soa2/index.php", array("API" => "shop", "APIRequest" => "OrderEventSet", "order_id" => $_POST["OrderID"], "eventtype_id" => 21, "data" => $data) );
			
	
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				track_error(9756, 7, __FILE__, __LINE__, "RESPONSE:".$responseXml); 
			}
		
	}
	
	echo "<crm_set_shipment_stateResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<state_id>".$_POST["state"]."</state_id>\n";
	echo "<state_date>".time()."</state_date>\n";
	echo "</crm_set_shipment_stateResponse>";


?>