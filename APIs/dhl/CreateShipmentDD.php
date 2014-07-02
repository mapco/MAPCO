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


	if( !isset($_POST["ProductCode"]) or $_POST["ProductCode"]=="" )
	{
		echo '<CreateShipmentDDResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellart nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bestellart (ProductCode) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CreateShipmentDDResponse>'."\n";
		exit;
	}

	if( !isset($_POST["WeightInKG"]) or $_POST["WeightInKG"]=="" )
	{
		echo '<CreateShipmentDDResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Paketgewicht nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Paketgewicht ((WeightInKG)) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CreateShipmentDDResponse>'."\n";
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
	if( !isset($_POST["ShipperCompany2"]) ) $_POST["ShipperCompany2"]='';
	if( !isset($_POST["ShipperStreetName"]) ) $_POST["ShipperCompany"]='Moosweg';
	if( !isset($_POST["ShipperStreetNumber"]) ) $_POST["ShipperStreetNumber"]='1';
	if( !isset($_POST["ShipperZip"]) ) $_POST["ShipperZip"]='14822';
	if( !isset($_POST["ShipperCity"]) ) $_POST["ShipperCity"]='Borkheide';
	if( !isset($_POST["ShipperOrigin"]) ) $_POST["ShipperOrigin"]='DE';
	if( !isset($_POST["ShipperPhone"]) ) $_POST["ShipperPhone"]='+493384475820';
	if( !isset($_POST["ShipperEmail"]) ) $_POST["ShipperEmail"]='info@mapco.de';
	if( !isset($_POST["ShipperContactPerson"]) ) $_POST["ShipperContactPerson"]='Jens Habermann';

	//save weight before errors can occur
	if( isset($_POST["id_order"]) and $_POST["id_order"]!="" )
	{
		q("	UPDATE shop_orders
		SET	shipping_WeightInKG='".$_POST["WeightInKG"]."',
			shipping_LengthInCM='".$_POST["LengthInCM"]."',
			shipping_WidthInCM='".$_POST["WidthInCM"]."',
			shipping_HeightInCM='".$_POST["HeightInCM"]."'
		WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
	}

	//get order if possible
	if( isset($_POST["id_order"]) and $_POST["id_order"]!="" )
	{
		$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
		$shop_orders=mysqli_fetch_array($results);
	}

	//create call
	try
	{
		if( $_SESSION["id_user"]==213711 )
		{
			$client = new SoapClient("https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/geschaeftskundenversand-api/1.0/geschaeftskundenversand-api-1.0.wsdl",
            	array('login' => "mapco",
                  'password' =>  "Merci2664!",
                  'location' =>  "https://cig.dhl.de/services/production/soap"));

			$auth->user = 'intraship.mapco';
			$auth->signature = 'Merci2664!';
			$auth->accountNumber = NULL;
			$auth->type = '0';
			$header = new SoapHeader('http://dhl.de/webservice/cisbase', 'Authentification', $auth);
			$client->__setSoapHeaders($header);
		}
		else
		{
			$client = new SoapClient("http://www.intraship.de/ws/1_0/ISService/DE.wsdl", array('trace' => 1, 'soap_version' => SOAP_1_2, 'encoding'=>'UTF-8'));
			$auth->user = 'intraship.mapco';
			$auth->signature = 'Merci2664!';
			$auth->type = '0';
			$header = new SoapHeader('http://dhl.de/webservice/cisbase','Authentification', $auth, false);
			$client->__setSoapHeaders($header);
		}

		$body=array();
		$body["Version"]["majorRelease"]=1;
		$body["Version"]["minorRelease"]=0;
		$body["ShipmentOrder"]["SequenceNumber"]=1;
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["CustomerReference"]=$_POST["CustomerReference"];
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ProductCode"]=$_POST["ProductCode"];
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentDate"]=date("Y-m-d", time());
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["Dutiable"]=0;
		$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["DescriptionOfContent"]=$_POST["DescriptionOfContent"];
		if($_POST["ProductCode"]=="BPI")
		{
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["Service"]["ServiceGroupBusinessPackInternational"]["Premium"]="true";
		}
		
		
		//multiple shipment items
		$WeightInKG=explode(";", $_POST["WeightInKG"]);
		$LengthInCM=explode(";", $_POST["LengthInCM"]);
		$WidthInCM=explode(";", $_POST["WidthInCM"]);
		$HeightInCM=explode(";", $_POST["HeightInCM"]);
		if( sizeof($WeightInKG)>1 )
		{
			if( $_POST["ProductCode"]=="EPN" ) $body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["Service"]["ServiceGroupDHLPaket"]["Multipack"]="true";
			for($i=0; $i<sizeof($WeightInKG); $i++)
			{
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"][$i]["PackageType"]=$_POST["PackageType"];
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"][$i]["WeightInKG"]=$WeightInKG[$i];
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"][$i]["LengthInCM"]=$LengthInCM[$i];
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"][$i]["WidthInCM"]=$WidthInCM[$i];
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"][$i]["HeightInCM"]=$HeightInCM[$i];
			}
		}
		else
		{
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["PackageType"]=$_POST["PackageType"];
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["WeightInKG"]=$_POST["WeightInKG"];
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["LengthInCM"]=$_POST["LengthInCM"];
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["WidthInCM"]=$_POST["WidthInCM"];
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["ShipmentItem"]["HeightInCM"]=$_POST["HeightInCM"];
		}
		
		//CASH ON DELIVERY - Nachnahme
		if ( $_POST["CODAmount"]>0 )
		{
			//COD details
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["Service"]["ServiceGroupOther"]["COD"]["CODAmount"]=$_POST["CODAmount"];
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["Service"]["ServiceGroupOther"]["COD"]["CODCurrency"]="EUR";
			//bank details
			if( $shop_orders["shop_id"]>=9 and $shop_orders["shop_id"]<=18 )
			{
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["accountOwner"]="MAPCO Autotechnik GmbH";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["accountNumber"]="5152043009";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["bankCode"]="10090000";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["bankName"]="Berliner Volksbank eG";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["iban"]="DE75100900005152043009";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["bic"]="BEVODEBB";
			}
			else
			{
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["accountOwner"]="MAPCO Autotechnik GmbH";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["accountNumber"]="5152043041";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["bankCode"]="10090000";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["bankName"]="Berliner Volksbank eG";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["iban"]="DE84100900005152043041";
				$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["bic"]="BEVODEBB";
			}
			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["BankData"]["note"]=$_POST["CustomerReference"];
			
			//for international COD
//			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["DeclaredValueOfGoods"]=$_POST["CODAmount"];
//			$body["ShipmentOrder"]["Shipment"]["ShipmentDetails"]["DeclaredValueOfGoodsCurrency"]="EUR";
		}
		
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
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Company"]["Company"]["name2"]=$_POST["ShipperCompany2"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["streetName"]=$_POST["ShipperStreetName"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["streetNumber"]=$_POST["ShipperStreetNumber"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["Zip"]["germany"]=$_POST["ShipperZip"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["city"]=$_POST["ShipperCity"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Address"]["Origin"]["countryISOCode"]=$_POST["ShipperOrigin"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Communication"]["phone"]=$_POST["ShipperPhone"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Communication"]["email"]=$_POST["ShipperEmail"];
		$body["ShipmentOrder"]["Shipment"]["Shipper"]["Communication"]["contactPerson"]=$_POST["ShipperCompany2"];
		//receiver details
		if( $_POST["ReceiverCompany"]!="" or $_POST["ReceiverCompany2"]!="")
		{
			if($_POST["ReceiverCompany"]=="") $_POST["ReceiverCompany"]=substr($_POST["ReceiverCompanyFirstname"].' '.$_POST["ReceiverCompanyLastname"], 0, 50);
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Company"]["Company"]["name1"]=substr($_POST["ReceiverCompany"], 0, 50);
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Company"]["Company"]["name2"]=substr($_POST["ReceiverCompany2"], 0, 50);
		}
		else
		{
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Company"]["Person"]["firstname"]=substr($_POST["ReceiverCompanyFirstname"], 0, 50);
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Company"]["Person"]["lastname"]=substr($_POST["ReceiverCompanyLastname"], 0, 50);
		}
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["streetName"]=substr($_POST["ReceiverStreetName"], 0, 40);
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["streetNumber"]=substr($_POST["ReceiverStreetNumber"], 0, 7);
		if( $_POST["ReceiverOrigin"]=="DE" )
		{
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["Zip"]["germany"]=$_POST["ReceiverZip"];
		}
		elseif( $_POST["ReceiverOrigin"]=="GB" )
		{
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["Zip"]["england"]=$_POST["ReceiverZip"];
		}
		else
		{
			$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["Zip"]["other"]=$_POST["ReceiverZip"];
		}
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["city"]=substr($_POST["ReceiverCity"], 0, 50);
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Address"]["Origin"]["countryISOCode"]=$_POST["ReceiverOrigin"];
		if( isset($_POST["ReceiverPhone"]) and $_POST["ReceiverPhone"]=="" ) $_POST["ReceiverPhone"]="0";
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Communication"]["phone"]=substr($_POST["ReceiverPhone"], 0, 20);
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Communication"]["email"]=substr($_POST["ReceiverEmail"], 0, 50);
		$body["ShipmentOrder"]["Shipment"]["Receiver"]["Communication"]["contactPerson"]=substr($_POST["ReceiverContactPerson"], 0, 50);


		//customs
		if( $_POST["Customs"]==1 )
		{
//			echo 'aaa';
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["InvoiceType"]="commercial";
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["InvoiceDate"]=date("Y-m-d", time());
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["TermsOfTrade"]="CIP";
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["Amount"]="1";
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["Description"]="";
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["CountryCodeOrigin"]=$_POST["ShipperOrigin"];
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportType"]=0;
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportTypeDescription"]="Sale";

			$i=0;
			$CustomsValue=0;
			$results=q("SELECT * FROM shop_orders_items WHERE order_id=".$_POST["id_order"]." LIMIT 5;", $dbshop, __FILE__, __LINE__);
			while( $row=mysqli_fetch_array($results) )
			{
				$CustomsValue+=$row["amount"]*$row["price"];
				$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportDocPosition"][$i]["Amount"]=$row["amount"];
				$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportDocPosition"][$i]["CustomsValue"]=$row["price"];
				$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportDocPosition"][$i]["CustomsCurrency"]="EUR";

				$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
				$shop_items=mysqli_fetch_array($results2);
				if( $shop_items["ItemWeight"]==0 ) $shop_items["ItemWeight"]=1000;
				$NetWeightInKG=ceil($shop_items["ItemWeight"]/1000);
				$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportDocPosition"][$i]["NetWeightInKG"]=$NetWeightInKG;
				if( $shop_items["GrossWeight"]==0 ) $shop_items["GrossWeight"]=1300;
				if( $shop_items["GrossWeight"]<=$shop_items["ItemWeight"] )
				{
					$shop_items["GrossWeight"]=$shop_items["ItemWeight"]*1.3;
				}
				$GrossWeightInKG=ceil($shop_items["GrossWeight"]/1000);
				$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportDocPosition"][$i]["GrossWeightInKG"]=$GrossWeightInKG;
				if( $shop_items["CommodityCode"]=="" )
				{
					$results2=q("SELECT * FROM shop_items_duty_numbers WHERE GART=".$shop_items["GART"].";", $dbshop, __FILE__, __LINE__);
					$row2=mysqli_fetch_array($results2);
					$shop_items["CommodityCode"]=$row2["duty_number"];
				}
				$shop_items["CommodityCode"]=str_replace(" ", "", $shop_items["CommodityCode"]);
				$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportDocPosition"][$i]["CommodityCode"]=$shop_items["CommodityCode"];

				$results2=q("SELECT * FROM shop_items_en WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
				$shop_items_en=mysqli_fetch_array($results2);
				$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportDocPosition"][$i]["Description"]=substr($shop_items_en["title"], 0, 40);
				
				$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["ExportDocPosition"][$i]["CountryCodeOrigin"]=$_POST["ShipperOrigin"];
				$i++;
			}
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["CustomsValue"]=$CustomsValue;
			$body["ShipmentOrder"]["Shipment"]["ExportDocument"]["CustomsCurrency"]="EUR";
		}
		
		$result = $client->createShipmentDD($body);
		if(is_soap_fault($result))
		{
			echo '<CreateShipmentDDResponse>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Fehler beim Aufruf von CreateShipmentDD</shortMsg>'."\n";
			echo '		<longMsg>'.$result->faultcode.': '.$result->faultstring.'</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CreateShipmentDDResponse>'."\n";
			exit;
		}
		$requestXml = $client->__getLastRequest();
//		if( $_SESSION["id_user"]==21371 ) echo $requestXml;
		$responseXml = $client->__getLastResponse();
	}
	catch(Exception $e)
	{
		echo $requestXml = $client->__getLastRequest();
		echo $responseXml = $client->__getLastResponse();
		echo '<CreateShipmentDDResponse>';
		echo '	<Ack>Failure</Ack>';
		echo '	<Msg>'.$e->getMessage().'</Msg>';
		echo '	<Request><![CDATA['.$requestXml.']]></Request>';
		echo '	<Response><![CDATA['.$responseXml.']]></Response>';
		echo '</CreateShipmentDDResponse>';
		exit;
	}  

	//get label
	$StatusCode=$result->CreationState->StatusCode;
	if( $StatusCode!="0" )
	{
		echo '<CreateShipmentDDResponse>';
		echo '	<Ack>Failure</Ack>';
		echo '	<Request><![CDATA['.$requestXml.']]></Request>';
		echo '	<Response><![CDATA['.$responseXml.']]></Response>';
		echo '</CreateShipmentDDResponse>';
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
	
	if( isset($_POST["id_order"]) and $_POST["id_order"]!="" )
	{
		//save label
		q("INSERT INTO cms_files (filename, extension, filesize, description, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$ShipmentNumber."', 'pdf', ".strlen($file).", 'DHL-Label', ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].", ".time().");", $dbweb, __FILE__, __LINE__);
		$id_file=mysqli_insert_id($dbweb);
		$directory='../../mapco_shop_de/files/'.bcdiv($id_file, 1000);
		if( !is_dir($directory) ) mkdir($directory);
		$path='../../mapco_shop_de/files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
		$LabelURLLocal=PATH.'files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
		$LabelPath='files/'.bcdiv($id_file, 1000).'/'.$id_file.'.pdf';
		$handle=fopen($path, "w");
		fwrite($handle, $file);
		fclose($handle);
		
		//DATA FOR SAVING ORDEREVENT
		$data=array();

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
						bill_additional='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompany2"])."',
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
				shipping_WeightInKG='".$_POST["WeightInKG"]."',
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
						bill_additional='".mysqli_real_escape_string($dbshop, $_POST["ReceiverCompany2"])."',
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
				shipping_WeightInKG='".$_POST["WeightInKG"]."',
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
	} //end if ( isset($_POST["id_order"]) )
	else
	{
		//create data file
		$fieldset=array();
		$fieldset["API"]="cms";
		$fieldset["Action"]="TempFileAdd";
		$fieldset["extension"]="pdf";
		$responseXml = post(PATH."soa/", $fieldset);
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<EbayAuctionsUploadResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Temporärdatei anlegen fehlgeschlagen.</shortMsg>'."\n";
			echo '		<longMsg>Beim Anlegen einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</EbayAuctionsUploadResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$LabelPath=(string)$response->File[0];

		$handle=fopen("../../mapco_shop_de/".$LabelPath, "w");
		fwrite($handle, $file);
		fclose($handle);

		$LabelURL="";
		$LabelURLLocal=PATH.$LabelPath;
	}

	//save export documents
	if( $_POST["Customs"]==1 )
	{
		post(PATH."soa/", array( "API" => "dhl", "Action" => "GetExportDocDD", "id_order" => $_POST["id_order"] ));
	}

	echo '<CreateShipmentDDResponse>';
	echo '	<Ack>Success</Ack>';
	echo '	<ShipmentNumber>'.$ShipmentNumber.'</ShipmentNumber>';
	echo '	<LabelPath>'.$LabelPath.'</LabelPath>';
	echo '	<LabelURL>'.$LabelURL.'</LabelURL>';
	echo '	<LabelURLLocal>'.$LabelURLLocal.'</LabelURLLocal>';
	echo '	<Response><![CDATA['.$responseXml.']]></Response>';
	echo '</CreateShipmentDDResponse>';
	
	//SET ORDEREVENT
	if( isset($_POST["id_order"]) and $_POST["id_order"]!="" )
	{
		$id_event=save_order_event(12, $_POST["id_order"], $data);
	}
?>