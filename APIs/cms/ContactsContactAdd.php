<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["department_id"]) )
	{
		echo '<ContactsContactAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Abteilungs-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Abteilungs-ID muss übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["firstname"]) )
	{
		echo '<ContactsContactAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Vorname nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Vorname des Kontakts muss übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactAdd>'."\n";
		exit;
	}

	if ( $_POST["firstname"]=="" )
	{
		echo '<ContactsContactAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Vorname leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Vorname des Kontakts darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["lastname"]) )
	{
		echo '<ContactsContactAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Nachname nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Nachname des Kontakts muss übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactAdd>'."\n";
		exit;
	}

	if ( $_POST["lastname"]=="" )
	{
		echo '<ContactsContactAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Nachname leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Nachname des Kontakts darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactAdd>'."\n";
		exit;
	}

	$data=array();
	//GET ORDERING
	$results=q("SELECT * FROM cms_contacts WHERE department_id=".$_POST["department_id"]." ORDER BY ordering DESC", $dbweb, __FILE__, __LINE__);
	$data["ordering"]=mysqli_num_rows($results)+1;
	// GET ID USER
	$data["idCmsUser"]=0;
	$results=q("SELECT * FROM cms_users WHERE usermail='".mysqli_real_escape_string($dbweb,$_POST["mail"])."' LIMIT 1", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($results)>0)
	{
		$row=mysqli_fetch_array($results);
		$data["idCmsUser"]=$row["id_user"];
	}
	else
	{
		echo '<ContactsContactAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>User nicht bekannt</shortMsg>'."\n";
		echo '		<longMsg>Der Kontakt muss zuerst als Shopuser angelegt werden!</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactAdd>'."\n";
		exit;
	}
	
	$data["firstname"]=$_POST["firstname"];
	$data["lastname"]=$_POST["lastname"];
	$data["position"]=$_POST["position"];
	$data["languages"]=$_POST["languages"];
	$data["phone"]=$_POST["phone"];
	$data["fax"]=$_POST["fax"];
	$data["mobile"]=$_POST["mobile"];
	$data["mail"]=$_POST["mail"];
	$data["gender"]=$_POST["gender"];
	$data["active"]=$_POST["active"];
	$data["department_id"]=$_POST["department_id"];
	
	q_insert("cms_contacts", $data, $dbweb, __FILE__, __LINE__);
	
	
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsContactAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsContactAdd>'."\n";

?>