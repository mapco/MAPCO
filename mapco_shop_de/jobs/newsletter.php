<?php
	include("../config.php");
	include("../functions/cms_newsletter.php");
	include("../functions/mapco_cutout.php");
	

	$_GET["lang"]="de";
	
	$results=q("SELECT b.id_article, b.title, b.article, b.introduction, b.firstmod FROM cms_articles_labels AS a, cms_articles AS b WHERE a.label_id=5 AND a.article_id=b.id_article AND b.newsletter=0 AND b.published>0 ORDER BY b.firstmod;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$id_article=$row["id_article"];
		$results2=q("SELECT * FROM cms_newsletter WHERE NOT newsletter_id=".$row["id_article"].";", $dbweb, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$receiver=$row2["email"];
			
			$header = 'MIME-Version: 1.0' . "\r\n";
			$header = 'Content-type: text/html; charset=utf-8' . "\r\n";
			$header .= 'From: MAPCO Autotechnik GmbH <newsletter@mapco.de>' . "\r\n";
			
			$mail = newsletter($receiver, $row["id_article"], $row["title"], $row["article"], $row["introduction"], $row["firstmod"], "de");
			
			mail($receiver, cutout($row["title"], "<", ">"), $mail, $header);
//			mail("developer@mapco.de", $receiver." - MAPCO Newsletter - ID".$row["id_article"], $mail, $header) or die("E-MAIL-VERSANDFEHLER: ".$receiver);

				q("UPDATE cms_newsletter SET newsletter_id=".$id_article.", newsletter_stamp=".time()." WHERE id=".$row2["id"].";", $dbweb, __FILE__, __LINE__);

		}
		q("UPDATE cms_articles SET newsletter=1 WHERE id_article=".$id_article.";", $dbweb, __FILE__, __LINE__);
	}
?>