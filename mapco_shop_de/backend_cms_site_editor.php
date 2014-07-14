<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<style type="text/css">
	.language_dialog_button { width:25px; height:25px; margin:3px; }

	#enabled-languages, #disabled-languages {
		list-style-type: none;  float: left; padding: 5px; padding-right:20px; width: 143px;	
		min-height:300px;
		border: 0px solid #B5B0B0;
	}
	
	#disabled-languages li, #enabled-languages li{
		margin: 2px; padding: 2px; font-size: 1.0em; width: 100px;
		border: 1px solid #B5B0B0;
	}
</style>

<script type="text/javascript">

	// global languages storage	(set by view())
	var $language_array = new Array();
	var $domain_array = new Array();
	
	/**************
	*	Base Sites-Editor View
	*
	**********/
	
	function main() {
		wait_dialog_show();
		PostData = new Object();
		PostData['API'] = "cms";
		PostData['APIRequest'] = "SitesGet_neu";
		soa2(PostData, "view");	
	}
	
	function view($xml) 
	{
			$xml.find('Languages').each(function()
			{
				$language_array[$(this).find('id').text()] = $(this).find('title').text();
			});	
					 
			var sites_details = '<table width="100%">';
				sites_details += '	<tr>';
				sites_details += '		<th>Seiten ID</th>';
				sites_details += '		<th>Titel</th>';
				sites_details += '		<th>Beschreibung</th>';
				sites_details += '		<th>Domain</th>';
				sites_details += '		<th>Template</th>';
				sites_details += '		<th>Google-Analytics-Nummer</th>';
				sites_details += '		<th style="width:150px;">Sprachen - Fallback</th>';
				sites_details += '		<th>Optionen</th>';
				sites_details += '	<tr>';
				$xml.find('Site').each(function()
				{
					$domain_array[$(this).find('site_id').text()] = $(this).find('domain').text()
					sites_details += '	<tr>';
					sites_details += '		<td>'+$(this).find('site_id').text()+'</td>';
					sites_details += '		<td>'+$(this).find('site_title').text()+'</td>';
					sites_details += '		<td>'+$(this).find('description').text()+'</td>';
					sites_details += '		<td><a href="http://www.'+$(this).find('domain').text()+'" target="'+$(this).find('domain').text()+' aufrufen">'+$(this).find('domain').text()+'</a></td>';
					sites_details += '		<td>'+$(this).find('template').text()+'</td>';
					sites_details += '		<td>'+$(this).find('google_analytics').text()+'</td>';
					sites_details += '		<td>';
					$(this).find('language').each(function()
					{
						sites_details += '<div>'+$(this).find('title').text() + ' - ' + $(this).find('fallback').text()+'</div>';
					});
					sites_details += '		</td>';
					sites_details += '		<td>';
					sites_details += '			<img id="button_edit_site_'+$(this).find('site_id').text()+'" class="button_edit_site" src="<?php echo PATH; ?>images/icons/24x24/edit.png" title="Seitendetails bearbeiten" style="cursor:pointer;">';
					sites_details += '			<img id="button_change_languages_'+$(this).find('site_id').text()+'" class="button_change_languages" src="<?php echo PATH; ?>images/icons/24x24/comments.png" title="Sprachen zuweisen" style="cursor:pointer;">';
					sites_details += '		</td>';
					sites_details += '	<tr>';
				});
				sites_details += '</table>';
			$("#view").html(sites_details);
	
			/*
			*	general view events
			*/
			
			$(".button_edit_site").click(function()
			{
				var id = $(this).attr('id');
				var site_id = id.split('_');
				//dialog_edit_site(site_id[3]);
				dialog_cms_site_editor(site_id[3]);
			});
			
			$(".button_change_languages").click(function()
			{
				var id = $(this).attr('id');
				var site_id = id.split('_');
				dialog_change_languages(site_id[3]);
			});
			
			$(".button_new_site").click(function() 
			{
				dialog_cms_site_editor();
			});
					
	}
	
	/**************
	*	Site-Creator Dialogs
	*
	**********/
	
	// globals
	var siteData = new Object();	// new site dialog storage
		siteData['title'] = "";
		siteData['domain'] = "";
		siteData['template'] = "";
		siteData['description'] = "";
		siteData['ssl'] = "";
		siteData['location_id'] = "";
		siteData['id_site'] = "";
		siteData['google_analytics'] = "";
		siteData['language_id'] = "";
	
	// global location array 	
	var $location_array = new Object();
	
	function dialog_cms_site_editor(id_site) 
	{
				
		if (typeof id_site != 'undefined') 
		{ 
			siteData['id_site'] = id_site;		
		}
		
		
		if ($("#dialog_cms_site_editor").length === 0) 
		{
			$("#content").append('<div id="dialog_cms_site_editor" style="display:none;"></div>');
		}
		
		wait_dialog_show();
		
		if (siteData['id_site'] != '') 
		{ 
			var postData = new Object();
				postData['API'] = 'cms';
				postData['APIRequest'] = 'TableDataSelect';
				postData['table'] = 'cms_sites';
				postData['db'] = 'dbweb';
				postData['where'] = 'WHERE id_site='+siteData['id_site'];
			soa2(postData, "dialog_cms_site_editor_view");	
		} 
		else 
		{
			dialog_cms_site_editor_view();	
		}
			
	}
	
	function dialog_cms_site_editor_view($xml) 
	{
		
		if (typeof $xml != 'undefined') 
		{
			siteData['title'] = $xml.find('title').text();
			siteData['domain'] = $xml.find('domain').text();
			siteData['template'] = $xml.find('template').text();
			siteData['description'] = $xml.find('description').text();
			siteData['ssl'] = $xml.find('ssl').text();
			siteData['location_id'] = $xml.find('location_id').text();
			siteData['google_analytics'] = $xml.find('google_analytics').text();
		}
		
		var ssl_checked = "checked";	// ssl enabled by default
		if (typeof(siteData) === 'object') 
		{ 
			if (siteData['ssl'] === true) 
			{
				ssl_checked = "checked";	
			} 
			else if (siteData['ssl'] === false)  
			{
				ssl_checked = "";	
			}
		}
	
		var site_content = '<table>';
			site_content +='<tr><td>Seiten-Titel:</td><td><input id="editor_site_title" value="'+siteData['title']+'" /></td></tr>'; 
			site_content +='<tr><td>Seiten-Domain:</td><td><input id="editor_site_domain" value="'+siteData['domain']+'" /></td></tr>';
			site_content +='<tr><td>Template:</td><td><input id="editor_site_template" value="'+siteData['template']+'" disabled /></td></tr>';
			site_content +='<tr><td>Beschreibung:</td><td><input id="editor_site_description" value="'+siteData['description']+'"/></td></tr>';
			site_content +='<tr><td>SSL</td><td><input id="editor_site_ssl" value="true" type="checkbox" '+ssl_checked+' /></td></tr>'; 
			site_content +='<tr><td>Google-Analytics:</td><td><input id="editor_site_google_analytics" value="'+siteData['google_analytics']+'"/></td></tr>';
			if (siteData['id_site'] === '') 
			{
				site_content += '<tr><td>Hauptsprache:</td><td><select id="editor_site_language"><option>Sprache wählen</option>';
				
				$.each( $language_array, function( key, value )
				{
					if (key !== 0) 
					{
						site_content +='<option value="'+key+'">'+value+'</option>';
					}
				});
				
				site_content +='</select></td></tr>';
			}
			site_content +='</table>';
			
		$("#dialog_cms_site_editor").html(site_content); 
		/*
			update template name by change the domain name
		*/
		$("input#editor_site_domain").focusout(function() 
		{
			$(this).trigger('change');
		});

		$("input#editor_site_domain").change(function() 
		{
			if (siteData['id_site'] === "") 
			{
				var this_domain = $(this).val();
				if ($domain_array.indexOf(this_domain) > 0)
				{
					alert("Domain: "+this_domain+" existiert schon!");
					$(this).val("");
					setTimeout(function(){
						$("input#editor_site_domain").focus();
					}, 1);
				}
				else 
				{
					$("input#editor_site_template").val($(this).val());
				}
				
			}

		});

		$("#dialog_cms_site_editor").dialog
		({	buttons:
			[
				{ text: "Weiter", click: function() 
					{ 
						siteData['title'] = $('input#editor_site_title').val();
						siteData['domain'] = $('input#editor_site_domain').val();
						siteData['template'] = $('input#editor_site_template').val();
						siteData['description'] = $('input#editor_site_description').val();	
						siteData['ssl'] = $('input#editor_site_ssl').prop('checked');
						siteData['google_analytics'] = $('input#editor_site_google_analytics').val();
						if (siteData['id_site'] === "") 
						{
							$('select#editor_site_language option:selected').each(function() 
							{
								siteData['language_id'] =	$(this).val();
							});	
						}
						dialog_cms_site_editor_location();
						$(this).dialog("close"); 
					} 
				},
				{ text: "Abbrechen", click: function() 
					{ 
						siteData['title'] = "";
						siteData['domain'] = "";
						siteData['template'] = "";
						siteData['description'] = "";
						siteData['ssl'] = "";
						siteData['location_id'] = "";
						siteData['id_site'] = "";
						siteData['google_analytics'] = "";
						siteData['language_id'] = "";
						$(this).dialog("close"); 
					} 
				}
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Seite bearbeiten",
			width:450
		});		
		wait_dialog_hide();		
	}
				
 	function dialog_cms_site_editor_location() 
	{	
			if ($('#dialog_cms_site_editor_location').length == 0) 
			{
				$('#content').append('<div id="dialog_cms_site_editor_location" style="display:none;"></div>');	
			}
			var postData = new Object();
				postData['API'] = 'cms';
				postData['APIRequest'] = 'ContactsLocationGet';
				postData['task'] = 'getFull';
						
			soa2(postData, "dialog_cms_site_editor_location_view");	
	} 
	
	function dialog_cms_site_editor_location_view(data) 
	{
			data.find('Locations').each(function()
			{
				$location_array[$(this).find('id_location').text()] = $(this).find('location_name').text()+' ('+$(this).find('company').text()+')';
			});
		
			var option_locations = "<option>Standort wählen</option>";
			var checked = "";
			 
			$.each($location_array, function(key, value) 
			{
				if (siteData['location_id'] === key) 
				{
					selected = 'selected';
				} 
				else 
				{
					selected = '';	
				}
				option_locations += '<option value="'+key+'" '+selected+'>'+value+'</option>';	
			});
			 
			var dialog_content = '<p>Standort auswählen:</p>';
				dialog_content += '<table>';
				dialog_content += '<tr><td>Standort:</td><td><select id="site_location_id">'+option_locations+'</select></td><td><img id="button_new_site_location" class="button_new_site_location" src="<? echo PATH; ?>images/icons/24x24/add.png" title="Neuen Standort anlegen" style="cursor:pointer;"></td></tr>';			
				dialog_content += '</table>';

			$('#dialog_cms_site_editor_location').html(dialog_content);
			
			$('#button_new_site_location').click(function()
			{
				window.location.href= '<? echo PATH; ?>backend/inhalte/kontakte/'; 
			});
							
			$("#dialog_cms_site_editor_location").dialog
			({	buttons:
				[
					{ text: "Zurück", click: function() 
						{ 
							$('#site_location_id option:selected').each(function() 
							{
								siteData['location_id'] = $(this).val();
							}); 
							dialog_cms_site_editor(); 
							$(this).dialog("close"); 
						} 
					},
					{ text: "Speichern", click: function() 
						{ 
							$('#site_location_id option:selected').each(function() 
							{
								siteData['location_id'] = $(this).val();
							}); 
							
							if (siteData['id_site'] !== "") {
								siteData['API'] = 'cms';
								siteData['APIRequest'] = 'SiteEdit';

							} else {
								delete siteData['id_site'];
								siteData['API'] = 'cms';
								siteData['APIRequest'] = 'SiteSaveNew';
							}
							soa2(siteData, "dialog_cms_site_editor_store",'');	
							$(this).dialog("close"); 

						} 
					},
					{ text: "Abbrechen", click: function() 
						{ 
							$(this).dialog("close"); 
							siteData['title'] = "";
							siteData['domain'] = "";
							siteData['template'] = "";
							siteData['description'] = "";
							siteData['ssl'] = "";
							siteData['location_id'] = "";
							siteData['id_site'] = "";
							siteData['google_analytics'] = "";
							siteData['language_id'] = "";
						} 
					}
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Standortzuweisung",
				width:450
			});	 
	}
 	
	function dialog_cms_site_editor_store(data)
	{	
		main();
		delete siteData['API'];
		delete siteData['APIRequest'];
	}
	
	/**************
	*	Language-Selector Dialog
	*
	**********/
	
	function dialog_change_languages(site_id)
	{
		if ($("#dialog_change_languages").length == 0)
		{
			var dialog_div = '<div id="dialog_change_languages" style="display:none;">';
			dialog_div += '</div>';
			$("#content").append(dialog_div);
		}
		
		wait_dialog_show();
		
		select_cols = 'language_id, fallback_language_id, ordering';
		where = 'WHERE site_id='+site_id+' ORDER BY `ordering` ASC';
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", table:'cms_sites_languages', db:'dbweb', select:select_cols, where:where }, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			$site_languages = new Array();
			$fallback_languages= new Array();
			$fallback_languages[0] = 'global default';
			$ordering = new Array();
			
			var language_id = 0;
			
			$xml.find('cms_sites_languages').each(function()
			{		
				$site_languages[$(this).find('language_id').text()] = true;
				$fallback_languages[$(this).find('language_id').text()] = $(this).find('fallback_language_id').text();
				$ordering[$(this).find('language_id').text()] = $(this).find('ordering').text();
			});	
	
			var languages = "";
			delete languages;	// important
			languages = new Object();
			languages['disabled'] = "";
			languages['enabled'] = "";
			var enabled_items = new Object();
			var disabled_items = "";
			$.each( $language_array, function( key, value )
			{
				if ( typeof(value) !== 'undefined')
				{				 
					if ($site_languages[key] === true) 
					{
						$state ='enabled';
					} else 
					{
						$state ='disabled';	
					} 
					
					var fallback_value = "";
					var fallback_id = "";
					
					if ($state === 'enabled') 
					{
						if ($language_array[$fallback_languages[key]] !== 'undefined' && key in $fallback_languages && $fallback_languages[key] !== 0) 
						{ 	
							fallback_value = $language_array[$fallback_languages[key]];
							fallback_id = $fallback_languages[key];
						}
						if (typeof(fallback_value) === "undefined") 
						{
							fallback_value = "global default";
							fallback_id = $fallback_languages[key];	
						}
					}
					item_content = '<li id="dialog_language_'+key+'" class="dialog_ui_languages selectable-language" data-id="'+key+'" data-state="'+$state+'" data-fallback="'+fallback_id+'" style="background-color:white; cursor:pointer; width:150px;"><p>'+value+'</p> <p class="item-fallback">(Fallback: '+ fallback_value +')</p></li>';

					if ($state === 'enabled') 
					{
						enabled_items[$ordering[key]] = item_content;
					} else {
						disabled_items += item_content;	
					}
				}

			});

			$.each(enabled_items, function(key, value) 
			{	
				languages['enabled'] += value;
			});
			
			languages['disabled'] = disabled_items; 
							
			site_details = '<table><tr><td><img class="button_change_languages" src="<?php echo PATH; ?>images/icons/48x48/comments.png" title="Sprachen zuweisen" style="cursor:pointer;"></td><td><p>Hinweis: Per Drag&Drop die gewünschte Sprache zuweisen</p><p>Fallback Sprache für zugewiesene Sprachen per Doppelklick anpassbar</p></td></tr></table>  <table width="100%"><tr><td valign="top" width="50%">';
			site_details += '<h3>verfügbare Sprachen</h3><p>Nicht verwendete Sprachen</p>';
			site_details += '<ul id="disabled-languages">';
			site_details += languages['disabled'];
			site_details += '</ul></td>';
					 
			site_details += '<td valign="top" width="50%"><h3>zugewiesene Sprachen</h3><p>Aktive Sprachen</p>';
			site_details += '<ul id="enabled-languages">';
			site_details += languages['enabled'];
			site_details += '</ul></td></tr></table>'; 
					
			$("#dialog_change_languages").html(site_details);
			
			$('li.selectable-language').dblclick(function() 
			{
				
				var state = $(this).data('state');
				var id = $(this).data('id');
				var old_fallback = $(this).data('fallback');
				
				if (state === 'enabled') 
				{
					var options = '<option value="0">global default</option>';
					$.each($language_array, function(key, value) 
					{
							if (key !== 0) 
							{
								var selection = "";
								if (key === old_fallback) 
								{
									selection = 'selected';
								}
								if (key !== id && value !== undefined) 
								{
									options +='<option value="'+key+'" '+selection+'>'+value+'</option>';
								}
							}
					});
					$(this).html('<p>'+$language_array[id]+'</p><p><select id="fallback-select" data-id="'+id+'">'+options+'</select></p>');
				}
				 
				$("select#fallback-select").focusout(function() 
				{
					$(this).trigger('change');	// see change event
				});
				
				$('select#fallback-select').change(function() 
				{
					var id = $(this).data('id');
					var list_item = $(this).parent().parent();
					var new_fallback_id = $( "select#fallback-select option:selected" ).val();
					list_item.data('fallback', new_fallback_id);
					var new_val = "none";
					if (new_fallback_id === '0') 
					{
						new_val	="global default";
					} else {
						new_val = $language_array[new_fallback_id];	
					}
					list_item.html('<p>'+$language_array[id]+'</p><p>(Fallback: '+ new_val +')</p>');	
				});
			}); 
						
			$('#enabled-languages').sortable(
			{ 
      			connectWith: "ul",
				receive: function( event, ui ) 
				{
					ui.item.data('state', "enabled");
					var elem_id = ui.item.attr('id');
				}
    		});
			
			$('#disabled-languages').sortable(
			{
				connectWith: "ul",
				receive: function( event, ui ) 
				{
					ui.item.data('state', "disabled");
				}
			});
						
			$("#dialog_change_languages").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() 
						{  
							// get the current enabled list from DOM
							var order=0
							var languages = new Object();
							$("#enabled-languages > li").each(function()
							{ 
								order++;
								languages[order] = new Object();
								languages[order]['id'] = $(this).data('id');
								languages[order]['fallback'] = $(this).data('fallback'); 
							});
							
							// send the new enabled list
							var postData = new Object();
							postData['API'] = 'cms';
							postData['APIRequest'] = 'SiteLanguagesSet';
							postData['task'] = 'update';
							postData['site_id'] = site_id;
							postData['languages'] = languages;
							soa2(postData, "dialog_change_languages_store"); 
						} 
					},
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Sprachen zuweisen",
				width:510,
				height:510
			});
			wait_dialog_hide();
		});
	}
	
	function dialog_change_languages_store(data) 
	{
		main();
		$("#dialog_change_languages").dialog("close");
	}
	
	$( document ).ready(function() 
	{
		main();
	});
</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > Seiten ';
	echo '</p>';
	echo '<h1>Seiten <img id="button_new_site" class="button_new_site" src="'. PATH .'images/icons/24x24/add.png" title="Neue Seite einrichten" style="cursor:pointer;"></h1>';

	echo '<div id="view"></div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>