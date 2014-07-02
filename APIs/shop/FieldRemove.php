<?php

	if ( !isset($_POST["id_field"]) )
	{
		echo '<FieldRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Feld-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Feld-ID übergeben werden, damit der Service weiß, welches Feld gelöscht werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FieldRemoveResponse>'."\n";
		exit;
	}
	
	//reorder fields
	$results=q("SELECT * FROM shop_fields WHERE NOT id_field=".$_POST["id_field"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	$i=0;
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		if ( $row["ordering"]!=$i )
		{
			q("UPDATE shop_fields SET ordering=".$i." WHERE id_field=".$row["id_field"].";", $dbshop, __FILE__, __LINE__);
		}
	}

	//remove field
	q("DELETE FROM shop_fields WHERE id_field=".$_POST["id_field"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<FieldRemoveResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</FieldRemoveResponse>'."\n";

?>