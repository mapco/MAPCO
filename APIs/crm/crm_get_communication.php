<?php

	if ( !isset($_POST["communication_id"]) )
	{
		echo '<crm_get_communicationResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Communication ID konnte nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Kommunikations ID angegeben werden, deren Kommunikation bearbeitet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_communicationResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM crm_communications WHERE id_communication = ".$_POST["communication_id"].";", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<crm_get_communicationResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine Kundenkommunikation gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnten keine Kundenkommunikation zur ID gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_communicationResponse>'."\n";
		exit;
	}

	$row=mysqli_fetch_array($res);
	
	echo "<crm_get_communicationResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<customer_id>".$row["customer_id"]."</customer_id>\n";
	echo "<communication_type><![CDATA[".$row["communtication_type"]."]]></communication_type>\n";
	echo "<communication_text><![CDATA[".$row["communication_text"]."]]></communication_text>\n";
	echo "<reminder>".$row["reminder"]."</reminder>\n";
	echo "</crm_get_communicationResponse>";

	
?>
	