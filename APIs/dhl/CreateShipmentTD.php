<?php

	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
		//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			if (!is_numeric($val)) $xml.='<![CDATA['.$val.']]>'; else $xml.=$val;
			$xml.='</'.$key.'>';
			
		}
		$xml.='</data>';
		
		//SAVE EVENT
		q("INSERT INTO shop_orders_events (
			order_id, 
			eventtype_id, 
			data, 
			firstmod, 
			firstmod_user
		) VALUES (
			".$order_id.",
			".$eventtype_id.",
			'".mysqli_real_escape_string($dbshop, $xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
		
	}


	if( !isset($_POST["id_order"]) )
	{
		echo '<CreateShipmentTDResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bestellnummer (id_order) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CreateShipmentTDResponse>'."\n";
		exit;
	}

	if( !isset($_POST["ProductCode"]) or $_POST["ProductCode"]=="" )
	{
		echo '<CreateShipmentTDResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellart nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bestellart (ProductCode) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CreateShipmentTDResponse>'."\n";
		exit;
	}

	if( !isset($_POST["WeightInKG"]) or $_POST["WeightInKG"]=="" )
	{
		echo '<CreateShipmentTDResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Paketgewicht nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Paketgewicht ((WeightInKG)) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CreateShipmentTDResponse>'."\n";
		exit;
	}
	$_POST["WeightInKG"]=str_replace(",", ".", $_POST["WeightInKG"]);
	
	//default values
	if( !isset($_POST["DescriptionOfContent"]) ) $_POST["DescriptionOfContent"]='KfZ-Ersatzteile';
	if( !isset($_POST["PackageType"]) ) $_POST["PackageType"]="PK";
	if( !isset($_POST["LengthInCM"]) ) $_POST["LengthInCM"]=60;
	if( !isset($_POST["WidthInCM"]) ) $_POST["WidthInCM"]=40;
	if( !isset($_POST["HeightInCM"]) ) $_POST["HeightInCM"]=30;
	if( !isset($_POST["ShipperCompany"]) ) $_POST["ShipperCompany"]='MAPCO Autotechnik GmbH';
	if( !isset($_POST["ShipperStreetName"]) ) $_POST["ShipperCompany"]='Moosweg';
	if( !isset($_POST["ShipperStreetNumber"]) ) $_POST["ShipperStreetNumber"]='1';
	if( !isset($_POST["ShipperZip"]) ) $_POST["ShipperZip"]='14822';
	if( !isset($_POST["ShipperCity"]) ) $_POST["ShipperCity"]='Borkheide';
	if( !isset($_POST["ShipperOrigin"]) ) $_POST["ShipperOrigin"]='DE';
	if( !isset($_POST["ShipperPhone"]) ) $_POST["ShipperPhone"]='+493384475820';
	if( !isset($_POST["ShipperEmail"]) ) $_POST["ShipperEmail"]='info@mapco.de';
	if( !isset($_POST["ShipperContactPerson"]) ) $_POST["ShipperContactPerson"]='Jens Habermann';

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
		$body["ShipmentOrder"]["SequenceNumber"]=1;
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ProductCode"]=$_POST["ProductCode"];
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentDate"]=date("Y-m-d", time());
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["Dutiable"]=0;
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["DescriptionOfContent"]=$_POST["DescriptionOfContent"];
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["PackageType"]=$_POST["PackageType"];
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["WeightInKG"]=$_POST["WeightInKG"];
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["LengthInCM"]=$_POST["LengthInCM"];
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["WidthInCM"]=$_POST["WidthInCM"];
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["HeightInCM"]=$_POST["HeightInCM"];
		//express deliveries need to be higher insured
		if( $_POST["ProductCode"]=="EXP" )
		{
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["Service"]["ServiceGroupOther"]["HigherInsurance"]["InsuranceAmount"]=2500;
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["Service"]["ServiceGroupOther"]["HigherInsurance"]["InsuranceCurrency"]='EUR';
		}
		//account information for Germany only / export = accountExpressNumber
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["EKP"]=5050030608;
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["Attendance"]["partnerID"]='01';
		//shipper details
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Company"]["Company"]["name1"]=$_POST["ShipperCompany"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Company"]["Company"]["name2"]=$_POST["ShipperCompanyFirstname"].' '.$_POST["ShipperCompanyLastname"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["streetName"]=$_POST["ShipperStreetName"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["streetNumber"]=$_POST["ShipperStreetNumber"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["Zip"]["germany"]=$_POST["ShipperZip"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["city"]=$_POST["ShipperCity"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["Origin"]["countryISOCode"]=$_POST["ShipperOrigin"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Communication"]["phone"]=$_POST["ShipperPhone"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Communication"]["email"]=$_POST["ShipperEmail"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Communication"]["contactPerson"]=$_POST["ShipperContactPerson"];
		//receiver details
		if( $_POST["ReceiverCompany"]!="" )
		{
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Company"]["Company"]["name1"]=$_POST["ReceiverCompany"];
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Company"]["Company"]["name2"]=$_POST["ReceiverCompanyFirstname"].' '.$_POST["ReceiverCompanyLastname"];
		}
		else
		{
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Company"]["Person"]["firstname"]=$_POST["ReceiverCompanyFirstname"];
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Company"]["Person"]["lastname"]=$_POST["ReceiverCompanyLastname"];
		}
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["streetName"]=$_POST["ReceiverStreetName"];
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["streetNumber"]=$_POST["ReceiverStreetNumber"];
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["Zip"]["germany"]=$_POST["ReceiverZip"];
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["city"]=$_POST["ReceiverCity"];
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["Origin"]["countryISOCode"]=$_POST["ReceiverOrigin"];
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Communication"]["phone"]=$_POST["ReceiverPhone"];
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Communication"]["email"]=$_POST["ReceiverEmail"];
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Communication"]["contactPerson"]=$_POST["ReceiverContactPerson"];

		$result = $client->createShipmentTD($body);
		if(is_soap_fault($result))
		{
			echo '<CreateShipmentTDResponse>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Fehler beim Aufruf von CreateShipmentDD</shortMsg>'."\n";
			echo '		<longMsg>'.$result->faultcode.': '.$result->faultstring.'</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CreateShipmentTDResponse>'."\n";
			exit;
		}
	//	echo $client->__getLastRequest();
		$responseXml = $client->__getLastResponse();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}  

	//get label
	$StatusCode=$result->CreationState->StatusCode;
	if( $StatusCode!="0" )
	{
		echo '<CreateShipmentTDResponse>';
		echo '	<Ack>Failure</Ack>';
		echo '	<Response><![CDATA['.$responseXml.']]></Response>';
		echo '</CreateShipmentTDResponse>';
		exit;
	}
	$ShipmentNumber=$result->CreationState->ShipmentNumber->shipmentNumber;
	$LabelURL=$result->CreationState->Labelurl;
	$connection = curl_init();
	curl_setopt($connection, CURLOPT_URL, $LabelURL);
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
	$file = curl_exec($connection);
	curl_close($connection);
	
	//save label
	q("INSERT INTO cms_files (filename, extension, filesize, description, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$ShipmentNumber."', 'pdf', ".strlen($file).", 'DHL-Label', ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].", ".time().");", $dbweb, __FILE__, __LINE__);
	$id_file=mysqli_insert_id($dbweb);
	$path='../../mapco_shop_de/files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
	$LabelURLLocal=PATH.'files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
	$handle=fopen($path, "w");
	fwrite($handle, $file);
	fclose($handle);
	
	//DATA FOR SAVING ORDEREVENT
	$data=array();
	
	//update shop_order
	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	
	if($row["combined_with"]>0)
	{
		if($row["ship_street"]!="")
		{
			q("	UPDATE shop_orders
				SET	ship_company='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompany"])."',
					ship_firstname='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompanyFirstname"])."',
					ship_lastname='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompanyLastname"])."',
					ship_street='".mysqli_real_escape_string($dbshop, $_POST["ReceiverStreetName"])."',
					ship_number='".mysqli_real_escape_string($dbshop, $_POST["ReceiverStreetNumber"])."',
					ship_zip='".mysqli_real_escape_string($dbshop, $_POST["ReceiverZip"])."',
					ship_city='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCity"])."'
				WHERE combined_with =".$row["combined_with"].";", $dbshop, __FILE__, __LINE__);
				
				$data["ship_company"]=$_POST["ReceiverCompany"];
				$data["ship_firstname"]=$_POST["ReceiverCompanyFirstname"];
				$data["ship_lastname"]=$_POST["ReceiverCompanyLastname"];
				$data["ship_street"]=$_POST["ReceiverStreetName"];
				$data["ship_number"]=$_POST["ReceiverStreetNumber"];
				$data["ship_zip"]=$_POST["ReceiverZip"];
				$data["ship_city"]=$_POST["ReceiverCity"];
				
		}
		else
		{
			q("	UPDATE shop_orders
				SET	bill_company='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompany"])."',
					bill_firstname='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompanyFirstname"])."',
					bill_lastname='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompanyLastname"])."',
					bill_street='".mysqli_real_escape_string($dbshop, $_POST["ReceiverStreetName"])."',
					bill_number='".mysqli_real_escape_string($dbshop, $_POST["ReceiverStreetNumber"])."',
					bill_zip='".mysqli_real_escape_string($dbshop, $_POST["ReceiverZip"])."',
					bill_city='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCity"])."'
				WHERE combined_with =".$row["combined_with"].";", $dbshop, __FILE__, __LINE__);
				
				$data["bill_company"]=$_POST["ReceiverCompany"];
				$data["bill_firstname"]=$_POST["ReceiverCompanyFirstname"];
				$data["bill_lastname"]=$_POST["ReceiverCompanyLastname"];
				$data["bill_street"]=$_POST["ReceiverStreetName"];
				$data["bill_number"]=$_POST["ReceiverStreetNumber"];
				$data["bill_zip"]=$_POST["ReceiverZip"];
				$data["bill_city"]=$_POST["ReceiverCity"];

		}
		q("	UPDATE shop_orders
			SET	userphone='".mysqli_real_escape_string($dbshop, $_POST["ReceiverPhone"])."',
				usermail='".mysqli_real_escape_string($dbshop, $_POST["ReceiverEmail"])."',
				shipping_WeightInKG=".$_POST["WeightInKG"].",
				shipping_LengthInCM='".$_POST["LengthInCM"]."',
				shipping_WidthInCM='".$_POST["WidthInCM"]."',
				shipping_HeightInCM='".$_POST["HeightInCM"]."',
				shipping_number='".$ShipmentNumber."',
				shipping_label_file_id=".$id_file.",
				status_id=3,
				status_date=".time()." 
				WHERE combined_with =".$row["combined_with"].";", $dbshop, __FILE__, __LINE__);
				
		$data["userphone"]=$_POST["ReceiverPhone"];
		$data["usermail"]=$_POST["ReceiverEmail"];
		$data["shipping_WeightInKG"]=$_POST["WeightInKG"];
		$data["shipping_LengthInCM"]=$_POST["LengthInCM"];
		$data["shipping_WidthInCM"]=$_POST["WidthInCM"];
		$data["shipping_HeightInCM"]=$_POST["HeightInCM"];
		$data["shipping_number"]=$ShipmentNumber;
		$data["shipping_label_file_id"]=$id_file;
		$data["status_id"]=3;
		$data["status_date"]=time();

	}
	else
	{
		if($row["ship_street"]!="")
		{
			q("	UPDATE shop_orders
				SET	ship_company='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompany"])."',
					ship_firstname='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompanyFirstname"])."',
					ship_lastname='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompanyLastname"])."',
					ship_street='".mysqli_real_escape_string($dbshop, $_POST["ReceiverStreetName"])."',
					ship_number='".mysqli_real_escape_string($dbshop, $_POST["ReceiverStreetNumber"])."',
					ship_zip='".mysqli_real_escape_string($dbshop, $_POST["ReceiverZip"])."',
					ship_city='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCity"])."'
				WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
				
				$data["ship_company"]=$_POST["ReceiverCompany"];
				$data["ship_firstname"]=$_POST["ReceiverCompanyFirstname"];
				$data["ship_lastname"]=$_POST["ReceiverCompanyLastname"];
				$data["ship_street"]=$_POST["ReceiverStreetName"];
				$data["ship_number"]=$_POST["ReceiverStreetNumber"];
				$data["ship_zip"]=$_POST["ReceiverZip"];
				$data["ship_city"]=$_POST["ReceiverCity"];

		}
		else
		{
			q("	UPDATE shop_orders
				SET	bill_company='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompany"])."',
					bill_firstname='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompanyFirstname"])."',
					bill_lastname='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompanyLastname"])."',
					bill_street='".mysqli_real_escape_string($dbshop, $_POST["ReceiverStreetName"])."',
					bill_number='".mysqli_real_escape_string($dbshop, $_POST["ReceiverStreetNumber"])."',
					bill_zip='".mysqli_real_escape_string($dbshop, $_POST["ReceiverZip"])."',
					bill_city='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCity"])."'
				WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
				
				$data["bill_company"]=$_POST["ReceiverCompany"];
				$data["bill_firstname"]=$_POST["ReceiverCompanyFirstname"];
				$data["bill_lastname"]=$_POST["ReceiverCompanyLastname"];
				$data["bill_street"]=$_POST["ReceiverStreetName"];
				$data["bill_number"]=$_POST["ReceiverStreetNumber"];
				$data["bill_zip"]=$_POST["ReceiverZip"];
				$data["bill_city"]=$_POST["ReceiverCity"];

		}
		q("	UPDATE shop_orders
			SET	userphone='".mysqli_real_escape_string($dbshop, $_POST["ReceiverPhone"])."',
				usermail='".mysqli_real_escape_string($dbshop, $_POST["ReceiverEmail"])."',
				shipping_WeightInKG=".$_POST["WeightInKG"].",
				shipping_LengthInCM='".$_POST["LengthInCM"]."',
				shipping_WidthInCM='".$_POST["WidthInCM"]."',
				shipping_HeightInCM='".$_POST["HeightInCM"]."',
				shipping_number='".$ShipmentNumber."',
				shipping_label_file_id=".$id_file.",
				status_id=3,
				status_date=".time()." 
			WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
			
		$data["userphone"]=$_POST["ReceiverPhone"];
		$data["usermail"]=$_POST["ReceiverEmail"];
		$data["shipping_WeightInKG"]=$_POST["WeightInKG"];
		$data["shipping_LengthInCM"]=$_POST["LengthInCM"];
		$data["shipping_WidthInCM"]=$_POST["WidthInCM"];
		$data["shipping_HeightInCM"]=$_POST["HeightInCM"];
		$data["shipping_number"]=$ShipmentNumber;
		$data["shipping_label_file_id"]=$id_file;
		$data["status_id"]=3;
		$data["status_date"]=time();

	}
		
	/*	
		//SET ORDER EVENT "SHIPMENT"
	$responseXML=post(PATH."soa/", array("API" => "crm", "Action" => "set_orderEvents", "event" => "Shipment", "order_id" => $_POST["id_order"]));
	try
	{
		$xml = new SimpleXMLElement($responseXML);
		if ($xml->Ack[0]!="Success")
		{
			error_logs(__FILE__, __LINE__, $responseXML);
		}

	}
	catch(Exception $e)
	{
		error_logs(__FILE__, __LINE__, $e->getMessage());
	}  
*/
	echo '<CreateShipmentTDResponse>';
	echo '	<Ack>Success</Ack>';
	echo '	<ShipmentNumber>'.$ShipmentNumber.'</ShipmentNumber>';
	echo '	<LabelURL>'.$LabelURL.'</LabelURL>';
	echo '	<LabelURLLocal>'.$LabelURLLocal.'</LabelURLLocal>';
	echo '	<Response><![CDATA['.$responseXml.']]></Response>';
	echo '</CreateShipmentTDResponse>';

	//SET ORDEREVENT
	$id_event=save_order_event(12, $_POST["id_order"], $data);
	
?>