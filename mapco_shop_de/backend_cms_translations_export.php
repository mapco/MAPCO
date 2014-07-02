<?php
	include("config.php");

	header('Content-type: application/ms-excel');
	header( "Content-Disposition:inline; filename=translations.csv"); 
	header( "Content-Description: csv File" ); 
	header( "Pragma: no-cache" ); 
	header( "Expires: 0" );  

	//write UTF-8 BOM
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	
	//write header
	echo 'GERMAN; ';
	echo 'ENGLISH; ';
	echo 'ITALIAN; ';
	echo 'RUSSIAN; ';
	echo 'FRENCH; ';
	echo 'POLISH; ';
	echo "CHINESE\n";
	
	//write data
	$results=q("SELECT * FROM web_translations;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo $row["de"].'; ';
		echo $row["en"].'; ';
		echo $row["it"].'; ';
		echo $row["ru"].'; ';
		echo $row["fr"].'; ';
		echo $row["pl"].'; ';
		echo $row["zh"]."\n";
	}
?>