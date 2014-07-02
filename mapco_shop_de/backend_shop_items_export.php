<?php
	include("config.php");
//	include("templates/".TEMPLATE_BACKEND."/header.php");
	include("functions/cms_remove_element.php");
	include("functions/mapco_get_titles.php");
	include("functions/shop_get_price.php");
	include("functions/mapco_gewerblich.php");
	include("functions/mapco_baujahr.php");
	include("functions/cms_t.php");


	function cutout($text, $from, $to)
	{
		while($start=strpos($text, $from))
		{
			$end=strpos($text, $to, $start)+strlen($to);
			$text2=substr($text, 0, $start);
			$text2.=substr($text, $end, strlen($text));
			$text=$text2;
		}
		return($text);
	}

	//PATH
	/*
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > <a href="backend_shop_items.php">Artikel</a>';
	echo ' > Artikel-Export';
	echo '</p>';
	*/
	

	//eBay export
	if ($_POST["export_type"]==0 or $_POST["export_type"]==1 or $_POST["export_type"]==2)
	{
		$handle=fopen("csvmanager.csv", "wb");
		
		//header
		fwrite($handle, 'Action(CC=UTF-8);SiteID;Format;Title;Condition;SubTitle;Custom Label;Category;Category2;StoreCategory;StoreCategory2;Quantity;LotSize;Currency;StartPrice;BuyItNowPrice;ReservePrice;InsuranceOption;InsuranceFee;DomesticInsuranceOption;DomesticInsuranceFee;PackagingHandlingCosts;InternationalPackagingHandlingCosts;Duration;PrivateAuction;Country;ProductIDType;ProductIDValue;Product:ProductReferenceID;ItemID;Description;HitCounter;PicURL;BoldTitle;Featured;GalleryType;FeaturedFirstDuration;Highlight;Border;HomePageFeatured;Subtitle in search resutls;GiftIcon;GiftServices-1;GiftServices-2;GiftServices-3;SalesTaxPercent;SalesTaxState;ShippingInTax;UseTaxTable;PostalCode;ProxyItem;VATPercent;Location;ImmediatePayRequired;PayPalAccepted;PayPalEmailAddress;PaymentInstructions;PaymateAccepted;ProPayAccepted;MoneyBookersAccepted;CCAccepted;AmEx;Discover;VisaMastercard;IntegratedMerchantCreditCard;COD;CODPrePayDelivery;PostalTransfer;MOCashiers;PersonalCheck;MoneyXferAccepted;MoneyXferAcceptedinCheckout;PaymentOther;OtherOnlinePayments;PaymentSeeDescription;Escrow;ShippingType;ShipFromZipCode;ShippingIrregular;ShippingPackage;WeightMajor;WeightMinor;WeightUnit;MeasurementUnit;ShippingDetails/CODCost;PackageLength;PackageWidth;PackageDepth;DomesticRateTable;InternationalRateTable;CharityID;CharityName;DonationPercent;ShippingService-1:Option;ShippingService-1:Cost;ShippingService-1:AdditionalCost;ShippingService-1:Priority;ShippingService-1:FreeShipping;ShippingService-1:ShippingSurcharge;ShippingService-2:Option;ShippingService-2:Cost;ShippingService-2:AdditionalCost;ShippingService-2:Priority;ShippingService-2:ShippingSurcharge;ShippingService-3:Option;ShippingService-3:Cost;ShippingService-3:AdditionalCost;ShippingService-3:Priority;ShippingService-3:ShippingSurcharge;ShippingService-4:Option;ShippingService-4:Cost;ShippingService-4:AdditionalCost;ShippingService-4:Priority;ShippingService-4:ShippingSurcharge;ShippingService-5:Option;ShippingService-5:Cost;ShippingService-5:AdditionalCost;ShippingService-5:Priority;ShippingService-5:ShippingSurcharge;GetItFast;DispatchTimeMax;IntlShippingService-1:Option;IntlShippingService-1:Cost;IntlShippingService-1:AdditionalCost;IntlShippingService-1:Locations;IntlShippingService-1:Priority;IntlShippingService-2:Option;IntlShippingService-2:Cost;IntlShippingService-2:AdditionalCost;IntlShippingService-2:Locations;IntlShippingService-2:Priority;IntlShippingService-3:Option;IntlShippingService-3:Cost;IntlShippingService-3:AdditionalCost;IntlShippingService-3:Locations;IntlShippingService-3:Priority;IntlShippingService-4:Option;IntlShippingService-4:Cost;IntlShippingService-4:AdditionalCost;IntlShippingService-4:Locations;IntlShippingService-4:Priority;IntlShippingService-5:Option;IntlShippingService-5:Cost;IntlShippingService-5:AdditionalCost;IntlShippingService-5:Locations;IntlShippingService-5:Priority;IntlAddnlShiptoLocations;PaisaPayAccepted;PaisaPay EMI payment;BasicUpgradePackBundle;ValuePackBundle;ProPackPlusBundle;BestOfferEnabled;AutoAccept;BestOfferAutoAcceptPrice;AutoDecline;MinimumBestOfferPrice;BestOfferRejectMessage;LocalOnlyChk;LocalListingDistance;BuyerRequirements:ShipToRegCountry;BuyerRequirements:ZeroFeedbackScore;BuyerRequirements:MinFeedbackScore;BuyerRequirements:MaxUnpaidItemsCount;BuyerRequirements:MaxUnpaidItemsPeriod;BuyerRequirements:MaxItemCount;BuyerRequirements:MaxItemMinFeedback;BuyerRequirements:LinkedPayPalAccount;BuyerRequirements:VerifiedUser;BuyerRequirements:VerifiedUserScore;BuyerRequirements:MaxViolationCount;BuyerRequirements:MaxViolationPeriod;SellerDetails:PrimaryPhone;SellerDetails:SecondaryPhone;ExtSellerDetails:Hours1Days;ExtSellerDetails:Hours1AnyTime;ExtSellerDetails:Hours1From;ExtSellerDetails:Hours1To;ExtSellerDetails:Hours2Days;ExtSellerDetails:Hours2AnyTime;ExtSellerDetails:Hours2From;ExtSellerDetails:Hours2To;ExtSellerDetails:TimeZoneID;ListingDesigner:LayoutID;ListingDesigner:ThemeID;ProStores Name;ProStores Enabled;ShippingDiscountProfileID;InternationalShippingDiscountProfileID;Apply Profile Domestic;Apply Profile International;PromoteCBT;ShipToLocations;CustomLabel;CashOnPickup;ReturnsAcceptedOption;ReturnsWithinOption;RefundOption;ShippingCostPaidBy;WarrantyOffered;WarrantyType;WarrantyDuration;AdditionalDetails;MarketplaceType;ProjectGoodCategory;ShortDescription;ProducerDescription;RegionOfOrigin;ProducerPhotoURL;A:Artikelzustand;Relationship;RelationshipDetails');
		
		for($i=0; $i<sizeof($_POST["item_id"]); $i++)
		{
			//get artnr
			$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["item_id"][$i].";", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$artnr=$row["MPN"];
		
			
			//title with replacements
			$titles=get_titles($artnr, 80);
		
			
			for ($j=0; $j<sizeof($titles); $j++)
			{
				//general details
				$SiteID='Germany';
				$Country='DE';
				$Currency='EUR';
				$InsuranceFee=0;
				$DomesticInsuranceFee=0;
				$PrivateAuction=0;
				$Quantity=10; //mandatory
				$Format='FixedPriceItem'; //mandatory
				
				//description
				$query="SELECT * FROM shop_items_de WHERE id_item=".$_POST["item_id"][$i].";";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$Title='"'.$titles[$j].'"'; //mandatory
				$Subtitle='';
				$Description='
<style type="text/css">	
.hover
{
	width:600px;
	margin:5px 0px 5px 45px;
	border:0;
	padding:0;
	background:#ffffff;
	font-family:Arial, Helvetica, sans-serif;
	font-family:Arial, Helvetica, sans-serif;
}
.hover tr:hover
{
	background:#eeeeee;
	font-family:Arial, Helvetica, sans-serif;
}
.hover tr td
{
	border:1px solid #cccccc;
	color:#393939;
	font-family:Arial, Helvetica, sans-serif;
}
.hover tr th
{
	border:1px solid #cccccc;
/*	color:#222222;;
	background:#eeeeee; */
	color:#ffffff;
	background:#e25400 url(http://www.mapco.de/tempßlates/shop/images/table_bg.jpg);
	font-family:Arial, Helvetica, sans-serif;
}

.box
{
	width:100%;
	margin:10px 0px 10px 0px;
	border:1px solid #cccccc;
	padding:20px 0px 20px 0px;
	font-family:Arial, Helvetica, sans-serif;
}
.box h1
{
	width:600px;
	margin:5px 0px 5px 45px;
	border:0;
	padding:0;
	color:#333333;
	font-size:15px;
	font-weight:bold;
	font-family:Arial, Helvetica, sans-serif;
}
.box p, ul
{
	width:600px;
	margin:5px 0px 5px 45px;
	font-family:Arial, Helvetica, sans-serif;
}

.box img
{
	margin:5px 0px 5px 45px;
}

.box ul
{
	list-style:circle;
}
		</style>
		';
				
				if ($_POST["export_type"]==0)
				{
					$Description .=  '<div class="box">';
					if (strpos($row["title"], "HPS")>0) $Description .=  '<img src="http://www.mapco.de/images/ebay_hps.jpg" />';
					else $Description .=  '<img src="http://mapco.de/images/ebay_header_mapco.png" />';
					$Description .=  '	<h1 style="font-size:20px;">'.$titles[$j].'</h1>';
					if ($_POST["comment"]!="") $Description .=  '<p>'.nl2br($_POST["comment"]).'</p>';
					$Description .=  '	<p>Bitte prüfen Sie die Fahrzeugzuordnungsliste, ob die Schlüsselnummern Ihres Fahrzeuges dort aufgeführt sind. Bitte beachten Sie außerdem die roten Einschränkungen, wenn vorhanden!</p>';
					$Description .=  '	<p>Wenn Sie sich nicht sicher sind, können Sie gerne per E-Mail unter <a href="mailto:ebay@mapco.de">ebay@mapco.de</a> nachfragen! Oder nutzen Sie unsere Technik-Hotline:</p>';
					$Description .=  '  <img src="http://mapco.de/images/technik_hotline.png" />';
					$Description .=  '	<p style="color:#090; font-weight:bold;">Unser eBay-Shop befindet sich gerade im Aufbau. Wir haben über 13.000 verschiedene Wartungs- und Ersatzteile im Lager. Was Sie auch benötigen: fragen Sie uns! ebay@mapco.de</p>';
					$Description .=  '<img src="http://mapco.de/images/platzhalter_0.png" />';
					$Description .=  '</div>';
				}
				elseif ($_POST["export_type"]==1)
				{
					$Description .=  '<div class="box">';
					if (strpos($row["title"], "HPS")>0) $Description .=  '<img src="http://www.mapco.de/images/ebay_hps.jpg" />';
					else $Description .=  '<img src="http://mapco.de/images/newsletter_header.jpg" />';
					$Description .=  '	<h1 style="font-size:20px;">'.$titles[$j].'</h1>';
					if ($_POST["comment"]!="") $Description .=  '<p>'.nl2br($_POST["comment"]).'</p>';
					$Description .=  '	<p>Bitte prüfen Sie die Fahrzeugzuordnungsliste, ob die Schlüsselnummern Ihres Fahrzeuges dort aufgeführt sind. Bitte beachten Sie außerdem die roten Einschränkungen, wenn vorhanden!</p>';
					$Description .=  '	<p>Wenn Sie sich nicht sicher sind, können Sie gerne per E-Mail unter <a href="mailto:ebay@ihr-autopartner.com">ebay@ihr-autopartner.com</a> nachfragen! Oder nutzen Sie unsere Technik-Hotline:</p>';
					$Description .=  '  <img src="http://mapco.de/images/technik_hotline.png" />';
					$Description .=  '	<p style="color:#090; font-weight:bold;">Unser eBay-Shop befindet sich gerade im Aufbau. Wir haben über 13.000 verschiedene Wartungs- und Ersatzteile im Lager. Was Sie auch benötigen: fragen Sie uns! ebay@ihr-autopartner.com</p>';
					$Description .=  '<img src="http://mapco.de/images/platzhalter_1.png" />';
					$Description .=  '</div>';
				}
				else
				{
					$Description .=  '<div class="box">';
					$Description .=  '<img src="http://mapco.de/images/mocom_header.jpg" />';
					$Description .=  '	<h1 style="font-size:20px;">'.$titles[$j].'</h1>';
					if ($_POST["comment"]!="") $Description .=  '<p>'.nl2br($_POST["comment"]).'</p>';
					$Description .=  '	<p>Bitte prüfen Sie anhand untenstehender Tabelle, ob der Artikel für Ihr Fahrzeug geeignet ist. Wenn Sie sich nicht sicher sind, können Sie gerne per E-Mail unter <a href="mailto:verkauf@mocom-germany.de">verkauf@mocom-germany.de</a> nachfragen!</p>';
					$Description .=  '	<p style="color:#090; font-weight:bold;">Unser eBay-Shop befindet sich gerade im Aufbau. Wir haben über 13.000 verschiedene Wartungs- und Ersatzteile im Lager. Was Sie auch benötigen: fragen Sie uns! verkauf@mocom-germany.de</p>';
					$Description .=  '<img src="http://mapco.de/images/platzhalter_2.png" />';
					$Description .=  '</div>';
				}
				
				$Description.=$row["description"];
				$Description =cutout($Description, '<!-- Reverse Start -->', '<!-- Reverse Stop -->');
				$Description=str_replace('<h1>', '<div class="box"><h1>', $Description);
				$Description=str_replace('</table>', '</table></div>', $Description);
				$Description=cutout($Description, '<a href="', '">');
				$Description=str_replace('</a>', '', $Description);

				$Description .= '<div style="font-family:Arial, Helvetica, sans-serif;">';
				$Description .= '<i>OEM-Nummern dienen ausschließlich Vergleichszwecken.</i>';
				$Description .= '<br /><i>Alle Artikelfotos sind Originalbilder des angebotenen Artikels und Eigentum der MAPCO Autotechnik GmbH.</i>';
				
				$Description .= '<br /><br /><span style="font-size:16px; font-weight:bold; color:#ff0022;">ACHTUNG - bitte teilen Sie uns zu jedem Kauf die Schlüsselnummern zu 2. und 3. sowie das Datum der Erstzulassung mit. Nur so können wir gewährleisten, dass Sie die passenden Teile erhalten.</span>';
				$Description .= '</div>';
				
			
				$Description .= '<div class="box">';
				if ($_POST["export_type"]==0)
				{
					$Description .= '<h1>Selbstabholung und Versand</h1>';
					$Description .= '<p>Nach vorheriger Absprache ist eine Abholung in einem unserer RegionalCENTER an folgenden Standorten möglich:</p>';
					$Description .= '<ul>';
					$Description .= '<li>Berlin</li>';
					$Description .= '<li>Brück</li>';
					$Description .= '<li>Dresden</li>';
					$Description .= '<li>Essen</li>';
					$Description .= '<li>Frankfurt/Main</li>';
					$Description .= '<li>Leipzig</li>';
					$Description .= '<li>Magdeburg</li>';
					$Description .= '<li>Neubrandenburg</li>';
					$Description .= '<li>Sömmerda</li>';
					$Description .= '</ul>';				
				}
				else
				{
					$Description .= '<h1>Versand</h1>';
				}
				$Description .= '<p>Inselzuschlag von 10,15€  wird erhoben für PLZ: 18565, 25845-25849, 25859, 25863, 25869, 25929-25955, 25961-25999, 26465-26486, 26548, 26571-26579, 26757,  27498-27499, 83209, 83256</p>';

				if ($_POST["export_type"]!=2)
				{	
					$Description .= '<br /><br />';
					$Description .= '<h1>International shipping fares:</h1>';
					$Description .= '<ul>';
					$Description .= '<li>BeNeLux: € 7,60</li>';
					$Description .= '<li>Denmark: € 9,07</li>';
					$Description .= '<li>Italy: € 13,82</li>';
					$Description .= '<li>UK: € 17,05</li>';
					$Description .= '<li>France: € 17,60</li>';
					$Description .= '<li>Sweden: € 17,65</li>';
					$Description .= '</ul><br />';
					$Description .= '<h1>Other countries upon request – inquiries welcome!</h1>';
					$Description .= '</div>';

					$Description .= '<div class="box">';
					$Description .= '<h1>Über MAPCO</h1>';
					$Description .= '<p>MAPCO-Produkte werden in Deutschland seit 1977 angeboten und mit großem Erfolg verkauft. Millionenfach werden MAPCO-Produkte in unendlich viele Fahrzeugtypen eingebaut. Kunden-zufriedenheit besitzt stets höchste Priorität. Ursprünglich in Frankreich als Aktiengesellschaft gegründet, werden heute sämtliche MAPCO-Aktivitäten von Borkheide bei Berlin gesteuert.</p>';
					$Description .= '<p>MAPCO hat sich seit mehr als 3 Jahrzehnten europaweit einen Namen als Bremsenspezialist gemacht. Obwohl das Lieferprogramm inzwischen gewaltig erweitert wurde, wird das Programm Bremsenteile innerhalb des Sortiments weiter gepflegt und entwickelt.</p>';
					$Description .= '<p>MAPCO-Lenkungs- und Chassisteile werden seit 1985 auf dem deutschen Markt angeboten. Das Programm entwickelte sich allerdings erst ab etwa 1995 in die Dimension, die heute erreicht wurde. Die neuen Technologien bei der Vorder- und Hinterachskonstruktion, welche die Automobilhersteller in den 90er Jahren einführten, hat zu einem explosionsartig ansteigenden Marktpotential für diese Ersatzteile geführt. Weit mehr als 3500 Einzelpositionen werden in dieser Produktfamilie geführt. Der Produktkatalog mit Originalabbildungen ist übersichtlich und praxisnah gehalten. Qualität, Preis und Verfügbarkeit dieser Teile sind vorbildlich.</p>';
					$Description .= '<p>MAPCO-Lenkgetriebe für hydraulische und mechanische Lenkungen runden das Programm ab. Auch hier hat die von der Automobilindustrie in den 90er Jahren verfolgte Politik der Erhöhung von Komfort und Sicherheit im Fahrzeug einen völlig neuen Ersatzteilmarkt entstehen lassen. Das MAPCO-Programm beinhaltet des Weiteren eine Vielzahl umsatzstarker Verschleißteile.</p>';
					$Description .= '</div>';
					$Description .= '<img src="http://www.mapco.de/templates/shop/images/sitemap_bg.jpg" />';
				}

				$Description=str_replace("\r", "", $Description);
				$Description=str_replace("\n", "", $Description);
				$Description='"'.str_replace('"', '""', $Description).'"';


				//category details
				$query="SELECT GART FROM t_200 WHERE ArtNr='".$artnr."';";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$query="SELECT * FROM mapco_gart_export WHERE GART='".$row["GART"]."';";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results)==0)
				{
					$query="SELECT * FROM mapco_gart_export WHERE GART=0;";
					$results=q($query, $dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($results);
					$Category=$row["Category"];
					$Category2=$row["Category2"];
					$StoreCategory=$row["StoreCategory"];
					$StoreCategory2=$row["StoreCategory2"];
				}
				else
				{
					$row=mysqli_fetch_array($results);
					if ($row["Category"]==0)
					{
						$query="SELECT * FROM mapco_gart_export WHERE GART=0;";
						$results=q($query, $dbshop, __FILE__, __LINE__);
						$row=mysqli_fetch_array($results);
						$Category=$row["Category"];
						$Category2=$row["Category2"];
						$StoreCategory=$row["StoreCategory"];
						$StoreCategory2=$row["StoreCategory2"];
					}
					else
					{
						$Category=$row["Category"];
						$Category2=$row["Category2"];
						$StoreCategory=$row["StoreCategory"];
						$StoreCategory2=$row["StoreCategory2"];
					}
				}
				
				
				//style details
				$GalleryType='Gallery';
				$HitCounter='BasicStyle';
				$BoldTitle=0;
				$Featured=0;
				$Highlight=0;
				$Border=0;
				$HomePageFeatured=0;
				$Subtitle_in_search_resutls=0;
				$GiftIcon=0;
				$ListingDesigner_LayoutID=10000;
				$ListingDesigner_ThemeID=7710;

				//images
				$k=0;
				$count=0;
				$PicURL='';
				$BackupURL='';
				$results=q("SELECT * FROM shop_items_files WHERE item_id=".$_POST["item_id"][$i].";", $dbshop, __FILE__, __LINE__);
				while($row=mysqli_fetch_array($results))
				{
					if ($k < 3)
					{
						$results2=q("SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
						$row2=mysqli_fetch_array($results2);
						$results3=q("SELECT a.* FROM cms_files AS a, cms_files_labels As b WHERE a.original_id=".$row2["original_id"]." and a.id_file=b.file_id and b.label_id=8;", $dbweb, __FILE__, __LINE__, 'item_id '.$_POST["item_id"][$i].' / file_id '.$row["file_id"]);
						if (mysqli_num_rows($results3)==0 or (mysqli_num_rows($results3)>0 and $_POST["export_type"]>0))
						{
							$k++;
							if ($k>1) $PicURL.='|';
							$PicURL.='http://www.mapco.de/files/'.floor(bcdiv($row["file_id"], 1000)).'/'.$row["file_id"].'.jpg';
							$BackupURL[$k].='|http://www.mapco.de/images/MAPCO_AP.png';
						}
						else
						{
							$row3=mysqli_fetch_array($results3);					
							$k++;
							if ($k>1) $PicURL.='|';
							$PicURL.='http://www.mapco.de/files/'.floor(bcdiv($row3["id_file"], 1000)).'/'.$row3["id_file"].'.jpg';
							$BackupURL[$k].='|http://www.mapco.de/files/'.floor(bcdiv($row["file_id"], 1000)).'/'.$row["file_id"].'.jpg';
						}
						$count++;
					}
				}
				if ($count == 1) $PicURL.= $BackupURL[1].'|http://www.mapco.de/images/MAPCO_AP.png';
				if ($count == 2) $PicURL.= $BackupURL[1];
	
				//price
				if ($_POST["pl_type"]==16815)
				{
					$price=get_price($_POST["item_id"][$i], 1, 22811);
					$StartPrice=number_format($price*((100+UST)/100), 2); //mandatory
				}
				else
				{
					$query="SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR=".$_POST["pl_type"].";";
					$results=q($query, $dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($results);
					$StartPrice=number_format($row["POS_0_WERT"]*((100+UST)/100), 2); //mandatory
				}
				$StartPrice=str_replace(".", ",", $StartPrice);
				$BuyItNowPrice='';
				
				//auction details
				$Condition='1000'; //mandatory
				$A_Artikelzustand='Neu';
				$Duration='GTC'; //mandatory (in days)
				$ProStores_Enabled=0;
				$CustomLabel='"'.$artnr.'"'; //MAPCO ArtNr
				$ItemID=''; //eBay Auction ID is later added by turbolister
				$ReturnsAcceptedOption=1;
				
				//payment details
				$ImmediatePayRequired=0;
				$VATPercent=UST;
				$ImmediatePayRequired=0;
				$PayPalAccepted=1;
				if ($_POST["export_type"]==0)
				{
					$PayPalEmailAddress='ebay@mapco.de';
				}
				elseif ($_POST["export_type"]==1)
				{
					$PayPalEmailAddress='ebay@ihr-autopartner.com';
				}
				else
				{
					$PayPalEmailAddress='verkauf@mocom-germany.de';
				}
				$MoneyXferAcceptedinCheckout=1;
				$PaymentSeeDescription=0;
//				$PaymentInstructions='Inselzuschlag von 10,15€ wird erhoben für PLZ: 18565, 25845-25849, 25859, 25863, 25869, 25929-25955, 25961-25999, 26465-26486, 26548, 26571-26579, 26757, 27498-27499, 83209, 83256. Bitte beachten Sie dies bei Ihrer Zahlung. Keine Lieferung an Postfächer oder Packstationen.';
				
				//shipping details
				$ShippingType='Flat';
				$ShippingDiscountProfileID='1|169197020|';
				$InternationalShippingDiscountProfileID='0|169197020|';
				$Apply_Profile_Domestic=0;
				$Apply_Profile_International=0;
				$ShippingService_1_Option='DE_DPDClassic';
				$ShippingService_1_Cost='5,90';
				$ShippingService_1_AdditionalCost='0,00';
				$ShippingService_1_Priority=1;
				$ShippingService_1_FreeShipping=0;
				$ShippingService_1_ShippingSurcharge='';
				if ($_POST["export_type"]==0)
				{
					$ShippingService_2_Option='"DE_Pickup"';
					$ShippingService_2_Cost=0;
					$ShippingService_2_AdditionalCost=0;
					$ShippingService_2_Priority=2;
					$ShippingService_2_FreeShipping=0;
					$ShippingService_2_ShippingSurcharge='';
				}
				$GetItFast=0;
				$DispatchTimeMax=1; //mandatory
				
				$IntlShippingService_1_Option='DE_PaketInternational';
				$IntlShippingService_1_Cost='8,96';
				$IntlShippingService_1_AdditionalCost='0,00';
				$IntlShippingService_1_Locations='AT';
				$IntlShippingService_1_Priority=1;
				
				$ValuePackBundle=0;
				$BestOfferEnabled=1;
				$BuyerRequirements_LinkedPayPalAccount=0;
				$Location=''; //mandatory
				$PostalCode=14822;
				if ($_POST["export_type"]==0) $CashOnPickup=1; else $CashOnPickup=0;
				$ReturnsAcceptedOption='ReturnsAccepted';
/*
				$AdditionalDetails=nl2br('Widerrufsbelehrung
1. Widerrufsrecht: Der Verbraucher kann die Vertragserklärung innerhalb von 14 Tagen ohne Angabe von Gründen in Textform (z. B. Brief, Fax, E-Mail) oder – wenn die Sache vor Fristablauf überlassen wird – durch Rücksendung der Sache widerrufen. Die Frist beginnt nach Erhalt dieser Belehrung in Textform. Zur Wahrung der Widerrufsfrist genügt die rechtzeitige Absendung des Widerrufs oder der Sache. Der Widerruf ist zu richten an: MAPCO Autotechnik GmbH, Moosweg 1, 14882 Borkheide, Deutschland.
2. Widerrufsfolgen: Im Falle eines wirksamen Widerrufs sind die beiderseits empfangenen Leistungen zurückzugewähren und gegebenenfalls gezogene Nutzungen (z. B. Zinsen) herauszugeben. Kann der Verbraucher dem Verkäufer die empfangene Leistung ganz oder teilweise nicht oder nur in verschlechtertem Zustand zurückgewähren, müssen, muss er insoweit gegebenenfalls Wertersatz leisten. Bei der Überlassung von Sachen gilt dies nicht, wenn die Verschlechterung der Sache ausschließlich auf deren Prüfung – wie sie etwa im Ladengeschäft möglich gewesen wäre – zurückzuführen ist. Im Übrigen kann der Verbraucher die Pflicht zum Wertersatz für eine durch die bestimmungsgemäße Ingebrauchnahme der Sache entstandene Verschlechterung vermeiden, indem er die Sache nicht wie sein Eigentum in Gebrauch nimmt und alles unterlässt, was deren Wert beeinträchtigt. Paketversandfähige Sachen sind auf Gefahr des Verkäufers zurückzusenden. Der Verbraucher hat die Kosten der Rücksendung zu tragen, wenn die gelieferte Ware der bestellten entspricht und wenn der Preis der zurückzusendenden Sache einen Betrag von 40 Euro nicht übersteigt oder wenn der Verbraucher bei einem höheren Preis der Sache zum Zeitpunkt des Widerrufs noch nicht die Gegenleistung oder eine vertraglich vereinbarte Teilzahlung erbracht hat. Anderenfalls ist die Rücksendung für den Verbraucher kostenfrei. Nicht paketversandfähige Sachen werden bei dem Verbraucher abgeholt. Verpflichtungen zur Erstattung von Zahlungen müssen innerhalb von 30 Tagen erfüllt werden. Die Frist beginnt für den Verbraucher mit der Absendung der Widerrufserklärung oder der Sache, für den Verkäufer mit deren Empfang."');  //Widerrufsrecht
*/			
					   $line="\r\nAdd"
					   .';'.$SiteID
					   .';'.$Format
					   .';'.$Title
					   .';'.$Condition
					   .';'.$SubTitle
					   .';'.$CustomLabel
					   .';'.$Category
					   .';'.$Category2
					   .';'.$StoreCategory
					   .';'.$StoreCategory2
					   .';'.$Quantity
					   .';'.$LotSize
					   .';'.$Currency
					   .';'.$StartPrice
					   .';'.$BuyItNowPrice
					   .';'.$ReservePrice
					   .';'.$InsuranceOption
					   .';'.$InsuranceFee
					   .';'.$DomesticInsuranceOption
					   .';'.$DomesticInsuranceFee
					   .';'.$PackagingHandlingCosts
					   .';'.$InternationalPackagingHandlingCosts
					   .';'.$Duration
					   .';'.$PrivateAuction
					   .';'.$Country
					   .';'.$ProductIDType
					   .';'.$ProductIDValue
					   .';'.$Product_ProductReferenceID
					   .';'.$ItemID
					   .';'.$Description
					   .';'.$HitCounter
					   .';'.$PicURL
					   .';'.$BoldTitle
					   .';'.$Featured
					   .';'.$GalleryType
					   .';'.$FeaturedFirstDuration
					   .';'.$Highlight
					   .';'.$Border
					   .';'.$HomePageFeatured
					   .';'.$Subtitle_in_search_resutls
					   .';'.$GiftIcon
					   .';'.$GiftServices_1
					   .';'.$GiftServices_2
					   .';'.$GiftServices_3
					   .';'.$SalesTaxPercent
					   .';'.$SalesTaxState
					   .';'.$ShippingInTax
					   .';'.$UseTaxTable
					   .';'.$PostalCode
					   .';'.$ProxyItem
					   .';'.$VATPercent
					   .';'.$Location
					   .';'.$ImmediatePayRequired
					   .';'.$PayPalAccepted
					   .';'.$PayPalEmailAddress
					   .';'.$PaymentInstructions
					   .';'.$PaymateAccepted
					   .';'.$ProPayAccepted
					   .';'.$MoneyBookersAccepted
					   .';'.$CCAccepted
					   .';'.$AmEx
					   .';'.$Discover
					   .';'.$VisaMastercard
					   .';'.$IntegratedMerchantCreditCard
					   .';'.$COD
					   .';'.$CODPrePayDelivery
					   .';'.$PostalTransfer
					   .';'.$MOCashiers
					   .';'.$PersonalCheck
					   .';'.$MoneyXferAccepted
					   .';'.$MoneyXferAcceptedinCheckout
					   .';'.$PaymentOther
					   .';'.$OtherOnlinePayments
					   .';'.$PaymentSeeDescription
					   .';'.$Escrow
					   .';'.$ShippingType
					   .';'.$ShipFromZipCode
					   .';'.$ShippingIrregular
					   .';'.$ShippingPackage
					   .';'.$WeightMajor
					   .';'.$WeightMinor
					   .';'.$WeightUnit
					   .';'.$MeasurementUnit
					   .';'.$ShippingDetails_CODCost
					   .';'.$PackageLength
					   .';'.$PackageWidth
					   .';'.$PackageDepth
					   .';'.$DomesticRateTable
					   .';'.$InternationalRateTable
					   .';'.$CharityID
					   .';'.$CharityName
					   .';'.$DonationPercent
					   .';'.$ShippingService_1_Option
					   .';'.$ShippingService_1_Cost
					   .';'.$ShippingService_1_AdditionalCost
					   .';'.$ShippingService_1_Priority
					   .';'.$ShippingService_1_FreeShipping
					   .';'.$ShippingService_1_ShippingSurcharge
					   .';'.$ShippingService_2_Option
					   .';'.$ShippingService_2_Cost
					   .';'.$ShippingService_2_AdditionalCost
					   .';'.$ShippingService_2_Priority
					   .';'.$ShippingService_2_ShippingSurcharge
					   .';'.$ShippingService_3_Option
					   .';'.$ShippingService_3_Cost
					   .';'.$ShippingService_3_AdditionalCost
					   .';'.$ShippingService_3_Priority
					   .';'.$ShippingService_3_ShippingSurcharge
					   .';'.$ShippingService_4_Option
					   .';'.$ShippingService_4_Cost
					   .';'.$ShippingService_4_AdditionalCost
					   .';'.$ShippingService_4_Priority
					   .';'.$ShippingService_4_ShippingSurcharge
					   .';'.$ShippingService_5_Option
					   .';'.$ShippingService_5_Cost
					   .';'.$ShippingService_5_AdditionalCost
					   .';'.$ShippingService_5_Priority
					   .';'.$ShippingService_5_ShippingSurcharge
					   .';'.$GetItFast
					   .';'.$DispatchTimeMax
					   .';'.$IntlShippingService_1_Option
					   .';'.$IntlShippingService_1_Cost
					   .';'.$IntlShippingService_1_AdditionalCost
					   .';'.$IntlShippingService_1_Locations
					   .';'.$IntlShippingService_1_Priority
					   .';'.$IntlShippingService_2_Option
					   .';'.$IntlShippingService_2_Cost
					   .';'.$IntlShippingService_2_AdditionalCost
					   .';'.$IntlShippingService_2_Locations
					   .';'.$IntlShippingService_2_Priority
					   .';'.$IntlShippingService_3_Option
					   .';'.$IntlShippingService_3_Cost
					   .';'.$IntlShippingService_3_AdditionalCost
					   .';'.$IntlShippingService_3_Locations
					   .';'.$IntlShippingService_3_Priority
					   .';'.$IntlShippingService_4_Option
					   .';'.$IntlShippingService_4_Cost
					   .';'.$IntlShippingService_4_AdditionalCost
					   .';'.$IntlShippingService_4_Locations
					   .';'.$IntlShippingService_4_Priority
					   .';'.$IntlShippingService_5_Option
					   .';'.$IntlShippingService_5_Cost
					   .';'.$IntlShippingService_5_AdditionalCost
					   .';'.$IntlShippingService_5_Locations
					   .';'.$IntlShippingService_5_Priority
					   .';'.$IntlAddnlShiptoLocations
					   .';'.$PaisaPayAccepted
					   .';'.$PaisaPay_EMI_payment
					   .';'.$BasicUpgradePackBundle
					   .';'.$ValuePackBundle
					   .';'.$ProPackPlusBundle
					   .';'.$BestOfferEnabled
					   .';'.$AutoAccept.';'.$BestOfferAutoAcceptPrice.';'.$AutoDecline
					   .';'.$MinimumBestOfferPrice.';'.$BestOfferRejectMessage.';'.$LocalOnlyChk
					   .';'.$LocalListingDistance
					   .';'.$BuyerRequirements_ShipToRegCountry
					   .';'.$BuyerRequirements_ZeroFeedbackScore
					   .';'.$BuyerRequirements_MinFeedbackScore
					   .';'.$BuyerRequirements_MaxUnpaidItemsCount
					   .';'.$BuyerRequirements_MaxUnpaidItemsPeriod
					   .';'.$BuyerRequirements_MaxItemCount
					   .';'.$BuyerRequirements_MaxItemMinFeedback
					   .';'.$BuyerRequirements_LinkedPayPalAccount
					   .';'.$BuyerRequirements_VerifiedUser
					   .';'.$BuyerRequirements_VerifiedUserScore
					   .';'.$BuyerRequirements_MaxViolationCount
					   .';'.$BuyerRequirements_MaxViolationPeriod.';'.$SellerDetails_PrimaryPhone
					   .';'.$SellerDetails_SecondaryPhone
					   .';'.$ExtSellerDetails_Hours1Days
					   .';'.$ExtSellerDetails_Hours1AnyTime
					   .';'.$ExtSellerDetails_Hours1From
					   .';'.$ExtSellerDetails_Hours1To
					   .';'.$ExtSellerDetails_Hours2Days
					   .';'.$ExtSellerDetails_Hours2AnyTime
					   .';'.$ExtSellerDetails_Hours2From
					   .';'.$ExtSellerDetails_Hours2To
					   .';'.$ExtSellerDetails_TimeZoneID
					   .';'.$ListingDesigner_LayoutID
					   .';'.$ListingDesigner_ThemeID
					   .';'.$ProStores_Name
					   .';'.$ProStores_Enabled
					   .';'.$ShippingDiscountProfileID
					   .';'.$InternationalShippingDiscountProfileID
					   .';'.$Apply_Profile_Domestic
					   .';'.$Apply_Profile_International
					   .';'.$PromoteCBT
					   .';'.$ShipToLocations
					   .';'.$CustomLabel
					   .';'.$CashOnPickup
					   .';'.$ReturnsAcceptedOption
					   .';'.$ReturnsWithinOption
					   .';'.$RefundOption
					   .';'.$ShippingCostPaidBy
					   .';'.$WarrantyOffered
					   .';'.$WarrantyType
					   .';'.$WarrantyDuration
					   .';'.$AdditionalDetails
					   .';'.$MarketplaceType
					   .';'.$ProjectGoodCategory
					   .';'.$ShortDescription
					   .';'.$ProducerDescription
					   .';'.$RegionOfOrigin
					   .';'.$ProducerPhotoURL
					   .';'.$A_Artikelzustand
					   .';'.$Relationship
					   .';'.$RelationshipDetails;
				fwrite($handle, $line);
			}
		}
		fclose($handle);
//		echo '<div class="success">Export erfolgreich abgespeichert. <a href="csvmanager.csv">Download</a></div>';
		$file="csvmanager.csv";
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		flush();
		readfile($file);
		exit;
	}







	/*****************
	 * amazon export *
	 *****************/
	if ($_POST["export_type"]==3)
	{
		$handle=fopen("csvmanager.csv", "wb");
		
		//header
		fwrite($handle, 		
		"TemplateType=AutoAccessory	Version=1.7/1.2.14	This row for Amazon.com use only.  Do not modify or delete.					required product information			required offer fields			product images									product description															AutoAccessory Specific				AutoPartSpecific					more product description										accessory relation		Price per Unit						item dimensions								shipping weight			availability				FBA	promotions			offer related information							custom browse categorization							");
		fwrite($handle, 		
		"\nsku	title	standard-product-id	product-id-type	brand	manufacturer	mfr-part-number	product_type	recommended-browse-node1	recommended-browse-node2	currency	item-price	quantity	main-image-url	other-image-url1	other-image-url2	other-image-url3	other-image-url4	other-image-url5	other-image-url6	other-image-url7	other-image-url8	description	bullet-point1	bullet-point2	bullet-point3	bullet-point4	bullet-point5	search-terms1	search-terms2	search-terms3	search-terms4	search-terms5	size	color	colormap	material	item-shape	viscosity	volume-unit-of-measure	volume	number-of-holes	number-of-grooves	oe-manufacturer	part-interchange-info	manufacturer-warranty-description	merchant-catalog-number	part-type-id	voltage	wattage	amperage-unit-of-measure	amperage	memorabilia	autographed	legal-disclaimer	item-package-quantity	parent-sku	relationship-type	display-weight-unit-of-measure	display-weight	display-volume-unit-of-measure	display-volume	display-length-unit-of-measure	display-length	diameter-unit-of-measure	diameter	item-weight-unit-of-measure	item-weight	item-length-unit-of-measure	item-length	item-height	item-width	shipping-weight-unit-of-measure	shipping-weight	registered-parameter	launch-date	release-date	restock-date	is-discontinued-by-manufacturer	fulfillment-center-id	sale-price	sale-start-date	sale-end-date	condition-type	condition-note	leadtime-to-ship	max-aggregate-ship-quantity	is-gift-message-available	is-giftwrap-available	product-tax-code	platinum-keywords1	platinum-keywords2	platinum-keywords3	platinum-keywords4	platinum-keywords5	update-delete		");
		
		for($i=0; $i<sizeof($_POST["item_id"]); $i++)
		{
			//get artnr
			$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["item_id"][$i].";", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$artnr=$row["MPN"];
		
			//title with replacements
			$titles=get_titles($artnr, 150);
			$query="SELECT * FROM mapco_replacements;";
			$results=q($query, $dbweb, __FILE__, __LINE__);
			while ($row=mysqli_fetch_array($results))
			{
				for($j=0; $j<sizeof($titles); $j++)
				{
					$titles[$j]=str_replace($row["search"], $row["replace"], $titles[$j]);
				}
			}
		
			
				$sku=$artnr; //product ID
				$standard_product_id=''; //EAN, UPC oder GTIN
				$product_id_type='';
				$brand='MAPCO';
				$manufacturer='MAPCO Autotechnik GmbH';
				$mfr_part_number=$artnr;
				$product_type='AutoPart';
				
				//category details
				$query="SELECT GART FROM t_200 WHERE ArtNr='".$artnr."';";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$query="SELECT * FROM mapco_gart_export WHERE GART='".$row["GART"]."';";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results)==0)
				{
					$query="SELECT * FROM mapco_gart_export WHERE GART=0;";
					$results=q($query, $dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($results);
					$recommended_browse_node1=$row["recommended_browse_node1"];
					$recommended_browse_node2=$row["recommended_browse_node2"];
				}
				else
				{
					$row=mysqli_fetch_array($results);
					if ($row["recommended_browse_node1"]==0)
					{
						$query="SELECT * FROM mapco_gart_export WHERE GART=0;";
						$results=q($query, $dbshop, __FILE__, __LINE__);
						$row=mysqli_fetch_array($results);
						$recommended_browse_node1=$row["recommended_browse_node1"];
						$recommended_browse_node2=$row["recommended_browse_node2"];
					}
					else
					{
						$recommended_browse_node1=$row["recommended_browse_node1"];
						$recommended_browse_node2=$row["recommended_browse_node2"];
					}
				}
				if ($recommended_browse_node1<1) $recommended_browse_node1='';
				if ($recommended_browse_node2<1) $recommended_browse_node2='';
				
				
				$curency='EUR';
				

				//price
				if ($_POST["pl_type"]==16815)
				{
					$price=get_price($_POST["item_id"][$i], 1, 22811);
					$item_price=number_format($price*((100+UST)/100), 2); //mandatory
				}
				else
				{
					$query="SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR=".$_POST["pl_type"].";";
					$results=q($query, $dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($results);
					$item_price=number_format($row["POS_0_WERT"]*((100+UST)/100), 2); //mandatory
				}
				
				$quantity=10;
				$item_package_quantity=1;
				
				//images
				$k=0;
				$PicURL='';
				$main_image_url="";
				$other_image_url1="";
				$other_image_url2="";
				$other_image_url3="";
				$other_image_url4="";
				$other_image_url5="";
				$other_image_url6="";
				$other_image_url7="";
				$other_image_url8="";
				$query="SELECT * FROM shop_items_files WHERE item_id=".$_POST["item_id"][$i]." LIMIT 9;";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				while($row=mysqli_fetch_array($results))
				{
					if ($k==0)
					{
						$main_image_url='http://www.mapco.de/files/'.floor(bcdiv($row["file_id"], 1000)).'/'.$row["file_id"].'.jpg';
					}
					else
					{
						${other_image_url.$k}='http://www.mapco.de/files/'.floor(bcdiv($row["file_id"], 1000)).'/'.$row["file_id"].'.jpg';
					}
					$k++;
				}


				//description
				//vehicle applications table
				$fid=array();
				$bez1=array();
				$bez2=array();
				$bez3=array();
				$bjvon=array();
				$bjbis=array();
				$ktypnr=array();
				$kbanr=array();
				$hubraum=array();
				$kw=array();
				$results=q("SELECT * FROM t_210 WHERE ArtNr='".$artnr."' AND SortNr=1;", $dbshop, __FILE__, __LINE__);
				while ($row=mysqli_fetch_array($results))
				{
					//PKW
					if ($row["KritNr"]==2)
					{
						$results2=q("SELECT * FROM fahrz WHERE KTypNr=".$row["KritVal"].";", $dbshop, __FILE__, __LINE__);
						$row2=mysqli_fetch_array($results2);
						if ($row2["f_ID"]!="")
						{
							$fid[$row2["f_ID"]]=$row2["f_ID"];
							$bez1[$row2["f_ID"]]=$row2["BEZ1"];
							$bez2[$row2["f_ID"]]=$row2["BEZ2"];
							$bez3[$row2["f_ID"]]=$row2["BEZ3"];
							$bjvon[$row2["f_ID"]]=$row2["BJvon"];
							$bjbis[$row2["f_ID"]]=$row2["BJbis"];
							$ktypnr[$row2["f_ID"]]=$row2["KTypNr"];
							$kbanr[$row2["f_ID"]]=substr($row2["KBANR"], 0, 4).'-'.substr($row2["KBANR"], 4, 3);
							$hubraum[$row2["f_ID"]]=number_format($row2["ccmTech"]).'ccm';
							$kw[$row2["f_ID"]]=number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)';
						}
					}
				}
				$results=q("SELECT * FROM t_400 WHERE ArtNr='".$artnr."' AND SortNr=1;", $dbshop, __FILE__, __LINE__);
				while ($row=mysqli_fetch_array($results))
				{
					//PKW
					if ($row["KritNr"]==2)
					{
						$results2=q("SELECT * FROM fahrz WHERE KTypNr=".$row["KritWert"].";", $dbshop, __FILE__, __LINE__);
						$row2=mysqli_fetch_array($results2);
						if ($row2["f_ID"]!="")
						{
							$fid[$row2["f_ID"]]=$row2["f_ID"];
							$bez1[$row2["f_ID"]]=$row2["BEZ1"];
							$bez2[$row2["f_ID"]]=$row2["BEZ2"];
							$bez3[$row2["f_ID"]]=$row2["BEZ3"];
							$bjvon[$row2["f_ID"]]=$row2["BJvon"];
							$bjbis[$row2["f_ID"]]=$row2["BJbis"];
							$ktypnr[$row2["f_ID"]]=(int)$row2["KTypNr"];
							$kbanr[$row2["f_ID"]]=substr($row2["KBANR"], 0, 4).'-'.substr($row2["KBANR"], 4, 3);
							$hubraum[$row2["f_ID"]]=number_format($row2["ccmTech"]).'ccm';
							$kw[$row2["f_ID"]]=number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)';
						}
					}
				}
		
				//sort by name
				array_multisort($bez1, $bez2, $bez3, $bjvon, $bjbis, $fid, $ktypnr, $kbanr, $hubraum, $kw);
				
				$description='<table>';
				$description.='<tr>';
				$description.='<th>'.t("Fahrzeug", $lang).'</th>';
				$description.='<th width="120">'.t("Baujahr", $lang).'</th>';
				$description.='<th width="100">'.t("Leistung", $lang).'</th>';
				$description.='<th width="80">'.t("Hubraum", $lang).'</th>';
				$description.='<th width="80">'.t("KBA-Nr.", $lang).'</th>';
				$description.='</tr>';
				for($k=0; $k<sizeof($fid); $k++)
				{
					$description.='<tr>';
					$description.='<td>'.$bez1[$k].' '.$bez2[$k].' '.$bez3[$k];
					if (sizeof($kritnr[$ktypnr[$k]])>0)
					{
						for($j=0; $j<sizeof($kritnr[$ktypnr[$k]]); $j++)
						{
							$description .= '<br /><i>'.$kritnr[$ktypnr[$k]][$j].': '.$kritwert[$ktypnr[$k]][$j].'</i>';
						}
					}
					$description.='</td>';
					$description.='<td>';
					$description.=baujahr($bjvon[$k]).' - '.baujahr($bjbis[$k]);
					$description.='</td>';
					$description.='<td>'.$kw[$k].'</td>';
					$description.='<td>'.$hubraum[$k].'</td>';
					$description.='<td>'.$kbanr[$k].'</td>';
					$description.='</tr>';
				}
				$description.='</table>';
				if (strlen($description)>2000) $description=substr(utf8_decode($description), 0, 1991).'</table>';


				$query="SELECT * FROM shop_items_de WHERE id_item=".$_POST["item_id"][$i].";";
				$results=q($query, $dbshop, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$bullet=explode("; ", utf8_decode($row["short_description"]));
				for($k=0; $k<sizeof($bullet); $k++)
				{
					if ($k<4)
					{
						${bullet_point.($k+1)}=$bullet[$k];
					}
					elseif($k==4)
					{
						$bullet_point5.=$bullet[$k];
					}
					else
					{
						$bullet_point5.=', '.$bullet[$k];
					}
				}
				if (strlen($bullet_point5)>500) $bullet_point5=substr(utf8_decode($bullet_point5), 0, 495);
				$search_terms1='';
				$search_terms2='';
				$search_terms3='';
				$search_terms4='';
				$search_terms5='';
				
				$size='';
				$color='';
				$colormap='';
				$material='';
				$item_shape='';
				$viscosity='';
				$volume_unit_of_measure='';
				$volume='';
				$number_of_holes='';
				$number_of_grooves='';
				$oe_manufacturer='';
				$part_interchange_info='';
				$manufacturer_warranty_description='';
				$merchant_catalog_number='';
				$part_type_id='';
				$voltage='';
				$wattage='';
				


				
				//style details
				$GalleryType='Gallery';
				$HitCounter='BasicStyle';
				$BoldTitle=0;
				$Featured=0;
				$Highlight=0;
				$Border=0;
				$HomePageFeatured=0;
				$Subtitle_in_search_resutls=0;
				$GiftIcon=0;
				$ListingDesigner_LayoutID=10000;
				$ListingDesigner_ThemeID=7710;

	
				
				//auction details
				$Condition='1000'; //mandatory
				$A_Artikelzustand='Neu';
				$Duration='GTC'; //mandatory (in days)
				$ProStores_Enabled=0;
				$CustomLabel='"'.$artnr.'"'; //MAPCO ArtNr
				$ItemID=''; //eBay Auction ID is later added by turbolister
				$ReturnsAcceptedOption=1;
				
				//payment details
				$ImmediatePayRequired=0;
				$VATPercent=UST;
				$ImmediatePayRequired=0;
				$PayPalAccepted=1;
				if ($_POST["export_type"]==0)
				{
					$PayPalEmailAddress='ebay@mapco.de';
				}
				elseif ($_POST["export_type"]==1)
				{
					$PayPalEmailAddress='ebay@ihr-autopartner.com';
				}
				else
				{
					$PayPalEmailAddress='verkauf@mocom-germany.de';
				}
				$MoneyXferAcceptedinCheckout=1;
				$PaymentSeeDescription=0;
				$PaymentInstructions='"Inselzuschlag von 10,15€ wird erhoben für PLZ: 18565, 25845-25849, 25859, 25863, 25869, 25929-25955, 25961-25999, 26465-26486, 26548, 26571-26579, 26757, 27498-27499, 83209, 83256. Bitte beachten Sie dies bei Ihrer Zahlung. Keine Lieferung an Postfächer oder Packstationen."';
				
				//shipping details
				$ShippingType='Flat';
				$ShippingDiscountProfileID='1|169197020|';
				$InternationalShippingDiscountProfileID='0|169197020|';
				$Apply_Profile_Domestic=0;
				$Apply_Profile_International=0;
				$ShippingService_1_Option='DE_DPDClassic';
				$ShippingService_1_Cost='5,90';
				$ShippingService_1_AdditionalCost='0,00';
				$ShippingService_1_Priority=1;
				$ShippingService_1_FreeShipping=0;
				$ShippingService_1_ShippingSurcharge='';
				if ($_POST["export_type"]==0)
				{
					$ShippingService_2_Option='"DE_Pickup"';
					$ShippingService_2_Cost=0;
					$ShippingService_2_AdditionalCost=0;
					$ShippingService_2_Priority=2;
					$ShippingService_2_FreeShipping=0;
					$ShippingService_2_ShippingSurcharge='';
				}
				$GetItFast=0;
				$DispatchTimeMax=1; //mandatory
				$ValuePackBundle=0;
				$BestOfferEnabled=1;
				$BuyerRequirements_LinkedPayPalAccount=0;
				$Location=''; //mandatory
				$PostalCode=14822;
				if ($_POST["export_type"]==0) $CashOnPickup=1; else $CashOnPickup=0;
				$ReturnsAcceptedOption='ReturnsAccepted';

			for ($j=0; $j<sizeof($titles); $j++)
			{
				$line="\n".$sku.'-'.($j+1);
				$line .= "\t".substr(utf8_decode($titles[$j]), 0, 149);
				$line .= "\t".$standard_product_id;
				$line .= "\t".$product_id_type;
				$line .= "\t".$brand;
				$line .= "\t".$manufacturer;
				$line .= "\t".$mfr_part_number;
				$line .= "\t".$product_type;
				$line .= "\t".$recommended_browse_node1;
				$line .= "\t".$recommended_browse_node2;
				$line .= "\t".$currency;
				$line .= "\t".$item_price;
				$line .= "\t".$quantity;
				$line .= "\t".$main_image_url;
				$line .= "\t".$other_image_url1;
				$line .= "\t".$other_image_url2;
				$line .= "\t".$other_image_url3;
				$line .= "\t".$other_image_url4;
				$line .= "\t".$other_image_url5;
				$line .= "\t".$other_image_url6;
				$line .= "\t".$other_image_url7;
				$line .= "\t".$other_image_url8;
				$line .= "\t".$description;
				$line .= "\t".$bullet_point1;
				$line .= "\t".$bullet_point2;
				$line .= "\t".$bullet_point3;
				$line .= "\t".$bullet_point4;
				$line .= "\t".$bullet_point5;
				$line .= "\t".$search_terms1;
				$line .= "\t".$search_terms2;
				$line .= "\t".$search_terms3;
				$line .= "\t".$search_terms4;
				$line .= "\t".$search_terms5;
				$line .= "\t".$size;
				$line .= "\t".$color;
				$line .= "\t".$colormap;
				$line .= "\t".$material;
				$line .= "\t".$item_shape;
				$line .= "\t".$viscosity;
				$line .= "\t".$volume_unit_of_measure;
				$line .= "\t".$volume;
				$line .= "\t".$number_of_holes;
				$line .= "\t".$number_of_grooves;
				$line .= "\t".$oe_manufacturer;
				$line .= "\t".$part_interchange_info;
				$line .= "\t".$manufacturer_warranty_description;
				$line .= "\t".$merchant_catalog_number;
				$line .= "\t".$part_type_id;
				$line .= "\t".$voltage;
				$line .= "\t".$wattage;
				$line .= "\t".$amperage_unit_of_measure;
				$line .= "\t".$amperage;
				$line .= "\t".$memorabilia;
				$line .= "\t".$autographed;
				$line .= "\t".$legal_disclaimer;
				$line .= "\t".$item_package_quantity;
				$line .= "\t".$parent_sku;
				$line .= "\t".$relationship_type;
				$line .= "\t".$display_weight_unit_of_measure;
				$line .= "\t".$display_weight;
				$line .= "\t".$display_volume_unit_of_measure;
				$line .= "\t".$display_volume;
				$line .= "\t".$display_length_unit_of_measure;
				$line .= "\t".$display_length_diameter_unit_of_measure;
				$line .= "\t".$diameter;
				$line .= "\t".$item_weight_unit_of_measure;
				$line .= "\t".$item_weight;
				$line .= "\t".$item_length_unit_of_measure;
				$line .= "\t".$item_length;
				$line .= "\t".$item_height;
				$line .= "\t".$item_width;
				$line .= "\t".$shipping_weight_unit_of_measure;
				$line .= "\t".$shipping_weight;
				$line .= "\t".$registered_parameter;
				$line .= "\t".$launch_date;
				$line .= "\t".$release_date;
				$line .= "\t".$restock_date;
				$line .= "\t".$is_discontinued_by_manufacturer;
				$line .= "\t".$fulfillment_center_id;
				$line .= "\t".$sale_price;
				$line .= "\t".$sale_start_date;
				$line .= "\t".$sale_end_date;
				$line .= "\t".$condition_type;
				$line .= "\t".$condition_note;
				$line .= "\t".$leadtime_to_ship;
				$line .= "\t".$max_aggregate_ship_quantity;
				$line .= "\t".$is_gift_message_available;
				$line .= "\t".$is_giftwrap_available;
				$line .= "\t".$product_tax_code;
				$line .= "\t".$platinum_keywords1;
				$line .= "\t".$platinum_keywords2;
				$line .= "\t".$platinum_keywords3;
				$line .= "\t".$platinum_keywords4;
				$line .= "\t".$platinum_keywords5;
				$line .= "\t".$update_delete;
				fwrite($handle, $line);
			}
		}
		fclose($handle);
//		echo '<div class="success">Export erfolgreich abgespeichert. <a href="csvmanager.csv">Download</a></div>';
		$file="csvmanager.csv";
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		flush();
		readfile($file);
		exit;
	}
	
//	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>