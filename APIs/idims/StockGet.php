<?php

	//non WSDL client
	$client = new SoapClient(NULL, array('location' => 'http://80.146.160.154/idims/service1.asmx/GESAMTBESTAND_GRUPPE?Token=it@mapco.de&ArtNr=19070', 'uri' => 'http://schemas.xmlsoap.org/soap/envelope/>', 'encoding'=>'UTF-8'));
//	$header = new SoapHeader('http://dhl.de/webservice/cisbase', 'Authentification', $auth, false);
//	$client->__setSoapHeaders($header);
	
	try
	{
		$body=array();
		$body["ArtNr"]="19070";
		$result = $client->__soapCall("GESAMTBESTAND_GRUPPE", $body);
		if(is_soap_fault($result))
		{
			echo '<StockGetResponse>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Fehler beim Aufruf von CreateShipmentDD</shortMsg>'."\n";
			echo '		<longMsg>'.$result->faultcode.': '.$result->faultstring.'</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</StockGetResponse>'."\n";
			exit;
		}
//		$requestXml = $client->__getLastRequest();
//		if( $_SESSION["id_user"]==21371 ) echo $requestXml;
		echo $responseXml = $client->__getLastResponse();
	}
	catch(Exception $e)
	{
		echo '!';
		var_dump($result);
		echo '!';
		echo $requestXml = $client->__getLastRequest();
		echo $responseXml = $client->__getLastResponse();
		echo '<StockGetResponse>';
		echo '	<Ack>Failure</Ack>';
		echo '	<Msg>'.$e->getMessage().'</Msg>';
		echo '	<Request><![CDATA['.$requestXml.']]></Request>';
		echo '	<Response><![CDATA['.$responseXml.']]></Response>';
		echo '</StockGetResponse>';
		exit;
	}  

	echo '<StockGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '  <Response><![CDATA['.$responseXml.']]></Response>';
	echo '</StockGetResponse>'."\n";

?>