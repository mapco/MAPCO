<?php
	if ( !isset($_POST["id"]) )
	{
		echo '<FieldAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID übergeben werden, damit der Service weiß, welche Spalte hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FieldAddResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["field_id"]) )
	{
		echo '<FieldAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Feld-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Feld-ID übergeben werden, damit der Service weiß, welche Spalte hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FieldAddResponse>'."\n";
		exit;
	}

	$data=$_POST;
	unset($data["API"]);
	unset($data["APIRequest"]);

	//update column
	q_update("shop_lists_fields", $data, "WHERE id=".$_POST["id"], $dbshop, __FILE__, __LINE__);

?>