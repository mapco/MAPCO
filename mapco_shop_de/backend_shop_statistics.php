<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>


<script type="text/javascript">
	$.datepicker.regional['de'] = {clearText: 			'löschen',
								   clearStatus: 		'aktuelles Datum löschen',
								   closeText: 			'schließen', 
								   closeStatus: 		'ohne Änderungen schließen',
								   prevText:			'zurück', 
								   prevStatus: 			'letzten Monat zeigen',
								   nextText: 			'vor', 
								   nextStatus: 			'nächsten Monat zeigen',
								   currentText: 		'heute', 
								   currentStatus: 		'',
								   monthNames: 			['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
								   monthNamesShort: 	['Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'],
								   monthStatus: 		'anderen Monat anzeigen',
								   yearStatus: 			'anderes Jahr anzeigen',
								   weekHeader: 			'Wo', 
								   weekStatus: 			'Woche des Monats',
								   dayNames: 			['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
								   dayNamesShort: 		['So','Mo','Di','Mi','Do','Fr','Sa'],
								   dayNamesMin: 		['So','Mo','Di','Mi','Do','Fr','Sa'],
								   dayStatus: 			'Setze DD als ersten Wochentag', 
								   dateStatus: 			'Wähle D, M d',
								   dateFormat: 			'dd.mm.yy', 
								   firstDay: 			1, 
								   initStatus:			'Wähle ein Datum', 
								   isRTL: 				false,
								   changeMonth: 		true,
								   changeYear: 			true,
								   showOtherMonths:		true,
								   selectOtherMonths:	true};
	
	function carriage_table_show(shop_data, shop_item_data, shops_selected, mode, shopcnt, item_id_array, time_type, date_from, date_to, date_comp_from, date_comp_to, shop_countries)
	{
		//Übersicht anzeigen
		//$("#statistics_show").empty();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "StatisticShippingNetGet",
																		mode:				mode,
																		time_type:			time_type,
																		date_from:			date_from,
																		date_to:			date_to,
																		date_comp_from:		date_comp_from,
																		date_comp_to:		date_comp_to,
																		'shops_selected[]':	shops_selected,
																		'shop_countries[]': shop_countries}, function ($data){
		
			//show_status2($data);
			
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) {show_status2($data); wait_dialog_hide(); return; }
			
			//shipping cost array
			var shipping = new Array();
			var shipping_comp = new Array()
			$xml.find("shop").each(function(){
				shipping[$(this).find("shop_id").text()] = $(this).find("shop_shipping_net").text();
			});
			$xml.find("shop_comp").each(function(){
				shipping_comp[$(this).find("shop_id").text()] = $(this).find("shop_shipping_net").text();
			});
			
			//shipping_cost table
			var shipping_all = 0;
			var shipping_all_comp = 0;
			var netto_all = 0;
			var netto_all_comp = 0;
			
			var table = $('<table class="hover" style="margin: 0px; width: 100%"></table>');
			
			if(mode=="single")
				var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline"></p></caption>');
			else
				var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline">Vergleichszeitraum: ' + $("#date_comp_from").val() + ' - ' + $("#date_comp_to").val() + '</p></caption>');
			table.append(caption);
			
			var thead = $('<thead></thead>');
			var tr = $('<tr></tr>');
			var th = $('<th>Shop</th>');
			tr.append(th);
			th = $('<th>Gesamt Netto</th>');
			tr.append(th);
			th = $('<th>Gesamt Frachtkosten</th>');
			tr.append(th);
			th = $('<th>Gesamt Netto incl. Frachtkosten</th>');
			tr.append(th);
			if(mode=="comp")
			{
				th = $('<th style="background-color: #99FF99; border-color: #99FF99">Gesamt Netto</th>');
				tr.append(th);
				th = $('<th style="background-color: #99FF99; border-color: #99FF99">Gesamt Frachtkosten</th>');
				tr.append(th);
				th = $('<th style="background-color: #99FF99; border-color: #99FF99">Gesamt Netto incl. Frachtkosten</th>');
				tr.append(th);
			}
			thead.append(tr);
			table.append(thead);
			
			var tbody = $('<tbody></tbody>');
			var td;
			for(a in shop_data)
			{
				if(shop_data[a]["parent_shop_id"]==0)
				{
					for(c in shops_selected)
					{
						if(a==shops_selected[c])
						{
							shipping_all += Math.round(shipping[a]*100)/100;
							netto_all += parseFloat(shop_item_data["shops"][a]["netto_shop"]) + parseFloat(shipping[a]);
							
							tr = $('<tr></tr>');
							td = $('<td>' + shop_data[a]["title"] + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right">' + shop_item_data["shops"][a]["netto_shop"] + ' €</td>');
							tr.append(td);
							td = $('<td style="text-align: right">' + (shipping[a]*1).toFixed(2) + ' €</td>');
							tr.append(td);
							td = $('<td style="text-align: right">' + (Math.round((parseFloat(shop_item_data["shops"][a]["netto_shop"]) + parseFloat(shipping[a]))*100)/100/1).toFixed(2) + ' €</td>');
							tr.append(td);
							if(mode=="comp")
							{
								shipping_all_comp += Math.round(shipping_comp[a]*100)/100;
								netto_all_comp += parseFloat(shop_item_data["shops"][a]["netto_comp_shop"]) + parseFloat(shipping_comp[a]);
								
								td = $('<td style="text-align: right">' + shop_item_data["shops"][a]["netto_comp_shop"] + ' €</td>');
								tr.append(td);
								td = $('<td style="text-align: right">' + (shipping_comp[a]*1).toFixed(2) + ' €</td>');
								tr.append(td);
								td = $('<td style="text-align: right">' + (Math.round((parseFloat(shop_item_data["shops"][a]["netto_comp_shop"]) + parseFloat(shipping_comp[a]))*100)/100/1).toFixed(2) + ' €</td>');
								tr.append(td);
							}
							tbody.append(tr);
						}
					}
				}
				for(b in shop_data)
				{
					if(shop_data[b]["parent_shop_id"]==a)
					{
						for(d in shops_selected)
						{
							if(b==shops_selected[d])
							{
								shipping_all += Math.round(parseFloat(shipping[b])*100)/100;
								netto_all += parseFloat(shop_item_data["shops"][b]["netto_shop"])+parseFloat(shipping[b]);
								
								tr = $('<tr></tr>');
								td = $('<td><p style="display: inline; margin-left: 10px">' + shop_data[b]["title"] + '</p></td>');
								tr.append(td);
								td = $('<td style="text-align: right">' + shop_item_data["shops"][b]["netto_shop"] + ' €</td>');
								tr.append(td);
								td = $('<td style="text-align: right">' + (shipping[b]*1).toFixed(2) + ' €</td>');
								tr.append(td);
								td = $('<td style="text-align: right">' + (Math.round((parseFloat(shop_item_data["shops"][b]["netto_shop"])+parseFloat(shipping[b]))*100)/100/1).toFixed(2) + ' €</td>');
								tr.append(td);
								if(mode=="comp")
								{
									shipping_all_comp += Math.round(parseFloat(shipping_comp[b])*100)/100;
									netto_all_comp += parseFloat(shop_item_data["shops"][b]["netto_comp_shop"])+parseFloat(shipping_comp[b]);
									
									td = $('<td style="text-align: right">' + shop_item_data["shops"][b]["netto_comp_shop"] + ' €</td>');
									tr.append(td);
									td = $('<td style="text-align: right">' + (shipping_comp[b]*1).toFixed(2) + ' €</td>');
									tr.append(td);
									td = $('<td style="text-align: right">' + (Math.round((parseFloat(shop_item_data["shops"][b]["netto_comp_shop"])+parseFloat(shipping_comp[b]))*100)/100/1).toFixed(2) + ' €</td>');
									tr.append(td);
								}
								tbody.append(tr);
							}
						}
					}
				}
			}
			table.append(tbody);
			
			var tfoot = $('<tfoot></tfoot>');
			tr = $('<tr></tr>');
			td = $('<td style="border: none"></td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["netto_all"] + ' €</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + Math.round(shipping_all*100)/100/1 + ' €</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + Math.round(netto_all*100)/100/1 + ' €</td>');
			tr.append(td);
			if(mode=="comp")
			{
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["netto_comp_all"] + ' €</td>');
				tr.append(td);
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + Math.round(shipping_all_comp*100)/100/1 + ' €</td>');
				tr.append(td);
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + Math.round(netto_all_comp*100)/100/1 + ' €</td>');
				tr.append(td);
			}
			tfoot.append(tr);
			table.append(tfoot);
			
			$('#statistics_show').append(table);
			wait_dialog_hide();
		});
	}
	
	function comp_clear()
	{
		$("#date_comp_from").val(null);
		$("#date_comp_to").val(null);
		$("#date_comp_to_hour").val(null);
		$("#date_comp_to_min").val(null);
	}
	
	function country_table_show(country_data, mode)
	{
		show_status2(print_r(country_data));
	}
	
	function  createSorter(propName) 
	{
		return function (a,b) {
			var aVal = a[propName], bVal = b[propName] ;
			aVal = aVal.toLowerCase();
			aVal = aVal.replace(/ä/g,"a");
			aVal = aVal.replace(/ö/g,"o");
			aVal = aVal.replace(/ü/g,"u");
			aVal = aVal.replace(/ß/g,"s");
		
			bVal = bVal.toLowerCase();
			bVal = bVal.replace(/ä/g,"a");
			bVal = bVal.replace(/ö/g,"o");
			bVal = bVal.replace(/ü/g,"u");
			bVal = bVal.replace(/ß/g,"s");
			return aVal > bVal ? 1 : (aVal < bVal ?  - 1 : 0);
		};
	}
	
	function customer_table_show(customer_data, mode)
	{
		var table = $('<table class="hover" id="statistic_table" style="margin: 0px; width: 100%"></table>');
		//caption
		if(mode=="single")
			var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline"></p></caption>');
		else
			var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline">Vergleichszeitraum: ' + $("#date_comp_from").val() + ' - ' + $("#date_comp_to").val() + '</p></caption>');
		table.append(caption);
		
		//head
		var thead = $('<thead></thead>');
		var tr = $('<tr style="cursor: pointer"></tr>');
		var th = $('<th>Kunde</th>');
		tr.append(th);
		th = $('<th style="width: 300px">Name</th>');
		tr.append(th);
		th = $('<th colspan="2" style="max-width: 100px">Gesamt Netto</th>');
		tr.append(th);
		if(mode=="comp")
		{
			th = $('<th colspan="2" style="max-width: 100px; background-color: #99FF99; border-color: #99FF99">Gesamt Netto</th>');
			tr.append(th);
		}
		thead.append(tr);
		table.append(thead);
		
		//body
		var tbody = $('<tbody></tbody>');
		var tr;
		var td;
		for(i in customer_data)
		{
			tr = $('<tr></tr>');
			td = $('<td>' + customer_data[i]["username"] + '</td>');
			tr.append(td)
			td = $('<td>' + customer_data[i]["name"] + '</td>');
			tr.append(td)
			td = $('<td style="text-align: right; border-right: none;">' + customer_data[i]["netto"] + '</td>');
			tr.append(td);
			td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
			tr.append(td);
			if(mode == "comp")
			{
				td = $('<td style="text-align: right; border-right: none">' + customer_data[i]["netto_comp"] + '</td>');
				tr.append(td);
				td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
				tr.append(td);
			}
			tbody.append(tr);
		}
		table.append(tbody);
/*
		var tbody = $('<tbody></tbody>');
		var i;
		var b;
		for(i in item_id_array)
		{
			var tr = $('<tr></tr>');
			var item_amount = 	0;
			var item_netto = 	0;
			for(b=0; b<shopcnt; b++)
			{
				if(b==0)
				{
					var td = $('<td>' + shop_item_data["shops"][shops_selected[b]]["items"][i]["title"] + '</td>');
					tr.append(td);
					td = $('<td style="border-right: solid; border-right-width: 1px;border-right-color: black">' + shop_item_data["shops"][shops_selected[b]]["items"][i]["MPN"] + '</td>');
					tr.append(td);
				}
				td = $('<td style="text-align: right">' + shop_item_data["shops"][shops_selected[b]]["items"][i]["amount_item"] + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; border-right: none">' + (shop_item_data["shops"][shops_selected[b]]["items"][i]["netto_item"]*1).toFixed(2) + '</td>');
				tr.append(td);
				td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
				tr.append(td);
				item_amount = item_amount + (shop_item_data["shops"][shops_selected[b]]["items"][i]["amount_item"]*1);
				item_netto = item_netto + ((shop_item_data["shops"][shops_selected[b]]["items"][i]["netto_item"]*1).toFixed(2)*1);
			}
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold">' + item_amount + '</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-right: none">' + (item_netto*1).toFixed(2) + '</td>');
			tr.append(td);
			td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
			tr.append(td);
			if(mode=="comp")
			{
				var item_comp_amount = 	0;
				var item_comp_netto = 	0;
				for(b=0; b<shopcnt; b++)
				{
					td = $('<td style="text-align: right">' + shop_item_data["shops"][shops_selected[b]]["items"][i]["amount_comp_item"] + '</td>');
					tr.append(td);
					td = $('<td style="text-align: right; border-right: none">' + (shop_item_data["shops"][shops_selected[b]]["items"][i]["netto_comp_item"]*1).toFixed(2) + '</td>');
					tr.append(td);
					td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
					tr.append(td);
					item_comp_amount = item_comp_amount + (shop_item_data["shops"][shops_selected[b]]["items"][i]["amount_comp_item"]*1);
					item_comp_netto = item_comp_netto + ((shop_item_data["shops"][shops_selected[b]]["items"][i]["netto_comp_item"]*1).toFixed(2)*1);
				}
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold">' + item_comp_amount + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-right: none">' + (item_comp_netto*1).toFixed(2) + '</td>');
				tr.append(td);
				td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
				tr.append(td);
			}
			tbody.append(tr);
		}
		table.append(tbody);
*/		
		
		
		$("#statistics_show").empty().append(table); 
		
		//$("#statistics_show").empty().append($('<p style="font-size: 20px; font-weight: bold">Fertig</p>'));
		$(function(){
		  $("#statistic_table").tablesorter({sortList: [[0,0]], locale: 'de', textExtraction: getTextExtractor()});
		});
	}
	
	function gart_table_show(shop_data, item_data, shops_selected, mode, shopcnt, gart, keyword, gart_data)
	{
		//summiertes Array zusammenbauen
		var shop_gart_data = 			new Array();
		shop_gart_data["amount"] = 		0;
		shop_gart_data["netto"] = 		0;
		shop_gart_data["amount_comp"] = 0;
		shop_gart_data["netto_comp"] = 	0;
		for(var a=0; a<shopcnt; a++)
		{
			shop_gart_data["amount_" + shops_selected[a]] = 		0;
			shop_gart_data["netto_" + shops_selected[a]] = 			0;
			shop_gart_data["amount_comp_" + shops_selected[a]] = 	0;
			shop_gart_data["netto_comp_" + shops_selected[a]] =  	0;
		}
		shop_gart_data["items"] = new Array();
		for(a=0; a<item_data.length; a++)
		{
			if(item_data[a]["GART"]==gart)
			{
				var netto = 0;
				if(item_data[a]["Currency_Code"]=="EUR")
				{
					netto = item_data[a]["netto"];
				}
				else
				{
					netto = (Math.round((item_data[a]["netto"]/item_data[a]["exchange_rate_to_EUR"])*100)/100).toFixed(2);
				}
				
				if(typeof shop_gart_data["items"][item_data[a]["item_id"]]=='undefined')//neu anlegen
				{
					shop_gart_data["items"][item_data[a]["item_id"]] = 				new Array();
					shop_gart_data["items"][item_data[a]["item_id"]]["item_id"] = 	item_data[a]["item_id"];
					shop_gart_data["items"][item_data[a]["item_id"]]["MPN"] = 		item_data[a]["MPN"];
					shop_gart_data["items"][item_data[a]["item_id"]]["title"] = 	item_data[a]["title"];
					shop_gart_data["items"][item_data[a]["item_id"]]["shop_id"] = 	item_data[a]["shop_id"];

					if(item_data[a]["time_range"]=="single")
					{
						shop_gart_data["items"][item_data[a]["item_id"]]["amount_item"] = 		item_data[a]["amount"];
						shop_gart_data["items"][item_data[a]["item_id"]]["netto_item"] = 		item_data[a]["amount"]*netto*1;
						shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item"] = 	0;
						shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item"] = 	0;
						for(var d=0; d<shopcnt; d++)
						{
							if(shops_selected[d]==item_data[a]["shop_id"])
							{
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 		item_data[a]["amount"];
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 		item_data[a]["amount"]*netto*1;
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = 0;
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	0;
								
								shop_gart_data["amount_" + shops_selected[d]] = shop_gart_data["amount_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
								shop_gart_data["netto_" + shops_selected[d]] =	shop_gart_data["netto_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
								shop_gart_data["amount"] = 						shop_gart_data["amount"]*1 + item_data[a]["amount"]*1;
								shop_gart_data["netto"] = 						shop_gart_data["netto"]*1 + item_data[a]["amount"]*netto*1;
							}
							else
							{
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 		0;
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 		0;
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = 0;
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	0;
							}
						}
					}
					else if(item_data[a]["time_range"]=="comp")
					{
						shop_gart_data["items"][item_data[a]["item_id"]]["amount_item"] = 		0;
						shop_gart_data["items"][item_data[a]["item_id"]]["netto_item"] = 		0;
						shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item"] = 	item_data[a]["amount"];
						shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item"] = 	item_data[a]["amount"]*netto*1;
						for(var d=0; d<shopcnt; d++)
						{
							if(shops_selected[d]==item_data[a]["shop_id"])
							{
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 		0;
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 		0;
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = item_data[a]["amount"];
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	item_data[a]["amount"]*netto*1;
								
								shop_gart_data["amount_comp_" + shops_selected[d]] = 	shop_gart_data["amount_comp_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
								shop_gart_data["netto_comp_" + shops_selected[d]] =		shop_gart_data["netto_comp_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
								shop_gart_data["amount_comp"] = 						shop_gart_data["amount_comp"]*1 + item_data[a]["amount"]*1;
								shop_gart_data["netto_comp"] = 							shop_gart_data["netto_comp"]*1 + item_data[a]["amount"]*netto*1;
							}
							else
							{
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 		0;
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 		0;
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = 0;
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	0;
							}
						}
					}
				}
				else//vorhandene ergänzen
				{
					if(item_data[a]["time_range"]=="single")
					{
						shop_gart_data["items"][item_data[a]["item_id"]]["amount_item"] = shop_gart_data["items"][item_data[a]["item_id"]]["amount_item"]*1 + item_data[a]["amount"]*1;
						shop_gart_data["items"][item_data[a]["item_id"]]["netto_item"] = shop_gart_data["items"][item_data[a]["item_id"]]["netto_item"]*1 + item_data[a]["amount"]*netto*1;
						for(var d=0; d<shopcnt; d++)
						{
							if(shops_selected[d]==item_data[a]["shop_id"])
							{
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 	shop_gart_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 	shop_gart_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
								
								shop_gart_data["amount_" + shops_selected[d]] = shop_gart_data["amount_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
								shop_gart_data["netto_" + shops_selected[d]] =	shop_gart_data["netto_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
								shop_gart_data["amount"] = 						shop_gart_data["amount"]*1 + item_data[a]["amount"]*1;
								shop_gart_data["netto"] = 						shop_gart_data["netto"]*1 + item_data[a]["amount"]*netto*1;
							}
						}
					}
					else if(item_data[a]["time_range"]=="comp")
					{
						shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item"] = shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item"]*1 + item_data[a]["amount"]*1;
						shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item"] = shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item"]*1 + item_data[a]["amount"]*netto*1;
						for(var d=0; d<shopcnt; d++)
						{
							if(shops_selected[d]==item_data[a]["shop_id"])
							{
								shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = 	shop_gart_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
								shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	shop_gart_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
								
								shop_gart_data["amount_comp_" + shops_selected[d]] =	shop_gart_data["amount_comp_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
								shop_gart_data["netto_comp_" + shops_selected[d]] =		shop_gart_data["netto_comp_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
								shop_gart_data["amount_comp"] = 						shop_gart_data["amount_comp"]*1 + item_data[a]["amount"]*1;
								shop_gart_data["netto_comp"] = 							shop_gart_data["netto_comp"]*1 + item_data[a]["amount"]*netto*1;
							}
						}
					}
				}
			}
		}
		//show_status2(print_r(shop_gart_data));	
		
		//Tabelle bauen
		$("#table_div").empty().append($('<p style="font-size: 20px; font-weight: bold">Bitte warten...Tabelle wird erzeugt...</p>'));
		var table = $('<table class="hover" id="statistic_table" style="margin: 0px; width: 100%"></table>');
		if(mode=="single")
			var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline"></p></caption>');
		else
			var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline">Vergleichszeitraum: ' + $("#date_comp_from").val() + ' - ' + $("#date_comp_to").val() + '</p></caption>');
		table.append(caption);
		
		var thead = $('<thead></thead>');
		var tr = $('<tr style="cursor: pointer"></tr>');
		var th = $('<th>Bezeichnung</th><th>MPN</th>');
		tr.append(th);
		for(var i=0; i<shopcnt; i++)
		{
			th = $('<th>Anz.</th><th style="max-width: 100px">' + shop_data[shops_selected[i]]["title"] + '</th><th></th>');
			tr.append(th);
		}
		th = $('<th>Anzahl gesamt</th><th colspan="2" style="max-width: 100px">Gesamt Netto Artikel</th>');
		tr.append(th);
		if(mode=="comp")
		{
			for(var i=0; i<shopcnt; i++)
			{
				th = $('<th style="background-color: #99FF99; border-color: #99FF99">Anz.</th><th colspan="2" style="max-width: 100px; background-color: #99FF99; border-color: #99FF99">' + shop_data[shops_selected[i]]["title"] + '</th>');
				tr.append(th);
			}
			th = $('<th style="background-color: #99FF99; border-color: #99FF99">Anzahl gesamt</th><th colspan="2" style="max-width: 100px; background-color: #99FF99; border-color: #99FF99">Gesamt Netto Artikel</th>');
			tr.append(th);
		}
		thead.append(tr);
		table.append(thead);
		
		var tbody = $('<tbody></tbody>');
		var i;
		var b;
		for(i in shop_gart_data["items"])
		{
			var tr = $('<tr></tr>');
			for(b=0; b<shopcnt; b++)
			{
				if(b==0)
				{
					var td = $('<td>' + shop_gart_data["items"][i]["title"] + '</td>');
					tr.append(td);
					td = $('<td style="border-right: solid; border-right-width: 1px;border-right-color: black">' + shop_gart_data["items"][i]["MPN"] + '</td>');
					tr.append(td);
				}
				td = $('<td style="text-align: right">' + shop_gart_data["items"][i]["amount_item_" + shops_selected[b]] + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; border-right: none">' + (Math.round(shop_gart_data["items"][i]["netto_item_" + shops_selected[b]]*100)/100/1).toFixed(2) + '</td>');
				tr.append(td);
				td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
				tr.append(td);
			}
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold">' + shop_gart_data["items"][i]["amount_item"] + '</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-right: none">' + (Math.round(shop_gart_data["items"][i]["netto_item"]*100)/100/1).toFixed(2) + '</td>');
			tr.append(td);
			td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
			tr.append(td);
			if(mode=="comp")
			{
				for(b=0; b<shopcnt; b++)
				{
					td = $('<td style="text-align: right">' + shop_gart_data["items"][i]["amount_comp_item_" + shops_selected[b]] + '</td>');
					tr.append(td);
					td = $('<td style="text-align: right; border-right: none">' + (Math.round(shop_gart_data["items"][i]["netto_comp_item_" + shops_selected[b]]*100)/100/1).toFixed(2) + '</td>');
					tr.append(td);
					td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
					tr.append(td);
				}
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold">' + shop_gart_data["items"][i]["amount_comp_item"] + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-right: none">' + (Math.round(shop_gart_data["items"][i]["netto_comp_item"]*100)/100/1).toFixed(2) + '</td>');
				tr.append(td);
				td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
				tr.append(td);
			}
			tbody.append(tr);
		}
		table.append(tbody);
		
		var tfoot = $('<tfoot></tfoot>');
		tr = $('<tr style="height: 50px"></tr>');
		
		td = $('<td colspan="2" style="border-left: none; border-bottom: none;  border-right: solid;  border-right-width: 1px; border-right-color: black"></td>');
		tr.append(td);
		for(var i=0; i<shopcnt; i++)
		{
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_gart_data["amount_" + shops_selected[i]] + '</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_gart_data["netto_" + shops_selected[i]]*100)/100/1).toFixed(2) + '</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: solid;  border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
			tr.append(td);
		}
		td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_gart_data["amount"] + '</td>');
		tr.append(td);
		td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_gart_data["netto"]*100)/100/1).toFixed(2) + '</td>');
		tr.append(td);
		td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px; border-top: solid; border-top-width: 2px; border-top-color: black">€</td>');
		tr.append(td);
		if(mode=="comp")
		{
			for(var i=0; i<shopcnt; i++)
			{
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_gart_data["amount_comp_" + shops_selected[i]] + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_gart_data["netto_comp_" + shops_selected[i]]*100)/100/1).toFixed(2) + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: solid;  border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
				tr.append(td);
			}
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_gart_data["amount_comp"] + '</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_gart_data["netto_comp"]*100)/100/1).toFixed(2) + '</td>');
			tr.append(td);
			td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px; border-top: solid; border-top-width: 2px; border-top-color: black">€</td>');
			tr.append(td);
		}
		tfoot.append(tr);
		table.append(tfoot);
		
		$("#table_div").empty().append(table);
		
		$(function(){
			var s = 2+3*shopcnt;
		  	$("#statistic_table").tablesorter({sortList: [[s,1]], locale: 'de', textExtraction: getTextExtractor()});
		});
	}
	
	function getTextExtractor()
	{
	  return (function() {
		var patternLetters = /[öäüÖÄÜáàâéèêúùûóòôÁÀÂÉÈÊÚÙÛÓÒÔß]/g;
		var patternDateDmy = /^(?:\D+)?(\d{1,2})\.(\d{1,2})\.(\d{2,4})$/;
		var lookupLetters = {
		  "ä": "a", "ö": "o", "ü": "u",
		  "Ä": "A", "Ö": "O", "Ü": "U",
		  "á": "a", "à": "a", "â": "a",
		  "é": "e", "è": "e", "ê": "e",
		  "ú": "u", "ù": "u", "û": "u",
		  "ó": "o", "ò": "o", "ô": "o",
		  "Á": "A", "À": "A", "Â": "A",
		  "É": "E", "È": "E", "Ê": "E",
		  "Ú": "U", "Ù": "U", "Û": "U",
		  "Ó": "O", "Ò": "O", "Ô": "O",
		  "ß": "s"
		};
		var letterTranslator = function(match) { 
		  return lookupLetters[match] || match;
		}
	
		return function(node) {
		  var text = $.trim($(node).text());
		  var date = text.match(patternDateDmy);
		  if (date)
			return [date[3], date[2], date[1]].join("-");
		  else
			return text.replace(patternLetters, letterTranslator);
		}
	  })();
	}
	
	function item_show_hide(c)
	{
		$("."+c).toggle("fade", 500);
	}
	
	function item_table_show(shop_data, shop_item_data, shops_selected, mode, shopcnt, item_id_array)
	{		
		var table = $('<table class="hover" id="statistic_table" style="margin: 0px; width: 100%"></table>');
		if(mode=="single")
			var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline"></p></caption>');
		else
			var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline">Vergleichszeitraum: ' + $("#date_comp_from").val() + ' - ' + $("#date_comp_to").val() + '</p></caption>');
		table.append(caption);
		
		var thead = $('<thead></thead>');
		var tr = $('<tr style="cursor: pointer"></tr>');
		var th = $('<th>Bezeichnung</th><th>MPN</th>');
		tr.append(th);
		for(var i=0; i<shopcnt; i++)
		{
			th = $('<th>Anz.</th><th colspan="2" style="max-width: 100px">' + shop_data[shops_selected[i]]["title"] + '</th>');
			tr.append(th);
		}
		th = $('<th>Anzahl gesamt</th><th colspan="2" style="max-width: 100px">Gesamt Netto Artikel</th>');
		tr.append(th);
		if(mode=="comp")
		{
			for(var i=0; i<shopcnt; i++)
			{
				th = $('<th style="background-color: #99FF99; border-color: #99FF99">Anz.</th><th colspan="2" style="max-width: 100px; background-color: #99FF99; border-color: #99FF99">' + shop_data[shops_selected[i]]["title"] + '</th>');
				tr.append(th);
			}
			th = $('<th style="background-color: #99FF99; border-color: #99FF99">Anzahl gesamt</th><th colspan="2" style="max-width: 100px; background-color: #99FF99; border-color: #99FF99">Gesamt Netto Artikel</th>');
			tr.append(th);
		}
		thead.append(tr);
		table.append(thead);
		
		var tbody = $('<tbody></tbody>');
		var i;
		var b;
		for(i in item_id_array)
		{
			var tr = $('<tr></tr>');
			var item_amount = 	0;
			var item_netto = 	0;
			for(b=0; b<shopcnt; b++)
			{
				if(b==0)
				{
					var td = $('<td>' + shop_item_data["shops"][shops_selected[b]]["items"][i]["title"] + '</td>');
					tr.append(td);
					td = $('<td style="border-right: solid; border-right-width: 1px;border-right-color: black">' + shop_item_data["shops"][shops_selected[b]]["items"][i]["MPN"] + '</td>');
					tr.append(td);
				}
				td = $('<td style="text-align: right">' + shop_item_data["shops"][shops_selected[b]]["items"][i]["amount_item"] + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; border-right: none">' + (shop_item_data["shops"][shops_selected[b]]["items"][i]["netto_item"]*1).toFixed(2) + '</td>');
				tr.append(td);
				td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
				tr.append(td);
				item_amount = item_amount + (shop_item_data["shops"][shops_selected[b]]["items"][i]["amount_item"]*1);
				item_netto = item_netto + ((shop_item_data["shops"][shops_selected[b]]["items"][i]["netto_item"]*1).toFixed(2)*1);
			}
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold">' + item_amount + '</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-right: none">' + (item_netto*1).toFixed(2) + '</td>');
			tr.append(td);
			td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
			tr.append(td);
			if(mode=="comp")
			{
				var item_comp_amount = 	0;
				var item_comp_netto = 	0;
				for(b=0; b<shopcnt; b++)
				{
					td = $('<td style="text-align: right">' + shop_item_data["shops"][shops_selected[b]]["items"][i]["amount_comp_item"] + '</td>');
					tr.append(td);
					td = $('<td style="text-align: right; border-right: none">' + (shop_item_data["shops"][shops_selected[b]]["items"][i]["netto_comp_item"]*1).toFixed(2) + '</td>');
					tr.append(td);
					td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
					tr.append(td);
					item_comp_amount = item_comp_amount + (shop_item_data["shops"][shops_selected[b]]["items"][i]["amount_comp_item"]*1);
					item_comp_netto = item_comp_netto + ((shop_item_data["shops"][shops_selected[b]]["items"][i]["netto_comp_item"]*1).toFixed(2)*1);
				}
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold">' + item_comp_amount + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-right: none">' + (item_comp_netto*1).toFixed(2) + '</td>');
				tr.append(td);
				td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
				tr.append(td);
			}
			tbody.append(tr);
		}
		table.append(tbody);
		
		var tfoot = $('<tfoot></tfoot>');
		tr = $('<tr style="height: 50px"></tr>');
		
		td = $('<td colspan="2" style="border-left: none; border-bottom: none;  border-right: solid;  border-right-width: 1px; border-right-color: black"></td>');
		tr.append(td);
		for(var i=0; i<shopcnt; i++)
		{
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["shops"][shops_selected[i]]["amount_shop"] + '</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (shop_item_data["shops"][shops_selected[i]]["netto_shop"]*1).toFixed(2) + '</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: solid;  border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
			tr.append(td);
		}
		td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["amount_all"] + '</td>');
		tr.append(td);
		td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + shop_item_data["netto_all"] + '</td>');
		tr.append(td);
		td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px; border-top: solid; border-top-width: 2px; border-top-color: black">€</td>');
		tr.append(td);
		if(mode=="comp")
		{
			for(var i=0; i<shopcnt; i++)
			{
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["shops"][shops_selected[i]]["amount_comp_shop"] + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (shop_item_data["shops"][shops_selected[i]]["netto_comp_shop"]*1).toFixed(2) + '</td>');
				tr.append(td);
				td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: solid;  border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
				tr.append(td);
			}
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["amount_comp_all"] + '</td>');
			tr.append(td);
			td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + shop_item_data["netto_comp_all"] + '</td>');
			tr.append(td);
			td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px; border-top: solid; border-top-width: 2px; border-top-color: black">€</td>');
			tr.append(td);
		}
		tfoot.append(tr);
		table.append(tfoot);
		
		$("#statistics_show").empty().append(table); 
		
		//$("#statistics_show").empty().append($('<p style="font-size: 20px; font-weight: bold">Fertig</p>'));
		$(function(){
		  $("#statistic_table").tablesorter({sortList: [[0,0]], locale: 'de', textExtraction: getTextExtractor()});
		});
	}
/*		
	function list_select_radiobutton_clicked()
	{
		if($("input[name='list_select_radiobutton']:checked").val()=="user_list")
			$("#user_list_select").prop("disabled", false);
		else
			$("#user_list_select").prop("disabled", true);
	}
*/	
	/*function lists_load(shop_data)
	{
		var lists_data = new Array();
		
		$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "ListsGet"},
			function (data)
			{
				show_status2(data);
				var $xml = $($.parseXML(data));
				var $ack = $xml.find("Ack");
				if ( $ack.text()=="Success" )
				{
					$xml.find("list").each(
						function()
						{
							lists_data[$(this).find("id_list").text()] = new Array;
							lists_data[$(this).find("id_list").text()]["title"] = $(this).find("title").text();
							//lists_data[$(this).find("id_list").text()]["private"] = $(this).find("private").text();
							lists_data[$(this).find("id_list").text()]["firstmod_user"] = $(this).find("firstmod_user").text();
							//lists_data[$(this).find("id_list").text()]["parent_shop_id"] = $(this).find("parent_shop_id").text();
						}
					);
					//shop_categories_load(shop_data, lists_data);
				}
			}
		);
	}*/
	
	function list_table_show(shop_data, item_data, shops_selected, mode, shopcnt, list, lists_data, listtypes_data, list_title)
	{
		//show_status2(print_r(lists_data));
		var list_item_ids = new Array();
		for(a in lists_data[list]["items"])
		{
			list_item_ids.push(lists_data[list]["items"][a]["item_id"]);
		}
		
		if(list_item_ids.length == 0)
			list_item_ids[0] = 0;
			
		$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "ListsGet", 'list_item_ids[]': list_item_ids, mode: "items"},
			function (data)
			{
				var list_item_data = new Array();
				
				var $xml = $($.parseXML(data));
				var $ack = $xml.find("Ack");
				if ( $ack.text()=="Success" )
				{
					$xml.find("item").each(
						function()
						{
							list_item_data[$(this).find("item_id").text()] = new Array();
							list_item_data[$(this).find("item_id").text()]["MPN"] = $(this).find("MPN").text();
							list_item_data[$(this).find("item_id").text()]["item_title"] = $(this).find("item_title").text();
						}
					);
					//show_status2(print_r(list_item_data));
					//show_status2(data);
					//summiertes Array zusammenbauen
					var shop_list_data = 			new Array();
					shop_list_data["amount"] = 		0;
					shop_list_data["netto"] = 		0;
					shop_list_data["amount_comp"] = 0;
					shop_list_data["netto_comp"] = 	0;
					for(var a=0; a<shopcnt; a++)
					{
						shop_list_data["amount_" + shops_selected[a]] = 		0;
						shop_list_data["netto_" + shops_selected[a]] = 			0;
						shop_list_data["amount_comp_" + shops_selected[a]] = 	0;
						shop_list_data["netto_comp_" + shops_selected[a]] =  	0;
					}
					shop_list_data["items"] = new Array();
					
					for(b in list_item_data)
					{
						if(b>0)
						{
							shop_list_data["items"][b] = 			new Array();
							shop_list_data["items"][b]["item_id"] = b;
							shop_list_data["items"][b]["MPN"] = 	list_item_data[b]["MPN"];
							shop_list_data["items"][b]["title"] = 	list_item_data[b]["item_title"];
							
							shop_list_data["items"][b]["amount_item"] = 		0;
							shop_list_data["items"][b]["netto_item"] = 			0;
							shop_list_data["items"][b]["amount_comp_item"] = 	0;
							shop_list_data["items"][b]["netto_comp_item"] = 	0;
							//shop_list_data["items"][b]["shop_id"] = item_data[a]["shop_id"];
							for(var a=0; a<shopcnt; a++)
							{
								shop_list_data["items"][b]["amount_item_" + shops_selected[a]] = 		0;
								shop_list_data["items"][b]["netto_item_" + shops_selected[a]] = 		0;
								shop_list_data["items"][b]["amount_comp_item_" + shops_selected[a]] = 	0;
								shop_list_data["items"][b]["netto_comp_item_" + shops_selected[a]] =  	0;
							}
						}
					}
					
					for(a=0; a<item_data.length; a++)
					{
						//if($.inArray(item_data[a]["item_id"], lists_data[list]["items"])>-1)
						if($.inArray(item_data[a]["item_id"], list_item_ids)>-1)
						{
							var netto = 0;
							if(item_data[a]["Currency_Code"]=="EUR")
							{
								netto = item_data[a]["netto"];
							}
							else
							{
								netto = (Math.round((item_data[a]["netto"]/item_data[a]["exchange_rate_to_EUR"])*100)/100).toFixed(2);
							}
							
							/*if(typeof shop_list_data["items"][item_data[a]["item_id"]]=='undefined')//neu anlegen
							{
								shop_list_data["items"][item_data[a]["item_id"]] = 				new Array();
								shop_list_data["items"][item_data[a]["item_id"]]["item_id"] = 	item_data[a]["item_id"];
								shop_list_data["items"][item_data[a]["item_id"]]["MPN"] = 		item_data[a]["MPN"];
								shop_list_data["items"][item_data[a]["item_id"]]["title"] = 	item_data[a]["title"];
								shop_list_data["items"][item_data[a]["item_id"]]["shop_id"] = 	item_data[a]["shop_id"];
			
								if(item_data[a]["time_range"]=="single")
								{
									shop_list_data["items"][item_data[a]["item_id"]]["amount_item"] = 		item_data[a]["amount"];
									shop_list_data["items"][item_data[a]["item_id"]]["netto_item"] = 		item_data[a]["amount"]*netto*1;
									shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item"] = 	0;
									shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item"] = 	0;
									for(var d=0; d<shopcnt; d++)
									{
										if(shops_selected[d]==item_data[a]["shop_id"])
										{
											shop_list_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 		item_data[a]["amount"];
											shop_list_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 		item_data[a]["amount"]*netto*1;
											shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = 0;
											shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	0;
											
											shop_list_data["amount_" + shops_selected[d]] = shop_list_data["amount_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
											shop_list_data["netto_" + shops_selected[d]] =	shop_list_data["netto_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
											shop_list_data["amount"] = 						shop_list_data["amount"]*1 + item_data[a]["amount"]*1;
											shop_list_data["netto"] = 						shop_list_data["netto"]*1 + item_data[a]["amount"]*netto*1;
										}
										else
										{
											shop_list_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 		0;
											shop_list_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 		0;
											shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = 0;
											shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	0;
										}
									}
								}
								else if(item_data[a]["time_range"]=="comp")
								{
									shop_list_data["items"][item_data[a]["item_id"]]["amount_item"] = 		0;
									shop_list_data["items"][item_data[a]["item_id"]]["netto_item"] = 		0;
									shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item"] = 	item_data[a]["amount"];
									shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item"] = 	item_data[a]["amount"]*netto*1;
									for(var d=0; d<shopcnt; d++)
									{
										if(shops_selected[d]==item_data[a]["shop_id"])
										{
											shop_list_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 		0;
											shop_list_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 		0;
			
											shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = item_data[a]["amount"];
											shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	item_data[a]["amount"]*netto*1;
											
											shop_list_data["amount_comp_" + shops_selected[d]] = 	shop_list_data["amount_comp_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
											shop_list_data["netto_comp_" + shops_selected[d]] =		shop_list_data["netto_comp_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
											shop_list_data["amount_comp"] = 						shop_list_data["amount_comp"]*1 + item_data[a]["amount"]*1;
											shop_list_data["netto_comp"] = 							shop_list_data["netto_comp"]*1 + item_data[a]["amount"]*netto*1;
										}
										else
										{
											shop_list_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 		0;
											shop_list_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 		0;
											shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = 0;
											shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	0;
										}
									}
								}
							}*/
							//else//vorhandene ergänzen
							//{
							if(item_data[a]["time_range"]=="single")
							{
								shop_list_data["items"][item_data[a]["item_id"]]["amount_item"] = shop_list_data["items"][item_data[a]["item_id"]]["amount_item"]*1 + item_data[a]["amount"]*1;
								shop_list_data["items"][item_data[a]["item_id"]]["netto_item"] = shop_list_data["items"][item_data[a]["item_id"]]["netto_item"]*1 + item_data[a]["amount"]*netto*1;
								for(var d=0; d<shopcnt; d++)
								{
									if(shops_selected[d]==item_data[a]["shop_id"])
									{
										shop_list_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]] = 	shop_list_data["items"][item_data[a]["item_id"]]["amount_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
										shop_list_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]] = 	shop_list_data["items"][item_data[a]["item_id"]]["netto_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
										
										shop_list_data["amount_" + shops_selected[d]] = shop_list_data["amount_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
										shop_list_data["netto_" + shops_selected[d]] =	shop_list_data["netto_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
										shop_list_data["amount"] = 						shop_list_data["amount"]*1 + item_data[a]["amount"]*1;
										shop_list_data["netto"] = 						shop_list_data["netto"]*1 + item_data[a]["amount"]*netto*1;
									}
								}
							}
							else if(item_data[a]["time_range"]=="comp")
							{
								shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item"] = shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item"]*1 + item_data[a]["amount"]*1;
								shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item"] = shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item"]*1 + item_data[a]["amount"]*netto*1;
								for(var d=0; d<shopcnt; d++)
								{
									if(shops_selected[d]==item_data[a]["shop_id"])
									{
										shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]] = 	shop_list_data["items"][item_data[a]["item_id"]]["amount_comp_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
										shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]] = 	shop_list_data["items"][item_data[a]["item_id"]]["netto_comp_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
										
										shop_list_data["amount_comp_" + shops_selected[d]] =	shop_list_data["amount_comp_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
										shop_list_data["netto_comp_" + shops_selected[d]] =		shop_list_data["netto_comp_" + shops_selected[d]]*1 + item_data[a]["amount"]*netto*1;
										shop_list_data["amount_comp"] = 						shop_list_data["amount_comp"]*1 + item_data[a]["amount"]*1;
										shop_list_data["netto_comp"] = 							shop_list_data["netto_comp"]*1 + item_data[a]["amount"]*netto*1;
									}
								}
							}
							//}
						}
					}
					//show_status2(print_r(shop_list_data));	
					
					//Tabelle bauen
					$("#table_div").empty().append($('<p style="font-size: 20px; font-weight: bold">Bitte warten...Tabelle wird erzeugt...</p>'));
					var table = $('<table class="hover" id="statistic_table" style="margin: 0px; width: 100%"></table>');
					if(mode=="single")
						var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline"></p></caption>');
					else
						var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline">Vergleichszeitraum: ' + $("#date_comp_from").val() + ' - ' + $("#date_comp_to").val() + '</p></caption>');
					table.append(caption);
					
					var thead = $('<thead></thead>');
					var tr = $('<tr style="cursor: pointer"></tr>');
					var th = $('<th>Bezeichnung</th><th>MPN</th>');
					tr.append(th);
					for(var i=0; i<shopcnt; i++)
					{
						th = $('<th>Anz.</th><th style="max-width: 100px">' + shop_data[shops_selected[i]]["title"] + '</th><th></th>');
						tr.append(th);
					}
					th = $('<th>Anzahl gesamt</th><th colspan="2" style="max-width: 100px">Gesamt Netto Artikel</th>');
					tr.append(th);
					if(mode=="comp")
					{
						for(var i=0; i<shopcnt; i++)
						{
							th = $('<th style="background-color: #99FF99; border-color: #99FF99">Anz.</th><th colspan="2" style="max-width: 100px; background-color: #99FF99; border-color: #99FF99">' + shop_data[shops_selected[i]]["title"] + '</th>');
							tr.append(th);
						}
						th = $('<th style="background-color: #99FF99; border-color: #99FF99">Anzahl gesamt</th><th colspan="2" style="max-width: 100px; background-color: #99FF99; border-color: #99FF99">Gesamt Netto Artikel</th>');
						tr.append(th);
					}
					thead.append(tr);
					table.append(thead);
					
					var tbody = $('<tbody></tbody>');
					var i;
					var b;
					for(i in shop_list_data["items"])
					{
						var tr = $('<tr></tr>');
						for(b=0; b<shopcnt; b++)
						{
							if(b==0)
							{
								var td = $('<td>' + shop_list_data["items"][i]["title"] + '</td>');
								tr.append(td);
								td = $('<td style="border-right: solid; border-right-width: 1px;border-right-color: black">' + shop_list_data["items"][i]["MPN"] + '</td>');
								tr.append(td);
							}
							td = $('<td style="text-align: right">' + shop_list_data["items"][i]["amount_item_" + shops_selected[b]] + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right; border-right: none">' + (Math.round(shop_list_data["items"][i]["netto_item_" + shops_selected[b]]*100)/100/1).toFixed(2) + '</td>');
							tr.append(td);
							td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
							tr.append(td);
						}
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold">' + shop_list_data["items"][i]["amount_item"] + '</td>');
						tr.append(td);
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-right: none">' + (Math.round(shop_list_data["items"][i]["netto_item"]*100)/100/1).toFixed(2) + '</td>');
						tr.append(td);
						td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
						tr.append(td);
						if(mode=="comp")
						{
							for(b=0; b<shopcnt; b++)
							{
								td = $('<td style="text-align: right">' + shop_list_data["items"][i]["amount_comp_item_" + shops_selected[b]] + '</td>');
								tr.append(td);
								td = $('<td style="text-align: right; border-right: none">' + (Math.round(shop_list_data["items"][i]["netto_comp_item_" + shops_selected[b]]*100)/100/1).toFixed(2) + '</td>');
								tr.append(td);
								td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
								tr.append(td);
							}
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold">' + shop_list_data["items"][i]["amount_comp_item"] + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-right: none">' + (Math.round(shop_list_data["items"][i]["netto_comp_item"]*100)/100/1).toFixed(2) + '</td>');
							tr.append(td);
							td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
							tr.append(td);
						}
						tbody.append(tr);
					}
					table.append(tbody);
					
					var tfoot = $('<tfoot></tfoot>');
					tr = $('<tr style="height: 50px"></tr>');
					
					td = $('<td colspan="2" style="border-left: none; border-bottom: none;  border-right: solid;  border-right-width: 1px; border-right-color: black"></td>');
					tr.append(td);
					for(var i=0; i<shopcnt; i++)
					{
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_list_data["amount_" + shops_selected[i]] + '</td>');
						tr.append(td);
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_list_data["netto_" + shops_selected[i]]*100)/100/1).toFixed(2) + '</td>');
						tr.append(td);
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: solid;  border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
						tr.append(td);
					}
					td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_list_data["amount"] + '</td>');
					tr.append(td);
					td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_list_data["netto"]*100)/100/1).toFixed(2) + '</td>');
					tr.append(td);
					td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px; border-top: solid; border-top-width: 2px; border-top-color: black">€</td>');
					tr.append(td);
					if(mode=="comp")
					{
						for(var i=0; i<shopcnt; i++)
						{
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_list_data["amount_comp_" + shops_selected[i]] + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_list_data["netto_comp_" + shops_selected[i]]*100)/100/1).toFixed(2) + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: solid;  border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
							tr.append(td);
						}
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_list_data["amount_comp"] + '</td>');
						tr.append(td);
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_list_data["netto_comp"]*100)/100/1).toFixed(2) + '</td>');
						tr.append(td);
						td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px; border-top: solid; border-top-width: 2px; border-top-color: black">€</td>');
						tr.append(td);
					}
					tfoot.append(tr);
					table.append(tfoot);
					
					$("#table_div").empty().append(table);
					
					if(shop_list_data["items"].length>0)
					{
						$(function(){
							var s = 2+3*shopcnt;
							$("#statistic_table").tablesorter({sortList: [[s,1]], locale: 'de', textExtraction: getTextExtractor()});
						});
					}
				}
			}
		);
	}
								   
	function settings_init(shop_data, category_data, shop_countries)
	{
		//show_status2(print_r(category_data));
		html = '';
/*		
		if(<?php echo $_SESSION["id_user"];?> == 49352)
		{
			html += '<div style="border: solid; border-width: 1px; border-color: #E6E6E6;">';
			html += '	<p style="font-weight: bold; background-color: #E6E6E6; padding: 5px">Test-Select</p>';
			html += '	<select name="test[]" multiple>';
			html += '		<option selected>test1</option>';
			html += '		<option>test2</option>';
			html += '		<option>test3</option>';
			html += '		<option selected>test4</option>';
			html += '		<option>test5</option>';
			html += '	</select>';
			html += '</div>';
		}
*/		
		html += '<div id="options" style="border: solid; border-width: 1px; border-color: #E6E6E6; margin-top: 0px">';
		html += '	<p style="font-weight: bold; background-color: #E6E6E6; padding: 5px">Statistik ausführen</p>';
		html += '	<input type="button" id="statistic_show_button" value="Start" style="margin-top: 5px; margin-bottom: 5px; width: 70px; margin-left: 140px">';
		html += '	<p id="message" style="display: none"></p>';
		html += '</div>';
		
		html += '<div id="date_range" style="border: solid; border-width: 1px; border-color: #E6E6E6; margin-top: 5px">';
		html += '	<p style="font-weight: bold; background-color: #E6E6E6; padding: 5px">Zeitraum auswählen</p>';
		html += '	<p style="display: inline; padding: 5px">vom:</p><input type="text" id="date_from" style="margin: 5px; width: 70px" readonly>';
		html += '	<p style="display: inline; padding: 5px">bis:</p><input type="text" id="date_to" style="margin: 5px; width: 70px" readonly>';
		html += '	<input style="width:30px;" type="text" id="date_to_hour" />:<input style="width:30px;" type="text" id="date_to_min" />';
		html += '</div>';
		
		html += '<div id="date_range_comp" style="border: solid; border-width: 1px; border-color: #E6E6E6; margin-top: 5px">';
		html += '	<p style="font-weight: bold; background-color: #E6E6E6; padding: 5px">Vergleichszeitraum auswählen (optional)</p>';
		html += '	<p style="display: inline; padding: 5px">vom:</p><input type="text" id="date_comp_from" style="margin: 5px; width: 70px" readonly>';
		html += '	<p style="display: inline; padding: 5px">bis:</p><input type="text" id="date_comp_to" style="margin: 5px; width: 70px" readonly>';
		html += '	<input style="width:30px;" type="text" id="date_comp_to_hour" />:<input style="width:30px;" type="text" id="date_comp_to_min" />';
		html += '	<input type="button" id="comp_clear_button" value="Löschen" style="float: right; margin: 5px">';
		html += '</div>';
		
		html += '<div id="shops_select" style="border: solid; border-width: 1px; border-color: #E6E6E6; margin-top: 5px; padding-bottom: 5px">';
		html += '	<p style="font-weight: bold; background-color: #E6E6E6; padding: 5px">Shops auswählen</p>';
		//var shop_data_sort = new Array();
/*		
		for(a in shop_data)
		{
			if(shop_data[a]["parent_shop_id"]==0)
				html += '<input type="checkbox" id="' + a + '" value="' + a + '" style="margin-top: 5px; margin-left: 5px"> ' + shop_data[a]["title"] + '<br>';
			for(b in shop_data)
			{
				if(shop_data[b]["parent_shop_id"]==a)
					if(b==3 || b==4 || b==5)
						html += '<p style="display: inline; padding-left: 10px"></p><input type="checkbox" id="' + b + '" value="' + b + '" checked style="margin-top: 5px; margin-left: 5px"> ' + shop_data[b]["title"] + '<br>';
					else
						html += '<p style="display: inline; padding-left: 10px"></p><input type="checkbox" id="' + b + '" value="' + b + '" style="margin-top: 5px; margin-left: 5px"> ' + shop_data[b]["title"] + '<br>';
			}
		}
*/		
		html += '<select id="shops_select" size="6" multiple style="margin-top: 5px; margin-left: 5px">';		
		for(a in shop_data)
		{
			if(shop_data[a]["parent_shop_id"]==0)
				html += '<option value="' + a + '">' + shop_data[a]["title"] + '</option>';
			for(b in shop_data)
			{
				if(shop_data[b]["parent_shop_id"]==a)
					if(b==3 || b==4 || b==5)
						html += '<option value="' + b + '" selected>&nbsp&nbsp&nbsp' + shop_data[b]["title"] + '</option>';
					else
						html += '<option value="' + b + '">&nbsp&nbsp&nbsp' + shop_data[b]["title"] + '</option>';
			}
		}
		html += '</select>';
		
		html += '</div>';
		
		html += '<div id="list_select_div" style="border: solid; border-width: 1px; border-color: #E6E6E6; margin-top: 5px; padding-bottom: 5px">';
		html += '	<p style="font-weight: bold; background-color: #E6E6E6; padding: 5px">Statistiken</p>';
		
		html += '	<select id="list_select" style="margin-top: 5px; margin-left: 5px">';
		html += '		<option value="item_list" selected>Artikelliste</option>';
		html += '		<option value="item_gart">generische Artikelnummer</option>';
		html += '		<option value="item_group">Artikelgruppen</option>';
		html += '		<option value="user_list">nach Listen</option>';
		html += '		<option value="customer">Kunden</option>';
		html += '		<option value="countries">Länder-Rangliste (under construction)</option>';
		html += '	</select>';
/*		
		html += '	<input type="radio" name="list_select_radiobutton" onclick="list_select_radiobutton_clicked()" style="margin-top: 5px; margin-left: 5px" value="item_list" checked> Artikelliste<br />';
		html += '	<input type="radio" name="list_select_radiobutton" onclick="list_select_radiobutton_clicked()" style="margin-top: 5px; margin-left: 5px" value="item_gart"> generische Artikelnummer<br />';
		html += '	<input type="radio" name="list_select_radiobutton" onclick="list_select_radiobutton_clicked()" style="margin-top: 5px; margin-left: 5px" value="item_group"> Artikelgruppen<br />';
		html += '	<input type="radio" name="list_select_radiobutton" onclick="list_select_radiobutton_clicked()" style="margin-top: 5px; margin-left: 5px" value="user_list"> nach Liste<br />';
		html += '	<input type="radio" name="list_select_radiobutton" onclick="list_select_radiobutton_clicked()" style="margin-top: 5px; margin-left: 5px" value="customer"> Kunden';
*/		
		/*html += '	<select id="user_list_select" style="width: 240px" disabled>';
		html += '		<option value="">bitte eine Liste auswählen</option>';
		for(c in lists_data)
		{
			if(lists_data[c]["private"]==0 || (lists_data[c]["private"]==1 && lists_data[c]["firstmod_user"]==<?php echo $_SESSION["id_user"]?>))
				html += '	<option value="' + c + '">' + lists_data[c]["title"] + '</option>';
		}
		html += '	</select>';*/
		html += '</div>';
		
		html += '<div id="time_type" style="border: solid; border-width: 1px; border-color: #E6E6E6 ;margin-top: 5px;">';
		html += '	<p style="font-weight: bold; background-color: #E6E6E6; padding: 5px">Zeitraum-Typ</p>';
//		html += '	<input type="radio" name="time_type_radiobutton" style="margin-top: 5px; margin-left: 5px" value="order" checked> Bestelleingang<br />';
//		html += '	<input type="radio" name="time_type_radiobutton" style="margin-bottom: 5px; margin-top: 5px; margin-left: 5px" value="idims"> gebucht (IDIMS)<br />';
		html += '	<select id="time_type_select" style="margin-bottom: 5px; margin-top: 5px; margin-left: 5px;">';
		html += '		<option value="order" selected>Bestelleingang</option>';
		html += '		<option value="idims">gebucht (IDIMS)</option>';
		html += '	</select>';
		html += '</div>';
		
		//if(<?php echo $_SESSION["id_user"];?> == 49352)
		{
			html += '<div id="countries" style="border: solid; border-color: #E6E6E6; border-width: 1px; margin-top: 5px; padding-bottom: 5px;">';
			html += '<p style="background-color: #E6E6E6; font-weight: bold; padding: 5px;">Länderauswahl</p>';
			html += '<select id="countries_select" size="6" multiple style="margin-left: 5px; margin-top: 5px;min-width: 100px;">';
			html += '	<option value="0" selected>Alle</option>';
			for(a in shop_countries)
			{
				html += '<option value="' + a + '">' + shop_countries[a] + '</option>';
			}
			html += '</select>';
			html += '</div>';
		}
		
		/*html += '<div id="options" style="border: solid; border-width: 1px; border-color: #E6E6E6; margin-top: 5px; padding-bottom: 5px">';
		html += '	<p style="font-weight: bold; background-color: #E6E6E6; padding: 5px">Optionen</p>';
		html += '	</p><input type="checkbox" id="show_zero_not" value="0" style="margin-top: 5px; margin-left: 5px"> Keine Zeilen mit Anzahl <p style="font-weight: bold; display: inline">0</p> anzeigen'
		html += '</div>';*/
		
		$("#statistics_settings").html(html);
		
		list_sort('countries_select', 1);
		
		$('#countries_select').change(function(){
			$('#countries_select option:selected').each(function(){
				if($(this).val()==0)
				{
					$('#countries_select option').attr('selected',false);
					$("#countries_select option[value='0']").attr('selected',true);
				}				
			});
		});
		
		$("#statistic_show_button").click(function() {
			statistic_show(shop_data, category_data, shop_countries);
		});
		$("#comp_clear_button").click(function() {
			comp_clear();
		});
		
		//list_sort("user_list_select", 1);
		
		$("#date_from").datepicker($.datepicker.regional['de']);
		$("#date_to").datepicker($.datepicker.regional['de']);
		$("#date_comp_from").datepicker($.datepicker.regional['de']);
		$("#date_comp_to").datepicker($.datepicker.regional['de']);

		$("#date_from").datepicker("option", "onClose", function(selectedDate)
											 {
												 $("#date_to" ).datepicker( "option", "minDate", selectedDate);
											 }
		);
		$("#date_to").datepicker("option", "onClose", function(selectedDate)
											 {
												 $("#date_from" ).datepicker( "option", "maxDate", selectedDate);
											 }
		);
		$("#date_comp_from").datepicker("option", "onClose", function(selectedDate)
											 {
												 $("#date_comp_to" ).datepicker( "option", "minDate", selectedDate);
											 }
		);
		$("#date_comp_to").datepicker("option", "onClose", function(selectedDate)
											 {
												 $("#date_comp_from" ).datepicker( "option", "maxDate", selectedDate);
											 }
		);
		$("#date_from" ).datepicker( "setDate", new Date());
		$("#date_to" ).datepicker( "setDate", new Date());
		var date=new Date();
		var date = new Date();
		date.setDate(date.getDate() - 7);
		$("#date_comp_from" ).datepicker( "setDate", date);
		$("#date_comp_to").datepicker( "setDate", date);
		$("#date_comp_to_hour").val(date.getHours());
		$("#date_comp_to_min").val(date.getMinutes());
		
		$("#date_to" ).datepicker( "option", "minDate", new Date());
		$("#date_from" ).datepicker( "option", "maxDate", new Date());
	}
	
	function shop_categories_load(shop_data, shop_countries)
	{
		var category_data = new Array();
		
		$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "ShopCategoriesGet"},
			function (data)
			{
				//show_status2(data);
				var $xml = $($.parseXML(data));
				var $ack = $xml.find("Ack");
				if ( $ack.text()=="Success" )
				{
					var cnt = 0;
					$xml.find("group").each(
						function()
						{
							category_data[cnt] = new Array;
							category_data[cnt]["type"] = $(this).find("type").text();
							category_data[cnt]["title"] = $(this).find("title").text();
							category_data[cnt]["menuitem_id"] = $(this).find("menuitem_id").text();
							category_data[cnt]["id_menuitem"] = $(this).find("id_menuitem").text();
							cnt = cnt + 1;
							/*category_data[$(this).find("id_menuitem").text()] = new Array;
							category_data[$(this).find("id_menuitem").text()]["type"] = $(this).find("type").text();
							category_data[$(this).find("id_menuitem").text()]["title"] = $(this).find("title").text();
							category_data[$(this).find("id_menuitem").text()]["menuitem_id"] = $(this).find("menuitem_id").text();*/
						}
					);
					settings_init(shop_data, category_data, shop_countries);
				}
			}
		);
	}
	
	function shops_load()
	{
		var shop_data = new Array();
		
		//$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "Get_Shop_Shops", mode: "all"},
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "Get_Shop_Shops", mode: "user"},
			function (data)
			{
				//show_status2(data);
				var $xml = $($.parseXML(data));
				var $ack = $xml.find("Ack");
				if ( $ack.text()=="Success" )
				{
					$xml.find("Shop_Shop").each(
						function()
						{
							//alert($(this).find("id_shop").text());
							shop_data[$(this).find("id_shop").text()] = new Array;
							shop_data[$(this).find("id_shop").text()]["title"] = $(this).find("title").text();
							shop_data[$(this).find("id_shop").text()]["shop_type"] = $(this).find("shop_type").text();
							shop_data[$(this).find("id_shop").text()]["account_id"] = $(this).find("account_id").text();
							shop_data[$(this).find("id_shop").text()]["parent_shop_id"] = $(this).find("parent_shop_id").text();
						}
					);
					//Länderdaten laden
					var shop_countries = new Array();
					$.post("<?php echo PATH;?>soa/", {API: "shop", Action: "CountriesGet"}, function($data){
						var $xml = $($.parseXML($data));
						var $ack = $xml.find("Ack");
						if ( $ack.text()=="Success" )
						{
							$xml.find('shop_countries').each(function(){
								shop_countries[$(this).find('id_country').text()] = $(this).find('country').text();
							});
							//show_status2(print_r(shop_countries));
							shop_categories_load(shop_data, shop_countries);
						}
					});
				}
			}
		);
	}
	
	function show_message_dialog(message)
	{
		$("#message").html(message);
		$("#message").dialog
		({	buttons:
			[
				{ text: "Ok", click: function() {$(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Achtung!",
			width:300
		});
	}
	
	/*function sortTitle(a,b) {
		 var index = 0;
		 if (a["title"] == b["title"]) index = 0;
		 else if (a["title"] > b["title"]) index = 1;
		 else if (a["title"] < b["title"]) index = -1;
		 return index;
		}*/
	
	function statistic_show(shop_data, category_data, shop_countries)
	{
		//show_status2(print_r(lists_data));
		//category_data.sort(sortTitle);
		category_data.sort(createSorter('title'));
		//show_status2(print_r(category_data));
		$("#statistics_show").empty().append($('<p style="font-size: 20px; font-weight: bold">Bitte warten...Statistik wird berechnet...</p>'));
		//settings auslesen
		//var time_type =	$("input[name='time_type_radiobutton']:checked").val();
		var time_type = $('#time_type_select option:selected').val();
		
		var date_from = 		new Date();
		date_from = 			$("#date_from").datepicker("getDate");
		if(date_from!=null)
		{
			date_from.setHours(0);
			date_from.setMinutes(0);
			date_from.setSeconds(1);
			date_from = 		(date_from.getTime()/1000).toFixed(0);
		}
		var date_to =			new Date();
		date_to = 				$("#date_to").datepicker("getDate");
		if(date_to!=null)
		{
			date_to.setHours(23);
			date_to.setMinutes(59);
			date_to.setSeconds(59);
			date_to = 			(date_to.getTime()/1000).toFixed(0);
		}
		var date_comp_from =	new Date();
		date_comp_from = 		$("#date_comp_from").datepicker("getDate");
		if(date_comp_from!=null)
		{
			date_comp_from.setHours(0);
			date_comp_from.setMinutes(0);
			date_comp_from.setSeconds(1);
			date_comp_from = 		(date_comp_from.getTime()/1000).toFixed(0);
		}
		var date_comp_to =		new Date();
		date_comp_to = 			$("#date_comp_to").datepicker("getDate");
		if(date_comp_to!=null)
		{
			var date_comp_to_hour=$("#date_comp_to_hour").val();
			if( date_comp_to_hour!="" ) date_comp_to.setHours(date_comp_to_hour);
			else date_comp_to.setHours(23);
			var date_comp_to_min=$("#date_comp_to_min").val();
			if( date_comp_to_min!="" ) date_comp_to.setMinutes(date_comp_to_min);
			else date_comp_to.setMinutes(59);
			date_comp_to.setSeconds(59);
			date_comp_to = 			(date_comp_to.getTime()/1000).toFixed(0);
		}
		
		var shops_selected = new Array();
		var shopcnt = 0;
		$('#shops_select option:selected').each(function(){
			shops_selected[shopcnt] = $(this).val();
			shopcnt += 1;
		});
		
		var shop_countries = new Array();
		var countriescnt = 0;
		$('#countries_select option:selected').each(function(){
			shop_countries[countriescnt] = $(this).val();
			countriescnt++;
		});
		//show_status2(print_r(shop_countries));
/*		
		for(a in shop_data)
		{
			if($("#" + a).is(":checked"))
			{
				shops_selected[shopcnt] = 	$("#" + a).val();
				shopcnt += 1;
			}
		}
*/		
		if(shopcnt==0)
		{
			show_message_dialog("Bitte shop(s) auswählen.");
			$("#statistics_show").empty();
			return;
		}
		
		var list_select = $('#list_select').val();
			
		if(date_from==null && date_to==null)
		{
			show_message_dialog("Bitte einen Berechnungszeitraum auswählen.");
			$("#statistics_show").empty();
			return;
		}
		else if(date_from==null && date_to!=null)
		{
			show_message_dialog("Bitte eine Beginndatum auswählen.");
			$("#statistics_show").empty();
			return;
		}
		else if(date_from!=null && date_to==null)
		{
			show_message_dialog("Bitte ein Enddatum auswählen.");
			$("#statistics_show").empty();
			return;
		}
		
		if((date_comp_from==null && date_comp_to!=null) || (date_comp_from!=null && date_comp_to==null))
		{
			show_message_dialog("Bitte beide Vergleichszeitraumfelder ausfüllen oder beide leer lassen.");
			$("#statistics_show").empty();
			return;
		}
		if(date_comp_from!=null && date_comp_to!=null)
		{
			var mode = "comp";
		}
		else
		{
			var mode = "single";
		}
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "StatisticItemsGet",
																		mode:				mode,
																		time_type:			time_type,
																		date_from:			date_from,
																		date_to:			date_to,
																		date_comp_from:		date_comp_from,
																		date_comp_to:		date_comp_to,
																		'shops_selected[]':	shops_selected,
																		'shop_countries[]': shop_countries},
			function (data)
			{
				//wait_dialog_hide();
				//if(<?php echo $_SESSION["id_user"];?> == 49352)
					//show_status2(data);
				//alert(data);
				var $xml = $($.parseXML(data));
				var $ack = $xml.find("Ack");
				if ( $ack.text()=="Success" )
				{
					var cnt = 0;
					var item_data = new Array();
					var gart_data = new Array();
					var gart_index = new Array();
					
					var order_sums = new Array();
					order_sums["all"] = $xml.find("orders_number_single").text();
					order_sums["all_comp"] = $xml.find("orders_number_comp").text();
					for(a in shops_selected)
					{
						 order_sums[shops_selected[a]] = $xml.find("orders_number_" + shops_selected[a] + "_single").text();
						 order_sums[shops_selected[a] + "_comp"] = $xml.find("orders_number_" + shops_selected[a] + "_comp").text();
					}
					//show_status2(print_r(order_sums));
					
					$xml.find("item").each(
						function()
						{
							item_data[cnt] = 							new Array();
							item_data[cnt]["order_id"] = 				$(this).find("order_id").text();
							item_data[cnt]["item_id"] = 				$(this).find("item_id").text();
							item_data[cnt]["customer_id"] = 			$(this).find("customer_id").text();
							item_data[cnt]["MPN"] = 					$(this).find("MPN").text();
							item_data[cnt]["GART"] = 					$(this).find("GART").text();
							item_data[cnt]["keyword"] = 				$(this).find("keyword").text();
							item_data[cnt]["menuitem_id"] = 			$(this).find("menuitem_id").text();
							item_data[cnt]["title"] = 					$(this).find("title").text();
							item_data[cnt]["shop_id"] = 				$(this).find("shop_id").text();
							item_data[cnt]["amount"] = 					$(this).find("amount").text();
							item_data[cnt]["netto"] = 					$(this).find("netto").text();
							item_data[cnt]["Currency_Code"] = 			$(this).find("Currency_Code").text();
							item_data[cnt]["exchange_rate_to_EUR"] = 	$(this).find("exchange_rate_to_EUR").text();
							item_data[cnt]["time_range"] = 				$(this).find("time_range").text();
							item_data[cnt]["country_id"] =				$(this).find("country_id").text();
							cnt = cnt + 1;
							//alert($.inArray((item_data[cnt-1]["GART"]), gart_data));
							if($.inArray(item_data[cnt-1]["GART"], gart_index)==-1)
							{
								gart_index.push(item_data[cnt-1]["GART"]);
								gart_data.push({"GART" : item_data[cnt-1]["GART"], "keyword" : item_data[cnt-1]["keyword"]});
							}
						}
					);
					gart_data.sort(createSorter('keyword'));
					//show_status2(print_r(item_data));
					/*if(mode=="comp")
					{
						show_status2(print_r(item_data));
					}*/
					wait_dialog_hide();
					//if(list_select_radiobutton=="item_list")
					if(list_select=="item_list")
					{
						//"summiertes" Array zusammenbauen
						var shop_item_data = new Array();
						shop_item_data["netto_all"] = 		0;
						shop_item_data["amount_all"] = 		0;
						shop_item_data["netto_comp_all"] = 	0;
						shop_item_data["amount_comp_all"] = 0;
						shop_item_data["shops"] = 			new Array();
						for(var a in shops_selected)
						{
							shop_item_data["shops"][shops_selected[a]] = 						new Array();
							shop_item_data["shops"][shops_selected[a]]["shop_name"] = 			shop_data[shops_selected[a]]["title"];
							shop_item_data["shops"][shops_selected[a]]["netto_shop"] = 			0;
							shop_item_data["shops"][shops_selected[a]]["amount_shop"] = 		0;
							shop_item_data["shops"][shops_selected[a]]["netto_comp_shop"] = 	0;
							shop_item_data["shops"][shops_selected[a]]["amount_comp_shop"] =	0;
							shop_item_data["shops"][shops_selected[a]]["items"] = 				new Array();
						}
						var item_id_array = new Array();
						for(var a = 0; a<item_data.length; a++)
						{
							var netto = 0;
							if(item_data[a]["Currency_Code"]=="EUR")
							{
								netto = item_data[a]["netto"];
							}
							else
							{
								netto = (Math.round((item_data[a]["netto"]/item_data[a]["exchange_rate_to_EUR"])*100)/100);
							}
							
							for(var b = 0; b<shops_selected.length; b++)
							{
								if(typeof shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]] !== 'undefined' && item_data[a]["shop_id"]==shops_selected[b])
								{
									if(item_data[a]["time_range"]=="single")
									{
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["amount_item"] = shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["amount_item"]*1 + (item_data[a]["amount"]*1);
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["netto_item"] = (Math.round((shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["netto_item"]*1 + ((item_data[a]["amount"]*netto)*1))*100)/100).toFixed(2);
										
										shop_item_data["shops"][item_data[a]["shop_id"]]["netto_shop"] = (Math.round((shop_item_data["shops"][item_data[a]["shop_id"]]["netto_shop"]*1 + (item_data[a]["amount"]*netto))*100)/100).toFixed(2);
										shop_item_data["shops"][item_data[a]["shop_id"]]["amount_shop"] = shop_item_data["shops"][item_data[a]["shop_id"]]["amount_shop"]*1 + (item_data[a]["amount"]*1);
										shop_item_data["netto_all"] = (Math.round((shop_item_data["netto_all"]*1 + (item_data[a]["amount"]*netto))*100)/100).toFixed(2);
										shop_item_data["amount_all"] = shop_item_data["amount_all"]*1 + (item_data[a]["amount"]*1);
									}
									else if(item_data[a]["time_range"]=="comp")
									{
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["amount_comp_item"] = shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["amount_comp_item"]*1 + (item_data[a]["amount"]*1);
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["netto_comp_item"] = (Math.round((shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["netto_comp_item"]*1 + ((item_data[a]["amount"]*netto)*1))*100)/100).toFixed(2);
										
										shop_item_data["shops"][item_data[a]["shop_id"]]["netto_comp_shop"] = (Math.round((shop_item_data["shops"][item_data[a]["shop_id"]]["netto_comp_shop"]*1 + (item_data[a]["amount"]*netto))*100)/100).toFixed(2);
										shop_item_data["shops"][item_data[a]["shop_id"]]["amount_comp_shop"] = shop_item_data["shops"][item_data[a]["shop_id"]]["amount_comp_shop"]*1 + (item_data[a]["amount"]*1);
										shop_item_data["netto_comp_all"] = (Math.round((shop_item_data["netto_comp_all"]*1 + (item_data[a]["amount"]*netto))*100)/100).toFixed(2);
										shop_item_data["amount_comp_all"] = shop_item_data["amount_comp_all"]*1 + (item_data[a]["amount"]*1);
	
									}
								}
								else if(typeof shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]] !== 'undefined' && item_data[a]["shop_id"]!=shops_selected[b])
								{
								}
								else if(typeof shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]] == 'undefined' && item_data[a]["shop_id"]==shops_selected[b])
								{
									shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]] = new Array();
									item_id_array[item_data[a]["item_id"]] = 1;
									shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["MPN"] = item_data[a]["MPN"];
									shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["menuitem_id"] = item_data[a]["menuitem_id"];
									shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["title"] = item_data[a]["title"];
									if(item_data[a]["time_range"]=="single")
									{
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["amount_item"] = item_data[a]["amount"];
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["netto_item"] = (Math.round((item_data[a]["amount"]*netto)*100)/100).toFixed(2);
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["amount_comp_item"] = 0;
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["netto_comp_item"] = 0;
										shop_item_data["shops"][item_data[a]["shop_id"]]["netto_shop"] = (Math.round((shop_item_data["shops"][item_data[a]["shop_id"]]["netto_shop"]*1 + (item_data[a]["amount"]*netto))*100)/100).toFixed(2);
										shop_item_data["shops"][item_data[a]["shop_id"]]["amount_shop"] = shop_item_data["shops"][item_data[a]["shop_id"]]["amount_shop"]*1 + (item_data[a]["amount"]*1);
										shop_item_data["netto_all"] = (Math.round((shop_item_data["netto_all"]*1 + (item_data[a]["amount"]*netto))*100)/100).toFixed(2);
										shop_item_data["amount_all"] = shop_item_data["amount_all"]*1 + (item_data[a]["amount"]*1);
									}
									else if(item_data[a]["time_range"]=="comp")
									{
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["amount_comp_item"] = item_data[a]["amount"];
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["netto_comp_item"] = (Math.round((item_data[a]["amount"]*netto)*100)/100).toFixed(2);
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["amount_item"] = 0;
										shop_item_data["shops"][item_data[a]["shop_id"]]["items"][item_data[a]["item_id"]]["netto_item"] = 0;
										shop_item_data["shops"][item_data[a]["shop_id"]]["netto_comp_shop"] = (Math.round((shop_item_data["shops"][item_data[a]["shop_id"]]["netto_comp_shop"]*1 + (item_data[a]["amount"]*netto))*100)/100).toFixed(2);
										shop_item_data["shops"][item_data[a]["shop_id"]]["amount_comp_shop"] = shop_item_data["shops"][item_data[a]["shop_id"]]["amount_comp_shop"]*1 + (item_data[a]["amount"]*1);
										shop_item_data["netto_comp_all"] = (Math.round((shop_item_data["netto_comp_all"]*1 + (item_data[a]["amount"]*netto))*100)/100).toFixed(2);
										shop_item_data["amount_comp_all"] = shop_item_data["amount_comp_all"]*1 + (item_data[a]["amount"]*1);
									}
								}
								else if(typeof shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]] == 'undefined' && item_data[a]["shop_id"]!=shops_selected[b])
								{
									shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]] = 						new Array();
									shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]]["MPN"] = 				item_data[a]["MPN"];
									shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]]["menuitem_id"] = 		item_data[a]["menuitem_id"];
									shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]]["title"] = 			item_data[a]["title"];
									shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]]["amount_item"] = 		0;
									shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]]["netto_item"] = 		0;
									shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]]["amount_comp_item"] = 	0;
									shop_item_data["shops"][shops_selected[b]]["items"][item_data[a]["item_id"]]["netto_comp_item"] = 	0;
								}
							}
						}
						//show_status2(print_r(shop_item_data));
						//show_status2(print_r(item_data));
						//alert(print_r(shops_selected));
						
						//Übersicht anzeigen
						$("#statistics_show").empty();
						//var table = $('<table class="hover" style="margin: 0px; width: 80%"></table>');
						var table = $('<table class="hover" style="margin: 0px;"></table>');						
						
						if(mode=="single")
							var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline"></p></caption>');
						else
							var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline">Vergleichszeitraum: ' + $("#date_comp_from").val() + ' - ' + $("#date_comp_to").val() + '</p></caption>');
						table.append(caption);
						
						var thead = $('<thead></thead>');
						var tr = $('<tr></tr>');
						var th = $('<th>Shop</th>');
						tr.append(th);
						th = $('<th>Anzahl Artikel</th>');
						tr.append(th);
						th = $('<th>Anzahl Bestellungen</th>');
						tr.append(th);
						th = $('<th>Gesamt Netto</th>');
						tr.append(th);
						if(mode=="comp")
						{
							th = $('<th style="background-color: #99FF99; border-color: #99FF99">Anzahl Artikel</th>');
							tr.append(th);
							th = $('<th style="background-color: #99FF99; border-color: #99FF99">Anzahl Bestellungen</th>');
							tr.append(th);
							th = $('<th style="background-color: #99FF99; border-color: #99FF99">Gesamt Netto</th>');
							tr.append(th);
						}
						thead.append(tr);
						table.append(thead);
						
						var tbody = $('<tbody></tbody>');
						var td;
						for(a in shop_data)
						{
							if(shop_data[a]["parent_shop_id"]==0)
							{
								for(c in shops_selected)
								{
									if(a==shops_selected[c])
									{
										tr = $('<tr></tr>');
										td = $('<td>' + shop_data[a]["title"] + '</td>');
										tr.append(td);
										td = $('<td style="text-align: right">' + shop_item_data["shops"][a]["amount_shop"] + '</td>');
										tr.append(td);
										td = $('<td style="text-align: right">' + order_sums[shops_selected[c]] + '</td>');
										tr.append(td);
										td = $('<td style="text-align: right">' + shop_item_data["shops"][a]["netto_shop"] + ' €</td>');
										tr.append(td);
										if(mode=="comp")
										{
											td = $('<td style="text-align: right">' + shop_item_data["shops"][a]["amount_comp_shop"] + '</td>');
											tr.append(td);
											td = $('<td style="text-align: right">' + order_sums[shops_selected[c] + "_comp"] + '</td>');
											tr.append(td);
											td = $('<td style="text-align: right">' + shop_item_data["shops"][a]["netto_comp_shop"] + ' €</td>');
											tr.append(td);
										}
										tbody.append(tr);
									}
								}
							}
							for(b in shop_data)
							{
								if(shop_data[b]["parent_shop_id"]==a)
								{
									for(d in shops_selected)
									{
										if(b==shops_selected[d])
										{
											tr = $('<tr></tr>');
											td = $('<td><p style="display: inline; margin-left: 10px">' + shop_data[b]["title"] + '</p></td>');
											tr.append(td);
											td = $('<td style="text-align: right">' + shop_item_data["shops"][b]["amount_shop"] + '</td>');
											tr.append(td);
											td = $('<td style="text-align: right">' + order_sums[shops_selected[d]] + '</td>');
											tr.append(td);
											td = $('<td style="text-align: right">' + shop_item_data["shops"][b]["netto_shop"] + ' €</td>');
											tr.append(td);
											if(mode=="comp")
											{
												td = $('<td style="text-align: right">' + shop_item_data["shops"][b]["amount_comp_shop"] + '</td>');
												tr.append(td);
												td = $('<td style="text-align: right">' + order_sums[shops_selected[d] + "_comp"] + '</td>');
												tr.append(td);
												td = $('<td style="text-align: right">' + shop_item_data["shops"][b]["netto_comp_shop"] + ' €</td>');
												tr.append(td);
											}
											tbody.append(tr);
										}
									}
								}
							}
						}
						table.append(tbody);
						
						var tfoot = $('<tfoot></tfoot>');
						tr = $('<tr></tr>');
						td = $('<td style="border: none"></td>');
						tr.append(td);
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["amount_all"] + '</td>');
						tr.append(td);
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + order_sums["all"] + '</td>');
						tr.append(td);
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["netto_all"] + ' €</td>');
						tr.append(td);
						if(mode=="comp")
						{
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["amount_comp_all"] + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + order_sums["all_comp"] + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_item_data["netto_comp_all"] + ' €</td>');
							tr.append(td);
						}
						tfoot.append(tr);
						table.append(tfoot);
						
						$("#statistics_show").append(table);
						var detail_button = $('<input type="button" id="detail_button" value="Details anzeigen" style="margin-top: 10px"><br />');
						$("#statistics_show").append(detail_button);
						var carriage_button = $('<input type="button" id="carriage_button" value="Frachtkosten berechnen" style="margin-top: 10px"><br />');
						$('#statistics_show').append(carriage_button);
						
						$("#detail_button").click(function() {
							$("#statistics_show").empty().append($('<p style="font-size: 20px; font-weight: bold">Bitte warten...Tabelle wird erzeugt...</p>'));
							setTimeout(function(){
							  item_table_show(shop_data, shop_item_data, shops_selected, mode, shopcnt, item_id_array);
							},10);
						});
						
						$('#carriage_button').click(function(){
							$('#carriage_button').hide();
							carriage_table_show(shop_data, shop_item_data, shops_selected, mode, shopcnt, item_id_array, time_type, date_from, date_to, date_comp_from, date_comp_to, shop_countries);
						});
					}
					//else if(list_select_radiobutton=="item_gart")
					else if(list_select=="item_gart")
					{
						var main = $("#statistics_show");
						main.empty();
						
						var row = $('<p style="font-weight: bold; font-size: 15px; display: inline; margin-right: 20px">generische Artikelgruppe auswählen:</p>');
						main.append(row);
						
						var sel = $('<select id="gart_select" style="width: 300px"></select>');
						row = $('<option value="">bitte auswählen</option><br>');
						sel.append(row);
						for(a in gart_data)
						{
							if(gart_data[a]["keyword"]=="")
								var keyword = "unbekannt";
							else
								var keyword = gart_data[a]["keyword"];
							row = $('<option value="' + gart_data[a]["GART"] + '">' + keyword + ' (' + gart_data[a]["GART"] + ')</option><br>');
							sel.append(row);
						}
						main.append(sel);
						
						//var detail_button = $('<input type="button" id="detail_button" value="Details anzeigen" style="margin-top: 10px; margin-left: 20px">');
						//main.append(detail_button);
						
						var table_div = $('<div id="table_div" style="margin-top: 10px"></div>');
						main.append(table_div);
						
						$('#gart_select').on('change', function() {
							if($("#gart_select").val()!="")
							{
								var gart = $("#gart_select").val();
								keyword = $("#gart_select :selected").text();
								//main.empty().append($('<p style="font-size: 20px; font-weight: bold">Bitte warten...Tabelle wird erzeugt...</p>'));
								$('#table_div').empty().append($('<p style="font-size: 20px; font-weight: bold">Bitte warten...Tabelle wird erzeugt...</p>'));
								setTimeout(function(){
									gart_table_show(shop_data, item_data, shops_selected, mode, shopcnt, gart, keyword, gart_data);
								},10);
							}
							else
								$('#table_div').empty();
						});
						
						/*$("#detail_button").click(function() {
							var gart = $("#gart_select").val();
							keyword = $("#gart_select :selected").text();
							//main.empty().append($('<p style="font-size: 20px; font-weight: bold">Bitte warten...Tabelle wird erzeugt...</p>'));
							$('#table_div').empty().append($('<p style="font-size: 20px; font-weight: bold">Bitte warten...Tabelle wird erzeugt...</p>'));
							setTimeout(function(){
							  	gart_table_show(shop_data, item_data, shops_selected, mode, shopcnt, gart, keyword, gart_data);
							},10);
						});*/
					}
					//else if(list_select_radiobutton=="item_group")
					else if(list_select=="item_group")
					{
						//shop_group_data zusammenbauen
						var shop_group_data = new Array();
						shop_group_data["amount_all"] = 		0;
						shop_group_data["netto_all"] = 			0;
						shop_group_data["amount_comp_all"] = 	0;
						shop_group_data["netto_comp_all"] =  	0;
						for(var a=0; a<shopcnt; a++)
						{
							shop_group_data["amount_all_" + shops_selected[a]] = 		0;
							shop_group_data["netto_all_" + shops_selected[a]] = 		0;
							shop_group_data["amount_comp_all_" + shops_selected[a]] = 	0;
							shop_group_data["netto_comp_all_" + shops_selected[a]] =  	0;
						}
						shop_group_data["main_groups"] = new Array();
						
						for(a in category_data)
						{
							if(category_data[a]["type"]=="main_group")
							{
								shop_group_data["main_groups"][category_data[a]["id_menuitem"]] = 							new Array();
								shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["amount_main_group"] = 		0;
								shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["netto_main_group"] = 		0;
								shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["amount_comp_main_group"] = 0;
								shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["netto_comp_main_group"] = 	0;
								shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"] = 			new Array();
								for(var c=0; c<shopcnt; c++)
								{
									shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["amount_main_group_" + shops_selected[c]] = 		0;
									shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["netto_main_group_" + shops_selected[c]] = 			0;
									shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["amount_comp_main_group_" + shops_selected[c]] = 	0;
									shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["netto_comp_main_group_" + shops_selected[c]] = 	0;
								}
								for(b in category_data)
								{
									if(category_data[b]["menuitem_id"]==category_data[a]["id_menuitem"])
									{
										shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]] = new Array();
										shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["amount_sub_group"] = 		0;
										shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["netto_sub_group"] = 		0;
										shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["amount_comp_sub_group"] = 	0;
										shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["netto_comp_sub_group"] = 	0;
										for(var c=0; c<shopcnt; c++)
										{
											shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["amount_sub_group_" + shops_selected[c]] = 	0;
											shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["netto_sub_group_" + shops_selected[c]] = 	0;
											shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["amount_comp_sub_group_" + shops_selected[c]] = 	0;
											shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["netto_comp_sub_group_" + shops_selected[c]] = 	0;
										}
										shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["items"] = new Array();
									}
								}
							}
						}
						
						for(a=0; a<item_data.length; a++)
						{
							for(b in category_data)
							{
								if(category_data[b]["type"]=="sub_group"  && item_data[a]["menuitem_id"]==category_data[b]["id_menuitem"])
								{
									item_data[a]["main_group"] = category_data[b]["menuitem_id"];
								}
							}
							
							var netto = 0;
							if(item_data[a]["Currency_Code"]=="EUR")
							{
								netto = item_data[a]["netto"];
							}
							else
							{
								netto = (Math.round((item_data[a]["netto"]/item_data[a]["exchange_rate_to_EUR"])*100)/100).toFixed(2);
							}
							//alert(print_r(item_data[a]));
							
							if(item_data[a]["menuitem_id"]!=0)
							{
								var sub_group = shop_group_data["main_groups"][item_data[a]["main_group"]]["sub_groups"][item_data[a]["menuitem_id"]];
								var main_group = shop_group_data["main_groups"][item_data[a]["main_group"]];
								
								if(typeof shop_group_data["main_groups"][item_data[a]["main_group"]]["sub_groups"][item_data[a]["menuitem_id"]]["items"][item_data[a]["item_id"]]=='undefined') // neu anlegen
								{
									shop_group_data["main_groups"][item_data[a]["main_group"]]["sub_groups"][item_data[a]["menuitem_id"]]["items"][item_data[a]["item_id"]] = new Array();
									var c = shop_group_data["main_groups"][item_data[a]["main_group"]]["sub_groups"][item_data[a]["menuitem_id"]]["items"][item_data[a]["item_id"]];
			
									c["item_id"] = item_data[a]["item_id"];
									c["title"] = item_data[a]["title"];
									if(item_data[a]["time_range"]=="single")
									{
										c["amount_item"] = 		item_data[a]["amount"];
										c["netto_item"] = 		netto*1*item_data[a]["amount"];
										c["amount_comp_item"] = 0;
										c["netto_comp_item"] = 	0;
										for(var d=0; d<shopcnt; d++)
										{
											if(shops_selected[d]==item_data[a]["shop_id"])
											{
												c["amount_item_" + shops_selected[d]] = 		item_data[a]["amount"];
												c["netto_item_" + shops_selected[d]] = 			netto*1*item_data[a]["amount"];
												c["amount_comp_item_" + shops_selected[d]] = 	0;
												c["netto_comp_item_" + shops_selected[d]] = 	0;
												//Summierungen
												sub_group["amount_sub_group_" + shops_selected[d]] = 	sub_group["amount_sub_group_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												sub_group["netto_sub_group_" + shops_selected[d]] = 	sub_group["netto_sub_group_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												sub_group["amount_sub_group"] = 						sub_group["amount_sub_group"]*1 + item_data[a]["amount"]*1;
												sub_group["netto_sub_group"] = 							sub_group["netto_sub_group"]*1 + netto*item_data[a]["amount"]*1; 
												main_group["amount_main_group_" + shops_selected[d]] = 	main_group["amount_main_group_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												main_group["netto_main_group_" + shops_selected[d]] = 	main_group["netto_main_group_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												main_group["amount_main_group"] = 						main_group["amount_main_group"]*1 + item_data[a]["amount"]*1;
												main_group["netto_main_group"] = 						main_group["netto_main_group"]*1 + netto*item_data[a]["amount"]*1;
												shop_group_data["amount_all_" + shops_selected[d]] = 	shop_group_data["amount_all_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												shop_group_data["netto_all_" + shops_selected[d]] = 	shop_group_data["netto_all_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												shop_group_data["amount_all"] = 						shop_group_data["amount_all"]*1 + item_data[a]["amount"]*1;
												shop_group_data["netto_all"] = 							shop_group_data["netto_all"]*1 + netto*item_data[a]["amount"]*1;
											}
											else
											{
												c["amount_item_" + shops_selected[d]] = 		0;
												c["netto_item_" + shops_selected[d]] = 			0;
												c["amount_comp_item_" + shops_selected[d]] = 	0;
												c["netto_comp_item_" + shops_selected[d]] = 	0;
											}
										}
									}
									else if(item_data[a]["time_range"]=="comp")
									{
										c["amount_item"] = 		0;
										c["netto_item"] = 		0;
										c["amount_comp_item"] = item_data[a]["amount"];
										c["netto_comp_item"] = 	netto*1*item_data[a]["amount"];
										for(var d=0; d<shopcnt; d++)
										{
											if(shops_selected[d]==item_data[a]["shop_id"])
											{
												c["amount_item_" + shops_selected[d]] = 		0;
												c["netto_item_" + shops_selected[d]] = 			0;
												c["amount_comp_item_" + shops_selected[d]] = 	item_data[a]["amount"];
												c["netto_comp_item_" + shops_selected[d]] = 	netto*1*item_data[a]["amount"];
												//Summierungen
												sub_group["amount_comp_sub_group_" + shops_selected[d]] = 	sub_group["amount_comp_sub_group_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												sub_group["netto_comp_sub_group_" + shops_selected[d]] = 	sub_group["netto_comp_sub_group_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												sub_group["amount_comp_sub_group"] = 						sub_group["amount_comp_sub_group"]*1 + item_data[a]["amount"]*1;
												sub_group["netto_comp_sub_group"] = 						sub_group["netto_comp_sub_group"]*1 + netto*item_data[a]["amount"]*1; 
												main_group["amount_comp_main_group_" + shops_selected[d]] = main_group["amount_comp_main_group_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												main_group["netto_comp_main_group_" + shops_selected[d]] = 	main_group["netto_comp_main_group_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												main_group["amount_comp_main_group"] = 						main_group["amount_comp_main_group"]*1 + item_data[a]["amount"]*1;
												main_group["netto_comp_main_group"] = 						main_group["netto_comp_main_group"]*1 + netto*item_data[a]["amount"]*1;
												shop_group_data["amount_comp_all_" + shops_selected[d]] = 	shop_group_data["amount_comp_all_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												shop_group_data["netto_comp_all_" + shops_selected[d]] = 	shop_group_data["netto_comp_all_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												shop_group_data["amount_comp_all"] = 						shop_group_data["amount_comp_all"]*1 + item_data[a]["amount"]*1;
												shop_group_data["netto_comp_all"] = 						shop_group_data["netto_comp_all"]*1 + netto*item_data[a]["amount"]*1;
											}
											else
											{
												c["amount_item_" + shops_selected[d]] = 		0;
												c["netto_item_" + shops_selected[d]] = 			0;
												c["amount_comp_item_" + shops_selected[d]] = 	0;
												c["netto_comp_item_" + shops_selected[d]] = 	0;
											}
										}
									}
								}
								else //vorhandene ergänzen
								{
									var c = shop_group_data["main_groups"][item_data[a]["main_group"]]["sub_groups"][item_data[a]["menuitem_id"]]["items"][item_data[a]["item_id"]];
			
									if(item_data[a]["time_range"]=="single")
									{
										c["amount_item"] = 		c["amount_item"]*1 + item_data[a]["amount"]*1;
										c["netto_item"] = 		c["netto_item"]*1 + netto*1*item_data[a]["amount"];
										for(var d=0; d<shopcnt; d++)
										{
											if(shops_selected[d]==item_data[a]["shop_id"])
											{
												c["amount_item_" + shops_selected[d]] = 		c["amount_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												c["netto_item_" + shops_selected[d]] = 			c["netto_item_" + shops_selected[d]]*1 + netto*1*item_data[a]["amount"];
												//Summierungen
												sub_group["amount_sub_group_" + shops_selected[d]] = 	sub_group["amount_sub_group_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												sub_group["netto_sub_group_" + shops_selected[d]] = 	sub_group["netto_sub_group_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												sub_group["amount_sub_group"] = 						sub_group["amount_sub_group"]*1 + item_data[a]["amount"]*1;
												sub_group["netto_sub_group"] = 							sub_group["netto_sub_group"]*1 + netto*item_data[a]["amount"]*1; 
												main_group["amount_main_group_" + shops_selected[d]] = 	main_group["amount_main_group_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												main_group["netto_main_group_" + shops_selected[d]] = 	main_group["netto_main_group_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												main_group["amount_main_group"] = 						main_group["amount_main_group"]*1 + item_data[a]["amount"]*1;
												main_group["netto_main_group"] = 						main_group["netto_main_group"]*1 + netto*item_data[a]["amount"]*1;
												shop_group_data["amount_all_" + shops_selected[d]] = 	shop_group_data["amount_all_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												shop_group_data["netto_all_" + shops_selected[d]] = 	shop_group_data["netto_all_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												shop_group_data["amount_all"] = 						shop_group_data["amount_all"]*1 + item_data[a]["amount"]*1;
												shop_group_data["netto_all"] = 							shop_group_data["netto_all"]*1 + netto*item_data[a]["amount"]*1;
											}
										}
									}
									else if(item_data[a]["time_range"]=="comp")
									{
										c["amount_comp_item"] = c["amount_comp_item"]*1 + item_data[a]["amount"]*1;
										c["netto_comp_item"] = c["netto_comp_item"]*1 + netto*1*item_data[a]["amount"];
										for(var d=0; d<shopcnt; d++)
										{
											if(shops_selected[d]==item_data[a]["shop_id"])
											{
												c["amount_comp_item_" + shops_selected[d]] = 	c["amount_comp_item_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												c["netto_comp_item_" + shops_selected[d]] = 	c["netto_comp_item_" + shops_selected[d]]*1 + netto*1*item_data[a]["amount"];
												//Summierungen
												sub_group["amount_comp_sub_group_" + shops_selected[d]] = 	sub_group["amount_comp_sub_group_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												sub_group["netto_comp_sub_group_" + shops_selected[d]] = 	sub_group["netto_comp_sub_group_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												sub_group["amount_comp_sub_group"] = 						sub_group["amount_comp_sub_group"]*1 + item_data[a]["amount"]*1;
												sub_group["netto_comp_sub_group"] = 						sub_group["netto_comp_sub_group"]*1 + netto*item_data[a]["amount"]*1; 
												main_group["amount_comp_main_group_" + shops_selected[d]] = main_group["amount_comp_main_group_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												main_group["netto_comp_main_group_" + shops_selected[d]] = 	main_group["netto_comp_main_group_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												main_group["amount_comp_main_group"] = 						main_group["amount_comp_main_group"]*1 + item_data[a]["amount"]*1;
												main_group["netto_comp_main_group"] = 						main_group["netto_comp_main_group"]*1 + netto*item_data[a]["amount"]*1;
												shop_group_data["amount_comp_all_" + shops_selected[d]] = 	shop_group_data["amount_comp_all_" + shops_selected[d]]*1 + item_data[a]["amount"]*1;
												shop_group_data["netto_comp_all_" + shops_selected[d]] = 	shop_group_data["netto_comp_all_" + shops_selected[d]]*1 + netto*item_data[a]["amount"]*1;
												shop_group_data["amount_comp_all"] = 						shop_group_data["amount_comp_all"]*1 + item_data[a]["amount"]*1;
												shop_group_data["netto_comp_all"] = 						shop_group_data["netto_comp_all"]*1 + netto*item_data[a]["amount"]*1;
											}
										}
									}
								}
							}
						}
						
						//show_status2(print_r(shop_group_data));
						
						//Tabelle zusammenbauen
						var table = $('<table class="hover" style="margin: 0px"></table>');
						if(mode=="single")
							var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline"></p></caption>');
						else
							var caption = $('<caption style="text-align: left; font-weight: bold"><p style="background-color: #CCCCCC; display: inline">Zeitraum: ' + $("#date_from").val() + ' - ' + $("#date_to").val() + '</p><p style="background-color: #99FF99; display: inline">Vergleichszeitraum: ' + $("#date_comp_from").val() + ' - ' + $("#date_comp_to").val() + '</p></caption>');
						table.append(caption);
						
						var thead = $('<thead></thead>');
						var tr = $('<tr></tr>');
						var th = $('<th>Artikelgruppen/-untergruppen</th>');
						tr.append(th);
						for(a in shops_selected)
						{
							th = $('<th>Anz.</th>');
							tr.append(th);
							th = $('<th colspan="2" style="max-width: 100px">' + shop_data[shops_selected[a]]["title"] + '</th>');
							tr.append(th);
						}
						th = $('<th>Anz. gesamt</th>');
						tr.append(th);
						th = $('<th colspan="2" style="max-width: 100px">Gesamt Netto</th>');
						tr.append(th);
						if(mode=="comp")
						{
							for(a in shops_selected)
							{
								th = $('<th style="background-color: #99FF99; border: none">Anz.</th>');
								tr.append(th);
								th = $('<th colspan="2" style="background-color: #99FF99; border: none; max-width: 100px">' + shop_data[shops_selected[a]]["title"] + '</th>');
								tr.append(th);
							}
							th = $('<th style="background-color: #99FF99; border: none">Anz. gesamt</th>');
							tr.append(th);
							th = $('<th colspan="2" style="background-color: #99FF99; border: none; max-width: 100px">Gesamt Netto</th>');
							tr.append(th);
						}
						thead.append(tr);
						table.append(thead);
						
						var tbody = $('<tbody></tbody>');
						tr = $('<tr></tr>');
						var td;
						for(a in category_data)
						{
							if(category_data[a]["type"]=="main_group")
							{
								tr = $('<tr id="' + category_data[a]["id_menuitem"] + '" style="cursor: pointer"></tr>');
								td = $('<td style="background-color: #DDDDDD; font-weight: bold">' + category_data[a]["title"] + '</td>');
								tr.append(td)
								for(c in shops_selected)
								{
									td = $('<td style="background-color: #DDDDDD; font-weight: bold; text-align: right">' + shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["amount_main_group_" + shops_selected[c]] + '</td>');
									tr.append(td);
									td = $('<td style="background-color: #DDDDDD; font-weight: bold; border-right: none; text-align: right">' + (Math.round((shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["netto_main_group_" + shops_selected[c]])*100)/100/1).toFixed(2) + '</td>');
									tr.append(td);
									td = $('<td style="background-color: #DDDDDD; font-weight: bold; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
									tr.append(td);
								}
								td = $('<td style="background-color: #DDDDDD; font-weight: bold; text-align: right">' + shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["amount_main_group"] + '</td>');
								tr.append(td);
								td = $('<td style="background-color: #DDDDDD; font-weight: bold; border-right: none; text-align: right">' + (Math.round((shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["netto_main_group"])*100)/100/1).toFixed(2) + '</td>');
								tr.append(td);
								td = $('<td style="background-color: #DDDDDD; font-weight: bold; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
								tr.append(td);
								
								if(mode=="comp")
								{
									for(c in shops_selected)
									{
										td = $('<td style="background-color: #DDDDDD; font-weight: bold; text-align: right">' + shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["amount_comp_main_group_" + shops_selected[c]] + '</td>');
										tr.append(td);
										td = $('<td style="background-color: #DDDDDD; font-weight: bold; border-right: none; text-align: right">' + (Math.round((shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["netto_comp_main_group_" + shops_selected[c]])*100)/100/1).toFixed(2) + '</td>');
										tr.append(td);
										td = $('<td style="background-color: #DDDDDD; font-weight: bold; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
										tr.append(td);
									}
									td = $('<td style="background-color: #DDDDDD; font-weight: bold; text-align: right">' + shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["amount_comp_main_group"] + '</td>');
									tr.append(td);
									td = $('<td style="background-color: #DDDDDD; font-weight: bold; border-right: none; text-align: right">' + (Math.round((shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["netto_comp_main_group"])*100)/100/1).toFixed(2) + '</td>');
									tr.append(td);
									td = $('<td style="background-color: #DDDDDD; font-weight: bold; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
									tr.append(td);
								}
								tbody.append(tr);
								for(b in category_data) //Untergruppen
								{
									if(category_data[b]["menuitem_id"]==category_data[a]["id_menuitem"])
									{
										tr = $('<tr class="sub' + category_data[a]["id_menuitem"] + '" id="sub' + category_data[b]["id_menuitem"] + '" style="cursor: pointer; display: none"></tr>');
										td = $('<td style="background-color: #F8F8F8; font-weight: bold; padding-left: 30px">' + category_data[b]["title"] + '</td>');
										tr.append(td)
										for(c in shops_selected)
										{
											td = $('<td style="background-color: #F8F8F8; font-weight: bold; text-align: right">' + shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["amount_sub_group_" + shops_selected[c]] + '</td>');
											tr.append(td);
											td = $('<td style="background-color: #F8F8F8; border-right: none; font-weight: bold; text-align: right">' + (Math.round((shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["netto_sub_group_" + shops_selected[c]])*100)/100/1).toFixed(2) + '</td>');
											tr.append(td);
											td = $('<td style="background-color: #F8F8F8; font-weight: bold; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
											tr.append(td);
										}
										td = $('<td style="background-color: #E6E6E6; font-weight: bold; text-align: right">' + shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["amount_sub_group"] + '</td>');
										tr.append(td);
										td = $('<td style="background-color: #E6E6E6; border-right: none; font-weight: bold; text-align: right">' + (Math.round((shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["netto_sub_group"])*100)/100/1).toFixed(2) + '</td>');
										tr.append(td);
										td = $('<td style="background-color: #E6E6E6; font-weight: bold; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
										tr.append(td);
										
										if(mode=="comp")
										{
											for(c in shops_selected)
											{
												td = $('<td style="background-color: #F8F8F8; font-weight: bold; text-align: right">' + shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["amount_comp_sub_group_" + shops_selected[c]] + '</td>');
												tr.append(td);
												td = $('<td style="background-color: #F8F8F8; border-right: none; font-weight: bold; text-align: right">' + (Math.round((shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["netto_comp_sub_group_" + shops_selected[c]])*100)/100/1).toFixed(2) + '</td>');
												tr.append(td);
												td = $('<td style="background-color: #F8F8F8; font-weight: bold; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
												tr.append(td);
											}
											td = $('<td style="background-color: #E6E6E6; font-weight: bold; text-align: right">' + shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["amount_comp_sub_group"] + '</td>');
											tr.append(td);
											td = $('<td style="background-color: #E6E6E6; border-right: none; font-weight: bold; text-align: right">' + (Math.round((shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["netto_comp_sub_group"])*100)/100/1).toFixed(2) + '</td>');
											tr.append(td);
											td = $('<td style="background-color: #E6E6E6; font-weight: bold; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
											tr.append(td);
										}
										tbody.append(tr);
										//items der Untergruppe
										var sub_group = shop_group_data["main_groups"][category_data[a]["id_menuitem"]]["sub_groups"][category_data[b]["id_menuitem"]]["items"];
										sub_group.sort(createSorter('title'));
										for(d in sub_group)
										{
											tr = $('<tr class="item' + category_data[b]["id_menuitem"] + '" style="display: none"></tr>');
											td = $('<td style="padding-left: 40px">' + sub_group[d]["title"] + '</td>');
											tr.append(td)
											for(c in shops_selected)
											{
												td = $('<td style="text-align: right">' + sub_group[d]["amount_item_" + shops_selected[c]] + '</td>');
												tr.append(td);
												td = $('<td style="border-right: none; text-align: right">' + (Math.round(sub_group[d]["netto_item_" + shops_selected[c]]*100)/100/1).toFixed(2) + '</td>');
												tr.append(td);
												td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
												tr.append(td);
											}
											td = $('<td style="background-color: #F8F8F8; text-align: right">' + sub_group[d]["amount_item"] + '</td>');
											tr.append(td);
											td = $('<td style="background-color: #F8F8F8; border-right: none; text-align: right">' + (Math.round(sub_group[d]["netto_item"]*100)/100/1).toFixed(2) + '</td>');
											tr.append(td);
											td = $('<td style="background-color: #F8F8F8; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
											tr.append(td);
											
											if(mode=="comp")
											{
												for(c in shops_selected)
												{
													td = $('<td style="text-align: right">' + sub_group[d]["amount_comp_item_" + shops_selected[c]] + '</td>');
													tr.append(td);
													td = $('<td style="border-right: none; text-align: right">' + (Math.round(sub_group[d]["netto_comp_item_" + shops_selected[c]]*100)/100/1).toFixed(2) + '</td>');
													tr.append(td);
													td = $('<td style="width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
													tr.append(td);
												}
												td = $('<td style="background-color: #F8F8F8; text-align: right">' + sub_group[d]["amount_comp_item"] + '</td>');
												tr.append(td);
												td = $('<td style="background-color: #F8F8F8; border-right: none; text-align: right">' + (Math.round(sub_group[d]["netto_comp_item"]*100)/100/1).toFixed(2) + '</td>');
												tr.append(td);
												td = $('<td style="background-color: #F8F8F8; width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
												tr.append(td);
											}
											tbody.append(tr);
										}
									}
								}
							}
						}
						table.append(tbody);
						
						var tfoot = $('<tfoot></tfoot>');
						tr = $('<tr style="height: 50px"></tr>');
						
						td = $('<td style="border-left: none; border-bottom: none;  border-right: solid;  border-right-width: 1px; border-right-color: black"></td>');
						tr.append(td);
						for(c in shops_selected)
						{
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_group_data["amount_all_" + shops_selected[c]] + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_group_data["netto_all_" + shops_selected[c]]*100)/100/1).toFixed(2) + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: solid;  border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
							tr.append(td);
						}
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_group_data["amount_all"] + '</td>');
						tr.append(td);
						td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round((shop_group_data["netto_all"])*100)/100/1).toFixed(2) + '</td>');
						tr.append(td);
						td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px; border-top: solid; border-top-width: 2px; border-top-color: black">€</td>');
						tr.append(td);
						if(mode=="comp")
						{
							for(c in shops_selected)
							{
								td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_group_data["amount_comp_all_" + shops_selected[c]] + '</td>');
								tr.append(td);
								td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round(shop_group_data["netto_comp_all_" + shops_selected[c]]*100)/100/1).toFixed(2) + '</td>');
								tr.append(td);
								td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: solid;  border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px">€</td>');
								tr.append(td);
							}
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black">' + shop_group_data["amount_comp_all"] + '</td>');
							tr.append(td);
							td = $('<td style="text-align: right; background-color: #E6E6E6; font-weight: bold; border-top: solid; border-top-width: 2px; border-top-color: black; border-right: none">' + (Math.round((shop_group_data["netto_comp_all"])*100)/100/1).toFixed(2) + '</td>');
							tr.append(td);
							td = $('<td style="background-color: #E6E6E6; font-weight: bold;width: 10px; border-right: solid; border-right-width: 1px; border-right-color: black; border-left: none; padding-left: 0px; border-top: solid; border-top-width: 2px; border-top-color: black">€</td>');
							tr.append(td);
						}
						tfoot.append(tr);
						table.append(tfoot);
						
						$("#statistics_show").empty().append(table);
						//show_status2(print_r(category_data));
						/*for(n in category_data)
						{
							$("#" + category_data[n]["id_menuitem"]).click(function() {
							  class_show_hide(category_data[n]["id_menuitem"]);
							  alert(category_data[n]["id_menuitem"]);
							});
						}*/
						for(var n in category_data){
						  (function(k) {
							$("#" + k).click(function() {
							  sub_show_hide("sub"+k, shop_group_data);
							 //alert(k);
							});
						  })(category_data[n]["id_menuitem"]);
						}
						
						for(var n in category_data){
						  (function(k) {
							$("#sub" + k).click(function() {
							  item_show_hide("item"+k);
							 //alert(k);
							});
						  })(category_data[n]["id_menuitem"]);
						}
						
						//$("#statistics_show").empty().append($('<p style="font-size: 20px; font-weight: bold">Hier entsteht eine Statistik nach Artikelgruppen</p>'));
						//group_table_show();
					}
					//else if(list_select_radiobutton=="user_list")
					else if(list_select=="user_list")
					{
						var lists_data = new Array();
						var listtypes_data = new Array();
						var listtypes_index = new Array();
		
						$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "ListsGet", mode: "lists"},
							function (data)
							{
								//show_status2(data);
								var $xml = $($.parseXML(data));
								var $ack = $xml.find("Ack");
								if ( $ack.text()=="Success" )
								{
									$xml.find("list").each(
										function()
										{
											var self = this;
											lists_data[$(self).find("id_list").text()] = 					new Array;
											lists_data[$(self).find("id_list").text()]["title"] = 			$(self).find("title").text();
											lists_data[$(self).find("id_list").text()]["listtype_id"] = 	$(self).find("listtype_id").text();
											lists_data[$(self).find("id_list").text()]["listtype_title"] = 	$(self).find("listtype_title").text();
											lists_data[$(self).find("id_list").text()]["firstmod_user"] = 	$(self).find("firstmod_user").text();
											lists_data[$(self).find("id_list").text()]["items"] = 			new Array();
											var item_cnt = 0;
											$(self).find("item").each(
												function()
												{
													//lists_data[$(self).find("id_list").text()]["items"][item_cnt] = $(this).find("item_id").text();
													lists_data[$(self).find("id_list").text()]["items"][item_cnt] = new Array();
													lists_data[$(self).find("id_list").text()]["items"][item_cnt]["item_id"] = $(this).find("item_id").text();
													item_cnt = item_cnt + 1;
												}
											);
											if($.inArray($(self).find("listtype_id").text(), listtypes_index)==-1 && ($(self).find("listtype_id").text()!="2" || ($(self).find("listtype_id").text()=="2" && $(self).find("firstmod_user").text()==<?php echo $_SESSION["id_user"]?>)))
											{
												listtypes_index.push($(self).find("listtype_id").text());
												listtypes_data.push({"listtype_id" : $(self).find("listtype_id").text(), "listtype_title" : $(self).find("listtype_title").text(), "lists" : new Array()});
											}
										}
									);
									for(a in lists_data)
									{
										for(b in listtypes_data)
										{
											if(lists_data[a]["listtype_id"]==listtypes_data[b]["listtype_id"] && (lists_data[a]["listtype_id"]!=2 || (lists_data[a]["listtype_id"]==2 && lists_data[a]["firstmod_user"]==<?php echo $_SESSION["id_user"]?>)))
											{
												listtypes_data[b]["lists"].push({"id_list" : a, "title" : lists_data[a]["title"]});
											}
										}
									}
						
									var main = $("#statistics_show");
									main.empty();
									
									var row = $('<p style="font-weight: bold; font-size: 15px; display: inline; margin-right: 20px">Liste auswählen:</p>');
									main.append(row);
									
									var sel = $('<select id="list_select2" style="width: 300px"></select>');
									row = $('<option value="">bitte auswählen</option><br>');
									sel.append(row);
									var option;
									for(a in listtypes_data)
									{
										row = $('<optgroup label="' + listtypes_data[a]["listtype_title"] + '"></optgroup>');
										listtypes_data[a]["lists"].sort(createSorter('title'));
										for(b in listtypes_data[a]["lists"])
										{
											option = $('<option value="' + listtypes_data[a]["lists"][b]["id_list"] + '">' + listtypes_data[a]["lists"][b]["title"] + '</option>');
											row.append(option);
										}
										sel.append(row);
									}

									main.append(sel);
									
									var table_div = $('<div id="table_div" style="margin-top: 10px"></div>');
									main.append(table_div);

									$('#list_select2').on('change', function() {
										if($("#list_select2").val()!="")
										{
											var list = $("#list_select2").val();
											var list_title = $("#list_select2 :selected").text();

											$('#table_div').empty().append($('<p style="font-size: 20px; font-weight: bold">Bitte warten...Tabelle wird erzeugt...</p>'));
											setTimeout(function(){
												list_table_show(shop_data, item_data, shops_selected, mode, shopcnt, list, lists_data, listtypes_data, list_title);
											},10);
										}
										else
											$('#table_div').empty();
									});
								}
							}
						);
					}
					//else if(list_select_radiobutton=="customer")
					else if(list_select=="customer")
					{
						//get customers usernames
						wait_dialog_show();
						var $users=new Array();
						for(var a = 0; a<item_data.length; a++)
						{
							$users['"'+item_data[a]["customer_id"]+'"']=item_data[a]["customer_id"];
						}
						var $id_user="";
						var $key;
						for($key in $users) $id_user+=", "+$users[$key];
						$id_user=$id_user.substr(2);
						$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "CustomersGet", id_user:$id_user }, function($data)
						{
							try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
							if ( $xml.find("Ack").text()!="Success" ) { show_message_dialog('<?php echo t("Die Userdaten konnten nicht geladen werden");?>!'); wait_dialog_hide(); return; }
							//usernames
							customers = new Array();
							customers_name = new Array();
							$xml.find("customer").each(function()
							{
								customers_name[$(this).find("user_id").text()] = $(this).find("name").text();
								customers[$(this).find("user_id").text()] = $(this).find("username").text();
							});
							
							//user sum array
							customer_data = new Array;
							
							for(var a = 0; a<item_data.length; a++)
							{
								var netto = 0;
								if(item_data[a]["Currency_Code"]=="EUR")
								{
									netto = item_data[a]["netto"];
								}
								else
								{
									netto = (Math.round((item_data[a]["netto"]/item_data[a]["exchange_rate_to_EUR"])*100)/100);
								}
								
								if(typeof customer_data[item_data[a]["customer_id"]] == 'undefined')
								{
									customer_data[item_data[a]["customer_id"]] = 				new Array();
									customer_data[item_data[a]["customer_id"]]["name"] = 	customers_name[item_data[a]["customer_id"]];
									customer_data[item_data[a]["customer_id"]]["username"] = 	customers[item_data[a]["customer_id"]];
									customer_data[item_data[a]["customer_id"]]["netto"] = 		0;
									customer_data[item_data[a]["customer_id"]]["netto_comp"] = 	0;
								}
								if(item_data[a]["time_range"] == "single")
								{
									customer_data[item_data[a]["customer_id"]]["netto"] = (Math.round((customer_data[item_data[a]["customer_id"]]["netto"]*1 + item_data[a]["amount"]*1*netto)*100)/100).toFixed(2);	
								}
								else if(item_data[a]["time_range"] == "comp")
								{
									customer_data[item_data[a]["customer_id"]]["netto_comp"] = (Math.round((customer_data[item_data[a]["customer_id"]]["netto_comp"]*1 + item_data[a]["amount"]*1*netto)*100)/100).toFixed(2);
								}
								
							}
							
							customer_table_show(customer_data, mode);
							
							wait_dialog_hide();
						});
					}
					else if(list_select == 'countries')
					{
						wait_dialog_show();
						//Länderdaten laden
						var shop_countries_all = new Array();
						$.post("<?php echo PATH;?>soa/", {API: "shop", Action: "CountriesGet"}, function($data){
							var $xml = $($.parseXML($data));
							var $ack = $xml.find("Ack");
							if ( $ack.text()=="Success" )
							{
								shop_countries_all[0] = 'Kein Land angegeben';
								$xml.find('shop_countries').each(function(){
									shop_countries_all[$(this).find('id_country').text()] = $(this).find('country').text();
								});
								
								//country-sum array
								var country_data = new Array();
								country_data['netto_all'] = 0;
								country_data['netto_comp_all'] = 0;
								
								for(var a = 0; a<item_data.length; a++)
								{
									var netto = 0;
									
									if(item_data[a]["Currency_Code"]=="EUR")
									{
										netto = item_data[a]["netto"];
									}
									else
									{
										netto = (Math.round((item_data[a]["netto"]/item_data[a]["exchange_rate_to_EUR"])*100)/100);
									}
									if(typeof country_data[item_data[a]["country_id"]] == 'undefined')
									{
										country_data[item_data[a]["country_id"]] = 				 new Array();
										country_data[item_data[a]["country_id"]]["country"] = 	 shop_countries_all[item_data[a]["country_id"]];
										country_data[item_data[a]["country_id"]]["netto"] = 	 0;
										country_data[item_data[a]["country_id"]]["netto_comp"] = 0;
									}
									if(item_data[a]["time_range"] == "single")
									{
										country_data[item_data[a]["country_id"]]["netto"] = (Math.round((country_data[item_data[a]["country_id"]]["netto"]*1 + item_data[a]["amount"]*1*netto)*100)/100).toFixed(2);
										country_data['netto_all'] = (Math.round((country_data['netto_all']*1 + item_data[a]["amount"]*1*netto)*100)/100).toFixed(2);	
									}
									else if(item_data[a]["time_range"] == "comp")
									{
										country_data[item_data[a]["country_id"]]["netto_comp"] = (Math.round((country_data[item_data[a]["country_id"]]["netto_comp"]*1 + item_data[a]["amount"]*1*netto)*100)/100).toFixed(2);
										country_data['netto_comp_all'] = (Math.round((country_data['netto_comp_all']*1 + item_data[a]["amount"]*1*netto)*100)/100).toFixed(2);
									}
								}
								
								if(<?php echo $_SESSION["id_user"];?> == 49352)
									country_table_show(country_data, mode);
								wait_dialog_hide();
							}
						});
					}
				}
			}
		);
		//alert(print_r(shops_selected));
	}
	
	function statistics_main()
	{
		//alert(screen.availWidth);
		html = '';
		html += '<h1>Statistiken</h1>';
		
		html += '<div id="statistics_settings" style="width: 350px; float: left"></div>';
		html += '<div id="statistics_show" style="float: left; overflow: auto; max-width: 78%; max-height: 700px; margin-left: 5px"></div>';
		//html += '<div id="statistics_show" style="float: left; overflow: auto; max-width: 1500px; max-height: 700px; margin-left: 5px"></div>';
		
		$("#content").html(html);
		
		shops_load();
	}
	
	function sub_show_hide(c, shop_group_data)
	{
		var mg = c.substring(3);
		var sub_groups = shop_group_data["main_groups"][mg]["sub_groups"];
		for(sg in sub_groups)
		{
			if($('.item' + sg).css('display')=="table-row")
				$('.item' + sg).toggle("fade", 500);
		}
		$("."+c).toggle("fade", 500);
	}
	
	statistics_main();

</script>

<?php	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>