<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Job Service for shop items
 *	- get the latest shop items data into the amazon products table
 *
 *	@params
 *	- $submitTypes
 * 	-- updates, data, images, quantities, prices
 *
 *	- images
 *	-- import (only for import images into the first marketplace (german))
 *
 *	- post
 *	-- account_id, marketplaceID, limit, import
*******************************************************************************/

//	keep post submit
$post = $_POST;

	//	set alternative limit value
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
     *	Job for data updating into the amazon products table
     *	- job for intern data
     *
     */
    if ($post['submitType'] == 'data' || $post['submitType'] == 'updates') 
	{
        if ($amazonAccountsSitesCount > 0) 
		{
            while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
            {
                $post_data = array();
                $post_data['API'] = "amazon";
                $post_data['APIRequest'] = "AmazonShopItemsProductGet";
                $post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				$post_data['language_id'] = $amazonAccountsSite['language_id'];

                //  set params
                $post_data['submitType'] = $post['submitType'];
                $post_data['limit'] = $post['limit'];
                echo soa2($post_data, __FILE__, __LINE__, 'xml');
            }
			mysqli_data_seek($amazonAccountsSitesResults, 0);
        }
    }
	
    /**
     *	Job for subtitle updating into the amazon products table
     *	- job for intern data
     *
     */
    if ($post['submitType'] == 'subtitle') 
	{
        if ($amazonAccountsSitesCount > 0) 
		{
            while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
            {
                $post_data = array();
                $post_data['API'] = "amazon";
                $post_data['APIRequest'] = "AmazonShopItemsSubTitle";
                $post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				$post_data['language_id'] = $amazonAccountsSite['language_id'];

                //  set params
                $post_data['submitType'] = $post['submitType'];
				$post_data['updateDescription'] = $post['updateDescription'];
                $post_data['limit'] = $post['limit'];
                echo soa2($post_data, __FILE__, __LINE__, 'xml');
            }
			mysqli_data_seek($amazonAccountsSitesResults, 0);
        }
    }	

    /**
     *	Job for image updating into the amazon products table
     *	- job for intern data
     *	- dont use accountsite id, copy the DE default images into the other accountsites
     */
    if ($post['submitType'] == 'images' || $post['submitType'] == 'updates') 
	{
		if ($amazonAccountsSitesCount > 0) 
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonShopItemsImagesGet";
				$post_data['account_id'] = $post['account_id'];
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				$post_data['name'] = 'Deutschland';
		
				//  set params
				$post_data['limit'] = $post['limit'];
				$post_data['import'] = $post['import'];
				$post_data['imageImport'] = $post['imageImport'];
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
    }

    /**
     *	Job for qantity updating into the amazon products table
     *	- job for intern data
     *
     */
    if ($post['submitType'] == 'quantities' || $post['submitType'] == 'updates') 
	{
        if ($amazonAccountsSitesCount > 0) 
		{
            while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
            {
                $post_data = array();
                $post_data['API'] = "amazon";
                $post_data['APIRequest'] = "AmazonShopItemsQuantitiesGet";
                $post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				$post_data['language_id'] = $amazonAccountsSite['language_id'];

                //  set params
                $post_data['limit'] = $post['limit'];
                echo soa2($post_data, __FILE__, __LINE__, 'xml');
            }
			mysqli_data_seek($amazonAccountsSitesResults, 0);
        }
    }

    /**
     *	Job for price updating into the amazon products table
     *	- job for intern data
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
                $post_data['APIRequest'] = "AmazonShopItemsPriceGet";
                $post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];
				$post_data['language_id'] = $amazonAccountsSite['language_id'];

                //  set params
                $post_data['limit'] = $post['limit'];
                echo soa2($post_data, __FILE__, __LINE__, 'xml');
            }
			mysqli_data_seek($amazonAccountsSitesResults, 0);
        }
    }