<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for shipping update
 *	- update shipping status for amazon
 *
 * @params
 * - API Version: 2009-01-01
 * - submit method: POST
 * - action: SubmitFeed
 * - MessageType: OrderFulfillment
 * - FeedType: _POST_ORDER_FULFILLMENT_DATA_
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
require_once("../functions/cms_core.php");
	
//	keep post submit
$post = $_POST;
	
	//	update a shop orders shipping status
	$data = array();
	$data['from'] = 'shop_orders';
	$data['select'] = '*';
	$data['where'] = "
		shop_id = " . $post['shop_id'] . " 
		AND status_id = 3
	";
	$data['orderBy'] = 'lastmod DESC';
	$shopOrders = SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, $post['limit'], 'shop', __FILE__, __LINE__);
	if (count($shopOrders) > 0 ) 
	{
		foreach ($shopOrders as $shopOrder)
		{
			if (!empty($shopOrder['foreign_OrderID'])) 
			{
				$data = array();
				$data['shippingStatusId'] = $shopOrder['status_id'];
				$data['shippingStatusDate'] = $shopOrder['status_date'];
				$data['importShopStatus'] = 3;
				$data['ShippingNumber'] = $shopOrder['shipping_number']; 
				$addWhere = "
					AmazonOrderId = '" . $shopOrder['foreign_OrderID'];
				SQLUpdate('amazon_orders', $data, $addWhere, 'shop', __FILE__, __LINE__);			
				$xmlResult = '<submitResult>Update Amazon Orders : ' . $shopOrder['foreign_OrderID'] . '</submitResult>' . "\n";
			} else {
				$xmlResult ='<submitResult>Keine Foreign OrderID fuer Amazon Orders Update vorhanden</submitResult>' . "\n";
			}
		}
	}
	$xml = "\n" . "<AmazonOrdersShippingUpdate>" . "\n";
	$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
	$xml.= $xmlResult;
	$xml.= '</AmazonOrdersShippingUpdate>'. "\n";
	echo $xml;	
