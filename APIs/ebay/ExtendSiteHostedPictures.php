<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<ExtendSiteHostedPicturesResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ExtendSiteHostedPicturesResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ExtendSiteHostedPicturesResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ExtendSiteHostedPicturesResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	if ( !isset($_POST["PictureURL"]) )
	{
		echo '<ExtendSiteHostedPicturesResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bild-URL nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine EPS-Bild-URL (PictureURL) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ExtendSiteHostedPicturesResponse>'."\n";
		exit;
	}

	//create XML
	$requestXmlBody  = '<?xml version="1.0" encoding="UTF-8"?>';
	$requestXmlBody .= '<ExtendSiteHostedPicturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
	$requestXmlBody .= '	<RequesterCredentials>';
	$requestXmlBody .= '		<eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
	$requestXmlBody .= '	</RequesterCredentials>';
	$requestXmlBody .= '	<ErrorLanguage>en_US</ErrorLanguage>';
	$requestXmlBody .= '	<WarningLevel>High</WarningLevel>';
	$requestXmlBody .= '	<Version>'.$account["Version"].'</Version>';
	$requestXmlBody .= '	<ExtensionInDays>10</ExtensionInDays>';
	$requestXmlBody .= '	<PictureURL>'.$_POST["PictureURL"].'</PictureURL>';
	$requestXmlBody .= '</ExtendSiteHostedPicturesRequest>';
	
	//submit XML
	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "ExtendSiteHostedPictures", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));
	exit;



	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<SetShipmentTrackingInfoResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Antwort von eBay fehlerhaft.</shortMsg>'."\n";
		echo '		<longMsg>Beim Abrufen der Serverantwort von eBay ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SetShipmentTrackingInfoResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if( $response->Ack[0]!="Success" and $response->Ack[0]!="Warning" )
	{
		if( isset($_POST["id_auction"]) )
		{
			q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
		}
		echo '<SetShipmentTrackingInfoResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Beenden der Auktion fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg><![CDATA['.$responseXml.']]></longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SetShipmentTrackingInfoResponse>'."\n";
		exit;
	}
	
	//return success
	echo '<SetShipmentTrackingInfoResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</SetShipmentTrackingInfoResponse>'."\n";

?>