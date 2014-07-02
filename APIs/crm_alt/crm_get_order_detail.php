<?php

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

	//GET ORDERdata
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_order)==0)
	{
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderID konnte keine Bestellung gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
	}
	
	$order=mysql_fetch_array($res_order);
	
	$xmldata.="<Order>\n";
	$xmldata.="	<id_order>".$order["id_order"]."</id_order>\n";
	$xmldata.="	<shop_id>".$order["shop_id"]."</shop_id>\n";
	//$xmldata.="	<foreign_order_id>".$order["foreign_order_id"]."</foreign_order_id>\n";
	$xmldata.="	<foreign_OrderID>".$order["foreign_OrderID"]."</foreign_OrderID>\n";
	$xmldata.="	<usermail><![CDATA[".$order["usermail"]."]]></usermail>\n";
	$xmldata.="	<bill_gender><![CDATA[".$order["bill_gender"]."]]></bill_gender>\n";	
	$xmldata.="	<bill_company><![CDATA[".$order["bill_company"]."]]></bill_company>\n";
	$xmldata.="	<bill_firstname><![CDATA[".$order["bill_firstname"]."]]></bill_firstname>\n";
	$xmldata.="	<bill_lastname><![CDATA[".$order["bill_lastname"]."]]></bill_lastname>\n";
	$xmldata.="	<bill_street><![CDATA[".$order["bill_street"]."]]></bill_street>\n";
	$xmldata.="	<bill_number><![CDATA[".$order["bill_number"]."]]></bill_number>\n";
	$xmldata.="	<bill_additional><![CDATA[".$order["bill_additional"]."]]></bill_additional>\n";
	$xmldata.="	<bill_city><![CDATA[".$order["bill_city"]."]]></bill_city>\n";
	$xmldata.="	<bill_zip><![CDATA[".$order["bill_zip"]."]]></bill_zip>\n";
	$xmldata.="	<bill_country><![CDATA[".$order["bill_country"]."]]></bill_country>\n";
	$xmldata.="	<userphone><![CDATA[".$order["userphone"]."]]></userphone>\n";
	$xmldata.="	<userfax><![CDATA[".$order["userfax"]."]]></userfax>\n";
	$xmldata.="	<usermobile><![CDATA[".$order["usermobile"]."]]></usermobile>\n";
	$xmldata.="	<firstmod>".$order["firstmod"]."</firstmod>\n";
	$xmldata.=" <orderDate>".date("d.m.Y H:i", $order["firstmod"])."</orderDate>\n";

		//USERID - PLATFORM
		$buyerid="";
		if ($order["shop_id"]==3 || $order["shop_id"]==4)
		{
			//$res_buyerid=q("SELECT * FROM ebay_orders2 WHERE id_order = ".$order["foreign_order_id"].";", $dbshop, __FILE__, __LINE__);
			$res_buyerid=q("SELECT * FROM ebay_orders WHERE OrderID = '".$order["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_buyerid)>0)
			{
				$row_buyerid=mysql_fetch_array($res_buyerid);
				$buyerid=$row_buyerid["BuyerUserID"];				
			}
		}
	$xmldata.=" <buyerUserID>".$buyerid."</buyerUserID>\n";
	
	$xmldata.="	<OrderItems>\n";

	//GET ORDER ITEMSdata
	$res_order_items=q("SELECT * FROM shop_orders_items WHERE order_id = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_order_items)==0)
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
	
	$i=0;
	while ($row_order_items=mysql_fetch_array($res_order_items))
	{
		//ITEM DESCRIPTION
		$res_items_desc=q("SELECT * FROM shop_items_de WHERE id_item = ".$row_order_items["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row_items_desc=mysql_fetch_array($res_items_desc);
		
		//MPN
		$res_items_MPN=q("SELECT MPN FROM shop_items WHERE id_item = ".$row_order_items["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row_items_MPN=mysql_fetch_array($res_items_MPN);
		
		//ITEMVEHICLE
		if ($row_order_items["customer_vehicle_id"]!=0)
		{
			$res_item_vehicle=q("SELECT * FROM crm_vehicles WHERE id_customer_vehicle = ".$row_order_items["customer_vehicle_id"].";", $dbweb, __FILE__, __LINE__);
			$item_vehicle=mysql_fetch_array($res_item_vehicle);
		}
		
		
		
		$orderItemTotal=$row_order_items["price"]*$row_order_items["amount"];
		$orderItemsTotal+=$orderItemTotal;
		$orderTotalCount+=$row_order_items["amount"];
		
		$xmldata.="		<Item>\n";
		$xmldata.="			<OrderItemID>".$row_order_items["id"]."</OrderItemID>\n";
		$xmldata.="			<OrderItemItemID>".$row_order_items["item_id"]."</OrderItemItemID>\n";
		$xmldata.="			<OrderItemMPN>".$row_items_MPN["MPN"]."</OrderItemMPN>\n";
		$xmldata.="			<OrderItemDesc><![CDATA[".$row_items_desc["title"]."]]></OrderItemDesc>\n";
		$xmldata.="			<OrderItemAmount>".$row_order_items["amount"]."</OrderItemAmount>\n";
		$xmldata.="			<OrderItemPrice>".$row_order_items["price"]."</OrderItemPrice>\n";
		$xmldata.="			<OrderItemNetto>".$row_order_items["netto"]."</OrderItemNetto>\n";
		$xmldata.="			<OrderItemTotal>".number_format($orderItemTotal, 2,",",".")."</OrderItemTotal>\n";
				
		if (isset($item_vehicle))
		{
			$xmldata.="			<OrderItemVehicleID>".$item_vehicle["vehicle_id"]."</OrderItemVehicleID>\n";
			$xmldata.="			<OrderItemCustomerVehicleID>".$item_vehicle["id_customer_vehicle"]."</OrderItemCustomerVehicleID>\n";
			$xmldata.="			<OrderItemVehicleHSN>".$item_vehicle["HSN"]."</OrderItemVehicleHSN>\n";
			$xmldata.="			<OrderItemVehicleTSN>".$item_vehicle["TSN"]."</OrderItemVehicleTSN>\n";
			$xmldata.="			<OrderItemVehicleDateBuilt>".$item_vehicle["DateBuilt"]."</OrderItemVehicleDateBuilt>\n";
			$xmldata.="			<OrderItemVehicleFIN>".$item_vehicle["FIN"]."</OrderItemVehicleFIN>\n";
			$xmldata.="			<OrderItemVehicleAdditional><![CDATA[".$item_vehicle["additional"]."]]></OrderItemVehicleAdditional>\n";
		}
		
		$xmldata.="		</Item>\n";

		unset($item_vehicle);
	}
		$xmldata.="	</OrderItems>\n";
		$xmldata.="	<OrderShippingCosts>".number_format($orders[$k]["shipping_costs"], 2,",",".")."</OrderShippingCosts>\n";
		$xmldata.="	<OrderItemsTotal>".number_format($orderItemsTotal, 2,",",".")."</OrderItemsTotal>\n";
		$xmldata.="	<OrderTotal>".number_format($orderItemsTotal+$orders[$k]["shipping_costs"], 2,",",".")."</OrderTotal>\n";
		$xmldata.="	<OrderTotalCount>".$orderTotalCount."</OrderTotalCount>\n";
		$xmldata.="</Order>\n";

	
echo "<crm_get_order_detailResponse>\n";
echo "<Ack>Success</Ack>\n";
	echo $xmldata;
echo "</crm_get_order_detailResponse>";

?>
