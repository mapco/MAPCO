<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_location"]) )
	{
		echo '<ContactsLocationEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Standort-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Standort-ID übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsLocationEdit>'."\n";
		exit;
	}

	if ( !isset($_POST["location"]) )
	{
		echo '<ContactsLocationEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Standort-Name nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Standort-Name übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsLocationEdit>'."\n";
		exit;
	}

	if ( $_POST["location"]=="" )
	{
		echo '<ContactsLocationEdit>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Standort-Name leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Standort-Name darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsLocationEdit>'."\n";
		exit;
	}

	$updatedata=$_POST;
	unset($updatedata["API"]);
	unset($updatedata["Action"]);
	unset($updatedata["id_location"]);
	$updatedata["site_id"]=$_SESSION["id_site"];
	$updatedata["lastmod"]=time();
	$updatedata["lastmod_user"]=$_SESSION["id_user"];
	q_update("cms_contacts_locations", $updatedata, "WHERE id_location=".$_POST["id_location"], $dbweb, __FILE__, __LINE__);
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsLocationEdit>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ContactsLocationEdit>'."\n";

?>