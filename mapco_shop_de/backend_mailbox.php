<?php

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

?>

<script type="text/javascript">
	
	var page_act = 1;
	var page_max = 0;
	var mails_abs = 0;
	var mails_p_page = 50;
	var lfd_nr_min = 0;
	var lfd_nr_max = 0;
	
	$(function(){
		backend_mailbox_main();
	});
	
	function backend_mailbox_main()
	{
		html = '';
		html += '<p>';
		html += '<a href="backend_index.php">Backend</a>';
		html += ' > <a href="#">...</a>';
		html += ' > Mailbox';
		html += '</p>';
		html += '<h1>Mailbox</h1>';
		
		html += '	<div id="navigation_div"></div>';
		html += '<div id="backend_mailbox_main_div" style="float: left; max-height: 700px; width:100%; overflow: auto;">';
		//html += '	<div id="navigation_div"></div>';
		html += '	<div id="email_list_div"></div>';
		html += '</div>';
		//html += '<div id="statistics_show" style="float: left; overflow: auto; max-width: 78%; max-height: 700px; margin-left: 5px"></div>';
		
		$("#content").html(html);
		
		mailbox_show();
	}
	
	function mailbox_show()
	{						
		var post_object = new Object();
		post_object['API'] = 'shop';
		post_object['APIRequest'] = 'MailOverviewGet';
		post_object['page_act'] = page_act;
		post_object['mails_p_page'] = mails_p_page;
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2('Die Mailbox konnte nicht gelesen werden.'); wait_dialog_hide(); return;};
			
			//show_status2($data);
			
			mails_abs = parseInt($xml.find("num_of_msgs").text());
			page_max = Math.floor(mails_abs/mails_p_page);
			if((mails_abs % mails_p_page) > 0) page_max = page_max + 1;
			//alert(page_max);
			
			var main = $('#email_list_div');
			var table =  $('<table class="hover" style="margin-top: 0px;"></table>');
			var tr;
			var th;
			var td;
			var msg_from;
			var msg_date;
			var msg_subject;
			var lfd_nr = parseInt(((page_act-1)*mails_p_page)+1);
			lfd_nr_min = lfd_nr;
			
			tr = $('<tr></tr>');
			th = $('<th>Lfd.-Nr.</th>');
			tr.append(th);
			th = $('<th>Msg.-Nr.</th>');
			tr.append(th);
			th = $('<th>Datum</th>');
			tr.append(th);
			th = $('<th>Absender</th>');
			tr.append(th);
			th = $('<th>Betreff</th>');
			tr.append(th);
			table.append(tr);
			
			$xml.find('message').each(function(){
				msg_from = $(this).find('msg_from').text().replace(/</g, '&lt');
				msg_from = msg_from.replace(/>/g, '&gt');
				msg_date = new Date(parseInt($(this).find('msg_date').text())*1000)
				msg_date = msg_date.toLocaleString();
				msg_subject = $(this).find('msg_subject').text()
				if(msg_from.search('ebay@ebay.de')>-1 || msg_from.search('eBay-Mitglied')>-1 || msg_from.search('eBay Member')>-1) 
					msg_subject = msg_subject.replace(/_/g, ' ');
//				msg_from = msg_from.replace(/&lt/g, '<br />&lt');
				tr = $('<tr></tr>');
				td = $('<td>' + lfd_nr + '</td>');
				tr.append(td);
				td = $('<td>' + $(this).find('msgno').text() + '</td>');
				tr.append(td);
				td = $('<td>' + msg_date + '</td>');
				tr.append(td);
				td = $('<td>' + msg_from + '</td>');
				tr.append(td);
				td = $('<td>' + msg_subject + '</td>');
				tr.append(td);
				table.append(tr);
				lfd_nr++;
			});
			$('#email_list_div').empty();
			main.append(table);
			
			lfd_nr_max = lfd_nr - 1;
			
			navigation_show();
			
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
		$('#navigation_div').empty();
		
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
			
		var navi_div = $('#navigation_div');
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

</script>

<?php

	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
