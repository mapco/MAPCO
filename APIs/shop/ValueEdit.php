<?php

	if ( !isset($_POST["id_value"]) )
	{
		echo '<ValueEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Wert-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Wert-ID übergeben werden, damit der Service weiß, welcher Wert aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ValueEditResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["title"]) )
	{
		echo '<ValueEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel übergeben werden, damit der Service weiß, welchen Titel das Feld haben soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ValueEditResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["value"]) )
	{
		echo '<ValueEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Wert nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Wert übergeben werden, damit der Service weiß, welchen internen Wert der Wert haben soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ValueEditResponse>'."\n";
		exit;
	}
	
	//update field
	q("	UPDATE shop_fields_values
		SET title='".mysqli_real_escape_string($dbshop, $_POST["title"])."',
			`value`='".mysqli_real_escape_string($dbshop, $_POST["value"])."',
			lastmod=".time().",
			lastmod_user=".$_SESSION["id_user"]."
		WHERE id_value=".$_POST["id_value"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ValueEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ValueEditResponse>'."\n";

?>