<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for Orders Update
 *	- get the latest orders from amazon into the amazon orders table by accountssites id
 *
 * @params
 * - API Version: 2013-09-01
 * - submit method: GET
 * - action: ListOrders
 * - MessageType: OrderReport
 * - Type: Orders/2013-09-01
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
	$MWS_OPERATION_TYPE = '<Use the MWS Operation Type>';
	$MWS_MESSAGE_TYPE = '<User teh MWS Message Type>';

	$url = "AWSAccessKeyId=" . $AWS_ACCESS_KEY_ID . "&Action=ListOrders";
	$url.= "&CreatedAfter=" . date('c', strtotime('-2 day', time())) . "&CreatedBefore=" . date('c', strtotime('-10 minutes', time()));
	$url.= "&MarketplaceId.Id.1=" . $MARKETPLACE_ID . "&SellerId=" . $MERCHANT_ID;
	$url.= "&SignatureMethod=HmacSHA256&SignatureVersion=2&Timestamp=" . gmdate("Y-m-d\TH:i:s\Z") . "&Version=" . $APPLICATION_VERSION;

	$post_data = array();
	$post_data['url'] = $url;
	$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
	$post_data['type'] = $MWS_TYPE;
	$post_data['method'] = $MWS_METHOD;
	$results = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
	$xml = new SimpleXMLElement($results);
	$orders = json_decode(json_encode($xml), TRUE);

	//	if no orders then cancel
	if (isset($orders['ListOrdersResult']['Orders']))
	{
		if (isset($orders['Error'])) {
			echo $orders['Error']['Type'] . "\n";
			echo $orders['Error']['Code'] . "\n";
			echo $orders['Error']['Message'] . "\n";

			$showInfo = 'API Amazon->AmazonOrdersImport' . "\n";
			$showInfo.= $orders['Error']['Type'] . "\n";
			$showInfo.= $orders['Error']['Code'] . "\n";
			$showInfo.= $orders['Error']['Message'] . "\n";
			show_error(9797,8,__FILE__, __LINE__, $showInfo);
			exit;
		}
	}

	//	fix the order single result
	if (!isset($orders['ListOrdersResult']['Orders']['Order'][0])) 
	{
		$orders['ListOrdersResult']['Orders'] = array($orders['ListOrdersResult']['Orders']['Order']);
	}

	$xmlUpdate = "";
	$xmlAdd = "";
	
	//import or update orders
	foreach($orders['ListOrdersResult']['Orders'] as $order)
	{
		if (!isset($order[0])) 
		{
			$new_order = $order;
			$order = array();
			$order['Order'] = $new_order;
		}

		foreach($order as $singleOrder)
		{
			$data = array();
			$data['from'] = 'amazon_orders';
			$data['select'] = '*';
			$data['where'] = "
				AmazonOrderId = '" . $singleOrder['AmazonOrderId'] . "'";
			$currentOrder = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
			if (count($currentOrder) > 0)
			{
				//check for new data
				if ($currentOrder["importShopStatus"] < 3)
				{
					$importShopStatus = 1;
					if( (string)$singleOrder['OrderTotal']['Amount']!=$currentOrder["OrderTotalAmount"] ) $importShopStatus=0;
					if( (string)$singleOrder['ShippingAddress']['Phone']!=$currentOrder["ShippingAddressPhone"] ) $importShopStatus=0;
					if( (string)$singleOrder['ShippingAddress']['PostalCode']!=$currentOrder["ShippingAddressPostalCode"] ) $importShopStatus=0;
					if( (string)$singleOrder['ShippingAddress']['Name']!=$currentOrder["ShippingAddressName"] ) $importShopStatus=0;
					if( (string)$singleOrder['ShippingAddress']['CountryCode']!=$currentOrder["ShippingAddressCountryCode"] ) $importShopStatus=0;
					if( (string)$singleOrder['ShippingAddress']['StateOrRegion']!=$currentOrder["ShippingAddressStateOrRegion"] ) $importShopStatus=0;
					if( (string)$singleOrder['ShippingAddress']['AddressLine2']!=$currentOrder["ShippingAddressAddressLine2"] ) $importShopStatus=0;
					if( (string)$singleOrder['ShippingAddress']['AddressLine1']!=$currentOrder["ShippingAddressAddressLine1"] ) $importShopStatus=0;
					if( (string)$singleOrder['ShippingAddress']['City']!=$currentOrder["ShippingAddressCity"] ) $importShopStatus=0;
					if( (string)$singleOrder['EarliestDeliveryDate']!=$currentOrder["EarliestDeliveryDate"] ) $importShopStatus=0;
					if( (string)$singleOrder['OrderStatus']!=$currentOrder["OrderStatus"] ) $importShopStatus=0;
					if( (string)$singleOrder['LatestDeliveryDate']!=$currentOrder["LatestDeliveryDate"] ) $importShopStatus=0;
					if( (string)$singleOrder['BuyerName']!=$currentOrder["BuyerName"] ) $importShopStatus=0;
					if( (string)$singleOrder['BuyerEmail']!=$currentOrder["BuyerEmail"] ) $importShopStatus=0;
					if( (string)$singleOrder['PaymentMethod']!=$currentOrder["PaymentMethod"] ) $importShopStatus=0;
					if( (string)$singleOrder['OrderTotal']['CurrencyCode']!=$currentOrder["OrderTotalCurrencyCode"] ) $importShopStatus=0;

					// update amazon orders
					if ($importShopStatus == 0)
					{
						if ($singleOrder['OrderStatus'] == 'Canceled')
						{
							$queryUpdate = "
								UPDATE amazon_orders SET
								OrderStatus = '" . $singleOrder['OrderStatus'] . "',
								lastmod = '" . time() . "',
								importShopStatus = '" . $importShopStatus . "'
								WHERE id_orders = '" . $currentOrder['id_orders'] . "'";
							q($queryUpdate, $dbshop, __FILE__, __LINE__);
						} else {
							$queryUpdate = "
								UPDATE amazon_orders SET
								OrderTotalAmount = '" . $singleOrder['OrderTotal']['Amount'] . "',
								ShippingAddressPhone = '" . $singleOrder['ShippingAddress']['Phone'] . "',
								ShippingAddressPostalCode = '" . $singleOrder['ShippingAddress']['PostalCode'] . "',
								ShippingAddressName = '" . $singleOrder['ShippingAddress']['Name'] . "',
								ShippingAddressCountryCode = '" . $singleOrder['ShippingAddress']['CountryCode'] . "',
								ShippingAddressStateOrRegion = '" . $singleOrder['ShippingAddress']['StateOrRegion'] . "',
								ShippingAddressAddressLine2 = '" . $singleOrder['ShippingAddress']['AddressLine2'] . "',
								ShippingAddressAddressLine1 = '" . $singleOrder['ShippingAddress']['AddressLine1'] . "',
								ShippingAddressCity = '" . $singleOrder['ShippingAddress']['City'] . "',
								EarliestDeliveryDate = '" . $singleOrder['EarliestDeliveryDate'] . "',
								OrderStatus = '" . $singleOrder['OrderStatus'] . "',
								LatestDeliveryDate = '" . $singleOrder['LatestDeliveryDate'] . "',
								BuyerName = '" . $singleOrder['BuyerName'] . "',
								BuyerEmail = '" . $singleOrder['BuyerEmail'] . "',
								PaymentMethod = '" . $singleOrder['PaymentMethod'] . "',
								lastmod = '" . time() . "',
								OrderTotalCurrencyCode = '" . $singleOrder['OrderTotal']['CurrencyCode'] . "',
								importShopStatus = '" . $importShopStatus . "'
								WHERE id_orders = '" . $currentOrder['id_orders'] . "'";
							q($queryUpdate, $dbshop, __FILE__, __LINE__);
						}
						$xmlUpdate.= '<OrderUpdated>' . $currentOrder["id_orders"] . ' => ' . $singleOrder['AmazonOrderId'] . '</OrderUpdated>' . "\n";
					}
				}

			} else {

				// insert new amazon orders
				$queryInsertAmazonOrders = "
				INSERT INTO amazon_orders
					(ShipmentServiceLevelCategory, OrderTotalAmount, OrderTotalCurrencyCode, ShipServiceLevel, LatestShipDate,
					amazonAccountID, amazonAccountSiteId, MarketplaceId, SalesChannel, ShippingAddressPhone, ShippingAddressPostalCode, ShippingAddressName,
					ShippingAddressCountryCode, ShippingAddressStateOrRegion, ShippingAddressAddressLine2, ShippingAddressAddressLine1,
					ShippingAddressCity, EarliestDeliveryDate, ShippedByAmazonTFM, OrderType, FulfillmentChannel, LatestDeliveryDate,
					OrderStatus, BuyerName, BuyerEmail, LastUpdateDate, EarliestShipDate, PurchaseDate, NumberOfItemsUnshipped,
					NumberOfItemsShipped, AmazonOrderId, PaymentMethod, firstmod, lastmod)
				VALUES('" . $singleOrder['ShipmentServiceLevelCategory'] . "',
				'" . $singleOrder['OrderTotal']['Amount'] . "',
				'" . $singleOrder['OrderTotal']['CurrencyCode'] . "',
				'" . $singleOrder['ShipServiceLevel'] . "',
				'" . $singleOrder['LatestShipDate'] . "',
				'" . $amazonAccountsSites['account_id'] . "',
				'" . $amazonAccountsSites['id_accountsite'] . "',
				'" . $singleOrder['MarketplaceId'] . "',
				'" . $singleOrder['SalesChannel'] . "',
				'" . $singleOrder['ShippingAddress']['Phone'] . "',
				'" . $singleOrder['ShippingAddress']['PostalCode'] . "',
				'" . $singleOrder['ShippingAddress']['Name'] . "',
				'" . $singleOrder['ShippingAddress']['CountryCode'] . "',
				'" . $singleOrder['ShippingAddress']['StateOrRegion'] . "',
				'" . $singleOrder['ShippingAddress']['AddressLine2'] . "',
				'" . $singleOrder['ShippingAddress']['AddressLine1'] . "',
				'" . $singleOrder['ShippingAddress']['City'] . "',
				'" . $singleOrder['EarliestDeliveryDate'] . "',
				'" . $singleOrder['ShippedByAmazonTFM'] . "',
				'" . $singleOrder['OrderType'] . "',
				'" . $singleOrder['FulfillmentChannel'] . "',
				'" . $singleOrder['LatestDeliveryDate'] . "',
				'" . $singleOrder['OrderStatus'] . "',
				'" . $singleOrder['BuyerName'] . "',
				'" . $singleOrder['BuyerEmail'] . "',
				'" . $singleOrder['LastUpdateDate'] . "',
				'" . $singleOrder['EarliestShipDate'] . "',
				'" . $singleOrder['PurchaseDate'] . "',
				'" . $singleOrder['NumberOfItemsUnshipped'] . "',
				'" . $singleOrder['NumberOfItemsShipped'] . "',
				'" . $singleOrder['AmazonOrderId'] . "',
				'" . $singleOrder['PaymentMethod'] . "',
				'" . time() . "',
				'" . time() . "')";
				q($queryInsertAmazonOrders, $dbshop, __FILE__, __LINE__);
				$xmlAdd.= '	<OrderAdded>' . mysqli_insert_id($dbshop) . ' => ' . $singleOrder['AmazonOrderId'] . '</OrderAdded>' . "\n";
			}


            //	IMPORT ORDER ITEMS FROM AMAZON BY AMAZON ORDER ID
            $post_data = array();
            $post_data['API'] = "amazon";
            $post_data['APIRequest'] = "AmazonOrderItemsUpdate";
            $post_data['MessageType'] = "OrderReport";
            $post_data['action'] = "ListOrderItems";
            $post_data['account_id'] = $amazonAccountsSites['account_id'];
			$post_data['accountsite_id'] = $amazonAccountsSites['id_accountsite'];
            $post_data['AmazonOrderId'] = $singleOrder['AmazonOrderId'];
			$response = soa2($post_data, __FILE__, __LINE__, 'obj');
            foreach($response->Order as $xml)
            {
                if( isset($xml->ItemAdded[0]) ) $xmlItemAdd.= '	<ItemAdded>' . $xml->ItemAdded[0] . '</ItemAdded>' . "\n";
                if( isset($xml->ItemUpdated[0]) ) $xmlItemUpdate.= '	<ItemUpdated>' . $xml->ItemUpdated[0] . '</ItemUpdated>' . "\n";
            }
			echo '<OrderImport>*****************************************************' . "\n";
			echo '  <marketplace>' . $amazonAccountsSites['name'] . '</marketplace>' . "\n";
			echo $xmlUpdate . "\n" . $xmlAdd . $xmlItemAdd . $xmlItemUpdate;
			echo '</OrderImport>' . "\n";
		}
	}

