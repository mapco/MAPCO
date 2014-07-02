<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for Product Data Update
 *	- update a mapco amazon product whit amazon product shop
 *
 * @params
 * - API Version: 2009-01-01
 * - Operation Type: Update
 * - submit method: POST
 * - action: SubmitFeed
 * - MessageType: Product
 * - FeedType: _POST_PRODUCT_DATA_
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
require_once("../functions/cms_core.php");

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
	$MWS_OPERATION_TYPE = 'Update';
	$MWS_MESSAGE_TYPE = $post['MessageType'];

	$url = "Action=" . $post['action'] . "&FeedType=" . $post['FeedType'] . "";
	$url.= "&AWSAccessKeyId=" . $AWS_ACCESS_KEY_ID . "&" . getAmazonMarketplaceListName($amazonAccountsSites) . "=" . $MARKETPLACE_ID;
	$url.= "&Merchant=" . $MERCHANT_ID . "&Timestamp=" . gmdate("Y-m-d\TH:i:s\Z");
	$url.= "&Version=" . $APPLICATION_VERSION . "&SignatureVersion=2&SignatureMethod=HmacSHA256";
	
	//	update a single product by product id
	if (isset($post['id_product']) && !empty($post['id_product'])) 
	{
		$data = array();
		$data['form'] = 'amazon_products';
		$data['select'] = '*';	
		$data['where'] = "
			id_product = " . $post['id_product'] . "
			AND accountsite_id = " . $amazonAccountsSites['id_accountsite'];
		$amazonProductsResult = SQLSelect($data['form'], $data['select'], $data['where'], 0, 0, $post['limit'], 'shop',  __FILE__, __LINE__);
	} else {
		// update all products by accountsite id
		$data = array();
		$data['form'] = 'amazon_products';
		$data['select'] = '*';
		$data['where'] = "
			accountsite_id = '" . $amazonAccountsSites["id_accountsite"] . "'
			AND StandardPrice > 0
			AND EAN > 0
			AND submitedProduct = 0
			AND upload = 1
		";
		$data['orderBy'] = 'lastmod DESC';
		$amazonProductsResult = SQLSelect($data['form'], $data['select'], $data['where'], $data['orderBy'], 0, $post['limit'], 'shop',  __FILE__, __LINE__);
	}

    if (count($amazonProductsResult) > 0) 
	{
        //	create a xml product feed
        $xmlProduct = "";
        foreach($amazonProductsResult as $product)
        {
            //	The optional OperationType element can be used to specify the type of operation
            //	to be performed on the data. The OperationType is only applicable to product-related feeds (Product, Inventory, Price, etc)
            //	and will be ignored for non-applicable feeds.
            //	(Update, Delete or PartialUpdate)

            //	in product -> <LaunchDate></LaunchDate>
            //	in StandardPrice currency=" from accountsite "
            $xmlProduct.= '
            <Message>
                <MessageID>' . $product['id_product'] . '</MessageID>
                <OperationType>' . $MWS_OPERATION_TYPE . '</OperationType>
                <Product>
                    <SKU>' . $product['SKU'] . '</SKU>
                    <StandardProductID>
                        <Type>EAN</Type>
                        <Value>' . $product['EAN'] . '</Value>
                    </StandardProductID>
                    <Condition>
                        <ConditionType>New</ConditionType>';
						if (!empty($product['OfferingConditionNote'])) 
						{
							$xmlProduct.= '<ConditionNote>' . $product['OfferingConditionNote'] . '</ConditionNote>';
						}	
                    $xmlProduct.= '
					</Condition>
                    <DescriptionData>';
					if (!empty($product['SubTitle'])) 
					{
						$subTitle = ' - ' . $product['SubTitle'];
					}
					$xmlProduct.= '
                        <Title>' . $product['Title'] .  $subTitle . '</Title>
                        <Brand>' . $product['Brand'] . '</Brand>
                        <Description><![CDATA[' . $product['Description'] . ']]></Description>' . "\n";
                        $xmlProduct.= trim($product['BulletPoints']) . "\n";
						$xmlProduct.= '<MSRP currency="' . $amazonAccountsSites['currency'] . '">' . $product['StandardPrice'] . '</MSRP>' . "\n";
                        $xmlProduct.= '<Manufacturer>' . $product['Manufacturer'] . '</Manufacturer>' . "\n";
                        $xmlProduct.= '<MfrPartNumber>' . $product['SKU'] . '</MfrPartNumber>' . "\n";
                            //synonyms
							$data = array();
							$data['from'] = 'shop_items_keywords';
							$data['select'] = '*';
							$data['where'] = "
								GART = " . $product['GART'] . "
                                AND language_id = " . $amazonAccountsSites['language_id'];
							$data['orderBy'] = 'ordering';
							$shopItemsKeywordsResults = SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 1, 5, 'shop',  __FILE__, __LINE__);
                            if (count($shopItemsKeywordsResults) > 0) 
							{
								foreach($shopItemsKeywordsResults as $shopItemsKeyword)
								{
									$shopItemsKeyword["keyword"] = str_replace(",", "", $shopItemsKeyword["keyword"]);
									$shopItemsKeyword["keyword"] = str_replace(" ", "", $shopItemsKeyword["keyword"]);
									$xmlProduct.= '<SearchTerms>' . $shopItemsKeyword["keyword"] . '</SearchTerms>' . "\n";
								}
							}
                        $xmlProduct.= trim($product['RecommendedBrowseNode']) . '
                    </DescriptionData>
                    <ProductData>
                        <AutoAccessory>
                            <ProductType>
                                <AutoAccessoryMisc>
                                    <NumberOfItems>1</NumberOfItems>
                                </AutoAccessoryMisc>
                            </ProductType>
                        </AutoAccessory>
                    </ProductData>
                </Product>
            </Message>';

            //  update submited status into the amazon products table
			$data = array();
			$data['submitedProduct'] = 1;
			$data['submitedProductDate'] = time();
			$addWhere = "
				id_product = " . $product['id_product'];
			$results = SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);		
        }

        $xmlFeedContent = '
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
		$xml = "\n" . "<AmazonProductDataExport>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= '	<textContent>' . $dom->textContent . '</textContent>' . "\n";
		$xml.= '</AmazonProductDataExport>'. "\n";
		echo $xml;
    } else {
		$xml = "\n" . "<AmazonProductDataExport>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= '	<textContent>no product data available</textContent>' . "\n";
		$xml.= '</AmazonProductDataExport>'. "\n";
		echo $xml;		
	}