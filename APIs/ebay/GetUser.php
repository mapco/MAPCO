<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<OrdersUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, welche Auktion aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrdersUpdateResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<OrdersUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrdersUpdateResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	if( isset($_POST["OrderID"]) )
	{
		$results=q("SELECT * FROM ebay_orders WHERE OrderID='".$_POST["OrderID"]."';", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["ContactID"]=$row["UserID"];
		$results=q("SELECT * FROM ebay_orders_items WHERE OrderID='".$_POST["OrderID"]."';", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["ItemID"]=$row["ItemItemID"];
	}

	if ( !isset($_POST["ContactID"]) )
	{
		echo '<OrdersUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ContactID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ContactID übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrdersUpdateResponse>'."\n";
		exit;
	}


	$requestXmlBody  = '<?xml version="1.0" encoding="utf-8"?>';
	$requestXmlBody .= '<GetUserRequest xmlns="urn:ebay:apis:eBLBaseComponents"> ';
	$requestXmlBody .= '	<RequesterCredentials>';
	$requestXmlBody .= '		<eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
	$requestXmlBody .= '	</RequesterCredentials>';
	$requestXmlBody .= '	<ItemID>'.$_POST["ItemID"].'</ItemID>';
	$requestXmlBody .= '	<UserID>'.$_POST["ContactID"].'</UserID>';
	if( isset($_POST["ItemID"]) ) $requestXmlBody .= '	<DetailLevel>ReturnAll</DetailLevel>';
	$requestXmlBody .= '</GetUserRequest> ';
	
	//submit Action
	echo $response = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetUser", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>