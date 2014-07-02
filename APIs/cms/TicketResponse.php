<?php
	include("../../mapco_shop_de/functions/cms_send_html_mail.php");


	//security check
	session_start();
	if ( !isset($_SESSION["id_user"]) or !($_SESSION["id_user"]>0) ) exit;

	//EINGABEN PRÜFEN
	if ($_POST["message"]=="") { echo 'Bitte einen Nachrichtentext eingaben!'; exit;}
	
	$date_now=time();
	$id_conv=$_POST["id_conv"];
/*
	// Usermail ermitteln
	$sql = "select usermail from cms_users where id_user = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) ) { $usermail=$row["usermail"]; }
*/	
	// SUBJECT ERMITTELN
	$sql="select * from cms_conversations_posts where id_conv = '".$id_conv."' order by post_date LIMIT 0,1";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )  
		{
			$sql2 = "select title from cms_articles where id_article = '".$row["id_cms_article"]."'";
			$results2=q($sql2, $dbweb, __FILE__, __LINE__);
			while( $row2=mysqli_fetch_array($results2) ) { $subject=$row2["title"];}
		}
	
	
	// NACRICHT IN CMS_ARTICLES EINFÜGEN	
	$sql = "INSERT INTO cms_articles (";
	$sql.= "site_id, language_id, article_id, title, introduction, article, published, format, imageprofile_id, ordering, newsletter, firstmod, firstmod_user, lastmod, lastmod_user";
	$sql.= ") VALUES (";
	$sql.= $_SESSION["id_site"].",'1', '0', '".mysqli_real_escape_string($dbweb,$subject)."', '', '".mysqli_real_escape_string($dbweb,$_POST["message"])."', '0', '0', '0', '', '0', '".$date_now."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."')";
	
	$res = q($sql, $dbweb, __FILE__, __LINE__);
	
	$id_article = mysqli_insert_id($dbweb);
	/*
	// id_article des ERMITTELN
	$sql = "select id_article from cms_articles where lastmod = '".$date_now."' and lastmod_user = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) ) { $id_article=$row["id_article"]; }
	*/
	
	//LABEL für CMS_ARTICLE verknüpfen
	$sql = "INSERT INTO cms_articles_labels (";
	$sql.= "article_id, label_id, ordering) VALUES ('".$id_article."', '13', '0')";
	q($sql, $dbweb, __FILE__, __LINE__);

	// NACHRICHT IN cms_conversations_posts VERKNÜPFEN
	$sql = "INSERT INTO cms_conversations_posts (";
	$sql.= "id_conv, id_cms_article, post_userid, post_date";
	$sql.= ") VALUES (";
	$sql.= "'".$id_conv."', '".$id_article."', '".$_SESSION["id_user"]."', '".$date_now."')";
	q($sql, $dbweb, __FILE__, __LINE__);
	
	// NACHRICHT IN cms_conversations VERKNÜPFEN
	$sql="UPDATE cms_conversations set last_mod_date = '".$date_now."', auto_remind_response = '0', auto_remind_close = '0', auto_close = '0' where id_conv = '".$id_conv."'";
	q($sql, $dbweb, __FILE__, __LINE__);	

// MAIL VERSENDEN
	// FROM USER ERMITTELN
	$sql="select * from cms_contacts where idCmsUser = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results) )  
	{ 
		$send_from["name"]=$row["firstname"]." ".$row["lastname"];
		$send_from["position"]=$row["position"];
		}
	//TO Mailadresse ermitteln
	$sql="select conv_start_userid, conv_partner_userid from cms_conversations where id_conv = '".$id_conv."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results) )  {$partner_userid=$row["conv_partner_userid"]; $start_userid=$row["conv_start_userid"];}
	
	if ($start_userid==$_SESSION["id_user"]) 
	{
		$sql="select mail from cms_contacts where idCmsUser = '".$partner_userid."'";
	}
	else 
	{
		$sql="select mail from cms_contacts where idCmsUser = '".$start_userid."'";
	}

	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results) )  {$toReciever=$row["mail"];}

	
	//MAIL senden
	$Subject ='Nachricht auf Ticket Nr.'.$id_conv.' von '.$send_from["name"].' erhalten';
	$MsgText ='<p>'.$send_from["name"].' <small>('.$send_from["position"].')</small> hat Ihnen eine Nachricht auf folgendes Ticket gesandt:</p>';
	$MsgText.='<p><b>'.$subject.'</b></p>';
	$MsgText.='<p>Auf die Nachricht / das Ticket Nr.'.$id_conv.' k&ouml;nnen Sie im <a href="http://www.mapco.de/backend_interna_tickets.php?lang=de&id_menuitem=201&TicketID='.$id_conv.'">Ticket-System</a> antworten</p>';
	if ($_SESSION["id_user"]!=$start_userid) {
		$MsgText.='<p>Sollte das Ticket mit der erhaltenen Nachricht entg&uuml;ltig beantwortet / bearbeitet worden sein, schließen Sie bitte das Ticket.</p>';
	}
	 
	send_ticket_mail($toReciever, $Subject, $MsgText);


?>