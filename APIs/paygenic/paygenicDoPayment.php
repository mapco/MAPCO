<?php

include("../config.php");
include("../functions/shop_get_prices.php");
//include("../functions/shop_get_net_price.php");
include("../functions/mapco_gewerblich.php");
include("blowfish.class.php");

require_once 'constants.php';


$PaymentMethod=$_POST["PaymentMethod"];
$gewerblich=gewerblich($_POST["id_user"]);

switch ($PaymentMethod)
{
	case "Kreditkarte": $url=PayGenicCreditCardURL; break;
	case "Sofortüberweisung": $url=PayGenicSofortURL; break;
	case "Lastschrift": $url=PayGenicDirectDebitURL; break;
}
	
//AMOUNT BERECHNEN
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
	$cartItemTotal+=$price["net"];
}
//AMOUNT
$tax=number_format(($cartItemTotal+$_SESSION["shipping_costs"])/100*19, 2);
$orderTotal=$cartItemTotal+$_SESSION["shipping_costs"]+$tax;

//SET PAYGENIC TansactionID
$_SESSION["paygenicTransactionID"]=(double)microtime().time();

	$bfnvpreq="";
	//NVPRequest for submitting to server
	$bfnvpreq.='&Currency=EUR';
	//$bfnvpreq.='&Amount='.str_replace(".","",(string)$orderTotal); // ÜBERGABE in CENT
	$bfnvpreq.='&Amount=600';
//	$bfnvpreq.='&TransID='.$_SESSION["paygenicTransactionID"];
//	$bfnvpreq.='&Capture=SALE';
//	$bfnvpreq.='&OrderDesc='.urlencode("Ihre Bestellung bei MAPCO Autotechnik");
//	$bfnvpreq.='&Userdata=paygenic';
	$bfnvpreq.='&URLSuccess='.URLSuccess;
	$bfnvpreq.='&URLFailure='.URLFailure;
	$bfnvpreq.='&URLNotify='.URLNotify;
	$len=strlen($bfnvpreq);
	
	//VERSCHLÜSSELUNG DES DATENTEILS
	$blowfish = new Blowfish(BlowFishKey);
	$data=$blowfish->Encrypt($bfnvpreq);


echo '<response>'."\n";
	echo '<Ack>Success</Ack>'."\n";
	echo '<PaymentData>'."\n";
		echo '<paygenicurl><![CDATA['.$url.']]></paygenicurl>'."\n";
		echo '<MerchantID><![CDATA['.$urlencode(MerchantID).']]></MerchantID>'."\n";
		echo '<len><![CDATA['.$len.']]></len>'."\n";
		echo '<data><![CDATA['.$data.']]></data>'."\n";
	echo '</PaymentData>'."\n";
echo '</response>'."\n";



?>


