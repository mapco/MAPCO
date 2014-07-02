<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_url_encode.php");
	include("functions/cms_t.php");

	//left column
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	
	//news
	echo '<div id="mid_column">';
	echo '<h1>'.t("Pressemitteilungen").'</h1>';
	echo '<p><a href="'.PATHLANG.'presse/presseberichte/">'.t("Zu den MAPCO-Presseberichten gelangen Sie hier").'.</a></p><hr />';
	$results=q("SELECT * FROM cms_articles AS a, cms_articles_labels AS b WHERE a.published AND b.label_id=6 AND a.id_article=b.article_id ORDER BY firstmod DESC;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
			echo '<h3>'.$row["title"].'</h3>';
			echo '<p>'.$row["introduction"].'</p>';
			echo '<p><a style="font-size:14px; float:right;" href="'.PATHLANG.'presse/pressemitteilungen/'.$row["id_article"].'/'.url_encode($row["title"]).'" title="'.$row["title"].'">'.t("weiterlesen").'</a></p>';
			if ($i<4) echo '<br style="clear:both;" /><hr />';
	}
	echo '</div>';
	
	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>