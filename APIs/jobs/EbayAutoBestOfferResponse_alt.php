<?php

/***********************************************************************************************************
SKRIPT ruft Preisvorschläge von Ebay ab
- Check, ob Auktion in EbayAuctions vorliegt -> wenn nicht Fehlermeldung, kein Gegenvorschlag
- Liegt PriceSuggestion vor?
	- Ja: Gegenvorschlag = Ebay-VK-Preis -10%
	- Nein: Gegenvorschlag = Gelber Preis -10% aber nur bis roter Preis

- Check, liegt Käufer Preisvorschlag innerhalb 10% unter Ebay VK-Preis
	- Ja: EbayRegel nicht aktiv für diesen Artikel -> Annahme Preisvorschlag, wenn Preisvorschlag >= Gegenvorschlag
	- Nein: - Check: Gegenvorschlag unter Ebay-VK?
				- Ja: Gegenvorschlag senden
				- Nein: Fehlermeldung

************************************************************************************************************/
//	include("config.php");
//	include("templates/".TEMPLATE_BACKEND."/header.php");

	$PVCounter=0;
	$errormsg="";
	
	$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "GetBestOffers", "id_account" => $_POST["account_id"], "PageNumber" => 1));

	$xml = new SimpleXMLElement($responseXml);
	$resultPageNumber = $xml->PageNumber[0];
	//$resultTotalPages = $xml->PaginationResult[0]->TotalNumberOfPages[0];
	//$resultTotalNumberOfEntries = $xml->PaginationResult[0]->TotalNumberOfEntries[0];
	
	for($i=0; isset($xml->ItemBestOffersArray[0]->ItemBestOffers[$i]); $i++)
	{
		$no_price_suggestion=false;
		for($j=0; isset($xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->BestOfferID[0]); $j++)
		{
			$bestOfferID[$j] = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->BestOfferID[0];
			$bestOfferUserID[$j] = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Buyer[0]->UserID[0];
			$bestOfferPrice[$j] = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Price[0];
			$bestOfferQty[$j] = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->BestOfferArray[$j]->BestOffer[0]->Quantity[0];
		}
		$bestOfferBuyItNow = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->Item[0]->BuyItNowPrice[0];
		$bestOfferItemID = $xml->ItemBestOffersArray[0]->ItemBestOffers[$i]->Item[0]->ItemID[0];
	
		$new_price_offer=0;
	
		//GET ITEM_ID
		$res=q("SELECT * FROM ebay_auctions WHERE ItemID = ".$bestOfferItemID.";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)>0)
		{
			$auction=mysqli_fetch_array($res);
		
			//PRICE SUGGESTION???
			$res=q("SELECT * FROM shop_price_suggestions WHERE item_id = ".$auction["shopitem_id"]." AND (status = 1 OR status = 2 OR status = 4) order by lastmod desc LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res)>0)
			{
				$no_price_suggestion=false;
				$price_suggestion=mysqli_fetch_array($res);
				$discounted_price_sugestion=number_format(($price_suggestion["price"]*0.9), 2);
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
				$no_price_suggestion=true;
				//Gelben Preis suchen
				$res=q("SELECT * FROM prpos where ARTNR = ".$auction["SKU"]." AND LST_NR = 5 ;", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res)>0)
				{
					$pricelists=mysqli_fetch_array($res);
					$price_yellow=number_format($pricelists["POS_0_WERT"]*1.19, 2);
				}
				//Roten Preis suchen
				$res=q("SELECT * FROM prpos where ARTNR = ".$auction["SKU"]." AND LST_NR = 7 ;", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res)>0)
				{
					$pricelists=mysqli_fetch_array($res);
					$price_red=number_format($pricelists["POS_0_WERT"]*1.19, 2);
				}
	
				
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
						$responseXml2 = post(PATH."soa/", array("API" => "ebay", "Action" => "RespondBestOffers", "id_account" => $_POST["account_id"], "BestOfferID" => $bestOfferID[$j], "ItemID" => $bestOfferItemID, "BestOfferAction" => "Accept"));
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
//echo '<textarea name="test" rows="20" cols="80">'.$responseXml.'</textarea>';;

echo "Es wurden ".$PVCounter++." Preisvorschläge beantwortet (EbayAccount ".$_POST["account_id"].") \n";
if ($errormsg!="") echo "FEHLERMELDUNGEN: \n".$errormsg;

//	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>