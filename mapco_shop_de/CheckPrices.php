<?php
		/******************************************************
		 * PRÜFUNG OB FÜR ARTIKEL GELBE PREISE VORHANDEN SIND *
		 ******************************************************/
	include("config.php");
	session_start();
/*	
	$articles=array();
	$results=q("SELECT MPN FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) )
	{
		$articles[$row["MPN"]]=$row["MPN"];
	}

	//get all articles that have a yellow price
	$yellows=array();
	$results=q("SELECT ARTNR FROM prpos WHERE LST_NR=5 GROUP BY ARTNR;", $dbshop, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) )
	{
		$yellow[$row["ARTNR"]]=$row["ARTNR"];
	}
	
	//find alle articles that do not have a yellow price
	foreach( $articles as $artnr )
	{
		if ( !isset($yellow[$artnr]) )
		{
		//	echo $artnr.'<br />';
		}
	}

*/
	//GET AUCTIONS
	$res=q("SELECT account_id, shopitem_id, StartPrice, SKU FROM ebay_auctions;", $dbshop, __FILE__, __LINE__);
	while ($row=mysql_fetch_array($res))
	{
		$accounts[$row["account_id"]]=$row["account_id"];
		//FALLS VERSCHIEDENE PREISE, DANN DEN NIEDRIGSTEN SPEICHERN
		if (isset($items_account[$row["account_id"]][$row["shopitem_id"]]) && $items_account[$row["account_id"]][$row["shopitem_id"]]>$row["StartPrice"])
		{
			$items_account[$row["account_id"]][$row["shopitem_id"]]=$row["StartPrice"];
		}
		elseif(!isset($items_account[$row["account_id"]][$row["shopitem_id"]])) $items_account[$row["account_id"]][$row["shopitem_id"]]=$row["StartPrice"];
	
		$items_all[$row["shopitem_id"]]=$row["SKU"];
	}
	//entferne items, für die Preisvorschläge existieren
	$res=q("SELECT item_id FROM shop_price_suggestions WHERE status = 1 OR status = 2 OR status = 4;", $dbshop, __FILE__, __LINE__);
	while ($row=mysql_fetch_array($res))
	{
		if (isset($items_all[$row["item_id"]]))
		{
			unset($items_all[$row["item_id"]]);
			foreach ($accounts as $account)
			{
				unset($items_account[$account][$row["item_id"]]);
			}
		}
	}
	
	//PREISE VERGLEICHEN
		//ziehe roten Preis
	$res=q("SELECT ARTNR, POS_0_WERT FROM prpos WHERE LST_NR=5;", $dbshop, __FILE__, __LINE__);
	while($row=mysql_fetch_array($res))
	{
		$prpos[$row["ARTNR"]]=number_format($row["POS_0_WERT"]*1.19, 2);	
	}
	
	while( list ($shopitemID, $SKU) = each ($items_all))
	{
		foreach($accounts as $account)
		{
			if (isset($prpos[$SKU]) && isset($items_account[$account][$shopitemID]) && $prpos[$SKU]>$items_account[$account][$shopitemID]) 
			{
//				echo "Account: ".$account." - ShopItemID: ".$shopitemID." VK-Preis: ".$items_account[$account][$shopitemID]." ROT: ".$prpos[$SKU]."<br />";
				$list[$shopitemID]=$SKU;
			}
		}
	}
	
	//RECHERCHELISTE EINLESEN
	$del_counter=0;
	$res=q("SELECT * FROM shop_lists_items WHERE list_id = 184;", $dbshop, __FILE__, __LINE__);
	while ($row=mysql_fetch_array($res))
	{
		//Artikel aus Liste löschen wenn in Überprüfungsliste nicht mehr vorhanden
		if (!isset($list[$row["item_id"]]))
		{
			$res_del=q("DELETE FROM shop_lists_items WHERE item_id = ".$row["item_id"].";" , $dbshop, __FILE__, __LINE__);
			$del_counter++;
		}
		$shop_list_items[$row["item_id"]];
	}
	//ABGLEICH: neue einträge finden
	$ins_counter=0;
	while ( list ($shopitemID, $SKU) = each ($list))
	{
		if(!isset($shop_list_items[$shopitemID])) 
		{
			$res_ins=q("INSERT INTO shop_lists_items (list_id, item_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ( 184, ".$shopitemID.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
//		echo $shopitemID.'<br />';
		$ins_counter++;
		
		}
	}
echo "Es wurden ".$ins_counter." neue Einträge in die Korrekturliste geschrieben. ".$del_counter." Einträge wurden entfernt";
?>