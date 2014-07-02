<?php
	include("../../mapco_shop_de/functions/cms_send_html_mail.php");

	//security check
	//session_start();
	if ( !isset($_SESSION["id_user"]) or !($_SESSION["id_user"]>0) ) exit;

	//EINGABEN PRÜFEN
	if ($_POST["contact"]=="") { echo 'Bitte einen Emfänger der Nachricht angeben!'; exit;}
	if ($_POST["subject"]=="") { echo 'Bitte einen Betreff der Nachricht angeben!'; exit;}
	if ($_POST["message"]=="") { echo 'Bitte einen Nachrichtentext eingaben!'; exit;}
	
	$date_now=time();
	
	// NACRICHT IN CMS_ARTICLES EINFÜGEN	
	$sql = "INSERT INTO cms_articles (";
	$sql.= "site_id, language_id, article_id, title, introduction, article, published, format, imageprofile_id, ordering, newsletter, firstmod, firstmod_user, lastmod, lastmod_user";
	$sql.= ") VALUES (";
	$sql.= $_SESSION["id_site"].",'1', '0', '".mysqli_real_escape_string($dbweb,$_POST["subject"])."', '', '".mysqli_real_escape_string($dbweb,$_POST["message"])."', '0', '0', '0', '', '0', '".$date_now."', '".$_SESSION["id_user"]."', '".$date_now."', '".$_SESSION["id_user"]."')";
	
	$res=q($sql, $dbweb, __FILE__, __LINE__);
	
	$id_article = mysqli_insert_id($dbweb);
	
	/*
// POST in CMS_ARTICLE ANLEGEN
	// id_article ERMITTELN
	$sql = "select id_article from cms_articles where lastmod = '".$date_now."' and lastmod_user = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) ) { $id_article=$row["id_article"]; }
*/
	//LABEL für CMS_ARTICLE verknüpfen
	$sql = "INSERT INTO cms_articles_labels (";
	$sql.= "article_id, label_id, ordering) VALUES ('".$id_article."', '13', '0')";
	q($sql, $dbweb, __FILE__, __LINE__);
	
// NEUE CONVERSATION ANLEGEN
/*
	// Usermail ermitteln
	$sql = "select usermail from cms_users where id_user = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) ) { $usermail=$row["usermail"]; }
*/	
	// TICKET anlegen
	$sql = "INSERT INTO cms_conversations (";
	$sql.= "state, conv_partner_userid, conv_start_userid, start_date, last_mod_date, end_date, auto_remind_response, auto_remind_close, auto_close";
	$sql.= ") VALUES (";
	$sql.= "'open', '".$_POST["contact"]."', '".$_SESSION["id_user"]."', '".$date_now."', '".$date_now."', '0', '0', '0', '0' )";
	q($sql, $dbweb, __FILE__, __LINE__);

	// id_conv ERMITTELN (Ticketnummer)
	$sql = "select id_conv from cms_conversations where start_date = '".$date_now."' and conv_start_userid = '".$_SESSION["id_user"]."' and conv_partner_userid = '".$_POST["contact"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) ) { $id_conv=$row["id_conv"]; }
	
	// NACHRICHT IN cms_conversations_post VERKNÜPFEN
	$sql = "INSERT INTO cms_conversations_posts (";
	$sql.= "id_conv, id_cms_article, post_userid, post_date";
	$sql.= ") VALUES (";
	$sql.= "'".$id_conv."', '".$id_article."', '".$_SESSION["id_user"]."', '".$date_now."')";
	q($sql, $dbweb, __FILE__, __LINE__);
	
//MAIL VERSENDEN ---------------------------------------------------------------------------

	//Kontaktdaten Sender ermitteln
	$sql="select * from cms_contacts where idCmsUser = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results) )  
	{ 
		$send_from["name"]=$row["firstname"]." ".$row["lastname"];
		$send_from["position"]=$row["position"];
		}
	//Empfängermail ermitteln
	$sql="select mail from cms_contacts where idCmsUser = '".$_POST["contact"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results) ) {$toReciever=$row["mail"];}

	//MAIL senden
	$Subject ='Ticket zur Bearbeitung von '.$send_from["name"].' erhalten';
	$MsgText ='<p>'.$send_from["name"].' <small>('.$send_from["position"].')</small> hat Ihnen folgendes Ticket Nr.'.$id_conv.' zur Bearbeitung gesandt:</p>';
	$MsgText.='<p><b>'.$_POST["subject"].'</b></p>';
	$MsgText.='<p>Auf das Ticket können Sie im <a href="http://www.mapco.de/backend_interna_tickets.php?lang=de&id_menuitem=201&TicketID='.$id_conv.'">Ticket-System</a> antworten</p>';
	 
	//send_ticket_mail($toReciever, $Subject, $MsgText);
	send_ticket_mail("nputzing@mapco", $Subject, $MsgText);
?>