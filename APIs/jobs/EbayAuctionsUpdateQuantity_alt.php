<?php
	include("../../mapco_shop_de/config.php");
		//session required	
	session_start();
	$_SESSION["id_user"]=1;
	$_SESSION["userrole_id"]=1;
	$starttime=time();

	if ( !isset($_POST["id_account"]) )
	{
		echo '<EbayGetOrdersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, welche Auktion aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EbayGetOrdersResponse>'."\n";
		exit;
	}


	$results=q("SELECT * FROM ebay_accounts WHERE id_account= ".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<EbayGetOrdersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EbayGetOrdersResponse>'."\n";
		exit;
	}
	else 
	{
		$account=mysqli_fetch_array($results);
		$results=q("SELECT * FROM ebay_accounts WHERE id_account=1;", $dbshop, __FILE__, __LINE__);		
		$accounts[1]=mysqli_fetch_array($results);
		$results=q("SELECT * FROM ebay_accounts WHERE id_account=2;", $dbshop, __FILE__, __LINE__);
		$accounts[2]=mysqli_fetch_array($results);

	}
	
	
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
function update_quantity($idEbayItem, $qnt, $account)
{
	//generate XML
	$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
	$requestXmlBody .= '<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
	if ( $account["production"]==0 )
	{
		$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>'.$account["token_sandbox"].'</eBayAuthToken></RequesterCredentials>';
	}
	else
	{
		$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>'.$account["token"].'</eBayAuthToken></RequesterCredentials>';
	}
	$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
	$requestXmlBody .= '<ErrorLanguage>de_DE</ErrorLanguage>';

	$requestXmlBody .= '<Item>';
	$requestXmlBody .= '	<ItemID>'.$idEbayItem.'</ItemID>';
	$requestXmlBody .= '	<Quantity>'.$qnt.'</Quantity>';
	$requestXmlBody .= '</Item>';
	$requestXmlBody .= '<Version>'.$account["Version"].'</Version>';
	$requestXmlBody .= '<WarningLevel>High</WarningLevel>';
	$requestXmlBody .= '</ReviseItemRequest>';

	if ( $account["production"]==0 )
	{
		$serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
		$devID=$account["devID_sandbox"];
		$appID=$account["appID_sandbox"];
		$certID=$account["certID_sandbox"];
	}
	else
	{
		$serverUrl = 'https://api.ebay.com/ws/api.dll';
		$devID=$account["devID"];
		$appID=$account["appID"];
		$certID=$account["certID"];
	}
	$headers = array (
		//Regulates versioning of the XML interface for the API
		'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $account["Version"],
		
		//set the keys
		'X-EBAY-API-DEV-NAME: ' . $devID,
		'X-EBAY-API-APP-NAME: ' . $appID,
		'X-EBAY-API-CERT-NAME: ' . $certID,
		
		//the name of the call we are requesting
		'X-EBAY-API-CALL-NAME: ReviseItem',			
		
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		'X-EBAY-API-SITEID: ' . $account["SiteID"]
		
	);

	$connection = curl_init();
	curl_setopt($connection, CURLOPT_URL, $serverUrl);
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($connection, CURLOPT_POST, 1);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $requestXmlBody);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
	$responseXml = curl_exec($connection);
	curl_close($connection);

	return $responseXml;

}
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	$res_orders=q("SELECT * FROM ebay_orders_items WHERE account_id = ".$account["id_account"]." ORDER BY CreatedDateTimestamp DESC LIMIT 200;", $dbshop, __FILE__, __LINE__);
	while ($row_orders=mysqli_fetch_array($res_orders))
	{
		$orders_items[$row_orders["ItemItemID"]]=$row_orders["ItemSKU"];
		$orders_time[$row_orders["ItemItemID"]]=$row_orders["CreatedDateTimestamp"];
	}
	//CHECK der EbayArtikel nach dem Verkauf bereits geupdated wurde
	$res_update=q("SELECT ItemID, lastQuantityUpdate FROM ebay_auctions WHERE account_id = ".$account["id_account"].";", $dbshop, __FILE__, __LINE__);
	while ($row_update=mysqli_fetch_array($res_update))
	{
		$update_time[$row_update["ItemID"]]=$row_update["lastQuantityUpdate"];
	}
		//NICHTVORHANDENE EBAYAUCTIONS aus ORDERARRAY entfernen
		while (list ($EbayItemID, $ItemSKU) = each ( $orders_items ))
		{
			if (!isset($update_time[$EbayItemID]))
			{
				unset($orders_items[$EbayItemID]);
				unset($orders_time[$EbayItemID]);
			}
		}
		reset($orders_items);
		//Check auf durgeführtes Update
		while (list ($EbayItemID, $soldTimestamp) = each ( $orders_time ))
		{
			if ($update_time[$EbayItemID]>=$soldTimestamp)
			{
				unset($orders_items[$EbayItemID]);
				unset($orders_time[$EbayItemID]);
			}
		}
		reset($orders_time);
		
	//LAGERBESTÄNDE LADEN
	$res_lager=q("SELECT * FROM lager;", $dbshop, __FILE__, __LINE__);
	while ($row_lager=mysqli_fetch_array($res_lager))
	{
		$lager[$row_lager["ArtNr"]]=($row_lager["ISTBESTAND"]*1)+($row_lager["MOCOMBESTAND"]*1);
	}

	
	//UPDATE DURCHFÜHREN
	while (list ($EbayItemID, $ItemSKU) = each ( $orders_items ))
	{
		if ($lager[$ItemSKU]>19)
		{

			$responseXML=update_quantity($EbayItemID, 20, $account[$id_account]);
			$xml = new SimpleXMLElement($responseXML);
			if ($xml->Ack[0]=="Success")
			{
				//QUNATITYUPDATE vermerken
				$row_update=q("UPDATE ebay_auctions SET lastQuantityUpdate = ".time()." WHERE ItemID = ".$EbayItemID." AND account_id = ".$id_account.";", $dbshop, __FILE__, __LINE__);
				echo 'Stückzahl für EbayItemID '.$EbayItemID.' auf 20 erhöht'."\n";
			}
			else
			{
				$errCode=$xml->Errors[0]->ErrorCode[0];
				$errShortMsg=$xml->Errors[0]->ShortMessage[0];
				$errLongMsg=$xml->Errors[0]->LongMessage[0];
				echo 'Fehler beim Update des EbayItems ('.$account[$row_auctions[$id_account]]["title"].') '.$EbayItemID.'. FEHLERMELDUNG: ErrorCode: '.$errCode.' | ShortMessage: '.$errShortMsg.' | LongMessage: '.$errLongMsg."\n";
				
				switch ($errCode)
				{
					case 23004: 
						$row_update=q("UPDATE ebay_auctions SET lastQuantityUpdate = ".time()." WHERE ItemID = ".$EbayItemID." AND account_id = ".$id_account.";", $dbshop, __FILE__, __LINE__); 
					break;
					case 291: 
						$row_update=q("DELETE FROM ebay_auctions WHERE ItemID = ".$EbayItemID.";", $dbshop, __FILE__ ,__LINE__);
					break;	
				}

			}
			
		}
		//ALLE BEKANNTEN AUCTIONS (MAPCO + AP) AUF ISTBESTAND SETZEN
		elseif ($lager[$ItemSKU]>2)
		{
			$res_auctions=q("SELECT * FROM ebay_auctions WHERE SKU = '".$ItemSKU."';", $dbshop, __FILE__, __LINE__);
			while ($row_auctions=mysqli_fetch_array($res_auctions))
			{
				$responseXML=update_quantity($row_auctions["ItemID"], $lager[$ItemSKU], $accounts[$row_auctions["account_id"]]);
				$xml = new SimpleXMLElement($responseXML);
				if ($xml->Ack[0]=="Success")
				{
					//QUNATITYUPDATE vermerken
					$row_update=q("UPDATE ebay_auctions SET lastQuantityUpdate = ".time()." WHERE ItemID = ".$row_auctions["ItemID"]." AND account_id = ".$row_auctions["account_id"].";", $dbshop, __FILE__, __LINE__);
					echo 'Stückzahl für EbayItemID ('.$account[$row_auctions["account_id"]]["title"].') '.$row_auctions["ItemID"].' auf '.$lager[$ItemSKU].' gesetzt (wegen ISTBESTAND zu MPN '.$ItemSKU.')'."\n";
				}
				else
				{
					$errCode=$xml->Errors[0]->ErrorCode[0];
					$errShortMsg=$xml->Errors[0]->ShortMessage[0];
					$errLongMsg=$xml->Errors[0]->LongMessage[0];
					echo 'Fehler beim Update des EbayItems ('.$account[$row_auctions["account_id"]]["title"].') '.$row_auctions["ItemID"].'. FEHLERMELDUNG: ErrorCode: '.$errCode.' | ShortMessage: '.$errShortMsg.' | LongMessage: '.$errLongMs."\n";
					
					switch ($errCode)
					{
						case 23004: 
							$row_update=q("UPDATE ebay_auctions SET lastQuantityUpdate = ".time()." WHERE ItemID = ".$row_auctions["ItemID"]." AND account_id = ".$row_auctions["account_id"].";", $dbshop, __FILE__, __LINE__); 
						break;
						case 291: 
							$row_update=q("DELETE FROM ebay_auctions WHERE ItemID = ".$row_auctions["ItemID"].";", $dbshop, __FILE__ ,__LINE__);
						break;	
					}

				}

			}

		}
		elseif ($lager[$ItemSKU]<=2)
		{
			//ENDITEM FÜR ALLE AUCTIONS (MAPCO + AP)
			$res_auctions=q("SELECT * FROM ebay_auctions WHERE SKU = '".$ItemSKU."';", $dbshop, __FILE__, __LINE__);
			while ($row_auctions=mysqli_fetch_array($res_auctions))
			{
				$responseXml=post(PATH."soa/", array("API" => "ebay", "Action" => "EndItem", "id_auction" => $row_auctions["id_auction"]));
				$xml = new SimpleXMLElement($responseXML);
				if ($xml->Ack[0]=="Success")
				{
				
					echo 'Angebot ('.$account[$row_auctions["account_id"]]["title"].') '.$row_auctions["ItemID"].' beendet (wegen ISTBESTAND = '.$lager[$ItemSKU].' zu MPN '.$ItemSKU.')'."\n";
				}
				else
				{
					$errCode=$xml->Error[0]->Code[0];
					$errShortMsg=$xml->Error[0]->shortMsg[0];
					$errLongMsg=$xml->Errors[0]->longMsg[0];
					echo 'Fehler beim Beenden des EbayItems ('.$account[$row_auctions["account_id"]]["title"].') '.$EbayItemID.'. FEHLERMELDUNG: ErrorCode: '.$errCode.' | ShortMessage: '.$errShortMsg.' | LongMessage: '.$errLongMs."\n";
				}
			}

		}
			
	}
echo '<br />Bearbeitungszeit: '.(time()-$starttime).' Sekunden'."\n";
	
?>