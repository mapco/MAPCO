<?php
	include("config.php");
	$leftmenu=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//CREATE
	if ($_POST["form_button"]=="Datei anlegen")
    {
		/*
		if ($_POST["title"]=="") echo '<div class="failure">Der Titel darf nicht leer sein!</div>';
		elseif ($_POST["file"]=="") echo '<div class="failure">Der Artikeltext darf nicht leer sein!</div>';
		else
        {
			q("INSERT INTO cms_files (title, file, published, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".addslashes(stripslashes($_POST["title"]))."', '".addslashes(stripslashes($_POST["file"]))."', '0', '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbweb, __FILE__, __LINE__);
			$_GET["id_file"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Artikel erfolgreich angelegt!</div>';
        }
		*/
	}

	//UPDATE
	if ($_POST["form_button"]=="Datei aktualisieren")
    {
		if ($_GET["id_file"]<=0) echo '<div class="failure">Es konnte keine ID für den Benutzer gefunden werden!</div>';
		else
        {
			q("UPDATE cms_files
						 SET description='".addslashes(stripslashes($_POST["description"]))."',
						 	 lastmod='".time()."',
						 	 lastmod_user='".$_SESSION["id_user"]."'
						 WHERE id_file=".$_GET["id_file"].";", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Datei erfolgreich aktualisiert!</div>';
        }
    }

	//READ
	if (isset($_GET["id_file"]))
	{
		$results=q("SELECT * FROM cms_files WHERE id_file=".$_GET["id_file"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["filename"]=$row["filename"];
		$_POST["extension"]=$row["extension"];
		$_POST["description"]=$row["description"];
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > <a href="backend_cms_files.php">Dateien</a>';
	echo ' > Editor';
	echo '</p>';


	//EDITOR
    echo '<h2>Datei-Editor</h2>';
	if (isset($_GET["id_file"]))
	{
		echo '<form action="backend_cms_file_editor.php?id_file='.$_GET["id_file"].'" method="post">';
	}
	else
	{
		echo '<form action="backend_cms_file_editor.php" method="post">';
	}
	echo '<table>';
	if ($_GET["id_file"]>0)
	{
		echo '	<tr>';
		echo '		<td>'.VORSCHAU.'</td>';
		$dir=bcdiv($row["id_file"], 1000, 0);
		echo '	<td><img style="width:100px; max-height:100px;" src="files/'.$dir.'/'.$_GET["id_file"].'.'.$_POST["extension"].'" /></td>';
		echo '	</tr>';
	}
	echo '	<tr>';
	echo '		<td>'.BESCHREIBUNG.'</td>';
	echo '		<td><textarea style="width:500px; height:300px;" name="description">'.$_POST["description"].'</textarea>';
	echo '	</tr>';
	if (isset($_GET["id_file"]))
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="form_button" value="Datei aktualisieren" /></td></tr>';
	}
	else
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="form_button" value="Datei anlegen" /></td></tr>';
	}
	echo '</table>';
	echo '</form>';


	//Neu
	if ($_POST["form_button"]=="Label zuweisen")
    {
		if ($_POST["title"]=="") echo '<div class="failure">Der Titel darf nicht leer sein!</div>';
		elseif ($_POST["file"]=="") echo '<div class="failure">Der Artikeltext darf nicht leer sein!</div>';
		else
        {
			q("INSERT INTO cms_files_labels (file_id, label_id) VALUES('".addslashes(stripslashes($_POST["title"]))."', '".addslashes(stripslashes($_POST["file"]))."', '0', '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbweb, __FILE__, __LINE__);
			$_GET["id_file"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Artikel erfolgreich angelegt!</div>';
        }
	}


	//labels
	if (isset($_GET["id_file"]))
	{
		echo '<h1>Zugewiesene Stichworte</h1>';
		$results=q("SELECT * FROM cms_labels ORDER BY label;", $dbweb, __FILE__, __LINE__);
		echo '<form>';
		echo '	<select name="id_label">';
		while($row=mysqli_fetch_array($results))
		{
			echo '<option value="'.$row["id_label"].'">'.$row["label"].'</option>';
		}
		echo '	</select>';
		echo '	<input class="formbutton" type="submit" name="form_button" value="Label zuweisen" />';
		echo '</form>';
		
		echo '<table>';
		$results=q("SELECT * FROM cms_files_labels WHERE file_id='".$_GET["id_file"]."';", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			echo '<tr>';
			echo '	<td>'.$row["id_label"].'</td>';
			echo '	<td>';
			echo '		<form action="backend_cms_file_editor.php?id_file='.$_GET["id_file"].'" style="margin:0; border:0; padding:0; float:right;" method="post">';
			echo '			<input type="hidden" name="id_label" value="'.$row["id_label"].'" />';
			echo '			<input type="hidden" name="form_button" value="Stichwort löschen" />';
			echo '			<input style="margin:2px 8px 2px 0px; border:0; padding:0; float:right;" type="image" src="images/icons/24x24/note_remove.png" alt="Stichwort löschen" title="Stichwort löschen" onclick="return confirm(\'Stichwort wirklich löschen?\');" />';
			echo '		</form>';
			echo '	</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
    
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>