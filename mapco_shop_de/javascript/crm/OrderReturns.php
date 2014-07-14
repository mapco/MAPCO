<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	orderitems = new Object;
	returns = new Array();
	creditreasons = new Object;

	function update_returns_array(data)
	{
		var $xml=$($.parseXML(data));
		$xml.find("orderreturn").each(
			function()
			{
				
			//	var order_id = $(this).find("id_return").text();
				//delete orders[order_id];
			//	orders[order_id] = new Array();
				
				$(this).children().each(
					function()
					{
						var $tagname=this.tagName;
						
						switch ($tagname)
						{
							case "returnitems": 
								returns["returnitem"] = new Array();
								var i=0;
								$(this).find("returnitem").each(
								function ()
								{
									returns["returnitem"][i] = new Array();
									$(this).children().each(
									function ()
									{
										var $tagname2=this.tagName;
										returns["returnitem"][i][$tagname2] = $(this).text();
									});
									
									
									i++;
								});
								break;
							case "returncredits": 
								returns["returncredit"] = new Array();
								var i=0;
								$(this).find("returncredit").each(
								function ()
								{
									returns["returncredit"][i] = new Array();
									$(this).children().each(
									function ()
									{
										var $tagname2=this.tagName;
										returns["returncredit"][i][$tagname2] = $(this).text();
									});
									
									
									i++;
								});
								break;
							default:
								returns[$tagname]=$(this).text();
								break;
						}
					}
				);
			}
		);
		
		
	}

	

	function order_returns_add(orderid)
	{
		if (confirm("Soll eine neue Rückgabe angelegt werden?"))
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/index.php", { 
					API: "shop", 
					APIRequest: "OrderReturnAdd", 
					mode:"return", 
					order_id:orderid},
				function ($data)
				{
					wait_dialog_hide();
					try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
					var $Ack = $xml.find("Ack").text();
					if ($Ack!="Success") {show_status2($data); return;}
					
					order_returns_dialog($xml.find("return_id").text());
					update_view(orderid);
				}
			);
		}
	}
	
	function order_exchange_add(orderid)
	{
		if (confirm("Soll ein neuer Umtausch angelegt werden?"))
		{

			wait_dialog_show();
			//FESTLEGEN DER VERSANDART
			var shippingtype_id = 0;
			if (orders[orderid]["ship_country_code"]!="")
			{
				if (orders[orderid]["ship_country_code"]=="DE") shippingtype_id = 1; else shippingtype_id = 5;
			}
			else
			{
				if (orders[orderid]["bill_country_code"]=="DE") shippingtype_id = 1; else shippingtype_id = 5;
			}
				
			//KOPIE DER URSPRÜNGLICHEN ORDER WIRD ANGELEGT
			$.post("<?php echo PATH; ?>soa2/index.php", { 
			
					API: "shop", 
					APIRequest: "OrderAdd", 
					mode:"new", 
					shop_id:orders[orderid]["shop_id"],
					ordertype_id:4,
					status_id:1,
					Currency_Code:orders[orderid]["Currency_Code"],
					VAT:orders[orderid]["VAT"],
					status_date:Math.round(new Date().getTime() / 1000),
					customer_id:orders[orderid]["customer_id"],
					usermail:orders[orderid]["usermail"],
					userphone:orders[orderid]["userphone"],
					bill_company:orders[orderid]["bill_company"],
					bill_firstname:orders[orderid]["bill_firstname"],
					bill_lastname:orders[orderid]["bill_lastname"],
					bill_zip:orders[orderid]["bill_zip"],
					bill_city:orders[orderid]["bill_city"],
					bill_street:orders[orderid]["bill_street"],
					bill_number:orders[orderid]["bill_number"],
					bill_additional:orders[orderid]["bill_additional"],
					bill_country:orders[orderid]["bill_country"],
					bill_country_code:orders[orderid]["bill_country_code"],
					bill_adr_id:orders[orderid]["bill_adr_id"],
					ship_company:orders[orderid]["ship_company"],
					ship_firstname:orders[orderid]["ship_firstname"],
					ship_lastname:orders[orderid]["ship_lastname"],
					ship_zip:orders[orderid]["ship_zip"],
					ship_city:orders[orderid]["ship_city"],
					ship_street:orders[orderid]["ship_street"],
					ship_number:orders[orderid]["ship_number"],
					ship_additional:orders[orderid]["ship_additional"],
					ship_country:orders[orderid]["ship_country"],
					ship_country_code:orders[orderid]["ship_country_code"],
					ship_adr_id:orders[orderid]["ship_adr_id"],
					bill_address_manual_update:orders[orderid]["bill_address_manual_update"],
					shipping_type_id:shippingtype_id,
					payments_type_id:orders[orderid]["payments_type_id"],
					shipping_costs:0, 
					shipping_net:0},
			function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
	
	
				var exchange_id_order=$xml.find("id_order").text();
	
				$.post("<?php echo PATH; ?>soa2/index.php", { 
						API: "shop", 
						APIRequest: "OrderReturnAdd", 
						mode:"exchange", 
						order_id:orderid,
						exchange_order_id:exchange_id_order
						},
					function ($data)
					{
						wait_dialog_hide();
						try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
						var $Ack = $xml.find("Ack").text();
						if ($Ack!="Success") {show_status2($data); return;}
						
						order_returns_dialog($xml.find("return_id").text());
						update_view(orderid);
					}
				);
	
			});
		}
	}

	function order_returns_partial_dialog()
	{
		if ($("#order_returns_partial_dialog").length == 0)
		{
			$("body").append('<div id="order_returns_partial_dialog" style="display:none">');
		}
		
		var $html = '';
		$html+= '<b>Um einen Teilumtausch (Teile eines Kits / Satzes) durchzuführen, sind folgende Schritte abzuarbeiten: </b>';
		$html+= '<table style="width:100%" >';
		$html+=	'<tr>';
		$html+=	'	<th>';
		$html+=	'	</th>';
		$html+=	'	<th>';
		$html+= '		Schritt';
		$html+=	'	</th>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	1.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	<b>Teilgutschriften können nur vom Teamleiter durchgeführt werden</b>';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	2.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Rabatt gegenüber Einzelteilkauf ausrechnen';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	3.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Preis der gutzuschreibenden Einzelteile ausrechnen und Rabatt abziehen</b>';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	4.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Gutschrift über die errechnete Summe schreiben';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	5.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Im Notizfeld eintragen, welche Teile der Kunde zurücksendet (KEINE Artikel über den Dialog auswählen)';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	6.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	Die Rechnungsnummer der neu erstellten Rechnung ebenfalls im Notizfeld vermerken';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '<tr>';
		$html+= '	<td>';
		$html+= '	7.';
		$html+= '	</td>';
		$html+= '	<td>';
		$html+= '	In der angelegten Umtausch-Bestellung die dem Kunden zu sendenden Artikel eintragen (Aktionen -> Umtausch bearbeiten -> Artikel hinzufügen)';
		$html+= '	</td>';
		$html+=	'</tr>';
		$html+= '</table>';

		$("#order_returns_partial_dialog").html($html);

		$("#order_returns_partial_dialog").dialog
		({	buttons:
			[
				{ text: "OK", click: function() { $(this).dialog("close");} }
			],
			closeOnEscape: false,
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title: "Teilumtausch anlegen",
			width:800
		});		

	}

	
	function order_returns_dialog(return_id)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/index.php", { 
				API: "shop", 
				APIRequest: "OrderReturnGet", 
				return_id:return_id},
			function ($data)
			{
				//show_status2($data);
				//return;
				
				wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
		
				if ($("#order_return_dialog").length == 0)
				{
					$("body").append('<div id="order_return_dialog" style="display:none">');
				}

				//FÜLLE RETURNS ARRAY 
				update_returns_array($data);
				var html = '';
				
				//RETURNS AGENT INFO
				if (typeof (Seller[returns["firstmod_user"]])!=="undefined") var firstmod_user = Seller[returns["firstmod_user"]]; else var firstmod_user = 'UserID: '+returns["firstmod_user"];

				if (returns["firstmod"]!=0) var firstmod = convert_time_from_timestamp (returns["firstmod"], "complete"); else var firstmod = '';

				if (typeof (Seller[returns["closed_by_user"]])!=="undefined") var closed_by_user = Seller[returns["firstmod_user"]]; else var closed_by_user = 'UserID: '+returns["firstmod_user"];

				if (returns["date_closed"]!=0) var date_closed = convert_time_from_timestamp (returns["date_closed"], "complete"); else var date_closed = '';

				if (typeof (Seller[returns["lastmod_user"]])!=="undefined") var lastmod_user = Seller[returns["lastmod_user"]]; else var lastmod_user = 'UserID: '+returns["lastmod_user"];

				if (returns["lastmod"]!=0) var lastmod = convert_time_from_timestamp (returns["lastmod"], "complete"); else var lastmod = '';
								
				html+='<table style="width:100%">';
				html+='<colgroup><col style="width:50%"><col style="width:50%"></colgroup>';
				html+='<tr>';
				html+='	<td><b>Fall angelegt von: </b>'+firstmod_user+' <b>am: </b>'+firstmod+'</td>';
				html+='	<td><b>letzte Bearbeitung von: </b>'+lastmod_user+' <b>am: </b>'+lastmod+'</td>';
				html+='</tr>';
				if (returns["date_closed"]!=0)
				{
				html+='<tr>';
				html+='	<td style="background-color:#cfc"><b>Fall geschlossen von: </b>'+closed_by_user+' <b>am: </b>'+date_closed+'</td>';
				html+='</tr>';
				}
				html+='</table>';
				
				//RETURNS DATA
					//KAUFDATUM 
					if (returns["order_firstmod"]!=0) var order_firstmod = convert_time_from_timestamp (returns["order_firstmod"], "date"); else var order_firstmod = '';

					//DATUM ARTIKEL ZURÜCK 
					if (returns["date_return"]!=0) var date_return = convert_time_from_timestamp (returns["date_return"], "date"); else var date_return = '';	

					//DATUM ERSTATTUNG
					if (returns["date_refund"]!=0)	var date_refund = convert_time_from_timestamp (returns["date_refund"], "date"); else var date_refund = '';

					//ERSTATTUNGSSUMME
					if (returns["refund"]!=0) 
					{
						var refund = returns["refund"]*1;
						refund = refund.toFixed(2).toString().replace(".", ",")
					}
					else 
					{
						var refund = '0,00';
					}

					//ERSTATTUNGSSUMME FÜR VERSANDKOSTEN
					if (returns["refund_shipment"]!=0) 
					{
						var refund_shipment = returns["refund_shipment"]*1;
						refund_shipment = refund_shipment.toString().replace(".", ","); 
					}
					else 
					{
						var refund_shipment = '0,00';
					}

					//EBAY DEMAND CLOSING 1
					if (returns["ebay_demand_closing1"]!=0) var ebay_demand_closing1 = convert_time_from_timestamp (returns["ebay_demand_closing1"], "date"); else var ebay_demand_closing1 = '';

					//EBAY DEMAND CLOSING 2
					if (returns["ebay_demand_closing2"]!=0) var ebay_demand_closing2 = convert_time_from_timestamp (returns["ebay_demand_closing2"], "date"); else var ebay_demand_closing2 = '';
					
					//EXCHANGE SENT
					if (returns["date_exchange_sent"]!=0) var date_exchange_sent = convert_time_from_timestamp (returns["date_exchange_sent"], "date"); else var date_exchange_sent = '';
				
					if (returns["refund_aufid"]!=0) var refund_aufid = returns["refund_aufid"]; else refund_aufid="";
					refund_order_shipment
					if (returns["refund_order_shipment"]!=0) var refund_order_shipment = returns["refund_order_shipment"].toString().replace(".", ","); else var refund_order_shipment = '0,00';
					
				if (returns["state"] == 1)
				{
					var $disable='disabled';
				}
				else
				{
					var $disable='';
				}
				
				html+='<table style="width:100%">';
				html+='<colgroup><col style="width:25%"><col style="width:25%"><col style="width:25%"><col style="width:25%"></colgroup>';
				html+='<tr>';
				html+='	<td><small><b>Rechnungsnummer</b></small><br />';
				html+=' <input type="text" id="order_returns_invoice_nr" class="order_returns_update" size="10" value="'+returns["invoice_nr"]+'" '+$disable+' /></td>';
				html+='	<td><small><b>Kaufdatum</b></small><br />';
				html+=' <input type="text" id="order_returns_order_firstmod" size="10" value="'+order_firstmod+'" disabled /></td>';
			//	html+='	<td><small><b>Fall geöfnet am:</b></small><br />';
			//	html+=' <input type="text" id="order_returns_firstmod" size="10" value="'+firstmod+'" disabled /></td>';
				if (returns["return_type"]=="exchange")
				{
					html+='	<td><small><b>Umtausch versendet am:</b></small><br />';
					html+=' <input type="text" id="order_returns_exchange_sent_date" size="10" value="'+date_exchange_sent+'" disabled /></td>';
				}
				else
				{
					html+='	<td></td>';
				}
				html+='	<td><small><b>Bearbeitungstatus</b></small><br />';
				if (<?php echo $_SESSION["userrole_id"]; ?> == 1)
				{
					html+=' <select id="order_returns_state" size="1" onchange="order_returns_state_check();">';
				}
				else
				{
					html+=' <select id="order_returns_state" size="1" onchange="order_returns_state_check();" '+$disable+'>';
				}
				html+='		<option value=0>offen</option>';
				html+='		<option value=1>geschlossen</option>';
				html+='		<select></td>';
				html+='</tr><tr>';
				html+='	<td><small><b>Rücksendung erhalten am:</b></small><br />';
				html+=' <input type="text" id="order_returns_date_return" class="order_returns_update" size="10" value="'+date_return+'" '+$disable+' /></td>';
				html+='	<td><small><b>Erstattung durchgeführt am:</b></small><br />';
				html+=' <input type="text" id="order_returns_date_refund" class="order_returns_update" size="10" value="'+date_refund+'" '+$disable+' /></td>';
				html+='	<td><small><b>Erstattungsumme</b></small><br />';
				html+=' <input type="text" id="order_returns_refund" class="order_returns_update" size="10" value="'+refund+'" '+$disable+' /><small>EUR</small></td>';
				html+='	<td><small><b>Erstattung Rücksendekosten</b></small><br />';
				html+=' <input type="text" id="order_returns_refund_shipment" class="order_returns_update" size="10" value="'+refund_shipment+'" '+$disable+' /><small>EUR</small></td>';
				html+='</tr><tr>';
				if (returns["return_type"]=="return")
				{
					html+='	<td><small><b>Aufford. Ebay-Rückgabe durch Kunde 1</b></small><br />';
					html+=' <input type="text" id="order_returns_ebay_demand_closing1" class="order_returns_update" size="10" value="'+ebay_demand_closing1+'" /></td>';
					html+='	<td><small><b>Aufford. Ebay-Rückgabe durch Kunde 2</b></small><br />';
					html+=' <input type="text" id="order_returns_ebay_demand_closing2" class="order_returns_update" size="10" value="'+ebay_demand_closing2+'" /></td>';
					html+='	<td><small><b>Ebay-Verkaufsprovision gutgeschrieben</b></small><br />';
					if (returns["ebay_fee_refund"]==1)
					{
						html+=' <input type="checkbox" id="order_returns_ebay_fee_refund" class="order_returns_update" value=1 checked="checked"/></td>';
					}
					else
					{
						html+=' <input type="checkbox" id="order_returns_ebay_fee_refund" class="order_returns_update" value=0 /></td>';
					}
					html+='<td></td>';
				}
				
				html+='</tr><tr>';
				html+='	<td><small><b>Gutschrift AufID</b></small><br /><input type="text" id="order_returns_refund_aufid" class="order_returns_update" size="10" value="'+refund_aufid+'" /></td>';
				html+='	<td><small><b>Gutschrift Rechnungsnummer</b></small><br /><input type="text" id="order_returns_refund_invoice_nr" size="10" disabled/></td>';
				html+='	<td><small><b>Erstattung Versandkosten</b></small><br /><input type="text" id="order_returns_refund_order_shipment" class="order_returns_update" size="10" value="'+refund_order_shipment+'" /></td>';
		//	show_status2(print_r(orders));
				if 	(orders[returns["order_id"]]["RetourLabelID"]!="")
				{
					html+='	<td><small>Retoulabel versendet <br />';
					html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Shipment_Returned.png" alt="Retourlabel versendet" title="Retourlabel versendet" /></span>';	
					html+='		<span>'+convert_time_from_timestamp(orders[returns["order_id"]]["RetourLabelTimestamp"], "complete")+'</span>';
					html+='	<br /><span><small><a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='+orders[returns["order_id"]]["RetourLabelID"]+'" target="_blank">'+orders[returns["order_id"]]["RetourLabelID"]+'</a></small></span>';
					if( typeof DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]] !== "undefined" )
					{
						var dhl_parameter = DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]]["dhl_parameter"];
						html+='	<br /><a href="javascript:send_DHLretourlabel('+returns["order_id"]+', \''+dhl_parameter+'\');">DHL-Retourlabel erneut senden</a>';
					}
					else html+='DHL-Retourlabel nicht möglich. Bitte Economy Select Import prüfen.';
					html+='	</td>';

				}
				//else if (returns["returnitem"].length>0 && returns["state"] == 0)
				else if (returns["state"] == 0)
				{
					html+='	<td>';
					if( typeof DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]] !== "undefined" )
					{
						var dhl_parameter = DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]]["dhl_parameter"];
						html+='<a href="javascript:send_DHLretourlabel('+returns["order_id"]+', \''+dhl_parameter+'\');">DHL-Retourlabel senden</a>';
					}
					else 
					{
						html+='DHL-Retourlabel nicht möglich. Bitte Economy Select Import prüfen.';
						html+='<br /><a href="javascript:save_DHLretourlabelID('+returns["order_id"]+');">ReturnTrackingID manuell eintragen</a>.';
					}
					
					html+='	</td>';
				}
				/*
				else
				{
					if (returns["state"] == 0)
					{
						html+='	<td><small><b>Retourlabel versenden: </b><br /> min. 1 Umtausch- /Rückgabeartikel anlegen</small></td>';
					}
					else
					{
						html+='	<td></td>';
					}
				}*/
				html+='</tr><tr>';
				html+='	<td colspan=4"><small><b>Notizen</b></small><br />';
				html+='	<textarea id="order_returns_return_note" class="order_returns_update" cols="120" rows="5">'+returns["return_note"]+'</textarea></td>';
				html+='</tr>';
				html+='</table>';
				
				//RETURNS ITEMS DATA
					//TYPE: RETURNS
				if (returns["return_type"]=="return")
				{
					html+='<table style="width:100%">';
					html+='<tr>';
					html+='	<th>MPN</th>';
					html+='	<th>Artikelbezeichnung</th>';
					html+='	<th>Anzahl</th>';
					html+='	<th>Rückgabegrund</th>';
					if ($disable=="") html+=' <th><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Artikel der Rückgabe hinzufügen" title="Artikel der Rückgabe hinzufügen" onclick="order_returnitem_add_dialog('+return_id+', '+returns["order_id"]+');"/>';
					html+='</tr>';
					
					for (var i  = 0; i<returns["returnitem"].length; i++)
					{
						if (returns["returnitem"][i]["return_reason_description"]!="")
						{
							var $style='style=\'border-bottom:0\'';
						}
						else
						{
							var $style='';
						}
						html+='<tr>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["MPN"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["title"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["amount"]+'</td>';
						html+='	<td '+$style+'>'+ReturnsReasons[returns["returnitem"][i]["return_reason"]]["title"]+'</td>';
						//html+='	<td '+$style+'>'+returns["returnitem"][i]["return_reason"]+'</td>';
						if ($disable=="") html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Artikel bearbeiten" title="Artikel bearbeiten" onclick="order_returnitem_update_dialog('+return_id+', '+returns["order_id"]+', '+returns["returnitem"][i]["item_id"]+');"/></td>';					html+='<tr>';
						if (returns["returnitem"][i]["return_reason_description"]!="")
						{
							if ($disable=="") var $cols=5; else var $cols=4;
							html+='<tr style="background-color:#FC7;"><td colspan="'+$cols+'" style="border-top:0">'+returns["returnitem"][i]["return_reason_description"]+'</td></tr>';
						}
					}
					html+='</table>';
				}
				
					//TYPE: EXCHNAGE
				if (returns["return_type"]=="exchange")
				{
					html+='<table style="width:100%">';
					html+='<tr>';
					html+='	<th>MPN</th>';
					html+='	<th>Artikelbezeichnung</th>';
					html+='	<th>Anzahl</th>';
					html+='	<th>Umtauschgrund</th>';
					html+='	<th style="background-color:#999">U-MPN</th>';
					html+='	<th style="background-color:#999">U-Artikelbezeichnung</th>';
					html+='	<th style="background-color:#999">U-Anzahl</th>';
					if ($disable=="") html+=' <th><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Artikel dem Umtausch hinzufügen" title="Artikel dem Umtausch hinzufügen" onclick="order_exchangeitem_add_dialog('+return_id+', '+returns["order_id"]+');"/>';
					html+='</tr>';
					
					for (var i  = 0; i<returns["returnitem"].length; i++)
					{
						if (returns["returnitem"][i]["return_reason_description"]!="")
						{
							var $style='style=\'border-bottom:0\'';
						}
						else
						{
							var $style='';
						}
						html+='<tr>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["MPN"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["title"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["amount"]+'</td>';
						html+='	<td '+$style+'>'+ReturnsReasons[returns["returnitem"][i]["return_reason"]]["title"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["exchangeMPN"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["exchangetitle"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["exchangeamount"]+'</td>';

						//html+='	<td '+$style+'>'+returns["returnitem"][i]["return_reason"]+'</td>';
						if ($disable=="") html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Artikel bearbeiten" title="Artikel bearbeiten" onclick="order_returnitem_update_dialog('+return_id+', '+returns["order_id"]+', '+returns["returnitem"][i]["item_id"]+');"/></td>';					html+='<tr>';
						if (returns["returnitem"][i]["return_reason_description"]!="")
						{
							if ($disable=="") var $cols=8; else var $cols=7;
							html+='<tr style="background-color:#FC7;"><td colspan="'+$cols+'" style="border-top:0">'+returns["returnitem"][i]["return_reason_description"]+'</td></tr>';
						}
					}
					html+='</table>';
				}
				
				$("#order_return_dialog").html(html);
				
				//SET RETURN STATE
				$("#order_returns_state").val(returns["state"]);
				
				
				//SET DATEPICKERs
				$( "#order_returns_date_return" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
				$( "#order_returns_date_refund" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });

				if (returns["return_type"]=="return")
				{
					$( "#order_returns_ebay_demand_closing1" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					$( "#order_returns_ebay_demand_closing2" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
				}

				//BIND EVENTHANDLER ONCHANGE
				$(".order_returns_update").bind("change", function(e) {
					order_returns_update($(this).attr("id"), return_id);
				});


				//BIND EVENTHANDLER "ENTER"
				$("#order_returns_billnumber").bind("keypress", function(e) {
					if(e.keyCode==13)
					{
						order_returns_update($(this).attr("id"), return_id);
					}
				});

				if (returns["return_type"]=="return")
				{
					var $dialogtitle="Rückgabe bearbeiten";
				}
				else
				{
					var $dialogtitle="Umtausch bearbeiten";
				}
				
				$("#order_return_dialog").dialog
				({	buttons:
					[
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeOnEscape: false,
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:$dialogtitle,
					width:800
				});		
				
			}
		);
	}


	function order_returns_dialog2(return_id)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/index.php", { 
				API: "shop", 
				APIRequest: "OrderReturnGet", 
				return_id:return_id},
			function ($data)
			{
				//show_status2($data);
				//return;
				
				wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
		
				if ($("#order_return_dialog").length == 0)
				{
					$("body").append('<div id="order_return_dialog" style="display:none">');
				}

				//FÜLLE RETURNS ARRAY 
				update_returns_array($data);
				var html = '';
				
				//RETURNS AGENT INFO
				//if (typeof (Seller[returns["firstmod_user"]])!=="undefined") var firstmod_user = Seller[returns["firstmod_user"]]; else var firstmod_user = 'UserID: '+returns["firstmod_user"];
				var firstmod_user = returns["firstmod_username"];

				if (returns["firstmod"]!=0) var firstmod = convert_time_from_timestamp (returns["firstmod"], "complete"); else var firstmod = '';

				if (typeof (Seller[returns["closed_by_user"]])!=="undefined") var closed_by_user = Seller[returns["firstmod_user"]]; else var closed_by_user = 'UserID: '+returns["firstmod_user"];

				if (returns["date_closed"]!=0) var date_closed = convert_time_from_timestamp (returns["date_closed"], "complete"); else var date_closed = '';

				//if (typeof (Seller[returns["lastmod_user"]])!=="undefined") var lastmod_user = Seller[returns["lastmod_user"]]; else var lastmod_user = 'UserID: '+returns["lastmod_user"];
				var lastmod_user = returns["lastmod_username"];

				if (returns["lastmod"]!=0) var lastmod = convert_time_from_timestamp (returns["lastmod"], "complete"); else var lastmod = '';
				
				//KAUFDATUM 
				if (returns["order_firstmod"]!=0) var order_firstmod = convert_time_from_timestamp (returns["order_firstmod"], "date"); else var order_firstmod = '';
				//EXCHANGE SENT
				if (returns["date_exchange_sent"]!=0) var date_exchange_sent = convert_time_from_timestamp (returns["date_exchange_sent"], "date"); else var date_exchange_sent = '';
								
				html+='<table style="width:100%">';
				html+='<colgroup><col style="width:33%"><col style="width:33%"><col style="width:34%"></colgroup>';
				html+='<tr>';
				html+='	<td><b>Fall angelegt von: </b>'+firstmod_user+' <b>am: </b>'+firstmod+'</td>';
				html+='	<td><b>letzte Bearbeitung von: </b>'+lastmod_user+' <b>am: </b>'+lastmod+'</td>';
				if (returns["date_closed"]==1 || <?php echo $_SESSION["userrole_id"]; ?> == 1)
				{
					html+='	<td><b>Bearbeitungstatus</b> ';
					html+=' <select id="order_returns_state" size="1" onchange="order_returns_state_check2();">';
					html+='		<option value=0>offen</option>';
					html+='		<option value=1>geschlossen</option>';
					html+='	<select></td>';
				}
				else
				{
					html+='	<td style="background-color:#cfc"><b>Fall geschlossen von: </b>'+closed_by_user+' <b>am: </b>'+date_closed+'</td>';
				}
				html+='</tr>';
				html+='<tr>';
				html+='	<td><b>Rechnungsnummer: </b>'+returns["invoice_nr"]+'</td>';
				html+='	<td><b>Kaufdatum: </b>'+order_firstmod+'</td>';
				if (returns["return_type"]=="exchange")
				{
					html+='	<td><b>Umtausch versendet am: </b>'+date_exchange_sent+'</td>';
				}
				else
				{
					html+='	<td></td>';
				}

				html+='</tr>';
				html+='</table>';
				
				//RETURNS DATA

					//DATUM ARTIKEL ZURÜCK 
					if (returns["date_return"]!=0) var date_return = convert_time_from_timestamp (returns["date_return"], "date"); else var date_return = '';	

					//DATUM ERSTATTUNG
					if (returns["date_refund"]!=0)	var date_refund = convert_time_from_timestamp (returns["date_refund"], "date"); else var date_refund = '';

					//ERSTATTUNGSSUMME
				//	if (returns["refund"]!=0) var refund = returns["refund"].toString().replace(".", ","); else var refund = '0,00';
				
					//if (returns["returncreditsum"]!=0) var refund = returns["returncreditsum"].toFixed(2).toString().replace(".", ","); else var refund = '0,00';
					var refund  = returns["returncreditsum"]*1;
					refund = refund.toFixed(2).toString().replace(".", ",");

					//ERSTATTUNGSSUMME FÜR VERSANDKOSTEN
					if (returns["refund_shipment"]!=0) var refund_shipment = returns["refund_shipment"].toString().replace(".", ","); else var refund_shipment = '0,00';

					//EBAY DEMAND CLOSING 1
					if (returns["ebay_demand_closing1"]!=0) var ebay_demand_closing1 = convert_time_from_timestamp (returns["ebay_demand_closing1"], "date"); else var ebay_demand_closing1 = '';

					//EBAY DEMAND CLOSING 2
					if (returns["ebay_demand_closing2"]!=0) var ebay_demand_closing2 = convert_time_from_timestamp (returns["ebay_demand_closing2"], "date"); else var ebay_demand_closing2 = '';
					
					if (returns["refund_aufid"]!=0) var refund_aufid = returns["refund_aufid"]; else refund_aufid="";
					refund_order_shipment
					if (returns["refund_order_shipment"]!=0) var refund_order_shipment = returns["refund_order_shipment"].toString().replace(".", ","); else var refund_order_shipment = '0,00';
					
				if (returns["state"] == 1)
				{
					var $disable='disabled';
				}
				else
				{
					var $disable='';
				}
				
				html+='<table style="width:100%">';
				html+='<colgroup><col style="width:25%"><col style="width:25%"><col style="width:25%"><col style="width:25%"></colgroup>';
				html+='<tr>';
				html+='	<td style="align-content:right"><small><b>Rücksendung erhalten am:</b></small>';
				html+=' <input type="text" id="order_returns_date_return" class="order_returns_update" size="10" value="'+date_return+'" '+$disable+' /></td>';
				html+='	<td><small><b>Erstattung durchgeführt am:</b></small>';
				html+=' <input type="text" id="order_returns_date_refund" class="order_returns_update" size="10" value="'+date_refund+'" '+$disable+' /></td>';
				html+='<td></td>';
//				html+='	<td><small><b>Erstattungsumme</b></small>';
//				html+=' <input type="text" id="order_returns_refund" class="order_returns_update" size="10" value="'+refund+'" '+$disable+' /><small>EUR</small></td>';
				html+='	<td><small><b>Gutschrift AufID</b></small> <input type="text" id="order_returns_refund_aufid" class="order_returns_update" size="10" value="'+refund_aufid+'" /></td>';
//				html+='	<td><small><b>Erstattung Rücksendekosten</b></small><br />';
//				html+=' <input type="text" id="order_returns_refund_shipment" class="order_returns_update" size="10" value="'+refund_shipment+'" '+$disable+' /><small>EUR</small></td>';
				html+='</tr><tr>';
				if (returns["return_type"]=="return")
				{
					html+='	<td><small><b>Aufford. Ebay-Rückgabe 1</b></small>';
					html+=' <input type="text" id="order_returns_ebay_demand_closing1" class="order_returns_update" size="10" value="'+ebay_demand_closing1+'" /></td>';
					html+='	<td><small><b>Aufford. Ebay-Rückgabe 2</b></small>';
					html+=' <input type="text" id="order_returns_ebay_demand_closing2" class="order_returns_update" size="10" value="'+ebay_demand_closing2+'" /></td>';
					html+='	<td><small><b>Ebay-Verkaufsprovision gutgeschrieben</b></small>';
					if (returns["ebay_fee_refund"]==1)
					{
						html+=' <input type="checkbox" id="order_returns_ebay_fee_refund" class="order_returns_update" value=1 checked="checked"/></td>';
					}
					else
					{
						html+=' <input type="checkbox" id="order_returns_ebay_fee_refund" class="order_returns_update" value=0 /></td>';
					}
					html+='<td></td>';
				}
				
				html+='</tr><tr>';
				html+='	<td colspan=3"><small><b>Notizen</b></small><br />';
				html+='	<textarea id="order_returns_return_note" class="order_returns_update" cols="150" rows="3">'+returns["return_note"]+'</textarea></td>';
				//RETOURLABEL
				if 	(orders[returns["order_id"]]["RetourLabelID"]!="")
				{
					html+='	<td>Retoulabel versendet <br />';
					html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Shipment_Returned.png" alt="Retourlabel versendet" title="Retourlabel versendet" /></span>';	
					html+='		<span>'+convert_time_from_timestamp(orders[returns["order_id"]]["RetourLabelTimestamp"], "complete")+'</span>';
					html+='	<br /><span><a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='+orders[returns["order_id"]]["RetourLabelID"]+'" target="_blank">'+orders[returns["order_id"]]["RetourLabelID"]+'</a></span>';
					if( typeof DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]] !== "undefined" )
					{
						var dhl_parameter = DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]]["dhl_parameter"];
						html+='	<br /><a href="javascript:send_DHLretourlabel('+returns["order_id"]+', \''+dhl_parameter+'\');">DHL-Retourlabel erneut senden</a>';
					}
					else 
					{
						html+='DHL-Retourlabel nicht möglich. Bitte Economy Select Import prüfen.';
					}
					html+='	</td>';

				}
				//else if (returns["returnitem"].length>0 && returns["state"] == 0)
				else if (returns["state"] == 0)
				{
					html+='	<td>';
					if( typeof DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]] !== "undefined" )
					{
						var dhl_parameter = DHL_RetourLabelParameter[orders[returns["order_id"]]["bill_country_code"]]["dhl_parameter"];
						html+='<a href="javascript:send_DHLretourlabel('+returns["order_id"]+', \''+dhl_parameter+'\');">DHL-Retourlabel senden</a>';
					}
					else 
					{
						html+='DHL-Retourlabel nicht möglich. Bitte Economy Select Import prüfen.';
					}
					html+='	</td>';
				}

				html+='</tr>';
				html+='</table>';
				
				//RETURNS ITEMS DATA
					//TYPE: RETURNS
				html+='<table style="width:100%">';
				
				if (returns["return_type"]=="return")
				{
					html+='<tr>';
					html+='	<th>MPN</th>';
					html+='	<th>Artikelbezeichnung</th>';
					html+='	<th>Summe</th>';
					html+='	<th>Anzahl</th>';
					html+='	<th>Rückgabegrund</th>';
					if ($disable=="") 
					{
						html+=' <th><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/note_add.png" alt="Gutschrift hinzufügen" title="Gutschrift hinzufügen" onclick="order_return_credit_add_dialog('+return_id+', '+returns["order_id"]+');"/>';
						html+=' <img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Artikel der Rückgabe hinzufügen" title="Artikel der Rückgabe hinzufügen" onclick="order_returnitem_add_dialog('+return_id+', '+returns["order_id"]+');"/></th>';
					}
					html+='</tr>';
					
					for (var i  = 0; i<returns["returnitem"].length; i++)
					{
						if (returns["returnitem"][i]["return_reason_description"]!="")
						{
							var $style='style=\'border-bottom:0\'';
						}
						else
						{
							var $style='';
						}
						html+='<tr style="background-color:#fff">';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["MPN"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["title"]+'</td>';
						var $itemtotal = returns["returnitem"][i]["price"]*returns["returnitem"][i]["amount"] / returns["returnitem"][i]["exchange_rate_to_EUR"];
						html+='	<td '+$style+' style="text-align:right">'+$itemtotal.toFixed(2).toString().replace(".", ",")+' EUR</td>';
						html+='	<td '+$style+' style="text-align:right">'+returns["returnitem"][i]["amount"]+'</td>';
						html+='	<td '+$style+'>'+ReturnsReasons[returns["returnitem"][i]["return_reason"]]["title"]+'</td>';
						//html+='	<td '+$style+'>'+returns["returnitem"][i]["return_reason"]+'</td>';
						if ($disable=="")
						{
							html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Artikel bearbeiten" title="Artikel bearbeiten" onclick="order_returnitem_update_dialog('+return_id+', '+returns["order_id"]+', '+returns["returnitem"][i]["item_id"]+');"/></td>';					html+='<tr>';
						}
						if (returns["returnitem"][i]["return_reason_description"]!="")
						{
							if ($disable=="") var $cols=5; else var $cols=4;
							html+='<tr style="background-color:#FC7;"><td colspan="'+$cols+'" style="border-top:0">'+returns["returnitem"][i]["return_reason_description"]+'</td></tr>';
						}
					}
				}
				
					//TYPE: EXCHNAGE
				if (returns["return_type"]=="exchange")
				{
					html+='<tr>';
					html+='	<th>MPN</th>';
					html+='	<th>Artikelbezeichnung</th>';
					html+='	<th>Summe</th>';
					html+='	<th>Anzahl</th>';
					html+='	<th>Umtauschgrund</th>';
					html+='	<th style="background-color:#999">U-MPN</th>';
					html+='	<th style="background-color:#999">U-Artikelbezeichnung</th>';
					html+='	<th style="background-color:#999">U-Anzahl</th>';
					if ($disable=="") 
					{
						html+=' <th><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Artikel dem Umtausch hinzufügen" title="Artikel dem Umtausch hinzufügen" onclick="order_exchangeitem_add_dialog('+return_id+', '+returns["order_id"]+');"/>';
						html+=' <img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Artikel der Rückgabe hinzufügen" title="Artikel der Rückgabe hinzufügen" onclick="order_returnitem_add_dialog('+return_id+', '+returns["order_id"]+');"/></th>';					
					}
					html+='</tr>';
					
					for (var i  = 0; i<returns["returnitem"].length; i++)
					{
						if (returns["returnitem"][i]["return_reason_description"]!="")
						{
							var $style='style=\'border-bottom:0\'';
						}
						else
						{
							var $style='';
						}
						html+='<tr style="background-color:#fff">';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["MPN"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["title"]+'</td>';
						var $itemtotal = returns["returnitem"][i]["price"]*returns["returnitem"][i]["amount"] / returns["returnitem"][i]["exchange_rate_to_EUR"];
						html+='	<td '+$style+' style="text-align:right">'+$itemtotal.toFixed(2).toString().replace(".", ",")+' EUR</td>';
						html+='	<td '+$style+' style="text-align:right">'+returns["returnitem"][i]["amount"]+'</td>';
						html+='	<td '+$style+'>'+ReturnsReasons[returns["returnitem"][i]["return_reason"]]["title"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["exchangeMPN"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["exchangetitle"]+'</td>';
						html+='	<td '+$style+'>'+returns["returnitem"][i]["exchangeamount"]+'</td>';

						//html+='	<td '+$style+'>'+returns["returnitem"][i]["return_reason"]+'</td>';
						if ($disable=="") 
						{
							html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Artikel bearbeiten" title="Artikel bearbeiten" onclick="order_returnitem_update_dialog('+return_id+', '+returns["order_id"]+', '+returns["returnitem"][i]["item_id"]+');"/></td>';					html+='<tr>';
						}
						if (returns["returnitem"][i]["return_reason_description"]!="")
						{
							if ($disable=="") var $cols=8; else var $cols=7;
							html+='<tr style="background-color:#FC7;"><td colspan="'+$cols+'" style="border-top:0">'+returns["returnitem"][i]["return_reason_description"]+'</td></tr>';
						}
					}
				}
				
				//CHECK FOR REFUNDABLE SHIPPING COSTS
				if (orders[returns["order_id"]]["shipping_costs"]!="0,00")
				{
					//GET SHIPPING CREDITS
					var $shipping_credits = 0;
					for (var i = 0; i<returns["returncredit"].length; i++)
					{
						if (returns["returncredit"][i]["reason_id"] == 2)
						{
							$shipping_credits+=	returns["returncredit"][i]["gross"]*1;
						}
						
					}

					html+='<tr>';
					html+=' <td>FRACHT</td>';
					if ($shipping_credits == 0)
					{
						html+=' <td><b>Keine Versandkosten erstattet</b> (bezahlte Versandkosten: '+orders[returns["order_id"]]["OrderShippingCosts"]+')</td>';
					}
					else
					{
						html+=' <td>bezahlte Versandkosten: '+orders[returns["order_id"]]["OrderShippingCosts"]+'</td>';
					}
					html+=' <td style="text-align:right">'+$shipping_credits.toFixed(2).toString().replace(".", ",")+' EUR</td>';
					html+=' <th></th>';
					html+=' <th></th>';
					if ($disable=="") 
					{
						html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Versandkosten erstatten" title="Versandkosten erstatten" onclick="order_return_shipping_costs_update_dialog('+return_id+');"/></td>';
					}
					html+='</tr>';
				}
				
				//CHECK FOR OTHER CREDITS
				for (var i = 0; i<returns["returncredit"].length; i++)
				{
					if (returns["returncredit"][i]["reason_id"] != 2)
					{
						html+='<tr>';
						html+=' <td>Gutschrift</td>';
						html+=' <td>';
						html+='	<b>'+returns["returncredit"][i]["reason_title_description"]+'</b>';
						html+='</td>';
						html+=' <td style="text-align:right">'+(returns["returncredit"][i]["gross"]*1).toFixed(2).toString().replace(".", ",")+' EUR</td>';
						
						html+=' <th></th>';
						html+=' <th></th>';
						if ($disable=="") 
						{
							html+='	<td '+$style+'><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Gutschrift bearbeiten" title="Gutschrift bearbeiten" onclick="order_return_credit_update_dialog('+return_id+', '+returns["returncredit"][i]["id_return_credit"]+');"/></td>';
						}
						html+='<tr>';
					}
				}
				
				//SHOW CREDIT SUM
				html+='<tr style="background-color:#fff">';
				html+=' <td></td>';
				html+='	<td><b>Gesamtsumme der Erstattung</b></td>';
				html+='	<td style="text-align:right"><b>'+refund+' EUR</b></td>';
				if ($disable=="") 
				{
					html+=' <td colspan="3"></td>';
				}
				else
				{
					html+=' <td colspan="2"></td>';
				}
				html+='</tr>';
				
				
				html+='</table>';

				$("#order_return_dialog").html(html);
				
				//SET RETURN STATE
				$("#order_returns_state").val(returns["state"]);
				
				
				//SET DATEPICKERs
				$( "#order_returns_date_return" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
				$( "#order_returns_date_refund" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });

				if (returns["return_type"]=="return")
				{
					$( "#order_returns_ebay_demand_closing1" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					$( "#order_returns_ebay_demand_closing2" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
				}

				//BIND EVENTHANDLER ONCHANGE
				$(".order_returns_update").bind("change", function(e) {
					order_returns_update2($(this).attr("id"), return_id);
				});


				//BIND EVENTHANDLER "ENTER"
				$("#order_returns_billnumber").bind("keypress", function(e) {
					if(e.keyCode==13)
					{
						order_returns_update2($(this).attr("id"), return_id);
					}
				});

				if (returns["return_type"]=="return")
				{
					var $dialogtitle="Rückgabe bearbeiten";
				}
				else
				{
					var $dialogtitle="Umtausch bearbeiten";
				}
				
				$("#order_return_dialog").dialog
				({	buttons:
					[
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeOnEscape: false,
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:$dialogtitle,
					width:1200
				});		
				
			}
		);
	}
	
	function order_return_shipping_costs_update_dialog($return_id)
	{
		
		if ($("#order_return_shipping_costs_update_dialog").length == 0)
		{
			$("body").append('<div id="order_return_shipping_costs_update_dialog" style="display:none">');
		}

		//SET SHIPPING CREDIT SUGGESTION
		var $shipping_credit_suggestion = 0;
		$shipping_credit_suggestion = ((orders[returns["order_id"]]["OrderShippingCosts"].replace(/,/g, "."))*1);
		var $shipping_credit_suggestion_string = $shipping_credit_suggestion.toFixed(2).toString().replace(".", ",");
		
		var $html='';
		$html+='<table style="width:100%">';
		$html+='<tr>';
		$html+='	<th>bezahlte Versandkosten</th>';
		$html+='	<td>'+orders[returns["order_id"]]["OrderShippingCosts"]+'</td>';
		$html+='</tr>';
		$html+='<tr>';
		$html+='	<th>Versandkosten erstatten</th>';
		$html+='	<td><input type="text" id="order_return_shipping_costs_update_dialog_shipping_credit" size="10" value="'+$shipping_credit_suggestion_string+'" /></td>';
		$html+='</tr>';
		$html+='</table>';
		
		$("#order_return_shipping_costs_update_dialog").html($html);
		
		$("#order_return_shipping_costs_update_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { order_return_shipping_costs_update($return_id);} },
				{ text: "Abbrechen", click: function() { $(this).dialog("close");} }
			],
			closeOnEscape: false,
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Versandkosten erstatten",
			width:400
		});		
	}
	
	function order_return_shipping_costs_update($return_id)
	{
		
		var $shipping_credit = $("#order_return_shipping_costs_update_dialog_shipping_credit").val();
		
		if ($shipping_credit == "" || $shipping_credit == 0 )
		{
			alert("Bitte einen gültigen Betrag eingeben");
			$("#order_return_shipping_costs_update_dialog_shipping_credit").focus();
			return;
		}
		
		// GET NEW SHIPPING CREDITS
		$shipping_credit = $shipping_credit.replace(/,/g, ".")*1;

		//GET SHIPPING COSTS
		var $shipping_costs = orders[returns["order_id"]]["OrderShippingCosts"].replace(/,/g, ".")*1;
		
		// CHECK IF SUM OF CREDITS <= SHIPPINGCOSTS
		if ($shipping_costs <  $shipping_credit)
		{
			var $max_credit = $shipping_costs;
			$max_credit = $max_credit.toFixed(2).toString().replace(".", ",");
			alert("Betrag kann nicht gutgeschrieben werden. Maximalbetrag: "+$max_credit);
			$("#order_return_shipping_costs_update_dialog_shipping_credit").val($max_credit);
			$("#order_return_shipping_costs_update_dialog_shipping_credit").focus();
			return;
		}
		
		var $shipping_credit_net = 0;
		
		if (orders[returns["order_id"]]["VAT"]!=0)
		{
			$shipping_credit_net = $shipping_credit;
		}
		else
		{
			$shipping_credit_net= $shipping_credit/((orders[returns["order_id"]]["VAT"]/100)+1);
		}
		
		var post_object 			= new Object();
		post_object['API'] 			= 'shop';
		post_object['APIRequest'] 	= 'OrderReturnCreditUpdate';
		post_object['return_id'] 	= $return_id;
		post_object['reason_id'] 	= 2;
		post_object['gross'] 		= $shipping_credit;
		post_object['net'] 			= $shipping_credit_net.toFixed(2);
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); wait_dialog_hide(); return; }
	
	
			$("#order_return_shipping_costs_update_dialog").dialog("close");
			order_returns_dialog2($return_id);
		});
		
	}
	
	function order_return_credit_add_dialog($return_id)
	{
		// GET CREDIT REASONS - WITHOUT "Umtausch/Rückgabe" & "Versandkosten"
		var $reasonselect = '';

		wait_dialog_show();
		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataSelect';
		postfields['table']="shop_orders_credits_reasons";
		postfields['db'] = "dbshop";
		postfields['where'] =  "WHERE NOT id_reason = 1 AND NOT id_reason = 2";
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			//show_status2($data);
			//return;
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			//show_status2($data);
			
			$reasonselect+= '<select id = "order_return_credit_add_reason" size = "1">';
			$reasonselect+= '	<option value = 0>Bitte Gutschriftgrund angeben</option>';
			$xml.find("shop_orders_credits_reasons").each(function()
			{
				$reasonselect+= '	<option value = '+$(this).find("id_reason").text()+'>'+$(this).find("title").text()+'</option>';
			});
			$reasonselect+= '</select>';
			
		
			var $html = '';
			$html+= '<table style="width:100%">';
			$html+=	'<tr>';
			$html+= '	<th>Gutschrift</th>';
			$html+=	'	<td>'+$reasonselect+'</td>';
			$html+=	'</tr><tr>';
			$html+=	'	<th>Gutschriftdetails</th>';
			$html+=	'	<td><textarea cols = "30" rows = "4" id = "order_return_credit_add_reason_detail"></textarea></td>';
			$html+=	'</tr><tr>';
			$html+=	'	<th>Gutschriftbetrag</th>';
			$html+=	'	<td><input type = "text" id = "order_return_credit_add_reason_creditgross" size = "10" value = "0,00"/> <b>EUR</b></td>';
			$html+=	'</tr>';
			$html+=	'</table>';
			
			if ($("#order_return_credit_add_dialog").length == 0)
			{
				$("body").append('<div id="order_return_credit_add_dialog" style="display:none">');
			}
	
			$("#order_return_credit_add_dialog").html($html);
			
			$("#order_return_credit_add_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { order_return_credit_add($return_id);} },
					{ text: "Abbrechen", click: function() { $(this).dialog("close");} }
				],
				closeOnEscape: false,
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Gutschrift erstellen",
				width:400
			});		
		});

	}

	function order_return_credit_add($return_id)
	{
		var $creditgross = ($("#order_return_credit_add_reason_creditgross").val().replace(/,/g, "."))*1;
		
		if ($creditgross == 0 || isNaN($creditgross) )
		{
			alert("Bitte eine korrekte Gutschriftsumme größer 0 angeben!");
			$("#order_return_credit_add_reason_creditgross").focus();
			return;
		}
		
		//CHECK IF REASON IS SELECTED
		if ($("#order_return_credit_add_reason").val() == 0)
		{
			alert("Bitte eine Gutschriftart festlegen!");
			$("#order_return_credit_add_reason").focus();
			return;
		}
		//CHECK FOR DETAIL FOR "Sonstige"
		if ($("#order_return_credit_add_reason").val() == 4 && $("#order_return_credit_add_reason_detail").val() == "")
		{
			alert("Bitte eine Erläuterung zur Gutschrift `Sonstige` angeben!");
			$("#order_return_credit_add_reason_detail").focus();
			return;
		}

		$creditnet= $creditgross/((orders[returns["order_id"]]["VAT"]/100)+1);
		
		var post_object 					= new Object();
		post_object['API'] 					= 'shop';
		post_object['APIRequest'] 			= 'OrderReturnCreditUpdate';
		post_object['return_id'] 			= $return_id;
		post_object['reason_id'] 			= $("#order_return_credit_add_reason").val();
		post_object['reason_description'] 	= $("#order_return_credit_add_reason_detail").val();
		post_object['gross'] 				= $creditgross;
		post_object['net'] 					= $creditnet.toFixed(2);
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); wait_dialog_hide(); return; }
	
			$("#order_return_credit_add_dialog").dialog("close");
			order_returns_dialog2($return_id);
		});
		
	}



	function order_return_credit_update_dialog($return_id, $returncredit_id)
	{
		//GET CREDIT DATA
		wait_dialog_show();
		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataSelect';
		postfields['table']="shop_returns_credits";
		postfields['db'] = "dbshop";
		postfields['where'] =  "WHERE id_return_credit = "+$returncredit_id;
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			//show_status2($data);
			//return;
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
		
			// GET CREDIT REASONS - WITHOUT "Umtausch/Rückgabe" & "Versandkosten"
			var $reasonselect = '';
	
			var postfields = new Object();
			postfields['API'] = 		'cms';
			postfields['APIRequest'] =	'TableDataSelect';
			postfields['table']="shop_orders_credits_reasons";
			postfields['db'] = "dbshop";
			postfields['where'] =  "WHERE NOT id_reason = 1 AND NOT id_reason = 2";
			$.post("<?php echo PATH; ?>soa2/", postfields, function($data2)
			{
				wait_dialog_hide();
				try { $xml2 = $($.parseXML($data2)); } catch ($err) { show_status($err.message); return; }
				$ack = $xml2.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data2); return; }
				
				$reasonselect+= '<select id = "order_return_credit_update_reason" size = "1">';
				$reasonselect+= '	<option value = 0>Bitte Gutschriftgrund angeben</option>';
				$xml2.find("shop_orders_credits_reasons").each(function()
				{
					$reasonselect+= '	<option value = '+$(this).find("id_reason").text()+'>'+$(this).find("title").text()+'</option>';
				});
				$reasonselect+= '</select>';
				
			
				var $html = '';
				$html+= '<table style="width:100%">';
				$html+=	'<tr>';
				$html+= '	<th>Gutschrift</th>';
				$html+=	'	<td>'+$reasonselect+'</td>';
				$html+=	'</tr><tr>';
				$html+=	'	<th>Gutschriftdetails</th>';
				$html+=	'	<td><textarea cols = "30" rows = "4" id = "order_return_credit_update_reason_detail"></textarea></td>';
				$html+=	'</tr><tr>';
				$html+=	'	<th>Gutschriftbetrag</th>';
				$html+=	'	<td><input type = "text" id = "order_return_credit_update_reason_creditgross" size = "10" value = "0,00"/> <b>EUR</b></td>';
				$html+=	'</tr>';
				$html+=	'</table>';
				
				if ($("#order_return_credit_update_dialog").length == 0)
				{
					$("body").append('<div id="order_return_credit_update_dialog" style="display:none">');
				}
		
				$("#order_return_credit_update_dialog").html($html);
				
				//FELDER BELEGEN
					//GUTSCHRIFT ART
					$("#order_return_credit_update_reason").val($xml.find("reason_id").text());

					//GUTSCHRIFT DETAIL
					$("#order_return_credit_update_reason_detail").val($xml.find("reason_description").text());

					//GUTSCHRIFT Gross
					$("#order_return_credit_update_reason_creditgross").val($xml.find("gross").text().replace(".", ","));
				
				$("#order_return_credit_update_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { order_return_credit_update($return_id, $returncredit_id);} },
						{ text: "Abbrechen", click: function() { $(this).dialog("close");} }
					],
					closeOnEscape: false,
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Gutschrift bearbeiten",
					width:400
				});		
			});
		});
	}

	function order_return_credit_update($return_id, $returncredit_id)
	{
		var $creditgross = ($("#order_return_credit_update_reason_creditgross").val().replace(/,/g, "."))*1;
		
		if ($creditgross == 0 || isNaN($creditgross) )
		{
			alert("Bitte eine korrekte Gutschriftsumme größer 0 angeben!");
			$("#order_return_credit_update_reason_creditgross").focus();
			return;
		}
		
		//CHECK IF REASON IS SELECTED
		if ($("#order_return_credit_update_reason").val() == 0)
		{
			alert("Bitte eine Gutschriftart festlegen!");
			$("#order_return_credit_update_reason").focus();
			return;
		}
		//CHECK FOR DETAIL FOR "Sonstige"
		if ($("#order_return_credit_update_reason").val() == 4 && $("#order_return_credit_update_reason_detail").val() == "")
		{
			alert("Bitte eine Erläuterung zur Gutschrift `Sonstige` angeben!");
			$("#order_return_credit_update_reason_detail").focus();
			return;
		}

		$creditnet= $creditgross/((orders[returns["order_id"]]["VAT"]/100)+1);
		
		var post_object 					= new Object();
		post_object['API'] 					= 'shop';
		post_object['APIRequest'] 			= 'OrderReturnCreditUpdate';
		post_object['return_credit_id'] 	= $returncredit_id;
		post_object['return_id'] 			= $return_id;
		post_object['reason_id'] 			= $("#order_return_credit_update_reason").val();
		post_object['reason_description'] 	= $("#order_return_credit_update_reason_detail").val();
		post_object['gross'] 				= $creditgross;
		post_object['net'] 					= $creditnet.toFixed(2);
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); wait_dialog_hide(); return; }
	
			$("#order_return_credit_update_dialog").dialog("close");
			order_returns_dialog2($return_id);
		});
	}
	
	
	function order_returns_update(element_id)
	{
		var return_id = returns["id_return"];
		//STRIP FIELDNAME
		var element = element_id.substr(14);
		// DATE TO TIMESTAMP
		if (element == "date_return" || element == "date_refund" || element == "ebay_demand_closing1" || element == "ebay_demand_closing2")
		{
			var fieldvalue = Math.round($("#"+element_id).datepicker('getDate') / 1000);
		}
		else if (element == "refund" || element == "refund_shipment" || element == "refund_order_shipment")
		{
			var fieldvalue = ($("#"+element_id).val().replace(/,/g, "."))*1;
		}
		else if (element == "state")
		{
		//	if ($("#order_returns_ebay_fee_refund").is(":checked")) fieldvalue=1; else fieldvalue=0;
			var fieldvalue = $("#"+element_id).val();
		}
		else if(element == "refund_aufid")
		{
			var fieldvalue = $("#"+element_id).val();
		}

		else
		{
			var fieldvalue=$("#"+element_id).val();
		}

		//CHECK IF VALUE HAS CHANGED
		if (fieldvalue!=returns[element])
		{
			//SET ORDEREVENT ID 
			var $event_id = 0;
			switch (element)
			{
				case "refund": $event_id = 110; break;
				case "refund_shipment": $event_id = 111; break;
				case "date_return": $event_id= 112; break;
				case "date_refund": $event_id= 113; break;
				case "state": $event_id= 114; break;
				case "ebay_demand_closing1": $event_id= 115; break;
				case "ebay_demand_closing2": $event_id= 116; break;
				case "refund_order_shipment": $event_id= 117; break;
				default: $event_id=109;break;
			}
			
			
			//SET EVENT DATA
			var eventmsg='';
			eventmsg+='<data>';
			eventmsg+='	<old>';
			eventmsg+='		<'+element+'><![CDATA['+returns[element]+']]></'+element+'>';
			eventmsg+='	</old>';
			eventmsg+='	<new>';
			eventmsg+='		<'+element+'><![CDATA['+fieldvalue+']]></'+element+'>';
			eventmsg+='	</new>';
			eventmsg+='<data>';
			
			//CHECK: WRITE CREDIT?
			if ($("#order_returns_refund").val()!="" && $("#order_returns_refund").val()!="0,00" && $("#order_returns_refund_aufid").val()!="" && $("#order_returns_refund_aufid").val()!=0 && $("#order_returns_date_refund").val()!="")
			{
				var write_credit = true;
			}
			else
			{
				var write_credit = false;
			}
			
			field = new Object();
			
			field["API"]="shop";
			field["APIRequest"]="OrderReturnUpdate";
			field["SELECTOR_id_return"]=returns["id_return"];
			field[element]=fieldvalue;
		
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/index.php", 
			field,
			function ($data)
			{
				
				//wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
			//	alert("OK1");
				//WRITE EVENT
				field = new Object();
				
				field["API"]="shop";
				field["APIRequest"]="OrderEventSet";
				field["order_id"]=returns["order_id"];
				field["eventtype_id"]=$event_id;
				field["data"]=eventmsg;
				wait_dialog_show();
				$.post("<?php echo PATH; ?>soa2/index.php", 
				field,
				function ($data)
				{
					
					try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
					var $Ack = $xml.find("Ack").text();
					if ($Ack!="Success") {show_status2($data); wait_dialog_hide(); return;}
					
					var id_event = $xml.find("id_event").text();
			//	alert("OK2");
			
					if (write_credit)
					{
						var $refund_gross = ($("#order_returns_refund").val().replace(/,/g, "."))*1;
						var $refund_net = $refund_gross  /((orders[returns["order_id"]]["VAT"]/100)+1);
						//WRITE CREDIT
						field = new Object();
						
						field["API"]="shop";
						field["APIRequest"]="OrderCreditSet";
						field["auf_id"]=$("#order_returns_refund_aufid").val();
						field["order_id"]=returns["order_id"];
						field["return_id"]=return_id;
						field["reason_id"]=1;
						field["gross"]=$refund_gross;
						field["net"]=$refund_net;
					
						$.post("<?php echo PATH; ?>soa2/index.php", 
						field,
						function ($data)
						{
							
							//wait_dialog_hide();
							try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
							var $Ack = $xml.find("Ack").text();
							if ($Ack!="Success") {show_status2($data); wait_dialog_hide(); return;}

							//IF REFUND || REFUND_SHIPMENT -> BUCHUNG
							if (element =="state" && fieldvalue ==1)
							{
								//CALL PAYMENTNOTIFICATIONHANDLER
								field = new Object();
								
								field["API"]="payments";
								field["APIRequest"]="PaymentNotificationHandler";
								field["mode"]="OrderReturn";
								field["orderid"]=returns["order_id"];
								field["returnid"]=return_id;
								field["order_event_id"]=id_event;
								
							//	wait_dialog_show();
								$.post("<?php echo PATH; ?>soa2/index.php", 
								field,
								function ($data)
								{
									
									wait_dialog_hide();
									try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
									var $Ack = $xml.find("Ack").text();
									if ($Ack!="Success") {show_status2($data); wait_dialog_hide(); return;}
									
								//	alert("OK3");
									show_status("Änderung durchgeführt, Rückzahlung wurde gebucht!");
									order_returns_dialog(returns["id_return"]);
								});
							
							}
							else
							
							{
								order_returns_dialog(returns["id_return"]);
								wait_dialog_hide();
								return;
							}
						});

					} // IF WRITE CREDIT
					order_returns_dialog(returns["id_return"]);
					wait_dialog_hide();

				}); // SET ORDEREVENT
			}); // WRITE ORDERRETURNUPDATE
		}
	}

	function order_returns_update2(element_id)
	{
		var return_id = returns["id_return"];
		//STRIP FIELDNAME
		var element = element_id.substr(14);
		// DATE TO TIMESTAMP
		if (element == "date_return" || element == "date_refund" || element == "ebay_demand_closing1" || element == "ebay_demand_closing2")
		{
			var fieldvalue = Math.round($("#"+element_id).datepicker('getDate') / 1000);
		}
		else if (element == "refund" || element == "refund_shipment" || element == "refund_order_shipment")
		{
			var fieldvalue = ($("#"+element_id).val().replace(/,/g, "."))*1;
		}
		else if (element == "state")
		{
		//	if ($("#order_returns_ebay_fee_refund").is(":checked")) fieldvalue=1; else fieldvalue=0;
			var fieldvalue = $("#"+element_id).val();
		}
		else if(element == "refund_aufid")
		{
			var fieldvalue = $("#"+element_id).val();
		}

		else
		{
			var fieldvalue=$("#"+element_id).val();
		}

		//CHECK IF VALUE HAS CHANGED
		if (fieldvalue!=returns[element])
		{
			//SET ORDEREVENT ID 
			var $event_id = 0;
			switch (element)
			{
				case "refund": $event_id = 110; break;
				case "refund_shipment": $event_id = 111; break;
				case "date_return": $event_id= 112; break;
				case "date_refund": $event_id= 113; break;
				case "state": $event_id= 114; break;
				case "ebay_demand_closing1": $event_id= 115; break;
				case "ebay_demand_closing2": $event_id= 116; break;
				case "refund_order_shipment": $event_id= 117; break;
				default: $event_id=109;break;
			}
			
			
			//SET EVENT DATA
			var eventmsg='';
			eventmsg+='<data>';
			eventmsg+='	<old>';
			eventmsg+='		<'+element+'><![CDATA['+returns[element]+']]></'+element+'>';
			eventmsg+='	</old>';
			eventmsg+='	<new>';
			eventmsg+='		<'+element+'><![CDATA['+fieldvalue+']]></'+element+'>';
			eventmsg+='	</new>';
			eventmsg+='<data>';
			
			//CHECK: WRITE CREDIT?
			if (returns["returncreditsum"] != 0 && $("#order_returns_refund_aufid").val()!="" && $("#order_returns_refund_aufid").val()!=0 && $("#order_returns_date_refund").val()!="" && $("#order_returns_state").val() == 1)
			{
				var write_credit = true;
			}
			else
			{
				var write_credit = false;
			}
			
			field = new Object();
			
			field["API"]="shop";
			field["APIRequest"]="OrderReturnUpdate";
			field["SELECTOR_id_return"]=returns["id_return"];
			field[element]=fieldvalue;
		
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/index.php", 
			field,
			function ($data)
			{
				
				//wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
			//	alert("OK1");
				//WRITE EVENT
				field = new Object();
				
				field["API"]="shop";
				field["APIRequest"]="OrderEventSet";
				field["order_id"]=returns["order_id"];
				field["eventtype_id"]=$event_id;
				field["data"]=eventmsg;
				wait_dialog_show();
				$.post("<?php echo PATH; ?>soa2/index.php", 
				field,
				function ($data)
				{
					
					try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
					var $Ack = $xml.find("Ack").text();
					if ($Ack!="Success") {show_status2($data); wait_dialog_hide(); return;}
					
					var id_event = $xml.find("id_event").text();
			//	alert("OK2");
			
					if (write_credit)
					{
						var $credit_gross = returns["returncreditsum"];
						var $credit_net = returns["returncreditsum"] / ((orders[returns["order_id"]]["VAT"]/100)+1);
						
						//WRITE CREDIT
						field = new Object();
						
						field["API"]="shop";
						field["APIRequest"]="OrderCreditSet";
						field["auf_id"]=$("#order_returns_refund_aufid").val();
						field["order_id"]=returns["order_id"];
						field["return_id"]=return_id;
						field["reason_id"]=1;
						//field["gross"]=($("#order_returns_refund").val().replace(/,/g, "."))*1;
						field["gross"]= $credit_gross;
						field["net"]= $credit_net;
					
						$.post("<?php echo PATH; ?>soa2/index.php", 
						field,
						function ($data)
						{
							
							//wait_dialog_hide();
							try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
							var $Ack = $xml.find("Ack").text();
							if ($Ack!="Success") {show_status2($data); wait_dialog_hide(); return;}

							//IF REFUND || REFUND_SHIPMENT -> BUCHUNG
							if (element =="state" && fieldvalue ==1)
							{
								//CALL PAYMENTNOTIFICATIONHANDLER
								field = new Object();
								
								field["API"]="payments";
								field["APIRequest"]="PaymentNotificationHandler";
								field["mode"]="OrderReturn";
								field["orderid"]=returns["order_id"];
								field["returnid"]=return_id;
								field["order_event_id"]=id_event;
								
							//	wait_dialog_show();
								$.post("<?php echo PATH; ?>soa2/index.php", 
								field,
								function ($data)
								{
									
									wait_dialog_hide();
									try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
									var $Ack = $xml.find("Ack").text();
									if ($Ack!="Success") {show_status2($data); wait_dialog_hide(); return;}
									
								//	alert("OK3");
									show_status("Änderung durchgeführt, Rückzahlung wurde gebucht!");
									order_returns_dialog(returns["id_return"]);
								});
							
							}
							else
							
							{
								order_returns_dialog(returns["id_return"]);
								wait_dialog_hide();
								return;
							}
						});

					} // IF WRITE CREDIT
					order_returns_dialog2(returns["id_return"]);
					wait_dialog_hide();

				}); // SET ORDEREVENT
			}); // WRITE ORDERRETURNUPDATE
		}
	}
	
	
	function order_returns_state_check()
	{
		//FALL GESCHLOSSEN
		if ($("#order_returns_state").val()==1)
		{
			//CHECK MANDANTORY FIELDS
				//RÜCKSENDUNG ERHALTEN
			if ($("#order_returns_date_return").val()==0 || $("#order_returns_date_return").val()=="")
			{
				$("#order_returns_date_return").focus();
				msg_box("Der Fall kann nicht geschlossen werden, da noch keine Rücksendung durch den Kunden vermerkt wurde!");
				$("#order_returns_state").val(0);
				return;
			}
				//RÜCKSENDUNG ERHALTEN
			if ($("#order_returns_date_refund").val()==0 || $("#order_returns_date_refund").val()=="")
			{
				$("#order_returns_date_refund").focus();
				msg_box("Der Fall kann nicht geschlossen werden, da noch keine Erstattung/Gutschrift vermerkt wurde!");
				$("#order_returns_state").val(0);
				return;
			}
				//AUFID gespeichert
			if ($("#order_returns_refund_aufid").val()=="")
			{
				$("#order_returns_refund_aufid").focus();
				msg_box("Der Fall kann nicht geschlossen werden, da keine IDIMS Erstattungs AUF ID vermerkt wurde!");
				$("#order_returns_state").val(0);
				return;
			}

				// CHECK OB ARTIKEL MIT RÜCKGABEGRUND "SONSTIGE" EINE BESCHREIBUNG DER RÜCKSENDUNG HABEN
			for (var i  = 0; i<returns["returnitem"].length; i++)
			{
				if (returns["returnitem"][i]["return_reason"]==100 && returns["returnitem"][i]["return_reason_description"]=="")
				{
					msg_box("Der Fall kann nicht geschlossen werden, da nicht  bei allen Artikeln mit dem Rückgabegrund ´sonstige´ eine Detailinfo vorliegt!");
					$("#order_returns_state").val(0);
					return;
				}
			}
						
		}

		order_returns_update("order_returns_state");
		update_view(returns["order_id"]);		

	}

	function order_returns_state_check2()
	{
		//FALL GESCHLOSSEN
		if ($("#order_returns_state").val()==1)
		{
			//CHECK MANDANTORY FIELDS
				//RÜCKSENDUNG ERHALTEN
			if ($("#order_returns_date_return").val()==0 || $("#order_returns_date_return").val()=="")
			{
				$("#order_returns_date_return").focus();
				msg_box("Der Fall kann nicht geschlossen werden, da noch keine Rücksendung durch den Kunden vermerkt wurde!");
				$("#order_returns_state").val(0);
				return;
			}
				//RÜCKSENDUNG ERHALTEN
			if ($("#order_returns_date_refund").val()==0 || $("#order_returns_date_refund").val()=="")
			{
				$("#order_returns_date_refund").focus();
				msg_box("Der Fall kann nicht geschlossen werden, da noch keine Erstattung/Gutschrift vermerkt wurde!");
				$("#order_returns_state").val(0);
				return;
			}
				//AUFID gespeichert
			if ($("#order_returns_refund_aufid").val()=="")
			{
				$("#order_returns_refund_aufid").focus();
				msg_box("Der Fall kann nicht geschlossen werden, da keine IDIMS Erstattungs AUF ID vermerkt wurde!");
				$("#order_returns_state").val(0);
				return;
			}

				// CHECK OB ARTIKEL MIT RÜCKGABEGRUND "SONSTIGE" EINE BESCHREIBUNG DER RÜCKSENDUNG HABEN
			for (var i  = 0; i<returns["returnitem"].length; i++)
			{
				if (returns["returnitem"][i]["return_reason"]==100 && returns["returnitem"][i]["return_reason_description"]=="")
				{
					msg_box("Der Fall kann nicht geschlossen werden, da nicht  bei allen Artikeln mit dem Rückgabegrund ´sonstige´ eine Detailinfo vorliegt!");
					$("#order_returns_state").val(0);
					return;
				}
			}
						
		}

		order_returns_update2("order_returns_state");
	//	update_view(returns["order_id"]);		

	}



	function order_returnitem_add_dialog(return_id, orderid)
	{
		//GET ORDER ITEMS
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet", OrderID:returns["order_id"]},
		function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			if ($("#order_returnitem_add_dialog").length == 0)
			{
				$("body").append('<div id="order_returnitem_add_dialog" style="display:none">');
			}
			
			var html = '';
			
			html+='<table>';
			html+='<tr>';
			html+='	<th>Artikel</th>';
			html+='	<td><select id="order_returnitem_add_item" size="1">';
			html+='		<option value="">Bitte einen Artikel wählen</option>';
			// ES WERDEN NUR ARTIKEL ANGEZEIGT, DIE NOCH NICHT IN DER RÜCKGABE STEHEN
			
			$xml.find("Item").each( function ()
			{
				if (typeof(returns["returnitem"])!=="undefined")
				{
					var matchingItem = false;
					for (var i  = 0; i<returns["returnitem"].length; i++)
					{
//						if ($(this).find("OrderItemMPN").text()==returns["returnitem"][i]["MPN"]) matchingItem = true;
						if ($(this).find("OrderItemID").text()==returns["returnitem"][i]["shop_orders_items_id"]) matchingItem = true;
					}
					//if (!matchingItem) html+='		<option value="'+$(this).find("OrderItemItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';
					if (!matchingItem) html+='		<option value="'+$(this).find("OrderItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';
				}
				else
				{
					html+='		<option value="'+$(this).find("OrderItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';					
				}
			});
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Anzahl</th>';
			html+='	<td><input type="text" size="2" id="order_returnitem_add_amount" value = 1 /><input type="hidden" id="order_returnitem_add_amount_max" value ='+$(this).find("OrderItemAmount").text()+' /></td>';
			html+='</tr><tr>';
			html+='	<th>Rückgabegrund</th>';
			html+='	<td><select id="order_returnitem_add_reason" size="1">';
			html+='		<option value=0>Bitte Umtauschgrund wählen</option>';
			
			$.each(ReturnsReasons, function($key, returnreason)
			{
				
				html+='<option value='+returnreason["id_returnreason"]+'  title="'+returnreason["description"]+'">'+returnreason["title"]+'</option>';
			});
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Rückgabeerläuterung</th>';
			html+='	<td><textarea id="order_returnitem_add_reason_description" cols="20" rows="5"></textarea></td>';
			html+='</tr>';
			html+='</table>';
			
			$("#order_returnitem_add_dialog").html(html);

			if (!$("#order_returnitem_add_dialog").is(":visible"))
			{
				$("#order_returnitem_add_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { order_returnitem_add(return_id, orderid);} },
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Rückgabe Artikel hinzufügen",
					width:500
				});		
			}
		});
	}
	
	function order_exchangeitem_add_dialog(return_id, orderid)
	{
		//GET ORDER ITEMS
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet", OrderID:returns["order_id"]},
		function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			if ($("#order_exchangeitem_add_dialog").length == 0)
			{
				$("body").append('<div id="order_exchangeitem_add_dialog" style="display:none">');
			}
			
			var html = '';
			
			html+='<table>';
			html+='<tr>';
			html+='	<th>Artikel</th>';
			html+='	<td colspan="2"><select id="order_exchangeitem_add_item" size="1" onchange="order_exchangeitem_add_setprices(this.value);">';
			html+='		<option value="">Bitte einen Artikel wählen</option>';
			
			
			orderitems = null;
			orderitems = new Object;
			
			$xml.find("Item").each( function ()
			{
				var ItemID = $(this).find("OrderItemItemID").text();
				orderitems[ItemID] = new Object;
				orderitems[ItemID]["id"] = $(this).find("OrderItemID").text();
				orderitems[ItemID]["amount"] = $(this).find("OrderItemAmount").text();
				orderitems[ItemID]["CurrencyCode"] = $(this).find("OrderItemCurrency_Code").text();
				orderitems[ItemID]["ExchangeRateToEUR"] = $(this).find("OrderItemExchangeRateToEUR").text();
//				orderitems[ItemID]["PriceGross"] = $(this).find("orderItemPriceGross").text();
				orderitems[ItemID]["PriceNet"] = $(this).find("orderItemPriceNet").text();
	
				// ES WERDEN NUR ARTIKEL ANGEZEIGT, DIE NOCH NICHT IN DER RÜCKGABE STEHEN
				if (typeof(returns["returnitem"])!=="undefined")
				{
					var matchingItem = false;
					for (var i  = 0; i<returns["returnitem"].length; i++)
					{
	//					if ($(this).find("OrderItemMPN").text()==returns["returnitem"][i]["MPN"]) matchingItem = true;
							if ($(this).find("OrderItemID").text()==returns["returnitem"][i]["shop_orders_items_id"]) matchingItem = true;

					}
					if (!matchingItem) html+='		<option value="'+$(this).find("OrderItemItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';
				}
				else
				{
					html+='		<option value="'+$(this).find("OrderItemItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';					
				}
			});
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Anzahl</th>';
			html+='	<td colspan="2"><input type="text" id="order_exchangeitem_add_amount" size="3" /></td>';
			html+='</tr><tr>';
			html+='	<th>Umtauschgrund</th>';
			html+='	<td colspan="2"><select id="order_exchangeitem_add_reason" size="1">';
			html+='		<option value=0>Bitte Umtauschgrund wählen</option>';
			
			$.each(ReturnsReasons, function($key, returnreason)
			{
				
				html+='<option value='+returnreason["id_returnreason"]+'  title="'+returnreason["description"]+'">'+returnreason["title"]+'</option>';
			});
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Umtauscherläuterung</th>';
			html+='	<td colspan="2"><textarea id="order_exchangeitem_add_reason_description" cols="20" rows="5"></textarea></td>';
			html+='</tr><tr>';
			html+='	<th></th>';
			html+=' <th>brutto</th>';
			html+=' <th>netto</th>';
			html+='</tr><tr>';
			html+='	<th>Artikel Einzel-VK-Preis</th>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span id="order_exchangeitem_add_FC_price" size="6" ></span></td>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span id="order_exchangeitem_add_FC_netto" size="6" ></span></td>';
			html+='</tr><tr class="exchangeitem_add_EUR_col" style="display:none">';
			html+='	<th>Artikel Einzel-VK-Preis</th>';
			html+='	<td>EUR <span id="order_exchangeitem_add_EUR_price" size="6" ></span></td>';
			html+='	<td>EUR <span id="order_exchangeitem_add_EUR_netto" size="6" ></span></td>';
			html+='</tr><tr>';
			html+='	<th>Artikel Gesamt-VK-Preis</th>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span style="background-color:fff" id="order_exchangeitem_add_FC_price_total"></span></td>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span style="background-color:fff" id="order_exchangeitem_add_FC_netto_total"></span></td>';
			html+='</tr><tr class="exchangeitem_add_EUR_col" style="display:none">';
			html+='	<th>Artikel Gesamt-VK-Preis</th>';
			html+='	<td>EUR <span style="background-color:fff" id="order_exchangeitem_add_EUR_price_total"></span></td>';
			html+='	<td>EUR <span style="background-color:fff" id="order_exchangeitem_add_EUR_netto_total"></span></td>';
			html+='	<input type="hidden" id = "order_exchangeitem_exchangerate">';
			html+='</tr><tr>';
			html+='	<th>Umtausch-MPN</th>';
			html+='	<td colspan="2"><input type="text" id="order_exchangeitem_add_exchange_MPN" size="10" onchange="order_exchangeitem_changeMPN(this.value)" />';
				html+='<input type="hidden" id="order_exchangeitem_add_exchange_itemID" /></td>';
			html+='</tr><tr>';
			html+='	<th>Umtausch-Artikelbezeichnung</th>';
			html+='	<td colspan="2"><span id="order_exchangeitem_add_exchange_title"></span></td>';
			html+='</tr><tr>';
			html+='	<th>Umtausch-Anzahl</th>';
			html+='	<td colspan="2"><input type="text" id="order_exchangeitem_add_exchange_amount" size="3" onchange="exchange_orderpositions_setPrices(\'amount\');"/></td>';
			html+='</tr><tr>';
			html+='	<th></th>';
			html+=' <th>brutto</th>';
			html+=' <th>netto</th>';
			html+='</tr><tr>';
// VORBELEGUNG DURCH LISTENPREIS AUS SHOP
	// ANHAND GETITEM-SERVICE??			
			html+='	<th>Umtausch-Artikel Einzel-VK-Preis</th>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <input type="text" id="order_exchangeitem_add_exchange_FC_price" size="6" onchange="ex_get_netto_from_FCbrutto(this.value)" /></td>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <input type="text" id="order_exchangeitem_add_exchange_FC_netto" size="6" onchange="ex_get_netto_from_FCnetto(this.value)" /></td>';
			html+='</tr><tr class="exchangeitem_add_EUR_col" style="display:none">';
			html+='	<th>Umtausch-Artikel Einzel-VK-Preis</th>';
			html+='	<td>EUR <input type="text" id="order_exchangeitem_add_exchange_EUR_price" size="6" /></td>';
			html+='	<td>EUR <input type="text" id="order_exchangeitem_add_exchange_EUR_netto" size="6" /></td>';
			html+='</tr><tr>';
			html+='	<th>Umtausch-Artikel Gesamt-VK-Preis</th>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span style="background-color:fff" id="order_exchangeitem_add_exchange_FC_price_total"></span></td>';
			html+='	<td><span class="order_exchangeitem_add_CurrencyCode"></span> <span style="background-color:fff" id="order_exchangeitem_add_exchange_FC_netto_total"></span></td>';
			html+='</tr><tr class="exchangeitem_add_EUR_col" style="display:none">';
			html+='	<th>Umtausch-Artikel Gesamt-VK-Preis</th>';
			html+='	<td>EUR <span style="background-color:fff" id="order_exchangeitem_add_exchange_EUR_price_total"></span></td>';
			html+='	<td>EUR <span style="background-color:fff" id="order_exchangeitem_add_exchange_EUR_netto_total"></span></td>';
			html+='</tr>';
			html+='</table>';
			
			$("#order_exchangeitem_add_dialog").html(html);

			if (!$("#order_exchangeitem_add_dialog").is(":visible"))
			{
				$("#order_exchangeitem_add_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { order_exchangeitem_add(return_id, orderid);} },
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Umtausch: Artikel hinzufügen",
					width:550
				});		
			}
		});
	}


	function order_exchangeitem_add_setprices(ItemID)
	{
		//ERSTBELEGUNG DER FELDER NACH AUSWAHLE DES UMZUTAUSCHENDEN ARTIKELS

		//CURRENCYCODE
		$(".order_exchangeitem_add_CurrencyCode").text(orderitems[ItemID]["CurrencyCode"]);
		
		//AMOUNT
		var amount = orderitems[ItemID]["amount"];
		$("#order_exchangeitem_add_amount").val(amount);
		$("#order_exchangeitem_add_exchange_amount").val(amount);
		
		//VK EINZEL NETTO
		var net = (orderitems[ItemID]["PriceNet"].toString().replace(/,/g, "."))*1;
		$("#order_exchangeitem_add_FC_netto").text(net.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_FC_netto").val(net.toFixed(2).toString().replace(".", ","));
		
		//VK EINZEL BRUTTO
		var gross = net * mwstmultiplier;
		$("#order_exchangeitem_add_FC_price").text(gross.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_FC_price").val(gross.toFixed(2).toString().replace(".", ","));

		//VK GESAMT NETTO
		var total_net = net * amount;
		$("#order_exchangeitem_add_FC_netto_total").text(total_net.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_FC_netto_total").text(total_net.toFixed(2).toString().replace(".", ","));
		
		//VK GESAMT BRUTTO
		var total_gross = net * amount * mwstmultiplier;
		$("#order_exchangeitem_add_FC_price_total").text(total_gross.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_FC_price_total").text(total_gross.toFixed(2).toString().replace(".", ","));
		
		if (orderitems[ItemID]["CurrencyCode"]!="EUR") 
		{
			$(".exchangeitem_add_EUR_col").show();
		}
		else
		{
			$(".exchangeitem_add_EUR_col").hide();
		}
		
		var exchangerate = orderitems[ItemID]["ExchangeRateToEUR"];
		$("#order_exchangeitem_exchangerate").val(exchangerate);
		
		//VK EUR EINZEL NETTO
		var netEUR = net / exchangerate;
		$("#order_exchangeitem_add_EUR_netto").text(netEUR.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_EUR_netto").val(netEUR.toFixed(2).toString().replace(".", ","));
		
		//VK EUR EINZEL BRUTTO
		var grossEUR = net / exchangerate * mwstmultiplier;
		$("#order_exchangeitem_add_EUR_price").text(grossEUR.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_EUR_price").val(grossEUR.toFixed(2).toString().replace(".", ","));

		//VK EUR GESAMT NETTO
		var total_netEUR = net / exchangerate * amount;
		$("#order_exchangeitem_add_EUR_netto_total").text(total_netEUR.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_EUR_netto_total").text(total_netEUR.toFixed(2).toString().replace(".", ","));
		
		//VK EUR GESAMT BRUTTO
		var total_grossEUR = net / exchangerate * amount * mwstmultiplier;
		$("#order_exchangeitem_add_EUR_price_total").text(total_grossEUR.toFixed(2).toString().replace(".", ","));
		$("#order_exchangeitem_add_exchange_EUR_price_total").text(total_grossEUR.toFixed(2).toString().replace(".", ","));
	}
	
	function order_exchangeitem_changeMPN(MPN)
	{
		wait_dialog_show();
		//var MPN = $("#change_orderpositions_MPN").val();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "ShopItemGet", MPN:MPN},
			function(data)
			{
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#order_exchangeitem_add_exchange_itemID").val($xml.find("id_item").text());
					$("#order_exchangeitem_add_exchange_title").text($xml.find("title").text());
				}
				else
				{
					$("#order_exchangeitem_add_exchange_itemID").val(0);
					$("#order_exchangeitem_add_exchange_title").text("ARTIKEL EXISTIERT NICHT!");
					$("#order_exchangeitem_add_exchange_MPN").focus();
				}
			}
		);
	}
	
	function order_exchangeitem_add(return_id)
	{
		// CHECK USER INPUT
		if ($("#order_exchangeitem_add_item").val()=="")
		{
			$("#order_exchangeitem_add_item").focus();
			msg_box("Bitte einen Artikel auswählen");
			return;
		}
			//ANZAHL
		if ($("#order_exchangeitem_add_amount").val()=="" || $("#order_exchangeitem_add_amount").val()==0)
		{
			$("#order_exchangeitem_add_amount").focus();
			msg_box("Bitte eine gültige Anzahl der Artikel eingeben");
			return;
		}
			//UMTAUSCHGRUND
		if ($("#order_exchangeitem_add_reason").val()==0)
		{
			$("#order_exchangeitem_add_reason").focus();
			msg_box("Bitte einen Rückgabegrund angeben");
			return;
		}
			//UMTAUSCHERLÄUTERUNG -> nur zwingend bei "sonstige"
		if ($("#order_exchangeitem_add_reason").val()==100 && $("#order_exchangeitem_add_reason_description").val()=="")
		{
			$("#order_exchangeitem_add_reason_description").focus();
			msg_box("Bitte eine Erläuterung zum Umtausch angeben");
			return;
		}
		//CHECKS FOR EXCHANGES
		//UMTAUSCH ARTIKEL
		if ($("#order_exchangeitem_add_exchange_itemID").val()=="" || $("#order_exchangeitem_add_exchange_itemID").val()==0)
		{
			$("#order_returnitem_add_exchange_MPN").focus();
			msg_box("Bitte einen Umtauschartikel angeben");
			return;
		}
		//UMTAUSCH ANZAHL
		if ($("#order_exchangeitem_add_exchange_amount").val()=="" || $("#order_exchangeitem_add_exchange_amount").val()==0)
		{
			$("#order_exchangeitem_add_exchange_amount").focus();
			msg_box("Bitte eine Anzahl der Umtauschartikel angeben");
			return;
		}
		//UMTAUSCH ARTIKELPREIS
		if ($("#order_exchangeitem_add_exchange_FC_netto").val()=="" || $("#order_exchangeitem_add_exchange_FC_netto").val()==0)
		{
			$("#order_exchangeitem_add_exchange_FC_brutto").focus();
			msg_box("Bitte den Preis des Umtauschartikels eingeben");
			return;
		}
		
		wait_dialog_show();
		
		// DATEN SCHREIBEN
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderReturnItemAdd", 
			return_id:return_id, 
			shop_orders_items_id:orderitems[$("#order_exchangeitem_add_item").val()]["id"],
			//shop_orders_items_id:$("#order_exchangeitem_add_item").val(),
			//item_id:$("#order_exchangeitem_add_item").val(),
			amount:$("#order_exchangeitem_add_amount").val(),
			return_reason:$("#order_exchangeitem_add_reason").val(),
			return_reason_description:$("#order_exchangeitem_add_reason_description").val(),
			exchange_shop_orders_item:$("#order_exchangeitem_add_exchange_itemID").val(),
			exchange_shop_orders_item_amount:$("#order_exchangeitem_add_exchange_amount").val(),
			exchange_shop_orders_item_FCnetto:($("#order_exchangeitem_add_exchange_FC_netto").val().toString().replace(/,/g, "."))*1,
			exchange_shop_orders_item:$("#order_exchangeitem_add_exchange_itemID").val(),
			},
		function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			var event_id = $xml.find("event_id").text();

			//CALL PAYMENTNOTIFICATIONHANDLER
			field = new Object();
			
			field["API"]="payments";
			field["APIRequest"]="PaymentNotificationHandler";
			field["mode"]="Exchange";
			//field["orderid"]=returns["order_id"];
			field["returnid"]=return_id;
			field["order_event_id"]=event_id;
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/index.php", 
			field,
			function ($data)
			{
				
				wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
			//	alert("OK3");
			//	show_status("Änderung durchgeführt, Rückzahlung wurde gebucht!");
				$("#order_exchangeitem_add_dialog").dialog("close");

				order_returns_dialog(return_id);
			});
		//	$("#order_exchangeitem_add_dialog").dialog("close");
			
			order_returns_dialog(return_id);
			$("#order_exchangeitem_add_dialog").dialog("close");
		});

	}
	
	function order_returnitem_add(return_id)
	{
		//CHECK USER INPUT
			//ITEM
		if ($("#order_returnitem_add_item").val()=="")
		{
			$("#order_returnitem_add_item").focus();
			msg_box("Bitte einen Artikel auswählen");
			return;
		}
			//ANZAHL
		if ($("#order_returnitem_add_amount").val()=="" || $("#order_returnitem_add_amount").val()==0)
		{
			$("#order_returnitem_add_amount").focus();
			//if ( $("#order_returnitem_add_amount_max").val()==1)
			//{
				msg_box("Bitte eine gültige Anzahl der Artikel eingeben");
			//}
			//else
			//{
			//	msg_box("Bitte eine gültige Anzahl der Artikel eingeben: 1 -"+$("#order_returnitem_add_amount_max").val());
			//}
			return;
		}
			//RÜCKGABEGRUND
		if ($("#order_returnitem_add_reason").val()==0)
		{
			$("#order_returnitem_add_reason").focus();
			msg_box("Bitte einen Rückgabegrund angeben");
			return;
		}
			//RÜCKGABEERLÄUTERUNG -> nur zwingend bei "sonstige"
		if ($("#order_returnitem_add_reason").val()==100 && $("#order_returnitem_add_reason_description").val() == '')
		{
			$("#order_returnitem_add_reason_description").focus();
			msg_box("Bitte eine Erläuterung zur Rückgabe angeben");
			return;
		}
		//CHECKS FOR EXCHANGES
		if (returns["return_type"]=="exchange")
		{
			if ($("#order_returnitem_add_exchange_itemID").val()=="" || $("#order_returnitem_add_exchange_itemID").val()==0)
			{
				$("#order_returnitem_add_exchange_MPN").focus();
				msg_box("Bitte einen Umtauschartikel angeben");
				return;
			}
			if ($("#order_returnitem_add_exchange_amount").val()=="" || $("#order_returnitem_add_exchange_amount").val()==0)
			{
				$("#order_returnitem_add_exchange_amount").focus();
				msg_box("Bitte eine Anzahl der Umtauschartikel angeben");
				return;
			}
			
		}
		
		
		wait_dialog_show();
		
		// DATEN SCHREIBEN
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderReturnItemAdd", 
			return_id:return_id, 
			shop_orders_items_id:$("#order_returnitem_add_item").val(),
			//item_id:$("#order_returnitem_add_item").val(),
			amount:$("#order_returnitem_add_amount").val(),
			item_id_exchange:$("#order_returnitem_add_exchange_itemID").val(),
			amout_exchange:$("#order_returnitem_add_exchange_amount").val(),
			return_reason:$("#order_returnitem_add_reason").val(),
			return_reason_description:$("#order_returnitem_add_reason_description").val(),
			//exchange_shop_orders_item:0
			},
		function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			
			var event_id = $xml.find("event_id").text();
		/*	
			//CALL PAYMENTNOTIFICATIONHANDLER
			field = new Object();
			
			field["API"]="payments";
			field["APIRequest"]="PaymentNotificationHandler";
			field["mode"]="OrderReturn";
			//field["orderid"]=returns["order_id"];
			field["returnid"]=return_id;
			field["order_event_id"]=event_id;
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/index.php", 
			field,
			function ($data)
			{
				
				wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
			//	alert("OK3");
			//	show_status("Änderung durchgeführt, Rückzahlung wurde gebucht!");
				$("#order_returnitem_add_dialog").dialog("close");

				order_returns_dialog(return_id);
			});
*/
			$("#order_returnitem_add_dialog").dialog("close");
			order_returns_dialog(return_id);
			
		});
	}
	
	function order_returnitem_update_dialog(return_id, orderid, returnitem_item_id)
	{
//		alert(returnitem_item_id);
		//GET ORDER ITEMS
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet", OrderID:returns["order_id"]},
		function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			if ($("#order_returnitem_update_dialog").length == 0)
			{
				$("body").append('<div id="order_returnitem_update_dialog" style="display:none">');
			}
			
			var html = '';
			
			html+='<table>';
			html+='<tr>';
			html+='	<th>Artikel</th>';
			html+='	<td><select id="order_returnitem_update_item" size="1">';
			//html+='		<option value="">Bitte einen Artikel wählen</option>';
			
			// ES WERDEN NUR ARTIKEL ANGEZEIGT, DIE NOCH NICHT IN DER RÜCKGABE STEHEN & der aktuell zu bearbeitende Artikel
			
			$xml.find("Item").each( function ()
			{
				if (typeof(returns["returnitem"])!=="undefined")
				{
					var matchingItem = false;
					for (var i  = 0; i<returns["returnitem"].length; i++)
					{
						if ($(this).find("OrderItemMPN").text()==returns["returnitem"][i]["MPN"] && $(this).find("OrderItemItemID").text()!=returnitem_item_id) matchingItem = true;
					}
					if (!matchingItem) html+='		<option value="'+$(this).find("OrderItemItemID").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';
				}
				else
				{
					html+='		<option value="'+$(this).find("OrderItemMPN").text()+'">'+$(this).find("OrderItemMPN").text()+' - '+$(this).find("OrderItemDesc").text()+'</option>';					
				}
			});
			
			//GET RETURNITEM DATA
			for (var i  = 0; i<returns["returnitem"].length; i++)
			{
				if (returns["returnitem"][i]["item_id"]==returnitem_item_id)
				{
					var amount = returns["returnitem"][i]["amount"];
					var reason = returns["returnitem"][i]["return_reason"];
					var reason_description = returns["returnitem"][i]["return_reason_description"];
					var id_returnitem = returns["returnitem"][i]["id_returnitem"];
				}
			}
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Anzahl</th>';
			html+='	<td><input type="text" size="2" id="order_returnitem_update_amount" value = '+amount+' /><input type="hidden" id="order_returnitem_update_amount_max" value ='+$(this).find("OrderItemAmount").text()+' /></td>';
			html+='</tr><tr>';
			html+='	<th>Rückgabegrund</th>';
			html+='	<td><select id="order_returnitem_update_reason" size="1">';
			html+='		<option value=0>Bitte Rückgabegrund wählen</option>';
			
			$.each(ReturnsReasons, function($key, returnreason)
			{
				//if (reason == returnreason["id_returnreason"]) var $selected = 'selected'; else var $selected = '';
				var $selected = '';
				html+='<option value='+returnreason["id_returnreason"]+' title="'+returnreason["description"]+'" '+$selected+'>'+returnreason["title"]+'</option>';
			});
			
			html+='	</select></td>';
			html+='</tr><tr>';
			html+='	<th>Rückgabeerläuterung</th>';
			html+='	<td><textarea id="order_returnitem_update_reason_description" cols="20" rows="5">'+reason_description+'</textarea></td>';
			html+='</tr>';
			html+='</table>';
			
			$("#order_returnitem_update_dialog").html(html);
			
			//SELECTs vorbelegen
			$("#order_returnitem_update_reason").val(reason);
			$("#order_returnitem_update_item").val(returnitem_item_id);
			
			if (!$("#order_returnitem_update_dialog").is(":visible"))
			{
				$("#order_returnitem_update_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { order_returnitem_update(id_returnitem);} },
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Rückgabe Artikel bearbeiten",
					width:500
				});		
			}
		});
	}

	function order_returnitem_update(returnitem_id)
	{
		//alert(returnitem_id);
		
		//CHECK USER INPUT
			//ITEM
		if ($("#order_returnitem_update_item").val()=="")
		{
			$("#order_returnitem_update_item").focus();
			msg_box("Bitte einen Artikel auswählen");
			return;
		}
			//ANZAHL
		if ($("#order_returnitem_update_amount").val()=="" || $("#order_returnitem_update_amount").val()==0)
		{
			$("#order_returnitem_update_amount").focus();
			//if ( $("#order_returnitem_add_amount_max").val()==1)
			//{
				msg_box("Bitte eine gültige Anzahl der Artikel eingeben");
			//}
			//else
			//{
			//	msg_box("Bitte eine gültige Anzahl der Artikel eingeben: 1 -"+$("#order_returnitem_add_amount_max").val());
			//}
			return;
		}
			//RÜCKGABEGRUND
		if ($("#order_returnitem_update_reason").val()==0)
		{
			$("#order_returnitem_update_reason").focus();
			msg_box("Bitte einen Rückgabegrund angeben");
			return;
		}
			//RÜCKGABEERLÄUTERUNG -> nur zwingend bei "sonstige"
		if ($("#order_returnitem_update_reason").val()==100 && $("#order_returnitem_update_reason_description").val() == '')
		{
			$("#order_returnitem_update_reason_description").focus();
			msg_box("Bitte eine Erläuterung zur Rückgabe angeben");
			return;
		}
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderReturnItemUpdate", 
			SELECTOR_id_returnitem:returnitem_id,
			item_id:$("#order_returnitem_update_item").val(),
			amount:$("#order_returnitem_update_amount").val(),
			return_reason:$("#order_returnitem_update_reason").val(),
			return_reason_description:$("#order_returnitem_update_reason_description").val(),
			exchange_shop_orders_item:0
			},
		function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			$("#order_returnitem_update_dialog").dialog("close");
			
			order_returns_dialog(returns["id_return"]);
		});
	}

	function exchange_orderpositions_setPrices(netto)
	{

		//IF FUNCTION  CALL from amount
		if (netto == "amount") netto = $("#order_exchangeitem_add_exchange_EUR_netto").val();
		
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		//BERECHNUNGEN LAUFEN IMMER VOM EINZELPREIS EUR NETTO
		var net = (netto.toString().replace(/,/g, "."))*1;
		
		var amount=$("#order_exchangeitem_add_exchange_amount").val()*1;
		
		var exchangerate=$("#order_exchangeitem_exchangerate").val()*1;

			//PREIS POSITION GESAMT NETTO
			var netTotal = net*amount;
			//PREIS ARTIKEL EINZELN BRUTTO
			var gross = net*mwstmultiplier;
			//PREIS POSITION GESAMT BRUTTO
			var grossTotal = net*amount*mwstmultiplier;
			
		//FOREIGN CURRENCIES
			var FCnet = net*exchangerate;
			var FCnetTotal = net*amount*exchangerate;
			var FCgross = net*mwstmultiplier*exchangerate;
			var FCgrossTotal = net*amount*mwstmultiplier*exchangerate;
			
			order_exchangeitem_add_exchange_FC_price
			$("#order_exchangeitem_add_exchange_FC_price").val(FCgross.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_FC_netto").val(FCnet.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_EUR_price").val(gross.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_EUR_netto").val(net.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_FC_price_total").text(FCgrossTotal.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_FC_netto_total").text(FCnetTotal.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_EUR_price_total").text(grossTotal.toFixed(2).toString().replace(".", ","));
			$("#order_exchangeitem_add_exchange_EUR_netto_total").text(netTotal.toFixed(2).toString().replace(".", ","));
		
	}
	
	function set_exchange_amount()
	{
		exchange_orderpositions_setPrices($("#order_exchangeitem_add_exchange_EUR_netto").val().replace(/,/g, ".")*1);
	}

	
	function ex_get_netto_from_brutto(brutto)
	{
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		var gross=(brutto.replace(/,/g, "."))*1;
		
		if (gross!=0) var netto = gross/mwstmultiplier; else var netto = 0;
		
		exchange_orderpositions_setPrices(netto);
		
	}
	
	function ex_get_netto_from_FCnetto(FCnetto)
	{
		var exchangerate=$("#order_exchangeitem_exchangerate").val()*1;
		var FCnet=(FCnetto.replace(/,/g, "."))*1;

		if (FCnet!=0) var netto = FCnet/exchangerate; else var netto = 0;
		exchange_orderpositions_setPrices(netto);
	
	}
	
	function ex_get_netto_from_FCbrutto(FCbrutto)
	{

		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		var exchangerate=$("#order_exchangeitem_exchangerate").val()*1;
		var FCgross=(FCbrutto.replace(/,/g, "."))*1;
		
		if (FCgross!=0) var netto = FCgross /exchangerate/ mwstmultiplier; else var netto = 0;
		exchange_orderpositions_setPrices(netto);
		
	}
