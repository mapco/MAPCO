<?php

	if ( !isset($_POST["PageNumber"]) )
	{
		echo '<GetBestOffersResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Seitennummer nicht gefunden (Ebay-Pagination).</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine PageNumber übergeben werden, damit der Service weiß, welche RespondPage aufgerufen werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetBestOffersResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_account"]) )
	{
		echo '<GetBestOffersResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetBestOffersResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<GetBestOffersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetBestOffersResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	//GET VERSION FOR ACCOUNT
	$res_version = q("SELECT * FROM ebay_accounts_sites WHERE account_id = ".$_POST["id_account"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_version)==0)
	{
		$OK = false;	
		show_error(9810,6, __FILE__, __LINE__, "Account ID: ".$account["id_account"]);
		exit;
	}
	$row_version = mysqli_fetch_assoc($res_version);


	$requestXmlBody='
		<?xml version="1.0" encoding="utf-8"?>
		<GetBestOffersRequest xmlns="urn:ebay:apis:eBLBaseComponents">
		  <RequesterCredentials>
			<eBayAuthToken>'.$account["token"].'</eBayAuthToken>
		  </RequesterCredentials>
		  <BestOfferStatus>Active</BestOfferStatus>
		  <Pagination>
			<EntriesPerPage>200</EntriesPerPage>
			<PageNumber>'.$_POST["PageNumber"].'</PageNumber>
		  </Pagination>
		  <Version>'.$row_version["Version"].'</Version>
		  <DetailLevel>ReturnAll</DetailLevel>
		  <WarningLevel>High</WarningLevel>
		</GetBestOffersRequest>
	';
	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetBestOffers", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));
	

?>