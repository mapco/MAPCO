<?php

    include("config.php");
//	include("functions/cms_core.php");
	include("functions/mapco_gewerblich.php");

    // hide menu
    $menu_hide = true;
    include("templates/".TEMPLATE."/header.php");
	
	$userId = 0;
	if ( isset( $_SESSION['id_user'] ) && !empty( $_SESSION['id_user'] ) )
	{
		$userId = $_SESSION['id_user'];
	}
	if ( isset( $_SESSION['checkout_user_id'] ) && !empty( $_SESSION['checkout_user_id'] ) )
	{
		$userId = $_SESSION['checkout_user_id'];
	}
	
	$gewerblich = 0;
	if ( gewerblich($userId) )
	{
		$gewerblich = 1;
	}
	
	$checkout_order_id = 0;
	if ( isset( $_SESSION['checkout_order_id'] ) and $_SESSION['checkout_order_id'] > 0 )
	{
		$checkout_order_id = $_SESSION['checkout_order_id'];
	}
	
	//	keep important session vars for the user
    $userData = array(
        'userId' => $userId,
		'shopId' => $_SESSION['id_shop'],
		'orderId' => $checkout_order_id,
		'shipCountryId' => $_SESSION['ship_country_id'],
		'billCountryId' => $_SESSION['bill_country_id']
    );
?>
	
<script type="text/javascript">

	var $payment_type = 0;
	
	function address_dialog_show( $message )
	{		
		$( "#message" ).html( $message );
		$( "#message" ).dialog
		({	buttons:
			[
				{ text: "<?php echo t("Ok"); ?>", click: function() 
					{
						$(this).dialog("close");
						location.href = '<?php echo PATHLANG . tl( 664, 'alias' );?>';
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
/*	
	function buttonsShopPaymentsMethods($xml)
	{
		
		var paymentId;
		var paymentTitle;
		var paymentItem;
		var paymentLogo;
		var paymentButton = $('<div class="payment-button"></div>');
		$('#shopcard-payment-list').append(paymentButton);
		$xml.find('shopPayment').each(function()
		{
			paymentId = $(this).find('id_payment').text();
			paymentTitle = $(this).find('payment').text();
			paymentLogo = '<img src="http://www.mapco.de/images/payment_options/' + paymentTitle + '.png"><br />';
			paymentItem = '<button class="btn-payment" id="setorder-payment-' + paymentId + '" value="' + paymentId + '">' + paymentLogo + paymentTitle + '</button>';
			
			paymentButton.append(paymentItem);
			ShopPaymentMethodSelect(paymentId);
		});
		
	}	 
*/
	function order_payment_set( $type )
	{
		$payment_type = $type;
		
		$( '.pay_button' ).removeClass( 'payment_div' );		
		$( '.pay_button' ).removeClass( 'payment_div_active' );
		$( '.pay_button' ).addClass( 'payment_div' );
		$( '#payment_' + $type ).removeClass( 'payment_div' );
		$( '#payment_' + $type ).addClass( 'payment_div_active' );
		
		wait_dialog_show();
		
		$post_data = 					new Object();
		$post_data['API'] = 			'shop';
		$post_data['APIRequest'] = 		'CheckoutPayment';
		$post_data['mode'] = 			'set';
		$post_data['payment_type'] = 	$type;	
		
		soa2( $post_data, 'order_payment_set_callback' );
	}
	
	function order_payment_set_callback( $xml )
	{
//		alert( 'set!' );
	}
	
	function payment_ready()
	{
		$post_data = 				new Object();
		$post_data['API'] = 		'shop';
		$post_data['APIRequest'] = 	'CheckoutIntegrityCheck';
		
		$post_data['checkout_order_id'] = <?php echo $checkout_order_id;?>;
		
		wait_dialog_show();
			
		soa2( $post_data, 'payment_ready_callback' );
//		soa2( $post_data, 'payment_ready_callback', 'xml' );
	}
	
	function payment_ready_callback( $xml )
	{
//		alert( $xml );
		
		if ( $xml.find( 'checkout_adr_edit' ).text() == 1 )
		{
			address_dialog_show( '<?php echo t( 'Sie müssen noch eine Rechnungsadresse auswählen' );?>!' );
		}
		else if ( $xml.find( 'checkout_payment_edit' ).text() == 1 )
		{
			location.href = '<?php echo PATHLANG . tl( 665, 'alias' );?>';
		}
		else if ( $xml.find( 'checkout_shipping_edit' ).text() == 1 )
		{
			location.href = '<?php echo PATHLANG . tl( 666, 'alias' );?>';
		}
		else
		{
			location.href = '<?php echo PATHLANG . tl( 667, 'alias' );?>';
		}
		
	}
	
	function ShopPaymentMethods(userId, orderId)
	{
		if (userId == "") { return; }
		
		$( '.go_on_button_div' ).click(function()
		{
			payment_ready();
		});
		
		wait_dialog_show();
		
		var $post_data = 			new Object();
		$post_data['API'] = 		'shop';
		$post_data['APIRequest'] = 	'CheckoutPayment';
		$post_data['mode'] = 		'get';
		
		soa2( $post_data, 'ShopPaymentMethodsCallback' );
//		soa2( $post_data, 'ShopPaymentMethodsCallback', 'xml' );
		
/*		
		var $post_data = new Object();
		$post_data['API'] = "shop";
		$post_data['APIRequest'] = "CheckoutPayment";
		$post_data['action'] = 'ShopPayment';
		$post_data['OrderID'] = orderId;
		$post_data['customer_id'] = userId;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			buttonsShopPaymentsMethods($xml);
		});
*/		
	}
	
	
	function ShopPaymentMethodsCallback( $xml )
	{
		var $div;
		var $payment_ids = new Array();
	
		$div = $( '<div><span id="payments_label"><?php echo t( 'Bitte wählen Sie eine Zahlungsart aus' );?>:</span></div>' );
		$( '#shopcard-payment-list' ).append( $div );
		$xml.find( 'shop_payment' ).each(function()
		{
			if ( <?php echo $gewerblich;?> == 1 || ( <?php echo $gewerblich;?> == 0 && $( this ).find( 'paymenttype_id' ).text() != '1' ) )
			{			
				$div = $( '<div class="pay_button payment_div" id="payment_' + $( this ).find( 'paymenttype_id' ).text() + '">' + $( this ).find( 'payment' ).text() + '</div>' );
				$( '#shopcard-payment-list' ).append( $div );
				$payment_ids.push( $( this ).find( 'paymenttype_id' ).text() );
				
				if ( $( this ).find( 'actual_payment' ).text() == '1' )
				{
					$( '#payment_' + $( this ).find( 'paymenttype_id' ).text() ).removeClass( 'payment_div' );
					$( '#payment_' + $( this ).find( 'paymenttype_id' ).text() ).addClass( 'payment_div_active' );
				}
			}
		});
		
		for( var n in $payment_ids)
		{
			(function(k)
			{
				$("#payment_" + k).click(function()
				{
					order_payment_set( k );
				});
			})( $payment_ids[n] );
		}
		
		if ( $xml.find( 'num_payments' ).text() == '0' )
		{
			address_dialog_show( '<?php echo t( 'Zu Ihrem Zielland (Rechnungsadresse) gibt es keine Zahlungsmöglichkeit. Bitte wählen Sie ein anderes Land oder setzen Sie sich mit uns in Verbindung.' );?>' );
		}
	}
/*	
	function ShopPaymentMethodSelect(paymentId)
	{
		
		$('#setorder-payment-' + paymentId).bind("click", function() {
			var $post_data = new Object();
			$post_data['API'] = "shop";
			$post_data['APIRequest'] = "CheckoutPayment";
			$post_data['action'] = 'SetShopPaymentMethod';
			$post_data['paymentId'] = paymentId;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				$('#shopcard-shipping-list').empty();
				document.location.href = '/online-shop/versandart/';
			});
		});
	
	}

	function widgetShopPaymentsMethods($xml)
	{
		
		var widgetSelect = $('<select id="form-field-payment-id" class="form-control" onchange="PaymentSelect();"><select>');
		var widgetLabel = $('<label for="form-field-payment-method">Zahlungsart festlegen</label>');
		var widgetInputGroup = $('<div class="input-group"></div>');
		var paymentSelect = $('<div class="field-wrapper"></div>');
		var widgetItem;
		
		widgetSelect.append('<option>...bitte wählen</option>');
		$xml.find('shopPayment').each(function()
		{
			widgetItem = '<option value="' + $(this).find('id_payment').text() + '">' + $(this).find('payment').text() + '</option>';
			widgetSelect.append(widgetItem);
		});
		widgetInputGroup.append(widgetSelect);
		$('#shopcard-payment-list').append(widgetLabel);
		$('#shopcard-payment-list').append(widgetInputGroup);
		$('#shopcard-payment-list').append(paymentSelect);
	
	}
*/	
</script>
	
<?php    
	//	payment types
	echo '
	<div id="form-add-payment" class="row">
		<div id="shopcard-payment-shipping" class="shopcard-wrapper">
			<h1 id="shopcard_title" class="shopcard-heading">' . t( 'Zahlungsart' ) . '</h1>
			
			<div class="widget-shopcard-assistent">
				<ul>
					<li><em>1.</em> ' . t( 'Adresse' ) . '</li>
					<li class="active"><em>2.</em> ' . t( 'Zahlungsart' ) . '</li>
					<li><em>3.</em> ' . t( 'Versandart' ) . '</li>
					<li><em>4.</em> ' . t( 'Bestellübersicht' ) . '</li>
				</ul>
				<div class="clear"></div>
			</div>
			
			<div class="shopcard-field-address">
			
				<div class="field-wrapper">
					<div id="shopcard-payment-list"></div>
					
					<hr />
					
					<div class="go_on_button_div" title="' . t( 'Weiter' ) . '">' . t( 'Weiter' ) . ' >></div>
					<div class="clear"></div>
				</div>
			</div>
				
		</div>
	</div>';
	
	echo '<div id="message"></div>';
	
	//	loading....
//	echo '<script src="' . PATH . 'javascript/shop/Payments.php" type="text/javascript"><s/script>';
	echo '<script type="text/javascript">ShopPaymentMethods(' . $userData['userId'] . ', ' . $userData['orderId'] . ');</script>';
	include("templates/".TEMPLATE."/footer.php");