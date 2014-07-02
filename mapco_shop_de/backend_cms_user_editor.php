<?php
	include("config.php");
	$leftmenu=true;
	$columns="MR";
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > <a href="backend_cms_users.php">Benutzer</a>';
	echo ' > Editor';
	echo '</p>';


	//CREATE
	if (isset($_POST["create"]))
    {
		if ($_POST["username"]=="") echo '<div class="failure">Der Name darf nicht leer sein!</div>';
		elseif ($_POST["usermail"]=="") echo '<div class="failure">Die E-Mail-Adresse darf nicht leer sein!</div>';
		elseif ($_POST["userrole_id"]<=0) echo '<div class="failure">Es konnte keine ID für die Benutzerrechte gefunden werden!</div>';
		else
        {
			q("INSERT INTO cms_users (shop_id, site_id, username, usermail, password, userrole_id, language_id, lastlogin, lastvisit, session_id, active, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(8, 1, '".addslashes(stripslashes($_POST["username"]))."', '".addslashes(stripslashes($_POST["usermail"]))."', ".$_POST["userrole_id"].", ".$_POST["language_id"].", 0, ".$_POST["active"].", '', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			$_GET["id_user"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Benutzerprofil erfolgreich angelegt!</div>';
        }
	}
	//UPDATE
	if (isset($_POST["update"]))
    {
		if ($_GET["id_user"]<=0) echo '<div class="failure">Es konnte keine ID für den Benutzer gefunden werden!</div>';
		elseif ($_POST["username"]=="") echo '<div class="failure">Der Name darf nicht leer sein!</div>';
		elseif ($_POST["usermail"]=="") echo '<div class="failure">Die E-Mail-Adresse darf nicht leer sein!</div>';
		elseif ($_POST["userrole_id"]<=0) echo '<div class="failure">Es konnte keine ID für die Benutzerrechte gefunden werden!</div>';
		else
        {
			q("UPDATE cms_users
						 SET username='".addslashes(stripslashes($_POST["username"]))."',
						 	 usermail='".addslashes(stripslashes($_POST["usermail"]))."',
						 	 userrole_id=".$_POST["userrole_id"].",
						 	 language_id=".$_POST["language_id"].",
						 	 active=".$_POST["active"].",
						 	 lastmod=".time().",
						 	 lastmod_user=".$_SESSION["id_user"]."
						 WHERE id_user=".$_GET["id_user"].";", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Benutzerprofil erfolgreich aktualisiert!</div>';
        }
    }

	//READ
	if (isset($_GET["id_user"]))
	{
		$results=q("SELECT * FROM cms_users WHERE id_user=".$_GET["id_user"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["active"]=$row["active"];
		$_POST["username"]=$row["username"];
		$_POST["usermail"]=$row["usermail"];
		$_POST["userrole_id"]=$row["userrole_id"];
		if ($_POST["userrole_id"]<=0) $_POST["userrole_id"]=6;
		$_POST["language_id"]=$row["language_id"];
	}

	//EDITOR
    echo '<h2>Benutzerprofil-Editor</h2>';
	if (isset($_GET["id_user"]))
	{
		echo '<form action="backend_cms_user_editor.php?id_user='.$_GET["id_user"].'" method="post">';
	}
	else
	{
		echo '<form action="backend_cms_user_editor.php" method="post">';
	}
	echo '<table>';
	echo '	<tr><td>Name</td><td><input size="40" type="text" name="username" value="'.$_POST["username"].'" /></td></tr>';
	echo '	<tr><td>E-Mail</td><td><input size="40" type="text" name="usermail" value="'.$_POST["usermail"].'" /></td></tr>';
	echo '	<tr>';
	echo '		<td>Benutzerrechte</td>';
	echo '		<td>';
	echo '			<select name="userrole_id">';
	$results=q("SELECT * FROM cms_userroles ORDER BY userrole;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		if ($row["id_userrole"]==$_POST["userrole_id"]) $selected=' selected="selected"'; else $selected='';
		echo '<option'.$selected.' value="'.$row["id_userrole"].'">'.$row["userrole"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	
	//Sprache
	echo '<tr>';
	echo '	<td>Sprache</td>';
	echo '	<td>';
	echo '		<select name="language_id">';
	$results2=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		if ($row2["id_language"]==$_POST["language_id"]) $selected=' selected="selected"'; else $selected='';
		echo '<option'.$selected.' value="'.$row2["id_language"].'">'.t($row2["language"], __FILE__, __LINE__).'</option>';
	}
	echo '		</select>';
	echo '	</td>';
	echo '</tr>';
	
	echo '	<tr>';
	echo '		<td>Status</td>';
	echo '		<td>';
	echo '			<select name="active">';
	if ($_POST["active"]>0) $selected=''; else $selected=' selected="selected"';
	echo '<option'.$selected.' value="0">deaktiviert</option>';
	if ($_POST["active"]>0) $selected=' selected="selected"'; else $selected='';
	echo '<option'.$selected.' value="1">aktiviert</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	if (isset($_GET["id_user"]))
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="update" value="Benutzerprofil aktualisieren" /></td></tr>';
	}
	else
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="create" value="Benutzerprofil anlegen" /></td></tr>';
	}
	echo '</table>';
	echo '</form>';
    
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>