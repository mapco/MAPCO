<?php
	/***** Author Sven E. *****/
	/*** Firstmod 25.03.2014 **/
	/*** Lastmod 25.03.2014 ***/

	include("../../../config.php");
	include("../../../functions/cms_t.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>
	var $auction_id=new Array();
	var $auction_call=new Array();
	var $auction_ItemID=new Array();
	
	function top_offer_toggle($id_auction)
	{
		wait_dialog_show('Ändere Top-Angebots-Status',0);
		$.post("<?php echo PATH; ?>soa2/", { API:"ebay", APIRequest:"AuctionTopOffer", id_auction:$id_auction }, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			load_auctions();
		});
	}

	function auctions_submit()
	{
		var id_item = $("#editor_tabs").attr('shop_item');
		var id_accountsite = $("#tabs_accounts_ul").attr('active_account_site');
		
		wait_dialog_show('Ermittle Auktionsdaten',0);
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"AuctionsGet", id_accountsite:id_accountsite, id_item:id_item }, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$auction_id=new Array();
			$auction_call=new Array();
			$auction_ItemID=new Array();
			var $i=0;
			
			$xml.find("Auction").each(function()
			{
				$auction_id[$i]=$(this).find("id_auction").text();
				$auction_call[$i]=$(this).find("Call").text();
				$auction_ItemID[$i]=$(this).find("ItemID").text();
				$i++;
			});
			
			wait_dialog_show('Ermittle Auktionsdaten',100);
			auctions_submit_auction(0);
		});			
	}


	function auctions_submit_auction($i)
	{
		if( $i==$auction_id.length )
		{
			load_auctions();
			wait_dialog_hide();
			show_status("Alle Auktionen erfolgreich an eBay gesendet.");
			return;
		}
		
		$percent=Math.floor($i/$auction_id.length*100);
		wait_dialog_show("Aktualisiere Auktion "+($i+1)+" von "+$auction_id.length, $percent);

		if( $auction_call[$i]=="AddItem" && $auction_ItemID[$i]>0 )
		{
			auctions_submit_auction($i+1);
			return;
		}

		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:$auction_call[$i], id_auction:$auction_id[$i] },
			function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					if( $data.indexOf('<Error ErrorCode="291">')>-1 )
					{
						item_create_auctions(true);
						return;
					}
					show_status2($data);
					return;
				}

				auctions_submit_auction($i+1);
				return;
			}
		);
	}
	

	function auction_submit($Action, $id_auction)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:$Action, id_auction:$id_auction },
			function($data)
			{
				wait_dialog_hide();
				try

				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				if( $Action=="AddItem" ) show_status("Auktion erfolgreich erstellt.");
				else if( $Action=="ReviseItem" ) show_status("Auktion erfolgreich aktualisiert.");
				else if( $Action=="EndItem" ) show_status("Auktion erfolgreich beendet.");
				load_auctions();
			}
		);
	}
	
	function get_item($ItemID)
	{
		var id_account = $("#tabs_accounts_ul").attr('active_account');
		$postdata=new Object();
		$postdata["API"]="ebay";
		$postdata["APIRequest"]="GetItem";
		$postdata["id_account"]=id_account;
		$postdata["ItemID"]=$ItemID;
		wait_dialog_show("Rufe Auktionsdaten bei eBay ab");
		soa2($postdata, "get_item_success", "obj");
	}
	
	function get_item_success($xml)
	{
		show_status2($xml);
	}
	
	function get_ebay_details($ItemID)
	{
		var id_accountsite = $("#tabs_accounts_ul").attr('active_account_site');
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetEbayDetails", id_accountsite:id_accountsite },
			function($data)
			{
				show_status2($data);
				wait_dialog_hide();
			}
		);
	}
	
	function get_last_response($id_auction)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"AuctionGet", id_auction:$id_auction },
			function($data)
			{
				wait_dialog_hide();
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				show_status2($xml.find("responseXml").text());
			}
		);
	}
		
	function item_create_auctions($submit)
	{
		wait_dialog_show();
		
		var id_item = $("#editor_tabs").attr('shop_item');
		var id_accountsite = $("#tabs_accounts_ul").attr('active_account_site');
		
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCreateAuctions", id_item:id_item, id_accountsite:id_accountsite }, function($data)
		{
			wait_dialog_hide();
			try
			{
				$xml = $($.parseXML($data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2($data);
					return;
				}
			}
			catch (err)
			{
				show_status2(err.message);
				return;
			}
			load_auctions();
			if( $submit )
			{
				auctions_submit();
			}
			else
			{
				show_status("Auktionen neu erstellt und in die Warteschlange gestellt. Die Auktionen werden in den nächsten 30min auf eBay hochgeladen.");
			}
		});
	}

	function load_view_auctions()
	{
		item_id = $("#editor_tabs").attr('shop_item');
		
		wait_dialog_show('Lade Accounts',0);
		
		var select_cols = 'id_account, title';
		var where = 'WHERE active>0 ORDER BY ordering';
				
		$.post("<?php print PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", db:'dbshop', table:'ebay_accounts', where:where, select:select_cols  }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}

			var article_auctions_menu = '<ul id="tabs_accounts_ul" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">';
			var t = 0;

			var first_site = 0;
			var class_ext = '';
			var aria_selected = 'false';
			var id_account = 0
			$xml.find("ebay_accounts").each(function(){
				id_account = $(this).find('id_account').text();
				
				if ( first_site == 0 )
				{
					first_site = id_account;
					class_ext = 'ui-tabs-active ui-state-active';
					aria_selected = 'true';
				}
				else
				{
					class_ext = '';
					aria_selected = 'false';	
				}
				article_auctions_menu += '		<li class="ui-state-default ui-corner-top '+class_ext+'"';
				article_auctions_menu += '" role="tab" tabindex="0" aria-controls="list-edit-tab'+t+'" aria-labelledby="ui-id-'+t+'" aria-selected="'+aria_selected+'">';
				article_auctions_menu += '<a href="#list-edit-tab'+t+'" id="ui-id-'+t+'" style="cursor:pointer;';
				article_auctions_menu += '" class="ui-tabs-anchor auctions_accounts_tab" value="'+id_account+'" role="presentation" tabindex="-1" id="ui-id-1">';
				article_auctions_menu += $(this).find('title').text()+'</a>';
				article_auctions_menu += '		</li>';
				t++;
			}); 
			article_auctions_menu += '	</ul>';
			article_auctions_menu += '	<div id="menu_account_sites"></div>';
			
			wait_dialog_show('Zeichne Account Menu',100);
			
			$("#article_editor_content").empty().append(article_auctions_menu);
			
			$(".auctions_accounts_tab").click(function(){
				var id = $( this ).attr( 'value' );
				$("#tabs_accounts_ul").attr('active_account',id);
				$("#tabs_accounts_ul").children().removeClass('ui-tabs-active ui-state-active');
				$(this).parent().addClass('ui-tabs-active ui-state-active');
				load_account_sites(id, 0);
			});
			$("#tabs_accounts_ul").attr('active_account',first_site);
			load_account_sites(1);
			wait_dialog_hide();
		});	
	}
	
	function load_account_sites(first_load)
	{
		var id_account = $("#tabs_accounts_ul").attr('active_account');
		
		wait_dialog_show('Lade Accounts',0);
		
		var select_cols = 'id_accountsite, title';
		var where = 'WHERE account_id="'+id_account+'" AND active>0 ORDER BY ordering';
				
		$.post("<?php print PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", db:'dbshop', table:'ebay_accounts_sites', where:where, select:select_cols  }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}

			var article_auctions_menu = '<ul id="tabs_account_sites_ul" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">';
			var t = 0;
			var site_id = 0;
			var site_title = '';
			var class_ext = '';
			var aria_selected = 'false';
			$xml.find("ebay_accounts_sites").each(function(){
				if ( site_id == 0 ) 
				{
					site_id = $(this).find('id_accountsite').text();
					site_title = $(this).find('title').text();
					class_ext = 'ui-tabs-active ui-state-active';
					aria_selected = 'true';
				}
				else
				{
					class_ext = '';
					aria_selected = 'false';
				}
				article_auctions_menu += '		<li class="ui-state-default ui-corner-top '+class_ext+'"';
				article_auctions_menu += '" role="tab" tabindex="0" aria-controls="list-edit-tab'+t+'" aria-labelledby="ui-id-'+t+'" aria-selected="'+aria_selected+'">';
				article_auctions_menu += '<a href="#list-edit-tab'+t+'" id="ui-id-'+t+'" style="cursor:pointer;';
				article_auctions_menu += '" class="ui-tabs-anchor auctions_account_sites_tab" value="'+$(this).find('id_accountsite').text()+'" title="'+$(this).find('title').text()+'" role="presentation" tabindex="-1" id="ui-id-1">';
				article_auctions_menu += $(this).find('title').text()+'</a>';
				article_auctions_menu += '		</li>';
				t++;
			});
			article_auctions_menu += '	</ul>';
			article_auctions_menu += '	<div id="list_auctions"></div>';
			wait_dialog_show('Zeichne Account Menu',100);
			
			$("#menu_account_sites").empty().append(article_auctions_menu);
			
			$(".auctions_account_sites_tab").click(function(){
				var id = $( this ).attr( 'value' );
				$("#tabs_accounts_ul").attr('active_account_site',id);
				$("#tabs_accounts_ul").attr('active_account_site_title',$( this ).attr( 'title' ));
				
				$("#tabs_account_sites_ul").children().removeClass('ui-tabs-active ui-state-active');
				$(this).parent().addClass('ui-tabs-active ui-state-active');
				var title = $(this).text();
				load_auctions(id_account, id, title);
			}); 
			if (first_load == 1 || t == 1)
			{
				$("#tabs_accounts_ul").attr('active_account_site',site_id);
				$("#tabs_accounts_ul").attr('active_account_site_title',site_title);
				load_auctions();
			}
			wait_dialog_hide();
		});	
	}
	
	function load_auctions()
	{
		var id_account = $("#tabs_accounts_ul").attr('active_account');
		var id_accountsite = $("#tabs_accounts_ul").attr('active_account_site');
		
		wait_dialog_show();
		var item_id = $("#editor_tabs").attr('shop_item');
		
		var select_cols = 'id_auction, Currency, ItemID, ShippingServiceCost, StartPrice, QuantitySold, Title, SubTitle, firstmod, firstmod_user, lastmod, lastmod_user, lastupdate, `Call`, upload, premium, responseXml';
		var where = 'WHERE shopitem_id="'+item_id+'" AND account_id="'+id_account+'" and accountsite_id="'+id_accountsite+'" ORDER BY ItemID';
		$.post("<?php print PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", db:'dbshop', table:'ebay_auctions', where:where, select:select_cols  }, function($data)
		{ 
			//show_status2($data); return;
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
			//var auctions_content = '<div id="list_edit_tab'+id_accountsite+'">';
			//auctions_content += '<h3>'+accountsite_title+'</h3>';
			var auctions_content = '<table class="hover">';
			auctions_content += '<tr>';
			auctions_content += '	<th>Nr.</th>';
			auctions_content += '	<th>Auktions-ID</th>';
			auctions_content += '	<th>Titel</th>';
			auctions_content += '	<th>Preis</th>';
			auctions_content += '	<th>Porto</th>';
			auctions_content += '	<th>Verkauft</th>';
			auctions_content += '	<th>eBay-Auktionsnummer</th>';
			auctions_content += '	<th>Erstellt am</th>';
			auctions_content += '	<th>Letzte Aktualisierung</th>';
			auctions_content += '	<th>Letzter Upload</th>';
			auctions_content += '	<th>Aufruf</th>';
			auctions_content += '	<th>Warteschlange</th>';
			auctions_content += '	<th>Optionen<br />';
			auctions_content += '	<img alt="Auktionen neu generieren" onclick="item_create_auctions();" src="<?php print PATH; ?>images/icons/24x24/repeat.png" style="cursor:pointer;" title="eBay-Auktionen neu generieren" />';
			auctions_content += '	<img alt="eBay-Details abrufen" onclick="get_ebay_details();" src="<?php print PATH; ?>images/icons/24x24/info.png" style="cursor:pointer;" title="eBay-Details abrufen" />';
			auctions_content += '	<img alt="Alle Auktionen an eBay senden" onclick="auctions_submit();" src="<?php print PATH; ?>images/icons/24x24/up.png" style="cursor:pointer;" title="Alle Auktionen an eBay senden" />';
			auctions_content += '</th>';
			auctions_content += '</tr>';
			
			if($xml.find('ebay_auctions').length>0)
			{
				var file_id = 0;
				var i = 0;
				$xml.find('ebay_auctions').each(function(){
					i++;
					if( $(this).find('premium').text()==1 ) $style=' style="background-color:#fef070;"'; else $style='';
					auctions_content += '<tr'+$style+'>';
					auctions_content += '	<td>'+i+'</td>';
					auctions_content += '	<td>'+$(this).find('id_auction').text()+'</td>';
					auctions_content += '	<td>';
					auctions_content += $(this).find('Title').text();
					if( $(this).find('SubTitle').text() !="" ) auctions_content += '<br /><span style="font-family: Arial, sans-serif, Verdana; font-size:11px; color:rgb(102, 102, 102);">'+$(this).find('SubTitle').text()+'</span>';
					auctions_content += '</td>';
					var $StartPrice=Math.round($(this).find('StartPrice').text()*100)/100;
					auctions_content += '	<td>'+$StartPrice+' '+$(this).find('Currency').text()+'</td>';
					var $ShippingServiceCost=Math.round($(this).find('ShippingServiceCost').text()*100)/100;
					auctions_content += '	<td>'+$ShippingServiceCost+' '+$(this).find('Currency').text()+'</td>';
					auctions_content += '	<td>'+$(this).find('QuantitySold').text()+'</td>';
					auctions_content += '	<td><a href="http://www.ebay.de/itm/'+$(this).find('ItemID').text()+'" target="_blank">'+$(this).find('ItemID').text()+'</a></td>';
					var firstmod = new Date($(this).find('firstmod').text()*1000);
					var lastmod = new Date($(this).find('lastmod').text()*1000);
					var lastupdate = new Date($(this).find('lastupdate').text()*1000);
					
					firstmod = firstmod.toLocaleString();
					firstmod = firstmod.substr(0,firstmod.lastIndexOf(':'));
					lastmod = lastmod.toLocaleString();
					lastmod = lastmod.substr(0,lastmod.lastIndexOf(':'));
					lastupdate = lastupdate.toLocaleString();
					lastupdate = lastupdate.substr(0,lastupdate.lastIndexOf(':'));

					auctions_content += '	<td>'+firstmod+'<br /><i>'+$(this).find('firstmod_user').text()+'</i></td>';
					auctions_content += '	<td>'+lastmod+'<br /><i>'+$(this).find('lastmod_user').text()+'</i></td>';
					auctions_content += '	<td>'+lastupdate+'<br /><i>'+$(this).find('lastmod_user').text()+'</i></td>';
					auctions_content += '	<td>'+$(this).find('Call').text()+'</td>';
					auctions_content += '	<td>'+$(this).find('upload').text()+'</td>';
					auctions_content += '	<td>';

					auctions_content += '		<img alt="TOP-Angebot" onclick="top_offer_toggle('+$(this).find('id_auction').text()+');" src="<?php print PATH; ?>images/icons/24x24/favorite.png" style="cursor:pointer;" title="TOP-Angebot" />';
					var icon='accept.png';
					var responseXml = $(this).find('responseXml').text();
					if( responseXml.indexOf("<SeverityCode>Error</SeverityCode>") != -1 ) icon='remove.png';
					else if( responseXml.indexOf("<SeverityCode>Warning</SeverityCode>") != -1 ) icon='warning.png';
					auctions_content += '		<img alt="Zeige letzte Serverantwort an" onclick="get_last_response('+$(this).find('id_auction').text()+');" src="<?php print PATH; ?>images/icons/24x24/'+icon+'" style="cursor:pointer;" title="Zeige letzte Serverantwort an" />';
					auctions_content += '		<img alt="Rufe Auktionsdaten ab" onclick="get_item('+$(this).find('ItemID').text()+');" src="<?php print PATH; ?>images/icons/24x24/info.png" style="cursor:pointer;" title="Rufe Auktionsdaten ab" />';
					auctions_content += '		<img alt="Auktion an eBay senden" onclick="auction_submit(\''+$(this).find('Call').text()+'\', '+$(this).find('id_auction').text()+')" src="<?php print PATH; ?>images/icons/24x24/up.png" style="cursor:pointer;" title="Auktion an eBay senden" />';
					if( $(this).find('ItemID').text()>0 )
					{
						auctions_content += '		<img alt="Auktion beenden" onclick="if (confirm(\'Wollen Sie die Auktion wirklich beenden?\')) { auction_submit(\'EndItem\', '+$(this).find('id_auction').text()+'); }" src="<?php print PATH; ?>images/icons/24x24/remove.png" style="cursor:pointer;" title="Auktion beenden" />';
					}
					auctions_content += '	</td>';
					auctions_content += '</tr>';
				});
			}
			else
			{
				auctions_content += '<tr><td colspan="13">Keine Auktionen gefunden.</td></tr>';
			}	
			auctions_content += '</table>';
			
			$("#list_auctions").empty().append(auctions_content);	
			wait_dialog_hide();
		});
	}