<?php
	$title="Login";
	$right_column=true;
	include("config.php");
	include("templates/default/header.php");
	
	if ($_POST["form_button"]==LOGIN)
	{
		$results=q("SELECT * FROM web_users WHERE nic='".$_POST["form_username"]."' AND password='".$_POST["form_password"]."';", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			$row=mysqli_fetch_array($results);
			$_SESSION["id_user"]=$row["id_user"];
			echo '<div class="success">Login erfolgreich.</div>';
		}
		else
		{
			echo '<div class="success">Die Kombination aus Benutzername und Passwort ist nicht bekannt.</div>';
		}
	}

	echo '<div class="box_mid_right">';
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
	echo '		<td colspan="2"><input type="submit" name="form_button" value="'.LOGIN.'" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';
	
	include("templates/default/footer.php");
?>
