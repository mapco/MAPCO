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

	$data = array();
	$data['from'] = 'amazon_products';
	$data['select'] = 'id_product, ASIN, accountsite_id, TopPrice, importTopPrice';
	$addWhere = "
		accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "' 
		AND importTopPrice = 0";
	if (isset($post['ASIN']) && !empty($post['ASIN'])) {		 
		$addWhere.= " AND ASIN = '" . $post['ASIN']. "'";
	} else {
		$addWhere.= " AND ASIN != ''";	
	}
	$amazonProductResults = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, $post['limit'], 'shop',  __FILE__, __LINE__);
	
	if (count($amazonProductResults) > 0) {
		
		$countInsert = 0;
		foreach($amazonProductResults as $amazonProductResult) 
		{
			$post_data = array();
			$post_data['url'] = "Action=" . $post['action'] . "&ASINList.ASIN.1=" . $amazonProductResult['ASIN'] . $urlTerm;
			$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
			$post_data['type'] = $MWS_TYPE;
			$post_data['method'] = $MWS_METHOD;
			$response = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
			$xml = new SimpleXMLElement($response);
			$offerResult = json_decode(json_encode($xml), TRUE);
			if (isset($offerResult['Error'])) {
				$xmlError.= '<errorImport>Error, ASIN not found:' . $amazonProductResult['ASIN'] . '</errorImport>' . "\n";
			} else {
		
				//	check if ASIN exist
				$addWhere = "
					MarketplaceId = '" . $MARKETPLACE_ID . "' 
					AND ASIN = '" . $amazonProductResult['ASIN'] . "'";
				$amazonOffersCheck = SQLCount('amazon_offers', $addWhere, 'shop',  __FILE__, __LINE__);
		
				if ($amazonOffersCheck < 1) {
					$MarketplaceId = $offerResult['GetLowestOfferListingsForASINResult']['Product']['Identifiers']['MarketplaceASIN']['MarketplaceId'];
					$ASIN = $offerResult['GetLowestOfferListingsForASINResult']['Product']['Identifiers']['MarketplaceASIN']['ASIN'];
					$offers = $offerResult['GetLowestOfferListingsForASINResult']['Product']['LowestOfferListings']['LowestOfferListing'];

					if (isset($offers[0])) {
						//	insert more then one offer by ASIN
						foreach($offers as $offer)
						{
							addAmazonOfferByAsin($MarketplaceId, $ASIN, $offer);
							$countInsert++;
						}
					} else {
						//	insert only one offer by ASIN
						addAmazonOfferByAsin($MarketplaceId, $ASIN, $offers);
						$countInsert++;
					}
				}
			}
			$data = array();
			$data['importTopPrice'] = 1;
			$addWhere = "
				id_product = " . $amazonProductResult['id_product'];
			SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}
	}
	echo '<amazonOfferPriceImport>' . "\n";
	echo '	<insert>Insert for ' . $countInsert .  ' prices</insert>' . "\n";
	echo '</amazonOfferPriceImport>' . "\n";	
	
