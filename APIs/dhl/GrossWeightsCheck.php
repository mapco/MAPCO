<?php

	//get all item gross weights
	$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$weight[$row["id_item"]]=$row["GrossWeight"];
	}


	//get all orders with one item only
	$orders=array();
	$items=array();
	$results=q("SELECT order_id, item_id FROM `shop_orders_items` WHERE item_id>0 GROUP BY order_id HAVING COUNT(id)=1;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$orders[]=$row["order_id"];
		$items[$row["order_id"]]=$row["item_id"];
	}
	
	//get all orders with gross weight and only one item
	$new=array();
	$results=q("SELECT id_order, shipping_WeightInKG FROM shop_orders WHERE shipping_number!='' AND shipping_WeightInKG!='' AND id_order IN (".implode(", ", $orders).") ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
//		$new[$items[$row["id_order"]]][]=$row["shipping_WeightInKG"];
		$new[$items[$row["id_order"]]]["GrossWeight"]=$row["shipping_WeightInKG"]*1000; //save in grams not kilograms
		$new[$items[$row["id_order"]]]["id_item"]=$items[$row["id_order"]];
	}

	//update shop_items gross weights
	$i=0;
	$query="UPDATE shop_items SET GrossWeight = CASE"."\n";
	foreach($new as $item)
	{
		if( $weight[$item["id_item"]] != $item["GrossWeight"] )
		{
			$i++;
			$query .= "WHEN id_item=".$item["id_item"]." THEN ".$item["GrossWeight"]."\n";
		}
	}
	$query .= "ELSE GrossWeight"."\n";
	$query .= "END;"."\n";
	if( $i>0 ) q($query, $dbshop, __FILE__, __LINE__);

	//return number of changes
	echo '	<Updated>'.$i.'</Updated>'."\n";
	
?>