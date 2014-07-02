<?php
	require_once("../functions/cms_t.php");

	$user=array();
	$results=q("SELECT * FROM cms_users;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		$users[$row["id_user"]]=$row["username"];
	}
	
	//labels
	$results=q("SELECT * FROM cms_labels WHERE site_id IN (0, ".$_SESSION["id_site"].") ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	echo '<ul id="labels" class="orderlist" style="width:300px;">';
	echo '	<li class="header" style="width:288px;">Stichworte';
	echo '	<a style="float:right;" href="javascript:label_add();" title="'.t("Neues Stichwort hinzufügen").'"><img src="images/icons/24x24/add.png" alt="'.t("Neues Stichwort hinzufügen").'" title="'.t("Neues Stichwort hinzufügen").'" /></a>';
	echo '	</li>';
	if ( $_POST["id_label"]==0 ) $style=' style="width:288px; background:#ffffff; font-weight:bold;"'; else $style=' style="width:288px; background:#ffffff;"';
	echo '<li'.$style.' class="header">';
	echo '	<div style="width:20px; float:left;"></div>';
	echo '	<a href="javascript:label(0);">Alle</a>';
	echo '</li>';
	while( $row=mysqli_fetch_array($results) )
	{
		if ( $_POST["id_label"]==$row["id_label"] ) $style=' style="width:288px; font-weight:bold;"'; else $style=' style="width:288px;"';
		echo '<li'.$style.' id="label'.$row["id_label"].'">';
		//ordering
		echo '	<div style="width:10px; float:left;">'.$row["ordering"].'.</div>';
		//label
		$results2=q("SELECT * FROM cms_articles_labels WHERE label_id=".$row["id_label"].";", $dbweb, __FILE__, __LINE__);
		echo '<div style="width:168px; float:left;">';
		echo '	<a href="javascript:label('.$row["id_label"].');">'.$row["label"].' ('.mysqli_num_rows($results2).')</a>';
		echo '</div>';
		//options
		echo '	<div style="width:50px; float:right;">';
		echo '		<img src="images/icons/24x24/remove.png" onclick="label_remove('.$row["id_label"].');" />';
		echo '		<img src="images/icons/24x24/edit.png" onclick="label_edit('.$row["id_label"].');" />';
		echo '	</div>';
		echo '</li>';
	}
	echo '</ul>';
	
	//articles
	if ( $_POST["id_label"]==0 )
	{
		$results=q("SELECT * FROM cms_articles WHERE site_id IN (0, ".$_SESSION["id_site"].") ORDER BY ordering LIMIT 100;", $dbweb, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT a.ordering, b.id_article, b.published, b.title, b.firstmod, b.firstmod_user, b.lastmod, b.lastmod_user FROM cms_articles_labels AS a, cms_articles AS b WHERE b.site_id IN (0, ".$_SESSION["id_site"].") AND a.label_id=".$_POST["id_label"]." AND a.article_id=b.id_article ORDER BY a.ordering LIMIT 100;", $dbweb, __FILE__, __LINE__);
	}
	if( isset($_POST["id_label"]) and $_POST["id_label"]>0 )
	{
		$results2=q("SELECT * FROM cms_labels WHERE site_id IN (0, ".$_SESSION["id_site"].") AND id_label=".$_POST["id_label"].";", $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		echo '<div style="width:1000px; margin:5px; border:1px solid blue; padding:5px; background-color:#c4def3; float:left;">';
		echo nl2br($row2["description"]);
		echo '</div>';
	}
	echo '<ul class="orderlist" id="articles" style="width:1000px; float:left;">';
	echo '	<li class="header" style="width:988px;">';
	echo '		<div style="width:20px;">Nr.</div>';
	echo '		<div style="width:75px;">Öffentlich</div>';
	echo '		<div style="width:373px; text-align:left;">Titel</div>';
	echo '		<div style="width:75px;">Stichworte</div>';
	echo '		<div style="width:120px;">Autor</div>';
	echo '		<div style="width:120px;">Letzte Bearbeitung</div>';
	echo '		<a style="float:right;" href="backend_cms_article_editor.php?lang='.$_SESSION["lang"].'" title="'.t("Neuen Artikel hinzufügen").'"><img src="images/icons/24x24/add.png" alt="'.t("Neuen Artikel hinzufügen").'" title="'.t("Neuen Artikel hinzufügen").'" /></a>';
	echo '	</li>';
	while ($row=mysqli_fetch_array($results))
	{
		echo '<li id="article'.$row["id_article"].'" style="width:988px;">';
		
		//ordering
		echo '	<div style="width:20px;">'.$row["ordering"].'</div>';
		
		//published
		if ($row["published"]==0) $img='<img style="float:none;" src="images/icons/16x16/remove.png" alt="Unveröffentlicht" title="Unveröffentlich" />';
		else $img='<img style="float:none;" src="images/icons/16x16/accept.png" alt="Veröffentlicht" title="Veröffentlich" />';
		echo '	<div style="width:75px;">'.$img.'</div>';
		
		//title
		echo '<div style="width:373px; text-align:left;">';
		if ($row["title"]=="") $title="[leer]"; else $title=$row["title"];
		echo '	<a href="backend_cms_article_editor.php?lang='.$_SESSION["lang"].'&id_article='.$row["id_article"].'" title="'.t("Artikel bearbeiten").'">'.$title.'</a>';
		echo '</div>';
		
		//labels
		echo '<div style="width:75px;">';
		$results2=q("SELECT * FROM cms_articles_labels AS a, cms_labels AS b WHERE b.site_id IN (0, ".$_SESSION["id_site"].") AND a.article_id=".$row["id_article"]." AND a.label_id=b.id_label;", $dbweb,__FILE__, __LINE__);
		if ( mysqli_num_rows($results2)>0 )
		{
			$i=0;
			while ($row2=mysqli_fetch_array($results2))
			{
				if ($i>0) echo ', ';
				echo $row2["label"];
				$i++;
			}
		} else echo '-';
		echo '</div>';

		//author
		echo '<div style="width:120x;">';
		if ( isset($users[$row["firstmod_user"]]) ) $user=$users[$row["firstmod_user"]]; else $user='unbekannt';
		echo $user.'<br />'.date("d-m-Y H:i", $row["firstmod"]);
		echo '</div>';

		//lastmod
		echo '<div style="width:120x;">';
		if ( isset($users[$row["lastmod_user"]]) ) $user=$users[$row["lastmod_user"]]; else $user='unbekannt';
		echo $user.'<br />'.date("d-m-Y H:i", $row["lastmod"]);
		echo '</div>';
		
		//options
		echo '<a style="float:right;" href="javascript:article_remove('.$row["id_article"].')" title="'.t("Artikel löschen").'"><img src="images/icons/24x24/remove.png" alt="'.t("Artikel löschen").'" title="'.t("Artikel löschen").'" /></a>';
		echo '<a style="float:right;" href="backend_cms_article_editor.php?lang='.$_SESSION["lang"].'&id_article='.$row["id_article"].'" title="'.t("Artikel bearbeiten").'"><img src="images/icons/24x24/edit.png" alt="'.t("Artikel bearbeiten").'" title="'.t("Artikel bearbeiten").'" /></a>';

		echo '</li>';
	}
	echo '</ul>';
?>