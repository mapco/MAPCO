<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_location"]) )
	{
		echo '<ContactsLocationRemove>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Standort-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Standort-ID übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsLocationRemove>'."\n";
		exit;
	}

	$results=q("SELECT * FROM cms_contacts_departments WHERE location_id=".$_POST["id_location"].";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		q("DELETE FROM cms_contacts WHERE department_id=".$row["id_department"].";", $dbweb, __FILE__, __LINE__);
	}
	q("DELETE FROM cms_contacts_departments WHERE location_id=".$_POST["id_location"].";", $dbweb, __FILE__, __LINE__);
	q("DELETE FROM cms_contacts_locations WHERE id_location=".$_POST["id_location"].";", $dbweb, __FILE__, __LINE__);

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsLocationRemove>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsLocationRemove>'."\n";

?>