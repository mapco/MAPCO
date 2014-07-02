<?php

	if ( !isset($_POST["customer_id"]) || $_POST["customer_id"]=="" )
	{
		echo '<update_customer_dataResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>CRM Customer ID nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine CRM Customer ID angegeben werden, um Daten die Daten zu ändern.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</update_customer_dataResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM crm_customers WHERE id_crm_customer = ".$_POST["customer_id"].";", $dbweb, __FILE__, __LINE__);
	if (mysql_num_rows($res)==0)
	{
		echo '<update_customer_dataResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>CRM Customer nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Zur angegebenen ID konnte kein Customer gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</update_customer_dataResponse>'."\n";
		exit;
	}

	q("UPDATE crm_customers SET company = '".mysql_real_escape_string($_POST["company"], $dbweb)."', name = '".mysql_real_escape_string($_POST["name"], $dbweb)."',street1 = '".mysql_real_escape_string($_POST["street1"], $dbweb)."',street2 = '".mysql_real_escape_string($_POST["street2"], $dbweb)."',zip = '".mysql_real_escape_string($_POST["zip"], $dbweb)."',city = '".mysql_real_escape_string($_POST["city"], $dbweb)."',country = '".mysql_real_escape_string($_POST["country"], $dbweb)."',phone = '".mysql_real_escape_string($_POST["phone"], $dbweb)."',mobile = '".mysql_real_escape_string($_POST["mobile"], $dbweb)."',fax = '".mysql_real_escape_string($_POST["fax"], $dbweb)."',mail = '".mysql_real_escape_string($_POST["mail"], $dbweb)."', lastmod =".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id_crm_customer = ".$_POST["customer_id"].";", $dbweb, __FILE__, __LINE__);
	
	echo '<update_customer_dataResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</update_customer_dataResponse>'."\n";

?>