<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//REMOVE
	if (isset($_POST["remove"]))
    {
		if ($_POST["id_label"]<=0) echo '<div class="failure">Es konnte keine ID für das Stichwort gefunden werden!</div>';
		else
		{
			q("DELETE FROM cms_labels WHERE id_label=".$_POST["id_label"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Stichwort erfolgreich gelöscht!</div>';
		}
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > Stichworte';
	echo '</p>';


	//LIST
	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Stichworte</span>';
	echo '<a href="backend_cms_label_editor.php" title="Neues Stichwort anlegen"><img src="images/icons/24x24/page_add.png" alt="Neues Stichwort anlegen" title="Neues Stichwort anlegen" /></a>';
	echo '</h1>';
	echo '<p>In der nachfolgenden Liste, finden Sie alle derzeit im CMS abgelegten Stichworte.</p>';
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>Stichwort</th>';
	echo '		<th>Letzte Bearbeitung</th>';
	echo '		<th>Optionen</th>';
	echo '	</tr>';
	$results=q("SELECT * FROM cms_labels ORDER BY lastmod DESC;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		echo '<tr>';
		echo '	<td><a href="backend_cms_label_editor.php?id_label='.$row["id_label"].'">'.$row["label"].'</a></td>';
		echo '	<td>'.date("d-m-Y H:i", $row["lastmod"]).'</td>';
		echo '	<td>';
		echo '<form action="backend_cms_labels.php" style="margin:0; border:0; padding:0; float:right;" method="post">';
		echo '	<input type="hidden" name="id_label" value="'.$row["id_label"].'" />';
		echo '	<input type="hidden" name="remove" value="Stichwort löschen" />';
		echo '	<input style="margin:2px 8px 2px 0px; border:0; padding:0; float:right;" type="image" src="images/icons/24x24/page_remove.png" alt="Stichwort löschen" title="Stichwort löschen" onclick="return confirm(\'Stichwort wirklich löschen?\');" />';
		echo '</form>';
		echo '		<a href="backend_cms_label_editor.php?id_label='.$row["id_label"].'" title="Stichwort bearbeiten"><img src="images/icons/24x24/page.png" alt="Stichwort bearbeiten" title="Stichwort bearbeiten" /></a>';
		echo '	</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>