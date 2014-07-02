<?php

if (!isset($_POST["from"])) $_POST["from"]=time()-3*3600; 
//$_POST["from"]=mktime(0,0,1,1,20,2014);
//$_POST["from"]=time()-20*3600; 
if (!isset($_POST["to"])) $_POST["to"]=time();
//$_POST["to"]=mktime(23,59,59,1,20,2014);
//$_POST["to"]=time()-6*3600;

	// ABFRAGEZEITRAUM um 5 Minuten verkürzen, um Überschneidungen bei ZUsammenfassungen und anschließender Zahlung zu vermeiden
	$_POST["to"]-=300;


	if ( !isset($_POST["id_accountsite"]) )
	{
		echo '<Get_Ebay_OrdersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, für welchen Account die Ebay-Daten heruntergeladen werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</Get_Ebay_OrdersResponse>'."\n";
		exit;
	}
	
	$results=q("SELECT * FROM ebay_accounts_sites WHERE id_accountsite=".$_POST["id_accountsite"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<Get_Ebay_OrdersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</Get_Ebay_OrdersResponse>'."\n";
		exit;
	}
	
	$account_site=mysqli_fetch_assoc($results);
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$account_site["account_id"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<Get_Ebay_OrdersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</Get_Ebay_OrdersResponse>'."\n";
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
//	$requestXmlBody .= '  <OrderIDArray>';
//	$requestXmlBody .= '  <OrderID>152743546015</OrderID>';
//		$requestXmlBody .= '  </OrderIDArray>';

	$requestXmlBody .= '  <OrderRole>Seller</OrderRole>';
	$requestXmlBody .= '  <Pagination>';
	$requestXmlBody .= '	<EntriesPerPage>100</EntriesPerPage>';
	$requestXmlBody .= '	<PageNumber>'.$requestPage.'</PageNumber>';
	$requestXmlBody .= '  </Pagination>';
	$requestXmlBody .= '</GetOrdersRequest>';
	
	//submit auction
	$response = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetOrders", "id_account" => $account_site["account_id"], "request" => $requestXmlBody));
//echo '<textarea cols="80" rows="20" name="text">'.$response.'</textarea>';
//echo "ADD".$response;
//exit;
	//read orders
	$xml = new SimpleXMLElement($response);
	$resultPageNumber = $xml->PageNumber[0];
	$resultPageOrderCount = $xml->ReturnedOrderCountActual[0];
	$resultTotalNumberOfEntries = $xml->PaginationResult[0]->TotalNumberOfEntries[0];

	if ($xml->HasMoreOrders[0]=="true")	{$resultHasMoreOrders = true;} else {$resultHasMoreOrders = false;}
	
	
	for($i=0; isset($xml->OrderArray[0]->Order[$i]); $i++)
	{
		$sql=array();
		$orderUpdate_from_Item=false;

		$transaction_old=array();
		
		$j=0;
		$sql[$j]["name"]="account_id";
		$sql[$j]["value"]=$account_site["account_id"];
		$j++;
		$sql[$j]["name"]="AdjustmentAmount";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AdjustmentAmount[0];
		$j++;
		$sql[$j]["name"]="AmountPaid";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AmountPaid[0];

		$j++;
		$sql[$j]["name"]="Currency_Code";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AmountPaid[0]["currencyID"];
	
		$j++;
		$sql[$j]["name"]="AmountSaved";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AmountSaved[0];
		$j++;
		$sql[$j]["name"]="BuyerUserID";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->BuyerUserID[0];
		$BuyerID=$xml->OrderArray[0]->Order[$i]->BuyerUserID[0];
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
		$VPN=$xml->OrderArray[0]->Order[$i]->ShippingDetails[0]->SellingManagerSalesRecordNumber[0];
		$j++;
		$sql[$j]["name"]="CreatedTime";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->CreatedTime[0];
		$j++;
		$sql[$j]["name"]="CreatedTimeTimestamp";
		$sql[$j]["value"]=strtotime($xml->OrderArray[0]->Order[$i]->CreatedTime[0]);
		$order_created_time=strtotime($xml->OrderArray[0]->Order[$i]->CreatedTime[0]);
		$j++;
		$sql[$j]["name"]="OrderID";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->OrderID[0];
		$OrderID=$sql[$j]["value"];
		$j++;
		$sql[$j]["name"]="OrderStatus";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->OrderStatus[0];
		$orderStatus=$sql[$j]["value"];
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
		$shippingAddressStreet1=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Street1[0];

		$j++;
		$sql[$j]["name"]="ShippingAddressStreet2";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Street2[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressCityName";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->CityName[0];
		$shippingAddressCityName=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->CityName[0];

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
		$item_row=array();
		foreach($xml->OrderArray[0]->Order[$i]->TransactionArray[0]->Transaction as $transaction)
		{
			$l=0;
			$t_sql[$k][$l]["name"]="account_id";
			$t_sql[$k][$l]["value"]=$account_site["account_id"];
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
			$itemID[$k]=$transaction->Item[0]->ItemID[0];
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
			$t_sql[$k][$l]["name"]="Currency_Code";
			$t_sql[$k][$l]["value"]=$transaction->TransactionPrice[0]["currencyID"];

			$l++;
			$t_sql[$k][$l]["name"]="UnpaidItem";
			$t_sql[$k][$l]["value"]=$transaction->UnpaidItem[0];
		
			$l++;
			$t_sql[$k][$l]["name"]="OrderLineItemID";
			$t_sql[$k][$l]["value"]=$transaction->OrderLineItemID[0];
			$orderLineItemID[$k]=$t_sql[$k][$l]["value"];
			$l++;
			$t_sql[$k][$l]["name"]="OrderID";
			$t_sql[$k][$l]["value"]=$OrderID;
			
			//SEARCH FOR ALREADY EXISTING TRANSACTION
			$trans_check_res=q("SELECT * FROM ebay_orders_items WHERE TransactionID='".$transactionID[$k]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($trans_check_res)>0)
			{
				$trans_check_row[$k]=mysqli_fetch_array($trans_check_res);
				$transaction_old[$k]["TransactionID"]=$transactionID[$k];
				//GET ACCORDING ORDER
				$order_check_res=q("SELECT * FROM ebay_orders WHERE OrderID = '".$trans_check_row[$k]["OrderID"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($order_check_res)>0)
				{
					$row=mysqli_fetch_array($order_check_res);
					$order_check_row[$row["OrderID"]]=$row;
					$transaction_old[$k]["OrderID"]=$trans_check_row[$k]["OrderID"];
					$transaction_old[$k]["CreatedTimeTimestamp"]=$row["CreatedTimeTimestamp"];
				}
				else
				{
					$transaction_old[$k]["OrderID"]="0";
					$transaction_old[$k]["CreatedTimeTimestamp"]=0;
				}
			}
			else
			{
				$transaction_old[$k]["TransactionID"]="";
				$transaction_old[$k]["OrderID"]="0";
				$transaction_old[$k]["CreatedTimeTimestamp"]=0;
			}
			
			
			$k++;
		} // FOREACH TRANSACTIONS

		//CHECK OB EINGELESENE ORDER NEUER ALS DIE ALTEN ZU DEN TRANSACTIONEN VORHANDENEN IST
		$newer=true;
	/*	
		for ($k=0; $k<sizeof($t_sql); $k++)
		{
			if ($order_created_time<$transaction_old[$k]["CreatedTimeTimestamp"]) 
			{
				$newer=false;
				//echo "O L D E R : ".$transaction_old[$k]["OrderID"]."<br/>";
			}
		}
	*/	
		
		//SUCHE unterschiedliche OrderIDs
		$old_orderids=array();
		$l=0;
		$firstorder_old="0";
		for ($k=0; $k<sizeof($t_sql); $k++)
		{
			if (!in_array($transaction_old[$k]["OrderID"], $old_orderids))
			{
				$old_orderids[$l]=$transaction_old[$k]["OrderID"];
				if ($firstorder_old=="0" && $transaction_old[$k]["OrderID"]!="0") $firstorder_old=$transaction_old[$k]["OrderID"];
				$l++;
			}
		}
					
		$newer=true;
		if ($newer)
		{
		
			//INSERT/Update ORDER ITEMS
			for ($k=0; $k<sizeof($t_sql); $k++)
			{
				//INSERT
				if ($transaction_old[$k]["TransactionID"]=="")
				{
	//echo "insert new transaction<br/>";
					$lines=sizeof($t_sql[$k]);
					$t_sql[$k][$lines]["name"]="firstmod";
					$t_sql[$k][$lines]["value"]=time();
					$lines++;
					$t_sql[$k][$lines]["name"]="firstmod_user";
					$t_sql[$k][$lines]["value"]=$_SESSION["id_user"];
					$lines++;
					$t_sql[$k][$lines]["name"]="lastmod";
					$t_sql[$k][$lines]["value"]=time();
					$lines++;
					$t_sql[$k][$lines]["name"]="lastmod_user";
					$t_sql[$k][$lines]["value"]=$_SESSION["id_user"];
				
					$query="INSERT INTO ebay_orders_items (";
					for ($l=0; $l<sizeof($t_sql[$k]); $l++) {
						$query.=$t_sql[$k][$l]["name"];
						if ( ($l+1)<sizeof($t_sql[$k]) ) $query.=", ";
					}
					$query.=") VALUES(";
					for ($l=0; $l<sizeof($t_sql[$k]); $l++) {
						$query.="'".mysqli_real_escape_string($dbshop, $t_sql[$k][$l]["value"])."'";
						if ( ($l+1)<sizeof($t_sql[$k]) ) $query.=", ";
					}
					$query.=");";
					//echo "INSERT <br />";
					q($query, $dbshop, __FILE__, __LINE__);
					
				}
				//UPDATE
				else
				{
					//VERGLEICHE ALTE MIT NEUEN ORDERITEMS
					$t_equals=true;
					
					for ($m=0; $m<sizeof($t_sql[$k]); $m++)
					{
						if ($t_sql[$k][$m]["value"]!=$trans_check_row[$k][$t_sql[$k][$m]["name"]]) $t_equals=false;
						//echo "+".$t_sql[$k][$m]["value"]."------".$item_row[$k][$t_sql[$k][$m]["name"]]."+<br />";
					}
					
					//bei abweichenden Transaktionsdaten
					if (!$t_equals)
					{
						
						$orderUpdate_from_Item=true;
						//echo "Abweichendes OrderItem ".$transaction_old[$k]["TransactionID"]."<br />";
						$lines=sizeof($t_sql[$k]);
						$t_sql[$k][$lines]["name"]="lastmod";
						$t_sql[$k][$lines]["value"]=time();
						$lines++;
						$t_sql[$k][$lines]["name"]="lastmod_user";
						$t_sql[$k][$lines]["value"]=$_SESSION["id_user"];
		
						$query="UPDATE ebay_orders_items SET ";
						for($l=0; $l<sizeof($t_sql[$k]); $l++)
						{
							//E-MAIL NICHT ÜBERSCHREIBEN
							if ($t_sql[$k][$l]["name"]!="BuyerEmail") {
								$query.=$t_sql[$k][$l]["name"]."='".mysqli_real_escape_string($dbshop, $t_sql[$k][$l]["value"])."'";
								if ( ($l+1)<sizeof($t_sql[$k]) ) $query.=", ";
							}
						}
						$query.=" WHERE TransactionID='".$transaction_old[$k]["TransactionID"]."';";
						//					echo "UPDATE <br />";
						q($query, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						//echo "UPDATE: keine geänderten Daten zur Transaction ".$transaction_old[$k]["TransactionID"]."<br />";
					}
				} // else (Update)
			} // for ()Insert/update)
			
//ORDERS ************************************************************************************************************************

			if ($firstorder_old!="0")
			{ //Upadate Order
				//GET ID_ORDER
				
				if (isset($order_check_row[$firstorder_old]))
				{
					$equals=true;
					//VERGLEICHE ALTE MIT NEUEN ORDERDATEN
					for ($m=0; $m<sizeof($sql); $m++)
					{
						//KREDITKARTEN BLA aus ABWEICHUNGSPRÜFUNG AUSKLAMMERN
						if ($sql[$m]["name"]!="CheckoutStatusIntegratedMerchantCreditCardEnabled")
						{
							if ($sql[$m]["value"]!=$order_check_row[$firstorder_old][$sql[$m]["name"]]) $equals=false;
							//echo $sql[$m]["value"]."-----".$row_check[$sql[$m]["name"]]."<br />";
						}
					}
				}
				else 
				{
					$equals=false;
				}
				$equals=false;
				//WENN ORDERDATEN oder TRANSACTIONDATEN abweichen, dann UPDATE
				if (!$equals || $orderUpdate_from_Item)
				{
				//echo "UPDATE ORDER# ".$firstorder_old."<br />";
					$res=q("SELECT * FROM ebay_orders WHERE OrderID = '".$firstorder_old."';",$dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($res);

					$j=sizeof($sql);
					$sql[$j]["name"]="lastmod";
					$sql[$j]["value"]=time();
					$j++;
					$sql[$j]["name"]="lastmod_user";
					$sql[$j]["value"]=$_SESSION["id_user"];
					
					$query="UPDATE ebay_orders SET ";
					for ($j=0;$j<sizeof($sql); $j++)
					{
						$query.=$sql[$j]["name"]." = '".mysqli_real_escape_string($dbshop, $sql[$j]["value"])."'";
						if ( ($j+1)<sizeof($sql) ) $query.=", ";
					}
					$query.=" WHERE OrderID = '".$firstorder_old."';";
					//			echo "+UDATE<br />";
					q($query, $dbshop, __FILE__, __LINE__);
			
			
				//ALLE ANDEREN ALTEN ORDERS LÖSCHEN
				
					for ($k=0; $k<sizeof($old_orderids);$k++)
					{
						if ($old_orderids[$k]!=$firstorder_old)
						{ 
							q("DELETE FROM ebay_orders WHERE OrderID = '".$old_orderids[$k]."';", $dbshop, __FILE__, __LINE__);
						//	echo "DELETE OLD ORDERS: ".$old_orderids[$k]."<br />";
						}
					}
				/*	
					$res=q("SELECT * FROM ebay_orders WHERE OrderID = '".$firstorder_old."';",$dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($res);
				*/
				//SHOP ORDER UPDATE
				/*
					$varField=array();
					$varField["API"]="crm";
					$varField["Action"]="import_ebayOrderData";
					$varField["mode"]="update";
					//$varField["EbayOrderID"]=$firstorder_old;
					$varField["EbayOrderID"]=$row["id_order"];
				
					echo post(PATH."soa/", $varField);
				*/
				echo "add".	post(PATH."soa2/", array("API" => "crm", "APIRequest" => "EbayOrderImport", "EbayOrderID" => $row["id_order"] ));
				
				}
				else
				{
					//echo "No Change for Order ".$OrderID."<br />";
				}
			} // IF mysqli_num_rows($res_check)>0
			
			// NEUE ORDER
			else
			{
	//echo "ADD ORDER: ".$OrderID."<br />";
				//ADD COMPLATE NEW ORDER
				$j=sizeof($sql);
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
			//						echo "INSERT <br />";
				q($query, $dbshop, __FILE__, __LINE__);
				
				$id_order=mysqli_insert_id($dbshop);
				
				/*
				$varField["API"]="crm";
				$varField["Action"]="import_ebayOrderData";
				$varField["mode"]="add";
				$varField["EbayOrderID"]=$id_order;
				echo post(PATH."soa/", $varField);
				*/
			echo "add". post(PATH."soa2/", array("API" => "crm", "APIRequest" => "EbayOrderImport", "EbayOrderID" => $id_order ));
			
			}
			
		
		} // newer
		
	} //FOR ISSET ORDER[$i]
} // WHILE has more orders


//***********************************************************************************
// SEARCH FOR MODIFIED ORDERS
//***********************************************************************************

//echo "<br />MODIFIED ORDERS: <br />";


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
	$requestXmlBody .= '  <ModTimeFrom>'.date('Y-m-d\TH:i:s.000\Z', $_POST["from"]).'</ModTimeFrom>';
	$requestXmlBody .= '  <ModTimeTo>'.date('Y-m-d\TH:i:s.000\Z', $_POST["to"]).'</ModTimeTo>';
	$requestXmlBody .= '  <OrderRole>Seller</OrderRole>';
	$requestXmlBody .= '  <Pagination>';
	$requestXmlBody .= '	<EntriesPerPage>100</EntriesPerPage>';
	$requestXmlBody .= '	<PageNumber>'.$requestPage.'</PageNumber>';
	$requestXmlBody .= '  </Pagination>';
	$requestXmlBody .= '</GetOrdersRequest>';
	
	//submit auction
$response = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "GetOrders", "id_account" => $account_site["account_id"], "request" => $requestXmlBody));
//echo "UPDATE".$response;
	//read orders
	$xml = new SimpleXMLElement($response);
	$resultPageNumber = $xml->PageNumber[0];
	$resultPageOrderCount = $xml->ReturnedOrderCountActual[0];
	$resultTotalNumberOfEntries = $xml->PaginationResult[0]->TotalNumberOfEntries[0];

	if ($xml->HasMoreOrders[0]=="true")	{$resultHasMoreOrders = true;} else {$resultHasMoreOrders = false;}
	
	for($i=0; isset($xml->OrderArray[0]->Order[$i]); $i++)
	{
		$sql=array();
		$orderUpdate_from_Item=false;

		$transaction_old=array();

		$j=0;
		$sql[$j]["name"]="account_id";
		$sql[$j]["value"]=$account_site["account_id"];
		$j++;
		$sql[$j]["name"]="AdjustmentAmount";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AdjustmentAmount[0];
		$j++;
		$sql[$j]["name"]="AmountPaid";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AmountPaid[0];
		
		$j++;
		$sql[$j]["name"]="Currency_Code";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AmountPaid[0]["currencyID"];
	

		$j++;
		$sql[$j]["name"]="AmountSaved";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->AmountSaved[0];
		$j++;
		$sql[$j]["name"]="BuyerUserID";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->BuyerUserID[0];
		$BuyerID=$xml->OrderArray[0]->Order[$i]->BuyerUserID[0];
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
		$VPN=$xml->OrderArray[0]->Order[$i]->ShippingDetails[0]->SellingManagerSalesRecordNumber[0];
		$j++;
		$sql[$j]["name"]="CreatedTime";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->CreatedTime[0];
		$j++;
		$sql[$j]["name"]="CreatedTimeTimestamp";
		$sql[$j]["value"]=strtotime($xml->OrderArray[0]->Order[$i]->CreatedTime[0]);
		$order_created_time=strtotime($xml->OrderArray[0]->Order[$i]->CreatedTime[0]);
		$j++;
		$sql[$j]["name"]="OrderID";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->OrderID[0];
		$OrderID=$sql[$j]["value"];
		$j++;
		$sql[$j]["name"]="OrderStatus";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->OrderStatus[0];
		$orderStatus=$sql[$j]["value"];
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
		$shippingAddressStreet1=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Street1[0];

		$j++;
		$sql[$j]["name"]="ShippingAddressStreet2";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->Street2[0];
		$j++;
		$sql[$j]["name"]="ShippingAddressCityName";
		$sql[$j]["value"]=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->CityName[0];
		$shippingAddressCityName=$xml->OrderArray[0]->Order[$i]->ShippingAddress[0]->CityName[0];

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
		$item_row=array();
		foreach($xml->OrderArray[0]->Order[$i]->TransactionArray[0]->Transaction as $transaction)
		{
			$l=0;
			$t_sql[$k][$l]["name"]="account_id";
			$t_sql[$k][$l]["value"]=$account_site["account_id"];
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
			$itemID[$k]=$transaction->Item[0]->ItemID[0];
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
			$t_sql[$k][$l]["name"]="Currency_Code";
			$t_sql[$k][$l]["value"]=$transaction->TransactionPrice[0]["currencyID"];

			$l++;
			$t_sql[$k][$l]["name"]="UnpaidItem";
			$t_sql[$k][$l]["value"]=$transaction->UnpaidItem[0];

		
			$l++;
			$t_sql[$k][$l]["name"]="OrderLineItemID";
			$t_sql[$k][$l]["value"]=$transaction->OrderLineItemID[0];
			$orderLineItemID[$k]=$t_sql[$k][$l]["value"];
			$l++;
			$t_sql[$k][$l]["name"]="OrderID";
			$t_sql[$k][$l]["value"]=$OrderID;
			
			//SEARCH FOR ALREADY EXISTING TRANSACTION
			$trans_check_res=q("SELECT * FROM ebay_orders_items WHERE TransactionID='".$transactionID[$k]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($trans_check_res)>0)
			{
				$trans_check_row[$k]=mysqli_fetch_array($trans_check_res);
				$transaction_old[$k]["TransactionID"]=$transactionID[$k];
				//GET ACCORDING ORDER
				$order_check_res=q("SELECT * FROM ebay_orders WHERE OrderID = '".$trans_check_row[$k]["OrderID"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($order_check_res)>0)
				{
					$row=mysqli_fetch_array($order_check_res);
					$order_check_row[$row["OrderID"]]=$row;
					$transaction_old[$k]["OrderID"]=$trans_check_row[$k]["OrderID"];
					$transaction_old[$k]["CreatedTimeTimestamp"]=$row["CreatedTimeTimestamp"];
				}
				else
				{
					$transaction_old[$k]["OrderID"]="0";
					$transaction_old[$k]["CreatedTimeTimestamp"]=0;
				}
			}
			else
			{
				$transaction_old[$k]["TransactionID"]="";
				$transaction_old[$k]["OrderID"]="0";
				$transaction_old[$k]["CreatedTimeTimestamp"]=0;
			}
			
			
			$k++;
		} // FOREACH TRANSACTIONS
		
		//CHECK OB EINGELESENE ORDER NEUER ALS DIE ALTEN ZU DEN TRANSACTIONEN VORHANDENEN IST
		$newer=true;
	/*	
		for ($k=0; $k<sizeof($t_sql); $k++)
		{
			if ($order_created_time<$transaction_old[$k]["CreatedTimeTimestamp"]) 
			{
				$newer=false;
			//	echo "O L D E R : ".$transaction_old[$k]["OrderID"]."<br/>";
			}
		}
	*/	
		
		//SUCHE unterschiedliche OrderIDs
		$old_orderids=array();
		$l=0;
		$firstorder_old="0";
		for ($k=0; $k<sizeof($t_sql); $k++)
		{
			if (!in_array($transaction_old[$k]["OrderID"], $old_orderids))
			{
				$old_orderids[$l]=$transaction_old[$k]["OrderID"];
				if ($firstorder_old=="0" && $transaction_old[$k]["OrderID"]!="0") $firstorder_old=$transaction_old[$k]["OrderID"];
				$l++;
			}
		}
					
		$newer=true;
		if ($newer)
		{
		
			//INSERT/Update ORDER ITEMS
			for ($k=0; $k<sizeof($t_sql); $k++)
			{
				//INSERT
				if ($transaction_old[$k]["TransactionID"]=="")
				{
	//echo "insert new transaction<br/>";
					$lines=sizeof($t_sql[$k]);
					$t_sql[$k][$lines]["name"]="firstmod";
					$t_sql[$k][$lines]["value"]=time();
					$lines++;
					$t_sql[$k][$lines]["name"]="firstmod_user";
					$t_sql[$k][$lines]["value"]=$_SESSION["id_user"];
					$lines++;
					$t_sql[$k][$lines]["name"]="lastmod";
					$t_sql[$k][$lines]["value"]=time();
					$lines++;
					$t_sql[$k][$lines]["name"]="lastmod_user";
					$t_sql[$k][$lines]["value"]=$_SESSION["id_user"];
				
					$query="INSERT INTO ebay_orders_items (";
					for ($l=0; $l<sizeof($t_sql[$k]); $l++) {
						$query.=$t_sql[$k][$l]["name"];
						if ( ($l+1)<sizeof($t_sql[$k]) ) $query.=", ";
					}
					$query.=") VALUES(";
					for ($l=0; $l<sizeof($t_sql[$k]); $l++) {
						$query.="'".mysqli_real_escape_string($dbshop, $t_sql[$k][$l]["value"])."'";
						if ( ($l+1)<sizeof($t_sql[$k]) ) $query.=", ";
					}
					$query.=");";
				//		echo "INSERT <br />";
					q($query, $dbshop, __FILE__, __LINE__);
					
				}
				//UPDATE
				else
				{
					//VERGLEICHE ALTE MIT NEUEN ORDERITEMS
					$t_equals=true;
					
					for ($m=0; $m<sizeof($t_sql[$k]); $m++)
					{
						if ($t_sql[$k][$m]["value"]!=$trans_check_row[$k][$t_sql[$k][$m]["name"]]) $t_equals=false;
						//echo "+".$t_sql[$k][$m]["value"]."------".$item_row[$k][$t_sql[$k][$m]["name"]]."+<br />";
					}
					
					//bei abweichenden Transaktionsdaten
					if (!$t_equals)
					{
						
						$orderUpdate_from_Item=true; //markieren, dass die Order geupdatet werden muss
					//	echo "Abweichendes OrderItem ".$transaction_old[$k]["TransactionID"]."<br />";
						$lines=sizeof($t_sql[$k]);
						$t_sql[$k][$lines]["name"]="lastmod";
						$t_sql[$k][$lines]["value"]=time();
						$lines++;
						$t_sql[$k][$lines]["name"]="lastmod_user";
						$t_sql[$k][$lines]["value"]=$_SESSION["id_user"];
		
						$query="UPDATE ebay_orders_items SET ";
						for($l=0; $l<sizeof($t_sql[$k]); $l++)
						{
							//E-MAIL NICHT ÜBERSCHREIBEN
							if ($t_sql[$k][$l]["name"]!="BuyerEmail") {
								$query.=$t_sql[$k][$l]["name"]."='".mysqli_real_escape_string($dbshop, $t_sql[$k][$l]["value"])."'";
								if ( ($l+1)<sizeof($t_sql[$k]) ) $query.=", ";
							}
						}
						$query.=" WHERE TransactionID='".$transaction_old[$k]["TransactionID"]."';";
			//								echo "UPDATE <br />";
						q($query, $dbshop, __FILE__, __LINE__);
					}
					else
					{
						//echo "UPDATE: keine geänderten Daten zur Transaction ".$transaction_old[$k]["TransactionID"]."<br />";
					}
				} // else (Update)
			} // for ()Insert/update)
			
//ORDERS ************************************************************************************************************************

			if ($firstorder_old!="0")
			{ //Upadate Order
				//GET ID_ORDER
				
				
				if (isset($order_check_row[$firstorder_old]))
				{
					$equals=true;
					//VERGLEICHE ALTE MIT NEUEN ORDERDATEN
					for ($m=0; $m<sizeof($sql); $m++)
					{
						//KREDITKARTEN BLA aus ABWEICHUNGSPRÜFUNG AUSKLAMMERN
						if ($sql[$m]["name"]!="CheckoutStatusIntegratedMerchantCreditCardEnabled")
						{
							if ($sql[$m]["value"]!=$order_check_row[$firstorder_old][$sql[$m]["name"]]) $equals=false;
							//echo $sql[$m]["value"]."-----".$row_check[$sql[$m]["name"]]."<br />";
						}
					}
				}
				else 
				{
					$equals=false;
				}
				$equals=false;
				//WENN ORDERDATEN oder TRANSACTIONDATEN abweichen, dann UPDATE
				if (!$equals || $orderUpdate_from_Item)
				{
					$res=q("SELECT * FROM ebay_orders WHERE OrderID = '".$firstorder_old."';",$dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($res);
				//echo "UPDATE ORDER# ".$firstorder_old."<br />";
					$j=sizeof($sql);
					$sql[$j]["name"]="lastmod";
					$sql[$j]["value"]=time();
					$j++;
					$sql[$j]["name"]="lastmod_user";
					$sql[$j]["value"]=$_SESSION["id_user"];
					
					$query="UPDATE ebay_orders SET ";
					for ($j=0;$j<sizeof($sql); $j++)
					{
						$query.=$sql[$j]["name"]." = '".mysqli_real_escape_string($dbshop, $sql[$j]["value"])."'";
						if ( ($j+1)<sizeof($sql) ) $query.=", ";
					}
	//	$query.=" WHERE OrderID = '".$OrderID."';";
				$query.=" WHERE OrderID = '".$firstorder_old."';";
				//				echo "UPDATE <br />";
					q($query, $dbshop, __FILE__, __LINE__);
			
			
				
				//SHOP ORDER UPDATE
				/*
					$varField=array();
					$varField["API"]="crm";
					$varField["Action"]="import_ebayOrderData";
					$varField["mode"]="update";
					//$varField["EbayOrderID"]=$firstorder_old;
					$varField["EbayOrderID"]=$row["id_order"];
				
					echo		post(PATH."soa/", $varField);
				*/
			
			
			echo "update". post(PATH."soa2/", array("API" => "crm", "APIRequest" => "EbayOrderImport", "EbayOrderID" => $row["id_order"] ));	
			
				//ALLE ANDEREN ALTEN ORDERS LÖSCHEN
				
					for ($k=0; $k<sizeof($old_orderids);$k++)
					{
						if ($old_orderids[$k]!=$firstorder_old)
						{ 
							q("DELETE FROM ebay_orders WHERE OrderID = '".$old_orderids[$k]."';", $dbshop, __FILE__, __LINE__);
					//		echo "DELETE OLD ORDERS: ".$old_orderids[$k]."<br />";
						}
					}
				}
				else
				{
					//echo "No Change for Order ".$OrderID."<br />";
				}
			} // IF mysqli_num_rows($res_check)>0
			
			// NEUE ORDER
			else
			{
	//echo "ADD ORDER: ".$OrderID."<br />";
				//ADD COMPLATE NEW ORDER
				$j=sizeof($sql);
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
			//						echo "INSERT <br />";
				q($query, $dbshop, __FILE__, __LINE__);

				$id_order=mysqli_insert_id($dbshop);
			/*
				$varField["API"]="crm";
				$varField["Action"]="import_ebayOrderData";
				$varField["mode"]="add";
				$varField["EbayOrderID"]=$id_order;
				echo 	post(PATH."soa/", $varField);
			*/
			
				echo "update".post(PATH."soa2/", array("API" => "crm", "APIRequest" => "EbayOrderImport", "EbayOrderID" => $id_order ));
			
			}
			
		
		} // newer
		
	} //FOR ISSET ORDER[$i]
} // WHILE has more orders

//COMBINE orders
	$res_shops=q("SELECT * FROM shop_shops WHERE shop_type = 2 AND account_id = ".$account_site["account_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_shops)==1)
	{
		$shop=mysqli_fetch_array($res_shops);	
//		echo "<br />combine orders<br />";
		echo post(PATH."soa/", array("API" => "crm", "Action" => "combine_orders", "mode" => "scan", "shop_id" => $shop["id_shop"]));
	}

	//return success
	echo '<Get_Ebay_OrdersResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<TotalOrders>'.$resultTotalNumberOfEntries.'</TotalOrders>'."\n";
	echo '</Get_Ebay_OrdersResponse>'."\n";
?>