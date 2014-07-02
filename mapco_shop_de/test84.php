<?php
	/***********************************
	 * export all vehicle applications *
	 ***********************************/
	include("config.php");
	require_once("functions/mapco_baujahr.php");
	
	if ( !isset($_GET["ArtNr"]) ) $mode="w"; else $mode="a+";
	$handle=fopen("amazonuk_fitments.csv", $mode);
	
	if ( !isset($_GET["ArtNr"]) ) fwrite($handle, "EAN;Vehicle;Year of manufacture;Engine Output\n");

	$j=0;
	$results=q("SELECT * FROM shop_items LIMIT 5000, 100000;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$j++;
		if ( !isset($_GET["ArtNr"]) or $_GET["ArtNr"]==$row["MPN"] )
		{
			$i=0;
			$results2=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$row["id_item"]." AND language_id=1;", $dbshop, __FILE__, __LINE__);
			while($row2=mysqli_fetch_array($results2))
			{
				$results3=q("SELECT * FROM vehicles_en WHERE id_vehicle=".$row2["vehicle_id"].";", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				if ( $i==0 ) $ean=$row["EAN"]; else $ean='';
				fwrite($handle, '"'.$ean.'";"'.utf8_decode($row3["BEZ1"]).' '.utf8_decode($row3["BEZ2"]).' '.utf8_decode($row3["BEZ3"]).'";"'.utf8_decode(baujahr($row3["BJvon"])).' - '.utf8_decode(baujahr($row3["BJbis"])).'";"'.($row3["kW"]*1).'kW ('.($row3["PS"]*1).'PS)"'."\n");
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
		echo '	<meta http-equiv="refresh" content="0;url=test84.php?ArtNr='.$row["MPN"].'">';
		echo '	<title>AmazonUK-Fitment-Export</title>';
		echo '</head>';
		echo '<body>';
		echo $j.' fertig';
		echo '</body></html>';
	}
	else
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml">';
		echo '<head>';
		echo '	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '	<title>AmazonUK-Fitment-Export</title>';
		echo '</head>';
		echo '<body>';
		echo 'Alle Fahrzeugdetails erfolgreich exportiert.';
		echo '</body></html>';
	}
?>