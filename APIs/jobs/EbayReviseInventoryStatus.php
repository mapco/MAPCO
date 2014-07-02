<?php
	require_once("../../mapco_shop_de/functions/shop_get_prices.php");

	//select oldest auction to get an account
	$results=q("SELECT * FROM ebay_auctions WHERE account_id!=8 ORDER BY lastQuantityUpdate LIMIT 1;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	
	//get active ebay accounts
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$row["account_id"].";", $dbshop, __FILE__, __LINE__);
	$account=mysqli_fetch_array($results);


	//create data file
	$fieldset=array();
	$fieldset["API"]="cms";
	$fieldset["Action"]="TempFileAdd";
	$responseXml = post(PATH."soa/", $fieldset);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<EbayReviseInventoryStatusResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Temporärdatei anlegen fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Anlegen einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response>'.$responseXml.'</Response>'."\n";
		echo '</EbayReviseInventoryStatusResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$filename1=(string)$response->Filename[0];

			
	//create payload
	$payload  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$payload .= '<BulkDataExchangeRequests>'."\n";
	$payload .= '  <Header>'."\n";
	$payload .= '		<SiteID>'.$account["SiteID"].'</SiteID>'."\n";
	$payload .= '		<Version>'.$account["Version"].'</Version>'."\n";
	$payload .= '  </Header>'."\n";
	$upload=array();
	$results=q("SELECT id_auction, shopitem_id, ItemID, SKU, ShippingServiceCost FROM ebay_auctions WHERE account_id=".$account["id_account"]." AND  lastQuantityUpdate<".(time()-24*3600)." ORDER BY lastQuantityUpdate LIMIT 15000;", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		exit;
	}
	while( $auction=mysqli_fetch_array($results) )
	{
		$upload[]=$auction["id_auction"];
		$payload .= '	<ReviseInventoryStatusRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$payload .= '		<Version>'.$account["Version"].'</Version>'."\n";
		$payload .= '		<InventoryStatus>'."\n";
		$payload .= '			<ItemID>'.$auction["ItemID"].'</ItemID>'."\n";
		if ($account["pricelist"]==16815)
		{
			$price=get_prices($auction["shopitem_id"], 1, 27991);
			$StartPrice=round($price["gross"], 2); //mandatory
		}
		elseif ($account["pricelist"]==18209)
		{
			$price=get_prices($auction["shopitem_id"], 1, 27992);
			$StartPrice=round($price["gross"], 2); //mandatory
		}
		else
		{
			$results2=q("SELECT * FROM prpos WHERE ARTNR='".$auction["SKU"]."' AND LST_NR=".$account["pricelist"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$StartPrice=round($row2["POS_0_WERT"]*1.19, 2); //mandatory
		}
		if( $auction["ShippingServiceCost"]!=4.90 ) $StartPrice+=4.90;
		if ($StartPrice<1) $StartPrice=1;
		$payload .= '			<StartPrice>'.$StartPrice.'</StartPrice>'."\n";
//		$payload .= '			<Quantity>15</Quantity>'."\n";
		$payload .= '		</InventoryStatus>'."\n";
		$payload .= '	</ReviseInventoryStatusRequest>'."\n";
	}
	$payload .= '</BulkDataExchangeRequests>'."\n";
	file_put_contents($filename1, $payload);

	//create ZIP file
	$fieldset=array();
	$fieldset["API"]="cms";
	$fieldset["Action"]="TempFileAdd";
	$fieldset["extension"]="zip";
	$responseXml = post(PATH."soa/", $fieldset);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<EbayReviseInventoryStatusResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bild hochladen fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Hochladen eines Bildes ist ein Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response>'.$responseXml.'</Response>'."\n";
		echo '</EbayReviseInventoryStatusResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$filename2=(string)$response->Filename[0];
	
	//add data to ZIP file
	$fieldset=array();
	$fieldset["API"]="cms";
	$fieldset["Action"]="ZipFileAdd";
	$fieldset["zipfile"]=$filename2;
	$fieldset["file"]=substr($filename1, 3);
	$fieldset["filename"]="data.xml";
	$responseXml = post(PATH."soa/", $fieldset);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<EbayReviseInventoryStatusResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Packen fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Die Datei konnte der ZIP-Datei nicht hinzugefügt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response>'.$responseXml.'</Response>'."\n";
		echo '</EbayReviseInventoryStatusResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$Ack=(string)$response->Ack[0];
	if( $Ack!="Success" )
	{
		echo '<EbayReviseInventoryStatusResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Packen fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Die Datei konnte der ZIP-Datei nicht hinzugefügt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response>'.$responseXml.'</Response>'."\n";
		echo '</EbayReviseInventoryStatusResponse>'."\n";
		exit;
	}

	$fieldset=array();
	$fieldset["API"]="ebay_lms";
	$fieldset["Action"]="startUploadJob";
	$fieldset["JobType"]="ReviseInventoryStatus";
	$fieldset["id_account"]=$account["id_account"];
	$fieldset["Filename"]=$filename2;
	echo $responseXml = post(PATH."soa/", $fieldset);

	if( strpos($responseXml, "<ack>Success</ack>") === false )
	{
		echo '<EbayReviseInventoryStatusResponse>';
		echo '	<Ack>Failure</Ack>';
		echo '	<Response><![CDATA['.$responseXml.']]></Response>';
		echo '</EbayReviseInventoryStatusResponse>';
		exit;					
	}
	//uncheck upload flag
	q("UPDATE ebay_auctions SET lastQuantityUpdate=".time()." WHERE id_auction IN (".implode(", ", $upload).");", $dbshop, __FILE__, __LINE__);

	echo '<EbayReviseInventoryStatusResponse>';
	echo '	<Ack>Success</Ack>';
	echo '	<Payload>'.sizeof($upload).'</Payload>';
	echo '	<Response><![CDATA['.$responseXml.']]></Response>';
	echo '</EbayReviseInventoryStatusResponse>';
?>