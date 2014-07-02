<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["list"]) )
	{
		echo '<ContactsContactEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kontak-Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Kontakt-Liste übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactEdit>'."\n";
		exit;
	}

	for($i=1; $i<sizeof($_POST["list"]); $i++)
	{
		$id_contact=str_replace("contacts_", "", $_POST["list"][$i]);
		q("UPDATE cms_contacts SET ordering=".$i." WHERE id_contact=".$id_contact.";", $dbweb, __FILE__, __LINE__);
	}
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsContactEdit>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsContactEdit>'."\n";

?>