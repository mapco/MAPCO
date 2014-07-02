<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for products prices updates
 *	-
 *
 * @params
 * - API Version: 2009-01-01
 * - submit method: POST
 * - action: SubmitFeed
 * - MessageType: Price
 * - FeedType: _POST_PRODUCT_PRICING_DATA_
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

//	keep post submit
$post = $_POST;

	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);

    //	get amazon account by account id
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
	$MWS_MESSAGE_TYPE = $post['MessageType'];

	$url = "Action=" . $post['action'] . "&FeedType=" . $post['FeedType'] . "";
	$url.= "&AWSAccessKeyId=" . $AWS_ACCESS_KEY_ID . "&" . getAmazonMarketplaceListName($amazonAccountsSites) . "=" . $MARKETPLACE_ID;
	$url.= "&Merchant=" . $MERCHANT_ID . "&Timestamp=" . gmdate("Y-m-d\TH:i:s\Z");
	$url.= "&Version=" . $APPLICATION_VERSION . "&SignatureVersion=2&SignatureMethod=HmacSHA256";

	//	get the amazon products data object
	$data = array();
	$data['form'] = 'amazon_products';
	$data['select'] = '*';
	$data['where'] = "
		accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "'
		AND submitedPrice = 0
	";
	$data['orderBy'] = "lastpriceupdate DESC";
	$amazonProducts = SQLSelect($data['form'], $data['select'], $data['where'], $data['orderBy'], 0, $post['limit'], 'shop',  __FILE__, __LINE__);
	if (count($amazonProducts) > 0) 
	{
		//	create a xml product price feed
		$countUpdate = 0;
		$xmlProduct = "";
		foreach ($amazonProducts as $amazonProduct)
		{
			$xmlProduct.= '
				<Message>
					<MessageID>' . $amazonProduct['id_product'] . '</MessageID>
						<Price>
							<SKU>' . $amazonProduct['SKU'] . '</SKU>
							<StandardPrice currency="' . $amazonAccountsSites['currency'] . '">' . $amazonProduct['StandardPrice'] . '</StandardPrice>
						</Price>
				</Message>';
				
			//	update status into the amazon products table
			$updateAmazonProductsPrice[] = $amazonProduct['id_product'];
			$countUpdate++;	
		}
		
		//	set submited price
		if (sizeof($updateAmazonProductsPrice) > 0) 
		{	
			$data = array();
			$data['submitedPrice'] = 1;
			$date['lastpriceupdate'] = time();
			$data['lastmod'] = time();
			$addWhere = "
				id_product IN (" . implode(", ", $updateAmazonProductsPrice) . ")
			";		
			SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}		
		
		$xmlFeedContent = '<?xml version="1.0" ?>
		<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
			<Header>
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>' . $MERCHANT_ID . '</MerchantIdentifier>
			</Header>
			<MessageType>' . $MWS_MESSAGE_TYPE . '</MessageType>
			<PurgeAndReplace>false</PurgeAndReplace>';
				$xmlFeedContent.= $xmlProduct;
			$xmlFeedContent.= '
		</AmazonEnvelope>';

		$post_data = array();
		$post_data['url'] = $url;
		$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
		$post_data['data'] = $xmlFeedContent;
		$post_data['method'] = $MWS_METHOD;
		$response = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
		
		// clear the response from the amazon submit
		$dom = new DOMDocument();
		$dom->loadXML($response);
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->saveXML();
		$xml = "\n" . "<AmazonProductPricesExport>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= '	<textContent>' . $dom->textContent . '</textContent>' . "\n";
		$xml.= '	<update>Update Prices: ' . $countUpdate . '</update>' . "\n";
		$xml.= '</AmazonProductPricesExport>'. "\n";
		echo $xml;		
	} else {
		$xml = "\n" . "<AmazonProductPricesExport>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= '	<textContent>no product prices available</textContent>' . "\n";
		$xml.= '</AmazonProductPricesExport>'. "\n";
		echo $xml;		
	}
