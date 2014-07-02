<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	payments = new Object;

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
		
		payments_data["lastOrderDeposit"] = $orderdata.find("lastOrderDeposit").text();
		//payments_data["lastOrderDepositEUR"] = $orderdata.find("lastOrderDepositEUR").text();
		payments_data["lastOrderDepositEUR"] = 20.23;
		payments_data["lastOrderTotal"] = $orderdata.find("lastOrderTotal").text();
		payments_data["lastOrderTotalEUR"] = $orderdata.find("lastOrderTotalEUR").text();
		
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
						
			}
		);		
		
show_status2(print_r(payments_data));
	}



	function payments_dialog_load(orderid)
	{
		

		$.post("<?php echo PATH; ?>soa2/index.php", { 
				API: "payments", 
				APIRequest: "OrderPaymentsGet2", 
				orderid:orderid},
			function ($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
				var $Ack = $xml.find("Ack").text();
				if ($Ack!="Success") {show_status2($data); return;}
				update_payments_array($data);
				payments_dialog(orderid);
			}
		);
	}
	
	function translate_payment_reason(reason, notification_type)
	{
		if (reason == "Completed") return "Zahlungseingang";
		else if (reason == "Payment") {
			if (notification_type == 4) return "Depositabzug von Zahlung";
			if (notification_type == 5) return "Depositerhöhung von Order";
			}
		else if (reason == "Linking Payment") return "Userdepositumbuchung";	
		else return reason;
		
	}
	
	function payments_dialog(orderid)
	{
		show_actions_menu(orderid); //hide options
		
		if( $("#Payment_Dialog").length==0 )
		{
			$("body").append('<div id="Payment_Dialog"></div>');
		}
		
		
		//BUILD PAYMENTOVERVIEW
		var html = '';
		html+='<div style="float:left; width:800px"><table>';
		html+='<tr>';
		html+='	<th>Datum</th>';
		html+='	<th>Vorgang</th>';
		html+='	<th>Summe '+orders[orderid]["Currency_Code"]+'</th>';
		if (orders[orderid]["Currency_Code"]!="EUR")
		{
			html+='	<th>Summe EUR</th>';
		}
		html+='	<th>TXN ID  <button onclick="payments_detail_show();">show</button></th>';
		html+='	<th>Betrag offen</th>';
		html+='</tr>';

		//ORDERDATA
		// VIEW FOR DETAIL
		for (var id_PN in payments_data["order"])
		{
			if (payments_data["order"][id_PN]["reason"]=="OrderAdd") var $orderVorgang = "Bestellung angelegt";
			if (payments_data["order"][id_PN]["reason"]=="OrderAdjustment") var $orderVorgang = "Bestellung angepasst";
			html+='<tr class="order_detail" style="display:none">';
			html+='	<td>'+convert_time_from_timestamp(payments_data["order"][id_PN]["accounting_date"], "complete")+'</td>';
			html+='	<td>'+$orderVorgang+'</td>';
			html+='	<td>'+payments_data["order"][id_PN]["total"]+' '+payments_data["order"][id_PN]["currency"]+'</td>';
			if (orders[orderid]["Currency_Code"]!="EUR")
			{
				html+='	<td></td>';
			}
			html+='	<td>'+payments_data["order"][id_PN]["f_id"]+'</td>';
			html+='	<td>'+payments_data["order"][id_PN]["deposit_EUR"]+'</td>';

			html+='</tr>';
		}
		
		//VIEW FOR OVERVIEW
		html+='<tr class="order_general" id="payment_dialog_order_row">';
		for (var id_PN in payments_data["order"])
		{
			if (payments_data["order"][id_PN]["reason"]=="OrderAdd") var firstmod = convert_time_from_timestamp(payments_data["order"][id_PN]["accounting_date"], "complete");
		}
		html+='	<td>'+firstmod+'</td>';
		html+='	<td><b>Bestellung</b></td>';
		html+='	<td>'+payments_data["lastOrderTotal"]+' '+orders[orderid]["Currency_Code"]+'</td>';
		if (orders[orderid]["Currency_Code"]!="EUR")
		{
			html+='	<td></td>';
		}
		html+='	<td>'+orderid+'</td>';
		html+=' <td>'+(payments_data["lastOrderDepositEUR"]*1).toFixed(2).toString().replace(".", ",");
		if ((payments_data["lastOrderDepositEUR"]*1)>0)
		{
			html+='		<img class="refund_button" id="refund_button_order" style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer; display:none" src="<?php echo PATH;?>images/icons/16x16/back.png" alt="Rückzahlung" title="Rückzahlung" onclick="payment_dialog_set_refund_order('+orderid+');" />';
		}
		html+='</td>';
		html+='</tr>';
		
		//PAYMENTDATA
		for (var TxnID  in payments_data["payment"])
		{
			for (var id_PN in payments_data["payment"][TxnID])
			{
				if (payments_data["payment"][TxnID][id_PN]["notification_type"]==4 || payments_data["payment"][TxnID][id_PN]["notification_type"]==5) var paymentdetail = true; else var paymentdetail = false;
				
				//define refundable rows
				
				if (paymentdetail)	html+='<tr class="payment_detail" style="display:none">'; else html+='<tr class="payment_dialog_payment_row" id="payment_dialog_payment_row_'+TxnID+'" style="background-color:#fff">';
				html+='	<td>'+convert_time_from_timestamp(payments_data["payment"][TxnID][id_PN]["accounting_date"], "complete")+'</td>';
				html+='	<td>'+PaymentTypes[payments_data["payment"][TxnID][id_PN]["payment_type_id"]]["title"]+' '+translate_payment_reason(payments_data["payment"][TxnID][id_PN]["reason"], payments_data["payment"][TxnID][id_PN]["notification_type"])+'</td>';
				html+='	<td>'+payments_data["payment"][TxnID][id_PN]["total"]+' '+payments_data["payment"][TxnID][id_PN]["currency"]+'</td>';
				if (orders[orderid]["Currency_Code"]!="EUR")
				{
					html+='	<td></td>';
				}
				html+='	<td>'+TxnID+'</td>';
				//ANZEIGE PAYMENT DEPOSIT / NUR ZUR ZAHLUNGSBUCHUNG ANZEIGEN, NICHT SYSTEMBUCHUNGEN 
				if (payments_data["payment"][TxnID][id_PN]["notification_type"]==1 && payments_data["payment"][TxnID][id_PN]["reason"]=="Completed" && payments_data["lastPaymentDeposit"][TxnID]!=0)
				{
					html+='	<td>';
					html+=(payments_data["lastPaymentDeposit"][TxnID]*1).toFixed(2).toString().replace(".", ",");
					html+='<img class="refund_button" id="refund_button_payment_'+TxnID+'" style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer; display:none" src="<?php echo PATH;?>images/icons/16x16/back.png" alt="Rückzahlung" title="Rückzahlung" onclick="payment_dialog_set_refund(\''+TxnID+'\');" />';
					html+=' </td>';	
				}
				else
				{
					html+='	<td></td>';	
				}
				html+='</tr>';
			}
		}

		html+='</table></div>';
		
		//PAYMENT ACTIONS
		html+='<div id="payments_dialog_actions" style="float:left; width:350px;">';
		html+='</div>';
		
		$("#Payment_Dialog").html(html);
		

		//DRAW PAYMENT ACTIONS
		payments_dialog_draw_actions(orderid, "payment");
		// SHOW / HIDE PAYMENTTRANSACTIONID FIELD
		show_txn_id_field("payment");
		
		$("#Payment_Dialog").dialog
		({	buttons:
			[
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeOnEscape: true,
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Zahlungen",
			width:1200
		});		
	}
	
	function payments_dialog_draw_actions(orderid, mode)
	{
		//BUILD PAYMENT SELECT BOX
			var paymentselectbox='';
			paymentselectbox+='<select id="payments_dialog_paymentType" size="1" onchange="show_txn_id_field(\''+mode+'\')">';
	
			//SHOPS AUßER EBAY
			if (Shop_Shops[orders[orderid]["shop_id"]]["shop_type"]!=2)
			{
				for (var arrayIndex in PaymentTypes)
				{
					//if (arrayIndex!=0)	paymentselectbox+='<option value='+arrayIndex+'>'+PaymentTypes[arrayIndex]["title"]+'</option>';
					if (arrayIndex == orders[orderid]["PaymentTypeID"])
					{
						paymentselectbox+='<option value='+arrayIndex+' selected>'+PaymentTypes[arrayIndex]["title"]+'</option>';
					}
					else
					{
						paymentselectbox+='<option value='+arrayIndex+'>'+PaymentTypes[arrayIndex]["title"]+'</option>';
					}
				}
			}
	
			else
			//EBAYSHOPS
			{
				for (var arrayIndex in PaymentTypes)
				{
					if (arrayIndex==0 || arrayIndex==2 || arrayIndex==3 || arrayIndex==4)
					{
						if (arrayIndex == orders[orderid]["PaymentTypeID"])
						{
							paymentselectbox+='<option value='+arrayIndex+' selected>'+PaymentTypes[arrayIndex]["title"]+'</option>';
						}
						else
						{
							paymentselectbox+='<option value='+arrayIndex+'>'+PaymentTypes[arrayIndex]["title"]+'</option>';
						}
					}
				}

			}
			paymentselectbox+='</select>';
	
		//GET TIME NOW 
		var time_now = new Date();
		var timestamp_now = Math.round(time_now.getTime()/1000);
		
		//VORBELEGUNG ZAHLSUMME
		if (mode == "payment")
		{
			var payment_total = payments_data["lastOrderDepositEUR"];
			if (payment_total<0) payment_total*=-1;
			if (payment_total ==0)
			{
				payment_total = "0,00";
			}
			else
			{
				payment_total= payment_total.toFixed(2).toString().replace(".", ",");
				if ((payments_data["lastOrderDepositEUR"]*1)<0) payment_total="-"+payment_total;
			}
			
			
			$("#payment_dialog_order_row").css("background-color", "#eee");
			$(".payment_dialog_payment_row").css("background-color", "#fff");
			// Rückzahlbutton ausblenden
			$(".refund_button").hide();

			
		}
		else
		{
			payment_total = "0,00";	
		}
		
		
		//MODE REFUND => Rückzahlbare Positionen -> Rückzahlbutton einblenden
		if (mode == "refund")
		{
			$(".refund_button").show();
		}
		
		
		html='';
		html+='<table>';
		html+='<colgroup><col width="200px"><col width="150px"></colgroup>';
		html+='<tr>';
		html+='	<td></td>';
		html+='	<td><select id="payments_dialog_payment_action" size="1" onchange="payments_dialog_draw_actions('+orderid+', this.value);">';
			if (mode == "payment") html+='	<option value="payment" selected>Zahlungseingang</option>'; else html+='	<option value="payment">Zahlungseingang</option>';
			if (mode == "refund") html+='	<option value="refund" selected>Rückzahlung</option>'; else html+='	<option value="refund">Rückzahlung</option>';
		html+='	</select></td>';
		html+='</tr>';
		if (mode == "payment")
		{
			html+='<tr>';
			html+='	<td>Zahlungstyp</td>';
			html+='	<td>'+paymentselectbox+'</td>';
			html+='</tr><tr id="payments_dialog_payment_txn_id_row" style="display:none">';
			html+='	<td>Transaktions ID</td>';
			html+='	<td><input type="text" id="payments_dialog_payment_txn_id" size="20" value="" /></td>';
			html+='</tr><tr>';
			html+='	<td>Betrag</td>';
			html+='	<td><input type="text" id="payments_dialog_payment_total" size="10" value="'+payment_total+'" /> EUR</td>';
			html+='</tr><tr>';
			html+='	<td>Zahlungsdatum</td>';
			html+='	<td><input type="text" id="payments_dialog_payment_date" size="10" value="'+convert_time_from_timestamp(timestamp_now, "date")+'" onchange="payments_dialog_payment_timestamp_update(this.value);"/>';
			html+='		<input type="text" id="payments_dialog_payment_timestamp" value="'+timestamp_now+'" /></td>';
			html+='</tr><tr>';
			html+='	<td><img  style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="<?php echo PATH;?>images/icons/24x24/remove.png" alt="Zahlung speichern" title="Zahlung speichern" onclick="" /></td>';
			html+='	<td><img  style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="<?php echo PATH;?>images/icons/24x24/accept.png" alt="Zahlung speichern" title="Zahlung speichern" onclick="payment_dialog_do_payment('+orderid+');" /></td>';		
			html+='</tr>';
		}
		if (mode == "refund")
		{
			html+='<tr id="payments_dialog_paymentType_row" style="display:none">';
			html+='	<td>Zahlungstyp</td>';
			html+='	<td><input type="text" id="payments_dialog_paymentType_show" size="20" disabled />';
			html+='		<input type="hidden" id="payments_dialog_paymentType" /></td>';
			html+='</tr><tr id="payments_dialog_payment_txn_id_row" style="display:none">';
			html+='	<td>Transaktions ID</td>';
			html+='	<td><input type="text" id="payments_dialog_payment_txn_id" size="20" value="" /></td>';
			html+='</tr><tr  id="payments_dialog_payment_total_row" style="display:none">';
			html+='	<td>Betrag</td>';
			html+='	<td><input type="text" id="payments_dialog_payment_total" size="10" disabled /> EUR</td>';
			html+='</tr><tr id="payments_dialog_payment_date_row" style="display:none">';
			html+='	<td>Zahlungsdatum</td>';
			html+='	<td><input type="text" id="payments_dialog_payment_date" size="10" value="'+convert_time_from_timestamp(timestamp_now, "date")+'" onchange="payments_dialog_payment_timestamp_update(this.value);"/>';
			html+='		<input type="text" id="payments_dialog_payment_timestamp" value="'+timestamp_now+'" /></td>';
			html+='</tr><tr id="payments_dialog_payment_button_row" style="display:none">';
			html+='	<td><img  style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="<?php echo PATH;?>images/icons/24x24/remove.png" alt="Rückzahlung zurücksetzen" title="Rückzahlung zurücksetzen" onclick="payment_dialog_reset_refund('+orderid+');" /></td>';
			html+='	<td><img  style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="<?php echo PATH;?>images/icons/24x24/accept.png" alt="Rückzahlung durchführen" title="Rückzahlung durchführen" onclick="payment_dialog_do_action('+orderid+');" /></td>';		
			html+='</tr>';
		}

		html+='</table>';


		$("#payments_dialog_actions").html(html);

		// SHOW / HIDE PAYMENTTRANSACTIONID FIELD
		show_txn_id_field();

		//SET DATEPICKER
		$( "#payments_dialog_payment_date" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });


	}
	
	
	function payments_detail_show()
	{
		if ($(".payment_detail").is(":visible"))
		{
			$(".payment_detail").hide();
			$(".order_detail").hide();
			$(".order_general").show();
			
		}
		else
		{
			$(".payment_detail").show();
			$(".order_detail").show();
			$(".order_general").hide();
		}
		
	}
	
	function payments_dialog_payment_timestamp_update(date)
	{
		var year = date.substring(6,10)*1;
		var month = ((date.substring(3,5))*1)-1;
		var day = date.substring(0,2)*1;
		var date_day = new Date(year, month, day, 0, 0, 0);
		$("#payments_dialog_payment_timestamp").val(Math.round(date_day/1000));
	}
	
	function show_txn_id_field(mode)
	{	
		if (mode == "payment")
		{
			if (PaymentTypes[$("#payments_dialog_paymentType").val()]["method"]==2)
			//if ($("#payments_dialog_paymentType").val()==4 || $("#payments_dialog_paymentType").val()==5)
			{
				$("#payments_dialog_payment_txn_id_row").show();
			}
			else
			{
				$("#payments_dialog_payment_txn_id_row").hide();
			}
		}
	}
	
	function payment_dialog_do_payment(orderid)
	{
		//check	for paymenttype
		if ($("#payments_dialog_paymentType").val()==0)
		{
			alert("Bitte eine Zahlart auswählen!");
			$("#payments_dialog_paymentType").focus();
			return;	
		}
		//CHECK IF TXNID is set if needed
		if (PaymentTypes[$("#payments_dialog_paymentType").val()]["method"]==2 && $("#payments_dialog_payment_txn_id").val()=="")
		{
			alert("Bitte die TransaktionsID zur Zahlung angeben!");
			$("#payments_dialog_payment_txn_id").focus();
			return;
		}
		//CHECK FOR PAYMENT_TOTAL
		if ($("#payments_dialog_payment_total").val()=="")
		{
			alert("Bitte den Zahlbetrag angeben!");
			$("#payments_dialog_payment_total").focus();
			return;
		}
		//CHECK FOR PAYMENT_TOTAL IS NOT <=0
		var $payment_total = $("#payments_dialog_payment_total").val().replace(",", ".")*1;
		if ($payment_total <= 0)
		{
			alert("Bitte einen Zahlbetrag größer 0 angeben!");
			$("#payments_dialog_payment_total").focus();
			return;
		}
		//check for payment_date
		if ($("#payments_dialog_payment_date").val()=="")
		{
			alert("Bitte das Zahldatum angeben!");
			$("#payments_dialog_payment_date").focus();
			return;
		}
				//GET TIME NOW 
		var time_now = new Date();
		var timestamp_now = Math.round(time_now.getTime()/1000);
		if ($("#payments_dialog_payment_timestamp").val()>timestamp_now)
		{
			alert("Das angegebene Zahldatum liegt in der Zukunft!");
			$("#payments_dialog_payment_date").focus();
			return;
		}
		
		//check if paypaltransactionID is known && PAYMENT DEPOSIT 0
		if ($("#payments_dialog_paymentType").val()==4)
		{
			$.post("<?php echo PATH; ?>soa2/index.php", { 
					API: "payments", 
					APIRequest: "PaymentGet", 
					TransactionID:$("#payments_dialog_payment_txn_id").val()},
				function ($data)
				{
					wait_dialog_hide();
					try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
					var $Ack = $xml.find("Ack").text();
					if ($Ack!="Success") {show_status2($data); return;}
					alert($xml.find("PaymentTotal").text());
				}
			);
		}
		
		alert("OK");
		
	}
	
	function payment_dialog_set_refund(TxnID)
	{
		
	//	alert(TxnID);
		var time_now = new Date();
		var timestamp_now = Math.round(time_now.getTime()/1000);

		var payment_type_id = false;
		for (var id_PN in payments_data["payment"][TxnID])
		{
			if (!payment_type_id) payment_type_id = payments_data["payment"][TxnID][id_PN]["payment_type_id"];
		}

		
		//hide refundbutton
		$("#refund_button_payment_"+TxnID).hide();
		//Green row
		$("#payment_dialog_payment_row_"+TxnID).css("background-color", "#dfd");


		$("#payments_dialog_paymentType").val(payment_type_id);
		//$("#payments_dialog_paymentType").attr(":disabled", "disabled");
		$("#payments_dialog_paymentType_show").val(PaymentTypes[payment_type_id]["title"]);
		$("#payments_dialog_paymentType_row").show();
		
		$("#payments_dialog_payment_total").val((payments_data["lastPaymentDeposit"][TxnID]*1).toFixed(2).toString().replace(".", ","));
		$("#payments_dialog_payment_total_row").show();
		
		$("#payments_dialog_payment_date").val(convert_time_from_timestamp(timestamp_now, "date"));
		payments_dialog_payment_timestamp_update($("#payments_dialog_payment_date").val());
		$("#payments_dialog_payment_date_row").show();
		
		$("#payments_dialog_payment_button_row").show();
		
		
		//PRÜFE WELCHE REFUNDBUTTONS AUSGEBLENDET WERDEN MÜSSEN
		for (var TxnID  in payments_data["payment"])
		{
			for (var id_PN in payments_data["payment"][TxnID])
			{
				if (payments_data["payment"][TxnID][id_PN]["notification_type"]==1 && payments_data["payment"][TxnID][id_PN]["reason"]=="Completed" && payments_data["lastPaymentDeposit"][TxnID]!=0)
				{
					if (payments_data["payment"][TxnID][id_PN]["payment_type_id"]!=payment_type_id)
					{
						$("#refund_button_payment_"+TxnID).hide();
					}
				}
			}
		}
		
	}

	function payment_dialog_set_refund_order(orderid)
	{
	//	alert(TxnID);
		var time_now = new Date();
		var timestamp_now = Math.round(time_now.getTime()/1000);

		//hide refundbutton
		$("#refund_button_order").hide();
		//Green row
		$("#payment_dialog_order_row").css("background-color", "#dfd");

		var payment_type_id = orders[orderid]["PaymentTypeID"];
		$("#payments_dialog_paymentType").val(payment_type_id);
		$("#payments_dialog_paymentType_show").val(PaymentTypes[payment_type_id]["title"]);
		$("#payments_dialog_paymentType_row").show();
		
		$("#payments_dialog_payment_total").val((payments_data["lastOrderDepositEUR"]*1).toFixed(2).toString().replace(".", ","));
		$("#payments_dialog_payment_total_row").show();
		
		$("#payments_dialog_payment_date").val(convert_time_from_timestamp(timestamp_now, "date"));
		payments_dialog_payment_timestamp_update($("#payments_dialog_payment_date").val());
		$("#payments_dialog_payment_date_row").show();
		
		$("#payments_dialog_payment_button_row").show();
	}

	function payment_dialog_reset_refund(orderid)
	{
		$("#payment_dialog_order_row").css("background-color", "#eee");
		$(".payment_dialog_payment_row").css("background-color", "#fff");
		payments_dialog_draw_actions(orderid, "refund");
	}
	
	function payment_dialog_do_action(orderid)
	{
		payments_dialog_load(orderid);
	}