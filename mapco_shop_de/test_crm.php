<?php
	include("config.php");
//	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script type="text/javascript">
</script>

<?php



	if (isset($_POST["send_message"]))
	{
		/*$response= post(PATH."soa/", array("API" => "shop", "Action" => "OrderAdd", "mode" =>"new",
						"shop_id" =>1, 
						"status_id" =>3, 
						"Currency_Code" => "EUR", 
						"customer_id" =>28625, 
						"usermail" => "nputzing",
						"bill_firstname" => "N.", 
						"bill_lastname" => "putzi", 
						"bill_zip" => "14822", 
						"bill_city" => "brück", 
						"bill_street" => "weg", 
						"bill_number" => "2", 
						"bill_country_code" => "DE", 
						"shipping_costs" =>0, 
						"shipping_type_id" =>1, 
						"bill_adr_id" =>12333, 
						"shipping_net" =>0)); */
			//$response=post(PATH."soa/", array("API" => "shop", "Action" => "OrderItemAdd", "mode" =>"new", "item_id" => 13377, "netto" => 1.00, "price" => 1.19, "Currency_Code" => "GBP", "order_id" => 1749450, "amount" => 1, "exchange_rate_to_EUR" => 1));
	//	$response=post(PATH."soa/", array("API" => "crm", "Action" => "import_ebayOrderData2", "mode" =>"update", "EbayOrderID" => 192699));
/*
	$data = array('user'=>array('name'=>'Bob Smith',
                            'alter'=>47,
                            'geschlecht'=>'M',
                            'geb'=>'5/12/1956'),
              'hobbies'=>array('golf', 'opera', 'poker', 'rap'),
              'kinder'=>array('bobby'=>array('alter'=>12,
                                               'geschlecht'=>'M'),
                                'sally'=>array('alter'=>8,
                                               'geschlecht'=>'F')),
              'CEO');
*/
/*
session_start();

$_SESSION["id_user"]=28625;
$_SESSION["id_userrole"]=1;
*/
	$postfields=array();

//	$postfields["API"]="payments";
//	$postfields["API"]="crm";
$postfields["API"]="idims";
//	$postfields["APIRequest"]="EbayOrderImport";
//	$postfields["APIRequest"]="OrderPaymentsGet2";
//	$postfields["APIRequest"]="PaymentNotificationHandler";
//	$postfields["APIRequest"]="PaymentsNotificationSet_PayPal";
//	$postfields["APIRequest"]="PaymentNotificationSet_Manual";
	$postfields["APIRequest"]="PaymentSend";
//$postfields["APIRequest"]="PaymentNotificationSet_Paygenic";
//	$postfields["mode"]="BankTransfer_SendMoney";
//	$postfields["APIRequest"]="PaymentNotificationSet_BankTransfer";
//	$postfields["mode"]="BankTransfer_send";
//	$postfields["mode"]="CreditCard_refund";
//	$postfields["mode"]="BankTransfer";
//	$postfields["payment_total"]=500;
//	$postfields["accounting_date"]=1386861111;
	
//$postfields["id"]=6217;
//$postfields["PNM_id"]=6260;
//	$postfields["EbayOrderID"]=217694;
//	$postfields["accounting_date"]=1.99;
//	$postfields["state"]="from promotion";
//	$postfields["ParentTransactionID"]="43193";
	
	$postfields["orderid"]=1792809;
//	$postfields["order_event_id"]=883179;
//	$postfields["orderid_from"]=1756405;
//	$postfields["userid"]=28625;	
//	$postfields["return_id"]=13;
//	$postfields["ParentTransactionID"]='VVVVVVVVV';
//$postfields["payment_total"]=424.40;
//	$postfields["CardType"]=2;
//	$postfields["sent_total"]=5.90;

/*
	$postfields["payment_type_id"]=2;
	$postfields["payment_total"]=13.50;
	$postfields["payment_mode"]="refund";
	$postfields["ParentTransactionID"]="2022";
*/	
	//echo $postfields=http_build_query($postfields);
	
//	$postfields["API"]="payments";
	//$postfields["APIRequest"]="PaymentsNotificationSet_PayPal";
//	$postfields["id"]=7452;


//print_r($_SESSION);
//print_r($postfields);
$response=post(PATH."soa2/", $postfields);
//$response = soa2($postfields);
echo '<p><textarea name="response" cols="100" rows="40">'.print_r($response, true).'</textarea></p>';
	//	echo '<p><textarea name="response" cols="40" rows="20">'.$response.'</textarea></p>';
		//echo $response;
	}


	echo '<form action="'.PATH.'test_crm.php" method="POST">';
//	echo 'AccountID: <input type="text" name="accountid" size="2" value="1" /><br />';
//	echo 'Orderid: <input type="text" name="transactionid" size="14" value="1442" /><br />';

//	echo 'ItemID: <input type="text" name="itemid" size="14" value="1010273128001" /><br />';
//	echo 'KäuferID: <input type="text" name="buyerid" size="14" value="kroesus176" /><br />';
//	echo 'Subject: <input type="text" name="subject" size="30" value="Fahrzeugdatenanfrage" /><br />';
//	echo 'Nachricht: <textarea name="message" cols="40" rows="20">fin agf</textarea><br />';
	echo '<input type="submit" name="send_message" value="Nachricht senden" />';
	echo '</form>';
//	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>