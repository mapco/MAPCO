<?php
	$starttime = time()+microtime();

	if($_POST["auto"]!=1)
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml">';
		echo '<head>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '<title>Artikeldaten-Aktualisierung | MAPCO Autotechnik GmbH</title>';
		echo '</head>';
		echo '<body>';
	}
	else
	{
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		echo '<UpdateArtNrResponse>'."\n";
	}

	$starttime=time()+microtime();
	include("../config.php");
//	include("../functions/shop_get_price.php");
	include("../functions/cms_t2.php");
	include("../functions/mapco_baujahr.php");
	
	//SprachNr
	$sprachnr=array("de" => "001",
				   "en" => "004",
				   "fr" => "006",
				   "it" => "007",
				   "es" => "008",
				   "nl" => "009",
				   "da" => "010",
				   "sv" => "011",
				   "no" => "012",
				   "fi" => "013",
				   "hu" => "014",
				   "pt" => "015",
				   "ru" => "016",
				   "sk" => "017",
				   "cs" => "018",
				   "pl" => "019",
				   "el" => "020",
				   "ro" => "021",
				   "tr" => "023",
				   "hr" => "024",
				   "sr" => "025",
				   "zh" => "004", //031
				   "bg" => "032",
				   "lv" => "033",
				   "lt" => "034",
				   "et" => "035",
				   "sl" => "036",
				   "qa" => "037",
				   "qb" => "038");


	//get languages
	$sprache=array();
	$results=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$sprache[sizeof($sprache)]=$row["code"];
	}
	
	
	//get codepages
	$codepage=array();
	$results=q("SELECT * FROM t_020;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$codepage[$row["ISOCode"]]=$row["Codepage"];
		if ($row["ISOCode"]=="en") $cp=$row["Codepage"];
	}
	$codepage["zh"]=$cp;


	function get_title($artnr, $lang)
	{
		global $dbshop;
		global $sprachnr;
		global $codepage;
		
		//Generische Bezeichnung
		$query="SELECT GART FROM t_200 WHERE t_200.ArtNr='".$artnr."';";
		$results=q($query, $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		if ($row["GART"] != "")
		{
			$query="SELECT BezNr FROM t_320 WHERE GenArtNr=".$row["GART"].";";
			$results2=q($query, $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			if ($row2["BezNr"] != "")
			{
				$query="SELECT Bez FROM t_030 WHERE SprachNr='".$sprachnr[$lang]."' AND BezNr=".$row2["BezNr"].";";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
			}
			else $row["Bez"] = "";
		}
		else $row["Bez"] = "";
		$bez=iconv("windows-".$codepage[$lang], "utf-8", utf8_decode($row["Bez"]));
		return($bez.' ('.$artnr.')');
	}
	
	function get_short_description($artnr, $lang)
	{
		global $dbshop;
		global $sprachnr;
		global $codepage;

		//criteria table
		$j=0;
		$criteria=array();
		$results2=q("SELECT KritNr, KritVal FROM t_210 WHERE ArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
		while ($row2=mysqli_fetch_array($results2))
		{
			$kritnr=$row2["KritNr"];
			$results3=q("SELECT BezNr, Typ FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			if ($row3["Typ"]=="K")
			{
				$results4=q("SELECT TabNr FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($results4);
				if (is_numeric($row2["KritVal"])) $results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl=".$row2["KritVal"].";", $dbshop, __FILE__, __LINE__);
				else $results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl='".$row2["KritVal"]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($results4);
				$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($results4);
				$kritwert=$row4["Bez"];
			}
			else $kritwert=$row2["KritVal"];
			
			$results3=q("SELECT Bez FROM t_030 WHERE BezNr=".$row3["BezNr"]." AND SprachNr=".$sprachnr[$lang].";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$bez=$row3["Bez"];
			
			//Einheiten ausschneiden
			$unit="";
			$start=strrpos($bez, " [");
			if ($start>0)
			{
				$end=strrpos($bez, "]")-$start;
				$unit=substr($bez, $start+2, $end-2);
				$bez=substr($bez, 0, $start);
				$kritwert.=$unit;
			}
			$criteria[$j][0]=iconv("windows-".$codepage[$lang], "utf-8", utf8_decode($bez));
			$criteria[$j][1]=iconv("windows-".$codepage[$lang], "utf-8", utf8_decode($kritwert));
			$j++;
		}
		$short_description="";
		for($i=0; $i<sizeof($criteria); $i++)
		{
			$short_description.=$criteria[$i][0].': '.$criteria[$i][1];
			if (($i+1)<sizeof($criteria)) $short_description.='; ';
		}
		return($short_description);
	}


	function get_description($artnr, $lang)
	{
		global $dbshop;
		global $sprachnr;
		global $codepage;
		
		$description='';
		
		$art_nr=array();
		$art_nr[]=$artnr;
		
		//Ersetzungen SA204
		$results=q("SELECT * FROM t_204 WHERE EArtNr='".$art_nr[0]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			$art_nr[]=$row["ArtNr"];
			$row=mysqli_fetch_array($results);
			$results2=q("SELECT b.id_item, b.title FROM shop_items AS a, shop_items_".$lang." AS b WHERE a.MPN='".$row["ArtNr"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$description .= '<div class="info"><a href="shop_item.php?lang='.$lang.'&id_item='.$row2["id_item"].'">Dieser Artikel wird künftig durch '.$row2["title"].' ersetzt!</a></div>';
		}
		
		
		//stücklisten SA205
		$results=q("SELECT PartNr, Menge FROM t_205 WHERE ArtNr='".$art_nr[0]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			$art_nr[]=$row["PartNr"];
			$description.='<h1>'.t("Stückliste", __FILE__, __LINE__).'</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("Artikelnummer", __FILE__, __LINE__).'</th>';
			$description.='	<th width="200">'.t("Menge", __FILE__, __LINE__).'</th>';
			$description.='</tr>';
			while ($row=mysqli_fetch_array($results))
			{
				$results2=q("SELECT b.id_item, b.title FROM shop_items AS a, shop_items_".$lang." AS b WHERE a.MPN='".$row["PartNr"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$description.='<tr>';
				$description.='<td><a href="http://www.mapco.de/shop_item.php?lang='.$lang.'&id_item='.$row2["id_item"].'">'.$row2["title"].'</a></td>';
				$description.='<td>'.$row["Menge"].'</td>';
				$description.='</tr>';
			}
			$description.='</table>';
		}
		
		//stücklisten reverse SA205
		$results=q("SELECT ArtNr, Menge FROM t_205 WHERE PartNr='".$art_nr[0]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			$description.='<!-- Reverse Start -->';
			$description.='<h1>'.t("Artikel ist auch enthalten in", __FILE__, __LINE__).'</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("Artikelnummer", __FILE__, __LINE__).'</th>';
			$description.='	<th width="200">'.t("enthaltene Menge", __FILE__, __LINE__).'</th>';
			$description.='</tr>';
			while ($row=mysqli_fetch_array($results))
			{
				$results2=q("SELECT b.id_item, b.title FROM shop_items AS a, shop_items_".$lang." AS b WHERE a.MPN='".$row["ArtNr"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$description.='<tr>';
				$description.='<td><a href="http://www.mapco.de/shop_item.php?lang='.$lang.'&id_item='.$row2["id_item"].'">'.$row2["title"].'</a></td>';
				$description.='<td>'.$row["Menge"].'</td>';
				$description.='</tr>';
			}
			$description.='</table>';
			$description.='<!-- Reverse Stop -->';

		}
		
		
		//criteria table
		$j=0;
		$criteria=array();
		$results2=q("SELECT KritNr, KritVal FROM t_210 WHERE ArtNr='".$art_nr[0]."';", $dbshop, __FILE__, __LINE__);
		while ($row2=mysqli_fetch_array($results2))
		{
			$kritnr=$row2["KritNr"];
			$results3=q("SELECT BezNr, Typ FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			if ($row3["Typ"]=="K")
			{
				$results4=q("SELECT TabNr FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($results4);
				if (is_numeric($row2["KritVal"])) $results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl=".$row2["KritVal"].";", $dbshop, __FILE__, __LINE__);
				else $results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl='".$row2["KritVal"]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($results4);
				$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($results4);
				$kritwert=$row4["Bez"];
			}
			else $kritwert=$row2["KritVal"];
			
			$results3=q("SELECT Bez FROM t_030 WHERE BezNr=".$row3["BezNr"]." AND SprachNr=".$sprachnr[$lang].";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$bez=$row3["Bez"];
			
			//Einheiten ausschneiden
			$unit="";
			$start=strrpos($bez, " [");
			if ($start>0)
			{
				$end=strrpos($bez, "]")-$start;
				$unit=substr($bez, $start+2, $end-2);
				$bez=substr($bez, 0, $start);
				$kritwert.=$unit;
			}
			
			$criteria[$j][0]=iconv("windows-".$codepage[$lang], "utf-8", utf8_decode($bez));
			$criteria[$j][1]=iconv("windows-".$codepage[$lang], "utf-8", utf8_decode($kritwert));
			$j++;
		}
		if (sizeof($criteria)>0)
		{
			$description.='<!-- CRIT START -->';
			$description.='<h1>'.t("Kriterien", __FILE__, __LINE__, $lang).'</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("Kriterium", __FILE__, __LINE__, $lang).'</th>';
			$description.='	<th width="200">'.t("Detail", __FILE__, __LINE__, $lang).'</th>';
			$description.='</tr>';
			for($i=0; $i<sizeof($criteria); $i++)
			{
				$description.='<tr>';
				$description.='<td>'.$criteria[$i][0].'</td>';
				$description.='<td>'.$criteria[$i][1].'</td>';
				$description.='</tr>';
			}
			$description.='</table>';
			$description.='<!-- CRIT STOP -->';
		}


		//OE numbers table
		$oenr=array();
		$bez=array();
		$results=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr IN ('".implode("', '", $art_nr)."') AND a.KHerNr=b.KherNr AND VGL=0;", $dbshop, __FILE__, __LINE__);
		$oe_numbers=mysqli_num_rows($results);
		if ($oe_numbers>0)
		{
			$description.='<!-- OE START -->';
			$description.='<h1>'.t("Herstellernummer", __FILE__, __LINE__, $lang).'*</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("OE-Nr.", __FILE__, __LINE__, $lang).'</th>';
			$description.='	<th width="200">'.t("Hersteller", __FILE__, __LINE__, $lang).'</th>';
			$description.='</tr>';
			while ($row=mysqli_fetch_array($results))
			{
				$results2=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row["LBezNr"]." AND SprachNr=".$sprachnr[$lang].";", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$oenr[sizeof($oenr)]=$row["OENr"];
				$bez[sizeof($bez)]=$row2["Bez"];
			}

			//sort by name
			array_multisort($bez, $oenr);
			
			//write to html
			for($i=0; $i<sizeof($bez); $i++)
			{
					$description.='<tr>';
					$description.='	<td>'.$oenr[$i].'</td>';
					$description.='	<td>'.$bez[$i].'</td>';
					$description.='</tr>';
			}
			$description.='</table>';
			$description.='<!-- OE STOP -->';
		}


		//OEM numbers table
		$oenr=array();
		$bez=array();
		$results=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr IN ('".implode("', '", $art_nr)."') AND a.KHerNr=b.KherNr AND VGL=1;", $dbshop, __FILE__, __LINE__);
		$oem_numbers=mysqli_num_rows($results);
		if ($oem_numbers>0)
		{
			$description.='<!-- OEM START -->';
			$description.='<h1>'.t("Vergleichshersteller", __FILE__, __LINE__, $lang).'*</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("OEM-Nr.", __FILE__, __LINE__, $lang).'</th>';
			$description.='	<th width="200">'.t("Hersteller", __FILE__, __LINE__, $lang).'</th>';
			$description.='</tr>';
			while ($row=mysqli_fetch_array($results))
			{
				$results2=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row["LBezNr"]." AND SprachNr=".$sprachnr[$lang].";", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$oenr[sizeof($oenr)]=$row["OENr"];
				$bez[sizeof($bez)]=$row2["Bez"];
			}

			//sort by name
			array_multisort($bez, $oenr);
			
			//write to html
			for($i=0; $i<sizeof($bez); $i++)
			{
					$description.='<tr>';
					$description.='	<td>'.$oenr[$i].'</td>';
					$description.='	<td>'.$bez[$i].'</td>';
					$description.='</tr>';
			}
			$description.='</table>';
			$description.='<!-- OEM STOP -->';
		}


		//vehicle applications table
		$fid=array();
		$bez1=array();
		$bez2=array();
		$bez3=array();
		$bjvon=array();
		$bjbis=array();
		$ktypnr=array();
		$kbanr=array();
		$hubraum=array();
		$kw=array();
		$i=0;
		$results=q("SELECT * FROM t_210 WHERE ArtNr='".$artnr."' AND SortNr=1;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($results))
		{
			//PKW
			if ($row["KritNr"]==2)
			{
				$results2=q("SELECT * FROM vehicles_".$lang." WHERE Exclude=0 AND KTypNr=".$row["KritVal"].";", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				if ($row2["id_vehicle"]!="")
				{
					$fid[$row2["id_vehicle"]]=$row2["id_vehicle"];
					$bez1[$row2["id_vehicle"]]=$row2["BEZ1"];
					$bez2[$row2["id_vehicle"]]=$row2["BEZ2"];
					$bez3[$row2["id_vehicle"]]=$row2["BEZ3"];
					$bjvon[$row2["id_vehicle"]]=$row2["BJvon"];
					$bjbis[$row2["id_vehicle"]]=$row2["BJbis"];
					$ktypnr[$row2["id_vehicle"]]=$row2["KTypNr"];
					$results3=q("SELECT KBANr FROM t_121 WHERE KTypNr=".$row2["KTypNr"].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$kbanr[$row2["id_vehicle"]]=substr($row3["KBANr"], 0, 4).'-'.substr($row3["KBANr"], 4, 3);
					$hubraum[$row2["id_vehicle"]]=number_format($row2["ccmTech"]).'ccm';
					$kw[$row2["id_vehicle"]]=number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)';
				}
			}
		}
		$results=q("SELECT * FROM t_400 WHERE ArtNr='".$artnr."' AND SortNr=1;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($results))
		{
			//PKW
			if ($row["KritNr"]==2)
			{
				$results2=q("SELECT * FROM vehicles_".$lang." WHERE Exclude=0 AND KTypNr=".$row["KritWert"].";", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				if ($row2["id_vehicle"]!="")
				{
					$fid[$row2["id_vehicle"]]=$row2["id_vehicle"];
					$bez1[$row2["id_vehicle"]]=$row2["BEZ1"];
					$bez2[$row2["id_vehicle"]]=$row2["BEZ2"];
					$bez3[$row2["id_vehicle"]]=$row2["BEZ3"];
					$bjvon[$row2["id_vehicle"]]=$row2["BJvon"];
					$bjbis[$row2["id_vehicle"]]=$row2["BJbis"];
					$ktypnr[$row2["id_vehicle"]]=(int)$row2["KTypNr"];
					$results3=q("SELECT KBANr FROM t_121 WHERE KTypNr=".$row2["KTypNr"].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$kbanr[$row2["id_vehicle"]]=substr($row3["KBANr"], 0, 4).'-'.substr($row3["KBANr"], 4, 3);
					$hubraum[$row2["id_vehicle"]]=number_format($row2["ccmTech"]).'ccm';
					$kw[$row2["id_vehicle"]]=number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)';
				}
			}
		}
		
		//sort by name
		array_multisort($bez1, $bez2, $bez3, $bjvon, $bjbis, $fid, $ktypnr, $kbanr, $hubraum, $kw);
		
		//SA400 read criteria
		$kritnr=array();
		$kritwert=array();
		$results=q("SELECT KritNr, KritWert, SortNr FROM t_400 WHERE ArtNr='".$artnr."' ORDER BY LfdNr, SortNr;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($results))
		{
			if ($row["SortNr"]==1) $ktyp=$row["KritWert"];
			else
			{
				$results2=q("SELECT BezNr, Typ, TabNr FROM t_050 WHERE KritNr='".$row["KritNr"]."';", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				if ($row2["Typ"]=="K")
				{
					if (is_numeric($row["KritWert"]))
					{
						$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl=".$row["KritWert"].";", $dbshop, __FILE__, __LINE__);
					}
					else
					{
						$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl='".$row["KritWert"]."';", $dbshop, __FILE__, __LINE__);
					}
					$row4=mysqli_fetch_array($results4);
					$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
					$row4=mysqli_fetch_array($results4);
					$kritw=$row4["Bez"];
				}
				else 
				{
					if ($row["KritNr"] == 20 or $row["KritNr"] == 21)
					{
						$kritw=substr($row["KritWert"], -2, 2).'/'.substr($row["KritWert"], 0, 4);
					}
					else $kritw=$row["KritWert"];
				}
				
				$results2=q("SELECT Bez FROM t_030 WHERE BezNr=".$row2["BezNr"]." AND SprachNr=".$sprachnr[$lang].";", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$bez=$row2["Bez"];

				//Einheiten ausschneiden
				$unit="";
				$start=strrpos($bez, " [");
				if ($start>0)
				{
					$end=strrpos($bez, "]")-$start;
					$unit=substr($bez, $start+2, $end-2);
					$bez=substr($bez, 0, $start);
					$kritw.=$unit;
				}
				$kritwert[$ktyp][sizeof($kritwert[$ktyp])]=iconv("windows-".$codepage[$lang], "utf-8", utf8_decode($kritw));
				$kritnr[$ktyp][sizeof($kritnr[$ktyp])]=iconv("windows-".$codepage[$lang], "utf-8", utf8_decode($bez));
	
			}
		}

		//write to html
		if (sizeof($fid)>0)
		{
			$description.='<!-- VEHICLES START -->';
			$description.='<h1>'.t("Fahrzeugzuordnungen", __FILE__, __LINE__, $lang).'</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("Fahrzeug", __FILE__, __LINE__, $lang).'</th>';
			$description.='	<th width="120">'.t("Baujahr", __FILE__, __LINE__, $lang).'</th>';
			$description.='	<th width="100">'.t("Leistung", __FILE__, __LINE__, $lang).'</th>';
			$description.='	<th width="80">'.t("Hubraum", __FILE__, __LINE__, $lang).'</th>';
			$description.='	<th width="80">'.t("KBA-Nr.", __FILE__, __LINE__, $lang).'</th>';
			$description.='</tr>';
			for($i=0; $i<sizeof($fid); $i++)
			{
				$description.='<tr>';
				$description.='	<td><a href="http://www.mapco.de/shop_searchbycar.php?lang='.$lang.'&id_vehicle='.$fid[$i].'">'.$bez1[$i].' '.$bez2[$i].' '.$bez3[$i].'</a>';
				if (sizeof($kritnr[$ktypnr[$i]])>0)
				{
					for($j=0; $j<sizeof($kritnr[$ktypnr[$i]]); $j++)
					{
						$description .= '<br /><span style="color:#FF0000"><i>'.$kritnr[$ktypnr[$i]][$j].': '.$kritwert[$ktypnr[$i]][$j].'</i></span>';
					}
				}
				$description.='</td>';
				$description.='	<td>';
				$description.=baujahr($bjvon[$i]).' - '.baujahr($bjbis[$i]);
				$description.='</td>';
				$description.='<td>'.$kw[$i].'</td>';
				$description.='<td>'.$hubraum[$i].'</td>';
				$description.='<td>'.$kbanr[$i].'</td>';
				$description.='</tr>';
			}
			$description.='</table>';
			$description.='<!-- VEHICLES STOP -->';
		}
		if ($oe_numbers>0 or $oem_numbers>0)
		{
			$description.='<!-- OETXT START -->';
			$description.='<i>* '.t("Die angezeigten OE- und OEM-Nummern dienen nur zur Zuordnung technischer Daten und der Verwendungszwecke.", __FILE__, __LINE__).'</i><br /><br />';
			$description.='<!-- OETXT STOP -->';
		}
		$fid="";
		$bez1="";
		$bez2="";
		$bez3="";
		$bjvon="";
		$bjbis="";
		$ktypnr="";
		$kbanr="";
		$hubraum="";
		$kw="";
		$i=0;
		$kritnr="";
		$kritwert="";
		$oe_numbers="";
		$oem_numbers="";
		
		
		return($description);
	}


	function get_menuitem_id($artnr)
	{
		global $dbshop;
		$results=q("SELECT menuitem_id FROM t_200 AS a, shop_menuitems_artgr AS b WHERE a.ArtNr='".$artnr."' AND a.ARTGR=b.artgr;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		if ($row["menuitem_id"]=="") $row["menuitem_id"]=0;
		return($row["menuitem_id"]);
	}

	
	//create items language tables if needed
	q("
		CREATE TABLE IF NOT EXISTS `shop_items` (
		  `id_item` int(11) NOT NULL AUTO_INCREMENT,
		  `MPN` tinytext NOT NULL,
		  `price` float NOT NULL,
		  `menuitem_id` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  PRIMARY KEY (`id_item`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=123765;
		", $dbshop, __FILE__, __LINE__);
	for($i=0; $i<sizeof($sprache); $i++)
	{
		q("
		CREATE TABLE IF NOT EXISTS `shop_items_".$sprache[$i]."` (
		  `id_item` int(11) NOT NULL AUTO_INCREMENT,
		  `title` tinytext NOT NULL,
		  `short_description` text NOT NULL,
		  `description` longtext NOT NULL,
		  PRIMARY KEY (`id_item`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=123765;
		", $dbshop, __FILE__, __LINE__);
	}


	//Alle Artikel auslesen
	$results=q("SELECT * FROM t_200 WHERE VK_SP_CHK=0;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{	
		$t200[$row["ArtNr"]]=$row["ArtNr"];
		$t200_collateral[$row["ArtNr"]]=$row["ATWERT"];
		$t200_GART[$row["ArtNr"]]=$row["GART"];
		$results2=q("SELECT BezNr FROM t_320 WHERE GenArtNr=".$row["GART"].";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results2)==0 ) $t200_active[$row["ArtNr"]]=0; else $t200_active[$row["ArtNr"]]=1;
	}
	$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{	
		$shop[$row["MPN"]]=$row["MPN"];
		$shop_id[$row["MPN"]]=$row["id_item"];
		$shop_active[$row["MPN"]]=$row["active"];
	}


	//Neue Artikel bestimmen
	$k=0;
	foreach($t200 as $item)
	{
		$exists=false;
		if (isset($shop[$item])) $exists=true;
		if (!$exists)
		{
			$new[$k]=$item;
			$k++;
		}
	}


	//Alte Artikel deaktivieren
	$k=0;
	foreach($shop as $item)
	{
		if ( $shop_active[$item] and (!isset($t200[$item]) or $t200_active[$item]==0) )
		{
			q("UPDATE shop_items SET active=0 WHERE id_item=".$shop_id[$item].";", $dbshop, __FILE__, __LINE__);
			echo '	<Deactivated>'.$shop[$item].'</Deactivated>'."\n";
		}
	}


	//Bekannte Artikel aktivieren
	$results=q("SELECT * FROM shop_items WHERE NOT active;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if ( isset($t200_active[$row["MPN"]]) and $t200_active[$row["MPN"]]>0 )
		{
			q("UPDATE shop_items SET active=1 WHERE id_item=".$row["id_item"]." and active=0;", $dbshop, __FILE__, __LINE__);
			echo '	<Activated>'.$row["MPN"].'</Activated>'."\n";
		}
	}


	//Neue Artikel eintragen
	$max=sizeof($new);
	$left=$max-500;
	if($max>500) $max=500;
	for ($i=0; $i<$max; $i++)
	{
		q("INSERT INTO cms_articles (site_id, title, imageprofile_id, firstmod, lastmod) VALUES(1, '".mysqli_real_escape_string($dbweb, get_title($new[$i], $sprache[$j]))."', 4, ".time().", ".time().");", $dbweb, __FILE__, __LINE__);
		$article_id=mysqli_insert_id($dbweb);
		q("INSERT INTO cms_articles_labels (article_id, label_id) VALUES(".$article_id.", 11);", $dbweb, __FILE__, __LINE__);
		q("INSERT INTO shop_items (Brand, MPN, GART, collateral, article_id, menuitem_id, firstmod, lastmod) VALUES('MAPCO Autotechnik GmbH', '".$new[$i]."', ".$t200_GART[$new[$i]].", ".$t200_collateral[$new[$i]].", ".$article_id.", '".get_menuitem_id($new[$i], $sprache[$j])."', '".time()."', '".time()."');", $dbshop, __FILE__, __LINE__);
		$item_id=mysqli_insert_id($dbshop);
		for($j=0; $j<sizeof($sprache); $j++)
		{
			q("INSERT INTO shop_items_".$sprache[$j]." (id_item, title, short_description, description) VALUES('".$item_id."', '".addslashes(stripslashes(get_title($new[$i], $sprache[$j])))."', '".addslashes(stripslashes(get_short_description($new[$i], $sprache[$j])))."', '".addslashes(stripslashes(get_description($new[$i], $sprache[$j])))."');", $dbshop, __FILE__, __LINE__);
		}
		echo '	<New>'.$new[$i].'</New>'."\n";
		$stoptime = time()+microtime();
		if ($stoptime-$starttime>60) exit;
	}

	//get lastmod from shop_items
	$lastmod_t_200=array();
	$lastmod_id_item=array();
	$results=q("SELECT * FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$lastmod_t200[$row["MPN"]]=$row["lastmod_t200"];
		$lastmod_id_item[$row["MPN"]]=$row["id_item"];
	}


	//LETZTE_BEARB fix shop_items 1398930313 (31.04.2014)
/*
	$results=q("SELECT * FROM t_200 WHERE LETZTE_BEARB<1398930313;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( $lastmod_t200[$row["ArtNr"]]!=$row["LETZTE_BEARB"] and isset($lastmod_id_item[$row["ArtNr"]]) )
		{
			echo $row["ArtNr"]."\n";
			$data=array();
			$data["lastmod_t200"]=$row["LETZTE_BEARB"];
			q_update("shop_items", $data, "WHERE id_item=".$lastmod_id_item[$row["ArtNr"]].";", $dbshop, __FILE__, __LINE__);
		}
	}
*/

	//compare LETZTE_BEARB from t_200 with shop_items
	$old=array();
	$results=q("SELECT * FROM t_200 ORDER BY LETZTE_BEARB;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
//		$time=mktime(10, 0, 0, substr($row["LETZTE_BEARB"], 5, 2), substr($row["LETZTE_BEARB"], 8, 2), substr($row["LETZTE_BEARB"], 0, 4));
		if( isset($lastmod_t200[$row["ArtNr"]]) and $lastmod_t200[$row["ArtNr"]]<$row["LETZTE_BEARB"] )
		{
			$old[$row["ArtNr"]]=$lastmod_id_item[$row["ArtNr"]];
		}
	}
	
	//compare ERSTER_WE from t_200 with shop_items
	$results=q("SELECT * FROM t_200 ORDER BY ERSTER_WE;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
//		$time=mktime(10, 0, 0, substr($row["ERSTER_WE"], 5, 2), substr($row["ERSTER_WE"], 8, 2), substr($row["ERSTER_WE"], 0, 4));
		if( !isset($old[$row["ArtNr"]]) and isset($lastmod_t200[$row["ArtNr"]]) and $lastmod_t200[$row["ArtNr"]]<$row["ERSTER_WE"] )
		{
			$old[$row["ArtNr"]]=$lastmod_id_item[$row["ArtNr"]];
		}
	}
	
	$todo=sizeof($old);
	echo '	<ToDo>'.$todo.'</ToDo>'."\n";
	
	/*******************************************************
	 * Es müssen noch Wareneingänge berücksichtigt werden. *
	 *******************************************************/

	//Bestehende Artikel aktualisieren
//	for($i=0; $i<sizeof($old); $i++)
	foreach($old as $id_item)
	{
		$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ItemUpdate", "id_item" => $id_item));
		echo '	<ItemUpdated><![CDATA['.$responseXml.']]></ItemUpdated>'."\n";
		$stoptime = time()+microtime();
		$todo--;
		//Stop when time is up
		if ($stoptime-$starttime>60)
		{
			//NextCall
			if( $todo>0 ) echo '	<NextCall>'.(time()+5*60).'</NextCall>'."\n";

			if($_POST["auto"]!=1)
			{
				echo '</body>';
				echo '</html>';
			}
			else
			{
				echo '</UpdateArtNrResponse>'."\n";
			}
			exit;
		}
	}
?>