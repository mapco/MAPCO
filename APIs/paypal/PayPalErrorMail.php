<?php 

//	include("../functions/cms_send_html_mail.php");

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

mail("developer@mapco.de", $subject, print_r($_POST, true));

?>