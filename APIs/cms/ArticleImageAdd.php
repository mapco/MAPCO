<?php

	if ( !isset($_POST["id_article"]) )
	{
		echo '<ArticleImageAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikel-ID übermittelt werden, damit der Service weiß, zu welchem Artikel die Datei gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageAddResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["source"]) )
	{
		echo '<ArticleImageAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Datei übermittelt werden, damit der Service weiß, welche Datei verarbeitet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageAddResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["filename"]) )
	{
		echo '<ArticleImageAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Datei übermittelt werden, damit der Service weiß, welche Datei verarbeitet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageAddResponse>'."\n";
		exit;
	}

	$filename=substr($_POST["filename"], strpos($_POST["filename"], "/"), strlen($_POST["filename"]));
	$filename=substr($filename, 0, strrpos($filename, "."));
	$extension=substr($_POST["filename"], strrpos($_POST["filename"], ".")+1, strlen($_POST["filename"]));
	$filesize=filesize($_POST["source"]);
	$description="";
	$imageformat_id=0;
	$original_id=0;
	$EPS_link="";
	$firstmod=time();
	$firstmod_user=$_SESSION["id_user"];
	$lastmod=time();
	$lastmod_user=$_SESSION["id_user"];
	q("INSERT INTO cms_files (filename, extension, filesize, description, imageformat_id, original_id, EPS_link, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".mysqli_real_escape_string($dbweb, $filename)."', '".mysqli_real_escape_string($dbweb,$extension)."', '".mysqli_real_escape_string($dbweb,$filesize)."', '".mysqli_real_escape_string($dbweb, $description)."', ".$imageformat_id.", ".$original_id.", '".mysqli_real_escape_string($dbweb,$EPS_link)."', ".$firstmod.", ".$firstmod_user.", ".$lastmod.", ".$lastmod_user.");", $dbweb, __FILE__, __LINE__);
	$id_file=mysqli_insert_id($dbweb);
	$results=q("SELECT id FROM cms_articles_images WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	$ordering=mysqli_num_rows($results)+1;
	q("INSERT INTO cms_articles_images (article_id, file_id, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["id_article"].", ".$id_file.", ".$ordering.", ".$firstmod.", ".$firstmod_user.", ".$lastmod.", ".$lastmod_user.");", $dbweb, __FILE__, __LINE__);
	$folder=floor($id_file/1000);
	if (!file_exists("../files/".$folder)) mkdir("../files/".$folder);
	$destination="../files/".$folder."/".$id_file.".".$extension;
	copy($_POST["source"], $destination);
	unlink($_POST["source"]);
	
	echo '<ArticleImageAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<id_file>'.$id_file.'</id_file>'."\n";
	echo '	<Sourcefile>'.$_POST["source"].'</Sourcefile>'."\n";
	echo '	<Filename>'.$destination.'</Filename>'."\n";
	echo '</ArticleImageAddResponse>'."\n";

?>