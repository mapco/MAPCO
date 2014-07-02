<?php

	if ( !isset($_POST["id_auction"]) and !isset($_POST["ItemID"]) )
	{
		echo '<EndItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktions-ID oder Auktionsnummer fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Auktions-ID oder Auktionsnummer übermittelt werden, damit der Service weiß, welche Auktion aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EndItemResponse>'."\n";
		exit;
	}

	if( isset($_POST["id_auction"]) )
	{
		$results=q("SELECT * FROM ebay_auctions WHERE id_auction IN (".$_POST["id_auction"].");", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			echo '<EndItemResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Auktion nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Die angegebene Ebay-Auktion konnte nicht gefunden werden. Die Auktions-ID scheint es nicht zu geben.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</EndItemResponse>'."\n";
			exit;
		}
	}
	else
	{
		$results=q("SELECT * FROM ebay_auctions WHERE ItemID=".$_POST["ItemID"].";", $dbshop, __FILE__, __LINE__);
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

		if( $auction["ItemID"]==0 )
		{
			q("DELETE FROM ebay_auctions WHERE id_auction=".$auction["id_auction"].";", $dbshop, __FILE__, __LINE__);
			if( !isset($_POST["ReturnXml"]) )
			{
				echo '<EndItemResponse>'."\n";
				echo '	<Ack>Success</Ack>'."\n";
				echo '</EndItemResponse>'."\n";
				exit;
			}
		}
		else
		{
			//submit EndItem
			if( !isset($_POST["ReturnXml"]) )
			{
				$payload  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			}
			$payload .= '<EndItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
			if( !isset($_POST["ReturnXml"]) )
			{
				if ( $account["production"]==0 )
				{
					$payload .= '<RequesterCredentials><eBayAuthToken>'.$account["token_sandbox"].'</eBayAuthToken></RequesterCredentials>'."\n";
				}
				else
				{
					$payload .= '<RequesterCredentials><eBayAuthToken>'.$account["token"].'</eBayAuthToken></RequesterCredentials>'."\n";
				}
			}
			$payload .= '	<EndingReason>NotAvailable</EndingReason>'."\n";
			$payload .= '	<ErrorLanguage>de_DE</ErrorLanguage>'."\n";
			$payload .= '	<ItemID>'.$auction["ItemID"].'</ItemID>'."\n";
			$payload .= '	<MessageID>'.$auction["id_auction"].'</MessageID>'."\n";
			$payload .= '	<Version>'.$accountsite["Version"].'</Version>'."\n";
			$payload .= '	<WarningLevel>High</WarningLevel>'."\n";
			$payload .= '</EndItemRequest>'."\n";
		}
	}

	if( isset($_POST["ReturnXml"]) )
	{
		echo $payload;
		exit;
	}


	//submit auction
	$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "EndItem", "id_accountsite" => $accountsite["id_accountsite"], "request" => $payload));

	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<EndItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Antwort von eBay fehlerhaft.</shortMsg>'."\n";
		echo '		<longMsg>Beim Abrufen der Serverantwort von eBay ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EndItemResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	//EndItem successful when auction has already been ended
	if( $response->Errors[0]->ErrorCode[0]=="1047" )
	{
		q("DELETE FROM ebay_auctions WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
		echo '<EndItemResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</EndItemResponse>'."\n";
		exit;
	}

	//wrong account fix
	if( $response->Errors[0]->ErrorCode[0]=="300" )
	{
		$id_auction=$response->CorrelationID[0];
		if( $account["id_account"]==1 ) $account["id_account"]=2;
		elseif( $account["id_account"]==2 ) $account["id_account"]=8;
		else $account["id_account"]=1;
		q("UPDATE ebay_auctions SET account_id=".$account["id_account"].", upload=1 WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
		echo '<EndItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account berichtigt.</shortMsg>'."\n";
		echo '		<longMsg>Die Auktion ('.$id_auction.') wurde ursprünglich mit der falschen Account-ID in der Datenbank gespeichert und nun korrigiert.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EndItemResponse>'."\n";
		exit;
	}

	if( $response->Ack[0]!="Success" and $response->Ack[0]!="Warning" )
	{
		if( isset($_POST["id_auction"]) )
		{
			q("UPDATE ebay_auctions SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."' WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
		}
		echo '<EndItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Beenden der Auktion fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg><![CDATA['.$responseXml.']]></longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EndItemResponse>'."\n";
		exit;
	}
	
	//update table
	if( isset($_POST["id_auction"]) )
	{
		q("DELETE FROM ebay_auctions WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
	}

	//return success
	echo '<EndItemResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</EndItemResponse>'."\n";

?>