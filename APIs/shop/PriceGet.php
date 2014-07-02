<?php

	/*
	benötigt:
	- $_POST["shop_id"]
	- $_POST["item_id"] oder $_POST["MPN"]
	*/
	
	/*
	liefert Netto und Bruttopreise eines Artikels in Abhängigkeit der hinterlegten Preisliste
	wird zum Artikel kein Preis zur Preisliste gefunenden wird, Preis aus DefaultPreisliste gezogen, sofern DefaultPreisliste angegeben und !=0
	*/
	
	$ust = (UST/100) +1;
	
	if (!isset($_POST["default_PriceList"])) $_POST["default_PriceList"]=0;
	

	if (isset($_POST["item_id"]) && $_POST["item_id"]!=0 && $_POST["item_id"]!="")
	{
		//GET MPN
		$res_MPN=q("SELECT * FROM shop_items WHERE id_item = ".$_POST["item_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_MPN)==0)
		{
			show_error(9758,7,__FILE__, __LINE__, "Shop_id: ".$_POST["shop_id"]);
		}
		else
		{
			$row_MPN=mysqli_fetch_array($res_MPN);
			$_POST["MPN"]=$row_MPN["MPN"];
		}
	}
	
	$required=array("shop_id" =>"numericNN", "MPN" => "textNN", "default_PriceList" => "numeric");	
	check_man_params($required);			
	
	//WHICH Price-LIST?
	$res_shop=q("SELECT * FROM shop_shops WHERE id_shop = ".$_POST["shop_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_shop)==0)
	{
		show_error(9757,7,__FILE__, __LINE__, "Shop_id: ".$_POST["shop_id"]);
	}
	else
	{
		$row_shop=mysqli_fetch_array($res_shop);
		if ($row_shop["shop_type"]==2)
		{
			//get Pricelist
			$res_list=q("SELECT * FROM ebay_accounts WHERE id_account = ".$row_shop["account_id"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_list)==0)
			{
				show_error(9759,7,__FILE__, __LINE__, "Ebay-Account: ".$_POST["account_id"]);
			}
			else
			{
				$row_list=mysqli_fetch_array($res_list);
				$listNr=$row_list["pricelist"];
				if ($listNr==0) $listNr=$_POST["default_PriceList"];
			}
		}
		else
		{
			//Gelbe Preisliste
			$listNr=$_POST["default_PriceList"];
		}
	}
	
	
	
	//GET PRICE
	$price = array();
	$res=q("SELECT * FROM prpos WHERE ARTNR = '".$_POST["MPN"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==0)
	{
		show_error(9760,7,__FILE__, __LINE__, "Artikelnummer: ".$_POST["MPN"]);
	}
	else
	{
		while ($row=mysqli_fetch_array($res))
		{
			$price[$row["LST_NR"]]=$row["POS_0_WERT"];
		}

		if ($listNr!=0 && isset($price[$listNr]))
		{
			echo '<price_net>'.$price[$listNr].'</price_net>'."\n";
			echo '<price_gross>'.number_format(($price[$listNr]*$ust),2,".","").'</price_gross>'."\n";
		}
		else
		{
			show_error(9761,7,__FILE__, __LINE__, "Listennummer: ".$listNr);
		}
	}

?>