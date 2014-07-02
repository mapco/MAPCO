<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script type="text/javascript">
	
	/* 
	 * amazon accounts sites list
	 * get the amazon accounts sites table
	 */	
	function amazonAccountsGet()
	{
		wait_dialog_show();	
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonAccountsGet";
		$post_data['action'] = 'listAmazonAccounts';
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			//if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}					

			$('h1').empty();
			$('h1').append('Amazon Accounts');
			$('#amazonAccounts').empty();
			amazonAccountsSitesTable($xml);
			wait_dialog_hide();
		});
	}
	
	/* 
	 * amazon accounts sites list
	 * get the amazon accounts sites table
	 */		
	function showAmazonAccountsSitesByAccountId($account_id)
	{
		wait_dialog_show();	
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonAccountsSitesGet";
		$post_data['action'] = 'listAmazonAccounts';
		$post_data['account_id'] = $account_id;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			//if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			amazonAccountsTable($xml);
			wait_dialog_hide();
		});		
	}
	
	/*
	 *--------------------------------------- Product Bundle and Items Tables ------------------------------------------------
	 */

	 
	/**
	 * show amazon accounts sites table
	 */		 	
	function amazonAccountsSitesTable($xml)
	{
		var headline = $('<h3>Sites</h3>');
		var table = $('<table class="listing list-left"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<th>Accounts</th>');
		thead.append(th);
		table.append(thead);	
		var row;
		$xml.find('amazon_accounts_sites').each(function()
		{
			$link = '<a href="javascript:showAmazonAccountsSitesByAccountId(' + $(this).find('account_id').text() + ')">' + $(this).find('title').text() + '</a>'
			row = '<tr>'
				+ '<td><strong>' + $link + '</strong><br>' + $(this).find('description').text()
				+ '</tr>'; 
				tbody.append(row);
		});

		$('#amazonAccounts').empty();		
		$('#amazonAccounts').append(headline);
		table.append(tbody);
		$('#amazonAccounts').append(table);
		$('#amazonAccounts').append('<div class="clear"></div>');	
	}
	
	/**
	 * show amazon accounts table
	 */		 	
	function amazonAccountsTable($xml)
	{
		if ($("#subcontent").length  == 0) {
			$('.clear').css("display","none");
			$('#amazonAccounts').append($('<div id="subcontent"></div>'));
		}
		var table = $('<table class="listing list-right"></table>');
		var thead = $('<thead></thead>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<th>ID</th><th>Activ</th><th>Titel</th><th>SiteID</th>');
		thead.append(th);
		table.append(thead);	
		var row;
		$xml.find('amazon_accounts').each(function()
		{
			row = '<tr>'
				+ '<td>' + $(this).find('id_account').text() + '</td>'
				+ '<td>' + $(this).find('active').text() + '</td>'
				+ '<td><strong>' + $(this).find('title').text() + '</strong><br>' + $(this).find('description').text()
				+ '<td></td>'
				+ '</tr>'; 
				tbody.append(row);
		});

		$('#subcontent').empty();
		table.append(tbody);
		$('#subcontent').append(table);
		$('#amazonAccounts').append('<div class="clear"></div>');	
	}	
</script>amazon_accounts_sites

<?php
	echo '<div id="breadcrumbs" class="breadcrumbs">';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' &#187 <a href="backend_amazon_index.php">Amazon</a>';
	echo ' &#187 <a href="">Amazon Accounts</a>';
	echo '</div>';
	echo '<h1></h1>';
	
	echo '<div id="amazonAccounts" class="widget-listing"></div>';
	echo '<script type="text/javascript">amazonAccountsGet();</script>';
	include("templates/".TEMPLATE_BACKEND."/footer.php");
