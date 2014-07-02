<?php
	include("config.php");
	$leftmenu=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//CREATE
	if (isset($_POST["label_add"]))
    {
		if ($_POST["label"]=="") echo '<div class="failure">Der Stichworttext darf nicht leer sein!</div>';
		else
        {
			q("INSERT INTO cms_labels (label, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".addslashes(stripslashes($_POST["label"]))."', '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbweb, __FILE__, __LINE__);
			$_GET["id_label"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Stichwort erfolgreich angelegt!</div>';
        }
	}

	//UPDATE
	if (isset($_POST["label_update"]))
    {
		if ($_GET["id_label"]<=0) echo '<div class="failure">Es konnte keine ID für den Benutzer gefunden werden!</div>';
		elseif ($_POST["label"]=="") echo '<div class="failure">Der Stichworttext darf nicht leer sein!</div>';
		else
        {
			q("UPDATE cms_labels
						 SET label='".addslashes(stripslashes($_POST["label"]))."',
						 	 lastmod='".time()."',
						 	 lastmod_user='".$_SESSION["id_user"]."'
						 WHERE id_label=".$_GET["id_label"].";", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Benutzerprofil erfolgreich aktualisiert!</div>';
        }
    }

	//READ
	if (isset($_GET["id_label"]))
	{
		$results=q("SELECT * FROM cms_labels WHERE id_label=".$_GET["id_label"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["label"]=$row["label"];
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > <a href="backend_cms_labels.php">Stichworte</a>';
	echo ' > Editor';
	echo '</p>';


	//EDITOR
    echo '<h2>Stichwort-Editor</h2>';
	if (isset($_GET["id_label"]))
	{
		echo '<form action="backend_cms_label_editor.php?id_label='.$_GET["id_label"].'" method="post">';
	}
	else
	{
		echo '<form action="backend_cms_label_editor.php" method="post">';
	}
	echo '<table>';
	echo '	<tr>';
	echo '		<td>'.STICHWORT.'</td>';
	echo '		<td><input style="width:500px;" type="text" name="label" value="'.$_POST["label"].'" /></td>';
	echo '	</tr>';
	if (isset($_GET["id_label"]))
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="label_update" value="Stichwort aktualisieren" /></td></tr>';
	}
	else
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="label_add" value="Stichwort anlegen" /></td></tr>';
	}
	echo '</table>';
	echo '</form>';
    
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>