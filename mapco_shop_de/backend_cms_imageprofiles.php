<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script>
	var id_imageprofile=0;
	
	function imageformat_add()
	{
		$("#imageformat_add_title").val("");
		$("#imageformat_add_width").val("");
		$("#imageformat_add_height").val("");
		$("#imageformat_add_aoe").val("");
		$("#imageformat_add_zc").val("");
		$("#imageformat_add_zoom").val(100);
		$("#imageformat_add_background_image").val("");
		$("#imageformat_add_watermark").val("");
		$("#imageformat_add_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { imageformat_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bildformat hinzufügen",
			width:600
		});
	}
	
	function imageformat_add_save()
	{
		var title=$("#imageformat_add_title").val();
		var width=$("#imageformat_add_width").val();
		var height=$("#imageformat_add_height").val();
		var aoe=$("#imageformat_add_aoe").val();
		var zc=$("#imageformat_add_zc").val();
		var zoom=$("#imageformat_add_zoom").val();
		var background_image=$("#imageformat_add_background_image").val();
		var watermark=$("#imageformat_add_watermark").val();
		var watermark_position=$("#imageformat_add_watermark_position").val();
		var watermark_opacity=$("#imageformat_add_watermark_opacity").val();
		$.post("modules/backend_cms_imageprofiles_actions.php", { action:"imageformat_add", imageprofile_id:id_imageprofile, title:title, width:width, height:height, aoe:aoe, zc:zc, zoom:zoom, background_image:background_image, watermark:watermark, watermark_position:watermark_position, watermark_opacity:watermark_opacity },
			function(data)
			{
				if (data=="")
				{
					$("#imageformat_add_dialog").dialog("close");
					show_status("Das Bildformat wurde erfolgreich angelegt.");
					view();
				}
				else
				{
					show_status(data);
				}
			}
		);
	}

	function imageformat_check()
	{
		$("#image_fix_dialog").dialog
		({	buttons:{},
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bildanalyse",
		});
		$("#image_fix_dialog").html("Analysiere Artikel...");
		$.post("modules/backend_cms_imageprofiles_actions.php", { action:"articles_get", id_imageprofile:id_imageprofile },
			function(data)
			{
				if (data=="")
				{
					$("#image_fix_dialog").html("Keine Fehler gefunden.");
				}
				else
				{
					articles=data.split("\n");
					imageformat_check2(articles);
				}
			}
		);
	}

	function imageformat_check2(article_ids)
	{
		$("#image_fix_dialog").html("Analysiere Originalbilder...");
		$.post("modules/backend_cms_imageprofiles_actions.php", { action:"images_get", article_ids:article_ids },
			function(data)
			{
				if (data=="")
				{
					$("#image_fix_dialog").html("Keine Fehler gefunden.");
				}
				else
				{
					originals=data.split("\n");
					imageformat_check3(originals);
				}
			}
		);
	}

	function imageformat_check3(originals)
	{
		$("#image_fix_dialog").html("Suche Fehler...");
		$.post("modules/backend_cms_imageprofiles_actions.php", { action:"errors_get", id_imageprofile:id_imageprofile, originals:originals },
			function(data)
			{
				if (data=="")
				{
					$("#image_fix_dialog").html("Keine Fehler gefunden.");
				}
				else
				{
					errors=data.split("\n");
					$("#image_fix_dialog").html(errors.length+" Fehler gefunden.");
					$("#image_fix_dialog").dialog( "option", "buttons", { "Fehler beheben": function() { imageformat_fix(errors, 0); } } );
				}
			}
		);
	}
	function imageformat_fix(errors, i)
	{
		if (i<errors.length)
		{
			$("#image_fix_dialog").html("Behebe Fehler "+(i+1)+" von "+errors.length+"<br /><br />("+errors[i]+")");
			$.post("modules/backend_cms_imageprofiles_actions.php", { action:"fix_image", error:errors[i] },
				function(data)
				{
					if (data=="")
					{
						imageformat_fix(errors, i+1);
					}
					else
					{
						show_status2(data);
					}
				}
			);
		}
		else
		{
			$('#image_fix_dialog').dialog('option', 'buttons', {}); 
			$("#image_fix_dialog").html("Alle Fehler behoben.");
		}
	}

	function imageformat_edit(id_imageformat, title, width, height, aoe, zc, zoom, background_image, watermark, watermark_position, watermark_opacity)
	{
		$("#imageformat_edit_id_imageformat").val(id_imageformat);
		$("#imageformat_edit_title").val(title);
		$("#imageformat_edit_width").val(width);
		$("#imageformat_edit_height").val(height);
		$("#imageformat_edit_aoe").val(aoe);
		$("#imageformat_edit_zc").val(zc);
		$("#imageformat_edit_zoom").val(zoom);
		$("#imageformat_edit_background_image").val(background_image);
		$("#imageformat_edit_watermark").val(watermark);
		$("#imageformat_edit_watermark_position").val(watermark_position);
		$("#imageformat_edit_watermark_opacity").val(watermark_opacity);
		$("#imageformat_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { imageformat_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bildformat bearbeiten",
			width:600
		});
	}
	
	function imageformat_edit_save()
	{
		var id_imageformat=$("#imageformat_edit_id_imageformat").val();
		var title=$("#imageformat_edit_title").val();
		var width=$("#imageformat_edit_width").val();
		var height=$("#imageformat_edit_height").val();
		var aoe=$("#imageformat_edit_aoe").val();
		var zc=$("#imageformat_edit_zc").val();
		var zoom=$("#imageformat_edit_zoom").val();
		var background_image=$("#imageformat_edit_background_image").val();
		var watermark=$("#imageformat_edit_watermark").val();
		var watermark_position=$("#imageformat_edit_watermark_position").val();
		var watermark_opacity=$("#imageformat_edit_watermark_opacity").val();
		$.post("modules/backend_cms_imageprofiles_actions.php", { action:"imageformat_edit", id_imageformat:id_imageformat, title:title, width:width, height:height, aoe:aoe, zc:zc, zoom:zoom, background_image:background_image, watermark:watermark, watermark_position:watermark_position, watermark_opacity:watermark_opacity },
			function(data)
			{
				if (data=="")
				{
					$("#imageformat_edit_dialog").dialog("close");
					show_status("Das Bildformat wurde erfolgreich geändert.");
					view();
				}
				else
				{
					show_status(data);
				}
			}
		);
	}

	function imageformat_remove(id_imageformat)
	{
		$("#imageformat_remove_id_imageformat").val(id_imageformat);
		$("#imageformat_remove_dialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { imageformat_remove_accept(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bildprofil löschen",
		});
	}
	
	function imageformat_remove_accept()
	{
		var id_imageformat=$("#imageformat_remove_id_imageformat").val();
		$.post("modules/backend_cms_imageprofiles_actions.php", { action:"imageformat_remove", id_imageformat:id_imageformat },
			function(data)
			{
				if (data=="")
				{
					$("#imageformat_remove_dialog").dialog("close");
					show_status("Das Bildformat wurde erfolgreich gelöscht.");
					view();
				}
				else
				{
					show_status(data);
				}
			}
		);
	}

	function imageprofile_add()
	{
		$("#imageprofile_add_title").val("");
		$("#imageprofile_add_description").val("");
		$("#imageprofile_add_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { imageprofile_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bildprofil hinzufügen",
		});
	}
	
	function imageprofile_add_save()
	{
		var title=$("#imageprofile_add_title").val();
		var description=$("#imageprofile_add_description").val();
		$.post("modules/backend_cms_imageprofiles_actions.php", { action:"imageprofile_add", title:title, description:description },
			function(data)
			{
				if (data=="")
				{
					$("#imageprofile_add_dialog").dialog("close");
					show_status("Das Bildprofil wurde erfolgreich angelegt.");
					view();
				}
				else
				{
					show_status(data);
				}
			}
		);
	}

	function imageprofile_edit(id_imageprofile, title, description)
	{
		$("#imageprofile_edit_title").val(title);
		$("#imageprofile_edit_description").val(description);
		$("#imageprofile_edit_id_imageprofile").val(id_imageprofile);
		$("#imageprofile_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { imageprofile_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bildprofil bearbeiten",
		});
	}
	
	function imageprofile_edit_save()
	{
		var id_imageprofile=$("#imageprofile_edit_id_imageprofile").val();
		var title=$("#imageprofile_edit_title").val();
		var description=$("#imageprofile_edit_description").val();
		$.post("modules/backend_cms_imageprofiles_actions.php", { action:"imageprofile_edit", id_imageprofile:id_imageprofile, title:title, description:description },
			function(data)
			{
				if (data=="")
				{
					$("#imageprofile_edit_dialog").dialog("close");
					show_status("Das Bildprofil wurde erfolgreich geändert.");
					view();
				}
				else
				{
					show_status(data);
				}
			}
		);
	}

	function imageprofile_remove(id_imageprofile)
	{
		$("#imageprofile_remove_id_imageprofile").val(id_imageprofile);
		$("#imageprofile_remove_dialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { imageprofile_remove_accept(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bildprofil löschen",
		});
	}
	
	function imageprofile_remove_accept()
	{
		var id_imageprofile=$("#imageprofile_remove_id_imageprofile").val();
		$.post("modules/backend_cms_imageprofiles_actions.php", { action:"imageprofile_remove", id_imageprofile:id_imageprofile },
			function(data)
			{
				if (data=="")
				{
					$("#imageprofile_remove_dialog").dialog("close");
					show_status("Das Bildprofil wurde erfolgreich gelöscht.");
					view();
				}
				else
				{
					show_status(data);
				}
			}
		);
	}

	function view()
	{
		$.post("modules/backend_cms_imageprofiles_actions.php?lang=<?php echo $_GET["lang"]; ?>", { action:"view", id_imageprofile:id_imageprofile },
			function(data)
			{
				$("#view").html(data);
				$(function() {
					$( "#imageprofiles" ).sortable( { items:"li:not(.header)" } );
					$( "#imageprofiles" ).sortable( { cancel:".header"} );
					$( "#imageprofiles" ).disableSelection();
					$( "#imageprofiles" ).bind( "sortupdate", function(event, ui)
					{
						var list = $('#imageprofiles').sortable('toArray');
						$.post("modules/backend_cms_imageprofiles_actions.php", { action:"imageprofile_sort", list:list}, function(data) { show_status(data); view(); });
					});
				});
				$(function() {
					$( "#imageformats" ).sortable( { items:"li:not(.header)" } );
					$( "#imageformats" ).sortable( { cancel:".header"} );
					$( "#imageformats" ).sortable({cancel: ".header"});
					$( "#imageformats" ).disableSelection();
					$( "#imageformats" ).bind( "sortupdate", function(event, ui)
					{
						var list = $('#imageformats').sortable('toArray');
						$.post("modules/backend_cms_imageprofiles_actions.php", { action:"imageformat_sort", list:list, id_imageprofile:id_imageprofile }, function(data) { show_status(data); view(); });
					});
				});
			}
		);
	}

</script>

<?php	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">'.t("Backend").'</a>';
	echo ' > <a href="backend_cms_index.php?lang='.$_GET["lang"].'">'.t("Content Management").'</a>';
	echo ' > Bildprofile';
	echo '</p>';

	echo '<h1>Bildprofile</h1>';

	//LIST
	echo '<div id="view"></div>';
	echo '<script> view(); </script>';
	
	//IMAGEFORMAT ADD DIALOG
	echo '<div id="imageformat_add_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input type="text" id="imageformat_add_title" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Breite</td>';
	echo '			<td><input style="width:50px;" type="text" id="imageformat_add_width" value="" /> Pixel</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Höhe</td>';
	echo '			<td><input style="width:50px;" type="text" id="imageformat_add_height" value="" /> Pixel</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Vergrößern</td>';
	echo '			<td>';
	echo '				<select id="imageformat_add_aoe">';
	echo '					<option value="0">Nein</option>';
	echo '					<option value="1">Ja</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Zoombeschnitt</td>';
	echo '			<td>';
	echo '				<select id="imageformat_add_zc">';
	echo '					<option value="0">Nein</option>';
	echo '					<option value="1">Ja</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Zoom</td>';
	echo '			<td><input style="width:40px;" type="text" id="imageformat_add_zoom" value="100" /> %</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Hintergrundbild</td>';
	echo '			<td><input style="width:300px;" type="text" id="imageformat_add_background_image" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Wasserzeichen</td>';
	echo '			<td>';
	echo '				<input style="width:250px;" type="text" id="imageformat_add_watermark" value="" />';
	echo '				<select id="imageformat_add_watermark_position">';
	echo '					<option value="C">zentriert</option>';
	echo '					<option value="*">gekachelt</option>';
	echo '					<option value="R">rechts</option>';
	echo '					<option value="L">links</option>';
	echo '					<option value="T">oben</option>';
	echo '					<option value="B">unten</option>';
	echo '					<option value="BR">unten rechts</option>';
	echo '					<option value="BL">unten links</option>';
	echo '					<option value="TR">oben rechts</option>';
	echo '					<option value="TL">oben links</option>';
	echo '				</select>';
	echo '				<input style="width:50px;" type="text" id="imageformat_add_watermark_opacity" value="" /> %';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input type="hidden" id="imageformat_edit_id_imageformat" value="" />';
	echo '</div>';
	
	//IMAGEFORMAT EDIT DIALOG
	echo '<div id="imageformat_edit_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input type="text" id="imageformat_edit_title" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Breite</td>';
	echo '			<td><input type="text" id="imageformat_edit_width" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Höhe</td>';
	echo '			<td><input type="text" id="imageformat_edit_height" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>AOE</td>';
	echo '			<td>';
	echo '				<select id="imageformat_edit_aoe">';
	echo '					<option value="0">Nein</option>';
	echo '					<option value="1">Ja</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>ZC</td>';
	echo '			<td>';
	echo '				<select id="imageformat_edit_zc">';
	echo '					<option value="0">Nein</option>';
	echo '					<option value="1">Ja</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Zoom</td>';
	echo '			<td><input style="width:40px;" type="text" id="imageformat_edit_zoom" value="" /> %</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Hintergrundbild</td>';
	echo '			<td><input style="width:300px;" type="text" id="imageformat_edit_background_image" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Wasserzeichen</td>';
	echo '			<td>';
	echo '				<input style="width:250px;" type="text" id="imageformat_edit_watermark" value="" />';
	echo '				<select id="imageformat_edit_watermark_position">';
	echo '					<option value="C">zentriert</option>';
	echo '					<option value="*">gekachelt</option>';
	echo '					<option value="R">rechts</option>';
	echo '					<option value="L">links</option>';
	echo '					<option value="T">oben</option>';
	echo '					<option value="B">unten</option>';
	echo '					<option value="BR">unten rechts</option>';
	echo '					<option value="BL">unten links</option>';
	echo '					<option value="TR">oben rechts</option>';
	echo '					<option value="TL">oben links</option>';
	echo '				</select>';
	echo '				<input style="width:50px;" type="text" id="imageformat_edit_watermark_opacity" value="" /> %';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';
	
	//IMAGEFORMAT REMOVE DIALOG
	echo '<div style="display:none;" id="imageformat_remove_dialog">';
	echo '	<p>Wollen Sie das Bildformat wirklich löschen?</p>';
	echo '	<input type="hidden" id="imageformat_remove_id_imageformat" value="" />';
	echo '</div>';
	
	//IMAGEPROFILE ADD DIALOG
	echo '<div id="imageprofile_add_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input type="text" id="imageprofile_add_title" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Beschreibung</td>';
	echo '			<td><textarea id="imageprofile_add_description" style="width:160px; height:50px;"></textarea></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';
	
	//IMAGEPROFILE EDIT DIALOG
	echo '<div id="imageprofile_edit_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input type="text" id="imageprofile_edit_title" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Beschreibung</td>';
	echo '			<td><textarea id="imageprofile_edit_description" style="width:160px; height:50px;"></textarea></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input type="hidden" id="imageprofile_edit_id_imageprofile" value="" />';
	echo '</div>';
	
	//IMAGEPROFILE REMOVE DIALOG
	echo '<div style="display:none;" id="imageprofile_remove_dialog">';
	echo '	<p>Wollen Sie das Bildprofil wirklich löschen?</p>';
	echo '	<input type="hidden" id="imageprofile_remove_id_imageprofile" value="" />';
	echo '</div>';
	
	//IMAGE FIX DIALOG
	echo '<div style="display:none;" id="image_fix_dialog">';
	echo '	<p>Bitte warten...</p>';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>