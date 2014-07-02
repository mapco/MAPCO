<?php

	if ( !isset($_POST["OrderID"]) )
	{
		echo '<crm_set_payment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID (id_order.shop_orders) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_payment_stateResponse>'."\n";
		exit;
	}

	//GET ORDERdata
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_order)==0)
	{
		echo '<crm_set_payment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Order nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderID konnte keine Bestellung gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_payment_stateResponse>'."\n";
		exit;
	}
	$order=mysql_fetch_array($res_order);
	
	//GET SHOP TYPE & ACCOUNT DATA
	$res_shop_type=q("SELECT * FROM shop_shops WHERE id_shop = ".$order["shop_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_shop_type)==0)
	{
		echo '<crm_set_payment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein Shop (shop_shops) zur Bestellung gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_payment_stateResponse>'."\n";
		exit;
	}
	$shop=mysql_fetch_array($res_shop_type);
	
	if ( !isset($_POST["payment_date"]) )
	{
		echo '<crm_set_payment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datum nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Datum zur Zahlung angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_payment_stateResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["PaymentTypeID"]) )
	{
		echo '<crm_set_payment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>PaymentTypeID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Zahlart angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_payment_stateResponse>'."\n";
		exit;
	}

	//GET PAYMENT TYPE info
	if ($_POST["PaymentTypeID"]!=0)
	{
		$res_payments=q("SELECT * FROM shop_payment_types WHERE id_paymenttype = ".$_POST["PaymentTypeID"].";",  $dbshop, __FILE__, __LINE__);
		if( mysql_num_rows($res_payments)==0 )
		{
			echo '<crm_set_payment_stateResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Zahlungstyp nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Zur Zahlungstyp ID konnte keine Zahlart gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</crm_set_payment_stateResponse>'."\n";
			exit;
		}
		$payment=mysql_fetch_array($res_payments);
		if ($_POST["PaymentTypeID"]==2)
		{
			$payment["status"]="Completed";
		}
		else
		{
			$payment["status"]="OK";
		}
		
	}
	else 
	{
		$payment["method"]=0;
		$payment["id_paymenttype"]=0;
		$payment["status"]="";
	}

		
	if ( !isset($_POST["Payments_TransactionID"]) && $payment["method"]==2 )
	{
		echo '<crm_set_payment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>PaymentsTransactionID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur angegebenen Zahlart muss eine Zahlungstransaktionsnummer übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_payment_stateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["Payments_TransactionID"]))
	{
		$paymentstransactionid="";
	}
	else
	{
		$paymentstransactionid=$_POST["Payments_TransactionID"];
	}
	
	if ( !isset($_POST["amount"]))
	{
		echo '<crm_set_payment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Zahlungssumme nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Zahlungssumme (oder 0.00) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_payment_stateResponse>'."\n";
		exit;
	}

	//UPDATE SHOP ORDERS
	q("UPDATE shop_orders SET payments_type_id = ".$_POST["PaymentTypeID"].", Payments_TransactionStateDate = ".$_POST["payment_date"].", Payments_TransactionID = '".$paymentstransactionid."', Payments_TransactionState = '".$payment["status"]."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	
	if ($shop["shop_type"]==2)
	{
		//UPDATE EBAY
		$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "ReviseCheckoutStatus", "id_order" => $_POST["OrderID"], "payment_type_id" => $_POST["PaymentTypeID"], "Payments_TransactionStateDate" => $_POST["payment_date"], "Payments_TransactionID" => $paymentstransactionid,  "amount" => $_POST["amount"] ));
	
	echo $responseXml;
	/*
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<crm_set_payment_stateResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Antwort von Service fehlerhaft.</shortMsg>'."\n";
			echo '		<longMsg>Beim Abrufen der Serverantwort ist ein XML-Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</crm_set_payment_stateResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if( $response->Ack[0]=="Failure")
		{
			echo '<crm_set_payment_stateResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Die Zahlung konnte bei Ebay nicht gesetzt werden.</shortMsg>'."\n";
			echo '		<longMsg><![CDATA['.$responseXml.']]></longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</crm_set_payment_stateResponse>'."\n";
			exit;
		}
	*/
	}
	
	echo "<crm_set_payment_stateResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<payment_date>".$_POST["payment_date"]."</payment_date>\n";
	echo "</crm_set_payment_stateResponse>";


?>