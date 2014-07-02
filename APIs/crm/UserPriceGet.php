<?php

	include_once("../../mapco_shop_de/functions/shop_get_prices.php");

	check_man_params(array("shop_id" => "numericNN", "user_id" => "numericNN", "MPN" => "textNN"));

	// GET SHOP DATA
	$res_shop = q("SELECT * FROM shop_shops WHERE id_shop = ".$_POST["shop_id"], $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows( $res_shop ) == 0)
	{
		//show_error();
		exit;
	}
	
	$shop = mysqli_fetch_assoc( $res_shop );
	
	$userpricelist = 0;
	$price_net = 0;
	if ($shop["shop_type"] == 1)
	{
	
		//GET ITEM_ID
		$res_item = q("SELECT * FROM shop_items WHERE MPN = '".$_POST["MPN"]."'", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows( $res_item ) == 0)
		{
			//show_error();
			exit;
		}

		
		$results=q("SELECT * FROM cms_users WHERE id_user=".$_POST["user_id"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$results=q("SELECT * FROM kunde WHERE ADR_ID='".$row["idims_adr_id"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results) > 0 )
		{
			$row=mysqli_fetch_array($results);
			if ($row["GEWERBE"]>0) 
			{
				$userpricelist = $row["PL1"];
				$item = mysqli_fetch_assoc( $res_item );
		
				$prices = get_prices($item["id_item"],1, $_POST["user_id"]);
		
				$price_net = $prices["net"];

			}
			else
			{
				$userpricelist = $shop["pricelist"];
			}
		}
		else
		{
			$userpricelist = $shop["pricelist"];
		}
		
		if ( $price_net == 0)
		{
			$res_price = q("SELECT * FROM prpos WHERE ARTNR = '".$_POST["MPN"]."' AND LST_NR = ".$userpricelist, $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows( $res_price ) > 0)
			{
				$row_price = mysqli_fetch_assoc( $res_price );
				$price_net = $row_price["POS_0_WERT"];	
			}
		}
	}
	else
	{
		$userpricelist = $shop["pricelist"];
		$res_price = q("SELECT * FROM prpos WHERE ARTNR = '".$_POST["MPN"]."' AND LST_NR = ".$userpricelist, $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows( $res_price ) > 0)
		{
			$row_price = mysqli_fetch_assoc( $res_price );
			$price_net = $row_price["POS_0_WERT"];	
		}
	}
	
	//GET PRICELIST FROM SHOP (3=blau, 4=grün, 5=gelb, 6=orange, 7=rot)
	$pricelists = array();
	$res_prices = q("SELECT * FROM prpos WHERE ARTNR = '".$_POST["MPN"]."' AND LST_NR IN (3,4,5,6,7)", $dbshop, __FILE__, __LINE__);
	while ( $row_prices = mysqli_fetch_assoc( $res_prices ) )
	{
		$pricelists[$row_prices["LST_NR"]] = $row_prices["POS_0_WERT"];
	}
	//SERVICE RESPONSE
	if ( sizeof($pricelists) > 0 )
	{
		foreach ($pricelists as $pl => $data)
		{
			echo '<pricelist>'."\n";
			echo '	<price_listnr>'.$pl.'</price_listnr>'."\n";
			echo '	<price_net>'.$data.'</price_net>'."\n";
			echo '</pricelist>'."\n";
		}
	}
	echo '<userprice>'."\n";
	echo '	<price_listnr>'.$userpricelist.'</price_listnr>'."\n";
	echo '	<price_net>'.$price_net.'</price_net>'."\n";
	echo '</userprice>'."\n";


	
?>