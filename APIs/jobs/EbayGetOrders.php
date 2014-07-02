<?php

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

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".number_format($_POST["id_account"]).";", $dbshop, __FILE__, __LINE__);
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
//	$account=mysqli_fetch_array($results);

/*	if ( !isset($_POST["date_to"]) )
	{
		echo '<EbayGetOrdersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Enddatum nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Das Datum bis zu welchem die Verkäufe bei Ebay abgerufen werden sollen konnte nicht gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EbayGetOrdersResponse>'."\n";
		exit;
	}
*/
	if ( !isset($_POST["period"]) )
	{
		echo '<EbayGetOrdersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Zeitraumangabe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Zeitraum der abzurufenden Verkäufe konnte nich gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EbayGetOrdersResponse>'."\n";
		exit;
	}

	//Zeitraum der abzurufenden Verkäufe festlegegen
	$from_date = time()-$_POST["period"]*24*3600; //PERIOD = STUNDEN
	$to_date = time();
	
	$response = post(PATH."soa/", array("API" => "ebay", "Action" => "GetOrders", "id_account" => number_format($_POST["id_account"]), "from" => $from_date, "to" => $to_date));
	
	$xml = new SimpleXMLElement($response);
	$ack = $xml->Ack[0];
	$TotalOrders = $xml->TotalOrders[0];
	
	if ($ack == "Success")
	{
		echo '<EbayGetOrdersResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<TotalOrders>'.$TotalOrders.'</TotalOrders>'."\n";
		echo '</EbayGetOrdersResponse>'."\n";
	}
	elseif ($ack == "Failure")
	{
		echo '<'.$xml->documentElement.'>'."\n";
		echo '	<Ack>'.$ack.'</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.$xml->Error[0]->Code[0].'</Code>'."\n";
		echo '		<shortMsg>'.$xml->Error[0]->shortMsg[0].'</shortMsg>'."\n";
		echo '		<longMsg>'.$xml->Error[0]->longMsg[0].'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</'.$xml->documentElement.'>'."\n";
		exit;
	}
