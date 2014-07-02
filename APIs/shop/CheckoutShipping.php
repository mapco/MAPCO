<?php
/***
 *	@author: rlange@mapco.de
 *	Shop Card Services for Checkout Shipping Methods
 *
*******************************************************************************/

include("../functions/mapco_gewerblich.php");

//	keep post submit
$post = $_POST;

	// is set $_SESSION['checkout_order_id'] and $_SESSION['ckeckout_guest']
	$checkout_order_id = 0;
	$checkout_guest = 0;
	if (isset($_SESSION['checkout_order_id']) && isset($_SESSION['ckeckout_guest']))
	{
		$checkout_order_id	= $_SESSION['checkout_order_id'];
		$checkout_guest		= $_SESSION['ckeckout_guest'];
	} else {
		// go far far away
	}

	//	keep important session vars for the user
    $userData = array(
        'userId' => $_SESSION['id_user'],
		'shopId' => $_SESSION['id_shop'],
		'orderId' => '1819478',
		'paymentId' => $_SESSION['paymentId'],
		'shipCountryId' => $_SESSION['ship_country_id'],
		'billCountryId' => $_SESSION['bill_country_id'],
		'checkoutGuestOrder' => $checkout_guest,
		'checkoutOrderId' => $checkout_order_id
    );
	
	//	check if user commercial
	if (true === gewerblich($userData['userId'])) {
		$userData = array_merge($userData, array('commercial' => true));
	}	
	
	//-----------------------------------------------------------------------------------------------------------------------

	/**
	 *	show shipping methods by payment method
	 */
	if ($post['action'] == 'ShopShipping')
	{
		//	get select shop shipping
		$shopShippings =findsShopPaymentQueryBuilder($userData);
		$countShippings = count($shopShippings);
		if ($countShippings > 0) {
			foreach($shopShippings as $shopShipping)
			{
				if ($countShippings == 1) {
					setSessionShippingId($shopShipping['id_shipping']);
					
					$shopShipping['jump'] = true;
					$xmlShopShipping.= xmlShopShipping($shopShipping, 'shopShipping');
				} else {
					$xmlShopShipping.= xmlShopShipping($shopShipping, 'shopShipping');
				}
			}
		}
		$xml.= $xmlShopShipping;
		echo $xml;
	}

	/**
	 *	save shippingId into a session var
	 *	update shop order with payment and shipping data
	 */
	if ($post['action'] == 'SetShopShippingMethod') {
		setSessionShippingId($post);
		updateShopOrderByPaymentIdAndShippingId($paymentId, $shippingId, $userData['orderId']);
	}

/**
 *	--------------------------------------------------- function -----------------------------------------------------
 *
 */


/**
 * Update Shop Order
 *
 * @param $paymentId
 * @param $shippingId
 * @param $orderId
 */
function updateShopOrderByPaymentIdAndShippingId($paymentId, $shippingId, $orderId)
{
	$order = findShopOrderByOrderId($orderId);
	$payment = findShopPaymentAndPaymentTypeByPaymentId($paymentId);
	$shipping = findShippingAndShippingTypeByShippingId($shippingId);

	$data = array();
	$data['payments_type_id'] = $payment['id_paymenttype'];
	$data['shipping_type_id'] = $shipping['id_shippingtype'];
	$data['shipping_costs'] = ShopShippingPrice($shipping, $order);
	$data['shipping_details'] = $payment['payment'] . ', ' . $shipping['shipping'];
	$data['shipping_details_memo'] = $shipping['shipping_memo'];
	$addWhere = "id_order = " . $orderId;
	SQLUpdate('shop_orders_1396002117', $data, $addWhere, 'shop', __FILE__, __LINE__);
}

/**
 * @param $orderId
 * @return array|null
 */
function findShopOrderByOrderId($orderId)
{
	$field['from'] = "shop_orders_1396002117";
	$field['select'] =  "*";
	$addWhere = "
		id_order = '" . $orderId . "'";
	return SQLSelect($field['from'], $field['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
}

/**
 * @param $paymentId
 * @return array|null
 */
function findShopPaymentAndPaymentTypeByPaymentId($paymentId)
{
	$field['from'] = "shop_payment
		LEFT JOIN shop_payment_types ON id_paymenttype = paymenttype_id";
	$field['select'] =  "*";
	$addWhere = "
		id_payment = '" . $paymentId . "'";
	return SQLSelect($field['from'], $field['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
}

/**
 * @param $shippingId
 * @return array|null
 */
function findShippingAndShippingTypeByShippingId($shippingId)
{
	$field['from'] = "shop_shipping
		LEFT JOIN shop_shipping_types ON id_shipping = shippingtype_id";
	$field['select'] =  "*";
	$addWhere = "
		id_shipping = '" . $shippingId . "'";
	return SQLSelect($field['from'], $field['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
}

/**
 * @param $shipping
 * @param $order
 * @return float
 */
function ShopShippingPrice($shipping, $order, $data)
{
	if ($data['commercial'] == false) {
    	$UST = $order['VAT'];
		$shippingPrice = ((100 + $UST) / 100) * $shipping['price'];
	} else {
		//	shipping price for commercials
		$shippingPrice = $shipping['price'];
	}
	return $shippingPrice;
}

/**
 * @param $data
 * @return array|null
 */
function findsShopPaymentQueryBuilder($data)
{
	$field['from'] = "shop_shipping
		LEFT JOIN shop_shipping_types ON id_shippingtype = shippingtype_id
	";
	$field['select'] =  "*";
	$field['orderBy'] = 'ordering';
	$addWhere = "
		payment_id = '" . $data["paymentId"] . "'
	";
	return SQLSelect($field['from'], $field['select'], $addWhere, $field['orderBy'], 0, 0, 'shop',  __FILE__, __LINE__);
}

/**
 * @param $post
 */
function setSessionShippingId($post)
{
	if (!isset( $_SESSION['shippingId'] )) {
		$_SESSION['shippingId'] = $post['shippingId'];
	}

	if (!isset($_SESSION['shippingId'])) {
		$shippingID = $post['shippingId'];
	} else {
		if ($_SESSION['shippingId'] != $post['shippingId']) {
			$shippingID = $post['shippingId'];
		} else {
			$shippingID = $_SESSION['shippingId'];
		}
	}
	$_SESSION['paymentId'] = $shippingID;
}

/**
 * @param $value
 * @param $envelope
 * @return string
 */
function xmlShopShipping($value, $envelope)
{
	if ($value['jump'] == true) {
		$xml = '	<Jump>Success</Jump>' . "\n";
	} else {
		$xml = '	<id_shipping>' . $value["id_shipping"] . '</id_shipping>' . "\n";
		$xml.= '	<shipping>' . $value["shipping"] . '</shipping>' . "\n";
		$xml.= '	<shipping_memo>' . $value["shipping_memo"] . '</shipping_memo>' . "\n";
		$xml.= '	<shippingtype_id>' . $value["shippingtype_id"] . '</shippingtype_id>' . "\n";
		$xml.= '	<price>' . $value['price'] . '</price>' . "\n";
		$xml.= '	<payment_id>' . $value['payment_id'] . '</payment_id>' . "\n";
	}
	return '<' . $envelope . '>' . "\n" . $xml . '</' . $envelope . '>' . "\n";
}
