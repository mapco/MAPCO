<?php
	include("config.php");

	$_POST["AUF_ID"]="1831940";

	if ( !isset($_POST["AUF_ID"]) or !($_POST["AUF_ID"]>0) )
	{
		echo '<OrderGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auftrags ID ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Es wurde keine gültige Auftrags ID (AUF_ID) übergeben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderGetResponse>'."\n";
		exit;
	}
	$results=q("SELECT parent_auf_id FROM shop_orders_auf_id WHERE AUF_ID IN(".$_POST["AUF_ID"].");", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<OrderGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auftrags ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es scheint keinen Auftrag mit dieser ID (AUF_ID) zu existieren.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderGetResponse>'."\n";
		exit;
	}

	//build status XML
	$statusXml  = '<WEB_AUF_STATUS>'."\n";

	while ( $row=mysqli_fetch_array($results) )
	{
		$statusXml .= '	<AUFID>'.$row["parent_auf_id"].'</AUFID>'."\n";
	}
	 
	$statusXml .= '</WEB_AUF_STATUS>'."\n";

//	$statusXml = str_replace("\n", "", $statusXml);
//	$statusXml = str_replace("\t", "", $statusXml);

	//it@mapco.de
	//it@mapco.de<TESTDB/>
	
//	echo $statusXml;
//	exit;

	//set POST variables
	$serverUrl = 'http://80.146.160.154/idims/service1.asmx/WEB_AUF_STATUS';
	$fields = array(
						'Token' => "it@mapco.de",
						'aufXML' => urlencode($statusXml),
						'booleanPDF' => "TRUE",
					);
	
	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');
	
	//open connection
	$connection = curl_init();
	//set the url, number of POST vars, POST data
	curl_setopt($connection, CURLOPT_FORBID_REUSE, true); 
	curl_setopt($connection, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($connection, CURLOPT_URL, $serverUrl);
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($connection, CURLOPT_POST, true);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
//	curl_setopt($connection,CURLOPT_URL, $url);
//	curl_setopt($connection,CURLOPT_POST, true);
//	curl_setopt($connection,CURLOPT_POSTFIELDS, $fields_string);
//	curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);

	$responseXml = curl_exec($connection);
	curl_close($connection);
	unset($fields);
	unset($fields_string);

	//xml validation fix
	$responseXml=str_replace('&lt;', '<', $responseXml);
	$responseXml=str_replace('&gt;', '>', $responseXml);

	//read response
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<PriceUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '  <Response><![CDATA['.$responseXml.']]></Response>';
		echo '</PriceUpdateResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	if (strpos($responseXml, "<ERROR>")>0)
	{
		echo '<PriceUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '  <Response><![CDATA['.$responseXml.']]></Response>';
		echo '</PriceUpdateResponse>'."\n";
		exit;
	}
	
	if (strpos($responseXml, "<PDF>")>0)
	{
		$RNG_ID=$response->AUFID[0]->RNG_ID[0];
		$PDF=$response->AUFID[0]->PDF[0];
		$file = fopen( $RNG_ID.".pdf", "w" ); 
		fwrite( $file, base64_decode($PDF) ); 
		fclose( $file ); 
		echo '<a href="http://www.mapco.de/'.$RNG_ID.'.pdf">'.$RNG_ID.'</a>'."\n";
	}

	echo '<OrderStausResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '  <Response><![CDATA['.$responseXml.']]></Response>';
	echo '</OrderStatusResponse>'."\n";

?>