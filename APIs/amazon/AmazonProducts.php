<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for product get
 *	- get the product from the amazon product table
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

//	keep post submit
$post = $_POST;

	if ($post['action'] == 'matchInventory')
	{
		//	get amazon accountsites for amazon marketplaces by accountssites id
		$amazonAccountsSites = getAmazonAccountsites($post);		
		$result = matchAmazonProductWithAmazonInventory($amazonAccountsSites);
		if ($result == true) 
		{
			matchAmazonProductsPricesWithAmazonInventory($amazonAccountsSites);
		}
	}

	/**
	 *	Search Amazon Products (SKU)
	 *
	 */
	if ($post['action'] == 'searchAmazonProducts') 
	{
		//	get amazon accountsites for amazon marketplaces by accountssites id
		$amazonAccountsSites = getAmazonAccountsites($post);		
		
		$from = 'amazon_products';
		$select = '*';
		$addWhere = "
			accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "'";
		if (isset($post['searchSKU']) && !empty($post['searchSKU'])) 
		{
			$addWhere.= "
				AND SKU = '" .  $post['searchSKU'] . "'";
		}
		if (isset($post['searchASIN']) && !empty($post['searchASIN'])) 
		{
			$addWhere.= "
				AND ASIN = '" .  $post['searchASIN'] . "'";
		}		
		$orderBy = 'accountsite_id ASC';
		$amazonProducts = SQLSelect($from, $select, $addWhere, 0, 0, 0, 'shop',  __FILE__, __LINE__);
		foreach ($amazonProducts as $amazonProduct)
		{
			$xml.= getXmlAmazonProduct($amazonProduct, $amazonAccountsSites);
		}
		echo getXmlAmazonAccountsites($amazonAccountsSites) . $xml;
	}

	/**
	 *	Amazon Products List
	 *
	 */
	if ($post['action'] == 'listAmazonProducts') 
	{
		// we can use a special post var:  set
		if ($post['set'] == 'StandardPriceImport') 
		{
			// set StandardPriceImport => 0
			setShopItemsStandardPriceImport();	
		}
		if ($post['set'] == 'QuantityImport') 
		{
			// set StandardPriceImport => 0
			setShopItemsQuantityImport();	
		}
		if ($post['set'] == 'ImageImport') 
		{
			// set ImageImport => 0
			setShopItemsImageImport();	
		}
		if ($post['set'] == 'importTopPrice') 
		{
			// set TopPriceImport => 0
			setamazonOffersTopPriceImport();	
		}
		//	get amazon accountsites for amazon marketplaces by accountssites id
		$amazonAccountsSites = getAmazonAccountsites($post);	

		//	get amazon products stats by accountsite id
		$xmlStats = '<countAmazonProducts>'. "\n" . getAmazonProductsStatsByAccountSite($amazonAccountsSites) . "\n" . '</countAmazonProducts>'. "\n";

		//	get amazon products by account id and accountsite id
		$from = 'amazon_products';
		$select = '*';
		$addWhere = "accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "'";
		if ($post['addWhere'] == 'asin') 
		{
			$addWhere.= " AND ASIN = ''";	
		}
		if ($post['addWhere'] == 'price') 
		{
			$addWhere.= " AND StandardPrice = ''";	
		}
		if ($post['addWhere'] == 'criticalPrice') 
		{
			$addWhere.= " AND CriticalPrice = '1'";	
		}				

		if (isset($post['orderBy']) && !empty($post['orderBy'])) 
		{
			if ($post['orderBy'] == 'StandardPriceDown') 
			{
				$orderBy = 'StandardPrice DESC';
			} elseif ($post['orderBy'] == 'StandardPriceUp') 
			{
				$orderBy = 'StandardPrice ASC';
			} else {				
				$orderBy = $post['orderBy'];
			}
			
		} else {
			$orderBy = 'lastmod DESC';
		}
		$amazonProducts = SQLSelect($from, $select, $addWhere, $orderBy, 0, $post['limit'], 'shop',  __FILE__, __LINE__);
		if (count($amazonProducts) > 0) 
		{
			$xml = '';
			foreach($amazonProducts as $amazonProduct)
			{
				$xml.= getXmlAmazonProduct($amazonProduct, $amazonAccountsSites);
			}
		}
		echo $xmlStats . getXmlAmazonAccountsites($amazonAccountsSites) . $xml . $offer;
	}
