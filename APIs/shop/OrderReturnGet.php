<?php

	//CHECK $_POST PARAMS
	check_man_params(array("return_id" => "numericNN"));
	
	//GET OrderReturn
	$res_return = q("SELECT * FROM shop_returns2 WHERE id_return = ".$_POST["return_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_return)==0)
	{
		show_error(9778, 7, __FILE__, __LINE__, "ReturnID: ".$_POST["return_id"]);
	}
	$return = mysqli_fetch_array($res_return);
	
	//GET OrderReturnItems
	$return_items=array();
	$res_return_items = q("SELECT * FROM shop_returns_items WHERE return_id = ".$return["id_return"].";", $dbshop, __FILE__, __LINE__);
	while ($row_return_items=mysqli_fetch_array($res_return_items))
	{
		$return_items[$row_return_items["id_returnitem"]] = $row_return_items;
	}
	
	//GET ORDER
	$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$return["order_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		show_error(9774, 7, __FILE__, __LINE__, "id_order: ".$return["order_id"]);
	}
	$order = mysqli_fetch_array($res_order);
	//GET ORDER_ITEMS
	$orderitems=array();
	$res_orderitems = q("SELECT * FROM shop_orders_items WHERE order_id = ".$order["id_order"].";", $dbshop, __FILE__, __LINE__);
	while ($row_orderitems = mysqli_fetch_array($res_orderitems))
	{
		//$orderitems[$row_orderitems["item_id"]]=$row_orderitems;
		$orderitems[$row_orderitems["id"]]=$row_orderitems;
	}
	
	//GET EXCHANGE_ORDER
	if ($return["exchange_order_id"]!=0)
	{
		$res_order_exchange = q("SELECT * FROM shop_orders WHERE id_order = ".$return["exchange_order_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_order_exchange)==0)
		{
			show_error(9774, 7, __FILE__, __LINE__, "id_order: ".$return["exchange_order_id"]);
		}
		$order_exchange = mysqli_fetch_array($res_order_exchange);
	}
	
	//GET EXCHANGE ORDERITEMS
	if (isset($order_exchange))
	{
		$res_orderitems_exchange = q("SELECT * FROM shop_orders_items WHERE order_id = ".$order_exchange["id_order"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_orderitems_exchange)>0)	
		{
			$order_items_exchange = array();
			while ($row_orderitems_exchange = mysqli_fetch_array($res_orderitems_exchange))
			{
				$order_items_exchange[$row_orderitems_exchange["id"]]=$row_orderitems_exchange;
			}
		}
		
	}


//PREPARE DATA FOR OUTPUT
	$item_id = array();
	if (sizeof($return_items)>0)
	{
		foreach ($return_items as $returnitem_id => $returnitem)
		{
			$item_id[]=$returnitem["item_id"];
			
		}
	}
	
	if (sizeof($orderitems)>0)
	{
		foreach ($orderitems as $id => $orderitem)
		{
			$item_id[]=$orderitem["item_id"];
		}
	}
	
	if (sizeof($order_items_exchange)>0)
	{
		foreach ($order_items_exchange as $id => $order_returnitem)
		{
			$item_id[]=$order_returnitem["item_id"];
		}
	}


	//GET MPN
	$MPN = array();
	if (sizeof($item_id)>0)
	{
		$res_MPN = q("SELECT id_item, MPN FROM shop_items WHERE id_item IN (".implode(",", $item_id).");", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_return)==0)
		{
			show_error(9779, 7, __FILE__, __LINE__, "ItemIDs: ".implode(",", $item_id));
		}
		while ($row_MPN = mysqli_fetch_array($res_MPN))
		{
			$MPN[$row_MPN["id_item"]] = $row_MPN["MPN"];
		}
	}
	
	// GET ITEMTITLE
	$itemtitle = array();
	if (sizeof($item_id)>0)
	{
		$res_title = q("SELECT id_item, title FROM shop_items_de WHERE id_item IN (".implode(",", $item_id).");", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_title)==0)
		{
			show_error(9780, 7, __FILE__, __LINE__, "ItemIDs: ".implode(",", $item_id));
		}
		while ($row_title = mysqli_fetch_array($res_title))
		{
			$itemtitle[$row_title["id_item"]] = $row_title["title"];
		}
	}
	
	//SERVICE RESPONSE
	$xmldata = '';
	$xmldata.= '<orderreturn>'."\n";
	
	//RETURN DATA
	while ( list ($key, $val) = each ($return))
	{
		if (!is_numeric($key)) $xmldata.= '<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
	}
	
	//KAUFDATUM DER ZUGRUNDELIEGENDEN ORDER
	$xmldata.= '<order_firstmod>'.$order["firstmod"].'</order_firstmod>'."\n";
	
	//RETURNITEM DATA
	$xmldata.= '	<returnitems>'."\n";
	foreach ($return_items as $returnitem_id => $returnitem)
	{
		$xmldata.= '		<returnitem>'."\n";
		while (list ($key, $val) = each ($returnitem))
		{
			if (!is_numeric($key)) $xmldata.= '		<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
		}
		
		$xmldata.= '		<MPN><![CDATA['.$MPN[$returnitem["item_id"]].']]></MPN>'."\n";
		$xmldata.= '		<title><![CDATA['.$itemtitle[$returnitem["item_id"]].']]></title>>'."\n";
		// DATEN AUS ORDER
		$xmldata.= '		<netto><![CDATA['.$orderitems[$returnitem["shop_orders_items_id"]]["netto"].']]></netto>>'."\n";
		$xmldata.= '		<price><![CDATA['.$orderitems[$returnitem["shop_orders_items_id"]]["price"].']]></price>>'."\n";
		$xmldata.= '		<Currency_Code><![CDATA['.$orderitems[$returnitem["shop_orders_items_id"]]["Currency_Code"].']]></Currency_Code>>'."\n";
		$xmldata.= '		<exchange_rate_to_EUR><![CDATA['.$orderitems[$returnitem["shop_orders_items_id"]]["exchange_rate_to_EUR"].']]></exchange_rate_to_EUR>'."\n";
		
		// UMTAUSCH ARTIKEL
		if ($return["return_type"]=="exchange")
		{
			if (isset($order_items_exchange))
			{
				$xmldata.='		<exchangeOrderItemID><![CDATA['.$order_items_exchange[$returnitem["exchange_shop_orders_item"]]["id"].']]></exchangeOrderItemID>'."\n";
				$xmldata.='		<exchangeMPN><![CDATA['.$MPN[$order_items_exchange[$returnitem["exchange_shop_orders_item"]]["item_id"]].']]></exchangeMPN>'."\n";
				$xmldata.='		<exchangetitle><![CDATA['.$itemtitle[$order_items_exchange[$returnitem["exchange_shop_orders_item"]]["item_id"]].']]></exchangetitle>'."\n";
				$xmldata.='		<exchangeamount><![CDATA['.$order_items_exchange[$returnitem["exchange_shop_orders_item"]]["amount"].']]></exchangeamount>'."\n";	
				$xmldata.='		<exchangenetto><![CDATA['.$order_items_exchange[$returnitem["exchange_shop_orders_item"]]["netto"].']]></exchangenetto>'."\n";	
				$xmldata.='		<exchangeprice><![CDATA['.$order_items_exchange[$returnitem["exchange_shop_orders_item"]]["price"].']]></exchangeprice>'."\n";	
				$xmldata.='		<exchangeCurrency_Code><![CDATA['.$order_items_exchange[$returnitem["item_id"]]["Currency_Code"].']]></exchangeCurrency_Code>>'."\n";
				$xmldata.='		<exchangeexchange_rate_to_EUR><![CDATA['.$order_items_exchange[$returnitem["item_id"]]["exchange_rate_to_EUR"].']]></exchangeexchange_rate_to_EUR>>'."\n";

			}
			else
			{
				$xmldata.='		<exchangeOrderItemID></exchangeOrderItemID>'."\n";
				$xmldata.='		<exchangeMPN></exchangeMPN>'."\n";
				$xmldata.='		<exchangetitle></exchangetitle>'."\n";
				$xmldata.='		<exchangeamount></exchangeamount>'."\n";	
				$xmldata.='		<exchangenetto></exchangenetto>'."\n";	
				$xmldata.='		<exchangeprice></exchangeprice>'."\n";	
				$xmldata.='		<exchangeCurrency_Code></exchangeCurrency_Code>'."\n";	
				$xmldata.='		<exchangeexchange_rate_to_EUR></exchangeexchange_rate_to_EUR>'."\n";	
			}
		}
		$xmldata.= '		</returnitem>'."\n";
	}
	$xmldata.= '	</returnitems>'."\n";
	$xmldata.= '</orderreturn>'."\n";

	echo $xmldata;



	/*
	$xmldata.= '	<return_id>'.$_POST["return_id"].'</return_id>'."\n";
	$xmldata.= '	<state><[CDATA['.$_POST["state"].']]></state>'."\n";
	$xmldata.= '	<shop_id>'.$_POST["shop_id"].'</shop_id>'."\n";
	$xmldata.= '	<order_id>'.$_POST["order_id"].'</order_id>'."\n";
	$xmldata.= '	<exchange_order_id>'.$_POST["exchange_order_id"].'</exchange_order_id>'."\n";
	$xmldata.= '	<invoice_id><[CDATA['.$_POST["invoice_id"].']]></invoice_id>'."\n";	
	$xmldata.= '	<return_type><[CDATA['.$_POST["return_type"].']]></return_type>'."\n";	
	$xmldata.= '	<date_return>'.$_POST["date_return"].'</date_return>'."\n";
	$xmldata.= '	<date_refund>'.$_POST["date_refund"].'</date_refund>'."\n";
	$xmldata.= '	<refund>'.$_POST["refund"].'</refund>'."\n";
	$xmldata.= '	<refund_shipment>'.$_POST["refund_shipment"].'</refund_shipment>'."\n";
	$xmldata.= '	<ebay_demand_closing1>'.$_POST["ebay_demand_closing1"].'</ebay_demand_closing1>'."\n";
	$xmldata.= '	<ebay_demand_closing2>'.$_POST["ebay_demand_closing2"].'</ebay_demand_closing2>'."\n";
	$xmldata.= '	<ebay_fee_refund>'.$_POST["ebay_fee_refund"].'</ebay_fee_refund>'."\n";
	$xmldata.= '	<firstmod>'.$_POST["firstmod"].'</firstmod>'."\n";
	$xmldata.= '	<firstmod_user>'.$_POST["firstmod_user"].'</firstmod_user>'."\n";
	$xmldata.= '	<lastmod>'.$_POST["lastmod"].'</lastmod>'."\n";
	$xmldata.= '	<date_refund>'.$_POST["exchange_order_id"].'</date_refund>'."\n";
	*/

?>