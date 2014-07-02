<?php
	include("../../mapco_shop_de/functions/cms_send_html_mail.php");


	$date_now=time();

	$sql="UPDATE cms_conversations set last_mod_date = '".$date_now."', end_date = '".$date_now."', state = 'closed', auto_remind_response = '0', auto_remind_close = '0', auto_close = '0' where id_conv = '".$_POST["conv_id"]."'";
	q($sql, $dbweb, __FILE__, __LINE__);	
	
	//PARTNER USERID ermitteln
	$sql="select conv_partner_userid from cms_conversations where id_conv = '".$_POST["conv_id"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results) )  {$partner_userid=$row["conv_partner_userid"];}
	
	//TO Mailadresse ermitteln
	$sql="select mail from cms_contacts where idCmsUser = '".$partner_userid."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results) )  {$toReciever=$row["mail"];}
	
	// FROM USER ERMITTELN
	$sql="select * from cms_contacts where idCmsUser = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results) )  
	{ 
		$send_from["name"]=$row["firstname"]." ".$row["lastname"];
		$send_from["position"]=$row["position"];
		}

	//SUBJECT ermitteln
	$sql="Select a.*, b.* from cms_conversations_posts a, cms_articles b where a.id_conv = '".$_POST["conv_id"]."' Limit 1";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$subject=$row["title"];

	//MAIL senden
	$Subject =$send_from["name"].' hat das Ticket Nr.'.$_POST["conv_id"].' geschlossen';
	$MsgText ='<p>'.$send_from["name"].' <small>('.$send_from["position"].')</small> hat das Ticket Nr.'.$_POST["conv_id"].' geschlossen:</p>';
	$MsgText.='<p>Betreff: '.$subject.'</p>';
	$MsgText.='<p>Den Nachrichtenverlauf können Sie im <a href="http://www.mapco.de/backend_interna_tickets.php?lang=de&id_menuitem=201&TicketID='.$_POST["conv_id"].'">Ticket-System</a> unter dem Punkt >geschlossene Tickets< einsehen</p>';
	 
	send_ticket_mail($toReciever, $Subject, $MsgText);


?>