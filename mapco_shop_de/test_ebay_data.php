<?php
	include("config.php");
/*
	// CHECK FOR UNIQUE TRANSACTIONIDs IN shop_orders_items
	$items = array();
	$duplicate = array();
	$res = q("SELECT * FROM shop_orders_items WHERE NOT foreign_transactionID = '' AND order_id > 1720000 ORDER BY order_id;", $dbshop, __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($res))
	{
		if (isset($items[$row["foreign_transactionID"]]))
		{
			$items[$row["foreign_transactionID"]]++;
			$duplicate[$row["foreign_transactionID"]]=$items[$row["foreign_transactionID"]];
			
		}
		else
		{
			$items[$row["foreign_transactionID"]]=1;
		}
	}
	
	foreach ($duplicate as $transactionID => $rest)
	{
		
		echo $transactionID."~".$duplicate[$transactionID]."<br />";
	}
*/
/*
//GET ORDERS WITH NO BILL ADRESS ID
$res = q ("SELECT * FROM shop_orders WHERE shop_id IN (3,4,5) AND bill_adr_id = 0;", $dbshop, __FILE__, __LINE__);
echo "Orders ohne BillAdrID: ".mysqli_num_rows($res)."<br />";
$res = q ("SELECT * FROM shop_orders WHERE shop_id IN (3,4,5) AND bill_adr_id = 0 AND NOT bill_street ='' AND NOT customer_id = 0;", $dbshop, __FILE__, __LINE__);
echo "Orders ohne BillAdrID die eine Adresse haben: ".mysqli_num_rows($res)."<br />";
$userids=array();
$userids2=array();
$f_orderid=array();
while ($row = mysqli_fetch_array($res))
{
	$userids[]=$row["customer_id"];
	$userids2[$row["customer_id"]]=1;
	
	echo "UserID".$row["customer_id"];
	
	//GET F_ID from EBAY
	$res_f_adr = q("SELECT * FROM ebay_orders WHERE OrderID = '".$row["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_f_adr)>0)
	{
		$row_f_adr = mysqli_fetch_array($res_f_adr);
		echo " F_adrID: ".$row_f_adr["ShippingAddressAddressID"];
		
		$res_shop_adr = q("SELECT * FROM shop_bill_adr WHERE user_id = ".$row["customer_id"]." AND foreign_address_id ='".$row_f_adr["ShippingAddressAddressID"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shop_adr)>0)
		{
			$row_shop_adr = mysqli_fetch_array($res_shop_adr);
			echo " SHOP_BILL_ADR_ID: ".$row_shop_adr["adr_id"];
			if (mysqli_num_rows($res_shop_adr)>1) {
				echo "MULTIPLE<br />"; }
			 else {
				//  q("UPDATE shop_orders SET bill_adr_id = ".$row_shop_adr["adr_id"]." WHERE id_order = ".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
			 		echo "UPDATED!!!!!!!!!!!!";
					echo "<br />";
			 
			 
			 }
		}
		else echo "<br />";
	}
	else echo "<br />";
}
echo "DAVON ".sizeof($userids2)." verschiedene User <br />";
	
$res_adr = q("SELECT * FROM shop_bill_adr WHERE user_id IN (".implode(",", $userids).") AND NOT foreign_address_id = '';", $dbshop, __FILE__, __LINE__);
echo "ES existieren ".mysqli_num_rows($res_adr)." Adresse <br />";
*/

//FIND ORDERS WITH NO ITEMS
/*
$items = array();
$res_items = q ("SELECT order_id FROM shop_orders_items GROUP BY order_id;", $dbshop, __FILE__, __LINE__);
while ($row_items = mysqli_fetch_array($res_items))
{
	$items[$row_items["order_id"]]=1;
}
$orders = array();
$counter=0;
$res_orders = q ("SELECT id_order FROM shop_orders WHERE NOT ordertype_id = 6;", $dbshop, __FILE__, __LINE__);
while ($row_orders = mysqli_fetch_array($res_orders))
{
	if (!isset($items[$row_orders["id_order"]])  ) 
	{
		echo $row_orders["id_order"]."<br />";
		$counter++;
	}
}
echo "COUNTER: ".$counter;
*/

/*
//GET ORDERS WITH CUSTOMER ID = 0
$res = q("SELECT * FROM shop_orders WHERE NOT customer_id = 0;", $dbshop, __FILE__, __LINE__);
echo mysqli_num_rows($res);
$users = array();
$counter=0;
while ($row = mysqli_fetch_array($res))
{
	if (!isset($users[$row["customer_id"]][$row["shop_id"]]))
	{
		$users[$row["customer_id"]][$row["shop_id"]]=1;
	}
	else
	{
		$users[$row["customer_id"]][$row["shop_id"]]++;
	}
}

foreach ($users as $user => $shopid)
{
	if (sizeof($shopid)>1)
	{
		echo "USERID: ".$user;
		foreach ($shopid as $shop => $counts)
		{
			echo " Shop: <b>".$shop."</b>*".$counts;
		}
		echo "<br />";
		
		$counter++;
	}
}

echo "ANZAHL: ".$counter;
*/
/*
//GET SHOPS && SITE IDs
$site_shop = array();
$res_shop = q("SELECT * FROM shop_shops WHERE NOT site_id =0 AND active = 1;", $dbshop, __FILE__, __LINE__);
while ($row_shops = mysqli_fetch_array($res_shop))
{
	$site_shop[$row_shops["site_id"]]=$row_shops["id_shop"];
}

//GET USERS
$users=array();
$res_users=q("SELECT * FROM cms_users_sites;", $dbweb, __FILE__, __LINE__);
while ($row_users = mysqli_fetch_array($res_users))
{
	if (isset($users[$row_users["user_id"]]) && $users[$row_users["user_id"]]!=$row_users["site_id"]) 
	{
//		echo "MULTISITE: ".$row_users["user_id"]."<br />";
		$users[$row_users["user_id"]]=0;
	}
	else
	{
		$users[$row_users["user_id"]]=$row_users["site_id"];
		
	}
}
echo "+++++++++++++++++++++++++++++++++++++++++++++++++<br />";
$counter=0;
$customer_err=array();
$res = q("SELECT * FROM shop_orders WHERE NOT customer_id = 0 and shop_id = 1 ORDER BY firstmod", $dbshop, __FILE__, __LINE__);
echo mysqli_num_rows($res);
while ($row = mysqli_fetch_array($res))
{
	if ($row["shop_id"] != $site_shop[$users[$row["customer_id"]]] && $users[$row["customer_id"]]!=0)
	{
		echo "USER: ".$row["customer_id"]." OrderID ".$row["id_order"]." SHOP_ID alt: ".$row["shop_id"]." ShopID neu: ".$site_shop[$users[$row["customer_id"]]]."<br />";
//		q("UPDATE shop_orders SET shop_id = ".$site_shop[$users[$row["customer_id"]]]." WHERE id_order = ".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
		$counter++;
		$customer_err[$row["customer_id"]]=1;
	}
}
echo "ANZAHL: ".$counter."<br />";

foreach ($customer_err as $user => $userdata)
{
	echo $user."<br />";
}

*/



/*
$res = q("SELECT * FROM shop_orders WHERE ordertype_id = 6", $dbshop, __FILE__, __LINE__);
echo mysqli_num_rows($res);
while ($row = mysqli_fetch_assoc($res))
{
	$res2 = q("SELECT * FROM shop_orders_items WHERE order_id = ".$row["id_order"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows ($res2)>0)
	{
		echo $row["id_order"]."<br />";	
	}
}*/


/*
$res = q("SELECT * FROM cms_users", $dbweb, __FILE__, __LINE__);
echo mysqli_num_rows($res)."<br />";

$user = array();
while ($row = mysqli_fetch_assoc($res))
{
	if (!isset($user[$row["username"]]))
	{
		$user[$row["username"]][0]=$row;
	}
	else
	{
		$user[$row["username"]][sizeof($user[$row["username"]])]=$row;
	}
}

//CREATE USERMAIL -> BuyerUserID LIST
$usermails = array();
$res_usermails = q("SELECT BuyerEmail, OrderID FROM ebay_orders_items", $dbshop, __FILE__, __LINE__);
while ($row_usermails = mysqli_fetch_assoc($res_usermails))
{	
	if ($row_usermails["BuyerEmail"]!="" && $row_usermails["BuyerEmail"]!="Invalid Request")
	{
		$usermails[$row_usermails["OrderID"]] = $row_usermails["BuyerEmail"];
	}
}

$BuyerUserIDs=array();
$res_UserIDs = q("SELECT OrderID, BuyerUserID FROM ebay_orders", $dbshop, __FILE__, __LINE__);
while ($row_UserIDs = mysqli_fetch_assoc($res_UserIDs))
{
	if (isset($usermails[$row_UserIDs["OrderID"]]))
	{
		$BuyerUserIDs[$usermails[$row_UserIDs["OrderID"]]]=$row_UserIDs["BuyerUserID"];
	}
}

//GET USERSITES
$usersites=array();
$res_usersites = q("SELECT * FROM cms_users_sites", $dbweb, __FILE__, __LINE__);
while ($row_usersites = mysqli_fetch_assoc($res_usersites))
{
		$usersites[$row_usersites["user_id"]] = $row_usersites["site_id"];
}

echo sizeof ($user)."<br />";
$counter=0;
echo "<table>";
foreach ($user as $username => $userdata)
{
	if (sizeof($userdata)>1 && substr($username,0,2)=="MA") 
	//if (sizeof($userdata)>1) 
	{
		
		foreach ($userdata as $Index => $userdata2)
		{
			echo "<tr>";
			//FINDE ERSETZUNG
			//FIND EBAY-USERNAME
			if (isset($BuyerUserIDs[$userdata2["usermail"]]))
			{
				$buyerUserID = $BuyerUserIDs[$userdata2["usermail"]];
			}
			else
			{
				$buyerUserID="######";
			}
			echo "<td>".$username."</td><td>".$userdata2["id_user"]."</td><td><b>".$usersites[$userdata2["id_user"]]."</b></td><td>".$userdata2["usermail"]."</td><td>".$buyerUserID."</td>";
			echo "</tr>";
		}
		echo "<tr></tr>";
	}
//	echo sizeof($userdata)."<br />";
}

*/





/*
$res = q("SELECT * FROM idims_auf_status", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	//GET IDIMS_ZLG_LOG
	$res_log = q("SELECT * FROM idims_zlg_log WHERE invoice_id = ".$row["rng_id"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_log)==1)
	{
		$row_log = mysqli_fetch_assoc($res_log);
		$dif = $row["rng_brutto"]-$row_log["amount"];
		q("UPDATE idims_zlg_log SET rng_brutto = ".$row["rng_brutto"]." , difference = ".$dif." WHERE invoice_id = ".$row["rng_id"], $dbshop, __FILE__, __LINE__);
	}
}
*/

/*
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
			
			if ($exchangerate==0 || $exchangerate=="" )
			{
				//$exchangerate=$currencies[$responsefield["currency"]]["exchange_rate_to_EUR"];
				$exchangerate=99;
			}
			
			$responsefield["exchangerate"]=$exchangerate;
			return $responsefield;
			
		}
	}

$start = time();
$res_zlg_log = q("SELECT * FROM idims_zlg_log WHERE order_total_EUR = 0 LIMIT 300", $dbshop, __FILE__, __LINE__);
echo "LOGS: ".mysqli_num_rows($res_zlg_log);

$counter=0;
while ($row_zlg_log = mysqli_fetch_assoc($res_zlg_log))
{
	$counter++;
	// GET ORDERDATA
	$res_order = q("SELECT * FROM shop_orders WHERE invoice_id = ".$row_zlg_log["invoice_id"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
	$row_order = mysqli_fetch_assoc($res_order);
	if ($row_order["combined_with"]>0)
	{
		$orderid = $row_order["combined_with"];
	}
	else
	{
		$orderid = $row_order["id_order"];
	}
	
	$order = getOrderData($orderid);
	
	echo $counter." CC:" .$order["currency"]." EXRATE: ".$order["exchangerate"]." TotalEUR: ".$order["ordertotalEUR"]." TotalFC: ".$order["ordertotal"]."<br />";

	q("UPDATE idims_zlg_log SET order_total_EUR =".$order["ordertotalEUR"].", order_total_FC =".$order["ordertotal"].", order_exchange_rate_from_EUR =".$order["exchangerate"].", currency_code ='".$order["currency"]."' WHERE id_log = ".$row_zlg_log["id_log"], $dbshop, __FILE__, __LINE__);
	
}
*/
/*
$start = time();

//CORRECT idims_auf_status
$res = q("SELECT * FROM idims_auf_status WHERE rng_time = 0", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	echo $t = $row["rng_timestamp"];
	$time = date("d.m.Y H:i:s", $t);
	echo $time."<br />";	
	q("UPDATE idims_auf_status SET rng_timestamp = '".$time."', rng_time = ".$t." WHERE auf_id = ".$row["auf_id"], $dbshop, __FILE__, __LINE__);
}
*/
/*
//$res = q("SELECT * FROM idims_zlg_log WHERE shop_id = 5 AND payment_type_id = 4 AND amount_transfer_time = 1392056910", $dbshop, __FILE__, __LINE__);
//while ($row = mysqli_fetch_assoc($res))
//{
//	$ex_rate = 0.8526244990928565; //1391707568
//	$ex_rate = 0.8526244990928565; //1391793488
	$ex_rate = 0.8471241548629656; //1392056910
//	$ex_rate = 0.8416238106330747; //1392243665
//	$ex_rate = 0.8403875845179151; //1392481784
//	$ex_rate = 0.8422782705880696; //1392674276
//	$ex_rate = 0.8446408901020486; //1392830761
//	$ex_rate = 0.845204437771755; //1393002314
	$ex_rate = 0.84806874067525; //1393262219
	q("UPDATE idims_zlg_log SET transfer_exchange_rate_from_EUR = ".$ex_rate." WHERE shop_id = 5 AND payment_type_id = 4 AND amount_transfer_time = 1392056910", $dbshop, __FILE__, __LINE__);
//}
*/

/*
$start = time();
$res = q("SELECT * FROM idims_zlg_log WHERE payment_type_id = 4 AND NOT amount_transfer_time = 0", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	$accounted = $row["amount"]-$row["fee"];
	q("UPDATE idims_zlg_log SET amount_transfered_EUR = ".$accounted." WHERE id_log = ".$row["id_log"], $dbshop, __FILE__, __LINE__);
}
*/

$start=time();

/*
$counter=0;
$counter2=0;
$differences=array();
$res=q("SELECT * FROM idims_zlg_log", $dbshop, __FILE__, __LINE__);
while ($row=mysqli_fetch_assoc($res))
{
	$OK=true;
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($row["idims_call"]);
	}
	catch(Exception $e)
	{
		echo "FEHER<br />";
		$OK=false;

	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if ($OK)
	{
		//echo (string)$response->AUFID[0]." ".(string)$response->BETRAG[0]." ".$row["amount"]."<br />";
		if ((string)$response->BETRAG[0]!=(string)number_format($row["rng_brutto"], 2,",",""))
		{
			echo "<b>".$row["shop_id"]."</b> ";
			echo (string)$response->AUFID[0]." ".(string)$response->BETRAG[0]." ".$row["rng_brutto"];
			//GET DIFFERENZ
			if ($row["difference"]<-0.20 || $row["difference"]>0.20)
			{
				echo " TOO MUCH DIFFERENCE";
				$counter2++;
			}
			else
			{
				// SET NEW XML
				$search='<BETRAG>'.(string)$response->BETRAG[0].'</BETRAG>';
				$replace = '<BETRAG>'.number_format($row["rng_brutto"], 2, ",", "").'</BETRAG>';
				$txt = str_replace($search, $replace, $row["idims_call"]);
				echo $txt;
				if (!isset($differences[$row["shop_id"]])) 
				{
					$differences[$row["shop_id"]]["diff"]=0;
					$differences[$row["shop_id"]]["count"]=0;
				}
				$differences[$row["shop_id"]]["diff"]+=$row["difference"];
				$differences[$row["shop_id"]]["count"]++;
				
				q("UPDATE idims_zlg_log SET idims_call = '".mysqli_real_escape_string($dbshop, $txt)."' WHERE id_log = ".$row["id_log"], $dbshop, __FILE__, __LINE__);
				
			}
		
			echo "<br />";	
			$counter++;
		}
	}

}

echo $counter." ".$counter2." <br />";

foreach ($differences as $shop_id => $data)
{
	echo "SHOP: <b>".$shop_id."</b> ".$data["count"]." ".$data["diff"]."<br />";
}

*/

/*
$orders = array();
//$res = q("SELECT * from shop_orders WHERE has_invoice_data = 1", $dbshop, __FILE__, __LINE__);
$res = q("SELECT * from shop_orders_test WHERE has_invoice_data = 0 AND NOT AUF_ID = 0", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	$orders[$row["AUF_ID"]]=0;
}

echo sizeof($orders)."<br />";
$invoices = array();
$res = q("SELECT * from idims_auf_status_test", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	$invoices[$row["auf_id"]]=0;
}
echo sizeof($invoices)."<br />";

$counter=0;
/*
foreach($orders as $order => $data)
{
	if (!isset($invoices[$order]))
	{
		echo $order."<br />";
		//q("update shop_orders SET has_invoice_data = 0 WHERE AUF_ID = ".$order, $dbshop, __FILE__, __LINE__);
		$counter++;
	}
	
}
*//*
foreach ($invoices as $aufid => $data)
{
	if (isset($orders[$aufid]))	
	{
		$counter++;
		echo $aufid."<br />";	
	}
	
}
echo "COUNTER: ".$counter."<br />";
*/

//$start=time();
// SET FEE_FC
/*
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

$invoices=array();
$res_zlg = q("SELECT * FROM idims_zlg_log WHERE fee_FC = 0  AND payment_type_id = 4 LIMIT 500", $dbshop, __FILE__, __LINE__);
while ($row_zlg = mysqli_fetch_assoc($res_zlg))
{
	$invoices[]=$row_zlg["invoice_id"];
}
echo sizeof ($invoices)."<br />";
$orders = array();
$res_orders = q("SELECT id_order, combined_with, invoice_id FROM shop_orders WHERE invoice_id IN (".implode($invoices, ",").")", $dbshop, __FILE__, __LINE__);
while ($row_orders = mysqli_fetch_assoc($res_orders))
{
	if ($row_orders["combined_with"]>0)
	{
		$order_id = $row_orders["combined_with"];
	}
	else
	{
		$order_id = $row_orders["id_order"];
	}
	
	$orders[$row_orders["invoice_id"]] = $order_id;
	
}
echo sizeof ($orders)."<br />";
echo "LAUFZEIT: ".(time()-$start)."<br />";


while (list ($invoiceid, $orderid) = each ($orders))
{
	if ($orderpayments=OrderPaymentsGet($orderid))
	{
		$i=0;
		$feeFC=0;
		while (isset($orderpayments->paymentdata[0]->transaction[$i]))
		{
			$feeFC+=(float)$orderpayments->paymentdata[0]->transaction[$i]->TransactionFee[0];
			$i++;	
		}
		echo $orderid." ".$feeFC."<br />";
		q("UPDATE idims_zlg_log SET fee_FC = ".$feeFC." WHERE invoice_id = ".$invoiceid, $dbshop, __FILE__, __LINE__);
		
	}
	else
	{
		echo "ERROR: ".$orderid."<br />"; 	
	}
	
}
*/

/*
$invoices=array();
$invoice_rng_betrag=array();
$res = q("SELECT * FROM idims_zlg_log", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	$invoices[]=$row["invoice_id"];
	$invoice_rng_betrag[$row["invoice_id"]]=round($row["rng_brutto"],2);
}
$oplist=array();
echo "INVOICES: ".sizeof($invoices)."<br />";
$res = q("SELECT * FROM idims_op_tmp", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	 $oplist[$row["invoice_id"]] = $row["betrag"];
}


$counter_success = 0;
$counter_error =0;
$dif_counter =0;
$diff_sum = 0;
foreach ($invoices as $invoice_id)
{
	 
	if (isset($oplist[$invoice_id]))
	{
		$diff = round($oplist[$invoice_id]-$invoice_rng_betrag[$invoice_id],2);
		if ($diff>0.01) echo "############";
		if ($diff<-0.01) echo "§§§§§";
		echo $invoice_id." ".$oplist[$invoice_id]." DIFF: ".$diff."<br />";
		if ($diff<0.02 && $diff>-0.02)
		{
			$dif_counter++;
			q("UPDATE idims_zlg_log SET rng_brutto = ".$oplist[$invoice_id]." WHERE invoice_id = ".$invoice_id, $dbshop, __FILE__, __LINE__);
			$diff_sum+=$diff;
		}
		$counter_success++;
	}
	else
	{
		echo $invoice_id." ERROR <br/>";
		$counter_error++;
	}
}

echo "SUCCESS: ".$counter_success."<br />";
echo "ERROR: ".$counter_error."<br />";
echo "DIFF_SUM: ".$diff_sum."<br />";
echo "DIFF_counter: ".$dif_counter."<br />";
*/
/*
$res = q("SELECT * FROM idims_zlg_log WHERE order_total_FC = 0", $dbshop, __FILE__, __LINE__);
while ($row=mysqli_fetch_assoc($res))
{
	q("UPDATE idims_zlg_log SET order_total_FC = ".$row["order_total_EUR"]." WHERE id_log = ".$row["id_log"], $dbshop, __FILE__, __LINE__);	
}
*/
/*
$start = time();

//GET LAST ORDER FROM paymentnotifications
$notifications = array();
$res_notification = q("SELECT * FROM payment_notifications WHERE notification_type = 2 order by id_PN DESC", $dbshop, __FILE__, __LINE__);
while ($row_notifications = mysqli_fetch_assoc($res_notification))
{
	if (!isset($notifications[$row_notifications["order_id"]]))
	{
		$notifications[$row_notifications["order_id"]]=$row_notifications["total"];
	}
}
//GET ORDERS STatus 4||8 oder ordertype = 6
$orders = array();
$res_orders = q("SELECT * FROM shop_orders WHERE (status_id = 4 OR status_id = 8 OR ordertype_id = 6) and firstmod>1391209200", $dbshop, __FILE__, __LINE__);
while ($row_orders = mysqli_fetch_assoc($res_orders))
{
	$orders[$row_orders["id_order"]] = $row_orders;
}
$counter=0;
foreach ($orders as $order_id => $order)
{
	if ((time()-$start)<60)
	{
		if (isset($notifications[$order_id]) && $notifications[$order_id]>0)
		{
			echo "ORDER ".$order_id." STATUS :".$order["status_id"]." OrderType :".$order["ordertype_id"]." TOTAL: ".$notifications[$order_id];
			$counter++;
			
			//BASISFELDER FÜR API-AUFRUF
			$fieldlist["API"]="payments";
			$fieldlist["APIRequest"]="PaymentNotificationHandler";
			$fieldlist["mode"]="OrderAdjustment";
			$fieldlist["orderid"]=$order_id;
			$fieldlist["order_event_id"]=1;
			
			$responseXML=post(PATH."soa2/", $fieldlist);
		
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				show_error(9756, 7, __FILE__, __LINE__, $responseXML, false);
				//exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]!="Success")
			{
				show_error(9773, 7, __FILE__, __LINE__, $responseXML, false);
				//exit;
			}
			
			unset($response);
			unset($responseXML);
			//PAYMENTNOTIFICATIONHANDLER -> REWRITE PAYMENTS
			$response = "";
			$responseXML = "";
			$fieldlist=array();
			//BASISFELDER FÜR API-AUFRUF
			$fieldlist["API"]="payments";
			$fieldlist["APIRequest"]="PaymentNotificationHandler";
			$fieldlist["mode"]="PaymentWriteBack";
			$fieldlist["orderid"]=$order_id;
			
			
			$responseXML=post(PATH."soa2/", $fieldlist);
		
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				show_error(9756, 7, __FILE__, __LINE__, $responseXML, false);
				//exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]!="Success")
			{
				show_error(9773, 7, __FILE__, __LINE__, $responseXML, false);
				//exit;
			}
			
			
			for ($i=0;isset($response->transactionID[$i]); $i++)
			{
				echo "RESPONSE TXNIDS:".(string)$response->transactionID[$i];
			}
			echo "<br />";
			unset($response);
			unset($responseXML);

		}
	}
}
echo "COUNTER: ".$counter."<br />";
*/
/*
$counter=0;
$zlg_logs = array();
$zlg_call = array();
$invoices = array();
//CHECK FOR ZLG LOGS WHERE AMOUNT <> order_total_EUR
$res = q("SELECT * FROM idims_zlg_log WHERE response_time = 0", $dbshop, __FILE__, __LINE__);
echo "UNVERSCHICKTE LOGS: ".mysqli_num_rows($res)."<br />";
while ($row = mysqli_fetch_assoc($res))
{
	$zlg_amount[$row["invoice_id"]]=$row["amount"];
	$zlg_logs[$row["invoice_id"]]=$row["rng_brutto"];
	$zlg_call[$row["invoice_id"]]=$row["idims_call"];
	$invoices[]=$row["invoice_id"];
}

//GET RECHNUNGSDATEN
$res = q("SELECT * FROM idims_auf_status WHERE rng_id IN (".implode(", ", $invoices).")", $dbshop, __FILE__, __LINE__);
echo "Matched Invoices: ".mysqli_num_rows($res)."<br />";
while($row = mysqli_fetch_assoc($res))
{
	if ($zlg_amount[$row["rng_id"]]!=$row["rng_brutto"])
	{
		echo $row["rng_id"]." ".$zlg_logs[$row["rng_id"]]." ".$row["rng_brutto"]."<br />";
		$counter++;
		
		$OK=true;
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($zlg_call[$row["rng_id"]]);
		}
		catch(Exception $e)
		{
			echo "FEHER<br />";
			$OK=false;
	
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($OK)
		{
			//echo (string)$response->AUFID[0]." ".(string)$response->BETRAG[0]." ".$row["amount"]."<br />";
					// SET NEW XML
			$search='<BETRAG>'.(string)$response->BETRAG[0].'</BETRAG>';
			$replace = '<BETRAG>'.number_format($zlg_amount[$row["rng_id"]], 2, ",", "").'</BETRAG>';
			$txt = str_replace($search, $replace, $zlg_call[$row["rng_id"]]);
			$output  = $txt;
			$output = str_replace("<", "[", $output);
			$output = str_replace(">", "]", $output);
			echo $output;
			
			$diff = $zlg_amount[$row["rng_id"]]-$row["rng_brutto"];
			
			echo "(((((((".$diff.")))))))";
			
			q("UPDATE idims_zlg_log SET idims_call = '".mysqli_real_escape_string($dbshop, $txt)."', rng_brutto = ".$row["rng_brutto"].", difference = ".$diff." WHERE invoice_id = ".$row["rng_id"], $dbshop, __FILE__, __LINE__);
			
		
	
			echo "<br />";	
			$counter++;
		
		}

		
		
	}
}

echo "COUNTER: ".$counter."<br />";
*/
/*
$start = time();
// ERROR 9823 from zlg_errors
$errors= array();
$res_error = q("SELECT * FROM idims_zlg_error WHERE error_code = 9823", $dbshop, __FILE__, __LINE__);
echo "Errors: ".mysqli_num_rows($res_error)."<br />";

$postfields = array();
$postfields["API"] = "payments";
$postfields["APIRequest"]="OrderPaymentsGet";

$postfields2 = array();
$postfields2["API"] = "payments";
$postfields2["APIRequest"]="PaymentNotificationHandler";


$accountingcounter = 0;
$counter  = 0;
while($row_errors=mysqli_fetch_assoc($res_error))
{
	if ($counter < 100)
	{
		$errors[]=$row_errors["order_id"];	
		echo "Order: ".$row_errors["order_id"];
		$postfields["orderid"] = $row_errors["order_id"];
		$response = soa2($postfields, __FILE__, __LINE__, "obj");
		if ((string)$response->Ack[0]=="Success")
		{
			$orderdeposit = 0;
			$paymentdeposit = 0;
			for ($i=0; isset($response->orderdata[0]->lastOrderDepositEUR[$i]); $i++)
			{
				echo " OrderDeposit: ".(float)$response->orderdata[0]->lastOrderDepositEUR[$i];
				$orderdeposit = (float)$response->orderdata[0]->lastOrderDepositEUR[$i];
			}
			for ($i=0; isset($response->paymentdata[0]->transaction[0]->transactionID[$i]); $i++)
			{
				echo " TxnID: ".(string)$response->paymentdata[0]->transaction[0]->transactionID[$i];
				echo " Deposit: ".(float)$response->paymentdata[0]->transaction[0]->lastpaymentdeposit[$i];
				$paymentdeposit+=(float)$response->paymentdata[0]->transaction[0]->lastpaymentdeposit[$i];
			}
			
			if ($orderdeposit<0 && $paymentdeposit>0)
			{
				echo " DO ACCOUNNTING";
				$accountingcounter++;
				$postfields2["mode"]="Payment";
				$postfields2["orderid"]=$row_errors["order_id"];
				$postfields2["TransactionID"]=(string)$response->paymentdata[0]->transaction[0]->transactionID[0];

				$response2 = soa2($postfields2, __FILE__, __LINE__, "obj");
				if ((string)$response2->Ack[0]=="Success")
				{
					echo " OK";	
					q("DELETE FROM idims_zlg_error WHERE error_code = 9823 AND order_id = ".$row_errors["order_id"], $dbshop, __FILE__, __LINE__);
				}
			}
					
		}
		echo "<br />";
	}
	$counter++;
}


echo $accountingcounter;
*/

/*

$start = time();
$found_transaction=0;
$invoices = 0;
$matches = 0;

$counter=0;

$postfields = array();
$postfields["API"] = "payments";
$postfields["APIRequest"]="PaymentNotificationLastOrderDepositGet";

$postfields2 = array();
$postfields2["API"] = "payments";
$postfields2["APIRequest"]="PaymentNotificationLastPaymentDepositGet";

$postfields3 = array();
$postfields3["API"] = "payments";
$postfields3["APIRequest"]="PaymentNotificationHandler";

// ERROR 9825 from zlg_errors
$errors= array();
$res_error = q("SELECT * FROM idims_zlg_error WHERE error_code = 9825", $dbshop, __FILE__, __LINE__);
echo "Errors: ".mysqli_num_rows($res_error)."<br />";
while ($row_errors = mysqli_fetch_assoc($res_error))
{
	echo "Invoice: ".$row_errors["invoice_id"]." OrderID: ".$row_errors["order_id"]; 
	$invoices++;
	//FIND EBAY TRANSACTION IDS from shop_orders -> shop_orders_items
	$res_order_items = q("SELECT b.*, a.shop_id, a.ordertype_id FROM shop_orders as a, shop_orders_items as b WHERE b.order_id = ".$row_errors["order_id"]." AND a.id_order=b.order_id AND a.ordertype_id = 1 and a.shop_id IN (3,4,5)", $dbshop, __FILE__, __LINE__);
	while ($row_order_items = mysqli_fetch_assoc($res_order_items))
	{
		if ($row_order_items["foreign_transactionID"]!="")
		{
			echo "<br/>-------------- T-ID: ".$row_order_items["foreign_transactionID"];
			
			//FIND PAYMENTS FOR foreign_transactionID
			$res_PN = q("SELECT * FROM payment_notifications WHERE orderTransactionID ='".$row_order_items["foreign_transactionID"]."' and notification_type = 1", $dbshop, __FILE__, __LINE__);
			while ($row_PN = mysqli_fetch_assoc($res_PN))
			{
				$paymentdeposit = 0;
				$orderdeposit = 0;
				$orderzuordnung = 0;
				echo " <b>FOUND TRANSACTION:</b> ".$row_PN["paymentTransactionID"]." Zugeordenet: <b>".$row_PN["order_id"]."</b>";
				$found_transaction++;
				$orderzuordnung = $row_PN["order_id"];
				
				//GET ORDERDEPOSIT
				$postfields["orderid"] = $row_errors["order_id"];
				$response = soa2($postfields, __FILE__, __LINE__, "obj");
				if ((string)$response->Ack[0]=="Success")
				{
					echo " Orderdeposit: ".$orderdeposit=(float)$response->orderdeposit[0];	
				}
				// GET PAYMENTDEPOSIT
				$postfields2["transactionID"] = $row_PN["paymentTransactionID"];
				$response2 = soa2($postfields2, __FILE__, __LINE__, "obj");
				if ((string)$response2->Ack[0]=="Success")
				{
					echo " Paymentdeposit: ".$paymentdeposit=(float)$response2->payment_deposit[0];	
				}
				
				if ($orderdeposit<0 && $paymentdeposit>0)
				{
					echo " <b>MATCH</b>";
					$OK=false;
					$matches++;
					if ($orderzuordnung!=$row_errors["order_id"])
					{
						//LINKING PAYMENT
						$postfields3["mode"] = "LinkingPayment";
						$postfields3["orderid"] = $row_errors["order_id"];
						$postfields3["TransactionID"] = $row_PN["paymentTransactionID"];
						$response3 = soa2($postfields3, __FILE__, __LINE__, "obj");
						if ((string)$response3->Ack[0]=="Success")
						{
							$OK=true;
						}
					}
					if ($orderzuordnung==$row_errors["order_id"] || $OK)
					{
						//PAYMENT	
						$postfields3["mode"] = "Payment";
						$postfields3["orderid"] = $row_errors["order_id"];
						$postfields3["TransactionID"] = $row_PN["paymentTransactionID"];
						$response3 = soa2($postfields3, __FILE__, __LINE__, "obj");
						
						if ((string)$response3->Ack[0]=="Success")
						{
							echo " OK";
							q("DELETE FROM idims_zlg_error WHERE error_code = 9825 AND order_id = ".$row_errors["order_id"], $dbshop, __FILE__, __LINE__);
							$counter++;
						}
					}
				}
			}
		}
	}
	echo "<br />";
}
echo "INVOIVES: ".$invoices."<br />";
echo "TXNs: ".$found_transaction."<br />";
echo "MATCHES: ".$matches."<br />";

*/

$start = time();

/*
//FIND ORDERS WITH WRONG TOTAL (RNG_TOTAL <-> OrderTotal), because of wrong calculated shipping costs (net*ex_rate*exrate)
// ERROR 9825 from zlg_errors
//GET ZLG_LOGS FÜR SHOP_ID 5
$res_zlg = q("SELECT * FROM idims_zlg_log WHERE shop_id = 5", $dbshop, __FILE__, __LINE__);
while ($row_zlg = mysqli_fetch_assoc($res_zlg))
{
	if ($row_zlg["difference"]!=0)
	{
		$error_invoices[]=$row_zlg["invoice_id"];	
	}
}

//GET ORders
$orders=array();
$res_orders = q("SELECT * FROM shop_orders WHERE invoice_id IN (".implode(",", $error_invoices).")", $dbshop, __FILE__, __LINE__);
while ($row_orders = mysqli_fetch_Assoc($res_orders))
{
	$orders[$row_orders["invoice_id"]] = $row_orders["id_order"];
}


//echo "INVOICES: ".sizeof($error_invoices)."<br />";
//GET RNG_BRUTTO
$res_auf = q("SELECT * FROM idims_auf_status WHERE rng_id IN (".implode(",", $error_invoices).")", $dbshop, __FILE__, __LINE__);
//echo "AufStatus: ".mysqli_num_rows($res_auf)."<br />";

echo "<table>";
//echo "<tr><th>Rechnungsnummer</th><th>Rechnungsdatum</th><th>Summe gezahlt</th><th>Rechnungssumme</th><th>Differenz</th></tr>";


$counter=0;
$dif_sum=0;
while ($row_auf = mysqli_fetch_assoc($res_auf))
{
	
	// GET ORDERDATA FOR SHOP_ID 5
	//if ($row_auf["rng_time"]<1393276784)
	{
		//GET ORDERDATA
		$postfields = array();
		$postfields["API"] ="shop";
		$postfields["APIRequest"] = "OrderDetailGet";
		$postfields["OrderID"] = $orders[$row_auf["rng_id"]];	
		
		$response = soa2($postfields, __FILE__, __LINE__, "obj");
		
		if ((string)$response->Ack[0]=="Success")
		{
			//echo $error[$row_auf["rng_id"]]." ";
			$exrate = (float)$response->Order[0]->OrderItems[0]->Item[0]->OrderItemExchangeRateToEUR[0];	
			$ship_netFC = str_replace(",",".",(string)$response->Order[0]->shippingCostsNetFC[0])*1;
			$ordertotal_EUR = str_replace(",",".", (string)$response->Order[0]->orderTotalGross[0])*1;
		}
		if ((int)$response->Order[0]->shop_id[0]==5)
		{
			
			$counter++;
			$diff = $row_auf["rng_brutto"]-$ordertotal_EUR;
			$dif_sum+=$diff;
			$shipdiff = round(($ship_netFC/$exrate/$exrate*1.19)-($ship_netFC/$exrate*1.19),2);
			//echo "ORDER ID ".$error[$row_auf["rng_id"]]." shop_id <b>".(int)$response->Order[0]->shop_id[0]."</b> OrderTotal: ".$ordertotal_EUR." RNG_TOTAL: ".$row_auf["rng_brutto"]." DIFF: ".$diff." EstimDiff: ".$shipdiff."<br /> ";
			
			echo '<tr>';
			echo '	<td>'.$row_auf["rng_nr"].'</td>';
			echo '	<td>'.$row_auf["rng_vom"].'</td>';
			echo '	<td>'.$ordertotal_EUR.'</td>';
			echo '	<td>'.$row_auf["rng_brutto"].'</td>';
			echo '	<td>'.$diff.'</td>';
			echo '</tr>';
			
		//	echo $row_auf["rng_nr"].";".$row_auf["rng_vom"].";".number_format($ordertotal_EUR,"2",",","").";".number_format($row_auf["rng_brutto"], 2, ",","").";".number_format($diff,2,",","").";<br />";
		}
	}
}
echo '</table>';

echo "Summe Diferenzen: ".$dif_sum."<br />";
echo $counter."<br />";

*/

/*
//GET ERROR 9823
$res_error = q("SELECT * FROM idims_zlg_error WHERE error_code = 9823", $dbshop, __FILE__, __LINE__);
while ($row_error = mysqli_fetch_assoc($res_error))
{
	echo "ORDERID: ".$row_error["order_id"];
	//CHECK FOR COMBINED ORDERS
	$res_order = q("SELECT combined_with from shop_orders WHERE id_order = ".$row_error["order_id"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)>0)
	{
		$row_order=mysqli_fetch_assoc($res_order);
		if ($row_order["combined_with"]>0)
		{
			//GET ALL ORDERS
			$res_orders = q("SELECT * FROM shop_orders WHERE combined_with = ".$row_order["combined_with"], $dbshop, __FILE__, __LINE__);
			while ($row_orders = mysqli_fetch_assoc($res_orders))
			{
			//CALL ORDERADJUSTMENT
				$postfields3 = array();
				$postfields3["API"] = "payments";
				$postfields3["APIRequest"]="PaymentNotificationHandler";
				$postfields3["mode"] = "OrderAdjustment";
				$postfields3["orderid"] = $row_orders["id_order"];
				$postfields3["order_event_id"] = 2;
				$response3 = soa2($postfields3, __FILE__, __LINE__, "obj");
				//if ((string)$response3->Ack[0]=="Success")
				{
					echo " OK";
				}
				
			}
		}
		else
		{
			//CALL ORDERADJUSTMENT
				$postfields3 = array();
				$postfields3["API"] = "payments";
				$postfields3["APIRequest"]="PaymentNotificationHandler";
				$postfields3["mode"] = "OrderAdjustment";
				$postfields3["orderid"] = $row_error["order_id"];
				$postfields3["order_event_id"] = 2;
				$response3 = soa2($postfields3, __FILE__, __LINE__, "obj");
				//if ((string)$response3->Ack[0]=="Success")
				{
					echo " OK";
				}
		}
	}
	echo "<br />";
}
*/

//ZLG_LOG difference correction
/*
$counter = 0;
$res = q("SELECT * FROM idims_zlg_log", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	if ($row["amount"] != $row["rng_brutto"] && $row["difference"]==0)
	{
		$diff = $row["rng_brutto"] - $row["amount"];
		q("UPDATE idims_zlg_log SET difference = ".$diff." WHERE id_log = ".$row["id_log"], $dbshop, __FILE__, __LINE__);
		echo "FEHLER ".$row["invoice_id"]."<br />";
		$counter++;
	}
}
*/

/*
$counter = 0;
$res_error = q("SELECT * FROM cms_errors WHERE error_id = 9844", $dbweb, __FILE__, __LINE__);
while ( $row_error = mysqli_fetch_assoc($res_error))
{
$userid_neu = 0;
$userid_alt = 0;
	
	// GET WRONG USER_ID 
	echo "ALT:".$userid_alt = str_replace("CMS_USER_ID:","",$row_error["text"])*1;
	
	$mail = "";
	//GET MAIL FROM SHOP_ORDERS 
	$res_order = q("SELECT * FROM shop_orders WHERE customer_id = ".$userid_alt, $dbshop, __FILE__, __LINE__);
	while ($row_orders = mysqli_fetch_assoc($res_order))
	{
		if ($mail =="")
		{
			if ( $row_orders["usermail"] != "" )
			{
				$mail = $row_orders["usermail"];
			}
		}
	}
	
	// GET correct userid
	if ($mail != "")
	{
		$res_user = q("SELECT * FROM cms_users WHERE usermail ='".$mail."'", $dbweb, __FILE__, __LINE__);
		while ($row_user = mysqli_fetch_assoc($res_user))
		{
			$userid_neu = $row_user["id_user"];
		}
	}
	
	echo "NEU:".$userid_neu;
	echo "<br />";
		


//$userid_neu = 98624;
//$userid_alt = 3167715;


	if ( $userid_neu != 0 && $userid_alt != 0 )
	{
		//KORREKTUR shop_orders
		$res = q("update shop_orders SET customer_id = ".$userid_neu." WHERE customer_id = ".$userid_alt, $dbshop, __FILE__, __LINE__);
		echo "shop_orders ".mysqli_affected_rows($dbshop)."<br />";
		
		//KORREKTUR shop_bill_adr
		$res = q("update shop_bill_adr SET user_id = ".$userid_neu." WHERE user_id = ".$userid_alt, $dbshop, __FILE__, __LINE__);
		echo "shop_bill_adr ".mysqli_affected_rows($dbshop)."<br />";
		
		//KORREKTUR shop_carfleet
		$res = q("update shop_carfleet SET user_id = ".$userid_neu." WHERE user_id = ".$userid_alt, $dbshop, __FILE__, __LINE__);
		echo "shop_carfleet ".mysqli_affected_rows($dbshop)."<br />";
		
		//KORREKTUR payment_notifications
		$res = q("update payment_notifications SET user_id = ".$userid_neu." WHERE user_id = ".$userid_alt, $dbshop, __FILE__, __LINE__);
		echo "payment_notifications ".mysqli_affected_rows($dbshop)."<br />";
		
		
		//KORREKTUR cms_users_sites
		$res = q("update cms_users_sites SET user_id = ".$userid_neu." WHERE user_id = ".$userid_alt, $dbweb, __FILE__, __LINE__);
		echo "cms_users_sites ".mysqli_affected_rows($dbweb)."<br />";
		
		//KORREKTUR crm_customer_accounts3
		$res = q("update crm_customer_accounts3 SET cms_user_id = ".$userid_neu." WHERE cms_user_id = ".$userid_alt, $dbweb, __FILE__, __LINE__);
		echo "crm_customer_accounts3 ".mysqli_affected_rows($dbweb)."<br />";
		
		//KORREKTUR crm_numbers3
		$res = q("update crm_numbers3 SET cms_user_id = ".$userid_neu." WHERE cms_user_id = ".$userid_alt, $dbweb, __FILE__, __LINE__);
		echo "crm_numbers3 ".mysqli_affected_rows($dbweb)."<br />";
		
		//KORREKTUR crm_conversations
		$res = q("update crm_conversations SET user_id = ".$userid_neu." WHERE user_id = ".$userid_alt, $dbweb, __FILE__, __LINE__);
		echo "crm_conversations ".mysqli_affected_rows($dbweb)."<br />";
	
	$counter++;
	echo "<br />";
	}
}

echo "Anzahl: ".$counter;
*/
/*
function checkEncoding ( $string, $string_encoding )
 {
     $fs = $string_encoding;

     $ts = 'UTF-8';

     return $string === mb_convert_encoding ( mb_convert_encoding ( $string, $fs, $ts ), $ts, $fs );
 }


//echo mb_internal_encoding( 'ISO-8859-1');
//echo "<br />";
//echo checkEncoding ('日本語' , 'ISO-8859-1'); // LATIN-1
echo checkEncoding ('Nico' , 'ISO-8859-1'); // LATIN-1
*/


//SHOP ORDERS CREDIT NETTO FIX
/*
$res = q("SELECT * FROM shop_orders_credits", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	$net = round($row["brutto"]/1.19, 2);
	
	q("UPDATE shop_orders_credits SET netto = ".$net." WHERE auf_id = ".$row["auf_id"], $dbshop, __FILE__, __LINE__);
}
*/




/*
$counter = 0;
$res = q("SELECT * FROM payment_notification_messages WHERE processed = 0", $dbshop, __FILE__, __LINE__);
echo mysqli_num_rows($res)."<br />";
while ($row = mysqli_fetch_assoc($res))
{
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement("<message><![CDATA[".$row["message"]."]]></message>");
	}
	catch(Exception $e)
	{
		echo "FEHER ".$row["id"]." ".$e."<br />";
		$counter++;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
}

echo "COUNT: ".$counter;
*/



/*
include_once("functions/shop_OrderSumGet.php");

$orders_sum = 0;
$payment_sum = 0;
$missing_sum = 0;


//$res_order = q("SELECT * FROM shop_orders WHERE shop_id IN (2) AND payments_type_id = 3 AND invoice_date > ".strtotime("01.05.2014")." AND invoice_date < ".strtotime("01.06.2014"), $dbshop, __FILE__, __LINE__);
$res_order = q("SELECT * FROM shop_orders WHERE shop_id IN (2,4) AND payments_type_id = 3 AND Payments_TransactionState = 'Completed' AND Payments_TransactionStateDate > ".strtotime("01.06.2014")." AND Payments_TransactionStateDate < ".strtotime("01.07.2014")." ORDER BY firstmod", $dbshop, __FILE__, __LINE__);
echo mysqli_num_rows( $res_order )."<br />";


while ($row_orders = mysqli_fetch_assoc( $res_order) )
{
	//$sum["OrderTotal"] = 0;
	echo "ORDER:".$row_orders["id_order"];
	echo " Date: ".date("d.m.Y", $row_orders["firstmod"]);
	$sum = OrderSumGet($row_orders["id_order"]);
	$orders_sum += $sum["OrderTotal"]["GrossEUR"];
	echo " SUM: ".$sum["OrderTotal"]["GrossEUR"];

			$postfields3 = array();
			$postfields3["API"] = "payments";
			$postfields3["APIRequest"]="OrderPaymentsGet";
			$postfields3["orderid"] = $row_orders["id_order"];
			$postfields3["order_event_id"] = 2;
			$response3 = soa2($postfields3, __FILE__, __LINE__, "obj");
			
		if ((string)$response3->Ack[0] == "Success" )
		{
			if ( (float)$response3->paymentdata[0]->paymentsAccountingsEUR[0] == 0)
			{
				echo "NOTPAYED<br />";
				$missing_sum += $sum["OrderTotal"]["GrossEUR"];
			}
			else
			{
				echo "PAYED: ".(float)$response3->paymentdata[0]->paymentsAccountingsEUR[0]."<br />";
			}
			$payment_sum += (float)$response3->paymentdata[0]->paymentsAccountingsEUR[0];
		}
		else
		{
			echo "ERROR<br />";
		}
}
			
echo "ORDERSSUM: ".$orders_sum."<br />";
echo "PAYMENTSUM: ".$payment_sum."<br />";
echo "MISSING: ".$missing_sum."<br />";
*/

/*

$res = q("SELECT a.*, b.invoice_date FROM idims_zlg_error as a, shop_orders as b WHERE b.invoice_date < 1398981601 AND a.order_id = b.id_order AND NOT error_code = 9839 ORDER BY id_error", $dbshop, __FILE__, __LINE__);
echo "ERRORS: ".mysqli_num_rows($res)."<br /";
$res = q("SELECT a.*, b.invoice_date FROM idims_zlg_error as a, shop_orders as b WHERE b.invoice_date < 1398981601 AND a.order_id = b.id_order AND NOT error_code = 9839 ORDER BY id_error LIMIT 100", $dbshop, __FILE__, __LINE__);

while ( $row = mysqli_fetch_assoc( $res ) )
{
	echo $row["order_id"]." ".date("d.m.Y", $row["invoice_date"])."<br />";

			$postfields3 = array();
			$postfields3["API"] = "payments";
			$postfields3["APIRequest"]="ZLGPaymentMessageCreateManual";
			$postfields3["error_id"] = $row["id_error"];
			$postfields3["expired"] = 1;
			$response3 = soa2($postfields3, __FILE__, __LINE__, "obj");
	
			if ( (string)$response3->Ack[0]=="Success")
			{
				echo "DELETE ERROR <br />";
				//DELETE ERROR
				q("DELETE FROM idims_zlg_error WHERE id_error = ".$row["id_error"], $dbshop, __FILE__, __LINE__);
			}

}
*/

/*
include_once("functions/shop_OrderSumGet_test.php");
//$array=OrderSumGet(1832598);
//$array=OrderSumGet(1832624, array("calculation_base" => "net"));
$array=OrderSumGet(1842517);
//print_r($array);
//echo $array."HALLO";
//exit;
//$array = OrderSumGet(1814792);
function print_array($array, &$level)
{
	foreach ($array as $index => $data)
	{
		
		if (is_array($data))
		{
			
			echo "<b>".$index."</b><br />";
			for ($i=0; $i<$level; $i++)
			{
				echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			}

			$level++;
			print_array($data, $level);	
		}
		else
		{
			for ($i=0; $i<$level; $i++)
			{
				echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			echo $index." ".$data."<br />";
		}
		
	}
	$level--;
}

$level=0;

//print_array($array["OrderTotal"],$level);
foreach($array["OrderPositions"] as $position => $data)
{
	echo "<b>POSITION ".$position."</b><br />";
	foreach ($data as $index => $value)
	{
		echo $index." ".$value."<br />";
	}
}
echo "OrderCredits".$array["OrderCredits"]["GrossEUR"]."<br />";
echo "ShippingCosts".$array["ShippingCosts"]["GrossEUR"]."<br />";
echo "PositionsTotal".$array["OrderPositionsTotal"]["GrossEUR"]."<br />";
echo "TotalWITHCredit".$array["OrderTotalWithCredit"]["GrossEUR"]."<br />";
echo "OrdertotalEUR".$array["OrderTotal"]["GrossEUR"];

print_r($array['OrderCredits']);

//print_r($array);

			
	*/		
			
			
			
			
			
	
/*						  
$client = new SoapClient( 'https://www.paypal.com/wsdl/PayPalSvc.wsdl',
                           array( 'soap_version' => SOAP_1_1 ));

$cred = array( 'Username' => 'nputzi_1357220940_biz_api1.mapco.de',
               'Password' => '1357220955',
               'Signature' => 'AFcWxV21C7fd0v3bYYYRCpSSRl31ARvCmT4HK0jBsx4MSoIadIdvMIuo' );

$Credentials = new stdClass();
$Credentials->Credentials = new SoapVar( $cred, SOAP_ENC_OBJECT, 'Credentials' );

$headers = new SoapVar( $Credentials,
                        SOAP_ENC_OBJECT,
                        'CustomSecurityHeaderType',
                        'urn:ebay:apis:eBLBaseComponents' );

$client->__setSoapHeaders( new SoapHeader( 'urn:ebay:api:PayPalAPI',
                                           'RequesterCredentials',
                                           $headers ));

$args = array( 'Version' => '71.0',
               'ReturnAllCurrencies' => '1' );

$GetBalanceRequest = new stdClass();
$GetBalanceRequest->GetBalanceRequest = new SoapVar( $args,
                                                     SOAP_ENC_OBJECT,
                                                     'GetBalanceRequestType',
                                                     'urn:ebay:api:PayPalAPI' );

$params = new SoapVar( $GetBalanceRequest, SOAP_ENC_OBJECT, 'GetBalanceRequest' );

$result = $client->GetBalance( $params );

echo 'Balance is: ', $result->Balance->_, $result->Balance->currencyID;
*/
/*
include_once("functions/shop_get_prices.php");
$_POST["user_id"] = 117958;
$prices = get_prices(18199,1, $_POST["user_id"]);

echo "NETTO".$prices["net"]."BRUTTO:".$prices["net"]*1.19;
*/
/*
//GET ERROR 9842
echo "<table>";

$cunter = 0;
$res_error = q("SELECT a.*, c.auf_brutto FROM idims_zlg_error as a, shop_orders as b, idims_auf_status as c WHERE error_code = 9842 AND a.order_id = b.id_order AND b.shop_id IN (2,4,6) AND c.rng_id = a.invoice_id", $dbshop, __FILE__, __LINE__);
while ( $row_error = mysqli_fetch_assoc ($res_error))
{
	echo "<tr>";
	echo "<td>".$row_error["order_id"]."</td>"; 
	echo "<td>".$row_error["invoice_id"]."</td>"; 
	echo "<td>".number_format($row_error["auf_brutto"], 2,",","")."</td>"; 
	
	$postfield = array();
	$postfield["API"] = "payments";
	$postfield["APIRequest"] = "PaymentNotificationLastOrderTotalGet";
	$postfield["orderid"] = $row_error["order_id"];
	
	$response = soa2($postfield, __FILE__, __LINE__);
	
	if ((string)$response->Ack[0]=="Success")
	{
		echo "<td>".number_format((string)$response->ordertotalEUR[0], 2, ",","")."</td>";
	}
	else
	{
		echo "<td>ERROR</td>";
	}
	echo "</tr>";
	$counter ++;
	
//	if ($counter == 1)
	{
		$postfield = array();
		$postfield["API"] = "shop";
		$postfield["APIRequest"] = "OrderNetPriceCorrection";
		$postfield["orderid"] = $row_error["order_id"];

		 $response = soa2($postfield, __FILE__, __LINE__);
		 
		if ((string)$response->Ack[0]=="Success")
		{
		 

			$postfield = array();
			$postfield["API"] = "payments";
			$postfield["APIRequest"] = "ZLGPaymentMessageCreateManual";
			$postfield["error_id"] = $row_error["id_error"];
			$postfield["betrag"] = $row_error["auf_brutto"];
			
			 soa2($postfield, __FILE__, __LINE__);
			if ((string)$response->Ack[0]=="Success")
			{
				
				q("DELETE FROM idims_zlg_error WHERE id_error = ".$row_error["id_error"], $dbshop, __FILE__, __LINE__);	
			}
		}
	}
}
echo "</table>";
echo "COUNTER".$counter;

*/

/*
//IMPORT SHIPPING LABELS
//GET ORDER EVENTS 12
$events = array();
$res_events = q("SELECT order_id, firstmod, firstmod_user FROM shop_orders_events WHERE eventtype_id = 12", $dbshop, __FILE__, __LINE__);
echo "events".mysqli_num_rows( $res_events)."<br />";
while ( $row_events = mysqli_fetch_assoc ( $res_events) )
{
	
	$events[$row_events['order_id']] = $row_events;
}
echo "EVENTSARRAY:".sizeof($events)."<br />";

unset ($row_events);

//GET SHIPPING ENTRIES

$labels = array();
$res_labels = q("SELECT * FROM shop_shipping_labels", $dbshop, __FILE__, __LINE__);
while ( $row_labels = mysqli_fetch_assoc ( $res_labels))
{
	$labels[$row_labels["shipping_number"]] = $row_labels['order_id'];
}

$counter_matches = 0;
$res_orders = q("SELECT * FROM shop_orders WHERE shipping_type_id IN (1,2,5,7,15,16,19) AND NOT shipping_number = '' AND NOT shipping_label_file_id = 0", $dbshop, __FILE__, __LINE__);
echo mysqli_num_rows ( $res_orders );
while ( $row_orders = mysqli_fetch_assoc( $res_orders) )
{
	if ( !isset ( $labels[$row_orders['shipping_number']]))
	{
		$data = array();
		if (isset( $events[$row_orders["id_order"]])	)
		{	
			$data['firstmod'] = $events[$row_orders["id_order"]]["firstmod"];
			$data['firstmod_user'] = $events[$row_orders["id_order"]]["firstmod_user"];
			$counter_matches ++;
		}
		else
		{
			$data['firstmod'] = $row_orders["status_date"];
			$data['firstmod_user'] = 10;
		}
		$data['order_id'] = $row_orders["id_order"];
		$data['type'] = 1;
		$data['shipping_number'] = $row_orders["shipping_number"];
		$data['shipping_label_file_id'] = $row_orders["shipping_label_file_id"];
	
		q_insert("shop_shipping_labels", $data, $dbshop, __FILE__, __LINE__);
//		$counter_insert ++;
	}
}
*/

/*
//IMPORT SHIPPING LABELS
//GET ORDER EVENTS 14
$events = array();
$res_events = q("SELECT order_id, firstmod, firstmod_user FROM shop_orders_events WHERE eventtype_id = 14", $dbshop, __FILE__, __LINE__);
echo "events".mysqli_num_rows( $res_events)."<br />";
while ( $row_events = mysqli_fetch_assoc ( $res_events) )
{
	
	$events[$row_events['order_id']] = $row_events;
}
echo "EVENTSARRAY:".sizeof($events)."<br />";

unset ($row_events);

//GET SHIPPING ENTRIES

$labels = array();
$res_labels = q("SELECT * FROM shop_shipping_labels", $dbshop, __FILE__, __LINE__);
while ( $row_labels = mysqli_fetch_assoc ( $res_labels))
{
	$labels[$row_labels["shipping_number"]] = $row_labels['order_id'];
}

$counter_matches = 0;
$known_counter = 0;
$res_orders = q("SELECT * FROM shop_orders WHERE NOT RetourLabelID = '' AND NOT RetourLabelID = '0'", $dbshop, __FILE__, __LINE__);
echo mysqli_num_rows ( $res_orders );

while ( $row_orders = mysqli_fetch_assoc( $res_orders) )
{
	if ( !isset ( $labels[$row_orders['RetourLabelID']]))
	{
		$data = array();
		if (isset( $events[$row_orders["id_order"]])	)
		{	
			$data['firstmod'] = $events[$row_orders["id_order"]]["firstmod"];
			$data['firstmod_user'] = $events[$row_orders["id_order"]]["firstmod_user"];
			$counter_matches ++;
		}
		else
		{
			$data['firstmod'] = $row_orders["RetourLabelTimestamp"];
			$data['firstmod_user'] = 10;
		}
		$data['order_id'] = $row_orders["id_order"];
		$data['type'] = 2;
		$data['shipping_number'] = $row_orders["RetourLabelID"];
		$data['shipping_label_file_id'] = 0;
	
		q_insert("shop_shipping_labels", $data, $dbshop, __FILE__, __LINE__);
		$counter_insert ++;
	}
	else
	{
		$known_counter ++;	
	}
}

echo "MATCHES: ".$counter_matches;
echo "KNOWN: ".$known_counter;
*/
/*
//GET LABEL type 2
$labels = array();
$res_labels = q("SELECT * FROM shop_shipping_labels WHERE type = 2", $dbshop, __FILE__, __LINE__);
while ($row_labels = mysqli_fetch_assoc($res_labels) )
{
	$labels[$row_labels["order_id"]][] = $row_labels;
		
}
$returns = array();

$res_return = q("SELECT * from shop_returns2", $dbshop, __FILE__, __LINE__);
while( $row_returns = mysqli_fetch_assoc($res_return))
{
	$returns[$row_returns["order_id"]][] = $row_returns;
}

foreach ( $labels as $orderid => $_label)
{
	foreach ($_label as $index => $label)
	{
		if (isset ($returns[$orderid]))
		{
			$returnlabel[$label['id_shipping_label']] = $returns[$orderid][0]["id_return"];
			
			echo "LABELID ".$label['id_shipping_label']." ".$returns[$orderid][0]["id_return"]."<br />";
			$data = array();
			$data["return_id"] = $returns[$orderid][0]["id_return"];
			q_update ("shop_shipping_labels", $data, "WHERE id_shipping_label = ".$label['id_shipping_label'], $dbshop, __FILE__, __LINE__);
			
		}
	}
}
*-/
echo "fertig";
echo "LAUFZEIT: ".(time()-$start);

?>	

