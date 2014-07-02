<?php
	$title="Login";
	$right_column=true;
	include("templates/default/header.php");

	echo '<div class="box_mid_right">';
	session_unset();
	session_destroy();
	echo '<p>Sie haben sich erfolgreich abgemeldet!</p>';
	echo '</div>';
	
	include("templates/default/footer.php");
?>