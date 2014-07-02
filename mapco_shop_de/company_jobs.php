<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_show_article.php");

	echo '<div id="mid_column">';
	show_article(55);
	echo '<hr />';
	
	$results=q("SELECT * FROM cms_articles_labels AS a, cms_articles AS b WHERE a.label_id=4 and a.article_id=b.id_article AND b.published order by b.ordering;", $dbweb, __FILE__, __LINE__);
	$max=mysqli_num_rows($results);
	$i=0;
	while($row=mysqli_fetch_array($results))
	{
		echo '<h2>'.$row["title"].'</h2>';
		echo $row["article"];
		$i++;
		if ($i<$max) echo '<hr />';
	}
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>