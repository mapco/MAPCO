<?php

	if ( !isset($_POST["orderItemID"]) )
	{
		echo '<crm_update_order_ItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItemID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderItemID (id.shop_orders_items) der zu löschenden Position der Bestellung angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_order_ItemResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["order_id"]) )
	{
		echo '<crm_update_order_ItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID zu der zu bearbeitenden Position der Bestellung angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_order_ItemResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["amount"]) )
	{
		echo '<crm_update_order_ItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Anzahl nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Anzahl zu der zu bearbeitenden Position der Bestellung angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_order_ItemResponse>'."\n";
		exit;
	}


	if ( !isset($_POST["itemID"]) )
	{
		echo '<crm_update_order_ItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ItemID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ItemID zu der zu bearbeitenden Position der Bestellung angegeben werden..</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_order_ItemResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["price"]) )
	{
		echo '<crm_update_order_ItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Price nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Preis zu der zu bearbeitenden Position der Bestellung angegeben werden..</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_order_ItemResponse>'."\n";
		exit;
	}
	if ( !isset($_POST["net"]) )
	{
		echo '<crm_update_order_ItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Netto nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Netto-Preis zu der zu bearbeitenden Position der Bestellung angegeben werden..</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_order_ItemResponse>'."\n";
		exit;
	}

/*
	if ($_POST["MPN"]!="")
	{
		$res_ItemID=q("SELECT * FROM shop_items WHERE MPN = '".$_POST["MPN"]."';", $dbshop, __FILE__, __LINE__);
		$row_ItemID=mysqli_fetch_array($res_ItemID);
		$itemID=$row_ItemID["id_item"];
	}
	else
	{
		$itemID=0;
	}
*/	
	q("UPDATE shop_orders_items SET item_id = ".$_POST["itemID"].", amount = ".$_POST["amount"].", price = ".$_POST["price"].", netto = ".$_POST["net"]." WHERE id = ".$_POST["orderItemID"]." and order_id = ".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
	
	echo "<crm_update_order_ItemResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_update_order_ItemResponse>";

?>
	