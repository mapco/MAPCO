<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > Sprachen';
	echo '</p>';

	//CREATE
	if (isset($_POST["create"]))
    {
		if ($_POST["language"]=="") echo '<div class="failure">Die Sprache muss einen Namen haben.</div>';
		else
        {
			q("INSERT INTO cms_languages (language, code, active, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".addslashes(stripslashes($_POST["language"]))."', '".addslashes(stripslashes($_POST["code"]))."', ".$_POST["active"].", ".$_POST["ordering"].", '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbweb, __FILE__, __LINE__);
			$_GET["id_language"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Sprache erfolgreich angelegt!</div>';
			$_POST["language"]="";
			$_POST["code"]="";
			$_POST["active"]="";
			$_POST["ordering"]="";
        }
	}


	//EDITOR


	//REMOVE
	if (isset($_POST["remove"]))
    {
		if ($_POST["id"]<=0) echo '<div class="failure">Es konnte keine ID für die Sprache gefunden werden!</div>';
		else
		{
			q("DELETE FROM cms_languages WHERE id_language=".$_POST["id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Sprache erfolgreich gelöscht!</div>';
		}
	}

	//LIST
	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Sprache</span>';
	echo '<img style="cursor:pointer;" src="images/icons/24x24/page_add.png" alt="Neue Sprache anlegen" title="Neue Sprache anlegen" onclick="popup(\'modules/backend_cms_language_editor.php\', 800, 600); return false;" />';
	echo '</h1>';
	echo '<p>In der nachfolgenden Liste, finden Sie alle derzeit im CMS abgelegten Sprachen.</p>';
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>Nr.</th>';
	echo '		<th>Sprache</th>';
	echo '		<th>Kürzel</th>';
	echo '		<th>Optionen</th>';
	echo '	</tr>';
	$results=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		echo '<tr>';
		echo '	<td>'.$row["ordering"].'</td>';
		echo '	<td><a href="backend_cms_language_editor.php?id_language='.$row["id_language"].'">'.$row["language"].'</a></td>';
		echo '	<td>'.$row["code"].'</td>';
		echo '	<td>';
		echo '<form action="backend_cms_languages.php" style="margin:0; border:0; padding:0; float:right;" method="post">';
		echo '	<input type="hidden" name="id" value="'.$row["id"].'" />';
		echo '	<input type="hidden" name="remove" value="Sprache löschen" />';
		echo '	<input style="margin:2px 8px 2px 0px; border:0; padding:0; float:right;" type="image" src="images/icons/24x24/remove.png" alt="Sprache löschen" title="Sprache löschen" onclick="return confirm(\'Sprache wirklich löschen?\');" />';
		echo '</form>';
		echo '		<a href="backend_cms_language_editor.php?id_language='.$row["id_language"].'" title="Sprache bearbeiten"><img src="images/icons/24x24/edit.png" alt="Sprache bearbeiten" title="Sprache bearbeiten" /></a>';
		echo '	</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>