<?php
	/******************************
	 * find missing yellow prices *
	 ******************************/
	include("config.php");
	
	//get all articles
/*
	$articles=array();
	$results=q("SELECT * FROM prpos GROUP BY ARTNR;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$articles[$row["ARTNR"]]=$row["ARTNR"];
	}
*/
	$articles=array();
	$results=q("SELECT * FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$articles[$row["MPN"]]=$row["MPN"];
	}

	//get all articles that have a yellow price
	$yellows=array();
	$results=q("SELECT * FROM prpos WHERE LST_NR=5 GROUP BY ARTNR;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$yellow[$row["ARTNR"]]=$row["ARTNR"];
	}
	
	//find alle articles that do not have a yellow price
	foreach( $articles as $artnr )
	{
		if ( !isset($yellow[$artnr]) )
		{
			echo $artnr.'<br />';
		}
	}

?>