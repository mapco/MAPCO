<?php
	include("config.php");
	$title='MAPCO Autoteile Shop / KFZ Teile 24 Stunden am Tag günstig kaufen!';
	include("templates/".TEMPLATE."/header.php");
	include("functions/shop_show_item.php");
	include("functions/cms_t.php");
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

	if( isset($_GET["getvars1"]) ) $_GET["id_order"]=$_GET["getvars1"];
?>

	<style>
		.ui-autocomplete .ui-state-focus
		{
			background: #0099FF;
			color: white;
			font-size:1.1em;
			border-radius: 0;
			border: none;
		}
		.ui-autocomplete
		{
			 background-image: none;
			 background-color: white;
			 border-radius: 0;
			 text-align: left;
			 padding: 0px;
			 margin: 0px;
			 line-height: 10px;
		}
		td.bill_address
		{
			float: left;
			padding: 0px;
			margin-top: 3px;
		}
		td.bill_address_input
		{
			padding: 0px;
		}
	</style>

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
			$("#hsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
			$("#tsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
		}
		//alert("ggg");
		document.getElementById("search_by_car_modell_box").style.display = "none";
		document.getElementById("search_by_car_type_box").style.display = "none";
		//wait_dialog_show();
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
					html+='				<option value=0><?php echo t("Hersteller"); ?></option>';

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
				$("#hsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
				$("#tsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
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
					html+='				<option value=0><?php echo t("Modell"); ?></option>';

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
				$("#hsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
				$("#tsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
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
					html+='				<option value=0><?php echo t("Fahrzeugtyp"); ?></option>';

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
							if(hsn!="")
							{
								$("#hsn").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#hsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
							if(tsn!="")
							{
								$("#tsn").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#tsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
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
			show_message_dialog("<?php echo t("Bitte mindestens die ersten drei Ziffern der TSN-Nr. eingeben!"); ?>");
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
							show_message_dialog("<?php echo t("Zu diesen HSN/TSN-Nummern wurde kein Fahrzeug gefunden."); ?>");
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
			show_message_dialog("<?php echo t("Die Fahrgestellnummer muss 17 Zeichen lang sein. Ansonsten bitte das Feld leer lassen."); ?>");
			return;
		}
		
		if(vehicle_id.length==0)
		{
			show_message_dialog("<?php echo t("Sie haben kein Fahrzeug ausgewählt."); ?>");
			return;
		}
		
		var month=$("#month").val();
	    var year=$("#year").val();
		
		if(month != "" || year != "")
		{
			if(month<1 || month>12)
			{
				show_message_dialog("<?php echo t("Den Monat bitte zwischen 01 und 12 angeben, oder die Felder für die Erstzulassung leer lassen."); ?>");
				return;
			}
			if(year<1900 || year>2100)
			{
				show_message_dialog("<?php echo t("Das Jahr bitte folgendermaßen angeben: jjjj also z.B. 2013, oder die Felder für die Erstzulassung leer lassen."); ?>");
				return;
			}
		}
		
		var date_built = build_date_from_mm_yyyy(month,year);
		
		if(month == "" && year == "")
		{
			date_built = 0;
		}
				
		var additional = "";
		var c0003 = $("#c0003").val();
		var c0004 = $("#c0004").val();
		var c0005 = $("#c0005").val();
		var c0006 = $("#c0006").val();
		var s0033 = $("#0033").val();
		var s0038 = $("#0038").val();
		var s0040 = $("#0040").val();
		var s0067 = $("#0067").val();
		var s0072 = $("#0072").val();
		var s0112 = $("#0112").val();
		var s0139 = $("#0139").val();
		var s0233 = $("#0233").val();
		var s0514 = $("#0514").val();
		var s0564 = $("#0564").val();
		var s0567 = $("#0567").val();
		var s0608 = $("#0608").val();
		//var s0649 = $("#0649 :selected").text();
		var s0649 = $("#0649").val();
		var s1197 = $("#1197").val();

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate", 
														customer_vehicle_id: customer_vehicle_id,
													    vehicle_id: 		 vehicle_id,
													    kbanr: 				 kbanr, 
														fin: 				 fin,
													    date_built: 		 date_built,
														c0003:				 c0003,
														c0004:				 c0004,
														c0005:				 c0005,
														c0006:				 c0006,
														s0033:				 s0033,
														s0038:				 s0038,
														s0040:				 s0040,
														s0067:				 s0067,
														s0072:				 s0072,
														s0112:				 s0112,
														s0139:				 s0139,
														s0233:				 s0233,
														s0514:				 s0514,
														s0564:				 s0564,
														s0567:				 s0567,
														s0608:				 s0608,
														s0649:				 s0649,
														s1197:				 s1197,
													    additional: 		 additional,
													    mode: 				 mode},
			function (data)
			{
				wait_dialog_hide();
				//alert(data);
				$("#cars").dialog("close");
				if(mode=="car_new")
				{
					//alert("customer_vehicle_id "+customer_vehicle_id+"\nvehicle_id "+vehicle_id+"\nid "+id);
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						var id_carfleet = $xml.find("id").text();
						$("#customer_vehicle_id").val(id_carfleet);
						car_status_save(id, order_id);
					}
				}
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
				{ text: "<?php echo t("Ok"); ?>", click: function() {$(this).dialog("close");} }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Achtung"); ?>!",
			width:300
		});
	}
	
	function bill_address_dialog(order_data, mode)
	{
		var ship_adr = 0;
		var button_text = "";
		var title_text = "";
		var html = '';
		
		if(order_data["ship_adr_id"]!="0" && order_data["ship_adr_id"]!=order_data["bill_adr_id"])
			ship_adr = 1;
		
		if(mode=="button")
		{
			button_text = "<?php echo t("Änderungen speichern"); ?>";
			title_text = "<?php echo t("Adresse(n) ändern"); ?>";
		}
		else
		{
			if(ship_adr==0)
			{
				if(order_data["bill_adr_lastname"].length>0 &&
				   order_data["bill_adr_street"].length>0 &&
				   order_data["bill_adr_number"].length>0 &&
				   order_data["bill_adr_city"].length>0 &&
				   order_data["bill_adr_zip"].length>0 &&
				   order_data["bill_adr_country"].length>0)
				{
					button_text = "<?php echo t("Adresse bestätigen"); ?>";
					title_text = "<?php echo t("Adresse bestätigen"); ?>";
					html += '<p style="float: left"><?php echo t("Bitte bestätigen Sie Ihre Rechnungsadresse:"); ?></p>';
				}
				else if(order_data["bill_adr_lastname"].length==0 &&
						order_data["bill_adr_street"].length==0 &&
						order_data["bill_adr_number"].length==0 &&
						order_data["bill_adr_city"].length==0 &&
						order_data["bill_adr_zip"].length==0 &&
						order_data["bill_adr_country"].length==0)
				{
					button_text = "<?php echo t("Adresse eingeben"); ?>";
					title_text = "<?php echo t("Adresse eingeben"); ?>";
					html += '<p style="float: left"><?php echo t("Bitte geben Sie Ihre Rechnungsadresse ein:"); ?></p>';
				}
				else if(order_data["bill_adr_lastname"].length==0 ||
						order_data["bill_adr_street"].length==0 ||
						order_data["bill_adr_number"].length==0 ||
						order_data["bill_adr_city"].length==0 ||
						order_data["bill_adr_zip"].length==0 ||
						order_data["bill_adr_country"].length==0)
				{
					button_text = "<?php echo t("Adresse speichern"); ?>";
					title_text = "<?php echo t("Adresse vervollständigen"); ?>";
					html += '<p style="float: left"><?php echo t("Bitte vervollständigen Sie Ihre Rechnungsadresse:"); ?></p>';
				}
			}
			else if(ship_adr==1)
			{
				if(order_data["ship_adr_lastname"].length>0 &&
				   order_data["ship_adr_street"].length>0 &&
				   order_data["ship_adr_number"].length>0 &&
				   order_data["ship_adr_city"].length>0 &&
				   order_data["ship_adr_zip"].length>0 &&
				   order_data["ship_adr_country"].length>0)
				{
					if(order_data["bill_adr_lastname"].length>0 &&
					   order_data["bill_adr_street"].length>0 &&
					   order_data["bill_adr_number"].length>0 &&
					   order_data["bill_adr_city"].length>0 &&
					   order_data["bill_adr_zip"].length>0 &&
					   order_data["bill_adr_country"].length>0)
					{
						button_text = "<?php echo t("Adresse bestätigen"); ?>";
						title_text = "<?php echo t("Adresse bestätigen"); ?>";
						html += '<p style="float: left"><?php echo t("Bitte bestätigen Sie Ihre Rechnungs- und Lieferadresse:"); ?></p>';
					}
					else if(order_data["bill_adr_lastname"].length==0 &&
							order_data["bill_adr_street"].length==0 &&
							order_data["bill_adr_number"].length==0 &&
							order_data["bill_adr_city"].length==0 &&
							order_data["bill_adr_zip"].length==0 &&
							order_data["bill_adr_country"].length==0)
					{
						button_text = "<?php echo t("Adresse eingeben"); ?>";
						title_text = "<?php echo t("Adresse eingeben"); ?>";
						html += '<p style="float: left"><?php echo t("Bitte geben Sie Ihre Rechnungsadresse ein:"); ?></p>';
					}
					else if(order_data["bill_adr_lastname"].length==0 ||
							order_data["bill_adr_street"].length==0 ||
							order_data["bill_adr_number"].length==0 ||
							order_data["bill_adr_city"].length==0 ||
							order_data["bill_adr_zip"].length==0 ||
							order_data["bill_adr_country"].length==0)
					{
						button_text = "<?php echo t("Adresse speichern"); ?>";
						title_text = "<?php echo t("Adresse vervollständigen"); ?>";
						html += '<p style="float: left"><?php echo t("Bitte vervollständigen Sie Ihre Rechnungsadresse:"); ?></p>';
					}
				}
				else if(order_data["ship_adr_lastname"].length==0 &&
						order_data["ship_adr_street"].length==0 &&
						order_data["ship_adr_number"].length==0 &&
						order_data["ship_adr_city"].length==0 &&
						order_data["ship_adr_zip"].length==0 &&
						order_data["ship_adr_country"].length==0)
				{
					if(order_data["bill_adr_lastname"].length>0 &&
					   order_data["bill_adr_street"].length>0 &&
					   order_data["bill_adr_number"].length>0 &&
					   order_data["bill_adr_city"].length>0 &&
					   order_data["bill_adr_zip"].length>0 &&
					   order_data["bill_adr_country"].length>0)
					{
						button_text = "<?php echo t("Adresse speichern"); ?>";
						title_text = "<?php echo t("Adresse vervollständigen"); ?>";
						html += '<p style="float: left"><?php echo t("Bitte vervollständigen Sie Ihre Lieferadresse:"); ?></p>';
					}
					else if(order_data["bill_adr_lastname"].length==0 &&
							order_data["bill_adr_street"].length==0 &&
							order_data["bill_adr_number"].length==0 &&
							order_data["bill_adr_city"].length==0 &&
							order_data["bill_adr_zip"].length==0 &&
							order_data["bill_adr_country"].length==0)
					{
						button_text = "<?php echo t("Adresse speichern"); ?>";
						title_text = "<?php echo t("Adresse eingeben"); ?>";
						html += '<p style="float: left"><?php echo t("Bitte geben Sie Ihre Rechnungsadresse ein und vervollständigen Sie Ihre Lieferadresse:"); ?></p>';
					}
					else if(order_data["bill_adr_lastname"].length==0 ||
							order_data["bill_adr_street"].length==0 ||
							order_data["bill_adr_number"].length==0 ||
							order_data["bill_adr_city"].length==0 ||
							order_data["bill_adr_zip"].length==0 ||
							order_data["bill_adr_country"].length==0)
					{
						button_text = "<?php echo t("Adresse speichern"); ?>";
						title_text = "<?php echo t("Adresse vervollständigen"); ?>";
						html += '<p style="float: left"><?php echo t("Bitte vervollständigen Sie Ihre Rechnungs- und Lieferadresse:"); ?></p>';
					}
				}
				else if(order_data["ship_adr_lastname"].length==0 ||
						order_data["ship_adr_street"].length==0 ||
						order_data["ship_adr_number"].length==0 ||
						order_data["ship_adr_city"].length==0 ||
						order_data["ship_adr_zip"].length==0 ||
						order_data["ship_adr_country"].length==0)
				{
					if(order_data["bill_adr_lastname"].length>0 &&
					   order_data["bill_adr_street"].length>0 &&
					   order_data["bill_adr_number"].length>0 &&
					   order_data["bill_adr_city"].length>0 &&
					   order_data["bill_adr_zip"].length>0 &&
					   order_data["bill_adr_country"].length>0)
					{
						button_text = "<?php echo t("Adresse bestätigen"); ?>";
						title_text = "<?php echo t("Adresse bestätigen"); ?>";
						html += '<p style="float: left"><?php echo t("Bitte vervollständigen Sie Ihre Lieferadresse:"); ?></p>';
					}
					else if(order_data["bill_adr_lastname"].length==0 &&
							order_data["bill_adr_street"].length==0 &&
							order_data["bill_adr_number"].length==0 &&
							order_data["bill_adr_city"].length==0 &&
							order_data["bill_adr_zip"].length==0 &&
							order_data["bill_adr_country"].length==0)
					{
						button_text = "<?php echo t("Adresse eingeben"); ?>";
						title_text = "<?php echo t("Adresse eingeben"); ?>";
						html += '<p style="float: left"><?php echo t("Bitte geben Sie Ihre Rechnungsadresse ein und vervollständigen Sie Ihre Lieferadresse:"); ?></p>';
					}
					else if(order_data["bill_adr_lastname"].length==0 ||
							order_data["bill_adr_street"].length==0 ||
							order_data["bill_adr_number"].length==0 ||
							order_data["bill_adr_city"].length==0 ||
							order_data["bill_adr_zip"].length==0 ||
							order_data["bill_adr_country"].length==0)
					{
						button_text = "<?php echo t("Adresse speichern"); ?>";
						title_text = "<?php echo t("Adresse vervollständigen"); ?>";
						html += '<p style="float: left"><?php echo t("Bitte vervollständigen Sie Ihre Rechnungs- und Lieferadresse:"); ?></p>';
					}
				}
			}
		}
		
		html += '<table>';
		html += '	<tr>';
		html += '		<td class="bill_address" style="font-size: 17px; font-weight: bold"><?php echo t("Rechnungsanschrift");?>:</td>';
		html += '		<td></td>';
		html += '		<td class="bill_address" style="font-size: 17px; font-weight: bold; padding-left: 30px"><?php echo t("Lieferanschrift");?>:</td>';
		html += '		<td></td>';
		html += '	<tr>';
		html += '  </tr>';
		html += '    <td class="bill_address"><?php echo t("Firma"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="company" style="width: 200px; float:left"></td>';
		html += '    <td class="bill_address" style="padding-left: 30px"><?php echo t("Firma"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="ship_company" style="width: 200px; float:left"></td>';
		html += '  </tr>';
		html += '  <tr>';
		html += '    <td class="bill_address"><?php echo t("Vorname"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="firstname" style="width: 200px; float:left"></td>';
		html += '    <td class="bill_address" style="padding-left: 30px"><?php echo t("Vorname"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="ship_firstname" style="width: 200px; float:left"></td>';
		html += '  </tr>';
		html += '  <tr>';
		html += '    <td class="bill_address"><?php echo t("Nachname"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="lastname" style="width: 200px; float:left"></td>';
		html += '    <td class="bill_address" style="padding-left: 30px"><?php echo t("Nachname"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="ship_lastname" style="width: 200px; float:left"></td>';
		html += '  </tr>';
		html += '  <tr>';
		html += '    <td class="bill_address"><?php echo t("Straße/Hausnummer"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="street" style="width: 157px; float:left"><input type="text" id="number" size="4" maxlength="20" style="float: left"></td>';
		html += '    <td class="bill_address" style="padding-left: 30px"><?php echo t("Straße/Hausnummer"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="ship_street" style="width: 157px; float:left"><input type="text" id="ship_number" size="4" maxlength="20" style="float: left"></td>';
		html += '  </tr>';
		html += '  <tr>';
		html += '    <td class="bill_address"><?php echo t("Adressenzusatz"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="additional" style="width: 200px; float:left"></td>';
		html += '    <td class="bill_address" style="padding-left: 30px"><?php echo t("Adressenzusatz"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="ship_additional" style="width: 200px; float:left"></td>';
		html += '  </tr>';
		html += '  <tr>';
		html += '    <td class="bill_address"><?php echo t("Plz/Ort"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="zip" size="6" maxlength="20" style="float: left; margin-left: 0px"><input type="text" id="city" style="float: left; width: 143px"></td>';
		html += '    <td class="bill_address" style="padding-left: 30px"><?php echo t("Plz/Ort"); ?>:</td>';
		html += '    <td class="bill_address_input"><input type="text" id="ship_zip" size="6" maxlength="20" style="float: left; margin-left: 0px"><input type="text" id="ship_city" style="float: left; width: 143px"></td>';
		html += '  </tr>';
		html += '  <tr>';
		html += '    <td class="bill_address"><?php echo t("Land"); ?>:</td>';
		html += '    <td class="bill_address_input">';
		html += '      <select id="country" style="width: 202px; float: left">';
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CountriesGet"},
			function (data)
			{
				//show_status2(data);
				try
				{
					var actual_country = "";
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						$xml.find("shop_countries").each(
							function()
							{
								if($(this).find("active").text()=="1")
									html += '<option value="' + $(this).find("country_code").text() + '">' + $(this).find("country").text() + '</option>';
								if(order_data["bill_adr_country_id"]==$(this).find("id_country").text())
								 	actual_country = $(this).find("country_code").text();
							});
						html += '      </select>';
						html += '    </td>';
						html += '    <td class="bill_address" style="padding-left: 30px"><?php echo t("Land"); ?>:</td>';
						html += '    <td class="bill_address_input">';
						html += '      <select id="ship_country" style="width: 202px; float: left">';
						var ship_actual_country = "";
						$xml.find("shop_countries").each(
							function()
							{
								if($(this).find("active").text()=="1")
									html += '<option value="' + $(this).find("country_code").text() + '">' + $(this).find("country").text() + '</option>';
								if(order_data["ship_adr_country_id"]==$(this).find("id_country").text())
								 	ship_actual_country = $(this).find("country_code").text();
							});
						html += '      </select>';
						html += '    </td>';
						html += '  </tr>';
						html += '</table>';
						
						$("#bill_address").html(html);
						
						list_sort("country");
						list_sort("ship_country");
						
						$("#country").val("DE");
						$("#ship_country").val("DE");
						
						$("#company").val(order_data["bill_adr_company"]);
						$("#firstname").val(order_data["bill_adr_firstname"]);
						$("#lastname").val(order_data["bill_adr_lastname"]);
						$("#street").val(order_data["bill_adr_street"]);
						$("#number").val(order_data["bill_adr_number"]);
						$("#additional").val(order_data["bill_adr_additional"]);
						$("#zip").val(order_data["bill_adr_zip"]);
						$("#city").val(order_data["bill_adr_city"]);
						if(actual_country != "")
							$("#country").val(actual_country);
						
						if(ship_adr==1)
						{	
							$("#ship_company").val(order_data["ship_adr_company"]);
							$("#ship_firstname").val(order_data["ship_adr_firstname"]);
							$("#ship_lastname").val(order_data["ship_adr_lastname"]);
							$("#ship_street").val(order_data["ship_adr_street"]);
							$("#ship_number").val(order_data["ship_adr_number"]);
							$("#ship_additional").val(order_data["ship_adr_additional"]);
							$("#ship_zip").val(order_data["ship_adr_zip"]);
							$("#ship_city").val(order_data["ship_adr_city"]);
							if(ship_actual_country != "")
								$("#ship_country").val(ship_actual_country);
						}
						
						$("#company").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						$("#firstname").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						if($("#lastname").val()=="")
							$("#lastname").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						else
							$("#lastname").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
	
						$("#lastname").keyup(function(){ 
						   if($("#lastname").val()!="")
							{
								$("#lastname").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#lastname").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
						if($("#street").val()=="")
							$("#street").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						else
							$("#street").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						$("#street").keyup(function(){ 
						   if($("#street").val()!="")
							{
								$("#street").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#street").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
						if($("#number").val()=="")
							$("#number").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						else
							$("#number").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						$("#number").keyup(function(){ 
						   if($("#number").val()!="")
							{
								$("#number").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#number").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
						$("#additional").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						if($("#zip").val()=="")
							$("#zip").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						else
							$("#zip").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						$("#zip").keyup(function(){ 
						   if($("#zip").val()!="")
							{
								$("#zip").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#zip").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
						if($("#city").val()=="")
							$("#city").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						else
							$("#city").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						$("#city").keyup(function(){ 
						   if($("#city").val()!="")
							{
								$("#city").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#city").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
						
						$("#ship_company").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						$("#ship_firstname").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						$("#ship_lastname").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						if(ship_adr==1)
						{
							if($("#ship_lastname").val()=="")
								$("#ship_lastname").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							else
								$("#ship_lastname").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							$("#ship_lastname").keyup(function(){ 
							   if($("#ship_lastname").val()!="")
								{
									$("#ship_lastname").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
								}
								else
								{
									$("#ship_lastname").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
								}
							});
						}
						$("#ship_street").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						if(ship_adr==1)
						{
							if($("#ship_street").val()=="")
								$("#ship_street").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							else
								$("#ship_street").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							$("#ship_street").keyup(function(){ 
							   if($("#ship_street").val()!="")
								{
									$("#ship_street").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
								}
								else
								{
									$("#ship_street").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
								}
							});
						}
						$("#ship_number").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						if(ship_adr==1)
						{
							if($("#ship_number").val()=="")
								$("#ship_number").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							else
								$("#ship_number").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							$("#ship_number").keyup(function(){ 
							   if($("#ship_number").val()!="")
								{
									$("#ship_number").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
								}
								else
								{
									$("#ship_number").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
								}
							});
						}
						$("#ship_additional").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						$("#ship_zip").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						if(ship_adr==1)
						{
							if($("#ship_zip").val()=="")
								$("#ship_zip").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							else
								$("#ship_zip").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							$("#ship_zip").keyup(function(){ 
							   if($("#ship_zip").val()!="")
								{
									$("#ship_zip").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
								}
								else
								{
									$("#ship_zip").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
								}
							});
						}
						$("#ship_city").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						if(ship_adr==1)
						{
							if($("#ship_city").val()=="")
								$("#ship_city").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							else
								$("#ship_city").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							$("#ship_city").keyup(function(){ 
							   if($("#ship_city").val()!="")
								{
									$("#ship_city").css({"background-color": "#FFFFFF", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
								}
								else
								{
									$("#ship_city").css({"background-color": "#fdd", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
								}
							});
						}
							
						$("#bill_address").dialog
						({	
							buttons:
							[
								{ text: button_text, click: function() {bill_address_update(order_data); $(this).dialog("close");} },
								{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
							],
							open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
							closeOnEscape: false,
							closeText:"<?php echo t("Fenster schließen"); ?>",
							//hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:title_text,
							width:850
						});
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
	
	function bill_address_update(order_data)
	{
		var addresstype = "bill";
		
		if($("#ship_company").val()=="" &&
			$("#ship_firstname").val()=="" &&
			$("#ship_lastname").val()=="" &&
			$("#ship_street").val()=="" &&
			$("#ship_number").val()=="" &&
			$("#ship_additional").val()=="" &&
			$("#ship_zip").val()=="" &&
			$("#ship_city").val()=="")
		{
			addresstype = "both";
		}
		
		$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "OrderAddressUpdate",
																	OrderID:			order_data["id_order"],
																	country_code:		$("#country").val(),
																	addresstype:		addresstype,
																	shop_id:			order_data["shop_id"],
																	company:			$("#company").val(),
																	firstname:			$("#firstname").val(),
																	lastname:			$("#lastname").val(),
																	street:				$("#street").val(),
																	number:				$("#number").val(),
																	additional:			$("#additional").val(),
																	zip:				$("#zip").val(),
																	city:				$("#city").val(),
																	customer_id:		order_data["customer_id"],
																	usermail:			order_data["usermail"],
																	userphone:			order_data["userphone"]},
			function (data)
			{
				//alert(data);
				//show_status2(data);
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{	
						if(addresstype=="bill")
						{
							addresstype="ship";
							$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "OrderAddressUpdate",
																	OrderID:			order_data["id_order"],
																	country_code:		$("#ship_country").val(),
																	addresstype:		addresstype,
																	shop_id:			order_data["shop_id"],
																	company:			$("#ship_company").val(),
																	firstname:			$("#ship_firstname").val(),
																	lastname:			$("#ship_lastname").val(),
																	street:				$("#ship_street").val(),
																	number:				$("#ship_number").val(),
																	additional:			$("#ship_additional").val(),
																	zip:				$("#ship_zip").val(),
																	city:				$("#ship_city").val(),
																	customer_id:		order_data["customer_id"],
																	usermail:			order_data["usermail"],
																	userphone:			order_data["userphone"]},
								function (data)
								{
									//alert(data);
									//show_status2(data);
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
						
						if(addresstype=="both")					
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

//Ab hier: Bestelldetails
	var ignore_cars = new Array();

	function show_order_item_list(order_data, service_ready, ignore_cars, kritcnt)
	{
		var check_service = 1;
		for (i=0; i<kritcnt; i++)
		{
			if(service_ready[i]==0)
			{
				check_service = 0;
			}
		}
		if(check_service==1)
		{
			//show_status2(print_r(order_data));
			//alert(order_data["bill_adress_manual_update"]);
			//alert(order_data["payments_type_id"]);
			//alert( order_data["bill_adr_country"]);
			if((order_data["bill_adr_lastname"].length==0 ||
			   order_data["bill_adr_street"].length==0 ||
			   order_data["bill_adr_number"].length==0 ||
			   order_data["bill_adr_city"].length==0 ||
			   order_data["bill_adr_zip"].length==0 ||
			   order_data["bill_adr_country"].length==0 ||
			   order_data["bill_adr_id"]==0 ||
			   order_data["bill_address_manual_update"]==0 || 
			   (order_data["ship_adr_id"]!="0" && order_data["ship_adr_id"]!=order_data["bill_adr_id"] && 
			   (order_data["ship_adr_lastname"].length==0 ||
			   order_data["ship_adr_street"].length==0 ||
			   order_data["ship_adr_number"].length==0 ||
			   order_data["ship_adr_city"].length==0 ||
			   order_data["ship_adr_zip"].length==0 ||
			   order_data["ship_adr_country"].length==0))) &&
			   order_data["payments_type_id"]!=4)
			{
				bill_address_dialog(order_data, "");
			}

			var i2 =				   new Array();
			var customer_vehicle_id2 = new Array();
			var i0649 =				   new Array();
			var status_id = 		   order_data["status_id"];
			
			var html = '';
			html += '<p>';
			html += '<a href="<?php echo PATH.$_GET["lang"]; ?>/online-shop/mein-konto/"><?php echo t("Mein Konto"); ?> </a>';
			html += '><a href="<?php echo PATH.$_GET["lang"]; ?>/online-shop/mein-konto/bestellungen/"> <?php echo t("Bestellungen"); ?></a>';
			html += ' > Bestellung ';
			html += '</p>';
			html += '<h1><?php echo t("Bestellung")." #".$_GET["id_order"]; ?><p style="display: inline; font-size:15px; font-weight: normal"> vom ' + order_data["orderDate"] + '</p></h1>';
			
			//Rechnungsanschrift
			if(order_data["ship_adr_id"]!="0" && order_data["ship_adr_id"]!=order_data["bill_adr_id"])
				html += '<div style="float: left"><p><b><?php echo t("Rechnungsanschrift"); ?>:</b>';
			else
				html += '<div style="float: left"><p><b><?php echo t("Rechnungs-/Lieferanschrift"); ?>:</b>';
				
			html += '<br>';
			if(order_data["bill_adr_company"]!="")
			{
				html += order_data["bill_adr_company"] + '<br>';
			}
			html += order_data["bill_adr_firstname"] + ' ' + order_data["bill_adr_lastname"] + '<br>';
			html += order_data["bill_adr_street"] + ' ' + order_data["bill_adr_number"] + '<br>';
			html += order_data["bill_adr_zip"] + ' ' + order_data["bill_adr_city"] + '<br>';
			if(order_data["bill_adr_additional"]!="")
			{
				html += order_data["bill_adr_additional"] + '<br>';
			}
			html += order_data["bill_adr_country"] + '<br><br />';
			if(order_data["payments_type_id"]!=4 && (order_data["status_id"]=="1" || order_data["status_id"]=="7"))
			{
				html += '<input type="button" id="address_button" value="<?php echo t("Adresse(n) ändern"); ?>" style="font-size: 10px">';
			}
			html += '</p></div>';
			
			//Lieferanschrift
			if(order_data["ship_adr_id"]!="0" && order_data["ship_adr_id"]!=order_data["bill_adr_id"])
			{
				html += '<div style="float: left; padding-left: 50px"><p><b><?php echo t("Lieferanschrift"); ?>:</b>';
				html += '<br>';
				if(order_data["ship_adr_company"]!="")
				{
					html += order_data["ship_adr_company"] + '<br>';
				}
				html += order_data["ship_adr_firstname"] + ' ' + order_data["ship_adr_lastname"] + '<br>';
				html += order_data["ship_adr_street"] + ' ' + order_data["ship_adr_number"] + '<br>';
				html += order_data["ship_adr_zip"] + ' ' + order_data["ship_adr_city"] + '<br>';
				if(order_data["ship_adr_additional"]!="")
				{
					html += order_data["ship_adr_additional"] + '<br>';
				}
				html += order_data["ship_adr_country"] + '<br><br />';
				html += '</p></div>';
			}
			
			//Rechnungstabelle
			html += '<table class="hover">';
			html += '<tr>';
			html += '<th style="width:300px"><?php echo t("Produktbeschreibung"); ?></th>';
			html += '<th><?php echo t("Menge"); ?></th>';
			html += '<th style="width:60px"><?php echo t("EK"); ?></th>';
			html += '<th style="width:60px"><?php echo t("Gesamt"); ?></th>';
			html += '<th style="width:200px"><?php echo t("Fahrzeugauswahl"); ?></th>';
			html += '<th style="width:20px"></th>';
			html += '</tr>';
			
			//Items
			var kritdo4 = 0; //seiten-reload?
			var car_data_input = 0;
			var order_id = order_data["id_order"];
			for (i=0; i<order_data["orderPositions"]; i++)
			{
				//alert(order_data["item"][i]["OrderItemItemID"]);
				if(order_data["item"][i]["OrderItemItemID"]!="28760")//Herbstaktion 2013
				{
					var customer_vehicle_id = order_data["item"][i]["OrderItemCustomerVehicleID"];
					
					var kritdo= 0; //Müssen Kriterien angezeigt werden?
					var kritdo2=0; //rote Zeile?
					var kritdo3=0; //noch leere Kriterienfelder?
					var kritdo5=0;
					if(order_data["item"][i]["OrderItemVehicleDateBuilt"]=="0" || order_data["item"][i]["OrderItemVehicleFIN"]=="")
					{
						kritdo4=1;
						kritdo5=1;
					}
					if(typeof order_data["item"][i]["criteria"] !== 'undefined' && order_data["item"][i]["criteria"].length>0)
					{
						for(j=0; j<order_data["item"][i]["criteria"].length; j++)
						{
							if(order_data["item"][i]["criteria"][j]["KritShow"] == "show")
							{
								kritdo=1;
							}
							if(order_data["item"][i]["criteria"][j]["UserKritBez"] == "")
							{
								kritdo3=1;
								kritdo4=1;
								kritdo5=1;
							}
						}	
					}
					//alert(kritdo+" "+kritdo3);
					if(!(ignore_cars && typeof ignore_cars[customer_vehicle_id] !== 'undefined')) //farbige Zeile setzen
					{
						if((order_data["item"][i]["OrderItemVehicleDateBuilt"]=="0" || order_data["item"][i]["OrderItemVehicleFIN"]=="" || kritdo==1) && customer_vehicle_id > 0 && car_data_input==0 && (status_id==1 || status_id==7) && order_data["orderComplete"]=="1"/* && kritdo3==1*/ && (order_data["item"][i]["OrderItemVehicleDateBuilt"]=="0" || order_data["item"][i]["OrderItemVehicleFIN"]=="" || kritdo3==1))
						{
							html += '<tr style="background: #FFCCCC">';
							kritdo2 = 1;
							car_data_input = 1;
						}
						else
						{
							html += '<tr>';
						}
					}
					else
					{
						html += '<tr>';
					}
					//html += '<tr>';
					if(order_data["Order_type"]=="business")
					{
						if(order_data["item"][i]["orderItemTotalCollateralNet"]!="0,00")
						{
							html += '<td style="width:300px">';
							if(order_data["orderComplete"]==1 && (status_id==1 || status_id==7))
							{
								if((kritdo==1 || kritdo5==1) && kritdo2==1)
								{
									html += '<a href="javascript:show_hide(\'' + order_data["item"][i]["OrderItemID"] + 'td\', \'' + order_data["item"][i]["OrderItemID"] + 'img\');">';
									html += '<img src="<?php echo PATH; ?>/images/icons/16x16/down.png" id="' + order_data["item"][i]["OrderItemID"] + 'img" style="vertical-align:-3px;"></a>';
								}
								else if((kritdo==1 || kritdo5==1) && kritdo2==0)
								{
									html += '<a href="javascript:show_hide(\'' + order_data["item"][i]["OrderItemID"] + 'td\', \'' + order_data["item"][i]["OrderItemID"] + 'img\');">';
									html += '<img src="<?php echo PATH; ?>/images/icons/16x16/right.png" id="' + order_data["item"][i]["OrderItemID"] + 'img" style="vertical-align:-3px;"></a>';
								}
							}
							html += order_data["item"][i]["OrderItemDesc"];
							html += '<br><?php echo t("zzgl."); ?> ' + order_data["item"][i]["orderItemTotalCollateralNet"] + ' € <?php echo t("Altteilpfand"); ?></td>';
						}
						else
						{
							//alert(kritdo+" "+kritdo4+" "+kritdo2);
							html += '<td style="width:300px">';
							if(order_data["orderComplete"]==1 && (status_id==1 || status_id==7))
							{
								if((kritdo==1 || kritdo5==1) && kritdo2==1)
								{
									html += '<a href="javascript:show_hide(\'' + order_data["item"][i]["OrderItemID"] + 'td\', \'' + order_data["item"][i]["OrderItemID"] + 'img\');">';
									html += '<img src="<?php echo PATH; ?>/images/icons/16x16/down.png" id="' + order_data["item"][i]["OrderItemID"] + 'img" style="vertical-align:-3px;"></a>';
								}
								else if((kritdo==1 || kritdo5==1) && kritdo2==0)
								{
									html += '<a href="javascript:show_hide(\'' + order_data["item"][i]["OrderItemID"] + 'td\', \'' + order_data["item"][i]["OrderItemID"] + 'img\');">';
									html += '<img src="<?php echo PATH; ?>/images/icons/16x16/right.png" id="' + order_data["item"][i]["OrderItemID"] + 'img" style="vertical-align:-3px;"></a>';
								}
							}
							html += order_data["item"][i]["OrderItemDesc"] + '</td>';
						}
						html += '<td>' + order_data["item"][i]["OrderItemAmount"] + '</td>';
						html += '<td style="width:60px">€ ' + order_data["item"][i]["orderItemPriceNet"] + '</td>';
						html += '<td style="width:60px">€ ' + order_data["item"][i]["orderItemTotalNet"] + '</td>';
					}
					else if(order_data["Order_type"]=="customer")
					{
						if(order_data["item"][i]["orderItemTotalCollateralGross"]!="0,00")
						{
							html += '<td style="width:300px">';
							if(order_data["orderComplete"]==1 && (status_id==1 || status_id==7))
							{
								if((kritdo==1 || kritdo5==1) && kritdo2==1)
								{
									html += '<a href="javascript:show_hide(\'' + order_data["item"][i]["OrderItemID"] + 'td\', \'' + order_data["item"][i]["OrderItemID"] + 'img\');">';
									html += '<img src="<?php echo PATH; ?>/images/icons/16x16/down.png" id="' + order_data["item"][i]["OrderItemID"] + 'img" style="vertical-align:-3px;"></a>';
								}
								else if((kritdo==1 || kritdo5==1) && kritdo2==0)
								{
									html += '<a href="javascript:show_hide(\'' + order_data["item"][i]["OrderItemID"] + 'td\', \'' + order_data["item"][i]["OrderItemID"] + 'img\');">';
									html += '<img src="<?php echo PATH; ?>/images/icons/16x16/right.png" id="' + order_data["item"][i]["OrderItemID"] + 'img" style="vertical-align:-3px;"></a>';
								}
							}
							html += order_data["item"][i]["OrderItemDesc"];
							html += '<br><?php echo t("zzgl."); ?> ' + order_data["item"][i]["orderItemTotalCollateralGross"] + ' € <?php echo t("Altteilpfand"); ?></td>';
						}
						else
						{
							html += '<td style="width:300px">';
							if(order_data["orderComplete"]==1 && (status_id==1 || status_id==7))
							{
								if((kritdo==1 || kritdo5==1) && kritdo2==1)
								{
									html += '<a href="javascript:show_hide(\'' + order_data["item"][i]["OrderItemID"] + 'td\', \'' + order_data["item"][i]["OrderItemID"] + 'img\');">';
									html += '<img src="<?php echo PATH; ?>/images/icons/16x16/down.png" id="' + order_data["item"][i]["OrderItemID"] + 'img" style="vertical-align:-3px;"></a>';
								}
								else if((kritdo==1 || kritdo5==1) && kritdo2==0)
								{
									html += '<a href="javascript:show_hide(\'' + order_data["item"][i]["OrderItemID"] + 'td\', \'' + order_data["item"][i]["OrderItemID"] + 'img\');">';
									html += '<img src="<?php echo PATH; ?>/images/icons/16x16/right.png" id="' + order_data["item"][i]["OrderItemID"] + 'img" style="vertical-align:-3px;"></a>';
								}
							}
							html += order_data["item"][i]["OrderItemDesc"] + '</td>';
						}
						html += '<td>' + order_data["item"][i]["OrderItemAmount"] + '</td>';
						html += '<td style="width:60px">€ ' + order_data["item"][i]["orderItemPriceGross"] + '</td>';
						html += '<td style="width:60px">€ ' + order_data["item"][i]["orderItemTotalGross"] + '</td>';
					}
					
					//var customer_vehicle_id = order_data["item"][i]["OrderItemCustomerVehicleID"];
					if(order_data["item"][i]["OrderItemCustomerVehicleID"]=="")
					{
						customer_vehicle_id = 0;
					}
					
					if(status_id==1 || status_id==7)
					{
						if(customer_vehicle_id == 0)
						{
							html += '<td><a href="javascript:car_input_show(' + customer_vehicle_id + ',' + order_data["item"][i]["OrderItemID"] + ',' + order_id + ');"><?php echo t("Fahrzeug auswählen"); ?></a></td>';
						}
						else if(customer_vehicle_id > 0)
						{
							html += '<td>' + order_data["item"][i]["OrderItemVehicleBrand"] + ' ' + order_data["item"][i]["OrderItemVehicleModel"] + ' ' + order_data["item"][i]["OrderItemVehicleType"] + '<a href="javascript:car_input_show(' + customer_vehicle_id + ',' + order_data["item"][i]["OrderItemID"] + ',' + order_id + ');"><br><?php echo t("Fahrzeug ändern"); ?></a></td>';
						}
					}
					else
					{
						html += '<td><?php echo t("Keine Eingabe nötig"); ?></td>';
					}
					
					if(order_data["item"][i]["OrderItemVehicleID"]>0)
					{
						html += '<td style="width:20px"><img src="<?php echo PATH; ?>/images/icons/24x24/accept.png" alt="" style="vertical-align:-3px;"></td>';
					}
					else if(order_data["item"][i]["OrderItemVehicleID"]==0)
					{
						html += '<td style="width:20px"><img src="<?php echo PATH; ?>/images/icons/24x24/warning.png" alt="" style="vertical-align:-3px;"></td>';
					}
					html += '</tr>';
					
	//*********************Eingabezeile für fehlende Fahrzeugdaten*****************************************
				
					//if(!(ignore_cars && typeof ignore_cars[customer_vehicle_id] !== 'undefined'))
					//{
					if((order_data["item"][i]["OrderItemVehicleDateBuilt"]=="0" || order_data["item"][i]["OrderItemVehicleFIN"]=="" || kritdo==1) && customer_vehicle_id > 0 /*&& car_data_input==0 */&& (status_id==1 || status_id==7) && order_data["orderComplete"]=="1")
					{
						if(kritdo2==1)
						{
							html += '<tr id="' + order_data["item"][i]["OrderItemID"] + 'td">';
						}
						else
						{
							html += '<tr id="' + order_data["item"][i]["OrderItemID"] + 'td" style="display: none">';
						}
						//html += '<tr>';
						html += '<td colspan="6" style="color: red; background-color: rgb(220,220,220); font-weight: bold">';
						
						
						html += '<table>';
						html += '<tr>';
						html += '<td width=50%>';
						html += '<p style="color: black; display: inline">' + order_data["item"][i]["OrderItemVehicleBrand"] + ' ' + order_data["item"][i]["OrderItemVehicleModel"] + ' ' + order_data["item"][i]["OrderItemVehicleType"] + ':</p>';
						html += '</td>';
						html += '<td>';
						html += '<button id="' + order_data["item"][i]["OrderItemID"] + 'databutton"><?php echo t("Daten speichern"); ?></button>';
						i2[i] = i;
						customer_vehicle_id2[i] = customer_vehicle_id;
						html += '</td>';
						html += '</tr>';
						
						if(order_data["item"][i]["OrderItemVehicleDateBuilt"]=="0")
						{
							html += '<tr style="padding: 0px">';
							html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
							html += ' <?php echo t("Erstzulassung (mm/jjjj)"); ?>:';
							html += '</td>';
							html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
							html += '<input type="text" id="month2' + order_data["item"][i]["OrderItemID"] + '" size="3" maxlength="2" style="width: 20px; padding: 0px; margin: 0px"><p style="color: black; display: inline">/</p><input type="text" id="year2' + order_data["item"][i]["OrderItemID"] + '" size="3" maxlength="4" style="width: 35px; padding: 0px; margin: 0px">';
							html += '</td>';
							html += '</tr>';
						}
						if(order_data["item"][i]["OrderItemVehicleFIN"]=="")
						{
							html += '<tr style="padding: 0px">';
							html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
							html += ' <?php echo t("Fahrgestellnummer"); ?>:';
							html += '</td>';
							html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
							html += '<input type="text" id="fin2' + order_data["item"][i]["OrderItemID"] + '" name="fin" maxlength="17" style="margin: 0px; padding: 0px">';
							html += '</td>';
							html += '</tr>';
						}
	//**************************************************************************** Kriterien/Zusatzdaten
						//show_order_data(order_data);
						var field_test = new Array();
						for (var j = 0; j < order_data["item"][i]["criteria"].length; j++)
						{
							html += '<tr style="padding: 0px">';
							if(order_data["item"][i]["criteria"][j]["KritShow"] == "show")
							{
								if(typeof krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]] !== 'undefined') //fix
								{
									//alert(field_test[order_data["item"][i]["criteria"][j]["KritNr"]]);
									if(typeof field_test[order_data["item"][i]["criteria"][j]["KritNr"]]=='undefined')
									{
										if(order_data["item"][i]["criteria"][j]["TabNr"]=="000")
										{
											if(order_data["item"][i]["criteria"][j]["KritNr"]=="0649")
											{
												/*var i0649 = i;
												var j0649 = j;*/
												i0649[i] = j;
											}
											if(typeof krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["name"] !== 'undefined')
											{
												html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
												html += '' + krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["name"] + ':';
												html += '</td>';
											}
											else
											{
												html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
												html += '' + order_data["item"][i]["criteria"][j]["KritBez"] + ':';
												html += '</td>';
											}
											html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
											html += '<input type="text" id="' + krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["column"] + order_data["item"][i]["OrderItemID"] + '" maxlength="20" style="margin: 0px; padding: 0px">';
											html += '</td>';
										}
										else
										{
											if(typeof krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["name"] !== 'undefined')
											{
												html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
												html += '' + krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["name"] + ':';
												html += '</td>';
											}
											else
											{
												html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
												html += '' + order_data["item"][i]["criteria"][j]["KritBez"] + ':';
												html += '</td>';
											}
											html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
											html += '<select id="' + krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["column"] + order_data["item"][i]["OrderItemID"] + '" style="width: 175px; margin: 0px">';
											html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
											for(var value in order_data["item"][i]["criteria"][j]["TabItems"])
											{
												html += '<option value="' + value + '">' + order_data["item"][i]["criteria"][j]["TabItems"][value] + '</option>';
											}
											html += '</select>';
											html += '</td>';
										}
										field_test[order_data["item"][i]["criteria"][j]["KritNr"]] = 1;
									}
								}
								else //variable
								{
									html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
									html += '' + order_data["item"][i]["criteria"][j]["KritBez"] + ':' + order_data["item"][i]["criteria"][j]["KritWertBez"] + ':';
									html += '</td>';
									html += '<td style="color: red; background-color: rgb(220,220,220); font-weight: bold; border: 0px; padding: 0px">';
//									html += '<select id="' + order_data["item"][i]["criteria"][j]["KritNr"] + order_data["item"][i]["criteria"][j]["KritWert"] + order_data["item"][i]["OrderItemID"] + '" style="margin: 0px">';
									html += '<select id="' + order_data["item"][i]["criteria"][j]["KritNr"] + order_data["item"][i]["criteria"][j]["KritWert"].replace( /\s/g, "" ) + order_data["item"][i]["OrderItemID"] + '" style="margin: 0px">';
									if(order_data["item"][i]["criteria"][j]["UserKritBez"] == "0")
									{
										html += '<option value=""><?php echo t("bitte auswählen"); ?></option>';
										html += '<option value="1">Ja</option>';
										html += '<option value="0" selected>Nein</option>';
									}
									else if(order_data["item"][i]["criteria"][j]["UserKritBez"] == "1")
									{
										html += '<option value=""><?php echo t("bitte auswählen"); ?></option>';
										html += '<option value="1" selected>Ja</option>';
										html += '<option value="0">Nein</option>';
									}
									else
									{
										html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
										html += '<option value="1">Ja</option>';
										html += '<option value="0">Nein</option>';
									}
									html += '</select>';
									html += '</td>';
								}
							}
							html += '</tr>';
						}
						html += '</tr>';
	//****************************************************************************
	
						i2[i] = i;
						customer_vehicle_id2[i] = customer_vehicle_id;
						html += '</table>';
						
						
						html += '</td>';
						html += '</tr>';
						//car_data_input = 1;
					}
				//} //ignorecars if
				}//Ende Herbstaktion
				else if(order_data["item"][i]["OrderItemItemID"]=="28760")//Herbstaktion 2013
				{
					html += '<tr>';
					html += '<td><?php echo t("Gutschrift aus Rabattaktion");?></td>';
					html += '<td>' + order_data["item"][i]["OrderItemAmount"] + '</td>';
					html += '<td style="width:60px">€ ' + order_data["item"][i]["orderItemPriceGross"] + '</td>';
					html += '<td style="width:60px">€ ' + order_data["item"][i]["orderItemTotalGross"] + '</td>';
					html += '<td></td><td></td>';
					html += '</tr>';
				}
			}
			
			//Summen
			if(order_data["Order_type"]=="business")
			{
				if(order_data["orderItemsTotalCollateralNet"]!="0,00")
				{
					html += '<tr>';
					html += '<td colspan="3"><?php echo t("Altteilpfand für"); ?> ' +  order_data["orderCollateralCount"] + ' <?php echo t("Artikel"); ?>';
					if(order_data["orderCollateralCount"]==1)
					{
						html += '<br><?php echo t("Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet."); ?></td>';
					}
					else
					{
						html += '<br><?php echo t("Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet."); ?></td>';
					}
					html += '<td>€ ' + order_data["orderItemsTotalCollateralNet"] + '</td>';
					html += '</tr>';
				}
				html += '<tr>';
				if(order_data["shipping_type_id"]=="0")
				{
					html += '<td colspan="3">' + order_data["shipping_details"] + '</td>';
				}
				else
				{
					html += '<td colspan="3">' + order_data["shipping_title"] + '</td>';
				}
				html += '<td>€ ' + order_data["shippingCostsNet"] + '</td>';
				html += '</tr>';
				html += '<tr>';
				html += '<td colspan="3"><?php echo t("Nettogesamtwert"); ?></td>';
				html += '<td>€ ' + order_data["orderTotalNet"] + '</td>';
				html += '</tr>';
				html += '<tr>';
				html += '<td colspan="3"><?php echo t("gesetzliche Umsatzsteuer"); ?> <?php echo UST; ?>%</td>';
				html += '<td>€ ' + order_data["orderTotalTax"] + '</td>';
				html += '</tr>';
			}
			else if(order_data["Order_type"]=="customer")
			{
				if(order_data["orderItemsTotalCollateralGross"]!="0,00")
				{
					html += '<tr>';
					html += '<td colspan="3"><?php echo t("Altteilpfand für"); ?> ' +  order_data["orderCollateralCount"] + ' <?php echo t("Artikel"); ?>';
					if(order_data["orderCollateralCount"]==1)
					{
						html += '<br><?php echo t("Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet."); ?></td>';
					}
					else
					{
						html += '<br><?php echo t("Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet."); ?></td>';
					}
					html += '<td>€ ' + order_data["orderItemsTotalCollateralGross"] + '</td>';
					html += '</tr>';
				}
				html += '<tr>';
				if(order_data["shipping_type_id"]=="0")
				{
					html += '<td colspan="3">' + order_data["shipping_details"] + '</td>';
				}
				else
				{
					html += '<td colspan="3">' + order_data["shipping_title"] + '</td>';
				}
				html += '<td>€ ' + order_data["shippingCostsGross"] + '</td>';
				html += '</tr>';
				html += '<tr>';
				html += '<td colspan="3"><?php echo t("Im Gesamtpreis sind"); ?> <?php echo UST; ?>% <?php echo t("gesetzliche Umsatzsteuer enthalten"); ?></td>';
				html += '<td>€ ' + order_data["orderTotalTax"] + '</td>';
				html += '</tr>';
			}
			html += '<tr>';
			html += '<td colspan="3"><b><?php echo t("Gesamtpreis"); ?></b></td>';
			html += '<td><b>€ ' + order_data["orderTotalGross"] + '</b></td>';
			html += '</tr>';
			html += '</table>';
			$("#orders").html(html);

			//Status_Box Anzeige
			html = '<div id="message_box_success" class="success" style="font-size: 16px; font-weight: bold; display: none; width: 732px"></div>';
			html += '<div id="message_box_warning" class="warning" style="font-size: 16px; font-weight: bold; display: none; width: 732px"></div>';
			html += '<div id="message_box_failure" class="failure" style="font-size: 16px; font-weight: bold; display: none; width: 732px"></div>';
			$("#mid_right_column_header").html(html);
			//alert(car_data_input + " " + kritdo4);
			if(status_id == 1 || status_id == 7)
			{
				if(order_data["orderComplete"]=="1")
				{
					if(car_data_input==1)
					{
						html2 = '<p style="margin:20px"><?php echo t("Bitte geben Sie noch fehlende Fahrzeugdaten in der Artikelliste ein."); ?>';
						$("#message_box_warning").html(html2);
						document.getElementById("message_box_warning").style.display = "";
					}
					else
					{
						if(kritdo4==1)
						{
							//alert("Achtung! Seiten-reload!");
							//location.reload(true);
							ignore_cars.length=ignore_cars.length-ignore_cars.length;
							order_item_list(ignore_cars);
							html2 = '<p style="margin:20px"><?php echo t("Bitte geben Sie noch fehlende Fahrzeugdaten in der Artikelliste ein."); ?>';
							$("#message_box_warning").html(html2);
							document.getElementById("message_box_warning").style.display = "";
							//stop();
						}
						else
						{
							html2 = '<p style="margin:20px"><?php echo t("Vielen Dank für die Eingabe der Fahrzeugdaten. Die Bestellung wird in Kürze versendet."); ?>';
							$("#message_box_success").html(html2);
							document.getElementById("message_box_success").style.display = "";
						}
					}
				}
				else if(order_data["orderComplete"]=="0")
				{
					html2 = '<p style="margin:20px"><?php echo t("Bitte bei allen Artikeln mit dem Warnsymbol"); ?> <img src="<?php echo PATH; ?>/images/icons/24x24/warning.png" alt="" style="vertical-align: -4px"> <?php echo t("Fahrzeuge zuordnen."); ?>';
					$("#message_box_failure").html(html2);
					document.getElementById("message_box_failure").style.display = "";
				}
			}
			if(status_id == 4)
			{
				html2 = '<p style="margin:20px"><?php echo t("Der Bestellvorgang wurde abgerochen. Es ist keine Bearbeitung der Fahrzeugdaten nötig."); ?>';
				$("#message_box_success").html(html2);
				document.getElementById("message_box_success").style.display = "";
			}
			if(status_id == 2)
			{
				html2 = '<p style="margin:20px"><?php echo t("Die Bestellung wurde an das Lager übergeben. Sie wird in Kürze versendet. Es ist keine Bearbeitung der Fahrzeugdaten nötig."); ?>';
				$("#message_box_success").html(html2);
				document.getElementById("message_box_success").style.display = "";
			}
			if(status_id == 3 || status_id == 6)
			{
				if(order_data["shipping_number"] != "")
				{
					if(order_data["shipping_type_id"] == 0 || order_data["shipping_type_id"] == 3 || order_data["shipping_type_id"] == 6)
					{
						html2 = '<p style="margin:20px"><?php echo t("Ihre Bestellung wurde mit DPD versendet. Zur Sendungsverfolgung gelangen Sie über folgenden link:"); ?> <a href="https://tracking.dpd.de/cgi-bin/delistrack?pknr='+order_data["shipping_number"]+'" target="_blank">'+order_data["shipping_number"]+'</a>';
						$("#message_box_success").html(html2);
						document.getElementById("message_box_success").style.display = "";
					}
					else if(order_data["shipping_type_id"] == 1 || order_data["shipping_type_id"] == 2 || order_data["shipping_type_id"] == 5 || order_data["shipping_type_id"] == 7)
					{
						html2 = '<p style="margin:20px"><?php echo t("Ihre Bestellung wurde mit DHL versendet. Zur Sendungsverfolgung gelangen Sie über folgenden link:"); ?> <a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='+order_data["shipping_number"]+'&rfn=&extendedSearch=true" target="_blank">'+order_data["shipping_number"]+'</a>';
						$("#message_box_success").html(html2);
						document.getElementById("message_box_success").style.display = "";
					}
				}
				else
				{
					html2 = '<p style="margin:20px"><?php echo t("Ihre Bestellung wurde versandt."); ?>';
					$("#message_box_success").html(html2);
					document.getElementById("message_box_success").style.display = "";
				}
			}
		}
	
		for( var m in i0649){
		  (function(k) {
			if($("#s0649" + order_data["item"][k]["OrderItemID"]).length==1 && typeof i0649[k]!=='undefined')
			{
				$("#s0649" +  + order_data["item"][k]["OrderItemID"]).autocomplete({
					source: order_data["item"][k]["criteria"][i0649[k]]["TabItems"]
				});
			}
		  })(m);
		}

		for( var n in i2){
		  (function(k) {
			$("#" + order_data["item"][k]["OrderItemID"] + "databutton").click(function() {
			  update_car_data(order_data, customer_vehicle_id2[k], i2[k]);
			});
		  })(i2[n]);
		}
		
		if(order_data["payments_type_id"]!=4)
		{
			$("#address_button").click(function() {
			  bill_address_dialog(order_data, "button");
			});
		}
	}
	
	function show_hide(id, idimg)
	{
		$("#"+id).toggle("fade", 500);
		if($("#"+idimg).attr("src")=="<?php echo PATH; ?>/images/icons/16x16/down.png")
		{
			$("#"+idimg).attr("src", "<?php echo PATH; ?>/images/icons/16x16/right.png");
		}
		else
		{
			$("#"+idimg).attr("src", "<?php echo PATH; ?>/images/icons/16x16/down.png");
		}
	}

	function order_item_list(ignore_cars)
	{
		var order_data = new Array();
		wait_dialog_show();
		//$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_order_detail", OrderID: <?php echo $_GET["id_order"]; ?>},
		$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "OrderDetailGet", OrderID: <?php echo $_GET["id_order"]; ?>},
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
						order_data["status_id"] = 						$xml.find("status_id").text();
						order_data["orderDate"] = 						$xml.find("orderDate").text();
						
						order_data["customer_id"] = 					$xml.find("customer_id").text();
						if(order_data["customer_id"]!="<?php echo $_SESSION["id_user"];?>")
						{
							alert("<?php echo t("Sie haben keine Zugriffsberechtigung für diese Seite!");?>");
							location.href = '<?php echo PATH.$_GET["lang"]; ?>/online-shop/mein-konto/bestellungen/';
						}
						order_data["usermail"] = 						$xml.find("usermail").text();
						order_data["userphone"] = 						$xml.find("userphone").text();
						order_data["shop_id"] = 						$xml.find("shop_id").text();
						
						//Rechnungsanschrift						
						order_data["bill_adr_company"] = 				$xml.find("bill_adr_company").text();
						order_data["bill_adr_firstname"] =				$xml.find("bill_adr_firstname").text();
						order_data["bill_adr_lastname"] = 				$xml.find("bill_adr_lastname").text();
						order_data["bill_adr_street"] = 				$xml.find("bill_adr_street").text();
						order_data["bill_adr_number"] = 				$xml.find("bill_adr_number").text();
						order_data["bill_adr_zip"] = 					$xml.find("bill_adr_zip").text();
						order_data["bill_adr_city"] = 					$xml.find("bill_adr_city").text();
						order_data["bill_adr_additional"] = 			$xml.find("bill_adr_additional").text();
						order_data["bill_adr_country"] = 				$xml.find("bill_adr_country").text();
						order_data["bill_adr_country_id"] = 			$xml.find("bill_adr_country_id").text();
						
						//Lieferanschrift
						order_data["ship_adr_company"] = 				$xml.find("ship_adr_company").text();
						order_data["ship_adr_firstname"] =				$xml.find("ship_adr_firstname").text();
						order_data["ship_adr_lastname"] = 				$xml.find("ship_adr_lastname").text();
						order_data["ship_adr_street"] = 				$xml.find("ship_adr_street").text();
						order_data["ship_adr_number"] = 				$xml.find("ship_adr_number").text();
						order_data["ship_adr_zip"] = 					$xml.find("ship_adr_zip").text();
						order_data["ship_adr_city"] = 					$xml.find("ship_adr_city").text();
						order_data["ship_adr_additional"] = 			$xml.find("ship_adr_additional").text();
						order_data["ship_adr_country"] = 				$xml.find("ship_adr_country").text();
						order_data["ship_adr_country_id"] = 			$xml.find("ship_adr_country_id").text();
						
						order_data["bill_address_manual_update"] = 		$xml.find("bill_address_manual_update").text();
						order_data["bill_adr_id"] = 					$xml.find("bill_adr_id").text();
						order_data["ship_adr_id"] = 					$xml.find("ship_adr_id").text();
						order_data["id_order"] = 						$xml.find("id_order").text();
						order_data["orderPositions"] = 					$xml.find("orderPositions").text();
		
						//Summen
						order_data["Order_type"] = 						$xml.find("Order").attr("type");
						order_data["orderItemsTotalCollateralNet"] = 	$xml.find("orderItemsTotalCollateralNet").text();
						order_data["orderCollateralCount"] =			$xml.find("orderCollateralCount").text();
						order_data["shipping_type_id"] = 				$xml.find("shipping_type_id").text();
						order_data["shipping_details"] = 				$xml.find("shipping_details").text();
						order_data["shipping_title"] = 					$xml.find("shipping_title").text();
						order_data["shippingCostsNet"] = 				$xml.find("shippingCostsNet").text();
						order_data["payments_type_id"] = 				$xml.find("payments_type_id").text();
						order_data["orderTotalNet"] = 					$xml.find("orderTotalNet").text();
						order_data["orderTotalTax"] = 					$xml.find("orderTotalTax").text();
						order_data["orderItemsTotalCollateralGross"] = 	$xml.find("orderItemsTotalCollateralGross").text();
						order_data["shippingCostsGross"] = 				$xml.find("shippingCostsGross").text();
						order_data["orderTotalGross"] = 				$xml.find("orderTotalGross").text();

						//Status_Box Anzeige
						order_data["orderComplete"] = 					$xml.find("orderComplete").text();
						order_data["shipping_number"] = 				$xml.find("shipping_number").text();
	
						//Items
						//var item_count = 0;
						var item_count2 = 0;
						var service_ready = new Array();
						for (i=0; i<$xml.find("orderPositions").text(); i++)
						{
							service_ready[i]=0;
						}
						
						var car_data_input = 0;
						var order_id = $xml.find("id_order").text();
						order_data["item"] = new Array();
						$xml.find("Item").each(
							function()
							{
								order_data["item"][item_count2] = new Array();
								order_data["item"][item_count2]["OrderItemDesc"] = 					$(this).find("OrderItemDesc").text();
								order_data["item"][item_count2]["orderItemTotalCollateralNet"] =	$(this).find("orderItemTotalCollateralNet").text();
								order_data["item"][item_count2]["OrderItemAmount"] = 				$(this).find("OrderItemAmount").text();
								order_data["item"][item_count2]["orderItemPriceNet"] = 				$(this).find("orderItemPriceNet").text();
								order_data["item"][item_count2]["orderItemTotalNet"] = 				$(this).find("orderItemTotalNet").text();
								order_data["item"][item_count2]["orderItemTotalCollateralGross"] = 	$(this).find("orderItemTotalCollateralGross").text();
								order_data["item"][item_count2]["orderItemPriceGross"] = 			$(this).find("orderItemPriceGross").text();
								order_data["item"][item_count2]["orderItemTotalGross"] = 			$(this).find("orderItemTotalGross").text();
								order_data["item"][item_count2]["OrderItemCustomerVehicleID"] = 	$(this).find("OrderItemCustomerVehicleID").text();
								order_data["item"][item_count2]["OrderItemVehicleBrand"] = 			$(this).find("OrderItemVehicleBrand").text();
								order_data["item"][item_count2]["OrderItemID"] = 					$(this).find("OrderItemID").text();
								order_data["item"][item_count2]["OrderItemItemID"] =				$(this).find("OrderItemItemID").text();
								order_data["item"][item_count2]["OrderItemMPN"] = 					$(this).find("OrderItemMPN").text();
								order_data["item"][item_count2]["OrderItemVehicleModel"] = 			$(this).find("OrderItemVehicleModel").text();
								order_data["item"][item_count2]["OrderItemVehicleType"] = 			$(this).find("OrderItemVehicleType").text();
								order_data["item"][item_count2]["OrderItemVehicleKTypNr"] = 		$(this).find("OrderItemVehicleKTypNr").text();
								order_data["item"][item_count2]["OrderItemVehicleID"] = 			$(this).find("OrderItemVehicleID").text();
								order_data["item"][item_count2]["OrderItemVehicleDateBuilt"] = 		$(this).find("OrderItemVehicleDateBuilt").text();
								order_data["item"][item_count2]["OrderItemVehicleFIN"] = 			$(this).find("OrderItemVehicleFIN").text();
								order_data["item"][item_count2]["OrderItemVehiclec0003"] = 			$(this).find("OrderItemVehiclec0003").text();
								order_data["item"][item_count2]["OrderItemVehiclec0004"] = 			$(this).find("OrderItemVehiclec0004").text();
								order_data["item"][item_count2]["OrderItemVehiclec0005"] = 			$(this).find("OrderItemVehiclec0005").text();
								order_data["item"][item_count2]["OrderItemVehiclec0006"] = 			$(this).find("OrderItemVehiclec0006").text();
								order_data["item"][item_count2]["OrderItemVehicles0033"] = 			$(this).find("OrderItemVehicles0033").text();
								order_data["item"][item_count2]["OrderItemVehicles0038"] = 			$(this).find("OrderItemVehicles0038").text();
								order_data["item"][item_count2]["OrderItemVehicles0040"] = 			$(this).find("OrderItemVehicles0040").text();
								order_data["item"][item_count2]["OrderItemVehicles0067"] = 			$(this).find("OrderItemVehicles0067").text();
								order_data["item"][item_count2]["OrderItemVehicles0072"] = 			$(this).find("OrderItemVehicles0072").text();
								order_data["item"][item_count2]["OrderItemVehicles0112"] = 			$(this).find("OrderItemVehicles0112").text();
								order_data["item"][item_count2]["OrderItemVehicles0139"] = 			$(this).find("OrderItemVehicles0139").text();
								order_data["item"][item_count2]["OrderItemVehicles0233"] = 			$(this).find("OrderItemVehicles0233").text();
								order_data["item"][item_count2]["OrderItemVehicles0514"] = 			$(this).find("OrderItemVehicles0514").text();
								order_data["item"][item_count2]["OrderItemVehicles0564"] = 			$(this).find("OrderItemVehicles0564").text();
								order_data["item"][item_count2]["OrderItemVehicles0567"] = 			$(this).find("OrderItemVehicles0567").text();
								order_data["item"][item_count2]["OrderItemVehicles0608"] = 			$(this).find("OrderItemVehicles0608").text();
								order_data["item"][item_count2]["OrderItemVehicles0649"] = 			$(this).find("OrderItemVehicles0649").text();
								order_data["item"][item_count2]["OrderItemVehicles1197"] = 			$(this).find("OrderItemVehicles1197").text();
								
								item_count2 = item_count2 + 1;
							}
						);
						for(i=0; i<order_data["orderPositions"]; i++)
						{
							criteria_get(order_data, i, service_ready);
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
	
	function criteria_get(order_data, i, service_ready)
	{
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "OrderItemCriteriaGet", MPN: order_data["item"][i]["OrderItemMPN"], KTypNr: Number(order_data["item"][i]["OrderItemVehicleKTypNr"]), item_lauf: i},
			function (data)
			{
				//wait_dialog_hide();
				//show_status2(data);
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						kritcnt=0;
						order_data["item"][i]["criteria"] = new Array();
						$xml.find("Krit").each(
							function()
							{
								var self = this;
								if($(self).find("KritNr").text()!="0008")
								{
									order_data["item"][i]["criteria"][kritcnt] = new Array();
									order_data["item"][i]["criteria"][kritcnt]["id"] = 0;
									order_data["item"][i]["criteria"][kritcnt]["KritNr"] = $(self).find("KritNr").text();
									order_data["item"][i]["criteria"][kritcnt]["KritWert"] = $(self).find("KritWert").text();
									order_data["item"][i]["criteria"][kritcnt]["KritBez"] = $(self).find("KritBez").text();
									order_data["item"][i]["criteria"][kritcnt]["KritWertBez"] = $(self).find("KritWertBez").text();
									order_data["item"][i]["criteria"][kritcnt]["KritShow"] = "show";
									order_data["item"][i]["criteria"][kritcnt]["TabNr"] = $(self).find("TabNr").text();
									if(order_data["item"][i]["criteria"][kritcnt]["TabNr"]!="000" || order_data["item"][i]["criteria"][kritcnt]["KritNr"]=="0649")
									{
										var cnt = 0;
										order_data["item"][i]["criteria"][kritcnt]["TabItems"] = new Array();
										$(self).find("TabItem").each(
											function()
											{
												if(order_data["item"][i]["criteria"][kritcnt]["KritNr"]=="0649")
												{
													order_data["item"][i]["criteria"][kritcnt]["TabItems"][cnt] = $(this).text();
												}
												else
												{
													order_data["item"][i]["criteria"][kritcnt]["TabItems"][$(this).attr("id")] = $(this).text();
												}
												cnt = cnt + 1;
												//alert($(self).find("cnt").text());
											}
										);	
									}
									kritcnt = kritcnt + 1;
								}
							}
						);
						service_ready[i] = 1;
						call_show_order_item_list(order_data, service_ready, ignore_cars, i);
						
						//show_order_data(order_data);
						//show_status2(print_r(order_data));
						//show_order_item_list(order_data, service_ready, ignore_cars);
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
//******************************************************************************************************	
	function show_order_data(order_data)
	{
		var str = "";
		for(m=0;m<order_data["item"].length;m++)
		{
			if(typeof order_data["item"][m]["criteria"] !== 'undefined')
			{
				for(r=0;r<order_data["item"][m]["criteria"].length;r++)
				{
					str += "item " + m + "Kriterium " + r + " " + order_data["item"][m]["criteria"][r]["KritNr"] + " " +order_data["item"][m]["criteria"][r]["TabNr"] + "\n";
					for(a in order_data["item"][m]["criteria"][r]["TabItems"])
					{
						str += "item " + m + "Kriterium " + r + " " + order_data["item"][m]["criteria"][r]["KritNr"] + " " +order_data["item"][m]["criteria"][r]["TabNr"] + " " + a + " " + order_data["item"][m]["criteria"][r]["TabItems"][a] + "\n";
					}
				}
			}
		}
		show_status2(str);
	}
//******************************************************************************************************	
	function call_show_order_item_list(order_data, service_ready, ignore_cars, i)
	{
		var check_service = 1;
		for (i=0; i<service_ready.length; i++)
		{
			if(service_ready[i]==0)
			{
				check_service = 0;
			}
		}
		//alert(check_service);
		if(check_service==1)
		{
			var kritcnt=0;
			for(j=0; j<order_data["item"].length; j++)
			{
				for(k=0; k<order_data["item"][j]["criteria"].length; k++)
				{
					kritcnt = kritcnt + 1;
				}
			}
			var service_ready2 = new Array();
			for(m=0; m<kritcnt; m++)
			{
				service_ready2[m]=0;
			}
			kritcnt2=0;
			for(j=0; j<order_data["item"].length; j++)
			{
				for(k=0; k<order_data["item"][j]["criteria"].length; k++)
				{
					call_show_order_item_list2(order_data, service_ready2, ignore_cars, kritcnt, j, k, kritcnt2)
					kritcnt2 = kritcnt2 + 1;	
				}
			}
			//alert(service_ready2[0]);
			//service_ready[i] = 1;
			if(kritcnt==0)
			{
				show_order_item_list(order_data, service_ready2, ignore_cars, kritcnt);
			}
		}
	}
	
	function call_show_order_item_list2(order_data, service_ready2, ignore_cars, kritcnt, j, k, kritcnt2)
	{
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetCriteria", 
																		mode: "read_car",
																	    id_carfleet: order_data["item"][j]["OrderItemCustomerVehicleID"],
																		order_id: order_data["id_order"],
																		order_item_id: order_data["item"][j]["OrderItemID"],
																		KritNr: order_data["item"][j]["criteria"][k]["KritNr"],
																		KritWert: order_data["item"][j]["criteria"][k]["KritWert"]},
			function (data)
			{
				//alert(data);
				var $xml = $($.parseXML(data));
				var $ack = $xml.find("Ack");
				if ( $ack.text()=="Success" )
				{
					if($xml.find("KritNumber").text()=="1")
					{
						order_data["item"][j]["criteria"][k]["id"]=$xml.find("id").text();
						order_data["item"][j]["criteria"][k]["UserKritBez"]=$xml.find("UserKritBez").text();
						service_ready2[kritcnt2] = 1;
						show_order_item_list(order_data, service_ready2, ignore_cars, kritcnt);
					}
					else if($xml.find("KritNumber").text()=="0")
					{
						if(typeof krit_fix[order_data["item"][j]["criteria"][k]["KritNr"]] !== 'undefined')
						{
							$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_customer_vehicles", 
																						mode: "vehicle",
																						vehicle_customer_id: order_data["item"][j]["OrderItemCustomerVehicleID"]},
								function (data)
								{
									//wait_dialog_hide();
									//alert(data);
									try
									{
										var $xml2 = $($.parseXML(data));
										var $ack = $xml2.find("Ack");
										if ( $ack.text()=="Success" )
										{
											if($xml2.find("vehicle" + krit_fix[order_data["item"][j]["criteria"][k]["KritNr"]]["column"]).text()=="")
											{
												if(order_data["item"][j]["criteria"][k]["KritNr"]=="0020" || 
												   order_data["item"][j]["criteria"][k]["KritNr"]=="0021" ||
												   order_data["item"][j]["criteria"][k]["KritNr"]=="0025" ||
												   order_data["item"][j]["criteria"][k]["KritNr"]=="0026")
												{
													order_data["item"][j]["criteria"][k]["KritShow"]="hide";
												}
												else
												{
													order_data["item"][j]["criteria"][k]["UserKritBez"]="";
												}
											}
											else
											{
												order_data["item"][j]["criteria"][k]["KritShow"]="hide";
											}
											service_ready2[kritcnt2] = 1;
											show_order_item_list(order_data, service_ready2, ignore_cars, kritcnt);
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
						else
						{
							order_data["item"][j]["criteria"][k]["UserKritBez"]="";
							service_ready2[kritcnt2] = 1;
							show_order_item_list(order_data, service_ready2, ignore_cars, kritcnt);
						}				
					}
				}
			}
		);
	}
	
	function update_car_data(order_data, customer_vehicle_id, i)
	{
		//alert(customer_vehicle_id + " " + i);
		ignore_cars[customer_vehicle_id] = 1;
		var sql = "";

		//Übergabeparameter
		var id = 			new Array; //id aus shop_carfleet_criteria Tabelle
		var id_carfleet =   new Array;
		var order_id = 	    new Array;
		var order_item_id = new Array;
		var item_id = 	    new Array;
		var KritNr = 	    new Array;
		var KritWert = 	    new Array;
		var KritBez = 	    new Array;
		var KritWertBez =   new Array;
		var UserKritBez =   new Array;	
		//alert($("#s0649").val());
		//Wie viele Eingabefelder > 0 und Belegung der Übergabeparameter
		var cnt_input = 0;
		for(j=0; j<order_data["item"][i]["criteria"].length; j++)
		{
			if(typeof krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]] !== 'undefined') //fixe Kriterien
			{
				if($("#" + krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["column"] + order_data["item"][i]["OrderItemID"]).length==1)
				{
					//if($("#" + krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["column"]).val().trim().length>0 || $("#" + krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["column"] + " :selected").text().trim().length>0)
					if($("#" + krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["column"] + order_data["item"][i]["OrderItemID"]).val().trim().length>0)
					{
						if(sql.length>0)
						{
							sql += ", ";
						}
						sql += krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["column"] + "='" + $("#" + krit_fix[order_data["item"][i]["criteria"][j]["KritNr"]]["column"] + order_data["item"][i]["OrderItemID"]).val().trim() + "'";
					}
				}
			}
			else //variable Kriterien
			{
				if($("#" + order_data["item"][i]["criteria"][j]["KritNr"] + order_data["item"][i]["criteria"][j]["KritWert"].replace( /\s/g, "" ) + order_data["item"][i]["OrderItemID"]).length==1)
				{
					id[cnt_input]            = order_data["item"][i]["criteria"][j]["id"];
					id_carfleet[cnt_input]   = customer_vehicle_id;
					order_id[cnt_input]	     = order_data["id_order"];
					order_item_id[cnt_input] = order_data["item"][i]["OrderItemID"]
					item_id[cnt_input] 	     = order_data["item"][i]["OrderItemItemID"];
					KritNr[cnt_input]        = order_data["item"][i]["criteria"][j]["KritNr"];
					KritWert[cnt_input]      = order_data["item"][i]["criteria"][j]["KritWert"];
					KritBez[cnt_input]       = order_data["item"][i]["criteria"][j]["KritBez"];
					KritWertBez[cnt_input]   = order_data["item"][i]["criteria"][j]["KritWertBez"];
					UserKritBez[cnt_input]   = $("#" + order_data["item"][i]["criteria"][j]["KritNr"] + order_data["item"][i]["criteria"][j]["KritWert"].replace( /\s/g, "" ) + order_data["item"][i]["OrderItemID"]).val().trim();
					
					cnt_input = cnt_input +1;
				}
				
			}
		}
		//show_status2( print_r( UserKritBez ) );
		//alert(sql);
		if($("#month2" + order_data["item"][i]["OrderItemID"]).length == 1 || $("#fin2" + order_data["item"][i]["OrderItemID"]).length == 1 || cnt_input > 0 || sql.length>0)
		{
			if($("#fin2" + order_data["item"][i]["OrderItemID"]).length == 1)
			{
				var fin=$("#fin2" + order_data["item"][i]["OrderItemID"]).val();
				fin = fin.trim();
				if(fin.length>0 && fin.length<17)
				{
					show_message_dialog("<?php echo t("Die Fahrgestellnummer muss 17 Zeichen lang sein. Ansonsten bitte das Feld leer lassen."); ?>");
					return;
				}
				if(fin.length>0)
				{
					if(sql.length>0)
					{
						sql += ", ";
					}
					sql += "FIN='" + fin + "'";
				}
			}
			if($("#month2" + order_data["item"][i]["OrderItemID"]).length == 1)
			{
				var month=$("#month2" + order_data["item"][i]["OrderItemID"]).val();
				var year=$("#year2" + order_data["item"][i]["OrderItemID"]).val();
				
				if(month != "" || year != "")
				{
					if(month<1 || month>12)
					{
						show_message_dialog("<?php echo t("Den Monat bitte zwischen 01 und 12 angeben, oder die Felder für die Erstzulassung leer lassen."); ?>");
						return;
					}
					if(year<1900 || year>2100)
					{
						show_message_dialog("<?php echo t("Das Jahr bitte folgendermaßen angeben: jjjj also z.B. 2013, oder die Felder für die Erstzulassung leer lassen."); ?>");
						return;
					}
				}
				var date_built = build_date_from_mm_yyyy(month,year);
				
				if(month == "" && year == "")
				{
					date_built = 0;
				}
				if(date_built!=0)
				{
					if(sql.length>0)
					{
						sql += ", ";
					}
					sql += "date_built=" + date_built;
				}
			}
			//alert("sql="+sql+"\ncnt_input="+cnt_input);
			if(sql.length>0 && cnt_input==0)
			{
				//alert("sql");
				wait_dialog_show();
				$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate", customer_vehicle_id: customer_vehicle_id, sql: sql, mode: "car_update_flex"},
					function (data)
					{
						//alert(data);
						wait_dialog_hide();
						order_item_list(ignore_cars);
					}
				);
			}
			else if(sql.length==0 && cnt_input>0)
			{
				//alert("cnt_input");
				wait_dialog_show();
				$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetCriteria",
												   'id[]':				id,
												   'id_carfleet[]':		id_carfleet,
												   'order_id[]': 		order_id,
												   'order_item_id[]': 	order_item_id,
												   'item_id[]': 		item_id,
												   'KritNr[]': 			KritNr,
												   'KritWert[]': 		KritWert,
												   'KritBez[]':			KritBez,
												   'KritWertBez[]': 	KritWertBez,
												   'UserKritBez[]': 	UserKritBez,
												   mode: 				"new"},
					function (data)
					{
						//alert(data);
						wait_dialog_hide();
						order_item_list(ignore_cars);
					}
				);
			}
			else if(sql.length>0 && cnt_input>0)
			{
				//alert("both");
				wait_dialog_show();
				$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetUpdate", customer_vehicle_id: customer_vehicle_id, sql: sql, mode: "car_update_flex"},
					function (data)
					{
						//alert(data);
						wait_dialog_hide();
						$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CarfleetCriteria",
												   'id[]':				id,
												   'id_carfleet[]': 	id_carfleet,
												   'order_id[]': 		order_id,
												   'order_item_id[]': 	order_item_id,
												   'item_id[]': 		item_id,
												   'KritNr[]': 			KritNr,
												   'KritWert[]': 		KritWert,
												   'KritBez[]': 		KritBez,
												   'KritWertBez[]': 	KritWertBez,
												   'UserKritBez[]': 	UserKritBez,
												   mode: 				"new"},
							function (data)
							{
								//alert(data);
								wait_dialog_hide();
								order_item_list(ignore_cars);
							}
						);
						//order_item_list(ignore_cars);
					}
				);
			}
			else
			{
				order_item_list(ignore_cars);
			}
		}
		else
		{
			order_item_list(ignore_cars);
		}
	}
	
	function car_input_show(customer_vehicle_id, id, order_id)
	{
		html = '';
		html += '';
		html += '<h1><?php echo t("Meine Fahrzeuge"); ?>:</h1>';
		html += '<table class="hover" style="font-size:12px; font-family: Arial">';									
		html += '<tr>';
		html += '<th><?php echo t("Fahrzeug auswählen"); ?></th>';
		html += '<th><?php echo t("Fahrzeugschein (zu_2 / zu_3)"); ?></th>';
		html += '<th><?php echo t("Baujahr"); ?></th>';
		html += '<th><?php echo t("Fahrgestellnummer"); ?></th>';
		html += '<th></th>';
		html += '<th style="width:20px"><?php echo t("Status"); ?></th>';
		html += '<th style="width:20px"><?php echo t("Löschen"); ?></th>';
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
						html += '<td colspan="7" style="background: #98Fb98"><a href="javascript: car_data_new(' + customer_vehicle_id + ',' + id + ',' + order_id + ');"><?php echo t("Neues Fahrzeug anlegen"); ?></a></td>';
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
									html += '<td><a href="javascript:car_data_change(' + $(this).find("vehicleCustomerID").text() + ',' + id + ',' + order_id + ',' + customer_vehicle_id + ');"><?php echo t("Fahrzeugdaten ändern"); ?></a></td>';
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
						//html += '<input type="checkbox" id="allcheck"  value="" style="display: none"><p style="font-size:12px; font-family: Arial; display: none"> <?php echo t("Änderung für alle Artikel der Bestellung übernehmen"); ?>';
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
		delete_dialog("<?php echo t("Wollen Sie dieses Fahrzeug wirklich löschen?"); ?>");
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
					{ text: "<?php echo t("Ok"); ?>", click: function() {close(); $(this).dialog("close");} },
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Achtung!"); ?>",
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
		var allselect = "selected";
		var customer_vehicle_id = $("#customer_vehicle_id").val();
		//$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_order_detail", OrderID: order_id},
		$.post("<?php echo PATH; ?>soa2/", {API: "shop", APIRequest: "OrderDetailGet", OrderID: order_id},
			function (data)
			{
				//show_status2(data);
				//wait_dialog_hide();
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						if($xml.find("orderPositions").text()>1)
						{
							allselect_set("<?php echo t("Soll das ausgewählte Fahrzeug für alle Artikel der Bestellung übernommen werden?"); ?>", allselect, id, order_id, customer_vehicle_id);
						}
						else if ($xml.find("orderPositions").text()==1)
						{
							allselect="selected";
							save_data(allselect, id, order_id, customer_vehicle_id);							
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
	
	function allselect_set(message, allselect, id, order_id, customer_vehicle_id)
	{
		$("#message").html(message);
		$("#message").dialog
		({	
			buttons:
			[
			  { text: "<?php echo t("Ja"); ?>", click: function() {allselect="all";save_data(allselect, id, order_id, customer_vehicle_id);$(this).dialog("close");} },
				{ text: "<?php echo t("Nein"); ?>", click: function() {allselect="selected";save_data(allselect, id, order_id, customer_vehicle_id);$(this).dialog("close");} }
			],
			open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
			closeOnEscape: false,
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Achtung!"); ?>",
			width:300
		});
	}
						
	function save_data(allselect, id, order_id, customer_vehicle_id)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_set_orderItem_vehicle", mode: allselect, customerVehicleID: customer_vehicle_id, OrderID: order_id, OrderItemID: id},
			function (data)
			{
				wait_dialog_hide();
				try
				{
					var $xml = $($.parseXML(data));
					var $ack = $xml.find("Ack");
					if ( $ack.text()=="Success" )
					{
						$("#usercars").dialog("close");
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

	function show_car_dialog(id, order_id)
	{
		$("#usercars").dialog
		//$("#carfleet").dialog
		({	buttons:
			[
				//{ text: "Auswahl bestätigen", click: function() {car_status_save(id, order_id); $(this).dialog("close");} },
				{ text: "<?php echo t("Abbrechen"); ?>", click: function() {order_item_list(ignore_cars);$(this).dialog("close");} }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Fahrzeugauswahl"); ?>",
			width:800,
			maxHeight:500,
			overflow:scroll,
			position:["center",150]
		});
	}
	
	function show_criteria_fields(krit_data2, customer_vehicle_id, id, order_id, customer_vehicle_id_old)
	{
		//get_criteria_array();
		var html = '';
		html += '<table style="border-style: solid; border-width: 1px; margin-top: 10px">';
			html += '<tr>';
				html += '<td align="left" style="width: 160px">' + krit_fix["0030"]["name"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="c0003" size="20" maxlength="20"></td>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0033"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="0033" size="20" maxlength="20"></td>';
			html += '<tr>';
			html += '<tr>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0514"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px">';
					html += '<select id="0514" style="width: 175px">';
					html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
					for(var value in krit_data2["0514"]["TabItems"])
					{
						html += '<option value="' + value + '">' + krit_data2["0514"]["TabItems"][value] + '</option>';
					}
					html += '</select>';
				html +='</td>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0233"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px">';
					html += '<select id="0233" style="width: 175px">';
					html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
					for(var value in krit_data2["0233"]["TabItems"])
					{
						html += '<option value="' + value + '">' + krit_data2["0233"]["TabItems"][value] + '</option>';
					}
					html += '</select>';
				html +='</td>';
			html += '<tr>';
			html += '<tr>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0608"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px">';
					html += '<select id="0608" style="width: 175px">';
					html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
					for(var value in krit_data2["0608"]["TabItems"])
					{
						html += '<option value="' + value + '">' + krit_data2["0608"]["TabItems"][value] + '</option>';
					}
					html += '</select>';
				html +='</td>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0649"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="0649" size="20" maxlength="20"></td>';
				/*html += '<td align="left" style="width: 200px">';
					html += '<select id="0649" style="width: 175px">';
					html += '<option value=""></option>';
					for(var value in krit_data2["0649"]["TabItems"])
					{
						html += '<option value="' + value + '">' + krit_data2["0649"]["TabItems"][value] + '</option>';
					}
					html += '</select>';
				html +='</td>';*/
			html += '<tr>';
			html += '<tr>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0040"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px">';
					html += '<select id="0040" style="width: 175px">';
					html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
					for(var value in krit_data2["0040"]["TabItems"])
					{
						//if(!krit_data2["0040"]["TabItems"][value].match("<?php echo t("für"); ?>"))
						//{
							html += '<option value="' + value + '">' + krit_data2["0040"]["TabItems"][value] + '</option>';
						//}
					}
					html += '</select>';
				html +='</td>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0038"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="0038" size="20" maxlength="20"></td>';
			html += '<tr>';
			html += '<tr>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0567"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px">';
					html += '<select id="0567" style="width: 175px">';
					html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
					for(var value in krit_data2["0567"]["TabItems"])
					{
						html += '<option value="' + value + '">' + krit_data2["0567"]["TabItems"][value] + '</option>';
					}
					html += '</select>';
				html +='</td>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0112"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px">';
					html += '<select id="0112" style="width: 175px">';
					html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
					for(var value in krit_data2["0112"]["TabItems"])
					{
						html += '<option value="' + value + '">' + krit_data2["0112"]["TabItems"][value] + '</option>';
					}
					html += '</select>';
				html +='</td>';
			html += '<tr>';
			html += '<tr>';
				html += '<td align="left" style="width: 160px">' + krit_fix["1197"]["name"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="1197" size="20" maxlength="20"></td>';
				html += '<td align="left" style="width: 160px">' + krit_fix["0045"]["name"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="c0004" size="20" maxlength="20"></td>';
			html += '<tr>';
			html += '<tr>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0067"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="0067" size="20" maxlength="20"></td>';
				html += '<td align="left" style="width: 160px">' + krit_fix["0265"]["name"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="c0006" size="20" maxlength="20"></td>';
			html += '<tr>';
			html += '<tr>';
				html += '<td align="left" style="width: 160px">' + krit_fix["0075"]["name"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="c0005" size="20" maxlength="20"></td>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0072"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px"><input type="text" id="0072" size="20" maxlength="20"></td>';
			html += '<tr>';
			html += '<tr>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0564"]["KritBez"] + ':</td>';
				html += '<td align="left" style="width: 200px">';
					html += '<select id="0564" style="width: 175px">';
					html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
					for(var value in krit_data2["0564"]["TabItems"])
					{
						html += '<option value="' + value + '">' + krit_data2["0564"]["TabItems"][value] + '</option>';
					}
					html += '</select>';
				html +='</td>';
				html += '<td align="left" style="width: 160px">' + krit_data2["0139"]["KritBez"] + '.:</td>';
				html += '<td align="left" style="width: 200px">';
					html += '<select id="0139" style="width: 175px">';
					html += '<option value="" selected><?php echo t("bitte auswählen"); ?></option>';
					for(var value in krit_data2["0139"]["TabItems"])
					{
						html += '<option value="' + value + '">' + krit_data2["0139"]["TabItems"][value] + '</option>';
					}
					html += '</select>';
					//html += '<button id="toggle">Show underlying select</button>';
				html +='</td>';
			html += '<tr>';
		html += '</table>';
		$("#criteria").html(html);
		
		list_sort("0040", 1);
		list_sort("0112", 1);
		list_sort("0139", 1);
		list_sort("0233", 1);
		list_sort("0514", 1);
		list_sort("0564", 1);
		list_sort("0567", 1);
		list_sort("0608", 1);
		
		//alert(customer_vehicle_id);
		if(typeof customer_vehicle_id !== 'undefined')
		{
			car_data_change2(customer_vehicle_id, id, order_id, customer_vehicle_id_old);
		}
		
		/*$(function() {
			$( "#0649" ).combobox();
		  });*/
		$( "#0649" ).autocomplete({
      		source: krit_data2["0649"]["TabItems"]
    	});
	}
	
	var krit_fix = new Array();
	krit_fix["0020"] 		   = new Array();
	krit_fix["0020"]["column"] = "date_built";
	krit_fix["0020"]["name"]   = "<?php echo t("Erstzulassung (mm/jjjj)"); ?>";
	krit_fix["0021"]		   = new Array();
	krit_fix["0021"]["column"] = "date_built";
	krit_fix["0021"]["name"]   = "<?php echo t("Erstzulassung (mm/jjjj)"); ?>";
	krit_fix["0025"]		   = new Array();
	krit_fix["0025"]["column"] = "FIN";
	krit_fix["0025"]["name"]   = "<?php echo t("Fahrgestellnummer"); ?>";
	krit_fix["0026"]		   = new Array();
	krit_fix["0026"]["column"] = "FIN";
	krit_fix["0026"]["name"]   = "<?php echo t("Fahrgestellnummer"); ?>";
	krit_fix["0030"]		   = new Array();
	krit_fix["0030"]["column"] = "c0003";
	krit_fix["0030"]["name"]   = "<?php echo t("Motornummer"); ?>";
	krit_fix["0031"]		   = new Array();
	krit_fix["0031"]["column"] = "c0003";
	krit_fix["0031"]["name"]   = "<?php echo t("Motornummer"); ?>";
	krit_fix["0033"]		   = new Array();
	krit_fix["0033"]["column"] = "s0033";
	krit_fix["0038"]		   = new Array();
	krit_fix["0038"]["column"] = "s0038";
	krit_fix["0040"]		   = new Array();
	krit_fix["0040"]["column"] = "s0040";
	krit_fix["0045"]		   = new Array();
	krit_fix["0045"]["column"] = "c0004";
	krit_fix["0045"]["name"]   = "<?php echo t("Organisationsnummer"); ?>";
	krit_fix["0046"]		   = new Array();
	krit_fix["0046"]["column"] = "c0004";
	krit_fix["0046"]["name"]   = "<?php echo t("Organisationsnummer"); ?>";
	krit_fix["0067"]		   = new Array();
	krit_fix["0067"]["column"] = "s0067";
	krit_fix["0072"]		   = new Array();
	krit_fix["0072"]["column"] = "s0072";
	krit_fix["0075"]		   = new Array();
	krit_fix["0075"]["column"] = "c0005";
	krit_fix["0075"]["name"]   = "<?php echo t("Felgengröße[Zoll]"); ?>";
	krit_fix["0112"]		   = new Array();
	krit_fix["0112"]["column"] = "s0112";
	krit_fix["0139"]		   = new Array();
	krit_fix["0139"]["column"] = "s0139";
	krit_fix["0233"]		   = new Array();
	krit_fix["0233"]["column"] = "s0233";
	krit_fix["0265"]		   = new Array();
	krit_fix["0265"]["column"] = "c0006";
	krit_fix["0265"]["name"]   = "<?php echo t("Nutzlast[kg]"); ?>";
	krit_fix["0514"]		   = new Array();
	krit_fix["0514"]["column"] = "s0514";
	krit_fix["0564"]		   = new Array();
	krit_fix["0564"]["column"] = "s0564";
	krit_fix["0567"]		   = new Array();
	krit_fix["0567"]["column"] = "s0567";
	krit_fix["0608"]		   = new Array();
	krit_fix["0608"]["column"] = "s0608";
	krit_fix["0649"]		   = new Array();
	krit_fix["0649"]["column"] = "s0649";
	krit_fix["0864"]		   = new Array();
	krit_fix["0864"]["column"] = "c0005";
	krit_fix["0864"]["name"]   = "<?php echo t("Felgengröße[Zoll]"); ?>";
	krit_fix["0869"]		   = new Array();
	krit_fix["0869"]["column"] = "c0006";
	krit_fix["0869"]["name"]   = "<?php echo t("Nutzlast[kg]"); ?>";
	krit_fix["1159"]		   = new Array();
	krit_fix["1159"]["column"] = "c0005";
	krit_fix["1159"]["name"]   = "<?php echo t("Felgengröße[Zoll]"); ?>";
	krit_fix["1197"]		   = new Array();
	krit_fix["1197"]["column"] = "s1197";
	krit_fix["1197"]["name"]   = "<?php echo t("PR-Nummer"); ?>";
	krit_fix["1303"]		   = new Array();
	krit_fix["1303"]["column"] = "c0006";
	krit_fix["1303"]["name"]   = "<?php echo t("Nutzlast[kg]"); ?>";
	krit_fix["1304"]		   = new Array();
	krit_fix["1304"]["column"] = "c0006";
	krit_fix["1304"]["name"]   = "<?php echo t("Nutzlast[kg]"); ?>";
	
	function get_criteria_array(customer_vehicle_id, id, order_id, customer_vehicle_id_old)
	{
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CriteriaGet"},
			function (data)
			{
				//show_status2(data);
				var $xml = $($.parseXML(data));
				var $ack = $xml.find("Ack");
				if ( $ack.text()=="Success" )
				{
					var krit_data2 = new Array();
					$xml.find("Krit").each(
						function()
						{
							var self = this;
							krit_data2[$(self).find("KritNr").text()] = new Array();
							krit_data2[$(self).find("KritNr").text()]["BezNr"]   = $(self).find("BezNr").text();
							krit_data2[$(self).find("KritNr").text()]["KritBez"] = $(self).find("KritBez").text();
							krit_data2[$(self).find("KritNr").text()]["Typ"]     = $(self).find("Typ").text();
							krit_data2[$(self).find("KritNr").text()]["TabNr"]   = $(self).find("TabNr").text();
							if($(self).find("TabNr").text()!="000" || $(self).find("KritNr").text()=="0649")
							{
								krit_data2[$(self).find("KritNr").text()]["TabItems"] = new Array();
								var cnt = 0;
								$(self).find("TabItem").each(
									function()
									{
										//alert($(this).attr("id"));
										if($(self).find("KritNr").text()=="0649")
										{
											krit_data2[$(self).find("KritNr").text()]["TabItems"][cnt] = $(this).text();
										}
										else
										{
											krit_data2[$(self).find("KritNr").text()]["TabItems"][$(this).attr("id")] = $(this).text();
										}
										cnt = cnt + 1;
									}
								);
							}
						}
					);
					show_criteria_fields(krit_data2, customer_vehicle_id, id, order_id, customer_vehicle_id_old);
				}
			}
		);
	}
	
	function car_data_new(customer_vehicle_id, id, order_id)
	{
		get_criteria_array();
		$("#month").val("");
		$("#year").val("");
		document.getElementById("kbabutton").style.display = "";
		document.getElementById("textfield").style.display = "";

		$("#hsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
		$("#tsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
		$("#month").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
		$("#year").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
		$("#fin").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
		$("#hsn").keyup(function(){ 
                   if($("#hsn").val()!="")
					{
						$("#hsn").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
					}
					else
					{
						$("#hsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
					}
        });
		$("#tsn").keyup(function(){ 
                   if($("#tsn").val()!="")
					{
						$("#tsn").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
					}
					else
					{
						$("#tsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
					}
        });
		$("#month").keyup(function(){ 
                   if($("#month").val()!="")
					{
						$("#month").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
					}
					else
					{
						$("#month").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
					}
        });
		$("#year").keyup(function(){ 
                   if($("#year").val()!="")
					{
						$("#year").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
					}
					else
					{
						$("#year").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
					}
        });
		$("#fin").keyup(function(){ 
                   if($("#fin").val()!="")
					{
						$("#fin").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
					}
					else
					{
						$("#fin").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
					}
        });
		show_select_search_by_car_manufacturer();
		car_data_change_dialog("car_new", customer_vehicle_id, id, order_id);
	}
	
	function car_data_change(customer_vehicle_id, id, order_id, customer_vehicle_id_old)
	{
		get_criteria_array(customer_vehicle_id, id, order_id, customer_vehicle_id_old);
	}
	
	function car_data_change2(customer_vehicle_id, id, order_id, customer_vehicle_id_old)
	{
		//get_criteria_array();
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
						kba = 		 $xml.find("vehicleKBA").text();
						datebuilt =  $xml.find("vehicleDateBuilt").text();
						fin = 		 $xml.find("vehicleFIN").text();
						var c0003 =	 $xml.find("vehiclec0003").text();
						var c0004 =	 $xml.find("vehiclec0004").text();
						var c0005 =	 $xml.find("vehiclec0005").text();
						var c0006 =	 $xml.find("vehiclec0006").text();
						var s0033 =  $xml.find("vehicles0033").text();
						var s0038 =  $xml.find("vehicles0038").text();
						var s0040 =  $xml.find("vehicles0040").text();
						var s0067 =  $xml.find("vehicles0067").text();
						var s0072 =  $xml.find("vehicles0072").text();
						var s0112 =  $xml.find("vehicles0112").text();
						var s0139 =  $xml.find("vehicles0139").text();
						var s0233 =  $xml.find("vehicles0233").text();
						var s0514 =  $xml.find("vehicles0514").text();
						var s0564 =  $xml.find("vehicles0564").text();
						var s0567 =  $xml.find("vehicles0567").text();
						var s0608 =  $xml.find("vehicles0608").text();
						var s0649 =  $xml.find("vehicles0649").text();
						var s1197 =  $xml.find("vehicles1197").text();
						var hsn = kba.substr(0,4);
						var tsn = kba.substr(4,3);
						$("#hsn").val(hsn);
						if(hsn!="")
						{
							$("#hsn").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
						}
						else
						{
							$("#hsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						}
						$("#hsn").keyup(function(){ 
						    if($("#hsn").val()!="")
							{
								$("#hsn").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#hsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
						$("#tsn").val(tsn);
						if(tsn!="")
						{
							$("#tsn").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
						}
						else
						{
							$("#tsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						}
						$("#tsn").keyup(function(){ 
							if($("#tsn").val()!="")
							{
								$("#tsn").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#tsn").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
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
						if(month!="")
						{
							$("#month").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
						}
						else
						{
							$("#month").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						}
						$("#month").keyup(function(){ 
							if($("#month").val()!="")
							{
								$("#month").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#month").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
						$("#year").val(year);
						if(year!="")
						{
							$("#year").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
						}
						else
						{
							$("#year").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						}
						$("#year").keyup(function(){ 
							if($("#year").val()!="")
							{
								$("#year").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#year").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
						
						$("#fin").val(fin);
						if(fin!="")
						{
							$("#fin").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
						}
						else
						{
							$("#fin").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
						}
						$("#fin").keyup(function(){ 
							if($("#fin").val()!="")
							{
								$("#fin").css({"background-color": "#99EB99", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});	
							}
							else
							{
								$("#fin").css({"background-color": "#FFB2B2", "border-style": "solid", "border-width": "1px", "border-color": "#B2B2B2"});
							}
						});
						$("#vehicle_id").val(vehicle_id);
						$("#c0003").val(c0003);
						$("#c0004").val(c0004);
						$("#c0005").val(c0005);
						$("#c0006").val(c0006);
						$("#0033").val(s0033);
						$("#0038").val(s0038);
						$("#0040").val(s0040);
						$("#0067").val(s0067);
						$("#0072").val(s0072);
						$("#0112").val(s0112);
						$("#0139").val(s0139);
						$("#0233").val(s0233);
						$("#0514").val(s0514);
						$("#0564").val(s0564);
						$("#0567").val(s0567);
						$("#0608").val(s0608);
						$("#0649").val(s0649);
						/*$("#0649").find("option").filter(function(index) {
							return s0649 === $(this).text();
						}).prop("selected", "selected");*/
						$("#1197").val(s1197);
						
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
					{ text: "<?php echo t("Änderungen übernehmen"); ?>", click: function() {safe_car_data("car_change", customer_vehicle_id, id, order_id, customer_vehicle_id_old);} },
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Fahrzeugdaten ändern / ergänzen"); ?>",
				width:800,
				position:["center",100]
			});		
		}
		else if(mode=="car_new")
		{
			$("#cars").dialog
			({	buttons:
				[
					{ text: "<?php echo t("Neues KFZ anlegen"); ?>", click: function() {safe_car_data("car_new", customer_vehicle_id, id, order_id, customer_vehicle_id_old);} },
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Neues Fahrzeug anlegen"); ?>",
				width:800,
				position:["center",100]
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
			echo '<table style="border-style: solid;border-width: 2px">';
				echo '<tr>';
					echo '<td id="textfield" align="left" colspan="3" style="font-weight: bold; font-size: 16px; border-style: solid; border-width:2px; background-color: #FFCC00">'.t("Bitte wählen Sie Ihr Fahrzeug nach KBA(HSN/TSN)-Nummer oder aus der Hersteller-Liste aus").':</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td align="left" style="width: 160px">'.t("Fahrzeugschein (zu 2(HSN)/ zu 3(TSN))").':</td>';
					echo '<td align="left"><input type="text" id="hsn" size="3" maxlength="4"><p style="color: red; display: inline">*</p>/
							  <input type="text" id="tsn" style="width: 35px"><p style="color: red; display: inline">*</p></td>';
					echo '<td align="left"><button id="kbabutton" onclick="get_car_data();">'.t("Fahrzeugdaten laden").'</button></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td align="left">'.t("Hersteller").':</td>';
					echo '<td align="left"><div id="search_by_car_manufacturer_box"></div></td>';
					echo '<td></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td align="left">'.t("Modell").':</td>';
					echo '<td align="left"><div id="search_by_car_modell_box"></div></td>';
					echo '<td></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td align="left">'.t("Typ").':</td>';
					echo '<td align="left"><div id="search_by_car_type_box"></div></td>';
					echo '<td align="center"><p style="color: red; display: inline">*</p><p style="display: inline">Pflichtfelder</p></td>';
				echo '</tr>';
			echo '</table>';
			echo '<table style="border-style: solid; border-width: 2px; margin-top: 10px">';
				echo '<tr>';
					echo '<td align="left" style="width: 160px">'.t("Erstzulassung (mm/jjjj)").':</td>';
					echo '<td align="left" style="width: 200px"><input type="text" id="month" size="3" maxlength="2"><p style="color: red; display: inline">*</p>/<input type="text" id="year" size="3" maxlength="4"><p style="color: red; display: inline">*</p></td>';
					echo '<td align="left" style="width: 160px">'.t("Fahrgestellnummer").':</td>';
					echo '<td align="left"style="width: 200px"><input type="text" id="fin" name="fin" maxlength="17"><p style="color: red; display: inline">*</p></td>';
				echo '</tr>';
			echo '</table>';
		echo '<div id="criteria"></div>';
		echo '<div id="bill_address"></div>';
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