<?php

	check_man_params(array("OrderID" =>"numericNN"));					


	include("../functions/mapco_gewerblich.php");

	if ( !isset($_POST["OrderID"]) )
	{
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
	}
	
//	$ust = (UST/100) +1;
	
	//GET ORDERdata
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		show_error(9762, 7, __FILE__, __LINE__, "OrderID:".$_POST["OrderID"]);
		exit;
		/*
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderID konnte keine Bestellung gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
		*/
	}
	$order=array();
	$order[0]=mysqli_fetch_array($res_order);

	if ($order[0]["combined_with"]>0)
	{
		$tmp_orderid=$order[0]["combined_with"];
		
		//GET ALL ORDERS OF COMBINATION; ORDER[0] ->"MOTHER"
		$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
		$order[0]=mysqli_fetch_array($res_orders);
		
		$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$tmp_orderid." AND NOT id_order = ".$tmp_orderid.";", $dbshop, __FILE__, __LINE__);
		while ($row_orders=mysqli_fetch_array($res_orders))
		{
			$order[sizeof($order)]=$row_orders;
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
	$in_orders='';
	for ($j=0; $j<sizeof($order); $j++)
	{
		if ($in_orders=='') $in_orders.=$order[$j]["id_order"]; else $in_orders.=", ".$order[$j]["id_order"];
	}
	
	$res_order_items=q("SELECT * FROM shop_orders_items WHERE order_id IN (".$in_orders.");", $dbshop, __FILE__, __LINE__);
	/*
	if (mysqli_num_rows($res_order_items)==0)
	{
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine Artikel zur Bestellung.</shortMsg>'."\n";
		echo '		<longMsg>Es konnten keine Artikel zur angegebenen Bestellung gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
	}
*/
	
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
	
	$exchangerates=array();
	//SET ACTUAL EXCHANGERATE
	$res_ex=q("SELECT * FROM shop_currencies WHERE currency_code = '".$order[0]["Currency_Code"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_ex)==1)
	{
		$row_ex=mysqli_fetch_array($res_ex);
		$exchangerates[0]=1/$row_ex["exchange_rate_to_EUR"];
	}
	
	while ($row_order_items=mysqli_fetch_array($res_order_items))
	{
		//ITEM DESCRIPTION
		$res_items_desc=q("SELECT * FROM shop_items_de WHERE id_item = ".$row_order_items["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row_items_desc=mysqli_fetch_array($res_items_desc);
		
		//MPN, COS
		$res_items_MPN=q("SELECT MPN, COS FROM shop_items WHERE id_item = ".$row_order_items["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row_items_MPN=mysqli_fetch_array($res_items_MPN);
		
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
		{
			$orderItemPriceGrossFC=$row_order_items["netto"]*$ust;
			$orderItemPriceGross=round($row_order_items["netto"]*$ust*$ex_rate,2);
			$orderItemPriceNetFC=$row_order_items["netto"];
			$orderItemPriceNet=round(($row_order_items["netto"])*$ex_rate,2);
		}
		//$orderItemPriceGross=$row_order_items["price"];
		//$orderItemPriceNet=$row_order_items["netto"];
		$orderItemCollateralGross=$collateralGross;
		$orderItemCollateralNet=$collateralNet;
		//$orderItemPriceTax=$row_order_items["netto"]*($ust-1);
		$orderItemPriceTax=$orderItemPriceNet*($ust-1);
		$orderItemPriceTaxFC=$orderItemPriceNetFC*($ust-1);
		
		//BESTELLPOSITION SUMME
		//$orderItemTotalGross=$row_order_items["price"]*$row_order_items["amount"];
		//$orderItemTotalNet=$row_order_items["netto"]*$row_order_items["amount"];
		$orderItemTotalGrossFC=$orderItemPriceNetFC*$row_order_items["amount"]*$ust;
		$orderItemTotalGross=$orderItemPriceNet*$row_order_items["amount"]*$ust;
		$orderItemTotalNetFC=$orderItemPriceNetFC*$row_order_items["amount"];
		$orderItemTotalNet=$orderItemPriceNet*$row_order_items["amount"];
		$orderItemTotalCollateralGross=$orderItemCollateralNet*$row_order_items["amount"]*$ust;
		$orderItemTotalCollateralNet=$orderItemCollateralNet*$row_order_items["amount"];
		$orderItemTotalTax=$orderItemTotalNet*($ust-1);
		$orderItemTotalTaxFC=$orderItemTotalNetFC*($ust-1);
		
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
		
		//ARTIKEL EINZELPREIS
		$xmldata.="			<orderItemPriceGrossFC>".number_format($orderItemPriceGrossFC, 2,",",".")."</orderItemPriceGrossFC>\n";
		$xmldata.="			<orderItemPriceGross>".number_format($orderItemPriceGross, 2,",",".")."</orderItemPriceGross>\n";
		$xmldata.="			<orderItemPriceNetFC>".number_format($orderItemPriceNetFC, 2,",",".")."</orderItemPriceNetFC>\n";
		$xmldata.="			<orderItemPriceNet>".number_format($orderItemPriceNet, 2,",",".")."</orderItemPriceNet>\n";
		$xmldata.="			<orderItemPriceCOS_M>".number_format($cos_m, 2,",",".")."</orderItemPriceCOS_M>\n";
		$xmldata.="			<orderItemCollateralGross>".number_format($orderItemCollateralGross, 2,",",".")."</orderItemCollateralGross>\n";
		$xmldata.="			<orderItemCollateralNet>".number_format($orderItemCollateralNet, 2,",",".")."</orderItemCollateralNet>\n";
		$xmldata.="			<orderItemPriceTaxFC>".number_format($orderItemPriceTaxFC, 2,",",".")."</orderItemPriceTaxFC>\n";
		$xmldata.="			<orderItemPriceTax>".number_format($orderItemPriceTax, 2,",",".")."</orderItemPriceTax>\n";
		//BESTELLPOSITION SUMME
		$xmldata.="			<orderItemTotalGrossFC>".number_format($orderItemTotalGrossFC, 2,",",".")."</orderItemTotalGrossFC>\n";
		$xmldata.="			<orderItemTotalGross>".number_format($orderItemTotalGross, 2,",",".")."</orderItemTotalGross>\n";
		$xmldata.="			<orderItemTotalNetFC>".number_format($orderItemTotalNetFC, 2,",",".")."</orderItemTotalNetFC>\n";
		$xmldata.="			<orderItemTotalNet>".number_format($orderItemTotalNet, 2,",",".")."</orderItemTotalNet>\n";
		$xmldata.="			<orderItemTotalCollateralGross>".number_format($orderItemTotalCollateralGross, 2,",",".")."</orderItemTotalCollateralGross>\n";
		$xmldata.="			<orderItemTotalCollateralNet>".number_format($orderItemTotalCollateralNet, 2,",",".")."</orderItemTotalCollateralNet>\n";
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
				//$shippingCostsGrossFC+=$order[$k]["shipping_costs"]*$ust;
				$shippingCostsGrossFC+=$order[$k]["shipping_costs"];
				//$shippingCostsGross+=round($order[$k]["shipping_costs"]*$ust*$ex_rate,2);
				$shippingCostsGross+=round($order[$k]["shipping_costs"]*$ex_rate,2);
				$shippingCostsNetFC+=$order[$k]["shipping_costs"];
				$shippingCostsNet+=round($order[$k]["shipping_costs"]*$ex_rate,2);
			}
			$shippingCostsTaxFC+=$shippingCostsGrossFC-$shippingCostsNetFC;
			$shippingCostsTax+=$shippingCostsGross-$shippingCostsNet;
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

				$shippingCostsGrossFC+=$order[$k]["shipping_costs"];
				$shippingCostsGross+=round($order[$k]["shipping_costs"]*$ex_rate,2);
				$shippingCostsNetFC+=$order[$k]["shipping_costs"]/$ust;
				$shippingCostsNet+=round($order[$k]["shipping_costs"]/$ust*$ex_rate,2);
			}
			$shippingCostsTaxFC=$shippingCostsGrossFC-$shippingCostsNetFC;
			$shippingCostsTax=$shippingCostsGross-$shippingCostsNet;
		}
		
		$collateralTax=$orderItemsTotalCollateralGross-$orderItemsTotalCollateralNet;
		
		$orderTotalNetFC=$orderItemsTotalNetFC+$orderItemsTotalCollateralNet+$shippingCostsNetFC;
		$orderTotalNet=$orderItemsTotalNet+$orderItemsTotalCollateralNet+$shippingCostsNet;
		$orderTotalTaxFC=$orderItemsTotalTaxFC+$collateralTax+$shippingCostsTaxFC;
		$orderTotalTax=$orderItemsTotalTax+$collateralTax+$shippingCostsTax;
		//$orderTotalGross=$orderItemsTotalGross+$orderItemsTotalCollateralGross+$shippingCostsGross+$orderTotalTax;
		$orderTotalGrossFC=$orderItemsTotalGrossFC+$orderItemsTotalCollateralGross+$shippingCostsGrossFC;
		$orderTotalGross=$orderItemsTotalGross+$orderItemsTotalCollateralGross+$shippingCostsGross;
		
		$xmldata.="	</OrderItems>\n";
		
			//SHOWS EACH ORDER (OF COMBINED)
		$xmldata.="	<orders>\n";
		for ($k=0; $k<sizeof($order); $k++)
		{
			$xmldata.="	<order>\n";
			$xmldata.="		<orderid><![CDATA[".$order[$k]["id_order"]."]]></orderid>\n";
			$xmldata.="		<foreign_OrderID><![CDATA[".$order[$k]["foreign_OrderID"]."]]></foreign_OrderID>\n";
			$xmldata.="		<combined_with><![CDATA[".$order[$k]["combined_with"]."]]></combined_with>\n";
			$xmldata.="		<shipping_type_id><![CDATA[".$order[$k]["shipping_type_id"]."]]></shipping_type_id>\n";
			$xmldata.="		<shipping_title><![CDATA[".$shipping_title[$k]."]]></shipping_title>\n";
			$xmldata.="		<shipping_number><![CDATA[".$order[$k]["shipping_number"]."]]></shipping_number>\n";
			$xmldata.="		<shipping_netFC><![CDATA[".number_format($order[$k]["shipping_net"], 2,",","")."]]></shipping_netFC>\n";
			$xmldata.="		<shipping_costsFC><![CDATA[".number_format($order[$k]["shipping_costs"], 2,",","")."]]></shipping_costsFC>\n";
			if (isset($exchangerates[$order[$k]["Currency_Code"]]))
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

		
		$xmldata.="	<shippingCostsGrossFC>".number_format($shippingCostsGrossFC, 2,",","")."</shippingCostsGrossFC>\n";
		$xmldata.="	<shippingCostsGross>".number_format($shippingCostsGross, 2,",","")."</shippingCostsGross>\n";
		$xmldata.="	<shippingCostsNetFC>".number_format($shippingCostsNetFC, 2,",","")."</shippingCostsNetFC>\n";
		$xmldata.="	<shippingCostsNet>".number_format($shippingCostsNet, 2,",","")."</shippingCostsNet>\n";
		$xmldata.="	<shippingCostsTaxFC>".number_format($shippingCostsTaxFC, 2,",","")."</shippingCostsTaxFC>\n";
		$xmldata.="	<shippingCostsTax>".number_format($shippingCostsTax, 2,",","")."</shippingCostsTax>\n";
		
		$xmldata.="	<orderItemsTotalGrossFC>".number_format($orderItemsTotalGrossFC, 2,",","")."</orderItemsTotalGrossFC>\n";
		$xmldata.="	<orderItemsTotalGross>".number_format($orderItemsTotalGross, 2,",","")."</orderItemsTotalGross>\n";
		$xmldata.="	<orderItemsTotalNetFC>".number_format($orderItemsTotalNetFC, 2,",","")."</orderItemsTotalNetFC>\n";
		$xmldata.="	<orderItemsTotalNet>".number_format($orderItemsTotalNet, 2,",","")."</orderItemsTotalNet>\n";
		$xmldata.="	<orderItemsTotalCollateralGross>".number_format($orderItemsTotalCollateralGross, 2,",","")."</orderItemsTotalCollateralGross>\n";
		$xmldata.="	<orderItemsTotalCollateralNet>".number_format($orderItemsTotalCollateralNet, 2,",","")."</orderItemsTotalCollateralNet>\n";
		$xmldata.="	<orderItemsTotalTaxFC>".number_format($orderItemsTotalTaxFC, 2,",","")."</orderItemsTotalTaxFC>\n";
		$xmldata.="	<orderItemsTotalTax>".number_format($orderItemsTotalTax, 2,",","")."</orderItemsTotalTax>\n";
		
		$xmldata.="	<orderTotalGrossFC>".number_format($orderTotalGrossFC, 2,",","")."</orderTotalGrossFC>\n";
		$xmldata.="	<orderTotalGross>".number_format($orderTotalGross, 2,",","")."</orderTotalGross>\n";
		$xmldata.="	<orderTotalNetFC>".number_format($orderTotalNetFC, 2,",","")."</orderTotalNetFC>\n";
		$xmldata.="	<orderTotalNet>".number_format($orderTotalNet, 2,",","")."</orderTotalNet>\n";
		$xmldata.="	<orderTotalTaxFC>".number_format($orderTotalTaxFC, 2,",","")."</orderTotalTaxFC>\n";
		$xmldata.="	<orderTotalTax>".number_format($orderTotalTax, 2,",","")."</orderTotalTax>\n";
		
		$xmldata.="	<orderItemCount>".$orderItemCount."</orderItemCount>\n";
		$xmldata.="	<orderCollateralCount>".$collcnt."</orderCollateralCount>\n";
		$xmldata.="	<orderPositions>".mysqli_num_rows($res_order_items)."</orderPositions>\n";
		$xmldata.="	<orderComplete>".$complete."</orderComplete>\n";
		$xmldata.="</Order>\n";

	
//echo "<crm_get_order_detailResponse>\n";
//echo "<Ack>Success</Ack>\n";
	echo $xmldata;
//echo "</crm_get_order_detailResponse>";

?>
