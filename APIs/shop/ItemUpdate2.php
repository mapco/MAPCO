<?php
	$starttime = time()+microtime();
	
	include("../functions/cms_t2.php");
	include("../functions/mapco_baujahr.php");
	include("../functions/cms_url_encode.php");

	if ( !isset($_POST["id_item"]) )
	{
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine gültige Artikel-ID übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}

	if ( !is_numeric($_POST["id_item"]) )
	{
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-ID ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Die Artikel-ID muss numerisch sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["lang"]) )
	{
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprache nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Sprache angegebenen werden, damit der Service weiß, in welcher Sprache der Artikel aktualisiert werden muss.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}

	//get artnr
	$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur angegebenen Artikel-ID konnte kein Artikel gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}
	$item=mysqli_fetch_array($results);
	$artnr=$item["MPN"];
	
	//check if item exists in this language
	$results=q("SELECT * FROM shop_items_".$_POST["lang"]." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		q("INSERT INTO shop_items_".$_POST["lang"]." (id_item, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["id_item"].", ".time().", ".$_SESSION["id_user"].", 0, ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	}

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
				   "zh" => "031",
				   "bg" => "032",
				   "lv" => "033",
				   "lt" => "034",
				   "et" => "035",
				   "sl" => "036",
				   "qa" => "037",
				   "qb" => "038");
	$sprachnr["zh"]="004"; //Chinese to English


	//get languages
	$id_language=array();
	$sprache=array();
	$id2lang=array();
	$results=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$id_language[$row["code"]]=$row["id_language"];
		$sprache[]=$row["code"];
		$id2lang[$row["id_language"]]=$row["code"];
	}


	//get codepages
	$codepage=array();
	$results=q("SELECT * FROM t_020;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$codepage[$row["ISOCode"]]=$row["Codepage"];
		if ($row["ISOCode"]=="en") $cp=$row["Codepage"];
	}
	$codepage["zh"]=$cp; //Chinese as English

	//get menuitems
	$artgr2menuitem=array();
	$results=q("SELECT * FROM shop_menuitems_artgr;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( !isset($artgr2menuitem[$row["artgr"]]) )
			$artgr2menuitem[$row["artgr"]]=$row["menuitem_id"];
	}

	//get title
	$title='';
	$results=q("SELECT * FROM t_200 WHERE t_200.ArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		q("UPDATE shop_items SET active=0, lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Generische Artikelnummer unbekannt.</shortMsg>'."\n";
		echo '		<longMsg>Die generische Artikelnummer konnte nicht ermittelt werden. Der Artikel-Zeitspempel wurde aktualisiert und der Artikel deaktiviert.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '	<ArtNr>'.$artnr.'</ArtNr>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);

	//update shop_items
	q("	UPDATE shop_items
		SET collateral=".$row["ATWERT"].",
		GART=".$row["GART"].",
		menuitem_id='".$artgr2menuitem[$row["ARTGR"]*1]."'
		WHERE id_item='".$_POST["id_item"]."';", $dbshop, __FILE__, __LINE__);



	$results2=q("SELECT BezNr FROM t_320 WHERE GenArtNr=".$row["GART"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results2)==0 )
	{
		q("UPDATE shop_items SET active=0, lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Generische Artikelbezeichnung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die generische Artikelbezeichnung konnte nicht gefunden werden. Der Artikel-Zeitspempel wurde aktualisiert und der Artikel deaktiviert.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '	<ArtNr>'.$artnr.'</ArtNr>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}
	$row2=mysqli_fetch_array($results2);
	$results=q("SELECT Bez FROM t_030 WHERE SprachNr='".$sprachnr[$_POST["lang"]]."' AND BezNr=".$row2["BezNr"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprachbezeichnung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Sprachbezeichnung für die generische Artikelnummer konnte nicht gefunden werden. Der Vorgang wurde abgebrochen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '	<ArtNr>'.$artnr.'</ArtNr>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$title=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($row["Bez"])).' ('.$artnr.')';


	//get short description
	$short_description='';
	$j=0;
	$criteria=array();
	$results2=q("SELECT KritNr, KritVal FROM t_210 WHERE ArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
	while ($row2=mysqli_fetch_array($results2))
	{
		$kritnr=$row2["KritNr"];
			$results3=q("SELECT TabNr, BezNr, Typ FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
//			print_r($row3);
//			echo '<hr />';
			if ($row3["Typ"]=="K")
			{
//				$results4=q("SELECT TabNr FROM t_050 WHERE KritNr=".$kritnr.";", $dbshop, __FILE__, __LINE__);
//				$row4=mysqli_fetch_array($results4);
//				if (is_numeric($row2["KritVal"])) $results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl='".$row2["KritVal"]."';", $dbshop, __FILE__, __LINE__);
//				else
				$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row3["TabNr"]." AND Schl='".$row2["KritVal"]."';", $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows($results4)==0 )
				{
					$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row3["TabNr"]." AND Schl=".$row2["KritVal"].";", $dbshop, __FILE__, __LINE__);
//					echo "SELECT BezNr FROM t_052 WHERE TabNr=".$row3["TabNr"]." AND Schl='".$row2["KritVal"]."';";
//					print_r($row3);
//					exit;
				}
				$row4=mysqli_fetch_array($results4);
				$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr='".$sprachnr[$_POST["lang"]]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($results4);
				$kritwert=$row4["Bez"];
//				$kritwert=$sa30[$row4["BezNr"]][$sprachnr[$_POST["lang"]]];
			}
			else $kritwert=$row2["KritVal"];

			$results3=q("SELECT Bez FROM t_030 WHERE BezNr=".$row3["BezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$bez=$row3["Bez"];
//			$bez=$sa30[$row3["BezNr"]][$sprachnr[$_POST["lang"]]];

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
			$criteria[$_POST["lang"]][$j][0]=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($bez));
			$criteria[$_POST["lang"]][$j][1]=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($kritwert));
		$j++;
	}

	for($i=0; $i<sizeof($criteria[$_POST["lang"]]); $i++)
	{
		$short_description.=$criteria[$_POST["lang"]][$i][0].': '.$criteria[$_POST["lang"]][$i][1];
		if (($i+1)<sizeof($criteria[$_POST["lang"]])) $short_description.='; ';
	}
	
	//get description
	$description='';
	
	//Ersetzungen SA204
	$results=q("SELECT * FROM t_204 WHERE EArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		$row=mysqli_fetch_array($results);
		$results2=q("SELECT b.id_item, b.title FROM shop_items AS a, shop_items_".$_POST["lang"]." AS b WHERE a.MPN='".$row["ArtNr"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$description .= '<div class="info"><a href="http://www.mapco.de/'.$_POST["lang"].'/online-shop/autoteile/'.$row2["id_item"].'/'.url_encode($row2["title"]).'">'.t("Dieser Artikel wird künftig ersetzt durch", __FILE__, __LINE__, $_POST["lang"]).': '.$row2["title"].'</a></div>'."\n\n";
	}

	//stücklisten SA205
	$results=q("SELECT PartNr, Menge FROM t_205 WHERE ArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		mysqli_data_seek($results, 0);

		$description.='<h2>'.t("Stückliste", __FILE__, __LINE__).'</h2>'."\n";
		$description.='<table class="hover">'."\n";
		$description.='<tr>'."\n";
		$description.='	<th>'.t("Artikelnummer", __FILE__, __LINE__).'</th>'."\n";
		$description.='	<th width="200">'.t("Menge", __FILE__, __LINE__).'</th>'."\n";
		$description.='</tr>'."\n";
		while ($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT b.id_item, b.title FROM shop_items AS a, shop_items_".$_POST["lang"]." AS b WHERE a.MPN='".$row["PartNr"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$description.='<tr>'."\n";
			if( $_POST["lang"]=="de" ) $lang_code=""; else $lang_code=$_POST["lang"]."/";
			$description.='	<td><a href="http://www.mapco.de/'.$lang_code.'online-shop/autoteile/'.$row2["id_item"].'/'.url_encode($row2["title"]).'">'.$row2["title"].'</a></td>'."\n";
			$description.='	<td>'.$row["Menge"].'</td>'."\n";
			$description.='</tr>'."\n";
		}
		$description.='</table>'."\n\n";
	}


	//stücklisten reverse SA205
	$results=q("SELECT ArtNr, Menge FROM t_205 WHERE PartNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		mysqli_data_seek($results, 0);

		if ( !isset($description) ) $description='';
		$description.='<!-- Reverse Start -->';
		$description.='<h2>'.t("Artikel ist auch enthalten in", __FILE__, __LINE__).'</h2>';
		$description.='<table class="hover">';
		$description.='<tr>';
		$description.='	<th>'.t("Artikelnummer", __FILE__, __LINE__).'</th>';
		$description.='	<th width="200">'.t("enthaltene Menge", __FILE__, __LINE__).'</th>';
		$description.='</tr>';
		while ($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT b.id_item, b.title FROM shop_items AS a, shop_items_".$_POST["lang"]." AS b WHERE a.MPN='".$row["ArtNr"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$description.='<tr>';
			if( $_POST["lang"]=="de" ) $lang_code=""; else $lang_code=$_POST["lang"]."/";
			$description.='<td><a href="http://www.mapco.de/'.$lang_code.'online-shop/autoteile/'.$row2["id_item"].'/'.url_encode($row2["title"]).'">'.$row2["title"].'</a></td>';
			$description.='<td>'.$row["Menge"].'</td>';
			$description.='</tr>';
		}
		$description.='</table>';
		$description.='<!-- Reverse Stop -->';
	}


	//Kriterien zum Artikel
	if (sizeof($criteria)>0)
	{
		if ( !isset($description) ) $description='';
		$description.='<div style="width:370px; margin:0px 16px 0px 0px; float:left;">'."\n";
		$description.='<h2>'.t("Kriterien", __FILE__, __LINE__, $_POST["lang"]).'</h2>'."\n";
		$description.='<table class="hover">'."\n";
		$description.='<tr>'."\n";
		$description.='	<th>'.t("Kriterium", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description.='	<th width="200">'.t("Detail", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description.='</tr>'."\n";
		for($i=0; $i<sizeof($criteria[$_POST["lang"]]); $i++)
		{
			$description.='<tr>'."\n";
			$description.='	<td>'.$criteria[$_POST["lang"]][$i][0].'</td>'."\n";
			$description.='	<td>'.$criteria[$_POST["lang"]][$i][1].'</td>'."\n";
			$description.='</tr>'."\n";
		}
		$description.='</table>'."\n\n";
		$description.='</div>'."\n\n";
	}


	//OE numbers table
	$oenr=array();
	$bez=array();
	$results=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$artnr."' AND a.KHerNr=b.KherNr AND VGL=0;", $dbshop, __FILE__, __LINE__);
	$oe_numbers=mysqli_num_rows($results);
	if ($oe_numbers>0)
	{
		mysqli_data_seek($results, 0);

		$description.='<!-- OE START -->'."\n";
		$description.='<div style="width:370px; float:left;">'."\n";
		$description.='<h2>'.t("Herstellernummer", __FILE__, __LINE__, $_POST["lang"]).'*</h2>'."\n";
		$description.='<table class="hover">'."\n";
		$description.='<tr>'."\n";
		$description.='	<th>'.t("OE-Nr.", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description.='	<th width="200">'.t("Hersteller", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description.='</tr>'."\n";
		while ($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row["LBezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$oenr[sizeof($oenr)]=$row["OENr"];
			$bez[sizeof($bez)]=$row2["Bez"];
		}

		//sort by name
		array_multisort($bez, $oenr);
		
		//write to html
		for($i=0; $i<sizeof($bez); $i++)
		{
				$description.='<tr>'."\n";
				$description.='	<td>'.$oenr[$i].'</td>'."\n";
				$description.='	<td>'.$bez[$i].'</td>'."\n";
				$description.='</tr>'."\n";
		}
		$description.='</table>'."\n";
		$description.='</div>'."\n";
		$description.='<!-- OE STOP -->'."\n\n";
	}

/*
	//OEM numbers table
	$oenr=array();
	$bez=array();
	$results=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$artnr."' AND a.KHerNr=b.KherNr AND VGL=1;", $dbshop, __FILE__, __LINE__);
	$oe_numbers=mysqli_num_rows($results);
	if ($oe_numbers>0)
	{
		mysqli_data_seek($results, 0);

		$description.='<!-- OEM START -->'."\n";
		$description.='<h2>'.t("Herstellernummer", __FILE__, __LINE__, $_POST["lang"]).'*</h2>'."\n";
		$description.='<table class="hover">'."\n";
		$description.='<tr>'."\n";
		$description.='	<th>'.t("OE-Nr.", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description.='	<th width="200">'.t("Hersteller", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description.='</tr>'."\n";
		while ($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row["LBezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$oenr[sizeof($oenr)]=$row["OENr"];
			$bez[sizeof($bez)]=$row2["Bez"];
		}

		//sort by name
		array_multisort($bez, $oenr);
		
		//write to html
		for($i=0; $i<sizeof($bez); $i++)
		{
				$description.='<tr>'."\n";
				$description.='	<td>'.$oenr[$i].'</td>'."\n";
				$description.='	<td>'.$bez[$i].'</td>'."\n";
				$description.='</tr>'."\n";
		}
		$description.='</table>'."\n";
		$description.='<!-- OEM STOP -->'."\n\n";
	}
*/




	$fid=array();
	$bez1=array();
	$bez2=array();
	$bez3=array();
	$bjvon=array();
	$bjbis=array();
	$ktype=array();
	$kba=array();
	$hubraum=array();
	$kw=array();
	$ktypes=array();
//	$results=q("SELECT * FROM t_210 WHERE ArtNr='".$artnr."' AND (KritNr=2 OR KritNr=16);", $dbshop, __FILE__, __LINE__);
	$results=q("SELECT * FROM t_210 WHERE ArtNr='".$artnr."' AND KritNr=2;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		$ktyp=$row["KritWert"]*1;
		$ktypes[$ktyp]=$ktyp;
		$results3=q("SELECT KBANr FROM t_121 WHERE KTypNr=".$ktyp.";", $dbshop, __FILE__, __LINE__);
		$row3=mysqli_fetch_array($results3);
		$results2=q("SELECT * FROM vehicles_".$_POST["lang"]." WHERE KTypNr=".$ktyp.";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results2)==0 ) error(__FILE__, __LINE__, "Fahrzeug mit KTypNr=".$ktyp." nicht in vehicles_".$_POST["lang"]." gefunden!");
		else
		{
			$row2=mysqli_fetch_array($results2);
			if ($row2["id_vehicle"]!="")
			{
				$fid[$_POST["lang"]][$row2["id_vehicle"]]=$row2["id_vehicle"];
				$bez1[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BEZ1"];
				$bez2[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BEZ2"];
				$bez3[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BEZ3"];
				$bjvon[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BJvon"];
				$bjbis[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BJbis"];
				$ktype[$_POST["lang"]][$row2["id_vehicle"]]=$row2["KTypNr"]*1;
				$kba[$_POST["lang"]][$row2["id_vehicle"]]=substr($row3["KBANr"], 0, 4).'-'.substr($row3["KBANr"], 4, 3);
				$hubraum[$_POST["lang"]][$row2["id_vehicle"]]=number_format($row2["ccmTech"]).'ccm';
				$kw[$_POST["lang"]][$row2["id_vehicle"]]=number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)';
			}
		}
	}

	$ktypes=array();
	$lfdnrs=array();
	$results=q("SELECT * FROM t_400 WHERE ArtNr='".$artnr."' AND SortNr=1 AND KritNr=2;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		$lfdnrs[]=$row["LfdNr"]*1;
		$ktyp=$row["KritWert"]*1;
		$ktypes[$ktyp]=$ktyp;
	}


	//cache shop_items_vehicles
	$vehicles=array();
	$query="SELECT * FROM shop_items_vehicles WHERE item_id=".$_POST["id_item"].";";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( isset($vehicles[$row["language_id"]][$row["vehicle_id"]]) )
		{
			q("DELETE FROM shop_items_vehicles WHERE id=".$row["id"].";", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$vehicles[$row["language_id"]][$row["vehicle_id"]]=$row["id"];
			$vres[$row["language_id"]][$row["vehicle_id"]]=$row["criteria"];
		}
	}


	//SA400 read criteria
	unset($ktyp);
	if( sizeof($lfdnrs)==0 ) $results=q("SELECT KritNr, KritWert, SortNr FROM t_400 WHERE ArtNr!=ArtNr;", $dbshop, __FILE__, __LINE__);
	else $results=q("SELECT KritNr, KritWert, SortNr FROM t_400 WHERE ArtNr='".$artnr."' AND LfdNr IN (".implode(", ", $lfdnrs).") ORDER BY LfdNr, SortNr;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		if ($row["SortNr"]==1)
		{
			//update last vehicle
			if ( isset($last_vehicle) )
			{
				$criteria='';
				for($j=0; $j<sizeof($kritnr[$_POST["lang"]]); $j++)
				{
					if( $kritwert[$_POST["lang"]][$j]=="" ) $criteria .= $kritnr[$_POST["lang"]][$j];
					else $criteria .= $kritnr[$_POST["lang"]][$j].': '.$kritwert[$_POST["lang"]][$j];
					if ( isset($kritnr[$_POST["lang"]][$j+1]) )
					{
						if ( $kritnr[$_POST["lang"]][$j+1]!="<hr />" and $kritnr[$_POST["lang"]][$j]!="<hr />" ) $criteria.=', ';
					}
				}
				if ( !isset($vehicles[$id_language[$_POST["lang"]]][$last_vehicle[$_POST["lang"]]["id_vehicle"]]) )
				{
					$query="INSERT INTO shop_items_vehicles (language_id, item_id, vehicle_id, criteria) VALUES(".$id_language[$_POST["lang"]].", ".$_POST["id_item"].", ".$last_vehicle[$_POST["lang"]]["id_vehicle"].", '".mysqli_real_escape_string($dbshop, $criteria)."');";
					q($query, $dbshop, __FILE__, __LINE__);
				}
				else
				{
					if ( $vres[$id_language[$_POST["lang"]]][$last_vehicle[$_POST["lang"]]["id_vehicle"]]!=$criteria )
					{
						$query="UPDATE shop_items_vehicles SET criteria='".mysqli_real_escape_string($dbshop, $criteria)."' WHERE id=".$vehicles[$id_language[$_POST["lang"]]][$last_vehicle[$_POST["lang"]]["id_vehicle"]].";";
						q($query, $dbshop, __FILE__, __LINE__);
					}
				}
			}
			
			//read out next vehicle
			$kritnr=array();
			$kritwert=array();
			$ktyp=$row["KritWert"]*1;
			$results2=q("SELECT * FROM vehicles_".$_POST["lang"]." WHERE KTypNr=".$ktyp.";", $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows($results2)>0 )
			{
				$row2=mysqli_fetch_array($results2);
				//get LBezNr
				$results3=q("SELECT * FROM t_120 WHERE KTypNr=".$ktyp.";", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results3)==0 )
				{
					q("DELETE FROM vehicles_".$_POST["lang"]." WHERE KTypNr=".$ktyp.";", $dbshop, __FILE__, __LINE__);
				}
				else
				{
					$t120=mysqli_fetch_array($results3);
					//get BEZ3
					$results3=q("SELECT * FROM t_012 WHERE LBezNr=".$t120["LBezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$BEZ3=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($row3["Bez"]));
					//get BEZ2
					$results3=q("SELECT * FROM t_110 WHERE KModNr=".$t120["KModNr"].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$KHerNr=$row3["KHerNr"];
					$results3=q("SELECT * FROM t_012 WHERE LBezNr=".$row3["LBezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$BEZ2=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($row3["Bez"]));
					//get BEZ1
					$results3=q("SELECT * FROM t_100 WHERE KHerNr=".$KHerNr.";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$results3=q("SELECT * FROM t_012 WHERE LBezNr=".$row3["LBezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$BEZ1=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($row3["Bez"]));
	
					//get KBA
					$kbanr='';
					$results3=q("SELECT KBANr FROM t_121 WHERE KTypNr=".$ktyp.";", $dbshop, __FILE__, __LINE__);
					while($row3=mysqli_fetch_array($results3))
					{
						if( $kbanr!="" ) $kbanr.=', ';
						$kbanr .= substr($row3["KBANr"], 0, 4).'-'.substr($row3["KBANr"], 4, 3);
					}
					//update if necessary
					if( $row2["BEZ1"]!=$BEZ1 or $row2["BEZ2"]!=$BEZ2 or $row2["BEZ3"]!=$BEZ3 )
					{
						echo 
						q("	UPDATE vehicles_".$_POST["lang"]."
							SET BEZ1='".mysqli_real_escape_string($dbshop, $BEZ1)."',
								BEZ2='".mysqli_real_escape_string($dbshop, $BEZ2)."',
								BEZ3='".mysqli_real_escape_string($dbshop, $BEZ3)."'
							WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);
					}
					$last_vehicle[$_POST["lang"]]=$row2;
					$fid[$_POST["lang"]][$row2["id_vehicle"]]=$row2["id_vehicle"];
					$bez1[$_POST["lang"]][$row2["id_vehicle"]]=$BEZ1;
					$bez2[$_POST["lang"]][$row2["id_vehicle"]]=$BEZ2;
					$bez3[$_POST["lang"]][$row2["id_vehicle"]]=$BEZ3;
					$bjvon[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BJvon"];
					$bjbis[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BJbis"];
					$ktype[$_POST["lang"]][$row2["id_vehicle"]]=$row2["KTypNr"]*1;
					$kba[$_POST["lang"]][$row2["id_vehicle"]]=$kbanr;
					$hubraum[$_POST["lang"]][$row2["id_vehicle"]]=number_format($row2["ccmTech"]).'ccm';
					$kw[$_POST["lang"]][$row2["id_vehicle"]]=number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)';
					
					//remove duplicate vehicles
					while( $row2=mysqli_fetch_array($results2) )
					{
						q("DELETE FROM vehicles_".$_POST["lang"]." WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);
					}
				}
			}
			else
			{
				$results2=q("SELECT * FROM t_120 WHERE KTypNr=".$ktyp.";", $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows($results2)>0 )
				{
					$row2=mysqli_fetch_array($results2);
					//get BEZ3
					$results3=q("SELECT * FROM t_012 WHERE LBezNr=".$row2["LBezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$BEZ3=$row3["Bez"];
					//get BEZ2
					$results3=q("SELECT * FROM t_110 WHERE KModNr=".$row2["KModNr"].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$KHerNr=$row3["KHerNr"];
					$results3=q("SELECT * FROM t_012 WHERE LBezNr=".$row3["LBezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$BEZ2=$row3["Bez"];
					//get BEZ1
					$results3=q("SELECT * FROM t_100 WHERE KHerNr=".$KHerNr.";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$results3=q("SELECT * FROM t_012 WHERE LBezNr=".$row3["LBezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$BEZ1=$row3["Bez"];
					//get KBA
					$kbanr='';
					$results3=q("SELECT KBANr FROM t_121 WHERE KTypNr=".$ktyp.";", $dbshop, __FILE__, __LINE__);
					while($row3=mysqli_fetch_array($results3))
					{
						if( $kbanr!="" )
						{
							$kbanr.=', '.$row3["KBANr"];
						}
						else $kbanr=$row3["KBANr"];
					}
					q("INSERT INTO vehicles_".$_POST["lang"]." (KBA, KTypNr, KHerNr, BEZ1, BEZ2, BEZ3, BEZ4, BEZ5, KModNr, Sort, BJvon, BJbis, kW, PS, ccmSteuer, ccmTech, Lit, Zyl, Tueren, TankInhalt, Spannung, ABS, ASR, MotArt, AntrArt, BremsArt, BremsSys, VENT, KrStoffArt, KatArt, GetrArt, AufbauArt, KRITNR, Exclude, firstmod, lastmod) VALUES('".$kbanr."', '".$row2["KTypNr"]."', '".$KHerNr."', '".$BEZ1."', '".$BEZ2."', '".$BEZ3."', '', '', '".$row2["KModNr"]."', '".$row2["Sort"]."', '".$row2["BJvon"]."', '".$row2["BJbis"]."', '".$row2["kW"]."', '".$row2["PS"]."', '".$row2["ccmSteuer"]."', '".$row2["ccmTech"]."', '".$row2["Lit"]."', '".$row2["Zyl"]."', '".$row2["Tueren"]."', '".$row2["TankInhalt"]."', '".$row2["Spannung"]."', '".$row2["ABS"]."', '".$row2["ASR"]."', '".$row2["MotArt"]."', '".$row2["AntrArt"]."', '".$row2["BremsArt"]."', '".$row2["BremsSys"]."', '".$row2["VENT"]."', '".$row2["KrSToffArt"]."', '".$row2["KatArt"]."', '".$row2["GetrArt"]."', '".$row2["AufbauArt"]."', '', '', ".time().", ".time().");", $dbshop, __FILE__, __LINE__);
					$results2=q("SELECT * FROM vehicles_".$_POST["lang"]." WHERE KTypNr=".$ktyp.";", $dbshop, __FILE__, __LINE__);
					if ( mysqli_num_rows($results2)>0 )
					{
						$row2=mysqli_fetch_array($results2);
						$last_vehicle[$_POST["lang"]]=$row2;
						$fid[$_POST["lang"]][$row2["id_vehicle"]]=$row2["id_vehicle"];
						$bez1[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BEZ1"];
						$bez2[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BEZ2"];
						$bez3[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BEZ3"];
						$bjvon[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BJvon"];
						$bjbis[$_POST["lang"]][$row2["id_vehicle"]]=$row2["BJbis"];
						$ktype[$_POST["lang"]][$row2["id_vehicle"]]=$row2["KTypNr"]*1;
						$kba[$_POST["lang"]][$row2["id_vehicle"]]=substr($row3["KBANr"], 0, 4).'-'.substr($row3["KBANr"], 4, 3);
						$hubraum[$_POST["lang"]][$row2["id_vehicle"]]=number_format($row2["ccmTech"]).'ccm';
						$kw[$_POST["lang"]][$row2["id_vehicle"]]=number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)';
						
						//remove duplicate vehicles
						while( $row2=mysqli_fetch_array($results2) )
						{
							q("DELETE FROM vehicles_".$_POST["lang"]." WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);
						}
					}
				}
			}
		}
		else
		{
			$results2=q("SELECT BezNr, Typ, TabNr FROM t_050 WHERE KritNr='".$row["KritNr"]."';", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			if( $row["KritNr"]==8 )
			{
				$kritwert[$_POST["lang"]][]="";
				$kritnr[$_POST["lang"]][]="<hr />";
			}
			elseif ($row2["Typ"]=="K")
//			$t050=$t50[$row["KritNr"]];
//			if ($t050["Typ"]=="K")
			{
				if (is_numeric($row["KritWert"]))
				{
					$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl=".$row["KritWert"].";", $dbshop, __FILE__, __LINE__);
//					$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$t050["TabNr"]." AND Schl=".$row["KritWert"].";", $dbshop, __FILE__, __LINE__);
				}
				else
				{
					$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl='".$row["KritWert"]."';", $dbshop, __FILE__, __LINE__);
//					$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$t050["TabNr"]." AND Schl='".$row["KritWert"]."';", $dbshop, __FILE__, __LINE__);
				}
				$row4=mysqli_fetch_array($results4);
				$results3=q("SELECT Bez FROM t_030 WHERE BezNr=".$row2["BezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$bez[$_POST["lang"]]=$row3["Bez"];

				$results5=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr='".$sprachnr[$_POST["lang"]]."';", $dbshop, __FILE__, __LINE__);
				$row5=mysqli_fetch_array($results5);
				$kritw[$_POST["lang"]]=$row5["Bez"];
				$kritwert[$_POST["lang"]][]=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($kritw[$_POST["lang"]]));
				$kritnr[$_POST["lang"]][]=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($bez[$_POST["lang"]]));
			}
			else 
			{
				if ($row["KritNr"] == 20 or $row["KritNr"] == 21)
				{
					$kritw[$_POST["lang"]]=substr($row["KritWert"], -2, 2).'/'.substr($row["KritWert"], 0, 4);
				}
				else $kritw[$_POST["lang"]]=$row["KritWert"];
				
				$results3=q("SELECT Bez FROM t_030 WHERE BezNr=".$row2["BezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
//					$results3=q("SELECT Bez FROM t_030 WHERE BezNr=".$t050["BezNr"]." AND SprachNr=".$sprachnr[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$bez[$_POST["lang"]]=$row3["Bez"];
//					$bez[$_POST["lang"]]=$t30[$_POST["lang"]][$row2["BezNr"]];

				//Einheiten ausschneiden
				$unit="";
				$start=strrpos($bez[$_POST["lang"]], " [");
				if ($start>0)
				{
					$end=strrpos($bez[$_POST["lang"]], "]")-$start;
					$unit=substr($bez[$_POST["lang"]], $start+2, $end-2);
					$bez[$_POST["lang"]]=substr($bez[$_POST["lang"]], 0, $start);
					echo $kritw[$_POST["lang"]].=$unit;
				}
//					$kritwert[$_POST["lang"]][$ktyp][]=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($kritw[$_POST["lang"]]));
//					$kritnr[$_POST["lang"]][$ktyp][]=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($bez[$_POST["lang"]]));
				$kritwert[$_POST["lang"]][]=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($kritw[$_POST["lang"]]));
				$kritnr[$_POST["lang"]][]=iconv("windows-".$codepage[$_POST["lang"]], "utf-8", utf8_decode($bez[$_POST["lang"]]));
			} //end for
		} //end else
	} //end while


	//update last vehicle
	if ( isset($last_vehicle) )
	{
		$criteria='';
		for($j=0; $j<sizeof($kritnr[$_POST["lang"]]); $j++)
		{
			if( $kritwert[$_POST["lang"]][$j]=="" ) $criteria .= $kritnr[$_POST["lang"]][$j];
			else $criteria .= $kritnr[$_POST["lang"]][$j].': '.$kritwert[$_POST["lang"]][$j];
			if ( isset($kritnr[$_POST["lang"]][$j+1]) )
			{
				if ( $kritnr[$_POST["lang"]][$j+1]!="<hr />" and $kritnr[$_POST["lang"]][$j]!="<hr />" ) $criteria.=', ';
			}
		}
		if ( !isset($vehicles[$id_language[$_POST["lang"]]][$last_vehicle[$_POST["lang"]]["id_vehicle"]]) )
		{
			$query="INSERT INTO shop_items_vehicles (language_id, item_id, vehicle_id, criteria) VALUES(".$id_language[$_POST["lang"]].", ".$_POST["id_item"].", ".$last_vehicle[$_POST["lang"]]["id_vehicle"].", '".mysqli_real_escape_string($dbshop, $criteria)."');";
			q($query, $dbshop, __FILE__, __LINE__);
		}
		else
		{
			if ( $vres[$id_language[$_POST["lang"]]][$last_vehicle[$_POST["lang"]]["id_vehicle"]]!=$criteria )
			{
				$query="UPDATE shop_items_vehicles SET criteria='".mysqli_real_escape_string($dbshop, $criteria)."' WHERE id=".$vehicles[$id_language[$_POST["lang"]]][$last_vehicle[$_POST["lang"]]["id_vehicle"]].";";
				q($query, $dbshop, __FILE__, __LINE__);
			}
		}
	}
	
	
	//cache all linked vehicles
	$vehicle=array();
	$results=q("SELECT * FROM shop_items_vehicles WHERE language_id=".$id_language[$_POST["lang"]]." AND item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( isset($fid[$id2lang[$row["language_id"]]][$row["vehicle_id"]]) )
		{
			$vehicle[$row["vehicle_id"]][$row["language_id"]]=$row["id"];
		}
		else
		{
			//remove wrong entries
			$query="DELETE FROM shop_items_vehicles WHERE id=".$row["id"].";";
			q($query, $dbshop, __FILE__, __LINE__);
		}
	}


	//sort by name
	if ( sizeof($fid)>0 )
	{
//		array_multisort($bez1[$_POST["lang"]], $bez2[$_POST["lang"]], $bez3[$_POST["lang"]], $bjvon[$_POST["lang"]], $bjbis[$_POST["lang"]], $fid[$_POST["lang"]], $ktype[$_POST["lang"]], $kba[$_POST["lang"]], $hubraum[$_POST["lang"]], $kw[$_POST["lang"]]);
//		exit;
		array_multisort($bez1[$_POST["lang"]], $bez2[$_POST["lang"]], $bez3[$_POST["lang"]], $bjvon[$_POST["lang"]], $bjbis[$_POST["lang"]], $fid[$_POST["lang"]], $ktype[$_POST["lang"]], $kba[$_POST["lang"]], $hubraum[$_POST["lang"]], $kw[$_POST["lang"]]);
	}

	//write to html
	$description2='';
	if (sizeof($fid[$_POST["lang"]])>0)
	{
		$criteria=array();
		$results5=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$_POST["id_item"]." AND language_id=".$id_language[$_POST["lang"]].";", $dbshop, __FILE__, __LINE__);
		while($row5=mysqli_fetch_array($results5))
		{
			$criteria[$row5["vehicle_id"]]=$row5["criteria"];
		}
		
		//build description			
		$description2.='<!-- VEHICLE APPLICATION START -->'."\n";
		$description2.='<br style="clear:both;" />'."\n";
		$description2.='<h2>'.t("Fahrzeugzuordnungen", __FILE__, __LINE__, $_POST["lang"]).'</h2>'."\n";
		$description2.='<table class="hover">'."\n";
		$description2.='<tr>'."\n";
		$description2.='	<th>'.t("Fahrzeug", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description2.='	<th width="120">'.t("Baujahr", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description2.='	<th width="100">'.t("Leistung", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description2.='	<th width="80">'.t("Hubraum", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description2.='	<th width="80">'.t("KBA-Nr.", __FILE__, __LINE__, $_POST["lang"]).'</th>'."\n";
		$description2.='</tr>'."\n";
//		print_r($fid);
//		exit;
		for($i=0; $i<sizeof($fid[$_POST["lang"]]); $i++)
		{
			$description2.='<tr>'."\n";
			$description2.='	<td><a href="http://www.mapco.de/shop_searchbycar.php?lang='.$_POST["lang"].'&id_vehicle='.$fid[$_POST["lang"]][$i].'">'.$bez1[$_POST["lang"]][$i].' '.$bez2[$_POST["lang"]][$i].' '.$bez3[$_POST["lang"]][$i].'</a>';
			$description2 .= '<br /><span style="color:#FF0000"><i>'.$criteria[$fid[$_POST["lang"]][$i]].'</i></span>'."\n";
			$description2.='</td>'."\n";
			$description2.='	<td>';
			$description2.=baujahr($bjvon[$_POST["lang"]][$i]).' - '.baujahr($bjbis[$_POST["lang"]][$i]);
			$description2.='</td>'."\n";
			$description2.='	<td>'.$kw[$_POST["lang"]][$i].'</td>'."\n";
			$description2.='	<td>'.$hubraum[$_POST["lang"]][$i].'</td>'."\n";
			$description2.='	<td>'.$kba[$_POST["lang"]][$i].'</td>'."\n";
			$description2.='</tr>'."\n";
		}
		$description2.='</table>'."\n\n";
		$description2.='<!-- VEHICLE APPLICATION STOP -->'."\n";

		if ($oe_numbers>0 or $oem_numbers>0)
		{
			$description2.='<!-- OETXT START -->'."\n";
			$description2.='<i>* '.t("Die angezeigten OE- und OEM-Nummern dienen nur zur Zuordnung technischer Daten und der Verwendungszwecke.", __FILE__, __LINE__).'</i><br /><br />'."\n";
			$description2.='<!-- OETXT STOP -->'."\n";
		}
	} //end if
	
	//update shop_items_lang
	$query="UPDATE shop_items_".$_POST["lang"]."
	   SET	title='".mysqli_real_escape_string($dbshop, $title)."',
			short_description='".mysqli_real_escape_string($dbshop, $short_description)."',
			description='".mysqli_real_escape_string($dbshop, $description.$description2)."',
			lastmod=".time().",
			lastmod_user=".$_SESSION["id_user"]."
	   WHERE id_item=".$_POST["id_item"].";";
	q($query, $dbshop, __FILE__, __LINE__);
	//update cms_articles if german
	if( $_POST["lang"]=="de" )
	{
		$query="UPDATE cms_articles
		   SET	title='".mysqli_real_escape_string($dbweb, $title)."',
				introduction='".mysqli_real_escape_string($dbweb, $short_description)."',
				article='".mysqli_real_escape_string($dbweb, $description.$description2)."',
				lastmod=".time().",
				lastmod_user=".$_SESSION["id_user"]."
		   WHERE id_article=".$item["article_id"].";";
		q($query, $dbweb, __FILE__, __LINE__);
	}
	echo $title;


	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ItemUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
	echo '	<ArtNr>'.$artnr.'</ArtNr>'."\n";
	echo '</ItemUpdateResponse>'."\n";
?>