<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");

	echo '<div class="mid_box">';
	echo '<h1>Logout</h1>';
	if ($_SESSION["id_user"]!="")
	{
		session_unset();
		session_destroy();
		echo '<p>';
		echo 'Sie haben sich erfolgreich abgemeldet.';
		echo '</p>';
	}
	else
	{
		echo '<p>Es konnte kein Benutzer abgemeldet werden, da niemand angemeldet ist.</p>';
	}
	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>