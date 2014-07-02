<?php
	if ( !isset($_POST["ItemID"]) )
	{
		echo '<GetItemResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktions-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Auktions-ID übergeben werden, damit der Service weiß, welche Daten abgerufen werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetItemResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_account"]) )
	{
		echo '<GetItemResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetItemResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<GetItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetItemResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	$requestXmlBody='
		<?xml version="1.0" encoding="utf-8"?>
		<getBestMatchItemDetailsRequest xmlns="http://www.ebay.com/marketplace/search/v1/services">
		  <itemId>'.($_POST["ItemID"]*1).'</itemId>
		</getBestMatchItemDetailsRequest>
	';
	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "getBestMatchItemDetails", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>