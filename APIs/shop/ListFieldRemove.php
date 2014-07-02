<?php

	if ( !isset($_POST["id"]) )
	{
		echo '<ListFieldRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Zeilen-ID übergeben werden, damit der Service weiß, welche Zeile gelöscht werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListFieldRemoveResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_lists_fields WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ListFieldRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Zeile nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine gültige Zeilen-ID übergeben werden, damit der Service weiß, welche Zeile gelöscht werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListFieldRemoveResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	
	//reorder fields
	$results=q("SELECT * FROM shop_lists_fields WHERE list_id=".$row["list_id"]." AND NOT id=".$_POST["id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	$i=0;
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		if ( $row["ordering"]!=$i )
		{
			q("UPDATE shop_lists_fields SET ordering=".$i." WHERE id=".$row["id"].";", $dbshop, __FILE__, __LINE__);
		}
	}

	//remove field
	q("DELETE FROM shop_lists_fields WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ListFieldRemoveResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ListFieldRemoveResponse>'."\n";

?>