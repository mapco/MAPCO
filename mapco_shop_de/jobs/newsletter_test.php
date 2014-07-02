<?php
	include("../config.php");
	include("../functions/cms_newsletter.php");
	include("../functions/mapco_cutout.php");
	
	echo "Newsletter Test";
	$_SESSION["lang"]="de";
	
	$blacklist=array();
	$results=q("SELECT mail FROM cms_mail_blacklist WHERE site_id IN (0, 1, 7, 8, 9, 10, 11, 12, 13, 14, 15, 17);", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$blacklist[strtolower($row["mail"])]=strtolower($row["mail"]);
	}

	$results=q("SELECT b.id_article, b.title, b.article, b.introduction, b.firstmod, b.language_id FROM cms_articles_labels AS a, cms_articles AS b WHERE a.label_id=5 AND a.article_id=b.id_article AND b.newsletter=0 AND b.published=0 ORDER BY b.firstmod;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$id_article=$row["id_article"];
		$results2=q("SELECT distinct a.id_user, a.usermail FROM cms_users AS a, cms_users_sites AS b WHERE NOT a.newsletter_id=".$row["id_article"]." AND b.user_id=a.id_user AND b.site_id IN (0, 1, 7, 8, 9, 10, 11, 12, 13, 14, 15, 17)AND a.language_id=".$row["language_id"]." AND a.newsletter=1 LIMIT 1;", $dbweb, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$receiver=strtolower($row2["usermail"]);
			
			if($receiver=="" or $receiver=="-")
			{
				q("UPDATE cms_users SET newsletter=0 WHERE id_user=".$row2["id_user"].";", $dbweb, __FILE__, __LINE__);
			}			
			elseif(!isset($blacklist[$receiver]))
			{
				$header = 'MIME-Version: 1.0' . "\r\n";
				$header = 'Content-type: text/html; charset=utf-8' . "\r\n";
				$header .= 'From: MAPCO Autotechnik GmbH <newsletter@mapco.de>' . "\r\n";
			
				$mail = newsletter($receiver, $row["id_article"], $row["title"], $row["article"], $row["introduction"], $row["firstmod"], "de");
				
				mail("pm@mapco.eu", $receiver." - ID".$row["id_article"]." - ".$row["title"], $mail, $header);
				echo 'Newsletter an '.$receiver.' versendet.<br />';
			}
			else q("UPDATE cms_users SET newsletter=0 WHERE id_user=".$row2["id_user"].";", $dbweb, __FILE__, __LINE__);
		}
	}
?>