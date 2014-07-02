<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>eBay-Test</title>
</head>

<body>

<?php
	error_reporting(E_ALL);
	
	ini_set("allow_url_fopen", "1");
	ini_set("allow_url_include", "1");
	echo '<hr />';
//	phpinfo();

	// Define global variables
	$m_endpoint = 'http://svcs.ebay.com/MerchandisingService?';  // Merchandising URL to call
	$appid = 'Webdesig-8afd-46d0-8aa0-3c7a4633b529';  // sandbox
	$appid = 'Webdesig-a2f7-4aea-b67e-f10bf2b725a1'; //production
	$responseEncoding = 'XML';  // Type of response we want back

	function get_data($url)
	{
	  $ch = curl_init();
	  $timeout = 5;
	  curl_setopt($ch,CURLOPT_URL,$url);
	  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	  $data = curl_exec($ch);
	  curl_close($ch);
	  return $data;
	}

	function getMostWatchedItemsResults ($selectedItemID = '', $cellColor = '')
	{
		global $m_endpoint;
		global $appid;
		global $responseEncoding;
	
		// Construct getMostWatchedItems call with maxResults and categoryId as input
		$apicalla  = 'http://svcs.ebay.com/MerchandisingService'; //production
//		$apicalla  = 'http://svcs.sandbox.ebay.com/MerchandisingService'; //sandox
		$apicalla .= '?OPERATION-NAME=getMostWatchedItems';
		$apicalla .= '&SERVICE-NAME=MerchandisingService';
		$apicalla .= '&SERVICE-VERSION=1.1.0';
		$apicalla .= '&CONSUMER-ID='.$appid;
		$apicalla .= '&RESPONSE-DATA-FORMAT=XML';
		$apicalla .= '&REST-PAYLOAD';
		$apicalla .= '&categoryId=267';
		
		/*
		$apicalla  = "$m_endpoint";
		$apicalla .= "OPERATION-NAME=getMostWatchedItems";
		$apicalla .= "&SERVICE-VERSION=1.0.0";
		$apicalla .= "&CONSUMER-ID=$appid";
		$apicalla .= "&RESPONSE-DATA-FORMAT=$responseEncoding";
		$apicalla .= "&maxResults=3";
		$apicalla .= "&categoryId=293";
		*/
	
		// Load the call and capture the document returned by eBay API
		$response=get_data($apicalla);
		$response = simplexml_load_string($response);
		print_r($response);
	}

	print getMostWatchedItemsResults();
?>

</body>
</html>
