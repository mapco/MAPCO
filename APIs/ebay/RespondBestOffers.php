<?php

	if ( !isset($_POST["BestOfferAction"]) || $_POST["BestOfferAction"]=="")
	{
		echo '<RespondBestOffersResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>BestOfferAction nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss angegeben werden ob ein Preisvorschlag angenommen wird (Accept) oder ein GegenVorschlag gesendet wird (Counter)</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</RespondBestOffersResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["BestOfferID"]) )
	{
		echo '<RespondBestOffersResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>BestOffer ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine auf den Preisvorschlag referenzierende BestOfferID angegeben werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</RespondBestOffersResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["DiscountedPrice"]) &&  $_POST["BestOfferAction"]=="Counter")
	{
		echo '<RespondBestOffersResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Preisvorschlag (Preis) nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Preis übergeben werden, der dem Käufer angezeigt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</RespondBestOffersResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["DiscountedPriceQty"]) &&  $_POST["BestOfferAction"]=="Counter")
	{
		echo '<RespondBestOffersResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Anzahl der Artikel zum Preisvorschlag nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Anzahl der Artikel übergeben werden, für die der Preisvorschlag gilt.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</RespondBestOffersResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["ItemID"]) )
	{
		echo '<RespondBestOffersResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay Artikelnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Ebay Artikelnummer übergeben werden, auf die sich der Preisvorschlag bezieht.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</RespondBestOffersResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_account"]) )
	{
		echo '<RespondBestOffersResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</RespondBestOffersResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<RespondBestOffersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</RespondBestOffersResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	$requestXmlBody ='<?xml version="1.0" encoding="utf-8"?>';
	$requestXmlBody.='<RespondToBestOfferRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
	$requestXmlBody.='	  <RequesterCredentials>';
	$requestXmlBody.='		<eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
	$requestXmlBody.='	  </RequesterCredentials>';
	$requestXmlBody.='	  <Action>'.$_POST["BestOfferAction"].'</Action>';
	$requestXmlBody.='	  <BestOfferID>'.$_POST["BestOfferID"].'</BestOfferID>';
	if ($_POST["BestOfferAction"]=="Counter")
	{
		$requestXmlBody.='	<CounterOfferPrice currencyID="EUR">'.($_POST["DiscountedPrice"]*1).'</CounterOfferPrice>';
		$requestXmlBody.='	<CounterOfferQuantity>'.($_POST["DiscountedPriceQty"]*1).'</CounterOfferQuantity>';
	}
	$requestXmlBody.='<ItemID>'.$_POST["ItemID"].'</ItemID>';
	if (isset($_POST["message"]) && $_POST["message"]!="")
	{	
		$requestXmlBody.='	<SellerResponse>'.$_POST["message"].'</SellerResponse>';
	}
	$requestXmlBody.='	<Version>'.$account["Version"].'</Version>';
	$requestXmlBody.='	<WarningLevel>High</WarningLevel>';
	$requestXmlBody.='</RespondToBestOfferRequest>';
			
	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "RespondToBestOffer", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));
	

?>