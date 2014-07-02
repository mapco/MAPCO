<?php
	if( !isset($_POST["id_order"]) )
	{
		echo '<GetExportDocDDResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bestellnummer (id_order) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetExportDocDDResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<GetExportDocDDResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Bestellnummer (id_order) konnte keine Bestellung in der Datenbank gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetExportDocDDResponse>'."\n";
		exit;
	}
	$shop_orders=mysqli_fetch_array($results);

	if( $shop_orders["shipping_number"]=="" )
	{
		echo '<GetExportDocDDResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sendungsnummer ist leer.</shortMsg>'."\n";
		echo '		<longMsg>Die Bestellung enthält keine Sendungsnummer (shipping_number). Die Sendungsnummer kann mit CreateShipmentDD erstellt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetExportDocDDResponse>'."\n";
		exit;
	}


	//check if export document already exists
	if( $shop_orders["shipping_customs_file_id"]>0 )
	{
		$id_file=$shop_orders["shipping_customs_file_id"];
		$LabelPath='files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
		$LabelURLLocal=PATH.'files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
		echo '<GetExportDocDDResponse>';
		echo '	<Ack>Success</Ack>';
		echo '	<LabelPath>'.$LabelPath.'</LabelPath>';
		echo '	<LabelURLLocal>'.$LabelURLLocal.'</LabelURLLocal>';
		echo '</GetExportDocDDResponse>';
		exit;
	}

	try
	{
		$client = new SoapClient("http://www.intraship.de/ws/1_0/ISService/DE.wsdl", array('trace' => 1, 'soap_version'   => SOAP_1_2));
		$auth->user = 'intraship.mapco';
		$auth->signature = 'Merci2664!';
		$auth->type = '0';
		$header = new SoapHeader('http://dhl.de/webservice/cisbase','Authentification', $auth, false);
		$client->__setSoapHeaders($header);

		$body=array();
		$body["Version"]["majorRelease"]=1;
		$body["Version"]["minorRelease"]=0;
		$body["ShipmentNumber"]["shipmentNumber"]=$shop_orders["shipping_number"];
		$result = $client->GetExportDocDD($body);
		$requestXml = $client->__getLastRequest();
		$responseXml = $client->__getLastResponse();

		//get label
		$StatusCode=$result->status->StatusCode;
		if( $StatusCode!="0" )
		{
			echo '<GetExportDocDDResponse>';
			echo '	<Ack>Failure</Ack>';
			echo '	<Response><![CDATA['.$responseXml.']]></Response>';
			echo '	<Request><![CDATA['.$requestXml.']]></Request>';
			echo '</GetExportDocDDResponse>';
			exit;
		}
		$ExportDoc=$result->ExportDocData->ExportDocPDFData;
		$file=base64_decode($ExportDoc);
/*
		$connection = curl_init();
		curl_setopt($connection, CURLOPT_URL, $LabelURL);
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
		$file = curl_exec($connection);
		curl_close($connection);
*/	
		//save label
		q("INSERT INTO cms_files (filename, extension, filesize, description, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$shop_orders["shipping_number"]."', 'pdf', ".strlen($file).", 'DHL-Label', ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].", ".time().");", $dbweb, __FILE__, __LINE__);
		$id_file=mysqli_insert_id($dbweb);
		$path='../../mapco_shop_de/files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
		$LabelPath='files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
		$LabelURLLocal=PATH.'files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
		$handle=fopen($path, "w");
		fwrite($handle, $file);
		fclose($handle);
	
		//update shop_order
		q("	UPDATE shop_orders
			SET	shipping_customs_file_id=".$id_file."
			WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
	
		echo '<GetExportDocDDResponse>';
		echo '	<Ack>Success</Ack>';
		echo '	<LabelPath>'.$LabelPath.'</LabelPath>';
		echo '	<LabelURLLocal>'.$LabelURLLocal.'</LabelURLLocal>';
//		echo '	<Response><![CDATA['.$responseXml.']]></Response>';
		echo '</GetExportDocDDResponse>';
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}
?>