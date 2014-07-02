<?php
	include("config.php");
	header ("Content-Type:text/xml");

	//header
	echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.sitemaps.org/schemas/sitemap-image/1.1" xmlns:video="http://www.sitemaps.org/schemas/sitemap-video/1.1">'."\n";

	//content menus
	$results3=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while( $row3=mysqli_fetch_array($results3) )
	{
		$results=q("SELECT * FROM cms_menus WHERE NOT idtag='backendmenu' AND site_id IN(0, ".$_SESSION["id_site"].");", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$results2=q("SELECT * FROM cms_menuitems WHERE menu_id=".$row["id_menu"].";", $dbweb, __FILE__, __LINE__);
			while( $row2=mysqli_fetch_array($results2) )
			{
				echo '<url>'."\n";
				if ( $row2["alias"]!="" )
				{
					if( $row3["code"]=="de" ) echo '	<loc>'.PATH.$row2["alias"].'</loc>'."\n";
					else echo '	<loc>'.PATH.$row3["lang"].'/'.$row2["alias"].'</loc>'."\n";
				}
				else
				{
					echo '	<loc>'.PATH.$row2["link"].'</loc>'."\n";
				}
/*
				echo '	<image:image>';
				echo '		<image:loc>http://example.com/image.jpg</image:loc> ';
				echo '	</image:image>';
				echo '  <video:video>     ';
				echo '		<video:content_loc>http://www.example.com/video123.flv</video:content_loc>';
				echo '		<video:player_loc allow_embed="yes" autoplay="ap=1">http://www.example.com/videoplayer.swf?video=123</video:player_loc>';
				echo '		<video:thumbnail_loc>http://www.example.com/miniaturbilder/123.jpg</video:thumbnail_loc>';
				echo '		<video:title>Grillen im Sommer</video:title>  ';
				echo '		<video:description>So grillen Sie jedes Mal perfekte Steaks</video:description>';
				echo '	</video:video>';
*/
				echo '</url>'."\n";
			}
		}
	}

	echo '</urlset>'."\n";
?>