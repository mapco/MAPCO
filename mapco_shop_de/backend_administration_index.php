<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a> > ';
	echo 'Interna';
	echo '</p>';


	echo '<h1>Administration</h1>';
	echo '<ul class="quickaccess">';
	show_tree(205, true);
	echo '</ul>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
