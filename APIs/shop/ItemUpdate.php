<?php
	$starttime = time()+microtime();
	
	include("../functions/cms_t2.php");
	include("../functions/mapco_baujahr.php");
	include("../functions/cms_url_encode.php");

	if ( !isset($_POST["id_item"]) )
	{
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine gültige Artikel-ID übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}

	if ( !is_numeric($_POST["id_item"]) )
	{
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-ID ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Die Artikel-ID muss numerisch sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ItemUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kein Artikel mit dieser ID gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein Artikel mit der angegebenen ID gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
		echo '</ItemUpdateResponse>'."\n";
		exit;
	}

	//remove wrong vehicles applications
	q("DELETE FROM t_400 WHERE SortNr!=1 AND (KritNr=2 OR KritNr=16);", $dbshop, __FILE__, __LINE__);

	//remove duplicate items
	$shop_items=mysqli_fetch_array($results);
	$results=q("SELECT * FROM shop_items WHERE MPN='".$shop_items["MPN"]."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)>1 )
	{
		while( $row=mysqli_fetch_array($results) )
		{
			if ( $row["id_item"]!=$_POST["id_item"] )
			{
				//update shop_orders_items
				$query="UPDATE shop_orders_items SET item_id=".$_POST["id_item"]." WHERE item_id=".$row["id_item"].";";
				q($query, $dbshop, __FILE__, __LINE__);
				//update amazon_products
				$query="UPDATE amazon_products SET item_id=".$_POST["id_item"]." WHERE item_id=".$row["id_item"].";";
				q($query, $dbshop, __FILE__, __LINE__);
				//update ebay_auctions
				$query="UPDATE ebay_auctions SET shopitem_id=".$_POST["id_item"]." WHERE shopitem_id=".$row["id_item"].";";
				q($query, $dbshop, __FILE__, __LINE__);
				//remove from shop_price_research
				$query="UPDATE shop_price_research SET item_id=".$_POST["id_item"]." WHERE item_id=".$row["id_item"].";";
				q($query, $dbshop, __FILE__, __LINE__);
				//remove from shop_price_suggestions
				$query="UPDATE shop_price_suggestions SET item_id=".$_POST["id_item"]." WHERE item_id=".$row["id_item"].";";
				q($query, $dbshop, __FILE__, __LINE__);
				//remove from shop_offers
//				$query="UPDATE shop_offers SET item_id=".$_POST["id_item"]." WHERE item_id=".$row["id_item"].";";
				q($query, $dbshop, __FILE__, __LINE__);
				//remove from shop_items_vehicles
				$query="DELETE FROM shop_items_vehicles WHERE item_id=".$row["id_item"].";";
				q($query, $dbshop, __FILE__, __LINE__);
				//remove from shop_lists_items
				$query="DELETE FROM shop_lists_items WHERE item_id=".$row["id_item"].";";
				q($query, $dbshop, __FILE__, __LINE__);
				//remove from shop_items_lang
				$results2=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
				while( $row2=mysqli_fetch_array($results2) )
				{
					$query="DELETE FROM shop_items_".$row2["code"]." WHERE id_item=".$row["id_item"].";";
					q($query, $dbshop, __FILE__, __LINE__);
				}
				//remove from shop_items
				$query="DELETE FROM shop_items WHERE id_item=".$row["id_item"].";";
				q($query, $dbshop, __FILE__, __LINE__);
			}
		}
	}

	//detect fresh start
	$fresh=true;
	$results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM shop_items_".$row["code"]." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results2)>0 )
		{
			$row2=mysqli_fetch_array($results2);
			if( $row2["lastmod"]==0 or $row2["lastmod"]>$shop_items["lastmod"] ) $fresh=false;
		}
	}
	if( $fresh )
	{
		$results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$results2=q("UPDATE shop_items_".$row["code"]." SET lastmod=0 WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		}
	}

	//update language by language
	$results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM shop_items_".$row["code"]." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		if( $row2["lastmod"]<$shop_items["lastmod"] )
		{
			$response=post(PATH."soa/", array( "API" => "shop", "Action" => "ItemUpdate2", "id_item" => $_POST["id_item"], "lang" => $row["code"]));
			if ( strpos($response, "<Ack>Success</Ack>") === false )
			{
				echo $response;
				exit;
			}
//			echo $row["code"]."\n";
			$stoptime = time()+microtime();
			$time = $stoptime-$starttime;
			if( $stoptime-$starttime>30 )
			{
				echo '<ItemUpdateResponse>'."\n";
				echo '	<Ack>Unfinished</Ack>'."\n";
				echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
				echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
				echo '</ItemUpdateResponse>'."\n";
				exit;
			}
		}
	}
	
	//update item when all languages have been updated successfully
	q("UPDATE shop_items SET lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
//	echo 'Seitenaufbau: '.number_format($time,2).'s<br />';
	
	//add item to priority ebay update
	$data=array();
	$data["API"]="shop";
	$data["Action"]="ListAddItem";
	$data["list_id"]=194;
	$data["item_id"]=$_POST["id_item"];
	post(PATH."soa/", $data);
	//add item to priority ebay update
	$data=array();
	$data["API"]="shop";
	$data["Action"]="ListAddItem";
	$data["list_id"]=2938;
	$data["item_id"]=$_POST["id_item"];
	post(PATH."soa/", $data);
/*
	$results=q("SELECT * FROM ebay_auctions_priority WHERE item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		q("INSERT INTO ebay_auctions_priority (item_id) VALUES(".$_POST["id_item"].");", $dbshop, __FILE__, __LINE__);
	}
*/

	echo '<ItemUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '	<ItemId>'.$_POST["id_item"].'</ItemId>'."\n";
	echo '</ItemUpdateResponse>'."\n";
?>