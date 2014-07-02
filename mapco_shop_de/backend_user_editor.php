<?php
	include("config.php");
	$leftmenu=true;
	$columns="MR";
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//Neues Benutzerprofil anlegen
	if ($_POST["form_button"]=="Benutzerprofil anlegen")
    {
		if ($_POST["form_username"]=="") echo '<div class="failure">Der Name darf nicht leer sein!</div>';
		elseif ($_POST["form_usermail"]=="") echo '<div class="failure">Die E-Mail-Adresse darf nicht leer sein!</div>';
		elseif ($_POST["form_password"]=="") echo '<div class="failure">Das Passwort darf nicht leer sein!</div>';
		elseif ($_POST["form_folder_id"]<=0) echo '<div class="failure">Es konnte keine ID für den Hauptordner gefunden werden!</div>';
		else
        {
			q("INSERT INTO users (username, usermail, usermail2, password, folder_id) VALUES('".addslashes(stripslashes($_POST["form_username"]))."', '".addslashes(stripslashes($_POST["form_usermail"]))."', '".addslashes(stripslashes($_POST["form_usermail2"]))."', '".addslashes(stripslashes($_POST["form_password"]))."', ".$_POST["form_folder_id"].");", $dbweb, __FILE__, __LINE__);
			$_GET["id_user"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Benutzerprofil erfolgreich angelegt!</div>';
        }
	}
	//Benutzerprofil aktualisieren
	if ($_POST["form_button"]=="Benutzerprofil aktualisieren")
    {
		if ($_GET["id_user"]<=0) echo '<div class="failure">Es konnte keine ID für den Benutzer gefunden werden!</div>';
		elseif ($_POST["form_username"]=="") echo '<div class="failure">Der Name darf nicht leer sein!</div>';
		elseif ($_POST["form_usermail"]=="") echo '<div class="failure">Die E-Mail-Adresse darf nicht leer sein!</div>';
		elseif ($_POST["form_password"]=="") echo '<div class="failure">Das Passwort darf nicht leer sein!</div>';
		elseif ($_POST["form_folder_id"]<=0) echo '<div class="failure">Es konnte keine ID für den Hauptordner gefunden werden!</div>';
		else
        {
			q("UPDATE users
						 SET username='".addslashes(stripslashes($_POST["form_username"]))."',
						 	 usermail='".addslashes(stripslashes($_POST["form_usermail"]))."',
						 	 usermail2='".addslashes(stripslashes($_POST["form_usermail2"]))."',
						 	 password='".addslashes(stripslashes($_POST["form_password"]))."',
						 	 folder_id=".$_POST["form_folder_id"]."
						 WHERE id_user=".$_GET["id_user"].";", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Benutzerprofil erfolgreich aktualisiert!</div>';
        }
    }

	//Benutzerprofil auslesen
	if (isset($_GET["id_user"]))
	{
		$results=q("SELECT * FROM users WHERE id_user=".$_GET["id_user"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["form_username"]=$row["username"];
		$_POST["form_usermail"]=$row["usermail"];
		$_POST["form_usermail2"]=$row["usermail2"];
		$_POST["form_password"]=$row["password"];
		$_POST["form_folder_id"]=$row["folder_id"];
	}

    //Ordner-Editor
    echo '<h2>Benutzerprofil-Editor</h2>';
	if (isset($_GET["id_user"]))
	{
		echo '<form action="user_editor?id_user='.$_GET["id_user"].'" method="post" enctype="multipart/form-data">';
	}
	else
	{
		echo '<form action="backend_user_editor.php" method="post" enctype="multipart/form-data">';
	}
	echo '<table>';
	echo '	<tr><td>Name</td><td><input size="40" type="text" name="form_username" value="'.$_POST["form_username"].'" /></td></tr>';
	echo '	<tr><td>E-Mail</td><td><input size="40" type="text" name="form_usermail" value="'.$_POST["form_usermail"].'" /></td></tr>';
	echo '	<tr><td>2. E-Mail</td><td><input size="40" type="text" name="form_usermail2" value="'.$_POST["form_usermail2"].'" /></td></tr>';
	echo '	<tr><td>Passwort</td><td><input size="40" type="text" name="form_password" value="'.$_POST["form_password"].'" /></td></tr>';
	echo '	<tr>';
	echo '		<td>Hauptordner</td>';
	echo '		<td>';
	echo '			<select name="form_folder_id">';
	$tree=array();
	$tree=foldertree($tree, 0, 3);
	for ($i=0; $i<sizeof($tree); $i++)
	{
		$foldername=str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $tree[$i][2]).$tree[$i][1];
		if ($tree[$i][0]==$_POST["form_folder_id"]) $selected=' selected="selected"'; else $selected='';
		echo '				<option'.$selected.' value="'.$tree[$i][0].'">'.$foldername.'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	if (isset($_GET["id_user"]))
	{
		echo '	<tr><td colspan="2"><input type="submit" name="form_button" value="Benutzerprofil aktualisieren" /></td></tr>';
	}
	else
	{
		echo '	<tr><td colspan="2"><input type="submit" name="form_button" value="Benutzerprofil anlegen" /></td></tr>';
	}
	echo '</table>';
	echo '</form>';
	echo '<form action="backend_users.php" method="post"><input type="submit" name="form_button" value="Zurück" /></form>';
    
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>