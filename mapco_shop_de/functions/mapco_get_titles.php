<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("get_titles"))
	{
		function get_titles($artnr, $length)
		{
			global $dbweb;
			global $dbshop;
			
$einbaubez=array(1 => "zum Zylinder 1", 2 => "zum Zylinder 2", 3 => "zum Zylinder 3", 4 => "zum Zylinder 4", 5 => "zum Zylinder 5", 52 => "Ausrückgabel an Kupplungsgehäuse", 6 => "zum Zylinder 6", 7 => "zum Zylinder 7", 8 => "zum Zylinder 8", AB => "am Bremssattel", BS => "beifahrerseitig", F => "Fahrzeugfront", FB => "beidseitig", FE => "Fronteinbau", FS => "fahrerseitig", GS => "getriebeseitig", H => "hinten", HA => "Hinterachse", HD => "hinter der Achse", HG => "Hinterachse beidseitig", HL => "Hinterachse links", HO => "Hinterachse oben", HP => "Fahrzeugheckklappe", HR => "Hinterachse rechts", HS => "Fahrzeugheckscheibe", HU => "Hinterachse unten", I => "innen", L => "links", LH => "hinten links", LV => "vorne links", M => "mitte", O => "oben", R => "rechts", RH => "hinten rechts", RS => "radseitig", RV => "vorne rechts", SE => "seitlicher Einbau", U => "unten", V => "vorne", VA => "Vorderachse", VD => "vor der Achse", VG => "Vorderachse beidseitig", VH => "vorne und hinten", VL => "Vorderachse links", VR => "Vorderachse rechts");			
			//catch errors
			if ($artnr=="") return(array());

			//get generic name
			$query="SELECT GART FROM t_200 WHERE t_200.ArtNr='".$artnr."' LIMIT 1;";
			$results=q($query, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$query="SELECT BezNr FROM t_320 WHERE GenArtNr=".$row["GART"]." LIMIT 1;";
				$results2=q($query, $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$query="SELECT Bez FROM t_030 WHERE SprachNr='001' AND BezNr=".$row2["BezNr"]." LIMIT 1;";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$name=iconv("windows-1252", "utf-8", utf8_decode($row["Bez"]));
	
				//get vehicles
				$ktypnr=array();
				$eb=array();
				$i=0;
				$results=q("SELECT * FROM t_400 WHERE ArtNr='".$artnr."' ORDER BY LfdNr, SortNr;", $dbshop, __FILE__, __LINE__);
				while($row=mysqli_fetch_array($results))
				{
	//				echo $row["KritNr"].'<br />';
					if ($row["KritNr"]==2)
					{
						$ktypnr[$i]=$row["KritWert"];
						$i++;
					}
					elseif($row["KritNr"]==100)
					{
						if ($eb[$i-1]!="") $eb[$i-1].=' ';
						$eb[$i-1].=$einbaubez[$row["KritWert"]];
					}
				}
	//			print_r($eb);
				
				$results=q("SELECT * FROM t_210 WHERE ArtNr='".$artnr."' AND KritNr=100;", $dbshop, __FILE__, __LINE__);
				while($row=mysqli_fetch_array($results))
				{
					$name.=' '.$einbaubez[$row["KritVal"]];
				}
				
				
				//get vehicle applications
				$bez1=array();
				$bez2=array();
				$einbauseite=array();
				for($i=0; $i<sizeof($ktypnr); $i++)
				{
					$results2=q("SELECT * FROM vehicles_de WHERE Exclude=0 AND KTypNr='".$ktypnr[$i]."' ORDER BY BEZ1, BEZ2;", $dbshop, __FILE__, __LINE__);
					while($row2=mysqli_fetch_array($results2))
					{
						$bez1[]=$row2["BEZ1"];
						$bez2[]=$row2["BEZ2"];
						$einbauseite[]=$eb[$i];
					}
				}
	//			print_r($einbauseite);
				
				//optimize vehicle applications
				if (sizeof($bez1)>0)
				{
	//				$name=$name.' für ';
					$name=$name.' ';
					array_multisort($bez1, $bez2, $einbauseite);
	
					//remove doubles
					$bezeichnung1=array();
					$bezeichnung2=array();
					$einbaus=array();
					$bez="";
					for ($i=0; $i<sizeof($bez2); $i++)
					{
						if ($bez!=$bez2[$i])
						{
							$bezeichnung1[]=$bez1[$i];
							$bezeichnung2[]=$bez2[$i];
							$einbaus[]=$einbauseite[$i];
							$bez=$bez2[$i];
						}
	/*
						if ($bez==$bez2[$i])
						{
							$bez1=remove_element($bez1, $i);
							$bez2=remove_element($bez2, $i);
							$einbauseite=remove_element($einbauseite, $i);
							$i--;
						}
						else $bez=$bez2[$i];
	*/
					}
					$bez1=$bezeichnung1;
					$bez2=$bezeichnung2;
					$einbauseite=$einbaus;
					
				}
	//			print_r($einbauseite);
	
	/*
				print_r($bez1);
				echo '<hr />';
				print_r($bez2);
				echo '<hr />';
	*/
	
				//optimize titles
				$j=0;
				$manu="";
				$titles=array();
				$titles[$j]=$name;
				for($i=0; $i<sizeof($bez2); $i++)
				{
					if ($manu!=$bez1[$i])
					{
						if ($titles[$j]!=$name) $titles[$j].=', ';
						$titles[$j].=$bez1[$i].' ';
						$manu=$bez1[$i];
					} else $titles[$j].=', ';
					$titles[$j].=' '.$bez2[$i].' '.$einbauseite[$i];
					if (($i+1)<sizeof($bez2))
					{
						if ((strlen($titles[$j]) + strlen($bez2[$i])) > $length)
						{
							$j++;
							$manu="";
							$titles[$j]=$name;
						}
					}
				}
	
				//title with replacements
				$query="SELECT * FROM mapco_replacements;";
				$results=q($query, $dbweb, __FILE__, __LINE__);
				while ($row=mysqli_fetch_array($results))
				{
					for($j=0; $j<sizeof($titles); $j++)
					{
						$titles[$j]=str_replace($row["search"], $row["replace"], $titles[$j]);
					}
				}
			}
			else
			{
				$titles = "MAPCO - Autoteile vom Hersteller";	
			}
			return $titles;
		}
	}
?>