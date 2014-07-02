<?php

	//XML error handler
	function HandleXmlError($errno, $errstr, $errfile, $errline)
	{
		error($errfile, $errline, $errno." ".$errstr);
	}


	if ( !isset($_POST["OrderID"]) )
	{
		echo '<send_fin_eBayMessageResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_fin_eBayMessageResponse>'."\n";
		exit;
	}

	//GET ORDERdata
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_order)==0)
	{
		echo '<send_fin_eBayMessageResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderID konnte keine Bestellung gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_fin_eBayMessageResponse>'."\n";
		exit;
	}

	$row_order=mysql_fetch_array($res_order);


	//GET EbayOrder
	//$res_ebay_order=q("SELECT * FROM ebay_orders2 WHERE id_order = ".$row_order["foreign_order_id"].";", $dbshop, __FILE__, __LINE__);
	$res_ebay_order=q("SELECT * FROM ebay_orders WHERE OrderID = '".$row_order["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_ebay_order)==0)
	{
		echo '<send_fin_eBayMessageResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderID konnte keine Bestellung gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_fin_eBayMessageResponse>'."\n";
		exit;
	}
	$row_ebay_order=mysql_fetch_array($res_ebay_order);
	
	//GET EbayItems
	$res_ebay_items=q("SELECT * FROM ebay_orders_items WHERE OrderID = '".$row_ebay_order["OrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res_ebay_items)==0)
	{
		echo '<send_fin_eBayMessageResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderID konnte keine Bestellposition gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_fin_eBayMessageResponse>'."\n";
		exit;
	}

	$row_ebay_items=mysql_fetch_array($res_ebay_items);
	
	$ItemID=$row_ebay_items["ItemItemID"];
	$BuyerID=$row_ebay_order["BuyerUserID"];
	$id_account=$row_ebay_order["account_id"];
	$message='';
	$message.='Vielen Dank für Ihre Bestellung.'.chr(13).chr(10).chr(13).chr(10);
	$message.='Sofern Sie uns bereits Ihre Fahrzeugdaten mitgeteilt haben, brauchen Sie auf diese Nachricht nicht zu antworten.'.chr(13).chr(10).chr(13).chr(10);

	$message.='Bitte übermitteln Sie uns noch die Schlüsselnummern zu 2.1 und 2.2 und DAS DATUM DER ERSTZULASSUNG sowie die komplette Fahrgestellnummer (17-stellig) Ihres Fahrzeugs, sodass wir Ihnen die passenden Teile zusenden können.'.chr(13).chr(10);
	$message.='Sollten Sie zu Ihrem Fahrzeug keine Schlüsselnummern haben, senden Sie uns stattdessen die allgemeinen Fahrzeugdaten (Hersteller, Modell, Motorisierung, Sonderausstattungen, etc.)'.chr(13).chr(10).chr(13).chr(10);
	$message.='Vielen Dank!';	
	
	$subject='Bitte teilen Sie uns die Fahrzeugdaten zu Ihrer Bestellung mit';
	
	//NACHRICHT SENDEN
	$response= post(PATH."soa/", array("API" => "ebay", "Action" => "SendMemberMessageToBuyer", "id_account" => $id_account, "ItemID" => $ItemID, "BuyerID" => $BuyerID, "subject" => $subject, "message" => $message));
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

		echo '<send_fin_eBayMessageResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>'.$shortMsg.'.</shortMsg>'."\n";
		echo '		<longMsg>'.$longMsg.'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_fin_eBayMessageResponse>'."\n";
		exit;
		
	}
	*/

	echo '<send_fin_eBayMessageResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</send_fin_eBayMessageResponse>'."\n";

	
?>