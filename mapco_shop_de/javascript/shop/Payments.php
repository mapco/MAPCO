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
	 * show button field for payment methods
	 */
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

	/**
	 * show widget field payment methods
	 */
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
	
	
	
	
	
	
	
	