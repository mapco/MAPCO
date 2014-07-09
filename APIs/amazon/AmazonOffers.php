<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for offer get
 *
 *	@params
 *	- $submitTypes
 *	-- criticalPrice, update, importShopPriceResearch
 *
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');

//	keep post submit
$post = $_POST;

	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);

    /**
     *	Detect critical prices
     *
     */
	if ($post['action'] == 'criticalPrice') 
	{	
		$from = 'amazon_products';
		$select = '*';
		$addWhere = "
			accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "'
			AND StandardPrice > 0
			AND TopPrice > 0";
		$orderBy = 'id_product DESC';
		$amazonProductsResults = SQLSelect($from, $select, $addWhere, $orderBy, 0, 0, 'shop',  __FILE__, __LINE__);
		if (count($amazonProductsResults) > 0) 
		{
			$countUpdate = 0;
			foreach($amazonProductsResults as $amazonProductsResult)
			{
				//$amazonProductsResult['StandardPrice'] > $amazonProductsResult['TopPrice'] AND $amazonProductsResult['TopPrice'] > 0)
				$data = array();
				if (true == getBestOfferPriceForAmazon($amazonProductsResult['StandardPrice'], $amazonProductsResult['TopPrice'])) 
				{
					$data['CriticalPrice'] = 1;
					$data['submitedProduct'] = 0;
					$data['upload'] = 1;
					$data['StandardPriceSuggestion']= ($amazonProductsResult['TopPrice'] - 1.20); 
					$data['lastpriceupdate'] = time();
				} else {
					$data['CriticalPrice'] = 0;			
				}
				$addWhere = "
					WHERE id_product = '" . $amazonProductsResult['id_product'] . "'";
				q_update("amazon_products", $data, $addWhere, $dbshop, __FILE__, __LINE__);
				$countUpdate++;				
			}
		}
		echo '<amazonOfferCriticalPrice>' . "\n";
		echo '	<update>Update for ' . $countUpdate .  ' critical prices</update>' . "\n";
		echo '</amazonOfferCriticalPrice>' . "\n";		
	}

	/**
	 *	Update offer top price into the amzon products table
	 *
	 */
	if ($post['action'] == 'update') 
	{
		$from = 'amazon_offers';
		$select = '*';
		$addWhere = "
			MarketplaceId = '" . $amazonAccountsSites['MarketplaceID'] . "'
			AND importTopPrice = 0";
		$orderBy = 'PriceListingPriceAmount DESC';
		$amazonOffersResults = SQLSelect($from, $select, $addWhere, $orderBy, 0, 0, 'shop',  __FILE__, __LINE__);
		$countInsert = 0;
		$countUpdate = 0;
		
		if (count($amazonOffersResults) > 0) 
		{ 
			foreach($amazonOffersResults as $amazonOffersResult)
			{
				$from = 'amazon_products';
				$select = 'id_product, ASIN, accountsite_id, TopPrice';
				$addWhere = "
					ASIN = '" . $amazonOffersResult['ASIN'] . "'
					AND accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "'";
				$amazonProductsResult = SQLSelect($from, $select, $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
				if (count($amazonProductsResult) > 0) 
				{
					if ($amazonProductsResult['TopPrice'] > 0 && $amazonProductsResult['TopPrice'] > $amazonOffersResult['PriceListingPriceAmount']) 
					{
						$data = array();
						$data['TopPrice'] = $amazonOffersResult['PriceListingPriceAmount'];
						$addWhere = "
							WHERE id_product = '" . $amazonProductsResult['id_product'] . "'";
						q_update("amazon_products", $data, $addWhere, $dbshop, __FILE__, __LINE__);
						$countUpdate++;
					} else {
						if ($amazonProductsResult['TopPrice'] == 0) 
						{
							$data = array();
							$data['TopPrice'] = $amazonOffersResult['PriceListingPriceAmount'];
							$addWhere = "
								WHERE id_product = '" . $amazonProductsResult['id_product'] . "'";
							q_update("amazon_products", $data, $addWhere, $dbshop, __FILE__, __LINE__);
							$countInsert++;
						}
					}
				}
				$data = array();
				$data['importTopPrice'] = 1;
				$addWhere = "
					WHERE id_offer = '" . $amazonOffersResult['id_offer'] . "'";
				q_update("amazon_offers", $data, $addWhere, $dbshop, __FILE__, __LINE__);
			}
		}
		echo '<amazonOfferTopPrice>' . "\n";
		echo '	<insert>Insert for ' . $countInsert .  ' top prices</insert>' . "\n";
		echo '	<update>Update for ' . $countUpdate .  ' top prices</update>' . "\n";
		echo '</amazonOfferTopPrice>' . "\n";
	}
	
	
	/**
	 *	insert price research into the shop price research table
	 *	and create a price suggestion
	 */
	if ($post['action'] == 'importShopPriceResearch') 
	{
		$from = 'amazon_offers';
		$select = '*';
		$addWhere = "
			MarketplaceId = '" . $amazonAccountsSites['MarketplaceID'] . "'
			AND importShopPriceResearch = 0";
		$orderBy = 'PriceListingPriceAmount DESC';
		$amazonOffersResults = SQLSelect($from, $select, $addWhere, $orderBy, 0, $post['limit'], 'shop',  __FILE__, __LINE__);
		$countPriceResearchInsert = 0;

		if (count($amazonOffersResults) > 0) 
		{ 
			foreach($amazonOffersResults as $amazonOffersResult)
			{
				$amazonProduct = findAmazonProductsByAsinAndByAccountsite($amazonOffersResult, $amazonAccountsSites);
				if ($amazonProduct['StandardPrice'] != 0 && $amazonProduct['StandardPrice'] > $amazonOffersResult['PriceListingPriceAmount']) {
	
					$itemID = getItemIdByAmazonAsin($amazonOffersResult['ASIN']);
					$data = array();
					$data['MarketplaceId'] = $amazonOffersResult['MarketplaceId'];
					$data['seller'] = 'Amazon Mitbewerber';
					$data['shipping'] = $amazonOffersResult['ShippingAmount'];
					$data['price'] = $amazonOffersResult['PriceListingPriceAmount'];
					$data['pricelist'] = $amazonAccountsSites['pricelist'];
					$data['item_id'] = $itemID;
					$data['EbayID'] = $amazonOffersResult['ASIN'];
					$data['expires'] = strtotime( "+3 month", strtotime("now"));
					
					$data['firstmod'] = time();
					$data['firstmod_user'] = 10;
					$data['lastmod'] = time();
					$data['lastmod_user'] = 10;
					q_insert('shop_price_research', $data, $dbshop, __FILE__, __LINE__);
					$countPriceResearchInsert++;
					
					//	create a price suggestion
					if ($amazonProduct['TopPrice'] > 0) 
					{
						$post_data = array();
						$post_data['API'] = "shop";
						$post_data['APIRequest'] = "PriceSuggestionAdd";
						$post_data['item_id'] = $itemID;
						$post_data['price'] = $amazonProduct['TopPrice'];
						$post_data['pricelist'] = $amazonAccountsSites['pricelist'];
						$resultPriceSuggestion.= soa2($post_data, __FILE__, __LINE__, 'xml');
					}
					
					$from = 'shop_lists_items';
					$select = 'id, list_id, item_id';
					$addWhere = "
						list_id = '3125' AND item_id = '" . $itemID . "'";
					$orderBy = 'PriceListingPriceAmount DESC';
					$shopListItemsResult = SQLSelect($from, $select, $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
					if (count($shopListItemsResult) == 0) 
					{
						$data = array();
						$data['list_id'] = '3125';
						$data['item_id'] = $itemID;
						
						$data['firstmod'] = time();
						$data['firstmod_user'] = 10;
						$data['lastmod'] = time();
						$data['lastmod_user'] = 10;
						q_insert('shop_lists_items', $data, $dbshop, __FILE__, __LINE__);
					}
					// set import status
					$data = array();
					$data['importShopPriceResearch'] = 1;
					$addWhere = "
						WHERE id_offer = '" . $amazonOffersResult['id_offer'] . "'";
					q_update("amazon_offers", $data, $addWhere, $dbshop, __FILE__, __LINE__);
				}
			}
		}
		echo '<amazonOfferPriceResearch>' . "\n";
		echo '	<insertPriceResearch>Insert for ' . $countPriceResearchInsert .  ' prices</insertPriceResearch>' . "\n";
		echo '	<resultPriceSuggestion>' . $resultPriceSuggestion .  ' </resultPriceSuggestion>' . "\n";
		echo '</amazonOfferPriceResearch>' . "\n";		
	}