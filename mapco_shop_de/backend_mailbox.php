<?php
	mb_internal_encoding('UTF-8');
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

?>

<style type="text/css">

	.list_mail_accounts:hover { background-color:RGB(240,240,240); }
	.list_mail_accounts { width:180px; margin-top:5px; margin-bottom:5px; }

	.list_mail_folders_li:hover { background-color:RGB(240,240,240); }
	.list_mail_folders_li { width:220px; margin-top:0px; margin-bottom:0px; }

</style>
<script src="javascript/cms/FileUpload.php" type="text/javascript" /></script>
<script type="text/javascript">

	var local_new_mail = '<?php echo t("Signatur bearbeiten"); ?>';
	var local_mail_uid = '<?php echo t("Mail ID"); ?>';
	var local_date = '<?php echo t("Datum"); ?>';
	var local_sender = '<?php echo t("Absender"); ?>';
	var local_subject = '<?php echo t("Betreff"); ?>';
	
	var img_new_mail = '<img src="images/icons/16x16/mail.png" alt="'+local_new_mail+'" title="'+local_new_mail+'">';
	var username = '';
	var signatur = '';
	var page_act = 1;
	var page_max = 0;
	var mails_abs = 0;
	var mails_p_page = 50;
	var lfd_nr_min = 0;
	var lfd_nr_max = 0;
	
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
	
	function backend_mailbox_main()
	{	
		mail_accounts_show()
	}
	
	function mail_accounts_show()
	{
		var userrole_id = <?php print $_SESSION['userrole_id']; ?>;
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'TableDataSelect';
		post_object['db'] = 'dbweb';
		post_object['table'] = 'cms_mail_accounts_users';
		post_object['join'] = ', cms_mail_accounts';
		post_object['select'] = 'account_id, title, user';
		post_object['where'] = 'WHERE user_id=<?php print $_SESSION['id_user']; ?> AND id_account=account_id ORDER BY user_ordering ASC';
		
		wait_dialog_show('<?php echo t("Lade E-Mailkonten"); ?>', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data);  return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2(); wait_dialog_hide(); return;};
			
			var l = 0;
			var k = 0;
			var account_id = 0;
			var active_account = 0;
			var active_account_mail = "ich@hier.de";
			var div_accounts = '<div id="mailbox_account_tabs" class="ui-tabs ui-widget" style="border:solid 1px;" active_account="'+active_account+'" active_account_mail="'+active_account_mail+'" active_folder="0">';
			div_accounts += '<ul id="tabs_accounts" class="ui-sortableui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist" style="height:45px;">'; 
			var tmp_mail_adr = '';
			var results = $xml.find(post_object['table']);
			
			if ( results.find("account_id").text() != '' )
			{
				var style = '';
				var css_class = '';
				results.each(function(){
					account_id = $(this).find("account_id").text();
					tmp_mail_adr = $(this).find("user").text();
					if ( l == 0 )
					{ 
						active_account = account_id;
						active_account_mail = tmp_mail_adr;
						style = ' font-weight:bold;';
						css_class = " ui-tabs-active ui-state-active";
					}
					else
					{
						style = '';	
						css_class = '';
					}
					k = l + 1;					
					
					div_accounts += '	<li id="account_'+account_id+'" class="list_mail_accounts ui-state-default ui-corner-top'+css_class+'" role="tab" tabindex="0" aria-controls="tab-general" aria-labelledby="ui-id-1" aria-selected="true" mail_addr="'+tmp_mail_adr+'" style="height:34px; float:left; padding-left:5px; padding-right:5px; margin-left:5px; margin-right:5px; width:150px;">'+$(this).find("title").text()+'<br />('+tmp_mail_adr+')</li>';
					l++;
				});
			}
			else
			{
				div_accounts += '	<li id="account_0"><?php echo t("Kein zugehöriges Postfach gefunden!"); ?></li>';
			}				
			div_accounts += '</ul>';
			
			$("#mailbox_accounts").html(div_accounts);
			
			$("#mailbox_account_tabs").attr('active_account', active_account);
			$("#mailbox_account_tabs").attr('active_account_mail', active_account_mail);
			
			$(function() {
				$( "#tabs_accounts" ).sortable( { items:"li:not(.header)" } );
				$( "#tabs_accounts" ).sortable( { cancel:".header"} );
				$( "#tabs_accounts" ).disableSelection();
				$( "#tabs_accounts" ).bind( "sortupdate", function(event, ui)
				{
					wait_dialog_show('<?php echo t("Ändere Reihenfolge"); ?>', 0);
					var list = $('#tabs_accounts').sortable('toArray');

					$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"MailAccountsSort", list:list, type:'accounts' }, function($data){ 
						//show_status2($data); return;
						try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
						if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
						wait_dialog_show('<?php echo t("Aktualisiere Ansicht"); ?>', 100);
						mail_accounts_show();
						
						wait_dialog_hide();
					});
				});
			});
			
			if ( results.find("account_id").text() != '' )
			{
				$(".list_mail_accounts").click(function(){
					var id = $( this ).attr( 'id' );
					var tab = id.split("_");
					
					$("#account_"+$("#mailbox_account_tabs").attr('active_account')).css("font-weight", "normal");
					
					$("#mailbox_account_tabs").attr('active_account', tab[1]);
					$("#mailbox_account_tabs").attr('active_account_mail', $( this ).attr( 'mail_addr' ));
					
					$("#tabs_accounts").children().removeClass('ui-tabs-active ui-state-active');
					$(this).addClass('ui-tabs-active ui-state-active');
					
					$("#account_"+tab[1]).css("font-weight", "bold");					
					page_act = 1;
					
					mailbox_folders_show();
				});
				mailbox_folders_show();
			}
		});
	}
	
	function mailbox_folders_show()
	{
		var post_object = new Object();
		post_object['API'] = 'cms';		
		post_object['APIRequest'] = 'MailFoldersGet';
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		
		wait_dialog_show('Lade Ordner', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data);  return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2(); wait_dialog_hide(); return;};
			
			var l = 0;
			var k = 0;
			var folder_id = 0;
			var active_folder = 0;
			var div_folders = '<ul id="tabs_folders" class="ui-sortable orderlist">';
			div_folders += '	<li class="header" style="font-weight:bold; width:220px;">';
			div_folders += '		<table>';
			div_folders += '			<tr>';
			div_folders += '				<td style="width:140px; border:none;"><?php echo t("Ordner"); ?></td>';
			//div_folders += '				<td style="border:none;"><img src="images/icons/24x24/database.png" style="cursor:pointer; float:right;" onclick="javascript:mail_account_folders_check();" alt="Ordner mit Mail-Server abgleichen" title="Ordner mit Mail-Server abgleichen">';

			var local_folder_create = '<?php echo t("Neuen Ordner anlegen"); ?>';
			div_folders += '		<img src="images/icons/24x24/add.png" style="cursor:pointer; float:right; margin-right:3px;" onclick="javascript:dialog_folder_create();" alt="'+local_folder_create+'" title="'+local_folder_create+'"></td>';
			div_folders += '			</tr>';
			div_folders += '		</table>';
			div_folders += '	</li>';
			
			if ( $xml.find("account_folder").text() != '' )
			{
				var style = '';
				$xml.find('account_folder').each(function(){
					folder_id = $(this).find("folder_id").text();
					if ( l == 0 )
					{
						style = ' font-weight:bold;';
						active_folder = folder_id;
					}
					else
					{
						style = '';	
					}
					k = l + 1;					
					
					div_folders += '	<li id="folder_'+folder_id+'" style="'+style+' cursor:pointer;" class="list_mail_folders_li">';
					div_folders += '		<table><tr><td style="width:220px; border:none;" class="list_mail_folders" id="folder_'+folder_id+'_td">'+$(this).find("folder_name").text()+' <span style="color:RGB(75,75,150);">('+$(this).find("folder_new_msgs").text()+')</span> '+$(this).find("folder_Nmsgs").text()+'</td>';
					
					var local_folder_rename = '<?php echo t("Ordner umbennen"); ?>';
					div_folders += '		<td style="border:none;"><img src="images/icons/24x24/edit.png" style="cursor:pointer;" onclick="javascript:dialog_folder_edit_name('+folder_id+');" alt="'+local_folder_rename+'" title="'+local_folder_rename+'"></td></tr></table>';
					div_folders += '	</li>';
					l++;
				});
			}
			else
			{
				div_folders += '	<li id="folder_0"><?php echo t("Keinen Ordner gefunden!"); ?></li>';
			}			
			div_folders += '</ul>';
			
			$("#mailbox_folders").html(div_folders);
			$("#mailbox_account_tabs").attr('active_folder', active_folder);
			
			$(function() {
				$( "#tabs_folders" ).sortable( { items:"li:not(.header)" } );
				$( "#tabs_folders" ).sortable( { cancel:".header"} );
				$( "#tabs_folders" ).disableSelection();
				$( "#tabs_folders" ).bind( "sortupdate", function(event, ui)
				{
					wait_dialog_show('<?php echo t("Ändere Reihenfolge"); ?>', 0);
					var list = $('#tabs_folders').sortable('toArray');
					
					$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"MailAccountsSort", list:list, type:'folders' }, function($data){ 
						//show_status2($data); return;
						try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
						if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
						wait_dialog_show('<?php echo t("Aktualisiere Ansicht"); ?>', 100);
						mail_accounts_show();
						
						wait_dialog_hide();
					});
				});
			});

			if ( l > 0 )
			{
				$(".list_mail_folders").click(function(){ 
					var id = $( this ).attr( 'id' );
					var tab = id.split("_");
					
					$("#folder_"+$("#mailbox_account_tabs").attr('active_folder')).css("font-weight", "normal");
					
					$("#mailbox_account_tabs").attr('active_folder', tab[1]);
					
					$("#folder_"+tab[1]).css("font-weight", "bold");					
					page_act = 1;
			
					mailbox_show($("#mailbox_input_search_mails").val());
				});
				mailbox_show(0,0);
			}
			setTimeout("mail_check_new()",600000);
			wait_dialog_hide();
		});
	}
	
	function mailbox_show(searchstring)
	{	
		var post_object = new Object();
		post_object['API'] = 'shop';
		post_object['APIRequest'] = 'MailOverviewGet';
		post_object['page_act'] = page_act;
		
		if ( searchstring != '' )
		{
			post_object['search_mail'] = 1;
			//post_object['search_field'] = field;
			post_object['searchstring'] = searchstring;
		}
		post_object['mails_p_page'] = mails_p_page;
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');
	
		document.title = '<?php echo t("Mail-System"); ?>';
		var account_div = $("#account_"+post_object['account']).text();
		account_div = account_div.replace(img_new_mail, "");
		$("#account_"+post_object['account']).html(account_div);
			
		wait_dialog_show('<?php echo t("Öffne Postfach"); ?>', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); return;};
			
			mails_abs = parseInt($xml.find("num_of_msgs").text());
			page_max = Math.floor(mails_abs/mails_p_page);
			if((mails_abs % mails_p_page) > 0) page_max = page_max + 1;
			//alert(page_max);
		
			var search_string = $xml.find("searchstring").text();

			var mailbox_mails =  '<table class="hover" style="max-width:1335px; margin:0px; width:99%; height:99%;">';
			
			mailbox_mails +=  '<tr>';
			//mailbox_mails +=  '	<th>Lfd.-Nr.</th>';
			mailbox_mails +=  '	<th>Flag</th>';
			mailbox_mails +=  '	<th>'+local_mail_uid+'</th>';
			mailbox_mails +=  '	<th>'+local_date+'</th>';
			mailbox_mails +=  '	<th>'+local_sender+'</th>';
			mailbox_mails +=  '	<th colspan="4">'+local_subject+'</th>';
			mailbox_mails += '<th><img src="images/icons/24x24/repeat.png" style="cursor:pointer;" onclick="javascript:mailbox_folders_show();" alt="Aktualisieren" title="Aktualisieren"></th>';
			mailbox_mails +=  '	<th><img src="images/icons/24x24/archive.png" style="cursor:pointer;" onclick="javascript:mail_account_filter_mails();" alt="Postfach filtern" title="Postfach filtern"></th>';
			mailbox_mails +=  '</tr>';
			
			var msg_from;
			var msg_date;
			var msg_subject;
			var lfd_nr = parseInt(((page_act-1)*mails_p_page)+1);
			lfd_nr_min = lfd_nr;
					
			var msg_no = 0;
			//var first_msg = 0;
			var eclass = '';
			var style= '';
			var msg_subject	= '';
		//	var re = new RegExp("/Herzlichen Glückwunsch, .*wurde verkauft!/im");
			
			$xml.find('message').each(function()
			{
				msg_subject = $(this).find('msg_subject').text();
				
				msg_no = $(this).find('msgno').text();
			/*	if ( first_msg == 0 )
				{
					first_msg =	msg_no;
				}*/
				msg_from = $(this).find('msg_from').text().replace(/</g, '&lt');
				msg_from = msg_from.replace(/>/g, '&gt');
				msg_date = new Date(parseInt($(this).find('msg_date').text())*1000)
				msg_date = msg_date.toLocaleString();
				
				if(msg_from.search('ebay@ebay.de')>-1 || msg_from.search('eBay-Mitglied')>-1 || msg_from.search('eBay Member')>-1) 
					msg_subject = msg_subject.replace(/_/g, ' ');
					
				msg_from = msg_from.split('<');
				msg_from = msg_from[0]+'<br><'+msg_from[1];
//				msg_from = msg_from.replace(/&lt/g, '<br />&lt');
				mailbox_mails += '<tr value="'+lfd_nr+'" class="'+eclass+'" style="'+style;
				if ( $(this).find('msg_seen').text() != 1 )
				{
					mailbox_mails += ' font-weight:bold;"';
				}
				mailbox_mails += '">';
				//mailbox_mails +=  '	<td onclick="javascript:show_mail_light('+msg_no+');">' + lfd_nr + '</td>';
				mailbox_mails +=  '	<td onclick="javascript:show_mail_light('+msg_no+');"';
				if ( $(this).find('msg_flag').text() != 0 )
				{
					mailbox_mails +=  ' style="background-color:red;"';
				}
				mailbox_mails +=  '></td>';
				mailbox_mails +=  '	<td onclick="javascript:show_mail_light('+msg_no+');">' + msg_no + '</td>';
				mailbox_mails +=  '	<td onclick="javascript:show_mail_light('+msg_no+');">' + msg_date + '</td>';
				mailbox_mails +=  '	<td onclick="javascript:show_mail_light('+msg_no+');">' + msg_from + '</td>';
				mailbox_mails +=  '	<td style="border-right:none;" class="button_show_mail" msg_no="'+msg_no+'" onclick="javascript:show_mail_light('+msg_no+');">' + msg_subject + '</td>';
				mailbox_mails +=  '	<td style="border-left:none; border-right:none; width:30px; margin:0px; padding:0px;"><img src="images/icons/24x24/shopping_cart.png" style="cursor:pointer;" onclick="javascript:dialog_mail_alocate('+msg_no+');" alt="Order zuweisen" title="Order zuweisen"></td>';
				mailbox_mails +=  '	<td style="border-left:none; border-right:none; width:30px; margin:0px; padding:0px;"><img src="images/icons/24x24/mail_edit.png" style="cursor:pointer;" onclick="javascript:dialog_mail_reply('+msg_no+',1);" alt="Mail beantworten" title="Mail beantworten"></td>';
				
				mailbox_mails +=  '	<td style="border-left:none; border-right:none; width:30px; margin:0px; padding:0px;"><img src="images/icons/24x24/forward_new_mail.png" style="cursor:pointer;" onclick="javascript:dialog_mail_reply('+msg_no+',2);" alt="Mail Weiterleiten" title="Mail Weiterleiten"></td>';
				mailbox_mails +=  '	<td style="border-left:none; border-right:none; width:30px; margin:0px; padding:0px;"><img src="images/icons/24x24/folder.png" style="cursor:pointer; margin-top:2px; margin-bottom:2px;" onclick="javascript:dialog_mail_move('+msg_no+');" alt="Mail verschieben" title="Mail verschieben"></td>';
				mailbox_mails +=  '	<td style="border-left:none; width:30px; margin:0px; padding:0px;"><img src="images/icons/24x24/remove.png" style="cursor:pointer; margin-top:2px; margin-bottom:2px;" onclick="javascript:mail_to_archiv('+msg_no+');" alt="Mail archivieren" title="Mail archivieren"></td>';
				mailbox_mails +=  '</tr>';
				mailbox_mails +=  '<tr id="msg_'+msg_no+'" style="display:none;"></tr>';
				lfd_nr++;
			});
			
			$('#mailbox_mails').html(mailbox_mails);
			
			$(".button_show_mails").click(function(){
				var msg_num = $(this).attr('msg_no');
				$(this).css('font-weight','normal');
				show_mail_light(msg_num);
			});
			
			lfd_nr_max = lfd_nr - 1;
			
			navigation_show(searchstring); 
			//show_mail_light(first_msg);
			
			wait_dialog_hide();
		});
	}
	
	function mails_p_page_set(num)
	{
		page_act = 1;
		mails_p_page = num;
		mailbox_show($("#mailbox_select_search_mails").val(), $("#mailbox_input_search_mails").val());
	}
	
	function navigation_show(searchstring)
	{
		if ( searchstring === 0 )
		{
			searchstring = '';	
		}
		$('#mailbox_controls').empty();
			
		var nav_text = '';
		if(page_act == 1) nav_text += '<b><< < </b>'; else nav_text += '<a href="javascript:page_goto(1);" title="Seite 1"><b><<</a> <a href="javascript:page_goto(' + parseInt(page_act-1) + ')" title="Seite zurück"><</b></a> ';
		if(page_act == 1) nav_text +='1 '; else nav_text += '<a href="javascript:page_goto(1)" title="Seite 1">1</a> ';
		
		for(k = 0; k < 5; k++)
		{
			if(parseInt(page_act-2+k) > 2 && k == 0) nav_text += '... ';
			if(parseInt(page_act-2+k) > 1 && parseInt(page_act-2+k) < page_max)
			{
				if(k == 2)
					nav_text += page_act;
				else
					nav_text += ' <a href="javascript:page_goto(' + parseInt(page_act-2+k) + ')" title="Seite ' + parseInt(page_act-2+k) + '">' + parseInt(page_act-2+k) + '</a> ';
			}
			if(parseInt(page_act-2+k) < parseInt(page_max-1) && k == 4) nav_text += ' ...';
		}
		
		if(page_max > 1)
		{
			if(page_act == page_max) nav_text += page_max + ' '; else nav_text += '<a href="javascript:page_goto(' + page_max + ')" title="Seite ' + page_max + '">' + page_max + '</a> ';
		}
		if(page_act == page_max) nav_text += '<b>> >></b>'; else nav_text += '<a href="javascript:page_goto(' + parseInt(page_act+1) + ')" title="Seite vor"><b>></a> <a href="javascript:page_goto(' + parseInt(page_max) + ')" title="Seite ' + page_max + '">>></b></a> ';
		
		var nav_text_2 = '';
		nav_text_2 += '<p style="display: inline">zur Seite:</p>';
		nav_text_2 += '<input type="text" id="page_input" style="margin-left: 10px; width: 30px;">';
		nav_text_2 += '<input type="button" id="page_button" style="margin-left: 10px;" value="Go!">';
		
		var nav_text_3 = '';
		nav_text_3 += '<p style="display: inline">Emails/Seite: </p>';
		if(mails_p_page == 25) nav_text_3 += '<p style="display: inline;">25 | </p>'; else nav_text_3 += '<a href="javascript:mails_p_page_set(25)">25</a> | ';
		if(mails_p_page == 50) nav_text_3 += '<p style="display: inline;">50 | </p>'; else nav_text_3 += '<a href="javascript:mails_p_page_set(50)">50</a> | ';
		if(mails_p_page == 100) nav_text_3 += '<p style="display: inline">100 | </p>'; else nav_text_3 += '<a href="javascript:mails_p_page_set(100)">100</a> | ';
		if(mails_p_page == 200) nav_text_3 += '<p style="display: inline">200</p>'; else nav_text_3 += '<a href="javascript:mails_p_page_set(200)">200</a>';
		
		var nav_text_4 = '';
		nav_text_4 += 'Angezeigte Treffer: ' + lfd_nr_min + ' - ' + lfd_nr_max + ' von ' + mails_abs;
			
		var navi_div = $('#mailbox_controls');
		var table = '<table style="width:99%;">';
		table += '	<tr>';
		table += '		<td style="padding-left: 10px; padding-right: 10px;">' + nav_text + '</td>';
		table += '		<td style="padding-left: 10px; padding-right: 10px; width:152px;">' + nav_text_2 + '</td>';
		table += '		<td style="padding-left: 10px; padding-right: 10px;">' + nav_text_3 + '</td>';
		table += '		<td style="padding-left: 10px; padding-right: 10px;">' + nav_text_4 + '</td>';
		table += '		<td style="padding-left: 10px; padding-right: 10px; width:130px;"><button id="mailbox_button_signature_edit">Signatur bearbeiten</button></td>';
		table += '		<td style="padding-left: 10px; padding-right: 10px; width:100px; padding:5px;"><button id="mailbox_button_create_mail">Mail schreiben</button></td>';
		table += '		<td style="padding-left: 10px; padding-right: 10px; width:218px;">';
		table += '			<input id="mailbox_input_search_mails" value="'+searchstring+'" />';
		table += '			<button id="mailbox_button_search_mails">Suchen</button>';
		table += '		</td>';
		table += '	</tr>';	
		table += '</table>';
		
		navi_div.html(table);
			
		$('#page_button').click(function(){
			page_goto($('#page_input').val());
		});
		   
		$('#page_input').bind('keypress', function(e) {
			if(e.keyCode==13){
				page_goto($('#page_input').val());
			}
		});
		
		$('#mailbox_button_signature_edit').click(function(){
			dialog_mail_signature_edit();
		});
		
		$('#mailbox_button_create_mail').click(function(){
			dialog_mail_reply(0);
		});	
		
		$('#mailbox_button_search_mails').click(function(){
			mailbox_show($("#mailbox_input_search_mails").val());
		});	
	}
	
	function dialog_mail_signature_edit()
	{
		if ($("#dialog_mail_signature_edit").length == 0)
		{
			var append = '<div id="dialog_mail_signature_edit"></div>';
			$("#content").append(append);
		}
		
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailSignatureGet';
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');

		wait_dialog_show('Suche Signatur', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			
			var signature_id = $xml.find('signature_id').text();
			
			var dialog_mail_signature_edit_content = '<textarea  cols="140" rows="22">'+$xml.find('signature_text').text()+'</textarea>';			
			
			var image_name = '';
			var folder = '';
			var image_path = '';
			dialog_mail_signature_edit_content += '<div>';
			$xml.find('signature_image').each(function(){
				image_name = $(this).find('filename').text()+"."+$(this).find('extension').text();
				image_path = "files/"+$(this).find('folder').text()+"/"+$(this).find('file_id').text()+"."+$(this).find('extension').text();
				
				dialog_mail_signature_edit_content += '	<table style="width:100px; margin-left:10px; margin-right:10px; float:left;">';
				dialog_mail_signature_edit_content += '		<tr><th class="header">'+$(this).find('replace_tag').text()+'</th></tr>';
				dialog_mail_signature_edit_content += '		<tr style="width:100px;"><td style="width:100px;"><img src="'+image_path+'" style="width:100px; padding-right:0px; margin-right:0px;" title="'+image_name+'" alt="'+image_name+'"></td></tr>';
				dialog_mail_signature_edit_content += '		<tr><td>'+image_name+'</td></tr>';
				dialog_mail_signature_edit_content += '	</table>';
			});
			dialog_mail_signature_edit_content += '	<table style="width:100px; margin-left:10px; margin-right:10px; float:left;" >';
			dialog_mail_signature_edit_content += '		<tr>';
			dialog_mail_signature_edit_content += '			<th class="header">Bild zufügen</th>';
			dialog_mail_signature_edit_content += '		</tr>';
			dialog_mail_signature_edit_content += '		<tr style="width:100px;">';
			dialog_mail_signature_edit_content += '			<td style="width:100px;"><img src="images/icons/48x48/add.png" id="button_upload_signature_image" style="cursor:pointer; width:100px;" alt="Bild hinzufügen" title="Bild hinzufügen"></td>';
			dialog_mail_signature_edit_content += '		</tr>';
			dialog_mail_signature_edit_content += '	</table>';
			dialog_mail_signature_edit_content += '</div>';
			
			$("#dialog_mail_signature_edit").html(dialog_mail_signature_edit_content);
			
			$("#button_upload_signature_image").click(function(){		
				window.$upload_end = function()
				{
					$.post("<?php print PATH; ?>soa2/", { API:"cms", APIRequest:"MailSignatureFileAdd", signature_id:signature_id, filename:$filename, filesize:$filesize, filename_temp:$filename_temp  }, function($data){ 
						show_status2($data); return;
						var dialog_mail_signature_image_add = '';
						var Filename = $xml.find('File_name').text();			
						dialog_mail_signature_image_add += '	<table style="width:100px; margin-left:10px; margin-right:10px; float:left;">';
						dialog_mail_signature_image_add += '		<tr><th class="header"><span style="color:red;">replace tag fehlt</span></th></tr>';
						dialog_mail_signature_image_add += '		<tr style="width:100px;"><td style="width:100px;"><img src="'+$xml.find('Path').text()+'" style="width:100px; padding-right:0px; margin-right:0px;" title="'+Filename+'" alt="'+Filename+'"></td></tr>';
						dialog_mail_signature_image_add += '		<tr><td>'+Filename+'</td></tr>';
						dialog_mail_signature_image_add += '	</table>';
			
						$("#dialog_mail_signature_edit").append(dialog_mail_signature_image_add);
					});				
				}
					
				file_upload();
			});
				
			$("#dialog_mail_signature_edit").dialog({	
				buttons:
				[
					{ text: "<?php echo t("Ändern"); ?>", click: function() { signature_edit(signature_id); } },
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:true,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Signatur bearbeiten"); ?>",
				width:900,
				height:700
			});
			
			wait_dialog_hide();
		});
	}
	
	function page_goto(page)
	{
		page = parseInt(page);
		if(!isNaN(page))
		{
			if(page > page_max) page = page_max;
			if(page < 1) page = 1;
			page_act = parseInt(page);
			mailbox_show($("#mailbox_input_search_mails").val());
		}
	}

	function dialog_mail_alocate(msg_num)
	{
		if ($("#dialog_mail_allocate").length == 0)
		{
			var append = '<div id="dialog_mail_allocate"></div>';
			$("#content").append(append);
		}
		var account = $("#mailbox_account_tabs").attr('active_account');
		var folder = $("#mailbox_account_tabs").attr('active_folder');
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'TableDataSelect';
		post_object['select'] = 'locked, locked_by, firstname, lastname';
		post_object['join'] = ', cms_contacts';
		post_object['db'] = 'dbweb';
		post_object['table'] = 'cms_mail_history';
		post_object['where'] = 'WHERE msg_uid='+msg_num+' AND account_id='+account+' AND folder_id='+folder+' AND idCmsUser=locked_by AND idCmsUser!= 0 AND locked_by!=<?php print $_SESSION['id_user']; ?> LIMIT 1';

		wait_dialog_show('Prüfe auf Sperrung', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};

			if ( $xml.find('cms_mail_history').text() != '' )
			{ 
				alert('Diese Mail ist durch '+$xml.find('firstname').text()+' '+$xml.find('lastname').text()+' gesperrt!');
			}
			else
			{
				var post_object = new Object();
				post_object['API'] = 'cms';
				post_object['APIRequest'] = 'MailCheckOrders2'; 
				post_object['order_id'] = $("#mail_alocate_order_id").val();
				post_object['msg_num'] = msg_num;
				post_object['account'] = account;
				post_object['folder'] = folder;
		
				wait_dialog_show('Suche nach möglichen Orders', 0);
				$.post('<?php echo PATH;?>soa2/', post_object, function($data){
					//show_status2($data); return;
					try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
					if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
					
					var msg_from = $xml.find('msg_from').text();
					var msg_date = new Date(parseInt($xml.find('msg_date').text())*1000);
					msg_date = msg_date.toLocaleString();
					var dialog_mail_alocate_content = '<table><tr><th>Datum</th><th>Absender</th><th>Betreff</th></tr>';
					dialog_mail_alocate_content += '<tr><td>'+msg_date+'</td><td>'+msg_from+'</td><td>'+ $xml.find('msg_subject').text()+'</td></tr>';
					dialog_mail_alocate_content += '</table>';
					
					dialog_mail_alocate_content += '<table style="width:100%;"><tr><th></th><th>OrderID</th><th>Plattform</th><th>Kontakt</th><th>Orderstatus</th><th>Artikel</th></tr>';
					var id_order = 0;
					var z = 0;
					var returns = $(this).find('returns').text();
					var exchanges = $(this).find('exchanges').text();
					var match_lvl = 0;
					var dont_reply = 0;
		
					$xml.find('order').each(function(){
						id_order = $(this).find('id_order').text();
						usermail = $(this).find('usermail').text();
						match_lvl = $(this).find('match_lvl').text();
						if ( match_lvl == 5 || match_lvl == 4 )
						{
							dont_reply = 1;
						}
						
						dialog_mail_alocate_content += '<tr>';
						dialog_mail_alocate_content += '	<td>';
						dialog_mail_alocate_content += '		<table style="border:none;">';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;"><input type="radio" name="matched_order"  id_type="2" value="'+id_order+'" /></td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '		<table>';
						
						if ( returns > 0 )
						{	
							dialog_mail_alocate_content += '			<tr style="border:none;">';
							dialog_mail_alocate_content += '				<td style="border:none;">'+returns+' Rücksendungen</td>';
							dialog_mail_alocate_content += '			</tr>';
						}
						if ( exchanges > 0 )
						{
							dialog_mail_alocate_content += '			<tr style="border:none;">';
							dialog_mail_alocate_content += '				<td style="border:none;">'+exchanges+' Umtäusche</td>';
							dialog_mail_alocate_content += '			</tr>';
						}
						dialog_mail_alocate_content += '		</table>';
						dialog_mail_alocate_content += '	</td>';
						dialog_mail_alocate_content += '	<td>';
						dialog_mail_alocate_content += '		<table>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '	<td';
						if ( match_lvl == 5 || match_lvl == 4 )
						{
							dialog_mail_alocate_content += ' style="font-weight:bold; color:blue;"';
						}
						dialog_mail_alocate_content += '>		<ul style="list-style-type:none; padding-left:10px; border:none; width:90px;">';
						dialog_mail_alocate_content += '			<li>O: '+id_order+'</li>';
						dialog_mail_alocate_content += '			<li>E: '+$(this).find('ebay_order_id').text()+'</li>';
						dialog_mail_alocate_content += '			<li>Ama: '+$(this).find('amazon_order_id').text()+'</li>';
//						dialog_mail_alocate_content += '			<li>R: '+id_order+'</li>';
						dialog_mail_alocate_content += '		<ul>';
						dialog_mail_alocate_content += '	</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('ebay_id').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('return_id').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('exchange_id').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '		</table>';
						dialog_mail_alocate_content += '	</td>';
						dialog_mail_alocate_content += '	<td>';
						dialog_mail_alocate_content += '		<table style="border:none;">';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('shop').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('username').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '		</table>';
						dialog_mail_alocate_content += '	</td>';
						dialog_mail_alocate_content += '	<td>';
						dialog_mail_alocate_content += '		<table style="border:none;">';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('bill_company').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('bill_firstname').text()+' '+$(this).find('bill_lastname').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('bill_street').text()+' '+$(this).find('bill_number').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('bill_additional').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).find('bill_zip').text()+' '+$(this).find('bill_city').text()+'</td>';
						dialog_mail_alocate_content += '			</tr>';
						dialog_mail_alocate_content += '			<tr style="border:none;">';
						dialog_mail_alocate_content += '				<td style="border:none;';
						if ( match_lvl || match_lvl == 3 )
						{
							dialog_mail_alocate_content += ' font-weight:bold; color:blue;';
						}
						dialog_mail_alocate_content += '">'+usermail+'</td>';
						dialog_mail_alocate_content += '						</tr>';
						dialog_mail_alocate_content += '					</table>';
						dialog_mail_alocate_content += '				</td>';
						dialog_mail_alocate_content += '				<td>';
						dialog_mail_alocate_content += '					<table style="border:none;">';
						dialog_mail_alocate_content += '						<tr style="border:none;">';
						dialog_mail_alocate_content += '							<td style="border:none;"><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Order.png" alt="Bestellt am:" title="Bestellt am:">'+format_time($(this).find('firstmod').text())+'</td>';
						dialog_mail_alocate_content += '						</tr>';
						dialog_mail_alocate_content += '						<tr style="border:none;">';
						dialog_mail_alocate_content += '							<td style="border:none;"><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Order.png" alt="Bestellt am:" title="Bestellt am:">'+$(this).find('order_status').text()+'</td>';
						dialog_mail_alocate_content += '						</tr>';
						dialog_mail_alocate_content += '					</table>';
						dialog_mail_alocate_content += '				</td>';
						dialog_mail_alocate_content += '				<td';
						if ( match_lvl == 1 )
						{
							dialog_mail_alocate_content += ' style="font-weight:bold; color:blue;"';
						}
						dialog_mail_alocate_content += '>';
						dialog_mail_alocate_content += '					<table style="border:none;">';
						$(this).find('order_item').each(function(){
							dialog_mail_alocate_content += '						<tr style="border:none;">';
							dialog_mail_alocate_content += '							<td style="border:none;">'+$(this).text()+'</td>';
							dialog_mail_alocate_content += '						</tr>';
						});
						dialog_mail_alocate_content += '					</table>';
						dialog_mail_alocate_content += '				</td>';
						dialog_mail_alocate_content += '			</tr>';
						z++;
					});
		
					if ( z > 0 )
					{
						var width =	800;
					}
					else
					{
						var width =	600;
					}
					var height = 500+z*80;
					
					if ( height > 750 )
					{
						height = 750;	
					}
		
					if ( z == 0 )
					{
						dialog_mail_alocate_content += '			<tr><td colspan="6">Keine Bestellungen gefunden!</td></tr>';
						var user_id = $xml.find('id_user').text();
						if ( user_id != '' )
						{
							dialog_mail_alocate_content += '			<tr><td colspan="6"><input type="radio" name="matched_order" id_type="1" value="'+user_id+'" /> Email ist diesem Kunden zugehörig: '+user_id+' '+$xml.find('username').text()+'</td></tr>';
						}
					}
					dialog_mail_alocate_content += '		</table>';
						
					dialog_mail_alocate_content += '		<div>';
					dialog_mail_alocate_content += '			<hr style="margin-bottom:10px;">';
					dialog_mail_alocate_content += '			<div class="header">Manuell zuordnen oder antworten</div>';
					dialog_mail_alocate_content += '			<select type="text" id="mail_alocate_id_type">';
					dialog_mail_alocate_content += '				<option value="1">User ID</option>';
					dialog_mail_alocate_content += '				<option value="2" selected="selected">Order ID</option>';
					dialog_mail_alocate_content += '			</select> eingeben:<input type="text" id="mail_alocate_id"></input>';
					dialog_mail_alocate_content += '			<div colspan="2" id="dialog_mail_alocate_error"></div>';
					dialog_mail_alocate_content += '		</div>';
					dialog_mail_alocate_content += '	</div>';
					
					if ($("#dialog_mail_allocate").length == 0)
					{
						var dialog_div = $('<div id="dialog_mail_allocate"></div>');
						$("#content").append(dialog_div);
					}
					$("#dialog_mail_allocate").html(dialog_mail_alocate_content);
					
					$('input[name="matched_order"]').click(function(){
						 $("#mail_alocate_id_type option[value='"+$(this).attr('id_type')+"']").attr('selected',true);
						$(mail_alocate_id).val($(this).prop('value'));
					});
					
					$("#dialog_mail_allocate").dialog({	
						buttons:
						[
							{ text: "<?php echo t("Zuweisen"); ?>", click: function() { mail_alocate(msg_num); } },
							{ text: "<?php echo t("Abbrechen"); ?>", click: function() { unlock_mail(msg_num); $(this).dialog("close"); } }
						],
						closeText:"<?php echo t("Fenster schließen"); ?>",
						modal:true,
						resizable:true,
						title:"<?php echo t("Mail einer Bestellung zuweisen"); ?>",
						width:width,
						height:height
					});
					
					wait_dialog_hide();
				});
			}
			
			wait_dialog_hide();
		});
	}
	
	function unlock_mail(msg_num)
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailUnlockMail'; //'MailAlocate';
		post_object['msg_num'] = msg_num;
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');

		wait_dialog_show('Entsperre Mail');
//		window.open("backend_crm_orders.php?lang=de&jump_to=order&order_type=0&orderid="+post_object['order_id'],"Bestellmanagment");
		//window.location = "backend_crm_orders.php?lang=de&jump_to=order&order_type=0&orderid="+post_object['order_id'];
		$.post('<?php echo PATH;?>soa2/', post_object, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			wait_dialog_hide();
		});
	}
	
	function mail_alocate(msg_num)
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailAlocate';
		post_object['id'] = $("#mail_alocate_id").val();
		post_object['id_type'] = $("#mail_alocate_id_type").val();
		post_object['msg_num'] = msg_num;
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');

		wait_dialog_show('Weise Mail der gewählten Bestellung zu', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};	
				
			if ( $xml.find('Error').length > 0 )
			{
				alert($xml.find('Error').text());
			}
			else
			{
				if ( post_object['id_type'] == 2 )
				{	
					var dialog_mail_alocate_content = '<br /><br /><a href="backend_crm_orders.php?lang=de&jump_to=order&order_type=0&orderid='+$("#mail_alocate_id").val()+'" target="Bestellmanagment">Zur Bestellung</a>';
					$("#dialog_mail_allocate").append(dialog_mail_alocate_content);
				}
				mailbox_show($("#mailbox_input_search_mails").val());
			}
			
			wait_dialog_hide();
		});
	}
	
	function show_mail_light(msg_num)
	{ 
		var id = "#msg_"+msg_num; 
		if ( !$(id).hasClass('shown') )
		{
			var post_object = new Object();
			post_object['API'] = 'cms';
			post_object['APIRequest'] = 'MailBodyGet';
			post_object['msg_num'] = msg_num;
			post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
			post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');
			post_object['mode'] = 1;
	
			wait_dialog_show('Lade gewählte Mail', 0);
			$.post('<?php echo PATH;?>soa2/', post_object, function($data){
				//show_status2($data); return;
				try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
				if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			
				var error = $xml.find("Error").text();
				if ( error == '' )
				{
					var dialog_show_mail_content = '		<td colspan="10" style="background-color:white;">';

					$xml.find('attachment').each(function()
					{ 
						dialog_show_mail_content += '<span><a href="'+$(this).find('Path').text()+'" target="_blank">'+$(this).find('Filename').text()+'</a></span>';
					});
						dialog_show_mail_content += '</div>';
					}
					
					dialog_show_mail_content += '				<div>'+$xml.find('text').text()+'<div>';					
					dialog_show_mail_content += '</td>';
								
					$(id).html(dialog_show_mail_content);
					$(id).css('display','');
					$(id).addClass('shown');
					
					$(".button_show_attachment").click(function()
					{ 
						show_attachment(msg_num, $(this).text());
					});
					
					wait_dialog_hide();	
			});
		}
		else
		{ 
			var post_object = new Object();
			post_object['API'] = 'cms';
			post_object['APIRequest'] = 'MailBodyGet';
			post_object['msg_num'] = msg_num;
			post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
			post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');
			post_object['mode'] = 3;
	
			wait_dialog_show('Lade gewünschte Mail', 0);
			$.post('<?php echo PATH;?>soa2/', post_object, function($data){
				//show_status2($data); return;
				try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
				if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
				
				$(id).removeClass('shown');
				$(id).empty();
				wait_dialog_hide();
			});
		}
	}
	
	function show_attachment(msg_num, filename)
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailAttachmentToClient';
		post_object['msg_num'] = msg_num;
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['filename'] = filename;

		wait_dialog_show('Lade Anhang', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			
			var new_window = window.open("_blank","Attachment");
			new_window.document.write($xml.find('attachment').text());
			wait_dialog_hide();
		});
	}
	
	function export_file(filedata){
		var uriContent = "data:text/utf-8," + encodeURIComponent(filedata);
		window.open(uriContent);
	}
	
	function dialog_mail_reply(msg_num, mode)
	{	
		if ($("#dialog_mail_reply").length == 0)
		{
			var append = '<div id="dialog_mail_reply"></div>';
			$("#content").append(append);
		}
		
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailBodyGet';
		post_object['msg_num'] = msg_num;
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');
		post_object['mode'] = 2;
		post_object['submode'] = mode;

		wait_dialog_show('Lade gewünschte Mail', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
		
			var order_id = $xml.find('order_id').text();
			var user_id = $xml.find('user_id').text();
			
			var check_history = true;
			if ( msg_num>0 && (order_id == 0 || order_id == '') && mode != 2 )
			{
				check_history = confirm('Keiner Bestellung zugwiesen! Dennoch antworten?');
			}
				
			if ( check_history === true )
			{		
				var dialog_show_mail_content = '<table style="width:98%;">';
				dialog_show_mail_content += '	<tr>';
				dialog_show_mail_content += '		<td>Empfänger</td>';
				dialog_show_mail_content += '		<td style="background-color:white;"><input size="120" type="text" id="dialog_mail_reply_ToReceiver" value="'+$xml.find('From').text()+'" /></td>';
				dialog_show_mail_content += '	<tr>';
				dialog_show_mail_content += '		<td>CC</td>';
				dialog_show_mail_content += '		<td style="background-color:white;"><input size="120" type="text" id="dialog_mail_reply_CC" value="'+$xml.find('CC').text()+'" /></td>';
				dialog_show_mail_content += '	</tr>';
				dialog_show_mail_content += '	<tr>';
				dialog_show_mail_content += '		<td>BCC</td>';
				dialog_show_mail_content += '		<td style="background-color:white;"><input size="120" type="text" id="dialog_mail_reply_BCC" value="'+$xml.find('BCC').text()+'" /></td>';
				dialog_show_mail_content += '	</tr>';
				dialog_show_mail_content += '	<tr>';
				dialog_show_mail_content += '		<td>Absender</td>';
				dialog_show_mail_content += '		<td style="background-color:white;">';
				dialog_show_mail_content += '			<select id="dialog_mail_reply_FromSender">';
				$xml.find('sendermail').each(function(){
					dialog_show_mail_content += $(this).text();
				});
				dialog_show_mail_content += '			</select>';
				dialog_show_mail_content += '	</tr>';
				dialog_show_mail_content += '	<tr>';
				dialog_show_mail_content += '		<td>Betreff</td>';
				
				var subject = '';
				if ( msg_num != 0 && mode != 0 )
				{
					if ( mode == 1 )
					{
						subject += 'Re: ';
					}
					else
					{
						subject += 'Fw: ';
					}
				}
				subject += $xml.find('Subject').text();
				
				dialog_show_mail_content += '		<td style="background-color:white;"><input size="120" type="text" id="dialog_mail_reply_Subject" value="'+subject+'" /></td>';
				dialog_show_mail_content += '	</tr>';
				dialog_show_mail_content += '	<tr>';
				dialog_show_mail_content += '	<tr>';
				dialog_show_mail_content += '		<td colspan="2" style="background-color:white;">';				
				dialog_show_mail_content += '		<textarea cols="140" rows="27" id="dialog_mail_reply_MsgText" >'+$xml.find('text').text()+'</textarea>';
				dialog_show_mail_content += '</td>';
				dialog_show_mail_content += '	</tr>';
				dialog_show_mail_content += '	<tr>';
				dialog_show_mail_content += '		<td colspan="2" id="mail_upload_attachment" style="background-color:white;"><button id="button_upload_attachment" style="float:right;">Datei anhängen</button></td>';
				dialog_show_mail_content += '	</tr>';
				dialog_show_mail_content += '</table>';
			
				$("#dialog_mail_reply").html(dialog_show_mail_content);
				
				$("#button_upload_attachment").click(function(){		
					window.$upload_end = function()
					{ 
						var filepath = '<?php print PATH; ?>'+$filename_temp.replace("../","");
						var mail_anhang_content = '&nbsp;&nbsp;<a href="'+filepath+'" target="_blank" filepath="'+$filename_temp+'" filename="'+$filename+'" class="mail_attachments">'+$filename+'</a>';
					
						$("#mail_upload_attachment").prepend(mail_anhang_content);
					}
						
					file_upload();
				});
				
				$("#dialog_mail_reply").dialog({	
					buttons:
					[
						{ text: "<?php echo t("Senden"); ?>", click: function() { mail_reply(msg_num, order_id, user_id, mode); } },
						{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
					],
					closeText:"<?php echo t("Fenster schließen"); ?>",
					modal:true,
					resizable:true,
					title:"<?php echo t("Mail verfassen"); ?>",
					width:925,
					height:755
				});
			}
			wait_dialog_hide();
		});
	}
	
	function mail_reply(msg_num, order_id, user_id, mode)
	{
		var post_object = new Object();
		post_object["API"] = "cms";
		post_object["APIRequest"]="MailSendMail";		
		post_object["receiver"] = $("#dialog_mail_reply_ToReceiver").val();
		post_object["CC"] = $("#dialog_mail_reply_CC").val();
		post_object["BCC"] = $("#dialog_mail_reply_BCC").val();
		post_object["sender"] = $("#dialog_mail_reply_FromSender").val();
		post_object["subject"] = $("#dialog_mail_reply_Subject").val();
		post_object["message"] = $("#dialog_mail_reply_MsgText").val();
		post_object["user_id"] = <?php print $_SESSION['id_user']; ?>;
		post_object["mode"] = mode;
		post_object["msg_num"] = msg_num;
		
		if ( msg_num > 0 )
		{
			post_object["message"] = post_object["message"].split('==========boundary=old_message==========');
			post_object["message"] = post_object["message"][0];
			post_object["message"] += $("#dialog_mail_reply_OldMsgText").text();
			post_object["order_id"] = order_id;
			post_object["user_id"] = user_id;
		}
		
		post_object['account_id'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder_id'] = $("#mailbox_account_tabs").attr('active_folder');

		post_object['attachments'] = new Object();

		$(".mail_attachments").each(function(){ 
			post_object['attachments'][$(this).attr('filename')] = $(this).attr('filepath');
		});

		wait_dialog_show('Sende gewünschte Mail', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			
			$("#dialog_mail_allocate").dialog("close");
			$("#dialog_mail_reply").dialog("close");
			$("#dialog_mail_forward").dialog("close");
			mailbox_show($("#mailbox_input_search_mails").val());
			wait_dialog_hide();
		});
	}
	
	function mail_to_archiv(msg_num)
	{ 
		var x = 0;
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailToArchiv';
		post_object['msg_num'] = msg_num;
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');

		wait_dialog_show('Archiviere Mail.', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			
			//$("#dialog_forward_mail").dialog("close");
			mailbox_show($("#mailbox_input_search_mails").val());
			wait_dialog_hide();
		});
	}
	
	function dialog_mail_move(msg_num)
	{ //mail_forward(msg_num); return;
		if ($("#dialog_forward_mail").length == 0)
		{
			var append = '<div id="dialog_forward_mail"></div>';
			$("#content").append(append);
		}
		
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'TableDataSelect';
		post_object['db'] = 'dbweb';
		post_object['table'] = 'cms_mail_accounts_folders';
		post_object['select'] = 'id_folder, name';
		post_object['where'] = "WHERE account_id="+$("#mailbox_account_tabs").attr('active_account')+' ORDER BY user_ordering ASC';

//		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');

		wait_dialog_show('Lade verfügbare Ordner', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
		
			var dialog_forward_mail_content = '<table style="width:98%;">';
			dialog_forward_mail_content += '	<tr>';
			dialog_forward_mail_content += '		<th colspan="2">Ordner</th>';
			dialog_forward_mail_content += '	</tr>';

			$xml.find(post_object['table']).each(function(){
				dialog_forward_mail_content += '	<tr>';
				dialog_forward_mail_content += '		<td style="border:none;"><input type="radio" name="move_mail_to" class="move_mail_to" value="'+$(this).find('id_folder').text()+'" /></td>';
				dialog_forward_mail_content += '		<td style="background-color:white;">'+$(this).find('name').text()+'</td>';
				dialog_forward_mail_content += '	</tr>';
			});
			dialog_forward_mail += '</table>';
		
			$("#dialog_forward_mail").html(dialog_forward_mail_content);
			
			$("#dialog_forward_mail").dialog({	
				buttons:
				[
					{ text: "<?php echo t("Mail weiterleiten"); ?>", click: function() { mail_move_to_folder(msg_num); } },
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:true,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Mail in Ordner anderen verschieben"); ?>",
				width:400,
				height:350
			});
			
			wait_dialog_hide();
		});
	}
	
	function mail_move_to_folder(msg_num)
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailMoveToFolder';
		post_object['msg_num'] = msg_num;
		post_object['target'] = '';
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');

		$(".move_mail_to").each(function(){
			if ( $(this).prop('checked') == true )
			{
				post_object['target'] = $(this).prop("value");
			}
		});	
		
		if ( post_object['target'] != '' )
		{
			wait_dialog_show('Verschiebe Mail.', 0);
			$.post('<?php echo PATH;?>soa2/', post_object, function($data){
				//show_status2($data); return;
				try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
				if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
				
				$("#dialog_forward_mail").dialog("close");
				mailbox_show($("#mailbox_input_search_mails").val());
				wait_dialog_hide();
			});
		}
		else
		{
			alert('Ziel benötigt!');	
		}
	}
	
	function dialog_folder_edit_name(folder_id)
	{ 
		if ($("#dialog_folder_edit_name").length == 0)
		{
			var append = '<div id="dialog_folder_edit_name"></div>';
			$("#content").append(append);
		}
		
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'TableDataSelect';
		post_object['db'] = 'dbweb';
		post_object['table'] = 'cms_mail_accounts_folders';
		post_object['select'] = 'name';
		post_object['where'] = "WHERE id_folder="+folder_id+' LIMIT 1';

//		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');

		wait_dialog_show('Lese Ordnename', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
		
			var dialog_forward_mail_content = '<label>Name: </label><input type="text" id="input_folder_name" value="'+$xml.find('name').text()+'" />';
		
			$("#dialog_folder_edit_name").html(dialog_forward_mail_content);
			
			$("#dialog_folder_edit_name").dialog({	
				buttons:
				[
					{ text: "<?php echo t("Ändern"); ?>", click: function() { folder_edit_name(folder_id); } },
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:true,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Ordner umbenennen"); ?>",
				width:250,
				height:140
			});
			
			wait_dialog_hide();
		});
	}
	
	function folder_edit_name(folder_id)
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailFolderEditName';
		post_object['folder'] = folder_id;
		post_object['name'] = $("#input_folder_name").val();

		$(".move_mail_to").each(function(){
			if ( $(this).prop('checked') == true )
			{
				post_object['target'] = $(this).prop("value");
			}
		});	
		
		if ( post_object['target'] != '' )
		{
			wait_dialog_show('Benenne Ordner um.', 0);
			$.post('<?php echo PATH;?>soa2/', post_object, function($data){
				//show_status2($data); return;
				try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
				if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
				
				$("#dialog_folder_edit_name").dialog("close");
				mailbox_folders_show();
				wait_dialog_hide();
			});
		}
		else
		{
			alert('Ziel benötigt!');	
		}
	}
	
	function dialog_folder_create()
	{
		if ($("#dialog_folder_create").length == 0)
		{
			var append = '<div id="dialog_folder_create"></div>';
			$("#content").append(append);
		}
		
		var dialog_folder_create_content = '<table><tr><td>Name: </td><td><input type="text" id="input_folder_create_name" /></td></tr>';
		dialog_folder_create_content += '<tr><td>Kennung: </td><td>INBOX.<input type="text" id="input_folder_create_mailbox" /></td></tr>';
		dialog_folder_create_content += '</table>';
		
		$("#dialog_folder_create").html(dialog_folder_create_content);
			
		$("#dialog_folder_create").dialog({	
			buttons:
			[
				{ text: "<?php echo t("Ändern"); ?>", click: function() { folder_create(); } },
				{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:true,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Ordner umbenennen"); ?>",
			width:250,
			height:300
		});
	}
	
	function folder_create()
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailFolderCreate';
		post_object['name'] = $("#input_folder_create_name").val();
		post_object['mailbox'] = $("#input_folder_create_mailbox").val();
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');
		
		wait_dialog_show('Lege neuen Ordner an.', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			
			$("#dialog_folder_create").dialog("close");
			mailbox_folders_show();
			wait_dialog_hide();
		});
	}
	
	function mail_account_folders_check()
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailFoldersCheck';
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');
		
		wait_dialog_show('Üperprüfe Ordnereinträge in der DB.', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			
			mailbox_folders_show();
			wait_dialog_hide();
		});
	}
	
	function mail_account_filter_mails()
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailFilterMails';
		post_object['account'] = $("#mailbox_account_tabs").attr('active_account');
		post_object['folder'] = $("#mailbox_account_tabs").attr('active_folder');
		
		wait_dialog_show('Suche nach Mails zum archivieren.', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			
			var get_filtered = $xml.find('get_filtered');
			
			if ( get_filtered == 0 )
			{
				alert('E-Mail-Konto wird bereits gefiltert!');	
			}
			
			mailbox_folders_show();
			wait_dialog_hide();
		});
	}
	
	function mail_check_new()
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailCheckNew';
		
		//wait_dialog_show('Suche nach neuen Mails.', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
			
			var set_title = 0;
			var id = 0;
			var account_div = '';
			var new_text = '';
			
			$xml.find('folder_new_msgs').each(function(){
				set_title = 1;
				id = $(this).text();
				account_div = $("#account_"+id).html();
				new_text = img_new_mail+"(";
				account_div = account_div.split("(");
				account_div = account_div[0]+new_text+account_div[1];
				$("#account_"+id).html(account_div);
			});
			
			if ( set_title === 1 )
			{
				document.title = 'Neue Nachricht eingetroffen!';
			}
			
			setTimeout("mail_check_new()",900000);
			//wait_dialog_hide();
		});
	}	
	
	document.title = 'Posteingang';
   	window.name = "Posteingang";
	$("#content").css("width","100%");
	$("#content").css("height","75%");
	$(function(){
		mail_accounts_show();
	});

</script>

<div id="mailbox_accounts">&nbsp;</div>
<div id="mailbox" style="width:100%; height:100%;">
    <div id="mailbox_folders" style="width:14%; min-width:122px; margin-right:30px; float:left; margin-top:10px;">&nbsp;</div>
    <div style="width:84%; margin:0px; padding:0px; float:left; min-width:845px;">
        <div id="mailbox_controls" style="width:98%; height:10%; margin:0px; padding:0px;">&nbsp;</div>
        <div id="mailbox_mails" style="width:98%;">&nbsp;</div>
    </div>
</div>
<?php
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
