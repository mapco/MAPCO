<?php

	include("../functions/cms_createPassword.php");
	include("../functions/cms_send_html_mail.php");
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
*/

//*************************************************************************************************************************
// F U N C T I O N S 
//*************************************************************************************************************************

	function get_cms_user($BuyerUserID, $usermail, $bill_firstname, $bill_lastname, $shop)
	{
		global $dbweb;
		//CHECK IF CUSTOMER IS KNOWN

		//CHECK FOR KNOWN ACCOUNT ID
		$buyerUserIDunknown=true;
		$res_check=q("SELECT * FROM crm_customer_accounts_test WHERE shop_user_id = '".$BuyerUserID."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($res_check)>0)
		{
			$row_check=mysql_fetch_array($res_check);
			$cms_user_id=$row_check["cms_user_id"];
			$buyerUserIDunknown=false;
		}
				
		//CHECK FOR KNOWN EMAIL
		$usermailunknown=true;
		if ($cms_user_id==0 && $usermail!="Invalid Request" && $usermail!="" )
		{
			$res_check=q("SELECT * FROM crm_numbers_test WHERE number = '".$usermail."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)>0)
			{
				$row_check=mysql_fetch_array($res_check);
				$cms_user_id=$row_check["cms_user_id"];
			}
		}
		
		//CMS USER unbekannt -> neu anlegen
		if ($cms_user_id==0)
		{
			//FIND UNIQUE USERNAME
			//GET existing CMS users
			$CMS=array();
			$res_CMS=q("SELECT * FROM cms_users_test WHERE shop_id = ".$shop["parent_shop_id"].";" , $dbweb, __FILE__, __LINE__);
			while ($row_CMS=mysql_fetch_array($res_CMS))
			{
				$CMS[$row_CMS["username"]]=$row_CMS["id_user"];
			}

			$cms_username="";
			
			if (!isset($CMS[$BuyerUserID]))
			{
				//EBAY ACCOUNT USERNAME noch nicht vorhanden für ShopID
				$cms_username=$BuyerUserID;
			}
			else
			{
				//EBAY ACCOUNT USERNAME bereits voranden -> Test ob E-Mail Addresse als Shop-UserName verwendet werden kann
				if ($usermail!="" && $usermail!="Invalid Request") 
				{
					if (!isset($CMS[$usermail])) $cms_username=$usermail;
				}
			}
			
				//WENN EBAY USRENAME UND EMAIL ADRESSE BEREITS VORHANDEN erzeuge Shop-Username AUS EbayUserName + Zähler
			if ($cms_username=="")
			{
				$counter=1;
				$tmp=$BuyerUserID."_".(string)$counter;
				while (isset($CMS[$tmp]))
				{
					$counter++;
					$tmp=$BuyerUserID."_".(string)$counter;
				}
				$cms_username=$tmp;
					
			}

			// CMS USER ANLEGEN
			$res_ins=q("INSERT INTO cms_users_test (
				shop_id, 
				username, 
				usermail, 
				name, 
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
				'".mysql_real_escape_string($cms_username, $dbweb)."', 
				'".mysql_real_escape_string($usermail, $dbweb)."', 
				'".mysql_real_escape_string($bill_firstname." ".$bill_lastname, $dbweb)."',
				'".mysql_real_escape_string(createPassword(8),$dbweb)."', 
				'".mysql_real_escape_string(createPassword(50),$dbweb)."',
				5,
				1,
				1, 
				".time().", 
				1, 
				".time().", 
				1
			);", $dbweb, __FILE__, __LINE__);

			$cms_user_id=mysql_insert_id($dbweb);
		}
		
		//SAVE USERMAIL
		if ($usermailunknown)
		{
			if ($usermail!="Invalid Request" && $usermail!="")
			{
				$res_ins=q("INSERT INTO crm_numbers_test (
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
					'".mysql_real_escape_string($usermail,$dbweb)."', 
					".time().", 
					1, ".time().", 
					1
				);", $dbweb, __FILE__, __LINE__);
				
			}
		}
		
		//SAVE EBAY ACCOUNT
		if ($buyerUserIDunknown)
		{
			$res_ins=q("INSERT INTO crm_customer_accounts_test (
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
				'".mysql_real_escape_string($BuyerUserID, $dbweb)."', 
				".time().", 
				1, 
				".time().", 
				1
			);", $dbweb, __FILE__, __LINE__);
		}
		
		return $cms_user_id;
	} // END FUNCTION
	
	function set_crm_phone($phone, $cms_user_id, $shop)
	{
		global $dbweb;
					
		if ($phone!="Invalid Request" && $phone!="" && $phone!="00000 00000")
		{
			$res_check=q("SELECT * FROM crm_numbers_test WHERE number = '".$phone."' AND cms_user_id = ".$cms_user_id." AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)==0)
			{
				//SAVE PHONE
				$res_ins=q("INSERT INTO crm_numbers_test (
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
					'".mysql_real_escape_string($phone, $dbweb)."',
					".time().", 
					1, 
					".time().", 
					1
				);", $dbweb, __FILE__, __LINE__);
					
			}
		}
	} // END FUNCTION
	
	function set_crm_address($addressID, $bill_firstname, $bill_lastname, $bill_street1, $bill_streetNumber, $bill_street2, $bill_zip, $bill_city, $bill_country, $bill_country_id, $cms_user_id, $shop)
	{
		global $dbshop;
		//CHECK FOR KNOWN ADDRESS ID
			//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRESSE VORHANDEN
		if ($addressID!="")
		{
			$res_check=q("SELECT * FROM shop_bill_adr_test WHERE foreign_address_id = ".$addressID." AND shop_id = ".$shop["id_shop"].";", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)==0)
			{
				$res=q("INSERT INTO shop_bill_adr_test (
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
					'".mysql_real_escape_string($addressID, $dbshop)."', 
					'".mysql_real_escape_string($bill_firstname, $dbshop)."', 
					'".mysql_real_escape_string($bill_lastname , $dbshop)."', 
					'".mysql_real_escape_string($bill_street1, $dbshop)."', 
					'".mysql_real_escape_string($bill_streetNumber, $dbshop)."', 
					'".mysql_real_escape_string($bill_street2, $dbshop)."', 
					'".mysql_real_escape_string($bill_zip, $dbshop)."', 
					'".mysql_real_escape_string($bill_city, $dbshop)."', 
					'".mysql_real_escape_string($bill_country, $dbshop)."', 
					".$bill_country_id.", 
					1,
					1 
				);", $dbshop, __FILE__, __LINE__);
				
				return mysql_insert_id($dbshop);
				
			}
			else
			{
				$row_check=mysql_fetch_array($res_check);
				return $row_check["adr_id"];
			}
		}
		else return 0;
		
	} // END FUNCTION
//************************************************************************************************************
// F U N C T I O N S   E N D
//************************************************************************************************************

	if ( !isset($_POST["EbayOrderID"]) )
	{
		echo '<import_ebayOrderDataResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay OrderID nicht übermittelt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Ebay OrderID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</import_ebayOrderDataResponse>'."\n";
		error__log(5,1,__FILE__, __LINE__, "Ebay-OrderID nicht übermittelt.");	
		exit;
	}
	$res_orders=q("SELECT * FROM ebay_orders_test WHERE OrderID = '".$_POST["EbayOrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_orders)==0)
	{
		echo '<import_ebayOrderDataResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Ebay OrderID konnte in der Tabelle ebay_orders nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</import_ebayOrderDataResponse>'."\n";
		error__log(5,2,__FILE__, __LINE__, "Ebay-OrderID ".$_POST["EbayOrderID"]." nicht in Tabelle ebay_orders gefunden.");	

		exit;
	}
	
	$ebay_orders=mysql_fetch_array($res_orders);
	$res_orders_items=q("SELECT * FROM ebay_orders_items_test WHERE OrderID = '".$ebay_orders["OrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_orders_items)==0)
	{
		echo '<import_ebayOrderDataResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>keine Bestellposition gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur angegebenen Ebay OrderID konnte keine Bestellposition gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</import_ebayOrderDataResponse>'."\n";
		error__log(5,3,__FILE__, __LINE__, "keine Bestellposition zur Ebay OrderID ".$_POST["EbayOrderID"]." gefunden.");	
		exit;
	}
	
	$ebay_orders_items=array();
	while ($row_orders_items=mysql_fetch_array($res_orders_items))
	{
		$ebay_orders_items[]=$row_orders_items;
	}
	
	// GET SHOP ID
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
	
	$shop_order=array();
	//SEARCH FOR EXISTING SHOP ORDER
	$res_shop_order=q("SELECT * FROM shop_orders_test WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_shop_order)>0)
	{
		$row_shop_order=mysql_fetch_array($res_shop_order);
		$shop_order[$ebay_orders["OrderID"]]=$row_shop_order;
	}
	
//*****************************************************************
// ADD
//*****************************************************************


	//if ($_POST["mode"]=="add")
	if (!isset($shop_order[$ebay_orders["OrderID"]]))
	{
		
		// CHECK FOR CMS_USER_ID - IF UNKNOWN -> CREATE
			// SAVE usermail & BuyerUserID
		$cms_user_id=get_cms_user($ebay_orders["BuyerUserID"], $usermail, $bill_firstname, $bill_lastname, $shop);
		
		// CHECK FOR KNOWN PHONE - IF UNKNOWN -> CREATE
		set_crm_phone($ebay_orders["ShippingAddressPhone"], $cms_user_id, $shop);
		
		
		$address_id=set_crm_address($ebay_orders["ShippingAddressAddressID"], $bill_firstname, $bill_lastname, $bill_street1, $bill_streetNumber, $ebay_orders["ShippingAddressStreet1"], $ebay_orders["ShippingAddressPostalCode"], $bill_city, $ebay_orders["ShippingAddressCountryName"], $countries[$ebay_orders["ShippingAddressCountry"]], $cms_user_id, $shop);
		
		
		
		$paymentstatusreason="";
		$payment_note="";
		$paytransactionID="";
		if ($payments_type_id==4) 
		{
			//GET PAYPAL Payment - Notification	
			$IPNs=array();
			for ($i=0; $i<sizeof ($ebay_orders_items); $i++)
			{
				$res_IPN=q("SELECT * FROM payment_notifications4 WHERE orderTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' OR parentPaymentTransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ORDER BY payment_date DESC;", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($res_IPN)>0)
				{
		//			echo "IPN COUNT: ".mysql_num_rows($res_IPN)."++++";
					while ($row_IPN=mysql_fetch_array($res_IPN))
					{
						$IPNs[$row_IPN["paymentTransactionID"]]=0;;
					}
				}
			}
			
			$PaymentTransaction=array();
			//GET LAST NOTIFICATION
			if (sizeof($IPNs)>0)
			{
				$qry="";
				foreach ($IPNs as $PTI => $tmp)
				{
					if ($qry=="") $qry="'".$PTI."'"; else $qry.=", '".$PTI."'";
				}
	
				$res_IPN=q("SELECT * FROM payment_notifications4 WHERE paymentTransactionID IN (".$qry.") OR parentPaymentTransactionID IN (".$qry.") ORDER BY payment_date DESC LIMIT 1;", $dbshop, __FILE__, __LINE__);
				
				if (mysql_num_rows($res_IPN)>0)
				{
					$PaymentTransaction=mysql_fetch_array($res_IPN);
					$paymentdate=$PaymentTransaction["payment_date"];
					$paymentstatus=$PaymentTransaction["state"];
					$paymentstatusreason=$PaymentTransaction["state_reason"];
					$payment_note=$PaymentTransaction["payment_note"];
					$paymenttransactionID=$PaymentTransaction["paymentTransactionID"];
				}
			}
		}
		
//INSERT SHOP ORDER ++++++++++++++++++++++++++++++++++++++++++++++++++++++
		//ADD ORDER TO SHOP ORDER
		
		
		q("INSERT INTO shop_orders_test (
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
			Payments_TransactionID,
			PayPal_PendingReason,
			PayPal_BuyerNote,
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
			'".mysql_real_escape_string($paymenttransactionID, $dbshop)."',
			'".mysql_real_escape_string($paymentstatusreason, $dbshop)."',
			'".mysql_real_escape_string($payment_note, $dbshop)."',
			'".mysql_real_escape_string($paymentstatus, $dbshop)."',
			0, 
			'".$address_id."', 
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
			
			q("INSERT INTO shop_orders_items_test (order_id, foreign_transactionID, item_id, amount, price, netto, Currency_Code, exchange_rate_to_EUR) VALUES (".$order_id.", '".$ebay_orders_items[$i]["TransactionID"]."', ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", 0, '".$ebay_orders_items[$i]["Currency_Code"]."', ".$exchange_rate.");", $dbshop, __FILE__, __LINE__);
			
		}
		
		//UPDATE PAYMENT NOTIFICATIONS
		if ($paymenttransactionID!="" && $payments_type_id==4)
		{
			q("UPDATE payment_notifications4 SET shop_orderID = ".$order_id." WHERE paymentTransactionID = '".$paymenttransactionID."';", $dbshop, __FILE__, __LINE__);
		}
		
		
	//************************
	//ADD EVENTS
	//************************	
	
	//ALERT MAIL - EXPRESS SHIPMENT
	
		if (($shippingType==2 ||$shippingType==7) && ($ebay_orders["account_id"]==1 || $ebay_orders["account_id"]==2))
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
				$msg.=$ebay_orders_item["QuantityPurchased"].'x '.$ebay_orders_item["ItemSKU"].' '.$ebay_orders_item["ItemTitle"].' <small>('.$ebay_orders_item["ItemItemID"].')</small><br />';
			}
			
		//	SendMail($reciever, "Bestellmanagement-System <noreply@mapco.de>", $subject, $msg);
			SendMail("nputzing@mapco.de", "Bestellmanagement-System <noreply@mapco.de>", $subject, $msg);
	
			
			//$response=post(PATH."soa/", array("API" => "crm", "Action" => "AlertMail_ExpressShipment", "OrderID" => $order_id));
		}


		
	} // MODE ADD
	
//*****************************************************************
// UPDATE
//*****************************************************************

	//if ($_POST["mode"]=="update")
	else
	{
		
		$old_transactions=array();
		
		//CHECK FOR OLD TRANSACTIONS
		
		for ($i=0; $i<sizeof($ebay_orders_items); $i++)
		{
			$res_check_items=q("SELECT * FROM shop_orders_items_test WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_check_items)>0)
			{
				$row_check_items=mysql_fetch_array($res_check_items);
				$old_transactions[$i]["TransactionID"]=$row_check_items["foreign_TransactionID"];
				$old_transactions[$i]["order_id"]=$row_check_items["order_id"];
				$res_check_order=q("SELECT * FROM shop_orders_test WHERE id_order = ".$row_check_items["order_id"].";",$dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($res_check_order)>0)
				{
					$row_check_order=mysql_fetch_array($res_check_order);
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
		$res_check=q("SELECT * FROM shop_orders_test WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res_check)>0)
		{
			$update_OrderID=$ebay_orders["OrderID"];
			$shop_order=mysql_fetch_array($res_check);
			$address_updated=$shop_order["bill_address_manual_update"];
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

				//UPDATE ORDER
			q("UPDATE shop_orders_test SET 
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
			q("UPDATE shop_orders_test SET 
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
			q("UPDATE shop_orders_test SET status_id = ".$status_id." , status_date = ".$status_date." WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
		}
		elseif ($status_id == 7)
		{
			$res_shop_order=q("SELECT * FROM shop_orders_test WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
			$row_shop_order=mysql_fetch_array($res_shop_order);
			if ($row_shop_order["status_id"]==1) 
			{
				q("UPDATE shop_orders_test SET status_id = ".$status_id." , status_date = ".$status_date." WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
			}
		}
		
		//check ob Order zusammengefasst wurde
		if (strpos($ebay_orders["OrderID"],"-")===true)
		{
			//UNSET COMBINED ORDERS
			q("UPDATE shop_orders_test SET combined_with = 0 WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
		}
		
		
			
		//GET id_order
		$res=q("SELECT id_order FROM shop_orders_test WHERE foreign_OrderID = '".$ebay_orders["OrderID"]."' ;",$dbshop, __FILE__, __LINE__);
		$row=mysql_fetch_array($res);
		$id_order=$row["id_order"];
			
		//DELETE other OLD ORDERS	
		for ($i=0; $i<sizeof($old_transactions); $i++)
		{
			if ($old_transactions[$i]["OrderID"]!=$update_OrderID && $old_transactions[$i]["OrderID"]!="")
			{
				q("DELETE FROM shop_orders_test WHERE foreign_OrderID ='".$old_transactions[$i]["OrderID"]."' AND shop_id = ".$shop["id_shop"].";", $dbshop, __FILE__, __LINE__);
			}
		}
			

		//UPDATE/ADD ITEMS
		for ($i=0; $i<sizeof($ebay_orders_items); $i++)
		{
			//check for existing Transaction
			$res_check=q("SELECT * FROM shop_orders_items_test WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)>0) 
			{
				//UPDATE TRANSACTION
				$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebay_orders_items[$i]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row_item=mysql_fetch_array($res_item);

				q("UPDATE shop_orders_items_test SET order_id = ".$id_order.", item_id = ".$row_item["id_item"].", amount = ".$ebay_orders_items[$i]["QuantityPurchased"].", price = ".$ebay_orders_items[$i]["TransactionPrice"].", netto = 0 WHERE foreign_TransactionID = '".$ebay_orders_items[$i]["TransactionID"]."' ;", $dbshop, __FILE__, __LINE__); 
				
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

				
				q("INSERT INTO shop_orders_items_test (order_id, foreign_TransactionID, item_id, amount, price, netto, Currency_Code, exchange_rate_to_EUR, customer_vehicle_id) VALUES (".$id_order.", '".$ebay_orders_items[$i]["TransactionID"]."', ".$row_item["id_item"].", ".$ebay_orders_items[$i]["QuantityPurchased"].", ".$ebay_orders_items[$i]["TransactionPrice"].", 0, '".$ebay_orders_items[$i]["Currency_Code"]."', ".$exchange_rate.", 0);", $dbshop, __FILE__, __LINE__);	
				
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
				q("UPDATE shop_orders_items_test SET order_id=0 WHERE foreign_TransactionID = '".$old_transactions[$i]["TransactionID"]."';", $dbshop, __FILE__, __LINE__);
			}
		}


//***********************************************************************************************************************


		// CHECK FOR CMS_USER_ID - IF UNKNOWN -> CREATE
			// SAVE usermail & BuyerUserID
		$cms_user_id=get_cms_user($ebay_orders["BuyerUserID"], $usermail, $bill_firstname, $bill_lastname, $shop);
		
		// CHECK FOR KNOWN PHONE - IF UNKNOWN -> CREATE
		set_crm_phone($ebay_orders["ShippingAddressPhone"], $cms_user_id, $shop);
		
		
		set_crm_address($ebay_orders["ShippingAddressAddressID"], $bill_firstname, $bill_lastname, $bill_street1, $bill_streetNumber, $ebay_orders["ShippingAddressStreet1"], $ebay_orders["ShippingAddressPostalCode"], $bill_city, $ebay_orders["ShippingAddressCountryName"], $countries[$ebay_orders["ShippingAddressCountry"]], $cms_user_id, $shop);

	
		
	}
	
	

	echo "<crm_add_customer_listResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_add_customer_listResponse>";

?>