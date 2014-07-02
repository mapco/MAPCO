<?php

/***********************************************************************************************************
SKRIPT ruft Preisvorschläge von Ebay (alle aktiven Accounts) ab
- ist Auction in der Tabelle ebay_auctions vorhanden (wegen SKU/MPN)?
	Ja: weiter
	Nein: Abbruch
- ist ein Preis im System hinterlegt?
	Ja: weiter
	Nein: Abbruch
- ist Preisvorschlag älter als 6 Stunden
	Ja: weiter
	Nein: Abbruch
- ist Ebay BuyItNow Preis gleich Systempreis
	Ja: weiter
	Nein: Preisvorschlag ablehnen
- Hat der Bieter innerhalb der letzten 48h denselben Artikel bereits gekauft (Ebay alle Plattformen)?
	Ja: Preisvorschlag ablehnen
	Nein: weiter
- ist Preisvorschlag kleiner / gleich BuyItNow?
	Ja: weiter
	Nein: Preisvorschlag ablehnen
- ist Preisvorschlag kleiner 70% von BuyItNow?
	Ja: Preisvorschlag ablehnen
	Nein: weiter
- ist Preisvorschlag Innerhalb 10% unter BuyItNow?
	Ja: Preisvorschlag akzeptieren
	Nein: Gegenvorschlag
	

	
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

	$ust = (UST/100) +1;

	//GET CURRENCIES
	$res_currencies=q("SELECT * FROM shop_currencies;", $dbshop, __FILE__, __LINE__);
	while ($row_currencies=mysqli_fetch_array($res_currencies))
	{
		$row_currencies["currency_code"];
		$exchangerate_to_EUR[$row_currencies["currency_code"]]=$row_currencies["exchange_rate_to_EUR"];
	}



	$PVCounter=0;
	$errormsg="";
	
	//GET ACTIVE EBAY-ACCOUNTS
	$res_account=q("SELECT * FROM ebay_accounts WHERE active = 1 AND NOT id_account = 8;", $dbshop, __FILE__, __LINE__);
	while ($row_account=mysqli_fetch_array($res_account))
	{
		$account=$row_account;
		
		
		//JOBAUSGABE
		echo 'Ziehe Preisvorschläge von: '.$account["title"]."\n";
		
		//Ziehe Preisvorschläge von den Ebay-Accounts
		$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "GetBestOffers", "id_account" => $account["id_account"], "PageNumber" => 1));
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$xml = new SimpleXMLElement($responseXml);
			$OK=true;
		}
		catch(Exception $e)
		{
			echo 'Fehler beim Abrufen der Preisvorschläge von eBay (Account :'.$account["title"].'). Zurückgeliefertes XML nicht valide'."\n";
			$OK=false;
			show_error(9752, 6, __FILE__, __LINE__, "Ebay-Account: ".$account["title"]." RESPONSE:".$responseXml); 
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($OK && $xml->Ack[0]!="Success")
		{
			echo 'Fehler beim Abrufen der Preisvorschläge von eBay (Account :'.$account["title"].'). FEHLER:'.$responseXml."\n";
			$OK=false;
			show_error(9753, 6, __FILE__, __LINE__, "Ebay-Account: ".$account["title"]." RESPONSE:".$responseXml); 
		}
		
		if ($OK)
		{
//Durchlauf aller Artikel, die einen Preisvorschlag haben

		echo 'Anzahl der vorhandenen Artikel mit Preisvorschlägen: '.$xml->PaginationResult[0]->TotalNumberOfEntries[0]."\n";


			for($i=0; isset($xml->ItemBestOffersArray[0]->ItemBestOffers[$i]); $i++)
			{
				$respondToOffer=true;
			
				$currency =(string)$xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->Item[0]->BuyItNowPrice[0]->attributes()->currencyID;


	// ist Auction in der Tabelle ebay_auctions vorhanden (wegen SKU/MPN)?
	echo "ItemID :";	
	echo $bestOfferItemID = (int)$xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->Item[0]->ItemID[0];
				//GET SKU
				$res_SKU=q("SELECT SKU, account_id from ebay_auctions WHERE ItemID = ".$bestOfferItemID.";", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_SKU)==0)
				{
					$respondToOffer=false;
					//JobResponse
					echo 'Keine Auction in Tabelle ebay_auctions für Ebay-Artikelnummer '.$bestOfferItemID."\n";
				}
				else
				{
					$row_SKU=mysqli_fetch_assoc($res_SKU);
					$shippingCosts=floatval($row_SKU["ShippingServiceCost"]);
					$SKU=$row_SKU["SKU"];
					$account_id = $row_SKU["account_id"];
					//echo $row_SKU["account_id"];
					//GET NATIONALSHIPPINGFEE
					$res_NSF=q("SELECT * FROM ebay_accounts WHERE id_account = ".$row_SKU["account_id"].";", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_NSF)>0)
					{
						$row_NSF = mysqli_fetch_assoc($res_NSF);
						$NationalShippingFee = $row_NSF["NationalShippingFee"];
					}
					else
					{
						$respondToOffer=false;
						echo 'Keinen EbayAccount in Tabelle ebay_accounts zur Account-ID '.$row_SKU["account_id"]." gefunden\n";
					}
				}

				
	// ist ein Preis im System hinterlegt?
				//GET SHOP_ID of Ebay-Account
				if ($respondToOffer)
				{
					$res_shop=q("SELECT * FROM shop_shops WHERE shop_type = 2 AND account_id = ".$account["id_account"].";", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_shop)==0)
					{
						$respondToOffer=false;
						//JobResponse
						echo 'Keinen Shop in Tabelle shop_shops mit Ebay-AccountID: '.$account["id_account"].' für Ebay-Artikelnummer '.$bestOfferItemID.' gefunden.'."\n";
					}
					else
					{
						$row_shop=mysqli_fetch_array($res_shop);
						$shop=$row_shop;
					}
				}
	
	
				//Ziehe Preis für Artikel
				if ($respondToOffer)
				{
					$price=0;
					$priceEUR=0;
					
					//GET PRICELIST
					$default_priceList=0;
					$res_account = q("SELECT * FROM ebay_accounts_sites WHERE account_id = ".$account_id." LIMIT 1", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_account)==0)
					{
						//show_error();
						echo "Kein Account zur account_id gefunden ";
						break;
					}
					else
					{
						$account_site = mysqli_fetch_assoc($res_account);
						$default_priceList = $account_site["pricelist"];
					}
					
					$responseXml2 = post(PATH."soa2/", array("API" => "shop", "APIRequest" => "PriceGet", "shop_id" => $shop["id_shop"], "MPN" => $SKU, "default_PriceList" =>$default_priceList ));
	
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$xml2 = new SimpleXMLElement($responseXml2);
					}
					catch(Exception $e)
					{
						//JobResponse
						echo 'Fehler beim Abrufen des Preises für Artikel: '.$SKU.', ShopID: '.$shop["id_shop"].' XML Invalide.'."\n";
						$respondToOffer=false;
						show_error(9752, 6, __FILE__, __LINE__, "API: PriceGet, APIRequest: PriceGet, RESPONSE:".$responseXml); 

					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
					
					if ($respondToOffer)
					{
						if ($xml2->Ack[0]!="Success")
						{
							//JobResponse
							echo 'Fehler beim Abrufen des Preises für Artikel: '.$SKU.', ShopID: '.$shop["id_shop"]."\n";
							$respondToOffer=false;
							show_error(9754, 6, __FILE__, __LINE__, 'Artikel: '.$SKU.', ShopID: '.$shop["id_shop"]); 

						}
						else
						{
							//$priceEUR=floatval($xml2->priceEUR[0]);
							$price=floatval($xml2->price_gross[0]);
						}
					}
				}

				if ($respondToOffer)
				{



//Durchlauf aller Preisvorschläge eines Artikels
					for($j=0; isset($xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->BestOfferID[0]); $j++)
					{
						$responsetype="";
						$responsemessage="";

						$bestOfferID = (int)$xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->BestOfferID[0];
						$bestOfferExpirationTime = strtotime((string)$xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->ExpirationTime[0]);
						$UserID = (string)$xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Buyer[0]->UserID[0];
						$bestOfferPrice = number_format((float)$xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Price[0], 2, ".", "");


	//ist Preisvorschlag älter als 6 Stunden
	
						if ($bestOfferExpirationTime > ( time()+42*3600 )) //Laufzeit Preisvorschlag 48h
						{
							//JobResponse
							echo 'Preisvorschlag (Preisvorschlag ID: '.$bestOfferID.') noch keine 6 Stunden alt. Artikel '.$SKU.', ShopID: '.$shop["id_shop"].' , Käufer: '.$UserID."\n";
							$respondToOffer=false;
						}
						else
						{
							$respondToOffer=true;
						}

						
						if ($respondToOffer)
						{
	//ist Ebay BuyItNow Preis gleich Systempreis
							$bestOfferBuyItNowForeignCurrency = floatval($xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->Item[0]->BuyItNowPrice[0]);
							
							//$ebayPriceWithoutShippingCosts=$bestOfferBuyItNowForeignCurrency-$shippingCosts;
								
						//	if (($price*floatval($exchangerate_to_EUR[$currency]))!=$ebayPriceWithoutShippingCosts)
							if ( (($price*floatval($exchangerate_to_EUR[$currency]))+$NationalShippingFee) != ($bestOfferBuyItNowForeignCurrency+$shippingCosts) )
							{
								//JobResponse
								echo 'Ebay-Preis und hinterlegter Preis weichen voneinander ab: '.$SKU.', ShopID: '.$shop["id_shop"].' hinterlegter Preis: '.($price*floatval($exchangerate_to_EUR[$currency])).' '.$currency.' Ebaypreis: '.$bestOfferBuyItNowForeignCurrency.' '.$currency.' ItemID: '.$bestOfferItemID."\n";
								$responsetype="Decline";
								$responsemessage="Ebay-Preis und hinterlegter Preis weichen voneinander ab.";
								
								$new_price_offer=0;

							}
							

	// Hat der Bieter innerhalb der letzten 48h denselben Artikel bereits gekauft (Ebay alle Plattformen)?
							if ($responsetype=="")
							{
								//Ziehe Verkäufe
								$res_buyings=q("SELECT a.CreatedDateTimestamp FROM ebay_orders_items as a, ebay_orders as b WHERE b.BuyerUserID = '".$UserID."' and a.ItemSKU = '".$SKU."' ;",$dbshop, __FILE__, __LINE__);
								while ($row_buyings=mysqli_fetch_array($res_buyings))
								{
									if ( ($row_buyings["CreatedDateTimestamp"]+48*3600) > time() )
									{
										echo 'Bieter hat Artikel bereits gekauft (innerhalb der letzten 48h). Artikel: '.$SKU.', ShopID: '.$shop["id_shop"]."\n";
										$responsetype="Decline";
										$responsemessage="Bieter hat Artikel bereits gekauft (innerhalb der letzten 48h).";
										
										$new_price_offer=0;
									}
									
								}
							}


	// ist Preisvorschlag kleiner / gleich BuyItNow?
							if ($responsetype=="")
							{
								if ($bestOfferBuyItNowForeignCurrency<$bestOfferPrice)
								{
									echo 'Preisvorschlag liegt über Verkaufspreis. Artikel: '.$SKU.', ShopID: '.$shop["id_shop"].' , Käufer: '.$UserID."\n";
									$responsetype="Decline";
									$responsemessage="Preisvorschlag liegt über Verkaufspreis.";
									
									$new_price_offer=0;
								}
							}
							


	// ist Preisvorschlag kleiner 70% von BuyItNow?
							if ($responsetype=="")
							{
								if ($bestOfferPrice<$bestOfferBuyItNowForeignCurrency*0.7)
								{
									echo 'Preisvorschlag unter 70% des Verkaufspreises. Artikel: '.$SKU.', ShopID: '.$shop["id_shop"].' , Käufer: '.$UserID."\n";
									$responsetype="Decline";
									$responsemessage="Preisvorschlag unter 70% des Verkaufspreises.";
									
									$new_price_offer=0;
								}
							}


	// ist Preisvorschlag Innerhalb 10% unter BuyItNow?
							if ($responsetype=="")
							{
								if ($bestOfferPrice>=$bestOfferBuyItNowForeignCurrency*0.9)
								{
									echo 'Preisvorschlag akzeptieren. Artikel: '.$SKU.', ShopID: '.$shop["id_shop"].' , Käufer: '.$UserID."\n";
									$responsetype="Accept";
									$responsemessage="Preisvorschlag OK.";
									
									$new_price_offer=$bestOfferPrice;
								}
								else
								{
									echo 'Gegenvorschlag. Artikel: '.$SKU.', ShopID: '.$shop["id_shop"].' , Käufer: '.$UserID."\n";
									$responsetype="Counter";
									$responsemessage="Preisvorschlag OK.";
									
									$new_price_offer = round($bestOfferBuyItNowForeignCurrency*0.9, 2);
								}
							}



							//SENDE OfferResponse zu Ebay
							switch ($responsetype)
							{
								case "Accept":
									$responseXml3 = post(PATH."soa/", array("API" => "ebay", "Action" => "RespondBestOffers", "id_account" => $account["id_account"], "BestOfferID" => $bestOfferID, "ItemID" => $bestOfferItemID, "BestOfferAction" => "Accept"));
									$new_price_offer=0;
									break;
								
								case "Counter":
									$bestOfferQty = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Quantity[0];
									$new_price_offer = round($bestOfferBuyItNowForeignCurrency*0.9, 2);
									$responseXml3 = post(PATH."soa/", array("API" => "ebay", "Action" => "RespondBestOffers", "id_account" => $account["id_account"], "BestOfferID" => $bestOfferID, "DiscountedPrice" => $new_price_offer, "DiscountedPriceQty" => $bestOfferQty, "ItemID" => $bestOfferItemID, "BestOfferAction" => "Counter"));
									break;
									
								case "Decline":
									$responseXml3 = post(PATH."soa/", array("API" => "ebay", "Action" => "RespondBestOffers", "id_account" => $account["id_account"], "BestOfferID" => $bestOfferID, "ItemID" => $bestOfferItemID, "BestOfferAction" => "Decline", "message" => ""));							
									$new_price_offer=0;
									break;
							}
							$error=false;
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$xml3 = new SimpleXMLElement($responseXml3);
							}
							catch(Exception $e)
							{
								//JobResponse
								echo 'Fehler beim Senden der Preisvorschlagsantwort: '.$SKU.', ShopID: '.$shop["id_shop"].' EbayAntwort XML Invalide.'."\n";
								$error=true;
								show_error(9752, 6, __FILE__, __LINE__, "API: ebay, APIRequest: RespondBestOffers, RESPONSE:".$responseXml3); 

							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);
							if (!$error && $xml3->Ack[0]=="Success")
							{
				
								//Preisvorschlag protokollieren
								q("INSERT INTO ebay_bestoffers (
									BestOfferID,
									ItemID,
									SKU,
									BuyerID,
									EbayAccountID,
									Price,
									BuyItNow,
									BestOfferPrice,
									ResponseType,
									ResponsePrice,
									Reason,
									ResponseTimestamp,
									ExpirationTimestamp
								) VALUES (
									".$bestOfferID.",
									".$bestOfferItemID.",
									'".mysqli_real_escape_string($dbshop, $SKU)."',
									'".mysqli_real_escape_string($dbshop, $UserID)."',
									".$account["id_account"].",
									".$price.",
									".$bestOfferBuyItNowForeignCurrency.",
									".$bestOfferPrice.",
									'".mysqli_real_escape_string($dbshop, $responsetype)."',
									".$new_price_offer.",
									'".mysqli_real_escape_string($dbshop, $responsemessage)."',
									".time().",
									".$bestOfferExpirationTime."
								);", $dbshop, __FILE__, __LINE__);
									
								
							}
							else
							{
								echo 'Fehler beim Senden der Preisvorschlagsantwort: '.$SKU.', ShopID: '.$shop["id_shop"]."\n";
								show_error(9755, 6, __FILE__, __LINE__, 'Artikel: '.$SKU.', ShopID: '.$shop["id_shop"]);
							}
							
						} // IF respondToOffer
						
					} // FOR $j

					
				}
			} // FOR $i
		}

	} // WHILE ROW_ACCOUNT
	


?>