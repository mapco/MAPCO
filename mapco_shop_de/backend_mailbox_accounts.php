<?php

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
?>
<script src="javascript/des_encryption.php" type="text/javascript" /></script>

<script type="text/javascript">

	var id_location = 0;
	var id_department = 0;
	var id_contact = 0;
	var active_account = 0;

	function dialog_account_add()
	{
		$("#mailaccounts_user_content").empty();
		
		if ($("#dialog_account_add").length == 0)
		{
			var dialog_div = $('<div id="dialog_account_add"></div>');
			$("#content").append(dialog_div);
		}
		
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'TableDataSelect';
		post_object['db'] = 'dbweb';
		post_object['table'] = 'cms_mail_servers';		
		post_object['select'] = 'id, title';
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2('Die Mailbox konnte nicht gelesen werden.'); wait_dialog_hide(); return;};
			
			var account_add_content = '<table>';
			account_add_content += ' <tr>';
			account_add_content += ' 	<td>Server</td>';
			account_add_content += ' 	<td>';
			account_add_content += ' 		<select id="account_add_server">';
			$xml.find("cms_mail_servers").each(function(){
				account_add_content += ' 		<option value="'+$(this).find('id').text()+'">'+$(this).find('title').text()+'</option>';
			});
			account_add_content += ' 		</select>';
			account_add_content += '	</td>';
			account_add_content += ' </tr>';
			account_add_content += ' <tr>';
			account_add_content += ' 	<td>Bezeichnung</td>';
			account_add_content += ' 	<td><input id="account_add_title" type="text" /></td>';
			account_add_content += ' </tr>';
			account_add_content += ' <tr>';
			account_add_content += ' 	<td>Mailbox</td>';
			account_add_content += ' 	<td><input id="account_add_mailbox" type="text" /></td>';
			account_add_content += ' </tr>';
			account_add_content += ' <tr>';
			account_add_content += ' 	<td>Passwort</td>';
			account_add_content += ' 	<td><input id="account_add_password" type="password" /></td>';
			account_add_content += ' </tr>';
			account_add_content += ' <tr>';
			account_add_content += ' 	<td>inkl. Postausgang</td>';
			account_add_content += ' 	<td><input id="account_add_postausgang" type="checkbox" /></td>';
			account_add_content += ' </tr>';
			account_add_content += ' <tr>';
			account_add_content += ' 	<td>inkl. Archiv</td>';
			account_add_content += ' 	<td><input id="account_add_archiv" type="checkbox" /></td>';
			account_add_content += ' </tr>';
			account_add_content += '</table>';
			
			$("#dialog_account_add").empty().append(account_add_content);
			
			wait_dialog_hide();
			$("#dialog_account_add").dialog({	
				buttons:
				[
					{ text: "<?php echo t("OK"); ?>", click: function() { account_add(); } },
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Bestätigung"); ?>",
				width:350
			});
		});
	}
	
	function account_add()
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailAccountAdd';
		post_object['server'] = $("#account_add_server").val();
		post_object['title'] = $("#account_add_title").val();
		post_object['mailbox'] = $("#account_add_mailbox").val();
		post_object['postausgang'] = 0;
		if ( $("#account_add_postausgang").prop('checked') == true )
		{
			post_object['postausgang'] = 1;
		}
		post_object['archiv'] = 0;
		if ( $("#account_add_archiv").prop('checked') == true )
		{
			post_object['archiv'] = 1;
		}
			
		post_object['password'] = encode_password($("#account_add_password").val());
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2('Die Mailbox konnte nicht gelesen werden.'); wait_dialog_hide(); return;};
			
			$("#dialog_account_add").dialog("close");
			mailaccounts_show();
		});
	}

	function mailaccounts_show()
	{				
		$("#mailaccounts_content").empty();
		var account = $("#navigation_div").attr('active_account');
	
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'TableDataSelect';
		post_object['db'] = 'dbweb';
		post_object['table'] = 'cms_mail_accounts';
		post_object['join'] = ', cms_mail_servers';		
		post_object['select'] = 'id_account, cms_mail_servers.title AS server, cms_mail_accounts.title, user, ordering';
		post_object['where'] = 'WHERE cms_mail_servers.id=cms_mail_accounts.server ORDER BY ordering ASC';
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data);  return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2('Die Mailbox konnte nicht gelesen werden.'); wait_dialog_hide(); return;};

			var accounts_table = '<div style="float:left;"><table id="mail_account_list" class="orderlist ui-sortable">';
			accounts_table += '	<tr class="header">';
			accounts_table += '		<th>Nummer</th>';
			accounts_table += '		<th>Bezeichnung</th>';
			accounts_table += '		<th>Server</th>';
			accounts_table += '		<th>Mailbox</th>';
			accounts_table += 		'<th><img alt="Mailaccount hinzufügen" src="<?php echo PATH; ?>images/icons/24x24/add.png" style="cursor:pointer;" title="Mailaccount hinzufügen" onclick="dialog_account_add();" /></th>';
			accounts_table += '		</th>';
			accounts_table += ' </tr>';
			if ( $xml.find("num_rows") == 0 )
			{
				accounts_table += ' <tr class="header">';
				accounts_table += ' 	<td colspan="2">Keine Accounts eingetragen!</td>';
				accounts_table += ' </tr>';
			}
			else
			{
				var account_id = 0;
				$xml.find("cms_mail_accounts").each(function(){
					account_id = $(this).find("id_account").text();
					accounts_table += ' <tr id="mail_account_'+account_id+'">';
					accounts_table += '		<td>'+$(this).find("ordering").text()+'</td>';
					accounts_table += '		<td>'+$(this).find("title").text()+'</td>';
					accounts_table += '		<td>'+$(this).find("server").text()+'</td>';
					accounts_table += '		<td>'+$(this).find("user").text()+'</td>';
					accounts_table += 		'<td><img alt="Online-Seller zuweisen" class="button_change_account_user" account="'+account_id+'" src="<?php echo PATH; ?>images/icons/24x24/users.png" style="cursor:pointer;" title="Online-Seller zuweisen" ';
					
					accounts_table += '		</td>'; //onclick="contacts_view();" /></td>';
					//accounts_table += 		'onclick="mail_accounts_set_users('+account_id+');" /></td>';
					accounts_table += ' </tr>';
				});
			}
			
			accounts_table += '</table></div>';
			
			$("#mailaccounts_content").append(accounts_table);
			
			$(".button_change_account_user").click(function(){
				active_account = $(this).attr('account');
				contacts_view();
			});
			
			$(function() 
			{
				$( "#mail_account_list" ).sortable( { items:"tr:not(.header)" } );
				$( "#mail_account_list" ).sortable( { cancel:".header"} );
				$( "#mail_account_list" ).disableSelection();
				$( "#mail_account_list" ).bind( "sortupdate", function(event, ui)
				{
					wait_dialog_show('Sortiere Einträge', 0);
					var list = $('#mail_account_list').sortable('toArray');
					$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSort", list:list, table:'cms_mail_accounts', label:'mail_account_', column:'id_account'}, function($data){ 
						//show_status2($data); return;
						try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
						if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
						wait_dialog_show('Aktualisiere Ansicht', 100);
						mailaccounts_show();
						
						wait_dialog_hide();
					});
				});
			});
			
			wait_dialog_hide();
		});
	}

	function mail_accounts_set_users(account_id)
	{
		$("#mailaccounts_user_content").empty();
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailAccountUsersGet';		
		post_object['account_id'] = account_id;
				
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data);  return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2(); wait_dialog_hide(); return;};
		
			var account_user_table = '<table style="float:left;">';
			account_user_table += '	<tr>';
			account_user_table += '		<th colspan="2">Online-Seller</th>';
			account_user_table += ' </tr>';
			$xml.find("mail_account_user").each(function(){
				account_user_table += ' <tr>';
				account_user_table += '		<td><input class="mail_account_user_check" type="checkbox" value="'+$(this).find("user_id").text()+'"';
				if ( $(this).find("checked").text() == 1 )
				{
					account_user_table += ' checked';
				}
				account_user_table += '></td>';
				account_user_table += '		<td>'+$(this).find("name").text()+'</td>';
				account_user_table += ' </tr>';
			});
			
			account_user_table += '</table>';
			
			account_user_table += '<button onClick="mail_account_users_save('+account_id+')">Ausgewählte Benutzer zuweisen</button>';
			
			$("#mailaccounts_user_content").append(account_user_table);
	
			wait_dialog_hide();
		});
	}

	function mail_account_users_save()
	{
		var users = '';
		$(".mail_account_user_check").each(function(){
			if ( $(this).is(':checked') )
			{
				if (  users != '' ) { users += ', '; }
				users += $(this).attr("value");
			}
		});

		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailAccountUsersSave';		
		post_object['account_id'] = active_account;
		post_object['user_ids'] = users;

		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data);  return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2(); wait_dialog_hide(); return;};
	
			wait_dialog_hide();
			mailaccounts_show();
		});
	}
	
	function contact_view($id_contact, $id_department, $id_location)
	{
		$("#contact_search_results").html("");
		$("#contact_search_results").hide();
		id_location=$id_location;
		id_department=$id_department;
		contacts_view();
	}
	
	function contacts_view()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"MailAccountsUsersView", id_location:id_location, id_department:id_department, account_id:active_account },
			function(data)
			{
				wait_dialog_hide();
				$("#contacts_view").html(data);
			}
		);
	}

	mailaccounts_show();
</script>

<?php
	print '<h2>Email-Accounts des Posteingangs</h2>';
	print '<div id="mailaccounts_content" style="float:left;"></div>';
	print '<div id="mailaccounts_user_content" style="float:left; padding-left:20px;"></div>';
	
	echo '<div id="contacts_view"></div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>