<?php

/***
 *	@author: rlange@mapco.de
 *	Address Bill Service for Shop
 *	- get the address bill
 *
*******************************************************************************/

include("../functions/cms_core.php");

//	keep post submit
$post = $_POST;

	/**
	 *	address bill List
	 *
	 */
	if ($post['action'] == 'list')
	{
		$xml = getAddressBillBillingAddress($post);
		$xml.= getAddressBillShippingAddress($post);
		$xml.= getAddressBillOtherAddresses($post);
		echo $xml;
	}

	/**
	 *	set standard billing address
	 *
	 */
	if ($post['action'] == 'set-standard-billing')
    {
		$data = array();
		$data['standard'] = 0;
		$addWhere = "
			user_id = " . $post["user_id"];
		SQLUpdate('shop_bill_adr_save', $data, $addWhere, 'shop', __FILE__, __LINE__);

		if ($post['bill_standard'] == 1) {
			$data = array();
			$data['standard'] = $post['bill_standard'];
			$addWhere = "adr_id = " . $post["adrId"];
			SQLUpdate('shop_bill_adr_save', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}
	}

	/**
	 *	set standard shipping address
	 *
	 */
	if ($post['action'] == 'set-standard-shipping')
    {
		$data = array();
		$data['standard_ship_adr'] = 0;
		$addWhere = "
			user_id = " . $post["user_id"];
		SQLUpdate('shop_bill_adr_save', $data, $addWhere, 'shop', __FILE__, __LINE__);

		$data = array();
		$data['standard_ship_adr'] = 1;
		$addWhere = "adr_id = " . $post["adrId"];
		SQLUpdate('shop_bill_adr_save', $data, $addWhere, 'shop', __FILE__, __LINE__);
	}

	/**
	 *	address bill remove
	 *
	 */
	if ($post['action'] == 'remove')
    {
		$data = array();
		$data['active'] = 0;
		$data['standard'] = 0;
		$data['standard_ship_adr'] = 0;
		$data['active_ship_adr'] = 0;
		$addWhere = "adr_id = " . $post["adrId"];
		SQLUpdate('shop_bill_adr_save', $data, $addWhere, 'shop', __FILE__, __LINE__);
	}

/**
 *	--------------------------------------------------- function -----------------------------------------------------
 *
 */

    /**
     * @param $post
     * @return array|null
     */
    function getShopOrderByOrderId($post)
	{
		//	get shop order by order id
		$data = array();
		$data['from'] = 'shop_orders_1396002117';
		$data['select'] = '*';
		$addWhere = "id_order = " . $post["orderId"];
		$shopOrder = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
		return $shopOrder;
	}

    /**
     * @param $post
     * @return string
     */
    function getAddressBillBillingAddress($post)
	{
		$shopOrder = getShopOrderByOrderId($post);
		$data = array();
		$data['from'] = 'shop_bill_adr_save';
		$data['select'] = '*';
		$data['where'] = "
			adr_id = '" . $shopOrder['bill_adr_id'] . "'
			AND user_id = '" . $post['user_id'] . "'
			AND active = 1
		";
		$users = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
		$xml = "";
		if (count($users) > 0) {
			foreach($users as $user)
			{
				$xml.= xmlShopAddressBill($user, 'widgetFieldAddressBillBilling');
			}
		} else {
			$xml = '<countBillingAddress>noBillingData</countBillingAddress>';
		}
		return $xml;
	}

    /**
     * @param $post
     * @return string
     */
    function getAddressBillShippingAddress($post)
	{
		$shopOrder = getShopOrderByOrderId($post);
		$data = array();
		$data['from'] = 'shop_bill_adr_save';
		$data['select'] = '*';
		$data['where'] = "
			adr_id = '" . $shopOrder['ship_adr_id'] . "'
			AND user_id = '" . $post['user_id'] . "'
			AND active_ship_adr = 1
		";
		$users = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
		$xml = "";
		if (count($users) > 0) {
			foreach($users as $user)
			{
				$xml.= xmlShopAddressBill($user, 'widgetFieldAddressBillShipping');
			}
		} else {
			$xml = '<countShippingAddress>noShippingData</countShippingAddress>';
		}
		return $xml;
	}

    /**
     * @param $post
     * @return string
     */
    function getAddressBillOtherAddresses($post)
	{
		$shopOrder = getShopOrderByOrderId($post);
		$data = array();
		$data['from'] = 'shop_bill_adr_save';
		$data['select'] = '*';
		$data['where'] = "
			adr_id != '" . $shopOrder['bill_adr_id'] . "'
			AND adr_id != '" . $shopOrder['ship_adr_id'] . "'
			AND user_id = '" . $post['user_id'] . "'
			AND active_ship_adr = 1
			AND active = 1
		";
		$users = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
		$xml = "";
		if (count($users) > 0) {
			foreach($users as $user)
			{
				$xml.= xmlShopAddressBill($user, 'widgetFieldAddressBill');
			}
		} else {
			$xml = '<countOtherAddress>noOtherData</countOtherAddress>';
		}
		return $xml;
	}

    /**
     * @param $user
     * @param $envelope
     * @return string
     */
    function xmlShopAddressBill($user, $envelope)
	{
		$gender = "Herr";
		if ($user["gender"] == 1) {
			$gender = "Frau";
		}

		$xml = '	<adr_id>' . $user["adr_id"] . '</adr_id>' . "\n";
		$xml.= '	<user_id>' . $user["user_id"] . '</user_id>' . "\n";
		$xml.= '	<shop_id>' . $user["shop_id"] . '</shop_id>' . "\n";
		$xml.= '	<company><![CDATA[' . $user["company"] . ']]></company>' . "\n";
		$xml.= '	<gender>' . $gender . '</gender>' . "\n";
		$xml.= '	<title><![CDATA[' . $user["title"] . ']]></title>' . "\n";
		$xml.= '	<firstname><![CDATA[' . $user["firstname"] . ']]></firstname>' . "\n";
		$xml.= '	<lastname><![CDATA[' . $user["lastname"] . ']]></lastname>' . "\n";
		$xml.= '	<street><![CDATA[' . $user["street"] . ']]></street>' . "\n";
		$xml.= '	<number>' . $user["number"] . '</number>' . "\n";
		$xml.= '	<zip>' . $user["zip"] . '</zip>' . "\n";
		$xml.= '	<city><![CDATA[' . $user["city"] . ']]></city>' . "\n";
		$xml.= '	<country><![CDATA[' . $user["country"] . ']]></country>' . "\n";
		$xml.= '	<standard>' . $user["standard"] . '</standard>' . "\n";
		$xml.= '	<standard_ship_adr>' . $user["standard_ship_adr"] . '</standard_ship_adr>' . "\n";
		$xml.= '	<active>' . $user["active"] . '</active>' . "\n";
		$xml.= '	<active_ship_adr>' . $user["active_ship_adr"] . '</active_ship_adr>' . "\n";
		return '<' . $envelope . '>' . "\n" . $xml . '</' . $envelope . '>' . "\n";
	}
