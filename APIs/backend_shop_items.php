<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script type="text/javascript" src="<?php echo PATH; ?>modules/flashupload/swfupload.js"></script>
<script type="text/javascript" src="<?php echo PATH; ?>modules/flashupload/js/handlers2.js"></script>
<script language="javascript">
	var id_account=0;
	var id_imageformat=0;
	var wait_dialog_timer;
	var item_submit_cancel=false;
	var items=new Array;
	var auction_id=new Array();
	var auction_action=new Array();
	var auction_counter=0;
	var id_list=-1;
	var id_menuitem=-1;

	$(function()
	{
		$( "#image_mass_download_options_date" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
	});

	function list_add_old_initialize()
	{
		if ( typeof $("#list_add_old_button").val() === "undefined" ) return;
		list_add_old_upload_obj = new SWFUpload({
			// Backend Settings
			upload_url: "<?php echo PATH; ?>soa/",
			post_params: { API:"shop", Action:"ListUpload" },
	
			// File Upload Settings
			file_size_limit : "0", //unlimited
			file_types : "*.csv",
			file_types_description : "CSV-Dateien",
			file_upload_limit : "1",
	
			// Event Handler Settings - these functions as defined in Handlers.js
			//  The handlers are not part of SWFUpload but are part of my website and control how
			//  my website reacts to the SWFUpload events.
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : list_add_old_fileDialogComplete,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : list_add_old_uploadSuccess,
			upload_complete_handler : list_add_old_uploadComplete,
	
			// Button Settings
			button_image_url : "<?php echo PATH; ?>images/icons/24x24/add_swfupload.png",
			button_placeholder_id : "list_add_old_button",
			button_width: 24,
			button_height: 24,
			button_text : '',
			button_text_style : '',
			button_text_top_padding: 0,
            button_text_left_padding: 0,
            button_text_right_padding: 0,
            button_text_bottom_padding: 0,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,
			
			// Flash Settings
			flash_url : "<?php echo PATH; ?>modules/flashupload/swfupload.swf",
	
			custom_settings : {
				upload_target : "list_add_old_uploadProgress"
			},
			
			// Debug Settings
			debug: false
		});
	}


	function image_mass_upload_initialize()
	{
		image_mass_upload_obj = new SWFUpload({
			// Backend Settings
			upload_url: "<?php echo PATH; ?>soa/",
			post_params: { API:"cms", Action:"ImageMassUpload" },
	
			// File Upload Settings
			file_size_limit : "0", //unlimited
			file_types : "*.jpg",
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


	function checkAll()
	{
		var state = document.getElementById("selectall").checked;
		var theForm = document.itemform;
		for (i=0; i<theForm.elements.length; i++)
		{
			if (theForm.elements[i].name=='item_id[]')
				theForm.elements[i].checked = state;
		}
	}
	
	
	function ebay_auctions(id_account, id_item)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemGetAuctions", id_account:id_account, id_item:id_item },
			   function(data)
			   {
					$("#ebay_auctions_dialog").html(data);
					$("#ebay_auctions_dialog").dialog
					({	buttons:
						[
							{ text: "OK", click: function() { $(this).dialog("close"); } }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Auktionen zum Artikel",
						width:600
					});
				wait_dialog_hide();
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


	function image_mass_upload()
	{
		$("#image_mass_upload_dialog").html('<span class="formbutton"><span id="image_mass_upload_button" title="Foto-Massenupload"></span></span>');
		image_mass_upload_initialize();
		$("#image_mass_upload_dialog").dialog
		({	buttons:
			[
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			height: 220,
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Foto-Massenupload",
			width:600
		});
	}


	function items_export()
	{
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

		$("#items_export_options_dialog").dialog
		({	buttons:
			[
				{ text: "Exportieren", click: function() { items_export_start(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			height: 200,
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Artikeldaten exportieren",
			width:400
		});
	}

	function items_export_start()
	{
		var id_exportformat=$("#items_export_id_exportformat").val();
		var now = new Date();
		$("#items_export_options_dialog").dialog("close");
		$("#items_export_dialog").dialog
		({	closeText:"Fenster schließen",
			height: 200,
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Artikeldaten exportieren",
			width:400
		});
		item_export(0, "export_"+now.getTime()+".csv", 1, id_exportformat);
	}
	
	
	function item_export(i, file, header, id_exportformat)
	{
		if (i<items.length)
		{
			$("#items_export_dialog").html("Exportiere Artikel "+(i+1)+" von "+items.length);
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemExport", id_exportformat:id_exportformat, id_item:items[i], file:file, header:header },
				function(data)
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
					}
					catch (ex)
					{
						show_status2(data);
						return;
						this.debug(ex);
					}

					item_export(i+1, file, 0, id_exportformat);
				}
			);
		}
		else
		{
			$("#items_export_dialog").html('Alle Artikel erfolgreich exportiert.<br /><br /><a href="<?php echo PATH; ?>soa/'+file+'" target="_blank">Download</a>');
		}
	}


	function item_create(i)
	{
		if ( i==items.length )
		{
			$('#items_submit_dialog').dialog('option', 'buttons', {});
			$("#items_submit_dialog").html("Alle Shopartikel erfolgreich übertragen.");
			return;
		}
		
		var id_account=$("#items_submit_id_account").val();
		var id_pricelist=$("#items_submit_id_pricelist").val();
		var bestoffer = $("#items_submit_bestoffer:checked").val()
		if ( bestoffer=="on" ) bestoffer=1; else bestoffer=0;
		var ShippingServiceCost=$("#items_submit_ShippingServiceCost").val();
		var id_article=$("#items_submit_id_article").val();
		var comment=$("#items_submit_comment").val();

		$("#items_submit_dialog").html("Shopartikel "+(i+1)+" von "+items.length+"<br /><br />Erstelle Auktionen...");
		var response = $.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCreateAuctions", id_account:id_account, id_item:items[i], pricelist_id:id_pricelist, bestoffer:bestoffer, ShippingServiceCost:ShippingServiceCost, comment:comment, id_article:id_article, id_imageformat:id_imageformat },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				var j=0;
				auction_id=new Array();
				auction_action=new Array();
				$xml.find("AuctionID").each(
					function()
					{
						auction_id[j]=$(this).text();
						auction_action[j]=$(this).attr("action");
						j++;
					}
				);
				auction_counter=0;
				item_submit(i);
			}
		);
//			response.error(function() { alert("error"); })
	}

	function item_remove(id_list, id)
	{
		if ( confirm("Wollen Sie diesen Artikel wirklich löschen?") )
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListItemRemove", id_list:id_list, id:id },
				function(data)
				{
					$xml = $($.parseXML(data));
					var status=$xml.find("Ack").text();
					wait_dialog_hide();
					if ( status=="Success" )
					{
						show_status("Der Artikel wurde erfolgreich gelöscht.");
						view();
					}
					else
					{
						show_status2(data);
					}
				}
			);
		}
	}

	function item_submit(item_counter)
	{
		if (auction_counter==auction_id.length)
		{
			item_create(item_counter+1);
			return;
		}
		if (auction_action[auction_counter]=="AddItem") actiontext="Erstelle Auktion";
		else if (auction_action[auction_counter]=="ReviseItem") actiontext="Aktualisiere Auktion";
		else if (auction_action[auction_counter]=="EndItem") actiontext="Beende Auktion";
		var status="Shopartikel "+(item_counter+1)+" von "+items.length;
		status+="<br /><br />Aktion "+(auction_counter+1)+" von "+auction_id.length+": "+actiontext+" "+auction_id[auction_counter];
		$("#items_submit_dialog").html(status);
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:auction_action[auction_counter], id_auction:auction_id[auction_counter] },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					item_create(item_counter);
					return;
				}
				auction_counter++;
				item_submit(item_counter);
			}
		);
	}


	
	function items_submit_options()
	{
		items=new Array();
		var theForm = document.itemform;
		var j=0;
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
		
		if (items.length==0)
		{
			alert("Es muss mindestens ein Shopartikel ausgewählt worden sein.");
			return;
		}

		item_submit_cancel=false;
		$("#items_submit_options_dialog").dialog
		({	buttons:
			[
				{ text: "Übertragung starten", click: function() { items_submit(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Übertragungsoptionen",
			width:450
		});
	}

	function items_submit()
	{
		$("#items_submit_options_dialog").dialog("close");
		$("#items_submit_dialog").dialog
		({	buttons:
			[
				{	text: "Abbrechen",
					click: function()
					{
						item_submit_cancel=true;
						$("#items_submit_dialog").html("Übertragung wird abgebrochen...");
						$('#items_submit_dialog').dialog('option', 'buttons', {});
					}
				}
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Shopartikel nach eBay übertragen",
			width:400
		});
		
		item_create(0);
	}


	function list_add()
	{
		$("#list_add_dialog").dialog
		({	buttons:
			[
				{ text: "Liste erstellen", click: function() { list_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neue Liste hinzufügen",
			width:400
		});
	}
	
	
	function list_add_save()
	{
		var title=$("#list_add_title").val();
		if ( title=="" )
		{
			show_status("Der Titel darf nicht leer sein.");
			return;
		}
		$.post("<?php echo PATH; ?>soa/", { API:"shop", "ListAdd", title:title },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}
				
				show_status("Liste erfolgreich angelegt.");
				$("#list_add_dialog").dialog("close");
			}
		);
	}

	
	function list_add_old()
	{
		$("#list_add_old_dialog").dialog
		({	closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neue Liste hinzufügen",
			width:400
		});
	}

	
	function list_add_old_fileDialogComplete(numFilesSelected, numFilesQueued)
	{
		try {
			if (numFilesQueued > 0)
			{
				$("#list_add_old_dialog").dialog
				({	closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Neue Liste hinzufügen",
					width:450
				});
				this.startUpload();
			}
		}
		catch (ex)
		{
			this.debug(ex);
		}
	}
	
	
	function list_add_old_import($ItemID)
	{
		var title = $("#list_add_old_title").val();
		if (title=="")
		{
			show_status("Der Titel der Liste darf nicht leer sein.");
			return;
		}

		$("#list_add_old_dialog").dialog("option", "buttons", { } );
		$("#list_add_old_dialog").html("Erstelle Liste...");	
		
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAdd", title:title },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status(data);
					return;
				}

				$id_list = $xml.find("ListID").text();
				list_add_old_import_line($id_list, $ItemID, 0);
			}
		);
	}


	function list_add_old_import_line($list_id, $ItemID, $i)
	{
		if ($i==$ItemID.length)
		{
			$("#list_add_old_dialog").html("Liste erfolgreich importiert.");
			$("#list_add_old_dialog").dialog("option", "buttons", { "Schließen": function() { view($list_id); $(this).dialog("close"); } } );
			return;
		}
		
		$("#list_add_old_dialog").html("Importiere Zeile "+($i+1)+" von "+$ItemID.length+".");

		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAddItem", list_id:$list_id, item_id:$ItemID[$i] },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status(data);
					return;
				}

				$i++;
				list_add_old_import_line($list_id, $ItemID, $i);
			}
		);
	}


	function list_add_old_uploadComplete(file)
	{
		try {
			//start next file upload automatically
			if (this.getStats().files_queued > 0)
			{
				this.startUpload();
			}
			else
			{
				/*
				var progress = new FileProgress(file,  this.customSettings.upload_target);
				progress.setComplete();
				progress.setStatus("Datei erfolgreich hochgeladen.");
				progress.toggleCancel(false);
				*/
			}
		} catch (ex)
		{
			this.debug(ex);
		}
	}


	function list_add_old_uploadSuccess(file, data, response)
	{
		try
		{
			$xml = $($.parseXML(data));
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" )
			{
				show_status(data);
				return;
			}
			
		    $filename = $xml.find( "Filename" );
			$i=0;
			var $ItemID = new Array();
		    $xml.find("ItemID").each(
				function()
				{
					$ItemID[$i]=$(this).text();
					$i++;
				}
			);
			$("#list_add_old_uploadProgress").html($filename.text()+"<br />"+$ItemID.length+" Einträge gefunden.");
			$("#list_add_old_dialog").dialog("option", "buttons", { "Importieren": function() { list_add_old_import($ItemID); }, "Abbrechen": function() { $(this).dialog("close"); } } );
//			attachment_view();
//			hide("status");
	
		} catch (ex) {
			this.debug(ex);
		}
	}
	
	
	function list_edit(id_list, title)
	{
		$("#list_edit_id_list").val(id_list);
		$("#list_edit_title").val(title);
		$("#list_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { list_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Liste bearbeiten",
			width:400
		});
	}


	function list_edit_save()
	{
		var id_list=$("#list_edit_id_list").val();
		var title=$("#list_edit_title").val();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListEdit", id_list:id_list, title:title },
			function(data)
			{
				$xml = $($.parseXML(data));
				var status=$xml.find("Ack").text();
				if ( status=="Success" )
				{
					show_status("Die Liste wurde erfolgreich aktualisiert.");
					view();
					$("#list_edit_dialog").dialog("close");
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}


	function list_remove(id_list)
	{
		if ( confirm("Wollen Sie die Liste wirklich löschen?") )
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListRemove", id_list:id_list },
				function(data)
				{
					$xml = $($.parseXML(data));
					var status=$xml.find("Ack").text();
					wait_dialog_hide();
					if ( status=="Success" )
					{
						show_status("Die Liste wurde erfolgreich gelöscht.");
						view();
					}
					else
					{
						show_status2(data);
					}
				}
			);
		}
	}

	
	function pdf_export()
	{
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
		
		$("#pdf_export_dialog").dialog
		({	buttons:
			[
				{ text: "PDF erstellen", click: function() { pdf_export2(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"PDF exportieren",
			width:350
		});
	}
	
	function pdf_export2()
	{
		var title1=$("#pdf_export_title1").val();
		var title2=$("#pdf_export_title2").val();
		var id_location=$("#pdf_export_id_location").val();
		var lang=$("#pdf_export_lang").val();
//		wait_dialog_show();
		post_to_url("<?php echo PATH; ?>backend_shop_items_pdfexport.php", { items:items, title1:title1, title2:title2, id_location:id_location, lang:lang }, "post");
		/*
		$.post("<?php //echo PATH; ?>soa/", { API:"shop", Action:"PdfExport", items:items, title1:title1, title2:title2, id_location:id_location, lang:lang },
			function(data)
			{
				show_status(data);
				wait_dialog_hide();
				$("#pdf_export_dialog").dialog("close");
			}
		);
		*/
	}


	function post_to_url(path, params, method)
	{
		method = method || "post"; // Set method to post by default, if not specified.
	
		// The rest of this code assumes you are not using a library.
		// It can be made less wordy if you use one.
		var form = document.createElement("form");
		form.setAttribute("method", method);
		form.setAttribute("action", path);
		form.setAttribute("target", "_blank");
	
		for(var key in params) {
			if(params.hasOwnProperty(key))
			{
				if (params[key] instanceof Array)
				{
					var param="";
					for(i=0; i<params[key].length; i++)
					{
						param+=params[key][i];
						if ((i+1)<params[key].length) param+=",";
					}
					params[key]=param;
				}
				var hiddenField = document.createElement("input");
				hiddenField.setAttribute("type", "hidden");
				hiddenField.setAttribute("name", key);
				hiddenField.setAttribute("value", params[key]);
	
				form.appendChild(hiddenField);
			 }
		}
	
		document.body.appendChild(form);
		form.submit();
		$("#pdf_export_dialog").dialog("close");
	}


	function artnr_jump(e)
	{
		$("#artnr_jump_dialog").dialog
		({	buttons:
			[
				{ text: "Zum Artikel springen", click: function() { artnr_jump_accept(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Sprung zum Artikel",
			width:300
		});
	}


	function artnr_jump_accept()
	{
		var code = 0;
		code = event.keyCode;
		if (code!=13 && code!=0) return;

		var artnr=$("#artnr_jump_artnr").val();
		$("#price_research_remove_dialog").dialog("close");
		$.post('<?php echo PATH; ?>modules/backend_shop_items_actions.php', { action:"artnr2id", artnr:artnr },
			function(data)
			{
				if ( !$.isNumeric(data) )
				{
					alert(data);
					return;
				}
				window.location="backend_shop_item_editor.php?id_item="+data;
			}
		);
	}


	function text_search(text)
	{
		setTimeout("view('"+text+"')", 250);
	}


	function price_research(id_item, title)
	{
		wait_dialog_show();
		$.post("modules/backend_shop_items_actions.php", { action:"price_research_view", id_item:id_item },
			function(data)
			{
				$("#price_research_dialog").html(data);
				$("#price_research_dialog").dialog
				({	closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Preisrecherche für "+title,
					width:730
				});
				wait_dialog_hide();
			}
		);
	}

	
	function price_research_add()
	{
		var id_item=$("#price_research_add_id_item").val();
		var price=$("#price_research_add_price").val();
		var shipping=$("#price_research_add_shipping").val();
		var seller=$("#price_research_add_seller").val();
		var EbayID=$("#price_research_add_EbayID").val();
		$.post("modules/backend_shop_items_actions.php", { action:"price_research_add", id_item:id_item, price:price, shipping:shipping, seller:seller, EbayID:EbayID },
			function(data)
			{
				if (data!="") show_status(data);
				else
				{
					price_research_view(id_item);
				}
			}
		);
	}


	function price_research_stats()
	{
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceResearchStats" },
			function(data)
			{
				$("#price_research_stats_dialog").html(data);
				$("#price_research_stats_dialog").dialog
				({	closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Statistiken Preisrecherche",
					width:400
				});
			}
		);
	}


	function price_suggestion_add()
	{
		var id_item=$("#price_research_add_id_item").val();
		var price=$("#price_research_suggest_price").val();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceSuggestionAdd", id_item:id_item, price:price },
			function(data)
			{
				if ( data.indexOf("Success")<0 ) show_status(data);
				else
				{
					show_status("Preis erfolgreich vorgeschlagen.");
					price_research_view(id_item);
				}
			}
		);
	}


	function price_research_ebay()
	{
		var ItemID=$("#price_research_add_EbayID").val();
		if ( !(ItemID>0) ) alert("Die Auktionsnummer ist ungültig.");
		else
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetItem", id_account:1, ItemID:ItemID },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						wait_dialog_hide();
						return;
					}

					var price=$xml.find("ConvertedStartPrice").text();
					var shipping=0;
					$xml.find("ShippingServiceOptions ShippingServiceCost").each(
						function()
						{
							if (shipping>$(this).text()) shipping=$(this).text();
						}
					);
					var seller=$xml.find("UserID").text();
					$("#price_research_add_price").val(price);
					$("#price_research_add_shipping").val(shipping);
					$("#price_research_add_seller").val(seller);
					wait_dialog_hide();
				}
			);
		}
	}

	function price_research_remove(id)
	{
		$("#price_research_remove_dialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { price_research_remove_accept(id); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Preisrecherche",
			width:700
		});
	}


	function price_research_remove_accept(id)
	{
		var id_item=$("#price_research_add_id_item").val();
		$.post("modules/backend_shop_items_actions.php", { action:"price_research_remove", id:id },
			function(data)
			{
				if (data!="") show_status(data);
				else
				{
					price_research_view(id_item);
					$("#price_research_remove_dialog").dialog("close");
				}
			}
		);
	}


	function price_research_view(id_item)
	{
		$.post("modules/backend_shop_items_actions.php", { action:"price_research_view", id_item:id_item },
			function(data)
			{
				$("#price_research_dialog").html(data);
			}
		);
	}


	function show_lists()
	{
		$("#itemgroups").hide();
		$("#tab_itemgroups").css("font-weight", "normal");
		$("#lists").show();
		$("#tab_lists").css("font-weight", "bold");
	}


	function show_itemgroups()
	{
		$("#lists").hide();
		$("#tab_lists").css("font-weight", "normal");
		$("#itemgroups").show();
		$("#tab_itemgroups").css("font-weight", "bold");
	}


	function view()
	{
		var filter=$("#filter").val();
		if ( typeof filter == "undefined" ) filter=4;
		var fotostatus=$("#fotostatus").val();
		if ( typeof fotostatus == "undefined" ) fotostatus=0;
		var pricestatus=$("#pricestatus").val();
		if ( typeof pricestatus == "undefined" ) pricestatus=0;
		var pricesuggestion=$("#pricesuggestion").val();
		if ( typeof pricesuggestion == "undefined" ) pricesuggestion=0;
		var needle=$("#needle").val();
		if ( typeof needle == "undefined" ) needle="";
		wait_dialog_show();
		
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemsView", lang:'<?php echo $_SESSION["lang"]; ?>', id_list:id_list, id_menuitem:id_menuitem, filter:filter, fotostatus:fotostatus, needle:needle, needle:needle, pricestatus:pricestatus, pricesuggestion:pricesuggestion },
			function(data)
			{
				$("#view").html(data);
				list_add_old_initialize();
				wait_dialog_hide();
			}
		);
	}
</script>

<?php
	//PATH
	echo '<p>'."\n";
	echo '<a href="backend_index.php">Backend</a>'."\n";
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>'."\n";
	echo ' > Artikel'."\n";
	echo '</p>'."\n";

	//HEADLINE
	echo '<h1>Shopartikel'."\n";
	if( $_SESSION["userrole_id"]==1 or $_SESSION["userrole_id"]==3 or $_SESSION["userrole_id"]==4 )
	{
		//price research stats
		echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/chart.png" alt="Statistiken Preisrecherche" title="Statistiken Preisrecherche" onclick="price_research_stats();" />'."\n";
	}
	if( $_SESSION["userrole_id"]==1 or $_SESSION["userrole_id"]==3 )
	{
		//image mass upload
		echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/image_up.png" alt="Foto-Massenupload" title="Foto-Massenupload" onclick="image_mass_upload();" />'."\n";
		//image mass download
		echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/image_down.png" alt="Foto-Massenupload" title="Foto-Massendownload" onclick="image_mass_download_options();" />'."\n";
		//item jump
		echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/next.png" alt="Zu Shopartikel springen" title="Zu Shopartikel springen" onclick="artnr_jump();" />'."\n";
	}
	echo '</h1>'."\n";


	//REMOVE ITEM
	if (isset($_POST["item_remove"]))
    {
		if ($_POST["id_item"]<=0) echo '<div class="failure">Es konnte keine ID für den Artikel gefunden werden!</div>';
		else
		{
			q("DELETE FROM shop_items WHERE id_item=".$_POST["id_item"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Artikel erfolgreich gelöscht!</div>';
		}
	}


	//LIST ADD
	if ( isset($_FILES["list_add_old_file"]["tmp_name"]) )
	{
		if ( !isset($_POST["list_add_old_title"]) )
		{
			echo '<div class="failure">Die Liste muss einen Titel haben.</div>';
		}
		else
		{
			q("INSERT INTO shop_lists (title, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$_POST["list_add_old_title"]."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
			$id_list=mysqli_insert_id($dbshop);
			$i=0;
			$handle=fopen($_FILES["list_add_old_file"]["tmp_name"], "r");
			$line=fgetcsv($handle, 4096, ";");
			while( $line=fgetcsv($handle, 4096, ";") )
			{
				$i++;
				$results=q("SELECT * FROM shop_items WHERE MPN='".$line[0]."';", $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows($results)>0 )
				{
					$row=mysqli_fetch_array($results);
					$id_item=$row["id_item"];
					q("INSERT INTO shop_lists_items (list_id, item_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$id_list.", ".$id_item.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
				}
			}
		}
	}


	//ARTNR JUMP DIALOG
	echo '<div id="artnr_jump_dialog" style="display:none;">';
	echo '	<input id="artnr_jump_artnr" type="text" value="" onkeypress="artnr_jump_accept();" />';
	echo '</div>';


	//EBAY AUCTIONS DIALOG
	echo '<div style="display:none;" id="ebay_auctions_dialog">';
	echo '</div>';


	//IMAGE MASS DOWNLOAD OPTIONS
	echo '<div id="image_mass_download_options_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Uhrzeit</td>';
	echo '		<td>';
	echo '			<input id="image_mass_download_options_hour" style="width:20px;" type="text" value="0" /> : ';
	echo '			<input id="image_mass_download_options_minute" style="width:20px;" type="text" value="0" /> Uhr';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datum</td>';
	echo '		<td>';
	echo '			<input id="image_mass_download_options_date" type="text" value="'.date("d.m.Y", time()).'" /> ';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Format</td>';
	echo '		<td>';
	echo '			<select id="image_mass_download_options_format">';
	echo '				<option value="0">JPG</option>';
	echo '				<option value="1">TecDoc</option>';
	echo '				<option value="2">IDIMS</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';


	//IMAGE MASS DOWNLOAD
	echo '<div id="image_mass_download_dialog" style="display:none;">';
	echo '</div>';

	
	//IMAGE MASS UPLOAD DIALOG
	echo '<div style="display:none;" id="image_mass_upload_dialog">aaa111';
	echo '</div>';


	//ITEMS EXPORT DIALOG
	echo '<div id="items_export_options_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Format</td>';
	echo '			<td>';
	echo '				<select id="items_export_id_exportformat">';
	$results=q("SELECT * FROM shop_export_formats ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_exportformat"].'">'.$row["title"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';
	
	
	//ITEMS EXPORT STATUS DIALOG
	echo '<div id="items_export_dialog" style="display:none;">';
	echo '</div>';
	
	
	//ITEMS SUBMIT OPTIONS DIALOG
	echo '<div id="items_submit_options_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Design</td>';
	echo '		<td>';
	echo '			<select id="items_submit_id_account">';
	$results=q("SELECT * FROM ebay_accounts ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while ( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_account"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Design</td>';
	echo '		<td>';
	echo '			<select id="items_submit_id_article">';
	$results=q("SELECT * FROM cms_articles AS a, cms_articles_labels AS b WHERE b.label_id=8 AND a.id_article=b.article_id AND a.id_article<246;", $dbweb, __FILE__, __LINE__);
	while ( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_article"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Preisvorschlag</td>';
	echo '		<td>';
	echo '				<input type="checkbox" id="items_submit_bestoffer" /> Preisvorschlag aktivieren';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Versandkosten</td>';
	echo '		<td>';
	echo '				<input style="width:50px;" type="input" id="items_submit_ShippingServiceCost" value="5.90" /> €';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Kommentar</td>';
	echo '		<td>';
	echo '			<textarea id="items_submit_comment" style="width:300px; height:50px;" ></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';


	//ITEMS SUBMIT DIALOG
	echo '<div style="display:none;" id="items_submit_dialog">';
	echo '</div>';


	//LIST ADD
	echo '<div id="list_add_dialog" style="display:none;">';
	echo '	<form method="post" enctype="multipart/form-data">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel der Liste</td>';
	echo '			<td><input id="list_add_title" type="text" value="" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	</form>';
	echo '</div>';


	//LIST ADD OLD
	echo '<div id="list_add_old_dialog" style="display:none;">';
	echo '	<form method="post" enctype="multipart/form-data">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td style="width:100px;">Datei</td>';
	echo '			<td><div id="list_add_old_uploadProgress"></div></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Titel der Liste</td>';
	echo '			<td><input id="list_add_old_title" type="text" value="" /></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	</form>';
	echo '</div>';


	//LIST EDIT DIALOG
	echo '<div style="display:none;" id="list_edit_dialog">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel</td>';
	echo '			<td><input id="list_edit_title" style="width:300px;" type="text"></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input id="list_edit_id_list" type="hidden">';
	echo '</div>';


	//PDF EXPORT DIALOG
	echo '<div id="pdf_export_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Titel groß</td>';
	echo '			<td><input id="pdf_export_title1" style="width:50px;" type="text" value="z.B. NEU" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Titel klein</td>';
	echo '			<td><input id="pdf_export_title2" style="width:200px;" type="text" value="z.B. Generatorfreiläufe" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Kontaktdaten</td>';
	echo '			<td>';
	echo '				<select id="pdf_export_id_location">';
	$results=q("SELECT * FROM cms_contacts_locations ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_location"].'">'.$row["location"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		<tr>';
	echo '			<td>Sprache</td>';
	echo '			<td>';
	echo '				<select id="pdf_export_lang">';
	$results=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["code"].'">'.$row["language"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';


	//PRICE RESEARCH DIALOG
	echo '<div id="price_research_dialog" style="display:none;">';
	echo '</div>';


	//PRICE RESEARCH STATS DIALOG
	echo '<div id="price_research_stats_dialog" style="display:none;">';
	echo '</div>';


	//PRICE RESEARCH REMOVE DIALOG
	echo '<div id="price_research_remove_dialog" style="display:none;">';
	echo '	Sind Sie sicher, dass Sie die Preisrecherche löschen möchten?';
	echo '</div>';


	//VIEW
	echo '<div id="view">';
	echo '</div>';
	echo '<script language="javascript"> view(); </script>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>