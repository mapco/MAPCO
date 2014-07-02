<?php

	if ( !isset($_POST["note_id"]) )
	{
		echo '<crm_update_noteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Note ID konnte nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Note ID angegeben werden, deren Notiz bearbeitet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_noteResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["note"]) )
	{
		echo '<crm_update_noteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Notiz nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Notiz angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_noteResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM crm_customer_notes WHERE id_note = ".$_POST["note_id"].";", $dbweb, __FILE__, __LINE__);
	if (mysql_num_rows($res)==0)
	{
		echo '<crm_update_noteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine Notizen gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnten keine Notiz zur ID gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_noteResponse>'."\n";
		exit;
	}
	
	q("UPDATE crm_customer_notes SET note = '".mysql_real_escape_string($_POST["note"], $dbweb)."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE id_note = ".$_POST["note_id"].";", $dbweb, __FILE__, __LINE__);
	
	echo "<crm_update_noteResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_update_noteResponse>";

?>