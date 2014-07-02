<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");

	$_GET["id"]=113;
	$results=q("SELECT * FROM jos_content WHERE id='".$_GET["id"]."';", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)==0) echo '<p>Die Seite konnte nicht gefunden werden.</p>';
	else
	{
		$row=mysqli_fetch_array($results);
		echo '<div class="mid_box">';
		echo '<h1>'.$row["title"].'</h1>';
		if (strstr($row["introtext"], "{loadposition overview}")) include("modules/cms_products_overview.php");
		else
		{
			echo $row["introtext"];
			echo $row["fulltext"];
		}
		echo '</div>';
	}

	include("templates/".TEMPLATE."/footer.php");
?>