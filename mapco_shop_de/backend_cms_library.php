<?php
	include("config.php");
	

	//Fotos exportieren
	/*
	if($_POST["form_button"]=="Fotos exportieren")
	{
		$min_time=mktime($_POST["form_hour"], $_POST["form_min"], 0, $_POST["form_month"], $_POST["form_day"], $_POST["form_year"]);
		$handle=fopen('mapco_'.$_POST["form_dir"].'.zip', "w");
		fclose($handle);
		
		//zip files
		$zip = new ZipArchive;
		if ($zip->open('mapco_'.$_POST["form_dir"].'.zip') === TRUE)
		{
			$handle=opendir("fotos57/abbildungen/".$_POST["form_dir"]."/");
			while (false !== ($file = readdir($handle)))
			{
				if ($file!="." and $file!="..")
				{
					if (filemtime('fotos57/abbildungen/'.$_POST["form_dir"].'/'.$file)>=$min_time)
					{
//							echo 'fotos57/abbildungen/'.$_POST["form_dir"].'/'.$file."<br />";
						if ($zip->addFile('fotos57/abbildungen/'.$_POST["form_dir"].'/'.$file, $file))
						{
			//				echo 'Datei "'.$file.'" in Archiv hinzugefügt.<br />';
						}
						else die('<p>Fehler beim Hinzufügen der Datei "'.$file.'".</p>');
					}
				}
			}
			$zip->close();
		}
		else echo '<p>Fehler beim Öffnen des ZIP-Archivs.</p>';

		//export as zip
		$archiveName="mapco_".$_POST["form_dir"].".zip";
		header("Pragma: public"); 
		header("Expires: 0"); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Cache-Control: private",false); 
		header("Content-Type: application/zip"); 
		header("Content-Disposition: attachment; filename=".basename($archiveName).";" ); 
		header("Content-Transfer-Encoding: binary"); 
		header("Content-Length: ".filesize($archiveName)); 
		readfile($archiveName);	
	}
	*/


	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
<script language="javascript">
	$(document).ready(function()
	{
jQuery(function($){
        $.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
                closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
                prevText: '&#x3c;zurück', prevStatus: 'letzten Monat zeigen',
                nextText: 'Vor&#x3e;', nextStatus: 'nächsten Monat zeigen',
                currentText: 'heute', currentStatus: '',
                monthNames: ['Januar','Februar','März','April','Mai','Juni',
                'Juli','August','September','Oktober','November','Dezember'],
                monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
                'Jul','Aug','Sep','Okt','Nov','Dez'],
                monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
                weekHeader: 'Wo', weekStatus: 'Woche des Monats',
                dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
                dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
                dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
                dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
                dateFormat: 'dd.mm.yy', firstDay: 1, 
                initStatus: 'Wähle ein Datum', isRTL: false};
        $.datepicker.setDefaults($.datepicker.regional['de']);
});		$("#datepicker").datepicker( { "dateFormat":"D dd.mm.yy" });
	});
	
	function export_image()
	{
		var sanduhrObj = document.getElementById("export");
		var sanduhrImg = new Image();
		sanduhrImg.src = "images/icons/128x128/clock.png";
		sanduhrImg.onload = function ()
		{
				export_images();
		}
		sanduhrObj.appendChild(sanduhrImg);
	}

	function export_images()
	{
		var date=document.getElementById("datepicker").value;
		var hour=document.getElementById("datepicker_hour").value;
		var minute=document.getElementById("datepicker_minute").value;
		var format=document.getElementById("format").value;
		var response=ajax("modules/backend_cms_library_export.php?date="+date+"&hour="+hour+"&minute="+minute+"&format="+format, false);
		var files=response.split("<br />");
		if (files.length>1)
		{
			for (i=1; i<files.length; i++)
			{
//				document.getElementById("export").innerHTML=i;
				var file=files[i].split("|");
				var response=ajax("modules/backend_cms_library_export2.php?zipfile="+encodeURIComponent(files[0])+"&file="+encodeURIComponent(file[0])+"&filename="+encodeURIComponent(file[1])+"&format="+format, false);
//				alert(response);
			}
			document.getElementById("export").style.display="none";
			popup_window(200, 100, "Fotoexport abgeschlossen", '<a href="modules/'+files[0]+'">Download</a>');
		}
		else
		{
			document.getElementById("export").style.display="none";
			popup_window(200, 100, "Fehler", '<p>Keine Fotos im angegebenen Zeitraum gefunden.</p>');
		}
	}
</script>
<?php
	//EXPORT WINDOW
	echo '<div id="export" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Fotos exportieren</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="document.getElementById(\'export\').style.display=\'none\';" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datum</td>';
	echo '		<td>';
	echo '			<input id="datepicker" type="text" name="date" value="'.date("D d.m.Y", time()).'" /> ';
	echo '			<input id="datepicker_hour" style="width:20px;" type="text" name="hour" value="0" /> : ';
	echo '			<input id="datepicker_minute" style="width:20px;" type="text" name="minute" value="0" /> Uhr';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Format</td>';
	echo '		<td>';
	echo '			<select id="format" name="format">';
	echo '				<option value="0">JPG</option>';
	echo '				<option value="1">TecDoc</option>';
	echo '				<option value="2">IDIMS</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"><input class="formbutton" type="button" value="Export starten" onclick="export_images();" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';


	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > '.t("Fotoarchiv");
	echo '</p>';
	
	echo '<br /><br /><p>Das Fotoarchiv wird derzeit überarbeitet. Bitte versuchen Sie es später noch einmal.</p>';
	include("templates/".TEMPLATE_BACKEND."/footer.php");
	exit;


	//DEBUG UPLOAD FUNCTION
	/*
	echo '<form action="modules/backend_cms_library_upload.php" method="post" enctype="multipart/form-data">';
	echo '	<input type="file" name="Filedata" />';
	echo '	<input type="submit" />';
	echo '</form>';
	*/

	//VIEW
	echo '<h1>'.t("Fotoarchiv");
	echo '	<div style="float:right;" class="formbutton"><span style="margin:0;" id="spanButtonPlaceholder"></span></div>';
	echo '	<input style="float:right;" class="formbutton" type="button" value="Fotos exportieren" onclick="document.getElementById(\'export\').style.display=\'block\';" />';
	echo '</h1>';


	//Foto löschen
	if($_POST["form_button"]=="Foto löschen")
	{
		if($_POST["form_artnr"]=="") echo '<div class="failure">Die Artikelnummer des Fotos konnte nicht gefunden werden!</div>';
		elseif($_POST["form_buchstabe"]=="") echo '<div class="failure">Die Fotozuordnung konnten nicht gefunden werden!</div>';
		else
		{

			$filename=str_replace("/", "_", $_POST["form_artnr"]);
			unlink("fotos57/abbildungen/druck/".$filename.$_POST["form_buchstabe"].'.jpg') or die(E_WARNING);
			unlink("fotos57/abbildungen/idims/".$filename.$_POST["form_buchstabe"].'.bmp') or die(E_WARNING);
			unlink("fotos57/abbildungen/tecdoc/".$filename.$_POST["form_buchstabe"].'.bmp') or die(E_WARNING);
			unlink("fotos57/abbildungen/web/".$filename.$_POST["form_buchstabe"].'.jpg') or die(E_WARNING);
			echo '<div class="success">Foto '.$filename.$_POST["form_buchstabe"].' erfolgreich gelöscht!</div>';
		}
	}

?>
		<script type="text/javascript" src="modules/flashupload/swfupload.js"></script>
		<script type="text/javascript" src="modules/flashupload/js/handlers2.js"></script>
		<script type="text/javascript">
		var swfu;
		window.onload = function ()
		{
			swfu = new SWFUpload({
				// Backend Settings
				upload_url: "modules/backend_cms_library_upload.php",
				post_params: {"PHPSESSID": "<?php echo session_id(); ?>" },

				// File Upload Settings
				file_size_limit : "0",	// unlimited
				file_types : "*.jpg",
				file_types_description : "JPG Images",
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
		button_text : '<span class="button">Fotos hinzufügen</span>',
		button_text_style : '.button { text-align:center; font-family: Helvetica, Arial, sans-serif; font-size: 14pt; }',
//      button_text_top_padding: 3,
//      button_text_left_padding: 30,
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
		};
	</script>
<?php		
		//Artikelgruppen-Auswahl
		$artgr=array();
		$artgr_title=array();
		$results=q("SELECT * FROM `cms_menuitems` WHERE menu_id=5 AND menuitem_id>0 ORDER BY title;", $dbweb, __FILE__, __LINE__);
		echo '<table style="width:600px;">';
		echo '	<tr><th colspan="2">'.t("Suchfunktion").'</th></tr>';
		echo '	<tr>';
		echo '		<td>'.t("Artikelgruppe").'</td>';
		echo '		<td>';
		echo '			<select id="id_menuitem" name="id_menuitem" onchange="view();">';
		while($row=mysqli_fetch_array($results))
		{
			if ($_GET["id_menuitem"]==$row["id_menuitem"]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$row["id_menuitem"].'">'.$row["title"].'</option>';
		}
		echo '			</select>';
		echo '		</td>';
		echo '	</tr>';
		
		echo '	<tr>';
		echo '		<td>'.t("Filter").'</td>';
		echo '		<td>';
		echo '			<select id="show" onchange="view()">';
		echo '	<option value="showall">alle anzeigen</option>';
		echo '	<option value="showphotos">nur mit Fotos anzeigen</option>';
		echo '	<option value="showmissing">nur ohne Fotos anzeigen</option>';
		echo '	<option value="showdouble">nur mit mehreren Fotos anzeigen</option>';
		echo '			</select>';
		echo '		</td>';
		echo '	</tr>';
		echo '	<tr><td>Suchtext</td><td><input id="search" type="text" onkeyup="view();" /></td></tr>';
		echo '</table>';
		
		echo '<div id="view"></div>';
?>

	<script>
		function view()
		{
			var id_menuitem = document.getElementById("id_menuitem").value;
			var show = document.getElementById("show").value;
			var search = document.getElementById("search").value;
//			alert("modules/backend_cms_library_view.php?lang=<?php echo $_GET["lang"]; ?>&id_menuitem="+id_menuitem+'&show='+show);
			var response = ajax("modules/backend_cms_library_view.php?lang=<?php echo $_GET["lang"]; ?>&id_menuitem="+id_menuitem+'&show='+show+'&search='+encodeURIComponent(search), false);
			document.getElementById("view").innerHTML=response;
		}
		function remove(id_file)
		{
			if (confirm("Foto wirklich löschen?"))
			{
				var response = ajax("modules/backend_cms_library_remove.php?id_file="+id_file, false);
				view();
			}
		}
		view();
	</script>

<?php
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>