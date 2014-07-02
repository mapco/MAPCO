<?php

	include("config.php");
	session_start();
	
	//DE Kategorie Autoteile und Zubehör
//	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "GetCategoryFeatures", "id_account" => 1, "CategoryID" => 9884));
	//14239 uk  car parts and acc
//	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "GetCategoryFeatures", "id_account" => 8, "CategoryID" => 14239));

	//get all auctions
	for($i=0; $i<2; $i++)
	{
		$time=time()-300+3600*24*($i-1);
		$_POST["EndTimeFrom"]=gmdate("Y-m-d", $time)."T".gmdate("H:i:s.000", $time)."Z";
		$time=time()+3600*24*$i;
		$_POST["EndTimeTo"]=gmdate("Y-m-d", $time)."T".gmdate("H:i:s.000", $time)."Z";
		echo '<hr />';
		echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "GetSellerList", "id_account" => 1, "EndTimeFrom" => $_POST["EndTimeFrom"], "EndTimeTo" => $_POST["EndTimeTo"]) );
	}

?>