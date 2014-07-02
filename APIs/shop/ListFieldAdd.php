<?php

	if ( !isset($_POST["list_id"]) )
	{
		echo '<FieldAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Listen-ID übergeben werden, damit der Service weiß, zu welcher Liste die Spalte hinzugefügt werden soll.</longMsg>'."\n";
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

	//get ordering
	$results=q("SELECT * FROM shop_lists_fields WHERE list_id=".$_POST["list_id"].";", $dbshop, __FILE__, __LINE__);
	$ordering=mysqli_num_rows($results)+1;
	
	//add column	
	q("INSERT INTO shop_lists_fields (list_id, field_id, value_id, title, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["list_id"].", ".$_POST["field_id"].", '".$_POST["value_id"]."', '".$_POST["title"]."', ".$ordering.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<FieldAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</FieldAddResponse>'."\n";

?>