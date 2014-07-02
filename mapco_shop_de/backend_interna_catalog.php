<?php

//	Header
include("config.php");
include("templates/" . TEMPLATE_BACKEND . "/header.php");

//	Content
?>
<script type="text/javascript">

	/**
     * show
     */
	function showCatalog()
    {
        var $post_data = new Object();
        $post_data['API'] = "catalog";
        $post_data['APIRequest'] = "CatalogCreate";
        $post_data['action'] = "showCatalog";
        $.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            wait_dialog_show();
            try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
            if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
        });
    }
</script>

<?php
//	Breadcrumbs
echo '
	<div id="breadcrumbs" class="breadcrumbs">
		<a href="backend_index.php">Backend</a>
		&#187; <a href="">Catalog</a>
		&#187; Dashboard
	</div>
	<h1>Catalog - Dashboard</h1>';

//	Footer
echo '<script type="text/javascript">showCatalog(' . $get['orderBy'] . ');</script>';
include("templates/" . TEMPLATE_BACKEND . "/footer.php");
