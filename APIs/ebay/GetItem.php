<?php
	if ( isset($_POST["id_auction"]) )
	{
		$results=q("SELECT ItemID FROM ebay_auctions WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<GetItemResponse>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Auktions-ID (id_auction) nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine Auktions-ID (id_auction) übergeben werden, damit der Service weiß, welche Daten abgerufen werden sollen.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GetItemResponse>'."\n";
			exit;
		}
		$row=mysqli_fetch_array($results);
		$_POST["ItemID"]=$row["ItemID"];
	}

	if ( !isset($_POST["ItemID"]) or $_POST["ItemID"]=="" )
	{
		echo '<GetItemResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktionsnummer (ItemID) nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Auktionsnummer (ItemID) übergeben werden, damit der Service weiß, welche Daten abgerufen werden sollen.</longMsg>'."\n";
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
	
	if( !isset($_POST["DetailLevel"]) ) $_POST["DetailLevel"]="ReturnAll";

	$requestXmlBody='
		<?xml version="1.0" encoding="utf-8"?>
		<GetItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
		  <RequesterCredentials>
			<eBayAuthToken>'.$account["token"].'</eBayAuthToken>
		  </RequesterCredentials>
		  <DetailLevel>'.$_POST["DetailLevel"].'</DetailLevel>
		  <ItemID>'.($_POST["ItemID"]*1).'</ItemID>
		</GetItemRequest>
	';

	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetItem", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>