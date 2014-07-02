<?php
	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 26.03.2014 ***
	*************************/
	
	$required=array("art_shop_id"	=> "numeric");
	check_man_params($required);
	
	$xml = '';

	$results=q("SELECT id FROM cms_articles_shopitems WHERE id=".$_POST["art_shop_id"].";", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{ 
		$xml .= '	<Error>Zuordnung von Artikel und Shopartikel nicht gefunden</Error>'."\n";
	}
	else
	{ 
		q("DELETE FROM cms_articles_shopitems WHERE id = '".$_POST["art_shop_id"]."';", $dbweb, __FILE__, __LINE__);	
	}
	print $xml;
?>