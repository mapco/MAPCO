<?php

	/****************************************************************
	 * IMPORTS ORDERS_ITEMS FROM OLD SHOPS TO THE CLONE SHOP SYSTEM *
	 ****************************************************************/

	include("config.php");
//	if( $_SESSION["id_user"]!=21371 ) exit;
	$db=$dblenkung24;

	//import shop_orders_items
	$order=array();
	$results=q("SELECT id_order, new_order_id FROM shop_orders;", $db, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$order[$row["id_order"]]=$row["new_order_id"];
	}
	$results=q("SELECT * FROM shop_orders_items WHERE new_id=0;", $db, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		//remove sequential keys
		for($i=0; $i<sizeof($row); $i++)
		{
			if( isset($row[$i]) ) unset($row[$i]);
		}
		//remove unknown keys
		$old_id=$row["id"];
		unset($row["id"]);
		unset($row["new_id"]);
		//convert to new format
		$row["order_id"]=$order[$row["order_id"]];
		//add new columns
		$row["netto"]=round($row["price"]/1.19, 2);
		$row["Currency_Code"]="EUR";
		$row["exchange_rate_to_EUR"]=1;
		
		q_insert("shop_orders_items", $row, $dbshop, __FILE__, __LINE__);
		$new_id=mysqli_insert_id($dbshop);
		q("UPDATE shop_orders_items SET new_id=".$new_id." WHERE id=".$old_id.";", $db, __FILE__, __LINE__);
		echo '<br />Bestellzeile '.$old_id.' erfolgreich nach '.$new_id.' importiert.';
	}
?>