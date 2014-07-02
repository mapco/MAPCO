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
		echo '		<shortMsg>Ebay Order ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID der EbayOrder angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		exit;
	}
	$res_orders=q("SELECT * FROM ebay_orders WHERE id_order = ".$_POST["EbayOrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_orders)==0)
	{
		echo '<crm_add_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>EbayOrder nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur angegebenen EbayOrder ID konnte keine Order gefunden werden</longMsg>'."\n";
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
		echo '		<shortMsg>Keine Bestellpositionen zur EbayOrder gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine Bestellpositionen zur EbayOrder gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		exit;
	}
	$ebay_orders_items=array();
	while ($row_orders_items=mysqli_fetch_array($res_orders_items))
	{
		$ebay_orders_items[sizeof($ebay_orders_items)]=$row_orders_items;
	}
	
	// GET PARENT ACCOUNT
	$res_shop=q("SELECT * FROM shop_shops WHERE account_id = ".$ebay_orders["account_id"]." AND shop_type = 2;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_shop)==0)
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
	$shop=mysqli_fetch_array($res_shop);
	//GET SITE_ID

	$res_shop=q("SELECT * FROM shop_shops WHERE id_shop = ".$shop["parent_shop_id"].";", $dbshop, __FILE__, __LINE__);
	
	if (mysqli_num_rows($res_shop)==0)
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
		$shop_site=mysqli_fetch_array($res_shop);
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
	while ($row_shiptype=mysqli_fetch_array($res_shiptype))
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
	
	//SHIPPING COSTS NET
	if ($ebay_orders["ShippingServiceSelectedShippingServiceCost"]==0)
	{
		$shipping_costs_net=0;
	}
	else
	{
		$shipping_costs_net=round(($ebay_orders["ShippingServiceSelectedShippingServiceCost"]/$ust),2);
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
	
	//GET COUNTRIES
	$countries=array();
	$res_countries=q("SELECT * FROM shop_countries;", $dbshop, __FILE__, __LINE__);
	while ($row_countries=mysqli_fetch_array($res_countries))
	{
		$countries[$row_countries["country_code"]]=$row_countries["id_country"];
		
	}

	//GET CURRENCY-Exchange_rate
	$currencies=array();
	$res_currencies=q("SELECT * FROM shop_currencies;", $dbshop, __FILE__, __LINE__);
	while ($row_currencies=mysqli_fetch_array($res_currencies))
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
		if (mysqli_num_rows($res_check)>0)
		{
			$row_check=mysqli_fetch_array($res_check);
			$shop_user_id=$row_check["shop_user_id"];
			$cms_user_id=$row_check["cms_user_id"];
		}

		//CHECK FOR KNOWN EMAIL
		$number_id=0;
		$number_account_id=0;
		if ($usermail!="Invalid Request" && $usermail!="" )
		{
			$res_check=q("SELECT * FROM crm_numbers3 WHERE number = '".$usermail."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)>0)
			{
				$row_check=mysqli_fetch_array($res_check);
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
			while ($row_CMS=mysqli_fetch_array($res_CMS))
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

		$res_ins=q("INSERT INTO cms_users (
			shop_id, 
			site_id, 
			username, 
			usermail, 
			name, 
			origin, 
			password, 
			user_token, 
			userrole_id, 
			language_id, 
			active, 
			firstmod, 
			firstmod_user, 
			lastmod, 
			lastmod_user
		) VALUES (
			".$shop["parent_shop_id"].", 
			".$site_id.", 
			'".mysqli_real_escape_string($dbweb, $cms_username)."', 
			'".mysqli_real_escape_string($dbweb, $usermail)."', 
			'".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressName"])."', 
			'".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressCountry"])."',
			'".mysqli_real_escape_string($dbweb, createPassword(8))."', 
			'".mysqli_real_escape_string($dbweb, createPassword(50))."',
			5,
			1,
			1, 
			".time().",
			".$_SESSION["id_user"].", 
			".time().",
			".$_SESSION["id_user"]."
		);", $dbweb, __FILE__, __LINE__);
		$cms_user_id=mysqli_insert_id($dbweb);

		}
		
		//ADD ACCOUNT
		if ($cms_user_id!=0 && $shop_user_id==0)
		{
//		echo "INSERT ACCOUNT";	
			$res_ins=q("INSERT INTO crm_customer_accounts3 (
				cms_user_id, 
				shop_id, 
				shop_user_id, 
				firstmod, 
				firstmod_user, 
				lastmod, 
				lastmod_user
			) VALUES (
				".$cms_user_id.", 
				".$shop["id_shop"].",
				'".mysqli_real_escape_string($dbweb, $ebay_orders["BuyerUserID"])."', 
				".time().", 
				".$_SESSION["id_user"].", 
				".time().", 
				".$_SESSION["id_user"]."
			);", $dbweb, __FILE__, __LINE__);
			
			$id_customer_account=mysqli_insert_id($dbweb);
		}
			

		
		//CHECK FOR KNOWN ADDRESS ID

		$address_id=0;
		$address_account_id=0;
		//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRRESSID VORHANDEN
		if ($ebay_orders["ShippingAddressAddressID"]!="" && $cms_user_id!=0)
		{

			$res_check=q("SELECT * FROM shop_bill_adr WHERE foreign_address_id = ".$ebay_orders["ShippingAddressAddressID"]." AND shop_id = ".$shop["id_shop"]." AND user_id = ".$cms_user_id.";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)>0)
			{
				$row_check=mysqli_fetch_array($res_check);
				$address_id=$row_check["adr_id"];
				//$address_account_id=$row_check["adr_id"];
			//	if ($cms_user_id==0) $cms_user_id=$row_check["user_id"];
			}
			//$address_id=0;
		}
		// CHECK FOR KNOWN ADDRESS
		if ($cms_user_id!=0 && $address_id==0)
		{
			$res_check=q("SELECT * FROM shop_bill_adr WHERE user_id = ".$cms_user_id." AND shop_id = ".$shop["id_shop"].";", $dbshop, __FILE__, __LINE__);
			while ($row_check=mysqli_fetch_array($res_check) && $address_id==0)
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
				$res=q("INSERT INTO shop_bill_adr (
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
					".$cms_user_id.", 
					".$shop["id_shop"].", 
					'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressAddressID"])."', 
					'".mysqli_real_escape_string($dbshop, $bill_firstname)."', 
					'".mysqli_real_escape_string($dbshop, $bill_lastname)."', 
					'".mysqli_real_escape_string($dbshop, $bill_street1)."', 
					'".mysqli_real_escape_string($dbshop, $bill_streetNumberp)."', 
					'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressStreet2"])."', 
					'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressPostalCode"])."', 
					'".mysqli_real_escape_string($dbshop, $bill_city)."', 
					'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCountryName"])."', 
					".$countries[$ebay_orders["ShippingAddressCountry"]].", 
					1,
					1 
				);", $dbshop, __FILE__, __LINE__);
				
				$address_id=mysqli_insert_id($dbshop);
			}
		}
		
		//ADD MAIL
		if ($cms_user_id!=0 && $number_id==0)
		{
			if ($usermail!="Invalid Request" && $usermail!="")
			{
				$res_ins=q("INSERT INTO crm_numbers3 (
					cms_user_id, 
					shop_id, 
					number_type, 
					number, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$cms_user_id.", 
					".$shop["id_shop"].", 
					7, 
					'".mysqli_real_escape_string($dbweb,$usermail)."', 
					".time().", 
					".$_SESSION["id_user"].", 
					".time().", 
					".$_SESSION["id_user"]."
				);", $dbweb, __FILE__, __LINE__);
				
				$number_id=mysqli_insert_id($dbweb);
			}
		}

		//CHECK FOR KNOWN PHONE
		$phone_id=0;
		$number_shop_id=0;
		if ($ebay_orders["ShippingAddressPhone"]!="Invalid Request" && $ebay_orders["ShippingAddressPhone"]!="")
		{
			$res_check=q("SELECT * FROM crm_numbers3 WHERE number = '".$ebay_orders["ShippingAddressPhone"]."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)>0)
			{
				$row_check=mysqli_fetch_array($res_check);
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
				$res_ins=q("INSERT INTO crm_numbers3 (
					cms_user_id, 
					shop_id, 
					number_type, 
					number, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$cms_user_id.", 
					".$shop["id_shop"].", 
					1, 
					'".mysqli_real_escape_string($dbweb, $ebay_orders["ShippingAddressPhone"])."',
					 ".time().", 
					 ".$_SESSION["id_user"].", 
					 ".time().",
					 ".$_SESSION["id_user"]."
				);", $dbweb, __FILE__, __LINE__);
				
				$phone_id=mysqli_insert_id($dbweb);
			}
		}



//INSERT SHOP ORDER ++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$IPNs=array();
		$paypalpaymentnote="";
		for ($i=0; $i<sizeof ($ebay_orders_items); $i++)
		{
			$res_IPN=q("SELECT * FROM payment_notifications3 WHERE orderTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' OR parentPaymentTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ORDER BY payment_date DESC;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_IPN)>0)
			{
	//			echo "IPN COUNT: ".mysqli_num_rows($res_IPN)."++++";
				while ($row_IPN=mysqli_fetch_array($res_IPN))
				{
					$IPNs[sizeof($IPNs)]=$row_IPN;
					if ($paypalpaymentnote=="")	$paypalpaymentnote=$row_IPN["payment_note"];
				}
			}
		}
		
		
		//ADD ORDER TO SHOP ORDER
		
		$fieldlist=array();
		//BASISFELDER FÜR API-AUFRUF
		$fieldlist["API"]="shop";
		$fieldlist["APIRequest"]="OrderAdd";
		$fieldlist["mode"]="new";
		
		//FIELDLIST FOR INSERT
		$fieldlist["shop_id"]=$shop["id_shop"];
		$fieldlist["ordertype_id"]=1;		// ONLINESHOP BESTELLUNG
		$fieldlist["status_id"]=$status_id;
		$fieldlist["status_date"]=$status_date;
		$fieldlist["Currency_Code"]=$ebay_orders["Currency_Code"];
		$fieldlist["foreign_OrderID"]=$ebay_orders["OrderID"];
		$fieldlist["customer_id"]=$cms_user_id;
		$fieldlist["usermail"]=$usermail;
		$fieldlist["userphone"]=$bill_phone;
		$fieldlist["bill_firstname"]=$bill_firstname;
		$fieldlist["bill_lastname"]=$bill_lastname;
		$fieldlist["bill_zip"]=trim($ebay_orders["ShippingAddressPostalCode"]);
		$fieldlist["bill_city"]=$bill_city;
		$fieldlist["bill_street"]=$bill_street1;
		$fieldlist["bill_number"]=$bill_streetNumber;
		$fieldlist["bill_additional"]=$ebay_orders["ShippingAddressStreet2"];
		$fieldlist["bill_country"]=$ebay_orders["ShippingAddressCountryName"];
		$fieldlist["bill_country_code"]=$ebay_orders["ShippingAddressCountry"];
	/*	
		$fieldlist["ship_firstname"]=$bill_firstname;
		$fieldlist["ship_lastname"]=$bill_lastname;
		$fieldlist["ship_zip"]=trim($ebay_orders["ShippingAddressPostalCode"]);
		$fieldlist["ship_city"]=$bill_city;
		$fieldlist["ship_street"]=$bill_street1;
		$fieldlist["ship_number"]=$bill_streetNumber;
		$fieldlist["ship_additional"]=$ebay_orders["ShippingAddressStreet2"];
		$fieldlist["ship_country"]=$ebay_orders["ShippingAddressCountryName"];
		$fieldlist["ship_country_code"]=$ebay_orders["ShippingAddressCountry"];
	*/	
		$fieldlist["shipping_costs"]=$ebay_orders["ShippingServiceSelectedShippingServiceCost"];
		$fieldlist["shipping_type_id"]=$shippingType;
		$fieldlist["shipping_details"]=$shipping_details;
		$fieldlist["Payments_TransactionStateDate"]=$paymentdate;
		$fieldlist["Payments_Type"]=$ebay_orders["CheckoutStatusPaymentMethod"];
		$fieldlist["payments_type_id"]=$payments_type_id;
		$fieldlist["Payments_TransactionState"]=$paymentstatus;
		$fieldlist["Payments_TransactionID"]=$IPNs[0]["paymentTransactionID"];
		$fieldlist["PayPal_BuyerNote"]=$paypalpaymentnote;
		$fieldlist["partner_id"]=0;
		$fieldlist["bill_adr_id"]=$address_id;
		//$fieldlist["ship_adr_id"]=$address_id;
		$fieldlist["firstmod"]=$ebay_orders["CreatedTimeTimestamp"];
		$fieldlist["shipping_net"]=$shipping_costs_net;
		
		$responseXML=post("https://www.mapco.de/soa2/index.php", $fieldlist);

		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			echo '<import_ebayOrderDataResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Die zurückgelieferten XML-Daten sind nicht valide und können deshalb nicht ausgewertet werden. Service gestoppt.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</import_ebayOrderDataResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response->Ack[0]=="Success")
		{
			$order_id=$response->id_order[0];
		}
		else
		{
			echo '<import_ebayOrderDataResponse>'."\n";
			echo '	<Ack>'.$response->Ack[0].'</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>'.$responseXML.'</shortMsg>'."\n";
			echo '		<longMsg>FEHLER.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</import_ebayOrderDataResponse>'."\n";
			exit;
		}
		
		unset($response);
		unset($responseXML);
		
	echo $order_id;	

		//ADD ORDER ITEMS TO SHOP ORDER ITEMS
		for ($i=0; $i<sizeof ($ebay_orders_items); $i++)
		{
			$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
			$row_item=mysqli_fetch_array($res_item);
			
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

			/*			
			q("INSERT INTO shop_orders_items (order_id, foreign_transactionID, item_id, amount, price, netto, Currency_Code, exchange_rate_to_EUR) VALUES (".$order_id.", '".$ebay_orders_items[$i]["TransactionID"]."', ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", ".$net.", '".$ebay_orders_items[$i]["Currency_Code"]."', ".$exchange_rate.");", $dbshop, __FILE__, __LINE__);
			*/
			
			$fieldlist=array();
			//BASISFELDER FÜR API-AUFRUF
			$fieldlist["API"]="shop";
			$fieldlist["APIRequest"]="OrderItemAdd";
			$fieldlist["mode"]="new";
		
			//FIELDLIST FOR INSERT
			$fieldlist["order_id"]=$order_id;
			$fieldlist["foreign_transactionID"]=$ebay_orders_items[$i]["TransactionID"];
			$fieldlist["item_id"]=$row_item["id_item"];
			$fieldlist["amount"]=$ebay_orders_items[$i]["QuantityPurchased"];
			$fieldlist["price"]=$ebay_orders_items[$i]["TransactionPrice"];
			$fieldlist["netto"]=$net;
			$fieldlist["Currency_Code"]=$ebay_orders_items[$i]["Currency_Code"];
			$fieldlist["exchange_rate_to_EUR"]=$exchange_rate;

			$responseXML=post("https://www.mapco.de/soa2/index.php", $fieldlist);
	echo $responseXML;
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				echo '<import_ebayOrderDataResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
				echo '		<longMsg>Die zurückgelieferten XML-Daten sind nicht valide und können deshalb nicht ausgewertet werden. Service gestoppt.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</import_ebayOrderDataResponse>'."\n";
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]=="Success")
			{

			}
			else
			{
				echo '<import_ebayOrderDataResponse>'."\n";
				echo '	<Ack>'.$response->Ack[0].'</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>FEHLER.</shortMsg>'."\n";
				echo '		<longMsg>FEHLER.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</import_ebayOrderDataResponse>'."\n";
				exit;
			}
			
			unset($response);
			unset($responseXML);
			
			
		}
		
	//************************
	//ADD EVENTS
	//************************	
	
	//ALERT MAIL - EXPRESS SHIPMENT
	
	if (($shippingType==2 ||$shippingType==7) && ($ebay_orders["account_id"]==1 || $ebay_orders["account_id"]==2 || $ebay_orders["account_id"]==8))
	{
	if ($ebay_orders["account_id"]==1) $reciever="ebay@mapco.de";
	if ($ebay_orders["account_id"]==2) $reciever="ebay@ihr-autopartner.com";
	if ($ebay_orders["account_id"]==8) $reciever="kfroehlich@mapco.de";
	
		$subject = 'NEUE EXPRESSBESTELLUNG bei eBay!!!!!!';
	
		$msg='<p>Es ist eine neue Express-Bestellung bei eBay eingegangen.<p>';
		$msg.='<p>eBay-Mitgliedsname: <b>'.$ebay_orders["BuyerUserID"].'</b></p>';
		$msg.='<p>Käufer E-Mailadresse: <b>'.$ebay_orders["BuyerEmail"].'</b></p>';
		$msg.='<p>eBay-Verkaufsprotokollnummer: <b>'.$ebay_orders["ShippingDetailsSellingManagerSalesRecordNumber"].'</b></p>';
		$msg.='<p>Bestellte Artikel: <br />';
		foreach ($ebay_orders_items as $ebay_orders_item)
		{
			$msg.=$ebay_orders_items["QuantityPurchased"].'x '.$ebay_orders_items["ItemSKU"].' '.$ebay_orders_items["ItemTitle"].' <small>('.$ebay_orders_items["ItemItemID"].')</small><br />';
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
			$res_check_items=q("SELECT * FROM shop_orders_items WHERE foreign_transactionID = '".$ebay_orders_items[$i]["TransactionID"]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check_items)>0)
			{
				$row_check_items=mysqli_fetch_array($res_check_items);
				$res_check_order=q("SELECT * FROM shop_orders WHERE id_order = ".$row_check_items["order_id"].";",$dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_check_order)>0)
				{
					$row_check_order=mysqli_fetch_array($res_check_order);
					$customer_id=$row_check_order["customer_id"];
					$old_transactions[$i]["OrderID"]=$row_check_order["foreign_OrderID"];
					$old_transactions[$i]["TransactionID"]=$row_check_items["foreign_TransactionID"];
					$old_transactions[$i]["order_id"]=$row_check_items["order_id"];

				}
				else
				{
					$old_transactions[$i]["TransactionID"]="0";
					$old_transactions[$i]["order_id"]=0;
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
		
		$IPNs=array();
		$paypalpaymentnote="";
		for ($i=0; $i<sizeof ($ebay_orders_items); $i++)
		{
			$res_IPN=q("SELECT * FROM payment_notifications3 WHERE orderTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' OR parentPaymentTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ORDER BY payment_date DESC;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_IPN)>0)
			{
	//			echo "IPN COUNT: ".mysqli_num_rows($res_IPN)."++++";
				while ($row_IPN=mysqli_fetch_array($res_IPN))
				{
					$IPNs[sizeof($IPNs)]=$row_IPN;
					if ($paypalpaymentnote=="")	$paypalpaymentnote=$row_IPN["payment_note"];
				}
			}
		}

		
		//FESTLEGEN DER ZU UPDATENDEN ORDER
			//Search for OrderID
		$res_check=q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)>0)
		{
			$update_OrderID=$ebay_orders["OrderID"];
			$shop_order=mysqli_fetch_array($res_check);
			$address_updated=$shop_order["bill_address_manual_update"];
			$update_orderid=$shop_order["id_order"];
			if ($customer_id==0) $customer_id=$shop_order["customer_id"];
		}
		else
		{
			$update_orderid=0;
			$update_OrderID="0";
			$address_updated=0;
		}
		if ($update_orderid==0)
		{
			for ($i=0; $i<sizeof($old_transactions); $i++)
			{
				if ($old_transactions[$i]["order_id"]!=0 && $update_orderid==0) $update_orderid=$old_transactions[$i]["order_id"];
			}
		}
		
// F E H L E R >>>>>>>>>>>>>>>>>>>>>>>>	
		if ($update_orderid==0) exit;
// F E H L E R >>>>>>>>>>>>>>>>>>>>>>>>	
		
		// CHECK, ob ADRESSE manuell im Backend geändert wurder - > kein Update der Adresse
		
		if ($address_updated==0 && $bill_street1!="")
		//if ($payments_type_id!=4 || $update)
		{

				//UPDATE SHOP_bill_address
			if (isset($shop_order) && $shop_order["bill_adr_id"]!=0 && $customer_id!=0)
			{
				
				q("UPDATE shop_bill_adr SET
				user_id = ".$customer_id.", 
				shop_id = ".$shop["id_shop"].",
				foreign_address_id = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressAddressID"])."',
				firstname = '".mysqli_real_escape_string($dbshop, $bill_firstname)."', 
				lastname = '".mysqli_real_escape_string($dbshop, $bill_lastname)."', 
				street = '".mysqli_real_escape_string($dbshop, $bill_street1)."', 
				number = '".mysqli_real_escape_string($dbshop, $bill_streetNumber)."',
				additional = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressStreet2"])."', 
				zip = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressPostalCode"])."', 
				city = '".mysqli_real_escape_string($dbshop, $bill_city)."', 
				country = '".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCountryName"])."', 
				country_id = ".$countries[$ebay_orders["ShippingAddressCountry"]].",
				standard = 1, 
				active = 1 
				WHERE adr_id = ".$shop_order["bill_adr_id"].";",$dbshop, __FILE__, __LINE__);
				
				echo "UPDATE ADDRESS ID:";
				
				echo $address_id=$shop_order["bill_adr_id"];
			}
			
			if (isset($shop_order) && $shop_order["bill_adr_id"]==0 && $customer_id!=0)
			{
				
				$res=q("INSERT INTO shop_bill_adr (
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
					'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressAddressID"])."', 
					'".mysqli_real_escape_string($dbshop, $bill_firstname)."', 
					'".mysqli_real_escape_string($dbshop, $bill_lastname)."', 
					'".mysqli_real_escape_string($dbshop, $bill_street1)."', 
					'".mysqli_real_escape_string($dbshop, $bill_streetNumber)."', 
					'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressStreet2"])."', 
					'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressPostalCode"])."', 
					'".mysqli_real_escape_string($dbshop, $bill_city)."', 
					'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCountryName"])."', 
					".$countries[$ebay_orders["ShippingAddressCountry"]].", 
					1,
					1 
				);", $dbshop, __FILE__, __LINE__);
				echo "INSERT ADDRESS ID:";
				 echo $address_id=mysqli_insert_id($dbshop);
				
			}
			
			if (!isset($address_id)) $address_id=0;

		}
		// Adresse wurde manuell geändert -> kein Überschreiben der Adressdaten
	
			$fieldlist=array();
			//BASISFELDER FÜR API-AUFRUF
			$fieldlist["API"]="shop";
			$fieldlist["APIRequest"]="OrderUpdate";
			
			//FIELDLIST FOR UPDATE
			$fieldlist["SELECTOR_id_order"]=$update_orderid;
			$fieldlist["foreign_OrderID"]=$ebay_orders["OrderID"];
			$fieldlist["Payments_TransactionStateDate"]=$paymentdate;
			$fieldlist["Payments_TransactionID"]=$IPNs[0]["paymentTransactionID"];
			$fieldlist["PayPal_BuyerNote"]=$paypalpaymentnote;
			$fieldlist["Payments_Type"]=$ebay_orders["CheckoutStatusPaymentMethod"];
			$fieldlist["payments_type_id"]=$payments_type_id;
			$fieldlist["Payments_TransactionState"]=$paymentstatus;
			$fieldlist["lastmod"]=time();
			$fieldlist["lastmod_user"]=$_SESSION["id_user"];
			
			if ($address_updated==0 && $bill_street1!="")
			{
				$fieldlist["usermail"]=$usermail;
				$fieldlist["userphone"]=$bill_phone;
				
				$fieldlist["bill_firstname"]=$bill_firstname;
				$fieldlist["bill_lastname"]=$bill_lastname;
				$fieldlist["bill_zip"]=trim($ebay_orders["ShippingAddressPostalCode"]);
				$fieldlist["bill_city"]=$bill_city;
				$fieldlist["bill_street"]=$bill_street1;
				$fieldlist["bill_number"]=$bill_streetNumber;
				$fieldlist["bill_additional"]=$ebay_orders["ShippingAddressStreet2"];
				$fieldlist["bill_country"]=$ebay_orders["ShippingAddressCountryName"];
				$fieldlist["bill_country_code"]=$ebay_orders["ShippingAddressCountry"];
			/*	
				$fieldlist["ship_firstname"]=$bill_firstname;
				$fieldlist["ship_lastname"]=$bill_lastname;
				$fieldlist["ship_zip"]=trim($ebay_orders["ShippingAddressPostalCode"]);
				$fieldlist["ship_city"]=$bill_city;
				$fieldlist["ship_street"]=$bill_street1;
				$fieldlist["ship_number"]=$bill_streetNumber;
				$fieldlist["ship_additional"]=$ebay_orders["ShippingAddressStreet2"];
				$fieldlist["ship_country"]=$ebay_orders["ShippingAddressCountryName"];
				$fieldlist["ship_country_code"]=$ebay_orders["ShippingAddressCountry"];
			*/	
				$fieldlist["shipping_costs"]=$ebay_orders["ShippingServiceSelectedShippingServiceCost"];
				$fieldlist["shipping_type_id"]=$shippingType;
				$fieldlist["shipping_details"]=$shipping_details;
				$fieldlist["shipping_net"]=$shipping_costs_net;
				if ($address_id!=0)	$fieldlist["bill_adr_id"]=$address_id;
				//if ($address_id!=0)	$fieldlist["ship_adr_id"]=$address_id;
				
			}
			//STATUS FESTLEGEN
			if ($status_id == 4)
			{
				$fieldlist["status_id"]=$status_id;
			}
			elseif ($status_id == 7 || $status_id == 1)
			{
				$res_shop_order=q("SELECT * FROM shop_orders WHERE id_order = ".$update_orderid." ;",$dbshop, __FILE__, __LINE__);
				$row_shop_order=mysqli_fetch_array($res_shop_order);
				if ($row_shop_order["status_id"]==1 || $row_shop_order["status_id"]==4) 
				{
					$fieldlist["status_id"]=$status_id;
				}
			}

			$responseXML=post("https://www.mapco.de/soa2/index.php", $fieldlist);
	echo $responseXML;
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				echo '<import_ebayOrderDataResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
				echo '		<longMsg>Die zurückgelieferten XML-Daten sind nicht valide und können deshalb nicht ausgewertet werden. Service gestoppt.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</import_ebayOrderDataResponse>'."\n";
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]=="Success")
			{

			}
			else
			{
				echo '<import_ebayOrderDataResponse>'."\n";
				echo '	<Ack>'.$response->Ack[0].'</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>FEHLER.</shortMsg>'."\n";
				echo '		<longMsg>FEHLER.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</import_ebayOrderDataResponse>'."\n";
				exit;
			}
			
			unset($response);
			unset($responseXML);

	
	/*	
		//check ob Order zusammengefasst wurde
		if (strpos($ebay_orders["OrderID"],"-")===true)
		{
			//UNSET COMBINED ORDERS
			q("UPDATE shop_orders SET combined_with = 0 WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
		}
	*/
		
		/*	
		//GET id_order
		$res=q("SELECT id_order from shop_orders WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($res);
		$id_order=$row["id_order"];
		*/	
		//DELETE other OLD ORDERS	
		for ($i=0; $i<sizeof($old_transactions); $i++)
		{
			if ($old_transactions[$i]["order_id"]!=$update_orderid && $old_transactions[$i]["order_id"]!=0)
			{
				q("DELETE FROM shop_orders WHERE id_order =".$old_transactions[$i]["order_id"]." AND shop_id = ".$shop["id_shop"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
			}
		}
			

		//UPDATE/ADD ITEMS
		for ($i=0; $i<sizeof($ebay_orders_items); $i++)
		{
			//check for existing Transaction
			$res_check=q("SELECT * FROM shop_orders_items WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)>0) 
			{
				$row_check=mysqli_fetch_array($res_check);
				//UPDATE TRANSACTION
				$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row_item=mysqli_fetch_array($res_item);
				
				if ($ebay_orders_items[$i]["TransactionPrice"]!=0)
				{
					$net=round(($ebay_orders_items[$i]["TransactionPrice"]/$ust), 2);
				}
				else
				{
					$net=0;
				}
				// NUR UPDATE DER order_id NÖTIG, ANDERE DATEN ÄNDERN SICH BEI EBAY NICHT
				//q("UPDATE shop_orders_items SET order_id = ".$id_order.", item_id = ".$row_item["id_item"].", amount = ".$ebay_orders_items[$i]["QuantityPurchased"].", price = ".$ebay_orders_items[$i]["TransactionPrice"].", netto = ".$net." WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__);
				
				//q("UPDATE shop_orders_items SET order_id = ".$id_order." WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__); 
				$fieldlist=array();
				//BASISFELDER FÜR API-AUFRUF
				$fieldlist["API"]="shop";
				$fieldlist["APIRequest"]="OrderItemUpdate";
				
				//FIELDLIST FOR UPDATE
				$fieldlist["order_id"]=$update_orderid;
				$fieldlist["SELECTOR_id"]=$row_check["id"];

				
			}
			else
			{
				//ADD TRANSACION
				$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row_item=mysqli_fetch_array($res_item);
				
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
	
				
				//q("INSERT INTO shop_orders_items (order_id, foreign_TransactionID, item_id, amount, price, netto, Currency_Code, exchange_rate_to_EUR, customer_vehicle_id) VALUES (".$id_order.", '".$ebay_orders_items[$i]["TransactionID"]."', ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", ".$net.", '".$ebay_orders_items[$i]["Currency_Code"]."', ".$exchange_rate.", 0);", $dbshop, __FILE__, __LINE__);	
				
				$fieldlist=array();
				//BASISFELDER FÜR API-AUFRUF
				$fieldlist["API"]="shop";
				$fieldlist["APIRequest"]="OrderItemAdd";
				$fieldlist["mode"]="new";
				
				//FIELDLIST FOR UPDATE
				$fieldlist["order_id"]=$update_orderid;
				$fieldlist["foreign_transactionID"]=$ebay_orders_items[$i]["TransactionID"];
				$fieldlist["item_id"]=$row_item["id_item"];
				$fieldlist["amount"]=$ebay_orders_items[$i]["QuantityPurchased"];
				$fieldlist["price"]=$ebay_orders_items[$i]["TransactionPrice"];
				$fieldlist["netto"]=$net;
				$fieldlist["Currency_Code"]=$ebay_orders_items[$i]["Currency_Code"];
				$fieldlist["exchange_rate_to_EUR"]=$exchange_rate;
				
			}
			
			$responseXML=post("https://www.mapco.de/soa2/index.php", $fieldlist);
	echo $responseXML;
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				echo '<import_ebayOrderDataResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
				echo '		<longMsg>Die zurückgelieferten XML-Daten sind nicht valide und können deshalb nicht ausgewertet werden. Service gestoppt.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</import_ebayOrderDataResponse>'."\n";
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]=="Success")
			{

			}
			else
			{
				echo '<import_ebayOrderDataResponse>'."\n";
				echo '	<Ack>'.$response->Ack[0].'</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>FEHLER.</shortMsg>'."\n";
				echo '		<longMsg>FEHLER.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</import_ebayOrderDataResponse>'."\n";
				exit;
			}
			
			unset($response);
			unset($responseXML);
		}
/*
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
*/

//***********************************************************************************************************************
/*

			//GET ACCOUNT DATA
		$res_account=q("SELECT * FROM crm_customer_accounts3 WHERE shop_user_id = '".$ebay_orders["BuyerUserID"]."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_account)>0)
		{
			$row_account=mysqli_fetch_array($res_account);

			//UPDATE ADDRESS	
				//CHECK IF ADDRESS IS KNOWN
				//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRESSE VORHANDEN
			if ($ebay_orders["ShippingAddressAddressID"]!="")
			{
				$res_check=q("SELECT * FROM shop_bill_adr WHERE foreign_address_id = ".$ebay_orders["ShippingAddressAddressID"]." AND shop_id = ".$shop["id_shop"].";", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_check)==0)
				{
				$res=q("INSERT INTO shop_bill_adr (
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
				'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressAddressID"])."', 
				'".mysqli_real_escape_string($dbshop, $bill_firstname)."', 
				'".mysqli_real_escape_string($dbshop, $bill_lastname)."', 
				'".mysqli_real_escape_string($dbshop, $bill_street1)."', 
				'".mysqli_real_escape_string($dbshop, $bill_streetNumber)."', 
				'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressStreet2"])."', 
				'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressPostalCode"])."', 
				'".mysqli_real_escape_string($dbshop, $bill_city)."', 
				'".mysqli_real_escape_string($dbshop, $ebay_orders["ShippingAddressCountryName"])."', 
				".$countries[$ebay_orders["ShippingAddressCountry"]].", 
				1,
				1 
				);", $dbshop, __FILE__, __LINE__);
				}
			}
			

		}
*/		
	}
	
	

	echo "<crm_add_customer_listResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_add_customer_listResponse>";

?>