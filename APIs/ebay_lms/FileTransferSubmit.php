<?php
	if ( $_POST["request"]=="" )
	{
		echo '<FileTransferSubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine XML-Anfrage übermittelt werden, damit der Service weiß, welche Anfrage er übermitteln soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FileTransferSubmitResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["Call"]) )
	{
		echo '<FileTransferSubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Aufruf nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Aufruf übermittelt werden, damit der Service weiß, welcher Aufruf übertragen werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FileTransferSubmitResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_account"]) )
	{
		echo '<FileTransferSubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, zu welchem Account der Aufruf gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FileTransferSubmitResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<FileTransferSubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FileTransferSubmitResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	//send the request and get response
	if ( $account["production"]==0 )
	{
		$serverUrl = 'https://webservices.sandbox.ebay.com/BulkDataExchangeService';
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
	
	$serverUrl='https://storage.ebay.com/FileTransferService';
	$headers = array (
		'CONTENT-TYPE: XML',
		'X-EBAY-SOA-OPERATION-NAME: '.$_POST["Call"],
		'X-EBAY-SOA-SECURITY-TOKEN: '.$account["token"],
		'X-EBAY-SOA-SERVICE-NAME: FileTransferService',
		'X-EBAY-SOA-REQUEST-DATA-FORMAT: XML',
		'X-EBAY-SOA-RESPONSE-DATA-FORMAT: XML'		
	);
	
	$connection = curl_init();
	curl_setopt($connection, CURLOPT_FORBID_REUSE, true); 
	curl_setopt($connection, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($connection, CURLOPT_URL, $serverUrl);
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($connection, CURLOPT_POST, true);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $_POST["request"]);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
	$responseXml = curl_exec($connection);
	curl_close($connection);


	//read response
	if( stristr($responseXml, 'HTTP 404') || $responseXml == '' )
	{
		echo '<FileTransferSubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler beim Senden der Anfrage.</shortMsg>'."\n";
		echo '		<longMsg>Die Anfrage konnte nicht korrekt an den eBay-Server übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</FileTransferSubmitResponse>'."\n";
		exit;
	}
	
	echo $responseXml;
?>