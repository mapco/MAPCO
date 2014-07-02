<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Job Service for offer import
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