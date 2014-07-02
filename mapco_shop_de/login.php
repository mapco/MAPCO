<?php

	include("config.php");
	include("templates/".TEMPLATE."/header.php");

	echo '<div class="mid_box">';
	echo '<h1>Login</h1>';
	if ($_SESSION["id_user"]!="")
	{
		echo '<p>';
		echo 'Willkommen, '.$_SESSION["id_user"];
		echo '</p>';
	}
	else
	{
		echo '<form method="post">';
		echo '<table>';
		echo '	<tr>';
		echo '		<td>Benutzername</td>';
		echo '		<td><input type="text" name="form_username" value="'.$_POST["form_username"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>Passwort</td>';
		echo '		<td><input type="text" name="form_password" value="" /></td>';
		echo '	</tr>';
		echo '</table>';
		echo '<input type="submit" value="Anmelden" />';
		echo '</form>';
	}
	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>
