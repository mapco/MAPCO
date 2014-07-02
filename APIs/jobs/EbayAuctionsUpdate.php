<?php

	$starttime = time()+microtime();
	$items=array();

	//get active items
	$active=array();
	$results=q("SELECT id_item, MPN FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$active[$row["MPN"]]=$row["id_item"];
	}
	//remove unavailable items
	$available=array();
	$results=q("SELECT ArtNr FROM lager WHERE ISTBESTAND>1 OR MOCOMBESTAND>1 OR ONLINEBESTAND>0 OR AMAZONBESTAND>0 ORDER BY ISTBESTAND, MOCOMBESTAND, ONLINEBESTAND, AMAZONBESTAND;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		if( isset($active[$row["ArtNr"]]) )
		{
			$available[$active[$row["ArtNr"]]]=$active[$row["ArtNr"]];
		}
	}
	//remove items with auctions
	$results=q("SELECT shopitem_id FROM ebay_auctions WHERE shopitem_id IN(".implode(", ", $available).") GROUP BY shopitem_id;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		unset($available[$row["shopitem_id"]]);
	}
	//remove blacklist items
	$results=q("SELECT * FROM shop_lists_items WHERE list_id=198;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		unset($available[$row["item_id"]]);
	}
	//remove single brake discs
	$results=q("SELECT * FROM shop_items WHERE GART=82 AND MPN NOT LIKE '%/2';", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		unset($available[$row["id_item"]]);
	}
	
	//add those items to priority list
	$postdata=array();
	$postdata["API"]="shop";
	$postdata["Action"]="ListItemsAdd";
	$postdata["list_id"]=194;
	$postdata["id_item"]=implode(", ", $available);
	post(PATH."soa/", $postdata);

	
	//get priority updates
	$results=q("SELECT * FROM shop_lists_items AS a, shop_items AS b WHERE a.list_id=194 AND a.item_id=b.id_item ORDER BY id;", $dbshop, __FILE__, __LINE__);
	//if no priority updates then update oldest auctions
	if(mysqli_num_rows($results)==0)	$results=q("SELECT * FROM shop_items ORDER BY lastupdate LIMIT 10;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$items[]=$row["MPN"];
		//update item
		$fieldset=array();
		$fieldset["API"]="ebay";
		$fieldset["Action"]="ItemCreateAuctions2";
//		$fieldset["id_account"]=1;
		$fieldset["id_item"]=$row["id_item"];
		$responseXml=post(PATH."soa/", $fieldset);

		//stop job if time limit is reached
		$stoptime = time()+microtime();
		if( !isset($_POST["timelimit"]) ) $_POST["timelimit"]=60;
		if ($stoptime-$starttime>$_POST["timelimit"]) break;
	}
	
	$msg  = '<EbayAuctionsUpdate>'."\n";
	$msg .= '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($items); $i++)
	{
		$msg .= '	<ItemUpdated>'.$items[$i].'</ItemUpdated>'."\n";
	}
	$msg .= '	<ItemsUpdated>'.$i.'</ItemsUpdated>'."\n";
	$msg .= '</EbayAuctionsUpdate>'."\n";
	echo $msg;
	
//	mail("habermann.jens@gmail.com", "EbayAuctionsUpdate", $msg);
	
?>