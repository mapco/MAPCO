<?php
	/***** Author Sven E. *****/
	/*** Lastmod 26.03.2014 ***/

	include("../../../config.php");
	include("../../../functions/cms_t.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	window.onkeydown = function (e)
	{
		if (!e) var e=window.event; //FireFox
		if(window.event.ctrlKey && window.event.keyCode==83)
		{
			if ($("#editor_tabs").attr('active_tab') == "menu_tab_1")
			{
				event.cancelBubble = true;
				event.returnValue = false;
				if ($("#Language_tabs").attr('active_language') == "1")
				{		
					save('save');
				}
				else
				{
					save_translation($("#Language_tabs").attr('active_translation'), $("#Language_tabs").attr('active_language') );
				}				
			}
		}
	}
	
	function start_editor(article_id, language_id, target)
	{ 	
		wait_dialog_show('Ermittle vorhandene Sprachen',0);
		$.post("<?php print PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleIsShopitem", article_id:article_id }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
	
			var shopitem = $xml.find('shopitem').text();
	
			var editor_menu = '<div id="editor_header">&nbsp;</div>';
			editor_menu += '<div id="editor_tabs" class="ui-tabs ui-widget" style="border:solid 1px;" article="'+$xml.find('article_id').text()+'" shop_item="'+shopitem+'" language_id="'+language_id+'" active_tab="menu_tab_1">';
			editor_menu += '	<ul id="tabs_ul" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">';
			editor_menu += '		<li id="editor_texts" class="ui-state-default ui-corner-top ui-tabs-active ui-state-active" role="tab" tabindex="0" aria-controls="tab-general" aria-labelledby="ui-id-1" aria-selected="true" style="with:75px; height:30px; margin:2px; padding:3px; border:solid 1px; border-bottom:0; display:inline;">';
			editor_menu += '			<a id="menu_tab_1" href="#tab-general" style="cursor:pointer;" class="ui-tabs-anchor menu_tab" role="presentation" tabindex="-1">Allgemein</a>';
			editor_menu += '		</li>';
			
			if ( shopitem != 0 )
			{
				editor_menu += '		<li id="editor_nvp" class="ui-state-default ui-corner-top" role="tab" tabindex="1" aria-controls="tab-general" aria-labelledby="ui-id-2" aria-selected="false" style="with:75px; height:30px; margin:2px; padding:3px; border:solid 1px; border-bottom:0;">';
				editor_menu += '			<a id="menu_tab_2" href="#tab-general" style="cursor:pointer;" class="ui-tabs-anchor menu_tab" role="presentation" tabindex="-2">Kriterien</a>';
				editor_menu += '		</li>';
			}
			
			editor_menu += '		<li id="editor_images" class="ui-state-default ui-corner-top" role="tab" tabindex="2" aria-controls="tab-general" aria-labelledby="ui-id-3" aria-selected="false" style="with:75px; height:30px; margin:2px; padding:3px; border:solid 1px; border-bottom:0; display:inline;">';
			editor_menu += '			<a id="menu_tab_3" href="#tab-general" style="cursor:pointer;" class="ui-tabs-anchor menu_tab" role="presentation" tabindex="-3">Dateianhänge</a>';
			editor_menu += '		</li>';
			editor_menu += '		<li id="editor_images" class="ui-state-default ui-corner-top" role="tab" tabindex="3" aria-controls="tab-general" aria-labelledby="ui-id-4" aria-selected="false" style="with:75px; height:30px; margin:2px; padding:3px; border:solid 1px; border-bottom:0; display:inline;">';
			editor_menu += '			<a id="menu_tab_4" href="#tab-general" style="cursor:pointer;" class="ui-tabs-anchor menu_tab" role="presentation" tabindex="-4">Bilder</a>';
			editor_menu += '		</li>';
			editor_menu += '		<li id="editor_videos" class="ui-state-default ui-corner-top" role="tab" tabindex="4" aria-controls="tab-general" aria-labelledby="ui-id-5" aria-selected="false" style="with:75px; height:30px; margin:2px; padding:3px; border:solid 1px; border-bottom:0; display:inline;">';
			editor_menu += '			<a id="menu_tab_5" href="#tab-general" style="cursor:pointer;" class="ui-tabs-anchor menu_tab" role="presentation" tabindex="-5">Videos</a>';
			editor_menu += '		</li>';
			if ( shopitem != 0 )
			{
				editor_menu += '		<li id="editor_auctions" class="ui-state-default ui-corner-top" role="tab" tabindex="5" aria-controls="tab-general" aria-labelledby="ui-id-6" aria-selected="false" style="with:75px; height:30px; margin:2px; padding:3px; border:solid 1px; border-bottom:0; display:inline;">';
				editor_menu += '			<a id="menu_tab_6" href="#tab-general" style="cursor:pointer;" class="ui-tabs-anchor menu_tab" role="presentation" tabindex="-6">Auktionen</a>';
				editor_menu += '		</li>';
			}
			editor_menu += '	</ul>';
			editor_menu += '	<br style="clear:both;" />';
			editor_menu += '	<div id="article_editor_content"></div>';
			editor_menu += '</div>';
			
			wait_dialog_show('Erstelle Hauptmenu',100);
			
			$("#"+target).empty().append(editor_menu);
			
			$(".menu_tab").click(function(){
				var id = $( this ).attr( 'id' );
				var tab = id.split("_");
				$("#editor_tabs").attr('active_tab',id);
				$("#tabs_ul").children().removeClass('ui-tabs-active ui-state-active');
				$(this).parent().addClass('ui-tabs-active ui-state-active');
				load_view(tab[2]);
			}); 
			
			load_view_generic();
			wait_dialog_hide();
		});
	}
	
	function load_view(view)
	{ 
		switch(view)
		{
			case "1" : load_view_generic();
					break;
			case "2" : load_view_nvps(); 
					break;			
			case "3" : load_view_files();
					break;
			case "4" : load_view_images();
					break;
			case "5" : load_view_videos();
					break;
			case "6" : load_view_auctions();
					break;
		}
	}
	
	function insertAtCaret(areaId,text)
	{
		var txtarea = document.getElementById(areaId);
		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
			"ff" : (document.selection ? "ie" : false ) );
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			strPos = range.text.length;
		}
		else if (br == "ff") strPos = txtarea.selectionStart;
	
		var front = (txtarea.value).substring(0,strPos);  
		var back = (txtarea.value).substring(strPos,txtarea.value.length); 
		txtarea.value=front+text+back;
		strPos = strPos + text.length;
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			range.moveStart ('character', strPos);
			range.moveEnd ('character', 0);
			range.select();
		}
		else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
		txtarea.scrollTop = scrollPos;
	}
	
	function load_subview(type)
	{
		switch(type){
			case "file": message =	load_view_files();
				break;
			case "image": message =	load_view_images();
				break;
			case "video": message =	load_view_videos();
				break;
		}	
	}
	
	function remove_file(file_id, type)
	{	
		var message = '';
		switch(type){
			case "file": message =	'Diesen Dateianhang löschen?';
				break;
			case "image": message =	'Dieses Bild löschen?';
				break;
			case "video": message =	'Dieses Video löschen?';
				break;
		}
		var $callback =	function(){
			wait_dialog_show('Entferne Datei',0);
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleFileRemove", file_id:file_id, type:type }, function($data){ 
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				wait_dialog_show('Aktualisiere Ansicht',100);
				load_subview(type);
				wait_dialog_hide();				
			});
		};
		dialog_confirm(message, $callback);
	}
	
	function dialog_edit_file(file_id, filename, extension, description, type)
	{
		if ($("#dialog_edit_file").length == 0)
		{
			var append = '<div id="dialog_edit_file"></div>';
			$("#content").append(append);
		}
		
		switch(type){
			case "file": title = 'Datei bearbeiten';
				label_desc =	'Beschreibung';
				break;
			case "image":  title = 'Bild bearbeiten';
				label_desc =	'Bildunterschrift';
				break;
			case "video":  title = 'Video bearbeiten';
				label_desc =	'Beschreibung?';
				break;
		}
		
		var dialog_content = ' <table>';
		dialog_content += ' 	<tr>';
		dialog_content += '			<td>Dateiname</td>';
		dialog_content += ' 		<td><input id="file_name" style="width:300px;" type="text" value="'+filename+'.'+extension+'" /></td>';
		dialog_content += ' 	</tr>';
		dialog_content += ' 	<tr>';
		dialog_content += '			<td>'+label_desc+'</td>';
		dialog_content += '			<td><textarea style="width:300px; height:50px;" id="file_description">'+description+'</textarea></td>';
		dialog_content += ' 	</tr>';
		dialog_content += ' 	</table>';
		
		$("#dialog_edit_file").empty().append(dialog_content);
			
		$("#dialog_edit_file").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { edit_file(file_id, type); $(this).dialog("close"); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title: title,
			width:450
		});
	}
	
	function edit_file(file_id, type)
	{
		wait_dialog_show('Speichere Änderungen',0);
		var file_name = $("#file_name").val().substr(0,$("#file_name").val().lastIndexOf('.'));
		var file_ext = $("#file_name").val().substr($("#file_name").val().lastIndexOf('.')+1); 
		var file_desc = $("#file_description").val();
				
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleFileEdit", file_id:file_id, file_name:file_name, file_ext:file_ext, file_desc:file_desc }, function($data){ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			wait_dialog_show('Aktualisiere Ansicht',100);
			load_subview(type);
			wait_dialog_hide();
		});
	}