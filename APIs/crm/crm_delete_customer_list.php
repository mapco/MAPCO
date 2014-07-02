<?php

	if ( !isset($_POST["listID"]) )
	{
		echo '<crm_delete_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Listen ID angegeben werden, um betreffende Liste zu löschen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_delete_customer_listResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM crm_costumer_lists WHERE id_list = ".$_POST["listID"].";", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<crm_delete_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Liste zur ID konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_delete_customer_listResponse>'."\n";
		exit;
	}

	q("DELETE FROM crm_costumer_lists_customers WHERE list_id = ".$_POST["listID"].";", $dbweb, __FILE__, __LINE__);

	q("DELETE FROM crm_costumer_lists WHERE id_list = ".$_POST["listID"].";", $dbweb, __FILE__, __LINE__);
	
	echo "<crm_delete_customer_listResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_delete_customer_listResponse>";

?>