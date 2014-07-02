<?php
/***
 *	@author: rlange@mapco.de
 *	Amazon Job Service for shop items
 *	- get the latest shop items data into the amazon products table
 *
 *	@params
 *	- $submitTypes
 * 	-- updates, data, images, qantities, prices, asin, taskAsin
 *	- data
 *	-- account_id, marketplaceID
 *
 *	- prices
 *	-- CriticalPrice
 *
*******************************************************************************/

session_start();
include("../config.php");

//	keep post submit
$post = $_POST;

	//	if set post limit, then use it, otherwise use 0
	if (isset($post['limit']) && $post['limit'] > 0) 
	{
		$postLimit = $post['limit'];
	} else {
		$postLimit = 0;
	}
	
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
	 *	Job for updating all new product data
	 *
	 *
	 */
	if ($post['submitType'] == 'data') 
	{
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "ProductDataGet";
				$post_data['MessageType'] = "Product";
				$post_data['action'] = "SubmitFeed";
				$post_data['FeedType'] = "_POST_PRODUCT_DATA_";
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['limit'] = $postLimit;
				$post_data['id_product'] = $post['productID'];
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}
	
	/**
	 *  Update product images into the amazon products image location
	 *  - use for all or for a product id
	 *
	 */
	if ($post['submitType'] == 'images' || $post['submitType'] == 'updates') 
	{
		//  set an default limit by zero
		if ($postLimit == 0) 
		{
			$postLimit = 2000;
		}

		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "ProductImagesGet";
				$post_data['MessageType'] = "ProductImage";
				$post_data['action'] = "SubmitFeed";
				$post_data['FeedType'] = "_POST_PRODUCT_IMAGE_DATA_";
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['id_product'] = $post['id_product'];
				$post_data['limit'] = $postLimit;
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}
	
	/**
	 *  Update product quantities into the amazon products quantities
	 *  - use for all or for one product id
	 *
	 */
	if ($post['submitType'] == 'qantities' || $post['submitType'] == 'updates') 
	{
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "ProductQantitiesGet";
				$post_data['MessageType'] = "Inventory";
				$post_data['action'] = "SubmitFeed";
				$post_data['FeedType'] = "_POST_INVENTORY_AVAILABILITY_DATA_";
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				// set params
				$post_data['id_product'] = $post['id_product'];
				$post_data['limit'] = $postLimit;
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}
	
	/** 
	 *	Update the amazon products prices into the amazon product prices 
	 *	- use for all or for a product id
	 *
	 */
	if ($post['submitType'] == 'prices' || $post['submitType'] == 'updates') 
	{
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "ProductPricesGet";
				$post_data['MessageType'] = "Price";
				$post_data['action'] = "SubmitFeed";
				$post_data['FeedType'] = "_POST_PRODUCT_PRICING_DATA_";
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['id_product'] = $post['id_product'];
				$post_data['limit'] = $postLimit;
				$post_data['CriticalPrice'] = $post['CriticalPrice'];
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}
	
	/**
	 *	Import the amazon ASIN into the amazon products table
	 *	- use for all or for a product id
	 *
	 */
	if ($post['submitType'] == 'asin')  
	{
		//  set an default limit by zero
		if ($postLimit == 0) 
		{
			$postLimit = 1000;
		}
		
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonProductAsinImport";
				$post_data['action'] = "GetMatchingProductForId";
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['id_product'] = $post['id_product'];
				$post_data['limit'] = $postLimit;
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}
	
	/**
	 *	Import the amazon ASIN into the amazon products table
	 *	- use for all or for a product id
	 *
	 */
	if ($post['submitType'] == 'taskAsin')  
	{
		//  set an default limit by zero
		if ($postLimit == 0) 
		{
			$postLimit = 1000;
		}
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{		
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonProductsTasks";
				$post_data['action'] = $post['submitType'];
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['id_product'] = $post['id_product'];
				$post_data['limit'] = $postLimit;
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
		}
	}
	
	/**
	 *	Match Amazon Inventory with Amazon Products
	 *
	 */
	if ($post['submitType'] == 'matchInventory')  
	{
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{		
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonProducts";
				$post_data['action'] = $post['submitType'];
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
		}
	}
	
	/**
	 *	Job for delete amazon products
	 *
	 *
	 */
	if ($post['submitType'] == 'deleteProducts') 
	{
		//  set an default limit by zero
		if ($postLimit == 0) 
		{
			$postLimit = 10000;
		}		
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "ProductDelete";
				$post_data['MessageType'] = "Product";
				$post_data['action'] = "SubmitFeed";
				$post_data['FeedType'] = "_POST_PRODUCT_DATA_";
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				
				//  set params
				$post_data['limit'] = $postLimit;
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}	