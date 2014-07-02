<?php
	include("config.php");
	$leftmenu=7;
	$columns="MR";
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//Löschen
	if ($_POST["form_button"]=="Artikel löschen")
    {
		if ($_POST["id_article"]<=0) echo '<div class="failure">Es konnte keine ID für den Artikel gefunden werden!</div>';
		else
		{
			q("DELETE FROM cms_articles WHERE id_article=".$_POST["id_article"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Artikel erfolgreich gelöscht!</div>';
		}
	}

	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Wareneingang</span>';
	echo '<a href="backend_cms_article_editor.php" title="Neuen Artikel anlegen"><img src="images/icons/24x24/page_add.png" alt="Neuen Artikel anlegen" title="Neuen Artikel anlegen" /></a>';
	echo '</h1>';
	echo '<p>Geben Sie eine Artikelnummer ein oder scannen Sie einen Strichcode ab, um zum Artikel zu springen.</p>';
	$results=q("SELECT * FROM cms_articles ORDER BY lastmod DESC;", $dbweb, __FILE__, __LINE__);
//	$foldername="";
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>Artikelnummer</th>';
	echo '		<td><input style="width:200px; height:30px; font-size:20px;" type="text" name="artnr" value="'.$_POST["artnr"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<th>Strichcode</th>';
	echo '		<td><input style="width:500px; height:30px; font-size:20px;" type="text" name="barcode" value="'.$_POST["barcode"].'" /></td>';
	echo '	</tr>';
	echo '</table>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>