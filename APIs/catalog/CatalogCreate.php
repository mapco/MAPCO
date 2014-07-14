<?php

/***
 * @author: rlange@mapco.de
 * Catalog API
 *
 * @params
 * - $submitTypes
 * -- language_id, KHerNr
 *
 *
 *******************************************************************************/
include("../functions/cms_core.php");
$templateLoad = 'templates/';
$templateSet = '001_car';
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
	if ($pos !== false)
	{
		$keywords = substr($keywords,0, $pos);
	}

	//	load the template file
    include($templateLoad . $templateSet . '/' . $templateSet. '_template.php');

	//	cache result as html file
	$buildHtmlFile = '
		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
		<head></head>
			<body>
	';
	$buildHtmlFile.= getCssForTable();
	$buildHtmlFile.= $showTemplate;
	$buildHtmlFile.= '
			</body>
		</html>
	';

	$catalogCacheFile = 'Catalog_' . ucfirst($templateSet) . '_' . $KHerNr . '.html';
	$catalogCacheFolder = '../../assets/CmsCatalog/';
	$file = fopen($catalogCacheFolder . $catalogCacheFile, 'w') or die('Can\'t open this file!');
	fwrite($file, $buildHtmlFile);
	fclose($file);

	//	save new catalog for caching
	$data = array();
	$data['from'] = 'cms_catalog';
	$data['select'] = '*';
	$data['where'] = "
		KHerNr = '" . $KHerNr . "'
		AND GART =	'" . $GART . "'
		AND template = '" . $templateSet . "'
	";
	$cmsCatalog = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'web',  __FILE__, __LINE__);

	if (count($cmsCatalog) > 0)
	{
		$data = array();
		//	user insert data
		$data['lastmod'] = time();
		$data['lastmod_user'] = $_SESSION["id_user"];
		$addWhere = "
			id_catalog = " . $cmsCatalog['id_catalog'];
		SQLUpdate('cms_catalog', $data, $addWhere, 'web', __FILE__, __LINE__);
	} else {
		$field = array(
			'table' => 'cms_catalog'
		);
		$data = array();
		//	content data
		$data['KHerNr'] = $KHerNr;
		$data['GART'] = $GART;
		$data['template'] = $templateSet;
		$data['filename'] = $catalogCacheFile;

		//	user insert data
		$data['firstmod'] = time();
		$data['firstmod_user'] = $_SESSION["id_user"];
		$data['lastmod'] = time();
		$data['lastmod_user'] = $_SESSION["id_user"];
		SQLInsert($field, $data, 'web', __FILE__, __LINE__);
	}
    echo '<showCatalog><![CDATA[' . $showTemplate . ']]></showCatalog>';
}

function getCssForTable()
{
	$css = "
    <style>
        table.vehicles-catalog {
            border: 1px solid #c4c4c4;
            width: 100%;
			font-family: helvetica;
        }
        table.vehicles-catalog th,
        table.vehicles-catalog td {
            border: none;
        }
        table.vehicles-catalog .center {
            text-align: center;
        }
        table.vehicles-catalog tr.headline th {
            border-bottom: none;
            background-color: #dddddd;
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            padding: 2px 10px;
        }
        table.vehicles-catalog tr.navigation th {
            background-color: #e2e2e2;
            font-size: 8pt;
            font-weight: bold;
            padding: 10px;
        }
        table.vehicles-catalog tr.content-list td {
            background-color: #ffffff;
            border-bottom: 1px solid #dddddd;
            padding: 5px 10px;
        }
        table.vehicles-catalog tr.content-list .mpn {
            display: block;
            margin-bottom: 10px;
            font-size: 12pt;
            font-weight: bold;
        }
        table.vehicles-catalog tr.content-list .keywords-list {
            display: block;
            margin-bottom: 10px;
            font-size: 12pt;
            font-weight: bold;
        }
        table.vehicles-catalog tr.content-list .vehicles-list {
            display: block;
            margin-bottom: 10px;
            margin-left: 10px;
            color: #626262;
        }
        table.vehicles-catalog tr.content-list .criteria-list {
            display: block;
            margin-bottom: 5px;
            margin-left: 10px;
        }
        table.vehicles-catalog tr.content-list .criteria-list i.fa {
            margin-right: 5px;
        }
        table.vehicles-catalog tr.content-list .description,
		table.vehicles-catalog tr.navigation th.description {
            width: 70%;
        }
        table.vehicles-catalog tr.content-list .numbers,
		table.vehicles-catalog tr.navigation th.numbers {
            width: 15%;
            border-left: 1px solid #dddddd;
            border-right: 1px solid #dddddd;
        }
        table.vehicles-catalog tr.content-list .image,
		table.vehicles-catalog tr.navigation th.image {
            width: 15%;
        }
    </style>
	";
	return $css;
}
