<?php
	if( !isset($_POST["id"]) )
	{
		echo '<DPDImportFailureResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>DPD-Importnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine DPD-Importnummer (id) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</DPDImportFailureResponse>'."\n";
		exit;
	}

	q("UPDATE dpd_import SET imported=1 WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	
	echo '<DPDImportFailureResponse>';
	echo '	<Ack>Success</Ack>';
	echo '</DPDImportFailureResponse>';
	
?>