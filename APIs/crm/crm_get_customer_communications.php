<?php

	if ( !isset($_POST["customer_id"]) )
	{
		echo '<crm_get_customer_communicationsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Customer ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Kunden ID angegeben werden, zu der der Kommunikationsverlauf angezeigt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_customer_communicationsResponse>'."\n";
		exit;
	}
	
	//get_cms_contacts
	$cms_contacts=array();
	$res_contacts=q("SELECT * FROM cms_contacts;",  $dbweb, __FILE__, __LINE__);
	while ($row_contacts=mysqli_fetch_array($res_contacts))
	{
		$cms_contacts[$row_contacts["idCmsUser"]]=$row_contacts["firstname"]." ".$row_contacts["lastname"];
	}
	

	$xmldata="";
	$counter=0;
	$res_comm=q("SELECT * FROM crm_communications WHERE customer_id = ".$_POST["customer_id"].";", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res_comm)>0)
	{
		while ($row_comm=mysqli_fetch_array($res_comm))
		{
			$xmldata.= "<communication>\n";
			$xmldata.= "<communication_id>".$row_comm["id_communication"]."</communication_id>\n";
			$xmldata.= "<communication_type><![CDATA[".$row_comm["communtication_type"]."]]></communication_type>\n";
			$xmldata.= "<communication_text><![CDATA[".$row_comm["communication_text"]."]]></communication_text>\n";
			$xmldata.= "<communication_reminder>".$row_comm["reminder"]."</communication_reminder>\n";
			$xmldata.= "<communication_lastmod>".$row_comm["lastmod"]."</communication_lastmod>\n";
			$xmldata.= "<communication_lastmod_user>".$row_comm["lastmod_user"]."</communication_lastmod_user>\n";
			$xmldata.= "<communication_lastmod_user_name>".$cms_contacts[$row_comm["lastmod_user"]]."</communication_lastmod_user_name>\n";
			$xmldata.= "<communication_firstmod>".$row_comm["firstmod"]."</communication_firstmod>\n";
			$xmldata.= "<communication_firstmod_user>".$row_comm["firstmod_user"]."</communication_firstmod_user>\n";
			$xmldata.= "<communication_firstmod_user_name>".$cms_contacts[$row_comm["firstmod_user"]]."</communication_firstmod_user_name>\n";
			$xmldata.= "</communication>\n";
		}
	}
	
	else 
	{
		echo '<crm_get_customer_communicationsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine Kommunikation zum Customer gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnten keine Kommunikation zum Customer gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_customer_communicationsResponse>'."\n";
		exit;
	}

	echo "<crm_get_customer_communicationsResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<count>".$counter."</count>\n";
	echo "<Communications>".$xmldata."</Communications>\n";
	echo "</crm_get_customer_communicationsResponse>";

?>

