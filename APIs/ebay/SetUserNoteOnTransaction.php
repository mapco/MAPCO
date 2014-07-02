<?php
	//XML error handler
	function HandleXmlError($errno, $errstr, $errfile, $errline)
	{
		error($errfile, $errline, $errno." ".$errstr);
	}

	if ( !isset($_POST["id_account"]) )
	{
		echo '<SendMemberMessageToBuyerResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, welche Auktion aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SendMemberMessageToBuyerResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<SendMemberMessageToBuyerResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SendMemberMessageToBuyerResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	if ( !isset($_POST["TransactionID"]) )
	{
		echo '<SendMemberMessageToBuyerResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ItemID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Ebay-Artikelnummer übermittelt werden, zu der eine Nachricht gesendet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SendMemberMessageToBuyerResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["Note"]) )
	{
		echo '<SendMemberMessageToBuyerResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>BuyerID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Ebay-Nutzer ID übermittelt werden, zu der eine Nachricht übermittelt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SendMemberMessageToBuyerResponse>'."\n";
		exit;
	}
	

	$requestXmlBody  = '<?xml version="1.0" encoding="utf-8"?>';
	$requestXmlBody .= '<SetUserNotesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
	$requestXmlBody .= '  <RequesterCredentials>';
	$requestXmlBody .= '    <eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
	$requestXmlBody .= '  </RequesterCredentials>';
	$requestXmlBody .= '<Action>AddOrUpdate</Action>';
$requestXmlBody .= '<ItemID>110944897140</ItemID>';
	$requestXmlBody .= '<TransactionID>'.$_POST["TransactionID"].'</TransactionID>';
	$requestXmlBody .= '<NoteText><![CDATA['.$_POST["Note"].']]></NoteText>';
 	$requestXmlBody .= '<ErrorLanguage>de_DE</ErrorLanguage>';
    $requestXmlBody .= '<Version>'.$account["Version"].'</Version>';
    $requestXmlBody .= '<WarningLevel>Low</WarningLevel>';
	$requestXmlBody .= '</SetUserNotesRequest>';
	
	//submit auction
$response = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "SetUserNotes", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));
echo $response;
/*
	set_error_handler('HandleXmlError');
    $dom = new DOMDocument();
    $dom->loadXml($response);    
    restore_error_handler();
	
	//get any error nodes
	$errors = $dom->getElementsByTagName('Error');

	//if there are error nodes
	if( $errors->length>0 )
	{
		//Get error code, ShortMesaage and LongMessage
		$code     = $errors->getElementsByTagName('Code');
		$shortMsg = $errors->getElementsByTagName('shortMsg');
		$longMsg  = $errors->getElementsByTagName('longMsg');

		echo '<SendMemberMessageToBuyerResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>'.$shortMsg.'.</shortMsg>'."\n";
		echo '		<longMsg>'.$longMsg.'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SendMemberMessageToBuyerResponse>'."\n";
		exit;
		
	}
*/
	echo '<SendMemberMessageToBuyerResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</SendMemberMessageToBuyerResponse>'."\n";
?>