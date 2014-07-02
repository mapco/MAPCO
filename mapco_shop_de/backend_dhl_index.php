<?php
	include("config.php");

	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > DHL';
	echo '</p>';

	echo '<h1>DHL</h1>';
	echo '<ul class="quickaccess">';
	show_tree(227, true);
	echo '</ul>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
