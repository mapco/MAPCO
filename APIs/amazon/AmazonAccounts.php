<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for Accounts get
 *	- get the amazon accounts table
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

// keep post submit
$post = $_POST;

/*
 *--------------------------------------- Amazon Accounts ------------------------------------------------
 */
	 
	if ($post['action'] == 'listAmazonAccounts')
	 {
		$post_data = array();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "TableDataSelect";
		$post_data['where'] = "ORDER BY id_account ASC";
		$post_data['table'] = "amazon_accounts";
		$post_data['db'] = "dbshop";
		$amazonAccountsResult = soa2($post_data, __FILE__, __LINE__, 'xml');
		echo $amazonAccountsResult;
	}

	if ($post['action'] == 'addAmazonAccounts')
	{
		$data = array();
		$data['title'] = $post['title'];
		$data['description'] = $post['description'];
		$data['AWSAccessKeyId'] = $post['AWSAccessKeyId'];
		$data['MarketplaceId'] = $post['MarketplaceId'];
		$data['MerchantId'] = $post['MerchantId'];
		$data['SecretKey'] = $post['SecretKey'];
		$results = q_insert("amazon_accounts", $data, $dbshop, _FILE__, __LINE__);	
	}

	if ($post["action"]=="editAmazonAccounts" )
	{
		$data = array();
		$data['title'] = $post['title'];
		$data['description'] = $post['description'];
		$data['AWSAccessKeyId'] = $post['AWSAccessKeyId'];
		$data['MarketplaceId'] = $post['MarketplaceId'];
		$data['MerchantId'] = $post['MerchantId'];
		$data['SecretKey'] = $post['SecretKey'];
	
		$data["lastmod"] = time();
		$data["lastmod_user"] = $_SESSION["id_user"];
		$addWhere = "WHERE id_account = " . $post["id_account"] . ";";
		$results = q_update("amazon_accounts", $data, $addWhere, $dbshop, __FILE__, __LINE__);
	}

/*
 *--------------------------------------- Amazon Accounts Sites ------------------------------------------------
 */
 
 	/**
	 *	get amazon accountsites for amazon marketplaces by account id
	 *
	 */
 	if ($post['action'] == 'getAccountSitesByAccountId')
	{
		$amazonAccountsSites = getAmazonAccountsitesByAccountId($post);	
		echo $amazonAccountsSites;
	}

	/**
	 * get a list amazon accounts sites
	 *
	 */
	if ($post['action'] == 'listAmazonAccountsSites') 
	{
		$post_data = array();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "TableDataSelect";
		$post_data['where'] = "WHERE account_id = '" . $post['account_id'] . "'";
		$post_data['table'] = "amazon_accounts_sites";
		$post_data['join'] = "LEFT JOIN amazon_marketplaces ON id_marketplace = marketplace_id";
		$post_data['db'] = "dbshop";
		$amazonAccountsSitesResult = soa2($post_data, __FILE__, __LINE__, 'xml');
		echo $amazonAccountsSitesResult;
	}

	/**
	 *	get an amazon accounts site by account sites id
	 *
	 */
	if ($post['action'] == 'getAmazonAccountsSite') 
	{
		$post_data = array();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "TableDataSelect";
		$post_data['where'] = "WHERE id_accountsite = '" . $post['accountsite_id'] . "'";
		$post_data['table'] = "amazon_accounts_sites";
		$post_data['db'] = "dbshop";
		$amazonAccountsSitesResult = soa2($post_data, __FILE__, __LINE__, 'xml');
		echo $amazonAccountsSitesResult;
	}

	/**
	 * add amazon accounts site
	 *
	 */
	if ($post['action'] == 'addAmazonAccountsSites')
	{
		$data = array();
		$data['title'] = $post['title'];
		$data['description'] = $post['description'];
		$data['marketplace_id'] = $post['marketplace_id'];
		$data['active'] = $post['active'];
		$data['account_id'] = $post['account_id'];
		
		$data["firstmod"] = time();
		$data["firstmod_user"] = $_SESSION["id_user"];	
		$results = q_insert("amazon_accounts_sites", $data, $dbshop, _FILE__, __LINE__);	
	}

	/**
	 * edit amazon accounts site
	 *
	 */
	if ($post["action"]=="editAmazonAccountsSites" )
	{
		$data = array();
		$data['title'] = $post['title'];
		$data['description'] = $post['description'];
		$data['marketplace_id'] = $post['marketplace_id'];
		$data['active'] = $post['active'];
	
		$data["lastmod"] = time();
		$data["lastmod_user"] = $_SESSION["id_user"];
		$addWhere = "WHERE id_accountsite = " . $post["id_accountsite"] . ";";
		$results = q_update("amazon_accounts_sites", $data, $addWhere, $dbshop, __FILE__, __LINE__);
	}

	/**
	 * execute delete amazon account sites
	 *
	 */	
	if ($post['action'] == 'deleteAmazonAccountsSites') 
	{
		$amazonAccountsSitesQuery = "
			DELETE FROM amazon_accounts_sites
			WHERE id_accountsite = '" . $post["accountsite_id"] . "'";
		$results = q($amazonAccountsSitesQuery, $dbshop, __LINE__, __FILE__);
			
		$xml = '<deleteAmazonAccountsSites>' . "\n";
		$xml.= '	<accountsite_id>' . $post['accountsite_id'] . '</accountsite_id>' . "\n";
		$xml.= '</deleteAmazonAccountsSites>' . "\n";
		echo $xml;
	}