<?php 

	if ( !isset($_POST["orderItemID"]) )
	{
		echo '<crm_delete_order_ItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItemID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID der zu löschenden Position der Bestellung angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_delete_order_ItemResponse>'."\n";
		exit;
	}

	$res=q("DELETE FROM shop_orders_items WHERE id = ".$_POST["orderItemID"].";", $dbshop, __FILE__, __LINE);
	
	echo "<crm_delete_order_ItemResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_delete_order_ItemResponse>";

?>