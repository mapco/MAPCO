<?php

	include("config.php");
	
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
	$res_lang=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while ($row_lang=mysqli_fetch_array($res_lang)) 
	{
		$language[$row_lang["code"]]=$row_lang["language"];
	}

	$res=q("SELECT * FROM shop_items_keywords;", $dbshop, __LINE__, __FILE__);
	while ($row=mysqli_fetch_array($res))
	{
		$keyword[$row["GART"]][$sprachnr[$row["language_id"]]][$row["keyword"]]=$row["id_keyword"];
	}

	$shop_items=array();
	$results=q("SELECT id_item, GART FROM shop_items WHERE active = 1 order by GART;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		$shop_items[$row["id_item"]]=$row["GART"]*1;
	}
	
	$results=q("SELECT id_item, short_description FROM shop_items_de;",$dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		if (isset($shop_items[$row["id_item"]]) && $row["short_description"]!="") $GART[$shop_items[$row["id_item"]]]="";
	}	
	unset($GART[00000]);

	while (list($key, $val) = each($GART))
	{
		//GenArt Bezeichnung als Keyword
		$results2=q("SELECT a.SprachNr, a.Bez FROM t_030 AS a, t_320 AS b WHERE b.GenArtNr=".$key." AND a.BezNr=b.BezNr AND SprachNr=001;", $dbshop, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) )
		{
			$results3=q("SELECT * from shop_items_keywords WHERE GART=".$key." AND language_id='de' AND keyword='".$row2["Bez"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($results3)==0)
			{
				//Max id_keyword
				$res=q("SELECT MAX(id_keyword) as id_keyword from shop_items_keywords;", $dbshop, __FILE__, __LINE__);
				if (!mysqli_num_rows($res)>0) $id_keyword=1; 
				else {$row4=mysqli_fetch_array($res); $id_keyword=$row4["id_keyword"]+1;}
				q("INSERT INTO shop_items_keywords (id_keyword, GART, language_id, ordering, keyword, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$id_keyword.", ".$key.", 'de', 1, '".mysqli_real_escape_string($dbshop, $row2["Bez"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);		
				$results4=q("SELECT a.SprachNr, a.Bez FROM t_030 AS a, t_320 AS b WHERE b.GenArtNr=".$key." AND a.BezNr=b.BezNr AND NOT SprachNr=001;", $dbshop, __FILE__, __LINE__);
				while( $row4=mysqli_fetch_array($results4) )
				{
					$valid_lang=array_search($row2["SprachNr"], $sprachnr);
					if(isset($language[$valid_lang]))
					{
						q("INSERT INTO shop_items_keywords (id_keyword, GART, language_id, ordering, keyword, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$id_keyword.", ".$key.", '".$valid_lang."', 1, '".mysqli_real_escape_string($dbshop, $row4["Bez"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);	
					}
				}
			}
			else
			{
				$row3=mysqli_fetch_array($results3);
				$results4=q("SELECT a.SprachNr, a.Bez FROM t_030 AS a, t_320 AS b WHERE b.GenArtNr=".$key." AND a.BezNr=b.BezNr AND NOT SprachNr=001;", $dbshop, __FILE__, __LINE__);
				while( $row4=mysqli_fetch_array($results4) )
				{
					$valid_lang=array_search($row2["SprachNr"], $sprachnr);
					if(isset($language[$valid_lang]))
					{
						$results5=q("SELECT * FROM shop_items_keywords WHERE id_keyword=".$row3["id_keyword"]." AND language_id='".$valid_lang."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($results5)>0)
						{
							$row5=mysqli_fetch_array($results5);
							if($row5["keyword"]!=$row4["Bez"])
							{
								q("UPDATE shop_items_keywords SET keyword='".$row4["Bez"]."' WHERE id=".$row5["id"].";", $dbshop, __FILE__, __LINE__);
							}
						}
						else
						{
							q("INSERT INTO shop_items_keywords (id_keyword, GART, language_id, ordering, keyword, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$row3["id_keyword"].", ".$key.", '".$valid_lang."', 1, '".mysqli_real_escape_string($dbshop, $row4["Bez"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);	
						}
					}
				}
			}
		}
	}
	
?>