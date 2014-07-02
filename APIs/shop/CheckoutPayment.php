<?php

/***
 *	@author: rlange@mapco.de
 *	Shop Card Services for Checkout Payment Methods
 *
*******************************************************************************/

include("../functions/mapco_gewerblich.php");

//	keep post submit
$post = $_POST;

	// is set $_SESSION['checkout_order_id'] and $_SESSION['ckeckout_guest']
	$checkout_order_id = 0;
	$checkout_guest = 0;
	if (isset($_SESSION['checkout_order_id']) && isset($_SESSION['ckeckout_guest'])) {
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
	 * show shop payments methods by shopId and countryId
	 */
	if ($post['action'] == 'ShopPayment') {

		$shopPayments = findsShopPaymentQueryBuilder($userData);
		if (count($shopPayments) > 0) {
			foreach($shopPayments as $shopPayment)
			{
				$xmlShopPayment.= xmlShopPayment($shopPayment, 'shopPayment');
			}
		}
		$xml = $xmlShopPayment;
		echo $xml;
	}

	/**
	 *	save paymentId into a session var
	 */
	if ($post['action'] == 'SetShopPaymentMethod') {
		setSessionPaymentId($post);
	}

/**
 *	--------------------------------------------------- function -----------------------------------------------------
 *
 */

/**
 * @param $data
 * @return array|null
 */
function findsShopPaymentQueryBuilder($data)
{
	$field['from'] = "shop_payment
		LEFT JOIN shop_payment_types ON id_paymenttype = paymenttype_id";
	$field['select'] =  "*";
	$field['orderBy'] = 'ordering';
	$addWhere = "
		shop_id = '" . $data['shopId'] . "'
		AND country_id = '" . $data['billCountryId'] . "'";
	if ($data['commercial'] == false) {
		$addWhere.= " AND NOT payment ='Rechnung'";
	}
	return SQLSelect($field['from'], $field['select'], $addWhere, $field['orderBy'], 0, 0, 'shop',  __FILE__, __LINE__);
}

/**
 * @param $post
 */
function setSessionPaymentId($post)
{
	if (!isset($_SESSION['paymentId'])) {
		$paymentID = $post['paymentId'];
	} else {
		if ($_SESSION['paymentId'] != $post['paymentId']) {
			$paymentID = $post['paymentId'];
		} else {
			$paymentID = $_SESSION['paymentId'];
		}
	}
	$_SESSION['paymentId'] = $paymentID;
}

/**
 * @param $value
 * @param $envelope
 * @return string
 */
function xmlShopPayment($value, $envelope)
{
	$xml = '	<id_payment>' . $value["id_payment"] . '</id_payment>' . "\n";
	$xml.= '	<payment>' . $value["payment"] . '</payment>' . "\n";
	$xml.= '	<payment_memo>' . $value["payment_memo"] . '</payment_memo>' . "\n";
	$xml.= '	<shop_id>' . $value["shop_id"] . '</shop_id>' . "\n";
	$xml.= '	<paymenttype_id>' . $value['paymenttype_id'] . '</paymenttype_id>' . "\n";
	$xml.= '	<country_id>' . $value["country_id"] . '</country_id>' . "\n";
	return '<' . $envelope . '>' . "\n" . $xml . '</' . $envelope . '>' . "\n";
}
