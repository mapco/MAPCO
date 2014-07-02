<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Services for Product Updates
 *	- update a amazon product with mapco amazon products (ASIN)
 *
 * @params
 * - API Version: 2011-10-01
 * - submit method: GET
 * - action: SubmitFeed
 * - MessageType: Product
 * - FeedType: _POST_PRODUCT_DATA_
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

//	keep post submit
$post = $_POST;

    //	get the amazon account data
    $amazonAccountQuery = "
        SELECT *
        FROM amazon_accounts
        WHERE id_account = '" . $post['id_account'] . "'";
    $amazonAccountResult = q($amazonAccountQuery, $dbshop, __FILE__, __LINE__);
    $amazonAccount = mysqli_fetch_array($amazonAccountResult);
	
    //	get the amazon account data
    //$amazonAccount = getAmazonAccount($post);
	
	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsitesByAccountId($post);
	
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
    $MWS_METHOD = 'GET';
	$MWS_OPERATION_TYPE = 'Update';
	$MWS_MESSAGE_TYPE = $post['MessageType'];		

    //	amazon mws GetMatchingProductForId
    //	use version 2011-10-01
    //	and submit as GET
    $urlTerm = "&AWSAccessKeyId=" . $amazonAccount["AWSAccessKeyId"] . "&MarketplaceId=" . $amazonAccount["MarketplaceId"];
    $urlTerm.= "&SellerId=" . $amazonAccount["MerchantId"] . "&Timestamp=" . gmdate("Y-m-d\TH:i:s\Z");
    $urlTerm.= "&Version=2011-10-01&SignatureVersion=2&SignatureMethod=HmacSHA256";

    //	asin check status for amazon products table
    if ($post['asincheck'] == 1) 
	{
        $amazonProductsQuery = "
            SELECT *
            FROM amazon_products
            WHERE account_id = " . $post['id_account'] . "
            AND ASIN = ''
            AND upload = 0
            ORDER BY lastmod DESC ";
        if ($post['limit'] > 0) 
		{
            $amazonProductsQuery.= "LIMIT " . $post['limit'];
        }
        $amazonProductsResult = q($amazonProductsQuery, $dbshop, __FILE__, __LINE__);
        if (mysqli_num_rows($amazonProductsResult) > 0) 
		{
            while($product = mysqli_fetch_array($amazonProductsResult))
            {
                $queryUpdate = "
                UPDATE amazon_products
                SET upload = '1' WHERE id_product = '" . $product['id_product'] . "'";
                q($queryUpdate, $dbshop, __FILE__, __LINE__);
                echo 'set upload = 1 for ProductID ' . $product['id_product'] . "\n";
            }
        }
    }

    //	amazon products Query
    if (isset($post['id_product']) && !empty($post['id_product'])) 
	{
        $amazonProductsQuery = "
            SELECT *
            FROM amazon_products
            WHERE id_product = " . $post['id_product'] . "
            AND account_id = " . $post['id_account'] . "
            AND ASIN = ''
            AND upload = 1";
    } else {
        $amazonProductsQuery = "
            SELECT *
            FROM amazon_products
            WHERE account_id = " . $post['id_account'] . "
            AND ASIN = ''
            AND upload = 1
            ORDER BY lastmod DESC ";
            if ($post['limit'] > 0) 
			{
                $amazonProductsQuery.= "
                    LIMIT " . $post['limit'];
            }
    }
    $amazonProductsResult = q($amazonProductsQuery, $dbshop, __FILE__, __LINE__);
    if (mysqli_num_rows($amazonProductsResult) > 0) 
	{
        $xml = '';
        while($product = mysqli_fetch_array($amazonProductsResult))
        {
            $i++;
            $post_data = array();
            $post_data['url'] = "Action=" . $post['action'] . "&IdType=SellerSKU&IdList.Id.1=" . $product['SKU'] . $urlTerm;
            $post_data['SecretKey'] = $amazonAccount["SecretKey"];
            $post_data['type'] = 'Products/2011-10-01';
            $post_data['method'] = 'GET';
            $results = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
            $xml = new SimpleXMLElement($results);
            $productResult = json_decode(json_encode($xml), TRUE);
            if (isset($productResult['Error'])) 
			{
                echo 'Error, Product not found:' . $product['id_product'] . "\n";
            } else {
                $asin = $productResult["GetMatchingProductForIdResult"]["Products"]['Product']['Identifiers']['MarketplaceASIN']['ASIN'];
                if (!empty($asin)) 
				{
                    $queryUpdate = "
                        UPDATE amazon_products
                        SET item_id = '" . $product['item_id'] . "',
                        lastupload = '" . time() . "',
                        ASIN = '" . $asin . "',
                        upload = '0' WHERE id_product = '" . $product['id_product'] . "'";
                    q($queryUpdate, $dbshop, __FILE__, __LINE__);
                    echo 'Update ASIN for ProductID ' . $product['id_product'] . "\n";
                } else {
                    $queryUpdate = "
                        UPDATE amazon_products
                        SET item_id = '" . $product['item_id'] . "',
                        upload = '1' WHERE id_product = '" . $product['id_product'] . "'";
                    q($queryUpdate, $dbshop, __FILE__, __LINE__);
                    echo 'no ASIN for ProductID ' . $product['id_product'] . "\n";
                }
            }
        }
    }
    echo 'Done!';