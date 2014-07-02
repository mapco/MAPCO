<?php
//	include("config.php");
//	session_start();
	include("../functions/cms_send_html_mail.php");
	
	$articles=array();
	$results=q("SELECT MPN, id_item FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$articles[$row["MPN"]]=$row["id_item"];
	}


	//get all articles that have a yellow price
	$yellow=array();
	$red=array();
	$ebay_ap=array();
	$ebay_mapco=array();
	$results=q("SELECT ARTNR, LST_NR, POS_0_WERT FROM prpos WHERE LST_NR=5 or LST_NR=7 or LST_NR=16815 or LST_NR=18209;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if (isset($articles[$row["ARTNR"]]))
		{
			switch ($row["LST_NR"])
			{
				case 5:			$yellow[$row["ARTNR"]]=$row["POS_0_WERT"]; break;
				case 7:			$red[$row["ARTNR"]]=$row["POS_0_WERT"]; break;
				case 16815:		$ebay_mapco[$row["ARTNR"]]=$row["POS_0_WERT"]; break;
				case 18209:		$ebay_ap[$row["ARTNR"]]=$row["POS_0_WERT"]; break;
			}
		}
		//FÜR CHECK OB GERLBER PREIS FÜR ARTIKEL VORHANDEN
		if ($row["LST_NR"]==5) $yellow2[$row["ARTNR"]]=$row["POS_0_WERT"];
	}
	
		/******************************************************
		 * PRÜFUNG OB FÜR ARTIKEL GELBE PREISE VORHANDEN SIND *
		 ******************************************************/
	$counter=0;
	$msg="<p>Zu den folgenden Mapco Artikeln ist kein gelber Preis (Preisliste) hinterlegt: </p>";
	while (list($MPN, $shopitemID) = each ($articles))
	{
		if ( !isset($yellow2[$MPN]) )
		{
			echo $msg.=$MPN."<br />";
			//exit;
			$counter++;
		}
	}
	$msg.="Die Online-Abteilung bittet um Bearbeitung";
	$dump=reset($articles);
	echo "Es gibt ".$counter." Artikel, für die kein gelber Preis hinterlegt ist. \n\r";
	
	//Send MAIL to PMs
	if (sizeof($articles)>0 && $counter>0)
	{
		$Subject="Fehlende Preise bei MAPCO Artikeln";
		$res=q("SELECT mail FROM cms_contacts WHERE department_id = 2;",  $dbweb, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			send_html_mail2($row["mail"], "MAPCO-Backend <noreply@mapco-shop.de>", $Subject, $msg);
			//send_html_mail2("nputizng@mapco.de", "MAPCO-Backend <noreply@mapco-shop.de>", $Subject, $msg);
		}
	}

//**************************************************************************************************************
//SUCHE ALLE BEI EBAY EINGESTELLTEN ARTIKEL DEREN LISTENPREIS UNTER ROT LIEGEN UND KEINEN PREISVORSCHLAG HABEN *
//**************************************************************************************************************

	//SUCHE ARTIKEL deren EbayPreis unter Rot oder keinen EbayPreis haben
	$list = array();
	while (list ($ARTNR, $rPreis) = each ($red))
	{
		//if (!isset($ebay_mapco[$ARTNR]))
		//if (!isset($ebay_mapco[$ARTNR]) || (isset($ebay_mapco[$ARTNR]) && $ebay_mapco[$ARTNR]<$rPreis))
		if (isset($ebay_mapco[$ARTNR]) && $ebay_mapco[$ARTNR]<$rPreis)
		{
		//	echo "MAPCO: ".$ARTNR."<br />";
			$list[$articles[$ARTNR]]=$ARTNR;
		}
		//if (!isset($ebay_ap[$ARTNR]))
		//if (!isset($ebay_ap[$ARTNR]) || (isset($ebay_ap[$ARTNR]) && $ebay_ap[$ARTNR]<$rPreis))
		if (isset($ebay_ap[$ARTNR]) && $ebay_ap[$ARTNR]<$rPreis)
		{
		//	echo "AP: ".$ARTNR."<br />";
			$list[$articles[$ARTNR]]=$ARTNR;
		}
	}
	//echo sizeof($list)."<br />";
	//PRICESUGGESTIONS EINLESEN
	$res=q("SELECT * FROM shop_price_suggestions where status = 1 or status = 2 or status = 4;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		if (isset($list[$row["item_id"]]))
		{
			unset($list[$row["item_id"]]);
		}
	}
	$dump=reset($list);
//	echo sizeof($list)."<br />";
/*
	$res=q("SELECT shopitem_id from ebay_auctions group by shopitem_id;", $dbshop, __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($res))
	{
		$auctionList[$row["shopitem_id"]]="";
	}
	
	while (list ( $shopitemID, $MPN) = each ($list))
	{
		if (!isset($auctionList[$shopitemID])) unset($list[$shopitemID]);
	}
*/
	$dump=reset($list);
	$count=sizeof($list);
//	echo sizeof($list)."<br />";
/*
	$res=q("SELECT ItemSKU, QuantityPurchased FROM ebay_orders_items;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($res))
	{
		$ebayOrders[$row["ItemSKU"]]=$row["QuantityPurchased"];
	}
	while (list ( $shopitemID, $MPN) = each ($list))
	{
		if (isset($ebayOrders[$MPN]))
		{
			//NIK
			$res_ins=q("INSERT INTO shop_lists_items (list_id, item_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ( 188, ".$shopitemID.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$res_ins=q("INSERT INTO shop_lists_items (list_id, item_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ( 189, ".$shopitemID.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		}
	}
*/
	
	//RECHERCHELISTE EINLESEN
	$del_counter=0;
	$res=q("SELECT * FROM shop_lists_items WHERE list_id = 184;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		//Artikel aus Liste löschen wenn in Überprüfungsliste nicht mehr vorhanden
		if (!isset($list[$row["item_id"]]))
		{
			$res_del=q("DELETE FROM shop_lists_items WHERE item_id = ".$row["item_id"].";" , $dbshop, __FILE__, __LINE__);
			$del_counter++;
		}
		$shop_list_items[$row["item_id"]]="";
	}
	//ABGLEICH: neue einträge finden
	$ins_counter=0;
	while ( list ($shopitemID, $SKU) = each ($list))
	{
		if(!isset($shop_list_items[$shopitemID])) 
		{
			$res_ins=q("INSERT INTO shop_lists_items (list_id, item_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ( 184, ".$shopitemID.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		$ins_counter++;
		}
	}

echo "Es gibt ".$count." Fehlerhafte referenzen. \n";
echo "Es wurden ".$ins_counter." neue Einträge in die Korrekturliste geschrieben. ".$del_counter." Einträge wurden entfernt";

?>