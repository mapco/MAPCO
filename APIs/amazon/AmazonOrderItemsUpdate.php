<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for order items update
 *	- get the latest order items by order id
 *
 *	@params
 *	- 
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

//keep post submit
$post = $_POST;
	
	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);	
	
	//	get the amazon account data
	$amazonAccount = getAmazonAccountById($amazonAccountsSites['account_id']);
	
   /************************************************************************
    * REQUIRED
    *
    * Access Key ID and Secret Acess Key ID, obtained from:
    * http://mws.amazon.com
    ***********************************************************************/
    $AWS_ACCESS_KEY_ID = $amazonAccount["AWSAccessKeyId"];
    $AWS_SECRET_ACCESS_KEY = $amazonAccount["SecretKey"];

   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain a User-Agent header. The application
    * name and version defined below are used in creating this value.
    ***********************************************************************/
    $APPLICATION_NAME = '<Your Application Name>';
    $APPLICATION_VERSION = '2013-09-01';

   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain the seller's merchant ID, host and
    * marketplace ID.
    ***********************************************************************/
    $MERCHANT_ID = $amazonAccount["MerchantId"];
    $MARKETPLACE_ID = $amazonAccountsSites["MarketplaceID"];
	$MARKETPLACE_HOST = $amazonAccountsSites["host"];
	
   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain the type and the method
    ***********************************************************************/
    $MWS_TYPE = 'Orders/2013-09-01';
    $MWS_METHOD = 'GET';		

	if (isset($post['AmazonOrderId']) && $post['AmazonOrderId'] > 0) 
	{
		//" . $post['AmazonOrderId'] . "
		$amazonOrdersQuery = "
			SELECT *
			FROM amazon_orders
			WHERE AmazonOrderId = '" . $post['AmazonOrderId'] . "'
			AND MarketplaceId = '" . $MARKETPLACE_ID . "'
			AND importShopStatus < 3;";
	} else {
		$amazonOrdersQuery = "
			SELECT *
			FROM amazon_orders
			WHERE MarketplaceId = '" . $MARKETPLACE_ID . "'
			AND importShopStatus < 3
			ORDER BY firstmod DESC;";
	}
	$amazonOrdersResult = q($amazonOrdersQuery, $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($amazonOrdersResult) > 0) 
	{
		while($order = mysqli_fetch_assoc($amazonOrdersResult))
		{
			echo '<Order>'."\n";
			echo '	<AmazonOrderId>' . $order["AmazonOrderId"] . '</AmazonOrderId>' . "\n";
			
			// get the amazon order items by amazon order id
			$url = "AWSAccessKeyId=" . $AWS_ACCESS_KEY_ID . "&Action=" . $post['action'];
			$url.= "&SellerId=" . $MERCHANT_ID . '&AmazonOrderId=' . $order['AmazonOrderId'];
			$url.= "&SignatureMethod=HmacSHA256&SignatureVersion=2&Timestamp=" . gmdate("Y-m-d\TH:i:s\Z") . "&Version=" . $APPLICATION_VERSION;	
			
			$post_data = array();
			$post_data['url'] = $url;
			$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
			$post_data['type'] = $MWS_TYPE;
			$post_data['method'] = $MWS_METHOD;
			$results = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
			$xml = new SimpleXMLElement($results);
			$orderItems = json_decode(json_encode($xml), TRUE);

			// insert or update the amazon order item
			if (isset($orderItems['ListOrderItemsResult']['OrderItems']['OrderItem'])) 
			{
				$counter = 0;
				$tempOrderItems = array();
				if (!isset($orderItems['ListOrderItemsResult']['OrderItems']['OrderItem'][0])) 
				{
					$tempOrderItems = array($orderItems['ListOrderItemsResult']['OrderItems']['OrderItem']);
				} else {
					$tempOrderItems = $orderItems['ListOrderItemsResult']['OrderItems']['OrderItem'];
				}

				foreach ($tempOrderItems as $item)
				{	
					//check the order items into the amazon product for GART = 82
					$MPN = $item["SellerSKU"];
					
					if (strpos($MPN, "-") !== false) 
					{
						$MPN = substr($MPN, 0, strpos($MPN, "-"));
					}

					$data = array();
					$data['from'] = 'shop_items';
					$data['select'] = 'GART, MPN';
					$data['where'] = "
						MPN = '" . $MPN . "'";
					$shopItem = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
					if (count($shopItem) > 0 ) 
					{
						if ($shopItem['GART'] == 82 && strpos($shopItem["MPN"], '/2') === false)	
						{
							$QuantityOrdered = $item['QuantityOrdered'] * 2;
						} else {
							$QuantityOrdered = $item['QuantityOrdered'];
						}
					}

					//checkfor existing item
					$data = array();
					$data['from'] = 'amazon_order_items';
					$data['select'] = '*';
					$data['where'] = "
						OrderItemId = '" . $item['OrderItemId'] . "'";
					$resultItems = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
					if (count($resultItems) > 0 )
					{
						//check for new data
						//$importShopStatus = 1;
						
						if ($item['QuantityOrdered'] != $QuantityOrdered) 
						{
							$importShopStatus = 0;
						}

						if ($importShopStatus == 0)
						{
							$data = array();
							$data["QuantityOrdered"] = $QuantityOrdered;
							$data["QuantityShipped"] = $item['QuantityShipped'];
							$data["ItemPriceCurrencyCode"] = $item['ItemPrice']['CurrencyCode'];
							$data["ItemPriceAmount"] = $item['ItemPrice']['Amount'];
							$data["ShippingPriceCurrencyCode"] = $item['ShippingPrice']['CurrencyCode'];
							$data["ShippingPriceAmount"] = $item['ShippingPrice']['Amount'];
							$data["ScheduledDeliveryEndDate"] = $item['ScheduledDeliveryEndDate'];
							$data["ScheduledDeliveryStartDate"] = $item['ScheduledDeliveryStartDate'];
							$addWhere = "
								id_order_item = " . $resultItems["id_order_item"];
							SQLUpdate('amazon_order_items', $data, $addWhere, 'shop', __FILE__, __LINE__);
							
							$data = array();
							$data["importShopStatus"] = 0;
							$addWhere = "
								importShopStatus = 1 
								AND AmazonOrderId = '" . $resultItems["AmazonOrderId"] . "'";							
							SQLUpdate('amazon_orders', $data, $addWhere, 'shop', __FILE__, __LINE__);
							echo '	<ItemUpdated>Update Orders ID ' . $order['id_orders'] . '</ItemUpdated>'. "\n";				
						
						} else {
							echo '	<ItemUpdateSkipped>Update Orders ID ' . $order['id_orders'] . '</ItemUpdateSkipped>'. "\n";
						}
					} else {
	
						// insert new amazon orders item by AmazonOrderId
						$queryInsertAmazonOrdersItem = "
						INSERT INTO amazon_order_items
							(ASIN, OrderItemId, SellerSKU, Title, QuantityOrdered, QuantityShipped, ItemPriceCurrencyCode, ItemPriceAmount, ShippingPriceCurrencyCode,
							ShippingPriceAmount, ScheduledDeliveryEndDate, ScheduledDeliveryStartDate, CODFeeCurrencyCode, CODFeeAmount, CODFeeDiscountCurrencyCode,
							CODFeeDiscountAmount, GiftMessageText, GiftWrapPriceCurrencyCode, GiftWrapPriceAmount, GiftWrapLevel, GiftWrapTaxCurrencyCode, 
							GiftWrapTaxAmount, ShippingTaxCurrencyCode, ShippingTaxAmount, ItemTaxCurrencyCode, ItemTaxAmount, 
							PromotionDiscountCurrencyCode, PromotionDiscountAmount, ConditionId, ConditionSubtypeId, AmazonOrderId, firstmod, lastmod)
						VALUES('" . $item['ASIN'] . "',
						'" . $item['OrderItemId'] . "',
						'" . $item['SellerSKU'] . "',
						'" . mysqli_real_escape_string($dbshop, $item['Title']) . "',
						'" . $QuantityOrdered . "',
						'" . $item['QuantityShipped'] . "',
						'" . $item['ItemPrice']['CurrencyCode'] . "',
						'" . $item['ItemPrice']['Amount'] . "',
						'" . $item['ShippingPrice']['CurrencyCode'] . "',
						'" . $item['ShippingPrice']['Amount'] . "',
						'" . $item['ScheduledDeliveryEndDate'] . "',
						'" . $item['ScheduledDeliveryStartDate'] . "',
						'" . $item['CODFee']['CurrencyCode'] . "',
						'" . $item['CODFee']['Amount'] . "',
						'" . $item['CODFeeDiscount']['CurrencyCode'] . "',
						'" . $item['CODFeeDiscount']['Amount'] . "',
						'" . $item['GiftMessageText'] . "',
						'" . $item['GiftWrapPrice']['CurrencyCode'] . "',
						'" . $item['GiftWrapPrice']['Amount'] . "',
						'" . $item['GiftWrapLevel'] . "',
						'" . $item['GiftWrapTax']['CurrencyCode'] . "',
						'" . $item['GiftWrapTax']['Amount'] . "',
						'" . $item['ShippingTax']['CurrencyCode'] . "',
						'" . $item['ShippingTax']['Amount'] . "',
						'" . $item['ItemTax']['CurrencyCode'] . "',
						'" . $item['ItemTax']['Amount'] . "',
						'" . $item['PromotionDiscount']['CurrencyCode'] . "',
						'" . $item['PromotionDiscount']['Amount'] . "',
						'" . $item['ConditionId'] . "',
						'" . $item['ConditionSubtypeId'] . "',
						'" . $orderItems['ListOrderItemsResult']['AmazonOrderId'] . "',
						'" . time() . "',
						'" . time() . "')";
						q($queryInsertAmazonOrdersItem, $dbshop, __FILE__, __LINE__);
						echo '	<ItemAdded>Insert Order Item</ItemAdded>' . "\n";
	
						$data = array();
						$data["importShopStatus"] = 0;
						$addWhere = "
							AmazonOrderId = '" . $orderItems['ListOrderItemsResult']['AmazonOrderId'] . "'";	
						SQLUpdate('amazon_orders', $data, $addWhere, 'shop', __FILE__, __LINE__);
					}
					$counter++;
				}
				
			}
			echo '</Order>'."\n";
		}
		echo '<submitResult>Import done</submitResult>'."\n";
	} else {
		echo '<submitResult>Nothing to do Luke..</submitResult>';
	}