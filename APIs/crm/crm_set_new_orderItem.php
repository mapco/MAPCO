<?php 

	if ( !isset($_POST["OrderID"]) )
	{
		echo '<crm_set_new_orderItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_new_orderItemResponse>'."\n";
		exit;
	}

	$res=q("INSERT INTO shop_orders_items (order_id, item_id, amount, price, netto) VALUES (".$_POST["OrderID"].", 0, 0, 0, 0);", $dbshop, __FILE__, __LINE);
	$shop_order_item_id=mysqli_insert_id($dbshop);
	
	echo "<crm_set_new_orderItemResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<shop_order_item_id>".$shop_order_item_id."</shop_order_item_id>\n";
	echo "</crm_set_new_orderItemResponse>";

?>