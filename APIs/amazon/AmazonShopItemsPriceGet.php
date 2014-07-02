<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for shop items
 *	- update a mapco amazon product whit prices from shop items
 * @params
 * -
*******************************************************************************/
$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');

//	keep post submit
$post = $_POST;

	//	set alternative limit value
	if (isset($post['limit']) && $post['limit'] == 0) {
		$post['limit'] = 50000;
	}
	
	//	reset shop items standard price import
	setShopItemsStandardPriceImport();
	
	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);

	//	finds shop items for price update
	$shopItemsResults = findsShopItemsForPriceUpdate($post);
	
	//	finds amazon products for price update
	$amazonProductsResults = findsAmazonProductsForPriceUpdate($amazonAccountsSites['id_accountsite']);
	$amazonProductsList = array();
	$countUpdatePrices = 0;
	foreach($amazonProductsResults as $amazonProduct)
	{	
		$amazonProductsList[$amazonProduct['SKU']] =  $amazonProduct;
	}	
	
	//	finds prpos pricelist by amazon acountsite pricelist
	$prices = findsPrPosPriceList($amazonAccountsSites["pricelist"]);
	$priceList = array();
	foreach($prices as $price)
	{	
		$priceList[$price['ARTNR']] =  $price;
	}

	if (count($shopItemsResults) > 0) 
	{	
		//	update prices
		$updateStandardPrice = array();
		$updateAmazonProducts = array();
		foreach($shopItemsResults as $item)
		{	
			if (isset($priceList[$item['MPN']]) && isset($amazonProductsList[$item['MPN']])) 
			{
				$updateAmazonProducts[$item['MPN']]['price'] = getPrPosPriceByArtNr($priceList[$item['MPN']], $item);
				$updateAmazonProducts[$item['MPN']]['id_product'] = $amazonProductsList[$item['MPN']]['id_product'];
					
				//	count price updates
				$countUpdatePrices++;					
			}
			//	save shop items ids for updating	
			$updateShopItems[] = $item["id_item"];
		}

		//	update amazon products prices from the shop items
		updateAmazonProductsPrices($updateAmazonProducts);
		
		//	update offering condition note
		$countUpdateOfferingConditionNote = 0;
		foreach($shopItemsResults as $item)
		{	
			if (isset($priceList[$item['MPN']]) && isset($amazonProductsList[$item['MPN']])) 
			{
				$updateOfferingConditionNote[$item['MPN']]['OfferingConditionNote'] = getOfferingConditionNote($item, $amazonAccountsSites['language_id']);
				$updateOfferingConditionNote[$item['MPN']]['id_product'] = $amazonProductsList[$item['MPN']]['id_product'];
				
				//	count offering condition note updates
				$countUpdateOfferingConditionNote++;					
			}
			$updateShopItems[] = $item["id_item"];
		}
		
		//	update amazon products offering condition notes (GART 82 or others)
		updateAmazonProductsOfferingConditionNotes($updateOfferingConditionNote);	
		
		//	set standard price import
		if (sizeof($updateShopItems) > 0) 
		{	
			$data = array();
			$data['StandardPriceImport'] = 1;
			$addWhere = "
				id_item IN (" . implode(", ", $updateShopItems) . ")
			";		
			SQLUpdate('shop_items', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}
	}
	$xml = "\n" . "<AmazonShopItemsPrice>" . "\n";
	$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
	$xml.= $xmlNextCall;
	$xml.= '	<update>Update Prices: ' . $countUpdatePrices . '</update>' . "\n";
	$xml.= '	<update>Update Offering Condition Notes: ' . $countUpdateOfferingConditionNote . '</update>' . "\n";
	$xml.= '</AmazonShopItemsPrice>'. "\n";
	echo $xml;
