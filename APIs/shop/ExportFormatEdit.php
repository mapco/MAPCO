<?php

	if ( !isset($_POST["id_exportformat"]) )
	{
		echo '<ExportFormatEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Exportformat-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Exportformat-ID übergeben werden, damit der Service weiß, welches Exportformat aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ExportFormatEditResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["title"]) )
	{
		echo '<ExportFormatEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel übergeben werden, damit der Service weiß, welcher neue Titel eingetragen werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ExportFormatEditResponse>'."\n";
		exit;
	}
	
	//update exportformat
	q("UPDATE shop_export_formats SET title='".$_POST["title"]."' WHERE id_exportformat=".$_POST["id_exportformat"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ExportFormatEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ExportFormatEditResponse>'."\n";

?>