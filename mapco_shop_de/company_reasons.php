<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_show_article.php");

	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	
	echo '<div id="mid_column">';
	show_article(141);
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>