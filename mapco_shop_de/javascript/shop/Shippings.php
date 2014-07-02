<?php

/**
 *	@author: rlange@mapco.de
 *	- payments
 */
 
header('Content-type: text/javascript');

	//make dreamweaver highlight javascript
	if (true == false) { ?> <script type="text/javascript"> <?php }
?>

	/*
	 *--------------------------------------- Widgets ------------------------------------------------
	 */
	 
	/**
	 * show button field for shipping methods
	 */
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
	 
	/**
	 * show widget field shipping methods
	 */
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