<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script>
		var $shop_add_select_counter=0;

		function shop_add()
		{
			wait_dialog_show();

			var $html = '';
			$html += '<table>';
			$html += '	<tr>';
			$html += '		<td>Seite</td>';
			$html += '		<td>';
			$html += '			<select id="shop_add_site_id">';
			$html += '			</select>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Titel</td>';
			$html += '		<td><input id="shop_add_title" style="width:300px;" type="text" /></td>';
			$html += '	</tr>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Beschreibung</td>';
			$html += '		<td><textarea id="shop_add_description" style="width:300px; height:50px;"></textarea></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>IDIMS-Kunden-ID</td>';
			$html += '		<td><input id="shop_add_KUN_ID" type="text" /></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Domain</td>';
			$html += '		<td><input id="shop_add_domain" type="text" /></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Shoptyp</td>';
			$html += '		<td>';
			$html += '			<select id="shop_add_shop_id">';
			$html += '				<option value="0">hier shoptypesget einbauen</option>';
			$html += '			</select>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Account-ID</td>';
			$html += '		<td>';
			$html += '			<select id="shop_add_account_id">';
			$html += '				<option value="0">hier shoptypesget einbauen</option>';
			$html += '			</select>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Land</td>';
			$html += '		<td>';
			$html += '			<select id="shop_add_country_code">';
			$html += '				<option value="0">hier countriesget einbauen</option>';
			$html += '			</select>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Obershop</td>';
			$html += '		<td>';
			$html += '			<select id="shop_add_parent_shop_id">';
			$html += '			</select>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Template</td>';
			$html += '		<td><input id="shop_add_template" type="text" /></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>E-Mail</td>';
			$html += '		<td><input id="shop_add_mail" type="text" /></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Bestell-E-Mail versenden</td>';
			$html += '		<td>';
			$html += '			<select id="shop_add_ordermail">';
			$html += '				<option value="1">Ja</option>';
			$html += '				<option value="0">Nein</option>';
			$html += '			</select>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Preisliste</td>';
			$html += '		<td><input id="shop_add_pricelist" type="text" /></td>';
			$html += '	</tr>';
			$html += '</table>';
			if( $("#shop_add_dialog").length==0 ) $("body").append('<div id="shop_add_dialog" style="display:none"></div>');
			$("#shop_add_dialog").html($html);

			//fil select boxes
			$shop_add_select_counter=0;
			sites_get("shop_add_site_id");
			shops_get("shop_add_parent_shop_id");
			
			//start dialog
			wait_dialog_hide();
			shop_add_dialog();
		}
		
		function shop_add_dialog()
		{
			//start dialog when all selects are filled
			if( $shop_add_select_counter<2 ) return;

			$("#shop_add_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { shop_add_save(); } },
					{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Shop hinzufügen",
				width:700
			});		
		}

		function shops_get($id)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ShopsGet" }, function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				$xml.find("Shop").each(function()
				{
					var $parent_shop_id=$(this).find("parent_shop_id").text();
					if( $parent_shop_id==0 )
					{
						var $id_shop=$(this).find("id_shop").text();
						var $title=$(this).find("title").text();
						$("#"+$id).append('<option value="'+$id_shop+'">'+$title+'</option>');
					}
				});
				
				$shop_add_select_counter++;
				shop_add_dialog();
			});
		}

		function sites_get($id)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"SitesGet" }, function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				$xml.find("Site").each(function()
				{
					var $id_site=$(this).find("id_site").text();
					var $title=$(this).find("title").text();
					$("#"+$id).append('<option value="'+$id_site+'">'+$title+'</option>');
				});
				
				$shop_add_select_counter++;
				shop_add_dialog();
			});
		}

		
		function shop_add_save()
		{
			alert("KOMMT NOCH");
		}


		function view()
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ShopsGet" },
				function($data)
				{
					wait_dialog_hide();
					try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" ) { show_status2($data); return; }

					var $html = '';
					$html += '<h1>Shops</h1>';
					$html += '<table>';
					$html += '	<tr>';
					$html += '		<th>Nr.</th>';
					$html += '		<th>ID</th>';
					$html += '		<th>Shop</th>';
					$html += '		<th>Domain</th>';
					$html += '		<th>';
					$html += '			<img src="<?php echo PATH; ?>images/icons/24x24/add.png" style="cursor:pointer;" title="Shop hinzufügen" onclick="shop_add();" />';
					$html += '		</th>';
					$html += '	</tr>';
					var $nr=0;
					$xml.find("Shop").each(function()
					{
						$nr++;
						$html += '	<tr>';
						$html += '		<td>'+$nr+'</td>';
						var $id_shop=$(this).find("id_shop").text();
						$html += '		<td>'+$id_shop+'</td>';
						var $title=$(this).find("title").text();
						$html += '		<td>'+$title+'</td>';
						var $domain=$(this).find("domain").text();
						$html += '		<td>'+$domain+'</td>';
						$html += '		<td>';
						$html += '			<img src="<?php echo PATH; ?>images/icons/24x24/edit.png" style="cursor:pointer;" title="Shop bearbeiten" onclick="shop_edit();" />';
						$html += '			<img src="<?php echo PATH; ?>images/icons/24x24/remove.png" style="cursor:pointer;" title="Shop entfernen" onclick="shop_remove();" />';
						$html += '		</td>';
						$html += '	<tr>';
					});
					$html += '</table>';
					$("#view").html($html);
				}
			);
		}
	</script>

<?php
	//REMOVE
	if (isset($_POST["form_button"]) and $_POST["form_button"]=="Artikel löschen")
    {
		if ($_POST["id_article"]<=0) echo '<div class="failure">Es konnte keine ID für den Artikel gefunden werden!</div>';
		else
		{
			q("DELETE FROM cms_articles WHERE id_article=".$_POST["id_article"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Artikel erfolgreich gelöscht!</div>';
		}
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">'.t("Backend").'</a>';
	echo ' > <a href="backend_administration_index.php?lang='.$_GET["lang"].'">'.t("Administration").'</a>';
	echo ' > Shops';
	echo '</p>';

	//VIEW
	echo '<div id="view"></div>';
	echo '<script> view(); </script>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>