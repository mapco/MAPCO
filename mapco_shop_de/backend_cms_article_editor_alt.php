<?php
	include("config.php");
	$leftmenu=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//CREATE
	if (!($_GET["id_article"]>0))
	{
		$query="INSERT INTO cms_articles (site_id, language_id, article_id, title, article, published, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_SESSION["id_site"].", 1, 0, '', '', 0, 1, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");";
		q($query, $dbweb, __FILE__, __LINE__);
		$_GET["id_article"]=mysqli_insert_id($dbweb);
		
		$i=1;
		$results=q("SELECT * FROM cms_articles WHERE site_id=".$_SESSION["id_site"]." AND NOT id_article=".$_GET["id_article"]." ORDER BY ordering, firstmod DESC;", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			$i++;
			q("UPDATE cms_articles SET ordering=".$i." WHERE id_article=".$row["id_article"].";", $dbweb, __FILE__, __LINE__);
		}
	}
	
?>
	<script src="modules/flashupload/swfupload.js" type="text/javascript"></script>
    <script src="modules/flashupload/js/handlers.js" type="text/javascript"></script>
    <script type="text/javascript">
		var image_edit_id;
		var attachment_edit_id

		window.onkeydown = function (e)
		{
			if (!e) var e=window.event; //FireFox
			if(window.event.ctrlKey && window.event.keyCode==83)
			{
				event.cancelBubble = true;
			    event.returnValue = false;
				save();
			}
		}
	
		window.onload = function () {
			attachament_add_upload = new SWFUpload({
				// Backend Settings
				upload_url: "modules/backend_cms_article_editor_actions.php?lang=<?php echo $_GET["lang"]; ?>&id_article=<?php echo $_GET["id_article"]; ?>&action=attachment_upload&id_user=<?php echo $_SESSION["id_user"]; ?>",
				post_params: {"PHPSESSID": "<?php echo session_id(); ?>"},
	
				// File Upload Settings
				file_size_limit : "0",	// unlimited
				file_types : "*.*",
				file_types_description : "Alle Dateien",
				file_upload_limit : "0",
	
				// Event Handler Settings - these functions as defined in Handlers.js
				//  The handlers are not part of SWFUpload but are part of my website and control how
				//  my website reacts to the SWFUpload events.
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : attachment_add_fileDialogComplete,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : attachment_add_uploadSuccess,
				upload_complete_handler : attachment_add_uploadComplete,
	
				// Button Settings
				button_image_url : "",
				button_placeholder_id : "attachment_add_button",
				button_width: 200,
				button_height: 20,
				button_text : '<span class="button">Neuen Anhang hinzufügen</span>',
				button_text_style : '.button { width:200px; background-color:#ff0000; padding:0px 10px 0 10px; text-align:center; font-family: Helvetica, Arial, sans-serif; font-size: 14pt; float:left; }',
				button_text_top_padding: 5,
	//            button_text_left_padding: 30,
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND,
				
				// Flash Settings
				flash_url : "modules/flashupload/swfupload.swf",
	
				custom_settings : {
					upload_target : "attachment_add_uploadProgress"
				},
				
				// Debug Settings
				debug: false
			});
			

			video_add_upload = new SWFUpload({
				// Backend Settings
				upload_url: "modules/backend_cms_article_editor_actions.php?lang=<?php echo $_GET["lang"]; ?>&id_article=<?php echo $_GET["id_article"]; ?>&action=video_upload&id_user=<?php echo $_SESSION["id_user"]; ?>",
				post_params: {"PHPSESSID": "<?php echo session_id(); ?>"},
	
				// File Upload Settings
				file_size_limit : "0",	// unlimited
				file_types : "*.flv",
				file_types_description : "FLV-Dateien",
				file_upload_limit : "0",
	
				// Event Handler Settings - these functions as defined in Handlers.js
				//  The handlers are not part of SWFUpload but are part of my website and control how
				//  my website reacts to the SWFUpload events.
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : video_add_fileDialogComplete,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : video_add_uploadSuccess,
				upload_complete_handler : video_add_uploadComplete,
	
				// Button Settings
				button_image_url : "",
				button_placeholder_id : "video_add_button",
				button_width: 200,
				button_height: 20,
				button_text : '<span class="button">Neues Video hinzufügen</span>',
				button_text_style : '.button { width:200px; background-color:#ff0000; padding:0px 10px 0 10px; text-align:center; font-family: Helvetica, Arial, sans-serif; font-size: 14pt; float:left; }',
				button_text_top_padding: 5,
	//            button_text_left_padding: 30,
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND,
				
				// Flash Settings
				flash_url : "modules/flashupload/swfupload.swf",
	
				custom_settings : {
					upload_target : "video_add_uploadProgress"
				},
				
				// Debug Settings
				debug: false
			});
		};
		
		function attachment_add_fileDialogComplete(numFilesSelected, numFilesQueued)
		{
			try {
				if (numFilesQueued > 0)
				{
					show("attachment_upload_window");
					this.startUpload();
				}
			} catch (ex) {
				this.debug(ex);
			}
		}

		function attachment_add_uploadComplete(file)
		{
			try {
				/*  I want the next upload to continue automatically so I'll call startUpload here */
				if (this.getStats().files_queued > 0) {
					this.startUpload();
				} else {
					var progress = new FileProgress(file,  this.customSettings.upload_target);
					progress.setComplete();
					progress.setStatus("Alle Anhänge hochgeladen.");
					progress.toggleCancel(false);
					hide("attachment_upload_window");
				}
			} catch (ex) {
				this.debug(ex);
			}
		}

		function attachment_add_uploadSuccess(file, serverData, response)
		{
			try
			{
				attachment_view();
				hide("status");
		
			} catch (ex) {
				this.debug(ex);
			}
		}

		function attachment_edit_cancel()
		{
			hide("attachment_edit");
			hide("status");
			view();
		}
		
		function attachment_edit_save()
		{
			var filename=document.getElementById("attachment_edit_filename").value;
			var description=document.getElementById("attachment_edit_caption").value;
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=attachment_edit&id_article=<?php echo $_GET["id_article"];?>&id_file="+attachment_edit_id+"&description="+encodeURIComponent(description)+"&filename="+encodeURIComponent(filename), false);
			if (response!="") show_status(response);
			else
			{
				hide("attachment_edit");
				view();
				hide("status");
			}
		}
		
		function attachment_move(direction, id_file)
		{
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=attachment_order&direction="+direction+"&id_file="+id_file+"&id_article=<?php echo $_GET["id_article"]; ?>", false);
			if (response!="") show_status(response);
			attachment_view();
		}

		function attachment_remove(id_file)
		{
			if (confirm('Wollen Sie den Anhang wirklich löschen?'))
			{
				var response=ajax("modules/backend_cms_article_editor_actions.php?action=attachment_remove&id_file="+id_file, false);
				if (response!="") show_status(response);
				attachment_view();
			}
		}
		
		function attachment_view()
		{
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=attachment_view&id_article=<?php echo $_GET["id_article"];?>", false);
			document.getElementById("attachment_view").innerHTML=response;
		}		
		
		function format()
		{
			var format=document.getElementById("article_format").value;
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=format&id_article=<?php echo $_GET["id_article"];?>&format="+format, false);
			if (response!="") show_status(response);
			view();
		}
		
		function image_add_fileDialogComplete(numFilesSelected, numFilesQueued)
		{
			try {
				if (numFilesQueued > 0)
				{
					show("image_upload_window");
					this.startUpload();
				}
			} catch (ex) {
				this.debug(ex);
			}
		}

		function image_add_uploadComplete(file)
		{
			try {
				/*  I want the next upload to continue automatically so I'll call startUpload here */
				if (this.getStats().files_queued > 0) {
					this.startUpload();
				} else {
					var progress = new FileProgress(file,  this.customSettings.upload_target);
					progress.setComplete();
					progress.setStatus("Alle Fotos hochgeladen.");
					progress.toggleCancel(false);
					hide("image_upload_window");
					image_view();
				}
			} catch (ex) {
				this.debug(ex);
			}
		}

		function image_add_uploadSuccess(file, serverData, response)
		{
			try
			{
//				image_view();
				if (serverData!="") show_status(serverData);
				else hide("status");
		
			} catch (ex) {
				this.debug(ex);
			}
		}

		function image_edit(id_file, filename, description)
		{
			$("#image_edit_id_file").val(id_file);
			$("#image_edit_filename").val(filename);
			$("#image_edit_description").val(description);
			$("#image_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { image_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Bildangaben bearbeiten",
				width:450
			});
		}

		function image_edit_save()
		{
			var id_file = $("#image_edit_id_file").val();
			var filename = $("#image_edit_filename").val();
			var description = $("#image_edit_description").val();
			wait_dialog_show();
			$.post("modules/backend_cms_article_editor_actions.php", { action:"image_edit", id_article:<?php echo $_GET["id_article"];?>, id_file:id_file, filename:filename, description:description },
					function(data)
					{
						if (data!="") show_status(data);
						else
						{
							show_status("Bildangaben erfolgreich aktualisiert.");
							$("#image_edit_dialog").dialog("close");
							image_view();
						}
						wait_dialog_hide();
					}
			);
			/*
			var filename=document.getElementById("image_edit_filename").value;
			var description=document.getElementById("image_edit_caption").value;
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=image_edit&id_article=<?php echo $_GET["id_article"];?>&id_file="+image_edit_id+"&description="+encodeURIComponent(description)+"&filename="+encodeURIComponent(filename), false);
			if (response!="") show_status(response);
			else
			{
				hide("image_edit");
				view();
				hide("status");
			}
			*/
		}

		function image_move(direction, id_file)
		{
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=image_order&direction="+direction+"&id_file="+id_file+"&id_article=<?php echo $_GET["id_article"]; ?>", false);
			if (response!="") show_status(response);
			image_view();
		}

		function image_profile_dialog()
		{
			$("#image_profile_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { image_profile_save(); } },
						{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
					],
				 	closeText:"Schließen",
					hide: { effect: 'drop', direction: "up" },
				 	modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
				 	title:"Bildformate einstellen",
					width:800,
				});
		}

		function image_remove(id_file)
		{
			if (confirm('Wollen Sie die Abbildung wirklich löschen?'))
			{
				var response=ajax("modules/backend_cms_article_editor_actions.php?action=image_remove&id_file="+id_file, false);
				if (response!="") show_status(response);
				else
				{
					show_status("Abbildung erfolgreich gelöscht.");
				}
				image_view();
			}
		}

		function image_view(skip_reload)
		{
			wait_dialog_show();
			$.post("modules/backend_cms_article_editor_actions.php", { action:"image_view", id_article:<?php echo $_GET["id_article"]; ?> },
				function(data)
				{
					$("#image_view").html(data);
					$(function() {
						$( "#images" ).sortable( { items:"li:not(.header)" } );
//						$( "#images" ).sortable({cancel: "#images_header"});
						$( "#images" ).disableSelection();
						$( "#images" ).bind( "sortupdate",
							function(event, ui)
							{
								var list = $('#images').sortable('toArray');
								$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ArticleImageSort", list:list},
									function(data)
									{
										show_status(data);
										image_view();
									}
								);
							}
						);
						wait_dialog_hide();
					});

					//upload button
					image_add_upload = new SWFUpload({
						// Backend Settings
						upload_url: "<?php echo PATH; ?>soa/",
						post_params: { "API":"cms", "Action":"ArticleImageUpload", "PHPSESSID":"<?php echo session_id(); ?>", "id_article":"<?php echo $_GET["id_article"]; ?>"},
			
						// File Upload Settings
						file_size_limit : "0",	// unlimited
						file_types : "*.jpg",
						file_types_description : "JPEG-Dateien",
						file_upload_limit : "0",
			
						// Event Handler Settings - these functions as defined in Handlers.js
						//  The handlers are not part of SWFUpload but are part of my website and control how
						//  my website reacts to the SWFUpload events.
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : image_add_fileDialogComplete,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : image_add_uploadSuccess,
						upload_complete_handler : image_add_uploadComplete,
			
						// Button Settings
						button_image_url : "images/icons/24x24/add_swfupload.png",
						button_placeholder_id : "image_add_button",
						button_width: 24,
						button_height: 24,
		//				button_text : '<span class="button">Neue Abbildung hinzufügen</span>',
		//				button_text_style : '.button { width:200px; background-color:#ff0000; padding:0px 10px 0 10px; text-align:center; font-family: Helvetica, Arial, sans-serif; font-size: 14pt; float:left; }',
		//				button_text_top_padding: 5,
			//            button_text_left_padding: 30,
						button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
						button_cursor: SWFUpload.CURSOR.HAND,
						
						// Flash Settings
						flash_url : "modules/flashupload/swfupload.swf",
			
						custom_settings : {
							upload_target : "image_add_uploadProgress"
						},
						
						// Debug Settings
						debug: false
					});
/*
					$(function()
					{
						$('.lazyimage').waypoint(
							function()
							{
								lazyload(this.alt, this);
								$(this).waypoint('destroy');
							},
							{
							   offset: '100%'
							}
						);
					});
*/
				}
			);
		}

		function imageprofile()
		{
			var imageprofile_id=document.getElementById("article_imageprofile_id").value;
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=imageprofile&id_article=<?php echo $_GET["id_article"];?>&imageprofile_id="+imageprofile_id, false);
			if (response!="") show_status(response);
			image_view();
//			view();
		}
		
		function insertAtCursor(myField, myValue)
		{
			var myField=document.getElementById(myField);
			//IE support
			if (document.selection)
			{
				myField.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
			}
			//MOZILLA/NETSCAPE support
			else if (myField.selectionStart || myField.selectionStart == '0')
			{
				var startPos = myField.selectionStart;
				var endPos = myField.selectionEnd;
				myField.value = myField.value.substring(0, startPos)
						   + myValue 
						   + myField.value.substring(endPos, myField.value.length);
			}
			else
			{
				myField.value += myValue;
			}
		}

		function label_add()
		{
			var id_label=document.getElementById("label_add_id").value;
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=label_add&id_article=<?php echo $_GET["id_article"];?>&id_label="+id_label, false);
			if (response!="") show_status(response);
			else
			{
				view();
				hide("status");
			}
		}
		
		function label_remove(id_label)
		{
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=label_remove&id_article=<?php echo $_GET["id_article"];?>&id_label="+id_label, false);
			if (response!="") show_status(response);
			else
			{
				view();
				hide("status");
			}
		}
		
		function language()
		{
			var language=document.getElementById("article_language").value;
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=language&id_article=<?php echo $_GET["id_article"];?>&language="+language, false);
			if (response!="") show_status(response);
//			view();
		}
		
		function original()
		{
			var original=document.getElementById("article_original").value;
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=original&id_article=<?php echo $_GET["id_article"];?>&original="+original, false);
			if (response!="") show_status(response);
//			view();
		}
		
		function published()
		{
			var published=document.getElementById("article_published").value;
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=published&id_article=<?php echo $_GET["id_article"];?>&published="+published, false);
			if (response!="") show_status(response);
//			view();
		}
		
		function save()
		{
			var title=$("#article_title").val();
			var introduction=$("#article_introduction").val();
			var text=$("#article_text").val();
			wait_dialog_show();
			var response=$.post("modules/backend_cms_article_editor_actions.php", { action:"save", id_article:'<?php  echo $_GET["id_article"]; ?>', title:title, introduction:introduction, text:text },
				function success(data)
				{
					view();
					if (data!="") show_status(data);
					wait_dialog_hide();
				}
			);
		}


		function shopitem_add()
		{
			var text=$("#shopitem_add_search").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ArticleShopitemAdd", article_id:<?php echo $_GET["id_article"];?>, text:text },
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
					show_status("Shopartikel erfolgreich hinzugefügt.");
					wait_dialog_hide();
				}
			);
		}
		
		function shopitem_remove(id_item)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ArticleShopitemRemove", id_item:id_item },
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
					show_status("Shopartikel erfolgreich entfernt.");
					wait_dialog_hide();
				}
			);
		}

		
		function GART_add()
		{
			var GART=$("#article_GART_add").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ArticleGARTAdd", article_id:<?php echo $_GET["id_article"];?>, GART:GART },
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
					show_status("Generische Artikelbezeichnuhng erfolgreich hinzugefügt.");
					wait_dialog_hide();
				}
			);
		}

		function GART_remove(GART_art_id)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ArticleGARTRemove", GART_art_id:GART_art_id },
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
					show_status("Generische Artikelbezeichnung erfolgreich entfernt.");
					wait_dialog_hide();
				}
			);
		}
		
		function video_add_fileDialogComplete(numFilesSelected, numFilesQueued)
		{
			try {
				if (numFilesQueued > 0)
				{
					show("video_upload_window");
					this.startUpload();
				}
			} catch (ex) {
				this.debug(ex);
			}
		}

		function video_add_uploadComplete(file)
		{
			try {
				/*  I want the next upload to continue automatically so I'll call startUpload here */
				if (this.getStats().files_queued > 0) {
					this.startUpload();
				} else {
					var progress = new FileProgress(file,  this.customSettings.upload_target);
					progress.setComplete();
					progress.setStatus("Alle Videos hochgeladen.");
					progress.toggleCancel(false);
					hide("video_upload_window");
				}
			} catch (ex) {
				this.debug(ex);
			}
		}

		function video_add_uploadSuccess(file, serverData, response)
		{
			try
			{
				video_view();
				hide("status");
		
			} catch (ex) {
				this.debug(ex);
			}
		}

		function video_edit_cancel()
		{
			hide("video_edit");
			hide("status");
			view();
		}
		
		function video_remove(id_file)
		{
			if (confirm('Wollen Sie das Video wirklich löschen?'))
			{
				var response=ajax("modules/backend_cms_article_editor_actions.php?action=video_remove&id_file="+id_file, false);
				if (response!="") show_status(response);
				video_view();
			}
		}
		
		function video_view()
		{
			var response=ajax("modules/backend_cms_article_editor_actions.php?action=video_view&id_article=<?php echo $_GET["id_article"];?>", false);
			document.getElementById("video_view").innerHTML=response;
		}		
		
		function view()
		{
			var response=ajax("modules/backend_cms_article_editor_actions.php?lang=<?php echo $_GET["lang"]; ?>&action=view&id_article=<?php echo $_GET["id_article"];?>", false);
			document.getElementById("view").innerHTML=response;
			image_view();
			attachment_view();
			video_view();
		}
		
	</script>

<?php
	//IMPORT joomfish data
/*
	$language=array();
	$ids=array();
	$title=array();
	$introtext=array();
	$fulltext=array();
	$results=q("SELECT * FROM jos_jf_content;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$ids[$row["reference_id"]]=$row["reference_id"];
		if ($row["reference_field"]=="introtext") $introtext[$row["reference_id"]][$row["language_id"]]=$row["value"];
		if ($row["reference_field"]=="title") $title[$row["reference_id"]][$row["language_id"]]=$row["value"];
		if ($row["reference_field"]=="fulltext")
		{
			$fulltext[$row["reference_id"]][$row["language_id"]]=$row["value"];
			$language[$row["reference_id"]][$row["language_id"]]=$row["language_id"];
			$modified[$row["reference_id"]][$row["language_id"]]=$row["modified"];
		}
	}
	print_r($ids);
	echo '<hr />';
	
	foreach($ids as $id)
	{
		for($j=0; $j<7; $j++)
		{
			if (isset($introtext[$id][$j]))
			{
				echo '<b>'.$title[$id][$j].' ('.$modified[$id][$j].')</b>';
				echo $introtext[$id][$j].$fulltext[$id][$j];
				echo '<br style="clear:both;" /><hr />';
//				q("INSERT INTO cms_articles (language_id, article_id, title, article, published, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(1, 0, '".addslashes(stripslashes($title[$id][$j]))."', '".addslashes(stripslashes($introtext[$id][$j].$fulltext[$id][$j]))."', 0, ".strtotime($modified[$id][$j]).", ".$_SESSION["id_user"].", ".strtotime($modified[$id][$j]).", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			}
		}
	}
*/
	//PATH
	$results=q("SELECT * FROM cms_articles_labels WHERE article_id=".$_GET["id_article"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)>0 )
	{
		$row=mysqli_fetch_array($results);
		$id_label=$row["label_id"];
		$results=q("SELECT * FROM cms_labels WHERE site_id IN(0, ".$_SESSION["id_site"].") AND id_label=".$id_label.";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$label=$row["label"];
	}
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">Backend</a>';
	echo ' > <a href="backend_cms_index.php?lang='.$_GET["lang"].'">Content Management</a>';
	echo ' > <a href="backend_cms_articles.php?lang='.$_GET["lang"].'">Beiträge</a>';
	if (isset($id_label))
	{
		echo ' > <a href="backend_cms_articles.php?lang='.$_GET["lang"].'&id_label='.$id_label.'">'.$label.'</a>';
	}
	echo ' > Editor';
	echo '</p>';
	

	//VIEW
	echo '<div id="view"></div>';

	//IMAGES
	echo '<div id="image_view"></div>';
/*
	echo '<table style="width:100%; margin-top:0;">';
	echo '<tr>';
	echo '	<td style="border-top:0;text-align:center;" colspan="4">';
	echo '		<div style="padding:1px 0px 2px 0px;" class="formbutton"><span id="image_add_button"></span></div>';
	echo '	</td>';
	echo '</tr>';
	echo '</table>';
*/

	//ATTACHMENTS
	echo '<div id="attachment_view"></div>';
	echo '<table style="width:100%; margin-top:0;">';
	echo '<tr>';
	echo '	<td style="border-top:0;text-align:center;" colspan="4">';
	echo '		<div style="padding:1px 0px 2px 0px;" class="formbutton"><span id="attachment_add_button"></span></div>';
	echo '	</td>';
	echo '</tr>';
	echo '</table>';

	//VIDEOS
	echo '<div id="video_view"></div>';
	echo '<table style="width:100%; margin-top:0;">';
	echo '<tr>';
	echo '	<td style="border-top:0;text-align:center;" colspan="4">';
	echo '		<div style="padding:1px 0px 2px 0px;" class="formbutton"><span id="video_add_button"></span></div>';
	echo '	</td>';
	echo '</tr>';
	echo '</table>';

	echo '<script> view(); </script>';

	//ATTACHMENT EDIT WINDOW
	echo '<div id="attachment_edit" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Anhang bearbeiten</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="attachment_edit_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Dateiname</td>';
	echo '		<td>';
	echo '			<input id="attachment_edit_filename" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Beschreibung</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="attachment_edit_caption"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="attachment_edit_save();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="attachment_edit_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//ATTACHMENT UPLOAD WINDOW
	echo '<div id="attachment_upload_window" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Anhänge hochladen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="attachment_upload_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Status</td>';
	echo '		<td>';
	echo '			<div id="attachment_add_uploadProgress"></div>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="attachment_upload_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//IMAGE EDIT DIALOG
	echo '<div id="image_edit_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Dateiname</td>';
	echo '		<td>';
	echo '			<input id="image_edit_filename" style="width:300px;" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Bildunterschrift</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="image_edit_description"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	</tr>';
	echo '</table>';
	echo '	<input type="hidden" id="image_edit_id_file" value="" />';
	echo '</div>';
	
	//IMAGE PROFILE DIALOG
	echo '<div id="image_profile_dialog" style="display:none;">';
	$imageformats=array();
	$imageformats[0]="Original";
	$results=q("SELECT * FROM cms_articles WHERE id_article=".$_GET["id_article"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$results=q("SELECT * FROM cms_imageformats WHERE imageprofile_id=".$row["imageprofile_id"].";", $dbweb, __FILE__, __LINE__);
	echo '	<table>';
	echo '		<tr>';
	echo '			<th>Format</th>';
	echo '			<th>Breite</th>';
	echo '			<th>Höhe</th>';
	echo '			<th>Vergrößern</th>';
	echo '			<th>Abschneiden</th>';
	echo '		</tr>';
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<tr>';
		echo '	<td>'.$row["title"].'</td>';
		echo '	<td><input id="image_profile_width" type="text" value="'.$row["width"].'" /></td>';
		echo '	<td><input id="image_profile_height" type="text" value="'.$row["height"].'" /></td>';
		echo '	<td><input id="image_profile_aoe" type="text" value="'.$row["aoe"].'" /></td>';
		echo '	<td><input id="image_profile_zc" type="text" value="'.$row["zc"].'" /></td>';
		echo '</tr>';
	}
	echo '	</table>';
	echo '</div>';
	
	//IMAGE UPLOAD WINDOW
	echo '<div id="image_upload_window" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Abbildungen hochladen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="image_upload_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Status</td>';
	echo '		<td>';
	echo '			<div id="image_add_uploadProgress"></div>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td style="text-align:center;" colspan="2">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="image_upload_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//VIDEO UPLOAD WINDOW
	echo '<div id="video_upload_window" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Videos hochladen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="video_upload_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Status</td>';
	echo '		<td>';
	echo '			<div id="video_add_uploadProgress"></div>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="video_upload_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//VIDEO EDIT WINDOW
	echo '<div id="video_edit" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Video bearbeiten</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="video_edit_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Dateiname</td>';
	echo '		<td>';
	echo '			<input id="video_edit_filename" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Beschreibung</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="video_edit_caption"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="video_edit_save();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="video_edit_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>