<?php
	$starttime=time()+microtime();

	$start=time();

	//get all GARTs
	$gart=array();
	$results=q("SELECT MPN, GART FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$gart[$row["MPN"]]=$row["GART"]*1;
	}


	//determine low availabilities
	$res=q("SELECT * FROM lager;", $dbshop,  __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		$bestand=($row["ISTBESTAND"]*1)+($row["MOCOMBESTAND"]*1)+($row["ONLINEBESTAND"]*1);
		$minbestand=3;
		
		//steering gear exception
		if( $gart[$row["ArtNr"]]==286 ) $minbestand=1;
		
		if ($bestand<$minbestand) $nullbestand[$row["ArtNr"]]="";
	}

//AUSNAHMEN

//unset ($nullbestand["76531"]);
//unset ($nullbestand[76531]);



//ITEM IDs zu MPN suchen
$res=q("SELECT * FROM shop_items", $dbshop,  __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res)) {
	if (isset($nullbestand[$row["MPN"]])) { 
	$nullbestand[$row["MPN"]]=$row["id_item"];
	}
}
$dump=reset($nullbestand);
//echo sizeof($nullbestand);

//ENTFERNEN ALLER EINTRÄGE OHNE ITEM ID ZUORDNUNG
while (list($key, $val) = each ($nullbestand)) if ($val=="") unset($nullbestand[$key]);
$dump=reset($nullbestand);
//echo sizeof($nullbestand);

/*
//ENTFERNEN ALLER EINTRÄGE DIE BEI EBAY NICHT AKTIV SIND
$res=q("SELECT item_id FROM ebay_accounts_items WHERE active = '1';",$dbshop,  __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res)) $ebay_accout_items[$row["item_id"]]="";
while (list($key, $val) = each ($nullbestand)) if (!isset($ebay_accout_items[$val])) unset($nullbestand[$key]);
$dump=reset($nullbestand);
*/

//AUCTIONS_IDs ZU DEN ITEM_IDs SUCHEN
$ebay_auctions=array();
$res=q("SELECT id_auction, shopitem_id, ItemID, SKU, account_id FROM ebay_auctions;", $dbshop,  __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res)) {
	if (isset($nullbestand[$row["SKU"]])) {
		$ebay_auctions[$row["id_auction"]]=$row["shopitem_id"];

	}
}
//echo sizeof($ebay_auctions);

$response="";
$count=0;

echo "ANZAHL: ".sizeof($ebay_auctions)."<br />";

if (sizeof($ebay_auctions)>0 ) 
{
	$varField["API"]="ebay";
	$varField["Action"]="EndItem";
	//$varField["usertoken"]="merci2664";
	

	$id_auction=array();
	while (list($key, $val) = each ($ebay_auctions))
	{
//		if ($count<50)
		{
		/*	if ($_POST["JobEndTime"]-time()<30) 
			{
				$response.='Jobende wegen Skriptlaufzeit \n'; 
				echo '<b>SCRIPTLAUFZEIT: '.(time()-$start).' Sekunden</b><br />';
				echo $response;
				exit;
			}
			else
		*/
			{
				$id_auction[]=$key;
				/*
				$varField["id_auction"]=$key;
				$response.='EndAuction: '.$key.'  ShopItem: '.$val."\n";
				post(PATH."soa/", $varField).' \n';
				$endtime=time()+microtime();
				if( ($endtime-$starttime) > 60 ) break;
				*/
				$count++;	
			}
		}
	}
//	print_r($id_auction);
	q("UPDATE ebay_auctions SET `Call`='EndItem', upload=1 WHERE id_auction IN (".implode(", ", $id_auction).");", $dbshop, __FILE__, __LINE__);
}

//while (list($key, $val) = each ($ebay_auctions)) echo $key.'->'.$val.': '.$ebay_auctions_itemid[$key]["itemid"].'-'.$ebay_auctions_itemid[$key]["accountid"].'<br />';
echo '<b>SCRIPTLAUFZEIT: '.(time()-$start).' Sekunden</b><br />';
echo $response;

?>