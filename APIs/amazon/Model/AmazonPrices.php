<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Prices Functions
 *
*******************************************************************************/

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
		AND LST_NR = '" . $amazonAccountsSites["pricelist"] . "'";;
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
	if ($language == 1 && $item["GART"] == 82 && stristr($item["MPN"], '/2') === true) {
		$offeringConditionNote = 'Der angegebene Verkaufspreis bezieht sich auf 2 Stück (ein Paar)!';
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
