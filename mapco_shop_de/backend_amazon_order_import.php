<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	if ( isset($_POST["form_button"]) )
	{
		//add order if new
		if( $id_order==0 )
		{
			$change=true;
			$results2=q("SELECT * FROM shop_orders WHERE ordernr='".$_POST["ordernr"]."';", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results2)==0 )
			{
				$data=array();
				$data["API"]="shop";
				$data["APIRequest"]="OrderAdd";
				$data["mode"]="new";
				$data["shop_id"]=23; //MAPCO Online-Shop
				$data["ordertype_id"]=3; //order via mail
				$data["status_id"]=1; //newly added order
				$data["status_date"]=time();
				$data["Currency_Code"]="GBP";
				$data["VAT"]=0;
				$data["customer_id"]=$_POST["form_user_id"]; //Amazon UK
				$data["ordernr"]=$_POST["ordernr"];
				$data["usermail"]="info@amazon.co.uk";
				$data["userphone"]="00000";
				//bill address
				$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_POST["form_user_id"]." AND standard=1;", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results)==0 )
				{
					echo '<div class="failure">Standard-Rechnungsadresse nicht gefunden. Bitte legen Sie eine Standard-Rechnungsadresse für den Benutzer an.</div>';
					exit;
				}
				$row=mysqli_fetch_array($results);
				$data["bill_company"]=$row["company"];
				if( $row["gender"]==0 ) $gender="Herr"; else $gender="Frau";
				$data["bill_gender"]=$gender;
				$data["bill_title"]=$row["title"];
				$data["bill_firstname"]=$row["firstname"];
				$data["bill_lastname"]=$row["lastname"];
				$data["bill_street"]=$row["street"];
				$data["bill_number"]=$row["number"];
				$data["bill_additional"]=$row["additional"];
				$data["bill_zip"]=$row["zip"];
				$data["bill_city"]=$row["city"];
				$data["bill_country"]=$row["country"];
				$results2=q("SELECT * FROM shop_countries WHERE id_country=".$row["country_id"].";", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$data["bill_country_code"]=$row2["country_code"];
				$data["bill_adr_id"]=$row["adr_id"];
				//ship address
				$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_POST["form_user_id"]." AND standard_ship_adr=1;", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results)==0 )
				{
					echo '<div class="failure">Standard-Lieferadresse nicht gefunden. Bitte legen Sie eine Standard-Rechnungsadresse für den Benutzer an.</div>';
					exit;
				}
				$row=mysqli_fetch_array($results);
				$data["ship_company"]=$row["company"];
				if( $row["gender"]==0 ) $gender="Herr"; else $gender="Frau";
				$data["ship_gender"]=$gender;
				$data["ship_title"]=$row["title"];
				$data["ship_firstname"]=$row["firstname"];
				$data["ship_lastname"]=$row["lastname"];
				$data["ship_street"]=$row["street"];
				$data["ship_number"]=$row["number"];
				$data["ship_additional"]=$row["additional"];
				$data["ship_zip"]=$row["zip"];
				$data["ship_city"]=$row["city"];
				$data["ship_country"]=$row["country"];
				$results2=q("SELECT * FROM shop_countries WHERE id_country=".$row["country_id"].";", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$data["ship_country_code"]=$row2["country_code"];
				$data["ship_adr_id"]=$row["adr_id"];
				//other data
				$data["shipping_costs"]=0; //free shipping
				$data["shipping_type_id"]=5; //DHL International
				$data["payments_type_id"]=1; //DHL International
				$data["shipping_net"]=0; //free shipping
				$responseXml=post(PATH."soa2/", $data);
				try
				{
					$response = new SimpleXMLElement($responseXml);
				}
				catch(Exception $e)
				{
					echo '<startUploadJobResponse>'."\n";
					echo '	<Ack>Failure</Ack>'."\n";
					echo '	<Error>'."\n";
					echo '		<Code>'.__LINE__.'</Code>'."\n";
					echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
					echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
					echo '	</Error>'."\n";
					echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
					echo '</startUploadJobResponse>'."\n";
					exit;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				$id_order=(integer)$response->id_order[0];
			}
			else
			{
				$row2=mysqli_fetch_array($results2);
				if( $row2["status_id"]!=1 )
				{
					$change=false;
					echo '<div class="failures">Die Bestellung ist nicht mehr änderbar.</div>';
//					exit;
				}
				$id_order=$row2["id_order"];
			}
		}

		//check bill address
		$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_POST["form_user_id"]." AND standard=1;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<div class="failure">Standard-Rechnungsadresse nicht gefunden. Bitte legen Sie eine Standard-Rechnungsadresse für den Benutzer an.</div>';
			exit;
		}
		$row=mysqli_fetch_array($results);
		$data=array();
		$data["VAT"]=0;
		$data["bill_company"]=$row["company"];
		if( $row["gender"]==0 ) $gender="Herr"; else $gender="Frau";
		$data["bill_gender"]=$row["gender"];
		$data["bill_title"]=$row["title"];
		$data["bill_firstname"]=$row["firstname"];
		$data["bill_lastname"]=$row["lastname"];
		$data["bill_street"]=$row["street"];
		$data["bill_number"]=$row["number"];
		$data["bill_zip"]=$row["zip"];
		$data["bill_city"]=$row["city"];
		$data["bill_country"]=$row["country"];
		$results2=q("SELECT * FROM shop_countries WHERE id_country=".$row["country_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$data["bill_country_code"]=$row2["country_code"];
		$data["bill_adr_id"]=$row["adr_id"];
		//check ship address
		$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_POST["form_user_id"]." AND standard_ship_adr=1;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<div class="failure">Standard-Rechnungsadresse nicht gefunden. Bitte legen Sie eine Standard-Rechnungsadresse für den Benutzer an.</div>';
			exit;
		}
		$row=mysqli_fetch_array($results);
		$data["ship_company"]=$row["company"];
		if( $row["gender"]==0 ) $gender="Herr"; else $gender="Frau";
		$data["customer_id"]=$_POST["form_user_id"];
		$data["ship_gender"]=$gender;
		$data["ship_title"]=$row["title"];
		$data["ship_firstname"]=$row["firstname"];
		$data["ship_lastname"]=$row["lastname"];
		$data["ship_street"]=$row["street"];
		$data["ship_number"]=$row["number"];
		$data["ship_zip"]=$row["zip"];
		$data["ship_city"]=$row["city"];
		$data["ship_country"]=$row["country"];
		$data["ship_adr_id"]=$row["adr_id"];
		$results2=q("SELECT * FROM shop_countries WHERE id_country=".$row["country_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$data["ship_country_code"]=$row2["country_code"];
		$data["ship_adr_id"]=$row["adr_id"];
		if( $change ) q_update("shop_orders", $data, "WHERE id_order=".$id_order.";", $dbshop, __FILE__, __LINE__);


		//reopen Excel file
		$responseXml = file_get_contents("temp/".$_POST["form_file"]);
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		//import order items and update xml file
		for($i=0; $i<sizeof($_POST["amount"]); $i++)
		{
//			print_r($response->Worksheet[0]->Table[0]->Row[$i+4]->Cell[8]);
			if( $_POST["amount"][$i]==-1 ) $AvailabilityStatus="CP - Cancelled: Discontinued";
			elseif( $_POST["amount"][$i]==0 ) $AvailabilityStatus="CO - Cancelled: Out of stock";
			else $AvailabilityStatus="AC - Accepted and shipped";
			$response->Worksheet[0]->Table[0]->Row[$i+4]->Cell[19]->Data=$AvailabilityStatus;
			if($_POST["amount"][$i]<1) $_POST["amount"][$i]=0; 
			$response->Worksheet[0]->Table[0]->Row[$i+4]->Cell[10]->Data=$_POST["amount"][$i];
			//add or update ordered item
			if( !isset($exchange_rate_to_EUR) )
			{
				$results2=q("SELECT * FROM shop_currencies WHERE currency_code='GBP';", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$exchange_rate_to_EUR=$row2["exchange_rate_to_EUR"];
			}
			$results2=q("SELECT * FROM shop_items WHERE MPN='".$_POST["ArtNr"][$i]."';", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$id_item=$row2["id_item"];
			//brake disc fix
			if( $row2["GART"]==82 and strpos($row2["MPN"], "/2") === false )
			{
				$results2=q("SELECT * FROM shop_items WHERE MPN='".$_POST["ArtNr"][$i]."/2';", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results2)==0 )
				{
					echo 'FEHLER: Keinen /2-Satz zu Bremsscheibe gefunden.';
					exit;
				}
				$row2=mysqli_fetch_array($results2);
				$id_item=$row2["id_item"];
			}
			$results2=q("SELECT * FROM shop_orders_items WHERE order_id=".$id_order." AND item_id=".$id_item.";", $dbshop, __FILE__, __LINE__);
			$data=array();
			if( mysqli_num_rows($results2)==0 )
			{
				$data["APIRequest"]="OrderItemAdd";
				$data["mode"]="new";
			}
			else
			{
				$row2=mysqli_fetch_array($results2);
				$data["SELECTOR_id"]=$row2["id"];
				$data["APIRequest"]="OrderItemUpdate";
				
			}
			$data["API"]="shop";
			$data["order_id"]=$id_order;
			$data["item_id"]=$id_item;
			$data["amount"]=$_POST["amount"][$i];
			$data["price"]=$_POST["price"][$i];
			$data["netto"]=$_POST["netto"][$i];
			$data["collateral"]=0;
			$data["Currency_Code"]="GBP";
			$data["exchange_rate_to_EUR"]=$exchange_rate_to_EUR;
			$data["customer_vehicle_id"]=0;
			$data["checked"]=0;
			$data["checked_by_user"]=0;
			if( $change )
			{
				if( $_POST["amount"][$i]>0 or $data["APIRequest"]=="OrderItemUpdate" ) post(PATH."soa2/", $data);
			}
		}//end foreach
		
		//write changes to excel file
		$response->asXml("temp/".$_POST["form_file"]);

		//show success
		echo '<div class="success">';
		echo '	Bestellung '.$id_order.' erfolgreich importiert.';
		echo '	<br /><br /><a target="_blank" href="'.PATH.'backend_crm_orders.php?lang=de&id_menuitem=224&jump_to=order&orderid='.$id_order.'&order_type=3">Zum Bestellmanagement</a>';
		echo '	<br /><br /><a target="_blank" href="'.PATH.'temp/'.$_POST["form_file"].'">Exceldatei herunterladen</a>';
		echo '</div>';
	}


	if ( isset($_FILES["file"]) and isset($_POST["form_upload"]) )
	{
		//cache lager
		$items=array();
		$results=q("SELECT * FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$items[$row["MPN"]]=1;
		}

		//cache lager
		$lager=array();
		$results=q("SELECT * FROM lager;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$lager[$row["ArtNr"]]=$row["ISTBESTAND"]+$row["MOCOMBESTAND"]+$row["AMAZONBESTAND"];
		}
		
		//cache lagerrc
		$lagerrc=array();
		$results=q("SELECT * FROM lagerrc;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$lagerrc[$row["RCBEZ"]][$row["ARTNR"]]=$row["ISTBESTAND"];
		}
		
		//cache rcbez
		$rcbez=array();
		$results=q("SELECT * FROM lagerrc GROUP BY RCBEZ;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$rcbez[]=$row["RCBEZ"];
		}
		
		//create data file
		$fieldset=array();
		$fieldset["API"]="cms";
		$fieldset["Action"]="TempFileAdd";
		$fieldset["extension"]="xls";
		$responseXml = post(PATH."soa/", $fieldset);
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Temporärdatei anlegen fehlgeschlagen.</shortMsg>'."\n";
			echo '		<longMsg>Beim Anlegen einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$tempfile=(string)$response->Filename[0];
		move_uploaded_file($_FILES["file"]["tmp_name"], "temp/".$tempfile);
		$responseXml = file_get_contents("temp/".$tempfile);
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);

		$nr=0;
		echo '<form method="post">';
		echo '<table class="hover">';
		$rows=$response->Worksheet[0]->Table[0]->Row;
		foreach($rows as $row)
		{
			$nr++;
			if( $nr>3 )
			{
				$ArtNr=(string)$row->Cell[2]->Data[0];
				$amount=(string)$row->Cell[8]->Data[0];
				$netto=(string)$row->Cell[7]->Data[0];
				$netto=utf8_decode($netto);
				$netto=substr($netto, 1, strlen($netto))*1;
				$price=round($netto*1.19, 2);
				$results2=q("SELECT * FROM shop_items WHERE MPN='".$ArtNr."';", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results2)==0 )
				{
					$amount=-1;
				}
				else
				{
					$row2=mysqli_fetch_array($results2);
					//set inactive items to discontinued
					//available inactive items are OK
					if( $lager[$ArtNr]==0 and $row2["active"]==0 )
					{
						$amount=-1;
					}
					//set items with collateral to discontinued
					elseif( $row2["collateral"]>0 )
					{
						$amount=-1;
					}
					//set silencer to discontinued
					elseif( $row2["GART"]==3435 or $row2["GART"]==3436 or $row2["GART"]==3437 )
					{
						$amount=-1;
					}
					else
					{
						$id_item=$row2["id_item"];
						if( $lager[$ArtNr]<=$amount ) $amount=$lager[$ArtNr];
					}
				}

				if( $nr==5 ) $PO="PO# ".$row->Cell[0]->Data[0];
				if( $amount<1 )
				{
					$missing++;
					$style=' style="background-color:#ff0000;"';
				}
				else
				{
					$deliverable++;
					$style='';
				}
				echo '<tr'.$style.'>';
				if( $nr==4 ) $td="th"; else $td="td";
				//checkbox
				echo '	<'.$td.'>';
				if($nr>4)
				{
					echo '		<input'.$checked.' name="amount[]" style="width:20px;" type="value" value="'.$amount.'" />';
					echo '		<input name="ArtNr[]" type="hidden" value="'.$ArtNr.'" />';
					echo '		<input name="netto[]" type="hidden" value="'.$netto.'" />';
					echo '		<input name="price[]" type="hidden" value="'.$price.'" />';
					echo '		<input name="id_item[]" type="hidden" value="'.$id_item.'" />';
				}
				else echo 'Menge';
				echo '	</'.$td.'>';
				//QunatityOrdered
				echo '	<'.$td.'>'.$row->Cell[8]->Data[0].'</'.$td.'>';
				//Zentrallager
				if( $nr==4 ) echo '	<'.$td.'>Zentrallager</'.$td.'>';
				else echo '	<'.$td.'>'.$lager[$ArtNr].'</'.$td.'>';
				//Zentrallager
				for($j=0; $j<sizeof($rcbez); $j++)
				{
					if( $nr==4 ) echo '	<'.$td.'>'.$rcbez[$j].'</'.$td.'>';
					else echo '	<'.$td.'>'.$lagerrc[$rcbez[$j]][$ArtNr].'</'.$td.'>';
				}
				//Title
				echo '	<'.$td.'>'.$row->Cell[4]->Data[0].'</'.$td.'>';
				//ASIN
				echo '	<'.$td.'>'.$row->Cell[3]->Data[0].'</'.$td.'>';
				echo '</tr>';
			}
		}
		echo '</table>';
		echo '	<input type="hidden" name="ordernr" value="'.$PO.'" />';
		echo '	<input type="hidden" name="form_user_id" value="'.$_POST["form_user_id"].'" />';
		echo '	<input type="hidden" name="form_file" value="'.$tempfile.'" />';
		echo '	<br /><br /><input name="form_button" type="submit" value="Bestellung importieren" />';
		echo '</form>';
		echo $deliverable.' lieferbar. '.$missing.' Artikel nicht vom Zentrallager lieferbar.';
	}


	if ( isset($_FILES["file"]) and isset($_POST["form_warehouses"]) )
	{
		//create data file
		$fieldset=array();
		$fieldset["API"]="cms";
		$fieldset["Action"]="TempFileAdd";
		$fieldset["extension"]="xls";
		$responseXml = post(PATH."soa/", $fieldset);
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Temporärdatei anlegen fehlgeschlagen.</shortMsg>'."\n";
			echo '		<longMsg>Beim Anlegen einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$tempfile=(string)$response->Filename[0];
		move_uploaded_file($_FILES["file"]["tmp_name"], "temp/".$tempfile);
		
		//parse XML file
		$responseXml = file_get_contents("temp/".$tempfile);
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);

		//get order
		$order=array();
		for($i=5; $i<count($response->Worksheet[0]->Table[0]); $i++)
		{
			$row=$response->Worksheet[0]->Table[0]->Row[$i];
			$ArtNr=(string)$row->Cell[2]->Data[0];
			$order[$ArtNr]["ArtNr"]=$ArtNr;
			$order[$ArtNr]["Quantity"]=(integer)$row->Cell[8]->Data[0];
			//brake disc fix
			$results2=q("SELECT GART FROM shop_items WHERE MPN='".$ArtNr."';", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results2)>0 )
			{
				$row2=mysqli_fetch_array($results2);
				if( $row2["GART"]==82 and strpos($ArtNr, "/2") === false )
				{
					$order[$ArtNr]["Quantity"]=2*$order[$ArtNr]["Quantity"];
				}
			}
		}


		//cache shop_items
		$shopitems=array();
		$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$shopitems[$row["MPN"]]=$row;
		}


		//cache lager
		$lager=array();
		$lager[0]["RCNR"]=999;
		$lager[0]["BEZ"]="Zentrallager";
		$results=q("SELECT * FROM lager;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if( $shopitems[$row["ArtNr"]]["active"]>0 and $shopitems[$row["ArtNr"]]["collateral"]==0 )
			{
				//set silencer to N/A
				if( $shopitems[$row["ArtNr"]]["GART"]!=3435 and $shopitems[$row["ArtNr"]]["GART"]!=3436 and $shopitems[$row["ArtNr"]]["GART"]!=3437 )
				{
					$lager[0][$row["ArtNr"]]=$row["ISTBESTAND"]+$row["MOCOMBESTAND"]+$row["AMAZONBESTAND"];
				}
				else $lager[0][$row["ArtNr"]]=0;
			}
			else $lager[0][$row["ArtNr"]]=0;
		}
		
		$rcnr=array(101, 21, 19, 16, 18, 17, 15, 20, 22, 39, 40, 41, 44);
		
		//cache lagerrc
		$i=0;
		$rcbez=array();
		for($j=0; $j<sizeof($rcnr); $j++)
		{
			$results=q("SELECT * FROM lagerrc WHERE RCNR=".$rcnr[$j].";", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$i++;
			$lager[$i]["RCNR"]=$row["RCNR"];
			$lager[$i]["BEZ"]=$row["RCBEZ"];
			$results2=q("SELECT * FROM lagerrc WHERE RCNR=".$row["RCNR"].";", $dbshop, __FILE__, __LINE__);
			while( $row2=mysqli_fetch_array($results2) )
			{
				if( $shopitems[$row2["ARTNR"]]["active"]>0 and $shopitems[$row2["ARTNR"]]["collateral"]==0 )
				{
					//set silencer to N/A
					if( $shopitems[$row2["ARTNR"]]["GART"]!=3435 and $shopitems[$row2["ARTNR"]]["GART"]!=3436 and $shopitems[$row2["ARTNR"]]["GART"]!=3437 )
					{
						$lager[$i][$row2["ARTNR"]]=$row2["ISTBESTAND"];
					}
					else $lager[$i][$row2["ARTNR"]]=0;
				}
				else $lager[$i][$row2["ARTNR"]]=0;
			}
		}
		
		$orderable=array();
		for($j=0; $j<sizeof($lager); $j++)
		{
			$i=0;
			foreach($order as $item)
			{
				$ArtNr=$item["ArtNr"];
				if( isset($lager[$j][$ArtNr]) )
				{
					if( $lager[$j][$ArtNr]>=$item["Quantity"] )
					{
						$i++;
						$orderable[$lager[$j]["RCNR"]]["BEZ"]=$lager[$j]["BEZ"];
						$orderable[$lager[$j]["RCNR"]]["Items"][$i]["ArtNr"]=$ArtNr;
						$orderable[$lager[$j]["RCNR"]]["Items"][$i]["Quantity"]=$item["Quantity"];
						unset($order[$ArtNr]);
						$orderable[$lager[$j]["RCNR"]]["RCNR"]=$lager[$j]["RCNR"];
						$orderable[$lager[$j]["RCNR"]]["left"]=sizeof($order);
					}
				}
			}
		}
		
		//Lagerlisten speichern
		for($j=0; $j<sizeof($lager); $j++)
		{
			if( isset($orderable[$lager[$j]["RCNR"]]["Items"]) )
			{
				//create data file
				$fieldset=array();
				$fieldset["API"]="cms";
				$fieldset["Action"]="TempFileAdd";
				$fieldset["extension"]="htm";
				$responseXml = post(PATH."soa/", $fieldset);
				$use_errors = libxml_use_internal_errors(true);
				try
				{
					$response = new SimpleXMLElement($responseXml);
				}
				catch(Exception $e)
				{
					echo '<startUploadJobResponse>'."\n";
					echo '	<Ack>Failure</Ack>'."\n";
					echo '	<Error>'."\n";
					echo '		<Code>'.__LINE__.'</Code>'."\n";
					echo '		<shortMsg>Temporärdatei anlegen fehlgeschlagen.</shortMsg>'."\n";
					echo '		<longMsg>Beim Anlegen einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
					echo '	</Error>'."\n";
					echo '	<Response>'.$responseXml.'</Response>'."\n";
					echo '</startUploadJobResponse>'."\n";
					exit;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				$tempfile=(string)$response->Filename[0];
				$orderable[$lager[$j]["RCNR"]]["tempfile"]=$tempfile;
				$handle=fopen("temp/".$tempfile, "w");
				fwrite($handle, "<table>\n");
				fwrite($handle, "	<tr>\n");
				fwrite($handle, "		<th>Nr.</th>\n");
				fwrite($handle, "		<th>Artikelnummer</th>\n");
				fwrite($handle, "		<th>Menge</th>\n");
				fwrite($handle, "	</tr>\n");
				$nr=0;
				foreach($orderable[$lager[$j]["RCNR"]]["Items"] as $item)
				{
					$nr++;
					fwrite($handle, "<tr>\n");
					fwrite($handle, "<td>".$nr."</td>\n");
					fwrite($handle, "<td>".$item["ArtNr"]."</td>\n");
					fwrite($handle, "<td>".$item["Quantity"]."</td>\n");
					fwrite($handle, "</tr>\n");
				}
				fwrite($handle, "</table>\n");
				fclose($handle);
			}
		}
		
		//Lagerauswertung anzeigen
		echo '<table class="hover">';
		echo '	<tr>';
		echo '		<th>Nr.</th>';
		echo '		<th>Lagernr.</th>';
		echo '		<th>Lager</th>';
		echo '		<th>lieferbar</th>';
		echo '		<th>übrig</th>';
		echo '		<th>Optionen</th>';
		echo '	</tr>';
		$i=0;
		foreach($orderable as $lager)
		{
			$i++;
			echo '<tr>';
			echo '	<td>'.$i.'</td>';
			echo '	<td>'.$lager["RCNR"].'</td>';
			echo '	<td>'.$lager["BEZ"].'</td>';
			echo '	<td>'.sizeof($lager["Items"]).'</td>';
			echo '	<td>'.$lager["left"].'</td>';
			echo '	<td><a target="_blank" href="temp/'.$lager["tempfile"].'">Artikelliste</a></td>';
			echo '</tr>';
		}
		echo '</table>';
	}



	if( !isset($_FILES["file"]) and !isset($_POST["form_button"]) )
	{
		//PATH
		echo '<p>';
		echo '<a href="backend_index.php">Backend</a>';
		echo ' > <a href="backend_amazon_index.php">Amazon</a>';
		echo ' > Amazon-Bestellimport';
		echo '</p>';
	
		echo '<h1>Vendor Central Import</h1>';
		echo '<p>Diese Funktion kann eine Amazon-Vendor-Central-Bestellung ins Bestellmanagement importieren.</p>';
		echo '<br /><br />';
		echo '<form method="post" enctype="multipart/form-data">';
		echo '	<select name="form_user_id">';
		echo '		<option value="29291">Amazon.co.uk Ltd Boundary Way, HP2 7LF Hemel Hempstead, Hertfordshire</option>';
		echo '		<option value="29642">Amazon.co.uk Unit 1, DN4 5JS Doncaster, Balby Carr Bank</option>';
		echo '		<option value="29905">Amazon EU S.a.r.L. Plot 8, MK43 0ZA Ridgmont, Bedford, Marston Gate</option>';
		echo '		<option value="30077">Amazon.co.uk Limited Towers Business Park, WS15 1NZ Rugeley, Staffordshire, Powe</option>';
		echo '		<option value="30288">Amazon.co.uk Ltd Phase Two, PE2 9EN Peterborough, Cambridge, Kingston Park</option>';
		echo '		<option value="30326">AMAZON EU SARL , SA1 8QX CRYMLYN BURROWS, FFORDD AMAZON</option>';
		echo '		<option value="29825">Amazon.co.uk Amazon Way, KY11 8EZ Dunfermline, Fife, Dunfermline East Business P</option>';
		echo '	</select>';
		echo '<br /><br />';
		echo '	<input type="file" name="file" />';
		echo '	<input type="submit" name="form_upload" value="Bestellung hochladen" />';
		echo '	<input type="submit" name="form_warehouses" value="Lagerauswertung" />';
		echo '</form>';
	}

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>