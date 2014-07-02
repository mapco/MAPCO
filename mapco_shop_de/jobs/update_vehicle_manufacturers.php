<?php
	include("../config.php");
	
	//create manufacturers table
	echo 'Erstelle Herstellertabelle ... ';
	q("DROP TABLE IF EXISTS `vehicle_manufacturers`;", $dbshop, __FILE__, __LINE__);
	q("CREATE TABLE IF NOT EXISTS `vehicle_manufacturers` (
				  `id_manufacturer` int(11) NOT NULL AUTO_INCREMENT,
				  `KHerNr` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
				  `name` tinytext NOT NULL,
				  `parts` int(11) NOT NULL,
				  PRIMARY KEY (`id_manufacturer`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;	", $dbshop, __FILE__, __LINE__);
	echo 'fertig!<br />';

	//read all names
	echo 'Bezeichnungen auslesen ... ';
	$names=array();
	$results=q("SELECT LBezNr, Bez FROM t_012 WHERE SprachNr=1;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$names[$row["LBezNr"]]=$row["Bez"];
	}
	echo 'fertig!<br />';

	//read all manufacturers
	echo 'Herstellerangaben auslesen ... ';
	$khernrs=array();
	$manufacturers=array();
	$results=q("SELECT a.LBezNr, a.KHerNr, d.ArtNr FROM t_100 AS a, t_110 AS b, t_120 AS c, t_400 AS d WHERE a.KHerNr=b.KHerNr AND b.KModNr=c.KModNr AND d.KritNr=2 AND c.KTypNr=d.KritWert GROUP BY ArtNr;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$khernrs[$row["KHerNr"]]=$row["KHerNr"];
		$manunames[$row["KHerNr"]]=$names[$row["LBezNr"]];
		$manufacturers[$row["KHerNr"]][sizeof($manufacturers[$row["KHerNr"]])]=$row["ArtNr"];
	}
	echo 'fertig!<br />';
	
	//write all manufacturers
	echo 'Herstellerangaben schreiben ... ';
//	array_multisort($manunames, SORT_ASC);
	foreach($khernrs as $khernr)
	{
		q("INSERT INTO vehicle_manufacturers (KHerNr, name, parts) VALUES('".$khernr."', '".$manunames[$khernr]."', '".sizeof($manufacturers[$khernr])."');", $dbshop, __FILE__, __LINE__);
	}
	echo 'fertig!<br />';
?>