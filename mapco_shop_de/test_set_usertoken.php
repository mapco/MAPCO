<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//GET SHOP DATA
	$res_shop=q("SELECT * FROM shop_shops WHERE id_shop = 4 LIMIT 1;", $dbshop, __FILE__, __LINE__);
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
	$res_ebay=q("SELECT OrderID, ShippingAddressAddressID FROM ebay_orders2 WHERE account_id = ".$shop["account_id"].";", $dbshop, __FILE__, __LINE__);
	while ($row_ebay=mysqli_fetch_array($res_ebay))
	{
		$ebay_order[$row_ebay["OrderID"]]=$row_ebay["ShippingAddressAddressID"];
	}



	$orders=array();
	$res=q("SELECT * FROM shop_orders7 WHERE shop_id = 4 AND (status_id = 1 OR status_id = 7);" , $dbshop, __FILE__, __LINE__);
	echo mysqli_num_rows($res);
	while ($row=mysqli_fetch_array($res))
	{
		//if (($row["status_id"]==1 || $row["status_id"]==7) && ($row["shop_id"]==3 || $row["shop_id"]==4 || $row["shop_id"]==5))
		//{
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
						if (($date-$row["firstmod"]<=24*3600 || $row["firstmod"]-$date<=24*3600) && !$insert && $addressID == $ebay_order[$row["foreign_OrderID"]])
//if (($date-$row["firstmod"]<=24*3600 || $row["firstmod"]-$date<=24*3600) && !$insert)
						{
							$insert=true;
							$orders[$row["customer_id"]][$j]["firstmod"][sizeof($orders[$row["customer_id"]][$j]["firstmod"])]=$row["firstmod"];
							$orders[$row["customer_id"]][$j]["id_order"][sizeof($orders[$row["customer_id"]][$j]["id_order"])]=$row["id_order"];
							$orders[$row["customer_id"]][$j]["combined_with"][sizeof($orders[$row["customer_id"]][$j]["combined_with"])]=$row["combined_with"];
							$orders[$row["customer_id"]][$j]["AddressID"][sizeof($orders[$row["customer_id"]][$j]["AddressID"])]=$ebay_order[$row["foreign_OrderID"]];
							
							//print_r($orders[$row["customer_id"]])."<br /><br /><br />";
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
		//}
	}
	
	foreach ($orders as $costumer => $order)		
	{
		//if (sizeof($order)>1)
		{
		//	echo "CUSTOMER ID: ".$costumer;
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
							echo " Order: ".$order[$i]["id_order"][$j];
						//	q("UPDATE shop_orders_test SET combined_with = ".$order[$i]["id_order"][0]." WHERE id_order = ".$order[$i]["id_order"][$j].";", $dbshop, __FILE__, __LINE__);
						}
					}
					echo "<br />";
				}
			}
		}
		/*
		else
		{
			//echo $costumer." KEIN MULTIKÄUFER";
		}
		*/
	}
	
	unset($orders);
	
	//suche nach Combined_with mit nur 1 Eintrag -> lösche combined_with
	
	//GET COMBINED ORDERS
	$orders=array();
	$res=q("SELECT * FROM shop_orders WHERE shop_id = 4 and combined_with >0;" , $dbshop, __FILE__, __LINE__);
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
		if ($val == 1) echo "NO MULTIPLE: ".$key."<br />";
	}
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
