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
<script src="javascript/crm/OrderUpdate.php" type="text/javascript" /></script>


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
	echo 'var FILTER_DateSearchFor="'.$_GET["FILTER_DateSearchFor"].'";'."\n";
	echo 'var FILTER_Ordertype="'.$_GET["FILTER_Ordertype"].'";'."\n";
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
	echo 'var FILTER_DateSearchFor="firstmod";'."\n";
	echo 'var date_from="";'."\n";
	echo 'var date_to="";'."\n";
	echo 'var OrderBy="firstmod";'."\n";
	echo 'var OrderDirection="down";'."\n";
	echo 'var FILTER_Ordertype=-1;'."\n"; // -1 Fix for initial load in function set_FILTER_Ordertype
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
	echo 'var FILTER_Ordertype=-1;'."\n"; // -1 Fix for initial load in function set_FILTER_Ordertype
	echo 'var FILTER_DateSearchFor="firstmod";'."\n";
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
	UserShops = new Array();
	$OrderTypes = new Object;
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
					//show_status2(print_r(OrderTypes));
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
		//USERSHOPS GET
		$.post("<?php echo PATH; ?>soa2/", { API: "cms", APIRequest: "UserShopsGet", userid:user_id },
		function(data)
		{
			//alert(data);
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack!="Success") {show_status2(data); return;}
			$xml.find("usershop").each(
			function()
			{
				UserShops[$(this).find("usershop_id").text()] = $(this).find("usershop_title").text();
			});
			ready_function();
		});	
		/*
		//ORDERTYPES		
		var postfields = new Object();
		postfields['API'] 			= 'cms';
		postfields['APIRequest'] 	= 'TableDataSelect';
		postfields['table']			= "shop_orders_types";
		postfields['db'] 			= "dbshop";
		postfields['where'] 		= "WHERE public = 1";
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			var $OrderTypes = new Array();
			$xml.find("shop_orders_types").each( function()
			{
				$OrderTypes[$(this).find("id_ordertype").text()] = $(this).find("title").text();
				
			});
			ready_function();
		});
		*/
	});

	function ready_function()
	{
		preloadcount++;
		var $percent=Math.round(preloadcount/10*100);
		wait_dialog_show("Cache wird gefüllt", $percent);
		if (preloadcount==10) 
		{
			$("#push").remove();
	
			if ($("#filter_bar").length == 0)
			{
				$("#content").append('<div id="filter_bar" style="display:inline; float:left; width:95%;" class="border_standard"></div>');
			}
		
			draw_navigation();
			draw_filter_bar1();
			draw_filter_bar2();
			
			// ENTER FÜR SUCHE  
			$("#FILTER_Searchfield").bind("keypress", function(e) {
				if(e.keyCode==13){
					FILTER_Searchfield=$("#FILTER_Searchfield").val();
					view_box();
				}
			});
			// FOCUS AUTOM. AUF SUCHFELD
			$("#FILTER_Searchfield").focus();				

			//SUCHE STANDARD AUF ORDER ALLE
			$("#FILTER_Ordertype").val(0);
			//WEITERE FILTER IN ABHÄNGIGKEIT SETZEN
			set_FILTER_Ordertype();

			if ($("#tableview").length == 0)
			{
				$("#content").append('<br style="clear:both" /><div id="tableview" style="display:inline; width:95%;"></div>');
			}
			
			if (jump_to)
			{
				view_box(<?php echo $_GET["orderid"]; ?>);
			}
			else
			{
				$("#tableview").html('<span style="text-align:center; width:100%;"><b>Zur Anzeige bitte Suchfilter nutzen</b></span>');
			}
			
			if ($("#push").length == 0)
			{
				$("#content").append('<div id="push"></div>');
			}

			wait_dialog_hide();
		}
	}


	
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
		//NAVIGATION TEMPLATE
		if ($("#navigation").length == 0)
		{
			$("#filter_bar").append('<div id="navigation" style="float:left;marginwidth:1200px">');
		}
		
		var $html = '';
		$html  += '	<span class="tabs" id="PageBack" style="width:120px; text-align:center"></span>';
		$html  += '	<span class="tabs" id="PageSelect" style="width:250px; text-align:center"></span>';
		$html  += '	<span class="tabs" id="pageRange" style="width:150px; text-align:center"></span>';
		$html  += '	<span class="tabs" id="ResultsBox" style="width:300px; text-align:center"></span>';
		$html  += '	<span class="tabs" id="PageForward" style="width:120px; text-align:center"></span>';
		$html  += '	<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Neue Bestellung erfassen" title="Neue Bestellung erfassen" onclick="order_add();"/>';

		$html  += '<br style="clear:both" />';

		$("#navigation").html($html);
		
		
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
			if ( $xml.find("Error").length>0 && $xml.find("Error Code").text()!=9865 )
			{
				//show_status2($data);
				var $Code=$xml.find("Error Code").text();
				var $shortMsg=$xml.find("Error shortMsg").text();
				var $longMsg=$xml.find("Error longMsg").text();
				alert("Fehler "+$Code+"\n\n"+$shortMsg+"\n\n"+$longMsg);
				//alert("Fehlercode: "+$Code+");
				return;
			}

			if($xml.find("Code").text()=="collateral_alert" && (<?php echo $_SESSION["id_user"]; ?>==21371 || <?php echo $_SESSION["id_user"]; ?>==22044))
			{
				$("#price_alert_dialog").html($xml.find("text").text());
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
				return;
			}
			if($xml.find("Error Code").text()==9865 && <?php echo $_SESSION["id_user"]; ?>==21371)
			{
				$("#price_alert_dialog").html($xml.find("text").text());
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
				return;
			}
			else if($xml.find("Error Code").text()==9865 && <?php echo $_SESSION["id_user"]; ?>!=21371)
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
				return;
				//show_status2($data);
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
		var FILTER_Ordertype_Old = FILTER_Ordertype;
		FILTER_Ordertype=$("#FILTER_Ordertype").val();
		
		//SET ORDERTYPE CORRELATION
			//TEST IF SOMETHING HAS CHANGED
		if ( (FILTER_Ordertype in {0:'', 1:'', 2:'', 3:'', 4:'', 5:''}) != (FILTER_Ordertype_Old in {0:'', 1:'', 2:'', 3:'', 4:'', 5:''}) )
		{
			if ( (FILTER_Ordertype in {0:'', 1:'', 2:'', 3:'', 4:'', 5:''}) )
			{
				//SET STATUS FILTERS FOR ORDERS
				draw_FILTER_Status('order');
				//SET DATE FILTERS FOR ORDERS
				draw_FILTER_DateSearchFor('order');

			}
			else
			{
				//SET STATUS FILTERS FOR RETURNS
				draw_FILTER_Status('return');
				//SET DATE FILTERS FOR RETURNS
				draw_FILTER_DateSearchFor('return');
			}
		}
	}
	
	function set_FILTER_DateSearchFor()
	{
		FILTER_DateSearchFor=$("#FILTER_DateSearchFor").val();
	}
	
	function unset_date()
	{
		date_from="";
		$("#date_from").val("");
		date_to="";
		$("#date_to").val("");
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
			if(user_id != 28623 && user_id != 28625)
			{	
				if (Shop_Shops[orders[orderid]["shop_id"]]["shop_type"]==2)
				{
					var items_OK = true;
			
					for (i in orders[orderid]["Items"])
					//for (i=0;i<orders[orderid]["Items"].length; i++)
					{
						if (orders[orderid]["Items"][i]["OrderItemChecked"]!=1) items_OK = false;
					}
					if (!items_OK && orders[orderid]["fz_fin_mail_count"]<2) return false;
				}
			}
		}
		//ANGEBOT?
		if (orders[orderid]["ordertype_id"] == 5) return false;
		
		//UMTAUSCH?
		if (orders[orderid]["ordertype_id"] == 4) 
		{
			// CHECK, ob Bestellung bereits versandt wurde
			if (orders[orderid]["AUF_ID"]!=0) return false;

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
	
	if ( orders[orderid]["usermail"] != ""  )
	{
		if (orders[orderid]["ordertype_id"] == 5 )
		{	
			menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:send_orderOffer('+orderid+');">Angebot versenden</a></li>';
		}
		else 
		{
			menu+='	<li style="margin:5px; margin-left:-22px;"><a href="javascript:send_orderConfirmation('+orderid+');">Bestellbestätigung versenden</a></li>';
		}
	}

	//AUFTRAG ABBRECHEN
		if (orders[orderid]["status_id"]!=4 && orders[orderid]["status_id"]!=8)
		{
			menu+='	<li style="margin:10px; margin-left:-22px;"><a href="javascript:order_abort_dialog('+orderid+');">Auftrag abbrechen</a></li>';
		}
	
	//BESTELLVERLAUF
		menu+='<li style="margin:10px; margin-left:-22px;"><a href="javascript:show_order_events('+orderid+');">Bestellverlauf</a></li>';
	//AUFID_RESET
		if ( <?php echo $_SESSION['userrole_id']; ?> == 1 )
		{
			menu+='<li style="margin:10px; margin-left:-22px;"><a href="javascript:order_aufid_reset('+orderid+');">AuftragsID Reset</a></li>';
		}
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
			var subject = '';
			if ( order_id > 0 && order_id != '' )
			{
				subject = 'ID: '+order_id;
			}
			td = $('<td><input type="text" id="con_subject" style="width: 400px;" value="'+subject+'"></td>');
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
		if (confirm("Soll eine Bestellbestätigung versendet werden?"))
		{

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
	}
	
	function send_orderOffer ($orderid)
	{
		alert("Nichts passiert");	
	}
	
	function order_aufid_reset($orderid)
	{
		show_actions_menu($orderid); //hide options

		$post_data 					= new Object;
		$post_data['API'] 			= 'shop';
		$post_data['APIRequest']	= 'OrderAufidReset';
		$post_data['order_id'] 		= $orderid;

		$.post('<?php echo PATH;?>soa2/', $post_data, function($data){
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2('AuftragsID konnte nicht zurückgesetzt werden!. Error Code:'+ $xml.find("Code").text()); wait_dialog_hide(); return; }
			
			show_status("AuftragsID erfolgreich zurückgesetzt");
			update_view($orderid);
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
				html+='<div style="float:left">';
				html+='<table style="width:550px">';
				html+='<tr>';
				html+='	<th>Zeit</th>';
				html+='	<th>Vorgang</th>';
				html+='	<th>Ausgeführt durch</th>';
				html+='	<th>Details</th>'
				html+='</tr>';
				
				$xml.find("event").each(
				function()
				{
					html+='<tr>';
					html+='	<td>'+convert_time_from_timestamp($(this).find("firstmod").text(), "complete")+'</td>';
					html+='	<td>'+$(this).find("eventtitle").text()+'</td>';
					html+='	<td>'+$(this).find("username").text()+'</td>';
					//Details
					html+='	<td>';
					html+='		<span id="event_data_show_link"><a href="javascript:show_order_event_detail ( '+$(this).find("id_event").text()+' )">Detail anzeigen</a></span>';
					html+='	</td>';
					
					html+='</tr>';
				});
				html+='</table>';
				html+='</div>';
				
				html+='<div id="event_detail" style="width:550px; float:right; overflow:auto; display:none; text-align:left;"></div>';
				
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
					width:1200
				});		
				
				
				
			}
			else
			{
				show_status2(data);
			}

		});

	}
	
	function show_order_event_detail ( $event_id )
	{
		$("#event_detail").html("");
		
		var $detailtext = '';
		
		
		$postfield = new Object;
		$postfield['API'] = 'cms';
		$postfield['APIRequest'] = 'TableDataSelect';
		$postfield['select'] = 'data';
		$postfield['table'] = 'shop_orders_events';
		$postfield['db'] = 'dbshop';
		$postfield['where'] = 'WHERE id_event = '+$event_id;
		$postfield['cdata'] = 'false';
		
		$.post('<?php echo PATH;?>soa2/', $postfield, function($data){
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); return; }
			
			if ( typeof($detail) != "undefined" )
			{
				$detailtext += '<table style="width:550px;">';
				$detailtext += '<tr>';
				$detailtext += '	<th colspan="2">Event Details</th>';
				$detailtext += '</tr>';

				$detail.children().each(
				function()
				{
					var $tagname=this.tagName;
					$detailtext += '<tr>';
					$detailtext += 	'<th>'+$tagname+'</th>';
					$detailtext +=	'<td>'+$(this).text()+'</td>';
					$detailtext += '</tr>';												
				});
				
				$detailtext += '</table>';
			}
			else
			{
				$detailtext = $xml.find("data").text();
			}
		//	show_status2($detailtext);
			
			$("#event_detail").html($detailtext);
			$("#event_detail").show();
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
				if (orders[orderid]["Payments_TransactionState"]=="Pending" || orders[orderid]["Payments_TransactionState"]=="" || orders[orderid]["Payments_TransactionState"]=="Created" || orders[orderid]["Payments_TransactionState"]=="Denied" )
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
			html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left;  cursor:pointer;" src="images/crm/Shipment.png" alt="noch nicht versendet" title="noch nicht versendet" />';
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
			
			/*
			var href = '<?php echo PATH; ?>backend_crm_orders.php?jump_to=order&orderid='+$exchange_orderid+'&order_type=4';
			//html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" onclick="view_box('+$exchange_orderid+');" src="images/crm/forward.png" alt="Gehe zu Umtausch" title="Gehe zu Umtausch" /></span>';
			html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" onclick="javascript:window.open(\''+href+'\', \'_blank\');" src="images/crm/forward.png" alt="Gehe zu Umtausch" title="Gehe zu Umtausch" /></span>';
			*/
			html+='		<span><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" onclick="show_exchangeorder('+$exchange_orderid+');" src="images/crm/forward.png" alt="Zeige Umtausch" title="Zeige Umtausch" /></span>';
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
				if (orders[orderid]["fz_fin_mail_count"]>=2)
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

	function show_exchangeorder($exchange_orderid)
	{
		if ( $("#orderdata"+$exchange_orderid).is(":visible") )
		{
			$("#ordernoterow1"+$exchange_orderid).hide();
			$("#ordernoterow2"+$exchange_orderid).hide();
			$("#PayPal_BuyerNote"+$exchange_orderid).hide();
			$("#ordernoterow3"+$exchange_orderid).hide();
			$("#orderdata"+$exchange_orderid).hide();

		}
		else
		{
			show_combined_note($exchange_orderid);
			show_COD_note($exchange_orderid);
			show_PayPal_note($exchange_orderid);
			show_order_note($exchange_orderid);
			$("#orderdata"+$exchange_orderid).show();
		}
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
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_orders_list", mode:mode, order_id:orderid, FILTER_Platform:FILTER_Platform, FILTER_Status:FILTER_Status, FILTER_Country:FILTER_Country, FILTER_SearchFor:FILTER_SearchFor, FILTER_Searchfield:FILTER_Searchfield, FILTER_Ordertype:FILTER_Ordertype, OrderBy:OrderBy, OrderDirection:OrderDirection, ResultPage:ResultPage,ResultRange:ResultRange, FILTER_DateSearchFor:FILTER_DateSearchFor, date_from:date_from, date_to:date_to},
			function(data)
			{
				//if ( user_id == 28625 ) show_status2(data);
				//return;
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
					
					if (Results == 0)
					{
						$html = '';
						$html += '<div class="warning" style="text-align:center"><strong>Keine Suchergebnisse</strong></div>';
						$("#tableview").html($html);
						draw_navigation();
						return;
					}
									
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
						
						//if (FILTER_Ordertype == 4 || ( FILTER_Ordertype != 4 && orders[OrderID]["ordertype_id"]!=4) )
						if ( orders[OrderID]["ordertype_id"]!=4 || ( orders[OrderID]["ordertype_id"] == 4 && orders[OrderID]["original_result"] == 1 ) )
						{
							rowcounter++;
						}
						
						//rowcounter++;
						
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
						
						//if (FILTER_Ordertype == 4 || ( FILTER_Ordertype != 4 && orders[OrderID]["ordertype_id"]!=4) )
						if ( orders[OrderID]["ordertype_id"]!=4 || ( orders[OrderID]["ordertype_id"] == 4 && orders[OrderID]["original_result"] == 1 ) )
						{
							show_combined_note(OrderID);
						}
						
						//show_combined_note(OrderID);
						
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
						
						//if (FILTER_Ordertype == 4 || ( FILTER_Ordertype != 4 && orders[OrderID]["ordertype_id"]!=4) )
						if ( orders[OrderID]["ordertype_id"]!=4 || ( orders[OrderID]["ordertype_id"] == 4 && orders[OrderID]["original_result"] == 1 ) )
						{
							show_COD_note(OrderID);
						}
						
						//show_COD_note(OrderID);
						//ORDERDATA
						table='';
						
					//	if (FILTER_Ordertype == 4 || ( FILTER_Ordertype != 4 && orders[OrderID]["ordertype_id"]!=4) )
						if ( orders[OrderID]["ordertype_id"]!=4 || ( orders[OrderID]["ordertype_id"] == 4 && orders[OrderID]["original_result"] == 1 ) )

						{
							table+='<tr id="orderdata'+OrderID+'" style="background-color:'+rowcolor+'">';
						}
					
						else
						{
							table+='<tr id="orderdata'+OrderID+'" style="background-color:'+rowcolor+'; display:none;">';
						}
						
							//CHECKBOX
							table+='	<td><input name="orderID[]" type="checkbox" value="'+OrderID+'" onmousedown="select_all_from_here(this.value);" /></td>';
							
							table+='	<td>';
							table+='		<b>'+orders[OrderID]["entry_nr"]+'</b><br>';
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
						
						//if (FILTER_Ordertype == 4 || ( FILTER_Ordertype != 4 && orders[OrderID]["ordertype_id"]!=4) )
						if ( orders[OrderID]["ordertype_id"]!=4 || ( orders[OrderID]["ordertype_id"] == 4 && orders[OrderID]["original_result"] == 1 ) )
						{
							show_PayPal_note(OrderID);
						}
						
						//show_PayPal_note(OrderID);

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
						
						//if (FILTER_Ordertype == 4 || ( FILTER_Ordertype != 4 && orders[OrderID]["ordertype_id"]!=4) )
						if ( orders[OrderID]["ordertype_id"]!=4 || ( orders[OrderID]["ordertype_id"] == 4 && orders[OrderID]["original_result"] == 1 ) )
						{
							show_order_note(OrderID);
						}
						
						//show_order_note(OrderID);
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
	
	function draw_filter_bar1()
	{
		if ($("#filter_bar1").length == 0)
		{
			$("#filter_bar").append('<div id="filter_bar1" style="float:left;width:1200px; margin-top:12px">');
			//$("#filter_bar").append('<br style="clear:both" />');
		}

		var $html = '';
		
		$html += '	<span class="tabs">Plattform: ';
		$html += '		<select id="FILTER_Platform" name="select1" size="1" onChange="set_FILTER_Platform();">';
		if (UserShops.length > 1)
		{
			$html += '			<option value=0>Alle</option>';
		}
		for ( var $shop_id in UserShops)
		{
			$html += '	<option value='+$shop_id+'>'+UserShops[$shop_id]+'</option>';
		}
		$html += '		</select>';
		$html += '	</span>';
		$html += '	<span class="tabs">';
		$html += '		<select id="FILTER_Country" size="1" onChange="set_FILTER_Country();">';
		$html += '			<option value="">Alle Länder</option>';
		$html += '			<option value="national">DE + AT</option>';
		$html += '			<option value="ES+PT">ES + PT</option>';
		$html += '			<option value="IT">IT</option>';
		$html += '			<option value="international">International</option>';
		$html += '		</select>';
		$html += '	</span>';
		
		$html += '	<span class="tabs">Status: ';
		$html += '		<span id="span_FILTER_Status">';
		$html += '		</span>';
		$html += '	</span>';
		
		
		$html += '	<span class="tabs">Vorgangsart: ';
		$html += '		<select id="FILTER_Ordertype" size="1" onChange="set_FILTER_Ordertype();">';
		$html += '		<optgroup label="Bestellungen">';
		$html += '				<option value="0">Alle</option>';
		for (var $ordertype_id in OrderTypes)
		{
			if ( $ordertype_id != 6)
			{
				$html += '			<option value='+$ordertype_id+'>'+OrderTypes[$ordertype_id]["title"]+'</option>';
			}
		}
		$html += '			</optgroup>';
		$html += '		<optgroup label="Rückgaben/Umtausch">';
		$html += '			<option value="return_all">Alle (Rück&Umt)</option>';
		$html += '			<option value="return">Rückgabefälle</option>';
		$html += '			<option value="exchange">Umtauschfälle</option>';
		$html += '		</select>';
		$html += '	</span>';
		//$html += '<br style="clear:both" />';
	
		$("#filter_bar1").html($html);
		$("#filter_bar").append('<br style="clear:both" />');
			
	}
	
	function draw_filter_bar2()
	{
		if ($("#filter_bar2").length == 0)
		{
			$("#filter_bar").append('<div id="filter_bar2" style="float:left;width:1200px; margin-top:12px">');
			//$("#filter_bar").append('<br style="clear:both" />');
		}
	
		var $html = '';

		$html += '	<span class="tabs">Suche nach: ';
		$html += '		<select id="FILTER_SearchFor" size="1" style="width:200px" onChange="set_FILTER_SearchFor();">';
		$html += '			<option value="1">E-Mail</option>';
		$html += '			<option value="2">Ebay-Mitgliedsname</option>';
		$html += '			<option value="3">Name</option>';
		$html += '			<option value="4">Adresse</option>';
		$html += '			<option value="5">MAPCO Artikelnummer</option>';
		$html += '			<option value="6">Ebay-Artikelnummer</option>';
	//	$html += '			<option value="7">PayPal-Transaction-ID</option>';
		$html += '			<option value="8">* Order ID</option>';
		$html += '			<option value="10">* Rechnungsnummer</option>';
		$html += '			<option value="11">* Auftrags ID</option>';
		$html += '			<option value="9">* Versand Tracking-ID</option>';
		$html += '			<option value="12">* Amazon Order ID</option>';
		$html += '			<option value="13">Kunden Auftragsnummer</option>';
		$html += '		</select>';
		$html += '		<input type="text" size="40" id="FILTER_Searchfield" style="background-color:#ffdddd" />';
		$html += '	</span>';
		$html += '	<span class="tabs" id="filterbar3" style="width:350px; text-align:center">';
		$html += '	<span id="span_FILTER_DateSearchFor">';
		$html += '	</span>';
		$html += '		Datum von <input type="text" id="date_from" size="8" onchange="date_from=this.value;" />';
		$html += '		&nbsp;bis&nbsp;<input type="text" id="date_to" size="8" onchange="date_to=this.value;" />';
		$html += '		<a href="javascript:onClick=unset_date();" title="Datum zurücksetzen"><span style="background-color:#cccccc">X</span></a>';
		$html += '	</span>';
		$html += '	<span>';
		$html += '		<button id="SearchButton" onclick="view_box();">Suchen</button>';
		$html += '	</span>';
		$html += '<br style="clear:both" />';
		
		$("#filter_bar2").html($html);

		//SET DATEPICKER		
		$( "#date_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		$( "#date_to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
	}


	function draw_FILTER_Status($mode)
	{
		// return || order
		var $html = '';
		if ( $mode == "order" )
		{
			$html += '	<select id="FILTER_Status" size="1" style="width:200px" onChange="set_FILTER_Status();">';
			$html += '	<option value="0" selected>Alle</option>';
			$html += '		<optgroup label="Zahlungs-&Versandstatus">';
			$html += '			<option value="1">nicht bezahlt</option>';
			$html += '			<option value="2">bezahlt & Kein Auftrag geschrieben</option>';
			$html += '			<option value="3">Auftrag geschrieben & nicht versand</option>';
			$html += '			<option value="4">versand beauftragt & versand</option>';
			$html += '			<option value="5">Bestellung abgebrochen</option>';
			$html += '		</optgroup>';
			$html += '		<optgroup label="Fahrzeugdaten">';
			$html += '			<option value="10">Fz.-Anfragemail gesendet</option>';
			$html += '			<option value="11">Fz.-Anfragemail nicht gesendet</option>';
			$html += '		</optgroup>';
			$html += '		<optgroup label="Versandart">';
			$html += '			<option value="20">Express</option>';
			$html += '			<option value="21">DPD</option>';
			$html += '			<option value="22">DHL</option>';
			$html += '		</optgroup>';
			$html += '		<optgroup label="Bestellungscheck Status">';
			$html += '			<option value="30">Versendbare Bestellungen</option>';
			$html += '		</optgroup>';	
			$html += '	</select>';
			
			$("#span_FILTER_Status").html($html);
		}
		if ( $mode == "return" )
		{
			$html += '	<select id="FILTER_Status" size="1" style="width:200px" onChange="set_FILTER_Status();">';
			$html += '		<optgroup label="Bearbeitungsstatus">';
			$html += '			<option value="40" selected >Alle</option>';
			$html += '			<option value="41">offen</option>';
			$html += '			<option value="42">geschlossen</option>';
			$html += '		</optgroup>';
			$html += '	</select>';
			$("#span_FILTER_Status").html($html);
		}
	}

	function draw_FILTER_DateSearchFor($mode)
	{
		// $mode: return || order
		var $html = '';
		
		$html += '		<select id="FILTER_DateSearchFor" size=1 style="width:170px" onChange="set_FILTER_DateSearchFor();">';
		$html += '			<option value="firstmod">Bestelldatum</option>';
		$html += '			<option value="payment">Zahldatum</option>';
		$html += '			<option value="shipping">Versanddatum</option>';
		
		if ($mode == "return")
		{
			$html += '			<option value="return_case_opened">Fall geöffnet</option>';
			$html += '			<option value="article_returned">Rücksendung erhalten</option>';
			$html += '			<option value="return_case_closed">Fall geschlossen</option>';
		}
		$html += '		</select>';

		$("#span_FILTER_DateSearchFor").html($html);
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
/*
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
*/	
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


	//ALERT DIALOG
	echo '<div id="msg_box" style="display:none"></div>';
	echo '<div id="order_events" style="display:none"></div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>	