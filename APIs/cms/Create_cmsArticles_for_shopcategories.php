<?php
	//include("config.php");
	if (!isset($_SESSION["userrole_id"]) && $_SESSION["userrole_id"]!=1)
	{
		echo '<Create_cmsArticles_for_shopcategoriesResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>User nicht berechtigt</shortMsg>'."\n";
		echo '		<longMsg>Das Update darf nur durch Administratoren durchgeführt werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</Create_cmsArticles_for_shopcategoriesResponse>'."\n";
		exit;
	}

	
	$label_id=19;
	
	$counter=0;

	$res=q("select * from cms_menuitems where menuitem_id = 0 and menu_id = 5;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		//CHECK, ob zugehöriger Beitrag erstellt ist
		$res_check=q("SELECT * FROM cms_articles WHERE introduction = '".$row["id_menuitem"]."';", $dbweb, __LINE__, __FILE__);
		if (mysqli_num_rows($res_check)==0)
		{
			$res_insert=q("Insert into cms_articles (title, introduction, language_id, published, newsletter, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ('Hauptkategorie ".$row["title"]."', '".$row["id_menuitem"]."', 1,0,0, ".time().", ".$_SESSION["id_user"].",".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			$id=mysqli_insert_id($dbweb);
			$res_insert=q("INSERT INTO cms_articles_labels ( article_id, label_id, ordering) VALUES (".$id.", 16, 0);", $dbweb, __FILE__, __LINE__);
			$counter++;
		}
	
		$res2=q("select * from cms_menuitems where menuitem_id = ".$row["id_menuitem"].";", $dbweb, __FILE__, __LINE__);
		while ($row2=mysqli_fetch_array($res2))
		{
			//CHECK, ob zugehöriger Beitrag erstellt ist
			$res_check=q("SELECT * FROM cms_articles WHERE introduction = '".$row2["id_menuitem"]."';", $dbweb, __LINE__, __FILE__);
			if (mysqli_num_rows($res_check)==0)
			{
				$res_insert=q("Insert into cms_articles (title, introduction, language_id, published, newsletter, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ('".$row["title"].": ".$row2["title"]."', '".$row2["id_menuitem"]."', 1,0,0, ".time().", ".$_SESSION["id_user"].",".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			
				$id=mysqli_insert_id($dbweb);
				$res_insert=q("INSERT INTO cms_articles_labels ( article_id, label_id, ordering) VALUES (".$id.", ".$label_id.", 0);", $dbweb, __FILE__, __LINE__);
				$counter++;
			}
		}
	}
	
	echo '<Create_cmsArticles_for_shopcategoriesResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<counter><![CDATA['.$counter.']]></counter>'."\n";
	echo '</Create_cmsArticles_for_shopcategoriesResponse>'."\n";

?>