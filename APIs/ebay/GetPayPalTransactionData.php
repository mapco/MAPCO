<?php

	if ( !isset($_POST["PayPalTransactionID"]) || $_POST["PayPalTransactionID"]=="" )
	{
		echo '<GetPayPalTransactionDataResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>PayPal TransactionID nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine PayPal TransactionID für die zu ermittelnden Daten übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetPayPalTransactionDataResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM payment_notifications WHERE paymentTransactionID = '".$_POST["PayPalTransactionID"]."' Order by PN_Date desc LIMIT 1;", $dbshop, __FILE__, __LINE__);
	
	if (mysqli_num_rows($res)==0)
	{
		echo '<GetPayPalTransactionDataResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>PayPal Transaction nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Zur ID konnte keine PayPal Transaction gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetPayPalTransactionDataResponse>'."\n";
		exit;
	}
	else 
	{
		$transactioncount=0;
		$xml="";
		while ($row=mysqli_fetch_array($res))
		{
			$transactioncount++;
			$transaction["PN_ID"]=$row["id_PN"];
			$transaction["state"]=$row["state"];
			$transaction["IDIMS_ID"]=$row["IDIMS_AuftragsNR"];
			$transaction["platform"]=$row["platform"];
			$transaction["orderID"]=$row["orderID"];
			$transaction["paypalaccount"]=$row["payment_account_id"];
			$transaction["payment_date"]=$row["payment_date"];
			$transaction["payment_total"]=$row["total"];
			$transaction["buyer_id"]=$row["buyer_id"];
			//$transaction["buyer_lastname"]=$row["buyer_lastname"];
			//$transaction["buyer_firstname"]=$row["buyer_firstname"];
			//$transaction["buyer_mail"]=$row["buyer_mail"];
			
			if ($row["platform"]=="EBAY_MAPCO" || $row["platform"]=="EBAY_AP")
			{
				$item_counter=0;
				$res_orderItems=q("SELECT * FROM ebay_orders_items WHERE TransactionID = '".$row["orderID"]."';", $dbshop, __FILE__, __LINE__);
				while ($row_orderItems=mysqli_fetch_array($res_orderItems))
				{
					//echo $row_orderItems["ItemTitle"];
					$transaction["Item"][$item_counter]=$row_orderItems["ItemTitle"];
					$transaction["ItemSKU"][$item_counter]=$row_orderItems["ItemSKU"];
					$transaction["ItemTransactionPrice"][$item_counter]=$row_orderItems["TransactionPrice"];
					$transaction["ItemQuantityPurchased"][$item_counter]=$row_orderItems["QuantityPurchased"];
					$item_counter++;
	
					$res_order=q("SELECT * FROM ebay_orders WHERE OrderID LIKE '%".$row_orderItems["OrderID"]."';", $dbshop, __FILE__, __LINE__);
					while ($row_order=mysqli_fetch_array($res_order))
					{
						$transaction["order_total"]=$row_order["Total"];
						$transaction["ShippingAddressName"]=$row_order["ShippingAddressName"];
					}
					
				}
				
			} // IF PLATFORM EBAY
			
			if ($row["platform"]=="MAPCO_ONLINESHOP")
			{
				$itemstotal=0;
				
				$res_order=q("SELECT * FROM shop_orders WHERE Payments_TransactionID = '".$_POST["PayPalTransactionID"]."';", $dbshop, __FILE__, __LINE__);
				while ($row_order=mysqli_fetch_array($res_order))
				{
					if ($row_order["bill_company"]=="" && $row_order["bill_firstname"]=="" && $row_order["bill_lastname"]=="")
					{
						$transaction["ShippingAddressName"]=$row_order["ship_company"].' '.$row_order["ship_firstname"].' '.$row_order["ship_lastname"];
					}
					else 
					{
						$transaction["ShippingAddressName"]=$row_order["bill_company"].' '.$row_order["bill_firstname"].' '.$row_order["bill_lastname"];
					}
					
					$item_counter=0;
					$res_orderItems=q("SELECT * FROM shop_orders_items WHERE order_id = '".$row_order["id_order"]."';", $dbshop, __FILE__, __LINE__);
					while ($row_orderItems=mysqli_fetch_array($res_orderItems))
					{
						$res_orderItemsDescription=q("SELECT * FROM shop_items_de WHERE id_item = ".$row_orderItems["item_id"].";",  $dbshop, __FILE__, __LINE__);
						while ($row_orderItemsDescription=mysqli_fetch_array($res_orderItemsDescription))
						{
							$transaction["Item"][$item_counter]=$row_orderItemsDescription["title"];
						}
						$res_orderItemsDetails=q("SELECT * FROM shop_items WHERE id_item = ".$row_orderItems["item_id"].";",  $dbshop, __FILE__, __LINE__);
						while ($row_orderItemsDetails=mysqli_fetch_array($res_orderItemsDetails))
						{
							$transaction["ItemSKU"][$item_counter]=$row_orderItemsDetails["MPN"];
						}

						$transaction["ItemTransactionPrice"][$item_counter]=number_format(($row_orderItems["netto"]*1.19), 2);
						$transaction["ItemQuantityPurchased"][$item_counter]=$row_orderItems["amount"]*1;
						$itemstotal+=(number_format(($row_orderItems["netto"]*1.19), 2))*($row_orderItems["amount"]*1);
						$item_counter++;
					}
					$transaction["order_total"]=$itemstotal+number_format(($row_order["shipping_costs"]*1.19),2);

				}
			} // IF PLATFORM MAPCO_ONLINESHOP
			if ($row["platform"]=="AUTOPARTNER_ONLINESHOP")
			{
				$varField["usertoken"]="merci2664";
				$varField["paymentTransactionID"]=$_POST["PayPalTransactionID"];
				$varField["API"]="payments";
				$varField["Action"]="PaymentNotificationGetOrder";
				
				if (strpos(PATH, "www")>0)
				{
					$responseXML=post("http://www.ihr-autopartner.de/soa/", $varField);
				}
				else 
				{
					$responseXML=post("http://localhost/AUTOPARTNER/AUTOPARTNER/soa/", $varField);
				}
				if (strpos($responseXML, "<Ack>Success</Ack>")>0)
				{
					$xml = new SimpleXMLElement($responseXML);
					$transaction["ShippingAddressName"]=$xml->buyer_firstname[0].' '.$xml->buyer_lastname[0];
					$transaction["order_total"]=$xml->total[0];
					$item_counter=$xml->OrderItemsCount[0];
					for ($i=0; $i<$item_counter; $i++)
					{
						$transaction["Item"][$i]=$xml->OrderItem[$i]->OrderItemTitle[0];
						$transaction["ItemSKU"][$i]=$xml->OrderItem[$i]->OrderItemMPN[0];
						$transaction["ItemTransactionPrice"][$i]=$xml->OrderItem[$i]->OrderItemPrice[0];
						$transaction["ItemQuantityPurchased"][$i]=$xml->OrderItem[$i]->OrderItemAmount[0];
					}
				}
				else 
				{
					$transaction["ShippingAddressName"]="";
					$transaction["order_total"]=0;
					$item_counter=0;
				}

			}
			
			$xml.='<transaction>'."\n";
			$xml.='<transactionPN_ID>'.$transaction["PN_ID"].'</transactionPN_ID>'."\n";
			$xml.='<transactionIDIMS_ID>'.$transaction["IDIMS_ID"].'</transactionIDIMS_ID>'."\n";
			$xml.='<transactionState><![CDATA['.$transaction["state"].']]></transactionState>'."\n";
			$xml.='<transactionPlatform><![CDATA['.$transaction["platform"].']]></transactionPlatform>'."\n";
			$xml.='<transactionPaymentTotal><![CDATA['.$transaction["payment_total"].']]></transactionPaymentTotal>'."\n";
			$xml.='<transactionBuyerID><![CDATA['.$transaction["buyer_id"].']]></transactionBuyerID>'."\n";
			$xml.='<transactionOrderTotal><![CDATA['.$transaction["order_total"].']]></transactionOrderTotal>'."\n";
			$xml.='<transactionShipName><![CDATA['.$transaction["ShippingAddressName"].']]></transactionShipName>'."\n";
			$xml.='<transactionItemCount><![CDATA['.$item_counter.']]></transactionItemCount>'."\n";
			for ($i=0; $i<$item_counter; $i++)
			{
				$xml.='<transactionItem>'."\n";
					$xml.='<transactionItemTitle><![CDATA['.$transaction["Item"][$i].']]></transactionItemTitle>'."\n";
					$xml.='<transactionItemSKU><![CDATA['.$transaction["ItemSKU"][$i].']]></transactionItemSKU>'."\n";
				//	$xml.='<transactionItemSKU><![CDATA['.$transaction["ItemSKU"][$i].']]></transactionItemSKU>'."\n";
					$xml.='<transactionItemPrice><![CDATA['.$transaction["ItemTransactionPrice"][$i].']]></transactionItemPrice>'."\n";
					$xml.='<transactionItemQuantity><![CDATA['.$transaction["ItemQuantityPurchased"][$i].']]></transactionItemQuantity>'."\n";
				$xml.='</transactionItem>'."\n";
			}
			$xml.='</transaction>'."\n";
			
		}
	}

	echo '<GetPayPalTransactionDataResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<transactioncount>'.$transactioncount.'</transactioncount>';
	echo $xml;
	echo '</GetPayPalTransactionDataResponse>'."\n";
	
				
?>