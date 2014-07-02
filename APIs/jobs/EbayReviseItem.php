<?php
	//get active ebay accounts
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=1;", $dbshop, __FILE__, __LINE__);
	$account=mysqli_fetch_array($results);

	//create payload
	$upload=array();
	$payload  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$payload .= '<BulkDataExchangeRequests xmlns="http://www.ebay.com/marketplace/services">'."\n";
	$payload .= '	<Header>'."\n";
	$payload .= '		<SiteID>'.$account["SiteID"].'</SiteID>'."\n";
	$payload .= '		<Version>'.$account["Version"].'</Version>'."\n";
	$payload .= '	</Header>'."\n";
	$i=0;
	$results=q("SELECT * FROM ebay_auctions WHERE account_id=1 AND upload=1 AND `Call`='ReviseItem';", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<EbayAuctionsUpload>';
		echo '	<Ack>Success</Ack>';
		echo '	<Response>No items to upload.</Response>';
		echo '</EbayAuctionsUpload>';
		exit;
	}
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		$payload .= '	<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$payload .= '		<RequesterCredentials><eBayAuthToken>'.$account["token"].'</eBayAuthToken></RequesterCredentials>';
		$payload .= '		<ErrorLanguage>en_US</ErrorLanguage>'."\n";
		$payload .= '		<WarningLevel>High</WarningLevel>'."\n";
		$payload .= '		<Version>'.$account["Version"].'</Version>'."\n";
//		$payload .= '		<MessageID>Request '.$i.'</MessageID>'."\n";
		$payload .= '		<Item>'."\n";
		$payload .= '			<ItemID>'.$row["ItemID"].'</ItemID>'."\n";
		$payload .= '			<Quantity>'.$row["Quantity"].'</Quantity>'."\n";
		$payload .= '			<ShippingDetails>'.$row["ShippingDetails"].'</ShippingDetails>'."\n";
		$payload .= '			<StartPrice>'.$row["StartPrice"].'</StartPrice>'."\n";
		$payload .= '		</Item>'."\n";
		$payload .= '	</ReviseItemRequest>'."\n";
		$upload[]=$row["id_auction"];
	}
	$payload .= '</BulkDataExchangeRequests>'."\n";

/*
	$fieldset=array();
	$fieldset["API"]="ebay";
	$fieldset["Action"]="EbaySubmit";
	$fieldset["Call"]="ReviseItem";
	$fieldset["id_account"]=1;
	$fieldset["request"]=$payload;
	echo $responseXml = post(PATH."soa/", $fieldset);
	exit;
*/

	$fieldset=array();
	$fieldset["API"]="ebay_lms";
	$fieldset["Action"]="startUploadJob";
	$fieldset["JobType"]="ReviseItem";
	$fieldset["id_account"]=1;
	$fieldset["Data"]=$payload;
	echo $responseXml = post(PATH."soa/", $fieldset);
	
	echo '<EbayAuctionsUpload>';
	echo '	<Ack>Success</Ack>';
	echo '</EbayAuctionsUpload>';
	
	//uncheck upload flag
	for($i=0; $i<sizeof($upload); $i++)
	{
		q("UPDATE ebay_auctions SET upload=0, lastupdate=".time()." WHERE id_auction=".$upload[$i].";", $dbshop, __FILE__, __LINE__);
	}
?>