<?php
/***
 *	@author: rlange@mapco.de
 *	Amazon Service for Categories get
 *	- 
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

// keep post submit
$post = $_POST;

	// get the amazon categories for listing
	if ($post['action'] == 'listAmazonCategories') {		
		
		// get account sites by account id
		$amazonAccountsSitesQuery = "
			SELECT * 
			FROM amazon_accounts_sites 
			WHERE id_accountsite = " . $post['accountsite_id'] . "";
		$amazonAccountsSitesResult = q($amazonAccountsSitesQuery, $dbshop, __FILE__, __LINE__);	
		$amazonAccountsSites = mysqli_fetch_assoc($amazonAccountsSitesResult);
		$xmlSites = '<amazonSites>' . "\n";
		$xmlSites.= '	<id_accountsite>' . $amazonAccountsSites['id_accountsite'] . '</id_accountsite>' . "\n";
		$xmlSites.= '	<account_id>' . $amazonAccountsSites['account_id'] . '</account_id>' . "\n";
		$xmlSites.= '	<active>' . $amazonAccountsSites['active'] . '</active>' . "\n";
		$xmlSites.= '	<marketplace_id>' . $amazonAccountsSites['marketplace_id'] . '</marketplace_id>' . "\n";
		$xmlSites.= '	<language_id>' . $amazonAccountsSites['language_id'] . '</language_id>' . "\n";
		$xmlSites.= '</amazonSites>' . "\n";
		
		//get amazon marketplaces
		$amazonMarketplacesQuery = "
			SELECT *
			FROM amazon_marketplaces
			WHERE id_marketplace = " . $amazonAccountsSites['marketplace_id'] . "";
		$amazonMarketplacesResult = q($amazonMarketplacesQuery, $dbshop, __FILE__, __LINE__);
		$amazonMarketplaces = mysqli_fetch_assoc($amazonMarketplacesResult);
		$xmlMarketplaces = '<amazonMarketplaces>' . "\n";
		$xmlMarketplaces.= '	<id_marketplace>' . $amazonMarketplaces['id_marketplace'] . '</id_marketplace>' . "\n";
		$xmlMarketplaces.= '	<country_code>' . $amazonMarketplaces['country_code'] . '</country_code>' . "\n";
		$xmlMarketplaces.= '	<name>' . $amazonMarketplaces['name'] . '</name>' . "\n";
		$xmlMarketplaces.= '	<MarketplaceID>' . $amazonMarketplaces['MarketplaceID'] . '</MarketplaceID>' . "\n";
		$xmlMarketplaces.= '</amazonMarketplaces>' . "\n";	
		
		$amazonCategoriesCountQuery = "
			SELECT * 
			FROM amazon_categories 
			WHERE accountsite_id = " . $amazonAccountsSites['id_accountsite'] . "
			ORDER BY GART";
		$amazonCategoriesCountResult = q($amazonCategoriesCountQuery, $dbshop, __FILE__, __LINE__);
		$amazonCategoriesCount = mysqli_num_rows($amazonCategoriesCountResult);	
		$xmlCategoriesCount = '<amazonCategoriesCount><categoriesTotal>' . $amazonCategoriesCount . '</categoriesTotal></amazonCategoriesCount>' . "\n";
		
		// get browsenodes
		$browsenode = array();
		$browsenodeQuery = "
			SELECT * 
			FROM amazon_browsenodes
			WHERE marketplace_id = " . $amazonAccountsSites['marketplace_id'] . "";
		$browsenodeResults = q($browsenodeQuery, $dbshop, __FILE__, __LINE__);
		$xmlCategories = "";
		while ($browsenodeResult = mysqli_fetch_assoc($browsenodeResults))
		{
			$browsenode[$browsenodeResult["BrowseNodeId"]] = str_replace("&nbsp;", "", $browsenodeResult["Category"]);
		}	
		
		// browsenodes not available, we have too import this
		if (mysqli_num_rows($browsenodeResults) > 0) 
		{	
			//we need a string to a certain length with another string
			$language_id = str_pad($amazonAccountsSites['language_id'], 3, "0", STR_PAD_LEFT);
		
			// get the amazon categories by account id
			$amazonCategoriesQuery = "
				SELECT * 
				FROM amazon_categories 
				WHERE accountsite_id = " . $amazonAccountsSites['id_accountsite'] . "
				ORDER BY GART";
			$amazonCategoriesResult = q($amazonCategoriesQuery, $dbshop, __FILE__, __LINE__);
			$amazonCategories = mysqli_num_rows($amazonCategoriesResult);
			
			while ($amazonCategory = mysqli_fetch_assoc($amazonCategoriesResult))
			{
				$t_320Query = "
					SELECT BezNr 
					FROM t_320 
					WHERE GenArtNr = " . $amazonCategory["GART"] . ";";
				$t_320Result = q($t_320Query, $dbshop, __FILE__, __LINE__);
				
				$xmlCategories.= '<amazonCategories>' . "\n";
				if ($amazonCategory["GART"] == 0) 
				{
					$xmlCategories.= '	<others>Sonstiges</others>' . "\n";
				} else {
					
					// problem with '001' numbers
					$t_320 = mysqli_fetch_assoc($t_320Result);
					if ($t_320["BezNr"] != null) 
					{
						$t_030Query = "
							SELECT * 
							FROM t_030 
							WHERE SprachNr = " . $language_id . "	 
							AND BezNr = " . $t_320["BezNr"] . ";";
						$t_030Result = q($t_030Query, $dbshop, __FILE__, __LINE__);
						$t_030 = mysqli_fetch_assoc($t_030Result);
						$xmlCategories.= '<Bez>' . $t_030["Bez"] . '</Bez>' . "\n";
					} 
				}
		
				$xmlCategories.= '	<BrowseNodeId1><![CDATA[' . $browsenode[$amazonCategory["BrowseNodeId1"]] . ']]></BrowseNodeId1>' . "\n";
				$xmlCategories.= '	<BrowseNodeId2><![CDATA[' . $browsenode[$amazonCategory["BrowseNodeId2"]] . ']]></BrowseNodeId2>' . "\n";
				$xmlCategories.= '	<amazonCategoryID>' . $amazonCategory["id"] . '</amazonCategoryID>' . "\n";
				$xmlCategories.= '	<amazonCategoryBrowseNodeId1>' . $amazonCategory["BrowseNodeId1"] . '</amazonCategoryBrowseNodeId1>' . "\n";
				$xmlCategories.= '	<amazonCategoryBrowseNodeId2>' . $amazonCategory["BrowseNodeId2"] . '</amazonCategoryBrowseNodeId2>' . "\n";
				$xmlCategories.= '</amazonCategories>' . "\n";
			}
		}
		echo $xmlSites . $xmlMarketplaces . $xmlCategoriesCount . $xmlCategories;
	}

	// update amazon categories
	if ($post["action"] == "editAmazonCategories")
	{
		$data = array();
		$data["BrowseNodeId1"] = $post["BrowseNodeId1"];
		$data["BrowseNodeId2"] = $post["BrowseNodeId2"];
	
		$data["lastmod"] = time();
		$data["lastmod_user"] = $_SESSION["id_user"];
		$addWhere = "WHERE id = " . $post["id"] . ";";
		$results = q_update("amazon_categories", $data, $addWhere, $dbshop, __FILE__, __LINE__);
	}