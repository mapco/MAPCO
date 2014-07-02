<?php

/**
 *	Created by RLange on 02.04.14.
 *	- Table Collection for Amazon View
 */
 
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
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
		
		var headline = $('<h3>Accounts</h3>');
		var table = $('<table class="listing"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<th>Title / Description</th>');
		thead.append(th);
		table.append(thead);	
		var row;
		$xml.find('amazon_accounts').each(function()
		{
			$link = '<a href="javascript:showAmazonAccountsSitesByAccountId(' + $(this).find('id_account').text() + ')">' + $(this).find('title').text() + '</a>'
			row = '<tr>'
				+ '<td><strong>' + $link + '</strong><br>' + $(this).find('description').text()
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
	function amazonAccountsSitesTable($xml, $container)
	{
		if ($("#subContent").length  == 0) {
			$('.clear').css("display","none");
			$($container).append($('<div id="subContent" class="widget-listing widget-right"></div>'));
		}
		var headline = $('<h3>Sites</h3>');
		var table = $('<table class="listing"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<th>SiteID</th><th>Active</th><th>Code</th><th>Title</th><th>MarketplaceID</th><th>Option</th>');
		thead.append(th);
		table.append(thead);	
		var row;
		$xml.find('amazon_accounts_sites').each(function()
		{
			var active = '<span class="label label-danger">No</span>';
			if ($(this).find('active').text() == 1) { active = '<span class="label label-success">Yes</span>' }
			
			var categories = '<a href=""><img class="btn button-list" title="Amazon Kategorien" alt="Amazon Kategorien" src="images/icons/24x24/page_swap.png"></a>';
			
			row = '<tr>'
				+ '<td class="center">' + $(this).find('id_accountsite').text() + '</td>'
				+ '<td class="center">' + active + '</td>'
				+ '<td class="center">' + $(this).find('country_code').text() + '</td>'
				+ '<td><strong>' + $(this).find('title').text() + '</strong><br>' + $(this).find('description').text()
				+ '<td>' + $(this).find('MarketplaceID').text() + '</td>'
				+ '<td>' + categories + '</td>'
				+ '</tr>'; 
				tbody.append(row);
		});
		table.append(tbody);
		
		$('#subContent').empty();
		$('#subContent').append(headline);
		$('#subContent').append(table);
		$($container).append('<div class="clear"></div>');
	}