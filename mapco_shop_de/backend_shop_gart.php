<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
<script type="text/javascript">
	var language_code=0;
	var GART_view="description";
	
	function setGartLabel(gart, lang_code)
	{
		language_code=0;
		if ( typeof lang_code != "undefined" ) language_code=lang_code;
		$(".GartLabel").css("font-weight","normal");
		$("#GartLabel"+gart).css("font-weight","bold");
		view("detail", gart);
	}
	
	function setGartTab(detail)
	{
		$(".tab").css("font-weight","normal");
		$("#tab_"+detail).css("font-weight","bold");
	}

	function view_detail(detail)
	{
		$(".detailView").hide();
		$("#detail_"+detail).show();
		
		GART_view=detail;
		setGartTab(detail);
		
		if (detail=="description") view_detail_description();
	}

	function view_detail_description()
	{
		$(".description_label_lang").css("font-weight","normal");
		$("#description_label_lang_"+language_code).css("font-weight","bold");
		
		$(".description_lang").hide();
		$(".description_lang_"+language_code).show();

	}



	function GartAddDescription(GART, GART_Bez)
	{
		$("#GartAddDescription_lang").val(0);
		$("#GartAddDescription_keywords").val("");
		$("#GartAddDescription_description").val("");
		$("#GartAddDescription_lang").prop('disabled', false);
		
		$("#GartAddDescriptionDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { GartAddDescription_Save(GART, "add"); } },
				{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neue Meta-Daten für "+GART_Bez+" anlegen",
			width:700
		});		
	}
	
	function GartUpdateDescription(GART, GART_Bez)
	{
		if( language_code==0 ) 
		{
			alert("Bitte wählen Sie erst eine Sprache aus!");
			return;
		}
		
		lang_code=language_code;
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartGetDescription", GART:GART, lang_code:lang_code },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					var $keywords = $xml.find("keywords").text();
					$("#GartAddDescription_keywords").val($keywords);
					var $description = $xml.find("description").text();
					$("#GartAddDescription_description").val($description);

					$("#GartAddDescription_lang").val(lang_code);
					$("#GartAddDescription_lang").prop('disabled', true);
					wait_dialog_hide();
					$("#GartAddDescriptionDialog").dialog
						({	buttons:
							[
							{ text: "Speichern", click: function() { GartAddDescription_Save(GART, "update"); } },
							{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
							],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Meta-Daten für "+GART_Bez+" bearbeiten",
						width:700
					});		
				}
			}
		);
	}

	
	function GartAddDescription_Save(GART, mode)
	{
		var lang_code=$("#GartAddDescription_lang").val();
		var keywords=$("#GartAddDescription_keywords").val();
		var description=$("#GartAddDescription_description").val();

		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartAddDescription", mode:mode, GART:GART, lang_code:lang_code, description:description, keywords:keywords },
			function($data)
			{
				try { $xml = $($.parseXML($data)); }
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

				$("#GartAddDescriptionDialog").dialog("close");
				wait_dialog_hide();
				show_status("Meta-Daten gespeichert!");
				setGartLabel(GART, lang_code);
			}
		);
	}
	
	function GartDeleteDescription (GART, GART_Bez)
	{
		lang_code=language_code;
		$("#GartDeleteDescriptionDialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { GartDoDeleteDescription(GART, lang_code); } },
				{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Meta-Daten zu "+GART_Bez+" löschen?",
			width:700
		});		
	}
		
		
	function GartDoDeleteDescription (GART, lang_code)
	{
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartDeleteDescription", GART:GART, lang_code:lang_code},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					language_code=0;
					$("#GartDeleteDescriptionDialog").dialog("close");
					wait_dialog_hide();
					show_status("Meta-Daten gelöscht!");
					view("detail", GART);
				}
				else 
				{
					$("#GartDeleteDescriptionDialog").dialog("close");
					wait_dialog_hide();
					show_status2(data);
					return;
				}

			}
		);
		
	}

	
	function show_description(GART, lang_code)
	{
		if( lang_code==0 ) return;
	
		language_code=lang_code;
		
		view_detail_description()

		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartGetDescription", GART:GART, lang_code:lang_code },
			function ($data)
			{
				try { $xml = $($.parseXML($data)); }
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

				var $keywords = $xml.find("keywords").text();
				$(".gart_keywords").val($keywords);
				var $description = $xml.find("description").text();
				$(".gart_description").val($description);
			}
		);
		
	}
	
	function GartAddKeyword(GART, GART_Bez)
	{
		var $html = '';
		$html += '<table>';
		$html += '	<tr>';
		$html += '		<th>Sprache</th>';
		$html += '		<th>Schlüsselwort</th>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td><b>'+language_code+'</b></td>';
		$html += '		<td><input type="text" class="lang_keyword" size="40" id="GartAddKeyword_keyword" /></td>';
		$html += '	</tr>';
		$html += '</table>';
		if( $("#GartAddKeywordDialog").length==0 ) $("body").append('<div id="GartAddKeywordDialog" style="display:none"></div>');
		$("#GartAddKeywordDialog").html($html);

		$("#GartAddKeywordDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { GartAddKeyword_Save(GART, 0, "add"); } },
				{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neues Schlüsselwort für "+GART_Bez+" anlegen",
			width:500
		});		
		
	}

	function GartUpdateKeyword($id, $keyword, GART)
	{
		var $html = '';
		$html += '<table>';
		$html += '	<tr>';
		$html += '		<th>Schlüsselwort</th>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>';
		$html += '			<input type="text" class="lang_keyword" size="40" id="GartKeywordUpdate_keyword" value="'+$keyword+'" />';
		$html += '			<input type="hidden" id="GartKeywordUpdate_id" value="'+$id+'" />';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '</table>';
		if( $("#GartKeywordUpdateDialog").length==0 ) $("body").append('<div id="GartKeywordUpdateDialog" style="display:none"></div>');
		$("#GartKeywordUpdateDialog").html($html);

		$("#GartKeywordUpdateDialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { GartAddKeywordSave(GART); } },
					{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
				],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Schlüsselwort bearbeiten",
			width:700
		});		
	}

	
	function GartAddKeywordSave(GART)
	{
		var $id=$("#GartKeywordUpdate_id").val();
		var $keyword=$("#GartKeywordUpdate_keyword").val();

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartKeywordUpdate", id:$id, keyword:$keyword }, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$("#GartKeywordUpdateDialog").dialog("close");
			show_status("Schlüsselwort aktualisiert.");
			view("detail", GART);
		});
	}


	function GartAddKeyword_Save(GART, id_keyword, mode)
	{
		var $keyword=$("#GartAddKeyword_keyword").val();

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartKeywordAdd", mode:mode, GART:GART, keyword:$keyword, id_language:language_code}, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$("#GartAddKeywordDialog").dialog("close");
			show_status("Schlüsselwort gespeichert!");
			view("detail", GART);
		});
	}

	function GartDeleteKeyword ($id, GART)
	{
		$("#GartDeleteKeywordDialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { GartDoGartDeleteKeyword($id, GART); } },
				{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Schlüsselwort löschen?",
			width:700
		});		
	}
		
		
	function GartDoGartDeleteKeyword ($id, GART)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartKeywordRemove", id:$id }, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$("#GartDeleteKeywordDialog").dialog("close");
			wait_dialog_hide();
			show_status("Schlüsselwort gelöscht!");
			view("detail", GART);
		});
	}
	

	function GartAddImageView(GART, GART_Bez)
	{
		$("#GartAddImageView_pre_title").text(GART_Bez);
		$("#GartAddImageView_title").val("");
		$("#GartAddImageView_desc").val("");
		
		$("#GartAddImageViewDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { GartAddImageView_Save(GART, 0, "add"); } },
				{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neue Artikelansicht für "+GART_Bez+" anlegen",
			width:600
		});		
	}
	
	function GartUpdateImageView(id_view, GART_Bez)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartGetImageView", id_view:id_view },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					$("#GartAddImageView_pre_title").text("");
					$("#GartAddImageView_title").val($xml.find("title").text());
					$("#GartAddImageView_desc").val($xml.find("desc").text());

					
					wait_dialog_hide();
					$("#GartAddImageViewDialog").dialog
						({	buttons:
							[
							{ text: "Speichern", click: function() { GartAddImageView_Save(0,id_view, "update"); } },
							{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
							],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Artikelansicht für "+GART_Bez+" bearbeiten",
						width:600
					});		
				}
			}
		);
	}

	
	function GartAddImageView_Save(GART, id_view, mode)
	{
		var pre_title=$("#GartAddImageView_pre_title").text();
		var desc=$("#GartAddImageView_desc").val();
		var post_title=$("#GartAddImageView_title").val();
		if (mode=="add") {var title=pre_title+" "+post_title;}
		if (mode=="update") {var title=post_title;}

		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartAddImageView", mode:mode, GART:GART, id_view:id_view, title:title, desc:desc},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					$("#GartAddImageViewDialog").dialog("close");
					wait_dialog_hide();
					show_status("Artikelansicht gespeichert!");
					view("detail", GART);
				}
				else
				{
					$("#GartAddImageViewDialog").dialog("close");
					view("detail", GART);
					show_status2(data);
					return;
				}

			}
		);
	}
	
	function GartDeleteImageView(id_view, GART, GART_Bez)
	{
		$("#GartDeleteImageViewDialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { GartDoDeleteImageView(id_view, GART); } },
				{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Artikelansicht zu "+GART_Bez+" löschen?",
			width:700
		});		
	}
		
		
	function GartDoDeleteImageView(id_view, GART)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartDeleteImageView", id_view:id_view, GART:GART},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					wait_dialog_hide();
					$("#GartDeleteImageViewDialog").dialog("close");
					show_status("Artikelansicht gelöscht!");
					view("detail", GART);
				}
				else 
				{
					wait_dialog_hide();
					$("#GartDeleteImageViewDialog").dialog("close");
					show_status2(data);
					view("detail", GART);
					return;
				}
			}
		);
	}
//--------------------	

	function GartAddDutyNumber(GART, GART_Bez)
	{
		$("#GartAddDutyNumber").val("");
		
		$("#GartAddDutyDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { GartAddDutyNumber_Save(GART, 0, "add"); } },
				{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neue Zolltarifnummer für "+GART_Bez+" anlegen",
			width:600
		});		
		
	}
	
	function GartUpdateDutyNumber(id_DutyNumber, GART, GART_Bez)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartGetDutyNumber", id_DutyNumber:id_DutyNumber },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					$("#GartAddDutyNumber").val($xml.find("DutyNumber").text());

					
					wait_dialog_hide();
					$("#GartAddDutyDialog").dialog
						({	buttons:
							[
							{ text: "Speichern", click: function() { GartAddDutyNumber_Save(GART,id_DutyNumber, "update"); } },
							{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
							],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Zolltarifnummer für "+GART_Bez+" bearbeiten",
						width:600
					});		
				}
			}
		);
	}

	
	function GartAddDutyNumber_Save(GART, id_DutyNumber, mode)
	{
		var DutyNumber=$("#GartAddDutyNumber").val();

		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartAddDutyNumber", mode:mode, GART:GART, id_DutyNumber:id_DutyNumber, DutyNumber:DutyNumber},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					$("#GartAddDutyDialog").dialog("close");
					wait_dialog_hide();
					show_status("Zolltarifnummer gespeichert!");
					view("detail", GART);
				}
				else
				{
					$("#GartAddDutyDialog").dialog("close");
					view("detail", GART);
					show_status2(data);
					return;
				}

			}
		);
	}
	
	function GartDeleteDutyNumber(id_DutyNumber, GART, GART_Bez)
	{
		$("#GartDeleteDutyNumberDialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { GartDoDeleteDutyNumber(id_DutyNumber, GART); } },
				{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Zolltarifnummer zu "+GART_Bez+" löschen?",
			width:700
		});		
	}
		
		
	function GartDoDeleteDutyNumber(id_DutyNumber, GART)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartDeleteDutyNumber", id_DutyNumber:id_DutyNumber, GART:GART},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					wait_dialog_hide();
					$("#GartDeleteDutyNumberDialog").dialog("close");
					show_status("Zolltarifnummer gelöscht!");
					view("detail", GART);
				}
				else 
				{
					wait_dialog_hide();
					$("#GartDeleteDutyNumberDialog").dialog("close");
					show_status2(data);
					view("detail", GART);
					return;
				}
			}
		);
	}
	
	function view(view2, gart)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartView", view:view2, gart:gart, id_language:language_code },
			function(data)
			{ 
				wait_dialog_hide();
				
				if (view2 == "gart") {
					$("#gart_view").html(data);
				}
				
				if (view2 == "detail")
				{
					$("#gart_details").html(data);
					view_detail(GART_view);
					
					$(function() {
						$( "#detail_keywords" ).sortable( { items:"tr:not(.unsortable)" } );
						$( "#detail_keywords" ).sortable( { cancel:".unsortable"} );
						$( "#detail_keywords" ).sortable({cancel: ".unsortable"});
						$( "#detail_keywords" ).disableSelection();
						$( "#detail_keywords" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#detail_keywords').sortable('toArray');
							$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartSortKeyword", list:list },
							 function(data) { 
								 view(view2,gart);
							 });
						});
					});
					$(function() {
						$( "#detail_image_views" ).sortable( { items:"tr:not(.unsortable)" } );
						$( "#detail_image_views" ).sortable( { cancel:".unsortable"} );
						$( "#detail_image_views" ).sortable({cancel: ".unsortable"});
						$( "#detail_image_views" ).disableSelection();
						$( "#detail_image_views" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#detail_image_views').sortable('toArray');
							$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartSortImageViews", list:list },
							 function(data) { 
								 view(view2,gart);
							 });
						});
					});
					if(GART_view=='description') show_description(gart, language_code);
				}
				
			}
		);

	}
	
	function GartKeywordTecDocUpdate()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartKeywordTecDocUpdate"},
			function(data)
			{ 
			
//				var $xml=$($.parseXML(data));
//				var Response = $xml.find("Response").text();

				show_status2(data);
			
	//		$("#gart_details").html(data);
				wait_dialog_hide();
			}
		);
	}
	
	
</script>

<?php

	echo '<div id="gart_view" style="width:600px; height:800px; overflow:auto; display:inline; float:left;"></div>';
	echo '<script type="text/javascript">view(\'gart\', \'\');</script>';
	
	echo '<div id="gart_details" style="display:inline; float:left; padding-left:10px"></div>';
	
	//GartAddDescription DIALOG
	echo '<div id="GartAddDescriptionDialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
	echo '	<td><b>Sprache: </b>';
	echo '	<select id="GartAddDescription_lang" size="1">';
	echo '		<option value="0">Bitte eine Sprache wählen</option>';
		$res=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res)) echo '<option value="'.$row["id_language"].'">'.$row["language"].'</option>';
	echo '	</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td><b>META-KEYWORDS:</b><br />';
	echo '	<textarea id="GartAddDescription_keywords" rows="5" cols="90"></textarea></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td><b>META_DESCRIPTION:</b><br />';
	echo '	<textarea id="GartAddDescription_description" rows="15" cols="90"></textarea></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//GartDeleteDescription DIALOG
	echo '<div id="GartDeleteDescriptionDialog" style="display:none">';
	echo '<span>Wollen Sie diese Meta-Daten wirklich löschen?</span>';
	echo '</div>';
	
	//GartDeleteKeyword DIALOG
	echo '<div id="GartDeleteKeywordDialog" style="display:none">';
	echo '<span>Wollen Sie dieses Schlüsselwort wirklich löschen?</span>';
	echo '</div>';
	
	//GartAddImageView DIALOG
	echo '<div id="GartAddImageViewDialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
	echo '	<td><b>Artikelansicht Bezeichnung: </b></td>';
	echo '	<td><span id="GartAddImageView_pre_title"></span>';
	echo '	<input type="text" id="GartAddImageView_title" size="40"></td>';
	echo '</tr><tr>';
	echo '	<td><b>Bildbeschriftung: </b></td>';
	echo '	<td><input type="text" id="GartAddImageView_desc" size="40" style="float:right"></td>';
	echo '</tr>';
	echo '</table>';
	echo '<i>Beschreibungen und Bilder können im Anschluß im Beitragseditor bearbeitet werden (Link)</i>';
	echo '</div>';
	
	//GartDeleteImageView DIALOG
	echo '<div id="GartDeleteImageViewDialog" style="display:none">';
	echo '<span>Wollen Sie diese Artikelansicht wirklich löschen?</span>';
	echo '</div>';

	//GartAddDutyNumber DIALOG
	echo '<div id="GartAddDutyDialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
	echo '	<td><b>Zolltarifnummer: </b></td>';
	echo '	<td><input type="text" id="GartAddDutyNumber" size="40"></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//GartDeleteDutyNumber DIALOG
	echo '<div id="GartDeleteDutyNumberDialog" style="display:none">';
	echo '<span>Wollen Sie diese Zolltarifnummer wirklich löschen?</span>';
	echo '</div>';

	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>