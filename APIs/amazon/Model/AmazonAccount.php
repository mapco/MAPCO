<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Account Functions
 *
*******************************************************************************/

/**
 * Returns amazon accounts
 *
 * @return bool|mysqli_result
 */
function getAmazonAccounts()
{
	$data['form'] = 'amazon_accounts';
	$data['select'] = '*';
	$data['where'] = "
		active > 0";
	return SQLSelect($data['form'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
}

/**
 * Returns an amazon account by account id
 *
 * @param $post array
 * @return array|null
 */
function getAmazonAccount($post)
{
	if (isset($post['account_id'])) {
		$data['from'] = 'amazon_accounts';
		$data['select'] = '*';
		$data['where'] = "
			active > 0 
			AND id_account = '" . $post['account_id'] . "'
		";
		$amazonAccount = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
		return $amazonAccount;
	}
}

/**
 * Returns amazon account by id
 *
 * @param $accountID
 * @return array|null
 */
function getAmazonAccountById($accountID)
{
	$data['form'] = 'amazon_accounts';
	$data['select'] = '*';
	$data['where'] = "
		active > 0 
		AND id_account = '" . $accountID . "'";
	return SQLSelect($data['form'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
}
