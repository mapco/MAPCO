<?php

	if ( !isset($_POST["field_id"]) )
	{
		echo '<ValueAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Feld-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Feld-ID übergeben werden, damit der Service weiß, zu welchem Feld ein Wert gespeichert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ValueAddResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["title"]) )
	{
		echo '<ValueAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel übergeben werden, damit der Service weiß, welchen Titel der Wert haben soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ValueAddResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["value"]) )
	{
		echo '<ValueAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Wert nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Wert übergeben werden, damit der Service weiß, welchen internen Wert der Wert haben soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ValueAddResponse>'."\n";
		exit;
	}
	
	//get ordering
	$results=q("SELECT * FROM shop_fields_values WHERE field_id=".$_POST["field_id"].";", $dbshop, __FILE__, __LINE__);
	$ordering=mysqli_num_rows($results)+1;
	
	//add field
	q("INSERT INTO shop_fields_values (field_id, title, `value`, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["field_id"].", '".mysqli_real_escape_string($dbshop, $_POST["title"])."', '".mysqli_real_escape_string($dbshop, $_POST["value"])."', ".$ordering.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ValueAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ValueAddResponse>'."\n";

?>