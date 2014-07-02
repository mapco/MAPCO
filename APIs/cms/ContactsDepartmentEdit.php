<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_department"]) )
	{
		echo '<ContactsDepartmentEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Abteilungs-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Abteilungs-ID muss übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsDepartmentEdit>'."\n";
		exit;
	}

	if ( !isset($_POST["department"]) )
	{
		echo '<ContactsDepartmentEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Titel der Abteilung muss übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsDepartmentEdit>'."\n";
		exit;
	}

	if ( $_POST["department"]=="" )
	{
		echo '<ContactsDepartmentEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Titel der Abteilung darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsDepartmentEdit>'."\n";
		exit;
	}

	q("UPDATE cms_contacts_departments SET department='".mysqli_real_escape_string($dbweb,$_POST["department"])."' WHERE id_department=".$_POST["id_department"].";", $dbweb, __FILE__, __LINE__);
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsDepartmentEdit>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsDepartmentEdit>'."\n";

?>