<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Job Service for report import
 *	- 
 *
 *	@params
 *	- $submitTypes
 *	-- 
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
	 *	Job for import amazon report lists
	 *
	 *
	 */
	if ($post['submitType'] == 'getFeedSubmissionList') 
	{
		if ($amazonAccountsSitesCount > 0)
		{
			while($amazonAccountsSite = mysqli_fetch_assoc($amazonAccountsSitesResults))
			{
				$post_data = array();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonSubmissionResultGet";
				$post_data['accountsite_id'] = $amazonAccountsSite['id_accountsite'];

				//  set params
				$post_data['limit'] = $post['limit'];
				echo soa2($post_data, __FILE__, __LINE__, 'xml');
			}
			mysqli_data_seek($amazonAccountsSitesResults, 0);
		}
	}	