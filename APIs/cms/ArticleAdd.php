<?php

	check_man_params(array("title"		=> "text",
						   "article"	=> "text",
						   "format"		=> "numeric"));

	//default params
	if( isset($_POST["id_language"]) )
	{
		$results=q("SELECT id_language FROM cms_languages WHERE id_language='".$_POST["id_language"]."';", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 ) unset($_POST["id_language"]);
	}
	if( !isset($_POST["id_language"]) )
	{
		$results=q("SELECT * FROM cms_languages ORDER BY ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["id_language"]=$row["id_language"];
	}
	if( !isset($_POST["article_id"]) ) $_POST["article_id"]=0;
	if( !isset($_POST["introduction"]) ) $_POST["introduction"]="";
	if( !isset($_POST["published"]) ) $_POST["published"]=0;
	if( !isset($_POST["imageprofile_id"]) ) $_POST["imageprofile_id"]=0;

	if(isset($_POST["site_id"]))
		$id_site=$_POST["site_id"];
	else
		$id_site=$_SESSION["id_site"];

	//get ordering
	$results=q("SELECT COUNT(id_article) FROM cms_articles;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ordering=$row[0]+1;

	//insert article
	$data=array();
	$data["site_id"]=$id_site;
	$data["language_id"]=$_POST["id_language"];
	$data["article_id"]=0;
	$data["title"]=$_POST["title"];
	$data["introduction"]=$_POST["introduction"];
	$data["article"]=$_POST["article"];
	$data["published"]=$_POST["published"];
	$data["format"]=$_POST["format"];
	$data["imageprofile_id"]=$_POST["imageprofile_id"];
	$data["ordering"]=$_POST["ordering"];
	$data["newsletter"]=$_POST["newsletter"];
	$data["firstmod"]=time();
	$data["firstmod_user"]=$_SESSION["id_user"];
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	$results=q_insert("cms_articles", $data, $dbweb, __FILE__, __LINE__);
	$article_id=mysqli_insert_id($dbweb);
						   
	echo '	<article_id>'.$article_id.'</article_id>'."\n";

?>