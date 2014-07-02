<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	//echo '<a href="backend_index.php">Backend</a> >';
	echo '<a href="">Backend</a> >';
	echo '</p>';
?>

<script type="text/javascript">
	
	function article_read_set(article_id)
	{
		var $post_data = new Object();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "ArticleReadSet";
		$post_data['article_id'] = article_id;
		
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data){
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); return;}
			if($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			//show_status2($data);
			$('#read_set_button_' + article_id).hide();
			var user_cnt = 1;
			var txt = '';
			txt += '<b>Bereits gelesen von:</b>';
			$xml.find('user').each(function(){	
				txt += '<br />' + user_cnt + '. ' + $(this).find('firstname').text() + ' ' + $(this).find('lastname').text();;
				user_cnt++;
			});
			$('#read_by_' + article_id).empty();
			$('#read_by_' + article_id).append(txt);
			$('#img_' + article_id).empty();
			$('#img_' + article_id).append('<img src="<?php echo PATH;?>images/icons/16x16/accept.png" title="gelesen" style="float: right;">');
		});
	}
		
	function backend_index_main()
	{
		var post_data = new Object;
		post_data['API'] = 'shop';
		post_data['APIRequest'] = 'InternalNewsGet';
		
		$.post('<?php echo PATH;?>soa2/', post_data, function($data){
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); return;}
			if($xml.find('Ack').text() != 'Success'){show_status2($data); return;}
			//show_status2($data);
			if(parseInt($xml.find('num_art').text()) == 0) return;
			
			var main = $('#main_div');
			var table = $('<table style="width: 33%;"></table>');
				var tr = $('<tr></tr>');
					var th = $('<th style="width: 30px;">Nr.</th>');
					tr.append(th);
					th = $('<th style="width: 80px;">Datum</th>');
					tr.append(th);
					th = $('<th style="width: 350px;">Betreff</th>');
					tr.append(th);
				table.append(tr);
			$xml.find('articles').each(function(){
				var articles = $(this);
				var date = new Date(parseInt(articles.find('firstmod').text()) * 1000);
				var date_str = date.getDate() + '.' + parseInt(date.getMonth() + 1) + '.' + date.getFullYear();
				tr = $('<tr onclick="toggle(\'tr_' + articles.find('article_id').text() + '\')"  style="background-color: #E6E6E6; cursor: pointer;"></tr>');
					td = $('<td style="text-align: right;">' + articles.find('ordering').text() + '.</td>');
					tr.append(td);
					td = $('<td style="text-align: right">' + date_str + '</td>'),
					tr.append(td);
					if(articles.find('article_read').text() == '0')
						td = $('<td><b>' + articles.find('title').text() + '</b><span id="img_' + articles.find('article_id').text() + '"><img src="<?php echo PATH;?>images/icons/16x16/warning.png" title="noch nicht gelesen" style="float: right;"></span></td>');
					else
						td = $('<td><b>' + articles.find('title').text() + '</b><span id="img_' + articles.find('article_id').text() + '"><img src="<?php echo PATH;?>images/icons/16x16/accept.png" title="gelesen" style="float: right;"></span></td>');
					tr.append(td);
				table.append(tr);
				tr = $('<tr class="tr_' + articles.find('article_id').text() + '" style="display: none;"></tr>');
					//td = $('<td colspan="2" style="border: none;"></td>');
					//tr.append(td);
					//td = $('<td>' + articles.find('introduction').text() + '</td>');
					td = $('<td colspan="3">' + articles.find('introduction').text() + '</td>');
					tr.append(td);
				table.append(tr);
				tr = $('<tr class="tr_' + articles.find('article_id').text() + '" style="display: none;"></tr>');
					//td = $('<td colspan="2" style="border: none;"></td>');
					//tr.append(td);
					if(articles.find('article_read').text() == '0')
						td = $('<td colspan="3">' + articles.find('article').text() + '<span id="read_set_button_' + articles.find('article_id').text() + '"><br /><br /><input type="button" value="Als gelesen markieren" onclick="article_read_set(\'' + articles.find('article_id').text() + '\')" style="cursor: pointer;"></span></td>');
					else
						td = $('<td colspan="3">' + articles.find('article').text() + '</td>');
					tr.append(td);
				table.append(tr);
				//Anhänge
				if(articles.find('num_att').text() != 0)
				{
					tr = $('<tr class="tr_' + articles.find('article_id').text() + '" style="display: none;"></tr>');
						//td = $('<td colspan="2" style="border: none;"></td>');
						//tr.append(td);
						var txt = '';
						articles.find('file').each(function(){
							txt += '<br /><a href="<?php echo PATH; ?>files/' + $(this).find('id_file').text().substr(0, 4) + '/' + $(this).find('id_file').text() + '.' + $(this).find('extension').text() + '" target="_blank">' + $(this).find('filename').text() + '.' + $(this).find('extension').text() + '</a>';
						});
						td = $('<td colspan="3"><b>Anhänge:</b>' + txt + '</td>');
						tr.append(td);
					table.append(tr);
				}
				tr = $('<tr class="tr_' + articles.find('article_id').text() + '" style="display: none;"></tr>');
					//td = $('<td colspan="2" style="border: none;"></td>');
					//tr.append(td);
					var txt = '';
					var user_cnt = 1;
					articles.find('user').each(function(){
						txt += '<br />' + user_cnt + '. ' + $(this).find('firstname').text() + ' ' + $(this).find('lastname').text();
						user_cnt++;
					});
					td = $('<td colspan="3" id="read_by_' + articles.find('article_id').text() + '"><b>Bereits gelesen von:</b>' + txt + '</td>');
					tr.append(td);
				table.append(tr);
			});	
			main.append(table);
		});
	}
	
	function toggle(class_id)
	{
		$('.' + class_id).toggle(500);
	}
	
</script>

<?php	
	if($_SESSION["id_site"]<18)
	{
		echo '<h1>Interne News</h1>';
		echo '<div id="main_div"></div>';
		echo '<script>backend_index_main()</script>';
	}
	
	else
	{
		echo '<h1>Favoriten</h1>';
	}
	

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
