<?php

	if ( !isset($_POST["note_id"]) )
	{
		echo '<crm_get_noteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Note ID konnte nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Note ID angegeben werden, deren Notiz bearbeitet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_noteResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM crm_customer_notes WHERE id_note = ".$_POST["note_id"].";", $dbweb, __FILE__, __LINE__);
	if (mysql_num_rows($res)==0)
	{
		echo '<crm_get_noteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine Notizen gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnten keine Notiz zur ID gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_noteResponse>'."\n";
		exit;
	}

	while ($row=mysql_fetch_array($res))
	{
		$note=$row["note"];	
	}
	
	echo "<crm_get_noteResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<Note><![CDATA[".$note."]]></Note>\n";
	echo "</crm_get_noteResponse>";

	
?>
	