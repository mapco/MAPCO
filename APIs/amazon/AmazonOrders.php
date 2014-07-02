<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for Orders get
 *	- get the latest orders from the amazon orders table
 *
 *	@params
 *	- set
 *
*******************************************************************************/
$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

// keep post submit
$post = $_POST;

	/**
	 *	search amazon products (SKU)
	 *
	 */
	if ($post['action'] == 'searchAmazonOrders') {
		//	get amazon accountsites for amazon marketplaces by accountssites id
		$amazonAccountsSites = getAmazonAccountsites($post);
		
		$from = 'amazon_orders';
		$select = '*';
		$addWhere = "
			amazonAccountSiteId = '" . $amazonAccountsSites['id_accountsite'] . "'";
		if (isset($post['searchAmazonOrderId']) && !empty($post['searchAmazonOrderId'])) {
			$addWhere.= "
				AND AmazonOrderId = '" .  $post['searchAmazonOrderId'] . "'";
		}
		$amazonOrder = SQLSelect($from, $select, $addWhere, 0, 0, 1, 'shop',  __FILE__, __LINE__);
		echo getXmlAmazonOrder($amazonOrder) . getXmlAmazonAccountsites($amazonAccountsSites);
	}
	
	/**
	 *	list amazon products
	 *
	 */	
	if ($post['action'] == 'listAmazonOrders') {

		//	get amazon accountsites for amazon marketplaces by accountssites id
		$amazonAccountsSites = getAmazonAccountsites($post);
				
		$from = 'amazon_orders';
		$select = '*';
		$addWhere = "amazonAccountSiteId = " . $amazonAccountsSites['id_accountsite'];

		if (isset($post['orderBy']) && $post['orderBy'] != 0) {
				$orderBy = $post['orderBy'];	
		} else {
			$orderBy = 'PurchaseDate DESC';
		}
		$amazonOrders = SQLSelect($from, $select, $addWhere, $orderBy, 0, $post['limit'], 'shop',  __FILE__, __LINE__);
		
		if (count($amazonOrders) > 0 ) {	
			$xml = '';
			foreach($amazonOrders as $amazonOrder)
			{
				$xml.= getXmlAmazonOrder($amazonOrder);
			}
			echo $xml . getXmlAmazonAccountsites($amazonAccountsSites);
		}
	}