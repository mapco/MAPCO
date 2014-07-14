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



	function payments_dialog_load(orderid)
	{
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/index.php", { 
				API: "payments", 
				APIRequest: "OrderPaymentsGet", 
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
		else if (reason == "Refunded") return "Rückzahlung";	
		else return reason;
		
	}
	
	function payments_dialog(orderid, Currency )
	{
		wait_dialog_hide();
		Currency = typeof Currency !== 'undefined' ? Currency : orders[orderid]["Currency_Code"];		//if (payments_dialog.arguments.lentgh==1) var $show_data_in_currency="EUR"; else var $show_data_in_currency=Currency;
		var $show_data_in_currency=Currency;
		
		if ($("#action_menu_options"+orderid).is(":visible")) show_actions_menu(orderid); //hide options
		
		if( $("#Payment_Dialog").length==0 )
		{
			$("body").append('<div id="Payment_Dialog"></div>');
		}
		
		//var $show_data_in_currency = "EUR";
		
		//BUILD PAYMENTOVERVIEW
		var html = '';
		html+='<div style="float:left; width:800px;" id="payment_overview" >';
		html+='<table>';
		html+='<tr>';
		html+='	<th style="width:150px">Datum</th>';
		html+='	<th style="width:200px">Vorgang</th>';
		if (orders[orderid]["Currency_Code"]=="EUR")
		{
			html+='	<th style="width:100px">Summe</th>';
		}
		else
		{
			html+='	<th style="width:100px">Summe';
			html+='<select id="currency_select" size="1" onchange="payments_dialog('+orderid+', this.value);">';
			if ($show_data_in_currency=="EUR")
			{
				html+='	<option value="EUR" selected="selected">EUR</option>';
				html+='	<option value="'+orders[orderid]["Currency_Code"]+'">'+orders[orderid]["Currency_Code"]+'</option>';
			}
			else
			{
				html+='	<option value="EUR">EUR</option>';
				html+='	<option value="'+orders[orderid]["Currency_Code"]+'" selected="selected">'+orders[orderid]["Currency_Code"]+'</option>';
			}
			html+='</select></th>';
		}
		html+='	<th  style="width:150px">Order ID</th>';
	//	html+='	<th  style="width:150px">Order ID  <button onclick="payments_detail_show();">show</button></th>';
	//	html+='	<th>verrechnet mit Zahlung</th>';
		if ((payments_data["lastOrderDeposit"]*1)<=0)
		{
			html+='	<th  style="width:100px">noch zu zahlen</th>';
		}
		else
		{
			html+='	<th  style="width:100px">Überzahlt</th>';
		}
		html+='</tr>';

		//ORDERDATA
		// VIEW FOR DETAIL
		/*
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
		*/
		//VIEW FOR OVERVIEW
		html+='<tr id="payment_dialog_order_row">';
		for (var id_PN in payments_data["order"])
		{
			if (payments_data["order"][id_PN]["reason"]=="OrderAdd") var firstmod = convert_time_from_timestamp(payments_data["order"][id_PN]["accounting_date"], "complete");
		}
		html+='	<td>'+firstmod+'</td>';
		html+='	<td><b>Bestellung</b></td>';
		if ($show_data_in_currency!="EUR")
		{
			html+='	<td>'+payments_data["lastOrderTotal"]+' '+orders[orderid]["Currency_Code"]+'</td>';
			html+='	<td>'+orderid+'</td>';
			if ((payments_data["lastOrderDeposit"]*1)<0) var orderdeposit=payments_data["lastOrderDeposit"]*-1; else var orderdeposit=payments_data["lastOrderDeposit"];
			html+='	<td>'+orderdeposit.toFixed(2).toString().replace(".", ",")+' '+orders[orderid]["Currency_Code"];
			if ((payments_data["lastOrderDepositEUR"]*1)>0)
			{
				html+='		<img class="refund_button" id="refund_button_order" style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer; display:none" src="<?php echo PATH;?>images/icons/16x16/back.png" alt="Rückzahlung" title="Rückzahlung" onclick="payment_dialog_set_refund_order('+orderid+');" />';
			}
			html+='</td>'
		}
		else
		{
			html+='	<td>'+payments_data["lastOrderTotalEUR"]+' EUR</td>';
			html+='	<td>'+orderid+'</td>';
			if ((payments_data["lastOrderDepositEUR"]*1)<0) var orderdeposit=payments_data["lastOrderDepositEUR"]*-1; else var orderdeposit=payments_data["lastOrderDepositEUR"];
			html+='	<td>'+orderdeposit.toFixed(2).toString().replace(".", ",")+' EUR';
			if ((payments_data["lastOrderDepositEUR"]*1)>0)
			{
				html+='		<img class="refund_button" id="refund_button_order" style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer; display:none" src="<?php echo PATH;?>images/icons/16x16/back.png" alt="Rückzahlung" title="Rückzahlung" onclick="payment_dialog_set_refund_order('+orderid+');" />';
			}
			html+=' </td>';
		}
		//html+=' <td>'+(payments_data["lastOrderDepositEUR"]*1).toFixed(2).toString().replace(".", ",");
	//	if ((payments_data["lastOrderDepositEUR"]*1)>0)
	//	{
	//		html+='		<img class="refund_button" id="refund_button_order" style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer; display:none" src="<?php echo PATH;?>images/icons/16x16/back.png" alt="Rückzahlung" title="Rückzahlung" onclick="payment_dialog_set_refund_order('+orderid+');" />';
	//	}
	//	html+='</td>';
		html+='</tr>';
		html+='</table>';
		
		
		//PAYMENTDATA
		html+='<table>';
		html+='<tr>';
		html+='	<th style="width:150px">Datum</th>';
		html+='	<th style="width:200px">Vorgang</th>';
		html+='	<th style="width:100px">Summe</th>';
		html+='	<th  style="width:150px">Transaktions ID</th>';
	//	html+='	<th  style="width:150px">Transaktions ID  <button onclick="payments_detail_show();">show</button></th>';
	//	html+='	<th>verrechnet mit Zahlung</th>';
		html+='	<th  style="width:100px">Restguthaben</th>';
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
					html+='	<td>'+convert_time_from_timestamp(payments_data["payment"][TxnID][id_PN]["accounting_date"], "complete")+'</td>';
					html+='	<td>'+PaymentTypes[payments_data["payment"][TxnID][id_PN]["payment_type_id"]]["title"]+' '+translate_payment_reason(payments_data["payment"][TxnID][id_PN]["reason"], payments_data["payment"][TxnID][id_PN]["notification_type"])+'</td>';
					if ($show_data_in_currency!="EUR")
					{
						var $exchangerate = payments_data["payment"][TxnID][id_PN]["exchange_rate_from_EUR"];
						html+='	<td>'+payments_data["payment"][TxnID][id_PN]["total"]+' '+payments_data["payment"][TxnID][id_PN]["currency"]+'</td>';
						html+='	<td>'+TxnID+'</td>';
						//ANZEIGE PAYMENT DEPOSIT / NUR ZUR ZAHLUNGSBUCHUNG ANZEIGEN, NICHT SYSTEMBUCHUNGEN 
						if (payments_data["payment"][TxnID][id_PN]["notification_type"]==1 && payments_data["payment"][TxnID][id_PN]["reason"]=="Completed" && payments_data["lastPaymentDeposit"][TxnID]!=0)
						{
							html+='	<td>';
							html+=(payments_data["lastPaymentDeposit"][TxnID]*1*$exchangerate).toFixed(2).toString().replace(".", ",")+' '+payments_data["payment"][TxnID][id_PN]["currency"];
							html+='<img class="refund_button" id="refund_button_payment_'+TxnID+'" style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer; display:none" src="<?php echo PATH;?>images/icons/16x16/back.png" alt="Rückzahlung" title="Rückzahlung" onclick="payment_dialog_set_refund(\''+TxnID+'\', \''+$show_data_in_currency+'\');" />';
							html+=' </td>';	
						}
						else
						{
							html+='	<td></td>';	
						}
					}
					else
					{
						var $exchangerate = payments_data["payment"][TxnID][id_PN]["exchange_rate_from_EUR"];
						var $totalEUR=(payments_data["payment"][TxnID][id_PN]["total"]*1)/$exchangerate;
						html+='	<td>'+$totalEUR.toFixed(2).toString().replace(".", ",")+' EUR</td>';
						html+='	<td>'+TxnID+'</td>';
						//ANZEIGE PAYMENT DEPOSIT / NUR ZUR ZAHLUNGSBUCHUNG ANZEIGEN, NICHT SYSTEMBUCHUNGEN 
						if (payments_data["payment"][TxnID][id_PN]["notification_type"]==1 && payments_data["payment"][TxnID][id_PN]["reason"]=="Completed" && payments_data["lastPaymentDeposit"][TxnID]!=0)
						{
							html+='	<td>';
							var $paymentdepositEUR = (payments_data["lastPaymentDeposit"][TxnID]*1)/ $exchangerate;
							html+=$paymentdepositEUR.toFixed(2).toString().replace(".", ",")+' EUR';
							html+='<img class="refund_button" id="refund_button_payment_'+TxnID+'" style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer; display:none" src="<?php echo PATH;?>images/icons/16x16/back.png" alt="Rückzahlung" title="Rückzahlung" onclick="payment_dialog_set_refund(\''+TxnID+'\', \''+$show_data_in_currency+'\');" />';
							html+=' </td>';	
						}
						else
						{
							html+='	<td></td>';	
						}
						
					}
					html+='</tr>';
				}
			}
		}

		html+='</table></div>';
		
	/*	
		//BUILD PAYMENT DETAILS
		html+='<div style="float:left; width:800px; display:none;" id="payment_detail" >';
		html+='<table>';
		html+='<tr>';
		html+='	<th style="width:130px">Datum</th>';
		html+='	<th style="width:200px">Vorgang</th>';
		html+='	<th style="width:90px">Wert</th>';
		html+='	<th style="width:80px">Wertänderung</th>';
		html+='	<th  style="width:150px">Transaktions ID  <button onclick="payments_detail_show();">show</button></th>';
		html+='	<th  style="width:90px">Kontostand</th>';
		html+='	<th  style="width:90px">Zahlungsguthaben</th>';
		html+='</tr>';
		
		html+='</table>';
		html+='</div>';
*/
		
		//PAYMENT ACTIONS
		html+='<div id="payments_dialog_actions" style="float:left; width:350px;">';
		html+='</div>';
		
		$("#Payment_Dialog").html(html);
		
		//DRAW PAYMENT ACTIONS
		payments_dialog_draw_actions(orderid, "payment", $show_data_in_currency);
		// SHOW / HIDE PAYMENTTRANSACTIONID FIELD
		show_txn_id_field("payment");
	
	
		if (<?php echo $_SESSION["userrole_id"]; ?>==1)	
		{
			$("#Payment_Dialog").dialog
			({	buttons:
				[
					{ text: "OrderAdjustment", click: function() { order_adjustment(orderid,Currency);} },
					{ text: "Beenden", click: function() { $(this).dialog("close");} }
				],
				closeOnEscape: true,
				beforeClose: function() {update_view(orderid);}, 
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Zahlungen",
				width:1200
			});		
		}
		else
		{
			$("#Payment_Dialog").dialog
			({	buttons:
				[
					{ text: "Beenden", click: function() { $(this).dialog("close");} }
				],
				closeOnEscape: true,
				beforeClose: function() {update_view(orderid);}, 
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Zahlungen",
				width:1200
			});		
		}
	}
	
	function order_adjustment(orderid, Currency)
	{
		var $OrderIDCounter=0;
		var $CicleCounter=0;
		var OrderIDs = new Array;

		//GET ALL ORDER IDs
		var postfield = new Object;
		postfield['API']=			'shop';
		postfield['Action']=	'OrderGet';
		postfield['id_order']=		orderid;
		$.post('<?php echo PATH;?>soa/', postfield, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
	
			if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); wait_dialog_hide(); return; }
			
			
			$xml.find("OrderItem").each(function()
			{
				OrderIDs[$(this).find("order_id").text()]=0;
			});
			for (var tmp_order_id in OrderIDs)
			{
				$OrderIDCounter++;
				
				//CREATE ORDEREVENT FOR ORDERADJUSTMENT
				var postfield = new Object;
				postfield['API']=			'shop';
				postfield['APIRequest']=	'OrderEventSet';
				postfield['order_id']=		tmp_order_id;
				postfield['eventtype_id']=	29;
		
				$.post('<?php echo PATH;?>soa2/', postfield, function($data1)
				{
					try { $xml1 = $($.parseXML($data1)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
					if ( $xml1.find("Ack").text()!="Success" ) { show_status2($data1); wait_dialog_hide(); return; }
					
					var $order_event_id = $xml1.find("id_event").text();
					
					var postfield = new Object;
					postfield['API']=			'payments';
					postfield['APIRequest']=	'PaymentNotificationHandler';
					postfield['mode']=			'OrderAdjustment';
					postfield['orderid']=		tmp_order_id;
					postfield['order_event_id']=$order_event_id;
					$.post('<?php echo PATH;?>soa2/', postfield, function($data2)
					{
						//alert($data2);
						$CicleCounter++
						try { $xml2 = $($.parseXML($data2)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
						if ( $xml2.find("Ack").text()!="Success" ) { show_status2($data2); wait_dialog_hide(); return; }
						if ($CicleCounter==$OrderIDCounter)
						{
							payments_dialog_load(orderid);
						}
					});
				});
			}
		});
	}
	
	function payments_dialog_draw_actions(orderid, mode, currency)
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
			if (currency =="EUR")
			{
				var payment_total = payments_data["lastOrderDepositEUR"];
			}
			else
			{
				var payment_total = payments_data["lastOrderDeposit"];
			}
			
			if (payment_total >0)
			{
				payment_total = "0,00";
				
			}
			else
			{
				payment_total*=-1;
				//if (currency=="EUR") payment_total/=payments_data["exchangerate"];
				
				payment_total= payment_total.toFixed(2).toString().replace(".", ",");
				//if ((payments_data["lastOrderDepositEUR"]*1)<0) payment_total="-"+payment_total;
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
		html+='	<td><select id="payments_dialog_payment_action" size="1" onchange="payments_dialog_draw_actions('+orderid+', this.value, \''+currency+'\');">';
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
			html+='</tr><tr  id="payments_dialog_payment_total_row" style="display:none">';
			html+='	<td>Betrag</td>';
			html+='	<td><input type="text" id="payments_dialog_payment_total" size="10" value="'+payment_total+'" /> '+currency+'</td>';
			html+='</tr><tr>';
			html+='	<td>Zahlungsdatum</td>';
			html+='	<td><input type="text" id="payments_dialog_payment_date" size="10" value="'+convert_time_from_timestamp(timestamp_now, "date")+'" onchange="payments_dialog_payment_timestamp_update(this.value);"/>';
			html+='		<input type="hidden" id="payments_dialog_payment_timestamp" value="'+timestamp_now+'" /></td>';
			html+='</tr>';
			if (payment_total != "0,00")
			{
				html+='<tr>';
				html+='	<td></td>';
				//html+='	<td><img  style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="<?php echo PATH;?>images/icons/24x24/remove.png" alt="Zahlung speichern" title="Zahlung speichern" onclick="" /></td>';
				//html+='	<td><img  style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="<?php echo PATH;?>images/icons/24x24/accept.png" alt="Zahlung speichern" title="Zahlung speichern" onclick="payment_dialog_do_payment('+orderid+', '+currency+' );" /></td>';		
					html+='	<td style="background-color:green; text-align:center; cursor:pointer;" onclick="payment_dialog_do_payment('+orderid+', \''+currency+'\' );"><strong>Zahlung speichern</strong></td>';
				html+='</tr>';
			}
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
			html+='	<td><input type="text" id="payments_dialog_payment_total" size="10"  value="0,00" disabled /> '+currency+'</td>';
			html+='</tr><tr id="payments_dialog_payment_date_row" style="display:none">';
			html+='	<td>Zahlungsdatum</td>';
			html+='	<td><input type="text" id="payments_dialog_payment_date" size="10" value="'+convert_time_from_timestamp(timestamp_now, "date")+'" onchange="payments_dialog_payment_timestamp_update(this.value);"/>';
			html+='		<input type="hidden" id="payments_dialog_payment_timestamp" value="'+timestamp_now+'" /></td>';
			html+='</tr><tr id="payments_dialog_payment_button_row" style="display:none">';
		//	html+='	<td><img  style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="<?php echo PATH;?>images/icons/24x24/remove.png" alt="Rückzahlung zurücksetzen" title="Rückzahlung zurücksetzen" onclick="payment_dialog_reset_refund('+orderid+');" /></td>';
		//	html+='	<td><img  style="margin:0px 0px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="<?php echo PATH;?>images/icons/24x24/accept.png" alt="Rückzahlung durchführen" title="Rückzahlung durchführen" onclick="payment_dialog_do_refund('+orderid+', \''+currency+'\');" /></td>';		
			html+='	<td style="background-color:red; text-align:center; cursor:pointer;" onclick="payment_dialog_reset_refund('+orderid+', \''+currency+'\');"><strong>Rückzahlung zurücksetzen</strong></td>';
			html+='	<td style="background-color:green; text-align:center; cursor:pointer;" onclick="payment_dialog_do_refund('+orderid+', \''+currency+'\');"><strong>Rückzahlung durchführen</strong></td>';
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
			if ($("#payments_dialog_paymentType").val()==4)
			{
				$("#payments_dialog_payment_total_row").hide();
			}
			else
			{
				$("#payments_dialog_payment_total_row").show();	
			}
		
		
		}
	}
	
	function payment_dialog_do_payment(orderid, currency)
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
		//ZAHLUNG IN EUR UMRECHNEN
		if (currency!="EUR")
		{
			if ($payment_total!=0)
			{
				$payment_total/=payments_data["exchangerate"];
			
				$payment_total=Math.round($payment_total * 100) / 100 ;
			}
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
					TransactionID:$("#payments_dialog_payment_txn_id").val(),
					payment_type_id:$("#payments_dialog_paymentType").val()},
				function ($data)
				{
					wait_dialog_hide();
					try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
					var $Ack = $xml.find("Ack").text();
					if ($Ack!="Success") {show_status2($data); return;}
					
					if ($xml.find("PaymentTotalEUR").text()=="")
					{
						alert("ZAHLUNG NICHT BEKANNT!");
		//>>>>TRANSACTION BEI PAYPAL ABFRAGEN
						$("#payments_dialog_payment_txn_id").focus();
						return;
					}
					if ($xml.find("Last_PaymentDeposit").text()*1<=0)
					{
						alert("Zahlung kann nicht mehr auf die Bestellung verknüpft werden!")	
						$("#payments_dialog_payment_txn_id").focus();
						return;
					}
					//alert("OK");
					//TRANSACTION IN SHOP_ORDERS EINTRAGEN
					wait_dialog_show();
					$.post("<?php echo PATH; ?>soa2/index.php", { 
						API: "shop",
						APIRequest: "OrdersPaymentStatusUpdate",
						orderid:orderid, 
						paymentTransactionID:$("#payments_dialog_payment_txn_id").val(),
						paymentState: "Created",
						paymentStateDate: $("#payments_dialog_payment_timestamp").val(),
						payments_type_id: $("#payments_dialog_paymentType").val()},
					function ($data)
					{
						wait_dialog_hide();	
						try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
						var $Ack = $xml.find("Ack").text();
						if ($Ack!="Success") {show_status2($data); return;}
						
						//PAYMENTNOTIFICATION HANDLER AUFRUFEN
						wait_dialog_show();
						$.post("<?php echo PATH; ?>soa2/index.php", { 
							API: "payments",
							APIRequest: "PaymentNotificationSet_Manual",
							mode: "PayPal",
							orderid:orderid},
						function ($data)
						{
							wait_dialog_hide();	
							try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
							var $Ack = $xml.find("Ack").text();
							if ($Ack!="Success") {show_status2($data); return;}
							
							show_status("Zahlung erfolgreich übernommen!");
							payments_dialog_load(orderid);

						});
						
					});
					
				}
			);
		}
		else if ($("#payments_dialog_paymentType").val()==5)
		{
			$.post("<?php echo PATH; ?>soa2/index.php", { 
					API: "payments", 
					APIRequest: "PaymentGet", 
					TransactionID:$("#payments_dialog_payment_txn_id").val(),
					payment_type_id:$("#payments_dialog_paymentType").val()},
				function ($data)
				{
					wait_dialog_hide();
					try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
					var $Ack = $xml.find("Ack").text();
					if ($Ack!="Success") {show_status2($data); return;}
					
					if ($xml.find("PaymentTotalEUR").text()=="")
					{
						if( $("#CreditCardTxnUnknown").length==0 )
						{
							$("body").append('<div id="CreditCardTxnUnknown"></div>');
						}
						
						$("#CreditCardTxnUnknown").html("Zahlung ist dem System nicht bekannt! Soll die Zahlung gespeichert werden");
						
						$("#CreditCardTxnUnknown").dialog
						({	buttons:
							[
								{ text: "Speichern", click: function() { 
									$.post("<?php echo PATH; ?>soa2/index.php", { 
											API: "payments", 
											APIRequest: "PaymentNotificationSet_Manual", 
											mode:"CreditCard",
											orderid:orderid,
											TransactionID: $("#payments_dialog_payment_txn_id").val(),
											payment_total:$payment_total,
											accounting_date:$("#payments_dialog_payment_timestamp").val()},
										function ($data)
										{
											try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
											var $Ack = $xml.find("Ack").text();
											if ($Ack!="Success") {show_status2($data); return;}
											$("#CreditCardTxnUnknown").dialog("close");
											show_status("Zahlung erfolgreich übernommen");
											payments_dialog_load(orderid);
										}
									); } },
								{ text: "Beenden", click: function() { $(this).dialog("close");} }

							],
							closeOnEscape: true,
							closeText:"Fenster schließen",
							modal:true,
							resizable:false,
							title:"Hinweis",
							width:400
						});		
					//	return;
						
						
					}
					else if ($xml.find("Last_PaymentDeposit").text()*1<=0)
					{
						alert("Zahlung kann nicht mehr auf die Bestellung verknüpft werden!")	
						$("#payments_dialog_payment_txn_id").focus();
						return;
					}
					else
					{
					$.post("<?php echo PATH; ?>soa2/index.php", { 
							API: "payments", 
							APIRequest: "PaymentNotificationSet_Manual", 
							mode:"CreditCard",
							orderid:orderid,
							TransactionID: $("#payments_dialog_payment_txn_id").val(),
							payment_total:$payment_total,
							accounting_date:$("#payments_dialog_payment_timestamp").val()},
						function ($data)
						{
							try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
							var $Ack = $xml.find("Ack").text();
							if ($Ack!="Success") {show_status2($data); return;}
							show_status("Zahlung erfolgreich übernommen");
							payments_dialog_load(orderid);
						});
					}
				}
			);
		}
		else if($("#payments_dialog_paymentType").val()==2)
		{
			//ÜBERGABE AN PAYMENTHANDLER
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/index.php", { 
					API: "payments", 
					APIRequest: "PaymentNotificationSet_Manual", 
					mode:"BankTransfer",
					orderid:orderid,
					payment_total:$payment_total,
					accounting_date:$("#payments_dialog_payment_timestamp").val()},
				function ($data)
				{
					try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
					var $Ack = $xml.find("Ack").text();
					if ($Ack!="Success") {show_status2($data); return;}
					wait_dialog_hide();
					show_status("Zahlung erfolgreich übernommen");
					payments_dialog_load(orderid);
					
				}
			);
		}
		else if($("#payments_dialog_paymentType").val()==3)
		{
			//ÜBERGABE AN PAYMENTHANDLER
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/index.php", { 
					API: "payments", 
					APIRequest: "PaymentNotificationSet_Manual", 
					mode:"COD",
					orderid:orderid,
					payment_total:$payment_total,
					accounting_date:$("#payments_dialog_payment_timestamp").val()},
				function ($data)
				{
					try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
					var $Ack = $xml.find("Ack").text();
					if ($Ack!="Success") {show_status2($data); return;}
					wait_dialog_hide();
					show_status("Zahlung erfolgreich übernommen");
					payments_dialog_load(orderid);
					
				}
			);
		}

		else { alert("nichts passiert");}
		
	}
	
	function payment_dialog_do_refund(orderid, currency)
	{
		//check	for paymenttype
		if ($("#payments_dialog_paymentType").val()==0)
		{
			alert("Bitte eine Zahlart auswählen!");
			$("#payments_dialog_paymentType").focus();
			return;	
		}
		if ($("#payments_dialog_payment_txn_id").val()=="")
		{
			alert("FEHLER: KEINE TRANSACTIONID GEFUNDEN!");
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
		//ZAHLUNG IN EUR UMRECHNEN
		if (currency!="EUR")
		{
			if ($payment_total!=0)
			{
				$payment_total/=payments_data["exchangerate"];
			
				$payment_total=Math.round($payment_total * 100) / 100 ;
			}
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
//>>>>>> CHECK OB RESTWERT DER ZAHLUNG DEN REFUND ABDECKT
		
		var mode ="";
		if ($("#payments_dialog_paymentType").val()==2)
		{
			mode = "BankTransfer_SendMoney";
		}
		
		if (mode !="")
		{
			//ÜBERGABE AN PAYMENTHANDLER
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/index.php", { 
					API: "payments", 
					APIRequest: "PaymentNotificationSet_Manual", 
					mode:mode,
					orderid:orderid,
					ParentTransactionID:$("#payments_dialog_payment_txn_id").val(),
					payment_total:$("#payments_dialog_payment_total").val().replace(",", ".")*1,
					accounting_date:$("#payments_dialog_payment_timestamp").val()},
				function ($data)
				{
					try { $xml = $($.parseXML($data));	} catch (err) {	show_status2(err.message); return;}
					var $Ack = $xml.find("Ack").text();
					if ($Ack!="Success") {show_status2($data); return;}
					wait_dialog_hide();
					show_status("Rückzahlung erfolgreich übernommen");
					payments_dialog_load(orderid);
					
				}
			);
		}
		else
		{
			alert("nichts passiert!");	
		}
	
	}
	
	function payment_dialog_set_refund(TxnID, currency)
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

			var html='';
			//html+='<tr id="payments_dialog_paymentType_row" style="display:none">';
			html+='	<td>Zahlungstyp</td>';
			html+='	<td><input type="text" id="payments_dialog_paymentType_show" size="20" disabled />';
			html+='		<input type="hidden" id="payments_dialog_paymentType" /></td>';

		$("#payments_dialog_paymentType_row").html(html);

		$("#payments_dialog_paymentType").val(payment_type_id);
		$("#payments_dialog_paymentType_show").val(PaymentTypes[payment_type_id]["title"]);
		$("#payments_dialog_paymentType_row").show();

		//PAYMENT TOTAL ADDIEREN
		if (currency !="EUR")
		{
			$payment_total = (payments_data["lastPaymentDeposit"][TxnID]*1*payments_data["exchangerate"])+$("#payments_dialog_payment_total").val().replace(",", ".")*1;
		}
		else
		{
			$payment_total = (payments_data["lastPaymentDeposit"][TxnID]*1)+$("#payments_dialog_payment_total").val().replace(",", ".")*1;
		}
	//	$("#payments_dialog_payment_total").val((payments_data["lastPaymentDeposit"][TxnID]*1).toFixed(2).toString().replace(".", ","));
		$("#payments_dialog_payment_total").val($payment_total.toFixed(2).toString().replace(".", ","));
		$("#payments_dialog_payment_total_row").show();
		
		$("#payments_dialog_payment_txn_id").val(TxnID);
		
		$("#payments_dialog_payment_date").val(convert_time_from_timestamp(timestamp_now, "date"));
		payments_dialog_payment_timestamp_update($("#payments_dialog_payment_date").val());
		$("#payments_dialog_payment_date_row").show();
		
		$("#payments_dialog_payment_button_row").show();
		
		
		//PRÜFE WELCHE REFUNDBUTTONS AUSGEBLENDET WERDEN MÜSSEN
		for (var $TxnID  in payments_data["payment"])
		{
			if ($TxnID != TxnID) $("#refund_button_payment_"+$TxnID).hide();
			
/*			for (var id_PN in payments_data["payment"][TxnID])
			{
				if (payments_data["payment"][TxnID][id_PN]["notification_type"]==1 && payments_data["payment"][TxnID][id_PN]["reason"]=="Completed" && payments_data["lastPaymentDeposit"][TxnID]!=0)
				{
					if (payments_data["payment"][TxnID][id_PN]["payment_type_id"]!=payment_type_id)
					{
						$("#refund_button_payment_"+TxnID).hide();
					}
				}
			}*/
		} 
		
	}
	
	function set_order_refund_type($TxnID)
	{
		
		//SET TxnID
		$("#payments_dialog_payment_txn_id").val($TxnID);
		
		var counter=0;
		for (var $id_PN in payments_data["payment"][$TxnID])
		{
			if (counter==0)
			{
				$("#payments_dialog_paymentType").val(payments_data["payment"][$TxnID][$id_PN]["payment_type_id"]);
			}
			counter++;
		}

	}

	function payment_dialog_set_refund_order(orderid, currency)
	{
	//	alert(TxnID);
		var time_now = new Date();
		var timestamp_now = Math.round(time_now.getTime()/1000);

		//hide refundbutton
		$("#refund_button_order").hide();
		//Green row
		$("#payment_dialog_order_row").css("background-color", "#dfd");

		if ($("#payments_dialog_paymentType").val()==0)
		{
			//GET PAYMENTS TYPES 
			var paymenttypes = Array();
			//paymenttypes[0]=0;
			for (var $TxnID  in payments_data["payment"])
			{
				for (var $id_PN in payments_data["payment"][$TxnID])
				{
						//paymenttypes[payments_data["payment"][$TxnID][$id_PN]["payment_type_id"]]=0;
						if (payments_data["payment"][$TxnID][$id_PN]["reason"]=="Completed")
						{
							paymenttypes[$TxnID]=payments_data["payment"][$TxnID][$id_PN]["payment_type_id"];
						}
				}
			}
			var paymentselectbox='';
			paymentselectbox+='<select id="payments_dialog_paymentType2" size="1" onchange="set_order_refund_type(this.value)">';
			paymentselectbox+='<option value="">Bitte Zahlung wählen</option>';
			for (var arrayIndex in paymenttypes)
			{
				//paymentselectbox+='<option value='+arrayIndex+'>'+PaymentTypes[arrayIndex]["title"]+'</option>';
				paymentselectbox+='<option value='+arrayIndex+'>'+PaymentTypes[paymenttypes[arrayIndex]]["title"]+' '+arrayIndex+'</option>';
			}
			paymentselectbox+='</select>';
				
				var html='';
			
				//html+='<tr id="payments_dialog_paymentType_row" style="display:none">';
				html+='	<td>Zahlungstyp</td>';
				html+='	<td>'+paymentselectbox+'</td>';
				html+='<input type="hidden" id="payments_dialog_paymentType" value=0 />';
				$("#payments_dialog_paymentType_row").html(html);
		}
		else
		{
			//var payment_type_id = orders[orderid]["PaymentTypeID"];
			//$("#payments_dialog_paymentType").val(payment_type_id);
			//$("#payments_dialog_paymentType_show").val(PaymentTypes[payment_type_id]["title"]);
		}
		$("#payments_dialog_paymentType_row").show();
		
		//PAYMENT TOTAL ADDIEREN
		if (currency !="EUR")
		{
			$payment_total = (payments_data["lastOrderDeposit"]*1)+$("#payments_dialog_payment_total").val().replace(",", ".")*1;
		}
		else
		{
			$payment_total = (payments_data["lastOrderDepositEUR"]*1)+$("#payments_dialog_payment_total").val().replace(",", ".")*1;
			
		}
		
		//$("#payments_dialog_payment_total").val((payments_data["lastOrderDepositEUR"]*1).toFixed(2).toString().replace(".", ","));
		$("#payments_dialog_payment_total").val($payment_total.toFixed(2).toString().replace(".", ","));
		$("#payments_dialog_payment_total_row").show();
		
		$("#payments_dialog_payment_date").val(convert_time_from_timestamp(timestamp_now, "date"));
		payments_dialog_payment_timestamp_update($("#payments_dialog_payment_date").val());
		$("#payments_dialog_payment_date_row").show();
		
		$("#payments_dialog_payment_button_row").show();
	}

	function payment_dialog_reset_refund(orderid, currency)
	{
		$("#payment_dialog_order_row").css("background-color", "#eee");
		$(".payment_dialog_payment_row").css("background-color", "#fff");
		payments_dialog_draw_actions(orderid, "refund", currency);
	}
	
	function payment_dialog_do_action(orderid)
	{
		payments_dialog_load(orderid);
	}