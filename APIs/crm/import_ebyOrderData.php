<?php

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

	$res_orders=q("SELECT * FROM ebay_orders2 WHERE id_order = ".$_POST["EbayOrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_orders)==0)
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
	
	$ebay_orders=mysql_fetch_array($res_orders);
	
	$res_orders_items=q("SELECT * FROM ebay_orders_items2 WHERE OrderID = ".$ebay_orders["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_orders_items)==0)
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
	$i++;
	while ($row_orders_items=mysql_fetch_array($res_orders_items))
	{
		$ebay_orders_items[$i]=$row_orders_items;
		$i++;
	}
	
	if ($ebay_orders["account_id"]==1) $crm_account_id=3;
	if ($ebay_orders["account_id"]==2) $crm_account_id=4;
	
	$payment_date=$ebay_orders["PaidTime"];
	$paymentdate=mktime(substr($payment_date, 11,2)*1, substr($payment_date, 13,2)*1, substr($payment_date, 15,2)*1, substr($payment_date, 5,2)*1, substr($payment_date, 8,2)*1, substr($payment_date, 0,4)*1);

	$shipping_details=$ebay_orders["ShippingServiceSelectedShippingService"].", ".$ebay_orders["CheckoutStatusPaymentMethod"];



//*****************************************************************
// ADD
//*****************************************************************


	if ($_POST["mode"]=="add")
	{
		
		//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> PAYMENT NOTIFICATION suchen
		
		$usermail=$ebay_orders_items[0]["BuyerEmail"];
		
		//ADD ORDER TO SHOP ORDER
		q("INSERT INTO shop_orders_crm (status_id, shop_id, foreign_order_id, customer_id, ordernr, comment, usermail, userphone, userfax, usermobile, bill_company, bill_gender, bill_title, bill_firstname, bill_lastname, bill_zip, bill_city, bill_street, bill_number, bill_additional, bill_country, ship_company, ship_gender, ship_title, ship_firstname, ship_lastname, ship_zip, ship_city, ship_street, ship_number, ship_additional, ship_country, shipping_costs, shipping_details, Payments_TransactionID, Payments_TransactionState, Payments_TransactionStateDate, Payments_Type, PayPal_PendingReason, PayPal_BuyerNote, partner_id, bill_adr_id, ship_adr_id, firstmod, firstmod_user, lastmod, lastmod_user, username, password, shipping_net) VALUES(1, ".$crm_account_id." , '".$ebay_orders["id_order"]."', 0, '', '', '".mysql_real_escape_string($usermail,$dbshop)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressPhone"],$dbshop)."', '','','','','','', '".mysql_real_escape_string($ebay_orders["ShippingAddressName"], $dbshop)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbshop)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressCityName"], $dbshop)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet1"], $dbshop)."', '', '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbshop)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbshop)."', '', '', '', '', '', '', '', '', '', '', '', ".$ebay_orders["ShippingServiceSelectedShippingServiceCost"].", '".mysql_real_escape_string($shipping_details, $dbshop)."', '', '', ".$paymentdate.", '".mysql_real_escape_string($ebay_orders["CheckoutStatusPaymentMethod"], $dbshop)."', '', '', 0, 0, 0, ".$ebay_orders["CreatedTimeTimestamp"].", ".$ebay_orders["firstmod_user"].", ".$ebay_orders["lastmod"].", ".$ebay_orders["lastmod_user"].", '', '', 0);", $dbshop, __FILE__, __LINE__);

		$order_id=mysql_insert_id($dbshop);
	
		//ADD ORDER ITEMS TO SHOP ORDER ITEMS
		for ($i=0; $i<sizeof ($ebay_orders_items); $i++)
		{
			$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
			$row_item=mysql_fetch_array($res_item);
			
			q("INSERT INTO shop_orders_items_crm (order_id, item_id, amount, price, netto) VALUES (".$order_id.", ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", 0);", $dbshop, __FILE__, __LINE__);
		}
		
	//CHECK IF CUSTOMER IS KNOWN
		$crm_customer_id=0;
		//CHECK FOR KNOWN ACCOUNT ID
		$user_account_id=0;
		$res_check=q("SELECT * FROM crm_customer_accounts2 WHERE account_user_id = '".$ebay_orders["BuyerUserID"]."' AND (account = 3 OR account = 4);", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($res_check)>0)
		{
			$row_check=mysql_fetch_array($res_check);
			$user_account_id=$row_check["id_customer_account"];
			$crm_customer_id=$row_check["crm_customer_id"];
		}
		
		//CHECK FOR KNOWN EMAIL
		$number_id=0;
		$number_account_id=0;
		$res_check=q("SELECT * FROM crm_numbers2 WHERE number = '".$usermail."';", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($res_check)>0)
		{
			$row_check=mysql_fetch_array($res_check);
			$number_id=$row_check["id_crm_number"];
			$number_account_id=$row_check["crm_customer_account_id"];
			$crm_customer_id=$row_check["crm_customer_id"];
		}
		
		//CHECK FOR KNOWN ADDRESS ID
		$address_id=0;
		$address_account_id=0;
		$res_check=q("SELECT * FROM crm_address2 WHERE foreign_address_id = ".$ebay_orders["ShippingAddressAddressID"]." AND (account = 3 OR account = 4);", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($res_check)>0)
		{
			$row_check=mysql_fetch_array($res_check);
			$res_check2=q("SELECT * FROM crm_customer_accounts2 WHERE id_customer_account = ".$row_check["crm_customer_account_id"]." AND (account = 3 OR account = 4);", $dbweb, __FILE__, __LINE__);
			if (mysql_num_rows($res_check2)==1)
			{
				$address_id=$row_check["id_address"];
				$address_account_id=$row_check["crm_customer_account_id"];
				$crm_customer_id=$row_check["crm_customer_id"];
			}
		}
//CUSTOMER KNOWN CHECK

		//ADD CUSTOMER DATA / UPDATE
		if ($crm_customer_id==0)
		{
			//ADD CUSTOMER
			$res_ins=q("INSERT INTO crm_customers2 (name, street1, street2, zip, city, country, phone, mail, gewerblich, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ('".mysql_real_escape_string($ebay_orders["ShippingAddressName"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet1"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbweb)."','".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressCityName"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressPhone"],$dbweb)."', '".mysql_real_escape_string($usermail,$dbweb)."', 0, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			
			$crm_customer_id=mysql_insert_id($dbweb);			
		}
		
		//ADD ACCOUNT
		if ($crm_customer_id==0 || ($crm_customer_id!=0 && $user_account_id==0))
		{
			
			$res_ins=q("INSERT INTO crm_customer_accounts2 (crm_customer_id, account, account_type, account_user_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$crm_customer_id.", ".$crm_account_id.", 1, '".mysql_real_escape_string($ebay_orders["BuyerUserID"], $dbweb)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			
			$user_account_id=mysql_insert_id($dbweb);
		}
			
		//ADD ADDRESS
		if ($crm_customer_id==0 || ($crm_customer_id!=0 && $address_id==0))
		{
			$res_ins=q("INSERT INTO crm_address2 (crm_customer_id, crm_customer_account_id, address_type, foreign_address_id, name, street1, street2, zip, city, country, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$crm_customer_id.", ".$crm_account_id.", 1, '".$ebay_orders["ShippingAddressAddressID"].", '".mysql_real_escape_string($ebay_orders["ShippingAddressName"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet1"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbweb)."','".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressCityName"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbweb)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			
			$address_id=mysql_insert_id($dbweb);
		}
		
		//ADD MAIL
		if ($crm_customer_id==0 || ($crm_customer_id!=0 && $number_id==0))
		{
			$res_ins=q("INSERT INTO crm_numbers2 (crm_customer_id, crm_customer_account_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$crm_customer_id.", ".$crm_account_id.", 1, '".mysql_real_escape_string($usermail,$dbweb)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			
			$number_id=mysql_insert_id($dbweb);
		}
		
	//************************
	//ADD EVENTS
	//************************	
		
	} // MODE ADD
	
//*****************************************************************
// UPDATE
//*****************************************************************

	if ($_POST["mode"]=="update")
	{
	
		//GET PREVIOUS EBAY ORDERIDs
		if (!isset($_POST["Prev_EbayOrderID_0"]))
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
		$prev_EbayOrderID=array();
		$prev_ShopOrderID=array();
		while (isset($_POST["Prev_EbayOrderID_".$i]))
		{
			$prev_EbayOrderID[$i]=$_POST["Prev_EbayOrderID_".$i];
			//Get SHOP ORDER IDs
			$res_shop_orderID=q("SELECT id_order FROM shop_orders_crm WHERE foreign_order_id = ".$prev_EbayOrderID[$i]." AND shop_id = ".$crm_account_id.";", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_shop_orderID)==0) 
			{
				$row_shop_orderID=mysql_fetch_array($res_shop_orderID);
				$prev_ShopOrderID[$i]=$row_shop_orderID["id_order"];
			}
						
			$i++;
		}
		
//>>>>>>>>>>>>>>> PAYMENT NOTIFICATION SUCHEN		
		//UPDATE FIRST PREV ORDER
		q("UPDATE shop_orders_crm SET usermail = '".mysql_real_escape_string($usermail,$dbshop)."', userphone = '".mysql_real_escape_string($ebay_orders["ShippingAddressPhone"],$dbshop)."', bill_lastname = '".mysql_real_escape_string($ebay_orders["ShippingAddressName"], $dbshop)."', bill_zip = '".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbshop)."', bill_city = '".mysql_real_escape_string($ebay_orders["ShippingAddressCityName"], $dbshop)."', bill_street = '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet1"], $dbshop)."', bill_additional = '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbshop)."'. bill_country = '".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbshop).", shipping_costs = ".$ebay_orders["ShippingServiceSelectedShippingServiceCost"].", shipping_details = '".mysql_real_escape_string($shipping_details, $dbshop)."', Payments_TransactionStateDate = ".$paymentdate.", Payments_Type = '".mysql_real_escape_string($ebay_orders["CheckoutStatusPaymentMethod"], $dbshop)."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE shop_id = ".$crm_account_id." AND foreign_order_id = ".$prev_EbayOrderID[$i].";", $dbshop, __FILE__, __LINE__);
	
		//UPDATE ORDER ITEMS 
		// 1. DELETE ORDER ITEMS
		for ($i=0; $i<sizeof($prev_EbayOrderID); $i++)
		{ 
			if (isset($prev_ShopOrderID[$i]))
			{
				q("DELETE FROM shop_orders_items_crm WHERE order_id = ".$prev_ShopOrderID[$i].";", $dbshop, __FILE__, __LINE__);
			}
		}
		// 2. INSERT ORDER ITEMS
		for ($i=0; $i<sizeof($ebay_orders_items); $i++)
		{
			$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
			$row_item=mysql_fetch_array($res_item);
			
			q("INSERT INTO shop_orders_items_crm (order_id, item_id, amount, price, netto) VALUES (".$prev_ShopOrderID[0].", ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", 0);", $dbshop, __FILE__, __LINE__);		
		}

			//GET ACCOUNT DATA
		$res_account=q("SELECT * FROM crm_customer_accounts2 WHERE account_user_id = '".$ebay_orders["BuyerUserID"]."' AND (account = 3 OR account = 4);", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($res_account)>0)
		{
			$row_account=mysql_fetch_array($res_account);

			//UPDATE ADDRESS	
				//CHECK IF ADDRESS IS KNOWN
			$res_check=q("SELECT * FROM crm_address2 WHERE foreign_address_id = ".$ebay_orders["ShippingAddressAddressID"]." AND (account = 3 OR account = 4);", $dbweb, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)==0)
			{
				$res_ins=q("INSERT INTO crm_address2 (crm_customer_id, crm_customer_account_id, address_type, foreign_address_id, name, street1, street2, zip, city, country, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$row_account["crm_customer_id"].", ".$row_account["crm_account_id"].", 1, '".$ebay_orders["ShippingAddressAddressID"].", '".mysql_real_escape_string($ebay_orders["ShippingAddressName"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet1"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbweb)."','".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressCityName"], $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbweb)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			}
			
				//CHECK IF MAIL IS KNOWN
			$res_check=q("SELECT * FROM crm_numbers2 WHERE crm_customer_id = ".$row_account["crm_customer_id"]." AND number = ".$usermail." ;", $dbweb, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)==0)
			{
				$res_ins=q("INSERT INTO crm_numbers2 (crm_customer_id, crm_customer_account_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$row_account["crm_customer_id"].", ".$row_account["crm_account_id"].", 1, '".mysql_real_escape_string($usermail,$dbweb)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			}
		}
	}
	

	echo "<crm_add_customer_listResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_add_customer_listResponse>";

?>