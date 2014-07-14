<?php
	include("config.php");
	header ("Content-Type:text/xml");

	//header
	echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.sitemaps.org/schemas/sitemap-image/1.1" xmlns:video="http://www.sitemaps.org/schemas/sitemap-video/1.1">'."\n";

	//content menus
	$results=q("SELECT * FROM cms_sites_languages WHERE site_id=".$_SESSION["id_site"]."  ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM cms_languages WHERE id_language=".$row["language_id"].";", $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);

		$results3=q("SELECT * FROM cms_menus WHERE NOT idtag='backendmenu' AND site_id IN(0, ".$_SESSION["id_site"].");", $dbweb, __FILE__, __LINE__);
		while( $row3=mysqli_fetch_array($results3) )
		{
			$results4=q("SELECT * FROM cms_menuitems WHERE menu_id=".$row3["id_menu"].";", $dbweb, __FILE__, __LINE__);
			while( $row4=mysqli_fetch_array($results4) )
			{
				$results5=q("SELECT * FROM cms_menuitems_languages WHERE menuitem_id=".$row4["id_menuitem"]." and language_id=".$row["language_id"].";", $dbweb, __FILE__, __LINE__);
				while( $row5=mysqli_fetch_array($results5) )
				{
					echo '<url>'."\n";
					if ( $row5["alias"]!="" )
					{
						if( $row["ordering"]==1 ) echo '	<loc>'.PATH.$row5["alias"].'</loc>'."\n";
						else echo '	<loc>'.PATH.$row2["code"].'/'.$row5["alias"].'</loc>'."\n";
					}
					elseif ($row4["home"]=1)
					{
						if( $row["ordering"]==1 ) echo '	<loc>'.PATH.'</loc>'."\n";
						else echo '	<loc>'.PATH.$row2["code"].'/'.'</loc>'."\n";
					}
					echo '</url>'."\n";
				}
			}
		}
	}
	
	echo '</urlset>'."\n";
?>