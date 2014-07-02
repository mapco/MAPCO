<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Job Service for order import
 *	- call crm/AmazonOrderImport
 *
 *	@params
 *	- account_id
 *	- marketplaceID
 *
 *	Amazon Job Orders import into the shop orders
 *
 *	@params
 *	- forceupdate (only for shop orders import)
 *	- AmazonOrderId
 *	- marketplaceID
*******************************************************************************/
$PATH = dirname(__FILE__);
require_once(str_replace('jobs', '', $PATH) . '/amazon/Model/AmazonModel.php');

$start = time();

// keep post submit
$post = $_POST;

	//	get amazon accountsites for amazon marketplaces by account id
	$amazonAccountsSites = getAmazonAccountsitesByAccountId($post);	
	
	/*
	//	get amazon accountsites for amazon marketplaces by account id
	if (isset($post['account_id']) && $post['account_id'] != null)
	{
		$post_data = array();
		$post_data["API"] = "amazon";
		$post_data["APIRequest"] = "AmazonAccounts";
		$post_data["APICleanRequest"] = true;
		$post_data["action"] = "getAccountSitesByAccountId";
		$post_data["account_id"] = $post['account_id'];
		$post_data["marketplaceID"] = $post['marketplaceID'];
		$amazonAccountsSites = soa4($post_data, __FILE__, __LINE__, 'arr');
		print_r($amazonAccountsSites);
		exit;
	}
	*/
	
	/**
	 *	import amazon orders into to amazon_orders
	 */	
	if (count($amazonAccountsSites) > 0 AND $post["forceupdate"] != 1) 
	{
		foreach($amazonAccountsSites as $amazonAccountsSite)
		{
			$post_data = array();
			$post_data["API"] = "amazon";
			$post_data["APIRequest"] = "AmazonOrdersImport";
			$post_data["accountsite_id"] = $amazonAccountsSite['id_accountsite'];
			echo soa2($post_data, __FILE__, __LINE__, 'xml');
		}
	}
		
	/**
	 *	import orders from amazon_orders to shop_orders
	 */	
	$post_data = array();
	$post_data["API"] = "crm";
	$post_data["APIRequest"] = "AmazonOrderShopImport";		 		 
	if (isset($post['AmazonOrderId']) && $post['AmazonOrderId'] > 0) 
	{
		$data = array();
		$data['from'] = 'amazon_orders';
		$data['select'] = 'id_orders, AmazonOrderId, importShopStatus';
		if ($post["forceupdate"] == 1) 
		{
			$data['where'] = "
				AmazonOrderId = '" . $post['AmazonOrderId'] . "'";
		} else {
			$data['where'] = "
				AmazonOrderId = '" . $post['AmazonOrderId'] . "'
				AND importShopStatus = 0";			
		}
		$amazonOrder = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
		
		$post_data["AmazonOrderId"] = $amazonOrder["AmazonOrderId"];
		$jobresponse = "\n\r" . post(PATH . "soa2/", $post_data);
	} else {

		$data = array();
		$data['from'] = 'amazon_orders';
		$data['select'] = 'id_orders, AmazonOrderId, importShopStatus, firstmod';
		$data['where'] = "
			importShopStatus = 0 
			AND ShippingAddressCountryCode != ''
		";
		$data['orderBy'] = 'firstmod DESC';
		$amazonOrders = SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, 0, 'shop',  __FILE__, __LINE__);
		if (count($amazonOrders) > 0)
		{
			$jobresponse = '';
			foreach ($amazonOrders as $amazonOrder)
			{
				if ((time() - $start) < 60) #
				{
					$post_data["AmazonOrderId"] = $amazonOrder["AmazonOrderId"];
					$jobresponse.= "\n\r" . post(PATH . "soa2/", $post_data);
				}
			}
		}
	}
	echo $jobresponse;
