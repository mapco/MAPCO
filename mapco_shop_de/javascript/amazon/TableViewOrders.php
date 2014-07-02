<?php

/**
 *	@author: rlange@mapco.de
 *	- Table Collection for Amazon Orders View
 */
 
header('Content-type: text/javascript');

	//make dreamweaver highlight javascript
	if (true == false) { ?> <script type="text/javascript"> <?php }
?>

	/*
	 *--------------------------------------- Orders and Items Tables ------------------------------------------------
	 */	

	/**
	 * view amazon orders table
	 */	
	function amazonOrdersTable($xml)
	{		
		var headline = $('<h3>Amazon Orders für den Maktplatz: ' + $xml.find('MarketplacesName').text() + '</h3>');
		var table = $('<table id="datatableAmazonOrders" class="listing display"></table>');
				var thead = $('<thead></thead>');
				var tbody = $('<tbody></tbody>');
				var tr = $('<tr></tr>');
				var td = $('<td></td>');
				var th = $('<tr><th>STID</th><th>ImportStatus</th><th>Bestelldatum</th><th>Vertriebskanal</th><th>Bestellnummer / Produktdetails</th><th>Käufer / DHL Trackingcode</th><th>Versandart</th><th>Status</th><th>Lieferdatum</th><th>Firstmod</th><th>Lastmod</th></tr>');
				thead.append(th);
				table.append(thead);				
				var row;
				$xml.find('AmazonOrder').each(function()
				{
					var trStatusView = "";
					var importStatusView = '<span class="label label-danger">No</span>';
					if ($(this).find('OrderStatus').text() == 'Canceled') { trStatusView = 'class="danger"' }
					if ($(this).find('OrderStatus').text() == 'Pending') { trStatusView = 'class="tipp"' }
					if ($(this).find('importShopStatus').text() >= 4) { trStatusView = 'class="done"' }
					if ($(this).find('importShopStatus').text() >= 2) { importStatusView = '<span class="label label-success">Yes</span>' }
					
					row = '<tr ' + trStatusView + '>'
							+ '<td class="center">' + $(this).find('shippingStatusId').text() + '</td>'
							+ '<td class="center">' + importStatusView + '</td>'
							+ '<td class="date center">' + $(this).find('PurchaseDate').text() + '</td>'
							+ '<td class="center">' + $(this).find('SalesChannel').text() + '</td>'
							+ '<td>' + '<a href="javascript:viewAmazonOrderItemById(\'' + $(this).find('AmazonOrderId').text() + '\')">'
							+ $(this).find('ProductDetails').text() + '</a>'
							+ '<br><span class="info-subline">' + $(this).find('ShipServiceLevel').text()
							+ ' | ' + $(this).find('ShippingAddressCountryCode').text() + ' | Items: ' + $(this).find('amazonOrderItemsCount').text() + '</span></td>'
							+ '<td>' + $(this).find('ShippingAddressName').text()
							+ '<br><span class="info-subline">' + $(this).find('shippingNumber').text() + '</span></td>'
							+ '<td class="center">' + $(this).find('ShipmentServiceLevelCategory').text() + '</td>'
							+ '<td class="center">' + $(this).find('OrderStatus').text() + '</td>'
							+ '<td class="date center">' + $(this).find('LatestDeliveryDate').text() + '</td>'
							+ '<td class="date center">' + $(this).find('firstmod').text() + '</td>'
							+ '<td class="date center">' + $(this).find('lastmod').text() + '</td>'
						+ '</tr>'; 
						tbody.append(row);
				});

		$('#amazonOrders').empty();		
		$('#amazonOrders').append(headline);
		table.append(tbody);
		$('#amazonOrders').append(table);
		
		$tableOptions = {
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"iDisplayLength": 25,
    		"aLengthMenu": [[25, 50, 100], [25, 50, 100]]
		};		
		$('#datatableAmazonOrders').dataTable($tableOptions);			
	}
	
	/**
	 * view amazon orders item table
	 */	
	function amazonOrderItem($xml)
	{		
		var headline = $('<h3>Amazon Order Item</h3>');
		var table = $('<table class="listing"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		table.append(thead);	
		var row;
		var amazonOrderIDLink;
		$xml.find('amazon_order_items').each(function()
		{							
			amazonOrderIDLink = '<a href="https://sellercentral.amazon.de/gp/orders-v2/details?ie=UTF8&orderID=' + $(this).find('AmazonOrderId').text() + '" target="_blank">'
				+ $(this).find('AmazonOrderId').text() + '</a>';
		
			row = '<tr>'
					+ '<td>Bestellnr.: # ' + amazonOrderIDLink + '</td>'
					+ '<td><strong>' + $(this).find('Title').text() + '</strong><br>Menge: ' + $(this).find('QuantityOrdered').text()
					+ '<br>SKU: ' + $(this).find('SellerSKU').text() + '<br> ASIN: ' + $(this).find('ASIN').text()
					+ '<br>Zustand: ' + $(this).find('ConditionId').text() + '</td>'
				+ '</tr>'; 
				tbody.append(row);
		});

		$('#amazonOrders').empty();		
		$('#amazonOrders').append(headline);
		table.append(tbody);
		$('#amazonOrders').append(table);		
	}