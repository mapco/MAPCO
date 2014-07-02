<?php

	if ( !isset($_POST["id_field"]) )
	{
		echo '<ExportFieldEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Feld-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Feld-ID übergeben werden, damit der Service weiß, welches Exportfeld aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ExportFormatEditResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["name"]) )
	{
		echo '<ExportFieldEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Name nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Name übergeben werden, damit der Service weiß, welcher neue Name eingetragen werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ExportFieldEditResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["value"]) )
	{
		echo '<ExportFieldEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Wert nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Wert übergeben werden, damit der Service weiß, welcher neue Wert eingetragen werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ExportFieldEditResponse>'."\n";
		exit;
	}
	
	//update exportfield
	q("UPDATE shop_export_fields SET name='".mysqli_real_escape_string($dbshop, $_POST["name"])."', value='".mysqli_real_escape_string($dbshop, $_POST["value"])."' WHERE id_field=".$_POST["id_field"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ExportFieldEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ExportFieldEditResponse>'."\n";

?>