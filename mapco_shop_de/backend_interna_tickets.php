<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	$TicketID="notset";
	if (isset($_GET["TicketID"]) && $_GET["TicketID"]!=="") {$TicketID=$_GET["TicketID"];}

?>
    	<style>
			.tab
			{
				width:210px;
				margin:0;
				border:1px solid #ccc;
				padding:5px;
				text-align:center;
				text-decoration:none;
				cursor:pointer;
				float:left;			
			}
			.msg_head
			{
				cursor:pointer;
				border-color:#000000;
			}
			.tabbox
			{
				width:188px; 
				height:25px;
				background:#ffffff;
			}
			.msg_box
			{
				width:1100px;
				margin:0;
				rules:rows;
			}
		</style>


<script type="text/javascript">

	function show_ticket_recieved() 
	{

		$("#ticket_recieved").show();
		$("#tabTicketRecieved").css("font-weight","bold");
		$("#ticket_sent").hide();
		$("#tabTicketSent").css("font-weight","normal");
		$("#ticket_all").hide();
		$("#tabTicketAll").css("font-weight","normal");
		
		//Nachrichten wieder "zuklappen"
		$(".msg_body").hide();
	}
		
	function show_ticket_sent() 
	{
		$("#ticket_recieved").hide();
		$("#tabTicketRecieved").css("font-weight","normal");
		$("#ticket_sent").show();
		$("#tabTicketSent").css("font-weight","bold");
		$("#ticket_all").hide();
		$("#tabTicketAll").css("font-weight","normal");
		
		//Nachrichten wieder "zuklappen"
		$(".msg_body").hide();
	}

	function show_ticket_all() 
	{
		$("#ticket_recieved").hide();
		$("#tabTicketRecieved").css("font-weight","normal");
		$("#ticket_sent").hide();
		$("#tabTicketSent").css("font-weight","normal");
		$("#ticket_all").show();
		$("#tabTicketAll").css("font-weight","bold");
		
		//Nachrichten wieder "zuklappen"
		$(".msg_body").hide();
	}

	function show_ticket_closed()
	{
		$(".send_h").hide();
		$(".send_b").hide();
		$(".responded_h").hide();
		$(".responded_b").hide();
		$(".closed_h").show();
		$(".closed_b").hide();
		
		$("#tabMsgSend").css("font-weight","normal");
		$("#tabMsgRecieved").css("font-weight","normal");
		$("#tabMsgAll").css("font-weight","normal");
		$("#tabMsgTicketClosed").css("font-weight","bold");		

		if ($(".closed_h.ticket_recieved").length==0) { $("#noMsgLine_recieved").show();}  else {$("#noMsgLine_recieved").hide();}
		if ($(".closed_h.ticket_sent").length==0) { $("#noMsgLine_sent").show();} else {$("#noMsgLine_sent").hide();}
		if ($(".closed_h.ticket_all").length==0) { $("#noMsgLine_all").show();} else {$("#noMsgLine_all").hide();}
	}

	function show_msg_send()
	{
		$(".send_h").show();
		$(".send_b").hide();
		$(".responded_h").hide();
		$(".responded_b").hide();
		$(".closed_h").hide();
		$(".closed_b").hide();
		
		$("#tabMsgSend").css("font-weight","bold");
		$("#tabMsgRecieved").css("font-weight","normal");
		$("#tabMsgAll").css("font-weight","normal");
		$("#tabMsgTicketClosed").css("font-weight","normal");		
		
		if ($(".send_h.ticket_recieved").length==0) { $("#noMsgLine_recieved").show();} else {$("#noMsgLine_recieved").hide();}
		if ($(".send_h.ticket_sent").length==0) { $("#noMsgLine_sent").show();} else {$("#noMsgLine_sent").hide();}
		if ($(".send_h.ticket_all").length==0) { $("#noMsgLine_all").show();} else {$("#noMsgLine_all").hide();}
	}
	
	function show_msg_responded()
	{
		$(".send_h").hide();
		$(".send_b").hide();
		$(".responded_h").show();
		$(".responded_b").hide();
		$(".closed_h").hide();
		$(".closed_b").hide();
		
		$("#tabMsgSend").css("font-weight","normal");
		$("#tabMsgRecieved").css("font-weight","bold");
		$("#tabMsgAll").css("font-weight","normal");
		$("#tabMsgTicketClosed").css("font-weight","normal");		
		
		if ($(".responded_h.ticket_recieved").length==0) { $("#noMsgLine_recieved").show();}  else {$("#noMsgLine_recieved").hide();}
		if ($(".responded_h.ticket_sent").length==0) { $("#noMsgLine_sent").show();} else {$("#noMsgLine_sent").hide();}
		if ($(".responded_h.ticket_all").length==0) { $("#noMsgLine_all").show();} else {$("#noMsgLine_all").hide();}
	}

	function show_msg_all()
	{
		$(".send_h").show();
		$(".send_b").hide();
		$(".responded_h").show();
		$(".responded_b").hide();
		$(".closed_h").hide();
		$(".closed_b").hide();
		
		$("#tabMsgSend").css("font-weight","normal");
		$("#tabMsgRecieved").css("font-weight","normal");
		$("#tabMsgAll").css("font-weight","bold");
		$("#tabMsgTicketClosed").css("font-weight","normal");	
		
		if ($(".responded_h.ticket_recieved").length==0 && $(".send_h.ticket_recieved").length==0) { $("#noMsgLine_recieved").show();}  else {$("#noMsgLine_recieved").hide();}
		if ($(".responded_h.ticket_sent").length==0 && $(".send_h.ticket_sent").length==0) { $("#noMsgLine_sent").show();} else {$("#noMsgLine_sent").hide();}
		if ($(".responded_h.ticket_all").length==0 && $(".send_h.ticket_all").length==0) { $("#noMsgLine_all").show();} else {$("#noMsgLine_all").hide();}
	}


	function show_msg(conv_id,ticketType)
	{
		if ($("#tr2conversation"+conv_id+ticketType+":visible").length>0)
		{
			$("#tr2conversation"+conv_id+ticketType).hide();
			$("#tr1conversation"+conv_id+ticketType).hide();

		}
		else 
		{
			$("#tr2conversation"+conv_id+ticketType).show();
			$("#tr1conversation"+conv_id+ticketType).show();
			
			wait_dialog_show();
			get_msgHistory(conv_id,ticketType);
			wait_dialog_hide();

		}
		
	}
	
	function get_msgHistory(conv_id,messageField)
	{
		if ($("#msg_history"+conv_id+messageField).html()=="") 
		{
			$.post("<?php echo PATH; ?>soa/", {API: "cms", Action: "TicketGetConversation", conv_id:conv_id},
			
			function (data) {
				
				if (data!="") {$("#msg_history"+conv_id+messageField).html(data)};
				
				}
			);
		}


	}

		function wait_dialog_show()
		{
			wait_dialog_timer=setTimeout("wait_dialog_show2();", 500);
		}
		
		function wait_dialog_show2()
		{
			$("#wait_dialog").dialog
			({	closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Bitte warten...",
				width:200
			});
		}
		
		function wait_dialog_hide()
		{
			clearTimeout(wait_dialog_timer);
			$("#wait_dialog").dialog("close");
		}

		function view(TicketID)  
//		function view()
		{
			$.post("<?php echo PATH; ?>soa/", {API: "cms", Action: "TicketView", TicketID:TicketID},
				function (data)
				{
					$("#view").html(data);
					if (TicketID=="notset")
						{
						show_ticket_all();
						show_msg_all();
						}
				}
			
			);
	
		}

		function conversationStart() {
			
			$("#CoversationStartDialogContact").val("");
			$("#CoversationStartDialogSubject").val("");
			$("#CoversationStartDialogMessage").val("");

			$("#ConversationStartDialog").dialog
			({	buttons:
				[
					{ text: "Nachricht senden", click: function() { conversationStartSave(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Nachricht verfassen",
				width:900
			});		
			
		}
		
		function conversationStartSave() {
			
			wait_dialog_show();
			
			var contact = $("#CoversationStartDialogContact").val();
			var subject = $("#CoversationStartDialogSubject").val();
			var message = $("#CoversationStartDialogMessage").val();
			
			$.post("<?php echo PATH; ?>soa/", { API: "cms", Action: "TicketAdd", contact:contact, subject:subject, message:message},
			
				function(data)
				{
					if (data!="") 
					{
						alert(data);
						$("#ConversationStartDialog").dialog("close");
					}
					else
					{
						
						$("#ConversationStartDialog").dialog("close");
						show_status("Daten wurden erfolgreich gespeichert!");
						view("notset");
						$("#CoversationStartDialogContact").val("");
						$("#CoversationStartDialogSubject").val("");
						$("#CoversationStartDialogMessage").val("");

						
					}
					wait_dialog_hide();
				}
			);
		} // FUNCTION conversationStartSave
		
		function conversationSave (id_conv, ticketType) {
			
			wait_dialog_show();

			var message=$("#reply"+id_conv+ticketType).val();
			
			$.post("<?php echo PATH; ?>soa/", { API: "cms", Action: "TicketResponse", id_conv:id_conv, message:message},
			
			function(data2)
				{
					if (data2!="") alert(data2);
				
					else
					{
						show_status("Daten wurden erfolgreich gespeichert!");
						view("notset");
					}
					
					wait_dialog_hide();
				}
			);
		}
		
		function enquirySteeringGear() {
			
			//ALTE FORMULARDATEN LÖSCHEN
			$("#enquirySteeringGear_article1").val("");
			$("#enquirySteeringGear_article2").val("");
			$("#enquirySteeringGear_reason1").val("");
			$("#enquirySteeringGear_reason2").val("");
			$("#enquirySteeringGear_reason3").val("");
			$("#enquirySteeringGear_maunfacturer").val("");
			$("#enquirySteeringGear_kbahsn").val("");
			$("#enquirySteeringGear_kbatsn").val("");
			$("#enquirySteeringGear_vehicleID").val("");
			$("#enquirySteeringGear_client").val("");
			$("#enquirySteeringGear_pricelist").val("");
			$("#enquirySteeringGear_comment").val("");

			$("#enquirySteeringGearDialog").dialog
			({	buttons:
				[
					{ text: "Anfrage senden", click: function() { enquirySteeringGearSave(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Anfrage bezüglich Lenkgetriebe / Servopumpen senden",
				width:500
			});		
			
		}

		
		function enquirySteeringGearSave()
		{ 
			
		
			var contact="28625";
			var subject="Anfrage bezüglich Lenkgetriebe/Servopumpe";
			var article = $("#enquirySteeringGear_article").val();
			var reason=$("#enquirySteeringGear_reason").val();
			var maunfacturer = $("#enquirySteeringGear_maunfacturer").val();
			var kbahsn = $("#enquirySteeringGear_kbahsn").val();
			var kbatsn = $("#enquirySteeringGear_kbatsn").val();
			var vehicleID = $("#enquirySteeringGear_vehicleID").val();
			var client = $("#enquirySteeringGear_client").val();
			var pricelist = $("#enquirySteeringGear_pricelist").val();
			var comment = $("#enquirySteeringGear_comment").val();
						
			//EINGABEPRÜFUNG <Ausgefüllt>
			var msg="";
			if (article=="") {if (msg=="") msg="Artikel"; else msg+="\n Artikel";}
			if (reason=="") {if (msg=="") msg="Anfragegrund"; else msg+="\n Anfragegrund";}
			if (maunfacturer=="") {if (msg=="") msg="Fahrzeughersteller"; else msg+="\n Fahrezeughersteller";}
			if (kbahsn=="") {if (msg=="") msg="Schlüsselnummer zu 2"; else msg+="\n Schlüsselnummer zu 2";}
			if (kbatsn=="") {if (msg=="") msg="Schlüsselnummer zu 3"; else msg+="\n Schlüsselnummer zu 3";}
			if (vehicleID=="") {if (msg=="") msg="Fahrgestellnummer"; else msg+="\n Fahrgestellnummer";}
			if (client=="") {if (msg=="") msg="Kunde"; else msg+="<br>Kunde";}
			if (pricelist=="") {if (msg=="") msg="Preisliste des Kunden"; else msg+="\n Preisliste des Kunden";}
			
			if (msg!="") {
				alert("Es müssen noch folgende Felder ausgefüllt werden: \n"+msg);
			}
			else 
			{
				wait_dialog_show();

			//MESSAGE erstellen
				if (article=="Lenkgetriebe") {var article_txt="eines <b>Lenkgetriebes</b>";} else {var article_txt="einer <b>Servopumpe</b>";}
				var message="";
				message ="<p>Anfrage bezüglich "+article_txt+"<br />";
				message+="<b>Anfragegrund:</b> "+reason+"</p>";
			
				message+="<p><table><tr><th>Fahrzeugdaten:</th></tr>";
				message+="<tr><td><b>Hersteller: </b></td><td>"+maunfacturer+"</td></tr>";
				message+="<tr><td><b>Schlüsselnummern: </b></td><td>"+kbahsn+"|"+kbatsn+"</td></tr>";
				message+="<tr><td><b>Fahrgestellnummer: </b></td><td>"+vehicleID+"</td></tr></table></p>";
			
				message+="<p><b>Kunde: </b>"+client+"<br />";
				message+="<p><b>Preisliste des Kunden: </b>"+pricelist+"</p>";
			
				if (comment!="") {
					message+="<p><b>Kommentar:</b><br />";
					message+=comment+"</p>";
				}
			}
			
			
			$.post("<?php echo PATH; ?>soa/", { API: "cms", Action: "TicketAdd", contact:contact, subject:subject, message:message},
			
				function(data)
				{
					if (data!="") 
					{
						alert(data);
						$("#enquirySteeringGearDialog").dialog("close");
					}
					else
					{
						
						$("#enquirySteeringGearDialog").dialog("close");
						show_status("Daten wurden erfolgreich gespeichert!");
						view("notset");
						
					}
					wait_dialog_hide();
				}
			);
			
		} // FUNCTION enquirySteeringGear


		function enquiryItemOrder() {
			
			//ALTE FORMULARDATEN LÖSCHEN
			$("#enquiryItemOrder_Contact").val("");
			$("#enquiryItemOrder_Item").val("");
			$("#enquiryItemOrder_qnt").val("");
			$("#enquiryItemOrder_comment").val("");

			$("#enquiryItemOrderDialog").dialog
			({	buttons:
				[
					{ text: "Anfrage senden", click: function() { enquiryItemOrderSave(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Anfrage bezüglich der Beschaffung von Artikeln senden",
				width:500
			});		
			
		}

		
		function enquiryItemOrderSave()
		{ 
		
			var contact=$("#enquiryItemOrder_Contact").val();
			var subject="Anfrage zur Beschaffung des Artikels "+$("#enquiryItemOrder_Item").val();
			var article = $("#enquiryItemOrder_Item").val();
			var qnt = $("#enquiryItemOrder_qnt").val();
			var comment = $("#enquiryItemOrder_comment").val();
						
			//EINGABEPRÜFUNG <Ausgefüllt>
			var msg="";
			if (contact=="") {if (msg=="") msg="Kontakt"; else msg+="\n Kontakt";}
			if (article=="") {if (msg=="") msg="Artikel"; else msg+="\n Artikel";}
			if (qnt=="") {if (msg=="") msg="vorgeschlagene Anzahl"; else msg+="\n vorgeschlagene Anzahl";}
			
			if (msg!="") 
			{
				alert("Es müssen noch folgende Felder ausgefüllt werden: \n"+msg);
			}
			else 
			{
				wait_dialog_show();

			//MESSAGE erstellen
				var message="";
				message ="<p><b>Beschaffungsanfrage bezüglich des MAPCO Artikels "+article+"</b></p>";
				message+="<p>Folgende Beschaffungsmenge wird vorgeschlagen: "+qnt+"</p>";
			
				if (comment!="") {
					message+="<p><b>Kommentar / weitere Infos:</b><br />";
					message+=comment+"</p>";
				}
			
			
			
				$.post("<?php echo PATH; ?>soa/", { API: "cms", Action: "TicketAdd", contact:contact, subject:subject, message:message},
			
					function(data)
					{
						if (data!="") 
						{
							alert(data);
							$("#enquiryItemOrderDialog").dialog("close");
						}
						else
						{
						
							$("#enquiryItemOrderDialog").dialog("close");
							show_status("Das Ticket wurde erfolgreich versendet!");
							view("notset");
						
						}
						wait_dialog_hide();
					}
				
				);
			}
			
		} // FUNCTION enquirySteeringGear


		
		function showConfirm_closeTicket(conv_id)
		{
			$("#ConversationTicketCloseDialog").dialog
			({	buttons:
				[
					{ text: "Ticket schließen", click: function() { conversationTicketClose(conv_id); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Bestätigung",
				width:900
			});		

		}
         
		function conversationTicketClose(conv_id)
		{
			
			
			$.post("<?php echo PATH; ?>soa/", { API: "cms", Action: "TicketCloseConversation", conv_id:conv_id},
				function(data)
				{
					if (data!="") show_status2(data);
					else
					{
						$("#ConversationTicketCloseDialog").dialog("close");
						show_status("Die Änderungen wurden erfolgreich gespeichert!");
						view("notset");
					}
					
				}
			);
		}

		function Ticketvorlage()
		{
			$("#TicketvorlageDialog").dialog
			({	buttons:
				[
					{ text: "Ticketvorlage wählen", click: function() { 
					
		//	alert($(".Ticketvorlage_auswahl").filter(":checked").val());
							switch ($(".Ticketvorlage_auswahl").filter(":checked").val())
							{
								case "1":	$("#TicketvorlageDialog").dialog("close"); enquirySteeringGear(); break;
								case "2":	$("#TicketvorlageDialog").dialog("close"); enquiryItemOrder(); break;
								
								default: 	alert("Es wurde keine Formatvorlage ausgewählt"); break;
							}
							
							 } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Auswahl Ticketvorlage",
				width:500
			});		


		}
			
</script>
<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_interna_index.php">Interna</a>';
	echo ' > Ticketsystem';
	echo '</p>';
	
	echo '<p>';
	echo '<h1>Ticketsystem</h1>';
	echo '</p>';
	
	//VIEW
	echo '<div id="view" style="display:inline; float:left;">';
	echo '</div>';
	echo '<script>view("'.$TicketID.'");</script>';
	
	//DIALOG AUSWAHL TICKETVORLAGE
	echo '<div id="TicketvorlageDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td><input type="radio" class="Ticketvorlage_auswahl" name="Ticketvorlage" id="TicketVorlage1" value="1"></td>';
	echo '	<td><label for="TicketVorlage1">Anfrageprotokoll bezüglich Lenkgetriebe / Servopumpen senden (an Stefan Habermann)</label></td>';
	echo '</tr><tr>';
	echo '	<td><input type="radio" class="Ticketvorlage_auswahl" name="Ticketvorlage" id="TicketVorlage2" value="2"></td>';
	echo '	<td><label for="TicketVorlage2">Anfrage zur Artikelbeschaffung senden (an Produktmanager)</label></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';	

	//DIALOG CONVERSATION START
	echo '<div id="ConversationStartDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Nachricht senden an: </td>';
	// SELECT CONTACT
	echo '	<td><select name="contact" size="1" id="CoversationStartDialogContact" />';
	echo '			<option value="">Bitte Kontakt auswählen</option>';
		$results2=q("select a.idCmsUser, a.position, a.firstname, a.lastname, a.department_id, b.id_department, b.location_id, c.id_location, c.location from cms_contacts a, cms_contacts_departments b, cms_contacts_locations c 
where a.department_id=b.id_department and b.location_id=c.id_location and not a.idCmsUser = 0 order by c.location, a.lastname;", $dbweb, __FILE__, __LINE__);
		$first_opt=true;
		while( $row2=mysqli_fetch_array($results2) ) {
		
			if ($first_opt) { echo '<optgroup label="'.$row2["location"].'">'; $first_opt=false; $lastLocation=$row2["location"]; }
			if (!$first_opt && $row2["location"]!=$lastLocation) 
			{
					echo '</optgroup>';
					echo '<optgroup label='.$row2["location"].'>';
			}
			echo '	<option value='.$row2["idCmsUser"].'>'.$row2["lastname"].', '.$row2["firstname"].' - '.$row2["position"].'</option>';
			$lastLocation=$row2["location"];
			
		}
	echo '		</optgroup>';
	echo '	</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Betreff</td>';
	echo '	<td><input type="text" name="subject" id="CoversationStartDialogSubject" size="90" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Nachricht</td>';
	echo '	<td><textarea name="message" id="CoversationStartDialogMessage" cols="90" rows="20"></textarea></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//DIALOG ANFRAGHE LENKGETRIEBE/SERVOPUMPE
	echo '<div id="enquirySteeringGearDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Artikel</td>';
	echo '	<td><select name="article" id="enquirySteeringGear_article" size="1">';
		echo '<option value="">Bitte Artikel auswählen</option>';
		echo '<option value="Lenkgetriebe">Lenkgetriebe</option>';
		echo '<option value="Servopumpe">Servopumpe</option>';
		echo '</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Grund</td>';
	echo '	<td><select name="reason" id="enquirySteeringGear_reason" size="1">';
		echo '<option value="">Bitte Anfragegrund auswählen</option>';
		echo '<option value="Aufarbeitung">Aufarbeitung</option>';
		echo '<option value="Beschaffung Neuteil">Beschaffung Neuteil</option>';
		echo '<option value="Andere">Andere</option>';
		echo '</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Fahrzeughersteller</td>';
	echo '	<td><input type="text" name="maunfacturer" id="enquirySteeringGear_maunfacturer" size="30" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Schlüsselnummern</td>';
	echo '	<td>zu 2 (oder 2.1) <input type="text" name="kbahsn" id="enquirySteeringGear_kbahsn" size="4" /><br />';
	echo '	zu 3 (oder 2.2) <input type="text" name="kbatsn" id="enquirySteeringGear_kbatsn" size="4" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Fahrgestellnummer</td>';
	echo '	<td><input type="text" name="vehicleID" id="enquirySteeringGear_vehicleID" size="20" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Kunde</td>';
	echo '	<td><input type="text" name="client" id="enquirySteeringGear_client" size="20" /></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Preisliste des Kunden</td>';
		echo '<td><select name="pricelist" size="1" id="enquirySteeringGear_pricelist">';
		echo '<option value="">Bitte eine Preisliste wählen</option>';
		echo '<option value="Rot">Rot</option>';
		echo '<option value="Orange">Orange</option>';
		echo '<option value="Gelb">Gelb</option>';
		echo '<option value="Grün">Grün</option>';
		echo '<option value="Blau">Blau</option>';
		echo '<option value="Andere">Andere</option>';
	echo '	</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Kommentar</td>';
	echo '	<td><textarea name="comment" id="enquirySteeringGear_comment" cols="40" rows="7"></textarea></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

	//DIALOG ARTIKEL BESCHAFFUNGSANFRAGE
	echo '<div id="enquiryItemOrderDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Beschaffungsanfrage an: </td>';
		// SELECT CONTACT
	echo '	<td><select name="contact" size="1" id="enquiryItemOrder_Contact">';
	echo '			<option value="">Bitte Kontakt auswählen</option>';
		$results2=q("select idCmsUser, position, firstname, lastname, department_id from cms_contacts where department_id='2' order by lastname;", $dbweb, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) ) {
			echo '	<option value='.$row2["idCmsUser"].'>'.$row2["lastname"].', '.$row2["firstname"].' - '.$row2["position"].'</option>';
		}
	echo '	</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>zu bestellender Artikel</td>';
	echo '	<td><input type="text" name="item" id="enquiryItemOrder_Item" size="10" />';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>vorgeschlagene Anzahl</td>';
	echo '	<td><input type="text" name="qnt" id="enquiryItemOrder_qnt" size="4" />';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Bemerkungen / weitere Infos</td>';
	echo '	<td><textarea name="comment" id="enquiryItemOrder_comment" cols="50" rows="10"></textarea></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//DIALOG CONVERSATION START
	echo '<div id="ConversationTicketCloseDialog" style="display:none;">';
	echo 'Möchten Sie das Ticket schließen?';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>