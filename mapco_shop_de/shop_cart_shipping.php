<?php

    include("config.php");
//	include("functions/cms_core.php");
	include("functions/mapco_gewerblich.php");

    // hide menu
    $menu_hide = false;
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
		
	//	keep important session and post vars for the user
    $userData = array(
        'userId' => $userId,
		'shopId' => $_SESSION['id_shop'],
		'orderId' => $checkout_order_id,
		'paymentId' => $_SESSION['paymentId'],
		'shipCountryId' => $_SESSION['ship_country_id'],
		'billCountryId' => $_SESSION['bill_country_id']
    );
?>
	
<script type="text/javascript">

	var $shipping_type = 	0;
	var $shipping_id = 		0;
	
/*
	function buttonsShopShippingsMethods($xml)
	{
		var shippingId;
		var shippingTitle;
		var shippingItem;
		var shippingSelect = $('<div class="field-wrapper"></div>');
		var shippingButton = $('<div class="shipping-button"></div>');
		$('#shopcard-shipping-list').append(shippingButton);
		$xml.find('shopShipping').each(function()
		{
			shippingId = $(this).find('id_shipping').text();
			shippingTitle = $(this).find('shipping').text();
			shippingItem = '<button class="btn-shipping" id="setorder-shipping-' + shippingId + '" value="' + shippingId + '">' + shippingTitle + '</button>';
			
			shippingButton.append(shippingItem);
			ShopShippingMethodSelect(shippingId);
		});
	}
*/	
	
	function order_shipping_set( $id )
	{
//		alert( 'set id: ' + $id );
		
//		$shipping_type = $type;
		$shipping_id = $id;
				
		$( '.ship_button' ).removeClass( 'shipping_div' );		
		$( '.ship_button' ).removeClass( 'shipping_div_active' );
		$( '.ship_button' ).addClass( 'shipping_div' );
		$( '#shipping_' + $id ).removeClass( 'shipping_div' );
		$( '#shipping_' + $id ).addClass( 'shipping_div_active' );	
		
		wait_dialog_show();
		
		$post_data = 					new Object();
		$post_data['API'] = 			'shop';
		$post_data['APIRequest'] = 		'CheckoutShipping';
		$post_data['mode'] = 			'set';
		$post_data['shipping_id'] = 	$id;	
		
//		soa2( $post_data, 'order_shipping_set_callback' );
		soa2( $post_data, 'order_shipping_set_callback', 'xml' );		
	}
	
	function order_shipping_set_callback( $xml )
	{
//		alert( $xml );
	}
	
	function payment_dialog_show( $message )
	{		
		$( "#message" ).html( $message );
		$( "#message" ).dialog
		({	buttons:
			[
				{ text: "<?php echo t("Ok"); ?>", click: function() 
					{
						$(this).dialog("close");
						location.href = '<?php echo PATHLANG . tl( 665, 'alias' );?>';
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
	
	function shipping_ready()
	{
		$post_data = 				new Object();
		$post_data['API'] = 		'shop';
		$post_data['APIRequest'] = 	'CheckoutIntegrityCheck';
		
		$post_data['checkout_order_id'] = <?php echo $checkout_order_id;?>;
		
		wait_dialog_show();
			
		soa2( $post_data, 'shipping_ready_callback' );
//		soa2( $post_data, 'shipping_ready_callback', 'xml' );
	}
	
	function shipping_ready_callback( $xml )
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
	
//	function ShopShippingMethods(userId, orderId, paymentId)
	function ShopShippingMethods()
	{
		if (<?php echo $userId;?> == "") { return; }
		
		$( '.go_on_button_div' ).click(function()
		{
			shipping_ready();
		});
		
		wait_dialog_show();
		
		var $post_data = 			new Object();
		$post_data['API'] = 		'shop';
		$post_data['APIRequest'] = 	'CheckoutShipping';
		$post_data['mode'] = 		'get';
		
		soa2( $post_data, 'ShopShippingMethodsCallback' );
//		soa2( $post_data, 'ShopShippingMethodsCallback', 'xml' );
		
/*		
		var $post_data = new Object();
		$post_data['API'] = "shop";
		$post_data['APIRequest'] = "CheckoutShipping";
		$post_data['action'] = 'ShopShipping';
		$post_data['OrderID'] = orderId;
		$post_data['PaymentID'] = paymentId;
		$post_data['customer_id'] = userId;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			if ($xml.find('Jump').text() == 'Success') {
				$('#shopcard-payment-shipping').empty();
				document.location.href = '/online-shop/kasse/';
			} else {
				buttonsShopShippingsMethods($xml);
			}
		});
*/		
	}
	
	function ShopShippingMethodsCallback( $xml )
	{
		
//		show_status2( $xml );
//		return;
		
		var $div;
		var $shipping_ids = new Array();
	
		$div = $( '<div><span id="shippings_label"><?php echo t( 'Bitte wählen Sie eine Versandart aus' );?>:</span></div>' );
		$( '#shopcard-shipping-list' ).append( $div );
		
		$xml.find( 'shop_shipping' ).each(function()
		{
			if ( $( this ).find( 'price_show' ).text() == 'net' )
			{
				$div = $( '<div class="ship_button shipping_div" id="shipping_' + $( this ).find( 'id_shipping' ).text() + '">' + $( this ).find( 'shipping' ).text() + '<br /><p class="shipping_costs">( </p><p class="shipping_costs red">' + $( this ).find( 'price' ).text() + ' €</p><p class="shipping_costs"> )</p><span class="shipping_memo">' + $( this ).find( 'shipping_memo' ).text() + '</span></div>' );
			}
			if ( $( this ).find( 'price_show' ).text() == 'gross' )
			{
				$div = $( '<div class="ship_button shipping_div" id="shipping_' + $( this ).find( 'id_shipping' ).text() + '">' + $( this ).find( 'shipping' ).text() + '<br /><p class="shipping_costs">( </p><p class="shipping_costs red">' + $( this ).find( 'price_gross' ).text() + ' €</p><p class="shipping_costs"> )</p><span class="shipping_memo">' + $( this ).find( 'shipping_memo' ).text() + '</span></div>' );
			}
			$( '#shopcard-shipping-list' ).append( $div );
			$shipping_ids.push( $( this ).find( 'id_shipping' ).text() );
			
			if ( $( this ).find( 'actual_shipping' ).text() == '1' )
			{
				$( '#shipping_' + $( this ).find( 'id_shipping' ).text() ).removeClass( 'shipping_div' );
				$( '#shipping_' + $( this ).find( 'id_shipping' ).text() ).addClass( 'shipping_div_active' );
			}			
		});
		
		
		for( var n in $shipping_ids)
		{
			(function(k)
			{
				$("#shipping_" + k).click(function()
				{
					order_shipping_set( k );
				});
			})( $shipping_ids[n] );
		}
		
		if ( $xml.find( 'num_shippings' ).text() == '0' )
		{
			payment_dialog_show( '<?php echo t( 'Zu Ihrem Zahlungsmethode gibt es keine Versandart. Bitte wählen Sie eine andere Zahlungsmethode oder setzen Sie sich mit uns in Verbindung.' );?>' );
		}
	}
	
/*	
	function ShopShippingMethodSelect(shippingId)
	{
		$('#setorder-shipping-' + shippingId).bind("click", function() {
			var $post_data = new Object();
			$post_data['API'] = "shop";
			$post_data['APIRequest'] = "CheckoutShipping";
			$post_data['action'] = 'SetShopShippingMethod';
			$post_data['shippingId'] = shippingId;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				$('#shopcard-shipping-list').empty();
				document.location.href = '/online-shop/kasse/';
			});
		});
	}	 
	 
	function widgetShopShippingMethods($xml)
	{
		var widgetSelect = $('<select id="form-field-shipping-id" class="form-control" onchange="ShippingSelect();"><select>');
		var widgetLabel = $('<label for="form-field-payment-method">Versandart festlegen</label>');
		var widgetInputGroup = $('<div class="input-group"></div>');
		var shippingSelect = $('<div class="field-wrapper"></div>');
		var widgetItemPrice = $('<div class="shipping-price"></div>');
		var widgetItemMemo = $('<div class="shipping-memo"></div>');
		var widgetItem;
		$xml.find('shopShipping').each(function()
		{
			widgetItem = '<option value="' + $(this).find('id_shipping').text() + '">' + $(this).find('shipping').text() + '</option>';
			widgetSelect.append(widgetItem);
		});
		widgetInputGroup.append(widgetSelect);
		$('#shopcard-shipping-list').append(widgetLabel);
		$('#shopcard-shipping-list').append(widgetInputGroup);
		$('#shopcard-shipping-list').append(shippingSelect);		
	}
*/		
</script>

<?php
	//	shipping methods
	echo '
	<div id="form-add-shipping" class="row">
		<div id="shopcard-payment-shipping" class="shopcard-wrapper">
			<h1 id="shopcard_title" class="shopcard-heading">' . t( 'Versandart' ) . '</h1>
			
			<div class="widget-shopcard-assistent">
				<ul>
					<li><em>1.</em> ' . t( 'Adresse' ) . '</li>
					<li><em>2.</em> ' . t( 'Zahlungsart' ) . '</li>
					<li class="active"><em>3.</em> ' . t( 'Versandart' ) . '</li>
					<li><em>4.</em> ' . t( 'Bestellübersicht' ) . '</li>
				</ul>
				<div class="clear"></div>
			</div>
			
			<div class="shopcard-field-address">
			
				<div class="field-wrapper">
					<div id="shopcard-shipping-list"></div>
				</div>
				
				<hr>
				
				<div class="go_on_button_div" title="' . t( 'Weiter' ) . '">' . t( 'Weiter' ) . ' >></div>
				<div class="clear"></div>
				
			</div>
				
		</div>
	</div>';
	
	echo '<div id="message"></div>';

	//	loading....
//	echo '<script src="' . PATH . 'javascript/shop/Shippings.php" type="text/javascript"></sxcript>';
//	echo '<script type="text/javascript">ShopShippingMethods(' . $userData['userId'] . ', ' . $userData['orderId'] . ', ' . $userData['paymentId'] . ');<x/script>';
	echo '<script type="text/javascript">ShopShippingMethods();</script>';
	include("templates/".TEMPLATE."/footer.php");