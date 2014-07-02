<?php
	if ( !isset($_POST["id_vehicle"]) )
	{
		echo '<VehicleUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte keine Fahrzeug-ID gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine ID für das Fahrzeug gefunden werden. Es muss eine ID für das fahrzeug geben, welches aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</VehicleUpdateResponse>'."\n";
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
	$results=q("SELECT * FROM vehicles_de WHERE id_vehicle=".$_POST["id_vehicle"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<VehicleUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fahrzeug ('.$_POST["id_vehicle"].') nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine TecDoc-Typnummer zu dem angegebenen Fahrzeug ('.$_POST["id_vehicle"].') ermittelt werden. Das fahrzeug existiert offenbar nicht und muss erst neu angelegt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</VehicleUpdateResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$ktypnr=$row["KTypNr"];

	//remove duplicates
	while( $row=mysqli_fetch_array($results) )
	{
		for($j=0; $j<sizeof($sprache); $j++)
		{
			q("DELETE FROM vehicles_".$sprache[$i]." WHERE id_vehicle=".$row["id_vehicle"].";", $dbshop, __FILE__, __LINE__);
		}
		echo '<VehicleUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Severity>Warning</Severity>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fahrzeug-Duplikat ('.$row["id_vehicle"].') gelöscht.</shortMsg>'."\n";
		echo '		<longMsg>Das Fahrzeug ('.$_POST["id_vehicle"].') wurde mehrmals gefunden. Das Fahrzeug-Duplikat ('.$row["id_vehicle"].') wurde deshalb gelöscht.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</VehicleUpdateResponse>'."\n";
	}
	

	//get vehicle data from TecDoc
	$results=q("SELECT * FROM t_120 WHERE KTypNr=".$ktypnr.";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<VehicleUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Severity>Warning</Severity>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fahrzeug ('.$_POST["id_vehicle"].') nicht gefunden und gelöscht.</shortMsg>'."\n";
		echo '		<longMsg>Die TecDoc-Typnummer zum Fahrzeug ('.$_POST["id_vehicle"].') konnte in den TecDoc-Stammdaten nicht gefunden werden. Das Fahrzeug wurde deshalb gelöscht.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</VehicleUpdateResponse>'."\n";
		for($i=0; $i<sizeof($sprache); $i++)
		{
			q("DELETE FROM vehicles_".$sprache[$i]." WHERE id_vehicle=".$_POST["id_vehicle"], $dbshop, __FILE__, __LINE__);
		}
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
					
		$query="UPDATE vehicles_".$sprache[$j]."
				SET KBANR='".$kbanr."',
					KTypNr=".$ktypnr.",
					KHerNr=".$khernr.",
					BEZ1='".$bez1."',
					BEZ2='".$bez2."',
					BEZ3='".$bez3."',
					KModNr=".$kmodnr.",
					BJvon=".$BJvon.",
					BJbis=".$BJbis.",
					kW=".$kW.",
					PS=".$PS.",
					ccmSteuer=".$ccmSteuer.",
					ccmTech=".$ccmTech.",
					Lit=".$Lit.",
					Zyl=".$Zyl.",
					Tueren=".$Tueren.",
					TankInhalt=".$TankInhalt.",
					Spannung=".$Spannung.",
					ABS=".$ABS.",
					ASR=".$ASR.",
					MotArt=".$MotArt.",
					AntrArt=".$AntrArt.",
					BremsArt=".$BremsArt.",
					BremsSys=".$BremsSys.",
					VENT=".$VENT.",
					KrStoffArt=".$KrStoffArt.",
					KatArt=".$KatArt.",
					GetrArt=".$GetrArt.",
					AufbauArt=".$AufbauArt.",
					KRITNR=".$KRITNR.",
					Exclude=".$Exclude.",
					lastmod=".time()."
				WHERE id_vehicle=".$_POST["id_vehicle"].";";
		q($query, $dbshop, __FILE__, __LINE__);
	} //end for
	
	//return success
	echo '<VehicleUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</VehicleUpdateResponse>'."\n";
?>