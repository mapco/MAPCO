<?php

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

?>

<style type="text/css">

	.list_mail_accounts:hover { background-color:RGB(240,240,240); }

</style>

<script type="text/javascript">
	
	var active_account;
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
/*		html = '';
		html += '<p>';
		html += '<a href="backend_index.php">Backend</a>';
		html += ' > <a href="#">...</a>';
		html += ' > Mailbox';
		html += '</p>';
		html += '<h1>Mailbox</h1>';
		
		html += '	<div id="accounts_div" active_account="0"></div>';
		html += '	<div id="navigation_div"></div>';
		html += '<div id="backend_mailbox_main_div" style="float: left; max-height: 700px; width:100%; overflow: auto;">';
		//html += '	<div id="navigation_div"></div>';
		html += '	<div id="email_list_div"></div>';
		html += '</div>';
		//html += '<div id="statistics_show" style="float: left; overflow: auto; max-width: 78%; max-height: 700px; margin-left: 5px"></div>';
		
		$("#mailbox").html(html);
	*/	
		mail_accounts_show()
	}
	
	function mail_accounts_show()
	{
		var userrole_id = <?php print $_SESSION['userrole_id']; ?>;
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'TableDataSelect';
		post_object['db'] = 'dbweb';
		post_object['where'] = 'ORDER BY id_account DESC';

		if ( userrole_id != 1 )
		{
			post_object['table'] = 'cms_mail_accounts_users';
			post_object['join'] = ', cms_mail_accounts';
			post_object['select'] = 'account_id, title';
			post_object['where'] = 'WHERE user_id=<?php print $_SESSION['id_user']; ?> AND id_account=account_id ORDER BY ordering ASC';
		}
		else
		{
			post_object['table'] = 'cms_mail_accounts';
			post_object['select'] = 'id_account AS account_id, title';
			post_object['where'] = 'ORDER BY ordering ASC';
		}
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data);  return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2(); wait_dialog_hide(); return;};
			
			var l = 0;
			var k = 0;
			var account_id = 0;
			var div_accounts = '<ul id="tabs_accounts">';
			if ( $xml.find(post_object['table']) )
			{
				var style = '';
				$xml.find(post_object['table']).each(function(){
					account_id = $(this).find("account_id").text();
					if ( l == 0 )
					{
						style = ' font-weight:bold;';
						active_account = account_id;	
					}
					else
					{
						style = '';	
					}
					k = l + 1;
					div_accounts += '	<li id="account_'+account_id+'" class="list_mail_accounts" style="list-style-type:none;'+style+'">'+$(this).find("title").text()+'</li>';
					l++;
				});
			}
			else
			{
				div_accounts += '	<li id="account_0">Kein Zugang zum Posteingang eingerichtet!</li>';
			}				
			div_accounts += '</ul>';
			
			$("#mailbox_accounts").append(div_accounts);
			
			if ( $xml.find(post_object['table']) )
			{
				$(".list_mail_accounts").click(function(){
					var id = $( this ).attr( 'id' );
					var tab = id.split("_");
					
					active_account = tab[1];
					mailbox_show();
				});
				mailbox_show();
			}
		});
	}
	
	function mailbox_show()
	{
		var post_object = new Object();
		post_object['API'] = 'shop';
		post_object['APIRequest'] = 'MailOverviewGet';
		post_object['page_act'] = page_act;
		post_object['mails_p_page'] = mails_p_page;
		post_object['account'] = active_account;
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2('Die Mailbox konnte nicht gelesen werden.'); wait_dialog_hide(); return;};
			
			mails_abs = parseInt($xml.find("num_of_msgs").text());
			page_max = Math.floor(mails_abs/mails_p_page);
			if((mails_abs % mails_p_page) > 0) page_max = page_max + 1;
			//alert(page_max);
		
			var mailbox_mails =  '<table class="hover" style="margin:0px; width:99%; height:99%;">';
			
			mailbox_mails +=  '<tr>';
			mailbox_mails +=  '	<th>Lfd.-Nr.</th>';
			mailbox_mails +=  '	<th>Msg.-Nr.</th>';
			mailbox_mails +=  '	<th>Datum</th>';
			mailbox_mails +=  '	<th>Absender</th>';
			mailbox_mails +=  '	<th colspan=2>Betreff</th>';
			mailbox_mails +=  '</tr>';
			
			var msg_from;
			var msg_date;
			var msg_subject;
			var lfd_nr = parseInt(((page_act-1)*mails_p_page)+1);
			lfd_nr_min = lfd_nr;
					
			var msg_no = 0;
			var first_msg = 0;
			
			$xml.find('message').each(function(){
				msg_no = $(this).find('msgno').text();
				if ( first_msg == 0 )
				{
					first_msg =	msg_no;
				}
				msg_from = $(this).find('msg_from').text().replace(/</g, '&lt');
				msg_from = msg_from.replace(/>/g, '&gt');
				msg_date = new Date(parseInt($(this).find('msg_date').text())*1000)
				msg_date = msg_date.toLocaleString();
				msg_subject = $(this).find('msg_subject').text()
				if(msg_from.search('ebay@ebay.de')>-1 || msg_from.search('eBay-Mitglied')>-1 || msg_from.search('eBay Member')>-1) 
					msg_subject = msg_subject.replace(/_/g, ' ');
					
				msg_from = msg_from.split('<');
				msg_from = msg_from[0]+'<br><'+msg_from[1];
//				msg_from = msg_from.replace(/&lt/g, '<br />&lt');
				mailbox_mails += '<tr value="'+lfd_nr+'" onclick="javascript:show_mail_light('+msg_no+');"';
				if ( $(this).find('msg_seen').text() != 1 )
				{
					mailbox_mails += ' style="font-weight:bold;"';
				}
				mailbox_mails += '>';
				mailbox_mails +=  '	<td>' + lfd_nr + '</td>';
				mailbox_mails +=  '	<td>' + msg_no + '</td>';
				mailbox_mails +=  '	<td>' + msg_date + '</td>';
				mailbox_mails +=  '	<td>' + msg_from + '</td>';
				mailbox_mails +=  '	<td style="border-right:none;">' + msg_subject + '</td>';
				mailbox_mails +=  '	<td style="border-left:none;"><img src="images/icons/24x24/shopping_cart.png" style="cursor:pointer;" onclick="javascript:dialog_mail_alocate('+msg_no+');" alt="Order zuweisen" title="Order zuweisen"></td>';
				mailbox_mails +=  '</tr>';
				lfd_nr++;
				
			});
					
			$('#mailbox_mails').html(mailbox_mails);
			
			lfd_nr_max = lfd_nr - 1;
			
			navigation_show(); 
			show_mail_light(first_msg);
			
			wait_dialog_hide();
		});
	}
	
	function mails_p_page_set(num)
	{
		page_act = 1;
		mails_p_page = num;
		mailbox_show();
	}
	
	function navigation_show()
	{
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
		var table = $('<table></table>');
		var tr = $('<tr></tr>');
		var td = $('<td style="padding-left: 30px; padding-right: 30px;">' + nav_text + '</td>');
		tr.append(td);
		td = $('<td style="padding-left: 30px; padding-right: 30px;">' + nav_text_2 + '</td>');
		tr.append(td);
		td = $('<td style="padding-left: 30px; padding-right: 30px;">' + nav_text_3 + '</td>');
		tr.append(td)
		td = $('<td style="padding-left: 30px; padding-right: 30px;">' + nav_text_4 + '</td>');
		tr.append(td);
		table.append(tr);
		navi_div.append(table);
			
		$('#page_button').click(function(){
			page_goto($('#page_input').val());
		});
		   
		$('#page_input').bind('keypress', function(e) {
			if(e.keyCode==13){
				page_goto($('#page_input').val());
			}
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
			mailbox_show();
		}
	}

	function dialog_mail_alocate(msg_num)
	{
		if ($("#dialog_mail_allocate").length == 0)
		{
			var append = '<div id="dialog_mail_allocate"></div>';
			$("#content").append(append);
		}
		//var account = $("#navigation_div").attr('active_account');
		
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailCheckOrders2'; 
		post_object['order_id'] = $("#mail_alocate_order_id").val();
		post_object['msg_num'] = msg_num;
		post_object['account'] = active_account;

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
			
			dialog_mail_alocate_content += '<table><tr><th></th><th>OrderID</th><th>Plattform</th><th>Kontakt</th><th>Orderstatus</th><th>Artikel</th></tr>';
			var id_order = 0;
			var z = 0;
			var returns = $(this).find('returns').text();
			var exchanges = $(this).find('exchanges').text();

			$xml.find('order').each(function(){
				id_order = $(this).find('id_order').text();
				usermail = $(this).find('usermail').text();
				
				dialog_mail_alocate_content += '<tr>';
				dialog_mail_alocate_content += '	<td>';
				dialog_mail_alocate_content += '		<table style="border:none;">';
				dialog_mail_alocate_content += '			<tr style="border:none;">';
				dialog_mail_alocate_content += '				<td style="border:none;"><input type="radio" name="matched_order" value="'+id_order+'" /></td>';
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
				if ( $(this).find('match_lvl').text() == 4 || $(this).find('match_lvl').text() == 3 )
				{
					dialog_mail_alocate_content += ' style="font-weight:bold; color:blue;"';
				}
				dialog_mail_alocate_content += '>'+id_order+'</td>';
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
				if ( $(this).find('match_lvl').text() == 4 || $(this).find('match_lvl').text() == 2 )
				{
					dialog_mail_alocate_content += ' font-weight:bold; color:blue;';
				}
				dialog_mail_alocate_content += '">'+usermail+'</td>';
				dialog_mail_alocate_content += '			</tr>';
				dialog_mail_alocate_content += '		</table>';
				dialog_mail_alocate_content += '	</td>';
				dialog_mail_alocate_content += '	<td>';
				dialog_mail_alocate_content += '		<table style="border:none;">';
				dialog_mail_alocate_content += '			<tr style="border:none;">';
				dialog_mail_alocate_content += '				<td style="border:none;"><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Order.png" alt="Bestellt am:" title="Bestellt am:">'+format_time($(this).find('firstmod').text())+'</td>';
				dialog_mail_alocate_content += '			</tr>';
				dialog_mail_alocate_content += '			<tr style="border:none;">';
				dialog_mail_alocate_content += '				<td style="border:none;"><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Order.png" alt="Bestellt am:" title="Bestellt am:">'+$(this).find('order_status').text()+'</td>';
				dialog_mail_alocate_content += '			</tr>';
				dialog_mail_alocate_content += '		</table>';
				dialog_mail_alocate_content += '	</td>';
				dialog_mail_alocate_content += '	<td';
				if ( $(this).find('match_lvl').text() == 1 )
				{
					dialog_mail_alocate_content += ' style="font-weight:bold; color:blue;"';
				}
				dialog_mail_alocate_content += '>';
				dialog_mail_alocate_content += '		<table style="border:none;">';
				$(this).find('order_item').each(function(){
					dialog_mail_alocate_content += '			<tr style="border:none;">';
					dialog_mail_alocate_content += '				<td style="border:none;">'+$(this).text()+'</td>';
					dialog_mail_alocate_content += '			</tr>';
				});
				dialog_mail_alocate_content += '		</table>';
				dialog_mail_alocate_content += '	</td>';
				dialog_mail_alocate_content += '</tr>';
				z++;
			});

			if ( z > 0 )
			{
				var width =	800;
			}
			else
			{
				var width =	500;
			}
			var height = 350+z*70;
			
			if ( height > 650 )
			{
				height = 650;	
			}
			
			if ( z == 0 )
			{
				dialog_mail_alocate_content += '<tr><td colspan="6">Keine möglichen Orders gefunden!</td></tr>'
			}
			dialog_mail_alocate_content += '</table>';
				
			dialog_mail_alocate_content += '<div><hr style="margin-bottom:10px;"><label>Order ID eingeben: </label><input type="text" id="mail_alocate_order_id"></input><div id="dialog_mail_alocate_error"></div></div>';
			
			if ($("#dialog_mail_allocate").length == 0)
			{
				var dialog_div = $('<div id="dialog_mail_allocate"></div>');
				$("#content").append(dialog_div);
			}
			$("#dialog_mail_allocate").html(dialog_mail_alocate_content);
			
			$('input[name="matched_order"]').click(function(){
				$(mail_alocate_order_id).val($(this).prop('value'));
			});
			
			$("#dialog_mail_allocate").dialog({	
				buttons:
				[
					{ text: "<?php echo t("Zuweisen"); ?>", click: function() { mail_alocate(msg_num); } },
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:true,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Mail einer Bestellung zuweisen"); ?>",
				width:width,
				height:height
			});
			
			wait_dialog_hide();
		});
		
		/*
		if ($("#dialog_mail_allocate").length == 0)
		{
			var dialog_div = $('<div id="dialog_mail_allocate"></div>');
			$("#content").append(dialog_div);
		}
		$("#dialog_mail_allocate").empty().append('<input type="text" id="mail_alocate_order_id"></input><div id="dialog_mail_alocate_error"></div>');
		
		$("#dialog_mail_allocate").dialog({	
			buttons:
			[
				{ text: "<?php echo t("Zuweisen"); ?>", click: function() { mail_alocate(msg_num); } },
				{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Mail einer Bestellung zuweisen"); ?>",
			width:300
		});	*/
	}
	
	function mail_alocate(msg_num)
	{	//$(
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailAlocate'; //'MailAlocate';
		post_object['order_id'] = $("#mail_alocate_order_id").val();
		post_object['msg_num'] = msg_num;
		post_object['account'] = active_account;

		wait_dialog_show();
		window.open("backend_crm_orders.php?lang=de&jump_to=order&order_type=0&orderid="+post_object['order_id'],"Bestellmanagment");
		//window.location = "backend_crm_orders.php?lang=de&jump_to=order&order_type=0&orderid="+post_object['order_id'];
		/*$.post('<?php echo PATH;?>soa2/', post_object, function($data){ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};	
				
			if ( $xml.find('Error').length > 0 )
			{
				alert($xml.find('Error').text());
			}
			else
			{
				$("#dialog_mail_allocate").dialog("close");
				window.location = "backend_crm_orders.php?lang=de&order_id="+post_object['order_id'];
//				mailbox_show();
			}
*/
			wait_dialog_hide();
	//	});*/
	}
	
	function show_mail_light(msg_num)
	{ 
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailBodyGet';
		post_object['msg_num'] = msg_num;
		post_object['account'] = active_account;
		post_object['mode'] = 'light';

		wait_dialog_show('Lade gewünschte Mail', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
		
			var dialog_show_mail_content = '<table style="width:98%;">';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td>Absender</td>';
			dialog_show_mail_content += '		<td style="background-color:white;">'+$xml.find('reply_toaddress').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td>cc</td>';
			dialog_show_mail_content += '		<td style="background-color:white;">'+$xml.find('cc').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td>bcc</td>';
			dialog_show_mail_content += '		<td style="background-color:white;">'+$xml.find('bcc').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td>Betreff</td>';
			dialog_show_mail_content += '		<td style="background-color:white;">'+$xml.find('subject').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td colspan="2" style="background-color:white;">'+$xml.find('text').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '</table>';
		
			$("#mailbox_mail").html(dialog_show_mail_content);
			wait_dialog_hide();
		});
	}
	
	function dialog_show_mail(msg_num)
	{
		if ($("#dialog_show_mail").length == 0)
		{
			var append = '<div id="dialog_show_mail"></div>';
			$("#content").append(append);
		}
		
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'MailBodyGet';
		post_object['msg_num'] = msg_num;
		post_object['account'] = active_account;
		post_object['mode'] = 'full';

		wait_dialog_show('Lade gewünschte Mail', 0);
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			//show_status2($data); return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2($data); wait_dialog_hide(); return;};
		
			var dialog_show_mail_content = '<table style="width:98%;">';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td>Absender</td>';
			dialog_show_mail_content += '		<td style="background-color:white;">'+$xml.find('reply_toaddress').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td>cc</td>';
			dialog_show_mail_content += '		<td style="background-color:white;">'+$xml.find('cc').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td>bcc</td>';
			dialog_show_mail_content += '		<td style="background-color:white;">'+$xml.find('bcc').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td>Betreff</td>';
			dialog_show_mail_content += '		<td style="background-color:white;">'+$xml.find('subject').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '	<tr>';
			dialog_show_mail_content += '		<td colspan="2" style="background-color:white;">'+$xml.find('text').text()+'</td>';
			dialog_show_mail_content += '	</tr>';
			dialog_show_mail_content += '</table>';
		
			$("#dialog_show_mail").html(dialog_show_mail_content);
			
			$("#dialog_show_mail").dialog({	
				buttons:
				[
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:true,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Mail einer Bestellung zuweisen"); ?>",
				width:825,
				height:625
			});
			
			wait_dialog_hide();
		});
	}

	window.name = "Posteingang";
	$("#content").css("width","100%");
	$("#content").css("height","75%");
	$(function(){
		mail_accounts_show();
	});

</script>
<div id="mailbox" style="width:100%; height:100%;">
    <div id="mailbox_accounts" style="width:15%; height:100%; background-color:RGB(225,225,225); vertical-align:top; float:left;">&nbsp;</div>
    <div style="width:85%; height:100%; margin:0px; padding:0px; float:left;">
        <div id="mailbox_controls" style="height:10%; margin:0px; padding:0px;">&nbsp;</div>
        <div id="mailbox_mails" style="height:45%; overflow:scroll;">&nbsp;</div>
        <div id="mailbox_mail" style="height:45%; overflow:scroll;">&nbsp;</div>
    </div>
</div>
<?php
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
