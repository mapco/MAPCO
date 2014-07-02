<?php
	if ( !isset($_POST["KTypNr"]) )
	{
		echo '<VehicleAddResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte keine TecDoc-Typnummer gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine TecDoc-Typnummer für das Fahrzeug gefunden werden. Es muss eine TecDoc-Typnummer übermittelt werden, damit ein Fahrzeug angelegt werden kann.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</VehicleAddResponse>'."\n";
		exit;
	}

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

	//get KTypNr
	$ktypnr=$_POST["KTypNr"];
	

	//get vehicle data from TecDoc
	$results=q("SELECT * FROM t_120 WHERE KTypNr=".$ktypnr.";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<VehicleAddResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>TecDoc-Typnummer '.$ktypnr.' nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die TecDoc-Typnummer '.$ktypnr.' konnte in den TecDoc-Stammdaten nicht gefunden werden. Verknüpfungen in der Satzart 400 werden entfernt.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</VehicleAddResponse>'."\n";
/*
		$results=q("SELECT * FROM t_400 WHERE KritNr=2 AND KritWert=".$ktypnr.";", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			q("DELETE FROM t_400 WHERE LfdNr=".$row["LfdNr"].";", $dbshop, __FILE__, __LINE__);
		}
		$results=q("SELECT * FROM t_400 WHERE KritNr=16 AND KritWert=".$ktypnr.";", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			q("DELETE FROM t_400 WHERE LfdNr=".$row["LfdNr"].";", $dbshop, __FILE__, __LINE__);
		}
*/
		exit;
	}

	$row=mysqli_fetch_array($results);
	$kmodnr=$row["KModNr"];
	$lbeznr=$row["LBezNr"];
	$BJvon=$row["BJvon"];
	$BJbis=$row["BJbis"];
	$kW=$row["kW"];
	$PS=$row["PS"];
	$ccmSteuer=$row["ccmSteuer"];
	$ccmTech=$row["ccmTech"];
	$Lit=$row["Lit"];
	$Zyl=$row["Zyl"];
	$Tueren=$row["Tueren"];
	$TankInhalt=$row["TankInhalt"];
	$Spannung=$row["Spannung"];
	$ABS=$row["ABS"];
	$ASR=$row["ASR"];
	$MotArt=$row["MotArt"];
	$AntrArt=$row["AntrArt"];
	$BremsArt=$row["BremsArt"];
	$BremsSys=$row["BremsSys"];
	$VENT=$row["VENT"];
	$KrStoffArt=$row["KrSToffArt"];
	$KatArt=$row["KatArt"];
	$GetrArt=$row["GetrArt"];
	$AufbauArt=$row["AufbauArt"];
	$Exclude=0;			

	for($j=0; $j<sizeof($sprache); $j++)
	{
		$results=q("SELECT Bez from t_012 WHERE SprachNr=".$sprachnr[$sprache[$j]]." AND LBezNr=".$lbeznr.";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$bez3=iconv("windows-".$codepage[$sprache[$j]], "utf-8", utf8_decode($row["Bez"]));
		$bez3=addslashes(stripslashes($bez3));
		
		$results=q("SELECT KHerNr, Bez, PKW FROM t_110 AS a, t_012 AS b WHERE a.KModNr=".$kmodnr." AND b.SprachNr=".$sprachnr[$sprache[$j]]." AND a.LBezNr=b.LBezNr ORDER BY b.LKZ;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$bez2=iconv("windows-".$codepage[$sprache[$j]], "utf-8", utf8_decode($row["Bez"]));
		$bez2=addslashes(stripslashes($bez2));
		$khernr=$row["KHerNr"];
		if ($row["PKW"]==0) $KRITNR=16; else $KRITNR=2;
		
		$results=q("SELECT Bez FROM t_100 AS a, t_012 AS b WHERE a.KHerNr=".$khernr." AND b.SprachNr=".$sprachnr[$sprache[$j]]." AND a.LBezNr=b.LBezNr ORDER BY b.LKZ;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
//				echo "windows-".$codepage[$sprache[$j]].'<br />';
		$bez1=iconv("windows-".$codepage[$sprache[$j]], "utf-8", utf8_decode($row["Bez"]));
		$bez1=addslashes(stripslashes($bez1));
		
		$kbanr='';
		$results=q("SELECT * FROM t_121 WHERE KTypNr=".$ktypnr.";", $dbshop, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			$kbanr.=', '.$row["KBANr"];
		}

		q("INSERT INTO vehicles_".$sprache[$j]." (KBANR, KTypNr, KHerNr, BEZ1, BEZ2, BEZ3, KModNr, BJvon, BJbis, kW, PS, ccmSteuer, ccmTech, Lit, Zyl, Tueren, TankInhalt, Spannung, ABS, ASR, MotArt, AntrArt, BremsArt, BremsSys, VENT, KrStoffArt, KatArt, GetrArt, AufbauArt, KRITNR, Exclude, firstmod, lastmod) VALUES('".$kbanr."', ".$ktypnr.", ".$khernr.", '".$bez1."', '".$bez2."', '".$bez3."', ".$kmodnr.", '".$BJvon."', '".$BJbis."', '".$kW."', '".$PS."', '".$ccmSteuer."', '".$ccmTech."', '".$Lit."', '".$Zyl."', '".$Tueren."', '".$TankInhalt."', '".$Spannung."', '".$ABS."', '".$ASR."', '".$MotArt."', '".$AntrArt."', '".$BremsArt."', '".$BremsSys."', '".$VENT."', '".$KrStoffArt."', '".$KatArt."', '".$GetrArt."', '".$AufbauArt."', '".$KRITNR."', '".$Exclude."', ".time().", ".time().");", $dbshop, __FILE__, __LINE__);
	} //end for
	
	//return success
	echo '<VehicleAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</VehicleAddResponse>'."\n";
?>