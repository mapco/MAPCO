<?php

	$countSuccess=0;
	$countFailure=0;
	$inserted=0;
	$updated=0;

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

	//get codepages
	$codepage=array();
	$results=q("SELECT * FROM t_020;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$codepage[$row["ISOCode"]]=$row["Codepage"];
		if ($row["ISOCode"]=="en") $cp=$row["Codepage"];
	}
	$codepage["zh"]=$cp; //Chinese as English


	//GET SYSTEM LANGUAGES
	$language=array();
	$lang_nr=array();
	$res_lang=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while ($row_lang=mysqli_fetch_array($res_lang))
	{
		$language[$row_lang["code"]]=$row_lang["id_language"];
		$lang_nr[]=$sprachnr[$row_lang["code"]];
	}

	//get known keywords
	$res=q("SELECT * FROM shop_items_keywords;", $dbshop, __LINE__, __FILE__);
	while ($row=mysqli_fetch_array($res))
	{
		$keyword[$row["GART"]][$sprachnr[$row["language_id"]]][$row["keyword"]]="";
	}

	//get all used GARTs
	$results=q("SELECT GART FROM shop_items GROUP BY GART ORDER BY GART;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		$GART[$row["GART"]*1]=$row["GART"]*1;
	}
	unset($GART[00000]);


	while (list($key, $val) = each($GART))
	{
		//GenArt-Bezeichnung als Keyword
		$results=q("SELECT a.SprachNr, a.Bez FROM t_030 AS a, t_320 AS b WHERE b.GenArtNr=".$key." AND a.BezNr=b.BezNr AND SprachNr IN (".implode(", ", $lang_nr).");", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			//get GART
			$gart=$key*1;

			//get language
			$valid_lang=array_search($row["SprachNr"], $sprachnr);
			$id_language=$language[$valid_lang];
			
			//get keyword
			$keyword=iconv("windows-".$codepage[$valid_lang], "utf-8", utf8_decode($row["Bez"]));

			//check if keyword is new
			$exists=false;
			$results5=q("SELECT * FROM shop_items_keywords WHERE GART=".$gart." AND language_id='".$id_language."';", $dbshop, __FILE__, __LINE__);
			while( $row5=mysqli_fetch_array($results5) )
			{
				if( $row5["keyword"]==$keyword )
				{
					$exists=true;
					break;
				}
			}

			//if keyword is new add it
			if( !$exists )
			{
				$varField["API"]="shop";
				$varField["Action"]="GartKeywordAdd";
				$varField["GART"]=$gart;
				$varField["id_language"]=$id_language;
				$varField["keyword"]=$keyword;
				$response=post(PATH."soa/", $varField)."\n";
				if (strpos($response,"<Ack>Success</Ack>"))
				{
					$countSuccess++;
				}
				else 
				{
					$countFailure++;
					echo $response;
				}
				$inserted++;
			}
		}//end while( $row=mysqli_fetch_array($results) )
	} //end while (list($key, $val) = each($GART))


	//ALSO ADD TECDOC SYNONYMS
	$results=q("SELECT * FROM t_327 WHERE GenArtNr IN (".implode(", ", $GART).") AND SprachNr IN (".implode(", ", $lang_nr).");", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )	
	{
		//get GART
		$gart=$row["GenArtNr"]*1;
		
		//get language
		$valid_lang=array_search($row["SprachNr"], $sprachnr);
		$id_language=$language[$valid_lang];
		
		//get keyword
		$keyword=iconv("windows-".$codepage[$valid_lang], "utf-8", utf8_decode($row["Bez"]));

		//check if keyword is new
		$exists=false;
		$results5=q("SELECT * FROM shop_items_keywords WHERE GART=".$gart." AND language_id=".$id_language.";", $dbshop, __FILE__, __LINE__);
		while( $row5=mysqli_fetch_array($results5) )
		{
			if( $row5["keyword"]==$keyword )
			{
				$exists=true;
				break;
			}
		}

		//if keyword is new add it
		if( !$exists )
		{
			$varField["API"]="shop";
			$varField["Action"]="GartKeywordAdd";
			$varField["GART"]=$gart;
			$varField["id_language"]=$id_language;
			$varField["keyword"]=$keyword;
			$response=post(PATH."soa/", $varField)."\n";
			if (strpos($response,"<Ack>Success</Ack>"))
			{
				$countSuccess++;
			}
			else 
			{
				$countFailure++;
				echo $response;
			}
			$inserted++;
		}
	}


	echo '<GartKeywordTecDocUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Inserted>'.$inserted.'</Inserted>'."\n";
	echo '	<Updated>'.$updated.'</Updated>'."\n";
	echo '	<Response><![CDATA['.$countSuccess.' Keywords eigefügt. '.$countFailure.' Fehler aufgetreten]]></Response>'."\n";
	echo '</GartKeywordTecDocUpdateResponse>'."\n";

?>