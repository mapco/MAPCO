<?php
//	include("config.php");
//	include("templates/".TEMPLATE_BACKEND."/header.php");

	$PVCounter=0;
	
	$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "GetBestOffers", "id_account" => $_POST["account_id"], "PageNumber" => 1));

	$xml = new SimpleXMLElement($responseXml);
	$resultPageNumber = $xml->PageNumber[0];
	//$resultTotalPages = $xml->PaginationResult[0]->TotalNumberOfPages[0];
	//$resultTotalNumberOfEntries = $xml->PaginationResult[0]->TotalNumberOfEntries[0];
	
	for($i=0; isset($xml->ItemBestOffersArray[0]->ItemBestOffers[$i]); $i++)
	{
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
		if (mysql_num_rows($res)>0)
		{
			$auction=mysql_fetch_array($res);
			
			//PRICE SUGGESTION???
			$res=q("SELECT * FROM shop_price_suggestions WHERE item_id = ".$auction["shopitem_id"]." AND (status = 1 OR status = 2 OR status = 4);", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res)>0)
			{
				$price_suggestion=mysql_fetch_array($res);
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
				//Gelben Preis suchen
				$res=q("SELECT * FROM prpos where ARTNR = ".$auction["SKU"]." AND LST_NR = 5 ;", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($res)>0)
				{
					$pricelists=mysql_fetch_array($res);
					$price_yellow=number_format($pricelists["POS_0_WERT"]*1.19, 2);
				}
				//Roten Preis suchen
				$res=q("SELECT * FROM prpos where ARTNR = ".$auction["SKU"]." AND LST_NR = 7 ;", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($res)>0)
				{
					$pricelists=mysql_fetch_array($res);
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
				//echo "PV:".$bestOfferID[$j].'+'.$new_price_offer."<br /><br />";
				$responseXml2 = post(PATH."soa/", array("API" => "ebay", "Action" => "RespondBestOffers", "id_account" => $_POST["account_id"], "BestOfferID" => $bestOfferID[$j], "DiscountedPrice" => $new_price_offer, "DiscountedPriceQty" => $bestOfferQty[$j], "ItemID" => $bestOfferItemID));
				if (strpos($responseXml2,"Success")>0)
				{
					$PVCounter++;
				}
				else 
				{
					mail("developer@mapco.de", "FEHLER bei Automatischem Preisvorschlag", $responseXml2);
				}
			
			
			}
			unset($bestOfferID);
			unset($bestOfferUserID);
			unset($bestOfferPrice);
			unset($bestOfferQty);
		}
		else
		{
			//FEHLER -> KEINE AUCTIONID gefunden
			mail("developer@mapco.de", "FEHLER bei Automatischem Preisvorschlag", "Zur Ebay Artikelnummer: ".$bestOfferItemID." konnte kein Eintrag in der Tabelle Ebay_auctions gefunden werden. ACCOUNT_ID: ".$_POST["account_id"]);
		}

	}
//echo '<textarea name="test" rows="20" cols="80">'.$responseXml.'</textarea>';;


//	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>