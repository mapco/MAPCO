<?php
	include("config.php");
	$title='MAPCO Autoteile Shop / KFZ Teile 24 Stunden am Tag günstig kaufen!';
	include("templates/".TEMPLATE."/header.php");
	include("functions/shop_show_item.php");
/*	include("functions/shop_itemstatus.php");
	include("functions/mapco_gewerblich.php");
	include("functions/mapco_get_titles.php");	
	include("functions/mapco_motorart.php");
	include("functions/mapco_baujahr.php");
	include("functions/cms_url_encode.php");*/
//	echo '*****'.$_SESSION["id_user"];
	include("templates/".TEMPLATE."/cms_leftcolumn_shop.php");
//	include("modules/shop_login_box.php");
//	include("modules/shop_searchbycar.php");
?>


<script type="text/javascript">
//Ab hier: Fahrzeugeingabe
	function show_select_search_by_car_manufacturer(mode, khernr)
	{
		$("#KHerNr").val("");
		$("#KModNr").val("");
		if(mode!="disabled")
		{
			$("#vehicle_id").val("");
			$("#hsn").val("");
			$("#tsn").val("");
			$("#fin").val("");
		}
		//alert("ggg");
		document.getElementById("search_by_car_modell_box").style.display = "none";
		document.getElementById("search_by_car_type_box").style.display = "none";
		wait_dialog_show();
		$.post(soa_path, { API: "crm", Action: "search_by_car", mode:"manufacturer"},
			function(data)
			{
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					html='';
					if(mode=="disabled")
					{
						html+='			<select id="search_by_car_manufacturer" size="1" onchange="show_select_search_by_car_modell(this.value, 0);" style="width:250px" disabled>';
					}
					else
					{
						html+='			<select id="search_by_car_manufacturer" size="1" onchange="show_select_search_by_car_modell(this.value, 0);" style="width:250px">';
					}
					html+='				<option value=0>Hersteller</option>';

					$xml.find("manufacturer").each(
						function()
						{
							if(khernr>0 && $(this).find("KHerNr").text()==khernr)
							{
                        		html+='			<option value='+$(this).find("KHerNr").text()+' selected>'+$(this).find("Name").text()+'</option>\n';
							}
							else
							{
								html+='			<option value='+$(this).find("KHerNr").text()+'>'+$(this).find("Name").text()+'</option>\n';
							}
						}
					);
					html+='			</select>';
					$("#search_by_car_manufacturer_box").html(html);
		//			document.getElementById("search_by_car_modell_box").style.display = "";
                }
				else
				{
					show_status2(data);
				}

			}
		);	
	}
	
	function show_select_search_by_car_modell(KHerNr, KModNr, mode, mode2)
	{
		do_reset_color();
		$("#KHerNr").val("");
		$("#KModNr").val("");
		if(mode!="disabled")
		{
			$("#vehicle_id").val("");
			if(mode!="no_change_kba")
			{
				$("#hsn").val("");
				$("#tsn").val("");
			}
		}
		document.getElementById("search_by_car_modell_box").style.display = "";
		document.getElementById("search_by_car_type_box").style.display = "none";
		if(KModNr==0)
		{	
			wait_dialog_show();
		}
		$.post(soa_path, { API: "crm", Action: "search_by_car", mode:"modell", KHerNr:KHerNr},
			function(data)
			{
				if(KModNr==0)
				{	
					wait_dialog_hide();
				}
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					html='';
					if(mode=="disabled")
					{
						html+='			<select id="search_by_car_modell" size="1" onchange="show_select_search_by_car_type(this.value, 0);" style="width:250px" disabled>';
					}
					else
					{
						html+='			<select id="search_by_car_modell" size="1" onchange="show_select_search_by_car_type(this.value, 0);" style="width:250px">';
					}
					html+='				<option value=0>Modell</option>';

					$xml.find("modell").each(
						function()
						{
							if(KModNr>0 && $(this).find("KModNr").text()==KModNr)
							{
                        		html+='			<option value='+$(this).find("KModNr").text()+' selected>'+$(this).find("Name").text()+'</option>\n';
							}
							else
							{
								html+='			<option value='+$(this).find("KModNr").text()+'>'+$(this).find("Name").text()+'</option>\n';
							}
						}
					);
					html+='			</select>';
					$("#search_by_car_modell_box").html(html);
                }
				else
				{
					show_status2(data);
				}

			}
		);	
	}
	
	function show_select_search_by_car_type(KModNr, vehicle_id, mode, mode2)
	{
		$("#KHerNr").val("");
		$("#KModNr").val("");
		if(mode!="disabled")
		{
			$("#vehicle_id").val("");
			if(mode!="no_change_kba")
			{
				$("#hsn").val("");
				$("#tsn").val("");
			}
		}
		if(vehicle_id==0)
		{	
			wait_dialog_show();
		}
		$.post(soa_path, { API: "crm", Action: "search_by_car", mode:"type", KModNr:KModNr},
			function(data)
			{
				if(vehicle_id==0)
				{	
					wait_dialog_hide();
				}
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					html='';
					if(mode=="disabled")
					{
						html+='			<select id="search_by_car_type" size="1" onchange="get_Vehicle_by_id_vehicle(this.value);" style="width:250px" disabled>';
					}
					else
					{
						html+='			<select id="search_by_car_type" size="1" onchange="get_Vehicle_by_id_vehicle(this.value);" style="width:250px">';
					}
					html+='				<option value=0>Fahrzeugtyp</option>';

					$xml.find("type").each(
						function()
						{
							if(vehicle_id>0 && $(this).find("id_vehicle").text()==vehicle_id)
							{
                        		html+='			<option value='+$(this).find("id_vehicle").text()+' selected>'+$(this).find("Name").text()+'</option>\n';
							}
							else
							{
								html+='			<option value='+$(this).find("id_vehicle").text()+'>'+$(this).find("Name").text()+'</option>\n';
							}
						}
					);
					html+='			</select>';
					$("#search_by_car_type_box").html(html);
					document.getElementById("search_by_car_type_box").style.display = "";
                }
				else
				{
					show_status2(data);
				}

			}
		);	

	}
	
	function get_Vehicle_by_id_vehicle(id_vehicle, mode)
	{
		$("#vehicle_id").val(id_vehicle);
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "get_vehicle_byID", vehicle_ID: id_vehicle},
			function (data)
			{
				wait_dialog_hide();
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						var kba = $xml.find("vehicleKBA").text();
						var hsn = kba.substr(0,4);
						var tsn = kba.substr(4,3);
						if(mode!="no_change_kba")
						{
							$("#hsn").val(hsn);
							$("#tsn").val(tsn);
						}
						return;
					}
				}
				catch (err)
				{
					alert(err.message);
					return;
				}
			}
		);	
	}
	

	function do_reset_color()
	{
		document.getElementById("tsn").style.color = "";
		document.getElementById("hsn").style.color = "";
	}
	
	function reset_fields()
	{
		$("#vehicle_id").val("");
		$("#KHerNr").val("");
		$("#KModNr").val("");
	}
		
	function get_car_data()
	{
		do_reset_color();
		var hsn=$("#hsn").val();
	    var tsn=$("#tsn").val();
		
		hsn = hsn.trim();
		if(hsn.length<4 && hsn.length>0)
		{
			while(hsn.length<4)
			{
				hsn = "0" + hsn;
			}
		}
		$("#hsn").val(hsn);
		
		tsn = tsn.trim();
		if(tsn.length<3)
		{
			show_message_dialog("Bitte mindestens die ersten drei Ziffern der TSN-Nr. eingeben!");
			document.getElementById("tsn").style.color = "red";
			reset_fields();
			document.getElementById("search_by_car_modell_box").style.display = "none";
			document.getElementById("search_by_car_type_box").style.display = "none";
			document.getElementById("search_by_car_manufacturer").selectedIndex = 0;
			return;
		}
		tsn = tsn.substr(0,3);
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "get_vehicle_byKBA", TSN: tsn, HSN: hsn},
			function (data)
			{
				//alert(data);
				wait_dialog_hide();
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						if($xml.find("vehicleBrand").text()=="")
						{
							show_message_dialog("Zu diesen HSN/TSN-Nummern wurde kein Fahrzeug gefunden.");
							reset_fields();
							document.getElementById("tsn").style.color = "red";
							document.getElementById("hsn").style.color = "red";
							return;
						}

						$("#vehicle_id").val($xml.find("vehicleID").text());
						$("#KHerNr").val($xml.find("vehicleKHerNr").text());
						$("#KModNr").val($xml.find("vehicleKModNr").text());
						
	//					wait_dialog_show();
						$("#search_by_car_manufacturer").val($xml.find("vehicleKHerNr").text());
						if(document.getElementById("search_by_car_modell")!=null)
						{
							document.getElementById("search_by_car_modell").selectedIndex = 0;
						}
						show_select_search_by_car_modell($xml.find("vehicleKHerNr").text(), $xml.find("vehicleKModNr").text(), "no_change_kba");
						show_select_search_by_car_type($xml.find("vehicleKModNr").text(), $xml.find("vehicleID").text(), "no_change_kba");
						get_Vehicle_by_id_vehicle($xml.find("vehicleID").text(), "no_change_kba");
	//					wait_dialog_hide();
						return;
					}
				}
				catch (err)
				{
					alert(err.message);
					return;
				}
			}
		);
	}
	
	function safe_car_data(mode, customer_vehicle_id, id, order_id, customer_vehicle_id_old)
	{
		//alert(mode);
		do_reset_color();
		var hsn=$("#hsn").val();
	    var tsn=$("#tsn").val();
		var fin=$("#fin").val();
		var vehicle_id=$("#vehicle_id").val();
		
		hsn = hsn.trim();
		if(hsn.length<4 && hsn.length>0)
		{
			while(hsn.length<4)
			{
				hsn = "0" + hsn;
			}
		}
		$("#hsn").val(hsn);
		
		tsn = tsn.trim();
		/*if(tsn.length<3)
		{
			show_message_dialog("Bitte mindestens die ersten drei Ziffern der TSN-Nr. eingeben!");
			document.getElementById("tsn").style.color = "red";

			return;
		}*/
		if(tsn.length>2)
		{
			tsn = tsn.substr(0,3);
		}
		
		var kbanr = hsn + tsn;
		
		fin = fin.trim();
		if(fin.length>0 && fin.length<17)
		{
			show_message_dialog("Die Fahrgestellnummer muss 17 Zeichen lang sein. Ansonsten bitte das Feld leer lassen.");
			return;
		}
		
		if(vehicle_id.length==0)
		{
			show_message_dialog("Sie haben kein Fahrzeug ausgewählt.");
			return;
		}
		
		var month=$("#month").val();
	    var year=$("#year").val();
		
		if(month != "" || year != "")
		{
			if(month<1 || month>12)
			{
				show_message_dialog("Den Monat bitte zwischen 01 und 12 angeben, oder die Felder für die Erstzulassung leer lassen.");
				return;
			}
			if(year<1900 || year>2100)
			{
				show_message_dialog("Das Jahr bitte folgendermaßen angeben: jjjj also z.B. 2013, oder die Felder für die Erstzulassung leer lassen.");
				return;
			}
		}
		
		var date_built = build_date_from_mm_yyyy(month,year);
		
		if(month == "" && year == "")
		{
			date_built = 0;
		}
				
		var additional = "";
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate", customer_vehicle_id: customer_vehicle_id, vehicle_id: vehicle_id, kbanr: kbanr, fin: fin, date_built: date_built, additional: additional, mode: mode},
			function (data)
			{
				wait_dialog_hide();
				//alert(data);
				$("#cars").dialog("close");
				//alert(customer_vehicle_id);
				if(mode=="car_change")
				{
					car_input_show(customer_vehicle_id_old, id, order_id);
				}
				else
				{
					car_input_show(customer_vehicle_id, id, order_id);
				}
			}
		);
	}
	
	function build_date_from_mm_yyyy(month,year)
	{
		var date = new Date();
		date.setFullYear(year, month-1);
		return((date.getTime()/1000).toFixed(0));
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

//Ab hier: Bestelldetails
	var ignore_cars = new Array();

	function order_item_list(ignore_cars)
	{
		var html = '';
		/*html = '<div id="message_box_success" class="success" style="font-size: 16px; font-weight: bold; display: none; width: 732px"></div>';
		html += '<div id="message_box_warning" class="warning" style="font-size: 16px; font-weight: bold; display: none; width: 732px"></div>';
		html += '<div id="message_box_failure" class="failure" style="font-size: 16px; font-weight: bold; display: none; width: 732px"></div>';
		$("#mid_right_column_header").html(html);*/
		html = '';
		html += '<p>';
		html += '<a href="<?php echo PATH.$_GET["lang"]; ?>/online-shop/mein-konto/"><?php echo t("Mein Konto"); ?> </a>';
		html += '><a href="<?php echo PATH.$_GET["lang"]; ?>/online-shop/mein-konto/bestellungen/"> <?php echo t("Bestellungen"); ?></a>';
		html += ' > Bestellung ';
		html += '</p>';
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_order_detail", OrderID: <?php echo $_GET["id_order"]; ?>},
			function (data)
			{
				//show_status2(data);
				wait_dialog_hide();
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						var status_id = $xml.find("status_id").text();
						//Rechnungsanschrift
						html += '<h1><?php echo t("Bestellung")." #".$_GET["id_order"]; ?><p style="display: inline; font-size:15px; font-weight: normal"> vom ' + $xml.find("orderDate").text() + '</p></h1>';
						//html += '<p>Kundennummer: </p>';
						html += '<p><b>Rechnungsanschrift:</b><br>';
						if($xml.find("bill_company").text()!="")
						{
							html += $xml.find("bill_company").text() + '<br>';
						}
						html += $xml.find("bill_firstname").text() + ' ' + $xml.find("bill_lastname").text() + '<br>';
						html += $xml.find("bill_street").text() + ' ' + $xml.find("bill_number").text() + '<br>';
						html += $xml.find("bill_zip").text() + ' ' + $xml.find("bill_city").text() + '<br>';
						if($xml.find("bill_additional").text()!="")
						{
							html += $xml.find("bill_additional").text() + '<br>';
						}
						html += $xml.find("bill_country").text() + '<br>';
						html += '</p>';
						
						//Rechnungstabelle
						html += '<table class="hover">';
					  	html += '<tr>';
						html += '<th style="width:300px">Produktbeschreibung</th>';
						html += '<th>Menge</th>';
						html += '<th style="width:60px">EK</th>';
						html += '<th style="width:60px">Gesamt</th>';
						html += '<th style="width:200px">Fahrzeugauswahl</th>';
						html += '<th style="width:20px"></th>';
						html += '</tr>';
						
						//Items
						var car_data_input = 0;
						var order_id = $xml.find("id_order").text();
						$xml.find("Item").each(
							function()
							{
								if($xml.find("Order").attr("type")=="business")
								{
									html += '<tr>';
									if($xml.find("orderItemsTotalCollateralNet").text()!="0,00")
									{
										html += '<td style="width:300px">' + $(this).find("OrderItemDesc").text();
										html += 'zzgl. ' + $(this).find("orderItemTotalCollateralNet").text() + ' € Altteilpfand</td>';
									}
									else
									{
										html += '<td style="width:300px">' + $(this).find("OrderItemDesc").text() + '</td>';
									}
									html += '<td>' + $(this).find("OrderItemAmount").text() + '</td>';
									html += '<td style="width:60px">€ ' + $(this).find("orderItemPriceNet").text() + '</td>';
									html += '<td style="width:60px">€ ' + $(this).find("orderItemTotalNet").text() + '</td>';
								}
								else if($xml.find("Order").attr("type")=="customer")
								{
									html += '<tr>';
									if($(this).find("orderItemTotalCollateralGross").text()!="0,00")
									{
										html += '<td style="width:300px">' + $(this).find("OrderItemDesc").text();
										html += 'zzgl. ' + $(this).find("orderItemTotalCollateralGross").text() + ' € Altteilpfand</td>';
									}
									else
									{
										html += '<td style="width:300px">' + $(this).find("OrderItemDesc").text() + '</td>';
									}
									html += '<td>' + $(this).find("OrderItemAmount").text() + '</td>';
									html += '<td style="width:60px">€ ' + $(this).find("orderItemPriceGross").text() + '</td>';
									html += '<td style="width:60px">€ ' + $(this).find("orderItemTotalGross").text() + '</td>';
								}
								
								var customer_vehicle_id = $(this).find("OrderItemCustomerVehicleID").text();
								if($(this).find("OrderItemCustomerVehicleID").text()=="")
								{
									customer_vehicle_id = 0;
								}
								
								if(status_id==1 || status_id==7)
								{
									if(customer_vehicle_id == 0)
									{
										html += '<td><a href="javascript:car_input_show(' + customer_vehicle_id + ',' + $(this).find("OrderItemID").text() + ',' + order_id + ');">Fahrzeug auswählen</a></td>';
									}
									else if(customer_vehicle_id > 0)
									{
										html += '<td>' + $(this).find("OrderItemVehicleBrand").text() + ' ' + $(this).find("OrderItemVehicleModel").text() + ' ' + $(this).find("OrderItemVehicleType").text() + '<a href="javascript:car_input_show(' + customer_vehicle_id + ',' + $(this).find("OrderItemID").text() + ',' + order_id + ');"><br>Fahrzeug ändern</a></td>';
									}
								}
								else
								{
									html += '<td>Keine Eingabe nötig</td>';
								}
								
								if($(this).find("OrderItemVehicleID").text()>0)
								{
									html += '<td style="width:20px"><img src="<?php echo PATH; ?>/images/icons/24x24/accept.png" alt=""></td>';
								}
								else if($(this).find("OrderItemVehicleID").text()==0)
								{
									html += '<td style="width:20px"><img src="<?php echo PATH; ?>/images/icons/24x24/warning.png" alt=""></td>';
								}
								html += '</tr>';
								
								//Eingabezeile für fehlende Fahrzeugdaten
								if(!(ignore_cars && typeof ignore_cars[customer_vehicle_id] !== 'undefined'))
								{
									if(($(this).find("OrderItemVehicleDateBuilt").text()=="0" || $(this).find("OrderItemVehicleFIN").text()=="") && customer_vehicle_id > 0 && car_data_input==0 && (status_id==1 || status_id==7) && $xml.find("orderComplete").text()=="1")
									{
										html += '<tr>';
										html += '<td colspan="6" style="color: red; background-color: rgb(220,220,220); font-weight: bold"><p style="color: black; display: inline">' + $(this).find("OrderItemVehicleBrand").text() + ' ' + $(this).find("OrderItemVehicleModel").text() + ' ' + $(this).find("OrderItemVehicleType").text() + ':</p>';
										if($(this).find("OrderItemVehicleDateBuilt").text()=="0")
										{
											html += ' Erstzulassung (mm/jjjj):<input type="text" id="month2" size="3" maxlength="2" style="width: 20px"><p style="color: black; display: inline">/</p><input type="text" id="year2" size="3" maxlength="4" style="width: 35px">';
										}
										if($(this).find("OrderItemVehicleFIN").text()=="")
										{
											html += ' Fahrgestellnummer:<input type="text" id="fin2" name="fin" maxlength="17">';
										}
										html += '<button id="databutton" onclick="update_car_data(' + customer_vehicle_id + ');">Daten speichern</button></td>';
										html += '</tr>';
										car_data_input = 1;
									}
								}
							}
						);
						
						//Summen
						if($xml.find("Order").attr("type")=="business")
						{
							if($xml.find("orderItemsTotalCollateralNet").text()!="0,00")
							{
								html += '<tr>';
								html += '<td colspan="3">Altteilpfand für ' +  $xml.find("orderCollateralCount").text() + ' Artikel';
								if($xml.find("orderCollateralCount").text()==1)
								{
									html += '<br>Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet.</td>';
								}
								else
								{
									html += '<br>Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet.</td>';
								}
								html += '<td>€ ' + $xml.find("orderItemsTotalCollateralNet").text() + '</td>';
								html += '</tr>';
							}
							html += '<tr>';
							html += '<td colspan="3">' + $xml.find("shipping_details").text() + '</td>';
							html += '<td>€ ' + $xml.find("shippingCostsNet").text() + '</td>';
							html += '</tr>';
							html += '<tr>';
							html += '<td colspan="3">Nettogesamtwert</td>';
							html += '<td>€ ' + $xml.find("orderTotalNet").text() + '</td>';
							html += '</tr>';
							html += '<tr>';
							html += '<td colspan="3">gesetzliche Umsatzsteuer <?php echo UST; ?>%</td>';
							html += '<td>€ ' + $xml.find("orderTotalTax").text() + '</td>';
							html += '</tr>';
						}
						else if($xml.find("Order").attr("type")=="customer")
						{
							if($xml.find("orderItemsTotalCollateralGross").text()!="0,00")
							{
								html += '<tr>';
								html += '<td colspan="3">Altteilpfand für ' +  $xml.find("orderCollateralCount").text() + ' Artikel';
								if($xml.find("orderCollateralCount").text()==1)
								{
									html += '<br>Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet.</td>';
								}
								else
								{
									html += '<br>Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet.</td>';
								}
								html += '<td>€ ' + $xml.find("orderItemsTotalCollateralGross").text() + '</td>';
								html += '</tr>';
							}
							html += '<tr>';
							html += '<td colspan="3">' + $xml.find("shipping_details").text() + '</td>';
							html += '<td>€ ' + $xml.find("shippingCostsGross").text() + '</td>';
							html += '</tr>';
							html += '<tr>';
							html += '<td colspan="3">Im Gesamtpreis sind <?php echo UST; ?>% gesetzliche Umsatzsteuer enthalten</td>';
							html += '<td>€ ' + $xml.find("orderTotalTax").text() + '</td>';
							html += '</tr>';
						}
						html += '<tr>';
						html += '<td colspan="3"><b>Gesamtpreis</b></td>';
						html += '<td><b>€ ' + $xml.find("orderTotalGross").text() + '</b></td>';
						html += '</tr>';
						html += '</table>';
						$("#orders").html(html);

						//Status_Box Anzeige
						html = '<div id="message_box_success" class="success" style="font-size: 16px; font-weight: bold; display: none; width: 732px"></div>';
						html += '<div id="message_box_warning" class="warning" style="font-size: 16px; font-weight: bold; display: none; width: 732px"></div>';
						html += '<div id="message_box_failure" class="failure" style="font-size: 16px; font-weight: bold; display: none; width: 732px"></div>';
						$("#mid_right_column_header").html(html);
						if(status_id == 1 || status_id == 7)
						{
							if($xml.find("orderComplete").text()=="1")
							{
								if(car_data_input==1)
								{
									html2 = '<p style="margin:20px">Bitte geben Sie noch fehlende Fahrzeugdaten in der Artikelliste ein.';
									$("#message_box_warning").html(html2);
									document.getElementById("message_box_warning").style.display = "";
								}
								else
								{
									html2 = '<p style="margin:20px">Vielen Dank für die Eingabe der Fahrzeugdaten. Die Bestellung wird in Kürze versendet.';
									$("#message_box_success").html(html2);
									document.getElementById("message_box_success").style.display = "";
								}
							}
							else if($xml.find("orderComplete").text()=="0")
							{
								html2 = '<p style="margin:20px">Bitte bei allen Artikeln mit dem Warnsymbol <img src="<?php echo PATH; ?>/images/icons/24x24/warning.png" alt="" style="vertical-align: -4px"> Fahrzeuge zuordnen.';
								$("#message_box_failure").html(html2);
								document.getElementById("message_box_failure").style.display = "";
							}
						}
						if(status_id == 4)
						{
							html2 = '<p style="margin:20px">Der Bestellvorgang wurde abgerochen. Es ist keine Bearbeitung der Fahrzeugdaten nötig.';
							$("#message_box_success").html(html2);
							document.getElementById("message_box_success").style.display = "";
						}
						if(status_id == 2)
						{
							html2 = '<p style="margin:20px">Die Bestellung wurde an das Lager übergeben. Sie wird in Kürze versendet. Es ist keine Bearbeitung der Fahrzeugdaten nötig.';
							$("#message_box_success").html(html2);
							document.getElementById("message_box_success").style.display = "";
						}
						if(status_id == 3 || status_id == 6)
						{
							if($xml.find("shipping_number").text() != "")
							{
								if($xml.find("shipping_type_id").text() == 0 || $xml.find("shipping_type_id").text() == 3 || $xml.find("shipping_type_id").text() == 6)
								{
									html2 = '<p style="margin:20px">Ihre Bestellung wurde mit DPD versendet. Zur Sendungsverfolgung gelangen Sie über folgenden link: <a href="https://tracking.dpd.de/cgi-bin/delistrack?pknr='+$xml.find("shipping_number").text()+'" target="_blank">'+$xml.find("shipping_number").text()+'</a>';
									$("#message_box_success").html(html2);
									document.getElementById("message_box_success").style.display = "";
								}
								else if($xml.find("shipping_type_id").text() == 1 || $xml.find("shipping_type_id").text() == 2 || $xml.find("shipping_type_id").text() == 5 || $xml.find("shipping_type_id").text() == 7)
								{
									html2 = '<p style="margin:20px">Ihre Bestellung wurde mit DHL versendet. Zur Sendungsverfolgung gelangen Sie über folgenden link: <a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='+$xml.find("shipping_number").text()+'&rfn=&extendedSearch=true" target="_blank">'+$xml.find("shipping_number").text()+'</a>';
									$("#message_box_success").html(html2);
									document.getElementById("message_box_success").style.display = "";
								}
							}
							else
							{
								html2 = '<p style="margin:20px">Ihre Bestellung wurde versandt.';
							$("#message_box_success").html(html2);
							document.getElementById("message_box_success").style.display = "";
							}
						}
						return;
					}
				}
				catch (err)
				{
					alert(err.message);
					return;
				}
			}
		);
	}
	
	function update_car_data(customer_vehicle_id)
	{
		ignore_cars[customer_vehicle_id] = 1;
		if($("#month2").length == 0)
		{
			var fin=$("#fin2").val();
			fin = fin.trim();
			if(fin.length>0 && fin.length<17)
			{
				show_message_dialog("Die Fahrgestellnummer muss 17 Zeichen lang sein. Ansonsten bitte das Feld leer lassen.");
				return;
			}
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate", customer_vehicle_id: customer_vehicle_id, fin: fin, mode: "car_update_fin"},
				function (data)
				{
					wait_dialog_hide();
					order_item_list(ignore_cars);
				}
			);
		}
		else if($("#fin2").length == 0)
		{
			var month=$("#month2").val();
			var year=$("#year2").val();
			
			if(month != "" || year != "")
			{
				if(month<1 || month>12)
				{
					show_message_dialog("Den Monat bitte zwischen 01 und 12 angeben, oder die Felder für die Erstzulassung leer lassen.");
					return;
				}
				if(year<1900 || year>2100)
				{
					show_message_dialog("Das Jahr bitte folgendermaßen angeben: jjjj also z.B. 2013, oder die Felder für die Erstzulassung leer lassen.");
					return;
				}
			}
			var date_built = build_date_from_mm_yyyy(month,year);
			
			if(month == "" && year == "")
			{
				date_built = 0;
			}
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate", customer_vehicle_id: customer_vehicle_id, date_built: date_built, mode: "car_update_date_built"},
				function (data)
				{
					wait_dialog_hide();
					order_item_list(ignore_cars);
				}
			);
		}
		else
		{
			var fin=$("#fin2").val();
			fin = fin.trim();
			if(fin.length>0 && fin.length<17)
			{
				show_message_dialog("Die Fahrgestellnummer muss 17 Zeichen lang sein. Ansonsten bitte das Feld leer lassen.");
				return;
			}
			var month=$("#month2").val();
			var year=$("#year2").val();
			
			if(month != "" || year != "")
			{
				if(month<1 || month>12)
				{
					show_message_dialog("Den Monat bitte zwischen 01 und 12 angeben, oder die Felder für die Erstzulassung leer lassen.");
					return;
				}
				if(year<1900 || year>2100)
				{
					show_message_dialog("Das Jahr bitte folgendermaßen angeben: jjjj also z.B. 2013, oder die Felder für die Erstzulassung leer lassen.");
					return;
				}
			}
			var date_built = build_date_from_mm_yyyy(month,year);
			
			if(month == "" && year == "")
			{
				date_built = 0;
			}
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate", customer_vehicle_id: customer_vehicle_id, fin: fin, date_built: date_built, mode: "car_update"},
				function (data)
				{
					wait_dialog_hide();
					order_item_list(ignore_cars);
				}
			);
		}
	}
	
	function car_input_show(customer_vehicle_id, id, order_id)
	{
		html = '';
		html += '';
		html += '<h1>Meine Fahrzeuge:</h1>';
		html += '<table class="hover" style="font-size:12px; font-family: Arial">';									
		html += '<tr>';
		html += '<th>Fahrzeug auswählen</th>';
		html += '<th>Fahrzeugschein (zu_2 / zu_3)</th>';
		html += '<th>Baujahr</th>';
		html += '<th>Fahrgestellnummer</th>';
		html += '<th></th>';
		html += '<th style="width:20px">Status</th>';
		html += '<th style="width:20px">Löschen</th>';
		html += '</tr>';
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_customer_vehicles", mode: "customer", customer_id: "'<?php echo $_SESSION["id_user"]; ?>'"},
			function (data)
			{
				wait_dialog_hide();
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						html += '<tr>';
						html += '<td colspan="7" style="background: #98Fb98"><a href="javascript: car_data_new(' + customer_vehicle_id + ',' + id + ',' + order_id + ');">Neues Fahrzeug anlegen</a></td>';
						html += '</tr>';
						$xml.find("vehicle").each(
							function()
							{	
								if($(this).find("vehicleActive").text()==1)
								{
									html += '<tr style="height:45px">';
									html += '<td><a href="javascript:status_change(' + $(this).find("vehicleCustomerID").text() + ',' + id + ',' + order_id + ');">' + $(this).find("vehicleBrand").text() + ' ' + $(this).find("vehicleModel").text() + ' ' + $(this).find("vehicleModelType").text() + '</a></td>';
									html += '<td>' + $(this).find("vehicleKBA").text().substr(0,4) + '/' + $(this).find("vehicleKBA").text().substr(4,3) + '</td>';
									if($(this).find("vehicleDateBuilt").text()!=0)
									{
										var date = new Date(($(this).find("vehicleDateBuilt").text())*1000);
										var str = "" + (date.getMonth()+1);
										var month = "00";
										month = month.substring(0, month.length - str.length) + str;
								
										html += '<td>' + month + '/' + date.getFullYear() + '</td>';
									}
									else
									{
										html += '<td></td>';
									}
									html += '<td>' + $(this).find("vehicleFIN").text() + '</td>';
									html += '<td><a href="javascript:car_data_change(' + $(this).find("vehicleCustomerID").text() + ',' + id + ',' + order_id + ',' + customer_vehicle_id + ');">Fahrzeugdaten ändern</a></td>';
									if(customer_vehicle_id==$(this).find("vehicleCustomerID").text())
									{
										html += '<td><img src="<?php echo PATH; ?>/images/icons/24x24/accept.png" alt="" id="' + $(this).find("vehicleCustomerID").text() + '"></td>';
									}
									else
									{
										html += '<td><img src="<?php echo PATH; ?>/images/icons/24x24/accept.png" alt="" style="display:none" id="' + $(this).find("vehicleCustomerID").text() + '"></td>';
									}
									html += '<td><a href="javascript:deactivate_car(' + $(this).find("vehicleCustomerID").text() + ',' + customer_vehicle_id + ',' + id + ',' + order_id + ')"><img src="<?php echo PATH; ?>/images/icons/24x24/remove.png" alt=""></a></td>';
									html += '</tr>';
								}
							}
						);
						html += '</table>';
						html += '<input type="checkbox" id="allcheck"  value="" style="display: none"><p style="font-size:12px; font-family: Arial; display: none"> Änderung für alle Artikel der Bestellung übernehmen';
						html += '<br><input id="allselect" style="display: none">';
						html += '<br><input id="customer_vehicle_id" style="display: none">';
						html += '<br><input id="item_count" style="display: none">';
						
						$("#usercars").html(html);
						$("#customer_vehicle_id").val(customer_vehicle_id);
						$("#allselect").val("selected");
						return;
					}
				}
				catch (err)
				{
					alert(err.message);
					return;
				}		
			}
		);
		show_car_dialog(id, order_id);
	//	show_select_search_by_car_manufacturer();
	//	self.scrollTo(0,0);
	}
	
	function deactivate_car(customer_vehicle_id, customer_vehicle_id_old, id, order_id)
	{
		//alert(customer_vehicle_id);
		//wait_dialog_show();
		delete_dialog("Wollen Sie dieses Fahrzeug wirklich löschen?");
		function close()
		{
			$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate", customer_vehicle_id: customer_vehicle_id, mode: "car_deactivate"},
				function (data)
				{
					car_input_show(customer_vehicle_id_old, id, order_id);
				}
			);
		}
		function delete_dialog(message)
		{
			$("#message").html(message);
			$("#message").dialog
			({	buttons:
				[
					{ text: "Ok", click: function() {close(); $(this).dialog("close");} },
					{ text: "Abbrechen", click: function() {$(this).dialog("close");} }
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
	}
	
	function status_change(vehicle_id_new, id, order_id)
	{
		if($("#customer_vehicle_id").val() == 0)
		{
			document.getElementById(vehicle_id_new).style.display = "";
			$("#customer_vehicle_id").val(vehicle_id_new);
		}
		else if($("#customer_vehicle_id").val() == vehicle_id_new)
		{
			document.getElementById(vehicle_id_new).style.display = "none";
			document.getElementById($("#customer_vehicle_id").val()).style.display = "none";
			$("#customer_vehicle_id").val("0");
		}
		else if($("#customer_vehicle_id").val() != vehicle_id_new)
		{
			document.getElementById(vehicle_id_new).style.display = "";
			if(document.getElementById($("#customer_vehicle_id").val()))
			{
				//alert("innen");
				document.getElementById($("#customer_vehicle_id").val()).style.display = "none";
			}
			//alert("hinter");
			$("#customer_vehicle_id").val(vehicle_id_new);
			
			//alert(vehicle_id_new);
			//$("#customer_vehicle_id").val(vehicle_id_new);
		}
		car_status_save(id, order_id);
		$("#usercars").dialog("close");
	}
	
	function car_status_save(id, order_id)
	{
		//alert($("#customer_vehicle_id").val());
		var allselect = "selected";
		//alert($("#item_count").val());
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_order_detail", OrderID: order_id},
			function (data)
			{
				//show_status2(data);
				//wait_dialog_hide();
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					//alert($xml.find("orderPositions").text());
					if ( $ack.text()=="Success" )
					{
						if($xml.find("orderPositions").text()>1)
						{
							allselect_set("Soll das ausgewählte Fahrzeug für alle Artikel der Bestellung übernommen werden?");
						}
						else if ($xml.find("orderPositions").text()==1)
						{
							allselect="selected";
							save_data(allselect, order_id);							
						}
						function allselect_set(message)
						{
							$("#message").html(message);
							$("#message").dialog
							({	buttons:
								[
									{ text: "Ja", click: function() {allselect="all";save_data(allselect);$(this).dialog("close");} },
									{ text: "Nein", click: function() {allselect="selected";save_data(allselect);$(this).dialog("close");} }
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
						
						function save_data(allselect, order_id)
						{
							//alert(allselect);
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_set_orderItem_vehicle", mode: allselect, customerVehicleID: $("#customer_vehicle_id").val(), OrderID: order_id, OrderItemID: id},
								function (data)
								{
									wait_dialog_hide();
									try
									{
										var $xml = $($.parseXML(data));
										var $ack = $xml.find("Ack");
										if ( $ack.text()=="Success" )
										{
											order_item_list(ignore_cars);
											return;
										}
									}
									catch (err)
									{
										alert(err.message);
										return;
									}		
								}
							);
						}
						
						return;
					}
				}
				catch (err)
				{
					alert(err.message);
					return;
				}
			}
		);
	}
	function show_car_dialog(id, order_id)
	{
		$("#usercars").dialog
		//$("#carfleet").dialog
		({	buttons:
			[
				//{ text: "Auswahl bestätigen", click: function() {car_status_save(id, order_id); $(this).dialog("close");} },
				{ text: "Abbrechen", click: function() {order_item_list(ignore_cars);$(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Fahrzeugauswahl",
			width:800,
			maxHeight:500,
			overflow:scroll,
			position:["center",150]
		});
	}
	
	function car_data_new(customer_vehicle_id, id, order_id)
	{
		$("#month").val("");
		$("#year").val("");
		document.getElementById("kbabutton").style.display = "";
		document.getElementById("textfield").style.display = "";
		//alert("" + customer_vehicle_id + " " + id + " " + order_id);
		//alert("car data new");
		//document.getElementById("search_by_car_modell_box").style.display = "none";
		show_select_search_by_car_manufacturer();
		car_data_change_dialog("car_new", customer_vehicle_id, id, order_id);
		//document.getElementById("usercars").style.display = "none";
		//document.getElementById("cars").style.display = "";
	}
	
	function car_data_change(customer_vehicle_id, id, order_id, customer_vehicle_id_old)
	{
		document.getElementById("kbabutton").style.display = "none";
		document.getElementById("textfield").style.display = "none";
		var vehicle_id = "";
		var kba = "";
		var datebuilt = "";
		var fin = "";
		//wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_customer_vehicles", mode: "vehicle", vehicle_customer_id: customer_vehicle_id},
			function (data)
			{
				//wait_dialog_hide();
				//alert(data);
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						vehicle_id = $xml.find("vehicleID").text();
						kba = $xml.find("vehicleKBA").text();
						datebuilt = $xml.find("vehicleDateBuilt").text();
						fin = $xml.find("vehicleFIN").text();
						var hsn = kba.substr(0,4);
						var tsn = kba.substr(4,3);
						$("#hsn").val(hsn);
						$("#tsn").val(tsn);
						if(datebuilt!=0)
						{
							var date = new Date(datebuilt*1000);
							var str = "" + (date.getMonth()+1);
							var month = "00";
							month = month.substring(0, month.length - str.length) + str;
							var year = date.getFullYear();
						}
						else
						{
							var month = "";
							var year = "";
						}
						$("#month").val(month);
						$("#year").val(year);
						//alert(vehicle_id);
						$("#fin").val(fin);
						$("#vehicle_id").val(vehicle_id);
						//alert(vehicle_id+kba+datebuilt+fin);
						//alert(vehicle_id);
						//wait_dialog_show();
						$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "get_vehicle_byID", vehicle_ID: vehicle_id},
							function (data)
							{
								//wait_dialog_hide();
								try
								{
									//alert(vehicle_id);
									var $xml = $($.parseXML(data));
									var $ack = $xml.find("Ack");
									if ( $ack.text()=="Success" )
									{
										var khernr = $xml.find("vehicleKHerNr").text();
										var kmodnr = $xml.find("vehicleKModNr").text();
										show_select_search_by_car_manufacturer("disabled", khernr);
										show_select_search_by_car_modell(khernr, kmodnr, "disabled");
										show_select_search_by_car_type(kmodnr, vehicle_id, "disabled");
										/*alert(khernr);
										alert(kmodnr);
										var hsn = kba.substr(0,4);
										var tsn = kba.substr(4,3);
										$("#hsn").val(hsn);
										$("#tsn").val(tsn);*/
										return;
									}
								}
								catch (err)
								{
									alert(err.message);
									return;
								}
							}
						);
						return;
					}
				}
				catch (err)
				{
					alert(err.message);
					return;
				}		
			}
		);
		car_data_change_dialog("car_change", customer_vehicle_id, id, order_id, customer_vehicle_id_old);
	}
	
	function car_data_change_dialog(mode, customer_vehicle_id, id, order_id, customer_vehicle_id_old)
	{
		if(mode=="car_change")
		{
			$("#cars").dialog
			({	buttons:
				[
					{ text: "Änderungen übernehmen", click: function() {safe_car_data("car_change", customer_vehicle_id, id, order_id, customer_vehicle_id_old);} },
					{ text: "Abbrechen", click: function() {$(this).dialog("close");} }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Fahrzeugdaten ändern / ergänzen",
				width:800,
				position:["center",200]
			});		
		}
		else if(mode=="car_new")
		{
			$("#cars").dialog
			({	buttons:
				[
					{ text: "Neues KFZ anlegen", click: function() {safe_car_data("car_new", customer_vehicle_id, id, order_id, customer_vehicle_id_old);} },
					{ text: "Abbrechen", click: function() {$(this).dialog("close");} }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Neues Fahrzeug anlegen",
				width:800,
				position:["center",200]
			});	
		}
	}
</script>
<?php
	echo '<div id="mid_right_column_header" style="width:776px;
	margin:0px 0px 0px 10px;
	display:inline;
	float:left;"></div>';
	echo '<div id="mid_right_column">';
		echo '<div id="orders"></div>';
		echo '<div id="usercars"></div>';
		echo '<div id="cars" style="display:none">';
			echo '<table style="border-style: solid;border-width: 1px">';
			echo '<tr>';
				echo '<td id="textfield" align="left" colspan="3" style="font-weight: bold; font-size: 16px; border-style: solid; border-width:2px; background-color: #FFCC00">Bitte wählen Sie Ihr Fahrzeug nach KBA(HSN/TSN)-Nummer oder aus der Hersteller-Liste aus:</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td align="left" style="width: 250px">Fahrzeugschein (zu 2(HSN)/ zu 3(TSN)):</td>';
				echo '<td align="left"><input type="text" id="hsn" size="3" maxlength="4">/
				          <input type="text" id="tsn" style="width: 35px"></td>';
				echo '<td align="left"><button id="kbabutton" onclick="get_car_data();">Fahrzeugdaten laden</button></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td align="left">Hersteller:</td>';
				echo '<td align="left"><div id="search_by_car_manufacturer_box"></div></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td align="left">Modell:</td>';
				echo '<td align="left"><div id="search_by_car_modell_box"></div></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td align="left">Typ:</td>';
				echo '<td align="left"><div id="search_by_car_type_box"></div></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '</table>';
			echo '<table style="border-style: solid;border-width: 1px; margin-top: 10px">';
			/*echo '<tr>';
				echo '<td align="left">Fahrzeugschein (zu 2(HSN)/ zu 3(TSN)):</td>';
				echo '<td align="left"><input type="text" id="hsn" size="3" maxlength="4">/
				          <input type="text" id="tsn"></td>';
				echo '<td align="left"><button id="kbabutton" onclick="get_car_data();">Fahrzeugdaten laden</button></td>';
			echo '</tr>';*/
			echo '<tr>';
				echo '<td align="left" style="width: 250px">Erstzulassung (mm/jjjj):</td>';
				echo '<td align="left"><input type="text" id="month" size="3" maxlength="2">/
				          <input type="text" id="year" size="3" maxlength="4"></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td align="left">Fahrgestellnummer:</td>';
				echo '<td align="left"><input type="text" id="fin" name="fin" maxlength="17"></td>';
				echo '<td></td>';
			echo '</tr>';
			echo '<tr>';
				//echo '<td><button onclick="safe_car_data();">Fahrzeugdaten speichern</button></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
			echo '</tr>';
		echo '</table>';
		echo '<input id="vehicle_id" style="display:none">';
		echo '<input id="KHerNr" style="display:none">';
		echo '<input id="KModNr" style="display:none">';
		echo '<input id="time" style="display:none">';
		echo '</div>';
	echo '</div>';
	echo  '<p id="message" style="display:none"></p>';
	//echo  '<p id="message"></p>';
	include("templates/".TEMPLATE."/footer.php");
?>
<script type="text/javascript">
	soa_path = "<?php echo PATH; ?>soa/";
	order_item_list(ignore_cars);		
</script>