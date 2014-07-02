<?php
include("../functions/cms_createPassword.php");

$required = array("AmazonOrderId" => "textNN");
check_man_params($required);

// keep post submit
$post = $_POST;

$amazonOrdersQuery = "
	SELECT * 
	FROM amazon_orders 
	WHERE AmazonOrderId = '" . $post['AmazonOrderId'] . "' 
	AND importShopStatus = 0";
$amazonOrdersResult = q($amazonOrdersQuery, $dbshop, __FILE__, __LINE__);
			
if (mysqli_num_rows($amazonOrdersResult) == 0) {
	show_error(9866,7,__FILE__, __LINE__, "AmazonOrderId = " . $post["AmazonOrderId"]);	
	exit;
}
$amazon_order = mysqli_fetch_assoc($amazonOrdersResult);

//14.03.2014 23:59:59
if (strtotime($amazon_order["PurchaseDate"]) > 1394837999) {
	//GET ORDERITEMS
		$orderItems = array();
		$shippingCosts = 0;
		$amazonOrdersItemsResults = q("
			SELECT * 
			FROM amazon_order_items 
			WHERE AmazonOrderId = '" . $amazon_order["AmazonOrderId"] . "'", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($amazonOrdersItemsResults) == 0) {
			//	NO ITEMS
			show_error(9867,7,__FILE__, __LINE__, "AmazonOrderId = " . $post["AmazonOrderId"]);	
			q("UPDATE amazon_orders 
				SET importShopStatus = 0 
				WHERE AmazonOrderId = '" . $post["AmazonOrderId"] . "'", $dbshop, __FILE__, __LINE__ );
			exit;
		}
		while ($orderItem = mysqli_fetch_assoc($amazonOrdersItemsResults))
		{
			$orderItems[$orderItem["OrderItemId"]] = $orderItem;
			
			//SUM SHIPPING COSTS
			$shippingCosts+= $orderItem["ShippingPriceAmount"];
		}
	
		//GET SHOP_SHOP
		$res_shop = q("
			SELECT b.* 
			FROM amazon_accounts as a, shop_shops as b 
			WHERE a.MarketplaceId = '" . $amazon_order["MarketplaceId"] . "' 
			AND a.id_account = b.account_id AND b.shop_type = 3", $dbshop, __FILE__, __LINE__); 
		if (mysqli_num_rows($res_shop) == 0) {
			show_error(9868,7,__FILE__, __LINE__, "MarketplaceId = ".$amazon_order["MarketplaceId"]);	
			q("UPDATE amazon_orders 
				SET importShopStatus = 0 
				WHERE AmazonOrderId = '" . $post["AmazonOrderId"] . "'", $dbshop, __FILE__, __LINE__ );
			exit;
		}
		$shop = mysqli_fetch_assoc($res_shop);
	
		//GET COUNTRY
		$res_country = q("
			SELECT * 
			FROM shop_countries 
			WHERE country_code = '" . $amazon_order["ShippingAddressCountryCode"] . "'", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_country) == 0)
		{
			show_error(9817,7,__FILE__, __LINE__, "country_code = " . $amazon_order["ShippingAddressCountryCode"]);	
			q("UPDATE amazon_orders 
				SET importShopStatus = 0 
				WHERE AmazonOrderId = '" . $post["AmazonOrderId"] . "'", $dbshop, __FILE__, __LINE__ );
			exit;
		}
		$country = mysqli_fetch_assoc($res_country);
	
		//GET SHIPPING_TYPE_ID
		$res_shipping_type = q("
			SELECT * 
			FROM amazon_shipping_types 
			WHERE ShipServiceLevel = '" . $amazon_order["ShipServiceLevel"] . "'", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shipping_type) == 0) {
			show_error(9869,7,__FILE__, __LINE__, "ShipServiceLevel = " . $amazon_order["ShipServiceLevel"]);	
			q("UPDATE amazon_orders 
				SET importShopStatus = 0 
				WHERE AmazonOrderId = '" . $post["AmazonOrderId"] . "'", $dbshop, __FILE__, __LINE__ );
			exit;
		}
		$row_shipping_type = mysqli_fetch_assoc($res_shipping_type);
		$shipping_id = $row_shipping_type["shipping_type_id"];
	
		//GET VAT FOR COUNTRY
		$VAT = $country["VAT"];
		if ($VAT == "" || $VAT == 0) {
			$VAT = 19;	
		}
		
		//AUSNAHME AUTOPARTNER -> immer 19 % VAT
		if ($shop["id_shop"] == 6) {
			$VAT = 19;	
		}
		
		if ($VAT == 0) {
			$VAT_multiplier = 1;
		} else {
			$VAT_multiplier = ($VAT/100)+1;
		}
		
		//GET EXCHANGERATE
		$res_currency = q("
			SELECT * 
			FROM shop_currencies 
			WHERE currency_code = '" . $amazon_order["OrderTotalCurrencyCode"] . "'", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_currency) == 0) {
			show_error(9870,7,__FILE__, __LINE__, "currency_code = " . $amazon_order["OrderTotalCurrencyCode"]);	
			exit;
		}
		$currency=mysqli_fetch_assoc($res_currency);
	
		//PREPARE ADDRESS
		$packstation=false;
		//PACKSTATION
			//search for "PACKSTATION";
			if (strpos(strtolower($amazon_order["ShippingAddressName"]),"packstation") === true) {
				$packstation = true;
				$tmp = $amazon_order["ShippingAddressName"];
				$amazon_order["ShippingAddressName"] = $amazon_order["ShippingAddressAddressLine2"];
				$amazon_order["ShippingAddressAddressLine2"] = $tmp;
			}
			if (strpos($amazon_order["ShippingAddressAddressLine2"], "Packstation") !== false) {
				$packstation = true;
				$tmp = $amazon_order["ShippingAddressAddressLine1"];
				$amazon_order["ShippingAddressAddressLine1"] = $amazon_order["ShippingAddressAddressLine2"];
				$amazon_order["ShippingAddressAddressLine2"] = $tmp;
			}
			
			if ($packstation) {
				if (is_numeric($amazon_order["ShippingAddressName"]) 
					&& strlen($amazon_order["ShippingAddressName"]) >= 7 
					&& strlen($amazon_order["ShippingAddressName"]) <= 10) {
						
					$tmp = $amazon_order["ShippingAddressName"];
					$amazon_order["ShippingAddressName"] = $amazon_order["ShippingAddressAddressLine2"];
					$amazon_order["ShippingAddressAddressLine2"] = $tmp;
				}
			}
		
		if (strpos($amazon_order["ShippingAddressName"]," ") === false) {
			$bill_firstname = substr($amazon_order["ShippingAddressName"], 0, strpos($amazon_order["ShippingAddressName"],"."));
			
			$bill_lastname = substr($amazon_order["ShippingAddressName"], strpos($amazon_order["ShippingAddressName"],".")+1);
		} else {
			$bill_firstname = substr($amazon_order["ShippingAddressName"], 0, strpos($amazon_order["ShippingAddressName"]," "));
			
			$bill_lastname = substr($amazon_order["ShippingAddressName"], strpos($amazon_order["ShippingAddressName"]," ")+1);
		}
		
		if ($bill_firstname == "") {
			$bill_lastname = $amazon_order["ShippingAddressName"];
		}
			
		$has_number = false;		
		$pos = 0;
		for ($i = strlen($amazon_order["ShippingAddressAddressLine2"])-1; $i>-1; $i--)
		{
			if ((is_numeric(substr($amazon_order["ShippingAddressAddressLine2"],$i, 1)) 
				|| substr($amazon_order["ShippingAddressAddressLine2"],$i, 1)=="/") 
				&& $pos == 0) {
					
				if (!$has_number) $has_number = true;
			} else {
				if ($has_number && $pos == 0) $pos = $i;
			}
		}

		if($pos == 0) {
			$bill_street1 = $amazon_order["ShippingAddressAddressLine2"];
			$bill_streetNumber = "0";
		} else {
			$bill_street1 = trim(substr($amazon_order["ShippingAddressAddressLine2"], 0, $pos+1));	
			$bill_streetNumber = trim(substr($amazon_order["ShippingAddressAddressLine2"], $pos+1));
		}
		
		//ADD StateOrProvince
		if ($amazon_order["ShippingAddressStateOrRegion"] == "") {
			$bill_city = $amazon_order["ShippingAddressCity"];
		} else {
			$bill_city = $amazon_order["ShippingAddressCity"].", ".$amazon_order["ShippingAddressStateOrRegion"];
		}
	
		//NETTOVERSANDKOSTEN
		$shipping_costs_net = round($shippingCosts/$VAT_multiplier,2);
	
		//STATUS_ID festlegen
		$status_id = 0;
		if ($amazon_order["OrderStatus"] == "Unshipped") $status_id = 7;
		if ($amazon_order["OrderStatus"] == "Shipped") $status_id = 3;
		if ($amazon_order["OrderStatus"] == "Canceled") $status_id = 8;
	
		$status_date = strtotime($amazon_order["LastUpdateDate"]);
	
		//CHECK FOR CUSTOMER ALREADY KNOWN
		$cms_user_id = 0;
		$res_check=q("
			SELECT * 
			FROM crm_customer_accounts3 
			WHERE shop_user_id = '".$amazon_order["BuyerEmail"]."' 
			AND shop_type = 3 
			AND site_id = ".$shop["site_id"].";", $dbweb, __FILE__, __LINE__);

        if (mysqli_num_rows($res_check)>0) {
			$row_check=mysqli_fetch_array($res_check);
			$shop_user_id=$row_check["shop_user_id"];
			$cms_user_id=$row_check["cms_user_id"];
		}
		
		//USER ANLEGEN
		if ($cms_user_id == 0) { 
			// USER UNKNOWN
			//GET USER_IDs from cms_users_sites WHERE SITE_ID = $site_id
			$site_userids = array();
			$res_site_userids = q("
				SELECT * 
				FROM cms_users_sites 
				WHERE site_id = ".$shop["site_id"].";", $dbweb, __FILE__, __LINE__);
			while ($row_site_userids = mysqli_fetch_array($res_site_userids))
			{
				$site_userids[$row_site_userids["user_id"]] = $row_site_userids["site_id"];
			}
			
			//GET existing CMS users
			$CMS = array();
			$res_CMS=q("SELECT * FROM cms_users;" , $dbweb, __FILE__, __LINE__);
			while ($row_CMS=mysqli_fetch_array($res_CMS))
			{
				if (isset($site_userids[$row_CMS["id_user"]])) {
					$CMS[$row_CMS["username"]] = $row_CMS["id_user"];
				}
			}
	
			//CREATE USERNAME
			$tmp = $bill_lastname;
			$cms_username = "";
			if (!isset($CMS[$tmp])) $cms_username = $tmp;
	
			if ($cms_username == "") {
				$counter = 1;
				$tmp = $bill_lastname . "_" . (string)$counter;
				while (isset($CMS[$tmp]))
				{
					$counter++;
					$tmp = $bill_lastname . "_" . (string)$counter;
				}
				$cms_username = $tmp;
			}
	
			//CREATE PASSWORD
			$salt = createPassword(32);
			$pw = createPassword(8);
			$pw = md5($pw);
			$pw = md5($pw.$salt);
			$pw = md5($pw.PEPPER);
	
			$insert_field = array();
			$insert_field["username"] = $cms_username;
			$insert_field["firstname"] = $bill_firstname;
			$insert_field["lastname"] = $bill_lastname;
			$insert_field["origin"] = $amazon_order["ShippingAddressCountryCode"];
			$insert_field["password"] = $pw;
			$insert_field["user_token"] = createPassword(50);
			$insert_field["user_salt"] = $cms_username;
			$insert_field["userrole_id"] = 5;
			$insert_field["language_id"] = 1;
			$insert_field["active"] = 1;
			$insert_field["firstmod"] = time();
			$insert_field["firstmod_user"] = $_SESSION["id_user"];
			$insert_field["lastmod"] = time();
			$insert_field["lastmod_user"] = $_SESSION["id_user"];
			
			q_insert("cms_users", $insert_field, $dbweb, __FILE__, __LINE__);
			$cms_user_id = mysqli_insert_id($dbweb);
			
			//SET USERSITE
			$insert_field = array();
			$insert_field["user_id"] = $cms_user_id;
			$insert_field["site_id"] = $shop["site_id"];
			$insert_field["firstmod"] = time();
			$insert_field["firstmod_user"] = $_SESSION["id_user"];
			$insert_field["lastmod"] = time();
			$insert_field["lastmod_user"] = $_SESSION["id_user"];
			q_insert("cms_users_sites", $insert_field, $dbweb, __FILE__, __LINE__);
			
			//SET CRM_CUSTOMER_ACCOUNT
			$insert_field = array();
			$insert_field["cms_user_id"] = $cms_user_id;
			$insert_field["shop_id"] = $shop["id_shop"];
			$insert_field["site_id"] = $shop["site_id"];
			$insert_field["shop_type"] = $shop["shop_type"];
			$insert_field["shop_user_id"] = $amazon_order["BuyerEmail"];
			$insert_field["firstmod"] = time();
			$insert_field["firstmod_user"] = $_SESSION["id_user"];
			$insert_field["lastmod"] = time();
			$insert_field["lastmod_user"] = $_SESSION["id_user"];
			q_insert("crm_customer_accounts3", $insert_field, $dbweb, __FILE__, __LINE__);
			
		} else {
			//echo  "USER KNOWN: ".$cms_user_id." ";
		}
		
		//CHECK FOR KNOWN ADDRESS
		$address_id=0;
		$res_check=q("SELECT * FROM shop_bill_adr WHERE user_id = ".$cms_user_id." AND active = 1", $dbshop, __FILE__, __LINE__);
		while ($row_check=mysqli_fetch_array($res_check))
		{
			if ($row_check["firstname"] == $bill_firstname 
				&& $row_check["lastname"] == $bill_lastname 
				&& $row_check["street"] == $bill_street1 
				&& $row_check["number"] == $bill_streetNumber 
				&& $row_check["additional"] == $amazon_order["ShippingAddressAddressLine1"] 
				&& $row_check["zip"] == trim($amazon_order["ShippingAddressPostalCode"]) 
				&& $row_check["city"] == $bill_city  
				&& $row_check["country_id"] == $country["id_country"]) {
					
				$address_id=$row_check["adr_id"];
			}
		}
	
		// ADRESSE ANLEGEN
		if ($address_id == 0) {
			
			$insert_field = array();
			$insert_field["user_id"] = $cms_user_id;
			$insert_field["shop_id"] = $shop["id_shop"];
			$insert_field["firstname"] = $bill_firstname;
			$insert_field["lastname"] = $bill_lastname;
			$insert_field["street"] = $bill_street1;
			$insert_field["number"] = $bill_streetNumber;
			$insert_field["additional"] = $amazon_order["ShippingAddressAddressLine1"];
			$insert_field["zip"] = trim($amazon_order["ShippingAddressPostalCode"]);
			$insert_field["city"] = $bill_city;
			$insert_field["country"] = $country["country"];
			$insert_field["country_id"] = $country["id_country"];
		
			q_insert("shop_bill_adr", $insert_field, $dbshop, __FILE__, __LINE__);
			$address_id = mysqli_insert_id($dbshop);
			
		} else {
			//	echo "ADRESS KNOWN ";	
		}
		
		//CHECK FOR KNOWN PHONENUMBER
		$number_id=0;
		$res_check=q("
			SELECT * 
			FROM crm_numbers3 
			WHERE cms_user_id = ".$cms_user_id." 
			AND shop_id = " . $shop["id_shop"] . " 
			AND number = '" . trim($amazon_order["ShippingAdressPhone"]) . "' 
			AND number_type = 1", $dbweb, __FILE__, __LINE__);
		if (mysqli_fetch_array($res_check) == 0) {
			// NUMBER UNKNOWN
			// INSERT NUMBER
			$insert_field = array();
			$insert_field["cms_user_id"] = $cms_user_id;
			$insert_field["shop_id"] = $shop["id_shop"];
			$insert_field["site_id"] = $shop["site_id"];
			$insert_field["shop_type"] = $shop["shop_type"];
			$insert_field["number_type"] = 1;
			$insert_field["number"] = trim($amazon_order["ShippingAdressPhone"]);
			$insert_field["firstmod"] = time();
			$insert_field["firstmod_user"] = $_SESSION["id_user"];
			$insert_field["lastmod"] = time();
			$insert_field["lastmod_user"] = $_SESSION["id_user"];
		
			q_insert("crm_numbers3", $insert_field, $dbweb, __FILE__, __LINE__);
			$address_id = mysqli_insert_id($dbshop);
		} else {
			// echo "Phone KNOWN ";	
		}
		
        //CHECK IF ORDER IS ALREADY KNOWN
        $res_order = q("
            SELECT *
            FROM shop_orders
            WHERE foreign_OrderID = '".$amazon_order["AmazonOrderId"]."'
            AND shop_id = ".$shop["id_shop"], $dbshop, __FILE, __LINE__);
        if (mysqli_num_rows($res_order) == 0) {
		
            //INSERT ORDER
            $fieldlist = array();
		
            //BASISFELDER FÜR API-AUFRUF
            $fieldlist["API"]="shop";
            $fieldlist["APIRequest"]="OrderAdd";
            $fieldlist["mode"]="ebay";

            //FIELDLIST FOR INSERT
            $fieldlist["shop_id"]=$shop["id_shop"];
            $fieldlist["ordertype_id"]=1;		// ONLINESHOP BESTELLUNG
            $fieldlist["status_id"]=$status_id;
            $fieldlist["status_date"]=$status_date;
            $fieldlist["Currency_Code"]=$amazon_order["OrderTotalCurrencyCode"];
            $fieldlist["VAT"]=$VAT;
            $fieldlist["foreign_OrderID"]=$amazon_order["AmazonOrderId"];
            $fieldlist["customer_id"]=$cms_user_id;
            $fieldlist["usermail"]="";
            $fieldlist["userphone"]=trim($amazon_order["ShippingAdressPhone"]);
            $fieldlist["bill_firstname"]=$bill_firstname;
            $fieldlist["bill_lastname"]=$bill_lastname;
            $fieldlist["bill_zip"]=trim($amazon_order["ShippingAddressPostalCode"]);
            $fieldlist["bill_city"]=$bill_city;
            $fieldlist["bill_street"]=$bill_street1;
            $fieldlist["bill_number"]=$bill_streetNumber;
            $fieldlist["bill_additional"]=$amazon_order["ShippingAddressAddressLine1"];
            $fieldlist["bill_country"]=$country["country"];
            $fieldlist["bill_country_code"]=$amazon_order["ShippingAddressCountryCode"];
            $fieldlist["shipping_costs"]=$shippingCosts;
            $fieldlist["shipping_type_id"]=$shipping_id;
            $fieldlist["shipping_details"]=$shipping_details;
            $fieldlist["Payments_TransactionStateDate"] = strtotime($amazon_order["PurchaseDate"]);
            $fieldlist["Payments_Type"] = "Vorkasse";
            $fieldlist["payments_type_id"] = 2;
			$fieldlist["Payments_TransactionState"] = "Created";
//			$fieldlist["Payments_TransactionID"]=$IPNs[0]["paymentTransactionID"];
            $fieldlist["bill_adr_id"] = $address_id;
            $fieldlist["firstmod"] = strtotime($amazon_order["PurchaseDate"]);
            $fieldlist["shipping_net"] = $shipping_costs_net;
            $response = soa2($fieldlist, __FILE__, __LINE__, "obj");

            if ((string)$response->Ack[0] != "Success") {
                show_error(9797,8,__FILE__, __LINE__, 'API Shop->OrderAdd / ' . $response);
                exit;
            }

            $order_id=(int)$response->id_order[0];
            $event_id=(int)$response->id_event[0];
            echo "<order_id>".$order_id."</order_id>";

            $OK = true;

        } else {
		
            $order = mysqli_fetch_assoc($res_order);
            $order_id=$order["id_order"];
            echo "<order_id>".$order_id."</order_id>";

            //UPDATE ORDER
            $fieldlist = array();

            //BASISFELDER FÜR API-AUFRUF
            $fieldlist["API"]="shop";
            $fieldlist["APIRequest"]="OrderUpdate";
            $fieldlist["mode"]="ebay";
		
            //FIELDLIST FOR INSERT
            $fieldlist["SELECTOR_id_order"]=$order_id;
            $fieldlist["shop_id"]=$shop["id_shop"];
            $fieldlist["ordertype_id"]=1;		// ONLINESHOP BESTELLUNG
            $fieldlist["status_id"]=$status_id;
            $fieldlist["status_date"]=$status_date;
            $fieldlist["Currency_Code"]=$amazon_order["OrderTotalCurrencyCode"];
            $fieldlist["VAT"]=$VAT;
            $fieldlist["foreign_OrderID"]=$amazon_order["AmazonOrderId"];
            $fieldlist["customer_id"]=$cms_user_id;
            $fieldlist["usermail"]="";
            $fieldlist["userphone"]=trim($amazon_order["ShippingAdressPhone"]);
            $fieldlist["bill_firstname"]=$bill_firstname;
            $fieldlist["bill_lastname"]=$bill_lastname;
            $fieldlist["bill_zip"]=trim($amazon_order["ShippingAddressPostalCode"]);
            $fieldlist["bill_city"]=$bill_city;
            $fieldlist["bill_street"]=$bill_street1;
            $fieldlist["bill_number"]=$bill_streetNumber;
            $fieldlist["bill_additional"]=$amazon_order["ShippingAddressAddressLine1"];
            $fieldlist["bill_country"]=$country["country"];
            $fieldlist["bill_country_code"]=$amazon_order["ShippingAddressCountryCode"];
            $fieldlist["shipping_costs"]=$shippingCosts;
            $fieldlist["shipping_type_id"]=$shipping_id;
            $fieldlist["shipping_details"]=$shipping_details;
            $fieldlist["Payments_TransactionStateDate"]=strtotime($amazon_order["PurchaseDate"]);
            $fieldlist["Payments_Type"]="Vorkasse";
            $fieldlist["payments_type_id"]=2;
//			$fieldlist["Payments_TransactionState"]="Created";
//			$fieldlist["Payments_TransactionID"]=$IPNs[0]["paymentTransactionID"];
            $fieldlist["bill_adr_id"]=$address_id;
            $fieldlist["firstmod"]=strtotime($amazon_order["PurchaseDate"]);
            $fieldlist["shipping_net"]=$shipping_costs_net;
            $response = soa2($fieldlist, __FILE__, __LINE__, "obj");
			
            if ((string)$response->Ack[0]!="Success") {
				$showInfo = 'API Shop->OrderUpdate / ' . "\n";
				$showInfo.= (string)$response . "\n";			
				show_error(9797,8,__FILE__, __LINE__, $showInfo);
                exit;
            }
            $event_id=(int)$response->id_event[0];
            $OK = true;
        }
		
        //INSERT ORDERITEMS
        foreach ($orderItems as $OrderItemId => $OrderItem)
        {
            //GET MPN
			if ($OrderItem["SellerSKU"] == "KH-MGLH-SI0U") $OrderItem["SellerSKU"] = 47855;
			if ($OrderItem["SellerSKU"] == "91704/2") $OrderItem["SellerSKU"] = 91704;
			if ($OrderItem["SellerSKU"] == "2x-26706") $OrderItem["SellerSKU"] = 26706;
			if ($OrderItem["SellerSKU"] == "2x  26706") $OrderItem["SellerSKU"] = 26706;
			if ($OrderItem["SellerSKU"] == "20975/2") $OrderItem["SellerSKU"] = 20975;
            if ($OrderItem["SellerSKU"] == "8Z-4BQP-271N") $OrderItem["SellerSKU"] = 26863;
            if ($OrderItem["SellerSKU"] == "WW-NBXW-F9XJ") $OrderItem["SellerSKU"] = 47858;
			if ($OrderItem["SellerSKU"] == "DL-3INS-NUGS") $OrderItem["SellerSKU"] = 47855;
			if ($OrderItem["SellerSKU"] == "68718 und 63236") $OrderItem["SellerSKU"] = 68718;
            if ($OrderItem["SellerSKU"] == "26751+") $OrderItem["SellerSKU"] = 26751;
            if ($OrderItem["SellerSKU"] == "20986/2") {
				
                $OrderItem['QuantityOrdered'] *= 2;
                $OrderItem['ItemPriceAmount'] /= 2;
                $OrderItem["SellerSKU"] = "20986";
            }
		
            $MPN = $OrderItem["SellerSKU"];
            if (strpos($MPN, "-") !== false) {
                $MPN = substr($MPN, 0, strpos($MPN, "-"));
            }
			
            //GET SHOP_ITEM_ID
            $res_item = q("
                SELECT *
                FROM shop_items
                WHERE MPN = '" . $MPN . "'", $dbshop, __FILE__, __LINE__);
            if (mysqli_num_rows($res_item) == 0) {
                show_error(9877, 7, __FILE__, __LINE__, print_r($OrderItem, true));
                exit;
            }
		
            $item = mysqli_fetch_assoc($res_item);

            //NETTOPREIS
            if ($OrderItem['QuantityOrdered'] == 0) {
                $temQuantity = 1;
            } else {
                $temQuantity = $OrderItem['QuantityOrdered'];
            }
            $net = round(($OrderItem["ItemPriceAmount"] / $temQuantity) / $VAT_multiplier,2);

        //CHECK FOR KNOWN ITEM
        $shop_orders_items_results = q("
			SELECT * 
			FROM shop_orders_items 
			WHERE order_id = " . $order_id . " 
			AND item_id = " . $item["id_item"] . ";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($shop_orders_items_results) == 0) {
			
            $fieldlist = array();
            //BASISFELDER FÜR API-AUFRUF
            $fieldlist["API"] = "shop";
            $fieldlist["APIRequest"] = "OrderItemAdd";
            $fieldlist["mode"] = "ebay";

            //FIELDLIST FOR INSERT
            $fieldlist["order_id"] = $order_id;
            $fieldlist["foreign_transactionID"] = $OrderItem["OrderItemId"];
            $fieldlist["item_id"] = $item["id_item"];
            $fieldlist["amount"] = $OrderItem["QuantityOrdered"];
            $fieldlist["price"] = ($OrderItem["ItemPriceAmount"] / $temQuantity);
            $fieldlist["netto"] = $net;
            $fieldlist["Currency_Code"] = $OrderItem["ItemPriceCurrencyCode"];
            $fieldlist["exchange_rate_to_EUR"] = $currency["exchange_rate_to_EUR"];
			$response = soa2($fieldlist, __FILE__, __LINE__, "obj");

			$OK = true;
            if ((string)$response->Ack[0] != "Success") {
				$showInfo = 'API Shop->OrderItemAdd: (OID: ' . $order_id . ') ' . "\n";
				$showInfo.= (string)$response . "\n";
				show_error(9797,8,__FILE__, __LINE__, $showInfo);
                $OK = false;
            }

        } else {
			
             $shop_orders_items = mysqli_fetch_assoc($shop_orders_items_results);

            //UPDATE ITEM
            $fieldlist = array();
            //BASISFELDER FÜR API-AUFRUF
            $fieldlist["API"] = "shop";
            $fieldlist["APIRequest"] = "OrderItemUpdate";
            $fieldlist["mode"] = "ebay";

            //FIELDLIST FOR UPDATE
            $fieldlist["SELECTOR_id"] = $shop_orders_items["id"];
            $fieldlist["order_id"] = $order_id;
            $fieldlist["foreign_transactionID"] = $OrderItem["OrderItemId"];
            $fieldlist["item_id"] = $item["id_item"];
            $fieldlist["amount"] = $OrderItem["QuantityOrdered"];
            $fieldlist["price"] = ($OrderItem["ItemPriceAmount"] / $temQuantity);
            $fieldlist["netto"] = $net;
            $fieldlist["Currency_Code"] = $OrderItem["ItemPriceCurrencyCode"];
            $fieldlist["exchange_rate_to_EUR"] = $currency["exchange_rate_to_EUR"];
			$response = soa2($fieldlist, __FILE__, __LINE__, "obj");
			
			$OK = true;
            if ((string)$response->Ack[0]!="Success") {
				$showInfo = 'API Shop->OrderItemUpdate / ' . "\n";
				$showInfo.= (string)$response . "\n";				
				show_error(9797,8,__FILE__, __LINE__, $showInfo);
                $OK = false;
            }
        }
    }
	
	//PAYMENT UPDATE
	if( $OK )
	{
        //PAYMENTNOTIFICATIONHANDLER -> WRITE ORDER
		
        $fieldlist = array();
        //BASISFELDER FÜR API-AUFRUF
        $fieldlist["API"] = "payments";
        $fieldlist["APIRequest"] = "PaymentNotificationHandler";
		$shop_orders_results=q("SELECT * FROM shop_orders WHEREid_order=".$order_id.";", $dbshop, __FILE__, __LINE__);
		$shop_orders=mysqli_fetch_assoc($shop_orders_results);
		if( $shop_orders["Payments_TransactionID"]=="" ) $fieldlist["mode"]="OrderAdd";
		else $fieldlist["mode"]="OrderAdjustment";
		print_r($shop_orders);
		exit;

        $fieldlist["orderid"] = $order_id;
        $fieldlist["order_event_id"] = $event_id;
        $response = soa2($fieldlist, __FILE__, __LINE__, "obj");
		
        if ($response->Ack[0] != "Success") {
            show_error(9773, 7, __FILE__, __LINE__, print_r($response, true), false);
            $OK = false;
        }
    }
		
    //ACCOUNT "PAYMENT"
	if( $OK )
	{
        $orderTotalEUR = round($amazon_order["OrderTotalAmount"]/$currency["exchange_rate_to_EUR"],2);
		
        //PAYMENTNOTIFICATIONHANDLER -> DO PAYMENT
        
		$fieldlist = array();
        //BASISFELDER FÜR API-AUFRUF
        $fieldlist["API"] = "payments";
        $fieldlist["APIRequest"] = "PaymentNotificationSet_Manual";
        $fieldlist["mode"] = "BankTransfer";
        $fieldlist["orderid"] = $order_id;
        $fieldlist["order_event_id"] = $event_id;

        $fieldlist["payment_total"] = $orderTotalEUR; //EUR
        $fieldlist["accounting_date"] = strtotime($amazon_order["PurchaseDate"]);
        $response=soa2($fieldlist, __FILE__, __LINE__, "obj");

        if ($response->Ack[0]!="Success") {
            show_error(9773, 7, __FILE__, __LINE__, print_r($response, true), false);
            $OK = false;
        }
    }
		
    //CHECK IF AMAZONORDERTOTAL == OrderTotal (Collateral)
    if ($OK) {
        $fieldlist = array();
        //BASISFELDER FÜR API-AUFRUF
        $fieldlist["API"]="payments";
        $fieldlist["APIRequest"]="PaymentNotificationLastOrderDepositGet";
        $fieldlist["orderid"]=$order_id;
        $response=soa2($fieldlist, __FILE__, __LINE__, "obj");

        if ($response->Ack[0]!="Success") {
            show_error(9773, 7, __FILE__, __LINE__, print_r($response, true), false);
            $OK = false;
        }

        $orderDeposit = (float)$response->orderdeposit[0];

        if ($orderDeposit < 0) {
            q("
                UPDATE shop_orders
                SET status_id = 1,
                Payments_TransactionState = 'Pending',
                order_deposit = " . $orderDeposit . " 
				WHERE id_order = " . $order_id, $dbshop, __FILE__, __LINE__);
        }
    }
}

//  update importShopStatus
	q("UPDATE amazon_orders
	SET importShopStatus = 2 
	WHERE AmazonOrderId = '" . $post["AmazonOrderId"] . "'", $dbshop, __FILE__, __LINE__ );
