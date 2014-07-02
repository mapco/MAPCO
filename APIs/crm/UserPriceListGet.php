<?php

	check_man_params(array("shop_id" => "numericNN", "user_id" => "numericNN"));

	// GET SHOP DATA
	$res_shop = q("SELECT * FROM shop_shops WHERE id_shop = ".$_POST["shop_id"], $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows( $res_shop ) == 0)
	{
		//show_error();
		exit;
	}
	
	$shop = mysqli_fetch_assoc( $res_shop );
	
	$pricelist = 0;
	$pricegroup = 0;
		
	if ($shop["shop_type"] == 1)
	{
		$results=q("SELECT * FROM cms_users WHERE id_user=".$_POST["user_id"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$results=q("SELECT * FROM kunde WHERE ADR_ID='".$row["idims_adr_id"]."';", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		if ($row["GEWERBE"]>0) 
		{
			$pricelist = $row["PL1"];
			$pricegroup = $row["PREISGR"];
		}
		else
		{
			$pricelist = $shop["pricelist"];
		}
	}
	
	//SERVICE RESPONSE
	echo '<pricelist>'.$pricelist.'</pricelist>';
	echo '<pricegroup>'.$pricegroup.'</pricegroup>';

?>