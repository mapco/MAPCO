<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	$start=time();


	$dbAP=mysqli_connect("localhost", "ihrautop_1", "NSuMy57N", "ihrautop_db1");
	q("SET NAMES utf8", $dbAP, __FILE__, __LINE__);

	$res_orders=q("select * from shop_orders;", $dbAP, __FILE__, __LINE__);
	while ($row_orders=mysqli_fetch_array($res_orders))
	{
		$orders[$row_orders["id_order"]]=$row_orders;
		$orderIDs[$row_orders["id_order"]]="";
	}
	

	while ( list ($key, $val) = each ($orderIDs))
	{
		q("INSERT INTO shop_orders_crm (status_id, shop_id, foreign_order_id, customer_id, ordernr, comment, usermail, userphone, userfax, usermobile, bill_company, bill_gender, bill_title, bill_firstname, bill_lastname, bill_zip, bill_city, bill_street, bill_number, bill_additional, bill_country, ship_company, ship_gender, ship_title, ship_firstname, ship_lastname, ship_zip, ship_city, ship_street, ship_number, ship_additional, ship_country, shipping_costs, shipping_details, Payments_TransactionID, Payments_TransactionState, Payments_TransactionStateDate, Payments_Type, PayPal_PendingReason, PayPal_BuyerNote, partner_id, bill_adr_id, ship_adr_id, firstmod, firstmod_user, lastmod, lastmod_user, username, password, shipping_net) VALUES('".$orders[$key]["status_id"]."', 2, '".$orders[$key]["id_order"]."', 0, '".mysqli_real_escape_string($dbshop, $orders[$key]["ordernr"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["comment"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["usermail"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["userphone"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["userfax"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["usermobile"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_company"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_gender"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_title"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_firstname"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_lastname"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_zip"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_city"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_street"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_number"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_additional"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["bill_country"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_company"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_gender"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_title"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_firstname"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_lastname"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_zip"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_city"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_street"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_number"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_additional"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ship_country"])."', '".$orders[$key]["shipping_costs"]."', '".mysqli_real_escape_string($dbshop, $orders[$key]["shipping_details"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["Payments_TransactionID"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["Payments_TransactionState"])."', ".$orders[$key]["Payments_TransactionStateDate"].", '".mysqli_real_escape_string($dbshop, $orders[$key]["Payments_Type"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["PayPal_PendingReason"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["PayPal_BuyerNote"])."', ".$orders[$key]["partner_id"].", 0, 0, ".$orders[$key]["firstmod"].", ".$orders[$key]["firstmod_user"].", ".$orders[$key]["lastmod"].", ".$orders[$key]["lastmod_user"].", '', '', 0);", $dbshop, __FILE__, __LINE__);

		$order_id=mysqli_insert_id($dbshop);
echo $order_id."<br />";
		$res_orders_items=q("select * from shop_orders_items where order_id = ".$key.";", $dbAP, __FILE__, __LINE__);
		while ($row_orders_items=mysqli_fetch_array($res_orders_items))
		{
			q("INSERT INTO shop_orders_items_crm (order_id, item_id, amount, price, netto) VALUES (".$order_id.", ".$row_orders_items["item_id"].", ".$row_orders_items["amount"].", ".$row_orders_items["price"].", 0);", $dbshop, __FILE__, __LINE__);
	
		}
		

	}

//EBAY ORDERS

	$res_orders=q("SELECT * FROM ebay_orders;", $dbshop, __FILE__, __LINE__);
	while($row_orders=mysqli_fetch_array($res_orders))
	{
		$orders[$row_orders["id_order"]]=$row_orders;
		$orderIDs[$row_orders["id_order"]]="";
	}
	
	
	$usermail = array();
	$res_usermail=q("select BuyerEmail, OrderID from ebay_orders_items;",  $dbshop, __FILE__, __LINE__);
	while ($row_usermail=mysqli_fetch_array($res_usermail))
	{
		if (strpos($row_usermail["OrderID"],"-")>0)
		{
			$orderid=substr($row_usermail["OrderID"], strpos($row_usermail["OrderID"],"-")+1);
		}
		else
		{
			$orderid=$row_usermail["OrderID"];
		}
		if (!isset($usermail[$orderid])) $usermail[$orderid]=$row_usermail["BuyerEmail"];
	}
		
	$res_shop_items=q("SELECT id_item, MPN FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while($row_shop_items=mysqli_fetch_array($res_shop_items))
	{	
		$shop_items[$row_shop_items["MPN"]]=$row_shop_items["id_item"];
	}
	
	
	$nullcouter=0;
	while ( list ($key, $val) = each ($orderIDs))
	{
		if (strpos($orders[$key]["OrderID"],"-")>0)
		{
			$orderid=substr($orders[$key]["OrderID"], strpos($orders[$key]["OrderID"],"-")+1);
		}
		else
		{
			$orderid=$orders[$key]["OrderID"];
		}

		if (isset($usermail[$orderid]))
		{
			$tmp_usermail=$usermail[$orderid];
		}
		else
		{
			$tmp_usermail="";
			echo "Usermail für OrderID ".$orderid." nicht gefunden <br />";
		}
		
		$shipping_details=$orders[$key]["ShippingServiceSelectedShippingService"].", ".$orders[$key]["CheckoutStatusPaymentMethod"];
		
		$payment_date=$orders[$key]["PaidTime"];
		$paymentdate=mktime(substr($payment_date, 11,2)*1, substr($payment_date, 13,2)*1, substr($payment_date, 15,2)*1, substr($payment_date, 5,2)*1, substr($payment_date, 8,2)*1, substr($payment_date, 0,4)*1);

		
		q("INSERT INTO shop_orders_crm (status_id, shop_id, foreign_order_id, customer_id, ordernr, comment, usermail, userphone, userfax, usermobile, bill_company, bill_gender, bill_title, bill_firstname, bill_lastname, bill_zip, bill_city, bill_street, bill_number, bill_additional, bill_country, ship_company, ship_gender, ship_title, ship_firstname, ship_lastname, ship_zip, ship_city, ship_street, ship_number, ship_additional, ship_country, shipping_costs, shipping_details, Payments_TransactionID, Payments_TransactionState, Payments_TransactionStateDate, Payments_Type, PayPal_PendingReason, PayPal_BuyerNote, partner_id, bill_adr_id, ship_adr_id, firstmod, firstmod_user, lastmod, lastmod_user, username, password, shipping_net) VALUES(1, ".($orders[$key]["account_id"]+2)." , '".$orders[$key]["id_order"]."', 0, '', '', '".mysqli_real_escape_string($dbshop, $tmp_usermail)."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ShippingAddressPhone"])."', '','','','','','', '".mysqli_real_escape_string($dbshop, $orders[$key]["ShippingAddressName"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ShippingAddressPostalCode"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ShippingAddressCityName"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ShippingAddressStreet1"])."', '', '".mysqli_real_escape_string($dbshop, $orders[$key]["ShippingAddressStreet2"])."', '".mysqli_real_escape_string($dbshop, $orders[$key]["ShippingAddressCountryName"])."', '', '', '', '', '', '', '', '', '', '', '', ".$orders[$key]["ShippingServiceSelectedShippingServiceCost"].", '".mysqli_real_escape_string($dbshop, $shipping_details)."', '', '".mysqli_real_escape_string($dbshop, $orders[$key]["CheckoutStatusStatus"])."', ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $orders[$key]["CheckoutStatusPaymentMethod"])."', '', '', 0, 0, 0, ".$orders[$key]["CreatedTimeTimestamp"].", ".$orders[$key]["firstmod_user"].", ".$orders[$key]["lastmod"].", ".$orders[$key]["lastmod_user"].", '', '', 0);", $dbshop, __FILE__, __LINE__);

		$order_id=mysqli_insert_id($dbshop);
echo $order_id."<br />";
		//ORDER ITEMS
		if (strpos($orders[$key]["OrderID"], "-")>0)
		{
			$idorder=substr($orders[$key]["OrderID"], strpos($orders[$key]["OrderID"], "-")+1);
			$res_orders_items=q("select * from ebay_orders_items where TransactionID = ".$idorder.";", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$idorder=$orders[$key]["OrderID"];
			$res_orders_items=q("select * from ebay_orders_items where OrderID = ".$idorder.";", $dbshop, __FILE__, __LINE__);
		}

		if (mysqli_affected_rows()==0) $nullcouter++;

		while ($row_orders_items=mysqli_fetch_array($res_orders_items))
		{
			q("INSERT INTO shop_orders_items_crm (order_id, item_id, amount, price, netto) VALUES (".$order_id.", ".$shop_items[$row_orders_items["ItemSKU"]].", ".$row_orders_items["QuantityPurchased"].", ".$row_orders_items["TransactionPrice"].", 0);", $dbshop, __FILE__, __LINE__);
		}
		
		//PAYMENTS
		//check for IPN
		$res_ipn=q("SELECT * FROM payment_notifications where orderID = '".$idorder."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ipn)>0)
		{
			while ($row_ipn=mysqli_fetch_array($res_ipn))
			{
				if ($row_ipn["state"]!="Refunded") $paymenttype="paymentrecieved"; else $paymenttype="paymentsent";
				
				$eventmsg ='<Payment>';
				$eventmsg.='<PaymentMethod><![CDATA['.$row_ipn["payment_type"].']]></PaymentType>';
				$eventmsg.='<PaymentType><![CDATA['.$paymenttype.']]></PaymentType>';
				$eventmsg.='<PaymentState><![CDATA['.$row_ipn["state"].']]></PaymentState>';
				$eventmsg.='<PaymentTime><![CDATA['.$row_ipn["payment_date"].']]></PaymentTime>';
				$eventmsg.='</Payment>';
				
				q("INSERT INTO shop_orders_events (order_id, event, message, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$order_id.", 'Payment', '".mysqli_real_escape_string($dbshop, $eventmsg)."', ".time().", 1, ".time().", 1);", $dbshop, __FILE__, __LINE__);
				
			}
		}
		else 
		{
			$paymenttype="";
			$paymentmethod="";
			$paymentstate="";
			$paymentdate=0;
//			if ($orders[$key]["CheckoutStatusStatus"]=="Complete") { $paymenttype="paymentrecieved";}
			if ($orders[$key]["CheckoutStatusPaymentMethod"]=="MoneyXferAcceptedInCheckout") $paymentmethod="BankTransfer";
			if ($orders[$key]["CheckoutStatusPaymentMethod"]=="PayPal") $paymentmethod="PayPal";
			if ($orders[$key]["CheckoutStatusPaymentMethod"]=="CashOnPickup") $paymentmethod="Cash";
			if ($orders[$key]["PaidTime"]!="") {
				$paymenttype="paymentrecieved";
				$paymentstate="Completed";
				$payment_date=$orders[$key]["PaidTime"];
				$paymentdate=mktime(substr($payment_date, 11,2)*1, substr($payment_date, 13,2)*1, substr($payment_date, 15,2)*1, substr($payment_date, 5,2)*1, substr($payment_date, 8,2)*1, substr($payment_date, 0,4)*1);
			}
			if ($paymenttype!="" && $paymentmethod!="" && $paymentstate!="" && $paymentdate!=0)
			{
				$eventmsg ='<Payment>';
				$eventmsg.='<PaymentMethod><![CDATA['.$paymentmethod.']]></PaymentMethod>';
				$eventmsg.='<PaymentType><![CDATA['.$paymenttype.']]></PaymentType>';
				$eventmsg.='<PaymentState><![CDATA['.$paymentstate.']]></PaymentState>';
				$eventmsg.='<PaymentTime><![CDATA['.$paymentdate.']]></PaymentTime>';
				$eventmsg.='</Payment>';
				
				q("INSERT INTO shop_orders_events (order_id, event, message, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$order_id.", 'Payment', '".mysqli_real_escape_string($dbshop, $eventmsg)."', ".time().", 1, ".time().", 1);", $dbshop, __FILE__, __LINE__);
			}
		}

		//SHIPMENT
		if ($orders[$key]["ShippedTime"]!="")
		{
			$shipping_date=$orders[$key]["ShippedTime"];
			$shippingdate=mktime(substr($shipping_date, 11,2)*1, substr($shipping_date, 13,2)*1, substr($shipping_date, 15,2)*1, substr($shipping_date, 5,2)*1, substr($shipping_date, 8,2)*1, substr($shipping_date, 0,4)*1);
			
			$shipping_service=$orders[$key]["ShippingServiceSelectedShippingService"];
			
			if (strpos($orders[$key]["OrderID"], "-")>0)
			{
				$idorder=substr($orders[$key]["OrderID"], strpos($orders[$key]["OrderID"], "-")+1);
				$res_orders_items=q("select * from ebay_orders_items where TransactionID = ".$idorder.";", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$idorder=$orders[$key]["OrderID"];
				$res_orders_items=q("select * from ebay_orders_items where OrderID = ".$idorder.";", $dbshop, __FILE__, __LINE__);
			}
	
			$shippingitems="";
			while ($row_orders_items=mysqli_fetch_array($res_orders_items))
			{
				$shippingitems.="<shippingItem>";
				$shippingitems.="<ItemID>".$shop_items[$row_orders_items["ItemSKU"]]."</ItemID>";
				$shippingitems.="<ItemAmount>".$row_orders_items["QuantityPurchased"]."</ItemAmount>";
				$shippingitems.="</shippingItem>";
			}
			
			$eventmsg ='<ShipmentAssigned>';
			$eventmsg.='<ShippedPartially>No</ShippedPartially>';
			$eventmsg.='<ShippingService><![CDATA['.$shipping_service.']]></ShippingService>';
			$eventmsg.='<ShippedItems>';
			$eventmsg.=$shippingitems;			
			$eventmsg.='</ShippedItems>';
			$eventmsg.='<ShipmentAssignedTime>'.$shippingdate.'</ShipmentAssignedTime>';
			$eventmsg.='<IDIMS_AUF_ID>0</IDIMS_AUF_ID>';
			$eventmsg.='</ShipmentAssigned>';
			
			q("INSERT INTO shop_orders_events (order_id, event, message, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$order_id.", 'ShipmentAssigned', '".mysqli_real_escape_string($dbshop, $eventmsg)."', ".time().", 1, ".time().", 1);", $dbshop, __FILE__, __LINE__);

			$eventmsg ='<ShipmentExecuted>';
			$eventmsg.='<IDIMS_AUF_ID>0</IDIMS_AUF_ID>';
			$eventmsg.='<ShippingService><![CDATA['.$shipping_service.']]></ShippingService>';
			$eventmsg.='<ShipmentExecuteTime>'.$shippingdate.'</ShipmentExecuteTime>';
			$eventmsg.='</ShipmentExecuted>';

			q("INSERT INTO shop_orders_events (order_id, event, message, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$order_id.", 'ShipmentExecuted', '".mysqli_real_escape_string($dbshop, $eventmsg)."', ".time().", 1, ".time().", 1);", $dbshop, __FILE__, __LINE__);
			
		}
		
	}

echo "fertig: ".$nullcouter;
echo "<br />Laufzeit: ".(time()-$start);
?>