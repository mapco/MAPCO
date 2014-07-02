<?php

include("config.php");
include("functions/shop_get_price.php");
include("functions/cms_t.php");
include("functions/mapco_baujahr.php");

$lang = 'de';


$results=q("SELECT * FROM t_400 WHERE ArtNr='6503' AND SortNr=1;", $dbshop, __FILE__, __LINE__);
while ($row=mysql_fetch_array($results))
{
	//PKW
	if ($row["KritNr"]==2)
	{
		$results2=q("SELECT * FROM fahrz WHERE KTypNr=".$row["KritWert"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		print_r($row);
		print_r($row2);
		if ($row2["f_ID"]!="")
		{
			$fid[$row2["f_ID"]]=$row2["f_ID"];
			$bez1[$row2["f_ID"]]=$row2["BEZ1"];
			$bez2[$row2["f_ID"]]=$row2["BEZ2"];
			$bez3[$row2["f_ID"]]=$row2["BEZ3"];
			$bjvon[$row2["f_ID"]]=$row2["BJvon"];
			$bjbis[$row2["f_ID"]]=$row2["BJbis"];
			$ktypnr[$row2["f_ID"]]=(int)$row2["KTypNr"];
			$results3=q("SELECT KBANr FROM t_121 WHERE KTypNr=".$row2["KTypNr"].";", $dbshop, __FILE__, __LINE__);
			$row3=mysql_fetch_array($results3);
			$kbanr[$row2["f_ID"]]=substr($row3["KBANr"], 0, 4).'-'.substr($row3["KBANr"], 4, 3);
			$hubraum[$row2["f_ID"]]=number_format($row2["ccmTech"]).'ccm';
			$kw[$row2["f_ID"]]=number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)';
		}
	}
}

//sort by name
//array_multisort($bez1, $bez2, $bez3, $bjvon, $bjbis, $fid, $ktypnr, $kbanr, $hubraum, $kw);

if (sizeof($fid)>0)
{
	echo '<h1>'.t("Fahrzeugzuordnungen", $lang).'</h1>';
	echo '<table class="hover">';
	echo '<tr>';
	echo '	<th>'.t("Fahrzeug", $lang).'</th>';
	echo '	<th width="120">'.t("Baujahr", $lang).'</th>';
	echo '	<th width="100">'.t("Leistung", $lang).'</th>';
	echo '	<th width="80">'.t("Hubraum", $lang).'</th>';
	echo '	<th width="80">'.t("KBA-Nr.", $lang).'</th>';
	echo '</tr>';
	for($i=0; $i<sizeof($fid); $i++)
	{
		echo '<tr>';
		echo '	<td><a href="http://www.mapco.de/shop_searchbycar.php?lang='.$lang.'&id_vehicle='.$fid[$i].'">'.$bez1[$i].' '.$bez2[$i].' '.$bez3[$i].'</a>';
		if (sizeof($kritnr[$ktypnr[$i]])>0)
		{
			for($j=0; $j<sizeof($kritnr[$ktypnr[$i]]); $j++)
			{
				echo '<br /><span style="color:#FF0000"><i>'.$kritnr[$ktypnr[$i]][$j].': '.$kritwert[$ktypnr[$i]][$j].'</i></span>';
			}
		}
		echo '</td>';
		echo '	<td>';
		echo baujahr($bjvon[$i]).' - '.baujahr($bjbis[$i]);
		echo '</td>';
		echo '<td>'.$kw[$i].'</td>';
		echo '<td>'.$hubraum[$i].'</td>';
		echo '<td>'.$kbanr[$i].'</td>';
		echo '</tr>';
	}
	echo '</table>';
}


?>