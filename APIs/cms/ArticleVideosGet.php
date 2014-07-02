<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("article_id"	=> "numeric", "lang"	=> "text");
	
	check_man_params($required);
	
	$article_id = $_POST['article_id'];
	$lang = $_POST['lang'];
	$xml = '';
	
	$query="SELECT id, file_id, ordering FROM cms_articles_videos WHERE article_id=".$article_id." ORDER BY ordering;";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$xml .= '	<article_video>'."\n";
		
		$query="SELECT filename, extension, description FROM cms_files WHERE id_file=".$row["file_id"].";";
		$results2=q($query, $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		
		$xml .= '		<id>'.$row["id"].'</id>'."\n";
		$xml .= '		<file_id>'.$row["file_id"].'</file_id>'."\n";
		$xml .= '		<ordering>'.$row["ordering"].'</ordering>'."\n";
		$xml .= '		<filename>'.$row2["filename"].'</filename>'."\n";
		$xml .= '		<extension>'.$row2["extension"].'</extension>'."\n";
		$xml .= '		<description>'.$row2["description"].'</description>'."\n";
		$xml .= '	</article_video>'."\n";
	}
	print $xml;
?>