<?php
	if ( !isset($_POST["id_order"]) )
	{
		echo '<SetShipmentTrackingInfoResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Bestellnummer (id_order) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SetShipmentTrackingInfoResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<SetShipmentTrackingInfoResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Bestellung mit der Nummer '.$_POST["id_order"].' konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SetShipmentTrackingInfoResponse>'."\n";
		exit;
	}
	$order=mysqli_fetch_array($results);

	$results=q("SELECT * FROM shop_shops WHERE id_shop=".$order["shop_id"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<SetShipmentTrackingInfoResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der in der Bestellung angegebene Shop (shop_id) ist ungültig.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SetShipmentTrackingInfoResponse>'."\n";
		exit;
	}
	$shop=mysqli_fetch_array($results);
	if( $shop["shop_type"]!=2 )
	{
		echo '<SetShipmentTrackingInfoResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop ist kein eBay-Shop.</shortMsg>'."\n";
		echo '		<longMsg>Der in der Bestellung angegebene Shop (shop_id) ist kein eBay-Shop.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SetShipmentTrackingInfoResponse>'."\n";
		exit;
	}
	
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$shop["account_id"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<SetShipmentTrackingInfoResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>eBay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der im Shop (shop_id) angegebene eBay-Account (account_id) ist nicht gültig.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SetShipmentTrackingInfoResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);


	//create XML
	$requestXmlBody  = '<?xml version="1.0" encoding="UTF-8"?>';
	$requestXmlBody .= '	<CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
	$requestXmlBody .= '	<RequesterCredentials>';
	$requestXmlBody .= '		<eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
	$requestXmlBody .= '	</RequesterCredentials>';
	$requestXmlBody .= '	<WarningLevel>High</WarningLevel>';
	$requestXmlBody .= '	<OrderID>'.$order["foreign_OrderID"].'</OrderID>';
	$requestXmlBody .= '	<Shipment>';
	$requestXmlBody .= '		<ShipmentTrackingDetails>';
	$requestXmlBody .= '			<ShipmentTrackingNumber>'.$order["shipping_number"].'</ShipmentTrackingNumber>';
	$requestXmlBody .= '			<ShippingCarrierUsed>DHL</ShippingCarrierUsed>';
	$requestXmlBody .= '		</ShipmentTrackingDetails>';
	$requestXmlBody .= '	</Shipment>';
	$requestXmlBody .= '	<Shipped>true</Shipped>';
	$requestXmlBody .= '</CompleteSaleRequest>';
	
	//submit XML
	$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "CompleteSale", "id_account" => $account["id_account"], "request" => $requestXmlBody));
	
	echo $responseXml;
	
	exit;
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<SetShipmentTrackingInfoResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Antwort von eBay fehlerhaft.</shortMsg>'."\n";
		echo '		<longMsg>Beim Abrufen der Serverantwort von eBay ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SetShipmentTrackingInfoResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if( $response->Ack[0]!="Success" and $response->Ack[0]!="Warning" )
	{
		if( isset($_POST["id_auction"]) )
		{
			q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
		}
		echo '<SetShipmentTrackingInfoResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Beenden der Auktion fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg><![CDATA['.$responseXml.']]></longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SetShipmentTrackingInfoResponse>'."\n";
		exit;
	}
	
	//return success
	echo '<SetShipmentTrackingInfoResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</SetShipmentTrackingInfoResponse>'."\n";

?>