<?php

$start=time();

//include("../../mapco_shop_de/config.php");
include("config.php");

//$res=q("SELECT a.*, b.id_item FROM lager as a, shop_items as b where ISTBESTAND = 0 and a.ArtNr=b.MPN;", $dbshop,  __FILE__, __LINE__);
$res=q("SELECT * FROM lager where ISTBESTAND = 0 AND MOCOMBESTAND = 0;", $dbshop,  __FILE__, __LINE__);
//echo '*'.(time()-$start).'*';

//NULLBESTAND SPEICHERN
while ($row=mysqli_fetch_array($res)) {
	$nullbestand[$row["ArtNr"]]="";
}
echo sizeof($nullbestand).'<br />';

//ITEM IDs zu MPN suchen
$res=q("SELECT * FROM shop_items", $dbshop,  __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res)) {
	if (isset($nullbestand[$row["MPN"]])) { $nullbestand[$row["MPN"]]=$row["id_item"];}
}

//ENTFERNEN ALLER EINTRÄGE OHNE ITEM ID ZUORDNUNG
while (list($key, $val) = each ($nullbestand)) if ($val=="") unset($nullbestand[$key]);

$dump=reset($nullbestand);
echo sizeof($nullbestand).'#<br />';
//ENTFERNEN ALLER EINTRÄGE DIE BEI EBAY NICHT AKTIV SIND
/*
$res=q("SELECT item_id FROM ebay_accounts_items WHERE active = '1';",$dbshop,  __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res)) $ebay_accout_items[$row["item_id"]]="";
while (list($key, $val) = each ($nullbestand)) if (!isset($ebay_accout_items[$key])) unset($nullbestand[$key]);
*/
//AUCTIONS_IDs ZU DEN ITEM_IDs SUCHEN
$dump=reset($nullbestand);
echo sizeof($nullbestand).'##<br />';
$res=q("SELECT id_auction, shopitem_id, ItemID, account_id FROM ebay_auctions;", $dbshop,  __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res)) {
	if (isset($nullbestand[$row["shopitem_id"]])) {
		$ebay_auctions[$row["id_auction"]]=$row["shopitem_id"];
		$ebay_auctions_itemid[$row["id_auction"]]["itemid"]=$row["ItemID"];
		$ebay_auctions_itemid[$row["id_auction"]]["accountid"]=$row["account_id"];

	}
}

echo sizeof($ebay_auctions).'<br />';
while (list($key, $val) = each ($ebay_auctions)) echo $key.'->'.$val.': '.$ebay_auctions_itemid[$key]["itemid"].'-'.$ebay_auctions_itemid[$key]["accountid"].'<br />';
echo '<b>SCRIPTLAUFZEIT: '.(time()-$start).' Sekunden</b><br />';
?>