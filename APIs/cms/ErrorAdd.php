<?php
	if ( !isset($_POST["id_errortype"]) )
	{
		echo '<ErrorAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehlertyp nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Fehlertyp (id_errortype) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ErrorAddResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_errorcode"]) )
	{
		echo '<ErrorAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Fehler (id_error) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ErrorAddResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["file"]) )
	{
		echo '<ErrorAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Fehler (id_error) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ErrorAddResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["line"]) )
	{
		echo '<ErrorAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Fehler (id_error) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ErrorAddResponse>'."\n";
		exit;
	}
	
	if( !isset($_POST["text"]) ) $_POST["text"]="";

	q("INSERT INTO cms_errors (errortype_id, error_id, file, line, text, time) VALUES(".$_POST["id_errortype"].", ".$_POST["id_errorcode"].", '".mysqli_real_escape_string($dbweb,$_POST["file"])."', ".$_POST["line"].", '".mysqli_real_escape_string($dbweb,$_POST["text"])."', ".time().");", $dbweb, __FILE__, __LINE__);

	echo '<ErrorAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ErrorAddResponse>'."\n";

?>