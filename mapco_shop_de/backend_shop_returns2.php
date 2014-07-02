<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_crm_index.php">CRM</a>';
	echo ' > Rückgaben';
	echo '</p>';
	
	echo '<p>';
	echo '<h1>Rückgaben & Umtausch</h1>';
	echo '</p>';
	
	
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
</style>

<script src="javascript/crm/OrderReturns.php" type="text/javascript" /></script>
<script type="text/javascript">

var preloadcount=0;

//FILTER
var FILTER_status = 0;
var FILTER_platform = 0;
var FILTER_return_type = "";
var date_from="";
var date_to="";

Shop_Shops = new Object;
ReturnsReasons = new Object;
Seller = new Array();

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
//----------------------------------------------------------------------------------------------------------------------

	//PRELOADS
	$(function()
	{
		
		wait_dialog_show();
		
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
		});
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
		});
			

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
		});
		
	});	

	function ready_function()
	{
		preloadcount++;
		if (preloadcount==3) 
		{
			draw_filters();
			view();
			wait_dialog_hide();
		}
	}

	function draw_filters()
	{
		var html = '';
		//STATUS
		html+='<span class="tabs" style="width:150px; text-align:center">';
		html+='<b>Status&nbsp;</b>';
		html+='<select id="FILTER_status" size=1 onchange="FILTER_status = this.value;">';
		html+='	<option value = 2>ALLE</option>';
		html+='	<option value = 0 selected="selected">offen</option>';
		html+='	<option value = 1>geschlossen</option>';
		html+='<select>';
		html+='</span>';

		//PLATFORM
		html+='<span class="tabs" style="width:400px; text-align:center">';
		html+='<b>Plattform&nbsp;</b>';
		html+='<select id="FILTER_platform" size=1 onchange="FILTER_platform = this.value;">';
		html+='	<option value=0>ALLE</option>';
		for (var shop_id in Shop_Shops)
		{
			html+='<option value='+shop_id+'>'+Shop_Shops[shop_id]["title"]+'</option>';
		}
		html+='</select>';
		html+='</span>';	
		
		//VORGANGSART
		html+='<span class="tabs" style="width:150px; text-align:center">';
		html+='<b>Vorgang&nbsp;</b>';
		html+='<select id="FILTER_return_type" size=1 onchange="FILTER_return_type = this.value;">';
		html+='	<option value = "" selected="selected">ALLE</option>';
		html+='	<option value = "exchange">Umtausch</option>';
		html+='	<option value = "return">Rückgabe</option>';
		html+='<select>';
		html+='</span>';

		//DATUMSEINSCHRÄNKUNG
		html+='	<span class="tabs" style="width:350px; text-align:center">';
		html+='		Datum von&nbsp;<input type="text" id="date_from" size="8" onchange="date_from=this.value;" />';
		html+='		&nbsp;bis&nbsp;<input type="text" id="date_to" size="8" onchange="date_to=this.value;" />';
		html+='	</span>';
		html+='	<span>';
		html+='		<button id="SearchButton" onclick="view();">Anzeigen</button>';
		html+='	</span>';


		$("#filter").html(html);
		
		//BIND DATEPICKERs
		$( "#date_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		$( "#date_to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		
		$("#filter").show();
			
	}


	function view()
	{
	
		//GET RETURNS
		$.post("<?php echo PATH; ?>soa2/", { API: "shop", APIRequest: "OrderReturnsGet", 
			FILTER_status:FILTER_status, 
			FILTER_platform:FILTER_platform, 
			FILTER_return_type:FILTER_return_type, 
			date_from:date_from, 
			date_to:date_to },
		function($data)
		{
//				show_status2($data);
			try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
			var $Ack = $xml.find("Ack").text();
			if ($Ack!="Success") {show_status2($data); return;}
			
			var html = '';
			html+='<table style="width:100%">';
			html+='	<tr>';
			html+='		<th style="width:50px">Status</th>';
			html+='		<th style="width:180px">Käufername</th>';
			html+='		<th style="width:150px">Käufer - Username</th>';
			html+='		<th style="width:120px">Plattform</th>';
			html+='		<th style="width:80px">Kaufdatum</th>';
			html+='		<th style="width:350px">Artikel</th>';
			html+='		<th style="width:120px">Grund</th>';
			html+='		<th style="width:80px">Vorgang</th>';
			html+='		<th style="width:80px">Fall geöffnet am</th>';
			html+='		<th style="width:30px"></th>';
			html+='		<th style="width:55px"></th>';
			html+='	</tr>';
			
			var rowcounter = 0;			
			$xml.find("return").each(
			function ()
			{
				rowcounter++;
				if (rowcounter%2 == 0) var rowcolor="#f0faff"; else var rowcolor="#fff";
				
				var return_id = $(this).find("id_return").text();
				
				html+='	<tr style="background-color:'+rowcolor+'">';
				html+='		<td>';
				if ($(this).find("state").text()==0)	
				{
					html+='offen';
				}
				else
				{
					html+='geschlossen';
				}
				html+='		</td>';
				html+='		<td>'+$(this).find("buyer_name").text()+'</td>';
				html+='		<td>'+$(this).find("buyer_username").text()+'</td>';
				html+='		<td>'+Shop_Shops[$(this).find("shop_id").text()]["title"]+'</td>';
				html+='		<td>'+convert_time_from_timestamp($(this).find("date_bought").text(), "complete")+'</td>';

				//ARTIKEL
				var items ='';
				$items = new Object;
				$items = $(this).find("items");
				$items.find("item").each(
				function ()
				{
					if (items != "") items+='<br />';
					items+=$(this).find("amount").text()+"x "+$(this).find("itemtitle").text();
				});
				html+='		<td>'+items+'</td>';

				//RETURNREASON
				var reasons = '';
				$items = new Object;
				$items = $(this).find("items");
				$items.find("item").each(
				function ()
				{
					
					if (reasons != "") reasons+='<br />';
					reasons+=ReturnsReasons[$(this).find("return_reason").text()]["title"];
				});
				html+='		<td>'+reasons+'</td>';

				//VORGANGSART
				if ($(this).find("return_type").text()=="return")
				{
					html+='	<td>Rückgabe</td>';
				}
				else
				{
					html+=' <td>Umtausch</td>';
				}
				html+='		</td>';
				//FALL GEÖFFNET
				html+='		<td>'+convert_time_from_timestamp($(this).find("firstmod").first().text(), "complete")+'</td>';
				
				//BEARBEITUNGSLINK
				html+='		<td><img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/edit.png" alt="Fall bearbeiten" title="Fall bearbeiten" onclick="order_returns_dialog('+return_id+')" /></td>';
				
				//WEITERLEITUNGEN
				html+='		<td>';
				html+='<a href="<?php echo PATH; ?>backend_crm_orders_test.php?jump_to=order&orderid='+$(this).find("order_id").text()+'&order_type=0"><img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:left;" src="images/icons/24x24/page_swap.png" alt="Gehe zur Bestellung" title="Gehe zur Bestellung" /></a>';
				if ($(this).find("return_type").text()=="exchange")
				{
					html+='<a href="<?php echo PATH; ?>backend_crm_orders_test.php?jump_to=order&orderid='+$(this).find("exchange_order_id").text()+'&order_type=4"><img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:left;" src="images/icons/24x24/next.png" alt="Gehe zu Umtausch" title="Gehe zu Umtausch" /></a>';
				}
				html+='		</td>';
				
				html+='	</tr>';
			});
			html+='</table>';
/*
			if ($("#returns_view").length == 0)
			{
alert("HALLO");
				$("body").append('<div id="returns_view" style="display:none">');
			}
*/
			$("#returns_view").html(html);

			$("#returns_view").show();
			
			
		});

	}

</script>

<?php
	echo '<div id="filter" style="display:none; float:left"></div>';
	echo '<br style="clear:both" />';
	echo '<div id="returns_view" style="display:none; float:left"></div>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>	