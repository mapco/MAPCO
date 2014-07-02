<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<OrdersUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
//		echo '		<longMsg>Es muss eine Auktions-ID übermittelt werden, damit der Service weiß, welche Auktion aktualisiert werden soll.</longMsg>'."\n";
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
	
	$requestPage = 0;
	$resultHasMoreOrders = true;
	
while ($resultHasMoreOrders)
{
	$requestPage++;

	$requestXmlBody  = '<?xml version="1.0" encoding="utf-8"?>';
	$requestXmlBody .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
	$requestXmlBody .= '  <RequesterCredentials>';
	$requestXmlBody .= '    <eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
	$requestXmlBody .= '  </RequesterCredentials>';
	$requestXmlBody .= '  <CreateTimeFrom>'.date('Y-m-d\TH:i:s.000\Z', $_POST["from"]).'</CreateTimeFrom>';
	$requestXmlBody .= '  <CreateTimeTo>'.date('Y-m-d\TH:i:s.000\Z', $_POST["to"]).'</CreateTimeTo>';
//	$requestXmlBody .= '  <NumberOfDays>7</NumberOfDays>';
	$requestXmlBody .= '  <OrderRole>Seller</OrderRole>';
	$requestXmlBody .= '  <Pagination>';
	$requestXmlBody .= '	<EntriesPerPage>100</EntriesPerPage>';
	$requestXmlBody .= '	<PageNumber>'.$requestPage.'</PageNumber>';
	$requestXmlBody .= '  </Pagination>';
	$requestXmlBody .= '</GetOrdersRequest>';
	
	//submit auction
	$response = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetOrders", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));
	
	//read orders
	$xml = new SimpleXMLElement($response);
	echo 'Anzahl Orders: '. $resultTotalNumberOfEntries = $xml->PaginationResult[0]->TotalNumberOfEntries[0].' <br />';
	echo 'Seiten: '.	$resultTotalNumberOfPages = $xml->PaginationResult[0]->TotalNumberOfPages[0];
	$resultPageNumber = $xml->PageNumber[0];
	$resultPageOrderCount = $xml->ReturnedOrderCountActual[0];
	if ($xml->HasMoreOrders[0]=="true")	{$resultHasMoreOrders = true;} else {$resultHasMoreOrders = false;}
	
	
	//for ($i=0; $i<$resultPageOrderCount; $i++) 
	for($i=0; isset($xml->OrderArray[0]->Order[$i]); $i++)
	{
		$sql=array();
		$j=0;
		$sql[$j]["name"]="account_id";
		$sql[$j]["value"]=$_POST["id_account"];
		$j++;
		$sql[$j]["name"]="AdjustmentAmount";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AdjustmentAmount[0];
		$j++;
		$sql[$j]["name"]="AmountPaid";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AmountPaid[0];
		$j++;
		$sql[$j]["name"]="AmountSaved";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AmountSaved[0];
		$j++;
		$sql[$j]["name"]="BuyerUserID";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->BuyerUserID[0];
		$j++;
		$sql[$j]["name"]="CheckoutStatuseBayPaymentStatus";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->CheckoutStatus[0]->eBayPaymentStatus[0];
		$j++;
		$sql[$j]["name"]="CheckoutStatusLastModifiedTime";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->CheckoutStatus[0]->LastModifiedTime[0];
		$j++;
		$sql[$j]["name"]="CheckoutStatusPaymentMethod";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->CheckoutStatus[0]->PaymentMethod[0];
		$j++;
		$sql[$j]["name"]="CheckoutStatusStatus";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->CheckoutStatus[0]->Status[0];
		$j++;
		$sql[$j]["name"]="CheckoutStatusIntegratedMerchantCreditCardEnabled";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->CheckoutStatus[0]->IntegratedMerchantCreditCardEnabled[0];
		$j++;
		$sql[$j]["name"]="ShippingDetailsSellingManagerSalesRecordNumber";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingDetails[0]->SellingManagerSalesRecordNumber[0];
		$j++;
		$sql[$j]["name"]="CreatedTime";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->CreatedTime[0];
		$j++;
		$sql[$j]["name"]="CreatedTimeTimestamp";
		$sql[$j]["value"]=strtotime($xml->OrderArray[0]->Order[$i]->CreatedTime[0]);
		$j++;
		$sql[$j]["name"]="OrderID";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->OrderID[0];
		$OrderID=$sql[$j]["value"];
		$j++;
		$sql[$j]["name"]="OrderStatus";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->OrderStatus[0];
		$j++;
		$sql[$j]["name"]="PaidTime";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->PaidTime[0];
		$j++;
		$sql[$j]["name"]="ShippedTime";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippedTime[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressName";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Name[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressStreet1";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Street1[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressStreet2";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Street2[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressCityName";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->CityName[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressStateOrProvince";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->StateOrProvince[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressCountry";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Country[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressCountryName";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->CountryName[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressPhone";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Phone[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressPostalCode";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->PostalCode[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressAddressID";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->AddressID[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressAddressOwner";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->AddressOwner[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressExternalAddressID";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->ExternalAddressID[0];
		$j++;
		$sql[$j]["name"]="ShippingServiceSelectedShippingService";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingServiceSelected[0]->ShippingService[0];
		$j++;
		$sql[$j]["name"]="ShippingServiceSelectedShippingServiceCost";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingServiceSelected[0]->ShippingServiceCost[0];
		$j++;
		$sql[$j]["name"]="Subtotal";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->Subtotal[0];
		$j++;
		$sql[$j]["name"]="Total";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->Total[0];
		
		//TRANSACTIONS
		$k=0;
		$t_sql=array();
		foreach($xml->OrderArray[0]->Order[$i]->TransactionArray[0]->Transaction as $transaction)
		{
			$l=0;
			$t_sql[$k][$l]["name"]="account_id";
			$t_sql[$k][$l]["value"]=$_POST["id_account"];
			$l++;
			$t_sql[$k][$l]["name"]="BuyerEmail";
			$t_sql[$k][$l]["value"]=$transaction->Buyer[0]->Email[0];
			$l++;
			$t_sql[$k][$l]["name"]="ShippingDetailsSellingManagerSalesRecordNumber";
			$t_sql[$k][$l]["value"]=$transaction->ShippingDetails[0]->SellingManagerSalesRecordNumber[0];
			$l++;
			$t_sql[$k][$l]["name"]="CreatedDate";
			$t_sql[$k][$l]["value"]=$transaction->CreatedDate[0];
			$l++;
			$t_sql[$k][$l]["name"]="CreatedDateTimestamp";
			$t_sql[$k][$l]["value"]=strtotime($transaction->CreatedDate[0]);
			$l++;
			$t_sql[$k][$l]["name"]="ItemItemID";
			$t_sql[$k][$l]["value"]=$transaction->Item[0]->ItemID[0];
			$l++;
			$t_sql[$k][$l]["name"]="ItemTitle";
			$t_sql[$k][$l]["value"]=$transaction->Item[0]->Title[0];
			$l++;
			$t_sql[$k][$l]["name"]="ItemSKU";
			$t_sql[$k][$l]["value"]=$transaction->Item[0]->SKU[0];
			$l++;
			$t_sql[$k][$l]["name"]="ItemConditionID";
			$t_sql[$k][$l]["value"]=$transaction->Item[0]->ConditionID[0];
			$l++;
			$t_sql[$k][$l]["name"]="QuantityPurchased";
			$t_sql[$k][$l]["value"]=$transaction->QuantityPurchased[0];
			$l++;
			$t_sql[$k][$l]["name"]="StatusPaymentHoldStatus";
			$t_sql[$k][$l]["value"]=$transaction->Status[0]->PaymentHoldStatus[0];
			$l++;
			$t_sql[$k][$l]["name"]="TransactionID";
			$t_sql[$k][$l]["value"]=$transaction->TransactionID[0];
			$transactionID[$k]=$t_sql[$k][$l]["value"];
			$l++;
			$t_sql[$k][$l]["name"]="TransactionPrice";
			$t_sql[$k][$l]["value"]=$transaction->TransactionPrice[0];
			$l++;
			$t_sql[$k][$l]["name"]="OrderLineItemID";
			$t_sql[$k][$l]["value"]=$transaction->OrderLineItemID[0];
			$orderLineItemID[$k]=$t_sql[$k][$l]["value"];
			$l++;
			$t_sql[$k][$l]["name"]="OrderID";
			$t_sql[$k][$l]["value"]=$OrderID;
			
			//Search for TransactionID is already stored
			$t_results[$k]=q("SELECT * FROM ebay_orders_items WHERE TransactionID='".$transactionID[$k]."';", $dbshop, __FILE__, __LINE__);
			$k++;

		} // FOREACH TRANSACTIONS

	//TRANSACTIONS
		for ($k=0; $k<sizeof($t_sql); $k++) {
			if ( mysqli_num_rows($t_results[$k])==0 )
			{ // INSERT
				$lines=sizeof($t_sql[$k]);
				$t_sql[$k][$lines]["name"]="firstmod";
				$t_sql[$k][$lines]["value"]=time();
				$lines++;
				$t_sql[$k][$lines]["name"]="firstmod_user";
				$t_sql[$k][$lines]["value"]=$_SESSION["id_user"];
			}
			$lines=sizeof($t_sql[$k]);
			$t_sql[$k][$lines]["name"]="lastmod";
			$t_sql[$k][$lines]["value"]=time();
			$lines++;
			$t_sql[$k][$lines]["name"]="lastmod_user";
			$t_sql[$k][$lines]["value"]=$_SESSION["id_user"];
			
			if ( mysqli_num_rows($t_results[$k])==0 )
			{ // INSERT
				$query="INSERT INTO ebay_orders_items (";
				for ($j=0; $j<sizeof($t_sql[$k]); $j++) {
					$query.=$t_sql[$k][$j]["name"];
					if ( ($j+1)<sizeof($t_sql[$k]) ) $query.=", ";
				}
				$query.=") VALUES(";
				for ($j=0; $j<sizeof($t_sql[$k]); $j++) {
					$query.="'".mysqli_real_escape_string($dbshop, $t_sql[$k][$j]["value"])."'";
					if ( ($j+1)<sizeof($t_sql[$k]) ) $query.=", ";
				}
				$query.=");";

				q($query, $dbshop, __FILE__, __LINE__);

			}
			else
			{ // UPDATE
				$query="UPDATE ebay_orders_items SET ";
				for($j=0; $j<sizeof($t_sql[$k]); $j++)
				{
					$query.=$t_sql[$k][$j]["name"]."='".mysqli_real_escape_string($dbshop, $t_sql[$k][$j]["value"])."'";
					if ( ($j+1)<sizeof($t_sql[$k]) ) $query.=", ";
				}
			$query.=" WHERE TransactionID='".$transactionID[$k]."';";
			q($query, $dbshop, __FILE__, __LINE__);
			}
		} // FOR ->TRANSAKTIOS
	
	//OREDERS	
		//ALLE VORHERIGEN ORDERDATEN WERDEN GELÖSCHT UND DURCH DIE AKTUELLE ORDER ERSETZT
		for ($i=0; $i<sizeof($t_sql); $i++)
		{
			$row=mysqli_fetch_array($t_results[$i]);
			$results=q("delete from ebay_orders where OrderID = '".$row["OrderID"]."';",$dbshop, __FILE__, __LINE__);
		}
		//INSERT
		$j++;
		$sql[$j]["name"]="firstmod";
		$sql[$j]["value"]=time();
		$j++;
		$sql[$j]["name"]="firstmod_user";
		$sql[$j]["value"]=$_SESSION["id_user"];
		$j++;
		$sql[$j]["name"]="lastmod";
		$sql[$j]["value"]=time();
		$j++;
		$sql[$j]["name"]="lastmod_user";
		$sql[$j]["value"]=$_SESSION["id_user"];

		$query="INSERT INTO ebay_orders (";
		for($j=0; $j<sizeof($sql); $j++)
		{
			$query.=$sql[$j]["name"];
			if ( ($j+1)<sizeof($sql) ) $query.=", ";
		}
		$query.=") VALUES(";
		for($j=0; $j<sizeof($sql); $j++)
		{
			$query.="'".mysqli_real_escape_string($dbshop, $sql[$j]["value"])."'";
			if ( ($j+1)<sizeof($sql) ) $query.=", ";
		}
		$query.=");";
		q($query, $dbshop, __FILE__, __LINE__);
			
/*
		if($updateOrder)
		{
			$j++;
			$sql[$j]["name"]="lastmod";
			$sql[$j]["value"]=time();
			$j++;
			$sql[$j]["name"]="lastmod_user";
			$sql[$j]["value"]=$_SESSION["id_user"];

			$query="UPDATE ebay_orders SET ";
			for($j=0; $j<sizeof($sql); $j++)
			{
				$query.=$sql[$j]["name"]."='".mysqli_real_escape_string($dbshop, $sql[$j]["value"])."'";
				if ( ($j+1)<sizeof($sql) ) $query.=", ";
			}
			$query.=" WHERE OrderID=".$OrderID.";";
			q($query, $dbshop, __FILE__, __LINE__);
		}
*/
		
	} // FOR $resultPageOrderCount
	
} // while ($resultHasMoreOrders)

	//return success
	echo '<OrdersUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</OrdersUpdateResponse>'."\n";

?>