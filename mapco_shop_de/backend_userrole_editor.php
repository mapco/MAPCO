<?php
	include("config.php");
	$leftmenu=true;
	$columns="MR";
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//create userrole
	if ($_POST["form_button"]=="Benutzerrolle anlegen")
    {
		if ($_POST["form_userrole"]=="") echo '<div class="failure">Der Name darf nicht leer sein!</div>';
		else
        {
			q("INSERT INTO cms_userroles (userrole) VALUES('".addslashes(stripslashes($_POST["form_userrole"]))."');", $dbweb, __FILE__, __LINE__);
			$_GET["id_userrole"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Benutzerrolle erfolgreich angelegt!</div>';
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
	if (isset($_GET["id_userrole"]))
	{
		$results=q("SELECT * FROM cms_userroles WHERE id_userrole=".$_GET["id_userrole"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["form_userrole"]=$row["userrole"];
	}

    //userrole editor
    echo '<h2>Benutzerrollen-Editor</h2>';
	if (isset($_GET["id_user"]))
	{
		echo '<form action="backend_userrole_editor?id_user='.$_GET["id_user"].'" method="post" enctype="multipart/form-data">';
	}
	else
	{
		echo '<form action="backend_userrole_editor.php" method="post" enctype="multipart/form-data">';
	}
	echo '<table>';
	echo '	<tr><td>Benutzerrolle</td><td><input size="40" type="text" name="form_userrole" value="'.$_POST["form_userrole"].'" /></td></tr>';
	if (isset($_GET["id_user"]))
	{
		echo '	<tr><td colspan="2"><input type="submit" name="form_button" value="Benutzerrolle aktualisieren" /></td></tr>';
	}
	else
	{
		echo '	<tr><td colspan="2"><input type="submit" name="form_button" value="Benutzerrolle anlegen" /></td></tr>';
	}
	echo '</table>';
	echo '</form>';
    
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>