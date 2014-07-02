<?php

include("config.php");
require("templates/" . TEMPLATE_BACKEND . "/header.php");
require_once("../APIs/amazon/Model/AmazonModel.php");

$title = 'Accounts Dashboard';
?>

<script type="text/javascript">
	
	/**
	 * amazon accounts list
	 * get the amazon accounts table
	 */	
	function listAmazonAccounts()
	{
		wait_dialog_show();	
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonAccounts";
		$post_data['action'] = 'listAmazonAccounts';
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			//if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}	

			$('h1').empty();
			$('#amazonAccounts').empty();
			$('h1').append('Amazon Accounts');
			amazonAccountsTable($xml, '#amazonAccounts');
			wait_dialog_hide();
		});
	}
	
	/**
	 * add an amazon account
	 * 
	 */	
	function addAmazonAccounts()
	{
		$("#account_title").val("");
		$("#account_description").val("");
		$("#account_AWSAccessKeyId").val("");
		$("#account_MarketplaceId").val("");
		$("#account_MerchantId").val("");
		$("#account_SecretKey").val("");
		$("#account_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { addAmazonAccountsSave(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Account hinzufügen",
			width:600
		});		
	}
	
	/**
	 * add an amazon account save
	 * 
	 */	
	function addAmazonAccountsSave()
	{
		var title = $("#account_title").val();
		var description = $("#account_description").val();
		var AWSAccessKeyId = $("#account_AWSAccessKeyId").val();
		var MarketplaceId = $("#account_MarketplaceId").val();
		var MerchantId = $("#account_MerchantId").val();
		var SecretKey = $("#account_SecretKey").val();
		
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonAccounts";
		$post_data['action'] = 'addAmazonAccounts';
		
		$post_data['title'] = title;
		$post_data['description'] = description;
		$post_data['AWSAccessKeyId'] = AWSAccessKeyId;
		$post_data['MarketplaceId'] = MarketplaceId;
		$post_data['MerchantId'] = MerchantId;
		$post_data['SecretKey'] = SecretKey;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() == 'Success') {
				wait_dialog_hide();
				$("#account_dialog").dialog("close");
				show_status("Der Account wurde erfolgreich angelegt.");
				listAmazonAccounts();
				wait_dialog_hide();
			} else  {
				wait_dialog_hide();
				show_status2($data);
				return;
			}
		});
	}	
	
	/**
	 * edit an amazon account
	 * 
	 */	
	function editAmazonAccounts($id_account)
	{
		var $post_data = new Object();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "TableDataSelect";
		$post_data['where'] = "WHERE id_account = '" + $id_account + "'";
		$post_data['table'] = "amazon_accounts";
		$post_data['db'] = "dbshop";		
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}

			$("#account_id_account").val($xml.find('id_account').text());
			$("#account_title").val($xml.find('title').text());
			$("#account_description").val($xml.find('description').text());
			$("#account_AWSAccessKeyId").val($xml.find('AWSAccessKeyId').text());
			$("#account_MarketplaceId").val($xml.find('MarketplaceId').text());
			$("#account_MerchantId").val($xml.find('MerchantId').text());
			$("#account_SecretKey").val($xml.find('SecretKey').text());
			$("#account_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { editAmazonAccountsSave(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal: true,
				resizable: false,
				show: { effect: 'drop', direction: "up" },
				title: "Account bearbeiten",
				width: 600
			});
		});
	}
	
	/** 
	 * edit an amazon account save
	 * 
	 */		
	function editAmazonAccountsSave()
	{
		var id_account = $("#account_id_account").val();
		
		var title = $("#account_title").val();
		if (title == "") { alert("Es muss ein Titel angegben werden."); $("#account_title").val(); return;}
		
		var description = $("#account_description").val();
		if (description == "") { alert("Es muss ein Beschreibung angegben werden."); $("#account_description").val(); return;}
		
		var AWSAccessKeyId = $("#account_AWSAccessKeyId").val();
		var MarketplaceId = $("#account_MarketplaceId").val();
		var MerchantId = $("#account_MerchantId").val();
		var SecretKey = $("#account_SecretKey").val();

		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonAccounts";
		$post_data['action'] = 'editAmazonAccounts';
		
		$post_data['id_account'] = id_account;
		$post_data['title'] = title;
		$post_data['description'] = description;
		$post_data['AWSAccessKeyId'] = AWSAccessKeyId;
		$post_data['MarketplaceId'] = MarketplaceId;
		$post_data['MerchantId'] = MerchantId;
		$post_data['SecretKey'] = SecretKey;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() == 'Success') {
				wait_dialog_hide();
				$("#account_dialog").dialog("close");
				show_status("Der Account wurden erfolgreich bearbeitet.");
				listAmazonAccounts();
				wait_dialog_hide();
			} else  {
				wait_dialog_hide();
				show_status2($data);
				return;
			}
		});
	}	
	
	/*
	 *--------------------------------------- Amazon Accounts Sites ------------------------------------------------
	 */	
	
	/**
	 * amazon accounts sites list
	 * get the amazon accounts sites table
	 */		
	function showAmazonAccountsSitesByAccountId($account_id)
	{
		wait_dialog_show();	
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonAccounts";
		$post_data['action'] = 'listAmazonAccountsSites';
		$post_data['account_id'] = $account_id;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			//if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			amazonAccountsSitesTable($xml, $account_id, '#amazonAccounts');
			wait_dialog_hide();
		});		
	}
	
	/**
	 * add an amazon accounts sites
	 * 
	 */	
	function addAmazonAccountsSites($account_id)
	{
		$("#account_site_title").val("");
		$("#account_site_description").val("");
		$("#account_site_active").val("");
		$("#account_site_MarketplaceId").val("");
		$("#account_site_currency").val("");
		$("#account_site_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { addAmazonAccountsSitesSave($account_id); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText: "Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal: true,
			resizable: false,
			show: { effect: 'drop', direction: "up" },
			title: "Account Site hinzufügen",
			width: 600
		});		
	}
	
	/** 
	 * add an amazon account site save
	 * 
	 */	
	 function addAmazonAccountsSitesSave($account_id)
	 {
		var title = $("#account_site_title").val();
		var description = $("#account_site_description").val();
		var marketplace_id = $("#account_site_MarketplaceId").val();
		var currency = $("#account_site_currency").val();
		var active = $("#account_site_active").val();
		
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonAccounts";
		$post_data['action'] = 'addAmazonAccountsSites';
		
		$post_data['title'] = title;
		$post_data['description'] = description;
		$post_data['marketplace_id'] = marketplace_id;
		$post_data['active'] = active;
		$post_data['currency'] = currency
		$post_data['account_id'] = $account_id;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() == 'Success') {
				wait_dialog_hide();
				$("#account_site_dialog").dialog("close");
				show_status("Die Account Site wurde erfolgreich angelegt.");
				listAmazonAccounts();
				wait_dialog_hide();
			} else  {
				wait_dialog_hide();
				show_status2($data);
				return;
			}
		});
	}
	
	/**
	 * edit an amazon account site
	 * 
	 */	
	function editAmazonAccountsSites($id_accountsite)
	{
		var $post_data = new Object();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "TableDataSelect";
		$post_data['where'] = "WHERE id_accountsite = '" + $id_accountsite + "'";
		$post_data['table'] = "amazon_accounts_sites";
		$post_data['db'] = "dbshop";		
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}

			$("#account_id_accountsite").val($xml.find('id_accountsite').text());
			$("#account_site_title").val($xml.find('title').text());
			$("#account_site_description").val($xml.find('description').text());
			$("#account_site_MarketplaceId").val($xml.find('marketplace_id').text());
			$("#account_site_currency").val($xml.find('currency').text());			
			$("#account_site_active").val($xml.find('active').text());

			$("#account_site_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { editAmazonAccountsSitesSave(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal: true,
				resizable: false,
				show: { effect: 'drop', direction: "up" },
				title: "Account Site bearbeiten",
				width: 600
			});
		});
	}
	
	/** 
	 * save edit amazon account site
	 * 
	 */		
	function editAmazonAccountsSitesSave()
	{
		var id_accountsite = $("#account_id_accountsite").val();
		
		var title = $("#account_site_title").val();
		if (title == "") { alert("Es muss ein Titel angegben werden."); $("#account_site_title").val(); return;}
		
		var description = $("#account_site_description").val();
		if (description == "") { alert("Es muss ein Beschreibung angegben werden."); $("#account_site_description").val(); return;}
		
		var MarketplaceId = $("#account_site_MarketplaceId").val();
		var currency = $("#account_site_currency").val();
		if (currency == "") { alert("Es muss eine Währung angegben werden."); $("#account_site_currency").val(); return;}
		
		var active = $("#account_site_active").val();

		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonAccounts";
		$post_data['action'] = 'editAmazonAccountsSites';
		
		$post_data['id_accountsite'] = id_accountsite;
		$post_data['title'] = title;
		$post_data['description'] = description;
		$post_data['marketplace_id'] = MarketplaceId;
		$post_data['currency'] = currency;
		$post_data['active'] = active;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            try {var $xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() == 'Success') {
				wait_dialog_hide();
				$("#account_site_dialog").dialog("close");
				show_status("Die Account Site wurden erfolgreich bearbeitet.");
				listAmazonAccounts();
				wait_dialog_hide();
			} else  {
				wait_dialog_hide();
				show_status2($data);
				return;
			}
		});
	}
	
	/**
	 * delete amazon site
	 */	
	function deleteAmazonSites($accountsite_id)
	{
		wait_dialog_show();
		var $post_data = new Object();
        $post_data['API'] = "amazon";
        $post_data['APIRequest'] = "AmazonAccounts";
        $post_data['action'] = 'getAmazonAccountsSite';
        $post_data['accountsite_id'] = $accountsite_id;
        $.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            wait_dialog_show();
            try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
            //if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			wait_dialog_hide();
			$("#deleteDialog").html("Soll die Amazon Site: <b>" + $xml.find("title").text() + "</b> wirklich gelöscht werden?");
			$("#deleteDialog").dialog
			({	buttons:
				[
					{ text: "Löschen", click: function() {  executeDeleteAmazonSites($accountsite_id); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title: "Amazon Site löschen",
				width: 500
			});	
        });
	}
	
	/**
	 * execute delete amazon site
	 */	
	function executeDeleteAmazonSites($accountsite_id)
	{
		wait_dialog_show();
		var $post_data = new Object();
        $post_data['API'] = "amazon";
        $post_data['APIRequest'] = "AmazonAccounts";
        $post_data['action'] = 'deleteAmazonAccountsSites';
        $post_data['accountsite_id'] = $accountsite_id;
        $.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
            wait_dialog_show();
            try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
            if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			wait_dialog_hide();
			$("#deleteDialog").dialog("close");
			show_status("Amazon Account Site wurde gelöscht");
			listAmazonAccounts();
		});
	}			

</script>

<?php
	echo '<div id="breadcrumbs" class="breadcrumbs">';
	echo '	<a href="backend_index.php">Backend</a>';
	echo '	&#187; <a href="backend_amazon_index.php">Amazon</a>';
	echo '	&#187; <a href="">Amazon Accounts</a>';
	echo '</div>';
	echo '<h1></h1>';
	
	echo '
		<div id="widget-dashboard">
			' . getCriticalPricesByAccount(1) . '
		</div>';
	
	//ACCOUNT ADD/EDIT DIALOG
	echo '<div id="account_dialog" style="display:none;">';
	echo '<table style="margin:5px; float:left;">';
	echo '	<tr>';
	echo '		<th colspan="2">Account bearbeiten</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td><input id="account_title" style="width:400px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Beschreibung</td>';
	echo '		<td><textarea id="account_description" style="width:400px; height:50px;"></textarea></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Händler ID</td>';
	echo '		<td><input id="account_MerchantId" style="width:400px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Marktplatz-ID</td>';
	echo '		<td>';
			
			$marketplacesQuery = "
				SELECT * 
				FROM amazon_marketplaces 
				ORDER BY name;";		
			$marketplacesResults = q($marketplacesQuery, $dbshop, __FILE__, __LINE__);
		
			$selectOptions = '
				<select id="account_MarketplaceId">
					<option value="">Bitte wählen...</option>';
			while ($row = mysqli_fetch_assoc($marketplacesResults))
			{	
				$selectOptions.= '<option value="' . $row['MarketplaceID'] . '">' . $row['name'] . '</option>';
			}
			$selectOptions.= '
				</select>';
	echo $selectOptions;
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>AWS Zugangsschlüssel-ID</td>';
	echo '		<td><input id="account_AWSAccessKeyId" style="width:400px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Geheimer Schlüssel</td>';
	echo '		<td><input id="account_SecretKey" style="width:400px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '	<input id="account_id_account" type="hidden" value="" />';
	echo '</div>';
	
	//ACCOUNT SITE ADD/EDIT DIALOG
	echo '<div id="account_site_dialog" style="display:none;">';
	echo '<table style="margin:5px; float:left;">';
	echo '	<tr>';
	echo '		<th colspan="2">Account Site bearbeiten</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel*</td>';
	echo '		<td><input id="account_site_title" style="width:400px;" type="text" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Beschreibung*</td>';
	echo '		<td><textarea id="account_site_description" style="width:400px; height:50px;"></textarea></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Marktplatz-ID*</td>';
	echo '		<td>';
			
			$marketplacesQuery = "
				SELECT * 
				FROM amazon_marketplaces 
				ORDER BY name;";		
			$marketplacesResults = q($marketplacesQuery, $dbshop, __FILE__, __LINE__);
		
			$selectOptions = '
				<select id="account_site_MarketplaceId">
					<option value="">Bitte wählen...</option>';
			while ($row = mysqli_fetch_assoc($marketplacesResults))
			{	
				$selectOptions.= '<option value="' . $row['id_marketplace'] . '">' . $row['name'] . '</option>';
			}
			$selectOptions.= '
				</select>';
	echo $selectOptions;
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Währung*</td>';
	echo '		<td>
					<input id="account_site_currency" style="width:50px;" type="text" value="" />
					<span class="tipp">(zb EUR => Euro, GBP => British Pound, USD => US Dollar)</span>
				</td>';
	echo '	</tr>';	
	echo '	<tr>';
	echo '		<td>Aktiv</td>';
	echo '		<td>';
	echo '			<select id="account_site_active">
						<option value="">Bitte wählen...</option>
						<option value="0">Nein</option>
						<option value="1">Ja</option>
					</select>';			
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '	<input id="account_id_accountsite" type="hidden" value="" />';
	echo '</div>';	
	
	//DELETE DIALOG
	echo '<div id="deleteDialog" style="display:none"></div>';	
	echo '<div id="amazonAccounts"></div>';	
	
	//loading....
	echo '<script src="' . PATH . 'javascript/amazon/TableViewAccount.php" type="text/javascript"></script>';
	echo '<script type="text/javascript">listAmazonAccounts();</script>';
	include("templates/" . TEMPLATE_BACKEND . "/footer.php");
