<?php
	$starttime=time()+microtime();
	$xml="";

	//move yesterday price suggestions from tbuls
/*
	$results=q("SELECT *  FROM `shop_price_suggestions` WHERE `firstmod_user` = 28623 AND firstmod>1402871709", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$postdata=array();
		$postdata["API"]="shop";
		$postdata["APIRequest"]="PriceSuggestionAdd";
		$postdata["item_id"]=$row["item_id"];
		$postdata["price"]=$row["price"];
		$postdata["pricelist"]=14110;
		post(PATH."soa2/", $postdata);
	}
	exit;
*/

	//resend imported=0 price suggestion to IDIMS
	$results=q("SELECT * FROM shop_price_suggestions WHERE imported=0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		print_r($row);
	}
	
	//check for open suggestions before sending new price suggestions
	exit;

	//get online pricelists and default pricelists
	$pricelists=array();
	$pls=array();
	$results=q("SELECT * FROM idims_price_update_groups GROUP BY pricelist;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$pricelists[]=$row;
		$pls[$row["default_pricelist"]]=$row["default_pricelist"];
	}


	//get all items
	$items=array();
	$items_id=array();
	$results=q("SELECT * FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$items[$row["MPN"]]=$row["MPN"];
		$items_id[$row["MPN"]]=$row["id_item"];
	}

	//get all default pricelist prices
	$dprices=array();
	$results=q("SELECT * FROM prpos WHERE LST_NR IN (".implode(", ", $pls).");", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$dprices[$row["LST_NR"]][$row["ARTNR"]]=$row["POS_0_WERT"];
	}

	//find all items that do not have a default price
	$missing=array();
	foreach($pls as $pl)
	{
		foreach( $items as $artnr )
		{
			if ( !isset($dprices[$pl][$artnr]) )
			{
				$missing[$artnr]=$artnr;
			}
		}
	}
	if( sizeof($missing)>0 )
	{
		show_error(9899, 5, __FILE__, __LINE__, sizeof($missing)." errors:\n".implode(", ", $missing), false);
	}


	//find all items that do not have a price
	for($i=0; $i<sizeof($pricelists); $i++)
	{
		$prices=array();
		$results=q("SELECT * FROM prpos WHERE LST_NR=".$pricelists[$i]["pricelist"].";", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$prices[$row["ARTNR"]]=$row["POS_0_WERT"];
		}

		foreach( $items as $artnr )
		{
			if ( isset($dprices[$pricelists[$i]["default_pricelist"]][$artnr]) and !isset($prices[$artnr]) )
			{
				$postdata=array();
				$postdata["API"]="shop";
				$postdata["APIRequest"]="PriceSuggestionAdd";
				$postdata["item_id"]=$items_id[$artnr];
				$postdata["price"]=round($dprices[$pricelists[$i]["default_pricelist"]][$artnr]*1.19, 2);
				$postdata["price"]=round($postdata["price"]*$pricelists[$i]["default_markup"], 2);
				$postdata["pricelist"]=$pricelists[$i]["pricelist"];
				if( $postdata["price"]>0 )
				{
	//				print_r($postdata);
					post(PATH."soa2/", $postdata);
					$xml .= '<ItemPriceUpdated MPN="'.$artnr.'" pricelist="'.$pricelists[$i]["pricelist"].'">'.$postdata["price"].'</ItemPriceUpdated>'."\n";
				}
				$stoptime=time()+microtime();
				if( ($stoptime-$starttime)>60 )
				{
					echo '<PriceCheckResponse>'."\n";
					echo '	<Ack>Success</Ack>'."\n";
					echo '	<NextCall>'.(time()+180).'</NextCall>';
					echo $xml;
					echo '</PriceCheckResponse>'."\n";
					exit;
				}
			}
		}
	}

?>