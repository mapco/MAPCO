<?php

	include("config.php");
	include("functions/cms_t2.php");
	include("functions/mapco_baujahr.php");
	
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
	while($row=mysql_fetch_array($results))
	{
		$sprache[sizeof($sprache)]=$row["code"];
	}
	
	
	//get codepages
	$codepage=array();
	$results=q("SELECT * FROM t_020;", $dbshop, __FILE__, __LINE__);
	while($row=mysql_fetch_array($results))
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
//		echo $artnr.' ArtNr<br />';
		$query="SELECT GART FROM t_200 WHERE t_200.ArtNr='".$artnr."';";
		$results=q($query, $dbshop, __FILE__, __LINE__);
		$row=mysql_fetch_array($results);
//		echo $row["GART"].' GenArt<br />';
		if ($row["GART"] != "")
		{
			$query="SELECT BezNr FROM t_320 WHERE GenArtNr=".$row["GART"].";";
			$results2=q($query, $dbshop, __FILE__, __LINE__);
			$row2=mysql_fetch_array($results2);
	//		echo $row2["BezNr"].' BezNr<br />';
			if ($row2["BezNr"] != "")
			{
				$query="SELECT Bez FROM t_030 WHERE SprachNr='".$sprachnr[$lang]."' AND BezNr=".$row2["BezNr"].";";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				$row=mysql_fetch_array($results);
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
		while ($row2=mysql_fetch_array($results2))
		{
			$kritnr=$row2["KritNr"];
			$results3=q("SELECT BezNr, Typ FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
			$row3=mysql_fetch_array($results3);
			if ($row3["Typ"]=="K")
			{
				$results4=q("SELECT TabNr FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
				$row4=mysql_fetch_array($results4);
				if (is_numeric($row2["KritVal"])) $results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl=".$row2["KritVal"].";", $dbshop, __FILE__, __LINE__);
				else $results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl='".$row2["KritVal"]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysql_fetch_array($results4);
				$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysql_fetch_array($results4);
				$kritwert=$row4["Bez"];
			}
			else $kritwert=$row2["KritVal"];
			
			$results3=q("SELECT Bez FROM t_030 WHERE BezNr=".$row3["BezNr"]." AND SprachNr=".$sprachnr[$lang].";", $dbshop, __FILE__, __LINE__);
			$row3=mysql_fetch_array($results3);
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
		
		//Ersetzungen SA204
		$results=q("SELECT * FROM t_204 WHERE EArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($results)>0)
		{
			$row=mysql_fetch_array($results);
			$results2=q("SELECT b.id_item, b.title FROM shop_items AS a, shop_items_".$lang." AS b WHERE a.MPN='".$row["ArtNr"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
			$row2=mysql_fetch_array($results2);
			$description .= '<div class="info"><a href="shop_item.php?lang='.$lang.'&id_item='.$row2["id_item"].'">Dieser Artikel wurde durch '.$row2["title"].' ersetzt!</a></div>';
		}
		
		
		//stücklisten SA205
		$results=q("SELECT PartNr, Menge FROM t_205 WHERE ArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($results)>0)
		{
			$description.='<h1>'.t("Stückliste", __FILE__, __LINE__).'</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("Artikelnummer", __FILE__, __LINE__).'</th>';
			$description.='	<th width="200">'.t("Menge", __FILE__, __LINE__).'</th>';
			$description.='</tr>';
			while ($row=mysql_fetch_array($results))
			{
				$results2=q("SELECT b.id_item, b.title FROM shop_items AS a, shop_items_".$lang." AS b WHERE a.MPN='".$row["PartNr"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
				$row2=mysql_fetch_array($results2);
				$description.='<tr>';
				$description.='<td><a href="http://www.mapco.de/shop_item.php?lang='.$lang.'&id_item='.$row2["id_item"].'">'.$row2["title"].'</a></td>';
				$description.='<td>'.$row["Menge"].'</td>';
				$description.='</tr>';
			}
			$description.='</table>';
		}
		
		//stücklisten reverse SA205
		$results=q("SELECT ArtNr, Menge FROM t_205 WHERE PartNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($results)>0)
		{
			$description.='<!-- Reverse Start -->';
			$description.='<h1>'.t("Artikel ist auch enthalten in", __FILE__, __LINE__).'</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("Artikelnummer", __FILE__, __LINE__).'</th>';
			$description.='	<th width="200">'.t("enthaltene Menge", __FILE__, __LINE__).'</th>';
			$description.='</tr>';
			while ($row=mysql_fetch_array($results))
			{
				$results2=q("SELECT b.id_item, b.title FROM shop_items AS a, shop_items_".$lang." AS b WHERE a.MPN='".$row["ArtNr"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
				$row2=mysql_fetch_array($results2);
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
		$results2=q("SELECT KritNr, KritVal FROM t_210 WHERE ArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
		while ($row2=mysql_fetch_array($results2))
		{
			$kritnr=$row2["KritNr"];
			$results3=q("SELECT BezNr, Typ FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
			$row3=mysql_fetch_array($results3);
			if ($row3["Typ"]=="K")
			{
				$results4=q("SELECT TabNr FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
				$row4=mysql_fetch_array($results4);
				if (is_numeric($row2["KritVal"])) $results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl=".$row2["KritVal"].";", $dbshop, __FILE__, __LINE__);
				else $results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl='".$row2["KritVal"]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysql_fetch_array($results4);
				$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysql_fetch_array($results4);
				$kritwert=$row4["Bez"];
			}
			else $kritwert=$row2["KritVal"];
			
			$results3=q("SELECT Bez FROM t_030 WHERE BezNr=".$row3["BezNr"]." AND SprachNr=".$sprachnr[$lang].";", $dbshop, __FILE__, __LINE__);
			$row3=mysql_fetch_array($results3);
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
		$results=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$artnr."' AND a.KHerNr=b.KherNr AND VGL=0;", $dbshop, __FILE__, __LINE__);
		$oe_numbers=mysql_num_rows($results);
		if ($oe_numbers>0)
		{
			$description.='<!-- OE START -->';
			$description.='<h1>'.t("Herstellernummer", __FILE__, __LINE__, $lang).'*</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("OE-Nr.", __FILE__, __LINE__, $lang).'</th>';
			$description.='	<th width="200">'.t("Hersteller", __FILE__, __LINE__, $lang).'</th>';
			$description.='</tr>';
			while ($row=mysql_fetch_array($results))
			{
				$results2=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row["LBezNr"]." AND SprachNr=".$sprachnr[$lang].";", $dbshop, __FILE__, __LINE__);
				$row2=mysql_fetch_array($results2);
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
		$results=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$artnr."' AND a.KHerNr=b.KherNr AND VGL=1;", $dbshop, __FILE__, __LINE__);
		$oem_numbers=mysql_num_rows($results);
		if ($oem_numbers>0)
		{
			$description.='<!-- OEM START -->';
			$description.='<h1>'.t("Vergleichshersteller", __FILE__, __LINE__, $lang).'*</h1>';
			$description.='<table class="hover">';
			$description.='<tr>';
			$description.='	<th>'.t("OEM-Nr.", __FILE__, __LINE__, $lang).'</th>';
			$description.='	<th width="200">'.t("Hersteller", __FILE__, __LINE__, $lang).'</th>';
			$description.='</tr>';
			while ($row=mysql_fetch_array($results))
			{
				$results2=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row["LBezNr"]." AND SprachNr=".$sprachnr[$lang].";", $dbshop, __FILE__, __LINE__);
				$row2=mysql_fetch_array($results2);
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
		while ($row=mysql_fetch_array($results))
		{
			//PKW
			if ($row["KritNr"]==2)
			{
				$results2=q("SELECT * FROM vehicles_".$lang." WHERE Exclude=0 AND KTypNr=".$row["KritVal"].";", $dbshop, __FILE__, __LINE__);
				$row2=mysql_fetch_array($results2);
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
					$row3=mysql_fetch_array($results3);
					$kbanr[$row2["id_vehicle"]]=substr($row3["KBANr"], 0, 4).'-'.substr($row3["KBANr"], 4, 3);
					$hubraum[$row2["id_vehicle"]]=number_format($row2["ccmTech"]).'ccm';
					$kw[$row2["id_vehicle"]]=number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)';
				}
			}
		}
		$results=q("SELECT * FROM t_400 WHERE ArtNr='".$artnr."' AND SortNr=1;", $dbshop, __FILE__, __LINE__);
		while ($row=mysql_fetch_array($results))
		{
			//PKW
			if ($row["KritNr"]==2)
			{
				$results2=q("SELECT * FROM vehicles_".$lang." WHERE Exclude=0 AND KTypNr=".$row["KritWert"].";", $dbshop, __FILE__, __LINE__);
				$row2=mysql_fetch_array($results2);
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
					$row3=mysql_fetch_array($results3);
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
		while ($row=mysql_fetch_array($results))
		{
			if ($row["SortNr"]==1) $ktyp=$row["KritWert"];
			else
			{
				$results2=q("SELECT BezNr, Typ, TabNr FROM t_050 WHERE KritNr='".$row["KritNr"]."';", $dbshop, __FILE__, __LINE__);
				$row2=mysql_fetch_array($results2);
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
					$row4=mysql_fetch_array($results4);
					$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
					$row4=mysql_fetch_array($results4);
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
				$row2=mysql_fetch_array($results2);
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
		$row=mysql_fetch_array($results);
		if ($row["menuitem_id"]=="") $row["menuitem_id"]=0;
		return($row["menuitem_id"]);
	}
	
	//welche Sprache soll geprüft werden?
	$lang="fr";

	// Artikel auslesen
	$results=q("SELECT id_item FROM shop_items_".$lang.";", $dbshop, __FILE__, __LINE__);
	while ($row=mysql_fetch_array($results))
	{
		$items_lang[$row["id_item"]]=$row["id_item"];
	}
	
	$count=0;
	$results=q("SELECT id_item FROM shop_items_de;", $dbshop, __FILE__, __LINE__);
	while ($row=mysql_fetch_array($results))
	{
		if (!isset($items_lang[$row["id_item"]]) and $count<100)
		{
			$results2=q("SELECT MPN FROM shop_items WHERE id_item=".$row["id_item"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row2=mysql_fetch_array($results2);
			q("INSERT INTO shop_items_".$lang." (id_item, title, short_description, description) VALUES('".$row["id_item"]."', '".addslashes(stripslashes(get_title($row2["MPN"], $lang)))."', '".addslashes(stripslashes(get_short_description($row2["MPN"], $lang)))."', '".addslashes(stripslashes(get_description($row2["MPN"], $lang)))."');", $dbshop, __FILE__, __LINE__);
			echo $row["id_item"].' / '.$row2["MPN"].'<br />';
			$count++;
		}
	}

	if($count==0)
	{
		// ungueltige Artikel entfernen
		$results=q("SELECT id_item FROM shop_items_".$lang.";", $dbshop, __FILE__, __LINE__);
		while ($row=mysql_fetch_array($results))
		{
			if (!isset($items_de[$row["id_item"]]))
			{
				q("DELETE FROM shop_items_".lang." WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);	
			}
		}
	}

	
	if ($count==100) echo '<script language="javascript">window.setTimeout(document.location.reload(), 10000);</script>';

?>