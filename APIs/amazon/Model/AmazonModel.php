<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Model (not relay ;-)
 *
*******************************************************************************/

include('AmazonAccount.php');
include('AmazonAccountSite.php');
include('AmazonPrice.php');
include('AmazonQuantity.php');

/**
 * Amazon  MarketplaceWebService Submit
 *
 * @param $post
 * @param null $host
 * @return mixed|string
 */
function MarketplaceWebServiceSubmit($post, $host = null)
{
	$APP_USERAGENT = "Mapco API - Amazon/2.0 (Language=PHP/" . phpversion() . "; Platform=Debian 3.2.54-2 x86_64)";

	if ($host != null) {
		$host = $host;
	} else {
		$host = "fix the call"; // add the $MARKETPLACE_HOST on the right place
	}

	if (!empty($post['method'])) {
		$method= $post['method'];
	} else {
		$method = "POST";
	}

	// for a special type define /folder/
	if (!empty($post['type'])) {
		$uri = "/" . $post['type'];
	} else {
		$uri = "/";
	}

	// Clean up and sort
	$url = explode('&', $post['url']);

	foreach ($url as $key => $value)
	{
		$t = explode("=",$value);
		$params[$t[0]] = $t[1];
	}
	unset($url);

	ksort($params);

	foreach ($params as $param=>$value)
	{
		$param = str_replace("%7E", "~", rawurlencode($param));
		$value = str_replace("%7E", "~", rawurlencode($value));
		$canonicalized_query[] = $param . "=" . $value;
	}

	$canonicalized_query = implode("&", $canonicalized_query);

	// create the string to sign
	$string_to_sign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;

	// calculate HMAC with SHA256 and base64-encoding
	$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $post['SecretKey'], true));

	// encode the signature for the request
	$signature = str_replace("%7E", "~", rawurlencode($signature));

	// create request
	$requestUrl = "https://" . $host . $uri . "?" . $canonicalized_query . "&Signature=" . $signature;
	$ch = curl_init();

	//	create an acceptable User-Agent header
	curl_setopt($ch, CURLOPT_USERAGENT, $APP_USERAGENT);

	if (!empty($post['data']))
	{
		$feedHandle = fopen('php://temp', 'w');
		fwrite($feedHandle, $post['data']);
		rewind($feedHandle);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=iso-8859-1", "Content-MD5:".base64_encode(md5(stream_get_contents($feedHandle), true)) ));
		rewind($feedHandle);
		curl_setopt($ch, CURLOPT_POSTFIELDS, stream_get_contents($feedHandle));
	} else {
		curl_setopt($ch, CURLOPT_USERAGENT, $APP_USERAGENT);
	}

	curl_setopt($ch, CURLOPT_URL, $requestUrl);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);

	//	if empty response, returns a xml string
	//	or returns a clean response only
	if ($response == null)
	{
		return '<Error>no response after submit</Error>';
	} else {
		return $response;
	}
}

/**
 * Finds all amazon products for submit ready by accountsite id
 *
 * @param $accountsiteId
 * @return int
 */
function findsAmazonProductsForSubmitReadyByAccountsiteId($accountsiteId)
{
	global $dbshop;
	$amazonProductsQuery = "
		SELECT *
		FROM amazon_products
		WHERE accountsite_id = '" . $accountsiteId . "'
		AND Quantity > 0
		AND StandardPrice > 0
		AND submitedProduct = 0
		AND upload = 1
		AND EAN > 0";
	$amazonProductsResult = q($amazonProductsQuery, $dbshop, __FILE__, __LINE__);
	return mysqli_num_rows($amazonProductsResult);
}

/**
 * Find Amazon Product by SKU (MPN)
 *
 * @param $item
 * @param string $accountSite
 * @return array|null
 */
function findAmazonProductBySku($item, $accountSite = '1')
{
	$data['from'] = 'amazon_products';
	$data['select'] = 'id_product, accountsite_id, SKU, ImageLocation, ImageLocationThumbnail, lastimageupdate, lastpriceupdate';
	$data['where'] = "
		SKU = '" . $item["MPN"] . "'
		AND accountsite_id = '" . $accountSite . "'
	";
	return SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
}

/**
 * Match all amazon products prices with amazon inventory
 *
 * @param $accountSite
 */
function matchAmazonProductsPricesWithAmazonInventory($accountSite)
{
	$data = array();
	$data['from'] = 'amazon_products';
	$data['select'] = 'id_product, accountsite_id, SKU, StandardPrice';
	$data['where'] = "
		accountsite_id = '" . $accountSite['id_accountsite'] . "'
	";
	$amazonProducts = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);

	$data = array();
	$data['from'] = 'amazon_inventory';
	$data['select'] = '*';
	$data['where'] = "
		accountsite_id = " . $accountSite['id_accountsite'] . "
		AND product_id != 0
	";
	$amazonInventory = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
	$newItem = array();
	if (count($amazonInventory) > 0)
	{
		foreach($amazonInventory as $item)
		{
			$newItem[$item['product_id']]['price'] = $item['price'];
			$newItem[$item['product_id']]['id_inventory'] = $item['id_inventory'];
		}
	}

	if (count($amazonProducts) > 0) {
		$countUpdate = 0;
		foreach($amazonProducts as $amazonProduct)
		{
			if (!empty($newItem[$amazonProduct['id_product']]['id_inventory']) && $newItem[$amazonProduct['id_product']]['price'] != $amazonProduct['StandardPrice']) {
				$data = array();
				$data['amazonStandardPrice'] = $amazonProduct['StandardPrice'];
				$addWhere = "
					id_inventory = '" . $newItem[$amazonProduct['id_product']]['id_inventory'] . "'
					AND accountsite_id = " . $accountSite['id_accountsite'];
				SQLUpdate('amazon_inventory', $data, $addWhere, 'shop', __FILE__, __LINE__);

				$data = array();
				$data['submitedPrice'] = 0;
				$data['submitedProduct'] = 0;
				$data['StandardPriceOnline'] = $newItem[$amazonProduct['id_product']]['price'];
				$addWhere = "
					id_product = '" . $amazonProduct['id_product'] . "'
					AND accountsite_id = " . $accountSite['id_accountsite'];
				SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
				$countUpdate++;
			}
		}
	}
	$data = array();
	$data['differentPrices'] = $countUpdate;
	$addWhere = "
		id_accountsite = " . $accountSite['id_accountsite'];
	SQLUpdate('amazon_accounts_sites', $data, $addWhere, 'shop', __FILE__, __LINE__);
	echo '<inventoryPriceUpdate>' . $accountSite['name'] . ' Price different: ' . $countUpdate . '</inventoryPriceUpdate>' . "\n";
}

/**
 * Match Amazon Inventory with Amazon Products
 *
 * @param $accountSite
 * @return bool
 */
function matchAmazonProductWithAmazonInventory($accountSite)
{
	$data = array();
	$data['from'] = 'amazon_products';
	$data['select'] = 'id_product, accountsite_id, SKU';
	$data['where'] = "
		accountsite_id = '" . $accountSite['id_accountsite'] . "'
	";
	$amazonProducts = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
	$SKU2product_id = array();
	foreach($amazonProducts as $amazonProduct)
	{
		$SKU2product_id[$amazonProduct["SKU"]] = $amazonProduct["id_product"];
	}

	$update_quantity = array();
	$update_product_id = array();
	$update_product_id2 = array();
	$data = array();
	$data['from'] = 'amazon_inventory';
	$data['select'] = '*';
	$addWhere = "
		accountsite_id = " . $accountSite['id_accountsite'] . "
		AND product_id = 0
		AND updateProductId = 0
	";

	$countUpdate = 0;
	$amazonInventory = SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, 50000, 'shop',  __FILE__, __LINE__);
	if (count($amazonInventory) > 0)
	{
		foreach($amazonInventory as $item)
		{
			if(!isset($SKU2product_id[$item["sku"]])) {
			  $update_quantity[] = $item["id_inventory"];
			  $countUpdate++;
			} else {
				$update_product_id[] = $SKU2product_id[$item["sku"]];
				$update_product_id2[] = $item["id_inventory"];
				$countUpdate++;
			}
		}

		//	update
		if ( sizeof($update_quantity) > 0 ) {
			$data = array();
			$data['updateProductId'] = 1;
			$addWhere = "
				id_inventory IN (" . implode(", ", $update_quantity) . ")
			";
			SQLUpdate('amazon_inventory', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}

		//update existing products
		global $dbshop;
		if (sizeof($update_product_id) > 0) {
			$sql = "
				UPDATE amazon_inventory
				SET updateProductId = 1,
				product_id = CASE";
				for($i = 0; $i < sizeof($update_product_id); $i++)
				{
					$sql.= "\n WHEN id_inventory = " . $update_product_id2[$i] . " THEN " . $update_product_id[$i];
				}
			$sql.= " END";
			$sql.= " WHERE
				accountsite_id = " . $accountSite['id_accountsite'] . "
				AND id_inventory IN (" . implode(", ", $update_product_id2) . ");
			";
			q($sql, $dbshop, __FILE__, __LINE__);
		}
	}
	echo '<inventoryProductIDUpdate>' . $accountSite['name'] . ' ProductID Update: ' . $countUpdate . '</inventoryProductIDUpdate>' . "\n";

	//
	return true;
}

/**
 * @param $amazonProduct
 * @param array $criteria
 * @return array|null
 */
function findAmazonProductBundleItems($amazonProduct, $criteria = array())
{
	$from = 'amazon_products_bundles';
	$select = '*';
	$addWhere = "
		product_id = '" . $amazonProduct['product_id'] . "'";
	if (isset($criteria['orderBy']) && !empty($criteria['orderBy'])) {
		$orderBy = $criteria['orderBy'];
	}
	return SQLSelect($from, $select, $addWhere, $orderBy, 0, 0, 'shop',  __FILE__, __LINE__);
}

/**
 * Returns an array with amazon product by asin and by accountsite
 *
 * @param $amazonProduct
 * @param $amazonAccountsSites
 * @return array|null
 */
function findAmazonProductsByAsinAndByAccountsite($amazonProduct, $amazonAccountsSites)
{
	$from = 'amazon_products';
	$select = '*';
	$addWhere = "
		ASIN = '" . $amazonProduct['ASIN'] . "'
		AND accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "'";
	return SQLSelect($from, $select, $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
}

/**
 * Returns an amazon products stats by accountsite
 *
 * @param $accountsite
 * @return string
 */
function getAmazonProductsStatsByAccountSite($accountsite)
{
	$from = 'amazon_products';

	//	count all active amazon products
	$addWhere = "
		accountsite_id = " . $accountsite['id_accountsite'] . "
		AND EAN > 0
		AND StandardPrice > 0
		AND Quantity > 0
	";
	$xml = '	<activeProducts>' . SQLCount($from, $addWhere, 'shop', __FILE__, __LINE__) . '</activeProducts>' . "\n";

	//	count all amazon products without ASIN number by accountsite id
	$addWhere = "
		accountsite_id = " . $accountsite['id_accountsite'] . "
		AND ASIN = ''";
	$xml.= '	<emptyAsin>' . SQLCount($from, $addWhere, 'shop', __FILE__, __LINE__) . '</emptyAsin>' . "\n";

	//	count all amazon products with critical prices by accountsite id
	$xml.= '	<criticalPrices><![CDATA[' . getCriticalPricesByAccountsite($accountsite) . ']]></criticalPrices>';

	//	count all amazon products by accountsite id
	$addWhere = "
		accountsite_id = " . $accountsite['id_accountsite'] . "";
	$xml.= '	<allProducts>' . SQLCount($from, $addWhere, 'shop', __FILE__, __LINE__) . '</allProducts>' . "\n";

	//	count all amazon products without images by accountsite id
	$addWhere = "
		accountsite_id = " . $accountsite['id_accountsite'] . "
		AND ImageLocation = ''";
	$xml.= '	<emptyImage>' . SQLCount($from, $addWhere, 'shop', __FILE__, __LINE__) . '</emptyImage>' . "\n";

	//	count all amazon products without EAN number by accountsite id
	$addWhere = "
		accountsite_id = " . $accountsite['id_accountsite'] . "
		AND EAN = ''";
	$xml.= '	<emptyEAN>' . SQLCount($from, $addWhere, 'shop', __FILE__, __LINE__) . '</emptyEAN>' . "\n";

	//	count all amazon products without price by accountsite id
	$addWhere = "
		accountsite_id = " . $accountsite['id_accountsite'] . "
		AND StandardPrice = 0";
	$xml.= '	<emptyStandardPrice>' . SQLCount($from, $addWhere, 'shop', __FILE__, __LINE__) . '</emptyStandardPrice>' . "\n";

	//	count all amazon products for ready to update
	$addWhere = "
		accountsite_id = " . $accountsite['id_accountsite'] . "
		AND submitedProduct = 1
		AND EAN > 0
		AND StandardPrice > 0
		AND submitedPrice = 0";
	$xml.= '	<submitedStandardPrice>' . SQLCount($from, $addWhere, 'shop', __FILE__, __LINE__) . '</submitedStandardPrice>' . "\n";

	//	count all amazon products with empty quantities by accountsite id
	$addWhere = "
		accountsite_id = " . $accountsite['id_accountsite'] . "
		AND Quantity < 1";
	$xml.= '	<emptyQuantities>' . SQLCount($from, $addWhere, 'shop', __FILE__, __LINE__) . '</emptyQuantities>' . "\n";
	$addWhere = "
		accountsite_id = " . $accountsite['id_accountsite'] . "
		AND submitedProduct = 0
		AND EAN > 0
		AND Quantity > 0
		AND submitedQuantity = 0";
	$xml.= '	<submitedQuantities>' . SQLCount($from, $addWhere, 'shop', __FILE__, __LINE__) . '</submitedQuantities>' . "\n";

	$xml.= '	<submitReady>' . findsAmazonProductsForSubmitReadyByAccountsiteId($accountsite['id_accountsite']) . '</submitReady>' . "\n";
	return $xml;
}

/**
 * Finds amazon offers for marketplace and ASIN
 *
 * @param $amazonAccountsSites
 * @param $ASIN
 * @return string
 */
function findsAmazonOffersForMarketplaceAndAsin($amazonAccountsSites, $ASIN)
{
	$from = 'amazon_offers';
	$select = '*';
	$addWhere = "MarketplaceId = '" . $amazonAccountsSites['MarketplaceID'] . "' AND ASIN = '" . $ASIN . "'";
	$orderBy = 'PriceListingPriceAmount ASC';
	$results = SQLSelect($from, $select, $addWhere, $orderBy, 0, 1, 'shop',  __FILE__, __LINE__);
	if (count($results) > 0) {
        $prices = null;
        foreach($results as $result)
		{
			if ($result['PriceListingPriceAmount'] > 0) {
				$prices.= '<price>' . $result['PriceListingPriceAmount'] . '</price>' . "\n";
			}
		}
	} else {
		$prices = '<price>n/a</price>' . "\n";
	}
	return '<amazonOffers>' . "\n" . $prices . '</amazonOffers>' . "\n";
}

/**
 * Counts amazon order items by amazon order ID
 *
 * @param $amazonOrder
 * @return mixed
 */
function countAmazonOrderItemyByAmazonOrderId($amazonOrder)
{
    if (isset($amazonOrder["AmazonOrderId"]) && !empty($amazonOrder["AmazonOrderId"])) 
	{
		$data = array();
		$data['from'] = 'amazon_order_items';
        $data['where'] = "
			AmazonOrderId = '" . $amazonOrder["AmazonOrderId"] . "'";
        return SQLCount($data['from'], $data['where'], 'shop', __FILE__, __LINE__);        
    }
}

/**
 * Returns an itemID by amazon ASIN
 *
 * @param $asin
 * @return mixed
 */
function getItemIdByAmazonAsin($asin)
{
	$from = 'amazon_products';
	$select = 'id_product, item_id, ASIN';
	$addWhere = "ASIN = '" . $asin . "'";
	$amazonProductsResult = SQLSelect($from, $select, $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
	if (count($amazonProductsResult) > 0) 
	{
		return $amazonProductsResult['item_id'];
	}
}

/**
 * Insert amazon offers by amazon ASIN
 *
 * @param $MarketplaceId
 * @param $ASIN
 * @param $offer
 */
function addAmazonOfferByAsin($MarketplaceId, $ASIN, $offer)
{
	$field = array(
		'table' => 'amazon_offers',
	);
	$data= array();
	$data['MarketplaceId'] = $MarketplaceId;
	$data['ASIN'] = $ASIN;
	$data['SellerPositiveFeedbackRating'] = $offer['Qualifiers']['SellerPositiveFeedbackRating'];
	$data['SellerFeedbackCount'] = $offer['SellerFeedbackCount'];
	$data['PriceLandedPriceCurrencyCode'] = $offer['Price']['LandedPrice']['CurrencyCode'];
	$data['PriceLandedPriceAmount'] = $offer['Price']['LandedPrice']['Amount'];
	$data['PriceListingPriceCurrencyCode'] = $offer['Price']['ListingPrice']['CurrencyCode'];
	$data['PriceListingPriceAmount'] = $offer['Price']['ListingPrice']['Amount'];
	$data['ShippingCurrencyCode'] = $offer['Price']['Shipping']['CurrencyCode'];
	$data['ShippingAmount'] = $offer['Price']['Shipping']['Amount'];
	$data['MultipleOffersAtLowestPrice'] = $offer['MultipleOffersAtLowestPrice'];
	$data['firstmod'] = time();
	$data['lastmod'] = time();
	SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
}

/**
 * Update amazon product browse nodes
 *
 * @param $item
 * @param $accountSiteID
 * @return string
 */
function updateAmazonProductsBrowseNodes($item, $accountSiteID)
{
	$data['from'] = 'amazon_categories';
	$date['select'] = '*';
	$date['where'] = "
		GART = '" . $item["GART"] . "'
		AND accountsite_id = " . $accountSiteID ."
	";
	$amazonCategoriesResults = SQLSelect($data['from'], $date['select'], $date['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
	if (count($amazonCategoriesResults) > 0) {
		foreach($amazonCategoriesResults as $amazonCategory)
		{
			if ($amazonCategory["BrowseNodeId1"] > 0) {
				$RecommendedBrowseNode = '<RecommendedBrowseNode><![CDATA[' . $amazonCategory["BrowseNodeId1"] . ']]></RecommendedBrowseNode>' . "\n";
			}
			if ($amazonCategory["BrowseNodeId2"] > 0) {
				$RecommendedBrowseNode.= '<RecommendedBrowseNode><![CDATA[' . $amazonCategory["BrowseNodeId2"] . ']]></RecommendedBrowseNode>' . "\n";
			}
		}
	}
	return $RecommendedBrowseNode;
}

/**
 * Update amazon product bullet points
 *
 * @param $item
 * @param $languageID
 * @return string
 */
function updateAmazonProductsBulletPoints($item, $languageID)
{
	$data['from'] = "shop_items_" . $languageID;
	$date['select'] = '*';
	$date['where'] = "
		id_item = '" . $item["id_item"] . "'
	";
	$shopItemsResults = SQLSelect($data['from'], $date['select'], $date['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
	if (count($shopItemsResults) > 0) 
	{
		$bulletPoints = explode("; ", $shopItemsResults["short_description"]);
		$count = count($bulletPoints);
		$i = 0;
		$bulletPointXml = "";
		$buildPoints = "";
		foreach($bulletPoints as $bulletPoint)
		{
			if ($i >= 4) 
			{
				($count <= $i) ? $set = "" : $set = " ; ";
				$buildPoints.= $bulletPoint . $set;
			} else {
				if ($bulletPoint != null) {
					$bulletPointXml .= '<BulletPoint><![CDATA[' . $bulletPoint . ']]></BulletPoint>' . "\n";
				}
			}
			$i++;
		}
		if ($buildPoints != null) 
		{
			$bulletPointXml.= '<BulletPoint><![CDATA[' . $buildPoints . ']]></BulletPoint>' . "\n";
		}
	}
	return $bulletPointXml;
}

/**
 * @param $accountSiteID
 * @param $limit
 * @param bool $importTemp
 * @return string
 */
function updateMarketplacesAsin($accountSiteID, $limit, $importTemp = false)
{
	if ($importTemp == true) {
		$data = array();
		$data['importTemp'] = 0;
		SQLUpdate('amazon_products', $data, 0, 'shop', __FILE__, __LINE__);
	}

	$from = 'amazon_products';
	$select = 'id_product, accountsite_id, SKU, ImageLocation, ASIN';
	$addWhere = "
		accountsite_id = '1'
		AND importTemp = 0
		AND ASIN != ''";
	$orderBy = 'lastmod DESC';
	$amazonProductsResults = SQLSelect($from, $select, $addWhere, $orderBy, 0, $limit, 'shop',  __FILE__, __LINE__);
	$count = 0;
	foreach($amazonProductsResults as $amazonProduct)
	{
		$data = array();
		$data['importTemp'] = $accountSiteID;
		$data['ASIN'] = $amazonProduct['ASIN'];
		$addWhere = "
			SKU = '" . $amazonProduct["SKU"] . "'
			AND accountsite_id = '" . $accountSiteID . "'";
		SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);

		$data = array();
		$data['importTemp'] = 1;
		$addWhere = "
			SKU = '" . $amazonProduct["SKU"] . "'
			AND accountsite_id = '1'";
		SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
		$count++;
	}
	$xml = "\n" . "<updateAsin>" . "\n";
	$xml.= '<asins>Update Asin: ' . $count . '</asins>' . "\n";
	$xml.= '</updateAsin>'. "\n";
	return $xml;
}

/**
 * @param $item
 * @param $keywords
 * @param $languageID
 * @return string
 */
function updateAmazonProductsSubTitle($item, $vehicles, $vehiclesDE)
{
	$k = 0;
	if (isset($vehicles[$item["id_item"]])) {
		foreach($vehicles[$item["id_item"]] as $row3)
		{
			$data = array();
			$data['from'] = 'vehicles_de';
			$data['select'] = '*';
			$data['where'] = "
				id_vehicle =".$row3["vehicle_id"];
			$vehiclesDEResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop', __FILE__, __LINE__);
			
			if (isset($vehiclesDE[$row3["vehicle_id"]]))
			$bez1[$k] = $vehiclesDE[$row3["vehicle_id"]]["BEZ1"];
			$bez2[$k] = $vehiclesDE[$row3["vehicle_id"]]["BEZ2"];
			if (strpos($bez2[$k], "(") > 0)
				$bez2[$k] = substr($bez2[$k], 0, strpos($bez2[$k], "(") - 1);
			$k++;
		}
	}
	array_multisort($bez1, $bez2);	
	//remove sub models
	$make = array();
	$model = array();
	$testbez2 = "___";
	for( $k = 0; $k < sizeof($bez2); $k++)
	{
		$state = strpos($bez2[$k], $testbez2 . " ");
		if ( ($state === false or $state > 0) and $bez2[$k] != $testbez2) {
			$make[] = $bez1[$k];
			$model[] = $bez2[$k];
			$testbez2 = $bez2[$k];
		}
	}
	$bez1 = $make;
	$bez2 = $model;
	array_multisort($bez1, $bez2);
	
	//remove repeated brands
	$vehicles = "";
	$testbez1 = "";
	$testbez2 = "";
	for($k=0; $k<sizeof($bez1); $k++)
	{
		if ( $testbez1!=$bez1[$k] )
		{
			$vehicles.=$bez1[$k];
			$testbez1=$bez1[$k];
		}
		if ( $testbez2!=$bez2[$k] )
		{
			$vehicles.=" ".$bez2[$k];
			$testbez2=$bez2[$k];
			if ( ($k+1)<sizeof($bez1) ) $vehicles.=", ";
		}
	}
		
	for($k=0; $k<sizeof($bez1); $k++)
	{
		if ( $testbez3!=$bez3[$k] )
		{
			$testbez3=$bez3[$k];
			if ( $testbez1!=$bez1[$k] )
			{
				$vehicles.=$bez1[$k];
				$testbez1=$bez1[$k];
			}
			if ( $testbez2!=$bez2[$k] )
			{
				$vehicles.=" ".$bez2[$k];
				$testbez2=$bez2[$k];
			}
			$vehicles.=" ".$bez3[$k];
			if ( ($k+1)<sizeof($bez1) ) $vehicles.=", ";
		}
	}

	if ( $nvp[$j]["id_value"] > 0 ) {
		$subTitle = $vehicles;
	} else {
		$subTitle = $vehicles;
	}
	return $subTitle;
}

/**
 *	----------------------------------------------------------- shop items -------------------------------------------------------------
 *
 */

/**
 * @param $post
 * @return array|null
 */
function getShopItemsByActive($post)
{
 	$data['from'] = 'shop_items';
	$data['select'] = '*';
	$data['where'] = "
		active = 1
	";
	if ($post['imageImport'] != null){
		$data['where'].= "
			AND ImageImport = " . $post['imageImport'];
	}
	$date['order'] = "lastmod DESC";
	return SQLSelect($data['from'], $data['select'], $data['where'], $date['order'], 0, $post["limit"], 'shop',  __FILE__, __LINE__);
}

/**
 * @param $item
 * @param string $value
 */
function setShopItemsExportStatus($item, $value = '1')
{
	$data['exportStatus'] = $value;
	$addWhere = "
		id_item = '" . $item["id_item"] . "'
	";
	SQLUpdate('shop_items', $data, $addWhere, 'shop', __FILE__, __LINE__);
}

/**
 *	----------------------------------------------------------- images -----------------------------------------------------------------
 *
 */

/**
 * @param $item
 * @return array|null
 */
function getCmsArticlesImages($item)
{
	$data['from'] = 'cms_articles_images';
	$data['select'] = '*';
	$data['where'] = "
		article_id = '" . $item["article_id"] . "'
	";
	$result = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'web',  __FILE__, __LINE__);
	return $result;
}

/**
 * @param $image
 * @param $IMAGE_LOCATION_PATH
 * @param $IMAGE_FORMAT_ID
 * @return string
 */
function getCmsFilesImages($image, $IMAGE_LOCATION_PATH, $IMAGE_FORMAT_ID)
{
	$data['from'] = 'cms_files';
	$data['select'] = '*';
	$data['where'] = "
		original_id = '" . $image["file_id"] . "'
		AND imageformat_id = '" . $IMAGE_FORMAT_ID . "'
	";
	$originalFile = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'web',  __FILE__, __LINE__);
	$file['imageLocation'] = $IMAGE_LOCATION_PATH . floor(bcdiv($originalFile["id_file"], 1000)) .'/' . $originalFile["id_file"] . '.' . $originalFile["extension"];
	$file['file_id'] = $originalFile['id_file'];
	return $file; 
}

/**
 * @param $amazonProduct
 * @param $images
 * @param null $accountsite
 */
function updateAmazonProductsImages($amazonProduct, $images, $accountsite = null)
{
	$data['lastmod_user'] = 10;
	$data['lastmod'] = time();
	$data['lastimageupdate'] = time();
	$data['submitedProduct'] = 0;
	$data['submitedImage'] = 0;
	$data['upload'] = 1;
	$data['ImageLocation'] = $images['imageLocation'];
	$data['ImageLocationThumbnail'] = $images['imageLocationThumb'];
	if ($accountsite != null) {
		$addWhere = "
		SKU = '" . $amazonProduct["SKU"] . "'
		AND accountsite_id = '" . $accountsite['id_accountsite'] . "'";
	} else {
		$addWhere = "
		id_product = '" . $amazonProduct["id_product"] . "'";
	}
	SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
}

/**
 *	----------------------------------------------------------- logic -----------------------------------------------------------------
 *
 */

/**
 * Returns an amazon marketplace list name for the different marketplaces
 *
 * @param $amazonAccountsSites
 * @return string
 */
function getAmazonMarketplaceListName($amazonAccountsSites)
{
	if ($amazonAccountsSites['marketplace_id'] == 1) {
		$marketPlace = 'Marketplace';
	} else {
		$marketPlace = 'MarketplaceIdList.Id.1';
	}
	return $marketPlace;
}

/**
 * @param $PriceListingPriceAmount
 * @param $TopPrice
 * @return bool
 */
function getBestOfferPriceForAmazon($PriceListingPriceAmount, $TopPrice)
{
	$price = round(((100 * $PriceListingPriceAmount) / $TopPrice) - 100);
	if ($price > 5) {
		return true;
	} else {
		return false;
	}
}


/**
 *	----------------------------------------------------------- tools -----------------------------------------------------------------
 *
 */

 function checkWorktime($begin, $set)
{
	$time = time() + microtime();
	return '<worktime' . $set . '>' . date("s", ($time - $begin)) . '</worktime' . $set . '>' . "\n";
}

/**
 * Returns country code
 *
 * @return array
 */
function getLanguageIds()
{
	$language = array(
		'1' => 'de',
		'2' => 'en',
		'3' => 'ru',
		'4' => 'pl',
		'5' => 'it',
		'6' => 'fr',
		'7' => 'es',
		'8' => 'zh'
	);
	return $language;
}

/**
 * Debug view
 *
 * @param $dump
 * @param bool $exit
 */
function pr($dump, $exit = false)
{
	echo '<pre>';
		var_dump($dump);
		if ($exit == true)
		exit;
	echo '</pre>' . "\n";
}

/**
 * Returns an amazon session user id
 *
 * @return mixed
 */
function getAmazonSessionUserId()
{
	if (isset($_SESSION["id_user"]) && $_SESSION["id_user"] != null) {
		return $_SESSION["id_user"];
	}
}

/**
 * Returns an amazon submited product status
 *
 * @param $submitedProduct
 * @return string
 */
function getAmazonSubmitedProduct($submitedProduct)
{
	($submitedProduct == 1) ? $html = '<span class="msg-success">Yes</span>' : $html = '<span class="msg-error">No</span>';
	return $html;
}

/**
 * Returns an amazon submited upload icon
 *
 * @param $submited
 * @return string
 */
function getAmazonSubmitedUpload($submited)
{
	($submited == 1) ? $html = '<i class="fa fa-upload ico-success"></i>' : $html = '<i class="fa fa-upload ico-danger"></i>';
	return $html;
}

/**
 * Returns a Amazon Submited Image with html
 *
 * @param $product
 * @return string
 */
function getAmazonSubmitedImage($product)
{
    if (!empty($product['ImageLocationThumbnail'])) {
        $path = str_ireplace('files_thumbnail', 'files', $product['ImageLocationThumbnail']);
		$html = '<a href="' . $path . '" target="_blank"><img style="float: none;" src="' . $product['ImageLocationThumbnail'] . '" width="80px" height="50px"></a>';
    } else {
        $html = '<span class="msg-error">keins</span>';
    }
    return $html;
}

/**
 * Set StandardPriceImport in shop items table
 *
 * @param int $setValue
 * @return bool|mysqli_result
 */
function setShopItemsStandardPriceImport($setValue = 0)
{
	if ($setValue == 0) {
		$data['StandardPriceImport'] = 0;
	} else {
		$data['StandardPriceImport'] = $setValue;
	}
	$result = SQLUpdate('shop_items', $data, 0, 'shop', __FILE__, __LINE__);
	return $result;
}

/**
 * Set QuantityImport in shop items table
 *
 * @param int $setValue
 * @return bool|mysqli_result
 */
function setShopItemsQuantityImport($setValue = 0)
{
	if ($setValue == 0) {
		$data['QuantityImport'] = 0;
	} else {
		$data['QuantityImport'] = $setValue;
	}
	$result = SQLUpdate('shop_items', $data, 0, 'shop', __FILE__, __LINE__);
	return $result;
}

/**
 * Set ImageImport in shop items table
 *
 * @param int $setValue
 * @param null $itemID
 * @return bool|mysqli_result
 */
function setShopItemsImageImport($setValue = 0, $itemID = null)
{
	if ($setValue == 0) {
		$data['ImageImport'] = 0;
	} else {
		$data['ImageImport'] = $setValue;
	}
	if ($itemID != null) {
		$addWhere = "
			id_item = '" . $itemID . "'
		";
	}
	$result = SQLUpdate('shop_items', $data, $addWhere, 'shop', __FILE__, __LINE__);
	return $result;
}

/**
 * Set QuantityImport in shop items table
 *
 * @param int $setValue
 * @return bool|mysqli_result
 */
function setamazonOffersTopPriceImport($setValue = 0)
{
	if ($setValue == 0) {
		$data['importTopPrice'] = 0;
	} else {
		$data['importTopPrice'] = $setValue;
	}
	$result = SQLUpdate('amazon_offers', $data, 0, 'shop', __FILE__, __LINE__);
	return $result;
}


/**
 *	----------------------------------------------------------- xml -----------------------------------------------------------------
 *
 */

/**
 * Returns a xml amazon accountsite name
 *
 * @param $data
 * @return string
 */
function getXmlAmazonAccountsites($data)
{
	$xml = '<AmazonMarketplaces>' . "\n";
	$xml.= '		<MarketplacesName><![CDATA[' . $data["name"] . ']]></MarketplacesName>' . "\n";
	$xml.= '</AmazonMarketplaces>'. "\n";
	return $xml;
}

/**
 * Returns a xml amazon product
 *
 * @param $product
 * @param null $amazonAccountsSites
 * @return string
 */
function getXmlAmazonProduct($product, $amazonAccountsSites = null)
{
	$xml = '<AmazonProduct>';
	if (count($product) > 0) {
		$xml.= '<ProductID><![CDATA[' . $product["id_product"] . ']]></ProductID>' . "\n";
		$xml.= '<Bundle><![CDATA[' . $product["bundle"] . ']]></Bundle>' . "\n";
		$xml.= '<ItemID><![CDATA[' . $product["item_id"] . ']]></ItemID>' . "\n";
		$xml.= '<ArticleID><![CDATA[' . $product["article_id"] . ']]></ArticleID>' . "\n";
		$xml.= '<ASIN><![CDATA[' . $product["ASIN"] . ']]></ASIN>' . "\n";
		$xml.= '<EAN><![CDATA[' . $product["EAN"] . ']]></EAN>' . "\n";
		$xml.= '<AccountID><![CDATA[' . $product["account_id"] . ']]></AccountID>' . "\n";
		$xml.= '<SKU><![CDATA[' . $product["SKU"] . ']]></SKU>' . "\n";
		$xml.= '<GART><![CDATA[' . $product["GART"] . ']]></GART>' . "\n";
		$xml.= '<Title><![CDATA[' . $product["Title"] . ']]></Title>' . "\n";
		$xml.= '<SubTitle><![CDATA[' . $product["SubTitle"] . ']]></SubTitle>' . "\n";
		$xml.= '<Description><![CDATA[' . $product["Description"] . ']]></Description>' . "\n";
		$xml.= '<Comment><![CDATA[' . $product["Comment"] . ']]></Comment>' . "\n";
		$xml.= '<Quantity><![CDATA[' . $product["Quantity"] . ']]></Quantity>' . "\n";
		$xml.= '<ItemPackageQuantity><![CDATA[' . $product["ItemPackageQuantity"] . ']]></ItemPackageQuantity>' . "\n";
		$xml.= '<OfferInventoryLeadTime><![CDATA[' . $product["OfferInventoryLeadTime"] . ']]></OfferInventoryLeadTime>' . "\n";
		$xml.= '<StandardPrice><![CDATA[' . $product["StandardPrice"] . ']]></StandardPrice>' . "\n";
		$xml.= '<StandardPriceSuggestion><![CDATA[' . $product["StandardPriceSuggestion"] . ']]></StandardPriceSuggestion>' . "\n";
		$xml.= '<CriticalPrice><![CDATA[' . $product["CriticalPrice"] . ']]></CriticalPrice>' . "\n";
		$xml.= '<TopPrice><![CDATA[' . $product["TopPrice"] . ']]></TopPrice>' . "\n";
		$xml.= '<OfferingCondition><![CDATA[' . $product["OfferingCondition"] . ']]></OfferingCondition>' . "\n";
		$xml.= '<Brand><![CDATA[' . $product["Brand"] . ']]></Brand>' . "\n";
		$xml.= '<Manufacturer><![CDATA[' . $product["Manufacturer"] . ']]></Manufacturer>' . "\n";
		$xml.= '<ImageLocation><![CDATA[' . $product["ImageLocation"] . ']]></ImageLocation>' . "\n";
		$xml.= $product["BulletPoints"];
		$xml.= $product["RecommendedBrowseNode"];
		$xml.= '<lastpriceupdate><![CDATA[' . getDateToday($product["lastpriceupdate"]) . ']]></lastpriceupdate>' . "\n";
		$xml.= '<lastquantityupdate><![CDATA[' . getDateToday($product["lastquantityupdate"]) . ']]></lastquantityupdate>' . "\n";
		$xml.= '<submitedProductDate><![CDATA[' . getDateToday($product["submitedProductDate"]) . ']]></submitedProductDate>' . "\n";
		$xml.= '<submitedImage><![CDATA[' . getAmazonSubmitedImage($product) . ']]></submitedImage>' . "\n";
		$xml.= '<submitedPrice><![CDATA[' . getAmazonSubmitedUpload($product["submitedPrice"]) . ']]></submitedPrice>' . "\n";
		$xml.= '<submitedQuantity><![CDATA[' . getAmazonSubmitedUpload($product["submitedQuantity"]) . ']]></submitedQuantity>' . "\n";
		$xml.= '<submitedProduct><![CDATA[' . getAmazonSubmitedUpload($product["submitedProduct"]) . ']]></submitedProduct>' . "\n";
		$xml.= '<Firstmod><![CDATA[' . $product["firstmod"] . ']]></Firstmod>' . "\n";
		$xml.= '<FirstmodUser><![CDATA[' . $product["firstmod_user"] . ']]></FirstmodUser>' . "\n";
		$xml.= '<Lastmod><![CDATA[' . $product["lastmod"] . ']]></Lastmod>' . "\n";
		$xml.= '<LastmodUser><![CDATA[' . $product["lastmod_user"] . ']]></LastmodUser>' . "\n";
	} else {
		$xml.=  '<error>kein Produkt gefunden</error>' . "\n";
	}
	$xml.= '</AmazonProduct>' . "\n";
	return $xml;
}

/**
 * Returns a xml amazon order
 *
 * @param $order
 * @return string
 */
function getXmlAmazonOrder($order)
{
	$xml = '<AmazonOrder>';
	if (count($order) > 0) {
			$xml.= '<id_orders><![CDATA[' . $order["id_orders"] . ']]></id_orders>' . "\n";
			$xml.= '<PurchaseDate><![CDATA[' . stringToTime($order["PurchaseDate"]) . ']]></PurchaseDate>' . "\n";
			$xml.= '<SalesChannel><![CDATA[' . $order["SalesChannel"] . ']]></SalesChannel>' . "\n";
			$xml.= '<ProductDetails><![CDATA[' . $order["AmazonOrderId"] . ']]></ProductDetails>';
			$xml.= '<ShippingAddressName><![CDATA[' . $order["ShippingAddressName"] . ']]></ShippingAddressName>' . "\n";
			$xml.= '<ShipmentServiceLevelCategory><![CDATA[' . $order["ShipmentServiceLevelCategory"] . ']]></ShipmentServiceLevelCategory>' . "\n";
			$xml.= '<ShipServiceLevel><![CDATA[' . $order["ShipServiceLevel"] . ']]></ShipServiceLevel>' . "\n";
			$xml.= '<OrderStatus><![CDATA[' . $order["OrderStatus"] . ']]></OrderStatus>' . "\n";
			$xml.= '<LatestDeliveryDate><![CDATA[' . stringToTime($order["LatestDeliveryDate"]) . ']]></LatestDeliveryDate>' . "\n";
			$xml.= '<AmazonOrderId>' . $order["AmazonOrderId"] . '</AmazonOrderId>' . "\n";
			$xml.= '<importShopStatus><![CDATA[' . $order["importShopStatus"] . ']]></importShopStatus>' . "\n";
			$xml.= '<firstmod><![CDATA[' . getDateToday($order["firstmod"]) . ']]></firstmod>' . "\n";
			$xml.= '<lastmod><![CDATA[' . getDateToday($order["lastmod"]) . ']]></lastmod>' . "\n";
			$xml.= '<shippingStatusId>' . $order["shippingStatusId"] . '</shippingStatusId>' . "\n";
			$xml.= '<ShippingAddressCountryCode>' . $order["ShippingAddressCountryCode"] . '</ShippingAddressCountryCode>' . "\n";
			$xml.= '<shippingStatusUpdate>' . $order["shippingStatusUpdate"] . '</shippingStatusUpdate>' . "\n";
			$xml.= '<amazonOrderItemsCount>' . countAmazonOrderItemyByAmazonOrderId($order) . '</amazonOrderItemsCount>' . "\n";
			$xml.= '<shippingNumber>' . $order['shippingNumber'] . '</shippingNumber>' . "\n";
	} else {
		$xml.=  '<error>keine Order gefunden</error>' . "\n";
	}
	$xml.= '</AmazonOrder>' . "\n";
	return $xml;
}

/**
 * Returns a xml feed submission
 *
 * @param $feedSubmission
 * @return string
 */
function getXmlAmazonFeedSubmission($feedSubmission)
{
	$FeedType = array(
		"_POST_PRODUCT_DATA_" => 									"Product Feed",
		"_POST_PRODUCT_RELATIONSHIP_DATA_" => 						"Relationships Feed",
		"_POST_ITEM_DATA_" => 										"Single Format Item Feed",
		"_POST_PRODUCT_OVERRIDES_DATA_" => 							"Shipping Override Feed",
		"_POST_PRODUCT_IMAGE_DATA_" => 								"Product Images Feed",
		"_POST_PRODUCT_PRICING_DATA_" => 							"Pricing Feed",
		"_POST_INVENTORY_AVAILABILITY_DATA_" => 					"Inventory Feed",
		"_POST_ORDER_ACKNOWLEDGEMENT_DATA_" => 					"Order Acknowledgement Feed",
		"_POST_ORDER_FULFILLMENT_DATA_" => 							"Order Fulfillment Feed",
		"_POST_FULFILLMENT_ORDER_REQUEST_DATA_" => 				"FBA Shipment Injection Fulfillment Feed",
		"_POST_FULFILLMENT_ORDER_CANCELLATION_REQUEST_DATA" =>	"FBA Shipment Injection Cancellation Feed",
		"_POST_PAYMENT_ADJUSTMENT_DATA_" => 						"Order Adjustment Feed",
		"_POST_INVOICE_CONFIRMATION_DATA_" => 						"Invoice Confirmation Feed",
		"_POST_ITEM_DATA_" => 										""
	);	
	
	$FeedProcessingStatus = array(
		"_SUBMITTED_" =>		'<span style="color:#9c6500;">Empfangen</span>', 
		"_IN_PROGRESS_" =>	'<span style="color:#9c6500;">In Bearbeitung</span>', 
		"_CANCELLED_" =>		'<span style="color:#9c0006;">Abgebrochen</span>', 
		"_DONE_" =>			'<span style="color:#006100;">Erledigt</span>'
	);	
	
	$xml = '<AmazonFeedSubmission>';
	if (count($feedSubmission) > 0) 
	{
		$messageWithError = $feedSubmission["MessagesWithError"];
		if ($feedSubmission["MessagesWithError"] > 0) 
		{
			$messageWithError = '<span style="color:red; font-weight: bold;">' . $feedSubmission["MessagesWithError"] . '</span>';	
		}
		$messageWithWarning = $feedSubmission["MessagesWithWarning"];
		if ($feedSubmission["MessagesWithWarning"] > 0) 
		{
			$messageWithWarning = '<span style="color:#E78F08; font-weight: bold;">' . $feedSubmission["MessagesWithWarning"] . '</span>';	
		}
		$xml.= '<id_feed_submission>' . $feedSubmission["id_feed_submission"] . '</id_feed_submission>' . "\n";
		$xml.= '<FeedSubmissionId>' . $feedSubmission["FSMFeedSubmissionId"] . '</FeedSubmissionId>' . "\n";
		$xml.= '<FeedType>' . $FeedType[$feedSubmission["FeedType"]] . '</FeedType>' . "\n";
		$xml.= '<SubmittedDate><![CDATA[' . date("d-m-Y H:i", strtotime($feedSubmission["SubmittedDate"])) . ']]></SubmittedDate>' . "\n";
		$xml.= '<StartedProcessingDate><![CDATA[' . date("d-m-Y H:i", strtotime($feedSubmission["StartedProcessingDate"])) . ']]></StartedProcessingDate>' . "\n";
		$xml.= '<CompletedProcessingDate><![CDATA[' . date("d-m-Y H:i", strtotime($feedSubmission["CompletedProcessingDate"])) . ']]></CompletedProcessingDate>' . "\n";
		$xml.= '<FeedProcessingStatus><![CDATA[' . $FeedProcessingStatus[$feedSubmission["FeedProcessingStatus"]] . ']]></FeedProcessingStatus>' . "\n";
		$xml.= '<MessagesProcessed>' . $feedSubmission["MessagesProcessed"] . '</MessagesProcessed>' . "\n";
		$xml.= '<MessagesSuccessful>' . $feedSubmission["MessagesSuccessful"] . '</MessagesSuccessful>' . "\n";
		$xml.= '<MessagesWithError><![CDATA[' . $messageWithError . ']]></MessagesWithError>' . "\n";
		$xml.= '<MessagesWithWarning><![CDATA[' . $messageWithWarning . ']]></MessagesWithWarning>' . "\n";
	} else {
		$xml.=  '<error>keine Feed Submissions gefunden</error>' . "\n";
	}
	$xml.= '</AmazonFeedSubmission>' . "\n";
	return $xml;	
}

/**
 * Returns a xml feed submission result
 *
 * @param $feedSubmissionsResult
 * @return string
 */
function getXmlAmazonFeedSubmissionResult($feedSubmissionsResult)
{
	$xml = '<AmazonFeedSubmissionResult>';
	if (count($feedSubmissionsResult) > 0) 
	{
			$xml.= '<amazon_feed_submission_result>' . $feedSubmissionsResult["amazon_feed_submission_result"] . '</amazon_feed_submission_result>' . "\n";
			$xml.= '<amazon_feed_submission_message_id>' . $feedSubmissionsResult["amazon_feed_submission_message_id"] . '</amazon_feed_submission_message_id>' . "\n";
			$xml.= '<FeedSubmissionId>' . $feedSubmissionsResult["FSRFeedSubmissionId"] . '</FeedSubmissionId>' . "\n";
			$xml.= '<DocumentTransactionID>' . $feedSubmissionsResult["DocumentTransactionID"] . '</DocumentTransactionID>' . "\n";
			$xml.= '<MessageID>' . $feedSubmissionsResult["MessageID"] . '</MessageID>' . "\n";
			$xml.= '<ResultCode>' . $feedSubmissionsResult["ResultCode"] . '</ResultCode>' . "\n";
			$xml.= '<ResultMessageCode>' . $feedSubmissionsResult["ResultMessageCode"] . '</ResultMessageCode>' . "\n";
			$xml.= '<ResultDescription><![CDATA[' . $feedSubmissionsResult["ResultDescription"] . ']]></ResultDescription>' . "\n";
			$xml.= '<AdditionalInfoSKU>' . $feedSubmissionsResult["AdditionalInfoSKU"] . '</AdditionalInfoSKU>' . "\n";
	} else {
		$xml.=  '<error>kein Feed Submission Result  gefunden</error>' . "\n";
	}
	$xml.= '</AmazonFeedSubmissionResult>' . "\n";
	return $xml;
}
