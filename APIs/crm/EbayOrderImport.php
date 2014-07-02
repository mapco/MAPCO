<?php


	check_man_params(array("EbayOrderID" => "numericNN"));
	
	include("../functions/cms_createPassword.php");
	include("../functions/cms_send_html_mail.php");

	$ust = (UST/100) +1;


/*############################################################################################################
	CHECK FOR UPADTE OR ADD
	IF UPDATE 	-> DEFINE UPDATE ID_ORDER
				-> DEFINE ID_ORDER FOR DEACTIVATION
##############################################################################################################*/
	
	//GET EBAY ORDER
	$res_ebay_order=q("SELECT * FROM ebay_orders WHERE id_order = ".$_POST["EbayOrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_ebay_order)==0)
	{
		//EBAY ORDER NICHT GEFUNDEN
		show_error(9767, 7, __FILE__, __LINE__, "Ebay Order ID: ".$_POST["EbayOrderID"]);
		exit;
	}
	$ebayOrder=mysqli_fetch_array($res_ebay_order);
	
	//GET EbayOrderTransactions
	$res_ebay_order_transactions = q("SELECT * FROM ebay_orders_items WHERE OrderID = '".$ebayOrder["OrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_ebay_order_transactions)==0)
	{
		//show_error();
		//Keine Transactions zur EbayOrder gefunden
		show_error(9768, 7, __FILE__, __LINE__, "Ebay OrderID: ".$ebayOrder["OrderID"]);
		exit;
	}
	$transactions=array();
	$ebayOrderItems=array();
	while ($row_transactions=mysqli_fetch_array($res_ebay_order_transactions))
	{
		$transactions[$row_transactions["TransactionID"]]["new_OrderID"] = $row_transactions["OrderID"];
		
		$ebayOrderItems[$row_transactions["TransactionID"]]=$row_transactions;
	}
	
	//GET EXISTING TRANSACTIONS FROM SHOP_ORDERS_ITEMS
	foreach ($transactions as $transaction => $transactiondata)
	{
		$res_shop_orders_transactions = q("SELECT * FROM shop_orders_items WHERE foreign_transactionID = '".$transaction."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shop_orders_transactions) == 0)
		{
			$transactions[$transaction]["mode"] = "add";
			$transactions[$transaction]["id"] = 0;
			$transactions[$transaction]["shop_order_id"] = 0;
		}
		else
		{
			$row_shop_orders_transactions=mysqli_fetch_array($res_shop_orders_transactions);
			$transactions[$transaction]["mode"] = "update";
			$transactions[$transaction]["id"] = $row_shop_orders_transactions["id"];
			$transactions[$transaction]["shop_order_id"] = $row_shop_orders_transactions["order_id"];
		}
	}
	
	//GET EXISTING foreign_OrderID FROM SHOP_ORDERS
	foreach ($transactions as $transaction => $transactiondata)
	{
		if ($transactiondata["shop_order_id"]!=0)
		{
			$res_shop_orders = q("SELECT * FROM shop_orders WHERE id_order = ".$transactiondata["shop_order_id"].";", $dbshop, __FILE__, __LINE);
			if (mysqli_num_rows($res_shop_orders)==0)
			{
				$transactions[$transaction]["foreign_OrderID"] = "";
				$transactions[$transaction]["combined_with"] = 0;
			}
			else
			{
				$row_shop_orders = mysqli_fetch_array($res_shop_orders);
				$transactions[$transaction]["foreign_OrderID"] = $row_shop_orders["foreign_OrderID"];
				$transactions[$transaction]["combined_with"] = $row_shop_orders["combined_with"];
			}
		}
	}
	
	//CHECK IF ORDER EXISTS in shop_orders
	$know_shop_orders=array();
	foreach ($transactions as $transaction => $transactiondata)
	{
		if ($transactiondata["shop_order_id"]!=0)
		{
			$know_shop_orders[$transactiondata["shop_order_id"]]["foreign_OrderID"]=$transactiondata["foreign_OrderID"];
			$know_shop_orders[$transactiondata["shop_order_id"]]["combined_with"]=$transactiondata["combined_with"];
		}
	}

//**************************************************
//DEFINE UPDATE id_order
//**************************************************

	$update_orderid=0;

	//search for ORDER_ID == foreign_OrderID
	$res_check = q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$ebayOrder["OrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_check)>0)
	{
		$row_check=mysqli_fetch_array($res_check);
		$update_orderid=$row_check["id_order"];
		$ordermode="update";
	}
	
	if ($update_orderid==0)
	{
		//IF SHOP_ORDER exists -> get update id_order ("Mother" || any other)
		if (sizeof($know_shop_orders)>0)
		{
			$ordermode="update";
			
			// SEARCH FOR MOTHER
			foreach ($know_shop_orders as $orderid => $known_shop_order)
			{
				if ($known_shop_order["combined_with"]!=0 && $known_shop_order["combined_with"]==$orderid && $update_orderid==0)
				{
					$update_orderid = $orderid;
				}
			}
			//if no mother search for any other
			if ($update_orderid==0)
			{
				foreach ($know_shop_orders as $orderid => $known_shop_order)
				{
					if ($orderid!=0 && $update_orderid == 0)
					{
						$update_orderid = $orderid;
					}
				}
			}
			// POSSIBLE ERROR -> found no updateorderid 
			if ($update_orderid==0)
			{
				//Transactions vorhanden, es konnte aber keine zu bearbeitenden Order festgestellt werden
				show_error(9769, 7, __FILE__, __LINE__, "Ebay OrderID: ".$ebayOrder["OrderID"]);
				exit;
			}
			
		}
		else
		// KEINE ALTE ORDER (nach OrderItems) vorhanden
		{
			//CHECK FOR ALREADY EXISTING foreignorderid
			$res_check_shop_order = q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$ebayOrder["OrderID"]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check_shop_order)==0)
			{
				// NO EXISTING ORDER -> ADD
				$ordermode="add";	
				$update_orderid=0;
			}
			else
			{
				$ordermode="update";
				$row_check_shop_order=mysqli_fetch_array($res_check_shop_order);
				$update_orderid=$row_check_shop_order["id_order"];	
			}
			
		}
	}
	// END OF DEFINE update_orderid
	//**************************************************************************************************
	
	//GET TRANSACTIONS OF UPDATEORDER
	$updateOrderTransactions=array();
	if ($update_orderid!=0)
	{
		$res_updattransactions = q("SELECT * FROM shop_orders_items WHERE order_id = ".$update_orderid.";", $dbshop, __FILE__, __LINE__);
		while ($row_updatetransactions=mysqli_fetch_array($res_updattransactions))
		{
			$updateOrderTransactions[$row_updatetransactions["foreign_transactionID"]]=$row_updatetransactions["id"];
		}
	}

/*######################################################################################################################
	PREPARE DATA
########################################################################################################################*/

	// GET SHOP_SHOPS data
	$res_shop=q("SELECT * FROM shop_shops WHERE account_id = ".$ebayOrder["account_id"]." AND shop_type = 2;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_shop)==0)
	{
		//ES KONNTE KEIN SHOP ZUM EBAY ACCOUNT GEFUNDEN WERDEN
		show_error(9770, 7, __FILE__, __LINE__, "Ebay-Account: ".$ebayOrder["account_id"]);
		exit;
	}
	$shop=mysqli_fetch_array($res_shop);

	// GET SITE ID
	$res_shop=q("SELECT * FROM shop_shops WHERE id_shop = ".$shop["parent_shop_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_shop)==0)
	{
		echo "DIE SITE-ID KONNTE NICHT GEFUNDEN WERDEN";
		show_error(9771, 7, __FILE__, __LINE__, "Parentshop_id: ".$shop["parent_shop_id"]);
		exit;
	}
	else
	{
		$shop_site=mysqli_fetch_array($res_shop);
		$site_id=$shop_site["site_id"];
	}

	// GET PAYMENTTIME
	$payment_date=$ebayOrder["PaidTime"];
	if ($ebayOrder["PaidTime"]=="")
	{
		$paymentdate=0;
	}
	else 
	{
		$paymentdate=strtotime($ebayOrder["PaidTime"]);
	}

	// CREATE SHIPPING DETAILS FOR SHOP_ORDERS
	$shipping_details=$ebayOrder["ShippingServiceSelectedShippingService"].", ".$ebayOrder["CheckoutStatusPaymentMethod"];

	// DEFINE USERMAIL
	$usermail="";
	foreach ($ebayOrderItems as $transaction => $transactiondata)
	{
		if ($transactiondata["BuyerEmail"]!="" && $transactiondata["BuyerEmail"]!="Invalid Request" && $usermail=="")
		{
			$usermail=$transactiondata["BuyerEmail"];
		}
	}

	//PREPARE ADDRESS
	$packstation=false;
	//PACKSTATION
		//search for "PACKSTATION";
		if (strpos(strtolower($ebayOrder["ShippingAddressName"]),"packstation")===true)
		{
			$packstation=true;
			$tmp=$ebayOrder["ShippingAddressName"];
			$ebayOrder["ShippingAddressName"]=$ebayOrder["ShippingAddressStreet1"];
			$ebayOrder["ShippingAddressStreet1"]=$tmp;
		}
		if (strpos($ebayOrder["ShippingAddressStreet2"], "Packstation") !== false)
		{
			$packstation=true;
			$tmp=$ebayOrder["ShippingAddressStreet1"];
			$ebayOrder["ShippingAddressStreet1"]=$ebayOrder["ShippingAddressStreet2"];
			$ebayOrder["ShippingAddressStreet2"]=$tmp;
		}
		
		if ($packstation)
		{
			if (is_numeric($ebayOrder["ShippingAddressName"]) && strlen($ebayOrder["ShippingAddressName"])>=7 && strlen($ebayOrder["ShippingAddressName"])<=10)
			{
				$tmp=$ebayOrder["ShippingAddressName"];
				$ebayOrder["ShippingAddressName"]=$ebayOrder["ShippingAddressStreet2"];
				$ebayOrder["ShippingAddressStreet2"]=$tmp;
			}
		}
	
	if (strpos($ebayOrder["ShippingAddressName"]," ")===false)
	{
		$bill_firstname=substr($ebayOrder["ShippingAddressName"], 0, strpos($ebayOrder["ShippingAddressName"],"."));
		
		$bill_lastname=substr($ebayOrder["ShippingAddressName"], strpos($ebayOrder["ShippingAddressName"],".")+1);
	}
	else
	{
		$bill_firstname=substr($ebayOrder["ShippingAddressName"], 0, strpos($ebayOrder["ShippingAddressName"]," "));
		
		$bill_lastname=substr($ebayOrder["ShippingAddressName"], strpos($ebayOrder["ShippingAddressName"]," ")+1);
	}
	
	if ($bill_firstname=="")
	{
		$bill_lastname=$ebayOrder["ShippingAddressName"];
	}
	
		
	$has_number=false;		
	$pos=0;
	for ($i=strlen($ebayOrder["ShippingAddressStreet1"])-1; $i>-1; $i--)
	{
		if ((is_numeric(substr($ebayOrder["ShippingAddressStreet1"],$i, 1)) || substr($ebayOrder["ShippingAddressStreet1"],$i, 1)=="/") && $pos==0)
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
		$bill_street1=$ebayOrder["ShippingAddressStreet1"];
		$bill_streetNumber="0";
	}
	else
	{
		$bill_street1=trim(substr($ebayOrder["ShippingAddressStreet1"], 0, $pos+1));	
		$bill_streetNumber=trim(substr($ebayOrder["ShippingAddressStreet1"], $pos+1));
	}
	
	//ADD StateOrProvince
	if ($ebayOrder["ShippingAddressStateOrProvince"]=="")
	{
		$bill_city=$ebayOrder["ShippingAddressCityName"];
	}
	else
	{
		$bill_city=$ebayOrder["ShippingAddressCityName"].", ".$ebayOrder["ShippingAddressStateOrProvince"];
	}
	
	//PHONE Number
	if ($ebayOrder["ShippingAddressPhone"] == "" || $ebayOrder["ShippingAddressPhone"] == "Invalid Request")
	{
		$bill_phone="00000 00000";
	}
	else
	{
		$bill_phone=$ebayOrder["ShippingAddressPhone"];
	}
	
	//GET SHIPPING TYPES
	$shop_shipping_type=array();
	$res_shiptype=q("SELECT * FROM ebay_shipping_types;", $dbshop, __FILE__, __LINE__);
	while ($row_shiptype=mysqli_fetch_array($res_shiptype))
	{
		$shop_shipping_type[$row_shiptype["ShippingServiceType"]]=$row_shiptype["shippingtype_id"];
	}
	$shippingTypes=array();
	$res_shippingtypes=q("SELECT * FROM shop_shipping_types", $dbshop, __FILE__, __LINE__);
	while ($row_shippingtypes = mysqli_fetch_assoc($res_shippingtypes))
	{
		$shippingTypes[$row_shippingtypes["id_shippingtype"]]=$row_shippingtypes;
	}

	if (isset($shop_shipping_type[$ebayOrder["ShippingServiceSelectedShippingService"]]))
	{
		$shippingType=$shop_shipping_type[$ebayOrder["ShippingServiceSelectedShippingService"]];
	}
	else
	{
		$shippingType=0;
	}
	
	//SHIPPING COSTS NET
	if ($ebayOrder["ShippingServiceSelectedShippingServiceCost"]==0)
	{
		$shipping_costs_net=0;
	}
	else
	{
		$shipping_costs_net=round(($ebayOrder["ShippingServiceSelectedShippingServiceCost"]/$ust),2);
	}
	
	$payments_type_id="";
	//GET PAYMENTS Types
	$res_payments=q("SELECT * FROM shop_payment_types WHERE PaymentMethod = '".$ebayOrder["CheckoutStatusPaymentMethod"]."';", $dbshop, __FILE__, __LINE__);
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
	foreach ($ebayOrderItems as $transaction => $transactiondata)
	{
		if ($transactiondata["UnpaidItem"]=="ClosedWithoutPayment") $unpaid_item=true;
	}

	//SET SHOP STATE ID
	$status_id=1;
	$paymentstatus="";
	$status_date=$ebayOrder["CreatedTimeTimestamp"];
	if ($ebayOrder["OrderStatus"]=="Cancelled" || $ebayOrder["OrderStatus"]=="Inactive" || $ebayOrder["Total"]==0 || $unpaid_item) 
	{
		$status_id=4;
	//	mail("nputzing@mapco.de", "Bestellungsabbruch", $ebayOrder["OrderID"]);
		$status_date=strtotime($ebayOrder["CheckoutStatusLastModifiedTime"]);
	}
	elseif (($payments_type_id == 2 || $payments_type_id == 4) && $paymentdate !=0 )
	{
		$status_id=7;
		$status_date=strtotime($ebayOrder["CheckoutStatusLastModifiedTime"]);
		$paymentstatus="Completed";
		//unten bei Update abchecken ob status 7 gesetzt werden kann
	}


/*#########################################################################################
	Order Add
#########################################################################################*/

if ($ordermode == "add")
{
	//CHECK IF CUSTOMER IS KNOWN
	
		$cms_user_id=0;
		//CHECK FOR KNOWN ACCOUNT ID
		$shop_user_id=0;
//		$res_check=q("SELECT * FROM crm_customer_accounts_test WHERE shop_user_id = '".$ebayOrder["BuyerUserID"]."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
		$res_check=q("SELECT * FROM crm_customer_accounts3 WHERE shop_user_id = '".$ebayOrder["BuyerUserID"]."' AND shop_type = 2 AND site_id = ".$site_id.";", $dbweb, __FILE__, __LINE__);
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
			//$res_check=q("SELECT * FROM crm_numbers_test WHERE number = '".$usermail."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
			$res_check=q("SELECT * FROM crm_numbers3 WHERE number = '".$usermail."' AND number_type = 7 AND shop_type = ".$shop["shop_type"]." AND site_id = ".$site_id.";", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)>0)
			{
				$row_check=mysqli_fetch_array($res_check);
				$number_id=$row_check["id_crm_number"];
				$number_shop_id=$row_check["shop_id"];
				//if ($cms_user_id==0) $cms_user_id=$row_check["cms_user_id"];
			}
		}
		
		

		//ADD CUSTOMER DATA / UPDATE
		if ($cms_user_id==0)
		{
			//CMS_USER ANLEGEN
			//check if username exists
			
			//EBAY USERNAME -> CMS USERNAME
			
			//GET USER_IDs from cms_users_sites WHERE SITE_ID = $site_id
			$site_userids = array();
			$res_site_userids = q("SELECT * FROM cms_users_sites WHERE site_id = ".$site_id.";", $dbweb, __FILE__, __LINE__);
			while ($row_site_userids = mysqli_fetch_array($res_site_userids))
			{
				$site_userids[$row_site_userids["user_id"]] = $row_site_userids["site_id"];
			}
			
			//GET existing CMS users
			$CMS=array();
		//	$res_CMS=q("SELECT * FROM cms_users_test WHERE shop_id = ".$shop["parent_shop_id"].";" , $dbweb, __FILE__, __LINE__);
			$res_CMS=q("SELECT * FROM cms_users;" , $dbweb, __FILE__, __LINE__);
			while ($row_CMS=mysqli_fetch_array($res_CMS))
			{
				if (isset($site_userids[$row_CMS["id_user"]]))
				{
					$CMS[$row_CMS["username"]]=$row_CMS["id_user"];
				}
			}

			
			
			$cms_username="";
			if (!isset($CMS[$ebayOrder["BuyerUserID"]]))
			{
				$cms_username=$ebayOrder["BuyerUserID"];
			}

			
			if ($cms_username=="")
			{
				if ($usermail!="" && $usermail!="Invalid Request") $cms_username=$usermail[0];
			}
			
			if ($cms_username=="" && isset($ebayOrder["ShippingAddressAddressID"]))
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
			
		//CREATE PASSWORD
		$salt=createPassword(32);
		$pw=createPassword(8);
		$pw=md5($pw);
		$pw=md5($pw.$salt);
		$pw=md5($pw.PEPPER);

		$res_ins=q("INSERT INTO cms_users (
			username, 
			usermail, 
			firstname, 
			lastname, 
			origin, 
			password, 
			user_token, 
			user_salt, 
			userrole_id, 
			language_id, 
			active, 
			firstmod, 
			firstmod_user, 
			lastmod, 
			lastmod_user
		) VALUES (
			'".mysqli_real_escape_string($dbweb, $cms_username)."', 
			'".mysqli_real_escape_string($dbweb, $usermail)."', 
			'".mysqli_real_escape_string($dbweb, $bill_firstname)."', 
			'".mysqli_real_escape_string($dbweb, $bill_lastname)."', 
			'".mysqli_real_escape_string($dbweb, $ebayOrder["ShippingAddressCountry"])."',
			'".mysqli_real_escape_string($dbweb, $pw)."', 
			'".mysqli_real_escape_string($dbweb, createPassword(50))."', 
			'".mysqli_real_escape_string($dbweb, $salt)."', 
			5,
			1,
			1, 
			".time().", 
			".$_SESSION["id_user"].", 
			".time().", 
			".$_SESSION["id_user"]."
		);", $dbweb, __FILE__, __LINE__);
		$cms_user_id=mysqli_insert_id($dbweb);
		
		//VERKNÜPFUNG
		$res_ins=q("INSERT INTO cms_users_sites (user_id, site_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$cms_user_id.", ".$site_id.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);

		}
		
		//ADD ACCOUNT
		if ($cms_user_id!=0 && $shop_user_id==0)
		{
//		echo "INSERT ACCOUNT";	
			$res_ins=q("INSERT INTO crm_customer_accounts3 (
				cms_user_id,
				shop_id, 
				site_id, 
				shop_type, 
				shop_user_id, 
				firstmod, 
				firstmod_user, 
				lastmod, 
				lastmod_user
			) VALUES (
				".$cms_user_id.", 
				".$shop["id_shop"].", 
				".$site_id.", 
				".$shop["shop_type"].", 
				'".mysqli_real_escape_string($dbweb, $ebayOrder["BuyerUserID"])."', 
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
		if ($ebayOrder["ShippingAddressAddressID"]!="" && $cms_user_id!=0)
		{

			$res_check=q("SELECT * FROM shop_bill_adr WHERE foreign_address_id = ".$ebayOrder["ShippingAddressAddressID"]." AND shop_id = ".$shop["id_shop"]." AND user_id = ".$cms_user_id.";", $dbshop, __FILE__, __LINE__);
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
				if ($row_check["additional"]!=$ebayOrder["ShippingAddressStreet2"]) $equals=false;
				if ($row_check["zip"]!=$ebayOrder["ShippingAddressPostalCode"]) $equals=false;
				if ($row_check["city"]!=$bill_city) $equals=false;
				if ($row_check["country"]!=$ebayOrder["ShippingAddressCountryName"]) $equals=false;
				
				if ($equals) $address_id=$row_check["adr_id"];
				
			}
		}
			
		//ADD ADDRESS
		if ($cms_user_id!=0 && $address_id==0)
		{
			//CHECK OB KAUFABWICKLUNG GESCHLOSSEN / ADRESSE VORHANDEN
			if ($ebayOrder["ShippingAddressAddressID"]!="")
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
					'".mysqli_real_escape_string($dbshop, $ebayOrder["ShippingAddressAddressID"])."', 
					'".mysqli_real_escape_string($dbshop, $bill_firstname)."', 
					'".mysqli_real_escape_string($dbshop, $bill_lastname)."', 
					'".mysqli_real_escape_string($dbshop, $bill_street1)."', 
					'".mysqli_real_escape_string($dbshop, $bill_streetNumber)."', 
					'".mysqli_real_escape_string($dbshop, $ebayOrder["ShippingAddressStreet2"])."', 
					'".mysqli_real_escape_string($dbshop, $ebayOrder["ShippingAddressPostalCode"])."', 
					'".mysqli_real_escape_string($dbshop, $bill_city)."', 
					'".mysqli_real_escape_string($dbshop, $ebayOrder["ShippingAddressCountryName"])."', 
					".$countries[$ebayOrder["ShippingAddressCountry"]].", 
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
					site_id, 
					shop_type, 
					number_type, 
					number, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$cms_user_id.", 
					".$shop["id_shop"].", 
					".$site_id.", 
					".$shop["shop_type"].", 
					7, 
					'".mysqli_real_escape_string($dbweb, $usermail)."', 
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
		if ($ebayOrder["ShippingAddressPhone"]!="Invalid Request" && $ebayOrder["ShippingAddressPhone"]!="")
		{
			//$res_check=q("SELECT * FROM crm_numbers_test WHERE number = '".$ebayOrder["ShippingAddressPhone"]."' AND shop_id = ".$shop["id_shop"].";", $dbweb, __FILE__, __LINE__);
			$res_check=q("SELECT * FROM crm_numbers3 WHERE number = '".$ebayOrder["ShippingAddressPhone"]."' AND site_id = ".$site_id." AND shop_type = ".$shop["shop_type"].";", $dbweb, __FILE__, __LINE__);
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
			if ($ebayOrder["ShippingAddressPhone"]!="Invalid Request" && $ebayOrder["ShippingAddressPhone"]!="")
			{
				$res_ins=q("INSERT INTO crm_numbers3 (
					cms_user_id, 
					shop_id, 
					site_id, 
					shop_type, 
					number_type, 
					number, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$cms_user_id.", 
					".$shop["id_shop"].", 
					".$site_id.",
					".$shop["shop_type"].", 
					1, 
					'".mysqli_real_escape_string($dbweb,$ebayOrder["ShippingAddressPhone"])."', 
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

		foreach ($ebayOrderItems as $transaction => $transactiondata)
		{
			$res_IPN=q("SELECT * FROM payment_notifications3 WHERE orderTransactionID = '".$transactiondata["TransactionID"]."' OR parentPaymentTransactionID = '".$transactiondata["TransactionID"]."' ORDER BY payment_date DESC;", $dbshop, __FILE__, __LINE__);
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
		
		if ($address_id==0) $shippingType=0;
		
		//CHECK FOR RIGHT SHIPPINGTYPE ID
		if (isset($shippingTypes[$shippingType]["international"]) && $ebayOrder["ShippingAddressCountry"]=="DE" && $shippingTypes[$shippingType]["international"]!=0) 
		{
			if ($shippingType==5) $shippingType=1;
			elseif ($shippingType==16) $shippingType=15;
		}
		if (isset($shippingTypes[$shippingType]["international"]) && $ebayOrder["ShippingAddressCountry"]!="DE" && $ebayOrder["ShippingAddressCountry"]!="" && $shippingTypes[$shippingType]["international"]!=1) 
		{
			if ($shippingType==15) $shippingType=16;
			else  $shippingType=5;
		}
		
		//ADD ORDER TO SHOP ORDER
		
		$fieldlist=array();
		//BASISFELDER FÜR API-AUFRUF
		$fieldlist["API"]="shop";
		$fieldlist["APIRequest"]="OrderAdd";
		$fieldlist["mode"]="ebay";
		
		//FIELDLIST FOR INSERT
		$fieldlist["shop_id"]=$shop["id_shop"];
		$fieldlist["ordertype_id"]=1;		// ONLINESHOP BESTELLUNG
		$fieldlist["status_id"]=$status_id;
		$fieldlist["status_date"]=$status_date;
		$fieldlist["Currency_Code"]=$ebayOrder["Currency_Code"];
		$fieldlist["foreign_OrderID"]=$ebayOrder["OrderID"];
		$fieldlist["customer_id"]=$cms_user_id;
		$fieldlist["usermail"]=$usermail;
		$fieldlist["userphone"]=$bill_phone;
		$fieldlist["bill_firstname"]=$bill_firstname;
		$fieldlist["bill_lastname"]=$bill_lastname;
		$fieldlist["bill_zip"]=trim($ebayOrder["ShippingAddressPostalCode"]);
		$fieldlist["bill_city"]=$bill_city;
		$fieldlist["bill_street"]=$bill_street1;
		$fieldlist["bill_number"]=$bill_streetNumber;
		$fieldlist["bill_additional"]=$ebayOrder["ShippingAddressStreet2"];
		$fieldlist["bill_country"]=$ebayOrder["ShippingAddressCountryName"];
		$fieldlist["bill_country_code"]=$ebayOrder["ShippingAddressCountry"];
		$fieldlist["shipping_costs"]=$ebayOrder["ShippingServiceSelectedShippingServiceCost"];
		$fieldlist["shipping_type_id"]=$shippingType;
		$fieldlist["shipping_details"]=$shipping_details;
		$fieldlist["Payments_TransactionStateDate"]=$paymentdate;
		$fieldlist["Payments_Type"]=$ebayOrder["CheckoutStatusPaymentMethod"];
		$fieldlist["payments_type_id"]=$payments_type_id;
		$fieldlist["Payments_TransactionState"]=$paymentstatus;
		$fieldlist["Payments_TransactionID"]=$IPNs[0]["paymentTransactionID"];
		$fieldlist["PayPal_BuyerNote"]=$paypalpaymentnote;
		$fieldlist["partner_id"]=0;
		$fieldlist["bill_adr_id"]=$address_id;
		//$fieldlist["ship_adr_id"]=$address_id;
		$fieldlist["firstmod"]=$ebayOrder["CreatedTimeTimestamp"];
		$fieldlist["shipping_net"]=$shipping_costs_net;
		
		$responseXML=post(PATH."soa2/", $fieldlist);

		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response->Ack[0]=="Success")
		{
			$order_id=$response->id_order[0];
			$event_id=$response->id_event[0];
		}
		else
		{
			show_error(9772, 7, __FILE__, __LINE__, $responseXML.print_r($fieldlist, true));
			exit;
		}
		
		unset($response);
		unset($responseXML);
		

		//ADD ORDER ITEMS TO SHOP ORDER ITEMS
		//UPDATE/ADD ITEMS
		foreach ($transactions as $transaction => $transactiondata)
		{
			//check for existing Transaction
			if ($transactiondata["mode"]=="update") 
			{
				//UPDATE TRANSACTION
				// NUR UPDATE DER order_id NÖTIG, ANDERE DATEN ÄNDERN SICH BEI EBAY NICHT

				$fieldlist=array();
				//BASISFELDER FÜR API-AUFRUF
				$fieldlist["API"]="shop";
				$fieldlist["APIRequest"]="OrderItemUpdate";
				
				//FIELDLIST FOR UPDATE
				$fieldlist["order_id"]=$order_id;
				$fieldlist["SELECTOR_id"]=$transactiondata["id"];

				
			}
			else
			{
				//ADD TRANSACION
				$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebayOrderItems[$transaction]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row_item=mysqli_fetch_array($res_item);
				
				if (isset($currencies[$ebayOrderItems[$transaction]["Currency_Code"]]))
				{
					$exchange_rate=$currencies[$ebayOrderItems[$transaction]["Currency_Code"]];
				}
				else
				{
					$exchange_rate=1;
				}
				
				if ($ebayOrderItems[$transaction]["TransactionPrice"]!=0)
				{
					$net=round(($ebayOrderItems[$transaction]["TransactionPrice"]/$ust), 2);
				}
				else
				{
					$net=0;
				}
	
				
				
				$fieldlist=array();
				//BASISFELDER FÜR API-AUFRUF
				$fieldlist["API"]="shop";
				$fieldlist["APIRequest"]="OrderItemAdd";
				$fieldlist["mode"]="ebay";
				
				//FIELDLIST FOR UPDATE
				$fieldlist["order_id"]=$order_id;
				$fieldlist["foreign_transactionID"]=$ebayOrderItems[$transaction]["TransactionID"];
				$fieldlist["item_id"]=$row_item["id_item"];
				$fieldlist["amount"]=$ebayOrderItems[$transaction]["QuantityPurchased"];
				$fieldlist["price"]=$ebayOrderItems[$transaction]["TransactionPrice"];
				$fieldlist["netto"]=$net;
				$fieldlist["Currency_Code"]=$ebayOrderItems[$transaction]["Currency_Code"];
				$fieldlist["exchange_rate_to_EUR"]=$exchange_rate;
				
			}
			
			$responseXML=post(PATH."soa2/", $fieldlist);

			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]!="Success")
			{
				show_error(9773, 7, __FILE__, __LINE__, $responseXML);
				exit;
			}
			
			unset($response);
			unset($responseXML);
		}
		
	//ALERT MAIL - EXPRESS SHIPMENT
	
	if (($shippingType==2 ||$shippingType==7) && ($ebayOrder["account_id"]==1 || $ebayOrder["account_id"]==2 || $ebayOrder["account_id"]==8))
	{
		if ($ebayOrder["account_id"]==1) $reciever="ebay@mapco.de";
		if ($ebayOrder["account_id"]==2) $reciever="ebay@ihr-autopartner.com";
		if ($ebayOrder["account_id"]==8) $reciever="kfroehlich@mapco.de";
		
			$subject = 'NEUE EXPRESSBESTELLUNG bei eBay!!!!!!';
		
			$msg='<p>Es ist eine neue Express-Bestellung bei eBay eingegangen.<p>';
			$msg.='<p>eBay-Mitgliedsname: <b>'.$ebayOrder["BuyerUserID"].'</b></p>';
			$msg.='<p>Käufer E-Mailadresse: <b>'.$ebayOrder["BuyerEmail"].'</b></p>';
			$msg.='<p>eBay-Verkaufsprotokollnummer: <b>'.$ebayOrder["ShippingDetailsSellingManagerSalesRecordNumber"].'</b></p>';
			$msg.='<p>Bestellte Artikel: <br />';

			foreach ($ebayOrderItems as $ebay_orders_item)
			{
				$msg.=$ebay_orders_items["QuantityPurchased"].'x '.$ebay_orders_items["ItemSKU"].' '.$ebay_orders_items["ItemTitle"].' <small>('.$ebay_orders_items["ItemItemID"].')</small><br />';
			}
			
			SendMail($reciever, "Bestellmanagement-System <noreply@mapco.de>", $subject, $msg);
			SendMail("nputzing@mapco.de", "Bestellmanagement-System <noreply@mapco.de>", $subject, $msg);
	
			
			//$response=post(PATH."soa/", array("API" => "crm", "Action" => "AlertMail_ExpressShipment", "OrderID" => $order_id));
	}


	//PAYMENTNOTIFICATIONHANDLER 
	$response = "";
	$responseXML = "";
	$fieldlist=array();
	//BASISFELDER FÜR API-AUFRUF
	$fieldlist["API"]="payments";
	$fieldlist["APIRequest"]="PaymentNotificationHandler";
	$fieldlist["mode"]="OrderAdd";
	$fieldlist["orderid"]=$order_id;
	$fieldlist["order_event_id"]=$event_id;
	
	$responseXML=post(PATH."soa2/", $fieldlist);

	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXML);
	}
	catch(Exception $e)
	{
		show_error(9756, 7, __FILE__, __LINE__, $responseXML, false);
		//exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if ($response->Ack[0]!="Success")
	{
		show_error(9773, 7, __FILE__, __LINE__, $responseXML, false);
		//exit;
	}
	
	unset($response);
	unset($responseXML);
		
	} // MODE ADD


/*#######################################################################################################
	Order Update
#######################################################################################################*/


	if ($ordermode=="update")
	{
		
		
		$IPNs=array();
		$paypalpaymentnote="";
		foreach ($ebayOrderItems as $transaction => $transactiondata)
		{
			$res_IPN=q("SELECT * FROM payment_notifications3 WHERE orderTransactionID = '".$transactiondata["TransactionID"]."' OR parentPaymentTransactionID = '".$transactiondata["TransactionID"]."' ORDER BY payment_date DESC;", $dbshop, __FILE__, __LINE__);
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

		
		
		// CHECK, ob ADRESSE manuell im Backend geändert wurder - > kein Update der Adresse
		$res_shop_order = q("SELECT * FROM shop_orders WHERE id_order = ".$update_orderid.";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shop_order)==0)
		{
			//SHOP Order mit angegebener id_order nicht gefunden
			show_error(9774, 7, __FILE__, __LINE__, "shop_order.id_order: ".$update_orderid);
			exit;
		}
		else
		{
			$shop_order = mysqli_fetch_array($res_shop_order);
			$address_updated = $shop_order["bill_address_manual_update"];
		}
		
		$address_id=0;
		if ( $shop_order["bill_address_manual_update"]==0 && $bill_street1!="")
		{

			if (isset($shop_order) && $shop_order["bill_adr_id"]!=0 && $shop_order["customer_id"]!=0)
			{
				
				$res_check_adr = q ("SELECT * FROM shop_bill_adr WHERE adr_id = ".$shop_order["bill_adr_id"]." AND user_id = ".$shop_order["customer_id"].";", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_check_adr)==0)
				{
					//INSERT ADDRESS
					$update_address = true;
				}
				else
				{
					$row_check_adr = mysqli_fetch_array($res_check_adr);
					// CHECK OB SICH ADDRESSEN UNTERSCHEIDEN
					$equals=true;
					if ($row_check_adr["firstname"]!=$bill_firstname) $equals=false;
					if ($row_check_adr["lastname"]!=$bill_lastname) $equals=false;
					if ($row_check_adr["street"]!=$bill_street1) $equals=false;
					if ($row_check_adr["number"]!=$bill_streetNumber) $equals=false;
					if ($row_check_adr["additional"]!=$ebayOrder["ShippingAddressStreet2"]) $equals=false;
					if ($row_check_adr["zip"]!=$ebayOrder["ShippingAddressPostalCode"]) $equals=false;
					if ($row_check_adr["city"]!=$bill_city) $equals=false;
					if ($row_check_adr["country"]!=$ebayOrder["ShippingAddressCountryName"]) $equals=false;
					
					if ($equals) $address_id=$row_check_adr["adr_id"]; else $update_address = true;

					
				}
				
				
			
			}
			
			if (isset($shop_order) && $shop_order["bill_adr_id"]==0 && $shop_order["customer_id"]!=0)
			{
				$update_address = true;
			}
			
			if ($update_address)
			{
				if (isset($countries[$ebayOrder["ShippingAddressCountry"]]) )
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
						".$shop_order["customer_id"].", 
						".$shop["id_shop"].", 
						'".mysqli_real_escape_string($dbshop, $ebayOrder["ShippingAddressAddressID"])."', 
						'".mysqli_real_escape_string($dbshop, $bill_firstname)."', 
						'".mysqli_real_escape_string($dbshop, $bill_lastname)."', 
						'".mysqli_real_escape_string($dbshop, $bill_street1)."', 
						'".mysqli_real_escape_string($dbshop, $bill_streetNumber)."', 
						'".mysqli_real_escape_string($dbshop, $ebayOrder["ShippingAddressStreet2"])."', 
						'".mysqli_real_escape_string($dbshop, $ebayOrder["ShippingAddressPostalCode"])."', 
						'".mysqli_real_escape_string($dbshop, $bill_city)."', 
						'".mysqli_real_escape_string($dbshop, $ebayOrder["ShippingAddressCountryName"])."', 
						".$countries[$ebayOrder["ShippingAddressCountry"]].", 
						0,
						1 
					);", $dbshop, __FILE__, __LINE__);
				
					$address_id=mysqli_insert_id($dbshop);
				}
				else
				{
					show_error(9817,7, __FILE__, __LINE__, "Ländercode: ".$ebayOrder["ShippingAddressCountry"] );	
				}
			}
			
			if (!isset($address_id)) $address_id=0;

		}
		else
		{
			$address_id=$shop_order["bill_adr_id"];
		}
		
		//CHECK FOR RIGHT SHIPPINGTYPE ID
		if (isset($shippingTypes[$shippingType]["international"]) && $ebayOrder["ShippingAddressCountry"]=="DE" && $shippingTypes[$shippingType]["international"]!=0) 
		{
			if ($shippingType==5) $shippingType=1;
			elseif ($shippingType==16) $shippingType=15;
		}
		if (isset($shippingTypes[$shippingType]["international"]) && $ebayOrder["ShippingAddressCountry"]!="DE" && $ebayOrder["ShippingAddressCountry"]!="" && $shippingTypes[$shippingType]["international"]!=1) 
		{
			if ($shippingType==15) $shippingType=16;
			else  $shippingType=5;
		}

		
	
			$fieldlist=array();
			//BASISFELDER FÜR API-AUFRUF
			$fieldlist["API"]="shop";
			$fieldlist["APIRequest"]="OrderUpdate";
			$fieldlist["mode"]="ebay";
			
			//FIELDLIST FOR UPDATE
			$fieldlist["SELECTOR_id_order"]=$update_orderid;
			$fieldlist["foreign_OrderID"]=$ebayOrder["OrderID"];
			$fieldlist["ordertype_id"]=1;		// ONLINESHOP BESTELLUNG
			$fieldlist["Payments_TransactionStateDate"]=$paymentdate;
			$fieldlist["Payments_TransactionID"]=$IPNs[0]["paymentTransactionID"];
			$fieldlist["PayPal_BuyerNote"]=$paypalpaymentnote;
			$fieldlist["Payments_Type"]=$ebayOrder["CheckoutStatusPaymentMethod"];
			$fieldlist["payments_type_id"]=$payments_type_id;
			$fieldlist["Payments_TransactionState"]=$paymentstatus;
			$fieldlist["lastmod"]=time();
			$fieldlist["lastmod_user"]=$_SESSION["id_user"];
			
			if ($shop_order["bill_address_manual_update"]==0 && $bill_street1!="")
			{
				$fieldlist["usermail"]=$usermail;
				$fieldlist["userphone"]=$bill_phone;
				
				$fieldlist["bill_firstname"]=$bill_firstname;
				$fieldlist["bill_lastname"]=$bill_lastname;
				$fieldlist["bill_zip"]=trim($ebayOrder["ShippingAddressPostalCode"]);
				$fieldlist["bill_city"]=$bill_city;
				$fieldlist["bill_street"]=$bill_street1;
				$fieldlist["bill_number"]=$bill_streetNumber;
				$fieldlist["bill_additional"]=$ebayOrder["ShippingAddressStreet2"];
				$fieldlist["bill_country"]=$ebayOrder["ShippingAddressCountryName"];
				$fieldlist["bill_country_code"]=$ebayOrder["ShippingAddressCountry"];
			/*	
				$fieldlist["ship_firstname"]=$bill_firstname;
				$fieldlist["ship_lastname"]=$bill_lastname;
				$fieldlist["ship_zip"]=trim($ebayOrder["ShippingAddressPostalCode"]);
				$fieldlist["ship_city"]=$bill_city;
				$fieldlist["ship_street"]=$bill_street1;
				$fieldlist["ship_number"]=$bill_streetNumber;
				$fieldlist["ship_additional"]=$ebayOrder["ShippingAddressStreet2"];
				$fieldlist["ship_country"]=$ebayOrder["ShippingAddressCountryName"];
				$fieldlist["ship_country_code"]=$ebayOrder["ShippingAddressCountry"];
			*/	
				$fieldlist["shipping_costs"]=$ebayOrder["ShippingServiceSelectedShippingServiceCost"];
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
				if ($shop_order["status_id"]==1 || $shop_order["status_id"]==4) 
				{
					$fieldlist["status_id"]=$status_id;
				}
			}

			$responseXML=post(PATH."soa2/", $fieldlist);

			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]!="Success")
			{
				show_error(9775, 7, __FILE__, __LINE__, $responseXML);
				exit;
			}
			else
			{
				$event_id=$response->id_event[0];
			}
			
			unset($response);
			unset($responseXML);

	
		
		//UPDATE/ADD ITEMS
		foreach ($transactions as $transaction => $transactiondata)
		{
			//check for existing Transaction
			if ($transactiondata["mode"]=="update") 
			{
				//UPDATE TRANSACTION
				// NUR UPDATE DER order_id NÖTIG, ANDERE DATEN ÄNDERN SICH BEI EBAY NICHT

				$fieldlist=array();
				//BASISFELDER FÜR API-AUFRUF
				$fieldlist["API"]="shop";
				$fieldlist["APIRequest"]="OrderItemUpdate";
				$fieldlist["mode"]="ebay";

				
				//FIELDLIST FOR UPDATE
				$fieldlist["order_id"]=$update_orderid;
				$fieldlist["SELECTOR_id"]=$transactiondata["id"];

				
			}
			else
			{
				//ADD TRANSACION
				$res_item=q("SELECT id_item FROM shop_items WHERE MPN = '".$ebayOrderItems[$transaction]["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row_item=mysqli_fetch_array($res_item);
				
				if (isset($currencies[$ebayOrderItems[$transaction]["Currency_Code"]]))
				{
					$exchange_rate=$currencies[$ebayOrderItems[$transaction]["Currency_Code"]];
				}
				else
				{
					$exchange_rate=1;
				}
				
				if ($ebayOrderItems[$transaction]["TransactionPrice"]!=0)
				{
					$net=round(($ebayOrderItems[$transaction]["TransactionPrice"]/$ust), 2);
				}
				else
				{
					$net=0;
				}
	
				
				
				$fieldlist=array();
				//BASISFELDER FÜR API-AUFRUF
				$fieldlist["API"]="shop";
				$fieldlist["APIRequest"]="OrderItemAdd";
				$fieldlist["mode"]="ebay";
				
				//FIELDLIST FOR UPDATE
				$fieldlist["order_id"]=$update_orderid;
				$fieldlist["foreign_transactionID"]=$ebayOrderItems[$transaction]["TransactionID"];
				$fieldlist["item_id"]=$row_item["id_item"];
				$fieldlist["amount"]=$ebayOrderItems[$transaction]["QuantityPurchased"];
				$fieldlist["price"]=$ebayOrderItems[$transaction]["TransactionPrice"];
				$fieldlist["netto"]=$net;
				$fieldlist["Currency_Code"]=$ebayOrderItems[$transaction]["Currency_Code"];
				$fieldlist["exchange_rate_to_EUR"]=$exchange_rate;
				
			}
			
			$responseXML=post(PATH."soa2/", $fieldlist);

			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]!="Success")
			{
				show_error(9776, 7, __FILE__, __LINE__, $responseXML);
				exit;
			}
			
			unset($response);
			unset($responseXML);
		}
		
		//UNSET LINK BETWEEN OLD TRANSACTIONS (THAT AREN'T PART OF ACTUAL ORDER) AND UPDATE ORDER
		foreach ($updateOrderTransactions as $updateOrderTransaction => $data)
		{
			if (!isset($ebayOrderItems[$updateOrderTransaction]))	
			{
				
				q("UPDATE shop_orders_items SET order_id = 0 WHERE id = ".$updateOrderTransactions[$updateOrderTransaction].";", $dbshop, __FILE__, __LINE__);
				echo mysqli_error($dbshop);
			}
		}
		
		//DEACTIVATE other OLD ORDERS	
		foreach ($know_shop_orders as $shop_order_id => $shop_order_data)
		{
			if ($shop_order_id!=$update_orderid)
			{
				// CHECK IF other old ORDER HAS ITEM(S)
				$res_check = q("SELECT * FROM shop_orders_items WHERE order_id = ".$shop_order_id.";", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_check)==0)
				{
					//NO ITEMS FOR other old order
					q("UPDATE shop_orders SET ordertype_id=6, combined_with=0, bill_address_manual_update=0 WHERE id_order = ".$shop_order_id.";", $dbshop, __FILE__, __LINE__);
				}
				else
				{
					q("UPDATE shop_orders SET combined_with=0 WHERE id_order = ".$shop_order_id.";", $dbshop, __FILE__, __LINE__);
				}
			}
		}
		
		//CHECK FOR VALID COMBINATION
		if ($know_shop_orders[$update_orderid]["combined_with"]!=0 && $know_shop_orders[$update_orderid]["combined_with"]!=-1)
		{
			$res_check = q("SELECT * FROM shop_orders WHERE combined_with = ".$know_shop_orders[$update_orderid]["combined_with"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_check)<2)
			{
				q("UPDATE shop_orders SET combined_with = 0 WHERE combined_with = ".$know_shop_orders[$update_orderid]["combined_with"].";", $dbshop, __FILE__, __LINE__);
			}
		}
		
		//PAYMENTNOTIFICATIONHANDLER 
		$response = "";
		$responseXML = "";
		$fieldlist=array();
		//BASISFELDER FÜR API-AUFRUF
		$fieldlist["API"]="payments";
		$fieldlist["APIRequest"]="PaymentNotificationHandler";
		$fieldlist["mode"]="OrderAdjustment";
		$fieldlist["orderid"]=$update_orderid;
		$fieldlist["order_event_id"]=$event_id;
		
		$responseXML=post(PATH."soa2/", $fieldlist);
	
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			show_error(9756, 7, __FILE__, __LINE__, $responseXML, false);
			//exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response->Ack[0]!="Success")
		{
			show_error(9773, 7, __FILE__, __LINE__, $responseXML, false);
			//exit;
		}
		
		unset($response);
		unset($responseXML);
		

	}

?>

