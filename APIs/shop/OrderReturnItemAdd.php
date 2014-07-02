<?php

	//SOA2 Service

	check_man_params(array("return_id" => "numericNN", "shop_orders_items_id" => "numericNN", "amount" => "numericNN", "return_reason" => "numericNN"));

	//check return_id
	$res_check = q("SELECT * from shop_returns2 WHERE id_return = ".$_POST["return_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_check)==0)
	{
		echo "keine Rückgabe gefunden";
		//show_error();
		exit;
	}
	$return = mysqli_fetch_assoc($res_check);
	
	$res_order_item =q("SELECT * FROM shop_orders_items WHERE order_id = ".$return["order_id"]." AND id = ".$_POST["shop_orders_items_id"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order_item)==0)
	{
		echo "keine Orderitem gefunden";
		//show_error();
		exit;
	}
	$order_item = mysqli_fetch_assoc($res_order_item);
	
	$exchange_orderitem_id = 0;
	
	//IF UMTAUSCH
	if ($return["return_type"]=="exchange")
	{

		check_man_params(array("exchange_shop_orders_item" => "numericNN", "exchange_shop_orders_item_amount" => "numericNN", "exchange_shop_orders_item_FCnetto" => "numericNN"));
		
		//BENÖTIGE UMTAUSCHKURS AUS URSPRUNGSBESTELLUNG

		//UMTAUSCH ORDERITEM in shop_orders_items SCHREIBEN
		//FIELDS FÜR POSTAUFRUF
		$postfields=array();
		$postfields["API"] = "shop";
		$postfields["APIRequest"] = "OrderItemAdd";
		$postfields["mode"] = "new";
		$postfields["order_id"] = $return["exchange_order_id"];
		$postfields["item_id"] = $_POST["exchange_shop_orders_item"];
		$postfields["amount"] = $_POST["exchange_shop_orders_item_amount"];
		$postfields["price"] = round($_POST["exchange_shop_orders_item_FCnetto"]*((UST/100)+1),2);
		$postfields["netto"] = $_POST["exchange_shop_orders_item_FCnetto"];
		$postfields["Currency_Code"] = $order_item["Currency_Code"];
		$postfields["exchange_rate_to_EUR"] = $order_item["exchange_rate_to_EUR"];

		$responseXML=post(PATH."soa2/", $postfields);

		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response->Ack[0]=="Success")
		{
			$exchange_orderitem_id=$response->id_orderItem[0];
		}
		else
		{
			show_error(0, 7, __FILE__, __LINE__, $responseXML);
			exit;
		}
		
		unset($response);
		unset($responseXML);

	}

	
	q("INSERT INTO shop_returns_items (
		return_id,
		shop_orders_items_id,
		item_id,
		amount,
		exchange_shop_orders_item,
		return_reason,
		return_reason_description,
		firstmod,
		firstmod_user,
		lastmod,
		lastmod_user
	) VALUES (
		".$_POST["return_id"].",
		".$_POST["shop_orders_items_id"].",
		".$order_item["item_id"].",
		".$_POST["amount"].",
		".$exchange_orderitem_id.",
		".$_POST["return_reason"].",
		'".mysqli_real_escape_string($dbshop, $_POST["return_reason_description"])."',
		".time().",
		".$_SESSION["id_user"].",
		".time().",
		".$_SESSION["id_user"]."
	);", $dbshop, __FILE__, __LINE__);
	
		$returnitem_id = mysqli_insert_id($dbshop);
		
	//ORDEREVENT SPEICHERN
	$data = "";
	$data.='<data>';
	$data.='	<return_id>'.$return_id.'</return_id>';
	$data.='	<item_id>'.$_POST["item_id"].'</state>';
	$data.='	<amount>'.$_POST["amount"].'</amount>';
	$data.='	<exchange_shop_orders_item>'.$exchange_orderitem_id.'</exchange_shop_orders_item>';
	$data.='	<return_reason>'.$_POST["return_reason"].'</return_reason>';
	$data.='	<return_reason_description><![CDATA['.$_POST["return_reason_description"].']]></return_reason_description>';
	$data.='	<firstmod>'.$_SESSION["id_user"].'</firstmod>';
	$data.='	<firstmod_user>'.time().'</firstmod_user>';
	$data.='	<lastmod>'.$_SESSION["id_user"].'</lastmod>';
	$data.='	<lastmod_user>'.time().'</lastmod_user>';
	$data.='</data>';

	$responseXML=post(PATH."soa2/", array("API" => "shop", "APIRequest" => "OrderEventSet", "order_id" => $return["order_id"], "eventtype_id" => 25, "data" => $data));

	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXML);
	}
	catch(Exception $e)
	{
		show_error(9756, 7, __FILE__, __LINE__, $responseXML);
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if ($response->Ack[0]!="Success")
	{
		show_error(9777, 7, __FILE__, __LINE__, $responseXML);
		exit;
	}

	//SERVICE RESPONSE
	echo '<returnitem_id>'.$returnitem_id.'</returnitem_id>'."\n";

?>