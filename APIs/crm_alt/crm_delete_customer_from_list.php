<?php

	if ( !isset($_POST["id_list"]) )
	{
		echo '<crm_delete_customer_from_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen ID konnte nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Liste angegeben werden, aus der der Kunde entfernt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_delete_customer_from_listResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["customer_id"]) )
	{
		echo '<crm_delete_customer_from_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kunden ID konnte nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Kunde angegeben werden, der aus der Liste entfernt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_delete_customer_from_listResponse>'."\n";
		exit;
	}
	$res=q("SELECT * FROM crm_costumer_lists_customers WHERE customer_id = ".$_POST["customer_id"]." AND list_id = ".$_POST["id_list"].";", $dbweb, __FILE__, __LINE__);
	if (mysql_num_rows($res)==0)
	{
		echo '<crm_delete_customer_from_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kunde nicht gefunden gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Kunde konnte in der Liste nicht gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_delete_customer_from_listResponse>'."\n";
		exit;
	}
	
	q("DELETE FROM crm_costumer_lists_customers WHERE customer_id = ".$_POST["customer_id"]." AND list_id = ".$_POST["id_list"].";", $dbweb, __FILE__, __LINE__);
	
	echo "<crm_delete_customer_from_listResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_delete_customer_from_listResponse>";

?>