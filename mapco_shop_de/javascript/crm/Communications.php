<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	function communication_show(customer_id, order_id)
	{
		//if(user_id == 49352)
		{
			var post_object = 			new Object();
			post_object['API'] = 		'crm';
			post_object['APIRequest'] = 'ConversationGet2';
			post_object['customer_id'] =	customer_id;
			post_object['order_id'] =	order_id;
			post_object['conversation_id'] = 0;
			
			wait_dialog_show();
			$.post('<?php echo PATH;?>soa2/', post_object, function($data){
				//show_status2($data); return;
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
				if ( $xml.find("Ack").text()!="Success" ) { show_status2('Die Kommunikationsdaten wurden nicht gefunden.'); wait_dialog_hide(); return; }
				
				//Daten einlesen
				var conversation_data = new Array();
				var cnt = 0;
				var cnt2 = 0;
				var contact_order = 0;
				$xml.find("contact").each(function(){
					contact_order = $(this).find("con_order_id").text();
					if ( !conversation_data.hasOwnProperty(contact_order) )
					{
						cnt2=0;	
					}
					conversation_data[contact_order] = new Array();
					conversation_data[contact_order][cnt2] = new Array();
					conversation_data[contact_order][cnt2]["sender"] = $(this).find("con_from").text();
					conversation_data[contact_order][cnt2]["receiver"] = $(this).find("con_to").text();
					conversation_data[contact_order][cnt2]["subject"] = $(this).find("subject").text();
					conversation_data[contact_order][cnt2]["message"] = $(this).find("message").text();
					conversation_data[contact_order][cnt2]["firstmod"] = $(this).find("firstmod").text();
					conversation_data[contact_order][cnt2]["type"] = $(this).find("type").text();
					if($(this).find("con_order_id").text() == order_id)
						conversation_data[contact_order][cnt2]["con_type"] = "order";
					else
						conversation_data[contact_order][cnt2]["con_type"] = "all";
					cnt++;
					cnt2++;
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
				/*main.empty();
	
				var table = $('<table></table>');
				var tr = '';
				var td = '';
				tr = $('<tr></tr>');
				td = $('<td colspan="5" style="border: none;"</td>');
				var text = $('<p style="display: inline; font-weight: bold;">Anzeige:</p>');
				td.append(text);
				
				if ( order_id > 0 )
				{
					text = $('<p style="display: inline; padding-left: 50px;"><input type="radio" id="r_one" name="order_view" value="all"> alle Kontakte</p>');
					td.append(text);
					text = $('<p style="display: inline; padding-left: 20px"><input type="radio" id="r_two" name="order_view" value="one" checked> Kontakte zur aktuellen Bestellung</p>');
					td.append(text);
				}
				text = $('<span style="float: right; margin-left:5px;"><input type="button" id="contact_button" value="Kunden kontaktieren"></span>');
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

				var contacts = '<table>';
				contacts += '	<tr>';
				if ( order_id > 0 )
				{
					
					contacts += '		<td colspan="5" style="border: none;">';
					contacts += '			<p style="display: inline; font-weight: bold;">Anzeige:</p>';
					contacts += '			<p style="display: inline; padding-left: 50px;"><input type="radio" id="r_one" name="order_view" value="all"> alle Kontakte</p>';
					contacts += '			<p style="display: inline; padding-left: 20px"><input type="radio" id="r_two" name="order_view" value="one" checked> Kontakte zur aktuellen Bestellung</p>';
					contacts += '		</td>';
					
				}
				contacts += '		<td colspan="5" style="border: none;">';
				contacts += '			<span style="float: right; margin-left:5px;"><input type="button" id="contact_button" value="Kunden kontaktieren"></span>';
				contacts += '		</td>';
				contacts += '	</tr>';
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
*/
				for(var cid in conversation_data)
				{
					contacts += '	<tr>';
					contacts += '		<td id="cid">';
					for(var a in conversation_data[cid])
					{
						contacts += '<table>';
						var date = new Date(conversation_data[cid][a]["firstmod"]*1000);
						//if( conversation_data[cid][a]["con_type"] == 'all' && order_id > 0 )
						if( order_id > 0 )
						{
							contacts += '	<tr id="'+a+'_t" class="'+conversation_data[cid][a]["con_type"]+' toggle_msg" style="background-color: #CCCCCC; cursor: pointer; display: none; font-weight: bold;">';
						}
						else
						{
							contacts += '	<tr id="'+a+'_t" class="'+conversation_data[cid][a]["con_type"]+' toggle_msg" style="background-color: #CCCCCC; cursor: pointer; font-weight: bold;">';
						}
						contacts += '		<td>'+conversation_data[cid][a]["sender"]+'</td>';
						contacts += '		<td>'+conversation_data[cid][a]["receiver"]+'</td>';
						contacts += '		<td>'+conversation_data[cid][a]["subject"]+'</td>';
						contacts += '		<td>'+date.toLocaleString()+'</td>';
						contacts += '		<td>'+conversation_data[cid][a]["type"]+'</td>';
						contacts += '	</tr>';
						contacts += '	<tr>';
						contacts += '		<td colspan="5" id="'+a+'" class="'+conversation_data[cid][a]["con_type"]+'_art" style="background-color: #FFFFFF; display: none;">'+conversation_data[cid][a]["message"]+'</td>';
						ids.push(a);
						contacts += '	</tr>';
						if( order_id > 0 )
						{
							contacts += '<tr class="'+conversation_data[cid][a]["con_type"]+'" style="display: none; height: 10px;"></tr>';
						}
						else
						{
							contacts += '<tr class="'+conversation_data[cid][a]["con_type"]+'" style="height: 10px;"></tr>';
						}
						contacts += '</table>';	
					}
					contacts += '		</td>';
					contacts += '	</tr>';
				}
				contacts += '</table>';
				main.html(contacts);
				
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
				
				/*for(n in ids)
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
				}*/
				
				$(".toggle_msg").click(function () {
					var id = $(this).attr('id');
					id.split('_');
					id = "#"+id[0];
					$(id).toggle(500);
				});
				
				$("#r_one").change(function () {
					$('.all').show("fade", 500);
				});
				
				$("#r_two").change(function () {
					$('.all, .all_art').hide("fade", 500);
				});
				
				$('#contact_button').click(function(){
					//user_contact(order_id, con_types_data);
					user_contact_2(customer_id, order_id, 0);
				});
			});
		}
	}

	function user_contact_2(customer_id, order_id, conversation_id)
	{
		//if(user_id == 49352)
		{
			var post_object = 			new Object();
			post_object['API'] = 		'crm';
			post_object['APIRequest'] = 'ConversationGet2';
			post_object['order_id'] =	order_id;
			post_object['customer_id'] = customer_id;
			post_object['conversation_id'] = conversation_id;
			
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
				
				user_contact(order_id, con_types_data, $shop_mail, customer_id);
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
		/*	if(user_id == 49352)
			{
				td = $('<td style="font-weight: bold;">Nachricht/Text:</td><td><input type="button" value="Vorlage laden"></td>');
			}
			else*/
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