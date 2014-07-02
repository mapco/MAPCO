<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Services for Offer Listing for ASIN
 *	- import the amazon offers for ASIN into the amazon offer table
 *
 * @params
 * - API Version: 2011-10-01
 * - submit method: GET
 * - action: GetLowestOfferListingsForASIN
 * - MessageType: Product
 * - Type: Products/2011-10-01
 * - IdType: SellerSKU
*******************************************************************************/
$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');

//	keep post submit
$post = $_POST;

	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);

    //  get the amazon account data
    $amazonAccount = getAmazonAccount($amazonAccountsSites);

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
    $APPLICATION_VERSION = '2011-10-01';

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
    $MWS_TYPE = 'Products/2011-10-01';
    $MWS_METHOD = 'GET';
	$MWS_OPERATION_TYPE = 'Update';
	$MWS_MESSAGE_TYPE = $post['MessageType'];

	//	amazon mws GetMatchingProductForId
	//	use version 2011-10-01
	//	and submit as GET
	$urlTerm = "&AWSAccessKeyId=" . $AWS_ACCESS_KEY_ID . "&MarketplaceId=" . $MARKETPLACE_ID;
	$urlTerm.= "&SellerId=" . $MERCHANT_ID . "&Timestamp=" . gmdate("Y-m-d\TH:i:s\Z");
	$urlTerm.= "&Version=" . $APPLICATION_VERSION . "&SignatureVersion=2&SignatureMethod=HmacSHA256";

	$post_data = array();
	$post_data['url'] = "Action=" . $post['action'] . "&ASINList.ASIN.1=" . $post['ASIN'] . $urlTerm;
	$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
	$post_data['type'] = $MWS_TYPE;
	$post_data['method'] = $MWS_METHOD;
	$response = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
	$xml = new SimpleXMLElement($response);
	$offerResult = json_decode(json_encode($xml), TRUE);
			var_dump($offerResult);
	exit;		
		
		
		if (isset($offerResult['Error']))
		{
			$xmlError.= '<errorImport>Error, ASIN not found:' . $post['ASIN'] . '</errorImport>' . "\n";
		} else {
			var_dump($offerResult);
			exit;
		}
		

	
	
	