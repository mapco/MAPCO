<?php

// Header
include("config.php");
include("templates/" . TEMPLATE_BACKEND . "/header.php");

$get = $_GET;
$post = $_POST;

// Content
?>
<script type="text/javascript">

	/**
	 * show catalog
	 */
	function showCatalog(KHerNr, gart)
	{
		wait_dialog_show();
		$('#showCatalog').append('Erstelle PDF Template Luke....bitte warten, ...ach und ich bin dein Vater');
		var $post_data = new Object();
		$post_data['API'] = "catalog";
		$post_data['APIRequest'] = "CatalogCreate";
		$post_data['action'] = "showCatalog";
		$post_data['KHerNr'] = KHerNr;
		$post_data['gart'] = gart;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			//if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}

			$('#showCatalog').empty();
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
	&#187; <a href="backend_interna_catalog.php?lang=de&id_menuitem=861">PDF Catalog Creator</a>
	&#187; Dashboard
</div>
<h1>Catalog - Dashboard</h1>
<div class="toolbar">
	<form method="post" enctype="multipart/form-data">
		<span>Hersteller: </span>
		<select name="khernr" id="label_khernr_id">
			<option value="00005">Audi</option>
			<option value="00093">Renault</option>
			<option value="00121">VW</option>
		</select>
		<span>Artikel Gruppe: </span>
		<select name="gart" id="label_gart_id">
			<option value="00247">Motorlager</option>
		</select>
		<span>Sprache: </span>
		<select name="gart" id="label_language_id">
			<option value="1">Deutsch</option>
		</select>
		<span>Datum: </span>
		<input type="submit" name="create_pdf" value="PDF Erstellen">
	<form>
</div>
<div id="showCatalog"></div>';

if (isset($post['khernr']) && !empty($post['khernr']) && isset($post['gart']) && !empty($post['gart'])) 
{
	echo '<script type="text/javascript">showCatalog("' . $post['khernr'] . '","' . $post['gart'] . '");</script>';
}

// Footer
include("templates/" . TEMPLATE_BACKEND . "/footer.php");
