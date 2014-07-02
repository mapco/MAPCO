<?php
	//include("config.php");
	
	if ( !isset($_POST["MPN"]) )
	{
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>MPN nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine MPN angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["item_lauf"]) )
	{
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>item_lauf nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Item-Laufvariable angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["KTypNr"]) )
	{
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>KTypNr nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine KTypNr angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
	}
	
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
	
	$xmldata='';
	$xmldata.="<item_lauf>".$_POST["item_lauf"]."</item_lauf>\n";
	$xmldata.="<KTypNr>".$_POST["KTypNr"]."</KTypNr>\n";
			
	//Gibt es überhaupt Kriterien?
	$result_t_400=q("SELECT * FROM t_400 WHERE ArtNr='".$_POST["MPN"]."' AND KritWert='".$_POST["KTypNr"]."' AND KritNr='0002' AND KritNr!='0008' AND KritNr!='0016' AND SortNr='00001';", $dbshop, __FILE__, __LINE__);
	$row2=mysqli_fetch_array($result_t_400);
	if(mysqli_num_rows($result_t_400)==1)
	{
		//$result_t_400_2=q("SELECT * FROM t_400 WHERE ArtNr='".$_POST["MPN"]."' AND KritWert!='".$_POST["KTypNr"]."' AND KritNr!='0002' AND KritNr!='0008' AND KritNr!='0016' AND SortNr!='00001' AND LfdNr='".$row2["LfdNr"]."';", $dbshop, __FILE__, __LINE__);
		$result_t_400_2=q("SELECT * FROM t_400 WHERE ArtNr='".$_POST["MPN"]."' AND KritWert!='".$_POST["KTypNr"]."' AND KritNr!='0002' AND KritNr!='0016' AND SortNr!='00001' AND LfdNr='".$row2["LfdNr"]."' ORDER BY SortNr;", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($result_t_400_2)>=1)
		{
			//Wenn Kriterien vorhanden, xml zusammenbauen
			$kritcnt=0;
			$xmldata.="<criteria>\n";
			//$xmldata.="<item_lauf>".$_POST["item_lauf"]."</item_lauf>\n";
			//$xmldata.="<KTypNr>".$_POST["KTypNr"]."</KTypNr>\n";
			while($row3=mysqli_fetch_array($result_t_400_2))
			{
				$xmldata.="<Krit>\n";
				$xmldata.="<cnt>".$kritcnt."</cnt>\n";
				$xmldata.="<KritNr>".$row3["KritNr"]."</KritNr>\n";
				$xmldata.="<KritWert><![CDATA[".$row3["KritWert"]."]]></KritWert>\n";
				$result_t_050=q("SELECT * FROM t_050 WHERE KritNr='".$row3["KritNr"]."';", $dbshop, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($result_t_050);
				if ($row4["Typ"]=="K")
				{
					if (is_numeric($row3["KritWert"]))
					{
						$result_t_052=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl=".$row3["KritWert"].";", $dbshop, __FILE__, __LINE__);
					}
					else
					{
						$result_t_052=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row4["TabNr"]." AND Schl='".$row3["KritWert"]."';", $dbshop, __FILE__, __LINE__);
					}
					$row5=mysqli_fetch_array($result_t_052);
					$result_t_030=q("SELECT Bez FROM t_030 WHERE BezNr=".$row5["BezNr"]." AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
					$row6=mysqli_fetch_array($result_t_030);
					$wert=$row6["Bez"];
				}
				else 
				{
					if ($row3["KritNr"] == 20 or $row3["KritNr"] == 21)
					{
						$wert=substr($row3["KritWert"], -2, 2).'/'.substr($row3["KritWert"], 0, 4);
					}
					else $wert=$row3["KritWert"];
				}
				$result_t_030_2=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
				$row7=mysqli_fetch_array($result_t_030_2);
				//$bez=iconv("windows-1252", "utf-8", $row2["Bez"]);
				$bez=$row7["Bez"];
				$bez=iconv("windows-".$codepage[$lang], "utf-8", utf8_decode($bez));
				$wert=iconv("windows-".$codepage[$lang], "utf-8", utf8_decode($wert));
				$xmldata.="<KritBez>".$bez."</KritBez>\n";
				$xmldata.="<KritWertBez><![CDATA[".$wert."]]></KritWertBez>\n";
				$xmldata.="<TabNr>".$row4["TabNr"]."</TabNr>\n";
//********************************************************************************************************
				if($row4["TabNr"]!="000")
				{
					$schl=array();
					$cnt=0;
					$result_t_030_3=q("SELECT * FROM t_052,t_030 WHERE t_052.BezNr=t_030.BezNr AND TabNr='".$row4["TabNr"]."' AND SprachNr='001';", $dbshop, __FILE__, __LINE__);
					while($row8=mysqli_fetch_array($result_t_030_3))
					{
						if($row3["KritNr"]=="0040")
						{
							if(strpos($row8["Bez"], "für")===false)
							{
								$schl[$cnt]=$row8["Schl"];
								$cnt=$cnt + 1;
							}
						}
						else if($row3["KritNr"]=="0139")
						{
							if(strpos($row8["Bez"], "auch für")===false && strpos($row8["Bez"], "nicht für")===false)
							{
								$schl[$cnt]=$row8["Schl"];
								$cnt=$cnt + 1;
							}
						}
						else if($row3["KritNr"]=="0514")
						{
							if(strpos($row8["Bez"], "für Fahrzeuge ohne")===false)
							{
								$schl[$cnt]=$row8["Schl"];
								$cnt=$cnt + 1;
							}
						}
						else if($row3["KritNr"]=="0567")
						{
							if(strpos($row8["Bez"], "für Fahrzeuge ohne")===false && strpos($row8["Bez"], "einstellbar")===false && strpos($row8["Bez"], "ca.")===false)
							{
								$schl[$cnt]=$row8["Schl"];
								$cnt=$cnt + 1;
							}
						}
						else if($row3["KritNr"]=="0608")
						{
							if(strpos($row8["Bez"], "ohne")===false && strpos($row8["Bez"], "nicht für")===false && strpos($row8["Bez"], "Verbindung")===false && strpos($row8["Bez"], "für Bremskraftverstärker")===false)
							{
								$schl[$cnt]=$row8["Schl"];
								$cnt=$cnt + 1;
							}
						}
					}
					
					
					$xmldata.="  <TabItems>\n";
					$result_t_030_3=q("SELECT * FROM t_052,t_030 WHERE t_052.BezNr=t_030.BezNr AND TabNr='".$row4["TabNr"]."' AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
					while($row7=mysqli_fetch_array($result_t_030_3))
					{
						//$xmldata.="    <TabItem id='".$row7["Schl"]."'>".$row7["Bez"]."</TabItem>\n";
						if($row3["KritNr"]=="0040" || $row3["KritNr"]=="0139" || $row3["KritNr"]=="0514" || $row3["KritNr"]=="0567" || $row3["KritNr"]=="0608")
						{
							$bez=$row7["Bez"];
							if($sprachnr[$lang]=="001")
							{
								if($row3["KritNr"]=="0139")
								{
									$bez = str_replace("für ", "", $bez);
									$bez = str_replace("zeuge", "zeug", $bez);
									$bez = str_replace("mobile", "mobil", $bez);
									$bez = str_replace("maschinen", "maschine", $bez);
									$bez = str_replace("zerte", "zertes", $bez);
								}
								else if($row3["KritNr"]=="0514")
								{
									$bez = str_replace("für Fahrzeuge mit ", "", $bez);
									$bez = str_replace("mischem", "mischer", $bez);
									$bez = str_replace("stetem", "stetes", $bez);
									$bez = str_replace("aktiver", "aktive", $bez);
									$bez = str_replace("nischer", "nische", $bez);
								}
								else if($row3["KritNr"]=="0567")
								{
									$bez = str_replace("für Fahrzeuge mit ", "", $bez);
									$bez = str_replace("barem", "bares", $bez);
									$bez = str_replace("tivem", "tives", $bez);
									$bez = str_replace("tem S", "ter S", $bez);
									$bez = str_replace("erhöhter", "erhöhte", $bez);
									$bez = str_replace("tem F", "tes F", $bez);
								}
								else if($row3["KritNr"]=="0608")
								{
									$bez = str_replace("für Fahrzeuge mit ", "", $bez);
									$bez = str_replace("für ", "", $bez);
									$bez = str_replace("er B", "e B", $bez);
									$bez = str_replace("schem", "scher", $bez);
								}
							}
							$find=array_search($row7["Schl"], $schl);
							if($find!==false)
							{
								$xmldata.="    <TabItem id='".$row7["Schl"]."'>".$bez."</TabItem>\n";
							}
						}
						else
						{
							$xmldata.="    <TabItem id='".$row7["Schl"]."'>".$row7["Bez"]."</TabItem>\n";
						}
					}
					$xmldata.="  </TabItems>\n";
				}
				if($row3["KritNr"]=="0649")
				{
					$xmldata.="  <TabItems>\n";
					$result_t_400=q("SELECT * FROM t_400 WHERE KritNr='0649' GROUP BY KritWert ORDER BY KritWert;", $dbshop, __FILE__, __LINE__);
					while($row9=mysqli_fetch_array($result_t_400))
					{
						if(strpos($row9["KritWert"], "ABS: Bosch")===false)
						{
							$xmldata.="    <TabItem>".$row9["KritWert"]."</TabItem>\n";
						}
					}
					$xmldata.="  </TabItems>\n";
				}
//********************************************************************************************************				
				/*if($row4["TabNr"]!="000")
				{
					$xmldata.="  <TabItems>\n";
					$result_t_030_3=q("SELECT * FROM t_052,t_030 WHERE t_052.BezNr=t_030.BezNr AND TabNr='".$row4["TabNr"]."' AND SprachNr='".$sprachnr[$lang]."';", $dbshop, __FILE__, __LINE__);
					while($row7=mysqli_fetch_array($result_t_030_3))
					{
						$xmldata.="    <TabItem id='".$row7["Schl"]."'>".$row7["Bez"]."</TabItem>\n";
					}
					$xmldata.="  </TabItems>\n";
				}*/
				
				$xmldata.="</Krit>\n";
				$kritcnt=$kritcnt+1;
			}
			$xmldata.="</criteria>\n";
			$xmldata.="<kritcnt>".$kritcnt."</kritcnt>\n";
		}
	}
	
	echo "<OrderItemCriteriaGetResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo $xmldata;
	echo "</OrderItemCriteriaGetResponse>";
?>