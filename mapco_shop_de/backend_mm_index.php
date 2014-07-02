<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > Warenwirtschaft';
	echo '</p>';

	echo '<h1>Warenwirtschaft</h1>';
	echo '<ul class="quickaccess">';
	show_tree(3, true);
	echo '</ul>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>