<?php

	if ( !isset($_POST["id_pricesuggestion"]) or !($_POST["id_pricesuggestion"]>0) )
	{
		echo '<OrderGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Preisvorschlag nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es wurde keine gültige Preisvorschlags ID (id_pricesuggestion) übergeben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderGetResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_price_suggestions WHERE id_pricesuggestion=".$_POST["id_pricesuggestion"]." AND status IN (1,2,4);", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<OrderGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Preisvorschlag nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es scheint keinen gültigen oder akzeptierten Preisvorschlag zu der angegebenen ID (id_pricesuggestion) zu existieren.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderGetResponse>'."\n";
		exit;
	}
	$price_suggestion=mysqli_fetch_array($results);

	if ( $price_suggestion["imported"]!=0 )
	{
		echo '<OrderGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Preisvorschlag bereits übermittelt.</shortMsg>'."\n";
		echo '		<longMsg>Der Preisvorschlag zu der angegebenen ID (id_pricesuggestion) wurde bereits an IDIMS übermittelt.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderGetResponse>'."\n";
		exit;
	}

	//SELECT NETTO PRICE	
	$price_netto=round($price_suggestion["price"]/1.19, 2);
	
	//WRITE STATUS TEXT
	$status="";
	switch ($price_suggestion["status"])
	{
		case 1: $status="akzeptiert da Preis nicht niedriger als gelb"; break;
		case 2: $status="akzeptiert von D.Seeliger"; break;
		case 4: $status="akzeptiert von D.Seeliger nach Aenderung"; break;
	}

	//SELECT MPN
	$results2=q("SELECT MPN FROM shop_items WHERE id_item=".$price_suggestion["item_id"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results2)==0 )
	{
		echo '<OrderGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Preislistengruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es scheint keine gültige Preislistengruppe zu der angegebenen ID (id_group) zu existieren.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderGetResponse>'."\n";
		exit;
	}
	$row2=mysqli_fetch_array($results2);

	//SELECT IDIMS USER ID
	$results3=q("SELECT idims_user_id FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results3)==0 )
	{
		echo '<OrderSendResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Benutzer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Benutzer (id_user) existiert nicht.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderSendResponse>'."\n";
		exit;
	}
	$row3=mysqli_fetch_array($results3);

	//CHECK IDIMS USER
	if($row3["idims_user_id"]==0 )
	{
		echo '<OrderSendResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kein gültiger IDIMS User.</shortMsg>'."\n";
		echo '		<longMsg>Zu diesem Benutzer ist kein gültiger IDIMS User (idims_user_id) hinterlegt.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderSendResponse>'."\n";
		exit;
	}

	//SELECT PRICE LISTS TO UPDATE
	$results4=q("SELECT * FROM idims_price_update_groups WHERE id_group=".$price_suggestion["group_id"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results4)==0 )
	{
		echo '<OrderGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Preislistengruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es scheint keine gültige Preislistengruppe zu der angegebenen ID (id_group) zu existieren.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderGetResponse>'."\n";
		exit;
	}
	$row4=mysqli_fetch_array($results4);

	//build price XML
	$priceXml  = '<WEB_PL>'."\n";

	while ( $row4=mysqli_fetch_array($results4) )
	{
		$priceXml .= '<PREIS>'."\n";
		$priceXml .= '	<MAN_ID>'.$row4["MAN_ID"].'</MAN_ID>'."\n";
		$priceXml .= '	<USR_ID>'.$row3["idims_user_id"].'</USR_ID>'."\n";
		$priceXml .= '	<ART_NR>'.$row2["MPN"].'</ART_NR>'."\n";
		$priceXml .= '	<PREISLISTE>'.$row4["PREISLISTE"].'</PREISLISTE>'."\n";
		$priceXml .= '	<VK>'.number_format($price_netto, 2, ',', '').'</VK>'."\n";
		$priceXml .= '	<AB_MENGE>0</AB_MENGE>';
//		$priceXml .= '	<AB_DATUM></AB_DATUM>';	//DATUMSFORMAT=20.11.2013
//		$priceXml .= '	<BIS_DATUM>20.11.2013</BIS_DATUM>'; //DATUMSFORMAT=20.11.2013
		$priceXml .= '	<INFO>'.$status.'</INFO>';
		$priceXml .= '</PREIS>'."\n";
	}
	 
	$priceXml .= '</WEB_PL>'."\n";

	$priceXml = str_replace("\n", "", $priceXml);
	$priceXml = str_replace("\t", "", $priceXml);

	//it@mapco.de
	//it@mapco.de<TESTDB/>
	
//	echo $priceXml;
//	exit;

	$serverUrl='http://80.146.160.154/idims/service1.asmx/WEB_PL?Token=it@mapco.de&PL_XML='.urlencode($priceXml);
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

	//update autopartner pricelist
	$timestamp = time();
	$GUELTIG_AB = date("Y-m-j", $timestamp);
	$timestamp = $timestamp+(3600*24*365*10);
	$GUELTIG_BIS = date("Y-m-j", $timestamp);
	$results=q("SELECT * FROM prpos WHERE LST_NR=18209 AND ARTNR='".$row2["MPN"]."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)>0 )
	{
		q("UPDATE prpos SET POS_0_WERT='".number_format($price_netto, 2)."', GUELTIG_AB='".$GUELTIG_AB."', GUELTIG_BIS='".$GUELTIG_BIS."' WHERE LST_NR=18209 AND ARTNR='".$row2["MPN"]."';", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		q("INSERT INTO prpos(ARTNR, LSt_NR, POS_0_WERT, POS_0_PE, LSt_AKTIV_CHK, POS_AKTIV_CHK, AKTION_CHK, NETTO_CHK, GUELTIG_AB, GUELTIG_BIS, NEU, GEAND, MAN_ID) VALUES('".$row2["MPN"]."', 18209, '".$row2["MPN"]."', 0, 1, 1, 0, 1, '".$GUELTIG_AB."', '".$GUELTIG_BIS."', '".date("Y-m-j H:i:s")."', '".date("Y-m-j H:i:s")."',	1) ;", $dbshop, __FILE__, __LINE__);
	}
	
	//update mapco pricelist
	$mapco_price=number_format($price_netto, 2);
//		$mapco_price*=1.1;
	$results=q("SELECT * FROM prpos WHERE LST_NR=16815 AND ARTNR='".$row2["MPN"]."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)>0 )
	{
		q("UPDATE prpos SET POS_0_WERT='".$mapco_price."', GUELTIG_AB='".$GUELTIG_AB."', GUELTIG_BIS='".$GUELTIG_BIS."' WHERE LST_NR=16815 AND ARTNR='".$row2["MPN"]."';", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		q("INSERT INTO prpos(ARTNR, LSt_NR, POS_0_WERT, POS_0_PE, LSt_AKTIV_CHK, POS_AKTIV_CHK, AKTION_CHK, NETTO_CHK, GUELTIG_AB, GUELTIG_BIS, NEU, GEAND, MAN_ID) VALUES('".$row2["MPN"]."', 16815, '".$mapco_price."', 0, 1, 1, 0, 1, '".$GUELTIG_AB."', '".$GUELTIG_BIS."', '".date("Y-m-j H:i:s")."', '".date("Y-m-j H:i:s")."',	1) ;", $dbshop, __FILE__, __LINE__);
	}

	//Set Price Suggestion to Imported
	q("UPDATE shop_price_suggestions SET imported=1 WHERE id_pricesuggestion=".$_POST["id_pricesuggestion"].";", $dbshop, __FILE__, __LINE__);

	//Ebay Update
	q("INSERT INTO ebay_auctions_priority (item_id) VALUES (".$price_suggestion["item_id"].");", $dbshop, __FILE__, __LINE__);
	
	echo '<PriceUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '  <Response><![CDATA['.$responseXml.']]></Response>';
	echo '</PriceUpdateResponse>'."\n";

?>