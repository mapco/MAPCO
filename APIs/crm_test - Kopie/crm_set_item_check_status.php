<?php

	if ( !isset($_POST["OrderItemID"]) )
	{
		echo '<crm_set_item_check_status>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItemID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderItemID (id.shop_orders_items) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_item_check_status>'."\n";
		exit;
	}

	//GET ORDERdata
	$res_order_item=q("SELECT * FROM shop_orders_items WHERE id = ".$_POST["OrderItemID"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_order_item)==0)
	{
		echo '<crm_set_item_check_status>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItem nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderItemID konnte keine Bestellposition gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_item_check_status>'."\n";
		exit;
	}
	$order_item=mysql_fetch_array($res_order_item);
	
	if ( !isset($_POST["status"]) )
	{
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Status nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Status zur Bestellposition angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
	}
	
	q("UPDATE shop_orders_items SET checked = ".$_POST["status"].", ckecked_by_user = ".$_SESSION["id_user"]." WHERE id = ".$_POST["OrderItemID"].";", $dbshop, __FILE__, __LINE__);
	

echo "<crm_set_item_check_status>\n";
echo "<Ack>Success</Ack>\n";
echo "<OrderID>".$order_item["order_id"]."</OrderID>\n";
echo "</crm_set_item_check_status>";



?>