<?php

	check_man_params(array("OrderID" =>"numericNN"));
	
	include("../functions/mapco_gewerblich.php");
	
	$id_order = $_POST["OrderID"];
	
	//$select_fields = 'id_order, shop_id, ordertype_id, VAT, customer_id, Currency_Code, usermail, shipping_costs, shipping_type_id, shipping_details, kunid, adrid, combined_with, firstmod, invoice_id, invoice_nr, payments_type_id, shipping_net, bill_adr_id, ship_adr_id';
	
	$select_fields = 'id_order, shop_id, ordertype_id, VAT, Currency_Code, shipping_costs, shipping_type_id, kunid, adrid, combined_with, firstmod, shipping_net, bill_adr_id, ship_adr_id, customer_id';

	//GET ORDERdata	
	$sql = 'SELECT '.$select_fields.' FROM shop_orders WHERE id_order='.$id_order;
	$result=q($sql, $dbshop, __FILE__, __LINE__);
	$order=mysqli_fetch_assoc($result);

	//GET CMS_USER DATA
	if ($order["customer_id"]!=0)
	{ 
		$result=q("SELECT username AS customer_username, name AS customer_name, firstname AS customer_firstname, lastname AS customer_lastname FROM cms_users WHERE id_user = ".$order["customer_id"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_assoc($result);
		$order=array_merge($order,$row);
	}
	
	//BILL ADDRESS FROM shop_bill_adr
	if ($order["bill_adr_id"]!=0)
	{		
		$adr_fields = 'company AS bill_adr_company, firstname AS bill_adr_firstname, lastname AS bill_adr_lastname, street AS bill_adr_street, number AS bill_adr_number, additional AS bill_adr_additional, city AS bill_adr_city, zip AS bill_adr_zip, country AS bill_adr_country, country_id AS bill_adr_country_id';
		$result=q("SELECT ".$adr_fields." FROM shop_bill_adr WHERE user_id = ".$order["customer_id"]." AND adr_id = ".$order["bill_adr_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($result)>0)
		{
			$row=mysqli_fetch_assoc($result);
			$order=array_merge($order,$row);
		}
	}

	//SHIP ADDRESS FROM shop_bill_adr
	if ($order["ship_adr_id"]!=0)
	{
		$adr_fields = 'company AS ship_adr_company, firstname AS ship_adr_firstname, lastname AS ship_adr_lastname, street AS ship_adr_street, number AS ship_adr_number, additional AS ship_adr_additional, city AS ship_adr_city, zip AS ship_adr_zip, country AS ship_adr_country, country_id AS ship_adr_country_id';
		$result=q("SELECT ".$adr_fields." FROM shop_bill_adr WHERE user_id = ".$order["customer_id"]." AND adr_id = ".$order["ship_adr_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($result)>0)
		{
			$row=mysqli_fetch_assoc($result);
			$order=array_merge($order,$row);
		}
	}
	
	//GET SHOPS
	$shop=array();
	$result=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($result))
	{
		$shop[$row["id_shop"]]=$row;
	}
	
	//GET EXCHANGERATE
	$exchange_rate=array();
	$result=q("SELECT * FROM shop_currencies;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($result))
	{
		$exchange_rates[$row["currency_code"]]=$row["exchange_rate_to_EUR"];
	}
	
	//SET ACTUAL EXCHANGERATE
	$exchangerates=array();
	$result=q("SELECT * FROM shop_currencies WHERE currency_code = '".$order["Currency_Code"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($result)==1)
	{
		$row=mysqli_fetch_array($result);
		$exchangerates[0]=1/$row["exchange_rate_to_EUR"];
	}
	
	//UMSATZSTEUER FESTLEGEN
	if ($order["VAT"]==0)
	{
		$ust = 1;
	}
	else
	{
		$ust = 	($order["VAT"]/100)+1;	
	}

	$sql = "SELECT id, order_id, foreign_TransactionID, item_id, amount, price, exchange_rate_to_EUR, netto, Currency_Code FROM shop_orders_items WHERE order_id=".$order['id_order'].";";
	
	$TmpTotalGrossFC=0;
	$TmpTotalGross=0;
	$TmpTotalNetFC=0;
	$TmpTotalNet=0;
	$TmpTotalTaxFC=0;
	$TmpTotalTax=0;
	
	//GET ORDER ITEMSdata
	$res_order_items=q($sql, $dbshop, __FILE__, __LINE__);
	$z=0;
	$orderitems = array();
	while ($row_order_items=mysqli_fetch_assoc($res_order_items))
	{
		$order['items'][$z] = $row_order_items;
	
		$TmpGrossFC=0;
		$TmpGross=0;
		$TmpNetFC=0;
		$TmpNet=0;
		$TmpTaxFC=0;
		$TmpTax=0;
	
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
	
		//ITEM DESCRIPTION
		$result=q("SELECT si.MPN, sid.title FROM shop_items_de AS sid, shop_items AS si WHERE sid.id_item = ".$row_order_items["item_id"]." AND si.id_item=".$row_order_items["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($result);
		
			$order['items'][$z]["MPN"] = $row["MPN"];
			$order['items'][$z]["title"] = $row["title"];
		
		//TRACK EXCHANGERATES FOR EACH ORDER
		$exchangerates[$row_order_items["order_id"]]=$ex_rate;
		
		if ($shop[$order["shop_id"]]["shop_type"]==2 || $shop[$order["shop_id"]]["shop_type"]==3 )
		{
			//ARTIKEL EINZELPREIS
			$TmpGrossFC=$row_order_items["price"];
			$TmpGross=$row_order_items["price"]*$ex_rate;
			$TmpNetFC=$row_order_items["price"]/$ust;
			$TmpNet=($row_order_items["price"]/$ust)*$ex_rate;
		}
		else
		{
			//ARTIKEL EINZELPREIS
			$TmpGrossFC=$row_order_items["netto"]*$ust;
			$TmpGross=$row_order_items["netto"]*$ust*$ex_rate;
			$TmpNetFC=$row_order_items["netto"];
			$TmpNet=$row_order_items["netto"]*$ex_rate;		
		}
		
		$TmpTaxFC=$TmpGrossFC-$TmpNetFC;
		$TmpTax=$TmpGross-$TmpNet;

		$order['items'][$z]['PriceGrossFC']=number_format($TmpGrossFC, 2,",",".");
		$order['items'][$z]['PriceGross']=number_format($TmpGross, 2,",",".");
		$order['items'][$z]['PriceNetFC']=number_format($TmpNetFC, 2,",",".");
		$order['items'][$z]['PriceNet']=number_format($TmpNet, 2,",",".");
		$order['items'][$z]['Tax']=number_format($TmpTax, 2,",",".");
		$order['items'][$z]['TaxFC']=number_format($TmpTaxFC, 2,",",".");
		
				//BESTELLPOSITION SUMME
		$TmpItemTotalGrossFC=$TmpGrossFC*$row_order_items["amount"];		
		$TmpItemTotalGross=$TmpGross*$row_order_items["amount"];
		$TmpItemTotalNetFC=$TmpNetFC*$row_order_items["amount"];
		$TmpItemTotalNet=$TmpNet*$row_order_items["amount"];
		$TmpItemTotalTax=$TmpTax*$row_order_items["amount"];
		$TmpItemTotalTaxFC=$TmpTaxFC*$row_order_items["amount"];
		
		$order['items'][$z]['ItemTotalGrossFC']=number_format($TmpItemTotalGrossFC, 2,",",".");
		$order['items'][$z]['ItemTotalGross']=number_format($TmpItemTotalGross, 2,",",".");
		$order['items'][$z]['ItemTotalNetFC']=number_format($TmpItemTotalNetFC, 2,",",".");
		$order['items'][$z]['ItemTotalNet']=number_format($TmpItemTotalNet, 2,",",".");
		$order['items'][$z]['ItemTotalTax']=number_format($TmpItemTotalTax, 2,",",".");
		$order['items'][$z]['ItemTotalTaxFC']=number_format($TmpItemTotalTaxFC, 2,",",".");
		
		//BESTELLUNGSSUMMEN der Order 
		$TmpOrderTotalGrossFC+=$TmpItemTotalGrossFC;
		$TmpOrderTotalGross+=$TmpItemTotalGross;
		$TmpOrderTotalNetFC+=$TmpItemTotalNetFC;
		$TmpOrderTotalNet+=$TmpItemTotalNet;
		$TmpOrderTotalTaxFC+=$TmpItemTotalTaxFC;
		$TmpOrderTotalTax+=$TmpItemTotalTax;
	
		$order['ItemCount']+=$row_order_items["amount"];
		
		$z++;
	}

	//SHIPPING COSTS
	if ($shop[$order["shop_id"]]["shop_type"]==2 || $shop[$order["shop_id"]]["shop_type"]==3 )
	{
		$shippingCostsGrossFC=$order["shipping_costs"];
		$shippingCostsGross=$order["shipping_costs"]*$exchangerates[$order["id_order"]];
		$shippingCostsNetFC=$order["shipping_costs"]/$ust;
		$shippingCostsNet=$order["shipping_costs"]/$ust*$exchangerates[$order["id_order"]];
	}
	else
	{
		$shippingCostsGrossFC=$order["shipping_net"]*$ust;
		$shippingCostsGross=$order["shipping_net"]*$ust*$exchangerates[$order["id_order"]];
		$shippingCostsNetFC=$order["shipping_net"];
		$shippingCostsNet=$order["shipping_net"]*$exchangerates[$order["id_order"]];
	}
	$shippingCostsTaxFC+=$order['shippingCostsGrossFC']-$order['shippingCostsNetFC'];
	$shippingCostsTax+=$order['shippingCostsGross']-$order['shippingCostsNet'];
	
	$order['shippingCostsGrossFC']=number_format($shippingCostsGrossFC, 2,",",".");
	$order['shippingCostsGross']=number_format($shippingCostsGross, 2,",",".");
	$order['shippingCostsNetFC']=number_format($shippingCostsNetFC, 2,",",".");
	$order['shippingCostsNet']=number_format($shippingCostsNet, 2,",",".");	
	$order['shippingCostsTaxFC']=number_format($shippingCostsTaxFC, 2,",",".");
	$order['shippingCostsTax']=number_format($shippingCostsTax, 2,",",".");

	$order['TotalNetFC']=number_format($TmpOrderTotalNetFC+$shippingCostsNetFC, 2,",",".");
	$order['TotalNet']=number_format($TmpOrderTotalNet+$shippingCostsNet, 2,",",".");
	$order['TotalTaxFC']=number_format($TmpOrderTotalTaxFC+$shippingCostsTaxFC, 2,",",".");
	$order['TotalTax']=number_format($TmpOrderTotalTax+$shippingCostsTax, 2,",",".");
	$order['TotalGrossFC']=number_format($TmpOrderTotalGrossFC+$shippingCostsGrossFC, 2,",",".");
	$order['TotalGross']=number_format($TmpOrderTotalGross+$shippingCostsGross, 2,",",".");

	$xmldata = '<orderdetails>'."\n";
	foreach($order as $okey => $ovalue)
	{
		if($okey == 'items')
		{
			$xmldata .= '	<orderitems>'."\n";
			for($y=0;$y<sizeof($order['items']);$y++)
			{		
				$xmldata .= '		<item>'."\n";	
				foreach( $order['items'][$y] as $ikey => $ivalue)
				{	
					$xmldata .="			<".$ikey.">".$ivalue."</".$ikey.">\n";
				}
				$xmldata .= '		</item>'."\n";
			}
			$xmldata .= '	</orderitems>'."\n";
		}
		else
		{
			$xmldata .="	<".$okey.">".$ovalue."</".$okey.">\n";
		}
	}

	$xmldata .= '</orderdetails>'."\n";

	//SERVICE RESPONSE
	echo $xmldata;
?>