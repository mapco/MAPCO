<?php

	//XML error handler
	function HandleXmlError($errno, $errstr, $errfile, $errline)
	{
		error($errfile, $errline, $errno." ".$errstr);
	}

	if ( !isset($_POST["id_auction"]) )
	{
		echo '<ReviseItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktions-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Auktions-ID übermittelt werden, damit der Service weiß, welche Auktion aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ReviseItemResponse>'."\n";
		exit;
	}
	

	$results=q("SELECT * FROM ebay_auctions WHERE id_auction IN (".$_POST["id_auction"].");", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ReviseItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktion nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die angegebene Ebay-Auktion konnte nicht gefunden werden. Die Auktions-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ReviseItemResponse>'."\n";
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
		$payload .= '	<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
	   if( !isset($_POST["ReturnXml"]) )
		$payload .= '		<RequesterCredentials><eBayAuthToken>'.$account["token"].'</eBayAuthToken></RequesterCredentials>';
		$payload .= '		<DeletedField>Item.ListingDetails.BestOfferAutoAcceptPrice</DeletedField>';
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
		$payload .= '			<ItemID>'.$auction["ItemID"].'</ItemID>'."\n";
		$payload .= '			'.$auction["ItemSpecifics"]."\n";
		$payload .= '			<ListingDuration>'.$auction["ListingDuration"].'</ListingDuration>'."\n";
		$payload .= '			<ListingType>'.$auction["ListingType"].'</ListingType>'."\n";
		if( $accountsite["SellerPaymentProfileID"]==0 )
		{
			$PaymentMethods=explode(", ", $accountsite["PaymentMethods"]);
			for($j=0; $j<sizeof($PaymentMethods); $j++)
			{
				//skip "Überweisung" in sandbox mode
				if ( $accountsite["production"]>0 or $PaymentMethods[$j]!="MoneyXferAcceptedInCheckout" )
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
		if( $auction["QuantitySold"]==0 )
		{
			$payload .= '			<SubTitle>'.$auction["SubTitle"].'</SubTitle>'."\n";
		}
		if($auction["StoreCategoryID"]!=0)
		{
			$payload .= '			<Storefront>'."\n";
			if($auction["StoreCategoryID"]!=0)
			$payload .= '				<StoreCategoryID>'.$auction["StoreCategoryID"].'</StoreCategoryID>'."\n";
			if($auction["StoreCategory2ID"]!=0)
			$payload .= '				<StoreCategory2ID>'.$auction["StoreCategory2ID"].'</StoreCategory2ID>'."\n";
			$payload .= '			</Storefront>'."\n";
		}
		if( $auction["QuantitySold"]==0 )
		{
			$payload .= '			<Title><![CDATA['.$auction["Title"].']]></Title>'."\n";
		}
		$payload .= '			<VATDetails>';
		$payload .= '				<BusinessSeller>false</BusinessSeller>';
		$payload .= '				<RestrictedToBusiness>false</RestrictedToBusiness>';
		$payload .= '				<VATPercent>19</VATPercent>';
		$payload .= '			</VATDetails>';
		$payload .= '		</Item>'."\n";
		$payload .= '	</ReviseItemRequest>'."\n";
	}

	if( isset($_POST["ReturnXml"]) )
	{
		echo $payload;
		exit;
	}


	//submit auction
	$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "ReviseItem", "id_accountsite" => $accountsite["id_accountsite"], "request" => $payload));
	
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<ReviseItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Antwort von eBay fehlerhaft.</shortMsg>'."\n";
		echo '		<longMsg>Beim Abrufen der Serverantwort von eBay ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</ReviseItemResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	//update ebay_auctions
	q("UPDATE ebay_auctions SET upload=0, responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."', lastupdate=".time()." WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);

	//response evaluation
	$fieldset=array();
	$fieldset["API"]="ebay";
	$fieldset["Action"]="ResponseEvaluateReviseItem";
	$fieldset["XML"]=$responseXml;
	$responseXml=post(PATH."soa/", $fieldset);

	//return success
	echo '<ReviseItemResponse>'."\n";
	if( strpos($responseXml, "<Error") !== false )
	{
		echo '	<Ack>Failure</Ack>'."\n";
	}
	else
	{
		echo '	<Ack>Success</Ack>'."\n";
	}
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</ReviseItemResponse>'."\n";
/*
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
		q("INSERT INTO cms_errors (errortype_id, error_id, file, line, text, time) VALUES(2, ".$code->item(0)->nodeValue.", '".mysqli_real_escape_string($dbweb, __FILE__)."', ".__LINE__.", '".mysqli_real_escape_string($dbweb, $shortMsg->item(0)->nodeValue)."', ".time().");", $dbweb, __FILE__, __LINE__);
	}

	if ( strpos($responseXml, '<Ack>Success</Ack>') === false and strpos($responseXml, '<Ack>Warning</Ack>') === false )
	{
		//update ebay_auctions
		q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
	

	

		//if there are error nodes
		if( $errors->length>0 )
		{
			//Get error code, ShortMesaage and LongMessage
			$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
			$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
			$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');
			if ($code->item(0)->nodeValue==17)
			{
	//			q("DELETE FROM ebay_auctions WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
	//				echo '<br /><br />Auf Auktion kann nicht zugegriffen werden. Auktion gelöscht.<br /><br />';
			}
			//The item cannot be listed or modified.
			elseif ($code->item(0)->nodeValue==240)
			{
				//mail code and messages
				$header  = 'MIME-Version: 1.0' . "\r\n";
				$header .= 'Content-type: text/html; charset=utf-8' . "\r\n";
				$header .= 'From: Server <info@mapco.de>'. "\r\n";
				$subject = 'eBay-Error '.$code->item(0)->nodeValue.' '.$shortMsg->item(0)->nodeValue;
				$msg  = '<p>Auktions-ID: '.$auction["id_auction"].'</p>';
				$msg .= '<p>Artikelnr : '.$artnr.'</p>';
				$msg .= '<p>Item-ID : '.$id_item.'</p>';
				$msg .= '<p>Account-ID : '.$id_account.'</p>';
				$msg .= '<p>eBay-Fehlernummer: '.$code->item(0)->nodeValue.'</p>';
				$msg .= '<p>'.$shortMsg->item(0)->nodeValue.'</p>';
				$msg .= '<p>'.$longMsg->item(0)->nodeValue.'</p>';
				$msg .= '<br /><br /><p>'.nl2br(htmlentities($_POST["request"])).'</p>';
			//	mail("developer@mapco.de", $subject, $msg, $header);
//				echo '<br /><br />Fehler 240 erkannt. Administrator wurde informiert.<br /><br />';
			}
			// Auction ended. You are not allowed to revise ended auctions.
			elseif ($code->item(0)->nodeValue==291)
			{
				q("DELETE FROM ebay_auctions WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			// Invalid auction listing type. The auction listing type is not valid. Please see the API documentation for valid types.
			elseif ($code->item(0)->nodeValue==302)
			{
				//ignore
				q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			// Call usage limit has been reached. Your application has exceeded usage limit on this call, please make call to GetAPIAccessRules to check your call usage.
			elseif ($code->item(0)->nodeValue==518) 
			{
				//ignore
				q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			// The auction has been closed. The auction has already been closed.
			elseif ($code->item(0)->nodeValue==1047)
			{
				q("DELETE FROM ebay_auctions WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			// Listing cannot be revised. The title or subtitle cannot be changed if an auction-style listing has a bid or ends within 12 hours, or a fixed price listing has a sale or a pending Best Offer.
			elseif ($code->item(0)->nodeValue==10039)
			{
				//ignore
				q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			// All compatabilities invalid. All compatibilities are invalid, Item not listed.
			elseif ($code->item(0)->nodeValue==21917122)
			{
				//ignore
				q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			// Invalid compatibility combination(s). One or more compatibility combinations are invalid. Name, value, or name-value pair are not recognized. replaceable_value
			elseif ($code->item(0)->nodeValue==21916724 )
			{
				//mail code and messages
				$header  = 'MIME-Version: 1.0' . "\r\n";
				$header .= 'Content-type: text/html; charset=utf-8' . "\r\n";
				$header .= 'From: Server <info@mapco.de>'. "\r\n";
				$subject = 'eBay-Error '.$code->item(0)->nodeValue.' '.$shortMsg->item(0)->nodeValue;
				$msg  = '<p>Call: '.$_POST["Call"].'</p>';
				$msg .= '<p>Artikelnr : '.$auction["SKU"].'</p>';
				$msg .= '<p>Item-ID : '.$auction["shopitem_id"].'</p>';
				$msg .= '<p>Account-ID : '.$_POST["id_account"].'</p>';
				$msg .= '<p>eBay-Fehlernummer: '.$code->item(0)->nodeValue.'</p>';
				$msg .= '<p>'.$shortMsg->item(0)->nodeValue.'</p>';
				$msg .= '<p>'.$longMsg->item(0)->nodeValue.'</p>';
				$msg .= '<p>'.nl2br(htmlentities($responseXml)).'</p>';
				$msg .= '<br /><br /><p>'.nl2br(htmlentities($_POST["request"])).'</p>';
			//	mail("developer@mapco.de", $subject, $msg, $header);
			}
			// Item compatibilities cannot be revised. The item compatibilities cannot be changed or removed if an auction-style listing has a bid or ends within 12 hours, or a fixed price listing has a pending Best Offer.
			elseif ($code->item(0)->nodeValue==21916730 )
			{
				//ignore
				q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			// Dimensions of the picture you uploaded are smaller than recommended. To reduce possible issues with picture display quality, eBay recommends that pictures you upload are replaceable_value pixels or larger on the longest side.
			elseif ($code->item(0)->nodeValue==21916790 )
			{
				// ignore
			}
			// Quality value of the JPEG format picture you uploaded is lower than recommended. To reduce possible issues with picture display quality, eBay recommends that pictures you upload have a JPEG quality value of replaceable_value or greater.
			elseif ($code->item(0)->nodeValue==21916791 )
			{
				//ignore
				q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			// Portions of this listing cannot be revised if the item has bid or active Best Offers or is ending in 12 hours. 
			elseif ($code->item(0)->nodeValue==21919028 )
			{
				q("UPDATE ebay_auctions SET QuantitySold=1, responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			//  Category change denied. You cannot change the category for this item.
			elseif ($code->item(0)->nodeValue==21919128 )
			{
				//ignore
				q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			// Maximum Call Limit Exceeded. You have exceeded your maximum call limit of replaceable_value for replaceable_value. Try back after replaceable_value.
			elseif ($code->item(0)->nodeValue==21919144) 
			{
				//ignore
				q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				//mail code and messages
				$header  = 'MIME-Version: 1.0' . "\r\n";
				$header .= 'Content-type: text/html; charset=utf-8' . "\r\n";
				$header .= 'From: Server <info@mapco.de>'. "\r\n";
				$subject = 'eBay-Error '.$code->item(0)->nodeValue.' '.$shortMsg->item(0)->nodeValue;
				$msg  = '<p>Call: '.$_POST["Call"].'</p>';
				$msg .= '<p>Artikelnr : '.$auction["SKU"].'</p>';
				$msg .= '<p>Item-ID : '.$auction["shopitem_id"].'</p>';
				$msg .= '<p>Account-ID : '.$_POST["id_account"].'</p>';
				$msg .= '<p>eBay-Fehlernummer: '.$code->item(0)->nodeValue.'</p>';
				$msg .= '<p>'.$shortMsg->item(0)->nodeValue.'</p>';
				$msg .= '<p>'.$longMsg->item(0)->nodeValue.'</p>';
				$msg .= '<p>'.nl2br(htmlentities($responseXml)).'</p>';
				$msg .= '<br /><br /><p>'.nl2br(htmlentities($_POST["request"])).'</p>';
			//	mail("developer@mapco.de", $subject, $msg, $header);
			}
		}
	
		//return failure
		echo '<ReviseItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
//		echo '	<Request><![CDATA['.$requestXmlBody.']]></Request>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</ReviseItemResponse>'."\n";
	}
	else
	{
		//update ebay_auctions
		q("UPDATE ebay_auctions SET upload=0, responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."', lastupdate=".time()." WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
	
	
		//return success
		echo '<ReviseItemResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</ReviseItemResponse>'."\n";
	}
*/
?>