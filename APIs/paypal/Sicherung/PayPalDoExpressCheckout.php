<?php

require_once 'CallerService.php';

$nvpstr ='&TOKEN='.$_SESSION["paypaltoken"];
$nvpstr.='&PAYERID='.$_SESSION["bill_PayPalPayerID"];
$nvpstr.='&PAYMENTREQUEST_0_CURRENCYCODE=EUR';
$nvpstr.='&PAYMENTREQUEST_0_PAYMENTACTION=sale';
$nvpstr.='&PAYMENTREQUEST_0_AMT='.$_SESSION["PAYMENTREQUEST_0_AMT"];
$nvpstr.='&PAYMENTREQUEST_0_SHIPPINGAMT='.$_SESSION["PAYMENTREQUEST_0_SHIPPINGAMT"];
$nvpstr.='&PAYMENTREQUEST_0_TAXAMT='.$_SESSION["PAYMENTREQUEST_0_TAXAMT"];
$nvpstr.='&PAYMENTREQUEST_0_ITEMAMT='.$_SESSION["PAYMENTREQUEST_0_ITEMAMT"];
$nvpstr.='&PAYMENTREQUEST_0_HANDLINGAMT=';

$field=hash_call("DoExpressCheckoutPayment",$nvpstr);

echo '<response>';
echo '	<state><![CDATA['.$field["ACK"].']]></state>';
if ($field["ACK"]=="Success") {
	$_SESSION["PayPalTransactionID"]=$field["PAYMENTINFO_0_TRANSACTIONID"];
	$_SESSION["PayPalPaymentStatus"]=$field["PAYMENTINFO_0_PAYMENTSTATUS"];
	$_SESSION["PayPalCheckout"]="CheckOut";
	if ($field["PAYMENTINFO_0_PAYMENTSTATUS"]=="Pending") $_SESSION["PayPalPendingReason"]=$field["PAYMENTINFO_0_PENDINGREASON"]; else $_SESSION["PayPalPendingReason"]="";

}
else { 
	echo '<statemsg><![CDATA[';
	while (list($key, $val) = each ($field)) {echo $key.": ".$val." | ";}
	echo ']]></statemsg>';
}	
echo '</response>';

?>
