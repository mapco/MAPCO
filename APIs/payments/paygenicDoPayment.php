<?php

include("../functions/shop_get_prices.php");
include("../functions/mapco_gewerblich.php");
require_once 'paygenicConstants.php';

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
while($row=mysqli_fetch_array($res))
{
	$price = get_prices($row["item_id"], $row["amount"]);
	
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


	//$cartItemTotal+=$price["net"];
	//$cartItemTotal+=(number_format($price["net"],2)*($row["amount"]*1));
	$cartItemTotal+=$itemprice*($row["amount"]*1);
}

//Herbstaktion 2013
if(isset ($_SESSION["user_deposit"]) and $_SESSION["user_deposit"]>0)
{
	$itemprice=number_format(($_SESSION["user_deposit"]/((100+UST)/100)), 2);
	$cartItemTotal=$cartItemTotal-$itemprice;
}
//Ende Herbstaktion 2013

//AMOUNT
//$tax=number_format(($cartItemTotal+$_SESSION["shipping_costs"])/100*19, 2);
$tax=number_format(($cartItemTotal+$_SESSION["shipping_costs"])/100*UST, 2);
$orderTotal=$cartItemTotal+$_SESSION["shipping_costs"]+$tax;

if ($gewerblich)
{
	$ship_without_tax=number_format($_SESSION["shipping_costs"],2);
	//$tax=number_format(($cartItemTotal+$_SESSION["shipping_costs"])/100*19, 2);
	$tax=number_format(($cartItemTotal+$_SESSION["shipping_costs"])/100*UST, 2);
	$orderTotal=$cartItemTotal+number_format($_SESSION["shipping_costs"], 2)+$tax;
}
else
{
	$ship_without_tax=number_format($_SESSION["shipping_costs"]/((100+UST)/100), 2);
	$tax=number_format(($cartItemTotal+$ship_without_tax)/100*UST, 2);
	$orderTotal=$cartItemTotal+$ship_without_tax+$tax;
}





//SET PAYGENIC TansactionID
$_SESSION["paygenicTransactionID"]=(string)time().'_'.$_POST["id_user"];

	$orderTotal=number_format($orderTotal,2);

	$bfnvpreq="";
	//NVPRequest for submitting to server
	$bfnvpreq.='MerchantID='.MerchantID;
	$bfnvpreq.='&Currency=EUR';
	$bfnvpreq.='&Amount='.str_replace(".","",(string)$orderTotal); // ÜBERGABE in CENT
//	$bfnvpreq.='&Amount=600';
	$bfnvpreq.='&TransID='.$_SESSION["paygenicTransactionID"];
	$bfnvpreq.='&Capture=SALE';
	$bfnvpreq.='&OrderDesc='.urlencode("Ihre Bestellung bei MAPCO Autotechnik");
	$bfnvpreq.='&Userdata=paygenic_mapco'; //WIRD ZURÜCKGEGEBEN -> FÜR SHOP-CART unterscheidung zwischen PayPal & PayGenic
	$bfnvpreq.='&URLSuccess='.URLSuccess;
	$bfnvpreq.='&URLFailure='.URLFailure;
	$bfnvpreq.='&URLNotify='.URLNotify;
	$len=strlen($bfnvpreq);
	
	//VERSCHLÜSSELUNG DES DATENTEILS
	$cipher = mcrypt_module_open('blowfish', '', 'ecb', '');
	$iv =  '12345678';
	if (mcrypt_generic_init($cipher, BlowFishKey, $iv) != -1)
	{
		// PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
		$cipherText = mcrypt_generic($cipher,$bfnvpreq );
		mcrypt_generic_deinit($cipher);
		$data=strtoupper(bin2hex($cipherText));
	}


echo '<response>'."\n";
	echo '<Ack>Success</Ack>'."\n";
	echo '<PaymentData>'."\n";
		echo '<total>'.str_replace(".","",(string)$orderTotal).'</total>';
		echo '<paygenicurl>'.$url.'</paygenicurl>'."\n";
		echo '<paygenicurl><![CDATA['.$url.']]></paygenicurl>'."\n";
		echo '<MerchantID><![CDATA['.urlencode(MerchantID).']]></MerchantID>'."\n";
		echo '<len><![CDATA['.$len.']]></len>'."\n";
		echo '<data><![CDATA['.$data.']]></data>'."\n";
	echo '</PaymentData>'."\n";
echo '</response>'."\n";
?>


