<?php

	if ( !isset($_POST["customer_id"]) )
	{
		echo '<crm_add_customer_communicationResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Customer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine KundenID für die anzulegende Notiz angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_communicationResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["contacttype"]) )
	{
		echo '<crm_add_customer_communicationResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kontaktart nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Kontaktart zur Kommunikation angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_communicationResponse>'."\n";
		exit;
	}

	if (isset($_POST["reminder_date"]) && $_POST["reminder_date"]!="")
	{
		$reminder=mktime($_POST["reminder_time"]*1, 0,0, substr($_POST["reminder_date"], 3,2), substr($_POST["reminder_date"], 0,2), substr($_POST["reminder_date"], 6));
	}
	else $reminder=0;

	q("INSERT INTO crm_communications (customer_id, communtication_type, communication_text, reminder, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["customer_id"].", '".mysqli_real_escape_string($dbweb, $_POST["contacttype"])."', '".mysqli_real_escape_string($dbweb,$_POST["note"])."', ".$reminder.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	
	echo "<crm_add_customer_communicationResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_add_customer_communicationResponse>";
