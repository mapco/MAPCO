<?php

/**
 *	@author: rlange@mapco.de
 *	- Table Collection for Amazon Products View
 */
 
header('Content-type: text/javascript');

	//make dreamweaver highlight javascript
	if (true == false) { ?> <script type="text/javascript"> <?php }
?>
	/*
	 *--------------------------------------- Products, Bundles and Items Tables ------------------------------------------------
	 */

	/**
	 * show amazon products table
	 */
	function amazonProductsTable($xml)
	{
		var headline = $('<h3>Amazon Produkte für den Maktplatz: ' + $xml.find('MarketplacesName').text() + '<span class="h3-info"></span></h3>');
		var table = $('<table id="datatableAmazonProducts" class="listing display"></table>');
		var thead = $('<thead></thead>');
		var tfoot = $('<tfoot></tfoot>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<tr><th>ProductID</th><th>SKU</th><th>Titel <br /><small>ASIN / EAN / GART / ArticleID / ItemID</small></th><th>Image</th><th>StandardPrice</th><th>SPS</th><th>LPU</th><th>Quantity</th><th>LQU</th><th>ProductExport</th></tr>');

		thead.append(th);
		table.append(thead);
		var row;
		
		//	amazon offer
		var standardPrice;
		var topPrice;
		var asinLink;
		var subTitle;
		$xml.find('AmazonProduct').each(function()
		{
			//	check standardPrice and TopPrice
			if (Number($(this).find('StandardPrice').text()) > Number($(this).find('TopPrice').text())) 
			{
				if ($(this).find('TopPrice').text() == 0) 
				{
					standardPrice = '<span class="msg-info">' + $(this).find('StandardPrice').text() + '</span>';
				} else {
					standardPrice = '<span class="msg-error">' + $(this).find('StandardPrice').text() + '</span>';	
				}
			} else {
				if ($(this).find('StandardPrice').text() == 0) 
				{
					standardPrice = '<span class="msg-warning">' + $(this).find('StandardPrice').text() + '</span>';
				} else {
					standardPrice = '<span class="msg-success">' + $(this).find('StandardPrice').text() + '</span>';
				}
			}
				
			//	check product quantity
			if ($(this).find('Quantity').text() > 0) 
			{
				quantity = '<span class="label label-success">' + $(this).find('Quantity').text() + '</span>'
			} else {
				quantity = '<span class="label label-danger">' + $(this).find('Quantity').text() + '</span>'
			}
			
			//	get TopPrice
			if ($(this).find('TopPrice').text() > 0) 
			{
				topPrice = '<span class="info-subline">' + $(this).find('TopPrice').text() + '</span>';
			} else {
				topPrice = '<span class="info-subline">' + 'n/a' + '</span>';
			}
			
			// get CriticalPrice
			if ($(this).find('CriticalPrice').text() == 1) 
			{
				criticalPrice = '<i class="fa fa-thumbs-down ico-danger"></i>';
			} else {
				if (Number($(this).find('StandardPrice').text()) == 0) 
				{
					criticalPrice = '<i class="fa fa-flash ico-warning"></i>';
				} else {
					criticalPrice = '<i class="fa fa-thumbs-up ico-success"></i>';	
				}
			}
			
			if ($(this).find('ASIN').text() != 0) 
			{
				asinLink = '<a target="_blank" href="http://www.amazon.de/gp/offer-listing/' + $(this).find('ASIN').text() + '">' + $(this).find('ASIN').text() + '</a>';
			} else {
				asinLink = 'keine';
			}
			if ($(this).find('SubTitle').text() != 0)
			{
				subTitle = ' - ' + $(this).find('SubTitle').text();	
			} else {
				subTitle = "";	
			}

			var trStatusView = "";
			if ($(this).find('Bundle').text() > 0) { trStatusView = 'class="tipp"' }

			row = '<tr ' + trStatusView + '>'
					+ '<td class="center">' + $(this).find('ProductID').text() + '</td>'
					+ '<td class="center">' + $(this).find('SKU').text() + '</td>'
					+ '<td>' + $(this).find('Title').text() + subTitle
					+ '<br /><span class="info-subline">' + asinLink
					+ ' / ' + $(this).find('EAN').text() + ' / ' + $(this).find('GART').text() + ' / ' + $(this).find('ArticleID').text()
					+ ' / ' + $(this).find('ItemID').text() + '</span>'
					+ '</td>'
					+ '<td class="center">' + $(this).find('submitedImage').text() + '</td>'
					+ '<td>' + criticalPrice + standardPrice + '<br>' + topPrice + '</td>'
					+ '<td>' + $(this).find('StandardPriceSuggestion').text() + '</td>'
					+ '<td class="date center">' + $(this).find('submitedPrice').text() + $(this).find('lastpriceupdate').text() + '</td>'
					+ '<td class="center">' + quantity + '</td>'
					+ '<td class="date center">' + $(this).find('submitedQuantity').text() + $(this).find('lastquantityupdate').text() + '</td>'
					+ '<td class="date center">' + $(this).find('submitedProduct').text() + $(this).find('submitedProductDate').text() + '</td>'
				+ '</tr>';
				tbody.append(row);
		});
		table.append(tbody);

		$('#amazonProduct').empty();
		$('#amazonProduct').append('<div class="alert alert-warning">Gelb angezeigte Listen sind Produkt-Bundles</div>');
		$('#amazonProduct').append(headline);
		$('#amazonProduct').append(table);

		$tableOptions = {
			//"fnSort": [[1,'desc']],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"iDisplayLength": 25,
			"bSort": false,
    		"aLengthMenu": [[25, 50, 100], [25, 50, 100]]
		};
		$('#datatableAmazonProducts').dataTable($tableOptions);
	}

	/**
	 * view amazon shop items table
	 */
	function amazonShopItemsTable($xml)
	{
		var headline = $('<h3>Amazon Shop Items</h3>');
		var table = $('<table class="logfile"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<th>ItemID</th><th>Titel</th><th>Marke</th><th>MPN</th><th>GART</th><th>EAN</th><th>Artikle ID</th><th>Bild Position</th><th>Price</th><th>Quantity</th><th>BrowseNode</th><th>BulletPoints</th>');

		thead.append(th)
		table.append(thead);
		var row;
		$xml.find('ShopItems').each(function()
		{
			row = '<tr><td class="center">' + $(this).find('ItemID').text() + '</td>'
					+ '<td>' + $(this).find('Title').text() + '</td>'
					+ '<td>' + $(this).find('Brand').text() + '</td>'
					+ '<td>' + $(this).find('MPN').text() + '</td>'
					+ '<td class="center">' + $(this).find('GART').text() + '</td>'
					+ '<td>' + $(this).find('EAN').text() + '</td>'
					+ '<td>' + $(this).find('ArticleID').text() + '</td>'
					+ '<td>' + $(this).find('ImageLocation').text() + '</td>'
					+ '<td>' + $(this).find('StandardPrice').text() + '</td>'
					+ '<td>' + $(this).find('Quantity').text() + '</td>'
					+ '<td>' + $(this).find('RecommendedBrowseNode').text() + '</td>'
					+ '<td>' + $(this).find('BulletPoints').text() + '</td>'
				+ '</tr>';
				tbody.append(row);
		});
		table.append(tbody);

		$('#amazonProduct').empty();
		$('#amazonProduct').append(headline);
		$('#amazonProduct').append(table);
	}

	/**
	 * view amazon products bundle table
	 */
	function amazonProductsBundleTable($xml, $account_id, $accountsite_id)
	{
		$('#stats-wrapper').empty();

		$addIcon = 'images/icons/24x24/add.png';
		$addLang = 'Neues Produkt Bundle anlegen';
		$add = '<img class="btn button-add" onclick="addAmazonProducts(' + $account_id + ',' + $accountsite_id + ')" title="' + $addLang + '" alt="' + $addLang + '" src="' + $addIcon + '">';

		$editIcon = 'images/icons/24x24/edit.png';
		$editLang = 'Produkt Bundle bearbeiten';

		$removeIcon = 'images/icons/24x24/remove.png';
		$removeLang = 'Produkt Bundle löschen';

		var headline = $('<h3>Amazon Produkt Bundles</h3>');
		var table = $('<table class="listing list-left"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<th>Lastmod</th><th>Titel</th><th>Preis</th><th>Quantity</th><th class="options">' + $add + '</th>');

		thead.append(th);
		table.append(thead);
		var row;
		$xml.find('AmazonProductsBundles').each(function()
		{
			$id_product = $(this).find('id_product').text();
			$SKU = "\'" + $(this).find('SKU').text() + "\'";
			$title = $(this).find('Title').text();

			row = '<tr>'
					+ '<td class="date center">' + $(this).find('lastmod').text() + '</td>'
					+ '<td class="title"><a href="javascript:showAmazonProductsBundlesItems(' + $id_product + ',' + $SKU + ')">' + $title + '</a>'
					+ '	<br /><span class="info-subline">SKU: ' + $(this).find('SKU').text() + ' / EAN: ' + $(this).find('EAN').text() + '</span>'
					+ '</td>'
					+ '<td class="center">' + $(this).find('StandardPrice').text() + '</td>'
					+ '<td class="center">' + $(this).find('Quantity').text() + '</td>'
					+ '<td>'
						+ '<img class="btn button-remove" onclick="deleteAmazonProductsBundles(' + $id_product + ',' + $account_id + ',' + $accountsite_id + ')" title="' + $removeLang + '" alt="' + $removeLang + '" src="' + $removeIcon + '">'
						+ '<img class="btn button-edit" onclick="editAmazonProductsBundles(' +  $id_product + ',' + $account_id + ',' + $accountsite_id + ')" title="' + $editLang + '" alt="' + $editLang + '" src="' + $editIcon + '">'
					+ '</td>'
				+ '</tr>';
			tbody.append(row);
		});
		table.append(tbody);

		$('#amazonProduct').empty();
		$('#stats-wrapper').hide();
		$('#amazonProduct').append(headline);
		$('#amazonProduct').append(table);
	}

	/**
	 * show amazon products bundles items table
	 */
	function amazonProductsBundleItemTable($xml, $product_id, $sku)
	{
		if ($("#subcontent").length  == 0) {
			$('.clear').css("display","none");
			$('#amazonProduct').append($('<div id="subcontent"></div>'));
		}
		$addIcon = 'images/icons/24x24/add.png';
		$addLang = 'Product Bundle zuweisen';
		$addProductSKU = "\'" + $sku + "\'";
		$add = '<img class="btn button-add" onclick="addAmazonProductsBundlesItem(' + $product_id + ',' + $addProductSKU + ')" title="' + $addLang + '" alt="' + $addLang + '" src="' + $addIcon + '">';

		$editIcon = 'images/icons/24x24/edit.png';
		$editLang = 'Produkt Bundle Item bearbeiten';

		$removeIcon = 'images/icons/24x24/remove.png';
		$removeLang = 'Produkt Bundle Item löschen';

		var table = $('<table class="listing list-right"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<th>Produkt ID</th><th>SKU</th><th>MPN</th><th>Anzahl</th><th>Preis</th><th>Quantity</th><th>' + $add + '</th>');

		thead.append(th);
		table.append(thead);
		var row;
		$xml.find('AmazonProductsBundlesItem').each(function()
		{
			$id_bundle = $(this).find('id_bundle').text();
			row = '<tr>'
					+ '<td class="center">' + $(this).find('product_id').text() + '</td>'
					+ '<td class="center">' + $(this).find('SKU').text() + '</td>'
					+ '<td class="center">' + $(this).find('SellerSKU').text() + '</td>'
					+ '<td class="center">' + $(this).find('QuantityOrdered').text() + '</td>'
					+ '<td class="center">' + $(this).find('ItemPriceAmount').text() + '</td>'
					+ '<td class="center">' + $(this).find('Quantity').text() + '</td>'
					+ '<td>'
						+ '<img class="btn button-remove" onclick="deleteAmazonProductsBundlesItem(' + $id_bundle + ')" title="' + $removeLang + '" alt="' + $removeLang + '" src="' + $removeIcon + '">'
						+ '<img class="btn button-edit" onclick="editAmazonProductsBundlesItem(' +  $id_bundle + ')" title="' + $editLang + '" alt="' + $editLang + '" src="' + $editIcon + '">'
					+ '</td>'
				+ '</tr>';
			tbody.append(row);
		});
		table.append(tbody);

		$('#subcontent').empty();
		$('#subcontent').append(table);
		$('#amazonProduct').append('<div class="clear"></div>');
	}
