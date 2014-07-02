<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<GetItemResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetItemResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<GetItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetItemResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	//PRICE RESEARCH EBAY
	$requestXmlBody='
		<?xml version="1.0" encoding="utf-8"?>
		<GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
		  <RequesterCredentials>
			<eBayAuthToken>'.$account["token"].'</eBayAuthToken>
		  </RequesterCredentials>
		  <CategoryID>'.$_POST["CategoryID"].'</CategoryID>
		  <DetailLevel>ReturnAll</DetailLevel>
		  <ViewAllNodes>true</ViewAllNodes>
		  <AllFeaturesForCategory>true</AllFeaturesForCategory>
		</GetCategoryFeaturesRequest>
	';
	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetCategoryFeatures", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));
	
//	echo $response = new SimpleXMLElement($responseXml);
//	echo $response->Item[0]->ListingDetails[0]->ConvertedStartPrice[0].'<br />';
//	echo $response->Item[0]->ShippingDetails[0]->ShippingServiceOptions[0]->ShippingServiceCost[0].'<br />';
//	echo $response->Item[0]->Seller[0]->UserID[0];

?>