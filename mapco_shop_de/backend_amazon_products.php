<?php

include("config.php");
include("templates/".TEMPLATE_BACKEND."/header.php");
require_once("../APIs/amazon/Model/AmazonModel.php");

// keep get request and post submit
$post = $_POST;
$get = $_GET;
if (!isset($get['set'])) $get['set'] = 0;
if (!isset($get['orderBy'])) $get['orderBy'] = 0;
?>

<script type="text/javascript">

	/**
	 * show amazon Products table by accountssites id
	 */	
	function listAmazonProducts($account_id, $accountSiteID, $limit, $set, $orderBy)
	{				
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonProducts";
		$post_data['action'] = "listAmazonProducts";
		$post_data['account_id'] = $account_id;
		$post_data['accountsite_id'] = $accountSiteID;
		$post_data['limit'] = $limit;
		$post_data['set'] = $set;
		$post_data['orderBy'] = $orderBy;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			wait_dialog_show();
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}

			var stats = $('<table class="widget-stats-full"></table>');
			
			if ($xml.find('activeProducts').text() == 0) { $setClassActiveProducts = 'widget-success'; } else { $setClassActiveProducts = 'widget-warning'; }
			var warningActiveProducts = '<div class="' + $setClassActiveProducts + '"><strong>Produkte<br>Aktiv</strong><span>'
				+ $xml.find('activeProducts').text()
				+ '</span></div>';			
			
			if ($xml.find('emptyAsin').text() == 0) { $setClassAsin = 'widget-success'; } else { $setClassAsin = 'widget-warning'; }
			var warningASIN = '<div class="' + $setClassAsin + '"><strong>Produkte<br>Ohne ASIN</strong><span>'
				+ $xml.find('emptyAsin').text()
				+ '</span><input type="button" class="info-corner-button" id="info_asin_button" value="!"></div>';
				
			var warningCriticalPrices = $xml.find('criticalPrices').text();
				
			if ($xml.find('emptyEAN').text() == 0) { $setClassEan = 'widget-success'; } else { $setClassEan = 'widget-warning'; }
			var warningEAN = '<div class="' + $setClassEan + '"><strong>Produkte<br>Ohne EAN</strong><span>'
				+ $xml.find('emptyEAN').text()
				+ '</span></div>';

			if ($xml.find('emptyImage').text() == 0) { $setClassImage = 'widget-success'; } else { $setClassImage = 'widget-warning'; }				
			var warningWithoutImage = '<div class="' + $setClassImage + '"><strong>Produkte<br>Ohne Image</strong><span>'
				+ $xml.find('emptyImage').text()
				+ '</span></div>';
				
			if ($xml.find('emptyStandardPrice').text() == 0) { $setClassStandardPrice = 'widget-success'; } else { $setClassStandardPrice = 'widget-warning'; }				
			var warningWithoutStandardPrice = '<div class="' + $setClassStandardPrice + '"><strong>Produkte<br>Ohne Preis</strong><span>'
				+ $xml.find('emptyStandardPrice').text()
				+ '</span><span class="update-open"><i class="fa fa-upload"></i>' + $xml.find('submitedStandardPrice').text() + '</span>'
				+ '<input type="button" class="info-corner-button" id="info_price_button" value="!"></div>';
				
			if ($xml.find('emptyQuantities').text() == 0) { $setClassQuantities = 'widget-success'; } else { $setClassQuantities = 'widget-warning'; }				
			var warningWithoutQuantities = '<div class="' + $setClassQuantities + '"><strong>Produkte<br>Ohne Verfügbarkeit</strong><span>'
				+ $xml.find('emptyQuantities').text()
				+ '</span><span class="update-open"><i class="fa fa-upload"></i>' + $xml.find('submitedQuantities').text() + '</span>'
				+ '</div>';
				
			if ($xml.find('submitReady').text() == 0) { $setClassSubmitReady = 'widget-success'; } else { $setClassSubmitReady = 'widget-warning'; }				
			var warningSubmitReady = '<div class="' + $setClassSubmitReady + '"><strong>Produkte<br>Upload bereit</strong><span>'
				+ $xml.find('submitReady').text()
				+ '</span></div>';												
				
			var warningCountAll = '<div class="widget-success"><strong>Produkte<br>Gesamt</strong><span>'
				+ $xml.find('allProducts').text()
				+ '</span></div>';
				
			$("#search-button").click(function() {
				var addSearchSKU = $("#searchSKU").val();
				var addSearchASIN = $("#searchASIN").val();
				
				var $post_data = new Object();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonProducts";
				$post_data['action'] = "searchAmazonProducts";
				$post_data['accountsite_id'] = $accountSiteID;
				$post_data['limit'] = 10;
				$post_data['searchSKU'] = addSearchSKU;
				$post_data['searchASIN'] = addSearchASIN;
				$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
					wait_dialog_show();
					try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
					if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				
					amazonProductsTable($xml);
					$('.h3-info').empty();
					if  (addSearchASIN != 0) {
						$('.h3-info').append('[ASIN Suchergebnis für ' + addSearchASIN + ']');
					}
					if  (addSearchSKU != 0) {
						$('.h3-info').append('[SKU Suchergebnis für ' + addSearchSKU + ']');
					}					
					wait_dialog_hide();
				});
			});	
			
			$("#ean-convert").click(function() {
				
				$('#ean-result').empty();
				$('#listPriceFormattedPrice-result').empty();
				$('#salesRank-result').empty();
				
				var addConvertASIN = $("#convertASIN").val();
				
				var $post_data = new Object();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonAsin2EAN";
				$post_data['action'] = "convertAsinToEan";
				$post_data['account_id'] = $account_id;
				$post_data['accountsite_id'] = $accountSiteID;
				$post_data['limit'] = 10;
				$post_data['convertASIN'] = addConvertASIN;
				$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
					wait_dialog_show();
					try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
					if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
					$xml.find('AmazonAsinToEan').each(function() 
					{
						$('#ean-result').append('EAN: ' + $(this).find('ean').text());
						$('#listPriceFormattedPrice-result').append('Preis: ' + $(this).find('listPriceFormattedPrice').text());
						$('#salesRank-result').append('Sales Rank: ' + $(this).find('SalesRank').text());
					});
					wait_dialog_hide();
				});
				
			});																	
			
			$importInventory = '<button style="float:right;margin-top: 2px;" onclick="importInventoryForm(' + $accountSiteID + ')">Import Inventory CSV</button>';			
			
			$('h1 span.h1').append('Amazon Produkte Liste');
			$('h1').append($importInventory);
			stats.append(warningCountAll);
			stats.append(warningActiveProducts);
			stats.append(warningCriticalPrices);			
			stats.append(warningWithoutImage);
			stats.append(warningASIN);
			stats.append(warningEAN);
			stats.append(warningWithoutStandardPrice);
			stats.append(warningWithoutQuantities);
			stats.append(warningSubmitReady);
			$('#stats-wrapper').append(stats);			
			
			amazonProductsTable($xml);
			wait_dialog_hide();
			
			$("#info_asin_button").click(function() {
				wait_dialog_show();
				var $post_data = new Object();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonProducts";
				$post_data['action'] = "listAmazonProducts";
				$post_data['accountsite_id'] = $accountSiteID;
				$post_data['limit'] = $limit;
				$post_data['addWhere'] = 'asin';
				$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
					try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
					if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}				
				
					amazonProductsTable($xml);
					$('.h3-info').empty();
					$('.h3-info').append('[Produkte ohne Asin]');					
					wait_dialog_hide();
				});
			});	
			
			$("#info_price_button").click(function() {
				wait_dialog_show();	
				var $post_data = new Object();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonProducts";
				$post_data['action'] = "listAmazonProducts";
				$post_data['accountsite_id'] = $accountSiteID;
				$post_data['limit'] = $limit;
				$post_data['addWhere'] = 'price';
				$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
					try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
					if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}				
				
					amazonProductsTable($xml);
					$('.h3-info').empty();
					$('.h3-info').append('[Produkte ohne Preise]');
					wait_dialog_hide();
				});
			});
			
			$("#info_critical_price_button").click(function() {
				wait_dialog_show();	
				var $post_data = new Object();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonProducts";
				$post_data['action'] = "listAmazonProducts";
				$post_data['accountsite_id'] = $accountSiteID;
				$post_data['limit'] = $limit;
				$post_data['addWhere'] = 'criticalPrice';
				$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
					try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
					if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}				
				
					amazonProductsTable($xml);
					$('.h3-info').empty();
					$('.h3-info').append('[Produkte mit Kritschen Preisen]');
					wait_dialog_hide();
				});
			});								
		});
	}
	
	/* 
	 * import Inventory Form
	 */		
	function importInventoryForm($accountSiteID)
	{
		$("#accountSiteID").val($accountSiteID);
		
		$("#import_form_dialog").dialog
		({	buttons:
			[
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText: "Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal: true,
			resizable: false,
			show: { effect: 'drop', direction: "up" },
			title: "Amazon Inventory Importieren",
			width: 600
		});			
	}	
	
	/* 
	 * test shop items prices
	 * 
	 */
	function testShopItemsPrices()
	{
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonShopItemsPriceGet";
		$post_data['MessageType'] = "Price";
		$post_data['action'] = "SubmitFeed";
		$post_data['FeedType'] = "_POST_PRODUCT_PRICING_DATA_";
		$post_data['id_account'] = 1;
		$post_data['limit'] = 0;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}					

			show_status2($data)
			wait_dialog_hide();
		});					
	}
	
	/* 
	 * amazon orders list
	 * list the latest orders form amazon orders import
	 */	
	function shopOrdersSippingStatusGet(shopId, id_account)
	{
		wait_dialog_show();
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonShippingUpdate";
		$post_data['limit'] = '1700';
		$post_data['shopId'] = shopId;
		$post_data['id_account'] = id_account;
		$post_data['MessageType'] = 'OrderFulfillment';
		$post_data['FeedType'] = '';
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}					

			show_status2($data)
			wait_dialog_hide();
		});
	}
		
	/**
	 * amazon Products
	 */	
	function amazonProductUpdate(id_account, limit)
	{
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonProductUpdate";
		$post_data['limit'] = limit;
		$post_data['id_account'] = id_account;
		$post_data['action'] = 'GetMatchingProductForId';
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			wait_dialog_show();
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			show_status2($data);
			wait_dialog_hide();
		});
	}
	
	/**
	 * 
	 */		
	function getAmazonSubmissionResult($id_account)
	{
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonSubmissionResultGet";
		$post_data['id_account'] = $id_account;
		$post_data['action'] = 'GetReportList';  //GetFeedSubmissionList //GetReportList

		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			$('h1').append('Amazon Submission Results');
			wait_dialog_show();
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			show_status2($data)
			wait_dialog_hide();
		});
	}

	/**
	 * show amazon products
	 */	
	function getAmazonProducts($id_account, $limit, $submitType, $request)
	{
		var $post_data = new Object();
		$post_data['API'] = "jobs";
		$post_data['APIRequest'] = "AmazonProductsJob";
		$post_data['id_account'] = $id_account;
		$post_data['limit'] = $limit;
		$post_data['submitType'] = $submitType;

		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			$('h1 span.h1').empty();
			$('h1 span.h1').append('Amazon Products');
			wait_dialog_show();
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}

			if ($request == 'view') {
				amazonProductsTable($xml);
			}
			wait_dialog_hide();
		});
	}
	
	/**
	 * show amazon shop items
	 */		
	function getAmazonShopItems($id_account, $limit)
	{	
		var $post_data = new Object();
		$post_data['API'] = "jobs";
		$post_data['APIRequest'] = "AmazonShopItemsJob";
		$post_data['id_account'] = $id_account;
		$post_data['limit'] = $limit;
		$post_data['delete'] = 0;
		$post_data['submitType'] = 'data';

		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			$('h1 span.h1').empty();
			$('h1 span.h1').append('Amazon Shop Items');
			wait_dialog_show();
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			amazonShopItemsTable($xml);
			wait_dialog_hide();
		});
	}
	
	/*
	 *--------------------------------------- Product Bundle ------------------------------------------------
	 */
	
	/**
	 * list amazon products bundles
	 */		
	function listAmazonProductsBundles($account_id, $accountsite_id, $limit)
	{
		$('#amazonProduct').empty();
		wait_dialog_show();

		var $post_data = new Object();
			$post_data['API'] = "amazon";
			$post_data['APIRequest'] = "AmazonProductsBundles";
			$post_data['action'] = 'listProductBundle';
			$post_data['account_id'] = $account_id;
			$post_data['accountsite_id'] = $accountsite_id;
			$post_data['limit'] = $limit;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				$('h1 span.h1').empty();	
				$('h1 span.h1').append('Amazon Produkt Bundles');
				wait_dialog_show();
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				
				$("#search-container").empty();
				amazonProductsBundleTable($xml, $account_id, $accountsite_id);
				showAmazonProductsBundlesItems(0, 0);
				wait_dialog_hide();
			});
	}
	
	/**
	 * show amazon products dialog
	 */		
	function addAmazonProducts($account_id, $accountsite_id)
	{
		$("#addProductTitle").val();
		$("#addProductSKU").val();
		$("#addProductEAN").val();
		
		wait_dialog_hide();
		$("#addProductDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() {  addAmazonProductsSave($account_id, $accountsite_id); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title: "Neues Produkt Bundle anlegen",
			width: 500
		});		
	}
	
	/**
	 * save add amazon products 
	 */		
	function addAmazonProductsSave($account_id, $accountsite_id)
	{
		wait_dialog_hide();
		var ProductBundleTitle = $("#addProductTitle").val();
		if (ProductBundleTitle == "") {alert("Bitte einen Namen für das Produkt Bundle eingeben"); $("#addProductBundleTitle").focus(); return;}
		var ProductBundleSKU = $("#addProductSKU").val();
		if (ProductBundleSKU == "") {alert("Bitte die SKU eingeben"); $("#addProductBundleSKU").focus(); return;}
		var ProductBundleEAN = $("#addProductEAN").val();
		if (ProductBundleEAN == "") {alert("Bitte die EAN eingeben"); $("#addProductBundleEAN").focus(); return;}		

		var $post_data = new Object();
			$post_data['API'] = "amazon";
			$post_data['APIRequest'] = "AmazonProductsBundles";
			$post_data['action'] = 'addProductBundle';
			$post_data['Title'] = ProductBundleTitle;
			$post_data['SKU'] = ProductBundleSKU;
			$post_data['accountsite_id'] = $accountsite_id
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() == 'Success') {
				wait_dialog_hide();
				$("#addProductDialog").dialog("close");
				show_status("Ein neues Produkt Bundle wurde erfolgreich angelegt");
				
				listAmazonProductsBundles($account_id, $accountsite_id, 500);
			} else  {
				wait_dialog_hide();
				show_status2($data);
				return;
			}
		});
	}
	
	/**
	 * edit amazon products bundles
	 */	
	function editAmazonProductsBundles($id_product, $account_id, $accountsite_id)
	{
		var $post_data = new Object();
        $post_data['API'] = "amazon";
        $post_data['APIRequest'] = "AmazonProductsBundles";
        $post_data['action'] = 'getProductBundle';
        $post_data['id_product'] = $id_product;
        $.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            wait_dialog_show();
            try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
            if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				
        $xml.find('AmazonProductsBundles').each(function()
        {
			$("#addProductTitle").val($(this).find("Title").text());
            $("#addProductSKU").val($(this).find('SKU').text());
			$("#addProductEAN").val($(this).find('EAN').text());

            wait_dialog_hide();
            $("#addProductDialog").dialog
                ({	buttons:
                    [
                        { text: "Speichern", click: function() { editAmazonProductsBundlesSave($id_product, $account_id, $accountsite_id); } },
                        { text: "Abbrechen", click: function() { $(this).dialog("close"); } }
                    ],
                    closeText:"Fenster schließen",
                    hide: { effect: 'drop', direction: "up" },
                    modal:true,
                    resizable:false,
                    show: { effect: 'drop', direction: "up" },
                    title:"Produkt Bundle beabeiten",
                    width: 500
                });
            });
        });
	}	
	
	function editAmazonProductsBundlesSave($id_product, $account_id, $accountsite_id)
	{
		wait_dialog_hide();
		var ProductBundleTitle = $("#addProductTitle").val();
		if (ProductBundleTitle == "") {alert("Bitte einen Namen für das Produkt Bundle eingeben"); $("#addProductBundleTitle").focus(); return;}
		var ProductBundleSKU = $("#addProductSKU").val();
		if (ProductBundleSKU == "") {alert("Bitte die SKU eingeben"); $("#addProductBundleSKU").focus(); return;}
		var ProductBundleEAN = $("#addProductEAN").val();
		if (ProductBundleEAN == "") {alert("Bitte die EAN eingeben"); $("#addProductBundleEAN").focus(); return;}		
		
		wait_dialog_show();

			var $post_data = new Object();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonProductsBundles";
				$post_data['action'] = 'editProductBundle';
				$post_data['Title'] = ProductBundleTitle;
				$post_data['SKU'] = ProductBundleSKU;
				$post_data['EAN'] = ProductBundleEAN;
				$post_data['id_product'] = $id_product;
				$post_data['account_id'] = $account_id;
				$post_data['accountsite_id'] = $accountsite_id;
				$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() == 'Success') {
					wait_dialog_hide();
					$("#addProductDialog").dialog("close");
					show_status("Das Produkt Bundle wurde erfolgreich bearbeitet");
										
					listAmazonProductsBundles($account_id, $accountsite_id, 500);
				} else  {
					wait_dialog_hide();
					show_status2($data);
					return;
				}
			}
		);
	}	
	
	/**
	 * delete amazon products bundles
	 */	
	function deleteAmazonProductsBundles($id_product, $account_id, $accountsite_id)
	{
		wait_dialog_show();
		var $post_data = new Object();
        $post_data['API'] = "amazon";
        $post_data['APIRequest'] = "AmazonProductsBundles";
        $post_data['action'] = 'getProductBundle';
        $post_data['id_product'] = $id_product;
        $.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            wait_dialog_show();
            try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
            if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			wait_dialog_hide();
			$("#deleteDialog").html("Soll das Produkt Bundle: <b>" + $xml.find("SKU").text() + "</b> wirklich gelöscht werden?");
			$("#deleteDialog").dialog
			({	buttons:
				[
					{ text: "Löschen", click: function() {  executeDeleteAmazonProductBundles($id_product, $account_id, $accountsite_id); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title: "Produkt Bundle löschen",
				width: 500
			});	
        });
	}	
	
	/**
	 * execute delete amazon products bundles
	 */	
	function executeDeleteAmazonProductBundles($id_product, $account_id, $accountsite_id)
	{
		wait_dialog_show();
		var $post_data = new Object();
        $post_data['API'] = "amazon";
        $post_data['APIRequest'] = "AmazonProductsBundles";
        $post_data['action'] = 'deleteProductBundle';
        $post_data['id_product'] = $id_product;
		$post_data['account_id'] = $account_id;
		$post_data['accountsite_id'] = $accountsite_id;
        $.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            wait_dialog_show();
            try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() == 'Success') {
				
				wait_dialog_hide();
				$("#deleteDialog").dialog("close");
				show_status("Produkt Bundle wurde gelöscht");
								
				listAmazonProductsBundles($account_id, $accountsite_id, 500);
			} else  {
				wait_dialog_hide();
				show_status2($data);
				return;
			}
		});
	}	
	
	/*
	 *--------------------------------------- Product Bundle Items ------------------------------------------------
	 */
	
	/**
	 * show amazon products bundles items
	 */	
	function showAmazonProductsBundlesItems($product_id, $sku)
	{
		if ($product_id == 0 && $sku == 0) {
			$('#amazonProduct').append('<div class="clear"></div>');
			return;	
		}
		var $post_data = new Object();
			$post_data['API'] = "amazon";
			$post_data['APIRequest'] = "AmazonProductsBundles";
			$post_data['action'] = 'listProductBundleItems';
			$post_data['product_id'] = $product_id;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				wait_dialog_show();
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				amazonProductsBundleItemTable($xml, $product_id, $sku);
				wait_dialog_hide();
			});		
	}	
	
	/**
	 * add amazon products bundle dialog
	 */		
	function addAmazonProductsBundlesItem(product_id, sku)
	{
		$('#SKU').append(sku);
		$("#addProductBundleSellerSKU").val();
		$("#addProductBundleQuantityOrdered").val();
		
		wait_dialog_hide();
		$("#addProductBundlesItemsDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { addAmazonProductsBundlesSave(product_id, sku); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title: "Item dem Produkt Bundle zuweisen",
			width: 500
		});		
	}	
	
	/**
	 * save add amazon products 
	 */		
	function addAmazonProductsBundlesSave(product_id, ProductBundlesSKU)
	{
		wait_dialog_hide();
		var ProductBundlesSellerSKU = $("#addProductBundlesSellerSKU").val();
		if (ProductBundlesSellerSKU == "") {alert("Bitte eine Seller SKU angeben"); $("#addProductBundlesSellerSKU").focus(); return;}	
		var ProductBundlesQuantityOrdered = $("#addProductBundlesQuantityOrdered").val();
		if (ProductBundlesQuantityOrdered == "") {alert("Bitte die Anzahl der Bestellungen angeben"); $("#addProductBundlesQuantityOrdered").focus(); return;}
		var ProductBundlesItemPriceAmount = $("#addProductBundlesItemPriceAmount").val();
		if (ProductBundlesItemPriceAmount == "") {alert("Bitte einen Preis angeben angeben"); $("#addProductBundlesItemPriceAmount").focus(); return;}

			wait_dialog_show();
			
			var $post_data = new Object();
				$post_data['API'] = "amazon";
				$post_data['APIRequest'] = "AmazonProductsBundles";
				$post_data['action'] = 'addProductBundleItem';
				$post_data['SKU'] = ProductBundlesSKU;
				$post_data['SellerSKU'] = ProductBundlesSellerSKU;
				$post_data['QuantityOrdered'] = ProductBundlesQuantityOrdered;
				$post_data['ItemPriceAmount'] = ProductBundlesItemPriceAmount;
				$post_data['product_id'] = product_id;
				$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() == 'Success') {
					wait_dialog_hide();
					$("#addProductBundlesItemsDialog").dialog("close");
					show_status("Item wurde dem Produkt Bundle erfolgreich zugewiesen");
					showAmazonProductsBundlesItems($xml.find('product_id').text(), $xml.find('SKU').text());
				} else  {
					wait_dialog_hide();
					show_status2($data);
					return;
				}
			}
		);
	}
	
	/**
	 * edit amazon products bundles items
	 */	
	function editAmazonProductsBundlesItem($id_bundle)
	{
		var $post_data = new Object();
        $post_data['API'] = "amazon";
        $post_data['APIRequest'] = "AmazonProductsBundles";
        $post_data['action'] = 'getProductBundleItem';
        $post_data['id_bundle'] = $id_bundle;
        $.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            wait_dialog_show();
            try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
            if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				
        $xml.find('AmazonProductsBundlesItem').each(function()
        {
			$("#addProductBundlesSellerSKU").val($(this).find("SellerSKU").text());
            $("#addProductBundlesQuantityOrdered").val($(this).find('QuantityOrdered').text());
            $("#addProductBundlesItemPriceAmount").val($(this).find('ItemPriceAmount').text());
			$("#addProductBundlesSellerSKU").val($(this).find("SellerSKU").text());

            wait_dialog_hide();
            $("#addProductBundlesItemsDialog").dialog
                ({	buttons:
                    [
                        { text: "Speichern", click: function() { editAmazonProductsBundlesItemSave($id_bundle); } },
                        { text: "Abbrechen", click: function() { $(this).dialog("close"); } }
                    ],
                    closeText:"Fenster schließen",
                    hide: { effect: 'drop', direction: "up" },
                    modal:true,
                    resizable:false,
                    show: { effect: 'drop', direction: "up" },
                    title: "Produkt Bundle Item bearbeiten",
                    width: 500
                });
            });
        });
	}
	
	/**
	 * save edit amazon products bundles items
	 */	
	function editAmazonProductsBundlesItemSave($id_bundle)
	{
		wait_dialog_hide();
		var ProductBundlesSellerSKU = $("#addProductBundlesSellerSKU").val();
		if (ProductBundlesSellerSKU == "") {alert("Bitte eine Seller SKU angeben"); $("#addProductBundlesSellerSKU").focus(); return;}
		
		var ProductBundlesQuantityOrdered = $("#addProductBundlesQuantityOrdered").val();
		if (ProductBundlesQuantityOrdered == "") {alert("Bitte die Anzahl der Bestellungen angeben"); $("#addProductBundlesQuantityOrdered").focus(); return;}
		
		var ProductBundlesItemPriceAmount = $("#addProductBundlesItemPriceAmount").val();
		if (ProductBundlesItemPriceAmount == "") {alert("Bitte einen Preis angeben angeben"); $("#addProductBundlesItemPriceAmount").focus(); return;}
		
		wait_dialog_show();

		var $post_data = new Object();
			$post_data['API'] = "amazon";
			$post_data['APIRequest'] = "AmazonProductsBundles";
			$post_data['action'] = 'editProductBundleItem';
			$post_data['SellerSKU'] = ProductBundlesSellerSKU;
			$post_data['QuantityOrdered'] = ProductBundlesQuantityOrdered;
			$post_data['ItemPriceAmount'] = ProductBundlesItemPriceAmount;
			$post_data['id_bundle'] = $id_bundle;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() == 'Success') {
				wait_dialog_hide();
				$("#addProductBundlesItemsDialog").dialog("close");
				show_status("Das Produkt Bundle Item wurde erfolgreich bearbeitet");
				listAmazonProductsBundles(1, 20, 1);
			} else  {
				wait_dialog_hide();
				show_status2($data);
				return;
			}
		});
	}	
	
	/**
	 * delete amazon products bundles items dialog
	 */	
	function deleteAmazonProductsBundlesItem($id_bundle)
	{
		wait_dialog_show();
		var $post_data = new Object();
        $post_data['API'] = "amazon";
        $post_data['APIRequest'] = "AmazonProductsBundles";
        $post_data['action'] = 'getProductBundleItem';
        $post_data['id_bundle'] = $id_bundle;
        $.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            wait_dialog_show();
            try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
            if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			wait_dialog_hide();
			$("#deleteDialog").html("Soll das Produkt Bundle Item: <b>" + $xml.find("SKU").text() + "</b> wirklich gelöscht werden?");
			$("#deleteDialog").dialog
			({	buttons:
				[
					{ text: "Löschen", click: function() {  executeDeleteProductBundleItem($id_bundle); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title: "Produkt Bundle Item löschen",
				width: 500
			});	
        });
	}	
	
	/**
	 * execute delete amazon products bundles items
	 */	
	function executeDeleteProductBundleItem($id_bundle)
	{
		wait_dialog_show();
		var $post_data = new Object();
        $post_data['API'] = "amazon";
        $post_data['APIRequest'] = "AmazonProductsBundles";
        $post_data['action'] = 'deleteProductBundleItem';
        $post_data['id_bundle'] = $id_bundle;
        $.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            wait_dialog_show();
            try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
            if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			wait_dialog_hide();
			$("#deleteDialog").dialog("close");
			show_status("Produkt Bundle Item wurde gelöscht");
			// fix the problem with reload after delete
			listAmazonProductsBundles(1, 20, 1);
		});
	}	
</script>

<?php
	echo '<div id="breadcrumbs" class="breadcrumbs">';
	echo '	<a href="backend_index.php">Backend</a>';
	echo ' 		&#187; <a href="">Amazon</a>';
	echo ' 		&#187; <a href="/backend_amazon_products.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID']. '">Amazon Produkt Liste</a>';
	echo '</div>';
	echo '
		<h1>
			<span class="h1"></span>
			<button style="float:right;margin: 2px 0 0 5px;" id="product-bundles" onclick="javascript:listAmazonProductsBundles(' . $get['accountID'] . ',' . $get['accountsiteID'] . ', \'500\');">Produkt Bundles</button>
			<a href="backend_amazon_categories.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID'] . '"><button>Amazon Kategorien</button></a>
			<a href="backend_amazon_products.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID'] . '"><button>Amazon Produkte</button></a>
			<a href="backend_amazon_orders_list.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID'] . '"><button>Amazon Bestellungen</button></a>
		</h1>';

	if ($_SESSION['id_user'] == '88837') {
		echo '<div class="developer-toolbar">My Developer Toolbar:';
		echo ' <a href="' . $_SERVER['REQUEST_URI'] . '&set=StandardPriceImport">Set StandardPriceImport</a>';
		echo ' | <a href="' . $_SERVER['REQUEST_URI'] . '&set=QuantityImport">Set QuantityImport</a>';
		echo ' | <a href="' . $_SERVER['REQUEST_URI'] . '&set=ImageImport">Set ImageImport</a>';
		echo ' | <a href="' . $_SERVER['REQUEST_URI'] . '&set=importTopPrice">Set ImportTopPrice</a>';
		echo '</div>';	
	}

	echo '<div id="content-wrapper">';
		echo '<div id="stats-wrapper"></div>';
		echo '
			<div class="search-wrapper">
				<form id="form-search">
					<div id="search-container" class="search">
						<label>Suche: </label>
						<input type="search" name="searchSKU" id="searchSKU" placeholder="SKU"/>
						<input type="search" name="searchASIN" id="searchASIN" placeholder="ASIN"/>
						<button id="search-button" type="button">Suche</button>';
						
						if (!empty($get['orderBy']) && $get['orderBy'] == 'StandardPriceDown') 
						{
							$orderByStandardPriceDown = $_SERVER['REQUEST_URI'];
						} else {
							if (!empty($get['orderBy']) && $get['orderBy'] == 'StandardPriceUp') 
							{
								$orderByStandardPriceDown = str_replace('&orderBy=StandardPriceUp', '&orderBy=StandardPriceDown', $_SERVER['REQUEST_URI']);
							} else {
								$orderByStandardPriceDown = $_SERVER['REQUEST_URI'] . '&orderBy=StandardPriceDown';	
							}
						}
						
						if (!empty($get['orderBy']) && $get['orderBy'] == 'StandardPriceUp') 
						{
							$orderByStandardPriceUp = $_SERVER['REQUEST_URI'];
						} else {
							if (!empty($get['orderBy']) && $get['orderBy'] == 'StandardPriceDown') 
							{
								$orderByStandardPriceUp = str_replace('&orderBy=StandardPriceDown', '&orderBy=StandardPriceUp', $_SERVER['REQUEST_URI']);
							} else {
								$orderByStandardPriceUp = $_SERVER['REQUEST_URI'] . '&orderBy=StandardPriceUp';
							}
						}					
						echo '
							<span class="sortable">
								<a href="' . $orderByStandardPriceDown . '">Zeige höchste Preise</a>
								<a href="' . $orderByStandardPriceUp . '">Zeige tiefste Preise</a>
							</span>';
						echo '
					</div>
				</form>
				<div class="convert-ean">
					<input type="search" name="convertASIN" id="convertASIN" placeholder="ASIN"/>
					<button id="ean-convert" type="button">Convert</button>
					<span class="result-ean">
						<span id="ean-result" class="convert-ean"></span>
						<span id="listPriceFormattedPrice-result" class="convert-ean"></span>
						<br />
						<span id="salesRank-result" class="convert-ean"></span>						
					</span>
				</div>
				<div class="clear"></div>
			</div>';
		
		//	ADD PRODUCT DIALOG
		$addProductDialog = '
			<div id="addProductDialog" style="display:none">
				<table>
					<tr>
						<td><strong>Title</strong></td>
						<td><input type="text" name="title" id="addProductTitle" size="40" /></td>
					</tr><tr>
						<td><strong>SKU</strong></td>
						<td>
							<input type="text" name="SKU" id="addProductSKU" size="40" s/>
							<span class="tipp">Keine Leerzeichen oder spezielle Sonderzeichen verwenden.</span>
						</td>	
					</tr>
						<tr>
						<td><strong>EAN</strong></td>
						<td>
							<input type="text" name="EAN" id="addProductEAN" size="40" s/>
							<span class="tipp">Haupt-EAN benutzen zum anhängen</span>
						</td>	
					</tr>					
				</table>
				<div class="alert alert-info">
					<ul>
						<li><strong>Titel: </strong>Gebe für den Titel eine aussagenkräftige Bezeichnung an, die keine speziellen Automarkeninformationen enthält.</li>
						<li><strong>SKU: </strong>Die SKU darf keine vorhandene SKU (MPN) sein, sondern eine einmalig ausgedachte.</li>
					<ul>
				</div>
			</div>';
		echo $addProductDialog;
		
		//	IMPORT INVENTORY FORM
		$importForm = '<div style="display:none;" id="import_form_dialog">
			<form method="post" enctype="multipart/form-data">
			Marktplatz: <select id="select_marketplaces" name="id_marketplace">';

				$data = array();
				$data['from'] = 'amazon_marketplaces';
				$data['select'] = '*';
				$amazonMarketplacesResults = SQLSelect($data['from'], $data['select'], 0, 0, 0, 0, 'shop',  __FILE__, __LINE__);
				foreach ($amazonMarketplacesResults as $amazonMarketplaces)
				{
					$importForm.= '<option value="' . $amazonMarketplaces["id_marketplace"] . '">' . $amazonMarketplaces["name"] . '</option>';
				}
		$importForm.= '</select><br />
					<input type="file" name="file" />
					<input type="hidden" name="accountSiteID" id="accountSiteID" value="" />
					<input type="submit" value="Hochladen" />
				</form>
			</div>';
		echo $importForm;
		
		if (isset($_FILES["file"])) {
			//	clear table
			q("
				DELETE FROM amazon_inventory
				WHERE accountsite_id = " . $post['accountSiteID'] . ";", $dbshop, __FILE__, __LINE__);
				
			$inventory = array();
			$handle = fopen($_FILES["file"]["tmp_name"], "r");
			$line = fgetcsv($handle, 4096, ";");
			while($line = fgetcsv($handle, 4096, ";"))
			{
				$inventory[] = "(" . $post['accountSiteID'] . ", '".mysqli_real_escape_string($dbweb, $line[0])."', '".mysqli_real_escape_string($dbweb, $line[1])."', '".mysqli_real_escape_string($dbweb, $line[2])."', '".mysqli_real_escape_string($dbweb, $line[3])."')";
			}
			fclose($handle);
			$amazonInventoryQuery = "
				INSERT INTO amazon_inventory (
					accountsite_id,
					sku, 
					asin, 
					price, 
					quantity) VALUES " . implode(", ", $inventory) . ";";
			q($amazonInventoryQuery, $dbshop, __FILE__, __LINE__);
			echo 'Inventory erfolgreich  importiert.';
			exit;
		}				
		
		//	ADD PRODUCT BUNDLES DIALOG
		$addProductBundlesDialog = '
			<div id="addProductBundlesItemsDialog" style="display:none">
				<div>Zuweisung für SKU</div>
				<table>
					<tr>
						<td><strong>MPN</strong></td>
						<td>
							<input type="text" name="SellerSKU" id="addProductBundlesSellerSKU" size="40" />
							<span class="tipp">Die zuordbare MPN angeben.</span>
						</td>
					</tr><tr>
						<td><strong>Anzahl der Bestellungen</strong></td>
						<td><input type="text" name="QuantityOrdered" id="addProductBundlesQuantityOrdered" size="10" /></td>						
					</tr><tr>
						<td><strong>Preis</strong></td>
						<td>
							<input type="text" name="ItemPriceAmount" id="addProductBundlesItemPriceAmount" size="10" />
							<span class="tipp">Es muss der Brutto Preis des Items angegeben werden.</span>
						</td>	
					</tr>
				</table>
				<div class="alert alert-info">
					<ul>
						<li><strong>MPN:</strong> Mit der Angabe der MPN wird dem Produkt Bundle eine Item SKU zugeweisen.</li>
						<li><strong>Anzahl der Bestellungen:</strong> Wenn das Produkt Item nur im Satz verkauft werden kann, muss hier die genaue Anzahl angegeben werden. Ansonsten nur mit 
						der Stückzahl 1.</li>
						<li><strong>Preis:</strong> Hier muss der Brutto-Einzelpreis des Items angegeben werden. Auch, wenn es sich um einen Satz handelt. Dieser wird dann durch die Anzahl berechet.</li>
					<ul>
				</div>				
			</div>';
		echo $addProductBundlesDialog;
		
		//	DELETE DIALOG
		echo '<div id="deleteDialog" style="display:none"></div>';
		echo '<div id="amazonProduct" class="widget-listing"></div>';
	echo '</div>';

	//	loading....
	echo '<script src="//datatables.net/download/build/nightly/jquery.dataTables.js"></script>';
	echo '<script src="' . PATH . 'javascript/cms/DataTablesConfig.php" type="text/javascript"></script>';
	echo '<script src="' . PATH . 'javascript/amazon/TableViewProducts.php" type="text/javascript"></script>';
	echo '<script type="text/javascript">listAmazonProducts(' . $get['accountID'] . ',' . $get['accountsiteID'] . ',\'150\',\'' . $get['set'] . '\',\'' . $get['orderBy'] . '\');</script>';
	include("templates/".TEMPLATE_BACKEND."/footer.php");