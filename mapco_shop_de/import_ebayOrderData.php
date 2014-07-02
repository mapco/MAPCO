<?php
include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

$res_so=q("SELECT * FROM shop_orders;" , $dbshop, __FILE__, __LINE__);
while ($row_so=mysqli_fetch_array($res_so))
{ 
	$shop_orders[$row_so["foreign_OrderID"]]=0;
}

$res=q("SELECT * FROM ebay_orders WHERE lastmod > 1371448954; ", $dbshop, __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res))
{
	
/*
	if ( !isset($_POST["mode"]) )
	{
		echo '<crm_add_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listentitel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel für anzulegende Liste angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["EbayOrderID"]) )
	{
		echo '<crm_add_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listentitel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel für anzulegende Liste angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		exit;
	}
*/
	$res_orders=q("SELECT * FROM ebay_orders WHERE id_order = ".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_orders)==0)
	{
		echo '<crm_add_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listentitel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel für anzulegende Liste angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		exit;
	}
	
	$ebay_orders=mysqli_fetch_array($res_orders);
	$res_orders_items=q("SELECT * FROM ebay_orders_items WHERE OrderID = '".$ebay_orders["OrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_orders_items)==0)
	{
		echo '<crm_add_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listentitel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel für anzulegende Liste angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		exit;
	}
	$i=0;
	$ebay_orders_items=array();
	while ($row_orders_items=mysqli_fetch_array($res_orders_items))
	{
		$ebay_orders_items[$i]=$row_orders_items;
		$i++;
	}
	
	if ($ebay_orders["account_id"]==1) $crm_account_id=3;
	if ($ebay_orders["account_id"]==2) $crm_account_id=4;
	
	$payment_date=$ebay_orders["PaidTime"];
	//$paymentdate=mktime(substr($payment_date, 11,2)*1, substr($payment_date, 13,2)*1, substr($payment_date, 15,2)*1, substr($payment_date, 5,2)*1, substr($payment_date, 8,2)*1, substr($payment_date, 0,4)*1);
	if ($ebay_orders["PaidTime"]=="")
	{
		$paymentdate=0;
	}
	else 
	{
		$paymentdate=strtotime($ebay_orders["PaidTime"]);
	}

	$shipping_details=$ebay_orders["ShippingServiceSelectedShippingService"].", ".$ebay_orders["CheckoutStatusPaymentMethod"];

	$usermail=$ebay_orders_items[0]["BuyerEmail"];

	
	if (strpos($ebay_orders["ShippingAddressName"]," ")===false)
	{
		$bill_firstname=substr($ebay_orders["ShippingAddressName"], 0, strpos($ebay_orders["ShippingAddressName"],"."));
		
		$bill_lastname=substr($ebay_orders["ShippingAddressName"], strpos($ebay_orders["ShippingAddressName"],".")+1);
	}
	else
	{
		$bill_firstname=substr($ebay_orders["ShippingAddressName"], 0, strpos($ebay_orders["ShippingAddressName"]," "));
		
		$bill_lastname=substr($ebay_orders["ShippingAddressName"], strpos($ebay_orders["ShippingAddressName"]," ")+1);
	}
	
	if ($bill_firstname=="")
	{
		$bill_lastname=$ebay_orders["ShippingAddressName"];
	}
		
	$has_number=false;		
	$pos=0;
	for ($i=strlen($ebay_orders["ShippingAddressStreet1"])-1; $i>-1; $i--)
	{
		if ((is_numeric(substr($ebay_orders["ShippingAddressStreet1"],$i, 1)) || substr($ebay_orders["ShippingAddressStreet1"],$i, 1)=="/") && $pos==0)
		{
			if (!$has_number) $has_number=true;
		}
		else
		{
			if ($has_number && $pos==0) $pos=$i;
		}
	}
	if($pos==0)
	{
		$bill_street1=$ebay_orders["ShippingAddressStreet1"];
		$bill_streetNumber="0";
	}
	else
	{
		$bill_street1=trim(substr($ebay_orders["ShippingAddressStreet1"], 0, $pos+1));	
		$bill_streetNumber=trim(substr($ebay_orders["ShippingAddressStreet1"], $pos+1));
	}

	//GET SHIPPING TYPES
	$shop_shipping_type=array();
	$res_shiptype=q("SELECT * FROM shop_shipping_types;", $dbshop, __FILE__, __LINE__);
	while ($row_shiptype=mysqli_fetch_array($res_shiptype))
	{
		$shop_shipping_type[$row_shiptype["ShippingServiceType"]]=$row_shiptype["id_shippingtype"];
	}

	if (isset($shop_shipping_type[$ebay_orders["ShippingServiceSelectedShippingService"]]))
	{
		$shippingType=$shop_shipping_type[$ebay_orders["ShippingServiceSelectedShippingService"]];
	}
	else
	{
		$shippingType=0;
	}
	
	$payments_type_id="";
	//GET PAYMENTS Types
	$res_payments=q("SELECT * FROM shop_payment_types WHERE PaymentMethod = '".$ebay_orders["CheckoutStatusPaymentMethod"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_payments)>0)
	{
		$row_payments=mysqli_fetch_array($res_payments);
		$payments_type_id=$row_payments["id_paymenttype"];
	}
	else
	{
		$payments_type_id=0;
	}
	
//*****************************************************************
// ADD
//*****************************************************************
echo $ebay_orders["OrderID"]."<br />";	
if ($ebay_orders["OrderID"]!="370799011937-468141826024"){

if (!isset($shop_orders[$ebay_orders["OrderID"]]))

//	if ($_POST["mode"]=="add")

	{
echo $ebay_orders["OrderID"]."<br />";		
	//CHECK IF CUSTOMER IS KNOWN
		$crm_customer_id=0;
		//CHECK FOR KNOWN ACCOUNT ID
		$user_account_id=0;
		$res_check=q("SELECT * FROM crm_customer_accounts2 WHERE account_user_id = '".$ebay_orders["BuyerUserID"]."' AND (account = 3 OR account = 4);", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)>0)
		{
			$row_check=mysqli_fetch_array($res_check);
			$user_account_id=$row_check["id_customer_account"];
			$crm_customer_id=$row_check["crm_customer_id"];
		}

		//CHECK FOR KNOWN EMAIL
		$number_id=0;
		$number_account_id=0;
		if ($usermail!="Invalid Request")
		{
			$res_check=q("SELECT * FROM crm_numbers2 WHERE number = '".$usermail."';", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)>0)
			{
				$row_check=mysqli_fetch_array($res_check);
				$number_id=$row_check["id_crm_number"];
				$number_account_id=$row_check["crm_customer_account_id"];
				$crm_customer_id=$row_check["crm_customer_id"];
			}
		}
		
		//CHECK FOR KNOWN ADDRESS ID

		$address_id=0;
		$address_account_id=0;
		//GET ACCOUNT DATA
		$res_account=q("SELECT * FROM crm_customer_accounts2 WHERE account_user_id = '".$ebay_orders["BuyerUserID"]."' AND (account = 3 OR account = 4);", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_account)>0)
		{
			//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRESSE VORHANDEN
			if ($ebay_orders["ShippingAddressAddressID"]!="")
			{

				$row_account=mysqli_fetch_array($res_account);
		
				$res_check=q("SELECT * FROM crm_address2 WHERE foreign_address_id = ".$ebay_orders["ShippingAddressAddressID"]." AND crm_customer_account_id = ".$row_account["id_customer_account"].";", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($res_check)>0)
				{
					$row_check=mysqli_fetch_array($res_check);
					$address_id=$row_check["id_address"];
					$address_account_id=$row_check["crm_customer_account_id"];
					$crm_customer_id=$row_check["crm_customer_id"];
				}
			}
		}
//CUSTOMER KNOWN CHECK

		//ADD CUSTOMER DATA / UPDATE
		if ($crm_customer_id==0)
		{
			//ADD CUSTOMER
			$res_ins=q("INSERT INTO crm_customers2 (name, street1, street2, zip, city, country, phone, mail, gewerblich, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ('".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressName"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressStreet1"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressStreet2"])."','".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressPostalCode"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressCityName"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressCountryName"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressPhone"])."', '".mysqli_real_escape_string($dbweb, $usermail)."', 0, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			
		$crm_customer_id=mysqli_insert_id($dbweb);			
		}
		
		//ADD ACCOUNT
		if ($crm_customer_id==0 || ($crm_customer_id!=0 && $user_account_id==0))
		{
//		echo "INSERT ACCOUNT";	
			$res_ins=q("INSERT INTO crm_customer_accounts2 (crm_customer_id, account, account_type, account_user_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$crm_customer_id.", ".$crm_account_id.", 1, '".mysqli_real_escape_string($dbweb, $ebay_orders["BuyerUserID"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			
			$user_account_id=mysqli_insert_id($dbweb);
		}
			
		//ADD ADDRESS
		if ($crm_customer_id==0 || ($crm_customer_id!=0 && $address_id==0))
		{
			//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRESSE VORHANDEN
			if ($ebay_orders["ShippingAddressAddressID"]!="")
			{
				$res_ins=q("INSERT INTO crm_address2 (crm_customer_id, crm_customer_account_id, address_type, foreign_address_id, name, street1, street2, zip, city, country, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$crm_customer_id.", ".$user_account_id.", 1, ".$ebay_orders["ShippingAddressAddressID"].", '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressName"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressStreet1"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressStreet2"])."','".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressPostalCode"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressCityName"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressCountryName"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			
				$address_id=mysqli_insert_id($dbweb);
			}
		}
		
		//ADD MAIL
		if ($crm_customer_id==0 || ($crm_customer_id!=0 && $number_id==0))
		{
			if ($usermail!="Invalid Request")
			{
				$res_ins=q("INSERT INTO crm_numbers2 (crm_customer_id, crm_customer_account_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$crm_customer_id.", ".$crm_account_id.", 7, '".mysqli_real_escape_string($dbweb, $usermail)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
				
				$number_id=mysqli_insert_id($dbweb);
			}
		}


//INSERT SHOP ORDER ++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$IPNs=array();
		for ($i=0; $i<sizeof ($ebay_orders_items); $i++)
		{
			$res_IPN=q("SELECT * FROM payment_notifications2 WHERE orderTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' OR parentPaymentTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ORDER BY payment_date DESC;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_IPN)>0)
			{
	//			echo "IPN COUNT: ".mysqli_num_rows($res_IPN)."++++";
				while ($row_IPN=mysqli_fetch_array($res_IPN))
				{
					$IPNs[sizeof($IPNs)]=$row_IPN;
				}
			}
		}
		
		
		//ADD ORDER TO SHOP ORDER
		q("INSERT INTO shop_orders (status_id, shop_id, foreign_OrderID, customer_id, crm_customer_id , ordernr, comment, usermail, userphone, userfax, usermobile, bill_company, bill_gender, bill_title, bill_firstname, bill_lastname, bill_zip, bill_city, bill_street, bill_number, bill_additional, bill_country, bill_country_code, ship_company, ship_gender, ship_title, ship_firstname, ship_lastname, ship_zip, ship_city, ship_street, ship_number, ship_additional, ship_country, ship_country_code, shipping_costs, shipping_type_id, shipping_details, Payments_TransactionID, Payments_TransactionState, Payments_TransactionStateDate, Payments_Type, payments_type_id, PayPal_PendingReason, PayPal_BuyerNote, partner_id, bill_adr_id, ship_adr_id, firstmod, firstmod_user, lastmod, lastmod_user, username, password, shipping_net) VALUES(1, ".$crm_account_id." , '".$ebay_orders["OrderID"]."', 0, ".$crm_customer_id.", '', '', '".mysqli_real_escape_string($dbshop, $usermail)."', '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressPhone"])."', '','','','','','".mysqli_real_escape_string($dbshop, $bill_firstname)."', '".mysqli_real_escape_string($dbshop, $bill_lastname)."', '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressPostalCode"])."', '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCityName"])."', '".mysqli_real_escape_string($dbshop, $bill_street1)."', '".mysqli_real_escape_string($dbshop, $bill_streetNumber)."', '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressStreet2"])."', '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCountryName"])."', '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCountry"])."', '', '', '', '', '', '', '', '', '', '', '', '', ".$ebay_orders["ShippingServiceSelectedShippingServiceCost"].", ".$shippingType.", '".mysqli_real_escape_string($dbshop, $shipping_details)."', '', '', ".$paymentdate.", '".mysqli_real_escape_string($dbshop, $ebay_orders["CheckoutStatusPaymentMethod"])."', ".$payments_type_id.", '', '', 0, 0, 0, ".$ebay_orders["CreatedTimeTimestamp"].", ".$ebay_orders["firstmod_user"].", ".$ebay_orders["lastmod"].", ".$ebay_orders["lastmod_user"].", '', '', 0);", $dbshop, __FILE__, __LINE__);

		$order_id=mysqli_insert_id($dbshop);
/*
		//UPDATE PAYMENT STATE
		if (sizeof($IPNs)>0)
		{
			//UPDATE SHOP ORDER
			q("UPDATE shop_orders SET Payments_TransactionID = '".$IPNs[0]["paymentTransactionID"]."', Payments_TransactionState = '".$IPNs[0]["state"]."', Payments_TransactionStateDate = ".$IPNs[0]["payment_date"].", PayPal_PendingReason = '".$IPNs[0]["state_reason"]."' WHERE id_order = ".$order_id.";", $dbshop, __FILE__, __LINE__);
	//		echo "Shop_Order IPN-Updated<br />";
			
			for ($i=0; $i<sizeof($IPNs); $i++)
			{
				//UPDATE IPNs
				q("UPDATE payment_notifications2 SET shop_orderID = ".$order_id." WHERE id_PN = ".$IPNs[$i]["id_PN"].";", $dbshop, __FILE__, __LINE__);
				//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
				if ($IPNs[$i]["state"]!="Refunded") $paymenttype="paymentrecieved"; else $paymenttype="paymentsent";
				
				$eventmsg ='<Payment>';
				$eventmsg.='<PaymentMethod><![CDATA['.$IPNs[$i]["PaymentMethod"].']]></PaymentType>';
				$eventmsg.='<PaymentType><![CDATA['.$paymenttype.']]></PaymentType>';
				$eventmsg.='<PaymentState><![CDATA['.$IPNs[$i]["state"].']]></PaymentState>';
				$eventmsg.='<PaymentTotal><![CDATA['.$IPNs[$i]["total"].']]></PaymentTotal>';
				$eventmsg.='<PaymentTime><![CDATA['.$IPNs[$i]["payment_date"].']]></PaymentTime>';
				$eventmsg.='</Payment>';
				
				q("INSERT INTO shop_orders_events (order_id, event, message, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$order_id.", 'Payment', '".mysqli_real_escape_string($dbshop, $eventmsg)."', ".time().", 1, ".time().", 1);", $dbshop, __FILE__, __LINE__);
				
			}
			
		}
	*/	
	
		//ADD ORDER ITEMS TO SHOP ORDER ITEMS
		for ($i=0; $i<sizeof ($ebay_orders_items); $i++)
		{
			$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
			$row_item=mysqli_fetch_array($res_item);
			
			q("INSERT INTO shop_orders_items (order_id, foreign_transactionID, item_id, amount, price, netto) VALUES (".$order_id.", '".$ebay_orders_items[$i]["TransactionID"]."', ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", 0);", $dbshop, __FILE__, __LINE__);
			
		}
		
		
	//************************
	//ADD EVENTS
	//************************	
	//ALERT MAIL - EXPRESS SHIPMENT
	if (($shippingType==2 ||$shippingType==7) && ($ebay_orders["shop_id"]==3 || $ebay_orders["shop_id"]==4))
	{
	if ($ebay_orders["shop_id"]==3) $reciever="ebay@mapco.de";
	if ($ebay_orders["shop_id"]==4) $reciever="ebay@ihr-autopartner.com";
	
	
		$subject = 'NEUE EXPRESSBESTELLUNG bei eBay!!!!!!';
	
		$msg='<p>Es ist eine neue Express-Bestellung bei eBay eingegangen.<p>';
		$msg.='<p>eBay-Mitgliedsname: <b>'.$ebay_order["BuyerUserID"].'</b></p>';
		$msg.='<p>Käufer E-Mailadresse: <b>'.$ebay_order["BuyerEmail"].'</b></p>';
		$msg.='<p>eBay-Verkaufsprotokollnummer: <b>'.$ebay_order["ShippingDetailsSellingManagerSalesRecordNumber"].'</b></p>';
		$msg.='<p>Bestellte Artikel: <br />';
		foreach ($ebay_order_items as $ebay_order_item)
		{
			$msg.=$ebay_order_item["QuantityPurchased"].'x '.$ebay_order_item["ItemSKU"].' '.$ebay_order_item["ItemTitle"].' <small>('.$ebay_order_item["ItemItemID"].')</small><br />';
		}
		
		SendMail($reciever, "Bestellmanagement-System <noreply@mapco.de>", $subject, $msg);
		SendMail("nputzing@mapco.de", "Bestellmanagement-System <noreply@mapco.de>", $subject, $msg);

		
		//$response=post(PATH."soa/", array("API" => "crm", "Action" => "AlertMail_ExpressShipment", "OrderID" => $order_id));
	}

		
	} // MODE ADD
	
//*****************************************************************
// UPDATE
//*****************************************************************

//	if ($_POST["mode"]=="update")
else
	{
		
		$old_transactions=array();
		
		//CHECK FOR OLD TRANSACTIONS
		
		for ($i=0; $i<sizeof($ebay_orders_items); $i++)
		{
			$res_check_items=q("SELECT * FROM shop_orders_items WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check_items)>0)
			{
				$row_check_items=mysqli_fetch_array($res_check_items);
				$old_transactions[$i]["TransactionID"]=$row_check_items["foreign_transactionID"];
				$old_transactions[$i]["order_id"]=$row_check_items["order_id"];
				$res_check_order=q("SELECT * FROM shop_orders WHERE id_order = ".$row_check_items["order_id"].";",$dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_check_order)>0)
				{
					$row_check_order=mysqli_fetch_array($res_check_order);
					$old_transactions[$i]["OrderID"]=$row_check_order["foreign_OrderID"];
				}
				else
				{
					$old_transactions[$i]["OrderID"]="0";
				}
			}
			else
			{
				$old_transactions[$i]["TransactionID"]="0";
				$old_transactions[$i]["order_id"]=0;
				$old_transactions[$i]["OrderID"]="0";
			}
		}
		
		//FESTLEGEN DER ZU UPDATENDEN ORDER
			//Search for OrderID
		$res_check=q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)>0)
		{
			$update_OrderID=$ebay_orders["OrderID"];
		}
		else
		{
			$update_OrderID="0";
		}
		if ($ebay_orders["OrderID"]!=$update_OrderID)
		{
			for ($i=0; $i<sizeof($old_transactions); $i++)
			{
				if ($old_transactions[$i]["OrderID"]!=0 && $update_OrderID=="0") $update_OrderID=$old_transactions[$i]["OrderID"];
			}
		}

			//UPDATE ORDER
		q("UPDATE shop_orders SET foreign_OrderID = '".mysqli_real_escape_string($dbshop, $ebay_orders["OrderID"])."', usermail = '".mysqli_real_escape_string($dbshop, $usermail)."', userphone = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressPhone"])."', bill_firstname = '".mysqli_real_escape_string($dbshop, $bill_firstname)."', bill_lastname = '".mysqli_real_escape_string($dbshop, $bill_lastname)."', bill_zip = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressPostalCode"])."', bill_city = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCityName"])."', bill_street = '".mysqli_real_escape_string($dbshop, $bill_street1)."', bill_number = '".mysqli_real_escape_string($dbshop, $bill_streetNumber)."', bill_additional = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressStreet2"])."', bill_country = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCountryName"])."', bill_country_code = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCountry"])."', shipping_costs = ".$ebay_orders["ShippingServiceSelectedShippingServiceCost"].", shipping_type_id =".$shippingType.", shipping_details = '".mysqli_real_escape_string($dbshop, $shipping_details)."', Payments_TransactionStateDate = ".$paymentdate.", Payments_Type = '".mysqli_real_escape_string($dbshop, $ebay_orders["CheckoutStatusPaymentMethod"])."', payments_type_id = ".$payments_type_id.", lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]."  WHERE foreign_OrderID = '".$update_OrderID."' ;",$dbshop, __FILE__, __LINE__);
			
		//GET id_order
		$res=q("SELECT id_order from shop_orders WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($res);
		$id_order=$row["id_order"];
			
		//DELETE other OLD ORDERS	
		for ($i=0; $i<sizeof($old_transactions); $i++)
		{
			if ($old_transactions[$i]["OrderID"]!=$update_OrderID)
			{
				q("DELETE FROM shop_orders WHERE foreign_OrderID ='".$old_transactions[$i]["OrderID"]."';", $dbshop, __FILE__, __LINE__);
			}
		}
			

		//UPDATE/ADD ITEMS
		for ($i=0; $i<sizeof($ebay_orders_items); $i++)
		{
			//check for existing Transaction
			$res_check=q("SELECT * FROM shop_orders_items WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)>0) 
			{
				//UPDATE TRANSACTION
				$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row_item=mysqli_fetch_array($res_item);

				q("UPDATE shop_orders_items SET order_id = ".$id_order.", item_id = ".$row_item["id_item"].", amount = ".$ebay_orders_items[$i]["QuantityPurchased"].", price = ".$ebay_orders_items[$i]["TransactionPrice"].", netto = 0 WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__); 
				
			}
			else
			{
				//ADD TRANSACION
				$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row_item=mysqli_fetch_array($res_item);
				
				q("INSERT INTO shop_orders_items (order_id, foreign_TransactionID, item_id, amount, price, netto, customer_vehicle_id) VALUES (".$id_order.", '".$ebay_orders_items[$i]["TransactionID"]."', ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", 0, 0);", $dbshop, __FILE__, __LINE__);	
				
			}
		}

		//CHECK, OB ALTE TRANSACTIONEN vorhanden sind, die nicht mehr in der aktuellen Order stehen
		for ($i=0; $i<sizeof($old_transactions); $i++)
		{
			$no_match=true;
			for ($j=0; $j<sizeof($ebay_orders_items); $j++)
			{
				if ($old_transactions[$i]["TransactionID"]==$ebay_orders_items["foreign_TransactionID"]) $no_match=false;
			}
			if ($no_match)
			{
				q("UPDATE shop_orders_items SET order_id=0 WHERE foreign_TransactionID = '".$old_transactions[$i]["TransactionID"]."';", $dbshop, __FILE__, __LINE__);
			}
		}


//***********************************************************************************************************************


			//GET ACCOUNT DATA
		$res_account=q("SELECT * FROM crm_customer_accounts2 WHERE account_user_id = '".$ebay_orders["BuyerUserID"]."' AND (account = 3 OR account = 4);", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_account)>0)
		{
			$row_account=mysqli_fetch_array($res_account);

			//UPDATE ADDRESS	
				//CHECK IF ADDRESS IS KNOWN
				//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRESSE VORHANDEN
			if ($ebay_orders["ShippingAddressAddressID"]!="")
			{
				$res_check=q("SELECT * FROM crm_address2 WHERE foreign_address_id = ".$ebay_orders["ShippingAddressAddressID"]." AND crm_customer_account_id = ".$row_account["id_customer_account"].";", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($res_check)==0)
				{
					$res_ins=q("INSERT INTO crm_address2 (crm_customer_id, crm_customer_account_id, address_type, foreign_address_id, name, street1, street2, zip, city, country, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$row_account["crm_customer_id"].", ".$row_account["id_customer_account"].", 1, ".$ebay_orders["ShippingAddressAddressID"].", '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressName"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressStreet1"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressStreet2"])."','".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressPostalCode"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressCityName"])."', '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressCountryName"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
				}
			}
			
			//CHECK IF MAIL IS KNOWN
			if ($usermail!="Invalid Request")
			{

				$res_check=q("SELECT * FROM crm_numbers WHERE crm_customer_id = ".$row_account["crm_customer_id"]." AND number = '".$usermail."' ;", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($res_check)==0)
				{
					$res_ins=q("INSERT INTO crm_numbers2 (crm_customer_id, crm_customer_account_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$row_account["crm_customer_id"].", ".$row_account["id_customer_account"].", 7, '".mysqli_real_escape_string($dbweb, $usermail)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
				}
			}
			

			//CHECK IF PHONE IS KNOWN
			$res_check=q("SELECT * FROM crm_numbers WHERE crm_customer_id = ".$row_account["crm_customer_id"]." AND number = '".$ebay_orders["ShippingAddressPhone"]."' ;", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)==0)
			{
				$res_ins=q("INSERT INTO crm_numbers2 (crm_customer_id, crm_customer_account_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$row_account["crm_customer_id"].", ".$row_account["id_customer_account"].", 1, '".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressPhone"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			}

		}
	
	}
	
}
}
	echo "<crm_add_customer_listResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_add_customer_listResponse>";

?>