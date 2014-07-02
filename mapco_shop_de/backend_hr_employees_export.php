<?php
	include("config.php");

	header('Content-type: application/ms-excel');
	header( "Content-Disposition:inline; filename=employees.csv"); 
	header( "Content-Description: csv File" ); 
	header( "Pragma: no-cache" ); 
	header( "Expires: 0" );  

	//write UTF-8 BOM
//	echo '﻿';
	
	//write header
	echo 'Vorname; ';
	echo 'Mittelname; ';
	echo 'Nachname; ';
	echo 'Name; ';
	echo 'Position; ';
	echo 'Abteilung; ';
	echo 'Telefon; ';
	echo 'Fax; ';
	echo 'Mobil; ';
	echo "E-Mail\n";
	
	//write data
	$results=q("SELECT * FROM hr_employees ORDER BY lastname;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo utf8_decode($row["firstname"]).'; ';
		echo utf8_decode($row["middlename"]).'; ';
		echo utf8_decode($row["lastname"]).'; ';
		echo utf8_decode($row["firstname"].' '.$row["lastname"]).'; ';
		echo utf8_decode($row["position"]).'; ';
		echo utf8_decode($row["department"]).'; ';
		echo utf8_decode($row["phone"]).'; ';
		echo utf8_decode($row["fax"]).'; ';
		echo utf8_decode($row["mobile"]).'; ';
		echo utf8_decode($row["mail"])."\n";
	}
?>