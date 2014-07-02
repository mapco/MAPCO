<?php

	if ( !isset($_POST["customer_id"]) )
	{
		echo '<crm_add_customer_to_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Customer ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Kunden ID angegeben werden, die zur Liste hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_to_listResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["list_id"]) )
	{
		echo '<crm_add_customer_to_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen ID nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Liste angegeben werden, zu der der Kunde hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_to_listResponse>'."\n";
		exit;
	}

	//CHECK, OB customer_id bereits in liste
	$res_check=q("SELECT * FROM crm_costumer_lists_customers WHERE list_id = ".$_POST["list_id"]." AND customer_id = ".$_POST["customer_id"].";", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res_check)>0) 
	{
		echo '<crm_add_customer_to_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kunde bereits in Liste</shortMsg>'."\n";
		echo '		<longMsg>Die Kundern ID ist bereits in der Kundenliste vorhanden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_to_listResponse>'."\n";
		exit;
	}
	
	q("INSERT INTO crm_costumer_lists_customers (list_id, customer_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["list_id"].", ".$_POST["customer_id"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	
	echo "<crm_add_customer_to_listResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_add_customer_to_listResponse>";

?>