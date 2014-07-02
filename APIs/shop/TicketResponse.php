<?php

	//security check
	session_start();
	if ( !isset($_SESSION["id_user"]) or !($_SESSION["id_user"]>0) ) exit;

	//EINGABEN PRÜFEN
	if ($_POST["message"]=="") { echo 'Bitte einen Nachrichtentext eingaben!'; exit;}
	
	$date_now=time();
	$id_conv=$_POST["id_conv"];
	// Usermail ermitteln
	$sql = "select usermail from cms_users where id_user = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) ) { $usermail=$row["usermail"]; }
	
	// SUBJECT ERMITTELN
	$sql="select * from cms_conversations_posts where id_conv = '".$id_conv."' order by post_date LIMIT 0,1";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) )  
		{
			$sql2 = "select title from cms_articles where id_article = '".$row["id_cms_article"]."'";
			$results2=q($sql2, $dbweb, __FILE__, __LINE__);
			while( $row2=mysql_fetch_array($results2) ) { $subject=$row2["title"];}
		}
	
	
	// NACRICHT IN CMS_ARTICLES EINFÜGEN	
	$sql = "INSERT INTO cms_articles (";
	$sql.= "language_id, article_id, title, introduction, article, published, format, imageprofile_id, ordering, newsletter, firstmod, firstmod_user, lastmod, lastmod_user";
	$sql.= ") VALUES (";
	$sql.= "'1', '0', '".$subject."', '', '".$_POST["message"]."', '0', '0', '0', '', '0', '".$date_now."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."')";
	
	q($sql, $dbweb, __FILE__, __LINE__);
	

	// id_article des ERMITTELN
	$sql = "select id_article from cms_articles where lastmod = '".$date_now."' and lastmod_user = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) ) { $id_article=$row["id_article"]; }

	// NACHRICHT IN cms_conversations_posts VERKNÜPFEN
	$sql = "INSERT INTO cms_conversations_posts (";
	$sql.= "id_conv, id_cms_article, post_usermail, post_date";
	$sql.= ") VALUES (";
	$sql.= "'".$id_conv."', '".$id_article."', '".$usermail."', '".$date_now."')";
	q($sql, $dbweb, __FILE__, __LINE__);
	
	// NACHRICHT IN cms_conversations VERKNÜPFEN
	$sql="UPDATE cms_conversations set last_mod_date = '".$date_now."' where id_conv = '".$id_conv."'";
	q($sql, $dbweb, __FILE__, __LINE__);	



?>