<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for shop items images
 *	- get the shop items images for the amazon products table
 *
*******************************************************************************/

$IMAGE_LOCATION_PATH = 'http://www.mapco.de/files/';
$IMAGE_FORMAT_ID = 19;
$IMAGE_FORMAT_THUMB_ID = 8;

$starttime = time() + microtime();
$countUpdate = 0;

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');

//	keep post submit
$post = $_POST;

	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);
	
	//	reset shop items image import
	if ($post['imageImport'] == null) 
	{
		setShopItemsImageImport();
	}
	
	//	get the shop items data object
	$data['from'] = 'shop_items';
	$data['select'] = '*';
	$data['where'] = "
		active = 1
		AND ImageImport = 0
	";
	if ($post['imageImport'] != null)
	{
		$data['where'].= "
			AND ImageImport = " . $post['imageImport'];
	}
	$date['order'] = "lastmod DESC";
	$shopItemsResults = SQLSelect($data['from'], $data['select'], $data['where'], $date['order'], 0, $post["limit"], 'shop',  __FILE__, __LINE__);

	//	get amazon product by SKU and accountsite id
	$data['from'] = 'amazon_products';
	$data['select'] = 'id_product, SKU, accountsite_id, lastimageupdate, ImageLocation, ImageLocationThumbnail';
	$data['where'] = "
		accountsite_id = " . $amazonAccountsSites['id_accountsite'];
	$amazonProductsResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
	$amazonProductsList = array();
	$countUpdateQuantity = 0;
	if (count($amazonProductsResults) > 0)
	{
		foreach($amazonProductsResults as $amazonProduct)
		{
			$amazonProductsList[$amazonProduct['SKU']] =  $amazonProduct;
		}
	}

	if (count($shopItemsResults) > 0)
    {
		foreach($shopItemsResults as $item)
		{
			$images = array();
			$setNewImages = array();
			if (isset($amazonProductsList[$item['MPN']]) && $item['lastmod'] > $amazonProductsList[$item['MPN']]['lastimageupdate'])
			{
				//	get Image Location
				if ($item["article_id"] != null)
				{
					$images = array();
					$data = array();
					$data['from'] = 'cms_articles_images';
					$data['select'] = '*';
					$data['where'] = "
						article_id = '" . $item["article_id"] . "'
					";
					$articleImagesResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'web',  __FILE__, __LINE__);
					if (count($articleImagesResults) > 0)
					{
						foreach($articleImagesResults as $articleImages)
						{
							$data['from'] = 'cms_files';
							$data['select'] = '*';
							$data['where'] = "
								original_id = '" . $articleImages["file_id"] . "'
								AND imageformat_id = '" . $IMAGE_FORMAT_ID . "'
							";
							$originalFiles = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'web',  __FILE__, __LINE__);
							if (count($originalFiles) > 0)
							{
								$saveImages = null;
								foreach($originalFiles as $originalFile)
								{
									$saveImages.= $IMAGE_LOCATION_PATH . floor(bcdiv($originalFile["id_file"], 1000)) . '/' . $originalFile["id_file"] . '.' . $originalFile["extension"] . "\n";
								}

                                //	create a Thumbnail
                                $data = array();
                                $data["API"] = "cms";
                                $data["APIRequest"] = "ImageThumbnail";
                                $data['APICleanRequest'] = true;
                                $data["id_file"] = $originalFile['id_file'];
                                $images['imageLocationThumb'] = post(PATH."soa2/", $data);
							} else {
                                //	clear old images in imageLocation and imageLocationThumb
								$saveImages = "";
                                $images['imageLocationThumb'] = "";
                            }
						}
					}
				}
				
				//	update amazon products with images
				$data = array();
				$data['lastmod_user'] = 10;
				$data['lastmod'] = time();
				$data['lastimageupdate'] = time();
				$data['submitedProduct'] = 0;
				$data['submitedImage'] = 0;
				$data['upload'] = 1;
				$data['ImageLocation'] = $saveImages;
				$data['ImageLocationThumbnail'] = $images['imageLocationThumb'];
				$addWhere = "
					id_product = " . $amazonProductsList[$item['MPN']]['id_product'];
				SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);

				//	count images updates
				$countUpdate++;
			}

			//	set shop items imageImport status
			$updateShopItems[] = $item['id_item'];

			$stoptime = time() + microtime();
			if ($stoptime-$starttime > 60) 
			{
				$xmlNextCall.= '	  <NextCall>' . (time() + 180) . '</NextCall>' . "\n";
				break;
			};
		}

		//	set ImageImport in shop items
		if (sizeof($updateShopItems) > 0)
		{
			$data = array();
			$data['ImageImport'] = 1;
			$addWhere = "
				id_item IN (" . implode(", ", $updateShopItems) . ")
			";
			SQLUpdate('shop_items', $data, $addWhere, 'shop', __FILE__, __LINE__);
		}
	}
	$xml = "\n" . "<AmazonShopItemsImages>" . "\n";
	$xml.= '<marketplace>' . $amazonAccountsSites['name'] . '</marketplace>' . "\n";
	$xml.= $xmlNextCall;
	$xml.= '	<update>Update Images: ' . $countUpdate . '</update>' . "\n";
	$xml.= '</AmazonShopItemsImages>'. "\n";
	echo $xml;
