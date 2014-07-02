<?php
	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 26.03.2014 ***
	*************************/
	
	check_man_params(array("art_label_id"	=> "numeric"));
	$xml = '';
						   
	$results=q("SELECT id, label_id, ordering FROM cms_articles_labels WHERE id='".$_POST["art_label_id"]."';", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		$xml .= '	<Error>Zuordnung von Artikel und Shopartikel nicht gefunden</Error>'."\n";
	}
	else
	{ 
		$row = mysqli_fetch_assoc($results);
		q("DELETE FROM cms_articles_labels WHERE id=".$row['id'].";", $dbweb, __FILE__, __LINE__);
		q("UPDATE cms_articles_labels SET ordering=ordering-1 WHERE label_id=".$row['label_id']." AND ordering>".$row['ordering'].";", $dbweb, __FILE__, __LINE__);		
	}
	print $xml;
?>