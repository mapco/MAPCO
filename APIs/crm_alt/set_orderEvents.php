<?php

	if ( !isset($_POST["event"]) )
	{
		echo '<set_orderEventsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Event nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Event-Typ für das zu speichernde Event angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</set_orderEventsResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["order_id"]) )
	{
		echo '<set_orderEventsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Order_id nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Die Bestellungs ID (id_order) für das zu speichernde Event konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</set_orderEventsResponse>'."\n";
		exit;
	}

	//GET ORDER
	$res=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($res)==0)
	{
		echo '<set_orderEventsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Order nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Die Bestellung für das zu speichernde Event konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</set_orderEventsResponse>'."\n";
		exit;
	}
	
	$order=mysql_fetch_array($res);
	
	if ($_POST["event"]=="Payment")
	{
		
		//GET PAYMENT EVENTS FOR ORDER
		$res_event_payments=q("SELECT * FROM shop_orders_events WHERE order_id = ".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res_event_payments)==0)
		{
			//PAYMENT EVENT ANLEGEN	
			$balance=0;
			$paymentevents='';
			//GET PAYMENT NOTIFICATIONS
			$res_payments=q("SELECT * FROM payment_notifications3 WHERE shop_orderID = ".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
			while($row_payments=mysql_fetch_array($res_payments))
			{
				if ($row_payments["state"]!="Created")
				{
					$paymentevents.='	<PaymentEvent>'."\n";
					$paymentevents.='		<PaymentMethod><![CDATA['.$row_payments["payment_type"].']]></PaymentMethod>'."\n";
					$paymentevents.='		<PaymentTransactionID><![CDATA['.$row_payments["paymentTransactionID"].']]></PaymentTransactionID>'."\n";
					$paymentevents.='		<PaymentType><![CDATA['.$row_payments["state"].']]></PaymentType>'."\n";
					$paymentevents.='		<PaymentTotal>'.$row_payments["total"].'</PaymentTotal>'."\n";
					$paymentevents.='		<PaymentTime>'.$row_payments["payment_date"].'</PaymentTime>'."\n";
					$paymentevents.='	</PaymentEvent>'."\n";
					$balance+=$row_payments["total"];
				}
			}
			
			$message='<?xml version="1.0" encoding="UTF-8"?>';
			$message.='<Payment>'."\n";
			$message.='	<PaymentBalance>'.$balance.'</PaymentBalance>'."\n";
			$message.='	<PaymentEvents>'."\n";
			$message.=$paymentevents;
			$message.='	</PaymentEvents>'."\n";
			$message.='</Payment>'."\n";

			q("INSERT INTO shop_orders_events (order_id, event, message, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["order_id"].", 'Payment', '".mysql_real_escape_string($message, $dbshop)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);


		}
		else
		{
			//PAYMENT EVENT UPDATE
			//GET PAYMENT EVENT
			while ($row_event_payments=mysql_fetch_array($res_event_payments))
			{
				$xml = new SimpleXMLElement($row_event_payments["message"]);
				$balance = $xml->PaymentBalance[0];
				$i=0;
				$PaymentEvent=array();
				while (isset($xml->PaymentEvents[0]->PaymentEvent[$i]))
				{
					$PaymentEvent[$i]["PaymentMethod"]=$xml->PaymentEvents[0]->PaymentEvent[$i]->PaymentMethod[0];
					$PaymentEvent[$i]["PaymentTransactionID"]=$xml->PaymentEvents[0]->PaymentEvent[$i]->PaymentTransactionID[0];
					$PaymentEvent[$i]["PaymentType"]=$xml->PaymentEvents[0]->PaymentEvent[$i]->PaymentType[0];
					$PaymentEvent[$i]["PaymentTotal"]=$xml->PaymentEvents[0]->PaymentEvent[$i]->PaymentTotal[0];
					$PaymentEvent[$i]["PaymentTime"]=$xml->PaymentEvents[0]->PaymentEvent[$i]->PaymentTime[0];
					$i++;
				}
			}
			//GET PAYMENT NOTIFICATIONS
			$res_payments=q("SELECT * FROM payment_notifications3 WHERE shop_orderID = ".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
			while($row_payments=mysql_fetch_array($res_payments))
			{
				$is_known=false;
				//CHECK IF NOTIFICATION IS KNOWN
				for ($i=0; $i<sizeof($PaymentEvent); $i++)
				{
					if ($PaymentEvent[$i]["PaymentMethod"]==$row_payments["payment_type"] && $PaymentEvent[$i]["PaymentTransactionID"]==$row_payments["paymentTransactionID"] && $PaymentEvent[$i]["PaymentType"]==$row_payments["state"] && $PaymentEvent[$i]["PaymentTotal"]==$row_payments["total"] && $PaymentEvent[$i]["PaymentTime"]==$row_payments["payment_date"]) $is_known=true;
					
				}
				if (!$is_known)
				{
					//INSERT NOTIFICATION 
					$size=sizeof($PaymentEvent);
					$PaymentEvent[$size]["PaymentMethod"]=$row_payments["payment_type"];
					$PaymentEvent[$size]["PaymentTransactionID"]=$row_payments["paymentTransactionID"];
					$PaymentEvent[$size]["PaymentType"]=$row_payments["state"];
					$PaymentEvent[$size]["PaymentTotal"]=$row_payments["total"];
					$PaymentEvent[$size]["PaymentTime"]=$row_payments["payment_date"];
				}
				else 
				{
					echo "NOTIFICATION KNOWN";
				}
			}
			
			//CREATE XML
			$balance=0;
			for ($i=0; $i<sizeof($PaymentEvent); $i++)
			{
				$paymentevents.='	<PaymentEvent>'."\n";
				$paymentevents.='		<PaymentMethod><![CDATA['.$PaymentEvent[$i]["PaymentMethod"].']]></PaymentMethod>'."\n";
				$paymentevents.='		<PaymentTransactionID><![CDATA['.$PaymentEvent[$i]["PaymentTransactionID"].']]></PaymentTransactionID>'."\n";
				$paymentevents.='		<PaymentType><![CDATA['.$PaymentEvent[$i]["PaymentType"].']]></PaymentType>'."\n";
				$paymentevents.='		<PaymentTotal>'.$PaymentEvent[$i]["PaymentTotal"].'</PaymentTotal>'."\n";
				$paymentevents.='		<PaymentTime>'.$PaymentEvent[$i]["PaymentTime"].'</PaymentTime>'."\n";
				$paymentevents.='	</PaymentEvent>'."\n";
				$balance+=$PaymentEvent[$i]["PaymentTotal"];
			}
			
			$message='<?xml version="1.0" encoding="UTF-8"?>';
			$message.='<Payment>'."\n";
			$message.='	<PaymentBalance>'.$balance.'</PaymentBalance>'."\n";
			$message.='	<PaymentEvents>'."\n";
			$message.=$paymentevents;
			$message.='	</PaymentEvents>'."\n";
			$message.='</Payment>'."\n";

			
			// UPDATE PAYMENT EVENT
			q("UPDATE shop_orders_events SET message = '".mysql_real_escape_string($message, $dbshop)."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE id_event = ".$row_event_payments["id_event"].";", $dbshop, __FILE__, __LINE__);
			
		}
		
	}

//****************************************************************************************************************************************************

	if ($_POST["event"]=="Shipment")
	{
		//GET SHIPPING TIME
		$res_shipping_date=q("SELECT * FROM cms_files WHERE filename = '".$order["shipping_number"]."' AND description = 'DHL-Label';", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($res_shipping_date)>0)
		{
			$row_shipping_date=mysql_fetch_array($res_shipping_date);
			$shippingDate=$row_shipping_date["firstmod_user"];
		}
		else 
		{
			$shippingDate=0;
		}
		
		//GET EVENT ID FOR "SHIPMENT ASSIGNED"
		$res_assigned=q("SELECT * FROM shop_orders_events WHERE event = 'ShippingAssigned' AND order_id = ".$_POST["order_id"].";", $dbshop, __FILLE__, __LINE__);
		if (mysql_num_rows($res_assigned)>0)
		{
			$row_assigned=mysql_fetch_array($res_assigned);
			$shippingAssignedID=$row_assigned["id_event"];
		}
		else
		{
			$shippingAssignedID=0;
		}
		
		$message='<?xml version="1.0" encoding="UTF-8"?>';
		$message.='<Shipment>'."\n";
		$message.='	<ShippingAssignedID>'.$shippingAssignedID.'</ShippingAssignedID>'."\n";
		$message.='	<ShippingServiceID>'.$order["shipping_type_id"].'</ShippingServiceID>'."\n";
		$message.='	<ShippingTrackingID>'.$order["shipping_number"].'</ShippingTrackingID>'."\n";
		$message.='	<ShippingDate>'.$shippingDate.'</ShippingDate>'."\n";
		$message.='</Shipment>'."\n";

		
		//CHECK IF EVENT IS ALREADY KNOWN
		$res_event_shipment=q("SELECT * FROM shop_orders_events WHERE order_id = ".$_POST["order_id"]." AND event = 'Shipment';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($res_event_shipment)>0)
		{
			$row_event_shipment=mysql_fetch_array($res_event_shipment);
			//UPDATE EVENT
q("UPDATE shop_orders_events SET message = '".mysql_real_escape_string($message, $dbshop)."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE id_event = ".$row_event_shipment["id_event"].";", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			//ADD EVENT
			q("INSERT INTO shop_orders_events (order_id, event, message, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["order_id"].", 'Shipment', '".mysql_real_escape_string($message, $dbshop)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		}
	}
	
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<set_orderEventsResponse>';
	echo '	<Ack>Success</Ack>';
	echo '</set_orderEventsResponse>';
		

		

?>