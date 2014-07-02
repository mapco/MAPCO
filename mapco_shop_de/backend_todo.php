<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script type="text/javascript">
	
	var done_visible = 0;
	
	$(window).load(function(){
		view(0, 'latest');
	});
	
	function category_add(title, parent_id)
	{
		$('#new_cat_title').val('');
		$.post("<?php echo PATH; ?>soa2/", { API:"shop", APIRequest:"TodoCategoryAdd", title: title, parent_id: parent_id},
			function(data)
			{
				try
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack").text();
					if ( $ack=="Success" )
					{
						//view($('#view_select :selected').val(), parent_id);	
						view(parent_id);						
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message+'<br />'+data);
					return;
				}
			}
		);
	}
	
	function category_delete(cat_id, parent_id)
	{
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoCategoryRemove", id: cat_id}, 
			function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ($ack == "Success") {
						//view($('#view_select :selected').val(), parent_id);
						view(parent_id);
						return;
					}
				}
				catch(err)
				{
					show_status2(err.message+'<br /'+$data);
					return;
				}
			}
		);
	}
	
	function category_update(mode, ids, parent_id)
	{
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoCategoryUpdate", mode: mode, 'ids[]': ids}, 
			function($data)
			{
				try
				{
					view(parent_id, "", 0);
					return;
				}
				catch(err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
			}
		);
	}
	
	function task_add(cat_id, new_task_title, new_task_description, parent_id)
	{
		$('#new_task_title').val('');
		$('#new_task_description').val('');
		
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTaskAdd", title: new_task_title, description: new_task_description, cat_id: cat_id}, 
			function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ($ack=="Success") {
						$('#cat_new_'+cat_id).hide();
						tasks_view(cat_id);
						if (cat_id == "p")
							view(0, "p", 0);
						else
							view(parent_id, "", 0);
						return;
					}
				}
				catch(err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
			}
		);
	}
	
	function task_delete(id, cat_id, parent_id, private_user_id)
	{
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTaskRemove", id: id}, 
			function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ($ack=="Success") {
						if (cat_id == 0)
							tasks_all_view();
						else if(cat_id == "p")
							tasks_private_view(private_user_id);
						else
							tasks_view(cat_id);
						if(cat_id == "p")
							view(0, "p", 0, private_user_id);
						else
							view(parent_id, "", 0, private_user_id);
						return;
					}
				}
				catch(err)
				{
					show_status2(err.message+'<br /'+$data);
					return;
				}
			}
		);
	}
	
	function task_done_mail_dialog(id, task_title)
	{
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoContactsGet"}, 
			function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ($ack == "Success") {
						$('#message_dialog2').empty();
						var div_tree = $('<div id="menu" style="float: left"></div>');
						var div_message = $('<div id="div_message" style="float: left"></div>');
						var tr;
						var td;
						
						var table = $('<table style="width: 308px"></table>');
						tr = $('<tr></tr>');
						td = $('<td><?php echo T("Empfänger auswählen");?>:</td>');
						tr.append(td)
						table.append(tr)
						var act_loc = 0;
						var act_dep = 0;
						var loc_ids = new Array();
						var dep_ids = new Array();
						var con_ids = new Array();
						$xml.find("contact").each(
							function()
							{
								if ($(this).find("id_location").text() != act_loc) {
									tr = $('<tr id="' + $(this).find("id_location").text() + 'loc" style="background-color: #D8D8D8; cursor: pointer;"></tr>');
									td = $('<td><input class="cb" id="' + $(this).find("id_location").text() + 'loccb" type="checkbox"> ' + $(this).find("location").text() + '</td>');
									tr.append(td);
									table.append(tr);
									loc_ids.push($(this).find("id_location").text());
								}
								act_loc = $(this).find("id_location").text();
								if ($(this).find("id_department").text() != act_dep) {
									tr = $('<tr id="' + $(this).find("id_department").text() + 'dep" class="l' + $(this).find("id_location").text() + 'l" style="background-color: #E6E6E6; cursor: pointer; display: none"></tr>');
									td = $('<td style="padding-left: 40px"><input class="cb" id="l' + $(this).find("id_location").text() + 'l' + $(this).find("id_department").text() + 'depcb" type="checkbox"> ' + $(this).find("department").text() + '</td>');
									tr.append(td);
									table.append(tr);
									dep_ids.push($(this).find("id_department").text());
								}
								act_dep = $(this).find("id_department").text();
								tr = $('<tr class="d' + $(this).find("id_department").text() + 'di' + $(this).find("id_location").text() + 'i" id="' + $(this).find("id").text() + 'con" style="background-color: #F8F8F8; display: none"></tr>');
								td = $('<td style="padding-left: 80px"><input class="cb" id="d' + $(this).find("id_department").text() + 'dl' + $(this).find("id_location").text() + 'l' + $(this).find("id").text() + 'concb" type="checkbox"> ' + $(this).find("firstname").text() + ' ' + $(this).find("lastname").text() + '</td>');
								tr.append(td);
								table.append(tr);
								con_ids.push($(this).find("id").text());
							}
						);
						div_tree.append(table);
						
						table = $('<table style="width: 308px"></table>');
						tr = $('<tr></tr>');
						td = $('<td style="border-bottom-width: 0px"><?php echo T("Betreff");?>:</td>');
						tr.append(td)
						table.append(tr)
						tr = $('<tr></tr>');
						td = $('<td style="border-top-width: 0px"><input type="text" id="subject_done_mail" value="done: ' + task_title + '" style="width: 300px"></td>');
						tr.append(td)
						table.append(tr)
						tr = $('<tr></tr>');
						td = $('<td style="border-bottom-width: 0px"><?php echo T("Nachricht");?>:</td>');
						tr.append(td)
						table.append(tr)
						tr = $('<tr></tr>');
						td = $('<td style="border-top-width: 0px"><textarea id="message_done_mail" style="height: 275px; width: 300px"><?php echo t("Die im Betreff genannte Aufgabe wurde erledigt.");?></textarea></td>');
						tr.append(td)
						table.append(tr)
						
						div_message.append(table);
						
						$('#message_dialog2').append(div_tree);
						$('#message_dialog2').append(div_message);
						
						for(n in loc_ids)
						{
							(function(k)
							{
								$('#' + k + 'loc').click(
									function(e)
									{
										if($(e.target).hasClass('cb')) return;
										$('[class*="l' + k + 'l"]').toggle(500);
										$('[class*="i' + k + 'i"]').hide("fade", 500);
									}
								);
							})(loc_ids[n])
						}
						
						for(n in dep_ids)
						{
							(function(k)
							{
								$('#' + k + 'dep').click(
									function(e)
									{
										if($(e.target).hasClass('cb')) return;
										$('[class*="d' + k + 'd"]').toggle(500);
									}
								);
							})(dep_ids[n])
						}
						
						for(n in loc_ids)
						{
							(function(k)
							{
								$('#' + k + 'loccb').change(
									function(e)
									{
										if( $('#' + k + 'loccb').prop('checked') == true )
											$('[id*="l' + k + 'l"]').prop('checked', true);
										else
											$('[id*="l' + k + 'l"]').prop('checked', false);
									}
								);
							})(loc_ids[n])
						}
						
						for(n in dep_ids)
						{
							(function(k)
							{
								$('[id*="' + k + 'depcb"]').change(
									function(e)
									{
										if( $('[id*="' + k + 'depcb"]').prop('checked') == true )
											$('[id*="d' + k + 'd"]').prop('checked', true);
										else
											$('[id*="d' + k + 'd"]').prop('checked', false);
									}
								);
							})(dep_ids[n])
						}
						
						$('#message_dialog2').dialog({	
							buttons:
							[
								{ text: "<?php echo t("Senden"); ?>", click: function() {task_done_mail_send(id, con_ids, $('#subject_done_mail').val(), $('#message_done_mail').val()); $(this).dialog("close");} },
								{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
							],
							closeText:"<?php echo t("Fenster schließen"); ?>",
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							//show: { effect: 'drop', direction: "up" },
							title:"<?php echo t("Mail(s) verschicken"); ?>",
							width:680
						});
					}
					return;
				}
				catch(err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
			}
		);
	}
	
	function task_done_mail_send(id, con_ids, subject, message)
	{
		var mail_ids = new Array();
		var mail_cnt = 0;
		
		for(n in con_ids)
		{
			if ($('[id*="l'+con_ids[n]+'concb"]').prop('checked') == true) {
				mail_ids[mail_cnt] = con_ids[n];
				mail_cnt++;
			}
		}
		
		if (mail_ids.length == 0) return;
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTaskDoneMail", 'mail_ids': mail_ids, subject: subject, message: message}, 
			function($data)
			{
				try
				{
					return;
				}
				catch(err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
			}
		);
	}
	
	function task_edit(id, cat_id, parent_id, private_user_id)
	{
		var cat_id_new = 0;
		if($('#cat_select').val()!="")
			cat_id_new = $('#cat_select').val();
		
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTaskUpdate", mode: "edit", id: id, title: $('#task_title').val(), description: $('#task_description').val(), cat_id_new: cat_id_new}, 
			function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ($ack=="Success") {
						if (cat_id==0)
							tasks_all_view();
						else if(cat_id == "p")
							tasks_private_view(private_user_id);
						else
							tasks_view(cat_id);
						if (cat_id_new != 0)
						{
							if(cat_id == "p")
								view(0, "p", 0, private_user_id);
							else
								view(parent_id, "", 0, private_user_id);
						}
						return;
					}
				}
				catch(err)
				{
					show_status2(err.message+'<br /'+$data);
					return;
				}
			}
		);
	}
	
	function task_update(id, mode, cat_id, ids, parent_id, private_user_id)
	{	
		if (mode == "order_private" || mode == "order_all") {
			for(a in ids)
			{
				ids[a] = ids[a].substr(0, ids[a].length-3);
			}
		}
		
		if (typeof parent_id == 'undefined') parent_id = 0;
		wait_dialog_show();	
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTaskUpdate", mode: mode, id: id, cat_id: cat_id, 'ids[]': ids}, 
			function($data)
			{
				try
				{
					if (mode == "done") {
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack").text();
						if($ack == "Success")
						{
							if ($xml.find("status").text() == "done") {
								task_done_mail_dialog(id, $xml.find("task_title").text());
								if (cat_id == 0)
									tasks_all_view();
								else if(cat_id == "p")
									tasks_private_view(private_user_id);
								else
									tasks_view(cat_id);
								if (cat_id == "p")
									view(parent_id, "p", 0, private_user_id);
								else
									view(parent_id, "", 0, private_user_id);
								wait_dialog_hide();
							}
							else
							{
								wait_dialog_hide();
								$('#message_dialog').html($xml.find("status").text());
								$('#message_dialog').dialog({	
									buttons:
									[
										//{ text: "<?php echo t("Löschen"); ?>", click: function() {task_delete(m, 0); $(this).dialog("close");} },
										{ text: "<?php echo t("Ok"); ?>", click: function() {$(this).dialog("close");} }
									],
									closeText:"<?php echo t("Fenster schließen"); ?>",
									hide: { effect: 'drop', direction: "up" },
									modal:true,
									resizable:false,
									show: { effect: 'drop', direction: "up" },
									title:"<?php echo t("Achtung!"); ?>",
									width:300
								});
							}
						}
					}
					else
					{
						if (cat_id == 0)
							tasks_all_view();
						else if(cat_id=="p")
							tasks_private_view(private_user_id);
						else
							tasks_view(cat_id);
						if (mode == "check") {
							if(cat_id=="p")
								view(parent_id, "p", 0, private_user_id);
							else
								view(parent_id, "", 0);
						}
						wait_dialog_hide();
					}
					return;
				}
				catch(err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
			}
		);
	}
	
	function tasks_all_view()
	{
		$('#listing-tasks').empty();
		var showHideTask = '<img src="<?php echo PATH . ICONS_24;?>accept.png" class="btn button-accept" id="done_tasks" title="<?php echo t("show/hide erledigte Tasks");?>">';
		var divDetails = $('<div class="widget-listing"></div>');
		var textDetails = $('<h3>Latest Tasks - Overview' + showHideTask + '</h3>');
		var table = $('<table class="listing hover"></table>');
		var tbody = $('<tbody id="task_table"></tbody>');
		var tr = $('<tr></tr>');
		var th = $('<th>ID</th><th><?php echo t("Aufgabe");?></th><th style="min-width: 150px"><?php echo t("Kategorien-Pfad");?></th><th class="center" style="min-width: 150px;"><?php echo t("wird bearbeitet von");?></th><th><?php echo t("erledigt");?></th><th class="center" style="min-width: 150px;"><?php echo t("angelegt von");?></th><th><?php echo t("Aktionen");?></th>');
		tr.append(th);
		table.append(tr);
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTasksGet", mode: "latest"},
			function($data)
			{
				try
				{
					var ids = new Array();
					var done = new Array();
					var in_work_by = new Array();
					var done_ids = new Array();
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if($ack=="Success")
					{
						$xml.find("task").each(
							function()
							{
								tr = $('<tr id="' + $(this).find("task_id").text() + '_at"></tr>');
								td = $('<td style="cursor: move">' + $(this).find("task_priority").text() + '</td>');
								tr.append(td);
								
								var str = '<nobr>' + $(this).find("task_title").text() + '</nobr>';
								if ($(this).find("task_description").text() != "") {
									str += '<p style="font-size: 10px"><nobr>' + $(this).find("task_description").text().substr(0,60);
									if($(this).find("task_description").text().length>60)
										str += '...';
									str += '</nobr></p>';
								}
								td = $('<td>' + str + '</td>');
								tr.append(td);
								
								td = $('<td>' + $(this).find("task_parent_path").text() + '</td>');
								tr.append(td);
								if ($(this).find("task_in_work_by").text() == "0")
									td = $('<td class="center">-</td>');
								else
									td = $('<td class="center">' + $(this).find("task_in_work_by_name").text() + '</td>');
								tr.append(td);
								if ($(this).find("task_done").text() == "1")
									td = $('<td class="center" style="padding-left: 18px;"><img src="<?php echo PATH . ICONS_24;?>accept.png"></td>');
								else
									td = $('<td class="center">-</td>');
								tr.append(td);
								td=$('<td class="center">' + $(this).find("task_firstmod_user").text() + '</td>');
								tr.append(td);
								var tdstr = '';
								tdstr += '<img src="<?php echo PATH . ICONS_24;?>remove.png" id="' + $(this).find("task_id").text() + 'delete" title="<?php echo t("Task löschen");?>" class="btn">';
								tdstr += '<img src="<?php echo PATH . ICONS_24;?>accept.png" id="' + $(this).find("task_id").text() + 'done" title="<?php echo t("erledigt setzen");?>" class="btn">';
								if ($(this).find("task_in_work_by").text() == "0")
									tdstr += '<img src="<?php echo PATH . ICONS_24;?>user_add.png" id="' + $(this).find("task_id").text() + 'check" title="<?php echo t("auschecken");?>" class="btn">';
								else
									tdstr += '<img src="<?php echo PATH . ICONS_24;?>user_remove.png" id="' + $(this).find("task_id").text() + 'check" title="<?php echo t("einchecken");?>" class="btn">';
								tdstr += '<img src="<?php echo PATH . ICONS_24;?>edit.png" id="' + $(this).find("task_id").text() + 'edit" title="<?php echo t("Task bearbeiten");?>" class="btn">';
								ids.push($(this).find("task_id").text());
								done[$(this).find("task_id").text()] = $(this).find("task_done").text();
								if ($(this).find("task_done").text() == "1")
									done_ids.push($(this).find("task_id").text());
								in_work_by[$(this).find("task_id").text()] = $(this).find("task_in_work_by").text();
								td = $('<td colspan="2">' + tdstr + '</td>');
								tr.append(td);
								tbody.append(tr);
							}
						);
						table.append(tbody);
						divDetails.append(textDetails);
						divDetails.append(table);
						$('#listing-tasks').empty().append(divDetails);
						
						$('th').each(function(){
							$(this).css('width', $(this).width() +'px');
						});

						if(done_visible == 0) {
							for(n in done_ids)
							{
								(function(k)
								{
									$('#'+k+'_at').hide();
								})(done_ids[n])
							}
						}
						
						$('#done_tasks').click(
							function()
							{
								for(n in done_ids)
								{
									(function(k)
									{
										$('#'+k+'_at').toggle(500);
									})(done_ids[n])
								}
								if (done_visible == 0)
									done_visible = 1;
								else
									done_visible = 0;
							}
						);
						
						$('#task_add_button').click(
							function()
							{
								$('#new_task_title').val('');
								$('#new_task_description').val('');
								$('#new_task_dialog').dialog({	
									buttons:
									[
										{ text: "<?php echo t("Speichern"); ?>", click: function() {task_add(cat_id, $('#new_task_title').val(), $('#new_task_description').val()); $(this).dialog("close");} },
										{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
									],
									closeText:"<?php echo t("Fenster schließen"); ?>",
									hide: { effect: 'drop', direction: "up" },
									modal:true,
									resizable:false,
									show: { effect: 'drop', direction: "up" },
									title:"<?php echo t("Task hinzufügen!"); ?>",
									width:350
								});
							}
						);
						
						var fixHelper = function(e, ui) {
							ui.children().each(function() {
								$(this).width($(this).width());
							});
							return ui;
						};
						
						$(function()
						{
							$( "#task_table" ).sortable({
								helper: fixHelper,
								placeholder: "ui-state-highlight"
							});
							$( "#task_table" ).disableSelection();
							$( "#task_table" ).bind( "sortupdate", function(event, ui) {task_update(0, "order_all", 0, $(this).sortable('toArray'));} );
						});
							
						for(n in ids)
						{
							(function(k)
							{
								$('#' + k + 'done').click(function(){
										if (done[k] == 0 && in_work_by[k] == <?php echo $_SESSION["id_user"];?>) {
											$('#message_dialog').html('<?php echo t("Wollen Sie diese Aufgabe wirklich als erledigt setzen? Sie können das Setzen nicht rückgängig machen!"); ?>');
											$('#message_dialog').dialog({	
												buttons:
												[
													{ text: "<?php echo t("Ok"); ?>", click: function() {task_update(k, "done", 0); $(this).dialog("close");} },
													{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
												],
												closeText:"<?php echo t("Fenster schließen"); ?>",
												//hide: { effect: 'drop', direction: "up" },
												modal:true,
												resizable:false,
												show: { effect: 'drop', direction: "up" },
												title:"<?php echo t("Achtung!"); ?>",
												width:300
											});
										}
										else
											task_update(k, "done", 0);
									}
								);
							})(ids[n])
						}
						
						for(n in ids)
						{
							(function(m)
							{
								$('#' + m + 'check').click(function(){
										if (done[m] == 1) {
											$('#message_dialog').html('<?php echo t("Der Bearbeiter kann nicht mehr eingecheckt werden, da die Aufgabe schon als erledigt markiert ist."); ?>');
											$('#message_dialog').dialog({	
												buttons:
												[
													//{ text: "<?php echo t("Ok"); ?>", click: function() {task_update(k, "done", 0); $(this).dialog("close");} },
													{ text: "<?php echo t("Ok"); ?>", click: function() {$(this).dialog("close");} }
												],
												closeText:"<?php echo t("Fenster schließen"); ?>",
												hide: { effect: 'drop', direction: "up" },
												modal:true,
												resizable:false,
												show: { effect: 'drop', direction: "up" },
												title:"<?php echo t("Achtung!"); ?>",
												width:300
											});
										}
										else if(done[m]==0 && in_work_by[m]!=0 && in_work_by[m]!=<?php echo $_SESSION["id_user"];?>)
										{
											$('#message_dialog').html('<?php echo t("Sie können keinen fremden Bearbeiter einchecken."); ?>');
											$('#message_dialog').dialog({	
												buttons:
												[
													//{ text: "<?php echo t("Ok"); ?>", click: function() {task_update(k, "done", 0); $(this).dialog("close");} },
													{ text: "<?php echo t("Ok"); ?>", click: function() {$(this).dialog("close");} }
												],
												closeText:"<?php echo t("Fenster schließen"); ?>",
												hide: { effect: 'drop', direction: "up" },
												modal:true,
												resizable:false,
												show: { effect: 'drop', direction: "up" },
												title:"<?php echo t("Achtung!"); ?>",
												width:300
											});
										}
										else
											task_update(m, "check", 0);
									}
								);
							})(ids[n])
						}
						
						for(n in ids)
						{
							(function(m)
							{
								$('#' + m + 'delete').click(function(){
										$('#message_dialog').html('<?php echo t("Wollen Sie diesen Task wirklich löschen?");?>');
										$('#message_dialog').dialog({	
											buttons:
											[
												{ text: "<?php echo t("Löschen"); ?>", click: function() {task_delete(m, 0, 0); $(this).dialog("close");} },
												{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
											],
											closeText:"<?php echo t("Fenster schließen"); ?>",
											hide: { effect: 'drop', direction: "up" },
											modal:true,
											resizable:false,
											show: { effect: 'drop', direction: "up" },
											title:"<?php echo t("Task löschen!"); ?>",
											width:300
										});
									}
								);
							})(ids[n])
						}
						
						for(n in ids)
						{
							(function(m)
							{
								$('#' + m + 'edit').click(function(){
										$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTaskGet", id: m}, 
											function($data)
											{
												try
												{
													$xml = $($.parseXML($data));
													$ack = $xml.find("Ack").text();
													if($ack=="Success")
													{
														$('#task_title').val($xml.find("task_title").text());
														$('#task_description').val($xml.find("task_description").text());
														
														if($('#cat_select_tr').length>0)
															$('#cat_select_tr').remove();
														var tr = $('<tr id="cat_select_tr"></tr>');
														var td = $('<td>verschieben nach:</td>');
														tr.append(td);
														td = $('<td></td>');
														var cat_select = $('<select id="cat_select" style="width: 406px"></select>');
														var option = $('<option value=""><?php echo t("nicht verschieben");?></option>');
														cat_select.append(option);
														$xml.find("cat").each(
															function()
															{
																option = $('<option value="' + $(this).find("cat_id").text() + '">' + $(this).find("cat_path").text() + '</option>');
																cat_select.append(option);
															}
														);
														td.append(cat_select);
														tr.append(td);
														$('#task_edit_table').append(tr);
														list_sort("cat_select", 1);
														
														$('#task_edit_dialog').dialog({	
															buttons:
															[
																{ text: "<?php echo t("Speichern"); ?>", click: function() {task_edit(m, 0, 0); $(this).dialog("close");} },
																{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
															],
															closeText:"<?php echo t("Fenster schließen"); ?>",
															hide: { effect: 'drop', direction: "up" },
															modal:true,
															resizable:false,
															show: { effect: 'drop', direction: "up" },
															title:"<?php echo t("Task bearbeiten"); ?>",
															width:530
														});
													}
													return;
												}
												catch(err)
												{
													show_status2(err.message+'<br />'+$data);
													return;
												}
											}
										);
									}
								);
							})(ids[n])
						}
						return;
					}
				}
				catch(err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
			}
		);
	}
	
	function tasks_private_view(private_user_id)
	{
		//we need a categories list
		//var addTask = '<img src="<?php echo PATH . ICONS_24;?>add.png" class="btn button-add" id="task_add_button" title="<?php echo t("Neuen Task hinzufügen");?>">';
		var showHideTask = '<img src="<?php echo PATH . ICONS_24;?>accept.png" class ="btn button-accept" id="done_tasks" title="<?php echo t("show/hide erledigte Tasks");?>">';
		var divDetails = $('<div class="widget-listing"></div>');
		var textDetails = $('<h3>Private List - Overview' + showHideTask + '</h3>');
		var table = $('<table class="listing hover"></table>');
		var tbody = $('<tbody id="task_table"></tbody>');
		var tr = $('<tr></tr>');
		var th = $('<th>ID</th><th><?php echo t("Aufgabe");?></th><th style="min-width: 150px"><?php echo t("Kategorien-Pfad");?></th><th class="" style="min-width: 150px;"><?php echo t("wird bearbeitet von");?></th><th><?php echo t("erledigt");?></th><th class="center" style="min-width: 150px;"><?php echo t("angelegt von");?></th><th><?php echo t("Aktionen");?></th>');
		tr.append(th);
		table.append(tr);
		
		$('#listing-tasks').append(table);
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTasksGet", mode: "private", private_user_id: private_user_id},
			function($data)
			{
				try
				{
					var ids = new Array();
					var done = new Array();
					var in_work_by = new Array();
					var done_ids = new Array();
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if($ack=="Success")
					{
						$xml.find("task").each(
							function()
							{
								tr = $('<tr id="' + $(this).find("task_id").text() + '_pt"></tr>');
								td = $('<td style="cursor: move">' + $(this).find("task_private_priority").text() + '</td>');
								tr.append(td);
			
								var str = '<nobr>' + $(this).find("task_title").text() + '</nobr>';
								if($(this).find("task_description").text()!="")
								{
									str += '<p style="font-size: 10px"><nobr>' + $(this).find("task_description").text().substr(0,60);
									if($(this).find("task_description").text().length>60)
										str += '...';
									str += '</nobr></p>';
								}
								td = $('<td>' + str + '</td>');
								tr.append(td);
								
								td = $('<td>' + $(this).find("task_parent_path").text() + '</td>');
								tr.append(td);
								if($(this).find("task_in_work_by").text() == "0")
									td = $('<td class="center">-</td>');
								else
									td = $('<td class="center">' + $(this).find("task_in_work_by_name").text() + '</td>');
								tr.append(td);
								if($(this).find("task_done").text()=="1")
									td = $('<td class="center" style="padding-left: 18px;"><img src="<?php echo PATH . ICONS_24;?>accept.png"></td>');
								else
									td = $('<td class="center">-</td>');
								tr.append(td);
								td=$('<td class="center">' + $(this).find("task_firstmod_user").text() + '</td>');
								tr.append(td);
								var tdstr = '';
								tdstr += '<img src="<?php echo PATH . ICONS_24;?>remove.png" id="' + $(this).find("task_id").text() + 'delete" title="<?php echo t("Task löschen");?>" class="btn">';
								tdstr += '<img src="<?php echo PATH . ICONS_24;?>accept.png" id="' + $(this).find("task_id").text() + 'done" title="<?php echo t("erledigt setzen");?>" class="btn">';
								if($(this).find("task_in_work_by").text() == "0")
									tdstr += '<img src="<?php echo PATH . ICONS_24;?>user_add.png" id="' + $(this).find("task_id").text() + 'check" title="<?php echo t("auschecken");?>" class="btn">';
								else
									tdstr += '<img src="<?php echo PATH . ICONS_24;?>user_remove.png" id="' + $(this).find("task_id").text() + 'check" title="<?php echo t("einchecken");?>" class="btn">';
								tdstr += '<img src="<?php echo PATH . ICONS_24;?>edit.png" id="' + $(this).find("task_id").text() + 'edit" title="<?php echo t("Task bearbeiten");?>" class="btn">';
								ids.push($(this).find("task_id").text());
								done[$(this).find("task_id").text()] = $(this).find("task_done").text();
								in_work_by[$(this).find("task_id").text()] = $(this).find("task_in_work_by").text();
								if ($(this).find("task_done").text() == "1")
									done_ids.push($(this).find("task_id").text());
								td = $('<td colspan="2">' + tdstr + '</td>');
								tr.append(td);
								tbody.append(tr);
							}
						);
						table.append(tbody);
						divDetails.append(textDetails);
						divDetails.append(table);						
						$('#listing-tasks').empty().append(divDetails);
						
						$('th').each(function(){
							$(this).css('width', $(this).width() +'px');
						});
						
						if (done_visible == 0) {
							for(n in done_ids)
							{
								(function(k)
								{
									$('#'+k+'_pt').hide();
								})(done_ids[n])
							}
						}
						
						$('#done_tasks').click(
							function()
							{
								for(n in done_ids)
								{
									(function(k)
									{
										$('#'+k+'_pt').toggle(500);
									})(done_ids[n])
								}
								if (done_visible == 0)
									done_visible = 1;
								else
									done_visible = 0;
							}
						);
						
						var fixHelper = function(e, ui) {
							ui.children().each(function() {
								$(this).width($(this).width());
							});
							return ui;
						};
						
						$(function()
						{
							$( "#task_table" ).sortable({
								helper: fixHelper,
								placeholder: "ui-state-highlight"
							});
							$( "#task_table" ).disableSelection();
							$( "#task_table" ).bind( "sortupdate", function(event, ui) {task_update(0, "order_private", "p", $(this).sortable('toArray'), 0, private_user_id);} );
						});
						
						for(n in ids)
						{
							(function(k)
							{
								$('#' + k + 'done').click(function(){
										if (done[k] == 0 && in_work_by[k] == <?php echo $_SESSION["id_user"];?>) {
											$('#message_dialog').html('<?php echo t("Wollen Sie diese Aufgabe wirklich als erledigt setzen? Sie können das Setzen nicht rückgängig machen!"); ?>');
											$('#message_dialog').dialog({	
												buttons:
												[
													{ text: "<?php echo t("Ok"); ?>", click: function() {task_update(k, "done", "p", 0, 0, private_user_id); $(this).dialog("close");} },
													{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
												],
												closeText:"<?php echo t("Fenster schließen"); ?>",
												//hide: { effect: 'drop', direction: "up" },
												modal:true,
												resizable:false,
												show: { effect: 'drop', direction: "up" },
												title:"<?php echo t("Achtung!"); ?>",
												width:300
											});
										}
										else
											task_update(k, "done", "p");
									}
								);
							})(ids[n])
						}
						
						for(n in ids)
						{
							(function(m)
							{
								$('#' + m + 'check').click(function(){
										if (done[m] == 1) {
											$('#message_dialog').html('<?php echo t("Der Bearbeiter kann nicht mehr eingecheckt werden, da die Aufgabe schon als erledigt markiert ist."); ?>');
											$('#message_dialog').dialog({	
												buttons:
												[
													//{ text: "<?php echo t("Ok"); ?>", click: function() {task_update(k, "done", 0); $(this).dialog("close");} },
													{ text: "<?php echo t("Ok"); ?>", click: function() {$(this).dialog("close");} }
												],
												closeText:"<?php echo t("Fenster schließen"); ?>",
												hide: { effect: 'drop', direction: "up" },
												modal:true,
												resizable:false,
												show: { effect: 'drop', direction: "up" },
												title:"<?php echo t("Achtung!"); ?>",
												width:300
											});
										}
										else if(done[m]==0 && in_work_by[m]!=0 && in_work_by[m]!=<?php echo $_SESSION["id_user"];?>)
										{
											$('#message_dialog').html('<?php echo t("Sie können keinen fremden Bearbeiter einchecken."); ?>');
											$('#message_dialog').dialog({	
												buttons:
												[
													//{ text: "<?php echo t("Ok"); ?>", click: function() {task_update(k, "done", 0); $(this).dialog("close");} },
													{ text: "<?php echo t("Ok"); ?>", click: function() {$(this).dialog("close");} }
												],
												closeText:"<?php echo t("Fenster schließen"); ?>",
												hide: { effect: 'drop', direction: "up" },
												modal:true,
												resizable:false,
												show: { effect: 'drop', direction: "up" },
												title:"<?php echo t("Achtung!"); ?>",
												width:300
											});
										}
										else
											task_update(m, "check", "p", 0, 0, private_user_id);
									}
								);
							})(ids[n])
						}
						
						for(n in ids)
						{
							(function(m)
							{
								$('#' + m + 'delete').click(function(){
										$('#message_dialog').html('<?php echo t("Wollen Sie diesen Task wirklich löschen?");?>');
										$('#message_dialog').dialog({	
											buttons:
											[
												{ text: "<?php echo t("Löschen"); ?>", click: function() {task_delete(m, "p", 0, private_user_id); $(this).dialog("close");} },
												{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
											],
											closeText:"<?php echo t("Fenster schließen"); ?>",
											hide: { effect: 'drop', direction: "up" },
											modal:true,
											resizable:false,
											show: { effect: 'drop', direction: "up" },
											title:"<?php echo t("Task löschen!"); ?>",
											width:300
										});
									}
								);
							})(ids[n])
						}
						
						for(n in ids)
						{
							(function(m)
							{
								$('#' + m + 'edit').click(function(){
										$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTaskGet", id: m}, 
											function($data)
											{
												try
												{
													$xml = $($.parseXML($data));
													$ack = $xml.find("Ack").text();
													if ($ack == "Success") {
														$('#task_title').val($xml.find("task_title").text());
														$('#task_description').val($xml.find("task_description").text());
														
														if ($('#cat_select_tr').length>0)
															$('#cat_select_tr').remove();
														var tr = $('<tr id="cat_select_tr"></tr>');
														var td = $('<td>verschieben nach:</td>');
														tr.append(td);
														td = $('<td></td>');
														var cat_select = $('<select id="cat_select" style="width: 406px"></select>');
														var option = $('<option value=""><?php echo t("nicht verschieben");?></option>');
														cat_select.append(option);
														$xml.find("cat").each(
															function()
															{
																option = $('<option value="' + $(this).find("cat_id").text() + '">' + $(this).find("cat_path").text() + '</option>');
																cat_select.append(option);
															}
														);
														td.append(cat_select);
														tr.append(td);
														$('#task_edit_table').append(tr);
														list_sort("cat_select", 1);
														
														$('#task_edit_dialog').dialog({	
															buttons:
															[
																{ text: "<?php echo t("Speichern"); ?>", click: function() {task_edit(m, "p", 0, private_user_id); $(this).dialog("close");} },
																{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
															],
															closeText:"<?php echo t("Fenster schließen"); ?>",
															hide: { effect: 'drop', direction: "up" },
															modal:true,
															resizable:false,
															show: { effect: 'drop', direction: "up" },
															title:"<?php echo t("Task bearbeiten"); ?>",
															width:530
														});
													}
													return;
												}
												catch(err)
												{
													show_status2(err.message+'<br />'+$data);
													return;
												}
											}
										);
									}
								);
							})(ids[n])
						}
						return;
					}
				}
				catch(err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
			}
		);
	}
	
	function tasks_view(cat_id, parent_id)
	{
		$('#cat_new_'+parent_id).hide();
		var addTask = '<img src="<?php echo PATH . ICONS_24;?>add.png" class="btn button-add" id="task_add_button" title="<?php echo t("Neuen Task hinzufügen");?>">';
		var showHideTask = '<img src="<?php echo PATH . ICONS_24;?>accept.png" class="btn button-accept" id="done_tasks" title="<?php echo t("show/hide erledigte Tasks");?>">';
		var divDetails = $('<div class="widget-listing"></div>');
		var textDetails = $('<h3>Category Tasks List' + addTask + showHideTask + '</h3>');
		var table = $('<table class="listing hover"></table>');
		var tbody = $('<tbody id="task_table"></tbody>');
		var tr = $('<tr></tr>');
		var th = $('<th>ID</th><th><?php echo t("Aufgabe");?></th><th class="center" style="min-width: 150px;"><?php echo t("wird bearbeitet von");?></th><th><?php echo t("erledigt");?></th><th class="center" style="min-width: 150px;"><?php echo t("angelegt von");?></th><th><?php echo t("Aktionen");?></th>');
		tr.append(th);
		table.append(tr);
		
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTasksGet", mode: "category", cat_id: cat_id},
			function($data)
			{
				try
				{
					var ids = new Array();
					var done = new Array();
					var in_work_by = new Array();
					var done_ids = new Array();
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ($ack == "Success") {
						$xml.find("task").each(
							function()
							{
								tr = $('<tr id="' + $(this).find("task_id").text() + '"></tr>');
								td = $('<td style="cursor: move">' + $(this).find("task_ordering").text() + '</td>');
								tr.append(td);
								
								var str = '<nobr>' + $(this).find("task_title").text() + '</nobr>';
								if ($(this).find("task_description").text() != "") {
									str += '<p style="font-size: 10px"><nobr>' + $(this).find("task_description").text().substr(0,60);
									if ($(this).find("task_description").text().length > 60)
										str += '...';
									str += '</nobr></p>';
								}
								td = $('<td>' + str + '</td>');
								tr.append(td);
								
								if ($(this).find("task_in_work_by").text() == "0")
									td = $('<td class="center">-</td>');
								else
									td = $('<td class="center">' + $(this).find("task_in_work_by_name").text() + '</td>');
								tr.append(td);
								if ($(this).find("task_done").text() == "1")
									td = $('<td class="center" style="padding-left: 18px";><img src="<?php echo PATH . ICONS_24;?>accept.png"></td>');
								else
									td = $('<td class="center">-</td>');
								tr.append(td);
								td=$('<td class="center">' + $(this).find("task_firstmod_user").text() + '</td>');
								tr.append(td);
								var tdstr = '';
								tdstr += '<img src="<?php echo PATH . ICONS_24;?>remove.png" id="' + $(this).find("task_id").text() + 'delete" title="<?php echo t("Task löschen");?>" class="btn">';
								tdstr += '<img src="<?php echo PATH . ICONS_24;?>accept.png" id="' + $(this).find("task_id").text() + 'done" title="<?php echo t("erledigt setzen");?>" class="btn">';
								if($(this).find("task_in_work_by").text() == "0")
									tdstr += '<img src="<?php echo PATH . ICONS_24;?>user_add.png" id="' + $(this).find("task_id").text() + 'check" title="<?php echo t("auschecken");?>" class="btn">';
								else
									tdstr += '<img src="<?php echo PATH . ICONS_24;?>user_remove.png" id="' + $(this).find("task_id").text() + 'check" title="<?php echo t("einchecken");?>" class="btn">';
								tdstr += '<img src="<?php echo PATH . ICONS_24;?>edit.png" id="' + $(this).find("task_id").text() + 'edit" title="<?php echo t("Task bearbeiten");?>" class="btn">';
								ids.push($(this).find("task_id").text());
								done[$(this).find("task_id").text()] = $(this).find("task_done").text();
								in_work_by[$(this).find("task_id").text()] = $(this).find("task_in_work_by").text();
								if($(this).find("task_done").text()=="1")
									done_ids.push($(this).find("task_id").text());
								td = $('<td colspan="2">' + tdstr + '</td>');
								tr.append(td);
								tbody.append(tr);
							}
						);
						table.append(tbody);
						divDetails.append(textDetails);
						divDetails.append(table);
						$('#listing-tasks').empty().append(divDetails);
						
						$('th').each(function(){
							$(this).css('width', $(this).width() +'px');
						});
						
						if (done_visible == 0) {
							for(n in done_ids)
							{
								(function(k)
								{
									$('#'+k).hide();
								})(done_ids[n])
							}
						}
						
						$('#done_tasks').click(
							function()
							{
								for(n in done_ids)
								{
									(function(k)
									{
										$('#'+k).toggle(500);
									})(done_ids[n])
								}
								if (done_visible == 0)
									done_visible = 1;
								else
									done_visible = 0;
							}
						);
						
						$('#task_add_button').click(
							function()
							{
								$('#new_task_title').val('');
								$('#new_task_description').val('');
								$('#new_task_dialog').dialog({	
									buttons:
									[
										{ text: "<?php echo t("Speichern"); ?>", click: function() {task_add(cat_id, $('#new_task_title').val(), $('#new_task_description').val(), cat_id); $(this).dialog("close");} },
										{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
									],
									closeText:"<?php echo t("Fenster schließen"); ?>",
									hide: { effect: 'drop', direction: "up" },
									modal:true,
									resizable:false,
									show: { effect: 'drop', direction: "up" },
									title:"<?php echo t("Task hinzufügen!"); ?>",
									width:350
								});
							}
						);
						
						var fixHelper = function(e, ui) {
							ui.children().each(function() {
								$(this).width($(this).width());
							});
							return ui;
						};
						
						$(function()
						{
							$( "#task_table" ).sortable({
								helper: fixHelper,
								placeholder: "ui-state-highlight"
							});
							$( "#task_table" ).disableSelection();
							$( "#task_table" ).bind( "sortupdate", function(event, ui) {task_update(0, "order", cat_id, $(this).sortable('toArray'));} );
						});
						
						for(n in ids)
						{
							(function(k)
							{
								$('#' + k + 'done').click(function(){
										if (done[k] == 0 && in_work_by[k] == <?php echo $_SESSION["id_user"];?>) {
											$('#message_dialog').html('<?php echo t("Wollen Sie diese Aufgabe wirklich als erledigt setzen? Sie können das Setzen nicht rückgängig machen!"); ?>');
											$('#message_dialog').dialog({	
												buttons:
												[
													{ text: "<?php echo t("Ok"); ?>", click: function() {task_update(k, "done", cat_id,"",cat_id); $(this).dialog("close");} },
													{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
												],
												closeText:"<?php echo t("Fenster schließen"); ?>",
												//hide: { effect: 'drop', direction: "up" },
												modal:true,
												resizable:false,
												show: { effect: 'drop', direction: "up" },
												title:"<?php echo t("Achtung!"); ?>",
												width:300
											});
										}
										else
											task_update(k, "done", cat_id);
									}
								);
							})(ids[n])
						}
						
						for(n in ids)
						{
							(function(m)
							{
								$('#' + m + 'check').click(function(){
										if (done[m] == 1) {
											$('#message_dialog').html('<?php echo t("Der Bearbeiter kann nicht mehr eingecheckt werden, da die Aufgabe schon als erledigt markiert ist."); ?>');
											$('#message_dialog').dialog({	
												buttons:
												[
													//{ text: "<?php echo t("Ok"); ?>", click: function() {task_update(k, "done", 0); $(this).dialog("close");} },
													{ text: "<?php echo t("Ok"); ?>", click: function() {$(this).dialog("close");} }
												],
												closeText:"<?php echo t("Fenster schließen"); ?>",
												hide: { effect: 'drop', direction: "up" },
												modal:true,
												resizable:false,
												show: { effect: 'drop', direction: "up" },
												title:"<?php echo t("Achtung!"); ?>",
												width:300
											});
										}
										else if(done[m]==0 && in_work_by[m]!=0 && in_work_by[m]!=<?php echo $_SESSION["id_user"];?>)
										{
											$('#message_dialog').html('<?php echo t("Sie können keinen fremden Bearbeiter einchecken."); ?>');
											$('#message_dialog').dialog({	
												buttons:
												[
													//{ text: "<?php echo t("Ok"); ?>", click: function() {task_update(k, "done", 0); $(this).dialog("close");} },
													{ text: "<?php echo t("Ok"); ?>", click: function() {$(this).dialog("close");} }
												],
												closeText:"<?php echo t("Fenster schließen"); ?>",
												hide: { effect: 'drop', direction: "up" },
												modal:true,
												resizable:false,
												show: { effect: 'drop', direction: "up" },
												title:"<?php echo t("Achtung!"); ?>",
												width:300
											});
										}
										else
											task_update(m, "check", cat_id, "", cat_id);
									}
								);
							})(ids[n])
						}
						
						for(n in ids)
						{
							(function(m)
							{
								$('#' + m + 'delete').click(function(){
										$('#message_dialog').html('<?php echo t("Wollen Sie diesen Task wirklich löschen?");?>');
										$('#message_dialog').dialog({	
											buttons:
											[
												{ text: "<?php echo t("Löschen"); ?>", click: function() {task_delete(m, cat_id, cat_id); $(this).dialog("close");} },
												{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
											],
											closeText:"<?php echo t("Fenster schließen"); ?>",
											hide: { effect: 'drop', direction: "up" },
											modal:true,
											resizable:false,
											show: { effect: 'drop', direction: "up" },
											title:"<?php echo t("Task löschen!"); ?>",
											width:300
										});
									}
								);
							})(ids[n])
						}
						
						for(n in ids)
						{
							(function(m)
							{
								$('#' + m + 'edit').click(function(){
										$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoTaskGet", id: m}, 
											function($data)
											{
												//show_status2($data);
												try
												{
													$xml = $($.parseXML($data));
													$ack = $xml.find("Ack").text();
													if ($ack == "Success") {
														$('#task_title').val($xml.find("task_title").text());
														$('#task_description').val($xml.find("task_description").text());
														
														if ($('#cat_select_tr').length > 0)
															$('#cat_select_tr').remove();
														var tr = $('<tr id="cat_select_tr"></tr>');
														var td = $('<td>verschieben nach:</td>');
														tr.append(td);
														td = $('<td></td>');
														var cat_select = $('<select id="cat_select" style="width: 406px"></select>');
														var option = $('<option value=""><?php echo t("nicht verschieben");?></option>');
														cat_select.append(option);
														$xml.find("cat").each(
															function()
															{
																option = $('<option value="' + $(this).find("cat_id").text() + '">' + $(this).find("cat_path").text() + '</option>');
																cat_select.append(option);
															}
														);
														td.append(cat_select);
														tr.append(td);
														$('#task_edit_table').append(tr);
														list_sort("cat_select", 1);
														
														$('#task_edit_dialog').dialog({	
															buttons:
															[
																{ text: "<?php echo t("Speichern"); ?>", click: function() {task_edit(m, cat_id, cat_id); $(this).dialog("close");} },
																{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
															],
															closeText:"<?php echo t("Fenster schließen"); ?>",
															hide: { effect: 'drop', direction: "up" },
															modal:true,
															resizable:false,
															show: { effect: 'drop', direction: "up" },
															title:"<?php echo t("Task bearbeiten"); ?>",
															width:530
														});
													}
													return;
												}
												catch(err)
												{
													show_status2(err.message+'<br />'+$data);
													return;
												}
											}
										);
									}
								);
							})(ids[n])
						}
						
						$('#task_add').hide();
						return;
					}
				}
				catch(err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
			}
		);
	}
	
	function view(parent_id, list_mode, update_list, private_user_id)
	{
		if (typeof list_mode == 'undefined') list_mode = "latest";
		if (typeof parent_id == 'undefined') parent_id = 0;
		if (typeof update_list == 'undefined') update_list = 1;
		if (typeof private_user_id == 'undefined') private_user_id = 0;
		
		if (update_list == 1)
			$('#listing-tasks').empty();
		var table = $('<table class="hover"></table>');
		var tr = $('<tr></tr>');
		var th = $('<th style="min-width: 150px"><?php echo t("todo-Kategorien");?><img src="<?php echo PATH . ICONS_24;?>add.png" id="cat_new_' + parent_id +'" style="cursor: pointer; float: right" title="<?php echo t("Neue Kategorie hinzufügen");?>"></th>');
		tr.append(th);
		table.append(tr);
		var td;
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "TodoCategoriesGet", parent_id: parent_id}, 
			function($data)
			{
				try
				{
					var offset = 5;
					var cat_add_show = 1;
					var cat_ids = new Array();
					var cat_ids2 = new Array();
					var cat_task_num = new Array();
					var private_list_ids = new Array();
					
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if ($ack == "Success") {
						$xml.find("list").each(
							function()
							{
								tr = $('<tr class="private-list"></tr>');
								if (list_mode == "p" && private_user_id == $(this).find("list_user_id").text())
									td = $('<td style="padding-left: ' + offset + 'px">&#187<p style="display: inline; font-weight: bold"><?php echo t("private Liste");?> ' + $(this).find("list_username").text() + '(' + $(this).find("list_undone_tasks").text() + ')</p></td>');
								else
									td = $('<td style="padding-left: ' + offset + 'px">&#187<a href="#" id="' + $(this).find("list_user_id").text() + 'private_list_link" onclick="return false"><?php echo t("private Liste");?> ' + $(this).find("list_username").text() + '(' + $(this).find("list_undone_tasks").text() + ')</a></td>');
								private_list_ids.push($(this).find("list_user_id").text());
								tr.append(td);
								table.append(tr);
							}
						);						
						if (parent_id == 0 && (list_mode == "" || list_mode == 'latest')) {
							tr = $('<tr></tr>');
							td = $('<td style="padding-left: ' + offset + 'px">&#187<p style="display: inline; font-weight: bold">Home (' + $xml.find("undone_tasks_home").text() + ')</p></td>');
							cat_ids2.push("0");
							tr.append(td);
							table.append(tr);
							offset += 30;
						} else {
							tr = $('<tr></tr>');
							td = $('<td style="padding-left: ' + offset + 'px">&#187<a href="#" id="0cat_childs" onclick="return false">Home (' + $xml.find("undone_tasks_home").text() + ')</a></td>');
							cat_ids2.push("0");
							tr.append(td);
							table.append(tr);
							offset += 30;
						}
												
						var task_add_id=0;
						$xml.find("p_cat").each(
							function()
							{
								tr = $('<tr></tr>');
								if ($xml.find("cat_cnt").text() == "0" && $(this).find("p_cnt").text() == "0" && $(this).find("p_t_childs").text() == "0") {
									td = $('<td style="padding-left: ' + offset + 'px">&#187<p style="display: inline; font-weight: bold">' + $(this).find("p_cat_title").text() + ' (' + $(this).find("p_cat_undone_tasks").text() + ')</p><img src="<?php echo PATH . ICONS_24;?>add.png" id="task_add" style="cursor: pointer; float: right" title="<?php echo t("Neuen Task hinzufügen");?>"></td>');
									task_add_id = $(this).find("p_cat_id").text();
								} else if ($(this).find("p_cnt").text() == "0") {
									td = $('<td style="padding-left: ' + offset + 'px">&#187<p style="display: inline; font-weight: bold">' + $(this).find("p_cat_title").text() + ' (' + $(this).find("p_cat_undone_tasks").text() + ')</p></td>');
								} else
									td = $('<td style="padding-left: ' + offset + 'px">&#187<a href="#" id="' + $(this).find("p_cat_id").text() + 'cat_childs" onclick="return false">' + $(this).find("p_cat_title").text() + ' (' + $(this).find("p_cat_undone_tasks").text() + ')</a></td>');
								cat_ids2.push($(this).find("p_cat_id").text());
								tr.append(td);
								table.append(tr);
								offset += 30;
								if ($(this).find("p_t_childs").text() * 1 > 0) {
									cat_add_show = 0;
									cat_task_num[$(this).find("p_cat_id").text()] = $(this).find("p_t_childs").text();
								}
							}
						);
						var tbody = $('<tbody id="cat_table"></tbody>');
						$xml.find("cat").each(
							function()
							{
								tr = $('<tr id="' + $(this).find("cat_id").text() + '"></tr>');
								var tdstr = '<td style="padding-left:' + offset + 'px"><nobr>';
								tdstr += $(this).find("cat_ordering").text() + '. ';
								tdstr += '<a href="#" id="' + $(this).find("cat_id").text() + 'cat_childs" onclick="return false">' + $(this).find("cat_title").text() + ' (' + $(this).find("cat_undone_tasks").text() + ')</a>';
								cat_ids2.push($(this).find("cat_id").text());
								cat_task_num[$(this).find("cat_id").text()] = $(this).find("cat_t_childs").text();
								if ($(this).find("cat_childs").text() == "0") {
									tdstr += '<img src="<?php echo PATH . ICONS_24;?>remove.png" class="btn menu-button-remove" id="' + $(this).find("cat_id").text() + 'delete_button" title="<?php echo t("Kategorie löschen");?>" style="cursor: pointer; float: right">';
									cat_ids.push($(this).find("cat_id").text());
								}
								tdstr += '</nobr></td>';
								td = $(tdstr);
								tr.append(td);
								
								tbody.append(tr);
							}
						);
						table.append(tbody);
						$('#menu').empty().append(table);
						
						$('th').each(function(){
							$(this).css('width', $(this).width() +'px');
						});
						
						if(cat_add_show == 0)
							$('#cat_new_' + parent_id).hide();
							
						var fixHelper = function(e, ui) {
							ui.children().each(function() {
								$(this).width($(this).width());
							});
							return ui;
						};
						
						$(function()
						{
							$( "#cat_table" ).sortable({
								helper: fixHelper,
								placeholder: "ui-state-highlight"
							});
							$( "#cat_table" ).disableSelection();
							$( "#cat_table" ).bind( "sortupdate", function(event, ui) {category_update("order", $(this).sortable('toArray'), parent_id);} );
						});
						
						if(task_add_id > 0)
						{
							$('#task_add').click(function(){
									$('#new_task_title').val('');
									$('#new_task_description').val('');
									$('#new_task_dialog').dialog({	
										buttons:
										[
											{ text: "<?php echo t("Speichern"); ?>", click: function() {task_add(task_add_id, $('#new_task_title').val(), $('#new_task_description').val(), task_add_id); $(this).dialog("close");} },
											{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
										],
										closeText:"<?php echo t("Fenster schließen"); ?>",
										hide: { effect: 'drop', direction: "up" },
										modal:true,
										resizable:false,
										show: { effect: 'drop', direction: "up" },
										title:"<?php echo t("Task hinzufügen!"); ?>",
										width:350
									});
								}
							);
						}
						
						for(n in cat_ids)
						{
							(function(k)
							{
								$('#' + k + 'delete_button').click(function(){
										$('#message_dialog').html('<?php echo t("Wollen Sie diese Kategorie wirklich löschen?");?>');
										$('#message_dialog').dialog({	
											buttons:
											[
												{ text: "<?php echo t("Löschen"); ?>", click: function() {category_delete(k, parent_id); $(this).dialog("close");} },
												{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
											],
											closeText:"<?php echo t("Fenster schließen"); ?>",
											hide: { effect: 'drop', direction: "up" },
											modal:true,
											resizable:false,
											show: { effect: 'drop', direction: "up" },
											title:"<?php echo t("Kategorie löschen!"); ?>",
											width:300
										});
									}
								);
							})(cat_ids[n])
						}
						
						for(n in private_list_ids)
						{
							(function(k)
							{
								$('#' + k + 'private_list_link').click(
									function()
									{
										tasks_private_view(k);
										view(0, "p", 1, k)
									}
								);
							})(private_list_ids[n])
						}
						
						for(n in cat_ids2)
						{
							(function(k)
							{
								$('#' + k + 'cat_childs').click(function(){
										$('#listing-tasks').empty();
										if (cat_task_num[k] *1 > 0) {
											tasks_view(k, parent_id);
											view(k);
										} else
											view(k);
											//view('tree', k);
									}
								);
							})(cat_ids2[n])
						}
						
						$('#cat_new_' + parent_id).click(function(){
							$('#new_cat_dialog').dialog({	
								buttons:
								[
									{ text: "<?php echo t("Speichern"); ?>", click: function() {category_add($('#new_cat_title').val(), parent_id); $(this).dialog("close");} },
									{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
								],
								closeText:"<?php echo t("Fenster schließen"); ?>",
								hide: { effect: 'drop', direction: "up" },
								modal:true,
								resizable:false,
								show: { effect: 'drop', direction: "up" },
								title:"<?php echo t("Kategorie hinzufügen!"); ?>",
								width:300
							});
						});
						
						if (parent_id == 0 && update_list == 1 && list_mode == 'latest')
							tasks_all_view();
						
						return;
					}
				}
				catch(err)
				{
					show_status2(err.message+'<br />'+$data);
					return;
				}
			}
		);
	}
	
</script>

<?php
	
	//PATH
	echo '<div id="breadcrumbs" class="breadcrumbs">';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' &#187 <a href="backend_interna_index.php">Interna</a>';
	echo ' &#187 <a href="backend_todo.php?lang=de&id_menuitem=283">Todo-Liste</a>';
	echo '</div>';
	echo '<h1>Todo-Liste</h1>';
	
	echo '<div id="content-wrapper">';
	echo '	<div id="menu"></div>';
	echo '	<div id="listing-tasks"></div>';
	echo '</div>';
	
	//new category dialog
	echo '<div id="new_cat_dialog" style="display: none">';
	echo '	<p style="display: inline">' . t("Name der Kategorie") . ': </p>';
	echo '	<input type="text" id="new_cat_title">';
	echo '</div>';
	
	//new task dialog
	echo '<div id="new_task_dialog" style="display: none">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>' . t("Name des Tasks") . ':</td>';
	echo '			<td><input type="text" id="new_task_title" style="width: 200px"></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>' . t("Beschreibung") . ':</td>';
	echo '			<td><textarea id="new_task_description" style="height: 100px; width: 200px"></textarea></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';
	
	//task edit dialog
	echo '<div id="task_edit_dialog" style="display: none">';
	echo '	<table id="task_edit_table">';
	echo '		<tr>';
	echo '			<td>' . t("Bezeichnung") . ':</td>';
	echo '			<td><input type="text" id="task_title" style="width: 400px"></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>' . t("Beschreibung") . ':</td>';
	echo '			<td><textarea id="task_description" style="height: 200px; width: 400px"></textarea></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';
	
	// message dialog
	echo '<div id="message_dialog" style="display: none"></div>';
	echo '<div id="message_dialog2" style="display: none"></div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>