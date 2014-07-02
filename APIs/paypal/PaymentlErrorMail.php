<?php 

//	include("../functions/cms_send_html_mail.php");

if (isset($_POST["Paymentplatform"]) && $_POST["Paymentplatform"]=="PayPal")
{

	$subject='Fehler bei der Weiterleitung eines Kunden zu PayPal (MAPCO-SHOP)';

	$text ='<p><b>Kundendaten: </b><br />';
	$text.=$_POST["firstname"].' '.$_POST["lastname"].'<br />';
	$text.=$_POST["street1"].' '.$_POST["streetnr"].'<br />';
	$text.=$_POST["street2"].'<br />';
	$text.=$_POST["zip"].' '.$_POST["city"].'<br />';
	$text.=$_POST["countryname"].'<br />';
	$text.=$_POST["phone"].'<br /><br />';
	$text.='<b>UserID: '.$_POST["id_user"].'</b></p>';

	$text.='Meldung von API PayPalSetExpressCheckout: </br>';
	$text.=$_POST["data"];

	mail("developer@mapco.de", $subject, $text);
}

if (isset($_POST["Paymentplatform"]) && $_POST["Paymentplatform"]=="Paygenic")
{
	$subject='Fehler bei der Zahlung über PayGenic (MAPCO-SHOP)';

	$text ='<p><b>Kundendaten: </b><br />';
	$text.=$_POST["firstname"].' '.$_POST["lastname"].'<br />';
	$text.=$_POST["street1"].' '.$_POST["streetnr"].'<br />';
	$text.=$_POST["street2"].'<br />';
	$text.=$_POST["zip"].' '.$_POST["city"].'<br />';
	$text.=$_POST["countryname"].'<br />';
	$text.=$_POST["phone"].'<br /><br />';
	$text.='<b>UserID: '.$_POST["id_user"].'</b></p>';

	$text.='Meldung von PayGenic: </br>';
	$text.=$_POST["code"].'<br />';
	$text.=$_POST["error_description"];

	mail("developer@mapco.de", $subject, $text);
	
}

?>