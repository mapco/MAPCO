<?php

/***
 *	@author: rlange@mapco.de
 *	Catalog API
 *
 *	@params
 *	- $submitTypes
 *	-- language_id, vehicle_id
 *
 *
 *******************************************************************************/
include("../functions/cms_core.php");
include('../functions/array_to_xml.php');

$templateLoad = 'templates/template_';

//	keep post submit
$post = $_POST;

if (isset($post['action']) && $post['action'] == 'showCatalog') 
{
	$data = array();
	$data['from'] = 'vehicles_de';
	$data['select'] = '*';
	$data['where'] = "
		id_vehicle = " . $post["vehicle_id"];
	$vehicleResult = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop', __FILE__, __LINE__);
	//$vehiclesXml = ArrayToXml::createXML('catalogVehicles', $vehicleResult);
	//echo $vehiclesXml->saveXML();
	
	include($templateLoad . 'car.php');
	
	echo '<showCatalog><![CDATA[' . $templateCar . ']]></showCatalog>';
}