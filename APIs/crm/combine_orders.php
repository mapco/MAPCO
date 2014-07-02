<?php

	if ( !isset($_POST["mode"]) )
	{
		echo '<combine_ordersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bearbeitungsmodus nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss angegeben werden, alle Bestellungen eines Shops gescannt werden soellen oder eine Einzelzusammenfassung durchgeführt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</combine_ordersResponse>'."\n";
		exit;
	}


	if ($_POST["mode"]=="scan")
	{
		if ( !isset($_POST["shop_id"]) )
		{
			echo '<combine_ordersResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>ShopID konnte nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine Shop ID angegeben werden, deren Bestellungen zusammengefasst werden sollen.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</combine_ordersResponse>'."\n";
			exit;
		}
		
		//GET SHOP DATA
		$res_shop=q("SELECT * FROM shop_shops WHERE id_shop = ".$_POST["shop_id"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shop)==0)
		{
			echo '<combine_ordersResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Shop konnte nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>SHOP konnte in der Tabelle shop_shops nicht gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</combine_ordersResponse>'."\n";
			exit;
		}
		$shop=mysqli_fetch_array($res_shop);
		
		
		//get EBAY ORDERS
		$ebay_order=array();
		$res_ebay=q("SELECT OrderID, ShippingAddressAddressID FROM ebay_orders WHERE account_id = ".$shop["account_id"].";", $dbshop, __FILE__, __LINE__);
		while ($row_ebay=mysqli_fetch_array($res_ebay))
		{
			$ebay_order[$row_ebay["OrderID"]]=$row_ebay["ShippingAddressAddressID"];
		}

		$stati = array(1,7);
		
		for ($k=0; $k<sizeof($stati); $k++)
		{
			$orders=array();
			
			$res=q("SELECT * FROM shop_orders WHERE shop_id = ".$_POST["shop_id"]." AND status_id = ".$stati[$k]." AND ordertype_id = 1;" , $dbshop, __FILE__, __LINE__);
			//echo mysqli_num_rows($res);
			while ($row=mysqli_fetch_array($res))
			{
				if (isset($orders[$row["customer_id"]]))
				{
					$insert=false;
					$size=sizeof($orders[$row["customer_id"]]);
					for ($j=0; $j<sizeof($orders[$row["customer_id"]]); $j++)
					{
						for ($i=0; $i<sizeof($orders[$row["customer_id"]][$j]["firstmod"]); $i++)
						{
							$date=$orders[$row["customer_id"]][$j]["firstmod"][$i];
							$addressID=$orders[$row["customer_id"]][$j]["AddressID"][$i];
							if (($date-$row["firstmod"]<=24*3600 || $row["firstmod"]-$date<=24*3600) && !$insert)
							{
								if (isset($ebay_order[$row["foreign_OrderID"]]) && $ebay_order[$row["foreign_OrderID"]]==$addressID)
								{
									$insert=true;
									$orders[$row["customer_id"]][$j]["firstmod"][sizeof($orders[$row["customer_id"]][$j]["firstmod"])]=$row["firstmod"];
									$orders[$row["customer_id"]][$j]["id_order"][sizeof($orders[$row["customer_id"]][$j]["id_order"])]=$row["id_order"];
									$orders[$row["customer_id"]][$j]["combined_with"][sizeof($orders[$row["customer_id"]][$j]["combined_with"])]=$row["combined_with"];
									$orders[$row["customer_id"]][$j]["AddressID"][sizeof($orders[$row["customer_id"]][$j]["AddressID"])]=$ebay_order[$row["foreign_OrderID"]];
								}
							}
						}
					}
					if (!$insert)
					{
						$orders[$row["customer_id"]][$size]["id_order"][0]=$row["id_order"];
						$orders[$row["customer_id"]][$size]["firstmod"][0]=$row["firstmod"];
						$orders[$row["customer_id"]][$size]["combined_with"][0]=$row["combined_with"];
						$orders[$row["customer_id"]][$size]["AddressID"][0]=$ebay_order[$row["foreign_OrderID"]];
					}
				}
				else 
				{
					$orders[$row["customer_id"]][0]["id_order"][0]=$row["id_order"];
					$orders[$row["customer_id"]][0]["firstmod"][0]=$row["firstmod"];
					$orders[$row["customer_id"]][0]["combined_with"][0]=$row["combined_with"];
					$orders[$row["customer_id"]][0]["AddressID"][0]=$ebay_order[$row["foreign_OrderID"]];
				}
			}
			
			foreach ($orders as $costumer => $order)		
			{
				for ($i=0; $i<sizeof($order); $i++)
				{
					if (sizeof($order[$i]["id_order"])>1)
					{
						//SUCHE NACH BEREITS VORHANDENER KOMBINATION
						$combined_with=0;
						for ($j=0; $j<sizeof($order[$i]["combined_with"]); $j++ )
						{
							if ($combined_with==0 && $order[$i]["combined_with"][$j]>0)
							{
								$combined_with=$order[$i]["combined_with"][$j];
							}
						}
						
						if ($combined_with==0) $combined_with=$order[$i]["id_order"][0];
						
						for ($j=0; $j<sizeof($order[$i]["id_order"]); $j++ )
						{
							if ($order[$i]["combined_with"][$j]!=-1)
							{
						//		echo " Order: ".$order[$i]["id_order"][$j];
								q("UPDATE shop_orders SET combined_with = ".$order[$i]["id_order"][0]." WHERE id_order = ".$order[$i]["id_order"][$j].";", $dbshop, __FILE__, __LINE__);
							}
						}
					//echo "<br />";
					}
				}
				
			}
		unset($orders);
		
		} // FOR stati
		
		
		//suche nach kombinierten Orders mit unterschiedlichen Stati oder Zahlungsstati
		$orders=array();
		$res=q("SELECT * FROM shop_orders WHERE shop_id = ".$_POST["shop_id"]." and combined_with > 0;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			{
				$orders[$row["combined_with"]][$row["id_order"]]["payments_type_id"]=$row["payments_type_id"];
				$orders[$row["combined_with"]][$row["id_order"]]["Payments_TransactionState"]=$row["Payments_TransactionState"];
				$orders[$row["combined_with"]][$row["id_order"]]["status_id"]=$row["status_id"];
			}
		}
		
		foreach ($orders as $order => $orderline)
		{
			//echo "<b>".$order."</b><br />";
			foreach ($orderline as $id_order => $line)
			{
				if ($line["status_id"]!=$orders[$order][$order]["status_id"] )
				{
					//echo $id_order." ABBRUCH<br />";
					q("UPDATE shop_orders SET combined_with = 0 WHERE id_order = ".$id_order.";", $dbshop, __FILE__, __LINE__);
				}
				elseif ($line["payments_type_id"]!=$orders[$order][$order]["payments_type_id"] )
				{
					//echo $id_order." PAYMENT TYPE<br />";
					q("UPDATE shop_orders SET combined_with = 0 WHERE id_order = ".$id_order.";", $dbshop, __FILE__, __LINE__);
				}
				elseif ($line["Payments_TransactionState"]!=$orders[$order][$order]["Payments_TransactionState"] )
				{
					//echo $id_order." PAYMENT STATE<br />";
					q("UPDATE shop_orders SET combined_with = 0 WHERE id_order = ".$id_order.";", $dbshop, __FILE__, __LINE__);
				}
				
			}
		}

			
			
		//suche nach Combined_with mit nur 1 Eintrag -> lösche combined_with
		
		//GET COMBINED ORDERS
		$orders=array();
		//$res=q("SELECT * FROM shop_orders WHERE shop_id = ".$_POST["shop_id"]." and combined_with >0;" , $dbshop, __FILE__, __LINE__);
		$res=q("SELECT * FROM shop_orders WHERE combined_with >0;" , $dbshop, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($res))
		{
			if (isset($orders[$row["combined_with"]]))
			{
				$orders[$row["combined_with"]]++;
			}
			else
			{
				$orders[$row["combined_with"]]=1;
			}
		}
		
		while ( list ($key, $val) = each ($orders))
		{
			if ($val == 1) 
			{
				//echo "NO MULTIPLE: ".$key."<br />";
				q("UPDATE shop_orders SET combined_with = 0 WHERE id_order = ".$key.";", $dbshop, __FILE__, __LINE__);
			}
		}
			
	

	} // IF POST SCAN
	
	if ($_POST["mode"]=="unset_combination")
	{

		if ( !isset($_POST["combined_with"]) )
		{
			echo '<combine_ordersResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Combined ID nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine ID angegeben werden, unter der Bestellungen zusammengefasst sind.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</combine_ordersResponse>'."\n";
			exit;
		}
	
		$res=q("SELECT * FROM shop_orders WHERE combined_with = ".$_POST["combined_with"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)==0)
		{
			echo '<combine_ordersResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Besetllungskombination nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Zur angegebenen ID konnte keine Kombination von Bestellungen gefunden werden</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</combine_ordersResponse>'."\n";
			exit;
		}
	
		q("UPDATE shop_orders SET combined_with = -1 WHERE combined_with = ".$_POST["combined_with"].";", $dbshop, __FILE__, __LINE__);
		
	}


	echo "<combine_ordersResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</combine_ordersResponse>";


?>