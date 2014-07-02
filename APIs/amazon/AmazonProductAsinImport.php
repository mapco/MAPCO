<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Services for Product Updates
 *	- import the amazon ASIN into the amazon product table
 *
 * @params
 * - API Version: 2011-10-01
 * - submit method: GET
 * - action: GetMatchingProductForId
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

	//	amazon products Query
	$from = 'amazon_products';
	$select = '*';
	if (isset($post['id_product']) && !empty($post['id_product'])) {
		$addWhere = "
			id_product = " . $post['id_product'] . "
			AND accountsite_id = " . $amazonAccountsSites['id_accountsite'] . "
			AND ASIN = ''
			AND asinImport = 0";
		$amazonProductsResults = SQLSelect($from, $select, $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
	} else {
		$addWhere = "
			accountsite_id = " . $amazonAccountsSites['id_accountsite'] . "
			AND ASIN = ''
			AND asinImport = 0";
		$orderBy = 'lastmod DESC';
		$amazonProductsResults = SQLSelect($from, $select, $addWhere, $orderBy, 0, $post['limit'], 'shop',  __FILE__, __LINE__);
	}

	if (count($amazonProductsResults) > 0) {
		$xml = '';
		foreach($amazonProductsResults as $product)
		{
			$i++;
			$post_data = array();
			$post_data['url'] = "Action=" . $post['action'] . "&IdType=SellerSKU&IdList.Id.1=" . $product['SKU'] . $urlTerm;
			$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
			$post_data['type'] = $MWS_TYPE;
			$post_data['method'] = $MWS_METHOD;
			$response = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);

			//	clear the response from the amazon submit, this is important, because we use the json_decode / encode fairytale
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			$dom->saveXML();

			if ($dom->textContent != null) {
				$xml = new SimpleXMLElement($response);
				$productResult = json_decode(json_encode($xml), TRUE);

                if (isset($productResult['Error'])) {
                    $xmlError.= '<errorImport>Error, Product not found:' . $product['id_product'] . '</errorImport>' . "\n";
                } else {

					$asin = $productResult["GetMatchingProductForIdResult"]["Products"]['Product']['Identifiers']['MarketplaceASIN']['ASIN'];
					
					//	update current asin data
					if (!empty($asin)) {

						$data = array();
						$data['item_id'] = $product['item_id'];
						$data['lastupload'] = time();
						$data['ASIN'] = $asin;
						$data['asinImport'] = 1;
						$data['upload'] = 0;
						$addWhere = "
							id_product = '" . $product['id_product'] . "'";
						SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
                        $xmlImport.= '	<Asin>Update ASIN for ProductID ' . $product['id_product'] . '</Asin>' . "\n";
                    } else {
						
						//	update amazon product without asin data
						$data = array();
						$data['item_id'] = $product['item_id'];
						$data['asinImport'] = 1;
						$data['upload'] = 1;
						$addWhere = "
							id_product = '" . $product['id_product'] . "'";
						SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
                        //	use for information about no asin import
                        $xmlNoImport.= '	<noAsin>no ASIN for ProductID ' . $product['id_product'] . '<noAsin>' . "\n";
                    }
                }
            }
        }
        if (empty($xmlImport)) {
            $xmlImport = '	<asin>no updates for ' . $amazonAccountsSites["name"] . '</asin>' . "\n";
        }
	} else {
        $xmlImport = '	<asin>all updates finish for ' . $amazonAccountsSites["name"] . '</asin>' . "\n";
    }
	$xml = "\n" . "<amazonAsinImport>" . "\n" . $xmlError . $xmlImport . '</amazonAsinImport>'. "\n";
	echo $xml;