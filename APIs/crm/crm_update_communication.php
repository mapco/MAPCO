<?php

	if ( !isset($_POST["communication_id"]) )
	{
		echo '<crm_update_communicationResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Communication ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Kommunikations ID für den zu bearbeitenden Kundenkontakt angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_communicationResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["contacttype"]) )
	{
		echo '<crm_update_communicationResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kontaktart nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Kontaktart zur Kommunikation angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_communicationResponse>'."\n";
		exit;
	}

	if (isset($_POST["reminder_date"]) && $_POST["reminder_date"]!="")
	{
		$reminder=mktime($_POST["reminder_time"]*1, 0,0, substr($_POST["reminder_date"], 3,2), substr($_POST["reminder_date"], 0,2), substr($_POST["reminder_date"], 6));
	}
	else $reminder=0;

	q("UPDATE crm_communications SET communtication_type = '".mysqli_real_escape_string($dbweb,$_POST["contacttype"])."', communication_text = '".mysqli_real_escape_string($dbweb,$_POST["note"])."', reminder = ".$reminder.", lastmod = ".time().", lastmod_user= ".$_SESSION["id_user"]." WHERE id_communication = ".$_POST["communication_id"].";", $dbweb, __FILE__, __LINE__);

	echo "<crm_update_communicationResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_update_communicationResponse>";
