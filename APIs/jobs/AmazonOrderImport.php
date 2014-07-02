<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Job Service for order import
 *	- call crm/AmazonOrderImport
 *
 *	@params
 *	- 
 *
*******************************************************************************/

$start = time();

	//IMPORT ORDERS FROM AMAZON TO AMAZON_ORDERS
	$postfield = array();
	$postfield["API"] = "amazon";
	$postfield["APIRequest"] = "AmazonOrdersImport";
	$postfield["id_account"] = 1;
	soa2($postfield, __FILE__, __LINE__, 'xml');

//IMPORT ORDERS FROM AMAZON_ORDERS TO SHOP_ORDERS
// keep post submit
$post = $_POST;

if (isset($post['AmazonOrderId']) && $post['AmazonOrderId'] > 0) {
	$amazonOrdersQuery = "
	SELECT * 
	FROM amazon_orders 
	WHERE AmazonOrderId = '" . $post['AmazonOrderId'] . "' 
	AND importShopStatus = 0;";
} else {
	$amazonOrdersQuery = "
		SELECT * 
		FROM amazon_orders 
		WHERE importShopStatus = 0 
		ORDER BY firstmod DESC;";
}
$amazonOrdersResult = q($amazonOrdersQuery, $dbshop, __FILE__, __LINE__);
$rowcount = mysqli_num_rows($amazonOrdersResult);

$postfield = array();
$postfield["API"] = "crm";
$postfield["APIRequest"] = "AmazonOrderImport";

$jobresponse = '';
while ($amazonOrder = mysqli_fetch_assoc($amazonOrdersResult))
{
	if ((time() - $start) < 60) {
		$postfield["AmazonOrderId"] = $amazonOrder["AmazonOrderId"];
		$jobresponse.= "\n\r" . post(PATH . "soa2/", $postfield);
	}
}
echo $jobresponse;
