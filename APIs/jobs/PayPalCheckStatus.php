<?php
//include("../../mapco_shop_de/config.php");
include("../../APIs/payments/PayPalCallerService.php");
include("../../mapco_shop_de/functions/shop_mail_order.php");


$transactionIDs=array();
$errmsg="";

if (isset($_POST["checkall"]) && $_POST["checkall"]=="Pending") 
	{
	$res=q("SELECT Payments_TransactionID, id_order, Payments_TransactionState FROM shop_orders WHERE Payments_TransactionState = 'Pending' AND shop_id = 2;",  $dbshop, __FILE__, __LINE__);
	$i=0;
	while($row=mysqli_fetch_array($res))
		{
			$transactionIDs[$i]=$row["Payments_TransactionID"];
			$transactionstate[$i]=$row["Payments_TransactionState"];
			$order_ids[$i]=$row["id_order"];
			$i++;
		}
	}

if (isset($_POST["checksingle"]) && $_POST["checksingle"]!=="") 
	{
		$transactionIDs[0]=$_POST["checksingle"];
		$res=q("SELECT id_order, Payments_TransactionState FROM shop_orders WHERE Payments_TransactionID = '".$transactionIDs[0]."';",  $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($res);
		$transactionstate[0]=$row["Payments_TransactionState"];
		$order_ids[0]=$row["id_order"];
	}

//_______________________________________________________

for ($i=0; $i<sizeof($transactionIDs); $i++)
{
	$nvpstr="&TRANSACTIONID=".urlencode($transactionIDs[$i]);
	$field=hash_call("GetTransactionDetails",$nvpstr);
	
	if ($field["ACK"]=="Success")
	{
		if ($field["PAYMENTSTATUS"]=="Pending")
		{
			$pendingreason=$field["PENDINGREASON"];
		}
		else
		{
			$pendingreason="";
		}
		//WENN SICH DER Paymentstatus auf Completed ändert, dann Mail an Borkheide
		if ($transactionstate[$i]!="Completed" && $field["PAYMENTSTATUS"]=="Completed") {
			//MAIL
			mail_order($order_ids[$i]);
			echo 'PayPalCheckStatus: Bestellung (Order ID: '.$order_ids[$i].'): PayPal - Paymentstatus gesetzt auf: COMPLETED <br />';
			}
		else {echo 'PayPalCheckStatus: Bestellung (Order ID: '.$order_ids[$i].'): PayPal - Paymentstatus gesetzt auf: '.$field["PAYMENTSTATUS"].' <br />';}

		$res=q("UPDATE shop_orders SET Payments_TransactionState = '".mysqli_real_escape_string($dbshop, $field["PAYMENTSTATUS"])."', PayPal_PendingReason = '".mysqli_real_escape_string($dbshop, $pendingreason)."', Payments_TransactionStateDate = ".time()." WHERE Payments_TransactionID = '".$transactionIDs[$i]."';", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		if ($errmsg=="") $errmsg.= '<ServiceResponse>'."\n";
		$errmsg.= '	<Error>'."\n";
		$errmsg.= '		<Code>'.$field["L_ERRORCODE0"].'</Code>'."\n";
		$errmsg.= '		<shortMsg>'.$field["L_SHORTMESSAGE0"].'</shortMsg>'."\n";
		$errmsg.= '		<longMsg>'.$field["L_LONGMESSAGE0"].'</longMsg>'."\n";
		$errmsg.= '		<TransactionID>'.$transactionIDs[$i].'</TransactionID>'."\n";
		$errmsg.= '	</Error>'."\n";
		
	}
	
	
}


if ($errmsg!="") $errmsg.= '</ServiceResponse>'."\n";

if ($errmsg!="") echo $errmsg."\n";

//PAYPAL CHECK AP
/*temporär*/
$response="";
echo 'AUSGABE PAYPAL AP:';
$varField["checkall"]="Pending";
$response.=  post("http://www.ihr-autopartner.de/PayPalCheckState/PayPalCheckStatus_tmp.php", $varField );

echo $respopnse."\n";

?>