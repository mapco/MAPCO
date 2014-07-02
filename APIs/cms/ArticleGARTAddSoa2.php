<?php

	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 25.03.2014 ***
	*************************/
	
	$required=array("article_id"	=> "numeric");
	check_man_params($required);

	if ( $_POST['gart_id'] != 0 && $_POST['gart_id'] != '' )
	{
		$data['article_id'] = $_POST['article_id'];
		$data['gart_id'] = $_POST['gart_id'];
		$data['firstmod'] = time();
		$data['firstmod_user'] = $_SESSION["id_user"];
		$data['lastmod'] = $data['firstmod'];
		$data['lastmod_user'] = $data['firstmod_user'];
		
		$result = q_insert('cms_articles_gart',$data, $dbweb, __FILE__, __LINE__);
		$xml = '<insert_id>'.mysqli_insert_id($dbweb).'</insert_id>';
	}
	else
	{
		$xml = '<Error>Kein Generischer Artikel ausgewählt!</Error>';
	}
	print $xml;
?>