<?php

	if ( !isset($_POST["id_value"]) )
	{
		echo '<ValueRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Feld-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Feld-ID übergeben werden, damit der Service weiß, welches Feld gelöscht werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ValueRemoveResponse>'."\n";
		exit;
	}

	//reorder values
	$results=q("SELECT * FROM shop_fields_values WHERE id_value=".$_POST["id_value"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$results=q("SELECT * FROM shop_fields_values WHERE NOT id_value=".$_POST["id_value"]." AND field_id=".$row["field_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	$i=0;
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		if ( $row["ordering"]!=$i )
		{
			q("UPDATE shop_fields_values SET ordering=".$i." WHERE id_value=".$row["id_value"].";", $dbshop, __FILE__, __LINE__);
		}
	}

	//remove value
	q("DELETE FROM shop_fields_values WHERE id_value=".$_POST["id_value"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ValueRemoveResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ValueRemoveResponse>'."\n";

?>