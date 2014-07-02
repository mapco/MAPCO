<?php

	//SOA 2 SERVICE
	$required=array("orderid" => "numericNN");
	check_man_params($required);

/*
	$order = array();
	$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["orderid"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		//KOMBINIERTE ORDER NICHT GEFUNDEN
		//show_error();
		echo "Keine Order zur Bestellung gefunden. OrderID: ".$_POST["orderid"];
		exit;
	}
	while ($row_order = mysqli_fetch_assoc($res_order))
	{
		if ($row_order["combined_with"] > 0 )
		{
			$res_combined = q("SELECT * FROM shop_orders WHERE id_order = ".$row_order["combined_with"], $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_combined)==0)
			{
				//KOMBINIERTE ORDER NICHT GEFUNDEN
				//show_error();
				echo "Keine Mutterorder  zur Bestellung gefunden OrderID: ".$_POST["orderid"]." Combined_with ".$row_order["combined_with"];
				exit;
			}
			$order = mysqli_fetch_assoc($res_combined);
		}
		else
		{
			$order = $row_order;	
		}
	}
*/

	//GET ORDER
	
	$postfields = array();
	$postfields["API"]="shop";
	$postfields["APIRequest"]="OrderDetailGet";
	$postfields["OrderID"]=$_POST["orderid"];
	
	$order = soa2($postfields, __FILE__, __LINE__);
	if ((string)$order->Ack[0]!="Success")
	{
		//show_error();
		exit;	
	}

 	$orderTotalEUR = (string)$order->Order[0]->orderTotalGross[0];
	
	
	// GET IDIMS USER ID FROM cms_users
	$res_idims_user_id = q("SELECT * FROM cms_users WHERE id_user = ".$_SESSION["id_user"], $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res_idims_user_id)==0)
	{
		//USER NICHT GEFUNDEN
		//show_error():
		echo "USER nicht in cms_users gefunden. USERID: ".$_SESSION["id_user"];
		exit;
	}
	$row_idims_user_id = mysqli_fetch_assoc($res_idims_user_id);
	$idims_user_id = $row_idims_user_id["idims_user_id"];
	
	$idims_user_id = 328;

	if ((int)$order->Order[0]->payments_type_id[0]==0)
	{
		//KEINE ZAHLART IN DER BESTELLUNG HINTERLEGT
		//show_error();
		echo "KEINE Zahlmethode in der Bestellung hinterlegt";
		exit;
	}
	$payment_type_id = (int)$order->Order[0]->payments_type_id[0];

	if ((int)$order->Order[0]->invoice_id[0]==0)
	{
		//KEINE RECHNUNGSID IN DER BESTELLUNG HINTERLEGT
		//show_error();
		echo "KEINE RechnungsID in Bestellung hinterlegt";
		exit;
	}
	$invoice_id = (int)$order->Order[0]->invoice_id[0];

	//GET ZLG 
	$res_zlg = q("SELECT * FROM shop_payment_types WHERE id_paymenttype = ".(int)$order->Order[0]->payments_type_id[0], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_zlg)==0)
	{
		//USER NICHT GEFUNDEN
		//show_error():
		echo "Paymenttype nicht in shop_payment_types gefunden. PaymentTypeID: ".(int)$order->Order[0]->payments_type_id[0];
		exit;
	}

	$row_zlg = mysqli_fetch_assoc($res_zlg);
	$zlg = $row_zlg["ZLG"];
	

	$opXml  = '<WEB_ZLG_OP>'."\n";
		$opXml .= '	<ZLG>'."\n";
		//INVOICE ID
		$opXml .= '		<AUFID>'.$invoice_id.'</AUFID>'."\n";
		//IDIMS USERID DES NUTZERS
		$opXml .= '		<USRID>'.$idims_user_id.'</USRID>'."\n";
		// BRUTTO
		$opXml .= '		<BETRAG>'.$orderTotalEUR.'</BETRAG>'."\n";
		// ZLG
		$opXml .= '		<TYP>'.$zlg.'</TYP>'."\n";
		$opXml .= '	</ZLG>'."\n";
		/*
		$opXml .= '	<ZLG>'."\n";
		$opXml .= '		<AUFID>1656304</AUFID>'."\n";
		$opXml .= '		<USRID>266</USRID>'."\n";
		$opXml .= '		<BETRAG>46,80</BETRAG>'."\n";
		$opXml .= '		<TYP>2</TYP>'."\n";
		$opXml .= '	</ZLG>'."\n";
		*/
	$opXml .= '</WEB_ZLG_OP>'."\n";

	echo $opXml;
	$opXml = str_replace("\n", "", $opXml);
	$opXml = str_replace("\t", "", $opXml);


	$serverUrl='http://80.146.160.154/idims/service1.asmx/WEB_ZLG_OP?Token=it@mapco.de&opXML='.urlencode($opXml);
	$headers = array (
		'Content-Type: application/x-www-form-urlencoded',
		'Content-Length:'.strlen($serverUrl),
		'Cache-Control: max-age=0',
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Encoding: gzip,deflate,sdch',
		'Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4'
	);

/*
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
		echo '<OPTranferResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '  <Response><![CDATA['.$responseXml.']]></Response>';
		echo '</OPTransferResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	if (strpos($responseXml, "<ERROR>")>0)
	{
		echo '<OPTransferResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '  <Response><![CDATA['.$responseXml.']]></Response>';
		echo '</OPTransferResponse>'."\n";
		exit;
	}
	else
	{
		echo "OK!";
	}
*/
?>