<?php
	include("config.php");
	$leftmenu=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > <a href="backend_cms_languages.php">Sprachen</a>';
	echo ' > Editor';
	echo '</p>';


	//CREATE
	if (isset($_POST["create"]))
    {
		if ($_POST["language"]=="") echo '<div class="failure">Der Sprachetext darf nicht leer sein!</div>';
		else
        {
			q("INSERT INTO cms_languages (language, code, active, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".addslashes(stripslashes($_POST["language"]))."', '".addslashes(stripslashes($_POST["code"]))."', ".$_POST["active"].", ".$_POST["ordering"].", '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbweb, __FILE__, __LINE__);
			$_GET["id_language"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Sprache erfolgreich angelegt!</div>';
        }
	}

	//UPDATE
	if (isset($_POST["update"]))
    {
		if ($_GET["id_language"]<=0) echo '<div class="failure">Es konnte keine ID für den Benutzer gefunden werden!</div>';
		elseif ($_POST["language"]=="") echo '<div class="failure">Der Sprachetext darf nicht leer sein!</div>';
		else
        {
			q("UPDATE cms_languages
						 SET language='".addslashes(stripslashes($_POST["language"]))."',
						 	 code='".addslashes(stripslashes($_POST["code"]))."',
						 	 active='".$_POST["active"]."',
						 	 ordering='".$_POST["ordering"]."',
						 	 lastmod='".time()."',
						 	 lastmod_user='".$_SESSION["id_user"]."'
						 WHERE id_language=".$_GET["id_language"].";", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Benutzerprofil erfolgreich aktualisiert!</div>';
        }
    }

	//READ
	if (isset($_GET["id_language"]))
	{
		$results=q("SELECT * FROM cms_languages WHERE id_language=".$_GET["id_language"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["language"]=$row["language"];
		$_POST["code"]=$row["code"];
		$_POST["active"]=$row["active"];
		$_POST["ordering"]=$row["ordering"];
	}

	//EDITOR
    echo '<h2>Sprache-Editor</h2>';
	if (isset($_GET["id_language"]))
	{
		echo '<form action="backend_cms_language_editor.php?id_language='.$_GET["id_language"].'" method="post">';
	}
	else
	{
		echo '<form action="backend_cms_language_editor.php" method="post">';
	}
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Aktiviert?</td>';
	echo '		<td>';
	echo '			<select name="active">';
	if ($_POST["active"]==0) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.selected.' value="0">Nein</option>';
	if ($_POST["active"]>0) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="1">Ja</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Sprache</td>';
	echo '		<td><input style="width:300px;" type="text" name="language" value="'.$_POST["language"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Kürzel</td>';
	echo '		<td><input style="width:300px;" type="text" name="code" value="'.$_POST["code"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Sortierung</td>';
	echo '		<td><input style="width:300px;" type="text" name="ordering" value="'.$_POST["ordering"].'" /></td>';
	echo '	</tr>';
	if (isset($_GET["id_language"]))
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="update" value="Sprache aktualisieren" /></td></tr>';
	}
	else
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="create" value="Sprache anlegen" /></td></tr>';
	}
	echo '</table>';
	echo '</form>';
    
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>