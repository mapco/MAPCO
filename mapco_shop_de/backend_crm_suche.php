<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
<style>
.search_detail
{
	width:150px;
	padding:5px;
	border-color:#999;
	border-style:solid;
	border-width:1px;
	background-color:#FFF;
  	z-index:100;
}
.search_option_box
{
	border-color:#999;
	border-style:solid;
	border-width:1px;
	padding:5px;
	margin-right:5px;
	margin-top:5px;
}
#suggestionsbox
{
	border-color:#999;
	border-style:solid;
	border-width:1px;
	background-color:#FFF;
	z-index:200;
	padding:3px;
	position:absolute;
}
</style>

<script type="text/javascript">

var id_user=<?php echo $_SESSION["id_user"]; ?>;

var zip_search_enabled=false;
var gewerblich_search_enabled=false;

var id_list=0;
var ListType="";
reminder_type="";

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
			$( "#add_communication_reminder_date" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		});

		$(function()
		{
			$( "#update_communication_reminder_date" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
		});

	function convert_time_from_timestamp(timestamp, mod)
	{
		var Timestamp = new Date(timestamp*1000);
		var time='';
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
	  return time;
	}

	function create_mail(kunden)
	{
		var counter=0;
		if (kunden=="all")
		{
			var customerList = document.customer_list;
			
			for (i=0; i<customerList.elements.length; i++)
			{
				if (customerList.elements[i].name=='customer_select[]' && customerList.elements[i].checked==true) counter++;
			}
			$("#send_mailDialog_kunden").html(counter);

		}
		
		$("#send_mailDialog").dialog
		({	buttons:
			[
				{ text: "Nachricht senden", click: function() { send_created_mail(0);  } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Mail erstellen",
			width:600
		});		
	
	}

	function customer_add()
	{
		$("#customer_add_business").val("");
		$("#customer_add_company").val("");
		$("#customer_add_name").val("");
		$("#customer_add_street1").val("");
		$("#customer_add_street2").val("");
		$("#customer_add_zip").val("");
		$("#customer_add_city").val("");
		$("#customer_add_country").val("");
		$("#customer_add_phone").val("");
		$("#customer_add_mobile").val("");
		$("#customer_add_fax").val("");
		$("#customer_add_mail").val("");
		$("#customer_add_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { customer_add_save();  } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neuen Kunden anlegen",
			width:600
		});		
	}

	function customer_add_save()
	{
		var $business=$("#customer_add_business").val();
		var $company=$("#customer_add_company").val();
		var $name=$("#customer_add_name").val();
		var $street1=$("#customer_add_street1").val();
		var $street2=$("#customer_add_street2").val();
		var $zip=$("#customer_add_zip").val();
		var $city=$("#customer_add_city").val();
		var $country=$("#customer_add_country").val();
		var $phone=$("#customer_add_phone").val();
		var $mobile=$("#customer_add_mobile").val();
		var $fax=$("#customer_add_fax").val();
		var $mail=$("#customer_add_mail").val();
		$("#customer_add_dialog").dialog("close");
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"crm", Action:"CustomerAdd", business:$business, company:$company, name:$name, street1:$street1, street2:$street2, zip:$zip, city:$city, country:$country, phone:$phone, mobile:$mobile, fax:$fax, mail:$mail }, function($data)
		{
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
			wait_dialog_hide();
			
			show_status("Neuer Kunde wurde erfolgreich angelegt.");
		});
	}

	function send_created_mail(count)
	{
		var customerList = document.customer_list;
		
		if ($("#send_mailDialog_itemList").val()=="")
		{
			alert("Es muss eine Liste angegeben werden!");
			return;
		}
		
		// MAIL COPY
		if (count==0)
		{
			$("#send_mailDialog").dialog("close");
			
			$("#send_mailActionDialog").dialog
			({	buttons:
				[
					{ text: "Beenden", click: function() { $(this).dialog("close"); return; } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Nachrichten werden gesendet",
				width:600
			});		
			
			
			if ($("#send_mailDialog_copy").attr("checked")=="checked" && $("#send_mailDialog_copyTo").val()!="")
			{
				var account_user_id="<?php echo $_SESSION["id_user"]; ?>";
				var mail=$("#send_mailDialog_copyTo").val();
				var itemList=$("#send_mailDialog_itemList").val();
				var subject=$("#send_mailDialog_subject").val();
			//	var mailFrom=$("#send_mailDialog_copyTo").val();
				$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "send_item_mail", account_user_id:account_user_id, itemList:itemList, mail:mail, subject:subject},
					function(data2)
					{
					//	alert(data2);
						var $xml=$($.parseXML(data2));
						var Ack = $xml.find("Ack").text();
						if (Ack=="Success") 
						{
						}
					}
				);
			}
		}

		// MAIL CUSTOMMER
		if (count<customerList.elements.length)
		{
			if (customerList.elements[count].name=='customer_select[]' && customerList.elements[count].checked==true)
			{
				var customer_id=customerList.elements[count].value;
				
				$("#send_mailActionDialog").html("Nachricht "+count+1+" von "+customerList.elements.length+" wird versendet");
				
				//GET CUSTOMER DATA
				$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "get_customer_data", customer_id:customer_id},
					function(data)
					{
						var $xml=$($.parseXML(data));
						var Ack = $xml.find("Ack").text();
						if (Ack=="Success") 
						{
							var account_user_id=$xml.find("account_user_id").text();
							var mail=$xml.find("mail").text();
							var itemList=$("#send_mailDialog_itemList").val();
							var subject=$("#send_mailDialog_subject").val();
							//var mailFrom=$("#send_mailDialog_copyTo").val();
							if (mail!="")
							{
								$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "send_item_mail", account_user_id:account_user_id, itemList:itemList, mail:mail, subject:subject},
									function(data2)
									{
										var $xml=$($.parseXML(data2));
										var Ack = $xml.find("Ack").text();
										if (Ack=="Success") 
										{
										}
									}
								);
							} // IF MAIL
	
						} // IF SUCCES data
					} // FUNCTION data
				); // GET CUSTOMER DATA
				
			}
			count++;
		}

		if (count==customerList.elements.length)
		{
			$("#send_mailActionDialog").html("Alle Nachrichten wurden versendet");
		}
		else
		{
			send_created_mail(count);
		}
	}
	
	function show()
	{
		if (ListType=="searchbox")
		{
			show_list_menu();
			show_searchbox();
		}
		if (ListType=="privateList" || ListType=="publicLists")
		{
			show_list_menu();
			show_customerLists();
			if (!id_list==0) show_customer_list();
		}
		if (ListType=="reminder")
		{
			show_list_menu();
			if (!reminder_type=="")	show_customers_reminders(reminder_type);
			show_reminder(reminder_type);
		}
	//STARTANSICHT
		if (ListType=="")
		{
			ListType="reminder";
			reminder_type="now";

			show_list_menu();
			show_customers_reminders("now");
			show_reminder("now");
		}

	}

	function show_customer_table(data)
	{
		//alert(data);
		var $xml=$($.parseXML(data));
		
		var table='';
		table+='<form name="customer_list">';
		table+='<table>';
		table+='<tr>';
		table+='	<th><input type="checkbox" name="customer_select_all" id="customer_select_all" onclick="checkAll();"></th>';
		//table+='	<th></th>';
		table+='	<th style="width:400px">Kunde</th>';
		table+='	<th style="width:120px">';
		table+='		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/mail_edit.png" alt="Mail/Newsletter an Auswahl versenden" title="Mail/Newsletter an Auswahl versenden" onclick="create_mail(\'all\');" />';
		table+='		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/note_add.png" alt="Kunde(n) zu einer Liste hinzufügen" title="Kunde(n) zu einer Liste hinzufügen" onclick="add_customer_to_costumer_list();" />';
		table+='		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Neuen Kunden anlegen" title="Neuen Kunden anlegen" onclick="customer_add();" />';
		table+='	</th>';
		table+='</tr>';
		
		if (($xml.find("listowner").text()*1)==id_user) var show_del_button=true; else var show_del_button=false;

		$xml.find("customer").each(
		function()
		{
			var customer_id=$(this).find("customer_id").text();
			var address="";
			
			if ($(this).find("street1").text()!="") address+=$(this).find("street1").text();
			if ($(this).find("street2").text()!="")
			{
				if (address=="") {address+=$(this).find("street2").text();} else {address+=' ,'+$(this).find("street2").text();}
			}
			if ($(this).find("zip").text()!="")
			{
				if (address=="") {address+=$(this).find("zip").text();} else {address+=' ,'+$(this).find("zip").text();}
			}
			if ($(this).find("city").text()!="")
			{
				if (address=="") {address+=$(this).find("city").text();} else {address+=' ,'+$(this).find("city").text();}
			}
			if ($(this).find("country").text()!="")
			{
				if (address=="") {address+=$(this).find("country").text();} else {address+=' ,'+$(this).find("country").text();}
			}
			
			var inlist='';
			$(this).find("inlist").each(
				function()
				{
					if (inlist=="")	inlist+=$(this).text(); else inlist+='\n'+$(this).text();
				}
			);
			
			var reminder=$(this).find("reminder").text();
			
			table+='<tr>';
			table+='	<td>'
			table+='		<input type="checkbox" name="customer_select[]" id="customer_select_'+customer_id+'" value="'+customer_id+'" />';	
			table+='	</td>';
			table+='	<td>'
			if (!reminder=="")
			{
				table+='	<span style="background-color:#f60; width:400px;">Kontakt-Wiedervorlage am: '+reminder+'</span><br />';
			}
			table+='		<b>'+$(this).find("name").text()+'</b><br />';
			table+='		<small>';
			table+=address;
			table+='		</small>';
			if ($(this).find("notes").text()!="0")
			{
				table+='<br /><span style="background-color:#fc0; width:400px; font-size:8pt">Es sind '+$(this).find("notes").text()+' Notizen vorhanden. <a href="javascript:show_notes('+customer_id+');">[+]</a></span>';
			}
			if ($(this).find("communications").text()!="0")
			{
				table+='<br /><span style="background-color:#cc0; width:400px; font-size:8pt">Es sind '+$(this).find("communications").text()+'  Kontakte protokolliert. <a href="javascript:show_communication('+customer_id+');">[+]</a></span>';
			}
			if (!inlist=="") table+='<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:help; float:right;" src="images/icons/24x24/attachment.png" alt="Kunde ist in Liste: \n'+inlist+'" title="Kunde ist in Liste: \n'+inlist+'" />';
			table+='	</td>';
			table+='	<td>';
			if (show_del_button) table+='<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/remove.png" alt="Kunde aus Liste löschen" title="Kunde aus Liste löschen" onclick="del_custormer_from_list('+customer_id+');" />';
				table+= '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/edit.png" alt="Kundendaten bearbeiten" title="Kundendaten bearbeiten" onclick="update_customer_data('+customer_id+');" />';
				table+= '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/page_edit.png" alt="Notiz hinzufügen" title="Notiz hinzufügen" onclick="add_customer_note('+customer_id+');" />';
				table+= '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/user_comment.png" alt="Kundenkommunikation protokollieren" title="Kundenkommunikation protokollieren" onclick="add_communication('+customer_id+');" />';
			table+='	</td>';
			table+='</tr>';
			
		});
		table+='</table>';
		table+='</form>';
		
		return table;
	}


	function show_list_menu()
	{
	//	wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_customer_lists_view", id_list:id_list},
			function(data)
			{
				$("#customer_lists").html(data);
				wait_dialog_hide();
			}
		);
//		show_customerLists();
	}


	function show_customer_list()
	{	
		if (!id_list==0 || !id_list=="")
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_customers_from_list", id_list:id_list},
				function(data)
				{
					wait_dialog_hide();
					var $xml=$($.parseXML(data));
					var Ack = $xml.find("Ack").text();
					if (Ack=="Success") 
					{
						$("#search_results").html(show_customer_table(data));
							
					}
					else
					{
						show_status2(data);
					}
				}
			);
		}

	}
	
	function show_customers_reminders(mode)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_customers_from_reminders", id_user:id_user, mode:mode},
			function(data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#search_results").html(show_customer_table(data));
						
				}
				else
				{
					show_status2(data);
				}
			}
		);
				wait_dialog_hide();

	}

	function show_communication(customer_id)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_customer_communications", customer_id:customer_id},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				var communications_box='';
								
				communications_box+='<span><b>Kundenkontakt</b></span>';
				communications_box+='<span style="float:right; background-color:#bbb;">';
				communications_box+='<a href="javascript:$(\'#communicationsView\').slideUp(300); $(\'#communicationsView\').html(\'\');" title="Kontaktprotokolle schließen" alt="Kontaktprotokolle schließen">&nbsp;X&nbsp;</a>';
				communications_box+='</span>';
				
				
				$xml.find("communication").each(
				function()
				{
					var first_contact= convert_time_from_timestamp($(this).find("communication_firstmod").text(), "complete");
					var last_contact=convert_time_from_timestamp($(this).find("communication_lastmod").text(), "complete");
					var id_communication=$(this).find("communication_id").text();
					communications_box+='<div id="customer_communication'+customer_id+'" class="search_option_box">';
					communications_box+='	<div style="background-color:#ddd; width:100%">';
					communications_box+='	<span sytle="font-size:8pt;">Kontaktiert von '+$(this).find("communication_firstmod_user_name").text()+' am '+first_contact+'</span>';
					communications_box+='	</div>';
					communications_box+='	<br /><b>'+$(this).find("communication_type").text()+'</b><br />';
					communications_box+=	$(this).find("communication_text").text()+'<br /><br />';
					communications_box+='	<div style="background-color:#ddd; font-size:8pt; width:100%">';
					communications_box+='	<i>letzte Bearbeitung durch <b> '+$(this).find("communication_lastmod_user_name").text()+'</b> am <b>'+last_contact+'</b></i><br />';
					communications_box+='	</div>';
					communications_box+='	<div style="width:100%">'
					communications_box+='	<span style="font-size:8pt"><a href="javascript:update_communication('+id_communication+');">[bearbeiten]</a></div>';
					communications_box+='</div>';
					
				});
				
				$("#communicationsView").html("");
				$("#communicationsView").html(communications_box);
				$("#communicationsView").slideDown(300);
			}
			else 
			{
				show_status2(data);
			}
			
		});
		
	}
	
	function show_notes(customer_id)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_customer_notes", customer_id:customer_id},
		function(data)
		{
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
					
				});
				$("#noteView").html("");
				$("#noteView").html(note_box);
				$("#noteView").slideDown(300);
			}
			else 
			{
				show_status2(data);
			}
			
		});
		
	}

	
	function add_customer_to_costumer_list()
	{
		$("#add_customerToListDialog").dialog
		({	buttons:
			[
				{ text: "Hinzufügen", click: function() { save_add_customer_to_costumer_list(); return; } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); return; } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Kunden einer Kundenliste hinzufügen",
			width:400
		});		
	
	}
	
	function save_add_customer_to_costumer_list()
	{
		var customerList = document.customer_list;
		wait_dialog_show();
		for (i=0; i<customerList.elements.length; i++)
		{
			if (customerList.elements[i].name=='customer_select[]' && customerList.elements[i].checked==true) 
			{
				$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_add_customer_to_list", customer_id:customerList.elements[i].value, list_id:$("#add_customerToList").val()},
					function(data)
					{
						var $xml=$($.parseXML(data));
						var Ack = $xml.find("Ack").text();
						if (Ack=="Success") 
						{
						}
					}
					
				);
			}
		}
		wait_dialog_hide();
		$("#add_customerToListDialog").dialog("close");
		show_status("Die Kunden wurden der Liste hinzugefügt");
		show();
		//show_list_menu();
	}
	
	
	function add_customer_list()
	{
		$("#add_customerListDialog").dialog
		({	buttons:
			[
				{ text: "Liste Anlegen", click: function() { save_add_customer_list(); return; } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); return; } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neue Kundenliste anlegen",
			width:400
		});		
	
	}
	
	function save_add_customer_list()
	{
		wait_dialog_show();
	
		var customerListTitle=$("#add_customer_List_Name").val();
		var ListPrivate=0;
		if ($("#add_customer_List_Private").is(":checked")) ListPrivate=1;
		
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_add_customer_list", customerListTitle:customerListTitle, ListPrivate:ListPrivate},
		function(data)
		{
			//alert(data);
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				show_status("Die Liste wurde erfolgreich angelegt!");
				$("#add_customer_List_Name").val("");
				$("#add_customer_List_Private").attr("checked", false);
				
			}
			else 
			{
				show_status2(data);
			}
			$("#add_customerListDialog").dialog("close");
			//show_list_menu();
			show();
		});
	}
	
	function del_customer_list(listID)
	{
		$("#view").dialog
		({	buttons:
			[
				{ text: "Kundenliste löschen", click: function() { save_del_customer_list(listID); } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Kundenliste löschen",
			width:300
		});		
	}
	
	function save_del_customer_list(listID)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_delete_customer_list", listID:listID},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				show_status("Die Kundenliste wurde erfolgreich gelöscht!");
				$("#view").val("");
				//show_customer_list();
				show();
			}
			else 
			{
				show_status2(data);
			}
			$("#view").dialog("close");
		});
	}

	function update_customer_list(listID, title, private)
	{
		$("#add_customer_List_Name").val(title);
		if (private==1)	$("#add_customer_List_Private").attr("checked", "checked");
		$("#add_customerListDialog").dialog
		({	buttons:
			[
				{ text: "Änderung speichern", click: function() { save_update_customer_list(listID); } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Liste ändern",
			width:300
		});		
	}
	
	function save_update_customer_list(listID)
	{
		if ($("#add_customer_List_Private").is(":checked"))
		{
			var private=1;
		}
		else 
		{
			var private=0;
		}
		
		var title=$("#add_customer_List_Name").val();
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_update_customer_list", listID:listID, title:title, private:private},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				show_status("Die Kundenliste wurde erfolgreich geändert!");
				$("#add_customer_List_Name").val("");
				//show_customer_list();
				show();

			}
			else 
			{
				show_status2(data);
			}
			$("#add_customerListDialog").dialog("close");

		});
	}
	
	
	function del_custormer_from_list(customer_id)
	{
		$("#view").dialog
		({	buttons:
			[
				{ text: "Kunde löschen", click: function() { save_del_custormer_from_list(customer_id); } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Kunde aus Liste löschen",
			width:300
		});		
	}

	function save_del_custormer_from_list(customer_id)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_delete_customer_from_list", customer_id:customer_id, id_list:id_list},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				show_status("Der Kunde wurde erfolgreich aus der Liste entfernt!");
				$("#add_customer_noteDialog").val("");
				show_customer_list();
				show();

			}
			else 
			{
				show_status2(data);
			}
			$("#view").dialog("close");
		});
	}

	function add_customer_note(customer_id)	
	{
		$("#add_customer_noteDialog").dialog
		({	buttons:
			[
				{ text: "Notiz speichern", click: function() { save_add_customer_note(customer_id); return; } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); return; } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Neue Notiz zum Kunden anlegen",
			width:400
		});		

	}

	function save_add_customer_note(customer_id)
	{
		wait_dialog_show();

		var note=$("#add_customer_note").val();

		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_add_customer_note", customer_id:customer_id, note:note},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				show_status("Die Notiz wurde erfolgreich gespeichert!");
				$("#add_customer_noteDialog").val("");
				//show_customer_list();
				show();

			}
			else 
			{
				show_status2(data);
			}
			$("#add_customer_noteDialog").dialog("close");
			
		});
	}

	
	
	function update_customer_note(note_id)
	{
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_note", note_id:note_id},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				$("#update_customer_note").val($xml.find("Note").text());
				$("#update_customer_noteDialog").dialog
				({	buttons:
					[
						{ text: "Änderung speichern", click: function() { save_update_customer_note(note_id); } },
						{ text: "Beenden", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Notiz bearbeiten",
					width:400
				});		
			}
			else 
			{
				show_status2(data);
				return;
			}
		});
	}
	
	function save_update_customer_note(note_id)
	{
		wait_dialog_show();

		var note=$("#update_customer_note").val();

		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_update_note", note_id:note_id, note:note},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				show_status("Die Änderung der Notiz wurde erfolgreich gespeichert!");
				$("#update_customer_noteDialog").val("");
			//	show_customer_list();
				show();
				
			}
			else 
			{
				show_status2(data);
			}
			$("#update_customer_noteDialog").dialog("close");
			
		});
	}
	
	function delete_customer_note(note_id)
	{
		$("#view").dialog
		({	buttons:
			[
				{ text: "Notiz löschen", click: function() { save_delete_customer_note(note_id); } },
				{ text: "Beenden", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Soll diese Notiz wirklich gelöscht werden",
			width:300
		});		
	}
	
	function save_delete_customer_note(note_id)
	{
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_delete_note", note_id:note_id},
		function(data)
		{
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				show_status("Die Notiz wurde gelöscht!");
				$("#noteView").html("");
				$("#noteView").slideUp(300);

				//show_customer_list();
				show();
			}
			else 
			{
				show_status2(data);
			}
			$("#view").dialog("close");
			
		});
	}
	
	
	function add_communication(customer_id)
	{
		//add_communication_contacttype
		
		$("#add_communicationDialog").dialog
		({	buttons:
			[
				{ text: "Kundenkontakt speichern", click: function() { save_add_communication(customer_id);} },
				{ text: "Beenden", click: function() { $(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Kundenkontakt / Kundenkommunikation speichern",
			width:500
		});		
	}


	function save_add_communication(customer_id)
	{
		wait_dialog_show();

		var contacttype=$("#add_communication_contacttype").val();
		if (contacttype=="") 
		{
			$("#add_communication_contacttype").focus();
			alert("Es muss eine Kontaktart angegeben werden!");
			return;
		}
		
		var reminder_date=$("#add_communication_reminder_date").val();
		var reminder_time=$("#add_communication_reminder_time").val();
		
		var note=$("#add_communication_note").val();
		
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_add_customer_communication", customer_id:customer_id, contacttype:contacttype, note:note, reminder_date:reminder_date, reminder_time:reminder_time },
		function(data)
		{
			//alert(data);
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				show_status("Der Eintag zur Kundenkommunikation wurde erfolgreich gespeichert!");
				$("#add_communication_note").val(""); 
				$("#add_communication_contacttype").val("");
				$("#add_communication_reminder_date").val("");
				$("#add_communication_reminder_time").val("");

				//show_customer_list();
				show();
			}
			else 
			{
				show_status2(data);
			}
			$("#add_communicationDialog").dialog("close");
			
		});
		
	}
	
	function update_reminder()
	{
		if ($("#update_communication_reminder").is(":checked"))
		{
			
			$("#update_communication_reminder_date").attr("disabled", false);
			$("#update_communication_reminder_time").attr("disabled", false);
			$("#update_communication_reminder_date").focus();
		}
		else
		{
			$("#update_communication_reminder_date").val("");
			$("#update_communication_reminder_date").attr("disabled", true);
			$("#update_communication_reminder_time").attr("disabled", true);
		}
	}
	
	function update_communication(communication_id)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_get_communication", communication_id:communication_id},
		function(data)
		{
			//alert(data);
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				$("#update_communication_type").text($xml.find("communication_type").text());
				$("#update_communication_note").val($xml.find("communication_text").text());
				
				if ($xml.find("reminder").text()==0)
				{
					$("#update_communication_reminder").attr("checked", false);
					$("#update_communication_reminder_date").val("");
					//$("#update_communication_reminder_time").val("");
				}
				else
				{
					var reminderDateTimestamp = new Date($xml.find("reminder").text()*1000);
					var reminderDate=reminderDateTimestamp.getDate();
					if (String(reminderDateTimestamp.getMonth()).length==1)
					{
						reminderDate+='.0'+reminderDateTimestamp.getMonth()+'.'+reminderDateTimestamp.getFullYear();
					}
					else 
					{
						reminderDate+='.'+reminderDateTimestamp.getMonth()+'.'+reminderDateTimestamp.getFullYear();
					}

					$("#update_communication_reminder").attr("checked", true);
					$("#update_communication_reminder_date").val(reminderDate);
					$("#update_communication_reminder_time").val(reminderDateTimestamp.getHours());

				}
				
				update_reminder();
				
				$("#update_communicationDialog").dialog
				({	buttons:
					[
						{ text: "Änderung speichern", click: function() { save_update_communication(communication_id);} },
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Kundenkontakt / Kundenkommunikation ändern",
					width:500
				});		

			}
			else 
			{
				show_status2(data);
			}
			$("#add_communicationDialog").dialog("close");
			
		});
	}
	
	function save_update_communication(communication_id)
	{
		var contacttype=$("#update_communication_type").text();
		var note=$("#update_communication_note").val();

		var reminder_date=$("#update_communication_reminder_date").val();
		var reminder_time=$("#update_communication_reminder_time").val();
		
		
		$.post("<?php echo PATH; ?>soa/", {API: "crm", Action: "crm_update_communication", communication_id:communication_id, contacttype:contacttype, note:note, reminder_date:reminder_date, reminder_time:reminder_time },
		function(data)
		{
			//alert(data);
			wait_dialog_hide();
			var $xml=$($.parseXML(data));
			var Ack = $xml.find("Ack").text();
			if (Ack=="Success") 
			{
				show_status("Der Eintag zur Kundenkommunikation wurde erfolgreich geändert!");
				$("#add_communication_note").val(""); 
				$("#add_communication_contacttype").val("");
				$("#add_communication_reminder_date").val("");
				$("#add_communication_reminder_time").val("");

				show();
				//show_customer_list();
				
			}
			else 
			{
				show_status2(data);
			}
			$("#update_communicationDialog").dialog("close");
			
		});
		
	}


	function update_customer_data(customer_id)
	{
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "get_customer_data", customer_id:customer_id},
			function(data)
			{
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					if ($xml.find("business").text()=="1")
					{
						$("#update_customer_data_business").text("Geschäftskunde");
					}
					else
					{
						$("#update_customer_data_business").text("Privatkunde");
					}
					$("#update_customer_data_company").val($xml.find("company").text());
					$("#update_customer_data_name").val($xml.find("name").text());
					$("#update_customer_data_street1").val($xml.find("street1").text());
					$("#update_customer_data_street2").val($xml.find("street2").text());
					$("#update_customer_data_zip").val($xml.find("zip").text());
					$("#update_customer_data_city").val($xml.find("city").text());
					$("#update_customer_data_country").val($xml.find("country").text());
					$("#update_customer_data_phone").val($xml.find("phone").text());
					$("#update_customer_data_mobile").val($xml.find("mobile").text());
					$("#update_customer_data_fax").val($xml.find("fax").text());
					$("#update_customer_data_mail").val($xml.find("mail").text());
					var lastmod = $xml.find("lastmod").text();
					var lastmod_user = $xml.find("lastmod_user").text();
					$("#update_customer_dataDialog").dialog
					({	buttons:
						[
							{ text: "Anderungen speichern", click: function() { save_update_customer_data(customer_id);} },
							{ text: "Beenden", click: function() { $(this).dialog("close");} }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Kundendaten ändern. Letzte Änderung: "+lastmod+" durch "+lastmod_user,
						width:600
					});		
					
				}
				else 
				{
					show_status2(data);
				}
			}
		);
	}

	function save_update_customer_data(customer_id)
	{
		wait_dialog_show();
		var business=$("#update_customer_data_business").val();
		var company=$("#update_customer_data_company").val();
		var name=$("#update_customer_data_name").val();
		var street1=$("#update_customer_data_street1").val();
		var street2=$("#update_customer_data_street2").val();
		var zip=$("#update_customer_data_zip").val();
		var city=$("#update_customer_data_city").val();
		var country=$("#update_customer_data_country").val();
		var phone=$("#update_customer_data_phone").val();
		var mobile=$("#update_customer_data_mobile").val();
		var fax=$("#update_customer_data_fax").val();
		var mail=$("#update_customer_data_mail").val();
		
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "update_costumer_data", customer_id:customer_id, company:company, name:name, street1:street1, street2:street2, zip:zip, city:city, country:country, phone:phone, mobile:mobile, fax:fax, mail:mail },
			function(data)
			{
				wait_dialog_hide();
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					show_status("Die Änderungen wurden erfolgreich gespeichert");
					$("#update_customer_dataDialog").dialog("close");
					
					$("#update_customer_data_business").val("");
					$("#update_customer_data_company").val("");
					$("#update_customer_data_name").val("");
					$("#update_customer_data_street1").val("");
					$("#update_customer_data_street2").val("");
					$("#update_customer_data_zip").val("");
					$("#update_customer_data_city").val("");
					$("#update_customer_data_country").val("");
					$("#update_customer_data_phone").val("");
					$("#update_customer_data_mobile").val("");
					$("#update_customer_data_fax").val("");
					$("#update_customer_data_mail").val("");
					
					show();
					//show_customer_list();
									
				}
				else 
				{
					show_status2(data);
				}
				
			}
		);
	}
	
	
	
	function keysearch()
	{
		if (event.keyCode == 13)
		{
		/*	if 	($("#suggestionsbox").is(":visible")) $("#suggestionsbox").slideUp(100,"linear",
				function()
				{
					get_search_results();
				}
			);
		*/
		get_search_results();
		}
		
		else
		{
			var qry_string=$("#search_field").val();
			if (qry_string.length>2)
			{
	//			get_suggestion();
			}
		}
	}
	
	function get_search_results()
	{
		wait_dialog_show();
		
		var qry_string = $("#search_field").val();
		var zip_search=true;
		var rc_zip = $("#zip").val();
		var distance = $("#distance").val();
		var ktype=$("input[name=ktype]:checked").val();
		
		if 	(zip_search_enabled && rc_zip!="" && distance!="") {zip_search="1";} else {zip_search="0";}
		if 	(gewerblich_search_enabled && ktype!="" ) {custormertype_search="1";} else {custormertype_search="0";}
		
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_get_customers_search_results", qry_string:qry_string, zip_search:zip_search, rc_zip:rc_zip,distance:distance, custormertype_search:custormertype_search, customer_type:ktype},
			function(data)
			{
				$("#searchbox").show();
				$("#search_results").html(show_customer_table(data));
				//$("#search_results").html(data);
				//show_customerLists();
				wait_dialog_hide();
			}
		);
	}
	
	function get_suggestion()
	{
		var qry_string=$("#search_field").val();

		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "crm_search", qry_string:qry_string, qry_option:2},
			function(data)
			{
//				alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
	//				alert(data);
					$("#suggestionsbox").html("");
					var count=parseInt($xml.find("count").text());
					if (count>0) 
					{
						for (i=0; i<=count; i++)
						{
							var respons_data=$xml.find("data_"+i+"_string").text()+"<br />";
							
							$("#suggestionsbox").append(respons_data);
						}
						
						var pos=$("#search_field").position();
						$("#suggestionsbox").css("top", pos.top+20);
						$("#suggestionsbox").css("left", pos.left);
	
						if 	(!$("#suggestionsbox").is(":visible")) $("#suggestionsbox").slideDown(100);
					}
					else
					{
						$("#suggestionsbox").html("<b>Keine Suchtreffer</b>");
						var pos=$("#search_field").position();
						$("#suggestionsbox").css("top", pos.top+20);
						$("#suggestionsbox").css("left", pos.left);
	
						if 	(!$("#suggestionsbox").is(":visible")) $("#suggestionsbox").slideDown(100);
						
					}
				}
				else { $("#suggestionsbox").html("Keine Suchergebnisse");;
				}
			}
		);
	} 

	function update_contacts_mapcoWebshop()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "import_contacts_mapcoWebshop"},
			function (data)
			{
				$("#view").html(data);
				wait_dialog_hide();
			}
		);
		
	}

	function checkAll()
	{
		var state = document.getElementById("customer_select_all").checked;
		var customerList = document.customer_list;
		for (i=0; i<customerList.elements.length; i++)
		{
			if (customerList.elements[i].name=='customer_select[]')
				customerList.elements[i].checked = state;
		}
	}

	function show_search_options()
	{
		if 	($("#search_options").is(":visible"))
		{
			$("#search_options").slideUp(150);
		}
		else
		{
			$("#search_options").slideDown(150);
		}
	}
	
	function show_zip_search_detail()
	{
		if 	($("#zip_search_detail").is(":visible"))
		{
			$("#zip_search_detail").slideUp(150)
		}
		else
		{
			var pos=$("#zip_search").position();
			$("#zip_search_detail").css("position", "absolute");
			$("#zip_search_detail").css("top", pos.top+30);
			$("#zip_search_detail").css("left", pos.left);
			$("#zip_search_detail").slideDown(150);
		}
		
	}
	
	function add_zip_search_details()
	{
		var rc_zip = $("#zip").val();
		var distance = $("#distance").val();
		
		var dellink="<a title='Suchkriterium entfernen' alt='Suchkriterium entfernen' href='javascript:del_zip_search_details();' >[X]</a>";
		
		var text="&nbsp;"+distance+"&nbsp;km von PLZ&nbsp"+rc_zip+"&nbsp;"+dellink;
		
		zip_search_enabled=true;

		$("#zip_search_detail").slideUp(150);
		$("#zip_search_text").html(text);
		$("#zip_search_text").slideDown(150);
	}
	
	function del_zip_search_details()
	{
		zip_search_enabled=false;
		$("#zip_search_text").slideUp(150);
		$("#zip_search_text").html("");
	}
		
	function show_gewerblich_search_detail()
	{
		if 	($("#gewerblich_search_detail").is(":visible"))
		{
			$("#gewerblich_search_detail").slideUp(150);
		}
		else
		{
			var pos=$("#gewerblich_search").position();
			$("#gewerblich_search_detail").css("position", "absolute");
			$("#gewerblich_search_detail").css("top", pos.top+30);
			$("#gewerblich_search_detail").css("left", pos.left);
			$("#gewerblich_search_detail").slideDown(150);
		}
		
	}
	
	function add_gewerblich_search_details()
	{
		var ktype=$("input[name=ktype]:checked").val();
		var dellink="<a title='Suchkriterium entfernen' alt='Suchkriterium entfernen' href='javascript:del_gewerblich_search_details();' >[X]</a>";

		if (ktype=="all") { var text = "&nbsp;<b>Alle</b>&nbsp;"+dellink; }
		if (ktype=="0") { var text = "&nbsp;Privat&nbsp;"+dellink; }
		if (ktype=="1") { var text = "&nbsp;Gewerblich&nbsp;"+dellink;	}
		
		gewerblich_search_enabled=true;

		$("#gewerblich_search_detail").slideUp(150);
		$("#gewerblich_search_text").html(text);
		$("#gewerblich_search_text").slideDown(150);
	}
	
	function del_gewerblich_search_details()
	{
		gewerblich_search_enabled=false;
		$("#gewerblich_search_text").slideUp(150);
		$("#gewerblich_search_text").html("");
	}
	
	
	function show_searchbox()
	{
		if ($("#searchbox").not(":visible") )
		{
			$("#searchbox").slideDown(300);
			$("#menu_searchbox").css("font-weight", "bold");
			
			$("#search_results").html("");
			
			$(".privateLists").slideUp(300);
			$(".privateLists").css("font-weight", "normal");
			$("#privateList_head").css("font-weight", "normal");
			$(".publicLists").slideUp(300);
			$(".publicLists").css("font-weight", "normal");
			$("#publicList_head").css("font-weight", "normal");
				
			$(".reminder").slideUp(300);
			$("#reminder_head").css("font-weight", "normal");
			$(".reminder_later").css("font-weight", "normal");
			$(".reminder_now").css("font-weight", "normal");
			
			$(".RCLists").slideUp(300);
			$(".RCLists").css("font-weight", "normal");
			$("#RCList_head").css("font-weight", "normal");

			
		}

		$("#communicationsView").slideUp(300);
		$("#noteView").slideUp(300);
	}
	
	
	function show_customerLists()
	{
		if (ListType=="privateList" && $(".privatLists").not(":visible") )
		{
			$("#privateList_head").css("font-weight", "bold");
			$("#publicList_head").css("font-weight", "normal");
			$("#RCList_head").css("font-weight", "normal");

			$(".privateLists").slideDown(300);
			$(".publicLists").slideUp(300);
			$(".RCLists").slideUp(300);

			$(".privateLists").css("font-weight", "normal");
			$(".publicLists").css("font-weight", "normal");
			$(".RCLists").css("font-weight", "normal");
			$("#menuList"+id_list).css("font-weight", "bold");
		}
		
		if (ListType=="publicLists" && $(".publicLists").not(":visible") ) 
		{
			$("#publicList_head").css("font-weight", "bold");
			$("#privateList_head").css("font-weight", "normal");
			$("#RCList_head").css("font-weight", "normal");

			$(".publicLists").slideDown(300);
			$(".privateLists").slideUp(300);
			$(".RCLists").slideUp(300);

			$(".privateLists").css("font-weight", "normal");
			$(".publicLists").css("font-weight", "normal");
			$(".RCLists").css("font-weight", "normal");
			$("#menuList"+id_list).css("font-weight", "bold");
		}
		
		if (ListType=="RCList" && $(".RCLists").not(":visible") ) 
		{
			$("#RCList_head").css("font-weight", "bold");
			$("#publicList_head").css("font-weight", "normal");
			$("#privateList_head").css("font-weight", "normal");
			
			$(".RCLists").slideDown(300);
			$(".publicLists").slideUp(300);
			$(".privateLists").slideUp(300);

			$(".RCLists").css("font-weight", "normal");
			$(".privateLists").css("font-weight", "normal");
			$(".publicLists").css("font-weight", "normal");
			$("#menuList"+id_list).css("font-weight", "bold");
		}

		
		$("#reminder_head").css("font-weight", "normal");
		$(".reminder_later").css("font-weight", "normal");
		$(".reminder_now").css("font-weight", "normal");
		$(".reminder").slideUp(300);
		$("#searchbox").slideUp(300);
		$("#menu_searchbox").css("font-weight", "normal");

	}
	
	function show_reminder(remindertype)
	{
		if (remindertype=="now")
		{
			$(".reminder_now").css("font-weight", "bold");
			$(".reminder_later").css("font-weight", "normal");
		}
		if (remindertype=="later")
		{
			$(".reminder_later").css("font-weight", "bold");
			$(".reminder_now").css("font-weight", "normal");
		}
		
		$("#reminder_head").css("font-weight", "bold");
		$(".reminder").slideDown(300);
		
		$(".privateLists").slideUp(300);
		$(".privateLists").css("font-weight", "normal");
		$("#privateList_head").css("font-weight", "normal");
		$(".publicLists").slideUp(300);
		$(".publicLists").css("font-weight", "normal");
		$("#publicList_head").css("font-weight", "normal");
		$(".RCLists").slideUp(300);
		$(".RCLists").css("font-weight", "normal");
		$("#RCList_head").css("font-weight", "normal");

		$("#menu_searchbox").css("font-weight", "normal");

		$("#searchbox").slideUp(300);
		$("#communicationsView").slideUp(300);
		$("#noteView").slideUp(300);
		

	}
	
	
	function hide_suggestionsbox()
	{
		$("#suggestionsbox").val("");
		$("#suggestionsbox").slideUp(100);
	}

	function setCopyTo()
	{
		if ($("#send_mailDialog_copy").attr("checked")=="checked") $("#send_mailDialog_copyTo").attr("disabled", false);
		else $("#send_mailDialog_copyTo").attr("disabled", true);
	}
</script>


<?php	

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_crm_suche.php">CRM</a>';
	echo ' > CRM-Suche';
	echo '</p>';
	
	echo '<p>';
//	echo '<h1>CRM-Suche<button id="btn1" onclick="update_contacts_mapcoWebshop();" style="float:right">Update</button></h1>';
	echo '<h1>CRM-Suche</h1>';
	echo '</p>';

	echo '	<div id="customer_lists" style="float:left"></div>';
	
	echo '<div id="middle" style="float:left; width:600px">';
	echo '	<div id="searchbox" style="float:left; display:none">';
	echo '		<div id="searchbar" style="display:inline; float:left;">';
	echo '			<input type="text" id="search_field" size="30" onKeyUp="keysearch();" onblur="hide_suggestionsbox();" />';
	echo '			<button id="btn3" onclick="get_search_results();">Suchen</button>';
	echo '			<button id="btn2" onclick="show_search_options();">Suchoptionen</button>';
	echo '		</div>';
	echo '		<br style="clear:both" />';

	// SUCHOPTIONEN
	echo '		<div id="search_options" style="display:none; float:left;">';
	echo '			<div class="search_option_box" id="zip_search" style="float:left">';
	echo '				<a href="javascript:show_zip_search_detail();">Umkreissuche</a>';
	echo '				<span class="option_box_textfield" id="zip_search_text" style="display:none"></span>';
	echo '			</div>';
	$res=q("select a.zipcode from cms_contacts_locations as a, cms_contacts_departments as b, cms_contacts as c where c.idCmsUser = ".$_SESSION["id_user"]." and c.department_id = b.id_department and b.location_id=a.id_location;", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==1)
	{
		$row=mysqli_fetch_array($res);
		$locationZip=$row["zipcode"];
	}
	else { $locationZip="";}
	
	echo '			<div class="search_detail" id="zip_search_detail" style="display:none; float:left">';
	echo '				<b>PLZ</b><input type="text" size="6" name="zip" id="zip" value="'.$locationZip.'" /><br />';
	echo '				<b>Umkreis</b><input type="text" size="6" name="distance" id="distance" value="0"/>km<br />';
	echo '				<button name="btn4" onclick="add_zip_search_details();">OK</button>';
	echo '			</div>';
	echo '			<div class="search_option_box" id="gewerblich_search" style="float:left">';
	echo '				<a href="javascript:show_gewerblich_search_detail();">Kundentyp</a>';
	echo '				<span class="option_box_textfield" id="gewerblich_search_text"></span>';
	echo '			</div>';
	echo '			<div class="search_detail" id="gewerblich_search_detail" style="display:none; float:left">';
	echo '				<input type="radio" name="ktype" value="all" checked="checked" /><b>alle</b><br />';
	echo '				<input type="radio" name="ktype" value="0" />Privat<br />';
	echo '				<input type="radio" name="ktype" value="1" />Gewerblich<br />';
	echo '				<button name="btn4" onclick="add_gewerblich_search_details();">OK</button>';
	echo '			</div>';
	echo '		</div>'; //SEARCHOPTIONS

	echo '	</div>'; //SEARCHBOX
//	echo '	<br style="clear:both">';
	echo '	<div id="search_results" style="float:left"></div>';
	echo '</div>'; //MIDDLE

	//VIEW
	// Communications View
	echo '<div style="float:left">';
		echo '<div id="communicationsView" class="search_option_box" style="display:none; width:400px; float:left"></div>';
		echo '<div id="noteView" class="search_option_box" style="display:none; width:400px; float:left"></div>';
	echo '</div>';

	
	echo '<script type="text/javascript">show();</script>';

	// SEND MAIL DIALOG
	echo '<div id="send_mailDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Betreffzeile der Mail</td>';
	echo '	<td><input type="text" size="50" id="send_mailDialog_subject"></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Anzahl ausgewählter Kunden</td>';
	echo '	<td id="send_mailDialog_kunden"></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Liste auswählen</td>';
	echo '	<td>';
	echo '		<select id="send_mailDialog_itemList" size="1">';
	echo '			<option value="">Bitte eine Liste auswählen</option>';
	echo '			<optgroup label="öffentliche Listen">';
	$res=q("SELECT * FROM shop_lists WHERE private = 0;", $dbshop, __LINE__, __FILE__);
	while ($row=mysqli_fetch_array($res)		)
	{
		echo '		<option value='.$row["id_list"].'>'.$row["title"].'</option>';
	}
	echo '			</optgroup>';
	echo '			<optgroup label="private Listen">';
	$res=q("SELECT * FROM shop_lists WHERE private = 1 AND firstmod_user = ".$_SESSION["id_user"].";", $dbshop, __LINE__, __FILE__);
	while ($row=mysqli_fetch_array($res))
	{
		echo '		<option value='.$row["id_list"].'>'.$row["title"].'</option>';
	}
	echo '			</optgroup>';
	echo '		</select>';
	echo '	</td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Kopie der Nachricht an</td>';
	echo '	<td><input type="checkbox" id="send_mailDialog_copy" checked="checked" onchange="setCopyTo();">';
	$res=q("SELECT * FROM cms_contacts WHERE idCmsUser = ".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==1)
	{ 
		$row=mysqli_fetch_array($res);
		$copyTo=$row["mail"];
	}
	else 
	{ 
		$copyTo="";
	}
	echo '	<input type="text" size="46" id="send_mailDialog_copyTo" value="'.$copyTo.'"></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//send_mailActionDialog
	echo '<div id="send_mailActionDialog" style="display:none"></div>';

	//ADD LIST DIALOG
	echo '<div id="add_customerListDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Titel der Liste</td>';
	echo '	<td><input type="text" name="ListName" id="add_customer_List_Name" size="25" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Private Liste</td>';
	echo '	<td><input type="checkbox" name="ListPrivate" id="add_customer_List_Private" value=1 /></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

	//add_customer DIALOG
	echo '<div id="add_customerToListDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Kunde(n) zur Liste hinzufügen</td>';
	echo '	<td>';
		echo '<select name="ListName" id="add_customerToList" size="1">';
		$res_private=q("SELECT * FROM crm_costumer_lists WHERE private=1 AND firstmod_user = ".$_SESSION["id_user"].";", $dbweb, __LINE__, __FILE__);
		if (mysqli_num_rows($res_private)>0)
		{
			echo '<optgroup label="private Listen">';
			while ($row_private=mysqli_fetch_array($res_private))
			{
				echo '<option value='.$row_private["id_list"].'>'.$row_private["title"].'</option>';
			}
			echo '</optgroup>';
		}
		$res_public=q("SELECT * FROM crm_costumer_lists WHERE private=0 ;", $dbweb, __LINE__, __FILE__);
		if (mysqli_num_rows($res_public)>0)
		{
			echo '<optgroup label="öffentliche Listen">';
			while ($row_public=mysqli_fetch_array($res_public))
			{
				echo '<option value='.$row_public["id_list"].'>'.$row_public["title"].'</option>';
			}
			echo '</optgroup>';
		}
		echo '</select>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
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

	//update_customer_note DIALOG
	echo '<div id="update_customer_noteDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Notiz:</td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td><textarea name="customer_note" id="update_customer_note" cols="50" rows="10"></textarea></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

	//add_communication DIALOG
	echo '<div id="add_communicationDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Kontaktart:</td>';
	echo '	<td><select name="contacttype" id="add_communication_contacttype" size="1">';
	echo '		<option value="">Bitte Kontaktart auswählen</option>';
	echo '		<option value="phone">Telefonat</option>';
	echo '		<option value="mail">E-Mail</option>';
	echo '		<option value="fax">Fax</option>';
	echo '		<option value="newsletter">Newsletter</option>';
	echo '		<option value="personally">Persönlich</option>';
	echo '	</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Gesprächsnotiz</td>';
	echo '	<td><textarea name="customer_note" id="add_communication_note" cols="50" rows="10"></textarea></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Wiedervorlage</td>';
	echo '	<td><input type="text" name="reminder_date" id="add_communication_reminder_date" size="10" />';
	echo '		<select id="add_communication_reminder_time" size="1">';
	for ($i=6; $i<24; $i++)
	{
		if ($i==12) $selected='selected'; else $selected="";
		echo '		<option value='.$i.' '.$selected.'>'.$i.':00 Uhr</option>';
	}
	echo ' 		</select>';
	echo '	</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//update_communication DIALOG
	echo '<div id="update_communicationDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Kontaktart:</td>';
	echo '	<td><span id="update_communication_type"></span></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Gesprächsnotiz</td>';
	echo '	<td><textarea name="customer_note" id="update_communication_note" cols="50" rows="10"></textarea></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Wiedervorlage</td>';
	echo '	<td>';
	echo '		<input type="checkbox" id="update_communication_reminder" onclick="update_reminder();">';
	echo '		<input type="text" name="reminder_date" id="update_communication_reminder_date" size="10" />';
	echo '		<select id="update_communication_reminder_time" size="1">';
	for ($i=6; $i<24; $i++)
	{
		if ($i==12) $selected='selected'; else $selected="";
		echo '		<option value='.$i.' '.$selected.'>'.$i.':00 Uhr</option>';
	}
	echo ' 		</select>';
	echo '	</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

	
	//CUSTOMER ADD DIALOG
	echo '<div id="customer_add_dialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td></td>';
	echo '	<td><span id="customer_business"><b></b></span></td>';
	echo '</tr><tr>';
	echo '	<td>Unternehmen</td>';
	echo '	<td><input type="text" id="customer_add_company" size="40" /></td>';
	echo '</tr><tr>';
	echo '	<td>Name</td>';
	echo '	<td><input type="text" id="customer_add_name" size="40" /></td>';
	echo '</tr><tr>';
	echo '	<td>Anschrift</td>';
	echo '	<td><input type="text" id="customer_add_street1" size="40" /></td>';
	echo '</tr><tr>';
	echo '	<td>Anschrift Zusatz</td>';
	echo '	<td><input type="text" id="customer_add_street2" size="40" /></td>';
	echo '</tr><tr>';
	echo '	<td>Postleitzahl</td>';
	echo '	<td><input type="text" id="customer_add_zip" size="8" /></td>';
	echo '</tr><tr>';
	echo '	<td>Stadt</td>';
	echo '	<td><input type="text" id="customer_add_city" size="30" /></td>';
	echo '</tr><tr>';
	echo '	<td>Land</td>';
	echo '	<td><input type="text" id="customer_add_country" size="30" /></td>';
	echo '</tr><tr>';
	echo '	<td>Telefon</td>';
	echo '	<td><input type="text" id="customer_add_phone" size="20" /></td>';
	echo '</tr><tr>';
	echo '	<td>Mobiltelefon</td>';
	echo '	<td><input type="text" id="customer_add_mobile" size="20" /></td>';
	echo '</tr><tr>';
	echo '	<td>Fax</td>';
	echo '	<td><input type="text" id="customer_add_fax" size="20" /></td>';
	echo '</tr><tr>';
	echo '	<td>E-Mail</td>';
	echo '	<td><input type="text" id="customer_add_mail" size="40" /></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';


	//update_customer_data
	echo '<div id="update_customer_dataDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td></td>';
	echo '	<td><span id="update_customer_data_business"><b></b></span></td>';
	echo '</tr><tr>';
	echo '	<td>Unternehmen</td>';
	echo '	<td><input type="text" name="company" id="update_customer_data_company" size="40" /></td>';
	echo '</tr><tr>';
	echo '	<td>Name</td>';
	echo '	<td><input type="text" name="name" id="update_customer_data_name" size="40" /></td>';
	echo '</tr><tr>';
	echo '	<td>Anschrift</td>';
	echo '	<td><input type="text" name="street1" id="update_customer_data_street1" size="40" /></td>';
	echo '</tr><tr>';
	echo '	<td>Anschrift Zusatz</td>';
	echo '	<td><input type="text" name="street2" id="update_customer_data_street2" size="40" /></td>';
	echo '</tr><tr>';
	echo '	<td>Postleitzahl</td>';
	echo '	<td><input type="text" name="zip" id="update_customer_data_zip" size="8" /></td>';
	echo '</tr><tr>';
	echo '	<td>Stadt</td>';
	echo '	<td><input type="text" name="city" id="update_customer_data_city" size="30" /></td>';
	echo '</tr><tr>';
	echo '	<td>Land</td>';
	echo '	<td><input type="text" name="country" id="update_customer_data_country" size="30" /></td>';
	echo '</tr><tr>';
	echo '	<td>Telefon</td>';
	echo '	<td><input type="text" name="phone" id="update_customer_data_phone" size="20" /></td>';
	echo '</tr><tr>';
	echo '	<td>Mobiltelefon</td>';
	echo '	<td><input type="text" name="mobile" id="update_customer_data_mobile" size="20" /></td>';
	echo '</tr><tr>';
	echo '	<td>Fax</td>';
	echo '	<td><input type="text" name="fax" id="update_customer_data_fax" size="20" /></td>';
	echo '</tr><tr>';
	echo '	<td>E-Mail</td>';
	echo '	<td><input type="text" name="mail" id="update_customer_data_mail" size="40" /></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

	echo 	'<div id="view" style="float:left"></div>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>