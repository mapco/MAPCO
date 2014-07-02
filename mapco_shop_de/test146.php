<?php

	/************************************************
	 * IMPORTS AMAZON DE ASINS INTO amazon_products *
	 ************************************************/

	include("config.php");
	
	//cache shop_items
	$items=array();
	$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$items[$row["MPN"]]=$row["id_item"];
	}

	//cache amazon_products
	$products=array();
	$results=q("SELECT * FROM amazon_products WHERE account_id=1;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$products[$row["SKU"]]=$row;
	}


	$handle=fopen("amazon_de_asins.csv", "r");
	$line=fgetcsv($handle, 4096, ";"); //skip header line
	$inserted=0;
	$updated=0;
	while( $line=fgetcsv($handle, 4096, ";") )
	{
		if( isset($products[$line[0]]) )
		{
			if( $products[$line[0]]["ASIN"]!=$line[1] )
			{
				echo 'Aktualisiere ASIN für '.$line[0].'.<br />';
				$query="UPDATE amazon_products SET ASIN='".$line[1]."' WHERE id_product=".$products[$line[0]]["id_product"].";";
				q($query, $dbshop, __FILE__, __LINE__);
				$updated++;
				if ( $updated==10000 ) break;
			}
		}
		else
		{
			echo 'Füge ASIN für '.$line[0].' hinzu.<br />';
			$SKU=substr($line[0], 0, strpos($line[0], "-"));
			$data=array();
			$data["item_id"]=$items[$SKU];
			$data["account_id"]=1;
			$data["SKU"]=$line[0];
			$data["ASIN"]=$line[1];
			$data["firstmod"]=time();
			$data["firstmod_user"]=21371;
			$data["lastmod"]=time();
			$data["lastmod_user"]=21371;
			q_insert("amazon_products", $data, $dbshop, __FILE__, __LINE__);
			$inserted++;
			if ( $inserted==10000 ) break;
		}
	}
	
	echo '<br /><br />'.$updated.' Produkte aktualisiert.';
	echo '<br /><br />'.$inserted.' Produkte angelegt.';
?>