<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_department"]) )
	{
		echo '<ContactsDepartmentRemove>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Abteilungs-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Abteilungs-ID muss übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsDepartmentRemove>'."\n";
		exit;
	}

	q("DELETE FROM cms_contacts_departments WHERE id_department=".$_POST["id_department"].";", $dbweb, __FILE__, __LINE__);

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsDepartmentRemove>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsDepartmentRemove>'."\n";
?>