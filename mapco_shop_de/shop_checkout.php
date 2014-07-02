<?php
	
	include("config.php");
	
	include("functions/cms_t.php");
	include("functions/cms_tl.php");
	
//	$login_required=true;
//	$title = 'Kasse';
	
//	$_SESSION["get_url"] = $_SERVER["REQUEST_URI"];
	
//	echo $_POST['url'];
//	echo $_SERVER["REQUEST_URI"];
		
	$menu_hide = true; // Menü ausblenden

	include("templates/".TEMPLATE."/header.php");
	
	$_SESSION["get_url"] = $_SERVER["REQUEST_URI"];
	
	// is checkout_order_id set?
	$checkout_order_id = 		0;
	$checkout_order_id_set = 	0;
	
	if ( isset( $_SESSION['checkout_order_id'] ) )
	{
		$checkout_order_id = 		$_SESSION['checkout_order_id'];
		$checkout_order_id_set = 	1;
	}
	
	// is user logged in?
	$logged_in = 0;
	
	if ( isset( $_SESSION['id_user'] ) )
	{
		$logged_in = 1;
	}
	
//	print_r( $_SESSION );
//	exit;
	
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
	
	#cart_div
	{
		border:				solid;
		border-color:		#E6E6E6;
		border-radius:		5px;
		border-width:		1px;
		margin-bottom:		10px;
		padding-bottom:		10px;
		padding-top:		10px;
	}

	.button
	{
		background-color:	#F2F2F2;
		border:				solid;
		border-color:		#E6E6E6;
		border-radius:		5px;
		border-width:		1px;
		cursor:				pointer;
		font-weight:		bold;
//		height:				40px;
		margin-bottom:		20px;
		margin-left:		auto;
		margin-right:		auto;
		margin-top:			20px;
		padding-bottom:		10px;
		padding-top:		10px;
		width:				300px;
	}
	
	.button:hover
	{ 
		background-color:	#8DD68D;
	}
	
	.p_20_b
	{
		font-size:			20px;
		font-weight:		bold;
	}
	
</style>

<script type="text/javascript">

	var $checkout_order_id = 		<?php echo $checkout_order_id;?>;
	var $checkout_order_id_set = 	<?php echo $checkout_order_id_set;?>;
	var $logged_in = 				<?php echo $logged_in;?>;
	
	$( document ).ready(function()
	{
		shop_checkout_main();
	});
	
	function assist_address_show()
	{
		var main = $( '#assist_div' );
		
		main.append( '<p class="p_20_b"><?php echo t("Wir benötigen noch Angaben zu Ihrer Adresse.");?></p>' );
		main.append( '<div id="adr_button" class="button"><?php echo t("Hier geht es zur Adressen-Eingabe");?></div>' );
		
		$( '#adr_button' ).click(function()
		{
			location.href = '<?php echo PATHLANG . tl( 664, 'alias' );?>';
		});
		
		$( '#assist_div' ).slideToggle(500);
	}
	
	function assist_login_show()
	{
		var main = $( '#assist_div' );
		
		main.append( '<p class="p_20_b"><?php echo t("Bitte wählen Sie aus:");?></p>' );
		main.append( '<div id="login_button_2" class="button"><?php echo t("Ich bin bereits Kunde (zur Anmeldung)");?></div>' );
		main.append( '<div id="guest_button" class="button"><?php echo t("Ich möchte ohne Anmeldung einkaufen");?></div>' );
		
		$( '#login_button_2' ).click(function()
		{
			location.href = '<?php echo PATHLANG . tl( 661, 'alias' );?>';
		});
		
		$( '#guest_button' ).click(function()
		{
//			alert( 'Zur Adressenseite' );
			wait_dialog_show();

			$postdata = 				new Object();
			$postdata['API'] = 		'shop';
			$postdata['APIRequest'] = 	'CheckoutGuestSet';
			
			soa2( $postdata, 'checkout_guest_set_callback');
		});
		
		$( '#assist_div' ).slideToggle(500);
//		$( '#cart_div' ).children().prop('disabled', true);
	}
	
	function assist_payment_show()
	{
		var main = $( '#assist_div' );
		
		main.append( '<p class="p_20_b"><?php echo t("Wir benötigen noch Angaben zur Zahlungsmethode.");?></p>' );
		main.append( '<div id="payment_button" class="button"><?php echo t("Hier geht es zur Zahlungsmethoden-Eingabe");?></div>' );
		
		$( '#payment_button' ).click(function()
		{
			location.href = '<?php echo PATHLANG . tl( 665, 'alias' );?>';
		});
		
		$( '#assist_div' ).slideToggle(500);
	}
	
	function assist_shipping_show()
	{
		var main = $( '#assist_div' );
		
		main.append( '<p class="p_20_b"><?php echo t("Wir benötigen noch Angaben zur Versandart.");?></p>' );
		main.append( '<div id="shipping_button" class="button"><?php echo t("Hier geht es zur Versandart-Eingabe");?></div>' );
		
		$( '#shipping_button' ).click(function()
		{
			location.href = '<?php echo PATHLANG . tl( 666, 'alias' );?>';
		});
		
		$( '#assist_div' ).slideToggle(500);
	}
	
	function checkout_guest_set_callback()
	{
//		alert( 'Guest set' );
		location.href = '<?php echo PATHLANG . tl( 664, 'alias' );?>';
	}
	
	function checkout_integrity_check_callback( $xml )
	{
//		show_status2( $xml );
		if ( $xml.find( 'checkout_adr_edit' ).text() == 1 )
		{
			assist_address_show();
		}
		else if ( $xml.find( 'checkout_payment_edit' ).text() == 1 )
		{
			assist_payment_show();
		}
		else if ( $xml.find( 'checkout_shipping_edit' ).text() == 1 )
		{
			assist_shipping_show();
		}
	}
	
	function checkout_order_id_unset()
	{
		wait_dialog_show();
		
		$postdata = 				new Object();
		$postdata['API'] = 			'cms';
		$postdata['APIRequest'] = 	'VariableUnset';
		$postdata['key'] = 			'checkout_order_id';
		
		soa2( $postdata, 'checkout_order_id_unset_callback' );
	}
	
	function checkout_order_id_unset_callback()
	{
		alert( 'unsetted' );
	}
/*	
	function order_set()
	{
		wait_dialog_show();
		
		$postdata = 				new Object;
		$postdata['API'] = 			'shop';
		$postdata['APIRequest'] = 	'CheckoutOrderSet';

		soa2( $postdata, 'order_set_callback', 'xml' );

	}
*/	
/*
	function order_set_callback( $xml )
	{
//		show_status2( $xml + '<?php echo str_replace("\n","",print_r( $_SESSION, true )); ?>' );
	}
*/	
	function session_show()
	{
		show_status2( '<?php echo str_replace("\n","",print_r( $_SESSION, true )); ?>' );
	}
	
	function shop_checkout_main()
	{	
		//show_status2( '<?php echo str_replace("\n","",print_r( $_SESSION, true )); ?>' );
		
		if ( $checkout_order_id_set == 0 )
		{
			location.href = '<?php echo PATHLANG . tl( 844, 'alias' );?>';
			return;
		}
		
		if ( $( '#assist_div' ).length == 0 )
		{
			$( '#main_div' ).append( $( '<div id="assist_div"></div>' ) );
		}
		
		if ( $( '#cart_div' ).length == 0 )
		{
			$( '#main_div' ).append( $( '<div id="cart_div"></div>' ) );
		}
		
		$( '#cart_div' ).append( $( '<h1>Kasse</h1>' ) );
		
		$( '#cart_div' ).append( $( '<input type="button" id="unset_checkout_order_id_button" value="unset checkout_order_id">' ) );
		
		$( '#unset_checkout_order_id_button' ).click(function()
		{
			checkout_order_id_unset();
		});
		
		$( '#cart_div' ).append( $( '<input type="button" id="session_show_button" value="session_show">' ) );
		
		$( '#session_show_button' ).click(function()
		{
			session_show();
		});
		
//		alert( 'checkout_order_id: ' + $checkout_order_id + '\n' + 'checkout_order_id_set: ' + $checkout_order_id_set );
		
		if ( $checkout_order_id_set == 1 && $checkout_order_id == 0 )
		{
//			alert( 'Hallo' );
			$( '#cart_div' ).append( '<h1><?php echo t( 'In Ihrem Warenkorb befinden sich keine Artikel' );?></h1>' );
			return;
		}
		
		if ( $logged_in == 0 )
		{
			assist_login_show();
		}
		
		if ( $logged_in == 1 )
		{
			wait_dialog_show();
			
			$postdata = 						new Object();
			$postdata['API'] = 					'shop';
			$postdata['APIRequest'] =			'CheckoutIntegrityCheck';
			$postdata['checkout_order_id'] = 	<?php echo $_SESSION['checkout_order_id'];?>;
			
			soa2( $postdata, 'checkout_integrity_check_callback' );
		}
//		alert( 'logged_in: ' + $logged_in );
		
//		order_set();
				
	}
	
</script>
		
<?php
	
	echo '<div id="main_div"></div>';
	include("templates/".TEMPLATE."/footer.php");
	
?>