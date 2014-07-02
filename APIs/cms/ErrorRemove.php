<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_errortype"]) )
	{
		echo '<ErrorRemoveResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehlertyp nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Fehlertyp (id_errortype) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ErrorRemoveResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_error"]) )
	{
		echo '<ErrorRemoveResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Fehler (id_error) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ErrorRemoveResponse>'."\n";
		exit;
	}

	q("DELETE FROM cms_errors WHERE errortype_id=".$_POST["id_errortype"]." AND error_id=".$_POST["id_error"].";", $dbweb, __FILE__, __LINE__);

	echo '<ErrorRemoveResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ErrorRemoveResponse>'."\n";

?>