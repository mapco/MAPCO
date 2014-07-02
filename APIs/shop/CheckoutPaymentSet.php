<?php

	// is set $_SESSION['checkout_order_id']
	$checkout_order_id = 0;
	if (isset($_SESSION['checkout_order_id']))
	{
		$checkout_order_id = $_SESSION['checkout_order_id'];
	}
	
	//	keep important session vars for the user
    $userData = array(
        'userId' => $_SESSION['id_user'],
		'shopId' => '4',
		'orderId' => '1819478',
		'shipCountryId' => $_SESSION['ship_country_id'],
		'billCountryId' => $_SESSION['bill_country_id']
    );
	
	//	get shop payment types
	$field = array();
	$field['from'] = "shop_payment";
	$field['select'] =  "*";
	$addWhere = "
		shop_id = '" . $userData['shopId'] . "'
		AND country_id = '" . $userData['billCountryId'] . "'
	";
	$shopPayments = SQLSelect($field['from'], $field['select'], 0, 0, 0, 0, 'shop',  __FILE__, __LINE__);	
	
	
	
	//	get shop payment types
	$field = array();
	$field['from'] = "shop_payment_types";
	$field['select'] =  "*";
	$field['orderBy'] =  "title ASC";
	$shopPaymentTypes = SQLSelect($field['from'], $field['select'], 0, $field['orderBy'], 0, 0, 'shop',  __FILE__, __LINE__);	
	
	
	
	//	get shop order by order id
	$field = array();
	$field['from'] = 'shop_orders_1396002117';
	$field['select'] = '*';
	$addWhere = "id_order = " . $userData["orderId"];
	$shopOrder = SQLSelect($field['from'], $field['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);	