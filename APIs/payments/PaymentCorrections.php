<?php

	$start = time();
	check_man_params(array("mode" =>"textNN"));			
	
	//GET SHOPS
	$shops = array();
	$res_shops = q("SELECT * FROM shop_shops", $dbshop, __FILE__, __LINE__);
	while ( $row_shops = mysqli_fetch_assoc( $res_shops ) )
	{
		$shops[$row_shops["id_shop"]]=$row_shops;
	}

	

	if ($_POST["mode"]=="9825_process_PayPal_IPNmessages")
	{
		/*
			1) suche alle Fehler aus idims_zlg_error mit error_code = 9825	
			2) suche Order aus shop_orders
				-> Wenn Shop_type = 2 -> Suche alle TransaktionIDs aus shop_orders_items
				-> andere Shops -> Suche TxnIDs aus shop_orders
			3) Suche anhand der TxnIDs zugehörige IPNmessages (processed =0)
			4) führe Buchungen der IPNs durch
			5) "SUCCESS" -> lösche Fehler aus idims_zlg_error
		*/
		
		
		$res_errors = q("SELECT * FROM idims_zlg_error WHERE error_code = 9825 ORDER BY id_error DESC LIMIT 500", $dbshop, __FILE__, __LINE__);
		while ( $row_errors = mysqli_fetch_assoc( $res_errors ) )
		{

			if (time()-$start<60) //timeout
			{
				$ipnm_id = array();
				$items = array();
				// GET ORDER
				$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$row_errors["order_id"], $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows( $res_order ) == 1 )
				{
					$order = mysqli_fetch_assoc( $res_order );
					
					if ($order["payments_type_id"]==4)
					{
						// EBAY
						if ( $shops[$order["shop_id"]]["shop_type"] == 2 )
						{
							$items = array();
							//GET COMBINED ITEMS
							if ( $order["combined_with"]>0)	
							{
								$res_items = q("SELECT b.* FROM shop_orders as a, shop_orders_items as b WHERE a.combined_with = ".$order["combined_with"]." AND a.id_order = b.order_id", $dbshop, __FILE__, __LINE__);
							}
							else
							{
								$res_items = q("SELECT * FROM shop_orders_items WHERE order_id = ".$order["id_order"], $dbshop, __FILE__, __LINE__);
							}
		
							while ( $row_items = mysqli_fetch_assoc( $res_items ) )
							{
								if ($row_items["foreign_transactionID"]!="")
								{
									$items[] = $row_items;	
									//GET IPNmesssages
									$res_IPNm = q("SELECT * FROM payment_notification_messages WHERE message LIKE '%".$row_items["foreign_transactionID"]."%' AND payment_type_id = 4 AND processed = 0", $dbshop, __FILE__, __LINE__);
									while ( $row_IPNm = mysqli_fetch_assoc( $res_IPNm ) )
									{
										$ipnm_id[$row_IPNm["id"]] = $row_IPNm;
									}
								}
							}
						}
						// ANDERE SHOPS
						else
						{
							if ( $order["Payments_TransactionID"]!="")
							{
								//GET IPNmesssages
								$res_IPNm = q("SELECT * FROM payment_notification_messages WHERE message LIKE '%".$order["Payments_TransactionID"]."%' AND payment_type_id = 4 AND processed = 0", $dbshop, __FILE__, __LINE__);
								while ( $row_IPNm = mysqli_fetch_assoc( $res_IPNm ) )
								{
									$ipnm_id[$row_IPNm["id"]] = $row_IPNm;
								}
							}
						}
					} // if ($order["payments_type_id"]==4)
	
				echo "ORDER: ".$order["id_order"]." Shop_id: ".$order["shop_id"];
				
				ksort($ipnm_id);
				
				foreach ( $ipnm_id as $id => $data )
				{
					echo " Message: ".$id;
					
					//CALL PAYMENTNOTIFICATION HANDLER
					$postfield = array();
					$postfield['API']			= "payments";
					$postfield['APIRequest']	= "PaymentsNotificationSet_PayPal";
					$postfield['id']			= $id;
					
					$response = soa2($postfield, __FILE__, __LINE__);
					
					if ((string)$response->Ack[0] == "Success")
					{
						//DELETE ERROR
						q("DELETE FROM idims_zlg_error WHERE id_error = ".$row_errors["id_error"], $dbshop, __FILE__, __LINE__);
						//IPNmessage processed
						q("UPDATE payment_notification_messages SET processed = 1 WHERE id = ".$id,  $dbshop, __FILE__, __LINE__);
						echo " SUCCESS";
					}
					else
					{
						echo "ERROR";
					}
						
					
				}
				echo "\n\r";
	
	
				} // if ( mysqli_num_rows( $res_order ) == 1 )
				
			} // TIMEOUT
		
		} // while ( $row_errors = mysqli_fetch_assoc( $res_errors ) )
	
	} //if ($_POST["mode"]=="9825_process_PayPal_IPNmessages")

	if ($_POST["mode"]=="9823_check_accountable_payments")
	{
		
		/*
			Skript prüft zu Fehlermeldungen, ob noch offene Paymentdeposits auf offene Orderdeposits gecht werden können -> Durchführen der Buchung
		
		*/
		
		if ( $_POST["mode"]=="9823_check_accountable_payments" ) 
		{
			$error_code = 9823;
		}
		
		$counter = 0;
		
		$res_errors = q("SELECT * FROM idims_zlg_error WHERE error_code = ".$error_code." ORDER BY id_error DESC", $dbshop, __FILE__, __LINE__);
		while ( $row_errors = mysqli_fetch_assoc( $res_errors ) )
		{
			if (time()-$start<60) //timeout
			{
				echo "Order: ".$row_errors["order_id"];
			
				//GET ORDERPAYMENTS
				$postfield = array();
				$postfield['API']			= "payments";
				$postfield['APIRequest']	= "OrderPaymentsGet";
				$postfield['orderid']		= $row_errors["order_id"];
	
				$response = soa2($postfield, __FILE__, __LINE__);
				
				$i = 0;
				
				$paymentdeposit = array();
				$paymentdeposit_sum = 0;
				
				if ( (string)$response->Ack[0] == "Success" )
				{
					
					while ( isset( $response->paymentdata[0]->transaction[$i]->transactionID[0]) )
					{
						$paymentdeposit[(string)$response->paymentdata[0]->transaction[$i]->transactionID[0]] = (float)$response->paymentdata[0]->transaction[$i]->lastpaymentdeposit[0];
						$paymentdeposit_sum += (float)$response->paymentdata[0]->transaction[$i]->lastpaymentdeposit[0];
						
					
						$i++;
	
					}
					
					$orderdeposit=(float)$response->orderdata[0]->lastOrderDeposit[0];
					
					if ($orderdeposit + $paymentdeposit_sum == 0 && $orderdeposit != 0 && $paymentdeposit_sum != 0)
					{
						echo " ZAHLUNG nachbuchen";
						$counter++;
						
						$OK = false;
						
						foreach ( $paymentdeposit as $txnid => $deposit )
						{
							if ($deposit > 0)
							{
								
								//CHECK IF PAYMENT IS ALREADY LINKED TO ORDER
								$postfield = array();
								$postfield['API']			= "payments";
								$postfield['APIRequest']	= "PaymentGet";
								$postfield['TransactionID']	= $txnid;
								
								$response = soa2($postfield, __FILE__, __LINE__);
								
								if ( (string)$response->Ack[0] == "Success" )
								{
									$linkedOrder = (int)$response->LinkedToOrder[0];	
									
									
								
									if ($linkedOrder != $row_errors["order_id"])
									{
										//LINKING PAYMENT
										echo " LINKING PAYMENT";
										
										$postfield = array();
										$postfield['API']			= "payments";
										$postfield['APIRequest']	= "PaymentNotificationHandler";
										$postfield['mode']			= "LinkingPayment";
										$postfield['orderid']		= $row_errors["order_id"];
										$postfield['TransactionID']	= $txnid;
										
										$response = soa2($postfield, __FILE__, __LINE__);
	
										//PAYMENT
										if ( (string)$response->Ack[0] == "Success" )
										{
											$postfield = array();
											$postfield['API']			= "payments";
											$postfield['APIRequest']	= "PaymentNotificationHandler";
											$postfield['mode']			= "Payment";
											$postfield['orderid']		= $row_errors["order_id"];
											$postfield['TransactionID']	= $txnid;
											
											$response = soa2($postfield, __FILE__, __LINE__);
	
											if ( (string)$response->Ack[0] == "Success" )
											{
												echo " SUCCESS";
												$OK = true;
											}
										}
										
									}
									else
									{
										//PAYMENT
										
										$postfield = array();
										$postfield['API']			= "payments";
										$postfield['APIRequest']	= "PaymentNotificationHandler";
										$postfield['mode']			= "Payment";
										$postfield['orderid']		= $row_errors["order_id"];
										$postfield['TransactionID']	= $txnid;
										
										$response = soa2($postfield, __FILE__, __LINE__);
	
										if ( (string)$response->Ack[0] == "Success" )
										{
											echo " SUCCESS";
											$OK = true;
										}
										
										echo " Direct Accounting";
									}
									
								}
							}
						}
						if ($OK)
						{
							//DELETE ERROR
							q("DELETE FROM idims_zlg_error WHERE id_error = ".$row_errors["id_error"], $dbshop, __FILE__, __LINE__);
						}
			
					} //if ($orderdeposit + $paymentdeposit_sum == 0 && $orderdeposit != 0 && $paymentdeposit_sum != 0)
					
					
					echo " Orderdeposit: ".$orderdeposit;
					echo " Paymentdeposit: ".$paymentdeposit_sum;
				} //if ( (string)$response->Ack[0] == "Success" )
				else
				{
					
					echo " FAILURE";
				
				}
			
				echo "\n\r";
	
			}// if (time()-$start<60) //timeout
		} //while ( $row_errors = mysqli_fetch_assoc( $res_errors ) )

		echo "Durchgeführte Buchungen: ".$counter;
		
	} // if ($_POST["mode"]=="9823_check_accountable_payments")


	if ($_POST["mode"]=="9825_check_accountable_payments")
	{
		/*
			1) suche alle Fehler aus idims_zlg_error mit error_code = 9825	
			2) suche Order aus shop_orders
				-> Wenn Shop_type = 2 -> Suche alle TransaktionIDs aus shop_orders_items
				-> andere Shops -> Suche TxnIDs aus shop_orders
			3) Suche anhand der TxnIDs zugehörige paymentnotifications (type 1)
			4) führe Buchungen der IPNs durch
			5) "SUCCESS" -> lösche Fehler aus idims_zlg_error
		*/
		
		$counter = 0;
		
		$res_errors = q("SELECT * FROM idims_zlg_error WHERE error_code = 9825 ORDER BY id_error DESC", $dbshop, __FILE__, __LINE__);
		while ( $row_errors = mysqli_fetch_assoc( $res_errors ) )
		{

			echo "Order: ".$row_errors["order_id"];

			if (time()-$start<60) //timeout
			{
				$TxnID = array();
				$items = array();
				// GET ORDER
				$res_order = q("SELECT * FROM shop_orders WHERE id_order = ".$row_errors["order_id"], $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows( $res_order ) == 1 )
				{
					$order = mysqli_fetch_assoc( $res_order );
					
					if ($order["payments_type_id"]==4)
					{
						// EBAY
						if ( $shops[$order["shop_id"]]["shop_type"] == 2 )
						{
							$items = array();
							//GET COMBINED ITEMS
							if ( $order["combined_with"]>0)	
							{
								$res_items = q("SELECT b.* FROM shop_orders as a, shop_orders_items as b WHERE a.combined_with = ".$order["combined_with"]." AND a.id_order = b.order_id", $dbshop, __FILE__, __LINE__);
							}
							else
							{
								$res_items = q("SELECT * FROM shop_orders_items WHERE order_id = ".$order["id_order"], $dbshop, __FILE__, __LINE__);
							}
		
							while ( $row_items = mysqli_fetch_assoc( $res_items ) )
							{
								if ($row_items["foreign_transactionID"]!="")
								{
									$items[] = $row_items;	
									//GET IPNmesssages
									$res_IPN = q("SELECT * FROM payment_notifications WHERE orderTransactionID = '".$row_items["foreign_transactionID"]."' AND payment_type_id = 4", $dbshop, __FILE__, __LINE__);
									while ( $row_IPN = mysqli_fetch_assoc( $res_IPN ) )
									{
										$TxnID[$row_IPN["paymentTransactionID"]] = $row_IPNm;
									}
								}
							}
						} // IF EBAY
					} // if ($order["payments_type_id"]==4) 
				} // if ( mysqli_num_rows( $res_order ) == 1 )
				

				if ( sizeof($TxnID)>0 ) 
				{

					$OK = false;
					foreach ( $TxnID as $txnid => $data) 
					{
						echo " TxnID: ".$txnid;	

						//CHECK IF PAYMENT IS ALREADY LINKED TO ORDER
						$postfield = array();
						$postfield['API']			= "payments";
						$postfield['APIRequest']	= "PaymentGet";
						$postfield['TransactionID']	= $txnid;
						
						$response = soa2($postfield, __FILE__, __LINE__);
						
						if ( (string)$response->Ack[0] == "Success" )
						{
							$linkedOrder = (int)$response->LinkedToOrder[0];	
							
							if ((float)$response->Last_PaymentDeposit[0] > 0)
							{
								echo " Paymentdeposit: ".(float)$response->Last_PaymentDeposit[0];
							
								if ($linkedOrder != $row_errors["order_id"])
								{
									//LINKING PAYMENT
									echo " LINKING PAYMENT";
									
									$postfield = array();
									$postfield['API']			= "payments";
									$postfield['APIRequest']	= "PaymentNotificationHandler";
									$postfield['mode']			= "LinkingPayment";
									$postfield['orderid']		= $row_errors["order_id"];
									$postfield['TransactionID']	= $txnid;
									
									$response = soa2($postfield, __FILE__, __LINE__);
	
									//PAYMENT
									if ( (string)$response->Ack[0] == "Success" )
									{
										$postfield = array();
										$postfield['API']			= "payments";
										$postfield['APIRequest']	= "PaymentNotificationHandler";
										$postfield['mode']			= "Payment";
										$postfield['orderid']		= $row_errors["order_id"];
										$postfield['TransactionID']	= $txnid;
										
										$response = soa2($postfield, __FILE__, __LINE__);
	
										if ( (string)$response->Ack[0] == "Success" )
										{
											echo " SUCCESS";
											$OK = true;
										}
									}
									
								}
								else
								{
									//PAYMENT
									
									$postfield = array();
									$postfield['API']			= "payments";
									$postfield['APIRequest']	= "PaymentNotificationHandler";
									$postfield['mode']			= "Payment";
									$postfield['orderid']		= $row_errors["order_id"];
									$postfield['TransactionID']	= $txnid;
									
									$response = soa2($postfield, __FILE__, __LINE__);
	
									if ( (string)$response->Ack[0] == "Success" )
									{
										echo " SUCCESS";
										$OK = true;
									}
									
									echo " Direct Accounting";
								}
							}
						}
				
					}
					if ($OK)
					{
						//DELETE ERROR
						q("DELETE FROM idims_zlg_error WHERE id_error = ".$row_errors["id_error"], $dbshop, __FILE__, __LINE__);
						$counter++;
					}


				}

				echo "\n\r";
			} // TIMEOUT
		} //while ( $row_errors = mysqli_fetch_assoc( $res_errors ) )
		
		echo "ANZAHL: ".$counter;
		
	} // if ($_POST["mode"]=="9825_check_accountable_payments")

	if ($_POST["mode"]=="9823_check_ordertotal")
	{
		$res_errors = q("SELECT * FROM idims_zlg_error WHERE error_code = 9823 ORDER BY id_error DESC LIMIT 200", $dbshop, __FILE__, __LINE__);
		while ( $row_errors = mysqli_fetch_assoc( $res_errors ) )
		{
			echo "Order: ".$row_errors["order_id"];
			//GET LASTORDERTOTAL (PaymentNotifications)
			$postfield['API']			= "payments";
			$postfield['APIRequest']	= "PaymentNotificationLastOrderTotalGet";
			$postfield['orderid']		= $row_errors["order_id"];

			$response = soa2($postfield, __FILE__, __LINE__);

			if ( (string)$response->Ack[0] == "Success" )
			{
				echo " OrderTotal(IPN): ".(float)$response->ordertotalEUR[0];
				$ordertotal = (float)$response->ordertotalEUR[0];
			}
			//GET LASTORDERTOTAL (PaymentNotifications)
			$postfield['API']			= "shop";
			$postfield['APIRequest']	= "OrderDetailGet_neu";
			$postfield['OrderID']		= $row_errors["order_id"];

			$response = soa2($postfield, __FILE__, __LINE__);

			if ( (string)$response->Ack[0] == "Success" )
			{
				echo " OrderTotal - Gross (shop): ".(string)$response->Order[0]->orderTotalGross[0];
				echo " OrderTotal - Complete (shop): ".(string)$response->Order[0]->completeTotalGross[0];
				
//				$ordertotalgross = number_format((string)$response->Order[0]->orderTotalGross[0], 2, ".", "")*1;
				$ordertotalgross = (float)str_replace(",",".",(string)$response->Order[0]->orderTotalGross[0]);
				echo " ".$diff = round($ordertotal-(float)$ordertotalgross,2);
				
				if ($diff == -0.01 || $diff == 0.01)
				{
					echo " ORDERADJUSTMENT";
					
					//GET ORDERIDS
					$orders = array();
					$res_orders = q("SELECT * FROM shop_orders WHERE id_order = ".$row_errors["order_id"], $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_orders) == 1)
					{
						$row_order = mysqli_fetch_assoc($res_orders);
						if ($row_order["combined_with"]>0)
						{
							$res_orders = q("SELECT * FROM shop_orders WHERE combined_with = ".$row_order["combined_with"], $dbshop, __FILE__, __LINE__);
							while ($row_orders = mysqli_fetch_assoc($res_orders))
							{
								$orders[$row_orders["id_order"]] = $row_orders;
							}
						}
						else
						{
							$orders[$row_order["id_order"]] = $row_order;
						}
					}
						
					foreach ($orders as $order_id => $data)
					{
						// SET ORDEREVENT
						$postfield = array();
						$postfield['API']=			'shop';
						$postfield['APIRequest']=	'OrderEventSet';
						$postfield['order_id']=		$order_id;
						$postfield['eventtype_id']=	29;
						$response = soa2($postfield, __LINE__, __FILE__);

						if ( (string)$response->Ack[0] == "Success" )
						{
							$event_id = (int)$response->id_event[0];
							//ORDERADJUSTMENT
							$postfield = array();
							$postfield['API']=			'payments';
							$postfield['APIRequest']=	'PaymentNotificationHandler';
							$postfield['mode']=			'OrderAdjustment';
							$postfield['orderid']=		$order_id;
							$postfield['order_event_id']=$event_id;
							$response = soa2($postfield, __LINE__, __FILE__);

							if ( (string)$response->Ack[0] == "Success" )
							{
								echo " DELETE ERROR";
								// DELETE ERROR	
								q("DELETE FROM idims_zlg_error WHERE id_error = ".$row_errors["id_error"], $dbshop, __FILE__, __LINE__);
							}
						
						}
					}
					
				}
				
			}
			echo "\n\r";
		
		}
	}
	
	if ($_POST["mode"]=="9825_check_error_code")
	{
		$counter = 0;
		
		$res_errors = q("SELECT * FROM idims_zlg_error WHERE error_code = 9825 ORDER BY id_error DESC", $dbshop, __FILE__, __LINE__);
		while ( $row_errors = mysqli_fetch_assoc( $res_errors ) )
		{
			echo "Order: ".$row_errors["order_id"];
			//GET LASTORDERTOTAL (PaymentNotifications)
			$postfield['API']			= "payments";
			$postfield['APIRequest']	= "OrderPaymentsGet";
			$postfield['orderid']		= $row_errors["order_id"];

			$response = soa2($postfield, __FILE__, __LINE__);

			if ( (string)$response->Ack[0] == "Success" )
			{
				$ordertotal = (float)$response->orderdata[0]->lastOrderTotalEUR[0];
				$orderdeposit = (float)$response->orderdata[0]->lastOrderDepositEUR[0];
				
				$paymentaccountings = (float)$response->paymentdata[0]->paymentsAccountingsEUR[0];
				$paymentdeposit_sum = 0;
				
				$i = 0;
				while ( isset($response->paymentdata[0]->transaction[$i]->lastpaymentdeposit[0]))
				{
					$paymentdeposit_sum+=(float)$response->paymentdata[0]->transaction[$i]->lastpaymentdeposit[0];
					$i++;	
				}
				
				if ( $ordertotal == $paymentaccountings && $ordertotal != 0 && $paymentaccountings != 0 && $orderdeposit == 0 && $paymentdeposit_sum == 0 )
				{
					echo " OrderTotal: ".$ordertotal;
					echo " OrderDeposit: ".$orderdeposit;
					echo " Paymentaccountings: ".$paymentaccountings;
					echo " PaymentDeposits: ".$paymentdeposit_sum;
					
					echo " DELETE ERROR";
					q("DELETE FROM idims_zlg_error WHERE id_error = ".$row_errors["id_error"], $dbshop, __FILE__, __LINE__);
					$counter++;
				}
				
			}
			echo "\n\r";
		}
		echo $counter." Fehler gelöscht";
	}
	
	
	if ($_POST["mode"]=="9823_check_error_code")
	{
		$counter = 0;
		
		$res_errors = q("SELECT * FROM idims_zlg_error WHERE error_code = 9823 ORDER BY id_error DESC", $dbshop, __FILE__, __LINE__);
		while ( $row_errors = mysqli_fetch_assoc( $res_errors ) )
		{
			echo "Order: ".$row_errors["order_id"];
			//GET LASTORDERTOTAL (PaymentNotifications)
			$postfield['API']			= "payments";
			$postfield['APIRequest']	= "OrderPaymentsGet";
			$postfield['orderid']		= $row_errors["order_id"];

			$response = soa2($postfield, __FILE__, __LINE__);

			if ( (string)$response->Ack[0] == "Success" )
			{
				$ordertotal = (float)$response->orderdata[0]->lastOrderTotalEUR[0];
				$orderdeposit = (float)$response->orderdata[0]->lastOrderDepositEUR[0];
				
				$paymentaccountings = (float)$response->paymentdata[0]->paymentsAccountingsEUR[0];
				$paymentdeposit_sum = 0;
				
				$i = 0;
				while ( isset($response->paymentdata[0]->transaction[$i]->lastpaymentdeposit[0]))
				{
					$paymentdeposit_sum+=(float)$response->paymentdata[0]->transaction[$i]->lastpaymentdeposit[0];
					$i++;	
				}
				
				if ( $ordertotal == $paymentaccountings && $ordertotal != 0 && $paymentaccountings != 0 && $orderdeposit == 0 && $paymentdeposit_sum == 0 )
				{
					echo " OrderTotal: ".$ordertotal;
					echo " OrderDeposit: ".$orderdeposit;
					echo " Paymentaccountings: ".$paymentaccountings;
					echo " PaymentDeposits: ".$paymentdeposit_sum;
					
					echo " DELETE ERROR";
					q("DELETE FROM idims_zlg_error WHERE id_error = ".$row_errors["id_error"], $dbshop, __FILE__, __LINE__);
					$counter++;
				}
				
			}
			echo "\n\r";
		}
		echo $counter." Fehler gelöscht";
	}

?>