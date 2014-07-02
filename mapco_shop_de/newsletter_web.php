<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_newsletter.php");
	include("functions/mapco_cutout.php");

	echo '<div id="mid_right_column">';
	
	if ($_GET["id_article"]>0)
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=".$_GET["id_article"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$mail = newsletter("-", $row["id_article"], $row["title"], $row["article"], $row["introduction"], $row["firstmod"], "de");
		echo cutout($mail, '<!-- Link Start -->', '<!-- Link Stop -->');
	}
	
	include("templates/".TEMPLATE."/footer.php");
?>

