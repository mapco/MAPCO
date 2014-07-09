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
include("../functions/cms_core.php");

$templateLoad = 'templates/template_';
$GART = '00247';

// keep post submit
$post = $_POST;

if (isset($post['action']) && $post['action'] == 'showCatalog')
{
	if (isset($post['KHerNr']) && $post['KHerNr'] != null) 
	{
		$KHerNr = $post['KHerNr'];
	} else {
		$KHerNr = '00093';
	}	
	
	//	get shop items by GART (category)
	$data = array();
	$data['from'] = 'shop_items';
	$data['select'] = '*';
	$data['where'] = "
		active = 1
		AND GART = " . $GART;
	$date['order'] = "lastmod DESC";
	$shopItems = SQLSelect($data['from'], $data['select'], $data['where'], $date['order'], 0, 0, 'shop',  __FILE__, __LINE__);
	
    //	get vehicles by KHerNr
	$data = array();
    $data['from'] = 'vehicles_de';
    $data['select'] = '*';
    $data['where'] = "
		KHerNr = '" . $KHerNr . "'
	";
    $vehicleResult = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop', __FILE__, __LINE__);
	
	//$results=q("SELECT * FROM vehicles_".$_SESSION["lang"]." WHERE Exclude=0 GROUP BY KHerNr ORDER BY BEZ1;", $dbshop, __FILE__, __LINE__);
	
	//	get shop items keyword by GART (category)
	$data = array();
	$data['from'] = 'shop_items_keywords';
	$data['select'] = 'id, GART, language_id, ordering, keyword';
	$data['where'] = "
		GART = " . $GART;
	$data['orderBy'] = 'ordering ASC';
	$shopItemsKeywords = SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, 3, 'shop',  __FILE__, __LINE__);
	foreach($shopItemsKeywords as $shopItemsKeyword)
	{
		$keywords.= $shopItemsKeyword['keyword'] . " - ";
	}
	$pos = strrpos($keywords, "-");
	if ($pos !== false) {
		$keywords = substr($keywords,0, $pos);
	}

    include($templateLoad . 'car.php');

    echo '<showCatalog><![CDATA[' . $templateCar . ']]></showCatalog>';
}

