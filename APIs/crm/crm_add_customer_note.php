<?php

	if ( !isset($_POST["customer_id"]) )
	{
		echo '<crm_add_customer_noteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Customer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine KundenID für die anzulegende Notiz angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_noteResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["note"]) )
	{
		echo '<crm_add_customer_noteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Notiz nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Notiz angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_noteResponse>'."\n";
		exit;
	}

	q("INSERT INTO crm_customer_notes (customer_id, note, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["customer_id"].", '".mysqli_real_escape_string($dbweb,$_POST["note"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	
	echo "<crm_add_customer_noteResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_add_customer_noteResponse>";

?>