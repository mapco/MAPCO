<?php
	include("../config.php");

	//XML error handler
	function HandleXmlError($errno, $errstr, $errfile, $errline)
	{
		error($errfile, $errline, $errno." ".$errstr);
	}

	$auctions=array();
	$results=q("SELECT ItemID FROM ebay_auctions WHERE account_id=1;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$auctions[$row["ItemID"]]=$row["ItemID"];
	}

	echo $response=post(PATH."soa/", array( "API" => "ebay", "Action" => "GetSellerList", "id_account" => 1, "EntriesPerPage" => 200));
	$xml = new DOMDocument();
	set_error_handler('HandleXmlError');
	$xml->loadXML($response);
	restore_error_handler();
	
	$Items=$xml->getElementsByTagName('Item');
	for($i=0; $i<$Items->length; $i++)
	{
		if ( !isset($auctions[$Items->Item($i)->nodeValue]) )
			echo $Items->Item($i)->nodeValue.'<br />';
	}
?>