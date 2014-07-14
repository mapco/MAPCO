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
	margin-bottom:0px;Shop_Shops
	margin-left:5px;
	background-color:#eee;
}

.border_standard
{
	border-style:solid;
	border-width:1px;
	border-color:#666;
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
<script src="javascript/crm/OrderReturns.php" type="text/javascript" /></script>
<script src="javascript/crm/OrderPayments.php" type="text/javascript" /></script>

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
	echo 'var jump_to=false;';
}
elseif(isset($_GET["jump_to"]) && $_GET["jump_to"]=="order")
{
	echo 'var ResultPage=1;'."\n";
	echo 'var ResultPages=0;'."\n";
	echo 'var ResultRange=50;'."\n";
	echo 'var Results=0;'."\n";
	echo 'var FILTER_Platform=0;'."\n";
	echo 'var FILTER_Status=0;'."\n";
	echo 'var FILTER_SearchFor=8;'."\n";
	echo 'var FILTER_Searchfield="'.$_GET["orderid"].'";'."\n";
	echo 'var FILTER_Ordertype='.$_GET["order_type"].';'."\n";
	echo 'var date_from="";'."\n";
	echo 'var date_to="";'."\n";
	echo 'var OrderBy="firstmod";'."\n";
	echo 'var OrderDirection="down";'."\n";
	
	echo 'var FILTER_Country="";'."\n";

	echo 'var user_id='.$_SESSION["id_user"]."\n";
	echo 'var jump_to=true;';
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
	echo 'var FILTER_Ordertype=0;'."\n";
	echo 'var date_from="";'."\n";
	echo 'var date_to="";'."\n";
	echo 'var OrderBy="firstmod";'."\n";
	echo 'var OrderDirection="down";'."\n";
	
	echo 'var FILTER_Country="";'."\n";

	echo 'var user_id='.$_SESSION["id_user"]."\n";
	
	echo 'var jump_to=false;';

/*
	if ($_SESSION["id_user"]==22733)
	{
		echo 'var FILTER_Country="international";'."\n";
	}
	elseif ($_SESSION["userrole_id"]==4)
	{
		echo 'var FILTER_Country="national";'."\n";
	}
	else
	{
		echo 'var FILTER_Country="";'."\n";
	}
*/
}
?>

var soa_path='<?php echo PATH; ?>soa/';
var soa_path2='<?php echo PATH; ?>soa2/';

var mwstmultiplier=<?php echo (UST/100)+1; ?>;

orders = new Object();
//returns = new Array();

//PRELOADED FIELDS
	PaymentTypes = new Object();
	ShipmentTypes = new Object();
	Shop_Shops = new Array();
	DHL_RetourLabelParameter = new Array();
	Seller = new Array();
	Countries= new Array();
	Currencies = new Array();
	OrderTypes = new Array();
	ReturnsReasons = new Object();
	UserSites = new Array();

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
						msg_box("Das Teil passt.");
						return;
					}
					else
					{
						msg_box('Das Teil passt, wenn folgende Einschränkungen erfüllt sind: '+$Restrictions);
						return;
					}
				}
				else
				{
					msg_box("Das Teil passt NICHT!!!");
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


	function isNumber(n) {
	  return !isNaN(parseFloat(n)) && isFinite(n);
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
		ShipmentTypes[0] = new Object();
		ShipmentTypes[0]["title"]="Kein Versand gewählt";
		ShipmentTypes[0]["description"]="";
		ShipmentTypes[0]["ShippingServiceType"]="";

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "ShippingTypesGet"},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("ShippingType").each(
						function()
						{
							var shippingtype_id=$(this).find("id_shippingtype").text();
							ShipmentTypes[shippingtype_id] = new Object();
							$(this).children().each(
								function()
								{
									var $tagname=this.tagName;
									ShipmentTypes[shippingtype_id][$tagname]=$(this).text();
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
		

	//PRELOAD PAYMENT TYPES
		PaymentTypes[0] = new Object();
		PaymentTypes[0]["title"]="keine Zahlung gewählt";
		PaymentTypes[0]["description"]="";

		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "PaymentTypesGet"},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("PaymentType").each(
						function()
						{
							var paymenttype_id=$(this).find("id_paymenttype").text();
							PaymentTypes[paymenttype_id] = new Object();
							$(this).children().each(
								function()
								{
									var $tagname=this.tagName;
									PaymentTypes[paymenttype_id][$tagname]=$(this).text();
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


	//PRELOAD SHOPS
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "ShopsGet" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("Shop").each(
						function()
						{
							var shop_id=$(this).find("id_shop").text();
							Shop_Shops[shop_id] = new Array();
							$(this).children().each(
								function()
								{
									var $tagname=this.tagName;
									Shop_Shops[shop_id][$tagname]=$(this).text();
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
//alert("countries");
					ready_function();
				}
				else
				{
					show_status2(data);
				}
			}
		);	
		

	//PRELOAD ReturnLabel Parameter
		$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "Get_DHL_Retourlabel_Parameter", store: 0 },

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
	/*
		$.post("<?php echo PATH; ?>soa2/", { API: "cms", APIRequest: "UsersGet", fields:"id_user, name" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("User").each(
						function()
						{
							Seller[$(this).find("id_user").text()] = $(this).find("name").text()
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
	*/
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

		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderTypesGet" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$xml.find("OrderType").each(
						function()
						{
							var id_ordertype=$(this).find("id_ordertype").text();
							OrderTypes[id_ordertype] = new Array();
							$(this).children().each(
								function()
								{
									var $tagname=this.tagName;
									OrderTypes[id_ordertype][$tagname]=$(this).text();
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

	//PRELOAD RETURN REASONS
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderReturnReasonsGet" },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					var i=0;
					$xml.find("ReturnReason").each(
						function()
						{
							var id_returnreason=$(this).find("id_returnreason").text();
							//ReturnsReasons[i] = new Object();
							ReturnsReasons[id_returnreason] = new Object();
							$(this).children().each(
								function()
								{
									var $tagname=this.tagName;
									//ReturnsReasons[i][$tagname]=$(this).text();
									ReturnsReasons[id_returnreason][$tagname]=$(this).text();
								}
															
							);
							i++;
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

	//PRELOAD UserSites
		$.post("<?php echo PATH; ?>soa2/", { API: "cms", APIRequest: "UsersSitesGet", user_id:user_id },
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					var i=0;
					$xml.find("site_id").each(
						function()
						{
							var site_id=$(this).text();
							UserSites.push(site_id);
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
		var $percent=Math.round(preloadcount/10*100);
		wait_dialog_show("Cache wird gefüllt", $percent);
		if (preloadcount==9) 
		{
			draw_navigation();
			//view_box();
			if (jump_to)
			{
				view_box(<?php echo $_GET["orderid"]; ?>);
			}
			else
			{
				$("#tableview").html("<b>Zur Anzeige bitte Suchfilter nutzen</b>");
			}
			wait_dialog_hide();
		}
	}


	$(function() {  
	
		// ENTER FÜR SUCHE  
		$("#FILTER_Searchfield").bind("keypress", function(e) {
			if(e.keyCode==13){
				FILTER_Searchfield=$("#FILTER_Searchfield").val();
				view_box();
			}
		});
		
		// FOCUS AUTOM. AUF SUCHFELD
		$("#FILTER_Searchfield").focus();				
		
	} );
	
	$(function() {    
		$("#find_customer_qry_string").bind("keypress", function(e) {
			if(e.keyCode==13){
				find_customer();
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
		$("#FILTER_Ordertype").val(FILTER_Ordertype);
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
		$("#order_send_dialog_order_send_btn").button("disable");
		
		if(typeof approved == 'undefined') approved = "false";
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API:"idims", APIRequest:"OrderSend", id_order:$id_order, approved: approved }, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $xml.find("Error").length>0 )
			{
				var $Code=$xml.find("Error Code").text();
				var $shortMsg=$xml.find("Error shortMsg").text();
				var $longMsg=$xml.find("Error longMsg").text();
				alert("Fehler "+$Code+"\n\n"+$longMsg);
				return;
			}
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			if($xml.find("Code").text()=="collateral_alert" && (<?php echo $_SESSION["id_user"]; ?>==21371 || <?php echo $_SESSION["id_user"]; ?>==22044))
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
			$("#order_send_dialog").dialog("close");
			show_status("Auftrag erfolgreich im IDIMS eingetragen.");

			update_view($id_order);
			return;
		});
	}


	function order_send2($id_order)
	{
//		alert($id_order);
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

			$("#order_send_dialog").dialog("close");
			show_status("Auftrag erfolgreich im IDIMS eingetragen.");
			update_view($id_order);
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
		
		// FILTER FOR TOBIAS, ANDREAS, ANDY
		if (user_id == 30719 || user_id == 28623 || user_id == 29115)
		{
			if (FILTER_Platform==3)
			{
				FILTER_Country="national";
				$("#FILTER_Country").val("national");
			}
			else
			{
				FILTER_Country="";
				$("#FILTER_Country").val("");
			}
		}
		// FILTER FOR KAI
		if (user_id == 22733)
		{
			if (FILTER_Platform==3)
			{
				FILTER_Country="international";
				$("#FILTER_Country").val("international");
			}
			else
			{
				FILTER_Country="";
				$("#FILTER_Country").val("");
			}
		}
		//FILTER FOR IVAN
		if (user_id == 88838)
		{
			if (FILTER_Platform==3)
			{
				FILTER_Country="ES+PT";
				$("#FILTER_Country").val("ES+PT");
			}
			else
			{
				FILTER_Country="";
				$("#FILTER_Country").val("");
			}
		}
		//FILTER FOR ALBERTO
		if (user_id == 92606)
		{
			if (FILTER_Platform==3 || FILTER_Platform==1)
			{
				FILTER_Country="IT";
				$("#FILTER_Country").val("IT");
			}
			else
			{
				FILTER_Country="";
				$("#FILTER_Country").val("");
			}
		}


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
	
	function set_FILTER_Ordertype()
	{
		FILTER_Ordertype=$("#FILTER_Ordertype").val();
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
	
/*
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
								
							default:
								returns[$tagname]=$(this).text();
								break;
						}
					}
				);
			}
		);
		
		
	}
*/

	function update_order_array(data)
	{
		var $xml=$($.parseXML(data));
		$xml.find("Order").each(
			function()
			{
				
				var order_id = $(this).find("id_order").text();
				//delete orders[order_id];
				orders[order_id] = new Object();
				var z = 0;
				
				$(this).children().each(
					function()
					{
						var $tagname=this.tagName;
						
						switch ($tagname)
						{
							case "OrderItems": 
								orders[order_id]["Items"] = new Object();
								var i=0;
								$(this).find("Item").each(
								function ()
								{
									orders[order_id]["Items"][i] = new Object();
									$(this).children().each(
									function ()
									{
										var $tagname2=this.tagName;
										orders[order_id]["Items"][i][$tagname2] = $(this).text();
									});
									
									
									i++;
								});
								break;
								
							case "returns": 
								orders[order_id]["returns"] = new Object();
								$(this).find("return").each(
								function ()
								{
									var return_id = $(this).find("id_return").text();
									orders[order_id]["returns"][return_id] = new Object();
									$(this).children().each(
									function ()
									{
										var $tagname2=this.tagName;
										if ($tagname2=="returnitems")
										{
											orders[order_id]["returns"][return_id]["returnitem"] = new Object();
											var j=0;
											$(this).find("returnitem").each(
											function ()
											{
												
												orders[order_id]["returns"][return_id]["returnitem"][j] = new Object;
												$(this).children().each(
												function()
												{
													var $tagname3=this.tagName;
													orders[order_id]["returns"][return_id]["returnitem"][j][$tagname3]=$(this).text();
												});
												j++;
											});
										}
										else
										{
											orders[order_id]["returns"][return_id][$tagname2] = $(this).text();
										}
									});
									
								});
								break;
								
								case "order_note":	if ( typeof orders[order_id]["order_notes"] == 'undefined' )
										{
											orders[order_id]["order_notes"] = new Object();
										}
										orders[order_id]["order_notes"][z] = $(this).text();
										z++;
								break; 	
							default:
								orders[order_id][$tagname]=$(this).text();
								break;
						}
					}
				);
			}
		);
		
			//show_status2(print_r(orders));
	}
	
	function update_view(orderid, action)
	{
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_orders_list", mode:"single", order_id:orderid},
			function(data)
			{
//				alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					update_order_array(data);
					draw_ordertable_row(orderid, action);
					
				}
				else
				{
					show_status2(data);
				}
				
			}
		);

	}
	
	function IDIMS_Order_check(orderid)
	{
		//ORDER == BESTELLUNG
		if (orders[orderid]["ordertype_id"] == 1 || orders[orderid]["ordertype_id"] == 2 || orders[orderid]["ordertype_id"] == 3)
		{
			// CHECK, ob Bestellung bereits versandt wurde
			if (orders[orderid]["AUF_ID"]!=0) return false;
	
			//Bestellung bezahlt? (Wenn nötig)
			if (orders[orderid]["PaymentTypeID"]==0) return false;
	
			if (orders[orderid]["Payments_TransactionState"] =="Completed" || orders[orderid]["Payments_TransactionState"] =="OK" ) var paymentOK = true; else  var paymentOK = false;
			if (PaymentTypes[orders[orderid]["PaymentTypeID"]]["ship_at_once"] == 0 && !paymentOK) return false;
	
			//VERSAND GEWÄHLT??
			if (orders[orderid]["shipping_type_id"]==0) return false;
	
			//BESTELLUNG ABGEBROCHEN?
			if (orders[orderid]["status_id"]==4 || orders[orderid]["status_id"]==8) return false;
	
			// ALLE ARTIKEL GECHECKT?		
				//CHECK NUR, WENN shop_type = 2
			if(user_id != 28623)
			{	
				if (Shop_Shops[orders[orderid]["shop_id"]]["shop_type"]==2)
				{
					var items_OK = true;
			
					for (i in orders[orderid]["Items"])
					//for (i=0;i<orders[orderid]["Items"].length; i++)
					{
						if (orders[orderid]["Items"][i]["OrderItemChecked"]!=1) items_OK = false;
					}
					if (!items_OK && orders[orderid]["fz_fin_mail_count"]<3) return false;
				}
			}
		}
		//ANGEBOT?
		if (orders[orderid]["ordertype_id"] == 5) return false;
		
		//UMTAUSCH?
		if (orders[orderid]["ordertype_id"] == 4) 
		{
			//VERSAND GEWÄHLT??
			if (orders[orderid]["shipping_type_id"]==0) return false;
	
			//BESTELLUNG ABGEBROCHEN?
			if (orders[orderid]["status_id"]==4 || orders[orderid]["status_id"]==8) return false;
		}
		
		//ADRESSCHECK
		/*
		{}
		*/
		
		return true;
	}

	
	function draw_ordertable_row(orderid, action)
	{
		show_combined_note(orderid);
		show_COD_note(orderid);
		
		$("#platform_data"+orderid).html(show_platform_data(orderid));
		$("#order_buyer"+orderid).html(show_order_buyer(orderid));
		$("#order_items"+orderid).html(show_order_items(orderid));
		$("#order_state"+orderid).html(show_order_state(orderid));
		$("#vehicle_data"+orderid).html(show_vehicle_data(orderid));
		
		show_PayPal_note(orderid);
		show_order_note(orderid);
		
		$("#action_menu"+orderid).html(draw_actions_menu(orderid));
		
		if (typeof(action)!=='undefined')
		{
			if (action == "order_update_dialog") 
			{
				order_update_dialog(orderid, "update");
			}
			
		}

		
	}

	function draw_actions_menu(orderid)
	{
	
	//IDIMS KNOPF	
		var menu='';
		/*
		if (user_id == 28625)
		{
			//menu+='<a href="javascript:order_exchange_set('+orderid+');">Umtausch anlegen</a><br /><br />';
			menu+='<a href="javascript:order_returns_add('+orderid+');">Rückgabe anlegen</a><br /><br />';
			if (typeof (orders[orderid]["return_id"]) !== "undefined" && orders[orderid]["return_id"]!="")
			{
				menu+='<a href="javascript:order_returns_dialog('+orders[orderid]["return_id"]+');">Rückgabe ansehen</a><br /><br />';
			}
		}
		*/
		if (orders[orderid]["status_id"]==4 || orders[orderid]["status_id"]==8)
		{
			menu+='<small>Bestellung abgebrochen</small><br /><br />';
		}
		else if ((orders[orderid]["PaymentTypeID"]==3 && ShipmentTypes[orders[orderid]["shipping_type_id"]]["expected_paymenttype_id"]!=3) || (orders[orderid]["PaymentTypeID"]!=3 && ShipmentTypes[orders[orderid]["shipping_type_id"]]["expected_paymenttype_id"]==3))

		//if (orders[orderid]["PaymentTypeID"]==3 && orders[orderid]["shipping_type_id"]!=15)
		{
			menu+='<b>Fehler:<br /><small>ungültiger Versand</small><br /><br />';
		}
		//else if (orders[orderid]["AUF_ID"]==0)
		else if (IDIMS_Order_check(orderid))
		{
			menu+='<a href="javascript:order_update_dialog('+orderid+', \'IDIMS\');">IDIMS</a><br /><br />';
			if( <?php echo $_SESSION["id_user"] ?>==21371 )
			{
				menu+='<a href="javascript:order_send2('+orderid+', \'IDIMS\');">IDIMS2</a><br /><br />';

			}
		}
		else if (orders[orderid]["AUF_ID"]!=0)
		{
			menu+='<small>IDIMS-Auftrag<br />bereits erfasst</small><br /><br />';
		}
		else
		{
			menu+='<small>Bestellung nicht vollständig</small><br /><br />';
		}

	//AKTIONS-MENÜ
		menu+='<a href="javascript:show_actions_menu('+orderid+');">Aktionen</a>';
		menu+='<div class="action_menu_options" id="action_menu_options'+orderid+'" style="position:absolute; display:none; background-color:#fff; z-index:100; padding:3px; border:1px solid #999;">';

		menu+='<div style="background-color:#f0faff">';
		menu+='<strong>Bearbeitung</strong>';
		menu+='<ul>';
	
	//UNSET COMBINED WITH
		if (orders[orderid]["combined_with"]>0 && orders[orderid]["status_id"]!=4 && orders[orderid]["status_id"]!=8)
		{
		//	menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:unset_combined_order('+orders[orderid]["combined_with"]+', '+orderid+');">kombinierte Order aufheben</a></li>';
		}
		//menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:set_order_payment('+orderid+');">Zahlung bearbeiten</a></li>';
		menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:payments_dialog_load('+orderid+');">Zahlung bearbeiten</a></li>';
	//AUFTRAG GESCHRIEBEN
		if ( (orders[orderid]["PaymentTypeID"]==1 || orders[orderid]["PaymentTypeID"]==7) && orders[orderid]["shipping_type_id"]!=15 && orders[orderid]["AUF_ID"]==0 && orders[orderid]["status_id"]!=4)
		{
			menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:set_order_state('+orderid+', 2);">Auftrag geschrieben</a></li>';
		}
		
	//BESTELLUNG NACH IDIMS NICHT MEHR BEARBEITBAR -> NUR ANZEIGEN
			//LINK TITLE
			if(orders[orderid]["ordertype_id"]==4) {var link_ordertype="Umtausch";} else {var link_ordertype="Bestellung";}
			
		if (((orders[orderid]["AUF_ID"]==0 || user_id == 22733) && orders[orderid]["status_id"]!=4) || <?php echo $_SESSION["userrole_id"]; ?>==1 )
		{
			menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_update_dialog('+orderid+', \'update\');">'+link_ordertype+' bearbeiten</a></li>';
		}
		else
		{
			menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_update_dialog('+orderid+', \'view\');">'+link_ordertype+' ansehen</a></li>';
		}
		menu+='</ul>';
		menu+='</div>';

		menu+='<div>';
		menu+='<strong>Rückg./Umtausch</strong>';
		menu+='<ul>';
	//RÜCKGABE	
		// check if there is a return
		var $hasReturn=0;
		if (typeof (orders[orderid]["returns"])!=="undefined")
		{
			$.each(orders[orderid]["returns"], function($returnid, $returnfield)
			{
				if ($returnfield["return_type"]=="return")
				{
					$hasReturn = $returnid;
				}
			});
		}
		if ($hasReturn>0)
		{
			if (user_id == 28625)
			{
				menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_returns_dialog2('+$hasReturn+');">Rückgabe ansehen/bearbeiten</a></li>';
			}
			else
			{
				menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_returns_dialog('+$hasReturn+');">Rückgabe ansehen/bearbeiten</a></li>';
			}
		}
		else
		{
			if (orders[orderid]["status_id"]!=4)
			{
				menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_returns_add('+orderid+');">Rückgabefall eröffnen</a></li>';
			}
		}

	
	//EXCHANGE
		var $hasExchange=0;
		if (typeof (orders[orderid]["returns"])!=="undefined")
		{
			$.each(orders[orderid]["returns"], function($returnid, $returnfield)
			{
				if ($returnfield["return_type"]=="exchange")
				{
					$hasExchange = $returnid;
				}
			});
		}
		if ($hasExchange>0)
		{
			menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_returns_dialog('+$hasExchange+');">Umtausch ansehen/bearbeiten</a></li>';
		}
		else
		{
			if (orders[orderid]["status_id"]!=4 && orders[orderid]["status_id"]!=8)
			{
				menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_exchange_add('+orderid+');">Umtauschfall eröffnen</a></li>';
			}
		}
	
		menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:order_returns_partial_dialog();">Teilumtausch eröffnen</a></li>';
	
		menu+='</ul>';
		menu+='</div>';

		menu+='<div style="background-color:#f0faff">';
		menu+='<strong>Sonstige</strong>';
		menu+='<ul>';
	
		
	menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:send_orderConfirmation('+orderid+');">Bestellbestätigung versenden</a></li>';

	//AUFTRAG ABBRECHEN
		if (orders[orderid]["status_id"]!=4 && orders[orderid]["status_id"]!=8)
		{
			menu+='	<li style="margin:10px; margin-left:-22px;"><a href="javascript:order_abort_dialog('+orderid+');">Auftrag abbrechen</a></li>';
		}
	
	//BESTELLVERLAUF
		menu+='<li style="margin:10px; margin-left:-22px;"><a href="javascript:show_order_events('+orderid+');">Bestellverlauf</a></li>';

		menu+='</ul>';
		menu+='</div>';
		
		menu+='</div>';
	
		//KOMMUNIKATION	
		//menu+='<br /><br /><a href="javascript:communication_show('+orderid+');">Kommunikation</a>';
		menu+='<br /><br /><a href="javascript:communication_show('+orderid+');" style="float: left"><img src="<?php echo PATH;?>images/icons/16x16/comments.png" title="Kommunikation"></a><span>&nbsp;'+orders[orderid]["OrderConCntOrder"]+' ('+orders[orderid]["OrderConCntAll"]+')</span>' ;
		menu+='<br /><br /><a href="javascript:user_contact_2('+orderid+');" style="float: left"><img src="<?php echo PATH;?>images/icons/16x16/comment_add.png" title="Kunden kontaktieren"></a>';
		//menu+='<div class="action_menu_options" id="order_events_'+orderid+'" style="position:absolute; display:none; background-color:#fee; z-index:100; padding:3px; border:1px solid #999; height:500px; overflow:auto">blablabla</div>';
				
		return menu;
	}
	
	function communication_show(order_id)
	{
		//if(user_id == 49352)
		{
			var post_object = 			new Object();
			post_object['API'] = 		'crm';
			post_object['APIRequest'] = 'ConversationGet';
			post_object['order_id'] =	order_id;
			
			wait_dialog_show();
			$.post('<?php echo PATH;?>soa2/', post_object, function($data){
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
				if ( $xml.find("Ack").text()!="Success" ) { show_status2('Die Kommunikationsdaten wurden nicht gefunden.'); wait_dialog_hide(); return; }
				
				//Daten einlesen
				var conversation_data = new Array();
				var cnt = 0;
				$xml.find("contact").each(function(){
					conversation_data[cnt] = new Array();
					conversation_data[cnt]["sender"] = $(this).find("con_from").text();
					conversation_data[cnt]["receiver"] = $(this).find("con_to").text();
					conversation_data[cnt]["subject"] = $(this).find("subject").text();
					conversation_data[cnt]["message"] = $(this).find("message").text();
					conversation_data[cnt]["firstmod"] = $(this).find("firstmod").text();
					conversation_data[cnt]["type"] = $(this).find("type").text();
					if($(this).find("con_order_id").text() == order_id)
						conversation_data[cnt]["con_type"] = "order";
					else
						conversation_data[cnt]["con_type"] = "all";
					cnt++;
				});
/*				
				var con_types_data = new Array();
				var cnt2 = 0;
				$xml.find("con_type").each(function(){
					con_types_data[cnt2] = new Array();
					con_types_data[cnt2]["id"] = $(this).find("id").text();
					con_types_data[cnt2]["type"] = $(this).find("c_type").text();
					cnt2++;
				});
*/							
				if( $("#communication_div").length==0 )
				{
					$html  = '<div id="communication_div"></div>';
					$("body").append($html);
				}
				//Dialog bauen
				var ids = new Array();
				var main = $("#communication_div");
				main.empty();
	
				var table = $('<table></table>');
				var tr = '';
				var td = '';
				tr = $('<tr></tr>');
				td = $('<td colspan="5" style="border: none;"</td>');
				var text = $('<p style="display: inline; font-weight: bold;">Anzeige:</p>');
				td.append(text);
				text = $('<p style="display: inline; padding-left: 50px;"><input type="radio" id="r_one" name="order_view" value="all"> alle Kontakte</p>');
				td.append(text);
				text = $('<p style="display: inline; padding-left: 20px"><input type="radio" id="r_two" name="order_view" value="one" checked> Kontakte zur aktuellen Bestellung</p>');
				td.append(text);
				text = $('<span style="float: right;"><input type="button" id="contact_button" value="Kunden kontaktieren"></span>');
				td.append(text);
				tr.append(td);
				table.append(tr);
/*				
				tr = $('<tr></tr>');
				var th = $('<th>Absender</th>');
				tr.append(th);
				th = $('<th>Empfänger</th>');
				tr.append(th);
				th = $('<th>Betreff</th>');
				tr.append(th);
				th = $('<th>Datum</th>');
				tr.append(th);
				th = $('<th>Nachrichtentyp</th>');
				tr.append(th);
				table.append(tr);
*/				
				for(var a in conversation_data)
				{
					var date = new Date(conversation_data[a]["firstmod"]*1000);
					if(conversation_data[a]["con_type"] == 'all')
						tr = $('<tr id="'+a+'_t" class="'+conversation_data[a]["con_type"]+'" style="background-color: #CCCCCC; cursor: pointer; display: none; font-weight: bold;"></tr>');
					else
						tr = $('<tr id="'+a+'_t" class="'+conversation_data[a]["con_type"]+'" style="background-color: #CCCCCC; cursor: pointer; font-weight: bold;"></tr>');
					td = $('<td>'+conversation_data[a]["sender"]+'</td>');
					tr.append(td);
					td = $('<td style="padding-left: 20px;">'+conversation_data[a]["receiver"]+'</td>');
					tr.append(td);
					td = $('<td style="padding-left: 20px;">'+conversation_data[a]["subject"]+'</td>');
					tr.append(td);
					td = $('<td style="padding-left: 20px;">'+date.toLocaleString()+'</td>');
					tr.append(td);
					td = $('<td style="padding-left: 20px;">'+conversation_data[a]["type"]+'</td>');
					tr.append(td);
					table.append(tr);
					
					tr = $('<tr></tr>');
					td = $('<td colspan="5" id="'+a+'" class="'+conversation_data[a]["con_type"]+'_art" style="background-color: #FFFFFF; display: none;">'+conversation_data[a]["message"]+'</td>');
					ids.push(a);
					tr.append(td);
					table.append(tr);
					if(conversation_data[a]["con_type"] == 'all')
						tr = $('<tr class="'+conversation_data[a]["con_type"]+'" style="display: none; height: 10px;"></tr>');
					else
						tr = $('<tr class="'+conversation_data[a]["con_type"]+'" style="height: 10px;"></tr>');
					table.append(tr);
				}
				main.append(table);
				
				$("#communication_div").dialog
				({	buttons:
					[
						{ text: "Schließen", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Kommunikation",
					maxHeight:600,
					width:1000,
					position:['middle',100]
				});
				wait_dialog_hide();
				
				for(n in ids)
				{
					(function(k)
					{
						$('#' + k + '_t').click(
							function()
							{
								//if($(e.target).hasClass('cb')) return;
								$('#' + k).toggle(500);
								//$('[class*="i' + k + 'i"]').hide("fade", 500);
							}
						);
					})(ids[n])
				}
				
				$("#r_one").change(function () {
					$('.all').show("fade", 500);
				});
				
				$("#r_two").change(function () {
					$('.all, .all_art').hide("fade", 500);
				});
				
				$('#contact_button').click(function(){
					//user_contact(order_id, con_types_data);
					user_contact_2(order_id);
				});
			});
		}
	}
	
	function user_contact(order_id, con_types_data, $shop_mail)
	{
		if( $("#contact_div").length==0 )
		{
			$html  = '<div id="contact_div"></div>';
			$("body").append($html);
		}
		
		var main = $('#contact_div');
		main.empty();
		var table = $('<table></table>');
		var tr = $('<tr></tr>');
			var td = $('<td style="font-weight: bold;">Kontaktart:</td>');
			tr.append(td);
			td = $('<td></td>');
			var select_box = $('<select id="con_types_select"></select>');
			var option = '';
			var selected = '';
			for(b in con_types_data)
			{
				if(con_types_data[b]["id"]<5 && con_types_data[b]["id"]!=3)
				{
					if ( con_types_data[b]["id"] == 4 )
					{
						selected = ' selected="selected"';
					}
					else
					{
						selected = '';
					}
					option = $('<option value="'+con_types_data[b]["id"]+'"'+selected+'>'+con_types_data[b]["type"]+'</option>');						
					
					select_box.append(option);
				}
			}
			td.append(select_box);
			tr.append(td);
		table.append(tr);
		tr = $('<tr style="display:none;"></tr>');
			td = $('<td style="font-weight: bold;">Bestellnummer (optional):</td>');
			tr.append(td);
			td = $('<td><input type="text" id="con_order_id" style="width: 400px;" value="'+order_id+'"></td>');
			tr.append(td);
		table.append(tr);
		tr = $('<tr class="con_cols_mail" style=" display:none;"></tr>');
			td = $('<td id="con_label_receiver" style="font-weight: bold;">Empfänger (email-Adr, Tel-Nr...):</td>');
			tr.append(td);
			td = $('<td><input type="text" id="con_receiver" style="width: 400px;" value="'+($('#column_buyer_mail'+order_id).text())+'"></td>');
			tr.append(td);
		table.append(tr);
		tr = $('<tr class="con_cols_mail" style=" display:none;"></tr>');

			td = $('<td style="font-weight: bold;">Absender:</td>');
			tr.append(td);
			td = $('<td><input type="text" id="con_sender" style="width: 400px;" value="'+$shop_mail+'"></td>');
			tr.append(td);
		table.append(tr);
		tr = $('<tr class="con_cols_mail" style="display:none;"></tr>');
			td = $('<td style="font-weight:bold;">Betreff:</td>');
			tr.append(td);
			td = $('<td><input type="text" id="con_subject" style="width: 400px;"></td>');
			tr.append(td);
		table.append(tr);
		tr = $('<tr></tr>');
			if(user_id == 49352)
			{
				td = $('<td style="font-weight: bold;">Nachricht/Text:</td><td><input type="button" value="Vorlage laden"></td>');
			}
			else
				td = $('<td colspan="2" style="font-weight: bold;">Nachricht/Text:</td>');
			tr.append(td);
		table.append(tr);
		tr = $('<tr></tr>');
			td = $('<td colspan="2"><textarea id="con_message" style="height: 200px;resize: none; width: 615px;"></textarea></td>');
			tr.append(td);
		table.append(tr);
		main.append(table);
						
		$("#con_types_select").change(function(){ 
			var value = $(this).val();
			if ( value == 4 )
			{
				$(".con_cols_mail").css('display','none');
			}
			else
			{
				$(".con_cols_mail").css('display','');
				if (value == 1)
				{
					$("#con_label_receiver").html('Email-Adresse');
				}
				else
				{
					$("#con_label_receiver").html('Telefonnummer');
				}
			}
		});
						
		$("#contact_div").dialog
		({	buttons:
			[
				{ text: "Abschicken/Speichern", click: function() { contact_save(order_id); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Neuer Kontakt",
			maxHeight:600,
			width: 666,
			position:['middle',130]
		});
	}
	
	function user_contact_2(order_id)
	{
		//if(user_id == 49352)
		{
			var post_object = 			new Object();
			post_object['API'] = 		'crm';
			post_object['APIRequest'] = 'ConversationGet';
			post_object['order_id'] =	order_id;
			
			wait_dialog_show();
			$.post('<?php echo PATH;?>soa2/', post_object, function($data){
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
				if ( $xml.find("Ack").text()!="Success" ) { show_status2('Die Kommunikationsdaten wurden nicht gefunden.'); wait_dialog_hide(); return; }
				
				//show_status2($data);
				//Daten einlesen	
				var con_types_data = new Array();
				var cnt2 = 0;
				$xml.find("con_type").each(function(){
					con_types_data[cnt2] = new Array();
					con_types_data[cnt2]["id"] = $(this).find("id").text();
					con_types_data[cnt2]["type"] = $(this).find("c_type").text();
					cnt2++;
				});
				var $shop_mail = $xml.find("shop_mail").text();
				
				wait_dialog_hide();
				
				user_contact(order_id, con_types_data, $shop_mail);
			});
		}
	}
	
	function contact_save(order_id)
	{
		if($('#con_types_select').val()=='1')
		{
			//MAIL SEND
			if($('#con_sender').val()=='')
			{
				alert('Bitte einen Absender angeben!');
				return;
			}
			$post_data = new Object;
			$post_data['API'] = 'shop';
			$post_data['APIRequest'] = 'MailUser';
			$post_data['user_id'] = orders[order_id]['customer_id'];
			$post_data['order_id'] = $('#con_order_id').val();
			$post_data['receiver'] = $('#con_receiver').val();
			$post_data['sender'] = $('#con_sender').val();
			$post_data['subject'] = $('#con_subject').val();
			$post_data['message'] = $('#con_message').val(); 
			
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data){
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
				if ( $xml.find("Ack").text()!="Success" ) { show_status2('Die Email konnte nicht gesendet werden.'); wait_dialog_hide(); return; }
				
				$("#communication_div").dialog('close');
				communication_show(order_id);
				$('#contact_div').dialog("close");
				update_view(order_id);				
			});
		}
		else if($('#con_types_select').val()=='2')
		{
			//PHONE-DATA SAVE
			$post_data = new Object;
			$post_data['API'] = 'shop';
			$post_data['APIRequest'] = 'PhoneUser';
			$post_data['user_id'] = orders[order_id]['customer_id'];
			$post_data['order_id'] = $('#con_order_id').val();
			$post_data['receiver'] = $('#con_receiver').val();
			$post_data['sender'] = $('#con_sender').val();
			$post_data['subject'] = $('#con_subject').val();
			$post_data['message'] = $('#con_message').val();
			
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data){
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
				if ( $xml.find("Ack").text()!="Success" ) { show_status2('Das Telefonat konnte nicht gespeichert werden.'); wait_dialog_hide(); return; }
				
				$("#communication_div").dialog('close');
				communication_show(order_id);
				$('#contact_div').dialog("close");
				update_view(order_id);
			});
		}
		else if($('#con_types_select').val()=='3')
			alert('fax');
		else if($('#con_types_select').val()=='4')
		{
			//NOTE SAVE
			$post_data = new Object;
			$post_data['API'] = 'shop';
			$post_data['APIRequest'] = 'NoteUser';
			$post_data['user_id'] = orders[order_id]['customer_id'];
			$post_data['order_id'] = $('#con_order_id').val();
			$post_data['receiver'] = ''; //$('#con_receiver').val();
			$post_data['sender'] = ''; //$('#con_sender').val();
			$post_data['subject'] = $('#con_subject').val();
			$post_data['message'] = $('#con_message').val();
			
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data){
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
				if ( $xml.find("Ack").text()!="Success" ) { show_status2('Die Notiz konnte nicht gespeichert werden.'); wait_dialog_hide(); return; }
				
				$("#communication_div").dialog('close');
				communication_show(order_id);
				$('#contact_div').dialog("close");
				update_view(order_id);
			});
		}
	}
	
	function send_orderConfirmation(orderid)
	{
		show_actions_menu(orderid); //hide options

		$post_data = new Object;
		$post_data['API'] = 'shop';
		$post_data['APIRequest'] = 'MailOrderConfirmation';
		$post_data['order_id'] = orderid;

		$.post('<?php echo PATH;?>soa2/', $post_data, function($data){
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2('Bestellbestätigungsmail konnte nicht versendet werden. Error Code:'+ $xml.find("Code").text()); wait_dialog_hide(); return; }
			
			show_status("Bestellbestätigungsmail erfolgreich versendet!");
			update_view(orderid);
		});
	}
	
	function show_order_events(orderid)
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
			//	html+='<span style="background-color:#999; float:right"><a href="javascript:show_order_events('+orderid+');"><b>[ X ]</a></b></span><br/ >';
				
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
				
				$("#order_events").html(html);
				$("#order_events").dialog
				({	buttons:
					[
						{ text: "OK", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Bestellungsverlauf",
					//overflow:auto,
					maxHeight:600, 
					width:600
				});		
				
				
				
			}
			else
			{
				show_status2(data);
			}

		});

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
			if (orders[orderid]["gewerblich"]==1)
			{
				html+='<br /><span style="background-color:orange"><b>'+orders[orderid]["buyerUserID"]+'</b></span>';
			}
			else
			{
				html+='<br /><span><b>'+orders[orderid]["buyerUserID"]+'</b></span>';
			}
		}
		return html;
	}
	
	
	function show_order_buyer(orderid)
	{
		var html='';
		
		if ((orders[orderid]["bill_adr_id"]==orders[orderid]["ship_adr_id"]) || (orders[orderid]["ship_adr_id"]==0) )
		{
			var secondcolumn=false;
		}
		else
		{
			var secondcolumn=true;
		}
			html+='<table style="width:100%; border:0; width:100%; margin:0px; cellpadding:1px; cellspacing:0px">';
			html+='<tr style="background-color:#ddd;">';
			if (secondcolumn)
			{
				html+='<td style="width:50%; border:0;"><small><b>Rechnungsanschrift</b></small></td><td style="width:50%; border:0;"><small><b>Lieferanschrift</b></small></td>';
			}
			else
			{
				html+='<td style="width:100%;border:0;"><small><b>Rechnungs- & Lieferanschrift</b></small></td>';
			}
			html+='</tr><tr>';
			html+='<td style="border:0;"><small>'+orders[orderid]["bill_company"]+'</small></td>';
			if (secondcolumn) html+='<td style="border:0; "><small>'+orders[orderid]["ship_company"]+'</small></td>';
			html+='</tr><tr>';
			html+='<td style="border:0;"><small>'+orders[orderid]["bill_firstname"]+' '+orders[orderid]["bill_lastname"]+'</small></td>';
			if (secondcolumn) html+='<td style="border:0;"><small>'+orders[orderid]["ship_firstname"]+' '+orders[orderid]["ship_lastname"]+'</small></td>';
			html+='</tr><tr>';
			html+='<td style="border:0;"><small>'+orders[orderid]["bill_additional"]+'</small></td>';
			if (secondcolumn) html+='<td style="border:0;"><small>'+orders[orderid]["ship_additional"]+'</small></td>';
			html+='</tr><tr>';
			html+='<td style="border:0;"><small>'+orders[orderid]["bill_street"]+' '+orders[orderid]["bill_number"]+'</small></td>';
			if (secondcolumn) html+='<td style="border:0;"><small>'+orders[orderid]["ship_street"]+' '+orders[orderid]["ship_number"]+'</small></td>';
			html+='</tr><tr>';
			html+='<td style="border:0;"><small>'+orders[orderid]["bill_zip"]+' '+orders[orderid]["bill_city"]+'</small></td>';
			if (secondcolumn) html+='<td style="border:0;"><small>'+orders[orderid]["ship_zip"]+' '+orders[orderid]["ship_city"]+'</small></td>';
			html+='</tr><tr>';
			html+='<td style="border:0;"><small>'+orders[orderid]["bill_country"]+'</small></td>';
			if (secondcolumn) html+='<td style="border:0;"><small>'+orders[orderid]["ship_country"]+'</small></td>';
			html+='</tr>';
			
			if (orders[orderid]["usermail"]!="Invalid Request" && orders[orderid]["usermail"]!="")
			{
				html+='<tr>';
				html+='<td style="border:0;"><small><b id="column_buyer_mail'+orderid+'">'+orders[orderid]["usermail"]+'</b></small></td>';
				if (secondcolumn) html+='<td style="border:0;"><small><b>'+orders[orderid]["usermail"]+'</b></small></td>';
				html+='</tr>';
			}
			html+='</table>';
			
		return html;		
	}
	
	function show_order_items(orderid)
	{
		var html='';
							
		html+='<table style="border:0; width:100%; margin:0px;">';
		if (orders[orderid]["shipping_type_id"]==2 || orders[orderid]["shipping_type_id"]==7)
		{
			html+='<tr><td colspan="4" style="background-color:#f00; border:0;"><b>EXPRESS-VERSAND</b></td></tr>';
		}
		//TOTAL	
		html+='<tr style="background-color:#ddd;">';
		html+='	<td style="width:7%; border:0;"></td>';
		html+=' <td style="width:15%; border:0;"></td>';
		html+='	<td style="width:58%; border:0;"><b>Gesamtsumme</b></td>';
		html+='	<td style="width:20%; border:0; text-align:right;"><b>'+orders[orderid]["OrderTotal"]+' €</b></td>';
		html+='</tr>';

		var rowcount=0;
		for (i in orders[orderid]["Items"])
		//for (var i=0; i<orders[orderid]["Items"].length; i++)
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
			
				html+='	<td style="width:58%; border:0;">';
				html+=' <a href="http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&rd=1&item='+orders[orderid]["Items"][i]["OrderItemItemID"]+'" target="_blank" >'+orders[orderid]["Items"][i]["OrderItemDesc"]+'</a>'
				html+='	</td>';
			}
			else
			{
				html+='	<td style="width:58%; border:0;">'+orders[orderid]["Items"][i]["OrderItemDesc"]+'</td>';
			}
			html+='	<td style="width:20%; border:0; text-align:right;">'+orders[orderid]["Items"][i]["OrderItemTotal"]+' €</td>';
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
		//SHIPMENT
		if (orders[orderid]["shipping_type_id"]==0)
		{
			html+='<tr style="background-color:orange;">';
		}
		else
		{
			html+='<tr style="background-color:#eee;">';
		}
		html+='	<td style="width:7%; border:0;"></td>';
		html+=' <td style="width:15%; border:0;">Versand:</td>';
		html+='	<td style="width:58%; border:0;">';
			html+=				ShipmentTypes[orders[orderid]["shipping_type_id"]]["title"];
		
		html+='	</td>';
		html+='	<td style="width:20%; border:0; text-align:right;">';
		html+=orders[orderid]["OrderShippingCosts"]+' €';
		html+=	'</td>';
		html+='</tr>';


		html+='	</table>';
		return html;
	}

	
	function show_order_state(orderid)
	{
		var html='';
		//BESTELLUNG


		html+='	<div style="width:100%; float:left; margin:3px;">';
		if (orders[orderid]["status_id"]!=4 && orders[orderid]["status_id"]!=8)
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
		//ANZEIGE FÜR UMTAUSCH || ANGEBOT
		if (orders[orderid]["ordertype_id"]==4 || orders[orderid]["ordertype_id"]==5)
		{
			html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;" src="images/crm/Payment_open.png" />';
			html+='-----------------';
		}
		else 
		//ANZEIGE FÜR BESTELLUNGEN
		{
			//ANZEIGE FÜR SOFORTVERSAND
			if (PaymentTypes[orders[orderid]["PaymentTypeID"]]["ship_at_once"]==1)
			{
				//PENDING
				if (orders[orderid]["Payments_TransactionState"]=="" || orders[orderid]["Payments_TransactionState"]=="Pending" || orders[orderid]["Payments_TransactionState"]=="Created")
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Payment_Pending.png" alt="Zahlungsbuchung austehend\nDoppelklick: Zahlung bearbeiten" title="Zahlungsbuchung austehend\nDoppelklick: Zahlung bearbeiten" ondblclick="payments_dialog_load('+orderid+');" />';
					html+='<b>'+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'</b>';
				}
				//COMPLETED
				if (orders[orderid]["Payments_TransactionState"]=="Completed")
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Payment_OK.png" alt="Zahlungsbuchung gebucht: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Zahlungsbuchung gebucht: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="payments_dialog_load('+orderid+');" />';
					html+=convert_time_from_timestamp(orders[orderid]["PaymentDate"], "complete");
				}
				//REFUNDED
				if (orders[orderid]["Payments_TransactionState"]=="Refunded")
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Payment_Refunded.png" alt="Zahlung erstattet: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Zahlung erstattet: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="payments_dialog_load('+orderid+');" />';
					html+=convert_time_from_timestamp(orders[orderid]["PaymentDate"], "complete");
				}
				
			}
			//ANZEIGE FÜR VERSAND NACH EINGANG DER ZAHLUNG
			else
			{
				if (orders[orderid]["Payments_TransactionState"]=="Pending" || orders[orderid]["Payments_TransactionState"]=="" || orders[orderid]["Payments_TransactionState"]=="Created")
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Payment_open.png" alt="Zahlung ausstehend:\nDoppelklick: Zahlung bearbeiten" title="Zahlung ausstehend:\nDoppelklick: Zahlung bearbeiten" ondblclick="payments_dialog_load('+orderid+');" />';
					html+='<b>'+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'</b>';
				}
				else if (orders[orderid]["Payments_TransactionState"]=="Completed" || orders[orderid]["Payments_TransactionState"]=="OK")
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Payment_OK.png" alt="Bezahlt per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Bezahlt per: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="payments_dialog_load('+orderid+');" />';
					html+=convert_time_from_timestamp(orders[orderid]["PaymentDate"], "complete");
				}
				else if (orders[orderid]["Payments_TransactionState"]=="Refunded")
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Payment_Refunded.png" alt="Zahlung erstattet: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" title="Zahlung erstattet: '+PaymentTypes[orders[orderid]["PaymentTypeID"]]["title"]+'\nDoppelklick: Zahlung bearbeiten" ondblclick="payments_dialog_load('+orderid+');" />';
					html+=convert_time_from_timestamp(orders[orderid]["PaymentDate"], "complete");
				}
					
			}
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

		//RETRUNS
		if (typeof (orders[orderid]["returns"])!=="undefined" || typeof (orders[orderid]["exchanges"])!=="undefined")
		{
			html+='------------------<br />';
		}

		var $hasReturn = 0;
		var $firstmod = 0;
		var $returnstate = 0;
		if (typeof (orders[orderid]["returns"])!=="undefined")
		{
			$.each(orders[orderid]["returns"], function($returnid, $returnfield)
			{
				if ($returnfield["return_type"]=="return")
				{
					$hasReturn = $returnid;
					$firstmod = $returnfield["firstmod"];
					$returnstate = $returnfield["state"];
				}
			});
		}
	
		if ($hasReturn>0)
		{
			html+='	<div id="order_return'+orderid+'" style="width:100%; float:left; margin:3px;">';
			html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" onclick="order_returns_dialog('+$hasReturn+');" src="images/crm/Shipment_Returned.png" alt="Rückgabefall" title="Rückgabefall" /></span>';	
			html+='		<span>'+convert_time_from_timestamp($firstmod, "complete")+'</span>';
			if ($returnstate==0)
			{
				html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/16x16/warning.png" alt="Rückgabefall noch nicht abgeschlossen" title="Rückgabefall noch nicht abgeschlossen" /></span>';	
			}
			html+='	</div>';
		}
	
		
		//EXCHANGES
		var $hasExchange = 0;
		var $firstmod = 0;
		var $returnstate = 0;
		if (typeof (orders[orderid]["returns"])!=="undefined")
		{
			$.each(orders[orderid]["returns"], function($returnid, $returnfield)
			{
				if ($returnfield["return_type"]=="exchange")
				{
					$hasExchange = $returnid;
					$firstmod = $returnfield["firstmod"]*1;
					$exchange_orderid = $returnfield["exchange_order_id"]*1;
					$returnstate = $returnfield["state"];
				}
			});
		}
		
		if ($hasExchange>0)
		{
			html+='	<div id="order_exchnage'+orderid+'" style="width:100%; float:left; margin:3px;">';
			html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" onclick="order_returns_dialog('+$hasExchange+');" src="images/crm/Shipment_Exchange.png" alt="Umtauschfall" title="Umtauschfall" /></span>';	
			html+='		<span>'+convert_time_from_timestamp($firstmod, "complete")+'</span>';
			if ($returnstate==0)
			{
				html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="images/icons/16x16/warning.png" alt="Umtauschfall noch nicht abgeschlossen" title="Umtauschfall noch nicht abgeschlossen" /></span>';
			}
			
			var href = '<?php echo PATH; ?>backend_crm_orders.php?jump_to=order&orderid='+$exchange_orderid+'&order_type=4';
			//html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" onclick="view_box('+$exchange_orderid+');" src="images/crm/forward.png" alt="Gehe zu Umtausch" title="Gehe zu Umtausch" /></span>';
			html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" onclick="javascript:window.open(\''+href+'\', \'_blank\');" src="images/crm/forward.png" alt="Gehe zu Umtausch" title="Gehe zu Umtausch" /></span>';
			html+='	</div>';

		}
		
		return html;
	}
	
	function show_vehicle_data(orderid)
	{
		
		//Get Vehicles
		var vehicle_count=0;
		for (i in orders[orderid]["Items"])
		//for (i=0;i<orders[orderid]["Items"].length; i++)
		{
			if (orders[orderid]["Items"][i]["OrderItemCustomerVehicleID"]!=0) vehicle_count++;
		}

				
		var checked_count=0;
		var checked_false=0;
		for (i in orders[orderid]["Items"])
		//for (i=0;i<orders[orderid]["Items"].length; i++)
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
			//if (checked_count==orders[orderid]["Items"].length && checked_false==0)
			if (checked_count==Object.keys(orders[orderid]["Items"]).length && checked_false==0)
			
			{
				
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_green.png" alt="Artikel gecheckt\nKLICK: Bearbeiten" title="Artikel gecheckt\nKLICK: Bearbeiten" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
				//html+='		<b>'+vehicle_count+'</b><small> Fahrzeugdaten für </small><b>'+orders[orderid]["itemcount"]+'</b> <small>Artikel</small>';
				html+='		<b>'+vehicle_count+'</b><small> Fahrzeugdaten für </small><b>'+Object.keys(orders[orderid]["Items"]).length+'</b> <small>Artikel</small>';
			}
			if (checked_count<Object.keys(orders[orderid]["Items"]).length && checked_false==0)
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
					if (orders[orderid]["user_carfleet_count"]>0)
					{
						//FAHRZEUGDATEN ZUM USER VORHANDEN
						html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_pink.png" alt="Fahrzeuge zum Kunden bekannt\n KLICK: Fahrzeugdaten zuordnen" title="Fahrzeuge zum Kunden bekannt\n KLICK: Fahrzeugdaten zuordnen" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';				
					}
					else
					{
						html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car.png" alt="fehlende Fahrzeugdaten\n KLICK: Fahrzeugdaten eingeben" title="fehlende Fahrzeugdaten\n KLICK: Fahrzeugdaten eingeben" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
					}
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
				html+='		<b>'+vehicle_count+'</b><small> Fahrzeugdaten für </small><b>'+Object.keys(orders[orderid]["Items"]).length+'</b> <small>Artikel</small>';
			}

		}
		else
		{
			if (checked_false==0)
			{
				if (checked_count==Object.keys(orders[orderid]["Items"]).length)
				{


					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_green.png" alt="Fahrzeugdatenvorhanden +Artikel gecheckt\nKLICK: Bearbeiten" title="Fahrzeugdatenvorhanden +Artikel gecheckt\nKLICK: Bearbeiten" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
					html+='		<b>'+vehicle_count+'</b><small> Fahrzeugdaten für </small><b>'+Object.keys(orders[orderid]["Items"]).length+'</b> <small>Artikel</small>';
				}
				else
				{
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_yellow.png" alt="Fahrzeugdatenvorhanden +Artikel nicht gecheckt\nKLICK: bearbeiten" title="Fahrzeugdatenvorhanden +Artikel nicht gecheckt\nKLICK: bearbeiten" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
					html+='		<b>'+vehicle_count+'</b><small> Fahrzeugdaten für </small><b>'+Object.keys(orders[orderid]["Items"]).length+'</b> <small>Artikel</small>';
					
				}
			}
			else
			{
				
				html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/crm/Car_red.png" alt="Bestellung hat fehlerhaften Artikel\nKLICK: Bearbeiten" title="Bestellung hat fehlerhaften Artikel\nKLICK: Bearbeiten" onclick="vehicle_item_update('+orderid+', '+orders[orderid]["customer_id"]+');" />';
				html+='		<b>'+vehicle_count+'</b><small> Fahrzeugdaten für </small><b>'+Object.keys(orders[orderid]["Items"]).length+'</b> <small>Artikel</small>';
			}
		}
		
		//LASTMOD USER INFO
		
		html+='<br /><br />';
		
		html+='<small>letzte Bearbeitung: <b>'+orders[orderid]["lastmod_username"]+'</b> am: '+convert_time_from_timestamp(orders[orderid]["lastmod"], "complete")+'</small>';
		
		/*
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
		*/
		html+='	</div>';
		
		return html;
	}
	
	function show_combined_note(orderid)
	{
		if (orders[orderid]["combined_with"]>0)
		{
			$("#ordernoterow1"+orderid).show();
		}
		else
		{
			$("#ordernoterow1"+orderid).hide();
		}
		
	}
	
	function show_COD_note(orderid)
	{
		if ((orders[orderid]["PaymentTypeID"]==3 && ShipmentTypes[orders[orderid]["shipping_type_id"]]["expected_paymenttype_id"]!=3) || (orders[orderid]["PaymentTypeID"]!=3 && ShipmentTypes[orders[orderid]["shipping_type_id"]]["expected_paymenttype_id"]==3))
		{
			$("#ordernoterow2"+orderid).show();
		}
		else
		{
			$("#ordernoterow2"+orderid).hide();
		}
	}
	
	function show_PayPal_note(orderid)
	{
		if (orders[orderid]["PayPal_BuyerNote"].length>0)
		{
			$("#PayPalnote"+orderid).html(orders[orderid]["PayPal_BuyerNote"]);
			$("#PayPal_BuyerNote"+orderid).show();
		}
		else
		{
			$("#PayPalnote"+orderid).html("");
			$("#PayPal_BuyerNote"+orderid).hide();
		}
	}

	function show_order_note(orderid)
	{
		var ordernote = '';
		if (orders[orderid]["ordernr"].length>0)
		{
			ordernote+='<p>';
			ordernote+='<b>Kunden Auftragsnummer: </b>';
			ordernote+=orders[orderid]["ordernr"];
			ordernote+='</p>';
		}
		if (orders[orderid]["comment"].length>0)
		{
			ordernote+='<p>';
			ordernote+='<b>Kunden Bestellungshinweis: </b>';
			ordernote+=orders[orderid]["comment"];
			ordernote+='</p>';
		}

		/*if (orders[orderid]["order_note"].length>0)
		{
			ordernote+='<p><b>Verkäufernotiz: </b>'+orders[orderid]["order_note"]+'</p>';
			//$("#ordernote"+orderid).text(orders[orderid]["order_note"]);
		}*/
		var y=0;
		for ( a in orders[orderid]["order_notes"] )
		{ 
			if ( orders[orderid]["order_notes"][y] != '' )
			{	
				ordernote+='<p';
				if ( y > 0 )
				{
					ordernote+=' class="hidden_note'+orderid+'" style="display:none"';
				}
				ordernote+='>'+orders[orderid]["order_notes"][y]+'</p>';
				y++;
			}	
		}
		if ( y > 1 )
		{
			ordernote+='<p class="button_show_notes"><a href="JavaScript:show_hide_notes('+orderid+');" value="'+orderid+'">alle anzeigen</a></p>';
		}

		$("#ordernote"+orderid).html(ordernote);
		
		if (ordernote != "")
		{
			$("#ordernoterow3"+orderid).show();
		}
		else
		{
			$("#ordernoterow3"+orderid).hide();
		}
	}

	function view_box(orderid, next_action)
	{
		//einzelorder
		if (typeof(orderid)!=='undefined')
		{
			FILTER_Platform=0;
			$("#FILTER_Platform").val(0);
			
			FILTER_Status=0;
			$("#FILTER_Status").val(0);
			
			FILTER_Country="";
			$("#FILTER_Country").val("");
			
			FILTER_SearchFor=8;
			$("#FILTER_SearchFor").val(8);
			
			FILTER_Searchfield=orderid;
			$("#FILTER_Searchfield").val(orderid);
			
			OrderBy="firstmod"
			OrderDirection="down";
			ResultPage=1;
			
			date_from="";
			$("#date_from").val("");
			date_to="";
			$("#date_to").val("");
		
			var mode = "single";
		}
		else
		{
			var mode = "list";
		}
		
		FILTER_Searchfield=$("#FILTER_Searchfield").val();
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_orders_list", mode:mode, order_id:orderid, FILTER_Platform:FILTER_Platform, FILTER_Status:FILTER_Status, FILTER_Country:FILTER_Country, FILTER_SearchFor:FILTER_SearchFor, FILTER_Searchfield:FILTER_Searchfield, FILTER_Ordertype:FILTER_Ordertype, OrderBy:OrderBy, OrderDirection:OrderDirection, ResultPage:ResultPage,ResultRange:ResultRange, date_from:date_from, date_to:date_to},
			function(data)
			{
				//show_status2(data);
	
				//ResultPage=1;
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					//OrderArray leeren
					//orders.length = 0;	
					
					orders = null;
					orders = new Object();
					
					//DATEN WERDEN IN DAS ORDER-ARRAY geschrieben
					update_order_array(data);
					//ResultPages
					Results=parseInt($xml.find("Entries").text());
					var rest=Results%ResultRange;
					ResultPages=(Results-rest)/ResultRange;
					if (rest>0) ResultPages++;
					
									
					var table ='<form name="orderform">';
					table+='<table id="orderdata" style="table-layout:fixed;">';
					table+='<tr>';
					table+='	<th style="width:20px;"><input id="selectall" type="checkbox" onclick="checkAll();" /></th>';
					table+='	<th style="width:90px; cursor:pointer" onclick="sortTable(\'id_order\');">OrderID</th>';
					table+='	<th style="width:100px;">Plattform</th>';
					table+='	<th style="width:410px; cursor:pointer" onclick="sortTable(\'bill_lastname\');">Käufer</th>';
					table+='	<th style="width:350px;">Artikel</th>';
				//	table+='	<th style="width:90px;">Gesamt</th>';
					table+='	<th style="width:180px; cursor:pointer" onclick="sortTable(\'firstmod\');">Orderstatus</th>';
					table+='	<th style="width:160px;">Fahrzeugdaten-Anfrage</th>';
				//	table+='	<th style="width:90px;"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/mail_add.png" alt=Fahrzeugdatenanfragen senden" title="Fahrzeugdatenanfragen senden" onclick="send_fin_mail_checked_dialog();" /></th>';
					table+='	<th style="width:90px;"></th>';
					table+='</tr>';
					table+='</table>';
					table+='</form>';

					$("#tableview").html(table);
			
					var rowcounter=0;
					
					//ORDERARRAY FÜR ANZEIGE NACH ENTRy_POS SORTIEREN
					var entry_pos=new Array();
					for (var OrderID in orders) {
						entry_pos[orders[OrderID]["entry_pos"]]=orders[OrderID]["id_order"];
					}
					
					var OrderID=0;
					for (var pos in entry_pos) {
						
						OrderID=entry_pos[pos];
						
						rowcounter++;

						if (orders[OrderID]["ordertype_id"]==4)
						{
							if (rowcounter%2 == 0) var rowcolor="#ffd0d0"; else var rowcolor="#ffe9e9";
						}
						else
						{
							if (rowcounter%2 == 0) var rowcolor="#f0faff"; else var rowcolor="#fff";
						}
						
						//INFOZEILE für kombinierte Orders
						table='';
						table+='<tr id="ordernoterow1'+OrderID+'" style="display:none; background-color:#99f; border-top-color:#000; border-top-width:2px; border-top-style:solid">';
						table+='	<td></td>';
						table+='	<td colspan="6">';
						table+='<b> kombinierte Bestellung </b>';
						table+='	</td>';
						table+='	<td></td>';
						table+='</tr>';	
						
						$("#orderdata").append(table);
						show_combined_note(OrderID);
						
						
						//INFOZEILE Warnhinweis Abweichung Versand <-> Zahlung bei Nachnahme
						table='';
						table+='<tr id="ordernoterow2'+OrderID+'" style="display:none; background-color:#f99; border-top-color:#000; border-top-width:2px; border-top-style:solid">';
						table+='	<td></td>';
						table+='	<td colspan="6">';
						table+='<b> Nachnahme -> Abweichung in Zahl- & Versandmethode</b>';
						table+='	</td>';
						table+='	<td></td>';
						table+='</tr>';	
						
						$("#orderdata").append(table);
						show_COD_note(OrderID);
						
						//ORDERDATA
						table='';
						table+='<tr id="orderdata'+OrderID+'" style="background-color:'+rowcolor+'">';
						
							//CHECKBOX
							table+='	<td><input name="orderID[]" type="checkbox" value="'+OrderID+'" onmousedown="select_all_from_here(this.value);" /></td>';
							
							table+='	<td>';
							table+='		<b>'+orders[OrderID]["entry_pos"]+'</b><br>';
							table+='		<small>';
							table+='			<br>O:&nbsp;'+OrderID;
							if( orders[OrderID]["VPN"]!=OrderID )
							{
								if (Shop_Shops[orders[OrderID]["shop_id"]]["shop_type"]==2 && orders[OrderID]["VPN"]!="") table+='			<br>E:&nbsp;'+orders[OrderID]["VPN"];
								if (Shop_Shops[orders[OrderID]["shop_id"]]["shop_type"]==3 && orders[OrderID]["VPN"]!="") table+='			<br>Ama:&nbsp;'+orders[OrderID]["VPN"];
							}
							if( orders[OrderID]["AUF_ID"]>0 ) table+='			<br>A:&nbsp;'+orders[OrderID]["AUF_ID"];
													
							if( orders[OrderID]["invoice_nr"]!="" ) 
							{
								if ( orders[OrderID]["invoice_file_id"]!=0)
								{
									var $path = '<?php echo PATH; ?>files/'+orders[OrderID]["invoice_file_id"].substr(0,orders[OrderID]["invoice_file_id"].length -3)+'/'+orders[OrderID]["invoice_file_id"]+'.pdf';
									table+='			<br>R:&nbsp;<a href="javascript:window.open(\''+$path+'\', \'_blank\');">'+orders[OrderID]["invoice_nr"]+'</a>';

								}
								else
								{
									table+='			<br>R:&nbsp;'+orders[OrderID]["invoice_nr"];
								}
							}
							table+='	</small>';
							table+='</td>';
							
							table+='	<td id="platform_data'+OrderID+'">'
							table+=show_platform_data(OrderID);
							table+='	</td>';
							

							table+='	<td id="order_buyer'+OrderID+'" style="padding:0px; vertical-align:top">';
							table+=show_order_buyer(OrderID);
							table+='	</td>';
							
							//ORDERITEMS						
							table+='	<td id="order_items'+OrderID+'" style="padding:0px; vertical-align:top">';
							table+= show_order_items(OrderID);
							table+='	</td>';
	/*
							//SUMMEN
							table+='	<td id="order_sum'+OrderID+'">'
							table+= show_order_sum(OrderID);
							table+='	</td>';
	*/
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
							table+='	</div>';
							

							table+='</td>';
						table+='</tr>';
						
						$("#orderdata").append(table);


						//PAYPAL NOTES
						table='';
						table+='<tr id="PayPal_BuyerNote'+OrderID+'" style="background-color:#FC7; display:none">';
						table+='	<td></td>';

						table+='	<td colspan="6">';
						table+='		<b>PayPal-Nachricht:</b>&nbsp;<span id="PayPalnote'+OrderID+'" style="font-size:8pt"></span>';
						table+='	</td>';
						table+='	<td></td>';
						table+='</tr>';	

						$("#orderdata").append(table);
						show_PayPal_note(OrderID);

						//ORDER NOTES
						table='';							
						table+='<tr id="ordernoterow3'+OrderID+'" style="background-color:#FE7; display:none">';
						table+='	<td></td>';
						table+='	<td colspan="6">';
						table+='		<span id="ordernote'+OrderID+'" style="font-size:8pt"></span>';
//						table+='		&nbsp;<a href="javascript:set_order_note('+OrderID+');"><small>[bearbeiten]</small></a>';
						table+='	</td>';
						table+='	<td></td>';
						table+='</tr>';	

						$("#orderdata").append(table);
						show_order_note(OrderID);
						
					}
					draw_navigation();
					
					//weitere aktionen
					if (typeof(next_action)!=='undefined')
					{
						if (next_action == "order_update_dialog") 
						{
							order_update_dialog(orderid, "update");
						}
						
					}

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
			//hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			//show: { effect: 'drop', direction: "up" },
			title:"Bestellung als versendet markieren",
			width:400
		});		

		

	}
	
	function msg_box(msgtext)
	{
		$("#msg_box").html(msgtext);
		$("#msg_box").dialog
		({	buttons:
			[
				{ text: "OK", click: function() { $(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			//stack: false,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Hinweis",
			width:400
		});	
		$('#msg_box').dialog({zIndex: 9999});
	
	}

	function order_abort_dialog(orderid)
	{
		show_actions_menu(orderid); //hide options
		if ($("#order_abort_dialog").length == 0)
		{
			$("body").append('<div id="order_abort_dialog" style="display:none">');
		}
		
		var html='';
		html+='<b>Soll die Bestellung wirklich storniert werden?</b> Der Vorgang kann nicht rückgängig gemacht werden.';
		
		$("#order_abort_dialog").html(html);

		$("#order_abort_dialog").dialog
		({	buttons:
			[
				{ text: "Abbrechen", click: function() { $(this).dialog("close");} },
				{ text: "Bestellung stornieren", click: function() { order_abort(orderid);} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			//stack: false,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Soll sie Bestellung storniert werden?",
			width:400
		});	
	}
	
	function order_abort(orderid)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderAbort", orderid:orderid},
		function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			var $xml=$($.parseXML($data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				$("#order_abort_dialog").dialog("close");
				if (Shop_Shops[orders[orderid]["shop_id"]]["shop_type"]==2)
				{
					alert("Bitte Bestellung auch bei Ebay abbrechen!");	
				}
				show_status("Bestellung storniert");
				update_view(orderid);
			}
			else
			{
				show_status2(data);
			}
		});
		
	}
	

	function set_order_state(orderid, state)
	{
		if ( (orders[orderid]["status_id"]==2 || orders[orderid]["status_id"]==3) && state==2)
		{
			msg_box("Der Auftrag wurde bereits als geschrieben markiert!");
			return;
		}
		if (orders[orderid]["bill_street"]=="")
		{
			msg_box("Der Auftrag kann nicht als geschrieben markiert werden, da der Käufer keine Anschrift hat. Gegebenenfalls bitte die Lieferanschrift aus dem Verkaufsprotokoll nachtragen.");
			return;
		}
		
		show_actions_menu(orderid);  //hide Actionsmenu
		
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
				update_view(orderid);
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
	
	// D E P R E C A T E D
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
			selectpayment+='<option value=3>Nachnahme</option>';
			selectpayment+='<option value=4>PayPal</option>';
		}
		selectpayment+='</select>';
		$("#paymentsselect").html(selectpayment);
		


		$("update_Payment_TransactionID").val("");

		$("#paymentType").val(orders[orderid]["PaymentTypeID"]);
		if ($("#paymentType").val()==4 || $("#paymentType").val()==5 || $("#paymentType").val()==6)
		{
			$("#update_Payment_Transaction_row").show();
		}

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
			msg_box("Bitte ein Datum zum Zahlungsvorgang angeben!");
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
					update_view(orderid);
				}
				else
				{
					show_status2(data);
				}
			}
		);
	}


	function set_order_note(orderid)
	{
		show_actions_menu(orderid); //hide options
				
		//$("#update_NoteDialogNote").val($("#ordernote3"+orderid).text());
		$("#update_NoteDialogNote").val(orders[orderid]["order_note"]);
		$("#update_NoteDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { save_order_note(orderid);} },
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

	function save_order_note(orderid)
	{
		var note = $("#update_NoteDialogNote").val();
		wait_dialog_hide();
		$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "crm_set_order_note_in_conversation", OrderID:orderid, note:note},
		function($data)
		{
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
			$("#update_NoteDialog").dialog("close");
			view_box();
		});
		/*
			$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_set_order_note", OrderID:orderid, note:note},
			function(data)

			{
		
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#ordernote"+orderid).text(note);
					$("#update_NoteDialog").dialog("close");
					update_view(orderid);
				}
				else
				{
					show_status2(data);
				}
			}
		);*/
	}

	function send_DHLretourlabel(orderid, dhl_parameter)
	{
		wait_dialog_show();		
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:orderid},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				         
				if ($xml.find("ship_adr_id").text()==0)
				{
					$("#DHLretourlabel_address_company").val($xml.find("bill_adr_company").text());
					$("#DHLretourlabel_address_additional").val($xml.find("bill_adr_additional").text());
					$("#DHLretourlabel_address_firstname").val($xml.find("bill_adr_firstname").text());
					$("#DHLretourlabel_address_lastname").val($xml.find("bill_adr_lastname").text());
					$("#DHLretourlabel_address_street").val($xml.find("bill_adr_street").text());
					$("#DHLretourlabel_address_number").val($xml.find("bill_adr_number").text());
					$("#DHLretourlabel_address_zip").val($xml.find("bill_adr_zip").text());
					$("#DHLretourlabel_address_city").val($xml.find("bill_adr_city").text());
					$("#DHLretourlabel_address_country").val($xml.find("bill_adr_country").text());
					$("#DHLretourlabel_address_country_code").val($xml.find("bill_adr_country_code").text());
					$("#DHLretourlabel_usermail").val($xml.find("usermail").text());
				}
				else
				{
					$("#DHLretourlabel_address_company").val($xml.find("ship_adr_company").text());
					$("#DHLretourlabel_address_additional").val($xml.find("ship_adr_additional").text());
					$("#DHLretourlabel_address_firstname").val($xml.find("ship_adr_firstname").text());
					$("#DHLretourlabel_address_lastname").val($xml.find("ship_adr_lastname").text());
					$("#DHLretourlabel_address_street").val($xml.find("ship_adr_street").text());
					$("#DHLretourlabel_address_number").val($xml.find("ship_adr_number").text());
					$("#DHLretourlabel_address_zip").val($xml.find("ship_adr_zip").text());
					$("#DHLretourlabel_address_city").val($xml.find("ship_adr_city").text());
					$("#DHLretourlabel_address_country").val($xml.find("ship_adr_country").text());
					$("#DHLretourlabel_address_country_code").val($xml.find("ship_adr_country_code").text());
					$("#DHLretourlabel_usermail").val($xml.find("usermail").text());
				}

				$("#DHLretourlabelDialog").dialog
				({	buttons:
					[
						{ text: "DHL Retourlabel senden", click: function() { do_send_DHLretourlabel(orderid, dhl_parameter);} },
						{ text: "Beenden", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
				//	hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
				//	show: { effect: 'drop', direction: "up" },
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
		href+='&ADDR_SEND_STREET_ADD='+orderid;
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
						update_view(orderid);
						order_returns_dialog(returns["id_return"]);
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
			msg_box("Es muss eine Retourlabel ID angegeben werden");
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
	
	function set_order_type(shop_id)
	{
		if (shop_id == 6)
		{
			$("#order_add_ordertype").val(1);
		}
	}
	
	
	function order_add()
	{

		//BUILD DIV FOR DIALOG
			//check if div exists
		if ($("#order_add_dialog").length == 0)
		{
			$("body").append('<div id="order_add_dialog" style="display:none">');
		}
		
		var html='';
		html+='<table>';
		html+='<tr>';
		html+=' <td>Für welchen Shop soll die neue Bestellung angelegt werden:</td>';
		html+='	<td>';
		html+='		<select id="order_add_site" size="1" onchange="set_order_type(this.value);">';
		html+='			<option value=0>Bitte Shop wählen</option>';
		for (var i = 0; i<UserSites.length; i++)
		{
			//PARENTSHOP
			for (var shop_id in Shop_Shops) 
			{
				if (Shop_Shops[shop_id]["active"]==1 && Shop_Shops[shop_id]["site_id"]==UserSites[i])
				{
					var parentshop_id = shop_id;
					html+='	<option value='+shop_id+'>'+Shop_Shops[shop_id]["title"]+'</option>';
				}
			}
			//CHILDSHOPS
			for (var shop_id in Shop_Shops) 
			{
				if (Shop_Shops[shop_id]["active"]==1 && Shop_Shops[shop_id]["parent_shop_id"]==parentshop_id)
				{
					html+='	<option value='+shop_id+'>'+Shop_Shops[shop_id]["title"]+'</option>';
				}
			}
		}
		html+='		</select>';
		html+='	</td>';
		html+='</tr><tr>';
		html+=' <td>Bitte Format festlegen:</td>';
		html+='	<td>';
		html+='		<select id="order_add_ordertype" size="1">';
		//html+='			<option value=0>Bitte wählen</option>';
		for (var ordertype_id in OrderTypes) 
		{
			if (ordertype_id!=4 && ordertype_id!=5 && ordertype_id!=6)
			{
				if (ordertype_id==2)
				{
					html+='	<option value='+ordertype_id+' selected>'+OrderTypes[ordertype_id]["title"]+'</option>';
				}
				else
				{
					html+='	<option value='+ordertype_id+'>'+OrderTypes[ordertype_id]["title"]+'</option>';
				}
			}
		}
		html+='		</select>';
		html+='	</td>';
		html+='</tr><tr>';
		html+=' <td>Bitte Währung festlegen:</td>';
		html+='	<td>';
		html+='		<select id="order_add_currency" size="1">';
		for (var currency in Currencies) 
		{
			if (currency=="EUR")
			{
				html+='	<option value='+currency+' selected>'+currency+'</option>';
			}
			else
			{
				html+='	<option value='+currency+'>'+currency+'</option>';
			}
		}
		html+='		</select>';

		html+='	</td>';
		
		html+='</tr>';
		html+='</table>';
		

		$("#order_add_dialog").html(html);
	
		
		
		//$("#order_add_shop").val(FILTER_Platform);
		//$("order_add_ordertype").val();
		
		
		$("#order_add_dialog").dialog
			({	buttons:
				[
					{ text: "Bestellung anlegen", click: function() { 
						if ($("#order_add_site").val()==0)
						{
							$("#order_add_site").focus();
							msg_box("Bitte zuerst einen Shop wählen!");	
						}
						else if($("#order_add_ordertype").val()==0)
						{
							$("#order_add_ordertype").focus();
							msg_box("Bitte zuerst einen Bestellungstyp wählen!");
						}
						else
						{
						
						var shop_id = $("#order_add_site").val();	
						var currency = $("#order_add_currency").val();
							
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderAdd", mode:"manual", Currency_Code:currency, shop_id:shop_id, ordertype_id:$("#order_add_ordertype").val(), status_id:1},
								function($data)
								{
									//alert ($data);
									wait_dialog_hide();
									//alert($data);
									try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
									$ack = $xml.find("Ack");
									if ( $ack.text()!="Success" ) { show_status2($data); return; }

									
										var orderid=$xml.find("id_order").text();
										//alert(orderid);
										if (orderid!=0 && orderid!="")
										{
											$("#order_add_dialog").dialog("close");
											view_box(orderid, "order_update_dialog");
										}

										else
										{
											msg_box("FEHLER BEIM ANLEGEN EINER NEUEN BESTELLUNG");
											
										}
								}
							);
						}
					} },
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Beenden",
				//hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				//show: { effect: 'drop', direction: "up" },
				title:"Neue Bestellung anlegen",
				width:800
			});	

	}
	
	function order_update_customer_ordernr(orderid)
	{
		// CREATE DIALOG
		if ($("#order_update_customer_ordernr_dialog").length==0)
		{
			$("body").append('<div id="order_update_customer_ordernr_dialog" style="display:none"></div>');
		}

		var html='';
		html+='<table>';
		html+='<tr>';
		html+='	<td><input type="text" id="order_update_customer_ordernr_dialog_ordernr" size="50" value="'+orders[orderid]["ordernr"]+'" /></td>';
		html+='</tr>';
		html+='</table>';
		
		$("#order_update_customer_ordernr_dialog").html(html);
		$("#order_update_customer_ordernr_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() 
						{ 
							
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, ordernr:$("#order_update_customer_ordernr_dialog_ordernr").val()},
							function($data)
							{
								//alert ($data);
								wait_dialog_hide();
								try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
								$ack = $xml.find("Ack");
								if ( $ack.text()!="Success" ) { show_status2($data); return; }

								$("#order_update_customer_ordernr_dialog").dialog("close");
								//view_box(orderid, "order_update_dialog");
								//order_update_dialog(orderid, "update");
								update_view(orderid, "order_update_dialog");
							});
						}
					 },
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Beenden",
				modal:true,
				resizable:false,
				title:"Kunden OrderNr bearbeiten",
				width:400
			});	
	}
	
	function order_update_customer_comment(orderid)
	{
		// CREATE DIALOG
		if ($("#order_update_customer_comment_dialog").length==0)
		{
			$("body").append('<div id="order_update_customer_comment_dialog" style="display:none"></div>');
		}

		var html='';
		html+='<table>';
		html+='<tr>';
		html+='	<td><textarea id="order_update_customer_comment_dialog_comment" cols="50" rows="5">'+orders[orderid]["comment"]+'</textarea></td>';
		html+='</tr>';
		html+='</table>';
		
		$("#order_update_customer_comment_dialog").html(html);
		$("#order_update_customer_comment_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() 
						{ 
							
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, comment:$("#order_update_customer_comment_dialog_comment").val()},
								function($data)
								{
									//alert ($data);
									wait_dialog_hide();
									try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
									$ack = $xml.find("Ack");
									if ( $ack.text()!="Success" ) { show_status2($data); return; }
									if (orderid!=0 && orderid!="")
									{
										$("#order_update_customer_comment_dialog").dialog("close");
										//view_box(orderid, "order_update_dialog");
										//order_update_dialog(orderid, "update");
										update_view(orderid, "order_update_dialog");
									}

								}
							);
						}
					 },
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Beenden",
				modal:true,
				resizable:false,
				title:"Kunden Bestellhinweis bearbeiten",
				width:400
			});	
	}
	
	function order_update_set_payment(orderid, payment_type_id)
	{
		wait_dialog_show();
		
		//SUCHE ALLE KOMBINIERTEN ORDERS 
		$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderDetailGet_neu",  OrderID:orderid},
			function($data)
			{
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
				var $orders=$xml.find("orders");
				$orders.find("order").each(
				function ()
				{
					//alert($(this).find("foreign_OrderID").text());
					if ($(this).find("orderid").text()!=0 && $(this).find("orderid").text()!=0)
					{
							var shipping_detail = ShipmentTypes[$(this).find("shipping_type_id").text()]["title"];
							if (payment_type_id == 0)
							{
								shipping_detail+=", Keine Zahlart gewählt";
							}
							else
							{
								shipping_detail+=", "+PaymentTypes[payment_type_id]["title"];
							}
						
						$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderUpdate", 
							SELECTOR_id_order:$(this).find("orderid").text(),
							payments_type_id:payment_type_id,
							Payments_TransactionState:"Pending",
							Payments_TransactionStateDate: Math.round(+new Date()/1000),
							shipping_details: shipping_detail },
						function(data)
						{
							var $xml=$($.parseXML(data));
							var Ack = $xml.find("Ack").text();
							if (Ack=="Success") 
							{
							}
							else
							{
								show_status2(data);
								order_update_dialog(orderid, "update");
								return;
							}
						});
					
					}
					
				});
				
				update_view(orderid, "order_update_dialog");
			}
		);
		
	}
	

	function order_update_dialog(orderid, mode)
	{
		//update_view(orderid);
		//var for correlation check
		var shipping_type_id=0;
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:orderid},
			function(data)
			{
				wait_dialog_hide();
			//	alert(data);
//show_status2(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					
					//CREATE ADDRESS DIV
					var html= '';
					//alert($xml.find("customer_shop_id").text());
					if ($xml.find("customer_id").text()!=0)
					{
						html+='<table style="width:750px">';
						html+='<tr>';
						html+='	<td>User-ID: '+$xml.find("customer_id").text()+'</td>';
						if ($xml.find("customer_site_id").text()!=0)
						{
							html+='<td style="backface-color:#fff">';
							html+='<b>'+$xml.find("customer_site_name").text()+' - Kunde</b>';
							html+='</td>';
						}
						html+='	<td>';
						if ($xml.find("customer_name").text()!="")
						{
							html+=$xml.find("customer_name").text();
						}
						else if ($xml.find("customer_lastname").text()!="")
						{
							if ($xml.find("customer_firstname").text()!="")
							{
								html+=' - '+$xml.find("customer_firstname").text()+' '+$xml.find("customer_lastname").text();
							}
							else
							{
								html+=' - '+$xml.find("customer_lastname").text();
							}
						}
						html+='</td>';
						html+='</tr>';
						html+='</table>';
					}
					else
					{
						html+='<table style="width:750px">';
						html+='<tr>';
						html+='	<td><span style="color:red; font-weight:bold;">Kein Kunde ausgewählt. Kunde durch Eingabe einer Adresse neu anlegen oder bestehenden Kunden auswählen: <img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/database_search.png" alt="Kunden suchen" title="Kunden suchen" onclick="find_customer_dialog('+orderid+', '+$xml.find("order_site_id").text()+');"/></span></td>';
						html+='</tr>';
						html+='</table>';
					}

					if ((orders[orderid]["bill_adr_id"]==orders[orderid]["ship_adr_id"]) || (orders[orderid]["ship_adr_id"]==0) || (orders[orderid]["bill_adr_id"]==0) )
					{
						var secondcolumn=false;
					}
					else
					{
						var secondcolumn=true;
					}
					
					html+='<table style="width:750px">';
					if (secondcolumn)
					{
						html+='<colgroup><col style="width:40%"><col style="width:30%"><col style="width:30%"></colgroup>';
					}
					else
					{
						html+='<colgroup><col style="width:40%"><col style="width:60%"></colgroup>';
					}
					html+='<tr>';
					html+='	<th></th>';
					if (secondcolumn)
					{
						html+='	<th>Rechnungsadresse';
						if (mode=="update")
						{
							html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Rechnungsadresse bearbeiten" title="Rechnungsadresse bearbeiten" onclick="order_address_update('+orderid+', \'bill\');"/>';
							//html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/database_search.png" alt="Kunden suchen" title="Kunden suchen" onclick="find_customer_dialog('+orderid+', '+$xml.find("shop_id").text()+');"/>';
						}
						html+='</th>';
						
						html+='	<th>Lieferadresse';
						if (mode=="update")
						{
							html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Lieferadresse bearbeiten" title="Lieferadresse bearbeiten" onclick="order_address_update('+orderid+', \'ship\');"/>';
							//html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/database_search.png" alt="Kunden suchen" title="Kunden suchen" onclick="find_customer_dialog('+orderid+', '+$xml.find("shop_id").text()+');"/>';
						}

						html+='</th>';
						

					}
					else
					{
						html+='	<th>Rechnungs- & Lieferadresse';
						if (mode=="update")
						{
							html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/page_add.png" alt="Abweichende Lieferadresse eingeben" title="Abweichende Lieferadresse eingeben" onclick="order_address_update('+orderid+', \'ship\');"/>';
							html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/blog_post_edit.png" alt="Rechnungs- &Lieferadresse bearbeiten" title="Rechnungs- &Lieferadresse bearbeiten" onclick="order_address_update('+orderid+', \'bill\');"/>';
						//if ($xml.find("customer_id").text()==0)	html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/database_search.png" alt="Kunden suchen" title="Kunden suchen" onclick="find_customer_dialog('+orderid+', '+$xml.find("shop_id").text()+');"/>';
						}
						html+='</th>';
					}
					
					html+='</tr>';
					//COMPAMY
					html+='<tr>';
					html+='	<td>Firma</td><td style="background-color:#fff">'+$xml.find("bill_adr_company").text()+'</td>';
					if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_company").text()+'</td>';
					html+='</tr>';
					//ADDITIONAL
					html+='<tr>';
					html+='	<td>Adresszusatz <small>(Postnummer)</small></td><td style="background-color:#fff">'+$xml.find("bill_adr_additional").text()+'</td>';
					if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_additional").text()+'</td>';
					html+='</tr>';
					//NAME
					html+='<tr>';
					html+='	<td>Name</td><td style="background-color:#fff">'+$xml.find("bill_adr_firstname").text()+' '+$xml.find("bill_adr_lastname").text()+'</td>';
					if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_firstname").text()+' '+$xml.find("ship_adr_lastname").text()+'</td>';
					html+='</tr>';
					//STREET & NUMBER
					html+='<tr>';
					html+='	<td>Straße/Hausnummer <small>(Packst.Nr./Packstation)</small> </td><td style="background-color:#fff">'+$xml.find("bill_adr_street").text()+' '+$xml.find("bill_adr_number").text()+'</td>';
					if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_street").text()+' '+$xml.find("ship_adr_number").text()+'</td>';
					html+='</tr>';
					//PLZ & CITY
					html+='<tr>';
					html+='	<td>Postleitzahl / Stadt</td><td style="background-color:#fff">'+$xml.find("bill_adr_zip").text()+' '+$xml.find("bill_adr_city").text()+'</td>';
					if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_zip").text()+' '+$xml.find("ship_adr_city").text()+'</td>';
					html+='</tr>';
					//COUNTRY
					html+='<tr>';
					html+='	<td>Land</td><td style="background-color:#fff">'+$xml.find("bill_adr_country").text()+'</td>';
					if (secondcolumn) html+='<td style="background-color:#fff">'+$xml.find("ship_adr_country").text()+'</td>';
					html+='</tr>';
					// TELEFON
					html+='<tr>';
					html+='	<td>Telefon</td><td style="background-color:#fff">'+$xml.find("userphone").text()+'</td>';
					if (secondcolumn) html+='<td style="background-color:#fff"></td>';
					html+='</tr>';
					// EMAIL
					html+='<tr>';
					html+='	<td>E-Mail</td><td style="background-color:#fff">'+$xml.find("usermail").text()+'</td>';
					if (secondcolumn) html+='<td style="background-color:#fff"></td>';
					html+='</tr>';

					html+='</table>';
					
					//KUNDEN ORDERNR & HINWEIS
					//FIRSTMOD_USER == VERKÄUFER -> FELDER BEARBEITBAR
					/*
					if (typeof(Seller[orders[orderid]["firstmod_user"]])!=="undefined" && mode == "update")
					{
						html+='<table style="width:750px">';
						html+='<colgroup><col style="width:40%"><col style="width:54%"><col style="width:6%"></colgroup>';
						html+='<tr>';
						html+='	<th>Kunden Auftragsnummer</th>';
						html+='	<td>'+orders[orderid]["ordernr"]+'</td>';
						html+='	<td><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/edit.png" alt="Kunden Ordernummer bearbeiten" title="Kunden Ordernummer bearbeiten" onclick="order_update_customer_ordernr('+orderid+');"/></td>';
						html+='</tr>';
						html+='<tr>';
						html+='	<th>Kunden Bestellungshinweis</th>';
						html+='	<td>'+orders[orderid]["comment"]+'</td>';
						html+='	<td><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/edit.png" alt="Kunden Bestellungshinweis bearbeiten" title="Kunden Bestellungshinweis bearbeiten" onclick="order_update_customer_comment('+orderid+');"/></td>';
						html+='</tr>';
						html+='</table>';
					}
					else
					{
					*/	
						if (orders[orderid]["ordernr"].length>0 || orders[orderid]["comment"].length>0 )
						{
							html+='<table style="width:750px">';
							html+='<colgroup><col style="width:40%"><col style="width:60%"></colgroup>';
							if (orders[orderid]["ordernr"].length>0)
							{
								html+='<tr>';
								html+='	<th>Kunden Auftragsnummer</th>';
								html+='	<td>'+orders[orderid]["ordernr"]+'</td>';
								html+='</tr>';
							}
							if ( orders[orderid]["comment"].length>0)
							{
								html+='<tr>';
								html+='	<th>Kunden Bestellungshinweis</th>';
								html+='	<td>'+orders[orderid]["comment"]+'</td>';
								html+='</tr>';
							}
							html+='</table>';
						}
					//}
					
					//CHECK if paymenttype is shown or selectable
						// if mode update and paymentstatus not completed -> selectable
					html+='<table>';
					html+='<tr>';
	
					if (mode == "update" && orders[orderid]["Payments_TransactionState"]!="Completed") 
					{
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
					}
					else
					{
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
					}
					html+='	<th>';
					html+='		Mehrwertsteuer:';
					html+='	</th>';
					html+='	<td>';
					html+='		<span id="VATinfo" style="cursor:pointer" onclick="change_orderVAT_dialog('+orderid+');"><b>'+orders[orderid]["VAT"]+' %<b></span>';
					html+='	</td>';
					html+='</tr>';
					html+='</table>';


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
						var parentorderid=0;
						var $orders = $xml.find("orders");
						$orders.find("order").each(
						function()
						{
						//	alert ($(this).find("combined_with").text());
							if (parentorderid==0)
							{
								if (1*$(this).find("combined_with").text()>0)
								{
									parentorderid=$(this).find("combined_with").text();							
								}
								else
								{
									parentorderid=$(this).find("orderid").text();
								}
							}
						});
						
						//html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Bestellposition hinzufügen" title="Bestellposition hinzufügen" onclick="add_orderpositions('+parentorderid+', '+$xml.find("shop_id").text()+');"/>';
						html+='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Bestellposition hinzufügen" title="Bestellposition hinzufügen" onclick="add_orderpositions('+parentorderid+', '+orders[orderid]["shop_id"]+');"/>';
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
					
						//DIALOG ZUM BESTELLUNGVERSENDEN
					if (mode=="IDIMS")
					{
						if ($("#order_send_dialog").length==0)
						{
							$("body").append('<div id="order_send_dialog" style="display:none"></div>');
						}
						else
						{
							$("#order_send_dialog").html('');
						}
						
						$("#order_send_dialog").html(html);
						
						if (!$("#order_send_dialog").is(":visible"))
						{
							$("#order_send_dialog").dialog
								({	buttons:
									[
										{ id: "order_send_dialog_order_send_btn",  text: "Bestellung senden", click: function() { order_send(orderid);} },
										{ text: "Beenden", click: function() { $(this).dialog("close"); } }
									],
									closeText:"Fenster schließen",
									closeOnEscape: true,
									//hide: { effect: 'drop', direction: "up" },
									modal:true,
									resizable:false,
									//show: { effect: 'drop', direction: "up" },
									title:"Bestellung ans IDIMS senden",
									width:800,
									beforeClose: function() {}
								});	
						}
					}
					if (mode=="view")
					{
						if ($("#order_view_dialog").length==0)
						{
							$("body").append('<div id="order_view_dialog" style="display:none"></div>');
						}
						else
						{
							$("#order_view_dialog").html('');
						}
						
						//DIALOG TITLE
						if(orders[orderid]["ordertype_id"]==4)
						{
							var dialog_title = "Umtausch-Ansicht";
						}
						else
						{
							var dialog_title = "Bestellungsansicht";
						}

						
						$("#order_view_dialog").html(html);
						
						if (!$("#order_view_dialog").is(":visible"))
						{

							$("#order_view_dialog").dialog
								({	buttons:
									[
										{ text: "Schließen", click: function() { $(this).dialog("close"); } }
									],
									closeText:"Fenster schließen",
									closeOnEscape: true,
									//hide: { effect: 'drop', direction: "up" },
									modal:true,
									resizable:false,
									//show: { effect: 'drop', direction: "up" },
									title:dialog_title,
									width:800,
									beforeClose: function() {}
									
								});	
						}
					}

					//DIALOG ZUR BEARBEITUNG DER BESTELLUNG
					if (mode=="update")
					{
						if ($("#order_update_dialogbox").length==0)
						{
							$("body").append('<div id="order_update_dialogbox" style="display:none"></div>');
						}
						else
						{
							$("#order_update_dialogbox").html('');
						}

						$("#order_update_dialogbox").html(html);
						
						//DIALOG TITLE
						if(orders[orderid]["ordertype_id"]==4)
						{
							var dialog_title = "Umtausch bearbeiten";
						}
						else
						{
							var dialog_title = "Bestellung bearbeiten";
						}
						
						
						if (!$("#order_update_dialogbox").is(":visible"))
						{

						$("#order_update_dialogbox").dialog
							({	buttons:
								[
								//{ text: "Bestellung senden", click: function() { order_send(orderid);} },
									{ text: "Beenden", click: function() { $(this).dialog("close"); } }
								],
								closeOnEscape: true,
								closeText:"Fenster schließen",
								modal:true,
								resizable:false,
								title:dialog_title,
								width:800,
								beforeClose: function() 
								{ 
								/*
									//CHECK FOR PAYMENT METHOD
										//BEI UMTAUSCH MUSS KEINE ZAHLART DEFINIERT SEIN
									if (mode=="update" && orders[orderid]["ordertype_id"]!=4 && $("#send_order_dialog_PaymentBox_Payment").val()==0) 
									{
										order_update_dialog(orderid, mode); 
										$("#send_order_dialog_PaymentBox_Payment").focus(); 
										alert("Es muss eine Zahlart ausgewählt werden!");
										return;
									} 
									if (mode=="update" && orders[orderid]["shipping_type_id"]==0) 
									{
										order_update_dialog(orderid, mode); 
										change_ordershipping(orderid);
										alert("Es muss eine Versandart ausgewählt werden!");
										return;
									} 
*/
									// CHECK FOR CORRELATION Payment Nachnahme (&& Shipment Nachnahme)
									ShipmentTypes
									if (mode=="update" && $("#send_order_dialog_PaymentBox_Payment").val()==3 && ShipmentTypes[orders[orderid]["shipping_type_id"]]["expected_paymenttype_id"]!=3) 
									{
										alert("Bitte den Versand auf Nachnahme und die Versandkosten setzen!");
										order_update_dialog(orderid, mode);
										change_ordershipping(orderid);
										return;
									}
									// CHECK FOR CORRELATION Shipment Nachnahme (&& Payment Nachnahme)
									if (mode=="update" && ShipmentTypes[orders[orderid]["shipping_type_id"]]["expected_paymenttype_id"]==3 && $("#send_order_dialog_PaymentBox_Payment").val()!=3) 
									{
										alert("Bitte den Versand auf Nachnahme und die Versandkosten setzen!");
										order_update_dialog(orderid, mode);
										change_ordershipping(orderid);
										return;
									}

								}
								
							});	
							
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
	
	function highlight_searchstring(source, searchstring, $fontcolor)
	{
 		var searchstrings = searchstring.split(" "); 
		
		for (var i = 0; i<searchstrings.length; i++)
		{
			var source = source.split(searchstrings[i]).join('<font style="color:'+$fontcolor+'">'+searchstrings[i]+'</font>');

		}
		
		return source;
		
	}

	
	function find_customer()
	{
		var orderid=$("#find_customer_dialog_orderid").val();
		//var shop_id=$("#find_customer_dialog_shop_id").val();
		var site_id=$("#find_customer_dialog_site_id").val();

		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerSearch", mode:"find_customer", site_id:site_id, qry_string:$("#find_customer_qry_string").val()},
		function(data)
		{
		//	show_status2(data);
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				
				var qry_string=$("#find_customer_qry_string").val();
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
					html+='<th colspan="3" style="cursor:pointer" ondblclick="set_order_customer('+orderid+', '+user_id+', 0)">Kunde: <span class="highlight">'+$(this).find("name").text()+'</span></th>';
					html+='<th colspan="3" style="cursor:pointer" ondblclick="set_order_customer('+orderid+', '+user_id+', 0)">Shop-Nutzername: <span class="highlight">'+$(this).find("username").text()+'</span></th>';
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
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">'+highlight_searchstring($(this).find("bill_company").text(), qry_string, "blue")+'</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">';
							html+='<span class="highlight">'+$(this).find("bill_firstname").text()+' '+$(this).find("bill_lastname").text()+'</span><br />';
							html+='<small><b>e-Mail: </b><span class="highlight">'+$(this).find("usermail").text()+'</span></small>';
						html+='</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">';
							html+='<span class="highlight">'+$(this).find("bill_street").text()+' '+$(this).find("bill_number").text()+'</span><br />';
							html+='<small><b>Telefon: </b><span class="highlight">'+$(this).find("userphone").text()+'</span></small>';
						html+='</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">';
							html+='<span class="highlight">'+$(this).find("bill_additional").text()+'</span><br />';
							html+='<small><b>Mobil: </b><span class="highlight">'+$(this).find("usermobile").text()+'</span></small>';
						html+='</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')">';
							html+='<span class="highlight">'+$(this).find("bill_zip").text()+' '+$(this).find("bill_city").text()+'</span><br />';
							html+='<small><b>Fax: </b><span class="highlight">'+$(this).find("userfax").text()+'</span></small>';
						html+='</td>';
						html+='	<td ondblclick="set_order_customer('+orderid+', '+user_id+', '+order_adr_id+')"><span class="highlight">'+$(this).find("bill_country").text()+'</span></td>';
						html+='</tr>';
					});
					
				});
				html+='</table>';
				
				//html = highlight_searchstring(html, $("#find_customer_qry_string").val(), "blue");
				
				$("#customer_select_dialog").html(html);
			/*
				$(".highlight").each(
				function ()
				{
					$(this).html(highlight_searchstring($(this).html(), qry_string, "red"));
				});
			*/
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
			
			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerSearch", mode:"show_customer", user_id:user_id},
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
				/*			
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
					*/
							$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderAddressUpdate", 
									OrderID:orderid,
									customer_id:user_id,
									//shop_id:$("#find_customer_dialog_shop_id").val(),
								//	ship_country_code:country_code,
									addresstype:"bill",
									country_code:country_code,
									company:company,
									firstname:firstname,
									lastname:lastname,
									street:street, 
									number:number,
									additional:additional,
									zip:zip,
									city:city,
									usermail:usermail,
									userphone:userphone},
									function(data)
									{
										var $xml=$($.parseXML(data));
										var Ack = $xml.find("Ack").text();
										if (Ack=="Success") 
										{
											$("#find_customer_dialogbox").dialog("close");
											update_view(orderid, "order_update_dialog");
											//order_update_dialog(orderid, "update");
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
					$("#find_customer_dialogbox").dialog("close");
					update_view(orderid, "order_update_dialog");
					//order_update_dialog(orderid, "update");
				}
				else
				{
					show_status2(data);
				}
			});
		}

	}
	function order_address_update(orderid, addresstype)
	{

		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:orderid},
			function(data)
			{
				//show_status2(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					list_sort("order_address_update_country_code");

					if (addresstype=="bill")
					{
						$("#order_address_update_type").val(addresstype);
						$("#order_address_update_company").val($xml.find("bill_adr_company").text());
						$("#order_address_update_firstname").val($xml.find("bill_adr_firstname").text());
						$("#order_address_update_lastname").val($xml.find("bill_adr_lastname").text());
						$("#order_address_update_street").val($xml.find("bill_adr_street").text());
						$("#order_address_update_number").val($xml.find("bill_adr_number").text());
						$("#order_address_update_additional").val($xml.find("bill_adr_additional").text());
						$("#order_address_update_zip").val($xml.find("bill_adr_zip").text());
						$("#order_address_update_city").val($xml.find("bill_adr_city").text());
												
						if ($xml.find("bill_adr_country_id").text()!="")
						{
							$("#order_address_update_country_code").val(Countries[$xml.find("bill_adr_country_id").text()]["country_code"]);
						}
						else
						{
							$("#order_address_update_country_code").val("DE");
						}
					}
					else if (addresstype=="ship")
					{
						$("#order_address_update_type").val(addresstype);
						$("#order_address_update_company").val($xml.find("ship_adr_company").text());
						$("#order_address_update_firstname").val($xml.find("ship_adr_firstname").text());
						$("#order_address_update_lastname").val($xml.find("ship_adr_lastname").text());
						$("#order_address_update_street").val($xml.find("ship_adr_street").text());
						$("#order_address_update_number").val($xml.find("ship_adr_number").text());
						$("#order_address_update_additional").val($xml.find("ship_adr_additional").text());
						$("#order_address_update_zip").val($xml.find("ship_adr_zip").text());
						$("#order_address_update_city").val($xml.find("ship_adr_city").text());
						
						if ($xml.find("ship_adr_country_id").text()!="")
						{
							$("#order_address_update_country_code").val(Countries[$xml.find("ship_adr_country_id").text()]["country_code"]);
						}
						else
						{
							$("#order_address_update_country_code").val("DE");
						}
					}


					$("#order_address_update_usermail").val($xml.find("usermail").text());
					$("#order_address_update_userphone").val($xml.find("userphone").text());
					
					$("#order_address_update_customer_id").val($xml.find("customer_id").text());
					$("#order_address_update_site_id").val($xml.find("order_site_id").text());
					//$("#order_address_update_shop_id").val($xml.find("shop_id").text());

					$("#order_address_updateDialog").dialog
					({	buttons:
						[
							//{ text: "Nach Kunde suchen", click: function() { find_customer(orderid, "");} },
							{ text: "Speichern", click: function() { order_address_update_save(orderid);} },
							{ text: "Beenden", click: function() { $(this).dialog("close");} }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Adresse bearbeiten",
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
	
	function find_customer_dialog(orderid, site_id)
	{
		$("#find_customer_qry_string").val("");
		//$("#find_customer_dialog_shop_id").val(shop_id);
		$("#find_customer_dialog_site_id").val(site_id);
		$("#find_customer_dialog_orderid").val(orderid);
		$("#find_customer_dialogbox").dialog
			({	buttons:
				[
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
	
	
	function order_address_update_save(orderid)
	{
		//CHECK FOR CUSTOMER ID 
		// IF 0 -> CREATE NEW CMS_USER
		if ($("#order_address_update_customer_id").val()==0)
		{
			var usermail=$("#order_address_update_usermail").val();
			var firstname=$("#order_address_update_firstname").val();
			var lastname=$("#order_address_update_lastname").val();
			//var shop_id=$("#order_address_update_shop_id").val();
			var site_id = $("#order_address_update_site_id").val();

			//if (usermail=="") {alert("Bei Neukunden bitte eine E-Mailadresse angeben"); return;}
			if (lastname=="") {alert("Bei Neukunden bitte einen Nachnamen angeben"); return;} 
						
			$.post("<?php echo PATH;?>soa2/", { API: "cms", APIRequest: "CMS_UserCreate", usermail:usermail, firstname:firstname, lastname:lastname, site_id:site_id},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#order_address_update_customer_id").val($xml.find("customer_id").text());
					order_address_update_save2(orderid);
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
			order_address_update_save2(orderid);
		}
	}
					
		
	function order_address_update_save2(orderid)
	{
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderAddressUpdate", 
			OrderID:orderid,
			addresstype:$("#order_address_update_type").val(),
			customer_id:$("#order_address_update_customer_id").val(),
		//	shop_id:$("#order_address_update_shop_id").val(),
			company:$("#order_address_update_company").val(),
			firstname:$("#order_address_update_firstname").val(),
			lastname:$("#order_address_update_lastname").val(),
			street:$("#order_address_update_street").val(),
			number:$("#order_address_update_number").val(),
			additional:$("#order_address_update_additional").val(),
			zip:$("#order_address_update_zip").val(),
			city:$("#order_address_update_city").val(),
			country_code:$("#order_address_update_country_code").val(),
			
			usermail:$("#order_address_update_usermail").val(),
			userphone:$("#order_address_update_userphone").val()
						
			 },
			function(data)
			{
				//show_status2(data);
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					
					//NETTOPREIS KORREKTUR -> NUR FÜR MAPCO EBAY (DE & UK) && AP Ebay & Amazon
					if (orders[orderid]["shop_id"] == 3 || orders[orderid]["shop_id"] == 5 || orders[orderid]["shop_id"] == 4 || orders[orderid]["shop_id"] == 6)
					{
						$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderNetPriceCorrection", orderid:orderid},
						function($data)
						{
							try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
							var $Ack = $xml.find("Ack").text();
							if ($Ack!="Success") 
							{
								show_status2($data);
							}
							else
							{
								$("#order_address_updateDialog").dialog("close");
			

								//order_update_dialog(orderid, "update");
								update_view(orderid, "order_update_dialog");
							}
						});
					}
					//BRUTTOPREISKORREKTUR
					else
					{
						if (orders[orderid]["VAT"]!=0)
						{
							//GET ACTUAL VAT for country
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "CountriesGet"},
							function($data)
							{
								try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
								var $Ack = $xml.find("Ack").text();
								if ($Ack!="Success") {show_status2($data2); return;}
								
								var $VAT = 0;
								$xml.find("shop_countries").each( function()
								{
									if ($(this).find("country_code").text()==$("#order_address_update_country_code").val())
									{
										$VAT = $(this).find("VAT").text();
									}
								});
								
								if ($VAT == 0) $VAT = 19;
							
								$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, VAT:$VAT },
								function($data2)
								{
									try { $xml = $($.parseXML($data2));	} catch (err) {	show_status2(err.message); return;}
									var $Ack = $xml.find("Ack").text();
									if ($Ack!="Success") {show_status2($data2); return;}
						
									$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "OrderVATUpdate", order_id:orderid},
									function($data3)
									{
										wait_dialog_hide();
										//alert(data);
										try { $xml = $($.parseXML($data3));	} catch (err) {	show_status2(err.message); return;}
										var $Ack = $xml.find("Ack").text();
										if ($Ack!="Success") {show_status2($data3); return;}
										$("#order_address_updateDialog").dialog("close");
										update_view(orderid, "order_update_dialog");
									});
								});
							});
						}
						else
						{
							$("#order_address_updateDialog").dialog("close");
							update_view(orderid, "order_update_dialog");
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
	
	function change_orderVAT_dialog(orderid)

	{
		if ($("#change_orderVAT_dialogbox").length==0)
		{
			$("body").append('<div id="change_orderVAT_dialogbox" style="display:none"></div>');
		}

		if (orders[orderid]["shop_id"] == 1 || orders[orderid]["shop_id"] == 3 || orders[orderid]["shop_id"] == 5 || orders[orderid]["shop_id"] == 7)
		{

			$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "CountriesGet"},
			function($data)
			{
				
				var html='';
				html+='<table>';
				html+='<tr>';
				html+='	<td>';
				
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") 
				{
					show_status2($data); 
					html+='		<select id="VAT_update" size=1>';
					if (orders[orderid]["VAT"]==0) var selected = 'selected'; else var selected ='';
					html+='		<option value=0 '+selected+'>0 %</option>';
					if (orders[orderid]["VAT"]==19) var selected = 'selected'; else var selected ='';
					html+='		<option value=19 '+selected+'>19 %</option>';
					html+='		</select>';
				}
				else
				{
					html+='		<select id="VAT_update" size=1>';
					if (orders[orderid]["VAT"]==0) var selected = 'selected'; else var selected ='';
					html+='		<option value=0 '+selected+'>0 %</option>';
					$xml.find("shop_countries").each( function()
					{
						if ($(this).find("country_code").text()==orders[orderid]["bill_country_code"])
						{
							if (orders[orderid]["VAT"]==$(this).find("VAT").text()) var selected = 'selected'; else var selected ='';
							html+='		<option value='+$(this).find("VAT").text()+' '+selected+'>'+$(this).find("VAT").text()+' %</option>';
						}
					});
					html+='		</select>';
					//html+='	Mehrwertsteuersatz:&nbsp;<input type="text" id="VAT_update" size = "3" value="'+orders[orderid]["VAT"]+'" />&nbsp;<b>%</b>';
				}
				html+='	</td>';
				html+='</tr>';
				html+='</table>';
				$("#change_orderVAT_dialogbox").html(html);

			});
		}
		else
		{
			var html='';
			html+='<table>';
			html+='<tr>';
			html+='	<td>';
			html+='		<select id="VAT_update" size=1>';
			if (orders[orderid]["VAT"]==0) var selected = 'selected'; else var selected ='';
			html+='		<option value=0 '+selected+'>0 %</option>';
			if (orders[orderid]["VAT"]==19) var selected = 'selected'; else var selected ='';
			html+='		<option value=19 '+selected+'>19 %</option>';
			html+='		</select>';
			html+='	</td>';
			html+='</tr>';
			html+='</table>';
			$("#change_orderVAT_dialogbox").html(html);
		}
		

		$("#change_orderVAT_dialogbox").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { change_order_VAT_save(orderid); } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neuen Mehrwertsteuersatz eingeben",
			width:250
		});	
		
	}
	
	function change_order_VAT_save(orderid)
	{
		var $VAT = $("#VAT_update").val().replace(/,/g, ".")*1;
		if ($VAT !=19 && $VAT !=0)
	//	if ($VAT == "" || !isNumber($VAT))
		{
			msg_box("Bitte einen Mehrwertsteuersatz angeben (0 oder 19)");
			$("#VAT_update").focus();
			return;
		}

		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, VAT:$VAT },
		function($data)
		{
			try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
			var $Ack = $xml.find("Ack").text();
			if ($Ack!="Success") {show_status2($data); return;}

			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "OrderVATUpdate", order_id:orderid},
			function($data)
			{
				wait_dialog_hide();
				//alert(data);
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				$("#change_orderVAT_dialogbox").dialog("close");
				update_view(orderid, "order_update_dialog");
			});
		});
				
		
	}


	function change_ordershipping(orderid)
	{
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:orderid},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{

				//	$("#change_orderpositions_Currency").val($xml.find("Currency_Code").text());
					var VAT=$xml.find("VAT").text();
					if (VAT == 0) $VAT = 1; else $VAT = (VAT/100 )+1;
					$("#change_ordershipping_VAT").val($VAT);

					var $payment_type_id = 0;
					
					$xml.find("order").each(
					function()
					{
						if (orderid==$(this).find("orderid").text())
						{
							$("#change_ordershipping_shipping_type_id").val($(this).find("shipping_type_id").text());
							$("#change_ordershipping_shipping_costsEURgross").val($(this).find("shipping_costs").text().toString().replace(".", ","));
							$("#change_ordershipping_shipping_costsEURnet").val($(this).find("shipping_net").text().toString().replace(".", ","));
							$("#change_ordershipping_shop_id").val($xml.find("shop_id").text());
							$("#currency_shipping_costs").text($xml.find("Currency_Code").text());
							
							$payment_type_id = $(this).find("payment_type_id").text();
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
					
					//FIX FÜR 0 BESTELLPOSITIONEN
					if ($("#change_ordershipping_exchangeratetoEUR").val()=="") $("#change_ordershipping_exchangeratetoEUR").val(Currencies[$xml.find("Currency_Code").text()]["exchange_rate_to_EUR"])

					var shipping_costsFCgross=($("#change_ordershipping_shipping_costsEURgross").val().replace(",",".")*1)*$("#change_ordershipping_exchangeratetoEUR").val();
					var shipping_costsFCnet=($("#change_ordershipping_shipping_costsEURnet").val().replace(",",".")*1)*$("#change_ordershipping_exchangeratetoEUR").val();
					$("#change_ordershipping_shipping_costsFCgross").val(shipping_costsFCgross.toFixed(2).toString().replace(".", ","));
					$("#change_ordershipping_shipping_costsFCnet").val(shipping_costsFCnet.toFixed(2).toString().replace(".", ","));

					
					if ($xml.find("Currency_Code").text()!="EUR") $(".change_ordershipping_EUR_col").show(); else $(".change_ordershipping_EUR_col").hide();
					
					$("#change_ordershipping_dialog").dialog
					({	buttons:
						[
							{ text: "Speichern", click: function() { change_ordershipping_save(orderid, $payment_type_id);} },
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

	function change_ordershipping_save(orderid, $payment_type_id)
	{
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
		var shipping_costs=($("#change_ordershipping_shipping_costsFCgross").val().replace(/,/g, "."))*1;
		var shipping_net=($("#change_ordershipping_shipping_costsFCnet").val().replace(/,/g, "."))*1;
		var shop_id=$("#change_ordershipping_shop_id").val();
		var shipping_type_id=$("#change_ordershipping_shipping_type_id").val();
		
		var shipping_detail = ShipmentTypes[shipping_type_id]["title"];
		shipping_detail+=", "+PaymentTypes[$payment_type_id]["title"];



		if (shipping_type_id==0)
		{
			$("#change_ordershipping_shipping_type_id").focus();
			msg_box("Es muss eine Versandart ausgewählt werden!");
			return;
		}
		else
		{
		
			$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:orderid, shipping_costs:shipping_costs, shipping_net:shipping_net, shipping_type_id:shipping_type_id, shipping_details:shipping_detail },
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
									//alert(data);

									$("#change_ordershipping_dialog").dialog("close");
									update_view(orderid, "order_update_dialog");
									//order_update_dialog(orderid, "update");
									
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
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:orderid},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					
					$("#change_orderpositions_Currency").val($xml.find("Currency_Code").text());
					//$("#change_orderpositions_VAT").val($xml.find("VAT").text());
					//$("#change_orderpositions_VATFree").val($xml.find("VATFree").text());
					var VAT=$xml.find("VAT").text();
					if (VAT == 0) $VAT = 1; else $VAT = (VAT/100 )+1;
					$("#change_orderpositions_VAT").val($VAT);
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
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
		
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
					update_view(orderid, "order_update_dialog");
					//order_update_dialog(orderid, "update");
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
		
		var mwstmultiplier=$("#change_orderpositions_VAT").val();

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
		var mwstmultiplier=$("#change_orderpositions_VAT").val();
		var gross=(brutto.replace(/,/g, "."))*1;
		
		if (gross!=0) var netto = gross/mwstmultiplier; else var netto = 0;
		
		change_orderpositions_setPrices(netto);
		
	}
	
	function get_netto_from_FCnetto(FCnetto)
	{
		var exchangerate=$("#change_orderpositions_exchangeratetoEUR").val()*1;
		var FCnet=(FCnetto.replace(/,/g, "."))*1;

		if (FCnet!=0) var netto = FCnet/exchangerate; else var netto = 0;
	//	alert(netto);
		change_orderpositions_setPrices(netto);
		
	}
	
	function get_netto_from_FCbrutto(FCbrutto)
	{
		var mwstmultiplier=$("#change_orderpositions_VAT").val();
		var exchangerate=$("#change_orderpositions_exchangeratetoEUR").val()*1;
		var FCgross=(FCbrutto.replace(/,/g, "."))*1;
		
		if (FCgross!=0) var netto = FCgross /exchangerate/ mwstmultiplier; else var netto = 0;
		change_orderpositions_setPrices(netto);
		
	}
	
	
	function change_ordershipping_setPrices(netto)
	{
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
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
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
		var exchangerate=$("#change_ordershipping_exchangeratetoEUR").val()*1;

		var FCgross=(FCbrutto_shipping.replace(/,/g, "."))*1;
		if (FCgross!=0) var netto = FCgross / exchangerate / mwstmultiplier; else var netto = 0;
		
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
		var mwstmultiplier=$("#change_ordershipping_VAT").val();
		var brutto=(brutto_shipping.replace(/,/g, "."))*1;
		if (brutto!=0) var netto = brutto / mwstmultiplier;
		
		change_ordershipping_setPrices(netto);
	}
	
	function add_orderpositions(parentorderid, shop_id)
	{
		var combined_with=0;
//alert(shop_id);
		//FELDER LEEREN
		$("#change_orderpositions_MPN").val("");
		$("#change_orderpositions_amount").val("0");
		$("#change_orderpositions_ArtBez").text("");

		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderDetailGet_neu", OrderID:parentorderid},
			function(data)
			{
				//alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					var VAT=$xml.find("VAT").text();
					if (VAT == 0) $VAT = 1; else $VAT = (VAT/100 )+1;
					$("#change_orderpositions_VAT").val($VAT);

					var currency = $xml.find("Currency_Code").text();
					$("#change_orderpositions_Currency").val(currency);
					//GET EXCHANGERATE
					var exchangerate=Currencies[currency]["exchange_rate_to_EUR"];
					$("#change_orderpositions_exchangeratetoEUR").val(exchangerate);
					//FOREIGN CURRENCY
					$(".change_orderpositions_currency").text(currency);

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

					combined_with = $xml.find("combined_with").text();
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
				{ text: "Speichern", click: function() { add_orderpositions_save(parentorderid, shop_id, combined_with, $VAT);} },
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

	function add_orderpositions_save(parentorderid, shop_id, combined_with, mwstmultiplier)
	{
	//	parentorderid=1749450;
		
		
		
		var item_id = $("#change_orderpositions_ItemID").val();
			if (item_id=="" || item_id==0) { alert("Es muss ein Artikel angegeben werden"); return;}
		var amount = $("#change_orderpositions_amount").val();

			if (amount=="" || amount==0) { alert("Es muss eine Stückzahl (größer 0) angegeben werden"); return;}
		var price = $("#change_orderpositions_FCprice_gross").val().replace(/,/g, ".")*1;
		var netto = $("#change_orderpositions_FCprice_net").val().replace(/,/g, ".")*1;

		var Currency_Code = $("#change_orderpositions_Currency").val();
		var exchange_rate_to_EUR = $("#change_orderpositions_exchangeratetoEUR").val();
		
		//IF SHOPTYPE = EBAY -> ITEM wird mit neuer Order angelegt und miteinander kombiniert
		var id_order=parentorderid;

		if (Shop_Shops[shop_id]["shop_type"]==2)
		{
			id_order=0;

			//CHECK, ob schon eine "neue" kombinierte nicht-Ebay Order existiert -> wenn ja, neuen Artikel dieser Order hinzufügen
			$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderDetailGet_neu",  OrderID:parentorderid},
			function($data)
			{
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				
				var $orders=$xml.find("orders");
				$orders.find("order").each(
				function ()
				{
					//alert($(this).find("foreign_OrderID").text());
					if ($(this).find("foreign_OrderID").text()=="") id_order = $(this).find("orderid").text();
				});

				//KEINE ANDERE ORDER VORHANDEN -> neue Order Anlegen und, kombinieren mit parantorde und artikel hinzufügen
				if (id_order == 0 || id_order == "")
				{
					//zu erstellende Order wird kombiniert
					if (combined_with<1) combined_with=parentorderid;
				
					$.post("<?php echo PATH; ?>soa2/index.php", { API: "shop", APIRequest: "OrderAdd", mode:"copy", id_order:parentorderid, foreign_OrderID:"", shipping_costs:0, shipping_net:0, combined_with:combined_with},
					function($data)
					{
						try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
						var $Ack = $xml.find("Ack").text();
						if ($Ack!="Success") {show_status2($data); return;}
						
						id_order=$xml.find("id_order").text();

						//UPDATE PARENT ORDER -> COMBINED_WITH
						$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderUpdate", SELECTOR_id_order:parentorderid, combined_with:combined_with},
						function($data)
						{
							try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
							var $Ack = $xml.find("Ack").text();
							if ($Ack!="Success") {show_status2($data); return;}
							
							// INSERT ORDERITEM
							$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderItemAdd", mode:"new", order_id:id_order, item_id:item_id, amount:amount, price:price, netto:netto, Currency_Code:Currency_Code, exchange_rate_to_EUR:exchange_rate_to_EUR},

							function($data)
							{
								try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
								var $Ack = $xml.find("Ack").text();
								if ($Ack!="Success") {show_status2($data); return;}
								
								show_status("Bestellposition wurde erfolgreich eingefügt");
								$("#change_orderpositions_dialog").dialog("close");
								update_view(parentorderid, "order_update_dialog");
							//	order_update_dialog(orderid, "update");
								return;
							});
						});
					});
				} // END OF IF order_id==0
				else
				{
					//"neue" Order bereits vorhanden => Artikel dieser Order hinzufügen
					// INSERT ORDERITEM
					$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderItemAdd", mode:"new", order_id:id_order, item_id:item_id, amount:amount, price:price, netto:netto, Currency_Code:Currency_Code, exchange_rate_to_EUR:exchange_rate_to_EUR},
					function(data)
					{
						try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
						var $Ack = $xml.find("Ack").text();
						if ($Ack!="Success") {show_status2($data); return;}
						
						show_status("Bestellposition wurde erfolgreich eingefügt");
						$("#change_orderpositions_dialog").dialog("close");
						update_view(parentorderid, "order_update_dialog");
					//	order_update_dialog(orderid, "update");
						return;
					});
				}
			});
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
					update_view(id_order, "order_update_dialog");
					//order_update_dialog(id_order, "update");
					return;
				}
				else
				{
				}
			});
		}

	}

	function show_hide_notes(orderid)
	{	
		$class= ".hidden_note"+orderid;
		if ( $($class).css('display') == 'none' )
		{	
			$($class).css('display','');
		}
		else
		{
			$($class).css('display','none');
		}
	}
	
	
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
		echo '<input type="hidden" id="change_orderpositions_VAT"; />';
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
	echo '		<input type="hidden" id="change_ordershipping_VAT" />';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

	echo '<div id="find_customer_dialogbox" style="display:none";>';
	echo '<input type="text" size ="60" id="find_customer_qry_string" />';
	echo '<input type="hidden" id="find_customer_dialog_orderid" />';
	echo '<input type="hidden" id="find_customer_dialog_site_id" />';
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

	//GET USER SITES
	$user_sites = array();
	$res_user_sites = q("SELECT * FROM cms_users_sites WHERE user_id = ".$_SESSION["id_user"], $dbweb, __FILE__, __LINE__);
	while ($row_user_sites = mysqli_fetch_assoc($res_user_sites))
	{
		$user_sites[]=$row_user_sites["site_id"];
	}

	//GET SHOPS FOR SITEs
	$shops = array();
	if (sizeof($user_sites)>0)
	{
		$res_shops=q("SELECT * FROM shop_shops WHERE site_id IN (".implode(",", $user_sites).") AND active = 1", $dbshop, __FILE__, __LINE__);
		while ($row_shops=mysqli_fetch_assoc($res_shops))
		{ 
			$shops[$row_shops["id_shop"]] = $row_shops["title"];
			//GET CHILDSHOPS
			$res_shops2=q("SELECT * FROM shop_shops WHERE parent_shop_id = ".$row_shops["id_shop"]." AND active = 1", $dbshop, __FILE__, __LINE__);
			while ($row_shops2 = mysqli_fetch_assoc($res_shops2))
			{
					$shops[$row_shops2["id_shop"]] = $row_shops2["title"];
			}
		}
	}

	//FILTER
	echo '<div id="filterbar1" style="width:1200px">';
	echo '	<span class="tabs">Plattform: ';
	echo '		<select id="FILTER_Platform" name="select1" size="1" onChange="set_FILTER_Platform();">';
	if (sizeof($shops)>1)
	{
		echo '			<option value=0>Alle</option>';
	}
	foreach ($shops as $shopid => $shoptitle)
	{
		echo '	<option value="'.$shopid.'">'.$shoptitle.'</option>';
	}
	echo '		</select>';
	echo '	</span>';
	echo '	<span class="tabs">';
	echo '		<select id="FILTER_Country" size="1" onChange="set_FILTER_Country();">';
	echo '			<option value="">Alle Länder</option>';
	echo '			<option value="national">DE + AT</option>';
	echo '			<option value="ES+PT">ES + PT</option>';
	echo '			<option value="IT">IT</option>';
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
	
	echo '	<span class="tabs">Vorgangsart: ';
	echo '		<select id="FILTER_Ordertype" size="1" onChange="set_FILTER_Ordertype();">';
	echo '				<option value="0">Alle Bestellungen</option>';
	$res_ordertype=q("SELECT * FROM shop_orders_types WHERE public = 1;", $dbshop, __FILE__, __LINE__);
	while ($row_ordertype=mysqli_fetch_array($res_ordertype))
	{
		echo '			<option value='.$row_ordertype["id_ordertype"].'>'.$row_ordertype["title"].'</option>';
	}
	echo '		</select>';
	echo '	</span>';

	
	echo '</div>';
	echo '<br style="clear:both" />';

	echo '<div id="filterbar2" style="width:1200px">';
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
	echo '			<option value="10">Rechnungsnummer</option>';
	echo '			<option value="11">Auftrags ID</option>';
	echo '			<option value="9">Versand Tracking-ID</option>';
	echo '			<option value="12">Amazon Order ID</option>';
	echo '			<option value="13">Kunden Auftragsnummer</option>';
	echo '		</select>';
	if (isset($_GET["FILTER_Searchfield"]))
	{
		echo '		<input type="text" size="40" id="FILTER_Searchfield" style="background-color:#ffdddd" value="'.$_GET["FILTER_Searchfield"].'" />';

	}
	else
	{
		echo '		<input type="text" size="40" id="FILTER_Searchfield" style="background-color:#ffdddd" value="" />';
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
	echo '	<td colspan="2">Land<br />';
	echo '	<input type="text" size="50" id="DHLretourlabel_address_country" /></td>';
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
	
	/*
	//SEND ORDER DIALOG
	echo '<div id="send_order_dialog" style="display:none">';
	echo '	<div id="send_order_dialog_addressBox"></div>';
	echo '	<div id="send_order_dialog_PaymentBox"></div>';
	echo '	<div id="send_order_dialog_OrderBox"></div>';
	echo '</div>';
	*/
	
	//PRICE ALERT DIALOG
	echo '<div id="price_alert_dialog" style="display:none">';
	echo '</div>';

	
	//UPDATE SHIPPING ADDRESS DIALOG
	echo '<div id="order_address_updateDialog" style="display:none;">';
	echo '<table>';
	echo '<input type="hidden" id="order_address_update_type" />';
/*
	echo '<tr>';
	echo '	<td colspan="2">Verwenden als:<br />';
	echo '	<select id="order_address_update_type" size=1>';
	echo '		<option value="both">Rechnungs & Lieferadresse</option>';
	echo '		<option value="bill">Rechnungsadresse</option>';
	echo '		<option value="ship">Lieferadresse</option>';
	echo '	</select>';
	echo '</td>';
	echo '</tr>';
*/
	echo '<tr>';
	echo '	<td colspan="2">Firma<br />';
	echo '	<input type="text" size="50" id="order_address_update_company" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td colspan="2">Adresszusatz / Postnummer<br />';
	echo '	<input type="text" size="50" id="order_address_update_additional" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Vorname<br />';
	echo '	<input type="text" size="20" id="order_address_update_firstname" /></td>';
	echo '	<td>Nachname<br />';
	echo '	<input type="text" size="20" id="order_address_update_lastname" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Straße / Packstation<br />';
	echo '	<input type="text" size="20" id="order_address_update_street" /></td>';
	echo '	<td>Nummer / Packstat.Nr.<br />';
	echo '	<input type="text" size="5" id="order_address_update_number" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Postleitzahl<br />';
	echo '	<input type="text" size="10" id="order_address_update_zip" /></td>';
	echo '	<td>Stadt<br />';
	echo '	<input type="text" size="20" id="order_address_update_city" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td colspan="2">Land<br />';
	echo '	<select id="order_address_update_country_code" size="1">';
	$res=q("SELECT * FROM shop_countries;", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		echo '<option value="'.$row["country_code"].'">'.$row["country"].'</option>';
	}
	echo '	</select>';
	echo '	</td>';
	echo '</tr><tr>';
	echo '	<td colspan="2">Telefon<br /><input type="text" size="50" id="order_address_update_userphone" /></td>';
	echo '</tr><tr>';
	echo '	<td colspan="2">E-Mail<br /><input type="text" size="50" id="order_address_update_usermail" /></td>';
	echo '</tr>';
	echo '<input type="hidden" id="order_address_update_customer_id" />';
	echo '<input type="hidden" id="order_address_update_site_id" />';
	echo '</table>';
	echo '</div>';
	
	
	//ALERT DIALOG
	echo '<div id="msg_box" style="display:none"></div>';
	echo '<div id="order_events" style="display:none"></div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>	