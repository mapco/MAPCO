<?php

include("config.php");

$_POST["id_account"]=1;
$_POST["from"]=1367810000;
$_POST["to"]=1367822020;

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
	if ( mysql_num_rows($results)==0 )
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
	$account=mysql_fetch_array($results);
	
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
	$resultPageNumber = $xml->PageNumber[0];
	$resultPageOrderCount = $xml->ReturnedOrderCountActual[0];
	$resultTotalNumberOfEntries = $xml->PaginationResult[0]->TotalNumberOfEntries[0];
//	echo '<p>Anzahl Orders: '.$resultPageOrderCount."/".$resultTotalNumberOfEntries = $xml->PaginationResult[0]->TotalNumberOfEntries[0].' <br />';
//	echo 'Seiten: '.$resultPageNumber."/".$resultTotalNumberOfPages = $xml->PaginationResult[0]->TotalNumberOfPages[0].'<br />';
//	echo 'Has More Orders: '.$xml->HasMoreOrders[0].'</p>';

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
			
			$t_orderID[$k]=$OrderID;
			
			//Search for TransactionID is already stored
			$t_results[$k]=q("SELECT * FROM ebay_orders_items2 WHERE TransactionID='".$transactionID[$k]."';", $dbshop, __FILE__, __LINE__);
			$k++;

		} // FOREACH TRANSACTIONS


//VERSION 2 **********************************************************************************************
//ORDERS WERDEN NICHT MEHR GENERELL GELÖSCHT SONDERN GEUPDATED
	//TRANSACTIONS
		for ($k=0; $k<sizeof($t_sql); $k++) {
			if ( mysql_num_rows($t_results[$k])==0 )
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
			
			if ( mysql_num_rows($t_results[$k])==0 )
			{ // INSERT
			
				$query="INSERT INTO ebay_orders_items2 (";
				for ($l=0; $l<sizeof($t_sql[$k]); $l++) {
					$query.=$t_sql[$k][$l]["name"];
					if ( ($l+1)<sizeof($t_sql[$k]) ) $query.=", ";
				}
				$query.=") VALUES(";
				for ($l=0; $l<sizeof($t_sql[$k]); $l++) {
					$query.="'".mysql_real_escape_string($t_sql[$k][$l]["value"], $dbshop)."'";
					if ( ($l+1)<sizeof($t_sql[$k]) ) $query.=", ";
				}
				$query.=");";

				q($query, $dbshop, __FILE__, __LINE__);

			}
			else
			{ // UPDATE
				$row_old=mysql_fetch_array($t_results[$k]);
				$OrderID_old[$k]=$row_old["OrderID"];

				$query="UPDATE ebay_orders_items2 SET ";
				for($l=0; $l<sizeof($t_sql[$k]); $l++)
				{
					//E-MAIL NICHT ÜBERSCHREIBEN
					if ($t_sql[$k][$l]["name"]!="BuyerEmail") {
						$query.=$t_sql[$k][$l]["name"]."='".mysql_real_escape_string($t_sql[$k][$l]["value"], $dbshop)."'";
						if ( ($l+1)<sizeof($t_sql[$k]) ) $query.=", ";
					}
				}
			$query.=" WHERE TransactionID='".$transactionID[$k]."';";
			q($query, $dbshop, __FILE__, __LINE__);
			}
		} // FOR ->TRANSAKTIOS

	//OREDERS	
		$order_inserted=false;
		for ($k=0; $k<sizeof($t_sql); $k++)
		{
			//CHECK, ob Order mit neuer OrderID (aus Item) bereits existiert
			$res_check=q("SELECT * FROM ebay_orders2 WHERE OrderID = ".$t_orderID[$k].";",$dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_check)>0 && !$order_inserted) 
			{ //Upadate Order
				$j++;
				$sql[$j]["name"]="lastmod";
				$sql[$j]["value"]=time();
				$j++;
				$sql[$j]["name"]="lastmod_user";
				$sql[$j]["value"]=$_SESSION["id_user"];
				//GET ID_ORDER
				$row_check=mysql_fetch_array($res_check);
				$id_order=$row_check["id_order"];
		
				$query="UPDATE ebay_orders2 SET ";
				for ($j=0;$j<sizeof($sql); $j++)
				{
					if ($sql[$j]["name"]!="firstmod" && $sql[$j]["name"]!="firstmod_user")
					{
						$query.=$sql[$j]["name"]." = '".mysql_real_escape_string($sql[$j]["value"], $dbshop)."'";
						if ( ($j+1)<sizeof($sql) ) $query.=", ";
					}
				}
				$query.=" WHERE OrderID = ".$t_orderID[$k].";";
		
				q($query, $dbshop, __FILE__, __LINE__);
				
				if ($id_order!=$id_order_tmp)
				{
					$varField["API"]="crm";
					$varField["Action"]="import_ebayOrderData";
					$varField["usertoken"]="merci2664";
					$varField["mode"]="add";
					$varField["EbayOrderID"]=$id_order;
					$id_order_tmp=$id_order;
					//>>CHECK TO MAKE ONLY SINGLE UPDATE FOR SAME ORDER
					echo "UPDATE ORDER ".$id_order."<br />".post(PATH."soa/", $varField)."<br />";
				}
			}
			else 
			{ //delete & add
				if (isset($OrderID_old[$k]))
				{
					$results=q("delete from ebay_orders2 where OrderID = '".$OrderID_old[$k]."';",$dbshop, __FILE__, __LINE__);
				}
				
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


				$query="INSERT INTO ebay_orders2 (";
				for($j=0; $j<sizeof($sql); $j++)
				{
					$query.=$sql[$j]["name"];
					if ( ($j+1)<sizeof($sql) ) $query.=", ";
				}
				$query.=") VALUES(";
				for($j=0; $j<sizeof($sql); $j++)
				{
					$query.="'".mysql_real_escape_string($sql[$j]["value"], $dbshop)."'";
					if ( ($j+1)<sizeof($sql) ) $query.=", ";
				}
				$query.=");";
				q($query, $dbshop, __FILE__, __LINE__);
				
				$id_order=mysql_insert_id();
				
				$varField["API"]="crm";
				$varField["Action"]="import_ebayOrderData";
				$varField["usertoken"]="merci2664";
				$varField["mode"]="add";
				$varField["EbayOrderID"]=$id_order;

				echo "Insert ORDER ".$id_order."<br />". post(PATH."soa/", $varField)."<br />";
				
				//FLAG, FÜR keine weiteren Updates der Order bei zusammenfassungen -> SONST KEINE VOLLSTÄNDIGE LÖSCHUNG ALTER ORDERS
				$order_inserted=true;


			}

			
		
		}
		
	} // FOR $resultPageOrderCount
	
} // while ($resultHasMoreOrders)

	//return success
	echo '<OrdersUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<TotalOrders>'.$resultTotalNumberOfEntries.'</TotalOrders>'."\n";
	echo '</OrdersUpdateResponse>'."\n";

?>