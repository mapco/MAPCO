<?php

//include("../functions/shop_get_price.php");
include("../functions/shop_get_prices.php");
//include("../functions/shop_get_net_price.php");
include("../functions/mapco_gewerblich.php");

require_once 'CallerService.php';
//$_SESSION["language"]=$_POST["language"];

$gewerblich=gewerblich($_SESSION["id_user"]);
$nvpstr="";

//ADRESSE
$nvpstr.="&ADDROVERRIDE=1"; //ADRESSE wird bei PayPal angezeigt
$nvpstr.="&PAYMENTREQUEST_0_SHIPTONAME=".$_POST["firstname"]." ".$_POST["lastname"];
$nvpstr.="&PAYMENTREQUEST_0_SHIPTOSTREET=".$_POST["street1"]." ".$_POST["streetnr"];
$nvpstr.="&PAYMENTREQUEST_0_SHIPTOSTREET2=".$_POST["street2"];
$nvpstr.="&PAYMENTREQUEST_0_SHIPTOSTATE=";
$nvpstr.="&PAYMENTREQUEST_0_SHIPTOZIP=".$_POST["zip"];
$nvpstr.="&PAYMENTREQUEST_0_SHIPTOCITY=".$_POST["city"];
	//LÄNDERKÜRZEL BEZIEHEN
$res=q("SELECT * FROM shop_countries WHERE country = '".$_POST["countryname"]."';", $dbshop, __FILE__, __LINE__);
$row=mysql_fetch_array($res);
$nvpstr.="&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=".$row["country_code"];
$nvpstr.="&PAYMENTREQUEST_0_SHIPTOPHONENUM=".$_POST["phone"];;

//ORDERITEMS
$cartItemCount=0;
$cartItemTotal=0;
$res=q("SELECT * FROM shop_carts WHERE user_id='".$_POST["id_user"]."';", $dbshop, __FILE__, __LINE__);
while($row=mysql_fetch_array($res))
{
	$price = get_prices($row["item_id"], $row["amount"]);

	if ($gewerblich)
	{
		if ($_SESSION["rcid"]==16 and time()>mktime(0,0,0,8,1,2012) and time()<mktime(0,0,0,10,1,2012))
		{
			if($_SESSION["id_shipping"]==8 or $_SESSION["id_shipping"]==50) $special=10;
			else $special=5;
			$price["net"]=$price["net"]*((100-$special)/100);
			$price["total"]=$price["net"];
		}
	}
	
	//-> ITEMDATA
	$nvpstr.="&L_PAYMENTREQUEST_0_AMT".$cartItemCount."=".number_format($price["net"],2);
	$nvpstr.="&L_PAYMENTREQUEST_0_QTY".$cartItemCount."=".$row["amount"];	
	$cartItemTotal+=(number_format($price["net"],2)*($row["amount"]*1));
	
	$res2=q("SELECT * FROM shop_items_".$_POST["language"]." WHERE id_item='".$row["item_id"]."';", $dbshop, __FILE__, __LINE__);
	$row2=mysql_fetch_array($res2);
	$nvpstr.="&L_PAYMENTREQUEST_0_NAME".$cartItemCount."=".urlencode($row2["title"]);
	if (strlen($row2["short_description"])>33)
	{
		$nvpstr.="&L_PAYMENTREQUEST_0_DESC".$cartItemCount."=".urlencode(substr($row2["short_description"],0,30)."...");	
	}
	else
	{
		$nvpstr.="&L_PAYMENTREQUEST_0_DESC".$cartItemCount."=".urlencode($row2["short_description"]);
	}
	$cartItemCount++;

}

//ORDERDATA
$tax=number_format(($cartItemTotal+$_SESSION["shipping_costs"])/100*19, 2);
$orderTotal=$cartItemTotal+number_format($_SESSION["shipping_costs"], 2)+$tax;

$nvpstr.="&PAYMENTREQUEST_0_ITEMAMT=".$cartItemTotal;
$nvpstr.="&PAYMENTREQUEST_0_SHIPPINGAMT=".number_format($_SESSION["shipping_costs"], 2);
$nvpstr.="&PAYMENTREQUEST_0_TAXAMT=".$tax;
$nvpstr.="&PAYMENTREQUEST_0_AMT=".$orderTotal;

	$_SESSION["PAYMENTREQUEST_0_ITEMAMT"]=$cartItemTotal;
	$_SESSION["PAYMENTREQUEST_0_SHIPPINGAMT"]=number_format($_SESSION["shipping_costs"], 2);
	$_SESSION["PAYMENTREQUEST_0_TAXAMT"]=$tax;
	$_SESSION["PAYMENTREQUEST_0_AMT"]=$orderTotal;
	
$nvpstr.="&PAYMENTREQUEST_0_CURRENCYCODE=EUR";
$nvpstr.="&PAYMENTREQUEST_0_PAYMENTACTION=sale";
$nvpstr.="&PAYMENTREQUEST_0_DESC=Mapco Autoteile";
$nvpstr.="&PAYMENTREQUEST_0_CUSTOM=Mapco Onlineshop";
//$nvpstr.="&PAYMENTREQUEST_0_INVNUM=Order ID:1122334455";
$nvpstr.="&RETURNURL=".PATH."shop_cart.php?PayPalAction=payment";	//ANGEZEIGTE SEITE BEI BESTÄTIGUNG DER ZAHLUNG
$nvpstr.="&CANCELURL=".PATH."shop_cart.php?PayPalAction=abort"; //ANGEZEIGTE SEITE BEI ABBRUCH DER ZAHLUNG



$field=hash_call("SetExpressCheckout",$nvpstr);

echo '<response>';
echo '<state><![CDATA['.$field["ACK"].']]></state>';
if ($field["ACK"]=="Success") {
	$_SESSION["paypaltoken"]=$field["TOKEN"];
	//echo '<token><![CDATA['.$field["TOKEN"].']]></token>';
	echo '<paypal_href>'.PAYPAL_URL.$field["TOKEN"].'</paypal_href>';
}
else {
	echo '<statemsg><![CDATA[';
	while (list($key, $val) = each ($field)) {echo $key.": ".$val." | ";}
	echo $nvpstr;
	echo ']]></statemsg>';
}	

echo '</response>';

//echo $nvpstr;
//while (list($key,$val) = each ($field)) { echo $key.': '.$val.' | ';}
/*
	$_SESSION["paypaltoken"]=$field["TOKEN"];

echo PAYPAL_URL.urlencode($field["TOKEN"]);
*/
?>
