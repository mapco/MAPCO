<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a> > ';
	echo 'eBay';
	echo '</p>';


	echo '<h1>eBay</h1>';
	echo '<ul class="quickaccess">';
	show_tree(171, true);
	echo '</ul>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
