<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<style>
		#list_load_status_text
		{
			width:100%;
			margin:4px 0px 0px 0px;
			border:0;
			font-weight: bold;
			text-align:center;
			text-shadow: 1px 1px 0 #fff;
			float: left;
		}
	</style>

<!--
<script type="text/javascript" src="<?php echo PATH; ?>modules/flashupload/swfupload.js"></script>
<script type="text/javascript" src="<?php echo PATH; ?>modules/flashupload/js/handlers2.js"></script>
-->
<script type="text/javascript" src="<?php echo PATH; ?>javascript/cms/ImageUpload.php"></script>
<script type="text/javascript" src="<?php echo PATH; ?>javascript/shop/Lists.php"></script>
<script type="text/javascript" src="<?php echo PATH; ?>javascript/shop/PriceResearch.php"></script>
<script language="javascript">
	var id_account=0;
	var id_imageformat=0;
	var $counter=0;
	var $fields=new Array();
	var $values=new Array();
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
	
	$.datepicker.regional['de'] = {clearText: 			'löschen',
								   clearStatus: 		'aktuelles Datum löschen',
								   closeText: 			'schließen', 
								   closeStatus: 		'ohne Änderungen schließen',
								   prevText:			'zurück', 
								   prevStatus: 			'letzten Monat zeigen',
								   nextText: 			'vor', 
								   nextStatus: 			'nächsten Monat zeigen',
								   currentText: 		'heute', 
								   currentStatus: 		'',
								   monthNames: 			['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
								   monthNamesShort: 	['Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'],
								   monthStatus: 		'anderen Monat anzeigen',
								   yearStatus: 			'anderes Jahr anzeigen',
								   weekHeader: 			'Wo', 
								   weekStatus: 			'Woche des Monats',
								   dayNames: 			['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
								   dayNamesShort: 		['So','Mo','Di','Mi','Do','Fr','Sa'],
								   dayNamesMin: 		['So','Mo','Di','Mi','Do','Fr','Sa'],
								   dayStatus: 			'Setze DD als ersten Wochentag', 
								   dateStatus: 			'Wähle D, M d',
								   dateFormat: 			'dd.mm.yy', 
								   firstDay: 			1, 
								   initStatus:			'Wähle ein Datum', 
								   isRTL: 				false,
								   changeMonth: 		true,
								   changeYear: 			true,
								   showOtherMonths:		true,
								   selectOtherMonths:	true};
	
	$(window).resize(function() { view_list_resize(); });
	function view_list_resize()
	{
		var $width=$(window).width()-400;
		$("#view_list_box").css("width", $width);
	}


	$(function()
	{
		$( "#image_mass_download_options_date" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
	});


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

		var $MPN=$("#artnr_jump_artnr").val();
		$.post('<?php echo PATH; ?>soa/', { API:"shop", Action:"ItemGet", MPN:$MPN },
			function($data)
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
				else
				{
					$id_article = $xml.find("article_id").text();
					$("#artnr_jump_dialog").dialog("close");
					window.location="<?php echo PATH ?>backend_cms_article_editor.php?id_article="+$id_article;
				}
			}
		);
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
		
		$("#image_mass_download_dialog").html("Erstelle Liste mit Abbildungen");
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


	function item_export(i, file, header)
	{
//		if (i<items.length)
		if (i<1)
		{
			$("#items_export_dialog").html("Exportiere Artikel "+(i+1)+" von "+items.length);
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemsExport", id_list:id_list, id_item:items[i], file:file, header:header },
				function(data)
				{
					try
					{
//						show_status2(data);
						$xml = $($.parseXML(data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2(data);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						return;
					}

					item_export(i+1, file, 0);
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
			return;
		}
		
		var id_account=$("#items_submit_id_account").val();
		var id_pricelist=$("#items_submit_id_pricelist").val();
		var bestoffer = $("#items_submit_bestoffer:checked").val()
		if ( bestoffer=="on" ) bestoffer=1; else bestoffer=0;
		var DiscountPercent=$("#items_submit_DiscountPercent").val();
		var ShippingServiceCost=$("#items_submit_ShippingServiceCost").val();
		var id_article=$("#items_submit_id_article").val();
		var comment=$("#items_submit_comment").val();
		var items_submit_skiphours=$("#items_submit_skiphours").val();

		$("#items_submit_dialog").html("Shopartikel "+(i+1)+" von "+items.length+"<br /><br />Erstelle Auktionen...");
		var response = $.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCreateAuctions", id_account:id_account, id_item:items[i], pricelist_id:id_pricelist, bestoffer:bestoffer, DiscountPercent:DiscountPercent, ShippingServiceCost:ShippingServiceCost, comment:comment, id_article:id_article, id_imageformat:id_imageformat, items_submit_skiphours:items_submit_skiphours },
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
				catch (err)
				{
					show_status2(err.message);
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


	function item_remove($id_list, $ids)
	{
		if ( confirm("Wollen Sie diesen Artikel wirklich löschen?") )
		{
			wait_dialog_show();
			var $postdata=new Object();
			$postdata["API"]="shop";
			$postdata["APIRequest"]="ListItemsRemove";
			$postdata["id_list"]=$id_list;
			$postdata["ids"]=$ids;
			soa2($postdata, "item_remove_success");
		}
	}
	
	function item_remove_success($xml)		
	{
		$xml.find("ItemRemoved").each(function()
		{
			for($i=0; $i<$list.length; $i++)
			{
				var $id_item=$(this).text();
				if( $list[$i]["id_item"]==$id_item ) delete $list[$i]["id_item"];
			}
		});
		

		var $list2=new Array();
		var $j=0;
		for($i=0; $i<$list.length; $i++)
		{
			if( typeof $list[$i]["id_item"] !== "undefined" )
			{
				$list2[$j]=new Array();
				$list2[$j]=$list[$i];
				$j++;
			}
		}

		$list=$list2;
		view_list();
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
		var items_submit_skiphours=$("#items_submit_skiphours").val();
		var status="Shopartikel "+(item_counter+1)+" von "+items.length;
		status+="<br /><br />Aktion "+(auction_counter+1)+" von "+auction_id.length+": "+actiontext+" "+auction_id[auction_counter];
		$("#items_submit_dialog").html(status);
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:auction_action[auction_counter], id_auction:auction_id[auction_counter], items_submit_skiphours:items_submit_skiphours },
			function(data)
			{
//				show_status2(data);
//				return;

				try
				{
					$xml = $($.parseXML(data));
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" && $ack.text()!="Warning" )
				{
					//end auction and repeat item submission when auction cannot be modified
					if ( data.indexOf("<ErrorCode>240</ErrorCode>") != -1 )
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
					//end auction and repeat item submission when auction format is too old
					else if ( data.indexOf("<ErrorCode>302</ErrorCode>") != -1 )
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
					//skip auction when auction cannot be updated
					else if ( data.indexOf("<ErrorCode>240</ErrorCode>") != -1 )
					{
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//repeat auction when category cannot be changed
					else if ( data.indexOf("<ErrorCode>21919128</ErrorCode>") != -1 )
					{
						item_create(item_counter+1);
						return;
					}
					//skip item when call limit is reached
					else if ( data.indexOf("<ErrorCode>21919144</ErrorCode>") != -1 )
					{
						item_create(item_counter+1);
						return;
					}
					//skip item
					else if ( data.indexOf("<ErrorCode>21916320</ErrorCode>") != -1 )
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


	function item_update(i)
	{
		if (i<items.length)
		{
			$("#items_update_dialog").html("Aktualisiere Artikel "+(i+1)+" von "+items.length);
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCreateAuctions2", id_item:items[i] },
				function(data)
				{
					try
					{
						$xml = $($.parseXML(data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2(data);
							item_update(i);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						item_update(i);
						return;
					}

					item_update(i+1);
				}
			);
		}
		else
		{
			$("#items_update_dialog").html('Alle Artikel erfolgreich aktualisiert.');
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
		items_export_start();
		return;

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
//		var id_exportformat=$("#items_export_id_exportformat").val();
		var now = new Date();
//		$("#items_export_options_dialog").dialog("close");
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
//		item_export(0, "export_"+now.getTime()+".csv", 1, id_exportformat);
		item_export(0, "export_"+now.getTime()+".csv", 1);
	}


	function items_get()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemsGet" },
		function($data)
		{
			wait_dialog_hide();
			$xml = $($.parseXML($data));
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" )
			{
				show_status2(data);
				return;
			}
			
			var $ArtNr="";
			$xml.find("Item").each(
				function()
				{
					$ArtNr+=$(this).text()+"\n";
				}
			);
			$("#list_items_add_text").val($ArtNr);
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


	function items_submit_job_options()
	{
		$("#items_submit_job_options_url").html('http://www.mapco.de/backend_job_ebayUpdate.php?lang=de&id_menuitem=219&id_list='+id_list+'&lastupdate=1374123411');
		$("#items_submit_job_options_url").attr('href', 'http://www.mapco.de/backend_job_ebayUpdate.php?lang=de&id_menuitem=219&id_list='+id_list+'&lastupdate=1374123411');
		$("#items_submit_job_options_dialog").dialog
		({	closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"eBay-Job starten",
			width:600
		});
	}


	function items_submit_job_url()
	{
		var $lastupdate = Math.round(+new Date()/1000);
		var $id_account = $("#items_submit_job_options_id_account").val();
		if( $id_account==0 )
		{
			$("#items_submit_job_options_url").attr('href', 'http://www.mapco.de/backend_job_ebayUpdate.php?lang=de&id_menuitem=219&id_list='+id_list+'&lastupdate='+$lastupdate);
			$("#items_submit_job_options_url").html('http://www.mapco.de/backend_job_ebayUpdate.php?lang=de&id_menuitem=219&id_list='+id_list+'&lastupdate='+$lastupdate);
		}
		else
		{
			$("#items_submit_job_options_url").attr('href', 'http://www.mapco.de/backend_job_ebayUpdate.php?lang=de&id_menuitem=219&id_list='+id_list+'&lastupdate='+$lastupdate+'&id_account='+$id_account);
			$("#items_submit_job_options_url").html('http://www.mapco.de/backend_job_ebayUpdate.php?lang=de&id_menuitem=219&id_list='+id_list+'&lastupdate='+$lastupdate+'&id_account='+$id_account);
		}
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


	function items_update()
	{
//		$("#export_dialog").dialog("close");
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


	function ebay_item_auctions_update(i)
	{
		if (i<items.length)
		{
			$("#wait_dialog_progressbar1_status").html("Aktualisiere Auktionen für Artikel "+(i+1)+" von "+items.length);
			var $percent=Math.round(i/items.length*100);
			if($percent==0) $percent=false;
			$("#wait_dialog_progressbar1").progressbar({ value: $percent });
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCreateAuctions2", id_item:items[i] },
				function(data)
				{
					try
					{
						$xml = $($.parseXML(data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2(data);
							item_update(i);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						item_update(i);
						return;
					}

					ebay_item_auctions_update(i+1);
				}
			);
		}
		else
		{
			$("#wait_dialog_progressbar1_status").html("Alle Auktionen für die Artikel erfolgreich aktualisiert.");
			var $percent=Math.round(i/items.length*100);
			if($percent==0) $percent=false;
			$("#wait_dialog_progressbar1").progressbar({ value: $percent });
		}
	}


	function ebay_items_auctions_update()
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

		wait_dialog_show("Aktualisiere Auktionen für Artikel");
		
		ebay_item_auctions_update(0);
	}


	function item_update(i)
	{
		if (i<items.length)
		{
			$("#wait_dialog_progressbar1_status").html("Aktualisiere Artikel "+(i+1)+" von "+items.length);
			var $percent=Math.round(i/items.length*100);
			if($percent==0) $percent=false;
			$("#wait_dialog_progressbar1").progressbar({ value: $percent });
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemUpdate", id_item:items[i] },
				function(data)
				{
					try
					{
						$xml = $($.parseXML(data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							if ( $ack.text()=="Unfinished" )
							{
								item_update(i);
								return;
							}
							show_status2(data);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						item_update(i);
						return;
					}

					item_update(i+1);
				}
			);
		}
		else
		{
			$("#wait_dialog_progressbar1_status").html("Alle Artikel erfolgreich aktualisiert.");
			var $percent=Math.round(i/items.length*100);
			if($percent==0) $percent=false;
			$("#wait_dialog_progressbar1").progressbar({ value: $percent });
		}
	}


	function items_update()
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

		wait_dialog_show("Aktualisiere ausgewählte Artikel...");
		
		item_update(0);
	}


	function ItemDetailsGet($id)
	{
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="TableDataSelect";
		$postdata["table"]="shop_lists_items";
		$postdata["db"]="dbshop";
		$postdata["where"]="WHERE list_id="+$id;
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			var $id_item="";
			$xml.find("item_id").each(
				function()
				{
					if($id_item!="") $id_item+=", ";
					$id_item+=$(this).text();
				}
			);

			var $postdata=new Object();
			$postdata["API"]="cms";
			$postdata["APIRequest"]="TableDataSelect";
			$postdata["table"]="shop_lists_fields";
			$postdata["db"]="dbshop";
			$postdata["where"]="WHERE list_id="+$id+" ORDER BY ordering";
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
	
				var $id_field="";
				var $id_value="";
				var $id_language="";
				$xml.find("shop_lists_fields").each(
					function()
					{
						if($id_field!="")
						{
							$id_field+=", ";
							$id_value+=", ";
							$id_language+=", ";
						}
						$id_field+=$(this).find("field_id").text();
						$id_value+=$(this).find("value_id").text();
						$id_language+=$(this).find("language_id").text();
					}
				);
				
				var $postdata=new Object();
				$postdata["API"]="shop";
				$postdata["Action"]="ItemDetailsGet";
				$postdata["id_field"]=$id_field;
				$postdata["id_value"]=$id_value;
				$postdata["id_language"]=$id_language;
				$postdata["id_item"]=$id_item;
				$.post("<?php echo PATH; ?>soa/", $postdata, function($data)
				{
					return($data);
				});
			});
		});
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

		id_listtype=$("#list_add_id_listtype").val();
		var title=$("#list_add_title").val();
		if ( title=="" )
		{
			show_status("Der Titel darf nicht leer sein.");
			return;
		}
		if ($("#list_add_private").is(":checked"))	{var private=1;} else {var private=0;}

		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListAdd", title:title, id_listtype:id_listtype },
			function(data)
			{
				try
				{
					$xml = $($.parseXML(data));
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				$ack = $xml.find("Ack");
				id_list = $xml.find("ListID").text();
				id_menuitem=0;
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



	function list_edit_save()
	{
		var $postdata=new Object();
		$postdata["API"]="shop";
		$postdata["Action"]="ListEdit";
		$postdata["id_list"]=$("#list_edit_id_list").val();
		$postdata["listtype_id"]=$("#list_edit_listtype_id").val();
		$postdata["title"]=$("#list_edit_title").val();
		$.post("<?php echo PATH; ?>soa/", $postdata, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
	
			show_status("Die Liste wurde erfolgreich aktualisiert.");
			if (id_list==$postdata["id_list"]) id_listtype=$postdata["listtype_id"];
			view();
			$("#list_edit_dialog").dialog("close");
		});
	}


	function list_field_add()
	{
		var list_id=$("#list_edit_id_list").val();
		var field_id=$("#list_edit_new_id_field").val();
		var value_id=$("#list_edit_new_id_value").val();
		var title=$("#list_edit_new_title").val();
		$.post("<?php echo PATH ?>soa2/", { API:"shop", APIRequest:"ListFieldAdd", list_id:list_id, field_id:field_id, value_id:value_id, title:title },
			function(data)
			{
				show_status(data);
				list_edit_view();
			}
		);
	}


	function list_field_remove(id)
	{
		$.post("<?php echo PATH ?>soa/", { API:"shop", Action:"ListFieldRemove", id:id },
			function(data)
			{
				show_status(data);
				list_edit_view();
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


	function list_items_add_gart()
	{
		var $GART=$("#list_items_add_gart").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemsGet", GART:$GART },
		function($data)
		{
			wait_dialog_hide();
			$xml = $($.parseXML($data));
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" )
			{
				show_status2(data);
				return;
			}
			
			var $ArtNr="";
			$xml.find("Item").each(
				function()
				{
					$ArtNr+=$(this).text()+"\n";
				}
			);
			$("#list_items_add_text").val($ArtNr);
		});
		
	}


	function list_items_add_priceresearch(id_list)
	{
		show_status("Rufe 10 Artikel zur Preisrecherche ab...");
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceResearchAddToList", id_list:id_list },
			function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2($data);
					return;
				}
				
				hide_status();
				view();
			}
		);
	}


	function list_items_add_save()
	{
		$("#list_items_add_dialog").dialog("close");
		wait_dialog_show();
		var text=$("#list_items_add_text").val();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListItemsAdd", list_id:id_list, text:text },
			function(data)
			{
				try
				{
					$xml = $($.parseXML(data));
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}
				
				$status="";
				$Found = $xml.find("Found").text();
				if( $Found!="" ) $status += $Found+" Nummern erkannt.";
				$xml.find("Skipped").each(
					function()
					{
						$Skipped=$(this).text();
						$status += "<br /> "+$Skipped+" nicht erkannt.";
					}
				);
				show_status($status);
				wait_dialog_hide();
				view();
			}
		);
	}


	function list_items_remove()
	{
		if ( typeof document.itemform === "undefined" )
		{
			alert("Bitte wählen Sie zunächst eine Liste aus.");
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
			alert("Bitte wählen Sie die Referenzen aus, die Sie aus der Liste entfernen möchten.");
			return;
		}
		
		var $ids=items.join(", ");
		item_remove(id_list, $ids);
	}


	function list_remove($id_list)
	{
		if ( confirm("Wollen Sie die Liste wirklich löschen?") )
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListRemove", id_list:$id_list },	function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }

				id_list=-1;
				show_status("Die Liste wurde erfolgreich gelöscht.");
				view();
			});
		}
	}


	function lists_compare($id_list1, $id_list2)
	{
		$id_list2=1597;
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListsCompare", id_list1:$id_list1, id_list2:$id_list2 },	function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			show_status2($data);
		});
	}


	function lists_hide($id)
	{
		$($id).hide();
		$($id+"_image").attr("src", "<?php echo PATH; ?>images/icons/24x24/right.png");
	}


	function lists_show($id)
	{
		$($id).show();
		$($id+"_image").attr("src", "<?php echo PATH; ?>images/icons/24x24/down.png");
	}
	

	function lists_showhide($id)
	{
		var $display=$($id).css("display");
		if( $display=="block" ) lists_hide($id);
		else lists_show($id);
	}
	
	function offer_timerange_save(id_list)
	{
		var date_from = new Date(0);
		var date_to = new Date(0);
		//var offer_start = 0;
		//var offer_end = 0;
		var percentage = 0;
		
		if($('#date_from').val() != '')
		{
			date_from = $('#date_from').datepicker('getDate');
			date_from.setHours($('#hour_from').val());
			date_to = $('#date_to').datepicker('getDate');
			date_to.setHours($('#hour_to').val());
		}
		
		date_from = date_from.getTime()/1000;
		date_to = date_to.getTime()/1000;
		
		percentage = $('#percentage').val();
		
		var post_data = new Object;
		post_data['API'] = 'shop';
		post_data['APIRequest'] = 'OfferTimerangeSet';
		post_data['id_list'] = id_list;
		post_data['offer_start'] = date_from;
		post_data['offer_end'] = date_to;
		post_data['percentage'] = percentage;
				
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_data, function($data){
			wait_dialog_hide();
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); return;}
			if($xml.find('Ack').text() != 'Success'){show_status2($data); return;}
			//show_status2($data);
			
			if($xml.find('active').text() == '0')
				$('#offer_icon_' + id_list).attr('src', '<?php echo PATH;?>images/icons/24x24/shopping_cart_favorite.png');
			else
				$('#offer_icon_' + id_list).attr('src', '<?php echo PATH;?>images/icons/24x24/shopping_cart_accept.png');
		});		
	}
	
	function offer_timerange_set(id_list)
	{
		var post_data = new Object;
		post_data["API"] = "shop";
		post_data["APIRequest"] = "OfferTimerangeGet";
		post_data["id_list"] = id_list;
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_data, function($data){
			wait_dialog_hide();
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message); return;}
			if($xml.find('Ack').text() != 'Success'){show_status2($data+print_r(post_data)); return;}
			
			//show_status2($data);
			var offer_start = $xml.find('offer_start').text();
			var offer_end = $xml.find('offer_end').text();
			var percentage = $xml.find('percentage').text();
		
			var option;
			var main = $('#offer_timerange_dialog');
			main.empty();
			var text = $('<h2><p>Zeitraum auswählen:</p></h2>');
			main.append(text);
			text = $('<input type="hidden" autofocus="autofocus" />');
			main.append(text);
			var table = $('<table></table>');
				var tr = $('<tr></tr>');
					var td = $('<td><b>Beginn:</b></td>');
					tr.append(td);
					td = $('<td><input id="date_from" readonly></td>');
					tr.append(td);
					td = $('<td></td>');
					var time_select = $('<select id="hour_from"></select>')
					for(i=0; i<24; i++){
						option = $('<option value="' + i + '">' + i + ':00</option>');	
						time_select.append(option);
					}
					td.append(time_select);
					tr.append(td);
				table.append(tr);
				tr = $('<tr></tr>');
					td = $('<td><b>Ende:</b></td>');
					tr.append(td);
					td = $('<td><input id="date_to" readonly></td>');
					tr.append(td);
					td = $('<td></td>');
					time_select = $('<select id="hour_to"></select>')
					for(i=0; i<24; i++){
						option = $('<option value="' + i + '">' + i + ':00</option>');	
						time_select.append(option);
					}
					td.append(time_select);
					tr.append(td);
				table.append(tr);
				tr = $('<tr></tr>');
					td = $('<td></td>');
					tr.append(td);
					td = $('<td><input type= "button" id="date_del_button" value="Zeitraum löschen"></td>');
					tr.append(td);
					td = $('<td></td>');
					tr.append(td);
				table.append(tr);
				tr = $('<tr></tr>');
					td = $('<td colspan="2"><b>Rabatt (Prozent):</b></td>');
					tr.append(td);
					td = $('<td></td>');
					var percent_select = $('<select id="percentage"></select>');
					for(j=0; j<=100; j++){
						option = $('<option value="' + j + '">' + j + '</option>');
						percent_select.append(option);
					}
					td.append(percent_select);
					tr.append(td);
				table.append(tr);
			main.append(table);
			
			$("#date_from").datepicker($.datepicker.regional['de']);
			$("#date_to").datepicker($.datepicker.regional['de']);
			
			$("#date_from").datepicker("option", "onClose", function(selectedDate)
												 {
													 $("#date_to" ).datepicker( "option", "minDate", selectedDate);
												 }
			);
			$("#date_to").datepicker("option", "onClose", function(selectedDate)
												 {
													 $("#date_from" ).datepicker( "option", "maxDate", selectedDate);
												 }
			);
			
			$('#date_del_button').click(function(){
				$.datepicker._clearDate($('#date_from'));
				$.datepicker._clearDate($('#date_to'));
				$('#hour_from').val('0');
				$('#hour_to').val('0');
				$('#percentage').val('0');
			});
			
			//set date values
			if(offer_start != 0 && offer_end != 0)
			{
				var date_from = new Date(parseInt(offer_start*1000));
				$('#date_from').datepicker("setDate", date_from);
				$('#hour_from').val(date_from.getHours());
				var date_to = new Date(offer_end*1000);
				$('#date_to').datepicker("setDate", date_to);
				$('#hour_to').val(date_to.getHours());
				$('#percentage').val(percentage);
			} 
			else
				$('#hour_to').val(23);
				
			$("#offer_timerange_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { 
													if(($('#date_from').val() != "" && $('#date_to').val() == "") || ($('#date_from').val() == "" && $('#date_to').val() != ""))
													{
														alert("Es müssen beide Datumsfelder ausgefüllt sein!");
														return;
													}
													offer_timerange_save(id_list); 
													$(this).dialog("close"); 
												} },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Angebotszeitraum auswählen",
				width:320
			});
		});
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
		post_to_url("<?php echo PATH; ?>backend_shop_items_pdfexport"+format+".php", { items:items, title1:title1, title2:title2, id_location:id_location, lang:lang, id_list:id_list }, "post");
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




	function promotion_item_update(i)
	{
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"SetPromotionalSaleListings", id_account:1, PromotionalSaleID:8385376015, id_item:items[i] },
			function(data)
			{
				show_status2(data);
			}
		);
	}


	function promotion_items_update()
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
		
		promotion_item_update(0);
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


	function show_itemgroups()
	{
		$("#lists").hide();
		$("#tab_lists").css("font-weight", "normal");
		$("#itemgroups").show();
		$("#tab_itemgroups").css("font-weight", "bold");
	}


	function show_lists()
	{
		$("#itemgroups").hide();
		$("#tab_itemgroups").css("font-weight", "normal");
		$("#lists").show();
		$("#tab_lists").css("font-weight", "bold");
	}


	function text_search(text)
	{
		setTimeout("view('"+text+"')", 250);
	}


	function view($NextItem)
	{
		if (id_list<1)
		{
			view2();
			return;
		}

		var $percent=0;
		if ( typeof $NextItem === "undefined")
		{
			$NextItem=1;

			wait_dialog_show("Lese Listenformat aus", 0);
			if(table_data_select("shop_lists_fields", "*", "WHERE list_id="+id_list+" ORDER BY ordering", "dbshop", "$shop_lists_fields", "view")) return;

			wait_dialog_show("Lese Listeninhalt aus", 10);
			if(table_data_select("shop_lists_items", "item_id", "WHERE list_id="+id_list, "dbshop", "$shop_lists_items", "view")) return;
		}

		var $id_item="";
		for($i=0; $i<$shop_lists_items.length; $i++)
		{
			if($id_item!="") $id_item+=", ";
			$id_item+=$shop_lists_items[$i]["item_id"];
		}

		var $id_field="";
		var $id_value="";
		var $id_language="";
		var $title="";
		for($i=0; $i<$shop_lists_fields.length; $i++)
		{
			if($id_field!="")
			{
				$id_field+=", ";
				$id_value+=", ";
				$id_language+=", ";
				$title+=", ";
			}
			$id_field+=$shop_lists_fields[$i]["field_id"];
			$id_value+=$shop_lists_fields[$i]["value_id"];
			$id_language+=$shop_lists_fields[$i]["language_id"];
			$title+=$shop_lists_fields[$i]["title"];
		}

		var $postdata=new Object();
		$postdata["API"]="shop";
		$postdata["Action"]="ItemDetailsGet";
		$postdata["id_field"]=$id_field;
		$postdata["id_value"]=$id_value;
		$postdata["id_language"]=$id_language;
		$postdata["title"]=$title;
		$postdata["id_item"]=$id_item;
		$postdata["NextItem"]=$NextItem;
		if ($NextItem==1) wait_dialog_show("Lese Artikelinformationen aus", 20);
		$.post("<?php echo PATH; ?>soa/", $postdata, function($data)
		{
			if( $NextItem==1 ) $list=new Array();
			$i=$list.length;
			try
			{
				$listdata = $($.parseXML($data));
			}
			catch (err)
			{
				show_status2(err.message);
				return;
			}		
			
			if($NextItem==1)
			{
				$listheader=new Array();
				$j=0;
				$listdata.find("Header").each(
					function()
					{
						$(this).children().each(
							function()
							{
								$listheader[$j]=new Array();
								$listheader[$j]["name"]=$(this).attr("name");
								$listheader[$j]["title"]=$(this).text();
								$j++;
							}
						);
					}
				);
			}
			$listdata.find("Item").each(
				function()
				{
					$j=0;
					$list[$i]=new Array();
					$id=$(this).attr("id");
					$list[$i]["id"]=$id;
					$id_item=$(this).attr("id_item");
					$list[$i]["id_item"]=$id_item;
					$id_article=$(this).attr("id_article");
					$list[$i]["id_article"]=$id_article;
					$(this).children().each(
						function()
						{
							$tagname=this.tagName;
							$list[$i][$tagname]=$(this).text();
							$list[$i][$j]=$(this).text();
							$j++;
						}
					);
					$i++;
					$NextItem++;
				}
			);
			$NextItem--;
			var $TotalItems=parseInt($listdata.find("TotalItems").text());
			if( $TotalItems==0 ) $percent=0; else $percent=Math.floor($NextItem/$TotalItems*80)+20;
			wait_dialog_show("Lese Artikelinformationen aus", $percent);
			if( !list_load_cancel && $NextItem<$TotalItems )
			{
				view($NextItem+1);
			}
			else
			{
				delete $shop_lists_items;
				delete $shop_lists_fields;
				view2();
				return;
			}
		});
	}


	function view2()
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
		wait_dialog_show("Zeichne Tabelle", 100);

		
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemsView", lang:'<?php echo $_GET["lang"]; ?>', id_list:id_list, id_menuitem:id_menuitem, filter:filter, fotostatus:fotostatus, needle:needle, needle_negate:needle_negate, pricestatus:pricestatus, pricesuggestion:pricesuggestion },
			function(data)
			{
				$("#view").html(data);
				
				//toggle view
				lists_show("#lists"+id_listtype);
				if( id_listtype!=1 ) lists_hide("#lists1");
				if( id_listtype!=2 ) lists_hide("#lists2");
				if( id_listtype!=3 ) lists_hide("#lists3");

				$sort_by=undefined;
				$sort_desc=0;
				view_list();

/*
				list_add_old_initialize();
				$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ListGet", lang:'<?php echo $_GET["lang"]; ?>', id_list:id_list },
					function(data)
					{
						$i=0;
						try
						{
							$listdata = $($.parseXML(data));
						}
						catch (err)
						{
							show_status2(err.message);
							return;
						}
						$("#view_list_header").html($listdata.find("Title").text());
/					
						$list=new Array();
						$listdata.find("Item").each(
							function()
							{
								$j=0;
								$list[$i]=new Array();
								$id=$(this).attr("id");
								$list[$i]["id"]=$id;
								$id_item=$(this).attr("id_item");
								$list[$i]["id_item"]=$id_item;
								$id_article=$(this).attr("id_article");
								$list[$i]["id_article"]=$id_article;
								$(this).children().each(
									function()
									{
										$tagname=this.tagName;
										$list[$i][$tagname]=$(this).text();
										$list[$i][$j]=$(this).text();
										$j++;
									}
								);
								$i++;
							}
						);


						$listheader=new Array();
						$i=0;
						$listdata.find("Header").each(
							function()
							{
								$(this).children().each(
									function()
									{
										$listheader[$i]=new Array();
										$listheader[$i]["name"]=$(this).attr("name");
										$listheader[$i]["title"]=$(this).text();
										$i++;
									}
								);
							}
						);

						$sort_by=undefined;
						$sort_desc=0;
						view_list();
//						wait_dialog_hide();
					}
				);
*/
			}
		);
	}


	function view_list($sortby, $desc)
	{
		if(id_list == -1)
		{
			$("#view_list_header").html("Bitte Liste auswählen");
			wait_dialog_hide();
			return;
		}
		view_list_resize();
		if( typeof $sortby != "undefined" && typeof $desc != "undefined" )
		{
			$sort_by=$sortby;
			$sort_desc=$desc;
		}

		var $filter=new Array();

		//show filter options
		if ( typeof $list[0] != "undefined" )
		{
			if ( typeof $list[0]["QuantityCentral"] != "undefined" )
			{
				$tagname="QuantityCentral";
				$filters="";
				$filter[$tagname]=$("#Filter"+$tagname).val();
				if ( typeof $filter[$tagname] == "undefined" )
				{
					$filter[$tagname]=0;
					$filters+='<select id="Filter'+$tagname+'" onchange="view_list();">';
					$filters+='	<option value="0">Zentralbestand: alle</option>';
					$filters+='	<option value="1">Zentralbestand: sofort lieferbar</option>';
					$filters+='	<option value="2">Zentralbestand: nicht lieferbar</option>';
					$filters+='</select>';
					$("#view_filters").append($filters);
				}
				$("Filter"+$tagname).val($filter[$tagname]);
			}
			if ( typeof $list[0]["QuantityMOCOM"] != "undefined" )
			{
				$tagname="QuantityMOCOM";
				$filters="";
				$filter[$tagname]=$("#Filter"+$tagname).val();
				if ( typeof $filter[$tagname] == "undefined" )
				{
					$filter[$tagname]=0;
					$filters+='<select id="Filter'+$tagname+'" onchange="view_list();">';
					$filters+='	<option value="0">MOCOM-Bestand: alle</option>';
					$filters+='	<option value="1">MOCOM-Bestand: sofort lieferbar</option>';
					$filters+='	<option value="2">MOCOM-Bestand: nicht lieferbar</option>';
					$filters+='</select>';
					$("#view_filters").append($filters);
				}
				$("Filter"+$tagname).val($filter[$tagname]);
			}
			//PriceResearch
			$tagname="PriceResearch";
			if ( typeof $list[0][$tagname] != "undefined" )
			{
				$filter[$tagname]=$("#Filter"+$tagname).val();
				if ( typeof $filter[$tagname] == "undefined" )
				{
					$filter[$tagname]=0;
					$filters="";
					$filters+='<select id="Filter'+$tagname+'" onchange="view_list();">';
					$filters+='	<option value="0">Preisrecherche: 0</option>';
					$filters+='	<option value="1">Preisrecherche: 0-2</option>';
					$filters+='	<option value="2">Preisrecherche: 1-2</option>';
					$filters+='	<option value="3">Preisrecherche: 3+</option>';
					$filters+='</select>';
					$("#view_filters").append($filters);
				}
				$("Filter"+$tagname).val($filter[$tagname]);
			}
			//PriceRelevant
			$tagname="PriceRelevant";
			if ( typeof $list[0][$tagname] != "undefined" )
			{
				$filter[$tagname]=$("#Filter"+$tagname).val();
				if ( typeof $filter[$tagname] == "undefined" )
				{
					$filter[$tagname]=0;
					$filters="";
					$filters+='<select id="Filter'+$tagname+'" onchange="view_list();">';
					$filters+='	<option value="0">Preis aktuell: alle</option>';
					$filters+='	<option value="1">Preis aktuell: ja</option>';
					$filters+='	<option value="2">Preis aktuell: nein</option>';
					$filters+='</select>';
					$("#view_filters").append($filters);
				}
				$("Filter"+$tagname).val($filter[$tagname]);
			}
		}

		//show list
		$listhtml='<form name="itemform">';
		$listhtml+='<table class="hover" style="margin:0;">';
		//show header
		$listdata.find("Header").each(
			function()
			{
				$listhtml+='<tr>';
				$listhtml+='<th><input id="selectall" type="checkbox" onclick="checkAll();" /></th>';
				$listhtml+='<th>Nr.</th>';
				var $cols=0;
				$(this).children().each(
					function()
					{
						$cols++;
						$name=$(this).attr("name");
						if ( $sort_by==$name )
						{
							if( $sort_desc==0 )
							{
								$descendant=1;
								$src='images/icons/16x16/up.png';
								$alt=' aufsteigend sortiert';
							}
							else
							{
								$descendant=0;
								$src='images/icons/16x16/down.png';
								$alt=' absteigend sortiert';
							}
						}
						else
						{
							$src="";
							$descendant=0;
						}
						$listhtml+='<th>';
						$listhtml+='	<a href="javascript:view_list(\''+$name+'\', '+$descendant+');">';
						$listhtml+=$(this).text();
						if( $src!="" ) $listhtml+='<img src="<?php echo PATH; ?>'+$src+'" alt="'+$alt+'" title="'+$alt+'" />';
						$listhtml+='	</a>';
						$listhtml+='</th>';
					}
				);
				$listhtml+='<th style="width:150px;">Optionen</th>';
				$listhtml+='</th>';
				$listhtml+='</tr>';
				$listhtml+='<tr>';
				$listhtml+='<th colspan="'+($cols+3)+'">';

//				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/up.png" alt="Neuen eBay-Upload-Job starten" title="Neuen eBay-Upload-Job starten" onclick="items_submit_job_options();" />';
//				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/up.png" alt="Zu eBay übertragen" title="Zu eBay übertragen" onclick="items_submit_options();" />';
				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/shopping_cart_up.png" alt="Artikel zu Promotion bei eBay hinzufügen" title="Artikel zu Promotion bei eBay hinzufügen" onclick="promotion_items_update();">';
				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/page_swap.png" alt="Listen vergleichen" title="Listen vergleichen" onclick="lists_compare('+id_list+', 1597);">';
				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/down.png" alt="Exportmenü öffnen" title="Exportmenü öffnen" onclick="export_overview();">';
				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/repeat.png" alt="Auktionen für Artikel aktualisieren" title="Auktionen für Artikel aktualisieren" onclick="ebay_items_auctions_update();">';
				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/repeat.png" alt="Artikeldaten aktualisieren" title="Artikeldaten aktualisieren" onclick="items_update();">';
				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/search.png" alt="Preisrecherche-Artikel zu Liste hinzufügen" title="Preisrecherche-Artikel zu Liste hinzufügen" onclick="list_items_add_priceresearch('+id_list+');">';
				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/remove.png" alt="Alle Artikel aus der Liste entfernen" title="Alle Artikel aus der Liste entfernen" onclick="list_items_remove();">';
				$listhtml+='<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="<?php echo PATH; ?>images/icons/24x24/add.png" alt="Artikel zu Liste hinzufügen" title="Artikel zu Liste hinzufügen" onclick="list_items_add();">';
				$listhtml+='</th>';
				$listhtml+='</tr>';
			}
		);
		$("#view_list").html($listhtml+"</table></form>");
		
		//array multisort
		if ( typeof $sort_by != "undefined" )
		{
			wait_dialog_show("Sortiere Liste");
			if ( is_numeric($list[0][$sort_by]) )
			{
				$list.sort(function(a, b) {  return a[$sort_by] - b[$sort_by]; })
				if ( $sort_desc>0 )
				{
					$list.reverse();
				}
			}
			else
			{
				$list.sort(function(a, b) {  return a[$sort_by].localeCompare(b[$sort_by]); })
				if ( $sort_desc>0 )
				{
					$list.reverse();
				}
			}
			wait_dialog_hide();
		}
		
		//show columns
		$nr=0;
		for($i=0; $i<$list.length; $i++)
		{
			$("#view_list").html($i);
			$show=true;
			if ( $filter["PriceRelevant"]==1 && $list[$i]["PriceRelevant"]!="ja" ) $show=false;
			if ( $filter["PriceRelevant"]==2 && $list[$i]["PriceRelevant"]!="nein" ) $show=false;
			if ( $filter["PriceResearch"]==1 && $list[$i]["PriceResearch"]>2 ) $show=false;
			if ( $filter["PriceResearch"]==2 && ($list[$i]["PriceResearch"]<1 || $list[$i]["PriceResearch"]>2) ) $show=false;
			if ( $filter["PriceResearch"]==3 && $list[$i]["PriceResearch"]<3 ) $show=false;
			if ( $filter["QuantityCentral"]==1 && $list[$i]["QuantityCentral"]==0 ) $show=false;
			if ( $filter["QuantityCentral"]==2 && $list[$i]["QuantityCentral"]>0 ) $show=false;
			if ( $filter["QuantityMOCOM"]==1 && $list[$i]["QuantityMOCOM"]==0 ) $show=false;
			if ( $filter["QuantityMOCOM"]==2 && $list[$i]["QuantityMOCOM"]>0 ) $show=false;
			if ( $show==true )
			{
				$nr++;
				$row="";
				$id=$list[$i]["id"]; //show_status2($id);
				$id_item=$list[$i]["id_item"];
				$id_article=$list[$i]["id_article"];
				$row+='<tr id="list_item_'+$id+'">';
				$row+='<td><input name="item_id[]" type="checkbox" value="'+$id_item+'" onmousedown="select_all_from_here(this.value);" /></td>';
				$row+='<td>'+$nr+'</td>';
				for($j=0; $j<$listheader.length; $j++)
				{
					if( $listheader[$j]["name"]=="SKU" )
					{
						$row+='<td><a href="<?php echo PATHLANG; ?>online-shop/autoteile/'+$list[$i]["id_item"]+'/" target="_blank">'+$list[$i][$j]+'</a></td>';
					}
					else if( $listheader[$j]["name"]=="ImagePreview" )
					{
						$row+='<td><img style="width:100px;" src="'+$list[$i][$j]+'" /></td>';
					}
					else if( $listheader[$j]["name"]=="ImageOverview" )
					{
						var $url=$list[$i][$j].split(", ");
						$row+='<td>';
						for($k=0; $k<$url.length; $k++)
						{
							$row+='	<img style="width:100px;" src="'+$url[$k]+'" />';
						}
						$row+='</td>';
					}
					else
					{
						if( $list[$i][$j].length>100 ) $value=$list[$i][$j].substring(0, 100)+'...';
						else $value=$list[$i][$j];
						if($value!="") $row+='<td>'+$value+'</td>';
						else $row+='<td>&nbsp;</td>';
					}
				}
				$row+='<td> ';
				$row+='<img src="<?php echo PATH; ?>images/icons/24x24/remove.png" style="cursor:pointer; float:right;" onclick="item_remove('+id_list+', '+$id_item+');" alt="Artikel löschen" title="Artikel löschen">';
				$row+='<img src="<?php echo PATH; ?>images/icons/24x24/search.png" alt="Preisrecherche" style="cursor:pointer; float:right;" title="Preisrecherche" onclick="price_research('+$id_item+', \'\');">';
				$row+='<a href="<?php echo PATH; ?>backend_cms_article_editor.php?id_article='+$id_article+'" target="_blank" title="Shopartikel bearbeiten"><img src="<?php echo PATH; ?>images/icons/24x24/edit.png" alt="Shopartikel-Beschreibung bearbeiten" title="Shopartikel-Beschreibung bearbeiten"></a>';
				$row+='</td>';
				$row+='</tr>';
				$listhtml+=$row;
			}
		}
		$listhtml+='</table>';
		$listhtml+='</form>';
		$("#view_list").html($listhtml);
		wait_dialog_hide();
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
	if( $_SESSION["userrole_id"]==1 or $_SESSION["userrole_id"]==3 or $_SESSION["userrole_id"]==4 or $_SESSION["userrole_id"]==15 )
	{
		//price research stats
		echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="'.PATH.'images/icons/24x24/chart.png" alt="Statistiken Preisrecherche" title="Statistiken Preisrecherche" onclick="price_research_stats();" />'."\n";
	}
	if( $_SESSION["userrole_id"]==1 or $_SESSION["userrole_id"]==3 or $_SESSION["userrole_id"]==15 )
	{
		//image mass upload
		echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="'.PATH.'images/icons/24x24/image_up.png" alt="Foto-Massenupload" title="Foto-Massenupload" onclick="images_upload();" />'."\n";
		//image mass download
		echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="'.PATH.'images/icons/24x24/image_down.png" alt="Foto-Massenupload" title="Foto-Massendownload" onclick="image_mass_download_options();" />'."\n";
		//item jump
		echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="'.PATH.'images/icons/24x24/next.png" alt="Zu Shopartikel springen" title="Zu Shopartikel springen" onclick="artnr_jump();" />'."\n";
	}
	echo '</h1>'."\n";


	//ARTNR JUMP DIALOG
	echo '<div id="artnr_jump_dialog" style="display:none;">';
	echo '	<input id="artnr_jump_artnr" type="text" value="" onkeypress="artnr_jump_accept();" />';
	echo '</div>';


	//EBAY AUCTIONS DIALOG
	echo '<div style="display:none;" id="ebay_auctions_dialog">';
	echo '</div>';


	//EXPORT DIALOG
	echo '<div style="display:none;" id="export_dialog">';
	echo '	<ul class="quickaccess">';
	echo '		<li>';
	echo '			<a href="javascript:items_export();" title="Als CSV exportieren">';
	echo '				<img alt="Als CSV exportieren" onclick="" src="'.PATH.'images/icons/64x64/application.png" title="Als CSV exportieren" />';
	echo '				Als CSV exportieren';
	echo '			</a>';
	echo '		</li>';
	echo '		<li>';
	echo '			<a href="javascript:images_export();" title="Als CSV exportieren">';
	echo '				<img alt="Bilder exportieren" onclick="" src="'.PATH.'images/icons/64x64/image.png" title="Bilder exportieren" />';
	echo '				Bilder exportieren';
	echo '			</a>';
	echo '		</li>';
	echo '		<li>';
	echo '			<a href="javascript:pdf_export();" title="Als CSV exportieren">';
	echo '				<img alt="Als PDF exportieren" onclick="" src="'.PATH.'images/icons/64x64/pdf.png" title="Als PDF exportieren" />';
	echo '				Als PDF exportieren';
	echo '			</a>';
	echo '		</li>';
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


	//IMAGES EXPORT OPTIONS DIALOG
	echo '<div id="images_export_options_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Format</td>';
	echo '			<td>';
	echo '				<select id="images_export_id_exportformat">';
	echo '					<option>Amazon UK</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';

	
	//ITEMS EXPORT OPTIONS DIALOG
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
	$results=q("SELECT * FROM ebay_accounts WHERE active=1 ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while ( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_account"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
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
	echo '				<input style="width:50px;" type="input" id="items_submit_skiphours" value="1" /> Stunden';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';


	//ITEMS SUBMIT JOB OPTIONS DIALOG
	echo '<div id="items_submit_job_options_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Account</td>';
	echo '		<td>';
	echo '			<select id="items_submit_job_options_id_account" onchange="items_submit_job_url();">';
	echo '				<option value="0">Alle</option>';
	$results=q("SELECT * FROM ebay_accounts WHERE active=1 ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while ( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_account"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"><a href="" id="items_submit_job_options_url" target="_blank"></a></td>';
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
	echo '		<tr>';
	echo '			<td>Listentyp</td>';
	echo '			<td>';
	echo '				<select id="list_add_id_listtype">';
	$results=q("SELECT * FROM shop_listtypes ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_listtype"].'">'.$row["title"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
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
	echo '		<tr>';
	echo '			<td>Listentyp</td>';
	echo '			<td>';
	echo '				<select id="list_edit_id_listtype">';
	$results=q("SELECT * FROM shop_listtypes ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_listtype"].'">'.$row["title"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '	<input id="list_edit_id_list" type="hidden">';
	echo '	<div id="list_edit_view"></div>';
	echo '</div>';


	//LIST ITEMS ADD
	echo '<div id="list_items_add_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td colspan="2">Es können <b>MAPCO-Artikelnummern</b>, <b>eBay-Artikelnummern</b> oder <b>OE/OEM-Nummern</b> getrennt durch Komma, Semikolon oder Zeilenumbruch in das Textfeld eingegeben werden.</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Artikelnummern</td>';
	echo '			<td><textarea id="list_items_add_text" style="width:300px; height:150px;"></textarea></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Optionen</td>';
	echo '			<td><input type="button" value="Alle aktiven MAPCO-Nummern" onclick="items_get();" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Generische Artikel</td>';
	echo '			<td>';
	echo '				<select id="list_items_add_gart">';
	$results=q("SELECT * FROM shop_items_keywords WHERE language_id='".$_SESSION["id_language"]."' AND ordering=1 ORDER BY keyword;", $dbshop, __FILE___, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["GART"].'">'.$row["keyword"].'</option>';
	}
	echo '				</select>';
	echo '				<input type="button" value="Hinzufügen" onclick="list_items_add_gart();" />';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';


	//LIST LOAD DIALOG
	echo '<div id="list_load_dialog" style="display:none;">';
	echo '	<div id="list_load_status"><span id="list_load_status_text">0%</span></div>';
	echo '	<div id="list_load_status2"></div>';
	echo '</div>';
	
	//OFFER TIMERANGE DIALOG
	echo '<div id="offer_timerange_dialog" style="display: none;">';
	echo '</div>';

	//PDF EXPORT DIALOG
	echo '<div id="pdf_export_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Format</td>';
	echo '			<td>';
	echo '				<select id="pdf_export_format">';
	echo '	<option value="3">Flexible Liste ausgeben</option>';
	echo '	<option value="">Titel, Passend für, Vergleichsnummer</option>';
	echo '	<option value="2">Titel, Passend für, Zustand</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
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
	echo '		</tr>';
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


	//VIEW
	echo '<div id="view">';
	echo '</div>';
	echo '<script language="javascript"> view(); </script>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>