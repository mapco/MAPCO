<?php

include_once("../../mapco_shop_de/functions/cms_send_html_mail.php");

	if ( !isset($_POST["OrderID"]) )
	{
		echo '<AlertMail_ExpressShipmentResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AlertMail_ExpressShipmentResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	
	if (mysqli_num_rows($res)==0)
	{
		echo '<AlertMail_ExpressShipmentResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Order nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderID konnte keine Bestellung gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AlertMail_ExpressShipmentResponse>'."\n";
		exit;
	}
	
	$order=mysqli_fetch_array($res);
	//GET EBAY ORDER-Data
	$res=q("SELECT * FROM ebay_orders WHERE id_order = ".$order["foreign_order_id"]." ;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<AlertMail_ExpressShipmentResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Order nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur ForeignOrderID konnte keine Ebay-Bestellung gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AlertMail_ExpressShipmentResponse>'."\n";
		exit;
	}
	
	$ebay_order=mysqli_fetch_array($res);
	
	//GET EBAY ORDER-ITEMS-Data
	$res=q("SELECT * FROM ebay_orders_items WHERE OrderID = '".$ebay_order["OrderID"]."' ;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<AlertMail_ExpressShipmentResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Order-Item nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur Ebay-OrderID konnte keine Ebay-Bestellposition gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AlertMail_ExpressShipmentResponse>'."\n";
		exit;
	}

	$ebay_order_items=array();
	while ($row=mysqli_fetch_array($res))
	{
		$ebay_order_items[sizeof($ebay_order_items)]=$row;
	}
	
	//AlertMailReciever
	if ($order["shop_id"]==3) $reciever="ebay@mapco.de";
	if ($order["shop_id"]==4) $reciever="ebay@ihr-autopartner.com";
	
	//AlertMailSubject
	$subject = 'NEUE EXPRESSBESTELLUNG bei eBay!!!!!!';
	
	$msg='<p>Es ist eine neue Express-Bestellung bei eBay eingegangen.<p>';
	$msg.='<p>eBay-Mitgliedsname: <b>'.$ebay_order["BuyerUserID"].'</b></p>';
	$msg.='<p>Käufer E-Mailadresse: <b>'.$ebay_order["BuyerEmail"].'</b></p>';
	$msg.='<p>eBay-Verkaufsprotokollnummer: <b>'.$ebay_order["ShippingDetailsSellingManagerSalesRecordNumber"].'</b></p>';
	$msg.='<p>Bestellte Artikel: <br />';
	foreach ($ebay_order_items as $ebay_order_item)
	{
		$msg.=$ebay_order_item["QuantityPurchased"].'x '.$ebay_order_item["ItemSKU"].' '.$ebay_order_item["ItemTitle"].' <small>('.$ebay_order_item["ItemItemID"].')</small><br />';
	}
	
	SendMail($reciever, "CRM-SYSTEM <noreply@mapco.de>", $subject, $msg);
	SendMail("nputzing@mapco.de", "CRM-SYSTEM <noreply@mapco.de>", $subject, $msg);

	echo '<AlertMail_ExpressShipmentResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</AlertMail_ExpressShipmentResponse>'."\n";


?>