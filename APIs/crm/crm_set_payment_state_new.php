<?php

	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
		//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			if (!is_numeric($val)) $xml.='<![CDATA['.$val.']]>'; else $xml.=$val;
			$xml.='</'.$key.'>';
			
		}
		$xml.='</data>';
		
		//SAVE EVENT
		q("INSERT INTO shop_orders_events (
			order_id, 
			eventtype_id, 
			data, 
			firstmod, 
			firstmod_user
		) VALUES (
			".$order_id.",
			".$eventtype_id.",
			'".mysqli_real_escape_string($dbshop,$xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
		
	}



	include("../functions/mapco_gewerblich.php");
	
	$ust = (UST/100) +1;

	if ( !isset($_POST["mode"]) )
	{
		echo '<crm_set_payment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Zahlungsmodus nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss angegeben werden, ob eine Zahlung erhalten (payment) oder gesendet wurde (refund).</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_payment_stateResponse>'."\n";
		exit;
	}


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
	if (mysqli_num_rows($res_order)==0)
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
	$order[0]=mysqli_fetch_array($res_order);
	
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
/*	
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
*/

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

	//GET SHOP TYPE & ACCOUNT DATA
	$res_shop_type=q("SELECT * FROM shop_shops WHERE id_shop = ".$order[0]["shop_id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_shop_type)==0)
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
	$shop=mysqli_fetch_array($res_shop_type);

	//ES KÖNNEN NUR ÜBERWEISUNGEN FÜR EBAY BEARBEITET WERDEN
	if ($shop["shop_type"]==2 && $_POST["PaymentTypeID"]!=2)
	{
		echo '<crm_set_payment_stateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Unzulässige Zahlmethode</shortMsg>'."\n";
		echo '		<longMsg>Für Ebaybestellungen können nur Überweisungen bearneitet werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_payment_stateResponse>'."\n";
		exit;
	}

	
  	if ($order[0]["combined_with"]>0)
	{
		$res_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$order[0]["combined_with"]." AND NOT id_order = ".$order[0]["id_order"].";", $dbshop, __FILE__, __LINE__);
		while ($row_orders=mysqli_fetch_array($res_orders))
		{
			$order[]=$row_orders;
		}
	}
	
	/*
	//GET ITEMS - > for TOTAL (Itemsprices & shipping costs)
	$total=0;
	for ($i=0; $i<sizeof($order); $i++)
	{
		$sum=0;
		$res_items=q("SELECT * FROM shop_orders_items WHERE order_id = ".$order[$i]["id_order"].";", $dbshop, __FILE__, __LINE__);
		if (gewerblich($order[0]["customer_id"]))
		{
			while ($row_items=mysqli_fetch_array($res_items))	
			{
				$sum+=( ($row_items["netto"]+$row_items["collateral"])*$row_items["amount"] );
				$sum+=( ($row_items["netto"]+$row_items["collateral"])*$row_items["amount"] );
			}
			$order[$i]["Total"]=($sum*$ust);
			$order[$i]["ShippingCosts"]=($order["shipping_costs"]*$ust);
			$total+=($sum*$ust)+($order["shipping_costs"]*$ust);
		}
		else
		{
			while ($row_items=mysqli_fetch_array($res_items))	
			{
				$sum+=( ($row_items["price"]+$row_items["collateral"])*$row_items["amount"] );
				$sum+=( ($row_items["price"]+$row_items["collateral"])*$row_items["amount"] );
			}
			$order[$i]["Total"]=($sum);
			$order[$i]["ShippingCosts"]=($order["shipping_costs"]);
			$total+=($sum)+($order["shipping_costs"]);
		}

	}
	*/
	
	/*
	//GET PAYMENT TYPE info
	if ($_POST["PaymentTypeID"]!=0)
	{
		$res_payments=q("SELECT * FROM shop_payment_types WHERE id_paymenttype = ".$_POST["PaymentTypeID"].";",  $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($res_payments)==0 )
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
		$payment=mysqli_fetch_array($res_payments);
		if ($_POST["PaymentTypeID"]==2)
		{
			if ($_POST["mode"]=="payment")
			{
				$payment["status"]="Completed";
				$eventtype_id=5;
			}
			elseif ($_POST["mode"]=="refund")
			{
				$payment["status"]="Refunded";
				$eventtype_id=6;
			}
			else
			{
				$payment["status"]="";
			}
				
		}
		else
		{
			if ($_POST["mode"]=="payment")
			{
				$payment["status"]="Completed";
				$eventtype_id=5;
			}
			elseif ($_POST["mode"]=="refund")
			{
				$payment["status"]="Refunded";
				$eventtype_id=6;
			}
			else
			{
				$payment["status"]="";
			}
		}
		
	}
	else 
	{
		$payment["method"]=0;
		$payment["id_paymenttype"]=0;
		$payment["status"]="";
	}
	*/
		
	
	if ( !isset($_POST["Payments_TransactionID"]))
	{
		$paymentstransactionid="";
	}
	else
	{
		$paymentstransactionid=$_POST["Payments_TransactionID"];
	}
	
	
	if ($order[0]["status_id"]==1 || $order[0]["status_id"]==0)
	{
		$status_id=7;
		$status_date=time();
	}
	else
	{
		$status_id=$order[0]["status_id"];
		$status_date=time();
	}
	
	//SET PAYMENT NOTIFICATION
	if ($_POST["mode"]=="refund")
	{
		$parentTransactionID = $order[0]["Payments_TransactionID"];
		$pn_total=$_POST["amount"]*(-1);
	}
	else
	{
		$parentTransactionID = '';
		$pn_total=$_POST["amount"];
	}

	if ($_POST["mode"]=="payment")
	{
		$payment["status"]="Completed";
		$eventtype_id=5;
	}
	elseif ($_POST["mode"]=="refund")
	{
		$payment["status"]="Refunded";
		$eventtype_id=6;
	}
	else
	{
		$payment["status"]="";
	}



	//ÜBERWEISUNGEN
	if ($_POST["PaymentTypeID"]==2)
	{
	//SET PAYMENT NOTIFICATION
	q("INSERT into payment_notifications2 (payment_account_id, PN_date, payment_date, state, orderTransactionID, shop_orderID, total, fee, platform, buyer_id, payment_type, buyer_lastname, buyer_firstname, buyer_mail, parentPaymentTransactionID) VALUES (0, 0, ".$status_date.", '".$payment["status"]."', '', ".$order[0]["id_order"].", ".$pn_total.", 0, ".$order[0]["shop_id"].", '', ".$_POST["PaymentTypeID"].", '".$order[0]["bill_lastname"]."', '".$order[0]["bill_firstname"]."', '".$order[0]["usermail"]."', '".$parentTransactionID."');", $dbshop, __FILE__, __LINE__);

		$paymentstransactionid=mysqli_insert_id($dbshop);
		q("UPDATE payment_notifications2 SET paymentTransactionID = '".$paymentstransactionid."' WHERE id_PN = ".$paymentstransactionid.";", $dbshop, __FILE__, __LINE__);
	}
	
	for ($i=0; $i<sizeof($order); $i++)
	{
		//UPDATE SHOP ORDERS
			q("UPDATE shop_orders SET status_id = ".$status_id.", status_date = ".$status_date.", payments_type_id = ".$_POST["PaymentTypeID"].", Payments_TransactionStateDate = ".$_POST["payment_date"].", Payments_TransactionID = '".$paymentstransactionid."', Payments_TransactionState = '".$payment["status"]."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE id_order = ".$order[$i]["id_order"].";", $dbshop, __FILE__, __LINE__);
			
			//SET ORDEREVENT
			$data=array();
			$data["status_id"]=$status_id;
			$data["status_date"]=$status_date;
			$data["payments_type_id"]=$_POST["PaymentTypeID"];
			$data["Payments_TransactionStateDate"]=$_POST["payment_date"];
			$data["Payments_TransactionID"]=$paymentstransactionid;
			$data["Payments_TransactionState"]=$payment["status"];
			save_order_event($eventtype_id, $order[$i]["id_order"], $data);

		
		//WENN EBAY VERKAUF UND ZAHLUNGSMETHODE: ÜBERWEISUNG
		if ($_POST["PaymentTypeID"]==2 && $shop["shop_type"]==2)
		{
			//UPDATE EBAY
			$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "ReviseCheckoutStatus", "id_order" => $order[$i]["id_order"], "payment_type_id" => $_POST["PaymentTypeID"], "Payments_TransactionStateDate" => $_POST["payment_date"], "Payments_TransactionID" => $paymentstransactionid,  "amount" => $_POST["amount"] , "mode" => "paymentupdate", "paymentmode" =>  $_POST["mode"]));
		
			
	//	echo $responseXml;
		
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
				//SET ORDEREVENT
				$data=array();
				$data["payments_type_id"]=$_POST["PaymentTypeID"];
				$data["Payments_TransactionStateDate"]=$_POST["payment_date"];
				$data["Payments_TransactionID"]=$paymentstransactionid;
				$data["amount"]=$_POST["amount"];
				$data["mode"]="paymentupdate";
				$data["paymentmode"]=$_POST["mode"];
				save_order_event(7, $order[$i]["id_order"], $data);
		} // IF NOT PAYPAL
	} // FOR SIZEOF ORDERS 

	
	echo "<crm_set_payment_stateResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<payment_date>".$_POST["payment_date"]."</payment_date>\n";
	echo "<Payments_TransactionState>".$payment["status"]."</Payments_TransactionState>\n";
	echo "</crm_set_payment_stateResponse>";


?>