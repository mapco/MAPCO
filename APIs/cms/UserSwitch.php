<?php

	if( isset($_SESSION["id_real_user"]) )
	{
		echo '<UserSwitchResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Wechsel bereits vollzogen.</shortMsg>'."\n";
		echo '		<longMsg>Die aktuelle Session hat bereits den Benutzer gewechselt. Erst nach dem Aufheben des Benutzerwechsels kann erneut gewechselt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserSwitchResponse>'."\n";
		exit;
	}

	if( $_SESSION["userrole_id"]!=1 )
	{
		echo '<UserSwitchResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Berechtigung (id_userrole=1) fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Der aktuelle Benutzer muss Administrator sein, um auf einen anderen Benutzer wechseln zu können.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserSwitchResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_user"]) )
	{
		echo '<UserSwitchResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Benutzer (id_user) fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Der Benutzer (id_user), zu dem gewechselt werden soll, konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserSwitchResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM cms_users WHERE id_user=".$_POST["id_user"].";", $dbweb, __FILE__, __LINE__);	
	if( mysqli_num_rows($results)==0 )
	{
		echo '<UserSwitchResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Benutzer (id_user) unbekannt.</shortMsg>'."\n";
		echo '		<longMsg>Der Benutzer (id_user), zu dem gewechselt werden soll, ist nicht bekannt.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserSwitchResponse>'."\n";
		exit;
	}
	$user=mysqli_fetch_array($results);

	$results=q("SELECT * FROM cms_users_sites WHERE user_id=".$_POST["id_user"]." AND site_id=".$_SESSION["id_site"].";", $dbweb, __FILE__, __LINE__);	
	if( mysqli_num_rows($results)==0 )
	{
		echo '<UserSwitchResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Seite ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Der Benutzer (id_user) ist auf dieser Seite nicht bekannt. Bitte wählen Sie eine Seite aus, auf der der Benutzer sich einloggen darf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserSwitchResponse>'."\n";
		exit;
	}
	
	$_SESSION["id_real_user"]=$_SESSION["id_user"];
	$_SESSION["id_user"]=$user["id_user"];
	$_SESSION["userrole_id"]=$user["userrole_id"];

	echo '<UserSwitchResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</UserSwitchResponse>'."\n";

?>