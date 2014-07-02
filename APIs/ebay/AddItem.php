<?php

	//XML error handler
	function HandleXmlError($errno, $errstr, $errfile, $errline)
	{
		error($errfile, $errline, $errno." ".$errstr);
	}

	if ( !isset($_POST["id_auction"]) )
	{
		echo '<AddItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktions-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Auktions-ID übermittelt werden, damit der Service weiß, welche Auktion aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AddItemResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_auctions WHERE id_auction IN (".$_POST["id_auction"].");", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<AddItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktion nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die angegebene Ebay-Auktion konnte nicht gefunden werden. Die Auktions-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AddItemResponse>'."\n";
		exit;
	}
	while( $auction=mysqli_fetch_array($results) )
	{
		//get accountsite
		if( !isset($accountsite) )
		{
			$results2=q("SELECT * FROM ebay_accounts_sites WHERE id_accountsite=".$auction["accountsite_id"].";", $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows($results2)==0 )
			{
				echo '<AddItemResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
				echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</AddItemResponse>'."\n";
				exit;
			}
			$accountsite=mysqli_fetch_array($results2);
		}
		//get account
		if( !isset($account) )
		{
			$results2=q("SELECT * FROM ebay_accounts WHERE id_account=".$accountsite["account_id"].";", $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows($results2)==0 )
			{
				echo '<AddItemResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
				echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</AddItemResponse>'."\n";
				exit;
			}
			$account=mysqli_fetch_array($results2);
		}


	   //generate XML
	   if( !isset($_POST["ReturnXml"]) )
		$payload  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$payload .= '	<AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
	   if( !isset($_POST["ReturnXml"]) )
		$payload .= '		<RequesterCredentials><eBayAuthToken>'.$account["token"].'</eBayAuthToken></RequesterCredentials>';
		$payload .= '		<ErrorLanguage>en_US</ErrorLanguage>'."\n";
		$payload .= '		<WarningLevel>High</WarningLevel>'."\n";
		$payload .= '		<Version>'.$accountsite["Version"].'</Version>'."\n";
		$payload .= '		<MessageID>'.$auction["id_auction"].'</MessageID>'."\n";
		$payload .= '		<Item>'."\n";
	//		$payload .= '			<BestOffer>'.$auction["BestOffer"].'</BestOffer>'."\n";
		$payload .= '			<ConditionID>'.$auction["ConditionID"].'</ConditionID>'."\n";
		$payload .= '			<Country>'.$auction["Country"].'</Country>'."\n";
		$payload .= '			<Currency>'.$auction["Currency"].'</Currency>'."\n";
		$payload .= '			<Description>'.$auction["Description"].'</Description>'."\n";
		$payload .= '			<DiscountPriceInfo>'.$auction["DiscountPriceInfo"].'</DiscountPriceInfo>'."\n";
		$payload .= '			<DispatchTimeMax>'.$accountsite["DispatchTimeMax"].'</DispatchTimeMax>'."\n";
		$payload .= '			'.$auction["ItemCompatibilityList"]."\n";
		$payload .= '			'.$auction["ItemSpecifics"]."\n";
		$payload .= '			<ListingDuration>'.$auction["ListingDuration"].'</ListingDuration>'."\n";
		$payload .= '			<ListingType>'.$auction["ListingType"].'</ListingType>'."\n";
		if( $accountsite["SellerPaymentProfileID"]==0 )
		{
			$PaymentMethods=explode(", ", $accountsite["PaymentMethods"]);
			for($j=0; $j<sizeof($PaymentMethods); $j++)
			{
				//skip "Überweisung" in sandbox mode
				if ( $account["production"]>0 or $PaymentMethods[$j]!="MoneyXferAcceptedInCheckout" )
				{
					$payload .= '	<PaymentMethods>'.$PaymentMethods[$j].'</PaymentMethods>'."\n";
				}
			}
		}
		$payload .= '			<PayPalEmailAddress>'.$accountsite["PayPalEmailAddress"].'</PayPalEmailAddress>'."\n";
		$payload .= '			<PictureDetails>'.$auction["PictureDetails"].'</PictureDetails>'."\n";
		$payload .= '			<PostalCode>'.$accountsite["PostalCode"].'</PostalCode>'."\n";
		$payload .= '			<PrimaryCategory><CategoryID>'.$auction["CategoryID"].'</CategoryID></PrimaryCategory>'."\n";
		$payload .= '			<Quantity>'.$auction["Quantity"].'</Quantity>'."\n";
		if( $accountsite["SellerReturnProfileID"]==0 )
		{
			$payload .= '			<ReturnPolicy>'.$auction["ReturnPolicy"].'</ReturnPolicy>'."\n";
		}
		if($auction["CategoryID2"] != 0 )
		{
			$payload .= '			<SecondaryCategory><CategoryID>'.$auction["CategoryID2"].'</CategoryID></SecondaryCategory>'."\n";
		}
		$payload .= '			<SellerContactDetails>'.$auction["SellerContactDetails"].'</SellerContactDetails>'."\n";
		if( $accountsite["SellerReturnProfileID"]>0 or $accountsite["SellerReturnProfileID"]>0 or $accountsite["SellerShippingProfileID"]>0 )
		{
			$payload .= '			<SellerProfiles>'."\n";
			if( $accountsite["SellerPaymentProfileID"]>0 )
			{
				$payload .= '	<SellerPaymentProfile>'."\n";
				$payload .= '		<PaymentProfileID>'.$accountsite["SellerPaymentProfileID"].'</PaymentProfileID>'."\n";
				$payload .= '	</SellerPaymentProfile>'."\n";
			}
			if( $accountsite["SellerReturnProfileID"]>0 )
			{
				$payload .= '	<SellerReturnProfile>'."\n";
				$payload .= '		<ReturnProfileID>'.$accountsite["SellerReturnProfileID"].'</ReturnProfileID>'."\n";
				$payload .= '	</SellerReturnProfile>'."\n";
			}
			if( $accountsite["SellerFreeShippingProfileID"]>0 and $auction["ShippingServiceCost"]==0 )
			{
				$payload .= '	<SellerShippingProfile>'."\n";
				$payload .= '		<ShippingProfileID>'.$accountsite["SellerFreeShippingProfileID"].'</ShippingProfileID>'."\n";
				$payload .= '	</SellerShippingProfile>'."\n";
			}
			elseif( $accountsite["SellerShippingProfileID"]>0 )
			{
				$payload .= '	<SellerShippingProfile>'."\n";
				$payload .= '		<ShippingProfileID>'.$accountsite["SellerShippingProfileID"].'</ShippingProfileID>'."\n";
				$payload .= '	</SellerShippingProfile>'."\n";
			}
			$payload .= '			</SellerProfiles>'."\n";
		}
		if( $accountsite["SellerShippingProfileID"]==0 )
		{
			$payload .= '			<ShippingDetails>'.$auction["ShippingDetails"].'</ShippingDetails>'."\n";
		}
		$payload .= '			<ShippingPackageDetails>'."\n";
		$payload .= '				<WeightMajor>'.$auction["ShippingPackageDetailsWeightMajor"].'</WeightMajor>'."\n";
		$payload .= '				<WeightMinor>'.$auction["ShippingPackageDetailsWeightMinor"].'</WeightMinor>'."\n";
		$payload .= '			</ShippingPackageDetails>'."\n";
		$payload .= '			<Site>'.$accountsite["Site"].'</Site>'."\n";
		$payload .= '			<SKU>'.$auction["SKU"].'</SKU>'."\n";
		$payload .= '			<StartPrice currencyID="'.$accountsite["currencyID"].'">'.$auction["StartPrice"].'</StartPrice>'."\n";
		$payload .= '			<SubTitle>'.$auction["SubTitle"].'</SubTitle>'."\n";
		if($auction["StoreCategoryID"]!=0)
		{
		$payload .= '			<Storefront>'."\n";
		if($auction["StoreCategoryID"]!=0)
		$payload .= '				<StoreCategoryID>'.$auction["StoreCategoryID"].'</StoreCategoryID>'."\n";
		if($auction["StoreCategory2ID"]!=0)
		$payload .= '				<StoreCategory2ID>'.$auction["StoreCategory2ID"].'</StoreCategory2ID>'."\n";
		$payload .= '			</Storefront>'."\n";
		}
		$payload .= '			<Title><![CDATA['.$auction["Title"].']]></Title>'."\n";
		$payload .= '			<VATDetails>';
		$payload .= '				<BusinessSeller>false</BusinessSeller>';
		$payload .= '				<RestrictedToBusiness>false</RestrictedToBusiness>';
		$payload .= '				<VATPercent>19</VATPercent>';
		$payload .= '			</VATDetails>';
		$payload .= '		</Item>'."\n";
		$payload .= '	</AddItemRequest>'."\n";
	}

	if( isset($_POST["ReturnXml"]) )
	{
		echo $payload;
		exit;
	}


	//submit auction
	$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "AddItem", "id_accountsite" => $accountsite["id_accountsite"], "request" => $payload));
	
	//get errors and warnings
	set_error_handler('HandleXmlError');
	$dom = new DOMDocument();
	$dom->loadXml($responseXml);    
	restore_error_handler();
	$errors = $dom->getElementsByTagName('Errors');
	//save errors in error db
	for($i=0; $i<$errors->length; $i++)
	{
		$code     = $errors->item($i)->getElementsByTagName('ErrorCode');
		$shortMsg = $errors->item($i)->getElementsByTagName('ShortMessage');
		$longMsg  = $errors->item($i)->getElementsByTagName('LongMessage');
		q("INSERT INTO cms_errors (errortype_id, error_id, file, line, text, time) VALUES(2, ".$code->item(0)->nodeValue.", '".mysqli_real_escape_string($dbweb,__FILE__)."', ".__LINE__.", '".mysqli_real_escape_string($dbweb, $shortMsg->item(0)->nodeValue)."', ".time().");", $dbweb, __FILE__, __LINE__);
	}

	//get ItemID
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<AddItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Antwort von eBay fehlerhaft.</shortMsg>'."\n";
		echo '		<longMsg>Beim Abrufen der Serverantwort von eBay ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AddItemResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	if( $response->Ack[0]!="Success" and $response->Ack[0]!="Warning" )
	{
		q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
		echo '<AddItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Hochladen der Auktion fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg><![CDATA['.$responseXml.']]></longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AddItemResponse>'."\n";
		exit;
	}
	$ItemID = $response->ItemID[0];
	
	//update ebay_auctions
	q("UPDATE ebay_auctions SET upload=0, responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."', ItemID='".$ItemID."', lastupdate=".time()." WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);


	//return success
	echo '<AddItemResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</AddItemResponse>'."\n";

?>