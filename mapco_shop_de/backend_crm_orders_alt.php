<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<style>
.tabs
{
	border-style:solid;
	border-width:1px;
	border-color:#999;
	border-bottom-width:0px;
	padding-left:10px;
	padding-right:10px;
	padding-bottom:10px;
	padding-top:10px;
	margin-top:5px;
	margin-bottom:0px;
	margin-left:5px;
	background-color:#eee;
}

.border_standard
{
	border-style:solid;
	border-width:1px;
	border-color:#999;
	padding-left:5px;
	padding-right:5px;
	padding-bottom:7px;
	padding-top:6px;
	margin:5px;
}

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
</style>
<script src="modules/CRM/OM_vehicle_orderitem_correlation.js" type="text/javascript" /></script>
<script type="text/javascript">

<?php


if(isset($_GET["backlink"]) &&  $_GET["backlink"]=="true")
{
	echo 'var ResultPage='.$_GET["ResultPage"].';'."\n";
	echo 'var ResultPages='.$_GET["ResultPages"].';'."\n";
	echo 'var ResultRange='.$_GET["ResultRange"].';'."\n";
	echo 'var Results='.$_GET["Results"].';'."\n";
	echo 'var FILTER_Platform='.$_GET["FILTER_Platform"].';'."\n";
	echo 'var FILTER_Status='.$_GET["FILTER_Status"].';'."\n";
	echo 'var FILTER_SearchFor='.$_GET["FILTER_SearchFor"].';'."\n";
	echo 'var FILTER_Searchfield="'.$_GET["FILTER_Searchfield"].'";'."\n";
	echo 'var date_from="'.$_GET["date_from"].'";'."\n";
	echo 'var date_to="'.$_GET["date_to"].'";'."\n";
	echo 'var OrderBy="'.$_GET["OrderBy"].'";'."\n";
	echo 'var OrderDirection="'.$_GET["OrderDirection"].'";'."\n";

}
else
{
	echo 'var ResultPage=1;'."\n";
	echo 'var ResultPages=0;'."\n";
	echo 'var ResultRange=50;'."\n";
	echo 'var Results=0;'."\n";
	echo 'var FILTER_Platform=0;'."\n";
	echo 'var FILTER_Status=0;'."\n";
	echo 'var FILTER_SearchFor=1;'."\n";
	echo 'var FILTER_Searchfield="";'."\n";
	echo 'var date_from="";'."\n";
	echo 'var date_to="";'."\n";
	echo 'var OrderBy="firstmod";'."\n";
	echo 'var OrderDirection="down";'."\n";
	
	echo 'var FILTER_Country="";'."\n";

}
?>

var soa_path='<?php echo PATH; ?>soa/';

orders = new Array();

//PRELOADED FIELDS
	PaymentTypes = new Array();
	ShipmentTypes = new Array();
	Shop_Shops = new Array();
	DHL_RetourLabelParameter = new Array();
	Seller = new Array();
	Countries= new Array();
	Currencies = new Array();

//to avoid asynchronism -> load view_box after PRELOADS (ready_function())
var preloadcount=0;

	function check_OrderItem_vehicle($id_orderitem, $id_carfleet)
	{
		wait_dialog_show();
		$.post(soa_path, { API:"crm", Action:"OrderItemVehicleCheck", id_orderitem:$id_orderitem, id_carfleet:$id_carfleet }, function($data)
		{
			wait_dialog_hide();
			try
			{
				$xml = $($.parseXML($data));
				$ack = $xml.find("Ack");
				if ( $ack.text()=="Success" )
				{
					var $Restrictions=$xml.find("Restrictions").text();
					if( $Restrictions=="" )
					{
						alert("Das Teil passt.");
						return;
					}
					else
					{
						alert('Das Teil passt, wenn folgende Einschränkungen erfüllt sind: '+$Restrictions);
						return;
					}
				}
				else
				{
					alert("Das Teil passt NICHT!!!");
					return;
				}
			}
			catch (err)
			{
				show_status2(err.message);
				return;
			}
		});
	}


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
		if (mod=="date")
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

	// DATEPICKERS ----------------------------------------------------------------------------------------------------------

		$.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
					closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
					prevText: '<zurück', prevStatus: 'letzten Monat zeigen',
					nextText: 'Vor>', nextStatus: 'nächsten Monat zeigen',
					currentText: 'heute', currentStatus: '',
					monthNames: ['Januar','Februar','März','April','Mai','Juni',
					'Juli','August','September','Oktober','November','Dezember'],
					monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
					'Jul','Aug','Sep','Okt','Nov','Dez'],
					monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
					weekHeader: 'Wo', weekStatus: 'Woche des Monats',
					dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
					dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
					dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
					dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
					dateFormat: 'dd.mm.yy', firstDay: 1, 
					initStatus: 'Wähle ein Datum', isRTL: false};
		$.datepicker.setDefaults($.datepicker.regional['de']);


	$(function()
	{
		$( "#date_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
	});

	$(function()
	{
		$( "#date_to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
	});
	
	$(function()
	{
		$( "#update_Payment_Date" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
	});
	//_________________________________________________________________________		

	$(function()
	{

		//PRELOAD SHIPMENT TYPES
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "Get_Shipment_Types", mode:"all" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("ShipmentType").each(
						function()
						{
							ShipmentTypes[$(this).find("id_shippingtype").text()] = new Array();
							ShipmentTypes[$(this).find("id_shippingtype").text()]["title"]=$(this).find("title").text();
							ShipmentTypes[$(this).find("id_shippingtype").text()]["description"]=$(this).find("description").text();
							ShipmentTypes[$(this).find("id_shippingtype").text()]["ShippingServiceType"]=$(this).find("ShippingServiceType").text();
						}
					);
					ready_function();
				}
				else
				{
					show_status2(data);
				}
				
			}
		);	
		

	//PRELOAD PAYMENT TYPES
		PaymentTypes[0] = new Array();
		PaymentTypes[0]["title"]="";
		PaymentTypes[0]["description"]="";

		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "Get_Payment_Types", mode:"all" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("PaymentType").each(
						function()
						{
							PaymentTypes[$(this).find("id_paymenttype").text()] = new Array();
							PaymentTypes[$(this).find("id_paymenttype").text()]["title"]=$(this).find("title").text();
							PaymentTypes[$(this).find("id_paymenttype").text()]["description"]=$(this).find("description").text();
							PaymentTypes[$(this).find("id_paymenttype").text()]["PaymentMethod"]=$(this).find("PaymentMethod").text();
							PaymentTypes[$(this).find("id_paymenttype").text()]["method"]=$(this).find("method").text();
							PaymentTypes[$(this).find("id_paymenttype").text()]["ZLG"]=$(this).find("ZLG").text();


						}
					);
					ready_function();
				}
				else
				{
					show_status2(data);
				}
			}
		);	


	//PRELOAD SHOPS
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "Get_Shop_Shops", mode:"all" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("Shop_Shop").each(
						function()
						{
							Shop_Shops[$(this).find("id_shop").text()] = new Array();
							Shop_Shops[$(this).find("id_shop").text()]["title"]=$(this).find("title").text();
							Shop_Shops[$(this).find("id_shop").text()]["description"]=$(this).find("description").text();
							Shop_Shops[$(this).find("id_shop").text()]["shop_type"]=$(this).find("shop_type").text();
							Shop_Shops[$(this).find("id_shop").text()]["account_id"]=$(this).find("account_id").text();
							Shop_Shops[$(this).find("id_shop").text()]["parent_shop_id"]=$(this).find("parent_shop_id").text();
							Shop_Shops[$(this).find("id_shop").text()]["template"]=$(this).find("template").text();
						}
					);

					
					ready_function();
				}
				else
				{
					show_status2(data);
				}
			}
		);	
		
	
	//PRELOAD COUNTRIES
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "CountriesGet" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("shop_countries").each(
						function()
						{
							var country_id=$(this).find("id_country").text();
							Countries[country_id] = new Array();
							$(this).children().each(
								function()
								{
									var $tagname=this.tagName;
									Countries[country_id][$tagname]=$(this).text();
								}
															
							);
						}
					);
					ready_function();
				}
				else
				{
					show_status2(data);
				}
			}
		);	
		

	//PRELOAD ReturnLabel Parameter
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "Get_DHL_Retourlabel_Parameter" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("parameter").each(
						function()
						{
							DHL_RetourLabelParameter[$(this).find("country_code").text()] = new Array();
							DHL_RetourLabelParameter[$(this).find("country_code").text()]["dhl_parameter"]=$(this).find("dhl_parameter").text();
						}
					);
					ready_function();
				}
				else
				{
					show_status2(data);
				}
			}
		);	
		

	//PRELOAD SELLER List
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "SellersGet" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("seller").each(
						function()
						{
							Seller[$(this).find("id_user").text()] = $(this).find("firstname").text()+' '+$(this).find("lastname").text()
						}
					);
					ready_function();
				}
				else
				{
					show_status2(data);
				}
			}
		);	

		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "CurrenciesGet" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("shop_currencies").each(
						function()
						{
							var currency_code=$(this).find("currency_code").text();
							Currencies[currency_code] = new Array();
							$(this).children().each(
								function()
								{
									var $tagname=this.tagName;
									Currencies[currency_code][$tagname]=$(this).text();
								}
															
							);
						}
					);
					ready_function();
				}
				else
				{
					show_status2(data);
				}
			}
		);	

		
	});

	function ready_function()
	{
		preloadcount++;
		if (preloadcount==7) 
		{
			draw_navigation();
			//view_box();
			$("#tableview").html("<b>Zur Anzeige bitte Suchfilter nutzen</b>");
			wait_dialog_hide();
		}
	}


	$(function() {    
		$("#FILTER_Searchfield").bind("keypress", function(e) {
			if(e.keyCode==13){
				FILTER_Searchfield=$("#FILTER_Searchfield").val();
				view_box();
			}
		});
		
	//view_box();
		
	} );
	
	//FILTER SETZEN
	$(function() {
		$("#FILTER_Platform").val(FILTER_Platform);
		$("#FILTER_Status").val(FILTER_Status);
		$("#FILTER_SearchFor").val(FILTER_SearchFor);
		$("#FILTER_Searchfield").val(FILTER_Searchfield);
		$("#date_from").val(date_from);
		$("#date_to").val(date_to);
	});

$(document).mouseup(function (e)
{
    var container = $(".action_menu_options");

    if (container.has(e.target).length === 0)
    {
        container.hide();
    }
});

	function select_all_from_here(item_id, e)
	{
		if (!e) e = window.event;
		e.returnValue = false;
		if(e.preventDefault) e.preventDefault();
		if ((e.type && e.type == "contextmenu") || (e.button && e.button == 2) || (e.which && e.which == 3))
		{
			var state=false;
			var theForm = document.orderform;
			for (i=0; i<theForm.elements.length; i++)
			{
				if ( theForm.elements[i].value == item_id) state=true;
				if (theForm.elements[i].name=='orderID[]')
					theForm.elements[i].checked = state;
			}
		}
	}

	function checkAll()
	{

		var state = document.getElementById("selectall").checked;
		var theForm = document.orderform;
//		alert(theForm.elements.length);

		for (i=0; i<theForm.elements.length; i++)
		{
			if (theForm.elements[i].name=='orderID[]')
				theForm.elements[i].checked = state;
		}
	}
	
	function draw_navigation()
	{
		//Seite zurück
		if (ResultPage>1) 
		{
			var gotopage=ResultPage-1;
			$("#PageBack").html("<a href='javascript:goto_page("+gotopage+")'>Seite zurück</a>");
		}
		else 
		{
			$("#PageBack").html("<b>Seite zurück</b>");
		}
		//Seite Vor
		if (ResultPage<ResultPages) 
		{
			var gotopage=ResultPage+1;
			$("#PageForward").html("<a href='javascript:goto_page("+gotopage+")'>Seite vor</a>");
		}
		else 
		{
			$("#PageForward").html("<b>Seite vor</b>");
		}
		$("#navigation").show();
		
		//PageRange
		var tmp="";
		if (ResultRange==25) {tmp+="25";} else {tmp+="<a href='javascript:set_pageRange(25);'>25</a>";}
		tmp+=" | ";
		if (ResultRange==50) {tmp+="50";} else {tmp+="<a href='javascript:set_pageRange(50);'>50</a>";}
		tmp+=" | ";
		if (ResultRange==100) {tmp+="100";} else {tmp+="<a href='javascript:set_pageRange(100);'>100</a>";}
		tmp+=" | ";
		if (ResultRange==200) {tmp+="200";} else {tmp+="<a href='javascript:set_pageRange(200);'>200</a>";}
		
		$("#pageRange").html(tmp);
		
		//		ResultsBox
		var tmp="";
		tmp+='Angezeigte Treffer: ';
		tmp+=((ResultPage-1)*ResultRange)+1;
		var pagerange=ResultPage*ResultRange;
		if (pagerange>Results)
		{
			tmp+='-'+Results;
		}
		else
		{
			tmp+='-'+pagerange;
		}
		tmp+=' von '+Results;
		
		$("#ResultsBox").html(tmp);

		
		//PageSelect
		var tmp="";
		var i=0;
		var tmppage=0;
		
		if (ResultPage-3>1) tmp+="<a href='javascript:goto_page(1);'>1</a> ... ";
		for (i=0; i<5; i++)
		{
			tmppage=ResultPage-2+i;
		
			if (tmppage>0 && tmppage<(ResultPages-1)) 
			{
				if (tmppage==ResultPage)
				{
					tmp+=String(tmppage)+" ";
				}
				else 








				{
					tmp+="<a href='javascript:goto_page("+tmppage+");'>"+String(tmppage)+"</a> ";
				}
			}
		}
		if (tmppage+1<ResultPages) tmp+="... <a href='javascript:goto_page("+ResultPages+");'>"+String(ResultPages)+"</a> ";
		
		tmp+="<input type='text'  id='gotoPageInput' style='width:30px; height:12px' />";
		tmp+="<button onclick='goto_page_direct();'>Go</button>";
		
		$("#PageSelect").html(tmp);
	}


	function order_send($id_order, approved)
	{
		if(typeof approved == 'undefined') approved = "false";
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"idims", Action:"OrderSend", id_order:$id_order, approved: approved }, function($data)
		{
			wait_dialog_hide();
			try
			{
				$xml = $($.parseXML($data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					if($xml.find("Code").text()=="price_alert" && <?php echo $_SESSION["id_user"]; ?>==21371)
					{
						$("#price_alert_dialog").html($xml.find("longMsg").text());
						$("#price_alert_dialog").dialog
						({	buttons:
							[
								{ text: "<?php echo t("Trotzdem senden"); ?>", click: function() {order_send($id_order, "true"); $(this).dialog("close");} },
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
					else if($xml.find("Code").text()=="price_alert" && <?php echo $_SESSION["id_user"]; ?>!=21371)
					{
						$("#price_alert_dialog").html($xml.find("longMsg").text());
						$("#price_alert_dialog").dialog
						({	buttons:
							[
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
						//show_status2($data);
					}
					else
						show_status2($data);
					return;
				}
			}
			catch (err)
			{
				show_status2(err.message);
				return;
			}
			orders[$id_order]["status_id"]=2;
			var $now=new Date();
			$lastmod=$now.getTime()/1000;
			orders[$id_order]["status_date"]=$lastmod;
			orders[$id_order]["lastmod"]=$lastmod;
			orders[$id_order]["lastmod_user"]=<?php echo $_SESSION["id_user"]; ?>;
			update_table_cell($id_order, "order_state");
			update_table_cell($id_order, "vehicle_data");
			
			$("#send_order_dialog").dialog("close");
			
			show_status("Auftrag erfolgreich im IDIMS eingetragen.");
			return;
		});
	}


	function order_send2($id_order)
	{
		alert($id_order);
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"idims", Action:"OrderSend2", id_order:$id_order }, function($data)
		{
			show_status2($data);
			return;
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
			orders[$id_order]["status_id"]=2;
			var $now=new Date();
			$lastmod=$now.getTime()/1000;
			orders[$id_order]["status_date"]=$lastmod;
			orders[$id_order]["lastmod"]=$lastmod;
			orders[$id_order]["lastmod_user"]=<?php echo $_SESSION["id_user"]; ?>;
			update_table_cell($id_order, "order_state");
			update_table_cell($id_order, "vehicle_data");
			
			$("#send_order_dialog").dialog("close");
			
			show_status("Auftrag erfolgreich im IDIMS eingetragen.");
			return;
		});
	}


	function set_pageRange(range)
	{
		ResultRange=range;
		ResultPage=1;
		view_box();
	}
	
	function goto_page_direct()
	{
		goto_page($("#gotoPageInput").val());
	}
	
	function goto_page(page)
	{
		page=parseInt(page);
		ResultPage=page;
		view_box();
	}
	
	function toggle_hiddenrows(orderID)
	{
		
		if ($(".hiddenrow"+orderID).is(":visible"))
		{
			
			$(".hiddenrow"+orderID).slideUp(200);
			$("#row_toggle"+orderID).html('<a href="javascript:toggle_hiddenrows('+orderID+');">Weiter Artikel anzeigen</a>');
		}
		else 
		{
			$(".hiddenrow"+orderID).slideDown(200);
			$("#row_toggle"+orderID).html('<a href="javascript:toggle_hiddenrows('+orderID+');">Artikel verbergen</a>');
		}
	}
	
	function set_FILTER_Platform()
	{
		FILTER_Platform=$("#FILTER_Platform").val();
	}

	function set_FILTER_Status()
	{
		FILTER_Status=$("#FILTER_Status").val();
	}

	function set_FILTER_Country()
	{
		FILTER_Country=$("#FILTER_Country").val();
	}

	function set_FILTER_SearchFor()
	{
		FILTER_SearchFor=$("#FILTER_SearchFor").val();
	}

//**************************************************************************************************************************
	function sortTable(column)
	{
		//chnage order direction
		if (OrderBy==column)
		{
			if (OrderDirection=="up")
			{
				OrderDirection="down";
			}
			else
			{
				OrderDirection="up";
			}
		}
		OrderBy=column;
		
		view_box();
		
	}

//#########################################################################################################################

	function show_shipment_label(Label_ID)
	{
		Label_ID+=''; // -> Konvertierung zu string
		var path='http://www.mapco.de/files/';
		path+=Label_ID.substr(0,4)+'/'+Label_ID	+'.pdf';
		
		tmp = window.open(path, "_blank");
		
	}
	
	function update_view(orderid)
	{
		$_order= new Array();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_orders_list_test", mode:"single", order_id:orderid},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("Order").each(
						function()
						{
							$(this).children().each(
								function()
								{
									var $tagname=this.tagName;
								
								// 2nd Level	
								
									if (this.hasChildNodes())
									{
										alert($tagname);
										$_order[$tagname] = new Array();
										
										$(this).children().each(
											function ()
											{
												var $tagname2=this.tagName;
												$_order[$tagname][$tagname2] = $(this).text();
											}
										);
									}
									else
									{
										
										$_order[$tagname]=$(this).text();
									}
								}
															
							);
						}
					);
					
				show_status2(print_r($_order));
				//NEUE DATEN in OrderArray einfügen
				orders[orderid]=$_order;
				update_table_cell(orderid, "order_buyer");
					
				}
				else
				{
					show_status2(data);
				}
				
			}
		);

	}

	function update_table_cell(orderid, $function)
	{
		switch ($function) {
		/*	
		  case "action_menu":
			$("#action_menu"+orderid).html(draw_actions_menu(orderid));
			break;
		*/
		  case "platform_data":
			$("#platform_data"+orderid).html(show_platform_data(orderid));
			break;
		  case "order_buyer":
			$("#order_buyer"+orderid).html(show_order_buyer(orderid));
			break;
		  case "order_items":
			$("#order_items"+orderid).html(show_order_items(orderid));
			break;
		  case "order_sum":
			$("#order_sum"+orderid).html(show_order_sum(orderid));
			break;
		  case "order_state":
			$("#order_state"+orderid).html(show_order_state(orderid));
			break;
		  case "vehicle_data":
			$("#vehicle_data"+orderid).html(show_vehicle_data(orderid));
			break;

		}
		
		$("#action_menu"+orderid).html(draw_actions_menu(orderid));

	}

	function draw_actions_menu(orderid)
	{
		var menu='';
		if (orders[orderid]["AUF_ID"]==0)
		{
			menu+='<a href="javascript:order_update_dialog('+orderid+', \'IDIMS\');">IDIMS</a><br /><br />';
			if( <?php echo $_SESSION["id_user"] ?>==21371)
			{
				menu+='<a href="javascript:order_send2('+orderid+', \'IDIMS\');">IDIMS2</a><br /><br />';

			}
		}
		else if (orders[orderid]["PaymentTypeID"]==3 && orders[orderid]["shipping_type_id"]!=15)
		{
			menu+='<b>Fehler:<br /><small>ungültiger Versand</small><br /><br />';
		}
		else
		{
			menu+='<small>IDIMS-Auftrag<br />bereits erfasst</small><br /><br />';
		}

		menu+='<a href="javascript:show_actions_menu('+orderid+');">Aktionen</a>';
		menu+='<div class="action_menu_options" id="action_menu_options'+orderid+'" style="position:absolute; display:none; background-color:#fff; z-index:100; padding:3px; border:1px solid #999;">';
		menu+='<ul>';
		
		if (orders[orderid]["combined_with"]>0)
		{
			menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:unset_combined_order('+orders[orderid]["combined_with"]+', '+orderid+');">kombinierte Order aufheben</a></li>';
		}
		menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:set_order_payment('+orderid+');">Zahlung bearbeiten</a></li>';
//		menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:set_order_state('+orderid+', 2);">Auftrag geschrieben</a></li>';
	
		//BESTELLUNG NACH IDIMS NICHT MEHR BEARBEITBAR
		if (orders[orderid]["AUF_ID"]==0 || <?php echo $_SESSION["userrole_id"]; ?>==1)
		{
			menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_update_dialog('+orderid+', \'update\');">Bestellung bearbeiten</a></li>';
		}
		else
		{
			menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_update_dialog('+orderid+', \'view\');">Bestellung bearbeiten</a></li>';
		}
		//menu+='	<li style="margin:5px; margin-left:-22px;">Kunde Kontaktieren</li>';
		menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:set_order_note('+orderid+');">Notiz hinzufügen / bearbeiten</a></li>';
		//menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:show_order_details('+orderid+', '+orders["customer_id"]+');">Bestellungsdetails ansehen</a></li>';
		if (orders[orderid]["DHL_RetourLabelParameter"]!='')
		{
			var dhl_parameter=orders[orderid]["DHL_RetourLabelParameter"];
			menu+='	<li style="margin:3px; margin-left:-22px;"><a href="javascript:send_DHLretourlabel('+orderid+', \''+dhl_parameter+'\');">DHL-Retourlabel senden</a></li>';
		}
	//	menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_update_dialog('+orderid+', \'return\');">Rückgabe / Umtausch bearbeiten</a></li>';
		menu+='</ul>';
		menu+='</div>';
		
		menu+='<br /><br /><a href="javascript:show_order_events('+orderid+');">Bestellverlauf</a>';
		menu+='<div class="action_menu_options" id="order_events_'+orderid+'" style="position:absolute; display:none; background-color:#fee; z-index:100; padding:3px; border:1px solid #999; height:500px; overflow:auto">blablabla</div>';
				
		return menu;
		
	}
	
	function show_order_events(orderid)
	{
		if ($("#order_events_"+orderid).is(":visible"))
		{
			$("#order_events_"+orderid).hide();
		}
		else
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "OrderEventsGet", order_id:orderid},
			function(data)
			{
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					var html='';
					html+='<span style="background-color:#999; float:right"><a href="javascript:show_order_events('+orderid+');"><b>[ X ]</a></b></span><br/ >';
					
					html+='<table>';
					html+='<tr>';
					html+='	<th>Zeit</th>';
					html+='	<th>Vorgang</th>';
					html+='	<th>Ausgeführt durch</th>';
					html+='</tr>';
					
					$xml.find("event").each(
					function()
					{
						html+='<tr>';
						html+='	<td>'+convert_time_from_timestamp($(this).find("firstmod").text(), "complete")+'</td>';
						html+='	<td>'+$(this).find("eventtitle").text()+'</td>';
						html+='	<td>'+$(this).find("username").text()+'</td>';
						html+='</tr>';
					});
					html+='</table>';
					
					$("#order_events_"+orderid).html(html);
				}
				else
				{
					show_status2(data);
				}

			});

			
			
			
			$(".action_menu_options").hide();
			var pos = $("#action_menu"+orderid).position();
			$("#order_events_"+orderid).css("left",pos.left-500);
			$("#order_events_"+orderid).css("top",pos.top-0);
			$("#order_events_"+orderid).show();
			
		}
	}
	
	function show_actions_menu(orderid)
	{
		if ($("#action_menu_options"+orderid).is(":visible"))
		{
			$("#action_menu_options"+orderid).hide();
		}
		else
		{
			$(".action_menu_options").hide();
			var pos = $("#action_menu"+orderid).position();
			$("#action_menu_options"+orderid).css("left",pos.left-100);
			$("#action_menu_options"+orderid).css("top",pos.top-0);
			$("#action_menu_options"+orderid).show();
			
		}
	}
	
	
	function show_platform_data(orderid)
	{
		var html='';
		
		html+=Shop_Shops[orders[orderid]["shop_id"]]["title"];

		if (!orders[orderid]["buyerUserID"]=="")
		{
			html+='<br /><b>'+orders[orderid]["buyerUserID"]+'</b>';
		}
		return html;
	}
	
	
	function show_order_buyer(orderid)
	{
		var html='';

		if (orders[orderid]["bill_street"]!="")
		{
			if (orders[orderid]["bill_company"]!="")
			{
				 html+=orders[orderid]["bill_company"]+'<br />';
			}
			 
			html+=orders[orderid]["bill_firstname"]+' '+orders[orderid]["bill_lastname"]+'<br />';
			
			if (orders[orderid]["bill_additional"]!="")
			{
				html+='<small>'+orders[orderid]["bill_additional"]+'</small><br />';
			}
			
			html+='<small>'+orders[orderid]["bill_street"]+' '+orders[orderid]["bill_number"]+'</small><br />';
			
			html+='<small>'+orders[orderid]["bill_zip"]+' '+orders[orderid]["bill_city"]+'</small><br />';
			html+='<small>'+orders[orderid]["bill_country"]+'</small><br />';
		}

		if (orders[orderid]["usermail"]=="Invalid Request" || orders[orderid]["usermail"]=="")
		{
			if(orders[orderid]["buyerUserID"]=="")
			{
				orders[orderid]["mailable"]=false;
			}
		}
		else 
		{
			orders[orderid]["mailable"]=true;
			html+='<b>'+orders[orderid]["usermail"]+'</b>';
		}

		return html;		
	}
	
	function show_order_items(orderid)
	{
		var html='';
							
		html+='<table style="border:0; width:100%; margin:0px;">';
		if (orders[orderid]["shipping_type_id"]==2 || orders[orderid]["shipping_type_id"]==7)
		{
			html+='<tr><td colspan="3" style="background-color:#f00; border:0;"><b>EXPRESS-VERSAND</b></td></tr>';
		}
		
		var rowcount=0;
		for (var i=0; i<orders[orderid]["Items"].length; i++)
		{
			if (rowcount<4)
			{
				html+='<tr>';
			}
			else 
			{
				html+='<tr class="hiddenrow'+orderid+'" style="display:none">';
			}
			html+='	<td style="width:7%; border:0;">'+orders[orderid]["Items"][i]["OrderItemAmount"]+'x</td>';
			html+=' <td style="width:15%; border:0;">'+orders[orderid]["Items"][i]["OrderItemMPN"]+'</td>';
			if (Shop_Shops[orders[orderid]["shop_id"]]["shop_type"]==2)
			{			//http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&rd=1&item=380649865926
			
				html+='	<td style="width:60%; border:0;">';
				html+=' <a href="http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&rd=1&item='+orders[orderid]["Items"][i]["OrderItemItemID"]+'" target="_blank" >'+orders[orderid]["Items"][i]["OrderItemDesc"]+'</a>'
				html+='	</td>';
			}
			else
			{
				html+='	<td style="width:60%; border:0;">'+orders[orderid]["Items"][i]["OrderItemDesc"]+'</td>';
			}
			html+='	<td style="width:18%; border:0; text-align:right;">'+orders[orderid]["Items"][i]["OrderItemTotal"]+' €</td>';
			html+='</tr>';
			
			rowcount++;
		}
		//ROW TOGGLE
		if (rowcount>4) 
		{
			html+='<tr><td style="border:0"></td>';
			html+='<td style="border:0"></td>';
			html+='<td id="row_toggle'+orderid+'" style="border:0"><a href="javascript:toggle_hiddenrows('+orderid+');">Weiter Artikel anzeigen</a></td>';
			html+='<td style="border:0"></td></tr>';
		}

		html+='	</table>';
		return html;
	}

	function show_order_sum(orderid)	
	{
		var html='';
		var shippingtype='';
		if (orders[orderid]["shipping_type_id"]==0)
		{
			shippingtype='<span style="background-color:green;"><b></b></span>';
		}

		if (orders[orderid]["shipping_type_id"]==1)
		{
			shippingtype='<span style="background-color:yellow;"><b>DHL</b></span>';
		}
		if (orders[orderid]["shipping_type_id"]==2)
		{
			shippingtype='<span style="background-color:orange;"><b>EXP</b></span>';
		}
		if (orders[orderid]["shipping_type_id"]==3)
		{
			shippingtype='<span style="background-color:red;"><b>DPD</b></span>';
		}
		if (orders[orderid]["shipping_type_id"]==4)
		{
			shippingtype='<span style="background-color:red;"><b>TNT</b></span>';
		}
		if (orders[orderid]["shipping_type_id"]==5)
		{
			shippingtype='<span style="background-color:yellow;"><b>DHL Int</b></span>';
		}
		if (orders[orderid]["shipping_type_id"]==6)
		{
			shippingtype='<span style="background-color:red;"><b>DPD Int</b></span>';
		}
		if (orders[orderid]["shipping_type_id"]==7)
		{
			shippingtype='<span style="background-color:orange;"><b>EXP Int</b></span>';
		}
		if (orders[orderid]["shipping_type_id"]==8)
		{
			shippingtype='<span style="background-color:green;"><b>Abholung</b></span>';
		}
		if (orders[orderid]["shipping_type_id"]==15)
		{
			shippingtype='<span style="background-color:orange;"><b>Nachnahme</b></span>';
		}

		
		html+='	<div style="text-align:right">';
			html+=		orders[orderid]["OrderItemsTotal"]+' €<br />';
			html+=		shippingtype+' '+orders[orderid]["OrderShippingCosts"]+' €<br />';
			html+='	<b>'+orders[orderid]["OrderTotal"]+' €</b>';
		html+='	</div>';
		
		return html;

	}
	
	function show_order_state(orderid)
	{
		var html='';
		//BESTELLUNG

		html+='	<div style="width:100%; float:left; margin:3px;">';
		if (orders[orderid]["status_id"]!=4)
		{
			html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Order.png" alt="Bestellt am:" title="Bestellt am:" />';
			html+=convert_time_from_timestamp(orders[orderid]["orderDate"], "complete");
		}
		else
		{
			html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Order_cancelled.png" alt="Bestellung abgebrochen am:" title="Bestellung abgebrochen am:" />';
			html+=convert_time_from_timestamp(orders[orderid]["status_date"], "complete");
		}
		html+='	</div>';
		
		//ZAHLUNG +++++++++++++++++++++
		html+='	<div id="payment_state'+orderid+'" style="width:100%; float:left; margin:3px;">';
		if (orders[orderid]["PaymentTypeID"]!=0 && orders[orderid]["PaymentDate"]!=0)
		{
			if (orders[orderid]["Payments_TransactionState"]=="Refunded")
			{
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Payment_Refunded.png" alt="Zahlung erstattet: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Zahlung erstattet: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="set_order_payment('+orderid+');" />';
				html+=convert_time_from_timestamp(orders[orderid]["PaymentDate"], "complete");
			}
			else if (orders[orderid]["Payments_TransactionState"]=="Pending")
			{
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Payment_Alert.png" alt="PayPal Zahlung noch nicht gebucht: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="PayPal Zahlung noch nicht gebucht: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="set_order_payment('+orderid+');" />';
				html+=convert_time_from_timestamp(orders[orderid]["PaymentDate"], "complete");
			}
			else 
			//if (orders[orderid]["Payments_TransactionState"]=="Completed" || orders[orderid]["Payments_TransactionState"]=="OK")
			{
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Payment_OK.png" alt="Bezahlt per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Bezahlt per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="set_order_payment('+orderid+');" />';
				html+=convert_time_from_timestamp(orders[orderid]["PaymentDate"], "complete");
			}
			
		}
		else if (orders[orderid]["PaymentTypeID"]==3)
		{
			html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Payment_OK.png" alt="Zahlung per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Zahlung per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="set_order_payment('+orderid+');" />';
			html+='<b>Nachnahme</b>';
		}
		else if (orders[orderid]["PaymentTypeID"]==1)
		{
			html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Payment_OK.png" alt="Zahlung per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Zahlung per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="set_order_payment('+orderid+');" />';
			html+='<b>Rechnung</b>';
		}
		else if (orders[orderid]["PaymentTypeID"]==2)
		{
			html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Payment_open.png" alt="Zahlung per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Zahlung per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="set_order_payment('+orderid+');" />';
			html+='<b>Vorkasse</b>';
		}
		else if (orders[orderid]["PaymentTypeID"]==7)
		{
			html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Payment_open.png" alt="Zahlung per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Zahlung per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="set_order_payment('+orderid+');" />';
			html+='<b>Barzahlung</b>';
		}

		else
		{
			html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Payment_open.png" alt="Zahlung ausstehend\nDoppelklick: Zahlung bearbeiten" title="Zahlung ausstehend\nDoppelklick: Zahlung bearbeiten"  ondblclick="set_order_payment('+orderid+');"/>';
		}
		html+='	</div>';
		
		//SHIPMENT ++++++++++++++++++++++
			
		html+='	<div id="shipment_state'+orderid+'" style="width:100%; float:left; margin:3px;">';
		
			//if (orders[orderid]["status_id"]=="3" || orders[orderid]["shipping_type_id"]==6 || orders[orderid]["shipping_type_id"]==3)
			if (orders[orderid]["status_id"]=="3")
			{
				html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Shipment_OK.png" alt="Versandet am:" title="Versendet am:" /></span>';
				html+=		'<span>'+convert_time_from_timestamp(orders[orderid]["status_date"], "complete")+'</span>';
			}
			else if(orders[orderid]["status_id"]=="2")
			{
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Shipment_assigned.png" alt="Auftrag geschrieben" title="Auftrag geschrieben" />';	
				html+=convert_time_from_timestamp(orders[orderid]["status_date"], "complete");
			}
			else
			{
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Shipment.png" alt="noch nicht versendet\nDoppelklick: Auftrag geschrieben markieren" title="noch nicht versendet\nDoppelklick: Auftrag geschrieben markieren" ondblclick="set_order_state('+orderid+', 2);" />';
			}
			html+='	</div>';
			
			//SHIPPING NUMBER / LABEL INFO
			if (orders[orderid]["shipping_number"]!="")
			{
				html+='<div id="shipment_info'+orderid+' style="width:100%; float:left; margin:3px;">';
				
				if (orders[orderid]["shipping_type_id"]==6 || orders[orderid]["shipping_type_id"]==3)
				{
					
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Shipment_info.png" alt="DPD-Tracking ID:" title="DPD-Tracking ID:" />';
					
					html+='		<small><a href="https://tracking.dpd.de/cgi-bin/delistrack?pknr='+orders[orderid]["shipping_number"]+'" target="_blank">'+orders[orderid]["shipping_number"]+'</a></small>';
				}
				else
				{
					
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Shipment_info.png" alt="DHL-Tracking ID:\nDoppelklick: DHL Label anzeigen" title="DHL-Tracking ID:\nDoppelklick: DHL Label anzeigen" / ondblclick="show_shipment_label('+orders[orderid]["shipping_label_file_id"]+');" />';

					html+='		<small><a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='+orders[orderid]["shipping_number"]+'&rfn=&extendedSearch=true" target="_blank">'+orders[orderid]["shipping_number"]+'</a></small>';
				}
				html+='</div>';
			}

		
			if (orders[orderid]["RetourLabelID"]!="")
			{
				html+='	<div id="shipment_retourlabel'+orderid+'" style="width:100%; float:left; margin:3px;">';
				html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Shipment_Returned.png" alt="Retourlabel versendet" title="Retourlabel versendet" /></span>';	
				html+=		'<span>'+convert_time_from_timestamp(orders[orderid]["RetourLabelTimestamp"], "complete");
				html+='		<br /><small><a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='+orders[orderid]["RetourLabelID"]+'" target="_blank">'+orders[orderid]["RetourLabelID"]+'</a></small></span>';
				html+='	</div>';
			}

		return html;
	}
	
	function show_vehicle_data(orderid)
	{
		
		//Get Vehicles
		var vehicle_count=0;
		for (i=0;i<orders[orderid]["Items"].length; i++)
		{
			if (orders[orderid]["Items"][i]["OrderItemCustomerVehicleID"]!=0) vehicle_count++;
		}
				
		var checked_count=0;
		var checked_false=0;
		for (i=0;i<orders[orderid]["Items"].length; i++)
		{
			if (orders[orderid]["Items"][i]["OrderItemChecked"]!=0) checked_count++;
			if (orders[orderid]["Items"][i]["OrderItemChecked"]==2) checked_false++;
		}

		var html='';
	
		html+='	<div style="width:100%; float:left">';
		//if (orders[orderid]["itemvehiclecount"]==0)
		if (vehicle_count==0)
		{
			//if (orders[orderid]["itemcheckcount"]==orders[orderid]["itemcount"])
			if (checked_count==orders[orderid]["itemcount"] && checked_false==0)
			{
				
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_green.png" alt="Artikel gecheckt\nKLICK: Bearbeiten" title="Artikel gecheckt\nKLICK: Bearbeiten" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
				html+='		<b>'+vehicle_count+'</b><small> Fahrzeugdaten für </small><b>'+orders[orderid]["itemcount"]+'</b> <small>Artikel</small>';
			}
			if (checked_count<orders[orderid]["itemcount"] && checked_false==0)
			{
				//VERSENDEN, WEIL keine FzDaten gesendet
				if (orders[orderid]["fz_fin_mail_count"]>=3)
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_blue.png" alt="fehlende Fahrzeugdaten\n KLICK: Fahrzeugdaten eingeben" title="fehlende Fahrzeugdaten\n KLICK: Fahrzeugdaten eingeben" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
					html+='<b>'+orders[orderid]["fz_fin_mail_count"]+'</b>';
					html+='	<small>Letzte Anfrage: '+convert_time_from_timestamp(orders[orderid]["fz_fin_mail_lastsent"], "complete")+'</small>';
				}
				else
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car.png" alt="fehlende Fahrzeugdaten\n KLICK: Fahrzeugdaten eingeben" title="fehlende Fahrzeugdaten\n KLICK: Fahrzeugdaten eingeben" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
					html+='<b>'+orders[orderid]["fz_fin_mail_count"]+'</b>';
					if (orders[orderid]["fz_fin_mail_lastsent"]==0)
					{
						html+='	<small>Letzte Anfrage: - - - - - </small>';
					}
					else
					{
						html+='	<small>Letzte Anfrage: '+convert_time_from_timestamp(orders[orderid]["fz_fin_mail_lastsent"], "complete")+'</small>';
					}
				}
			}
			if (checked_false>0)
			{
				
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_red.png" alt="Bestellung hat fehlerhaften Artikel\nKLICK: Bearbeiten" title="Bestellung hat fehlerhaften Artikel\nKLICK: Bearbeiten" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
				html+='		<b>'+vehicle_count+'</b><small> Fahrzeugdaten für </small><b>'+orders[orderid]["itemcount"]+'</b> <small>Artikel</small>';
			}

		}
		else
		{
			if (checked_false==0)
			{
				if (checked_count==orders[orderid]["itemcount"])
				{


					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_green.png" alt="Fahrzeugdatenvorhanden +Artikel gecheckt\nKLICK: Bearbeiten" title="Fahrzeugdatenvorhanden +Artikel gecheckt\nKLICK: Bearbeiten" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
					html+='		<b>'+orders[orderid]["itemvehiclecount"]+'</b><small> Fahrzeugdaten für </small><b>'+orders[orderid]["itemcount"]+'</b> <small>Artikel</small>';
				}
				else
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_yellow.png" alt="Fahrzeugdatenvorhanden +Artikel nicht gecheckt\nKLICK: bearbeiten" title="Fahrzeugdatenvorhanden +Artikel nicht gecheckt\nKLICK: bearbeiten" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
					html+='		<b>'+orders[orderid]["itemvehiclecount"]+'</b><small> Fahrzeugdaten für </small><b>'+orders[orderid]["itemcount"]+'</b> <small>Artikel</small>';
					
				}
			}
			else
			{
				
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_red.png" alt="Bestellung hat fehlerhaften Artikel\nKLICK: Bearbeiten" title="Bestellung hat fehlerhaften Artikel\nKLICK: Bearbeiten" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
				html+='		<b>'+vehicle_count+'</b><small> Fahrzeugdaten für </small><b>'+orders[orderid]["itemcount"]+'</b> <small>Artikel</small>';
			}
		}
		
		//LASTMOD USER INFO
		html+='<br /><br />';
		if (orders[orderid]["lastmod_user"]==1)
		{
			html+='<small>letzte Bearbeitung: <b>System</b> am: '+convert_time_from_timestamp(orders[orderid]["lastmod"], "complete")+'</small>';
		}
		else
		{
			if (Seller[orders[orderid]["lastmod_user"]] === undefined)
			{		
				html+='<small>letzte Bearbeitung: <b>UserID '+orders[orderid]["lastmod_user"]+'</b> am: '+convert_time_from_timestamp(orders[orderid]["lastmod"], "complete")+'</small>';
			}
			else
			{
				html+='<small>letzte Bearbeitung: <b>'+Seller[orders[orderid]["lastmod_user"]]+'</b> am: '+convert_time_from_timestamp(orders[orderid]["lastmod"], "complete")+'</small>';
			}
		}
		html+='	</div>';
		
		return html;
	}
	
	

	function view_box()
	{
		FILTER_Searchfield=$("#FILTER_Searchfield").val();
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_orders_list", mode:"list", FILTER_Platform:FILTER_Platform, FILTER_Status:FILTER_Status, FILTER_Country:FILTER_Country, FILTER_SearchFor:FILTER_SearchFor, FILTER_Searchfield:FILTER_Searchfield, OrderBy:OrderBy, OrderDirection:OrderDirection, ResultPage:ResultPage,ResultRange:ResultRange, date_from:date_from, date_to:date_to},
			function(data)
			{
			//show_status2(data);
				ResultPage=1;
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					
					//ResultPages
					Results=parseInt($xml.find("Entries").text());
					var rest=Results%ResultRange;
					ResultPages=(Results-rest)/ResultRange;
					if (rest>0) ResultPages++;
					
					//Array leeren
					orders.length = 0;	
									
					var table ='<form name="orderform">';
					table+='<table class="hover" style="table-layout:fixed">';
					table+='<tr>';
					table+='	<th style="width:20px;"><input id="selectall" type="checkbox" onclick="checkAll();" /></th>';
					table+='	<th style="width:50px; cursor:pointer" onclick="sortTable(\'id_order\');">OrderID</th>';
					table+='	<th style="width:100px;">Plattform</th>';
					table+='	<th style="width:300px; cursor:pointer" onclick="sortTable(\'bill_lastname\');">Käufer</th>';
					table+='	<th style="width:350px;">Artikel</th>';
					table+='	<th style="width:90px;">Gesamt</th>';
					table+='	<th style="width:180px; cursor:pointer" onclick="sortTable(\'firstmod\');">Orderstatus</th>';
					table+='	<th style="width:180px;">Fahrzeugdaten-Anfrage</th>';
				//	table+='	<th style="width:90px;"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/mail_add.png" alt=Fahrzeugdatenanfragen senden" title="Fahrzeugdatenanfragen senden" onclick="send_fin_mail_checked_dialog();" /></th>';
					table+='	<th style="width:90px;"></th>';
					table+='</tr>';
					
					$xml.find("Order").each(
						function()
						{
							var OrderID=$(this).find("id_order").text();
							orders[OrderID] = new Array();
							
							orders[OrderID]["AUF_ID"]=$(this).find("AUF_ID").text();
//orders[OrderID]["platformID"]=$(this).find("shop_id").text();				
							orders[OrderID]["shop_id"]=$(this).find("shop_id").text();
							orders[OrderID]["customer_id"]=$(this).find("customer_id").text();

							orders[OrderID]["entry_pos"]=$(this).find("entry_pos").text();
							orders[OrderID]["VPN"]=$(this).find("VPN").text();

							//BUYER
							orders[OrderID]["buyerUserID"]=$(this).find("buyerUserID").text();
							orders[OrderID]["bill_company"]=$(this).find("bill_company").text();
							orders[OrderID]["bill_firstname"]=$(this).find("bill_firstname").text();
							orders[OrderID]["bill_lastname"]=$(this).find("bill_lastname").text();
							orders[OrderID]["bill_zip"]=$(this).find("bill_zip").text();
							orders[OrderID]["bill_city"]=$(this).find("bill_city").text();
							orders[OrderID]["bill_street"]=$(this).find("bill_street").text();
							orders[OrderID]["bill_number"]=$(this).find("bill_number").text();
							orders[OrderID]["bill_additional"]=$(this).find("bill_additional").text();
							orders[OrderID]["bill_country"]=$(this).find("bill_country").text();
							orders[OrderID]["bill_country_code"]=$(this).find("bill_country_code").text();
							orders[OrderID]["usermail"]=$(this).find("usermail").text();

							//SUMMEN
							orders[OrderID]["OrderItemsTotal"]=$(this).find("OrderItemsTotal").text();
							orders[OrderID]["OrderShippingCosts"]=$(this).find("OrderShippingCosts").text();
							orders[OrderID]["OrderTotal"]=$(this).find("OrderTotal").text();
							
							//SHIPPING
							orders[OrderID]["shipping_type_id"]=$(this).find("shipping_type_id").text();
							orders[OrderID]["shipping_number"]=$(this).find("shipping_number").text();
							orders[OrderID]["shipping_label_file_id"]=$(this).find("shipping_label_file_id").text();
							
							orders[OrderID]["RetourLabelID"]=$(this).find("RetourLabelID").text();
							orders[OrderID]["RetourLabelTimestamp"]=$(this).find("RetourLabelTimestamp").text();
							
							if (DHL_RetourLabelParameter[$(this).find("bill_country_code").text()] === undefined)
							{
								orders[OrderID]["DHL_RetourLabelParameter"]='';
							}
							else
							{
								orders[OrderID]["DHL_RetourLabelParameter"]=DHL_RetourLabelParameter[$(this).find("bill_country_code").text()]["dhl_parameter"];
							}
							//BESTELLDATUM
							orders[OrderID]["orderDate"]=$(this).find("orderDate").text();
							//ZAHLUNG +++++++++++++++++++++
							orders[OrderID]["PaymentDate"]=$(this).find("PaymentDate").text();
							orders[OrderID]["PaymentType"]=$(this).find("PaymentType").text();
							orders[OrderID]["PaymentTypeID"]=$(this).find("PaymentTypeID").text();
							orders[OrderID]["Payments_TransactionState"]=$(this).find("Payments_TransactionState").text();
							//SHIPMENT ++++++++++++++++++++++
							orders[OrderID]["status_id"]=$(this).find("status_id").text();
							orders[OrderID]["status_date"]=$(this).find("status_date").text();

							orders[OrderID]["fz_fin_mail_count"]=$(this).find("fz_fin_mail_count").text();
							orders[OrderID]["fz_fin_mail_lastsent"]=$(this).find("fz_fin_mail_lastsent").text();
							
							orders[OrderID]["lastmod"]=$(this).find("lastmod").text();
							orders[OrderID]["lastmod_user"]=$(this).find("lastmod_user").text();
							
							orders[OrderID]["order_note"]=$(this).find("order_note").text();
							orders[OrderID]["PayPal_BuyerNote"]=$(this).find("PayPal_BuyerNote").text();
							
							orders[OrderID]["combined_with"]=$(this).find("combined_with").text();
							

							var i=0;
							orders[OrderID]["itemcount"]=0;
							orders[OrderID]["itemvehiclecount"]=0;

							orders[OrderID]["itemcheckcount"]=0;

							orders[OrderID]["Items"]=new Array();
							$(this).find("Item").each(
							function()
							{
								
								orders[OrderID]["Items"][i]=new Array();
								orders[OrderID]["Items"][i]["OrderItemID"]=$(this).find("OrderItemID").text();
								orders[OrderID]["Items"][i]["OrderItemItemID"]=$(this).find("OrderItemItemID").text();
								orders[OrderID]["Items"][i]["OrderItemMPN"]=$(this).find("OrderItemMPN").text();
								orders[OrderID]["Items"][i]["OrderItemAmount"]=$(this).find("OrderItemAmount").text();
								orders[OrderID]["Items"][i]["OrderItemDesc"]=$(this).find("OrderItemDesc").text();
								orders[OrderID]["Items"][i]["OrderItemTotal"]=$(this).find("OrderItemTotal").text();
								orders[OrderID]["Items"][i]["OrderItemChecked"]=$(this).find("OrderItemChecked").text();
								orders[OrderID]["Items"][i]["OrderItemCustomerVehicleID"]=$(this).find("OrderItemCustomerVehicleID").text();
								
								orders[OrderID]["itemcount"]++;
								if ($(this).find("OrderItemCustomerVehicleID").text()!=0) orders[OrderID]["itemvehiclecount"]++;
								if ($(this).find("OrderItemckecked_by_user").text()!=0) orders[OrderID]["itemcheckcount"]++;

								i++;
							}
							);

							//DRAW TABLE
							//combined order
							if (orders[OrderID]["combined_with"]>0)
							{
								table+='<tr id="ordernoterow'+OrderID+'" style="background-color:#99f; border-top-color:#000; border-top-width:2px; border-top-style:solid">';
								table+='	<td></td>';
								table+='	<td colspan="7">';
								table+='<b> kombinierte Bestellung </b>';
								table+='	</td>';
								table+='	<td></td>';
								table+='</tr>';	
							}
							if (orders[OrderID]["PaymentTypeID"]==3 && orders[OrderID]["shipping_type_id"]!=15)
							{
								table+='<tr id="ordernoterow2'+OrderID+'" style="background-color:#f99; border-top-color:#000; border-top-width:2px; border-top-style:solid">';
								table+='	<td></td>';
								table+='	<td colspan="7">';
								table+='<b> Zahlart: Nachnahme -> abweichende Versandmethode</b>';
								table+='	</td>';
								table+='	<td></td>';
								table+='</tr>';	
							}
							
							table+='<tr>';
							table+='	<td><input name="orderID[]" type="checkbox" value="'+OrderID+'" onmousedown="select_all_from_here(this.value);" /></td>';
							table+='	<td><b>'+orders[OrderID]["entry_pos"]+'</b><br><small>'+orders[OrderID]["VPN"]+'</small></td>';
							
							table+='	<td id="platform_data'+OrderID+'">'
							table+=show_platform_data(OrderID);
							table+='	</td>';
							

							table+='	<td id="order_buyer'+OrderID+'">';
							table+=show_order_buyer(OrderID);
							table+='	</td>';
							
							//ORDERITEMS						
							table+='	<td id="order_items'+OrderID+'" style="padding:0px">';
							table+= show_order_items(OrderID);
							table+='	</td>';
	
							//SUMMEN
							table+='	<td id="sorder_sum'+OrderID+'">'
							table+= show_order_sum(OrderID);
							table+='	</td>';

							//ORDERSTATUS
							table+='	<td id="order_state'+OrderID+'">';
							table+= show_order_state(OrderID);
							table+='	</td>';
							
							//FAHRZEUGDATEN
							table+='	<td id="vehicle_data'+OrderID+'">';
							table+= show_vehicle_data(OrderID);
							table+='	</td>';
							
							table+='	<td>';
							table+='	<div id="action_menu'+OrderID+'">';
							table+= draw_actions_menu(OrderID);
							//table+='		<a href="javascript:show_actions_menu('+OrderID+');">Aktionen</a>';
							table+='	</div>';
							

							table+='</td>';
							table+='</tr>';

							//PAYPAL NOTES
							if (orders[OrderID]["PayPal_BuyerNote"].length>0)
							{
								table+='<tr id="PayPal_BuyerNote'+OrderID+'" style="background-color:#FC7;">';

							}
							else
							{
								table+='<tr id="PayPal_BuyerNote'+OrderID+'" style="background-color:#FC7; display:none">';
							}

							table+='	<td></td>';
							table+='	<td colspan="7">';
							table+='		<span id="PayPalnote'+OrderID+'" style="font-size:8pt"><b>PayPal-Nachricht:</b>&nbsp;'+orders[OrderID]["PayPal_BuyerNote"]+'</span>';
							table+='	</td>';
							table+='	<td></td>';
							table+='</tr>';	


							//NOTES
						
							if (orders[OrderID]["order_note"].length>0)
							{
								table+='<tr id="ordernoterow'+OrderID+'" style="background-color:#FE7;">';
							}
							else
							{
								table+='<tr id="ordernoterow'+OrderID+'" style="background-color:#FE7; display:none">';
							}

							table+='	<td></td>';
							table+='	<td colspan="7">';

							table+='		<span id="ordernote'+OrderID+'" style="font-size:8pt">'+orders[OrderID]["order_note"]+'</span>';
							table+='		&nbsp;<a href="javascript:set_order_note('+OrderID+');"><small>[bearbeiten]</small></a>';
							table+='	</td>';
							table+='	<td></td>';
							table+='</tr>';	
							
						}
					);
					table+='</table>';
					table+='</form>';
					draw_navigation();
					$("#tableview").html(table);

				}
			}
			
		);

	}

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	function unset_combined_order(combined_with, orderid)
	{
		show_actions_menu(orderid); //hide options
		$("#dialogbox").dialog
		({	buttons:
			[
				{ text: "Kombinierung aufheben", click: function() { 
				
					wait_dialog_show();
					$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "combine_orders", mode: "unset_combination", combined_with:combined_with},
					function(data)
					{
			
		//	alert(data);
						wait_dialog_hide();
						var $xml=$($.parseXML(data));
						var Ack = $xml.find("Ack").text();
						if (Ack=="Success") 
						{
							view_box();
							$("#dialogbox").dialog("close");
						}
						else
						{
							show_status2(data);
						}
					});
				
				
				} },
				{ text: "Abbrechen", click: function() { $(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Kombinierung von Bestellungen aufheben",
			width:400
		});		
	}

	function set_order_state_dialog(orderid, state)
	{
		//show_actions_menu(orderid); //hide options
				
		$("#update_order_state_shipping_number").val(orders[orderid]["shipping_number"]);
		$("#update_order_stateDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { set_order_state(orderid, state);} },
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bestellung als versendet markieren",
			width:400
		});		

		

	}


	function set_order_state(orderid, state)
	{
		if( state==2 && orders[orderid]["bill_street"]=="" && orders[orderid]["bill_street"]==""  )
		{
			alert("Der Auftrag kann nicht als geschrieben markiert werden, da der Käufer keine Anschrift hat. Gegebenenfalls bitte die Lieferanschrift aus dem Verkaufsprotokoll nachtragen.");
			return;
		}
		
		show_actions_menu(orderid);  //hide options
		shipping_number=$("#update_order_state_shipping_number").val();
		
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_set_shipment_state", OrderID:orderid, state:state, shipping_number:shipping_number},
		function(data)
		{
			//show_status2(data);
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				orders[orderid]["status_id"]=state;
				orders[orderid]["status_date"]=$xml.find("state_date").text();
				orders[orderid]["shipping_number"]=shipping_number;
				update_table_cell(orderid, "order_state");
				
			}
			else
			{
				show_status2(data);
			}
		});
		
		if (state==3) $("#update_order_stateDialog").dialog("close");
		
	}
	
	function change_paymenttype()
	{
		if ($("#paymentType").val()==4 || $("#paymentType").val()==5 || $("#paymentType").val()==6)
		{
			$("#update_Payment_Transaction_row").show();
		}
		else 
		{
			$("#update_Payment_Transaction_row").hide();
			$("#update_Payment_TransactionID").val("");
		}
		if ($("#paymentType").val()==0) 
		{
			$("#update_Payment_amount").val("0,00");
		}
		
	}
	
	function set_order_payment(orderid)
	{
		show_actions_menu(orderid); //hide options
/*
		if (orders[orderid]["Payments_TransactionID"]!="")
		{
			$("#update_Payment_mode_view").show();
		}
*/
		$("#update_Payment_mode_view").show();

		//SELECT FOR PAYMENTMETHODS
		var selectpayment='';
		selectpayment+='<select id="paymentType" size="1" onchange="change_paymenttype();">';
//		selectpayment+='<option value=0>keine Zahlung</option>';
		//SHOPS AUßER EBAY
		if (Shop_Shops[orders[orderid]["shop_id"]]["shop_type"]!=2)
		{
			for (var arrayIndex in PaymentTypes)
			{
				if (arrayIndex!=0)	selectpayment+='<option value='+arrayIndex+'>'+PaymentTypes[arrayIndex]["title"]+'</option>';
			}
			//);
		}

		else
		//EBAYSHOPS
		{
			selectpayment+='<option value=2>Vorkasse</option>';
			//selectpayment+='<option value=4>PayPal</option>';
		}
		selectpayment+='</select>';
		$("#paymentsselect").html(selectpayment);

		$("update_Payment_TransactionID").val("");

		$("#paymentType").val(orders[orderid]["PaymentTypeID"]);
		/*
		if (orders[orderid]["PaymentDate"]>0)
		{
			$("#update_Payment_Date").val(convert_time_from_timestamp(orders[orderid]["PaymentDate"], "date"));
		}
		*/
		$("#update_Payment_Date").val("");
		
		$("#update_Payment_amount").val(orders[orderid]["OrderTotal"]);
		
		$("#update_PaymentDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { set_order_payment_save(orderid);} },
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Zahlung erhalten markieren / bearbeiten",
			width:400
		});		
	}
	
	function set_order_payment_save(orderid)
	{
		if ($("#update_Payment_Date").val()=="")
		{
			alert("Bitte ein Datum zum Zahlungsvorgang angeben!");
			$("#update_Payment_Date").focus();
			return;
		}
		
		var payment_date=Math.round($("#update_Payment_Date").datepicker('getDate') / 1000);
		var PaymentTypeID=$("#paymentType").val();
		var paymentTransactionID=$("#update_Payment_TransactionID").val();
		var amount=$("#update_Payment_amount").val().replace(",",".");;

	//	$("#update_Payment_mode_view").hide();
	/*	
		if (orders[orderid]["Payments_TransactionID"]!="")
		{
			var mode = $("#paymentsmodeselect").val();
		}
		else
		{
			var mode = "payment";
		}
	*/
		
		var mode = $("#paymentsmodeselect").val();
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_set_payment_state", OrderID:orderid, payment_date:payment_date, PaymentTypeID:PaymentTypeID, Payments_TransactionID:paymentTransactionID, amount:amount, mode:mode},
			function(data)
			{
//show_status2(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#update_PaymentDialog").dialog("close");
					orders[orderid]["PaymentDate"]=payment_date;
					orders[orderid]["PaymentTypeID"]=PaymentTypeID;
					orders[orderid]["Payments_TransactionState"]=$xml.find("Payments_TransactionState").text();
					//update view_box
					//show_payment_state(orderid);
					update_table_cell(orderid, "order_state");
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}


	function set_order_note(OrderID)
	{
		show_actions_menu(OrderID); //hide options
				
		$("#update_NoteDialogNote").val($("#ordernote"+OrderID).text());
		$("#update_NoteDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { save_order_note(OrderID);} },
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Notiz hinzufügen / bearbeiten",
			width:800
		});		
	
	}

	function save_order_note(OrderID)
	{
		var note = $("#update_NoteDialogNote").val();
			$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_set_order_note", OrderID:OrderID, note:note},
			function(data)

			{
		
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#ordernote"+OrderID).text(note);
					$("#update_NoteDialog").dialog("close");
					if (note.length>0)
					{
						$("#ordernoterow"+OrderID).show();
					}
					else
					{
						$("#ordernoterow"+OrderID).hide();

					}
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}


	function send_DHLretourlabel(orderid, dhl_parameter)
	{
		wait_dialog_show();		
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_order_detail", OrderID:orderid},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				         
				$("#DHLretourlabel_address_company").val($xml.find("bill_company").text());
				$("#DHLretourlabel_address_additional").val($xml.find("bill_additional").text());
				$("#DHLretourlabel_address_firstname").val($xml.find("bill_firstname").text());
				$("#DHLretourlabel_address_lastname").val($xml.find("bill_lastname").text());
				$("#DHLretourlabel_address_street").val($xml.find("bill_street").text());
				$("#DHLretourlabel_address_number").val($xml.find("bill_number").text());
				$("#DHLretourlabel_address_zip").val($xml.find("bill_zip").text());
				$("#DHLretourlabel_address_city").val($xml.find("bill_city").text());
				$("#DHLretourlabel_address_country_code").val($xml.find("bill_country_code").text());
				$("#DHLretourlabel_usermail").val($xml.find("usermail").text());

				$("#DHLretourlabelDialog").dialog
				({	buttons:
					[
						{ text: "DHL Retourlabel senden", click: function() { do_send_DHLretourlabel(orderid, dhl_parameter);} },
						{ text: "Beenden", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"DHL Retourlabel senden",
					width:400
				});		

				
			}
			else
			{
				show_status2(data);
			}
		});

	}

	function do_send_DHLretourlabel(orderid, dhl_parameter)
	{
		$("#DHLretourlabelDialog").dialog("close");
		
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "set_DHLRetourLabelID", OrderID:orderid, LabelID:"unbekannt"},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack!="Success") 
				{
					show_status2(data);
				}
			}
		);
		save_DHLretourlabelID(orderid);
		
	
		//var href='https://amsel.dpwn.net/abholportal/gw/lp/portal/mapco/customer/RpOrder.action?delivery=RetourenLager01';
		var href='https://amsel.dpwn.net/abholportal/gw/lp/portal/mapco/customer/RpOrder.action?delivery='+dhl_parameter;
		href+='&SHIPMENT_REFERENCE='+orderid;
		href+='&ADDR_SEND_STREET_ADD=';
		href+='&ADDR_SEND_EMAIL='+escape($("#DHLretourlabel_usermail").val());
		href+='&ADDR_SEND_FIRST_NAME='+escape($("#DHLretourlabel_address_firstname").val());
		href+='&ADDR_SEND_LAST_NAME='+escape($("#DHLretourlabel_address_lastname").val());
		href+='&ADDR_SEND_NAME_ADD='+escape($("#DHLretourlabel_address_additional").val());
		href+='&ADDR_SEND_STREET='+escape($("#DHLretourlabel_address_street").val()+' '+$("#DHLretourlabel_address_number").val());
		href+='&ADDR_SEND_ZIP='+escape($("#DHLretourlabel_address_zip").val());
		href+='&ADDR_SEND_CITY='+escape($("#DHLretourlabel_address_city").val());
	//	alert(href);
		window.open(href);
		
	}
	
	function save_DHLretourlabelID(orderid)
	{
		$("#DHLretourlabelID_LabelID").val("");
		$("#DHLretourlabelIDDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { do_save_DHLretourlabelID(orderid);} },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"DHL Retourlabel ID zur Bestellung speichern",
			width:400
		});		
		
	}
	
	function do_save_DHLretourlabelID(orderid)
	{
		var LabelID = $("#DHLretourlabelID_LabelID").val();
		
		//check if Field is not empty
		if (LabelID != "")
		{
			$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "set_DHLRetourLabelID", OrderID:orderid, LabelID:LabelID},
				function(data)
				{
					var $xml=$($.parseXML(data));
					var Ack = $xml.find("Ack").text();
					if (Ack=="Success") 
					{
						show_status("Retourlabel ID wurde erfolgreich gespeichert");
						$("#DHLretourlabelIDDialog").dialog("close");
					}
					else
					{
						show_status2(data);
					}
				}
			);
			
		}
		else
		{
			alert("Es muss eine Retourlabel ID angegeben werden");
			$("#DHLretourlabelID_LabelID").focus();
		}
		
	}
	
	
	function show_order_details(Order_id, customer_id)
	{
		if (Order_id==null) Order_id=0;
		if (customer_id==null) customer_id=0;
		var href="";
		href+='<?php echo PATH."backend_crm_orders_details_test.php?"; ?>';
		href+='OrderID='+Order_id;
		href+='&customer_id='+customer_id;
		href+='&ResultPage='+ResultPage;
		href+='&ResultPages='+ResultPages;
		href+='&ResultRange='+ResultRange;
		href+='&Results='+Results;
		href+='&FILTER_Platform='+FILTER_Platform;
		href+='&FILTER_Status='+FILTER_Status;
		href+='&FILTER_SearchFor='+FILTER_SearchFor;
		href+='&FILTER_Searchfield='+encodeURIComponent($("#FILTER_Searchfield").val());
		href+='&date_from='+date_from;
		href+='&date_to='+date_to;
		href+='&OrderBy='+OrderBy;
		href+='&OrderDirection='+OrderDirection;

//	alert(href);
		window.location = href;		
	}
	
	function order_add()
	{
		$("#order_add_shop").val(FILTER_Platform);
		
		
		$("#order_add_dialog").dialog
			({	buttons:
				[
					{ text: "Bestellung anlegen", click: function() { 
						if ($("#order_add_shop").val()!=0)
						{
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderAdd", mode:"manual", Currency_Code:"EUR", shop_id:$("#order_add_shop").val()},
								function(data)
								{
									wait_dialog_hide();
								//	alert(data);
									var $xml=$($.parseXML(data));
									var Ack = $xml.find("Ack").text();
									if (Ack=="Success") 
									{
										
										var orderid=$xml.find("id_order").text();
									//	alert(orderid);
										if (orderid!=0 && orderid!="")
										{
											$("#order_add_dialog").dialog("close");
											order_update_dialog(orderid, "update");
											return;
										}
										else
										{
											show_status("FEHLER BEIM ANLEGEN EINER NEUEN BESTELLUNG");
											return;
										}
									}
									else
									{
										show_status2(data);
									}
								}
							);
						}
						else
						{
							alert("Bitte zuerst einen Shop wählen!");	
						}
					} },
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Neue Bestellung anlegen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Bestellung ans IDIMS senden",
				width:400
			});	

	}
	
	function order_update_set_payment(orderid, payment_type_id)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderUpdate", 
				SELECTOR_id_order:orderid,
				payments_type_id:payment_type_id,
				Payments_TransactionState:"Pending",
				Payments_TransactionStateDate: Math.round(+new Date()/1000) },
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				orders[orderid]["PaymentTypeID"]=payment_type_id;
				update_table_cell(orderid, "order_state");
				order_update_dialog(orderid, "update");
			}
			else
			{
				show_status2(data);
				order_update_dialog(orderid, "update");
			}
		});
	
	}
	

	function order_update_dialog(orderid, mode)
	{
		//var for correlation check
		var shipping_type_id=0;

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_order_detail", OrderID:orderid},
			function(data)
			{
				wait_dialog_hide();
			//	alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					//CREATE ADDRESS DIV
					$("#send_order_dialog_addressBox").html("");
					var html= '';
					
					if ($xml.find("customer_id").text()!=0)
					{
						html+='<table style="width:750px">';
						html+='<tr>';
						html+='<td style="backface-color:#fff">';
						html+='<b>'+Shop_Shops[$xml.find("customer_shop_id").text()]["title"]+' Kunde</b>';
						html+='</td><td>';
						html+=$xml.find("customer_username").text();
						if ($xml.find("customer_name").text()!="")
						{
							html+=' - '+$xml.find("customer_name").text();
						}
						html+='</td>';
						html+='</tr>';
						html+='</table>';
					}
					
					html+='<table style="width:750px">';
					html+='<colgroup><col style="width:40%"><col style="width:60%"></colgroup>';
					html+='<tr>';
					html+='	<th colspan="2">';
					html+='Lieferadresse';
					if (mode=="update")
					{
						html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/24x24/blog_post_edit.png" alt="Lieferadresse bearbeiten" title="Lieferadresse bearbeiten" onclick="change_shippingAddress('+orderid+');"/>';
						html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/24x24/database_search.png" alt="Kunden suchen" title="Kunden suchen" onclick="find_customer_dialog('+orderid+', '+$xml.find("shop_id").text()+');"/>';
					}
					html+='</th>';
					html+='</tr>';
					//COMPAMY
					html+='<tr>';
					html+='	<td>Firma</td><td style="background-color:#fff">'+$xml.find("shop_bill_adr_company").text()+'</td>';
					html+='</tr>';
					//ADDITIONAL
					html+='<tr>';
					html+='	<td>Adresszusatz <small>(Postnummer)</small></td><td style="background-color:#fff">'+$xml.find("shop_bill_adr_additional").text()+'</td>';
					html+='</tr>';
					//NAME
					html+='<tr>';
					html+='	<td>Name</td><td style="background-color:#fff">'+$xml.find("shop_bill_adr_firstname").text()+' '+$xml.find("shop_bill_adr_lastname").text()+'</td>';
					html+='</tr>';
					//STREET & NUMBER
					html+='<tr>';
					html+='	<td>Straße/Hausnummer <small>(Packst.Nr./Packstation)</small> </td><td style="background-color:#fff">'+$xml.find("shop_bill_adr_street").text()+' '+$xml.find("shop_bill_adr_number").text()+'</td>';
					html+='</tr>';
					//PLZ & CITY
					html+='<tr>';
					html+='	<td>Postleitzahl / Stadt</td><td style="background-color:#fff">'+$xml.find("shop_bill_adr_zip").text()+' '+$xml.find("shop_bill_adr_city").text()+'</td>';
					html+='</tr>';
					//COUNTRY
					html+='<tr>';
					html+='	<td>Land</td><td style="background-color:#fff">'+$xml.find("shop_bill_adr_country").text()+'</td>';
					html+='</tr>';
					// TELEFON
					html+='<tr>';
					html+='	<td>Telefon</td><td style="background-color:#fff">'+$xml.find("userphone").text()+'</td>';
					html+='</tr>';
					// EMAIL
					html+='<tr>';
					html+='	<td>E-Mail</td><td style="background-color:#fff">'+$xml.find("usermail").text()+'</td>';
					html+='</tr>';

					html+='</table>';
					
					$("#send_order_dialog_addressBox").html(html);
					
					//CREATE ORDER PAYMENT BOX
					$("#send_order_dialog_PaymentBox").html("");
					
					html='';
					//CHECK if paymenttype is shown or selectable
						// if mode update and paymentstatus not completed -> selectable
					if (mode == "update" && orders[orderid]["Payments_TransactionState"]!="Completed") 
					{
						html+='<table>';
						html+='<tr>';
						html+='	<th>';
						html+='		Zahlungsart:';
						html+='	</th>';
						html+='	<td>';

						html+='<select id="send_order_dialog_PaymentBox_Payment" size="1" onchange="order_update_set_payment('+orderid+',this.value);">';
						$.each(PaymentTypes, function(paymentypeid, fields)
						{
							if (paymentypeid==0) fields["title"]="Zahlart wählen";
							//SELECTBOX FÜR EBAY-SHOPS
							if (Shop_Shops[orders[orderid]["shop_id"]]["shop_type"]==2)
							{
								if (fields["PaymentMethod"]!="")
								{
									if (paymentypeid==orders[orderid]["PaymentTypeID"])
									{
										html+='<option value='+paymentypeid+' selected>'+fields["title"]+'</option>';
									}
									else
									{
										html+='<option value='+paymentypeid+'>'+fields["title"]+'</option>';
									}
								}
							}
							else
							//SELECTBOX FÜR ANDERE SHOPS
							{
								if (paymentypeid==orders[orderid]["PaymentTypeID"])
								{
									html+='<option value='+paymentypeid+' selected>'+fields["title"]+'</option>';
								}
								else
								{
									html+='<option value='+paymentypeid+'>'+fields["title"]+'</option>';
								}
							
							}
							
						});
						html+='</select>';
						
						html+=' </td>';
						html+='</tr>';
						html+='</table>';
					}
					else
					{
						html+='<table>';
						html+='<tr>';
						html+='	<th>';
						html+='		Zahlungsart:';
						html+='	</th>';
						html+='	<td>';
						if (orders[orderid]["PaymentTypeID"]==0)
						{
							html+='		<b>Keine Zahlart ausgewählt</b>';
						}
						else
						{
							html+='		<b>'+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'</b>';
						}
						html+=' </td>';
						html+='</tr>';
						html+='</table>';
					}
					$("#send_order_dialog_PaymentBox").html(html);
					
					
					
					//CREATE ORDER DIV
					//$("#send_order_dialog").append('<div id="send_order_dialog_OrderBox"></div>');
					
					html='';
					html+='<table style="width:750px">';
					html+='<tr>';
					html+='	<th>MPN</th>';
					html+='	<th>ArtBez</th>';
					html+='	<th>Menge</th>';
					html+='	<th>einz.VK</th>';
					html+='	<th>ges. VK';
					html+='</th>';
					if(<?php echo $_SESSION["id_user"];?>==21371)
						html+='	<th>COS Marge</th>';
					html+='<th>';
					if (mode=="update")
					{
						//PARENTORDERID festlegen
						
						if (1*$xml.find("combined_with").text()>0)
						{
							var parentorderid=$xml.find("combined_with").text();							
						}
						else
						{
							var parentorderid=$xml.find("id_order").text();
						}

						html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Bestellposition hinzufügen" title="Bestellposition hinzufügen" onclick="add_orderpositions('+parentorderid+', '+$xml.find("shop_id").text()+');"/>';
					}
					html+='</th>';
					html+='</tr>';
					
					$xml.find("order").each(
					function()
					{
						shipping_type_id=$(this).find("shipping_type_id").text();
						var order_id=$(this).find("orderid").text();
					
						$xml.find("Item").each(
						function()
						{
							if (order_id == $(this).find("OrderItemOrderID").text())
							{
								if ($(this).find("OrderItemChecked").text()==1) var bgcolor='background-color:#dfd';
								else if ($(this).find("OrderItemChecked").text()==2) var bgcolor='background-color:#fdd';
								else var bgcolor='background-color:#fff';
								html+='<tr style="'+bgcolor+'">';
								html+='	<td>'+$(this).find("OrderItemMPN").text()+'</td>';
								html+='	<td>'+$(this).find("OrderItemDesc").text()+'</td>';
								html+='	<td>'+$(this).find("OrderItemAmount").text()+'</td>';
								html+='	<td>EUR '+$(this).find("orderItemPriceGross").text()+'</td>';
								html+='	<td>EUR '+$(this).find("orderItemTotalGross").text()+'</td>';
								if(<?php echo $_SESSION["id_user"];?>==21371)
									html+='	<td>' + $(this).find("orderItemPriceCOS_M").text() + ' %</td>';
								html+='	<td>';
								if (mode=="update")
								{
									html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/24x24/blog_post_edit.png" alt="Bestellposition bearbeiten" title="Bestellposition bearbeiten" onclick="change_orderpositions('+orderid+', '+$(this).find("OrderItemID").text()+');"/>';
								}
								if (mode=="return")
								{
									html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/24x24/blog_post_edit.png" alt="Rückgabe / Umtausch bearbeiten" title="Rückgabe / Umtausch bearbeiten" onclick="return_dialog('+orderid+', '+$(this).find("OrderItemID").text()+');"/>';
								}
								
								html+='	</td>';
								html+='</tr>';
							}
						});
						html+='<tr>';
						html+='	<td></td>';
						
		
						if ($(this).find("shipping_type_id").text()==0)
						{
							html+='	<td><b>KEINE VERSANDART AUSGEWÄHLT!</b></td>';
						}
						else
						{
							html+='	<td><b>Versand per '+ShipmentTypes[$(this).find("shipping_type_id").text()]["title"]+'</b></td>'
						}
						html+='	<td></td>';
						html+='	<td></td>';
						html+='	<td>EUR '+$(this).find("shipping_costs").text()+'</td>';
						html+='	<td>';
						if (mode=="update")
						{
							html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/24x24/blog_post_edit.png" alt="Versand bearbeiten" title="Versand bearbeiten" onclick="change_ordershipping('+order_id+');"/>';
						}
						html+='	</td>';
						if(<?php echo $_SESSION["id_user"];?>==21371)
							html+='	<td></td>';
						html+='</tr>';

					});

					html+='<tr style="background-color:#fff">';
					html+='	<td></td>';
					html+='	<td><b>Gesamt</b></td>';
					html+='	<td>'+$xml.find("orderItemCount").text()+'</td>';
					html+='	<td></td>';
					html+='	<td><b>EUR '+$xml.find("orderTotalGross").text()+'</b></td>';
					html+='	<td>';
					html+='	</td>';
					if(<?php echo $_SESSION["id_user"];?>==21371)
							html+='	<td></td>';
					html+='</tr>';
					
					html+='</table>';
					
					$("#send_order_dialog_OrderBox").html(html);
					if (!$("#send_order_dialog").is(":visible"))
					{
						//DIALOG ZUM BESTELLUNGVERSENDEN
						if (mode=="IDIMS")
						{
							$("#send_order_dialog").dialog
								({	buttons:
									[
										{ text: "Bestellung senden", click: function() { order_send(orderid);} },
										{ text: "Beenden", click: function() { $(this).dialog("close"); } }
									],
									closeText:"Fenster schließen",
									hide: { effect: 'drop', direction: "up" },
									modal:true,
									resizable:false,
									show: { effect: 'drop', direction: "up" },
									title:"Bestellung ans IDIMS senden",
									width:800
								});	
						}
						//DIALOG ZUR BEARBEITUNG DER BESTELLUNG
						if (mode=="update" || mode=="view")
						{
							$("#send_order_dialog").dialog
								({	buttons:
									[
									//{ text: "Bestellung senden", click: function() { order_send(orderid);} },
										{ text: "Beenden", click: function() { $(this).dialog("close"); } }
									],
									closeOnEscape: false,
									closeText:"Fenster schließen",
								//	hide: { effect: 'drop', direction: "up" },
									modal:true,
									resizable:false,
								//	show: { effect: 'drop', direction: "up" },
									title:"Bestellung bearbeiten",
									width:800,
									//CHECK, OB Einträge vollständig
									beforeClose: function() 
									{ 
										//CHECK FOR PAYMENT METHOD
										if (mode=="update" && $("#send_order_dialog_PaymentBox_Payment").val()==0) 
										{
											order_update_dialog(orderid, mode); 
											$("#send_order_dialog_PaymentBox_Payment").focus(); 
											alert("Es muss eine Zahlart ausgewählt werden!");
										} 
										if (mode=="update" && shipping_type_id==0) 
										{
											order_update_dialog(orderid, mode); 
											change_ordershipping(orderid);
											alert("Es muss eine Versandart ausgewählt werden!");
										} 

										// CHECK FOR CORRELATION Payment Nachnahme (&& shipment Nachnahme)
										if (mode=="update" && $("#send_order_dialog_PaymentBox_Payment").val()==3 && shipping_type_id!=15) 
										{
											alert("Bitte den Versand auf Nachnahme und die Versandkosten setzen!");
											order_update_dialog(orderid, mode);
											change_ordershipping(orderid);
										}
									}
									
								});	
							
							// CHECK FOR CORRELATION Payment Nachnahme (&& shipment Nachnahme)
							if ($("#send_order_dialog_PaymentBox_Payment").val()==3 && shipping_type_id!=15)
							{
								alert("Bitte den Versand auf Nachnahme und die Versandkosten setzen!");
								change_ordershipping(orderid);
							}

								
						}
						//DIALOG ZUR BEARBEITUNG DER BESTELLUNG
						if (mode=="return")
						{
							$("#send_order_dialog").dialog
								({	buttons:
									[
									//{ text: "Bestellung senden", click: function() { order_send(orderid);} },
										{ text: "Beenden", click: function() { $(this).dialog("close"); } }
									],
									closeText:"Fenster schließen",
									hide: { effect: 'drop', direction: "up" },
									modal:true,
									resizable:false,
									show: { effect: 'drop', direction: "up" },
									title:"Rückgabe / Umtausch bearbeiten",
									width:800
								});	
						}

					}
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}
	

	function change_shippingAddress(orderid)
	{
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_order_detail", OrderID:orderid},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{

					$("#update_shipping_address_company").val($xml.find("shop_bill_adr_company").text());
					$("#update_shipping_address_firstname").val($xml.find("shop_bill_adr_firstname").text());
					$("#update_shipping_address_lastname").val($xml.find("shop_bill_adr_lastname").text());
					$("#update_shipping_address_street").val($xml.find("shop_bill_adr_street").text());
					$("#update_shipping_address_number").val($xml.find("shop_bill_adr_number").text());
					$("#update_shipping_address_additional").val($xml.find("shop_bill_adr_additional").text());
					$("#update_shipping_address_zip").val($xml.find("shop_bill_adr_zip").text());
					$("#update_shipping_address_city").val($xml.find("shop_bill_adr_city").text());
					
					$("#update_shipping_usermail").val($xml.find("usermail").text());
					$("#update_shipping_userphone").val($xml.find("userphone").text());
					
					$("#update_shipping_customer_id").val($xml.find("customer_id").text());
					$("#update_shipping_shop_id").val($xml.find("shop_id").text());
					
					if ($xml.find("shop_bill_adr_country_id").text()!="")
					{
						$("#update_shipping_address_country_code").val(Countries[$xml.find("shop_bill_adr_country_id").text()]["country_code"]);
					}

					$("#update_shipping_addressDialog").dialog
					({	buttons:
						[
							//{ text: "Nach Kunde suchen", click: function() { find_customer(orderid, "");} },
							{ text: "Speichern", click: function() { change_shippingAddress_save(orderid);} },
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
				else
				{
					show_status2(data);
				}
			}
		);
	}
	
	function find_customer_dialog(orderid, shop_id)
	{
		$("#find_customer_qry_string").val("");
		$("#find_customer_dialog_shop_id").val(shop_id);
		$("#find_customer_dialog_orderid").val(orderid);
		$("#find_customer_dialog").dialog
			({	buttons:
				[
				//{ text: "Bestellung senden", click: function() { order_send(orderid);} },
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Kunde suchen",
				width:1200
			});	
	}
	
	function find_customer()
	{
		var orderid=$("#find_customer_dialog_orderid").val();
		var shop_id=$("#find_customer_dialog_shop_id").val();

		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerSearch2", mode:"find_customer", shop_id:shop_id, qry_string:$("#find_customer_qry_string").val()},
		function(data)
		{
			//show_status2(data);
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				var html='';
				
				html+='<table>';
				html+='<colgroup>';
				html+='<col style="width:150px">';
				html+='<col style="width:250px">';
				html+='<col style="width:250px">';
				html+='<col style="width:200px">';
				html+='<col style="width:200px">';
				html+='<col style="width:100px">';
				html+='</colgroup>';
				
				$xml.find("customer").each(
				function()
				{

					var user_id=$(this).find("user_id").text();

					html+='<tr>';
					html+='<th colspan="3" ondblclick="set_order_customer('+orderid+', '+user_id+', 0)">Kunde:'+$(this).find("name").text()+'</th>';
					html+='<th colspan="3" ondblclick="set_order_customer('+orderid+', '+user_id+', 0)">Shop-Nutzername: '+$(this).find("username").text()+'</th>';
					html+='</tr>';
					html+='<tr>';
					html+='	<td>Firma</td>';
					html+='	<td>Name</td>';
					html+='	<td>Anschrift</td>';
					html+='	<td>Adresszusatz</td>';
					html+='	<td>Postleitzahl / Stadt</td>';
					html+='	<td>Land</td>';
					html+='</tr>';
					$(this).find("address").each(
					function()
					{
						var order_adr_id=$(this).find("id_order").text();
						html+='<tr style="background-color:#fff; cursor:pointer">';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">'+$(this).find("bill_company").text()+'</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">';
							html+=$(this).find("bill_firstname").text()+' '+$(this).find("bill_lastname").text()+'<br />';
							html+='<small><b>e-Mail: </b>'+$(this).find("usermail").text()+'</small>';
						html+='</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">';
							html+=$(this).find("bill_street").text()+' '+$(this).find("bill_number").text()+'<br />';
							html+='<small><b>Telefon: </b>'+$(this).find("userphone").text()+'</small>';
						html+='</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">';
							html+=$(this).find("bill_additional").text()+'<br />';
							html+='<small><b>Mobil: </b>'+$(this).find("usermobile").text()+'</small>';
						html+='</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">';
							html+=$(this).find("bill_zip").text()+' '+$(this).find("bill_city").text()+'<br />';
							html+='<small><b>Fax: </b>'+$(this).find("userfax").text()+'</small>';
						html+='</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">'+$(this).find("bill_country").text()+'</td>';
						html+='</tr>';
					});
					/*
					$(this).find("phonenumber").each(
					function()
					{
						html+='<tr>';
						html+=' <td><b>Telefon: <b/></td>';
						html+='	<td colspan="5" style="background-color:#fff">'+$(this).text()+'</td>';
						html+='</tr>';
					});
					*/
					/*
					$(this).find("mailaddress").each(
					function()
					{
						html+='<tr>';
						html+=' <td><b>Telefon: <b/></td>';
						html+='	<td colspan="5" style="background-color:#fff">'+$(this).text()+'</td>';
						html+='</tr>';
					});
					*/
					/*
					$(this).find("customer_account").each(
					function()
					{
						html+='<tr>';
						html+='	<td><b>Nutzername bei</b></td>';
						html+='	<td><b>'+Shop_Shops[$(this).attr("shop_id")]["title"]+'<b></td>';
						html+='	<td colspan="4" style="background-color:#fff">'+$(this).text()+'</td>';
						html+='</tr>';
					});
					*/
					
				});
				html+='</table>';
				
				$("#customer_select_dialog").html(html);
				
			}
			else
			{
			}
		});
	}

	function set_order_customer(orderid, user_id, order_adr_id)
	{
		var company="";
		var firstname="";
		var lastname="";
		var street="";
		var number="";
		var additional="";
		var city="";
		var zip="";
		var country="";
		var country_code="";
		var usermail="";
		var userphone="";
		var usermobile="";
		var userfax="";
		var bill_adr_id=0;
		
		if (order_adr_id!=0)
		{
			
			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerSearch2", mode:"show_customer", user_id:user_id},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("address").each(
					function()
					{
						if ($(this).find("id_order").text()==order_adr_id)
						{
							company=$(this).find("bill_company").text();
							firstname=$(this).find("bill_firstname").text();
							lastname=$(this).find("bill_lastname").text();
							street=$(this).find("bill_street").text();
							number=$(this).find("bill_number").text();
							additional=$(this).find("bill_additional").text();
							city=$(this).find("bill_city").text();
							zip=$(this).find("bill_zip").text();
							country=$(this).find("bill_country").text();
							country_code=$(this).find("bill_country_code").text();
							usermail=$(this).find("usermail").text();
							userphone=$(this).find("userphone").text();
							usermobile=$(this).find("usermobile").text();
							userfax=$(this).find("userfax").text();
							bill_adr_id=$(this).find("bill_adr_id").text();
							
							$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", 
									SELECTOR_id_order:orderid,
									bill_company:company,
									bill_firstname:firstname,
									bill_lastname:lastname,
									bill_zip:zip,
									bill_city:city,
									bill_street:street,
									bill_number:number,
									bill_additional:additional,
									bill_country:country,
									bill_country_code:country_code,
									bill_adr_id:bill_adr_id,
									customer_id:user_id,
									usermail:usermail,
									userphone:userphone,
									usermobile:usermobile,
									userfax:userfax},
							function(data)
							{
								var $xml=$($.parseXML(data));
								var Ack = $xml.find("Ack").text();
								if (Ack=="Success") 
								{
									$("#find_customer_dialog").dialog("close");
									order_update_dialog(orderid, "update");
								}
								else
								{
									show_status2(data);
								}
							});
						}
							
					});
				}
				else
				{
					show_status2(data);
				}
			});
		}
		else
		//NUR CUSTOMER ÜBERNEHMEN
		{
			$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", 
				SELECTOR_id_order:orderid,
				bill_company:company,
				bill_firstname:firstname,
				bill_lastname:lastname,
				bill_zip:zip,
				bill_city:city,
				bill_street:street,
				bill_number:number,
				bill_additional:additional,
				bill_country:country,
				bill_country_code:country_code,
				bill_adr_id:bill_adr_id,
				customer_id:user_id,
				usermail:usermail,
				userphone:userphone,
				usermobile:usermobile,
				userfax:userfax
			},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#find_customer_dialog").dialog("close");
					order_update_dialog(orderid, "update");
				}
				else
				{
					show_status2(data);
				}
			});
		}

	}
	
	
	function change_shippingAddress_save(orderid)
	{
		//CHECK FOR CUSTOMER ID 
		// IF 0 -> CREATE NEW CMS_USER
		if ($("#update_shipping_customer_id").val()==0)
		{
			var usermail=$("#update_shipping_usermail").val();
			var firstname=$("#update_shipping_address_firstname").val();
			var lastname=$("#update_shipping_address_lastname").val();
			var shop_id=$("#update_shipping_shop_id").val();
			
			if (usermail=="") {alert("Bei Neukunden bitte eine E-Mailadresse angeben"); return;}
			else if (lastname=="") {alert("Bei Neukunden bitte einen Nachnamen angeben"); return;} 
						
			$.post("<?php echo PATH;?>soa2/", { API: "cms", APIRequest: "CMS_UserCreate", usermail:usermail, firstname:firstname, lastname:lastname, shop_id:shop_id},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#update_shipping_customer_id").val($xml.find("customer_id").text());
					change_shippingAddress_save2(orderid);
					return;
				}
				else
				{
					show_status2(data);
				}
			});
		}
		else
		{
			change_shippingAddress_save2(orderid);
		}
	}
					
		
	function change_shippingAddress_save2(orderid)
	{
		
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_update_shipping_address", 
			OrderID:orderid,
			customer_id:$("#update_shipping_customer_id").val(),
			shop_id:$("#update_shipping_shop_id").val(),
			ship_company:$("#update_shipping_address_company").val(),
			ship_firstname:$("#update_shipping_address_firstname").val(),
			ship_lastname:$("#update_shipping_address_lastname").val(),
			ship_street:$("#update_shipping_address_street").val(),
			ship_number:$("#update_shipping_address_number").val(),
			ship_additional:$("#update_shipping_address_additional").val(),
			ship_zip:$("#update_shipping_address_zip").val(),
			ship_city:$("#update_shipping_address_city").val(),
			ship_country_code:$("#update_shipping_address_country_code").val(),
			
			usermail:$("#update_shipping_usermail").val(),
			userphone:$("#update_shipping_userphone").val()
						
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

					order_update_dialog(orderid, "update");
				//	update_view(orderid);
				}
				else
				{
					show_status2(data);
				}
			}
		);
		
	}
	


	function change_ordershipping(orderid)
	{
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_order_detail", OrderID:orderid},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("order").each(
					function()
					{
						if (orderid==$(this).find("orderid").text())
						{
							$("#change_ordershipping_shipping_type_id").val($(this).find("shipping_type_id").text());
							$("#change_ordershipping_shipping_costsFCgross").val($(this).find("shipping_costs").text().toString().replace(".", ","));
							$("#change_ordershipping_shipping_costsFCnet").val($(this).find("shipping_net").text().toString().replace(".", ","));
							$("#change_ordershipping_shop_id").val($xml.find("shop_id").text());
							$("#currency_shipping_costs").text($xml.find("Currency_Code").text());
							$xml.find("Item").each(
							function()
							{
								if ($(this).find("OrderItemOrderID").text()==orderid)
								{
									$("#change_ordershipping_exchangeratetoEUR").val($(this).find("OrderItemExchangeRateToEUR").text());
								}
							});
						}
					});
					
					var shipping_costsEURgross=($("#change_ordershipping_shipping_costsFCgross").val().replace(",",".")*1)/$("#change_ordershipping_exchangeratetoEUR").val();
					var shipping_costsEURnet=($("#change_ordershipping_shipping_costsFCnet").val().replace(",",".")*1)/$("#change_ordershipping_exchangeratetoEUR").val();
					$("#change_ordershipping_shipping_costsEURgross").val(shipping_costsEURgross.toFixed(2).toString().replace(".", ","));
					$("#change_ordershipping_shipping_costsEURnet").val(shipping_costsEURnet.toFixed(2).toString().replace(".", ","));

					
					if ($xml.find("Currency_Code").text()!="EUR") $(".change_ordershipping_EUR_col").show(); else $(".change_ordershipping_EUR_col").hide();
					
					$("#change_ordershipping_dialog").dialog
					({	buttons:
						[
							{ text: "Speichern", click: function() { change_ordershipping_save(orderid);} },
							{ text: "Beenden", click: function() { $(this).dialog("close");} }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Versand bearbeiten",
						width:400
					});		
				}
				else
				{
					
				}
			}
		);
	}

	function change_ordershipping_save(orderid)
	{
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		var shipping_costs=($("#change_ordershipping_shipping_costsFCgross").val().replace(/,/g, "."))*1;
		var shipping_net=($("#change_ordershipping_shipping_costsFCnet").val().replace(/,/g, "."))*1;
		var shop_id=$("#change_ordershipping_shop_id").val();
		var shipping_type_id=$("#change_ordershipping_shipping_type_id").val();
		if (shipping_type_id==0)
		{
			$("#change_ordershipping_shipping_type_id").focus();
			alert("Es muss eine Versandart ausgewählt werden!");
			return;
		}
		else
		{
		
			$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, shipping_costs:shipping_costs, shipping_net:shipping_net, shipping_type_id:shipping_type_id },
				function(data)
				{
					var $xml=$($.parseXML(data));
					var Ack = $xml.find("Ack").text();
					if (Ack=="Success") 
					{
						/*
						//WENN EBAY-SHOP UND NICHT PAYPAL
						if (Shop_Shops[shop_id]["shop_type"]==2  && orders[orderid]["PaymentTypeID"]!=4)
						{
							$.post("<?php echo PATH; ?>soa/", { API: "ebay", Action: "ReviseCheckoutStatus", mode:"shipmentupdate", id_order:orderid},
								function (data)
								{
									var $xml=$($.parseXML(data));
									var Ack = $xml.find("Ack").text();
									if (Ack=="Success") 
									{
										$("#change_ordershipping_dialog").dialog("close");
										order_update_dialog(orderid, "update");
										return;
									}
									else
									{
										show_status2(data);
									}
								}
							);
						}
						else
						*/
						
						// SET MANUAL ADDRESS UPDATE -> VERSAND WIRD VON EBAY NICHT MEHR ÜBERNOMMEN
						$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, bill_address_manual_update:1 },
							function(data)
							{
								var $xml=$($.parseXML(data));
								var Ack = $xml.find("Ack").text();
								if (Ack=="Success") 
								{
									alert(data);
									$("#change_ordershipping_dialog").dialog("close");
									order_update_dialog(orderid, "update");
								}
								else
								{
									show_status2(data);
								}
							});

					}
					else
					{
						show_status2(data);
					}
				}
			);
		}
	}

	
	function change_orderpositions(orderid, orderitemid)
	{
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_order_detail", OrderID:orderid},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					
					$("#change_orderpositions_Currency").val($xml.find("Currency_Code").text());
					$("#change_orderpositions_VATFree").val($xml.find("VATFree").text());
					var VATFree=$xml.find("VATFree").text();
					$xml.find("Item").each(
					function()
					{
						if (orderitemid == $(this).find("OrderItemID").text())
						{
							$("#change_orderpositions_exchangeratetoEUR").val($(this).find("OrderItemExchangeRateToEUR").text());
							$("#change_orderpositions_Currency").val($(this).find("OrderItemCurrency_Code").text());

							var exchangerate=$(this).find("OrderItemExchangeRateToEUR").text()*1;
							var currency=$(this).find("OrderItemCurrency_Code").text();
														
							$("#change_orderpositions_MPN").val($(this).find("OrderItemMPN").text());
							$("#change_orderpositions_ItemID").val($(this).find("OrderItemItemID").text());
							$("#change_orderpositions_ArtBez").text($(this).find("OrderItemDesc").text());
							$("#change_orderpositions_amount").val($(this).find("OrderItemAmount").text());
							
							//FOREIGN CURRENCY
							var priceGross = $(this).find("orderItemPriceGross").text().replace(/,/g, ".")*exchangerate;
							var priceNet = $(this).find("orderItemPriceNet").text().replace(/,/g, ".")*exchangerate;
							$(".change_orderpositions_currency").text(currency);
							$("#change_orderpositions_FCprice_gross").val(priceGross.toFixed(2).toString().replace(".", ","));
							$("#change_orderpositions_FCprice_net").val(priceNet.toFixed(2).toString().replace(".", ","));
							
							var totalpriceGross = $(this).find("orderItemTotalGross").text().replace(/,/g, ".")*exchangerate;
							var totalpriceNet = $(this).find("orderItemTotalNet").text().replace(/,/g, ".")*exchangerate;
							$("#change_orderpositions_FCpriceTotal_gross").text(totalpriceGross.toFixed(2).toString().replace(".", ","));
							$("#change_orderpositions_FCpriceTotal_net").text(totalpriceNet.toFixed(2).toString().replace(".", ","));
							
							//EUR
							if (currency=="EUR") {$(".change_orderpositions_colEUR").hide();} else {$(".change_orderpositions_colEUR").show();}
							
							$("#change_orderpositions_price_gross").val($(this).find("orderItemPriceGross").text());
							$("#change_orderpositions_price_net").val($(this).find("orderItemPriceNet").text());
							$("#change_orderpositions_priceTotal_gross").text($(this).find("orderItemTotalGross").text());
							$("#change_orderpositions_priceTotal_net").text($(this).find("orderItemTotalNet").text());
							
							$("#change_orderpositions_Currency").val($(this).find("OrderItemCurrency_Code").text());
						}
					});
					
					$("#change_orderpositions_dialog").dialog
					({	buttons:
						[
							{ text: "Speichern", click: function() { change_orderpositions_save(orderid, orderitemid);} },
							{ text: "Beenden", click: function() { $(this).dialog("close");} }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Bestellpositionen bearbeiten",
						width:800
					});		
					
				}
				else
				{
				}
			}
		);
	}

	function change_orderpositions_save(orderid, orderitemid)
	{
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		
		var amount = $("#change_orderpositions_amount").val();
		var itemID = $("#change_orderpositions_ItemID").val();
		var price = ($("#change_orderpositions_FCprice_gross").val().replace(/,/g, "."))*1;
		var netto = ($("#change_orderpositions_FCprice_net").val().replace(/,/g, "."))*1;

		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderItemUpdate", SELECTOR_id:orderitemid, order_id:orderid, amount:amount, item_id:itemID, price:price, netto:netto },
			function(data)
			{

				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#change_orderpositions_dialog").dialog("close");
					order_update_dialog(orderid, "update");
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}
	

	function change_orderpositions_changeMPN()
	{
		wait_dialog_show();
		var MPN = $("#change_orderpositions_MPN").val();
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "ShopItemGet", MPN:MPN},
			function(data)
			{
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#change_orderpositions_ItemID").val($xml.find("id_item").text());
					$("#change_orderpositions_ArtBez").text($xml.find("title").text());
				}
				else
				{
					$("#change_orderpositions_ItemID").val(0);
					$("#change_orderpositions_ArtBez").text("ARTIKEL EXISTIERT NICHT!");
					$("#change_orderpositions_MPN").focus();
				}
			}
		);
	}

	function change_orderpositions_setPrices(netto)
	{
		//IF FUNCTION  CALL from amount
		if (netto == "amount") netto = $("#change_orderpositions_price_net").val();
		
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		//BERECHNUNGEN LAUFEN IMMER VOM EINZELPREIS EUR NETTO
		var net = (netto.toString().replace(/,/g, "."))*1;
		
		var amount=$("#change_orderpositions_amount").val()*1;
		
		var exchangerate=$("#change_orderpositions_exchangeratetoEUR").val()*1;
	
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
			
			$("#change_orderpositions_FCprice_gross").val(FCgross.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_FCprice_net").val(FCnet.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_price_gross").val(gross.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_price_net").val(net.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_FCpriceTotal_gross").text(FCgrossTotal.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_FCpriceTotal_net").text(FCnetTotal.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_priceTotal_gross").text(grossTotal.toFixed(2).toString().replace(".", ","));
			$("#change_orderpositions_priceTotal_net").text(netTotal.toFixed(2).toString().replace(".", ","));
	}
	
	function get_netto_from_brutto(brutto)
	{
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		var gross=(brutto.replace(/,/g, "."))*1;
		
		if (gross!=0) var netto = gross/mwstmultiplier; else var netto = 0;
		
		change_orderpositions_setPrices(netto);
		
	}
	
	function get_netto_from_FCnetto(FCnetto)
	{
		var exchangerate=$("#change_orderpositions_exchangeratetoEUR").val()*1;
		var FCnet=(FCnetto.replace(/,/g, "."))*1;

		if (FCnet!=0) var netto = FCnet/exchangerate; else var netto = 0;
		alert(netto);
		change_orderpositions_setPrices(netto);
		
	}
	
	function get_netto_from_FCbrutto(FCbrutto)
	{
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		var exchangerate=$("#change_orderpositions_exchangeratetoEUR").val()*1;
		var FCgross=(FCbrutto.replace(/,/g, "."))*1;
		
		if (FCgross!=0) var netto = FCgross /exchangerate/ mwstmultiplier; else var netto = 0;
		change_orderpositions_setPrices(netto);
		
	}
	
	
	function change_ordershipping_setPrices(netto)
	{
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		var exchangerate=$("#change_ordershipping_exchangeratetoEUR").val()*1;
		
		var net = (netto.toString().replace(/,/g, "."))*1;

		var gross = net*mwstmultiplier;
		var netFC = net*exchangerate;
		var grossFC = net*exchangerate*mwstmultiplier;
		
		$("#change_ordershipping_shipping_costsFCgross").val(grossFC.toFixed(2).toString().replace(".", ","));
		$("#change_ordershipping_shipping_costsFCnet").val(netFC.toFixed(2).toString().replace(".", ","));
		$("#change_ordershipping_shipping_costsEURgross").val(gross.toFixed(2).toString().replace(".", ","));
		$("#change_ordershipping_shipping_costsEURnet").val(net.toFixed(2).toString().replace(".", ","));
		
	}
	
	function get_netto_from_FCbrutto_shipping(FCbrutto_shipping)
	{
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		var exchangerate=$("#change_ordershipping_exchangeratetoEUR").val()*1;
		
		var FCgross=(FCbrutto_shipping.replace(/,/g, "."))*1;
		if (FCgross!=0) var netto = FCgross / exchangerate / mwstmultiplier;
		
		change_ordershipping_setPrices(netto);
	}

	function get_netto_from_FCnetto_shipping(FCnetto_shipping)
	{
		var exchangerate=$("#change_ordershipping_exchangeratetoEUR").val()*1;
		
		var FCnetto=(FCnetto_shipping.replace(/,/g, "."))*1;
		if (FCnetto!=0) var netto = FCnetto / exchangerate;
		
		change_ordershipping_setPrices(netto);
	}
	
	function get_netto_from_brutto_shipping(brutto_shipping)
	{
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		
		var brutto=(brutto_shipping.replace(/,/g, "."))*1;
		if (brutto!=0) var netto = brutto / mwstmultiplier;
		
		change_ordershipping_setPrices(netto);
	}
	
	function add_orderpositions(parentorderid, shop_id)
	{
		var combined_with=0;

		//FELDER LEEREN
		$("#change_orderpositions_MPN").val("");
		$("#change_orderpositions_amount").val("0");
		$("#change_orderpositions_ArtBez").val("");

		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_order_detail", OrderID:parentorderid},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					var currency = $xml.find("Currency_Code").text();
					$("#change_orderpositions_Currency").val(currency);
					//GET EXCHANGERATE
					var exchangerate=Currencies[currency]["exchange_rate_to_EUR"];
					$("#change_orderpositions_exchangeratetoEUR").val(exchangerate);
					//FOREIGN CURRENCY
					$(".change_orderpositions_currency").text(currency);
		//			$("#currency_singleprice").text(currency);
		//			$("#currency_totalprice").text(currency);
		//			$("#change_orderpositions_price").val("0,00");
		//			$("#change_orderpositions_PriceTotal").text("0,00");

					$("#change_orderpositions_amount").val("0");

					$("#change_orderpositions_FCprice_gross").val("0,00");
					$("#change_orderpositions_FCprice_net").val("0,00");
					$("#change_orderpositions_price_gross").val("0,00");
					$("#change_orderpositions_price_net").val("0,00");
					$("#change_orderpositions_FCpriceTotal_gross").text("0,00");
					$("#change_orderpositions_FCpriceTotal_net").text("0,00");
					$("#change_orderpositions_priceTotal_gross").text("0,00");
					$("#change_orderpositions_priceTotal_net").text("0,00");

							
					//EUR
					if (currency=="EUR") {$(".change_orderpositions_colEUR").hide();} else {$(".change_orderpositions_colEUR").show();}
	//				if (currency=="EUR") {$(".change_orderpositions_EUR_col").hide();} else {$(".change_orderpositions_EUR_col").show();}
	//				$("#change_orderpositions_priceEUR").val("0,00");
	//				$("#change_orderpositions_PriceTotalEUR").text("0,00");

					combined_with = $xml.find("combined_with").text();
					/*
					$xml.find("Item").each(
					function()
					{
						exchangerate=$(this).find("OrderItemExchangeRateToEUR").text()*1;
						$("#change_orderpositions_exchangeratetoEUR").val($(this).find("OrderItemExchangeRateToEUR").text());
						$("#change_orderpositions_Currency").val($(this).find("OrderItemCurrency_Code").text());
					});
					*/
				}
				else
				{
					show_status2(data);
					return;
				}
			}
		);


		$("#change_orderpositions_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { add_orderpositions_save(parentorderid, shop_id, combined_with);} },
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Bestellpositionen hinzufügen",
			width:800
		});		
	}

	function add_orderpositions_save(parentorderid, shop_id, combined_with)
	{
	//	parentorderid=1749450;
		
		var mwstmultiplier=<?php echo (UST/100)+1; ?>;
		
		var item_id = $("#change_orderpositions_ItemID").val();
			if (item_id=="" || item_id==0) { alert("Es muss ein Artikel angegeben werden"); return;}
		var amount = $("#change_orderpositions_amount").val();
			if (amount=="" || amount==0) { alert("Es muss eine Stückzahl (größer 0) angegeben werden"); return;}
		var price = $("#change_orderpositions_FCprice_gross").val().replace(/,/g, ".")*1;
		var netto = $("#change_orderpositions_FCprice_net").val().replace(/,/g, ".")*1;

		/*
			if (price=="" || price==0) 
			{
				price = 0;
				var netto = 0;
			}
			else
			{
				var netto = (price/mwstmultiplier).toFixed(2);
			}
		*/
		var Currency_Code = $("#change_orderpositions_Currency").val();
		var exchange_rate_to_EUR = $("#change_orderpositions_exchangeratetoEUR").val();
		
		//IF SHOPTYPE = EBAY -> ITEM wird mit neuer Order angelegt und miteinander kombiniert
		var id_order=parentorderid;
		if (Shop_Shops[shop_id]["shop_type"]==2)
		{
			id_order=0;
			
			//zu erstellende Order wird kombiniert
			if (combined_with<1) combined_with=parentorderid;
		
			$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderAdd", mode:"copy", id_order:parentorderid, foreign_OrderID:"", shipping_costs:0, shipping_net:0, combined_with:combined_with},
				function(data)
				{
					//alert(data);
					var $xml=$($.parseXML(data));
					var Ack = $xml.find("Ack").text();
					if (Ack=="Success") 
					{
						var id_order=$xml.find("id_order").text();
						//UPDATE PARENT ORDER -> COMBINED_WITH
						$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:parentorderid, combined_with:combined_with},
						function(data)
						{
							//alert(data);
							var $xml=$($.parseXML(data));
							var Ack = $xml.find("Ack").text();
							if (Ack=="Success") 
							{
							
								// INSERT ORDERITEM
								$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderItemAdd", mode:"new", order_id:id_order, item_id:item_id, amount:amount, price:price, netto:netto, Currency_Code:Currency_Code, exchange_rate_to_EUR:exchange_rate_to_EUR},
								function(data)
								{
									//alert(data);
									var $xml=$($.parseXML(data));
									var Ack = $xml.find("Ack").text();
									if (Ack=="Success") 
									{
										show_status("Bestellposition wurde erfolgreich eingefügt");
										$("#change_orderpositions_dialog").dialog("close");
										order_update_dialog(orderid, "update");
										return;
									}
									else
									{
									}
								});
							}
							else
							{
							}
						});
						
						
					}
					else
					{
					}
				}
			);
		} // IF EBAYORDER
		else
		//SHOP ORDER
		{
			$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderItemAdd", mode:"new", order_id:id_order, item_id:item_id, amount:amount, price:price, netto:netto, Currency_Code:Currency_Code, exchange_rate_to_EUR:exchange_rate_to_EUR},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					show_status("Bestellposition wurde erfolgreich eingefügt");
					$("#change_orderpositions_dialog").dialog("close");
					order_update_dialog(id_order, "update");
					return;
				}
				else
				{
				}
			});
		}

	}
	
//	function return_add_dialog(orderid)

	
	
</script>		


<?php	

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_crm_index.php">CRM</a>';
	echo ' > CRM-Orders';
	echo '</p>';
	
	echo '<p>';
	echo '<h1>CRM-Orders</h1>';
	echo '</p>';
	//CHANGE ORDER POSTIONS DIALOG
	echo '<div id="change_orderpositions_dialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
	echo '	<th>MPN</th>';
	echo '	<td colspan="2"><input type="text" id="change_orderpositions_MPN" size="8" / onchange="change_orderpositions_changeMPN();">';
	echo '	<input type="hidden" id="change_orderpositions_ItemID" /></td>';
	echo '</tr><tr>';
	echo '	<th>Artikelbezeichnung</th>';
	echo '	<td style="background-color:#fff" colspan="2"><span id="change_orderpositions_ArtBez"></td>';
	echo '</tr><tr>';
	echo '	<th>Anzahl</th>';
	echo '	<td colspan="2"><input type="text" id="change_orderpositions_amount" size="2" onchange="change_orderpositions_setPrices(\'amount\');" /></td>';
	echo '</tr><tr>';
	echo '	<th></th><th>Brutto</th><th>Netto</th>';
	echo '</tr><tr>';
	echo '	<th>Einzel-VK</th>';
	echo '	<td><span class="change_orderpositions_currency"></span>&nbsp;<input type="text" id="change_orderpositions_FCprice_gross" size="8" onchange="get_netto_from_FCbrutto(this.value);" /></td>';
	echo '	<td><span class="change_orderpositions_currency"></span>&nbsp;<input type="text" id="change_orderpositions_FCprice_net" size="8" onchange="get_netto_from_FCnetto(this.value);" /></td>';
	echo '</tr>';
	echo '<tr class="change_orderpositions_colEUR">';
	echo '	<th>Einzel-VK</th>';
	echo '	<td>EUR&nbsp;<input type="text" id="change_orderpositions_price_gross" size="8" onchange="get_netto_from_brutto(this.value);" /></td>';
	echo '	<td>EUR&nbsp;<input type="text" id="change_orderpositions_price_net" size="8" onchange="change_orderpositions_setPrices(this.value);" /></td>';
	echo '</tr><tr>';
	echo '	<th>Gesamt-VK</th>';
	echo '	<td style="background-color:#fff"><span class="change_orderpositions_currency"></span>&nbsp;<span id="change_orderpositions_FCpriceTotal_gross">0,00</span></td>';
	echo '	<td style="background-color:#fff"><span class="change_orderpositions_currency"></span>&nbsp;<span id="change_orderpositions_FCpriceTotal_net">0,00</span></td>';
	echo '</tr>';
	echo '<tr class="change_orderpositions_colEUR">';
	echo '	<th>Gesamt-VK</th>';
	echo '	<td style="background-color:#fff">EUR&nbsp;<span id="change_orderpositions_priceTotal_gross">0,00</span></td>';
	echo '	<td style="background-color:#fff">EUR&nbsp;<span id="change_orderpositions_priceTotal_net">0,00</span></td>';
	echo '</tr>';
	
		echo '<input type="hidden" id="change_orderpositions_exchangeratetoEUR"; />';
		echo '<input type="hidden" id="change_orderpositions_Currency"; />';
	echo '</table>';
	echo '</div>';
	
	//CHANGE ORDER SHIPPING DIALOG
	echo '<div id="change_ordershipping_dialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
	echo '	<th>Versandart</th>';
	echo '	<td colspan="2">';
	echo '		<select id="change_ordershipping_shipping_type_id" size="1">';
	echo '			<option value=0>Bitte Versandart auswählen</option>';
	$res_shipping=q("SELECT * FROM shop_shipping_types;", $dbshop, __FILE__, __LINE__);
	while ($row_shipping=mysqli_fetch_array($res_shipping))
	{
		echo '			<option value='.$row_shipping["id_shippingtype"].'>'.$row_shipping["title"].'</option>';
	}
	echo '		</select>';
	echo '	</td>';
	echo '</tr><tr>';
	echo '	<th></th><th>brutto</th><th>netto</th>';
	echo '</tr><tr>';
	echo '	<th>Versandkosten&nbsp;<span id="currency_shipping_costs"></span></th>';
	echo '	<td>';
	echo '		<input type="text" id="change_ordershipping_shipping_costsFCgross" size="6" value="0,00" onchange="get_netto_from_FCbrutto_shipping(this.value);"/>';
	echo '	</td>';
	echo '	<td>';
	echo '		<input type="text" id="change_ordershipping_shipping_costsFCnet" size="6" value="0,00" onchange="get_netto_from_FCnetto_shipping(this.value);"/>';
	echo '	</td>';
	echo '</tr>';
	echo '<tr class="change_ordershipping_EUR_col">';
	echo '	<th>Versandkosten&nbsp;EUR</th>';
	echo '	<td>';
	echo '		<input type="text" id="change_ordershipping_shipping_costsEURgross" size="6" value="0,00" onchange="get_netto_from_brutto_shipping(this.value);" />';
	echo '</td><td>';
	echo '		<input type="text" id="change_ordershipping_shipping_costsEURnet" size="6" value="0,00" onchange="change_ordershipping_setPrices(this.value);" /></td>';
	echo '	</td>';
	echo '		<input type="hidden" id="change_ordershipping_shop_id" />';
	echo '		<input type="hidden" id="change_ordershipping_exchangeratetoEUR" />';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

	echo '<div id="find_customer_dialog" style="display:none";>';
	echo '<input type="text" size ="60" id="find_customer_qry_string" />';
	echo '<input type="hidden" id="find_customer_dialog_orderid" />';
	echo '<input type="hidden" id="find_customer_dialog_shop_id" />';
	echo '<button id="find_customer_btn" onclick="find_customer()">Suchen</button><br>';
	echo '<div id="customer_select_dialog" style="height:500px; overflow:auto;"></div>';
	echo '</div>';



	//VIEW
	//NAVIGATION
	echo '<div class="border_standard">';
	
	echo '<div id="navigation" style="display:none; width:1200px">';
	echo '	<span class="tabs" id="PageBack" style="width:120px; text-align:center"></span>';
	echo '	<span class="tabs" id="PageSelect" style="width:250px; text-align:center"></span>';
	echo '	<span class="tabs" id="pageRange" style="width:150px; text-align:center"></span>';
	echo '	<span class="tabs" id="ResultsBox" style="width:300px; text-align:center"></span>';
	echo '	<span class="tabs" id="PageForward" style="width:120px; text-align:center"></span>';
	echo '	<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Neue Bestellung erfassen" title="Neue Bestellung erfassen" onclick="order_add();"/>';
	echo '</div>';
	echo '<br style="clear:both" />';

	//FILTER
	echo '<div id="navigation" style="display:inline; width:1200px">';
	echo '	<span class="tabs">Plattform: ';
	echo '		<select id="FILTER_Platform" size="1" onChange="set_FILTER_Platform();">';
	echo '			<option value="0">Alle</option>';
	$res_shops=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
	while ($row_shops=mysqli_fetch_array($res_shops))
	{
		echo '	<option value='.$row_shops["id_shop"].'>'.$row_shops["title"].'</option>';
	}
	echo '		</select>';
	echo '	</span>';

	echo '	<span class="tabs">';
	echo '		<select id="FILTER_Country" size="1" onChange="set_FILTER_Country();">';
	echo '			<option value="">Alle Länder</option>';
	echo '			<option value="DE">Deutschland</option>';
	echo '			<option value="international">International</option>';
	echo '		</select>';
	echo '	</span>';
	
	echo '	<span class="tabs">Status: ';
	echo '		<select id="FILTER_Status" size="1" onChange="set_FILTER_Status();">';
	echo '				<option value="0">Alle</option>';
	echo '			<optgroup label="Zahlungs-&Versandstatus">';
	echo '				<option value="1">nicht bezahlt</option>';
	echo '				<option value="2">bezahlt & Kein Auftrag geschrieben</option>';
	echo '				<option value="3">Auftrag geschrieben & nicht versand</option>';
	echo '				<option value="4">versand beauftragt & versand</option>';
	echo '				<option value="5">Bestellung abgebrochen</option>';
	echo '			</optgroup>';
	echo '			<optgroup label="Fahrzeugdaten">';

	echo '				<option value="10">Fz.-Anfragemail gesendet</option>';
	echo '				<option value="11">Fz.-Anfragemail nicht gesendet</option>';
	echo '			</optgroup>';
	echo '			<optgroup label="Versandart">';
	echo '				<option value="20">Express</option>';
	echo '				<option value="21">DPD</option>';
	echo '				<option value="22">DHL</option>';
	echo '			</optgroup>';
	echo '			<optgroup label="Bestellungscheck Status">';
	echo '				<option value="30">Versendbare Bestellungen</option>';
	echo '			</optgroup>';
	echo '		</select>';
	echo '	</span>';
	echo '	<span class="tabs">Suche nach: ';
	echo '		<select id="FILTER_SearchFor" size="1" onChange="set_FILTER_SearchFor();">';
	echo '			<option value="1">E-Mail</option>';
	echo '			<option value="2">Ebay-Mitgliedsname</option>';
	echo '			<option value="3">Name</option>';
	echo '			<option value="4">Adresse</option>';
	echo '			<option value="5">MAPCO Artikelnummer</option>';
	echo '			<option value="6">Ebay-Artikelnummer</option>';
//	echo '			<option value="7">PayPal-Transaction-ID</option>';
	echo '			<option value="8">Order ID</option>';
	echo '			<option value="9">Versand Tracking-ID</option>';
	echo '		</select>';
	if (isset($_GET["FILTER_Searchfield"]))
	{
		echo '		<input type="text" size="20" id="FILTER_Searchfield" value="'.$_GET["FILTER_Searchfield"].'" />';
	}
	else
	{
		echo '		<input type="text" size="20" id="FILTER_Searchfield" />';
	}
	echo '	</span>';
	echo '	<span class="tabs" id="FILTER_Date" style="width:350px; text-align:center">';
	echo '		Datum von <input type="text" id="date_from" size="8" onchange="date_from=this.value;" />';
	echo '		&nbsp;bis&nbsp;<input type="text" id="date_to" size="8" onchange="date_to=this.value;" />';
	echo '	</span>';
	echo '	<span>';
	echo '		<button id="SearchButton" onclick="view_box();">Suchen</button>';
	echo '	</span>';
	echo '</div>';
echo '</div>'; 
	echo '<br style="clear:both" />';

	echo '<div id="tableview" style="display:inline;">';
	echo '</div>';
	
	
	echo '<div id="viewbox" style="display:inline;">';
	echo '</div>';
	
	echo '<div id="dialogbox" style="display:none"></div>';
	
	//UPDATE ITEMVEHICLE DIALOG
	echo '<div id="update_vehicleDialog" style="display:none;"></div>';

	//UPDATE NOTE DIALOG
	echo '<div id="update_NoteDialog" style="display:none;">';
	echo '<textarea id="update_NoteDialogNote" cols="80" rows="5"></textarea>';
	echo '</div>';

	//UPDATE PAYMENT DIALOG
	echo '<div id="update_PaymentDialog" style="display:none;">';
	echo '<table>';
	echo '<tr id="update_Payment_mode_view" style="display:none">';
	echo '	<td>Zahlungseingang / Zahlungserstattung:</td>';
	echo '	<td>';
	echo '		<select size="1" id="paymentsmodeselect">';
	echo '			<option value="payment">Zahlungseingang</option>';
	echo '			<option value="refund">Zahlungserstattung</option>';
	echo '		</select>';
	echo '	</td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Zahlungsart:</td>';
	echo '	<td id="paymentsselect"></td>';
	echo '</tr>';
	echo '<tr id="update_Payment_Transaction_row" style="display:none">';
	echo '	<td>Transaction ID</td>';
	echo '	<td><input type="text" size="20" id="update_Payment_TransactionID" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Gezahlte Summe</td>';
	echo '	<td><input type="text" size="10" id="update_Payment_amount" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Datum</td>';
	echo '	<td><input type="text" size="10" id="update_Payment_Date" value="'.date("d.m.Y").'" /></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

	//UPDATE SHIPPING STATUS
	echo '<div id="update_order_stateDialog" style="display:none">';
	echo 'Tracking ID:&nbsp;<input type="text" id="update_order_state_shipping_number" size="15" />';
	echo '</div>';

	//DHL RETOUR LABEL 
	echo '<div id="DHLretourlabelDialog" style="display:none">';
	echo '<table>';
	/*
	echo '<tr>';
	echo '	<td colspan="2">Firma<br />';
	echo '	<input type="text" size="50" id="DHLretourlabel_address_company" /></td>';
	echo '</tr>';
	*/
	echo '<tr>';
	echo '	<td colspan="2">Adresszusatz / Postnummer<br />';
	echo '	<input type="text" size="50" id="DHLretourlabel_address_additional" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Vorname<br />';
	echo '	<input type="text" size="20" id="DHLretourlabel_address_firstname" /></td>';
	echo '	<td>Nachname<br />';
	echo '	<input type="text" size="20" id="DHLretourlabel_address_lastname" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Straße / Packstation<br />';
	echo '	<input type="text" size="20" id="DHLretourlabel_address_street" /></td>';
	echo '	<td>Nummer / Packstat.Nr.<br />';
	echo '	<input type="text" size="3" id="DHLretourlabel_address_number" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Postleitzahl<br />';
	echo '	<input type="text" size="6" id="DHLretourlabel_address_zip" /></td>';
	echo '	<td>Stadt<br />';
	echo '	<input type="text" size="20" id="DHLretourlabel_address_city" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td colspan="2">E-Mail Adresse<br />';
	echo '	<input type="text" size="50" id="DHLretourlabel_usermail" /></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';	
	
	//DHL RETOUR LABEL ID
	echo '<div id="DHLretourlabelIDDialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Retour Label-ID</td>';
	echo '	<td><input type="text" size="30" id="DHLretourlabelID_LabelID" /></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';	
	
	//Order Add Dialog
	echo '<div id="order_add_dialog" style="display:none">';
	echo '<b>Für welchen Shop soll die neue Bestellung angelegt werden:<br />';
	echo '<select id="order_add_shop" size="1">';
	$res_shops=q("SELECT * FROM shop_shops WHERE id_shop IN (1,2,7,8);", $dbshop, __FILE__, __LINE__);
	echo '	<option value=0>Bitte Shop wählen</option>';
	while ($row_shops=mysqli_fetch_array($res_shops))
	{
		echo '	<option value='.$row_shops["id_shop"].'>'.$row_shops["title"].'</option>';
	}
	echo '</select>';
	echo '</div>';
	

	//SEND ORDER DIALOG
	echo '<div id="send_order_dialog" style="display:none">';
	echo '	<div id="send_order_dialog_addressBox"></div>';
	echo '	<div id="send_order_dialog_PaymentBox"></div>';
	echo '	<div id="send_order_dialog_OrderBox"></div>';
	echo '</div>';
	
	//PRICE ALERT DIALOG
	echo '<div id="price_alert_dialog" style="display:none">';
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
	echo '	<input type="text" size="5" id="update_shipping_address_number" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Postleitzahl<br />';
	echo '	<input type="text" size="10" id="update_shipping_address_zip" /></td>';
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
	echo '	</td>';
	echo '</tr><tr>';
	echo '	<td colspan="2">Telefon<br /><input type="text" size="50" id="update_shipping_userphone" /></td>';
	echo '</tr><tr>';
	echo '	<td colspan="2">E-Mail<br /><input type="text" size="50" id="update_shipping_usermail" /></td>';
	echo '</tr>';
	echo '<input type="hidden" id="update_shipping_customer_id" />';
	echo '<input type="hidden" id="update_shipping_shop_id" />';
	echo '</table>';
	echo '</div>';
	
	
	//RETURNS
	echo '<div id="ReturnAddDialog" style="display:none;">';
	echo '<fieldset style="background-color:#ffdddd">';
	echo '<table>';
	
	echo '	<tr>';
	echo '		<td>Plattform</td>';
	echo '		<td><select name="platform" size="1" id="ReturnAdd_platform" onchange="set_add_relations_platform()"/>';
	echo '			<option value="">Bitte Verkaufsplattform wählen</option>';
		$results=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($results))
		{
			echo '			<option value='.$row["id_shop"].'>'.$row["title"].'</option>';
		}
	echo ' 		</select>';
//	echo '<img id="ReturnAdd_GetEbayData" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right; display:none;" src="images/icons/16x16/rightarrow.png" alt="Daten aus Ebayverkäufen ziehen" title="Daten aus Ebayverkäufen ziehen" onclick="getEbayData_Dialog();">';
	echo '</td>';
	echo '		<td>Vorgang</td>';
	echo '		<td><select name="rAction" size="1" id="ReturnAdd_rAction" onchange="set_add_relations_rAction()"/>';
	echo '			<option value="">Bitte Vorgangsart wählen</option>';
		$results=q("SELECT * FROM shop_returns_rAction;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) ) {
			echo '			<option value='.$row["ID"].'>'.$row["rAction"].'</option>';
		}
	echo '		</select></td>';
	echo '	</tr>';
	
	echo '	<tr>';
	echo '		<td>Käufername</td>';
	echo '		<td><input type="text" name="buyerName" size="35" id="ReturnAdd_buyerName")/></td>';
	echo '		<td id="t_ReturnAdd_userid1">Käufer ID</td>';
	echo '		<td id="t_ReturnAdd_userid2"><input type="text" name="userid" size="20" id="ReturnAdd_userid" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td style="background-color:#eeffee">Rechnungsnummer</td>';
	echo '		<td style="background-color:#eeffee"><input type="text" name="invoiceID" size="20" id="ReturnAdd_invoiceID"/></td>';
//	echo '		<td>Transaction ID</td>';
	echo '		<input type="hidden" name="transactionID" id="ReturnAdd_transactionID" />';
//	echo '		<td><input type="text" name="transactionID" size="20" id="ReturnAdd_transactionID" disabled="disabled" /></td>';
	echo '		<td>Artikel</td>';
	echo '		<td><input type="text" name="MPU" size="20" id="ReturnAdd_MPU" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Stückzahl</td>';
	echo '		<td><input type="text" name="quantity" size="2" id="ReturnAdd_quantity" value=1 /></td>';
	echo '		<td>Kaufdatum</td>';
	echo '		<td><input type="text" name="date_order"  style="cursor:pointer;" size="10" id="ReturnAdd_date_order" /></td>';
	echo '	</tr>';
	echo '  <tr>';
	echo '		<td>Rückgabegrund</td>';
	echo '		<td><select name="rReason" size="1" id="ReturnAdd_rReason" onchange="set_add_relations_rReason()" />';
	echo '			<option value="">Bitte Rückgabe-/Umtauschgrund wählen</option>';
		$results=q("SELECT * FROM shop_returns_rReason;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) ) {
			echo '			<option value='.$row["ID"].' title='.$row["rDescription"].'>'.$row["rReason"].'</option>';
		}
	echo '		</select></td>';
	echo '		<td id="t_ReturnAdd_rReason_detail1" style="background-color:#eeffee">Rückgabenotizen</td>';
	echo '		<td id="t_ReturnAdd_rReason_detail2" style="background-color:#eeffee"><textarea name="rReason_detail" cols="42" rows="5" id="ReturnAdd_rReason_detail"></textarea></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</fieldset>';
	
	echo '<div id="ReturnAddDialog_exchange" style="display:none;">';
	echo '	<table>';
	echo '  <tr>';
	echo '		<td>Umtauschartikel</td>';
	echo '		<td><input type="text" name="exchange_MPU" size="20" id="ReturnAdd_exchange_MPU" /></td>';
	echo '		<td>Stückzahl</td>';	
	echo '		<td><input type="text" name="exchange_quantity" size="2" id="ReturnAdd_exchange_quantity" /></td>';
	echo '		<td>Umtausch versandt am</td>';
	echo '		<td><input type="text" name="date_exchange_sent"  style="cursor:pointer;" size="10" id="ReturnAdd_date_exchange_sent" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	echo '<table>';
	echo '	<tr>';
	echo '		<td>Datum Fall geöffnet</td>';
	echo '		<td><input type="text" name="date_announced" size="10" style="cursor:pointer;" id="ReturnAdd_date_announced" value="'.date("d.m.Y").'" /></td>';
	echo '		<td>Datum Rücksendung erhalten</td>';
	echo '		<td><input type="text" name="date_return"  size="10" style="cursor:pointer;" id="ReturnAdd_date_return" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datum Erstattung</td>';
	echo '		<td><input type="text" name="date_refund"  size="10" style="cursor:pointer;" id="ReturnAdd_date_refund" onchange=\'$("#ReturnAdd_date_refund_reshipment").val($(this).val());\' /></td>';
	echo '		<td>Erstattungssumme</td>';
	echo '		<td><input type="text" name="refund" size="6" id="ReturnAdd_refund" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datum Erstattung Rücksendekosten am</td>';
	echo '		<td><input type="text" name="date_refund_reshipment"  size="10" style="cursor:pointer;" id="ReturnAdd_date_refund_reshipment" /></td>';
	echo '		<td>Erstattungssumme</td>';
	echo '		<td><input type="text" name="refund_reshipment" size="6" id="ReturnAdd_refund_reshipment" /></td>';
	echo '	</tr>';
	echo '	<tr>';
//	echo '		<td>Datum Gutschrift IDIMS</td>';
//	echo '		<td><input type="text" name="date_r_IDIMS"  size="10" style="cursor:pointer;" id="ReturnAdd_date_r_IDIMS" /></td>';
	echo '		<td>Bearbeitungstatus Rückgabe / Umtausch</td>';
	echo '		<td><select name="state" size="1" id="ReturnAdd_state" onchange="set_add_relations_state()" />';
			$results=q("SELECT * FROM shop_returns_state;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) ) {

			if ($row["ID"]=="open") {echo '<option value='.$row["ID"].' selected="selected">'.$row["state"].'</option>';}
			else {echo '<option value='.$row["ID"].'>'.$row["state"].'</option>';}
		}
	echo '		</select></td>';
	echo '	</tr>';
	echo '<input type="hidden" id="ReturnAdd_orderid" />';
	echo '</table>';
	
	echo '<div id="ReturnAddDialog_demandEbayClosing" style="display:none;">';
	echo '<fieldset>';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Ebay Rückg. angef. x1</td>';
	echo '		<td><input type="text" name="date_demandEbayClosing1"  size="10" style="cursor:pointer;" id="ReturnAdd_date_demandEbayClosing1" /></td>';
	echo '		<td>Ebay Rückg. angef. 2x</td>';
	echo '		<td><input type="text" name="date_demandEbayClosing2"  size="10" style="cursor:pointer;" id="ReturnAdd_date_demandEbayClosing2" /></td>';
	echo '		<td>Ebay-Provision zurück</td>';
	echo '		<td><input type="checkbox" name="ebayFeeRefundOK" id="ReturnAdd_ebayFeeRefundOK" value="1">';	
	echo '	</tr>';	
	echo '</table>';
	echo '</fieldset>';
	echo '</div>';

	echo '</div>';

	
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>