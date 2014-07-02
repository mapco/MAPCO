<?php
//	unset($_POST["id_order"]);
	if ( !isset($_POST["id_order"]) )
	{
		show_error(0, 5, __FILE__, __LINE__, explode("; ", $_POST));
		exit;
	}
	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)==0)
	{
		echo '<OrderSendResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Bestellung zu der angegebenen Bestellnummer (id_order) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderSendResponse>'."\n";
		exit;
	}
	$order=mysqli_fetch_array($results);
	
	if($order["AUF_ID"]>0)
	{
		echo '<OrderSendResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auftrag im IDIMS bereits erfasst.</shortMsg>'."\n";
		echo '		<longMsg>Der Auftrag wurde bereits erfolgreich ans IDIMS übermittelt.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderSendResponse>'."\n";
		exit;
	}

	//get shops	
	$results=q("SELECT * FROM shop_shops WHERE id_shop=".$order["shop_id"].";", $dbshop, __FILE__, __LINE__);
	$shop=mysqli_fetch_array($results);

	//get payment method

	//build request XML
	$requestXml  = '<ORDER>'."\n";

	//USR_ID - what is that?
	$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
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
	$row=mysqli_fetch_array($results);
	$requestXml .= '	<USR_ID>'.$row["idims_user_id"].'</USR_ID>'."\n";

	//KUN_ID - linked to shop_id
	$requestXml .= '	<KUN_ID>'.$shop["KUN_ID"].'</KUN_ID>'."\n";
	
	//select bill or ship address
	if( $order["ship_adr_id"]>0 )
	{
		$results=q("SELECT * FROM shop_bill_adr WHERE adr_id=".$order["ship_adr_id"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT * FROM shop_bill_adr WHERE adr_id=".$order["bill_adr_id"].";", $dbshop, __FILE__, __LINE__);
	}
	if( mysqli_num_rows($results)==0 )
	{
		echo '<OrderSendResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Adresse nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der Bestellung konnte keine Adresse gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderSendResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);

	//KUN_SPRACHE - could be detected by user default language
	if( $row["country_id"]>3) $KUN_SPRACHE=4; else $KUN_SPRACHE=1;
	$requestXml .= '	<KUN_SPRACHE>'.$KUN_SPRACHE.'</KUN_SPRACHE>'."\n";

	//ADR - either bill_address or ship_address from shop_orders
	$requestXml .= '	<ADR_ORG_NR>rg32a</ADR_ORG_NR>'."\n";

	if($row["ADR__ID"]>0) $requestXml .= '	<ADR_ID>'.$row["ADR__ID"].'</ADR_ID>'."\n";
	else
	{
		$requestXml .= '	<ADR_ID>0</ADR_ID>'."\n";
		//ADR_ANREDE
		if( $row["gender"]=="Frau" ) $ADR_ANREDE='Frau'; else $ADR_ANREDE='Herr';
		$requestXml .= '	<ADR_ANREDE>'.$ADR_ANREDE.'</ADR_ANREDE>'."\n";
		
		//ADR_NAME
		if($row["company"]!="")
		{
			$ADR_NAME1=$row["company"];
			$ADR_NAME2=$row["firstname"].' '.$row["lastname"];
		}
		else
		{
			$ADR_NAME1=$row["firstname"].' '.$row["lastname"];
			$ADR_NAME2='';
		}
		$requestXml .= '	<ADR_NAME_1>'.$ADR_NAME1.'</ADR_NAME_1>'."\n";
		$requestXml .= '	<ADR_NAME_2>'.$ADR_NAME2.'</ADR_NAME_2>'."\n";
		
		//ADR_STR1
		$ADR_STR1=$row["street"].' '.$row["number"];
		$requestXml .= '	<ADR_STR_1>'.$ADR_STR1.'</ADR_STR_1>'."\n";
		
		//ADR_STR2
		if( $row["additional"]!="" ) $ADR_STR2=$row["additional"]; else $ADR_STR2='';
		$requestXml .= '	<ADR_STR_2>'.$ADR_STR2.'</ADR_STR_2>'."\n";
		
		//ADR_PLZ
		$ADR_PLZ=$row["zip"];
		$requestXml .= '	<ADR_PLZ>'.$ADR_PLZ.'</ADR_PLZ>'."\n";
		
		//ADR_ORT
		$ADR_ORT=$row["city"];
		$requestXml .= '	<ADR_ORT>'.$ADR_ORT.'</ADR_ORT>'."\n";
		
		//ADR_LKZ
		$results2=q("SELECT * FROM shop_countries WHERE id_country=".$row["country_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$ADR_LKZ=$row2["country_code"];
		$requestXml .= '	<ADR_LKZ>'.$ADR_LKZ.'</ADR_LKZ>'."\n";

		//ADR_LAND
		$ADR_LAND=$row["country"];
		$requestXml .= '	<ADR_LAND>'.$ADR_LAND.'</ADR_LAND>'."\n";
	}
	//ADR_MAIL
	$ADR_MAIL=$order["usermail"];
	$requestXml .= '	<ADR_MAIL>'.$ADR_MAIL.'</ADR_MAIL>'."\n";

	//ZLG Zahlungsart - 0=Rechnung, 1=Überweisung, 2=PayPal
	$results=q("SELECT * FROM shop_payment_types WHERE id_paymenttype=".$order["payments_type_id"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ZLG=$row["ZLG"];
	$requestXml .= '	<ZLG>'.$ZLG.'</ZLG>'."\n";
	
	//VERS_ID Versandart - ID laut IDIMS
	$results=q("SELECT * FROM shop_shipping_types WHERE id_shippingtype=".$order["shipping_type_id"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	if( $order["shop_id"]==2 or $order["shop_id"]==4 ) $VERS_ID=$row["AP_VERS_ID"];
	else $VERS_ID=$row["VERS_ID"];
	$requestXml .= '	<VERS_ID>'.$VERS_ID.'</VERS_ID>'."\n";
	
	//PACK Packstelle - 0=Zentrale, ab 15=RCs
	$requestXml .= '	<PACK>0</PACK>'."\n";

	//PRODUKT - data from shop_orders_items
	$results=q("SELECT * FROM shop_orders_items WHERE order_id=".$order["id_order"].";", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$ART_NR=$row2["MPN"];
		$exchange_rate_to_EUR=1/$row["exchange_rate_to_EUR"];
		$VK=number_format($row["price"]*$exchange_rate_to_EUR, 2, ",", ".");
		$requestXml .= '	<PRODUKT>'."\n";
		$requestXml .= '		<ART_NR>'.$ART_NR.'</ART_NR>'."\n";
		$requestXml .= '		<ART_UST>2</ART_UST>'."\n";
		$requestXml .= '		<MENGE>'.$row["amount"].'</MENGE>'."\n";
		$requestXml .= '		<VK>'.$VK.'</VK>'."\n";
		$requestXml .= '	</PRODUKT>'."\n";
	}
	//FRACHT if shipping costs are special
	if( $order["shipping_net"]>0 )
	{
		$requestXml .= '	<PRODUKT>'."\n";
		$requestXml .= '		<ART_NR>FRACHT</ART_NR>'."\n";
		$results2=q("SELECT * FROM shop_shipping_types WHERE id_shippingtype=".$order["shipping_type_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$ART_BEZ='Versandkosten '.$row2["title"];
		$requestXml .= '		<ART_BEZ>'.$ART_BEZ.'</ART_BEZ>'."\n";
		$requestXml .= '		<ART_UST>1</ART_UST>'."\n";
		$requestXml .= '		<MENGE>1</MENGE>'."\n";
		$VK=number_format($order["shipping_net"]*$exchange_rate_to_EUR, 2, ",", ".");
		$requestXml .= '		<VK>'.$VK.'</VK>'."\n";
		$requestXml .= '	</PRODUKT>'."\n";
	}
  
	$requestXml .= '</ORDER>'."\n";

	//it@mapco.de
	//it@mapco.de<TESTDB/>

	$serverUrl='http://80.146.160.154/idims/service1.asmx/BUILD_ORDER?Token=it@mapco.de&orderXML='.urlencode($requestXml);
//	$serverUrl='http://80.146.160.154/idims/service1.asmx/BUILD_ORDER';
//	$serverUrl='http://localhost/MAPCO/mapco_shop_de/';
	$headers = array (
		'Content-Type: application/x-www-form-urlencoded',
		'Content-Length:'.strlen($serverUrl),
		'Cache-Control: max-age=0',
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Encoding: gzip,deflate,sdch',
		'Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4'
	);
//	$post=array("Token" => "it@mapco.de<TESTDB/>", "orderXML" => $requestXml);

	$connection = curl_init();
	curl_setopt($connection, CURLOPT_FORBID_REUSE, true); 
	curl_setopt($connection, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($connection, CURLOPT_URL, $serverUrl);
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($connection, CURLOPT_POST, false);
//	curl_setopt($connection, CURLOPT_POSTFIELDS, $post);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
	$responseXml = curl_exec($connection);
	curl_close($connection);

	//xml validation fix
	$responseXml=str_replace('&lt;', '<', $responseXml);
	$responseXml=str_replace('&gt;', '>', $responseXml);
	$responseXml=str_replace('KUN_ID><', '/KUN_ID><', $responseXml);

	//read response
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<OrderSendResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '  <Response><![CDATA['.$responseXml.']]></Response>';
		echo '</OrderSendResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$AUF_ID=$response->AUF_ID[0];
	$ADR_ID=$response->ADR_ID[0];
	
	if( $AUF_ID==0 or $ADR_ID=="" )
	{
		echo '<OrderSendResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auftrag erstellen fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Erstellen des Auftrages trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '  <Response><![CDATA['.$responseXml.']]></Response>';
		echo '</OrderSendResponse>'."\n";
		exit;
	}
	
	q("	UPDATE shop_orders
		SET AUF_ID=".$AUF_ID.",
			status_id=2,
			status_date=".time().",
			lastmod=".time().",
			lastmod_user=".$_SESSION["id_user"]."
		WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
	if( $order["ship_adr_id"]>0 )
	{
		q("UPDATE shop_bill_adr SET ADR__ID=".$ADR_ID." WHERE adr_id=".$order["ship_adr_id"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		q("UPDATE shop_bill_adr SET ADR__ID=".$ADR_ID." WHERE adr_id=".$order["bill_adr_id"].";", $dbshop, __FILE__, __LINE__);
	}

	echo '<OrderSendResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '  <Response><![CDATA['.$responseXml.']]></Response>';
	echo '</OrderSendResponse>'."\n";

?>