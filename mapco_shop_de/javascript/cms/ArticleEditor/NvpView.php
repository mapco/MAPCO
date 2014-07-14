<?php
	/***** Author Sven E. *****/
	/*** Lastmod 26.03.2014 ***/

	include("../../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>
	
	$duplicate_id = 0;
	$duplicate_name = '';
	$duplicate_value = '';
	$duplicate_comment = '';
	
	function load_view_nvps()  
	{
		wait_dialog_show('Ermittle vorhandene Sprachen', 0);
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", table:"cms_languages", where:"ORDER BY ordering", db:"dbweb" }, function($data){ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			 
			 // Language
			var article_nvp_menu = '	<div id="NVP_Language_tabs" class="ui-tabs ui-widget" style="border:solid 1px;">';
			article_nvp_menu += '		<ul id="tabs_language_ul" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">';
			var t=0;
			
			$xml.find("cms_languages").each(function(){
				article_nvp_menu += '		<li class="ui-state-default ui-corner-top"';
				article_nvp_menu += ' role="tab" tabindex="0" aria-controls="list-edit-tab'+t+'" aria-labelledby="ui-id-'+t+'" aria-selected="false">';
				article_nvp_menu += '<a href="#list-edit-tab'+t+'" id="ui-id-'+t+'" style="cursor:pointer;';
				article_nvp_menu += '" class="ui-tabs-anchor nvp_translation_tab" value="'+$(this).find('id_language').text()+'" role="presentation" tabindex="-1" id="ui-id-1">';
				article_nvp_menu += $(this).find('language').text()+'</a>';
				article_nvp_menu += '		</li>';
				t++;
			});
			article_nvp_menu += '		</ul>';
			article_nvp_menu += '	<div id="NVP_tab_categories" style="margin-left:10px;"></div>';
			article_nvp_menu += '	<div id="NVP_tab_nvps" style="margin-left:235px;"></div>';
			article_nvp_menu += '	<br style="clear:both;" />';
			article_nvp_menu += '	</div>';
			
			wait_dialog_show('Erzeuge Menu', 100);
			$("#article_editor_content").empty().append(article_nvp_menu);
			
			$(".nvp_translation_tab").click(function(){
				$("#NVP_tab_categories").empty();
				$("#NVP_tab_nvps").empty();
	
				var active = 'ui-tabs-active ui-state-active';
				$("#tabs_language_ul").children().removeClass('ui-tabs-active ui-state-active');
				$(this).parent().addClass('ui-tabs-active ui-state-active');
				
				var id = $(this).attr('value');
				$("#NVP_Language_tabs").attr('active_language',id);
				load_editor_NVP_tab(id);
			});
							
			wait_dialog_hide();
		});
	}
	
	function load_editor_NVP_tab()
	{ 
		wait_dialog_show('Lade Kategorien', 0);
		var item_id = $("#editor_tabs").attr('shop_item');
		var language_id = $("#NVP_Language_tabs").attr('active_language');
	
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleNVPsGet", item_id:item_id, lang:language_id }, function($data){ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
	
			var article_nvp_content = '';  
			var cat_id = '';
			var ordering = '';
			var name_label = '';
			var value_label = '';
			var title = '';
			var counting = 0;
			
			wait_dialog_show('Erstelle Liste der Kategorien', 50);
			
			var duplicate = '<div id="nvp_duplicate_'+$duplicate_id+'">';
			duplicate += $duplicate_name + ' - ' + $duplicate_value;
			duplicate += '<img id="button_clear_duplicate" src="<?php echo PATH; ?>images/icons/24x24/remove.png" title="Duplizieren abbrechen" style="cursor:pointer; float:right;">>';
			
			article_nvp_content += '<table id="duplicate_table" style="';
			if ( $duplicate_id == 0 )
			{
				article_nvp_content += 'display:none; ';
			}
			article_nvp_content += 'width:225px;">';
			article_nvp_content += '	<tr>';
			article_nvp_content += '		<th>Duplikat</th>';
			article_nvp_content += '	</tr>';
			article_nvp_content += '	<tr>';
			article_nvp_content += '		<td id="duplicates">'+duplicate+'</td>';
			article_nvp_content += '	</tr>';
			article_nvp_content += '</table>';
			
			article_nvp_content += '<table id="category_list" class="ui-sortable hover" style="width:200px; float:left;">';
			article_nvp_content += '<tr class="header" style="width:198px;"><th colspan="2">Kategorien <img src="<?php echo PATH; ?>images/icons/24x24/add.png" value="'+$(this).find('id').text()+'" id="button_create_category" title="Kategorie hinzufügen" style="float:right; cursor:pointer;"></th></tr>';
			$xml.find('category').each(function(){	
				cat_id = $(this).find('id').text();
				ordering = $(this).find('ordering').text();
				name_label = $(this).find('name_label').text();
				name_length = $(this).find('name_length').text();
				value_label = $(this).find('value_label').text();
				value_length = $(this).find('value_length').text();
				title = $(this).find('title').text();
				counting = $(this).find('counting').text();
							
				article_nvp_content	+= '<tr><td id="NVP_cat_'+cat_id+'" class="button_open_category" style="cursor:pointer; width:198px; border-right:none;" cat_id="'+cat_id+'" ordering="'+ordering+'"';
				article_nvp_content += ' name_label="'+name_label+'" value_label="'+value_label+'" counting="'+counting+'"';
				article_nvp_content += ' name_length="'+name_length+'" value_length="'+value_length+'">';
				article_nvp_content += title+' ('+counting+')</td>';
				article_nvp_content += '	<td style="border-left:none;"><img id="button_edit_category_'+$(this).find('id').text()+'" class="button_edit_category" src="<?php echo PATH; ?>images/icons/24x24/edit.png" title="Kriterium bearbeiten" style="cursor:pointer;"></td></tr>';
			});
			article_nvp_content += '</table>';
	
			wait_dialog_show('Erzeuge Oberfläche', 100);
			$("#NVP_tab_categories").empty().append(article_nvp_content);
			
			article_nvp_content = '<table id="nvp_list" value="0" class="hover ui-sortable" style="width:1000px;">';
			article_nvp_content += '	<tr class="unsortable" style="width:998px;">';
			article_nvp_content += '		<th style="width:198px;"></th>';
			article_nvp_content += '		<th style="width:250px;"></th>';
			article_nvp_content += '		<th style="width:370px;">Kommentar</td>';
			//article_nvp_content += '		<th style="width:98px;" colspan="2"><img id="button_create_nvp_0" class="button_created_nvp" src="<?php echo PATH; ?>images/icons/24x24/add.png" title="Neues Kriterium anlegen" style="float:right; cursor:pointer;">';
			
			article_nvp_content += '		<img id="button_import_auctions" class="button_import_auctions" src="<?php echo PATH; ?>images/icons/24x24/repeat.png" title="Auktionstitel importieren" style="float:right; cursor:pointer;">';
	
			article_nvp_content += '		</th>';
			article_nvp_content += '	</tr>';
			article_nvp_content += '	<tr>';
			article_nvp_content += '		<td colspan=4>Keine Kategorie gewählt!</td>';
			article_nvp_content += '	</tr>';
			article_nvp_content += '</table>';
			$("#NVP_tab_nvps").empty().append(article_nvp_content);
			
			$("#button_create_category").click(function(){
				dialog_create_category();
			});
			
			$(".button_open_category").click(function(){
				if ( $(this).attr('counting') == 0 )
				{
					$confirm=confirm("Für diese Kategorie existieren noch keine Kriterien! Soll ein neues Kriterium angelegt werden?");
					if( $confirm )
					{
						var id = $(this).attr('cat_id');
						$("#NVP_Language_tabs").attr('active_category',id);
						dialog_create_nvp(0);
					}
				}
				else
				{
					var id = $(this).attr('cat_id');
					$("#NVP_Language_tabs").attr('active_category',id);
					show_nvps(id);
				}
			});
			
			$(".button_import_auctions").click(function(){
				import_auctions();
			});
			
			$(".button_edit_category").click(function(){
				var id = $(this).attr('id');
				var category_id = id.split('_');
				dialog_edit_category();
			});
			
			$(function() {
				$( "#category_list" ).sortable( { items:"tr:not(.header)" } );
				$( "#category_list" ).sortable( { cancel:".header"} );
				$( "#category_list" ).disableSelection();
				$( "#category_list" ).bind( "sortupdate", function(event, ui)
				{
					wait_dialog_show('Sortiere Einträge', 0);
					var list = $('#category_list').sortable('toArray');
					$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSort", list:list, table:'shop_items_nvp_categories', label:'NVP_cat_', column:'id', db:'dbshop' }, function($data){ 
						//show_status2($data); return;
						try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
						if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
						
						wait_dialog_show('Sortiere Einträge', 100);
						load_editor_NVP_tab();
					});
				});
			});
			
			wait_dialog_hide();
		});
	}
	
	function create_category()
	{
		wait_dialog_show('Erstelle Kategorie', 0);
		var title = $("#create_cat_title").val();
		var name_label = $("#create_cat_name_label").val();
		var value_label = $("#create_cat_value_label").val();
		var ordering = $("#create_cat_ordering :selected").val();
		
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleNVPCategoryCreate", title:title, name_label:name_label, value_label:value_label, ordering:ordering }, function($data){ 
			//show_status2($data); return;
			wait_dialog_show('Erstelle Kategorie', 100);
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
			load_editor_NVP_tab();
			wait_dialog_hide();
		});
	}
	
	function dialog_create_category()
	{
		wait_dialog_show('Zeichne Kategorieerstellungsdialog', 100);
		var language_id = $("#NVP_Language_tabs").attr('active_language');
		if ($("#dialog_create_category").length == 0)
		{
			var dialog_div = '<div id="dialog_create_category">';
			dialog_div += '</div>';
			$("#content").append(dialog_div);
		}
	
		var dialog_content = ' <table>';
		dialog_content += ' 	<tr>';
		dialog_content += '			<td>Titel</td>';
		dialog_content += ' 		<td><input id="create_cat_title" style="width:300px;" type="text" value="" /></td>';
		dialog_content += ' 	</tr>';
		dialog_content += ' 	<tr>';
		dialog_content += '			<td>Name-Label</td>';
		dialog_content += ' 		<td><input id="create_cat_name_label" style="width:300px;" type="text" value="" /></td>';
		dialog_content += ' 	</tr>';
		dialog_content += ' 	<tr>';
		dialog_content += '			<td>Value-Label</td>';
		dialog_content += '			<td><input id="create_cat_value_label" style="width:300px;" type="text" value="" /></td>';
		dialog_content += ' 	</tr>';
		dialog_content += ' 	<tr>';
		dialog_content += '			<td>Sortierung</td>';
		dialog_content += '			<td><select id="create_cat_ordering">';
		for(ordering=1;ordering<$("#category_list").find('tr').length;ordering++)
		{
			dialog_content += '		<option value="'+ordering+'">'+ordering+'</option>';		
		}
		dialog_content += '		<option value="'+ordering+'">'+ordering+'</option>';
		dialog_content += ' </select></td>';
		dialog_content += ' 	</tr>';
		dialog_content += ' 	</table>';
		$("#dialog_create_category").empty().append(dialog_content);
		
		$("#dialog_create_category").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { create_category(); $(this).dialog("close"); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neue Kategorie anlegen",
			width:450
		});
		
		wait_dialog_hide();
	}
	
	function create_nvp()
	{
		var language_id = $("#NVP_Language_tabs").attr('active_language');
		var category_id = $("#NVP_Language_tabs").attr('active_category');
		wait_dialog_show('Erstelle Kriterium',0);
		var item_id = $("#editor_tabs").attr('shop_item');
		var name = $("#create_nvp_name").val();
		var value = $("#create_nvp_value").val();
		var comment = $("#create_nvp_comment").val();
		
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleNVPCreate", item_id:item_id, category_id:category_id, language_id:language_id, name:name, value:value, comment:comment }, function($data){ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
			var message = '';
			if ($xml.find('insert_id').text() != '' || $xml.find('insert_id').text() == false)
			{
				message += 'Neues Kriterium wurde unter mit der ID ' + $xml.find('insert_id').text() + ' angelegt!';
				wait_dialog_show('Erstelle neues Kriterium',100);
			}
			else
			{
				message += 'Erstellen des Kriteriums fehlgeschlagen!';
			}
			
			wait_dialog_hide();
			load_editor_NVP_tab();
			show_nvps();
			//dialog_notify(message);
		});
	}
	
	
	function dialog_create_nvp(duplicate)
	{ 
		var language_id = $("#NVP_Language_tabs").attr('active_language');
		var category_id = $("#NVP_Language_tabs").attr('active_category');
		
		if ($("#dialog_create_nvp").length == 0)
		{
			var dialog_div = '<div id="dialog_create_nvp" style="display:none;">';
			dialog_div += '</div>';
			$("#content").append(dialog_div);
		}
		
		var dialog_content = ' <table>';
		dialog_content += ' 	<tr>';
		dialog_content += '			<td>'+$("#NVP_cat_"+category_id).attr('name_label')+'</td>';
		dialog_content += ' 		<td>';
		dialog_content += '				<textarea id="create_nvp_name" style="width:300px; height:40px;">';
		if ( duplicate == 1 )
		{
			dialog_content += $duplicate_name;
		}
		dialog_content += '</textarea>';
		dialog_content += '				<br /><span id="create_nvp_name_length"></span>';
		dialog_content += '			</td>';
		dialog_content += ' 	</tr>';
		dialog_content += ' 	<tr>';
		dialog_content += '			<td>'+$("#NVP_cat_"+category_id).attr('value_label')+'</td>';
		dialog_content += ' 		<td>';
		dialog_content += ' 			<textarea id="create_nvp_value" style="width:300px; height:40px;" type="text" onkeyup="">';
		if ( duplicate == 1 )
		{
			dialog_content += $duplicate_value;
		}
		dialog_content += '</textarea>';
		dialog_content += '				<br /><span id="create_nvp_value_length"></span>';
		dialog_content += '			</td>';
		dialog_content += ' 	</tr>';
		dialog_content += ' 	<tr>';
		dialog_content += '			<td>Kommentar</td>';
		dialog_content += '			<td><textarea style="width:300px; height:50px;" id="create_nvp_comment">';
		if ( duplicate == 1 )
		{
			dialog_content += $duplicate_comment;
		}
		dialog_content += '</textarea></td>';
		dialog_content += ' 	</tr>';
		dialog_content += ' 	</table>';
		$("#dialog_create_nvp").html(dialog_content);
		$("#create_nvp_name").bind("keyup", function() { textlength("create_nvp_name", "create_nvp_name_length", $("#NVP_cat_"+category_id).attr('name_length')); });
		$("#create_nvp_value").bind("keyup", function() { textlength("create_nvp_value", "create_nvp_value_length", $("#NVP_cat_"+category_id).attr('value_length')); });
		
	
		$("#dialog_create_nvp").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { create_nvp(); $(this).dialog("close"); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Neues Kriterium anlegen",
			width:450
		});
	}
	
	function show_nvps() 
	{
		var language_id = $("#NVP_Language_tabs").attr('active_language');
		var category_id = $("#NVP_Language_tabs").attr('active_category');
		wait_dialog_show('Lade Kriterien',0);
		var item_id = $("#editor_tabs").attr('shop_item');
			
		var where = 'WHERE item_id='+item_id+' AND language_id='+language_id+' AND category_id='+category_id+' ORDER BY ordering ASC';
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", table:"shop_items_nvp", where:where, db:"dbshop" }, function($data){ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
	
			var name = '';
			var value = '';
			var comment = '';
			wait_dialog_show('Erstelle Kriterienliste',50);
			var article_nvp_content = '<table id="nvp_list" value="'+category_id+'" class="hover ui-sortable" style="width:1000px;">';
			article_nvp_content += '	<tr class="unsortable" style="width:998px;">';
			article_nvp_content += '		<th style="width:198px;">'+$('#NVP_cat_'+category_id).attr('name_label')+'</th>';
			article_nvp_content += '		<th style="width:250px;">'+$('#NVP_cat_'+category_id).attr('value_label')+'</th>';
			article_nvp_content += '		<th style="width:370px;">Kommentar</td>';
			article_nvp_content += '		<th style="width:98px;" colspan="2"><img id="button_create_nvp_'+category_id+'" class="button_created_nvp" src="<?php echo PATH; ?>images/icons/24x24/add.png" title="Neues Kriterium anlegen" style="float:right; cursor:pointer;">';
			
			if ( category_id == 5 )
			{
				article_nvp_content += '		<img id="button_import_auctions" class="button_import_auctions" src="<?php echo PATH; ?>images/icons/24x24/repeat.png" title="Auktionstitel importieren" style="float:right; cursor:pointer;">';
			}
			
			article_nvp_content += '			<img id="button_insert_duplicate" src="<?php echo PATH; ?>images/icons/24x24/down.png" title="Duplikat einfügen" style="cursor:pointer;';
			if ( $duplicate_id == 0 )
			{
				article_nvp_content += ' display:none;';
			}
			article_nvp_content += '">';
			
			article_nvp_content += '		</th>';
			article_nvp_content += '	</tr>';	
			$xml.find('shop_items_nvp').each(function(){
				name = ($(this).find('name').text() != '') ? $(this).find('name').text() : '&nbsp;';
				value = ($(this).find('value').text() != '') ? $(this).find('value').text() : '&nbsp;';
				comment = ($(this).find('comment').text() != '') ? $(this).find('comment').text() : '&nbsp;';
				active = ($(this).find('active').text() == 1) ? 'aktiv' : 'inaktiv';
				
				article_nvp_content += '	<tr id="nvp_'+$(this).find('id').text()+'" style="width:998px;">';
				article_nvp_content += '		<td style="width:198px;">'+name+'</td>'; 
				article_nvp_content += '		<td style="width:250px;">'+value+'</td>';
				article_nvp_content += '		<td style="width:370px;">'+comment+'</td>';
				//article_nvp_content += '		<td style="width:76px; border-right:none;">'+active+'</td>';
				article_nvp_content += '		<td style="width:98px; border-left:none"><div style="float:right;";>';
				article_nvp_content += '				<img id="button_delete_nvp_'+$(this).find('id').text()+'" class="button_delete_nvp" src="<?php echo PATH; ?>images/icons/24x24/remove.png" title="Kriterium löschen" style="cursor:pointer;">';
				article_nvp_content += '				<img id="button_edit_nvp_'+$(this).find('id').text()+'" class="button_edit_nvp" src="<?php echo PATH; ?>images/icons/24x24/edit.png" title="Kriterium bearbeiten" style="cursor:pointer;">';
				article_nvp_content += '				<img id="button_copy_nvp_'+$(this).find('id').text()+'" class="button_copy_nvp" src="<?php echo PATH; ?>images/icons/24x24/add.png" title="Kriterium duplizieren" style="cursor:pointer;">';			
				article_nvp_content += '			</div>';
				article_nvp_content += '		</td>';
				article_nvp_content += '	</tr>';	
			});
	
			article_nvp_content += '</table>';
	
			$("#NVP_tab_nvps").empty().append(article_nvp_content);
			
			wait_dialog_show('Erstelle Kriterienliste',100);
			
			$(".button_created_nvp").click(function(){
				dialog_create_nvp(0);
			});
			
			$(".button_import_auctions").click(function(){
				import_auctions();
			});
			
			$(".button_edit_nvp").click(function(){
				var id = $(this).attr('id');
				var nvp_id = id.split('_');
				dialog_edit_nvp(nvp_id[3]);
			});
			
			$(".button_copy_nvp").click(function(){
				var id = $(this).attr('id');
				var nvp_id = id.split('_');
				copy_nvp(nvp_id[3]);
			});
			
			$("#button_insert_duplicate").click(function(){
				dialog_create_nvp(1);
			});
			
			$(".button_delete_nvp").click(function(){
				var id = $(this).attr('id');
				var nvp_id = id.split('_');
				delete_nvp(nvp_id[3]);
			});
			
			$(function() {
				$( "#nvp_list" ).sortable( { items:"tr:not(.unsortable)" } );
				$( "#nvp_list" ).sortable( { cancel:".unsortable"} );
				$( "#nvp_list" ).disableSelection();
				$( "#nvp_list" ).bind( "sortupdate", function(event, ui)
				{
					var list = $('#nvp_list').sortable('toArray');
					$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSort", list:list, table:'shop_items_nvp', label:'nvp_', column:'id', db:'dbshop' }, function($data){ 
						//show_status2($data); return;
						try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
						if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
						
						show_nvps();
					});
				});
			});
			wait_dialog_hide();
		}); 
	}
	
	function copy_nvp(nvp_id)
	{
		$("#duplicate_table").css('display','');
		var duplicate = '<div id="nvp_duplicate_'+nvp_id+'">';
		var z=0;
		var duplicate_values = new Array();
		$("#nvp_"+nvp_id+" > td").each(function(){		
			duplicate_values[z] = $(this).html();
			z++;
			if ( z == 3 )
			{
				return false;	
			}
		});
		$duplicate_id = nvp_id;
		$duplicate_name = duplicate_values[0];
		$duplicate_value = duplicate_values[1];
		$duplicate_comment = duplicate_values[2];
		$("#button_insert_duplicate").css('display','');
	
		duplicate += $duplicate_name + ' - ' + $duplicate_value;
		duplicate += '<img id="button_clear_duplicate" src="<?php echo PATH; ?>images/icons/24x24/remove.png" title="Duplizieren abbrechen" style="cursor:pointer; float:right;">>';
		duplicate += '</div>';
		$("#duplicates").empty().append(duplicate);
		
		$("#button_clear_duplicate").click(function(){
			$duplicate_id = 0;
			$duplicate_name = '';
			$duplicate_value = '';
			$duplicate_comment = '';
			$("#duplicates").empty();
			$("#button_insert_duplicate").css('display','none');
			$("#duplicate_table").css('display','none');
		});
	}
	
	function import_auctions()
	{
		var item_id = $("#editor_tabs").attr('shop_item');
		var language_id = $("#NVP_Language_tabs").attr('active_language');
	
	
		wait_dialog_show('Importiere Auktionstitel', 0);
		$.post("<?php print PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleNVPImportAuctions", db:'dbshop', item_id:item_id, language_id:language_id }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
			wait_dialog_hide();
	
			alert($xml.find('check').text()+' Auktionen geprüft, davon '+$xml.find('entries').text()+'importiert!');
			
			if ( typeof($("#NVP_Language_tabs").attr('active_category')) == 'undefined' )
			{	
				load_editor_NVP_tab();
			}
			else
			{
				show_nvps();
			}
		});
	}
	
	function delete_nvp(nvp_id)
	{
		var language_id = $("#NVP_Language_tabs").attr('active_language');
		var category_id = $("#NVP_Language_tabs").attr('active_category');
		var $callback = function(){	
			wait_dialog_show('Lösche Kriterium', 0);
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleNVPDelete", nvp_id:nvp_id }, function($data){ 
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				var message = '';
				if ($xml.find('Error').length>0)
				{
					message += 'Löschen fehlgeschlagen';
				}
				else
				{	
					wait_dialog_show('Lösche Kriterium', 100);
					message += 'Kriterium wurde gelöscht!';
				}
				wait_dialog_hide();
				load_editor_NVP_tab();
				show_nvps();
				//dialog_notify(message);
			});
		}
		dialog_confirm('Kriterium wirklich löschen?',$callback);
	}
	
	function dialog_edit_nvp(nvp_id)
	{ 
		var language_id = $("#NVP_Language_tabs").attr('active_language');
		if ($("#dialog_edit_nvp").length == 0)
		{
			var dialog_div = '<div id="dialog_edit_nvp">';
			dialog_div += '</div>';
			$("#content").append(dialog_div);
		}
		
		var where = 'WHERE id='+nvp_id;
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", table:'shop_items_nvp', db:'dbshop', where:where }, function($data){ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
		//	if ($("#dialog_edit_nvp").length == 0)
		//	{
				var $category_id=$xml.find('category_id').text();
				var dialog_content = ' <table>';
				dialog_content += ' 	<tr>';
				dialog_content += '			<td>'+$("#NVP_cat_"+$category_id).attr('name_label')+'</td>';
				dialog_content += ' 		<td>';
				dialog_content += '				<textarea id="edit_nvp_name" style="width:300px; height:40px;">'+$xml.find('name').text()+'</textarea>';
				dialog_content += '				<br /><span id="edit_nvp_name_length"></span>';
				dialog_content += '			</td>';
				dialog_content += ' 	</tr>';
				dialog_content += ' 	<tr>';
				dialog_content += '			<td>'+$("#NVP_cat_"+$category_id).attr('value_label')+'</td>';
				dialog_content += ' 		<td>';
				dialog_content += '				<textarea id="edit_nvp_value" style="width:300px; height:40px;">'+$xml.find('value').text()+'</textarea>';
				dialog_content += '				<br /><span id="edit_nvp_value_length"></span>';
				dialog_content += '			</td>';
				dialog_content += ' 	</tr>';
				dialog_content += ' 	<tr>';
				dialog_content += '			<td>Kommentar</td>';
				dialog_content += '			<td><textarea style="width:300px; height:50px;" id="edit_nvp_comment">'+$xml.find('comment').text()+'</textarea></td>';
				dialog_content += ' 	</tr>';
				dialog_content += ' 	</table>';
				dialog_content += '</div>';
		
				$("#dialog_edit_nvp").empty().append(dialog_content);
				$("#edit_nvp_name").bind("keyup", function() { textlength("edit_nvp_name", "edit_nvp_name_length", $("#NVP_cat_"+$category_id).attr('name_length')); });
				$("#edit_nvp_value").bind("keyup", function() { textlength("edit_nvp_value", "edit_nvp_value_length", $("#NVP_cat_"+$category_id).attr('value_length')); });
				textlength("edit_nvp_name", "edit_nvp_name_length", $("#NVP_cat_"+$category_id).attr('name_length'));
				textlength("edit_nvp_value", "edit_nvp_value_length", $("#NVP_cat_"+$category_id).attr('value_length'));
		//	}	
			$("#dialog_edit_nvp").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { edit_nvp(nvp_id); $(this).dialog("close"); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Neues Kriterium anlegen",
				width:450
			});
		});
	}
	
	function edit_nvp(nvp_id)
	{
		var language_id = $("#NVP_Language_tabs").attr('active_language');
		var category_id = $("#NVP_Language_tabs").attr('active_category');
		wait_dialog_show('Speichere Kriterium', 0);
		var item_id = $("#editor_tabs").attr('shop_item');
	
		var name = $("#edit_nvp_name").val();
		var value = $("#edit_nvp_value").val();
		var comment = $("#edit_nvp_comment").val();
		
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleNVPEdit", nvp_id:nvp_id,item_id:item_id, category_id:category_id, language_id:language_id, name:name, value:value, comment:comment }, function($data){ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
			var message = '';
			if ($xml.find('Error').length>0)
			{
				message += $xml.find('Error').text();
			}
			else
			{	
				wait_dialog_show('Speichere Kriterium', 100);
				message += 'Änderungen wurden übernommen!';
			}
			wait_dialog_hide();
			//dialog_notify(message);
			show_nvps();
		});
	}
	
	function edit_category()
	{
		var language_id = $("#NVP_Language_tabs").attr('active_language');
		var category_id = $("#NVP_Language_tabs").attr('active_category');
		wait_dialog_show('Speichere Kategorie', 0);
		var title = $("#edit_cat_title").val();
		var name_label = $("#edit_cat_name_label").val();
		var value_label = $("#edit_cat_value_label").val();
		var ordering = $("#edit_cat_ordering :selected").val();
	
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleNVPCategoryEdit", category_id:category_id, title:title, name_label:name_label, value_label:value_label, ordering:ordering }, function($data){ 
			//show_status2($data); return;
			wait_dialog_show('Speichere Kategorie', 100);
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
			wait_dialog_hide();
			load_editor_NVP_tab();
		});
	}
	
	function dialog_edit_category()
	{
		var language_id = $("#NVP_Language_tabs").attr('active_language');
		var category_id = $("#NVP_Language_tabs").attr('active_category');
		if ($("#dialog_edit_category").length == 0)
		{
			var dialog_div = '<div id="dialog_edit_category">';
			dialog_div += '</div>';
			$("#content").append(dialog_div);
		}
		
		var where = 'WHERE id='+category_id;
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", table:'shop_items_nvp_categories', db:'dbshop', where:where }, function($data){ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			var dialog_content = ' <table>';
			dialog_content += ' 	<tr>';
			dialog_content += '			<td>Titel</td>';
			dialog_content += ' 		<td><input id="edit_cat_title" style="width:300px;" type="text" value="'+$xml.find('title').text()+'" /></td>';
			dialog_content += ' 	</tr>';
			dialog_content += ' 	<tr>';
			dialog_content += '			<td>Name-Label</td>';
			dialog_content += ' 		<td><input id="edit_cat_name_label" style="width:300px;" type="text" value="'+$xml.find('name_label').text()+'" /></td>';
			dialog_content += ' 	</tr>';
			dialog_content += ' 	<tr>';
			dialog_content += '			<td>Value-Label</td>';
			dialog_content += '			<td><input id="edit_cat_value_label" style="width:300px;" type="text" value="'+$xml.find('value_label').text()+'" /></td>';
			dialog_content += ' 	</tr>';
			dialog_content += ' </table>';
			$("#dialog_edit_category").empty().append(dialog_content);
			
			$("#dialog_edit_category").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { edit_category(); $(this).dialog("close"); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Kategorie bearbeiten",
				width:450
			});
		});
	}
