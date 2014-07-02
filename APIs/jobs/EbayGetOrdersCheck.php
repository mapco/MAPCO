<?php

	$ebay_orders=array();
	$results=q("SELECT id_order, OrderID, CreatedTime FROM ebay_orders WHERE firstmod>".mktime(0, 0, 0, 6, 1, 2013).";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$ebay_orders[]=$row;
	}

	$shop_orders=array();
	$results=q("SELECT id_order, foreign_OrderID FROM shop_orders;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$shop_orders[$row["foreign_OrderID"]]=$row["id_order"];
	}
	
	$j=0;
	for($i=0; $i<sizeof($ebay_orders); $i++)
	{
		if( !isset($shop_orders[$ebay_orders[$i]["OrderID"]]) )
		{
			$j++;
			echo $j.' '.$ebay_orders[$i]["CreatedTime"].'<br />';
			$varField["API"]="crm";
			$varField["Action"]="import_ebayOrderData";
			$varField["mode"]="add";
			$varField["EbayOrderID"]=$ebay_orders[$i]["id_order"];
		 	post(PATH."soa/", $varField);
		}
	}

?>