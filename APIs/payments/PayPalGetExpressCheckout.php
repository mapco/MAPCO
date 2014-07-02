<?php

require_once 'PayPalCallerService.php';

$token=$_SESSION["paypaltoken"];

$nvpstr="&TOKEN=".urlencode($token);

$field=hash_call("GetExpressCheckoutDetails",$nvpstr);

//RETURN__________________________________________________________________________________________________________
echo '<response>';
echo '	<state><![CDATA['.$field["ACK"].']]></state>';
if ($field["ACK"]=="Success") {
	$name=$field["PAYMENTREQUEST_0_SHIPTONAME"];
	$street=$field["PAYMENTREQUEST_0_SHIPTOSTREET"];
	$_SESSION["bill_firstname"]=substr($name, 0, strrpos($name,' '));
	$_SESSION["bill_lastname"]=substr($name, strrpos($name,' ')+1);
	$_SESSION["bill_number"]=substr($street, strrpos($street,' ')+1);
	$_SESSION["bill_street"]=substr($street,  0, strrpos($street,' '));
	//LANDESKÜRZEL ZU LANDESNAMEN
	$res=q("SELECT * FROM shop_countries WHERE country_code = '".$field["PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE"]."';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	$_SESSION["bill_country"]=$row["country"];
	$_SESSION["bill_zip"]=$field["PAYMENTREQUEST_0_SHIPTOZIP"];
	$_SESSION["bill_city"]=$field["PAYMENTREQUEST_0_SHIPTOCITY"];
	$_SESSION["userphone"]=$field["PAYMENTREQUEST_0_SHIPTOPHONENUM"];
	$_SESSION["bill_PayPalNote"]=$field["PAYMENTREQUEST_0_NOTETEXT"];
	$_SESSION["bill_PayPalPayerID"]=$field["PAYERID"];



}
else { 
	echo '<statemsg><![CDATA[';
	while (list($key, $val) = each ($field)) {echo $key.": ".$val." | ";}
	echo ']]></statemsg>';
}	
echo '</response>';

?>