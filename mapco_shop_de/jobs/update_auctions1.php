<?php
	include("../config.php");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml">';
	echo '<head>';
	echo '	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	echo '	<meta http-equiv="refresh" content="3600;url=http://www.mapco.de/jobs/update_auctions.php">';
	echo '	<title>Auktions-Aktualisierung</title>';
	echo '</head>';
	echo '<body>';

	//CSS
	echo '	<link rel="stylesheet" href="'.PATH.'templates/'.TEMPLATE_BACKEND.'/css/styles.css" type="text/css" />'."\n";
	//AJAX obsolet
	echo '	<script src="'.PATH.'modules/mapco/javascript.php" type="text/javascript"></script>'; 
	//jQuery
	echo '	<script src="'.PATH.'modules/jQuery/jQuery.js" type="text/javascript"></script>'."\n";
	//jQuery.tablesorter	
	echo '	<script src="'.PATH.'modules/jQuery.tablesorter/jquery.tablesorter.js"></script>'."\n";
	//jQuery UI
	echo '	<link rel="stylesheet" type="text/css" href="'.PATH.'modules/jQueryUI/css/ui-lightness/jquery-ui-1.8.18.custom.css" />'."\n";
	echo '	<script src="'.PATH.'modules/jQueryUI/js/jquery-ui-1.8.18.custom.min.js" type="text/javascript"></script>'."\n";
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
	var $timestamp=0;

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
			button_image_url : "<?php echo PATH; ?>/images/icons/24x24/add_swfupload.png",
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


	function items_export()
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
		if ( item_submit_cancel == true )
		{
			$("#items_submit_dialog").html("Übertragung erfolgreich abgebrochen.");
			return;
		}
		if ( i==items.length )
		{
			$('#items_submit_dialog').dialog('option', 'buttons', {});
			$("#items_submit_dialog").html("Alle Shopartikel erfolgreich übertragen.");
			$('#items_submit_dialog').dialog("close");
//			alert(window.location.href);
			window.location.href=window.location.href;
			return;
		}
		
		/*
		var id_account=$("#items_submit_id_account").val();
		var id_pricelist=$("#items_submit_id_pricelist").val();
		var bestoffer = $("#items_submit_bestoffer:checked").val()
		if ( bestoffer=="on" ) bestoffer=1; else bestoffer=0;
		var ShippingServiceCost=$("#items_submit_ShippingServiceCost").val();
		var id_article=$("#items_submit_id_article").val();
		var comment=$("#items_submit_comment").val();
		var items_submit_skiphours=$("#items_submit_skiphours").val();
		*/

		$("#items_submit_dialog").html('<a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[i]+'" target="_blank">Shopartikel</a> '+(i+1)+" von "+items.length+"<br /><br />Erstelle Auktionen...");
		var response = $.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCreateAuctions", id_account:id_account, id_item:items[i], bestoffer:bestoffer, ShippingServiceCost:ShippingServiceCost, id_article:id_article, id_imageformat:id_imageformat, items_submit_skiphours:items_submit_skiphours },
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
		if ( item_submit_cancel == true )
		{
			item_create(item_counter);
			return;
		}
		if (auction_counter==auction_id.length)
		{
			item_create(item_counter+1);
			return;
		}
		if (auction_action[auction_counter]=="AddItem") actiontext="Erstelle Auktion";
		else if (auction_action[auction_counter]=="ReviseItem") actiontext="Aktualisiere Auktion";
		else if (auction_action[auction_counter]=="EndItem") actiontext="Beende Auktion";
//		var items_submit_skiphours=$("#items_submit_skiphours").val();
		var status='<a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">Shopartikel</a> '+(item_counter+1)+" von "+items.length;
		status+="<br /><br />Aktion "+(auction_counter+1)+" von "+auction_id.length+": "+actiontext+" "+auction_id[auction_counter];
		$("#items_submit_dialog").html(status);
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:auction_action[auction_counter], id_auction:auction_id[auction_counter], items_submit_skiphours:items_submit_skiphours },
			function(data)
			{
//				show_status2(data);
//				return;

				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					//end auction and repeat item submission when auction format is too old
					if ( data.indexOf("<ErrorCode>302</ErrorCode>") != -1 )
					{
						$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"EndItem", id_auction:auction_id[auction_counter] },
							function(data)
							{
								item_create(item_counter);
								return;
							}
						);
						return;
					}
					//repeat item submission when auction title and subtitle cannot be revised
					else if ( data.indexOf("<ErrorCode>10039</ErrorCode>") != -1 )
					{
						item_create(item_counter);
						return;
					}
					//skip auction when price suggestions are open
					else if ( data.indexOf("<ErrorCode>21916923</ErrorCode>") != -1 )
					{
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when promo sales are active
					else if ( data.indexOf("<ErrorCode>21917089</ErrorCode>") != -1 )
					{
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when parts of the auction cannot be updated
					else if ( data.indexOf("<ErrorCode>21919028</ErrorCode>") != -1 )
					{
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when parts of the auction cannot be updated
					else if ( data.indexOf("<ErrorCode>21916730</ErrorCode>") != -1 )
					{
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when auction cannot be updated
					else if ( data.indexOf("<ErrorCode>240</ErrorCode>") != -1 )
					{
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//end auction when "old" specifics cannot be changed
					else if ( data.indexOf("<ErrorCode>21916885</ErrorCode>") != -1 )
					{
						$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"EndItem", id_auction:auction_id[auction_counter] },
							function(data)
							{
								item_create(item_counter);
								return;
							}
						);
						return;
					}
					//repeat auction when category cannot be changed
					else if ( data.indexOf("<ErrorCode>21919128</ErrorCode>") != -1 )
					{
						item_create(item_counter+1);
						return;
					}
					//skip auction when call limit is reached
					else if ( data.indexOf("<ErrorCode>21919144</ErrorCode>") != -1 )
					{
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					else
					{
						show_status2(data);
						item_create(item_counter);
						return;
					}
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
			height:400,
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Shopartikel nach eBay übertragen",
			width:400
		});
		
		item_create(0);
	}


	function items_update()
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

		$("#items_update_dialog").dialog
		({	closeText:"Fenster schließen",
			height: 200,
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Artikeldaten aktualisieren",
			width:400
		});
		
		item_update(0);
	}


	function item_update(i)
	{
		if (i<items.length)
		{
			$("#items_update_dialog").html("Aktualisiere Artikel "+(i+1)+" von "+items.length);
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemUpdate", id_item:items[i] },
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

					item_update(i+1);
				}
			)
			.error(function(x, status) { item_update(i); });
			;
		}
		else
		{
			$("#items_update_dialog").html('Alle Artikel erfolgreich aktualisiert.');
		}
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
		if ($("#list_add_private").is(":checked"))	{var private=1;} else {var private=0;}

		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAdd", title:title, private:private },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				id_list = $xml.find("ListID").text();
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}
				
				show_status("Liste erfolgreich angelegt.");
				view();
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
		
		if ($("#list_add_old_private").is(":checked"))	{var private=1;} else {var private=0;}

		$("#list_add_old_dialog").dialog("option", "buttons", { } );
		$("#list_add_old_dialog").html("Erstelle Liste...");	
		
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAdd", title:title, private:private },
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
	
	
	function list_edit(id_list, title, private)
	{
		$("#list_edit_id_list").val(id_list);
		$("#list_edit_title").val(title);
		if (private=="1") {$("#list_edit_private").attr("checked", true);} else {$("#list_edit_private").attr("checked", false);}
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
			width:450
		});
	}


	function list_edit_save()
	{
		var id_list=$("#list_edit_id_list").val();
		var title=$("#list_edit_title").val();
		if ($("#list_edit_private").is(":checked"))	{var private=1;} else {var private=0;}

		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListEdit", id_list:id_list, title:title, private:private },
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


	function list_items_add()
	{
		$("#list_items_add_text").val("");
		$("#list_items_add_dialog").dialog
		({	buttons:
			[
				{ text: "Hinzufügen", click: function() { list_items_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Artikel zu Liste hinzufügen",
			width:500
		});
	}
	
	
	function list_items_add_save()
	{
		$("#list_items_add_dialog").dialog("close");
		wait_dialog_show();
		var text=$("#list_items_add_text").val();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListItemsAdd", list_id:id_list, text:text },
			function(data)
			{
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}
				
				show_status("Artikel erfolgreich der Liste hinzugefügt.");
				wait_dialog_hide();
				view();
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
			width:450
		});
	}
	
	function pdf_export2()
	{
		var format=$("#pdf_export_format").val();
		var title1=$("#pdf_export_title1").val();
		var title2=$("#pdf_export_title2").val();
		var id_location=$("#pdf_export_id_location").val();
		var lang=$("#pdf_export_lang").val();
//		wait_dialog_show();
		post_to_url("<?php echo PATH; ?>backend_shop_items_pdfexport"+format+".php", { items:items, title1:title1, title2:title2, id_location:id_location, lang:lang }, "post");
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

	function select_all_from_here(item_id, e)
	{
		if (!e) e = window.event;
		e.returnValue = false;
		if(e.preventDefault) e.preventDefault();
		if ((e.type && e.type == "contextmenu") || (e.button && e.button == 2) || (e.which && e.which == 3))
		{
			var state=false;
			var theForm = document.itemform;
			for (i=0; i<theForm.elements.length; i++)
			{
				if ( theForm.elements[i].value == item_id) state=true;
				if (theForm.elements[i].name=='item_id[]')
					theForm.elements[i].checked = state;
			}
		}
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
					width:800
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
		var comment=$("#price_research_add_comment").val();
		$.post("modules/backend_shop_items_actions.php", { action:"price_research_add", id_item:id_item, price:price, shipping:shipping, seller:seller, EbayID:EbayID, comment:comment },
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
					var i=0;
					$xml.find("ShippingServiceOptions").each(
						function()
						{
							$ShippingServiceCost=parseFloat($(this).find("ShippingServiceCost").text());
							$ShippingService=$(this).find("ShippingService").text();
							if ( $ShippingService!="DE_Pickup" ) //skip pickup
							{
								if ( i==0 ) shipping=$ShippingServiceCost;
								else if ( shipping>$ShippingServiceCost )
								{
									shipping=$ShippingServiceCost;
								}
								i++;
							}
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
		var needle_negate=$('#needle_negate').attr('checked');
		wait_dialog_show();
		
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemsView", lang:'<?php echo $_GET["lang"]; ?>', id_list:id_list, id_menuitem:id_menuitem, filter:filter, fotostatus:fotostatus, needle:needle, needle_negate:needle_negate, pricestatus:pricestatus, pricesuggestion:pricesuggestion },
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



	//ITEMS UPDATE DIALOG
	echo '<div id="items_update_dialog" style="display:none;">';
	echo '</div>';
	
	
	//ITEMS SUBMIT OPTIONS DIALOG
	echo '<div id="items_submit_options_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Account</td>';
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
	$results=q("SELECT * FROM cms_articles AS a, cms_articles_labels AS b WHERE b.label_id=8 AND a.id_article=b.article_id AND a.published>0;", $dbweb, __FILE__, __LINE__);
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
	echo '	<tr>';
	echo '		<td>Älter als</td>';
	echo '		<td>';
	echo '				<input style="width:50px;" type="input" id="items_submit_skiphours" value="0" /> Stunden';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';


	//ITEMS SUBMIT DIALOG
	echo '<div style="display:none;" id="items_submit_dialog">';
	echo '</div>';

	//status dialog
	echo '<div style="display:none;" id="status">';
	echo '</div>';


	//WAIT DIALOG
	echo '<div id="wait_dialog" style="display:none;">';
	echo '	<img src="'.PATH.'images/icons/loaderb64.gif" style="margin:0px 0px 0px 30px" />';
	echo '</div>';





	//HEADLINE
	echo '<h1>eBay-Update-Job'."\n";
	
	echo $account_id=1;
	echo '<script type="text/javascript">';
	$i=0;
	$results=q("SELECT * FROM ebay_auctions WHERE account_id=".$account_id." ORDER BY lastupdate LIMIT 1;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo 'items['.$i.']='.$row["shopitem_id"].";\n";
		$i++;
	}

	//config
	echo '
	var id_account='.$account_id.';
	var bestoffer = 1;
	var ShippingServiceCost=5.9;
	var id_article=199;
	var items_submit_skiphours=24;
	';


	//start
	echo 'items_submit();';

    echo '</script>';

?>