<?php
	/*********************************************
	 * import Amazon UK ASINs to amazon_products *
	 *********************************************/
	include("config.php");

	$artnr2id=array();
	$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$artnr2id[$row["MPN"]]=$row["id_item"];
	}

	$exists=array();
	$results=q("SELECT * FROM amazon_products WHERE account_id=5;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$exists[$row["item_id"]]=$row["id_product"];
	}

	$handle=fopen("amazon_uk_asins.csv", "r");
	$line=fgetcsv($handle, 4096, ";"); //skip header
	while( $line=fgetcsv($handle, 4096, ";") )
	{
		if ( isset($artnr2id[$line[1]]) )
		{
			$item_id=$artnr2id[$line[1]];
			if ( !isset($exists[$item_id]) )
			{
				echo "INSERT INTO amazon_products (item_id, ASIN, account_id) VALUES('".$item_id."', '".$line[0]."', 5);<br />";
				q("INSERT INTO amazon_products (item_id, ASIN, account_id) VALUES('".$item_id."', '".$line[0]."', 5);", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				echo "UPDATE amazon_products SET ASIN='".$line[0]."' WHERE id_product=".$exists[$item_id].";<br />";
				q("UPDATE amazon_products SET ASIN='".$line[0]."' WHERE id_product=".$exists[$item_id].";", $dbshop, __FILE__, __LINE__);
			}
		}
		else echo $line[1].' nicht gefunden!<br /><br />';
	}
	
?>