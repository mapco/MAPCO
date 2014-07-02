<?php
//include("../../mapco_shop_de/config.php");
include("CallerService.php");
include("../../mapco_shop_de/functions/shop_mail_order.php");


$transactionIDs=array();
$errmsg="";

if (isset($_POST["checkall"]) && $_POST["checkall"]=="Pending") 
	{
	$res=q("SELECT PayPal_TransactionID, id_order, PayPal_TransactionState FROM shop_orders WHERE PayPal_TransactionState = 'Pending';",  $dbshop, __FILE__, __LINE__);
	$i=0;
	while($row=mysql_fetch_array($res))
		{
			$transactionIDs[$i]=$row["PayPal_TransactionID"];
			$transactionstate[$i]=$row["PayPal_TransactionState"];
			$order_ids[$i]=$row["id_order"];
			$i++;
		}
	}

if (isset($_POST["checksingle"]) && $_POST["checksingle"]!=="") 
	{
		$transactionIDs[0]=$_POST["checksingle"];
		$res=q("SELECT id_order, PayPal_TransactionState FROM shop_orders WHERE PayPal_TransactionID = '".$transactionIDs[0]."';",  $dbshop, __FILE__, __LINE__);
		$row=mysql_fetch_array($res);
		$transactionstate[0]=$row["PayPal_TransactionState"];
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
			echo 'PayPalCheckStatus: Bestellung (Order ID: '.$order_ids[$i].'): PayPal - Paymentstatus gesetzt auf: COMPLETED <br />/n';
			}
		else {echo 'PayPalCheckStatus: Bestellung (Order ID: '.$order_ids[$i].'): PayPal - Paymentstatus gesetzt auf: '.$field["PAYMENTSTATUS"].' <br />/n';}

		$res=q("UPDATE shop_orders SET PayPal_TransactionState = '".mysql_real_escape_string($field["PAYMENTSTATUS"], $dbshop)."', PayPal_PendingReason = '".mysql_real_escape_string($pendingreason, $dbshop)."', PayPalTransactionStateDate = ".time()." WHERE PayPal_TransactionID = '".$transactionIDs[$i]."';", $dbshop, __FILE__, __LINE__);
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

if ($errmsg!="") echo $errmsg;
?>