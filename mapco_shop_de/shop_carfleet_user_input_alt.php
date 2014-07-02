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
	include("modules/cms_leftcolumn_shop.php");
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
	
	function show_select_search_by_car_modell(KHerNr, KModNr, mode)
	{
		do_reset_color();
		$("#KHerNr").val("");
		$("#KModNr").val("");
		if(mode!="disabled")
		{
			$("#vehicle_id").val("");
			$("#hsn").val("");
			$("#tsn").val("");
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
	
	function show_select_search_by_car_type(KModNr, vehicle_id, mode)
	{
		$("#KHerNr").val("");
		$("#KModNr").val("");
		if(mode!="disabled")
		{
			$("#vehicle_id").val("");
			$("#hsn").val("");
			$("#tsn").val("");
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
	
	function get_Vehicle_by_id_vehicle(id_vehicle)
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
						$("#hsn").val(hsn);
						$("#tsn").val(tsn);
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
						show_select_search_by_car_modell($xml.find("vehicleKHerNr").text(), $xml.find("vehicleKModNr").text());
						show_select_search_by_car_type($xml.find("vehicleKModNr").text(), $xml.find("vehicleID").text());
						get_Vehicle_by_id_vehicle($xml.find("vehicleID").text());
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
	function order_item_list()
	{
		var html = '';
		html += '<p>';
		html += '<a href="<?php PATH.$_GET["lang"] ?>/online-shop/mein-konto/">Mein Konto</a>';
		html += ' > Bestellungen';
		html += '</p>';
		html += '<h1>Meine Bestellungen:</h1>';
		
		wait_dialog_show();
		
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_user_orders_get"},
			function (data)
			{
				wait_dialog_hide();
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						var check = "";
						var sum = 0;
						var price = 0;
						$xml.find("item").each(
							function()
							{
								//alert($(this).find("order_id").text());
								if($(this).find("order_id").text() != check && check != "")
								{
									html += '<tr>';
									html += '<td style="border-style:none; background: white"></td>';
									html += '<td>Gesamt:</td>';
									html += '<td style="text-align: right">' + (Math.round(sum*100)/100/1).toFixed(2) + '</td>';
									html += '<td style="border-style:none; background: white"></td>';
									html += '<td style="border-style:none; background: white"></td>';
									html += '</tr>';
									html += '<tr>';
									html += '<td style="border-style:none; background: white; height:10px"> </td>';
									html += '<td style="border-style:none; background: white"></td>';
									html += '<td style="border-style:none; background: white"></td>';
									html += '<td style="border-style:none; background: white"></td>';
									html += '<td style="border-style:none; background: white"></td>';
									html += '</tr>';
									html += '</table>';
									sum = 0;
								}
								if($(this).find("order_id").text() != check)
								{
									var date = new Date(($(this).find("firstmod").text())*1000);
									html += '<table class="hover">';
								  html += '<p style="font-size:25px; text-indent: 15px"><h2 style="display: inline">Bestellung Nr. ' + $(this).find("order_id").text() + '</h2><p><p style="font-size:15px; text-indent: 15px; display: inline">   vom ' + date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear();
									check = $(this).find("order_id").text();
									
									html += '<tr>';
									html += '<th style="width:300px">Produktbeschreibung</th>';
									html += '<th>Anzahl</th>';
									html += '<th>Summe</th>';
									html += '<th style="width:200px">Fahrzeugauswahl</th>';
									html += '<th style="width:20px"></th>';
									html += '</tr>';
								}
								//Items
								price = (Math.round($(this).find("amount").text() * $(this).find("price").text() * 100)) / 100;
								html += '<tr>';
								html += '<td>' + $(this).find("title").text() + '</td>';
								html += '<td style="text-align: right">' + $(this).find("amount").text() + '</td>';
								html += '<td style="text-align: right">' + price.toFixed(2) + '</td>';
								if($(this).find("customer_vehicle_id").text()>0)
								{
									html += '<td><a href="javascript:car_input_show(' + $(this).find("customer_vehicle_id").text() + ',' + $(this).find("id").text() + ',' + $(this).find("order_id").text() + ');">Fahrzeug ändern</a></td>';
									html += '<td><img src="<?php echo PATH; ?>/images/icons/24x24/accept.png" alt=""></td>';
								}
								if($(this).find("customer_vehicle_id").text()==0)
								{
									html += '<td><a href="javascript:car_input_show(' + $(this).find("customer_vehicle_id").text() + ',' + $(this).find("id").text() + ',' + $(this).find("order_id").text() + ');">Fahrzeug auswählen</a></td>';
									html += '<td><img src="<?php echo PATH; ?>/images/icons/24x24/warning.png" alt=""></td>';
								}
								html += '</tr>';
								sum += price;
							}
						);
						
						html += '<tr>';
						html += '<td style="border-style:none; background: white"></td>';
						html += '<td>Gesamt:</td>';
						html += '<td style="text-align: right">' + (Math.round(sum*100)/100/1).toFixed(2) + '</td>';
						html += '<td style="border-style:none; background: white"></td>';
						html += '<td style="border-style:none; background: white"></td>';
						html += '</tr>';
						html += '<tr>';
						html += '<td style="border-style:none; background: white; height:10px"> </td>';
						html += '<td style="border-style:none; background: white"></td>';
						html += '<td style="border-style:none; background: white"></td>';
						html += '<td style="border-style:none; background: white"></td>';
						html += '<td style="border-style:none; background: white"></td>';
						html += '</tr>';
						html += '</table>';
						$("#orders").html(html);
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
	
	function car_input_show(customer_vehicle_id, id, order_id)
	{
		//alert(customer_vehicle_id);
		html = '';
		html += '';
		html += '<h1>Meine Fahrzeuge:</h1>';
		html += '<table class="hover">';									
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
									//alert(html);
									html += '<tr style="height:45px">';
									html += '<td><a href="javascript:status_change(' + $(this).find("vehicleCustomerID").text() + ');">' + $(this).find("vehicleBrand").text() + ' ' + $(this).find("vehicleModel").text() + ' ' + $(this).find("vehicleModelType").text() + '</a></td>';
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
									//alert(customer_vehicle_id);
									//alert($(this).find("vehicleCustomerID").text());
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
						html += '<input type="checkbox" id="allcheck"  value=""> Änderung für alle Artikel der Bestellung übernehmen';
						html += '<br><input id="allselect" style="display: none">';
						html += '<br><input id="customer_vehicle_id" style="display: none">';
						
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
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate", customer_vehicle_id: customer_vehicle_id, mode: "car_deactivate"},
			function (data)
			{
				//wait_dialog_hide();
				//alert(data);
				//$("#cars").dialog("close");
				//alert(customer_vehicle_id);
				/*if(mode=="car_change")
				{
					car_input_show(customer_vehicle_id_old, id, order_id);
				}
				else
				{
					car_input_show(customer_vehicle_id, id, order_id);
				}*/
				car_input_show(customer_vehicle_id_old, id, order_id);
			}
		);
	}
	
	function status_change(vehicle_id_new)
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
	}
	
	function car_status_save(id, order_id)
	{
		//alert($("#customer_vehicle_id").val());
		var allselect = "selected";
		
		if(document.getElementById("allcheck").checked == true)
		{
			allselect = "all";
		}
		
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
						order_item_list();
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
				{ text: "Auswahl bestätigen", click: function() {car_status_save(id, order_id); $(this).dialog("close");} },
				{ text: "Abbrechen", click: function() {$(this).dialog("close");} }
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
		//alert(vehicle_id);
		//alert("" + customer_vehicle_id + " " + id + " " + order_id);
		//document.getElementById("search_by_car_modell_box").style.display = "none";
		//document.getElementById("search_by_car_manufacturer").style.display = "none";
		//$("#search_by_car_manufacturer").attr("disabled", "disabled");
		car_data_change_dialog("car_change", customer_vehicle_id, id, order_id, customer_vehicle_id_old);
		//document.getElementById("search_by_car_manufacturer_box").style.display = "none";
		//$("#fin").attr("disabled", "disabled");
		//document.getElementById("usercars").style.display = "none";
		//document.getElementById("cars").style.display = "";
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
	echo '<div id="mid_right_column">';
		echo '<div id="orders"></div>';
		echo '<div id="usercars"></div>';
		echo '<div id="cars" style="display:none">';
			echo '<table>';
			
			/*echo '<tr><div id="search_by_car_manufacturer_box"></div>
				      <div id="search_by_car_modell_box"></div>
					  <div id="search_by_car_type_box"></div></tr>';	*/
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
			echo '<tr>';
				echo '<td align="left">Fahrzeugschein (zu 2(HSN)/ zu 3(TSN)):</td>';
				echo '<td align="left"><input type="text" id="hsn" size="3" maxlength="4">/
				          <input type="text" id="tsn"></td>';
				echo '<td align="left"><button id="kbabutton" onclick="get_car_data();">Fahrzeugdaten laden</button></td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td align="left">Erstzulassung (mm/jjjj):</td>';
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
	include("templates/".TEMPLATE."/footer.php");
?>
<script type="text/javascript">
	soa_path = "<?php echo PATH; ?>soa/";
	//show_select_search_by_car_manufacturer();
	order_item_list();			
/*	$("#hsn").val("0600");
	$("#tsn").val("607");
	$("#month").val("08");
	$("#year").val("1992");
	$("#fin").val("ksejdirhn56leo345");*/
	
//	date = new Date(857170800000);
//	date.setMonth(6);
//	date.setFullYear(2013,6,1);
//	$("#time").val((date.getTime()/1000).toFixed(0));
//	$("#time").val("" + date.getMonth() + date.getFullYear());
	
</script>