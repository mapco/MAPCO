<?php
	
	include("config.php");
	
	include("functions/cms_t.php");
	include("functions/cms_tl.php");
//	include("functions/shop_OrderSumGet.php");
	
//	$login_required=true;
//	$title = 'Kasse';
	
//	$_SESSION["get_url"] = $_SERVER["REQUEST_URI"];
	
//	echo $_POST['url'];
//	echo $_SERVER["REQUEST_URI"];
	
	// zu Testzwecken
	if ( $_SESSION['id_shop'] != 1 )
	{
		exit;
	}
		
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
		
		// checkout-order price correction
		$postdata = 				array();
		$postdata['API'] = 			'shop';
		$postdata['APIRequest'] = 	'CheckoutPriceCorrection';
		
		$request = soa2( $postdata, __FILE__, __LINE__ );
	}
	
	// is user logged in?
	$logged_in = 0;
	
	if ( isset( $_SESSION['id_user'] ) or isset( $_SESSION['checkout_user_id'] ) )
	{
		$logged_in = 1;
	}
	
//	print_r( $_SESSION );
//	exit;
	
?>

<script type="text/javascript">

	var $checkout_order_id = 		<?php echo $checkout_order_id;?>;
	var $checkout_order_id_set = 	<?php echo $checkout_order_id_set;?>;
	var $logged_in = 				<?php echo $logged_in;?>;

	$( document ).ready(function()
	{
		shop_checkout_main();
	});
	
	function agb_dialog_show( $message )
	{
		$( "#message" ).html( $message );
		$( "#message" ).dialog
		({	buttons:
			[
				{ text: "<?php echo t("Ok"); ?>", click: function() 
					{
						$(this).dialog("close");
						$('html, body').animate( { scrollTop: ( $( '#agb_td' ).offset().top )}, 'slow' );
						$( '#agb_td' ).css( 'border-color', '#FF0000' );
						$( '#agb_td' ).css( 'background-color', '#FFC9C9' );
					}
				}
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Achtung"); ?>!",
			width:300
		});
	}
	
	function agb_set()
	{
		if ( $( '#agb_checkbox' ).prop( 'checked' ) )
		{
			$( '#agb_td' ).css( 'border-color', '#E6E6E6' );
			$( '#agb_td' ).css( 'background-color', '#FFFFFF' );
		}
		//alert( $( '#agb_checkbox' ).prop( 'checked' ) );
	}
	
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
			wait_dialog_show();

			$postdata = 				new Object();
			$postdata['API'] = 			'shop';
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
		location.href = '<?php echo PATHLANG . tl( 664, 'alias' );?>';
	}
	
	function checkout_integrity_check_callback( $xml )
	{
		if ( $xml.find( 'checkout_adr_edit' ).text() == 1 )
		{
			assist_address_show();
			return;
		}
		else if ( $xml.find( 'checkout_payment_edit' ).text() == 1 )
		{
			assist_payment_show();
			return;
		}
		else if ( $xml.find( 'checkout_shipping_edit' ).text() == 1 )
		{
			assist_shipping_show();
			return;
		}
		
		wait_dialog_show();
		
		$postdata = 				new Object();
		$postdata['API'] = 			'shop';
		$postdata['APIRequest'] = 	'OrderDetailGet_neu_test';
		$postdata['OrderID'] = 		<?php if ( isset( $_SESSION['checkout_order_id'] ) ) { echo $_SESSION['checkout_order_id']; } else { echo 0; }?>;
//		$postdata['gewerblich'] = 	0; // for testing
		
		soa2( $postdata, 'order_show' );
//		soa2( $postdata, 'order_show', 'xml' );		
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
//		alert( 'unsetted' );
	}
	
	function order_send()
	{
		if ( $( '#agb_checkbox' ).prop( 'checked' ) )
		{
//			alert( 'Ok' ); // Weiterleitung Nicos Seite
			location.href = '<?php echo PATHLANG . tl( 847, 'alias' );?>';
		}
		else
		{
			agb_dialog_show( '<?php echo t( 'Sie müssen noch den Allgemeinen Geschäftsbedingungen zustimmen' );?>!' );
		}
	}
	
	function order_show( $xml )
	{
//		show_status2( $xml ); return;
		var div, table, td, text, th, tr;
		
		table = $( '<table></table>' );
			
			tr = $( '<tr></tr>' );
				td = $( '<td colspan="3"></td>' );
					div = $( '<div class="order_button_div" title="<?php echo t('Kostenpflichtig bestellen');?>"><?php echo t('Kostenpflichtig bestellen');?></div>' );
					td.append( div );
				tr.append( td );
			table.append( tr );
			
			tr = $( '<tr></tr>' );
				td = $( '<td class="header_td" colspan="3"><?php echo t( 'Bestellübersicht' );?>:</td>' );
				tr.append( td );
			table.append( tr );
			
			// addresses
			tr = $( '<tr></tr>' );
				td = $( '<td class="address_td" colspan="2"></td>' );
					if ( $xml.find( 'ship_adr_id' ).text() == '0' ||  $xml.find( 'ship_adr_id' ).text() ==  $xml.find( 'bill_adr_id' ).text() )
					{
						text = $( '<p class="p_16_b address"><?php echo t( 'Rechnungs-/Lieferanschrift' );?></p>' );
						td.append( text );
					}
					else
					{
						text = $( '<p class="p_16_b address"><?php echo t( 'Rechnungsanschrift' );?></p>' );
						td.append( text );
					}
					if ( $xml.find( 'bill_adr_company' ).text() != '' )
					{
						text = $( '<span class="p_15 address">' + $xml.find( 'bill_adr_company' ).text() + '</span><br />' );
						td.append( text );
					}
					text = $( '<span class="p_15 address">' + $xml.find( 'bill_adr_firstname' ).text() + ' ' + $xml.find( 'bill_adr_lastname' ).text() + '</span><br />' );
					td.append( text );
					text = $( '<span class="p_15 address">' + $xml.find( 'bill_adr_street' ).text() + ' ' + $xml.find( 'bill_adr_number' ).text() + '</span><br />' );
					td.append( text );
					if ( $xml.find( 'bill_adr_additional' ).text() != '' )
					{
						text = $( '<span class="p_15 address">' + $xml.find( 'bill_adr_additional' ).text() + '</span><br />' );
						td.append( text );
					}
					text = $( '<span class="p_15 address">' + $xml.find( 'bill_adr_zip' ).text() + ' ' + $xml.find( 'bill_adr_city' ).text() + '</span><br />' );
					td.append( text );
					text = $( '<span class="p_15 address">' + $xml.find( 'bill_adr_country' ).text() + '</span><br />' );
					td.append( text );
					text = $( '<a class="change_link address" href="<?php echo PATHLANG . tl( 664, 'alias' );?>"><?php echo t( 'ändern' );?></a><br />' );
					td.append( text );
				tr.append( td );
				td = $( '<td class="address_td"></td>' );
					if ( $xml.find( 'ship_adr_id' ).text() != 0 &&  $xml.find( 'ship_adr_id' ).text() !=  $xml.find( 'bill_adr_id' ).text() )
					{
						text = $( '<p class="p_16_b address"><?php echo t( 'Lieferanschrift' );?></p>' );
						td.append( text );
						if ( $xml.find( 'ship_adr_company' ).text() != '' )
						{
							text = $( '<span class="p_15 address">' + $xml.find( 'ship_adr_company' ).text() + '</span><br />' );
							td.append( text );
						}
						text = $( '<span class="p_15 address">' + $xml.find( 'ship_adr_firstname' ).text() + ' ' + $xml.find( 'ship_adr_lastname' ).text() + '</span><br />' );
						td.append( text );
						text = $( '<span class="p_15 address">' + $xml.find( 'ship_adr_street' ).text() + ' ' + $xml.find( 'ship_adr_number' ).text() + '</span><br />' );
						td.append( text );
						if ( $xml.find( 'ship_adr_additional' ).text() != '' )
						{
							text = $( '<span class="p_15 address">' + $xml.find( 'ship_adr_additional' ).text() + '</span><br />' );
							td.append( text );
						}
						text = $( '<span class="p_15 address">' + $xml.find( 'ship_adr_zip' ).text() + ' ' + $xml.find( 'ship_adr_city' ).text() + '</span><br />' );
						td.append( text );
						text = $( '<span class="p_15 address">' + $xml.find( 'ship_adr_country' ).text() + '</span><br />' );
						td.append( text );
						text = $( '<a class="change_link address" href="<?php echo PATHLANG . tl( 664, 'alias' );?>"><?php echo t( 'ändern' );?></a><br />' );
						td.append( text );
					}
				tr.append( td );
			table.append( tr );
			
			// payment, shipping
			tr = $( '<tr></tr>' );
				td = $( '<td class="payship_td"></td>' );
					text = $( '<p class="p_16_b"><?php echo t( 'Zahlung' );?></p>' );
					td.append( text );
					text = $( '<span class="p_15">' + $xml.find( 'payment' ).text() + '</span><br />' );
					td.append( text );
					text = $( '<a class="change_link" href="<?php echo PATHLANG . tl( 665, 'alias' );?>"><?php echo t( 'ändern' );?></a><br />' );
					td.append( text );
				tr.append( td );
				td = $( '<td class="payship_td"></td>' );
					text = $( '<p class="p_16_b"><?php echo t( 'Versand' );?></p>' );
					td.append( text );
					text = $( '<span class="p_15">' + $xml.find( 'shipping' ).text() + '</span><br />' );
					td.append( text );
					text = $( '<a class="change_link" href="<?php echo PATHLANG . tl( 666, 'alias' );?>"><?php echo t( 'ändern' );?></a><br />' );
					td.append( text );
				tr.append( td );
				td = $( '<td class="address_td"></td>' );
				tr.append( td );
			table.append( tr );
		
		$( '#cart_div' ).append( table );
		
		// item-table
		table = $( '<table style="vertical-align: middle;"></table>' );
			
			tr = $( '<tr></tr>' );
				th = ( '<th class="header_item_left" colspan="2"><?php echo t( 'Artikelbeschreibung' );?></th>' );
				tr.append( th );
				th = ( '<th class="header_item_right"><?php echo t( 'Menge' );?></th>' );
				tr.append( th );
				th = ( '<th class="header_item_right"><?php echo t( 'Einzelpreis' );?></th>' );
				tr.append( th );
				th = ( '<th class="header_item_right"><?php echo t( 'Gesamtpreis' );?></th>' );
				tr.append( th );
			table.append( tr );
			
			// items
			$xml.find( 'Item' ).each(function()
			{
				if ( $( this ).find( 'OrderItemItemID' ).text() != '28093' )
				{
					var $img_path = '';
					var $shop_item = this;
					
					$( $shop_item ).find( 'ItemPicture' ).each(function()
					{
						if ( $( this ).attr( 'imageformat_id' ) == '19' )
						{
							$img_path = $( this ).find( 'ItemPictureFilePath' ).text();
						}
					});
					
					tr = $( '<tr></tr>' );
						td = $( '<td class="item_td"><img src="' + $( $shop_item ).find( 'ItemThumbSrc' ).text() + '" width="60" height="auto"></td>' ); // picture
/*						
						if ( $img_path != '' )
						{
//							td = $( '<td class="item_td"><img src="<?php echo PATH;?>files/' + $img_path + '" width="60" height="auto"></td>' ); // picture
							td = $( '<td class="item_td"><img src="' + $( $shop_item ).find( 'ItemThumbSrc' ).text() + '" width="60" height="auto"></td>' ); // picture
						}
						else
						{
							td = $( '<td></td>' );
						}
*/						
						tr.append( td );

						if ( $( $shop_item ).find( 'orderItemTotalCollateralPriceNetFC' ).text() != '0,00' ) // title
						{
							if ( $xml.find( 'Order' ).attr( 'type' ) == 'business' )
							{
								td = $( '<td class="item_td">' + $( $shop_item ).find( 'OrderItemDesc' ).text() + '<br /><span class="collateral"><?php echo t( 'Diese Bestellposition beinhaltet Altteilpfand' )?>: ' + $( $shop_item ).find( 'orderItemTotalCollateralPriceNetFC' ).text() + ' ' + $( $shop_item ).find( 'OrderItemCurrency_Code' ).text() + ' (' + $( $shop_item ).find( 'OrderItemAmount' ).text() + ' <?php echo t( 'Artikel' );?>)<br /><?php echo t( 'Wird nach Erhalt des Altteils zurückerstattet' );?>.</span></td>' );
							}
							else
							{
								td = $( '<td class="item_td">' + $( $shop_item ).find( 'OrderItemDesc' ).text() + '<br /><span class="collateral"><?php echo t( 'Diese Bestellposition beinhaltet Altteilpfand' )?>: ' + $( $shop_item ).find( 'orderItemTotalCollateralPriceGrossFC' ).text() + ' ' + $( $shop_item ).find( 'OrderItemCurrency_Code' ).text() + ' (' + $( $shop_item ).find( 'OrderItemAmount' ).text() + ' <?php echo t( 'Artikel' );?>)<br /><?php echo t( 'Wird nach Erhalt des Altteils zurückerstattet' );?>.</span></td>' );
							}
						}
						else
						{
							td = $( '<td class="item_td">' + $( $shop_item ).find( 'OrderItemDesc' ).text() + '</td>' );
						}
						tr.append( td );
						td = $( '<td class="numeric_td">' + $( $shop_item ).find( 'OrderItemAmount' ).text() + '</td>' ); // amount
						tr.append( td );
						
						if ( $xml.find( 'Order' ).attr( 'type' ) == 'business' )
						{
							td = $( '<td class="numeric_td">' + $( $shop_item ).find( 'orderItemPriceNetFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</td>' ); // item
						}
						else
						{
							td = $( '<td class="numeric_td">' + $( $shop_item ).find( 'orderItemPriceGrossFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</td>' );
						}
						tr.append( td );
						if ( $xml.find( 'Order' ).attr( 'type' ) == 'business' )
						{
							td = $( '<td class="numeric_td">' + $( $shop_item ).find( 'orderItemTotalNetFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</td>' ); // position
						}
						else
						{
							td = $( '<td class="numeric_td">' + $( $shop_item ).find( 'orderItemTotalGrossFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</td>' );
						}
						tr.append( td );
						
					table.append( tr );
				}
			});
			
			// shipping and sums
			tr = $( '<tr></tr>' );
				td = $( '<td class="sum_td" colspan="4"><span class="address"><?php echo t( 'Versandkosten' );?></span><span class="p_15 address">' + $xml.find( 'shipping' ).text() + '</span></td>' );
				tr.append( td );
				if ( $xml.find( 'Order' ).attr( 'type' ) == 'business' )
				{
					td = $( '<td class="numeric_td">' + $xml.find( 'shippingCostsNetFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</td>' );
				}
				else
				{
					td = $( '<td class="numeric_td">' + $xml.find( 'shippingCostsGrossFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</td>' );
				}
				tr.append( td );
			table.append( tr );
			
			// netto sum
			if ( $xml.find( 'Order' ).attr( 'type' ) == 'business' )
			{
				tr = $( '<tr></tr>' );
					td = $( '<td class="sum_td" colspan="4"><span class="p_15_b address"><?php echo t( 'Nettogesamtwert' );?></span></td>' );
					tr.append( td );
					td = $( '<td class="numeric_td">' + $xml.find( 'orderTotalNetFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</td>' );
					tr.append( td );
				table.append( tr );
			}
			
			// total
			tr = $( '<tr></tr>' ); 
				if ( $xml.find( 'Order' ).attr( 'type' ) == 'business' )
				{
					if ( $xml.find( 'VAT' ).text() != 0 )
					{
						td = $( '<td class="sum_td" colspan="4"><span class="p_16_b address"><?php echo t( 'Gesamtpreis' );?></span><br /><span class="address p_14"><?php echo t( 'inklusive' );?> ' + $xml.find( 'VAT' ).text() + '% <?php echo t( 'Mehrwertsteuer' );?></span></td>' );
					}
					else
					{
						td = $( '<td class="sum_td" colspan="4"><span class="p_16_b address"><?php echo t( 'Gesamtpreis' );?></span></td>' );
					}
					tr.append( td );
					if ( $xml.find( 'VAT' ).text() != 0 )
					{
						td = $( '<td class="numeric_td"><span class="p_16_b">' + $xml.find( 'orderTotalGrossFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</span><br /><span class="p_14">' + $xml.find( 'orderTotalTaxFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</span></td>' );
					}
					else
					{
						td = $( '<td class="numeric_td"><span class="p_16_b">' + $xml.find( 'orderTotalGrossFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</span></td>' );
					}
					tr.append( td );
				}
				else
				{
					if ( $xml.find( 'VAT' ).text() != 0 )
					{
						td = $( '<td class="sum_td" colspan="4"><span class="p_16_b address"><?php echo t( 'Gesamtpreis' );?></span><br /><span class="address p_14"><?php echo t( 'inklusive' );?> ' + $xml.find( 'VAT' ).text() + '% <?php echo t( 'Mehrwertsteuer' );?></span></td>' );
					}
					else
					{
						td = $( '<td class="sum_td" colspan="4"><span class="p_16_b address"><?php echo t( 'Gesamtpreis' );?></span></td>' );
					}
					tr.append( td );
					if ( $xml.find( 'VAT' ).text() != 0 )
					{
						td = $( '<td class="numeric_td"><span class="p_16_b">' + $xml.find( 'orderTotalGrossFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</span><br /><span class="p_14">' + $xml.find( 'orderTotalTaxFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</span></td>' );
					}
					else
					{
						td = $( '<td class="numeric_td"><span class="p_16_b">' + $xml.find( 'orderTotalGrossFC' ).text() + ' ' + $xml.find( 'Currency_Code' ).text() + '</span></td>' );
					}
					tr.append( td );
				}
			table.append( tr );
		
		$( '#cart_div' ).append( table );
		
		table = $( '<table></table>' );
		
		
		// AGBs
		tr = $( '<tr></tr>' );
			td = $( '<td id="agb_td"></td>' );
				text = $( '<input id="agb_checkbox" type="checkbox" />' );
				td.append( text );	
				text = $( '<a id="agb_text" href="<?php echo PATHLANG . tl( 39, 'alias' );?>"><?php echo t( 'Hiermit erkläre ich mich mit den Allgemeinen Geschäftsbedingungen einverstanden.' );?></a>' );
				td.append( text );					
			tr.append( td );
		table.append( tr );
		
		$( '#cart_div' ).append( table );
		
		table = $( '<table></table>' );
		
		// send order button
		tr = $( '<tr></tr>' );
			td = $( '<td colspan="5"></td>' );
				div = $( '<div class="order_button_div" title="<?php echo t('Kostenpflichtig bestellen');?>"><?php echo t('Kostenpflichtig bestellen');?></div>' );
				td.append( div );
			tr.append( td );
		table.append( tr );
		
		$( '#cart_div' ).append( table );
		
		$( '#agb_checkbox' ).click(function()
		{
			agb_set();
		});
		
		$( '.order_button_div' ).click(function()
		{
			order_send();
		});
	}

	function session_show()
	{
		show_status2( '<?php echo str_replace("\n","",print_r( $_SESSION, true )); ?>' );
//		show_status2( '<?php echo str_replace("\n","",print_r( $_SERVER, true )); ?>' );
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
/*		
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
*/		
		if ( $checkout_order_id_set == 1 && $checkout_order_id == 0 )
		{
			$( '#cart_div' ).append( '<h1><?php echo t( 'In Ihrem Warenkorb befinden sich keine Artikel' );?></h1>' );
			checkout_order_id_unset();
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
			$postdata['checkout_order_id'] = 	<?php if ( isset( $_SESSION['checkout_order_id'] ) ) { echo $_SESSION['checkout_order_id']; } else { echo 0; }?>;
			
			soa2( $postdata, 'checkout_integrity_check_callback' );
		}
	}
	
</script>
		
<?php
	
	echo '<div id="main_div"></div>';
	echo '<div id="message"></div>';
	include("templates/".TEMPLATE."/footer.php");
	
?>
