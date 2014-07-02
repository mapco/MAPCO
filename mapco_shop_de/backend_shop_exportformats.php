<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	//exportformat add
	if ( isset($_FILES["csvfile"]) and $_FILES["csvfile"]["tmp_name"]!="" )
	{
		$results=q("SELECT * FROM shop_export_formats;", $dbshop, __FILE__, __LINE__);
		$ordering=mysqli_num_rows($results)+1;
		
		q("INSERT INTO shop_export_formats (title, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$_POST["title"]."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		$id_exportformat=mysqli_insert_id($dbshop);
		
		$handle=fopen($_FILES["csvfile"]["tmp_name"], "r");
		$line=fgetcsv($handle, 4096, ";");
		for($i=0; $i<sizeof($line); $i++)
		{
			q("INSERT INTO shop_export_fields (name, value, exportformat_id, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".mysqli_real_escape_string($dbshop, $line[$i])."', '', ".$id_exportformat.", ".($i+1).", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		}
	}
?>
	<script>
		var id_exportformat=0;

		function exportformat_add()
		{
			$("#exportformat_add_dialog").dialog
			({	buttons:
				[
					{ text: "Hochladen", click: function() { document.exportformat_add_form.submit(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Exportformat importieren",
				width:400
			});
		}


		function exportformat_edit(id_exportformat, title)
		{
			$("#exportformat_edit_id_exportformat").val(id_exportformat);
			$("#exportformat_edit_title").val(title);
			$("#exportformat_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { exportformat_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Exportformat bearbeiten",
				width:400
			});
		}


		function exportformat_edit_save()
		{
			var id_exportformat=$("#exportformat_edit_id_exportformat").val();
			var title=$("#exportformat_edit_title").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ExportFormatEdit", id_exportformat:id_exportformat, title:title },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					view();
					show_status("Exportformat erfolgreich aktualisiert.");
					$("#exportformat_edit_dialog").dialog("close");
					wait_dialog_hide();
				}
			);
		}


		function exportformat_remove(id_exportformat, title)
		{
			$("#exportformat_remove_id_exportformat").val(id_exportformat);
			$("#exportformat_remove_title").html(title);
			$("#exportformat_remove_dialog").dialog
			({	buttons:
				[
					{ text: "Löschen", click: function() { exportformat_remove_accept(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Exportformat löschen",
				width:400
			});
		}


		function exportformat_remove_accept()
		{
			var id_exportformat=$("#exportformat_remove_id_exportformat").val();
			var title=$("#exportformat_remove_title").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ExportFormatRemove", id_exportformat:id_exportformat, title:title },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					view();
					show_status("Exportformat "+title+" erfolgreich gelöscht.");
					$("#exportformat_remove_dialog").dialog("close");
					wait_dialog_hide();
				}
			);
		}


		function exportformat_select(id)
		{
			id_exportformat=id;
			view();
		}


		function field_edit(id_field, name, value)
		{
			$("#field_edit_id_field").val(id_field);
			$("#field_edit_name").val(name);
			$("#field_edit_value").val(value);
			$("#field_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { field_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Exportformat bearbeiten",
				width:400
			});
		}


		function field_edit_save()
		{
			var id_field=$("#field_edit_id_field").val();
			var name=$("#field_edit_name").val();
			var value=$("#field_edit_value").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ExportFieldEdit", id_field:id_field, name:name, value:value },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					view();
					show_status("Exportfeld erfolgreich aktualisiert.");
					$("#field_edit_dialog").dialog("close");
					wait_dialog_hide();
				}
			);
		}


		function view()
		{
//			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ExportFormatsView", id_exportformat:id_exportformat },
				function(data)
				{
					$("#view").html(data);
//					wait_dialog_hide();
				}
			);
		}
	</script>
<?php
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Exportformate';
	echo '</p>';

	echo '<div id="view"></div>';
	echo '<script>view();</script>';
	
	//EXPORTFORMAT ADD DIALOG
	echo '<div id="exportformat_add_dialog" style="display:none;">';
	echo '	<form method="post" name="exportformat_add_form" enctype="multipart/form-data">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input type="text" name="title" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>CSV-Datei</td>';
	echo '			<td><input type="file" name="csvfile"></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	</form>';
	echo '</div>';


	//EXPORTFORMAT EDIT DIALOG
	echo '<div id="exportformat_edit_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input type="text" id="exportformat_edit_title" value="" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input type="hidden" id="exportformat_edit_id_exportformat" value="" />';
	echo '</div>';


	//EXPORTFORMAT REMOVE DIALOG
	echo '<div id="exportformat_remove_dialog" style="display:none;">';
	echo '	<p>Wollen Sie das Exportformat <span id="exportformat_remove_title"></span> wirklich löschen?</p>';
	echo '	<input type="hidden" id="exportformat_remove_id_exportformat" value="" />';
	echo '</div>';


	//EXPORTFIELD EDIT DIALOG
	echo '<div id="field_edit_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Name</td>';
	echo '			<td><input type="text" id="field_edit_name" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Wert</td>';
	echo '			<td><input type="text" id="field_edit_value" value="" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input type="hidden" id="field_edit_id_field" value="" />';
	echo '</div>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>