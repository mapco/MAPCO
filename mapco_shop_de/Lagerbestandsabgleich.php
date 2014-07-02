<?php

	include("config.php");
	
	//ACTIVE AUS SHOP_ITEMS
	$res=q("SELECT * FROM shop_items WHERE active > 0;", $dbshop, __FILE__ , __LINE__);
	while($row=mysqli_fetch_array($res))
	{
		$articles[$row["MPN"]]=$row["id_item"];
	}
	
	//LAGERLISTE
	$res=q("SELECT * FROM lager;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		if (isset($articles[$row["ArtNr"]]))
		{
			$lager[$row["ArtNr"]]=$row["ISTBESTAND"]+$row["MOCOMBESTAND"];
		}
	}
	
	//LAGERLISTE RC
	$res=q("SELECT * FROM lagerrc;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		if (isset($articles[$row["ARTNR"]]))
		{
			if ($row["RCBEZ"]!="LYON/CHASSIEU" && $row["RCBEZ"]!="DEPOT CADIZ" && $row["RCBEZ"]!="DEPOT MILANO") {
				$lagerrc[$row["ARTNR"]][$row["RCBEZ"]]=$row["ISTBESTAND"];
			}
		}
	}
$counter=0;

while (list($MPN, $id_item) = each ($articles))
{
	if ($lager[$MPN]==0)
	{
		if (isset($lagerrc[$MPN]))
		{
			$text = $MPN." ZENTRALLAGER 0";
			{
				$tmp_bestand=0;
				while(list($rcbez, $rcbestand) = each ($lagerrc[$MPN]))
				{
					$text.= ", ".$rcbez." ".$rcbestand;
					$tmp_bestand+=$rcbestand;
					
				}
			}
			$text.= "<br />";
			if ($tmp_bestand>39)
			{
				$counter++;
				echo $text;
			}
		}
	}
}

echo "ANZAHL: ".$counter;
?>