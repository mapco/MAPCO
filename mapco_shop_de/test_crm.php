<?php
	include("config.php");
//	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script type="text/javascript">
</script>

<?php



	if (isset($_POST["send_message"]))
	{
	$postfields=array();

//	$postfields["API"]="cms";
	$postfields["API"]="payments";
//	$postfields["API"]="shop";
//	$postfields["API"]="idims";

//	$postfields["APIRequest"]="OrderSend_test2";
//	$postfields["APIRequest"]="ZLGPaymentMessageCreate_new";
	$postfields["APIRequest"]="PaymentNotificationHandler";
//	$postfields["APIRequest"]="PaymentNotificationHandler";
//	$postfields["APIRequest"]="PaymentsNotificationSet_PayPal";
//	$postfields["APIRequest"]="PaymentNotificationSet_Manual";
//	$postfields["mode"]="single";
//	$postfields["APIRequest"]="crm_orders_list";
	$postfields["orderid"]=1809407;
//	$postfields["FILTER_SearchFor"]=8;
	$postfields["mode"]="PaymentWriteBack";
//	$postfields["mode"]="OrderAdjustment";
//	$postfields["payment_total"]=500;
//	$postfields["accounting_date"]=1386861111;
//	$postfields["mode"]="single";
//$postfields["id"]=28126;
//$postfields["EbayOrderID"]=223582;
//	$postfields["id_order"]=1796557;
//		$postfields["payment_type_id"]=4;
//		$postfields["response_from"]=1391468400;
//		$postfields["response_to"]=1391554800;
//		$postfields["shop_id"]=3;
//		$postfields["flag_as_transfered"]=1;
//		$postfields["not_transfered"]=1;
//	$postfields["accounting_date"]=time();
//	$postfields["state"]="from promotion";
//	$postfields["ParentTransactionID"]="43193";
//	$postfields["unsend"]=1;
//	$postfields["returnid"]=100;
	$postfields["order_event_id"]=1;
//	$postfields["TransactionID"]="86F98254W3531884Y";
//	$postfields["userid"]=28625;	
//	$postfields["return_id"]=13;
//	$postfields["ParentTransactionID"]='VVVVVVVVV';
//$postfields["payment_total"]=10;
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
echo '<p><textarea name="response" cols="100" rows="30">'.print_r($response, true).'</textarea></p>';
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