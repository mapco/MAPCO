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
	margin-top:10px;
	margin-bottom:0px;
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
	padding-bottom:0px;
	padding-top:6px;
	margin:5px;
}

</style>

<script src="javascript/cms/core.php" type="text/javascript" /></script>
<script type="text/javascript">

var preloadcount = 0;
var PaymentTypes = new Object();
var	Shop_Shops = new Object();
var	payments = new Object;

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
					initStatus: 'Wähle ein Datum', isRTL: false,
				   changeMonth: 		true,
				   changeYear: 			true,
				   showOtherMonths:		true,
				   selectOtherMonths:	true};

		$.datepicker.setDefaults($.datepicker.regional['de']);


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


	function update_payments_array(data)
	{
		payments_data = new Object;
		var $xml=$($.parseXML(data));
		//PAYMENTDATA
		var $paymentdata = $xml.find("paymentdata");
		payments_data["payment"] = new Object;
		payments_data["lastPaymentDeposit"] = new Object;
		$paymentdata.find("transaction").each(
			function()
			{
				var TransID = $(this).find("transactionID").text();
				var lastPaymentDeposit = $(this).find("lastpaymentdeposit").text();
				payments_data["payment"][TransID]=new Object;
				payments_data["lastPaymentDeposit"][TransID]=lastPaymentDeposit;
				$(this).find("accounting").each(
					function()
					{
						var $tagname=$(this).find("id_PN").text();
						payments_data["payment"][TransID][$tagname]=new Object;
						

						$(this).children().each(
							function()
							{
								var $tagname2=this.tagName;
								payments_data["payment"][TransID][$tagname][$tagname2]=$(this).text();
							});
						
					}
				);
			}
		);
		var $orderdata = $xml.find("orderdata");
		
		payments_data["lastOrderDeposit"] = $orderdata.find("lastOrderDeposit").text()*1;
		payments_data["lastOrderDepositEUR"] = $orderdata.find("lastOrderDepositEUR").text()*1;
		//payments_data["lastOrderDepositEUR"] = 20.23;
		payments_data["lastOrderTotal"] = $orderdata.find("lastOrderTotal").text();
		payments_data["lastOrderTotalEUR"] = $orderdata.find("lastOrderTotalEUR").text();
		payments_data["exchangerate"]=0;
		payments_data["order"] = new Object;
		$orderdata.find("accounting").each(
		function()
		{
			var $tagname=$(this).find("id_PN").text();
			payments_data["order"][$tagname]=new Object;
			

			$(this).children().each(
				function()
				{
					var $tagname2=this.tagName;
					payments_data["order"][$tagname][$tagname2]=$(this).text();
				});
			if (payments_data["exchangerate"]==0) payments_data["exchangerate"]=payments_data["order"][$tagname]["exchange_rate_from_EUR"]*1;
			}
		);		
		
//show_status2(print_r(payments_data));
	}


	$(function()
	{
		
		wait_dialog_show();
		
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
							Shop_Shops[shop_id] = new Object();
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
		
	});

	function ready_function()
	{
		preloadcount++;
		if (preloadcount==2) 
		{
			page_view();
			wait_dialog_hide();
		}
	}


	function page_view()
	{
		if ($("#main_menu").length == 0)
		{
			$("#main").append('<div id="main_menu" style="float:left">');
			$("#main").append('<br style="clear:both" />');
		}
		
		//DRAW ACTIONS
		var html='';
		html+= '<div id="navigation" style="width:1200px">';
		html+= '	<span class="tabs" style="width:120px; text-align:center"><a href = "javascript:show_zlg_sum_menu();">ZLG Summen</a></span>';
		html+= '	<span class="tabs" style="width:150px; text-align:center"><a href = "javascript:show_create_zlg_msgs();">IDIMS Buchungen vorbereiten</a></span>';
		html+= '	<span class="tabs" style="width:150px; text-align:center"><a href = "javascript:show_zlg_msgs_menu();">ZLG Messages</a></span>';
		html+= '	<span class="tabs" style="width:150px; text-align:center"><a href = "javascript:show_zlg_msgs_errors(0);">ZLG Messages Fehler <span id="error_counter"></span></a></span>';
		html+= '	<span class="tabs" style="width:150px; text-align:center"><a href = "javascript:show_transactions_menu();">Zahlungstransaktionen</a></span>';
		html+= '	<span class="tabs" style="width:150px; text-align:center"><a href = "javascript:show_paymentmessages_menu();">IPNs</a></span>';
		html+= '</div>';
		
		$("#main_menu").html(html);

		if ($("#sub_menu").length == 0)
		{
			$("#main").append('<div id="sub_menu" class="border_standard" style="float:left; width:1200px; display:none">');
			$("#main").append('<br style="clear:both" />');
		}
		
	
		if ($("#view").length == 0)
		{
			$("#main").append('<div id="view" style="float:left">');
		}
		
	}
	
	function show_paymentmessages_menu()
	{
		var html='';
		html+='<span class="tabs">';
		html+='	<select id="search_for_ipn_state" size="1">';
		html+='		<option value=0> unbearbeitet </option>';
		html+='		<option value=1> bearbeitet </option>';
		html+='		<option value=2> alle </option>';
		html+='	</select>';
		html+='</span>';
		
		html+='<span class="tabs">';
		html+='<select id="payment_type_ipn" size="1">';
		for (var Index in PaymentTypes)
		{
			html+='		<option value='+Index+'>'+PaymentTypes[Index]["title"]+'</option>';	
		}
		html+='	</select>';
		html+='</span>';

		html+='<span class="tabs">';
		html+='	<select id="search_for_ipn" size="1">';
		html+='		<option value=0> Suche nach.... </option>';
		html+='		<option value=1> IPN ID </option>';
		html+='		<option value=2> in Message </option>';
		html+='	</select>';
		html+='</span>';

		html+='<span class="tabs">';
		html+='	Suche nach <input type="text" id="search_for_ipn_text" size="15" />';
		html+='</span>';

		html+='<span class="tabs">';
		html+='<button id="btn3" onclick="show_paymentmessages();"> Anzeigen </button>';
		html+='</span>';

		$("#sub_menu").html(html);
		$("#sub_menu").show();
		$(".view").hide();
	}
	
	function show_paymentmessages()
	{
		wait_dialog_show();
		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataSelect';
		postfields['table']="payment_notification_messages";
		postfields['db'] = "dbshop";
		postfields['where'] =  "";
		if ($("#search_for_ipn_state").val()!=2)
		{
			postfields['where'] = "WHERE processed = "+$("#search_for_ipn_state").val();
		}
		if ($("#payment_type_ipn").val()!=0)
		{
			if (postfields['where']=="")
			{
				postfields['where'] = "WHERE payment_type_id = "+$("#payment_type_ipn").val();
			}
			else
			{
				postfields['where']+= " AND payment_type_id = "+$("#payment_type_ipn").val();
			}
		}
		if ($("#search_for_ipn").val()!=0)
		{
			if ($("#search_for_ipn").val()==1)
			{
				if (postfields['where']=="")
				{
					postfields['where'] = "WHERE id = "+$("#search_for_ipn_text").val();
				}
				else
				{
					postfields['where']+= " AND id = "+$("#search_for_ipn_text").val();
				}
			}
			if ($("#search_for_ipn").val()==2)
			{
				if (postfields['where']=="")
				{
					postfields['where'] = "WHERE message LIKE '%"+$("#search_for_ipn_text").val()+"%'";
				}
				else
				{
					postfields['where']+= " AND message LIKE '%"+$("#search_for_ipn_text").val()+"%'";
				}
			}
		}

				
			postfields['where']+=" ORDER BY date_received DESC LIMIT 200";
		
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			//show_status2($data);
			//return;
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			var html='';
			html+='<table>';
			html+='<tr>';
			html+='	<th>ID</th>';
			html+='	<th style="width:600px">Message</th>';
			html+='	<th>Date Received</th>';
			html+='	<th>Processed</th>';
			html+='	<th>Payment Type</th>';
			html+='	<th>IPN Track ID</th>';
			html+='	<th></th>';
			html+='</tr>';
			
			$xml.find("payment_notification_messages").each(
			function()
			{
				html+='<tr>';
				html+='	<td>'+$(this).find("id").text()+'</td>';
				//CHECK FOR CORRUPTED MESSAGE
				html+='	<td id="ipn_message_'+$(this).find("id").text()+'" style="cursor:pointer" ondblclick="show_ipn_message('+$(this).find("id").text()+');">';
				var check=check_ipn_message($(this).find("message").text());
				if (check!="") html+=' <span style="color:red">'+check+'</span><br />';
				html+=$(this).find("message").text().substr(0,70)+'</td>';
				html+='	<td>'+convert_time_from_timestamp($(this).find("date_received").text(), "complete")+'</td>';		
				html+='	<td>'+$(this).find("processed").text()+'</td>';
				html+='	<td>'+PaymentTypes[$(this).find("payment_type_id").text()]["title"]+'</td>';
				html+='	<td>'+$(this).find("ipn_track_id").text()+'</td>';
				html+='	<td>';
				if ($(this).find("processed").text()==0)
				{
					html+='	<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer" onclick="ipn_process_manual('+$(this).find("id").text()+', '+$(this).find("payment_type_id").text()+')" src="images/icons/16x16/process.png" alt="IPN manuel ausführen" title="IPN manuel ausführen" />';	
				}
				if (check_ipn_message_for_pending($(this).find("message").text()))
				{
					html+='	<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer" onclick="ipn_create_completed('+$(this).find("id").text()+')" src="images/icons/16x16/process.png" alt="Aus Pending Completed erstellen" title="Aus Pending Completed erstellen" />';	
				}
				html+='</td>';
				html+='</tr>';
			});
			
			html+='</table>';
			if ($("#show_paymentmessages").length == 0)
			{
				$("#main").append('<div class="view" id="show_paymentmessages" style="float:left">');
			}
			
			$("#show_paymentmessages").html(html);
			
		});
		
	}
	function ipn_create_completed($id)
	{
		var postfields = new Object();
		postfields['API'] = 		'payments';
		postfields['APIRequest'] =	'PaymentMessageCompleteCreate';
		postfields['id']=$id;
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			//show_status2($data);
			//return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			show_paymentmessages();
		});
	}
	
	function check_ipn_message(text)
	{
		//CHECK FOR MISSING EBAY_TXN_ID1
		if (text.search('for_auction=true')>-1)
		{
			if (text.search('ebay_txn_id1')==-1) 
			{
				return 'ebay_txn_id fehlt';
			}
			else return '';
		}
		else
		return '';
	}
	function check_ipn_message_for_pending(text)
	{
		if (text.search('payment_status=Pending')>-1)
		{
			return true;	
		}
		else
		{
			return false;
		}
		
	}
	
	function show_ipn_message(id)
	{
		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataSelect';
		postfields['table']="payment_notification_messages";
		postfields['db'] = "dbshop";
		postfields['where'] =  "WHERE id ="+id;
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			var msg = $xml.find("message").text();
			var msg_parts = msg.split("&");
			var html = '';
			for (var i =0; i<msg_parts.length; i++)
			{
				var text = msg_parts[i].split("=");
				html+='<b>'+text[0]+':</b> '+text[1]+'<br />';
			}
			
			$("#ipn_message_"+id).html(html);
			
		});
	}
	
	function ipn_process_manual(IPN_id, payment_type_id)
	{

		
		if (IPN_id==0 || IPN_id=="" || payment_type_id==0 || payment_type_id=="") return;
		// PAYPAL
		if (payment_type_id == 4 ) 
		{
			//CALL IPN HANDLER 
			var postfields = new Object();
			postfields['API'] = 		'payments';
			postfields['APIRequest'] =	'PaymentsNotificationSet_PayPal';
			postfields['id']=			IPN_id;
			$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				//UPDATE payment_notification_message
				var postfields = new Object();
				postfields['API'] = 		'cms';
				postfields['APIRequest'] =	'TableDataUpdate';
				postfields['table']=		'payment_notification_messages';
				postfields['db']=			'dbshop';
				postfields['where']=		'WHERE id = '+IPN_id;
				postfields['processed']=	1;
				
				$.post("<?php echo PATH; ?>soa2/", postfields, function($data1)
				{
					wait_dialog_hide();
					try { $xml = $($.parseXML($data1)); } catch ($err) { show_status($err.message); return; }
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" ) { show_status2($data1); return; }
					
					show_paymentmessages();
				});
				
				
			});
		}
		if (payment_type_id == 3 ) 
		{
			//CALL IPN HANDLER 
			var postfields = new Object();
			postfields['API'] = 		'payments';
			postfields['APIRequest'] =	'PaymentNotificationSet_COD';
			postfields['PNM_id']=			IPN_id;
			$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				//UPDATE payment_notification_message
				var postfields = new Object();
				postfields['API'] = 		'cms';
				postfields['APIRequest'] =	'TableDataUpdate';
				postfields['table']=		'payment_notification_messages';
				postfields['db']=			'dbshop';
				postfields['where']=		'WHERE id = '+IPN_id;
				postfields['processed']=	1;
				
				$.post("<?php echo PATH; ?>soa2/", postfields, function($data1)
				{
					wait_dialog_hide();
					try { $xml = $($.parseXML($data1)); } catch ($err) { show_status($err.message); return; }
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" ) { show_status2($data1); return; }
					
					show_paymentmessages();
				});
				
				
			});
		}
	}


	function show_zlg_msgs_errors_correction($params)
	{
		if (typeof($params["mode"])==="undefined") return;
		if (typeof($params["error_code"])==="undefined") $params["error_code"] = 0;

		wait_dialog_show();		
		//GET ORDERDATA
		var $postdata = new Object();
		$postdata['API'] =				'payments';
		$postdata['APIRequest'] =		'PaymentCorrections';
		$postdata['mode'] =				$params["mode"];

		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			show_status2($data);
			show_zlg_msgs_errors($params["error_code"]);
			wait_dialog_hide();
		});
	}
	
	function show_invoice($invoice_id)
	{
		// GET INVOICE FILE
		wait_dialog_show();
		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataSelect';
		postfields['table']="idims_auf_status";
		postfields['db'] = "dbshop";
		postfields['where'] = "WHERE rng_id = "+$invoice_id;
		
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			if ( $xml.find("num_rows").text() == 0 )
			{
				alert("KEINE RECHNUNG GEFUNDEN");
				return;	
			}

			$rng_file_id = $xml.find("rng_file_id").text()
			var $path = '<?php echo PATH; ?>files/'+$rng_file_id.substr(0,$rng_file_id.length -3)+'/'+$rng_file_id+'.pdf';
			window.open($path, '_blank');
			return;
			
		});
		
	}

	function show_zlg_msgs_errors(error_code)
	{
			$("#sub_menu").html('');
		
			//$(".view").hide();

		//SHOW SUBMENU FOR ERRORCODES
		if (error_code == 9825)
		{
			
			var $html = '';
			$html+='<span class="tabs">';
			$html+='<button id="btn4" onclick="show_zlg_msgs_errors_correction({ mode : \'9825_process_PayPal_IPNmessages\', error_code: 9825});"> PayPal IPNs (processed = 0) bearbeiten </button>';
			$html+='<button id="btn5" onclick="show_zlg_msgs_errors_correction({ mode : \'9825_check_accountable_payments\', error_code: 9825});">Zahlungen auf Order verknüpfen und buchen </button>';
			$html+='<button id="btn6" onclick="show_zlg_msgs_errors_correction({ mode : \'9825_check_error_code\', error_code: 9825});"> Fehlercode bereinigen </button>';
			$html+='</span>';$html+='</span>';
	
			$("#sub_menu").html($html);
			$("#sub_menu").show();
			$(".view").hide();
		}

		if (error_code == 9823)
		{
			var $html = '';
			$html+='<span class="tabs">';
			$html+='<button id="btn4" onclick="show_zlg_msgs_errors_correction({ mode : \'9823_check_accountable_payments\', error_code: 9823});"> Zahlungsrestbeträge auf Order buchen </button>';
			$html+='<button id="btn5" onclick="show_zlg_msgs_errors_correction({ mode : \'9823_check_ordertotal\', error_code: 9823});"> Prüfe OrderTotal - Adjustment </button>';
			$html+='<button id="btn6" onclick="show_zlg_msgs_errors_correction({ mode : \'9823_check_error_code\', error_code: 9823});"> Fehlercode bereinigen </button>';
			$html+='</span>';$html+='</span>';
	
			$("#sub_menu").html($html);
			$("#sub_menu").show();
			$(".view").hide();
		}

		wait_dialog_show();
		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataSelect';
		postfields['table']="idims_zlg_error";
		postfields['join']=", shop_orders";
		postfields['db'] = "dbshop";
		postfields['select'] = "shop_orders.shop_id, shop_orders.invoice_date, idims_zlg_error.*";
		postfields['where'] = "WHERE shop_orders.id_order = idims_zlg_error.order_id"
		
		if (error_code!=0)
		{
			postfields['where']+= " AND idims_zlg_error.error_code = "+error_code;
		}
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			wait_dialog_hide();
			//show_status2($data);
			//return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			$("#error_counter").text(" ("+$xml.find("num_rows").text()+")");
			
			
			var html='';
			html+='<table class="hover" id="error_code_table">';
			html+='<thead>';
			html+='<tr>';
			html+='	<th>ID</th>';
			html+='	<th>Invoice ID</th>';
			html+='	<th>Invoice Date</th>';
			html+='	<th>Order ID</th>';
			html+='	<th>Error</th>';
			html+='	<th>Shop ID</th>';
			html+='	<th style="width:600px">message</th>';
			html+='	<th>Date</th>';
			html+='	<th>Notiz</th>';
			html+='	<th></th>';
			html+='</tr>';
			html+='</thead>';
			html+='<tbody>';
			
			$xml.find("idims_zlg_error").each(
			function()
			{
				html+='<tr>';
				html+='	<td>'+$(this).find("id_error").text()+'</td>';
				html+='	<td style="cursor:pointer" ondblclick="javascript:show_invoice('+$(this).find("invoice_id").text()+')";>'+$(this).find("invoice_id").text()+'</td>';
				html+='	<td>'+convert_time_from_timestamp($(this).find("invoice_date").text(), "complete")+'</td>';	
				var href = '<?php echo PATH; ?>backend_crm_orders.php?lang=de&jump_to=order&orderid='+$(this).find("order_id").text()+'&order_type=1';
				html+='	<td style="cursor:pointer" ondblclick="javascript:window.open(\''+href+'\', \'_blank\');"	>'+$(this).find("order_id").text()+'</td>';
				html+='	<td style="cursor:pointer" ondblclick="show_zlg_msgs_errors('+$(this).find("error_code").text()+');">'+$(this).find("error_code").text()+'</td>';
				html+='	<td>'+$(this).find("shop_id").text()+'</td>';
				html+='	<td>'+$(this).find("message").text()+'</td>';
				html+='	<td>'+convert_time_from_timestamp($(this).find("firstmod").text(), "complete")+'</td>';		
				html+='	<td id="error_note_'+$(this).find("id_error").text()+'" style="cursor:pointer" ondblclick="zlg_msgs_errors_note_set('+$(this).find("id_error").text()+', '+error_code+');">'+$(this).find("notes").text()+'</td>';
				html+='	<td>';
					html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer" onclick="show_delete_error('+$(this).find("id_error").text()+', '+error_code+')" src="images/icons/16x16/remove.png" alt="Fehler löschen" title="Fehler löschen" />';
					if ($(this).find("error_code").text() == 9823 || $(this).find("error_code").text() == 9842 || $(this).find("error_code").text() == 9825 || $(this).find("error_code").text() == 9839)
					{
						html+='		<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer" onclick="show_create_zlgmessage('+$(this).find("id_error").text()+', '+error_code+')" src="images/icons/16x16/calculator.png" alt="ZLGMessage erzeugen" title="ZLGMessage erzeugen" />';						
					}
				html+='	</td>';	
				html+='</tr>';
			});
			html+='<tbody>';
			html+='</table>';
			if ($("#view_zlg_msgs_errors").length == 0)
			{
				$("#main").append('<div class="view" id="view_zlg_msgs_errors" style="float:left">');
			}
			
			$("#view_zlg_msgs_errors").html(html).show();
			
			$(function(){
				$("#error_code_table").tablesorter({sortList: [[0,0]], locale: 'de'});
			});

			
		});
		
	}
	
	function show_create_zlgmessage($id_error, $error_code)
	{
		//GET RNG_BRUTTO
		wait_dialog_show();
		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataSelect';
		postfields['table']=		"idims_auf_status";
		postfields['join']=			", idims_zlg_error";
		postfields['select']=		"idims_auf_status.rng_brutto";
		postfields['where']=		"WHERE idims_auf_status.rng_id = idims_zlg_error.invoice_id AND idims_zlg_error.id_error = "+$id_error;
		postfields['db'] = 			"dbshop";
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			//show_status2($data);
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			if ($("#view_zlg_msgs_errors_create_zlgmessage").length == 0)
			{
				$("#main").append('<div class="dialog" id="view_zlg_msgs_errors_create_zlgmessage" style="float:left">');
			}
			$("#view_zlg_msgs_errors_create_zlgmessage").html('');
			$("#view_zlg_msgs_errors_create_zlgmessage").append('<p>zu übermittelnder Rechnungsbetrag EUR <input type="text" id="view_zlg_msgs_errors_create_zlgmessage_betrag" size="7" value="'+$xml.find("rng_brutto").text()+'" /></p>');
			$("#view_zlg_msgs_errors_create_zlgmessage").append('<p>Fehler im Anschluss löschen <input type="checkbox" id="view_zlg_msgs_errors_create_zlgmessage_delete_error" value="1" checked /></p>');
//			$("#view_zlg_msgs_errors_create_zlgmessage").append('<p>Zahlungsbuchung abgelaufen <input type="checkbox" id="view_zlg_msgs_errors_create_zlgmessage_expired" value="1" /></p>');
			$("#view_zlg_msgs_errors_create_zlgmessage").dialog
			({	buttons:
				[
					{ text: "ZLG Message generieren", click: function() { create_zlgmessage($id_error, $error_code);} },
					{ text: "Beenden", click: function() { $(this).dialog("close");} }
				],
				closeOnEscape: true,
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"IDIMS Buchungssatz manuell erstellen",
				width:400
			});		

		});
	}
	
	function create_zlgmessage($id_error, $error_code)
	{
		var postfields = new Object();
		postfields['API'] 			= 'payments';
		postfields['APIRequest'] 	= 'ZLGPaymentMessageCreateManual';
		postfields['error_id'] 		= $id_error;
		postfields['betrag'] 		= $("#view_zlg_msgs_errors_create_zlgmessage_betrag").val();
//		postfields['expired'] 		= $("#view_zlg_msgs_errors_create_zlgmessage_expired").val();
		
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			//show_status2($data);
			//return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
		
			if ($("#view_zlg_msgs_errors_create_zlgmessage_delete_error").val() == 1)	
			{
				delete_error($id_error, $error_code);
			}
			
			$("#view_zlg_msgs_errors_create_zlgmessage").dialog("close");
		});

	}
	
	function zlg_msgs_errors_note_set(error_id, error_code)
	{
		//GET NOTE FROM TABLE
		$("#error_note_"+error_id).unbind('dblclick');
		var note = $("#error_note_"+error_id).html();
		var html = '<input type="text" size="40" id="error_note_field_'+error_id+'" value = "'+note+'" onchange="zlg_msgs_errors_note_set2('+error_id+','+error_code+');" />';
		$("#error_note_"+error_id).html(html);
		
	}

	function zlg_msgs_errors_note_set2(error_id, error_code)
	{
		wait_dialog_show();
		var postfields = new Object();
		postfields['API'] = 		'cms';
		postfields['APIRequest'] =	'TableDataUpdate';
		postfields['table']="idims_zlg_error";
		postfields['db'] = "dbshop";
		postfields['where'] = " WHERE id_error = "+error_id;
		postfields['notes'] = $("#error_note_field_"+error_id).val();
		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			wait_dialog_hide();
			//show_status2($data);
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			show_zlg_msgs_errors(error_code);
		});
	}

	function show_delete_error(error_id, error_code)
	{
		if ($("#view_zlg_msgs_errors_delete_error").length == 0)
		{
			$("#main").append('<div class="dialog" id="view_zlg_msgs_errors_delete_error" style="float:left">');
		}
		$("#view_zlg_msgs_errors_delete_error").dialog
		({	buttons:
			[
				{ text: "Fehler löschen", click: function() { delete_error( error_id, error_code);} },
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeOnEscape: true,
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Fehler löschen",
			width:400
		});		
	}
	
	function delete_error(error_id, error_code)
	{
		wait_dialog_show();
		var postfields = new Object();
		postfields['API'] = 		'payments';
		postfields['APIRequest'] =	'ZLGErrorDelete';
		postfields['error_id']=		error_id;

		$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
		{
			wait_dialog_hide();
			//show_status2($data);
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			show_status("Fehler gelöscht");
			$("#view_zlg_msgs_errors_delete_error").dialog("close");
			show_zlg_msgs_errors(error_code);
			
		});
	}
	
	function show_transactions_menu()
	{
		var html='';
		html+='<span class="tabs">';
		html+='	<select id="search_for" size="1">';
		html+='		<option value=1> TransactionsID </option>';
		html+='		<option value=2 selected> Order ID </option>';
		html+='		<option value=3> User ID </option>';
		html+='	</select>';
		html+='</span>';
		
		html+='<span class="tabs">';
		html+='	<input type="text" id="search_for_value" size="20" />';
		html+='</span>';

		html+='<span class="tabs">';
		html+='<button id="btn3" onclick="show_transactions_get();"> Anzeigen </button>';
		html+='</span>';

		$("#sub_menu").html(html);
		$("#sub_menu").show();
		$(".view").hide();


	}
	
	function show_transactions_get()
	{
		//SET DIV FOR VIEW
		if ($("#view_show_transactions").length == 0)
		{
			$("#main").append('<div class="view" id="view_show_transactions" style="float:left">');
		}
		//ANZEIGE LEEREN
		$("#view_show_transactions").html('');
		
		if ($("#search_for").val()==1) // TransactionID
		{

			wait_dialog_show();
			var postfields = new Object();
			postfields['API'] = 		'payments';
			postfields['APIRequest'] =	'PaymentGet';
			postfields['TransactionID']=$("#search_for_value").val().trim();
			$.post("<?php echo PATH; ?>soa2/", postfields, function($data1)
			{
				//show_status2($data1);
				wait_dialog_hide();
				try { $xml1 = $($.parseXML($data1)); } catch ($err) { show_status($err.message); return; }
				$ack = $xml1.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data1); return; }

				$orders = $xml1.find("orders");
				$xml1.find("order_id").each(
				function()
				{
					var postfields = new Object();
					postfields['API'] = 		'payments';
					postfields['APIRequest'] =	'OrderPaymentsGet';
					postfields['orderid']=$(this).text();
					$.post("<?php echo PATH; ?>soa2/", postfields, function($data2)
					{
						wait_dialog_hide();
						try { $xml2 = $($.parseXML($data2)); } catch ($err) { show_status($err.message); return; }
						$ack = $xml2.find("Ack");
						if ( $ack.text()!="Success" ) { show_status2($data2); return; }
						show_transactions($(this).text(),$data2);
					});
				});
				return;				
			});
		}
		if ($("#search_for").val()==2) // OrderID
		{
			wait_dialog_show();
			var postfields = new Object();
			postfields['API'] = 		'payments';
			postfields['APIRequest'] =	'OrderPaymentsGet';
			postfields['orderid']=$("#search_for_value").val().trim();
			$.post("<?php echo PATH; ?>soa2/", postfields, function($data)
			{
				//show_status2($data);
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				show_transactions($("#search_for_value").val(),$data);
				wait_dialog_hide();
				return;				
			});
		}

		if ($("#search_for").val()==3)	// USER_ID
		{		
			wait_dialog_show();
			var postfields = new Object();
			postfields['API'] = 		'shop';
			postfields['APIRequest'] =	'UserOrderIDsGet';
			postfields['user_id']=$("#search_for_value").val().trim();
			$.post("<?php echo PATH; ?>soa2/", postfields, function($data1)
			{
				wait_dialog_hide();
				try { $xml1 = $($.parseXML($data1)); } catch ($err) { show_status($err.message); return; }
				$ack = $xml1.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data1); return; }

				$xml1.find("order_id").each(
				function()
				{
					var postfields = new Object();
					postfields['API'] = 		'payments';
					postfields['APIRequest'] =	'OrderPaymentsGet';
					postfields['orderid']=$(this).text();
					$.post("<?php echo PATH; ?>soa2/", postfields, function($data2)
					{
						try { $xml2 = $($.parseXML($data2)); } catch ($err) { show_status($err.message); return; }
						$ack = $xml2.find("Ack");
						if ( $ack.text()!="Success" ) { show_status2($data2); return; }
						show_transactions($(this).text(),$data2);
					});
				});
				wait_dialog_hide();
				return;				
			});
		}
		
	}
	
	function show_transactions(orderid, $data)
	{
		update_payments_array($data)		
		
		var html='';
		//ORDERDATA
		html+='<div>';
		html+='<table style="width:800px" id="ordertable_general">';
		//html+='<tr class="order_general" style="display:none">';
		html+='<tr>';
		html+='	<th style="width:150px">Order / TxN ID</th>';
		html+='	<th style="width:120px">Datum</th>';
		html+='	<th style="width:90px">Total</th>';
		html+='	<th style="width:90px">Deposit</th>';
		html+='	<th style="width:270px"><button id="btn4" onclick="payments_detail_show()"> Details </button></th>';
		html+='</tr><tr>';
		html+='<td>'+orderid+'</td>';
		html+='<td><b>Bestellung</b></td>';
		html+='<td style="text-align:right">'+payments_data["lastOrderTotalEUR"]+' <b>EUR</b></td>';
		html+='<td style="text-align:right">'+payments_data["lastOrderDepositEUR"]+' <b>EUR</b></td>';
		html+='<td></td>';
		html+='</tr>';
		for (var TxnID  in payments_data["payment"])
		{
			for (var id_PN in payments_data["payment"][TxnID])
			{
				if (payments_data["payment"][TxnID][id_PN]["notification_type"]==4 || payments_data["payment"][TxnID][id_PN]["notification_type"]==5) var paymentdetail = true; else var paymentdetail = false;
				
				//define refundable rows
				
				if (!paymentdetail)	
				{
					html+='<tr class="payment_dialog_payment_row" id="payment_dialog_payment_row_'+TxnID+'" style="background-color:#fff">';
					html+='	<td>'+TxnID+'</td>';
					html+='	<td>'+convert_time_from_timestamp(payments_data["payment"][TxnID][id_PN]["accounting_date"], "complete")+'</td>';
	
					var $exchangerate = payments_data["payment"][TxnID][id_PN]["exchange_rate_from_EUR"];
					var $totalEUR=(payments_data["payment"][TxnID][id_PN]["total"]*1)/$exchangerate;
					html+='	<td style="text-align:right">'+$totalEUR.toFixed(2).toString().replace(".", ",")+' EUR</td>';
					
					var $paymentdepositEUR = (payments_data["lastPaymentDeposit"][TxnID]*1)/ $exchangerate;
					html+='	<td style="text-align:right">'+$paymentdepositEUR.toFixed(2).toString().replace(".", ",")+' EUR</td>';
	
					html+='	<td>'+PaymentTypes[payments_data["payment"][TxnID][id_PN]["payment_type_id"]]["title"]+' '+translate_payment_reason(payments_data["payment"][TxnID][id_PN]["reason"], payments_data["payment"][TxnID][id_PN]["notification_type"])+'</td>';
	
					html+='</tr>';
				}
			}
		}
		html+='</table>';

		//ORDERDETAILS
		html+='<table style="width:1200px; display:none" id="ordertable_detail">';
		html+='<thead id="ordertable_detail_head">';
		//html+='<tr class="order_detail" style="display:none">';
		html+='<tr>';
		html+='	<th style="text-align:center">PN_ID</th>';
		html+='	<th style="text-align:center">f_ID</th>';
		html+='	<th style="text-align:center">OrderID</th>';
		html+='	<th style="text-align:center">TxnID</th>';
		html+='	<th style="text-align:center">Datum</th>';
		html+='	<th style="text-align:center">Total</th>';
		html+='	<th style="text-align:center">Deposit</th>';
		html+='	<th style="text-align:center">User Deposit</th>';
		html+='	<th style="text-align:center">User ID</th>';
		html+='	<th style="text-align:center">Buchung</th>';
		html+='	<th>OrderEvent<button id="btn5" onclick="payments_detail_show();"> Details </button></th>';
		html+='</tr>';
		html+='</thead>';
		html+='<tbody id="ordertable_detail_body">';
		for (var id_PN in payments_data["order"])
		{
			if (payments_data["order"][id_PN]["reason"]=="OrderAdd") var $orderVorgang = "Bestellung angelegt";
			if (payments_data["order"][id_PN]["reason"]=="OrderAdjustment") var $orderVorgang = "Bestellung angepasst";
			html+='<tr class="order_detail">';
			html+='	<td>'+id_PN+'</td>';
			html+='	<td>'+payments_data["order"][id_PN]["f_id"]+'</td>';
			html+='	<td>'+payments_data["order"][id_PN]["order_id"]+'</td>';
			html+='	<td>'+payments_data["order"][id_PN]["paymentTransactionID"]+'</td>';
			html+='	<td>'+convert_time_from_timestamp(payments_data["order"][id_PN]["accounting_date"], "complete")+'</td>';
			html+='	<td style="text-align:right">'+payments_data["order"][id_PN]["total"]+' '+payments_data["order"][id_PN]["currency"]+'</td>';
			//ORDER DEPOSIT
			html+='	<td style="text-align:right">'+payments_data["order"][id_PN]["deposit_EUR"]+' <b>EUR</b></td>';
			//USER DEPOSIT
			html+='	<td style="text-align:right">'+payments_data["order"][id_PN]["user_deposit_EUR"]+' <b>EUR</b></td>';
			//USER ID
			html+='	<td style="text-align:right">'+payments_data["order"][id_PN]["user_id"]+'</td>';
			//ACCOUNTING
			html+='	<td style="text-align:right">'+payments_data["order"][id_PN]["accounting_EUR"]+' <b>EUR</b></td>';
			//ACCOUNTING TYPE
			html+='	<td>'+$orderVorgang+'</td>';

			html+='</tr>';
		}
		
		for (var TxnID  in payments_data["payment"])
		{
			for (var id_PN in payments_data["payment"][TxnID])
			{
				if (payments_data["payment"][TxnID][id_PN]["notification_type"]==4 || payments_data["payment"][TxnID][id_PN]["notification_type"]==5) var paymentdetail = true; else var paymentdetail = false;
				
				//define refundable rows
				
				if (!paymentdetail)	
				{
					html+='<tr class="payment_dialog_payment_row" id="payment_dialog_payment_row_'+TxnID+'" style="background-color:#fff" onmouseover="highlight_txn_row(\''+TxnID+'\');" onmouseout="unhighlight_txn_row(\''+TxnID+'\');">';
				}
				else
				{
					html+='<tr class="payment_dialog_payment_detail_row_'+TxnID+'" id="payment_dialog_payment_detail_row'+id_PN+'" style="background-color:#eee;">';
				}
				//ID_PN
				html+='	<td id="id_PN_'+id_PN+'" onmouseover="highlight_detail_child_correlation(\''+id_PN+'\');" onmouseout="unhighlight_detail_child_correlation(\''+id_PN+'\');">'+id_PN+'</td>';
				//F_ID
				if (!paymentdetail)	
				{
					html+='	<td id="f_id_'+payments_data["payment"][TxnID][id_PN]["f_id"]+'">'+payments_data["payment"][TxnID][id_PN]["f_id"]+'</td>';
				}
				else
				{
					html+='	<td id="f_id_'+payments_data["payment"][TxnID][id_PN]["f_id"]+'" onmouseover="highlight_detail_mother_correlation(\''+payments_data["payment"][TxnID][id_PN]["f_id"]+'\');" onmouseout="unhighlight_detail_mother_correlation(\''+payments_data["payment"][TxnID][id_PN]["f_id"]+'\');">'+payments_data["payment"][TxnID][id_PN]["f_id"]+'</td>';
				}
				//ORDER_ID
				html+='	<td>'+payments_data["payment"][TxnID][id_PN]["order_id"]+'</td>';
				//TxnID
				html+='	<td>'+TxnID+'</td>';
				//BUCHUNGSZEIT
				html+='	<td>'+convert_time_from_timestamp(payments_data["payment"][TxnID][id_PN]["accounting_date"], "complete")+'</td>';
				//PAYMENTTOTAL
				var $exchangerate = payments_data["payment"][TxnID][id_PN]["exchange_rate_from_EUR"];
				var $totalEUR=(payments_data["payment"][TxnID][id_PN]["total"]*1)/$exchangerate;
				html+='	<td style="text-align:right">'+$totalEUR.toFixed(2).toString().replace(".", ",")+' EUR</td>';
				//PAYMENTDEPOSIT
				var $paymentdepositEUR = payments_data["payment"][TxnID][id_PN]["deposit_EUR"]*1;
				html+='	<td style="text-align:right">'+$paymentdepositEUR.toFixed(2).toString().replace(".", ",")+' EUR</td>';
				//USERDEPOSIT
				html+='	<td style="text-align:right">'+payments_data["payment"][TxnID][id_PN]["user_deposit_EUR"]+' EUR</td>';
				//USERID
				html+='	<td style="text-align:right">'+payments_data["payment"][TxnID][id_PN]["user_id"]+'</td>';
				// ACCOUNTING
				html+='	<td style="text-align:right">'+payments_data["payment"][TxnID][id_PN]["accounting_EUR"]+' EUR</td>';
				//ACCOUNTING TYPE
				html+='	<td>'+PaymentTypes[payments_data["payment"][TxnID][id_PN]["payment_type_id"]]["title"]+' '+translate_payment_reason(payments_data["payment"][TxnID][id_PN]["reason"], payments_data["payment"][TxnID][id_PN]["notification_type"])+'</td>';

				html+='</tr>';
				
			}
		}
		html+='</tbody>';
		html+='</table>';
		html+='</div>';
		
		$("#view_show_transactions").append(html);
		
		$("#view_show_transactions").show();
		//ADD HOVEREVENTS
		/*
		for (var TxnID  in payments_data["payment"])
		{
			$("#payment_dialog_payment_row_"+TxnID).hover(
				function()
				{
					alert("HALLO");
					$("#payment_dialog_payment_row_"+TxnID).css("background-color", "#EDBFC0");
					$(".payment_dialog_payment_detail_row"+TxnID).css("background-color", "#EDBFC0");
				},
				function()
				{
					$("#payment_dialog_payment_row_"+TxnID).css("background-color", "#FFFFFF");
					$(".payment_dialog_payment_detail_row"+TxnID).css("background-color", "#eee");
				}
			);
		}
		*/
		
		$("#ordertable_detail").tablesorter({sortList: [[0,1]]});
		
	}
	
	function highlight_txn_row(TxnID)
	{
		$("#payment_dialog_payment_row_"+TxnID).css("background-color", "#EDBFC0");
		$(".payment_dialog_payment_detail_row_"+TxnID).css("background-color", "#F4D9DA");
	}
	function unhighlight_txn_row(TxnID)
	{
		$("#payment_dialog_payment_row_"+TxnID).css("background-color", "#FFFFFF");
		$(".payment_dialog_payment_detail_row_"+TxnID).css("background-color", "#eee");
	}

	var tmp_color='';	
	function highlight_detail_mother_correlation(f_id)
	{
		//STORE orig BGColor
		tmp_color = $("#id_PN_"+f_id).css("background-color");
		$("#id_PN_"+f_id).css("background-color", "#EDBFC0");
	}
	function unhighlight_detail_mother_correlation(f_id)
	{
		$("#id_PN_"+f_id).css("background-color", tmp_color);
	}
	
	function highlight_detail_child_correlation(id_PN)
	{
		//STORE orig BGColor
		tmp_color = $("#f_id_"+id_PN).css("background-color");
		$("#f_id_"+id_PN).css("background-color", "#EDBFC0");
	}
	function unhighlight_detail_child_correlation(id_PN)
	{
		$("#f_id_"+id_PN).css("background-color", tmp_color);
	}

	function translate_payment_reason(reason, notification_type)
	{
		if (reason == "Completed") return "Zahlungseingang";
		else if (reason == "Payment") {
			if (notification_type == 4) return "Depositabzug von Zahlung";
			if (notification_type == 5) return "Depositerhöhung von Order";
			}
		else if (reason == "Linking Payment") return "Userdepositumbuchung";
		else if (reason == "Refunded") return "Rückzahlung";	
		else return reason;
		
	}

	
	function payments_detail_show()
	{
		if ($("#ordertable_detail").is(":visible"))
		{
			$("#ordertable_detail").hide();
			$("#ordertable_general").show();
			
		}
		else
		{
			$("#ordertable_detail").show();
			$("#ordertable_general").hide();
		}
		
	}

	
	
	function show_create_zlg_msgs()
	{
		var html = '';
		/*
		html+='<span class="tabs">Rechnungserstellungszeitraum ';
		html+='von <input type="text" id="from" size="10" />';
		html+='bis <input type="text" id="to" size="10" />';
		html+='</span>';
		*/
		html+='<span class="tabs">';
		html+='<button id="btn2" onclick="create_zlg_msgs();"> Buchungssätze Erstellen </button>';
		html+='</span>';

		$("#sub_menu").html(html);
		
		// ADD Datepicker
		//$( "#from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		// ADD Datepicker
		//$( "#to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		
		$("#sub_menu").show();
		$(".view").hide();

	}


	function create_zlg_msgs()
	{
		wait_dialog_show();
		
		var post_object = 					new Object();
		post_object['API'] = 				'payments';
		post_object['APIRequest'] =			'ZLGPaymentMessageCreate';
	/*
		if ($("#to").val()!="")
		{
			post_object['from'] = 			$("#from").val();
			post_object['to'] =				$("#to").val();
		}
	*/
		$.post("<?php echo PATH; ?>soa2/", post_object, function($data)
		{
			wait_dialog_hide();
//show_status2($data);
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			var html ='';
			html+='<textarea cols="80" rows="30" id="output_textarea">';
			html+=$data;
			html+='</textarea>';

			if ($("#view_create_zlg_msgs").length == 0)
			{
				$("#main").append('<div class="view" id="view_create_zlg_msgs" style="float:left">');
			}
		
			$("#view_create_zlg_msgs").html(html);
			$("#view_create_zlg_msgs").show();
		});
	}

	
	function show_zlg_sum_menu()
	{
		var html = '';
		html+='<div style="height:35px";>';
		html+='<span class="tabs">';
		html+='<select id="payment_type" size="1">';
		for (var Index in PaymentTypes)
		{
			html+='		<option value='+Index+'>'+PaymentTypes[Index]["title"]+'</option>';	
		}
		html+='	</select>';
		html+='</span>';
		html+='<span class="tabs">';
		html+='<select id="shop" size="1">';
		html+='	<option value=0>Alle Shops</option>';
		for (var Index in Shop_Shops)
		{
			if (Index in ({1:'',2:'',3:'',4:'',5:'',6:'',7:''}))
			{
				html+='		<option value='+Index+'>'+Shop_Shops[Index]["title"]+'</option>';	
			}
		}
		html+='	</select>';
		html+='</span>';

		html+='<span class="tabs">';
		html+='<select id="not_transfered" size="1">';
		html+='	<option value=0>bereits überwiesen</option>';
		html+='	<option value=1>noch nicht überwiesen</option>';
		html+='	</select>';
		html+='</span>';
		
		html+='<span class="tabs">Überweisungszeitraum ';
		html+='von <input type="text" id="transfered_from" size="10" />';
		html+='bis <input type="text" id="transfered_to" size="10" />';
		html+='</span>';
		
		html+='</div>';
		
		html+='<div style="height:35px";>';
		
		html+='<span class="tabs">IDIMS Buchung ';
		html+='von <input type="text" id="response_from" size="10" />';
		html+='bis <input type="text" id="response_to" size="10" />';
		html+='</span>';
		
		html+='<span class="tabs">Buchung angelegt ';
		html+='von <input type="text" id="created_from" size="10" />';
		html+='bis <input type="text" id="created_to" size="10" />';
		html+='</span>';
		
		html+='<span class="tabs">Buchungsdatum ';
		html+='von <input type="text" id="invoice_from" size="10" />';
		html+='bis <input type="text" id="invoice_to" size="10" />';
		html+='</span>';


		html+='<span class="tabs">';
		html+='<button id="btn1" onclick="show_zlg_sum(0);"> Anzeigen </button>';
		html+='</span>';

		html+='<span class="tabs">';
		html+='<button id="btn2" onclick="show_zlg_sum_transfer();"> Anzeigen & Überwiesen </button>';
		html+='</span>';
		html+='</div>';



		$("#sub_menu").html(html);
		
		// ADD Datepicker
		$( "#transfered_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		// ADD Datepicker
		$( "#transfered_to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		// ADD Datepicker
		$( "#response_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		// ADD Datepicker
		$( "#response_to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		// ADD Datepicker
		$( "#created_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		// ADD Datepicker
		$( "#created_to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		// ADD Datepicker
		$( "#invoice_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		// ADD Datepicker
		$( "#invoice_to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });

		$("#sub_menu").show();
		$(".view").hide();
	}

	function show_zlg_sum_transfer()
	{
		//SET DIV FOR VIEW
		if ($("#view_zlg_sum_transfer").length == 0)
		{
			$("#main").append('<div class="view" id="view_zlg_sum_transfer" style="display:none">');
		}
		
		var html = '';
		html+='<input type="text" id="transfer_exhangerate" size="10" />  Umtauschkurs von EUR zu Währung';
		
		$("#view_zlg_sum_transfer").html(html);

		$("#view_zlg_sum_transfer").dialog
		({	buttons:
			[
				{ text: "OK & als überwiesen markieren", click: function() { show_zlg_sum(1);} },
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeOnEscape: false,
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Umtauschkurs angeben",
			width:400
		});		

	}

	function show_zlg_sum(flag_as_transfered)
	{
		if ($("#payment_type").val()==0)
		{
			alert("Bitte eine Zahlart auswählen!");
			$("#payment_type").focus();
			return;			
		}
		wait_dialog_show();
		
		var post_object = 					new Object();
		post_object['API'] = 				'payments';
		post_object['APIRequest'] =			'ZLGPaymentSumGet';
		post_object['payment_type_id'] =	$("#payment_type").val();
		post_object['not_transfered'] =		$("#not_transfered").val();
		if ($("#not_transfered").val()==0)
		{
			post_object['transfered_from'] = $("#transfered_from").val();
			post_object['transfered_to'] =	 $("#transfered_to").val();
		}
		if ($("#response_to").val()!="")
		{
			post_object['response_from'] = 	 $("#response_from").val();
			post_object['response_to'] =	 $("#response_to").val();
		}
		if ($("#created_to").val()!="")
		{
			post_object['created_from'] = 	 $("#created_from").val();
			post_object['created_to'] =	 	$("#created_to").val();
		}
		if ($("#invoice_to").val()!="")
		{
			post_object['invoice_from'] = 	 $("#invoice_from").val();
			post_object['invoice_to'] =	 	$("#invoice_to").val();
		}
		if ($("#shop").val()!=0)
		{
			post_object['shop_id'] =	 $("#shop").val();
		}
		if (flag_as_transfered==1)
		{
			post_object['flag_as_transfered'] =	 1;
			//CHECK FOR EXCHANGERATE
			if ($("#transfer_exhangerate").val()=="" || $("#transfer_exhangerate").val()==0)
			{
				alert("Bitte einen Wechselkurs angeben");
				$("#transfer_exhangerate").focus();
				return;
			}
			else
			{
				post_object['exchangerate_from_EUR'] =	 $("#transfer_exhangerate").val().replace(",", ".");	
				$("#view_zlg_sum_transfer").dialog("close");
			}
		}

		$.post("<?php echo PATH; ?>soa2/", post_object, function($data)
		{
			wait_dialog_hide();
//show_status2($data);
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
		
			//CREATE OUTPUT
			var html = '';
			html+='<table>';
			html+='<tr>';
			html+='	<th></th>';
			html+='	<th>Shop</th>';
			html+='	<th>Invoice ID</th>';
			html+='	<th>Invoice Date</th>';
			html+='	<th>Order ID</th>';
			html+='	<th>Order Total</th>';
			html+='	<th>Txn Fee</th>';
			html+='	<th>Payment Type</th>';
			html+='	<th>Created</th>';
			html+='	<th>IDIMS gebcht am</th>';
			html+='	<th>Überwiesen am</th>';
			html+='</tr>';
			
			var $zlg_logs = $xml.find("zlg_logs");
			$zlg_logs.find("zlg_log").each(function()
			{
				html+='<tr>';
				html+='	<td>'+$(this).find("id_log").text()+'</td>';
				html+='	<td>'+Shop_Shops[$(this).find("shop_id").text()]["title"]+'</td>';
				html+='	<td>'+$(this).find("invoice_id").text()+'</td>';
				html+='	<td>'+convert_time_from_timestamp($(this).find("creation_time").text(), "date")+'</td>';	
				html+='	<td></td>';
				html+='	<td>'+$(this).find("order_total_FC").text()+' '+$(this).find("currency_code").text()+'</td>';
				html+='	<td>'+$(this).find("fee_FC").text()+' '+$(this).find("currency_code").text()+'</td>';
				html+='	<td>'+PaymentTypes[$(this).find("payment_type_id").text()]["title"]+'</td>';
				if ($(this).find("creation_time").text()==0)
				{
					html+='	<td></td>';
				}
				else
				{
					html+='	<td>'+convert_time_from_timestamp($(this).find("creation_time").text(), "complete")+'</td>';	
				}
				if ($(this).find("response_time").text()==0)
				{
					html+='	<td></td>';
				}
				else
				{
					html+='	<td>'+convert_time_from_timestamp($(this).find("response_time").text(), "complete")+'</td>';	
				}
				if ($(this).find("amount_transfer_time").text()==0)
				{
					html+='	<td></td>';
				}
				else
				{
					html+='	<td>'+convert_time_from_timestamp($(this).find("amount_transfer_time").text(), "complete")+'</td>';
				}
				html+='</tr>';
			});
			html+='<tr>';
			html+='	<th colspan="4"></th>';
			html+='	<th>'+$zlg_logs.find("zlg_logs_total_FC").text()+' '+$(this).find("currency_code").text()+'</th>';
			html+='	<th>'+$zlg_logs.find("zlg_logs_fee_FC").text()+' '+$(this).find("currency_code").text()+'</th>';
			html+='	<th colspan="3"></th>';
			html+='</tr>';
			html+='</table>';
			
			if ($("#view_show_zlg_sum").length == 0)
			{
				$("#main").append('<div class="view" id="view_show_zlg_sum" style="float:left">');
			}

			$("#view_show_zlg_sum").html(html);
			$("#view_show_zlg_sum").show();
			
		});
	}


	function show_zlg_msgs_menu()
	{
		var html = '';
		html+='<div>';
		html+='<span class="tabs">';
		html+='<select id="send_state" size="1">';
		html+='		<option value=1>ungesendete ZLG Msgs</option>';	
		html+='		<option value=3>Expired</option>';	
		html+='		<option value=2>ungesendete ZLG Msgs+Expired</option>';	
		html+='		<option value=0>gesendete ZLG Msgs</option>';	
		html+='	</select>';
		html+='</span>';

		html+='<span class="tabs">';
		html+='<select id="acknowledgment" size="1">';
		html+='		<option value="Success">Erfolgreich gesendete ZLG Msgs</option>';	
		html+='		<option value="Error">gesendete ZLG Msgs mit Fehlern</option>';	
		html+='	</select>';
		html+='</span>';

		html+='<span class="tabs">';
		html+='<select id="shop_id" size="1">';
		html+='	<option value=0>alle Shops</option>';
		for (var Index in Shop_Shops)
		{
			html+='		<option value='+Index+'>'+Shop_Shops[Index]["title"]+'</option>';	
		}
		html+='	</select>';
		html+='</span>';



		html+='<span class="tabs">';
		html+='<select id="payment_type" size="1">';
		for (var Index in PaymentTypes)
		{
			html+='		<option value='+Index+'>'+PaymentTypes[Index]["title"]+'</option>';	
		}
		html+='	</select>';
		html+='</span>';
		html+='</div>';
		
		html+='<br />';

		html+='<div>';
		html+='<span class="tabs">';
		html+='Rechnungsdatum von&nbsp;';
		html+='<input type="text" id="invoice_date_from" size="10" />';
		html+='&nbsp;bis&nbsp;';
		html+='<input type="text" id="invoice_date_to" size="10" />';
		html+='</span>';

		html+='<span class="tabs">';
		html+='<button id="btn2" onclick="show_zlg_msgs();"> Anzeigen </button>';
		html+='</span>';
		html+='</div>';

		$("#sub_menu").html(html);
		
		//ADD DATEPICKER
		$( "#invoice_date_from" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		
		$( "#invoice_date_to" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });

		$("#sub_menu").show();
		$(".view").hide();
	}
	
	function show_zlg_msgs()
	{
		// GET DATA 
		wait_dialog_show();
		var post_object 					= new Object();
		post_object['API'] 					= 'payments';
		post_object['APIRequest'] 			= 'ZLGPaymentMessagesGet';
		post_object['send_state'] 			= $("#send_state").val();
		post_object['acknowledgment'] 		= $("#Ack").val();
		post_object['payment_type_id'] 		= $("#payment_type").val();
		post_object['shop_id'] 				= $("#shop_id").val();
		post_object['invoice_date_from'] 	= $("#invoice_date_from").val(); 
		post_object['invoice_date_to'] 		= $("#invoice_date_to").val();

		$.post("<?php echo PATH; ?>soa2/", post_object, function($data)
		{
			wait_dialog_hide();
//show_status2($data);
//alert($data);
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			//CREATE OUTPUT
			var html = '';
			html+='<table>';
			html+='<tr>';
			html+='	<th></th>';
			html+='	<th>Shop</th>';
			html+='	<th>Invoice ID</th>';
			html+='	<th>Invoice Time</th>';
			html+='	<th>Ack</th>';
			html+='	<th>Total EUR</th>';
			html+='	<th>IDIMS EUR</th>';
			html+='	<th>Diff.</th>';
//			html+='	<th>Txn Fee</th>';
			html+='	<th>Payment Type</th>';
			html+='	<th>IDIMS gebcht am</th>';
			html+='	<th>Created Time</th>';
//			html+='	<th>Überwiesen am</th>';
			html+='	<th></th>';
			html+='</tr>';
			
			var $zlg_logs = $xml.find("idims_zlg_logs");
			$zlg_logs.find("idims_zlg_log").each(function()
			{
				html+='<tr>';
				html+='	<td><input name="invoices" type="checkbox" value="'+$(this).find("invoice_id").text()+'" /> '+$(this).find("id_log").text()+'</td>';
				html+='	<td>'+Shop_Shops[$(this).find("shop_id").text()]["title"]+'</td>';
				html+='	<td>'+$(this).find("invoice_id").text()+'</td>';
				html+='	<td>'+convert_time_from_timestamp($(this).find("invoice_time").text(), "complete")+'</td>';
				html+='	<td>'+$(this).find("acknowledgment").text()+'</td>';
				html+='	<td>'+$(this).find("amount").text()+'</td>';
				html+='	<td>'+$(this).find("rng_brutto").text()+'</td>';
				html+='	<td>'+$(this).find("difference").text()+'</td>';
	//			html+='	<td>'+$(this).find("fee").text()+'</td>';
				html+='	<td>'+PaymentTypes[$(this).find("payment_type_id").text()]["title"]+'</td>';
				if ( $(this).find("response_time").text() != 0)
				{
					html+='	<td>'+convert_time_from_timestamp($(this).find("response_time").text(), "complete")+'</td>';
				}
				else
				{
					html+='	<td></td>';	
				}
	//			html+='	<td>'+convert_time_from_timestamp($(this).find("amount_transfer_time").text(), "complete")+'</td>';
				html+='	<td>'+convert_time_from_timestamp($(this).find("creation_time").text(), "complete")+'</td>';
				if ($(this).find("response_time").text() == 0)
				{
					html+='	<td><img style="margin:0px 0px 0px 0px; border:0; padding:0; float:left; cursor:pointer" onclick="ZLG_process_manual('+$(this).find("invoice_id").text()+');" src="images/icons/16x16/process.png" alt="ZLG Message an IDIMS senden" title="ZLG Message an IDIMS senden" /></td>';	
				}
				else
				{
					html+='	<td></td>';	
				}
				html+='</tr>';
			});
			html+='<tr>';
			html+='	<th colspan="6"></th>';
			html+='	<th>'+$zlg_logs.find("zlg_logs_total").text()+'</th>';
			html+='	<th>'+$zlg_logs.find("zlg_logs_fee").text()+'</th>';
			html+='	<th colspan="3"></th>';
			html+='</tr>';
			html+='</table>';

			if ($("#view_show_zlg_msgs").length == 0)
			{
				$("#main").append('<div id="view_show_zlg_msgs" style="float:left">');
			}

			$("#view_show_zlg_msgs").html(html);
			$("#view_show_zlg_msgs").show();
			/*
			if ($("#transfer_zld_btn").length == 0)
			{

				// BUTTON hinzufügen
				var html='';
				html+='<span class="tabs" id="transfer_zld_btn">';
				html+='<button id="btn2" onclick="send_selected_zlg_msgs(1);"> Buchungssätze übermitteln </button>';
				html+='</span>';
				
				$("#sub_menu").append(html);
			}
			*/
		});
	}
	
	function send_selected_zlg_msgs(cicle)
	{
		//show_status2($('input[name="invoices"]:checked').serialize());
		//return;
		var invoice=new Array();
		var counter=0;
		$('input[name="invoices"]:checked').each(function() {
			invoice[counter] = this.value;
		  counter++;
		});

		var step = 100 / counter;

		if (cicle <= counter)
		{
			
			wait_dialog_show("Zahldaten werden an IDIMS übermittelt...", Math.round(step*cicle));
			

			var post_object = 					new Object();
			post_object['API'] = 				'idims';
			post_object['APIRequest'] =			'ZLGPaymentSend';
			post_object['invoice_id'] =			invoice[cicle-1];
			
			$.post("<?php echo PATH; ?>soa2/", post_object, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				send_selected_zlg_msgs(cicle+1);
				
			});
				
		//	send_selected_zlg_msgs(cicle+1);
			return;
		}
		wait_dialog_hide();
		
		show_zlg_msgs();
	}
		
	function ZLG_process_manual($invoice_id)
	{
		wait_dialog_show("Zahldaten werden an IDIMS übermittelt...");
		

		var post_object = 					new Object();
		post_object['API'] = 				'idims';
		post_object['APIRequest'] =			'ZLGPaymentSend';
		post_object['invoice_id'] =			$invoice_id;
		
		$.post("<?php echo PATH; ?>soa2/", post_object, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			send_selected_zlg_msgs(cicle+1);
			
		});
			
		show_zlg_msgs();
		return;
	}
	
</script>


<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Shop</a>';
	echo ' > Zahlungstools';
	echo '</p>';
	echo '<h1>Zahlungstools</h1>';
	
	echo '<div id="main"></div>';
/*	
	echo '<script type="text/javascript">view();</script>';
*/
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>