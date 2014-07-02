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
	if (mysqli_num_rows($res)==0)
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

	q("UPDATE crm_customers SET company = '".mysqli_real_escape_string($dbweb, $_POST["company"])."', name = '".mysqli_real_escape_string($dbweb, $_POST["name"])."',street1 = '".mysqli_real_escape_string($dbweb, $_POST["street1"])."',street2 = '".mysqli_real_escape_string($dbweb, $_POST["street2"])."',zip = '".mysqli_real_escape_string($dbweb, $_POST["zip"])."',city = '".mysqli_real_escape_string($dbweb, $_POST["city"])."',country = '".mysqli_real_escape_string($dbweb, $_POST["country"])."',phone = '".mysqli_real_escape_string($dbweb, $_POST["phone"])."',mobile = '".mysqli_real_escape_string($dbweb, $_POST["mobile"])."',fax = '".mysqli_real_escape_string($dbweb, $_POST["fax"])."',mail = '".mysqli_real_escape_string($dbweb, $_POST["mail"])."', lastmod =".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id_crm_customer = ".$_POST["customer_id"].";", $dbweb, __FILE__, __LINE__);
	
	echo '<update_customer_dataResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</update_customer_dataResponse>'."\n";

?>