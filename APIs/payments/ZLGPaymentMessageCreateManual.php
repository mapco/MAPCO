<?php

	$required=array("error_id" => "numericNN");
	check_man_params($required);

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
		$postfields["APIRequest"]="OrderDetailGet_neu";
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
	
	
	function getFEE($orderid)
	{
		if (!$orderpayments=OrderPaymentsGet($orderid))
		{
			show_error(9822,9, __FILE__, __LINE__, "OrderID: ".$orderid, false);
			exit;	
		}
		else
		{
			$accounted_payments = (float)$orderpayments->paymentdata[0]->paymentsAccountingsEUR[0];
			
			$fee["EUR"] = 0;
			$fee["FC"] = 0;
			
			if ( $accounted_payments != 0) // wegen status pending
			{
				$i=0;
				while (isset($orderpayments->paymentdata[0]->transaction[$i]->TransactionExchangeRate[0]))
				{
					if ((float)$orderpayments->paymentdata[0]->transaction[$i]->TransactionExchangeRate[0]!=0)
					{
						$fee["EUR"]+=(float)$orderpayments->paymentdata[0]->transaction[$i]->TransactionFee[0] / (float)$orderpayments->paymentdata[0]->transaction[$i]->TransactionExchangeRate[0];
		
					}
					else
					{
						show_error(9839,9, __FILE__, __LINE__, "OrderID: ".$orderid, false);
						echo "FEHLER KEINE EXRATE".$i." ".(float)$orderpayments->paymentdata[0]->transaction[$i]->TransactionExchangeRate[0];
						//print_r($orderpayments);
						exit;
					}
					
					$fee["FC"]+=(float)$orderpayments->paymentdata[0]->transaction[$i]->TransactionFee[0];
					$i++;	
				}
				$fee["EUR"]=round($fee["EUR"], 2);
			}
		}
		
		return $fee;
	
	}

//*********************************************************************************************

		//GET ERROR
		$res_error = q("SELECT * FROM idims_zlg_error WHERE id_error = ".$_POST["error_id"], $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($res_error) == 0 )
		{
			//show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			echo "Fehler nicht gefunden";
			exit;
		}

		$error = mysqli_fetch_assoc($res_error);
		
		$order_id = $error["order_id"];
		$invoice_id = $error["invoice_id"];
		
		// GET INVOICE
		$res_invoice = q("SELECT * FROM idims_auf_status WHERE rng_id = ".$invoice_id, $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($res_invoice) == 0 )
		{
			//show_error(9797, 8, __FILE__, __LINE__, "ServiceResponse".print_r($response, true).print_r($postfields, true));
			echo "Keine Rechnung in Idims_auf_status gefunden";
			exit;
		}
		
		$invoice = mysqli_fetch_assoc($res_invoice);
		
		if ( isset($_POST["betrag"]) && $_POST["betrag"] != 0 && $_POST["betrag"] != 0 )
		{
			$betrag = number_format($_POST["betrag"], 2, ",", "");
		}
		else
		{
			$betrag = number_format($order["ordertotalEUR"], 2, ",", "");
		}
		
		$order=getOrderData($order_id);
		
		$fee = getFEE($order_id);
		
		$difference = $invoice["rng_brutto"]-$order["ordertotalEUR"];


		$opXml = '';
		
		$opXml .= '	<ZLG>'."\n";
		//INVOICE ID
		$opXml .= '		<AUFID>'.$invoice_id.'</AUFID>'."\n";
		//IDIMS USERID DES NUTZERS
		//$opXml .= '		<USRID>'.$idims_user_id.'</USRID>'."\n";
		$opXml .= '		<USRID>328</USRID>'."\n";
		// BRUTTO
		$opXml .= '		<BETRAG>'.$betrag.'</BETRAG>'."\n";
		// ZLG
		$opXml .= '		<TYP>'.$payments[$order["payments_type_id"]]["ZLG"].'</TYP>'."\n";
		$opXml .= '	</ZLG>'."\n";
		
		//SPEICHERE INS IDIMS_ZLG_LOG
		$datafield = array();
		$datafield["invoice_id"] = $invoice_id;
		$datafield["invoice_time"] = $invoice["rng_time"];
		$datafield["shop_id"] = $order["shop_id"];
		$datafield["payment_type_id"] = $order["payments_type_id"];
		$datafield["amount"] = $order["ordertotalEUR"];
		$datafield["fee_EUR"] = $fee["EUR"];
		$datafield["fee_FC"] = $fee["FC"];
		$datafield["rng_brutto"] = $invoice["rng_brutto"];
		$datafield["difference"] = $difference;
		$datafield["idims_call"] = $opXml;
		$datafield["creation_time"] = time();
		$datafield["created_manual"] = 1;
		if (isset($_POST["expired"]) && $_POST["expired"] == 1 )
		{
			$datafield["acknowledgment"] = "Expired";
		}
		$datafield["order_total_EUR"] = $order["ordertotalEUR"];
		$datafield["order_total_FC"] = $order["ordertotal"];
		$datafield["currency_code"] = $order["currency"];
		$datafield["order_exchange_rate_from_EUR"] = $order["exchangerate"];
		
		$res_ins =q_insert("idims_zlg_log", $datafield, $dbshop, __FILE__, __LINE__);
	

?>