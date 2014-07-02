<?php

	if ( !isset($_POST["orderItemID"]) )
	{
		echo '<crm_update_order_ItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItemID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID der zu löschenden Position der Bestellung angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_order_ItemResponse>'."\n";
		exit;
	}

	if ($_POST["MPN"]!="")
	{
		$res_ItemID=q("SELECT * FROM shop_items WHERE MPN = '".$_POST["MPN"]."';", $dbshop, __FILE__, __LINE__);
		$row_ItemID=mysql_fetch_array($res_ItemID);
		$itemID=$row_ItemID["id_item"];
	}
	else
	{
		$itemID=0;
	}
	
	q("UPDATE shop_orders_items SET item_id = ".$itemID.", amount = ".$_POST["amount"].", price = ".number_format($_POST["price"], 2, ".","")." WHERE id = ".$_POST["orderItemID"].";", $dbshop, __FILE__, __LINE__);
	
	echo "<crm_update_order_ItemResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_update_order_ItemResponse>";

?>
	