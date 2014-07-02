<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["location"]) )
	{
		echo '<ContactsLocationAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Standort-Name nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Standort-Name übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsLocationAdd>'."\n";
		exit;
	}

	if ( $_POST["location"]=="" )
	{
		echo '<ContactsLocationAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Standort-Name leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Standort-Name darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsLocationAdd>'."\n";
		exit;
	}

	//insert
	$insertdata=$_POST;
	unset($insertdata["API"]);
	unset($insertdata["Action"]);
	$results=q("SELECT * FROM cms_contacts_locations WHERE site_id=".$_SESSION["id_site"].";", $dbweb, __FILE__, __LINE__);
	$insertdata["ordering"]=mysqli_num_rows($results)+1;
	$insertdata["site_id"]=$_SESSION["id_site"];
	$insertdata["firstmod"]=time();
	$insertdata["firstmod_user"]=$_SESSION["id_user"];
	$insertdata["lastmod"]=time();
	$insertdata["lastmod_user"]=$_SESSION["id_user"];
	q_insert("cms_contacts_locations", $insertdata, $dbweb, __FILE__, __LINE__);

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;

	echo '<ContactsLocationAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsLocationAdd>'."\n";

?>