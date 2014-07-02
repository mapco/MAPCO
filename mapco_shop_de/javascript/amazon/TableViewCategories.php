<?php

/**
 *	Created by RLange on 02.04.14.
 *	- Table Collection for Amazon Categories View
 */
header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if (true == false) { ?> <script type="text/javascript"> <?php }
?>

	/*
	 *--------------------------------------- Categories Tables ------------------------------------------------
	 */

	/**
	 * show amazon categories table
	 */
	function amazonCategoriesTable($xml, $container)
	{
		var content = $('<div id="mainContent" class="widget-listing"></div>');
		$($container).append(content);
		
		$editLang = 'Kategorien bearbeiten';
		$editIcon = 'images/icons/16x16/edit.png';
		
		var headline = $('<h3>Kategorien für den Marktplatz: ' + $xml.find('name').text() + ' , gefundene Kategorien: ' + $xml.find('categoriesTotal').text() + '</h3>');
		var table = $('<table class="listing"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<tr><th>GART</th><th>Browse Node 1</th><th>Browse Node 2</th><th>Optionen</th></tr>');
		thead.append(th);
		table.append(thead);	
		var row;
		$xml.find('amazonCategories').each(function()
		{
			$categoryEdit = $(this).find('amazonCategoryID').text() 
				+ ',' 
				+ $(this).find('amazonCategoryBrowseNodeId1').text() 
				+ ',' 
				+ $(this).find('amazonCategoryBrowseNodeId2').text()
				+ ','
				+ $xml.find('language_id').text()
				+ ','
				+ $xml.find('account_id').text()
				+ ','
				+ $xml.find('marketplace_id').text();							
			
			row = '<tr>'
					+ '<td>' + $(this).find('Bez').text() + '</td>'
					+ '<td>' + $(this).find('BrowseNodeId1').text() + '</td>'
					+ '<td>' + $(this).find('BrowseNodeId2').text() + '</td>'
					+ '<td><img class="btn button-edit" src="' + $editIcon + '" alt="' + $editLang + '" title="' + $editLang + '" onclick="editAmazonCategories(' + $categoryEdit + ');"></td>'
				+ '</tr>'; 
			tbody.append(row);
		});
		table.append(tbody);
		
		$($container + ' #mainContent').append(headline);
		$($container + ' #mainContent').append(table);
		$($container).append('<div class="clear"></div>');
	}