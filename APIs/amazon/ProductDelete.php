<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for products deleted
 *	-
 *
 * @params
 * - API Version: 2009-01-01
 * - submit method: POST
 * - action: SubmitFeed
 * - MessageType: Inventory
 * - FeedType: _POST_INVENTORY_AVAILABILITY_DATA_
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

//	keep post submit
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
	$MWS_OPERATION_TYPE = 'Delete';
	$MWS_MESSAGE_TYPE = $post['MessageType'];

	$url = "Action=" . $post['action'] . "&FeedType=" . $post['FeedType'] . "";
	$url.= "&AWSAccessKeyId=" . $AWS_ACCESS_KEY_ID . "&" . getAmazonMarketplaceListName($amazonAccountsSites) . "=" . $MARKETPLACE_ID;
	$url.= "&Merchant=" . $MERCHANT_ID . "&Timestamp=" . gmdate("Y-m-d\TH:i:s\Z");
	$url.= "&Version=" . $APPLICATION_VERSION . "&SignatureVersion=2&SignatureMethod=HmacSHA256";
	
	$data = array();
	$data['from'] = 'amazon_inventory';
	$data['select'] = '*';
	$addWhere = "
		product_id = 0
		AND submitedDelete = 0
		AND accountsite_id = " . $amazonAccountsSites['id_accountsite'];
	$amazonProducts = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, $post['limit'], 'shop',  __FILE__, __LINE__);	
	if (count($amazonProducts) > 0) {
		//	create a xml product delete feed
		$countUpdate = 0;
		$xmlProduct = "";
		foreach ($amazonProducts as $amazonProduct)
		{
			$xmlProduct.= '
				<Message>
					<MessageID>' . $amazonProduct['id_inventory'] . '</MessageID>
					<Product>
						<SKU>' . $amazonProduct['sku']. '</SKU>
					</Product>
				</Message>';
				$updateAmazonInventory[] = $amazonProduct['id_inventory'];
				$countUpdate++;
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

		//	update status into the amazon inventory table		
		if (sizeof($updateAmazonInventory) > 0) 
		{
			$data = array();
			$data['submitedDelete'] = 1;
			$addWhere = "
				id_inventory IN (" . implode(", ", $updateAmazonInventory) . ")
			";
			SQLUpdate('amazon_inventory', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}		
			
		$post_data = array();
		$post_data['url'] = $url;
		$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
		$post_data['data'] = $xmlFeedContent;
		$post_data['method'] = $MWS_METHOD;
		$response = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
		
		//	clear the response from the amazon submit
		$dom = new DOMDocument();
		$dom->loadXML($response);
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->saveXML();
		$xml = "\n" . "<AmazonProductDeleteExport>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= '	<textContent>' . $dom->textContent . '</textContent>' . "\n";
		$xml.= '	<update>Delete Products: ' . $countUpdate . '</update>' . "\n";
		$xml.= '</AmazonProductDeleteExport>'. "\n";
		echo $xml;		
	} else {
		$xml = "\n" . "<AmazonProductDeleteExport>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= '	<textContent>no product available to delete</textContent>' . "\n";
		$xml.= '</AmazonProductDeleteExport>'. "\n";
		echo $xml;		
	}	