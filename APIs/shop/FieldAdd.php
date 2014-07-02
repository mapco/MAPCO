<?php

	if ( !isset($_POST["title"]) )
	{
		echo '<FieldAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel übergeben werden, damit der Service weiß, welchen Titel das Feld haben soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FieldAddResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["name"]) )
	{
		echo '<FieldAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Name nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Wert übergeben werden, damit der Service weiß, welchen internen Namen das Feld haben soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FieldAddResponse>'."\n";
		exit;
	}
	
	//get ordering
	$results=q("SELECT * FROM shop_fields;", $dbshop, __FILE__, __LINE__);
	$ordering=mysqli_num_rows($results)+1;
	
	//add field
	q("INSERT INTO shop_fields (title, name, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".mysqli_real_escape_string($dbshop, $_POST["title"])."', '".mysqli_real_escape_string($dbshop, $_POST["name"])."', ".$ordering.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<FieldAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</FieldAddResponse>'."\n";

?>