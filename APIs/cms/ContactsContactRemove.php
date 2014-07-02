<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_contact"]) )
	{
		echo '<ContactsContactRemove>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kontakt-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Kontakt-ID muss übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactRemove>'."\n";
		exit;
	}

	q("DELETE FROM cms_contacts WHERE id_contact=".$_POST["id_contact"].";", $dbweb, __FILE__, __LINE__);

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsContactRemove>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsContactRemove>'."\n";

?>