<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Services for Product Tasks
 *	- for all system intern products tasks
 *
 * @params
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

//	keep post submit#
$post = $_POST;

//	get amazon accountsites for amazon marketplaces by accountssites id
$amazonAccountsSites = getAmazonAccountsitesByAccountId($post);

if ($post['submitType'] == 'taskProductBundlePriceUpdate') 
{
	foreach($amazonAccountsSites as $amazonAccountsSite)
	{
		$data = array();
		$data['from'] = 'amazon_products';
		$data['select'] = '*';
		$data['where'] = "
			bundle = 1
			AND accountsite_id = " .  $amazonAccountsSite['id_accountsite'];
		$data['orderBy'] = "lastmod DESC";
		$amazonProductsResults = SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, $post['limit'], 'shop',  __FILE__, __LINE__);
		if (count($amazonProductsResults) > 0) 
		{
			foreach($amazonProductsResults as $product)
			{
				$data = array();
				$data['from'] = 'amazon_products_bundles';
				$data['select'] = '*';
				$data['where'] = "
					product_id = " . $product['id_product'];
				$amazonProductsBundlesResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
				if (count($amazonProductsBundlesResults) > 0) 
				{
					$price = 0;
					$quantity = 30;
					$gart = 0;
					$articleId = 0;
					foreach($amazonProductsBundlesResults as $item)
					{
						$data = array();
						$data['from'] = 'amazon_products';
						$data['select'] = 'id_product, Quantity, SKU, EAN, GART, article_id';
						$data['where'] = "
							accountsite_id = " .  $amazonAccountsSite['id_accountsite'] . "
							AND SKU = '" . $item['SellerSKU'] . "'";
						$amazonProduct = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
						
						//	quantity update
						if ($amazonProduct['Quantity'] < $quantity) 
						{
							$quantity = $amazonProduct['Quantity'];
						}
						if ($product['EAN'] == $amazonProduct['EAN']) 
						{
							$gart = $amazonProduct['GART'];
							$articleId = $amazonProduct['article_id'];
						}
						
						//	price update
						if ($item['QuantityOrdered'] > 1) 
						{
							$price+= $item['ItemPriceAmount'] * $item['QuantityOrdered'];
						} else {
							$price+= $item['ItemPriceAmount'];						
						}
					}
					$data = array();
					$data['GART'] = $gart;
					$data['article_id'] = $articleId;
					$data['StandardPrice'] = $price;
					$data['Quantity'] = $quantity;
					$data['submitedQuantity'] = 0;
					$data['submitedPrice'] = 0;
					$data['submitedProduct'] = 0;
					$data['lastmod'] = time();
					$data['lastpriceupdate'] = time();
					$data['lastmod_user'] = getAmazonSessionUserId();					
					$addWhere = "
						id_product = " . $item['product_id'];
					SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
					$xml.= '<productID>' . $item['product_id'] . '</productID>' . "\n";			
				}		
			}
		}
	}
}

if ($post['action'] == 'taskAsin') 
{
	$countUpdate = 0;   
	$data = array();
	$data['from'] = 'amazon_products';
	$data['select'] = 'id_product, ASIN, SKU, accountsite_id, importTemp, lastmod';
	$data['where'] = "
		accountsite_id = 1
		AND ASIN != ''
		AND importTemp = 0";
	$data['orderBy'] = 'lastmod DESC';
	$amazonProductsResults = SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, $post['limit'], 'shop',  __FILE__, __LINE__);
	if (count($amazonProductsResults) > 0) 
	{
		foreach($amazonProductsResults as $product)
		{
			$data = array();
			$data['ASIN'] = $product['ASIN'];
			$addWhere = "
				SKU = '" . $product['SKU'] . "'
				AND accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "'";
			$result = SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
			
			//	set import temp status
			$data = array();
			$data['importTemp'] = 1;
			$addWhere = "
				id_product = '" . $product['id_product'] . "'
				AND accountsite_id = '1'";
			$result = SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);			
			$countUpdate++;
		}
		$xml = "\n" . "<AmazonProductAsin>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= '	<update>Update Asin: ' . $countUpdate . '</update>' . "\n";
		$xml.= '</AmazonProductAsin>'. "\n";
		echo $xml;		
	} else {
		$xml = "\n" . "<AmazonProductAsin>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= '	<update>no products found</update>' . "\n";
		$xml.= '</AmazonProductAsin>'. "\n";
		echo $xml;	
	}
}