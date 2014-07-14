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
			
			reader_list_view( $xml, article_id );

			$('#img_' + article_id).empty();
			$('#img_' + article_id).append('<img src="<?php echo PATH;?>images/icons/16x16/accept.png" title="gelesen" style="float: right;">');
		});
	}
		
	function backend_index_main()
	{
		var att_cnt = 0;
		var file_ids = new Array();
		
		var post_data = new Object;
		post_data['API'] = 			'shop';
		post_data['APIRequest'] = 	'InternalNewsGet';
		
		$.post('<?php echo PATH;?>soa2/', post_data, function($data)
		{
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); return;}
			if($xml.find('Ack').text() != 'Success'){show_status2($data); return;}
			//show_status2($data);
			if(parseInt($xml.find('num_art').text()) == 0) return;
			
			var main = $('#main_div');
			var table = $('<table style="width: 33%;"></table>');
			main.append(table);
				var tr = $('<tr></tr>');
					var th = $('<th style="width: 30px;">Nr.</th>');
					tr.append(th);
					th = $('<th style="width: 80px;">Datum</th>');
					tr.append(th);
					th = $('<th style="width: 350px;">Betreff</th>');
					tr.append(th);
				table.append(tr);
			var cnt = 1;	
			$xml.find('articles').each(function(){
				if ( $( this ).find( 'published' ).text() == '0' )
				{
					return !false;
				}
				var articles = $(this);
				var date = new Date(parseInt(articles.find('firstmod').text()) * 1000);
				var date_str = date.getDate() + '.' + parseInt(date.getMonth() + 1) + '.' + date.getFullYear();
				tr = $('<tr onclick="toggle(\'tr_' + articles.find('article_id').text() + '\')"  style="background-color: #E6E6E6; cursor: pointer;"></tr>');
					//td = $('<td style="text-align: right;">' + articles.find('ordering').text() + '.</td>');
					td = $('<td style="text-align: right;">' + cnt + '.</td>');
					cnt++;
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
					td = $('<td colspan="3">' + articles.find('introduction').text() + '</td>');
					tr.append(td);
				table.append(tr);
				tr = $('<tr class="tr_' + articles.find('article_id').text() + '" style="display: none;"></tr>');
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
						var txt = '';
						articles.find('file').each(function(){
							att_cnt++;
							//txt += '<br /><a href="<?php echo PATH; ?>files/' + $(this).find('id_file').text().substr(0, 4) + '/' + $(this).find('id_file').text() + '.' + $(this).find('extension').text() + '" target="_blank" id="download_' + att_cnt + '_' + articles.find('article_id').text() + '_' + $(this).find('id_file').text() + '">' + $(this).find('filename').text() + '.' + $(this).find('extension').text() + '</a>';
							txt += '<br /><a href="<?php echo PATH; ?>download.php?id_file=' + $(this).find('id_file').text() + '" id="download_' + att_cnt + '_' + articles.find('article_id').text() + '_' + $(this).find('id_file').text() + '" target="link_frame">' + $(this).find('filename').text() + '.' + $(this).find('extension').text() + '</a><iframe name="link_frame" style="display: none"></iframe>';
							//txt += '<br /><a href="#" id="download_' + att_cnt + '_' + articles.find('article_id').text() + '_' + $(this).find('id_file').text() + '">' + $(this).find('filename').text() + '.' + $(this).find('extension').text() + '</a>';
							file_ids.push( att_cnt + '_' + articles.find('article_id').text() + '_' + $(this).find('id_file').text() );
						});
						td = $('<td colspan="3"><b>Anhänge:</b>' + txt + '</td>');
						tr.append(td);
					table.append(tr);
				}				
				//Gelesen von
/*				
				//Array mit Namen (cms_contacts) und "gelesen"-Informationen
				var look_1 = 'neutral';
				var look_2 = 'neutral';
				var names = new Array();
				var user_cnt = 1;
				var user_mid = Math.floor( articles.find( 'num_user' ).text() / 2 ) + 1;
				articles.find('user').each( function() {
					names[ user_cnt - 1 ] = new Array();
					names[ user_cnt - 1 ][ 'name' ] = $( this ).find( 'firstname' ).text() + ' ' + $( this ).find( 'lastname' ).text();
					names[ user_cnt - 1 ][ 'read' ] = $( this ).find( 'read' ).text();
					user_cnt++; 
				} );
				
				tr = $( '<tr class="tr_' + articles.find( 'article_id' ).text() + '" style="display: none;"></tr>' );
					var txt = '';
					//var user_cnt = 1;
					txt += '<table>';
					for( var i = 0; i < user_mid; i++ ) {
						if ( names[ i ][ 'read' ] == '1' ) look_1 = 'good'; else look_1 = 'bad';
						if ( parseInt( i + user_mid + 1 ) < user_cnt ) { if ( names[ i + user_mid ][ 'read' ] == '1' ) look_2 = 'good'; else look_2 = 'bad' };
						txt += '<tr>';
						txt += '<td style="border: none; text-align: right">' + ( i + 1 ) + '.</td>';
						txt += '<td class="' + look_1 + '" style="border: none; width: 200px;">' + names[ i ][ 'name' ] + '</td>';
						if ( parseInt( i + user_mid + 1 ) < user_cnt )
							txt += '<td style="border: none; text-align: right">' + ( i + user_mid  + 1) + '.</td>';
						else
							txt += '<td style="border: none;"></td>';
						if ( parseInt( i + user_mid + 1 ) < user_cnt )
							txt += '<td class="' + look_2 + '" style="border: none; width: 200px;">' + names[ i + user_mid ][ 'name' ] + '</td>';
						else
							txt += '<td style="border: none;"></td>';
						txt += '</tr>';
					}
					txt += '</table>';
*/					
					//td = $('<td colspan="3" id="read_by_' + articles.find('article_id').text() + '"><b><span class="good">Gelesen (' + articles.find('num_article_read').text() + ')</span> / <span class="bad">nicht gelesen (' + (parseInt(articles.find('num_user').text() - articles.find('num_article_read').text())) + ')</span> von:</b>' + txt + '</td>');
					tr = $( '<tr class="tr_' + articles.find( 'article_id' ).text() + '" style="display: none;"></tr>' );
					td = $('<td colspan="3" id="read_by_' + articles.find('article_id').text() + '"></td>');
					tr.append(td);
				table.append(tr);
				
				reader_list_view( articles, articles.find('article_id').text() );
			});
			
			//download-links	
			for ( n in file_ids ) {
				(function( k ) {
					$( '#download_' + k ).click(function( ) {
							var $arr = k.split( '_' );
							file_downloaded_set( $arr[ 1 ], $arr[ 2 ] );
						}
					);
				})( file_ids[ n ] )
			}
			
		});
	}
	
	function file_downloaded_set( article_id, file_id )
	{
		//if ( <?php echo $_SESSION[ 'id_user' ];?> != 49352 ) return;
		//alert( 'article_id: ' + article_id + ' file_id: ' + file_id );
		var post_data = 			new Object();
		post_data[ 'API' ] = 		'cms';
		post_data[ 'APIRequest' ] = 'ArticleFileDownloadSet';
		post_data[ 'article_id' ] = article_id;
		post_data[ 'file_id' ] = 	file_id;
		
		$.post( '<?php echo PATH;?>soa2/', post_data, function( $data ) {
			try { $xml = $( $.parseXML( $data ) ); } catch($err) { show_status2( $err.message ); return; }
			if ( $xml.find( 'Ack' ).text() != 'Success' ) { show_status2( $data ); return; }
			
			reader_list_view( $xml, article_id );
			
		});
	}
	
	function reader_list_view( $xml, article_id )
	{
		//Array mit Namen (cms_contacts) und "gelesen"-Informationen
		var location = 		'';
		var look_1 = 		'neutral';
		var look_2 = 		'neutral';
		var names = 		new Array();
		var user_cnt = 		1;
		var user_cnt_real = 1;
//		var user_mid = 	Math.floor( $xml.find( 'num_user' ).text() / 2 ) + 1;
		$xml.find('user').each( function() {
			names[ user_cnt - 1 ] = new Array();
			if ( $( this ).find( 'location' ).text() != location )
			{
				names[ user_cnt - 1 ][ 'name' ] = 			$( this ).find( 'location' ).text() + ':';
				names[ user_cnt - 1 ][ 'read' ] = 			3;
				names[ user_cnt - 1 ][ 'downloads' ] = 		$( this ).find( 'downloads' ).text();
				names[ user_cnt - 1 ][ 'user_cnt_real' ] = 	0;
				location = $( this ).find( 'location' ).text();
				user_cnt++;
				names[ user_cnt - 1 ] = new Array();
				names[ user_cnt - 1 ][ 'name' ] = 			$( this ).find( 'firstname' ).text() + ' ' + $( this ).find( 'lastname' ).text();
				names[ user_cnt - 1 ][ 'read' ] = 			$( this ).find( 'read' ).text();
				names[ user_cnt - 1 ][ 'downloads' ] = 		$( this ).find( 'downloads' ).text();
				names[ user_cnt - 1 ][ 'user_cnt_real' ] = 	user_cnt_real;
				user_cnt++;
				user_cnt_real++;
			}
			else
			{
				names[ user_cnt - 1 ][ 'name' ] = 			$( this ).find( 'firstname' ).text() + ' ' + $( this ).find( 'lastname' ).text();
				names[ user_cnt - 1 ][ 'read' ] = 			$( this ).find( 'read' ).text();
				names[ user_cnt - 1 ][ 'downloads' ] = 		$( this ).find( 'downloads' ).text();
				names[ user_cnt - 1 ][ 'user_cnt_real' ] = 	user_cnt_real;
				user_cnt++;
				user_cnt_real++;
			}
		} );
		var user_mid = 	Math.floor( ( user_cnt - 1 ) / 2 ) + 1;
		//show_status2( print_r( names ) );
		//tr = $( '<tr class="tr_' + articles.find( 'article_id' ).text() + '" style="display: none;"></tr>' );
		var txt = '';
		txt += '<b><span class="good">Gelesen (' + $xml.find('num_article_read').text() + ')</span> / <span class="bad">nicht gelesen (' + ($xml.find('num_user').text() - $xml.find('num_article_read').text()) + ')</span> von:</b><br />';
		//var user_cnt = 1;
		txt += '<table>';
		for( var i = 0; i < user_mid; i++ ) {
			
			var down_left = 	new Array();
			var down_right = 	new Array();
			//alert(names[ i ][ 'downloads' ]);
			down_left = 		names[ i ][ 'downloads' ].split( '_' );
			if ( parseInt( i + user_mid + 1 ) < user_cnt ) {
				down_right = 		names[ i + user_mid ][ 'downloads' ].split( '_' );
			}
			var down_left_str = 	''; 
			var down_right_str = 	'';
			
			if ( parseInt( $xml.find( 'num_att' ).text() ) > 0 ) {
				for ( a in down_left )
				{
					if ( down_left[ a ] == 0 )
					{
						down_left_str = down_left_str + '<img src="<?php echo PATH;?>images/icons/16x16/page.png" title="Anhang noch nicht runtergeladen">';
					}
					if ( down_left[ a ] == 1 )
					{
						down_left_str = down_left_str + '<img src="<?php echo PATH;?>images/icons/16x16/page_accept.png" title="Anhang runtergeladen">';
					}
				}
				for ( a in down_right )
				{
					if ( down_right[ a ] == 0 )
					{
						down_right_str = down_right_str + '<img src="<?php echo PATH;?>images/icons/16x16/page.png" title="Anhang noch nicht runtergeladen">';
					}
					if ( down_right[ a ] == 1 )
					{
						down_right_str = down_right_str + '<img src="<?php echo PATH;?>images/icons/16x16/page_accept.png" title="Anhang runtergeladen">';
					}
				}
			}
			
			if ( names[ i ][ 'read' ] == '1' ) look_1 = 'good'; else look_1 = 'bad';
			if ( parseInt( i + user_mid + 1 ) < user_cnt ) { if ( names[ i + user_mid ][ 'read' ] == '1' ) look_2 = 'good'; else look_2 = 'bad' };
			txt += '<tr>';
			if ( names[ i ][ 'user_cnt_real' ] > 0 )
			{
				txt += '<td style="border: none; text-align: right">' + names[ i ][ 'user_cnt_real' ] + '.</td>';
				txt += '<td class="' + look_1 + '" style="border: none; width: 250px;">' + names[ i ][ 'name' ] + '<span style="float: right;">' + down_left_str + '</span></td>';
			}
			else
			{
				txt += '<td style="border: none; text-align: right"></td>';
				txt += '<td style="border: none; width: 250px;"><b>' + names[ i ][ 'name' ] + '</b></td>';
			}
			//txt += '<td class="' + look_1 + '" style="border: none; width: 250px;">' + names[ i ][ 'name' ] + '<span style="float: right;">' + down_left_str + '</span></td>';
			
			if ( parseInt( i + user_mid + 1 ) < user_cnt )
			{
				if ( names[ i + user_mid ][ 'user_cnt_real' ] > 0 )
				{
					txt += '<td style="border: none; text-align: right">' + names[ i + user_mid ][ 'user_cnt_real' ] + '.</td>';
					txt += '<td class="' + look_2 + '" style="border: none; width: 250px;">' + names[ i + user_mid ][ 'name' ] + '<span style="float: right;">' + down_right_str + '</span></td>';
				}
				else
				{
					txt += '<td style="border: none; text-align: right"></td>';
					txt += '<td style="border: none; width: 250px;"><b>' + names[ i + user_mid ][ 'name' ] + '</b></td>';
				}
			}
			else
				txt += '<td style="border: none;"></td><td style="border: none;"></td>';
			txt += '</tr>';
		}
		txt += '</table>';
				
		$('#read_by_' + article_id).empty();
		$('#read_by_' + article_id).append(txt);
	}
	
	function toggle(class_id)
	{
		$('.' + class_id).toggle(500);
	}
	
</script>

<?php	
	if( $_SESSION["id_user"]==21371 )
	{
		//get menus
		$menus=array();
		$results=q("SELECT * FROM cms_menus WHERE site_id IN (0, ".$_SESSION["id_site"].");", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$menus[]=$row["id_menu"];
		}
		//get menuitems
		$menuitems=array();
		$results=q("SELECT * FROM cms_menuitems WHERE menu_id IN (".implode(", ", $menus).");", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$menuitems[]=$row["id_menuitem"];
		}
		//analyze translations
		//coming soon

		//analyze SEO
		$missing_seo=array();
		$results=q("SELECT * FROM cms_menuitems_languages WHERE menuitem_id IN (".implode(", ", $menuitems).");", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if( $row["meta_title"]=="" or $row["meta_description"]=="" )
			{
				$missing_seo[$row["language_id"]][]=$row;
			}
		}
		//show SEO tips
		echo '<table>';
		echo '<tr><th colspan="2">SEO-Links</th></tr>';
		$cms_languages_results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
		while( $cms_languages=mysqli_fetch_array($cms_languages_results) )
		{
			echo '<tr>';
			echo '	<td>'.$cms_languages["language"].'</td>';
			echo '	<td>'.sizeof($missing_seo[$cms_languages["id_language"]]).'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	elseif($_SESSION["id_site"]<18)
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
