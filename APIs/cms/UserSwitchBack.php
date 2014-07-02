<?php

	if( !isset($_SESSION["id_real_user"]) )
	{
		echo '<UserSwitchResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kein Benutzerwechsel erkannt.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein Ursprungsbenutzer (id_real_user) in der aktuellen Session gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserSwitchResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_real_user"].";", $dbweb, __FILE__, __LINE__);	
	if( mysqli_num_rows($results)==0 )
	{
		echo '<UserSwitchResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Benutzer (id_real_user) unbekannt.</shortMsg>'."\n";
		echo '		<longMsg>Der Benutzer (id_real_user), zu dem zurück gewechselt werden soll, ist nicht bekannt.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserSwitchResponse>'."\n";
		exit;
	}
	$user=mysqli_fetch_array($results);
	
	$_SESSION["id_user"]=$user["id_user"];
	$_SESSION["userrole_id"]=$user["userrole_id"];
	unset($_SESSION["id_real_user"]);

	echo '<UserSwitchResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</UserSwitchResponse>'."\n";

?>