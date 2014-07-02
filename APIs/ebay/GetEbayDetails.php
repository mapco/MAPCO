<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<GeteBayDetailsResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GeteBayDetailsResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<GeteBayDetailsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GeteBayDetailsResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	//PRICE RESEARCH EBAY
	$requestXmlBody='
	<?xml version="1.0" encoding="utf-8"?> 
	<GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents"> 
	  <RequesterCredentials> 
				<eBayAuthToken>'.$account["token"].'</eBayAuthToken>
	  </RequesterCredentials> 
	  <DetailName>ReturnPolicyDetails</DetailName> 
	  <DetailName>PaymentOptionDetails</DetailName> 
	  <DetailName>ShippingCarrierDetails</DetailName> 
	  <DetailName>ShippingServiceDetails</DetailName> 
	</GeteBayDetailsRequest> 
	';
//	  <DetailName>ShippingCarrierDetails</DetailName> 
//	  <DetailName>ShippingServiceDetails</DetailName> 

	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GeteBayDetails", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>