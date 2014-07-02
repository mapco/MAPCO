<?php
	if ( !isset($_POST["ip"]) )
	{
		echo '<ConnectionsBlacklistRemove>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>IP nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine IP-Adresse (ip) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ConnectionsBlacklistRemove>'."\n";
		exit;
	}

	q("DELETE FROM cms_connections_blacklist WHERE ip=".$_POST["ip"].";", $dbweb, __FILE__, __LINE__);
	
	echo '<ConnectionsBlacklistRemove>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ConnectionsBlacklistRemove>'."\n";

?>