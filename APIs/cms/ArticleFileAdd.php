<?php

	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 25.03.2014 ***
	*************************/

	check_man_params(array("article_id" => "numeric"));

	//get filename
	$arrfilename = explode(".",$_POST['filename']);
	$article_id =$_POST['article_id'];
	
	$extension = array_pop($arrfilename);
	$filename = implode(".",$arrfilename);
	
	$filesize =$_POST['filesize'];
	$filename_temp =$_POST['filename_temp'];
	
	$query="INSERT INTO cms_files (filename, extension, filesize, description, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."','".$extension."','".$filesize."','', 0, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");";
	q($query, $dbweb, __FILE__, __LINE__);
	$file_id=mysqli_insert_id($dbweb);

	$dir=floor(bcdiv($file_id, 1000));
	if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
	$destination='../files/'.$dir.'/'.$file_id.'.'.$extension;
	$filename=substr($destination, 3);
	
	$query="SELECT ordering FROM cms_articles_files WHERE article_id=".$article_id." ORDER BY ordering DESC LIMIT 1;";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ordering=$row["ordering"]+1;
	
	$query="INSERT INTO cms_articles_files (article_id, file_id, ordering) VALUES(".$article_id.", ".$file_id.", ".$ordering.");";
	q($query, $dbweb, __FILE__, __LINE__);
	
	copy($filename_temp, $destination);

	$xml = '<ArticleFileAdd>'."\n";
	$xml .= '	<Ack>Success</Ack>'."\n";
	$xml .= '	<File>'.str_replace("../", "", $destination).'</File>'."\n";
	$xml .= '	<Filename>'.$destination.'</Filename>'."\n";
	$xml .= '	<Path>'.PATH.$filename.'</Path>'."\n";
	$xml .= '</ArticleFileAdd>'."\n";

	print $xml;

?>