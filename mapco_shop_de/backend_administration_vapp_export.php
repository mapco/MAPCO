<?php
	/***********************************
	 * export all vehicle applications *
	 ***********************************/
	include("config.php");
	require_once("functions/mapco_baujahr.php");
	
	if(!isset($_GET["language"]))
	{
		echo '<form>';
		echo '<select name="language">';
		$results=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			echo '<option value="'.$row["code"].'">'.$row["language"].'</option>';
		}
		echo '</select>';
		echo '<input type="submit" value="Fahrzeugdaten exportieren" />';
		echo '</form>';
		exit;
	}
	
	if ( !isset($_GET["ArtNr"]) ) $mode="w"; else $mode="a+";
	$handle=fopen("fitments.csv", $mode);
	
	if ( !isset($_GET["ArtNr"]) )
	{
		if( $_GET["language"]=="de" ) fwrite($handle, "MAPCO ArtNr;EAN-Strichcode;Fahrzeug;Baujahr;kW (PS);KBA\n");
		else fwrite($handle, "MAPCO ArtNr;EAN;Vehicle;Year of manufacture;Engine Output\n");
	}

	$j=0;
	$results=q("SELECT * FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$j++;
		if ( !isset($_GET["ArtNr"]) or $_GET["ArtNr"]==$row["MPN"] )
		{
			$i=0;
			$results2=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$row["id_item"]." AND language_id=1;", $dbshop, __FILE__, __LINE__);
			while($row2=mysqli_fetch_array($results2))
			{
				$results3=q("SELECT * FROM vehicles_".$_GET["language"]." WHERE id_vehicle=".$row2["vehicle_id"].";", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$ArtNr='';
				$ean='';
				if ( $i==0 )
				{
					$ArtNr=$row["MPN"];
					$ean=$row["EAN"];
				}
				$KBA='';
				if( $_GET["language"]=="de" ) $KBA=$row3["KBA"];
				fwrite($handle, '"'.$ArtNr.'";"'.$ean.'";"'.utf8_decode($row3["BEZ1"]).' '.utf8_decode($row3["BEZ2"]).' '.utf8_decode($row3["BEZ3"]).'";"'.utf8_decode(baujahr($row3["BJvon"])).' - '.utf8_decode(baujahr($row3["BJbis"])).'";"'.($row3["kW"]*1).'kW ('.($row3["PS"]*1).'PS)";"'.$KBA.'"'."\n");
				$i++;
			}
			break;
		}
	}
	fclose($handle);
	if ($row=mysqli_fetch_array($results))
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml">';
		echo '<head>';
		echo '	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '	<meta http-equiv="refresh" content="0;url=?ArtNr='.$row["MPN"].'&language='.$_GET["language"].'">';
		echo '	<title>Fahrzeugapplikationsexport</title>';
		echo '</head>';
		echo '<body>';
		echo $j.' von '.mysqli_num_rows($results).' fertig';
		echo '</body></html>';
	}
	else
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml">';
		echo '<head>';
		echo '	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '	<title>Fahrzeugapplikationsexport</title>';
		echo '</head>';
		echo '<body>';
		echo '<a href="'.PATH.'fitments.csv">Alle Fahrzeugdetails erfolgreich exportiert.</a>';
		echo '</body></html>';
	}
?>