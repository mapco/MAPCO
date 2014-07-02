<?php

	if ( !isset($_POST["id_account"]) )
	{
		echo '<GetPromotionalSaleDetailsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, welche Promotion-Aktionen abgerufen werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetPromotionalSaleDetailsResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	$account=mysqli_fetch_array($results);

	$requestXmlBody='
	<?xml version="1.0" encoding="utf-8"?>
<GetPromotionalSaleDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
   <RequesterCredentials>
      <eBayAuthToken>'.$account["token"].'</eBayAuthToken>
   </RequesterCredentials>
   <ErrorLanguage>en_US</ErrorLanguage>
  <Version>707</Version>
  <WarningLevel>High</WarningLevel>
  <PromotionalSaleStatus>Active</PromotionalSaleStatus>
  <PromotionalSaleStatus>Scheduled</PromotionalSaleStatus>
  <PromotionalSaleStatus>Processing</PromotionalSaleStatus>
</GetPromotionalSaleDetailsRequest>
';

	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetPromotionalSaleDetails", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>