<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	
	echo '<div id="mid_column">';
	echo '<h1>'.t("Kataloge", __FILE__, __LINE__).'</h1>';
	
	$results=q("SELECT * FROM web_catalogs AS a WHERE active ORDER BY title;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		echo '<div class="catalog">';
		echo '	<span style="font-weight:bold;">'.t($row["title"], __FILE__, __LINE__).'</span>';
		if (file_exists('images/kataloge/'.strtolower($row["number"].$_SESSION["lang"]).'.pdf'))
		{
			echo '	<a target="_blank" href="'.PATH.'images/kataloge/'.strtolower($row["number"].$_SESSION["lang"]).'.pdf" title="'.t($row["title"], __FILE__, __LINE__).'">';
		}
		elseif (file_exists('images/kataloge/'.strtolower($row["number"]).'.pdf'))
		{
			echo '	<a target="_blank" href="'.PATH.'images/kataloge/'.strtolower($row["number"]).'.pdf" title="'.t($row["title"], __FILE__, __LINE__).'">';
		}
		echo '		<img src="'.PATH.'images/kataloge/'.strtolower($row["number"]).'.jpg" alt="'.t($row["title"], __FILE__, __LINE__).'" title="'.t($row["title"], __FILE__, __LINE__).'" />';
		if (file_exists('images/kataloge/'.strtolower($row["number"].$_SESSION["lang"]).'.pdf'))
		{
			echo '	<br />'.t("Download", __FILE__, __LINE__).' ('.number_format(filesize('images/kataloge/'.strtolower($row["number"].$_SESSION["lang"]).'.pdf')/(1024*1024), 2).' MB)';
			echo '	</a>';
		}
		elseif (file_exists('images/kataloge/'.strtolower($row["number"]).'.pdf'))
		{
			echo '	<br />'.t("Download", __FILE__, __LINE__).' ('.number_format(filesize('images/kataloge/'.strtolower($row["number"]).'.pdf')/(1024*1024), 2).' MB)';
			echo '	</a>';
		}
		echo '</div>';
	}
	
	echo '</div>';
	
	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>