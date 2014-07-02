<?php
	/*****************************************************************
	 * set gross weight for items who have net weight and dimensions *
	 *****************************************************************/
	include("config.php");
	
	$i=0;
	$yellow_price=array();
	$results=q("SELECT * FROM prpos WHERE LST_NR=5;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$yellow_price[$row["ARTNR"]]=round($row["POS_0_WERT"]*1.19, 2);
	}
	
	$count_fail=0;
	$count_ok=0;
	$art_nr=array();
	$results=q("SELECT shopitem_id, SKU, StartPrice, ShippingServiceCost FROM ebay_auctions WHERE accountsite_id=9;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ($row["ShippingServiceCost"]==0) $check_price=$yellow_price[$row["SKU"]]+10.9;
		else $check_price=$yellow_price[$row["SKU"]];
		
		if (!isset($yellow_price[$row["SKU"]]) or $row["StartPrice"]<$check_price)
		{
			$count_fail++;
			echo $count_fail.' - '.$row["SKU"].' / Startpreis: '.$row["StartPrice"].' / Versand: '.$row["ShippingServiceCost"].' / Gelb: '.$yellow_price[$row["SKU"]].'<br />'."\n";
			if(!isset($art_nr[$row["SKU"]])) 
			{
				$art_nr[$row["SKU"]]=$row["SKU"];
				//add item to priority ebay update
				$data=array();
				$data["API"]="shop";
				$data["Action"]="ListAddItem";
				$data["list_id"]=194;
				$data["item_id"]=$_row["shopitem_id"];
				post(PATH."soa/", $data);
			}
		}
		else $count_ok++;
	}
	echo $count_ok.' Auktionen OK!<br />'."\n";
	echo sizeof($art_nr).' Artikel sind betroffen.';
	
?>