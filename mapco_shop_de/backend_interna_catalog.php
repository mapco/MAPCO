<?php

// Header
include("config.php");
include("templates/" . TEMPLATE_BACKEND . "/header.php");

$get = $_GET;

// Content
?>
<script type="text/javascript">

	/**
	 * show catalog
	 */
	function showCatalog(KHerNr)
	{
		var $post_data = new Object();
		$post_data['API'] = "catalog";
		$post_data['APIRequest'] = "CatalogCreate";
		$post_data['action'] = "showCatalog";
		$post_data['KHerNr'] = KHerNr;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			wait_dialog_show();
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			//if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}

			$('#showCatalog').append($xml.find('showCatalog').text());
			wait_dialog_hide();
		});
	}

	/**
	 * create catalog pdf
	 */
	function createCatalogPDF(catalogNumber)
	{
		var $post_data = new Object();
		$post_data['API'] = "catalog";
		$post_data['APIRequest'] = "CatalogExportPDF";
		$post_data['action'] = "createCatalogPDF";
		$post_data['catalogNumber'] = catalogNumber;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			wait_dialog_show();
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			//if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}

			//$('#showCatalog').append($xml.find('showCatalog').text());
			wait_dialog_hide();
		});
	}
</script>

<?php
// Breadcrumbs
echo '
<div id="breadcrumbs" class="breadcrumbs">
<a href="backend_index.php">Backend</a>
&#187; <a href="">Catalog</a>
&#187; Dashboard
</div>
<h1>Catalog - Dashboard</h1>
<div class="toolbar"><a href="/backend_interna_catalog_export.php">PDF Export</a></div>
<div id="showCatalog"></div>';

// Footer
echo '<script type="text/javascript">showCatalog(' . $get['KHerNr'] . ');</script>';
include("templates/" . TEMPLATE_BACKEND . "/footer.php");
