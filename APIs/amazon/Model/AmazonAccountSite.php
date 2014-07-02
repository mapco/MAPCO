<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon AccountSite Functions
 *
*******************************************************************************/

/**
 * Returns an amazon accountsite by accountsites id

 * @param $post
 * @return array|null
 */
function getAmazonAccountsites($post)
{
	if ($post['accountsite_id'] > 0) {
		$data['from'] = 'amazon_accounts_sites
			LEFT JOIN amazon_marketplaces ON id_marketplace = marketplace_id';
		$data['select'] = '*';
		$data['where'] = "
			id_accountsite = " . $post['accountsite_id'] . ' AND active = 1';
		$amazonAccountsSite = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
		return $amazonAccountsSite;
	}
}

/**
 * Returns an amazon accountsite by account id
 *
 * @param $post
 * @return array|null
 */
function getAmazonAccountsitesByAccountId($post)
{
	$data['from'] = 'amazon_accounts_sites';
	$data['select'] = '*';
	$data['where'] = "account_id = '" . $post['account_id'] . "'";
	//	and by marketplace id
	if (isset($post['marketplaceID']) && $post['marketplaceID'] != null) {
		 $data['where'].= " AND marketplace_id = '" . $post['marketplaceID'] . "'";
	} else {
		$data['order'] = "marketplace_id ASC";
	}
	$result = SQLSelect($data['from'], $data['select'], $data['where'], $data['order'], 0, 0, 'shop',  __FILE__, __LINE__);
	return $result;
} 