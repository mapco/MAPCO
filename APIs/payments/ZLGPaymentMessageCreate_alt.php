<?php

	$start=time();

	//include_once "constants.php";

	//SOA 2 SERVICE
	
	//KONVERT TIME TO TIMESTAMP if needed
	if (isset($_POST["from"]) && strpos($_POST["from"], "."))
	{
		$_POST["from"] = mktime(0,0,1,substr($_POST["from"], 3,2),substr($_POST["from"], 0,2),substr($_POST["from"], 6));
	}
	if (isset($_POST["to"]) && strpos($_POST["to"], "."))
	{
		$_POST["to"] = mktime(23,59,59,substr($_POST["to"], 3,2),substr($_POST["to"], 0,2),substr($_POST["to"], 6));
	}

	
	if (!isset($_POST["from"])) $_POST["from"]=time()-24*3600*3;
	if (!isset($_POST["to"])) $_POST["to"]=time()-0*3600;
	
	
	//GET CURRENCIES
	$currencies=array();
	$res_curr=q("SELECT * FROM shop_currencies", $dbshop, __FILE__, __LINE__);
	while ($row_curr = mysqli_fetch_assoc($res_curr))
	{
		$currencies[$row_curr["currency_code"]] = $row_curr;
	}
	
	// GET PAYMENT_TYPES DATA
	$payments = array();
	$res_payments = q("SELECT * FROM shop_payment_types", $dbshop, __FILE__, __LINE__);
	while ($row_payments = mysqli_fetch_assoc($res_payments))
	{
		$payments[$row_payments["id_paymenttype"]] = $row_payments;
	}
	
	// F U N C T I O N S ===================================================================
	function getOrderData($orderid, $mode="")
	{
		global $currencies;
		global $dbshop;
		//GET ORDER
		$postfields["API"]="shop";
		$postfields["APIRequest"]="OrderDetailGet";
		$postfields["OrderID"]=$orderid;
		if ($mode!="") $postfields["mode"]=$mode;

		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			$responsefield=array();
			
			$responsefield["ordertotalEUR"]=(float)str_replace(",", ".",(string)$response->Order[0]->orderTotalGross[0]);
			$responsefield["ordertotal"]=(float)str_replace(",", ".",(string)$response->Order[0]->orderTotalGrossFC[0]);
			$responsefield["shop_id"]=(int)$response->Order[0]->shop_id[0];
			$responsefield["user_id"]=(int)$response->Order[0]->customer_id[0];

			$responsefield["payments_type_id"]=(int)$response->Order[0]->payments_type_id[0];
			$responsefield["order_deposit"]=(float)$response->Order[0]->order_deposit[0];
			
			$responsefield["currency"]=(string)$response->Order[0]->Currency_Code[0];
			
			$exchangerate=0;
			for ($i=0; isset($response->Order[0]->OrderItems[0]->Item[$i]); $i++)
			{
				$exchangerate =(float)$response->Order[0]->OrderItems[0]->Item[$i]->OrderItemExchangeRateToEUR[0];
			}
			
			if ($exchangerate==0)
			{
				$exchangerate=$currencies[$responsefield["currency"]]["exchange_rate_to_EUR"];
			}
			
			$responsefield["exchangerate"]=$exchangerate;
			
			return $responsefield;
			
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			return false;
		}
	}

	function OrderPaymentsGet($orderid)
	{
		$postfields["API"]="payments";
		$postfields["APIRequest"]="OrderPaymentsGet";
		$postfields["orderid"]=$orderid;
		$response = soa2($postfields);
		if ($response->Ack[0]=="Success")
		{
			return $response;
		}
		else
		{
			show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			return false;
		}

	}


	function write_idims_error($invoice_id, $order_id, $errorcode, $message)
	{
		global $dbshop;
		//CHECK FOR EXISTING ENTRY
		$res_check = q("SELECT * FROM idims_zlg_error WHERE invoice_id = ".$invoice_id." AND error_code = ".$errorcode, $dbshop, __FILE__ , __LINE__);
		if (mysqli_num_rows($res_check)==0)
		{
			//WRITE LOG
			$fieldlist = array();
			$fieldlist["invoice_id"] = $invoice_id;
			$fieldlist["order_id"] = $order_id;
			$fieldlist["error_code"] = $errorcode;
			$fieldlist["message"] = $message;
			$fieldlist["firstmod"] = time();
			
			//q_insert("idims_zlg_error", $fieldlist, $dbshop, __FILE__, __LINE__);
			q("INSERT INTO idims_zlg_error (invoice_id,order_id, error_code, message, firstmod) VALUES (".$invoice_id.", ".$order_id.", ".$errorcode.", '".mysqli_real_escape_string($dbshop, $message)."', ".time().")", $dbshop, __FILE__, __LINE);	
		}
		
	}
	// =========================================================================================
	
	$order_invoice=array();
	$invoices=array();
	
	$ZLG_SYSTEM_STARTTIME = 1391209200;
	
//	$res_invoice_id = q("SELECT * FROM shop_orders WHERE NOT invoice_id = 0 AND shop_id IN (1,2,3,4,5,6,7) AND NOT ordertype_id in (4,6) AND invoice_date > ".$ZLG_SYSTEM_STARTTIME." AND invoice_date > ".$_POST["from"]." AND invoice_date < ".$_POST["to"], $dbshop, __FILE__, __LINE__);
	$res_invoice_id = q("SELECT * FROM shop_orders WHERE NOT invoice_id = 0 AND shop_id IN (1,2,3,4,5,6,7) AND NOT ordertype_id in (4,6) AND NOT payments_type_id = 1 AND invoice_date > ".$ZLG_SYSTEM_STARTTIME, $dbshop, __FILE__, __LINE__);
//echo "RES". mysqli_num_rows($res_invoice_id)."<br />";

	while ($row_invoice_id = mysqli_fetch_assoc($res_invoice_id))
	{
		//GET ONLY MOTHER ORDER
		if ($row_invoice_id["combined_with"]>0)
		{
			$orderid = $row_invoice_id["combined_with"];
		}
		else 
		{
			$orderid = $row_invoice_id["id_order"];
		}
		$order_invoice[$row_invoice_id["invoice_id"]]=$orderid;	
		$time_invoice[$row_invoice_id["invoice_id"]]=$row_invoice_id["invoice_date"];
		$invoices[]=$row_invoice_id["invoice_id"];	
	}
	//echo "LAUFTZEIT (nach Einlesen der infrage kommenden Orders): ".(time()-$start)." <br />";
	$num_invoices= sizeof($invoices);
	
	//GET already created ZLGMessages which don't have to be created again
	$ZLG_cutouts=array();
	if (sizeof($invoices)>0)
	{
		$res_ZLG=q("SELECT invoice_id FROM idims_zlg_log WHERE invoice_id IN (".implode(",", $invoices).") AND (acknowledgment ='Success' OR response_time = 0)", $dbshop, __FILE__, __LINE__);
		while ($row_ZLG = mysqli_fetch_assoc($res_ZLG))
		{
			$ZLG_cutouts[$row_ZLG["invoice_id"]] = true;
		}
	}
	//DEFINE WHICH ZLG MSG HAS TO BE CREATED
	$ZLG_create_list = array();
	while ( list ($invoice,$orderid) = each ($order_invoice))
	{
		if (!isset($ZLG_cutouts[$invoice]))
		{
			$ZLG_create_list[$invoice]=$orderid;
		}
	}
	//echo "LAUFTZEIT (nach Abgleich mit bereits geschriebenen Msg): ".(time()-$start)." <br />";
	$num_ZLG_create_list = sizeof($ZLG_create_list);
	//CHECK FOR EACH INVOICE: ORDER_TOTAL == accounted_payments for Order and -0,02<OrderDeposit<0,02
	// IF OK => create ZLG MESSAGE 
	$error=array();
		//INIT Errorfield
		$error[9820]=0;
		$error[9821]=0;
		$error[9822]=0;
		$error[9825]=0;
		$error[9823]=0;
		$error[9819]=0;
		$error[9839]=0;

	$success=0;
	
	$counter=0;

	//while ( list ($invoice,$orderid) = each ($ZLG_create_list) && (time()-$start)<59)
	while ( list ($invoice,$orderid) = each ($ZLG_create_list))
	{
		if ((time()-$start)<59)
		{
		//	echo $orderid."<br />";
			$counter ++;
			$OK = true;
			if (!$order=getOrderData($orderid))
			{
				$OK = false;
				//echo $msg="Order mit OrderID :".$orderid." nicht gefunden!\n";
				show_error(9820,9, __FILE__, __LINE__, "OrderID: ".$orderid, false);
				write_idims_error($invoice, $orderid, 9820, $msg);
				$error[9820]++;
			}
			
			if ($order["order_deposit"]<-0.02 || $order["order_deposit"]>0.02)
			{
				$OK = false;
				//show_error();
	//			echo $msg=$orderid."OrderDeposit nicht gleich null: ".$order["order_deposit"]."\n";
				show_error(9821,9, __FILE__, __LINE__, "OrderID: ".$orderid." Deposit: ".$order["order_deposit"], false);
				write_idims_error($invoice, $orderid, 9821, $msg);
				$error[9821]++;
			}
			
			//GET accounted Payments for Order && FEE
			if ($OK)
			{
				if (!$orderpayments=OrderPaymentsGet($orderid))
				{
					$OK = false;
					//echo $msg=$orderid."Fehler beim suchen der Zahlungsdaten.\n";
					show_error(9822,9, __FILE__, __LINE__, "OrderID: ".$orderid, false);
					write_idims_error($invoice, $orderid, 9822, $msg);
					$error[9822]++;
	
				}
				else
				{
					$accounted_payments = (float)$orderpayments->paymentdata[0]->paymentsAccountingsEUR[0];
					
					$i=0;
					$fee=0;
					while (isset($orderpayments->paymentdata[0]->transaction[$i]))
					{
						if ((float)$orderpayments->paymentdata[0]->transaction[$i]->TransactionExchangeRate[0]!=0)
						{
							$fee+=(float)$orderpayments->paymentdata[0]->transaction[$i]->TransactionFee[0] / (float)$orderpayments->paymentdata[0]->transaction[$i]->TransactionExchangeRate[0];

						}
						else
						{
							show_error(9839,9, __FILE__, __LINE__, "OrderID: ".$orderid, false);
							$OK = false;
							$error[9839]++;
						}
						$i++;	
					}
					$fee=round($fee, 2);
	
				}
			}
			if ($OK)
			{
				if ($accounted_payments==0)
				{
					$OK = false;
				}
				// WENN ZAHLART NICHT NACHNAHME -> FEHLER
				if ($accounted_payments==0 && $order["payments_type_id"]!=3) // NACHNAHME
				{
					show_error(9825,9, __FILE__, __LINE__, "OrderID: ".$orderid, false);
					$msg="Accounted Payments =0 und Zahlart nicht Nachnahme. OrderID:".$orderid;
					write_idims_error($invoice, $orderid, 9825, $msg);
					$error[9825]++;
				}
			}
			
			
			//CHECK ORDER_TOTAL == accounted_payments
			if ($OK)
			{
				$diff=$order["ordertotalEUR"]-$accounted_payments;
		//		echo "Ordertotal :".$order["ordertotalEUR"]." - Accounted Payments: ".$accounted_payments."    ";
				if ($diff>0.02)
				{
					$OK = false;
					//echo $msg=$orderid."Zahlbetrag (".$accounted_payments.") geringer als OrderTotal (".$order["ordertotalEUR"]."). \n";
					show_error(9823,9, __FILE__, __LINE__, "OrderID: ".$orderid."Zahlbetrag (".$accounted_payments.") geringer als OrderTotal (".$order["ordertotalEUR"].")", false);
					write_idims_error($invoice, $orderid, 9823, $msg);	
					$error[9823]++;
				}
			}
			// CHECK ob, Paymenttype hinterlegt ist
			if ($OK)
			{
				if ($order["payments_type_id"]==0)
				{
					$OK = false;
					show_error(9819,9, __FILE__, __LINE__, "OrderID: ".$orderid);
					$msg = "Keine Zahlart für Bestellung festgelegt. OrderID: ".$orderid;
					write_idims_error($invoice, $orderid, 9819, $msg);	
					$error[9819]++;
				}
			}
			
			if ($OK)
			{
				//echo $orderid."Zahldaten werden an IDIMS gesendet. \n";
				$success++;
				
				$opXml = '';
				
				$opXml .= '	<ZLG>'."\n";
				//INVOICE ID
				$opXml .= '		<AUFID>'.$invoice.'</AUFID>'."\n";
				//IDIMS USERID DES NUTZERS
				//$opXml .= '		<USRID>'.$idims_user_id.'</USRID>'."\n";
				$opXml .= '		<USRID>328</USRID>'."\n";
				// BRUTTO
				$opXml .= '		<BETRAG>'.number_format($order["ordertotalEUR"], 2, ",", "").'</BETRAG>'."\n";
				// ZLG
				$opXml .= '		<TYP>'.$payments[$order["payments_type_id"]]["ZLG"].'</TYP>'."\n";
				$opXml .= '	</ZLG>'."\n";
				
				//SPEICHERE INS IDIMS_ZLG_LOG
				$datafield = array();
				$datafield["invoice_id"] = $invoice;
				$datafield["invoice_time"] = $time_invoice[$invoice];
				$datafield["shop_id"] = $order["shop_id"];
				$datafield["payment_type_id"] = $order["payments_type_id"];
				$datafield["amount"] = $order["ordertotalEUR"];
				$datafield["fee"] = $fee;
				$datafield["idims_call"] = $opXml;
				$datafield["creation_time"] = time();
				
				$res_ins =q_insert("idims_zlg_log", $datafield, $dbshop, __FILE__, __LINE__);
			}
		}
		//echo "LAUFTZEIT (nach bearbeitung der Invoice Daten): ".(time()-$start)." <br />";
		
	}
	
//SERVICE RESPONSE
echo '<orders_invoices>'.$num_invoices.'</orders_invoices>'."\n";
echo '<creatable_ZLG_Messages>'.$num_ZLG_create_list.'</creatable_ZLG_Messages>'."\n";
echo '<processed_invoices>'.$counter.'</processed_invoices>'."\n";
echo '<succeed_msgs>'.$success.'</succeed_msgs>'."\n";
echo '<error_9819>'.$error[9819].'</error_9819>'."\n";
echo '<error_9820>'.$error[9820].'</error_9820>'."\n";
echo '<error_9821>'.$error[9821].'</error_9821>'."\n";
echo '<error_9822>'.$error[9822].'</error_9822>'."\n";
echo '<error_9823>'.$error[9823].'</error_9823>'."\n";
echo '<error_9825>'.$error[9825].'</error_9825>'."\n";
echo '<error_9839>'.$error[9839].'</error_9839>'."\n";

if ((time()-$start)>58) echo '<NextCall>'.(time()+5*60).'</NextCall>'."\n";


?>