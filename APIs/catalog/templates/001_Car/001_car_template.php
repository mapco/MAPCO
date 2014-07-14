<?php

$vehiclesRow = "";
foreach($shopItems as $shopItem)
{
	//	get criterias
	$data = array();
	$data['from'] = 'shop_items_de';
	$data['select'] = '*';
	$data['where'] = "
		id_item = " . $shopItem['id_item'];
	$shopItemsDe = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop', __FILE__, __LINE__);
	$criterias = explode("; ", $shopItemsDe["short_description"]);
	$criteriasList = null;
	$criteriaTemp['Einbauseite'] = array();
	foreach($criterias as $criteria)
	{
		$pos = strpos($criteria, 'Einbauseite');
		if ($pos !== false) 
		{
			$criteriaTemp['Einbauseite'][] = str_replace('Einbauseite:', "", $criteria);
		} else {
			$criteria = str_replace('Lagerungsart:', "", $criteria);
			$criteria = str_replace('Farbe:', "", $criteria);
			$pos = strpos($criteria, 'Gewicht');
			if ($pos === false) 
			{
				$criteriasList.= $criteria . ', ';
			}
		}
	}
	if (count($criteriaTemp['Einbauseite']) > 0) 
	{
		$criteriaTempList = 'Einbauseite:' . implode(',', $criteriaTemp['Einbauseite']) . ', ';		
	}
	$criteriasList = rtrim($criteriaTempList . $criteriasList, ', '); 
		
	//	get vehicles
	$data = array();
	$data['from'] = 'shop_items_vehicles';
	$data['select'] = '*';
	$data['where'] = "
		item_id = " . $shopItem['id_item'] . "
		AND language_id = 1	
	";
	$shopItemsVehicles = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop', __FILE__, __LINE__);		
	$showVehicles = "";
	if (count($shopItemsVehicles) > 0)
	{
		foreach($shopItemsVehicles as $shopItemsVehicle)
		{
			//	get vehicles
			$data = array();
			$data['from'] = 'vehicles_de';
			$data['select'] = '*';
			$data['where'] = "
				id_vehicle = " . $shopItemsVehicle['vehicle_id'] . "
				AND KHerNr = " .$KHerNr;
			$vehicles = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop', __FILE__, __LINE__);
			if (count($vehicles) > 0) {
				foreach($vehicles as $vehicle)
				{
					$stringOne = '<b>' . $vehicle["BEZ1"] . ' ' . $vehicle["BEZ2"] . '</b>';
					$stringTwo = $stringTemp;
					
					if ($stringOne == $stringTwo) 
					{
						$showVehicles.= $vehicle["BEZ3"] . ', ';
					} else {
						if (empty($stringTemp)) 
						{
							$showVehicles.= $stringOne . ' ' . $vehicle["BEZ3"] . ', ';
						} else {
							$showVehicles.= '<br />' . $stringOne . ' ' . $vehicle["BEZ3"];
						}
					}
					$stringTemp = $stringOne;
				}
			}
		}
	}

	$setCriteria['htmlImgTag'] = true;
	$image = getImagesByArticleId($shopItem, $setCriteria);
	
	$data = array();
	$data['from'] = 't_203';
	$data['select'] = '*'; 
	$data['where'] = "
		ArtNr = '" . $shopItem['MPN'] . "'
		AND KHerNr = '" . $KHerNr . "'
	";
    $t203Results = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 2, 'shop', __FILE__, __LINE__);	
	$oeNumbers = "";
	if (count($t203Results) > 0) 
	{
		foreach($t203Results as $t203Result)
		{
			$oeNumbers.= $t203Result['OENr'] . "<br />"; 	
		}
	}	
	
	// build template body
	$templateRow = '
	<tr class="content-list">
		<td class="description">
			<strong>' . $keywords . '</strong><br /><br />
			' . $showVehicles . '<br /><br />
			<i class="fa fa-info-circle"></i>Hinweis: ' . $criteriasList . '
		</td>
		<td class="center numbers">
			<span class="mpn"><strong>' . $shopItem['MPN'] . '</strong></span><br /><br />' . $oeNumbers . '
		</td>
		<td class="center image">' . $image . '</td>
	</tr>';	
	if (!empty($showVehicles)) 
	{	
		$vehiclesRow.= $templateRow;
	}
}

$showTemplate = "";
$showTemplateHeadline = $vehicleResult['BEZ1'];

if (!empty($vehiclesRow))
{
	$templateCar = '
		<table class="vehicles-catalog" cellspacing="0" cellpadding="5">
			<thead>
				<tr class="headline">
					<th colspan="3">' . $showTemplateHeadline . '</th>
				</tr>
				<tr class="navigation">
					<th class="description">Fahrzeuge</th>
					<th class="center numbers">vergleichbar mit</th>
					<th class="center image">Bild</th>
				</tr>
			</thead>
			<tbody>' . $vehiclesRow . '</tbody>
		</table>';
}
$showTemplate = $templateCar;


	/*
	$post_data = array();
	$post_data['API'] = "cms";
	$post_data['APIRequest'] = "ArticleImagesGet";
	$post_data['article_id'] = $shopItem['article_id'];
	$post_data['lang'] = 'de';
	$response = soa2($post_data, __FILE__, __LINE__, 'obj_to_arr');
	foreach($response['image'] as $image)
	{
		if (!empty($image['original_path'])) 
		{
			$image = '<img src="' . $image['original_path'] . '">';	
		} else {
			$image = "";	
		}
	}
	*/

/**
 * Returns an image by article id
 *
 * @param $item
 * @param array $criteria
 * @return array
 *
 * @criteria
 *	- htmlImgTag - returns a html img tag <img src="">
 */
function getImagesByArticleId($item, $criteria = array())
{
	
/***
 * @author: rlange@mapco.de
 * CORE API - Images
 *
 *******************************************************************************/

DEFINE('IMAGE_LOCATION_PATH',        	'http://www.mapco.de/files/');
DEFINE('IMAGE_NO_PATH',					'http://www.mapco.de/files_thumbnail/0.jpg');
DEFINE('IMAGE_FORMAT_ID',            	19);
DEFINE('IMAGE_FORMAT_THUMB_ID',      	8);
	
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
                    AND imageformat_id = '" . IMAGE_FORMAT_ID . "'
                ";
                $originalFiles = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'web',  __FILE__, __LINE__);
                if (count($originalFiles) > 0)
                {
                    $saveImages = null;
                    foreach($originalFiles as $originalFile)
                    {
                        $saveImages.= IMAGE_LOCATION_PATH . floor(bcdiv($originalFile["id_file"], 1000)) . '/' . $originalFile["id_file"] . '.' . $originalFile["extension"] . "\n";
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

            if (isset($criteria['htmlImgTag']) && $criteria['htmlImgTag'] == true)
            {
                if (!empty($images['imageLocationThumb'])) {
                    return '<img style="float: none;" src="' . $images['imageLocationThumb'] . '">';
                } else {
                    //return '<img style="float: none;" src="' . IMAGE_NO_PATH . '">';
                }
            }

            return $images;
        }
        //return '<img src="' . IMAGE_NO_PATH . '">';
    }
}
