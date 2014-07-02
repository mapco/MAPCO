<?php

	check_man_params(array("order_id" => "numericNN"));


	//GET ORDER
	$response="";
	$responseXML="";
	
	$postfields=array();
	$postfields["API"]="shop";
	$postfields["Action"]="OrderGet";
	$postfields["id_order"]=$_POST["order_id"];
	
	$responseXML = post(PATH."soa/", $postfields);
	
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$order = new SimpleXMLElement($responseXML);
	}
	catch(Exception $e)
	{
		//XML FEHLERHAFT
		show_error(9756, 7, __FILE__, __LINE__, $responseXML);
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if ($order->Ack[0]!="Success")
	{
		show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($order, true).print_r($postfields, true));
		exit;
	}

	$VAT = (int)$order->VAT[0];
	
	if ($VAT == 0)
	{
		$VAT = 1;	
	}
	else
	{
		$VAT = ($VAT/100)+1;
	}
	
	// VERSANDKOSTEN ANPASSEN
	$ship_net = (float)$order->shipping_net[0];
	
	$shipping_costs = round($ship_net*$VAT,2);
	
	//UPDATE ORDER
	$postfields=array();
	$postfields["API"]="shop";
	$postfields["APIRequest"]="OrderUpdate";
	$postfields["SELECTOR_id_order"]=$_POST["order_id"];
	$postfields["shipping_costs"]=$shipping_costs;
	
	$response=soa2($postfields, __FILE__, __LINE__);
	if ($response->Ack[0]!="Success")
	{
		show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
		exit;
	}
	
	//UPDATE SHOP_ORDERS_ITEMS
	$i=0;
	while (isset($order->OrderItem[$i]))
	{
		$netto = (float)$order->OrderItem[$i]->netto[0];
		$price = round($netto*$VAT, 2);
		//UPDATE ORDERITEM
		$postfields=array();
		$postfields["API"]="shop";
		$postfields["APIRequest"]="OrderItemUpdate";
		$postfields["SELECTOR_id"]=(int)$order->OrderItem[$i]->id[0];
		$postfields["price"]=$price;
		$response_item =soa2($postfields, __FILE__, __LINE__);
		if ($response_item->Ack[0]!="Success")
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response_item, true).print_r($postfields, true));
		}
		
		$i++;
		
	}

?>