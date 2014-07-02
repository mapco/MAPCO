<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	/*
	//import joomla content
	$results=q("SELECT * FROM jos_content ORDER BY created;", $dbweb, __FILE__, __LINE__);
	while($row=mysql_fetch_array($results))
	{
		q("INSERT INTO cms_articles (title, article, published, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$row["title"]."', '".$row["introtext"].$row["fulltext"]."', 1, '".strtotime($row["created"])."', 0, '".strtotime($row["modified"])."', 0);", $dbweb, __FILE__, __LINE__);
		
		//frontpage markieren
		$id_article=mysql_insert_id($dbweb);
		$results2=q("SELECT * FROM jos_content_frontpage WHERE content_id=".$row["id"].";", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($results2)>0)
		{
			q("INSERT INTO cms_articles_labels (article_id, label_id) VALUES(".$id_article.", 1);", $dbweb, __FILE__, __LINE__);
		}
	}
	exit();
	*/
	

	$results=q("SELECT * FROM shop_items AS a, shop_items_de AS b WHERE a.id_item=b.id_item ORDER BY a.id_item;", $dbshop, __FILE__, __LINE__);
	echo '<h1>Warenbestand</h1>';
	echo '<table class="hover">';
	echo '<tr>';
	echo '	<th>ID</th>';
	echo '	<th>Artikelbezeichnung</th>';
	echo '	<th>Bestand</th>';
	echo '</tr>';
	while($row=mysql_fetch_array($results))
	{
		echo '<tr>';
		echo '	<td>'.$row["id_item"].'</td>';
		echo '	<td>'.$row["title"].'</td>';
		echo '	<td>'.rand(0,99).'</td>';
		echo '</tr>';
	}
	echo '</table>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>