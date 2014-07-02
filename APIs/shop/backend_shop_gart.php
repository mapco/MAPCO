<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
<script type="text/javascript">
	var language_code="";
	var GART_view="description";
	
	function setGartLabel(gart)
	{
		$(".GartLabel").css("font-weight","normal");
		$("#GartLabel"+gart).css("font-weight","bold");
		view("detail",gart);
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
		
		if (detail=="descrition") view_detail_description();
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
		$("#GartAddDescription_lang").val("");
		$("#GartAddDescription_description").val("");
		
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
			title:"Neue Artikelbeschreibung für "+GART_Bez+" anlegen",
			width:700
		});		
	}
	
	function GartUpdateDescription(GART, GART_Bez)
	{
		lang_code=language_code;
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartGetDescription", GART:GART, lang_code:lang_code },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					var description = $xml.find("Description").text();
					$("#GartAddDescription_description").val(description);
					$("#GartAddDescription_lang").val(lang_code);
					$("#GartAddDescription_lang").attr('disabled','disabled');
					//DISABLE #GartAddDescription_lang
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
						title:"Artikelbeschreibung für "+GART_Bez+" bearbeiten",
						width:700
					});		
				}
			}
		);
	}

	
	function GartAddDescription_Save(GART, mode)
	{
		var lang_code=$("#GartAddDescription_lang").val();
		var description=$("#GartAddDescription_description").val();

		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartAddDescription", mode:mode, GART:GART, lang_code:lang_code, description:description},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					$("#GartAddDescriptionDialog").dialog("close");
					wait_dialog_hide();
					show_status("Artikelbeschreibung gespeichert!");
					view("detail", GART);
				}
				else 
				{
					wait_dialog_hide();
					show_status2(data);
					return;
				}
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
			title:"Artikelbeschreibung zu "+GART_Bez+" löschen?",
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
					$("#GartDeleteDescriptionDialog").dialog("close");
					wait_dialog_hide();
					show_status("Artikelbeschreibung gelöscht!");
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
		language_code=lang_code;
		
		view_detail_description()

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartGetDescription", GART:GART, lang_code:lang_code },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					wait_dialog_hide();
					var description = $xml.find("Description").text();
					$(".gart_description").val(description);
				}
				else 
				{
					wait_dialog_hide();
					show_status2(data);
					return;
				}
			}
		);
		
	}
	
	function GartAddKeyword(GART, GART_Bez)
	{
		$("#GartAddKeyword_keyword_de").val("");
		$("#GartAddKeyword_keyword_en").val("");
		$("#GartAddKeyword_keyword_ru").val("");
		$("#GartAddKeyword_keyword_pl").val("");
		$("#GartAddKeyword_keyword_it").val("");
		$("#GartAddKeyword_keyword_fr").val("");
		$("#GartAddKeyword_keyword_es").val("");
		$("#GartAddKeyword_keyword_zh").val("");
		
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

	function GartUpdateKeyword(id_keyword,GART, GART_Bez)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartGetKeywords", id_keyword:id_keyword },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					$("#GartAddKeyword_keyword_de").val( $xml.find("keyword_de").text() );
					$("#GartAddKeyword_keyword_en").val( $xml.find("keyword_en").text() );
					$("#GartAddKeyword_keyword_ru").val( $xml.find("keyword_ru").text() );
					$("#GartAddKeyword_keyword_pl").val( $xml.find("keyword_pl").text() );
					$("#GartAddKeyword_keyword_it").val( $xml.find("keyword_it").text() );
					$("#GartAddKeyword_keyword_fr").val( $xml.find("keyword_fr").text() );
					$("#GartAddKeyword_keyword_es").val( $xml.find("keyword_es").text() );
					$("#GartAddKeyword_keyword_zh").val( $xml.find("keyword_zh").text() );

					wait_dialog_hide();
					
					$("#GartAddKeywordDialog").dialog
						({	buttons:
							[
							{ text: "Speichern", click: function() { GartAddKeyword_Save(GART, id_keyword, "update"); } },
							{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
							],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Schlüsselwort für "+GART_Bez+" bearbeiten",
						width:700
					});		
				} // SUCCES
				else 
				{
					wait_dialog_hide();
					show_status2(data);
					return;
				}
			}
		);		
	}

	
	function GartAddKeyword_Save(GART, id_keyword, mode)
	{
		var keyword_de=$("#GartAddKeyword_keyword_de").val();
		var keyword_en=$("#GartAddKeyword_keyword_en").val();
		var keyword_ru=$("#GartAddKeyword_keyword_ru").val();
		var keyword_pl=$("#GartAddKeyword_keyword_pl").val();
		var keyword_it=$("#GartAddKeyword_keyword_it").val();
		var keyword_fr=$("#GartAddKeyword_keyword_fr").val();
		var keyword_es=$("#GartAddKeyword_keyword_es").val();
		var keyword_zh=$("#GartAddKeyword_keyword_zh").val();

		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartAddKeyword", mode:mode, GART:GART, id_keyword:id_keyword, keyword_de:keyword_de, keyword_en:keyword_en, keyword_ru:keyword_ru, keyword_pl:keyword_pl, keyword_it:keyword_it, keyword_fr:keyword_fr, keyword_es:keyword_es, keyword_zh:keyword_zh},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					$("#GartAddKeywordDialog").dialog("close");
					wait_dialog_hide();
					show_status("Schlüsselwort gespeichert!");
					view("detail", GART);
				}
			}
		);
	}

	function GartDeleteKeyword (id_keyword, GART, GART_Bez)
	{
		$("#GartDeleteKeywordDialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { GartDeleteKeyword(id_keyword, GART); } },
				{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Schlüsselwort zu "+GART_Bez+" löschen?",
			width:700
		});		
	}
		
		
	function GartDoGartDeleteKeyword (id_keyword, GART)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartDeleteKeyword", id_keyword:id_keyword},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")
				{
					$("#GartDeleteKeywordDialog").dialog("close");
					wait_dialog_hide();
					show_status("Schlüsselwort gelöscht!");
					view("detail", GART);
				}
			}
		);
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
	
	function GartDeleteImageView(id_view, GART_Bez)
	{
		$("#GartDeleteImageViewDialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { GartDoDeleteImageView(id_view); } },
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
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "GartDeleteImageView", id_view:id_view},
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
	
	function view(view2, gart)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"GartView", view:view2, gart:gart },
			function(data)
			{ 
				
				if (view2 == "gart") {
					$("#gart_view").html(data);
				}
				
				if (view2 == "detail") {
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

				}
				
				wait_dialog_hide();
			}
		);

	}
	
	
	
</script>

<?php

	echo '<div id="gart_view" style="width:310px; display:inline; float:left;"></div>';
	echo '<script type="text/javascript">view(\'gart\', \'\');</script>';
	
	echo '<div id="gart_details" style="display:inline; float:left; padding-left:10px"></div>';
	
	//GartAddDescription DIALOG
	echo '<div id="GartAddDescriptionDialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
	echo '	<td><b>Sprache: </b>';
	echo '	<select id="GartAddDescription_lang" size="1">';
	echo '		<option value="">Bitte eine Sprache wählen</option>';
		$res=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res)) echo '<option value="'.$row["code"].'">'.$row["language"].'</option>';
	echo '	</select></td>';
	echo '</tr><tr>';
	echo '	<td><b>Artikelbeschreibung:</b><br />';
	echo '	<textarea id="GartAddDescription_description" rows="15" cols="90"></textarea></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//GartDeleteDescription DIALOG
	echo '<div id="GartDeleteDescriptionDialog" style="display:none">';
	echo '<span>Wollen Sie diese Artikelbeschreibung wirklich löschen?</span>';
	echo '</div>';
	
	//GartAddKeyword DIALOG
	echo '<div id="GartAddKeywordDialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
	echo '	<th>Sprache</th>';
	echo '	<th>Schlüsselwort</th>';
	echo '</tr>';
	$res=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res)) 
	{
	echo '<tr>';
	echo '	<td><b>'.$row["language"].'</b></td>';
	echo '	<td><input type="text" class="lang_keyword" size="40" id="GartAddKeyword_keyword_'.$row["code"].'" /></td>';
	echo '</tr>';
	}
	echo '</table>';
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
	
	//GartImageView DIALOG
	echo '<div id="GartDeleteImageViewDialog" style="display:none">';
	echo '<span>Wollen Sie diese Artikelansicht wirklich löschen?</span>';
	echo '</div>';

	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>