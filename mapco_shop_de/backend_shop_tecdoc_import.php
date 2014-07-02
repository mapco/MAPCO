<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");


	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > TecDoc-Import';
	echo '</p>';




	//import data
	if (isset($_POST["import"]))
	{
		//import SA10
		if ($_POST["sa"]==10)
		{
			q("TRUNCATE TABLE t_".str_pad($_POST["sa"], 3, '0', STR_PAD_LEFT).";", $dbshop, __FILE__, __LINE__);
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$Reserviert=substr($zeile, 0, 22);
					$DLNr=substr($zeile, 22, 4);
					$SA=substr($zeile, 26, 3);
					$LKZ=substr($zeile, 29, 3);
					$BezNr=substr($zeile, 32, 9);
					$Verkehr=substr($zeile, 41, 1);
					$WarNr=substr($zeile, 42, 3);
					$WKZ=substr($zeile, 45, 3);
					$WarBezNr=substr($zeile, 48, 9);
					$Vorwahl=substr($zeile, 57, 5);
					$IstGruppe=substr($zeile, 62, 1);
					$ISOCode2=substr($zeile, 63, 2);
					$ISOCode3=substr($zeile, 65, 3);
					$ISOCodeNr=substr($zeile, 68, 3);
					q("INSERT INTO t_".str_pad($_POST["sa"], 3, '0', STR_PAD_LEFT)." VALUES('', '".$Reserviert."', '".$DLNr."', '".$SA."', '".$LKZ."', '".$BezNr."', '".$Verkehr."', '".$WarNr."', '".$WKZ."', '".$WarBezNr."', '".$Vorwahl."', '".$IstGruppe."', '".$ISOCode2."', '".$ISOCode3."', '".$ISOCodeNr."');", $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart '.$_POST["sa"].' erfolgreich importiert.</div>';
		}
		//import SA20
		if ($_POST["sa"]==20)
		{
			q("TRUNCATE TABLE t_".str_pad($_POST["sa"], 3, '0', STR_PAD_LEFT).";", $dbshop, __FILE__, __LINE__);
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$Reserviert=substr($zeile, 0, 22);
					$DLNr=substr($zeile, 22, 4);
					$SA=substr($zeile, 26, 3);
					$SprachNr=substr($zeile, 29, 3);
					$BezNr=substr($zeile, 32, 9);
					$ISOCode=substr($zeile, 41, 2);
					$Codepage=substr($zeile, 43, 4);
					q("INSERT INTO t_".str_pad($_POST["sa"], 3, '0', STR_PAD_LEFT)." VALUES('', '".$Reserviert."', '".$DLNr."', '".$SA."', '".$SprachNr."', '".$BezNr."', '".$ISOCode."', '".$Codepage."');", $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart '.$_POST["sa"].' erfolgreich importiert.</div>';
		}
		//import SA122
		if ($_POST["sa"]==122)
		{
			q("TRUNCATE TABLE t_122;", $dbshop, __FILE__, __LINE__);
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$Reserviert=substr($zeile, 0, 22);
					$DLNr=substr($zeile, 22, 4);
					$SA=substr($zeile, 26, 3);
					$KTypNr=substr($zeile, 29, 5);
					$LKZ=substr($zeile, 34, 3);
					$Exclude=substr($zeile, 37, 1);
					q("INSERT INTO t_122 VALUES('', '".$Reserviert."', '".$DLNr."', '".$SA."', '".$KTypNr."', '".$LKZ."', '".$Exclude."');", $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart '.$_POST["sa"].' erfolgreich importiert.</div>';
		}
		//import SA204
		if ($_POST["sa"]==204)
		{
			q("TRUNCATE TABLE t_204;", $dbshop, __FILE__, __LINE__);
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$artnr=substr($zeile, 0, 22);
					$DLNr=substr($zeile, 22, 4);
					$SA=substr($zeile, 26, 3);
					$LKZ=substr($zeile, 29, 3);
					$EArtNr=substr($zeile, 32, 22);
					$Exclude=substr($zeile, 54, 1);
					$Sort=substr($zeile, 55, 5);
					q("INSERT INTO t_204 VALUES('', '".$artnr."', '".$DLNr."', '".$SA."', '".$LKZ."', '".$EArtNr."', '".$Exclude."', '".$Sort."');", $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart '.$_POST["sa"].' erfolgreich importiert.</div>';
		}
		//import SA210
		if ($_POST["sa"]==210)
		{
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$ArtNr=substr($zeile, 0, 22);
					$DLNr=substr($zeile, 22, 4);
					$SA=substr($zeile, 26, 3);
					$GenArtNr=substr($zeile, 29, 5);
					$LKZ=substr($zeile, 34, 3);
					$SortNr=substr($zeile, 37, 3);
					$KritNr=substr($zeile, 40, 4);
					$KritWert=substr($zeile, 44, 20);
					$AnzSofort=substr($zeile, 64, 1);
					$Exclude=substr($zeile, 65, 1);
					if (isset($test[$ArtNr][$SortNr])) echo '<br />'.$ArtNr;
					else $test[$ArtNr][$SortNr]=$SortNr;
				}
			}
//			exit;
			q("TRUNCATE TABLE t_210;", $dbshop, __FILE__, __LINE__);
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$ArtNr=substr($zeile, 0, 22);
					$DLNr=substr($zeile, 22, 4);
					$SA=substr($zeile, 26, 3);
					$GenArtNr=substr($zeile, 29, 5);
					$LKZ=substr($zeile, 34, 3);
					$SortNr=substr($zeile, 37, 3);
					$KritNr=substr($zeile, 40, 4);
					$KritWert=substr($zeile, 44, 20);
					$AnzSofort=substr($zeile, 64, 1);
					$Exclude=substr($zeile, 65, 1);
					q("INSERT INTO t_210 VALUES('".$ArtNr."', '".$DLNr."', '".$SA."', '".$GenArtNr."', '".$LKZ."', '".$SortNr."', '".$KritNr."', '".$KritWert."', '".$AnzSofort."', '".$Exclude."');", $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart '.$_POST["sa"].' erfolgreich importiert.</div>';
		}
		//import SA212
		if ($_POST["sa"]==212)
		{
			q("TRUNCATE TABLE t_212;", $dbshop, __FILE__, __LINE__);
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$ArtNr=substr($zeile, 0, 22);
					$DLNr=substr($zeile, 22, 4);
					$SA=substr($zeile, 26, 3);
					$LKZ=substr($zeile, 29, 3);
					$VPE=substr($zeile, 32, 5);
					$MengeProVPE=substr($zeile, 37, 5);
					$ArtStat=substr($zeile, 42, 5);
					$StatusDat=substr($zeile, 45, 8);
					q("INSERT INTO t_212 VALUES('".$ArtNr."', '".$DLNr."', '".$SA."', '".$LKZ."', '".$VPE."', '".$MengeProVPE."', '".$ArtStat."', '".$StatusDat."');", $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart '.$_POST["sa"].' erfolgreich importiert.</div>';
		}
		//import SA232
		if ($_POST["sa"]==232)
		{
			q("TRUNCATE TABLE t_232;", $dbshop, __FILE__, __LINE__);
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$artnr=substr($zeile, 0, 22);
					$DLNr=substr($zeile, 22, 4);
					$SA=substr($zeile, 26, 3);
					$SortNr=substr($zeile, 29, 2);
					$LKZ=substr($zeile, 31, 3);
					$Exclude=substr($zeile, 34, 1);
					$BildNr=substr($zeile, 35, 9);
					$DokumentenArt=substr($zeile, 44, 2);
					q("INSERT INTO t_232 VALUES('".$artnr."', '".$DLNr."', '".$SA."', '".$SortNr."', '".$LKZ."', '".$Exclude."', '".$BildNr."', '".$DokumentenArt."');", $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart '.$_POST["sa"].' erfolgreich importiert.</div>';
		}
		//import SA233
		if ($_POST["sa"]==233)
		{
			q("TRUNCATE TABLE t_233;", $dbshop, __FILE__, __LINE__);
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$DLNr=substr($zeile, 0, 3);
					$SA=substr($zeile, 4, 3);
					$BildNr=substr($zeile, 7, 9);
					$DokumentenArt=substr($zeile, 16, 2);
					$KoordinatenNr=substr($zeile, 18, 4);
					$LfdNr=substr($zeile, 22, 3);
					$SprachNr=substr($zeile, 25, 3);
					$Typ=substr($zeile, 28, 1);
					$X1=substr($zeile, 29, 4);
					$Y1=substr($zeile, 33, 4);
					$X2=substr($zeile, 37, 4);
					$Y2=substr($zeile, 41, 4);
					q("INSERT INTO t_233 VALUES(".$DLNr.", ".$SA.", ".$BildNr.", ".$DokumentenArt.", ".$KoordinatenNr.", ".$LfdNr.", ".$SprachNr.", ".$Typ.", ".$X1.", ".$Y1.", ".$X2.", '".$Y2."');", $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart '.$_POST["sa"].' erfolgreich importiert.</div>';
		}
	}

	
	//Editor
	echo '<h1>TecDoc-Satzarten importieren</h1>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Satzart</td>';
	echo '		<td>';
	echo '			<select name="sa">';
	echo '				<option value="10">SA10</option>';
	echo '				<option value="20">SA20</option>';
	echo '				<option value="122">SA122</option>';
	echo '				<option value="204">SA204</option>';
	echo '				<option value="210">SA210</option>';
	echo '				<option value="212">SA212</option>';
	echo '				<option value="232">SA232</option>';
	echo '				<option value="233">SA233</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datei</td>';
	echo '		<td><input type="file" name="file" /></td>';
	echo '	</tr>';
	echo '	<tr><td colspan="2"><input style="float:right;" type="submit" name="import" value="Importieren" /></td></tr>';
	echo '</table>';
	echo '</form>';
/*	
	$sprachnr=16;
	$results=q("SELECT * FROM t_020 WHERE SprachNr=".$sprachnr." LIMIT 1;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$cp=$row["Codepage"];
	$results=q("SELECT * FROM t_030 WHERE SprachNr=".$sprachnr." ORDER BY Bez LIMIT 1000, 100;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
//		echo $row["Bez"].'<br />';
		echo iconv("windows-".$cp, "utf-8", $row["Bez"]).'<br />';
//		echo iconv("utf-8", "windows_1251", $row["Bez"]).'<br />';
	}
*/

	//create t_020
	q("
		CREATE TABLE IF NOT EXISTS `t_020` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `Reserviert` varchar(22) NOT NULL,
		  `DLNr` int(4) unsigned zerofill NOT NULL,
		  `SA` int(3) unsigned zerofill NOT NULL,
		  `SprachNr` int(3) unsigned zerofill NOT NULL,
		  `BezNr` int(9) unsigned zerofill NOT NULL,
		  `ISOCode` varchar(2) NOT NULL,
		  `Codepage` int(4) unsigned zerofill NOT NULL,
		  PRIMARY KEY (`ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	", $dbshop, __FILE__, __LINE__);
	//create t_122
	q("
		CREATE TABLE IF NOT EXISTS `t_122` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `Reserviert` varchar(22) NOT NULL,
		  `DLNr` int(4) unsigned zerofill NOT NULL,
		  `SA` varchar(3) NOT NULL,
		  `KTypNr` varchar(5) NOT NULL,
		  `LKZ` varchar(3) NOT NULL,
		  `Exclude` varchar(1) NOT NULL,
		  PRIMARY KEY (`ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
	", $dbshop, __FILE__, __LINE__);
	//create t_232
	q("
		CREATE TABLE IF NOT EXISTS `t_232` (
		  `ArtNr` char(22) NOT NULL DEFAULT '',
		  `DLNr` smallint(4) unsigned zerofill NOT NULL DEFAULT '0133',
		  `SA` smallint(3) unsigned zerofill NOT NULL DEFAULT '232',
		  `SortNr` smallint(2) unsigned zerofill NOT NULL DEFAULT '00',
		  `LKZ` char(3) NOT NULL DEFAULT '',
		  `Exclude` smallint(1) unsigned zerofill NOT NULL DEFAULT '0',
		  `BildNr` int(9) unsigned zerofill NOT NULL DEFAULT '000000000',
		  `DokumentenArt` smallint(2) unsigned zerofill NOT NULL DEFAULT '00',
		  PRIMARY KEY (`ArtNr`,`SortNr`),
		  KEY `ArtNr` (`ArtNr`),
		  KEY `BildNr` (`BildNr`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		", $dbshop, __FILE__, __LINE__);
	//create t_233
	q("
		CREATE TABLE IF NOT EXISTS `t_233` (
		  `DLNr` int(4) unsigned zerofill NOT NULL,
		  `SA` int(3) unsigned zerofill NOT NULL,
		  `BildNr` int(9) unsigned zerofill NOT NULL,
		  `DokumentenArt` int(2) unsigned zerofill NOT NULL,
		  `KoordinatenNr` int(4) unsigned zerofill NOT NULL,
		  `LfdNr` int(3) unsigned zerofill NOT NULL,
		  `SprachNr` int(3) unsigned zerofill NOT NULL,
		  `Typ` int(1) unsigned zerofill NOT NULL,
		  `X1` int(4) unsigned zerofill NOT NULL,
		  `Y1` int(4) unsigned zerofill NOT NULL,
		  `X2` int(4) unsigned zerofill NOT NULL,
		  `Y2` int(4) unsigned zerofill NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		", $dbshop, __FILE__, __LINE__);
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>