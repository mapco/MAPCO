<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Job Service for offer import
 *	- 
 *
 *	@params
 *	- $submitTypes
 *	-- import, update, criticalPrice, importShopPriceResearch
 *
 *	- account_id
 *	- marketplaceID
 *	- ASIN
 *	- limit
 *
*******************************************************************************/

$start = time();

// keep post submit
$post = $_POST;

	//	get amazon accountsites by account id
	$amazonAccountsSitesResults = null;
	if (isset($post['account_id']) && $post['account_id'] != 0) 
	{
		$amazonAccountsSitesQuery = "
		SELECT *
		FROM amazon_accounts_sites
		WHERE account_id = '" . $post['account_id'] . "'";
		//	and by marketplace id
		if (isset($post['marketplaceID']) && $post['marketplaceID'] != null) 
		{
			$amazonAccountsSitesQuery.= "
			AND marketplace_id = '" . $post['marketplaceID'] . "'";
		} else {
			$amazonAccountsSitesQuery.= "
				ORDER BY marketplace_id ASC";			
		}
		$amazonAccountsSitesResults = q($amazonAccountsSitesQuery, $dbshop, __FILE__, __LINE__);
		$amazonAccountsSitesCount = mysqli_num_rows($amazonAccountsSitesResults);
	}

	/**
	 *	Job for offer import listing for asin
	 *
	 *
	 */
	if ($post['submitType'] == 'import')  
	{
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonOffersImport";
				$post_data['action'] = 'GetLowestOfferListingsForASIN';
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['limit'] = $post['limit'];
				$post_data['ASIN'] = $post['ASIN'];
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}
	
	/**
	 *	Job for offer updates
	 *
	 *
	 */
	if ($post['submitType'] == 'update') 
	{
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonOffers";
				$post_data['action'] = $post['submitType'];
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['limit'] = $post['limit'];
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}
	
	/**
	 *	Job for amazon products for detect critical price
	 *
	 *
	 */
	if ($post['submitType'] == 'criticalPrice') 
	{
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonOffers";
				$post_data['action'] = $post['submitType'];
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['limit'] = $post['limit'];
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}
	
	/**
	 *	Job for import amazon offers into the shop price research table
	 *	- set limit (500er)
	 *
	 */
	if ($post['submitType'] == 'importShopPriceResearch') 
	{
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonOffers";
				$post_data['action'] = $post['submitType'];
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['limit'] = $post['limit'];
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}