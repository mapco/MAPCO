<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for shop items
 *	- generate new amazon products from shop items
 *
 * @params
 * -
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');

$starttime = time() + microtime();
$countInsert = 0;
$countUpdate = 0;
$languageIds = getLanguageIds();

//	keep post submit
$post = $_POST;

	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);
	
	/**
	 *	---------------------------------------- update description ----------------------------------------------------------------------
	 */
	
	if ($post['updateDescription'] == 'updateDescription')
	{
		//	get amazon products
		$data = array();
		$data['from'] = 'amazon_products';
		$data['select'] = 'id_product, accountsite_id, item_id, SKU, lastmod, Title, SubTitle';
		$data['where'] = "
			accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "'
			AND SubTitle != ''
			AND importSubTitle = 1
			AND bundle = 0
		";			
		$amazonProductsResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, $post['limit'], 'shop',  __FILE__, __LINE__);	
		if (count($amazonProductsResults) > 0) 
		{
			//	DE
			if ($amazonAccountsSites['language_id'] == 1) {
				$subtext_title = ' ist für folgende Fahrzeuge geeignet:';
				$subtext_description = 'Für weitere Fragen erreichen Sie uns unter  0800 / 2060666';
			}
			//	GB
			if ($amazonAccountsSites['language_id'] == 2) {
				$subtext_title = ' Is suitable for the following vehicles:';
				//$subtext_description = 'Feel free to contact us at';
			}
			// IT
			if ($amazonAccountsSites['language_id'] == 5) {
				$subtext_title = ' è adatto per i seguenti veicoli:';
				//$subtext_description = 'Per ulteriori informazioni, contatteci al numero 0800/2060666';
			}
			//	FR
			if ($amazonAccountsSites['language_id'] == 6) {
				$subtext_title = '';
				$subtext_description = '';
			}			
			//	ES		
			if ($amazonAccountsSites['language_id'] == 7) {
				$subtext_title = " es adecuado para los siguientes vehículos:";
				//$subtext_description = 'Si tiene más preguntas, llámenos al 0800/2060666';
			}																		

			foreach($amazonProductsResults as $amazonProduct)
			{
				$description = '<strong>' . $amazonProduct['Title'] . '<strong>' . $subtext_title . '<br><br>';
				$description.= $amazonProduct['SubTitle'] . '<br><br>';
				$description.= $subtext_description;
				
				$data = array();
				$data['Description'] = $description;
				$data['importSubTitle'] = 2;
				$addWhere = "
					id_product = " . $amazonProduct['id_product'];
				SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
				$countUpdate++;
			}
		}
		$xml = "\n" . "<AmazonShopItemsDescription>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= '	<update>Update Descriptions: ' . $countUpdate . '</update>' . "\n";
		$xml.= '</AmazonShopItemsDescription>'. "\n";
		echo $xml;	
	}
		
	/**
	 *	---------------------------------------- create or update subTitles ---------------------------------------------------------------
	 */	
	
	if ($post['updateDescription'] != 'updateDescription') 
	{	
		//	get amazon products
		$data = array();
		$data['from'] = 'amazon_products';
		$data['select'] = 'id_product, accountsite_id, item_id, SKU, lastmod, importSubTitle';
		$data['where'] = "
			accountsite_id = '" . $amazonAccountsSites['id_accountsite'] . "'
			AND importSubTitle = 0
		";			
		$amazonProductsResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, $post['limit'], 'shop',  __FILE__, __LINE__);
		if (count($amazonProductsResults) > 0) 
		{
			$updateAmazonProducts = array();
			foreach ($amazonProductsResults as $item)
			{
				$bez1 = array();
				$bez2 = array();
				$k = 0;	
				
				//	get shop items vehicles
				$data = array();
				$data['from'] = 'shop_items_vehicles';
				$data['select'] = 'id, language_id, item_id, vehicle_id';
				$data['where'] = " 
					item_id = ". $item['item_id'] . "
					AND language_id=" . $amazonAccountsSites['language_id'];
				$shopItemsVehiclesResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop', __FILE__, __LINE__);				
				if (count($shopItemsVehiclesResults) > 0) 
				{
					foreach($shopItemsVehiclesResults as $shopItemsVehicle)
					{	
						if ($shopItemsVehicle['item_id'] == $item['item_id']) 
						{
							$data = array();
							$data['from'] = 'vehicles_' . $languageIds[$amazonAccountsSites['language_id']];
							$data['select'] = 'id_vehicle, BEZ1, BEZ2';
							$data['where'] = "
								id_vehicle = " . $shopItemsVehicle["vehicle_id"];
							$vehiclesDEResult = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop', __FILE__, __LINE__);
							$bez1[$k] = $vehiclesDEResult["BEZ1"];
							$bez2[$k] = $vehiclesDEResult["BEZ2"];
							if (strpos($bez2[$k], "(") > 0) 
							{
								$bez2[$k] = substr($bez2[$k], 0, strpos($bez2[$k], "(") - 1);
							}
						}
						$k++;
					}
				}
				array_multisort($bez1, $bez2);
				
				//remove sub models
				$make = array();
				$model = array();
				$testbez2 = "___";
				$k = 0;
				for( $k = 0; $k < sizeof($bez2); $k++)
				{
					$state = strpos($bez2[$k], $testbez2 . " ");
					if ( ($state === false or $state > 0) and $bez2[$k] != $testbez2) 
					{
						$make[] = $bez1[$k];
						$model[] = $bez2[$k];
						$testbez2 = $bez2[$k];
					}
				}
				$bez1 = $make;
				$bez2 = $model;
				array_multisort($bez1, $bez2);
				
				//remove repeated brands
				$vehicles = "";
				$testbez1 = "";
				$testbez2 = "";
				$k=0;
				for($k = 0; $k < sizeof($bez1); $k++)
				{
					if ( $testbez1!=$bez1[$k] )
					{
						$vehicles.= $bez1[$k];
						$testbez1 = $bez1[$k];
					}
					if ( $testbez2 != $bez2[$k] )
					{
						$vehicles.= " ".$bez2[$k];
						$testbez2=$bez2[$k];
						if (($k+1) < sizeof($bez1)) $vehicles.= ", ";
					}
				}
					
				$updateAmazonProducts[$item['id_product']]['subTitle'] = $vehicles;
				$updateAmazonProducts[$item['id_product']]['id_product'] = $item['id_product'];
				$productsIds[] = $item['id_product'];
	
				//	count product update
				$countUpdate++;
			}
			
			unset($amazonProductsResults);
	
			if (sizeof($updateAmazonProducts) > 0) 
			{
				$data = array();
				$data['lastmod'] = time();
				$data['lastmod_user'] = getAmazonSessionUserId();
				$data['submitedProduct'] = 0;
				$data['importSubTitle'] = 1;
				$data['upload'] = 1;
				$caseSubTitle = "SubTitle = CASE";
				foreach($updateAmazonProducts as $updateAmazonProduct)
				{
					$caseSubTitle.= "\n WHEN id_product = " . $updateAmazonProduct['id_product'] . " THEN '" . $updateAmazonProduct['subTitle'] . "'";
				}
				$caseSubTitle.= "
					END
					WHERE id_product IN (" . implode(", ", $productsIds) . ")
				";
				$addWhere = $caseSubTitle;
				SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
			}		
		}
		$xml = "\n" . "<AmazonShopItemsSubTitle>" . "\n";
		$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
		$xml.= $xmlNextCall;
		$xml.= '	<insert>Insert SubTitle: ' . $countInsert . '</insert>' . "\n";
		$xml.= '	<update>Update SubTitle: ' . $countUpdate . '</update>' . "\n";
		$xml.= '</AmazonShopItemsSubTitle>'. "\n";
		echo $xml;
	}
