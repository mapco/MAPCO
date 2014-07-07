<?php

/***
 * @author: rlange@mapco.de
 * Catalog API
 *
 * @params
 * - $submitTypes
 * -- language_id, vehicle_id
 *
 *
 *******************************************************************************/
require_once('/usr/www/users/admapco/APIs/apiCore/Images.php');

$templateLoad = 'templates/template_';
$GART = '00247';

// keep post submit
$post = $_POST;

if (isset($post['action']) && $post['action'] == 'showCatalog')
{
    //	get vehicle data
	$data = array();
    $data['from'] = 'vehicles_de';
    $data['select'] = '*';
    $data['where'] = "
		id_vehicle = " . $post["vehicle_id"];
    $vehicleResult = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop', __FILE__, __LINE__);

	//	get shop items keyword
	$data = array();
	$data['from'] = 'shop_items_keywords';
	$data['select'] = 'id, GART, language_id, ordering, keyword';
	$data['where'] = "
		GART = " . $GART . "
		AND language_id = 1";
	$data['orderBy'] = 'ordering ASC';
	$shopItemsKeyword = SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, 1, 'shop',  __FILE__, __LINE__);

	//	get shop items by GART
	$data = array();
	$data['from'] = 'shop_items';
	$data['select'] = '*';
	$data['where'] = "
		active = 1
		AND GART = " . $GART;
	$date['order'] = "lastmod DESC";
	$shopItems = SQLSelect($data['from'], $data['select'], $data['where'], $date['order'], 0, 10, 'shop',  __FILE__, __LINE__);

    include($templateLoad . 'car.php');

    echo '<showCatalog><![CDATA[' . $templateCar . ']]></showCatalog>';
}


