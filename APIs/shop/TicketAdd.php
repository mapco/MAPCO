<?
	//security check
	session_start();
	if ( !isset($_SESSION["id_user"]) or !($_SESSION["id_user"]>0) ) exit;

	//EINGABEN PRÜFEN
	if ($_POST["contact"]=="") { echo 'Bitte einen Emfänger der Nachricht angeben!'; exit;}
	if ($_POST["subject"]=="") { echo 'Bitte einen Betreff der Nachricht angeben!'; exit;}
	if ($_POST["message"]=="") { echo 'Bitte einen Nachrichtentext eingaben!'; exit;}
	
	$date_now=time();
	
	// NACRICHT IN CMS_ARTICLES EINFÜGEN	
	$sql = "INSERT INTO cms_articles (";
	$sql.= "language_id, article_id, title, introduction, article, published, format, imageprofile_id, ordering, newsletter, firstmod, firstmod_user, lastmod, lastmod_user";
	$sql.= ") VALUES (";
	$sql.= "'1', '0', '".$_POST["subject"]."', '', '".$_POST["message"]."', '0', '0', '0', '', '0', '".$date_now."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."')";
	
	q($sql, $dbweb, __FILE__, __LINE__);
	
	// NEUE CONVERSATION ANLEGEN
	
	// Usermail ermitteln
	$sql = "select usermail from cms_users where id_user = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) ) { $usermail=$row["usermail"]; }
	
	$sql = "INSERT INTO cms_conversations (";
	$sql.= "state, conv_start_usermail, conv_partner_usermail, conv_start_userid, start_date, last_mod_date, end_date";
	$sql.= ") VALUES (";
	$sql.= "'open', '".$usermail."', '".$_POST["contact"]."', '".$_SESSION["id_user"]."', '".$date_now."', '".$date_now."', '')";
	
	q($sql, $dbweb, __FILE__, __LINE__);
	
	// id_article ERMITTELN
	$sql = "select id_article from cms_articles where lastmod = '".$date_now."' and lastmod_user = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) ) { $id_article=$row["id_article"]; }
	
	// id_conv ERMITTELN
	$sql = "select id_conv from cms_conversations where start_date = '".$date_now."' and conv_start_userid = '".$_SESSION["id_user"]."' and conv_partner_usermail = '".$_POST["contact"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) ) { $id_conv=$row["id_conv"]; }

	// NACHRICHT IN cms_conversations_post VERKNÜPFEN
	$sql = "INSERT INTO cms_conversations_posts (";
	$sql.= "id_conv, id_cms_article, post_usermail, post_date";
	$sql.= ") VALUES (";
	$sql.= "'".$id_conv."', '".$id_article."', '".$usermail."', '".$date_now."')";
	q($sql, $dbweb, __FILE__, __LINE__);

?>