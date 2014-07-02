<?php

	include("../functions/cms_createPassword.php");
	include("../functions/cms_send_html_mail.php");

	$ust = (UST/100) +1;

//echo $_POST["mode"];
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
	$res_orders=q("SELECT * FROM ebay_orders10 WHERE id_order = ".$_POST["EbayOrderID"].";", $dbshop, __FILE__, __LINE__);
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
	$res_orders_items=q("SELECT * FROM ebay_orders_items10 WHERE OrderID = '".$ebay_orders["OrderID"]."';", $dbshop, __FILE__, __LINE__);
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
	$ebay_orders_items=array();
	while ($row_orders_items=mysql_fetch_array($res_orders_items))
	{
		$ebay_orders_items[]=$row_orders_items;
	}
	
	// GET PARENT ACCOUNT
	$res_shop=q("SELECT * FROM shop_shops WHERE account_id = ".$ebay_orders["account_id"]." AND shop_type = 2;", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_shop)==0)
	{
		echo '<crm_add_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop_id nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur Ebay Bestellung konnte kein Shop (shop_shops) gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		exit;
	}
	$shop=mysql_fetch_array($res_shop);
	//GET SITE_ID

	$res_shop=q("SELECT * FROM shop_shops WHERE id_shop = ".$shop["parent_shop_id"].";", $dbshop, __FILE__, __LINE__);
	
	if (mysql_num_rows($res_shop)==0)
	{
		echo '<crm_add_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop_id nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>SITE ID konnte nicht ermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		//exit;
		$site_id=0;
	}
	else
	{
		$shop_site=mysql_fetch_array($res_shop);
		$site_id=$shop_site["site_id"];
	}

	$payment_date=$ebay_orders["PaidTime"];

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
	
	$packstation=false;
	//PACKSTATION
		//search for "PACKSTATION";
		if (strpos(strtolower($ebay_orders["ShippingAddressName"]),"packstation")===true)
		{
			$packstation=true;
			$tmp=$ebay_orders["ShippingAddressName"];
			$ebay_orders["ShippingAddressName"]=$ebay_orders["ShippingAddressStreet1"];
			$ebay_orders["ShippingAddressStreet1"]=$tmp;
		}
		if (strpos($ebay_orders["ShippingAddressStreet2"], "Packstation") !== false)
		{
			$packstation=true;
			$tmp=$ebay_orders["ShippingAddressStreet1"];
			$ebay_orders["ShippingAddressStreet1"]=$ebay_orders["ShippingAddressStreet2"];
			$ebay_orders["ShippingAddressStreet2"]=$tmp;
		}
		
		if ($packstation)
		{
			if (is_numeric($ebay_orders["ShippingAddressName"]) && strlen($ebay_orders["ShippingAddressName"])>=7 && strlen($ebay_orders["ShippingAddressName"])<=10)
			{
				$tmp=$ebay_orders["ShippingAddressName"];
				$ebay_orders["ShippingAddressName"]=$ebay_orders["ShippingAddressStreet2"];
				$ebay_orders["ShippingAddressStreet2"]=$tmp;
			}
		}
	
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
	
	//ADD StateOrProvince
	if ($ebay_orders["ShippingAddressStateOrProvince"]=="")
	{
		$bill_city=$ebay_orders["ShippingAddressCityName"];
	}
	else
	{
		$bill_city=$ebay_orders["ShippingAddressCityName"].", ".$ebay_orders["ShippingAddressStateOrProvince"];
	}
	
	//PHONE Number
	if ($ebay_orders["ShippingAddressPhone"] == "" || $ebay_orders["ShippingAddressPhone"] == "Invalid Request")
	{
		$bill_phone="00000 00000";
	}
	else
	{
		$bill_phone=$ebay_orders["ShippingAddressPhone"];
	}
	
	

	//GET SHIPPING TYPES
	$shop_shipping_type=array();
	$res_shiptype=q("SELECT * FROM ebay_shipping_types;", $dbshop, __FILE__, __LINE__);
	while ($row_shiptype=mysql_fetch_array($res_shiptype))
	{
		$shop_shipping_type[$row_shiptype["ShippingServiceType"]]=$row_shiptype["shippingtype_id"];
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
	if (mysql_num_rows($res_payments)>0)
	{
		$row_payments=mysql_fetch_array($res_payments);
		$payments_type_id=$row_payments["id_paymenttype"];
	}
	else
	{
		$payments_type_id=0;
	}
	
	//GET COUNTRIES
	$countries=array();
	$res_countries=q("SELECT * FROM shop_countries;", $dbshop, __FILE__, __LINE__);
	while ($row_countries=mysql_fetch_array($res_countries))
	{
		$countries[$row_countries["country_code"]]=$row_countries["id_country"];
		
	}

	//GET CURRENCY-Exchange_rate
	$currencies=array();
	$res_currencies=q("SELECT * FROM shop_currencies;", $dbshop, __FILE__, __LINE__);
	while ($row_currencies=mysql_fetch_array($res_currencies))
	{
		$currencies[$row_currencies["currency_code"]]=$row_currencies["exchange_rate_to_EUR"];
		
	}

	//check for UnPaid Item
	$unpaid_item=false;
	for ($i=0; $i<sizeof($ebay_orders_items); $i++)
	{
		if ($ebay_orders_items[$i]["UnpaidItem"]=="ClosedWithoutPayment") $unpaid_item=true;
	}

	//SET SHOP STATE ID
	$status_id=1;
	$paymentstatus="";
	$status_date=$ebay_orders["CreatedTimeTimestamp"];
	if ($ebay_orders["OrderStatus"]=="Cancelled" || $ebay_orders["OrderStatus"]=="Inactive" || $ebay_orders["Total"]==0 || $unpaid_item) 
	{
		$status_id=4;
	//	mail("nputzing@mapco.de", "Bestellungsabbruch", $ebay_orders["OrderID"]);
		$status_date=strtotime($ebay_orders["CheckoutStatusLastModifiedTime"]);
	}
	elseif (($payments_type_id == 2 || $payments_type_id == 4) && $paymentdate !=0 )
	{
		$status_id=7;
		$status_date=strtotime($ebay_orders["CheckoutStatusLastModifiedTime"]);
		$paymentstatus="Completed";
		//unten bei Update abchecken ob status 7 gesetzt werden kann
	}
	
//*****************************************************************
// ADD
//*****************************************************************


	if ($_POST["mode"]=="add")
	{
		
	//CHECK IF CUSTOMER IS KNOWN
		$cms_user_id=0;
		//CHECK FOR KNOWN ACCOUNT ID
		$shop_user_id=0;
		$res_check=q("SELECT * FROM crm_customer_accounts3 WHERE shop_user_id = '".$ebay_orders["BuyerUserID"]."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($res_check)>0)
		{
			$row_check=mysql_fetch_array($res_check);
			$shop_user_id=$row_check["shop_user_id"];
			$cms_user_id=$row_check["cms_user_id"];
		}

		//CHECK FOR KNOWN EMAIL
		$number_id=0;
		$number_account_id=0;
		if ($usermail!="Invalid Request" && $usermail!="" )
		{
			$res_check=q("SELECT * FROM crm_numbers3 WHERE number = '".$usermail."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)>0)
			{
				$row_check=mysql_fetch_array($res_check);
				$number_id=$row_check["id_crm_number"];
				$number_shop_id=$row_check["shop_id"];
				if ($cms_user_id==0) $cms_user_id=$row_check["cms_user_id"];
			}
		}
		
		
//CUSTOMER KNOWN CHECK

		//ADD CUSTOMER DATA / UPDATE
		if ($cms_user_id==0)
		{
			//CMS_USER ANLEGEN
			//check if username exists
			
			//EBAY USERNAME -> CMS USERNAME
			
			//GET existing CMS users
			$CMS=array();
			$res_CMS=q("SELECT * FROM cms_users WHERE shop_id = ".$shop["parent_shop_id"].";" , $dbweb, __FILE__, __LINE__);
			while ($row_CMS=mysql_fetch_array($res_CMS))
			{
				$CMS[$row_CMS["username"]]=$row_CMS["id_user"];
			}

			
			
			$cms_username="";
			if (!isset($CMS[$ebay_orders["BuyerUserID"]]))
			{
				$cms_username=$ebay_orders["BuyerUserID"];
			}

			
			if ($cms_username=="")
			{
				if ($usermail!="" && $usermail!="Invalid Request") $cms_username=$usermail[0];
			}
			
			if ($cms_username=="" && isset($ebay_orders["ShippingAddressAddressID"]))
			{
				$tmp=$bill_lastname;
				if (!isset($CMS[$tmp]) && $cms_username=="") $cms_username=$tmp;

				if ($cms_username=="")
				{
					$counter=1;
					$tmp=$bill_lastname.(string)$counter;
					while (isset($CMS[$tmp]))
					{
						$counter++;
						$tmp=$tmp=$bill_lastname.(string)$counter;
					}
					$cms_username=$tmp;
					
				}
			}

		$res_ins=q("INSERT INTO cms_users (shop_id, site_id, username, usermail, name, password, user_token, userrole_id, language_id, active, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$shop["parent_shop_id"].", ".$site_id.", '".mysql_real_escape_string($cms_username, $dbweb)."', '".mysql_real_escape_string($usermail, $dbweb)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressName"], $dbweb)."','".mysql_real_escape_string(createPassword(8),$dbweb)."', '".mysql_real_escape_string(createPassword(50),$dbweb)."',5,1,1, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
		$cms_user_id=mysql_insert_id($dbweb);

		}
		
		//ADD ACCOUNT
		if ($cms_user_id!=0 && $shop_user_id==0)
		{
//		echo "INSERT ACCOUNT";	
			$res_ins=q("INSERT INTO crm_customer_accounts3 (cms_user_id, shop_id, shop_user_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$cms_user_id.", ".$shop["id_shop"].",'".mysql_real_escape_string($ebay_orders["BuyerUserID"], $dbweb)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			
			$id_customer_account=mysql_insert_id($dbweb);
		}
			

		
		//CHECK FOR KNOWN ADDRESS ID

		$address_id=0;
		$address_account_id=0;
		//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRRESSID VORHANDEN
		if ($ebay_orders["ShippingAddressAddressID"]!="" && $cms_user_id!=0)
		{

			$res_check=q("SELECT * FROM shop_bill_adr10 WHERE foreign_address_id = ".$ebay_orders["ShippingAddressAddressID"]." AND shop_id = ".$shop["id_shop"]." AND user_id = ".$cms_user_id.";", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)>0)
			{
				$row_check=mysql_fetch_array($res_check);
				$address_id=$row_check["adr_id"];
				//$address_account_id=$row_check["adr_id"];
			//	if ($cms_user_id==0) $cms_user_id=$row_check["user_id"];
			}
			//$address_id=0;
		}
		// CHECK FOR KNOWN ADDRESS
		if ($cms_user_id!=0 && $address_id==0)
		{
			$res_check=q("SELECT * FROM shop_bill_adr10 WHERE user_id = ".$cms_user_id." AND shop_id = ".$shop["id_shop"].";", $dbshop, __FILE__, __LINE__);
			while ($row_check=mysql_fetch_array($res_check) && $address_id==0)
			{
				$equals=true;
				if ($row_check["firstname"]!=$bill_firstname) $equals=false;
				if ($row_check["lastname"]!=$bill_lastname) $equals=false;
				if ($row_check["street"]!=$bill_street1) $equals=false;
				if ($row_check["number"]!=$bill_streetNumber) $equals=false;
				if ($row_check["additional"]!=$ebay_orders["ShippingAddressStreet2"]) $equals=false;
				if ($row_check["zip"]!=$ebay_orders["ShippingAddressPostalCode"]) $equals=false;
				if ($row_check["city"]!=$bill_city) $equals=false;
				if ($row_check["country"]!=$ebay_orders["ShippingAddressCountryName"]) $equals=false;
				
				if ($equals) $address_id=$row_check["adr_id"];
				
			}
		}
			
		//ADD ADDRESS
		if ($cms_user_id!=0 && $address_id==0)
		{
			//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRESSE VORHANDEN
			if ($ebay_orders["ShippingAddressAddressID"]!="")
			{
				$res=q("INSERT INTO shop_bill_adr10 (user_id, shop_id, foreign_address_id, firstname, lastname, street, number, additional, zip, city, country, country_id, standard, active) VALUES (".$cms_user_id.", ".$shop["id_shop"].", '".mysql_real_escape_string($ebay_orders["ShippingAddressAddressID"], $dbshop)."', '".mysql_real_escape_string($bill_firstname, $dbshop)."', '".mysql_real_escape_string($bill_lastname , $dbshop)."', '".mysql_real_escape_string($bill_street1, $dbshop)."', '".mysql_real_escape_string($bill_streetNumber, $dbshop)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbshop)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbshop)."', '".mysql_real_escape_string($bill_city, $dbshop)."', '".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbshop)."', ".$countries[$ebay_orders["ShippingAddressCountry"]].", 1,1 );", $dbshop, __FILE__, __LINE__);
				
				$address_id=mysql_insert_id($dbshop);
			}
		}
		
		//ADD MAIL
		if ($cms_user_id!=0 && $number_id==0)
		{
			if ($usermail!="Invalid Request" && $usermail!="")
			{
				$res_ins=q("INSERT INTO crm_numbers3 (cms_user_id, shop_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$cms_user_id.", ".$shop["id_shop"].", 7, '".mysql_real_escape_string($usermail,$dbweb)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
				
				$number_id=mysql_insert_id($dbweb);
			}
		}

		//CHECK FOR KNOWN PHONE
		$phone_id=0;
		$number_shop_id=0;
		if ($ebay_orders["ShippingAddressPhone"]!="Invalid Request" && $ebay_orders["ShippingAddressPhone"]!="")
		{
			$res_check=q("SELECT * FROM crm_numbers3 WHERE number = '".$ebay_orders["ShippingAddressPhone"]."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)>0)
			{
				$row_check=mysql_fetch_array($res_check);
				$phone_id=$row_check["id_crm_number"];
				$number_shop_id=$row_check["shop_id"];
			//	if ($cms_user_id==0) $cms_user_id=$row_check["cms_user_id"];
			}
		}


		//ADD PHONE
		if ($cms_user_id!=0 && $phone_id==0)
		{
			if ($ebay_orders["ShippingAddressPhone"]!="Invalid Request" && $ebay_orders["ShippingAddressPhone"]!="")
			{
				$res_ins=q("INSERT INTO crm_numbers3 (cms_user_id, shop_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$cms_user_id.", ".$shop["id_shop"].", 1, '".mysql_real_escape_string($ebay_orders["ShippingAddressPhone"],$dbweb)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
				
				$phone_id=mysql_insert_id($dbweb);
			}
		}



//INSERT SHOP ORDER ++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$IPNs=array();
		for ($i=0; $i<sizeof ($ebay_orders_items); $i++)
		{
			$res_IPN=q("SELECT * FROM payment_notifications3 WHERE orderTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' OR parentPaymentTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ORDER BY payment_date DESC;", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_IPN)>0)
			{
	//			echo "IPN COUNT: ".mysql_num_rows($res_IPN)."++++";
				while ($row_IPN=mysql_fetch_array($res_IPN))
				{
					$IPNs[sizeof($IPNs)]=$row_IPN;
				}
			}
		}
		
		
		//ADD ORDER TO SHOP ORDER
		
		//get STATUS ID
		
		q("INSERT INTO shop_orders10 (
			shop_id, 
			status_id,
			status_date,
			Currency_Code,
			foreign_OrderID, 
			customer_id, 
			usermail, 
			userphone, 
			bill_firstname, 
			bill_lastname, 
			bill_zip, 
			bill_city, 
			bill_street, 
			bill_number, 
			bill_additional, 
			bill_country, 
			bill_country_code, 
			shipping_costs, 
			shipping_type_id, 
			shipping_details, 
			Payments_TransactionStateDate, 
			Payments_Type, 
			payments_type_id, 
			Payments_TransactionState,
			partner_id, 
			bill_adr_id, 
			ship_adr_id, 
			firstmod, 
			firstmod_user, 
			lastmod, 
			lastmod_user, 
			shipping_net) 
		VALUES(
			".$shop["id_shop"].",
			".$status_id.", 
			".$status_date.",
			'".$ebay_orders["Currency_Code"]."',
			'".$ebay_orders["OrderID"]."', 
			".$cms_user_id.", 
			'".mysql_real_escape_string($usermail,$dbshop)."', 
			'".mysql_real_escape_string($bill_phone,$dbshop)."', 
			'".mysql_real_escape_string($bill_firstname, $dbshop)."', 
			'".mysql_real_escape_string($bill_lastname, $dbshop)."', 
			'".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbshop)."', 
			'".mysql_real_escape_string($bill_city, $dbshop)."', 
			'".mysql_real_escape_string($bill_street1, $dbshop)."', 
			'".mysql_real_escape_string($bill_streetNumber, $dbshop)."', 
			'".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbshop)."', 
			'".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbshop)."', 
			'".mysql_real_escape_string($ebay_orders["ShippingAddressCountry"], $dbshop)."', 
			".$ebay_orders["ShippingServiceSelectedShippingServiceCost"].", 
			".$shippingType.", 
			'".mysql_real_escape_string($shipping_details, $dbshop)."', 
			".$paymentdate.", 
			'".mysql_real_escape_string($ebay_orders["CheckoutStatusPaymentMethod"], $dbshop)."', 
			".$payments_type_id.", 
			'".mysql_real_escape_string($paymentstatus, $dbshop)."',
			0, 
			".$address_id.", 
			0, 
			".$ebay_orders["CreatedTimeTimestamp"].", 
			".$ebay_orders["firstmod_user"].", 
			".$ebay_orders["lastmod"].", 
			".$ebay_orders["lastmod_user"].", 
			0
			);", $dbshop, __FILE__, __LINE__);

		$order_id=mysql_insert_id($dbshop);
	
		//ADD ORDER ITEMS TO SHOP ORDER ITEMS
		for ($i=0; $i<sizeof ($ebay_orders_items); $i++)
		{
			$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
			$row_item=mysql_fetch_array($res_item);
			
			if (isset($currencies[$ebay_orders_items[$i]["Currency_Code"]]))
			{
				$exchange_rate=$currencies[$ebay_orders_items[$i]["Currency_Code"]];
			}
			else
			{
				$exchange_rate=1;
			}
			
			if ($ebay_orders_items[$i]["TransactionPrice"]!=0)
			{
				$net=round(($ebay_orders_items[$i]["TransactionPrice"]/$ust), 2);
			}
			else
			{
				$net=0;
			}
			
			
			q("INSERT INTO shop_orders_items10 (order_id, foreign_transactionID, item_id, amount, price, netto, Currency_Code, exchange_rate_to_EUR) VALUES (".$order_id.", '".$ebay_orders_items[$i]["TransactionID"]."', ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", ".$net.", '".$ebay_orders_items[$i]["Currency_Code"]."', ".$exchange_rate.");", $dbshop, __FILE__, __LINE__);
			
		}
		
		
	//************************
	//ADD EVENTS
	//************************	
	
	//ALERT MAIL - EXPRESS SHIPMENT
	
	if (($shippingType==2 ||$shippingType==7) && ($ebay_orders["account_id"]==1 || $ebay_orders["account_id"]==2))
	{
	if ($ebay_orders["account_id"]==1) $reciever="ebay@mapco.de";
	if ($ebay_orders["account_id"]==2) $reciever="ebay@ihr-autopartner.com";
	
	
		$subject = 'NEUE EXPRESSBESTELLUNG bei eBay!!!!!!';
	
		$msg='<p>Es ist eine neue Express-Bestellung bei eBay eingegangen.<p>';
		$msg.='<p>eBay-Mitgliedsname: <b>'.$ebay_orders["BuyerUserID"].'</b></p>';
		$msg.='<p>Käufer E-Mailadresse: <b>'.$ebay_orders["BuyerEmail"].'</b></p>';
		$msg.='<p>eBay-Verkaufsprotokollnummer: <b>'.$ebay_orders["ShippingDetailsSellingManagerSalesRecordNumber"].'</b></p>';
		$msg.='<p>Bestellte Artikel: <br />';
		foreach ($ebay_orders_items as $ebay_orders_item)
		{
			$msg.=$ebay_orders_item["QuantityPurchased"].'x '.$ebay_orders_item["ItemSKU"].' '.$ebay_orders_item["ItemTitle"].' <small>('.$ebay_orders_item["ItemItemID"].')</small><br />';
		}
		
		SendMail($reciever, "Bestellmanagement-System <noreply@mapco.de>", $subject, $msg);
		SendMail("nputzing@mapco.de", "Bestellmanagement-System <noreply@mapco.de>", $subject, $msg);

		
		//$response=post(PATH."soa/", array("API" => "crm", "Action" => "AlertMail_ExpressShipment", "OrderID" => $order_id));
	}


		
	} // MODE ADD
	
//*****************************************************************
// UPDATE
//*****************************************************************

	if ($_POST["mode"]=="update")
	{
		
		$old_transactions=array();
		$customer_id=0;
		//CHECK FOR OLD TRANSACTIONS
		
		for ($i=0; $i<sizeof($ebay_orders_items); $i++)
		{
			$res_check_items=q("SELECT * FROM shop_orders_items10 WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_check_items)>0)
			{
				$row_check_items=mysql_fetch_array($res_check_items);
				$old_transactions[$i]["TransactionID"]=$row_check_items["foreign_TransactionID"];
				$old_transactions[$i]["order_id"]=$row_check_items["order_id"];
				$res_check_order=q("SELECT * FROM shop_orders10 WHERE id_order = ".$row_check_items["order_id"].";",$dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($res_check_order)>0)
				{

					$row_check_order=mysql_fetch_array($res_check_order);
					$customer_id=$row_check_order["customer_id"];
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
		$res_check=q("SELECT * FROM shop_orders10 WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res_check)>0)
		{
			$update_OrderID=$ebay_orders["OrderID"];
			$shop_order=mysql_fetch_array($res_check);
			$address_updated=$shop_order["bill_address_manual_update"];
			if ($customer_id==0) $customer_id=$shop_order["customer_id"];
		}
		else
		{
			$update_OrderID="0";
			$address_updated=0;
		}
		if ($ebay_orders["OrderID"]!=$update_OrderID)
		{
			for ($i=0; $i<sizeof($old_transactions); $i++)
			{
				if ($old_transactions[$i]["OrderID"]!=0 && $update_OrderID=="0") $update_OrderID=$old_transactions[$i]["OrderID"];
			}
		}
		// CHECK, ob ADRESSE manuell im Backend geändert wurder - > kein Update der Adresse
		
		if ($address_updated==0 && $bill_street1!="")
		//if ($payments_type_id!=4 || $update)
		{

				//UPDATE SHOP_bill_address
			if ($shop_order["bill_adr_id"]!=0 && $customer_id!=0)
			{
				q("UPDATE shop_bill_adr10 SET
				user_id = ".$customer_id.", 
				shop_id = ".$shop["id_shop"].",
				foreign_address_id = '".mysql_real_escape_string($ebay_orders["ShippingAddressAddressID"], $dbshop)."',
				firstname = '".mysql_real_escape_string($bill_firstname,$dbshop)."', 
				lastname = '".mysql_real_escape_string($bill_lastname, $dbshop)."', 
				street = '".mysql_real_escape_string($bill_street1, $dbshop)."', 
				number = '".mysql_real_escape_string($bill_streetNumber, $dbshop)."',
				additional = '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbshop)."', 
				zip = '".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbshop)."', 
				city = '".mysql_real_escape_string($bill_city, $dbshop)."', 
				country = '".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbshop)."', 
				country_id = ".$countries[$ebay_orders["ShippingAddressCountry"]].",
				standard = 1, 
				active = 1 
				WHERE adr_id = ".$shop_order["bill_adr_id"].";",$dbshop, __FILE__, __LINE__);
				
				echo "UPDATE ADDRESS ID:";
				echo $address_id=$shop_order["bill_adr_id"];
			}
			
			if ($shop_order["bill_adr_id"]==0 && $customer_id!=0)
			{
				$res=q("INSERT INTO shop_bill_adr10 (
					user_id, 
					shop_id, 
					foreign_address_id, 
					firstname, 
					lastname, 
					street, 
					number, 
					additional, 
					zip, 
					city, 
					country, 
					country_id, 
					standard, 
					active
				) VALUES (
					".$customer_id.", 
					".$shop["id_shop"].", 
					'".mysql_real_escape_string($ebay_orders["ShippingAddressAddressID"], $dbshop)."', 
					'".mysql_real_escape_string($bill_firstname, $dbshop)."', 
					'".mysql_real_escape_string($bill_lastname , $dbshop)."', 
					'".mysql_real_escape_string($bill_street1, $dbshop)."', 
					'".mysql_real_escape_string($bill_streetNumber, $dbshop)."', 
					'".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbshop)."', 
					'".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbshop)."', 
					'".mysql_real_escape_string($bill_city, $dbshop)."', 
					'".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbshop)."', 
					".$countries[$ebay_orders["ShippingAddressCountry"]].", 
					1,
					1 
				);", $dbshop, __FILE__, __LINE__);
				echo "INSERT ADDRESS ID:";
				 echo $address_id=mysql_insert_id($dbshop);
	
			}
	if (!isset($address_id)) $address_id=0;
			//UPDATE ORDER
			q("UPDATE shop_orders10 SET 
			foreign_OrderID = '".mysql_real_escape_string($ebay_orders["OrderID"],$dbshop)."', 
			usermail = '".mysql_real_escape_string($usermail,$dbshop)."', 
			userphone = '".mysql_real_escape_string($bill_phone,$dbshop)."', 
			bill_firstname = '".mysql_real_escape_string($bill_firstname,$dbshop)."', 
			bill_lastname = '".mysql_real_escape_string($bill_lastname, $dbshop)."', 
			bill_zip = '".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbshop)."', 
			bill_city = '".mysql_real_escape_string($bill_city, $dbshop)."', 
			bill_street = '".mysql_real_escape_string($bill_street1, $dbshop)."', 
			bill_number = '".mysql_real_escape_string($bill_streetNumber, $dbshop)."', 
			bill_additional = '".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbshop)."', 
			bill_country = '".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbshop)."', 
			bill_country_code = '".mysql_real_escape_string($ebay_orders["ShippingAddressCountry"], $dbshop)."', 
			bill_adr_id = ".$address_id.",
			shipping_costs = ".$ebay_orders["ShippingServiceSelectedShippingServiceCost"].", 
			shipping_type_id =".$shippingType.", 
			shipping_details = '".mysql_real_escape_string($shipping_details, $dbshop)."', 
			Payments_TransactionStateDate = ".$paymentdate.", 
			Payments_Type = '".mysql_real_escape_string($ebay_orders["CheckoutStatusPaymentMethod"], $dbshop)."', 
			payments_type_id = ".$payments_type_id.", 
			Payments_TransactionState = '".mysql_real_escape_string($paymentstatus, $dbshop)."', 
			lastmod = ".time().", 
			lastmod_user = ".$_SESSION["id_user"]."  
			WHERE foreign_OrderID = '".$update_OrderID."' ;",$dbshop, __FILE__, __LINE__);
			
		}
		// Adresse wurde manuell geändert -> kein Überschreiben der Adressdaten
		else
		{
			q("UPDATE shop_orders10 SET 
			foreign_OrderID = '".mysql_real_escape_string($ebay_orders["OrderID"],$dbshop)."', 
			shipping_costs = ".$ebay_orders["ShippingServiceSelectedShippingServiceCost"].", 
			shipping_type_id =".$shippingType.", 
			shipping_details = '".mysql_real_escape_string($shipping_details, $dbshop)."', 
			Payments_TransactionStateDate = ".$paymentdate.", 
			Payments_Type = '".mysql_real_escape_string($ebay_orders["CheckoutStatusPaymentMethod"], $dbshop)."', 
			payments_type_id = ".$payments_type_id.", 
			Payments_TransactionState = '".mysql_real_escape_string($paymentstatus, $dbshop)."', 
			lastmod = ".time().", 
			lastmod_user = ".$_SESSION["id_user"]."  
			WHERE foreign_OrderID = '".$update_OrderID."' ;",$dbshop, __FILE__, __LINE__);
		}
		// SET status_id
		if ($status_id == 4)
		{
			q("UPDATE shop_orders10 SET status_id = ".$status_id." , status_date = ".$status_date." WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
		}
		elseif ($status_id == 7 || $status_id == 1)
		{
			$res_shop_order=q("SELECT * FROM shop_orders10 WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
			$row_shop_order=mysql_fetch_array($res_shop_order);
			if ($row_shop_order["status_id"]==1 || $row_shop_order["status_id"]==4) 
			{
				q("UPDATE shop_orders10 SET status_id = ".$status_id." , status_date = ".$status_date." WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
			}
		}
		
		//check ob Order zusammengefasst wurde
		if (strpos($ebay_orders["OrderID"],"-")===true)
		{
			//UNSET COMBINED ORDERS
			q("UPDATE shop_orders10 SET combined_with = 0 WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
		}
		
		
			
		//GET id_order
		$res=q("SELECT id_order from shop_orders10 WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
		$row=mysql_fetch_array($res);
		$id_order=$row["id_order"];
			
		//DELETE other OLD ORDERS	
		for ($i=0; $i<sizeof($old_transactions); $i++)
		{
			if ($old_transactions[$i]["OrderID"]!=$update_OrderID && $old_transactions[$i]["OrderID"]!="")
			{
				q("DELETE FROM shop_orders10 WHERE foreign_OrderID ='".$old_transactions[$i]["OrderID"]."' AND shop_id = ".$shop["id_shop"].";", $dbshop, __FILE__, __LINE__);
			}
		}
			

		//UPDATE/ADD ITEMS
		for ($i=0; $i<sizeof($ebay_orders_items); $i++)
		{
			//check for existing Transaction
			$res_check=q("SELECT * FROM shop_orders_items10 WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)>0) 
			{
				//UPDATE TRANSACTION
				$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row_item=mysql_fetch_array($res_item);
				
				if ($ebay_orders_items[$i]["TransactionPrice"]!=0)
				{
					$net=round(($ebay_orders_items[$i]["TransactionPrice"]/$ust), 2);
				}
				else
				{
					$net=0;
				}

				q("UPDATE shop_orders_items10 SET order_id = ".$id_order.", item_id = ".$row_item["id_item"].", amount = ".$ebay_orders_items[$i]["QuantityPurchased"].", price = ".$ebay_orders_items[$i]["TransactionPrice"].", netto = ".$net." WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__); 
				
			}
			else
			{
				//ADD TRANSACION
				$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row_item=mysql_fetch_array($res_item);
				
				if (isset($currencies[$ebay_orders_items[$i]["Currency_Code"]]))
				{
					$exchange_rate=$currencies[$ebay_orders_items[$i]["Currency_Code"]];
				}
				else
				{
					$exchange_rate=1;
				}
				
				if ($ebay_orders_items[$i]["TransactionPrice"]!=0)
				{
					$net=round(($ebay_orders_items[$i]["TransactionPrice"]/$ust), 2);
				}
				else
				{
					$net=0;
				}
	
				
				q("INSERT INTO shop_orders_items10 (order_id, foreign_TransactionID, item_id, amount, price, netto, Currency_Code, exchange_rate_to_EUR, customer_vehicle_id) VALUES (".$id_order.", '".$ebay_orders_items[$i]["TransactionID"]."', ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", ".$net.", '".$ebay_orders_items[$i]["Currency_Code"]."', ".$exchange_rate.", 0);", $dbshop, __FILE__, __LINE__);	
				
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
				q("UPDATE shop_orders_items10 SET order_id=0 WHERE foreign_TransactionID = '".$old_transactions[$i]["TransactionID"]."';", $dbshop, __FILE__, __LINE__);
			}
		}


//***********************************************************************************************************************


			//GET ACCOUNT DATA
		$res_account=q("SELECT * FROM crm_customer_accounts3 WHERE shop_user_id = '".$ebay_orders["BuyerUserID"]."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($res_account)>0)
		{
			$row_account=mysql_fetch_array($res_account);

			//UPDATE ADDRESS	
				//CHECK IF ADDRESS IS KNOWN
				//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRESSE VORHANDEN
			if ($ebay_orders["ShippingAddressAddressID"]!="")
			{
				$res_check=q("SELECT * FROM shop_bill_adr10 WHERE foreign_address_id = ".$ebay_orders["ShippingAddressAddressID"]." AND shop_id = ".$shop["id_shop"].";", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($res_check)==0)
				{
				$res=q("INSERT INTO shop_bill_adr10 (
				user_id, 
				shop_id, 
				foreign_address_id, 
				firstname, 
				lastname, 
				street, 
				number, 
				additional, 
				zip, 
				city, 
				country, 
				country_id, 
				standard, 
				active
				) VALUES (
				".$row_account["cms_user_id"].", 
				".$shop["id_shop"].", 
				'".mysql_real_escape_string($ebay_orders["ShippingAddressAddressID"], $dbshop)."', 
				'".mysql_real_escape_string($bill_firstname, $dbshop)."', 
				'".mysql_real_escape_string($bill_lastname , $dbshop)."', 
				'".mysql_real_escape_string($bill_street1, $dbshop)."', 
				'".mysql_real_escape_string($bill_streetNumber, $dbshop)."', 
				'".mysql_real_escape_string($ebay_orders["ShippingAddressStreet2"], $dbshop)."', 
				'".mysql_real_escape_string($ebay_orders["ShippingAddressPostalCode"], $dbshop)."', 
				'".mysql_real_escape_string($bill_city, $dbshop)."', 
				'".mysql_real_escape_string($ebay_orders["ShippingAddressCountryName"], $dbshop)."', 
				".$countries[$ebay_orders["ShippingAddressCountry"]].", 
				1,
				1 
				);", $dbshop, __FILE__, __LINE__);
				}
			}
			
			//CHECK IF PHONE IS KNOWN
			$res_check=q("SELECT * FROM crm_numbers3 WHERE cms_user_id = ".$row_account["cms_user_id"]." AND number = '".$ebay_orders["ShippingAddressPhone"]."' ;", $dbweb, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)==0)
			{
				$res_ins=q("INSERT INTO crm_numbers3 (cms_user_id, shop_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$row_account["cms_user_id"].", ".$shop["id_shop"].", 1, '".mysql_real_escape_string($ebay_orders["ShippingAddressPhone"],$dbweb)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			}

		}
		
	}
	
	

	echo "<crm_add_customer_listResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_add_customer_listResponse>";

?>