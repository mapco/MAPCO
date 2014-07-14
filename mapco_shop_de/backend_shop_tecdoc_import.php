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
		if ( $_FILES["file"]["name"]=="010.dat" )
		{
			//create table if not exists
			q("
				CREATE TABLE IF NOT EXISTS `t_010` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `Reserviert` varchar(22) NOT NULL,
				  `DLNr` int(4) NOT NULL,
				  `SA` int(3) NOT NULL,
				  `LKZ` varchar(3) NOT NULL,
				  `BezNr` int(9) NOT NULL,
				  `Verkehr` varchar(1) NOT NULL,
				  `WarNr` varchar(3) NOT NULL,
				  `WKZ` varchar(3) NOT NULL,
				  `WarBezNr` varchar(9) NOT NULL,
				  `Vorwahl` varchar(5) NOT NULL,
				  `IstGruppe` int(1) NOT NULL,
				  `ISOCode2` varchar(2) NOT NULL,
				  `ISOCode3` varchar(3) NOT NULL,
				  `ISOCodeNr` varchar(3) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
			", $dbshop, __FILE__, __LINE__);
			//empty table
			q("TRUNCATE TABLE t_010;", $dbshop, __FILE__, __LINE__);
			//read data to array
			$data=array();
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
					$data[]="('', '".$Reserviert."', '".$DLNr."', '".$SA."', '".$LKZ."', '".$BezNr."', '".$Verkehr."', '".$WarNr."', '".$WKZ."', '".$WarBezNr."', '".$Vorwahl."', '".$IstGruppe."', '".$ISOCode2."', '".$ISOCode3."', '".$ISOCodeNr."')";
				}
			}
			//import to SQL database
			$sql  = "INSERT INTO t_010 VALUES";
			$sql .= implode(", ", $data);
			$sql .= ";";
			q($sql, $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Satzart 10 erfolgreich importiert.</div>';
		}
		//import SA20
		elseif ( $_FILES["file"]["name"]=="020.dat" )
		{
			q("TRUNCATE TABLE t_020;", $dbshop, __FILE__, __LINE__);
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
					q("INSERT INTO t_020 VALUES('', '".$Reserviert."', '".$DLNr."', '".$SA."', '".$SprachNr."', '".$BezNr."', '".$ISOCode."', '".$Codepage."');", $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart 20 erfolgreich importiert.</div>';
		}
		//import SA100
		if ( $_FILES["file"]["name"]=="100.dat" )
		{
			//create table if not exists
			q("
				CREATE TABLE IF NOT EXISTS `t_100` (
				  `DLNr` smallint(4) unsigned zerofill NOT NULL DEFAULT '0000',
				  `SA` smallint(3) unsigned zerofill NOT NULL DEFAULT '100',
				  `KHerNr` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
				  `KHKZ` char(5) NOT NULL DEFAULT '',
				  `LBezNr` int(9) unsigned zerofill NOT NULL DEFAULT '000000000',
				  `PKW` smallint(1) NOT NULL DEFAULT '0',
				  `NKW` smallint(1) NOT NULL DEFAULT '0',
				  `VGL` smallint(1) NOT NULL DEFAULT '0',
				  `Achse` tinyint(1) NOT NULL,
				  `Motor` tinyint(1) NOT NULL,
				  `Getriebe` tinyint(1) NOT NULL,
				  `Delete` tinyint(1) NOT NULL,
				  PRIMARY KEY (`KHerNr`),
				  KEY `LBezNr` (`LBezNr`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			", $dbshop, __FILE__, __LINE__);
			//empty table
			q("TRUNCATE TABLE t_100;", $dbshop, __FILE__, __LINE__);
			//read data to array
			$data=array();
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$line=array();
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 0, 4))."'"; //DLNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 4, 3))."'"; //SA
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 7, 5))."'"; //HerNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 12, 10))."'"; //HKZ
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 22, 9))."'"; //LBezNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 31, 1))."'"; //PKW
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 32, 1))."'"; //NKW
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 33, 1))."'"; //VGL
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 34, 1))."'"; //Achse
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 35, 1))."'"; //Motor
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 36, 1))."'"; //Getriebe
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 37, 1))."'"; //Delete
					$data[]="(".implode(", ", $line).")";
				}
			}
			//import to SQL database
			$sql  = "INSERT INTO t_100 VALUES";
			$sql .= implode(", ", $data);
			$sql .= ";";
			q($sql, $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Satzart 100 erfolgreich importiert.</div>';
		}
		//import SA110
		if ( $_FILES["file"]["name"]=="110.dat" )
		{
			//create table if not exists
			q("
				CREATE TABLE IF NOT EXISTS `t_110` (
				  `DLNr` smallint(4) unsigned zerofill NOT NULL DEFAULT '0000',
				  `SA` smallint(3) unsigned zerofill NOT NULL DEFAULT '110',
				  `KModNr` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
				  `LBezNr` int(9) unsigned zerofill NOT NULL DEFAULT '000000000',
				  `KHerNr` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
				  `Sort` smallint(3) unsigned zerofill NOT NULL DEFAULT '000',
				  `BJvon` int(6) unsigned zerofill NOT NULL DEFAULT '000000',
				  `BJBis` int(6) unsigned zerofill NOT NULL DEFAULT '000000',
				  `PKW` smallint(1) NOT NULL DEFAULT '1',
				  `NKW` smallint(1) NOT NULL DEFAULT '0',
				  `Achse` tinyint(1) NOT NULL,
				  `Delete` tinyint(1) NOT NULL,
				  PRIMARY KEY (`KModNr`),
				  KEY `LBezNr` (`LBezNr`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			", $dbshop, __FILE__, __LINE__);
			//empty table
			q("TRUNCATE TABLE t_110;", $dbshop, __FILE__, __LINE__);
			//read data to array
			$data=array();
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$line=array();
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 0, 4))."'"; //DLNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 4, 3))."'"; //SA
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 7, 5))."'"; //KModNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 12, 9))."'"; //LBezNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 21, 5))."'"; //HerNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 26, 3))."'"; //SortNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 29, 6))."'"; //Bjvon
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 35, 6))."'"; //BJBis
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 41, 1))."'"; //PKW
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 42, 1))."'"; //NKW
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 43, 1))."'"; //Achse
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 44, 1))."'"; //Delete
					$data[]="(".implode(", ", $line).")";
				}
			}
			//import to SQL database
			$sql  = "INSERT INTO t_110 VALUES";
			$sql .= implode(", ", $data);
			$sql .= ";";
			q($sql, $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Satzart 110 erfolgreich importiert.</div>';
		}
		//import SA120
		if ( $_FILES["file"]["name"]=="120.dat" )
		{
			//create table if not exists
			q("
				CREATE TABLE IF NOT EXISTS `t_120` (
				  `DLNr` smallint(4) unsigned zerofill NOT NULL DEFAULT '0000',
				  `SA` smallint(3) unsigned zerofill NOT NULL DEFAULT '120',
				  `KTypNr` int(5) unsigned zerofill NOT NULL,
				  `LBezNr` int(9) unsigned zerofill NOT NULL DEFAULT '000000000',
				  `KModNr` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
				  `Sort` smallint(2) unsigned zerofill DEFAULT NULL,
				  `BJvon` int(6) DEFAULT NULL,
				  `BJbis` int(6) DEFAULT NULL,
				  `kW` smallint(4) unsigned zerofill DEFAULT NULL,
				  `PS` smallint(4) unsigned zerofill DEFAULT NULL,
				  `ccmSteuer` int(5) unsigned zerofill DEFAULT NULL,
				  `ccmTech` int(5) unsigned zerofill DEFAULT NULL,
				  `Lit` smallint(4) unsigned zerofill DEFAULT NULL,
				  `Zyl` smallint(2) unsigned zerofill DEFAULT NULL,
				  `Tueren` smallint(1) unsigned zerofill DEFAULT NULL,
				  `TankInhalt` smallint(4) unsigned zerofill DEFAULT NULL,
				  `Spannung` smallint(2) unsigned zerofill DEFAULT NULL,
				  `ABS` smallint(1) unsigned zerofill DEFAULT NULL,
				  `ASR` smallint(1) unsigned zerofill DEFAULT NULL,
				  `MotArt` smallint(3) unsigned zerofill DEFAULT NULL,
				  `KAUFB` smallint(3) DEFAULT NULL,
				  `AntrArt` smallint(3) DEFAULT NULL,
				  `BremsArt` smallint(3) DEFAULT NULL,
				  `BremsSys` smallint(3) DEFAULT NULL,
				  `VENT` smallint(2) DEFAULT NULL,
				  `KrSToffArt` smallint(3) DEFAULT NULL,
				  `KatArt` smallint(3) DEFAULT NULL,
				  `GetrArt` smallint(3) DEFAULT NULL,
				  `AufbauArt` smallint(3) DEFAULT NULL,
				  `Delete` tinyint(1) NOT NULL,
				  KEY `LBezNr` (`LBezNr`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			", $dbshop, __FILE__, __LINE__);
			//empty table
			q("TRUNCATE TABLE t_120;", $dbshop, __FILE__, __LINE__);
			//read data to array
			$data=array();
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$line=array();
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 0, 4))."'"; //DLNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 4, 3))."'"; //SA
//					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 7, 4))."'"; //Reserviert
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 11, 5))."'"; //KTypNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 16, 9))."'"; //LbezNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 25, 5))."'"; //KModNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 30, 2))."'"; //SortNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 32, 6))."'"; //Bjvon
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 38, 6))."'"; //BJBis
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 44, 4))."'"; //KW
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 48, 4))."'"; //PS
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 52, 5))."'"; //ccmSteuer
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 57, 5))."'"; //ccmTech
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 62, 4))."'"; //Lit
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 66, 2))."'"; //Zyl
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 68, 1))."'"; //Tueren
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 69, 4))."'"; //TankInhalt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 70, 2))."'"; //Spannung
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 75, 1))."'"; //ABS
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 76, 1))."'"; //ASR
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 77, 3))."'"; //MotArt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 80, 3))."'"; //Kraftstoffaufbereitungsprinzip
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 83, 3))."'"; //AntrArt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 86, 3))."'"; //BremsArt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 89, 3))."'"; //BremsSys
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 92, 2))."'"; //Ventile/Brennraum
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 94, 3))."'"; //KrStoffArt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 97, 3))."'"; //KatArt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 100, 3))."'"; //GetrArt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 103, 3))."'"; //AufbauArt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 106, 1))."'"; //Delete
					$data[]="(".implode(", ", $line).")";
				}
			}
			//import to SQL database
			$sql  = "INSERT INTO t_120 VALUES";
			$sql .= implode(", ", $data);
			$sql .= ";";
			q($sql, $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Satzart 120 erfolgreich importiert.</div>';
		}
		//import SA122
		elseif ( $_FILES["file"]["name"]=="122.dat" )
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
			echo '<div class="success">Satzart 122 erfolgreich importiert.</div>';
		}
		//import SA125
		elseif ( $_FILES["file"]["name"]=="125.dat" )
		{
			//create table if not exists
			q("
				CREATE TABLE IF NOT EXISTS `t_125` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `Reserviert` varchar(22) NOT NULL,
				  `DLNr` int(4) NOT NULL,
				  `SA` int(3) NOT NULL,
				  `KTypNr` int(5) NOT NULL,
				  `LfdNr` int(3) NOT NULL,
				  `MotNr` int(5) NOT NULL,
				  `Bjvon` varchar(6) NOT NULL,
				  `Bjbis` varchar(6) NOT NULL,
				  `LKZ` varchar(3) NOT NULL,
				  `Exclude` varchar(1) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
			", $dbshop, __FILE__, __LINE__);
			//empty table
			q("TRUNCATE TABLE t_125;", $dbshop, __FILE__, __LINE__);
			//read data to array
			$data=array();
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$line=array();
					$line[]="''"; //id
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 0, 22))."'"; //Reserviert
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 22, 4))."'"; //DLNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 26, 3))."'"; //SA
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 33, 5))."'"; //KTypNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 38, 3))."'"; //LfdNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 41, 5))."'"; //MotNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 46, 6))."'"; //Bjvon
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 52, 6))."'"; //Bjbis
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 58, 3))."'"; //LKZ
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 61, 1))."'"; //Exclude
					$data[]="(".implode(", ", $line).")";
				}
			}
			//import to SQL database
			$sql  = "INSERT INTO t_125 VALUES";
			$sql .= implode(", ", $data);
			$sql .= ";";
			q($sql, $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Satzart 125 erfolgreich importiert.</div>';
		}
		//import SA155
		elseif ( $_FILES["file"]["name"]=="155.dat" )
		{
			//create table if not exists
			q("
				CREATE TABLE IF NOT EXISTS `t_155` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `DLNr` int(4) NOT NULL,
				  `SA` int(3) NOT NULL,
				  `HerNr` int(4) NOT NULL,
				  `MotNr` int(5) NOT NULL,
				  `MCode` varchar(60) NOT NULL,
				  `BJvon` varchar(6) NOT NULL,
				  `BJbis` varchar(6) NOT NULL,
				  `kWvon` varchar(4) NOT NULL,
				  `kWbis` varchar(4) NOT NULL,
				  `PSvon` varchar(4) NOT NULL,
				  `PSbis` varchar(4) NOT NULL,
				  `Ventile` varchar(2) NOT NULL,
				  `Zyl` varchar(2) NOT NULL,
				  `VerdichtV` varchar(4) NOT NULL,
				  `VerdichtB` varchar(5) NOT NULL,
				  `DrehmV` varchar(4) NOT NULL,
				  `DrehmB` varchar(5) NOT NULL,
				  `ccmSteuerV` varchar(5) NOT NULL,
				  `ccmSteuerB` varchar(5) NOT NULL,
				  `ccmTechV` varchar(5) NOT NULL,
				  `ccmTechB` varchar(5) NOT NULL,
				  `LitSteuerV` varchar(4) NOT NULL,
				  `LitSteuerB` varchar(4) NOT NULL,
				  `LitTechV` varchar(4) NOT NULL,
				  `LitTechB` varchar(4) NOT NULL,
				  `MotVerw` varchar(3) NOT NULL,
				  `MotBauForm` varchar(3) NOT NULL,
				  `KrStoffArt` varchar(3) NOT NULL,
				  `KrStoffAuf` varchar(3) NOT NULL,
				  `MotBeatm` varchar(3) NOT NULL,
				  `UminKwV` varchar(5) NOT NULL,
				  `UminKwB` varchar(5) NOT NULL,
				  `UminDrehmV` varchar(5) NOT NULL,
				  `UminDrehmB` varchar(5) NOT NULL,
				  `Kurbel` varchar(2) NOT NULL,
				  `Bohrung` varchar(6) NOT NULL,
				  `Hub` varchar(6) NOT NULL,
				  `Motorart` varchar(3) NOT NULL,
				  `Abgasnorm` varchar(3) NOT NULL,
				  `ZylBauForm` varchar(3) NOT NULL,
				  `MotSteuer` varchar(3) NOT NULL,
				  `VentilSteuer` varchar(3) NOT NULL,
				  `KuehlArt` varchar(3) NOT NULL,
				  `VkBez` varchar(30) NOT NULL,
				  `Exclude` varchar(1) NOT NULL,
				  `Delete` int(1) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
			", $dbshop, __FILE__, __LINE__);
			//empty table
			q("TRUNCATE TABLE t_155;", $dbshop, __FILE__, __LINE__);
			//read data to array
			$data=array();
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$line=array();
					$line[]="''"; //id
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 0, 4))."'"; //DLNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 4, 3))."'"; //SA
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 7, 4))."'"; //HerNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 11, 5))."'"; //MotNr
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 16, 60))."'"; //MCode
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 76, 6))."'"; //BJvon
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 82, 6))."'"; //BJbis
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 88, 4))."'"; //kWvon
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 92, 4))."'"; //kWbis
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 96, 4))."'"; //PSvon
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 100, 4))."'"; //PSbis
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 104, 2))."'"; //Ventile
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 106, 2))."'"; //Zyl
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 108, 4))."'"; //VerdichtV
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 112, 5))."'"; //VerdichtB
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 117, 4))."'"; //DrehmV
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 121, 5))."'"; //DrehmB
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 126, 5))."'"; //ccmSteuerV
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 131, 5))."'"; //ccmSteuerB
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 136, 5))."'"; //ccmTechV
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 141, 5))."'"; //ccmTechB
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 146, 4))."'"; //LitSteuerV
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 150, 4))."'"; //LitSteuerB
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 154, 4))."'"; //LitTechV
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 158, 4))."'"; //LitTechB
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 162, 3))."'"; //MotVerw
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 165, 3))."'"; //MotBauForm
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 168, 3))."'"; //KrStoffArt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 171, 3))."'"; //KrStoffAuf
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 174, 3))."'"; //MotBeatm
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 177, 5))."'"; //UminKwV
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 182, 5))."'"; //UminKwB
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 187, 5))."'"; //UminDrehmV
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 192, 5))."'"; //UminDrehmB
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 197, 5))."'"; //Kurbel
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 199, 6))."'"; //Bohrung
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 205, 6))."'"; //Hub
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 211, 3))."'"; //Motorart
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 214, 3))."'"; //Abgasnorm
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 217, 3))."'"; //ZylBauForm
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 220, 3))."'"; //MotSteuer
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 223, 3))."'"; //VentilSteuer
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 226, 3))."'"; //KuehlArt
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 229, 30))."'"; //VkBez
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 259, 1))."'"; //Exclude
					$line[]="'".mysqli_real_escape_string($dbshop, substr($zeile, 260, 1))."'"; //Delete
					$data[]="(".implode(", ", $line).")";
				}
			}
			//import to SQL database
			$sql  = "INSERT INTO t_155 VALUES";
			$sql .= implode(", ", $data);
			$sql .= ";";
			q($sql, $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Satzart 155 erfolgreich importiert.</div>';
		}
		//import SA204
		elseif ( $_FILES["file"]["name"]=="204.dat" )
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
			echo '<div class="success">Satzart 204 erfolgreich importiert.</div>';
		}
		//import SA205
		elseif ( $_FILES["file"]["name"]=="205.dat" )
		{
			q("TRUNCATE TABLE t_205;", $dbshop, __FILE__, __LINE__);
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgets($handle, 1024))
			{
				if ($zeile!="")
				{
					$data=array();
					$data["ArtNr"]=substr($zeile, 0, 22);
					$data["EinspNr"]=substr($zeile, 22, 4);
					$data["SA"]=substr($zeile, 26, 3);
//					$data["PartGenArtNr"]=substr($zeile, 29, 5);
					$data["LfdNr"]=substr($zeile, 34, 3);
					$data["PartNr"]=substr($zeile, 37, 22);
					$data["Menge"]=substr($zeile, 59, 3);
					q_insert("t_205", $data, $dbshop, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Satzart 205 erfolgreich importiert.</div>';
		}
		//import SA210
		elseif ( $_FILES["file"]["name"]=="210.dat" )
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
			echo '<div class="success">Satzart 210 erfolgreich importiert.</div>';
		}
		//import SA212
		elseif ( $_FILES["file"]["name"]=="212.dat" )
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
			echo '<div class="success">Satzart 212 erfolgreich importiert.</div>';
		}
		//import SA232
		elseif ( $_FILES["file"]["name"]=="232.dat" )
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
			echo '<div class="success">Satzart 232 erfolgreich importiert.</div>';
		}
		//import SA233
		elseif ( $_FILES["file"]["name"]=="233.dat" )
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
			echo '<div class="success">Satzart 233 erfolgreich importiert.</div>';
		}
		else
		{
			echo '<div class="failure">Satzart zu '.$_FILES["file"]["name"].' nicht erkannt.</div>';
		}
	}

	
	//Editor
	echo '<h1>TecDoc-Satzarten importieren</h1>';
	echo '<p>Die Satzarten werden nach dem Hochladen an Ihren Dateinamen erkannt. Zum Beispiel: 010.dat = SA10, 120.dat = SA120, etc.</p>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table>';
/*
	echo '	<tr>';
	echo '		<td>Satzart</td>';
	echo '		<td>';
	echo '			<select name="sa">';
	echo '				<option value="10">SA10</option>';
	echo '				<option value="20">SA20</option>';
	echo '				<option value="122">SA122</option>';
	echo '				<option value="204">SA204</option>';
	echo '				<option value="205">SA205</option>';
	echo '				<option value="210">SA210</option>';
	echo '				<option value="212">SA212</option>';
	echo '				<option value="232">SA232</option>';
	echo '				<option value="233">SA233</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
*/
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