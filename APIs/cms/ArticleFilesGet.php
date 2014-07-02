<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("article_id"	=> "numeric", "language_id"	=> "text");
	
	check_man_params($required);
	
	$article_id = $_POST['article_id'];
	$lang = $_POST['language_id'];
	
	//FILE VIEW
 
	$query="SELECT af.id, af.file_id, af.ordering, f.filename, f.extension, f.filesize, f.description,f.EPS_link, f.firstmod, f.firstmod_user, f.lastmod, f.lastmod_user FROM cms_articles_files AS af, cms_files AS f WHERE af.article_id=".$article_id." AND f.id_file=af.file_id ORDER BY af.ordering ASC;"; //32247
	$results=q($query, $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)<1)
	{
		$xml .= '<Error>Es sind noch keine Dateianhänge mit diesem Artikel verknüpft!</Error>'."\n";
	}	
	while($row=mysqli_fetch_array($results))
	{
		$xml .= '<file>'."\n";
		$xml .= '	<article_file_id>'.$row["id"].'</article_file_id>'."\n";
		$xml .= '	<file_id>'.$row["file_id"].'</file_id>'."\n";
		$xml .= '	<ordering>'.$row["ordering"].'</ordering>'."\n";
		$xml .= '	<filename>'.$row["filename"].'</filename>'."\n";
		$xml .= '	<extension>'.$row["extension"].'</extension>'."\n";
		$xml .= '	<description>'.$row["description"].'</description>'."\n";
		$xml .= '	<filesize>'.$row["filesize"].'</filesize>'."\n";
		$xml .= '	<EPS_link>'.$row["EPS_link"].'</EPS_link>'."\n";
		$xml .= '	<firstmod>'.$row["firstmod"].'</firstmod>'."\n";
		$xml .= '	<firstmod_user>'.$row["firstmod_user"].'</firstmod_user>'."\n";
		$xml .= '	<lastmod>'.$row["lastmod"].'</lastmod>'."\n";
		$xml .= '	<lastmod_user>'.$row["lastmod_user"].'</lastmod_user>'."\n";
		$xml .= '</file>'."\n";
	}

	print $xml;
?>