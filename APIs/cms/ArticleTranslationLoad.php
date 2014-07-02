<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("translation_id"	=> "numeric");
	
	check_man_params($required);
	
	$translation_id = $_POST['translation_id'];
	
	$xml = '<article_translation>'."\n";
	$sql = "SELECT id_article, title, introduction, article, published, meta_title, meta_keywords, meta_description FROM cms_articles WHERE id_article=".$translation_id;
	$result = q($sql, $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($result);
	foreach($row as $key => $value)
	{
		$xml .= '	<'.$key.'><![CDATA['.$value.']]></'.$key.'>'."\n";
	}
	$xml .= '</article_translation>'."\n";

	print $xml;
?>