<?php

/***********************************************************************************************************
SKRIPT ruft Preisvorschläge von Ebay ab
- Check, ob Preisvorschlag älter als 1 Stunde
- Check, ob der Kunde den Artikel (MPN) innerhalb der letzten 24h noch nicht gekauft hat
- Check, ob Auktion in EbayAuctions vorliegt -> wenn nicht Fehlermeldung, kein Gegenvorschlag
- Liegt PriceSuggestion vor?
	- Ja: Gegenvorschlag = Ebay-VK-Preis -10%
	- Nein: Gegenvorschlag = Gelber Preis -10% aber nur bis roter Preis

- Check, liegt Käufer Preisvorschlag innerhalb 10% unter Ebay VK-Preis
	- Ja: EbayRegel nicht aktiv für diesen Artikel -> Annahme Preisvorschlag, wenn Preisvorschlag >= Gegenvorschlag
	- Nein: - Check: Gegenvorschlag unter Ebay-VK?
				- Ja: Gegenvorschlag senden
				- Nein: Fehlermeldung

<?xml version="1.0" encoding="UTF-8"?>
<GetBestOffersResponse xmlns="urn:ebay:apis:eBLBaseComponents">
  <Timestamp>2013-05-30T14:20:33.418Z</Timestamp>
  <Ack>Success</Ack>
  <Version>823</Version>
  <Build>E823_INTL_API_16068688_R1</Build>
  <ItemBestOffersArray>
    <ItemBestOffers>
      <Role>Seller</Role>
      <BestOfferArray>
        <BestOffer>
          <BestOfferID>31249027</BestOfferID>
          <ExpirationTime>2013-05-30T18:46:37.000Z</ExpirationTime>
          <Buyer>
            <Email>stephan.1961@freenet.de</Email>
            <FeedbackScore>16</FeedbackScore>
            <RegistrationDate>2009-10-23T17:01:26.000Z</RegistrationDate>
            <UserID>1610stephan</UserID>
          </Buyer>
          <Price currencyID="EUR">158.09</Price>
          <Status>Pending</Status>
          <Quantity>1</Quantity>
          <BestOfferCodeType>SellerCounterOffer</BestOfferCodeType>
        </BestOffer>
        <BestOffer>
          <BestOfferID>31267536</BestOfferID>
          <ExpirationTime>2013-05-31T18:09:43.000Z</ExpirationTime>
          <Buyer>
            <Email>kawabubi@yahoo.de</Email>
            <FeedbackScore>180</FeedbackScore>
            <RegistrationDate>2002-07-22T19:24:24.000Z</RegistrationDate>
            <UserID>kawabubi</UserID>
          </Buyer>
          <Price currencyID="EUR">150.0</Price>
          <Status>Pending</Status>
          <Quantity>1</Quantity>
          <BestOfferCodeType>BuyerBestOffer</BestOfferCodeType>
        </BestOffer>
      </BestOfferArray>
      <Item>
        <BuyItNowPrice currencyID="EUR">175.66</BuyItNowPrice>
        <ItemID>110930437825</ItemID>
        <ListingDetails>
          <EndTime>2013-06-01T01:03:51.000Z</EndTime>
        </ListingDetails>
        <Location>Borkheide</Location>
        <Title>MAPCO Querlenker-Satz vorne  mit Befestigungsmaterial</Title>
        <ConditionID>1000</ConditionID>
        <ConditionDisplayName>Neu</ConditionDisplayName>
      </Item>
    </ItemBestOffers>
  </ItemBestOffersArray>
  <PageNumber>1</PageNumber>
  <PaginationResult>
    <TotalNumberOfPages>1</TotalNumberOfPages>
    <TotalNumberOfEntries>3</TotalNumberOfEntries>
  </PaginationResult>
</GetBestOffersResponse>

************************************************************************************************************/

	$PVCounter=0;
	$errormsg="";
	$_POST["account_id"]=1;
	$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "GetBestOffers", "id_account" => $_POST["account_id"], "PageNumber" => 1));
echo $responseXml;
exit;
	$xml = new SimpleXMLElement($responseXml);
	$resultPageNumber = $xml->PageNumber[0];
	//$resultTotalPages = $xml->PaginationResult[0]->TotalNumberOfPages[0];
	//$resultTotalNumberOfEntries = $xml->PaginationResult[0]->TotalNumberOfEntries[0];

	for($i=0; isset($xml->ItemBestOffersArray[0]->ItemBestOffers[$i]); $i++)
	{
		$bestOfferBuyItNow = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->Item[0]->BuyItNowPrice[0];
		$bestOfferItemID = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->Item[0]->ItemID[0];
		
		//GET SKU
		$res_SKU=q("SELECT SKU from ebay_auctions WHERE ItemID = ".$bestOfferItemID.";", $dbshop, __FILE__, __LINE__);
		//AUCTIONEN VORHANDEN
		if (mysqli_num_rows($res_SKU)>0)
		{
			$row_SKU=mysqli_fetch_array($res_SKU);
		
			$no_price_suggestion=false;
			$k=0;
			
			for($j=0; isset($xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->BestOfferID[0]); $j++)
			{
				//CHECK, ob eine Stunde seit Abgabe des Preisvorschlages abgelaufen ist
				$Expiration = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->ExpirationTime[0];
				$UserID = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Buyer[0]->UserID[0];
				$respondToOffer=false;
				if ((time()-strtotime($Expiration))<169200) // 169200 => 47h
				{
					$respondToOffer=true;
					
					//CHECK, OB KUNDEN DEN ARTIKEL IN DER ZWISCHENZEIT BEREITS GEKAUFT HAT (AP & MAPCO)
					$res_buyings=q("SELECT a.CreatedDateTimestamp FROM ebay_orders_items as a, ebay_orders as b WHERE b.BuyerUserID = '".$UserID."' and a.ItemSKU = '".$row_SKU["SKU"]."' ;",$dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_buyings)==0)
					{
						
					}
					else
					{
						while ($row_buyings=mysqli_fetch_array($res_buyings))
						{
							if((time()-$row_buyings["CreatedDateTimestamp"])<86400) 
							{
								$respondToOffer=false;
		//						echo "Kunde hat MPN schon gekauf (innerh. 24h)";
							}
						}
					}
					
					if ($respondToOffer)
					{
		//				echo "Preisvorschlag senden";
						$bestOfferID[$k] = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->BestOfferID[0];
						$bestOfferExpiration[$k] = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->ExpirationTime[0];
						$bestOfferUserID[$k] = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Buyer[0]->UserID[0];
						$bestOfferPrice[$k] = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Price[0];
						$bestOfferQty[$k] = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Quantity[0];
						$k++;
					}
				}
				else 
				{
		//			echo "KEIN PREISVORSCHLAG SENDEN (Gebotsabgabe noch keine Stunde her)";
				}
			}
		}
		//KEINE AUCTIONEN VORHANDEN
		else
		{
		//	echo "KEINE AUCTIONS VORHANDEN!!!!!!!!!!";
		}
	
		$new_price_offer=0;
	
		//GET ITEM_ID
		$res=q("SELECT * FROM ebay_auctions WHERE ItemID = ".$bestOfferItemID.";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)>0)
		{
			$auction=mysqli_fetch_array($res);
		//	echo $bestOfferItemID." ".$auction["shopitem_id"];
			//PRICE SUGGESTION???
			$res=q("SELECT * FROM shop_price_suggestions WHERE item_id = ".$auction["shopitem_id"]." AND (status = 1 OR status = 2 OR status = 4) order by lastmod desc LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res)>0)
			{
		//	echo " PS vorhanden - Discounted PS: ";
				$no_price_suggestion=false;
				$price_suggestion=mysqli_fetch_array($res);
				$discounted_price_sugestion=number_format(($price_suggestion["price"]*0.9), 2);
		//	echo " discounted BuyItNow: ";	
				$discounted_bestOfferBuyItNow=number_format(($bestOfferBuyItNow*0.9), 2);
				if ($discounted_bestOfferBuyItNow>=$discounted_price_sugestion)
				{
					$new_price_offer=$discounted_bestOfferBuyItNow;
				}
				else 
				{
					$new_price_offer=$discounted_price_sugestion;
				}
			}
			//NO PRICESUGGESTION
			else
			{
		//	echo " KEIN PS vorhanden";

				$no_price_suggestion=true;
				//Gelben Preis suchen
				$res=q("SELECT * FROM prpos where ARTNR = ".$auction["SKU"]." AND LST_NR = 5 ;", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res)>0)
				{
					$pricelists=mysqli_fetch_array($res);
		//			echo " yellow price: "; 
					$price_yellow=number_format($pricelists["POS_0_WERT"]*1.19, 2);
					
				}
				//Roten Preis suchen
				$res=q("SELECT * FROM prpos where ARTNR = ".$auction["SKU"]." AND LST_NR = 7 ;", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res)>0)
				{
		//			echo " red price: "; 
					$pricelists=mysqli_fetch_array($res);
					$price_red=number_format($pricelists["POS_0_WERT"]*1.19, 2);
				}
	
		//		echo " discounted yellow: ";
				$dicounted_yellow_price=number_format($price_yellow*0.9, 2);
				if ($dicounted_yellow_price<=$price_red)
				{
					$new_price_offer=$price_red;
				}
				else 
				{
					$new_price_offer=$dicounted_yellow_price;
				}
	
				$discounted_bestOfferBuyItNow=number_format($bestOfferBuyItNow*0.9, 2);
				if ($discounted_bestOfferBuyItNow>=$new_price_offer)
				{
					$new_price_offer=$discounted_bestOfferBuyItNow;
				}
			}
			
			//PREISVORSCHLAG senden
			for ($j=0; $j<sizeof($bestOfferID); $j++)
			{
		//		echo " NewPriceOffer ".$new_price_offer."<br>";
			
				//Check, ob neuer Preisvorschlag über Ebay VK liegt
				if ($new_price_offer>$bestOfferBuyItNow)
				{
					$text ="Gegenpreisvorschlag (".$new_price_offer." €) liegt über Ebay VK-Preis (".$bestOfferBuyItNow." €). \n";
					if ($no_price_suggestion) $text.="Zum Artikel liegt keine Preisrecherche vor \n"; else $text.="Zum Artikel liegen Preisrecherchen vor \n";
					$text.="Roter Preis (brutto): ".$price_red." € \n";
					$text.="Shop item_id: ".$auction["shopitem_id"]." \n";
					$text.="Mapco Nummer: ".$auction["SKU"]." \n";
					$text.="Ebay-Account: ".$_POST["account_id"]."\n";
					$text.="Ebay-Artikelnummer: ".$bestOfferItemID."\n\r";
					
					$errormsg.=$text."\n\r";
	
					mail("developer@mapco.de", "FEHLER bei Automatischem Preisvorschlag", $text);
				//	mail("jhabemann@mapco", "FEHLER bei Automatischem Preisvorschlag", $text);
				}
				else 
				//Gegenvorschlag senden / Preisvorschlag annehmen
				{
				
					if ($new_price_offer<$bestOfferPrice[$j])
					{
						//Preisvorschlag akzeptieren
			//			$responseXml2 = post(PATH."soa/", array("API" => "ebay", "Action" => "RespondBestOffers", "id_account" => $_POST["account_id"], "BestOfferID" => $bestOfferID[$j], "ItemID" => $bestOfferItemID, "BestOfferAction" => "Accept"));
						if (strpos($responseXml2,"Success")>0)
						{
							$PVCounter++;
						}
						else 
						{
							
							$text ="FEHLER bei Automatischem Preisvorschlag \n";
							$text.="EBAY-RESPONSE: \n";
							$text.=$responseXml2." \n";
							$text.="Ebay-Account: ".$_POST["account_id"]."\n";
							$text.="Ebay-Artikelnummer: ".$bestOfferItemID."\n\r";
							
							$errormsg.=$text."\n\r";
							
							mail("developer@mapco.de", "FEHLER bei Automatischem Preisvorschlag", $text);
						}
	
					}
					else
					{
						//Gegenvorschlag senden
						$responseXml2 = post(PATH."soa/", array("API" => "ebay", "Action" => "RespondBestOffers", "id_account" => $_POST["account_id"], "BestOfferID" => $bestOfferID[$j], "DiscountedPrice" => $new_price_offer, "DiscountedPriceQty" => $bestOfferQty[$j], "ItemID" => $bestOfferItemID, "BestOfferAction" => "Counter"));
						if (strpos($responseXml2,"Success")>0)
						{
							$PVCounter++;
						}
						else 
						{
							
							$text ="FEHLER bei Automatischem Preisvorschlag \n";
							$text.="EBAY-RESPONSE: \n";
							$text.=$responseXml2." \n";
							$text.="Ebay-Account: ".$_POST["account_id"]."\n";
							$text.="Ebay-Artikelnummer: ".$bestOfferItemID."\n\r";
							
							$errormsg.=$text."\n\r";
							
							mail("developer@mapco.de", "FEHLER bei Automatischem Preisvorschlag", $text);
						}
					}
					
				}
				
			}
			unset($bestOfferID);
			unset($bestOfferUserID);
			unset($bestOfferPrice);
			unset($bestOfferQty);
		}
		else 
		//FEHLER -> KEINE AUCTIONID gefunden
		{
			$text ="FEHLER bei Automatischem Preisvorschlag \n";
			$text.="Zur Ebay Artikelnummer: ".$bestOfferItemID." konnte kein Eintrag in der Tabelle Ebay_auctions gefunden werden. ACCOUNT_ID: ".$_POST["account_id"];
			
			$errormsg.=$text."\n\r";
			
			mail("developer@mapco.de", "FEHLER bei Automatischem Preisvorschlag - keine AuctionID",$text);

		}

	}

echo "Es wurden ".$PVCounter++." Preisvorschläge beantwortet (EbayAccount ".$_POST["account_id"].") \n";
if ($errormsg!="") echo "FEHLERMELDUNGEN: \n".$errormsg;


?>