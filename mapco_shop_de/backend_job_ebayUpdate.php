<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	//PATH
	echo '<p>'."\n";
	echo '<a href="backend_index.php">Backend</a>'."\n";
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>'."\n";
	echo ' > Artikel'."\n";
	echo '</p>'."\n";
	
	echo '<h1>eBay-Update</h1>';
	echo '<p>Dieses Update wurde durch eine vollautomatische Version ersetzt. Bitte diese Seite nicht mehr aufrufen!</p>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
	exit;





	require_once("functions/shop_get_prices.php");
	$refresh=3600;
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script language="javascript">
	var id_account=0;
	var $accounts=new Array();
	<?php
	if ( isset($_GET["id_account"]) )
	{
		echo '
	$accounts[0]='.$_GET["id_account"].';
		';
	}
	else
	{
//		echo '$accounts[0]=1;';
//		echo '$accounts[1]=2;';
		echo '$accounts[0]=8;';
	}
	?>
	var id_imageformat=0;
	var wait_dialog_timer;
	var item_submit_cancel=false;
	var items=new Array;
	var auction_id=new Array();
	var auction_action=new Array();
	var auction_counter=0;
	var id_list=-1;
	var id_menuitem=-1;
	var $timestamp=0;
	
	function ebay_auctions(id_account, id_item)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemGetAuctions", id_account:$accounts[id_account], id_item:id_item },
			   function(data)
			   {
					$("#ebay_auctions_dialog").html(data);
					$("#ebay_auctions_dialog").dialog
					({	buttons:
						[
							{ text: "OK", click: function() { $(this).dialog("close"); } }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Auktionen zum Artikel",
						width:600
					});
				wait_dialog_hide();
			   }
		);
	}


	function item_create(i)
	{
		if ( item_submit_cancel == true )
		{
			var jetzt = new Date();
			$row  = '<tr>';
			$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
			$row += '<td>'+$accounts[id_account]+'</td>';
			$row += '<td>'+items[i]+'</td>';
			$row += '<td>Übertragung erfolgreich abgebrochen.</td>';
			$row += '</tr>';
			$("#items_submit_logtable").append($row);
			return;
		}
		if ( i>=items.length )
		{
			id_account++;
			items_submit();
			return;
		}
		
		var jetzt = new Date();
		$row  = '<tr>';
		$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
		$row += '<td>'+$accounts[id_account]+'</td>';
		$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[i]+'" target="_blank">'+items[i]+'</a></td>';
		$row += '<td>Erstelle Auktionen</td>';
		$row += '</tr>';
		$("#items_submit_logtable").append($row);

//		$("#items_submit_text").html('Account: '+$accounts[id_account]+'<br /><br /><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[i]+'" target="_blank">Shopartikel</a> '+(i+1)+" von "+items.length+"<br /><br />Erstelle Auktionen...");
		var response = $.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCreateAuctions", id_account:$accounts[id_account], id_item:items[i], bestoffer:bestoffer, ShippingServiceCost:ShippingServiceCost, id_article:id_article[id_account], id_imageformat:id_imageformat, items_submit_skiphours:items_submit_skiphours },
			function(data)
			{
				try
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}
				}
				catch (ex)
				{
					show_status2(data);
					return;
					this.debug(ex);
				}

				var j=0;
				auction_id=new Array();
				auction_action=new Array();
				$xml.find("AuctionID").each(
					function()
					{
						auction_id[j]=$(this).text();
						auction_action[j]=$(this).attr("action");
						j++;
					}
				);
				auction_counter=0;
				item_submit(i);
			}
		);
//			response.error(function() { alert("error"); })
	}

	function item_submit(item_counter)
	{
		if ( item_submit_cancel == true )
		{
			item_create(item_counter);
			return;
		}
		if (auction_counter==auction_id.length)
		{
			item_create(item_counter+1);
			return;
		}
		if (auction_action[auction_counter]=="AddItem") actiontext="Erstelle Auktion";
		else if (auction_action[auction_counter]=="ReviseItem") actiontext="Aktualisiere Auktion";
		else if (auction_action[auction_counter]=="EndItem") actiontext="Beende Auktion";
//		var items_submit_skiphours=$("#items_submit_skiphours").val();
//		var status='Account: '+$accounts[id_account]+'<br /><br /><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">Shopartikel</a> '+(item_counter+1)+" von "+items.length;
//		status+="<br /><br />Aktion "+(auction_counter+1)+" von "+auction_id.length+": "+actiontext+" "+auction_id[auction_counter];
//		$("#items_submit_text").html(status);
		
		var jetzt = new Date();
		$row  = '<tr>';
		$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
		$row += '<td>'+$accounts[id_account]+'</td>';
		$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
		$row += '<td>'+actiontext+' '+auction_id[auction_counter]+'</td>';
		$row += '</tr>';
		$("#items_submit_logtable").append($row);

		$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:auction_action[auction_counter], id_auction:auction_id[auction_counter], items_submit_skiphours:items_submit_skiphours },
			function(data)
			{
//				show_status2(data);
//				return;

				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					//end auction and repeat item submission when auction format is too old
					if ( data.indexOf("<shortMsg>Auktion nicht aktiv.</shortMsg>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Auktion nicht aktiv und gelöscht.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						item_create(item_counter);
						return;
					}
					//end auction and repeat item submission when auction was unsuccessful
					else if ( data.indexOf("<shortMsg>Auktion nicht erfolgreich.</shortMsg>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Auktion war nicht erfolgreich und wurde gelöscht.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						item_create(item_counter);
						return;
					}
					else if ( data.indexOf("<shortMsg>Ungültige Eingabedaten.</shortMsg>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler beim Hochladen eines Bildes.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					else if ( data.indexOf("<ErrorCode>302</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 302: Auktion wird beendet.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"EndItem", id_auction:auction_id[auction_counter] },
							function(data)
							{
								item_create(item_counter);
								return;
							}
						);
						return;
					}
					//repeat item submission when auction title and subtitle cannot be revised
					else if ( data.indexOf("<ErrorCode>10039</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 10039: Titel und Untertitel können nicht geändert werden. Wiederhole Aufruf.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when price suggestions are open
					else if ( data.indexOf("<ErrorCode>10041</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 10041: Der Wert "ShippingService" kann nicht bearbeitet werden, wenn für den Artikel bereits Gebote abgegeben wurden, aktive Preisangebote vorhanden sind oder das Angebot in 12 Stunden endet.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when price suggestions are open
					else if ( data.indexOf("<ErrorCode>10048</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 10048: Der Wert "ShippingService" kann nicht bearbeitet werden, wenn für den Artikel bereits Gebote abgegeben wurden, aktive Preisangebote vorhanden sind oder das Angebot in 12 Stunden endet.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when price suggestions are open
					else if ( data.indexOf("<ErrorCode>21916923</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21916923: Offene Preisvorschläge gefunden. Auktion kann nicht geändert werden und wird übersprungen.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when promo sales are active
					else if ( data.indexOf("<ErrorCode>21917089</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21917089: Aktionsrabatte aktiv. Auktion kann nicht geändert werden und wird übersprungen.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when parts of the auction cannot be updated
					else if ( data.indexOf("<ErrorCode>21919028</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21919028: Teile der Auktion können nicht aktualisiert werden. Auktion kann nicht geändert werden und wird übersprungen.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when duplicate
					else if ( data.indexOf("<ErrorCode>21919067</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21919067: Auktion als Duplikat erkannt. Wird übersprungen.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when parts of the auction cannot be updated
					else if ( data.indexOf("<ErrorCode>21916730</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21916730: Teile der Auktion können nicht aktualisiert werden. Auktion kann nicht geändert werden und wird übersprungen.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//end auction when "old" specifics cannot be changed
					else if ( data.indexOf("<ErrorCode>21916885</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21916885: Alte Eigenschaften können nicht geändert werden. Auktion wird beendet.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"EndItem", id_auction:auction_id[auction_counter] },
							function(data)
							{
								item_create(item_counter);
								return;
							}
						);
						return;
					}
					//skip auction when auction cannot be updated
					else if ( data.indexOf("<ErrorCode>240</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 240: Auktion kann nicht aktualisiert werden und wird übersprungen.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//repeat auction when category cannot be changed
					else if ( data.indexOf("<ErrorCode>21919128</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21919128: Kategorie kann nicht geändert werden. Artikel wird übersprungen.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when call limit is reached
					else if ( data.indexOf("<ErrorCode>21919144</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21919144: Call-Limit erreicht. Auktion wird übersprungen.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when call limit is reached
					else if ( data.indexOf("<ErrorCode>21919165</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21919165: Call-Limit erreicht. Auktion wird übersprungen.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when price suggestion is open
					else if ( data.indexOf("<ErrorCode>21916320</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21919165: Die Angaben zur Rücknahme können nicht geändert werden, wenn für eine Auktion ein Gebot vorliegt, das Angebot innerhalb der nächsten 12 Stunden endet, oder wenn für ein Festpreisangebot ein offener Preisvorschlag vorliegt.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					//skip auction when price suggestion is open
					else if ( data.indexOf("<ErrorCode>21916924</ErrorCode>") != -1 )
					{
						var jetzt = new Date();
						$row  = '<tr>';
						$row += '<td>'+jetzt.getDate()+'.'+(jetzt.getMonth()+1)+'.'+jetzt.getFullYear()+' '+jetzt.getHours()+':'+jetzt.getMinutes()+':'+jetzt.getSeconds()+'</td>';
						$row += '<td>'+$accounts[id_account]+'</td>';
						$row += '<td><a href="http://www.mapco.de/backend_shop_item_editor.php?id_item='+items[item_counter]+'" target="_blank">'+items[item_counter]+'</a></td>';
						$row += '<td>Fehler 21916924: Die Gebühren für Verpackung und Versand können nicht geändert werden, wenn für eine Auktion ein Gebot vorliegt, das Angebot innerhalb der nächsten 12 Stunden endet, oder wenn für ein Festpreisangebot ein offener Preisvorschlag vorliegt.</td>';
						$row += '</tr>';
						$("#items_submit_logtable").append($row);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					else
					{
						show_status2(data);
						auction_counter++;
						item_submit(item_counter);
						return;
					}
					return;
				}
				auction_counter++;
				item_submit(item_counter);
			}
		);
	}


	
	function items_submit()
	{
		if ( id_account>=$accounts.length )
		{
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCheckOut", id_item:items[0] },
				function(data)
				{
					window.location.href=window.location.href;
					return;
				}
			);
			return;
		}

		if( $('#items_submit_options_dialog').hasClass("ui-dialog-content") ) $("#items_submit_options_dialog").dialog("close");
		$("#items_submit_dialog").dialog
		({	buttons:
			[
				{	text: "Abbrechen",
					click: function()
					{
						item_submit_cancel=true;
//						show_status("Übertragung wird abgebrochen...");
						$('#items_submit_dialog').dialog('option', 'buttons', {});
					}
				}
			],
			closeText:"Fenster schließen",
			height:400,
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Shopartikel nach eBay übertragen",
			width:420
		});
		
		item_create(0);
	}


	function items_update()
	{
		$("#export_dialog").dialog("close");
		if ( typeof document.itemform === "undefined" )
		{
			alert("Bitte wählen Sie zunächst eine Liste oder Artikelgruppe aus.");
			return;
		}

		var theForm = document.itemform;
		var j=0;
		items = new Array();
		for (i=0; i<theForm.elements.length; i++)
		{
			if (theForm.elements[i].checked)
			{
				if (theForm.elements[i].name=='item_id[]')
				{
					items[j]=theForm.elements[i].value;
					j++;
				}
			}
		}
		
		if ( items.length==0 )
		{
			alert("Bitte wählen Sie die Referenzen aus, die Sie exportieren möchten.");
			return;
		}

		$("#items_update_dialog").dialog
		({	closeText:"Fenster schließen",
			height: 200,
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Artikeldaten aktualisieren",
			width:400
		});
		
		item_update(0);
	}


	function item_update(i)
	{
		if (i<items.length)
		{
			$("#items_update_dialog").html("Aktualisiere Artikel "+(i+1)+" von "+items.length);
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemUpdate", id_item:items[i] },
				function(data)
				{
					try
					{
						$xml = $($.parseXML(data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2(data);
							return;
						}
					}
					catch (ex)
					{
						show_status2(data);
						return;
						this.debug(ex);
					}

					item_update(i+1);
				}
			)
			.error(function(x, status) { item_update(i); });
			;
		}
		else
		{
			$("#items_update_dialog").html('Alle Artikel erfolgreich aktualisiert.');
		}
	}
</script>

<?php
	//PATH
	echo '<p>'."\n";
	echo '<a href="backend_index.php">Backend</a>'."\n";
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>'."\n";
	echo ' > Artikel'."\n";
	echo '</p>'."\n";
	

	//ITEMS UPDATE DIALOG
	echo '<div id="items_update_dialog" style="display:none;">';
	echo '</div>';
	
	
	//ITEMS SUBMIT OPTIONS DIALOG
	echo '<div id="items_submit_options_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Account</td>';
	echo '		<td>';
	echo '			<select id="items_submit_id_account">';
	$results=q("SELECT * FROM ebay_accounts ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while ( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_account"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Design</td>';
	echo '		<td>';
	echo '			<select id="items_submit_id_article">';
	$results=q("SELECT * FROM cms_articles AS a, cms_articles_labels AS b WHERE b.label_id=8 AND a.id_article=b.article_id AND a.published>0;", $dbweb, __FILE__, __LINE__);
	while ( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_article"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Preisvorschlag</td>';
	echo '		<td>';
	echo '				<input type="checkbox" id="items_submit_bestoffer" /> Preisvorschlag aktivieren';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Versandkosten</td>';
	echo '		<td>';
	echo '				<input style="width:50px;" type="input" id="items_submit_ShippingServiceCost" value="5.90" /> €';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Kommentar</td>';
	echo '		<td>';
	echo '			<textarea id="items_submit_comment" style="width:300px; height:50px;" ></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Älter als</td>';
	echo '		<td>';
	echo '				<input style="width:50px;" type="input" id="items_submit_skiphours" value="0" /> Stunden';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	//ITEMS SUBMIT DIALOG
	echo '<div style="display:none;" id="items_submit_dialog">';
	echo '	<div id="items_submit_text"></div>';
	echo '	<table>';
	echo '		<tr>';
	echo '			<th>Zeit</th>';
	echo '			<th>Account</th>';
	echo '			<th>Artikel</th>';
	echo '			<th>Aktion</th>';
	echo '		</tr>';
	echo '		<tbody id="items_submit_logtable">';
	echo '		</tbody>';
	echo '	</table>';
	echo '</div>';

	//status dialog
	echo '<div style="display:none;" id="status">';
	echo '</div>';


	//WAIT DIALOG
	echo '<div id="wait_dialog" style="display:none;">';
	echo '	<img src="'.PATH.'images/icons/loaderb64.gif" style="margin:0px 0px 0px 30px" />';
	echo '</div>';

	echo '<script type="text/javascript">';

	//config
	echo '
	var bestoffer = 1;
	var ShippingServiceCost=5.9;
	var id_article=new Array();
	id_article[1]=199;
	id_article[2]=202;
	var items_submit_skiphours=24;
	id_account=0;
	';

	$i=0;
/*
	//create auctions for new items first
	$ebay=array();
	$results=q("SELECT shopitem_id FROM ebay_auctions GROUP BY shopitem_id;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$ebay[$row["shopitem_id"]]=$row["shopitem_id"];
	}
	$new=array();
	$results=q("SELECT * FROM shop_items WHERE active>0 AND lastupdate<".mktime(19, 00, 00, 05, 27, 2013)." AND lastupdatestart<".(time()-3600).";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( !isset($ebay[$row["id_item"]]) and ( $row["GART"]==82 and (strpos($row["MPN"], "/2") !== false) ) )
		{
			$results2=q("SELECT * FROM lager WHERE ArtNr='".$row["MPN"]."' AND (ISTBESTAND>2 OR MOCOMBESTAND>2);", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results2)>0 )
			{
				//price > 10 EUR gross
				$price=get_prices($row["id_item"], 1, 27991);
				$StartPrice=round($price["gross"], 2); //mandatory
				if( $StartPrice>10 )
				{
					$new[]=$row["id_item"];
				}
			}
		}
	}
	$todo=sizeof($new);
	if($todo>0) echo 'items[0]='.$new[0].";\n";
*/
/*
	//update existing brake discs
	$results=q("SELECT * FROM shop_items WHERE GART=82 AND lastupdate<".mktime(10, 49, 00, 05, 02, 2013)." AND lastupdatestart<".(time()-3600)." ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
	$todo=mysqli_num_rows($results);
	while( $row=mysqli_fetch_array($results) )
	{
		echo 'items['.$i.']='.$row["id_item"].";\n";
		$i++;
		break;
	}
*/

/*
	//update list of items
	$items=array();
	$results=q("SELECT * FROM shop_lists_items WHERE list_id=256;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$items[$row["item_id"]]=$row["item_id"];
	}
	$results=q("SELECT * FROM shop_items WHERE lastupdate>".mktime(12, 58, 00, 07, 08, 2013)." AND lastupdatestart<".(time()-3600)." ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
	$todo=mysqli_num_rows($results);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( isset($items[$row["id_item"]]) ) unset($items[$row["id_item"]]);
	}
	sort($items);
	$todo=sizeof($items);
	if($todo>0) echo 'items[0]='.$items[0].";\n";
*/

/*
	//update cheap items
	$artnr=array();
	$results=q("SELECT * FROM prpos WHERE LST_NR=5 AND POS_0_WERT<10;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$artnr[$row["ARTNR"]]=$row["ARTNR"];
	}
	$todo=0;
	$results=q("SELECT * FROM shop_items WHERE lastupdate<".mktime(19, 00, 00, 06, 01, 2013)." AND lastupdatestart<".(time()-3600)." ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( isset($artnr[$row["MPN"]]) )
		{
			if($todo==0) $id_item=$row["id_item"];
			$todo++;
		}
	}
	if($todo>0) echo 'items[0]='.$id_item.";\n";
*/
	//update list
	$todo=0;
	if( isset($_GET["id_list"]) )
	{
		$listitems=array();
		$results=q("SELECT * FROM shop_lists WHERE id_list=".$_GET["id_list"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$title='Listenupdate: '.$row["title"];
		$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_GET["id_list"].";", $dbshop, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			$listitems[$row["item_id"]]=$row["item_id"];
		}
		$results=q("SELECT * FROM shop_items WHERE lastupdate<".$_GET["lastupdate"]." AND lastupdatestart<".(time()-3600)." ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if( isset($listitems[$row["id_item"]]) )
			{
				if( $todo==0 ) echo 'items['.$todo.']='.$row["id_item"].";\n";
				$todo++;
			}
		}
	}
	//update existing items afterwards
	if( $todo==0 )
	{
		$title='eBay-Update aller Artikel';
//		$results=q("SELECT * FROM shop_items WHERE lastupdate<".mktime(19, 00, 00, 06, 17, 2013)." AND lastupdatestart<".(time()-3600)." ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
		$results=q("SELECT * FROM shop_items WHERE lastupdate<1377711116 AND lastupdatestart<".(time()-3600)." ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
		$todo=mysqli_num_rows($results);
		while( $row=mysqli_fetch_array($results) )
		{
			echo 'items['.$i.']='.$row["id_item"].";\n";
			$i++;
			break;
		}
	}

/*
	$i=0;
	//Lenkgetriebe routine
	$results=q("SELECT * FROM shop_items WHERE GART=286 AND lastupdate<".mktime(15, 00, 00, 05, 16, 2013)." AND lastupdatestart<".(time()-3600)." ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
	$todo=mysqli_num_rows($results);
	while( $row=mysqli_fetch_array($results) )
	{
		echo 'items['.$i.']='.$row["id_item"].";\n";
		$i++;
		break;
	}
*/
/*
	//update all AP-auctions with MAPCO-Design
	$results=q("SELECT shopitem_id FROM ebay_auctions WHERE account_id=2 AND Description LIKE '%Selbstabholung%' GROUP BY shopitem_id;", $dbshop, __FILE__, __LINE__);
	$todo=mysqli_num_rows($results);
	while( $row=mysqli_fetch_array($results) )
	{
		echo 'items['.$i.']='.$row["shopitem_id"].";\n";
		$i++;
		break;
	}
*/
/*
	//update all brake discs with numbers other than 15
	$items=array();
	$results=q("SELECT * FROM shop_items WHERE GART=82 AND MPN NOT LIKE '15%' AND MPN NOT LIKE '%/2';", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$items[$row["id_item"]]=0;
	}
	$results=q("SELECT shopitem_id FROM ebay_auctions;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( isset($items[$row["shopitem_id"]]) ) $items[$row["shopitem_id"]]=1;
	}
	$keys=array_keys($items);
	foreach($keys as $key)
	{
		if ( $items[$key]==0 ) unset($items[$key]);
	}
	$todo=sizeof($items);
	$keys=array_keys($items);
	foreach($keys as $key)
	{
		$results=q("SELECT id_item FROM shop_items WHERE id_item=".$key." AND lastupdatestart<".(time()-3600).";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			echo 'items['.$i.']='.$key.";\n";
			$i++;
			break;
		}
	}
*/
/*
	//update all items with more than 15 auctions
	$items=array();
	$results=q("SELECT shopitem_id FROM ebay_auctions WHERE account_id=2;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( !isset($items[$row["shopitem_id"]]) ) $items[$row["shopitem_id"]]=1;
		else $items[$row["shopitem_id"]]++;
	}
	$keys=array_keys($items);
	foreach($keys as $key)
	{
		if ( $items[$key]<=15 ) unset($items[$key]);
	}
	$todo=sizeof($items);
	$keys=array_keys($items);
	foreach($keys as $key)
	{
		$results=q("SELECT id_item FROM shop_items WHERE id_item=".$key." AND lastupdatestart<".(time()-3600).";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			if ( $items[$key]>15 )
			{
				echo 'items['.$i.']='.$key.";\n";
				$i++;
				break;
			}
		}
	}
*/

    echo '</script>';
	echo '<h1>'.$title.'</h1>';
	echo '<span style="color:#000000; font-size:40px;">ToDo: '.$todo.'</span>';
	if($todo==0)
	{
		mail("jhabermann@mapco.de", "Auktionen aktuell", "Die Auktionen wurden alle erfolgreich aktualisiert.");
		exit;
	}

	//start
	echo '<script type="text/javascript"> items_submit(); </script>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>