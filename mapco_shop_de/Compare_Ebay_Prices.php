<?php

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	/**************************************************************************************************************
	* Vergleich Ebay Preise AP & MAPCO 
	* AUSGABE: Anzahl der bei Mapco und nicht bei AP eingestellten Artikel
	* AUSGABE: Anzahl der bei AP und nicht bei Mapco eingestellten Artikel
	* AUSGABE: ANZAHL der gleichen Artikel
	* AUSGABE: Anzahl der gleichen Preise
	* AUSGABE: Anzahl der Preise die bei Mapco höher sind
	* AUSGABE: Anzahl der Preise die bei AP höher sind
	**************************************************************************************************************/
	
	$auctions1=array();
	$auctions2=array();	
	$prices1=array();
	$prices2=array();
	
	$res=q("SELECT account_id, SKU, StartPrice FROM ebay_auctions;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		if ($row["account_id"]==1)
		{
			if (isset($auctions1[$row["SKU"]]))
			{
				$auctions1[$row["SKU"]]++;
			}
			else
			{
				$auctions1[$row["SKU"]]=1;
			}

			if (!isset($prices1[$row["SKU"]]))
			{
				$prices1[$row["SKU"]]=$row["StartPrice"];
			}
			
			if (!isset($auctions[$row["SKU"]])) $auctions[$row["SKU"]] = 0;			
			
		}
	
		if ($row["account_id"]==2)
		{
			if (isset($auctions2[$row["SKU"]]))
			{
				$auctions2[$row["SKU"]]++;
			}
			else
			{
				$auctions2[$row["SKU"]]=1;
			}

			if (!isset($prices2[$row["SKU"]]))
			{
				$prices2[$row["SKU"]]=$row["StartPrice"];
			}

			if (!isset($auctions[$row["SKU"]])) $auctions[$row["SKU"]] = 0;			

		}
	}
	
	echo "Anzahl eingestellter Referenzen bei Ebay-Mapco: ".sizeof($auctions1)."<br />";
	echo "Anzahl eingestellter Referenzen bei Ebay-AP: ".sizeof($auctions2)."<br /><br />";
	
	$missing_mapco=0;
	$missing_ap=0;
	$match_both=0;
	$higher_mapco_prices=0;
	$higher_ap_prices=0;
	$equal_prices=0;
	
	while ( list ( $key, $val ) = each ($auctions))
	{
		if (!isset($auctions1[$key])) $missing_mapco++;
		
		if (!isset($auctions2[$key])) $missing_ap++;
		
		if (isset($auctions1[$key]) && isset($auctions2[$key])) 
		{
			$match_both++;
			
			if ($prices1[$key]==$prices2[$key]) $equal_prices++;
			if ($prices1[$key]<$prices2[$key]) $higher_mapco_prices++;
			if ($prices1[$key]>$prices2[$key]) $higher_ap_prices++;
		}
	}
	
	echo "Anzahl bei beiden Accounts eingestellter Referenzen: ".$match_both."<br />";
	echo "Anzahl nicht bei Mapco eingestellter Referenzen: ".$missing_mapco."<br />";
	echo "Anzahl nicht bei Ap eingestellter Referenzen: ".$missing_ap."<br /><br />";
	
	echo "Anzahl gleicher Preise :".$equal_prices."<br />";
	echo "Anzahl höhererer Preise bei Mapco :".$higher_mapco_prices."<br />";
	echo "Anzahl höhererer Preise bei Ap :".$higher_ap_prices."<br />";

	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
