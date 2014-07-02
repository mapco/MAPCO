<?php
	function q($query, $db, $file="", $line="", $error_txt="")
	{
		global $dbweb;
		global $dbshop;
		global $debug;
		global $queries;
		if ( isset($debug) )
		{
			$start=strpos($query, "FROM ")+5;
			$stop=strpos($query, " ", $start)-$start;
			$tablename=substr($query, $start, $stop);
			if ( !isset($queries[$tablename]) ) $queries[$tablename]=1; else $queries[$tablename]++;
		}
		$starttime=time()+microtime();
		$results = mysqli_query($db, $query) or error($file, $line, "<br />".$query."<br />".mysqli_error($db)."<br />".$error_txt);
		$stoptime=time()+microtime();
		$time=$stoptime-$starttime;
		if( $time>1 )
		{
			mysqli_query($dbweb, "INSERT INTO cms_errors_sql (query, time) VALUES('".mysqli_real_escape_string($dbweb, $query)."', '".$time."');");
		}
		return $results;
	}

	$dbweb=mysqli_connect("localhost", "dedi473_14", "Merci2664!", "admapco_mapcoweb");
	q("SET NAMES utf8", $dbweb, __FILE__, __LINE__);
	$dbshop=mysqli_connect("localhost","mapcoshop","merci2664", "admapco_mapcoshop");
	q("SET NAMES utf8", $dbshop, __FILE__, __LINE__);

	$_POST["AUF_ID"]="1611184, 1611374, 1682969, 1682976, 1682979";

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
	
	echo $statusXml;
//	exit;

	$serverUrl='http://80.146.160.154/idims/service1.asmx/WEB_AUF_STATUS?Token=it@mapco.de&aufXML='.urlencode($statusXml);
	$headers = array (
		'Content-Type: application/x-www-form-urlencoded',
		'Content-Length:'.strlen($serverUrl),
		'Cache-Control: max-age=0',
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Encoding: gzip,deflate,sdch',
		'Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4'
	);

	$connection = curl_init();
	curl_setopt($connection, CURLOPT_FORBID_REUSE, true); 
	curl_setopt($connection, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($connection, CURLOPT_URL, $serverUrl);
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($connection, CURLOPT_POST, false);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
	$responseXml = curl_exec($connection);
	curl_close($connection);

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

	echo '<OrderStausResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '  <Response><![CDATA['.$responseXml.']]></Response>';
	echo '</OrderStatusResponse>'."\n";

?>