<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<style type="text/css">
    	.hover:hover { background:#eeeeee; }
		.nav_element { width:94%; }
		.no_border { border:none; }
		.inactive_input { background-color:RGB(180,180,180); }
    </style>

	<script src="javascript/cms/DialogConfirm.php" type="text/javascript" /></script>
    <script src="javascript/cms/DialogNotify.php" type="text/javascript" /></script>
    <script src="javascript/crm/Communications.php" type="text/javascript" /></script>

	<script type="text/javascript" />

        var id_user=<?php echo $_SESSION["id_user"]; ?>;
		
		function customer_list_add()
		{
//			alert( $( '#c_list_types_2' ).val() );
//			alert( $( '#dialog_add_list_input' ).val() );
			
			$post_data = 				new Object();
			$post_data['API'] = 		'crm';
			$post_data['APIRequest'] = 	'CustomerListAdd';
			$post_data['title'] = 		$( '#dialog_add_list_input' ).val();
			$post_data['type'] = 		$( '#c_list_types_2' ).val();
			
			soa2( $post_data, 'customer_list_add_callback' );
		}
		
		function customer_list_add_callback( $xml )
		{
			//view aktualisieren
			load_customers( $xml.find('list_id').text(), $xml.find('title').text(), $xml.find('type').text() );
			$( '#c_list_types' ).val( $xml.find('type').text() );
			load_customer_lists( $xml.find('type').text() );
		}
    
        function load_customer_lists(type_id)
        {
           /* var where = '';
            
            if(type_id != 0)
            {
                where += 'WHERE type='+type_id;
                if (type_id == 1) 
                {
                    where += ' AND firstmod_user='+id_user;	
                }
            }*/
            
            wait_dialog_show('Lade Kundenlisten');
            $.post("<?php echo PATH; ?>soa2/", { API:"crm", APIRequest:"CustomerListsGet", type_id:type_id }, function($data){ 
              	//show_status2($data); return;
                try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
                				
				var lists='';
                if( $xml.find('num_rows').text() > 0 )
                {	
                    $xml.find('customer_list').each(function()
					{
						$list_text = $(this).find('title').text();
						lists += '<li class="customer_list hover nav_element" id="list_'+$(this).find('customer_list_id').text()+'" list_text="'+$list_text+'"style="cursor:pointer;">'+$list_text+' ('+$(this).find('ccount').text()+')</li>';
                    });
                }
				else
				{
					lists += '<li class="nav_element">Keine Liste gefunden!</li>';
				}
               
				$("#ul_lists").children().each(function(){
					if (!$(this).hasClass('list_nav'))
					{
						$(this).remove();						  
					}
				});
			   
				$("#ul_lists").append(lists);
                
                $(".customer_list").click(function(){
                    var id = $(this).attr('id');
                    id = id.split('_');
                    load_customers(id[1],$(this).attr('list_text'), type_id);
                });
				
				$(function() {
					$( "#ul_lists" ).sortable( { items:"li:not(.header)" } );
					$( "#ul_lists" ).sortable( { cancel:".header"} );
					$( "#ul_lists" ).disableSelection();
					$( "#ul_lists" ).bind( "sortupdate", function(event, ui)
					{
						var list = $('#ul_lists').sortable('toArray');
						$.post("<?php echo PATH; ?>soa2/", { API:"crm", APIRequest:"CustomerListsSort", list:list, type:'lists' }, function($data){ 
							//show_status2($data); return;
							try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
							if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
							
							load_customer_lists(type_id)
						});
					});
				});
                        
                wait_dialog_hide();
            });
        }
        
        function load_customer_list_types()
        {
            wait_dialog_show('Lade Typenliste');
			
            $.post("<?php echo PATH; ?>soa2/", { API:"crm", APIRequest:"CustomerListTypesGet" }, function($data)
			{ 
               // show_status2($data); return;
                try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
                if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
                
                var type_title = '';
                var id = '';
                var listcount = 0;
                
				//buttons_lists = '		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/note_add.png" alt="Kunde(n) zu einer Liste hinzufügen" title="Kunde(n) zu einer Liste hinzufügen" onclick="dialog_add_customer_to_costumer_list('+id_list +','+ list_title +','+ type_id+');" />';
				button_lists = '		<img class="button_add_list" value="0" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Neue Liste anlegen" title="Neue Liste anlegen" onclick="JavaScript:dialog_add_list();" />';
				
				$("#header_types").css('vertical-align','center');
				$("#header_types").append(button_lists);
				
              //  var types = '<ul id="ul_types" class="orderlist" style="width:200px;">';
               // types += '	<li id="header_types" class="header" style="width:94%;">Listentypen</li>';
                var types = ''  ;
				var first_type = 0;
                $xml.find('list_type').each(function()
				{
                    type_id = $(this).find('type_id').text();
					
					if ( first_type == 0 )
					{
						first_type = type_id;	
					}
					
                    listcount = $(this).find('listcount').text();
                   // types += '<li class="customer_list_type hover nav_element" id="type_'+type_id+'" value="'+listcount+'" style="cursor:pointer;">'+$(this).find('title').text()+' ('+listcount+')</li>';
				   types += '<option value="'+type_id+'">'+$(this).find('title').text()+' ('+listcount+')</option>';
                });
            
                $("#c_list_types").html(types);
                
				$("#c_list_types").change(function(){
					
					load_customer_lists($(this).val(),'');
				/*	if ( $(this).attr('value') > 0 )
					{
						var id = $(this).attr('id');
						id = id.split('_');
	
						load_customer_lists(id[1],$(this).text());
					}
					else
					{
						if($("#no_lists").length == 0)
						{
							$("#ul_types").append('<li id="no_lists" class="hover" style="width:94%;">Keine Listen gefunden!</li>');
						}
					}*/
                });
				
                /*$(".customer_list_type").click(function(){
					if ( $(this).attr('value') > 0 )
					{
						var id = $(this).attr('id');
						id = id.split('_');
	
						load_customer_lists(id[1],$(this).text());
					}
					else
					{
						if($("#no_lists").length == 0)
						{
							$("#ul_types").append('<li id="no_lists" class="hover" style="width:94%;">Keine Listen gefunden!</li>');
						}
					}
                });*/
                
               /* $("#header_types").click(function(){
                    load_customer_list_types();
                });*/

				load_customer_lists(first_type)   
                wait_dialog_hide();
            });
        }
        
        function load_customers(id_list, list_title, type_id)
        {
            wait_dialog_show('Lade Kundenliste');
            $.post("<?php echo PATH; ?>soa2/", { API:"crm", APIRequest:"GetCustomerFromList", id_list:id_list }, function($data){ 
                //show_status2($data); return;
                try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
                if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
                
				var customers='<form name="customer_list">';
				customers+='<table style="width:544px;">';
				customers+='<tr>';
				customers+='	<th><input type="checkbox" name="customer_select_all" id="customer_select_all" onclick="checkAll();"></th>';
				//customers+='	<th></th>';
				customers+='	<th id="customer_list_header" value="'+id_list+'" value2="'+type_id+'" style="width:430px">'+list_title+'</th>';
				customers+='	<th style="width:90px">';
				customers+='		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/mail_edit.png" alt="Mail/Newsletter an Auswahl versenden" title="Mail/Newsletter an Auswahl versenden" onclick="create_mail(\'all\');" />';
				customers+='		<img id="button_add_customer_to_list" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/search.png" alt="Kunde(n) zu einer Liste hinzufügen" title="Kunde(n) zu einer Liste hinzufügen" />';
				customers+='		<img id="button_create_customer" value="0" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Neuen Kunden anlegen" title="Neuen Kunden anlegen" />';
				customers+='	</th>';
				customers+='</tr>';
				
				
				if ($xml.find("customer").length >0 )
				{
					if (($xml.find("listowner").text()*1)=="1") var show_del_button=true; else var show_del_button=false;					
					
					var name = '';
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
						
						customers+='<tr>';
						customers+='	<td>'
						customers+='		<input type="checkbox" name="customer_select[]" id="customer_select_'+customer_id+'" value="'+customer_id+'" />';	
						customers+='	</td>';
						customers+='	<td>'
						if (!reminder=="")
						{
							customers+='	<span style="background-color:#f60; width:400px;">Kontakt-Wiedervorlage am: '+reminder+'</span><br />';
						}
						name = $(this).find("company").text() + ' ' + $(this).find("name").text();
						customers+='		<b>'+name+'</b><br />';
						customers+='		<small>';
						customers+= address;
						customers+='		</small>';
						if ($(this).find("notes").text()!="0")
						{
							customers+='<br /><span style="background-color:#fc0; width:400px; font-size:8pt">Es sind '+$(this).find("notes").text()+' Notizen vorhanden. <a href="javascript:show_notes('+customer_id+');">[+]</a></span>';
						}
						if ($(this).find("communications").text()!="0")
						{
							customers+='<br /><span style="background-color:#cc0; width:400px; font-size:8pt">Es sind '+$(this).find("communications").text()+'  Kontakte protokolliert. <a href="javascript:communication_show('+customer_id+',0,0);">[+]</a></span>';
						}
						if (!inlist=="") customers+='<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:help; float:right;" src="images/icons/24x24/attachment.png" alt="Kunde ist in Liste: \n'+inlist+'" title="Kunde ist in Liste: \n'+inlist+'" />';
						customers+='	</td>';
						customers+='	<td>';
						if (show_del_button)
						{
							customers+='<img class="button_delete_customer_from_list" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/remove.png" alt="Kunde aus Liste löschen" customer_id="'+customer_id+'" customer_name="'+name+'" title="Kunde aus Liste löschen" />';
						}
					//	customers+='		<img class="button_edit_address" value="'+customer_id+'" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/home.png" alt="Addressen anzeigen" title="Adressen anzeigen" />';
						customers+= '<img value="'+customer_id+'" class="button_edit_customer" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/edit.png" alt="Kundendaten bearbeiten" title="Kundendaten bearbeiten" />';
					//	customers+= '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/page_edit.png" alt="Notiz hinzufügen" title="Notiz hinzufügen" onclick="user_contact_2('+customer_id+',0);" />'; //onclick="add_customer_note('+customer_id+');"
					//	customers+= '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/user_comment.png" alt="Kundenkommunikation protokollieren" title="Kundenkommunikation protokollieren" onclick="add_communication('+customer_id+');" />';
						customers+= '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/user_comment.png" alt="Kundenkommunikation protokollieren" title="Kundenkommunikation einsehen" onclick="communication_show('+customer_id+',0,0);" />';
						customers+='	</td>';
						customers+='</tr>';
					});					
				}
				else
				{
					customers+='<tr><td colspan="3">Dieser Liste sind noch keine Kunden zugeordnet.</td></tr>';						
				}
				
				customers+='</table>';
				customers+='</form>';

				$("#c_list_customers").html(customers);
				
				$("#button_add_customer_to_list").click(function()
				{
					dialog_add_customer_to_costumer_list(id_list,list_title,type_id);
				});
				
				$(".button_edit_customer").click(function(){
					dialog_customer_detail_edit($(this).attr('value'), id_list, list_title, type_id);
				});
				
				$(".button_edit_address").click(function(){
					load_customer_address_data($(this).attr('value'));
				});
				
				$("#button_create_customer").click(function(){
					dialog_customer_create(id_list, list_title, type_id);
				});
				
				$(".button_delete_customer_from_list").click(function(){
					del_customer_from_list($(this).attr('customer_id'), id_list, list_title, type_id, $(this).attr('customer_name'));
				});
				
				wait_dialog_hide();
			});
        }
        
        function add_communication(customer_id)
        {
            if ($("#dialog_communication_add").length == 0)
            {
                var dialog_div = '<div id="dialog_communication_add">';
                dialog_div += '</div>';
                $("#content").append(dialog_div);
            }
            //add_communication DIALOG
    
            var dialog_content = '<table>';
            dialog_content += '<tr>';
            dialog_content += '	<td>Kontaktart:</td>';
            dialog_content += '	<td><select name="contacttype" id="add_communication_contacttype" size="1">';
            dialog_content += '		<option value="">Bitte Kontaktart auswählen</option>';
            dialog_content += '		<option value="phone">Telefonat</option>';
            dialog_content += '		<option value="mail">E-Mail</option>';
            dialog_content += '		<option value="fax">Fax</option>';
            dialog_content += '		<option value="newsletter">Newsletter</option>';
            dialog_content += '		<option value="personally">Persönlich</option>';
            dialog_content += '	</select></td>';
            dialog_content += '</tr>';
            dialog_content += '<tr>';
            dialog_content += '	<td>Gesprächsnotiz</td>';
            dialog_content += '	<td><textarea name="customer_note" id="add_communication_note" cols="50" rows="10"></textarea></td>';
            dialog_content += '</tr>';
            dialog_content += '<tr>';
            dialog_content += '	<td>Wiedervorlage</td>';
            dialog_content += '	<td><input type="text" name="reminder_date" id="add_communication_reminder_date" size="10" />';
            dialog_content += '		<select id="add_communication_reminder_time" size="1">';
            for (i=6; i<24; i++)
            {
                var selected = (i==12) ? 'selected': '';
                dialog_content += '		<option value='+i+' '+selected+'>'+i+':00 Uhr</option>';
            }
            dialog_content += ' 		</select>';
            dialog_content += '	</td>';
            dialog_content += '</tr>';
            dialog_content += '</table>';
            
            $("#dialog_communication_add").empty().append(dialog_content);
    
            $("#dialog_communication_add").dialog
            ({	buttons:
                [
                    { text: "Kundenkontakt speichern", click: function() { save_add_communication(customer_id); $(this).dialog("close"); } },
                    { text: "Beenden", click: function() { $(this).dialog("close"); } }
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
            wait_dialog_show('Speichere Kontaktinformationen');
    
            var contacttype=$("#add_communication_contacttype").val();
            if (contacttype=="") 
            {
                $("#add_communication_contacttype").focus();
                dialog_notify("Es muss eine Kontaktart angegeben werden!");
                return;
            }

            var reminder_date=$("#add_communication_reminder_date").val();
            var reminder_time=$("#add_communication_reminder_time").val();
            
            var note=$("#add_communication_note").val();
            
            wait_dialog_show('Lade Kunden');
    
            $.post("<?php echo PATH; ?>soa2/", { API:"crm", APIRequest:"CustomerCommunicationAdd", customer_id:customer_id, contacttype:contacttype, note:note, reminder_date:reminder_date, reminder_time:reminder_time },
            function($data)
            {
                //show_status2($data); return;
                try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
                if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
                
                dialog_notify("Der Eintrag zur Kundenkommunikation wurde erfolgreich gespeichert!");
				
				var list_title = $("#customer_list_header").text();
				list_title = list_title.replace('Liste ', '');
        		load_customers($("#customer_list_header").attr('value'), list_title, $("#customer_list_header").attr('value2'));
                wait_dialog_hide();
            });
            
        }
	
		function add_customer_note(customer_id)	
		{
			if ($("#dialog_note_add").length == 0)
            {
                var dialog_div = '<div id="dialog_note_add">';
                dialog_div += '</div>';
                $("#content").append(dialog_div);
            }
			//add_customer_note DIALOG
			var dialog_content = '<table>';
			dialog_content += '<tr>';
			dialog_content += '	<td>Notiz zum Kunden hinzufügen:</td>';
			dialog_content += '</tr>';
			dialog_content += '<tr>';
			dialog_content += '	<td><textarea name="customer_note" id="add_customer_note" cols="50" rows="10"></textarea></td>';
			dialog_content += '</tr>';
			dialog_content += '</table>';
			
			$("#dialog_note_add").empty().append(dialog_content);
			
			$("#dialog_note_add").dialog
			({	buttons:
				[
					{ text: "Notiz speichern", click: function() { save_add_customer_note(customer_id); $(this).dialog("close"); return; } },
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
			wait_dialog_show('Speichere Notiz');
	
			var note=$("#add_customer_note").val();
	
			$.post("<?php echo PATH; ?>soa2/", {API: "crm", APIRequest: "CustomerNoteAdd", customer_id:customer_id, note:note},
			function($data)
            {
                //show_status2($data); return;
                try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
                if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	

				var message = '';
				var insert_id = $xml.find('insert_id').text()
				if ( insert_id != '' || insert_id != 0 )
				{
					message = 'Notiz gespeichert.';
				}
				else
				{
					message = 'Fehler aufgetreten.';
				}
				
				var list_title = $("#customer_list_header").text();
				list_title = list_title.replace('Liste ', '');
        		load_customers($("#customer_list_header").attr('value'), list_title, $("#customer_list_header").attr('value2'));
				wait_dialog_hide();
			});
		}

		function dialog_customer_create(id_list, list_title, type_id)
		{	
			if ($("#dialog_customer_create").length == 0)
			{
				var dialog_div = '<div id="dialog_customer_create">';
				dialog_div += '</div>';
				$("#content").append(dialog_div);
			}
				
			var dialog_content = '<table>';
			dialog_content += '<tr>';
			dialog_content += '	<td><select id="update_customer_data_business">';
			dialog_content += '		<option value="0">Privatkunde</option>';
			dialog_content += '		<option value="1">Geschäftskunde</option>';
			dialog_content += '	</select></td>';	
			dialog_content += '</tr>';
			dialog_content += '<tr>';
			dialog_content += '	<td>Registrierter Nutzer</td>';
			dialog_content += '	<td><span id="update_customer_data_username"></span></td>';
			dialog_content += '</tr><tr id="row_customer_data_company">';
			dialog_content += '	<td>Unternehmen</td>';
			dialog_content += '	<td><input type="text" name="company" id="update_customer_data_company" size="40" /></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Ansprechpartner</td>';
			dialog_content += '	<td><input type="text" name="name" id="update_customer_data_name" size="40" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Anschrift</td>';
			dialog_content += '	<td><input type="text" name="street1" id="update_customer_data_street1" size="40" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Anschrift Zusatz</td>';
			dialog_content += '	<td><input type="text" name="street2" id="update_customer_data_street2" size="40" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Postleitzahl</td>';
			dialog_content += '	<td><input type="text" name="zip" id="update_customer_data_zip" size="8" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Stadt</td>';
			dialog_content += '	<td><input type="text" name="city" id="update_customer_data_city" size="30" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Land</td>';
			dialog_content += '	<td><input type="text" name="country" id="update_customer_data_country" size="30" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Telefon</td>';
			dialog_content += '	<td><input type="text" name="phone" id="update_customer_data_phone" size="20" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Mobiltelefon</td>';
			dialog_content += '	<td><input type="text" name="mobile" id="update_customer_data_mobile" size="20" /></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Fax</td>';
			dialog_content += '	<td><input type="text" name="fax" id="update_customer_data_fax" size="20" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>E-Mail</td>';
			dialog_content += '	<td><input type="text" name="mail" id="update_customer_data_mail" size="40" /></td>';
			dialog_content += '</tr>';
			dialog_content += '</table>';
			
			$("#dialog_customer_create").html(dialog_content);
			
			$("#dialog_customer_create").dialog
			({	buttons:
				[
					{ text: "Änderungen speichern", click: function() { save_update_customer_details(0, id_list, list_title, type_id) } },
					{ text: "Beenden", click: function() { $(this).dialog("close");} }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Kunden hinzufügen",
				width:600
			});
		}

		function dialog_customer_detail_edit(customer_id, id_list, list_title, type_id)
		{
			//update_customer_data
			var dialog_content = '<div id="customer_detail_tabs" class="ui-tabs ui-widget" style="border:solid 1px;" active_tab="1">';
			dialog_content += '	<ul id="tabs_ul" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist">';
			dialog_content += '		<li id="customer_details" class="ui-state-default ui-corner-top ui-tabs-active ui-state-active" role="tab" tabindex="0" aria-controls="tab-general" aria-labelledby="ui-id-1" aria-selected="true" style="with:75px; height:30px; margin:2px; padding:3px; border:solid 1px; border-bottom:0; display:inline;">';
			dialog_content += '			<a id="menu_tab_1" href="#tab-general" style="cursor:pointer;" class="ui-tabs-anchor menu_tab" role="presentation" tabindex="-1">Allgemein</a>';
			dialog_content += '		</li>';
			dialog_content += '		<li id="customer_address" class="ui-state-default ui-corner-top" role="tab" tabindex="1" aria-controls="tab-general" aria-labelledby="ui-id-2" aria-selected="true" style="with:75px; height:30px; margin:2px; padding:3px; border:solid 1px; border-bottom:0; display:inline;">';
			dialog_content += '			<a id="menu_tab_2" href="#tab-general" style="cursor:pointer;" class="ui-tabs-anchor menu_tab" role="presentation" tabindex="-2">Nebenadressen</a>';
			dialog_content += '		</li>';
			dialog_content += '	</ul>';
			dialog_content += '</div>';
				
			$post_data = new Object();
			$post_data['API'] = "crm";
			$post_data['APIRequest'] = "CustomerDetailGet";
			$post_data['customer_id'] = customer_id;
			
			wait_dialog_show('Lade Kundendaten');
			$.post("<?php echo PATH; ?>soa2/", $post_data, function($data){ 
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				if ($xml.find("business").text()=="1")
				{
					customer_type = "Geschäftskunde";
				}
				else
				{
					customer_type = "Privatkunde";
				}
			
				dialog_content += '<div id="dialog_customer_detail_edit_content">';	
				dialog_content += '	<table>';
				dialog_content += '		<tr>';
				dialog_content += '			<td><input type="hidden" id="update_customer_data_id_address" value= "0"\></td>';
				dialog_content += '			<td>'+customer_type+'</td>';	
				dialog_content += '		</tr>';
				dialog_content += '		<tr>';
				dialog_content += '			<td>Registrierter Nutzer</td>';
				
				var username = ($xml.find("username").text().length > 0) ? $xml.find("username").text() : 'Kein Benutzer mit diesem Kunden verbunden!';	//??????			
				dialog_content += '			<td>'+username+' - '+$xml.find("usermail").text()+'</td>';
				dialog_content += '		</tr><tr id="row_customer_data_company">';
				dialog_content += '			<td>Unternehmen</td>';
				dialog_content += '			<td><input type="text" name="company" id="update_customer_data_company" size="40" value="'+$xml.find("company").text()+'" /></td>';
				dialog_content += '		</tr><tr>';
				dialog_content += '			<td>Ansprechpartner</td>';
				dialog_content += '			<td><input type="text" name="name" id="update_customer_data_name" size="40" value="'+$xml.find("name").text()+'" \></td>';
				dialog_content += '		</tr><tr>';
				dialog_content += '			<td>Anschrift</td>';
				dialog_content += '			<td><input type="text" name="street1" id="update_customer_data_street1" size="40" value="'+$xml.find("street1").text()+'" \></td>';
				dialog_content += '		</tr><tr>';
				dialog_content += '			<td>Anschrift Zusatz</td>';
				dialog_content += '			<td><input type="text" name="street2" id="update_customer_data_street2" size="40" value="'+$xml.find("street2").text()+'" \></td>';
				dialog_content += '		</tr><tr>';
				dialog_content += '			<td>Postleitzahl</td>';
				dialog_content += '			<td><input type="text" name="zip" id="update_customer_data_zip" size="8" value="'+$xml.find("zip").text()+'" \></td>';
				dialog_content += '		</tr><tr>';
				dialog_content += '			<td>Stadt</td>';
				dialog_content += '			<td><input type="text" name="city" id="update_customer_data_city" size="30" value="'+$xml.find("city").text()+'" \></td>';
				dialog_content += '		</tr><tr>';
				dialog_content += '			<td>Telefon</td>';
				dialog_content += '			<td><input type="text" name="phone" id="update_customer_data_phone" size="20" value="'+$xml.find("phone").text()+'" \></td>';
				dialog_content += '		</tr><tr>';
				dialog_content += '			<td>Mobiltelefon</td>';
				dialog_content += '			<td><input type="text" name="mobile" id="update_customer_data_mobile" size="20" value="'+$xml.find("mobile").text()+'" /></td>';
				dialog_content += '		</tr><tr>';
				dialog_content += '			<td>Fax</td>';
				dialog_content += '			<td><input type="text" name="fax" id="update_customer_data_fax" size="20" value="'+$xml.find("fax").text()+'" \></td>';
				dialog_content += '		</tr><tr>';
				dialog_content += '			<td>E-Mail</td>';
				dialog_content += '			<td><input type="text" name="mail" id="update_customer_data_mail" size="40" value="'+$xml.find("mail").text()+'"/></td>';
				dialog_content += '		</tr>';
				dialog_content += '	</table>';
				dialog_content += '</div>';

				var lastmod = $xml.find("lastmod").text();
				var lastmod_user = $xml.find("lastmod_user").text();
			
				if ($("#dialog_customer_detail_edit").length == 0)
				{
					var dialog_div = '<div id="dialog_customer_detail_edit">';
					dialog_div += '</div>';
					$("#content").append(dialog_div);
				}
				
				$("#dialog_customer_detail_edit").html(dialog_content);

				$(".menu_tab").click(function(){
					var id = $( this ).attr( 'id' );
					var tab = id.split("_");
					$("#tabs_ul").children().removeClass('ui-tabs-active ui-state-active');
					$(this).parent().addClass('ui-tabs-active ui-state-active');
					$("#customer_detail_tabs").attr('active_tab',tab[2]);
					if ( tab[2] == "2" )
					{
						load_dialog_customer_address(customer_id);
					}
					else
					{
						load_dialog_customer_details(customer_id, list_title, type_id);
					}
				});

				wait_dialog_hide();		
				$("#dialog_customer_detail_edit").dialog
				({	buttons:
					[
						{ text: "Änderungen speichern", click: function() { save_update_customer_data($("#customer_detail_tabs").attr('active_tab'), id_list,  customer_id, list_title, type_id); $(this).dialog("close"); } },
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Kunden hinzufügen",
					width:600
				});
			});
		}
		
		function load_dialog_customer_address(customer_id)
		{
			$post_data = new Object();
			$post_data['db'] = "dbweb";
			$post_data['API'] = "cms";
			$post_data['APIRequest'] = "TableDataSelect";
			$post_data['table'] = "crm_address";
			$post_data['where'] = "WHERE crm_customer_id="+customer_id+" AND site_id=<?php print $_SESSION['id_site']; ?>";
			
			$.post("<?php echo PATH; ?>soa2/", $post_data, function($data){ 
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				var address_list = '<table>';
				if( $xml.find('num_rows').text() > 0 )
				{	
					var id_address = 0;
					$xml.find('crm_address').each(function()
					{
						id_address = $(this).find('id_address').text();
						
						address_list += '	<tr>';
						address_list += '		<td></td><td><input type="checkbox" id="check_address_'+id_address+'" class="dialog_address_checkbox" /></td>';
						address_list += '	</tr>';
						address_list += '	<tr>';
						address_list += '		<td><label>Unternehmen</label></td><td><input type="text" id="dialog_address_input_company_'+id_address+'" class="dialog_address_input" check="check_address_'+id_address+'" value="'+$(this).find('company').text()+'" /></td>';
						address_list += '	</tr>';
						address_list += '	<tr>';
						address_list += '		<td><label>Name</label></td><td><input type="text" id="dialog_address_input_name_'+id_address+'" class="dialog_address_input" check="check_address_'+id_address+'" value="'+$(this).find('name').text()+'" /></td>';
						address_list += '	</tr>';
						address_list += '	<tr>';
						address_list += '		<td><label>Adresse</label></td><td><input type="text" id="dialog_address_input_street1_'+id_address+'" class="dialog_address_input" check="check_address_'+id_address+'" value="'+$(this).find('street1').text()+'" /></td>';
						address_list += '	<tr>';
						address_list += ' 		<td><label>Adresszusatz</label></td><td><input type="text" id="dialog_address_input_street2_'+id_address+'" class="dialog_address_input" check="check_address_'+id_address+'" value="'+$(this).find('street2').text()+'" /></td>';
						address_list += '	</tr>';
						address_list += '	<tr>';
						address_list += '		<td><label>PLZ</label></td><td><input type="text" id="dialog_address_input_zip_'+id_address+'" class="dialog_address_input" check="check_address_'+id_address+'" value="'+$(this).find('zip').text()+'" /></td>';
						address_list += '	</tr>';
						address_list += '	<tr>';
						address_list += '		<td><label>Stadt</label></td><td><input type="text" id="dialog_address_input_city_'+id_address+'" class="dialog_address_input" check="check_address_'+id_address+'" value="'+$(this).find('city').text()+'" /></td>';
						address_list += '	</tr>';
					});
				}
				else
				{
					address_list += '	<li>Keine Addressen eingetragen!</li>';
				}
				address_list += '</table>';
				wait_dialog_hide();
				$("#dialog_customer_detail_edit_content").html(address_list);
				
				$(".dialog_address_input").keyup(function(){
					$("#"+$(this).attr('check')).prop('checked','true');
				});
			});
		}
		
		function load_dialog_customer_details(customer_id, list_title, type_id)
		{ 
		/*	$post_data = new Object();
			$post_data['db'] = "dbweb";
			$post_data['API'] = "cms";
			$post_data['APIRequest'] = "TableDataSelect";
			$post_data['table'] = "crm_customers";
			$post_data['where'] = "WHERE id_crm_customer="+customer_id;
		*/
			$post_data = new Object();
			$post_data['API'] = "crm";
			$post_data['APIRequest'] = "CustomerDetailGet";
			$post_data['customer_id'] = customer_id;
			
			wait_dialog_show('Lade Kundendaten');
			$.post("<?php echo PATH; ?>soa2/", $post_data, function($data){ 
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				if ($xml.find("business").text()=="1")
				{
					customer_type = "Geschäftskunde";
				}
				else
				{
					customer_type = "Privatkunde";
				}
				
				var dialog_content = '<table>';
				dialog_content += '<tr>';
				dialog_content += '	<td><input type="hidden" id="update_customer_data_id_address" value= "0"\></td>';
				dialog_content += '	<td>'+customer_type+'</td>';	
				dialog_content += '</tr>';
				dialog_content += '<tr>';
				dialog_content += '	<td>Registrierter Nutzer</td>';
				
				var username = ($xml.find("username").length > 0) ? $xml.find("username").text() : 'Kein Benutzer mit diesem Kunden verbunden!';				
				dialog_content += '	<td>'+username+' - '+$xml.find("usermail").text()+'</td>';
				dialog_content += '</tr><tr id="row_customer_data_company">';
				dialog_content += '	<td>Unternehmen</td>';
				dialog_content += '	<td><input type="text" name="company" id="update_customer_data_company" size="40" value="'+$xml.find("company").text()+'" /></td>';
				dialog_content += '</tr><tr>';
				dialog_content += '	<td>Ansprechpartner</td>';
				dialog_content += '	<td><input type="text" name="name" id="update_customer_data_name" size="40" value="'+$xml.find("name").text()+'" \></td>';
				dialog_content += '</tr><tr>';
				dialog_content += '	<td>Anschrift</td>';
				dialog_content += '	<td><input type="text" name="street1" id="update_customer_data_street1" size="40" value="'+$xml.find("street1").text()+'" \></td>';
				dialog_content += '</tr><tr>';
				dialog_content += '	<td>Anschrift Zusatz</td>';
				dialog_content += '	<td><input type="text" name="street2" id="update_customer_data_street2" size="40" value="'+$xml.find("street2").text()+'" \></td>';
				dialog_content += '</tr><tr>';
				dialog_content += '	<td>Postleitzahl</td>';
				dialog_content += '	<td><input type="text" name="zip" id="update_customer_data_zip" size="8" value="'+$xml.find("zip").text()+'" \></td>';
				dialog_content += '</tr><tr>';
				dialog_content += '	<td>Stadt</td>';
				dialog_content += '	<td><input type="text" name="city" id="update_customer_data_city" size="30" value="'+$xml.find("city").text()+'" \></td>';
				dialog_content += '</tr><tr>';
				dialog_content += '	<td>Telefon</td>';
				dialog_content += '	<td><input type="text" name="phone" id="update_customer_data_phone" size="20" value="'+$xml.find("phone").text()+'" \></td>';
				dialog_content += '</tr><tr>';
				dialog_content += '	<td>Mobiltelefon</td>';
				dialog_content += '	<td><input type="text" name="mobile" id="update_customer_data_mobile" size="20" value="'+$xml.find("mobile").text()+'" /></td>';
				dialog_content += '</tr><tr>';
				dialog_content += '	<td>Fax</td>';
				dialog_content += '	<td><input type="text" name="fax" id="update_customer_data_fax" size="20" value="'+$xml.find("fax").text()+'" \></td>';
				dialog_content += '</tr><tr>';
				dialog_content += '	<td>E-Mail</td>';
				dialog_content += '	<td><input type="text" name="mail" id="update_customer_data_mail" size="40" value="'+$xml.find("mail").text()+'"/></td>';
				dialog_content += '</tr>';
				dialog_content += '</table>';

				var lastmod = $xml.find("lastmod").text();
				var lastmod_user = $xml.find("lastmod_user").text();
				
				wait_dialog_hide();
				$("#dialog_customer_detail_edit_content").html(dialog_content);
			});
		}
		
		function save_update_customer_data(save_type, id_list, customer_id, list_title, type_id)
		{
			if ( save_type === "1" )
			{
				save_update_customer_details(customer_id, id_list, list_title, type_id);
			}
			else if ( save_type === "2" )
			{
				var id = '';
				var row = '';
				var addresses = new Array();
				var z = 0;
				$(".dialog_address_checkbox").each(function()
				{
					if ( $(this).prop('checked') == true )
					{
						id = $(this).attr('id');
						row = id.split('_');
						addresses[z] = row[2];
						z++;
					}
				});
				addresses = addresses.join(',');
				save_update_customer_addresses(customer_id, addresses);
			}
		}
		
		function save_update_customer_details(customer_id, id_list, list_title, type_id)
		{
			wait_dialog_show();
			var business=$("#update_customer_data_business").val();
			var company=$("#update_customer_data_company").val();
			var name=$("#update_customer_data_name").val();
			var street1=$("#update_customer_data_street1").val();
			var street2=$("#update_customer_data_street2").val();
			var zip=$("#update_customer_data_zip").val();
			var city=$("#update_customer_data_city").val();
			var phone=$("#update_customer_data_phone").val();
			var mobile=$("#update_customer_data_mobile").val();
			var fax=$("#update_customer_data_fax").val();
			var mail=$("#update_customer_data_mail").val();
			
			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerDetailEdit", customer_id:customer_id, list_id:list_id, company:company, name:name, street1:street1, street2:street2, zip:zip, city:city, phone:phone, mobile:mobile, fax:fax, mail:mail }, function($data){
				
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				wait_dialog_hide();
				load_customers(id_list, list_title, type_id);
			});
		}
		
		function save_update_customer_addresses(customer_id, addresses)
		{
			var addr_arr = addresses.split(',');
			var count_addresses = addr_arr.length;
			var percent = 0;
			for ( x=0; x<count_addresses; x++ )
			{
				percent = (count_addresses/100)*x;
				wait_dialog_show('Speichert Adressen', percent);
				var company=$("#dialog_address_input_company_"+addr_arr[x]).val();
				var name=$("#dialog_address_input_name_"+addr_arr[x]).val();
				var street1=$("#dialog_address_input_street1_"+addr_arr[x]).val();
				var street2=$("#dialog_address_input_street2_"+addr_arr[x]).val();
				var zip=$("#dialog_address_input_zip_"+addr_arr[x]).val();
				var city=$("#dialog_address_input_city_"+addr_arr[x]).val();

				$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerAddressEdit", customer_id:customer_id, company:company, name:name, street1:street1, street2:street2, zip:zip, city:city, id_address:addr_arr[x] }, function($data){
				
					//show_status2($data); return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				});
			}
			wait_dialog_hide();
		}
		
		function create_mail(kunden)
		{ 
			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerSendMailDialogDataGet" }, function($data){
				
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				if ($("#send_mailDialog").length == 0)
				{
					var dialog_div = '<div id="send_mailDialog">';
					dialog_div += '</div>';
					$("#content").append(dialog_div);
				}
				// SEND MAIL DIALOG
				var dialog_content = '<table>';
				dialog_content += '<tr>';
				dialog_content += '	<td>Betreffzeile der Mail</td>';
				dialog_content += '	<td><input type="text" size="50" id="send_mailDialog_subject"></td>';
				dialog_content += '</tr>';
				dialog_content += '<tr>';
				dialog_content += '	<td>Anzahl ausgewählter Kunden</td>';
				dialog_content += '	<td id="send_mailDialog_kunden"></td>';
				dialog_content += '</tr>';
				dialog_content += '<tr>';
				dialog_content += '	<td>Liste auswählen</td>';
				dialog_content += '	<td>';
				dialog_content += '		<select id="send_mailDialog_itemList" size="1">';
				dialog_content += '			<option value="">Bitte eine Liste auswählen</option>';
		
				$xml.find('shop_list').each(function(){
					dialog_content += '			<optgroup label="'+$(this).find('shop_list_group').text()+'">';
					$(this).find('shop_list_item').each(function(){
						dialog_content += '			<option value="'+$(this).find('id').text()+'">'+$(this).find('title').text()+'</option>';
					});
					dialog_content += '			</optgroup>';
				});
				
				dialog_content += '		</select>';
				dialog_content += '	</td>';
				dialog_content += '</tr>';
				dialog_content += '<tr>';
				dialog_content += '	<td>Kopie der Nachricht an</td>';
				dialog_content += '	<td><input type="checkbox" id="send_mailDialog_copy" checked="checked" onchange="setCopyTo();">';
				dialog_content += '	<input type="text" size="46" id="send_mailDialog_copyTo" value="'+$xml.find('copyTo').text()+'"></td>';
				dialog_content += '</tr>';
				dialog_content += '</table>';
	
				$("#send_mailDialog").empty().append(dialog_content);
	
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
			});
		}
		
		function send_created_mail(count)
		{ //alert('Unter Bearbeitung!'); return;
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
					$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "send_item_mail_neu", account_user_id:account_user_id, itemList:itemList, mail:mail, subject:subject}, 
						function(data2)
						{ 
							alert(data2);
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
									$.post("<?php echo PATH; ?>soa/", { API: "crm", Action: "send_item_mail_neu", account_user_id:account_user_id, itemList:itemList, mail:mail, subject:subject},
										function(data2)
										{ alert(data2); return;
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
		
		function dialog_add_customer_to_costumer_list(id_list, list_title, type_id)
		{
			if ($("#dialog_add_customer_to_costumer_list").length == 0)
			{
				var dialog_div = '<div id="dialog_add_customer_to_costumer_list">';
				dialog_div += '</div>';
				$("#content").append(dialog_div);
			}
			//add_communication DIALOG
			var dialog_content = '<div><input type="checkbox" id="dialog_add_customer_zip_search_check" style="with:700px; align:center;" /><label> Umkreissuche</label></div>';
			dialog_content += '<div style="width:700px; height:32px;">';
			dialog_content += '	<div style="width:220px; height:100%; float:left;"><label>Freitextsuche:</label><input type="text" id="dialog_add_customer_search_field"></div>';
			dialog_content += '	<div style="width:390px; height:100%; float:left;">';
			dialog_content += '		<div>';
			dialog_content += 			'<label>PLZ </label><input type="text" id="dialog_add_customer_zip" class="inactive_input" readonly />';
			dialog_content += 			'<label>Umkreis in km </label><input type="text" id="dialog_add_customer_distance" class="inactive_input" readonly />';
			dialog_content += '		</div>';
			dialog_content += '	</div>';
			dialog_content += '	<div style="width:80px; height:100%; float:left;"><button id="dialog_add_customer_search_button">Suchen</button></div>';
			dialog_content += '</div>';
            dialog_content += '<div id="dialog_add_customer_search_results"></div>';
			$("#dialog_add_customer_to_costumer_list").empty().append(dialog_content);
    
			$("#dialog_add_customer_search_button").click(function(){
				var zip_search = $("#dialog_add_customer_zip_search_check").prop("checked");
				add_customer_to_costumer_list_search(id_list, list_title, zip_search);
			});
			
			$("#dialog_add_customer_zip_search_check").click(function(){
				var zip_search = $(this).prop('checked');

				if ( zip_search == false )
				{
					$("#dialog_add_customer_distance").empty().addClass('inactive_input').prop('readonly', true);
					$("#dialog_add_customer_zip").empty().addClass('inactive_input').prop('readonly', true);
				}
				else
				{
					$("#dialog_add_customer_distance").removeClass('inactive_input').prop('readonly', false);
					$("#dialog_add_customer_zip").removeClass('inactive_input').prop('readonly', false);
				}
			});
	
			$("#dialog_add_customer_to_costumer_list").dialog
			({	buttons:
				[
					{ text: "Kunden dieser Liste zuordnen", click: function() { add_customer_to_costumer_list_save(id_list); } },
					{ text: "Beenden", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Kunden zur Liste hinzufügen",
				width:750,
				height:500
			});			
		}
    
		function add_customer_to_costumer_list_search(id_list, list_title, zip_search)
		{
			wait_dialog_show();
		
			var qry_string = $("#dialog_add_customer_search_field").val();
			if ( zip_search == true )
			{
				var rc_zip = $("#dialog_add_customer_zip").val();
				var distance = $("#dialog_add_customer_distance").val();
				if ( distance == '' )
				{
					distance = 0;	
				}
				zip_search = 1;
			}
			else
			{
				var rc_zip = '';
				var distance = '';
				zip_search = 0;
			}

			var ktype= ''; //$("input[name=ktype]:checked").val();
			
		/*	if 	(rc_zip!="" && distance!="") {zip_search="1";} else {zip_search="0";}
			if 	(ktype!="" ) {custormertype_search="1";} else {custormertype_search="0";}*/
			
			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "crm_get_customers_search_results_neu2", id_list:id_list, qry_string:qry_string, zip_search:zip_search, rc_zip:rc_zip,distance:distance},
				function($data)
				{
					//show_status2($data); return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
					
					var phone = '';
					var mobile = '';
					var fax = '';
					var name = '';
					
					var search_result = '<table>';
					if ( $xml.find('num_rows').text() != "0"  )
					{
						$xml.find('customer').each(function(){
							search_result += '	<tr>';
							search_result += '		<td style="width:15px;"><input type="checkbox" class="dialog_add_customer_customer_select" value="' + $(this).find('id_crm_customer').text() + '" /></td>';
							search_result += '		<td style="width:730px;">';
								search_result += '		<table style="width:99%;">';
								search_result += '			<tr>';
								name = $(this).find('company').text();
								if ( name == '' )
								{
									name = $(this).find('name').text();
								}
								search_result += '				<td style="width:34%; border:none;">Name: ' + name +'</td>';
								search_result += '				<td style="width:62%; border:none;" colspan="2">Adresse: ' + $(this).find('street1').text()+ ' ' + $(this).find('street2').text();
								search_result += 			' '+$(this).find('zip').text()+' '+$(this).find('city').text()+'</td>';
								search_result += '			</tr>';
								phone = $(this).find('phone').text()
								mobile = $(this).find('mobile').text()
								fax = $(this).find('fax').text()
								if ( phone != '' || mobile != '' || fax != '' )
								{
									search_result += '			<tr><td style="width:32%; border:none;">Tel: ' + phone +'</td>';
									search_result += '			<td style="width:32%; border:none;">Handy: ' + mobile +'</td>';
									search_result += '			<td style="width:32%; border:none;">Fax: ' + fax +'</td></tr>';
								}
								search_result += '		</table>';
							search_result += '		</td>';
							search_result += '	</tr>';	
						});
					}
					else
					{
						search_result += '<tr><td style="border:none;">Keine Kunden gefunden!</td></tr>'	
					}
					search_result += '</table>';
					$("#dialog_add_customer_search_results").html(search_result);
					wait_dialog_hide();
					//$("#dialog_add_customer_to_costumer_list").dialog("close");
				}
			);
		}
		
		function add_customer_to_costumer_list_save(id_list, list_title, type_id)
		{
			wait_dialog_show();
			var x = 0;
			var customer_ids = new Array();
			$(".dialog_add_customer_customer_select").each(function(){
				if ( $(this).prop("checked") == true )
				{
					customer_ids[x] = $(this).attr('value');
					x++;
				}
			});
			
			var postdata = new Object();
			postdata['API'] = "crm";
			postdata['APIRequest'] = "crm_customers_search_add_to_list";
			postdata['id_list'] = id_list;
			postdata['customer_ids'] = customer_ids;
			
			customer_ids = customer_ids.join();

			$.post("<?php echo PATH; ?>soa2/", postdata,
				function($data)
				{
					//show_status2($data); return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
					
					wait_dialog_hide();
					load_customers(id_list, list_title, type_id);		
				});
		}
		
		function dialog_add_list()
		{
			if ($("#dialog_add_list").length == 0)
			{
				var dialog_div = '<div id="dialog_add_list">';
				dialog_div += '</div>';
				$("#content").append(dialog_div);
			}
			
			wait_dialog_show('Lade Typenliste');
			
			$post_data = new Object();
			$post_data['db'] = "dbweb";
			$post_data['API'] = "cms";
			$post_data['APIRequest'] = "TableDataSelect";
			$post_data['table'] = "crm_customer_list_types";
			//$post_data['select'] = "id_type, title";
			
            $.post("<?php echo PATH; ?>soa2/", $post_data, function($data){ 
                //show_status2($data); return;
                try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
                if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
                
				var types = '<label><select id="c_list_types_2" style="width:100%; margin:0px; padding:0px;">';				
                $xml.find('crm_customer_list_types').each(function()
				{
				   types += '<option value="'+$(this).find('id_type').text()+'">'+$(this).find('title').text()+'</option>';
                });
				types += '</select></label>';
				
				types += '<label>Listenbezeichnung: </label><input type="text" id="dialog_add_list_input" />';
				//show_status2(types); return;
				$( '#dialog_add_list' ).empty();
				
                $("#dialog_add_list").append(types);
						
				$("#dialog_add_list").dialog
				({	buttons:
					[
						{ text: "Neue Liste anlegen", click: function() { customer_list_add(); $(this).dialog('close') } },
						{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Neue Kundenliste anlegen",
					width:500
				});
				
				wait_dialog_hide();
			});
		}
		
		function load_customer_address_data(customer_id)
		{
			$post_data = new Object();
			$post_data['db'] = "dbweb";
			$post_data['API'] = "cms";
			$post_data['APIRequest'] = "TableDataSelect";
			$post_data['table'] = "crm_address";
			$post_data['where'] = "WHERE crm_customer_id="+customer_id+" AND site_id=<?php print $_SESSION['id_site']; ?>";
			
			$.post("<?php echo PATH; ?>soa2/", $post_data, function($data){ 
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				var address_list = '<ul class="orderlist" style="width:250px;">';
				address_list += '	<li class="header" style="width:94%;">';
				address_list += 		'Kundenadressen';
				address_list += 		'<img value="0" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Adresse hinzufügen" title="Adresse hinzufügen" onclick="JavaScript:dialog_customer_address_edit(0,'+customer_id+');" />';
				address_list += '	</li>';
				if( $xml.find('num_rows').text() > 0 )
				{	
					var name = '';
					$xml.find('crm_address').each(function()
					{
						name = $(this).find('company').text();
						if ( name == '' )
						{
							name = $(this).find('name').text();
						}
						address_list += '	<li style="width:94%;">';
						address_list += '		<table style="width:102%;">';
						address_list += '			<tr>';
						address_list += '				<td style="border:none;"><b>'+name+'</b>';
						address_list += '				<img value="0" style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Adresse hinzufügen" title="Adresse hinzufügen" onclick="JavaScript:dialog_customer_address_edit('+$(this).find('id_address').text()+','+customer_id+');" /></td>';
						address_list += '			</tr>';
						address_list += '			<tr><td style="border:none;" colspan="2">'+$(this).find('street1').text()+ ' ' + $(this).find('street2').text();
						address_list += 			' '+$(this).find('zip').text()+' '+$(this).find('city').text()+'</td></tr>';
						address_list += '		</table>';
						address_list += '	</li>';
					});
				}
				else
				{
					address_list += '	<li>Keine weiteren Addressen eingetragen!</li>';
				}
				address_list += '</ul>';
				$("#c_list_customer_address").html(address_list);
			});
		}
		
		function dialog_customer_address_edit(customer_address_id, customer_id)
		{
			//update_customer_data
			var dialog_content = '<table>';
			dialog_content += '<tr id="row_customer_data_company">';
			dialog_content += '	<td>Unternehmen</td>';
			dialog_content += '	<td><input type="text" name="company" id="update_customer_address_data_company" size="40" /></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Ansprechpartner</td>';
			dialog_content += '	<td><input type="text" name="name" id="update_customer_address_data_name" size="40" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Anschrift</td>';
			dialog_content += '	<td><input type="text" name="street1" id="update_customer_address_data_street1" size="40" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Anschrift Zusatz</td>';
			dialog_content += '	<td><input type="text" name="street2" id="update_customer_address_data_street2" size="40" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Postleitzahl</td>';
			dialog_content += '	<td><input type="text" name="zip" id="update_customer_address_data_zip" size="8" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Stadt</td>';
			dialog_content += '	<td><input type="text" name="city" id="update_customer_address_data_city" size="30" \></td>';
			dialog_content += '</tr><tr>';
			dialog_content += '	<td>Land</td>';
			dialog_content += '	<td><input type="text" name="country" id="update_customer_address_data_country" size="30" \></td>';
			dialog_content += '</tr>';
			dialog_content += '</table>';

			if(customer_address_id == 0)
			{
				if ($("#dialog_customer_address_edit").length == 0)
				{
					var dialog_div = '<div id="dialog_customer_address_edit">';
					dialog_div += '</div>';
					$("#content").append(dialog_div);
				}
				$("#dialog_customer_address_edit").html(dialog_content);

				$("#dialog_customer_address_edit").dialog
				({	buttons:
					[
						{ text: "Adresse speichern", click: function() { save_update_customer_address_data(customer_address_id, customer_id); } },
						{ text: "Beenden", click: function() { $(this).dialog("close");} }
					],
					closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Adresse hinzufügen",
					width:600
				});
			}
			else
			{
				$post_data = new Object();
				$post_data['db'] = "dbweb";
				$post_data['API'] = "cms";
				$post_data['APIRequest'] = "TableDataSelect";
				$post_data['table'] = "crm_address";
				$post_data['where'] = "WHERE id_address="+customer_address_id;
				
				$.post("<?php echo PATH; ?>soa2/", $post_data, function($data){ 
					//show_status2($data); return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}

					if ($("#dialog_customer_address_edit").length == 0)
					{
						var dialog_div = '<div id="dialog_customer_address_edit">';
						dialog_div += '</div>';
						$("#content").append(dialog_div);
					}
					$("#dialog_customer_address_edit").html(dialog_content);

					$("#update_customer_address_data_company").val($xml.find("company").text());
					$("#update_customer_address_data_name").val($xml.find("name").text());
					$("#update_customer_address_data_street1").val($xml.find("street1").text());
					$("#update_customer_address_data_street2").val($xml.find("street2").text());
					$("#update_customer_address_data_zip").val($xml.find("zip").text());
					$("#update_customer_address_data_city").val($xml.find("city").text());
					$("#update_customer_address_data_country").val($xml.find("country").text());
					var lastmod = $xml.find("lastmod").text();
					var lastmod_user = $xml.find("lastmod_user").text();
					wait_dialog_hide();
					
					$("#dialog_customer_address_edit").dialog
					({	buttons:
						[
							{ text: "Änderungen speichern", click: function() { save_update_customer_address_data(customer_address_id, customer_id); } },
							{ text: "Beenden", click: function() { $(this).dialog("close"); } }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Adresse ändern. Letzte Änderung: "+lastmod+" durch "+lastmod_user,
						width:600
					});
				});
			}
		}
		
		function save_update_customer_address_data(customer_address_id, customer_id)
		{
			wait_dialog_show();
			var company=$("#update_customer_address_data_company").val();
			var name=$("#update_customer_address_data_name").val();
			var street1=$("#update_customer_address_data_street1").val();
			var street2=$("#update_customer_address_data_street2").val();
			var zip=$("#update_customer_address_data_zip").val();
			var city=$("#update_customer_address_data_city").val();
			var country=$("#update_customer_address_data_country").val();

			$.post("<?php echo PATH; ?>soa2/", { API: "crm", APIRequest: "CustomerAddressEdit", customer_id:customer_id, company:company, name:name, street1:street1, street2:street2, zip:zip, city:city, country:country, id_address:customer_address_id }, function($data){
				
				//show_status2($data); return;
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				$("#dialog_customer_detail_edit").dialog("close");
				wait_dialog_hide();
				load_customer_address_data(customer_id);
			});
		}
		
		function del_customer_from_list(customer_id, id_list, list_title, type_id, costumer_name)
		{
			/*$("#view").dialog
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
			});		*/
			var $callback =	function(){
				wait_dialog_show('Kunde aus Liste entfernen',0);
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
						load_customers(id_list, list_title, type_id)
						show();
		
					}
					else 
					{
						show_status2(data);
					}
				});
			}

			dialog_confirm('Kunde "'+costumer_name+'" aus Liste entfernen', $callback);
		}
		
        //load_customer_list_types();
		$(function(){load_customer_list_types()});
		
    </script>
<?php

	//print '	<div id="c_list_types" style="float:left;"></div>';
	print '	<ul id="ul_lists" class="orderlist ui-sortable" style="width:200px;">';
	print '		<li id="header_types" class="header nav_element list_nav">Kundenlisten</li>';
	print '		<li class="list_nav header" style="width:99%; padding:0px;"><select id="c_list_types" style="width:100%; margin:0px; padding:0px;"></select></li>';
	print '	</ul>';
	print '	<div id="c_list_customers" style="float:left"></div>';
	print '	<div id="c_list_customer_address" style="float:left"></div>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>