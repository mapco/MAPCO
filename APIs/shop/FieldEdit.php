<?php

	if ( !isset($_POST["id_field"]) )
	{
		echo '<FieldEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Feld-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Feld-ID übergeben werden, damit der Service weiß, welches Feld aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FieldEditResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["title"]) )
	{
		echo '<FieldEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel übergeben werden, damit der Service weiß, welchen Titel das Feld haben soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FieldEditResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["name"]) )
	{
		echo '<FieldEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Name nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Wert übergeben werden, damit der Service weiß, welchen internen Namen das Feld haben soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FieldEditResponse>'."\n";
		exit;
	}
	
	//update field
	q("	UPDATE shop_fields
		SET title='".mysqli_real_escape_string($dbshop, $_POST["title"])."',
			name='".mysqli_real_escape_string($dbshop, $_POST["name"])."',
			lastmod=".time().",
			lastmod_user=".$_SESSION["id_user"]."
		WHERE id_field=".$_POST["id_field"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<FieldEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</FieldEditResponse>'."\n";

?>