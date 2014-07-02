<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["list"]) )
	{
		echo '<ContactsLocationSort>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Standort-Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Standort-Liste übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsLocationSort>'."\n";
		exit;
	}

	for($i=1; $i<sizeof($_POST["list"]); $i++)
	{
		$id_location=str_replace("locations_", "", $_POST["list"][$i]);
		q("UPDATE cms_contacts_locations SET ordering=".$i." WHERE id_location=".$id_location.";", $dbweb, __FILE__, __LINE__);
	}
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsLocationSort>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsLocationSort>'."\n";

?>