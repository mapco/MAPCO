<?php
	include("../config.php");
	
	//create models table
	echo 'Erstelle Modelltabelle ... ';
	q("DROP TABLE IF EXISTS `vehicle_models`;", $dbshop, __FILE__, __LINE__);
	q("CREATE TABLE IF NOT EXISTS `vehicle_models` (
				  `id_model` int(11) NOT NULL AUTO_INCREMENT,
				  `KModNr` int(5) unsigned zerofill NOT NULL DEFAULT '00000',
				  `manufacturer_id` int(11) NOT NULL,
				  `name` tinytext NOT NULL,
				  `parts` int(11) NOT NULL,
				  PRIMARY KEY (`id_model`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;", $dbshop, __FILE__, __LINE__);
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

	//read all models
	echo 'Modellangaben auslesen ... ';
	$kmodnrs=array();
	$manufacturer_ids=array();
	$modelnames=array();
	$models=array();
	$results=q("SELECT a.id_manufacturer, b.LBezNr, b.KModNr, d.ArtNr FROM vehicle_manufacturers AS a, t_110 AS b, t_120 AS c, t_400 AS d WHERE a.KHerNr=b.KHerNr AND b.KModNr=c.KModNr AND d.KritNr=2 AND c.KTypNr=d.KritWert GROUP BY ArtNr;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$manufacturer_ids[$row["KModNr"]]=$row["id_manufacturer"];
		$kmodnrs[$row["KModNr"]]=$row["KModNr"];
		$modelnames[$row["KModNr"]]=$names[$row["LBezNr"]];
		$models[$row["KModNr"]][sizeof($models[$row["KModNr"]])]=$row["ArtNr"];
	}
	echo 'fertig!<br />';
	
	//write all models
	echo 'Modellangaben schreiben ... ';
//	array_multisort($modelnames, SORT_ASC);
	foreach($kmodnrs as $kmodnr)
	{
		q("INSERT INTO vehicle_models (manufacturer_id, KModNr, name, parts) VALUES('".$manufacturer_ids[$kmodnr]."', '".$kmodnr."', '".$modelnames[$kmodnr]."', '".sizeof($models[$kmodnr])."');", $dbshop, __FILE__, __LINE__);
	}
	echo 'fertig!<br />';
?>