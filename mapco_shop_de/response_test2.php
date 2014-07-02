<?php
	include($_GET["url"]);
/*
	//utf8 äöü
	$start=time()+microtime();
	
	//connect to database
	$dbweb=mysql_connect("localhost", "admapco_1", "G7kCp4m8") or die(mysql_error());
	mysql_select_db("admapco_db1") or die(mysql_error($dbweb));
	mysql_query("SET NAMES utf8", $dbweb) or die(mysql_error($dbweb));
	$dbshop=mysql_connect("localhost","mapcoshop","merci2664") or die(mysql_error());
	mysql_select_db("admapco_mapcoshop") or die(mysql_error());
	mysql_query("SET NAMES utf8", $dbshop) or die(mysql_error());

	$results=mysql_query("SELECT * FROM web_videos WHERE id_video='".$_GET["id_video"]."';", $dbweb) or die("ERROR #1: ".mysql_error($dbweb));
	while ($row=mysql_fetch_array($results))
	{
		echo $row["id_video"];
	}

	$stop=time()+microtime();
	echo '<p>PHP-Skript-Ausführung: '.($stop-$start).'s</p>';
	*/
?>