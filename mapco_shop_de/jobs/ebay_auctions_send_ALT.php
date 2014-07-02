<?php
	include("../config.php");
	include("../functions/mapco_cutout.php");
	require_once('../modules/ebay/get-common/keys.php');
	require_once('../modules/ebay/get-common/eBaySession.php');


//	sleep(300);

	//read accounts
	$account=array();
	$results=q("SELECT * FROM ebay_accounts;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$account[$row["id_account"]]=$row;
	}

	if ( isset($_POST["id_auction"]) )
	{
		$results=q("SELECT * FROM ebay_auctions WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
	}
	elseif ( isset($_GET["id_auction"]) )
	{
		$results=q("SELECT * FROM ebay_auctions WHERE id_auction=".$_GET["id_auction"].";", $dbshop, __FILE__, __LINE__);
		if ( !(mysqli_num_rows($results)>0) )
		{
			echo 'Auktion nicht gefunden. Abbruch.';
			exit;
		}
	}
	elseif ( isset($_GET["id_item"]) and isset($_GET["id_account"]) )
	{
		$results=q("SELECT * FROM ebay_auctions WHERE shopitem_id=".$_GET["id_item"]." and account_id=".$_GET["id_account"]." and lastupdate<".(time()-3660)." order by lastupdate LIMIT 10;", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results)==0)
		{
			echo 'Alle Auktionen wurden in der letzten Stunde mindestens einmal aktualisiert!';
			exit;
		}
	}
	elseif ( isset($_GET["id_account"]) )
	{
		$results=q("SELECT * FROM ebay_auctions WHERE lastupdate<lastmod and account_id=".$_GET["id_account"]." order by lastupdate LIMIT 30;", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT * FROM ebay_auctions AS a, ebay_accounts_items AS b WHERE b.active=0 AND a.EbayID>0 AND b.item_id=a.shopitem_id order by a.lastupdate LIMIT 30;", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results)==0)
		{
			$results=q("SELECT * FROM ebay_auctions WHERE lastupdate<lastmod LIMIT 30;", $dbshop, __FILE__, __LINE__);
			echo 'Ebay Aktivierung<br />';
		}
		else 
		{
			echo 'Ebay Deaktivierung<br />';
/*
			while($row=mysqli_fetch_array($results))
			{
				echo $row["id_auction"].' ';
				echo $row["shopitem_id"].' ';
				echo $row["account_id"].' ';
				echo $row["EbayID"].' ';
				echo $row["Title"];
				echo '<br />';
			}
			exit;
*/
		}
	}
	while($row=mysqli_fetch_array($results))
	{
		$id_auction=$row["id_auction"];
		
		//ArtNr
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["shopitem_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$artnr=$row2["MPN"];
		$id_account=$row["account_id"];
		$id_item=$row["shopitem_id"];

		//Article
		$results2=q("SELECT article_id FROM shop_items WHERE id_item=".$row["shopitem_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$id_article=$row2["article_id"];
		
		//active?
		$results2=q("SELECT * FROM ebay_accounts_items WHERE item_id=".$row["shopitem_id"]." and account_id=".$id_account.";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$active=$row2["active"];
		$bestoffer=$row2["bestoffer"];
		$free_shipping=$row2["free_shipping"];


		if ($active==0 and $row["EbayID"]>0)
		{
			$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
			$requestXmlBody .= '<EndItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			if ( $account[$id_account]["production"]==0 )
			{
				$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>'.$account[$id_account]["token_sandbox"].'</eBayAuthToken></RequesterCredentials>';
			}
			else
			{
				$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>'.$account[$id_account]["token"].'</eBayAuthToken></RequesterCredentials>';
			}
			$requestXmlBody .= '	<ErrorLanguage>en_US</ErrorLanguage>';
//			$requestXmlBody .= '	<MessageID> string </MessageID>';
			$requestXmlBody .= '	<Version>'.$compatabilityLevel.'</Version>';
			$requestXmlBody .= '	<WarningLevel>High</WarningLevel>';
			$requestXmlBody .= '	<EndingReason>NotAvailable</EndingReason>';
			$requestXmlBody .= '	<ItemID>'.$row["EbayID"].'</ItemID>';
//			$requestXmlBody .= '	<SellerInventoryID> string </SellerInventoryID>';
			$requestXmlBody .= '</EndItemRequest>';
			$row["EbayID"]=0;
		}
		elseif( $active==0 and $row["EbayID"]==0 )
		{
			$requestXmlBody="";
		}
		else
		{
			$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
			if ($row["EbayID"]>0) $requestXmlBody .= '<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			else $requestXmlBody .= '<AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			if ( $account[$id_account]["production"]==0 )
			{
				$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>'.$account[$id_account]["token_sandbox"].'</eBayAuthToken></RequesterCredentials>';
			}
			else
			{
				$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>'.$account[$id_account]["token"].'</eBayAuthToken></RequesterCredentials>';
			}
			$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
			$requestXmlBody .= '<ErrorLanguage>en_US</ErrorLanguage>';
			
			$requestXmlBody .= '<Item>';
			$requestXmlBody .= '	<Title>'.substr($row["Title"], 0, 80).'</Title>';
			
			//Description
			$results3=q("SELECT * FROM cms_articles WHERE id_article=".$row2["article_id"].";", $dbweb, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$Description=$row3["article"];

			$Description=str_replace("<!-- TITLE -->", $row["Title"], $Description);
			if ( strpos($artnr, "HPS")>0 )
			{
				$Description=str_replace("<!-- HPS -->", '<img src="http://www.mapco.de/images/ebay_hps.jpg" />', $Description);
			}
			else
			{
				$Description=str_replace("<!-- HPS -->", '<img src="http://mapco.de/images/ebay_header_mapco.png" />', $Description);
			}

			$results5=q("SELECT * FROM shop_items_de WHERE id_item=".$id_item.";", $dbshop, __FILE__, __LINE__);
			$row5=mysqli_fetch_array($results5);
			$Desc=$row5["description"];
			$Desc=cutout($Desc, 'OEM START -->', '<!-- OEM STOP');
			$Desc=cutout($Desc, '<!-- Reverse Start -->', '<!-- Reverse Stop -->');
			$Desc=str_replace('<h1>', '<div class="box"><h2>', $Desc);
			$Desc=str_replace('</h1>', '</h2>', $Desc);
			$Desc=str_replace('</table>', '</table></div>', $Desc);
			$Desc=cutout($Desc, '<a href="', '">');
			$Desc=str_replace('</a>', '', $Desc);
//			$Desc=addslashes(stripslashes($Desc));

			$Description=str_replace("<!-- DESCRIPTION -->", $Desc, $Description);
			if ($row2["comment"]!="")
			{
				$Description=str_replace("<!-- COMMENT -->", '<div class="box"><p>'.$row2["comment"].'</p></div>', $Description);
			}
	
			$Description=str_replace("<div class=\"box\"><h2>Fahrzeugzuordnungen</h2>", "<div style=\"overflow:scroll; max-height:600px;\" class=\"box\"><h2>Fahrzeugzuordnungen</h2>", $Description);	
	//		$Description=str_replace("<h1>", "<h2>", $Description);
	//		$Description=str_replace("</h1>", "</h2>", $Description);
	//		$Description=str_replace("'", "&apos;", $Description);
			$Description=str_replace("&", "&amp;", $Description);
			$Description=str_replace("<", "&lt;", $Description);
			$Description=str_replace(">", "&gt;", $Description);
			$requestXmlBody .= '	<Description>'.$Description.'</Description>';
//			echo htmlentities($requestXmlBody);
//			exit;
	
			
			//Category		
			$requestXmlBody .= '	<PrimaryCategory>';
			$requestXmlBody .= '		<CategoryID>'.$row["CategoryID"].'</CategoryID>';
			$requestXmlBody .= '	</PrimaryCategory>';
			if ($row["StartPrice"]>10 and $bestoffer==1)
			{
				$requestXmlBody .= '	<BestOfferDetails>';
				$requestXmlBody .= '		<BestOfferEnabled>true</BestOfferEnabled>';
				$requestXmlBody .= '	</BestOfferDetails>';		
			}
			else
			{
				$requestXmlBody .= '	<BestOfferDetails>';
				$requestXmlBody .= '		<BestOfferEnabled>false</BestOfferEnabled>';
				$requestXmlBody .= '	</BestOfferDetails>';		
			}
			$requestXmlBody .= '	<ConditionID>1000</ConditionID>';
			$requestXmlBody .= '	<CategoryMappingAllowed>true</CategoryMappingAllowed>';
			$requestXmlBody .= '	<Country>DE</Country>';
			$requestXmlBody .= '	<Currency>EUR</Currency>';
			$requestXmlBody .= '	<DispatchTimeMax>'.$account[$id_account]["DispatchTimeMax"].'</DispatchTimeMax>';
			
			//ItemCompatibiliy
			$kritnr=array();
			$kritwert=array();
			$results5=q("SELECT KritNr, KritWert, SortNr FROM t_400 WHERE ArtNr='".$artnr."' ORDER BY LfdNr, SortNr;", $dbshop, __FILE__, __LINE__);
			while ($row5=mysqli_fetch_array($results5))
			{
				if ($row5["SortNr"]==1) $ktyp=$row5["KritWert"];
				else
				{
					$results2=q("SELECT BezNr, Typ, TabNr FROM t_050 WHERE KritNr='".$row5["KritNr"]."';", $dbshop, __FILE__, __LINE__);
					$row2=mysqli_fetch_array($results2);
					if ($row2["Typ"]=="K")
					{
						if (is_numeric($row5["KritWert"]))
						{
							$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl=".$row5["KritWert"].";", $dbshop, __FILE__, __LINE__);
						}
						else
						{
							$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl='".$row5["KritWert"]."';", $dbshop, __FILE__, __LINE__);
						}
						$row4=mysqli_fetch_array($results4);
						$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"]." AND SprachNr=1;", $dbshop, __FILE__, __LINE__);
						$row4=mysqli_fetch_array($results4);
						$kritw=$row4["Bez"];
//						echo $kritw.'<br />';
					}
					else 
					{
						if ($row5["KritNr"] == 20 or $row5["KritNr"] == 21)
						{
							$kritw=substr($row5["KritWert"], -2, 2).'/'.substr($row5["KritWert"], 0, 4);
						}
						else $kritw=$row5["KritWert"];
//						echo $kritw.'<br />';
					}
					
					$results2=q("SELECT Bez FROM t_030 WHERE BezNr=".$row2["BezNr"]." AND SprachNr=1;", $dbshop, __FILE__, __LINE__);
					$row2=mysqli_fetch_array($results2);
					$bez=$row2["Bez"];
	
					//Einheiten ausschneiden
					$unit="";
					$start=strrpos($bez, " [");
					if ($start>0)
					{
						$end=strrpos($bez, "]")-$start;
						$unit=substr($bez, $start+2, $end-2);
						$bez=substr($bez, 0, $start);
						$kritw.=$unit;
					}
@					$kritwert[$ktyp][sizeof($kritwert[$ktyp])]=iconv("windows-1252", "utf-8", utf8_decode($kritw));
@					$kritnr[$ktyp][sizeof($kritnr[$ktyp])]=iconv("windows-1252", "utf-8", utf8_decode($bez));
				}
			}
			
			$requestXmlBody .= '	<ItemCompatibilityList>';
			$requestXmlBody .= '		<ReplaceAll>true</ReplaceAll>';
			$requestXmlBody .= '		<Compatibility>';
			$requestXmlBody .= '			<NameValueList>';
			$requestXmlBody .= '				<Name>KType</Name>';
			$requestXmlBody .= '				<Value>'.$row["KTypNr"].'</Value>';
			$requestXmlBody .= '			</NameValueList>';
			if ( isset($kritnr[$row["KTypNr"]][0]) )
			{
				$CompatibilityNotes=$kritnr[$row["KTypNr"]][0].':'.$kritwert[$row["KTypNr"]][0];
				for($j=1; $j<sizeof($kritnr[$row["KTypNr"]]); $j++)
				{
					$CompatibilityNotes.=', '.$kritnr[$row["KTypNr"]][$j].':'.$kritwert[$row["KTypNr"]][$j];
				}
				$CompatibilityNotes=str_replace("&", "&amp;", $CompatibilityNotes);
				$CompatibilityNotes=str_replace("<", "&lt;", $CompatibilityNotes);
				$CompatibilityNotes=str_replace(">", "&gt;", $CompatibilityNotes);
				$requestXmlBody .= '			<CompatibilityNotes>'.$CompatibilityNotes.'</CompatibilityNotes>';
			}
			$requestXmlBody .= '		</Compatibility>';
	
			$requestXmlBody .= '	</ItemCompatibilityList>';
			
			
			//OE and OEM numbers
			$requestXmlBody .= '	<ItemSpecifics>';

			//OE numbers
			$oenr=array();
			$bez=array();
			$results2=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$artnr."' AND a.KHerNr=b.KherNr AND VGL=0;", $dbshop, __FILE__, __LINE__);
			$oem_numbers=mysqli_num_rows($results2);
			if ($oem_numbers>0)
			{
				while ($row2=mysqli_fetch_array($results2))
				{
					$results3=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row2["LBezNr"]." AND SprachNr=1;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$oenr[sizeof($oenr)]=$row2["OENr"];
					$bez[sizeof($bez)]=$row3["Bez"];
				}
	
				//sort by name
				array_multisort($bez, $oenr);
				
				//write to html
				$requestXmlBody .= '		<NameValueList>';
				$requestXmlBody .= '			<Name>Referenznummer(n) OE</Name>';
				for($i=0; $i<sizeof($bez); $i++)
				{
					$value=$bez[$i].' '.$oenr[$i];
					$value=str_replace("&", "&amp;", $value);
					$requestXmlBody .= '<Value>'.$value.'</Value>';
				}
				$requestXmlBody .= '			<Source>ItemSpecific</Source>';
				$requestXmlBody .= '		</NameValueList>';
			}

			//OEM numbers
			$oenr=array();
			$bez=array();
			$results2=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$artnr."' AND a.KHerNr=b.KherNr AND VGL=1;", $dbshop, __FILE__, __LINE__);
			$oem_numbers=mysqli_num_rows($results2);
			if ($oem_numbers>0)
			{
				while ($row2=mysqli_fetch_array($results2))
				{
					$results3=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row2["LBezNr"]." AND SprachNr=1;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$oenr[sizeof($oenr)]=$row2["OENr"];
					$bez[sizeof($bez)]=$row3["Bez"];
				}
	
				//sort by name
				array_multisort($bez, $oenr);
				
				//write to html
				$requestXmlBody .= '		<NameValueList>';
				$requestXmlBody .= '			<Name>Referenznummer(n) OEM</Name>';
				for($i=0; $i<sizeof($bez); $i++)
				{
					$value=$bez[$i].' '.$oenr[$i];
					$value=str_replace("&", "&amp;", $value);
					$requestXmlBody .= '<Value>'.$value.'</Value>';
				}
				$requestXmlBody .= '			<Source>ItemSpecific</Source>';
				$requestXmlBody .= '		</NameValueList>';
			}
			$requestXmlBody .= '	</ItemSpecifics>';
			
			//ItemID
			if ($row["EbayID"]>0) $requestXmlBody .= '<ItemID>'.$row["EbayID"].'</ItemID>';
	
			//best offer
			if ($row["StartPrice"]>10)
			{
				$requestXmlBody .= '	<ListingDetails>';
				$requestXmlBody .= '		<BestOfferAutoAcceptPrice currencyID="EUR">'.round($row["StartPrice"]*0.9, 2).'</BestOfferAutoAcceptPrice>';
				$requestXmlBody .= '		<MinimumBestOfferPrice currencyID="EUR">'.round($row["StartPrice"]*0.5, 2).'</MinimumBestOfferPrice>';
				$requestXmlBody .= '	</ListingDetails>';
			}
	
			//Listing
			if ( $account[$id_account]["production"]>0 )
			{
				$requestXmlBody .= '	<ListingDuration>'.$row["ListingDuration"].'</ListingDuration>';
			}
			else
			{
				$requestXmlBody .= '	<ListingDuration>Days_7</ListingDuration>';
			}
			$requestXmlBody .= '	<ListingType>FixedPriceItem</ListingType>';
	
			//PaymentMethods
			$PaymentMethods=explode(", ", $account[$id_account]["PaymentMethods"]);
			for($i=0; $i<sizeof($PaymentMethods); $i++)
			{
				if ( $account[$id_account]["production"]>0 or $PaymentMethods[$i]!="MoneyXferAcceptedInCheckout" )
				{
					$requestXmlBody .= '	<PaymentMethods>'.$PaymentMethods[$i].'</PaymentMethods>';
				}
			}
			$requestXmlBody .= '	<PayPalEmailAddress>'.$account[$id_account]["PayPalEmailAddress"].'</PayPalEmailAddress>';
	
			//PictureDetails
			$requestXmlBody .= '<PictureDetails>';
			$PicURL='';
			if($id_account!=2)
			{
				$results4=q("SELECT * FROM ebay_items_files WHERE active>0 AND item_id=".$row["shopitem_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$results4=q("SELECT * FROM cms_articles_images WHERE article_id=".$id_article." AND imageformat_id=10 ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($results4)==0)
				{
					$results4=q("SELECT * FROM shop_items_files WHERE active>0 AND item_id=".$row["shopitem_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
				}
			}
			while($row4=mysqli_fetch_array($results4))
			{
				$results2=q("SELECT * FROM cms_files WHERE id_file=".$row4["file_id"].";", $dbweb, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$PicURL='http://www.mapco.de/files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
				$requestXmlBody .= '<PictureURL>'.$PicURL.'</PictureURL>';
			}
			$requestXmlBody .= '</PictureDetails>';
	
			//PostalCode
			$requestXmlBody .= '	<PostalCode>'.$account[$id_account]["PostalCode"].'</PostalCode>';

			//Quantity
			$requestXmlBody .= '	<Quantity>10</Quantity>';
			
			//ReturnPolicy
			if ($row["EbayID"]==0)
			{
				$requestXmlBody .= '	<ReturnPolicy>';
				$requestXmlBody .= '		<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>';
				$requestXmlBody .= '	</ReturnPolicy>';
			}
	
			//SellerContactDetails
			$requestXmlBody .= '	<SellerContactDetails>';
			$requestXmlBody .= '		<CompanyName>MAPCO Autotechnik GmbH</CompanyName>';
			$requestXmlBody .= '		<County>DE</County>';
			$requestXmlBody .= '		<PhoneAreaOrCityCode>030</PhoneAreaOrCityCode>';
			$requestXmlBody .= '		<PhoneCountryCode>DE</PhoneCountryCode>';
			$requestXmlBody .= '		<PhoneLocalNumber>12345</PhoneLocalNumber>';
			$requestXmlBody .= '		<Street>Moosweg 1</Street>';
			$requestXmlBody .= '		<Street2>Gewerbegebiet</Street2>';
			$requestXmlBody .= '	</SellerContactDetails>		';
			
			//ShippingDetails
			$requestXmlBody .= '	<ShippingDetails>';
			if ( $account[$id_account]["production"]>0 )
			{
				$requestXmlBody .= '		<InternationalShippingDiscountProfileID>0|169197020|</InternationalShippingDiscountProfileID>';
				$requestXmlBody .= '		<ShippingDiscountProfileID>1|169197020|</ShippingDiscountProfileID>';
			}
			$requestXmlBody .= '		<ShippingType>Flat</ShippingType>';
			$requestXmlBody .= '		<ShippingServiceOptions>';
			$requestXmlBody .= '			<ShippingService>DE_DPDClassic</ShippingService>';
			if ($free_shipping!=0)
			{
				$requestXmlBody .= '			<ShippingServiceCost>0.00</ShippingServiceCost>';
			}
			else
			{
				$requestXmlBody .= '			<ShippingServiceCost>5.90</ShippingServiceCost>';
			}
			$requestXmlBody .= '			<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>';
			$requestXmlBody .= '			<ShippingServicePriority>1</ShippingServicePriority>';
			$requestXmlBody .= '		</ShippingServiceOptions>';
	
			if ($id_account==1)
			{
				$requestXmlBody .= '		<ShippingServiceOptions>';
				$requestXmlBody .= '			<ShippingService>DE_Pickup</ShippingService>';
				$requestXmlBody .= '			<ShippingServiceCost>0.00</ShippingServiceCost>';
				$requestXmlBody .= '			<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>';
				$requestXmlBody .= '			<ShippingServicePriority>2</ShippingServicePriority>';
				$requestXmlBody .= '		</ShippingServiceOptions>';
			}
	
			$requestXmlBody .= '		<InternationalShippingServiceOption>';
			$requestXmlBody .= '			<ShippingService>DE_PaketInternational</ShippingService>';
			$requestXmlBody .= '			<ShippingServiceCost>8.96</ShippingServiceCost>';
			$requestXmlBody .= '			<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>';
			$requestXmlBody .= '			<ShippingServicePriority>1</ShippingServicePriority>';
			$requestXmlBody .= '			<ShipToLocation>AT</ShipToLocation>';
			$requestXmlBody .= '		</InternationalShippingServiceOption>';
	
			$requestXmlBody .= '		<PaymentInstructions>Inselzuschlag von 10,15€ wird erhoben für PLZ: 18565, 25845-25849, 25859, 25863, 25869, 25929-25955, 25961-25999, 26465-26486, 26548, 26571-26579, 26757, 27498-27499, 83209, 83256. Bitte beachten Sie dies bei Ihrer Zahlung. Keine Lieferung an Postfächer oder Packstationen.</PaymentInstructions>';
			$requestXmlBody .= '	</ShippingDetails>';
			
			//Site
			if ( $account[$id_account]["production"]>0 )
			{
				$requestXmlBody .= '	<Site>US</Site>';
			}
			else
			{
				$requestXmlBody .= '	<Site>US</Site>';
			}
			
			//SKU
			$requestXmlBody .= '	<SKU>'.$artnr.'</SKU>';
			
	
			//StartPrice
			$requestXmlBody .= '	<StartPrice>'.$row["StartPrice"].'</StartPrice>';
	
			if ( $account[$id_account]["production"]>0 )
			{
				$requestXmlBody .= '	<Storefront>';
				$requestXmlBody .= '		<StoreCategory2ID>'.$row["StoreCategory2ID"].'</StoreCategory2ID>';
				$requestXmlBody .= '		<StoreCategoryID>'.$row["StoreCategoryID"].'</StoreCategoryID>';
				$requestXmlBody .= '	</Storefront>';
			}
	
	
			//VATDetails
			if ( $account[$id_account]["production"]>0 )
			{
				$requestXmlBody .= '	<VATDetails>';
				$requestXmlBody .= '		<BusinessSeller>false</BusinessSeller>';
				$requestXmlBody .= '		<RestrictedToBusiness>false</RestrictedToBusiness>';
				$requestXmlBody .= '		<VATPercent>'.UST.'</VATPercent>';
				$requestXmlBody .= '	</VATDetails>';
			}
	
			$requestXmlBody .= '</Item>';
	
			$requestXmlBody .= '<Version>'.$compatabilityLevel.'</Version>';
			$requestXmlBody .= '<WarningLevel>High</WarningLevel>';
			if ($row["EbayID"]>0) $requestXmlBody .= '</ReviseItemRequest>';
			else $requestXmlBody .= '</AddItemRequest>';
		}


//		echo htmlentities($requestXmlBody);
//		exit;

		
		//Create a new eBay session with all details pulled in from included keys.php
		if ( $requestXmlBody!="" )
		{
			$siteID = 77; //DE
			
			if ($active==0)
			{
				$verb="EndItem";
			}
			elseif ($row["EbayID"]>0)
			{
				$verb = 'ReviseItem';
			}
			else
			{
				$verb = 'AddItem';
			}
			
			if ( $account[$id_account]["production"]==0 )
			{
				$serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
				$session = new eBaySession($account[$id_account]["token_sandbox"], $account[$id_account]["devID_sandbox"], $account[$id_account]["appID_sandbox"], $account[$id_account]["certID_sandbox"], $serverUrl, $compatabilityLevel, $siteID, $verb);
			}
			else
			{
				echo $serverUrl = 'https://api.ebay.com/ws/api.dll';
				echo '<br /><br />';
				$session = new eBaySession($account[$id_account]["token"], $account[$id_account]["devID"], $account[$id_account]["appID"], $account[$id_account]["certID"], $serverUrl, $compatabilityLevel, $siteID, $verb);
			}
		
			//send the request and get response
			$responseXml = $session->sendHttpRequest($requestXmlBody);
			if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
				die('<P>Error sending request');
				
			//show in html
//			echo '<hr />'.htmlentities($responseXml).'<hr />';
//			exit;
				
			//Xml string is parsed and creates a DOM Document object
			$responseDoc = new DomDocument();
			$responseDoc->loadXML($responseXml);
			
			//get any error nodes
			$errors = $responseDoc->getElementsByTagName('Errors');
		}

		//if there are error nodes
//		echo '<br /><br />requestXmlBody: '.$requestXmlBody;
		if( $requestXmlBody!="" and $errors->length>0 )
		{
			echo '<P><B>eBay returned the following error(s):</B>';
			echo '<br /><br />Artikelnr = '.$artnr.'<br /><br />';
			echo 'Item_ID = '.$id_item.'<br />';
			echo 'Auction_ID = '.$id_auction.'<br />';
			echo 'Account_ID = '.$id_account.'<br />';

			//display each error
			//Get error code, ShortMesaage and LongMessage
			$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
			$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
			$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');

			if ($code->item(0)->nodeValue==291)
			{
				q("DELETE FROM ebay_auctions WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
				echo '<br /><br />Fehler erkannt. Auktion gelöscht.<br /><br />';
			}
			elseif ($code->item(0)->nodeValue==1047)
			{
				q("DELETE FROM ebay_auctions WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
				echo '<br /><br />Angebot wurde bereits beendet. Auktion gelöscht.<br /><br />';
			}
			elseif ($code->item(0)->nodeValue==21917122)
			{
				q("DELETE FROM ebay_auctions WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
				echo '<br /><br />Kein gültiges Fahrzeug. Auktion gelöscht.<br /><br />';
			}
			else
			{
				if ($code->item(0)->nodeValue!=518) // Call-Limit überschritten
				{
					//mail code and messages
					$header  = 'MIME-Version: 1.0' . "\r\n";
					$header .= 'Content-type: text/html; charset=utf-8' . "\r\n";
					$header .= 'From: Server <info@mapco.de>'. "\r\n";
					$subject = 'eBay-Error '.$code->item(0)->nodeValue.' '.$shortMsg->item(0)->nodeValue;
					$msg  = '<p>Auktions-ID: '.$id_auction.'</p>';
					$msg .= '<p>Artikelnr : '.$artnr.'</p>';
					$msg .= '<p>Item-ID : '.$id_item.'</p>';
					$msg .= '<p>Account-ID : '.$id_account.'</p>';
					$msg .= '<p>eBay-Fehlernummer: '.$code->item(0)->nodeValue.'</p>';
					$msg .= '<p>'.$shortMsg->item(0)->nodeValue.'</p>';
					$msg .= '<p>'.$longMsg->item(0)->nodeValue.'</p>';
					mail("developer@mapco.de", $subject, $msg, $header);
				}
				else
				{
					echo '<P>Call-Limit erreicht, starte neu!';
					sleep(200);
					exit;
				}
			}

			
			//Display code and shortmessage
			echo '<P>', $code->item(0)->nodeValue, ' : ', str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue));
			//if there is a long message (ie ErrorLevel=1), display it
			if(count($longMsg) > 0)
				echo '<BR>', str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue));
		}
		else
		{ //no errors
			//get results nodes
//			if ($row["EbayID"]>0) $responses = $responseDoc->getElementsByTagName("ReviseFixedPriceItemResponse");
//			else $responses = $responseDoc->getElementsByTagName("AddFixedPriceItemResponse");

			//remove obsolete auctions
//			echo '<br /><br />active: '.$active;
//			echo '<br /><br />ebayid: '.$row["EbayID"];
			if ($active==0 and $row["EbayID"]==0)
			{
				q("DELETE FROM ebay_auctions WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
				echo 'Auktion erfolgreich gelöscht.';
			}
			else
			{
				if ( $requestXmlBody!="" )
				{
					if ($active==0) $responses = $responseDoc->getElementsByTagName("EndItemResponse");
					elseif ($row["EbayID"]>0) $responses = $responseDoc->getElementsByTagName("ReviseItemResponse");
					else $responses = $responseDoc->getElementsByTagName("AddItemResponse");
					
					foreach ($responses as $response)
					{
					  $acks = $response->getElementsByTagName("Ack");
					  $ack   = $acks->item(0)->nodeValue;
					  $msg = "Ack = $ack <BR />\n";   // Success if successful
					  
					  $endTimes  = $response->getElementsByTagName("EndTime");
					  $endTime   = $endTimes->item(0)->nodeValue;
					  $ebay_lastmod=strtotime($endTime);
					  $msg .= "endTime = $endTime <BR />\n";
					  $msg .= "endTime Timestamp = $ebay_lastmod <br />\n";
					  
					  $itemIDs  = $response->getElementsByTagName("ItemID");
					  $ebay_id   = $itemIDs->item(0)->nodeValue;
					  $query="UPDATE ebay_auctions SET EbayID=".$ebay_id.", lastupdate=".time()." WHERE id_auction=".$row["id_auction"].";";
					  q($query, $dbshop, __FILE__, __LINE__);
					  
					  $msg .= '<br />Artikelnr = '.$artnr.'<br />';
					  $msg .= '<br />Auction_ID = '.$row["id_auction"];
					  $msg .= '<br />Item_ID = '.$id_item;
					  $msg .= '<br />Account_ID = '.$id_account.'<br />';
					  $msg .= '<br /><a target="_blank" href="http://cgi.sandbox.ebay.de/ws/eBayISAPI.dll?ViewItem&item='.$ebay_id.'">Sandbox-Link</a>';
					  $msg .= '<br /><a target="_blank" href="http://www.ebay.de/itm/'.$ebay_id.'">Live-Link</a><br />';
								  
					  echo $msg;
					  
					  $linkBase = "http://cgi.sandbox.ebay.com/ws/eBayISAPI.dll?ViewItem&item=";
		//				  echo "<a href=$linkBase" . $ebay_id . ">$itemTitle</a> <BR />";
					  
					  $feeNodes = $responseDoc->getElementsByTagName('Fee');
					  foreach($feeNodes as $feeNode) {
						$feeNames = $feeNode->getElementsByTagName("Name");
						if ($feeNames->item(0)) {
							$feeName = $feeNames->item(0)->nodeValue;
							$fees = $feeNode->getElementsByTagName('Fee');  // get Fee amount nested in Fee
							$fee = $fees->item(0)->nodeValue;
							if ($fee > 0.0) {
								if ($feeName == 'ListingFee') {
								  printf("<B>$feeName : %.2f </B><BR>\n", $fee); 
								} else {
								  printf("$feeName : %.2f <BR>\n", $fee);
								}      
							}  // if $fee > 0
						} // if feeName
					  } // foreach $feeNode
					
					} // foreach response
				}
			}
			
		} // if $errors->length > 0
	}	
?>