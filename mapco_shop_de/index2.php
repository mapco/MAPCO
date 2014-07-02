<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");

	//left column
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	
	//Videobox
	echo '<div id="videobox" style="width:548px; margin:0px 10px 10px 10px; border:0; padding:10px; background:url('.PATH.'images/videobox_bg.jpg); display:inline; float:left;">';
	$i=0;
	$results=q("SELECT * FROM cms_videos WHERE active=1 ORDER BY ordering LIMIT 4;", $dbweb, __FILE__, __LINE__);
	//automechanika live fix
//		if ( date("H", time())>18 or date("H", time())<9 )
//		{
//			$row=mysqli_fetch_array($results);
//			$autoplay="autoplay";
//		}
	while($row=mysqli_fetch_array($results))
	{
		$i++;
		if ($i==1)
		{
			echo ' <video id="player" style="width:547px; height:308px; background-color:black;" src="'.PATH.'player/videos/'.$row["file"].'" type="video/mp4" controls '.$autoplay.'>';
			echo '<div style="color:white;">Schade – hier käme ein Video, wenn Ihr Browser HTML5 Unterstützung hätte!</div>';
			echo '</video>';
		}
		if ($i!=4) $style=' style="margin:10px 22px 0px 0px; display:inline; float:left;"'; else $style=' style="margin:10px 0px 0px 0px; display:inline; float:left;"';
		echo '<a'.$style.' href="javascript:play(\''.$row["file"].'\');">';
		echo '	<img style="display:inline; float:left;" src="'.PATH.'player/videos/'.$row["preview"].'" alt="'.$row["title"].'" title="'.$row["title"].'" />';
		echo '</a>';
	}
	echo '</div>';
	include("modules/cms_rightcolumn.php");
	
	//news
	echo '<div id="mid_column">';
	if (isset($_GET["id_article"]))
	{
		show_article($_GET["id_article"]);
	}
	else
	{
		$results=q("SELECT a.id_article FROM cms_articles AS a, cms_articles_labels AS b WHERE b.label_id=3 AND b.article_id=a.id_article AND a.published ORDER BY ordering LIMIT 4;", $dbweb, __FILE__, __LINE__);
		$i=0;
		while($row=mysqli_fetch_array($results))
		{
			$i++;
			$results2=q("SELECT * FROM cms_articles AS a, cms_languages AS b WHERE a.id_article=".$row["id_article"]." AND a.language_id=b.id_language;", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			echo '<h1>'.$row2["title"].'</h1>';
			echo substr($row2["article"], 0, strpos($row2["article"], "</p>"));
			echo '<p><a style="font-size:14px; float:right;" href="index.php?lang='.$_GET["lang"].'&id_article='.$row["id_article"].'">'.t("weiterlesen").'</a></p>';
			if ($i<4) echo '<br style="clear:both;" /><hr />';
		}
	}
	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>