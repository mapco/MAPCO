<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for shop items quantities
 *	-
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');

$starttime = time() + microtime();

//	keep post submit
$post = $_POST;

	//	set alternative limit value
	if (isset($post['limit']) && $post['limit'] == 0) 
	{
		$post['limit'] = 50000;
	}
	
	//	reset shop items quantity import
	setShopItemsQuantityImport();
	
	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);

	//	finds shop items for quantity update
	$shopItemsResults = findsShopItemsForQuantityUpdate($post);
	
	//	finds amazon products for quantity update
	$amazonProductsResults = findsAmazonProductsForQuantityUpdate($amazonAccountsSites['id_accountsite']);
	$amazonProductsList = array();
	$countUpdateQuantity = 0;
	if (count($amazonProductsResults) > 0) 
	{
		foreach($amazonProductsResults as $amazonProduct)
		{	
			$amazonProductsList[$amazonProduct['SKU']] =  $amazonProduct;
		}
	}
	
	if (count($shopItemsResults) > 0) 
	{
		//	update quantitiy
		$updateQuantity = array();
		$updateAmazonProducts = array();
		foreach($shopItemsResults as $item)
		{	
			if (isset($amazonProductsList[$item['MPN']])) 
			{
				$updateAmazonProducts[$item['MPN']]['quantity'] = getAmazonProductsQuantityByArtNr($amazonProductsList[$item['MPN']]);
				$updateAmazonProducts[$item['MPN']]['id_product'] = $amazonProductsList[$item['MPN']]['id_product'];
					
				//	count quantity updates
				$countUpdateQuantity++;					
			}		
			$updateShopItems[] = $item["id_item"];
		}

		//	update amazon products quantities from the shop items
		updateAmazonProductsQuantities($updateAmazonProducts);
		
		//	set quantity import
		if (sizeof($updateShopItems) > 0) 
		{	
			$data = array();
			$data['QuantityImport'] = 1;
			$addWhere = "
				id_item IN (" . implode(", ", $updateShopItems) . ")
			";		
			SQLUpdate('shop_items', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}		
	}
	$xml = "\n" . "<AmazonShopItemsQuantities>" . "\n";
	$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
	$xml.= $xmlNextCall;
	$xml.= '	<update>Update Quantity: ' . $countUpdateQuantity . '</update>' . "\n";
	$xml.= '</AmazonShopItemsQuantities>'. "\n";
	echo $xml;
