<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	var $images_upload_id_item;
	var $images_upload_id_list;
	var $images_upload_id_original;
	var $images_upload_id_article;
	var $images_upload_imageformats;
	var $images_upload_progressbar;
	var $images_upload_progressbar2;
	var $tempfile="";
	var $images_upload_cancel=false;
	var $files="";
	var $filecontent="";
	var id_account=0;
	var id_imageformat=0;
	var wait_dialog_timer;
	var item_submit_cancel=false;
	var list_load_cancel=false;
	var items=new Array;
	var auction_id=new Array();
	var auction_action=new Array();
	var auction_counter=0;
	var id_listtype=1;
	var id_list=-1;
	var id_menuitem=-1;
	var $timestamp=0;
	var $list=new Array();
	var $listheader=new Array();
	var $listdata=new Array();
	var $sort_by=undefined;
	var $sort_desc=0;
	
	function export_overview()
	{
		$("#export_dialog").dialog
		({	closeText:"Fenster schließen",
			height: 190,
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Exportart wählen",
			width:450
		});
	}


	function image_export(i)
	{
		if ( items.length>0 )
		{
			if ( $timestamp==0 ) $timestamp=new Date().getTime();
			$("#images_export_options_dialog").html("Packe Bilder zu Artikel "+items[i]+" ("+(i+1)+"/"+items.length+")");
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ImagesExport", id_item:items[i], zipname:"amazonuk_images_"+$timestamp+".zip" },
				function(data)
				{
					if ( items.length==(i+1) )
					{
						$("#images_export_options_dialog").dialog("close");
						show_status(data);
					}
					else image_export(i+1);
				}
			);
		}
	}

	function image_mass_download(i, exportfile, format)
	{
		if ( i >= images.length )
		{
			$("#image_mass_download_dialog").html(i+' Dateien erfolgreich exportiert.<br /><br /><a href="<?php echo PATH; ?>soa/'+exportfile+'" target="_blank">Download</a>');
			$('#image_mass_download_dialog').dialog('option', 'buttons', [{ text: "Schließen", click: function() { $(this).dialog("close"); } }] );
			return;
		}

		$("#image_mass_download_dialog").html("Packe Bild "+(i+1)+" von "+images.length);
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ImageMassDownload", id_file:images[i], filename:files[i], exportfile:exportfile, format:format },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}
				else image_mass_download(i+1, exportfile, format);
			}
		);
	}


	var images=new Array;
	var files=new Array;
	function image_mass_download_list()
	{
		var date=$("#image_mass_download_options_date").val();
		var hour=$("#image_mass_download_options_hour").val();
		var minute=$("#image_mass_download_options_minute").val();
		var format=$("#image_mass_download_options_format").val();
		$("#image_mass_download_options_dialog").dialog("close");
		
		$("#image_mass_download_dialog").html("Erstelle Liste mit Abbildungen...");
		$("#image_mass_download_dialog").dialog
		({	closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Foto-Massendownload",
			width:250
		});
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ImageMassDownloadList", date:date, hour:hour, minute:minute, format:format },
			function(data)
			{
				var j=0;
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				$exportfile = $xml.find("ExportFile");
				$exportfile=$exportfile.text();
				$xml.find("Image").each(
					function()
					{
						images[j]=$(this).text();
						files[j]=$(this).attr("filename");
						j++;
					}
				);
				wait_dialog_hide();
				if ( images.length==0 )
				{
					$("#image_mass_download_dialog").html("Im angegebenen Zeitraum wurden keine Abbildungen gefunden.");
					$('#image_mass_download_dialog').dialog('option', 'buttons', [{ text: "Schließen", click: function() { $(this).dialog("close"); } }] );
					return;
				}
				else image_mass_download(0, $exportfile, format);
			}
		);
	}


	function image_mass_download_options()
	{
		$("#image_mass_download_options_dialog").dialog
		({	buttons:
			[
				{ text: "Starten", click: function() { image_mass_download_list(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Foto-Massendownload",
			width:250
		});
	}


	function images_upload()
	{
    	if( $("#images_upload_dialog").length==0 )
        {
			$html  = '<div id="images_upload_dialog"></div>';
            $("body").append($html);
        }
		$html  = '';
		$html += '	<input type="file" id="images_upload_files" name="files[]" multiple />';
		$html += '	<div id="images_upload_dialog_status" style="width:100%; position:relative;">';
		$html += '		<div id="images_upload_dialog_status2" style="width:100%; position:absolute; top:4px; text-align:center;"></div>';
		$html += '	</div>';
		$html += '	<div id="images_upload_dialog_status3" style="width:100%; position:relative;">';
		$html += '		<div id="images_upload_dialog_status4" style="width:100%; position:absolute; top:4px; text-align:center;"></div>';
		$html += '	</div>';
		$("#images_upload_dialog").html($html);
		
		document.getElementById('images_upload_files').addEventListener('click', images_upload_filehandler_open, false);
		document.getElementById('images_upload_files').addEventListener('change', images_upload_filehandler_close, false);
		if (window.File && window.FileReader && window.FileList && window.Blob)
		{
			$("#images_upload_dialog_status2").html("Bitte wählen Sie die Bilder aus, die Sie hochladen möchten.");
		}
		else
		{
			$("#images_upload_dialog_status2").html("Die Datei-APIs werden in diesem Browser nicht unterstützt. Bitte benutzen Sie Chrome oder Firefox.");
		}
		$("#images_upload_dialog").dialog
		({	buttons:
			[
				{ text: "Schließen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Bilder hochladen",
			width:400
		});
	}


  	function images_upload_cancel()
	{
		$images_upload_cancel=true;
		$('#images_upload_dialog').dialog('option', 'buttons', {});
	}

  	function images_upload_cancel_completed()
	{
		$("#images_upload_dialog").html("Hochladen der Bilder erfolgreich abgebrochen.");
		$('#images_upload_dialog').dialog('option', 'buttons', [ { text: "Schließen", click: function() { $(this).dialog("close");} } ] );
	}

  	function images_upload_start()
	{
		if( typeof $files == "undefined" || $files.length==0 )
		{
			alert("Datei nicht gefunden.");
			return;
		}

		$('#images_upload_dialog').dialog('option', 'buttons', 
		[
			{ text: "Abbrechen", click: function() { images_upload_cancel(); } }
		] );

		//add an image upload list
		var today = new Date();
		var dd = today.getDate();
		var mm = today.getMonth()+1; //January is 0!
		var yyyy = today.getFullYear();
		var hour = today.getHours();
		var minute = today.getMinutes();
		if(dd<10) {dd='0'+dd}
		if(mm<10) {mm='0'+mm} today = dd+'.'+mm+'.'+yyyy+' '+hour+':'+minute;
		if(hour<10) {hour='0'+hour}
		if(minute<10) {minute='0'+minute}
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAdd", title:"Bilderupload "+today, id_listtype:2 }, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$images_upload_id_list=$xml.find("ListID").text();
			id_listtype=2;
			id_list=$images_upload_id_list;
			$.post("<?php echo PATH; ?>soa2/", { API:"shop", APIRequest:"ListFieldAdd", list_id:$images_upload_id_list, field_id:19 }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }

				view();

				//reset dialog
				$("#images_upload_files").hide();
				$images_upload_progressbar=$("#images_upload_dialog_status").progressbar({ value: false });
				$images_upload_progressbar2=$("#images_upload_dialog_status3").progressbar({ value: 0 });
				$("#images_upload_dialog_status4").html("0 von "+$files.length+" Dateien fertig.");

				//start upload of first file
				$images_upload_cancel=false;
				images_upload_file(0, 0);
			});
		});
	}


	function images_upload_file($filenr, $pos)
	{
		if( $images_upload_cancel == true )
		{
			images_upload_cancel_completed();
			return;
		}
	
		if( $pos>=$files[$filenr].size )
		{
			$MPN=$files[$filenr].name;
			$MPN=$MPN.toLowerCase();
			var $replacements=new Array(".jpg", "a", "b", "c", "d", "e", "f", "g");
			for($i=0; $i<$replacements.length; $i++)
			{
				$MPN=$MPN.replace($replacements[$i], "");
			}
			$MPN=$MPN.replace("_", "/");
			$.post('<?php echo PATH; ?>soa/', { API:"shop", Action:"ItemGet", MPN:$MPN }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }

				$images_upload_id_article = $xml.find("article_id").text();
				$images_upload_id_item = $xml.find("id_item").text();
				var $filename=$files[$filenr].name;
				$filename=$filename.toLowerCase();
				//add original image
				$("#images_upload_dialog_status2").html("Speichere Originalbild zum Artikel.");
				$.post('<?php echo PATH; ?>soa/', { API:"cms", Action:"ArticleImageAdd", id_article:$images_upload_id_article, filename:$filename, source:$tempfile }, function($data)
				{
					try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" ) { show_status2($data); return; }

					$images_upload_id_original=$xml.find("id_file").text();
					$images_upload_imageformats=new Array(8, 10, 9, 25, 19);
					images_upload_file_imageformats($filenr, 0);
					return;
				});
				
			});
			return;
		}

		//create tempfile is necessary
		if( $tempfile=="" )
		{
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"TempFileAdd", extension:"jpg" }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
				}
				catch (err)
				{
					show_status(err.message);
					return;
				}
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status($data);
					return;
				}
				
				$tempfile=$xml.find("Filename").text();
	
				$("#images_upload_dialog_status2").html("0% hochgeladen.");
				images_upload_file($filenr, 0);
			});
			return;
		}

		var $chunksize=32768;
		var $start=$pos;
		var $stop=$pos+$chunksize;
		if ($stop > $files[$filenr].size) $stop=$files[$filenr].size;
		var reader = new FileReader();
		reader.onloadend = function(evt)
		{
			if (evt.target.readyState == FileReader.DONE)
			{ // DONE == 2
				$Data=evt.target.result;
				$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"TempFileUpdate", Filename:$tempfile, Data:$Data }, function($data)
				{
					var $percent=Math.floor($stop / $files[$filenr].size * 100);
					$("#images_upload_dialog_status2").html('Datei '+($filenr+1)+' von '+$files.length+': '+ $percent+'% hochgeladen. ('+$stop+' / '+$files[$filenr].size+') <br /><br />'+$data);
					$images_upload_progressbar.progressbar("value", $percent);
					images_upload_file($filenr, $pos+$chunksize);
				});
			}
		};

		//start reading file
		var blob = $files[$filenr].slice($start, $stop) || file.mozSlice($start, $stop) || file.webkitSlice($start, $stop);
		reader.readAsDataURL(blob);
	}

  	function images_upload_filehandler_close(evt)
	{
		wait_dialog_show();
		$files = evt.target.files; // FileList object
		$("#images_upload_dialog_status2").html($files.length+" Dateien ausgewählt.");
		$('#images_upload_dialog').dialog('option', 'buttons', 
		[
			{ text: "Hochladen", click: function() { images_upload_start(); } },
			{ text: "Schließen", click: function() { $(this).dialog("close"); } }
		] );
		wait_dialog_hide();
	}


  	function images_upload_filehandler_open()
	{
		$("#images_upload_dialog_status2").html("Dateien werden eingelesen.");
	}


	function images_upload_file_imageformats($filenr, $pos)
	{
		if( $images_upload_cancel == true )
		{
			images_upload_cancel_completed();
			return;
		}

		if( $pos == $images_upload_imageformats.length )
		{
			$images_upload_progressbar.progressbar("value", 100);
			images_upload_file_completed($filenr);
			return;
		}
		
		//add imageformat images
		$percent=Math.floor($pos/$images_upload_imageformats.length*100);
		$images_upload_progressbar.progressbar("value", $percent);
		$("#images_upload_dialog_status2").html("Erstelle Bildformat "+$images_upload_imageformats[$pos]+" zum Artikel.");
		$.post('<?php echo PATH; ?>soa/', { API:"cms", Action:"ArticleImageImageformatAdd", id_article:$images_upload_id_article, id_file:$images_upload_id_original, id_imageformat:$images_upload_imageformats[$pos] }, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			images_upload_file_imageformats($filenr, $pos+1);
			return;
		});
	}

	function images_upload_file_completed($filenr)
	{
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAddItem", list_id:194, item_id:$images_upload_id_item }, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAddItem", list_id:$images_upload_id_list, item_id:$images_upload_id_item }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
	
				view();
				$filenr++;
				if( $filenr==$files.length )
				{
					$('#images_upload_dialog').dialog('option', 'buttons', 
					[
						{ text: "Schließen", click: function() { $(this).dialog("close"); } }
					] );
					$images_upload_progressbar2.progressbar("value", 100);
					$("#images_upload_dialog_status").hide();
					$("#images_upload_dialog_status4").html("Alle Dateien erfolgreich hochgeladen.");
					return;
				}
				else
				{
					$tempfile="";
					images_upload_file($filenr, 0);
					var $percent=Math.floor($filenr/$files.length*100);
					$images_upload_progressbar2.progressbar("value", $percent);
					$("#images_upload_dialog_status4").html($filenr+" von "+$files.length+" Dateien fertig.");
					return;
				}
			});
		});
	}




























	function image_mass_upload_fileDialogComplete(numFilesSelected, numFilesQueued)
	{
		if (numFilesQueued <= 0)
		{
			alert("Bitte wählen Sie mindestens eine Datei aus.");
		}
		
		current=0;
		
		//add list
		var today = new Date();
		var dd = today.getDate();
		var mm = today.getMonth()+1; //January is 0!
		var yyyy = today.getFullYear();
		var hour = today.getHours();
		var minute = today.getMinutes();
		
		if(dd<10) {dd='0'+dd}
		if(mm<10) {mm='0'+mm} today = dd+'.'+mm+'.'+yyyy+' '+hour+':'+minute;
		if(hour<10) {hour='0'+hour}
		if(minute<10) {minute='0'+minute}
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAdd", title:"Bilderupload "+today, id_listtype:2 }, function($data)
		{
			try
			{
				$xml = $($.parseXML($data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2($data);
					return;
				}
			}
			catch (err)
			{
				show_status2(err.message);
				return;
			}
			id_list=$xml.find("ListID").text();
			$.post("<?php echo PATH; ?>soa2/", { API:"shop", APIRequest:"ListFieldAdd", list_id:id_list, field_id:19 }, function($data)
			{
				view();
				
				try {
					if (numFilesQueued > 0)
					{
						total=numFilesQueued;
						$("#"+id).dialog('option', 'buttons', { } );
			
						//get target id
						var id="#"+image_mass_upload_obj.customSettings.upload_target;
			
						//create progressbar1
						var id_progressbar1=image_mass_upload_obj.customSettings.upload_target+"_progressbar1";
						$(id).append('<div id="'+id_progressbar1+'_wrapper" style="position:relative;" style="100%"></div>');
						$("#"+id_progressbar1+"_wrapper").append('<div id="'+id_progressbar1+'" style="width:100%;"></div>');
						$("#"+id_progressbar1+"_wrapper").append('<div id="'+id_progressbar1+'_status" style="width:100%; position:absolute; left:0; top:5px; text-align:center;"></div>');
						$(function() {
							$("#"+id_progressbar1).progressbar({
								value: 0
							});
						});
						$(id).append('<br style="clear:both;" />');
			
						//create progressbar2
						var id_progressbar2=image_mass_upload_obj.customSettings.upload_target+"_progressbar2";
						$(id).append('<div id="'+id_progressbar2+'_wrapper" style="position:relative;" style="100%"></div>');
						$("#"+id_progressbar2+"_wrapper").append('<div id="'+id_progressbar2+'" style="width:100%;"></div>');
						$("#"+id_progressbar2+"_wrapper").append('<div id="'+id_progressbar2+'_status" style="width:100%; position:absolute; left:0; top:5px; text-align:center;"></div>');
						$(function() {
							$("#"+id_progressbar2).progressbar({
								value: 0
							});
						});
			
						//start upload
						image_mass_upload_obj.startUpload();
					}
				}
				catch (ex) { image_mass_upload_obj.debug(ex); }
			});
		});
	}


	function image_mass_upload_initialize()
	{
		image_mass_upload_obj = new SWFUpload({
			// Backend Settings
			upload_url: "<?php echo PATH; ?>soa/",
			post_params: { API:"cms", Action:"ImageMassUpload", id_list:id_list },
	
			// File Upload Settings
			file_size_limit : "0", //unlimited
			file_types : "*.jpg",
			file_types_description : "JPEG-Dateien",
			file_upload_limit : "0",
	
			// Event Handler Settings - these functions as defined in Handlers.js
			//  The handlers are not part of SWFUpload but are part of my website and control how
			//  my website reacts to the SWFUpload events.
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : image_mass_upload_fileDialogComplete,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : image_mass_upload_uploadSuccess,
			upload_complete_handler : image_mass_upload_uploadComplete,
	
			// Button Settings
			button_image_url : "",
			button_placeholder_id : "image_mass_upload_button",
			button_width: 150,
			button_height: 25,
			button_text : 'Dateien auswählen...',
			button_text_style : '',
			button_text_top_padding: 2,
            button_text_left_padding: 2,
            button_text_right_padding: 2,
            button_text_bottom_padding: 2,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,
			
			// Flash Settings
			flash_url : "<?php echo PATH; ?>modules/flashupload/swfupload.swf",
	
			custom_settings : {
				upload_target : "image_mass_upload_dialog"
			},
			
			// Debug Settings
			debug: false
		});
	}


	function image_mass_upload_uploadComplete(file)
	{
		try {
			/*  I want the next upload to continue automatically so I'll call startUpload here */
			if (this.getStats().files_queued > 0)
			{
				this.startUpload();
			}
			else
			{
				var id_progressbar1="#"+this.customSettings.upload_target+"_progressbar1";
				$(id_progressbar1).progressbar("option", "value", 100);
				var id=this.customSettings.upload_target;
				$("#"+id+"_progressbar1_status").html("Alle Dateien erfolgreich hochgeladen.");
				$("#"+id).dialog('option', 'buttons', [ { text: "OK", click: function() { $(this).dialog("close"); } } ] );
				
			}
		} catch (ex) {
			this.debug(ex);
		}
	}


	function image_mass_upload_uploadSuccess(file, data)
	{
		try
		{
			$xml = $($.parseXML(data));
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" )
			{
				show_status2(data);
				return;
			}
			var $id_item = $xml.find("ItemID").text();
			current++;
			
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAddItem", list_id:id_list, item_id:$id_item }, function($data)
			{
				view();
			});
		}
		catch (ex)
		{
			
			show_status2(this.debug(ex));
			return;
		}
	}


	function images_export()
	{
		$("#export_dialog").dialog("close");
		if ( typeof document.itemform === "undefined" )
		{
			alert("Bitte wählen Sie zunächst eine Liste oder Artikelgruppe aus.");
			return;
		}

		var theForm = document.itemform;
		var j=0;
		items = new Array();
		for (i=0; i<theForm.elements.length; i++)
		{
			if (theForm.elements[i].checked)
			{
				if (theForm.elements[i].name=='item_id[]')
				{
					items[j]=theForm.elements[i].value;
					j++;
				}
			}
		}
		
		if ( items.length==0 )
		{
			alert("Bitte wählen Sie die Referenzen aus, die Sie exportieren möchten.");
			return;
		}
		
		$timestamp=0;

		$("#images_export_options_dialog").dialog
		({	buttons:
			[
				{ text: "Exportieren", click: function() { image_export(0); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			height: 200,
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bilder exportieren",
			width:400
		});
	}


	function is_numeric(n)
	{
	  return !isNaN(parseFloat(n)) && isFinite(n);
	}
