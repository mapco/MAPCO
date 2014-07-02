<?php

	if ( !isset($_POST["OrderID"]) )
	{
		echo '<crm_set_order_noteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_order_noteResponse>'."\n";
		exit;
	}

	//GET ORDERdata
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_order)==0)
	{
		echo '<crm_set_order_noteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderID konnte keine Bestellung gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_order_noteResponse>'."\n";
		exit;
	}
	
	q("UPDATE shop_orders SET order_note = '".mysql_real_escape_string($_POST["note"], $dbshop)."' WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);

echo "<crm_set_order_noteResponse>\n";
echo "<Ack>Success</Ack>\n";
echo "</crm_set_order_noteResponse>";


?>