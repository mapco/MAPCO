<?php

include("../../mapco_shop_de/config.php");
include("../../mapco_shop_de/functions/cms_send_html_mail.php");


//FUNCTION erhöht timestamp($date_from) um die Anzahl angegebener Tage, berücksichtigt nur die Werktage (MO-FR)
function find_next_day($date_from, $werktage)
{
	//$date_from=number_format($date_from);
	$date_from=$date_from*1;
	while (date("N", $date_from+86400*$werktage)>5) $werktage++;
	return $date_from+86400*$werktage;
}

function find_contact_from_conversation($conv_id)
{
	global $dbweb;
	//CONVERSATION STARTER
	$res=q("SELECT a.lastname, a.firstname, a.position, a.mail FROM cms_contacts AS a, cms_conversations AS b WHERE b.id_conv = '".$conv_id."' AND b.conv_start_userid = a.idCmsUser;", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==1) {
		$row=mysqli_fetch_array($res);
		$contact["starter"]["name"]=$row["firstname"].' '.$row["lastname"];
		$contact["starter"]["position"]=$row["position"];
		$contact["starter"]["mail"]=$row["mail"];
	}
	
	//CONVERSATION PARTNER
	$res=q("SELECT a.lastname, a.firstname, a.position, a.mail FROM cms_contacts AS a, cms_conversations AS b WHERE b.id_conv = '".$conv_id."' AND b.conv_partner_userid = a.idCmsUser;", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==1) {
		$row=mysqli_fetch_array($res);
		$contact["partner"]["name"]=$row["firstname"].' '.$row["lastname"];
		$contact["partner"]["position"]=$row["position"];
		$contact["partner"]["mail"]=$row["mail"];
	}
return $contact;
}

function find_conversation_subject($conv_id)
{
	global $dbweb;
	$res=q("SELECT b.title FROM cms_conversations_posts AS a, cms_articles AS b WHERE a.id_conv = '".$conv_id."' AND a.id_cms_article=b.id_article ORDER BY a.post_date LIMIT 0,1;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	
return $row["title"];
}

function send_reminder_mail($type, $id_conv, $contact){

	//MAIL REMIND RESPONSE
	if ($type=="remind_response") 
	{
		$Subject ='ERINNERUNG: Bitte auf das Ticket Nr.'.$id_conv.' von '.$contact["starter"]["name"].' antworten';
		$MsgText ='<p>Ihre Antwort auf folgendes Ticket <b>'.find_conversation_subject($id_conv).'</b> von '.$contact["starter"]["name"].' <small>('.$contact["starter"]["position"].')</small> steht aus.</p>';
		$MsgText.='<p>Auf die Nachricht / das Ticket Nr.'.$id_conv.' k&ouml;nnen Sie im <a href="http://www.mapco.de/backend_interna_tickets.php?lang=de&id_menuitem=201&TicketID='.$id_conv.'">Ticket-System</a> antworten</p>';
		$MsgText.='<p>Vielen Dank!</p>';
		
		send_ticket_mail($contact["partner"]["mail"], $Subject, $MsgText);
	//	send_ticket_mail('nputzing@mapco.de','@'.$contact["partner"]["mail"].'-'.$Subject, $MsgText);
	}
	
	//MAIL REMIND CLOSE 
	 if ($type=="remind_close") 
	{
		$Subject ='ERINNERUNG: Sie haben auf das Ticket Nr.'.$id_conv.' eine Antwort erhalten';
		$MsgText ='<p>'.$contact["partner"]["name"].' <small>('.$contact["partner"]["position"].')</small> hat auf Ihr Ticket <b>'.find_conversation_subject($id_conv).'</b> geantwortet.</p>';
		$MsgText.='<p>Zu diesem Ticket Nr.'.$id_conv.' k&ouml;nnen Sie im <a href="http://www.mapco.de/backend_interna_tickets.php?lang=de&id_menuitem=201&TicketID='.$id_conv.'">Ticket-System</a> eine weitere Nachricht verfassen. <br/>Falls Ihr Anliegen mit dem erhalt der Nachricht von '.$contact["partner"]["name"].' erledigt ist, schlie&szlig;en Sie bitte das Ticket.</p>';
		$MsgText.='<p>Vielen Dank!</p>';

		send_ticket_mail($contact["starter"]["mail"], $Subject, $MsgText);
	//	send_ticket_mail('nputzing@mapco.de','@'.$contact["starter"]["mail"].'-'.$Subject, $MsgText);

	}

	//MAILS AUTO CLOSE
	 if ($type=="auto_close") 
	{
		$Subject ='HINWEIS: Das Ticket Nr.'.$id_conv.' wurde automatisch geschlo&szlig;en';
		$MsgText ='<p>'.$contact["partner"]["name"].' <small>('.$contact["partner"]["position"].')</small> hatte auf Ihr Ticket <b>'.find_conversation_subject($id_conv).'</b> geantwortet.</p>';
		$MsgText.='<p>Da keine weitere Aktion erfolgte, wurde das Ticket automatisch geschlo&szlig;en. Sie k&ouml;nnen das Ticket Nr.'.$id_conv.'  und dessen Nachrichtverlauf jederzeit im <a href="http://www.mapco.de/backend_interna_tickets.php?lang=de&id_menuitem=201&TicketID='.$id_conv.'">Ticket-System</a> einsehen (geschlo&ouml;ene Tickets). </p>';

		send_ticket_mail($contact["starter"]["mail"], $Subject, $MsgText);
		send_ticket_mail('nputzing@mapco.de','@'.$contact["starter"]["mail"].'-'.$Subject, $MsgText);

		
		$Subject ='HINWEIS: Das Ticket Nr.'.$id_conv.' wurde automatisch geschlo&szlig;en';
		$MsgText ='<p>'.$contact["starter"]["name"].' <small>('.$contact["starter"]["position"].')</small> hat Ihnen keine weitere Nachricht zum Ticket <b>'.find_conversation_subject($id_conv).'</b> gesendet.</p>';
		$MsgText.='<p>Das wurde Ticket automatisch geschlo&szlig;en. Sie k&ouml;nnen das Ticket Nr.'.$id_conv.'  und dessen Nachrichtverlauf jederzeit im <a href="http://www.mapco.de/backend_interna_tickets.php?lang=de&id_menuitem=201&TicketID='.$id_conv.'">Ticket-System</a> einsehen (geschlo&ouml;ene Tickets). </p>';

		send_ticket_mail($contact["partner"]["mail"], $Subject, $MsgText);
	//	send_ticket_mail('nputzing@mapco.de','@'.$contact["partner"]["mail"].'-'.$Subject, $MsgText);


	}

}
// END FUNCTIONS
//######################################################################################################################################

//COUNTER FÜR AUSGABE
$counter["remind_respond"]=0;
$counter["remind_close"]=0;
$counter["auto_close"]=0;

$res=q("SELECT * FROM cms_conversations WHERE state = 'open';", $dbweb, __FILE__, __LINE__);
while($row=mysqli_fetch_array($res))
{
	$res2=q("SELECT * FROM cms_conversations_posts WHERE id_conv = '".$row["id_conv"]."' order by post_date desc LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res2)==1) {
		$row2=mysqli_fetch_array($res2);
		
		//wenn Conversation-Starter == User Lastpost => Remind Conv-Partner, wenn 24h keine Antwort
		if ($row["conv_start_userid"]==$row2["post_userid"] && $row["last_mod_date"]<=time()-86400)
		{
			if ($row["auto_remind_response"]=="0") {
				$auto_remind_response=find_next_day($row["last_mod_date"], 1); 
				$res_remind=q("UPDATE cms_conversations SET auto_remind_response='".$auto_remind_response."' WHERE id_conv = '".$row["id_conv"]."';", $dbweb, __FILE__, __LINE__);
			}
			else {
				$auto_remind_response=$row["auto_remind_response"];
			}
			//Nachricht senden
			if ($auto_remind_response<=time()) 
			{
				//MAIL
				$contact=find_contact_from_conversation($row["id_conv"]);
				send_reminder_mail('remind_response', $row["id_conv"], $contact);
				// COUNTER
				$counter["remind_respond"]++;

				//REMIND ZEITPUNKT NEU SETZEN
				$auto_remind_response=find_next_day(time(), 1);
				$res_remind=q("UPDATE cms_conversations SET auto_remind_response='".$auto_remind_response."' WHERE id_conv = '".$row["id_conv"]."';", $dbweb, __FILE__, __LINE__);
			}
		}

		//wenn Conversation-Starter <> User Lastpost => Remind Conv-Starter, wenn 48h keine Antwort
		if ($row["conv_start_userid"]!=$row2["post_userid"] && find_next_day($row["last_mod_date"], 2)<=time() && $row["auto_close"]=="0")
		{
			if ($row["auto_remind_close"]=="0") {
				$auto_remind_close=find_next_day($row["last_mod_date"], 2); 
				$res_remind=q("UPDATE cms_conversations SET auto_remind_close='".$auto_remind_close."' WHERE id_conv = '".$row["id_conv"]."';", $dbweb, __FILE__, __LINE__);
			}
			else $auto_remind_close=$row["auto_remind_close"];
			//Nachricht senden
			if ($auto_remind_close<=time()) 
			{
				//MAIL
				$contact=find_contact_from_conversation($row["id_conv"]);
				send_reminder_mail('remind_close', $row["id_conv"], $contact);
				// COUNTER
				$counter["remind_close"]++;
				//AUTOCLOSE ZEITPUNKT NEU SETZEN
				$auto_close=find_next_day(time(), 1);
				$res_remind=q("UPDATE cms_conversations SET auto_close='".$auto_close."', auto_remind_close = '0' WHERE id_conv = '".$row["id_conv"]."';", $dbweb, __FILE__, __LINE__);
			}
		}
		
		//AUTO CLOSE
		if ($row["auto_close"]>0 && $row["auto_close"]<=time())
		{
			$res_close=q("UPDATE cms_conversations SET state='closed', auto_remind_response = '0', auto_remind_close = '0', auto_close = '0' WHERE id_conv = '".$row["id_conv"]."';", $dbweb, __FILE__, __LINE__);
			if (mysqli_affected_rows==1)	
			{
				//MAIL
				$contact=find_contact_from_conversation($row["id_conv"]);
				send_reminder_mail('auto_close', $row["id_conv"], $contact);
				// COUNTER
				$counter["auto_close"]++;
			}
		}
	}
	
}

//JOB - Skriptausgabe
echo '<b>'.$counter["remind_respond"].'</b> Mail(s) [Remind Respond] gesendet (Antwort auf Ticket erforderlich).< br />';
echo '<b>'.$counter["remind_close"].'</b> Mail(s) [Remind Close] gesendet (Erinnerung Ticket schlie&szlig;en).< br />';
echo '<b>'.$counter["auto_close"].'</b> Mail(s) [Auto Close] gesendet (Ticket geschlo&szlig;en) & Tickets geschlo&szlig;en.< br />';

?>