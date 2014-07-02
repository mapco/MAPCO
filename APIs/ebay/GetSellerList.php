<?php
	set_time_limit(60);

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

	if ( !isset($_POST["EndTimeFrom"]) )
	{
		$time=time()-300;
		$_POST["EndTimeFrom"]=gmdate("Y-m-d", $time)."T".gmdate("H:i:s.000", $time)."Z";
	}
	if ( !isset($_POST["EndTimeTo"]) )
	{
		$time=time()+3600*24*31;
		$_POST["EndTimeTo"]=gmdate("Y-m-d", $time)."T".gmdate("H:i:s.000", $time)."Z";
	}
	if( !isset($_POST["EntriesPerPage"]) ) $_POST["EntriesPerPage"]=200;
	if( !isset($_POST["PageNumber"]) ) $_POST["PageNumber"]=1;

/*
			<OutputSelector>PaginationResult.TotalNumberOfPages</OutputSelector> 
			<OutputSelector>PaginationResult.TotalNumberOfEntries</OutputSelector> 
			<OutputSelector>ItemsPerPage</OutputSelector> 
			<OutputSelector>ReturnedItemCountActual</OutputSelector> 
			<OutputSelector>PageNumber</OutputSelector> 
*/
	$requestXmlBody='
		<?xml version="1.0" encoding="utf-8"?>
			<GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">
			<RequesterCredentials>
				<eBayAuthToken>'.$account["token"].'</eBayAuthToken>
			</RequesterCredentials>
			<ErrorLanguage>en_US</ErrorLanguage>
			<GranularityLevel>Coarse</GranularityLevel> 
			<OutputSelector>'.$_POST["OutputSelector"].'</OutputSelector>
  			<Pagination> 
				<EntriesPerPage>'.$_POST["EntriesPerPage"].'</EntriesPerPage> 
				<PageNumber>'.$_POST["PageNumber"].'</PageNumber>
			</Pagination> 
			<WarningLevel>High</WarningLevel>
			<EndTimeFrom>'.$_POST["EndTimeFrom"].'</EndTimeFrom> 
			<EndTimeTo>'.$_POST["EndTimeTo"].'</EndTimeTo>
			<IncludeWatchCount>true</IncludeWatchCount>
		</GetSellerListRequest>
	';
	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetSellerList", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>