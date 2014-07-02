<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Quantity Functions
 *
*******************************************************************************/

/**
 * Finds shop items for quantity update
 *
 * @param $post
 * @return array|null
 */
function findsShopItemsForQuantityUpdate($post)
{
	$data['from'] = 'shop_items';
	$data['select'] = 'id_item, active, MPN, GART, QuantityImport, lastmod';
	$data['where'] = "
		active = 1
		AND QuantityImport = 0
	";
	$data['orderBy'] = 'lastmod DESC';
	return SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, $post['limit'], 'shop',  __FILE__, __LINE__);
}

/**
 * Finds amazon products for quantity update
 *
 * @param $amazonAccountsSitesId
 * @return array|null
 */
function findsAmazonProductsForQuantityUpdate($amazonAccountsSitesId)
{
	$data['from'] = 'amazon_products';
	$data['select'] = 'id_product, accountsite_id, SKU, lastquantityupdate';
	$data['where'] = "
		accountsite_id = '" . $amazonAccountsSitesId . "'
	";
	return SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
}

/**
 * Update amazon products quantities
 *
 * @param $items
 * @param $prices
 */
function updateAmazonProductsQuantities($items)
{
	if (sizeof($items) > 0) {
		$data['lastmod'] = time();
		$data['lastmod_user'] = getAmazonSessionUserId();
		$data['lastquantityupdate'] = time();
		$data['submitedProduct'] = 0;
		$data['submitedQuantity'] = 0;
		$data['upload'] = 1;
		$caseStandardPrice = "Quantity = CASE";
		foreach($items as $key => $item)
		{
			$caseStandardPrice.= "\n WHEN id_product = " . $items[$key]['id_product'] . " THEN " . $items[$key]['quantity'];
			$productsIds[] = $items[$key]['id_product'];
		}
		$caseStandardPrice.= "
			END
			WHERE id_product IN (" . implode(", ", $productsIds) . ")
		";
		$addWhere	 = $caseStandardPrice;
		SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
	}
}

/**
 * Returns an amazon product quantity
 *
 * @param $item array
 * @return int
 *	@info: change $item['MPN'] in $item['SKU'] by call AmazonShopItemsQuantitiesGet
 */
function getAmazonProductsQuantityByArtNr($item)
{
	if (isset($item["SKU"]) && $item["SKU"] != null) 
	{
		$MPN = $item["SKU"];	
	}	
	if (isset($item["SKU"]) && $item["SKU"] != null) 
	{
		$MPN = $item["SKU"];	
	}
	if (isset($item["MPN"]) && $item["MPN"] != null) 
	{
		$MPN = $item["MPN"];	
	}
	
	$from = 'lager';
	$select = '*';
	$addWhere = "ArtNr = '" . $MPN . "'";
	$lagerResult = SQLSelect($from, $select, $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
	if (count($lagerResult) > 0) {
		$Quantity = $lagerResult["ISTBESTAND"] + $lagerResult["MOCOMBESTAND"] + $lagerResult["ONLINEBESTAND"] + $lagerResult["AMAZONBESTAND"];
		if ($Quantity > 30) {
			$Quantity = 30;
		}
	} else {
		$Quantity = 0;
	}
	return $Quantity;
}

/**
 * @param $amazonProduct
 * @param $item
 * @return int
 */
function getAmazonProductBundleQuantityByArtNr($amazonProduct, $item)
{
	// 	update quantity for each bundle item
	$amazonProductBundleItems = findAmazonProductBundleItems($amazonProduct);
	if (count($amazonProductBundleItems) > 0) {
		foreach($amazonProductBundleItems as $amazonProductBundleItem)
		{
			$lagerQuantity = getAmazonProductsQuantityByArtNr($amazonProductBundleItem['SellerSKU']);
			$data['Quantity'] = $lagerQuantity;
			$addWhere = "
				id_bundle = '" . $amazonProductBundleItem['id_bundle'] . "'
			";
			SQLUpdate('amazon_products_bundles', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}
	}

	//	find item with lowest quantity value
	$criteria['orderBy'] = 'Quantity ASC';
	$amazonProductBundleItemsQuantities = findAmazonProductBundleItems($amazonProduct, $criteria);
	if (count($amazonProductBundleItemsQuantities) > 0) {
		$quantity = $amazonProductBundleItemsQuantities[0]['Quantity'];
	} else {
		$quantity = 0;
	}
	return $quantity;
}