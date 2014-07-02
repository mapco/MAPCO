<?php
//	include("../config.php");

	//fahrz
	$fahrz=array();
	$results=q("SELECT * FROM fahrz;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$fahrz[(int)$row["KTypNr"]]=(int)$row["KTypNr"];
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
	
	
	//read all linked vehicles from SA400
	$vehicles=array();
	$results=q("SELECT * FROM t_400 WHERE KritNr=2 OR KritNr=16 GROUP BY KritNr, KritWert;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$vehicles[(int)$row["KritNr"]][(int)$row["KritWert"]]=(int)$row["KritWert"];
	}


	echo 'Erstelle Fahrzeugtabellen...';
	for($i=0; $i<sizeof($sprache); $i++)
	{
		q("
			CREATE TABLE IF NOT EXISTS `vehicles_".$sprache[$i]."` (
			  `id_vehicle` mediumint(9) NOT NULL AUTO_INCREMENT,
			  `KBANR` int(7) unsigned zerofill NOT NULL DEFAULT '0000000',
			  `KTypNr` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
			  `KHerNr` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
			  `BEZ1` char(60) NOT NULL DEFAULT '',
			  `BEZ2` char(60) NOT NULL DEFAULT '',
			  `BEZ3` char(60) NOT NULL DEFAULT '',
			  `BEZ4` char(60) NOT NULL DEFAULT '',
			  `BEZ5` char(60) NOT NULL DEFAULT '',
			  `KModNr` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
			  `Sort` int(2) unsigned zerofill NOT NULL DEFAULT '00',
			  `BJvon` int(6) unsigned zerofill NOT NULL DEFAULT '000000',
			  `BJbis` int(6) unsigned zerofill NOT NULL DEFAULT '000000',
			  `kW` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
			  `PS` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
			  `ccmSteuer` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
			  `ccmTech` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
			  `Lit` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
			  `Zyl` int(2) unsigned zerofill NOT NULL DEFAULT '00',
			  `Tueren` int(1) unsigned zerofill NOT NULL DEFAULT '0',
			  `TankInhalt` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
			  `Spannung` int(2) unsigned zerofill NOT NULL DEFAULT '00',
			  `ABS` int(1) unsigned zerofill NOT NULL DEFAULT '0',
			  `ASR` int(1) unsigned zerofill NOT NULL DEFAULT '0',
			  `MotArt` int(3) unsigned zerofill NOT NULL DEFAULT '000',
			  `AntrArt` int(3) unsigned zerofill NOT NULL DEFAULT '000',
			  `BremsArt` int(3) unsigned zerofill NOT NULL DEFAULT '000',
			  `BremsSys` int(3) unsigned zerofill NOT NULL DEFAULT '000',
			  `VENT` int(2) unsigned zerofill NOT NULL DEFAULT '00',
			  `KrStoffArt` int(3) unsigned zerofill NOT NULL DEFAULT '000',
			  `KatArt` int(3) unsigned zerofill NOT NULL DEFAULT '000',
			  `GetrArt` int(3) unsigned zerofill NOT NULL DEFAULT '000',
			  `AufbauArt` int(3) unsigned zerofill NOT NULL DEFAULT '000',
			  `KRITNR` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
			  `firstmod` int(11) NOT NULL,
			  `lastmod` int(11) NOT NULL,
			  PRIMARY KEY (`id_vehicle`),
			  KEY `KBANR` (`KBANR`),
			  KEY `KTypNr` (`KTypNr`),
			  KEY `BEZ1` (`BEZ1`),
			  KEY `BEZ2` (`BEZ2`),
			  KEY `BEZ3` (`BEZ3`),
			  KEY `BEZ4` (`BEZ4`),
			  KEY `BEZ5` (`BEZ5`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1145 ;
	", $dbshop, __FILE__, __LINE__);
	}
	echo 'fertig.<br />';


	//read out all existing vehicles
	$existing=array();
	$results=q("SELECT KRITNR, KTypNr FROM vehicles_".$sprache[0].";", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$existing[(int)$row["KRITNR"]][(int)$row["KTypNr"]]=1;
	}

	//add new vehicles
	$k=0;
	$kritnrs=array(2, 16);
	foreach( $kritnrs as $kritnr)
	{
		foreach( $vehicles[$kritnr] as $ktypnr )
		{
			if ( !isset($existing[$kritnr][$ktypnr]) )
			{
				$k++;
//				$response = post("http://www.mapco.de/soa/", Array("API" => "shop", "Action" => "VehicleAdd", "KTypNr" => $ktypnr));
				$response = post(PATH."soa/", Array("API" => "shop", "Action" => "VehicleAdd", "KTypNr" => $ktypnr));
				if ( strpos($response, "Success") === true ) echo $k.' Fahrzeug mit KTypNr='.$ktypnr.' erfolgeich erstellt.<br />';
				else
				{
					nl2br($response);
				}

				if ($k==50) break;
			}
		}
	}

	//update existing vehicles
	$results=q("SELECT * FROM vehicles_".$sprache[0]." ORDER BY lastmod;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$k++;
		//$response =  post("http://www.mapco.de/soa/", Array("API" => "shop", "Action" => "VehicleUpdate", "id_vehicle" => $row["id_vehicle"]));
	$response =  post(PATH."soa/", Array("API" => "shop", "Action" => "VehicleUpdate", "id_vehicle" => $row["id_vehicle"]));
		if ( strpos($response, "Success") === false )
		{
			echo nl2br(htmlentities($response));
		}
		else echo $k.' Fahrzeug mit id_vehicle='.$row["id_vehicle"].' erfolgreich aktualisiert.<br />';
		if ($k==100) break;
	}
?>