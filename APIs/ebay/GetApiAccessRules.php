<?php
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

	//get info
	if ( $account["production"]==0 ) $token=$account["token_sandbox"]; else $token=$account["token"];
	$requestXmlBody='
		<?xml version="1.0" encoding="utf-8"?>
		<GetApiAccessRulesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
			<RequesterCredentials>
				<eBayAuthToken>'.$token.'</eBayAuthToken>
			</RequesterCredentials>		  <ErrorLanguage>de_DE</ErrorLanguage>
			<Version>'.$account["Version"].'</Version>
			<WarningLevel>High</WarningLevel>
		</GetApiAccessRulesRequest>
	';
	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetApiAccessRules", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));
	exit;

	//view
	$response = new SimpleXMLElement($responseXml);
	echo '<table>';
	echo '	<tr>';
	echo '		<th></th>';
	echo '		<th>Täglich</th>';
	echo '		<th>Stündlich</th>';
	echo '		<th>Periodisch</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Aufrufe</td>';
	echo '		<td>'.number_format($response->ApiAccessRule[0]->DailyUsage[0], 0, ",", ".").'</td>';
	echo '		<td>'.number_format($response->ApiAccessRule[0]->HourlyUsage[0], 0, ",", ".").'</td>';
	echo '		<td>'.number_format($response->ApiAccessRule[0]->PeriodicUsage[0], 0, ",", ".").'</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Limit</td>';
	echo '		<td>'.number_format($response->ApiAccessRule[0]->DailyHardLimit[0], 0, ",", ".").'</td>';
	echo '		<td>'.number_format($response->ApiAccessRule[0]->HourlyHardLimit[0], 0, ",", ".").'</td>';
	echo '		<td>'.number_format($response->ApiAccessRule[0]->PeriodicHardLimit[0], 0, ",", ".").'</td>';
	echo '	</tr>';
	echo '</table>';
?>