<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["list"]) )
	{
		echo '<ContactsDepartmentSort>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Abteilungs-Liste icht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Abteilungs-Liste übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsDepartmentSort>'."\n";
		exit;
	}

	for($i=1; $i<sizeof($_POST["list"]); $i++)
	{
		$id_department=str_replace("departments_", "", $_POST["list"][$i]);
		q("UPDATE cms_contacts_departments SET ordering=".$i." WHERE id_department=".$id_department.";", $dbweb, __FILE__, __LINE__);
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsDepartmentSort>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsDepartmentSort>'."\n";

?>