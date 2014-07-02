<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
// keep get request and post submit
$post = $_POST;
$get = $_GET;	
?>

<script type="text/javascript">
	
	/* 
	 * amazon orders list
	 * list the latest orders form amazon orders import
	 */	
	function listAmazonOrders($account_id, $accountsite_id)
	{
		wait_dialog_show();
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonOrders";
		$post_data['action'] = 'listAmazonOrders',
		$post_data['account_id'] = $account_id;
		$post_data['accountsite_id'] = $accountsite_id;		
		$post_data['limit'] = '150';
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}					

			$('h1').append('Amazon Orders');
			
			$('#amazonOrders').empty();
			amazonOrdersTable($xml);
			wait_dialog_hide();
		});
		
		$("#search-button").click(function() {
			var addSearchAmazonOrderId = $("#searchAmazonOrderId").val();
			
			var $post_data = new Object();
			$post_data['API'] = "amazon";
			$post_data['APIRequest'] = "AmazonOrders";
			$post_data['action'] = "searchAmazonOrders";
			$post_data['accountsite_id'] = $accountsite_id;
			$post_data['limit'] = 10;
			$post_data['searchAmazonOrderId'] = addSearchAmazonOrderId;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
				wait_dialog_show();
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}				
			
				amazonOrdersTable($xml);
				$('.h3-info').empty();
				if  (addSearchAmazonOrderId != 0) {
					$('.h3-info').append('[Bestellummer Suchergebnis für ' + addSearchAmazonOrderId + ']');
				}					
				wait_dialog_hide();
			});
		});			
	}
	
	/**
	 * 
	 */	
	function amazonOrdersUpdate(id_account, limit, submitType)
	{
		wait_dialog_show();
		var $post_data = new Object();
		$post_data['API'] = "jobs";
		$post_data['APIRequest'] = "AmazonOrdersUpdateJob";
		$post_data['id_account'] = id_account;
		$post_data['limit'] = limit;
		$post_data['submitType'] = submitType;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}

			$('h1').append('Amazon Orders');
			wait_dialog_hide();
		});
	}	

    /*
     * update amazon order item by order id
     */
	function updateOrderById(id_account, AmazonOrderId)
	{
		wait_dialog_show();
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonOrderItemsUpdate";
		$post_data['id_account'] = id_account;
		$post_data['AmazonOrderId'] = AmazonOrderId;
		$post_data['action'] = "ListOrderItems";
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				
			    var $post_data = new Object();
				$post_data['API'] = "crm";
				$post_data['APIRequest'] = "AmazonOrderImport";
				$post_data['AmazonOrderId'] = AmazonOrderId;
				$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
					try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
					if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
		
					listAmazonOrders();
					wait_dialog_hide();
				});
		});
	}	
	
	/* 
	 * Amazon Order Item by amazon order id
	 */
	function viewAmazonOrderItemById(AmazonOrderId)
	{
		var $post_data = new Object();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "TableDataSelect";
		$post_data['where'] = "WHERE AmazonOrderId = '" + AmazonOrderId + "'";
		$post_data['table'] = "amazon_order_items";
		$post_data['db'] = "dbshop";		
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			amazonOrderItem($xml);
			wait_dialog_hide();
		});
	}	

	/**
	 * amazon order items update
	 */
	function amazonOrderItemsUpdate(id_account, limit, submitType)
	{	
		wait_dialog_show();
		var $post_data = new Object();
		$post_data['API'] = "jobs";
		$post_data['APIRequest'] = "AmazonOrderItemsUpdateJob";
		$post_data['id_account'] = id_account;
		$post_data['limit'] = limit;
		$post_data['submitType'] = submitType;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			$('h1').empty();
			$('h1').append('Amzon Orders');			
			amazonOrdersTable($xml);
			wait_dialog_hide();
		});
	}	
</script>

<?php
	echo '<div id="breadcrumbs" class="breadcrumbs">';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' &#187; <a href="backend_amazon_index.php">Amazon</a>';
	echo ' &#187; <a href="">Amazon Bestellungen</a>';
	echo '</div>';
	echo '
		<h1>
			<a href="backend_amazon_categories.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID'] . '"><button>Amazon Kategorien</button></a>
			<a href="backend_amazon_products.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID'] . '"><button>Amazon Produkte</button></a>
			<a href="backend_amazon_orders_list.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID'] . '"><button>Amazon Bestellungen</button></a>		
		</h1>';

	echo '<div id="content-wrapper">';
		echo '<form id="form-search">
				<div class="search">
					<label>Suche: </label>
					<input type="search" name="searchAmazonOrderId" id="searchAmazonOrderId" placeholder="Bestellnummer"/>
					<button id="search-button" type="button">Suche</button>			
				</div>
			</form>';
			
	echo '	<div id="amazonOrders" class="widget-listing"></div>';			
	echo '</div>';
	
	//loading....
	echo '<script src="//datatables.net/download/build/nightly/jquery.dataTables.js"></script>';
	echo '<script src="' . PATH . 'javascript/cms/DataTablesConfig.php" type="text/javascript"></script>';
	echo '<script src="' . PATH . 'javascript/amazon/TableViewOrders.php" type="text/javascript"></script>';
	echo '<script type="text/javascript">listAmazonOrders(' . $get['accountID'] . ',' . $get['accountsiteID'] . ');</script>';
	include("templates/".TEMPLATE_BACKEND."/footer.php");