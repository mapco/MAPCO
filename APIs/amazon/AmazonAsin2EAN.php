<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for products quantities update
 *	source: https://github.com/Exeu/Amazon-ECS-PHP-Library/downloads
 *
 * @params
*******************************************************************************/
$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');

//	keep post submit
$post = $_POST;

if ($post['action'] == 'convertAsinToEan') 
{
	$amazonAccount = getAmazonAccount($post);
	$amazonAccountSites = getAmazonAccountsitesByAccountId($post);
	pr($amazonAccountSites, true);
	define('AWS_API_KEY', $amazonAccount['AWS_AccessKeyId']);
	define('AWS_API_SECRET_KEY', $amazonAccount['AWS_SecretAccessKey']);
	define('AWS_ASSOCIATE_TAG', $amazonAccount['AWS_Associate_tag']);
	define('AWS_ANOTHER_ASSOCIATE_TAG', 'ANOTHER ASSOCIATE TAG');
	
	require_once($PATH . '/AWS/lib/AmazonECS.class.php');
	$amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'DE', AWS_ASSOCIATE_TAG);
	$amazonEcs->associateTag(AWS_ASSOCIATE_TAG);
	$amazonEcs->returnType(AmazonECS::RETURN_TYPE_ARRAY);
	
	// Looking up multiple items
	if (isset($post['convertASIN']) && $post['convertASIN'] != null) {
		$response = $amazonEcs->responseGroup('Large')->optionalParameters(array('Condition' => 'New'))->lookup($post['convertASIN'], $post['convertASIN']);
	}
	//$response = $amazonEcs->responseGroup('Images')->lookup('B0017TZY5Y');

	$listPriceCurrencyCode = $response['Items']['Item']['ItemAttributes']['ListPrice']['CurrencyCode'];
	$listPriceFormattedPrice = $response['Items']['Item']['ItemAttributes']['ListPrice']['FormattedPrice'];
	
	$lowestNewPriceCurrencyCode = $response['Items']['Item']['OfferSummary']['LowestNewPrice']['CurrencyCode'];
	$lowestNewPriceFormattedPrice = $response['Items']['Item']['OfferSummary']['LowestNewPrice']['FormattedPrice'];
	
	$ean = $response['Items']['Item']['ItemAttributes']['EAN'];
	$salesRank = $response['Items']['Item']['SalesRank'];
	
	
	$data = array();
	$data['form'] = 'amazon_products';
	$data['select'] = '*';
	$data['where'] = "
		EAN = " . $ean;
	$resultAmazonProduct = SQLSelect($data['form'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);	
	
	$data = array();
	$data['form'] = 'amazon_asin_to_ean';
	$data['select'] = '*';
	$data['where'] = "
		ean = " . $ean;
	$resultAsin = SQLSelect($data['form'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
	if (count($resultAsin) == 0) 
	{	
		$field = array(
			'table' => 'amazon_asin_to_ean'
		);
		if ($ean != null) {
			$data = array();
			$data['accountsite_id'] = $amazonAccountSites['id_accountsite'];
			$data['amazonProductId'] = $resultAmazonProduct['id_product'];
			$data['asin'] = $post['convertASIN'];
			$data['ean'] = $ean;
			$data['ListPriceCurrencyCode'] = $listPriceCurrencyCode;
			$data['ListPriceFormattedPrice'] = $listPriceFormattedPrice;
			$data['LowestNewPriceCurrencyCode'] = $lowestNewPriceCurrencyCode;
			$data['LowestNewPriceFormattedPrice'] = $lowestNewPriceFormattedPrice;
			$data['SalesRank'] = $salesRank;
			$data['firstmod'] = time();
			$data['lastmod'] = time();
			SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
		}
	} else {
		$data = array();
		$data['amazonProductId'] = $resultAmazonProduct['id_product'];
		$data['ListPriceCurrencyCode'] = $listPriceCurrencyCode;
		$data['ListPriceFormattedPrice'] = $listPriceFormattedPrice;
		$data['LowestNewPriceCurrencyCode'] = $lowestNewPriceCurrencyCode;
		$data['LowestNewPriceFormattedPrice'] = $lowestNewPriceFormattedPrice;
		$data['SalesRank'] = $salesRank;
		$data['lastmod'] = time();
		$addWhere = "
			ean = " . $ean;
		SQLUpdate('amazon_asin_to_ean', $data, $addWhere, 'shop', __FILE__, __LINE__);	
	}
	
	$xml = "\n" . "<AmazonAsinToEan>" . "\n";
	$xml.= '	<ean>' . $ean . '</ean>' . "\n";
	$xml.= '	<listPriceFormattedPrice>' . $listPriceFormattedPrice . '</listPriceFormattedPrice>' . "\n";
	$xml.= '	<SalesRank>' . $salesRank . '</SalesRank>' . "\n";
	$xml.= '</AmazonAsinToEan>'. "\n";
	echo $xml;
}