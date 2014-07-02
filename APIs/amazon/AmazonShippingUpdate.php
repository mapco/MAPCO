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

	//	update amazon_orders done
	$amazonOrdersQuery = "
		SELECT *
		FROM amazon_orders
		WHERE importShopStatus < 3";
    $amazonOrdersResults = q($amazonOrdersQuery, $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($amazonOrdersResults) > 0)
	{
		while($amazonOrders = mysqli_fetch_assoc($amazonOrdersResults))
		{
			$shopOrdersQuery = "
				SELECT *
				FROM shop_orders
				WHERE foreign_OrderID = '" . $amazonOrders["AmazonOrderId"] . "'
				AND shipping_number != ''";
			$shopOrdersResult = q($shopOrdersQuery, $dbshop, __FILE__, __LINE__);
			$countShippingUpdate = 0;
			if (mysqli_num_rows($shopOrdersResult) > 0)
			{
				$shopOrder = mysqli_fetch_assoc($shopOrdersResult);
				$data = array();
				$data["shippingStatusId"] = $shopOrder["status_id"];
				$data["shippingStatusDate"] = $shopOrder["status_date"];
				$data["ShippingNumber"] = $shopOrder["shipping_number"];
				$data["importShopStatus"] = 3;
				q_update("amazon_orders", $data, "WHERE id_orders = " . $amazonOrders["id_orders"] . ";", $dbshop, __FILE__, __LINE__);
				$countShippingUpdate++;
			}
		}
	}

    //	get the amazon accounts data
    $amazonAccountResults = getAmazonAccounts();
    if (count($amazonAccountResults) > 0) {
		foreach($amazonAccountResults as $amazonAccount)
        {
            //	get amazon accountsites for amazon marketplaces by account id
            $amazonAccountsSitesQuery = "
                SELECT *
                FROM amazon_accounts_sites
                LEFT JOIN amazon_marketplaces ON id_marketplace = marketplace_id
                WHERE account_id = " . $amazonAccount['id_account'] . "";
            $amazonAccountsSitesResults = q($amazonAccountsSitesQuery, $dbshop, __FILE__, __LINE__);
            if (mysqli_num_rows($amazonAccountsSitesResults) > 0)
            {
                while ($amazonAccountsSites = mysqli_fetch_assoc($amazonAccountsSitesResults))
                {
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
                    $APPLICATION_VERSION = '2009-01-01';

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
                    $MWS_TYPE = '<Use the MWS Type>';
                    $MWS_METHOD = 'POST';
                    $MWS_OPERATION_TYPE = 'Update';
                    $MWS_MESSAGE_TYPE = 'OrderFulfillment';

                    //generate URL
                    $post['Action'] = 'SubmitFeed';
                    $post["FeedType"] = '_POST_ORDER_FULFILLMENT_DATA_';

                    $url = "Action=" . $post['Action'] . "&FeedType=" . $post['FeedType'] . "";
                    $url.= "&AWSAccessKeyId=" . $AWS_ACCESS_KEY_ID . "&" . getAmazonMarketplaceListName($amazonAccountsSites) . "=" . $MARKETPLACE_ID;
                    $url.= "&Merchant=" . $MERCHANT_ID . "&Timestamp=" . gmdate("Y-m-d\TH:i:s\Z");
                    $url.= "&Version=" . $APPLICATION_VERSION . "&SignatureVersion=2&SignatureMethod=HmacSHA256";

                    $xmlShippingStatus = '';
                    $i = 1;
                    $updates = array();

                    $amazonOrdersQuery = "
                        SELECT *
                        FROM amazon_orders
                        WHERE importShopStatus = 3
                        AND ShippingNumber != ''";
                    $amazonOrdersResults = q($amazonOrdersQuery, $dbshop, __FILE__, __LINE__);
                    while ($amazonOrder = mysqli_fetch_assoc($amazonOrdersResults))
                    {
                        $OperationType = 'Update';
                        $xmlShippingStatus .= '
                        <Message>
                            <MessageID>' . $i . '</MessageID>
                            <OrderFulfillment>
                                <AmazonOrderID>' . $amazonOrder['AmazonOrderId'] . '</AmazonOrderID>
                                <FulfillmentDate>' . date('c', $amazonOrder['shippingStatusDate']) . '</FulfillmentDate>
                                <FulfillmentData>
                                   <CarrierName>DHL</CarrierName>';
                                    if (!empty($amazonOrder['shippingNumber']))
                                    {
                                        $xmlShippingStatus .= '<ShipperTrackingNumber>' . $amazonOrder['shippingNumber'] . '</ShipperTrackingNumber>';
                                    }
                               $xmlShippingStatus .= '
                               </FulfillmentData>
                            </OrderFulfillment>
                        </Message>';
                        $i++;

                        $xmlItemShipped.= '	<ItemShipped>' . $amazonOrder['AmazonOrderId'] . '</ItemShipped>' . "\n";

                        //	update shipping status
                        $updates[] = $amazonOrder['id_orders'];
                    }

                    if (sizeof($updates) > 0 )
                    {
                        $xmlFeedContent = '<?xml version="1.0" ?>
                        <AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
                            <Header>
                                <DocumentVersion>1.01</DocumentVersion>
                                <MerchantIdentifier>' . $MERCHANT_ID . '</MerchantIdentifier>
                            </Header>
                            <MessageType>' . $MWS_MESSAGE_TYPE . '</MessageType>
                            <PurgeAndReplace>false</PurgeAndReplace>';
                                $xmlFeedContent.= $xmlShippingStatus;
                            $xmlFeedContent.= '
                        </AmazonEnvelope>';

                        $post_data = array();
                        $post_data['url'] = $url;
                        $post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
                        $post_data['data'] = $xmlFeedContent;
                        $post_data['method'] = $MWS_METHOD;
                        $response = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);						

                        if (strpos($response, '<FeedProcessingStatus>_SUBMITTED_</FeedProcessingStatus>') !== false)
                        {
                            q("UPDATE amazon_orders
                                SET importShopStatus = 4
                                WHERE id_orders IN (".implode(", ", $updates).");", $dbshop, __FILE__, __LINE__);
                        }
						
						// clear the response from the amazon submit
						$dom = new DOMDocument();
						$dom->loadXML($response);
						$dom->preserveWhiteSpace = false;
						$dom->formatOutput = true;
						$dom->saveXML();
						$xml = "\n" . "<AmazonShippingExport>" . "\n";
						$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
						$xml.= '	<countShippingUpdate>Shop Orders Update: ' . $countShippingUpdate . '<countShippingUpdate>' . "\n";
						$xml.= $xmlItemShipped;
						$xml.= '	<textContent>' . $dom->textContent . '</textContent>' . "\n";
						$xml.= '</AmazonShippingExport>'. "\n";
						echo $xml;						
                    } else {
						$xml = "\n" . "<AmazonShippingExport>" . "\n";
						$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
						$xml.= '	<countShippingUpdate>Shop Orders Update: ' . $countShippingUpdate . '<countShippingUpdate>' . "\n";
						$xml.= '	<textContent>no shipping updates available</textContent>' . "\n";
						$xml.= '</AmazonShippingExport>'. "\n";
						echo $xml;							
					}
                }
            }
        }
    }