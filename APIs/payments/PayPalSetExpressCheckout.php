<?php

include("../functions/shop_get_prices.php");
include("../functions/mapco_gewerblich.php");

require_once 'PayPalCallerService.php';

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
$row=mysqli_fetch_array($res);
$nvpstr.="&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=".$row["country_code"];
$nvpstr.="&PAYMENTREQUEST_0_SHIPTOPHONENUM=".$_POST["phone"];;

//ORDERITEMS
$cartItemCount=0;
$cartItemTotal=0;


$res=q("SELECT * FROM shop_carts WHERE user_id='".$_POST["id_user"]."';", $dbshop, __FILE__, __LINE__);
while($row=mysqli_fetch_array($res))
{
	$price = get_prices($row["item_id"], $row["amount"]);

	//-> ITEMDATA
	//itemprice
	if($row["item_id"]==30701 or $row["item_id"]==30702)//Herbstaktion
		$price["net"]=0;
	
	$itemprice=number_format($price["net"],2);
	if ($gewerblich) 
	{
		$itemprice+=number_format($price["collateral_total"],2);
	}
	else 
	{
		$itemprice+=number_format($price["collateral_total"]/((100+UST)/100),2);
	}
	
	//$nvpstr.="&L_PAYMENTREQUEST_0_AMT".$cartItemCount."=".number_format($price["net"],2);
	$nvpstr.="&L_PAYMENTREQUEST_0_AMT".$cartItemCount."=".$itemprice;
	$nvpstr.="&L_PAYMENTREQUEST_0_QTY".$cartItemCount."=".$row["amount"];	
	//$cartItemTotal+=(number_format($price["net"],2)*($row["amount"]*1));
	$cartItemTotal+=($itemprice*($row["amount"]*1));
	
	$res2=q("SELECT * FROM shop_items_".$_POST["language"]." WHERE id_item='".$row["item_id"]."';", $dbshop, __FILE__, __LINE__);
	$row2=mysqli_fetch_array($res2);
	$nvpstr.="&L_PAYMENTREQUEST_0_NAME".$cartItemCount."=".$row2["title"];
	if (strlen($row2["short_description"])>33)
	{
		$nvpstr.="&L_PAYMENTREQUEST_0_DESC".$cartItemCount."=".substr($row2["short_description"],0,30)."...";	
	}
	else
	{
		$nvpstr.="&L_PAYMENTREQUEST_0_DESC".$cartItemCount."=".$row2["short_description"];
	}
	$cartItemCount++;

}
//Herbstaktion 2013
if(isset ($_SESSION["user_deposit"]) and $_SESSION["user_deposit"]>0)
{
	$itemprice=number_format(($_SESSION["user_deposit"]/((100+UST)/100)), 2)*(-1);
	$nvpstr.="&L_PAYMENTREQUEST_0_AMT".$cartItemCount."=".$itemprice;
	$nvpstr.="&L_PAYMENTREQUEST_0_QTY".$cartItemCount."=1";
	$cartItemTotal+=$itemprice;
	$nvpstr.="&L_PAYMENTREQUEST_0_NAME".$cartItemCount."=Gutschrift";
	$nvpstr.="&L_PAYMENTREQUEST_0_DESC".$cartItemCount."=Rabattaktion";	
}
//Ende Herbstaktion 2013

//ORDERDATA
if ($gewerblich)
{
	$ship_without_tax=number_format($_SESSION["shipping_costs"],2);
	//$tax=number_format(($cartItemTotal+$_SESSION["shipping_costs"])/100*19, 2);
	$tax=number_format(($cartItemTotal+$_SESSION["shipping_costs"])/100*UST, 2);
	$orderTotal=$cartItemTotal+number_format($_SESSION["shipping_costs"], 2)+$tax;
}
else
{
	//$ship_without_tax=number_format($_SESSION["shipping_costs"]/1.19, 2);
	$ship_without_tax=number_format($_SESSION["shipping_costs"]/((100+UST)/100), 2);
	//$tax=number_format(($cartItemTotal+$ship_without_tax)/100*19, 2);
	$tax=number_format(($cartItemTotal+$ship_without_tax)/100*UST, 2);
	$orderTotal=$cartItemTotal+$ship_without_tax+$tax;
}

$nvpstr.="&PAYMENTREQUEST_0_ITEMAMT=".$cartItemTotal;
$nvpstr.="&PAYMENTREQUEST_0_SHIPPINGAMT=".$ship_without_tax;
$nvpstr.="&PAYMENTREQUEST_0_TAXAMT=".$tax;
$nvpstr.="&PAYMENTREQUEST_0_AMT=".$orderTotal;

	$_SESSION["PAYMENTREQUEST_0_ITEMAMT"]=$cartItemTotal;
	$_SESSION["PAYMENTREQUEST_0_SHIPPINGAMT"]=$ship_without_tax;
	$_SESSION["PAYMENTREQUEST_0_TAXAMT"]=$tax;
	$_SESSION["PAYMENTREQUEST_0_AMT"]=$orderTotal;
	
$nvpstr.="&PAYMENTREQUEST_0_CURRENCYCODE=EUR";
$nvpstr.="&PAYMENTREQUEST_0_PAYMENTACTION=sale";
$nvpstr.="&PAYMENTREQUEST_0_DESC=Mapco Autoteile";
$nvpstr.="&PAYMENTREQUEST_0_CUSTOM=Mapco Onlineshop";
//$nvpstr.="&PAYMENTREQUEST_0_INVNUM=Order ID:1122334455";
$nvpstr.="&RETURNURL=".PATHLANG."online-shop/kasse/?PayPalAction=payment";
$nvpstr.="&CANCELURL=".PATHLANG."online-shop/kasse/?PayPalAction=abort";
//$nvpstr.="&RETURNURL=".PATH."shop_cart.php?PayPalAction=payment";	//ANGEZEIGTE SEITE BEI BESTÄTIGUNG DER ZAHLUNG
//$nvpstr.="&CANCELURL=".PATH."shop_cart.php?PayPalAction=abort"; //ANGEZEIGTE SEITE BEI ABBRUCH DER ZAHLUNG

//echo $nvpstr;

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
