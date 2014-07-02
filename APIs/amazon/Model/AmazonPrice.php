<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Price Functions
 *
*******************************************************************************/

/**
 * Finds shop items for price update
 *
 * @param $post
 * @return array|null
 */
function findsShopItemsForPriceUpdate($post)
{
	$data['from'] = 'shop_items';
	$data['select'] = 'id_item, active, MPN, GART, StandardPriceImport, lastmod';
	$data['where'] = "
		active = 1
		AND StandardPriceImport = 0
	";
	$data['orderBy'] = 'lastmod DESC';
	return SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, $post['limit'], 'shop',  __FILE__, __LINE__);
}

/**
 * Finds amazon products for price update
 *
 * @param $amazonAccountsSitesId
 * @return array|null
 */
function findsAmazonProductsForPriceUpdate($amazonAccountsSitesId)
{
	$data['from'] = 'amazon_products';
	$data['select'] = 'id_product, accountsite_id, SKU, lastpriceupdate';
	$data['where'] = "
		accountsite_id = '" . $amazonAccountsSitesId . "'
	";
	return SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
}

/**
 * Finds prpos pricelist by LST NR
 *
 * @param $pricelist
 * @return array|null
 */
function findsPrPosPriceList($pricelist)
{
	$data['from'] = 'prpos';
	$data['select'] = 'ID, ARTNR, LST_NR, POS_0_WERT';
	$addWhere = "
		LST_NR = '" . $pricelist . "'";
	return SQLSelect($data['from'], $data['select'], $addWhere, 0, 0, 0, 'shop',  __FILE__, __LINE__);
}

/**
 * Returns prpos price by artnr
 *
 * @param $priceList
 * @param $item
 * @return float
 */
function getPrPosPriceByArtNr($priceList, $item)
{
	$price = round($priceList["POS_0_WERT"] * 1.19, 2);

    //	Einzelne Bremsbelege als Satz ausweisen
    if ($item["GART"] == 82 && stristr($item["MPN"], '/2') === false) {
        $price = round($price * 2, 2);
    }
    return $price;
}

/**
 * Returns an amazon product price
 *
 * @param $item
 * @param $amazonAccountsSites
 * @return float
 */
function getAmazonProductsPriceByArtNr($item, $amazonAccountsSites)
{
	$from = 'prpos';
	$select = '*';
	$addWhere = "
		ArtNr = '" . $item["MPN"] . "'
		AND LST_NR = '" . $amazonAccountsSites["pricelist"] . "'";
	$prposResult = SQLSelect($from, $select, $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
	$price = round($prposResult["POS_0_WERT"] * 1.19, 2);

    //	Einzelne Bremsbelege als Satz ausweisen
    if ($item["GART"] == 82 && stristr($item["MPN"], '/2') === false) {
        $price = round($price * 2, 2);
    }
    return $price;
}

/**
 * Returns offering condition note by language
 *
 * @param $item
 * @param null $language
 * @return string
 */
function getOfferingConditionNote($item, $language = null)
{	
	if ($item["GART"] == 82 && stristr($item["MPN"], '/2') == false) {
		//	DE
		if ($language == 1) {
			$offeringConditionNote = 'Der angegebene Verkaufspreis bezieht sich auf 2 Stück (ein Paar)!';
		}
		//	GB
		if ($language == 2) {
			$offeringConditionNote = 'The item price is for 2 units!';
		}
		//
		if ($language == 3) {
			$offeringConditionNote = '';
		}
		//
		if ($language == 4) {
			$offeringConditionNote = '';
		}
		//	IT
		if ($language == 5) {
			$offeringConditionNote = 'Il prezzo di vendita specificato si riferisce a 2 pezzi (una coppia)!';
		}
		//	FR
		if ($language == 6) {
			$offeringConditionNote = 'Le prix est pour 2 pièces!';
		}
		//	ES
		if ($language == 7) {
			$offeringConditionNote = '¡El precio de compra corresponde a dos piezas (un par)!';
		}
	}
	return $offeringConditionNote;
}

/**
 * Returns an amazon products count for critical prices by account
 *
 * @param $account
 * @return string
 */
function getCriticalPricesByAccount($account)
{
	$amazonAccountResult = getAmazonAccountById($account);
	$from = 'amazon_accounts_sites
		LEFT JOIN amazon_marketplaces ON id_marketplace = marketplace_id';
	$select = '*';
	$addWhere = "
		account_id = '" . $amazonAccountResult['id_account'] . "'";
	$orderBy = 'id_accountsite ASC';
	$amazonAccountsSitesResults = SQLSelect($from, $select, $addWhere, $orderBy, 0, 0, 'shop',  __FILE__, __LINE__);

	$html = '<h3>Amazon Dashboard - ' . $amazonAccountResult['title'] . '</h3>';
	if (count($amazonAccountsSitesResults) > 0) {
		foreach($amazonAccountsSitesResults as $amazonAccountsSitesResult)
		{
            $html.= getCriticalPricesByAccountsite($amazonAccountsSitesResult);
		}
	}
	return $html;
}

/**
 * Returns an amazon products count for critical prices by accountsite
 *
 * @param $accountSite
 * @return string
 */
function getCriticalPricesByAccountsite($accountSite)
{
	$addWhere = "accountsite_id = " . $accountSite['id_accountsite'] . " AND CriticalPrice = 1";
	$countCriticalPrice = SQLCount('amazon_products', $addWhere, 'shop', __FILE__, __LINE__);
	if ($countCriticalPrice > 0) {
		$setClass = 'widget-warning';
	} else {
		$setClass = 'widget-success';
	}
	$html = '<div class="' . $setClass . '">';
	$html.= '	<strong>Produkte<br />Kritische Preise<br />' . $accountSite['name'] . '</strong><br />' . $countCriticalPrice;
	$html.= '<input type="button" class="info-corner-button" id="info_critical_price_button" value="!"></div>';
	return $html;
}

/**
 * Update amazon products prices
 *
 * @param $items
 * @param $prices
 */
function updateAmazonProductsPrices($items)
{
	if (sizeof($items) > 0) {
		$data['lastmod'] = time();
		$data['lastmod_user'] = getAmazonSessionUserId();
		$data['lastpriceupdate'] = time();
		$data['submitedProduct'] = 0;
		$data['submitedPrice'] = 0;
		$data['upload'] = 1;
		$caseStandardPrice = "StandardPrice = CASE";
		foreach($items as $key => $item)
		{
			$caseStandardPrice.= "\n WHEN id_product = " . $items[$key]['id_product'] . " THEN " . $items[$key]['price'];
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
 * Update amazon products offering condition notes
 *
 * @param $items
 * @param $notes
 */
function updateAmazonProductsOfferingConditionNotes($items)
{
	if (sizeof($items) > 0) {
		$data = array();
		$data['submitedPrice'] = 0;
		$caseOfferingConditionNote = "OfferingConditionNote = CASE";
		foreach($items as $key => $item)
		{	
			if ($items[$key]['OfferingConditionNote'] != null) {
				$caseOfferingConditionNote.= "\n WHEN id_product = " . $items[$key]['id_product'] . " THEN '" . $items[$key]['OfferingConditionNote'] . "'";
				$productsIds[] = $items[$key]['id_product'];
			}			
		}
		if (count($productsIds) > 0) {
			$caseOfferingConditionNote.= "
				END
				WHERE id_product IN (" . implode(", ", $productsIds) . ")
			";
			$addWhere = $caseOfferingConditionNote;
			SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}
	}
}
