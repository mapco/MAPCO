<?php

	if ( !isset($_POST["id_exportformat"]) )
	{
		echo '<ExportFormatEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Exportformat-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Exportformat-ID übergeben werden, damit der Service weiß, welches Exportformat gelöscht werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ExportFormatEditResponse>'."\n";
		exit;
	}
	
	//delete exportformat
	q("DELETE FROM shop_export_fields WHERE exportformat_id=".$_POST["id_exportformat"].";", $dbshop, __FILE__, __LINE__);
	q("DELETE FROM shop_export_formats WHERE id_exportformat=".$_POST["id_exportformat"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ExportFormatEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ExportFormatEditResponse>'."\n";

?>