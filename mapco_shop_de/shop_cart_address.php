<?php

    include("config.php");

    // hide menu
    $menu_hide = true;
	
    include("templates/".TEMPLATE."/header.php");
	
	$userId = 0;
	if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user']))
	{
		$userId = $_SESSION['id_user'];
	}
	if (isset($_SESSION['checkout_user_id']) && !empty($_SESSION['checkout_user_id']))
	{
		$userId = $_SESSION['checkout_user_id'];
	}
	
	$checkout_guest = 0;
	if ( isset( $_SESSION['checkout_guest'] ) )
	{
		$checkout_guest = 1;
	}
	
	$checkout_order_id = 0;
	if ( isset( $_SESSION['checkout_order_id'] ) and $_SESSION['checkout_order_id'] > 0 )
	{
		$checkout_order_id = $_SESSION['checkout_order_id'];
	}
	
    //	keep important session vars for the user 
    $userData = array(
		'ship_country_id' => 	$_SESSION['ship_country_id'],
        'userId' => 			$userId,
		'shopId' => 			$_SESSION['id_shop'],
		'orderId' => 			$checkout_order_id,
		'checkoutGuestOrder' => $_SESSION['ckeckout_guest']
    );
?>

<script type="text/javascript">
	
	// global variables
	var $adr_type = 	''; // bill or ship
	var $bill_adr_id = 	0;
	var $ship_adr_id = 	0;
	
	function address_dialog_show( $message )
	{		
		$( "#message" ).html( $message );
		$( "#message" ).dialog
		({	buttons:
			[
				{ text: "<?php echo t("Ok"); ?>", click: function() 
					{
						$(this).dialog("close");
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
	 
	function address_ready()
	{
		$post_data = 				new Object();
		$post_data['API'] = 		'shop';
		$post_data['APIRequest'] = 	'CheckoutIntegrityCheck';
		
		$post_data['checkout_order_id'] = <?php echo $_SESSION['checkout_order_id'];?>;
		
		wait_dialog_show();
			
		soa2( $post_data, 'address_ready_callback' );
	}
	
	function address_ready_callback( $xml )
	{
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
	
	function guest_user()
	{
//		alert('guest_user');
		wait_dialog_show();
		
		var $post_data = 			new Object();
		$post_data['API'] = 		"shop";
		$post_data['APIRequest'] = 	"CheckoutGuestUser";

		$post_data['usermail'] = 	$("#form-field-bill-usermail").val();
		$post_data['firstname'] = 	$("#form-field-bill-firstname").val();
		$post_data['lastname'] = 	$("#form-field-bill-lastname").val();
		$post_data['street'] = 		$("#form-field-bill-street").val();
		$post_data['number'] = 		$("#form-field-bill-number").val();
		$post_data['zip'] = 		$("#form-field-bill-zip").val();
		$post_data['city'] = 		$("#form-field-bill-city").val();
		$post_data['country_id'] = 	$("#form-field-bill-country_id").val();	
		
		soa2( $post_data, 'guest_user_callback' );	
	}
	 
	function guest_user_callback( $xml )
	{
//		alert('666');
		address_ready();
	}
	 
	function order_adr_set( $adr_id )
	{
		wait_dialog_show();
		
		$post_data = 				new Object();
		$post_data['API'] = 		'shop';
		$post_data['APIRequest'] = 	'OrderAddressSet';
		
		$post_data['OrderID'] = 	<?php echo $_SESSION['checkout_order_id'];?>;
		$post_data['customer_id'] = <?php echo $userId;?>;
		$post_data['addresstype'] = $adr_type;
		$post_data['adr_id'] = 		$adr_id;
		
		soa2( $post_data, 'order_adr_set_callback' );		
	}
	
	function order_adr_set_callback( $xml )
	{
		AddressBillList( <?php echo $userId;?>, <?php echo $_SESSION['id_shop'];?>, <?php echo $_SESSION['checkout_order_id'];?> );
		$( "#shopcard-address-list" ).hide();
		$( "#shopcard-address-list-current" ).show();
	}
/*	 
	function order_bill_adr_set( adrId, shopId, orderId, userId )
	{
		alert( 'Rechnungsadresse setzen' );
		return;
		
		$('#setorder-billing-' + adrId).click(function(e) {
			var $post_data = new Object();
			$post_data['API'] = "shop";
			$post_data['APIRequest'] = "OrderAddressUpdate_test";

			$post_data['adrId'] = adrId;
			$post_data['addresstype'] = 'bill';
			$post_data['OrderID'] = orderId;
			$post_data['customer_id'] = userId;
			$post_data['shop_id'] = shopId;
			$post_data['country_id'] = 1;
			$post_data['country'] = 'Deutschland';
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				document.location.href = '/online-shop/adressen/';
			});
		});
	}

	function order_ship_adr_set(adrId, shopId, orderId, userId)
	{
		alert( 'Lieferadresse setzen' );
		return;
		
		$('#setorder-shipping-' + adrId).click(function(e) {
			var $post_data = new Object();
			$post_data['API'] = "shop";
			$post_data['APIRequest'] = "OrderAddressUpdate_test";

			$post_data['adrId'] = adrId;
			$post_data['addresstype'] = 'ship';
			$post_data['OrderID'] = orderId;
			$post_data['customer_id'] = userId;
			$post_data['shop_id'] = shopId;
			$post_data['country_id'] = 1;
			$post_data['country'] = 'Deutschland';
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				document.location.href = '/online-shop/adressen/';
			});
		});
	}
*/
	function SetAddressBillingStandard( adrId, userId )
	{
		
		if ( $( "#form-field-billing-standard:checked" ).val() === undefined )
		{
			var bill_standard = 0;
		}
		else
		{
			var bill_standard = 1;
		}
		
		var $post_data = 				new Object();
		$post_data['API'] = 			"shop";
		$post_data['APIRequest'] = 		"AddressBill";
		$post_data['action'] = 			'set-standard-billing';
//		$post_data['user_id'] = 		userId;
		$post_data['adr_id'] = 			adrId;
		$post_data['bill_standard'] = 	bill_standard;
		
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data)
		{
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			//document.location.href = '/online-shop/adressen/';
			AddressBillList( userId, adrId, <?php echo $_SESSION['checkout_order_id'];?> );
		});
		
	}

	function SetAddressShippingStandard(adrId, userId)
	{
		if ( $( "#form-field-shipping-standard:checked" ).val() === undefined )
		{
			var bill_standard_ship_adr = 0;
		}
		else
		{
			var bill_standard_ship_adr = 1;
		}
		
		var $post_data = 						new Object();
		$post_data['API'] = 					"shop";
		$post_data['APIRequest'] = 				"AddressBill";
		$post_data['action'] = 					'set-standard-shipping';
//		$post_data['user_id'] = 				userId;
		$post_data['adr_id'] = 					adrId;
		$post_data['bill_standard_ship_adr'] = 	bill_standard_ship_adr;
		
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
//			document.location.href = '/online-shop/adressen/';
			AddressBillList( userId, <?php echo $_SESSION['id_shop'];?>, <?php echo $_SESSION['checkout_order_id'];?> );
		});
		
	}
	
	function ship_adr_choose()
	{
		$adr_type = 'ship';
				
		$( '#address_list_label' ).empty();
		$( '#address_list_label' ).append( 'Lieferadresse auswählen:' );
		
		$( '.address_field' ).removeClass( 'flied-standard-active flied-shipping-active flied-inactive' );
		$( '.address_field' ).addClass( 'flied-inactive' );
		
		if ( $ship_adr_id > 0 )
		{
			$( '#address-bill-' + $ship_adr_id ).removeClass( 'flied-inactive' );
			$( '#address-bill-' + $ship_adr_id ).addClass( 'flied-shipping-active' );
			$( '#address-bill-' + $ship_adr_id ).css( 'cursor', 'default' );
		}
		
		if ( $bill_adr_id > 0 )
		{
			$( '#address-bill-' + $bill_adr_id ).css( 'cursor', 'pointer' );
		}
		
		$( "#shopcard-address-list-current" ).hide();
		$( "#shopcard-address-list" ).show();
	}
	
	function ship_adr_unset()
	{
		wait_dialog_show();
		
		$post_data = 				new Object();
		$post_data['API'] = 		'shop';
		$post_data['APIRequest'] = 	'OrderAddressUnset';
		
		$post_data['OrderID'] = 	<?php echo $_SESSION['checkout_order_id'];?>;
		$post_data['customer_id'] = <?php echo $userId;?>;
		$post_data['addresstype'] = 'ship';
		
		soa2( $post_data, 'ship_adr_unset_callback' );
	}
	
	function ship_adr_unset_callback()
	{
		$ship_adr_id = 0;
		AddressBillList( <?php echo $userId;?>, <?php echo $_SESSION['id_shop'];?>, <?php echo $_SESSION['checkout_order_id'];?> );
	}

	function AddressBillList( userId, shopId, orderId )
	{
		
		if ( <?php echo $checkout_order_id;?> == 0 )
		{
			location.href = '<?php echo PATHLANG . tl( 667, 'alias' );?>';
			return;
		}
		
//		if (userId == "") { return; }
		$( ".address-billing" ).empty();
		$( ".address-shipping" ).empty();
		$( "#address_list" ).empty();
//		$( "#shopcard-address-list-current" ).empty();
		
		var $post_data = 			new Object();
		$post_data['API'] = 		"shop";
		$post_data['APIRequest'] = 	"AddressBill";
		$post_data['action'] = 		'list';
		$post_data['user_id'] = 	userId;
		$post_data['orderId'] = 	orderId;
		$post_data['shopId'] = 		shopId;
		
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) 
		{
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
//			show_status2($data); return;
			
			$xml.find('widgetFieldAddressBillBilling').each(function()
			{
				$bill_adr_id = $(this).find('adr_id').text();
			});
			
			$xml.find('widgetFieldAddressBillShipping').each(function()
			{
				$ship_adr_id = $(this).find('adr_id').text();
			});
			
//			alert('<?php echo  $checkout_guest;?>');
			
			if ( <?php echo  $checkout_guest;?> == 1 )
			{
				$adr_type = 'bill';
				
				$("#list-address").hide();
				$("#add-new-address-cancel").hide();
				$("#form-add-address").show();
			}
			else if ($xml.find('countBillingAddress').text() == 'noBillingData' && $xml.find('countShippingAddress').text() == 'noShippingData' && $xml.find('countOtherAddress').text() == 'noOtherData')
			{
				$adr_type = 'bill';
				
				$("#list-address").hide();
				$("#add-new-address-cancel").hide();
				$("#form-add-address").show();
			}
			else
			{
				$("#add-new-address-cancel").show();	
/*			
				if ($xml.find('countBillingAddress').text() == 'noBillingData' && $xml.find('countShippingAddress').text() != 'noShippingData')
				{
					widgetFieldAddressBoth($xml);
				}
				else
				{
					widgetFieldAddressBilling($xml);
					widgetFieldAddressShipping($xml);
				}
*/				
				widgetFieldAddressBilling($xml);
				widgetFieldAddressShipping($xml);
				
				widgetFieldAddress($xml, shopId, orderId);
/*
				if ($xml.find('countShippingAddress').text() == 'noShippingData') {
					$('.address-shipping').append('<div class="alert alert-warning">Wählen Sie eine Adresse aus Ihrer Liste aus oder tragen Sie eine Neue ein.</div>');
				}

				if ($xml.find('countBillingAddress').text() == 'noBillingData' && $xml.find('countShippingAddress').text() == 'noShippingData' && $xml.find('countOtherAddress').text() != 'noOtherData') {
					$("#shopcard-address-list").show();
				}
*/				
/*				
				$(".address-billing").click(function() {
					$("#shopcard-address-list").toggle('fade');
				});
*/				
/*
				$("#edit-standard").click(function() {
					$("#shopcard-address-list").toggle('fade');
				});
*/
/*				
				$("#edit-shipping").click(function() {
					$("#shopcard-address-list").toggle('fade');
				});
*/				
			}
			
			$("#add-new-address").click(function() {
			$("#form-add-address").show();
			$("#shopcard-address-list").hide();
			});
	
			$("#add-new-address-cancel").click(function() {
				$("#form-add-address").hide();
				$("#shopcard-address-list").show();
			});
			
			$( '.go_on_button_div' ).unbind('click')
			
			$( '.go_on_button_div' ).click(function()
			{
				address_ready();
			});
			
			$( "#new-address-cancel" ).click(function()
			{
				$( "#shopcard-address-list" ).hide();
				$( "#shopcard-address-list-current" ).show();
			});
			
			list_sort( 'form-field-bill-country_id' );
			
			$( '#form-field-bill-country_id' ).val(1);
		});
/*
		$("#add-new-address").click(function() {
			$("#form-add-address").show();
			$("#shopcard-address-list").hide();
		});

		$("#add-new-address-cancel").click(function() {
			$("#form-add-address").hide();
			$("#shopcard-address-list").show();
		});
		
		$( '.go_on_button_div' ).unbind('click')
		
		$( '.go_on_button_div' ).click(function()
		{
			address_ready();
		});
		
		$( "#new-address-cancel" ).click(function()
		{
			$( "#shopcard-address-list" ).hide();
			$( "#shopcard-address-list-current" ).show();
		});
		
		list_sort( 'form-field-bill-country_id' );
		
		$( '#form-field-bill-country_id' ).val(1);
*/		
/*
		$(".show-more").click(function() {
			$("#shopcard-address-list").toggle('fade');
		});
*/		
	}

	/**
	 * save new address bill user id
	 */
	function AddressBillAdd(userId, shopId, orderId)
	{
//		alert( $adr_type );
		
		var bill_user_id			=	userId;
		var bill_shop_id			=	shopId;
		if ( <?php echo $checkout_guest?> ==1 )
		{
			var bill_usermail			= 	$("#form-field-bill-usermail").val();
		}
		var bill_company			= 	$("#form-field-bill-company").val();
		var bill_gender				= 	$("#form-field-bill-gender").val();
		var bill_title				= 	$("#form-field-bill-title").val();
			var bill_firstname			=	$("#form-field-bill-firstname").val();
			var bill_lastname			= 	$("#form-field-bill-lastname").val();
			var bill_street				=	$("#form-field-bill-street").val();
			var bill_number				=	$("#form-field-bill-number").val();
		var bill_additional			=	$("#form-field-bill-additional").val();
			var bill_zip				=	$("#form-field-bill-zip").val();
			var bill_city				=	$("#form-field-bill-city").val();
		var bill_country			=	$("#form-field-bill-country").val();
			var bill_country_id			=	$("#form-field-bill-country_id").val();
//		var bill_standard_ship_adr	=	$("#form-field-bill-standard-ship").val();
/*
		if ($("#form-field-bill-standard:checked").val() === undefined) {
			var bill_standard = 0;
		} else {
			var bill_standard = 1;
		}

		if ($("#form-field-bill-standard-ship:checked").val() === undefined) {
			var bill_standard_ship_adr = 0;
		} else {
			var bill_standard_ship_adr = 1;
		}
*/
		if ( <?php echo $checkout_guest?> ==1 )
		{
			if ( bill_usermail == "" )
			{
				$("#form-field-bill-usermail").css("border", "1px solid red");
				alert("<?php echo t("Bitte eine Emailadresse angeben"); ?>");
				return;
			}
			else if ( bill_usermail.indexOf('@') == -1 || bill_usermail.indexOf('.') == -1 )
			{
				$("#form-field-bill-usermail").css("border", "1px solid red");
				alert("<?php echo t("Bitte geben Sie eine gültige Emailadresse an"); ?>");
				return;
			}
			else
			{
				$("#form-field-bill-usermail").css("border", "1px solid green");
			}
		}
		
		if ( bill_firstname == "" || bill_lastname == "" )
		{
//			$("#form-field-bill-company").css("border", "1px solid red");
			$("#form-field-bill-firstname").css("border", "1px solid red");
			$("#form-field-bill-lastname").css("border", "1px solid red");
			alert("<?php echo t("Bitte Vor- und Nachnamen angeben!"); ?>");
			return;
		}
		else
		{
//			$("#form-field-bill-company").css("border", "1px solid green");
			$("#form-field-bill-firstname").css("border", "1px solid green");
			$("#form-field-bill-lastname").css("border", "1px solid green");
		}
/*
		if (bill_company == "" && bill_firstname == "" ) {
			$("#bill_firstname").css("border", "1px solid red");
			return;
		} else {
			$("#bill_firstname").css("border", "1px solid green");
		}

		if (bill_company == "" && bill_lastname == "" ) {
			$("#bill_lastname").css("border", "1px solid red");
			return;
		} else {
			$("#bill_lastname").css("border", "1px solid green");
		}
*/
		if ( bill_street == "" )
		{
			$("#form-field-bill-street").css("border", "1px solid red");
			alert("<?php echo t("Bitte Straßennamen angeben!"); ?>");
			return;
		}
		else
		{
			$("#form-field-bill-street").css("border", "1px solid green");
		}

		if ( bill_number == "" )
		{
			$("#form-field-bill-number").css("border", "1px solid red");
			alert("<?php echo t("Bitte die Hausnummer angeben!"); ?>");
			return;
		}
		else
		{
			$("#form-field-bill-number").css("border", "1px solid green");
		}

		if ( bill_zip == "" )
		{
			$("#form-field-bill-zip").css("border", "1px solid red");
			alert("<?php echo t("Bitte die Postleitzahl angeben!"); ?>");
			return;
		}
		else
		{
			$("#form-field-bill-zip").css("border", "1px solid green");
		}

		if ( bill_city == "" )
		{
			$("#form-field-bill-city").css("border", "1px solid red");
			alert("<?php echo t("Bitte die Stadt angeben!"); ?>");
			return;
		}
		else
		{
			$("#form-field-bill-city").css("border", "1px solid green");
		}
		
		if ( <?php echo $checkout_guest?> ==1 )
		{
			guest_user();
			return;
		}
		
		var $post_data = 			new Object();
		$post_data['API'] = 		"shop";
		$post_data['APIRequest'] = 	"OrderAddressUpdate";

		$post_data['addresstype'] = $adr_type;
		$post_data['OrderID'] = 	orderId;
		$post_data['customer_id'] = bill_user_id;
//		$post_data['shop_id'] = 	bill_shop_id;
		$post_data['company'] = 	bill_company;
		$post_data['gender'] = 		bill_gender;
		$post_data['title'] = 		bill_title;
		$post_data['firstname'] = 	bill_firstname;
		$post_data['lastname'] = 	bill_lastname;
		$post_data['street'] = 		bill_street;
		$post_data['number'] = 		bill_number;
		$post_data['additional'] = 	bill_additional;
		$post_data['zip'] = 		bill_zip;
		$post_data['city'] = 		bill_city;
		$post_data['country'] = 	bill_country;
		$post_data['country_id'] = 	bill_country_id;
//		$post_data['standard'] = bill_standard;
//		$post_data['standard_ship_adr'] = bill_standard_ship_adr;
//		$post_data['active'] =	1;
//		$post_data['active_ship_adr'] =	1;
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data)
		{
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
//			document.location.href = '/online-shop/zahlungsart/';
			
			AddressBillList( <?php echo $userData['userId'];?>, <?php echo $_SESSION['id_shop'];?>, <?php echo $checkout_order_id;?> );
			
//			$( "#shopcard-address-list" ).hide();
			$( "#form-add-address" ).hide();
			$( "#list-address" ).show();
			$( "#shopcard-address-list-current" ).show();
			wait_dialog_hide();
			
		});
	}

	/**
	 * remove address bill adr by adrId and userId
	 */
	function AddressBillRemove(adrId, userId)
	{
		if ( confirm( "<?php echo t( "Wollen Sie die Adresse wirklich löschen?" );?>" ) )
		{
			var $post_data = 			new Object();
			$post_data['API'] = 		"shop";
			$post_data['APIRequest'] = 	"AddressBill";
			$post_data['action'] = 		'remove';
			$post_data['adrId'] = 		adrId;
			$.post( '<?php echo PATH;?>soa2/', $post_data, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch( $err ) { show_status2( $err.message ); return; }
				if ( $xml.find( 'Ack' ).text() != 'Success' ) { show_status2( $data ); return; }
				
				$( '#address-bill-' + adrId ).hide();
			});
		}		
	}
/*	
	function widgetFieldAddressBoth($xml)
	{
		var addressField = ('.address-billing');
		var widgetItem;
		var adrId;
		var userId;
		var setChecked;
		$(addressField).append('<span><strong><i class="fa fa-money"></i> Rechnungs- und Lieferadresse</strong></span>');
		$xml.find('widgetFieldAddressBillShipping').each(function()
		{
			if ($(this).find('standard_ship_adr').text() == 1) {
				setChecked = '<input id="form-field-shipping-standard" class="shopcard" type="checkbox" value="1" checked="checked">'
				+ '<span>wird als Standard verwendet</span>';	
			} else {
				setChecked = '<input id="form-field-shipping-standard" class="shopcard" type="checkbox" value="1">'
				+ '<span>als Standard verwenden</span>';				
			}
			adrId = $(this).find('adr_id').text();
			userId = $(this).find('user_id').text();
			widgetItem = '<div id="' + adrId + '" class="widget-field-address field-shipping-active">'
				+ '	<span class="title">' + $(this).find('gender').text() + '</span>'
				+ '	<span class="title">' + $(this).find('firstname').text() + ' ' + $(this).find('lastname').text() + '</span>'
				+ '	<span class="street">' + $(this).find('street').text() + ' ' + $(this).find('number').text() + '</span>'
				+ '	<span class="zip">' + $(this).find('zip').text() + ' ' + $(this).find('city').text() + '</span>'
				+ '	<span class="country">' + $(this).find('country').text() + '</span>'
				+ '</div>'
				+ '<button id="edit-shipping" class="btn btn-edit btn-right">ändern</button>'
				+ '	<div class="clear"></div>'
				+ '	<div class="input-group">' + setChecked + '	</div>';				
			$(widgetItem).appendTo(addressField);
			$("#form-field-shipping-standard").click(function() {
				SetAddressShippingStandard(adrId, userId);
			});
		});
		if (adrId == 0) {
			$('.address-shipping').append('<div class="alert alert-info">Lieferadresse ist gleich der Rechnungsadresse</div>');
		}		
	}
*/
	/**
	 * show widget field address billing
	 */
	function widgetFieldAddressBilling( $xml )
	{
		var addressField = ('.address-billing');
		var widgetItem;
		var adrId;
//		var $ship_adr_id = 0;
		var userId;
		var setChecked;
		if ( $ship_adr_id == 0 )
		{
			$(addressField).append('<span><strong><?php echo t( 'Rechnungs- und Lieferadresse' );?></strong></span>');
		}
		else
		{
			$(addressField).append('<span><strong><?php echo t( 'Rechnungsadresse' );?></strong></span>');
		}
		$xml.find('widgetFieldAddressBillBilling').each(function()
		{
			if ($(this).find('standard').text() == 1) {
				setChecked = '<input id="form-field-billing-standard" class="shopcard" type="checkbox" value="1" checked="checked">'
				+ '<span>wird als Standard verwendet</span>';	
			} else {
				setChecked = '<input id="form-field-billing-standard" class="shopcard" type="checkbox" value="1">'
				+ '<span>als Standard verwenden</span>';				
			}	
			adrId = $(this).find('adr_id').text();
			userId = $(this).find('user_id').text();
			widgetItem =  '<div id="' + adrId + '" class="bill_address_field widget-field-address field-standard-active" title="<?php echo t( 'Rechnungsadresse ändern' );?>">';
//			widgetItem += '	<span class="title">' + $(this).find('gender').text() + '</span>';
			widgetItem += '<br />';
			if ( $(this).find('company').text() != '' )
			{
				widgetItem += '		<span class="title">' + $(this).find('company').text() + '</span>';
			}
			widgetItem += '	<span class="title">' + $(this).find('firstname').text() + ' ' + $(this).find('lastname').text() + '</span>';
			widgetItem += '	<span class="street">' + $(this).find('street').text() + ' ' + $(this).find('number').text() + '</span>';
			if ( $(this).find('additional').text() != '' )
			{
				widgetItem += '		<span class="title">' + $(this).find('additional').text() + '</span>';
			}
			widgetItem += '	<span class="zip">' + $(this).find('zip').text() + ' ' + $(this).find('city').text() + '</span>';
			widgetItem += '	<span class="country">' + $(this).find('country').text() + '</span>';
			widgetItem += '</div>';
//				+ '	<button id="edit-standard" class="btn btn-edit btn-right">ändern</button>'
			widgetItem += '	<div class="clear"></div>';
			widgetItem += '	<div class="input-group">' + setChecked + '	</div>';

			$(widgetItem).appendTo('.address-billing');
			
			$('#form-field-billing-standard').click(function(e) {
				SetAddressBillingStandard(adrId, userId);
			});	
		});
		
		$( ".bill_address_field" ).click(function()
		{
			$adr_type = 'bill';
			
			$( '#address_list_label' ).empty();
			$( '#address_list_label' ).append( 'Rechnungsadresse auswählen:' );
			
			$( '.address_field' ).removeClass( 'flied-standard-active flied-shipping-active flied-inactive' );
			$( '.address_field' ).addClass( 'flied-inactive' );
			
			if ( $bill_adr_id > 0 )
			{
				$( '#address-bill-' + $bill_adr_id ).removeClass( 'flied-inactive' );
				$( '#address-bill-' + $bill_adr_id ).addClass( 'flied-standard-active' );
				$( '#address-bill-' + $bill_adr_id ).css( 'cursor', 'default' );
			}
			
			if ( $ship_adr_id > 0 )
			{
				$( '#address-bill-' + $ship_adr_id ).css( 'cursor', 'pointer' );
			}

 			$( "#shopcard-address-list-current" ).hide();
			$( "#shopcard-address-list" ).show();
		});	
	}
	
	/**
	 * show widget field address shipping
	 */
	function widgetFieldAddressShipping($xml)
	{
		if ( $ship_adr_id == 0 )
		{
			$( '.address-shipping' ).append('<span class="address_label_hide"><strong><?php echo t( 'Lieferadresse' );?></strong></span>');
			$( '.address-shipping' ).append( '<button class="btn btn-add" id="ship_adr_set" title="<?php echo t( 'Gesonderte Lieferadresse verwenden' );?>"><?php echo t( 'Ich möchte eine gesonderte Lieferadresse verwenden' );?></button>' );
			
			$( '#ship_adr_set' ).click(function()
			{
				ship_adr_choose();
			});
			
			return;
		}
		var addressField = ('.address-shipping');
		var widgetItem;
		var adrId;
		var userId;
		var setChecked;
		$(addressField).append('<span><strong><?php echo t( 'Lieferadresse' );?></strong></span>');
		$xml.find('widgetFieldAddressBillShipping').each(function()
		{
			if ($(this).find('standard_ship_adr').text() == 1) {
				setChecked = '<input id="form-field-shipping-standard" class="shopcard" type="checkbox" value="1" checked="checked">'
				+ '<span>wird als Standard verwendet</span>';	
			} else {
				setChecked = '<input id="form-field-shipping-standard" class="shopcard" type="checkbox" value="1">'
				+ '<span>als Standard verwenden</span>';				
			}
			adrId = $(this).find('adr_id').text();
			userId = $(this).find('user_id').text();
			widgetItem =  '<div id="' + adrId + '" class="shipping_address_field widget-field-address field-shipping-active" title="<?php echo t( 'Lieferadresse ändern' );?>">';
//			widgetItem += '	<span class="title">' + $(this).find('gender').text() + '</span>';
			widgetItem += '<br />';
			if ( $(this).find('company').text() != '' )
			{
				widgetItem += '		<span class="title">' + $(this).find('company').text() + '</span>';
			}
			widgetItem += '	<span class="title">' + $(this).find('firstname').text() + ' ' + $(this).find('lastname').text() + '</span>';
			widgetItem += '	<span class="street">' + $(this).find('street').text() + ' ' + $(this).find('number').text() + '</span>';
			if ( $(this).find('additional').text() != '' )
			{
				widgetItem += '		<span class="title">' + $(this).find('additional').text() + '</span>';
			}
			widgetItem += '	<span class="zip">' + $(this).find('zip').text() + ' ' + $(this).find('city').text() + '</span>';
			widgetItem += '	<span class="country">' + $(this).find('country').text() + '</span>';
			widgetItem += ' <br /><button class="btn btn-add" id="ship_adr_unset" title="<?php echo t( 'Keine gesonderte Lieferadresse verwenden' );?>"><?php echo t( 'Keine gesonderte Lieferadresse verwenden' );?></button>';
			widgetItem += '</div>';
//				+ '<button id="edit-shipping" class="btn btn-edit btn-right">ändern</button>'
			widgetItem += '	<div class="clear"></div>';
			widgetItem += '	<div class="input-group">' + setChecked + '	</div>';
			
			$( widgetItem ).appendTo( addressField );
			
			$("#form-field-shipping-standard").click(function() {
				SetAddressShippingStandard(adrId, userId);
			});
		});
		if (adrId == 0) {
			$('.address-shipping').append('<div class="alert alert-info">Lieferadresse ist gleich der Rechnungsadresse</div>');
		}
		
		$( ".shipping_address_field" ).click(function(e)
		{
			if ( e.target.id != 'ship_adr_unset' )
			{
				ship_adr_choose();
/*				
				$adr_type = 'ship';
				
				$( '#address_list_label' ).empty();
				$( '#address_list_label' ).append( 'Lieferadresse auswählen:' );
				
				$( '.address_field' ).removeClass( 'flied-standard-active flied-shipping-active flied-inactive' );
				$( '.address_field' ).addClass( 'flied-inactive' );
				
				if ( $ship_adr_id > 0 )
				{
					$( '#address-bill-' + $ship_adr_id ).removeClass( 'flied-inactive' );
					$( '#address-bill-' + $ship_adr_id ).addClass( 'flied-shipping-active' );
					$( '#address-bill-' + $ship_adr_id ).css( 'cursor', 'default' );
				}
				
				if ( $bill_adr_id > 0 )
				{
					$( '#address-bill-' + $bill_adr_id ).css( 'cursor', 'pointer' );
				}
				
				$( "#shopcard-address-list-current" ).hide();
				$( "#shopcard-address-list" ).show();
*/				
			}
		});
		
		$( '#ship_adr_unset' ).click(function()
		{
			ship_adr_unset();
		});
	}	
	
	/**
	 * show widget field address
	 */
	function widgetFieldAddress($xml, shopId, orderId)
	{
		if ( $bill_adr_id == 0 )
		{
			$adr_type = 'bill';
			
			$( '#address_list_label' ).empty();
			$( '#address_list_label' ).append( 'Rechnungsadresse auswählen:' );
			
			$( "#shopcard-address-list-current" ).hide();
			$( "#shopcard-address-list" ).show();
		}
		
		var widgetItem;
		var adrId;
		var $adr_ids = new Array();
		var userId;
		var buttons;
		var standard;
		var viewStandard;
/*		
		var $bill_adr_id = 0;
		var $ship_adr_id = 0;
		
		$xml.find( 'widgetFieldAddressBillBilling' ).each(function()
		{
			if ( $( this ).find( 'adr_id' ).text() != '' )
			{
				$bill_adr_id = $( this ).find( 'adr_id' ).text();
			}
		});
		$xml.find( 'widgetFieldAddressBillShipping' ).each(function()
		{
			if ( $( this ).find( 'adr_id' ).text() != '' )
			{
				$ship_adr_id = $( this ).find( 'adr_id' ).text();
			}
		});
*/
		$( '#new-address-cancel' ).show();
		if ( $bill_adr_id == 0 )
		{
			$( '#new-address-cancel' ).hide();
		}
				
		$xml.find('widgetFieldAddressBill').each(function()
		{
			standard = 		"";
			viewStandard = 'flied-inactive';
			adrId = 		$(this).find('adr_id').text();
			userId = 		$(this).find('user_id').text();

			if ($(this).find('standard').text() == 1 && $(this).find('standard_ship_adr').text() == 1)
			{
				standard = '<small class="text-warning"><?php echo t( 'Standard Rechnungs- und Lieferadresse' );?></small>';
//				viewStandard = 'flied-standard-shipping-active';
			}
			else
			{
				if ($(this).find('standard').text() == 1)
				{
					standard = '<small class="text-warning"><?php echo t( 'Standard Rechnungsadresse' );?></small>';
//					viewStandard = 'flied-standard-active';
				}
				if ($(this).find('standard_ship_adr').text() == 1)
				{
					standard = '<small class="text-warning"><?php echo t( 'Standard Lieferadresse' );?></small>';
//					viewStandard = 'flied-shipping-active';
				}
			}
			
/*			
			if ( adrId == $ship_adr_id )
			{
				viewStandard = 'flied-shipping-active';
			}
			if ( adrId == $bill_adr_id )
			{
				viewStandard = 'flied-standard-active';
			}
*/			
			buttons = '<div class="widget-buttons">' 
			if  ( adrId != $bill_adr_id && adrId != $ship_adr_id )
			{
				buttons	+= '	<span id="trash_button" onclick=\"AddressBillRemove(' + adrId + ',' + userId + ');\" class="set-active" title="<?php echo t( 'Adresse löschen' );?>"><i id="trash_pic" class="fa fa-trash-o"></i></span>';
				buttons	+= '	<div class="clear"></div>'
				+ '</div>';
			}
			else
			{
				buttons	+= '	<div class="clear"></div><br />'
				+ '</div>';
			}
//				+ '	<span id="active-trash-' + adrId + '" onclick=\"AddressBillRemove(' + adrId + ',' + userId + ');\" class="set-active" title="<?php echo t( 'Adresse löschen' );?>"><i id="trash_pic" class="fa fa-trash-o"></i></span>'
//				+ '	<span id="setorder-shipping-' + adrId + '" class="set-active"><i class="fa fa-road"></i></span>'
//				+ '	<span id="setorder-billing-' + adrId + '" class="set-active"><i class="fa fa-money"></i></span>'
//			buttons	+= '	<div class="clear"></div><br />'
//				+ '</div>';
			widgetItem =  '<div id="address-bill-' + adrId + '" class="address_field widget-field-address ' + viewStandard + '" title="<?php echo t( 'Adresse auswählen' );?>">' + buttons
				+ '	<div class="widget-address">';
//			widgetItem += '		<span class="title">' + $(this).find('gender').text() + '</span>';
			if ( $(this).find('company').text() != '' )
			{
				widgetItem += '		<span class="title">' + $(this).find('company').text() + '</span>';
			}
			widgetItem += '		<span class="title">' + $(this).find('firstname').text() + ' ' + $(this).find('lastname').text() + '</span>'
				+ '		<span class="street">' + $(this).find('street').text() + ' ' + $(this).find('number').text() + '</span>';
			if ( $(this).find('additional').text() != '' )
			{
				widgetItem += '		<span class="title">' + $(this).find('additional').text() + '</span>';
			}	
			widgetItem += '		<span class="zip">' + $(this).find('zip').text() + ' ' + $(this).find('city').text() + '</span>'
				+ '		<span class="country">' + $(this).find('country').text() + '</span>'
				+ '	</div>'
				+ standard
				+ '</div>';
			//$('#shopcard-address-list').append(widgetItem);
			$('#address_list').append(widgetItem);
			
			$adr_ids.push( adrId );
		});
		
		for( var n in $adr_ids)
		{
			(function(k)
			{
				$("#address-bill-" + k).click(function(e)
				{
					if ( e.target.id != 'trash_button' && e.target.id != 'trash_pic' )
					{
						if ( ( $adr_type == 'bill' && k != $bill_adr_id ) || ( $adr_type == 'ship' && k != $ship_adr_id ) )
						{
							order_adr_set( k );
						}
					}
				});
			})( $adr_ids[n] );
		}
		
		if ( adrId == 0 )
		{
			$('#shopcard-address-list').append('<div class="alert alert-warning">Keine alternativen Adressen vorhanden! Wenn sie die Rechnungs/Lieferadresse ändern möchten, müssen sie eine Neue eintragen!</div>');
		}		
		$('#shopcard-address-list').append('<div class="clear"></div>');
	}

</script>

<?php

	//	address list
	echo '
	<div id="list-address" class="row">
			<div id="shopcard-adress" class="shopcard-wrapper">
			<h1 id="shopcard_title" class="shopcard-heading">' . t('Adresse') . '</h1>
			<div>';
			echo getShopcardAssistent($userData);
//			echo '<div class="go_on_button_div" title="' . t( 'Weiter' ) . '">' . t( 'Weiter' ) . '</div>';
//			echo '<div class="clear"></div>';
			echo '
				<div id="shopcard-address-list-current" class="shopcard-field-address first-element">
					<div class="field-wrapper-block">
						<div class="address-billing left"></div>
						<div class="address-shipping right"></div>

						<div class="clear"></div>
					</div>
				<div class="go_on_button_div" title="' . t( 'Weiter' ) . '">' . t( 'Weiter' ) . ' >></div>
				<div class="clear"></div>	
				</div>';
/*
			echo '<div class="show-more">
					<span>' . t('weitere Adressen von mir anzeigen') . '</span>
					<i class="fa fa-angle-double-down"></i>
				</div>';
*/
			echo '<div id="shopcard-address-list" class="shopcard-field-address" style="display:none">
					<div class="add-container">
						<div id="address_list_label"></div>						
						<button id="add-new-address" class="btn btn-add btn-left" title="' . t('Neue Ardresse anlegen') . '">' . t('Neue Ardresse anlegen') . '</button>
						<button id="new-address-cancel" class="btn btn-add btn-left" title="' . t( 'Abbrechen' ) . '">' . t( 'Abbrechen' ) . '</button>
						<div class="clear"></div>
					</div>
					<div id="address_list"></div>
				</div>
			</div>
		</div>
	</div>';
	
	// if order a guest order
	if ( $checkout_guest == 1 )
	{
		
		echo '<div id="form-add-address" class="row" style="display:none">';
//		echo '<div id="form-add-address" class="row">';
		echo '	<div id="shopcard-adress" class="shopcard-wrapper">
				<h1 id="shopcard_title" class="shopcard-heading">' . t('Bitte geben Sie Ihre Adresse ein') . '</h1>
				<div class="shopcard-field-address">
					
					<div class="field-wrapper-block">
						<div class="field-wrapper left">
							<label for="form-field-bill-firstname"><b>
								' . t('Emailadresse') . '</b><span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihre Emailadresse an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-usermail" class="form-control" type="text">
							</div>
						</div>

						<div class="clear"></div>
					</div>
					
					<hr />
					
					<div class="field-wrapper-block">
						<div class="field-wrapper left">
							<label for="form-field-bill-firstname">
								' . t('Vorname') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihren Vornamen an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-firstname" class="form-control" type="text">
							</div>
						</div>

						<div class="field-wrapper right">
							<label for="form-field-bill-lastname">
								' . t('Nachname') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihren Nachnamen an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-lastname" class="form-control" type="text">
							</div>
						</div>

						<div class="clear"></div>
					</div>

					<hr>

					<div class="field-wrapper-block">

						<div class="field-wrapper left">
							<label for="form-field-bill-street">
								' . t('Straße') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihre Straße an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-street" class="form-control" type="text">
							</div>
						</div>

						<div class="field-wrapper right">
							<label for="form-field-bill-number">
								' . t('Hausnummer') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihre Hausnummer an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-number" class="form-control input-mask-number" type="text">
							</div>
						</div>

						<div class="clear"></div>
					</div>

					<hr>

					<div class="field-wrapper-block">

						<div class="field-wrapper left">
							<label for="form-field-bill-zip">
								' . t('Postleizahl') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihre Postleitzahl an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-zip" class="form-control input-mask-zip" type="text">
							</div>
						</div>

						<div class="field-wrapper right">
							<label for="form-field-bill-city">
								' . t('Ort') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier den Ort an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-city" class="form-control" type="text">
							</div>
						</div>

						<div class="clear"></div>
					</div>

					<hr>

					<div class="field-wrapper">
						<label for="form-field-bill-country">
							' . t('Land') . '
							<small class="text-warning"' . t('Geben sie hier ihr Land an') . '></small>
						</label>
						<div class="input-group">';
					echo getSelectShopCountries($userData);
					echo '
						</div>
					</div>

					<hr>

					<div class="field-wrapper-block">

						<div class="field-wrapper left"></div>

						<div class="field-wrapper right">
							<button id="add-new-address-save" onclick="AddressBillAdd(' . $userId . ', ' . $userData['shopId'] . ', ' . $userData['orderId'] . ');" class="btn btn-add">' . t('Speichern') . '</button>
							<button id="add-new-address-cancel" class="btn btn-add">' . t('Abbrechen') . '</button>
						</div>

						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>';
			
	}
	elseif ( $checkout_guest == 0 )
	{
		// add and edit form for address
		echo '
			<div id="form-add-address" class="row" style="display:none">
				<div id="shopcard-adress" class="shopcard-wrapper">
					<h1 id="shopcard_title" class="shopcard-heading">' . t('Neue Adresse anlegen') . '</h1>
					<div class="shopcard-field-address">

						<div class="field-wrapper">
							<label for="form-field-bill-company">
								' . t('Firma') . '
								<small class="text-warning">' . t('Geben sie hier ihre Firmenbezeichnung an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-company" class="form-control" type="text">
							</div>
						</div>

						<hr>

						<div class="field-wrapper-block">
							<div class="field-wrapper left">
								<label for="form-field-bill-gender">
									' . t('Anrede') . '
								</label>
								<div class="input-group">
									<select id="form-field-bill-gender" class="form-control">
										<option value="0" selected="selected">' . t('Herr') . '</option>
										<option value="1">' . t('Frau') . '</option>
									</select>
								</div>
							</div>

							<div class="field-wrapper right">
								<label for="form-field-bill-title">
									' . t('Title') . '
								</label>
								<div class="input-group">
									<input id="form-field-bill-title" class="form-control" type="text">
								</div>
							</div>

							<div class="clear"></div>
						</div>

						<hr>

						<div class="field-wrapper-block">
							<div class="field-wrapper left">
								<label for="form-field-bill-firstname">
									' . t('Vorname') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihren Vornamen an') . '</small>
								</label>
								<div class="input-group">
									<input id="form-field-bill-firstname" class="form-control" type="text">
								</div>
							</div>

							<div class="field-wrapper right">
								<label for="form-field-bill-lastname">
									' . t('Nachname') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihren Nachnamen an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-lastname" class="form-control" type="text">
								</div>
							</div>

							<div class="clear"></div>
						</div>

						<hr>

						<div class="field-wrapper-block">

							<div class="field-wrapper left">
								<label for="form-field-bill-street">
									' . t('Straße') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihre Straße an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-street" class="form-control" type="text">
								</div>
							</div>

							<div class="field-wrapper right">
								<label for="form-field-bill-number">
									' . t('Hausnummer') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihre Hausnummer an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-number" class="form-control input-mask-number" type="text">
								</div>
							</div>

							<div class="clear"></div>
						</div>

						<hr>

						<div class="field-wrapper">
							<label for="form-field-bill-additional">
								' . t('Adresszusatz') . '
								<small class="text-warning">' . t('Geben sie hier einen Adresszusatz an, wenn erforderlich') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-additional" class="form-control" type="text">
							</div>
						</div>

						<hr>

						<div class="field-wrapper-block">

							<div class="field-wrapper left">
								<label for="form-field-bill-zip">
									' . t('Postleitzahl') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihre Postleitzahl an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-zip" class="form-control input-mask-zip" type="text">
								</div>
							</div>

							<div class="field-wrapper right">
								<label for="form-field-bill-city">
									' . t('Ort') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier den Ort an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-city" class="form-control" type="text">
								</div>
							</div>

							<div class="clear"></div>
						</div>

						<hr>

						<div class="field-wrapper">
							<label for="form-field-bill-country">
								' . t('Land') . '
								<small class="text-warning">' . t('Geben sie hier ihr Land an') . '</small>
							</label>
							<div class="input-group">';
						echo getSelectShopCountries($userData);
						echo '
							</div>
						</div>

						<hr>

						<div class="field-wrapper-block">';
/*
						echo '<div class="field-wrapper left">
								<div class="input-group">
									<input id="form-field-bill-standard" class="shopcard" type="checkbox" checked="checked" value="1">
									<span>' . t('Als Standard Rechnungsandresse festlegen!') . '</span>
								</div>
								<div class="input-group">
									<input id="form-field-bill-standard-ship" class="shopcard" type="checkbox" checked="checked" value="1">
									<span>' . t('und als Lieferanschrift verwenden!') . '</span>
								</div>
							</div>';
*/
						echo '<div class="field-wrapper left">
								<button id="add-new-address-save" onclick="AddressBillAdd(' . $userId . ', ' . $userData['shopId'] . ', ' . $userData['orderId'] . ');" class="btn btn-add btn-left" title="' . t( 'Adresse speichern' ) . '">' . t('Speichern') . '</button>
								<button id="add-new-address-cancel" class="btn btn-add btn-left" title="' . t( 'Abbrechen' ) . '">' . t('Abbrechen') . '</button>
							</div>

							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>';
	}
	
	echo '<div id="message"></div>';
	
	//	loading....
//	echo '<script src="' . PATH . 'javascript/shop/AddressBill.php" type="text/javascript"></scriptc>';
	echo '<script type="text/javascript">AddressBillList(' . $userId . ', ' . $userData['shopId'] . ', ' . $userData['orderId'] . ');</script>';
	include("templates/".TEMPLATE."/footer.php");

/**
 *	--------------------------------------------------- functions -----------------------------------------------------
 *
 */


	function getShopcardAssistent($userData)
	{
		if (!empty($userData['userId'])) {
			$payment = '<li><em>2.</em>' . t('Zahlungsart') . '</li>';
		} else {
			$payment = '<li>' . t('Zahlungsart') . '</li>';	
		}
		$html ='
		<div class="widget-shopcard-assistent">
			<ul>
				<li class="active"><em>1.</em>' . t('Adresse') . '</li>';
			$html.= $payment;
			$html.= '
				<li><em>3.</em>' . t('Versandart') . '</li>
				<li><em>4.</em>' . t('Bestellübersicht') . '</li>
			</ul>
			<div class="clear"></div>
		</div>';
		return $html;
	}

	/**
	 * Returns a shop countries select field
	 *
	 * @param $userData
	 * @return string
	 */
	function getSelectShopCountries($userData)
	{
		
		// get shop-shipping-countries
		$post_data = 				array();
		$post_data['API'] = 		'shop';
		$post_data['APIRequest'] = 	'ShippingCountriesGet';
		
		$response = soa2( $post_data, __FILE__, __LINE__ );
		
		// build select
		$html = '';
		
		$html .= '<select id="form-field-bill-country_id" class="form-control">';

		foreach ( $response->shipping_country as $value )
		{
			$html .= '<option value="' . (string)$value->country_id[0] . '">' . (string)$value->country[0] . '</option>';
		}
//		$html .= '<option>test</option>';
		$html .= '</select>';
		
		return $html;
		
	/*	
		$field = array();
		$field['from'] = 'shop_payment';
		$field['select'] = 'country_id';
		$addWhere = "shop_id=" . $userData['shopId'];
		$shopPayments = SQLSelect($field['from'], $field['select'], $addWhere, 0, 0, 0, 'shop',  __FILE__, __LINE__);
		foreach($shopPayments as $shopPayment)
		{
			$shop_countries[$shopPayments["country_id"]] = $shopPayment["country_id"];
		}
	
		$field = array();
		$field['from'] = 'shop_countries';
		$field['select'] = '*';
		$field['orderBy'] = 'ordering';
		$shopCountries = SQLSelect($field['from'], $field['select'], 0, $field['orderBy'], 0, 0, 'shop',  __FILE__, __LINE__);
		$option = "";
		foreach($shopCountries as $shopCountry)
		{
			if (isset($shop_countries[$shopCountry["id_country"]]) || ($userData['shopId'] != 2 && $userData['shopId'] != 22))
			{
				if ($userData['ship_country_id'] == $shopCountry["id_country"]) {
					$selected = ' selected="selected"';
				} else {
					$selected = '';
				}
				$option.= '<option' . $selected . ' value="' . $shopCountry["id_country"] . '">' . t($shopCountry["country"]) . '</option>';
			}
		}
		$html = '<select id="form-field-bill-country_id" class="form-control">' . $option . '</select>';
		return  $html;
	*/	
	}
