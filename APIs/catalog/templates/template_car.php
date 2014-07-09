<?php

$vehiclesRow = "";
foreach($shopItems as $shopItem)
{
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
				$showVehicles.= $vehicle["BEZ1"] . ' ' . $vehicle["BEZ2"] . ' ' . $vehicle["BEZ3"] . '<br />';
			}
		}
	}		

	$criteria['htmlImgTag'] = true;
	$image = getImagesByArticleId($shopItem, $criteria);
	
	$data = array();
	$data['from'] = 't_203';
	$data['select'] = '*'; 
	$data['where'] = "
		ArtNr = '" . $shopItem['MPN'] . "'
		AND KHerNr = '" . $KHerNr . "'
	";
    $t203Results = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 2, 'shop', __FILE__, __LINE__);	
	$oeNumbers = "";
	foreach($t203Results as $t203Result)
	{
		$oeNumbers.= $t203Result['OENr'] . "<br />"; 	
	}
	
	$data = array();
	$data['from'] = 't_210';
	$data['select'] = '*'; 
	$data['where'] = "
		ArtNr = '" . $shopItem['MPN'] . "'
	";
	$t210Results = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop', __FILE__, __LINE__);
	foreach($t210Results as $t210Results)
	{
		$data = array();
		$data['from'] = 't_050';
		$data['select'] = '*'; 
		$data['where'] = "
			KritNr = '" . $t210Results['KritNr'] . "'
		";
		$t050Results = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop', __FILE__, __LINE__);
		foreach($t050Results as $t050Result)
		{
			if ($t050Result["Typ"] == "K")
			{
				$data = array();
				$data['from'] = 't_052';
				$data['select'] = '*'; 
				$data['where'] = "
					TabNr = '" . $t050Results['KritNr'] . "'
					AND Schl = '" . $t050Result['KritVal'] . "'
				";
				$t052Results = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop', __FILE__, __LINE__);				
			}
		}
	}
	
	$templateRow = '
	<tr>
		<td>' . $showVehicles . '
			<br />
			<div>
				<strong>' . $keywords . '</strong>
			</div>
			<div>' . $shopItemsVehicle['criteria'] . '</div>
		</td>
		<td style="text-align: center;"><span style="font-size: 16px"><strong>' . $shopItem['MPN'] . '</strong></span><br />' . $oeNumbers . '</td>
		<td style="text-align: center; width:200px;">' . $image . '</td>
	</tr>';	
	if (!empty($showVehicles)) 
	{	
		$vehiclesRow.= $templateRow;
	}
}

$templateCar = '
	<table>
		<thead>
			<tr>
				<th colspan="3">' . $vehicleResult['BEZ1'] . '</th>
			</tr>
			<tr>
				<th style="font-size: 12px;">Fahrzeug</th>
				<th style="font-size: 12px;">vergleichbar mit</th>
				<th style="font-size: 12px;">Bild</th>
			</tr>
		</thead>

		<tbody>';
			$templateCar.= $vehiclesRow;
			$templateCar.= '
		</tbody>
	</table>';

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
                    return '<img style="float: none;" src="' . IMAGE_NO_PATH . '">';
                }
            }

            return $images;
        }
        return '<img src="' . IMAGE_NO_PATH . '">';
    }
}
