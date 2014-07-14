<?php
	include("config.php");
	header ("Content-Type:text/xml");

	//header
	echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.sitemaps.org/schemas/sitemap-image/1.1" xmlns:video="http://www.sitemaps.org/schemas/sitemap-video/1.1">'."\n";


	$results=q("SELECT * FROM cms_sites_languages WHERE site_id=".$_SESSION["id_site"]." and ordering=1 LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$results=q("SELECT * FROM cms_menuitems_languages WHERE menuitem_id=832 and language_id=".$row["language_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($results)==0)
	{
		$alias="oe-nummern-suche/";
	}
	else
	{
		$row=mysqli_fetch_array($results);
		$alias=$row["alias"];
	}

	//content online-shop
	$i=0;
	$results3=q("SELECT MPN FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row3=mysqli_fetch_array($results3) )
	{
		$results2=q("SELECT OENr FROM t_203 WHERE ArtNr='".$row3["MPN"]."' AND OENr NOT LIKE '%/%';", $dbshop, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) )
		{
			$i++;
			if ($i>=150000)
			{
				echo '<url>'."\n";
				echo '<loc>'.PATH.$alias.$row2["OENr"].'/</loc>'."\n";
				echo '</url>'."\n";
			}
			if ($i==199999) break;
		}
		if ($i==199999) break;
	}

	echo '</urlset>'."\n";
?>
