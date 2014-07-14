<?php
	include("config.php");
	include("functions/cms_url_encode.php");




	//XML Entity Replacer
		function xmlentities($string)
		{
   		return str_replace ( array ( '&', '"', "'", '<', '>', '?' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string );
		}

	//header
		header("Content-Type: application/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
		echo '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";

	//channel data
		echo "	<channel>\n";
		echo "		<title>MAPCO - News</title>\n";
		echo "		<link>http://www.mapco.de/</link>\n";
		echo "		<description>Nachrichten</description>\n";
		echo "		<language>de</language>\n";
		echo "		<copyright>Copyright ".date("Y", time()).", MAPCO Autotechnik GmbH</copyright>\n";
		echo "		<managingEditor>flangrock@mapco.de (Frank Langrock)</managingEditor>\n";
//		echo "		<webMaster>habermann@webdesignpotsdam.de (Jens Habermann)</webMaster>\n";
		//echo "		<lastBuildDate>".$row["date"]."</lastBuildDate>\n";
		echo "		<category>Autoteile</category>\n";
		echo "		<category>Autos</category>\n";
		echo "		<category>Autotechnik</category>\n";
		echo "		<generator>mapco.de News Feed Generator</generator>\n";
//		echo "		<docs>http://backend.userland.com/rss</docs>\n";
		echo "		<image>\n";
		echo "			<url>http://www.mapco.de/images/quality.jpg</url>\n";
		echo "			<title>MAPCO - News</title>\n";
		echo "			<link>http://www.mapco.de/</link>\n";
		echo "		</image>\n";

	//atom link???
		echo '<atom:link href="http://www.mapco.de/rss.php" rel="self" type="application/rss+xml" />'."\n";

	//add news items
		$results=q("SELECT * FROM cms_articles AS a, cms_articles_labels AS b WHERE b.label_id=3 AND a.published>0 AND a.id_article=b.article_id ORDER BY firstmod DESC LIMIT 20;", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$row = mysqli_fetch_array($results);
			echo "		<item>\n";
			echo "			<title>".xmlentities($row["title"])."</title>\n";
			echo '			<link>http://www.mapco.de/de/news/'.$row["id_article"].'/'.url_encode($row["title"]).'</link>'."\n";
			
			echo '			<pubDate>'.date("r", $row["firstmod"]).'</pubDate>'."\n";
			echo '			<description>'.xmlentities($row["introduction"]).'</description>'."\n";
			echo '			<content:encoded>'."\n";
			echo '			<![CDATA['."\n";
			$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["id_article"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results3)>0)
			{
				$row3=mysqli_fetch_array($results3);
				$results4=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=2;", $dbweb, __FILE__, __LINE__);
				if ( mysqli_num_rows($results4)>0 )
				{
					$row4=mysqli_fetch_array($results4);
					$filename='files/'.floor(bcdiv($row4["id_file"], 1000)).'/'.$row4["id_file"].'.'.$row4["extension"];
					echo '<a href="'.PATHLANG.'news/'.$row["id_article"].'/'.url_encode($row["title"]).'" title="'.$row["title"].'">'."\n";
					echo '	<img src="'.PATH.$filename.'" alt="" title="" />'."\n";
					echo '</a>'."\n";
				}
			}
			echo '			<p>'."\n";
			echo '				'.xmlentities($row["introduction"]).' [<a href="http://www.titanenderrennbahn.de/news.php?n_id='.$row["id_news"].'">mehr</a>]'."\n";

			echo '			</p>'."\n";
			echo '			]]>'."\n";
			echo '			</content:encoded>'."\n";
			echo '			<guid>http://www.mapco.de/de/news/'.$row["id_article"].'/'.url_encode($row["title"]).'</guid>'."\n";
			echo "			<author>flangrock@mapco.de (Frank Langrock)</author>"."\n";
			echo "		</item>\n";
		}

	//close channel and feed
		echo "	</channel>\n";
		echo "</rss>";

?>