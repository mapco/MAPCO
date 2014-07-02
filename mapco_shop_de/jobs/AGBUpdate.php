<?php
	include("../config.php");

	$agb_service = array("haendlerbund_impressum|Impressum" => "1293C20B491","haendlerbund_agb|AGB" => "12766C46A8A","haendlerbund_widerruf|Widerrufsbelehrung" => "12766C53647","haendlerbund_rueckgabe|R&uuml;ckgaberecht" => "12766C4A6BE","haendlerbund_versandinfo|Zahlung und Versand" => "12766C58F26","haendlerbund_datenschutz|Datenschutzerkl&auml;rung" => "12766C5E204","haendlerbund_batteriegesetz|Hinweise zur Batterieentsorgung" => "134CBB4D101");
	
	$agb_article = array("haendlerbund_impressum|Impressum" => "28289","haendlerbund_agb|AGB" => "28290","haendlerbund_widerruf|Widerrufsbelehrung" => "28291","haendlerbund_rueckgabe|R&uuml;ckgaberecht" => "28292","haendlerbund_versandinfo|Zahlung und Versand" => "28293","haendlerbund_datenschutz|Datenschutzerkl&auml;rung" => "28294","haendlerbund_batteriegesetz|Hinweise zur Batterieentsorgung" => "28295");
	
	$apikey='23461df3-eb9e-4c09-9ecf-0fb859a5c9fa';


	$keys=array_keys($agb_service);
	foreach($keys as $i)
	{		
		$url="https://www.hb-intern.de/www/hbm/api/live_rechtstexte.htm?APIkey=1IqJF0ap6GdDNF7HKzhFyciibdml8t4v&did=".$agb_service[$i]."&AccessToken=".$apikey;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		//curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, "INPUT DATA SCRIPT");
		curl_setopt($curl, CURLOPT_URL, $url);
		$result = curl_exec($curl);
		curl_close ($curl);

		if ( $result!="" )
		{
			q("UPDATE cms_articles SET article='".mysqli_real_escape_string($dbshop, $result)."', lastmod_user=1, lastmod=".time()." WHERE id_article=".$agb_article[$i].";", $dbweb, __FILE__, __LINE__);
		}
	}
?>