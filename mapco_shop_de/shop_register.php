<?php
	$title="Registrieren";
	$right_column=true;
	include("templates/default/header.php");

	echo '<form method="post">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Benutzername</td>';
	echo '		<td><input type="text" name="form_username" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Passwort</td>';
	echo '		<td><input type="password" name="form_password" value="" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"><input type="submit" name="form_button" value="Registrieren" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	
	include("templates/default/footer.php");
?>