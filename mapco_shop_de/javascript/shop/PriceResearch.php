<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	function price_research($id_item, title)
	{
		delete $shop_items;
		delete $shop_price_suggestions;
		//build dialog
		if( $("#price_research_dialog").length==0 ) $("body").append('<div id="price_research_dialog" style="display:none;"></div>');
		var $html = '';
		$html += '<div id="price_research_tabs">';
		$html += '	<ul>';
		$html += '		<li><a id="price_research_general_button" href="#price_research_general_tab">Recherche</a></li>';
		$html += '		<li><a id="price_research_history_button" href="#price_research_history_tab">Historie</a></li>';
		$html += '		<li><a id="price_research_stats_button" href="#price_research_stats_tab">Statistik</a></li>';
		if( <?php echo $_SESSION["userrole_id"]; ?>==1 || <?php echo $_SESSION["userrole_id"]; ?>==3 ) $html += '		<li><a id="price_research_margin_button" href="#price_research_margin_tab">Margenrechner</a></li>';
		$html += '	</ul>';
		$html += '	<div id="price_research_general_tab"></div>';
		$html += '	<div id="price_research_history_tab"></div>';
		$html += '	<div id="price_research_stats_tab" style="width:550px; height:400px;"></div>';
		$html += '	<div id="price_research_margin_tab"></div>';
		$html += '</div>';
		$("#price_research_dialog").html($html);

		//activate tabs
		$("#price_research_tabs").tabs();

		//tab events
		$("#price_research_general_button").bind("click", function() { price_research_general_tab($id_item); });
		$("#price_research_history_button").bind("click", function() { price_research_history_tab($id_item); });
		$("#price_research_stats_button").bind("click", function() { price_research_stats_tab($id_item); });
		$("#price_research_margin_button").bind("click", function() { price_research_margin_tab($id_item); });

		//show
		price_research_general_tab($id_item);
		$("#price_research_dialog").dialog
		({	closeText:"Fenster schließen",
			minHeight:500,
			modal:true,
			resizable:false,
			title:"Preisrecherche",
			width:800
		});
	}


	function price_research_pricelist_select()
	{
		var $id_item=$("#price_research_id_item").val();
		var $pricelist=$("#price_research_pricelist").val();
		var $showall=$("#price_research_showall").prop('checked');
		$("#price_research_general_tab").html("");
		price_research_general_tab($id_item, $pricelist, $showall)
	}


	function price_research_general_tab($id_item, $pricelist, $showall)
	{
		if( $("#price_research_general_tab").html()!="" ) return;
		var $html='';
		$html += '<select id="price_research_pricelist" onchange="price_research_pricelist_select()" style="width:300px;">';
		$html += '	<option value="16815">Onlinepreis DE eBay</option>';
		$html += '	<option value="18209">Onlinepreis DE Amazon</option>';
		$html += '	<option value="20412">Onlinepreis ES</option>';
		$html += '	<option value="20413">Onlinepreis IT</option>';
		$html += '	<option value="20414">Onlinepreis PL</option>';
		$html += '	<option value="20415">Onlinepreis GB</option>';
		$html += '	<option value="20416">Onlinepreis FR</option>';
		$html += '	<option value="14110">MOCOM</option>';
		$html += '</select>';
		$html += '&nbsp; &nbsp;<input type="checkbox" id="price_research_showall" onclick="price_research_pricelist_select()" /> abgelaufene Preisrecherchen anzeigen';
		$html += '<input type="hidden" id="price_research_id_item" value="'+$id_item+'" />';
		$("#price_research_general_tab").html($html);
		if( typeof $pricelist === "undefined" )
		{
			var $pricelist=$("#price_research_pricelist").val();
		}
		else
		{
			$("#price_research_pricelist").val($pricelist);
		}
		if( typeof $showall === "undefined" )
		{
			var $showall=$('#price_research_showall').prop('checked');
		}
		else
		{
			$('#price_research_showall').prop('checked', $showall);
		}
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceResearchGeneralView", id_item:$id_item, pricelist:$pricelist, showall:$showall },
			function(data)
			{
				$("#price_research_general_tab").append(data);
				wait_dialog_hide();
			}
		);
	}


	function price_research_history_tab($id_item)
	{
//		if( $("#price_research_history_tab").html()!="" ) return;
		if(table_data_select("shop_price_suggestions", "*", "WHERE item_id="+$id_item+" ORDER BY firstmod DESC", "dbshop", "$shop_price_suggestions", "price_research_history_tab")) return;

		$status=new Array();
		$status[0]="Bestätigung steht aus";
		$status[1]="automatisch bestätigt";
		$status[2]="durch DS bestätigt";
		$status[3]="durch DS abgelehnt";
		$status[4]="durch DS geändert";
		
		var $html  = '';
		$html += '<table>';
		$html += '	<tr><th colspan="6">Preisvorschlags-Historie</th></tr>';
		$html += '	<tr>';
		$html += '		<th>Datum</th>';
		$html += '		<th style="width:60px;">Preis</th>';
		$html += '		<th style="width:60px;">Preisliste</th>';
		$html += '		<th style="width:60px;">Vorschlag</th>';
		$html += '		<th style="width:119px;">Status</th>';
		$html += '		<th style="width:97px;">Letzte Änderung</th>';
		$html += '		<th>Optionen</th>';
		$html += '	</tr>';
		for($i=0; $i<$shop_price_suggestions.length; $i++)
		{
			$html += '	<tr>';
			$html += '<td>';
			$html += $shop_price_suggestions[$i]["firstmod_user"];
			var $d=new Date($shop_price_suggestions[$i]["firstmod"]*1000);
			var $lastmod=$d.getDate()+"."+($d.getMonth()+1)+"."+$d.getFullYear()+" "+$d.getHours()+":"+$d.getMinutes();
			$html += '	<br />'+$lastmod;
			$html += '</td>';
			$html += '		<td>'+$shop_price_suggestions[$i]["price"]+' €</td>';
			$html += '		<td>'+$shop_price_suggestions[$i]["pricelist"]+'</td>';
			var $suggestion='';
			if($shop_price_suggestions[$i]["suggestion"]>0) $suggestion=$shop_price_suggestions[$i]["suggestion"]+" €";
			$html += '		<td>'+$suggestion+'</td>';
			$html += '		<td>'+$status[$shop_price_suggestions[$i]["status"]]+'</td>';
	//			$results4=q("SELECT * FROM cms_users WHERE id_user=".$row3["firstmod_user"].";", $dbweb, __FILE__, __LINE__);
	//			$row4=mysqli_fetch_array($results4);
			$html += '<td>';
			$html += $shop_price_suggestions[$i]["lastmod_user"];
			var $d=new Date($shop_price_suggestions[$i]["lastmod"]*1000);
			var $lastmod=$d.getDate()+"."+($d.getMonth()+1)+"."+$d.getFullYear()+" "+$d.getHours()+":"+$d.getMinutes();
			$html += '	<br />'+$lastmod;
			$html += '</td>';
			$html += '		<td></td>';
			$html += '		</tr>';
		}
		$html += '</table>';
		$("#price_research_history_tab").html($html);
	}


	function price_research_stats_tab($id_item)
	{
		if( $("#price_research_stats_tab").html()!="" ) return;
		//generate stats
		var d1 = [];
		for (var i = 0; i < 14; i += 0.5) {
			d1.push([i, Math.sin(i)]);
		}
		var $date1 = (new Date()).getTime()
		var $date2 = $date1 + 24*3600*1000;
		var $date3 = $date2 + 24*3600*1000;
		var $date4 = $date3 + 24*3600*1000;
		var d2 = [[$date1, 3], [$date2, 8], [$date3, 5], [$date4, 13]];
		// A null signifies separate line segments
		var d3 = [[0, 12], [7, 12], null, [7, 2.5], [12, 2.5]];
		
		$.plot("#price_research_stats_tab", [ d2 ], { xaxis: { mode: "time", timeformat: "%y/%m/%d" } });
	}


	function price_research_margin_tab($id_item, $pricelist, $showall)
	{
		wait_dialog_show("Lese Artikeldaten", 50);
		if(table_data_select("shop_items", "*", "WHERE id_item="+$id_item, "dbshop", "$shop_items", "price_research_margin_tab")) return;
		wait_dialog_hide();

		var $html='';
		$html += '<table>';
		$html += '	<tr>';
		$html += '		<td>COS</td>';
		$html += '		<td id="margin_cos">'+$shop_items[0]["COS"]+'</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Nettopreis</td>';
		$html += '		<td><input id="margin_net_price" type="text" /></td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Bruttopreis (19%)</td>';
		$html += '		<td><input id="margin_gross_price" type="text" /></td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Marge</td>';
		$html += '		<td><span id="margin_results"></span></td>';
		$html += '	</tr>';
		$html += '</table>';
		$("#price_research_margin_tab").html($html);

		$("#margin_net_price").bind("keyup", function() { margin_calculate("margin_net_price"); });
		$("#margin_gross_price").bind("keyup", function() { margin_calculate("margin_gross_price"); });
		$("#margin_gross_price").val($shop_items[0]["BRUTTO"]);
		margin_calculate("margin_gross_price");
	}
	
	function margin_calculate($id)
	{
		var $price=$("#"+$id).val();
		if ( $price.indexOf(",")!=-1 ) $("#"+$id).val($price.replace(",", "."));
		if( $id=="margin_net_price" )
		{
			var $gross=Math.round($price*119)/100;
			$("#margin_gross_price").val($gross);
		}
		else
		{
			var $net=Math.round($price/0.0119)/100;
			$("#margin_net_price").val($net);
		}

		var $cos=$shop_items[0]["COS"];
		var $margin=100-($shop_items[0]["COS"]/$net*100);
		$("#margin_results").html( (Math.round($margin*100)/100) + "%" );
	}


	function price_research_add($id_item)
	{
		if( $("#price_research_add_dialog").length==0 ) $("body").append('<div id="price_research_add_dialog" style="display:none;"></div>');
		var $html = '';
		$html += '	<table>';
		$html += '		<tr>';
		$html += '			<td>Link</td>';
		$html += '			<td>';
		$html += '				<input id="price_research_add_link" type="text" style="width:500px;" value="" />';
		$html += '				<img src="<?php echo PATH; ?>images/icons/24x24/down.png" alt="Auktionsdaten bei eBay abrufen" title="Auktionsdaten bei eBay abrufen" style="cursor:pointer; float:right;" onclick="price_research_link();" />';
		$html += '			</td>';
		$html += '		</tr>';
		$html += '		<tr>';
		$html += '			<td>Preisliste</td>';
		$html += '			<td>';
		$html += '				<select id="price_research_add_pricelist" style="width:300px;">';
		$html += '					<option value="16815">Onlinepreis DE eBay</option>';
		$html += '					<option value="18209">Onlinepreis DE Amazon</option>';
		$html += '					<option value="20412">Onlinepreis ES</option>';
		$html += '					<option value="20413">Onlinepreis IT</option>';
		$html += '					<option value="20414">Onlinepreis PL</option>';
		$html += '					<option value="20415">Onlinepreis GB</option>';
		$html += '					<option value="20416">Onlinepreis FR</option>';
		$html += '					<option value="14110">MOCOM</option>';
		$html += '				</select>';
		$html += '			</td>';
		$html += '		</tr>';
		$html += '		<tr>';
		$html += '			<td>Anmerkung</td>';
		$html += '			<td><textarea id="price_research_add_comment" style="width:550px; height:50px;"></textarea></td>';
		$html += '		</tr>';
		$html += '		<tr>';
		$html += '			<td>Preis</td>';
		$html += '			<td><input id="price_research_add_price" style="width:40px;" type="text" value="" /> Euro</td>';
		$html += '		</tr>';
		$html += '		<tr>';
		$html += '			<td>Versand</td>';
		$html += '			<td><input id="price_research_add_shipping" style="width:40px;" type="text" value="" /> Euro</td>';
		$html += '		</tr>';
		$html += '		<tr>';
		$html += '			<td>Händler</td>';
		$html += '			<td><input id="price_research_add_seller" style="width:300px;" type="text" value="" /></td>';
		$html += '		</tr>';
		$html += '		<tr>';
		$html += '			<td>Identnummer</td>';
		$html += '			<td><input id="price_research_add_EbayID" type="text" value="" /></td>';
		$html += '		</tr>';
		$html += '	</table>';
		$html += '	<input type="hidden" id="price_research_add_item_id" value="'+$id_item+'">';
		$("#price_research_add_dialog").html($html);
		$("#price_research_add_pricelist").val($("#price_research_pricelist").val());
		$("#price_research_add_dialog").dialog
		({	closeText:"Fenster schließen",
			buttons:
			[
				{ text: "Hinzufügen", click: function() { price_research_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			modal:true,
			resizable:false,
			title:"Preisrecherche hinzufügen",
			width:700
		});
	}


	function price_research_add_save($xml)
	{
		if( typeof $xml === "undefined" )
		{
			$("#price_research_add_price").val($("#price_research_add_price").val().trim().replace(",", "."));
			$("#price_research_add_shipping").val($("#price_research_add_shipping").val().trim().replace(",", "."));
			var $postdata=get_values("input, textarea, select", "price_research_add_");
			$postdata["API"]="shop";
			$postdata["APIRequest"]="PriceResearchAdd";
			soa2($postdata, "price_research_add_save");
			return;
		}
		
		$("#price_research_add_dialog").dialog("close");
		var $id_item=$("#price_research_add_item_id").val();
		var $pricelist=$("#price_research_pricelist").val();
		$("#price_research_general_tab").html("");
		price_research_general_tab($id_item, $pricelist);
	}


	function price_suggestion_add($id_item)
	{
		if( $("#price_suggestion_add_dialog").length==0 ) $("body").append('<div id="price_suggestion_add_dialog" style="display:none;"></div>');
		var $html = '';
		$html += '	<table>';
		$html += '		<tr>';
		$html += '			<td>Preisliste</td>';
		$html += '			<td colspan=5>';
		$html += '				<select id="price_suggestion_add_pricelist" style="width:300px;">';
		$html += '					<option value="16815">Onlinepreis DE eBay</option>';
		$html += '					<option value="18209">Onlinepreis DE Amazon</option>';
		$html += '					<option value="20412">Onlinepreis ES</option>';
		$html += '					<option value="20413">Onlinepreis IT</option>';
		$html += '					<option value="20414">Onlinepreis PL</option>';
		$html += '					<option value="20415">Onlinepreis GB</option>';
		$html += '					<option value="20416">Onlinepreis FR</option>';
		$html += '					<option value="14110">MOCOM</option>';
		$html += '				</select>';
		$html += '			</td>';
		$html += '		</tr>';
		$html += '		<tr>';
		$html += '			<td colspan="4">Neuen Preis ohne Versandkosten vorschlagen</td>';
		$html += '			<td>';
		$html += '				<input value="" type="text" id="price_suggestion_add_price" />';
		$html += '			</td>';
		$html += '		</tr>';
		$html += '	</table>';
		$html += '	<input type="hidden" id="price_suggestion_add_item_id" value="'+$id_item+'">';
		$("#price_suggestion_add_dialog").html($html);
		$("#price_suggestion_add_pricelist").val($("#price_research_pricelist").val());
		$("#price_suggestion_add_dialog").dialog
		({	closeText:"Fenster schließen",
			buttons:
			[
				{ text: "Hinzufügen", click: function() { price_suggestion_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			modal:true,
			resizable:false,
			title:"Preisvorschlag hinzufügen",
			width:700
		});

	}


	function price_suggestion_add_save($xml)
	{
		if( typeof $xml === "undefined" )
		{
			$("#price_suggestion_add_price").val($("#price_suggestion_add_price").val().trim().replace(",", "."));
			var	$postdata=get_values("input, select", "price_suggestion_add_");
			$postdata["API"]="shop";
			$postdata["APIRequest"]="PriceSuggestionAdd";
			soa2($postdata, "price_suggestion_add_save");
			return;
		}
		
		delete $shop_price_suggestions;
		$("#price_suggestion_add_dialog").dialog("close");
		var $id_item=$("#price_suggestion_add_item_id").val();
		var $pricelist=$("#price_research_pricelist").val();
		$("#price_research_general_tab").html("");
		$("#price_research_history_tab").html("");
		price_research_general_tab($id_item, $pricelist);
/*
		if( price > (2*$yellow) )
		{
			var $confirm=confirm("Der Preis übersteigt den gelben Preis um mehr als das doppelte. Wollen Sie diesen Preis wirklich eintragen?");
			if( !$confirm ) return;
		}
*/
	}


	function price_research_link()
	{
		//reset fields
		$("#price_research_add_comment").val("");
		$("#price_research_add_price").val("");
		$("#price_research_add_shipping").val("");
		$("#price_research_add_seller").val("");
		$("#price_research_add_EbayID").val("");
		
		var $link=$("#price_research_add_link").val();
		if( $link.indexOf("ebay")>0 )
		{
			$("#price_research_add_EbayID").val( $link.match("[0-9]{12,}") );
			price_research_ebay();
			return;
		}
		else if( $link.indexOf("amazon")>0 )
		{
//			http://www.amazon.de/gp/product/B00A7RDPCQ/ref=s9_psimh_gw_p147_d0_i2?pf_rd_m=A3JWKAKR8XB7XF&pf_rd_s=center-2&pf_rd_r=18JZJN4YVFAH8JGHW2N8&pf_rd_t=101&pf_rd_p=455353807&pf_rd_i=301128
			$("#price_research_add_EbayID").val( $link.match("[A-Z0-9]{10,}") );
//			alert("Amazon-Links werden noch nicht unterstützt");
			return;
		}
		else
		{
			alert("Diese Links werden noch nicht unterstützt. Wenn wichtig, bitte Bescheid geben!");
			return;
		}
	}

	function price_research_ebay()
	{
		var ItemID=$("#price_research_add_EbayID").val();
		if ( !(ItemID>0) ) alert("Die Auktionsnummer ist ungültig.");
		else
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetItem", id_account:1, ItemID:ItemID },
				function(data)
				{
					try
					{
						$xml = $($.parseXML(data));
					}
					catch (err)
					{
						show_status(err.message);
						return;
					}
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						wait_dialog_hide();
						return;
					}

					var price=$xml.find("ConvertedStartPrice").text();
					var i=0;
					$xml.find("ShippingServiceOptions").each(
						function()
						{
							$ShippingServiceCost=parseFloat($(this).find("ShippingServiceCost").text());
							$ShippingService=$(this).find("ShippingService").text();
							if ( $ShippingService!="DE_Pickup" ) //skip pickup
							{
								if ( i==0 ) shipping=$ShippingServiceCost;
								else if ( shipping>$ShippingServiceCost )
								{
									shipping=$ShippingServiceCost;
								}
								i++;
							}
						}
					);
					var seller=$xml.find("UserID").text();
					$("#price_research_add_price").val(price);
					$("#price_research_add_shipping").val(shipping);
					$("#price_research_add_seller").val(seller);
					wait_dialog_hide();
				}
			);
		}
	}


	function price_research_remove($id)
	{
		if( $("#price_research_remove_dialog").length==0 ) $("body").append('<div id="price_research_remove_dialog" style="display:none;"></div>');
		var $html = '';
		$html += '	Sind Sie sicher, dass Sie die Preisrecherche löschen möchten?';
		$html += '	<input type="hidden" id="price_research_remove_id" value="'+$id+'" />';
		$("#price_research_remove_dialog").html($html);
		$("#price_research_remove_dialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { price_research_remove_accept(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Preisrecherche entfernen",
			width:400
		});
	}


	function price_research_remove_accept($xml)
	{
		if( typeof $xml === "undefined" )
		{
			var $postdata=get_values("input", "price_research_remove_");
			$postdata["API"]="shop";
			$postdata["APIRequest"]="PriceResearchRemove";
			soa2($postdata, "price_research_remove_accept");
			return;
		}
		
		$("#price_research_remove_dialog").dialog("close");
		var $id_item=$("#price_research_id_item").val();
		var $pricelist=$("#price_research_pricelist").val();
		$("#price_research_general_tab").html("");
		price_research_general_tab($id_item, $pricelist);
	}

/*
	{
		var id_item=$("#price_research_add_id_item").val();
		$.post("modules/backend_shop_items_actions.php", { action:"price_research_remove", id:id },
			function(data)
			{
				if (data!="") show_status(data);
				else
				{
					price_research_view(id_item);
					$("#price_research_remove_dialog").dialog("close");
				}
			}
		);
	}
*/


	function price_research_stats()
	{
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PriceResearchStats" },
			function(data)
			{
				$("#price_research_stats_dialog").html(data);
				$("#price_research_stats_dialog").dialog
				({	closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Statistiken Preisrecherche",
					width:400
				});
			}
		);
	}


	function price_research_view(id_item)
	{
		$.post("modules/backend_shop_items_actions.php", { action:"price_research_view", id_item:id_item },
			function(data)
			{
				$("#price_research_dialog").html(data);
			}
		);
	}




	function price_suggestion_idims($id_pricesuggestion)
	{
		var $id_item=$("#price_research_add_id_item").val();

		wait_dialog_show("Sende Preis an IDIMS...");
		$.post("<?php echo PATH; ?>soa/", { API:"idims", Action:"PriceUpdate", id_pricesuggestion:$id_pricesuggestion }, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			show_status("Preis erfolgreich übernommen.");
			price_research_view($id_item);
			return;
		});
	}
