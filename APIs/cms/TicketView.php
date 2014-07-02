<?php
	//session_start();
	if ( !isset($_SESSION["id_user"]) or !($_SESSION["id_user"]>0) ) exit;

	$TicketID=$_POST["TicketID"];
	
// ermittle Tickets
	$sql = "select * from cms_conversations where conv_start_userid = '".$_SESSION["id_user"]."' or conv_partner_userid = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);

	while( $row=mysqli_fetch_array($results) ) 
	{

		if ($row["conv_start_userid"]==$_SESSION["id_user"])
		{
			$conversation[$row["id_conv"]]["starter"]=$_SESSION["id_user"];
			$conversation[$row["id_conv"]]["partner"]=$row["conv_partner_userid"];
			
		}
		else 
		{
			$conversation[$row["id_conv"]]["starter"]=$row["conv_start_userid"];
			$conversation[$row["id_conv"]]["partner"]=$_SESSION["id_user"];
		}
		
		
		$contact_starter=mysqli_fetch_array(q("select * from cms_contacts where idCmsUser = '".$conversation[$row["id_conv"]]["starter"]."'", $dbweb, __FILE__, __LINE__));
		$conversation[$row["id_conv"]]["starterName"]=$contact_starter["firstname"]." ".$contact_starter["lastname"];
		$conversation[$row["id_conv"]]["starterPosition"]=$contact_starter["position"];
		
		$contact_partner=mysqli_fetch_array(q("select * from cms_contacts where idCmsUser = '".$conversation[$row["id_conv"]]["partner"]."'", $dbweb, __FILE__, __LINE__));
		$conversation[$row["id_conv"]]["partnerName"]=$contact_partner["firstname"]." ".$contact_partner["lastname"];
		$conversation[$row["id_conv"]]["partnerPosition"]=$contact_partner["position"];

		$conversation[$row["id_conv"]]["ID"]=$row["id_conv"];
		$conversation[$row["id_conv"]]["start_date"]=$row["start_date"];
		$conversation[$row["id_conv"]]["state"]=$row["state"];
		$conversation[$row["id_conv"]]["first_post"]="";

		$sql2="select * from cms_conversations_posts where id_conv = '".$row["id_conv"]."' order by post_date";
		$results2=q($sql2, $dbweb, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) ) 
		{
			$conv_posts[$row["id_conv"]][$row2["id_post"]]["post_date"]=$row2["post_date"];
			$conv_posts[$row["id_conv"]][$row2["id_post"]]["id_cms_article"]=$row2["id_cms_article"];
			
			if ($conversation[$row["id_conv"]]["first_post"]=="") {$conversation[$row["id_conv"]]["first_post"]=$row2["id_post"];}
			
			//Letzten POST - User festhalten
			$conversation[$row["id_conv"]]["last_post_userid"]=$row2["post_userid"];
			
			$sql3="select title, article from cms_articles where id_article = '".$row2["id_cms_article"]."'";
			$results3=q($sql3, $dbweb, __FILE__, __LINE__);
			while( $row3=mysqli_fetch_array($results3) ) 
			{ 
				$conv_posts[$row["id_conv"]][$row2["id_post"]]["subject"]=$row3["title"];
				$conv_posts[$row["id_conv"]][$row2["id_post"]]["msg"]=$row3["article"];
				//$conv_posts[$row["id_conv"]][$row2["id_post"]]["sender"]=$row3["post_usermail"];
			}
		}
	}
	?>
    <?php

	// ANZEIGE TABS
	echo '<div style="float:left; display:inline;">';
	echo '<ul id="tabs" class="orderlist" style="width:200px;">';
	echo '<li class="header" style="width:188px;">Tickets</li>';
	echo '<li class="tabbox"><a id="tabTicketAll" href="javascript:show_ticket_all();">alle Tickets</a></li>';
	echo '<li class="tabbox"><a id="tabTicketRecieved"" href="javascript:show_ticket_recieved();">erhaltene Tickets</a></li>';
	echo '<li class="tabbox"><a id="tabTicketSent" href="javascript:show_ticket_sent();">gesendete Tickets</a></li>';
	echo '</ul>';
	echo '</div>';

	echo '<div id="msgs" style="width:1130px; height:670px; display:inline; float:left;">'; //umschließt MSG Tabs und MSG Box
	echo '<a class="tab" id="tabMsgAll" href="javascript:show_msg_all();">alle Nachrichten</a>';
	echo '<a class="tab" id="tabMsgSend" href="javascript:show_msg_send();">Antwort austehend</a>';
	echo '<a class="tab" id="tabMsgRecieved" href="javascript:show_msg_responded();">Nachrichten erhalten</a>';
	echo '<a class="tab" id="tabMsgTicketClosed" href="javascript:show_ticket_closed();">geschlossene Tickets</a>';


//MSG BOX
	// ANZEIGE RECIEVED
		echo '<div id="ticket_recieved" style="display:inline; float:left; width:1130px; height:630px; overflow:auto;">';
		echo '<table class="msg_box">';
		echo '<tr>';
		echo '	<th>Ticket Nr.</th>';
		echo '	<th>Betreff<img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/folder_full_add.png" alt="Neues Ticket aus Vorlage Senden" title="Neues Ticket aus Vorlage Senden" onclick="Ticketvorlage()" /><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Neues Ticket Senden" title="Neues Ticket Senden" onclick="conversationStart()" /></th>';
		echo '</tr>';
	// NO MSG Line
		echo '<tr id="noMsgLine_recieved" style="display:none"><td colspan="2"><b>Keine Nachricht in dieser Ansicht</b></td></tr>';
	//Prüfung auf Mindestens eine Conversation
	if (sizeof($conversation)>0) {	
		foreach ($conversation as $conv_id) 
		{
			if ($conv_id["partner"]==$_SESSION["id_user"]) 
			{
				// KLASSEN VORBEREITEN
				if ($conv_id["state"]=="closed") { $klasse_h="closed_h"; $klasse_b="closed_b";}
				else {
					if ($conv_id["last_post_userid"]==$_SESSION["id_user"]) { $klasse_h="send_h"; $klasse_b="send_b"; } else { $klasse_h="responded_h"; $klasse_b="responded_b";}
				} // klassen vorbereiten
				
				// NACHRICHTENKOPF / BETREFFZEILE
				echo "<tr id='tr0conversation".$conv_id["ID"]."recieved' class='".$klasse_h." msg_head ticket_recieved' onclick='show_msg(".$conv_id["ID"].", \"recieved\");' style='cursor:pointer;'>";
								
				if ($conv_id["state"]=="open") {
					if ($conv_id["last_post_userid"]==$_SESSION["id_user"]) 
					// WARTE AUF ANTWORT
					{
   	 	           		 echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left; cursor:pointer;" src="images/icons/24x24/mail_next.png" alt="Sie warten aus Antwort" title="Sie warten auf Antwort" />Ticket Nr.<b>'.$conv_id["ID"].'</b> von <b>'.$conv_id["starterName"].'</b> <small>'.$conv_id["starterPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
					}
					else
					// ANTWORT ERFORFDERLICH
					{
        	      	  echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/mail.png" alt="Sie müssen antworten" title="Sie müssen antworten" />Ticket Nr.<b>'.$conv_id["ID"].'</b> von <b>'.$conv_id["starterName"].'</b> <small>'.$conv_id["starterPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
					}
				} // if state >open<
				else 
				{
					// TICKET GESCHLOSSEN
					echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/accept.png" alt="Ticket ist abgeschlossen" title="Ticket ist abgeschlossen" />Ticket Nr.<b>'.$conv_id["ID"].'</b> von <b>'.$conv_id["starterName"].'</b> <small>'.$conv_id["starterPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
				} // else state not >open<
				
				// NACHRICHTENKOPF / BETREFFZEILE
                echo'<td id="subject'.$conv_id["ID"].'recieved"><b>'.$conv_posts[$conv_id["ID"]][$conv_id["first_post"]]["subject"].'</b></td>';
              	echo '</tr>';
				if ($conv_id["state"]=="open") 
				{
                echo '<tr id="tr1conversation'.$conv_id["ID"].'recieved" class="'.$klasse_b.' msg_body" style="display:none">';
					echo '<td colspan="2">';
					echo '<textarea name="reply" id="reply'.$conv_id["ID"].'recieved" cols="125" rows="10"></textarea>';
					if ($conv_id["state"]=="open")
					{
						echo "<img style='margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;' src='images/icons/32x32/forward_new_mail.png' alt='Antwort senden' title='Antwort senden' onclick='conversationSave(".$conv_id["ID"].",\"recieved\");' />";
					}
					echo '</td>';
                echo '</tr>';
				}
                echo '<tr id="tr2conversation'.$conv_id["ID"].'recieved" class="'.$klasse_b.' msg_body" style="display:none">';
                 	echo '<td colspan="2" id="msg_history'.$conv_id["ID"].'recieved"></td>';
                echo '</tr>';
			} // if partner >usermail<
		} // for each
	} // if sizeof conversation
					
		echo '</table>';
		echo '</div>';
		
	// ANZEIGE SENT
		echo '<div id="ticket_sent" style="display:inline; float:left; width:1130px; height:630px; overflow:auto;">';
		echo '<table class="msg_box">';
		echo '<tr>';
		echo '	<th>Ticket Nr.</th>';
		echo '	<th>Betreff<img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/folder_full_add.png" alt="Neues Ticket aus Vorlage Senden" title="Neues Ticket aus Vorlage Senden" onclick="Ticketvorlage()" /><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Neues Ticket Senden" title="Neues Ticket Senden" onclick="conversationStart()" /></th>';
		echo '</tr>';
	// NO MSG Line
		echo '<tr id="noMsgLine_sent" style="display:none"><td colspan="2"><b>Keine Nachricht in dieser Ansicht</b></td></tr>';
	if (sizeof($conversation)>0) {	
		foreach ($conversation as $conv_id) 
		{
			if ($conv_id["starter"]==$_SESSION["id_user"]) 
			{
				// KLASSEN VORBEREITEN
				if ($conv_id["state"]=="closed") { $klasse_h="closed_h"; $klasse_b="closed_b";}
				else {
					if ($conv_id["last_post_userid"]==$_SESSION["id_user"]) { $klasse_h="send_h"; $klasse_b="send_b"; } else { $klasse_h="responded_h"; $klasse_b="responded_b";}
				}
				echo "<tr id='tr0conversation".$conv_id["ID"]."sent' class='".$klasse_h." msg_head ticket_sent' onclick='show_msg(".$conv_id["ID"].", \"sent\");'>";
				if ($conv_id["state"]=="open") {
					if ($conv_id["last_post_userid"]==$_SESSION["id_user"]) 
					{
        		        echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/mail_next.png" alt="Sie warten aus Antwort" title="Sie warten auf Antwort" />Ticket Nr.<b>'.$conv_id["ID"].'</b> an <b>'.$conv_id["partnerName"].'</b> <small>'.$conv_id["partnerPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
					}
					else
					{
                		echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/mail.png" alt="Sie müssen antworten" title="Sie müssen antworten" />Ticket Nr.<b>'.$conv_id["ID"].'</b> an <b>'.$conv_id["partnerName"].'</b> <small>'.$conv_id["partnerPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
					}
				}
				else
				{
					echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/accept.png" alt="Ticket ist abgeschlossen" title="Ticket ist abgeschlossen" />Ticket Nr.<b>'.$conv_id["ID"].'</b> an <b>'.$conv_id["partnerName"].'</b> <small>'.$conv_id["partnerPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
				}
				
				if ($conv_id["state"]=="open") 
				{
					echo "<td id='subject".$conv_id["ID"]."sent'><b>".$conv_posts[$conv_id["ID"]][$conv_id["first_post"]]["subject"]."</b><img style='margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;' src='images/icons/24x24/accept.png' alt='Ticket schließen' title='Ticket schließen' onclick='showConfirm_closeTicket(".$conv_id["ID"].")' /></td>";
				}
				if ($conv_id["state"]=="closed") 
				{
					echo "<td id='subject".$conv_id["ID"]."sent'><b>".$conv_posts[$conv_id["ID"]][$conv_id["first_post"]]["subject"]."</b></td>";
				}
              	echo '</tr>';
				if ($conv_id["state"]=="open") 
				{
                echo '<tr id="tr1conversation'.$conv_id["ID"].'sent" class="'.$klasse_b.' msg_body" style="display:none">';
					echo '<td colspan="2">';
					echo '<textarea name="reply" id="reply'.$conv_id["ID"].'sent" cols="125" rows="10"></textarea>';
					if ($conv_id["state"]=="open")
					{ 
						echo "<img style='margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;' src='images/icons/32x32/forward_new_mail.png' alt='Antwort senden' title='Antwort senden' onclick='conversationSave(".$conv_id["ID"].",\"sent\");' />";
					}
				echo '</td>';
                echo '</tr>';
				}
                echo '<tr id="tr2conversation'.$conv_id["ID"].'sent" class="'.$klasse_b.' msg_body" style="display:none">';
                 	echo '<td colspan="2" id="msg_history'.$conv_id["ID"].'sent"></td>';
                echo '</tr>';
			}
		}
	}
	
		echo '</table>';
		echo '</div>';

		
		// ANZEIGE ALL		
		echo '<div id="ticket_all" style="display:inline; float:left; width:1130px; height:630px; overflow:auto;">';
		echo '<table class="msg_box">';
		echo '<tr>';
		echo '	<th>Ticket Nr.</th>';
		echo '	<th>Betreff<img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/folder_full_add.png" alt="Neues Ticket aus Vorlage Senden" title="Neues Ticket aus Vorlage Senden" onclick="Ticketvorlage()" /><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right; cursor:pointer;" src="images/icons/24x24/add.png" alt="Neues Ticket Senden" title="Neues Ticket Senden" onclick="conversationStart()" /></th>';
		echo '</tr>';
	// NO MSG Line
		echo '<tr id="noMsgLine_all" style="display:none"><td colspan="2"><b>Keine Nachricht in dieser Ansicht</b></td></tr>';
	if (sizeof($conversation)>0) {
		foreach ($conversation as $conv_id) 
		{
				// KLASSEN VORBEREITEN
				if ($conv_id["state"]=="closed") { $klasse_h="closed_h"; $klasse_b="closed_b";}
				else {
					if ($conv_id["last_post_userid"]==$_SESSION["id_user"]) { $klasse_h="send_h"; $klasse_b="send_b"; } else { $klasse_h="responded_h"; $klasse_b="responded_b";}
				}
				echo "<tr id='tr0conversation".$conv_id["ID"]."all' class='".$klasse_h." msg_head ticket_all' onclick='show_msg(".$conv_id["ID"].", \"all\");'>";
			if ($conv_id["starter"]==$_SESSION["id_user"]) {
				if ($conv_id["state"]=="open") {
					if ($conv_id["last_post_userid"]==$_SESSION["id_user"]) 
					{
        		        echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/mail_next.png" alt="Sie warten aus Antwort" title="Sie warten auf Antwort" />Ticket Nr.<b>'.$conv_id["ID"].'</b> an <b>'.$conv_id["partnerName"].'</b> <small>'.$conv_id["partnerPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
					}
					else
					{
                		echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/mail.png" alt="Sie müssen antworten" title="Sie müssen antworten" />Ticket Nr.<b>'.$conv_id["ID"].'</b> an <b>'.$conv_id["partnerName"].'</b> <small>'.$conv_id["partnerPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
					}
				}
				else
				{
					echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/accept.png" alt="Ticket ist abgeschlossen" title="Ticket ist abgeschlossen" />Ticket Nr.<b>'.$conv_id["ID"].'</b> an <b>'.$conv_id["partnerName"].'</b> <small>'.$conv_id["partnerPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
				}
			}
			if ($conv_id["partner"]==$_SESSION["id_user"]) {
				if ($conv_id["state"]=="open") {
					if ($conv_id["last_post_userid"]==$_SESSION["id_user"]) 
					// WARTE AUF ANTWORT
					{
   	 	           		 echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/mail_next.png" alt="Sie warten aus Antwort" title="Sie warten auf Antwort" />Ticket Nr.<b>'.$conv_id["ID"].'</b> von <b>'.$conv_id["starterName"].'</b> <small>'.$conv_id["starterPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
					}
					else
					// ANTWORT ERFORFDERLICH
					{
        	      	  echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/mail.png" alt="Sie müssen antworten" title="Sie müssen antworten" />Ticket Nr.<b>'.$conv_id["ID"].'</b> von <b>'.$conv_id["starterName"].'</b> <small>'.$conv_id["starterPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
					}
				}
				else 
				{
					// TICKET GESCHLOSSEN
					echo '<td style="width:450px"><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:left;" src="images/icons/24x24/accept.png" alt="Ticket ist abgeschlossen" title="Ticket ist abgeschlossen" />Ticket Nr.<b>'.$conv_id["ID"].'</b> von <b>'.$conv_id["starterName"].'</b> <small>'.$conv_id["starterPosition"].'</small><br><small>'.date("d.m.Y H:i",$conv_id["start_date"]).'</small></td>';
				}
			}
			
			if ($conv_id["starter"]==$_SESSION["id_user"] && $conv_id["state"]=="open") {
				echo "<td id='subject".$conv_id["ID"]."all'><b>".$conv_posts[$conv_id["ID"]][$conv_id["first_post"]]["subject"]."</b><img style='margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;' src='images/icons/24x24/accept.png' alt='Ticket schließen' title='Ticket schließen'  onclick='showConfirm_closeTicket(".$conv_id["ID"].")' /></td>";
			}
			if ($conv_id["starter"]==$_SESSION["id_user"] && $conv_id["state"]=="closed") {
				echo "<td id='subject".$conv_id["ID"]."all'><b>".$conv_posts[$conv_id["ID"]][$conv_id["first_post"]]["subject"]."</b></td>";
			}

			if ($conv_id["partner"]==$_SESSION["id_user"]) {
				echo "<td id='subject".$conv_id["ID"]."all'><b>".$conv_posts[$conv_id["ID"]][$conv_id["first_post"]]["subject"]."</b></td>";
			}
              	echo '</tr>';
			if ($conv_id["state"]=="open") {
                echo '<tr id="tr1conversation'.$conv_id["ID"].'all" class="'.$klasse_b.' msg_body" style="display:none">';
					echo '<td colspan="2">';
					echo '<textarea name="reply" id="reply'.$conv_id["ID"].'all" cols="125" rows="10"></textarea>';
					if ($conv_id["state"]=="open")
					{
						echo "<img style='margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;' src='images/icons/32x32/forward_new_mail.png' alt='Antwort senden' title='Antwort senden' onclick='conversationSave(".$conv_id["ID"].",\"all\");' />";
					}
					echo '</td>';
                echo '</tr>';
			}
                echo '<tr id="tr2conversation'.$conv_id["ID"].'all" class="'.$klasse_b.' msg_body" style="display:none">';
                 	echo '<td colspan="2" id="msg_history'.$conv_id["ID"].'all"></td>';
                echo '</tr>';
		}
	}
	
		echo '</table>';
		echo '</div>';
	echo '</div>'; //umschließt MSG Tabs und MSG Box

	
//ANZEIGE TICKET AUS MAIL - LINK
	if ($TicketID<>"notset")
	{

		if ($conversation[$TicketID]["starter"]==$_SESSION["id_user"])

		{
			echo '<script>show_ticket_sent();</script>';
			$TicketType="sent";
		}
		else
		{
			echo '<script>show_ticket_recieved();</script>';
			$TicketType="recieved";
		}

		
		//CHECK AUF KATEGORIE RECIEVED/SENT/CLOSED
		if ($conversation[$TicketID]["state"]=="closed") 
		{
			echo '<script>show_ticket_closed();</script>';
		}
		else {
		//CHECK AUF MSG SEND / RESPONDED
			if ($conversation[$TicketID]["last_post_userid"]==$_SESSION["id_user"]) 
			{
				echo '<script>show_msg_send();</script>';
			}
			else
			{
				echo '<script>show_msg_responded();</script>';
			}
		}
		
	echo '<script>show_msg('.$TicketID.', "'.$TicketType.'");</script>';
	}
?>
