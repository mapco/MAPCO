<?php

	if ( $_POST["request"]=="" )
	{
		echo '<EbaySubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine XML-Anfrage übermittelt werden, damit der Service weiß, welche Anfrage er übermitteln soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EbaySubmitResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["Call"]) )
	{
		echo '<EbaySubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Aufruf nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Aufruf übermittelt werden, damit der Service weiß, welcher Aufruf übertragen werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EbaySubmitResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_account"]) )
	{
		echo '<EbaySubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, zu welchem Account der Aufruf gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EbaySubmitResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<EbaySubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EbaySubmitResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	//send the request and get response
	if ( $account["production"]==0 )
	{
		$serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
		$devID=$account["devID_sandbox"];
		$appID=$account["appID_sandbox"];
		$certID=$account["certID_sandbox"];
	}
	else
	{
		$serverUrl = 'https://webservices.ebay.com/BulkDataExchangeService';
		$devID=$account["devID"];
		$appID=$account["appID"];
		$certID=$account["certID"];
	}
	$headers = array (
		//Regulates versioning of the XML interface for the API
		'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $account["Version"],
		
		//set the keys
		'X-EBAY-API-DEV-NAME: ' . $devID,
		'X-EBAY-API-APP-NAME: ' . $appID,
		'X-EBAY-API-CERT-NAME: ' . $certID,
		
		//the name of the call we are requesting
		'X-EBAY-API-CALL-NAME: ' . $_POST["Call"],			
		
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		'X-EBAY-API-SITEID: ' . $account["SiteID"],
		'X-EBAY-SOA-OPERATION-NAME:UploadFile',
		'X-EBAY-SOA-SECURITY-TOKEN:'.$account["token"]
	);
	
	$connection = curl_init();
	curl_setopt($connection, CURLOPT_URL, $serverUrl);
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($connection, CURLOPT_POST, 1);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $_POST["request"]);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
	$responseXml = curl_exec($connection);
	curl_close($connection);


	//read response
	if( stristr($responseXml, 'HTTP 404') || $responseXml == '' )
	{
		echo '<EbaySubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler beim Senden der Anfrage.</shortMsg>'."\n";
		echo '		<longMsg>Die Anfrage konnte nicht korrekt an den eBay-Server übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EbaySubmitResponse>'."\n";
		exit;
	}
	
	echo $responseXml;
?>