<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>


<script type="text/javascript">

	$(window).load(function() {
		
		AmazonOrderItemGet(1);
	});

	function AmazonOrderItemGet(id_account)
	{	
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonOrderItemGet";
		$post_data['id_account'] = id_account;

		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			$('h1').append('Amzon Orders');
			wait_dialog_show();
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			amazonOrderItem($xml);
			wait_dialog_hide();
		});
	}

	/**
	 * view amazon orders item table
	 */	
	function amazonOrderItem($xml)
	{		
		var headline = $('<h3>Amazon Order Item</h3>') + $('<div>Bestellnr.: # ' + $(this).find('AmazonOrderId').text() + '</div>');
		var table = $('<table class="listing"></table>');
				var thead = $('<thead></thead>');
				var tbody = $('<tbody></tbody>');
				var tr = $('<tr></tr>');
				var td = $('<td></td>');
				var th = $('<th></th>');
				thead.append(th);
				table.append(thead);				
				var row;
				$xml.find('AmazonOrder').each(function()
				{
					row = '<tr><td class="center">' + $(this).find('PurchaseDate').text() + '</td>'
						+ '</tr>'; 
						tbody.append(row);
				});

		$('#amazonOrders').empty();		
		$('#amazonOrders').append(headline);
		table.append(tbody);
		$('#amazonOrders').append(table);		
	}	
</script>
</script>


<?php
	//PATH
	echo '<div id="breadcrumbs" class="breadcrumbs">';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' &#187 <a href="">Amazon</a>';
	echo ' &#187 <a id="send" onclick="return true" href="backend_amazon_order_item.php">Amazon Order Item</a>';
	echo '</div>';
	echo '<h1></h1>';

	echo '<div id="content-wrapper">';
		echo '<div id="menu"></div>';	
		echo '<div id="detail"></div>';
	echo '</div>';
	
	echo '<div id="amazonOrders" class="widget-listing">' . $html . '</div>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>