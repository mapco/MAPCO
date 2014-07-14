<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script type="text/javascript">
		
		// Inhalt der Häufigkeitstabelle
		var errortypes = new Array();
		
		// Fehlertypen (IDIMS, EBay etc.)
		var types = new Array();
		
		// Sortierrichtung
		var direct = 'DESC';
		
		// jüngst aufgetretener Fehler
		var latest = 0;
		
		// Werte aus dem Filterfeld
		var filter_col = '';
		var filter_value = '';
		
		// html für Pagination - Auswahl
		var pagination_select_desc = 'pro Seite';
		var pagination_select = '			<select id="analyse_menu_pagination" style="align:center;">';
		pagination_select += '				<option value="50" selected>50</option>';
		pagination_select += '				<option value="100">100</option>';
		pagination_select += '				<option value="200">200</option>';
		pagination_select += '			</select>';

		var per_page = 50;
		
		var page = 1;
			
		// Bestätigungsdialog über Löschung von aufgetretenen Fehlern
		function show_dialog_delete_errors(id_errorcode)
		{
			if ($("#dialog_delete_errors").length == 0)
			{
				var dialog_div = $('<div id="dialog_delete_errors"></div>');
				$("#content").append(dialog_div);
			}
			$("#dialog_delete_errors").empty();
			
			var dialog_content = 'Die aufgetretenen Fehler mit dem Fehlercode '+id_errorcode +' wurden gelöscht!';
			
			$("#dialog_delete_errors").append(dialog_content);
			
			$("#dialog_delete_errors").dialog({	
				buttons:
				[
					{ text: "<?php echo t("OK"); ?>", click: function() {$(this).dialog("close");} }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Aufgetretene Fehler wurden gelöscht!"); ?>",
				width:300
			});
		}
			
		// Serviceaufruf zum entfernen aller aufgetretenene Fehler eines bestimmten Fehlertyps und -Codes
//		function error_remove($id_errortype, $id_error)
		function error_remove(id_errorcode)
		{
			$confirm=confirm("Sind Sie sicher, dass der Fehler gelöscht werden kann?");
			if( $confirm )
			{
//				$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ErrorRemove", id_errortype:$id_errortype, id_error:$id_error }, function($data)
				$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorDelete", id_errorcode:id_errorcode }, function($data)
				{
					var delete_message = '';
					//show_status2($data);return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
					
					if($xml.find('geloescht').length != 0)
					{
						show_dialog_delete_errors(id_errorcode);
						change_view(1);
					}
				});
			}
		}
		
		// formatiert den Timestamp zu einem lesbarem Datum		
		function format_time(tstamp)
		{
			var time_options = {
				weekday: "long", year: "numeric", month: "short",
				day: "numeric", hour: "2-digit", minute: "2-digit"
			};
			var time = new Date( tstamp*1000);
			time = time.toLocaleDateString("en-US",time_options);
			return time;
		}
		
		// wechselt zwischen Kurztext und ausführlicher Beschreibung in der Häufigkeitstabelle
		function show_longMsg(row)
		{	
			var id = "#Msg_"+row;
			if ( $(id).hasClass('ext') ) 
			{
				Msg = errortypes[row]['shortMsg'];				
			}
			else
			{
				Msg = errortypes[row]['longMsg'];	
			}
			$(id).empty().append(Msg);
			$(id).toggleClass('ext');
		}
		
		// erzeugt die Haufigkeitstabelle anhand der Daten aus der DB unter Berücksichtigung von Filter- und Sucheingaben
		function build_counting_table(search_value,target_page)
		{
			/*page = target_page;
			
			if ( $("#analyse_menu_pagination :selected").text() != '' )
			{
				per_page = $("#analyse_menu_pagination :selected").text();
			}
			
			$("#th_pagination_select").empty().append(pagination_select_desc);
			$("#td_pagination_select").empty().append(pagination_select);
			$("#analyse_menu_pagination option[value=" + per_page +"]").prop("selected", "selected");
		
			$( "#analyse_menu_pagination" ).change(function() {
				build_counting_table(search_value,page);
			});*/
			
			var cursor_pointer = "cursor:pointer;";
			var func_close_ext_rows = "close_ext_rows()";
		
			var output = '<table id="analyse_table">';
			output += '	<thead id="analyse_table_head">';
			output += '		<tr>';
			output += '			<th onclick="'+func_close_ext_rows+'" style="'+cursor_pointer+'">Anzahl</th>';
			output += '			<th onclick="'+func_close_ext_rows+'" style="'+cursor_pointer+'">Zeitpunkt des letzten Auftretens</th>';
			output += '			<th onclick="'+func_close_ext_rows+'" style="'+cursor_pointer+'">Fehlercode</th>';
			output += '			<th onclick="'+func_close_ext_rows+'" style="'+cursor_pointer+'">Fehlertyp</th>';
			output += '			<th onclick="'+func_close_ext_rows+'" style="'+cursor_pointer+'">Typ</th>';
			output += '			<th onclick="'+func_close_ext_rows+'" colspan="2" style="'+cursor_pointer+'">Beschreibung</th>';
			output += '		</tr>';
			output += '	</thead>';
			output += '	<tbody id="analyse_table_body" class="hover">'; 

			var e = 0;	
			var tlength = errortypes.length;

			for (var i = 0; i < tlength; i++)
			{  
				if ( (filter_col == '' && filter_value == 0) || errortypes[i][filter_col] == filter_value )
				{	
					var patt=new RegExp(search_value,'i');
					
					if ( search_value == '' || errortypes[i]['errorcode'].search(search_value) != -1 || errortypes[i]['shortMsg'].search(patt) != -1 || errortypes[i]['longMsg'].search(patt) != -1) 
					{
						/*if ()
						{*/
							var code_tooltip = "Zeige die letzten 5 aufgetretenen Fehler";
							var Msg_tooltip = "Ansicht zwischen Kurztext und langer Beschreibung mit Linksklick wechseln";
							var list_error_tooltip = "Aufgetretene Fehler dieses Fehlercodes anzeigen";
							
							output += '	<tr id="row_'+i+'">';
							output += '		<td onclick="list_errors('+i+',0,0,0);" style="'+cursor_pointer+'" title="'+code_tooltip+'">';
							output += 			errortypes[i]['codecount'];
							output += '		</td>';
							output += '		<td onclick="list_errors('+i+',0,0,0);" style="'+cursor_pointer+'" title="'+code_tooltip+'">';
							output += 			errortypes[i]['time'];
							output += '		</td>';
							output += '		<td onclick="list_errors('+i+',1,1,0);" style="'+cursor_pointer+'" title="'+list_error_tooltip+'">'+errortypes[i]['errorcode']+'</td>';
							output += '		<td>'+errortypes[i]['error_type']+'</td>';
							output += '		<td>'+errortypes[i]['type']+'</td>';
							output += '		<td id="Msg_'+i+'" onclick="show_longMsg('+i+');" style="'+cursor_pointer+'; border-right:0;" title="'+Msg_tooltip+'">';
							output += 			errortypes[i]['shortMsg'];
							output += '		</td>';
							output += '		<td style="border-left:0;">';
							output += '			<img src="<?php echo PATH; ?>images/icons/24x24/remove.png" onclick="error_remove('+errortypes[i]['errorcode']+');" title="Aufgetretene Fehler dieses Code und Typs löschen">';
							output += '		</td>';
							output += '	</tr>';
							e = 1;
						//}
					}
				}
			}
			
			if( e == 0 )
			{
				output += "	<tr id='row_"+i+"'>";
				output += "		<td colspan=6>Keine Einträge zu ihrer Suche gefunden!</td>";
				output += "	</tr>";
			}

			output += '	</tbody>';
			output += '</table>';
		
			$("#erroranalyse_table").empty().append(output);
				
	 		$("#analyse_table").tablesorter({sortList: [[0,1]]});
		}
		
		// liest das Feld für den Filter aus und triggert einen Neuaufbau der Tabelle
		function filter_table(view)
		{
			if ( $('select#field_filter_types :selected').val() > 0 )
			{	
				filter_value = $('select#field_filter_types :selected').val();

				switch(view)
				{
					case 1: filter_col = 'error_type_id';
							count_errors(); 
							break;
					case 2: filter_col = 'error_type';
							code_search(1);
							break;
					case 3: filter_col = 'errortype_id';
							list_errors(false, 2, 1, $( "#field_search_value" ).val());
							break;
				}
			}
			else
			{
				filter_col = '';
				filter_value = '';
				switch(view)
				{
					case 1: count_errors(); 
							break;
					case 2: code_search(1);
							break;
					case 3: list_errors(false,2,1,0);
							break;
				}				
			}
		}
	
		// fügt in die Häufigkeitstabelle Zeilen mit den jüngsten 5 aufgetretenen Fehlern eines bestimmten Fehlercodes ein
		function list_errors(list_row, mode, target_page, search_value)
		{	
			if (list_row != '')
			{
				build_table_menu(3);
			}
				
			var type_id = '';
			var code_id = '';

			if ( mode == 1)
			{ 
				var text1 = 3;
				$("#analyse_menu_view option[value=" + text1+"]").prop("selected", "selected");
			}
			
			if ( mode != 0)
			{
				page = target_page;
			}
			
			if ( $("select#analyse_menu_pagination :selected").text() != '' )
			{
				per_page = $("select#analyse_menu_pagination :selected").text();
			}
			
			$("#th_pagination_select").empty().append(pagination_select_desc);
			$("#td_pagination_select").empty().append(pagination_select);
			$("#analyse_menu_pagination option[value=" + per_page+"]").prop("selected", "selected");
		
			$( "#analyse_menu_pagination" ).change(function() {
				list_errors(list_row, mode, page,'');
			});
			
			if ( mode != 2 )
			{		
				type_id = errortypes[list_row]['error_type_id'];
				code_id = errortypes[list_row]['errorcode'];
			}

			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorsGet", filter_col:filter_col, filter_value:filter_value, type_id:type_id, code_id:code_id, mode:mode, page:page, per_page:per_page, search_value:search_value }, function($data)
			{ 	
				//show_status2($data);return;				
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}

				if ( mode == 0)
				{
					var row = '#row_'+list_row;
					if ($('#row_ext_'+list_row).length != 0)
					{
						$('.row_ext').remove();
					}
					else
					{
						$('.row_ext').remove();
						var error_table = '<tr id="row_ext_'+list_row+'" class="row_ext">';
						error_table += '	<th colspan=2>Zeitpunkt des Auftretens</th>';
						error_table += '	<th>Fehlercode</th>';
						error_table += '	<th>Datei</th>';
						error_table += '	<th>Zeile</th>';
						error_table += '	<th>Beschreibung</th>';
						error_table += '<tr>';
	
						var text = '';
						$xml.find('error').each(function(){
							error_table += '<tr class="row_ext">';
							error_table += '	<td colspan=2>'+format_time($(this).find('time').text())+'</td>';
							error_table += '	<td>'+$(this).find('error_id').text()+'</td>';
							error_table += '	<td>'+$(this).find('file').text()+'</td>';
							error_table += '	<td>'+$(this).find('line').text()+'</td>';

							text = $(this).find('text').text();
							error_table += '	<td>'+text+'</td>';
							error_table += '</tr>';
						});
						error_table += '<tr class="row_ext"><th colspan=6></th></tr>';
						$(row).after(error_table);
					}
				}
				else if (mode == 1 || mode ==2)
				{	
					pages = $xml.find('pages').text();
					
					var page_nav = '';
					
					if (pages > 1)
					{
						if ( page >5 && pages>10 )
						{
							page_nav += '<a href="#" onclick="list_errors('+list_row+', '+mode+', 1,0);">...</a> ';		
						}
						for(x=1;x<=pages;x++)
						{
							if ( x>page-5 && x<page+5 )
							{
								( x == page ) ? page_nav += x+' ' : page_nav += '<a href="#" onclick="list_errors('+list_row+', '+mode+', '+x+',0);">'+x+'</a> ';	
							}
						}
						if ( page < pages-5 && pages>10 )
						{
							page_nav += '<a href="#" onclick="list_errors('+list_row+', '+mode+', '+pages+',0);">...</a> ';		
						}
						page_nav += '<br />';
					}
					
					var error_table = page_nav;
					error_table += '<table>';
					error_table += '	<tr id="row_ext_'+list_row+'" class="row_ext">';
					error_table += '		<th colspan=2>Zeitpunkt des Auftretens</th>';
					error_table += '		<th>Fehlercode</th>';
					error_table += '		<th>Datei</th>';
					error_table += '		<th>Zeile</th>';
					error_table += '		<th>Beschreibung</th>';
					error_table += '	<tr>';

					$xml.find('error').each(function(){
						
						error_table += '<tr class="row_ext">';
						error_table += '	<td colspan=2>'+format_time($(this).find('time').text())+'</td>';
						error_table += '	<td>'+$(this).find('error_id').text()+'</td>';
						error_table += '	<td>'+$(this).find('file').text()+'</td>';
						error_table += '	<td>'+$(this).find('line').text()+'</td>';
						
						text = $(this).find('text').text();
						
						error_table += '	<td>'+text+'</td>';
						error_table += '</tr>';
					});
					error_table += '</table>';
					$("#erroranalyse_table").empty().append(error_table);
				}
				wait_dialog_hide();
			});
		}

		// Serviceaufruf und Darstellung zur Zählung der Häufigkeit der Fehlercodes
		function count_errors()
		{	
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorsCount", latest:latest }, function($data)
			{
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				var z = 0;
				$xml.find('errortypes').each(function(){
					var outer = $(this);
					outer.find('errorcodes').each(function(){
						errortypes[z] = new Array();
						errortypes[z]['error_type_id'] = $(this).find('error_type_id').text();
						errortypes[z]['error_type'] = $(this).find('error_type').text();
						errortypes[z]['errorcode'] = $(this).find('errorcode').text();
						errortypes[z]['codecount'] = $(this).find('codecount').text();
						errortypes[z]['type'] = $(this).find('type').text();
						errortypes[z]['shortMsg'] = $(this).find('shortMsg').text();
						errortypes[z]['longMsg'] = $(this).find('longMsg').text();
						errortypes[z]['time'] = format_time($(this).find('time').text());

						if (types[errortypes[z]['error_type_id']] == undefined || types[errortypes[z]['error_type_id']].length == 0)
						{ 
							types[errortypes[z]['error_type_id']] = new Array();
							types[errortypes[z]['error_type_id']]['id'] = errortypes[z]['error_type_id'];
							types[errortypes[z]['error_type_id']]['title'] = errortypes[z]['error_type'];
						}
						
						if ( latest < errortypes[z]['time'] )
						{
							latest = errortypes[z]['time'];	
						}
						z++;
					});
				});

				build_counting_table('');
				wait_dialog_hide();
			});
			
		}
		
		// Serviceaufruf und Darstellung zur Suche eines Fehlercodes
		function code_search(target_page)
		{
			page = target_page;
			
			if ( $("select#analyse_menu_pagination :selected").text() != '' )
			{
				per_page = $("select#analyse_menu_pagination :selected").text();
			}
			
			$("#th_pagination_select").empty().append(pagination_select_desc);
			$("#td_pagination_select").empty().append(pagination_select);
			$("#analyse_menu_pagination option[value=" + per_page+"]").prop("selected", "selected");
		
			$( "#analyse_menu_pagination" ).change(function() {
				code_search(1);
			});
			
			wait_dialog_show();
			
			var search_value = $( "#field_search_value" ).val();
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorCodeSearch", search_value:search_value, page:page, per_page:per_page, filter_col:filter_col, filter_value:filter_value }, function($data)
			{
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
				pages = $xml.find('pages').text();
					
				var page_nav = '';
				
				if (pages > 1)
				{ 
					if ( page >5 && pages>10 )
					{
						page_nav += '<a href="#" onclick="code_search(1);">...</a> ';		
					}
					for(x=1;x<=pages;x++)
					{
						if ( x>page-5 && x<page+5 )
						{
							( x == page ) ? page_nav += x+' ' : page_nav += '<a href="#" onclick="code_search('+x+');">'+x+'</a> ';	
						}
					}
					if ( page < pages-5 && pages>10 )
					{
						page_nav += '<a href="#" onclick="code_search('+pages+');">...</a> ';		
					}
					page_nav += '<br />';
				}
				
				var errorcodes = '';				
				var z = 0;
				
				var errorcodes = page_nav;
				errorcodes += '<table id="analyse_table">';
				errorcodes += '	<tr>';
				errorcodes += '		<th>Fehlercode</th>';
				errorcodes += '		<th>Fehlertyp</th>';
				errorcodes += '		<th>Fehlertyp ID</th>';				
				errorcodes += '		<th>Art</th>';
				errorcodes += '		<th>Kurztext</th>';	
				errorcodes += '		<th>Beschreibung</th>';
				errorcodes += '	</tr>';
				errorcodes += '	</thead>';
				errorcodes += '	<tbody id="analyse_table_body" class="hover">'; 
				
				($xml.find('codes').length==0)? errorcodes += '<tr><td colspan=5>Keine Fehlercodes gefunden!<td></tr>': errorcodes += '';
				
				$xml.find('codes').each(function(){
					errorcodes += '<tr>';
					errorcodes += '	<td>'+$(this).find('errorcode').text()+"</td>";
					errorcodes += '	<td>'+$(this).find('error_type').text()+"</td>";
					errorcodes += '	<td>'+$(this).find('errortype_id').text()+"</td>";
					errorcodes += '	<td>'+$(this).find('type').text()+"</td>";
					errorcodes += '	<td>'+$(this).find('shortMsg').text()+"</td>";
					errorcodes += '	<td>'+$(this).find('longMsg').text()+' ';
					errorcodes += '<img src="<?php echo PATH; ?>images/icons/24x24/add.png" class="button_list_errorcode_mails" code="'+$(this).find('errorcode').text()+'" type="'+$(this).find('errortype_id').text()+'" title="Zeige zugeordnete Mails" alt="Zeige zugeordnete Mails" style="float:right;">';
					errorcodes += '	</td>';
					errorcodes += '</tr>';
					z++;
				});
				
				($xml.find('message').length > 0) ? $("#analyse_message").empty().append($xml.find('message').text()) : $("#analyse_message").empty();
				
				errorcodes += '	</tbody>';
				errorcodes += '</table>';
				
				$("#erroranalyse_table").empty().append(errorcodes);
				
				$(".button_list_errorcode_mails").click(function(){
					dialog_errorcode_mails($(this).attr('code'), $(this).attr('type'));	
				});
										
				wait_dialog_hide();
			});
		}
		
		function dialog_errorcode_mails(errorcode, errortype)
		{
			if ($("#dialog_errorcode_mails").length == 0)
			{
				var dialog_div = $('<div id="dialog_errorcode_mails"></div>');
				$("#content").append(dialog_div);
			}
			$("#dialog_errorcode_mails").empty();
			
			var where = 'WHERE errorcode_id='+errorcode+' AND errortype_id='+errortype;
			
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", table:'cms_errorcodes_mails', db:'dbweb', where:where, select:'mail,id' }, function($data)
			{
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				var dialog_content = '	<table>';
				dialog_content += '		<tr>';
				dialog_content += '			<th style="width:200px;">Mailadressen</th>';
				dialog_content += '			<td style="border:none;"><input id="input_new_mail" style="float:right;" /></td>';
				dialog_content += '		</tr>';
				
				if ($xml.find('num_rows').text() >0)
				{
					var x=0;
					$xml.find('cms_errorcodes_mails').each(function(){
						dialog_content += '		<tr>';
						dialog_content += '			<td style="width:200px;">'+$(this).find('mail').text();
						dialog_content += '<img src="<?php echo PATH; ?>images/icons/24x24/remove.png" value="'+$(this).find('id').text()+'" class="button_remove_mail" title="Fehlercode hinzufügen" style="float:right;">';
						dialog_content += '</td>';
						if(x==0){
							dialog_content += '	<td style="width:200px; border:none;"><button id="button_add_mail" class="ui-button ui-button-txt-only">Mail-Adresse zuordnen</button></td>';
						}	
						dialog_content += '		</tr>';

						x++;
					});
				}
				else
				{
					dialog_content += '		<tr>';
					dialog_content += '			<td style="width:200px;">Keine Mail zugeordnet!</td>';
					dialog_content += '			<td style="width:200px; border:none;"><button id="button_add_mail" class="ui-button ui-button-txt-only" style="float:right;">Mail-Adresse zuordnen</button></td>';
					dialog_content += '		</tr>';
				}
				dialog_content += '	</table>';
				dialog_content += '	<div style="width:190px;>';
				dialog_content += '		';
				dialog_content += '		';
				dialog_content += '	</div>';
				dialog_content += '</div>';
				
				$("#dialog_errorcode_mails").append(dialog_content);
				
				$("#button_add_mail").click(function(){
					add_mail_to_errorcode(errorcode, errortype);
				});
				
				$(".button_remove_mail").click(function(){
					remove_mail($(this).attr('value'), errorcode, errortype);
				});
			});
			
			$("#dialog_errorcode_mails").dialog({	
				buttons:
				[
					{ text: "<?php echo t("Schließen"); ?>", click: function() {$(this).dialog("close"); } }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Mailliste"); ?>",
				width:400
			});
		}
		
		function remove_mail(code_mail_id, errorcode, errortype)
		{
			wait_dialog_show('Entferne Email-Zuordnung');
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorCodeMailsRemove", code_mail_id:code_mail_id }, function($data)
			{
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				dialog_errorcode_mails(errorcode, errortype);
				wait_dialog_hide();
			});
		}
		
		function add_mail_to_errorcode(errorcode, errortype)
		{
			wait_dialog_show('Füge Mail-Zuordung hinzu');
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorCodeMailsAdd", errorcode:errorcode, errortype:errortype, mail:$("#input_new_mail").val() }, function($data)
			{
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
				dialog_errorcode_mails(errorcode, errortype);
				wait_dialog_hide();
			});
		}
		
		// entfernt die eingefügte Fehleranzeige in der Häufigkeitstabelle
		function close_ext_rows()
		{ 
			$('.row_ext').remove();	
		}
		
		// Bestätigungsdialog zu Fehlertyp-Erstellung
		function show_dialog_type_created(title)
		{
			if ($("#dialog_type_created").length == 0)
			{
				var dialog_div = $('<div id="dialog_type_created"></div>');
				$("#content").append(dialog_div);
			}
			$("#dialog_type_created").empty();
				
			var message = 'Der Fehlertyp mit der ID ' + title + ' wurde erstellt.';
				
			$("#dialog_type_created").append(message);
				
			$("#dialog_type_created").dialog({	
				buttons:
				[
					{ text: "<?php echo t("OK"); ?>", click: function() {$(this).dialog("close");} }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Errorcode angelegt!"); ?>",
				width:300
			});
			code_search(1);
		}
		
		// Bestätigungsdialog zu Fehlercode-Erstellung
		function show_dialog_code_created(created_id, type_id)
		{
			if ($("#dialog_code_created").length == 0)
			{
				var dialog_div = $('<div id="dialog_code_created"></div>');
				$("#content").append(dialog_div);
			}
			$("#dialog_code_created").empty();
				
			var message = '<div>Der Fehlercode mit der ID ' + created_id + ' wurde erstellt.</div>';
			message += '<div><input type="text" style="width:125px;" value="show_error('+created_id+', '+type_id+', __FILE__, __LINE__);"></div>';
				
			$("#dialog_code_created").append(message);
				
			$("#dialog_code_created").dialog({	
				buttons:
				[
					{ text: "<?php echo t("OK"); ?>", click: function() {$(this).dialog("close"); } }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Errorcode angelegt!"); ?>",
				width:300
			});
			code_search(1);
		}
		
		function create_errortype()
		{
			var title = $("#dialog_create_errortype_input").val();
			
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorTypeAdd", title:title }, function($data)
			{
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
				
				show_dialog_type_created($xml.find('errortype_id').text());
			});
		}
		
		function show_dialog_create_errortype(from_dialog)
		{
			if ($("#dialog_create_errortype").length == 0)
			{
				var dialog_div = $('<div id="dialog_create_errortype"></div>');
				$("#content").append(dialog_div);
			}
			$("#dialog_create_errortype").empty();
			
			var dialog_content = '<label>Bezeichnung: </label><input type="text" id="dialog_create_errortype_input">';
				
			$("#dialog_create_errortype").append(dialog_content);
			
			$("#dialog_create_errortype").dialog({	
				buttons:
				[
					{ text: "<?php echo t("Erstellen"); ?>", click: function() {create_errortype();	$(this).dialog("close"); change_view(1);}	},
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Neuen Fehlertyp anlegen"); ?>",
				width:500
			});
		}
		
		// der Serviceaufruf zum Erstellen eines Fehlercodes
		function create_code()
		{
			var error_type_id = $("#dialog_field_error_type").val();
			var type = $("#dialog_field_type").val();
			var shortMsg = $("#dialog_field_shortMsg").val();
			var longMsg = $("#dialog_field_longMsg").val();
			
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorCodeAdd", error_type_id:error_type_id, type:type, shortMsg:shortMsg, longMsg:longMsg }, function($data)
			{
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
				
				show_dialog_code_created($xml.find('errorcode_id').text(), error_type_id);
			});
		}
		
		// erzeugt eine Eingabemaske zum erstellen eines Fehlercodes
		function show_dialog_create_errorcode()
		{
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorTypeGet" }, function($data)
			{
				if ($("#dialog_create_errorcode").length == 0)
				{
					var dialog_div = $('<div id="dialog_create_errorcode"></div>');
					$("#content").append(dialog_div);
				}
				$("#dialog_create_errorcode").empty();
				
				
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
			
				var dialog_content = '<table>';
				dialog_content += '	<tr>';
				dialog_content += '		<td>';
				dialog_content += '			<label>Fehlertyp</label>';
				dialog_content += '		</td>';
				dialog_content += '		<td>';
				dialog_content += '			<select id="dialog_field_error_type" style="width:100%;">';		
					
				var count_types = types.length;
				var error_type_id = '';
				var error_type_title = '';
				var filter_options = '';

				$xml.find("errortype").each(function(){
					dialog_content += '	<option value="'+$(this).find('id_errortype').text()+'">'+$(this).find('title').text()+'</option>';
				});
				
				dialog_content += '			</select>';
				dialog_content += '			<img src="<?php echo PATH; ?>images/icons/24x24/add.png" id="dialog_add_errortype" title="Fehlercode hinzufügen">';
				dialog_content += '		</td>';
				dialog_content += '	</tr>';
				dialog_content += '	<tr>';
				dialog_content += '		<td>';
				dialog_content += '			<label>Art</label>';
				dialog_content += '		</td>';
				dialog_content += '		<td>';
				dialog_content += '			<input id="dialog_field_type" style="width:100%;">';
				dialog_content += '		</td>';
				dialog_content += '	</tr>';
				dialog_content += '	<tr>';
				dialog_content += '		<td>';
				dialog_content += '			<label>Kurztext</label>';
				dialog_content += '		</td>';
				dialog_content += '		<td>';
				dialog_content += '			<textarea id="dialog_field_shortMsg" style="width:350px; min-height:75px;"></textarea>';
				dialog_content += '		</td>';
				dialog_content += '	</tr>';
				dialog_content += '	<tr>';
				dialog_content += '		<td>';
				dialog_content += '			<label>Beschreibung</label>';
				dialog_content += '		</td>';
				dialog_content += '		<td>';
				dialog_content += '			<textarea id="dialog_field_longMsg" style="width:350px; min-height:100px;"></textarea>';
				dialog_content += '		</td>';
				dialog_content += '	</tr>';
				dialog_content += '</table>';
				
				$("#dialog_create_errorcode").append(dialog_content);
				
				$("#dialog_add_errortype").click(function(){
					show_dialog_create_errortype();
				});
				
				$("#dialog_create_errorcode").dialog({	
					buttons:
					[
						{ text: "<?php echo t("Erstellen"); ?>", click: function() { create_code(); $(this).dialog("close");} },
						{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
					],
					closeText:"<?php echo t("Fenster schließen"); ?>",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"<?php echo t("Neuen Fehlercode anlegen"); ?>",
					width:500
				});
			});
		}
			
		// erzeugt das die Such- und Filterfelder
		function build_table_menu(view)
		{			
			var menu_field_filter ='';
			var menu_field_search = '';
		//	if(view=="1" || view == "2")
		//	{
				menu_field_filter += "<select id='field_filter_types' onchange='filter_table("+view+");'>";
				menu_field_filter += "	<option id='filter_option_"+0+"' value='0'>Alle anzeigen</option>";
				$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ErrorTypeGet" }, function($data)
				{
					//show_status2($data);
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
									
					$xml.find('errortype').each(function(){ 
						menu_field_filter += "	<option value='"+$(this).find('id_errortype').text()+"' id='filter_option_"+$(this).find('id_errortype').text()+"'>" + $(this).find('title').text() + "</option>";
					});
					menu_field_filter += "</select>";
					$("td#menu_col_filter").empty().append(menu_field_filter);
				});
				
				$("#th_col_filter").empty().append('Filter');
		/*	}
			else
			{
				$("#th_col_filter").empty();
				$("td#menu_col_filter").empty()
			}*/
			menu_field_search += "<input id='field_search_value'>";
			
			$("td#menu_col_search").empty().append(menu_field_search);
			
			if(view=="1")
			{		
				$( "#field_search_value" ).keyup(function() { 
  					build_counting_table($('#field_search_value').val(),1);
				});	
			}
			else if (view=="2")
			{
				$('#field_search_value').keyup(function(e) {
					if(e.keyCode == 13) {
						code_search(1);
					}
				});	
			}
			else if (view=="3")
			{
				$('#field_search_value').keyup(function(e) {
					if(e.keyCode == 13) { 
						list_errors(false, 2, 1, $( "#field_search_value" ).val());
					}
				});	
			}
		}
		
		// wechselt zwischen Anzeige der Häufigkeit(1) und Codesuche(2)
		function change_view(view)
		{ 
			build_table_menu(view);
			$("#filter_option_0").prop('selected', 'selected'); 
			filter_col = '';
			filter_value = '';
			$("#analyse_message").empty();
			switch(view)
			{
				case "1": $("#th_pagination_select").empty();
						$("#td_pagination_select").empty();
						count_errors(); 
						break;
				case "2": code_search(1);
						break;
				case "3": list_errors(false,2,1,0);
						break;
			}
		}
		
		// erzeugt die grundlegende Struktur der Seite
		function build_error_analyse(view)
		{	
			var analyse_view = '<table style="display:inline;">';
			analyse_view += '	<tr>';
			analyse_view += '		<td style="border:0;">Ansicht wechseln</td>';
			analyse_view += '		<td id="th_pagination_select" style="border:0;"></td>';
			analyse_view += '		<td id="th_col_filter" style="border:0;">Filter</td>';
			analyse_view += '		<td style="border:0;">Suche</td>';
			analyse_view += '	</tr>';
			analyse_view += '	<tr>';
			analyse_view += '		<td style="border:0;">';
			analyse_view += '			<select id="analyse_menu_view" style="align:center;">';
			analyse_view += '				<option value="1">nach Häufigkeit</option>';
			analyse_view += '				<option value="2">Fehlercode suchen</option>';
			analyse_view += '				<option value="3">Fehlerlog anzeigen</option>';
			analyse_view += '			</select>';
			analyse_view += '		</td>';
			analyse_view += '		<td id="td_pagination_select" style="border:0;"></td>';
			analyse_view += '		<td id="menu_col_filter" style="border:0;"></td>';
			analyse_view += '		<td id="menu_col_search" style="border:0;"></td>';
			analyse_view += '		<td id="analyse_message" style="color:red; border:0;"></td>';
			analyse_view += '		<td style="border:0;">';
			analyse_view += '			<button id="add_errorcode" style="margin-right:10px;">Fehlercode hinzufügen</button>';
			analyse_view += '		</td>';
			analyse_view += '		<td style="border:0;">';
			analyse_view += '			<button id="add_errortype">Fehlertyp hinzufügen</button>';
			analyse_view += '		</td>';
			analyse_view += '	</tr>';			
			analyse_view += '</table>';
			analyse_view += '<div id="erroranalyse_table"></div>';
				
			$("#erroranalyse").empty().append(analyse_view);
			
			$("#analyse_menu_view").change(function(){
				change_view($("#analyse_menu_view").val());
			});
			
			$("#add_errorcode").click(function(){
				show_dialog_create_errorcode();
			});
			
			$("#add_errortype").click(function(){
				show_dialog_create_errortype();
			});
					
			build_table_menu(1);
			count_errors();
		}
		
		$(function(){build_error_analyse()});
		</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">'.t("Backend").'</a>';
	echo ' > <a href="backend_administration_index.php?lang='.$_GET["lang"].'">'.t("Administration").'</a>';
	echo ' > Fehleranalyse';
	echo '</p>';
	
	echo '<h1>Fehleranalyse';
	echo '	<a href="'.PATH.'backend_administration_errorcodes.php">Fehlermeldungen importieren</a>';
	echo '</h1>';

	echo '<div id="erroranalyse"></div>';
	
/*
	//error types
	echo '<table>';
	echo '	<tr><th colspan="2">Fehlertypen</th></tr>';
	echo '	<tr>';
	echo '		<th>ID</th>';
	echo '		<th>Titel</th>';
	echo '	</tr>';
	$results=q("SELECT * FROM cms_errortypes;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<tr>';
		echo '	<td>'.$row["id_errortype"].'</td>';
		echo '	<td>'.$row["title"].'</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	if( isset($_GET["id_errortype"]) )
	{
		$results=q("SELECT COUNT(*) AS Anzahl, error_id, errortype_id FROM cms_errors WHERE errortype_id=".$_GET["id_errortype"]." GROUP BY error_id ORDER BY Anzahl DESC;", $dbweb, __FILE__, __LINE__);
		echo '<table>';
		echo '	<tr><th colspan="2">Fehlermeldungen</th></tr>';
		echo '	<tr>';
		echo '		<th>Anzahl</th>';
		echo '		<th>Fehelernummer</th>';
		echo '		<th>Beschreibung</th>';
		echo '		<th>Optionen</th>';
		echo '	</tr>';
		while( $row=mysqli_fetch_array($results) )
		{
			echo '<tr>';
			echo '	<td>'.$row["Anzahl"].'</td>';
			echo '	<td>'.$row["error_id"].'</td>';
			$results2=q("SELECT * FROM cms_errorcodes WHERE errorcode='".$row["error_id"]."';", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			echo '	<td>'.$row2["shortMsg"].'</td>';
			echo '	<td><img alt="Fehler wurde behoben und kann gelöscht werden" onclick="error_remove('.$row["errortype_id"].', '.$row["error_id"].');" src="'.PATH.'images/icons/24x24/remove.png" style="cursor:pointer;" title="Fehler wurde behoben und kann gelöscht werden" /></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	*/
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>