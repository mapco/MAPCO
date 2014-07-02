<?php

/*
	- SKRIPT prüft Zahltyp der Bestellung und führt Zahlungsaktionen durch
	- Zahlinformationnen werden in die Order geschrieben
	- Orderevent (OrderAdd) wird geschrieben
	- Buchung zur Order (OrderAdd) wird durchgeführt
	- bei erfolg wird abschließend der Orderstatus von 0 auf 1 oder 7 gesetzt

*/


	include("config.php");
	
	include("functions/cms_t.php");
	include("functions/cms_tl.php");



	$_SESSION["checkout_order_id"] = 1851448;
	
	
	//GET PAYMENT_TYPE_ID
	$res = q("SELECT * FROM shop_orders WHERE id_order = ".$_SESSION["checkout_order_id"], $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows( $res ) == 0 )
	{
		//REDIRECT TO CHECKOUT
	}
	$row = mysqli_fetch_assoc( $res );
	$payment_type_id = $row["payments_type_id"];

	$menu_hide = true; // Menü ausblenden
	include("templates/".TEMPLATE."/header.php");



	//PAYMENT TYPE IS DEFINED BY RETURN URL
		/*
			https://www.mapco.de/online-shop/zahlungsverarbeitung/paypal
		*/
	$_GET['paymenttype'] = "";

	if( isset( $_GET["getvars1"] ) )
	{
		$payment_response_from = $_GET["getvars1"];
	}

	if ( $payment_response_from == "paypal")
	{

		$paypal_token 	= $_REQUEST["token"];
		$paypal_payerID = $_REQUEST["PayerID"];
	}
	else
	{
		$paypal_token 	= "";
		$paypal_payerID = "";
	}


	//WRITE PARAMS IN SESSION
	$_SESSION["checkout"]["paypal_token"] 			= $paypal_token;
	$_SESSION["checkout"]["paypal_payerID"] 		= $paypal_payerID;
	$_SESSION["checkout"]["payment_response_from"] 	= $payment_response_from;
	$_SESSION["checkout"]["getvars2"] 				= $_GET["getvars2"];



?>

<style>
	#assist_div
	{
		border:				solid;
		border-color:		#E6E6E6;
		border-radius:		5px;
		border-width:		1px;
		display:			none;
		margin-bottom:		20px;
		padding-bottom:		50px;	
		padding-top:		50px;
	}

</style>


<script type="text/javascript">

	var $payment_type_id			= "<?php echo $payment_type_id;?>";
	var $paymenttype				= "<?php echo $payment_response_from;?>";
	var $getvars2					= "<?php echo $_GET["getvars2"];?>";
	


	$( document ).ready(function()
	{
		$( '#main_div' ).css("height", $( window ).height() - 350);
		
		payment_action();
		
	});

	function payment_action()
	{
		if ( $payment_type_id == 4) // PAYPAL
		{
			//CHECK von woher der Aufruf der Seite kam
				// shop_checkout ($paymenttype == "") -> PayPalExpressCheckoutSet
				// PayPal ($paymenttype == "paypal") -> PayPalExpressCheckoutGet -> PayPalExpressCheckoutDo
				
			if ( $paymenttype == "" )
			{
				alert("Die Zahlungsdaten werden an PayPal gesendet...");
			// SET EXPRESSCHECKOUT	
				wait_dialog_show("Die Zahlungsdaten werden zu PayPal gesendet...");
				
			}
			if ( $paymenttype == "paypal" )
			{
				if ( $getvars2 == "cancel")
				{
					//RÜCKLEITUNG AUF CHECKOUT
					wait_dialog_show("Sie werden auf die Bestellübersicht zurückgeleitet.");	
				}
				
				if ( $getvars2 == "success")
				{
					//GET EXPRESSCHECKOUT + DO EXPRESSCHECKOUT
					//SHOW MESSAGE TO USER
					
					wait_dialog_show("Ihre Zahlung wird verbucht und die Bestellung verarbeitet.");
				}
			}
		}
		else
		{
			wait_dialog_show("Ihre Bestellung wird verarbeitet.");
			
		}

		
		var $postfield = new Object();
		$postfield['API'] 					= 'payments';
		$postfield['APIRequest'] 			= 'ShopCheckoutPaymentsProcess';
		$postfield['ValidateResponse']		= true;

		$.post('<?php echo PATH;?>soa2/', $postfield, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			paymentaction_evaluate ( $xml );
		});

	//	soa3( $postfield, 'paymentaction_evaluate', 'xml');
		
	}

	function paymentaction_evaluate ( $xml )
	{
		//show_status2($xmlstring);

		// FUNKTION WIRD AUFGERUFEN, WENN ZAHLUNGBERARBEITUNG DURCHLAUFEN WURDE
		if ( $xml.find("Ack").text() == "Success" )
		{
			//PAYPAL
			if ( $payment_type_id == 4)
			{
				// WENN $paymenttype == "paypal" && $getvars2 == "success" dann wurden GETEXPRESSCHECKOUT UND DOEXPRESSCHECKOUT durchgeführt -> Bestellabschluss
				if ( $paymenttype == "paypal" && $getvars2 == "success" )
				{
					paymentsSuccess ();
					return;
				}
				// AUFRUF NACH SETEXPRESSCHECKOUT -> WEITERLEITUNG ZU PAYPAL
				else
				{
					var $paypal_url = $xml.find("paypal_redirect_url").text();
					paypal_redirect ( $paypal_url );
					return;
				}
			}
			else // OTHER PAYMENTS
			{
				paymentsSuccess ();
				return;
			}
		}
		else
		{
			paymentFailure ();	
		}
	}
	
	function paypal_redirect ( $paypal_url )
	{
		//SHOW MESSAGE TO USER
		if ( $( '#assist_div' ).length == 0 )
		{
			$( '#main_div' ).append( $( '<div id="assist_div"></div>' ) );
		}
	
		$( '#assist_div' ).css("position","relative");
		$( '#assist_div' ).css("top", Math.max(0, (($( '#main_div' ).height() - $( '#assist_div' ).outerHeight()) / 2) + $( '#main_div' ).scrollTop()) + "px");
	
		//$( '#assist_div' ).append( '<?php echo t("Sie werden zu PayPal weitergeleitet..." ); ?>' );
		$( '#assist_div' ).append( '<strong>Sie werden zu PayPal weitergeleitet...</strong> LINK:'+$paypal_url );
		$( '#assist_div' ).fadeIn(500);

		location.href = $paypal_url;
		
		setTimeout( function ()
		{
			$( '#assist_div' ).fadeOut(300);
			setTimeout( function ()
			{
				$( '#assist_div' ).html( '' );
				//$( '#assist_div' ).append( '<?php echo t("Sollten Sie nicht innerhalb weiniger Sekunden zu PayPal weitergeleitet worden sein, nutzen Sie bitte folgenden Link: " ); ?>' );
				$( '#assist_div' ).append( '<strong>Sollten Sie nicht innerhalb weiniger Sekunden zu PayPal weitergeleitet worden sein, nutzen Sie bitte folgenden Link: </strong><a href='+$paypal_url+'> Weiter zu PayPal </a>' );
				$( '#assist_div' ).fadeIn(300);
			}, 300 );
		}, 7000 );
		
	}
	
	function paymentsSuccess( )
	{
		location.href = '<?php echo PATHLANG . tl( 848, 'alias' );?>';
	}

	function paymentFailure ( )
	{
		location.href = '<?php echo PATHLANG . tl( 844, 'alias' );?>';
	}
	
</script>
<?php
	
	echo '<div id="main_div"></div>';
	include("templates/".TEMPLATE."/footer.php");
	
?>


