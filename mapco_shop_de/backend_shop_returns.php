<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	

?>

<script type="text/javascript">
	var returnsViewFilterState="open";
	var returnsViewFilterType="all";
	var returnsViewFilterDateFrom="";
	var returnsViewFilterDateTo="";
	var returnsViewFilterReason="all";
	var returnsViewFilterPlatform="all";
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

			//_________________________________________________________________________		



					$(function()
					{
						$( "#ReturnAdd_date_order" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					
					$(function()
					{
						$( "#ReturnAdd_date_announced" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
	
					$(function()
					{
						$( "#ReturnAdd_date_return" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					
					$(function()
					{
						$( "#ReturnAdd_date_refund" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					
					$(function()
					{
						$( "#ReturnAdd_date_refund_reshipment" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
				/*	
					$(function()
					{
						$( "#ReturnAdd_date_r_IDIMS" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
				*/	
					$(function()
					{
						$( "#ReturnAdd_date_demandEbayClosing1" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});

					$(function()
					{
						$( "#ReturnAdd_date_demandEbayClosing2" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					
					$(function()
					{
						$( "#ReturnAdd_date_exchange_sent" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					
			//_________________________________________________________________________________________________________

					$(function()
					{
						$( "#ReturnUpdate_date_order" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});

					$(function()
					{
						$( "#ReturnUpdate_date_announced" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
	
					$(function()
					{
						$( "#ReturnUpdate_date_return" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					
					$(function()
					{
						$( "#ReturnUpdate_date_refund" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					
					$(function()
					{
						$( "#ReturnUpdate_date_refund_reshipment" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
			/*		
					$(function()
					{
						$( "#ReturnUpdate_date_r_IDIMS" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
			*/		
					$(function()
					{
						$( "#ReturnUpdate_date_demandEbayClosing1" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});

					$(function()
					{
						$( "#ReturnUpdate_date_demandEbayClosing1" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
					
					$(function()
					{
						$( "#ReturnUpdate_date_exchange_sent" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});

		// DATEPICKERS END ---------------------------------------------------------------------------------------------------

		function view()  
		{
			//var returnsViewFilterState=$("#returns_ViewFilterState").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "ReturnsView", returnsViewFilterState:returnsViewFilterState, returnsViewFilterType:returnsViewFilterType, returnsViewFilterDateFrom:returnsViewFilterDateFrom, returnsViewFilterDateTo:returnsViewFilterDateTo, returnsViewFilterReason:returnsViewFilterReason, returnsViewFilterPlatform:returnsViewFilterPlatform},
				function (data)
				{
					$("#view").html(data);
					wait_dialog_hide();
				}
			);
		}
		
		function clear_input()
		{
			$("#ReturnAdd_platform").val("");
			$("#ReturnAdd_rAction").val("");
			$("#ReturnAdd_buyerName").val("");
			$("#ReturnAdd_userid").val("");
			$("#ReturnAdd_invoiceID").val("");
			$("#ReturnAdd_MPU").val("");
		//	$("#ReturnAdd_article_group").val("");
			$("#ReturnAdd_date_order").val("");
			$("#ReturnAdd_rReason").val("");
			$("#ReturnAdd_rReason_detail").val("");
			$("#ReturnAdd_exchange_MPU").val("");
			$("#ReturnAdd_exchange_quantity").val("");
			$("#ReturnAdd_date_exchange_sent").val("");
			var TimeToday=new Date();
			//var PaymentTime= new Date($(this).find("PaymentTime").text()*1000);
			var DateToday=TimeToday.getDate();
			if (String(TimeToday.getMonth()).length==1)
			{
				DateToday+='.0'+TimeToday.getMonth()+'.'+TimeToday.getFullYear();
			}
			else 
			{
				DateToday+='.'+TimeToday.getMonth()+'.'+TimeToday.getFullYear();
			}
			$("#ReturnAdd_date_announced").val(DateToday);
			$("#ReturnAdd_date_return").val("");
			$("#ReturnAdd_date_refund").val("");
			$("#ReturnAdd_refund").val("");
			$("#ReturnAdd_date_refund_reshipment").val("");
			$("#ReturnAdd_refund_reshipment").val("");
			//$("#ReturnAdd_date_r_IDIMS").val("");
			$("#ReturnAdd_date_demandEbayClosing1").val("");
			$("#ReturnAdd_date_demandEbayClosing2").val("");
			$("#ReturnAdd_ebayFeeRefundOK").attr("checked", false);
			$("#ReturnAdd_state").val("open");
		}

		
		
		function ReturnAdd()  
		{
			
			
			$("#ReturnAddDialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() {ReturnAddSave();} },
					{ text: "Abbrechen", click: function() {$(this).dialog("close"); clear_input();} }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Rückgabe / Umtausch neu",
				width:1000
			});		
		}

		function ReturnAddSave()  
		{
			//$("#ReturnAddDialog").button("disable");
			
			var platform=$("#ReturnAdd_platform").val();
			var rAction=$("#ReturnAdd_rAction").val();
			var userid=$("#ReturnAdd_userid").val();
			var buyerName=$("#ReturnAdd_buyerName").val();
			var transactionID=$("#ReturnAdd_transactionID").val();
			var invoiceID=$("#ReturnAdd_invoiceID").val();
			var MPU=$("#ReturnAdd_MPU").val();
		//	var article_group=$("#ReturnAdd_article_group").val();
			var quantity=$("#ReturnAdd_quantity").val();
			var date_order=$("#ReturnAdd_date_order").val();
			var rReason=$("#ReturnAdd_rReason").val();
			var rReason_detail=$("#ReturnAdd_rReason_detail").val();
			
			var exchange_MPU=$("#ReturnAdd_exchange_MPU").val();
			var exchange_quantity=$("#ReturnAdd_exchange_quantity").val();
			var date_exchange_sent=$("#ReturnAdd_date_exchange_sent").val();
			
			var date_return=$("#ReturnAdd_date_return").val();
			var date_announced=$("#ReturnAdd_date_announced").val();
			var date_refund=$("#ReturnAdd_date_refund").val();
			var refund=$("#ReturnAdd_refund").val();
			var date_refund_reshipment=$("#ReturnAdd_date_refund_reshipment").val();
			var refund_reshipment=$("#ReturnAdd_refund_reshipment").val();
		//	var date_r_IDIMS=$("#ReturnAdd_date_r_IDIMS").val();
			var date_demandEbayClosing1=$("#ReturnAdd_date_demandEbayClosing1").val();
			var date_demandEbayClosing2=$("#ReturnAdd_date_demandEbayClosing2").val();
			if ($("#ReturnAdd_ebayFeeRefundOK").is(":checked"))
				{var ebayFeeRefundOK="1";}
			else {var ebayFeeRefundOK="0";}
			
			var state=$("#ReturnAdd_state").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "ReturnsAdd", 
					platform:platform, rAction:rAction, userid:userid, buyerName:buyerName, transactionID:transactionID, invoiceID:invoiceID, MPU:MPU, quantity:quantity, date_order:date_order, rReason:rReason, rReason_detail:rReason_detail, exchange_MPU:exchange_MPU, exchange_quantity:exchange_quantity, date_exchange_sent:date_exchange_sent, date_return:date_return, date_announced:date_announced, date_refund:date_refund, refund:refund, refund_reshipment:refund_reshipment, date_refund_reshipment:date_refund_reshipment, date_demandEbayClosing1:date_demandEbayClosing1, date_demandEbayClosing2:date_demandEbayClosing2, ebayFeeRefundOK:ebayFeeRefundOK, state:state},
				function(data)
				{
					if (data!="") show_status2(data);
					else {
						$("#ReturnAddDialog").dialog("close");
						view();
						clear_input();
						show_status("Daten wurden erfolgreich gespeichert!");
	
					}
					wait_dialog_hide();
				}
			);
			
		}
		
		

		function ReturnUpdate(field)  
		{
		
			$("#ReturnUpdate_platform").val(field["platform"]);
			$("#ReturnUpdate_rAction").val(field["rAction"]);
			$("#ReturnUpdate_userid").val(field["userid"]);
			$("#ReturnUpdate_buyerName").val(field["buyerName"]);
			$("#ReturnUpdate_transactionID").val(field["transactionID"]);
			$("#ReturnUpdate_invoiceID").val(field["invoiceID"]);
			$("#ReturnUpdate_MPU").val(field["MPU"]);
			//$("#ReturnUpdate_article_group").val(field["article_group"]);
			$("#ReturnUpdate_quantity").val(field["quantity"]);
			$("#ReturnUpdate_date_order").val(field["date_order"]);
			$("#ReturnUpdate_rReason").val(field["rReason"]);
			$("#ReturnUpdate_rReason_detail").val(field["rReason_detail"]);
			
			$("#ReturnUpdate_exchange_MPU").val(field["exchange_MPU"]);
			$("#ReturnUpdate_exchange_quantity").val(field["exchange_quantity"]);
			$("#ReturnUpdate_date_exchange_sent").val(field["date_exchange_sent"]);
			
			$("#ReturnUpdate_date_return").val(field["date_return"]);
			$("#ReturnUpdate_date_announced").val(field["date_announced"]);
			$("#ReturnUpdate_date_refund").val(field["date_refund"]);
			$("#ReturnUpdate_refund").val(field["refund"]);
			$("#ReturnUpdate_date_refund_reshipment").val(field["date_refund_reshipment"]);
			$("#ReturnUpdate_refund_reshipment").val(field["refund_reshipment"]);
			//$("#ReturnUpdate_date_r_IDIMS").val(field["date_r_IDIMS"]);
			$("#ReturnUpdate_date_demandEbayClosing1").val(field["date_demandEbayClosing1"]);
			$("#ReturnUpdate_date_demandEbayClosing2").val(field["date_demandEbayClosing2"]);
			if (field["ebayFeeRefundOK"]==1) {$("#ReturnUpdate_ebayFeeRefundOK").attr("checked", true);}
			if (field["ebayFeeRefundOK"]==0) {$("#ReturnUpdate_ebayFeeRefundOK").attr("checked", false);}
			
			$("#ReturnUpdate_state").val(field["state"]);
			
			set_update_relations_rAction();
			set_update_relations_platform();				
			set_update_relations_rReason();
	
			$("#ReturnUpdateDialog").dialog
			({	buttons:
				[
					{ text: "Änderungen Speichern", click: function() { ReturnUpdateSave(field["id"]); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Rückgabe / Umtausch bearbeiten",
				width:900
			});		
		}



		function ReturnUpdateSave(id)  
		{
			
			var platform=$("#ReturnUpdate_platform").val();
			var rAction=$("#ReturnUpdate_rAction").val();
			var userid=$("#ReturnUpdate_userid").val();
			var buyerName=$("#ReturnUpdate_buyerName").val();
			var transactionID=$("#ReturnUpdate_transactionID").val();
			var invoiceID=$("#ReturnUpdate_invoiceID").val();
			var MPU=$("#ReturnUpdate_MPU").val();
		//	var article_group=$("#ReturnUpdate_article_group").val();
			var quantity=$("#ReturnUpdate_quantity").val();
			var date_order=$("#ReturnUpdate_date_order").val();
			var rReason=$("#ReturnUpdate_rReason").val();
			var rReason_detail=$("#ReturnUpdate_rReason_detail").val();
			
			var exchange_MPU=$("#ReturnUpdate_exchange_MPU").val();
			var exchange_quantity=$("#ReturnUpdate_exchange_quantity").val();
			var date_exchange_sent=$("#ReturnUpdate_date_exchange_sent").val();
			
			var date_return=$("#ReturnUpdate_date_return").val();
			var date_announced=$("#ReturnUpdate_date_announced").val();
			var date_refund=$("#ReturnUpdate_date_refund").val();
			var refund=$("#ReturnUpdate_refund").val();
			var date_refund_reshipment=$("#ReturnUpdate_date_refund_reshipment").val();
			var refund_reshipment=$("#ReturnUpdate_refund_reshipment").val();
		//	var date_r_IDIMS=$("#ReturnUpdate_date_r_IDIMS").val();
			var date_demandEbayClosing1=$("#ReturnUpdate_date_demandEbayClosing1").val();
			var date_demandEbayClosing2=$("#ReturnUpdate_date_demandEbayClosing2").val();
			if ($("#ReturnUpdate_ebayFeeRefundOK").is(":checked")) {
				var ebayFeeRefundOK=1;
			}
			else {
				var ebayFeeRefundOK=0;
			}
			
			var state=$("#ReturnUpdate_state").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "ReturnsUpdate", 
					id:id, platform:platform, rAction:rAction, userid:userid, buyerName:buyerName, transactionID:transactionID, invoiceID:invoiceID, MPU:MPU, quantity:quantity, date_order:date_order, rReason:rReason, rReason_detail:rReason_detail, exchange_MPU:exchange_MPU, exchange_quantity:exchange_quantity, date_exchange_sent:date_exchange_sent, date_return:date_return, date_announced:date_announced, date_refund:date_refund, refund:refund, refund_reshipment:refund_reshipment, date_refund_reshipment:date_refund_reshipment, date_demandEbayClosing1:date_demandEbayClosing1, date_demandEbayClosing2:date_demandEbayClosing2, ebayFeeRefundOK:ebayFeeRefundOK, state:state},
				function(data)
				{
					if (data!="") show_status2(data);
					else
					{
//						$("#ReturnUpdateDialog").remove();
						show_status("Die Änderungen wurden erfolgreich gespeichert!");
						$("#ReturnUpdateDialog").dialog("close");
						view();
//						clear_input();
						wait_dialog_hide();
					}
				}
			);
			
		}

	// RELATIONS DIALOG ADD ----------------------------------------------------------------------------
		function set_add_relations_platform(){
			
			if ($("#ReturnAdd_platform").val()=="ooe" || $("#ReturnAdd_platform").val()=="amazon") 
			{
				$("#t_ReturnAdd_userid1").hide();
				$("#t_ReturnAdd_userid2").hide();				
				
			}
			else
			{
				$("#t_ReturnAdd_userid1").show();
				$("#t_ReturnAdd_userid2").show();				
			}
			
			if ( ($("#ReturnAdd_platform").val()=="ebay_AP" || $("#ReturnAdd_platform").val()=="ebay_MAPCO" ) && ($("#ReturnAdd_rAction").val()=="return") ) 
			{
				$("#ReturnAddDialog_demandEbayClosing").show();
			}
			else 
			{ 
				$("#ReturnAddDialog_demandEbayClosing").hide();
			}
			//DATEN AUS EBAY ZIEHEN
			if ($("#ReturnAdd_platform").val()=="ebay_AP" || $("#ReturnAdd_platform").val()=="ebay_MAPCO" )
			{
				$("#ReturnAdd_GetEbayData").show();
			}
			else 
			{
				$("#ReturnAdd_GetEbayData").hide();
			}

		}
		
		function set_add_relations_rAction(){
			
			if ($("#ReturnAdd_rAction").val()=="return" || $("#ReturnAdd_rAction").val()=="") 
			{
				$("#ReturnAddDialog_exchange").hide();
				
			}
			else
			{
				$("#ReturnAddDialog_exchange").show();
			}
			
			if ( ($("#ReturnAdd_platform").val()=="ebay_AP" || $("#ReturnAdd_platform").val()=="ebay_MAPCO" ) && ($("#ReturnAdd_rAction").val()=="return") ) 
			{
				$("#ReturnAddDialog_demandEbayClosing").show();
			}
			else 
			{ 
				$("#ReturnAddDialog_demandEbayClosing").hide();
			}

		}
			
		function set_add_relations_rReason(){
				
			if ($("#ReturnAdd_rReason").val()=="100") 
			{
				$("#t_ReturnAdd_rReason_detail1").css("background-color", "#ffdddd");
				$("#t_ReturnAdd_rReason_detail2").css("background-color", "#ffdddd");
				
			}
			else
			{
				$("#t_ReturnAdd_rReason_detail1").css("background-color", "#eeffee");
				$("#t_ReturnAdd_rReason_detail2").css("background-color", "#eeffee");
			}
		}
		
		
		function set_add_relations_state() {
			
			msg_parts = new Array();
			msg = new String();
			
		//PRÜFUNG BEI FALL RÜCKGABE	
			if ($("#ReturnAdd_rAction").val()=="return")
			{
				if ($("#ReturnAdd_date_announced").val()=="") { msg_parts[msg_parts.length]="Datum Fall geöffnet";}
				
				if ($("#ReturnAdd_date_return").val()=="") { msg_parts[msg_parts.length]="Datum Rücksendung erhalten";}
				
				if ($("#ReturnAdd_date_refund").val()=="") { msg_parts[msg_parts.length]="Datum Erstattung";}
				
			//	if ($("#ReturnAdd_date_r_IDIMS").val()=="") { msg_parts[msg_parts.length]="Datum Gutschrift IDIMS";}
				
				
				if (msg_parts.length>0 && $("#ReturnAdd_state").val()=="closed") 
				{
					msg="Der Fall kann noch nicht als geschlossen markiert werden! Bitte die folgenden Felder ausfüllen: \n";
					for (var i in msg_parts)	{ msg+=msg_parts[i]+"\n"; }
					alert (msg);
					$("#ReturnAdd_state").val("open");

				}
				

			}

		//PRÜFUNG BEI FALL Umtausch
			if ($("#ReturnAdd_rAction").val()=="exchange")
			{
				
				if ($("#ReturnAdd_date_announced").val()=="") { msg_parts[msg_parts.length]="Datum Fall geöffnet";}
				
				if ($("#ReturnAdd_date_return").val()=="") { msg_parts[msg_parts.length]="Datum Rücksendung erhalten";}
				
				if ($("#ReturnAdd_date_exchange_sent").val()=="") { msg_parts[msg_parts.length]="Umtausch versandt";}
				
				//if ($("#ReturnAdd_date_r_IDIMS").val()=="") { msg_parts[msg_parts.length]="Datum Gutschrift IDIMS";}
				
				
				if (msg_parts.length>0 && $("#ReturnAdd_state").val()=="closed") 
				{
					msg="Der Fall kann noch nicht als geschlossen markiert werden! Bitte die folgenden Felder ausfüllen: \n";
					for (var i in msg_parts)	{ msg+=msg_parts[i]+"\n"; }
					alert (msg);
					$("#ReturnAdd_state").val("open");

				}
				
			}
			
		}

		
	//RELATIONS DIALOG UPDATE
		function set_update_relations_platform(){
			
			if ($("#ReturnUpdate_platform").val()=="ooe" || $("#ReturnUpdate_platform").val()=="amazon" ) 
			{
				$("#t_ReturnUpdate_userid1").hide();
				$("#t_ReturnUpdate_userid2").hide();				
				
			}
			else
			{
				$("#t_ReturnUpdate_userid1").show();
				$("#t_ReturnUpdate_userid2").show();				
			}
			
			if ( ($("#ReturnUpdate_platform").val()=="ebay_AP" || $("#ReturnUpdate_platform").val()=="ebay_MAPCO" ) && ($("#ReturnUpdate_rAction").val()=="return") ) 
			{
				$("#ReturnUpdateDialog_demandEbayClosing").show();
			}
			else 
			{ 
				$("#ReturnUpdateDialog_demandEbayClosing").hide();
			}
			
		}
		
		function set_update_relations_rAction(){
		
			if ($("#ReturnUpdate_rAction").val()=="return" || $("#ReturnUpadte_rAction").val()=="") 
			{
				$("#ReturnUpdateDialog_exchange").hide();
			}
			else
			{
				$("#ReturnUpdateDialog_exchange").show();
			
			}
			
			if ( ($("#ReturnUpdate_platform").val()=="ebay_AP" || $("#ReturnUpdate_platform").val()=="ebay_MAPCO" ) && ($("#ReturnUpdate_rAction").val()=="return") ) 
			{
				$("#ReturnUpdateDialog_demandEbayClosing").show();
			}
			else 
			{ 
				$("#ReturnUpdateDialog_demandEbayClosing").hide();
			}

			
		}
			
		function set_update_relations_rReason(){
				
			if ($("#ReturnUpdate_rReason").val()=="100") 
			{
				$("#t_ReturnUpdate_rReason_detail1").css("background-color", "#ffdddd");
				$("#t_ReturnUpdate_rReason_detail2").css("background-color", "#ffdddd");
				
			}
			else
			{
				$("#t_ReturnUpdate_rReason_detail1").css("background-color", "#eeffee");
				$("#t_ReturnUpdate_rReason_detail2").css("background-color", "#eeffee");			}
		}

//PRÜFUNG OB DER FALL GESCHLOSSEN WERDEN DARF
		function set_update_relations_state() {
			
			msg_parts = new Array();
			msg = new String();
			
		//PRÜFUNG BEI FALL RÜCKGABE	
			if ($("#ReturnUpdate_rAction").val()=="return")
			{
				if ($("#ReturnUpdate_date_announced").val()=="") { msg_parts[msg_parts.length]="Datum Fall geöffnet";}
				
				if ($("#ReturnUpdate_date_return").val()=="") { msg_parts[msg_parts.length]="Datum Rücksendung erhalten";}
				
				if ($("#ReturnUpdate_date_refund").val()=="") { msg_parts[msg_parts.length]="Datum Erstattung";}
				
			//	if ($("#ReturnUpdate_date_r_IDIMS").val()=="") { msg_parts[msg_parts.length]="Datum Gutschrift IDIMS";}
				
				
				if (msg_parts.length>0 && $("#ReturnUpdate_state").val()=="closed") 
				{
					msg="Der Fall kann noch nicht als geschlossen markiert werden! Bitte die folgenden Felder ausfüllen: \n";
					for (var i in msg_parts)	{ msg+=msg_parts[i]+"\n"; }
					alert (msg);
					$("#ReturnUpdate_state").val("open");
					
				}
				

			}

		//PRÜFUNG BEI FALL Umtausch
			if ($("#ReturnUpdate_rAction").val()=="exchange")
			{
				
				if ($("#ReturnUpdate_date_announced").val()=="") { msg_parts[msg_parts.length]="Datum Fall geöffnet";}
				
				if ($("#ReturnUpdate_date_return").val()=="") { msg_parts[msg_parts.length]="Datum Rücksendung erhalten";}
				
				if ($("#ReturnUpdate_date_exchange_sent").val()=="") { msg_parts[msg_parts.length]="Umtausch versandt";}
				
			//	if ($("#ReturnUpdate_date_r_IDIMS").val()=="") { msg_parts[msg_parts.length]="Datum Gutschrift IDIMS";}
				
				
				if (msg_parts.length>0 && $("#ReturnUpdate_state").val()=="closed") 
				{
					msg="Der Fall kann noch nicht als geschlossen markiert werden! Bitte die folgenden Felder ausfüllen: \n";
					for (var i in msg_parts)	{ msg+=msg_parts[i]+"\n"; }
					alert (msg);
					$("#ReturnUpdate_state").val("open");
					
				}
				

			}
		}
	/*	
		function set_ReturnUpdate_date_r_IDIMS()
		{
			$("#ReturnUpdate_date_r_IDIMS").val($("#ReturnAdd_date_refund").val());
		}
	*/	
		function setViewFilterState()
		{
			returnsViewFilterState=$("#returns_ViewFilterState").val();
		}



		function getEbayData_Dialog()
		{
			
			$("#ReturnAddGetEbayDataDialog").dialog
			({	buttons:
				[
					{ text: "Daten übernehmen", click: function() { 

						var TransactionIDs = [];
 					    $('#transaction_select :checked').each(function() {
   					   		TransactionIDs.push($(this).val());
   						});
						setEbayData_Dialog(TransactionIDs);
						$(this).dialog("close");						

					 } },
					{ text: "Abbrechen", click: function() {$(this).dialog("close"); clear_input();} }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Rückgabe / Umtausch neu",
				width:1000
			});		
		}

		function getEbayData()
		{
			if ($("#ReturnAdd_platform").val()=="ebay_MAPCO") var account_id=1;
			if ($("#ReturnAdd_platform").val()=="ebay_AP") var account_id=2;
			var user_id=$("#ReturnAddGetEbayDataDialog_user_id").val();

			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API: "shop", Action: "ReturnsGetEbayData", account_id:account_id, user_id:user_id},
				function(data)
				{
					$("#ReturnsEbayTransactions").html(data);
					wait_dialog_hide();
					
				}
			);
		}
		
		function setEbayData_Dialog(TransactionIDs)
		{
			for (i=0; i<TransactionIDs.length; i++) {
				$("#ReturnAdd_date_order").val($("#transaction_select_OrderDate_"+TransactionIDs[i]).html());
				$("#ReturnAdd_userid").val($("#transaction_select_OrderBuyerID_"+TransactionIDs[i]).html());
				$("#ReturnAdd_buyerName").val($("#transaction_select_OrderBuyerName_"+TransactionIDs[i]).html());
				//$("#ReturnAdd_article_group").val($("#transaction_select_OrderArtGroup_"+TransactionIDs[i]).val());
				$("#ReturnAdd_quantity").val($("#transaction_select_OrderQty_"+TransactionIDs[i]).html());
			}

			var tmp="";
			for (i=0; i<TransactionIDs.length; i++) {
				if (i>0) {tmp+=","+TransactionIDs[i];} else {tmp=TransactionIDs[i];}
			}
			$("#ReturnAdd_transactionID").val(tmp);
			var tmp="";
			for (i=0; i<TransactionIDs.length; i++) {
				if (i>0) {tmp+=","+$("#transaction_select_OrderMPN_"+TransactionIDs[i]).html();} else {tmp=$("#transaction_select_OrderMPN_"+TransactionIDs[i]).html();}
			}
			$("#ReturnAdd_MPU").val(tmp);

		}
		
	function key_getEbayData()
	{
		if (event.keyCode == 13)
		{
			getEbayData();
		}
	}

</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Rückgaben';
	echo '</p>';
	echo '<h1>Rückgaben</h1>';
	
	//TABS
	echo '<a class="tab" href="javascript: $(\'#view\').show(); $(\'#view_stats\').hide();">Rückgaben</a>';
	echo '<a class="tab" href="javascript: $(\'#view\').hide(); $(\'#view_stats\').show();">Statistiken</a>';
	echo '<br style="clear:both;" />';
	
	//VIEW
	echo '<div id="view" style="margin-top:0px; border:1px solid #dddddd; padding:5px;">';
	echo '</div>';
	
	//VIEW STATS
	echo '<div id="view_stats" style="margin-top:0px; border:1px solid #dddddd; padding:5px; display:none;">';
	echo '<h2>Statistiken</h2>';
	$menuitem2artgr=array();
	$results=q("SELECT * FROM shop_menuitems_artgr;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$menuitem2artgr[$row["menuitem_id"]]=$row["artgr"];
	}
	$item=array();
	$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		@$artnr2artgr[$row["MPN"]]=$menuitem2artgr[$row["menuitem_id"]];
	}

	$artnr=array();
	$i=0;
	$results=q("SELECT * FROM shop_returns;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( $row["MPU"]!="" )
		{
			if ( !isset($artnr2i[$row["MPU"]]) )
			{
				if ( isset($artnr2artgr[$row["MPU"]]) )
				{
					$i++;
					$artnr2i[$row["MPU"]]=$i;
					$artnr[$i]=$row["MPU"];
					$artgr[$i]=$artnr2artgr[$row["MPU"]];
					$returns[$i]=1;
				}
			}
			else
			{
				$returns[$artnr2i[$row["MPU"]]]++;
			}
		}
	}
	
	//show return stats by artnr
	array_multisort($returns, SORT_DESC, $artnr, $artgr);
	echo '<table class="hover" style="float:left;">';
	echo '	<tr>';
	echo '		<th>Nr.</th>';
	echo '		<th>Artikelnummer</th>';
	echo '		<th>Artikelgruppe</th>';
	echo '		<th>Rückgaben</th>';
	echo '	</tr>';
	for($i=0; $i<sizeof($returns); $i++)
	{
		echo '<tr>';
		echo '	<td>'.($i+1).'</td>';
		echo '	<td>'.$artnr[$i].'</td>';
		echo '	<td>'.$artgr[$i].'</td>';
		echo '	<td>'.$returns[$i].'</td>';
		echo '</tr>';
		if ( !isset($returns_artgr[$artgr[$i]]) ) $returns_artgr[$artgr[$i]]=1;
		else $returns_artgr[$artgr[$i]]++;
	}
	echo '</table>';
	//show return stats by artgr
	$artgr=array_keys($returns_artgr);
	array_multisort($returns_artgr, SORT_DESC, $artgr);
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>Nr.</th>';
	echo '		<th>Artikelgruppe</th>';
	echo '		<th>Rückgaben</th>';
	echo '	</tr>';
	for($i=0; $i<sizeof($returns_artgr); $i++)
	{
		echo '<tr>';
		echo '	<td>'.($i+1).'</td>';
		echo '	<td>'.$artgr[$i].'</td>';
		echo '	<td>'.$returns_artgr[$i].'</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
	
	//VIEW
	echo '<script type="text/javascript">view();</script>';
	
	//RETURN ADD DIALOG    
	echo '<div id="ReturnAddDialog" style="display:none;">';
	echo '<fieldset style="background-color:#ffdddd">';
	echo '<table>';
	
	echo '	<tr>';
	echo '		<td>Plattform</td>';
	echo '		<td><select name="platform" size="1" id="ReturnAdd_platform" onchange="set_add_relations_platform()"/>';
	echo '			<option value="">Bitte Verkaufsplattform wählen</option>';
		$results=q("SELECT * FROM shop_returns_platform;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) ) {
			echo '			<option value='.$row["ID"].'>'.$row["platform"].'</option>';
		}
	echo ' 		</select>';
	echo '<img id="ReturnAdd_GetEbayData" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right; display:none;" src="images/icons/16x16/rightarrow.png" alt="Daten aus Ebayverkäufen ziehen" title="Daten aus Ebayverkäufen ziehen" onclick="getEbayData_Dialog();">';
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
	

	//RETURN UPDATE DIALOG    
	echo '<div id="ReturnUpdateDialog" style="display:none;">';
	echo '<input type="hidden" name="id" id="ReturnUpdate_id">';
	echo '<fieldset style="background-color:#ffdddd">';
	echo '<table>';
	
	echo '	<tr>';
	echo '		<td>Plattform</td>';
	echo '		<td><select name="platform" size="1" id="ReturnUpdate_platform" onchange="set_update_relations_platform()"/>';
	echo '			<option value="">Bitte Verkaufsplattform wählen</option>';
		$results=q("SELECT * FROM shop_returns_platform;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) ) {
			echo '			<option value='.$row["ID"].'>'.$row["platform"].'</option>';
		}
	echo ' 		</select></td>';
	echo '		<td>Vorgang</td>';
	echo '		<td><select name="rAction" size="1" id="ReturnUpdate_rAction" onchange="set_update_relations_rAction()"/>';
	echo '			<option value="">Bitte Vorgangsart wählen</option>';
		$results=q("SELECT * FROM shop_returns_rAction;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) ) {
			echo '			<option value='.$row["ID"].'>'.$row["rAction"].'</option>';
		}
	echo '		</select></td>';
	echo '	</tr>';
	
	echo '	<tr>';
	echo '		<td>Käufername</td>';
	echo '		<td><input type="text" name="buyerName" size="35" id="ReturnUpdate_buyerName")/></td>';
	echo '		<td id="t_ReturnUpdate_userid1">Käufer ID</td>';
	echo '		<td id="t_ReturnUpdate_userid2"><input type="text" name="userid" size="20" id="ReturnUpdate_userid" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td style="background-color:#eeffee">Rechnungsnummer</td>';
	echo '		<td style="background-color:#eeffee"><input type="text" name="invoiceID" size="20" id="ReturnUpdate_invoiceID"/></td>';
//	echo '		<td>Transaction ID</td>';
	echo '		<input type="hidden" name="transactionID" id="ReturnUpdate_transactionID" />';
//	echo '		<td><input type="hidden" name="transactionID" size="20" id="ReturnUpdate_transactionID" disabled="disabled" /></td>';
	echo '		<td>Artikel</td>';
	echo '		<td><input type="text" name="MPU" size="20" id="ReturnUpdate_MPU" /></td>';
//	echo '		<td>Artikelgruppe</td>';
//	echo '		<td><input type="text" name="article_group" size="20" id="ReturnUpdate_article_group" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Stückzahl</td>';
	echo '		<td><input type="text" name="quantity" size="2" id="ReturnUpdate_quantity" /></td>';
	echo '		<td>Kaufdatum</td>';
	echo '		<td><input type="text" name="date_order"  style="cursor:pointer;" size="10" id="ReturnUpdate_date_order" /></td>';
	echo '	</tr>';
	echo '  <tr>';
	echo '		<td>Rückgabegrund</td>';
	echo '		<td><select name="rReason" size="1" id="ReturnUpdate_rReason" onchange="set_update_relations_rReason()" />';
	echo '			<option value="">Bitte Rückgabe-/Umtauschgrund wählen</option>';
		$results=q("SELECT * FROM shop_returns_rReason;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) ) {
			echo '			<option value='.$row["ID"].' title='.$row["rDescription"].'>'.$row["rReason"].'</option>';
		}
	echo '		</select></td>';
	echo '		<td id="t_ReturnUpdate_rReason_detail1" style="background-color:#eeffee">Rückgabenotizen</td>';
	echo '		<td id="t_ReturnUpdate_rReason_detail2" style="background-color:#eeffee"><textarea name="rReason_detail" cols="42" rows="5" id="ReturnUpdate_rReason_detail"></textarea></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</fieldset>';
	
	echo '<div id="ReturnUpdateDialog_exchange">';
	echo '	<table>';
	echo '  <tr>';
	echo '		<td>Umtauschartikel</td>';
	echo '		<td><input type="text" name="exchange_MPU" size="20" id="ReturnUpdate_exchange_MPU" /></td>';
	echo '		<td>Stückzahl</td>';	
	echo '		<td><input type="text" name="exchange_quantity" size="2" id="ReturnUpdate_exchange_quantity" /></td>';
	echo '		<td>Umtausch versandt am</td>';
	echo '		<td><input type="text" name="date_exchange_sent"  style="cursor:pointer;" size="10" id="ReturnUpdate_date_exchange_sent" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	echo '<table>';
	echo '	<tr>';
	echo '		<td>Datum Fall geöffnet</td>';
	echo '		<td><input type="text" name="date_announced" size="10" style="cursor:pointer;" id="ReturnUpdate_date_announced" /></td>';
	echo '		<td>Datum Rücksendung erhalten</td>';
	echo '		<td><input type="text" name="date_return"  size="10" style="cursor:pointer;" id="ReturnUpdate_date_return" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datum Erstattung</td>';
	echo '		<td><input type="text" name="date_refund"  size="10" style="cursor:pointer;" id="ReturnUpdate_date_refund" onchange=\'$("#ReturnUpdate_date_refund_reshipment").val($(this).val());\'/></td>';
	echo '		<td>Erstattungssumme</td>';
	echo '		<td><input type="text" name="refund" size="6" id="ReturnUpdate_refund" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datum Erstattung Rücksendekosten am</td>';
	echo '		<td><input type="text" name="date_refund_reshipment"  size="10" style="cursor:pointer;" id="ReturnUpdate_date_refund_reshipment" /></td>';
	echo '		<td>Erstattungssumme</td>';
	echo '		<td><input type="text" name="refund_reshipment" size="6" id="ReturnUpdate_refund_reshipment" /></td>';
	echo '	</tr>';
	echo '	<tr>';
//	echo '		<td>Datum Gutschrift IDIMS</td>';
//	echo '		<td><input type="text" name="date_r_IDIMS"  size="10" style="cursor:pointer;" id="ReturnUpdate_date_r_IDIMS" /></td>';
	echo '		<td>Bearbeitungstatus Rückgabe / Umtausch</td>';
	echo '		<td><select name="state" size="1" id="ReturnUpdate_state" onchange="set_update_relations_state()" />';
			$results=q("SELECT * FROM shop_returns_state;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) ) {
			if ($row["ID"]=="open") {echo '<option value='.$row["ID"].' selected="selected">'.$row["state"].'</option>';}
			else {echo '<option value='.$row["ID"].'>'.$row["state"].'</option>';}
		}
	echo '		</select></td>';
	echo '	</tr>';	
	echo '</table>';
	
	echo '<div id="ReturnUpdateDialog_demandEbayClosing" style="display:none;">';
	echo '<fieldset>';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Ebay Rückg. angef. 1x</td>';
	echo '		<td><input type="text" name="date_demandEbayClosing1"  size="10" style="cursor:pointer;" id="ReturnUpdate_date_demandEbayClosing1" /></td>';
	echo '		<td>Ebay Rückg. angef. 2x</td>';
	echo '		<td><input type="text" name="date_demandEbayClosing2"  size="10" style="cursor:pointer;" id="ReturnUpdate_date_demandEbayClosing2" /></td>';
	echo '		<td>Ebay-Provision zurück</td>';
	echo '		<td><input type="checkbox" name="ebayFeeRefundOK" id="ReturnUpdate_ebayFeeRefundOK" value="1">';	
	echo '	</tr>';	
	echo '</table>';
	echo '</fieldset>';
	echo '</div>';

	echo '</div>';
	
	//GETEBAYDATA DIALOG
	echo '<div id="ReturnAddGetEbayDataDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td><b>Käufer ID: </b><input type="text" size="20" name="user_id" id="ReturnAddGetEbayDataDialog_user_id" onKeyUp="key_getEbayData();" />';
	echo '	<button name="GetData" onclick="getEbayData();">Ebay-Daten beziehen</button></td>';
	echo '</tr>';
	echo '</table>';
	echo '<div id="ReturnsEbayTransactions"></div>';
	echo '</div>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>