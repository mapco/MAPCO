<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<style type="text/css">

	.language_dialog_button { width:25px; height:25px; margin:3px; }

	#enabled-languages, #disabled-languages {
		list-style-type: none; margin: 0; padding: 0; float: left; margin-right: 10px; padding: 5px; width: 143px;	
		min-height:100px;
		border: 1px solid #B5B0B0;
	}
	
	#disabled-languages li, #enabled-languages li{
		margin: 2px; padding: 2px; font-size: 1.0em; width: 100px;
		border: 1px solid #B5B0B0;
	}
</style>

<script type="text/javascript">

	var $language_array = new Array();
	
	function showview($xml) {

			$xml.find('Languages').each(function()
			{
				
				$language_array[$(this).find('id').text()] = $(this).find('title').text();
				
			});	
					
			var sites_details = '<table>';
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
				$xml.find('Site').each(function(){
					sites_details += '	<tr>';
					sites_details += '		<td>'+$(this).find('site_id').text()+'</td>';
					sites_details += '		<td>'+$(this).find('site_title').text()+'</td>';
					sites_details += '		<td>'+$(this).find('description').text()+'</td>';
					sites_details += '		<td>'+$(this).find('domain').text()+'</td>';
					sites_details += '		<td>'+$(this).find('template').text()+'</td>';
					sites_details += '		<td>'+$(this).find('google_analytics').text()+'</td>';
					sites_details += '		<td>';
					$(this).find('language').each(function(){
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
			$("#view").empty().append(sites_details);
	
			$(".button_edit_site").click(function(){
				var id = $(this).attr('id');
				var site_id = id.split('_');
				dialog_edit_site(site_id[3]);
			});
			
			$(".button_change_languages").click(function(){
				var id = $(this).attr('id');
				var site_id = id.split('_');
				dialog_change_languages(site_id[3]);
			});
		
	}

		
	function dialog_edit_site(site_id)
	{
		if ($("#dialog_edit_site").length == 0)
		{
			var dialog_div = '<div id="dialog_edit_site" style="display:none;">';
			dialog_div += '</div>';
			$("#content").append(dialog_div);
		}
		
		wait_dialog_show();
		var where = 'WHERE id_site='+site_id;
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", table:'cms_sites', db:'dbweb', where:where }, function($data)
		{
			//show_status2($data); return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			var site_details = '<table>';
			site_details += '	<tr>';
			site_details += '		<td>Titel</td>';
			site_details += '		<td><input id="edit_site_title" value="'+$xml.find('title').text()+'"></td>';
			site_details += '	</tr>';
			site_details += '	<tr>';
			site_details += '		<td>Beschreibung</td>';
			site_details += '		<td><input id="edit_site_description" value="'+$xml.find('description').text()+'"></td>';
			site_details += '	</tr>';
			site_details += '	<tr>';
			site_details += '		<td>Domäne</td>';
			site_details += '		<td><input id="edit_site_domain" value="'+$xml.find('domain').text()+'"></td>';
			site_details += '	</tr>';
			site_details += '	<tr>';
			site_details += '		<td>Template</td>';
			site_details += '		<td><input id="edit_site_template" value="'+$xml.find('template').text()+'"></td>';
			site_details += '	</tr>';
			site_details += '	<tr>';
			site_details += '		<td>Google-Analytics</td>';
			site_details += '		<td><input id="edit_site_google_analytics" value="'+$xml.find('google_analytics').text()+'"></td>';
			site_details += '	</tr>';
			site_details += '</table>';
			
			$("#dialog_edit_site").html(site_details);
			
			$("#dialog_edit_site").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { edit_site(site_id); $(this).dialog("close"); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Seitendetails bearbeiten",
				width:450
			});		
			wait_dialog_hide();	
		});
	}
	
	function edit_site(id_site)
	{
		var title = $("#edit_site_title").val();
		var description = $("#edit_site_description").val();
		var domain = $("#edit_site_domain").val();
		var template = $("#edit_site_template").val();
		var google_analytics = $("#edit_site_google_analytics").val();

		wait_dialog_show();

		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"SiteEdit", id_site:id_site, title:title, description:description, domain:domain, template:template, google_analytics:google_analytics }, function($data)
		{ 
			//show_status2($data); //return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			wait_dialog_hide();
		});
	}
	
	function mark_language ($id)
	{
		if ( $($id).hasClass('marked'))
		{
			$($id).css('background-color','white');
			$($id).css('color','black');
			$($id).removeClass('marked');
		}
		else
		{
			$($id).css('background-color','orange');
			$($id).css('color','white');
			$($id).addClass('marked');
		}
	}
	
	function dialog_change_languages(site_id)
	{
		if ($("#dialog_change_languages").length == 0)
		{
			var dialog_div = '<div id="dialog_change_languages" style="display:none;">';
			dialog_div += '</div>';
			$("#content").append(dialog_div);
		}
		
		wait_dialog_show();
		
		select_cols = 'language_id, fallback_language_id';
		where = 'WHERE site_id='+site_id;
		
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", table:'cms_sites_languages', db:'dbweb', select:select_cols, where:where }, function($data)
		{
			//show_status2($data); return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			/*
				@array $site_languages 		
			*/
			$site_languages = new Array();
			var language_id = 0;

			$xml.find('cms_sites_languages').each(function(){
				$site_languages[$(this).find('language_id').text()] = true;
			});		
			
			var languages = new Object();
				languages['disabled'] = "";
				languages['enabled'] = "";
			$.each( $language_array, function( key, value ){
				
				if ( typeof(value) !== 'undefined')
				{				
					
					if ($site_languages[key] === 'undefined') {
						$state ='disabled';
					} else {
						$state ='enabled';	
					} 
					 
					if (languages[$state] === 'undefined') {
						languages[$state] = '<li id="dialog_language_'+key+'" class="dialog_ui_languages selectable-language" data-id="'+key+'" data-state="'+$state+'" style="background-color:white; cursor:pointer; width:150px;">'+value+'</li>';	
					} else {
						languages[$state] += '<li id="dialog_language_'+key+'" class="dialog_ui_languages selectable-language" data-id="'+key+'" data-state="'+$state+'" style="background-color:white; cursor:pointer; width:150px;">'+value+'</li>';	
					}
				} 
 
			});
			
			site_details = '<table><tr><td valign="top">';
			site_details += '<h3>verfügbare Sprachen</h3><p><span style="font-size:9ox;">Nicht verwendete Sprachen<span></p>';
			site_details += '<ul id="disabled-languages">';
			site_details += languages['disabled'];
			site_details += '</ul></td><td width="50"></td>';
					 
			site_details += '<td valign="top"><h3>zugewiesene Sprachen</h3><p><span style="font-size:9px;">Aktive Sprachen und dessen Reihenfolge</span></p>';
			site_details += '<ul id="enabled-languages">';
			site_details += languages['enabled'];
			site_details += '</ul></td></tr></table>';
					
			$("#dialog_change_languages").html(site_details);
			
			$('#enabled-languages').sortable({
      			connectWith: "ul"
    		});
			
			$('#disabled-languages').sortable({
				connectWith: "ul"
			});
						
			$("#dialog_change_languages").dialog
			({	buttons:
				[
					{ text: "Weiter", click: function() { change_language(site_id); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Sprachen zuweisen",
				width:510,
				height:420
			});
			wait_dialog_hide();
		});
	}
	/*
	*	DEPRECATED!!!!! 
	*/
	function change_language(site_id)
	{	
		var x = 0;
		var id = new Array();
		$("#list_site_languages > li").each(function()
	  	{ 
			if (!$(this).hasClass('header'))
			{
				$row_id = $(this).attr('id');
				$row_id = $row_id.split('_');
				
				id[x] = $row_id[3];
				x++;
			}
			
		});
		var languages = id.join(',');

		wait_dialog_show('Speicher Sprachzuordnung',0);
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"SiteLanguagesSet", site_id:site_id, languages:languages }, function($data)
		{
			//show_status2($data); return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			wait_dialog_hide();
			dialog_set_fallbacks(site_id, languages);
		});
	}
	
/*	function dialog_set_fallbacks(site_id, languages)
	{
		if ($("#dialog_change_languages").length == 0)
		{
			var dialog_div = '<div id="dialog_change_languages" style="display:none;">';
			dialog_div += '</div>';
			$("#content").append(dialog_div);
		}
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"SiteGetLanguages", site_id:site_id }, function($data)
		{
			$languages = new Array();
			//show_status2($data); return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			$languages = new Array();
			
			$x = 0;
			$xml.find('language').each(function(){
				$languages[$x] = new Array();
				$languages[$x]['title'] = $(this).find('title').text();
				$languages[$x]['id'] = $(this).find('id').text();
				$languages[$x]['in_site'] = $(this).find('in_site').text();
				$x++;
			});			
			
			var site_details = '<table style="width:100;"><tr><td style="border:none; vertical-align:top;"><ul id="list_languages" class="orderlist language_list" style="width:150px;">';
			site_details += '	<li class="header" style="width:150px;">verfügbare Sprachen</li>';
			
			for ( $i=0;$i<$x;$i++ )
			{
				$style = '';
				if ( $languages[$i]['in_site'] == 1 )
				{
					$style = 'display:none;';
				}
				site_details += '	<li id="dialog_language_'+$languages[$i]['id']+'" class="dialog_ui_languages" value="'+$languages[$i]['id']+'" style="background-color:white; cursor:pointer; width:150px; '+$style+'">'+$languages[$i]['title']+'</li>';
			}
			
			site_details += '</ul></td>';
			
			site_details += '<td style="border:none; width:116px;">';
			site_details += '	<div style="margin-left:14px;">';
			site_details += '		<button id="button_add_language" class="language_dialog_button">></button>';	
			site_details += '		<button id="button_remove_language" class="language_dialog_button" style="margin-left:34px;"><</button>';
			site_details += '	</div>';
			site_details += '	<div style="margin-left:14px;">';
			site_details += '		<button id="button_add_all" class="language_dialog_button" style="padding-left:4px;">>></button>';	
			site_details += '		<button id="button_remove_all" class="language_dialog_button" style="padding-left:4px; margin-left:34px;"><<</button>'
			site_details += '	</div>';
			site_details += '</td>';
			
			site_details += '<td style="border:none; vertical-align:top;"><ul id="list_site_languages" class="orderlist language_list" style="width:150px;">';
			site_details += '	<li class="header" style="width:150px;">zugewiesene Sprachen</li>';
			
			for ( $i=0;$i<$x;$i++ )
			{
				if ( $languages[$i]['in_site'] == 1 )
				{
					site_details += '	<li id="dialog_site_language_'+$languages[$i]['id']+'" class="dialog_ui_languages" value="'+$languages[$i]['id']+'" style="background-color:white; cursor:pointer; width:150px;">'+$languages[$i]['title']+'</li>';
				}
			}
			site_details += '</ul></td></tr></table>';
			
			$("#dialog_change_languages").html(site_details);
			
			$("#dialog_change_languages").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { change_language(site_id); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Sprachen zuweisen",
				width:510,
				height:420
			});
			wait_dialog_hide();
	}*/
	
	function remove_language()
	{
		$( "#list_site_languages li[class*='marked']" ).each(function(){
			$(this).css('background-color','white');
			$(this).css('color','black');
			
			$id = $(this).attr('id');
			$id = $id.split('_');
			$(this).remove();					
			
			$("#dialog_language_"+$id[3]).css('display','');
		});

		$( "#list_languages").html( $( "#list_languages" ).html() );
		
		$( "#list_site_languages").html( $( "#list_site_languages" ).html()  );
		
		$(".dialog_ui_languages").click(function(){
			mark_language($(this));
		});	
	}
	
	function add_language()
	{
		var site_language_entry = '';
		$( "#list_languages li[class*='marked']" ).each(function(){
			site_language_entry = $( "#list_site_languages" ).html();
				
			$row_id = $(this).attr('id');
			$row_id = "#"+$row_id;
			$id = $row_id.split('_');
			$value = $($row_id).attr('value');
			site_language_entry += '<li id="dialog_site_language_'+$id[2]+'" style="background-color:white; color:black; cursor:pointer; width:150px;" class="dialog_ui_languages" value="'+$value+'" onClick="Javascript:mark_language('+$value+');">'+$($row_id).html()+'</li>';
			
			$( "#list_site_languages" ).html( site_language_entry );
			
			$($row_id).css('background-color','white');
			$($row_id).css('color','black');
			$($row_id).removeClass('marked');
			$($row_id).css('display','none');
			$( "#list_languages" ).html( $( "#list_languages" ).html() );

		});

		$(".dialog_ui_languages").click(function(){
			mark_language($(this));
		});	
	}
	
	function remove_all_language()
	{
		$("#list_site_languages > li").each(function()
	  	{ 
			if (!$(this).hasClass('header'))
			{
				$(this).remove();
			}
		});
		
		$("#list_languages > li").each(function()
	  	{ 
			$(this).css('display','');
		});
	}
	
	function add_all_language()
	{
		$("#list_site_languages > li").each(function()
	  	{ 
			if (!$(this).hasClass('header'))
			{
				$(this).remove();
			}
		});
		
		var site_language_entry = '<li class="header" style="width:150px;">zugewiesene Sprachen</li>';
		$("#list_languages > li").each(function()
	  	{ 
			if (!$(this).hasClass('header'))
			{
				$row_id = $(this).attr('id');
				$row_id = "#"+$row_id;
				$id = $row_id.split('_');
				$value = $($row_id).attr('value');
				site_language_entry += '<li id="dialog_site_language_'+$id[2]+'" style="background-color:white; color:black; cursor:pointer; width:150px;" class="dialog_ui_languages" value="'+$value+'" onClick="Javascript:mark_language('+$value+');">'+$($row_id).html()+'</li>';
				
				$( "#list_site_languages" ).html( site_language_entry );
				$(this).css('display','none');
			}
		});
	}
	
	$( document ).ready(function() {
		 
		wait_dialog_show();
		PostData = new Object();
		PostData['API'] = "cms";
		PostData['APIRequest'] = "SitesGet_neu";
		soa2(PostData, "showview");
	
	 });
</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > Seiten';
	echo '</p>';
	echo '<h1>Seiten</h1>';

	echo '<div id="view"></div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>