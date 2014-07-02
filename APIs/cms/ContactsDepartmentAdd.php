<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["location_id"]) )
	{
		echo '<ContactsDepartmentAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Die ID des Standorts darf nicht leer sein.</shortMsg>'."\n";
		echo '		<longMsg>Die ID des Standorts darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsDepartmentAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["department"]) )
	{
		echo '<ContactsDepartmentAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Titel der Abteilung darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsDepartmentAdd>'."\n";
		exit;
	}

	$results=q("SELECT * FROM cms_contacts_departments WHERE location_id=".$_POST["location_id"]." ORDER BY ordering DESC", $dbweb, __FILE__, __LINE__);
	$ordering=mysqli_num_rows($results)+1;
	q("INSERT INTO cms_contacts_departments (department, location_id, ordering) VALUES('".mysqli_real_escape_string($dbweb,$_POST["department"])."', ".$_POST["location_id"].", ".$ordering.");", $dbweb, __FILE__, __LINE__);
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsDepartmentAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsDepartmentAdd>'."\n";

?>