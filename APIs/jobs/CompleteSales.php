<?php
	$starttime=time()+microtime();

	$upload_counter=0;
	
	$results=q("SELECT * FROM shop_orders WHERE shipping_number!='' AND ebay_tracking=0 AND (shop_id = 3 OR shop_id = 4 OR shop_id = 5);", $dbshop, __FILE__, __LINE__);
	echo "ANZAHL Tracking IDs zum hochladen: ".mysqli_num_rows($results)."<br />";
	while( $order[0]=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM shop_shops WHERE id_shop=".$order[0]["shop_id"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results2)==0 )
		{
			echo '<CompleteSalesResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Shop nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Der in der Bestellung angegebene Shop (shop_id) ist ungültig.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CompleteSalesResponse>'."\n";
			exit;
		}
		$shop=mysqli_fetch_array($results2);
		/*
		if( $shop["shop_type"]!=2 )
		{
			echo '<CompleteSalesResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Shop ist kein eBay-Shop.</shortMsg>'."\n";
			echo '		<longMsg>Der in der Bestellung angegebene Shop (shop_id) ist kein eBay-Shop.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CompleteSalesResponse>'."\n";
			exit;
		}
		*/
		if ( $shop["shop_type"]==2 )
		{
			
			$results2=q("SELECT * FROM ebay_accounts WHERE id_account=".$shop["account_id"].";", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results2)==0 )
			{
				echo '<CompleteSalesResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>eBay-Account nicht gefunden.</shortMsg>'."\n";
				echo '		<longMsg>Der im Shop (shop_id) angegebene eBay-Account (account_id) ist nicht gültig.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</CompleteSalesResponse>'."\n";
			//	exit;
			}
			else
			{
				$account=mysqli_fetch_array($results2);
				
			$trackingID=$order[0]["shipping_number"];
//			echo "TRACKID";
				$combined_with=$order[0]["combined_with"];
				$status_id=$order[0]["status_id"];
				
				//find combined orders
				if ($combined_with>0)
				{
					$res=q("SELECT * FROM shop_orders WHERE combined_with = ".$combined_with." AND NOT id_order = ".$order[0]["id_order"].";", $dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($res);
					$order[]=$row;
				}
		
				foreach ($order as $orderline)
				{
					//Only if Order has foreign_OrderID (check, because of man. add. Orders(items))
					if ($orderline["foreign_OrderID"]!="")
					{
						//create XML
						$requestXmlBody  = '<?xml version="1.0" encoding="UTF-8"?>';
						$requestXmlBody .= '	<CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
						$requestXmlBody .= '	<RequesterCredentials>';
						$requestXmlBody .= '		<eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
						$requestXmlBody .= '	</RequesterCredentials>';
						$requestXmlBody .= '	<WarningLevel>High</WarningLevel>';
						$requestXmlBody .= '	<OrderID>'.$orderline["foreign_OrderID"].'</OrderID>';
						
						$requestXmlBody .= '	<Shipment>';
						$requestXmlBody .= '		<ShipmentTrackingDetails>';
						//send tracking id to mother order only
						if( $orderline["id_order"]==$orderline["combined_with"] || $orderline["combined_with"]==0)
						{
							$requestXmlBody .= '			<ShipmentTrackingNumber>'.$trackingID.'</ShipmentTrackingNumber>';
						}
						$requestXmlBody .= '			<ShippingCarrierUsed>DHL</ShippingCarrierUsed>';
						$requestXmlBody .= '		</ShipmentTrackingDetails>';
						$requestXmlBody .= '	</Shipment>';
						
						$requestXmlBody .= '	<Shipped>true</Shipped>';
						$requestXmlBody .= '</CompleteSaleRequest>';

						//submit XML
						//$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "CompleteSale", "id_account" => $account["id_account"], "request" => $requestXmlBody));
						$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "CompleteSale", "id_account" => $account["id_account"], "request" => $requestXmlBody));
						//echo $responseXml;
						$use_errors = libxml_use_internal_errors(true);
						try
						{
							$response = new SimpleXMLElement($responseXml);
						}
						catch(Exception $e)
						{
							echo '<CompleteSalesResponse>'."\n";
							echo '	<Ack>Failure</Ack>'."\n";
							echo '	<Error>'."\n";
							echo '		<Code>'.__LINE__.'</Code>'."\n";
							echo '		<shortMsg>Sendungsnummer hochladen fehlgeschlagen.</shortMsg>'."\n";
							echo '		<longMsg>Beim Hochladen einer Sendungsnummer ('.$orderline["shipping_number"].') ist ein Fehler aufgetreten.</longMsg>'."\n";
							echo 'TRACKING ID: '.$trackingID."\n";
							echo $responseXml;
							echo '	</Error>'."\n";
							echo '</CompleteSalesResponse>'."\n";
						//	exit;
						}
						libxml_clear_errors();
						libxml_use_internal_errors($use_errors);

						$OK=false;
						if( $response->Ack[0] == "Success" )
						{
							$OK = true;
						}
						elseif (isset($response->Errors[0]))
						{
							//flag already uploaded tracking-id as finished
							if ($response->Errors[0]->ErrorCode[0]=="21916964") $OK=true;
							//flag already for combined auction uploaded tracking id as finished
							elseif ($response->Errors[0]->ErrorCode[0]=="21919089") $OK=true;
							//show every other error
							else
							{
								echo '<Error>';
								echo '	<id_order>'.$order[0]["id_order"].'</id_order>';
								echo '	<Response><![CDATA['.$responseXml.']]></Response>';
								echo '</Error>';
								$OK=false;
							}
						}
					
						if ($OK)
						{
							q("UPDATE shop_orders SET ebay_tracking=1, shipping_number = '".$trackingID."' , status_id = ".$status_id." WHERE id_order=".$orderline["id_order"].";", $dbshop, __FILE__, __LINE__);
							$upload_counter++;
							echo '<OrderUpdated>'.$orderline["foreign_OrderID"].'</OrderUpdated>';
						}
					}
					else
					//Order has no foreign_OrderID
					{
							q("UPDATE shop_orders SET ebay_tracking=1, shipping_number = '".$trackingID."' , status_id = ".$status_id." WHERE id_order=".$orderline["id_order"].";", $dbshop, __FILE__, __LINE__);
							echo '<OrderUpdated>'.$orderline["id_order"].'</OrderUpdated>';
					}
					$stoptime=time()+microtime();
					if( ($stoptime-$starttime) > 60 ) break;
				} // FOREACH
			}
		} //if ( $shop["shop_type"]==2 )
		
		unset ($order);
		$stoptime=time()+microtime();
		
		if( ($stoptime-$starttime) > 60 ) break;
	} //while( $order[0]=mysqli_fetch_array($results) )
	
	//return success
	echo '<CompleteSalesResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Uploads>'.$upload_counter.'</Uploads>'."\n";
	echo '</CompleteSalesResponse>'."\n";

?>