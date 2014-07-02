<?php

	$required=array("orderid" => "numericNN");
	check_man_params($required);

	$orders = array();
	

	
	//GET Order
	$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["orderid"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		show_error(9762, 7, __FILE__, __LINE__, "OrderID :".$_POST["orderid"]);	
		exit;
	}

	$orders[0] = mysqli_fetch_assoc($res_order);	

	//GET COMBINED
	if ($orders[0]["combined_with"]>0)
	{
		$res_order = q("SELECT * FROM shop_orders WHERE combined_with = ".$_POST["orderid"]." AND NOT id_order = ".$_POST["orderid"], $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_order)==0)
		{
			show_error(9838, 7, __FILE__, __LINE__, "OrderID :".$_POST["orderid"]);	
			exit;
		}
		while ($row_orders=mysqli_fetch_assoc($res_order))
		{
			$orders[sizeof($orders)]=$row_orders;
		}
	}

/*
	//GET SHOP DATA
	$res_shop=q("SELECT * FROM shop_shops WHERE id_shop = ".$orders[0], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_shop)==0)
	{
		show_error(9757, 7, __FILE__, __LINE__, "OrderID :".$orders[0]["id_order"]." SHOP_ID: ".$orders[0]["shop_id"]);	
		exit;
	}

	$shop = mysqli_fetch_assoc($res_shop);
*/
	
	//CHECK IF THERE IS SOMETHING TO CHANGE
	foreach ($orders as $index => $order)
	{		
		//if ($order["VAT"] != 0 && ($shop["shop_type"]==2 || $shop["shop_type"]==3))	//WENN VAT AUF 0 GESETZT -> Keine Preise anpassen ODER SHOP_TYPE NICHT IN (2,3)
		if ($order["VAT"] != 0)
		{
			if ($order["bill_country_code"]!="") // WENN COUNTRYCODE vorhanden -> SUCHE VAT IN shop_countries
			{
				$res_country_VAT = q("SELECT * FROM shop_countries WHERE country_code = '".$order["bill_country_code"]."' AND EU = 1 LIMIT 1", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_country_VAT)==0)
				{
					$VAT = 19;	
				}
				else
				{
					$row_country_VAT = mysqli_fetch_assoc($res_country_VAT);
					$VAT = $row_country_VAT["VAT"];
				}
				
				if ($order["shop_id"] == 4 || $order["shop_id"] == 2 || $order["shop_id"] == 6)
				{
					$VAT = 19;
				}
				
				if ($VAT == 0)
				{
					$VAT_multiplier = 1;
				}
				else
				{
					$VAT_multiplier = ($VAT/100)+1;
				}
			}
			// WENN UNTERSCHIEDLICHE VAT -> KORREKTUR
			if ($order["VAT"] != $VAT)
			{
				//KORREKTUR shipping_net
				$shipping_net = round($order["shipping_costs"] / $VAT_multiplier, 2);
				
				$datafield = array();
				$datafield["shipping_net"] = $shipping_net;
				$datafield["VAT"] = $VAT;
				
				q_update("shop_orders", $datafield, "WHERE id_order = ".$order["id_order"], $dbshop, __FILE__, __LINE__);
				
				//GET ITEMS OF ORDER
				$res_items = q("SELECT * FROM shop_orders_items WHERE order_id = ".$order["id_order"], $dbshop, __FILE__, __LINE__);
				while ($row_items = mysqli_fetch_assoc($res_items))
				{
					$net = round($row_items["price"] / $VAT_multiplier,2);
					
					$datafield = array();
					$datafield["netto"] = $net;
					
					q_update("shop_orders_items", $datafield, "WHERE id = ".$row_items["id"], $dbshop, __FILE__, __LINE__);

				}
				
				
			}
		}
	}
	
?>