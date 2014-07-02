<?php

	/*********************************************************************
	 * REPAIRS ORDER_ID IN SHOP_ORDERS_ITEMS AFTER EBAY_GET_ORDER UPDATE *
	 *********************************************************************/

	include("config.php");
//	if( $_SESSION["id_user"]!=21371 ) exit;

	$order_id=array();
	$results=q("SELECT id, order_id FROM shop_orders_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$order_id[$row["id"]]=$row["order_id"];
	}

	$results=q("SELECT id, order_id FROM shop_orders_items_1383657234;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( $order_id[$row["id"]]==0 )
		{
			echo $query="UPDATE shop_orders_items SET order_id=".$row["order_id"]." WHERE id=".$row["id"].";";
			echo '<br />';
			q($query, $dbshop, __FILE__, __LINE__);
		}
	}
	
?>