<?php
	include("config.php");
	header ("Content-Type:text/xml");

	//header
	echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.sitemaps.org/schemas/sitemap-image/1.1" xmlns:video="http://www.sitemaps.org/schemas/sitemap-video/1.1">'."\n";

	//content online-shop
	$results3=q("SELECT * FROM cms_languages WHERE code='zh' ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while( $row3=mysqli_fetch_array($results3) )
	{
		$results2=q("SELECT * FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) )
		{
			echo '<url>'."\n";
			if( $row3["code"]=="de" ) echo '<loc>'.PATH.'online-shop/autoteile/'.$row2["id_item"].'/</loc>'."\n";
			else echo '<loc>'.PATH.$row3["code"].'/'.'online-shop/autoteile/'.$row2["id_item"].'/</loc>'."\n";
			echo '</url>'."\n";
		}
	}

	echo '</urlset>'."\n";
?>