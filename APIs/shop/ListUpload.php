<?php

	$artnr2id=array();
	$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$artnr2id[$row["MPN"]]=$row["id_item"];
	}

	$ItemID=array();
	$handle=fopen($_FILES["Filedata"]["tmp_name"], "r");
	$line=fgetcsv($handle, 4096, ";");
	$i=0;
	while( $line=fgetcsv($handle, 4096, ";") )
	{
		if ( isset($artnr2id[$line[0]]) )
		{
			$ItemID[]=$artnr2id[$line[0]];
		}
	}

	//return success
	echo '<ListUploadResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Filename>'.$_FILES["Filedata"]["name"].'</Filename>'."\n";
	for($i=0; $i<sizeof($ItemID); $i++)
	{
		echo '	<ItemID>'.$ItemID[$i].'</ItemID>'."\n";
	}
	echo '</ListUploadResponse>'."\n";

?>