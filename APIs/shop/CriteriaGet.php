<?php
	//include("config.php");
	
	$lang=$_SESSION["lang"];
	
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
	
	$xmldata="";
	
	$result_t_050=q("SELECT * FROM t_050 WHERE KritNr!='0002' AND KritNr!='0008' AND KritNr!='0016' ORDER BY KritNr;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($result_t_050))
	{
		$xmldata.="<Krit>\n";
			$xmldata.="  <KritNr>".$row["KritNr"]."</KritNr>\n";
			$xmldata.="  <BezNr>".$row["BezNr"]."</BezNr>\n";
			$result_t_030=q("SELECT Bez FROM t_030 WHERE BezNr=".$row["BezNr"]." AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($result_t_030)==1)
			{
				$row2=mysqli_fetch_array($result_t_030);
				$xmldata.="  <KritBez>".$row2["Bez"]."</KritBez>\n";
			}
			else
			{
				$xmldata.="  <KritBez></KritBez>\n";
			}
			$xmldata.="  <Typ>".$row["Typ"]."</Typ>\n";
			$xmldata.="  <TabNr>".$row["TabNr"]."</TabNr>\n";
			if($row["TabNr"]!="000")
			{
				//************************************************************************
				$schl=array();
				$cnt=0;
				$result_t_030_3=q("SELECT * FROM t_052,t_030 WHERE t_052.BezNr=t_030.BezNr AND TabNr='".$row["TabNr"]."' AND SprachNr='001';", $dbshop, __FILE__, __LINE__);
				while($row5=mysqli_fetch_array($result_t_030_3))
				{
					if($row["KritNr"]=="0040")
					{
						if(strpos($row5["Bez"], "für")===false)
						{
							$schl[$cnt]=$row5["Schl"];
							$cnt=$cnt + 1;
						}
					}
					else if($row["KritNr"]=="0139")
					{
						if(strpos($row5["Bez"], "auch für")===false && strpos($row5["Bez"], "nicht für")===false)
						{
							$schl[$cnt]=$row5["Schl"];
							$cnt=$cnt + 1;
						}
					}
					else if($row["KritNr"]=="0514")
					{
						if(strpos($row5["Bez"], "für Fahrzeuge ohne")===false)
						{
							$schl[$cnt]=$row5["Schl"];
							$cnt=$cnt + 1;
						}
					}
					else if($row["KritNr"]=="0567")
					{
						if(strpos($row5["Bez"], "für Fahrzeuge ohne")===false && strpos($row5["Bez"], "einstellbar")===false && strpos($row5["Bez"], "ca.")===false)
						{
							$schl[$cnt]=$row5["Schl"];
							$cnt=$cnt + 1;
						}
					}
					else if($row["KritNr"]=="0608")
					{
						if(strpos($row5["Bez"], "ohne")===false && strpos($row5["Bez"], "nicht für")===false && strpos($row5["Bez"], "Verbindung")===false && strpos($row5["Bez"], "für Bremskraftverstärker")===false)
						{
							$schl[$cnt]=$row5["Schl"];
							$cnt=$cnt + 1;
						}
					}
				}					
				//************************************************************************
				$xmldata.="  <cnt>".$cnt."</cnt>\n";
				
				$xmldata.="  <TabItems>\n";
				$result_t_030_2=q("SELECT * FROM t_052,t_030 WHERE t_052.BezNr=t_030.BezNr AND TabNr='".$row["TabNr"]."' AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
				while($row3=mysqli_fetch_array($result_t_030_2))
				{
					if($row["KritNr"]=="0040" || $row["KritNr"]=="0139" || $row["KritNr"]=="0514" || $row["KritNr"]=="0567" || $row["KritNr"]=="0608")
					{
						$bez=$row3["Bez"];
						if($sprachnr[$lang]=="001")
						{
							if($row["KritNr"]=="0139")
							{
								$bez = str_replace("für ", "", $bez);
								$bez = str_replace("zeuge", "zeug", $bez);
								$bez = str_replace("mobile", "mobil", $bez);
								$bez = str_replace("maschinen", "maschine", $bez);
								$bez = str_replace("zerte", "zertes", $bez);
							}
							else if($row["KritNr"]=="0514")
							{
								$bez = str_replace("für Fahrzeuge mit ", "", $bez);
								$bez = str_replace("mischem", "mischer", $bez);
								$bez = str_replace("stetem", "stetes", $bez);
								$bez = str_replace("aktiver", "aktive", $bez);
								$bez = str_replace("nischer", "nische", $bez);
							}
							else if($row["KritNr"]=="0567")
							{
								$bez = str_replace("für Fahrzeuge mit ", "", $bez);
								$bez = str_replace("barem", "bares", $bez);
								$bez = str_replace("tivem", "tives", $bez);
								$bez = str_replace("tem S", "ter S", $bez);
								$bez = str_replace("erhöhter", "erhöhte", $bez);
								$bez = str_replace("tem F", "tes F", $bez);
							}
							else if($row["KritNr"]=="0608")
							{
								$bez = str_replace("für Fahrzeuge mit ", "", $bez);
								$bez = str_replace("für ", "", $bez);
								$bez = str_replace("er B", "e B", $bez);
								$bez = str_replace("schem", "scher", $bez);
							}
						}
						$find=array_search($row3["Schl"], $schl);
						if($find!==false)
						{
							$xmldata.="    <TabItem id='".$row3["Schl"]."'>".$bez."</TabItem>\n";
						}
					}
					else
					{
						$xmldata.="    <TabItem id='".$row3["Schl"]."'>".$row3["Bez"]."</TabItem>\n";
					}
				}
				$xmldata.="  </TabItems>\n";
			}
			if($row["KritNr"]=="0649")
			{
				$xmldata.="  <TabItems>\n";
				$result_t_400=q("SELECT * FROM t_400 WHERE KritNr='0649' GROUP BY KritWert ORDER BY KritWert;", $dbshop, __FILE__, __LINE__);
				while($row4=mysqli_fetch_array($result_t_400))
				{
					if(strpos($row4["KritWert"], "ABS: Bosch")===false)
					{
						$xmldata.="    <TabItem>".$row4["KritWert"]."</TabItem>\n";
					}
				}
				$xmldata.="  </TabItems>\n";
			}
		$xmldata.="</Krit>\n";
	}
	
	echo "<CriteriaGetResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo $xmldata;
	echo "</CriteriaGetResponse>";
?>