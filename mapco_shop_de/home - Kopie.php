<?php
	$domain = str_replace("www.", "", $_SERVER['HTTP_HOST']);
	
	if ( isset($_GET["id_article"]) and $_GET["id_article"]>0 )
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=".$_GET["id_article"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$title=$row["title"];
		if (strlen($title)>70)
		{
			$title=substr($title, 0, 66).'...';
		}
		$description=htmlentities(utf8_decode($row["introduction"]));
		$author='https://plus.google.com/107035270187211720288?rel=author';
	}
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_show_article.php");
	include("functions/mapco_gewerblich.php");
	include("functions/cms_url_encode.php");

	//NEWSLETTER
	include("modules/cms_newsletter.php");

	//left column
	include("templates/".TEMPLATE."/cms_leftcolumn.php");


	// Suche Box
	echo '<div id="suche_box" style="display:inline">';
	echo '<h3>'.t("Fahrzeug-Teilesuche").'</h3>';
	echo '<div id="suche_box_content" style="display:inline">';
	
		echo '<div id="pkw_suche" style="display:inline">';
			include("modules/shop_start_searchbycar.php");
		echo '</div>';
	
		echo '<div id="kba_suche" style="display:inline">';
			include("modules/shop_start_searchbykba.php");
		echo '</div>';
		
	echo '</div>'; // DIV ID="suche_box_content"
	echo '</div>'; // DIV ID="suche_box"

	//Videobox
	if (!isset($_GET["id_article"]))
	{
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
	}
		
	//Gewerbskunde?
	$gewerblich=gewerblich($_SESSION["id_user"]);
	
	//news
	echo '<div id="mid_column">';
	if (isset($_GET["id_article"]) and $_GET["id_article"]>0)
	{
		show_article($_GET["id_article"]);
	}
	else
	{
//		echo '<h1>'.t("Aktuelles").'!</h1><hr />';
		$results=q("SELECT * FROM cms_articles_labels WHERE label_id=3 ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		$i=0;
		while($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT * FROM cms_articles_labels WHERE article_id=".$row["article_id"]." AND label_id=9;", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results2)==0 or (mysqli_num_rows($results2)>0 and $gewerblich)  )
			{
				$results2=q("SELECT * FROM cms_articles AS a, cms_languages AS b WHERE a.id_article=".$row["article_id"]." AND a.language_id=b.id_language;", $dbweb, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				if($row2["published"]>0)
				{
					$i++;
					if ($i==1) echo '<h1>'.$row2["title"].'</h1>';
					else echo '<h2>'.$row2["title"].'</h2>';
					
					$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["article_id"]." ORDER BY ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
					if (mysqli_num_rows($results3)>0)
					{
						$row3=mysqli_fetch_array($results3);
						$results4=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=20;", $dbweb, __FILE__, __LINE__);
						if ( mysqli_num_rows($results4)>0 )
						{
							$row4=mysqli_fetch_array($results4);
							$filename='files/'.floor(bcdiv($row4["id_file"], 1000)).'/'.$row4["id_file"].'.'.$row4["extension"];
							echo '<a href="'.PATHLANG.'news/'.$row["article_id"].'/'.url_encode($row2["title"]).'" title="'.$row2["title"].'">';
							echo '	<img src="'.PATH.$filename.'" alt="'.$row2["title"].'" title="'.$row2["title"].'" />';
							echo '</a>';
							echo '<br /><br />';
						}
					}
					if ($row2["introduction"]!="") 
					{
						if ($row2["format"]==0) echo nl2br($row2["introduction"]);
						else echo $row2["introduction"];
					}
					else echo substr($row2["article"], 0, strpos($row2["article"], "</p>"));
					echo '<p><a style="font-size:14px; float:right;" href="'.PATHLANG.'news/'.$row["article_id"].'/'.url_encode($row2["title"]).'" title="'.$row2["title"].'">'.t("weiterlesen").'</a></p>';
					if ($i<2) echo '<br style="clear:both;" /><hr />';
				}
				if($i==3) break;
			}
		}
		echo '<br style="clear:both;" /><hr />';
		echo '<a style="font-size:14px; float:left;" href="'.PATHLANG.'presse/newsletter-archiv/">'.t("Newsletter-Archiv").'</a>';
		echo '<a style="font-size:14px; float:right;" href="'.PATHLANG.'presse/nachrichten-archiv/">'.t("Nachrichten-Archiv").'</a>';
	}
	echo '</div>';
	
	if (isset($_GET["id_article"]) and $_GET["id_article"]>0) include("templates/".TEMPLATE."/cms_rightcolumn.php");


	include("templates/".TEMPLATE."/footer.php");

?>