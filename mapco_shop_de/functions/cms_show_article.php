<!--
	1 ) Reference to the files containing the JavaScript and CSS.
	These files must be located on your server.
-->

<script type="text/javascript" src="<?php echo PATH; ?>modules/highslide/highslide/highslide-with-gallery.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo PATH; ?>modules/highslide/highslide/highslide.css" />
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="<?php echo PATH; ?>modules/highslide/highslide-ie6.css" />
<![endif]-->

<script type="text/javascript">
// Language strings
hs.lang = {
   cssDirection:     'ltr',
   loadingText :     'Lade...',
   loadingTitle :    'Klick zum Abbrechen',
   focusTitle :      'Klick um nach vorn zu bringen',
   fullExpandTitle : 'Zur Originalgröße erweitern',
   fullExpandText :  'Vollbild',
   creditsText :     '',
   creditsTitle :    'Gehe zur Highslide JS Homepage',
   previousText :    'Voriges',
   previousTitle :   'Voriges (Pfeiltaste links)',
   nextText :        'Nächstes',
   nextTitle :       'Nächstes (Pfeiltaste rechts)',
   moveTitle :       'Verschieben',
   moveText :        'Verschieben',
   closeText :       'Schließen',
   closeTitle :      'Schließen (Esc)',
   resizeTitle :     'Größe wiederherstellen',
   playText :        'Abspielen',
   playTitle :       'Slideshow abspielen (Leertaste)',
   pauseText :       'Pause',
   pauseTitle :      'Pausiere Slideshow (Leertaste)',
   number :          'Bild %1/%2',
   restoreTitle :    'Klick um das Bild zu schließen, klick und ziehe um zu verschieben. Benutze Pfeiltasten für vor und zurück.'
};
</script>

<!--
    2) Optionally override the settings defined at the top
    of the highslide.js file. The parameter hs.graphicsDir is important!
-->

<script type="text/javascript">
	hs.graphicsDir = '<?php echo PATH; ?>modules/highslide/highslide/graphics/';
	hs.align = 'center';
	hs.transitions = ['expand', 'crossfade'];
	hs.fadeInOut = true;
	hs.dimmingOpacity = 0.8;
	hs.wrapperClassName = 'borderless floating-caption';
	hs.captionEval = 'this.thumb.alt';
	hs.marginLeft = 100; // make room for the thumbstrip
	hs.marginBottom = 80 // make room for the controls and the floating caption
	hs.numberPosition = 'caption';
	hs.lang.number = '%1/%2';

	// Add the slideshow providing the controlbar and the thumbstrip
	hs.addSlideshow({
		//slideshowGroup: 'group1',
		interval: 5000,
		repeat: false,
		useControls: true,
		overlayOptions: {
			className: 'text-controls',
			position: 'bottom center',
			relativeTo: 'viewport',
			offsetX: 50,
			offsetY: -5

		},
		thumbstrip: {
			position: 'middle left',
			mode: 'vertical',
			relativeTo: 'viewport'
		}
	});

	// Add the simple close button
	hs.registerOverlay({
		html: '<div class="closebutton" onclick="return hs.close(this)" title="Close"></div>',
		position: 'top right',
		fade: 2 // fading the semi-transparent overlay looks bad in IE
	});
</script>

<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("show_article"))
	{
		function show_article($id_article, $echo=true)
		{
			global $dbweb;
			
			if (!is_numeric($id_article))
			{
				echo 'Es konnte kein gültiger Artikel gefunden werden.';
				exit;
			}
			
			$results2=q("SELECT * FROM cms_articles_images WHERE article_id=".$id_article." ORDER BY ordering", $dbweb, __FILE__, __LINE__);
			$max=mysqli_num_rows($results2);
			$results3=q("SELECT * FROm cms_articles_labels WHERE article_id=".$id_article." AND label_id=12;", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results3)>0 ) $max=0;
			if ($max>0)
			{
				echo '<script>';
				echo 'var ads = new Array();';
				echo 'var caption = new Array();';
				$i=0;
				while($row2=mysqli_fetch_array($results2))
				{
					$results3=q("SELECT * FROM cms_files WHERE original_id=".$row2["file_id"]." AND imageformat_id=20;", $dbweb, __FILE__, __LINE__);
					if ( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$filename='files/'.floor(bcdiv($row3["id_file"], 1000)).'/'.$row3["id_file"].'.'.$row3["extension"];
						echo 'ads['.$i.']=\''.PATH.$filename."';\n";
						$results=q("SELECT * FROM cms_files WHERE id_file=".$row2["file_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
						$row=mysqli_fetch_array($results);
						echo 'caption['.$i.']=\''.$row["description"]."';\n";
						$i++;
					}
				}
?>
				<script>
				var i=<?php echo $i; ?>;
				function next_image()
				{
					i++;
					if (i>=ads.length) i=0;
					document.getElementById("gallery").src=ads[i];
					document.getElementById("gallery").title=caption[i];
					document.getElementById("gallery").alt=caption[i];
					document.getElementById("caption").innerHTML='Foto Nr. '+(i+1)+' von '+ads.length+': '+caption[i];
				}
	
				</script>
<?php
			}
			
			//read languages
			$results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
			while($row=mysqli_fetch_array($results))
			{
				$language_id[$row["code"]]=$row["id_language"];
				$fallback_id[$row["code"]]=$row["language_id"];
			}
			
			//find article
			$results=q("SELECT * FROM cms_articles AS a, cms_languages AS b WHERE a.id_article=".$id_article." AND a.language_id=b.id_language;", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results)==0)
			{
				if ($echo) echo '<p>Die Seite konnte nicht gefunden werden.</p>';
				else return('<p>Die Seite konnte nicht gefunden werden.</p>');
			}
			else
			{
				//original language
				$row=mysqli_fetch_array($results);
				if ($row["code"]!=$_GET["lang"])
				{
					//translation available?
					$results2=q("SELECT * FROM cms_articles WHERE article_id=".$id_article." AND language_id=".$language_id[$_GET["lang"]].";", $dbweb, __FILE__, __LINE__);
					if (mysqli_num_rows($results2)>0)
					{
						$row2=mysqli_fetch_array($results2);
						$id_article=$row2["id_article"];
					}
					else
					{
						$results2=q("SELECT * FROM cms_articles WHERE article_id=".$id_article." AND language_id=".$fallback_id[$_GET["lang"]].";", $dbweb, __FILE__, __LINE__);
						if (mysqli_num_rows($results2)>0)
						{
							$row2=mysqli_fetch_array($results2);
							$id_article=$row2["id_article"];
						}
					}
				}
				
				//create videobox
?>

	<script>
				function play_video(file)
				{
					document.getElementById("player").setAttribute("src", file);
					document.getElementById("player").setAttribute("autoplay", "");
				}
	</script>


<?php
				$videobox = '';
				$results=q("SELECT * FROM cms_articles_videos WHERE article_id=".$id_article." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($results)>0)
				{

					$row=mysqli_fetch_array($results);
					$results2=q("SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
					$row2=mysqli_fetch_array($results2);
					$dir=floor(bcdiv($row2["id_file"], 1000));
					$videobox .= '<div id="videobox" align="center" style="width:545px; height:315px; border:0; position:static;">';
					$videobox .= ' <video id="player" style="width:545px; height:308px; background-color:black;" src="'.PATH.'files/'.$dir.'/'.$row2["id_file"].'.'.$row2["extension"].'" type="video/mp4" controls>';
					$videobox .= '<div style="color:white;">Schade – hier käme ein Video, wenn Ihr Browser HTML5 Unterstützung hätte!</div>';
					$videobox .= '</video>';
					$videobox .= '</div>';
					
					//create video selection
					$results=q("SELECT * FROM cms_articles_videos WHERE article_id=".$id_article." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
					$i=0;
					while($row=mysqli_fetch_array($results))
					{
						$i++;
						$results2=q("SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
						$row2=mysqli_fetch_array($results2);
						$dir=floor(bcdiv($row2["id_file"], 1000));
						$videobox .= '<a style="width:50px; height:50px; margin-right:5px; float:left;" href="javascript:play_video(\''.PATH.'files/'.$dir.'/'.$row2["id_file"].'.'.$row2["extension"].'\');">Video '.$i.'</a>';
					}
				}

				//get article
				$results2=q("SELECT * FROM cms_articles WHERE id_article=".$id_article.";", $dbweb, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				if ($row2["format"]==0)
				{
					$row2["introduction"]=nl2br($row2["introduction"]).'<br /><br />';
					$row2["article"]=nl2br($row2["article"]);
				}
				$row2["introduction"]=str_replace("<!-- Videobox -->", $videobox, $row2["introduction"]);
				$row2["article"]=str_replace("<!-- Videobox -->", $videobox, $row2["article"]);
				
				//show title
				echo '<h1>'.$row2["title"].'</h1>';
				
				//show gallery
				if ($max>0)
				{
					$results3=q("SELECT * FROM cms_articles_labels WHERE article_id=".$id_article." AND label_id=12 LIMIT 1;", $dbweb, __FILE__, __LINE__);
					if (!mysqli_num_rows($results3)>0)
					{
						$i=0;
						echo '<div class="highslide-gallery" style="margin:0; border:0; padding:0;">';
						$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$id_article." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
						$results=q("SELECT * FROM cms_articles_images WHERE article_id=".$id_article." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
						while($row=mysqli_fetch_array($results))
						{
							$i++;
							if ($i>1) $style=' style="display:none;"'; else $style='';
							$results4=q("SELECT * FROM cms_files WHERE original_id=".$row["file_id"]." AND imageformat_id=20 LIMIT 1;", $dbweb, __FILE__, __LINE__);
							$row4=mysqli_fetch_array($results4);
							$src2='files/'.floor($row4["id_file"]/1000).'/'.$row4["id_file"].'.'.$row4["extension"];
							$row3=mysqli_fetch_array($results3);
							$results4=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=7 LIMIT 1;", $dbweb, __FILE__, __LINE__);
							$row4=mysqli_fetch_array($results4);
							$src3='files/'.floor($row4["id_file"]/1000).'/'.$row4["id_file"].'.'.$row4["extension"];
							echo '<a'.$style.' class="highslide" href="'.PATH.$src3.'" onclick="return hs.expand(this)">';
							echo '	<img style="margin:5px; border:0; padding:0;" src="'.PATH.$src2.'" alt="'.$row4["description"].'" title="'.$row4["description"].'" />';
							echo '</a>';
						}
						echo '</div>';
					}
				}
				
				//show article
				$row2["introduction"]=str_replace('src="', 'src="'.PATH, $row2["introduction"]);
				$row2["introduction"]=str_replace('src="'.PATH.'http', 'http', $row2["introduction"]);
				$row2["article"]=str_replace('src="', 'src="'.PATH, $row2["article"]);
				$row2["article"]=str_replace('src="'.PATH.'http', 'src="http', $row2["article"]);
				echo '<span style="font-weight:bold;">';
				echo $row2["introduction"];
				echo '</span>';
				echo $row2["article"];
				
				//show attachments
				$results=q("SELECT * FROM cms_articles_files WHERE article_id=".$id_article.";", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($results)>0)
				{
					echo '<hr />';
					echo 'Anhänge:';
					echo '<ul>';
					while($row=mysqli_fetch_array($results))
					{
						echo '<li>';
						$results2=q("SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
						$row2=mysqli_fetch_array($results2);
						echo '	<a href="'.PATH.'download.php?id_file='.$row["file_id"].'">'.$row2["filename"].'.'.$row2["extension"].' ('.number_format($row2["filesize"]/1024, 2).'kB)</a>';
						if ($row2["description"]!="") echo '<br /><i>'.$row2["description"].'</i>';
						echo '</li>';
					}
					echo '</ul>';
				}

				if ($echo and $id_article!=106 and $id_article!=107)
				{
					echo '<hr />';
					echo '<p>';
					echo '<span ><div class="g-plusone" data-size="medium"></div></span>';
					echo '</p><p>';
					echo '<span ><div class="fb-like" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false"></div></span>';
					echo '</p><p>';
					echo '<input class="button" type="button" value="Zurück zur Übersicht" onClick="history.back();">';
					echo '</p>';
				}
				
			}
		}
	}
?>