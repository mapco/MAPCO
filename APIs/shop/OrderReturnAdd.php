<?php

	//CHECK $_POST PARAMS
	check_man_params(array("mode" => "text", "order_id" => "numericNN"));
	
	
	//GET SHOP ORDER (and combined)
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		show_error(9762, 7, __FILE__, __LINE__, "order_id:".$_POST["order_id"]);
	}
	$order=array();
	$order[0]=mysqli_fetch_array($res_order);
	
	if ($order[0]["combined_with"]>0)
	{
		$tmp_orderid=$order[0]["combined_with"];
		
		//GET ALL ORDERS OF COMBINATION; ORDER[0] ->"MOTHER"
		$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
		$order[0]=mysqli_fetch_array($res_orders);
		
		$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND NOT id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
		while ($row_orders=mysqli_fetch_array($res_orders))
		{
			$order[sizeof($order)]=$row_orders;
		}
	}

	if (!isset($_POST["exchange_order_id"]) || !is_numeric($_POST["exchange_order_id"]) )
	{
		$_POST["exchange_order_id"]=0;
	}

	// SET RETURN
	
	$res_insert = q("INSERT INTO shop_returns2 (
		state,
		shop_id,
		order_id, 
		exchange_order_id,
		invoice_nr,
		return_type,
		firstmod,
		firstmod_user,
		lastmod,
		lastmod_user
	) VALUES (
		0,
		".$order[0]["shop_id"].",
		".$order[0]["id_order"].",
		".$_POST["exchange_order_id"].",
		'".mysqli_real_escape_string($dbshop, $order[0]["invoice_nr"])."',
		'".mysqli_real_escape_string($dbshop, $_POST["mode"])."',
		".time().",
		".$_SESSION["id_user"].",
		".time().",
		".$_SESSION["id_user"]."
	);", $dbshop, __FILE__, __LINE__);
	
	$return_id = mysqli_insert_id($dbshop);
	
	//ORDEREVENT SPEICHERN
	$data = "";
	$data.='<data>';
	$data.='	<return_id>'.$return_id.'</return_id>';
	$data.='	<state>0</state>';
	$data.='	<shop_id>'.$order[0]["shop_id"].'</shop_id>';
	$data.='	<order_id>'.$order[0]["id_order"].'</order_id>';
	$data.='	<return_action><![CDATA['.$_POST["mode"].']]></return_action>';
	$data.='	<firstmod>'.$_SESSION["id_user"].'</firstmod>';
	$data.='	<firstmod_user>'.time().'</firstmod_user>';
	$data.='	<lastmod>'.$_SESSION["id_user"].'</lastmod>';
	$data.='	<lastmod_user>'.time().'</lastmod_user>';
	$data.='</data>';
	
	if ($_POST["mode"]=="return") $eventtype_id = 23;
	if ($_POST["mode"]=="exchange") $eventtype_id = 24;
	
		
	$responseXML=post(PATH."soa2/", array("API" => "shop", "APIRequest" => "OrderEventSet", "order_id" => $order[0]["id_order"], "eventtype_id" => $eventtype_id, "data" => $data));

	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXML);
	}
	catch(Exception $e)
	{
		show_error(9756, 7, __FILE__, __LINE__, $responseXML);
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if ($response->Ack[0]!="Success")
	{
		show_error(9777, 7, __FILE__, __LINE__, $responseXML);
	}

	//SERVICE RESPONSE
	echo '<return_id>'.$return_id.'</return_id>'."\n";

?>