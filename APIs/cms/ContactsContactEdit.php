<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["firstname"]) )
	{
		echo '<ContactsContactEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Vorname nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Vorname des Kontakts muss übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactEdit>'."\n";
		exit;
	}

	if ( $_POST["firstname"]=="" )
	{
		echo '<ContactsContactEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Vorname leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Vorname des Kontakts darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactEdit>'."\n";
		exit;
	}

	if ( !isset($_POST["lastname"]) )
	{
		echo '<ContactsContactEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Nachname nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Nachname des Kontakts muss übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactEdit>'."\n";
		exit;
	}

	if ( $_POST["lastname"]=="" )
	{
		echo '<ContactsContactEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Nachname leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Nachname des Kontakts darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactEdit>'."\n";
		exit;
	}

	$data=array();
	// GET ID USER
	$data["idCmsUser"]=0;
	$results=q("SELECT * FROM cms_users WHERE usermail='".mysqli_real_escape_string($dbweb,$_POST["mail"])."' LIMIT 1", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($results)>0)
	{
		$row=mysqli_fetch_array($results);
		$data["idCmsUser"]=$row["id_user"];
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
	
	q_update("cms_contacts", $data, "WHERE id_contact=".$_POST["id_contact"], $dbweb, __FILE__, __LINE__);
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsContactEdit>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsContactEdit>'."\n";

?>