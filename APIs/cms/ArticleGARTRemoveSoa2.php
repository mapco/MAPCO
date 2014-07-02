<?php
	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 26.03.2014 ***
	*************************/
	
	$required=array("article_gart_id"	=> "numeric");
	check_man_params($required);

	$xml = '';

	$results=q("SELECT id FROM cms_articles_gart WHERE id='".$_POST["article_gart_id"]."';", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		$xml .= '<Error>Zuordnung von Artikel und GART nicht gefunden</Error>'."\n";
		print $xml;
	}
	else
	{
		q("DELETE FROM cms_articles_gart WHERE id = '".$_POST["article_gart_id"]."';", $dbweb, __FILE__, __LINE__);
	}

	print $xml;
?>