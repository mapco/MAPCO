<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_url_encode.php");
	

	//left column
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	
	//news
	echo '<div id="mid_column">';
	echo '<h1>MAPCO-TV</h1>';
	echo '<img src="'.PATH.'images/schrauber.png" alt="Der Schrauber" title="Der Schrauber" />';
	echo '<hr />';
	$results=q("SELECT * FROM cms_articles AS a, cms_articles_labels AS b WHERE a.article_id=0 AND a.published AND b.label_id=12 AND a.id_article=b.article_id ORDER BY firstmod DESC;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '<h3>'.$row["title"].'</h3>';
		$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["article_id"]." ORDER BY ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results3)>0)
		{
			$row3=mysqli_fetch_array($results3);
			$results4=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=2;", $dbweb, __FILE__, __LINE__);
			if ( mysqli_num_rows($results4)>0 )
			{
				$row4=mysqli_fetch_array($results4);
				$filename='files/'.floor(bcdiv($row4["id_file"], 1000)).'/'.$row4["id_file"].'.'.$row4["extension"];
				echo '<a href="'.PATHLANG.'presse/mapco-tv/'.$row["id_article"].'/'.url_encode($row["title"]).'" title="'.$row["title"].'">';
				echo '	<img src="'.PATH.$filename.'" alt="'.$row["title"].'" title="'.$row["title"].'" />';
				echo '</a>';
				echo '<br /><br />';
			}
		}
		echo '<p>'.$row["introduction"].'</p>';
		echo '<p><a style="font-size:14px; float:right;" href="'.PATHLANG.'presse/mapco-tv/'.$row["id_article"].'/'.url_encode($row["title"]).'" title="'.$row["title"].'">'.t("ansehen").'</a></p>';
		echo '<br style="clear:both;" /><hr />';
	}
	echo '</div>';
	
	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>