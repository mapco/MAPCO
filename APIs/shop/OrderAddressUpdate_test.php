<?php

/***
 *	@author: nputzing@mapco.de / edit by rlange@mapco.de
 *	address update for order and bill address
 *
 *	@params
 *	OrderID, customer_id (user_id), addresstype
 *
 *	$_POST
 *	country_code, country_id
*******************************************************************************/

//CHECK FOR REQUIRED POST-VARIABLES
$required = array(
	"OrderID" =>"numericNN",
	"customer_id" =>"numericNN",
	"addresstype" => "text"
);
check_man_params($required);

//	keep post submit
$post = $_POST;

	//	Get Shop Orders
	$data = array();
	$data['from'] = 'shop_orders_1396002117';
	$data['select'] = '*';
	$addWhere = "id_order = " . $post["OrderID"];
	$shopOrders = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
	if (count($shopOrders) == 0) {
		show_error(9762, 7, __FILE__, __LINE__, "OrderID:" . $post["OrderID"]);
		exit;
	}
	$order[0] = $shopOrders;

	if (!isset($post["country_code"]) && $post["country_code"] == ""
        && !isset($post["country_id"]) && ($post["country_id"] == 0 || $post["country_id"] == "")) {
		show_error(9763, 7, __FILE__, __LINE__);
		exit;
	}

	// GET COUNTY & CODE
	if (isset($post["country_code"]) && $post["country_code"] != "") {

		$data = array();
		$data['from'] = 'shop_countries';
		$data['select'] = '*';
		$addWhere = "country_code = " . $post["country_code"];
		$shopCountrys = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
		if (count($shopCountrys) == 0) {
			show_error(9764, 7, __FILE__, __LINE__, "CountryCode: " . $post["country_code"]);
			exit;
		}
	}

	if (isset($post["country_id"]) && $post["country_id"] != "" && $post["country_id"] != 0) {

		$data = array();
		$data['from'] = 'shop_countries';
		$data['select'] = '*';
		$addWhere = "id_country = " . $post["country_id"];
		$shopCountrys = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
		if (count($shopCountrys) == 0) {
			show_error(9765, 7, __FILE__, __LINE__, "CountryCode: " . $post["country_code"]);
			exit;
		}
	}
	$country = $shopCountrys;

	// Get shop shops by order shop id
	$data = array();
	$data['from'] = 'shop_shops';
	$data['select'] = '*';
	$addWhere = "id_shop = " . $order[0]["shop_id"];
	$shop = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
	if (count($shop) == 0) {
		show_error(9757, 7, __FILE__, __LINE__, "Shop_ID: " . $order[0]["shop_id"]);
		exit;
	}

	// Check addresstype
	if ($post["addresstype"] != "bill" && $post["addresstype"] != "ship" && $post["addresstype"] != "both") {
		//show_error(9766, 7, __FILE__, __LINE__, "AddressType: " . $post["addresstype"]);
		//exit;
	}

	//	CHECK IF address is known in shop_bill_adr
	//	GET ALL ADDRESSES FROM CMS_USER
	switch ($post["addresstype"])
	{
		case "bill": $active = " AND active = 1"; break;
		case "ship": $active = " AND active_ship_adr = 1"; break;
		case "both": $active = " AND active = 1 AND active_ship_adr = 1"; break;
		default: $active = "";
	}

	$known_addresses = array();
	if (isset($post['adrId']) && $post['adrId'] != null) {
		$data = array();
		$data['from'] = 'shop_bill_adr';
		$data['select'] = '*';
		$addWhere = "
			adr_id = " . $post['adrId'] . $active;
		$shopBillAddress = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
		$known_addresses[] = $shopBillAddress["adr_id"];
	} else {

		$data = array();
		$data['from'] = 'shop_bill_adr';
		$data['select'] = '*';
		$addWhere = "
			user_id = " . $post["customer_id"] . $active;
		$shopBillAddress = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, 0, 'shop',  __FILE__, __LINE__);
		foreach ($shopBillAddress as $row_check)
		{
			if ($row_check["company"] == $post["company"]
				&& $row_check["firstname"] == $post["firstname"]
				&& $row_check["lastname"] == $post["lastname"]
				&& $row_check["street"] == $post["street"]
				&& $row_check["number"] == $post["number"]
				&& $row_check["additional"] == $post["additional"]
				&& $row_check["zip"] == $post["zip"]
				&& $row_check["city"] == $post["city"]
				&& $row_check["country_id"] == $country["id_country"]) {
	
				$known_addresses[] = $row_check["adr_id"];
			}
		}
	}

	//	if address is known -> do nothing
	//	else insert new address
	if (sizeof($known_addresses) > 0) {
		$adrID = $known_addresses[0];	

		$data = array();
		//	DATA FOR ORDEREVENT
		$data["adr_id"] = $adrID;
		//	SAVE ORDEREVENT
		//$id_event = save_order_event(9, $order[0]["id_order"], $data);

	} else {

		//	insert new address by user id (customer id)
		$field = array(
			'table' => 'shop_bill_adr_save',
			'lastInsertId' => true
		);

		$data = array();
		$data['user_id'] = $post["customer_id"];
		$data['shop_id'] = $order[0]["shop_id"];
		$data['foreign_address_id'] = '';
		$data['company'] = $post["company"];
		$data['firstname'] = $post["firstname"];
		$data['lastname'] = $post["lastname"];
		$data['street'] = $post["street"];
		$data['number'] = $post["number"];
		$data['additional'] = $post["additional"];
		$data['zip'] = $post["zip"];
		$data['city'] = $post["city"];
		$data['country'] = $country["country"];
		$data['country_id'] = $country["id_country"];
		$data['standard'] = $post['standard'];
		$data['standard_ship_adr'] = $post['standard_ship_adr'];
		$lastInsertId = SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
		$adrID = $lastInsertId;

		//	DATA FOR ORDEREVENT
		$data["adr_id"] = $lastInsertId;

		//	SAVE ORDEREVENT
		if ($post["addresstype"] == "bill" || $post["addresstype"] == "both") {
 			//$id_event = save_order_event(22, $order[0]["id_order"], $data);
		}

		if ($post["addresstype"] == "ship" || $post["addresstype"] == "both") {
 			//$id_event = save_order_event(9, $order[0]["id_order"], $data);
		}
	}

/*
 *------------------------------------------------- SHOP ORDERS ------------------------------------------
 */	
 
	if ($post["addresstype"] == "bill" || $post["addresstype"] == "both")
	{
		$data = array();
		$data['customer_id'] = $post["customer_id"];
		$data['usermail'] = $post["usermail"];
		$data['userphone'] = $post["userphone"];		
		$data['bill_company'] = $post["company"];
		$data['bill_firstname'] = $post["firstname"];
		$data['bill_lastname'] = $post["lastname"];
		$data['bill_street'] = $post["street"];
		$data['bill_number'] = $post["number"];
		$data['bill_additional'] = $post["additional"];
		$data['bill_zip'] = $post["zip"];
		$data['bill_city'] = $post["city"];
		$data['bill_country'] = $country["country"];
		$data['bill_country_code'] = $country["country_code"];
		$data['bill_adr_id'] = $adrID;
		$data['bill_address_manual_update'] = 1;		
		if ($order[0]["combined_with"] > 0) {
			$addWhere = "combined_with = " . $order[0]["combined_with"];
		} else {
			$addWhere = "id_order = ". $post["OrderID"];
		}
		SQLUpdate('shop_orders_1396002117', $data, $addWhere, 'shop', __FILE__, __LINE__);
	}

	if ($post["addresstype"] == "ship" || $post["addresstype"] == "both")
	{
		$data = array();
		$data['customer_id'] = $post["customer_id"];
		$data['usermail'] = $post["usermail"];
		$data['userphone'] = $post["userphone"];		
		$data['ship_company'] = $post["company"];
		$data['ship_firstname'] = $post["firstname"];
		$data['ship_lastname'] = $post["lastname"];
		$data['ship_street'] = $post["street"];
		$data['ship_number'] = $post["number"];
		$data['ship_additional'] = $post["additional"];
		$data['ship_zip'] = $post["zip"];
		$data['ship_city'] = $post["city"];
		$data['ship_country'] = $country["country"];
		$data['ship_country_code'] = $country["country_code"];
		$data['ship_adr_id'] = $adrID;
		$data['bill_address_manual_update'] = 1;		
		if ($order[0]["combined_with"] > 0) {
			$addWhere = "combined_with = " . $order[0]["combined_with"];
		} else {
			$addWhere = "id_order = " . $post["OrderID"];
		}
		SQLUpdate('shop_orders_1396002117', $data, $addWhere, 'shop', __FILE__, __LINE__);
	}

/*
 *------------------------------------------------- functions ------------------------------------------
 */


/**
 * Returns a last insert id
 *
 * @param $eventtype_id
 * @param $order_id
 * @param $data
 * @return bool|int|mysqli_result|string
 */
function save_order_event($eventtype_id, $order_id, $data)
{
	//	CREATE XML FROM DATA
	$xml = '<data>';
	foreach ($data as $key => $val)
	{
		$xml.= '    <'.$key.'>';
		if (!is_numeric($val)) $xml.= ' <![CDATA[' . $val . ']]>'; else $xml.= $val;
		$xml.= '    </'.$key.'>';
	}
	$xml.= '</data>';

	$field = array(
		'table' => 'shop_orders_events'
	);
	$data = array();
	$data['order_id'] = $order_id;
	$data['eventtype_id'] = $eventtype_id;
	$data['data'] = $xml;
	$data['firstmod'] = time();
	$data['firstmod_user'] = $_SESSION["id_user"];
	$data['lastInsertId'] = true;
	return SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
}
