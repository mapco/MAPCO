<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("article_id"	=> "numeric", "lang"	=> "text");
	
	check_man_params($required);
	
	$article_id = $_POST['article_id'];
	$lang = $_POST['lang'];
	
	$xml = '<article_translation>'."\n";
	$sql = "SELECT id_article, title, firstmod, firstmod_user as firstmod_user_id, lastmod, lastmod_user as lastmod_user_id, language_id FROM cms_articles WHERE article_id=".$article_id;
	$result = q($sql, $dbweb, __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc($result) )
	{
		foreach($row as $key => $value)
		{
			$xml .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>'."\n";
		}
		$sql2 = "SELECT username FROM cms_users WHERE id_user=".$row['firstmod_user_id'];
		$result2 = q($sql2, $dbweb, __FILE__, __LINE__);
		$row2 = mysqli_fetch_assoc($result2);
		$xml .= '<firstmod_user><![CDATA['.$row2['username'].']]></firstmod_user>'."\n";
		
		$sql2 = "SELECT username FROM cms_users WHERE id_user=".$row['lastmod_user_id'];
		$result2 = q($sql2, $dbweb, __FILE__, __LINE__);
		$row2 = mysqli_fetch_assoc($result2);
		$xml .= '<lastmod_user><![CDATA['.$row2['username'].']]></lastmod_user>'."\n";
	}
	$xml .= '</article_translation>'."\n";

	print $xml;
?>