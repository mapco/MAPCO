<?php
	include("config.php");
	include("templates/" . TEMPLATE_BACKEND . "/header.php");
	
	// keep get request and post submit
	$get = $_GET;
	$post = $_POST;
?>

<script type="text/javascript">

	/* 
	 * amazon category list by account id and accountsite id
	 * get the amazon categories and the amazon sites
	 */	
	function listAmazonCategoriesByAccount($id_account, $accountsiteID)
	{
		wait_dialog_show();	
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonCategories";
		$post_data['action'] = 'listAmazonCategories';
		$post_data['id_account'] = $id_account;
		$post_data['accountsite_id'] = $accountsiteID;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			$languageId = $xml.find('language_id').text();
			$importBrowsenode = '<button style="float: right;margin-top: 2px;" onclick="importBrowsenodesForm(' + $accountsiteID + ',' + $languageId + ')">Import Browsenodes CSV</button>';		
			
			$('#amazonCategories').empty();
			$('h1').append('Amazon Categories' + $importBrowsenode);
			amazonCategoriesTable($xml, '#amazonCategories');
		});
	}
	
	/* 
	 * import Browsenodes Form
	 */		
	function importBrowsenodesForm($marketplace_id, $language_id)
	{
		$("#select_marketplaces").val($marketplace_id);
		$("#language").val($language_id);
		
		$("#import_form_dialog").dialog
		({	buttons:
			[
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText: "Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal: true,
			resizable: false,
			show: { effect: 'drop', direction: "up" },
			title: "Browsenodes Importieren",
			width: 600
		});			
	}
	
	/* 
	 * edit amazon category by id, browsenodeid1, browsenodeid2 and language_id
	 */		
	function editAmazonCategories($id, $browsenodeid1, $browsenodeid2, $language_id, $id_account, $marketplace_id)
	{
		$("#category_edit_id").val($id);
		$(".tr_category_edit_dialog").hide();
		$("#tr1langID" + $language_id).show();
		$("#tr2langID" + $language_id).show();

		$("#category_edit_browsenodeid1_" + $language_id).val($browsenodeid1);
		$("#category_edit_browsenodeid2_" + $language_id).val($browsenodeid2);
		$("#category_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { editAmazonCategoriesSave($language_id, $id_account, $marketplace_id); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText: "Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal: true,
			resizable: false,
			show: { effect: 'drop', direction: "up" },
			title: "Kategorien bearbeiten",
			width: 600
		});
	}
	
	/* 
	 * save edit amazon category by language_id
	 */	
	function editAmazonCategoriesSave($language_id, $id_account, $marketplace_id)
	{
		var id = $("#category_edit_id").val();
		var BrowseNodeId1 = $("#category_edit_browsenodeid1_" + $language_id).val();
		var BrowseNodeId2 = $("#category_edit_browsenodeid2_" + $language_id).val();
		
		if (id == "") {alert("Es konnte keine ID ermittelt werden."); $("#category_edit_id").val(); return;}
		if (BrowseNodeId1 == "") {alert("Es konnte keine Kategorie 1 ermittelt werden."); $("#category_edit_browsenodeid1_" + $language_id).val().focus(); return;}
		if (BrowseNodeId1 == "") {alert("Es konnte keine Kategorie 2 ermittelt werden."); $("#category_edit_browsenodeid2_" + $language_id).val().focus(); return;}		
		
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonCategories";
		$post_data['action'] = 'editAmazonCategories';
		$post_data['id'] = id;
		$post_data['BrowseNodeId1'] = BrowseNodeId1;
		$post_data['BrowseNodeId2'] = BrowseNodeId2;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() == 'Success') {
				wait_dialog_hide();
				$("#category_edit_dialog").dialog("close");
				show_status("Die Kategorien wurden erfolgreich geändert.");
				amazonCategoriesGet($id_account, $marketplace_id);
				wait_dialog_hide();
			} else  {
				wait_dialog_hide();
				show_status2($data);
				return;
			}
		});
	}
</script>

<?php
	$breadcrumbs = '
	<div id="breadcrumbs" class="breadcrumbs">
		<a href="backend_index.php">Backend</a>
			&#187; <a href="backend_amazon_index.php">Amazon</a>
			&#187; <a href="">Amazon Kategorien</a>
	</div>';
	echo $breadcrumbs;
	echo '
		<h1>
			<a href="backend_amazon_categories.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID'] . '"><button>Amazon Kategorien</button></a>
			<a href="backend_amazon_products.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID'] . '"><button>Amazon Produkte</button></a>
			<a href="backend_amazon_orders_list.php?accountID=' . $get['accountID'] . '&accountsiteID=' . $get['accountsiteID'] . '"><button>Amazon Bestellungen</button></a>
		</h1>';
	
	//	IMPORT BROWSENODES FORM
	$importForm = '<div style="display:none;" id="import_form_dialog">
		<form method="post" enctype="multipart/form-data">
		Marktplatz: <select id="select_marketplaces" name="id_marketplace">';
		
		$amazonMarketplacesResults = q("
			SELECT * 
			FROM amazon_marketplaces;", $dbshop, __FILE__, __LINE__);
			while ($amazonMarketplaces = mysqli_fetch_array($amazonMarketplacesResults))
			{
				$importForm.= '<option value="' . $amazonMarketplaces["id_marketplace"] . '">' . $amazonMarketplaces["name"] . '</option>';
			}
	$importForm.= '</select><br />
				<input type="file" name="file" />
				<input type="hidden" name="language" id="language" value="" />
				<input type="submit" value="Hochladen" />
			</form>
		</div>';
	echo $importForm;
	
	if (isset($_FILES["file"])) {
		//	clear table
		q("
			DELETE FROM amazon_browsenodes 
			WHERE marketplace_id = " . $post["id_marketplace"] . ";", $dbshop, __FILE__, __LINE__);

		//cache vehicles file
		$browsenodes = array();
		$handle = fopen($_FILES["file"]["tmp_name"], "r");
		$line = fgetcsv($handle, 4096, ";");
		while ($line = fgetcsv($handle, 4096, ";"))
		{
			$line[1] = utf8_encode($line[1]);
			$count = substr_count($line[1], "/");
			if (strrpos($line[1], "/") === false ) {
				$Path = $line[1]; 
			} else {
				$Path = substr($line[1], strrpos($line[1], "/")+1);
			}
			for ($i = 1; $i < $count; $i++) $Path = "&nbsp;&nbsp;" . $Path;
			$browsenodes[] = "(" . $post['language'] . ", " . $post["id_marketplace"] . ", " . $line[0] . ", '" . mysqli_real_escape_string($dbshop, $line[1]) . "', '" . mysqli_real_escape_string($dbshop, $Path) . "')";
		}
		fclose($handle);

		$amazonBrowsenodesQuery = "
			INSERT INTO amazon_browsenodes (
				Lang_Id,
				marketplace_id, 
				BrowseNodeId, 
				Category, 
				Path) VALUES " . implode(", ", $browsenodes) . ";";
		q($amazonBrowsenodesQuery, $dbshop, __FILE__, __LINE__);
		echo 'Kategorien erfolgreich  importiert.';
		exit;
	}	
	
	//CATEGORIES EDIT DIALOG
	echo '<div style="display:none;" id="category_edit_dialog">';
	echo '	<table>';
	
	$res = q("
		select distinct Lang_Id 
		from amazon_browsenodes 
		ORDER BY Lang_Id;", $dbshop, __FILE__, __LINE__);
	while ($LangID = mysqli_fetch_array($res))
	{
		echo '<tr class="tr_category_edit_dialog" id="tr1langID' . $LangID["Lang_Id"] . '" style="display:none">';
		echo '	<td>Kateorie #1</td>';
		echo '	<td>';
		echo '		<select id="category_edit_browsenodeid1_' . $LangID["Lang_Id"] . '">';
		echo '		<option value="0">Ersten Browsnode wählen</option>';
		$results=q("
			SELECT * 
			FROM amazon_browsenodes 
			WHERE Lang_Id = '" . $LangID["Lang_Id"] . "' 
			ORDER BY Path;", $dbshop, __FILE__, __LINE__);
			while( $row = mysqli_fetch_array($results) )
			{
				echo '<option value="' . $row["BrowseNodeId"] . '">' . $row["Category"].'</option>';
			}
			echo '	</td>';
			echo '</tr>';
			echo '<tr class="tr_category_edit_dialog" id="tr2langID' . $LangID["Lang_Id"] . '" style="display:none">';
			echo '	<td>Kateorie #2</td>';
			echo '	<td>';
			echo '		<select id="category_edit_browsenodeid2_' . $LangID["Lang_Id"] . '">';
			echo '		<option value="0">(Zweiten Browsnode wählen)</option>';
			$results=q("
				SELECT * 
				FROM amazon_browsenodes 
				WHERE Lang_Id = '" . $LangID["Lang_Id"] . "' 
				ORDER BY Path;", $dbshop, __FILE__, __LINE__);
				while( $row = mysqli_fetch_array($results) )
				{
					echo '<option value="' . $row["BrowseNodeId"] . '">' . $row["Category"].'</option>';
				}
		echo '	</td>';
		echo '</tr>';
	}
	echo '	</table>';
	echo '	<input type="hidden" id="category_edit_id" value="" />';
	echo '</div>';
		
 	echo '<div id="amazonCategories"></div>';
	
	//loading....	
	echo '<script src="' . PATH . 'javascript/amazon/TableViewCategories.php" type="text/javascript"></script>';
	echo '<script type="text/javascript">listAmazonCategoriesByAccount(' . $get['accountID'] . ',' . $get['accountsiteID'] . ');</script>';
	include("templates/" . TEMPLATE_BACKEND . "/footer.php");