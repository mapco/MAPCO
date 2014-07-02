<?php

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	

$response="";

	if (isset($_POST["get_EbayData"]))
	{
		if ($_POST["id_account"]==0) 
		{
			echo "<p><b>Es muss ein Ebay-Account ausgewählt werden!</b></p>";
		}
		else
		{
			$from=time()-(3600*$_POST["past_hours"]);
			$to=time();
			
			$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
			$account=mysqli_fetch_array($results);
			
			$requestPage = 0;
			$resultHasMoreOrders = true;
			
			$i=0;
				
			while ($resultHasMoreOrders)
			{
				$requestPage++;
			
				$requestXmlBody  = '<?xml version="1.0" encoding="utf-8"?>';
				$requestXmlBody .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
				$requestXmlBody .= '  <RequesterCredentials>';
				$requestXmlBody .= '    <eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
				$requestXmlBody .= '  </RequesterCredentials>';
				$requestXmlBody .= '  <CreateTimeFrom>'.date('Y-m-d\TH:i:s.000\Z', $from).'</CreateTimeFrom>';
				$requestXmlBody .= '  <CreateTimeTo>'.date('Y-m-d\TH:i:s.000\Z', $to).'</CreateTimeTo>';
			//	$requestXmlBody .= '  <NumberOfDays>7</NumberOfDays>';
				$requestXmlBody .= '  <OrderRole>Seller</OrderRole>';
				$requestXmlBody .= '  <Pagination>';
				$requestXmlBody .= '	<EntriesPerPage>100</EntriesPerPage>';
				$requestXmlBody .= '	<PageNumber>'.$requestPage.'</PageNumber>';
				$requestXmlBody .= '  </Pagination>';
				$requestXmlBody .= '</GetOrdersRequest>';
				
				//submit auction
				$response = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetOrders", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

				$xml = new SimpleXMLElement($response);
				$resultPageNumber = $xml->PageNumber[0];
				$resultPageOrderCount = $xml->ReturnedOrderCountActual[0];
				$resultTotalNumberOfEntries = $xml->PaginationResult[0]->TotalNumberOfEntries[0];
			
				if ($xml->HasMoreOrders[0]=="true")	{$resultHasMoreOrders = true;} else {$resultHasMoreOrders = false;}
				
				
				//for ($i=0; $i<$resultPageOrderCount; $i++) 
				while(isset($xml->OrderArray[0]->Order[$i]))
				{
			
					$Order[$i]["OrderStatus"]=$xml->OrderArray[0]->Order[$i]->OrderStatus[0];
					$Order[$i]["Verkaufsprotokollnummer"]=$xml->OrderArray[0]->Order[$i]->ShippingDetails[0]->SellingManagerSalesRecordNumber[0];
					$Order[$i]["Mitgliedsname"]=$xml->OrderArray[0]->Order[$i]->BuyerUserID[0];
					$Order[$i]["Vollständiger Name des Käufers"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Name[0];
					$Order[$i]["Käuferadresse 1"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Street1[0];
					$Order[$i]["Käuferadresse 2"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Street2[0];
					$Order[$i]["Ort des Käufers"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->CityName[0];
					$Order[$i]["Staat des Käufers"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->StateOrProvince[0];
					$Order[$i]["Postleitzahl des Käufers"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->PostalCode[0];
					$Order[$i]["Land des Käufers"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->CountryName[0];
					$Order[$i]["Bestellnummer"]=$xml->OrderArray[0]->Order[$i]->OrderID[0];
					$Order[$i]["Verpackung und Versand"]="EUR ".number_format($xml->OrderArray[0]->Order[$i]->ShippingServiceSelected[0]->ShippingServiceCost[0], 2,",",".");
					$Order[$i]["Versicherung"]="EUR 0,00";
					$Order[$i]["Verkaufspreis"]="EUR ".number_format($xml->OrderArray[0]->Order[$i]->Subtotal[0], 2,",",".");
					$Order[$i]["Gesamtpreis"]="EUR ".number_format($xml->OrderArray[0]->Order[$i]->Total[0], 2,",",".");
					$zahlungsmethode=$xml->OrderArray[0]->Order[$i]->CheckoutStatus[0]->PaymentMethod[0];
					if ($zahlungsmethode=="PayPal")
					{
						$Order[$i]["Zahlungsmethode"]="PayPal";
					}
					else 
					{ 
						$Order[$i]["Zahlungsmethode"]="";
					}
					$Order[$i]["PayPal Transaktions-ID"]="";
					$Order[$i]["Rechnungsnummer"]="";
					$Order[$i]["Rechnungsdatum"]="";
					$Order[$i]["Verkaufsdatum"]=date("d.m.Y", strtotime($xml->OrderArray[0]->Order[$i]->CreatedTime[0]));
					if (isset($xml->OrderArray[0]->Order[$i]->CheckoutStatus[0]->LastModifiedTime[0]) && $xml->OrderArray[0]->Order[$i]->CheckoutStatus[0]->LastModifiedTime[0]!="")
					{
						$Order[$i]["Kaufabwicklungsdatum"]=date("d.m.Y", strtotime($xml->OrderArray[0]->Order[$i]->CheckoutStatus[0]->LastModifiedTime[0]));
					}
					else { $Order[$i]["Kaufabwicklungsdatum"]="";}
					if (isset($xml->OrderArray[0]->Order[$i]->PaidTime[0]) && $xml->OrderArray[0]->Order[$i]->PaidTime[0]!="")
					{
						$Order[$i]["Bezahldatum"]=date("d.m.Y", strtotime($xml->OrderArray[0]->Order[$i]->PaidTime[0]));
					}
					else { $Order[$i]["Bezahldatum"]="";}
					$Order[$i]["Verkaufsprotokollnummer"]=$xml->OrderArray[0]->Order[$i]->ShippingDetails[0]->SellingManagerSalesRecordNumber[0];
					$Order[$i]["Versandservice"]=$xml->OrderArray[0]->Order[$i]->ShippingServiceSelected[0]->ShippingService[0];
					$Order[$i]["Sendungsnummer"]="";
					if (isset($xml->OrderArray[0]->Order[$i]->ShippedTime[0]) && $xml->OrderArray[0]->Order[$i]->ShippedTime[0]!="")
					{
						$Order[$i]["Versanddatum"]=date("d.m.Y", strtotime($xml->OrderArray[0]->Order[$i]->ShippedTime[0]));
					}
					else { $Order[$i]["Versanddatum"]="";}


					$k=0;
					$ordercountItems=0;
					

					foreach($xml->OrderArray[0]->Order[$i]->TransactionArray[0]->Transaction as $transaction)
					{
						if (isset($transaction->Buyer[0]->Email[0]))
						{
							$buyerMail=$transaction->Buyer[0]->Email[0];
						}
						else 
						{
							$buyerMail="";
							
						}
						$Item[$i][$k]["Transaktions-ID"]=$transaction->TransactionID[0];
						$Item[$i][$k]["Artikelbezeichnung"]=$transaction->Item[0]->Title[0];
						$Item[$i][$k]["Stückzahl"]=$transaction->QuantityPurchased[0];
						$Item[$i][$k]["Verkaufspreis"]="EUR ".number_format($transaction->TransactionPrice[0], 2,",",".");
						$Item[$i][$k]["Inklusive Mehrwertsteuersatz"]="19%";
						$Item[$i][$k]["Abgegebene Bewertungen"]="";
						$Item[$i][$k]["Erhaltene Bewertungen"]="";
						$Item[$i][$k]["Notizzettel"]="";
						$Item[$i][$k]["Bestandseinheit"]=$transaction->Item[0]->SKU[0];
						$Item[$i][$k]["Private Notizen"]="";
						$Item[$i][$k]["Verkaufsprotokollnummer"]=$Order[$i]["Verkaufsprotokollnummer"];
						$Item[$i][$k]["Artikelnummer"]=$transaction->Item[0]->ItemID[0];
						$Item[$i][$k]["Produkt-ID-Typ"]="";
						$Item[$i][$k]["Produkt-ID-Wert"]="";
						$Item[$i][$k]["Produkt-ID-Wert 2"]="";
						$Item[$i][$k]["Variantendetails"]="";
						$Item[$i][$k]["Produktreferenznummer"]="";
						$Item[$i][$k]["Verwendungszweck"]="";
						$Item[$i][$k]["PayPal Transaktions-ID"]="";
						$Item[$i][$k]["Rechnungsnummer"]="";
						if ($transaction->CreatedDate[0]!="")
						{
							$Item[$i][$k]["Verkaufsdatum"]=date("d.m.Y", strtotime($transaction->CreatedDate[0]));
						}
						else {$Item[$i][$k]["Verkaufsdatum"];}

						$ordercountItems+=($transaction->QuantityPurchased[0]*1);

						$k++;
					}
					$Order[$i]["Stückzahl"]=$ordercountItems;
					$Order[$i]["E-Mail des Käufers"]=$buyerMail;
					
				$i++;
				}
		
			}
		
			//AUSGABE IN DATEI
			$header = array("Verkaufsprotokollnummer", "Mitgliedsname", "Vollständiger Name des Käufers", "E-Mail des Käufers", "Käuferadresse 1", "Käuferadresse 2", "Ort des Käufers", "Staat des Käufers", "Postleitzahl des Käufers", "Land des Käufers", "Bestellnummer", "Artikelnummer", "Transaktions-ID", "Artikelbezeichnung", "Stückzahl", "Verkaufspreis", "Inklusive Mehrwertsteuersatz", "Verpackung und Versand", "Versicherung", "Gesamtpreis", "Zahlungsmethode", "PayPal Transaktions-ID", "Rechnungsnummer", "Rechnungsdatum", "Verkaufsdatum", "Kaufabwicklungsdatum", "Bezahldatum", "Versanddatum", "Versandservice", "Abgegebene Bewertungen", "Erhaltene Bewertungen", "Notizzettel", "Bestandseinheit", "Private Notizen", "Produkt-ID-Typ", "Produkt-ID-Wert", "Produkt-ID-Wert 2", "Variantendetails", "Produktreferenznummer", "Verwendungszweck", "Sendungsnummer");
			
			//EINZELARTIKEL
			$Orderfields1 = array("Verkaufsprotokollnummer", "Mitgliedsname", "E-Mail des Käufers", "Vollständiger Name des Käufers", "Käuferadresse 1", "Käuferadresse 2", "Ort des Käufers", "Staat des Käufers", "Postleitzahl des Käufers", "Land des Käufers", "Bestellnummer", "Verpackung und Versand", "Versicherung", "Versanddatum", "Gesamtpreis", "Zahlungsmethode", "PayPal Transaktions-ID", "Rechnungsnummer", "Rechnungsdatum", "Verkaufsdatum", "Kaufabwicklungsdatum", "Bezahldatum",  "Versandservice", "Sendungsnummer");
			$Itemfields1 = array("Artikelnummer", "Transaktions-ID", "Artikelbezeichnung", "Stückzahl", "Verkaufspreis", "Inklusive Mehrwertsteuersatz", "Abgegebene Bewertungen", "Erhaltene Bewertungen", "Notizzettel", "Bestandseinheit", "Private Notizen", "Produkt-ID-Typ", "Produkt-ID-Wert", "Produkt-ID-Wert 2", "Variantendetails", "Produktreferenznummer", "Verwendungszweck");
			
			//ZUSAMMENGEFASSTE ORDER
			$Orderfields2 = array("Verkaufsprotokollnummer", "Mitgliedsname", "E-Mail des Käufers", "Vollständiger Name des Käufers", "Käuferadresse 1", "Käuferadresse 2", "Ort des Käufers", "Staat des Käufers", "Postleitzahl des Käufers", "Land des Käufers", "Stückzahl", "Verkaufspreis", "Bestellnummer", "Verpackung und Versand", "Versicherung", "Gesamtpreis", "Zahlungsmethode", "PayPal Transaktions-ID", "Rechnungsnummer", "Rechnungsdatum", "Verkaufsdatum", "Kaufabwicklungsdatum", "Bezahldatum", "Versandservice","Versanddatum", "Sendungsnummer");
			$Itemfields2 = array("Verkaufsprotokollnummer", "Artikelnummer", "Transaktions-ID", "Artikelbezeichnung", "Stückzahl", "Verkaufspreis", "Inklusive Mehrwertsteuersatz", "Verkaufsdatum",  "Abgegebene Bewertungen", "Erhaltene Bewertungen", "Notizzettel", "Bestandseinheit", "Private Notizen", "Produkt-ID-Typ", "Produkt-ID-Wert", "Produkt-ID-Wert 2", "Variantendetails", "Produktreferenznummer", "Verwendungszweck");
			
			for ($i=0; $i<sizeof($header); $i++)
			{
				$header2[$i]=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$header[$i]);
			}
			
			$j=0;
			for ($i=0; $i<sizeof($Order); $i++)
			{
				if ($Order[$i]["OrderStatus"]!="Cancelled" && $Order[$i]["OrderStatus"]!="Inactive" && $Order[$i]["Verkaufsprotokollnummer"]!="0") 
				{
					//ZUSAMMENGEFASSTE ORDER
					if (sizeof($Item[$i])>1)
					{
						//Zusammenfassungszeile
						for ($k=0; $k<sizeof($header); $k++)
						{
							if (in_array($header[$k], $Orderfields2))
							{
								$zeile[$j][$k]=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$Order[$i][$header[$k]]);
							}
							else $zeile[$j][$k]="";
						}
						$j++;
						//ORDERITEMS
						for ($l=0; $l<sizeof($Item[$i]); $l++)
						{
							for ($k=0; $k<sizeof($header); $k++)
							{
								if (in_array($header[$k], $Itemfields2))
								{
									$zeile[$j][$k]=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$Item[$i][$l][$header[$k]]);
								}
								else $zeile[$j][$k]="";
							}
							$j++;
						}
						
					}
					//EINZELORDER
					else
					{
						for ($k=0; $k<sizeof($header); $k++)
						{
							if (in_array($header[$k], $Orderfields1)) $zeile[$j][$k]=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$Order[$i][$header[$k]]);
							if (in_array($header[$k], $Itemfields1)) $zeile[$j][$k]=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$Item[$i][0][$header[$k]]);
						}
						$j++;
					}
				}
			} //FOR $i	
	
			//CREATE CSV	
			$filename='soa/Verkaeufe_'.$account["title"].date("dmY-Hi").'.csv';
			$fp = fopen($filename, 'w');
			fputcsv($fp, $header2, ';');
			for ($i=0; $i<sizeof($zeile); $i++)
			{
				fputcsv($fp,$zeile[$i], ';');
			}
			fclose($fp);
		
			$link='<a href="http://www.mapco.de/'.$filename.'">CSV herunterladen</a><br />';

		}
	}


	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">eBay</a>';
	echo ' > eBay Verkäufe: CSV-Generator';
	echo '</p>';
	echo '<h1>eBay Verkäufe: CSV-Generator</h1>';
	
	
	echo '<p><b>CSV-Datei erstellen für Ebay-Account</b>';
	echo '<form action="backend_ebay_csv_for_IDIMS.php" method="POST">';
	echo '<select name="id_account" size=1>';
	echo '	<option value=0>Bitte Ebay Account wählen</option>';
	$res=q("SELECT * FROM ebay_accounts;" ,$dbshop, __FILE, __LINE__ );
	while ($row=mysqli_fetch_array($res))
	{
		echo '<option value='.$row["id_account"].'>'.$row["title"].'</option>';
	}
	echo '</select>&nbsp;';
	echo 'Verkaufsdaten für die letzten <input type="text" name="past_hours" size="2" value="24" /> Stunden</p>';
	
	echo '<p><input type="submit" name="get_EbayData" value="CSV-Datei erstellen" /></p>';
	
	echo '</form>';

	if (isset($_POST["get_EbayData"]))
	{
		echo '<b>CSV-Download-Link: </b>'.$link;
	}
	//echo '<textarea rows="40" cols="100">'.$response.'</textarea>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
