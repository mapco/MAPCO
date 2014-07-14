<?php

	require_once("mapco_gewerblich.php");

	/*
	
		$params["combined"] = [boolean] true (default)/ false => Komplette Order (kombiniert) oder nur die übergebene Order
		$params["calculation_base"] = [string] "net" / "gross" => Kalkulationsbasis Netto order Bruttopreise
										(default wird nach Order gesetzt: "net" bei Onlineshop-Orders && customer==gewerblich; "gross" alle anderen Fälle)
		$params["round"] = [boolean] true (default) / false => alle Werte runden
	*/

	function round_array($array)
	{
		foreach ($array as $index => $data)
		{
			if (is_array($data))
			{
				$array[$index] = round_array($data);	
			}
			else
			{
				if (is_numeric($data) && $index != "ExchangeRate")
				{
					$array[$index] = round($data,2);
				
				}
			}
		}
		return $array;
	}


	function OrderSumGet($order_id, $params = array())
	{
		
		
		global $dbshop;
		
		$gewerblich = false;
		
		$response = array();
		
		//OPTIONALER PARAMETER COMBINED
			if ( !isset( $params["combined"] ) )
			{
				$params["combined"] = true;
			}
			else
			//check for valid values
			{
				if ( $params["combined"] !== true && $params["combined"] !== false )
				{
					return false;	
				}
			}
		
		//OPTIONALER PARAMETER "calculation_base"
			if ( !isset( $params["calculation_base"] ) )
			{
				$params["calculation_base"] = "gross";
			}
			else
			//check for valid values
			{
				if ( $params["calculation_base"] != "gross" && $params["calculation_base"] != "net" )
				{
					return false;	
				}
			}

		//OPTIONALER PARAMETER "round"
			if ( !isset( $params["round"] ) )
			{
				$params["round"] = true;
			}
			else
			//check for valid values
			{
				if ( $params["round"] !== true && $params["round"] !== false )
				{
					return true;	
				}
			}


	// G E T   D A T A ===============================================================================================
		$order = array();
		$order_id_list = array();
		
		//GET ORDER
		$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows( $res_order ) == 0 )
		{
			return false;
		}
		
		$row_order = mysqli_fetch_assoc( $res_order );
		$order[$row_order["id_order"]] = $row_order;
		$order_id_list[0]=$row_order["id_order"];
		
		//WENN Order combiniert ist und PARAMS[combined] == true finde alle Orders
		if ( $order[$order_id]["combined_with"] > 0 &&  $params["combined"] )
		{
			$res_orders = q("SELECT * FROM shop_orders WHERE combined_with = ".$order[$order_id]["combined_with"], $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows( $res_orders ) == 0 )
			{
				return false;
			}
			
			$order_id_list = array();
			while ( $row_order = mysqli_fetch_assoc( $res_orders ) )
			{
				$order[$row_order["id_order"]] = $row_order;
				$order_id_list[sizeof($order_id_list)]=$row_order["id_order"];
			}
		}

		//GET ORDERS ITEMS
		$order_items = array();
		$order_items_list = array();
		$exrates = array();
		if (sizeof($order_id_list)>0)
		{
			$res_orders_items = q("SELECT * FROM shop_orders_items WHERE order_id IN (".implode(", ", $order_id_list).")", $dbshop, __FILE__, __LINE__);
			while ( $row_order_items = mysqli_fetch_assoc( $res_orders_items ) )
			{
				$order_items[$row_order_items["order_id"]][sizeof($order_items[$row_order_items["order_id"]])] = $row_order_items;	
				$exrates[$row_order_items["order_id"]] = $row_order_items["exchange_rate_to_EUR"];
				$order_items_list[sizeof($order_items_list)] = $row_order_items["item_id"];
				//FIX FOR EXCHANGE RATES 
					//WENN ZU EINER ORDER KEINE ITEMS VORHANDEN SIND
					$correction_exrate = $row_order_items["exchange_rate_to_EUR"];
			}
		}

		//FIX FOR EXCHANGE RATES 
				if (sizeof($order_id_list)>0 && sizeof($order_items) > 0)
				{
					foreach ( $order_id_list as $order_id)
					{
						if ( !isset( $exrates[$order_id] ) )
						{
							//show_error(9895, 7, __FILE__, __LINE__, false);
							$exrates[$order_id] = $correction_exrate;
						}
					}
				}
				//WENN ZU EINER GESAMTORDER KEINE ITEMS VORHANDEN SIND -> KEIN FEHLER
				elseif ( sizeof($order_id_list)>0 && sizeof($order_items) == 0 )
				{
					// GET SYSTEM EX_RATE
					foreach ($order as $order_id => $orderdata)
					{
						if ( !isset( $exrates[$order_id] ) )
						{
							$res_exrate = q("SELECT * FROM shop_currencies WHERE currency_code = '".$orderdata["Currency_Code"]."'", $dbshop, __FILE__, __LINE__);
							if ( mysqli_num_rows( $res_exrate ))
							{
								//show_error();
								exit;	
							}
							$row_exrate = mysqli_fetch_assoc( $res_exrate );
							$exrates[$order_id] = $row_exrate["exchange_rate_to_EUR"];
						}
					}
				}

		//GET ORDER_ITEMS DATA FOR COLLATERAL
		$items = array();
		if (sizeof($order_items_list)>0)
		{
			$res_items = q("SELECT * FROM shop_items WHERE id_item IN (".implode(", ", $order_items_list).")", $dbshop, __FILE__, __LINE__);
			while ($row_items = mysqli_fetch_assoc( $res_items ) )
			{
				$items[$row_items["id_item"]] = $row_items;	
			}
		}
		
		//ORDER CREDITS
		
		$credits = array();
		if (sizeof($order_id_list)>0)
		{
			$res_credits = q("SELECT * FROM shop_orders_credits WHERE order_id IN (".implode(",", $order_id_list).")", $dbshop, __FILE__, __LINE__);
			while($row_credits = mysqli_fetch_assoc($res_credits))
			{
				if (isset($credits[$row_credits["order_id"]]))
				{
					$credits[$row_credits["order_id"]][sizeof($credits[$row_credits["order_id"]])] = $row_credits;
				}
				else
				{
					$credits[$row_credits["order_id"]][0] = $row_credits;
				}
			}
		}
		
		
		//MEHRWERTSTEUER FESTLEGEN
		if ( $order[$order_id]["VAT"] == 0 )
		{
			$VAT_m = 1;
		}
		else
		{
			$VAT_m = ($order[$order_id]["VAT"]/100)+1;
		}
	//===============================================================================================================
		
	//CHECK FOR NETTO- oder BRUTTOPreisbasis
		// PRÜFUNG NUR, wenn NETTOPREISBASIS noch nicht gesetzt wurde
		if 	( $params["calculation_base"] == "gross" )
		{
			// IF Customer aus Order = 0 (nicht angemeldet) dann wird von Privatkunden ausgegangen => BRUTTOPREISBASIS
			if ( $order[$order_id]["customer_id"] != 0 )
			{
				$gewerblich = gewerblich($order[$order_id]["customer_id"]);
				
				// WENN KUNDE GEWERBLICH UND SHOP_TYPE == 1 (ONLINE-SHOP)
				if ($gewerblich)
				{
					$res_shop = q("SELECT * FROM shop_shops WHERE id_shop = ".$order[$order_id]["shop_id"], $dbshop, __FILE__, __LINE__);
					if ( mysqli_num_rows( $res_shop ) == 0 )
					{
						return false;
					}
					$shop = mysqli_fetch_assoc( $res_shop );
					if ( $shop["shop_type"] == 1)
					{
						$params["calculation_base"] = "net";
					}
				}
			}
		}
		
	//=================================================================================================================
		if ( $params["calculation_base"] == "net" )
		{
			//PROCESS DATA
			//ITEMS
				//FELDINITIALISIERUNG
				$response["OrderTotal"] ["NetFC"]							= 0;
				$response["OrderPositionsTotal"] ["NetFC"] 					= 0;
				$response["OrderPositionsTotal"] ["PositionsCount"]			= 0;
				$response["OrderPositionsTotal"] ["ItemsCount"] 			= 0;
				$response["OrderPositionsTotal"] ["CollateralCount"]		= 0;
				$response["OrderPositionsCollateral"] ["NetEUR"] 			= 0;
				$response["OrderPositionsCollateral"] ["CollateralCount"]	= 0;
				$response["OrderTotalWoCredits"] ["NetFC"]					= 0;
				$shipment_count_total = 0;
	
			foreach ($order_id_list as $orderid)
			{
				//FELDINITIALISIERUNG
				$response[$orderid]["OrderPositionsTotal"] ["NetFC"] 				= 0;
				$response[$orderid]["OrderPositionsTotal"] ["PositionsCount"]		= 0;
				$response[$orderid]["OrderPositionsTotal"] ["ItemsCount"] 			= 0;
				$response[$orderid]["OrderPositionsTotal"] ["CollateralCount"]		= 0;
				$response[$orderid]["OrderPositionsCollateral"] ["NetEUR"] 			= 0;
				$response[$orderid]["OrderPositionsCollateral"] ["CollateralCount"]	= 0;
	
				for ( $i=0; $i<sizeof($order_items[$orderid]); $i++ )
				{
					//response["OrderItems"][shop_orders_items.id]["NetFC|GrossFC|NetEUR|GrossEUR|TaxFC|TaxEUR|exchangerate|Currency"]
					
					$net = $order_items[$orderid][$i]["netto"];
					$amount = $order_items[$orderid][$i]["amount"];
					$ex = $exrates[$orderid];
					
					//SETZE COLLATERALWERT
					if ( isset($items[$order_items[$orderid][$i]["item_id"]]))
					{
						$coll_netEUR = $items[$order_items[$orderid][$i]["item_id"]]["collateral"];
					}
					else
					{
						$coll_netEUR = 0;
					}
					
					$coll_netFC = $coll_netEUR / $ex;
					
					
					$net_w_coll = $net + $coll_netFC;
					
					// BERECHNUNG OHNE "COLLATERAL ITEM"
					if ($order_items[$orderid][$i]["item_id"] != 28093)
					{
						//PRICES WITH COLLATERAL
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $net_w_coll;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $net_w_coll * $VAT_m;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $net_w_coll * ( $VAT_m-1 );
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		=  ($net / $ex) + $coll_netEUR;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= (($net / $ex) + $coll_netEUR) * $VAT_m;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= (($net / $ex) + $coll_netEUR) * ($VAT_m-1);
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $net_w_coll;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $net_w_coll * $VAT_m;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $net_w_coll * ( $VAT_m-1 );
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		=  ($net / $ex) + $coll_netEUR;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= (($net / $ex) + $coll_netEUR) * $VAT_m;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= (($net / $ex) + $coll_netEUR) * ($VAT_m-1);
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];


						//COLLATERAL
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $coll_netFC;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $coll_netFC * $VAT_m;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $coll_netFC * ( $VAT_m-1 );
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $coll_netEUR;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $coll_netEUR * $VAT_m;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $coll_netEUR * ($VAT_m-1);
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $coll_netFC;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $coll_netFC * $VAT_m;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $coll_netFC * ( $VAT_m-1 );
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $coll_netEUR;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $coll_netEUR * $VAT_m;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $coll_netEUR * ($VAT_m-1);
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

	
						//PRICES WITHOUT COLLATERAL				
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $net;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $net * $VAT_m;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $net * ( $VAT_m-1 );
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $net / $ex;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $net / $ex * $VAT_m;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $net / $ex * ($VAT_m-1);
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $net;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $net * $VAT_m;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $net * ( $VAT_m-1 );
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $net / $ex;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $net / $ex * $VAT_m;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $net / $ex * ($VAT_m-1);
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

	
						//POSITIONS WITH COLLATERAL
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $net_w_coll * $amount;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $net_w_coll * $amount * $VAT_m;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $net_w_coll * $amount * ($VAT_m-1);
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $net_w_coll * $amount / $ex;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $net_w_coll * $amount / $ex * $VAT_m;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $net_w_coll * $amount / $ex * ($VAT_m-1);
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["Amount"] 		= $amount;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];
	
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $net_w_coll * $amount;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $net_w_coll * $amount * $VAT_m;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $net_w_coll * $amount * ($VAT_m-1);
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $net_w_coll * $amount / $ex;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $net_w_coll * $amount / $ex * $VAT_m;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $net_w_coll * $amount / $ex * ($VAT_m-1);
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["Amount"] 		= $amount;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];


						//POSITIONS COLLATERAL
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $coll_netFC * $amount;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $coll_netFC * $amount * $VAT_m;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $coll_netFC * $amount * ( $VAT_m-1 );
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $coll_netEUR * $amount;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $coll_netEUR * $amount * $VAT_m;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $coll_netEUR * $amount * ($VAT_m-1);
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $coll_netFC * $amount;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $coll_netFC * $amount * $VAT_m;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $coll_netFC * $amount * ( $VAT_m-1 );
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $coll_netEUR * $amount;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"]	 	= $coll_netEUR * $amount * $VAT_m;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $coll_netEUR * $amount * ($VAT_m-1);
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];
	
	
						//POSITIONS WITHOUT COLLATERAL				
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $net * $amount;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $net * $amount * $VAT_m;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $net * $amount * ( $VAT_m-1 );
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $net * $amount / $ex;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $net * $amount / $ex * $VAT_m;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $net * $amount / $ex * ($VAT_m-1);
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];
	
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $net * $amount;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $net * $amount * $VAT_m;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $net * $amount * ( $VAT_m-1 );
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $net * $amount / $ex;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $net * $amount / $ex * $VAT_m;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $net * $amount / $ex * ($VAT_m-1);
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

							
						//SUMMEN
						$response[$orderid]["OrderPositionsTotal"] ["NetFC"] 			+= $net_w_coll * $amount;
						$response[$orderid]["OrderPositionsTotal"] ["GrossFC"] 			= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] * $VAT_m;
						$response[$orderid]["OrderPositionsTotal"] ["TaxFC"] 			= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] * ($VAT_m-1);
						$response[$orderid]["OrderPositionsTotal"] ["NetEUR"] 			= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] / $ex;
						$response[$orderid]["OrderPositionsTotal"] ["GrossEUR"] 		= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] / $ex * $VAT_m;
						$response[$orderid]["OrderPositionsTotal"] ["TaxEUR"] 			= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] / $ex * ($VAT_m-1);
						$response[$orderid]["OrderPositionsTotal"] ["ExchangeRate"] 	= $ex;
						$response[$orderid]["OrderPositionsTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

				
						$response[$orderid]["OrderCollateralTotal"] ["NetEUR"] 			+= $coll_netEUR * $amount;
						$response[$orderid]["OrderCollateralTotal"] ["GrossEUR"] 		= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $VAT_m;
						$response[$orderid]["OrderCollateralTotal"] ["TaxEUR"] 			= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * ($VAT_m-1);
						$response[$orderid]["OrderCollateralTotal"] ["NetFC"] 			= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $ex;
						$response[$orderid]["OrderCollateralTotal"] ["GrossFC"] 		= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $ex * $VAT_m;
						$response[$orderid]["OrderCollateralTotal"] ["TaxFC"] 			= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $ex * ($VAT_m-1);
						$response[$orderid]["OrderCollateralTotal"] ["ExchangeRate"] 	= $ex;
						$response[$orderid]["OrderCollateralTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
	
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] 		+= $net * $amount;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] 		= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] * $VAT_m;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["TaxFC"] 		= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] * ($VAT_m-1);
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetEUR"] 		= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] / $ex ;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossEUR"] 	= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] / $ex * $VAT_m;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["TaxEUR"] 		= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] / $ex * ($VAT_m-1);
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["ExchangeRate"] = $ex;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["Currency_Code"]= $order[$orderid]["Currency_Code"];
	
						
						//COUNTS
						$response[$orderid]["OrderPositionsTotal"] ["PositionsCount"] ++;
						$response[$orderid]["OrderPositionsTotal"] ["ItemsCount"] 		+= $amount;
						if ($coll_netEUR != 0)
						{
							$response[$orderid]["OrderPositionsTotal"] ["CollateralCount"] += $amount;
						}
						$response[$orderid]["OrderPositionsTotal"] ["Currency_Code"] = $order[$orderid]["Currency_Code"];
						$response[$orderid]["OrderPositionsTotal"] ["Currency_Code"] = $order[$orderid]["Currency_Code"];
	
						$response["OrderPositionsTotal"] ["PositionsCount"] ++;
						$response["OrderPositionsTotal"] ["ItemsCount"] 		+= $amount;
						if ($coll_netEUR != 0)
						{
							$response["OrderPositionsTotal"] ["CollateralCount"] += $amount;
						}
					
					}
				} // ITEMS
			
				$response["OrderPositionsTotal"] ["NetFC"] 			+= $response[$orderid]["OrderPositionsTotal"] ["NetFC"];
				$response["OrderPositionsTotal"] ["GrossFC"] 		+= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] * $VAT_m;
				$response["OrderPositionsTotal"] ["TaxFC"] 			+= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] * ($VAT_m-1);
				$response["OrderPositionsTotal"] ["NetEUR"] 		+= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] / $ex;
				$response["OrderPositionsTotal"] ["GrossEUR"] 		+= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] / $ex * $VAT_m;
				$response["OrderPositionsTotal"] ["TaxEUR"] 		+= $response[$orderid]["OrderPositionsTotal"] ["NetFC"] / $ex * ($VAT_m-1);
				$response["OrderPositionsTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

				$response["OrderCollateralTotal"] ["NetEUR"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"];
				$response["OrderCollateralTotal"] ["GrossEUR"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $VAT_m;
				$response["OrderCollateralTotal"] ["TaxEUR"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * ($VAT_m-1);
				$response["OrderCollateralTotal"] ["NetFC"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $ex;
				$response["OrderCollateralTotal"] ["GrossFC"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $ex * $VAT_m;
				$response["OrderCollateralTotal"] ["TaxFC"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $ex * ($VAT_m-1);
				$response["OrderCollateralTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];


				$response["OrderPositionsTotalWoCollateral"] ["NetFC"] 			+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"];
				$response["OrderPositionsTotalWoCollateral"] ["GrossFC"] 		+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] * $VAT_m;
				$response["OrderPositionsTotalWoCollateral"] ["TaxFC"] 			+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] * ($VAT_m-1);
				$response["OrderPositionsTotalWoCollateral"] ["NetEUR"] 		+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] / $ex ;
				$response["OrderPositionsTotalWoCollateral"] ["GrossEUR"] 		+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] / $ex * $VAT_m;
				$response["OrderPositionsTotalWoCollateral"] ["TaxEUR"] 		+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] / $ex * ($VAT_m-1);
				$response["OrderPositionsTotalWoCollateral"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

				//SHIPPING COSTS
				$ex = $exrates[$orderid];
				
				//SHIPMENT COUNT
				if ($order[$orderid]["shipping_net"] != 0)
				{
					$shipment_count = 1;
				}
				else
				{
					$shipment_count = 0;
				}
				
				$shipment_count_total += $shipment_count;
				
				
				//ORDER CREDITS
				$credit_net = 0;
				$credit_count = array();
				for ($i = 0; $i<sizeof($credits[$orderid]); $i++)
				{
					$credit_net += $credits[$orderid][$i]["netto"];
					if (!isset($credit_count[$orderid]))
					{
						$credit_count[$orderid] = 0;
					}
					if (!isset($credit_count["all"]))
					{
						$credit_count["all"] = 0;
					}

					$credit_count[$orderid] ++;
					$credit_count["all"] ++;
				}
				$response[$orderid]["OrderCredits"] ["NetEUR"] 			= $credit_net;
				$response[$orderid]["OrderCredits"] ["GrossEUR"]		= $credit_net * $VAT_m;
				$response[$orderid]["OrderCredits"] ["TaxEUR"]			= $credit_net * ( $VAT_m-1 );
				$response[$orderid]["OrderCredits"] ["NetFC"] 			= $credit_net * $ex;
				$response[$orderid]["OrderCredits"] ["GrossFC"] 		= $credit_net * $ex * $VAT_m;
				$response[$orderid]["OrderCredits"] ["TaxFC"] 			= $credit_net * $ex * ( $VAT_m-1 );
				$response[$orderid]["OrderCredits"] ["ItemsCount"]		= $credit_count[$orderid];
				$response[$orderid]["OrderCredits"] ["ExchangeRate"]	= $ex;
				$response[$orderid]["OrderCredits"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
	
				//SHIPPING COSTS
				$response[$orderid]["ShippingCosts"] ["NetFC"] 			= $order[$orderid]["shipping_net"];
				$response[$orderid]["ShippingCosts"] ["GrossFC"] 		= $response[$orderid]["ShippingCosts"] ["NetFC"] * $VAT_m;
				$response[$orderid]["ShippingCosts"] ["TaxFC"] 			= $response[$orderid]["ShippingCosts"] ["NetFC"] * ( $VAT_m-1 );
				$response[$orderid]["ShippingCosts"] ["NetEUR"] 		= $response[$orderid]["ShippingCosts"] ["NetFC"] / $ex;
				$response[$orderid]["ShippingCosts"] ["GrossEUR"]		= $response[$orderid]["ShippingCosts"] ["NetFC"] / $ex * $VAT_m;
				$response[$orderid]["ShippingCosts"] ["TaxEUR"]			= $response[$orderid]["ShippingCosts"] ["NetFC"] / $ex * ( $VAT_m-1 );
				$response[$orderid]["ShippingCosts"] ["ItemsCount"]		= $shipment_count;
				$response[$orderid]["ShippingCosts"] ["ExchangeRate"]	= $ex;
				$response[$orderid]["ShippingCosts"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
	
				//TOTALS for Order
				$response[$orderid]["OrderTotal"] ["NetFC"] 			=  $response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"];
				$response[$orderid]["OrderTotal"] ["GrossFC"]			= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) * $VAT_m;
				$response[$orderid]["OrderTotal"] ["TaxFC"]				= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) * ( $VAT_m-1 );
				$response[$orderid]["OrderTotal"] ["NetEUR"] 			= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) / $ex;
				$response[$orderid]["OrderTotal"] ["GrossEUR"]			= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) / $ex * $VAT_m;
				$response[$orderid]["OrderTotal"] ["TaxEUR"]			= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) / $ex * ( $VAT_m-1 );
				$response[$orderid]["OrderTotal"] ["PositionsCount"]	= $response[$orderid]["OrderPositionsTotal"]["PositionsCount"];
				$response[$orderid]["OrderTotal"] ["ItemsCount"]		= $response[$orderid]["OrderPositionsTotal"]["ItemsCount"];
				$response[$orderid]["OrderTotal"] ["CollateralCount"]	= $response[$orderid]["OrderPositionsTotal"]["CollateralCount"];
				$response[$orderid]["OrderTotal"] ["ExchangeRate"]		= $response[$orderid]["OrderPositionsTotal"]["ExchangeRate"];
				$response[$orderid]["OrderTotal"] ["Currency_Code"]		= $response[$orderid]["OrderPositionsTotal"]["Currency_Code"];
	
				
				$response[$orderid]["OrderTotalWoCollateral"] ["NetFC"] 			=  $response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"];
				$response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]			= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"]) * $VAT_m;
				$response[$orderid]["OrderTotalWoCollateral"] ["TaxFC"]				= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"]) * ( $VAT_m-1 );
				$response[$orderid]["OrderTotalWoCollateral"] ["NetEUR"] 			= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"]) / $ex;
				$response[$orderid]["OrderTotalWoCollateral"] ["GrossEUR"]			= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"]) / $ex * $VAT_m;
				$response[$orderid]["OrderTotalWoCollateral"] ["TaxEUR"]			= ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"]) / $ex * ( $VAT_m-1 );
				$response[$orderid]["OrderTotalWoCollateral"] ["PositionsCount"]	=  $response[$orderid]["OrderPositionsTotal"]["PositionsCount"];
				$response[$orderid]["OrderTotalWoCollateral"] ["ItemsCount"]		=  $response[$orderid]["OrderPositionsTotal"]["ItemsCount"];
				$response[$orderid]["OrderTotalWoCollateral"] ["CollateralCount"]	=  $response[$orderid]["OrderPositionsTotal"]["CollateralCount"];
				$response[$orderid]["OrderTotalWoCollateral"] ["ExchangeRate"]		=  $response[$orderid]["OrderPositionsTotal"]["ExchangeRate"];
				$response[$orderid]["OrderTotalWoCollateral"] ["Currency_Code"]		=  $response[$orderid]["OrderPositionsTotal"]["Currency_Code"];
	
				//TOTAL ALL ORDERS
				$response["OrderCredits"] ["NetEUR"] 		+= $credit_net;
				$response["OrderCredits"] ["GrossEUR"]		+= $credit_net * $VAT_m;
				$response["OrderCredits"] ["TaxEUR"]		+= $credit_net * ( $VAT_m-1 );
				$response["OrderCredits"] ["NetFC"] 		+= $credit_net * $ex;
				$response["OrderCredits"] ["GrossFC"] 		+= $credit_net * $ex * $VAT_m;
				$response["OrderCredits"] ["TaxFC"] 		+= $credit_net * $ex * ( $VAT_m-1 );
				$response["OrderCredits"] ["ItemsCount"]	= $credit_count["all"];
				$response["OrderCredits"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

				$response["ShippingCosts"] ["NetFC"] 		+= $order[$orderid]["shipping_net"];
				$response["ShippingCosts"] ["GrossFC"] 		+= $order[$orderid]["shipping_net"] * $VAT_m;
				$response["ShippingCosts"] ["TaxFC"] 		+= $order[$orderid]["shipping_net"] * ( $VAT_m-1 );
				$response["ShippingCosts"] ["NetEUR"] 		+= $order[$orderid]["shipping_net"] / $ex;
				$response["ShippingCosts"] ["GrossEUR"]		+= $order[$orderid]["shipping_net"] / $ex * $VAT_m;
				$response["ShippingCosts"] ["TaxEUR"]		+= $order[$orderid]["shipping_net"] / $ex * ( $VAT_m-1 );
				$response["ShippingCosts"] ["ItemsCount"]	= $shipment_count_total;
				$response["ShippingCosts"] ["Currency_Code"]= $order[$orderid]["Currency_Code"];
	
				$response["OrderTotal"] ["NetFC"] 			+=  $response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"];
				$response["OrderTotal"] ["GrossFC"]			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) * $VAT_m;
				$response["OrderTotal"] ["TaxFC"]			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) * ( $VAT_m-1 );
				$response["OrderTotal"] ["NetEUR"] 			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) / $ex;
				$response["OrderTotal"] ["GrossEUR"]		+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) / $ex * $VAT_m;
				$response["OrderTotal"] ["TaxEUR"]			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"]) / $ex * ( $VAT_m-1 );
				$response["OrderTotal"] ["PositionsCount"]	= $response["OrderPositionsTotal"] ["PositionsCount"];
				$response["OrderTotal"] ["ItemsCount"]		= $response["OrderPositionsTotal"] ["ItemsCount"];
				$response["OrderTotal"] ["CollateralCount"]	= $response["OrderPositionsTotal"] ["CollateralCount"];
				$response["OrderTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
	
				$response["OrderTotalWoCollateral"] ["NetFC"] 			+=  $response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderWoCollateralTotal"] ["NetFC"];
				$response["OrderTotalWoCollateral"] ["GrossFC"]			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderWoCollateralTotal"] ["NetFC"]) * $VAT_m;
				$response["OrderTotalWoCollateral"] ["TaxFC"]			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderWoCollateralTotal"] ["NetFC"]) * ( $VAT_m-1 );
				$response["OrderTotalWoCollateral"] ["NetEUR"] 			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderWoCollateralTotal"] ["NetFC"]) / $ex;
				$response["OrderTotalWoCollateral"] ["GrossEUR"]		+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderWoCollateralTotal"] ["NetFC"]) / $ex * $VAT_m;
				$response["OrderTotalWoCollateral"] ["TaxEUR"]			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderWoCollateralTotal"] ["NetFC"]) / $ex * ( $VAT_m-1 );
				$response["OrderTotalWoCollateral"] ["PositionsCount"]	= $response["OrderPositionsTotal"] ["PositionsCount"];
				$response["OrderTotalWoCollateral"] ["ItemsCount"]		= $response["OrderPositionsTotal"] ["ItemsCount"];
				$response["OrderTotalWoCollateral"] ["CollateralCount"]	= $response["OrderPositionsTotal"] ["CollateralCount"];
				$response["OrderTotalWoCollateral"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

				$response["OrderTotalWoCredits"] ["NetFC"] 			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"] - $response[$orderid]["OrderCredits"] ["NetFC"]);
				$response["OrderTotalWoCredits"] ["GrossFC"]		+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"] - $response[$orderid]["OrderCredits"] ["NetFC"]) * $VAT_m;
				$response["OrderTotalWoCredits"] ["TaxFC"]			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"] - $response[$orderid]["OrderCredits"] ["NetFC"]) * ( $VAT_m-1 );
				$response["OrderTotalWoCredits"] ["NetEUR"] 		+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"] - $response[$orderid]["OrderCredits"] ["NetFC"]) / $ex;
				$response["OrderTotalWoCredits"] ["GrossEUR"]		+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"] - $response[$orderid]["OrderCredits"] ["NetFC"]) / $ex * $VAT_m;
				$response["OrderTotalWoCredits"] ["TaxEUR"]			+=  ($response[$orderid]["ShippingCosts"] ["NetFC"] + $response[$orderid]["OrderPositionsTotal"] ["NetFC"] - $response[$orderid]["OrderCredits"] ["NetFC"]) / $ex * ( $VAT_m-1 );
				$response["OrderTotalWoCredits"] ["PositionsCount"]	= $response["OrderPositionsTotal"] ["PositionsCount"];
				$response["OrderTotalWoCredits"] ["ItemsCount"]		= $response["OrderPositionsTotal"] ["ItemsCount"];
				$response["OrderTotalWoCredits"] ["CollateralCount"]= $response["OrderPositionsTotal"] ["CollateralCount"];
				$response["OrderTotalWoCredits"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

			} // foreach ($order_id_list as $orderid)
			
		}

//***************************************************************************************************************

		if ( $params["calculation_base"] == "gross" )
		{
			//PROCESS DATA
			//ITEMS
				//FELDINITIALISIERUNG
				$response["OrderTotal"] ["GrossFC"]							= 0;
				$response["OrderPositionsTotal"] ["GrossFC"] 				= 0;
				$response["OrderPositionsTotal"] ["PositionsCount"]			= 0;
				$response["OrderPositionsTotal"] ["ItemsCount"] 			= 0;
				$response["OrderPositionsTotal"] ["CollateralCount"]		= 0;
				$response["OrderPositionsCollateral"] ["GrossEUR"] 			= 0;
				$response["OrderPositionsCollateral"] ["CollateralCount"]	= 0;
	
				$shipment_count_total = 0;
	
			foreach ($order_id_list as $orderid)
			{
				//FELDINITIALISIERUNG
				$response[$orderid]["OrderPositionsTotal"] ["GrossFC"] 				= 0;
				$response[$orderid]["OrderPositionsTotal"] ["PositionsCount"]		= 0;
				$response[$orderid]["OrderPositionsTotal"] ["ItemsCount"] 			= 0;
				$response[$orderid]["OrderPositionsTotal"] ["CollateralCount"]		= 0;
				$response[$orderid]["OrderPositionsCollateral"] ["GrossEUR"] 		= 0;
				$response[$orderid]["OrderPositionsCollateral"] ["CollateralCount"]	= 0;
	
				for ( $i=0; $i<sizeof($order_items[$orderid]); $i++ )
				{
					//response["OrderItems"][shop_orders_items.id]["NetFC|GrossFC|NetEUR|GrossEUR|TaxFC|TaxEUR|exchangerate|Currency"]
					
					$gross = $order_items[$orderid][$i]["price"];
					$amount = $order_items[$orderid][$i]["amount"];
					$ex = $exrates[$orderid];
					
					//SETZE COLLATERALWERT
					if ( isset($items[$order_items[$orderid][$i]["item_id"]]))
					{
						$coll_netEUR = $items[$order_items[$orderid][$i]["item_id"]]["collateral"];
					}
					else
					{
						$coll_netEUR = 0;
					}
					
					$coll_netFC = $coll_netEUR / $ex;
					
					
					$gross_w_coll = $gross + $coll_netFC * $VAT_m;
					
					// BERECHNUNG OHNE "COLLATERAL ITEM"
					if ($order_items[$orderid][$i]["item_id"] != 28093)
					{
						//PRICES WITH COLLATERAL
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $gross_w_coll / $VAT_m;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $gross_w_coll;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $gross_w_coll / ($VAT_m * 100 ) * ($VAT_m*100-100);
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $gross_w_coll / $ex / $VAT_m;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $gross_w_coll / $ex;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $gross_w_coll / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderItems"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $gross_w_coll / $VAT_m;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $gross_w_coll;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $gross_w_coll / ($VAT_m * 100 ) * ($VAT_m*100-100);
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $gross_w_coll / $ex / $VAT_m;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $gross_w_coll / $ex;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $gross_w_coll / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderItems"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

	
						//COLLATERAL
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $coll_netFC;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $coll_netFC * $VAT_m;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $coll_netFC * ( $VAT_m-1 );
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $coll_netEUR;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $coll_netEUR * $VAT_m;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $coll_netEUR * ($VAT_m-1);
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];
	
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $coll_netFC;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $coll_netFC * $VAT_m;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $coll_netFC * ( $VAT_m-1 );
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $coll_netEUR;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $coll_netEUR * $VAT_m;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $coll_netEUR * ($VAT_m-1);
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderItemsCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

	
						//PRICES WITHOUT COLLATERAL				
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $gross / $VAT_m;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $gross;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $gross / ($VAT_m * 100 ) * ($VAT_m*100-100);
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $gross / $VAT_m / $ex;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $gross / $ex;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $gross / ($VAT_m * 100 ) * ($VAT_m*100-100) / $ex;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $gross / $VAT_m;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $gross;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $gross / ($VAT_m * 100 ) * ($VAT_m*100-100);
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $gross / $VAT_m / $ex;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $gross / $ex;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $gross / ($VAT_m * 100 ) * ($VAT_m*100-100) / $ex;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderItemsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];


						//POSITIONS WITH COLLATERAL
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $gross_w_coll / $VAT_m * $amount;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $gross_w_coll * $amount;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $gross_w_coll / ($VAT_m * 100 ) * ($VAT_m*100-100) * $amount;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $gross_w_coll / $ex / $VAT_m * $amount;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $gross_w_coll / $ex * $amount;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $gross_w_coll / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100) * $amount;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["Amount"] 		= $amount;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $gross_w_coll / $VAT_m * $amount;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $gross_w_coll * $amount;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $gross_w_coll / ($VAT_m * 100 ) * ($VAT_m*100-100) * $amount;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $gross_w_coll / $ex / $VAT_m * $amount;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $gross_w_coll / $ex * $amount;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $gross_w_coll / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100) * $amount;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["Amount"] 		= $amount;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderPositions"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];
	
						//POSITIONS COLLATERAL
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $coll_netFC * $amount;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $coll_netFC*$amount*$VAT_m;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $coll_netFC*$amount*( $VAT_m-1 );
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $coll_netEUR*$amount;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $coll_netEUR*$amount*$VAT_m;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $coll_netEUR*$amount*($VAT_m-1);
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $coll_netFC * $amount;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $coll_netFC*$amount*$VAT_m;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $coll_netFC*$amount*( $VAT_m-1 );
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $coll_netEUR*$amount;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $coll_netEUR*$amount*$VAT_m;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $coll_netEUR*$amount*($VAT_m-1);
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderPositionsCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

	
						//POSITIONS WITHOUT COLLATERAL				
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 			= $gross / $VAT_m * $amount;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $gross * $amount;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 			= $gross / ($VAT_m * 100 ) * ($VAT_m*100-100) * $amount;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $gross / $VAT_m / $ex * $amount;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 		= $gross / $ex * $amount;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $gross / ($VAT_m * 100 ) * ($VAT_m*100-100) / $ex * $amount;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response[$orderid]["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];

						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetFC"] 		= $gross / $VAT_m * $amount;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossFC"] 		= $gross * $amount;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxFC"] 		= $gross / ($VAT_m * 100 ) * ($VAT_m*100-100) * $amount;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["NetEUR"] 		= $gross / $VAT_m / $ex * $amount;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["GrossEUR"] 	= $gross / $ex * $amount;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["TaxEUR"] 		= $gross / ($VAT_m * 100 ) * ($VAT_m*100-100) / $ex * $amount;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["ExchangeRate"]	= $ex;
						$response["OrderPositionsWoCollateral"] [$order_items[$orderid][$i]["id"]] ["Currency"]		= $order[$orderid]["Currency_Code"];
	
							
						//SUMMEN
						$response[$orderid]["OrderPositionsTotal"] ["GrossFC"] 			+= $gross_w_coll * $amount;
						$response[$orderid]["OrderPositionsTotal"] ["NetFC"] 			= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / $VAT_m;
						$response[$orderid]["OrderPositionsTotal"] ["TaxFC"] 			= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / ($VAT_m * 100 ) * ($VAT_m*100-100);
						$response[$orderid]["OrderPositionsTotal"] ["GrossEUR"] 		= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / $ex;
						$response[$orderid]["OrderPositionsTotal"] ["NetEUR"] 			= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / $VAT_m / $ex;
						$response[$orderid]["OrderPositionsTotal"] ["TaxEUR"] 			= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / ($VAT_m * 100 ) * ($VAT_m*100-100) / $ex;
						$response[$orderid]["OrderPositionsTotal"] ["ExchangeRate"] 	= $ex;
						$response[$orderid]["OrderPositionsTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

						$response[$orderid]["OrderCollateralTotal"] ["NetEUR"] 			+= $coll_netEUR * $amount;
						$response[$orderid]["OrderCollateralTotal"] ["GrossEUR"] 		= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $VAT_m;
						$response[$orderid]["OrderCollateralTotal"] ["TaxEUR"] 			= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * ($VAT_m-1);
						$response[$orderid]["OrderCollateralTotal"] ["GrossFC"] 		= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] / $ex * $VAT_m;
						$response[$orderid]["OrderCollateralTotal"] ["NetFC"] 			= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] / $ex;
						$response[$orderid]["OrderCollateralTotal"] ["TaxFC"] 			= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] / $ex * ($VAT_m-1);
						$response[$orderid]["OrderCollateralTotal"] ["ExchangeRate"] 	= $ex;
						$response[$orderid]["OrderCollateralTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] 		+= $gross * $amount;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetFC"] 		= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / $VAT_m;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["TaxFC"] 		= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / ($VAT_m * 100 ) * ($VAT_m*100-100);
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossEUR"] 	= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / $ex;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["NetEUR"] 		= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / $ex / $VAT_m;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["TaxEUR"] 		= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["ExchangeRate"] = $ex;
						$response[$orderid]["OrderPositionsTotalWoCollateral"] ["Currency_Code"]= $order[$orderid]["Currency_Code"];
	
						
						//COUNTS
						$response[$orderid]["OrderPositionsTotal"] ["PositionsCount"] ++;
						$response[$orderid]["OrderPositionsTotal"] ["ItemsCount"] 		+= $amount;
						if ($coll_netEUR != 0)
						{
							$response[$orderid]["OrderPositionsTotal"] ["CollateralCount"] += $amount;
						}
						$response[$orderid]["OrderPositionsTotal"] ["Currency_Code"] = $order[$orderid]["Currency_Code"];
						$response[$orderid]["OrderPositionsTotal"] ["Currency_Code"] = $order[$orderid]["Currency_Code"];
	
						$response["OrderPositionsTotal"] ["PositionsCount"] ++;
						$response["OrderPositionsTotal"] ["ItemsCount"] 		+= $amount;
						if ($coll_netEUR != 0)
						{
							$response["OrderPositionsTotal"] ["CollateralCount"] += $amount;
						}
					
					}
				} // ITEMS


				$response["OrderPositionsTotal"] ["GrossFC"] 		+= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"];
				$response["OrderPositionsTotal"] ["NetFC"] 			+= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / $VAT_m;
				$response["OrderPositionsTotal"] ["TaxFC"] 			+= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderPositionsTotal"] ["GrossEUR"] 		+= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / $ex;
				$response["OrderPositionsTotal"] ["NetEUR"] 		+= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / $VAT_m / $ex;
				$response["OrderPositionsTotal"] ["TaxEUR"] 		+= $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] / ($VAT_m * 100 ) * ($VAT_m*100-100) / $ex;
				$response["OrderPositionsTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
	
				$response["OrderCollateralTotal"] ["NetEUR"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"];
				$response["OrderCollateralTotal"] ["GrossEUR"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * $VAT_m;
				$response["OrderCollateralTotal"] ["TaxEUR"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] * ($VAT_m-1);
				$response["OrderCollateralTotal"] ["GrossFC"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] / $ex * $VAT_m;
				$response["OrderCollateralTotal"] ["NetFC"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] / $ex;
				$response["OrderCollateralTotal"] ["TaxFC"] 		+= $response[$orderid]["OrderCollateralTotal"] ["NetEUR"] / $ex * ($VAT_m-1);
				$response["OrderCollateralTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
		
				$response["OrderPositionsTotalWoCollateral"] ["GrossFC"] 		+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"];
				$response["OrderPositionsTotalWoCollateral"] ["NetFC"] 			+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / $VAT_m;
				$response["OrderPositionsTotalWoCollateral"] ["TaxFC"] 			+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderPositionsTotalWoCollateral"] ["GrossEUR"] 		+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / $ex;
				$response["OrderPositionsTotalWoCollateral"] ["NetEUR"] 		+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / $ex / $VAT_m;
				$response["OrderPositionsTotalWoCollateral"] ["TaxEUR"] 		+= $response[$orderid]["OrderPositionsTotalWoCollateral"] ["GrossFC"] / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderPositionsTotalWoCollateral"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];


				//SHIPPING COSTS
				$ex = $exrates[$orderid];
				
				//SHIPMENT COUNT
				if ($order[$orderid]["shipping_net"] != 0)
				{
					$shipment_count = 1;
				}
				else
				{
					$shipment_count = 0;
				}
				
				$shipment_count_total += $shipment_count;

				//ORDER CREDITS
				$credit_gross = 0;
				$credit_count = array();
				for ($i = 0; $i<sizeof($credits[$orderid]); $i++)
				{
					$credit_gross += $credits[$orderid][$i]["brutto"];
					if (!isset($credit_count[$orderid]))
					{
						$credit_count[$orderid] = 0;
					}
					if (!isset($credit_count["all"]))
					{
						$credit_count["all"] = 0;
					}

					$credit_count[$orderid] ++;
					$credit_count["all"] ++;
				}
				$response[$orderid]["OrderCredits"] ["GrossEUR"]		= $credit_gross;
				$response[$orderid]["OrderCredits"] ["NetEUR"] 			= $credit_gross / $VAT_m;
				$response[$orderid]["OrderCredits"] ["TaxEUR"]			= $credit_gross / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response[$orderid]["OrderCredits"] ["GrossFC"] 		= $credit_gross / $ex;
				$response[$orderid]["OrderCredits"] ["NetFC"] 			= $credit_gross / $ex / $VAT_m;
				$response[$orderid]["OrderCredits"] ["TaxFC"] 			= $credit_gross / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response[$orderid]["OrderCredits"] ["ItemsCount"]		= $credit_count[$orderid];
				$response[$orderid]["OrderCredits"] ["ExchangeRate"]	= $ex;
				$response[$orderid]["OrderCredits"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
	
				
				$response[$orderid]["ShippingCosts"] ["GrossFC"] 		= $order[$orderid]["shipping_costs"];
				$response[$orderid]["ShippingCosts"] ["NetFC"] 			= $response[$orderid]["ShippingCosts"] ["GrossFC"] / $VAT_m;
				$response[$orderid]["ShippingCosts"] ["TaxFC"] 			= $response[$orderid]["ShippingCosts"] ["GrossFC"] / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response[$orderid]["ShippingCosts"] ["GrossEUR"]		= $response[$orderid]["ShippingCosts"] ["GrossFC"] / $ex;
				$response[$orderid]["ShippingCosts"] ["NetEUR"] 		= $response[$orderid]["ShippingCosts"] ["GrossFC"] / $ex / $VAT_m;
				$response[$orderid]["ShippingCosts"] ["TaxEUR"]			= $response[$orderid]["ShippingCosts"] ["GrossFC"] / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response[$orderid]["ShippingCosts"] ["ItemsCount"]		= $shipment_count;
				$response[$orderid]["ShippingCosts"] ["ExchangeRate"]	= $ex;
				$response[$orderid]["ShippingCosts"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
	
				//TOTALS for Order
				$response[$orderid]["OrderTotal"] ["GrossFC"] 			=  $response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"];
				$response[$orderid]["OrderTotal"] ["NetFC"]				= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / $VAT_m;
				$response[$orderid]["OrderTotal"] ["TaxFC"]				= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response[$orderid]["OrderTotal"] ["GrossEUR"] 			= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / $ex;
				$response[$orderid]["OrderTotal"] ["NetEUR"]			= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / $ex / $VAT_m;
				$response[$orderid]["OrderTotal"] ["TaxEUR"]			= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response[$orderid]["OrderTotal"] ["PositionsCount"]	= $response[$orderid]["OrderPositionsTotal"]["PositionsCount"];
				$response[$orderid]["OrderTotal"] ["ItemsCount"]		= $response[$orderid]["OrderPositionsTotal"]["ItemsCount"];
				$response[$orderid]["OrderTotal"] ["CollateralCount"]	= $response[$orderid]["OrderPositionsTotal"]["CollateralCount"];
				$response[$orderid]["OrderTotal"] ["ExchangeRate"]		= $response[$orderid]["OrderPositionsTotal"]["ExchangeRate"];
				$response[$orderid]["OrderTotal"] ["Currency_Code"]		= $response[$orderid]["OrderPositionsTotal"]["Currency_Code"];
	
				
				$response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"] 			=  $response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"];
				$response[$orderid]["OrderTotalWoCollateral"] ["NetFC"]				= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / $VAT_m;
				$response[$orderid]["OrderTotalWoCollateral"] ["TaxFC"]				= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response[$orderid]["OrderTotalWoCollateral"] ["GrossNetEUR"] 		= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / $ex;
				$response[$orderid]["OrderTotalWoCollateral"] ["NetEUR"]			= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / $ex / $VAT_m;
				$response[$orderid]["OrderTotalWoCollateral"] ["TaxEUR"]			= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response[$orderid]["OrderTotalWoCollateral"] ["PositionsCount"]	=  $response[$orderid]["OrderPositionsTotal"]["PositionsCount"];
				$response[$orderid]["OrderTotalWoCollateral"] ["ItemsCount"]		=  $response[$orderid]["OrderPositionsTotal"]["ItemsCount"];
				$response[$orderid]["OrderTotalWoCollateral"] ["CollateralCount"]	=  $response[$orderid]["OrderPositionsTotal"]["CollateralCount"];
				$response[$orderid]["OrderTotalWoCollateral"] ["ExchangeRate"]		=  $response[$orderid]["OrderPositionsTotal"]["ExchangeRate"];
				$response[$orderid]["OrderTotalWoCollateral"] ["Currency_Code"]		=  $response[$orderid]["OrderPositionsTotal"]["Currency_Code"];
	
				//TOTAL ALL ORDERS
				$response["OrderCredits"] ["GrossEUR"]		+= $credit_gross;
				$response["OrderCredits"] ["NetEUR"] 		+= $credit_gross / $VAT_m;
				$response["OrderCredits"] ["TaxEUR"]		+= $credit_gross / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderCredits"] ["GrossFC"] 		+= $credit_gross * $ex;
				$response["OrderCredits"] ["NetFC"] 		+= $credit_gross * $ex / $VAT_m;
				$response["OrderCredits"] ["TaxFC"] 		+= $credit_gross * $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderCredits"] ["ItemsCount"]	+= $credit_count[$orderid];
				$response["OrderCredits"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

				$response["ShippingCosts"] ["GrossFC"] 		+= $order[$orderid]["shipping_costs"];
				$response["ShippingCosts"] ["NetFC"] 		+= $order[$orderid]["shipping_costs"] / $VAT_m;
				$response["ShippingCosts"] ["TaxFC"] 		+= $order[$orderid]["shipping_costs"] / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["ShippingCosts"] ["GrossEUR"] 	+= $order[$orderid]["shipping_costs"] / $ex;
				$response["ShippingCosts"] ["NetEUR"]		+= $order[$orderid]["shipping_costs"] / $ex / $VAT_m;
				$response["ShippingCosts"] ["TaxEUR"]		+= $order[$orderid]["shipping_costs"] / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["ShippingCosts"] ["ItemsCount"]	= $shipment_count_total;
				$response["ShippingCosts"] ["Currency_Code"]= $order[$orderid]["Currency_Code"];
	
				$response["OrderTotal"] ["GrossFC"]			+=  $order[$orderid]["shipping_costs"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"];
				$response["OrderTotal"] ["NetFC"]			+= ($order[$orderid]["shipping_costs"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / $VAT_m;
				$response["OrderTotal"] ["TaxFC"]			+= ($order[$orderid]["shipping_costs"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderTotal"] ["GrossEUR"] 		+= ($order[$orderid]["shipping_costs"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / $ex;
				$response["OrderTotal"] ["NetEUR"]			+= ($order[$orderid]["shipping_costs"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / $ex / $VAT_m;
				$response["OrderTotal"] ["TaxEUR"]			+= ($order[$orderid]["shipping_costs"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"]) / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderTotal"] ["PositionsCount"]	= $response["OrderPositionsTotal"] ["PositionsCount"];
				$response["OrderTotal"] ["ItemsCount"]		= $response["OrderPositionsTotal"] ["ItemsCount"];
				$response["OrderTotal"] ["CollateralCount"]	= $response["OrderPositionsTotal"] ["CollateralCount"];
				$response["OrderTotal"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
	
				$response["OrderTotalWoCollateral"] ["GrossFC"] 		+=  $response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"];
				$response["OrderTotalWoCollateral"] ["NetFC"]			+= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / $VAT_m;
				$response["OrderTotalWoCollateral"] ["TaxFC"]			+= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderTotalWoCollateral"] ["GrossEUR"] 		+= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / $ex;
				$response["OrderTotalWoCollateral"] ["NetEUR"]			+= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / $ex / $VAT_m;
				$response["OrderTotalWoCollateral"] ["TaxEUR"]			+= ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderTotalWoCollateral"] ["GrossFC"]) / $ex / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderTotalWoCollateral"] ["PositionsCount"]	= $response["OrderPositionsTotal"] ["PositionsCount"];
				$response["OrderTotalWoCollateral"] ["ItemsCount"]		= $response["OrderPositionsTotal"] ["ItemsCount"];
				$response["OrderTotalWoCollateral"] ["CollateralCount"]	= $response["OrderPositionsTotal"] ["CollateralCount"];
				$response["OrderTotalWoCollateral"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];

				$response["OrderTotalWoCredits"] ["GrossFC"] 		+=  ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] - $response[$orderid]["OrderCredits"] ["GrossFC"]);
				$response["OrderTotalWoCredits"] ["NetFC"]			+=  ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] - $response[$orderid]["OrderCredits"] ["GrossFC"]) / $VAT_m;
				$response["OrderTotalWoCredits"] ["TaxFC"]			+=  ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] - $response[$orderid]["OrderCredits"] ["GrossFC"]) / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderTotalWoCredits"] ["GrossEUR"] 		+=  ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] - $response[$orderid]["OrderCredits"] ["GrossFC"]) / $ex;
				$response["OrderTotalWoCredits"] ["NetEUR"]			+=  ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] - $response[$orderid]["OrderCredits"] ["GrossFC"]) / $ex / $VAT_m;
				$response["OrderTotalWoCredits"] ["TaxEUR"]			+=  ($response[$orderid]["ShippingCosts"] ["GrossFC"] + $response[$orderid]["OrderPositionsTotal"] ["GrossFC"] - $response[$orderid]["OrderCredits"] ["GrossFC"]) / ($VAT_m * 100 ) * ($VAT_m*100-100);
				$response["OrderTotalWoCredits"] ["PositionsCount"]	= $response["OrderPositionsTotal"] ["PositionsCount"];
				$response["OrderTotalWoCredits"] ["ItemsCount"]		= $response["OrderPositionsTotal"] ["ItemsCount"];
				$response["OrderTotalWoCredits"] ["CollateralCount"]= $response["OrderPositionsTotal"] ["CollateralCount"];
				$response["OrderTotalWoCredits"] ["Currency_Code"]	= $order[$orderid]["Currency_Code"];
				
			} // foreach ($order_id_list as $orderid)
	
		
		
		}

	//ALLE WERTE RUNDEN
	if ( $params["round"] )
	{
		$response = round_array($response);
	}
	
	$response["calculation_base"] = $params["calculation_base"];

	return $response;
	
	} // END OF FUNCTION

?>
