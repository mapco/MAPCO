<?php

	if ( !isset($_POST["PromotionalSaleID"]) )
	{
		echo '<GetPromotionalSaleDetailsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>PromotionalSaleID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine PromotionalSaleID übermittelt werden, damit der Service weiß, welche Promotion mit dem Artikel verlinkt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetPromotionalSaleDetailsResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_account"]) )
	{
		echo '<GetPromotionalSaleDetailsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, auf welchem Account die Promotion-Aktion eingestellt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetPromotionalSaleDetailsResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	$account=mysqli_fetch_array($results);


	if ( !isset($_POST["id_item"]) )
	{
		echo '<GetPromotionalSaleDetailsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Item-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Item-ID übermittelt werden, damit der Service weiß, welche Auktionen mit der Promotion verlinkt werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetPromotionalSaleDetailsResponse>'."\n";
		exit;
	}
	
	$requestXmlBody='
		<?xml version="1.0" encoding="utf-8"?>
		<SetPromotionalSaleListingsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
		  <RequesterCredentials>
			<eBayAuthToken>'.$account["token"].'</eBayAuthToken>
		  </RequesterCredentials>
		  <Action>Add</Action>
		  <PromotionalSaleID>'.$_POST["PromotionalSaleID"].'</PromotionalSaleID>
		  <PromotionalSaleItemIDArray>';
		  $results=q("SELECT * FROM ebay_auctions WHERE shopitem_id=".$_POST["id_item"]." AND account_id=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
		  while( $row=mysqli_fetch_array($results) )
		  {
			$requestXmlBody.='<ItemID>'.$row["ItemID"].'</ItemID>';
		  }
	$requestXmlBody.='
		  </PromotionalSaleItemIDArray>
		  <AllFixedPriceItems>true</AllFixedPriceItems>
		  <AllStoreInventoryItems>true</AllStoreInventoryItems>
		</SetPromotionalSaleListingsRequest>';


	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "SetPromotionalSaleListings", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>