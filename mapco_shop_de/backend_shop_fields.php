<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
?>
	<script>
		var id_field=0;

		function field_add()
		{
			$("#field_add_title").val("");
			$("#field_add_name").val("");
			$("#field_add_dialog").dialog
			({	buttons:
				[
					{ text: "Hinzufügen", click: function() { field_add_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Feld hinzufügen",
				width:400
			});
		}


		function field_add_save()
		{
			var title=$("#field_add_title").val();
			var name=$("#field_add_name").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"FieldAdd", title:title, name:name },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					wait_dialog_hide();
					view();
					show_status("Feld erfolgreich hinzugefügt.");
					$("#field_add_dialog").dialog("close");
				}
			);
		}


		function field_edit(id_field, title, name)
		{
			$("#field_edit_id_field").val(id_field);
			$("#field_edit_title").val(title);
			$("#field_edit_name").val(name);
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
				title:"Feld bearbeiten",
				width:400
			});
		}


		function field_edit_save()
		{
			var id_field=$("#field_edit_id_field").val();
			var title=$("#field_edit_title").val();
			var name=$("#field_edit_name").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"FieldEdit", id_field:id_field, title:title, name:name },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					wait_dialog_hide();
					view();
					show_status("Feld erfolgreich aktualisiert.");
					$("#field_edit_dialog").dialog("close");
				}
			);
		}


		function field_remove(id_field, title)
		{
			$("#field_remove_id_field").val(id_field);
			$("#field_remove_title").html(title);
			$("#field_remove_dialog").dialog
			({	buttons:
				[
					{ text: "Löschen", click: function() { field_remove_accept(id_field, title); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Feld löschen",
				width:400
			});
		}


		function field_remove_accept(id_field, title)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"FieldRemove", id_field:id_field },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					wait_dialog_hide();
					view();
					show_status("Feld "+title+" erfolgreich gelöscht.");
					$("#field_remove_dialog").dialog("close");
				}
			);
		}


		function field_select(id)
		{
			id_field=id;
			view();
		}


		function value_add()
		{
			$("#value_add_title").val("");
			$("#value_add_value").val("");
			$("#value_add_dialog").dialog
			({	buttons:
				[
					{ text: "Hinzufügen", click: function() { value_add_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Wert hinzufügen",
				width:400
			});
		}


		function value_add_save()
		{
			var title=$("#value_add_title").val();
			var value=$("#value_add_value").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ValueAdd", field_id:id_field, title:title, value:value },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					wait_dialog_hide();
					view();
					show_status("Wert erfolgreich hinzugefügt.");
					$("#value_add_dialog").dialog("close");
				}
			);
		}


		function value_edit(id_value, title, value)
		{
			$("#value_edit_id_value").val(id_value);
			$("#value_edit_title").val(title);
			$("#value_edit_value").val(value);
			$("#value_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { value_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Feld bearbeiten",
				width:400
			});
		}


		function value_edit_save()
		{
			var id_value=$("#value_edit_id_value").val();
			var title=$("#value_edit_title").val();
			var value=$("#value_edit_value").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ValueEdit", id_value:id_value, title:title, value:value },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					wait_dialog_hide();
					view();
					show_status("Feld erfolgreich aktualisiert.");
					$("#value_edit_dialog").dialog("close");
				}
			);
		}


		function value_remove(id_value, title)
		{
			$("#value_remove_id_value").val(id_value);
			$("#value_remove_title").html(title);
			$("#value_remove_dialog").dialog
			({	buttons:
				[
					{ text: "Löschen", click: function() { value_remove_accept(id_value, title); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Wert löschen",
				width:400
			});
		}


		function value_remove_accept(id_value, title)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ValueRemove", id_value:id_value },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					wait_dialog_hide();
					view();
					show_status("Wert "+title+" erfolgreich gelöscht.");
					$("#value_remove_dialog").dialog("close");
				}
			);
		}


		function view()
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"FieldsView", id_field:id_field },
				function(data)
				{
					$("#view").html(data);
					wait_dialog_hide();
				}
			);
		}
	</script>
<?php
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Felder';
	echo '</p>';

	echo '<div id="view"></div>';
	echo '<script>view();</script>';


	//FIELD ADD DIALOG
	echo '<div id="field_add_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input id="field_add_title" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Name</td>';
	echo '			<td><input id="field_add_name" type="text" value="" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';


	//FIELD EDIT DIALOG
	echo '<div id="field_edit_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input id="field_edit_title" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Name</td>';
	echo '			<td><input id="field_edit_name" type="text" value="" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input type="hidden" id="field_edit_id_field" value="" />';
	echo '</div>';


	//FIELD REMOVE DIALOG
	echo '<div id="field_remove_dialog" style="display:none;">';
	echo '	Wollen Sie das Feld <span id="field_remove_title"></span> wirklich löschen?';
	echo '	<input type="hidden" id="field_remove_id_field" value="" />';
	echo '</div>';


	//VALUE ADD DIALOG
	echo '<div id="value_add_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input id="value_add_title" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Wert</td>';
	echo '			<td><input id="value_add_value" type="text" value="" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';


	//VALUE EDIT DIALOG
	echo '<div id="value_edit_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input id="value_edit_title" type="text" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Name</td>';
	echo '			<td><input id="value_edit_value" type="text" value="" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input type="hidden" id="value_edit_id_value" value="" />';
	echo '</div>';


	//VALUE REMOVE DIALOG
	echo '<div id="value_remove_dialog" style="display:none;">';
	echo '	Wollen Sie den Wert <span id="value_remove_title"></span> wirklich löschen?';
	echo '	<input type="hidden" id="value_remove_id_value" value="" />';
	echo '</div>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>