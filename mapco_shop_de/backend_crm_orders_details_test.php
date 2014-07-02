<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	//echo $OrderID=$_GET["OrderID"];
	//echo $crm_userID=$_GET["customer_id"];
	//$OrderID=1728099;
?>

<style>
.view_box
{
	border-style:solid;
	border-width:1px;
	border-color:#999;
	margin:5px;
}
.border_standard
{
	border-style:solid;
	border-width:1px;
	border-color:#999;
	padding-left:5px;
	padding-right:5px;
	padding-bottom:5px;
	padding-top:5px;
	margin:5px;
}

</style>

<script src="modules/CRM/OM_vehicle_orderitem_correlation.js" type="text/javascript" /></script>
<script type="text/javascript">
var tmp_id="";
var soa_path='<?php echo PATH; ?>soa/';
var OrderID=<?php echo $_GET["OrderID"]; ?>;
var customer_id=<?php echo $_GET["customer_id"]; ?>;
order = new Array();
var tmp='';

	function convert_time_from_timestamp(timestamp, mod)
	{
		var Timestamp = new Date(timestamp*1000);
		var time='';
		if (mod=="complete")
		{
			if (String(Timestamp.getDate()).length==1)
			{
				time+='0'+Timestamp.getDate();
			}
			else 
			{
				time+=Timestamp.getDate();
			}
			if (String(parseInt(Timestamp.getMonth())+1).length==1)
			{
				time+='.0'+String(parseInt(Timestamp.getMonth())+1)+'.'+Timestamp.getFullYear();
			}
			else 
			{
				time+='.'+String(parseInt(Timestamp.getMonth())+1)+'.'+Timestamp.getFullYear();
			}
			time+=' ';
			if (String(Timestamp.getHours()).length==1)
			{
				time+='0'+Timestamp.getHours();
			}
			else 
			{
				time+=Timestamp.getHours();
			}
			if (String(Timestamp.getMinutes()).length==1)
			{
				time+=':0'+Timestamp.getMinutes();
			}
			else 
			{
				time+=':'+Timestamp.getMinutes();
			}
			if (String(Timestamp.getSeconds()).length==1)
			{
				time+=':0'+Timestamp.getSeconds();
			}
			else 
			{
				time+=':'+Timestamp.getSeconds();
			}
		}
		if (mod=="datebuilt")
		{
			if (String(parseInt(Timestamp.getMonth())+1).length==1)
			{
				time+='0'+String(parseInt(Timestamp.getMonth())+1)+'/'+Timestamp.getFullYear();
			}
			else 
			{
				time+=String(parseInt(Timestamp.getMonth())+1)+'/'+Timestamp.getFullYear();
			}
		}
		if (mod=="month")
		{
			if (String(parseInt(Timestamp.getMonth())+1).length==1)
			{
				time+='0'+String(parseInt(Timestamp.getMonth())+1);
			}
			else 
			{
				time+=String(parseInt(Timestamp.getMonth())+1);
			}
		}
		if (mod=="year")
		{
			time+=Timestamp.getFullYear();
		}
		
	  return time;
	}
	
	function field_update(id)
	{
		$("#"+id).css("border", "1px solid #999");
		$("#"+id).bind("blur", function ()
			{
					$("#"+id).css("border", "0px solid");
					
					table_field_update_database(id);
			});
	
		$("#"+id).bind("keypress", function (e)
			{
				if (e.keyCode == 13)
				{
					var ev=e.keyCode;
					$("#"+id).css("border", "0px solid");
					$("#"+id).blur();
					//update_order_item(id);
					//
				}
			});
	}
	
	function table_field_update_database(id)
	{
	//	alert(id);
		$("#"+id).unbind("blur");
	//	$("#"+id).unbind("keypress");
		//$("#"+id).blur();
		if (id.search("orderposition_amount_")>-1)
		{
		//	alert(id.substr(21));
			update_order_item(id.substr(21));
		}
		if (id.search("orderposition_MPN_")>-1)
		{
			update_order_item(id.substr(18));
		}
		
		
		//FAHRZEUGDATEN
		if (id.search("orderposition_HSN_")>-1)
		{
			update_order_item_vehicle(id.substr(18), $("#vehicleID_"+id.substr(18)).val());
		}
		if (id.search("orderposition_TSN_")>-1)
		{
			update_order_item_vehicle(id.substr(18), $("#vehicleID_"+id.substr(18)).val());
		}
		if (id.search("orderposition_datebuilt_")>-1)
		{
			update_order_item_vehicle(id.substr(24), $("#vehicleID_"+id.substr(24)).val());
		}
		if (id.search("orderposition_FIN_")>-1)
		{
			update_order_item_vehicle(id.substr(18), $("#vehicleID_"+id.substr(18)).val());
		}
		if (id.search("orderposition_additionalcardata_")>-1)
		{
			update_order_item_vehicle(id.substr(32), $("#vehicleID_"+id.substr(32)).val());
		}
	}
	
	function update_order_item(orderItemID)
	{
		var amount = $("#orderposition_amount_"+orderItemID).val();
		var price = $("#orderposition_itemprice_"+orderItemID).val();
		var MPN = $("#orderposition_MPN_"+orderItemID).val();
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_update_orderItem", orderItemID:orderItemID, amount:amount, price:price, MPN:MPN },
			function(data)
			{
			//	alert(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					get_order_detail();
				}
				else
				{
					show_status2(data);
				}
			}
		);		
	}
/*	
	function uoi_show_order_items(order_id)
	{
		//set_OrderItem_vehicle(0," ");
		
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_order_detail", OrderID:order_id },
			function(data)
			{
		//		alert(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					var itemtable='';
					itemtable+='<table>';
					itemtable+='<tr>';
					itemtable+='	<th><img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/accept.png" alt="Fahrzeug mit allen Artikeln verknüpfen" title="Fahrzeug mit allen Artikeln verknüpfen" onclick="set_OrderItem_vehicle('+order_id+', \'all\');" /></th>';
					itemtable+='	<th>MPN</th>';
					itemtable+='	<th>Artikel</th>';
					itemtable+='	<th>HSN</th>';
					itemtable+='	<th>TSN</th>';
					itemtable+='	<th>Baujahr</th>';
					itemtable+='	<th>FIN</th>';
					itemtable+='	<th>additinional</th>';
					itemtable+='</tr>';
					
					$xml.find("Item").each(
						function()
						{
							itemtable+='<tr id="uoi_order_item'+$(this).find("OrderItemID").text()+'">';
							itemtable+=' <td>';
							itemtable+='	<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/accept.png" alt="Fahrzeug mit diesem Artikel verknüpfen" title="Fahrzeug mit diesem Artikel verknüpfen" onclick="set_OrderItem_vehicle('+$(this).find("OrderItemID").text()+', \'selected\');" />';
							itemtable+=' </td>';
							itemtable+=' <td>';
							itemtable+=		$(this).find("OrderItemMPN").text();
							itemtable+=' </td>';
							itemtable+=' <td>';
							itemtable+=		$(this).find("OrderItemDesc").text();
							itemtable+=' </td>';
							itemtable+=' <td>';
							itemtable+=		$(this).find("OrderItemVehicleHSN").text();
							itemtable+=' </td>';
							itemtable+=' <td>';
							itemtable+=		$(this).find("OrderItemVehicleTSN").text();
							itemtable+=' </td>';
							itemtable+=' <td>';
							itemtable+=		convert_time_from_timestamp($(this).find("OrderItemVehicleDateBuilt").text(),"datebuilt");
							itemtable+=' </td>';
							itemtable+=' <td>';
							itemtable+=		$(this).find("OrderItemVehicleFIN").text();
							itemtable+=' </td>';
							itemtable+=' <td>';
							itemtable+=		$(this).find("OrderItemVehicleAdditional").text();
							itemtable+=' </td>';
							itemtable+='</tr>';
						}
					);
					itemtable+='</table>';
					$("#order_items").html(itemtable);
				}
			}
		);
		
	}
	
	function show_customer_vehicles(crm_customer_id)
	{
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_customer_vehicles", crm_customer_id:crm_customer_id, mode:"customer"},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					var table='';
					table+='<table>';
					table+='<tr>';
					table+='	<th colspan="8">';
					table+='Kundenfahrzeuge';
					table+='	</th>';
					table+='</tr>';
					table+='<tr>';
					table+='	<td>Fahrzeug</td>';
					table+='	<td>KBA</td>';
					table+='	<td>Baujahr</td>';
					table+='	<td>FIN</td>';
					table+='	<td>additional</td>';
					table+='	<td>Leistung</td>';
					table+='	<td>Hubraum</td>';
					table+='	<td>Herst.Zeitraum</td>';
					table+='</tr>';
					$xml.find("vehicle").each(
						function()
						{
							var customer_vehicle_id=$(this).find("vehicleCustomerID").text();
							if (customer_vehicle_id==$("#update_vehicle_customerVehicleID").val())
							{
								var backgroundcolor='#fdd';
							}
							else
							{
								var backgroundcolor='#fff';
							}
							table+='<tr ondblclick="select_customer_vehicle('+customer_vehicle_id+');" style="background-color:'+backgroundcolor+'; cursor:pointer">';
							table+='	<td>';
							table+=$(this).find("vehicleBrand").text()+' '+$(this).find("vehicleModel").text()+' '+$(this).find("vehicleModelType").text();
							table+='	</td>';
							table+='	<td>';
							table+=$(this).find("vehicleHSN").text()+'|'+$(this).find("vehicleTSN").text();
							table+='	</td>';
							table+='	<td>';
							table+=convert_time_from_timestamp($(this).find("vehicleDateBuilt").text(),"datebuilt");
							table+='	</td>';
							table+='	<td>';
							table+=$(this).find("vehicleFIN").text();
							table+='	</td>';
							table+='	<td>';
							table+=$(this).find("vehicleAdditional").text();
							table+='	</td>';
							table+='	<td>';
							table+=$(this).find("vehicleKW").text()+'('+$(this).find("vehiclePS").text()+')';
							table+='	</td>';
							table+='	<td>';
							table+=$(this).find("vehicleCcmTech").text();
							table+='	</td>';
							table+='	<td>';
							table+=$(this).find("vehicleBuiltFrom").text()+'-'+$(this).find("vehicleBuiltTo").text();
							table+='	</td>';
							table+='</tr>';
						});
					table+='</table>';
					$("#customer_vehicles").html(table);
				}
				else
				{
					show_status2(data);
				}
			}
		);		
	
	}
	
	function select_customer_vehicle(customer_vehicle_id)
	{
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_customer_vehicles", vehicle_customer_id:customer_vehicle_id, mode:"vehicle"},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					var vehicle_info=$xml.find("vehicleBrand").text();
					vehicle_info+=' '+$xml.find("vehicleModel").text();
					vehicle_info+=' '+$xml.find("vehicleModelType").text()+'<br />';
					vehicle_info+=$xml.find("vehicleBuiltFrom").text();
					vehicle_info+='-'+$xml.find("vehicleBuiltTo").text();
					vehicle_info+=' Leistung:'+$xml.find("vehicleKW").text()+'('+$xml.find("vehiclePS").text()+')';
					vehicle_info+=' Hubraum'+$xml.find("vehicleCcmTech").text();
					
					$("#update_vehicle_HSN").val($xml.find("vehicleHSN").text());
					$("#update_vehicle_TSN").val($xml.find("vehicleTSN").text());
					$("#update_vehicle_vehicleInfo").html(vehicle_info);
					$("#update_vehicle_dateBuiltMonth").val(convert_time_from_timestamp($xml.find("vehicleDateBuilt").text(),"month"));
					$("#update_vehicle_dateBuiltYear").val(convert_time_from_timestamp($xml.find("vehicleDateBuilt").text(),"year"));
					$("#update_vehicle_FIN").val($xml.find("vehicleFIN").text());
					$("#update_vehicle_additional").val($xml.find("vehicleAdditional").text());
					
					$("#update_vehicle_customerVehicleID").val($xml.find("vehicleCustomerID").text());
					$("#update_vehicle_vehicleID").val($xml.find("vehicleID").text());	
					
					
					show_customer_vehicles(crm_userID);
					uoi_show_order_items(OrderID);
				}
				else
				{
				}
			}
		);
	}
	
	function select_vehicle_view(data)
	{
		var $xml=$($.parseXML(data));
		var vehicle_table='';
		vehicle_table+= '<table>';
		vehicle_table+= '<tr>';
		vehicle_table+= '	<th>Fahrzeug</th>';
		vehicle_table+= '	<th>Herst.Zeitraum</th>';
		vehicle_table+= '	<th>Leistung</th>';
		vehicle_table+= '	<th>Hubraum</th>';
		vehicle_table+= '</tr>';
		
		$xml.find("vehicle").each(
		function()
		{
			var vehicle_info=$(this).find("vehicleBrand").text();
			vehicle_info+=' '+$(this).find("vehicleModel").text();
			vehicle_info+=' '+$xml.find("vehicleModelType").text()+'<br />';
			vehicle_info+=$(this).find("vehicleBuiltFrom").text();
			vehicle_info+='-'+$(this).find("vehicleBuiltTo").text();
			vehicle_info+=' Leistung:'+$(this).find("vehicleKW").text()+'('+$(this).find("vehiclePS").text()+')';
			vehicle_info+=' Hubraum'+$(this).find("vehicleCcmTech").text();
			
			vehicle_table+= '<tr ondblclick="set_vehicle(\''+vehicle_info+'\','+$(this).find("vehicleID").text()+');" style="background-color:#fff; cursor:pointer">';
			vehicle_table+= '	<td>';
			vehicle_table+=$(this).find("vehicleBrand").text();
			vehicle_table+=' '+$(this).find("vehicleModel").text();
			vehicle_table+=' '+$(this).find("vehicleModelType").text();
			vehicle_table+= '	</td>';
			vehicle_table+= '	<td>';
			vehicle_table+=$(this).find("vehicleBuiltFrom").text();
			vehicle_table+='-'+$(this).find("vehicleBuiltTo").text();
			vehicle_table+= '	</td>';
			vehicle_table+= '	<td>';
			vehicle_table+=''+$(this).find("vehicleKW").text()+'('+$(this).find("vehiclePS").text()+')';
			vehicle_table+= '	</td>';
			vehicle_table+= '	<td>';
			vehicle_table+=' Hubraum'+$(this).find("vehicleCcmTech").text();
			vehicle_table+= '	</td>';
			vehicle_table+= '</tr>';
		});
		vehicle_table+='</table>';
		$("#view").html(vehicle_table);
		
		$("#view").dialog
		({	
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Fahrzeug auswählen",
			width:500
		});		
	}
	
	function set_vehicle(info, vehicleID)
	{
		$("#update_vehicle_customerVehicleID").val(0);
		$("#update_vehicle_vehicleID").val(vehicleID);	
		$("#update_vehicle_vehicleInfo").html(info);
		$("#update_vehicle_dateBuiltMonth").val("");
		$("#update_vehicle_dateBuiltYear").val("");
		$("#update_vehicle_FIN").val("");
		$("#update_vehicle_additional").val("");
		$("#view").dialog("close");
	}
		
	
	function get_vehicle_byKBA()
	{
		var HSN=$("#update_vehicle_HSN").val();
		var TSN=$("#update_vehicle_TSN").val();
		
		if (!HSN.length==4)
		{
			alert("Die HSN muss 4-stellig sein!");
			$("#update_vehicle_HSN").focus();
			return;
		}
		if (!TSN.length==3)
		{
			alert("Die TSN muss 4-stellig sein!");
			$("#update_vehicle_TSN").focus();
			return;
		}
		
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "get_vehicle_byKBA", TSN:TSN, HSN:HSN},
			function(data)
			{
				
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					select_vehicle_view(data);
				}
				else
				{
					show_status2(data);
				}
			}
		);

	}
	
	function update_customer_vehicle()
	{
		var vehicleID=$("#update_vehicle_vehicleID").val();
		var customerVehicleID=$("#update_vehicle_customerVehicleID").val();
		var crm_customer_id=crm_userID;
		
		var HSN=$("#update_vehicle_HSN").val();
		var TSN=$("#update_vehicle_TSN").val();
		var dateBuiltMonth=$("#update_vehicle_dateBuiltMonth").val();
		var dateBuiltYear=$("#update_vehicle_dateBuiltYear").val();
		var FIN=$("#update_vehicle_FIN").val();
		var additional=$("#update_vehicle_additional").val();
			
		if (dateBuiltMonth!="" && dateBuiltYear!="")
		{
			var datum = new Date(dateBuiltYear,String(parseInt(dateBuiltMonth)-1),'1','0','0','0');
			dateBuilt=datum.getTime()/1000;
		}
		else {dateBuilt=0;}

		var mode="";
		
		if (customerVehicleID==0) mode="add"; else mode="update";
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm_test", Action: "crm_update_orderItem_vehicle", mode:mode, vehicleID:vehicleID, customerVehicleID:customerVehicleID, crm_customer_id:crm_customer_id, HSN:HSN, TSN:TSN, dateBuilt:dateBuilt, FIN:FIN, additional:additional },
			function(data)
			{
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#update_vehicle_customerVehicleID").val($xml.find("customerVehicleID").text()) ;
					show_customer_vehicles(crm_customer_id);
					uoi_show_order_items(OrderID);
				}
				else
				{
					show_status2(data);
				}
			}
		);		
	}

	function vehicle_item_update(OrderItemID)
	{
		/*
		if (id.search("orderposition_HSN_")>-1)
		{
			//var OrderItemID=$("#vehicleID_"+id.substr(18)).val();
			var OrderItemID=id.substr(18);
		}
		if (id.search("orderposition_TSN_")>-1)
		{
			//var OrderItemID=$("#vehicleID_"+id.substr(18)).val();
			var OrderItemID=id.substr(18);
		}
		if (id.search("orderposition_datebuilt_")>-1)
		{
			//var OrderItemID=$("#vehicleID_"+id.substr(24)).val();
			var OrderItemID=id.substr(24);
		}
		if (id.search("orderposition_FIN_")>-1)
		{
			//var OrderItemID=$("#vehicleID_"+id.substr(18)).val();
			var OrderItemID=id.substr(18);
		}
		if (id.search("orderposition_additionalcardata_")>-1)
		{
			//var OrderItemID=$("#vehicleID_"+id.substr(32)).val();
			var OrderItemID=id.substr(32);
		}
		*/
		//GET CUSTOMER VEHICLES
	//	show_customer_vehicles(crm_userID);
	/*	
		//Setze vorhanden Verknüpfung
		if (!$("#customer_vehicle_id_"+OrderItemID).val()==0)	select_customer_vehicle($("#customer_vehicle_id_"+OrderItemID).val());

		//GET CUSTOMER VEHICLES
		show_customer_vehicles(crm_userID);
		uoi_show_order_items(OrderID);
		$("#update_vehicleDialog").dialog
		({	buttons:
			[
				//{ text: "Fz-Zuordnung für alle Artikel speichern", click: function() {set_OrderItem_vehicle(OrderItemID, "all");} },
				//{ text: "Fz-Zuordnung zu diesem Artikel speichern", click: function() {set_OrderItem_vehicle(OrderItemID, "selected");} },
				{ text: "Fertig", click: function() { $(this).dialog("close"); get_order_detail();} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Fahrzeug zum Artikel auswählen",
			width:1400
		});		
		
	}
	
	function input_HSN()
	{
		if ($("#update_vehicle_HSN").val().length==4) $("#update_vehicle_TSN").focus();
	}
	
	function input_TSN()
	{
		if ($("#update_vehicle_TSN").val().length==3) $("#update_vehicle_btn").focus();
	}

	
	function set_OrderItem_vehicle(OrderItemID, mode)
	{

		var customerVehicleID=$("#update_vehicle_customerVehicleID").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_set_orderItem_vehicle", customerVehicleID:customerVehicleID, OrderItemID:OrderItemID, OrderID:OrderID, mode:mode },
			function(data)
			{
				//alert(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					show_customer_vehicles(crm_userID);
					uoi_show_order_items(OrderID);

				//	get_order_detail();
				//	$("#update_vehicleDialog").dialog("close");
				}
				else
				{
					show_status2(data);
				}
			}
		);	
	
	}
	
	

	function update_order_item_vehicle(orderItemID, vehicleID)
	{
		var HSN = $("#orderposition_HSN_"+orderItemID).val();
		var TSN = $("#orderposition_TSN_"+orderItemID).val();
		var dateBuilt = $("#orderposition_datebuilt_"+orderItemID).val();
		var FIN = $("#orderposition_FIN_"+orderItemID).val();
		var additional = $("#orderposition_additionalcardata_"+orderItemID).val();
		
		if (!dateBuilt=="")
		{
			var datum = new Date(dateBuilt.substr(3),dateBuilt.substr(0,2)-1,'1','0','0','0');
			dateBuilt=datum.getTime()/1000;
		}
		else {dateBuilt=0;}
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_update_orderItem_vehicle", vehicleID:vehicleID, HSN:HSN, TSN:TSN, dateBuilt:dateBuilt, FIN:FIN, additional:additional },
			function(data)
			{
			//	alert(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					get_order_detail();
				}
				else
				{
					show_status2(data);
				}
			}
		);		
		
	}
*/
	function show_shipping_address()
	{
		var html='';
			//BuyerData_Address
		//Gender
		if (order["ship_gender"]!="") html+=order["ship_gender"]+'<br />';
		
		//Name
		if (order["ship_company"]!="") html+=order["ship_company"]+'<br />';

		//Additional		
		if (order["ship_additional"]!="") html+=order["ship_additional"]+'<br />';

		//Name
		if (order["ship_firstname"]!="") html+=order["ship_firstname"]+' ';
		html+=order["ship_lastname"]+'<br />';

		//Street
		html+=order["ship_street"]+' '+order["ship_number"]+'<br />';
		
		//ZIP + CITY
		html+=order["ship_zip"]+' '+order["ship_city"]+'<br />';
		
		//COUNTRY
		html+=order["ship_country"];
	
		$("#shipping_address").html(html);

	}

	function get_order_detail()
	{
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_order_detail", OrderID:OrderID },
			function(data)
			{
		//		alert(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					
					$xml.find("Order").each(
						function()
						{

							order["ship_company"]=$(this).find("bill_company").text();
							order["ship_firstname"]=$(this).find("bill_firstname").text();
							order["ship_lastname"]=$(this).find("bill_lastname").text();
							order["ship_street"]=$(this).find("bill_street").text();
							order["ship_number"]=$(this).find("bill_number").text();
							order["ship_additional"]=$(this).find("bill_additional").text();
							order["ship_zip"]=$(this).find("bill_zip").text();
							order["ship_city"]=$(this).find("bill_city").text();
							order["ship_country"]=$(this).find("bill_country").text();
							order["ship_country_code"]=$(this).find("bill_country_code").text();
							order["OrderTotal"]=$(this).find("OrderTotal").text();
				
			/*				
							//BuyerData_Address
							//Gender
							$("#BuyerData_Address_Gender").text($(this).find("bill_gender").text());
							//Name
							var KundBez="";
							if ($(this).find("bill_company").text()!="") KundBez+=$(this).find("bill_company").text()+' ';
							KundBez+=$(this).find("bill_firstname").text()+' '+$(this).find("bill_lastname").text();
							$("#BuyerData_Address_Name").text(KundBez);
							//Street
							var street="";
							street=$(this).find("bill_street").text();
							if ($(this).find("bill_number").text()!="") street+=' '+$(this).find("bill_number").text();
							$("#BuyerData_Address_Street").text(street);
							//Additional
							$("#BuyerData_Address_Additional").text($(this).find("bill_additional").text());
							//ZIP
							$("#BuyerData_Address_Zip").text($(this).find("bill_zip").text());
							//CITY
							$("#BuyerData_Address_City").text($(this).find("bill_city").text());
							//COUNTRY
							$("#BuyerData_Address_Country").text($(this).find("bill_country").text());
					*/		
							show_shipping_address();
							
							//BuyerData_Contact
							$("#BuyerData_Contact_Mail").text($(this).find("usermail").text());
							$("#BuyerData_Contact_Phone").text($(this).find("userphone").text());
							$("#BuyerData_Contact_Fax").text($(this).find("userfax").text());
							$("#BuyerData_Contact_Mobile").text($(this).find("usermobile").text());

							//ORDERDATA
							var orderstable='<table id="orderstable">';
							orderstable+='<tr>';
							orderstable+='	<th style="width:20px"></th>';
							orderstable+='	<th style="width:20px">Anz.</th>';
							orderstable+='	<th style="width:40px">MPN</th>';
							orderstable+='	<th style="width:300px">Artikel</th>';
							orderstable+='	<th style="width:30px">Art.Einz.</th>';
							orderstable+='	<th style="width:30px">Art.Gesamt</th>';
							orderstable+='	<th style="width:30px">Anz.Verf.</th>';
							orderstable+='	<th style="width:40px">HSN</th>';
							orderstable+='	<th style="width:40px">TSN</th>';
							orderstable+='	<th style="width:50px">Baujahr</th>';
							orderstable+='	<th style="width:150px">FIN</th>';
							orderstable+='	<th style="width:130px">weiter Fz.Daten</th>';
							orderstable+='	<th style="width:30px">';
							orderstable+='		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/32x32/add.png" alt="Neue Position der Bestellung hinzufügen" title="Neue Position der Bestellung hinzufügen" onclick="order_addItem('+OrderID+');" />';
							orderstable+='	</th>';
							orderstable+='</tr>';							

							orderstable+='<tr style="background-color:"#ddd">';
							orderstable+='	<td></td>';
							orderstable+='	<td id="orderposition_amount">';
							orderstable+='		<b>'+$(this).find("OrderTotalCount").text()+'</b>';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_MPN">';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_title">';
							orderstable+='		<b>Versandkosten:</b>';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_itemprice">';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_itemtotal">';
							orderstable+='	<b>'+$(this).find("OrderShippingCosts").text()+'</b>';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_amountstock">';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_HSN">';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_TSN">';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_datebuilt">';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_FIN">';
							orderstable+='	</td>';
							orderstable+='	<td id="orderposition_additionalcardata">';
							orderstable+='	</td>';
							orderstable+='	<td></td>';
							orderstable+='</tr>';
							//ORDERDATA ITEMS
							
							var orderposition=0;
							
							$xml.find("Item").each(
							function()
							{
								var orderItemID=$(this).find("OrderItemID").text();
								orderstable+='<tr>';
								orderstable+='	<td></td>';
								orderstable+='	<td>';
								orderstable+=' <input type="text" style="width:35px; border: 0px solid;" id="orderposition_amount_'+orderItemID+'" value="'+$(this).find("OrderItemAmount").text()+'" onclick="field_update(this.id);" />';
								orderstable+='	</td>';
								orderstable+='	<td>';
								orderstable+='	<input type="text" style="width:35px; border: 0px solid;" id="orderposition_MPN_'+orderItemID+'" value="'+$(this).find("OrderItemMPN").text()+'" onclick="field_update(this.id);" />';
								orderstable+='	</td>';
								orderstable+='	<td id="orderposition_title_'+orderItemID+'">';
								orderstable+=$(this).find("OrderItemDesc").text();
								orderstable+='	</td>';
								orderstable+='	<td id="orderposition_itemprice_'+orderItemID+'">';
								orderstable+=$(this).find("OrderItemPrice").text();
								orderstable+='	</td>';
								orderstable+='	<td id="orderposition_itemtotal_'+orderItemID+'">';
								orderstable+=$(this).find("OrderItemTotal").text();
								orderstable+='	</td>';
								orderstable+='	<td id="orderposition_amountstock_'+orderItemID+'">';
								orderstable+='	</td>';
								orderstable+='	<td id="orderposition_HSN_'+orderItemID+'" ondblclick="vehicle_item_update('+customer_id+');" style="cursor:pointer">';
								orderstable+=$(this).find("OrderItemVehicleHSN").text();
								orderstable+='	</td>';
								orderstable+='	<td id="orderposition_TSN_'+orderItemID+'" ondblclick="vehicle_item_update('+orderItemID+');" style="cursor:pointer">';
								orderstable+=$(this).find("OrderItemVehicleTSN").text();
								orderstable+='	</td>';
								orderstable+='	<td id="orderposition_datebuilt_'+orderItemID+'" ondblclick="vehicle_item_update('+OrderID+', '+customer_id+');" style="cursor:pointer">';
								orderstable+=convert_time_from_timestamp($(this).find("OrderItemVehicleDateBuilt").text(),"datebuilt");
								orderstable+='	</td>';
								orderstable+='	<td id="orderposition_FIN_'+orderItemID+'" ondblclick="vehicle_item_update('+OrderID+', '+customer_id+');" style="cursor:pointer">';
								orderstable+=$(this).find("OrderItemVehicleFIN").text();
								orderstable+='	</td>';
								orderstable+='	<td id="orderposition_additionalcardata_'+orderItemID+'" ondblclick="vehicle_item_update('+OrderID+', '+customer_id+');" style="cursor:pointer">';
								orderstable+=$(this).find("OrderItemVehicleAdditional").text();
								var tmp=$(this).find("OrderItemCustomerVehicleID").text();
								orderstable+='<input type="hidden" id="customer_vehicle_id_'+orderItemID+'" value="'+tmp+'" />';
								orderstable+='	</td>';
								orderstable+='	<td>';
								orderstable+='		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/16x16/remove.png" alt="Position aus Bestellung löschen" title="Position aus Bestellung löschen" onclick="order_deleteItem('+orderItemID+', '+OrderID+');" />';
								orderstable+='	</td>';
								orderstable+='</tr>';
								
								orderposition++;
							});
							orderstable+='</table>';
							$("#OrderData").html("");
							$("#OrderData").html(orderstable);


						}
					);
				}
				else
				{
					//FEHLER
				}
				get_order_events();
			}
		);
	}


	function get_order_events()
	{
//		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_order_events", OrderID:OrderID },
			function(data)
			{
				//alert(data);
				//wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("OrderEvent").each(
						function()
						{
							var OrderEvent = $(this).find("Event").text();
							var OrderEventID = $(this).find("EventID").text();
							//Payments
							if (OrderEvent=="Payment")
							{
								var OrderEventOutput='';
								OrderEventOutput+='<div id="event'+OrderEventID+'" style="width:499px; float:left;">';
								OrderEventOutput+='<span style="width:100px; float:left;">'+$(this).find("PaymentMethod").text()+'</span>';
								OrderEventOutput+='<span style="width:100px; float:left;">'+$(this).find("PaymentState").text()+'</span>';
								var PaymentTime= new Date($(this).find("PaymentTime").text()*1000);
								OrderEventOutput+='<span style="width:100px; float:left;">'+PaymentTime.getDate()+'.'+PaymentTime.getMonth()+'.'+PaymentTime.getFullYear()+'</span><br />';
								OrderEventOutput+='</div>';
								//alert (OrderEventOutput);
								$("#OrderEventsPayments").html(OrderEventOutput);
								if ($(this).find("PaymentType").text()=="paymentrecieved") $("#event"+OrderEventID).css("background-color", "#6F9");
							}
							// SHIPPING ASSIGNED
							if (OrderEvent=="ShipmentAssigned")
							{
								var OrderEventOutput='';
								OrderEventOutput+='<div id="event'+OrderEventID+'" style="width:499px; float:left;">Shipment Assigned<br />';
								if ($(this).find("ShippedPartially").text()=="No")
								{
									OrderEventOutput+='<span style="width:150px; float:left;">Versand komplett</span>';
								}
								else 
								{
									OrderEventOutput+='<span style="width:150px; float:left;">partieller Versand</span>';
								}
								OrderEventOutput+='<span style="width:120px; float:left;">'+$(this).find("ShippingService").text()+'</span>';
								var ShipmentAssignedTime= new Date($(this).find("ShipmentAssignedTime").text()*1000);
								OrderEventOutput+='<span style="width:229px; float:left;">Versand beauftragt am: '+ShipmentAssignedTime.getDate()+'.'+ShipmentAssignedTime.getMonth()+'.'+ShipmentAssignedTime.getFullYear()+'</span><br />';
								OrderEventOutput+='</div>';
								$("#OrderEventsShipment").html(OrderEventOutput);
							}
							//SHIPPING EXECUTED
							if (OrderEvent=="ShipmentExecuted")
							{
								var OrderEventOutput='';
								OrderEventOutput+='<div id="event'+OrderEventID+'" style="width:499px; float:left;">Shipment Executed<br />';
								OrderEventOutput+='<span style="width:120px; float:left;">'+$(this).find("ShippingService").text()+'</span>';
								var ShipmentExecuteTime= new Date($(this).find("ShipmentExecuteTime").text()*1000);
								OrderEventOutput+='<span style="width:229px; float:left;">Versand erfolgt am: '+ShipmentExecuteTime.getDate()+'.'+ShipmentExecuteTime.getMonth()+'.'+ShipmentExecuteTime.getFullYear()+'</span><br />';
								OrderEventOutput+='</div>';
								$("#OrderEventsShipment").append(OrderEventOutput);
							}

						}
					);

				}
				else
				{
					//FEHLER
				}
			}
		);
	}
	
	function get_customer_notes()
	{
	/*
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_customer_notes2", customer_id:crm_userID, mode:"customer" },
			function(data)
			{
				//alert(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					var note_box='';
					note_box+='<span><b>Notizen zum Kunden</b></span>';
					note_box+='<span style="float:right; background-color:#bbb;">';
					note_box+='<a href="javascript:$(\'#noteView\').slideUp(300); $(\'#noteView\').html(\'\');" title="Notizen schließen" alt="Notizen schließen">&nbsp;X&nbsp;</a>';
					note_box+='</span>';
					
					$xml.find("note").each(
					function()
					{
						var first_note= convert_time_from_timestamp($(this).find("note_firstmod").text(), "complete");
						var last_note=convert_time_from_timestamp($(this).find("note_lastmod").text(), "complete");
						var id_note=$(this).find("noteID").text();
						note_box+='<div id="customer_note'+customer_id+'" class="search_option_box">';
						note_box+='	<div style="background-color:#ddd; width:100%">';
						note_box+='	<span style=" font-size:8pt">Notiz von '+$(this).find("note_firstmod_user_name").text()+' am '+first_note+'</span>';
						note_box+='</div>';
						note_box+='';
						note_box+=	'<br />'+$(this).find("note_text").text()+'<br /><br />';
						note_box+='	<div style="background-color:#ddd; width:100%">';
						note_box+='	<span style="font-size:8pt">';
						note_box+='		<i>letzte Bearbeitung durch '+$(this).find("note_lastmod_user_name").text()+' am '+last_note+'</i>';
						note_box+='	</span>';
						note_box+='</div>';
						note_box+='<div>';
						note_box+='	<span style="font-size:8pt"><a href="javascript:update_customer_note('+id_note+');">[bearbeiten]</a></span>';
						note_box+='	<span style="font-size:8pt; float:right"><a href="javascript:delete_customer_note('+id_note+');">[löschen]</a></span>';
						note_box+='</div>';
						
						$("#OrderNotes").append(note_box);
						
					});

				}
				else
				{
				}
			}
		);
		*/
	}
	
	function add_customer_notes(mode)
	{
		$("#add_customer_noteDialog").dialog
		({	buttons:
			[
				{ text: "Notiz hinzufügen", click: function() { save_add_customer_notes(mode); } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Notiz zum Kunden hinzufügen",
			width:400
		});		
	}
	
	function save_add_customer_notes(mode)
	{
		if (mode=="order")
		{
			var orderid=OrderID;
		}
		else
		{
			var orderid=0;
		}
		
		var note=$("#add_customer_note").val();
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_add_customer_note2", customer_id:crm_userID, order_id:orderid, note:note },
			function(data)
			{
				//alert(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					show_status("Notiz wurde erfolgreich gespeichert");
					$("#add_customer_noteDialog").dialog("close");
					get_customer_notes();
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}
	
	
	function order_addItem()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_set_new_orderItem", OrderID:OrderID },
			function(data)
			{
				//alert(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					get_order_detail(OrderID);
					alert($xml.find("shop_order_item_id").text());
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}
	
	function order_deleteItem(orderItemID)
	{
		$("#view").dialog
		({	buttons:
			[
				{ text: "Position löschen", click: function() { save_order_deleteItem(orderItemID); } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Position wirklich löschen?",
			width:300
		});		
	}
	
	function save_order_deleteItem(orderItemID)
	{
		alert(OrderID);
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_delete_orderItem", orderItemID:orderItemID },
			function(data)
			{
				alert(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#view").dialog("close");
					get_order_detail(OrderID);
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}
	
	function change_shippingAddress()
	{
		$("#update_shipping_address_company").val(order["ship_company"]);
		$("#update_shipping_address_firstname").val(order["ship_firstname"]);
		$("#update_shipping_address_lastname").val(order["ship_lastname"]);
		$("#update_shipping_address_street").val(order["ship_street"]);
		$("#update_shipping_address_number").val(order["ship_number"]);
		$("#update_shipping_address_additional").val(order["ship_additional"]);
		$("#update_shipping_address_zip").val(order["ship_zip"]);
		$("#update_shipping_address_city").val(order["ship_city"]);
		$("#update_shipping_address_country_code").val(order["ship_country_code"]);
				
		$("#update_shipping_addressDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { change_shippingAddress_save();} },
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Versandadresse bearbeiten",
			width:400
		});		

	}
	
	function change_shippingAddress_save()
	{
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_update_shipping_address", 
			OrderID:OrderID,
			ship_company:$("#update_shipping_address_company").val(),
			ship_firstname:$("#update_shipping_address_firstname").val(),
			ship_lastname:$("#update_shipping_address_lastname").val(),
			ship_street:$("#update_shipping_address_street").val(),
			ship_number:$("#update_shipping_address_number").val(),
			ship_additional:$("#update_shipping_address_additional").val(),
			ship_zip:$("#update_shipping_address_zip").val(),
			ship_city:$("#update_shipping_address_city").val(),
			ship_country_code:$("#update_shipping_address_country_code").val(),
			amount:order["OrderTotal"]
			
			 },
			function(data)
			{
				//show_status2(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#update_shipping_addressDialog").dialog("close");
					
					order["ship_company"]=$("#update_shipping_address_company").val();
					order["ship_firstname"]=$("#update_shipping_address_firstname").val();
					order["ship_lastname"]=$("#update_shipping_address_lastname").val();
					order["ship_street"]=$("#update_shipping_address_street").val();
					order["ship_number"]=$("#update_shipping_address_number").val();
					order["ship_additional"]=$("#update_shipping_address_additional").val();
					order["ship_zip"]=$("#update_shipping_address_zip").val();
					order["ship_city"]=$("#update_shipping_address_city").val();
					order["ship_country_code"]=$("#update_shipping_address_country_code").val();
					order["ship_country"]=$xml.find("country").text();
					show_shipping_address();
					
					
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}

	
	function goback()
	{
		
		var href="";
		href+='<?php echo PATH."backend_crm_orders.php?"; ?>';
		href+='backlink=true';
		href+='&OrderID=<?php echo $_GET["OrderID"]; ?>';
		href+='&ResultPage=<?php echo $_GET["ResultPage"]; ?>';
		href+='&ResultPages=<?php echo $_GET["ResultPages"]; ?>';
		href+='&ResultRange=<?php echo $_GET["ResultRange"]; ?>';
		href+='&Results=<?php echo $_GET["Results"]; ?>';
		href+='&FILTER_Platform=<?php echo $_GET["FILTER_Platform"]; ?>';
		href+='&FILTER_Status=<?php echo $_GET["FILTER_Status"]; ?>';
		href+='&FILTER_SearchFor=<?php echo $_GET["FILTER_SearchFor"]; ?>';
		href+='&FILTER_Searchfield=<?php echo $_GET["FILTER_Searchfield"]; ?>';
		href+='&date_from=<?php echo $_GET["date_from"]; ?>';
		href+='&date_to=<?php echo $_GET["date_to"]; ?>';
		href+='&OrderBy=<?php echo $_GET["OrderBy"]; ?>';
		href+='&OrderDirection=<?php echo $_GET["OrderDirection"]; ?>';
		
		//alert(href);
		window.location = href;		

	}

</script>	

<?php	

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_crm_index.php">CRM</a>';
	echo ' > CRM-Order-Detail';
	echo '</p>';
	
	echo '<p>';
	echo '<h1>CRM-Order-Detail</h1>';
	echo '</p>';

	echo '<p><a href="javascript:goback();">Zurück zur Bestellungsübersicht</a></p>';
	
	//VIEW	
	echo '<div style="width:1100px; display:inline">';
	echo '	<div id="BuyerData" class="view_box" style="width:500px; height:200px; float:left">';
	echo '		<div id="BuyerData_Address" style="width:248px; float:left">';
	echo '			<span style="width:248px; float:left; background-color:#aaa">';
	echo '				<b>Lieferadresse: </b><a href="javascript:change_shippingAddress();" style="float:right">ändern</a>';
	echo '			</span>';
	echo '		<div id="shipping_address" style="width:500px;"></div>';
	echo ' 		</div>';
	echo '		<div id="BuyerData_Contact" style="width:248px; float:right;">';
	echo '			<span style="width:248px; float:left; background-color:#aaa">';
	echo '				<b>Kontakt</b><a href="javascript:select_customer();" style="float:right">Kunde wählen</a>';
	echo '			</span>';
	echo '			<span style="width:248px; float:left;" id="BuyerData_Contact_Mail"></span>';
	echo '			<span style="width:248px; float:left;" id="BuyerData_Contact_Phone"></span>';
	echo '			<span style="width:248px; float:left;" id="BuyerData_Contact_Fax"></span>';
	echo '			<span style="width:248px; float:left;" id="BuyerData_Contact_Mobile"></span>';
	echo ' 		</div>';
	echo '	</div>';
	
	echo '	<div style="width:500px; float:left;">';
	echo '		<div id="OrderEventsPayments" class="view_box" style="width:500px; height:97px" float:left;"></div>';
	echo '		<div id="OrderEventsShipment" class="view_box" style="width:500px; height:97px" float:left;"></div>';
	echo '	</div>';
	
	echo '	<br style="clear:both" />';
	
	echo '	<div id="Communication" class="view_box" style="width:500px; height:200px; float:left">';
	echo '		<span style="width:500px; float:left; background-color:#aaa"><b>Kommunikation zur Bestellung</b></span>';
	echo '	</div>';
	
	echo '	<div id="OrderNotes" class="view_box" style="width:500px; height:200px; float:left">';
	echo '		<span style="width:480px; float:left; background-color:#aaa">';
	echo '			<b>Notizen</b>';
	echo '		</span>';
	echo '		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/16x16/add.png" alt="Neue Notiz zum Kunden hinzufügen" title="Neue Notiz zum Kunden hinzufügen" onclick="add_customer_notes(\'customer\');" />';
	echo '	</div>';
	
	echo '	<br style="clear:both" />';
	
	echo '	<div id="OrderData" class="view_box" style=" overflow:auto; width:1310px; height:250px; float:left;">';
	echo '		</div>';
	echo '	</div>';
	
	echo '</div>';
	
	echo '<div id="view" style="display:none"></div>';
	
	
	//UPDATE ITEMVEHICLE DIALOG
	echo '<div id="update_vehicleDialog" style="display:none;"></div>';

	/*
	echo '<div id="update_vehicleDialog" style="display:none;">';
	echo '<div style="float:left">';
	echo '	<table style="float:left">';
	echo '	<tr>';
	echo '		<th colspan="2">Neues Fahrzeug eingeben / Daten ändern</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '	<tr>';
	echo '		<td>KBA</td>';
	echo '		<td><input type="text" size="4" id="update_vehicle_HSN" onkeyup="input_HSN();" />&nbsp;/&nbsp;<input type="text" size="4" id="update_vehicle_TSN" onkeyup="input_TSN();"/>&nbsp;<button id="update_vehicle_btn" onclick="get_vehicle_byKBA();">Fahrzeug suchen</button></td>';
	echo '	<tr>';
	echo '		<td>Fahrzeug-Info</td>';
	echo '		<td><div id="update_vehicle_vehicleInfo" style="width:300px; height:80px;"></div></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Baujahr</td>';
	echo '		<td><input type="text" size="2" id="update_vehicle_dateBuiltMonth" value="01" />&nbsp;/&nbsp;<input type="text" size="4" id="update_vehicle_dateBuiltYear" value="1970"/></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>FIN</td>';
	echo '		<td><input type="text" size="17" id="update_vehicle_FIN" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>weitere Daten</td>';
	echo '		<td><textarea cols="40" rows="4" id="update_vehicle_additional"></textarea></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td></td>';
	echo '		<td><button id="btn2" onclick="update_customer_vehicle();">Fz in Kundenliste übernehmen / Änderung speichern</button></td>';
	echo '	</tr>';
	echo '	</table>';
	echo '</div>';
	echo '<div style="float:left">';
	echo '	<div id="customer_vehicles"></div>';
	echo '	<br style="clear:both">';
	echo '	<div id="order_items"></div>';
	echo '	<input type="text" id="update_vehicle_vehicleID" />';
	echo '	<input type="text" id="update_vehicle_customerVehicleID" value=0 />';
	echo '</div>';
	echo '</div>';
*/
	//add_customer_note DIALOG
	echo '<div id="add_customer_noteDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Notiz zum Kunden hinzufügen:</td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td><textarea name="customer_note" id="add_customer_note" cols="50" rows="10"></textarea></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//UPDATE SHIPPING ADDRESS DIALOG
	echo '<div id="update_shipping_addressDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td colspan="2">Firma<br />';
	echo '	<input type="text" size="50" id="update_shipping_address_company" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td colspan="2">Adresszusatz / Postnummer<br />';
	echo '	<input type="text" size="50" id="update_shipping_address_additional" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Vorname<br />';
	echo '	<input type="text" size="20" id="update_shipping_address_firstname" /></td>';
	echo '	<td>Nachname<br />';
	echo '	<input type="text" size="20" id="update_shipping_address_lastname" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Straße / Packstation<br />';
	echo '	<input type="text" size="20" id="update_shipping_address_street" /></td>';
	echo '	<td>Nummer / Packstat.Nr.<br />';
	echo '	<input type="text" size="3" id="update_shipping_address_number" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Postleitzahl<br />';
	echo '	<input type="text" size="6" id="update_shipping_address_zip" /></td>';
	echo '	<td>Stadt<br />';
	echo '	<input type="text" size="20" id="update_shipping_address_city" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td colspan="2">Land<br />';
	echo '	<select id="update_shipping_address_country_code" size="1">';
	$res=q("SELECT * FROM shop_countries;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		echo '<option value="'.$row["country_code"].'">'.$row["country"].'</option>';
	}
	echo '	</select>';
//	echo '	<input type="text" size="50" id="update_shipping_address_country" /></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

	echo '<script type="text/javascript">get_order_detail(); get_customer_notes();</script>';

	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>

