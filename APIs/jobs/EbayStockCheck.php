<?php
	$starttime = time() + microtime();

	$start = time();

	//get all GARTs
	$gart = array();
	$results = q("SELECT MPN FROM shop_items WHERE GART = 00286;", $dbshop, __FILE__, __LINE__);
	while( $row = mysqli_fetch_assoc( $results ) )
	{
		$gart[$row["MPN"]] = 1;
	}
	unset ( $results );

	//determine low availabilities
	$nullbestand = array();
	$res = q("SELECT * FROM lager;", $dbshop,  __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc( $res ) )
	{
		$bestand = ($row['ISTBESTAND']*1) + ($row['MOCOMBESTAND']*1) + ($row['ONLINEBESTAND']*1);
		$minbestand = 3;
		
		//steering gear exception
		if( isset ( $gart[$row['ArtNr']] ) )
		{
			$minbestand=1;
		}
		
		if ( $bestand < $minbestand ) 
		{
			if ( $row['ONLINEBESTAND']*1 == 0 )
			{
				$nullbestand[$row['ArtNr']] = '';
			}
		}
	}

//AUSNAHMEN

unset ($nullbestand['59818/1HPS']);
//unset ($nullbestand[76531]);



	//ITEM IDs zu MPN suchen
	$res=q('SELECT * FROM shop_items', $dbshop,  __FILE__, __LINE__);
	while ( $row=mysqli_fetch_assoc( $res ) ) 
	{
		if ( isset( $nullbestand[$row['MPN']] ) ) 
		{ 
			$nullbestand[$row['MPN']] = $row['id_item'];
		}
	}
	
	//$dump=reset($nullbestand);
//echo sizeof($nullbestand);

//ENTFERNEN ALLER EINTRÄGE OHNE ITEM ID ZUORDNUNG
	foreach ( $nullbestand as $key => $val )
//	while ( list($key, $val) = each ($nullbestand) ) 
	{
		if ( $val == '' ) 
		{
			unset( $nullbestand[$key] );
		}
		else
		{
			// ARRAY SPEICHERT AN SPÄTERER STELLE DIE ANZAHL DER EBAYVERKÄUFE DES ARTIKELS
			$nullbestand[$key] = 0;	
		}
	}
	//$dump=reset($nullbestand);
//echo sizeof($nullbestand);

/*
//ENTFERNEN ALLER EINTRÄGE DIE BEI EBAY NICHT AKTIV SIND
$res=q("SELECT item_id FROM ebay_accounts_items WHERE active = '1';",$dbshop,  __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res)) $ebay_accout_items[$row["item_id"]]="";
while (list($key, $val) = each ($nullbestand)) if (!isset($ebay_accout_items[$val])) unset($nullbestand[$key]);
$dump=reset($nullbestand);
*/

	//AUCTIONS_IDs ZU DEN ITEM_IDs SUCHEN
	
	//PUT ALL MPNs into simple array
	$MPNs = array();
	foreach ( $nullbestand as $MPN => $qty_sold )
	{
		$MPNs[] = $MPN;	
	}
	
	$ebay_auctions = array();
	//$res=q("SELECT id_auction, shopitem_id, ItemID, SKU, account_id FROM ebay_auctions;", $dbshop,  __FILE__, __LINE__);
	
	$res=q("SELECT id_auction, shopitem_id, ItemID, SKU, account_id FROM ebay_auctions WHERE SKU IN ('".implode("', '", $MPNs )."')", $dbshop,  __FILE__, __LINE__);
	
	while ( $row = mysqli_fetch_assoc( $res ) ) 
	{
		if ( isset( $nullbestand[$row['SKU']] ) ) 
		{
			$ebay_auctions[$row['id_auction']] = $row['SKU'];
		}
	}
	//echo sizeof($ebay_auctions);
	
	//GET SOLD ITEMS
	$res = q("SELECT QuantityPurchased, ItemSKU FROM ebay_orders_items WHERE ItemSKU IN ('".implode("', '", $MPNs )."') AND CreatedDateTimestamp > ".( time() - (90*24*3600) ), $dbshop,  __FILE__, __LINE__);

	while ( $row = mysqli_fetch_assoc( $res ) )
	{
		//if ( isset( $nullbestand[$row['ItemSKU']] ) ) 
		{
			$nullbestand[$row['ItemSKU']] += $row['QuantityPurchased'];
		}
	}
	
	echo 'ANZAHL zu beendender Auktionen: '.sizeof($ebay_auctions)."\n";
	
	if ( sizeof($ebay_auctions) > 0 ) 
	{
		
		$id_auction=array();
		$MPN_auctions = array();
		foreach ( $ebay_auctions as $auction_id => $SKU )
		{
			$MPN_auctions[$SKU][] = $auction_id;
			$id_auction[] = $auction_id;
		}
		
		foreach ( $MPN_auctions as $mpn => $auctions )
		{
			if ( $nullbestand[$mpn] > 9 )
			{
				//show_error(11366, 7, __FILE__, __LINE__);
				$msg = 'Der Artikel '.$mpn.' wurde '.$nullbestand[$mpn].' mal verkauft und wegen Fehlbestandes beendet. Auction_ids: '.implode(', ', $auctions)."\n";	
	
				$postfields 				= array();
				$postfields['API'] 			= 'cms';
				$postfields['APIRequest'] 	= 'ErrorAdd';
				$postfields['id_errortype'] = 7;
				$postfields['id_errorcode'] = 11366;
				$postfields['file'] 		= __FILE__;
				$postfields['line'] 		= __LINE__;
				$postfields['text'] 		= $msg;
				
				soa2( $postfields, __FILE__, __LINE__ );
	
			}
		}
		
		q("UPDATE ebay_auctions SET `Call`='EndItem', upload=1 WHERE id_auction IN (".implode(", ", $id_auction).");", $dbshop, __FILE__, __LINE__);
	}
	
	echo 'Folgende Artikel wurden gelöscht:'."\n";
	foreach ( $MPN_auctions as $mpn => $auctions )
	{
		echo '<b>MPN '.$mpn.':</b> '.implode(', ', $auctions)."\n";
	}

	echo '<b>SCRIPTLAUFZEIT: '.(time()-$start).' Sekunden</b>'."\n";
	echo $response;

?>