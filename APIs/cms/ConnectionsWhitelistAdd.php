<?php
	if ( !isset($_POST["ip"]) )
	{
		echo '<ConnectionsWhitelistAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>IP nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine IP-Adresse (ip) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ConnectionsWhitelistAdd>'."\n";
		exit;
	}

	if( !($_POST["ip"]>0) )
	{
		echo '<ConnectionsWhitelistAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>IP ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Die angegebene IP-Adresse (ip) entspricht nicht dem gültigen Format für IP-Adressen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ConnectionsWhitelistAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["name"]) )
	{
		echo '<ConnectionsWhitelistAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Name nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Name (name) zur IP-Adresse übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ConnectionsWhitelistAdd>'."\n";
		exit;
	}

	if ( $_POST["name"]=="" )
	{
		echo '<ConnectionsWhitelistAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Name ist leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Name (name) darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ConnectionsWhitelistAdd>'."\n";
		exit;
	}

	$results=q("SELECT * FROM cms_connections_whitelist WHERE ip=".$_POST["ip"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		echo '<ConnectionsWhitelistAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>IP bereits auf der Whitelist.</shortMsg>'."\n";
		echo '		<longMsg>Eine IP-Adresse (ip) kann nur einmal auf der Whitelist eingetragen werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ConnectionsWhitelistAdd>'."\n";
		exit;
	}
	
	q("INSERT INTO cms_connections_whitelist (ip, name, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["ip"].", '".mysqli_real_escape_string($dbweb,$_POST["name"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	q("DELETE FROM cms_connections_blacklist WHERE ip=".$_POST["ip"].";", $dbweb, __FILE__, __LINE__);
	
	echo '<ConnectionsWhitelistAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ConnectionsWhitelistAdd>'."\n";

?>