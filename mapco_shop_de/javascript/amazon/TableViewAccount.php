<?php

/**
 *	Created by RLange on 02.04.14.
 *	- Table Collection for Amazon Account View
 */
header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if (true == false) { ?> <script type="text/javascript"> <?php }
?>

	/*
	 *--------------------------------------- Accounts and Accounts Tables ------------------------------------------------
	 */

	/**
	 * show amazon accounts table
	 */		 	
	function amazonAccountsTable($xml, $container)
	{
		var content = $('<div id="mainContent" class="widget-listing widget-left"></div>');
		$($container).append(content);
		
		$addIcon = 'images/icons/24x24/add.png';
		$addLang = 'Account anlegen';
		$add = '<img class="btn button-add" onclick="addAmazonAccounts()" title="' + $addLang + '" alt="' + $addLang + '" src="' + $addIcon + '">';
		
		$editIcon = 'images/icons/24x24/edit.png';
		$editLang = 'Account Bearbeiten';
		
		$removeIcon = 'images/icons/24x24/remove.png';
		$removeLang = 'Account löschen';
			
		var headline = $('<h3>Accounts</h3>');
		var table = $('<table class="listing"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<tr><th>Titel / Beschreibung</th><th>' + $add + '</th></tr>');
		thead.append(th);
		table.append(thead);	
		var row;
		$xml.find('amazon_accounts').each(function()
		{
			$id_account = $(this).find('id_account').text();
			$title = $(this).find('title').text();
			$listLink = '<a href="javascript:showAmazonAccountsSitesByAccountId(' + $id_account + ')">' + $title + '</a>';
			+ '<img class="btn button-remove" onclick="deleteAmazonAccounts(' + $id_account + ')" title="' + $removeLang + '" alt="' + $removeLang + '" src="' + $removeIcon + '">';
			
			row = '<tr>'
					+ '<td><strong>' + $listLink + '</strong><br>' + $(this).find('description').text() + '</td>'
					+ '<td>'
						+ '<img class="btn button-edit" onclick="editAmazonAccounts(' +  $id_account + ')" title="' + $editLang + '" alt="' + $editLang + '" src="' + $editIcon+ '">'
					+ '</td>'
				+ '</tr>'; 
				tbody.append(row);
		});
		table.append(tbody);

		$($container + ' #mainContent').append(headline);
		$($container + ' #mainContent').append(table);
		$($container).append('<div class="clear"></div>');
	}
	 
	/**
	 * show amazon accounts sites table
	 */		 	
	function amazonAccountsSitesTable($xml, $account_id, $container)
	{
		if ($("#subContent").length  == 0) {
			$('.clear').css("display","none");
			$($container).append($('<div id="subContent" class="widget-listing widget-right"></div>'));
		}
		
		$addIcon = 'images/icons/24x24/add.png';
		$addLang = 'Accounts Site anlegen';
		$add = '<img class="btn button-add" onclick="addAmazonAccountsSites(' + $account_id + ')" title="' + $addLang + '" alt="' + $addLang + '" src="' + $addIcon + '">';
			
		$editIcon = 'images/icons/24x24/edit.png';
		$editLang = 'Account Site Bearbeiten';
		
		$removeIcon = 'images/icons/24x24/remove.png';
		$removeLang = 'Amazon Site löschen';		
						
		$categoriesIcon = 'images/icons/24x24/page_swap.png';
		$categoriesLang = 'Amazon Kategorien';
		
		$productsIcon = 'images/icons/24x24/database_accept.png';
		$productsLang = 'Amazon Produkte';
		
		$ordersIcon = 'images/icons/24x24/shopping_cart.png';
		$ordersLang = 'Amazon Bestellungen';				
		
		var headline = $('<h3>Sites</h3>');
		var table = $('<table class="listing"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<tr><th>SiteID</th><th>Active</th><th>Code</th><th>Title</th><th>' + $add + '</th></tr>');
		thead.append(th);
		table.append(thead);	
		var row;
		$xml.find('amazon_accounts_sites').each(function()
		{
			var active = '<span class="label label-danger">No</span>';
			if ($(this).find('active').text() == 1) { active = '<span class="label label-success">Yes</span>' }
			
			$account_id = $(this).find('account_id').text();
			$accountsite_id = $(this).find('id_accountsite').text();
			$buttonCategories = '<img class="btn button-list" title="' + $categoriesLang + '" alt="' + $categoriesLang + '" src="' + $categoriesIcon + '">';
			$listCategoriesLink = 'backend_amazon_categories.php?accountID=' + $account_id + '&accountsiteID=' + $accountsite_id;
			
			$buttonProducts = '<img class="btn button-list" title="' + $productsLang + '" alt="' + $productsLang + '" src="' + $productsIcon + '">';
			$listProductsLink = 'backend_amazon_products.php?accountID=' + $account_id + '&accountsiteID=' + $accountsite_id;
			
			$buttonOrders = '<img class="btn button-list" title="' + $ordersLang + '" alt="' + $ordersLang + '" src="' + $ordersIcon + '">';
			$listOrdersLink = 'backend_amazon_orders_list.php?accountID=' + $account_id + '&accountsiteID=' + $accountsite_id;			
			
			row = '<tr>'
				+ '<td class="center">' + $accountsite_id + '</td>'
					+ '<td class="center">' + active + '</td>'
					+ '<td class="center">' + $(this).find('country_code').text() + '</td>'
					+ '<td>'
					+ '	<strong>' + $(this).find('title').text() + '</strong>'
					+ '	<br>' + $(this).find('description').text()
					+ ' <br><span class="info-subline">MarketplaceID: ' + $(this).find('MarketplaceID').text() + '</span>'
					+ ' <br><small style="color:red;">(Preiswarnung: ' + $(this).find('differentPrices').text() + ')</small>'
					+ '<td>'
					+ '	<img class="btn button-remove" onclick="deleteAmazonSites(' + $accountsite_id + ')" title="' + $removeLang + '" alt="' + $removeLang + '" src="' + $removeIcon + '">'
					+ '	<img class="btn button-edit" onclick="editAmazonAccountsSites(' +  $accountsite_id + ')" title="' + $editLang + '" alt="' + $editLang + '" src="' + $editIcon+ '">'
					+ '	<a href="' + $listOrdersLink + '">' + $buttonOrders + '</a>'
					+ '	<a href="' + $listProductsLink + '">' + $buttonProducts + '</a>'
					+ '	<a href="' + $listCategoriesLink + '">' + $buttonCategories + '</a>'
					+ '</td>'
				+ '</tr>'; 
				tbody.append(row);
		});
		table.append(tbody);
		
		$('#subContent').empty();
		$('#subContent').append(headline);
		$('#subContent').append(table);
		$($container).append('<div class="clear"></div>');
	}