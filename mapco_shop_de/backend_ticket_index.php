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

	//DIALOG CONVERSATION START
	echo '<div id="ConversationStartDialog" style="display:none;">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Nachricht senden an: </td>';
	// SELECT CONTACT
	echo '	<td><select name="contact" size="1" id="CoversationStartDialogContact" />';
	echo '			<option value="">Bitte Kontakt auswählen</option>';
		$results2=q("select a.idCmsUser, a.position, a.firstname, a.lastname, a.department_id, b.id_department, b.location_id, c.id_location, c.location from cms_contacts a, cms_contacts_departments b, cms_contacts_locations c 
where a.department_id=b.id_department and b.location_id=c.id_location and not a.idCmsUser = 0 order by c.location;", $dbweb, __FILE__, __LINE__);
		$first_opt=true;
		while( $row2=mysql_fetch_array($results2) ) {
		
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
	
	//DIALOG CONVERSATION START
	echo '<div id="ConversationTicketCloseDialog" style="display:none;">';
	echo 'Möchten Sie das Ticket schließen?';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>