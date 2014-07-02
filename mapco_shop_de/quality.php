<?php
	include("config.php");
	$title='Qualität von MAPCO';
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_show_article.php");
	
	echo '<div id="mid_column">';
	show_article(209);
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>