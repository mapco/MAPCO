<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script type="text/javascript" src="modules/flashupload/swfupload.js"></script>
    <script type="text/javascript" src="modules/flashupload/js/handlers.js"></script>
    <script type="text/javascript">
		var press_release_edit_file="";
		var press_report_edit_file="";
	
		//FILE UPLOAD
		var swfu;
		window.onload = function () {
			swfu = new SWFUpload({
				// Backend Settings
				upload_url: "modules/backend_cms_press_release.php?action=upload",
				post_params: {"PHPSESSID": "<?php echo session_id(); ?>"},
	
				// File Upload Settings
				file_size_limit : "0",	// unlimited
				file_types : "*.*",
				file_types_description : "JPEG-Dateien",
				file_upload_limit : "0",
	
				// Event Handler Settings - these functions as defined in Handlers.js
				//  The handlers are not part of SWFUpload but are part of my website and control how
				//  my website reacts to the SWFUpload events.
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : fileDialogComplete,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
	
				// Button Settings
				button_image_url : "",
				button_placeholder_id : "spanButtonPlaceholder",
				button_width: 130,
				button_height: 20,
				button_text : '<span class="button">Datei hochladen</span>',
				button_text_style : '.button { width:200px; background-color:#ff0000; padding:0px 10px 0 10px; text-align:center; font-family: Helvetica, Arial, sans-serif; font-size: 14pt; float:left; }',
				button_text_top_padding: 5,
	//            button_text_left_padding: 30,
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND,
				
				// Flash Settings
				flash_url : "modules/flashupload/swfupload.swf",
	
				custom_settings : {
					upload_target : "divFileProgressContainer"
				},
				
				// Debug Settings
				debug: false
			});
			press_report_edit_upload = new SWFUpload({
				// Backend Settings
				upload_url: "modules/backend_cms_press_report.php?action=upload",
				post_params: {"PHPSESSID": "<?php echo session_id(); ?>"},
	
				// File Upload Settings
				file_size_limit : "0",	// unlimited
				file_types : "*.*",
				file_types_description : "JPEG-Dateien",
				file_upload_limit : "0",
	
				// Event Handler Settings - these functions as defined in Handlers.js
				//  The handlers are not part of SWFUpload but are part of my website and control how
				//  my website reacts to the SWFUpload events.
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : fileDialogComplete,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
	
				// Button Settings
				button_image_url : "",
				button_placeholder_id : "press_report_edit_placeholder",
				button_width: 130,
				button_height: 20,
				button_text : '<span class="button">Datei hochladen</span>',
				button_text_style : '.button { width:200px; background-color:#ff0000; padding:0px 10px 0 10px; text-align:center; font-family: Helvetica, Arial, sans-serif; font-size: 14pt; float:left; }',
				button_text_top_padding: 5,
	//            button_text_left_padding: 30,
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND,
				
				// Flash Settings
				flash_url : "modules/flashupload/swfupload.swf",
	
				custom_settings : {
					upload_target : "press_report_edit_fileprogress"
				},
				
				// Debug Settings
				debug: false
			});
		};
		function uploadSuccess(file, serverData, response) {
			try {
		//		var progress = new FileProgress(file,  this.customSettings.upload_target);
				if (serverData.substring(0, 8) === "TMPFILE:")
				{
					press_release_edit_file=serverData.substring(8);
					document.getElementById("press_release_edit_file").innerHTML=file.name;
		//			progress.setStatus("Vorschaubild erstellt.");
		//			progress.toggleCancel(false);
				}
				else
				{
					progress.setStatus("Error.");
					progress.toggleCancel(false);
				}
		
			} catch (ex) {
				this.debug(ex);
			}
		}


		function view()
		{
			var response=ajax("modules/backend_cms_press_view.php", false);
			document.getElementById("view").innerHTML=response;
		}

		function move(direction, id)
		{
			var response=ajax("modules/backend_cms_articles_move.php?direction="+direction+"&id_article="+id, false);
			view();
		}

		function press_release_add()
		{
			var title = document.getElementById("press_release_add_title").value;
			var description = document.getElementById("press_release_add_description").value;
			response=ajax("modules/backend_cms_press_release.php?action=add&title="+encodeURIComponent(title)+"&description="+encodeURIComponent(description), false);
			if (response!="") show_status(response);
			else
			{
				view();
				document.getElementById("press_release_add_title").value="";
				document.getElementById("press_release_add_description").value="";
				showhide('press_release_add');
				hide('status');
			}
		}
		
		function press_release_edit()
		{
			var title = document.getElementById("press_release_edit_title").value;
			var description = document.getElementById("press_release_edit_description").value;
			var filename = document.getElementById("press_release_edit_file").innerText;
			response=ajax("modules/backend_cms_press_release.php?action=edit&title="+encodeURIComponent(title)+"&file="+encodeURIComponent(press_release_edit_file)+"&filename="+encodeURIComponent(filename)+"&description="+encodeURIComponent(description)+"&id_press="+press_release_edit_id, false);
			if (response!="") show_status(response);
			else
			{
				view();
				document.getElementById("press_release_edit_title").value="";
				document.getElementById("press_release_edit_description").value="";
				document.getElementById("divFileProgressContainer").innerHTML="";
				press_release_edit_file="";
				showhide('press_release_edit');
				hide('status');
			}
		}
		
		function press_release_remove()
		{
			response=ajax("modules/backend_cms_press_release.php?action=remove&id_press="+press_release_remove_id, false);
			if (response!="") show_status(response);
			else
			{
				view();
				showhide('press_release_remove');
				hide('status');
			}
		}
		
		function press_release_order(id_press, direction)
		{
			response=ajax("modules/backend_cms_press_release.php?action=order&id_press="+id_press+"&direction="+direction, false);
			if (response!="") show_status(response);
			else
			{
				view();
			}
		}
		
		function press_release_cancel()
		{
			view();
			document.getElementById("press_release_add_title").value="";
			document.getElementById("press_release_add_description").value="";
			hide('press_release_add');
			document.getElementById("press_release_edit_title").value="";
			document.getElementById("press_release_edit_description").value="";
			hide('press_release_edit');
			hide('press_release_remove');
			hide('status');
		}

		function press_report_add()
		{
			var title = document.getElementById("press_report_add_title").value;
			var description = document.getElementById("press_report_add_description").value;
			response=ajax("modules/backend_cms_press_report.php?action=add&title="+encodeURIComponent(title)+"&description="+encodeURIComponent(description), false);
			if (response!="") show_status(response);
			else
			{
				view();
				document.getElementById("press_report_add_title").value="";
				document.getElementById("press_report_add_description").value="";
				showhide('press_report_add');
				hide('status');
			}
		}
		
		function press_report_edit()
		{
			var title = document.getElementById("press_report_edit_title").value;
			var description = document.getElementById("press_report_edit_description").value;
			var filename = document.getElementById("press_report_edit_file").innerText;
			response=ajax("modules/backend_cms_press_report.php?action=edit&title="+encodeURIComponent(title)+"&file="+encodeURIComponent(press_report_edit_file)+"&filename="+encodeURIComponent(filename)+"&description="+encodeURIComponent(description)+"&id_press="+press_report_edit_id, false);
			if (response!="") show_status(response);
			else
			{
				view();
				document.getElementById("press_report_edit_title").value="";
				document.getElementById("press_report_edit_description").value="";
				document.getElementById("divFileProgressContainer").innerHTML="";
				press_report_edit_file="";
				showhide('press_report_edit');
				hide('status');
			}
		}
		
		function press_report_remove()
		{
			response=ajax("modules/backend_cms_press_report.php?action=remove&id_press="+press_report_remove_id, false);
			if (response!="") show_status(response);
			else
			{
				view();
				showhide('press_report_remove');
				hide('status');
			}
		}
		
		function press_report_order(id_press, direction)
		{
			response=ajax("modules/backend_cms_press_report.php?action=order&id_press="+id_press+"&direction="+direction, false);
			if (response!="") show_status(response);
			else
			{
				view();
			}
		}
		
		function press_report_cancel()
		{
			view();
			document.getElementById("press_report_add_title").value="";
			document.getElementById("press_report_add_description").value="";
			hide('press_report_add');
			document.getElementById("press_report_edit_title").value="";
			document.getElementById("press_report_edit_description").value="";
			hide('press_report_edit');
			hide('press_report_remove');
			hide('status');
		}

		function showhide(id)
		{
			var display=document.getElementById(id).style.display;
			if (display=="block")
			{
				document.getElementById(id).style.display="none";
			}
			else
			{
				document.getElementById(id).style.display="block";
			}
		}

		function hide(id)
		{
			document.getElementById(id).style.display="none";
		}

	</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > Pressemitteilungen';
	echo '</p>';

	//VIEW
	echo '<div id="view"></div>';
	echo '<script> view(); </script>';

	//PRESS RELEASE ADD WINDOW
	echo '<div id="press_release_add" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Pressemitteilung hinzufügen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="press_release_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td>';
	echo '			<input id="press_release_add_title" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Memo</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="press_release_add_description"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="press_release_add();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="press_release_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//PRESS RELEASE EDIT WINDOW
	echo '<div id="press_release_edit" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Pressemitteilung bearbeiten</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="press_release_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td>';
	echo '			<input id="press_release_edit_title" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Memo</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="press_release_edit_description"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datei</td>';
	echo '		<td>';
	echo '			<div id="press_release_edit_file"></div>';
	echo '			<div id="divFileProgressContainer"></div>';
	echo '			<div style="padding:1px 0px 2px 0px;" class="formbutton"><span id="spanButtonPlaceholder"></span></div>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="press_release_edit();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="press_release_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//PRESS RELEASE REMOVE WINDOW
	echo '<div id="press_release_remove" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Pressemitteilung wirklich löschen?</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="press_release_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">Sind Sie sicher, dass Sie diese Versandart löschen möchten?</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>';
	echo '			<input class="formbutton" type="button" value="Ja" onclick="press_release_remove();" />';
	echo '			<input class="formbutton" type="button" value="Nein" onclick="press_release_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	//PRESS REPORT ADD WINDOW
	echo '<div id="press_report_add" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Pressebericht hinzufügen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="press_report_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td>';
	echo '			<input id="press_report_add_title" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Memo</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="press_report_add_description"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="press_report_add();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="press_report_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//PRESS REPORT EDIT WINDOW
	echo '<div id="press_report_edit" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Pressemitteilung bearbeiten</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="press_report_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td>';
	echo '			<input id="press_report_edit_title" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Memo</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="press_report_edit_description"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datei</td>';
	echo '		<td>';
	echo '			<div id="press_report_edit_file"></div>';
	echo '			<div id="press_report_edit_fileprogress"></div>';
	echo '			<div style="padding:1px 0px 2px 0px;" class="formbutton"><span id="press_report_edit_placeholder"></span></div>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="press_report_edit();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="press_report_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//PRESS REPORT REMOVE WINDOW
	echo '<div id="press_report_remove" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Pressemitteilung wirklich löschen?</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="press_report_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">Sind Sie sicher, dass Sie diese Versandart löschen möchten?</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>';
	echo '			<input class="formbutton" type="button" value="Ja" onclick="press_report_remove();" />';
	echo '			<input class="formbutton" type="button" value="Nein" onclick="press_report_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>