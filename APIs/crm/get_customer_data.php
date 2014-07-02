<?php

	if ( !isset($_POST["customer_id"]) || $_POST["customer_id"]=="" )
	{
		echo '<get_customer_dataResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>CRM Customer ID nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine CRM Customer ID angegeben werden, um Daten zum Customer zu erhalten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</get_customer_dataResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM crm_customers WHERE id_crm_customer = ".$_POST["customer_id"].";", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<get_customer_dataResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>CRM Customer nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Zur angegebenen ID konnte kein Customer gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</get_customer_dataResponse>'."\n";
		exit;
	}

	$row=mysqli_fetch_array($res);
	
	$res_cms_user=q("SELECT * FROM crm_customer_accounts WHERE crm_customer_id = ".$_POST["customer_id"]." AND account = 1;",  $dbweb, __FILE__, __LINE__);
	$row_cms_user=mysqli_fetch_array($res_cms_user);

	echo '<get_customer_dataResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response>'."\n";
	echo '		<business><![CDATA['.$row["gewerblich"].']]></business>'."\n";
	echo '		<company><![CDATA['.$row["company"].']]></company>'."\n";
	echo '		<name><![CDATA['.$row["name"].']]></name>'."\n";
	echo '		<street1><![CDATA['.$row["street1"].']]></street1>'."\n";
	echo '		<street2><![CDATA['.$row["street2"].']]></street2>'."\n";
	echo '		<zip><![CDATA['.$row["zip"].']]></zip>'."\n";
	echo '		<city><![CDATA['.$row["city"].']]></city>'."\n";
	echo '		<country><![CDATA['.$row["country"].']]></country>'."\n";
	echo '		<phone><![CDATA['.$row["phone"].']]></phone>'."\n";
	echo '		<mobile><![CDATA['.$row["mobile"].']]></mobile>'."\n";
	echo '		<fax><![CDATA['.$row["fax"].']]></fax>'."\n";
	echo '		<mail><![CDATA['.$row["mail"].']]></mail>'."\n";
	echo '		<account_user_id><![CDATA['.$row_cms_user["account_user_id"].']]></account_user_id>'."\n";
	echo '		<lastmod>'.$row["lastmod"].'</lastmod>'."\n";
	echo '		<lastmod_user>'.$row["lastmod_user"].'</lastmod_user>'."\n";	
	echo '	</Response>'."\n";
	echo '</get_customer_dataResponse>'."\n";
	

?>
