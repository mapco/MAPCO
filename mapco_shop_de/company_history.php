<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_show_article.php");

	$_GET["id"]=44;
	$results=q("SELECT * FROM jos_content WHERE id='".$_GET["id"]."';", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)==0) echo '<p>Die Seite konnte nicht gefunden werden.</p>';
	else
	{
		$row=mysqli_fetch_array($results);
		echo '<div id="mid_right_column">';
		echo '<h1>'.$row["title"].'</h1>';
		echo $row["introtext"];
		echo $row["fulltext"];
		echo '</div>';
	}

	include("templates/".TEMPLATE."/footer.php");
?>