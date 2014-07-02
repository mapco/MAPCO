<?php

/**
 *	@author: rlange@mapco.de
 *	- address bill
 */
 
header('Content-type: text/javascript');

	//make dreamweaver highlight javascript
	if (true == false) { ?> <script type="text/javascript"> <?php }
?>

	/*
	 *--------------------------------------- Widgets ------------------------------------------------
	 */
	 
	/**
	 * show widget field address billing and shipping
	 */	 
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
			widgetItem = '<div id="' + adrId + '" class="widget-field-address flied-shipping-active">'
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

	/**
	 * show widget field address billing
	 */
	function widgetFieldAddressBilling($xml)
	{
		var addressField = ('.address-billing');
		var widgetItem;
		var adrId;
		var userId;
		var setChecked;
		$(addressField).append('<span><strong><i class="fa fa-money"></i> Rechnungsadresse</strong></span>');
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
			widgetItem = '<div id="' + adrId + '" class="widget-field-address flied-standard-active">'
				+ '	<span class="title">' + $(this).find('gender').text() + '</span>'
				+ '	<span class="title">' + $(this).find('firstname').text() + ' ' + $(this).find('lastname').text() + '</span>'
				+ '	<span class="street">' + $(this).find('street').text() + ' ' + $(this).find('number').text() + '</span>'
				+ '	<span class="zip">' + $(this).find('zip').text() + ' ' + $(this).find('city').text() + '</span>'
				+ '	<span class="country">' + $(this).find('country').text() + '</span>'
				+ '</div>'
				+ '	<button id="edit-standard" class="btn btn-edit btn-right">ändern</button>'
				+ '	<div class="clear"></div>'
				+ '	<div class="input-group">' + setChecked + '	</div>';		
			$(widgetItem).appendTo('.address-billing');
			$('#form-field-billing-standard').click(function(e) {
				SetAddressBillingStandard(adrId, userId);
			});	
		});	
	}
	
	/**
	 * show widget field address shipping
	 */
	function widgetFieldAddressShipping($xml)
	{
		var addressField = ('.address-shipping');
		var widgetItem;
		var adrId;
		var userId;
		var setChecked;
		$(addressField).append('<span><strong><i class="fa fa-money"></i> Lieferadresse</strong></span>');
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
			widgetItem = '<div id="' + adrId + '" class="widget-field-address flied-shipping-active">'
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
	
	/**
	 * show widget field address
	 */
	function widgetFieldAddress($xml, shopId, orderId)
	{
		var widgetItem;
		var adrId;
		var userId;
		var buttons;
		var standard;
		var viewStandard;
		$xml.find('widgetFieldAddressBill').each(function()
		{
			standard = "";
			viewStandard = 'flied-inactive';
			adrId = $(this).find('adr_id').text();
			userId = $(this).find('user_id').text();

			if ($(this).find('standard').text() == 1 && $(this).find('standard_ship_adr').text() == 1) {
				standard = '<small class="text-warning">Wird als Standard Rechnungs/Lieferadresse verwendet!</small>';
				viewStandard = 'flied-standard-shipping-active';
			} else {
				if ($(this).find('standard').text() == 1) {
					standard = '<small class="text-warning">Wird als Standard Rechnungsadresse verwendet!</small>';
					viewStandard = 'flied-standard-active';
				}
				if ($(this).find('standard_ship_adr').text() == 1) {
					standard = '<small class="text-warning">Wird als Standard Lieferadresse verwendet</small>';
					viewStandard = 'flied-shipping-active';
				}
			}
			buttons = '<div class="widget-buttons">' 
				+ '	<span id="active-trash-' + adrId + '" onclick=\"AddressBillRemove(' + adrId + ',' + userId + ');\" class="set-active"><i class="fa fa-trash-o"></i></span>'
				+ '	<span id="setorder-shipping-' + adrId + '" class="set-active"><i class="fa fa-road"></i></span>'
				+ '	<span id="setorder-billing-' + adrId + '" class="set-active"><i class="fa fa-money"></i></span>'
				+ '	<div class="clear"></div>'
				+ '</div>';
			widgetItem = '<div id="address-bill-' + adrId + '" class="widget-field-address ' + viewStandard + '">' + buttons
				+ '	<div class="widget-address">'
				+ '		<span class="title">' + $(this).find('gender').text() + '</span>'
				+ '		<span class="title">' + $(this).find('firstname').text() + ' ' + $(this).find('lastname').text() + '</span>'
				+ '		<span class="street">' + $(this).find('street').text() + ' ' + $(this).find('number').text() + '</span>'
				+ '		<span class="zip">' + $(this).find('zip').text() + ' ' + $(this).find('city').text() + '</span>'
				+ '		<span class="country">' + $(this).find('country').text() + '</span>'
				+ '	</div>'
				+ standard
				+ '</div>';
			$('#shopcard-address-list').append(widgetItem);
			
			SetShopOrderBillingAddress(adrId, shopId, orderId, userId);
			SetShopOrderShppingAddress(adrId, shopId, orderId, userId);
		});
		if (adrId == 0) {
			$('#shopcard-address-list').append('<div class="alert alert-warning">Keine alternativen Adressen vorhanden! Wenn sie die Rechnungs/Lieferadresse ändern möchten, müssen sie eine Neue eintragen!</div>');
		}		
		$('#shopcard-address-list').append('<div class="clear"></div>');
	}