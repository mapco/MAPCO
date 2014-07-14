<?php
	/******Author Sven E.******/
	/****Lastmod 26.03.2014****/

	include("../../../config.php");
	include("../../../functions/cms_t.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

var translations = new Array();

function load_view_generic()
{	
	wait_dialog_show('Lese Artikeldetails aus', 0);
	article_id = $("#editor_tabs").attr('article');
	type = $("#editor_tabs").attr('type');
	language_id = $("#editor_tabs").attr('language_id');
	
	$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleDetailsGet", article_id:article_id, type:type, lang:language_id }, function($data)
	{ 
		//show_status2($data); return;
		try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
		if($xml.find('Ack').text()!='Success'){show_status2($data);return;}

		wait_dialog_show('Lese Artikeldetails aus', 25);
		var header_div = '<h1>'+$xml.find('article_title').text()+'</h1>';
		$("#editor_header").empty().append(header_div); 

		var article_generic_content = '<h2 style="padding-left:10px; border-bottom:0;">Beitrags-Editor - Allgemein</h2>';
		article_generic_content += '<div style="padding:10px;">';

		// Stichworte
		article_generic_content += '	<div style="min-height:60px; padding-top:5px;">';
		article_generic_content += '		<h4>Stichworte</h4>';
		article_generic_content += '		<div id="article_labels" style="display:inline;">';
		$xml.find('article_labels').each(function(){
			$label_id = $(this).find('id_label').text();
			article_generic_content += '		<div id="label_'+$label_id+'" class="label">';
			article_generic_content += $(this).find('label').text();
			article_generic_content += '			<a alt="Stichwort löschen" title="Stichwort löschen" href="#" class="button_remove_label" value="'+$label_id+'">x</a>';
			article_generic_content += '		</div>';
		});
		article_generic_content += '		</div>';
		article_generic_content += '		<div style="display:inline; float:right;">';
		article_generic_content += '			<select id="article_label" style="width:200px;">';
		if($xml.find('label_list_label').length != 0)
		{
			$xml.find('label_list_label').each(function(){
				article_generic_content += '			<option value='+$(this).find('id_label').text()+'>'+$(this).find('title').text()+'</option>';	
			});
		}
		article_generic_content += '			</select>';
		article_generic_content += '			<button id="button_add_label" style="width:180px;">Stichwort hinzufügen</button>';
		article_generic_content += '		</div>';
		article_generic_content += '		<br style="clear:both;">';
		article_generic_content += '	</div>';
		article_generic_content += '	<hr>';
		wait_dialog_show('Ermittle Stichworte', 30);		
		
		// Shopartikel
		article_generic_content += '	<div style="min-height:60px; padding-top:5px;">';
		article_generic_content += '		<h4>Shopartikel</h4>';
		article_generic_content += '		<div id="article_shopitems">';
		$xml.find('article_shopitems').each(function(){
			article_generic_content += '			<div id="shopitem_'+$(this).find('shoparticle_id').text()+'" class="label">';
			article_generic_content += $(this).find('shoparticle_title').text();
			article_generic_content += '				<a alt="Shopartikel löschen" title="Shopartikel löschen" href="#" class="button_remove_shopitem" value="'+$(this).find('shoparticle_id').text()+'">x</a>';
			article_generic_content += '			</div>';
		});
		article_generic_content += '		</div>';
		article_generic_content += '		<div style="display:inline; float:right;">';
		article_generic_content += '			<input id="input_shopitem" type="text" style="width:195px;" />';
		article_generic_content += '			<button id="button_add_shopitem" style="width:180px;">Shopartikel hinzufügen</button>';
		
		<?php if ($_SESSION["id_user"] == 87921)
		{ ?>    
			article_generic_content += '			<button id="button_add_shop_item" style="width:180px;">Shopartikel hinzufügen Prompt</button>';
		<?php } ?>		
		article_generic_content += '		</div>';
		article_generic_content += '		<br style="clear:both;">';
		article_generic_content += '	</div>';
		article_generic_content += '	<hr>';
		
		wait_dialog_show('Ermittle generische Artikel', 50);
		// Generische Artikel
		article_generic_content += '	<div style="min-height:60px; padding-top:5px;">';
		article_generic_content += '		<h4>Generische Artikel</h4>';
		article_generic_content += '		<div id="article_garts">';
		$xml.find('gart_articles').each(function(){
		$gart_id = $(this).find('gart_id').text();
		article_generic_content += '		<div id="gart_'+$gart_id+'" class="label">';
		article_generic_content += 				$(this).find('gart_bez').text();
		article_generic_content += '			<a alt="Stichwort löschen" title="Stichwort löschen" href="#" class="button_remove_gart" value="'+$gart_id+'">x</a>';
		article_generic_content += '		</div>';
	});
		article_generic_content += '		</div>';
	
		article_generic_content += '		<div style="display:inline; float:right; padding-bottom:10px;">';
		article_generic_content += '			<select id="article_GART_add" style="width:380px;">';
		article_generic_content += '				<option value="">Bitte generische Artikelbezeichnung wählen</option>';
		$xml.find('gart_option').each(function(){
			article_generic_content += '			<option value='+$(this).find('option_value').text()+'>'+$(this).find('option_text').text()+'</option>';
		});
		article_generic_content += '			</select>';
		article_generic_content += '			<br style="clear:both;" />';
		article_generic_content += '			<button id="button_add_gart" style="width:380px;">generische Artikelbezeichnung hinzufügen</button>';
		article_generic_content += '		</div>';
		article_generic_content += '		<br style="clear:both;">';
		article_generic_content += '	</div>';
		article_generic_content += '	<hr>';			
		wait_dialog_show('Ermittle generische Artikel', 60);
		
		//published
		article_generic_content += '	<div style="height:30px; padding-top:10px;">';
		article_generic_content += '		<div style="display:inline;">';
		article_generic_content += '			<h4 style="display:inline;">Veröffentlicht</h4>';						
		article_generic_content += '			<select id="article_published" style="display:inline;">';
		if($xml.find("article_published").text() == 0)
		{
			article_generic_content += '				<option value=0 selected="selected">Nein</option>';
			article_generic_content += '				<option value=1>Ja</option>';
		}
		else if($xml.find("article_published").text() == 1)
		{
			article_generic_content += '				<option value=0>Nein</option>';
			article_generic_content += '				<option value=1 selected="selected">Ja</option>';
		}
		article_generic_content += '			</select>';
		article_generic_content += '		</div>';
		
		// Artikelformat
		article_generic_content += '		<h4 style="display:inline; margin-left:15%;">Artikelformat</h4>';
		article_generic_content += '		<select id ="article_formation" style="display:inline;">';
		if($xml.find("article_format").text() == 0)
		{
			article_generic_content += '		<option value=0 selected="selected">Text</option>';
			article_generic_content += '		<option value=1>HTML</option>';	
		}
		else
		{
			article_generic_content += '		<option value=0>Text</option>';
			article_generic_content += '		<option value=1 selected="selected">HTML</option>';
		}
		article_generic_content += '		</select>';
			
		// Bildprofil
		article_generic_content += '	<div style="display:inline; float:right;">';
		article_generic_content += '			<h4 style="display:inline;">Bildprofil</h4>';
		article_generic_content +='				<select id ="article_imageprofile_id" style="display:inline;">';
		article_generic_content += '				<option value=0>kein Bildprofil ausgewählt</option>';
		$xml.find("imageprofile_option").each(function(){	
		$option_value = $(this).find('option_value').text();
			article_generic_content += '				<option value='+$option_value;
			if( $xml.find("imageprofile_id").text() == $option_value )
			{
				article_generic_content += '  selected="selected"';
			}
			article_generic_content += '>'+$(this).find('option_text').text()+'</option>';
		});			
		article_generic_content += '			</select>';
		article_generic_content += '		</div>';
		article_generic_content += '		<br style="clear:both;">';
		article_generic_content += '	</div>';
		
		wait_dialog_show('Suche nach Übersetzungen', 80);
		// Language
		article_generic_content += '	<div id="Language_tabs" class="ui-tabs ui-widget" style="border:solid 1px;" active_translation="'+article_id+'" active_language="1">';
		article_generic_content += '		<ul id="tabs_language_ul" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">';
		
		$xml.find("languages").each(function(){
			var translation_id = $(this).find('translation_id').text();
			$translation_language_id = $(this).find('language_id').text();
			translations[$translation_language_id] = new Array();

			article_generic_content += '		<li class="ui-corner-top';
			if(translation_id != 0) { article_generic_content += ' ui-state-default ';  }
			if (language_id==$translation_language_id) { article_generic_content += ' ui-tabs-active ui-state-active'; aria_selected = 'true'} else { aria_selected = 'false' }			
			article_generic_content += '" role="tab" tabindex="0" aria-controls="tab-general" aria-labelledby="ui-id-1" aria-selected="'+aria_selected+'">';
			article_generic_content += '<a href="#tab-general" id="translation_tab_'+$translation_language_id+'" style="cursor:pointer;';
			article_generic_content += '" class="ui-tabs-anchor translation_tab';
			
			article_generic_content += '" value="'+translation_id+'" role="presentation" tabindex="-1" id="ui-id-1" >';
			article_generic_content += $(this).find('language_text').text()+'</a>';
			article_generic_content += '		</li>';

			translations[$translation_language_id]['translation_id'] = translation_id;
			if ( translation_id > 0 )
			{
				translations[$translation_language_id]['translation_published'] = $(this).find('translation_published').text();
			}
		});
		article_generic_content += '		</ul>';
//		show_status2(print_r(translations));
		
		translations[language_id]['article_meta_title'] = $xml.find('article_meta_title').text();
		translations[language_id]['article_meta_keywords'] = $xml.find('article_meta_keywords').text();
		translations[language_id]['article_meta_description'] = $xml.find('article_meta_description').text();
		translations[language_id]['article_title'] = $xml.find('article_title').text();
		translations[language_id]['article_introduction'] = $xml.find('article_introduction').text();
		translations[language_id]['article_text'] = $xml.find('article_text').text();
		
		wait_dialog_show('Erstelle Oberfläche', 90);
		// Title, Introduction and Text
		article_generic_content += '		<div id="editor_translation_texts" style="padding:10px;">';
		article_generic_content += '			<div id="editor_translation_published"></div>';
		article_generic_content += '			<ul id="tabs_textfields" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">';				
		article_generic_content += '				<li id="tab_textfields_text" class="ui-corner-top ui-state-default ui-tabs-active ui-state-active" role="tab" tabindex="0" aria-controls="tab-general" aria-labelledby="ui-id-1" aria-selected="true">';
		article_generic_content += '					<a href="#tab-general" id="textfield_tab_text" style="cursor:pointer;" class="ui-tabs-anchor textfield_tab" value="text" role="presentation" tabindex="-1" id="ui-id-1" >Texte</a>';
		article_generic_content += '				</li>';
		article_generic_content += '				<li id="tab_textfields_meta" class="ui-corner-top ui-state-default" role="tab" tabindex="0" aria-controls="tab-general" aria-labelledby="ui-id-2" aria-selected="false">';
		article_generic_content += '					<a href="#tab-general" id="textfield_tab_text" style="cursor:pointer;" class="ui-tabs-anchor textfield_tab" value="meta" role="presentation" tabindex="-2" id="ui-id-2" >Metadaten</a>';
		article_generic_content += '				</li>';
		article_generic_content += '			</ul>';
		article_generic_content += '			<div id="article_meta_fields" style="display:none;">';
		article_generic_content += '				<h4 style="padding-top:5px; width:72px;">Meta Title<img height="10px" src="<?php echo PATH; ?>images/icons/16x16/help.png" title="Wird in der Programmzeile angezeigt. Nicht mehr als 70 Zeichen" style="float:right;"></h4>';
		article_generic_content += '				<input type="text" id="article_meta_title" style="width:100%;" value="'+translations[language_id]['article_meta_title']+'" />';
		article_generic_content += '				<h4 style="padding-top:5px; width:105px;">Meta Keywords<img height="10px" src="<?php echo PATH; ?>images/icons/16x16/help.png" title="Schlüsselwörter für Google Adwords. Maximal 20 Stichworte, weniger ist mehr." style="float:right;"></h4>';
		article_generic_content += '				<textarea id="article_meta_keywords" style="width:100%; height:75px;">'+translations[language_id]['article_meta_keywords']+'</textarea>';
		article_generic_content += '				<h4 style="padding-top:5px; width:115px;">Meta Description<img height="10px" src="<?php echo PATH; ?>images/icons/16x16/help.png" title="Wird in den Google-Suchergebnissen als Beschreibungstext dargestellt." style="float:right;"></h4>';
		article_generic_content += '				<textarea id="article_meta_description" style="width:100%; height:150px;">'+translations[language_id]['article_meta_description']+'</textarea>';
		article_generic_content += '			</div>';
		article_generic_content += '			<div id="article_text_fields">';
		article_generic_content += '				<h4 style="padding-top:5px;">Titel</h4>';
		article_generic_content += '				<input type="text" id="article_title" style="width:100%;" value="'+translations[language_id]['article_title']+'" />';
		article_generic_content += '				<h4 style="padding-top:5px;">Einleitung</h4>';
		article_generic_content += '				<textarea id="article_intro" style="width:100%; height:100px;">'+translations[language_id]['article_introduction']+'</textarea>';
		article_generic_content += '				<h4 style="padding-top:5px;">Text</h4>';
		article_generic_content += '<img id="button_mark_new_page" title="Neue Seite markieren" style="margin:5px; float:left;" src="images/icons/16x16/page_next.png" alt="Neue Seite markieren" title="Neue Seite markieren" />';
		article_generic_content += '<img id="button_add_videobox" title="Videobox einfügen" style="margin:5px;float:left;" src="images/icons/16x16/movie_track.png" alt="Videobox einfügen" title="Videobox einfügen" />';
		article_generic_content += '				<textarea id="article_text" style="width:100%; height:500px;">'+translations[language_id]['article_text']+'</textarea>';
		article_generic_content += '			</div>';
		article_generic_content += '		</div>';
		
		// Speichern Button
		article_generic_content += '		<div id="save_button_container">';
		article_generic_content += '			<button id="button_save" style="margin-left:10px; margin-bottom:10px;">Speichern</button>';
		article_generic_content += '		</div>';
		article_generic_content += '	</div>';
		article_generic_content += '</div>';
				
		wait_dialog_show('Erstelle Oberfläche', 100);
		$("#article_editor_content").empty().append(article_generic_content);
		
		$(".textfield_tab").click(function(){
			switch_meta_text($(this).attr('value'));
		});		

		$("#button_mark_new_page").click(function(){
			var var1 = "article_text";
			var var2 = "<!-- Newpage -->";
			insertAtCaret(var1, var2);
		});

		$("#button_add_videobox").click(function(){
			var var1 = "article_text";
			var var2 = "<!-- Videobox -->";
			insertAtCaret(var1, var2);
		});

		$(".button_remove_label").click(function(){
			remove_label($( this ).attr( 'value' ) );
		});
		
		$(".button_remove_shopitem").click(function(){
			remove_shopitem($( this ).attr( 'value' ) );
		});

		$(".button_remove_gart").click(function(){
			remove_gart($( this ).attr( 'value' ) );
		});

		$(".translation_tab").click(function(){
			var id = $( this ).attr( 'id' );
			var lang = id.split("_");
			show_translation($( this ).attr( 'value' ),lang[2]);
		});
		
		$("#button_add_label").click(function(){
			add_label();
		});
		$("#button_add_shopitem").click(function(){
			add_shopitem();
		});		
		$("#button_add_gart").click(function(){
			add_gart();
		});
		$("#button_save").click(function(){
			save('save');
		});
		$("#article_imageprofile_id").change(function(){
			save('imgprfl');
		});
		$("#article_formation").change(function(){
			save('format');
		});
		$("#article_languages").change(function(){
			save('lang');
		});
		$("#article_published").change(function(){
			save('publish');
		});
		
		$("#button_add_shop_item").click(function(){
			dialog_prompt_shopitem();
		});	
		
		wait_dialog_hide();
	});
}

function switch_meta_text(tab)
{	
	if ( tab == 'meta' )
	{
		$("#tab_textfields_text").removeClass('ui-tabs-active ui-state-active');
		$("#tab_textfields_meta").addClass('ui-tabs-active ui-state-active');
		$("#article_meta_fields").css('display','');
		$("#article_text_fields").css('display','none');
	}
	else if ( tab == 'text' )
	{
		$("#tab_textfields_meta").removeClass('ui-tabs-active ui-state-active');
		$("#tab_textfields_text").addClass('ui-tabs-active ui-state-active');
		$("#article_meta_fields").css('display','none');
		$("#article_text_fields").css('display','');
	}
}

function dialog_prompt_shopitem()
{
	if ($("#dialog_prompt_shopitem").length == 0)
	{
		var dialog_div = $('<div id="dialog_prompt_shopitem"></div>');
		$("#content").append(dialog_div);
	}
	$("#dialog_prompt_shopitem").empty().append('<input type="text" id="prompt_shop_item"></input>');
	
	$("#dialog_prompt_shopitem").dialog({	
		buttons:
		[
			{ text: "<?php echo t("OK"); ?>", click: function() { add_shop_item(); $(this).dialog("close");	} },
			{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
		],
		closeText:"<?php echo t("Fenster schließen"); ?>",
		hide: { effect: 'drop', direction: "up" },
		modal:true,
		resizable:false,
		show: { effect: 'drop', direction: "up" },
		title:"<?php echo t("Bestätigung"); ?>",
		width:300
	});	
}

function add_shop_item()
{
	alert($("#prompt_shop_item").val());	
}

function remove_shopitem(art_shop_id)
{		
	var $callback = function(){
		wait_dialog_show('Entferne Shopartikel',0);
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleShopitemRemoveSoa2",  art_shop_id:art_shop_id }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			if($xml.find('Error').length==0)
			{
				$("#shopitem_"+art_shop_id).remove();
			}
			else
			{
				dialog_notify($xml.find('Error').text());
			}
			wait_dialog_show('Entferne Shopartikel', 100);
			wait_dialog_hide();
		});	
	}
	dialog_confirm("Soll der Shopartikel wirklich gelöscht werden?",$callback);
}

function add_shopitem()
{
	if($("#input_shopitem").val() !='')
	{
		var $callback = function(){
			var shopitem = $("#input_shopitem").val();
		
			wait_dialog_show('Füge Shopartikel hinzu', 0);
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleShopitemAddSoa2",  article_id:$("#editor_tabs").attr('article'), text:shopitem }, function($data)
			{ 
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				if($xml.find('Error').length==0)
				{
					var insert_id = $xml.find('insert_id').text();
					var item_title = $xml.find('item_title').text();
					
					var added_shopitem = '<div id="shopitem_'+insert_id+'" class="label">';
					added_shopitem += item_title+'<a alt="Shopartikel löschen" title="Shopartikel löschen" href="#" class="button_remove_shopitem" value="'+insert_id+'">x</a>';
					added_shopitem += '</div>';
				
					$("#article_shopitems").append(added_shopitem);
					$(".button_remove_shopitem").click(function(){
						remove_shopitem($( this ).attr( 'value' ));
					});
					
					wait_dialog_show('Füge Shopartikel hinzu', 100);
				}
				else
				{
					dialog_notify($xml.find('Error').text());
				}
				wait_dialog_hide();
			});
		}
		dialog_confirm('Diesen Shopartikel hinzufügen?', $callback);
	}
	else
	{
		dialog_notify('Bitte ID des Shopartikels eingeben.');	
	}
}

function remove_gart(article_gart_id)
{
	var $callback = function(){
		wait_dialog_show('Entferne Generischen Artikel', 0);
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleGARTRemoveSoa2",  article_gart_id:article_gart_id }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
			if($xml.find('Error').length==0)
			{
				wait_dialog_show('Entferne Generischen Artikel', 100);
				$("#gart_"+article_gart_id).remove();
			}
			else
			{
				dialog_notify($xml.find('Error').text());
			}
			wait_dialog_hide();
		});	
	}
	dialog_confirm("Diesen generischen Artikel löschen?",$callback);
}

function add_gart()
{
	var $callback=function(){
		wait_dialog_show('Füge generischen Artikel in Datenbank hinzu', 0);
		var gart_id = $("#article_GART_add :selected").val();
		var gart_bez = $("#article_GART_add :selected").text();		
		
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleGARTAddSoa2",  article_id:$("#editor_tabs").attr('article'), gart_id:gart_id }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
			if($xml.find('Error').length==0)
			{
				var insert_id = $xml.find('insert_id').text();
				var added_gart = '<div id="gart_'+insert_id+'" class="label">';
				added_gart += gart_bez+'<a alt="Stichwort löschen" value="'+insert_id+'" class="button_remove_gart" title="Stichwort löschen" href="#">x</a>';
				added_gart += '</div>';
				
				wait_dialog_show('Füge generischen Artikel in Ansicht hinzu', 100);
				$("#article_garts").append(added_gart);
				
				$(".button_remove_gart").click(function(){
					remove_gart($( this ).attr( 'value' ));
				});
			}
			else
			{
				dialog_notify($xml.find('Error').text());
			}
			wait_dialog_hide();
		});	
	}
	dialog_confirm("Soll der gernerische Artikel hinzugefügt werden?",$callback);
}

function remove_label(art_label_id)
{
	var $callback=function(){
		wait_dialog_show('Entferne Stichwort aus Datenbank', 0);
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleLabelRemove",  art_label_id:art_label_id }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			wait_dialog_show('Füge generischen Artikel in Datenbank hinzu', 0);
			$("#label_"+art_label_id).remove();
			wait_dialog_hide();
		});	
	}
	dialog_confirm("Soll das Stichwort wirklich gelöscht werden?",$callback);
}

function add_label()
{
	var $callback=function(){
		var label_id = $("#article_label :selected").val();
		var label = $("#article_label :selected").text();
		
		if ( $("#path_link_label").html() == '' )
		{	
			var path_link_label = ' > <a href="backend_cms_articles.php?lang=<?php print $_GET["lang"]; ?>&id_label='+label_id+'">'+label+'</a>';
			$("#path_link_label").empty().html(path_link_label);
		}
		
		wait_dialog_show('Füge Stichwort hinzu');
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleLabelAdd",  article_id:$("#editor_tabs").attr('article'), label_id:label_id }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			var insert_id = $xml.find('insert_id').text();
			var added_label = '<div id="label_'+insert_id+'" class="label">';
			added_label += label+'<a alt="Stichwort löschen" class="button_remove_label" value="'+insert_id+'" title="Stichwort löschen" href="#">x</a>';
			added_label += '</div>';
			
			$("#article_labels").append(added_label);
			$(".button_remove_label").click(function(){
				remove_label($( this ).attr( 'value' ));
			});
			wait_dialog_hide();
		});
	}
	dialog_confirm("Soll das Stichwort hinzugefügt werden?",$callback);
}

function save(todo)
{ 
	var message = '';
	switch(todo)
	{
		case "save":	message = 'Textänderungen übernehmen?';
						break;
		case "imgprfl":	message = 'Bildprofil ändern?';
						break;
		case "publish":	message = 'Artikel veröffentlichen?';
						break;
		case "format":	message = 'Artikelformat ändern?';
						break;
	}

	var $callback = function(){
		var meta_title = '';
		var meta_keywords = '';
		var meta_description = '';
		var title = '';
		var introduction = '';
		var text = '';
		var published = 0;
		var language = 1;
		var imageprofile = 0;
		var format = 0;
	
		switch(todo)
		{
			case "save":	title = $("#article_title").val();
							introduction = $("#article_intro").val();
							text = $("#article_text").val();
							meta_title = $("#article_meta_title").val();
							meta_keywords = $("#article_meta_keywords").val();
							meta_description = $("#article_meta_description").val();
							break;
			case "imgprfl":	imageprofile = $("#article_imageprofile_id").val();
							break;
			case "publish":	published = $("#article_published").val();
							break;
			case "format":	format = $("#article_formation").val();
							break;
		}
		
		wait_dialog_show('Speichere Änderungen');
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleDetailsSave", todo:todo, id_article:$("#editor_tabs").attr('article'), title:title, introduction:introduction, text:text, published:published, imageprofile:imageprofile, format:format, meta_title:meta_title, meta_keywords:meta_keywords, meta_description:meta_description }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			wait_dialog_hide();
		});
	}
	dialog_confirm(message,$callback);
}

function save_translation(translation_id, language_id)
{
	var message = 'Übersetzung speichern?';

	$callback = function(){
		var article_id = $("#editor_tabs").attr('article');
			
		var published = 0;
		if ( $("#translation_published").prop("checked" ) == true )
		{
			published = 1;			
		}
		var title = $("#article_title").val();
		var introduction = $("#article_intro").val();
		var text = $("#article_text").val();
		var meta_title = $("#article_meta_title").val();
		var meta_keywords = $("#article_meta_keywords").val();
		var meta_description = $("#article_meta_description").val();
	
		wait_dialog_show('Speichere Übersetzung');
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleTranslationSave", lang:language_id, translation_id:translation_id, article_id:article_id, title:title, introduction:introduction, text:text, published:published, meta_title:meta_title, meta_keywords:meta_keywords, meta_description:meta_description }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			wait_dialog_hide();
			
			if ( translation_id == 0)
			{ 
				var insert_id = $xml.find('insert_id').text();
				$("#translation_tab_"+language_id).attr('value', insert_id);
			}
		}); 
	}
	dialog_confirm(message,$callback);
}

function show_translation(translation_id, language_id)
{ 
	// schreibe Daten aus den Textfeldern der aktuellen Ansicht in das Übersetzungsarray
	$active_language = $("#Language_tabs").attr('active_language');
//	alert($active_language);
//	show_status2(print_r(translations));
	translations[$active_language]['article_meta_title'] = $("#article_meta_title").val();
	translations[$active_language]['article_meta_keywords'] = $("#article_meta_keywords").val();
	translations[$active_language]['article_meta_description'] = $("#article_meta_description").val();
	translations[$active_language]['article_title'] = $("#article_title").val();
	translations[$active_language]['article_introduction'] = $("#article_intro").val();
	translations[$active_language]['article_text'] = $("#article_text").val();
//show_status2(print_r(translations));


	var article_id = $("#editor_tabs").attr('article');
	var created_article = false;

	var $callback=function(){
		var z = "#translation_tab_"+language_id; 
		if ( translation_id == 0 )
		{ 
			created_article = true;
			$local_language = $("#editor_tabs").attr('active_tab');
			$(z).parent().addClass('ui-state-default');

			wait_dialog_show('Erstelle Artikel');
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleTranslationSave", translation_id:0, article_id:article_id, lang:language_id, meta_title:'', meta_keywords:'', meta_description:'', title:'', introduction:'', text:'', published:0 }, function($data)
			{
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				translation_id = $xml.find('insert_id').text();
				$( "#translation_tab_"+language_id ).attr( 'value' , translation_id );

				$("#article_meta_title").val($xml.find('meta_title').text());
				$("#article_meta_keywords").val($xml.find('meta_keywords').text());
				$("#article_meta_description").val($xml.find('meta_description').text());
				$("#article_title").val($xml.find('title').text());
				$("#article_intro").val($xml.find('introduction').text());
				$("#article_text").val($xml.find('article').text());

				translations[language_id] = new Array();
				
				$("#tabs_language_ul").children().removeClass('ui-tabs-active ui-state-active');
				$(z).parent().addClass('ui-tabs-active ui-state-active');
				
				$("#Language_tabs").attr('active_translation', translation_id);
				$("#Language_tabs").attr('active_language', language_id);
				
				var checkbox_published = '<h3>Übersetzung - '+$("#translation_tab_"+language_id).text()+'</h3>';
				
				checkbox_published += ' <input id="translation_published" type="checkbox" value="0"'; 
			
				checkbox_published += '> <label>Übersetzung veröffentlicht</label>';
				$("#editor_translation_published").empty().append(checkbox_published);
				$button_text = 'Übersetzung speichern';
				$("#save_button_container").html('<button id="button_save" style="margin-left:10px; margin-bottom:10px;">'+$button_text+'</button>');
				$("#button_save").click(function(){
					save_translation(translation_id, language_id );
				});
				
			//	alert(translation_id);
				wait_dialog_hide();			
			});			
		}
		else
		{
			$("#tabs_language_ul").children().removeClass('ui-tabs-active ui-state-active');
			$(z).parent().addClass('ui-tabs-active ui-state-active');
			
			if ( typeof(translations[language_id]['article_title']) != "undefined" )
			{  
				//show_status2( print_r(translations) );
				// schreibe Daten der neuen Ansicht aus dem Übersetzungsarray in die Textfelder
				$("#article_meta_title").val(translations[language_id]['article_meta_title'])
				$("#article_meta_keywords").val(translations[language_id]['article_meta_keywords']);
				$("#article_meta_description").val(translations[language_id]['article_meta_description']);
				$("#article_title").val(translations[language_id]['article_title']);
				$("#article_intro").val(translations[language_id]['article_introduction']);
				$("#article_text").val(translations[language_id]['article_text']);
				
				var tab_id = "#translation_tab_"+language_id;
				var checkbox_published = '<h3>Übersetzung - '+$(tab_id).text()+'</h3>';
				$translation_published = $xml.find('published').text();
				checkbox_published += ' <input id="translation_published" type="checkbox" value="'+$translation_published+'"'; 
				
				if ( translations[language_id]['translation_published'] == 1 )
				{
					checkbox_published += '	checked="checked"';
				}
				
				checkbox_published += '> <label>Übersetzung veröffentlicht</label>';
				if ( language_id > 1 )
				{
					$("#editor_translation_published").empty().append(checkbox_published);
					$button_text = 'Übersetzung speichern';
				}
				else
				{
					$("#editor_translation_published").empty();
					$button_text = 'Hauptartikel speichern';
				}
			
				$("#save_button_container").html('<button id="button_save" style="margin-left:10px; margin-bottom:10px;">'+$button_text+'</button>');
				
				$("#button_save").click(function(){
					if ( language_id > 1)
					{	
						save_translation(translation_id, language_id );
					}
					else
					{
						save('save');
					}
				});
				
				$("#Language_tabs").attr('active_translation', translation_id);
				$("#Language_tabs").attr('active_language', language_id);
			}
			else
			{ 
				if ( created_article == true )
				{
					l_translation_id = article_id;
				}
				else
				{
					l_translation_id = translation_id;
				}
				
				wait_dialog_show('Lese Übersetzungen');
				$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleTranslationLoad", translation_id:l_translation_id }, function($data)
				{
					//show_status2($data); return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
					
					var tab_id = "#translation_tab_"+language_id;
					var checkbox_published = '<h3>Übersetzung - '+$(tab_id).text()+'</h3>';
					$translation_published = $xml.find('published').text();
					checkbox_published += ' <input id="translation_published" type="checkbox"><label>Übersetzung veröffentlicht</label>';
					if (language_id >1)
					{
						$("#editor_translation_published").empty().append(checkbox_published);
						$button_text = 'Übersetzung speichern';
					}
					else
					{
						$("#editor_translation_published").empty();
						$button_text = 'Hauptartikel speichern';
					}
			
					if ( parseInt($xml.find('published').text()) == 1 )
					{
						$("#translation_published").prop("checked", true);
					}
					else
					{
						$("#translation_published").prop("checked", false);
					}
			
					translations[language_id]['article_meta_title'] = $xml.find('meta_title').text();
					translations[language_id]['article_meta_keywords'] = $xml.find('meta_keywords').text();
					translations[language_id]['article_meta_description'] = $xml.find('meta_description').text();
					translations[language_id]['article_title'] = $xml.find('title').text();
					translations[language_id]['article_introduction'] = $xml.find('introduction').text();
					translations[language_id]['article_text'] = $xml.find('article').text();
					
					$("#article_meta_title").val(translations[language_id]['article_meta_title']);
					$("#article_meta_keywords").val(translations[language_id]['article_meta_keywords']);
					$("#article_meta_description").val(translations[language_id]['article_meta_description']);
					$("#article_title").val(translations[language_id]['article_title']);
					$("#article_intro").val(translations[language_id]['article_introduction']);
					$("#article_text").val(translations[language_id]['article_text']);
	
					$("#save_button_container").empty().append('<button id="button_save" style="margin-left:10px; margin-bottom:10px;">'+$button_text+'</button>');
					
					$("#button_save").click(function(){
						if ( language_id > 1)
						{	
							save_translation(translation_id, language_id );
						}
						else
						{
							save('save');
						}
					});
				
					$("#Language_tabs").attr('active_translation', translation_id);
					$("#Language_tabs").attr('active_language', language_id);
	
					wait_dialog_hide();
				});
			}
		}
	}
	
	if ( translation_id == 0 )
	{
		dialog_confirm('Noch keine Übersetzung angelegt. Jetzt eine erstellen?',$callback);
	}
	else
	{
		$callback();
	}
} 