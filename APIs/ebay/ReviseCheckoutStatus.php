<?php

		if ( !isset($_POST["id_order"]) )
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Bestellnummer nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss ein Bestellnummer (id_order) übermittelt werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Bestellung nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Die Bestellung mit der Nummer '.$_POST["id_order"].' konnte nicht gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		$order=mysqli_fetch_array($results);
	
		$results=q("SELECT * FROM shop_shops WHERE id_shop=".$order["shop_id"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Shop nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Der in der Bestellung angegebene Shop (shop_id) ist ungültig.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		$shop=mysqli_fetch_array($results);
		if( $shop["shop_type"]!=2 )
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Shop ist kein eBay-Shop.</shortMsg>'."\n";
			echo '		<longMsg>Der in der Bestellung angegebene Shop (shop_id) ist kein eBay-Shop.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		
		$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$shop["account_id"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>eBay-Account nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Der im Shop (shop_id) angegebene eBay-Account (account_id) ist nicht gültig.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		$account=mysqli_fetch_array($results);


// MODE PAYMENTUPDATE
	if (isset($_POST["mode"]) && $_POST["mode"] == "paymentupdate")
	{
		if ( !isset($_POST["paymentmode"]) )
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Modus nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss angegeben werden, ob eine Zahlung erhalten (payment) oder gesendet wurde (refund).</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		
		//ES KÖNNEN NUR ÜBERWEISUNGEN ÜBERMITTELT WERDEN
		if ($order["payments_type_id"]!=2 && $order["payments_type_id"]!=3)
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Zahlmethode bei Ebay nicht bearbeitbar.</shortMsg>'."\n";
			echo '		<longMsg>Es können bei Ebay nur Überweisungen und Nachnahmen durch das Bestellmanagement bearbeitet werden</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
	
		
			//GET PAYMENT TYPE info
			if ($order["payments_type_id"]!=0)
			{
				$res_payments=q("SELECT * FROM shop_payment_types WHERE id_paymenttype = ".$order["payments_type_id"].";",  $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($res_payments)==0 )
				{
					echo '<ReviseCheckoutStatusResponse>'."\n";
					echo '	<Ack>Failure</Ack>'."\n";
					echo '	<Error>'."\n";
					echo '		<Code>'.__LINE__.'</Code>'."\n";
					echo '		<shortMsg>Zahlungstyp nicht gefunden.</shortMsg>'."\n";
					echo '		<longMsg>Zur Zahlungstyp ID konnte keine Zahlart gefunden werden.</longMsg>'."\n";
					echo '	</Error>'."\n";
					echo '</ReviseCheckoutStatusResponse>'."\n";
					exit;
				}
				$payment=mysqli_fetch_array($res_payments);
			}
			else {$payment["method"]=0;}
		
			if ( $order["Payments_TransactionStateDate"]==0 && $order["payments_type_id"]!=0 )
			{
				echo '<ReviseCheckoutStatusResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Zahldatum nicht gefunden.</shortMsg>'."\n";
				echo '		<longMsg>Es muss ein Zahldatum übermittelt werden.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</ReviseCheckoutStatusResponse>'."\n";
				exit;
			}
		
		
		
			if ( $order["Payments_TransactionID"]=="" && $payment["method"]==2 )
			{
				echo '<ReviseCheckoutStatusResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>PaymentsTransactionID nicht gefunden.</shortMsg>'."\n";
				echo '		<longMsg>Zur angegebenen Zahlart muss eine Zahlungstransaktionsnummer übermittelt werden.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</ReviseCheckoutStatusResponse>'."\n";
				exit;
			}
		
			if ( !isset($_POST["amount"]))
			{
				echo '<ReviseCheckoutStatusResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Zahlungssumme nicht gefunden</shortMsg>'."\n";
				echo '		<longMsg>Es muss eine Zahlungssumme (oder 0.00) übermittelt werden.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</ReviseCheckoutStatusResponse>'."\n";
				exit;
			}
		//echo "PAYMENT:".$order["payments_type_id"];
			if ($_POST["paymentmode"]=="refund")
			{
				$paymenttype="Other";
				$amount="0.00";
				$paymentstatus="Pending";
				$paymenttransactionid="SIS";
				
				$paymentstatus="false";
				$paymentstatus2="Pending";
				$orderstatus="Incomplete";

			}
			else 
			{
				$paymenttype=$payment["PaymentMethod"];	
//				$paymenttype="MoneyXferAcceptedInCheckout";
				$amount=number_format($_POST["amount"], 2, ".", ",");
				$paymentstatus="Paid";
				$paymenttransactionid="SIS";
	
				$paymentstatus="true";
				$paymentstatus2="Paid";
				$orderstatus="Complete";
	
			}
			
			//SHIPPINGSERVICE
			$shippingservice="";
			$res_shippingservice=q("SELECT * FROM shop_shipping_types WHERE id_shippingtype = ".$order["shipping_type_id"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_shippingservice)==1)
			{
				$row_shippingservice=mysqli_fetch_array($res_shippingservice);
				$shippingservice=$row_shippingservice["ShippingServiceType"];
			}
		
			$name="";
			if ($order["bill_company"]!="") $name.=$order["bill_company"];
			if ($order["bill_firstname"]!="")
			{
				if ($name!="") $name.=" ";
				$name.=$order["bill_firstname"];
			}
			if ($order["bill_lastname"]!="")
			{
				if ($name!="") $name.=" ";
				$name.=$order["bill_lastname"];
			}
			$street1=$order["bill_street"]." ".$order["bill_number"];
			
			
			$requestXmlBody  = '<?xml version="1.0" encoding="UTF-8"?>';
			$requestXmlBody .= '	<ReviseCheckoutStatusRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			$requestXmlBody .= '	<RequesterCredentials>';
			$requestXmlBody .= '		<eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
			$requestXmlBody .= '	</RequesterCredentials>';
			$requestXmlBody .= '	<WarningLevel>High</WarningLevel>';
			$requestXmlBody .= '	<OrderID>'.$order["foreign_OrderID"].'</OrderID>';
			
			$requestXmlBody .= '	<AmountPaid currencyID="'.$order["Currency_Code"].'">'.$amount.'</AmountPaid>';
			
				$requestXmlBody .= '	<ExternalTransaction>';
				$requestXmlBody .= '	    <ExternalTransactionID>'.$paymenttransactionid.'</ExternalTransactionID>';
				$requestXmlBody .= '		<ExternalTransactionTime>'.date('Y-m-d\TH:i:s.000\Z', $order["Payments_TransactionStateDate"]).'</ExternalTransactionTime>';
				$requestXmlBody .= '    </ExternalTransaction>';
	
			
			$requestXmlBody .= '	<PaymentMethodUsed>'.$paymenttype.'</PaymentMethodUsed>';
			$requestXmlBody .= '	<PaymentStatus>'.$paymentstatus2.'</PaymentStatus>';
			$requestXmlBody .= '	<ShippingCost currencyID="'.$order["Currency_Code"].'">'.$order["shipping_costs"].'</ShippingCost>';
			if ($shippingservice!="")	$requestXmlBody .= '	<ShippingService>'.$shippingservice.'</ShippingService>';
			
		/*	
			$requestXmlBody .= '	 <ShippingAddress>';
			$requestXmlBody .= '	 	<Name>'.$name.'</Name>';
			$requestXmlBody .= '	 	<Street1>'.$street1.'</Street1>';
			$requestXmlBody .= '	 	<Street2>'.$order["bill_additional"].'</Street2>';
			$requestXmlBody .= '	 	<PostalCode>'.$order["bill_zip"].'</PostalCode>';
			$requestXmlBody .= '	 	<CityName>'.$order["bill_city"].'</CityName>';
			$requestXmlBody .= '	 	<Country>'.$order["bill_country_code"].'</Country>';
			$requestXmlBody .= '	</ShippingAddress>';
		*/	
			$requestXmlBody .= '	<CheckoutStatus>'.$orderstatus.'</CheckoutStatus>';
	
			$requestXmlBody .= '</ReviseCheckoutStatusRequest>';
			
	//echo $requestXmlBody;
	
			$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "ReviseCheckoutStatus", "id_account" => $account["id_account"], "request" => $requestXmlBody));
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				echo '<ReviseCheckoutStatusResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Antwort von eBay fehlerhaft.</shortMsg>'."\n";
				echo '		<longMsg>Beim Abrufen der Serverantwort von eBay ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</ReviseCheckoutStatusResponse>'."\n";
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if( $response->Ack[0]!="Success" and $response->Ack[0]!="Warning" )
			{
				echo '<ReviseCheckoutStatusResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Die Adresse konnte bei Ebay nicht gesetzt werden.</shortMsg>'."\n";
				echo '		<longMsg><![CDATA['.$responseXml.$requestXmlBody.']]></longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</ReviseCheckoutStatusResponse>'."\n";
				exit;
			}
			
	//	}
	
			$requestXmlBody  = '<?xml version="1.0" encoding="UTF-8"?>';
			$requestXmlBody .= '	<CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			$requestXmlBody .= '	<RequesterCredentials>';
			$requestXmlBody .= '		<eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
			$requestXmlBody .= '	</RequesterCredentials>';
			$requestXmlBody .= '	<WarningLevel>High</WarningLevel>';
			$requestXmlBody .= '	<OrderID>'.$order["foreign_OrderID"].'</OrderID>';
			
			$requestXmlBody .= '	<Paid>'.$paymentstatus.'</Paid>';
		
			$requestXmlBody .= '</CompleteSaleRequest>';
	//echo 	$requestXmlBody;
		//echo $requestXmlBody;
			//submit XML
			$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "CompleteSale", "id_account" => $account["id_account"], "request" => $requestXmlBody));
		//	echo $responseXml;
			
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				echo '<ReviseCheckoutStatusResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Antwort von eBay fehlerhaft.</shortMsg>'."\n";
				echo '		<longMsg>Beim Abrufen der Serverantwort von eBay ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</ReviseCheckoutStatusResponse>'."\n";
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if( $response->Ack[0]!="Success" and $response->Ack[0]!="Warning" )
			{
				echo '<ReviseCheckoutStatusResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Die Zahlung konnte bei Ebay nicht gesetzt werden.</shortMsg>'."\n";
				echo '		<longMsg><![CDATA['.$responseXml.$requestXmlBody.']]></longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</ReviseCheckoutStatusResponse>'."\n";
				exit;
			}
		
		
		//return success
		echo '<ReviseCheckoutStatusResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
//		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</ReviseCheckoutStatusResponse>'."\n";
	}
	
	if (isset($_POST["mode"]) && $_POST["mode"] == "shipmentupdate")
	{
		if ($order["payments_type_id"]==4)
		{	
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keine Änderungen bei Ebay möglich</shortMsg>'."\n";
			echo '		<longMsg><![CDATA[Zahlungsmetode PayPal angegeben. Daher keine Änderungen bei Ebay möglich]]></longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		
		//GET SHIPPING TYPE
		$res_shipment=q("SELECT * FROM shop_shipping_types WHERE id_shippingtype = ".$order["shipping_type_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shipment)==0)
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keine Versandart gefunden</shortMsg>'."\n";
			echo '		<longMsg><![CDATA[Zur shop_orders.shipping_type_id konnte keine Versandart gefunden werden]]></longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		$shipment=mysqli_fetch_array($res_shipment);
		if ($shipment["ShippingServiceType"]=="")
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Versandart nicht für Ebay hinterlegt</shortMsg>'."\n";
			echo '		<longMsg><![CDATA[Zur shop_orders.shipping_type_id konnte keine für Ebay gültige Versandart gefunden werden]]></longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		
		//GET PaymentType
		$payment=array();
		if ($order["payments_type_id"]!=0)
		{
			$res_payments=q("SELECT * FROM shop_payment_types WHERE id_paymenttype = ".$order["payments_type_id"].";",  $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($res_payments)==0 )
			{
				echo '<ReviseCheckoutStatusResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Zahlungstyp nicht gefunden.</shortMsg>'."\n";
				echo '		<longMsg>Zur Zahlungstyp ID konnte keine Zahlart gefunden werden.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</ReviseCheckoutStatusResponse>'."\n";
				exit;
			}
			$payment=mysqli_fetch_array($res_payments);
		}
		if ($payment["PaymentMethod"]=="")
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Zahlungstyp nicht für Ebay definiert.</shortMsg>'."\n";
			echo '		<longMsg>Zur Zahlungstyp ID konnte keine für Ebay gültige Zahlart gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		
		//GET EBAY CHECKOUT-STATUS
		$res_ebay_order=q("SELECT * FROM ebay_orders WHERE OrderID = '".$order["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_ebay_order)==0)
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keine EbayOrder zu ShopOrder gefunden</shortMsg>'."\n";
			echo '		<longMsg><![CDATA[Zur shop_orders.foreign_OrderID konnte keine Ebay-Bestellung (ebay_orders) gefunden werden]]></longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		$ebay_order=mysqli_fetch_array($res_ebay_order);
		
		//Paymentstatus
		if ($order["Payments_TransactionState"]=="Completed" && $order["payments_type_id"]!=0)
		{
			$paymentstatus="Paid";
			if ($order["payments_type_id"]==4)
			{
				$paymenttransactionid=$order["Payments_TransactionID"];
			}
			else 
			{
				$paymenttransactionid="SIS";
			}

		}
		else
		{
			$paymentstatus="Pending";	
			$paymenttransactionid="SIS";
		}
		
		
		
		$requestXmlBody  = '<?xml version="1.0" encoding="UTF-8"?>';
		$requestXmlBody .= '	<ReviseCheckoutStatusRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXmlBody .= '	<RequesterCredentials>';
		$requestXmlBody .= '		<eBayAuthToken>'.$account["token"].'</eBayAuthToken>';
		$requestXmlBody .= '	</RequesterCredentials>';
		$requestXmlBody .= '	<WarningLevel>High</WarningLevel>';
		$requestXmlBody .= '	<OrderID>'.$order["foreign_OrderID"].'</OrderID>';
		$requestXmlBody .= '	<ShippingCost currencyID="'.$order["Currency_Code"].'">'.$order["shipping_costs"].'</ShippingCost>';
		$requestXmlBody .= '	<ShippingService>'.$shipment["ShippingServiceType"].'</ShippingService>
';
		if ($ebay_order["CheckoutStatusStatus"]=="Complete")
		{ 
			$requestXmlBody .= '	<PaymentMethodUsed>'.$payment["PaymentMethod"].'</PaymentMethodUsed>';
			$requestXmlBody .= '	<PaymentStatus>'.$paymentstatus.'</PaymentStatus>';
		}
		if ($paymentstatus=="Paid")
		{
			$requestXmlBody .= '	<AmountPaid currencyID="'.$order["Currency_Code"].'">'.$ebay_order["AmountPaid"].'</AmountPaid>';
			$requestXmlBody .= '	<ExternalTransaction>';
			$requestXmlBody .= '	    <ExternalTransactionID>'.$paymenttransactionid.'</ExternalTransactionID>';
			$requestXmlBody .= '		<ExternalTransactionTime>'.date('Y-m-d\TH:i:s.000\Z', $order["Payments_TransactionStateDate"]).'</ExternalTransactionTime>';
			$requestXmlBody .= '    </ExternalTransaction>';
		}
		
		$requestXmlBody .= '	<CheckoutStatus>'.$ebay_order["CheckoutStatusStatus"].'</CheckoutStatus>';
		$requestXmlBody .= '</ReviseCheckoutStatusRequest>';
	 	$requestXmlBody;
	//echo $requestXmlBody;
		//submit XML
		$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "ReviseCheckoutStatus", "id_account" => $account["id_account"], "request" => $requestXmlBody));

		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Antwort von eBay fehlerhaft.</shortMsg>'."\n";
			echo '		<longMsg>Beim Abrufen der Serverantwort von eBay ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if( $response->Ack[0]!="Success" and $response->Ack[0]!="Warning" )
		{
			echo '<ReviseCheckoutStatusResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Die Zahlung konnte bei Ebay nicht gesetzt werden.</shortMsg>'."\n";
			echo '		<longMsg><![CDATA['.$responseXml.$requestXmlBody.']]></longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ReviseCheckoutStatusResponse>'."\n";
			exit;
		}
	
	
	//return success
	echo '<ReviseCheckoutStatusResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
//	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</ReviseCheckoutStatusResponse>'."\n";
		
	}
?>