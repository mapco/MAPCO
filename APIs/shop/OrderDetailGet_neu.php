<?php

	check_man_params(array("OrderID" =>"numericNN"));					


	include("../functions/mapco_gewerblich.php");

	
	if (isset($_POST["mode"]) && $_POST["mode"]=="single")
	{
		$singleorder=true;
	}
	else
	{
		$singleorder=false;
	}

	//GET ORDERdata
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		show_error(9762, 7, __FILE__, __LINE__, "OrderID:".$_POST["OrderID"]);
		exit;
	}
	
	$order=array();
	$order[0]=mysqli_fetch_array($res_order);


	if (!$singleorder)
	{
		if ($order[0]["combined_with"]>0)
		{
			$tmp_orderid=$order[0]["combined_with"];
			
			//GET ALL ORDERS OF COMBINATION; ORDER[0] ->"MOTHER"
			//$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
			$res_orders=q("SELECT * FROM shop_orders WHERE id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
			$order[0]=mysqli_fetch_array($res_orders);
			
			$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND NOT id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
			while ($row_orders=mysqli_fetch_array($res_orders))
			{
				$order[sizeof($order)]=$row_orders;
			}
		}
	}
	//GET shipping_title
	$shipping_title=array();
	for ($k=0; $k<sizeof($order); $k++)
	{
		if($order[$k]["shipping_type_id"]!=0)
		{
			$res_shipping=q("SELECT * FROM shop_shipping_types WHERE id_shippingtype = ".$order[$k]["shipping_type_id"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_shipping)==1)
			{
				$shipping=mysqli_fetch_array($res_shipping);
				$shipping_title[$k]=$shipping["title"];
			}
			else
			{
				$shipping_title[$k]="";
			}
		}
	}
	//$shipping=mysqli_fetch_array($res_shipping);
	
	//$order=mysqli_fetch_array($res_order);
	
	//GET SHOPS
	$shop=array();
	$res_shops=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
	while ($row_shops=mysqli_fetch_array($res_shops))
	{
		$shop[$row_shops["id_shop"]]=$row_shops;
	}
	
	//GET EXCHANGERATE
	$exchange_rate=array();
	$res_exchange=q("SELECT * FROM shop_currencies;", $dbshop, __FILE__, __LINE__);
	while ($row_exchange=mysqli_fetch_array($res_exchange))
	{
		$exchange_rate[$row_exchange["currency_code"]]=$row_exchange["exchange_rate_to_EUR"];
	}
	
	if (gewerblich($order[0]["customer_id"])) 
	{
		$ordertype="business"; 
		$gewerblich=true; 
	}
	else 
	{
		$ordertype="customer";
		$gewerblich=false; 
	}
	
	//GET CMS_USER DATA
	if ($order[0]["customer_id"]!=0)
	{
		$res_cms_user=q("SELECT * FROM cms_users WHERE id_user = ".$order[0]["customer_id"].";", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_cms_user)==0)
		{
			$customer_username="";
			$customer_name="";
			$customer_firstname="";
			$customer_lastname="";
			//$customer_shop_id=0;
		}
		else
		{
			$row_cms_user=mysqli_fetch_array($res_cms_user);
			$customer_username=$row_cms_user["username"];
			$customer_name=$row_cms_user["name"];
			$customer_firstname=$row_cms_user["firstname"];
			$customer_lastname=$row_cms_user["lastname"];

			//$customer_shop_id=$row_cms_user["shop_id"];
		}
		// GET USER SITE
		$customer_site_id = 0;
		$res_cms_user_site=q("SELECT * FROM cms_users_sites WHERE user_id = ".$order[0]["customer_id"].";", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_cms_user_site)==1)
		{
			$row_cms_user_site = mysqli_fetch_array($res_cms_user_site);
			$customer_site_id = $row_cms_user_site["site_id"];
		}
		else if(mysqli_num_rows($res_cms_user_site)>1)
		{
			while ($row_cms_user_site = mysqli_fetch_assoc($res_cms_user_site))
			{
				if ($customer_site_id == 0) $customer_site_id=$row_cms_user_site["site_id"];
				if ($row_cms_user_site["site_id"] == $shop[$order[0]["shop_id"]]["site_id"])	
				{
					$customer_site_id = $row_cms_user_site["site_id"];
				}
			}
		}
		else
		{
			$customer_site_id=0;
		}
		//GET SITE NAME
		$customer_site_name='';
		if ($customer_site_id!=0)
		{
			foreach ($shop as $shopid => $shopdata)
			{
				if ($shopdata["site_id"] == $customer_site_id && $shopdata["parent_shop_id"]==0)
				{
					$customer_site_name = $shopdata["title"];
				}
			}
		}
				
	}
	else
	{
		$customer_username="";
		$customer_name="";
		$customer_firstname="";
		$customer_lastname="";

		//$customer_shop_id=0;
		$customer_site_id=0;
	}
	
	//GET ORDER_SITE
	$order_site_id=0;
	if ($shop[$order[0]["shop_id"]]["parent_shop_id"]==0)
	{
		$order_site_id=$shop[$order[0]["shop_id"]]["site_id"];
		//$order_site_id=1;
	}
	else
	{
		$parentshop_id = $shop[$order[0]["shop_id"]]["parent_shop_id"];
//		$order_site_id=$shop[$parentshop_id ]["site_id"];
		$order_site_id=$parentshop_id;
	}

	//UMSATZSTEUER FESTLEGEN
	if ($order[0]["VAT"]==0)
	{
		$ust = 1;
	}
	else
	{
		$ust = 	($order[0]["VAT"]/100)+1;
	}
		
	$xmldata = "";
	$xmldata.="<Order type='".$ordertype."'>\n";
	$xmldata.="	<id_order>".$order[0]["id_order"]."</id_order>\n";
	$xmldata.="	<shop_id>".$order[0]["shop_id"]."</shop_id>\n";
	$xmldata.="	<status_id>".$order[0]["status_id"]."</status_id>\n";
	$xmldata.="	<ordertype_id>".$order[0]["ordertype_id"]."</ordertype_id>\n";
	
	$xmldata.="	<foreign_OrderID>".$order[0]["foreign_OrderID"]."</foreign_OrderID>\n";
	$xmldata.="	<VAT>".$order[0]["VAT"]."</VAT>\n";
	$xmldata.="	<customer_id>".$order[0]["customer_id"]."</customer_id>\n";
	$xmldata.="	<customer_username><![CDATA[".$customer_username."]]></customer_username>\n";
	$xmldata.="	<customer_name><![CDATA[".$customer_name."]]></customer_name>\n";
	//$xmldata.="	<customer_shop_id>".$customer_shop_id."</customer_shop_id>\n";
	$xmldata.="	<customer_site_id>".$customer_site_id."</customer_site_id>\n";
	$xmldata.="	<customer_site_name><![CDATA[".$customer_site_name."]]></customer_site_name>\n";
	
	$xmldata.="	<order_site_id>".$order_site_id."</order_site_id>\n";
	$xmldata.="	<customer_firstname><![CDATA[".$customer_firstname."]]></customer_firstname>\n";
	$xmldata.="	<customer_lastname><![CDATA[".$customer_lastname."]]></customer_lastname>\n";

	$xmldata.="	<Currency_Code><![CDATA[".$order[0]["Currency_Code"]."]]></Currency_Code>\n";
	$xmldata.="	<usermail><![CDATA[".$order[0]["usermail"]."]]></usermail>\n";
	$xmldata.="	<bill_gender><![CDATA[".$order[0]["bill_gender"]."]]></bill_gender>\n";	
	$xmldata.="	<bill_company><![CDATA[".$order[0]["bill_company"]."]]></bill_company>\n";
	$xmldata.="	<bill_firstname><![CDATA[".$order[0]["bill_firstname"]."]]></bill_firstname>\n";
	$xmldata.="	<bill_lastname><![CDATA[".$order[0]["bill_lastname"]."]]></bill_lastname>\n";
	$xmldata.="	<bill_street><![CDATA[".$order[0]["bill_street"]."]]></bill_street>\n";
	$xmldata.="	<bill_number><![CDATA[".$order[0]["bill_number"]."]]></bill_number>\n";
	$xmldata.="	<bill_additional><![CDATA[".$order[0]["bill_additional"]."]]></bill_additional>\n";
	$xmldata.="	<bill_city><![CDATA[".$order[0]["bill_city"]."]]></bill_city>\n";
	$xmldata.="	<bill_zip><![CDATA[".$order[0]["bill_zip"]."]]></bill_zip>\n";
	$xmldata.="	<bill_country><![CDATA[".$order[0]["bill_country"]."]]></bill_country>\n";
	$xmldata.="	<bill_country_code><![CDATA[".$order[0]["bill_country_code"]."]]></bill_country_code>\n";
	$xmldata.="	<bill_address_manual_update>".$order[0]["bill_address_manual_update"]."</bill_address_manual_update>\n";
	$xmldata.="	<bill_adr_id>".$order[0]["bill_adr_id"]."</bill_adr_id>\n";
	$xmldata.="	<ship_adr_id>".$order[0]["ship_adr_id"]."</ship_adr_id>\n";
	
	//ADDRESS FROM shop_bill_adr
	if ($order[0]["bill_adr_id"]!=0)
	{
		$res_shop_bill_adr=q("SELECT * FROM shop_bill_adr WHERE user_id = ".$order[0]["customer_id"]." AND adr_id = ".$order[0]["bill_adr_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shop_bill_adr)>0)
		{
			$row_shop_bill_adr=mysqli_fetch_array($res_shop_bill_adr);
			$xmldata.="	<bill_adr_company><![CDATA[".$row_shop_bill_adr["company"]."]]></bill_adr_company>\n";
			$xmldata.="	<bill_adr_firstname><![CDATA[".$row_shop_bill_adr["firstname"]."]]></bill_adr_firstname>\n";
			$xmldata.="	<bill_adr_lastname><![CDATA[".$row_shop_bill_adr["lastname"]."]]></bill_adr_lastname>\n";
			$xmldata.="	<bill_adr_street><![CDATA[".$row_shop_bill_adr["street"]."]]></bill_adr_street>\n";
			$xmldata.="	<bill_adr_number><![CDATA[".$row_shop_bill_adr["number"]."]]></bill_adr_number>\n";
			$xmldata.="	<bill_adr_additional><![CDATA[".$row_shop_bill_adr["additional"]."]]></bill_adr_additional>\n";
			$xmldata.="	<bill_adr_city><![CDATA[".$row_shop_bill_adr["city"]."]]></bill_adr_city>\n";
			$xmldata.="	<bill_adr_zip><![CDATA[".$row_shop_bill_adr["zip"]."]]></bill_adr_zip>\n";
			$xmldata.="	<bill_adr_country><![CDATA[".$row_shop_bill_adr["country"]."]]></bill_adr_country>\n";
			$xmldata.="	<bill_adr_country_id><![CDATA[".$row_shop_bill_adr["country_id"]."]]></bill_adr_country_id>\n";
	
		}
	}
	//ADDRESS FROM shop_bill_adr

	if ($order[0]["ship_adr_id"]!=0)
	{
		$res_shop_bill_adr=q("SELECT * FROM shop_bill_adr WHERE user_id = ".$order[0]["customer_id"]." AND adr_id = ".$order[0]["ship_adr_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shop_bill_adr)>0)
		{
			$row_shop_bill_adr=mysqli_fetch_array($res_shop_bill_adr);
			$xmldata.="	<ship_adr_company><![CDATA[".$row_shop_bill_adr["company"]."]]></ship_adr_company>\n";
			$xmldata.="	<ship_adr_firstname><![CDATA[".$row_shop_bill_adr["firstname"]."]]></ship_adr_firstname>\n";
			$xmldata.="	<ship_adr_lastname><![CDATA[".$row_shop_bill_adr["lastname"]."]]></ship_adr_lastname>\n";
			$xmldata.="	<ship_adr_street><![CDATA[".$row_shop_bill_adr["street"]."]]></ship_adr_street>\n";
			$xmldata.="	<ship_adr_number><![CDATA[".$row_shop_bill_adr["number"]."]]></ship_adr_number>\n";
			$xmldata.="	<ship_adr_additional><![CDATA[".$row_shop_bill_adr["additional"]."]]></ship_adr_additional>\n";
			$xmldata.="	<ship_adr_city><![CDATA[".$row_shop_bill_adr["city"]."]]></ship_adr_city>\n";
			$xmldata.="	<ship_adr_zip><![CDATA[".$row_shop_bill_adr["zip"]."]]></ship_adr_zip>\n";
			$xmldata.="	<ship_adr_country><![CDATA[".$row_shop_bill_adr["country"]."]]></ship_adr_country>\n";
			$xmldata.="	<ship_adr_country_id><![CDATA[".$row_shop_bill_adr["country_id"]."]]></ship_adr_country_id>\n";
		}
	}

	$xmldata.="	<shipping_details><![CDATA[".$order[0]["shipping_details"]."]]></shipping_details>\n";
	$xmldata.="	<shipping_type_id><![CDATA[".$order[0]["shipping_type_id"]."]]></shipping_type_id>\n";
	$xmldata.="	<shipping_title><![CDATA[".$shipping_title[0]."]]></shipping_title>\n";
	$xmldata.="	<shipping_number><![CDATA[".$order[0]["shipping_number"]."]]></shipping_number>\n";
	
	
	$xmldata.="	<payments_type_id>".$order[0]["payments_type_id"]."</payments_type_id>\n";
	$xmldata.="	<invoice_id>".$order[0]["invoice_id"]."</invoice_id>\n";
	$xmldata.="	<invoice_nr><![CDATA[".$order[0]["invoice_nr"]."]]></invoice_nr>\n";
	$xmldata.="	<userphone><![CDATA[".$order[0]["userphone"]."]]></userphone>\n";
	$xmldata.="	<userfax><![CDATA[".$order[0]["userfax"]."]]></userfax>\n";
	$xmldata.="	<usermobile><![CDATA[".$order[0]["usermobile"]."]]></usermobile>\n";
	$xmldata.="	<firstmod>".$order[0]["firstmod"]."</firstmod>\n";
	$xmldata.="	<combined_with>".$order[0]["combined_with"]."</combined_with>\n";
	$xmldata.=" <orderDate>".date("d.m.Y H:i", $order[0]["firstmod"])."</orderDate>\n";

		//USERID - PLATFORM
		$buyerid="";
		if ($order[0]["shop_id"]==3 || $order[0]["shop_id"]==4 || $order[0]["shop_id"]==5)
		{
			$res_buyerid=q("SELECT * FROM ebay_orders WHERE OrderID = '".$order[0]["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_buyerid)>0)
			{
				$row_buyerid=mysqli_fetch_array($res_buyerid);
				$buyerid=$row_buyerid["BuyerUserID"];				
			}
		}
	$xmldata.=" <buyerUserID>".$buyerid."</buyerUserID>\n";
	
	$xmldata.="	<OrderItems>\n";
	
	//GET ORDER ITEMSdata
	$order_ids=array();
	for ($j=0; $j<sizeof($order); $j++)
	{
//		if ($in_orders=='') $in_orders.=$order[$j]["id_order"]; else $in_orders.=", ".$order[$j]["id_order"];
		$order_ids[]=$order[$j]["id_order"];
	}
	
	$res_order_items=q("SELECT * FROM shop_orders_items WHERE order_id IN (".implode(", ", $order_ids).")", $dbshop, __FILE__, __LINE__);
	
	$i=0;
	$orderItemsTotalGross=0;
	$orderItemsTotalGrossFC=0;
	$orderItemsTotalNet=0;
	$orderItemsTotalNetFC=0;
	$orderItemsTotalCollateralGross=0;
	$orderItemsTotalCollateralNet=0;
	$orderItemsTotalTax=0;
	$orderItemsTotalTaxFC=0;
	$orderItemCount=0;
	$CollateralGross=0;
	$collcnt=0;
	$complete=1;
	
	//SET ACTUAL EXCHANGERATE
	$exchangerates=array();
	$res_ex=q("SELECT * FROM shop_currencies WHERE currency_code = '".$order[0]["Currency_Code"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_ex)==1)
	{
		$row_ex=mysqli_fetch_array($res_ex);
		$exchangerates[0]=1/$row_ex["exchange_rate_to_EUR"];
	}
	
	$orderitems = array();
	while ($row_order_items=mysqli_fetch_array($res_order_items))
	{
		$orderitems[]=$row_order_items["id"];
		
		
		//ITEM DESCRIPTION
		$res_items_desc=q("SELECT * FROM shop_items_de WHERE id_item = ".$row_order_items["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row_items_desc=mysqli_fetch_array($res_items_desc);
		
		//MPN, COS
		$res_items_MPN=q("SELECT MPN, COS FROM shop_items WHERE id_item = ".$row_order_items["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row_items_MPN=mysqli_fetch_array($res_items_MPN);

		//GET STOCKAMOUNT
		if ( $order[0]['shop_id'] == 22 )
		{
			$res_stockamount = q("SELECT * FROM lagerrc WHERE RCNR = 41 AND ARTNR = '".$row_items_MPN['MPN']."'", $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows( $res_stockamount ) == 0 )
			{
				$stockamount = 0;
			}
			else
			{
				$row_stockamount = mysqli_fetch_assoc( $res_stockamount );
				$stockamount = $row_stockamount['ISTBESTAND'];	
			}
			
		}
		else
		{
			$stockamount;
		}

		
		//ITEMVEHICLE
		if ($row_order_items["customer_vehicle_id"]!=0)
		{
			$res_item_vehicle=q("SELECT * FROM shop_carfleet WHERE id = ".$row_order_items["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
			$item_vehicle=mysqli_fetch_array($res_item_vehicle);
			if ($item_vehicle["vehicle_id"]!=0)
			{
				$res_vehicle=q("SELECT * FROM vehicles_de WHERE id_vehicle = ".$item_vehicle["vehicle_id"].";", $dbshop, __FILE__, __LINE__);
				$vehicle=mysqli_fetch_array($res_vehicle);
			}
		}
		//GET EBAY ITEM ID
		$ebay_itemItemID="";
		if ($shop[$order[0]["shop_id"]]["shop_type"]==2)
		{
			$res_ebay_item=q("SELECT ItemItemID FROM ebay_orders_items WHERE TransactionID = '".$row_order_items["foreign_transactionID"]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_ebay_item)>0)
			{
				$ebay_item=mysqli_fetch_array($res_ebay_item);
				$ebay_itemItemID=$ebay_item["ItemItemID"];
			}
		}
		
		
		if ($row_order_items["customer_vehicle_id"]==0 and $row_order_items["item_id"]!="28760") //28760 wg Herbstaktion 2013
		{
			$complete = 0;
		}
				
		if ($row_order_items["Currency_Code"]=="")
		{
			$ex_rate=1;
		}
		else
		{
			//$ex_rate=1/$exchange_rate[$row_order_items["Currency_Code"]];
			$ex_rate=1/$row_order_items["exchange_rate_to_EUR"];
		}	
		//TRACK EXCHANGERATES FOR EACH ORDER
		$exchangerates[$row_order_items["order_id"]]=$ex_rate;
		
		
		if ($gewerblich)
		{
			$collateralGross=round($row_order_items["collateral"]*$ust*$ex_rate, 2);
			$collateralNet=round($row_order_items["collateral"]*$ex_rate, 2);
		}
		else
		{
			$collateralGross=round($row_order_items["collateral"]*$ex_rate, 2);
			$collateralNet=round(($row_order_items["collateral"]/$ust)*$ex_rate, 2);
		}
		
		if($row_order_items["collateral"]>0)
		{
			$collcnt = $collcnt + 1;
		}
		
		//ARTIKEL EINZELPREIS
//		if ($gewerblich)
//		{
//			$orderItemPriceGross=round($row_order_items["price"]*$ust*$ex_rate, 2);
//			$orderItemPriceNet=round($row_order_items["price"]*$ex_rate,2);
//		}
//		else
		if ($shop[$order[0]["shop_id"]]["shop_type"]==2 || $shop[$order[0]["shop_id"]]["shop_type"]==3)
		{
			//SUMMEN AUF BASIS DER BRUTTOPREISE
			$orderItemPriceGrossFC=$row_order_items["price"];
			$orderItemPriceGross=round($row_order_items["price"]*$ex_rate,2);
			$orderItemPriceNetFC=round($row_order_items["price"]/$ust,2);
			$orderItemPriceNet=round($row_order_items["price"]/$ust*$ex_rate,2);
		}
		else
		{
			//SUMMEN AUF BASIS DER NETTOPREISE
			$orderItemPriceGrossFC=round($row_order_items["netto"]*$ust, 2);
			$orderItemPriceGross=round($row_order_items["netto"]*$ust*$ex_rate,2);
			$orderItemPriceNetFC=round($row_order_items["netto"],2);
			$orderItemPriceNet=round($row_order_items["netto"]*$ex_rate,2);
			
		}
		//$orderItemPriceGross=$row_order_items["price"];
		//$orderItemPriceNet=$row_order_items["netto"];
		//$orderItemCollateralGross=$collateralGross;
		//$orderItemCollateralNet=$collateralNet;
		//$orderItemPriceTax=$row_order_items["netto"]*($ust-1);
		$orderItemPriceTax=$orderItemPriceNet*($ust-1);
		$orderItemPriceTaxFC=$orderItemPriceNetFC*($ust-1);
		
		//BESTELLPOSITION SUMME
		//$orderItemTotalGross=$row_order_items["price"]*$row_order_items["amount"];
		//$orderItemTotalNet=$row_order_items["netto"]*$row_order_items["amount"];
		$orderItemTotalGrossFC=$orderItemPriceGrossFC*$row_order_items["amount"];
		$orderItemTotalGross=$orderItemPriceGross*$row_order_items["amount"];
		$orderItemTotalNetFC=$orderItemPriceNetFC*$row_order_items["amount"];
		$orderItemTotalNet=$orderItemPriceNet*$row_order_items["amount"];
		//$orderItemTotalCollateralGross=$orderItemCollateralNet*$row_order_items["amount"]*$ust;
		//$orderItemTotalCollateralNet=$orderItemCollateralNet*$row_order_items["amount"];
		$orderItemTotalTax=$orderItemPriceTax*$row_order_items["amount"];
		$orderItemTotalTaxFC=$orderItemPriceTaxFC*$row_order_items["amount"];
		
		//BESTELLUNGSSUMMEN
		$orderItemsTotalGrossFC+=$orderItemTotalGrossFC;
		$orderItemsTotalGross+=$orderItemTotalGross;
		$orderItemsTotalNetFC+=$orderItemTotalNetFC;
		$orderItemsTotalNet+=$orderItemTotalNet;
		$orderItemsTotalCollateralGrossFC+=$orderItemTotalCollateralGrossFC;
		$orderItemsTotalCollateralGross+=$orderItemTotalCollateralGross;
		$orderItemsTotalCollateralNetFC+=$orderItemTotalCollateralNetFC;
		$orderItemsTotalCollateralNet+=$orderItemTotalCollateralNet;
		$orderItemsTotalTaxFC+=$orderItemTotalTaxFC;
		$orderItemsTotalTax+=$orderItemTotalTax;
		
		$orderItemCount+=$row_order_items["amount"];
		
		//Marge
		if ($orderItemPriceNet>0)
		{
			$cos_m=($orderItemPriceNet-$row_items_MPN["COS"])/$orderItemPriceNet*100;
		}
		else
		{
			$cos_m=0;
		}
		
		$xmldata.="		<Item>\n";
		$xmldata.="			<OrderItemID>".$row_order_items["id"]."</OrderItemID>\n";
		$xmldata.="			<OrderItemOrderID>".$row_order_items["order_id"]."</OrderItemOrderID>\n";
		$xmldata.="			<OrderItemItemID>".$row_order_items["item_id"]."</OrderItemItemID>\n";
		$xmldata.="			<OrderItemforeign_transactionID><![CDATA[".$row_order_items["foreign_transactionID"]."]]></OrderItemforeign_transactionID>\n";
		$xmldata.="			<OrderItemEbayItemID><![CDATA[".$ebay_itemItemID."]]></OrderItemEbayItemID>\n";
		$xmldata.="			<OrderItemMPN>".$row_items_MPN["MPN"]."</OrderItemMPN>\n";
		$xmldata.="			<OrderItemDesc><![CDATA[".$row_items_desc["title"]."]]></OrderItemDesc>\n";
		$xmldata.="			<OrderItemAmount>".$row_order_items["amount"]."</OrderItemAmount>\n";
		$xmldata.="			<OrderItemCurrency_Code>".$row_order_items["Currency_Code"]."</OrderItemCurrency_Code>\n";
		$xmldata.="			<OrderItemExchangeRateToEUR>".$row_order_items["exchange_rate_to_EUR"]."</OrderItemExchangeRateToEUR>\n";
		
		//LAGERBESTAND
		$xmldata.="			<OrderItemStockAmount>".$stockamount."</OrderItemStockAmount>\n";

		//ARTIKEL EINZELPREIS
		$xmldata.="			<orderItemPriceGrossFC>".number_format($orderItemPriceGrossFC, 2,",",".")."</orderItemPriceGrossFC>\n";
		$xmldata.="			<orderItemPriceGross>".number_format($orderItemPriceGross, 2,",",".")."</orderItemPriceGross>\n";
		$xmldata.="			<orderItemPriceNetFC>".number_format($orderItemPriceNetFC, 2,",",".")."</orderItemPriceNetFC>\n";
		$xmldata.="			<orderItemPriceNet>".number_format($orderItemPriceNet, 2,",",".")."</orderItemPriceNet>\n";
		$xmldata.="			<orderItemPriceCOS_M>".number_format($cos_m, 2,",",".")."</orderItemPriceCOS_M>\n";
	//	$xmldata.="			<orderItemCollateralGross>".number_format($orderItemCollateralGross, 2,",",".")."</orderItemCollateralGross>\n";
	//	$xmldata.="			<orderItemCollateralNet>".number_format($orderItemCollateralNet, 2,",",".")."</orderItemCollateralNet>\n";
		$xmldata.="			<orderItemPriceTaxFC>".number_format($orderItemPriceTaxFC, 2,",",".")."</orderItemPriceTaxFC>\n";
		$xmldata.="			<orderItemPriceTax>".number_format($orderItemPriceTax, 2,",",".")."</orderItemPriceTax>\n";
		//BESTELLPOSITION SUMME
		$xmldata.="			<orderItemTotalGrossFC>".number_format($orderItemTotalGrossFC, 2,",",".")."</orderItemTotalGrossFC>\n";
		$xmldata.="			<orderItemTotalGross>".number_format($orderItemTotalGross, 2,",",".")."</orderItemTotalGross>\n";
		$xmldata.="			<orderItemTotalNetFC>".number_format($orderItemTotalNetFC, 2,",",".")."</orderItemTotalNetFC>\n";
		$xmldata.="			<orderItemTotalNet>".number_format($orderItemTotalNet, 2,",",".")."</orderItemTotalNet>\n";
		//$xmldata.="			<orderItemTotalCollateralGross>".number_format($orderItemTotalCollateralGross, 2,",",".")."</orderItemTotalCollateralGross>\n";
		//$xmldata.="			<orderItemTotalCollateralNet>".number_format($orderItemTotalCollateralNet, 2,",",".")."</orderItemTotalCollateralNet>\n";
		$xmldata.="			<orderItemTotalTaxFC>".number_format($orderItemTotalTax, 2,",",".")."</orderItemTotalTaxFC>\n";
		$xmldata.="			<orderItemTotalTax>".number_format($orderItemTotalTax, 2,",",".")."</orderItemTotalTax>\n";
		
		$xmldata.="			<OrderItemChecked>".$row_order_items["checked"]."</OrderItemChecked>\n";
		$xmldata.="			<OrderItemckecked_by_user>".$row_order_items["ckecked_by_user"]."</OrderItemckecked_by_user>\n";
				
		if (isset($item_vehicle))
		{
			$xmldata.="			<OrderItemVehicleID>".$item_vehicle["vehicle_id"]."</OrderItemVehicleID>\n";
			$xmldata.="			<OrderItemCustomerVehicleID>".$item_vehicle["id"]."</OrderItemCustomerVehicleID>\n";
			$xmldata.="			<OrderItemVehicleKBA>".$item_vehicle["kbanr"]."</OrderItemVehicleKBA>\n";
			$xmldata.="			<OrderItemVehicleDateBuilt>".$item_vehicle["date_built"]."</OrderItemVehicleDateBuilt>\n";
			$xmldata.="			<OrderItemVehicleFIN>".$item_vehicle["FIN"]."</OrderItemVehicleFIN>\n";
			$xmldata.="			<OrderItemVehiclec0003>".$item_vehicle["c0003"]."</OrderItemVehiclec0003>\n";
			$xmldata.="			<OrderItemVehiclec0004>".$item_vehicle["c0004"]."</OrderItemVehiclec0004>\n";
			$xmldata.="			<OrderItemVehiclec0005>".$item_vehicle["c0005"]."</OrderItemVehiclec0005>\n";
			$xmldata.="			<OrderItemVehiclec0006>".$item_vehicle["c0006"]."</OrderItemVehiclec0006>\n";
			$xmldata.="			<OrderItemVehicles0033>".$item_vehicle["s0033"]."</OrderItemVehicles0033>\n";
			$xmldata.="			<OrderItemVehicles0038>".$item_vehicle["s0038"]."</OrderItemVehicles0038>\n";
			$xmldata.="			<OrderItemVehicles0040>".$item_vehicle["s0040"]."</OrderItemVehicles0040>\n";
			$xmldata.="			<OrderItemVehicles0067>".$item_vehicle["s0067"]."</OrderItemVehicles0067>\n";
			$xmldata.="			<OrderItemVehicles0072>".$item_vehicle["s0072"]."</OrderItemVehicles0072>\n";
			$xmldata.="			<OrderItemVehicles0112>".$item_vehicle["s0112"]."</OrderItemVehicles0112>\n";
			$xmldata.="			<OrderItemVehicles0139>".$item_vehicle["s0139"]."</OrderItemVehicles0139>\n";
			$xmldata.="			<OrderItemVehicles0233>".$item_vehicle["s0233"]."</OrderItemVehicles0233>\n";
			$xmldata.="			<OrderItemVehicles0514>".$item_vehicle["s0514"]."</OrderItemVehicles0514>\n";
			$xmldata.="			<OrderItemVehicles0564>".$item_vehicle["s0564"]."</OrderItemVehicles0564>\n";
			$xmldata.="			<OrderItemVehicles0567>".$item_vehicle["s0567"]."</OrderItemVehicles0567>\n";
			$xmldata.="			<OrderItemVehicles0608>".$item_vehicle["s0608"]."</OrderItemVehicles0608>\n";
			$xmldata.="			<OrderItemVehicles0649>".$item_vehicle["s0649"]."</OrderItemVehicles0649>\n";
			$xmldata.="			<OrderItemVehicles1197>".$item_vehicle["s1197"]."</OrderItemVehicles1197>\n";
			$xmldata.="			<OrderItemVehicleAdditional><![CDATA[".$item_vehicle["additional"]."]]></OrderItemVehicleAdditional>\n";
		}
		if (isset($vehicle))
		{
			$xmldata.="			<OrderItemVehicleBrand>".$vehicle["BEZ1"]."</OrderItemVehicleBrand>\n";
			$xmldata.="			<OrderItemVehicleModel>".$vehicle["BEZ2"]."</OrderItemVehicleModel>\n";
			$xmldata.="			<OrderItemVehicleType>".$vehicle["BEZ3"]."</OrderItemVehicleType>\n";
			$xmldata.="			<OrderItemVehicleKTypNr>".$vehicle["KTypNr"]."</OrderItemVehicleKTypNr>\n";
		}
		
		$xmldata.="		</Item>\n";

		unset($item_vehicle);
		unset($vehicle);
	}

		//SHIPPING COSTS
		$shippingCostsGrossFC=0;
		$shippingCostsGross=0;
		$shippingCostsNetFC=0;
		$shippingCostsNet=0;
		$shippingCostsTaxFC=0;
		$shippingCostsTax=0;
		
		if ($gewerblich)
		{
			for ($k=0; $k<sizeof($order); $k++)
			{
				if ($order[$k]["Currency_Code"]=="")
				{
					$ex_rate=1;
				}
				else
				{
					$ex_rate=1/$exchange_rate[$order[$k]["Currency_Code"]];
				}
				
				$ex_rate = $exchangerates[$order[$k]["id_order"]];
				//$shippingCostsGrossFC+=$order[$k]["shipping_costs"]*$ust;
				$shippingCostsGrossFC+=round($order[$k]["shipping_net"]*$ust,2);
				//$shippingCostsGross+=round($order[$k]["shipping_costs"]*$ust*$ex_rate,2);
				$shippingCostsGross+=round($order[$k]["shipping_net"]*$ust*$ex_rate,2);
				$shippingCostsNetFC+=$order[$k]["shipping_net"];
				$shippingCostsNet+=round($order[$k]["shipping_net"]*$ex_rate,2);
			}
			$shippingCostsTaxFC+=round($shippingCostsNetFC*($ust-1),2);
			$shippingCostsTax+=round($shippingCostsNet*($ust-1),2);
		}
		else
		{
			for ($k=0; $k<sizeof($order); $k++)
			{
				if ($order[$k]["Currency_Code"]=="")
				{
					$ex_rate=1;
				}
				else
				{
					$ex_rate=1/$exchange_rate[$order[$k]["Currency_Code"]];
				}
				$ex_rate = $exchangerates[$order[$k]["id_order"]];
				if ($shop[$order[0]["shop_id"]]["shop_type"]==2 || $shop[$order[0]["shop_id"]]["shop_type"]==3)
				{
					$shippingCostsGrossFC+=$order[$k]["shipping_costs"];
					$shippingCostsGross+=round($order[$k]["shipping_costs"]*$ex_rate,2);
					$shippingCostsNetFC+=round($order[$k]["shipping_costs"]/$ust,2);
					$shippingCostsNet+=round($order[$k]["shipping_costs"]/$ust*$ex_rate,2);
				}
				else
				{
					$shippingCostsGrossFC+=round($order[$k]["shipping_net"]*$ust,2);
					$shippingCostsGross+=round($order[$k]["shipping_net"]*$ust*$ex_rate,2);
					$shippingCostsNetFC+=$order[$k]["shipping_net"];
					$shippingCostsNet+=round($order[$k]["shipping_net"]*$ex_rate,2);
				}
			}
			if ($shop[$order[0]["shop_id"]]["shop_type"]==2 || $shop[$order[0]["shop_id"]]["shop_type"]==3)
			{
				$shippingCostsTaxFC+=$shippingCostsGrossFC/$ust*($ust-1);
				$shippingCostsTax+=$shippingCostsGross/$ust*($ust-1);
			}
			else
			{
				$shippingCostsTaxFC+=round($shippingCostsNetFC*($ust-1),2);
				$shippingCostsTax+=round($shippingCostsNet*($ust-1),2);
			}
		}
		
		//$collateralTax=$orderItemsTotalCollateralGross-$orderItemsTotalCollateralNet;
		
		$orderTotalNetFC=$orderItemsTotalNetFC+$shippingCostsNetFC;
		$orderTotalNet=$orderItemsTotalNet+$shippingCostsNet;
		$orderTotalTaxFC=$orderItemsTotalTaxFC+$shippingCostsTaxFC;
		$orderTotalTax=$orderItemsTotalTax+$shippingCostsTax;
		//$orderTotalGross=$orderItemsTotalGross+$orderItemsTotalCollateralGross+$shippingCostsGross+$orderTotalTax;
		$orderTotalGrossFC=$orderItemsTotalGrossFC+$shippingCostsGrossFC;
		$orderTotalGross=$orderItemsTotalGross+$shippingCostsGross;
		
		$xmldata.="	</OrderItems>\n";
		
			//SHOWS EACH ORDER (OF COMBINED)
		$xmldata.="	<orders>\n";
		for ($k=0; $k<sizeof($order); $k++)
		{
			$xmldata.="	<order>\n";
			$xmldata.="		<orderid><![CDATA[".$order[$k]["id_order"]."]]></orderid>\n";
			$xmldata.="		<foreign_OrderID><![CDATA[".$order[$k]["foreign_OrderID"]."]]></foreign_OrderID>\n";
			$xmldata.="		<combined_with><![CDATA[".$order[$k]["combined_with"]."]]></combined_with>\n";
			$xmldata.="		<payment_type_id><![CDATA[".$order[$k]["payments_type_id"]."]]></payment_type_id>\n";
			$xmldata.="		<shipping_type_id><![CDATA[".$order[$k]["shipping_type_id"]."]]></shipping_type_id>\n";
			$xmldata.="		<shipping_title><![CDATA[".$shipping_title[$k]."]]></shipping_title>\n";
			$xmldata.="		<shipping_number><![CDATA[".$order[$k]["shipping_number"]."]]></shipping_number>\n";
			$xmldata.="		<shipping_netFC><![CDATA[".number_format($order[$k]["shipping_net"], 2,",","")."]]></shipping_netFC>\n";
			$xmldata.="		<shipping_costsFC><![CDATA[".number_format($order[$k]["shipping_costs"], 2,",","")."]]></shipping_costsFC>\n";
			if (isset($exchangerates[$order[$k]["id_order"]]))
			{
				$xmldata.="		<shipping_costs><![CDATA[".number_format($order[$k]["shipping_costs"]*$exchangerates[$order[$k]["id_order"]], 2,",","")."]]></shipping_costs>\n";
				$xmldata.="		<shipping_net><![CDATA[".number_format($order[$k]["shipping_net"]*$exchangerates[$order[$k]["id_order"]], 2,",","")."]]></shipping_net>\n";
			}
			else
			//VERSANDKOSTEN MIT AKTUELLEN WECHSELKURS
			{
				$xmldata.="		<shipping_costs><![CDATA[".number_format($order[$k]["shipping_costs"]*$exchangerates[0], 2,",","")."]]></shipping_costs>\n";
				$xmldata.="		<shipping_net><![CDATA[".number_format($order[$k]["shipping_net"]*$exchangerates[0], 2,",","")."]]></shipping_net>\n";
			}

			$xmldata.="	</order>\n";
		}
		$xmldata.="	</orders>\n";
//RETURNS		

		if (!$singleorder)
		{
			//GET ORDERITEMS
			$order_items=array();
			if (sizeof($orderitems)>0)
			{
				$res_orderitems = q("SELECT * FROM shop_orders_items WHERE id IN (".implode(",", $orderitems).")", $dbshop, __FILE__, __LINE__);
				while ($row_orderitems = mysqli_fetch_assoc($res_orderitems))
				{
					$order_items[$row_orderitems["id"]]=$row_orderitems;
				}
			}

			
			$returns=array();
			$returns_ids=array();
			$res_returns = q("SELECT * FROM shop_returns2 WHERE order_id IN (".implode(",",$order_ids).")", $dbshop, __FILE__, __LINE__);
			while ($row_returns = mysqli_fetch_assoc($res_returns))
			{
				if (isset($returns[$row_returns["order_id"]]))
				{
					$returns[$row_returns["order_id"]][sizeof($returns[$row_returns["order_id"]])]=$row_returns;
				}
				else
				{
					$returns[$row_returns["order_id"]][0]=$row_returns;
				}
				$returns_ids[]=$row_returns["id_return"];
			}
			//GET RETURNS ITEMS 
			$returns_items = array();
			if (sizeof($returns_ids)>0)
			{
				$res_returns_items = q("SELECT * FROM shop_returns_items WHERE return_id IN (".implode(",",$returns_ids).");", $dbshop, __FILE__, __LINE__);
				while ($row_returns_items=mysqli_fetch_assoc($res_returns_items))
				{
					if (isset($returns_items[$row_returns_items["return_id"]]))
					{
						$returns_items[$row_returns_items["return_id"]][sizeof($returns_items[$row_returns_items["return_id"]])]=$row_returns_items;
					}
					else
					{
						$returns_items[$row_returns_items["return_id"]][0]=	$row_returns_items;
					}
					
				}
		
			}
			
		//OUTPUT RETURN DATA
			$sum_returnitemsFCnet=0;
			$sum_returnitemsFCgross=0;
			$sum_returnitemsEURnet=0;
			$sum_returnitemsEURgross=0;
			$sum_return_shippingFCnet = 0;
			$sum_return_shippingFCgross = 0;
			$sum_return_shippingEURnet = 0;
			$sum_return_shippingEURgross = 0;
			if (sizeof ($returns)>0)
			{
				$xmldata.=" <returns>\n";
				foreach ($returns as $orderid => $return)
				{
					for ($l=0; $l<sizeof($return); $l++)
					{
						$xmldata.="		<return>\n";
						$id_return=0;
						while (list ($key, $val) = each ($return[$l]))
						{		
							$xmldata.="			<r_".$key."><![CDATA[".$val."]]></r_".$key.">\n";
							if ($key=="id_return") $id_return = $val;
						}
						//ADD RETURNITEMS
						if (isset($returns_items[$id_return]))
						{
							$xmldata.="			<returnitems>\n";
							for ($m=0; $m<sizeof($returns_items[$id_return]); $m++)
							{
								$xmldata.="			<returnitem>\n";
								while (list ($key, $val) = each ($returns_items[$id_return][$m]))
								{		
									$xmldata.="			<r_".$key."><![CDATA[".$val."]]></r_".$key.">\n";
									
								}
								if ($return[$l]["state"]==1)
								{
									$sum_returnitemsFCnet+=$returns_items[$id_return][$m]["amount"]*$order_items[$returns_items[$id_return][$m]["shop_orders_items_id"]]["netto"];
									$sum_returnitemsFCgross+=$returns_items[$id_return][$m]["amount"]*$order_items[$returns_items[$id_return][$m]["shop_orders_items_id"]]["price"];
									$sum_returnitemsEURnet+=$returns_items[$id_return][$m]["amount"]*$order_items[$returns_items[$id_return][$m]["shop_orders_items_id"]]["netto"]/$order_items[$returns_items[$id_return][$m]["shop_orders_items_id"]]["exchange_rate_to_EUR"];
									$sum_returnitemsEURgross+=$returns_items[$id_return][$m]["amount"]*$order_items[$returns_items[$id_return][$m]["shop_orders_items_id"]]["price"]/$order_items[$returns_items[$id_return][$m]["shop_orders_items_id"]]["exchange_rate_to_EUR"];
									$exchangerate=$order_items[$returns_items[$id_return][$m]["shop_orders_items_id"]]["exchange_rate_to_EUR"];
								}
								$xmldata.="			</returnitem>\n";
							}
							$xmldata.="			</returnitems>\n";
							
							if ($return[$l]["state"]==1)
							{
								$sum_return_shippingFCnet+= $return[$l]["refund_order_shipment"]/$ust;
								$sum_return_shippingFCgross+=$return[$l]["refund_order_shipment"];
								$sum_return_shippingEURnet+=$return[$l]["refund_order_shipment"]/$exchangerate/$ust;
								$sum_return_shippingEURgross+=$return[$l]["refund_order_shipment"]/$exchangerate;
							}
					//		echo "%%%%".$sum_return_shippingFCgross;
						}
						$xmldata.="		</return>\n";
					}
				}
				$xmldata.="	</returns>\n";
			}
	/*		
		//CREATE RETURN SUMS
			// ES WERDER NUR ARTIKEL BERÜCKSICHTIGT, DEREN RETURNS ALS GUTGESCHRIEBEN UND EINGEGANGEN MARKIERT WURDEN
			$affected_returns = array();
			$returns_refund = array();
			$returns_refund_shipment = array();
			if (sizeof ($returns)>0)
			{
				foreach ($returns as $orderid => $return)
				{
					for ($l=0; $l<sizeof($return); $l++)
					{
						//CHECK FOR GUTSCHRIFT && EINGANG bei Rcükgaben
//						if (($return[$l]["date_return"]!=0 && $return[$l]["date_refund"]!=0 && $return[$l]["return_type"]=="return") || $return[$l]["return_type"]=="exchange")
						{
							$affected_returns[$return[$l]["id_return"]]=0;
							$returns_refund[$return[$l]["id_return"]] = $return[$l]["refund"];
							$returns_refund_shipment[$return[$l]["id_return"]] = $return[$l]["refund_shipment"];
						}
					}
				}
			}
			//GET RETURNED ITEMS FOR AFFECTED RETURN
			$returned_shop_orders_items_ids = array();
			$returned_shop_orders_items_amounts = array();
			if (sizeof($affected_returns)>0)
			{
				foreach ($affected_returns as $id_return =>$data)
				{
					for ($m=0; $m<sizeof($returns_items[$id_return]); $m++)
					{
						if (isset($returned_shop_orders_items_amounts[$returns_items[$id_return][$m]["shop_orders_items_id"]]))
						{
							$returned_shop_orders_items_amounts[$returns_items[$id_return][$m]["shop_orders_items_id"]]+=$returns_items[$id_return][$m]["amount"];
						}
						else
						{
							$returned_shop_orders_items_amounts[$returns_items[$id_return][$m]["shop_orders_items_id"]]=$returns_items[$id_return][$m]["amount"];
							$returned_shop_orders_items_ids[]=$returns_items[$id_return][$m]["shop_orders_items_id"];
						}
					}
				}
			}
			//GET SHOP_ORDRES_ITEMS
			//$res_order_items = q("SELECT * FROM shop_orders_items WHERE id IN (".implode(", ", $returned_shop_orders_items_ids).")", $dbshop, __FILE__, __LINE__);
			//CREATE REFUND SUMS
			if (isset($exchangerates[$order[0]["id_order"]]))
			{
				$exrate=$exchangerates[$order[0]["id_order"]];
			}
			else
			{
				$exrate=$exchange_rate[$order[0]["Currency_Code"]];
			}
	
	
	
			$refund_grossFC=0;
			$refund_netFC=0;
			$refund_grossEUR=0;
			$refund_netEUR=0;
			foreach($returns_refund as $returnid => $refund)
			{
				$refund_grossEUR+=$refund;
			}
				$refund_netEUR = $refund_grossEUR / $ust;
	
				$refund_grossFC = $refund_grossEUR * $exrate;
				$refund_netFC = $refund_grossEUR * $exrate / $ust;
	
			$refund_shipment_grossFC=0;
			$refund_shipment_netFC=0;
			$refund_shipment_grossEUR=0;
			$refund_shipment_netEUR=0;
			foreach($returns_refund_shipment as $returnid => $refund_shipment)
			{
				$refund_shipment_grossEUR+=$refund_shipment;
			}
				$refund_shipment_netEUR = $refund_shipment_grossEUR / $ust;
				
				$refund_shipment_grossFC = $refund_shipment_grossEUR * $exrate;
				$refund_shipmen_netFC = $refund_shipment_grossEUR * $exrate / $ust;
				
				//TOTALS
			$refund_total_grossFC = $refund_grossFC+$refund_shipment_grossFC;
			$refund_total_netFC = $refund_netFC+$refund_shipment_netFC;
			$refund_total_grossEUR = $refund_grossEUR + $refund_shipment_grossEUR;
			$refund_total_netEUR = $refund_netEUR + $refund_shipmen_netEUR;
		*/
		}
		else
		//MODE != SINGLE
		{
			//GET ORDERITEMS
			$order_items=array();
			if (sizeof($orderitems)>0)
			{
				$res_orderitems = q("SELECT * FROM shop_orders_items WHERE id IN (".implode(",", $orderitems).")", $dbshop, __FILE__, __LINE__);
				while ($row_orderitems = mysqli_fetch_assoc($res_orderitems))
				{
					$order_items[$row_orderitems["id"]]=$row_orderitems;
				}
			}
			
			//GET RETURNITEMS 
			$returns_ids=array();
			$return_items=array();
			if (sizeof($orderitems)>0)
			{
				$res_returns_items = q("SELECT * FROM shop_returns_items WHERE shop_orders_items_id IN (".implode(",", $orderitems).")", $dbshop, __FILE__, __LINE__);
				while ($row_returns_items=mysqli_fetch_assoc($res_returns_items))
				{
					$returns_ids[]=$row_returns_items["return_id"];
					$return_items[$row_returns_items["return_id"]][]=$row_returns_items;
					
				}
			}
			//GET RETURNS
			$sum_returnitemsFCnet=0;
			$sum_returnitemsFCgross=0;
			$sum_returnitemsEURnet=0;
			$sum_returnitemsEURgross=0;
			$sum_return_shippingFCnet = 0;
			$sum_return_shippingFCgross = 0;
			$sum_return_shippingEURnet = 0;
			$sum_return_shippingEURgross = 0;
			$returns=array();
			if (sizeof($returns_ids)>0)
			{
				$res_returns = q ("SELECT * FROM shop_returns2 WHERE id_return IN (".implode(",", $returns_ids).")", $dbshop, __FILE__, __LINE__);
				while($row_returns=mysqli_fetch_assoc($res_returns))
				{
					$returns[]=$row_returns;
				}
			}
			$xmldata.="	<returns>\n";
			for ($i=0;$i<sizeof($returns);$i++)
			{
				$xmldata.="	<return>\n";
				while (list ($key, $val) = each ($returns[$i]))
				{		
					$xmldata.="			<r_".$key."><![CDATA[".$val."]]></r_".$key.">\n";
					if ($key=="id_return") $id_return = $val;
					if ($key=="state") $status = $val;
				}
				if (isset($return_items[$id_return]))
				{
					$xmldata.="			<returnitems>\n";
					for ($m=0; $m<sizeof($return_items[$id_return]); $m++)
					{
						$xmldata.="			<returnitem>\n";
						while (list ($key, $val) = each ($return_items[$id_return][$m]))
						{		
							$xmldata.="			<r_".$key."><![CDATA[".$val."]]></r_".$key.">\n";
							
						}
						if ($status==1)
						{
							$sum_returnitemsFCnet+=$return_items[$id_return][$m]["amount"]*$order_items[$return_items[$id_return][$m]["shop_orders_items_id"]]["netto"];
							$sum_returnitemsFCgross+=$return_items[$id_return][$m]["amount"]*$order_items[$return_items[$id_return][$m]["shop_orders_items_id"]]["price"];
							$sum_returnitemsEURnet+=$return_items[$id_return][$m]["amount"]*$order_items[$return_items[$id_return][$m]["shop_orders_items_id"]]["netto"]/$order_items[$return_items[$id_return][$m]["shop_orders_items_id"]]["exchange_rate_to_EUR"];
							$sum_returnitemsEURgross+=$return_items[$id_return][$m]["amount"]*$order_items[$return_items[$id_return][$m]["shop_orders_items_id"]]["price"]/$order_items[$return_items[$id_return][$m]["shop_orders_items_id"]]["exchange_rate_to_EUR"];
							$exchangerate=$order_items[$return_items[$id_return][$m]["shop_orders_items_id"]]["exchange_rate_to_EUR"];
						}
						$xmldata.="			</returnitem>\n";
					}
					
					if ($status==1)
					{
						$sum_return_shippingFCnet+= $returns[$i]["refund_order_shipment"]/$ust;
						$sum_return_shippingFCgross+=$returns[$i]["refund_order_shipment"];
						$sum_return_shippingEURnet+=$returns[$i]["refund_order_shipment"]/$exchangerate/$ust;
						$sum_return_shippingEURgross+=$returns[$i]["refund_order_shipment"]/$exchangerate;
					}
						//	echo "%%%%".$sum_return_shippingFCgross;
					$xmldata.="			</returnitems>\n";
				}
				$xmldata.="		</return>\n";
			}
			$xmldata.="	</returns>\n";
/*
		//CREATE RETURN SUMS
			// ES WERDER NUR ARTIKEL BERÜCKSICHTIGT, DEREN RETURNS ALS GUTGESCHRIEBEN UND EINGEGANGEN MARKIERT WURDEN
			$affected_returns = array();
			$returns_refund = array();
			$returns_refund_shipment = array();
			for ($l=0; $l<sizeof($returns); $l++)
			{
				//CHECK FOR GUTSCHRIFT && EINGANG
//				if ($returns[$l]["date_return"]!=0 && $returns[$l]["date_refund"]!=0)
//				if (($returns[$l]["date_return"]!=0 && $returns[$l]["date_refund"]!=0 && $returns[$l]["return_type"]=="return") || $returns[$l]["return_type"]=="exchange")
				{
					$affected_returns[$returns[$l]["id_return"]]=0;
					$returns_refund[$returns[$l]["id_return"]] = $returns[$l]["refund"];
					$returns_refund_shipment[$returns[$l]["id_return"]] = $returns[$l]["refund_shipment"];
				}
			}
			//GET RETURNED ITEMS FOR AFFECTED RETURN
			$returned_shop_orders_items_ids = array();
			$returned_shop_orders_items_amounts = array();
			if (sizeof($affected_returns)>0)
			{
				foreach ($affected_returns as $id_return =>$data)
				{
					for ($m=0; $m<sizeof($return_items[$id_return]); $m++)
					{
						if (isset($returned_shop_orders_items_amounts[$return_items[$id_return][$m]["shop_orders_items_id"]]))
						{
							$returned_shop_orders_items_amounts[$return_items[$id_return][$m]["shop_orders_items_id"]]+=$return_items[$id_return][$m]["amount"];
						}
						else
						{
							$returned_shop_orders_items_amounts[$return_items[$id_return][$m]["shop_orders_items_id"]]=$return_items[$id_return][$m]["amount"];
							$returned_shop_orders_items_ids[]=$return_items[$id_return][$m]["shop_orders_items_id"];
						}
					}
				}
			}
			//GET SHOP_ORDRES_ITEMS
			//$res_order_items = q("SELECT * FROM shop_orders_items WHERE id IN (".implode(", ", $returned_shop_orders_items_ids).")", $dbshop, __FILE__, __LINE__);
			//CREATE REFUND SUMS
			if (isset($exchangerates[$order[0]["id_order"]]))
			{
				$exrate=$exchangerates[$order[0]["id_order"]];
			}
			else
			{
				$exrate=$exchange_rate[$order[0]["Currency_Code"]];
			}
	
			$refund_grossFC=0;
			$refund_netFC=0;
			$refund_grossEUR=0;
			$refund_netEUR=0;
			foreach($returns_refund as $returnid => $refund)
			{
				$refund_grossEUR+=$refund;
			}
				$refund_netEUR = $refund_grossEUR / $ust;
	
				$refund_grossFC = $refund_grossEUR * $exrate;
				$refund_netFC = $refund_grossEUR * $exrate / $ust;
	
			$refund_shipment_grossFC=0;
			$refund_shipment_netFC=0;
			$refund_shipment_grossEUR=0;
			$refund_shipment_netEUR=0;
			foreach($returns_refund_shipment as $returnid => $refund_shipment)
			{
				$refund_shipment_grossEUR+=$refund_shipment;
			}
				$refund_shipment_netEUR = $refund_shipment_grossEUR / $ust;
				
				$refund_shipment_grossFC = $refund_shipment_grossEUR * $exrate;
				$refund_shipmen_netFC = $refund_shipment_grossEUR * $exrate / $ust;
				
				//TOTALS
			$refund_total_grossFC = $refund_grossFC+$refund_shipment_grossFC;
			$refund_total_netFC = $refund_netFC+$refund_shipment_netFC;
			$refund_total_grossEUR = $refund_grossEUR + $refund_shipment_grossEUR;
			$refund_total_netEUR = $refund_netEUR + $refund_shipmen_netEUR;
*/


		}
/*		
		$xmldata.="	<refundGrossFC>".number_format($refund_grossFC, 2,",","")."</refundGrossFC>\n";
		$xmldata.="	<refundNetFC>".number_format($refund_netFC, 2,",","")."</refundNetFC>\n";
		$xmldata.="	<refundGrossEUR>".number_format($refund_grossEUR, 2,",","")."</refundGrossEUR>\n";
		$xmldata.="	<refundNetEUR>".number_format($refund_netEUR, 2,",","")."</refundNetEUR>\n";

		$xmldata.="	<refundShipmentGrossFC>".number_format($refund_shipment_grossFC, 2,",","")."</refundShipmentGrossFC>\n";
		$xmldata.="	<refundShipmentNetFC>".number_format($refund_shipment_netFC, 2,",","")."</refundShipmentNetFC>\n";
		$xmldata.="	<refundShipmentGrossEUR>".number_format($refund_shipment_grossEUR, 2,",","")."</refundShipmentGrossEUR>\n";
		$xmldata.="	<refundShipmentNetEUR>".number_format($refund_shipment_netEUR, 2,",","")."</refundShipmentNetEUR>\n";

		$xmldata.="	<refundTotalGrossFC>".number_format($refund_total_grossFC, 2,",","")."</refundTotalGrossFC>\n";
		$xmldata.="	<refundTotalNetFC>".number_format($refund_total_netFC, 2,",","")."</refundTotalNetFC>\n";
		$xmldata.="	<refundTotalGrossEUR>".number_format($refund_total_grossEUR, 2,",","")."</refundTotalGrossEUR>\n";
		$xmldata.="	<refundTotalNetEUR>".number_format($refund_total_netEUR, 2,",","")."</refundTotalNetEUR>\n";
*/
//SUMS
	//Shipping Costs
	
//	$exchangerates[$row_order_items["order_id"]]
		if (isset($exchangerates[$order[0]["id_order"]]) && $exchangerates[$order[0]["id_order"]]!=0 )
		{
			$exchangerate = $exchangerates[$order[0]["id_order"]];
		}
		else
		{
			$exchangerate = $exchangerates[0];
		}
		
		$exchangerate = $exchangerates[$order[0]["id_order"]];
	//	$xmldata.="	<testtest>".(1/$exchangerate)."</testtest>\n";
		if ($shop[$order[0]["shop_id"]]["shop_type"]==2 || $shop[$order[0]["shop_id"]]["shop_type"]==3)
		{

			$xmldata.="	<shippingCostsGrossFC>".number_format($shippingCostsGrossFC, 2,",","")."</shippingCostsGrossFC>\n";
			$xmldata.="	<shippingCostsGross>".number_format(round($shippingCostsGrossFC*$exchangerate,2),2,",","")."</shippingCostsGross>\n";
			$xmldata.="	<shippingCostsNetFC>".number_format($shippingCostsNetFC, 2,",","")."</shippingCostsNetFC>\n";
			$xmldata.="	<shippingCostsNet>".number_format(round($shippingCostsNetFC*$exchangerate,2), 2,",","")."</shippingCostsNet>\n";
			$xmldata.="	<shippingCostsTaxFC>".number_format($shippingCostsTaxFC, 2,",","")."</shippingCostsTaxFC>\n";
			$xmldata.="	<shippingCostsTax>".number_format(round($shippingCostsTaxFC*$exchangerate,2), 2,",","")."</shippingCostsTax>\n";
		//Item Total
			$xmldata.="	<orderItemsTotalGrossFC>".number_format($orderItemsTotalGrossFC, 2,",","")."</orderItemsTotalGrossFC>\n";
			$xmldata.="	<orderItemsTotalGross>".number_format(round($orderItemsTotalGrossFC*$exchangerate,2), 2,",","")."</orderItemsTotalGross>\n";
			$xmldata.="	<orderItemsTotalNetFC>".number_format($orderItemsTotalNetFC, 2,",","")."</orderItemsTotalNetFC>\n";
			$xmldata.="	<orderItemsTotalNet>".number_format(round($orderItemsTotalNetFC*$exchangerate,2), 2,",","")."</orderItemsTotalNet>\n";
		//	$xmldata.="	<orderItemsTotalCollateralGross>".number_format($orderItemsTotalCollateralGross, 2,",","")."</orderItemsTotalCollateralGross>\n";
		//	$xmldata.="	<orderItemsTotalCollateralNet>".number_format($orderItemsTotalCollateralNet, 2,",","")."</orderItemsTotalCollateralNet>\n";
			$xmldata.="	<orderItemsTotalTaxFC>".number_format($orderItemsTotalTaxFC, 2,",","")."</orderItemsTotalTaxFC>\n";
			$xmldata.="	<orderItemsTotalTax>".number_format(round($orderItemsTotalTaxFC*$exchangerate,2), 2,",","")."</orderItemsTotalTax>\n";
		//OrderTotal
			$xmldata.="	<orderTotalGrossFC>".number_format($orderTotalGrossFC, 2,",","")."</orderTotalGrossFC>\n";
			//$xmldata.="	<orderTotalGross>".number_format(round($orderTotalGrossFC*$exchangerate,2), 2,",","")."</orderTotalGross>\n";
			$xmldata.="	<orderTotalGross>".number_format($orderTotalGross, 2,",","")."</orderTotalGross>\n";
			$xmldata.="	<orderTotalNetFC>".number_format($orderTotalNetFC, 2,",","")."</orderTotalNetFC>\n";
			$xmldata.="	<orderTotalNet>".number_format(round($orderTotalNetFC*$exchangerate,2),2,",","")."</orderTotalNet>\n";
			$xmldata.="	<orderTotalTaxFC>".number_format($orderTotalTaxFC, 2,",","")."</orderTotalTaxFC>\n";
			$xmldata.="	<orderTotalTax>".number_format(round($orderTotalTaxFC*$exchangerate,2), 2,",","")."</orderTotalTax>\n";
		//ORDER - REFUND
		/*
			$xmldata.="	<completeTotalGrossFC>".number_format($orderTotalGrossFC-$refund_total_grossFC, 2,",","")."</completeTotalGrossFC>\n";
			$xmldata.="	<completeTotalGross>".number_format($orderTotalGross-$refund_total_grossEUR, 2,",","")."</completeTotalGross>\n";
			$xmldata.="	<completeTotalNetFC>".number_format($orderTotalNetFC-$refund_total_netFC, 2,",","")."</completeTotalNetFC>\n";
			$xmldata.="	<completeTotalNet>".number_format($orderTotalNet-$refund_total_netEUR, 2,",","")."</completeTotalNet>\n";
		*/
			$xmldata.="	<completeTotalGrossFC>".number_format($orderTotalGrossFC-$sum_returnitemsFCgross-$sum_return_shippingFCgross, 2,",","")."</completeTotalGrossFC>\n";
			$xmldata.="	<completeTotalGross>".number_format(round(($orderTotalGrossFC-$sum_returnitemsFCgross-$sum_return_shippingFCgross)*$exchangerate,2), 2,",","")."</completeTotalGross>\n";
			$xmldata.="	<completeTotalNetFC>".number_format(round(($orderTotalNetFC-$sum_returnitemsFCgross-$sum_return_shippingFCgross)/$ust,2), 2,",","")."</completeTotalNetFC>\n";
			$xmldata.="	<completeTotalNet>".number_format(round(($orderTotalNet-($sum_returnitemsFCgross-$sum_return_shippingFCgross)/$ust*$exchangerate),2), 2,",","")."</completeTotalNet>\n";
	
			$xmldata.="	<orderItemCount>".$orderItemCount."</orderItemCount>\n";
			$xmldata.="	<orderCollateralCount>".$collcnt."</orderCollateralCount>\n";
			$xmldata.="	<orderPositions>".mysqli_num_rows($res_order_items)."</orderPositions>\n";
			$xmldata.="	<orderComplete>".$complete."</orderComplete>\n";
			
		}
		else
		{
			$xmldata.="	<shippingCostsGrossFC>".number_format(round(($shippingCostsNetFC*$ust),2), 2,",","")."</shippingCostsGrossFC>\n";
			$xmldata.="	<shippingCostsGross>".number_format(round($shippingCostsNetFC*$exchangerate*$ust,2), 2,",","")."</shippingCostsGross>\n";
			$xmldata.="	<shippingCostsNetFC>".number_format($shippingCostsNetFC, 2,",","")."</shippingCostsNetFC>\n";
			$xmldata.="	<shippingCostsNet>".number_format(round($shippingCostsNetFC*$exchangerate,2), 2,",","")."</shippingCostsNet>\n";
			$xmldata.="	<shippingCostsTaxFC>".number_format($shippingCostsTaxFC, 2,",","")."</shippingCostsTaxFC>\n";
			$xmldata.="	<shippingCostsTax>".number_format(round($shippingCostsTaxFC*$exchangerate,2), 2,",","")."</shippingCostsTax>\n";
		//Item Total
			$xmldata.="	<orderItemsTotalGrossFC>".number_format(round($orderItemsTotalNetFC*$ust,2), 2,",","")."</orderItemsTotalGrossFC>\n";
			$xmldata.="	<orderItemsTotalGross>".number_format(round($orderItemsTotalNetFC*$ust*$exchangerate,2), 2,",","")."</orderItemsTotalGross>\n";
			$xmldata.="	<orderItemsTotalNetFC>".number_format($orderItemsTotalNetFC, 2,",","")."</orderItemsTotalNetFC>\n";
			$xmldata.="	<orderItemsTotalNet>".number_format(round($orderItemsTotalNetFC*$exchangerate,2), 2,",","")."</orderItemsTotalNet>\n";
		//	$xmldata.="	<orderItemsTotalCollateralGross>".number_format($orderItemsTotalCollateralGross, 2,",","")."</orderItemsTotalCollateralGross>\n";
		//	$xmldata.="	<orderItemsTotalCollateralNet>".number_format($orderItemsTotalCollateralNet, 2,",","")."</orderItemsTotalCollateralNet>\n";
			$xmldata.="	<orderItemsTotalTaxFC>".number_format($orderItemsTotalTaxFC, 2,",","")."</orderItemsTotalTaxFC>\n";
			$xmldata.="	<orderItemsTotalTax>".number_format(round($orderItemsTotalTaxFC*$exchangerate,2), 2,",","")."</orderItemsTotalTax>\n";
		//OrderTotal
			$xmldata.="	<orderTotalGrossFC>".number_format(round($orderTotalNetFC*$ust,2), 2,",","")."</orderTotalGrossFC>\n";
			$xmldata.="	<orderTotalGross>".number_format(round($orderTotalNetFC*$exchangerate*$ust,2), 2,",","")."</orderTotalGross>\n";
			$xmldata.="	<orderTotalNetFC>".number_format($orderTotalNetFC, 2,",","")."</orderTotalNetFC>\n";
			$xmldata.="	<orderTotalNet>".number_format(round($orderTotalNetFC*$exchangerate,2), 2,",","")."</orderTotalNet>\n";
			$xmldata.="	<orderTotalTaxFC>".number_format($orderTotalTaxFC, 2,",","")."</orderTotalTaxFC>\n";
			$xmldata.="	<orderTotalTax>".number_format(round($orderTotalTaxFC*$exchangerate,2), 2,",","")."</orderTotalTax>\n";
		//ORDER - REFUND
		/*
			$xmldata.="	<completeTotalGrossFC>".number_format($orderTotalGrossFC-$refund_total_grossFC, 2,",","")."</completeTotalGrossFC>\n";
			$xmldata.="	<completeTotalGross>".number_format($orderTotalGross-$refund_total_grossEUR, 2,",","")."</completeTotalGross>\n";
			$xmldata.="	<completeTotalNetFC>".number_format($orderTotalNetFC-$refund_total_netFC, 2,",","")."</completeTotalNetFC>\n";
			$xmldata.="	<completeTotalNet>".number_format($orderTotalNet-$refund_total_netEUR, 2,",","")."</completeTotalNet>\n";
		*/
			$xmldata.="	<completeTotalGrossFC>".number_format(round(($orderTotalNetFC-$sum_returnitemsFCnet - $sum_return_shippingFCnet)*$ust,2), 2,",","")."</completeTotalGrossFC>\n";
			$xmldata.="	<completeTotalGross>".number_format(round(($orderTotalNetFC-$sum_returnitemsFCnet - $sum_return_shippingFCnet)*$ust*$exchangerate,2), 2,",","")."</completeTotalGross>\n";
			$xmldata.="	<completeTotalNetFC>".number_format($orderTotalNetFC-$sum_returnitemsFCnet - $sum_return_shippingFCnet, 2,",","")."</completeTotalNetFC>\n";
			$xmldata.="	<completeTotalNet>".number_format(round(($orderTotalNetFC-$sum_returnitemsFCnet - $sum_return_shippingFCnet)*$exchangerate,2), 2,",","")."</completeTotalNet>\n";
	
			$xmldata.="	<orderItemCount>".$orderItemCount."</orderItemCount>\n";
			$xmldata.="	<orderCollateralCount>".$collcnt."</orderCollateralCount>\n";
			$xmldata.="	<orderPositions>".mysqli_num_rows($res_order_items)."</orderPositions>\n";
			$xmldata.="	<orderComplete>".$complete."</orderComplete>\n";
			
		}
		
		$xmldata.="</Order>\n";

	//SERVICE RESPONSE
	echo $xmldata;

?>
